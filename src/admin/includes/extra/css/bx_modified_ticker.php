<?php
/**
 * BX Modified Ticker - CSS Includes
 *
 * Central CSS inclusion point for the Ticker module.
 * Ensures that all necessary CSS files are loaded for the admin interface.
 *
 * @package    BX Modified Ticker
 * @subpackage CSS
 * @version    1.0.0
 * @author     benax
 * @copyright  2006-2026 benax
 * @license    GNU GPL v2.0
 *
 */

defined('_VALID_XTC') or die('Direct Access to this location is not allowed.'); 

if ( defined('MODULE_BX_MODIFIED_TICKER_STATUS') && 
    ((string)MODULE_BX_MODIFIED_TICKER_STATUS === 'True') && 
    basename($_SERVER['PHP_SELF']) == 'bx_modified_ticker.php') {
?>
<style>
/* ==========================================================
   BX Modified Ticker – Admin-CSS
   Alles unterhalb von .bxa gescoped, damit das modified-Admin-UI
   nicht beeinflusst wird. Der Ticker-Block (.bx-ticker*) ist bewusst
   unscoped und wird im Frontend identisch verwendet.
   ========================================================== */
.bxa {
  --bxa-bg: #f4f6f8;
  --bxa-pane: #fff;
  --bxa-ink: #141a22;
  --bxa-mut: #5d6877;
  --bxa-line: #dfe4ea;
  --bxa-acc: #AF417E;
  --bxa-accInk: #fff;
  --bxa-r: 10px;

  /* Standardwerte der Vorschau (JS überschreibt sie auf #bxa-tk) */
  --bx-bg: var(--bxa-bg);
  --bx-ink: var(--bxa-ink);
  --bx-accent: var(--bxa-acc);
  --bx-line: var(--bxa-line);
  --bx-gap: 3rem;
  --bx-size: 15px;
  --bx-pad: .8rem;
  --bx-fade: 4rem;
  --bx-dur: 30s;

}

.bxa .bxa-body {
  margin: 0;
  background: var(--bxa-bg);
  color: var(--bxa-ink);
  font: 14px/1.45 system-ui, -apple-system, "Segoe UI", Roboto, sans-serif;
  text-align: left;
}

.bxa,
.bxa *,
.bxa *::before,
.bxa *::after {
  box-sizing: border-box;
}

.bxa button,
.bxa input {
  font: inherit;
  color: inherit;
}

/* Tools (Sprache, Speichern) in der bx-headboard-Leiste */
.bx-headboard.bxa-headboard {
  display: flex;
  align-items: center;
  justify-content: space-between;
  gap: 1rem;
}

.bxa .bxa-tools {
  display: flex;
  align-items: center;
  gap: .5rem;
}

.bxa .bxa-tools .bxa-seg {
  border-radius: 6px;
}

.bxa .bxa-tools .bxa-seg button {
  padding: .2rem .6rem;
  font-size: .85em;
}

.bxa .bxa-tools .bxa-btn {
  padding: .25rem .75rem;
  border-radius: 6px;
  font-size: .85em;
}

.bxa .bxa-seg {
  display: inline-flex;
  border: 1px solid var(--bxa-line);
  border-radius: 8px;
  overflow: hidden;
}

.bxa .bxa-seg button {
  border: 0;
  background: none;
  padding: .4rem .8rem;
  cursor: pointer;
}

.bxa .bxa-seg button[aria-pressed="true"] {
  background: var(--bxa-acc);
  color: var(--bxa-accInk);
  font-weight: 600;
}

.bxa .bxa-btn {
  border: 0;
  border-radius: 8px;
  padding: .5rem 1rem;
  background: var(--bxa-acc);
  color: var(--bxa-accInk);
  font-weight: 600;
  cursor: pointer;
}

.bxa .bxa-btn.bxa-ghost {
  background: none;
  color: var(--bxa-ink);
  border: 1px dashed var(--bxa-line);
  font-weight: 500;
}

.bxa :focus-visible {
  outline: 2px solid var(--bxa-acc);
  outline-offset: 2px;
}

/* Live-Vorschau */

.bxa .bxa-stage {
  position: sticky;
  top: 0;
  z-index: 5;
  padding: 1rem 1.25rem;
  background: var(--bxa-bg);
}

.bxa .bxa-frame {
  display: flex;
  flex-direction: column;
  min-height: 9rem;
  border: 1px solid var(--bxa-line);
  border-radius: var(--bxa-r);
  overflow: hidden;
  background: var(--bxa-pane);
}

.bxa .bxa-frame[data-pos="bottom"] .bx-ticker {
  order: 2;
  margin-top: auto;
}

.bxa .bxa-frame .bxa-page {
  padding: 1rem 1.25rem;
  color: var(--bxa-mut);
}

.bxa .bxa-page b {
  display: block;
  color: var(--bxa-ink);
  margin-bottom: .25rem;
}

/* Ticker (identisch zum Frontend, Präfix bx-) */

.bx-ticker {
  display: flex;
  align-items: stretch;
  background: var(--bx-bg);
  color: var(--bx-ink);
  border-block: 1px solid var(--bx-line);
  font-size: var(--bx-size);
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

/* Layout */

.bxa .bxa-cols {
  display: grid;
  grid-template-columns: minmax(0, 22rem) minmax(0, 1fr);
  gap: 1.25rem;
  padding: 0 1.25rem 2rem;
  align-items: start;
}

@media (max-width: 860px) {
  .bxa .bxa-cols {
    grid-template-columns: 1fr;
  }
}

.bxa section {
  background: var(--bxa-pane);
  border: 1px solid var(--bxa-line);
  border-radius: var(--bxa-r);
  padding: 1rem 1.1rem;
}

.bxa h2 {
  font-size: .95rem;
  margin: 0 0 .8rem;
}

.bxa h3 {
  font-size: .85rem;
  margin: 1.1rem 0 .5rem;
  color: var(--bxa-mut);
  font-weight: 600;
}

.bxa .bxa-row {
  display: grid;
  grid-template-columns: 6.5rem 1fr 4.6rem;
  align-items: center;
  gap: .6rem;
  margin: .55rem 0;
}

.bxa .bxa-row output {
  text-align: right;
  color: var(--bxa-mut);
  font-variant-numeric: tabular-nums;
}

.bxa .bxa-row.bxa-plain {
  grid-template-columns: 1fr auto;
}

.bxa input[type="range"] {
  width: 100%;
  accent-color: var(--bxa-acc);
}

.bxa input[type="color"] {
  inline-size: 2.2rem;
  block-size: 1.8rem;
  padding: 0;
  border: 1px solid var(--bxa-line);
  border-radius: 6px;
  background: none;
  cursor: pointer;
}

.bxa .bxa-sw {
  appearance: none;
  inline-size: 2.4rem;
  block-size: 1.4rem;
  border-radius: 99px;
  background: var(--bxa-line);
  position: relative;
  cursor: pointer;
  transition: background .15s;
}

.bxa .bxa-sw::after {
  content: "";
  position: absolute;
  inset: .15rem auto .15rem .15rem;
  inline-size: 1.1rem;
  border-radius: 50%;
  background: #fff;
  transition: transform .15s;
}

.bxa .bxa-sw:checked {
  background: var(--bxa-acc);
}

.bxa .bxa-sw:checked::after {
  transform: translateX(1rem);
}

.bxa .bxa-txt {
  width: 100%;
  border: 1px solid var(--bxa-line);
  border-radius: 7px;
  padding: .4rem .6rem;
  background: var(--bxa-bg);
}

/* Meldungen */

.bxa .bxa-it {
  border: 1px solid var(--bxa-line);
  border-radius: 8px;
  padding: .6rem;
  margin-bottom: .6rem;
  background: var(--bxa-bg);
}

.bxa .bxa-it.bxa-over {
  outline: 2px dashed var(--bxa-acc);
}

.bxa .bxa-l1 {
  display: grid;
  grid-template-columns: auto 1fr auto auto;
  gap: .6rem;
  align-items: center;
}

.bxa .bxa-l2 {
  display: grid;
  grid-template-columns: 2fr 1fr 1fr;
  gap: .5rem;
  margin-top: .5rem;
}

.bxa .bxa-l2 label {
  font-size: .75rem;
  color: var(--bxa-mut);
}

.bxa .bxa-hd {
  cursor: grab;
  user-select: none;
  color: var(--bxa-mut);
  padding: .2rem .35rem;
  border-radius: 6px;
}

.bxa .bxa-x {
  border: 0;
  background: none;
  color: var(--bxa-mut);
  cursor: pointer;
  font-size: 1.1rem;
}

.bxa .bxa-hint {
  color: var(--bxa-mut);
  font-size: .8rem;
  margin: .4rem 0 0;
}

.bxa pre {
  margin: 0;
  max-height: 13rem;
  overflow: auto;
  font-size: .75rem;
  background: var(--bxa-bg);
  border: 1px solid var(--bxa-line);
  border-radius: 8px;
  padding: .7rem;
}

.bxa details {
  margin-top: 1rem;
}

.bxa summary {
  cursor: pointer;
  color: var(--bxa-mut);
}

.bxa #bxa-toast {
  position: fixed;
  right: 1rem;
  bottom: 1rem;
  background: var(--bxa-acc);
  color: var(--bxa-accInk);
  padding: .6rem 1rem;
  border-radius: 8px;
  opacity: 0;
  transition: opacity .2s;
  pointer-events: none;
}

.bxa #bxa-toast.on {
  opacity: 1;
}
</style>
<?php
}
