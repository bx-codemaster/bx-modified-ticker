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
:root {
  color-scheme: light dark;
  --bg: #f4f6f8;
  --pane: #fff;
  --ink: #141a22;
  --mut: #5d6877;
  --line: #dfe4ea;
  --acc: #AF417E;
  --accInk: #fff;
  --r: 10px;

  --bx-bg: var(--bg);
  --bx-ink: var(--ink);
  --bx-accent: var(--acc);
  --bx-line: var(--line);
  --bx-gap: 3rem;
  --bx-size: 15px;
  --bx-pad: .8rem;
  --bx-fade: 4rem;
  --bx-dur: 30s;
}

@media (prefers-color-scheme: dark) {
  :root {
    --bg: #0e1216;
    --pane: #171c22;
    --ink: #e9edf2;
    --mut: #93a0b0;
    --line: #2a323c;
    --acc: #e055a2;
    --accInk: #062b27;
  }
}

* {
  box-sizing: border-box;
}

body {
  margin: 0;
  background: var(--bg);
  color: var(--ink);
  font: 14px/1.45 system-ui, -apple-system, "Segoe UI", Roboto, sans-serif;
}

button,
input {
  font: inherit;
  color: inherit;
}

.top {
  display: flex;
  flex-wrap: wrap;
  align-items: center;
  gap: 1rem;
  padding: .9rem 1.25rem;
  background: var(--pane);
  border-bottom: 1px solid var(--line);
}

.top h1 {
  font-size: 1.05rem;
  margin: 0;
  margin-right: auto;
}

.seg {
  display: inline-flex;
  border: 1px solid var(--line);
  border-radius: 8px;
  overflow: hidden;
}

.seg button {
  border: 0;
  background: none;
  padding: .4rem .8rem;
  cursor: pointer;
}

.seg button[aria-pressed="true"] {
  background: var(--acc);
  color: var(--accInk);
  font-weight: 600;
}

.btn {
  border: 0;
  border-radius: 8px;
  padding: .5rem 1rem;
  background: var(--acc);
  color: var(--accInk);
  font-weight: 600;
  cursor: pointer;
}

.btn.ghost {
  background: none;
  color: var(--ink);
  border: 1px dashed var(--line);
  font-weight: 500;
}

:focus-visible {
  outline: 2px solid var(--acc);
  outline-offset: 2px;
}

/* Live-Vorschau */
.stage {
  position: sticky;
  top: 0;
  z-index: 5;
  padding: 1rem 1.25rem;
  background: var(--bg);
}

.frame {
  display: flex;
  flex-direction: column;
  min-height: 9rem;
  border: 1px solid var(--line);
  border-radius: var(--r);
  overflow: hidden;
  background: var(--pane);
}

.frame[data-pos="bottom"] .bx-ticker {
  order: 2;
  margin-top: auto;
}

.frame .page {
  padding: 1rem 1.25rem;
  color: var(--mut);
}

.page b {
  display: block;
  color: var(--ink);
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
  animation: bxp 2s ease-in-out infinite;
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
  animation: bxs var(--bx-dur, 30s) linear infinite;
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

@keyframes bxs {
  to {
    transform: translateX(calc(-100% - var(--bx-gap)));
  }
}

@keyframes bxp {
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
.cols {
  display: grid;
  grid-template-columns: minmax(0, 22rem) minmax(0, 1fr);
  gap: 1.25rem;
  padding: 0 1.25rem 2rem;
  align-items: start;
}

@media (max-width: 860px) {
  .cols {
    grid-template-columns: 1fr;
  }
}

section {
  background: var(--pane);
  border: 1px solid var(--line);
  border-radius: var(--r);
  padding: 1rem 1.1rem;
}

h2 {
  font-size: .95rem;
  margin: 0 0 .8rem;
}

h3 {
  font-size: .85rem;
  margin: 1.1rem 0 .5rem;
  color: var(--mut);
  font-weight: 600;
}

.row {
  display: grid;
  grid-template-columns: 6.5rem 1fr 4.6rem;
  align-items: center;
  gap: .6rem;
  margin: .55rem 0;
}

.row output {
  text-align: right;
  color: var(--mut);
  font-variant-numeric: tabular-nums;
}

.row.plain {
  grid-template-columns: 1fr auto;
}

input[type="range"] {
  width: 100%;
  accent-color: var(--acc);
}

input[type="color"] {
  inline-size: 2.2rem;
  block-size: 1.8rem;
  padding: 0;
  border: 1px solid var(--line);
  border-radius: 6px;
  background: none;
  cursor: pointer;
}

.sw {
  appearance: none;
  inline-size: 2.4rem;
  block-size: 1.4rem;
  border-radius: 99px;
  background: var(--line);
  position: relative;
  cursor: pointer;
  transition: background .15s;
}

.sw::after {
  content: "";
  position: absolute;
  inset: .15rem auto .15rem .15rem;
  inline-size: 1.1rem;
  border-radius: 50%;
  background: #fff;
  transition: transform .15s;
}

.sw:checked {
  background: var(--acc);
}

.sw:checked::after {
  transform: translateX(1rem);
}

.txt {
  width: 100%;
  border: 1px solid var(--line);
  border-radius: 7px;
  padding: .4rem .6rem;
  background: var(--bg);
}

/* Meldungen */
.it {
  border: 1px solid var(--line);
  border-radius: 8px;
  padding: .6rem;
  margin-bottom: .6rem;
  background: var(--bg);
}

.it.over {
  outline: 2px dashed var(--acc);
}

.l1 {
  display: grid;
  grid-template-columns: auto 1fr auto auto;
  gap: .6rem;
  align-items: center;
}

.l2 {
  display: grid;
  grid-template-columns: 2fr 1fr 1fr;
  gap: .5rem;
  margin-top: .5rem;
}

.l2 label {
  font-size: .75rem;
  color: var(--mut);
}

.hd {
  cursor: grab;
  user-select: none;
  color: var(--mut);
  padding: .2rem .35rem;
  border-radius: 6px;
}

.x {
  border: 0;
  background: none;
  color: var(--mut);
  cursor: pointer;
  font-size: 1.1rem;
}

.hint {
  color: var(--mut);
  font-size: .8rem;
  margin: .4rem 0 0;
}

pre {
  margin: 0;
  max-height: 13rem;
  overflow: auto;
  font-size: .75rem;
  background: var(--bg);
  border: 1px solid var(--line);
  border-radius: 8px;
  padding: .7rem;
}

details {
  margin-top: 1rem;
}

summary {
  cursor: pointer;
  color: var(--mut);
}

#toast {
  position: fixed;
  right: 1rem;
  bottom: 1rem;
  background: var(--acc);
  color: var(--accInk);
  padding: .6rem 1rem;
  border-radius: 8px;
  opacity: 0;
  transition: opacity .2s;
  pointer-events: none;
}

#toast.on {
  opacity: 1;
}
</style>
<?php
}
