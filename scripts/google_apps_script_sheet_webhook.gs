// Update this to the production URL before installing the trigger in Google Apps Script.
const WEBHOOK_URL = 'https://selancar.kanal3516.site/panduanSE/webhook/sheet-sync';
const WEBHOOK_SECRET = 'umkm_sync_2026_secret_key';

// Sheets that should trigger sync. Edit names if your tab names differ.
const TRACKED_SHEETS = ['Data_Usaha_Besar', 'Daftar_KBLI', 'Master_GoogleMaps', 'Master_Tokopedia'];

// IMPORTANT:
// Use installable onEdit/onChange triggers, not simple triggers.
// Simple triggers can fail to call UrlFetchApp because they do not get full authorization.

function onEdit(e) {
  triggerSync_(e, 'onEdit');
}

function onChange(e) {
  triggerSync_(e, 'onChange');
}

function triggerSync_(e, source) {
  try {
    const sheet = getSheetName_(e);
    if (sheet && TRACKED_SHEETS.indexOf(sheet) === -1) {
      return;
    }

    const payload = {
      source: source,
      sheet: sheet || '',
      range: getRangeA1_(e),
      timestamp: new Date().toISOString(),
      spreadsheetId: SpreadsheetApp.getActiveSpreadsheet().getId(),
    };

    const response = UrlFetchApp.fetch(WEBHOOK_URL, {
      method: 'post',
      contentType: 'application/json',
      headers: {
        'X-SYNC-SECRET': WEBHOOK_SECRET,
      },
      payload: JSON.stringify(payload),
      muteHttpExceptions: true,
    });

    Logger.log('Sheet sync status: ' + response.getResponseCode() + ' | body: ' + response.getContentText());
  } catch (err) {
    Logger.log('Sheet sync error: ' + err);
  }
}

function getSheetName_(e) {
  if (e && e.range && e.range.getSheet) {
    return e.range.getSheet().getName();
  }

  if (e && e.source && e.source.getActiveSheet) {
    return e.source.getActiveSheet().getName();
  }

  return '';
}

function getRangeA1_(e) {
  if (e && e.range && e.range.getA1Notation) {
    return e.range.getA1Notation();
  }

  return '';
}

// Manual test from Apps Script editor.
function testWebhookSync() {
  triggerSync_({
    source: SpreadsheetApp.getActiveSpreadsheet(),
  }, 'manual_test');
}

// Backward compatibility: old default Apps Script trigger often points to myFunction.
function myFunction() {
  testWebhookSync();
}
