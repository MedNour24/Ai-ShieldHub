// Simple chat widget that queries api/suggest-course.php
(function(){
  const wrapper = document.createElement('div');
  wrapper.id = 'chatbot-wrapper';
  wrapper.innerHTML = `
    <div id="chatbot-box" class="d-flex flex-column">
      <div id="chatbot-header">
        <div><strong>Course Helper</strong><div style="font-size:12px;color:#9fb3db;">Describe what you want to learn</div></div>
        <div style="display:flex;gap:8px;align-items:center;">
          <button id="chatbot-clear" class="btn btn-sm btn-outline-light">Clear</button>
          <button id="chatbot-close" class="btn btn-sm btn-light">✕</button>
        </div>
      </div>
      <div id="chatbot-messages" aria-live="polite"></div>
      <div id="chatbot-input">
        <input id="chatbot-text" type="text" placeholder="I want to learn about web security and pentesting..." />
        <button id="chatbot-send">Ask</button>
      </div>
    </div>
    <div id="chatbot-button" title="Ask Course Helper" style="margin-top:8px;">
      <i class="fas fa-comments"></i>
    </div>
  `;

  document.body.appendChild(wrapper);

  const box = document.getElementById('chatbot-box');
  const btn = document.getElementById('chatbot-button');
  const closeBtn = document.getElementById('chatbot-close');
  const clearBtn = document.getElementById('chatbot-clear');
  const sendBtn = document.getElementById('chatbot-send');
  const input = document.getElementById('chatbot-text');
  const messages = document.getElementById('chatbot-messages');

  let open = false;

  function toggle(openTo) {
    open = typeof openTo === 'boolean' ? openTo : !open;
    box.style.display = open ? 'flex' : 'none';
  }

  btn.addEventListener('click', function(){ toggle(true); input.focus(); });
  closeBtn.addEventListener('click', function(){ toggle(false); });
  clearBtn.addEventListener('click', function(){ messages.innerHTML = ''; input.value = ''; input.focus(); });

  function appendMessage(kind, html) {
    const el = document.createElement('div');
    el.className = 'chat-msg ' + (kind === 'user' ? 'user' : 'bot');
    const bubble = document.createElement('div');
    bubble.className = 'bubble';
    bubble.innerHTML = html;
    el.appendChild(bubble);
    messages.appendChild(el);
    messages.scrollTop = messages.scrollHeight;
  }

  function showTyping() {
    const el = document.createElement('div');
    el.className = 'chat-msg bot typing-row';
    el.innerHTML = '<div class="bubble"><span class="typing"></span></div>';
    messages.appendChild(el);
    messages.scrollTop = messages.scrollHeight;
    return el;
  }

// Debounce helper
function debounce(fn, wait) {
    let t = null;
    return function() {
        const ctx = this, args = arguments;
        clearTimeout(t);
        t = setTimeout(() => fn.apply(ctx, args), wait);
    }
}

let lastResultsHash = null;

async function ask(query) {
    if (!query || query.trim().length < 2) return;
    appendMessage('user', escapeHtml(query));
    input.value = '';

    const typingEl = showTyping();

    try {
        const res = await fetch('api/suggest-course.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ query: query })
        });
        const data = await res.json();
        typingEl.remove();

        if (!data.success) {
            appendMessage('bot', 'Sorry, I could not find anything. Try different keywords.');
            return;
        }

        const arr = data.data;
        if (!arr || arr.length === 0) {
            appendMessage('bot', 'No matching courses found. Try broader terms like "Python", "web security", or "machine learning".');
            return;
        }

        // Simple client-side dedupe: if the returned set is identical to the last one, try to notify user
        const hash = JSON.stringify(arr.map(d => d.id));
        if (hash === lastResultsHash) {
            appendMessage('bot', 'I found similar results — try broader or different keywords for more options.');
        } else {
            lastResultsHash = hash;
            let out = '<div><strong>Here are some courses you might like:</strong></div>';
            out += '<div style="margin-top:8px;display:flex;flex-direction:column;gap:8px;">';
            arr.forEach(function(c){
                const short = c.description ? (c.description.length > 120 ? c.description.substr(0,120) + '…' : c.description) : '';
                out += `<div class="suggestion"><div style="font-weight:600;color:#fff;">${escapeHtml(c.title)}</div><div style="font-size:12px;color:#cfe0ff;margin-top:6px;">${escapeHtml(short)}</div><div style="margin-top:8px;text-align:right;"><a class='btn btn-sm btn-primary' href='course-details.php?id=${c.id}'>Open Course</a></div></div>`;
            });
            out += '</div>';

            appendMessage('bot', out);
        }
    } catch (err) {
        typingEl.remove();
        appendMessage('bot', 'Network error. Please try again.');
        console.error(err);
    }
}

// Wire debounced ask to input controls
const askDebounced = debounce(ask, 400);
sendBtn.addEventListener('click', function(){ askDebounced(input.value); });
input.addEventListener('keydown', function(e){ if (e.key === 'Enter') { e.preventDefault(); askDebounced(input.value); } });

  function escapeHtml(unsafe) {
    return unsafe
      .replace(/&/g, "&amp;")
      .replace(/</g, "&lt;")
      .replace(/>/g, "&gt;")
      .replace(/\"/g, "&quot;")
      .replace(/'/g, "&#039;");
  }

})();
