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

        <section class="bx-main-content">

          <div class="bx-headboard">
            <strong><?php echo MODULE_BX_MODIFIED_TICKER; ?></strong>
          </div>

          <article class="bx-panel">

<div class="bxa">
<header class="bxa-top">
  <div class="bxa-seg" id="bxa-langs" role="group" aria-label="Sprache">
    <button data-l="de" aria-pressed="true">Deutsch</button>
    <button data-l="en" aria-pressed="false">English</button>
  </div>
  <button class="bxa-btn" id="bxa-save">Speichern</button>
</header>

<div class="bxa-stage">
  <div class="bxa-frame" id="bxa-frame">
    <div class="bx-ticker" id="bxa-tk"></div>
    <div class="bxa-page"><b>Vorschau</b>Änderungen wirken sofort. Über den Tabs wechselst du die Sprache der Texte.</div>
  </div>
</div>

<div class="bxa-cols">
  <section>
    <h2>Darstellung</h2>
    <div id="bxa-sliders"></div>
    <div class="bxa-row bxa-plain"><span>Pause bei Hover</span><input class="bxa-sw" type="checkbox" data-k="pause"></div>
    <h3>Richtung</h3>
    <div class="bxa-seg" data-seg="dir"><button data-v="left">Nach links</button><button data-v="right">Nach rechts</button></div>
    <h3>Position</h3>
    <div class="bxa-seg" data-seg="pos"><button data-v="top">Oben</button><button data-v="bottom">Unten</button></div>
    <h3>Farben</h3>
    <div id="bxa-colors"></div>
    <h3>Label</h3>
    <input class="bxa-txt" id="bxa-label" aria-label="Label-Text">
    <p class="bxa-hint">Reduzierte Bewegung wird immer respektiert und ist nicht abschaltbar.</p>
  </section>

  <section>
    <h2>Meldungen</h2>
    <div id="bxa-list"></div>
    <button class="bxa-btn bxa-ghost" id="bxa-add">+ Meldung hinzufügen</button>
    <p class="bxa-hint">Reihenfolge per Ziehen am Griff oder mit Pfeiltasten. Fehlt ein Text in der gewählten Sprache, wird der deutsche verwendet.</p>
    <details><summary>Werte, wie das Modul sie speichern würde</summary><pre id="bxa-json"></pre></details>
  </section>
</div>
<div id="bxa-toast" role="status">Gespeichert (Prototyp)</div>
</div> <!-- .bxa -->


















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