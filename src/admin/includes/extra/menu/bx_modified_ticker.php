<?php
/**
 * BX Modified Ticker Menu
 */

defined( '_VALID_XTC' ) or die( 'Direct Access to this location is not allowed.' );

switch ($_SESSION['language_code']) {
  case 'de':
    define('MODULE_MODIFIED_TICKER_MENU_TITLE','BX Modified Ticker');
    break;
  default:
    define('MODULE_MODIFIED_TICKER_MENU_TITLE','BX Modified Ticker');
    break;
}

$add_contents[BOX_HEADING_TOOLS][] = array( 
    'admin_access_name' => 'bx_modified_ticker', 
    'filename' 				  => 'bx_modified_ticker.php', 
    'boxname' 				  => MODULE_MODIFIED_TICKER_MENU_TITLE,
    'parameters' 			  => '', 
    'ssl' 					    => ''
  );
