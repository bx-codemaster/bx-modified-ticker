<?php
/**
 * BX Modified Ticker - Frontend Ausgabe
 *
 * Zeigt aktive Meldungen im gültigen Zeitraum in der aktuellen Sitzungssprache,
 * mit Rückfall auf die Shop-Standardsprache, falls in der aktuellen Sprache
 * kein Text gepflegt ist. Läuft auf jeder Frontend-Seite (header_body).
 *
 * BX_TICKER_TYPE 'standard' gibt das Markup automatisch hier aus. Bei 'smarty'
 * erfolgt keine automatische Ausgabe, stattdessen wird nur die Smarty-Template-
 * Variable {$bx_ticker} global befüllt, die der User selbst im Template einbaut.
 *
 * @package    BX Modified Ticker
 * @subpackage Frontend Ausgabe
 * @version    1.0.0
 * @author     benax
 * @license    GNU GPL v2.0
 */

if (defined('MODULE_BX_MODIFIED_TICKER_STATUS') && (string)MODULE_BX_MODIFIED_TICKER_STATUS === 'True') {

  $bx_current_languages_id = (int)($_SESSION['languages_id'] ?? 1);
  $bx_default_languages_id = $bx_current_languages_id;

  if (defined('DEFAULT_LANGUAGE') && ($_SESSION['language_code'] ?? '') !== DEFAULT_LANGUAGE) {
    $bx_default_lang_query = xtc_db_query("SELECT languages_id
                                              FROM " . TABLE_LANGUAGES . "
                                             WHERE code = '" . xtc_db_input(DEFAULT_LANGUAGE) . "'");
    if ($bx_default_lang_row = xtc_db_fetch_array($bx_default_lang_query)) {
      $bx_default_languages_id = (int)$bx_default_lang_row['languages_id'];
    }
  }

  // ---- Label (mit Fallback auf Standardsprache) ----
  $bx_ticker_label = '';
  $bx_label_query = xtc_db_query("SELECT bx_ticker_label
                                     FROM bx_ticker_label
                                    WHERE bx_ticker_language_id = '" . $bx_current_languages_id . "'");
  if ($bx_label_row = xtc_db_fetch_array($bx_label_query)) {
    $bx_ticker_label = $bx_label_row['bx_ticker_label'];
  }
  if ($bx_ticker_label === '' && $bx_default_languages_id !== $bx_current_languages_id) {
    $bx_label_query = xtc_db_query("SELECT bx_ticker_label
                                       FROM bx_ticker_label
                                      WHERE bx_ticker_language_id = '" . $bx_default_languages_id . "'");
    if ($bx_label_row = xtc_db_fetch_array($bx_label_query)) {
      $bx_ticker_label = $bx_label_row['bx_ticker_label'];
    }
  }

  // ---- Aktive Meldungen im gültigen Zeitraum ----
  $bx_ticker_items = array();
  $bx_items_query = xtc_db_query("SELECT bti.bx_ticker_items_id, bti.bx_ticker_link,
                                          cur.bx_ticker_text AS text_current,
                                          def.bx_ticker_text AS text_default
                                     FROM bx_ticker_items bti
                                     LEFT JOIN bx_ticker_items_description cur
                                            ON cur.bx_ticker_items_id = bti.bx_ticker_items_id
                                           AND cur.bx_ticker_language_id = '" . $bx_current_languages_id . "'
                                     LEFT JOIN bx_ticker_items_description def
                                            ON def.bx_ticker_items_id = bti.bx_ticker_items_id
                                           AND def.bx_ticker_language_id = '" . $bx_default_languages_id . "'
                                    WHERE bti.bx_ticker_status = 1
                                      AND (bti.bx_ticker_date_from IS NULL OR bti.bx_ticker_date_from <= CURDATE())
                                      AND (bti.bx_ticker_date_to IS NULL OR bti.bx_ticker_date_to >= CURDATE())
                                    ORDER BY bti.bx_ticker_sort_order, bti.bx_ticker_items_id");
  while ($bx_item_row = xtc_db_fetch_array($bx_items_query)) {
    $bx_text = ($bx_item_row['text_current'] !== null && $bx_item_row['text_current'] !== '')
      ? $bx_item_row['text_current']
      : (string)$bx_item_row['text_default'];

    // Meldungen ohne Text (weder aktuelle noch Standardsprache gepflegt) werden übersprungen.
    if ($bx_text === '') {
      continue;
    }

    $bx_ticker_items[] = array(
      'text' => $bx_text,
      'link' => $bx_item_row['bx_ticker_link'],
    );
  }

  // Ohne Meldungen keinen leeren Ticker-Balken anzeigen.
  if (!empty($bx_ticker_items)) {
    $bx_ticker_reverse  = (defined('BX_TICKER_DIRECTION') && BX_TICKER_DIRECTION === 'right');
    $bx_ticker_pause    = !(defined('BX_TICKER_PAUSE_HOVER') && BX_TICKER_PAUSE_HOVER === 'False');
    $bx_ticker_bottom   = ( (defined('BX_TICKER_POSITION') && BX_TICKER_POSITION === 'bottom') && (defined('BX_TICKER_TYPE') && BX_TICKER_TYPE === 'standard') );
    $bx_ticker_classes  = 'bx-ticker' . ($bx_ticker_reverse ? ' rev' : '') . ($bx_ticker_pause ? ' pause' : '') . ($bx_ticker_bottom ? ' bx-ticker-fixed-bottom' : '');

    $bx_render_group = function ($bx_hidden = false) use ($bx_ticker_items) {
      echo '<ul class="bx-ticker-group"' . ($bx_hidden ? ' aria-hidden="true"' : '') . '>';
      foreach ($bx_ticker_items as $bx_item) {
        $bx_text_html = htmlspecialchars($bx_item['text'], ENT_QUOTES, 'UTF-8');
        if ($bx_item['link'] !== '') {
          echo '<li><a href="' . htmlspecialchars($bx_item['link'], ENT_QUOTES, 'UTF-8') . '"' . ($bx_hidden ? ' tabindex="-1"' : '') . '>' . $bx_text_html . '</a></li>';
        } else {
          echo '<li>' . $bx_text_html . '</li>';
        }
      }
      echo '</ul>';
    };

    // Bei Typ "smarty" wird nur die Template-Variable {$bx_ticker} befüllt, keine automatische Ausgabe.
    $bx_ticker_smarty_mode = defined('BX_TICKER_TYPE') && BX_TICKER_TYPE === 'smarty';
    if ($bx_ticker_smarty_mode) {
      ob_start();
    }
?>
<div class="<?php echo $bx_ticker_classes; ?>" id="bx-ticker">
  <div class="bx-ticker-label"><?php echo htmlspecialchars($bx_ticker_label, ENT_QUOTES, 'UTF-8'); ?></div>
  <div class="bx-ticker-viewport">
    <?php $bx_render_group(false); ?>
    <?php // Zweite, identische Gruppe für die nahtlose Endlosschleife; für Screenreader ausgeblendet. ?>
    <?php $bx_render_group(true); ?>
  </div>
</div>
<?php if ($bx_ticker_bottom) { ?>
<div id="bx-ticker-spacer" aria-hidden="true"></div>
<?php
    }

    if ($bx_ticker_smarty_mode) {
      (new Smarty())->assignGlobal('bx_modified_ticker', ob_get_clean());
    }
  }
}
