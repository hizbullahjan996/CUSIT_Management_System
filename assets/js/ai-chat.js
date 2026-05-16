document.addEventListener('DOMContentLoaded', () => {
  const triggers = document.querySelectorAll('[data-ai-chat-trigger]');
  const csrfMeta = document.querySelector('meta[name="csrf-token"]');
  const csrfToken = csrfMeta ? csrfMeta.getAttribute('content') : '';
  const panel = document.getElementById('aiChatPanel');
  const closeButton = document.getElementById('aiChatClose');
  const messages = document.getElementById('aiChatMessages');
  const body = document.getElementById('aiChatBody');
  const form = document.getElementById('aiChatForm');
  const input = document.getElementById('aiChatInput');
  const sendButton = document.getElementById('aiChatSend');
  const suggestions = document.querySelectorAll('[data-ai-chat-suggestion]');
  const userId = document.body.dataset.userId || 'guest';
  const storageKey = `cusit-ai-chat-history-${userId}`;
  let isSending = false;

  if (!triggers.length || !csrfToken || !panel || !messages || !body || !form || !input || !sendButton) {
    return;
  }

  const scrollToBottom = () => {
    body.scrollTop = body.scrollHeight;
  };

  const autoResize = () => {
    input.style.height = '24px';
    input.style.height = `${Math.min(input.scrollHeight, 140)}px`;
  };

  const saveHistory = () => {
    const history = Array.from(messages.querySelectorAll('[data-role]')).map((node) => ({
      role: node.getAttribute('data-role') || 'assistant',
      text: node.querySelector('.ai-chat-bubble') ? node.querySelector('.ai-chat-bubble').textContent || '' : '',
      type: node.getAttribute('data-type') || 'message',
    }));
    window.sessionStorage.setItem(storageKey, JSON.stringify(history.slice(-20)));
  };

  const renderMessage = (role, text, type = 'message') => {
    const item = document.createElement('div');
    item.className = `ai-chat-message is-${role}${type === 'error' ? ' is-error' : ''}`;
    item.setAttribute('data-role', role);
    item.setAttribute('data-type', type);

    if (role === 'assistant') {
      const avatar = document.createElement('div');
      avatar.className = 'ai-chat-avatar';
      avatar.innerHTML = '<span class="material-symbols-outlined" style="font-variation-settings:\'FILL\' 1,\'wght\' 500,\'GRAD\' 0,\'opsz\' 24;">auto_awesome</span>';
      item.appendChild(avatar);
    }

    const bubble = document.createElement('div');
    bubble.className = 'ai-chat-bubble';
    bubble.textContent = text;
    item.appendChild(bubble);

    messages.appendChild(item);
    scrollToBottom();
    saveHistory();
    return item;
  };

  const renderWelcome = () => {
    if (messages.children.length) {
      return;
    }

    renderMessage('assistant', `Hi ${document.body.dataset.userName || 'there'}! I can help with complaints, events, FYP workflow, announcements, and portal guidance.`);
  };

  const loadHistory = () => {
    try {
      const raw = window.sessionStorage.getItem(storageKey);
      if (!raw) {
        renderWelcome();
        return;
      }

      const history = JSON.parse(raw);
      if (!Array.isArray(history) || !history.length) {
        renderWelcome();
        return;
      }

      history.forEach((entry) => {
        if (entry && typeof entry.text === 'string' && (entry.role === 'assistant' || entry.role === 'user')) {
          renderMessage(entry.role, entry.text, entry.type === 'error' ? 'error' : 'message');
        }
      });
    } catch (error) {
      messages.innerHTML = '';
      renderWelcome();
    }
  };

  const setOpen = (open) => {
    panel.classList.toggle('is-open', open);
    panel.setAttribute('aria-hidden', open ? 'false' : 'true');
    if (open) {
      renderWelcome();
      scrollToBottom();
      window.setTimeout(() => input.focus(), 120);
    }
  };

  const addTyping = () => {
    const typing = document.createElement('div');
    typing.className = 'ai-chat-message is-assistant';
    typing.id = 'aiChatTyping';
    typing.innerHTML = `
      <div class="ai-chat-avatar">
        <span class="material-symbols-outlined" style="font-variation-settings:'FILL' 1,'wght' 500,'GRAD' 0,'opsz' 24;">auto_awesome</span>
      </div>
      <div class="ai-chat-bubble">
        <span class="ai-chat-typing"><span></span><span></span><span></span></span>
      </div>
    `;
    messages.appendChild(typing);
    scrollToBottom();
    return typing;
  };

  const removeTyping = () => {
    const typing = document.getElementById('aiChatTyping');
    if (typing) {
      typing.remove();
    }
  };

  const sendMessage = async (rawMessage) => {
    const message = rawMessage.trim();
    if (!message || isSending) {
      return;
    }

    isSending = true;
    sendButton.disabled = true;
    renderMessage('user', message);
    input.value = '';
    autoResize();
    const typing = addTyping();

    try {
      const response = await fetch('/ParticipantName-WebDev/ai_chat.php', {
        method: 'POST',
        headers: {
          'Content-Type': 'application/json',
          'X-Requested-With': 'XMLHttpRequest',
        },
        body: JSON.stringify({
          csrf_token: csrfToken,
          message,
        }),
      });

      const data = await response.json();
      removeTyping();
      if (!response.ok || !data.reply) {
        renderMessage('assistant', data.error || 'AI chat is unavailable right now.', 'error');
        return;
      }

      renderMessage('assistant', data.reply);
    } catch (error) {
      removeTyping();
      renderMessage('assistant', 'AI chat is unavailable right now.', 'error');
    } finally {
      if (typing && typing.parentNode) {
        typing.remove();
      }
      isSending = false;
      sendButton.disabled = false;
      input.focus();
    }
  };

  triggers.forEach((trigger) => {
    trigger.addEventListener('click', () => {
      setOpen(true);
    });
  });

  if (closeButton) {
    closeButton.addEventListener('click', () => setOpen(false));
  }

  form.addEventListener('submit', async (event) => {
    event.preventDefault();
    await sendMessage(input.value);
  });

  input.addEventListener('input', autoResize);
  input.addEventListener('keydown', async (event) => {
    if (event.key === 'Enter' && !event.shiftKey) {
      event.preventDefault();
      await sendMessage(input.value);
    }
  });

  suggestions.forEach((button) => {
    button.addEventListener('click', async () => {
      setOpen(true);
      await sendMessage(button.getAttribute('data-ai-chat-suggestion') || '');
    });
  });

  document.addEventListener('keydown', (event) => {
    if (event.key === 'Escape' && panel.classList.contains('is-open')) {
      setOpen(false);
    }
  });

  loadHistory();
  autoResize();
});
