<?php
session_start();
if (!isset($_SESSION["user_id"])) {
    header("Location: ../auth/login.php");
    exit;
}

require "../config/db.php";

// Get booking ID from URL
$booking_id = (int)($_GET["id"] ?? 0);
if ($booking_id <= 0) {
    die("Invalid booking ID.");
}

// Fetch booking details (students can only see their own bookings)
$user_id = (int)$_SESSION["user_id"];
$is_admin = ($_SESSION["role"] ?? "") === "admin";

$stmt = mysqli_prepare($conn, "
    SELECT b.*, r.room_name
    FROM bookings b
    JOIN rooms r ON b.room_id = r.room_id
    WHERE b.booking_id = ?
      AND (b.user_id = ? OR ? = 1)
");
$admin_flag = $is_admin ? 1 : 0;
mysqli_stmt_bind_param($stmt, "iii", $booking_id, $user_id, $admin_flag);
mysqli_stmt_execute($stmt);
$booking = mysqli_fetch_assoc(mysqli_stmt_get_result($stmt));

if (!$booking) {
    die("Booking not found.");
}

// --------------------------
// QR CODE GENERATION
// --------------------------

// TEXT that will appear inside QR
$qr_text = BASE_URL . "/booking/checkin.php?id=" . $booking_id;


// Create folder if not exists
$dir = "../qrcodes/";
if (!is_dir($dir)) {
    mkdir($dir, 0777, true);
}

// File paths
$qr_filename = "booking_" . $booking_id . ".png";
$qr_file_path = $dir . $qr_filename; // for saving file
$qr_file_url  = "../qrcodes/" . $qr_filename; // for browser display

// Generate QR
include "../phpqrcode/qrlib.php";
QRcode::png($qr_text, $qr_file_path, QR_ECLEVEL_L, 4);
?>

<?php require "../includes/header.php"; ?>
<?php require "../includes/navbar.php"; ?>

<div class="container">
    <div class="card shadow mt-4">
        <div class="card-body">

            <h3>Booking Successful</h3>

            <p>Your booking has been confirmed!</p>

            <p><b>Room:</b> <?= htmlspecialchars($booking['room_name']) ?></p>
            <p><b>Date:</b> <?= $booking['date'] ?></p>
            <p><b>Time:</b> <?= $booking['start_time'] ?> - <?= $booking['end_time'] ?></p>

            <h5 class="mt-4">Your Booking QR Code</h5>
            <img src="<?= $qr_file_url ?>" style="width:200px; border:1px solid #ccc; padding:5px;">

            <br><br>

            <a href="<?= $qr_file_url ?>" download class="btn btn-primary">Download QR Code</a>
            <a href="my_bookings.php" class="btn btn-secondary">Go to My Bookings</a>

        </div>
    </div>
</div>

<?php require "../includes/footer.php"; ?>
