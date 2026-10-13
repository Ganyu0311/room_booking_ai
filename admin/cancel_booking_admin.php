<?php
session_start();
require "../config/db.php";

if (!isset($_SESSION["user_id"]) || ($_SESSION["role"] ?? "") !== "admin") {
    header("Location: ../auth/login.php");
    exit;
}

$booking_id = isset($_GET["id"]) ? (int)$_GET["id"] : 0;

if ($booking_id <= 0) {
    header("Location: manage_bookings.php?error=invalid");
    exit;
}

/* If checked-in or expired, admin still can cancel
   For now: only cancel if pending
*/
$q = mysqli_prepare($conn, "SELECT status FROM bookings WHERE booking_id=? LIMIT 1");
mysqli_stmt_bind_param($q, "i", $booking_id);
mysqli_stmt_execute($q);
$res = mysqli_stmt_get_result($q);
$row = mysqli_fetch_assoc($res);

if (!$row) {
    header("Location: manage_bookings.php?error=not_found");
    exit;
}

$status = strtolower($row["status"] ?? "pending");
if ($status !== "pending") {
    header("Location: manage_bookings.php?error=cannot_cancel");
    exit;
}

/* delete checkin log just in case */
$delLog = mysqli_prepare($conn, "DELETE FROM checkin_logs WHERE booking_id=?");
mysqli_stmt_bind_param($delLog, "i", $booking_id);
mysqli_stmt_execute($delLog);

/*Soft cancel */
$upd = mysqli_prepare($conn, "UPDATE bookings SET status='cancelled' WHERE booking_id=?");
mysqli_stmt_bind_param($upd, "i", $booking_id);

if (mysqli_stmt_execute($upd)) {
    header("Location: manage_bookings.php?success=1");
    exit;
} else {
    header("Location: manage_bookings.php?error=1");
    exit;
}
