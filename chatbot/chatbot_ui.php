<!-- Chatbot UI -->
<div id="chatbot-icon" title="Chat with us">
  <i class="fa-solid fa-robot"></i>
</div>

<div id="chatbot-box" aria-hidden="true">
  <div id="chatbot-header">
    <div class="cb-head-left">
      <div class="cb-badge">RZ</div>
      <div>
        <div class="cb-title">Auto Parts Assistant</div>
        <div class="cb-sub">RevZone • Online</div>
      </div>
    </div>

    <button id="chatbot-close" class="cb-close" title="Close chat" type="button">✖</button>
  </div>

  <div id="chatbot-messages" aria-live="polite"></div>

  <div id="chatbot-input">
    <input
      type="text"
      id="userMessage"
      placeholder="Type your message..."
      autocomplete="off" />
    <button id="sendBtn" type="button">Send</button>
  </div>
</div>

<link rel="stylesheet" href="chatbot/chatbot.css">
<script src="chatbot/chatbot.js" defer></script>