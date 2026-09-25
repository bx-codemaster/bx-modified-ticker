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
                  $languages = array();
                  $languages_query = xtc_db_query("SELECT languages_id, name, code
                                                    FROM " . TABLE_LANGUAGES . "
                                                    WHERE status = '1' OR status_admin = '1'
                                                    ORDER BY sort_order");
                  while ($language_row = xtc_db_fetch_array($languages_query)) {
                    $languages[] = $language_row;
                  }
                  
                  $activeLanguage = $_SESSION['language_code'] ?? 'de';

                  foreach ($languages as $language) {
                    $code = $language['code'];
                    $name = $language['name'];
                    $ariaPressed = $code === $activeLanguage ? 'true' : 'false';
                    echo '<button data-l="' . htmlspecialchars($code, ENT_QUOTES, 'UTF-8') . '" aria-pressed="' . $ariaPressed . '">' . htmlspecialchars($name, ENT_QUOTES, 'UTF-8') . '</button>' . PHP_EOL;
                  }
                ?>
              </div>
              <button class="bxa-btn" id="bxa-save"><?php echo MODULE_BX_MODIFIED_TICKER_SAVE; ?></button>
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

                <h3><?php echo MODULE_BX_MODIFIED_TICKER_DIR; ?></h3>
                <div class="bxa-seg" data-seg="dir">
                  <button data-v="left"><?php echo MODULE_BX_MODIFIED_TICKER_LEFT; ?></button>
                  <button data-v="right"><?php echo MODULE_BX_MODIFIED_TICKER_RIGHT; ?></button>
                </div>

                <h3><?php echo MODULE_BX_MODIFIED_TICKER_POS; ?></h3>
                <div class="bxa-seg" data-seg="pos">
                  <button data-v="top"><?php echo MODULE_BX_MODIFIED_TICKER_TOP; ?>
                  </button><button data-v="bottom"><?php echo MODULE_BX_MODIFIED_TICKER_BOTTOM; ?></button>
                </div>

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