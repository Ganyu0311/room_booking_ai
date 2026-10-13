<?php
session_start();
header("Content-Type: application/json");

if (!isset($_SESSION["user_id"])) {
    echo json_encode(["status" => "error", "code" => "UNAUTHORIZED", "message" => "Please login first."]);
    exit;
}

require "../config/db.php";

/*Force Malaysia timezone */
date_default_timezone_set("Asia/Kuala_Lumpur");
mysqli_query($conn, "SET time_zone = '+08:00'");

$data = trim($_GET["data"] ?? "");

if ($data === "") {
    echo json_encode(["status" => "error", "code" => "NO_DATA", "message" => "No QR data received."]);
    exit;
}

/**
 * SUPPORT MULTIPLE QR FORMATS:
 * 1) Old text: "Booking ID: 69"
 * 2) URL: "http://localhost/.../checkin.php?id=69"
 * 3) URL: "...?booking_id=69" or "...?bid=69"
 */
$booking_id = 0;

// Format 1: Booking ID: 69
if (preg_match("/Booking ID:\s*(\d+)/i", $data, $m)) {
    $booking_id = (int)$m[1];
}

// Format 2/3: URL contains query
if ($booking_id <= 0) {
    $parts = @parse_url($data);

    // If QR text isn't a valid URL, parse_url can fail.
    // We'll still try to extract ?id= from raw string:
    $query = $parts["query"] ?? "";

    if ($query === "" && strpos($data, "?") !== false) {
        $query = substr($data, strpos($data, "?") + 1);
    }

    if ($query !== "") {
        parse_str($query, $q);

        $keys = ["id", "booking_id", "bid"];
        foreach ($keys as $k) {
            if (isset($q[$k]) && is_numeric($q[$k])) {
                $booking_id = (int)$q[$k];
                break;
            }
        }
    }
}

if ($booking_id <= 0) {
    echo json_encode(["status" => "error", "code" => "INVALID_QR", "message" => "Invalid QR format."]);
    exit;
}

/* Get booking details */
$stmt = mysqli_prepare($conn, "
    SELECT b.booking_id, b.user_id, b.room_id, b.date, b.start_time, b.end_time,
           COALESCE(b.status,'pending') AS status,
           b.check_in_time,
           r.room_name
    FROM bookings b
    JOIN rooms r ON b.room_id = r.room_id
    WHERE b.booking_id = ?
    LIMIT 1
");
mysqli_stmt_bind_param($stmt, "i", $booking_id);
mysqli_stmt_execute($stmt);
$res = mysqli_stmt_get_result($stmt);
$booking = mysqli_fetch_assoc($res);

if (!$booking) {
    echo json_encode(["status" => "error", "code" => "NOT_FOUND", "message" => "Booking not found."]);
    exit;
}

$bookingStatus = strtolower(trim($booking["status"] ?? "pending"));

/* Block invalid statuses */
if ($bookingStatus === "cancelled") {
    echo json_encode(["status" => "error", "code" => "CANCELLED", "message" => "This booking is cancelled."]);
    exit;
}
if ($bookingStatus === "expired") {
    echo json_encode(["status" => "error", "code" => "EXPIRED", "message" => "This booking is expired."]);
    exit;
}
if ($bookingStatus === "checked_in") {
    // already checked in (even if log not found)
    echo json_encode([
        "status" => "error",
        "code" => "ALREADY_CHECKED_IN",
        "message" => "This booking is already checked-in.",
        "room" => $booking["room_name"],
        "date" => $booking["date"],
        "time" => $booking["start_time"] . " - " . $booking["end_time"],
        "checkin_time" => $booking["check_in_time"] ?: null
    ]);
    exit;
}

/* Build DateTime objects */
$tz = new DateTimeZone("Asia/Kuala_Lumpur");
$now = new DateTime("now", $tz);

$start = new DateTime($booking["date"] . " " . $booking["start_time"], $tz);
$end   = new DateTime($booking["date"] . " " . $booking["end_time"], $tz);

/* Optional grace period (allow early check-in) */
$graceMinutes = 10; // change to 10 if you want 10 mins early
$startWithGrace = clone $start;
$startWithGrace->modify("-{$graceMinutes} minutes");

/* Time window checks */
if ($now < $startWithGrace) {
    echo json_encode([
        "status" => "error",
        "code" => "TOO_EARLY",
        "message" => "Too early. Allowed from " . $startWithGrace->format("H:i") . ".",
        "room" => $booking["room_name"],
        "date" => $booking["date"],
        "time" => $booking["start_time"] . " - " . $booking["end_time"]
    ]);
    exit;
}

if ($now > $end) {
    // Optional: mark as expired automatically
    mysqli_query($conn, "UPDATE bookings SET status='expired' WHERE booking_id=".(int)$booking_id." AND (status IS NULL OR status='pending')");

    echo json_encode([
        "status" => "error",
        "code" => "ENDED",
        "message" => "Booking time is over. Check-in not allowed.",
        "room" => $booking["room_name"],
        "date" => $booking["date"],
        "time" => $booking["start_time"] . " - " . $booking["end_time"]
    ]);
    exit;
}

/* Prevent duplicate check-in based on logs */
$chk = mysqli_prepare($conn, "SELECT log_id FROM checkin_logs WHERE booking_id = ? LIMIT 1");
mysqli_stmt_bind_param($chk, "i", $booking_id);
mysqli_stmt_execute($chk);
$chkRes = mysqli_stmt_get_result($chk);

if (mysqli_fetch_assoc($chkRes)) {
    echo json_encode([
        "status" => "error",
        "code" => "DUPLICATE_LOG",
        "message" => "This booking is already checked-in."
    ]);
    exit;
}

/* Insert check-in */
$checkin_time = $now->format("Y-m-d H:i:s");

$ins = mysqli_prepare($conn, "
    INSERT INTO checkin_logs (booking_id, user_id, room_id, checkin_time)
    VALUES (?, ?, ?, ?)
");
mysqli_stmt_bind_param($ins, "iiis", $booking_id, $booking["user_id"], $booking["room_id"], $checkin_time);

if (!mysqli_stmt_execute($ins)) {
    echo json_encode(["status" => "error", "code" => "INSERT_FAIL", "message" => "Failed to save check-in log."]);
    exit;
}

/* Update bookings table */
$upd = mysqli_prepare($conn, "
    UPDATE bookings
    SET status = 'checked_in',
        check_in_time = ?
    WHERE booking_id = ?
");
mysqli_stmt_bind_param($upd, "si", $checkin_time, $booking_id);
mysqli_stmt_execute($upd);

/* Success */
echo json_encode([
    "status"  => "success",
    "code"    => "OK",
    "message" => "Check-in Successful!",
    "room"    => $booking["room_name"],
    "date"    => $booking["date"],
    "time"    => $booking["start_time"] . " - " . $booking["end_time"],
    "checkin_time" => $checkin_time
]);
