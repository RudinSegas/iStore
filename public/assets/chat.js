'use strict';
(() => {
  const root = document.querySelector('[data-ai-chat]');
  if (!root) return;
  const launch = root.querySelector('.ai-launch'), panel = root.querySelector('.ai-panel');
  const log = root.querySelector('.ai-messages'), form = root.querySelector('form');
  const input = root.querySelector('textarea'), send = form.querySelector('button');
  const reset = root.querySelector('[data-ai-reset]');
  let busy = false, loaded = false;
  async function restore() {
  if (loaded || busy) return;

  setBusy(true);

  try {
    const data = await request({ action: 'history' });

    if (!Array.isArray(data.history)) {
      throw new Error('Invalid history response');
    }

    loaded = true;

    if (data.history.length > 0) {
      log.replaceChildren();

      data.history.forEach(m => {
        bubble(m.text, m.role);
      });
    }
  } catch (error) {
    console.error('Chat history restore failed:', error);
  } finally {
    setBusy(false);
  }
}
  function open(value) {
    panel.hidden = !value;
    launch.setAttribute('aria-expanded', String(value));
    (value ? input : launch).focus();
    if (value) restore();
  }
  launch.addEventListener('click', () => open(panel.hidden));
  root.querySelector('[data-ai-close]').addEventListener('click', () => open(false));
  root.addEventListener('keydown', e => { if (e.key === 'Escape') open(false); });
  function element(tag, text, className) {
    const el = document.createElement(tag);
    if (text !== undefined) el.textContent = text;
    if (className) el.className = className;
    return el;
  }
  function bubble(text, who) {
    const el = element('div', text, 'ai-bubble ai-' + who);
    log.append(el);
    // Bound the DOM as well as server-side context.
    while (log.children.length > 40) log.firstElementChild.remove();
    log.scrollTop = log.scrollHeight;
    return el;
  }
  const money = cents => new Intl.NumberFormat('he-IL', {style:'currency', currency:'ILS'}).format(cents / 100);
  function products(items, compare) {
    if (!Array.isArray(items) || !items.length) return;
    const box = element('div', undefined, 'ai-results');
    if (compare) {
      const table = element('table');
      const caption = element('caption', 'השוואה לפי נתוני הקטלוג'); table.append(caption);
      const head = element('tr'); ['מוצר', 'מחיר', 'פרטים מהקטלוג'].forEach(t => head.append(element('th', t)));
      const thead = element('thead'); thead.append(head); table.append(thead);
      const body = element('tbody');
      items.forEach(p => {
        const row = element('tr'), name = element('td'); name.append(link(p));
        row.append(name, element('td', money(p.price_cents)), element('td', p.short_description + ' — ' + p.description + (p.stock > 0 ? ' · במלאי' : ' · אזל מהמלאי')));
        body.append(row);
      }); table.append(body); box.append(table);
    } else items.forEach(p => {
      const card = element('article', undefined, 'ai-product');
      card.append(link(p), element('strong', money(p.price_cents)), element('p', p.short_description), element('p', p.description));
      box.append(card);
    });
    log.append(box); log.scrollTop = log.scrollHeight;
  }
  function link(p) {
    const a = element('a', p.name);
    // Construct the only allowed link locally; do not trust model-generated URLs.
    if (Number.isSafeInteger(p.id) && p.id > 0) a.href = '/?page=product&id=' + p.id;
    return a;
  }
  async function request(payload) {
    const controller = new AbortController(), timer = setTimeout(() => controller.abort(), 35000);
    try {
      const response = await fetch('/api/chat.php', {method:'POST', credentials:'same-origin',
        headers:{'Content-Type':'application/json','X-CSRF-Token':root.dataset.csrf},
        body:JSON.stringify(payload), signal:controller.signal});
      const data = await response.json();
      if (!response.ok || !data.ok) throw new Error(typeof data.message === 'string' ? data.message : 'לא ניתן לקבל תשובה כרגע.');
      return data;
    } catch (error) {
      if (error.name === 'AbortError') throw new Error('הבקשה ארכה יותר מדי זמן. נסו שוב בעוד רגע.');
      if (error instanceof SyntaxError || error instanceof TypeError) throw new Error('לא ניתן להתחבר לעוזר כרגע. אפשר להמשיך בקטלוג.');
      throw error;
    } finally { clearTimeout(timer); }
  }
  function setBusy(value) {
    busy = value; send.disabled = value; reset.disabled = value;
    form.setAttribute('aria-busy', String(value));
  }
  form.addEventListener('submit', async e => {
    e.preventDefault(); const message = input.value.trim();
    if (busy || !message || [...message].length > 1000) return;
    bubble(message, 'user'); input.value = ''; setBusy(true);
    const pending = bubble('חושב על התשובה…', 'assistant');
    try { const data = await request({message}); pending.textContent = data.message; products(data.products, data.compare); if (data.note) bubble(data.note, 'assistant'); }
    catch (error) { pending.textContent = error.message; input.value = message; }
    finally { setBusy(false); input.focus(); log.scrollTop = log.scrollHeight; }
  });
  input.addEventListener('keydown', e => { if (e.key === 'Enter' && !e.shiftKey && !e.isComposing) { e.preventDefault(); form.requestSubmit(); } });
  reset.addEventListener('click', async () => {
    if (busy) return; setBusy(true);
    try { await request({action:'reset'}); log.replaceChildren(); bubble('השיחה אופסה. איזה מוצר מחפשים?', 'assistant'); }
    catch (error) { bubble(error.message, 'assistant'); }
    finally { setBusy(false); input.focus(); }
  });
})();
