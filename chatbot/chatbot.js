const icon = document.getElementById("chatbot-icon");
const box = document.getElementById("chatbot-box");
const closeBtn = document.getElementById("chatbot-close");
const sendBtn = document.getElementById("sendBtn");
const input = document.getElementById("userMessage");
const messages = document.getElementById("chatbot-messages");

const CHATBOT_PHP_PATH = "chatbot/chatbot.php";

// ---- helpers ----
function scrollToBottom(){
  messages.scrollTop = messages.scrollHeight;
}

function addUserMessage(text){
  const div = document.createElement("div");
  div.className = "msg user";
  div.innerHTML = `<span class="meta">You</span>`;
  div.appendChild(document.createTextNode(text)); 
  messages.appendChild(div);
  scrollToBottom();
}

function addBotMessage(html){
  const div = document.createElement("div");
  div.className = "msg bot";
  div.innerHTML = `<span class="meta">Bot</span> ${html}`;
  messages.appendChild(div);
  scrollToBottom();
}


function addTyping(){
  const div = document.createElement("div");
  div.className = "msg bot";
  div.dataset.typing = "1";
  div.innerHTML = `
    <span class="meta">Bot</span>
    <span class="typing">
      <span class="dot"></span><span class="dot"></span><span class="dot"></span>
    </span>
  `;
  messages.appendChild(div);
  scrollToBottom();
}

function removeTyping(){
  const el = messages.querySelector('[data-typing="1"]');
  if (el) el.remove();
}

async function postMessage(msg){
  const body = "message=" + encodeURIComponent(msg);
  const res = await fetch(CHATBOT_PHP_PATH, {
    method: "POST",
    headers: { "Content-Type": "application/x-www-form-urlencoded" },
    body
  });
  return await res.text();
}

// ---- open / close ----
icon.onclick = async () => {
  box.style.display = "flex";
  box.setAttribute("aria-hidden", "false");
  input.focus();

  // load welcome once per page load (frontend)
  if (messages.children.length === 0) {
    addTyping();
    try{
      const reply = await postMessage("hi");
      removeTyping();
      addBotMessage(reply);
    }catch(e){
      removeTyping();
      addBotMessage("⚠️ Connection error. Please try again.");
    }
  }
};

closeBtn.onclick = () => {
  box.style.display = "none";
  box.setAttribute("aria-hidden", "true");
};

// ---- sending ----
async function sendMessage(){
  const msg = input.value.trim();
  if (!msg) return;

  addUserMessage(msg);
  input.value = "";

  sendBtn.disabled = true;
  input.disabled = true;

  addTyping();

  try{
    const reply = await postMessage(msg);
    removeTyping();
    addBotMessage(reply);
  }catch(e){
    removeTyping();
    addBotMessage("⚠️ Connection error. Please try again.");
  }finally{
    sendBtn.disabled = false;
    input.disabled = false;
    input.focus();
  }
}

sendBtn.onclick = sendMessage;
input.onkeydown = (e) => {
  if (e.key === "Enter") sendMessage();
};