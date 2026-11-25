<?php
/**
* BX Modified Ticker System Module - Installation & Configuration
*
* System module for installation and management of the BX Modified Ticker module in modified eCommerce.
* Creates configuration keys, admin access right and database tables for a multilingual, configurable ticker.
*
* In addition, this module provides a self-contained update mechanism based on
* GitHub Releases. On the admin module overview and edit page, the latest release
* is checked at configurable intervals and an update is offered when its semantic
* version is newer than the installed module version. Release checks are cached;
* GitHub and external module servers are not contacted on every request.
*
* Before an update is installed, the updater performs the following checks:
* - The release tag and package name must contain a valid semantic version.
* - The archive must be the exact module asset belonging to that version.
* - The SHA-256 checksum of the complete ZIP archive must match.
* - The package must contain a BX module manifest.
* - Every manifest file must match its recorded SHA-256 checksum.
* - ZIP paths, symbolic links, file counts and archive sizes must be safe.
*
* File changes are protected by an exclusive update lock and a backup-based
* rollback plan. Files removed from a newer package are deleted when they were
* registered by the preceding installed manifest. Update execution is performed
* only through the modified admin interface, where the form is protected by the
* shop's standard CSRF token mechanism.
*
* Release archives are created by the GitHub Actions workflow shipped with the
* module. They contain the files below the configured source directory at archive
* root level plus a generated .bx-module-manifest.json file. The updater is
* independent of MMLC and can therefore also be used by manually installed
* modules.
*
* PHP 8.0 or newer and the cURL and ZipArchive extensions are required for the
* online update functionality. Normal module operation remains independent from
* GitHub once the release status has been cached.
*
* @package    BX Modified Ticker
* @subpackage System Module
* @version    1.3.0
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
  public array $properties = array();
  private ?BxGithubModuleUpdater $githubUpdater = null;
  private ?array $updateStatus = null;

  // Spalte in admin_access (Recht auf die Admin-Seite bx_ticker.php)
  private const ACCESS_COLUMN = 'bx_modified_ticker';

  // Tabellen in Lösch-Reihenfolge (abhängige zuerst)
  private const TABLES = array('bx_ticker_items_description', 'bx_ticker_label', 'bx_ticker_items');

  // Einstellungen: key => array(Standardwert, set_function)
  private const SETTINGS = array(
    'BX_TICKER_SPEED'        => array('60',      ''),
    'BX_TICKER_MOBILE_FACTOR'     => array('1.5',  ''),
    'BX_TICKER_MOBILE_BREAKPOINT' => array('768',  ''),
    'BX_TICKER_GAP'          => array('3',       ''),
    'BX_TICKER_FONT_SIZE'    => array('15',      ''),
    'BX_TICKER_PADDING'      => array('0.8',     ''),
    'BX_TICKER_FADE'         => array('4',       ''),
    'BX_TICKER_FONT_FAMILY'  => array('inherit', "xtc_cfg_select_option(array('inherit', 'system', 'serif', 'mono'), "),
    'BX_TICKER_FONT_WEIGHT'  => array('400',     "xtc_cfg_select_option(array('400', '600', '700'), "),
    'BX_TICKER_PAUSE_HOVER'  => array('True',    "xtc_cfg_select_option(array('True', 'False'), "),
    'BX_TICKER_DIRECTION'    => array('left',    "xtc_cfg_select_option(array('left', 'right'), "),
    'BX_TICKER_POSITION'     => array('top',     "xtc_cfg_select_option(array('top', 'bottom'), "),
    'BX_TICKER_TYPE'         => array('standard', "xtc_cfg_select_option(array('standard', 'smarty'), "),
    'BX_TICKER_COLOR_BG'     => array('#ffffff', ''),
    'BX_TICKER_COLOR_TEXT'   => array('#14181f', ''),
    'BX_TICKER_COLOR_ACCENT' => array('#e5322d', ''),
    'BX_TICKER_COLOR_LINE'   => array('#dfe4ea', ''),
  );

  public function __construct() {
    // Bootstrap-Logik für Übergangsphase: zentraler Updater bevorzugt, eigene Kopie nur als Fallback
    if (!class_exists(BxGithubModuleUpdater::class)) {
      $bootstrapPath = DIR_FS_ADMIN . 'includes/classes/BX/Github/BxGithubModuleUpdater.php';
      if (!is_file($bootstrapPath)) {
        throw new RuntimeException('bx-module-updater ist nicht installiert.');
      }
      require_once $bootstrapPath;
    } elseif (
      defined(BxGithubModuleUpdater::class . '::VERSION')
      && version_compare(BxGithubModuleUpdater::VERSION, '1.1.0', '>')
    ) {
      // Zentrale Version ist neuer -> eigene Kopie NICHT laden (wird gar nicht mehr ausgeliefert)
    } else {
      // Fallback: eigene Kopie laden (für Installationen ohne zentralen Updater)
      require_once DIR_FS_ADMIN . 'includes/classes/BX/Github/BxGithubModuleUpdater.php';
    }

    $migrationFile = DIR_FS_ADMIN
      . 'includes/classes/BX/Github/BxModifiedTickerMigrations.php';
    if (is_file($migrationFile)) {
      require_once $migrationFile;
    }

    $this->code        = 'bx_modified_ticker';
    $this->version     = '1.3.0';
    $this->title       = defined('MODULE_BX_MODIFIED_TICKER_TEXT_TITLE') ? MODULE_BX_MODIFIED_TICKER_TEXT_TITLE : '';
    $this->description = defined('MODULE_BX_MODIFIED_TICKER_TEXT_DESC') ? MODULE_BX_MODIFIED_TICKER_TEXT_DESC : '';
    $this->sort_order  = defined('MODULE_BX_MODIFIED_TICKER_SORT_ORDER') ? (int)MODULE_BX_MODIFIED_TICKER_SORT_ORDER : 0;
    $this->enabled     = (defined('MODULE_BX_MODIFIED_TICKER_STATUS') && MODULE_BX_MODIFIED_TICKER_STATUS == 'True');
    $this->development_status = 'd';

    if (
      defined('MODULE_BX_MODIFIED_TICKER_VERSION')
      && class_exists(BxModifiedTickerMigrations::class)
    ) {
      try {
        BxModifiedTickerMigrations::migratePending();
      } catch (Throwable $migrationException) {
        global $messageStack;
        if (isset($messageStack) && is_object($messageStack)) {
          $messageStack->add_session(
            'BX Modified Ticker Datenbankmigration fehlgeschlagen: '
              . htmlspecialchars($migrationException->getMessage(), ENT_QUOTES, 'UTF-8'),
            'error'
          );
        }
      }
    } elseif (defined('MODULE_BX_MODIFIED_TICKER_VERSION')) {
      global $messageStack;
      if (isset($messageStack) && is_object($messageStack)) {
        $messageStack->add_session(
          'BX Modified Ticker Migrationsklasse fehlt. Keine Datenbankmigration wurde ausgeführt.',
          'error'
        );
      }
    }

    if (defined('MODULE_BX_MODIFIED_TICKER_VERSION')) {
      $this->updateStatus = $this->getGithubUpdater()->checkForUpdate(
        ($_GET['bx_updater_refresh'] ?? '') === '1'
      );
      $this->properties['button_update'] = $this->createRefreshButton();
      if (
        $this->updateStatus['has_update']
        && !empty($this->updateStatus['zip_url'])
        && !empty($this->updateStatus['sha256'])
      ) {
        $this->properties['button_update'] = $this->createUpdateButton(
          $this->updateStatus['latest_version'],
          true
        ) . $this->properties['button_update'];
      }
    }
  }

  public function process($file): bool {
    if (($_POST['bx_updater_action'] ?? '') === 'install') {
      global $messageStack;
      $result = $this->getGithubUpdater()->installLatestUpdate();
      $messageStack->add_session($result['message'], $result['ok'] ? 'success' : 'error');
      xtc_redirect(xtc_href_link(
        FILENAME_MODULE_EXPORT,
        'set=system&module=' . $this->code . '&action=edit'
      ));
      return true;
    }
    return true;
  }

  public function display(): array {
    $manage = defined('MODULE_BX_MODIFIED_TICKER_BUTTON_MANAGE') ? MODULE_BX_MODIFIED_TICKER_BUTTON_MANAGE : 'Ticker';
    $txt = $this->createUpdateNotice() . '<br /><div align="center">' . xtc_button(BUTTON_SAVE) .
      xtc_button_link(BUTTON_CANCEL, xtc_href_link(FILENAME_MODULE_EXPORT, 'set=' . $_GET['set'] . '&module=' . $this->code)) .
      xtc_button_link($manage, xtc_href_link('bx_modified_ticker.php')) . '</div>';
    return array('text' => $txt);
  }

  public function check(): bool {
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

  public function install(): void {
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
      bx_ticker_link_target varchar(10) NOT NULL DEFAULT '_self' COMMENT 'Linkziel: gleiches oder neues Fenster',
      bx_ticker_date_from date DEFAULT NULL COMMENT 'Anzeige ab (NULL = sofort)',
      bx_ticker_date_to date DEFAULT NULL COMMENT 'Anzeige bis inkl. (NULL = unbegrenzt)',
      bx_ticker_date_added datetime NOT NULL DEFAULT CURRENT_TIMESTAMP COMMENT 'Erstellungsdatum',
      PRIMARY KEY (bx_ticker_items_id),
      KEY idx_bx_ticker_items_active (bx_ticker_status, bx_ticker_sort_order)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Ticker-Meldungen (sprachunabhaengige Daten)'");

    if (!$this->column_exists('bx_ticker_items', 'bx_ticker_link_target')) {
      xtc_db_query("ALTER TABLE bx_ticker_items ADD bx_ticker_link_target varchar(10) NOT NULL DEFAULT '_self' AFTER bx_ticker_link");
    }

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

    // Schema-Version setzen
    $schemaSortQuery = xtc_db_query(
      "SELECT COALESCE(MAX(sort_order), 0) AS max_sort_order
       FROM " . TABLE_CONFIGURATION . "
       WHERE configuration_group_id = " . (int)$group_id
    );
    $schemaSort = $schemaSortQuery ? xtc_db_fetch_array($schemaSortQuery) : array();
    $schemaSortOrder = ((int) ($schemaSort['max_sort_order'] ?? 0)) + 1;

    xtc_db_query("INSERT INTO " . TABLE_CONFIGURATION . "
                  (configuration_key, configuration_value, configuration_group_id, sort_order, date_added)
                  VALUES (
                    'MODULE_BX_MODIFIED_TICKER_SCHEMA_VERSION',
                    '1.3.0',
                    " . (int)$group_id . ",
                    " . $schemaSortOrder . ",
                    NOW()
                  )");
  }

  public function remove(): void {
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

  public function keys(): array {
    $key = array(
        'MODULE_BX_MODIFIED_TICKER_STATUS',
        'MODULE_BX_MODIFIED_TICKER_VERSION',
        'MODULE_BX_MODIFIED_TICKER_CONFIG_ID',
      );
    return $key;
  }

  public function keys2(): array {
    $keys = array_keys(self::SETTINGS);
    return $keys;
  }

  public function custom(): void {
    if (($_POST['bx_updater_action'] ?? '') === 'install') {
      global $messageStack;
      $result = $this->getGithubUpdater()->installLatestUpdate();
      $messageStack->add_session($result['message'], $result['ok'] ? 'success' : 'error');
      return;
    }

    if (($_GET['delete'] ?? '') !== 'true') {
      return;
    }

    global $messageStack;
    $result = true;
    $dirs_and_files = array();

    $dirs_and_files[] = DIR_FS_ADMIN . 'images/icons/heading/bx_modified_ticker.png';
    $dirs_and_files[] = DIR_FS_ADMIN . 'includes/extra/css/bx_modified_ticker.php';
    $dirs_and_files[] = DIR_FS_ADMIN . 'includes/extra/javascript/bx_modified_ticker.php';
    $dirs_and_files[] = DIR_FS_ADMIN . 'includes/extra/filenames/bx_modified_ticker.php';
    $dirs_and_files[] = DIR_FS_ADMIN . 'includes/extra/menu/bx_modified_ticker.php';
    $dirs_and_files[] = DIR_FS_ADMIN . 'includes/extra/functions/bx_modified_ticker.php';
    $dirs_and_files[] = DIR_FS_ADMIN . 'bx_modified_ticker.php';
    $dirs_and_files[] = DIR_FS_ADMIN . 'includes/modules/system/bx_modified_ticker.php';
    $dirs_and_files[] = DIR_FS_CATALOG . 'cache/bx_github_updater/bx-modified-ticker';
    $dirs_and_files[] = DIR_FS_CATALOG . 'lang/english/extra/admin/bx_modified_ticker.php';
    $dirs_and_files[] = DIR_FS_CATALOG . 'lang/english/modules/system/bx_modified_ticker.php';
    $dirs_and_files[] = DIR_FS_CATALOG . 'lang/german/extra/admin/bx_modified_ticker.php';
    $dirs_and_files[] = DIR_FS_CATALOG . 'lang/german/modules/system/bx_modified_ticker.php';
    $dirs_and_files[] = DIR_FS_CATALOG . 'includes/extra/header/header_body/bx_modified_ticker.php';
    $dirs_and_files[] = DIR_FS_CATALOG . 'includes/extra/header/header_head/bx_modified_ticker.php';

    foreach ($dirs_and_files as $dir_or_file) {
      if (!$this->secureDelete($dir_or_file)) {
        $messageStack->add_session($dir_or_file . ' konnte nicht gelöscht werden.', 'error');
        $result = false;
      }
    }

    if ($result === true) {
      $messageStack->add_session('BX Modified Ticker erfolgreich entfernt.', 'success');
    } else {
      $messageStack->add_session('Entfernung unvollständig.', 'error');
    }

    $this->secureDelete(DIR_FS_ADMIN . 'includes/modules/system/bx_modified_ticker.php');
    xtc_redirect(xtc_href_link(FILENAME_MODULE_EXPORT, 'set=system'));
  }

  private function getGithubUpdater(): BxGithubModuleUpdater
  {
    if ($this->githubUpdater === null) {
      $this->githubUpdater = new BxGithubModuleUpdater(array(
        'owner'           => 'bx-codemaster',
        'repo'            => 'bx-modified-ticker',
        'module_name'     => 'bx-modified-ticker',
        'module_prefix'   => 'BX_MODIFIED_TICKER',
        'admin_dir'       => DIR_ADMIN,
        'public_key'      => 'xG6mrEKk81LUxWyTIpFodXsQbp2k9rKXhPX5dUPnhdc=',
        'allowed_paths'   => array(
          'admin/includes/extra/css',
          'admin/includes/extra/javascript',
          'admin/includes/extra/filenames',
          'admin/includes/extra/menu',
          'admin/includes/extra/functions',
          'admin/includes/modules/system',
          'admin/images/icons/heading',
          'admin',
          'includes/extra/header/header_body',
          'includes/extra/header/header_head',
          'lang/english/extra/admin',
          'lang/english/modules/system',
          'lang/german/extra/admin',
          'lang/german/modules/system',
        ),
        'current_version' => version_compare(
          $this->version,
          MODULE_BX_MODIFIED_TICKER_VERSION,
          '>'
        ) ? $this->version : MODULE_BX_MODIFIED_TICKER_VERSION,
      ));
    }
    return $this->githubUpdater;
  }

  private function createUpdateButton(string $version, bool $standaloneForm): string
  {
    $formStart   = '';
    $formEnd     = '';
    $actionField = ' name="bx_updater_action" value="install"';
    if ($standaloneForm) {
      $formStart = xtc_draw_form(
        'bx_modified_ticker_update',
        FILENAME_MODULE_EXPORT,
        'set=system&module=' . $this->code . '&action=custom'
      );
      $formEnd = '</form>';
      $actionField = '';
    }

    return $formStart
      . ($standaloneForm ? xtc_draw_hidden_field('bx_updater_action', 'install') : '')
      . '<button type="submit"' . $actionField . ' class="button bx-update" onclick="return confirm(\''
      . sprintf(
        MODULE_BX_MODIFIED_TICKER_UPDATE_CONFIRM,
        htmlspecialchars($version, ENT_QUOTES, 'UTF-8')
      )
      . '\');">'
      . sprintf(
        MODULE_BX_MODIFIED_TICKER_UPDATE_BUTTON,
        htmlspecialchars($version, ENT_QUOTES, 'UTF-8')
      )
      . '</button>' . $formEnd . '<br>';
  }

  private function createRefreshButton(): string
  {
    $label = defined('MODULE_BX_MODIFIED_TICKER_UPDATE_CHECK')
      ? MODULE_BX_MODIFIED_TICKER_UPDATE_CHECK
      : 'Jetzt prüfen';
    return '<a class="button btnbox" href="'
      . xtc_href_link(
        FILENAME_MODULE_EXPORT,
        xtc_get_all_get_params(array('bx_updater_refresh'))
          . 'bx_updater_refresh=1'
      )
      . '" style="width: auto; display: inline-block;">' . htmlspecialchars($label, ENT_QUOTES, 'UTF-8') . '</a>';
  }

  private function createUpdateNotice(): string
  {
    $refreshLink = $this->createRefreshButton();

    if (
      $this->updateStatus === null
      || !$this->updateStatus['has_update']
      || empty($this->updateStatus['latest_version'])
    ) {
      return '<div class="messageStackInfo txta-c">'
        . $refreshLink
        . '</div>';
    }

    return '<div class="messageStackWarning txta-c">'
      . sprintf(
        MODULE_BX_MODIFIED_TICKER_UPDATE_AVAILABLE,
        htmlspecialchars($this->updateStatus['latest_version'], ENT_QUOTES, 'UTF-8')
      )
      . '<br>'
      . (
        !empty($this->updateStatus['zip_url']) && !empty($this->updateStatus['sha256'])
          ? $this->createUpdateButton($this->updateStatus['latest_version'], false)
          : ' ' . MODULE_BX_MODIFIED_TICKER_UPDATE_NOT_INSTALLABLE
      )
      . $refreshLink
      . '</div>';
  }

  private function secureDelete(string $path): bool {
    if (!file_exists($path)) {
      return true;
    }
    $realPath  = realpath($path);
    $adminRoot = realpath(DIR_FS_ADMIN);
    if (empty($realPath) || empty($adminRoot)) {
      return false;
    }
    if ($realPath === $adminRoot || $realPath === DIRECTORY_SEPARATOR) {
      return false;
    }
    if (!is_writable($realPath)) {
      return false;
    }
    if (!is_dir($realPath) || is_link($realPath)) {
      return unlink($realPath);
    }
    try {
      $iterator = new RecursiveIteratorIterator(
        new RecursiveDirectoryIterator($realPath, FilesystemIterator::SKIP_DOTS),
        RecursiveIteratorIterator::CHILD_FIRST
      );
      foreach ($iterator as $item) {
        $itemPath = $item->getRealPath();
        if (!is_writable($itemPath)) {
          return false;
        }
        if ($item->isDir() && !$item->isLink()) {
          if (!rmdir($itemPath)) {
            return false;
          }
        } else {
          if (!unlink($itemPath)) {
            return false;
          }
        }
      }
    } catch (UnexpectedValueException $e) {
      return false;
    }
    return rmdir($realPath);
  }

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