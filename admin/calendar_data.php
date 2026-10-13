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

$events = [];

$query = mysqli_query($conn, "
    SELECT b.*, r.room_name, u.name AS user_name
    FROM bookings b
    JOIN rooms r ON b.room_id = r.room_id
    JOIN users u ON b.user_id = u.user_id
");

while ($row = mysqli_fetch_assoc($query)) {

    $events[] = [
        "title"      => $row["room_name"],
        "start"      => $row["date"] . "T" . $row["start_time"],
        "end"        => $row["date"] . "T" . $row["end_time"],

        // Extended props for popup info
        "booking_id" => $row["booking_id"],
        "room"       => $row["room_name"],
        "user_name"  => $row["user_name"],
        "start_time" => $row["start_time"],
        "end_time"   => $row["end_time"]
    ];
}

echo json_encode($events);






