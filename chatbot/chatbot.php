<?php
session_start();
if (!isset($_SESSION["user_id"])) {
    header("Location: ../auth/login.php");
    exit;
}

require "../includes/header.php";
require "../includes/navbar.php";
?>

<style>
.chat-wrap { max-width: 900px; margin: 30px auto; }
.chat-box {
  height: 430px; overflow-y: auto; background: #fff;
  border: 1px solid #e5e7eb; border-radius: 10px; padding: 16px;
}
.msg { margin: 10px 0; display: flex; }
.msg.user { justify-content: flex-end; }
.bubble {
  max-width: 78%; padding: 10px 12px; border-radius: 12px;
  line-height: 1.35; white-space: pre-wrap;
}
.user .bubble { background: #0d6efd; color: #fff; border-bottom-right-radius: 4px; }
.bot .bubble { background: #f3f4f6; color: #111827; border-bottom-left-radius: 4px; }
.small-hint { color:#6b7280; font-size: 13px; }
.quick { display:flex; gap:8px; flex-wrap: wrap; margin-top: 10px; }
.quick button { border:1px solid #d1d5db; background:#fff; border-radius: 999px; padding:6px 10px; font-size: 13px; }
</style>

<div class="container chat-wrap">
  <div class="card shadow">
    <div class="card-body">
      <h3 class="mb-1">💬 Room Booking Chatbot</h3>
      <div class="small-hint mb-3">
        Try:
        <b>available today 2pm-3pm</b>,
        <b>recommend 15 projector tomorrow 10-11</b>,
        <b>my bookings</b>,
        <b>cancel booking 42</b>,
        <b>book LAB3 tomorrow 10-11</b>,
        <b>download qr 69</b>
      </div>

      <div id="chatBox" class="chat-box"></div>

      <div class="quick" id="quickActions" style="display:none;"></div>

      <form id="chatForm" class="mt-3 d-flex gap-2">
        <input id="chatInput" class="form-control" placeholder="Type a message..." autocomplete="off" />
        <button class="btn btn-primary" type="submit">Send</button>
      </form>

    </div>
  </div>
</div>

<?php require "../includes/footer.php"; ?>

<script>
const chatBox = document.getElementById("chatBox");
const chatForm = document.getElementById("chatForm");
const chatInput = document.getElementById("chatInput");
const quickActions = document.getElementById("quickActions");

function escapeHtml(str) {
  return (str || "").replace(/[&<>"']/g, (m) => ({
    "&":"&amp;", "<":"&lt;", ">":"&gt;", '"':"&quot;", "'":"&#039;"
  }[m]));
}

function linkify(text) {
  // make /room_booking_ai/... or http(s) links clickable
  const urlRegex = /((https?:\/\/[^\s]+)|(\/room_booking_ai\/[^\s]+))/g;
  return text.replace(urlRegex, (url) => {
    const href = url.startsWith("http") ? url : url;
    return `<a href="${href}" target="_blank" rel="noopener noreferrer">${url}</a>`;
  });
}

function addMsg(text, who="bot") {
  const row = document.createElement("div");
  row.className = "msg " + who;

  const b = document.createElement("div");
  b.className = "bubble";

  if (who === "bot") {
    const safe = escapeHtml(text);
    b.innerHTML = linkify(safe).replace(/\n/g, "<br>");
  } else {
    b.textContent = text;
  }

  row.appendChild(b);
  chatBox.appendChild(row);
  chatBox.scrollTop = chatBox.scrollHeight;
}

function renderQuick(actions) {
  quickActions.innerHTML = "";
  if (!actions || actions.length === 0) {
    quickActions.style.display = "none";
    return;
  }
  actions.forEach(a => {
    const btn = document.createElement("button");
    btn.type = "button";
    btn.textContent = a;
    btn.onclick = () => {
      chatInput.value = a;
      chatForm.dispatchEvent(new Event("submit"));
    };
    quickActions.appendChild(btn);
  });
  quickActions.style.display = "flex";
}

async function sendMsg(text) {
  addMsg(text, "user");

  try {
    const res = await fetch("chatbot_api.php", {
      method: "POST",
      headers: {"Content-Type":"application/x-www-form-urlencoded"},
      body: "message=" + encodeURIComponent(text)
    });

    const data = await res.json();
    addMsg(data.reply || "Sorry, I didn't understand that.", "bot");
    renderQuick(data.quick_actions || []);
  } catch (e) {
    addMsg("Error: chatbot_api.php not responding or returned invalid JSON.", "bot");
  }
}

addMsg(
  "Hi! I can help you check availability, recommend rooms, show your bookings, and cancel bookings.\n\n" +
  "Now you can also book from chat ✅\n" +
  "Examples:\n" +
  "• available today 2pm-3pm\n" +
  "• recommend for 15 projector + whiteboard tomorrow 10-11\n" +
  "• my bookings\n" +
  "• cancel booking 42\n" +
  "• book LAB3 tomorrow 10-11\n" +
  "• download qr 69\n",
  "bot"
);

chatForm.addEventListener("submit", (e) => {
  e.preventDefault();
  const text = chatInput.value.trim();
  if (!text) return;
  chatInput.value = "";
  sendMsg(text);
});
</script>
