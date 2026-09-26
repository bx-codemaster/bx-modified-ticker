<?php
/**
 * BX Modified Ticker - JavaScript Includes
 *
 * Central JavaScript inclusion point for the Ticker module.
 * Ensures that all necessary JS files are loaded for the admin interface.
 *
 * @package    BX Modified Ticker
 * @subpackage JavaScript
 * @version    1.0.0
 * @author     benax
 * @copyright  2006-2026 benax
 * @license    GNU GPL v2.0
 *
 */

defined('_VALID_XTC') or die('Direct Access to this location is not allowed.');

if ( defined('MODULE_BX_MODIFIED_TICKER_STATUS') && 
    ((string)MODULE_BX_MODIFIED_TICKER_STATUS === 'True') && 
    basename($_SERVER['PHP_SELF']) == 'bx_modified_ticker.php') {

    $ui_language = $_SESSION['language_code'] ?? 'de';
    $ui_language_json = json_encode($ui_language, JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT);

    // Standardsprache des Shops = Fallback-Sprache (wie später im Frontend)
    $default_language = defined('DEFAULT_LANGUAGE') ? DEFAULT_LANGUAGE : $ui_language;
    
    $default_language_json = json_encode($default_language, JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT);

    $translations = [
      'bx_txt_speed' => MODULE_BX_MODIFIED_TICKER_SPEED,
      'bx_txt_gap' => MODULE_BX_MODIFIED_TICKER_GAP,
      'bx_txt_size' => MODULE_BX_MODIFIED_TICKER_SIZE,
      'bx_txt_pad' => MODULE_BX_MODIFIED_TICKER_PAD,
      'bx_txt_fade' => MODULE_BX_MODIFIED_TICKER_FADE,
      'bx_txt_bg' => MODULE_BX_MODIFIED_TICKER_BG,
      'bx_txt_ink' => MODULE_BX_MODIFIED_TICKER_INK,
      'bx_txt_accent' => MODULE_BX_MODIFIED_TICKER_ACCENT,
      'bx_txt_line' => MODULE_BX_MODIFIED_TICKER_LINE,
      'bx_txt_no_active_message' => MODULE_BX_MODIFIED_TICKER_NO_ACTIVE_MESSAGE,
      'bx_txt_message_text' => MODULE_BX_MODIFIED_TICKER_TEXT,
      'bx_txt_message_link' => MODULE_BX_MODIFIED_TICKER_LINK,
      'bx_txt_message_from' => MODULE_BX_MODIFIED_TICKER_FROM,
      'bx_txt_message_to' => MODULE_BX_MODIFIED_TICKER_TO,
      'bx_txt_saving' => MODULE_BX_MODIFIED_TICKER_SAVING,
      'bx_txt_saved' => MODULE_BX_MODIFIED_TICKER_SAVED,
      'bx_txt_save_error' => MODULE_BX_MODIFIED_TICKER_SAVE_ERROR,
    ];

?>
 <script>
/* Läuft erst nach dem Laden des DOM (extra/javascript wird im <head> eingebunden)
   und in eigenem Scope, damit weder jQuery ($) noch andere Admin-Skripte
   durch globale Namen überschrieben werden. */
document.addEventListener('DOMContentLoaded', function () {
"use strict";

const defaultLanguage = <?php echo $default_language_json; ?>;
const I18n = <?php echo json_encode($translations, JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT); ?>;

// Aktuelle Werte aus der Datenbank (Konfiguration, Label, Meldungen).
const tickerSettings = <?php echo json_encode($bx_settings, JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT); ?>;

const tickerState = {
  lang: <?php echo $ui_language_json; ?>,
  label: <?php echo json_encode($bx_label, JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT); ?>,
  items: <?php echo json_encode($bx_items, JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT); ?>
};

const sliderDefinitions = [
  ['speed', I18n.bx_txt_speed, 'px/s', 20, 240, 5],
  ['gap', I18n.bx_txt_gap, 'rem', 1, 8, .25],
  ['size', I18n.bx_txt_size, 'px', 12, 24, 1],
  ['pad', I18n.bx_txt_pad, 'rem', .4, 1.6, .1],
  ['fade', I18n.bx_txt_fade, 'rem', 0, 8, .5]
];

const colorDefinitions = [
  ['bg', I18n.bx_txt_bg],
  ['ink', I18n.bx_txt_ink],
  ['accent', I18n.bx_txt_accent],
  ['line', I18n.bx_txt_line]
];

// Feste, sichere Font-Stacks statt freier Eingabe oder externer Webfonts
const fontStacks = {
  inherit: 'inherit',
  system: 'system-ui, -apple-system, "Segoe UI", Roboto, sans-serif',
  serif: 'Georgia, "Times New Roman", serif',
  mono: 'ui-monospace, "SF Mono", Consolas, monospace'
};

const querySelector = selector => document.querySelector(selector);
const tickerElement = querySelector('#bxa-tk');
const availableLanguages = [...new Set(
  [...document.querySelectorAll('#bxa-langs button')]
    .map(button => button.dataset.l)
    .filter(Boolean)
)];

const selectedLanguageButton = querySelector('#bxa-langs button[aria-pressed="true"]');
if (selectedLanguageButton) {
  tickerState.lang = selectedLanguageButton.dataset.l;
}

availableLanguages.forEach(languageCode => {
  if (!(languageCode in tickerState.label)) {
    tickerState.label[languageCode] = '';
  }

  tickerState.items.forEach(item => {
    if (!(languageCode in item.text)) {
      item.text[languageCode] = '';
    }
  });
});

const escapeHtml = value => String(value).replace(/[&<>"']/g, character => ({
  '&': '&amp;',
  '<': '&lt;',
  '>': '&gt;',
  '"': '&quot;',
  "'": '&#39;'
}[character]));

const getLocalizedText = translations => translations[tickerState.lang] || translations[defaultLanguage] || '';

querySelector('#bxa-sliders').innerHTML = sliderDefinitions.map(([settingKey, label, unit, minimum, maximum, step]) => `
  <label class="bxa-row">
    <span>${label}</span>
    <input type="range" data-k="${settingKey}" min="${minimum}" max="${maximum}" step="${step}" value="${tickerSettings[settingKey]}">
    <output data-o="${settingKey}">${tickerSettings[settingKey]} ${unit}</output>
  </label>
`).join('');

querySelector('#bxa-colors').innerHTML = colorDefinitions.map(([settingKey, label]) => `
  <label class="bxa-row bxa-plain">
    <span>${label}</span>
    <span>
      <output data-o="${settingKey}" style="color:var(--mut);margin-right:.5rem">${tickerSettings[settingKey]}</output>
      <input type="color" data-k="${settingKey}" value="${tickerSettings[settingKey]}">
    </span>
  </label>
`).join('');

querySelector('[data-k=pause]').checked = tickerSettings.pause;

function applyTickerStyles() {
  const tickerStyle = tickerElement.style;

  tickerStyle.setProperty('--bx-bg', tickerSettings.bg);
  tickerStyle.setProperty('--bx-ink', tickerSettings.ink);
  tickerStyle.setProperty('--bx-accent', tickerSettings.accent);
  tickerStyle.setProperty('--bx-line', tickerSettings.line);
  tickerStyle.setProperty('--bx-gap', tickerSettings.gap + 'rem');
  tickerStyle.setProperty('--bx-size', tickerSettings.size + 'px');
  tickerStyle.setProperty('--bx-pad', tickerSettings.pad + 'rem');
  tickerStyle.setProperty('--bx-fade', tickerSettings.fade + 'rem');
  tickerStyle.setProperty('--bx-font', fontStacks[tickerSettings.font] || fontStacks.inherit);
  tickerStyle.setProperty('--bx-weight', tickerSettings.weight);

  tickerElement.classList.toggle('rev', tickerSettings.dir === 'right');
  tickerElement.classList.toggle('pause', tickerSettings.pause);
  querySelector('#bxa-frame').dataset.pos = tickerSettings.pos;

  const tickerGroup = tickerElement.querySelector('.bx-ticker-group');
  if (tickerGroup) {
    const rootFontSize = parseFloat(getComputedStyle(document.documentElement).fontSize);
    const tickerDistance = tickerGroup.offsetWidth + tickerSettings.gap * rootFontSize;
    tickerStyle.setProperty('--bx-dur', Math.max(4, tickerDistance / tickerSettings.speed).toFixed(1) + 's');
  }
}

function render() {
  const currentDate = new Date().toISOString().slice(0, 10);
  const tickerItemsMarkup = tickerState.items
    .filter(item => item.active && (!item.from || item.from <= currentDate) && (!item.to || item.to >= currentDate) && getLocalizedText(item.text))
    .map(item => `
      <li>${item.link ? `<a href="#">${escapeHtml(getLocalizedText(item.text))}</a>` : escapeHtml(getLocalizedText(item.text))}</li>
    `)
    .join('') || '<li>' + I18n.bx_txt_no_active_message + '</li>';

  tickerElement.innerHTML = `
    <div class="bx-ticker-label">${escapeHtml(getLocalizedText(tickerState.label))}</div>
    <div class="bx-ticker-viewport">
      <ul class="bx-ticker-group">${tickerItemsMarkup}</ul>
      <ul class="bx-ticker-group" aria-hidden="true">${tickerItemsMarkup}</ul>
    </div>
  `;

  applyTickerStyles();
  updateJsonPreview();
}

// Wird sowohl für die JSON-Vorschau als auch für den echten Speichern-Request genutzt,
// damit beide garantiert denselben Stand zeigen bzw. senden.
function buildPayload() {
  const configuration = {
    BX_TICKER_SPEED: tickerSettings.speed,
    BX_TICKER_GAP: tickerSettings.gap,
    BX_TICKER_FONT_SIZE: tickerSettings.size,
    BX_TICKER_PADDING: tickerSettings.pad,
    BX_TICKER_FADE: tickerSettings.fade,
    BX_TICKER_FONT_FAMILY: tickerSettings.font,
    BX_TICKER_FONT_WEIGHT: tickerSettings.weight,
    BX_TICKER_PAUSE_HOVER: tickerSettings.pause ? 'True' : 'False',
    BX_TICKER_DIRECTION: tickerSettings.dir,
    BX_TICKER_POSITION: tickerSettings.pos,
    BX_TICKER_COLOR_BG: tickerSettings.bg,
    BX_TICKER_COLOR_TEXT: tickerSettings.ink,
    BX_TICKER_COLOR_ACCENT: tickerSettings.accent,
    BX_TICKER_COLOR_LINE: tickerSettings.line
  };

  return {
    configuration,
    label: tickerState.label,
    bx_ticker_items: tickerState.items
  };
}

function updateJsonPreview() {
  querySelector('#bxa-json').textContent = JSON.stringify(buildPayload(), null, 1);
}

function list() {
  querySelector('#bxa-list').innerHTML = tickerState.items.map((item, itemIndex) => `
    <div class="bxa-it" data-i="${itemIndex}">
      <div class="bxa-l1">
        <span class="bxa-hd" draggable="true" tabindex="0" role="button" aria-label="Verschieben">⠿</span>
        <input class="bxa-txt" data-f="text" value="${escapeHtml(item.text[tickerState.lang] || '')}" placeholder="${tickerState.lang !== defaultLanguage && item.text[defaultLanguage] ? 'Fallback: ' + escapeHtml(item.text[defaultLanguage]) : I18n.bx_txt_message_text}" aria-label="Text">
        <input class="bxa-sw" type="checkbox" data-f="active" ${item.active ? 'checked' : ''} aria-label="Aktiv">
        <button class="bxa-x" data-del aria-label="Löschen">✕</button>
      </div>
      <div class="bxa-l2">
        <label>${I18n.bx_txt_message_link}<input class="bxa-txt" data-f="link" value="${escapeHtml(item.link)}" placeholder="optional"></label>
        <label>${I18n.bx_txt_message_from}<input class="bxa-txt" type="date" data-f="from" value="${item.from}"></label>
        <label>${I18n.bx_txt_message_to}<input class="bxa-txt" type="date" data-f="to" value="${item.to}"></label>
      </div>
    </div>
  `).join('');
}

function moveItem(fromIndex, toIndex) {
  if (toIndex < 0 || toIndex >= tickerState.items.length) {
    return;
  }

  tickerState.items.splice(toIndex, 0, tickerState.items.splice(fromIndex, 1)[0]);
  list();
  render();
}

document.addEventListener('input', event => {
  const settingKey = event.target.dataset.k;
  if (!settingKey) {
    return;
  }

  tickerSettings[settingKey] = event.target.type === 'checkbox'
    ? event.target.checked
    : event.target.type === 'range'
      ? +event.target.value
      : event.target.value;

  const outputElement = querySelector(`[data-o=${settingKey}]`);
  if (outputElement) {
    const unit = (sliderDefinitions.find(definition => definition[0] === settingKey) || [])[2];
    outputElement.textContent = tickerSettings[settingKey] + (unit ? ' ' + unit : '');
  }

  applyTickerStyles();
  updateJsonPreview();
});

document.querySelectorAll('[data-seg]').forEach(segmentElement => {
  const syncSegmentButtons = () => segmentElement.querySelectorAll('button').forEach(button => {
    button.setAttribute('aria-pressed', button.dataset.v === tickerSettings[segmentElement.dataset.seg]);
  });

  syncSegmentButtons();
  segmentElement.onclick = event => {
    const button = event.target.closest('button');
    if (!button) {
      return;
    }

    tickerSettings[segmentElement.dataset.seg] = button.dataset.v;
    syncSegmentButtons();
    applyTickerStyles();
    updateJsonPreview();
  };
});

querySelector('#bxa-langs').onclick = event => {
  const button = event.target.closest('button');
  if (!button) {
    return;
  }

  tickerState.lang = button.dataset.l;
  document.querySelectorAll('#bxa-langs button').forEach(languageButton => {
    languageButton.setAttribute('aria-pressed', languageButton === button);
  });
  querySelector('#bxa-label').value = tickerState.label[tickerState.lang] || '';
  list();
  render();
};

querySelector('#bxa-label').value = tickerState.label[tickerState.lang] || '';
querySelector('#bxa-label').oninput = event => {
  tickerState.label[tickerState.lang] = event.target.value;
  render();
};

const messageListElement = querySelector('#bxa-list');
messageListElement.addEventListener('input', event => {
  const itemRow = event.target.closest('.bxa-it');
  const fieldName = event.target.dataset.f;
  if (!itemRow || !fieldName) {
    return;
  }

  const item = tickerState.items[+itemRow.dataset.i];
  if (fieldName === 'text') {
    item.text[tickerState.lang] = event.target.value;
  } else if (fieldName === 'active') {
    item.active = event.target.checked;
  } else {
    item[fieldName] = event.target.value;
  }
  render();
});

messageListElement.addEventListener('click', event => {
  if (event.target.dataset.del !== undefined) {
    tickerState.items.splice(+event.target.closest('.bxa-it').dataset.i, 1);
    list();
    render();
  }
});

let draggedItemIndex = null;
messageListElement.addEventListener('dragstart', event => {
  const itemRow = event.target.closest('.bxa-it');
  draggedItemIndex = +itemRow.dataset.i;
  event.dataTransfer.setDragImage(itemRow, 10, 10);
});

messageListElement.addEventListener('dragover', event => {
  event.preventDefault();
  messageListElement.querySelectorAll('.bxa-over').forEach(itemRow => itemRow.classList.remove('bxa-over'));
  event.target.closest('.bxa-it')?.classList.add('bxa-over');
});

messageListElement.addEventListener('drop', event => {
  event.preventDefault();
  const itemRow = event.target.closest('.bxa-it');
  if (itemRow && draggedItemIndex !== null) {
    moveItem(draggedItemIndex, +itemRow.dataset.i);
  }
  draggedItemIndex = null;
});

messageListElement.addEventListener('dragend', () => {
  messageListElement.querySelectorAll('.bxa-over').forEach(itemRow => itemRow.classList.remove('bxa-over'));
});

messageListElement.addEventListener('keydown', event => {
  const dragHandle = event.target.closest('.bxa-hd');
  if (!dragHandle || !['ArrowUp', 'ArrowDown'].includes(event.key)) {
    return;
  }

  event.preventDefault();
  const currentIndex = +dragHandle.closest('.bxa-it').dataset.i;
  const targetIndex = event.key === 'ArrowUp' ? currentIndex - 1 : currentIndex + 1;
  moveItem(currentIndex, targetIndex);
  messageListElement.querySelector(`.bxa-it[data-i="${targetIndex}"] .bxa-hd`)?.focus();
});

querySelector('#bxa-add').onclick = () => {
  tickerState.items.push({
    active: true,
    link: '',
    from: '',
    to: '',
    text: Object.fromEntries(availableLanguages.map(languageCode => [languageCode, '']))
  });
  list();
  render();
  messageListElement.querySelector('.bxa-it:last-child [data-f=text]').focus();
};

const saveButton = querySelector('#bxa-save');
const toastElement = querySelector('#bxa-toast');

function showToast(message, isError) {
  toastElement.textContent = message;
  toastElement.classList.toggle('bxa-toast-error', Boolean(isError));
  toastElement.classList.add('on');
  setTimeout(() => toastElement.classList.remove('on'), 2600);
}

saveButton.onclick = async () => {
  const csrfFieldName = saveButton.dataset.csrfName;
  const csrfFieldValue = saveButton.dataset.csrfValue;

  const body = new URLSearchParams();
  body.set('bx_ticker_action', 'save');
  body.set('bx_ticker_payload', JSON.stringify(buildPayload()));
  if (csrfFieldName) {
    body.set(csrfFieldName, csrfFieldValue);
  }

  saveButton.disabled = true;
  const originalLabel = saveButton.textContent;
  saveButton.textContent = I18n.bx_txt_saving || originalLabel;

  try {
    const response = await fetch(location.href, {
      method: 'POST',
      headers: { 'Content-Type': 'application/x-www-form-urlencoded; charset=UTF-8' },
      body: body.toString()
    });

    let result = null;
    try {
      result = await response.json();
    } catch (parseError) {
      result = null;
    }

    if (response.ok && result && result.success) {
      showToast(I18n.bx_txt_saved || 'OK', false);
    } else {
      showToast(I18n.bx_txt_save_error || 'Error', true);
    }
  } catch (networkError) {
    showToast(I18n.bx_txt_save_error || 'Error', true);
  } finally {
    saveButton.disabled = false;
    saveButton.textContent = originalLabel;
  }
};

addEventListener('resize', applyTickerStyles);
list();
render();

/* Dauer nach dem Laden der Schriften neu berechnen (Textbreite ändert sich) */
if (document.fonts && document.fonts.ready) {
  document.fonts.ready.then(applyTickerStyles);
}
});
</script>
<?php
}