<?php
session_start();
if (!isset($_SESSION["user_id"]) || ($_SESSION["role"] ?? "") !== "admin") {
    http_response_code(403);
    header("Content-Type: application/json");
    echo json_encode(["status" => "error", "message" => "Admins only."]);
    exit;
}

require "../config/db.php";

header("Content-Type: application/json");

// Total Users
$total_users = mysqli_fetch_row(mysqli_query($conn,
    "SELECT COUNT(*) FROM users"))[0];

// Total Rooms
$total_rooms = mysqli_fetch_row(mysqli_query($conn,
    "SELECT COUNT(*) FROM rooms"))[0];

// Total Bookings
$total_bookings = mysqli_fetch_row(mysqli_query($conn,
    "SELECT COUNT(*) FROM bookings"))[0];

// Total Check-ins
$total_checkins = mysqli_fetch_row(mysqli_query($conn,
    "SELECT COUNT(*) FROM checkin_logs"))[0];

// Today's Check-ins
$today = date("Y-m-d");
$today_checkins = mysqli_fetch_row(mysqli_query($conn,
    "SELECT COUNT(*) FROM checkin_logs WHERE DATE(checkin_time) = '$today'"))[0];

// Room Usage Stats
$room_usage = [];
$result = mysqli_query($conn,
    "SELECT r.room_name, COUNT(b.booking_id) AS total
     FROM rooms r
     LEFT JOIN bookings b ON b.room_id = r.room_id
     GROUP BY r.room_id");

while ($x = mysqli_fetch_assoc($result)) {
    $room_usage[] = $x;
}

// Daily Bookings (last 14 days)
$daily_bookings = [];
$result = mysqli_query($conn,
    "SELECT date, COUNT(*) AS total
     FROM bookings
     GROUP BY date
     ORDER BY date DESC
     LIMIT 14");

while ($x = mysqli_fetch_assoc($result)) {
    $daily_bookings[] = $x;
}

// Final JSON Response
echo json_encode([
    "total_users" => $total_users,
    "total_rooms" => $total_rooms,
    "total_bookings" => $total_bookings,
    "total_checkins" => $total_checkins,
    "today_checkins" => $today_checkins,
    "room_usage" => $room_usage,
    "daily_bookings" => array_reverse($daily_bookings)
]);
