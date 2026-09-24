<?php
/**
* BX Modified Ticker System Module - Installation & Configuration
*
* System module for installation and management of the BX Modified Ticker module in modified eCommerce.
* Creates configuration keys, admin access right and database tables for a multilingual, configurable ticker.
*
* @package    BX Modified Ticker
* @subpackage System Module
* @version    1.0.0
* @author     benax
* @license    GNU General Public License v2.0
*/

defined( '_VALID_XTC' ) or die( 'Direct Access to this location is not allowed.' );

class bx_modified_ticker {
  public string $code;
  public string $version;
  public string $development_status;
  public string $title;
  public string $description;
  public int $sort_order;
  public bool $enabled;
  public bool $_check;

  // Spalte in admin_access (Recht auf die Admin-Seite bx_ticker.php)
  private const ACCESS_COLUMN = 'bx_modified_ticker';

  // Tabellen in Lösch-Reihenfolge (abhängige zuerst)
  private const TABLES = array('bx_ticker_items_description', 'bx_ticker_label', 'bx_ticker_items');

  // Einstellungen: key => array(Standardwert, set_function)
  private const SETTINGS = array(
    'BX_TICKER_SPEED'        => array('60',      ''),
    'BX_TICKER_GAP'          => array('3',       ''),
    'BX_TICKER_FONT_SIZE'    => array('15',      ''),
    'BX_TICKER_PADDING'      => array('0.8',     ''),
    'BX_TICKER_FADE'         => array('4',       ''),
    'BX_TICKER_PAUSE_HOVER'  => array('True',    "xtc_cfg_select_option(array('True', 'False'), "),
    'BX_TICKER_DIRECTION'    => array('left',    "xtc_cfg_select_option(array('left', 'right'), "),
    'BX_TICKER_POSITION'     => array('top',     "xtc_cfg_select_option(array('top', 'bottom'), "),
    'BX_TICKER_COLOR_BG'     => array('#ffffff', ''),
    'BX_TICKER_COLOR_TEXT'   => array('#14181f', ''),
    'BX_TICKER_COLOR_ACCENT' => array('#e5322d', ''),
    'BX_TICKER_COLOR_LINE'   => array('#dfe4ea', ''),
  );

  function __construct() {
    $this->code        = 'bx_modified_ticker';
    $this->version     = '1.0.0';
    $this->title       = defined('MODULE_BX_MODIFIED_TICKER_TEXT_TITLE') ? MODULE_BX_MODIFIED_TICKER_TEXT_TITLE : '';
    $this->description = defined('MODULE_BX_MODIFIED_TICKER_TEXT_DESC') ? MODULE_BX_MODIFIED_TICKER_TEXT_DESC : '';
    $this->sort_order  = defined('MODULE_BX_MODIFIED_TICKER_SORT_ORDER') ? (int)MODULE_BX_MODIFIED_TICKER_SORT_ORDER : 0;
    $this->enabled     = (defined('MODULE_BX_MODIFIED_TICKER_STATUS') && MODULE_BX_MODIFIED_TICKER_STATUS == 'True');
    $this->development_status = 'd';
  }

  function process($file): bool {
    return true;
  }

  function display(): array {
    $manage = defined('MODULE_BX_MODIFIED_TICKER_BUTTON_MANAGE') ? MODULE_BX_MODIFIED_TICKER_BUTTON_MANAGE : 'Ticker';
    return array('text' => '<br /><div align="center">' . xtc_button(BUTTON_SAVE) .
      xtc_button_link(BUTTON_CANCEL, xtc_href_link(FILENAME_MODULE_EXPORT, 'set=' . $_GET['set'] . '&module=' . $this->code)) .
      xtc_button_link($manage, xtc_href_link('bx_modified_ticker.php')) . '</div>');
  }

  function check(): bool {
    if (!isset($this->_check)) {
      if (defined('MODULE_BX_MODIFIED_TICKER_STATUS')) {
        $this->_check = true;
      } else {
        $check_query = xtc_db_query("SELECT configuration_value
                                       FROM " . TABLE_CONFIGURATION . "
                                      WHERE configuration_key = 'MODULE_BX_MODIFIED_TICKER_STATUS'");
        $this->_check = (bool)xtc_db_num_rows($check_query);
      }
    }
    return $this->_check;
  }

  function install(): void {
    // -----------------------------------------------------------------------------
    // 1. Admin-Recht (nur anlegen, wenn Spalte noch nicht existiert)
    // -----------------------------------------------------------------------------
    if (!$this->column_exists(TABLE_ADMIN_ACCESS, self::ACCESS_COLUMN)) {
      xtc_db_query("ALTER TABLE " . TABLE_ADMIN_ACCESS . " ADD " . self::ACCESS_COLUMN . " TINYINT(1)");
    }
    xtc_db_query("UPDATE " . TABLE_ADMIN_ACCESS . " SET " . self::ACCESS_COLUMN . " = 1");

    // -----------------------------------------------------------------------------
    // 2. Konfigurationsgruppe und -werte
    // -----------------------------------------------------------------------------
    $freeId_query = xtc_db_query("SELECT MIN(configuration_group_id+1) AS id
                                    FROM " . TABLE_CONFIGURATION_GROUP . "
                                   WHERE (configuration_group_id+1) NOT IN
                                     (SELECT configuration_group_id FROM " . TABLE_CONFIGURATION_GROUP . " WHERE configuration_group_id IS NOT NULL)");
    $freeId = xtc_db_fetch_array($freeId_query);

    $freeSort_query = xtc_db_query("SELECT MIN(sort_order+1) AS sort_order
                                      FROM " . TABLE_CONFIGURATION_GROUP . "
                                     WHERE (sort_order+1) NOT IN (SELECT sort_order FROM " . TABLE_CONFIGURATION_GROUP . " WHERE sort_order IS NOT NULL)");
    $freeSort = xtc_db_fetch_array($freeSort_query);
    $group_id = (int)$freeId['id'];

    xtc_db_query("INSERT INTO " . TABLE_CONFIGURATION_GROUP . " (configuration_group_id, configuration_group_title, configuration_group_description, sort_order, visible)
                       VALUES ('" . $group_id . "',
                               '" . xtc_db_input('BX Modified Ticker') . "',
                               '" . xtc_db_input('Settings for the BX Modified Ticker module') . "',
                               '" . (int)$freeSort['sort_order'] . "',
                               1)");

    // Modul-Grundwerte in Gruppe 6 (wie bei allen System-Modulen)
    $module_keys = array(
      'MODULE_BX_MODIFIED_TICKER_STATUS'    => array('True',            "xtc_cfg_select_option(array('True', 'False'), "),
      'MODULE_BX_MODIFIED_TICKER_VERSION'   => array($this->version,    ''),
      'MODULE_BX_MODIFIED_TICKER_CONFIG_ID' => array((string)$group_id, 'bx_configuration_field_version('),
    );
    $sort = 1;
    foreach ($module_keys as $key => $def) {
      $this->insert_config($key, $def[0], 6, $sort++, $def[1]);
    }

    // Ticker-Einstellungen in eigener Gruppe
    $sort = 1;
    foreach (self::SETTINGS as $key => $def) {
      $this->insert_config($key, $def[0], $group_id, $sort++, $def[1]);
    }

    // -----------------------------------------------------------------------------
    // 3. Tabellen
    // -----------------------------------------------------------------------------
    xtc_db_query("CREATE TABLE IF NOT EXISTS bx_ticker_items (
      bx_ticker_items_id int(11) UNSIGNED NOT NULL AUTO_INCREMENT COMMENT 'Primary Key',
      bx_ticker_sort_order int(11) UNSIGNED NOT NULL DEFAULT '0' COMMENT 'Reihenfolge im Ticker',
      bx_ticker_status tinyint(1) UNSIGNED NOT NULL DEFAULT '1' COMMENT 'Aktiv/Inaktiv',
      bx_ticker_link varchar(255) NOT NULL DEFAULT '' COMMENT 'Optionaler Link der Meldung',
      bx_ticker_date_from date DEFAULT NULL COMMENT 'Anzeige ab (NULL = sofort)',
      bx_ticker_date_to date DEFAULT NULL COMMENT 'Anzeige bis inkl. (NULL = unbegrenzt)',
      bx_ticker_date_added datetime NOT NULL DEFAULT CURRENT_TIMESTAMP COMMENT 'Erstellungsdatum',
      PRIMARY KEY (bx_ticker_items_id),
      KEY idx_bx_ticker_items_active (bx_ticker_status, bx_ticker_sort_order)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Ticker-Meldungen (sprachunabhaengige Daten)'");

    xtc_db_query("CREATE TABLE IF NOT EXISTS bx_ticker_items_description (
      bx_ticker_items_id int(11) UNSIGNED NOT NULL COMMENT 'FK zu bx_ticker_items',
      bx_ticker_language_id int(11) UNSIGNED NOT NULL DEFAULT '1' COMMENT 'FK zu languages',
      bx_ticker_text varchar(255) NOT NULL DEFAULT '' COMMENT 'Text der Meldung in dieser Sprache',
      PRIMARY KEY (bx_ticker_items_id, bx_ticker_language_id),
      CONSTRAINT fk_bx_ticker_desc_item FOREIGN KEY (bx_ticker_items_id)
          REFERENCES bx_ticker_items (bx_ticker_items_id) ON DELETE CASCADE
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Mehrsprachige Texte der Ticker-Meldungen'");

    xtc_db_query("CREATE TABLE IF NOT EXISTS bx_ticker_label (
      bx_ticker_language_id int(11) UNSIGNED NOT NULL DEFAULT '1' COMMENT 'FK zu languages',
      bx_ticker_label varchar(64) NOT NULL DEFAULT '' COMMENT 'Label links im Ticker (z.B. Live)',
      PRIMARY KEY (bx_ticker_language_id)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Mehrsprachiges Ticker-Label'");

    // -----------------------------------------------------------------------------
    // 4. Beispieldaten für alle tatsächlich installierten Sprachen
    //    (languages_id ist von Shop zu Shop unterschiedlich)
    // -----------------------------------------------------------------------------
    $labels = array('de' => 'Aktuell', 'en' => 'Live', 'default' => 'Live');
    $items = array(
      array('de' => 'Versandkostenfrei ab 50 Euro',                  'default' => 'Free shipping from 50 euros'),
      array('de' => 'Kostenlose Rücksendung innerhalb von 30 Tagen', 'default' => 'Free returns within 30 days'),
    );

    $languages = array();
    $languages_query = xtc_db_query("SELECT languages_id, code FROM " . TABLE_LANGUAGES);
    while ($language = xtc_db_fetch_array($languages_query)) {
      $languages[] = $language;
    }

    foreach ($languages as $language) {
      $code  = strtolower($language['code']);
      $label = $labels[$code] ?? $labels['default'];
      xtc_db_query("INSERT INTO bx_ticker_label (bx_ticker_language_id, bx_ticker_label)
                         VALUES ('" . (int)$language['languages_id'] . "', '" . xtc_db_input($label) . "')");
    }

    foreach ($items as $position => $texts) {
      xtc_db_query("INSERT INTO bx_ticker_items (bx_ticker_sort_order, bx_ticker_status)
                         VALUES ('" . (int)($position + 1) . "', 1)");
      $item_id = xtc_db_insert_id();

      foreach ($languages as $language) {
        $code = strtolower($language['code']);
        $text = $texts[$code] ?? $texts['default'];
        xtc_db_query("INSERT INTO bx_ticker_items_description (bx_ticker_items_id, bx_ticker_language_id, bx_ticker_text)
                           VALUES ('" . (int)$item_id . "', '" . (int)$language['languages_id'] . "', '" . xtc_db_input($text) . "')");
      }
    }
  }

  function remove(): void {
    if (defined('MODULE_BX_MODIFIED_TICKER_CONFIG_ID')) {
      xtc_db_query("DELETE FROM " . TABLE_CONFIGURATION_GROUP . " WHERE configuration_group_id = '" . (int)MODULE_BX_MODIFIED_TICKER_CONFIG_ID . "'");
    }

    $configuration_keys = array_merge($this->keys(), $this->keys2());
    xtc_db_query("DELETE FROM " . TABLE_CONFIGURATION . " WHERE configuration_key IN ('" . implode("', '", $configuration_keys) . "')");

    if ($this->column_exists(TABLE_ADMIN_ACCESS, self::ACCESS_COLUMN)) {
      xtc_db_query("ALTER TABLE " . TABLE_ADMIN_ACCESS . " DROP " . self::ACCESS_COLUMN);
    }

    foreach (self::TABLES as $table) {
      xtc_db_query("DROP TABLE IF EXISTS " . $table);
    }
  }

  function keys(): array {
    $key = array(
        'MODULE_BX_MODIFIED_TICKER_STATUS',
        'MODULE_BX_MODIFIED_TICKER_VERSION',
        'MODULE_BX_MODIFIED_TICKER_CONFIG_ID',
      );
    return $key;
  }

  function keys2(): array {
    $keys = array_keys(self::SETTINGS);
    return $keys;
  }

  function custom(): void { }

  // ---------------------------------------------------------------------------
  // Hilfsmethoden
  // ---------------------------------------------------------------------------
  private function insert_config(string $key, string $value, int $group_id, int $sort_order, string $set_function): void {
    xtc_db_query("INSERT INTO " . TABLE_CONFIGURATION . " (configuration_key, configuration_value, configuration_group_id, sort_order, last_modified, date_added, use_function, set_function)
                       VALUES ('" . xtc_db_input($key) . "',
                               '" . xtc_db_input($value) . "',
                               '" . (int)$group_id . "',
                               '" . (int)$sort_order . "',
                               NULL, now(), '',
                               '" . xtc_db_input($set_function) . "')");
  }

  private function column_exists(string $table, string $column): bool {
    $query = xtc_db_query("SHOW COLUMNS FROM " . $table . " LIKE '" . xtc_db_input($column) . "'");
    return xtc_db_num_rows($query) > 0;
  }
}
