<?php
declare(strict_types=1);
?>
</main>
<section class="ai-chat-panel" id="aiChatPanel" aria-hidden="true">
  <div class="ai-chat-header">
    <div class="ai-chat-brand">
      <div class="ai-chat-brand-badge">
        <span class="material-symbols-outlined" style="font-variation-settings:'FILL' 1,'wght' 500,'GRAD' 0,'opsz' 24;">auto_awesome</span>
      </div>
      <div>
        <h2 class="ai-chat-title">CUSIT AI Assistant</h2>
        <p class="ai-chat-subtitle">Portal help for complaints, events, FYP, and announcements</p>
      </div>
    </div>
    <button class="ai-chat-close" id="aiChatClose" type="button" aria-label="Close AI chat">
      <span class="material-symbols-outlined">close</span>
    </button>
  </div>
  <div class="ai-chat-body" id="aiChatBody">
    <div class="ai-chat-stack" id="aiChatMessages"></div>
  </div>
  <div class="ai-chat-suggestions" id="aiChatSuggestions">
    <button class="ai-chat-chip" type="button" data-ai-chat-suggestion="How do I submit a complaint on the portal?">Complaints help</button>
    <button class="ai-chat-chip" type="button" data-ai-chat-suggestion="Show me what I should do before registering for an event.">Event registration</button>
    <button class="ai-chat-chip" type="button" data-ai-chat-suggestion="Guide me on creating my FYP group and milestones.">FYP guidance</button>
  </div>
  <div class="ai-chat-composer">
    <form class="ai-chat-form" id="aiChatForm">
      <textarea class="ai-chat-input" id="aiChatInput" rows="1" placeholder="Message CUSIT AI Assistant"></textarea>
      <button class="ai-chat-send" id="aiChatSend" type="submit" aria-label="Send message">
        <span class="material-symbols-outlined" style="font-variation-settings:'FILL' 1,'wght' 500,'GRAD' 0,'opsz' 24;">arrow_upward</span>
      </button>
    </form>
    <p class="ai-chat-footer-note">AI replies are generated from the portal assistant endpoint for on-campus guidance.</p>
  </div>
</section>
<script src="<?= h(base_url('assets/js/ai-chat.js')) ?>"></script>
<script src="<?= h(base_url('assets/js/theme-switcher.js')) ?>"></script>
</body>
</html>
