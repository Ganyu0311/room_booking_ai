// Extracted from booking/scan_qr.php
let scanner = null;

const btnRescan = document.getElementById("btnRescan");
const resultCard = document.getElementById("resultCard");
const emptyHint = document.getElementById("emptyHint");
const loadingHint = document.getElementById("loadingHint");

const resultHeader = document.getElementById("resultHeader");
const resultMsg = document.getElementById("resultMsg");
const resultDetails = document.getElementById("resultDetails");

const dRoom = document.getElementById("dRoom");
const dDate = document.getElementById("dDate");
const dTime = document.getElementById("dTime");
const dCheckin = document.getElementById("dCheckin");

function showCard() {
  emptyHint.style.display = "none";
  resultCard.style.display = "block";
}

function setLoading(isLoading) {
  loadingHint.style.display = isLoading ? "block" : "none";
}

function setResult(status, message, details = null) {
  showCard();
  setLoading(false);

  // Reset
  resultDetails.style.display = "none";
  dRoom.textContent = "-";
  dDate.textContent = "-";
  dTime.textContent = "-";
  dCheckin.textContent = "-";

  if (status === "success") {
    resultCard.className = "card border border-success";
    resultHeader.innerHTML = `<h4 class="text-success mb-0">✔ Check-in Successful</h4>`;
    resultMsg.className = "alert alert-success";
    resultMsg.textContent = message || "Check-in successful.";

    if (details) {
      resultDetails.style.display = "block";
      dRoom.textContent = details.room || "-";
      dDate.textContent = details.date || "-";
      dTime.textContent = details.time || "-";
      dCheckin.textContent = details.checkin_time || "-";
    }
  } else {
    resultCard.className = "card border border-danger";
    resultHeader.innerHTML = `<h4 class="text-danger mb-0">✖ Check-in Failed</h4>`;
    resultMsg.className = "alert alert-danger";
    resultMsg.textContent = message || "Check-in failed.";
  }

  btnRescan.style.display = "inline-block";
}

async function verifyBooking(decodedText) {
  showCard();
  setLoading(true);

  try {
    const res = await fetch("verify_qr.php?data=" + encodeURIComponent(decodedText));
    const data = await res.json();

    if (data.status === "success") {
      setResult("success", data.message, {
        room: data.room,
        date: data.date,
        time: data.time,
        checkin_time: data.checkin_time
      });
    } else {
      setResult("error", data.message || "Invalid QR format.");
    }
  } catch (e) {
    setResult("error", "Server error while verifying QR.");
  }
}

function startScanner() {
  btnRescan.style.display = "none";
  resultCard.style.display = "none";
  emptyHint.style.display = "block";

  scanner = new Html5QrcodeScanner("reader", { fps: 10, qrbox: 250 });

  scanner.render(async (decodedText) => {
    // stop scanning after success read
    try { await scanner.clear(); } catch(e) {}
    await verifyBooking(decodedText);
  });
}

btnRescan.addEventListener("click", () => {
  // rebuild the reader area cleanly
  document.getElementById("reader").innerHTML = "";
  startScanner();
});

// init
startScanner();
