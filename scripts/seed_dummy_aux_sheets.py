from __future__ import annotations

import argparse
import json
from pathlib import Path

from google.oauth2.service_account import Credentials
from googleapiclient.discovery import build


SPREADSHEET_ID_DEFAULT = '13XpBXcQ_0OZc07ev0mrFvyrMtGbVHa4lQzxb7E-WA5o'

KBLI_ROWS = [
    ['Kode', 'Deskripsi'],
    ['10111', 'Industri Pengolahan dan Pengawetan Daging'],
    ['10761', 'Industri Roti dan Kue'],
    ['14111', 'Konveksi Pakaian Jadi'],
    ['15121', 'Industri Alas Kaki'],
    ['16299', 'Industri Kayu Lainnya'],
    ['18111', 'Percetakan Umum'],
    ['22299', 'Industri Barang Plastik Lainnya'],
    ['31001', 'Industri Furnitur Kayu'],
    ['47911', 'Perdagangan Eceran Melalui Media Internet'],
    ['56101', 'Rumah Makan'],
]

USAHA_BESAR_ROWS = [
    ['id_usaha_besar', 'nama_usaha', 'nama_pencacah', 'status'],
    ['UB-001', 'PT Sinar Mojokerto Sejahtera', 'Ayu Pratiwi', 'Open'],
    ['UB-002', 'CV Jaya Mandiri Abadi', 'Budi Santoso', 'Process'],
    ['UB-003', 'PT Karya Pangan Nusantara', 'Citra Lestari', 'Success'],
    ['UB-004', 'PT Mojokerto Tekstil Lestari', 'Dewi Anggraini', 'Open'],
    ['UB-005', 'PT Surya Plastik Mandiri', 'Eko Saputra', 'Process'],
    ['UB-006', 'PT Sukses Furnitur Indonesia', 'Fajar Hidayat', 'Success'],
    ['UB-007', 'PT Inti Percetakan Digital', 'Gina Maharani', 'Open'],
    ['UB-008', 'PT Aneka Alas Kaki Mojokerto', 'Hendra Wijaya', 'Process'],
    ['UB-009', 'PT Internet Retail Nusantara', 'Indah Permata', 'Success'],
    ['UB-010', 'PT Rasa Nusantara Mojokerto', 'Joko Prabowo', 'Open'],
]


def parse_args() -> argparse.Namespace:
    parser = argparse.ArgumentParser(description='Seed dummy auxiliary sheets in Google Spreadsheet.')
    parser.add_argument('--spreadsheet-id', default=SPREADSHEET_ID_DEFAULT)
    parser.add_argument('--credentials', default=r'storage/app/private/google/service-account.json')
    return parser.parse_args()


def ensure_sheet(service, spreadsheet_id: str, title: str) -> None:
    meta = service.spreadsheets().get(spreadsheetId=spreadsheet_id).execute()
    existing = {sheet['properties']['title'] for sheet in meta.get('sheets', [])}
    if title in existing:
        return
    service.spreadsheets().batchUpdate(
        spreadsheetId=spreadsheet_id,
        body={'requests': [{'addSheet': {'properties': {'title': title}}}]},
    ).execute()


def clear_sheet(service, spreadsheet_id: str, title: str) -> None:
    service.spreadsheets().values().clear(
        spreadsheetId=spreadsheet_id,
        range=f"'{title}'",
        body={},
    ).execute()


def set_dropdown(service, spreadsheet_id: str, title: str, column_letter: str, start_row: int, end_row: int, values: list[str]) -> None:
    sheet_meta = service.spreadsheets().get(spreadsheetId=spreadsheet_id).execute()
    sheet_id = None
    for sheet in sheet_meta.get('sheets', []):
        props = sheet.get('properties', {})
        if props.get('title') == title:
            sheet_id = props.get('sheetId')
            break
    if sheet_id is None:
        raise RuntimeError(f'Sheet not found: {title}')

    data_validation_rule = {
        'setDataValidation': {
            'range': {
                'sheetId': sheet_id,
                'startRowIndex': start_row - 1,
                'endRowIndex': end_row,
                'startColumnIndex': ord(column_letter.upper()) - ord('A'),
                'endColumnIndex': ord(column_letter.upper()) - ord('A') + 1,
            },
            'rule': {
                'condition': {
                    'type': 'ONE_OF_LIST',
                    'values': [{'userEnteredValue': value} for value in values],
                },
                'showCustomUi': True,
                'strict': True,
            },
        }
    }

    service.spreadsheets().batchUpdate(
        spreadsheetId=spreadsheet_id,
        body={'requests': [data_validation_rule]},
    ).execute()


def write_rows(service, spreadsheet_id: str, sheet_name: str, rows: list[list[str]]) -> None:
    clear_sheet(service, spreadsheet_id, sheet_name)
    service.spreadsheets().values().update(
        spreadsheetId=spreadsheet_id,
        range=f"'{sheet_name}'!A1",
        valueInputOption='RAW',
        body={'values': rows},
    ).execute()


def main() -> None:
    args = parse_args()
    credentials_path = Path(args.credentials)
    if not credentials_path.exists():
        raise FileNotFoundError(f'Service account JSON not found: {credentials_path}')

    creds = Credentials.from_service_account_file(
        credentials_path,
        scopes=['https://www.googleapis.com/auth/spreadsheets'],
    )
    service = build('sheets', 'v4', credentials=creds, cache_discovery=False)

    ensure_sheet(service, args.spreadsheet_id, 'Data_Usaha_Besar')
    ensure_sheet(service, args.spreadsheet_id, 'Daftar_KBLI')

    write_rows(service, args.spreadsheet_id, 'Data_Usaha_Besar', USAHA_BESAR_ROWS)
    write_rows(service, args.spreadsheet_id, 'Daftar_KBLI', KBLI_ROWS)

    try:
        set_dropdown(service, args.spreadsheet_id, 'Data_Usaha_Besar', 'D', 2, 1000, ['Open', 'Process', 'Success'])
    except Exception:
        # Some spreadsheet layouts reject validation on typed columns; keep the sheet data writable even if validation fails.
        pass

    print(
        json.dumps(
            {
                'ok': True,
                'spreadsheet_id': args.spreadsheet_id,
                'written_sheets': {
                    'Data_Usaha_Besar': len(USAHA_BESAR_ROWS) - 1,
                    'Daftar_KBLI': len(KBLI_ROWS) - 1,
                },
            },
            ensure_ascii=False,
        )
    )


if __name__ == '__main__':
    main()
