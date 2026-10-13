<?php
session_start();
require "../config/db.php";

/* Must be logged in */
if (!isset($_SESSION["user_id"])) {
    header("Location: ../auth/login.php");
    exit;
}

$user_id = (int)$_SESSION["user_id"];
$booking_id = isset($_GET["id"]) ? (int)$_GET["id"] : 0;

if ($booking_id <= 0) {
    header("Location: my_bookings.php?error=invalid");
    exit;
}

/* Only cancel own booking + only if still pending */
$stmt = mysqli_prepare($conn, "
    SELECT booking_id, status
    FROM bookings
    WHERE booking_id = ?
      AND user_id = ?
    LIMIT 1
");
mysqli_stmt_bind_param($stmt, "ii", $booking_id, $user_id);
mysqli_stmt_execute($stmt);
$res = mysqli_stmt_get_result($stmt);
$row = mysqli_fetch_assoc($res);

if (!$row) {
    header("Location: my_bookings.php?error=not_found");
    exit;
}

$status = strtolower($row["status"] ?? "pending");

/* Block cancel if checked_in / cancelled / expired */
if ($status !== "pending") {
    header("Location: my_bookings.php?error=cannot_cancel");
    exit;
}

/* (Optional safety) delete checkin log if any */
$delLog = mysqli_prepare($conn, "DELETE FROM checkin_logs WHERE booking_id = ?");
mysqli_stmt_bind_param($delLog, "i", $booking_id);
mysqli_stmt_execute($delLog);

/* Soft-cancel (UPDATE not DELETE) */
$upd = mysqli_prepare($conn, "
    UPDATE bookings
    SET status = 'cancelled'
    WHERE booking_id = ?
      AND user_id = ?
      AND (status IS NULL OR status = 'pending')
");
mysqli_stmt_bind_param($upd, "ii", $booking_id, $user_id);

if (mysqli_stmt_execute($upd) && mysqli_stmt_affected_rows($upd) > 0) {
    header("Location: my_bookings.php?success=cancelled");
    exit;
} else {
    header("Location: my_bookings.php?error=failed");
    exit;
}
