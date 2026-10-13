<?php
session_start();
if (!isset($_SESSION["user_id"]) || ($_SESSION["role"] ?? "") !== "admin") {
    header("Location: ../auth/login.php");
    exit;
}

require "../includes/header.php";
require "../includes/navbar.php";
?>

<style>
  .scan-wrap { max-width: 950px; margin: 24px auto; }
  .reader-box { max-width: 380px; }
  #reader { border-radius: 12px; overflow: hidden; }
</style>

<div class="container scan-wrap">
  <div class="card shadow">
    <div class="card-body">
      <div class="d-flex align-items-center justify-content-between flex-wrap gap-2">
        <div>
          <h3 class="mb-1">📷 QR Check-In Scanner</h3>
          <div class="text-muted">
            Allow camera access, then scan the student booking QR code.
          </div>
        </div>
        <button id="btnRescan" class="btn btn-outline-primary" style="display:none;">
          Scan Another QR
        </button>
      </div>

      <hr>

      <div class="row g-4 align-items-start">
        <div class="col-lg-5">
          <div class="reader-box">
            <div id="reader"></div>

            <div class="small text-muted mt-2">
              Tip: Hold the QR steady and ensure good lighting.
            </div>
          </div>
        </div>

        <div class="col-lg-7">
          <!-- Result Card -->
          <div id="resultCard" class="card border" style="display:none;">
            <div class="card-body">
              <div id="resultHeader" class="d-flex align-items-center gap-2 mb-2"></div>
              <div id="resultMsg" class="mb-3"></div>

              <div id="resultDetails" style="display:none;">
                <div class="row g-3">
                  <div class="col-md-6">
                    <div class="p-3 border rounded">
                      <div class="text-muted small">Room</div>
                      <div class="fw-semibold" id="dRoom">-</div>
                    </div>
                  </div>
                  <div class="col-md-6">
                    <div class="p-3 border rounded">
                      <div class="text-muted small">Date</div>
                      <div class="fw-semibold" id="dDate">-</div>
                    </div>
                  </div>
                  <div class="col-md-6">
                    <div class="p-3 border rounded">
                      <div class="text-muted small">Time</div>
                      <div class="fw-semibold" id="dTime">-</div>
                    </div>
                  </div>
                  <div class="col-md-6">
                    <div class="p-3 border rounded">
                      <div class="text-muted small">Check-in Time</div>
                      <div class="fw-semibold" id="dCheckin">-</div>
                    </div>
                  </div>
                </div>
              </div>

              <div id="loadingHint" class="text-muted small" style="display:none;">
                Verifying booking...
              </div>
            </div>
          </div>

          <!-- Empty hint -->
          <div id="emptyHint" class="alert alert-info">
            Scan a QR code to see the check-in result here.
          </div>
        </div>
      </div>

    </div>
  </div>
</div>

<?php require "../includes/footer.php"; ?>

<!-- QR Scanner Library -->
<script src="https://unpkg.com/html5-qrcode"></script>

<script>
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
</script>
