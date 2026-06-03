from __future__ import annotations

import argparse
import base64
import hashlib
import json
import time
from pathlib import Path
from urllib.parse import quote

import requests
from Crypto.Hash import SHA256
from Crypto.PublicKey import RSA
from Crypto.Signature import pkcs1_15
from openpyxl.utils import get_column_letter
from urllib3.exceptions import InsecureRequestWarning

requests.packages.urllib3.disable_warnings(category=InsecureRequestWarning)


SHEET_NAME = 'Data_Usaha_Besar'
SPREADSHEET_SCOPE = 'https://www.googleapis.com/auth/spreadsheets'
SPREADSHEET_API_HOST = 'sheets.googleapis.com'
SPREADSHEET_API_IPS = [
    '74.125.24.95',
    '172.253.144.95',
    '142.250.4.95',
]


def parse_args() -> argparse.Namespace:
    parser = argparse.ArgumentParser(description='Update status for a row in Data_Usaha_Besar.')
    parser.add_argument('--spreadsheet-id', required=True, help='Google Spreadsheet ID')
    parser.add_argument('--credentials', required=True, help='Path to service account JSON file')
    parser.add_argument('--id', required=True, help='id_usaha_besar value to update')
    parser.add_argument('--status', required=True, help='New status value (OPEN/PROCESS/SUCCESS)')
    return parser.parse_args()


def normalize_status(value: str) -> str:
    normalized = value.strip().upper()
    allowed = {'OPEN', 'PROCESS', 'SUCCESS'}
    if normalized not in allowed:
        raise ValueError(f'Invalid status: {value}')
    return normalized


def status_to_sheet_value(status: str) -> str:
    return {
        'OPEN': 'Open',
        'PROCESS': 'Process',
        'SUCCESS': 'Success',
    }[status]


def load_service_account(credentials_path: Path) -> dict:
    data = json.loads(credentials_path.read_text(encoding='utf-8'))
    required_keys = {'client_email', 'private_key'}
    missing = required_keys - set(data)
    if missing:
        raise RuntimeError(f'Service account JSON missing keys: {", ".join(sorted(missing))}')
    return data


def normalize_private_key_text(private_key: str) -> str:
    text = str(private_key).replace('\r\n', '\n').replace('\\n', '\n').strip()
    if text.startswith('"') and text.endswith('"'):
        text = text[1:-1].strip()
    if text.startswith("'") and text.endswith("'"):
        text = text[1:-1].strip()

    begin_marker = '-----BEGIN PRIVATE KEY-----'
    end_marker = '-----END PRIVATE KEY-----'
    begin_index = text.find(begin_marker)
    end_index = text.find(end_marker)

    if begin_index != -1 and end_index != -1:
        text = text[begin_index:end_index + len(end_marker)]

    if not text.startswith(begin_marker) or not text.endswith(end_marker):
        raise ValueError('Invalid private key format')

    return f'{text}\n'


def b64url(data: bytes) -> str:
    return base64.urlsafe_b64encode(data).decode('ascii').rstrip('=')


def create_access_token(credentials: dict, scopes: list[str]) -> str:
    now = int(time.time())
    header = {'alg': 'RS256', 'typ': 'JWT'}
    claims = {
        'iss': credentials['client_email'],
        'sub': credentials['client_email'],
        'scope': ' '.join(scopes),
        'aud': 'https://sheets.googleapis.com/',
        'iat': now,
        'exp': now + 3600,
    }
    header_segment = b64url(json.dumps(header, separators=(',', ':')).encode('utf-8'))
    claims_segment = b64url(json.dumps(claims, separators=(',', ':')).encode('utf-8'))
    signing_input = f'{header_segment}.{claims_segment}'.encode('ascii')

    private_key_text = normalize_private_key_text(credentials['private_key'])
    try:
        private_key = RSA.import_key(private_key_text.encode('utf-8'))
    except Exception as exc:
        key_fingerprint = hashlib.sha256(private_key_text.encode('utf-8')).hexdigest()
        diagnostics = {
            'length': len(private_key_text),
            'fingerprint': key_fingerprint,
            'starts_with': private_key_text[:30],
            'ends_with': private_key_text[-30:],
        }
        raise ValueError(f'Invalid private key: {diagnostics}') from exc
    digest = SHA256.new(signing_input)
    signature = pkcs1_15.new(private_key).sign(digest)
    return f'{header_segment}.{claims_segment}.{b64url(signature)}'


def authorized_request(method: str, path: str, token: str, **kwargs) -> requests.Response:
    headers = kwargs.pop('headers', {})
    headers = {
        **headers,
        'Authorization': f'Bearer {token}',
        'Accept': 'application/json',
        'Host': SPREADSHEET_API_HOST,
    }
    last_error: Exception | None = None

    for ip_address in SPREADSHEET_API_IPS:
        url = f'https://{ip_address}/v4{path}'
        try:
            response = requests.request(method, url, headers=headers, timeout=60, verify=False, **kwargs)
            if response.status_code >= 400:
                raise RuntimeError(f'{method} {url} failed: {response.status_code} {response.text}')
            return response
        except Exception as exc:
            last_error = exc
            continue

    raise RuntimeError(f'{method} {path} failed on all IPs: {last_error}')


def encode_range(range_name: str) -> str:
    return quote(range_name, safe='')


def main() -> None:
    args = parse_args()
    credentials_path = Path(args.credentials)
    if not credentials_path.is_file():
        raise FileNotFoundError(f'Credentials not found: {credentials_path}')

    status = normalize_status(args.status)
    credentials = load_service_account(credentials_path)
    token = create_access_token(credentials, [SPREADSHEET_SCOPE])

    header_response = authorized_request(
        'GET',
        f'/spreadsheets/{args.spreadsheet_id}/values/{encode_range(f"\'{SHEET_NAME}\'!A1:Z1")}',
        token,
        params={'majorDimension': 'ROWS'},
    ).json()
    header_rows = header_response.get('values', [])
    if not header_rows:
        raise RuntimeError(f'Header row not found in {SHEET_NAME}')

    headers = [str(value).strip() for value in header_rows[0]]
    header_index = {name: idx for idx, name in enumerate(headers) if name}

    id_col = header_index.get('id_usaha_besar')
    status_col = header_index.get('status')
    if id_col is None or status_col is None:
        raise RuntimeError('Required columns id_usaha_besar/status not found')

    rows_response = authorized_request(
        'GET',
        f'/spreadsheets/{args.spreadsheet_id}/values/{encode_range(f"\'{SHEET_NAME}\'!A2:Z")}',
        token,
        params={'majorDimension': 'ROWS'},
    ).json()
    rows = rows_response.get('values', [])
    if not rows:
        raise RuntimeError(f'No data rows found in {SHEET_NAME}')

    target_row_number = None
    for offset, row in enumerate(rows, start=2):
        row_values = list(row)
        current_id = row_values[id_col] if id_col < len(row_values) else ''
        if str(current_id).strip() == str(args.id).strip():
            target_row_number = offset
            break

    if target_row_number is None:
        raise RuntimeError(f'id_usaha_besar not found: {args.id}')

    column_letter = get_column_letter(status_col + 1)
    update_range = f"'{SHEET_NAME}'!{column_letter}{target_row_number}"
    sheet_value = status_to_sheet_value(status)
    authorized_request(
        'PUT',
        f'/spreadsheets/{args.spreadsheet_id}/values/{encode_range(update_range)}',
        token,
        params={'valueInputOption': 'RAW'},
        json={'values': [[sheet_value]]},
    )

    print(
        json.dumps(
            {
                'ok': True,
                'sheet': SHEET_NAME,
                'row': target_row_number,
                'range': update_range,
                'id_usaha_besar': args.id,
                'status': status,
                'sheet_value': sheet_value,
            },
            ensure_ascii=False,
        )
    )


if __name__ == '__main__':
    main()
