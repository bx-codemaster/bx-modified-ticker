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
    <h3 style="margin-top: 0;">Configurable multilingual news ticker.</h3>
    <p>Messages and appearance are managed under <i>Tools -> BX Modified Ticker</i>.</p>
  </div>
</details>');
define('MODULE_BX_MODIFIED_TICKER_BUTTON_MANAGE', 'Manage ticker');
define('MODULE_BX_MODIFIED_TICKER_STATUS_TITLE', 'Status');
define('MODULE_BX_MODIFIED_TICKER_STATUS_DESC', 'Show ticker in the shop');
define('BX_TICKER_SPEED_TITLE', 'Speed (px/s)');
define('BX_TICKER_SPEED_DESC', 'Speed in pixels per second. Stays constant regardless of text length.');
define('BX_TICKER_GAP_TITLE', 'Gap (rem)');
define('BX_TICKER_GAP_DESC', 'Space between messages.');
define('BX_TICKER_FONT_SIZE_TITLE', 'Font size (px)');
define('BX_TICKER_FONT_SIZE_DESC', 'Font size of the ticker.');
define('BX_TICKER_PADDING_TITLE', 'Height (rem)');
define('BX_TICKER_PADDING_DESC', 'Vertical padding of the ticker.');
define('BX_TICKER_FADE_TITLE', 'Edge fade (rem)');
define('BX_TICKER_FADE_DESC', 'Width of the soft fade at both edges. 0 = off.');
define('BX_TICKER_PAUSE_HOVER_TITLE', 'Pause on hover');
define('BX_TICKER_PAUSE_HOVER_DESC', 'Ticker pauses on mouse hover or keyboard focus.');
define('BX_TICKER_DIRECTION_TITLE', 'Direction');
define('BX_TICKER_DIRECTION_DESC', 'left or right.');
define('BX_TICKER_POSITION_TITLE', 'Position');
define('BX_TICKER_POSITION_DESC', 'top or bottom.');
define('BX_TICKER_FONT_FAMILY_TITLE', 'Font family');
define('BX_TICKER_FONT_FAMILY_DESC', 'inherit (shop font), system, serif or mono.');
define('BX_TICKER_FONT_WEIGHT_TITLE', 'Font weight');
define('BX_TICKER_FONT_WEIGHT_DESC', '400 (normal), 600 (semibold) or 700 (bold).');
define('BX_TICKER_COLOR_BG_TITLE', 'Background color');
define('BX_TICKER_COLOR_BG_DESC', 'Hex value, e.g. #ffffff.');
define('BX_TICKER_COLOR_TEXT_TITLE', 'Text color');
define('BX_TICKER_COLOR_TEXT_DESC', 'Hex value, e.g. #14181f.');
define('BX_TICKER_COLOR_ACCENT_TITLE', 'Accent color');
define('BX_TICKER_COLOR_ACCENT_DESC', 'Hex value for the label dot.');
define('BX_TICKER_COLOR_LINE_TITLE', 'Line color');
define('BX_TICKER_COLOR_LINE_DESC', 'Hex value for borders.');
