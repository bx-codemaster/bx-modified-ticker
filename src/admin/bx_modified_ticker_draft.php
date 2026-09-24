<?php
// Admin-Prototyp für BX Modified Ticker
?>
<!doctype html>
<html lang="de">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>bx_modified_ticker – Admin-Prototyp</title>
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
</head>
<body>
<header class="top">
  <h1>Ticker · bx_modified_ticker</h1>
  <div class="seg" id="langs" role="group" aria-label="Sprache">
    <button data-l="de" aria-pressed="true">Deutsch</button>
    <button data-l="en" aria-pressed="false">English</button>
  </div>
  <button class="btn" id="save">Speichern</button>
</header>

<div class="stage">
  <div class="frame" id="frame">
    <div class="bx-ticker" id="tk"></div>
    <div class="page"><b>Vorschau</b>Änderungen wirken sofort. Über den Tabs wechselst du die Sprache der Texte.</div>
  </div>
</div>

<div class="cols">
  <section>
    <h2>Darstellung</h2>
    <div id="sliders"></div>
    <div class="row plain"><span>Pause bei Hover</span><input class="sw" type="checkbox" data-k="pause"></div>
    <h3>Richtung</h3>
    <div class="seg" data-seg="dir"><button data-v="left">Nach links</button><button data-v="right">Nach rechts</button></div>
    <h3>Position</h3>
    <div class="seg" data-seg="pos"><button data-v="top">Oben</button><button data-v="bottom">Unten</button></div>
    <h3>Farben</h3>
    <div id="colors"></div>
    <h3>Label</h3>
    <input class="txt" id="label" aria-label="Label-Text">
    <p class="hint">Reduzierte Bewegung wird immer respektiert und ist nicht abschaltbar.</p>
  </section>

  <section>
    <h2>Meldungen</h2>
    <div id="list"></div>
    <button class="btn ghost" id="add">+ Meldung hinzufügen</button>
    <p class="hint">Reihenfolge per Ziehen am Griff oder mit Pfeiltasten. Fehlt ein Text in der gewählten Sprache, wird der deutsche verwendet.</p>
    <details><summary>Werte, wie das Modul sie speichern würde</summary><pre id="json"></pre></details>
  </section>
</div>
<div id="toast" role="status">Gespeichert (Prototyp)</div>

<script>
const S = {
  speed: 60,
  gap: 3,
  size: 15,
  pad: .8,
  fade: 4,
  pause: true,
  dir: 'left',
  pos: 'top',
  bg: '#ffffff',
  ink: '#14181f',
  accent: '#e5322d',
  line: '#dfe4ea'
};

const st = {
  lang: 'de',
  label: {
    de: 'Live',
    en: 'Live'
  },
  items: [
    {
      active: true,
      link: '/aktion',
      from: '',
      to: '',
      text: {
        de: 'Versandkostenfrei ab 50 Euro',
        en: 'Free shipping from 50 euros'
      }
    },
    {
      active: true,
      link: '',
      from: '',
      to: '',
      text: {
        de: 'Neu im Sortiment: Sommerkollektion',
        en: ''
      }
    },
    {
      active: true,
      link: '/service',
      from: '',
      to: '2099-12-31',
      text: {
        de: 'Kostenlose Rücksendung innerhalb von 30 Tagen',
        en: 'Free returns within 30 days'
      }
    }
  ]
};

const SL = [
  ['speed', 'Tempo', 'px/s', 20, 240, 5],
  ['gap', 'Abstand', 'rem', 1, 8, .25],
  ['size', 'Schrift', 'px', 12, 24, 1],
  ['pad', 'Höhe', 'rem', .4, 1.6, .1],
  ['fade', 'Kanten', 'rem', 0, 8, .5]
];

const CO = [
  ['bg', 'Hintergrund'],
  ['ink', 'Text'],
  ['accent', 'Akzent'],
  ['line', 'Linien']
];

const $ = s => document.querySelector(s);
const tk = $('#tk');
const esc = s => String(s).replace(/[&<>"']/g, c => ({
  '&': '&amp;',
  '<': '&lt;',
  '>': '&gt;',
  '"': '&quot;',
  "'": '&#39;'
}[c]));
const T = o => o[st.lang] || o.de || '';

$('#sliders').innerHTML = SL.map(([k, n, u, a, b, s]) => `
  <label class="row">
    <span>${n}</span>
    <input type="range" data-k="${k}" min="${a}" max="${b}" step="${s}" value="${S[k]}">
    <output data-o="${k}">${S[k]} ${u}</output>
  </label>
`).join('');

$('#colors').innerHTML = CO.map(([k, n]) => `
  <label class="row plain">
    <span>${n}</span>
    <span>
      <output data-o="${k}" style="color:var(--mut);margin-right:.5rem">${S[k]}</output>
      <input type="color" data-k="${k}" value="${S[k]}">
    </span>
  </label>
`).join('');

document.querySelector('[data-k=pause]').checked = S.pause;

function vars() {
  const s = tk.style;

  s.setProperty('--bx-bg', S.bg);
  s.setProperty('--bx-ink', S.ink);
  s.setProperty('--bx-accent', S.accent);
  s.setProperty('--bx-line', S.line);
  s.setProperty('--bx-gap', S.gap + 'rem');
  s.setProperty('--bx-size', S.size + 'px');
  s.setProperty('--bx-pad', S.pad + 'rem');
  s.setProperty('--bx-fade', S.fade + 'rem');

  tk.classList.toggle('rev', S.dir === 'right');
  tk.classList.toggle('pause', S.pause);
  $('#frame').dataset.pos = S.pos;

  const g = tk.querySelector('.bx-ticker-group');
  if (g) {
    const rootFontSize = parseFloat(getComputedStyle(document.documentElement).fontSize);
    const dist = g.offsetWidth + S.gap * rootFontSize;
    s.setProperty('--bx-dur', Math.max(4, dist / S.speed).toFixed(1) + 's');
  }
}

function render() {
  const today = new Date().toISOString().slice(0, 10);
  const li = st.items
    .filter(i => i.active && (!i.from || i.from <= today) && (!i.to || i.to >= today) && T(i.text))
    .map(i => `
      <li>${i.link ? `<a href="#">${esc(T(i.text))}</a>` : esc(T(i.text))}</li>
    `)
    .join('') || '<li>Keine aktive Meldung</li>';

  tk.innerHTML = `
    <div class="bx-ticker-label">${esc(T(st.label))}</div>
    <div class="bx-ticker-viewport">
      <ul class="bx-ticker-group">${li}</ul>
      <ul class="bx-ticker-group" aria-hidden="true">${li}</ul>
    </div>
  `;

  vars();
  json();
}

function json() {
  const K = {
    BX_TICKER_SPEED: S.speed,
    BX_TICKER_GAP: S.gap,
    BX_TICKER_FONT_SIZE: S.size,
    BX_TICKER_PADDING: S.pad,
    BX_TICKER_FADE: S.fade,
    BX_TICKER_PAUSE_HOVER: S.pause ? 'True' : 'False',
    BX_TICKER_DIRECTION: S.dir,
    BX_TICKER_POSITION: S.pos,
    BX_TICKER_COLOR_BG: S.bg,
    BX_TICKER_COLOR_TEXT: S.ink,
    BX_TICKER_COLOR_ACCENT: S.accent,
    BX_TICKER_COLOR_LINE: S.line
  };

  $('#json').textContent = JSON.stringify({
    configuration: K,
    label: st.label,
    bx_ticker_items: st.items
  }, null, 1);
}

function list() {
  $('#list').innerHTML = st.items.map((it, i) => `
    <div class="it" data-i="${i}">
      <div class="l1">
        <span class="hd" draggable="true" tabindex="0" role="button" aria-label="Verschieben">⠿</span>
        <input class="txt" data-f="text" value="${esc(it.text[st.lang] || '')}" placeholder="${st.lang === 'de' ? 'Text der Meldung' : 'Fallback: ' + esc(it.text.de || '')}" aria-label="Text">
        <input class="sw" type="checkbox" data-f="active" ${it.active ? 'checked' : ''} aria-label="Aktiv">
        <button class="x" data-del aria-label="Löschen">✕</button>
      </div>
      <div class="l2">
        <label>Link<input class="txt" data-f="link" value="${esc(it.link)}" placeholder="optional"></label>
        <label>Von<input class="txt" type="date" data-f="from" value="${it.from}"></label>
        <label>Bis<input class="txt" type="date" data-f="to" value="${it.to}"></label>
      </div>
    </div>
  `).join('');
}

function move(a, b) {
  if (b < 0 || b >= st.items.length) {
    return;
  }

  st.items.splice(b, 0, st.items.splice(a, 1)[0]);
  list();
  render();
}

document.addEventListener('input', e => {
  const k = e.target.dataset.k;
  if (!k) {
    return;
  }

  S[k] = e.target.type === 'checkbox'
    ? e.target.checked
    : e.target.type === 'range'
      ? +e.target.value
      : e.target.value;

  const o = document.querySelector(`[data-o=${k}]`);
  if (o) {
    const u = (SL.find(x => x[0] === k) || [])[2];
    o.textContent = S[k] + (u ? ' ' + u : '');
  }

  vars();
  json();
});

document.querySelectorAll('[data-seg]').forEach(g => {
  const sync = () => g.querySelectorAll('button').forEach(b => {
    b.setAttribute('aria-pressed', b.dataset.v === S[g.dataset.seg]);
  });

  sync();
  g.onclick = e => {
    const b = e.target.closest('button');
    if (!b) {
      return;
    }

    S[g.dataset.seg] = b.dataset.v;
    sync();
    vars();
    json();
  };
});

$('#langs').onclick = e => {
  const b = e.target.closest('button');
  if (!b) {
    return;
  }

  st.lang = b.dataset.l;
  document.querySelectorAll('#langs button').forEach(x => {
    x.setAttribute('aria-pressed', x === b);
  });
  $('#label').value = st.label[st.lang] || '';
  list();
  render();
};

$('#label').value = st.label.de;
$('#label').oninput = e => {
  st.label[st.lang] = e.target.value;
  render();
};

const L = $('#list');
L.addEventListener('input', e => {
  const r = e.target.closest('.it');
  const f = e.target.dataset.f;
  if (!r || !f) {
    return;
  }

  const it = st.items[+r.dataset.i];
  if (f === 'text') {
    it.text[st.lang] = e.target.value;
  } else if (f === 'active') {
    it.active = e.target.checked;
  } else {
    it[f] = e.target.value;
  }
  render();
});

L.addEventListener('click', e => {
  if (e.target.dataset.del !== undefined) {
    st.items.splice(+e.target.closest('.it').dataset.i, 1);
    list();
    render();
  }
});

let drag = null;
L.addEventListener('dragstart', e => {
  const r = e.target.closest('.it');
  drag = +r.dataset.i;
  e.dataTransfer.setDragImage(r, 10, 10);
});

L.addEventListener('dragover', e => {
  e.preventDefault();
  L.querySelectorAll('.over').forEach(x => x.classList.remove('over'));
  e.target.closest('.it')?.classList.add('over');
});

L.addEventListener('drop', e => {
  e.preventDefault();
  const r = e.target.closest('.it');
  if (r && drag !== null) {
    move(drag, +r.dataset.i);
  }
  drag = null;
});

L.addEventListener('dragend', () => {
  L.querySelectorAll('.over').forEach(x => x.classList.remove('over'));
});

L.addEventListener('keydown', e => {
  const h = e.target.closest('.hd');
  if (!h || !['ArrowUp', 'ArrowDown'].includes(e.key)) {
    return;
  }

  e.preventDefault();
  const i = +h.closest('.it').dataset.i;
  const n = e.key === 'ArrowUp' ? i - 1 : i + 1;
  move(i, n);
  L.querySelector(`.it[data-i="${n}"] .hd`)?.focus();
});

$('#add').onclick = () => {
  st.items.push({
    active: true,
    link: '',
    from: '',
    to: '',
    text: {
      de: '',
      en: ''
    }
  });
  list();
  render();
  L.querySelector('.it:last-child [data-f=text]').focus();
};

$('#save').onclick = () => {
  const t = $('#toast');
  t.classList.add('on');
  setTimeout(() => t.classList.remove('on'), 1800);
};

addEventListener('resize', vars);
list();
render();
</script>
</body>
</html>