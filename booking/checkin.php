<?php
session_start();
if (!isset($_SESSION["user_id"])) {
    header("Location: ../auth/login.php");
    exit;
}

require "../config/db.php";
date_default_timezone_set("Asia/Kuala_Lumpur");

$booking_id = (int)($_GET["id"] ?? 0);
if ($booking_id <= 0) die("Invalid booking");

// Only the booking owner or an admin can check in
$user_id = (int)$_SESSION["user_id"];
$is_admin = ($_SESSION["role"] ?? "") === "admin";
$own = mysqli_query($conn, "SELECT user_id FROM bookings WHERE booking_id=$booking_id LIMIT 1");
$owner = $own ? mysqli_fetch_assoc($own) : null;
if (!$owner) die("Booking not found");
if (!$is_admin && (int)$owner["user_id"] !== $user_id) die("You can only check in to your own booking.");

$now = date("Y-m-d H:i:s");

// Update booking
mysqli_query($conn, "
    UPDATE bookings
    SET status='checked_in', check_in_time='$now'
    WHERE booking_id=$booking_id
      AND status='pending'
");

$check = mysqli_query($conn, "
    SELECT b.*, r.room_name
    FROM bookings b
    JOIN rooms r ON b.room_id=r.room_id
    WHERE b.booking_id=$booking_id
");

$data = mysqli_fetch_assoc($check);
if (!$data) die("Booking not found");

header("Location: checkin_result.php?id=$booking_id");
exit;
