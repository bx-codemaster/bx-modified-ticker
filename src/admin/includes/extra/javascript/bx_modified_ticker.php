<?php
/**
 * BX Modified Ticker - JavaScript Includes
 *
 * Central JavaScript inclusion point for the Ticker module.
 * Ensures that all necessary JS files are loaded for the admin interface.
 *
 * @package    BX Modified Ticker
 * @subpackage JavaScript
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
 <script>
    'use strict';
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
<?php
}