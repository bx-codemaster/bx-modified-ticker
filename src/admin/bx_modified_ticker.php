<?php
/** --------------------------------------------------------------
 * $Id: admin/bx_modified_ticker.php 16358 2026-09-24 12:00:00Z benax $
 * modified eCommerce Shopsoftware
 * http://www.modified-shop.org
 * 
 * Copyright (c) 2009 - 2013 [www.modified-shop.org]
 * --------------------------------------------------------------
 * based on:
 * (c) 2000-2001 The Exchange Project  (earlier name of osCommerce)
 * (c) 2002-2003 osCommercecoding standards www.oscommerce.com
 * (c) 2003	nextcommerce www.nextcommerce.org
 * (c) 2003 XT-Commerce
 * 
 * Released under the GNU General Public License
 * --------------------------------------------------------------
 */

require ('includes/application_top.php');

// ==========================================================================
// Sprachen: eine Abfrage für Tabs, Laden und Speichern
// ==========================================================================
$languages = array();
$languages_query = xtc_db_query("SELECT languages_id, name, code
                                    FROM " . TABLE_LANGUAGES . "
                                   WHERE status = '1' OR status_admin = '1'
                                   ORDER BY sort_order");
while ($language_row = xtc_db_fetch_array($languages_query)) {
  $languages[] = $language_row;
}
$bx_language_codes = array_column($languages, 'code');
$default_language = (defined('DEFAULT_LANGUAGE') && in_array(DEFAULT_LANGUAGE, $bx_language_codes, true))
  ? DEFAULT_LANGUAGE
  : ($bx_language_codes[0] ?? 'de');

// ==========================================================================
// Hilfsfunktionen: Validierung/Sanitizing
// ==========================================================================
if (!function_exists('bx_ticker_json_response')) {
  function bx_ticker_json_response($success, $message = '', $extra = array()) {
    header('Content-Type: application/json; charset=utf-8');
    echo json_encode(array_merge(array('success' => $success, 'message' => $message), $extra));
    exit;
  }

  function bx_ticker_clamp_number($value, $min, $max, $default, $decimals = 2) {
    if (!is_numeric($value)) {
      return $default;
    }
    $value = (float)$value;
    $value = max($min, min($max, $value));
    return round($value, $decimals);
  }

  function bx_ticker_hex_color($value, $default) {
    $value = trim((string)$value);
    return preg_match('/^#[0-9a-fA-F]{6}$/', $value) ? strtolower($value) : $default;
  }

  function bx_ticker_choice($value, array $allowed, $default) {
    return in_array($value, $allowed, true) ? $value : $default;
  }

  function bx_ticker_sanitize_text($value, $max_length = 255) {
    $value = trim(strip_tags((string)$value));
    return function_exists('mb_substr') ? mb_substr($value, 0, $max_length) : substr($value, 0, $max_length);
  }

  function bx_ticker_sanitize_link($value) {
    $value = trim((string)$value);
    if ($value === '') {
      return '';
    }
    // Nur relative Links oder http(s) zulassen, kein javascript:/data: etc.
    return preg_match('#^(https?://|/)#i', $value) ? substr($value, 0, 255) : '';
  }

  function bx_ticker_sanitize_date($value) {
    $value = trim((string)$value);
    if ($value === '' || !preg_match('/^(\d{4})-(\d{2})-(\d{2})$/', $value, $m)) {
      return null;
    }
    return checkdate((int)$m[2], (int)$m[3], (int)$m[1]) ? $value : null;
  }
}

// ==========================================================================
// Speichern (POST) - läuft vor jeder HTML-Ausgabe
// ==========================================================================
if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['bx_ticker_action'] ?? '') === 'save') {
  // Der globale CSRF-Schutz (inc/csrf_token.inc.php) hat den Request beim
  // Laden von application_top.php bereits geprüft. War der Token falsch
  // oder fehlte er, wurde $_POST bereits geleert.
  if (empty($_POST) || !isset($_POST['bx_ticker_payload'])) {
    bx_ticker_json_response(false, 'csrf_or_payload_missing');
  }

  $payload = json_decode((string)$_POST['bx_ticker_payload'], true);
  if (!is_array($payload) || json_last_error() !== JSON_ERROR_NONE) {
    bx_ticker_json_response(false, 'invalid_json');
  }

  $configuration_input = is_array($payload['configuration'] ?? null) ? $payload['configuration'] : array();
  $label_input         = is_array($payload['label'] ?? null) ? $payload['label'] : array();
  $items_input          = is_array($payload['bx_ticker_items'] ?? null) ? array_slice($payload['bx_ticker_items'], 0, 100) : array();

  // ---- Konfigurationswerte validieren (Whitelists/Bereiche wie im Admin-UI) ----
  $config_values = array(
    'BX_TICKER_SPEED'        => bx_ticker_clamp_number($configuration_input['BX_TICKER_SPEED'] ?? null, 20, 240, 60, 0),
    'BX_TICKER_GAP'          => bx_ticker_clamp_number($configuration_input['BX_TICKER_GAP'] ?? null, 1, 8, 3, 2),
    'BX_TICKER_FONT_SIZE'    => bx_ticker_clamp_number($configuration_input['BX_TICKER_FONT_SIZE'] ?? null, 12, 24, 15, 0),
    'BX_TICKER_PADDING'      => bx_ticker_clamp_number($configuration_input['BX_TICKER_PADDING'] ?? null, .4, 1.6, .8, 2),
    'BX_TICKER_FADE'         => bx_ticker_clamp_number($configuration_input['BX_TICKER_FADE'] ?? null, 0, 8, 4, 2),
    'BX_TICKER_FONT_FAMILY'  => bx_ticker_choice($configuration_input['BX_TICKER_FONT_FAMILY'] ?? null, array('inherit', 'system', 'serif', 'mono'), 'inherit'),
    'BX_TICKER_FONT_WEIGHT'  => bx_ticker_choice((string)($configuration_input['BX_TICKER_FONT_WEIGHT'] ?? ''), array('400', '600', '700'), '400'),
    'BX_TICKER_PAUSE_HOVER'  => (($configuration_input['BX_TICKER_PAUSE_HOVER'] ?? '') === 'True') ? 'True' : 'False',
    'BX_TICKER_DIRECTION'    => bx_ticker_choice($configuration_input['BX_TICKER_DIRECTION'] ?? null, array('left', 'right'), 'left'),
    'BX_TICKER_POSITION'     => bx_ticker_choice($configuration_input['BX_TICKER_POSITION'] ?? null, array('top', 'bottom'), 'top'),
    'BX_TICKER_TYPE'         => bx_ticker_choice($configuration_input['BX_TICKER_TYPE'] ?? null, array('standard', 'smarty'), 'standard'),
    'BX_TICKER_COLOR_BG'     => bx_ticker_hex_color($configuration_input['BX_TICKER_COLOR_BG'] ?? null, '#ffffff'),
    'BX_TICKER_COLOR_TEXT'   => bx_ticker_hex_color($configuration_input['BX_TICKER_COLOR_TEXT'] ?? null, '#14181f'),
    'BX_TICKER_COLOR_ACCENT' => bx_ticker_hex_color($configuration_input['BX_TICKER_COLOR_ACCENT'] ?? null, '#e5322d'),
    'BX_TICKER_COLOR_LINE'   => bx_ticker_hex_color($configuration_input['BX_TICKER_COLOR_LINE'] ?? null, '#dfe4ea'),
  );

  // ---- Label je Sprache validieren ----
  $sanitized_label = array();
  foreach ($languages as $language) {
    $sanitized_label[$language['code']] = bx_ticker_sanitize_text($label_input[$language['code']] ?? '', 64);
  }

  // ---- Meldungen validieren ----
  $sanitized_items = array();
  foreach ($items_input as $item_input) {
    if (!is_array($item_input)) {
      continue;
    }
    $item_text = is_array($item_input['text'] ?? null) ? $item_input['text'] : array();
    $sanitized_text = array();
    foreach ($languages as $language) {
      // Bewusst auch leer speichern, wenn in dieser Sprache nichts eingetragen wurde.
      $sanitized_text[$language['code']] = bx_ticker_sanitize_text($item_text[$language['code']] ?? '', 255);
    }

    $sanitized_items[] = array(
      'active' => !empty($item_input['active']),
      'link'   => bx_ticker_sanitize_link($item_input['link'] ?? ''),
      'from'   => bx_ticker_sanitize_date($item_input['from'] ?? ''),
      'to'     => bx_ticker_sanitize_date($item_input['to'] ?? ''),
      'text'   => $sanitized_text,
    );
  }

  // ---- Schreiben (in einer Transaktion) ----
  xtc_db_query("BEGIN TRANSACTION");
  try {
    foreach ($config_values as $config_key => $config_value) {
      xtc_db_query("UPDATE " . TABLE_CONFIGURATION . "
                       SET configuration_value = '" . xtc_db_input($config_value) . "', last_modified = now()
                     WHERE configuration_key = '" . xtc_db_input($config_key) . "'");
    }

    foreach ($languages as $language) {
      $label_text = $sanitized_label[$language['code']];
      xtc_db_query("UPDATE bx_ticker_label
                       SET bx_ticker_label = '" . xtc_db_input($label_text) . "'
                     WHERE bx_ticker_language_id = '" . (int)$language['languages_id'] . "'");
      if (xtc_db_affected_rows() === 0) {
        xtc_db_query("INSERT INTO bx_ticker_label (bx_ticker_language_id, bx_ticker_label)
                           VALUES ('" . (int)$language['languages_id'] . "', '" . xtc_db_input($label_text) . "')");
      }
    }

    // Meldungen komplett ersetzen (Reihenfolge kommt 1:1 aus dem Array).
    // ON DELETE CASCADE räumt bx_ticker_items_description automatisch mit auf.
    xtc_db_query("DELETE FROM bx_ticker_items");

    foreach ($sanitized_items as $sort_index => $item) {
      xtc_db_query("INSERT INTO bx_ticker_items (bx_ticker_sort_order, bx_ticker_status, bx_ticker_link, bx_ticker_date_from, bx_ticker_date_to)
                         VALUES ('" . (int)($sort_index + 1) . "',
                                 '" . ($item['active'] ? 1 : 0) . "',
                                 '" . xtc_db_input($item['link']) . "',
                                 " . ($item['from'] !== null ? "'" . xtc_db_input($item['from']) . "'" : "NULL") . ",
                                 " . ($item['to'] !== null ? "'" . xtc_db_input($item['to']) . "'" : "NULL") . ")");
      $new_item_id = xtc_db_insert_id();

      foreach ($languages as $language) {
        $item_text = $item['text'][$language['code']];
        xtc_db_query("INSERT INTO bx_ticker_items_description (bx_ticker_items_id, bx_ticker_language_id, bx_ticker_text)
                           VALUES ('" . (int)$new_item_id . "', '" . (int)$language['languages_id'] . "', '" . xtc_db_input($item_text) . "')");
      }
    }

    xtc_db_query("COMMIT");
  } catch (\Throwable $exception) {
    xtc_db_query("ROLLBACK");
    trigger_error('bx_modified_ticker save failed: ' . $exception->getMessage(), E_USER_WARNING);
    bx_ticker_json_response(false, 'database_error');
  }

  bx_ticker_json_response(true, 'saved');
}

// ==========================================================================
// Aktuelle Daten für die Admin-Vorschau laden (GET)
// ==========================================================================
$bx_settings = array(
  'speed'  => (float) (defined('BX_TICKER_SPEED') ? BX_TICKER_SPEED : 60),
  'gap'    => (float) (defined('BX_TICKER_GAP') ? BX_TICKER_GAP : 3),
  'size'   => (float) (defined('BX_TICKER_FONT_SIZE') ? BX_TICKER_FONT_SIZE : 15),
  'pad'    => (float) (defined('BX_TICKER_PADDING') ? BX_TICKER_PADDING : .8),
  'fade'   => (float) (defined('BX_TICKER_FADE') ? BX_TICKER_FADE : 4),
  'pause'  => (defined('BX_TICKER_PAUSE_HOVER') ? BX_TICKER_PAUSE_HOVER : 'True') === 'True',
  'dir'    => defined('BX_TICKER_DIRECTION') ? BX_TICKER_DIRECTION : 'left',
  'pos'    => defined('BX_TICKER_POSITION') ? BX_TICKER_POSITION : 'top',
  'type'   => defined('BX_TICKER_TYPE') ? BX_TICKER_TYPE : 'standard',
  'font'   => defined('BX_TICKER_FONT_FAMILY') ? BX_TICKER_FONT_FAMILY : 'inherit',
  'weight' => defined('BX_TICKER_FONT_WEIGHT') ? BX_TICKER_FONT_WEIGHT : '400',
  'bg'     => defined('BX_TICKER_COLOR_BG') ? BX_TICKER_COLOR_BG : '#ffffff',
  'ink'    => defined('BX_TICKER_COLOR_TEXT') ? BX_TICKER_COLOR_TEXT : '#14181f',
  'accent' => defined('BX_TICKER_COLOR_ACCENT') ? BX_TICKER_COLOR_ACCENT : '#e5322d',
  'line'   => defined('BX_TICKER_COLOR_LINE') ? BX_TICKER_COLOR_LINE : '#dfe4ea',
);

$bx_label = array_fill_keys($bx_language_codes, '');
$label_query = xtc_db_query("SELECT l.code, bl.bx_ticker_label
                                FROM bx_ticker_label bl
                                JOIN " . TABLE_LANGUAGES . " l ON l.languages_id = bl.bx_ticker_language_id");
while ($label_row = xtc_db_fetch_array($label_query)) {
  $bx_label[$label_row['code']] = $label_row['bx_ticker_label'];
}

$bx_items = array();
$items_query = xtc_db_query("SELECT * FROM bx_ticker_items ORDER BY bx_ticker_sort_order, bx_ticker_items_id");
while ($item_row = xtc_db_fetch_array($items_query)) {
  $bx_items[$item_row['bx_ticker_items_id']] = array(
    'active' => $item_row['bx_ticker_status'] == 1,
    'link'   => $item_row['bx_ticker_link'],
    'from'   => $item_row['bx_ticker_date_from'] ?: '',
    'to'     => $item_row['bx_ticker_date_to'] ?: '',
    'text'   => array_fill_keys($bx_language_codes, ''),
  );
}
if (!empty($bx_items)) {
  $desc_query = xtc_db_query("SELECT bid.bx_ticker_items_id, l.code, bid.bx_ticker_text
                                 FROM bx_ticker_items_description bid
                                 JOIN " . TABLE_LANGUAGES . " l ON l.languages_id = bid.bx_ticker_language_id
                                WHERE bid.bx_ticker_items_id IN (" . implode(',', array_map('intval', array_keys($bx_items))) . ")");
  while ($desc_row = xtc_db_fetch_array($desc_query)) {
    if (isset($bx_items[$desc_row['bx_ticker_items_id']])) {
      $bx_items[$desc_row['bx_ticker_items_id']]['text'][$desc_row['code']] = $desc_row['bx_ticker_text'];
    }
  }
}
$bx_items = array_values($bx_items);

// CSRF-Feld für den Speichern-Request per fetch() (nur falls aktiv)
$bx_csrf_field_name  = (defined('CSRF_TOKEN_SYSTEM') && CSRF_TOKEN_SYSTEM === 'true' && isset($_SESSION['CSRFName'])) ? $_SESSION['CSRFName'] : '';
$bx_csrf_field_value = (defined('CSRF_TOKEN_SYSTEM') && CSRF_TOKEN_SYSTEM === 'true' && isset($_SESSION['CSRFToken'])) ? $_SESSION['CSRFToken'] : '';

require_once (DIR_WS_INCLUDES.'head.php');

$messageStack->output();
?>

</head>
<!-- header //-->
<?php require(DIR_WS_INCLUDES.'header.php'); ?>

<!-- header_eof //-->
<!-- body //-->
<table class="tableBody">
  <tr>
    <?php //left_navigation
    if (USE_ADMIN_TOP_MENU == 'false') {
      echo '<td class="columnLeft2">'.PHP_EOL;
      echo '<!-- left_navigation //-->'.PHP_EOL;
      require_once(DIR_WS_INCLUDES.'column_left.php');
      echo '<!-- left_navigation eof //-->'.PHP_EOL;
      echo '</td>'.PHP_EOL;
    }
    ?>
    <!-- body_text //-->
    <td class="boxCenter">
      
      <div class="pageHeadingImage" style="width: 65px;">
        <?php echo xtc_image(DIR_WS_ICONS.'heading/bx_modified_ticker.png', MODULE_BX_MODIFIED_TICKER, '', '', 'style="height:100%;"'); ?>
      </div>
      <div class="pageHeading pdg2 flt-l">
        <?php echo MODULE_BX_MODIFIED_TICKER; ?>
        <div class="main pdg2"><?php echo MODULE_BX_MODIFIED_TICKER_SUBTITLE; ?></div> 
      </div>
      <div class="clear"></div>

      <div class="bx-grid-full">

        <section class="bx-main-content bxa">

          <div class="bx-headboard bxa-headboard">
            <strong><?php echo MODULE_BX_MODIFIED_TICKER; ?></strong>
            <div class="bxa-tools">
              <div class="bxa-seg" id="bxa-langs" role="group" aria-label="Sprache">
                <?php
                  // $languages kommt bereits von oben (eine Abfrage für Tabs, Laden, Speichern).
                  // Vorausgewählt wird die UI-Sprache des Admins, mit Fallback auf die Shop-Standardsprache.
                  $activeLanguage = $_SESSION['language_code'] ?? $default_language;
                  if (!in_array($activeLanguage, $bx_language_codes, true)) {
                    $activeLanguage = $default_language;
                  }

                  foreach ($languages as $language) {
                    $code = $language['code'];
                    $name = $language['name'];
                    $ariaPressed = $code === $activeLanguage ? 'true' : 'false';
                    echo '<button data-l="' . htmlspecialchars($code, ENT_QUOTES, 'UTF-8') . '" aria-pressed="' . $ariaPressed . '">' . htmlspecialchars($name, ENT_QUOTES, 'UTF-8') . '</button>' . PHP_EOL;
                  }
                ?>
              </div>
              <button class="bxa-btn" id="bxa-save" data-csrf-name="<?php echo htmlspecialchars($bx_csrf_field_name, ENT_QUOTES, 'UTF-8'); ?>" data-csrf-value="<?php echo htmlspecialchars($bx_csrf_field_value, ENT_QUOTES, 'UTF-8'); ?>"><?php echo MODULE_BX_MODIFIED_TICKER_SAVE; ?></button>
            </div>
          </div>

          <article class="bx-panel" style="margin: 0; padding: 0;">

            <div class="bxa-body">
            <div class="bxa-stage">
              <div class="bxa-frame" id="bxa-frame">
                <div class="bx-ticker" id="bxa-tk"></div>
                <div class="bxa-page"><?php echo MODULE_BX_MODIFIED_TICKER_PREVIEW; ?></div>
              </div>
            </div>

            <div class="bxa-cols">
              <section>
                <h2><?php echo MODULE_BX_MODIFIED_TICKER_PRESENTATION; ?></h2>
                <div id="bxa-sliders"></div>
                
                <div class="bxa-row bxa-plain">
                  <span><?php echo MODULE_BX_MODIFIED_TICKER_HOVER; ?></span>
                  <input class="bxa-sw" type="checkbox" data-k="pause">
                </div>
                
                <h3><?php echo MODULE_BX_MODIFIED_TICKER_TYPE; ?></h3>
                <div class="bxa-seg" data-seg="type">
                  <button data-v="standard"><?php echo MODULE_BX_MODIFIED_TICKER_STANDARD; ?></button>
                  <button data-v="smarty"><?php echo MODULE_BX_MODIFIED_TICKER_SMARTY; ?></button>
                </div>
                <p style="margin-top: 0;"><small><?php echo MODULE_BX_MODIFIED_TICKER_SMARTY_HINT; ?></small></p>

                <h3><?php echo MODULE_BX_MODIFIED_TICKER_POS; ?></h3>
                <div class="bxa-seg" data-seg="pos">
                  <button data-v="top"><?php echo MODULE_BX_MODIFIED_TICKER_TOP; ?></button>
                  <button data-v="bottom"><?php echo MODULE_BX_MODIFIED_TICKER_BOTTOM; ?></button>
                </div>
                <p style="margin-top: 0;"><small><?php echo MODULE_BX_MODIFIED_TICKER_POS_HINT; ?></small></p>

                <h3><?php echo MODULE_BX_MODIFIED_TICKER_FONT; ?></h3>
                <div class="bxa-seg" data-seg="font">
                  <button data-v="inherit"><?php echo MODULE_BX_MODIFIED_TICKER_FONT_SHOP; ?></button>
                  <button data-v="system"><?php echo MODULE_BX_MODIFIED_TICKER_FONT_SANS; ?></button>
                  <button data-v="serif"><?php echo MODULE_BX_MODIFIED_TICKER_FONT_SERIF; ?></button>
                  <button data-v="mono"><?php echo MODULE_BX_MODIFIED_TICKER_FONT_MONO; ?></button>
                </div>

                <h3><?php echo MODULE_BX_MODIFIED_TICKER_WEIGHT; ?></h3>
                <div class="bxa-seg" data-seg="weight">
                  <button data-v="400"><?php echo MODULE_BX_MODIFIED_TICKER_WEIGHT_NORMAL; ?></button>
                  <button data-v="600"><?php echo MODULE_BX_MODIFIED_TICKER_WEIGHT_SEMIBOLD; ?></button>
                  <button data-v="700"><?php echo MODULE_BX_MODIFIED_TICKER_WEIGHT_BOLD; ?></button>
                </div>

                <h3><?php echo MODULE_BX_MODIFIED_TICKER_COLORS; ?></h3>
                <div id="bxa-colors"></div>

                <h3><?php echo MODULE_BX_MODIFIED_TICKER_LABEL; ?></h3>
                <input class="bxa-txt" id="bxa-label" aria-label="Label-Text">
                <p class="bxa-hint"><?php echo MODULE_BX_MODIFIED_TICKER_HINT; ?></p>
              </section>

              <section>
                <h2><?php echo MODULE_BX_MODIFIED_TICKER_MESSAGE; ?></h2>
                <div id="bxa-list"></div>
                <button class="bxa-btn bxa-ghost" id="bxa-add"><?php echo MODULE_BX_MODIFIED_TICKER_ADD_MESSAGE; ?></button>
                <p class="bxa-hint"><?php echo MODULE_BX_MODIFIED_TICKER_HINT_ORDER; ?></p>
                <details><summary><?php echo MODULE_BX_MODIFIED_TICKER_HINT_JSON; ?></summary><pre id="bxa-json"></pre></details>
              </section>
            </div>
            <div id="bxa-toast" role="status">Gespeichert (Prototyp)</div>
            </div> <!-- .bxa-body -->

          </article> <!-- bx-panel -->

        </section>

      </div> <!-- bx-grid-full -->

    </td> <!-- end boxCenter //-->
  </tr>
</table>
<!-- body_eof //-->
<!-- footer //-->
<?php require(DIR_WS_INCLUDES.'footer.php'); ?>
<!-- footer_eof //-->

</body>
</html>
<?php require(DIR_WS_INCLUDES.'application_bottom.php'); ?>