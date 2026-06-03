from __future__ import annotations

from pathlib import Path

from google.oauth2.service_account import Credentials
from googleapiclient.discovery import build


SPREADSHEET_ID = '13XpBXcQ_0OZc07ev0mrFvyrMtGbVHa4lQzxb7E-WA5o'
SERVICE_ACCOUNT_PATH = Path(r'c:\xampp\htdocs\UMKM\storage\app\private\google\service-account.json')
SHEET_NAME = 'Daftar_KBLI'

ROWS = [
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


def main() -> None:
    creds = Credentials.from_service_account_file(
        SERVICE_ACCOUNT_PATH,
        scopes=['https://www.googleapis.com/auth/spreadsheets'],
    )
    service = build('sheets', 'v4', credentials=creds, cache_discovery=False)

    meta = service.spreadsheets().get(spreadsheetId=SPREADSHEET_ID).execute()
    sheet_id = next(
        (sheet['properties']['sheetId'] for sheet in meta.get('sheets', []) if sheet['properties']['title'] == SHEET_NAME),
        None,
    )

    if sheet_id is not None:
        service.spreadsheets().batchUpdate(
            spreadsheetId=SPREADSHEET_ID,
            body={'requests': [{'deleteSheet': {'sheetId': sheet_id}}]},
        ).execute()

    service.spreadsheets().batchUpdate(
        spreadsheetId=SPREADSHEET_ID,
        body={'requests': [{'addSheet': {'properties': {'title': SHEET_NAME}}}]},
    ).execute()

    service.spreadsheets().values().update(
        spreadsheetId=SPREADSHEET_ID,
        range=f"'{SHEET_NAME}'!A1",
        valueInputOption='RAW',
        body={'values': ROWS},
    ).execute()

    verify = service.spreadsheets().values().get(
        spreadsheetId=SPREADSHEET_ID,
        range=f"'{SHEET_NAME}'!A1:B15",
    ).execute().get('values', [])

    print('rewritten:', SHEET_NAME, 'rows=', len(ROWS) - 1)
    print(verify)


if __name__ == '__main__':
    main()
