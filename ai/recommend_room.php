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
mysqli_query($conn, "SET time_zone = '+08:00'");

// Accept both GET and POST
$date       = $_POST["date"]       ?? $_GET["date"]       ?? "";
$start_time = $_POST["start_time"] ?? $_GET["start_time"] ?? "";
$end_time   = $_POST["end_time"]   ?? $_GET["end_time"]   ?? "";
$capacity   = $_POST["capacity"]   ?? $_GET["capacity"]   ?? "";
$equipment  = $_POST["equipment"]  ?? $_GET["equipment"]  ?? "";

// Basic validation 
if ($date === "" || $start_time === "" || $end_time === "") {
    echo json_encode([
        "status" => "error",
        "message" => "Missing required parameters: date, start_time, end_time"
    ]);
    exit;
}

$capacity = (int)$capacity;
if ($capacity <= 0) $capacity = 1;

// Normalize equipment required (comma-separated: "PROJECTOR,WHITEBOARD")
$requiredEquip = [];
if (trim($equipment) !== "") {
    $parts = explode(",", $equipment);
    foreach ($parts as $p) {
        $p = strtoupper(trim($p));
        if ($p !== "") $requiredEquip[] = $p;
    }
    $requiredEquip = array_values(array_unique($requiredEquip));
}

/*
  Only treat bookings with status IN ('pending','checked_in') as blocking.
  cancelled / expired should NOT block.
*/
$sql = "
SELECT r.room_id, r.room_name, r.capacity, r.equipment, r.location
FROM rooms r
WHERE r.status = 'active'
  AND r.capacity >= ?
  AND NOT EXISTS (
      SELECT 1
      FROM bookings b
      WHERE b.room_id = r.room_id
        AND b.date = ?
        AND b.status IN ('pending','checked_in')
        AND (b.start_time < ? AND b.end_time > ?)
  )
";

$stmt = mysqli_prepare($conn, $sql);
mysqli_stmt_bind_param($stmt, "isss", $capacity, $date, $end_time, $start_time);
mysqli_stmt_execute($stmt);
$res = mysqli_stmt_get_result($stmt);

$rooms = [];
while ($row = mysqli_fetch_assoc($res)) {
    $rooms[] = $row;
}

if (count($rooms) === 0) {
    echo json_encode([
        "status" => "success",
        "message" => "No available rooms match your request.",
        "recommendations" => []
    ]);
    exit;
}

// 2) Score rooms
$scored = [];

foreach ($rooms as $r) {
    $score = 0;
    $reasons = [];

    // Capacity fit scoring (closer is better)
    $extra = (int)$r["capacity"] - $capacity;

    if ($extra === 0) {
        $score += 40;
        $reasons[] = "Perfect capacity match.";
    } elseif ($extra <= 5) {
        $score += 30;
        $reasons[] = "Capacity is a close fit (+" . $extra . " seats).";
    } elseif ($extra <= 15) {
        $score += 18;
        $reasons[] = "Capacity fits (+" . $extra . " seats).";
    } else {
        $score += 8;
        $reasons[] = "Capacity fits but oversized (+" . $extra . " seats).";
    }

    // Equipment matching
    $roomEquipText = strtoupper($r["equipment"] ?? "");

    if (count($requiredEquip) > 0) {
        $matchCount = 0;

        foreach ($requiredEquip as $req) {
            if ($req !== "" && strpos($roomEquipText, $req) !== false) {
                $matchCount++;
            }
        }

        if ($matchCount === count($requiredEquip)) {
            $score += 45;
            $reasons[] = "All required equipment available (" . implode(", ", $requiredEquip) . ").";
        } elseif ($matchCount > 0) {
            $score += 20;
            $reasons[] = "Some required equipment available ($matchCount/" . count($requiredEquip) . ").";
        } else {
            $score -= 30;
            $reasons[] = "Required equipment not available.";
        }
    } else {
        $score += 8;
        $reasons[] = "No equipment constraint.";
    }

    // Small bonus if location exists
    if (!empty($r["location"])) {
        $score += 2;
    }

    $scored[] = [
        "room_id" => (int)$r["room_id"],
        "room_name" => $r["room_name"],
        "capacity" => (int)$r["capacity"],
        "equipment" => $r["equipment"],
        "location" => $r["location"],
        "score" => $score,
        "explanation" => implode(" ", $reasons)
    ];
}

// 3) Sort by score desc
usort($scored, function($a, $b) {
    return $b["score"] <=> $a["score"];
});

// Return top 3
$top = array_slice($scored, 0, 3);

echo json_encode([
    "status" => "success",
    "message" => "Top recommended rooms based on availability + best fit.",
    "recommendations" => $top
]);
