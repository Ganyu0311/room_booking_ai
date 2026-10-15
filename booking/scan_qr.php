<?php
session_start();
if (!isset($_SESSION["user_id"]) || ($_SESSION["role"] ?? "") !== "admin") {
    header("Location: ../auth/login.php");
    exit;
}

require "../includes/header.php";
require "../includes/navbar.php";
?>


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

<script src="/room_booking_ai/assets/js/scan-qr.js"></script>
