<?php
session_start();
require "../config/db.php";

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

/* Only cancel own booking AND only if pending */
$stmt = mysqli_prepare($conn, "
    SELECT booking_id, COALESCE(status,'pending') AS status
    FROM bookings
    WHERE booking_id = ? AND user_id = ?
    LIMIT 1
");
mysqli_stmt_bind_param($stmt, "ii", $booking_id, $user_id);
mysqli_stmt_execute($stmt);
$res = mysqli_stmt_get_result($stmt);
$row = mysqli_fetch_assoc($res);

if (!$row) {
    header("Location: my_bookings.php?error=notfound");
    exit;
}

$status = strtolower($row["status"]);

if ($status !== "pending") {
    header("Location: my_bookings.php?error=cannot_cancel");
    exit;
}

/* Soft cancel */
$upd = mysqli_prepare($conn, "
    UPDATE bookings
    SET status='cancelled'
    WHERE booking_id = ? AND user_id = ?
");
mysqli_stmt_bind_param($upd, "ii", $booking_id, $user_id);

if (mysqli_stmt_execute($upd)) {
    header("Location: my_bookings.php?success=cancelled");
    exit;
}

header("Location: my_bookings.php?error=failed");
exit;
