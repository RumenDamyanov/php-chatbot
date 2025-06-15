<?php
// Minimal chat popup HTML/CSS/JS for embedding in any PHP app (plain PHP, not Blade)
?>
<div id="php-chatbot-popup" style="position:fixed;bottom:24px;right:24px;width:340px;max-width:90vw;z-index:9999;font-family:sans-serif;box-shadow:0 4px 24px rgba(0,0,0,0.12);">
  <div id="php-chatbot-header" style="background:#222;color:#fff;padding:14px 16px;border-radius:8px 8px 0 0;cursor:pointer;font-weight:600;display:flex;align-items:center;gap:8px;">
    <span style="font-size:1.3em;">💬</span> <span>Chat with us!</span>
    <span style="margin-left:auto;font-size:1.1em;opacity:0.7;">&#x25B2;</span>
  </div>
  <div id="php-chatbot-body" style="display:none;background:#fff;border:1px solid #ddd;border-top:none;padding:14px 12px 12px 12px;border-radius:0 0 8px 8px;max-height:400px;overflow-y:auto;font-size:1em;"></div>
  <form id="php-chatbot-form" style="display:none;background:#f9f9f9;padding:10px 8px 8px 8px;border-radius:0 0 8px 8px;border-top:1px solid #eee;display:flex;gap:8px;align-items:center;">
    <input id="php-chatbot-input" type="text" placeholder="Type your message..." style="flex:1;padding:8px 12px;border:1px solid #ccc;border-radius:4px;font-size:1em;" autocomplete="off" />
    <button type="submit" style="padding:8px 16px;border:none;background:#222;color:#fff;border-radius:4px;font-weight:600;">Send</button>
  </form>
</div>
<script>
const popup = document.getElementById('php-chatbot-popup');
const header = document.getElementById('php-chatbot-header');
const body = document.getElementById('php-chatbot-body');
const form = document.getElementById('php-chatbot-form');
const input = document.getElementById('php-chatbot-input');
header.onclick = () => {
  const open = body.style.display === 'block';
  body.style.display = open ? 'none' : 'block';
  form.style.display = open ? 'none' : 'flex';
  header.querySelector('span:last-child').innerHTML = open ? '&#x25B2;' : '&#x25BC;';
  if (!open) input.focus();
};
form.onsubmit = async (e) => {
  e.preventDefault();
  const msg = input.value.trim();
  if (!msg) return;
  body.innerHTML += `<div style='margin:8px 0;text-align:right;'><span style='background:#e0e0e0;padding:6px 12px;border-radius:16px;display:inline-block;'>${msg}</span></div>`;
  input.value = '';
  // Call backend (replace with your endpoint)
  const res = await fetch('/php-chatbot/message', {
    method: 'POST',
    headers: {'Content-Type': 'application/json'},
    body: JSON.stringify({message: msg})
  });
  const data = await res.json();
  body.innerHTML += `<div style='margin:8px 0;text-align:left;'><span style='background:#f0f0ff;padding:6px 12px;border-radius:16px;display:inline-block;'>${data.reply}</span></div>`;
  body.scrollTop = body.scrollHeight;
};
</script>
