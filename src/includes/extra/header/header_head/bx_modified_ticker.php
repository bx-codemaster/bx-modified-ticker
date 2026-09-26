<?php
/**
 * BX Modified Ticker - Frontend CSS
 *
 * Gibt die Ticker-Styles im <head> aus. Die Regeln unter .bx-ticker* sind
 * bewusst identisch zur Admin-Vorschau (admin/includes/extra/css), nur dass
 * die CSS-Variablen hier direkt aus den BX_TICKER_*-Konfigurationswerten
 * kommen statt aus dem Admin-JS.
 *
 * @package    BX Modified Ticker
 * @subpackage Frontend CSS
 * @version    1.0.0
 * @author     benax
 * @license    GNU GPL v2.0
 */

if (defined('MODULE_BX_MODIFIED_TICKER_STATUS') && (string)MODULE_BX_MODIFIED_TICKER_STATUS === 'True') {
  $bx_font_stacks = array(
    'inherit' => 'inherit',
    'system'  => 'system-ui, -apple-system, "Segoe UI", Roboto, sans-serif',
    'serif'   => 'Georgia, "Times New Roman", serif',
    'mono'    => 'ui-monospace, "SF Mono", Consolas, monospace',
  );
  $bx_font_key    = defined('BX_TICKER_FONT_FAMILY') ? BX_TICKER_FONT_FAMILY : 'inherit';
  $bx_font_family = $bx_font_stacks[$bx_font_key] ?? $bx_font_stacks['inherit'];
?>
<style>
:root {
  --bx-bg: <?php echo (defined('BX_TICKER_COLOR_BG') ? BX_TICKER_COLOR_BG : '#ffffff'); ?>;
  --bx-ink: <?php echo (defined('BX_TICKER_COLOR_TEXT') ? BX_TICKER_COLOR_TEXT : '#14181f'); ?>;
  --bx-accent: <?php echo (defined('BX_TICKER_COLOR_ACCENT') ? BX_TICKER_COLOR_ACCENT : '#af417e'); ?>;
  --bx-accent-secondary: <?php echo (defined('BX_TICKER_COLOR_ACCENT_SECONDARY') ? BX_TICKER_COLOR_ACCENT_SECONDARY : '#21e773'); ?>;
  --bx-line: <?php echo (defined('BX_TICKER_COLOR_LINE') ? BX_TICKER_COLOR_LINE : '#dfe4ea'); ?>;
  --bx-gap: <?php echo (defined('BX_TICKER_GAP') ? BX_TICKER_GAP : 3); ?>rem;
  --bx-size: <?php echo (defined('BX_TICKER_FONT_SIZE') ? BX_TICKER_FONT_SIZE : 15); ?>px;
  --bx-pad: <?php echo (defined('BX_TICKER_PADDING') ? BX_TICKER_PADDING : .8); ?>rem;
  --bx-fade: <?php echo (defined('BX_TICKER_FADE') ? BX_TICKER_FADE : 4); ?>rem;
  --bx-font: <?php echo $bx_font_family; ?>;
  --bx-weight: <?php echo (defined('BX_TICKER_FONT_WEIGHT') ? BX_TICKER_FONT_WEIGHT : 400); ?>;
  --bx-dur: 30s;
}

.bx-ticker {
  display: flex;
  align-items: stretch;
  background: var(--bx-bg);
  color: var(--bx-ink);
  border-block: 1px solid var(--bx-line);
  font-size: var(--bx-size);
  font-family: var(--bx-font, inherit);
  font-weight: var(--bx-weight, 400);
}

.bx-ticker a,
.bx-ticker a:hover,
.bx-ticker a:link {
  font-size: var(--bx-size);
  font-family: var(--bx-font, inherit);
  font-weight: var(--bx-weight, 400);
}
.bx-ticker a:hover {
  color: var(--bx-accent-secondary) !important;
}

.bx-ticker-label {
  display: flex;
  align-items: center;
  gap: .5rem;
  padding-inline: 1rem;
  font-weight: 700;
  border-right: 1px solid var(--bx-line);
  position: relative;
  z-index: 1;
  background: var(--bx-bg);
}

.bx-ticker-label::before {
  content: "";
  inline-size: .55em;
  block-size: .55em;
  border-radius: 50%;
  background: var(--bx-accent);
  animation: bx-ticker-pulse 2s ease-in-out infinite;
}

.bx-ticker-viewport {
  flex: 1;
  min-inline-size: 0;
  display: flex;
  overflow: hidden;
  gap: var(--bx-gap);
  padding-block: var(--bx-pad);
  mask-image: linear-gradient(
    to right,
    transparent,
    #000 var(--bx-fade),
    #000 calc(100% - var(--bx-fade)),
    transparent
  );
}

.bx-ticker-group {
  display: flex;
  flex-shrink: 0;
  min-inline-size: 100%;
  justify-content: space-around;
  gap: var(--bx-gap);
  margin: 0;
  padding: 0;
  list-style: none;
  white-space: nowrap;
  animation: bx-ticker-scroll var(--bx-dur, 30s) linear infinite;
  will-change: transform;
}

.bx-ticker.rev .bx-ticker-group {
  animation-direction: reverse;
}

.bx-ticker.pause:hover .bx-ticker-group {
  animation-play-state: paused;
}

.bx-ticker a {
  color: inherit;
  text-decoration: none;
}

@keyframes bx-ticker-scroll {
  to {
    transform: translateX(calc(-100% - var(--bx-gap)));
  }
}

@keyframes bx-ticker-pulse {
  50% {
    opacity: .35;
  }
}

@media (prefers-reduced-motion: reduce) {
  .bx-ticker-group,
  .bx-ticker-label::before {
    animation: none;
  }

  .bx-ticker-viewport {
    overflow-x: auto;
    mask-image: none;
  }
}

/* Position "unten": als fixierter Balken am Bildschirmrand, da header_body
   im Dokument immer an derselben Stelle (Seitenanfang) ausgegeben wird und
   ein reines order/margin wie in der Admin-Vorschau hier nicht greifen kann. */
.bx-ticker-fixed-bottom {
  position: fixed;
  left: 0;
  right: 0;
  bottom: 0;
  z-index: 1000;
  padding-bottom: env(safe-area-inset-bottom, 0px);
}
</style>
<script>
(function () {
  var ticker = document.getElementById('bx-ticker');
  if (!ticker) {
    return;
  }
  var group = ticker.querySelector('.bx-ticker-group');
  var speed = <?php echo (float) (defined('BX_TICKER_SPEED') ? BX_TICKER_SPEED : 60); ?>; // px/s
  var gapRem = <?php echo (float) (defined('BX_TICKER_GAP') ? BX_TICKER_GAP : 3); ?>;

  function applyDuration() {
    if (!group) {
      return;
    }
    var gapPx = gapRem * parseFloat(getComputedStyle(document.documentElement).fontSize);
    var distance = group.offsetWidth + gapPx;
    var duration = Math.max(4, distance / speed);
    ticker.style.setProperty('--bx-dur', duration.toFixed(1) + 's');
  }

  function applySpacerHeight() {
    var spacer = document.getElementById('bx-ticker-spacer');
    if (spacer) {
      spacer.style.height = ticker.offsetHeight + 'px';
    }
  }

  applyDuration();
  applySpacerHeight();
  window.addEventListener('resize', applyDuration);
  window.addEventListener('resize', applySpacerHeight);
  if (document.fonts && document.fonts.ready) {
    document.fonts.ready.then(applyDuration);
    document.fonts.ready.then(applySpacerHeight);
  }
})();
</script>
<?php
}
