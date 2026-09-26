<?php
defined( '_VALID_XTC' ) or die( 'Direct Access to this location is not allowed.' );

define('MODULE_BX_MODIFIED_TICKER_TEXT_TITLE', 'BX Modified Ticker');

define('MODULE_BX_MODIFIED_TICKER_TEXT_DESC','
<details class="bxac-card">
  <summary class="bxac-summary" style="list-style: none;">
    <span class="bxac-arrow">▸</span>
    ' . xtc_image(DIR_WS_ICONS.'heading/bx_modified_ticker.png', 'BX Modified Ticker') . '
    <span class="bxac-title">BX Modified Ticker</span>
  </summary>
  <div class="bxac-body">
    <h3 style="margin-top: 0;">Konfigurierbare, mehrsprachige Laufschrift.</h3>
    <p>Ticker-Meldungen und Darstellung werden unter <i>Hilfsprogramme -> BX Modified Ticker</i> gepflegt.</p>
  </div>
</details>');

define('MODULE_BX_MODIFIED_TICKER_BUTTON_MANAGE', 'Ticker verwalten');
define('MODULE_BX_MODIFIED_TICKER_STATUS_TITLE', 'Status');
define('MODULE_BX_MODIFIED_TICKER_STATUS_DESC', 'Ticker im Shop anzeigen');
define('MODULE_BX_MODIFIED_TICKER_VERSION_TITLE', 'Version');
define('MODULE_BX_MODIFIED_TICKER_VERSION_DESC', 'Aktuelle Version des Moduls');
define('MODULE_BX_MODIFIED_TICKER_CONFIG_ID_TITLE', 'Konfigurations-ID');
define('MODULE_BX_MODIFIED_TICKER_CONFIG_ID_DESC', 'ID der Modulkategorie in der Konfiguration');

define('BX_TICKER_SPEED_TITLE', 'Tempo (px/s)');
define('BX_TICKER_SPEED_DESC', 'Geschwindigkeit in Pixel pro Sekunde. Bleibt bei jeder Textmenge gleich.');
define('BX_TICKER_GAP_TITLE', 'Abstand (rem)');
define('BX_TICKER_GAP_DESC', 'Abstand zwischen den Meldungen.');
define('BX_TICKER_FONT_SIZE_TITLE', 'Schriftgröße (px)');
define('BX_TICKER_FONT_SIZE_DESC', 'Schriftgröße des Tickers.');
define('BX_TICKER_PADDING_TITLE', 'Höhe (rem)');
define('BX_TICKER_PADDING_DESC', 'Vertikaler Innenabstand des Tickers.');
define('BX_TICKER_FADE_TITLE', 'Kantenbreite (rem)');
define('BX_TICKER_FADE_DESC', 'Breite der weichen Ausblendung an den Rändern. 0 = aus.');
define('BX_TICKER_PAUSE_HOVER_TITLE', 'Pause bei Hover');
define('BX_TICKER_PAUSE_HOVER_DESC', 'Ticker pausiert, wenn die Maus darüber steht oder er Tastaturfokus hat.');
define('BX_TICKER_DIRECTION_TITLE', 'Richtung');
define('BX_TICKER_DIRECTION_DESC', 'left oder right.');
define('BX_TICKER_POSITION_TITLE', 'Position');
define('BX_TICKER_POSITION_DESC', 'top oder bottom.');
define('BX_TICKER_FONT_FAMILY_TITLE', 'Schriftart');
define('BX_TICKER_FONT_FAMILY_DESC', 'inherit (Shop-Schrift), system, serif oder mono.');
define('BX_TICKER_FONT_WEIGHT_TITLE', 'Schriftschnitt');
define('BX_TICKER_FONT_WEIGHT_DESC', '400 (normal), 600 (halbfett) oder 700 (fett).');
define('BX_TICKER_COLOR_BG_TITLE', 'Farbe Hintergrund');
define('BX_TICKER_COLOR_BG_DESC', 'Hex-Wert, z. B. #ffffff.');
define('BX_TICKER_COLOR_TEXT_TITLE', 'Farbe Text');
define('BX_TICKER_COLOR_TEXT_DESC', 'Hex-Wert, z. B. #14181f.');
define('BX_TICKER_COLOR_ACCENT_TITLE', 'Farbe Akzent');
define('BX_TICKER_COLOR_ACCENT_DESC', 'Hex-Wert für den Punkt am Label.');
define('BX_TICKER_COLOR_LINE_TITLE', 'Farbe Linien');
define('BX_TICKER_COLOR_LINE_DESC', 'Hex-Wert für Rahmenlinien.');
