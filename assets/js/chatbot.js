// Extracted from chatbot/chatbot.php
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
