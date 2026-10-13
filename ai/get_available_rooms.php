<?php
session_start();
header("Content-Type: application/json");

if (!isset($_SESSION["user_id"])) {
    echo json_encode(["status" => "error", "message" => "Please login first."]);
    exit;
}

require "../config/db.php";

// Malaysia timezone
date_default_timezone_set("Asia/Kuala_Lumpur");

/* Force Malaysia timezone in MySQL */
mysqli_query($conn, "SET time_zone = '+08:00'");

$date  = $_POST["date"] ?? null;
$start = $_POST["start_time"] ?? null;
$end   = $_POST["end_time"] ?? null;

if (!$date || !$start || !$end) {
    echo json_encode(["status" => "error", "message" => "Missing parameters"]);
    exit;
}

// Fetch all active rooms only 
$rooms_result = mysqli_query($conn, "SELECT * FROM rooms WHERE status = 'active'");
$rooms = [];

while ($r = mysqli_fetch_assoc($rooms_result)) {
    $rooms[] = $r;
}

$available = [];
$unavailable = [];

// Prepared once, reused for every room (prevents SQL injection)
$stmt = mysqli_prepare($conn, "
    SELECT 1 FROM bookings
    WHERE room_id = ?
      AND date = ?
      AND status IN ('pending','checked_in')
      AND (? < end_time) AND (? > start_time)
    LIMIT 1
");

foreach ($rooms as $room) {
    $room_id = (int)$room["room_id"];

    // Only treat pending + checked_in as blocking
    mysqli_stmt_bind_param($stmt, "isss", $room_id, $date, $start, $end);
    mysqli_stmt_execute($stmt);
    $conflict = mysqli_stmt_get_result($stmt);

    if ($conflict && mysqli_num_rows($conflict) > 0) {
        $unavailable[] = $room;
    } else {
        $available[] = $room;
    }
}

echo json_encode([
    "status" => "success",
    "available" => $available,
    "unavailable" => $unavailable
]);
?>
