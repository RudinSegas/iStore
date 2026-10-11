'use strict';
const toggle = document.querySelector('.menu-toggle');
toggle?.addEventListener('click', () => {
  const expanded = toggle.getAttribute('aria-expanded') === 'true';
  toggle.setAttribute('aria-expanded', String(!expanded));
  document.getElementById('nav').classList.toggle('open', !expanded);
});
document.querySelector('[data-checkout]')?.addEventListener('submit', event => {
  const button = event.currentTarget.querySelector('button');
  button.disabled = true;
  button.textContent = 'שומרים את ההזמנה...';
});
