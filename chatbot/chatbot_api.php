<?php
session_start();
require "../config/db.php";
header("Content-Type: application/json");

// Malaysia timezone
date_default_timezone_set("Asia/Kuala_Lumpur");

if (!isset($_SESSION["user_id"])) {
    echo json_encode(["reply" => "Please login first."]);
    exit;
}

$user_id = (int)$_SESSION["user_id"];
$role = $_SESSION["role"] ?? "user";

$msg = trim($_POST["message"] ?? "");
$msg_lc = strtolower($msg);

/* Ensure MySQL timezone */
mysqli_query($conn, "SET time_zone = '+08:00'");

function respond($reply, $quick = []) {
    echo json_encode([
        "reply" => $reply,
        "quick_actions" => $quick
    ]);
    exit;
}

function norm_date($raw) {
    $raw = trim($raw);

    if (preg_match('/^\d{4}-\d{2}-\d{2}$/', $raw)) return $raw;

    if (preg_match('/^(\d{1,2})\/(\d{1,2})\/(\d{4})$/', $raw, $m)) {
        return sprintf("%04d-%02d-%02d", (int)$m[3], (int)$m[2], (int)$m[1]);
    }
    return "";
}

function parse_date_from_text($text) {
    $today = date("Y-m-d");
    if (strpos($text, "today") !== false) return $today;
    if (strpos($text, "tomorrow") !== false) return date("Y-m-d", strtotime("+1 day"));

    if (preg_match('/\d{4}-\d{2}-\d{2}/', $text, $m)) return $m[0];
    if (preg_match('/\d{1,2}\/\d{1,2}\/\d{4}/', $text, $m)) return norm_date($m[0]);

    return $today;
}

function parse_time_token($token) {
    $t = strtolower(trim($token));

    // support dot time like 7.30pm or 19.30
    $t = str_replace(".", ":", $t);

    $t = str_replace(" ", "", $t);

    // 14:00 or 14:00:00
    if (preg_match('/^(\d{1,2}):(\d{2})(?::(\d{2}))?$/', $t, $m)) {
        $h = (int)$m[1]; $min = (int)$m[2];
        if ($h < 0 || $h > 23 || $min < 0 || $min > 59) return "";
        return sprintf("%02d:%02d:00", $h, $min);
    }

    // 2pm / 2:30pm / 2am
    if (preg_match('/^(\d{1,2})(?::(\d{2}))?(am|pm)$/', $t, $m)) {
        $h = (int)$m[1]; $min = isset($m[2]) ? (int)$m[2] : 0;
        $ap = $m[3];
        if ($h < 1 || $h > 12 || $min < 0 || $min > 59) return "";
        if ($ap === "pm" && $h !== 12) $h += 12;
        if ($ap === "am" && $h === 12) $h = 0;
        return sprintf("%02d:%02d:00", $h, $min);
    }

    // "10" => treat as 10:00
    if (preg_match('/^\d{1,2}$/', $t)) {
        $h = (int)$t;
        if ($h < 0 || $h > 23) return "";
        return sprintf("%02d:00:00", $h);
    }

    return "";
}

function parse_time_range($text) {
    $clean = strtolower($text);

    // ✅ support dot range like 7.30pm-8.00pm / 19.30-20.00
    $clean = str_replace(".", ":", $clean);

    $clean = str_replace([" to ", " – ", " — ", " till ", " until "], [" - ", " - ", " - ", " - ", " - "], $clean);

    // supports: 2pm-3pm, 2:30pm-3pm, 10-11, 19:30-20:00
    if (preg_match('/(\d{1,2}(?::\d{2})?\s?(?:am|pm)?|\d{1,2}:\d{2}|\b\d{1,2}\b)\s*-\s*(\d{1,2}(?::\d{2})?\s?(?:am|pm)?|\d{1,2}:\d{2}|\b\d{1,2}\b)/', $clean, $m)) {
        $a = trim($m[1]);
        $b = trim($m[2]);

        $start = parse_time_token($a);
        $end   = parse_time_token($b);

        return [$start, $end];
    }

    return ["", ""];
}

function extract_capacity($text) {
    $t = strtolower($text);

    if (preg_match('/\b(?:recommend|suggest)\s+(?:for\s+)?(\d{1,3})\b/', $t, $m)) return (int)$m[1];
    if (preg_match('/\b(?:for|capacity)\s*(\d{1,3})\b/', $t, $m)) return (int)$m[1];
    if (preg_match('/\b(\d{1,3})\s*(?:people|pax)\b/', $t, $m)) return (int)$m[1];

    return 0;
}

function normalize_room_name_from_text($text) {
    $u = strtoupper($text);

    if (preg_match('/\bA-\d-\d+\b/', $u, $m)) {
        return $m[0];
    }

    if (preg_match('/\bLAB\s?\d+\b/', $u, $m)) {
        return str_replace(" ", "", $m[0]); // "LAB 3" => "LAB3"
    }

    return "";
}

function normalize_equipment_text($text) {
    $t = strtolower($text);
    $t = str_replace(["+", "&", "/", ";"], [",", ",", ",", ","], $t);

    $t = str_replace([
        "smarttv", "smart tv", "tv",
        "speaker", "speakers",
        "mic", "microphone",
        "video call", "video-call", "videocall", "zoom", "teams", "meet",
        "doc cam", "document cam", "doccam",
        "gamingpc", "gaming pc"
    ], [
        "smart tv", "smart tv", "smart tv",
        "speaker system", "speaker system",
        "mic system", "mic system",
        "video conferencing", "video conferencing", "video conferencing", "video conferencing", "video conferencing", "video conferencing",
        "document camera", "document camera", "document camera",
        "gaming pc", "gaming pc"
    ], $t);

    return $t;
}

function extract_equipment($text) {
    $t = normalize_equipment_text($text);
    $u = strtoupper($t);

    $equip = [
        "WHITEBOARD",
        "PROJECTOR",
        "SMART TV",
        "SPEAKER SYSTEM",
        "MIC SYSTEM",
        "VIDEO CONFERENCING",
        "DOCUMENT CAMERA",
        "GAMING PC",
    ];

    $found = [];
    foreach ($equip as $e) {
        if (strpos($u, $e) !== false) $found[] = $e;
    }

    return array_values(array_unique($found));
}

function room_has_all_equipment($roomEquipText, $required) {
    $roomEquipText = strtoupper($roomEquipText ?? "");
    foreach ($required as $req) {
        if ($req === "") continue;
        if (strpos($roomEquipText, strtoupper($req)) === false) return false;
    }
    return true;
}

function is_past_slot($date, $start_time) {
    $tz = new DateTimeZone("Asia/Kuala_Lumpur");
    $now = new DateTime("now", $tz);
    $start = new DateTime($date . " " . $start_time, $tz);
    return $start < $now;
}

function create_booking($conn, $user_id, $room_id, $date, $start_time, $end_time) {

    $stmt = mysqli_prepare($conn, "
        SELECT booking_id FROM bookings
        WHERE room_id = ?
          AND date = ?
          AND status IN ('pending','checked_in')
          AND (? < end_time AND ? > start_time)
        LIMIT 1
    ");
    mysqli_stmt_bind_param($stmt, "isss", $room_id, $date, $start_time, $end_time);
    mysqli_stmt_execute($stmt);
    $res = mysqli_stmt_get_result($stmt);

    if ($res && mysqli_fetch_assoc($res)) {
        return ["ok" => false, "error" => "This time slot is already booked."];
    }

    $ins = mysqli_prepare($conn, "
        INSERT INTO bookings (user_id, room_id, date, start_time, end_time, status)
        VALUES (?, ?, ?, ?, ?, 'pending')
    ");
    mysqli_stmt_bind_param($ins, "iisss", $user_id, $room_id, $date, $start_time, $end_time);

    if (!mysqli_stmt_execute($ins)) {
        return ["ok" => false, "error" => "Failed to create booking."];
    }

    $bid = mysqli_insert_id($conn);
    return ["ok" => true, "booking_id" => $bid];
}

// ---------------------------------------------------------
// INTENTS
// ---------------------------------------------------------

if ($msg === "" || $msg_lc === "help") {
    respond(
        "I can do:\n• availability (available today 2pm-3pm)\n• recommendation (recommend for 15 projector + whiteboard tomorrow 10-11)\n• booking by chat (book LAB3 tomorrow 10-11)\n• my bookings\n• cancel booking 42\n• download qr 42",
        ["available today 2pm-3pm", "recommend for 15 projector + whiteboard tomorrow 10-11", "book LAB3 tomorrow 10-11", "my bookings"]
    );
}

/* Download QR intent */
if (preg_match('/\b(download|qr)\s+qr\s+(\d+)\b/i', $msg, $m) || preg_match('/\bdownload\s+(\d+)\b/i', $msg, $m)) {
    $bid = (int)($m[2] ?? 0);
    if ($bid <= 0) $bid = (int)($m[1] ?? 0);

    if ($bid <= 0) {
        respond("Please provide booking id. Example: download qr 69", ["my bookings"]);
    }

    if ($role !== "admin") {
        $own = mysqli_query($conn, "SELECT booking_id FROM bookings WHERE booking_id=$bid AND user_id=$user_id LIMIT 1");
        if (!$own || mysqli_num_rows($own) === 0) {
            respond("You can only access your own QR. Try: my bookings");
        }
    }

    $link = "/room_booking_ai/booking/booking_success.php?id=" . $bid;
    respond("Here is your QR page:\n$link", ["my bookings"]);
}

/* BOOK intent (draft + confirm) */
if (preg_match('/^\s*book\b/i', $msg)) {

    $roomName = normalize_room_name_from_text($msg);
    $date = parse_date_from_text($msg_lc);
    list($start, $end) = parse_time_range($msg_lc);

    if ($roomName === "") {
        respond("Which room do you want to book? Example: book LAB3 tomorrow 10-11", ["book LAB3 tomorrow 10-11", "book A-5-3 2026-01-02 08:00-08:30"]);
    }
    if ($start === "" || $end === "") {
        respond("Tell me the time like: book $roomName tomorrow 10-11\n(You can also type 7:30pm-8:00pm or 7.30pm-8.00pm)", ["book $roomName tomorrow 10-11"]);
    }

    if (is_past_slot($date, $start)) {
        respond("You can’t book a past time slot. Try a future time.", ["book $roomName tomorrow 10-11"]);
    }

    $stmt = mysqli_prepare($conn, "SELECT room_id, room_name FROM rooms WHERE UPPER(room_name)=? LIMIT 1");
    $roomNameUpper = strtoupper($roomName);
    mysqli_stmt_bind_param($stmt, "s", $roomNameUpper);
    mysqli_stmt_execute($stmt);
    $res = mysqli_stmt_get_result($stmt);
    $room = mysqli_fetch_assoc($res);

    if (!$room) {
        respond("I can’t find that room name ($roomName). Check the spelling like LAB3 or A-5-3.");
    }

    $_SESSION["chat_booking_draft"] = [
        "room_id" => (int)$room["room_id"],
        "room_name" => $room["room_name"],
        "date" => $date,
        "start_time" => $start,
        "end_time" => $end
    ];

    respond(
        "Confirm booking?\n• Room: {$room['room_name']}\n• Date: $date\n• Time: $start - $end\n\nReply: yes / no",
        ["yes", "no", "my bookings"]
    );
}

/* Confirm booking */
if ($msg_lc === "yes" || $msg_lc === "confirm" || $msg_lc === "ok") {

    if (!isset($_SESSION["chat_booking_draft"])) {
        respond("Nothing to confirm. Start with: book LAB3 tomorrow 10-11", ["book LAB3 tomorrow 10-11"]);
    }

    $d = $_SESSION["chat_booking_draft"];

    $result = create_booking($conn, $user_id, (int)$d["room_id"], $d["date"], $d["start_time"], $d["end_time"]);

    if (!$result["ok"]) {
        unset($_SESSION["chat_booking_draft"]);
        respond("❌ Booking failed: {$result['error']}\nTry another slot.", ["available {$d['date']} {$d['start_time']}-{$d['end_time']}", "book {$d['room_name']} tomorrow 10-11"]);
    }

    $bid = (int)$result["booking_id"];
    $qr = "/room_booking_ai/booking/booking_success.php?id=" . $bid;

    unset($_SESSION["chat_booking_draft"]);

    respond(
        "✅ Booked successfully!\nBooking ID: $bid\nQR Page: $qr\n\nYou can open the QR page anytime to download it.",
        ["my bookings", "download qr $bid"]
    );
}

/* Cancel draft */
if ($msg_lc === "no" || $msg_lc === "cancel") {
    unset($_SESSION["chat_booking_draft"]);
    respond("Okay, I cancelled the booking request.", ["book LAB3 tomorrow 10-11", "my bookings"]);
}

// My bookings
if (strpos($msg_lc, "my booking") !== false || $msg_lc === "my bookings") {
    $q = mysqli_query($conn, "
        SELECT b.booking_id, r.room_name, b.date, b.start_time, b.end_time
        FROM bookings b
        JOIN rooms r ON b.room_id = r.room_id
        WHERE b.user_id = $user_id
        ORDER BY b.date, b.start_time
        LIMIT 10
    ");

    if (!$q || mysqli_num_rows($q) === 0) {
        respond("You have no bookings yet.", ["recommend for 10 projector today 2pm-3pm", "book LAB3 tomorrow 10-11"]);
    }

    $lines = [];
    $firstId = null;
    while ($row = mysqli_fetch_assoc($q)) {
        if ($firstId === null) $firstId = $row['booking_id'];
        $lines[] = "• ID {$row['booking_id']} — {$row['room_name']} — {$row['date']} ({$row['start_time']} - {$row['end_time']})";
    }

    $quickCancel = $firstId ? ("cancel booking " . $firstId) : "cancel booking 42";
    $quickQr = $firstId ? ("download qr " . $firstId) : "download qr 42";

    respond("Here are your latest bookings:\n" . implode("\n", $lines), [$quickCancel, $quickQr, "book LAB3 tomorrow 10-11"]);
}

// Cancel booking
if (preg_match('/cancel\s+booking\s+(\d+)/i', $msg, $m) || preg_match('/cancel\s+(\d+)/i', $msg, $m)) {
    $bid = (int)$m[1];

    if ($role !== "admin") {
        $own = mysqli_query($conn, "SELECT booking_id FROM bookings WHERE booking_id=$bid AND user_id=$user_id LIMIT 1");
        if (!$own || mysqli_num_rows($own) === 0) {
            respond("You can only cancel your own bookings. Try: my bookings");
        }
    }

    mysqli_query($conn, "DELETE FROM checkin_logs WHERE booking_id=$bid");
    $del = mysqli_query($conn, "DELETE FROM bookings WHERE booking_id=$bid");

    if ($del) respond("✅ Booking ID $bid cancelled successfully.", ["my bookings", "available today 2pm-3pm"]);
    respond("❌ Failed to cancel booking ID $bid. (Maybe it doesn't exist.)");
}

// Availability
if (strpos($msg_lc, "available") !== false || strpos($msg_lc, "availability") !== false) {
    $date = parse_date_from_text($msg_lc);
    list($start, $end) = parse_time_range($msg_lc);

    if ($start === "" || $end === "") {
        respond("Tell me the time range like: available today 2pm-3pm\n(You can also type 7.30pm-8.00pm)", ["available today 2pm-3pm"]);
    }

    $stmt = mysqli_prepare($conn, "
        SELECT r.room_id, r.room_name, r.capacity, r.equipment
        FROM rooms r
        WHERE r.status='active'
        AND NOT EXISTS (
            SELECT 1 FROM bookings b
            WHERE b.room_id = r.room_id
              AND b.date = ?
              AND b.status IN ('pending','checked_in')
              AND (b.start_time < ? AND b.end_time > ?)
        )
        ORDER BY r.capacity ASC
    ");
    mysqli_stmt_bind_param($stmt, "sss", $date, $end, $start);
    mysqli_stmt_execute($stmt);
    $res = mysqli_stmt_get_result($stmt);

    if (!$res || mysqli_num_rows($res) === 0) {
        respond("No rooms available on $date ($start - $end). Try another time.", ["available tomorrow 10-11"]);
    }

    $out = [];
    while ($r = mysqli_fetch_assoc($res)) {
        $out[] = "• {$r['room_name']} (Cap: {$r['capacity']}, Equip: {$r['equipment']})";
    }

    respond("Available rooms on $date ($start - $end):\n" . implode("\n", $out), ["book LAB3 $date $start-$end"]);
}

// Recommendation
if (strpos($msg_lc, "recommend") !== false || strpos($msg_lc, "suggest") !== false) {

    $date = parse_date_from_text($msg_lc);
    list($start, $end) = parse_time_range($msg_lc);

    $cap = extract_capacity($msg_lc);
    $equipList = extract_equipment($msg);

    if ($start === "" || $end === "") {
        respond("Tell me the time range like: recommend for 15 projector + whiteboard tomorrow 10-11\n(You can also type 7.30pm-8.00pm)", ["recommend for 15 projector + whiteboard tomorrow 10-11"]);
    }
    if ($cap <= 0) {
        respond("Tell me expected capacity like: recommend for 15 projector + whiteboard tomorrow 10-11", ["recommend for 15 projector + whiteboard tomorrow 10-11"]);
    }

    $stmt = mysqli_prepare($conn, "
        SELECT r.room_id, r.room_name, r.capacity, r.equipment, r.location
        FROM rooms r
        WHERE r.status='active'
        AND r.capacity >= ?
        AND NOT EXISTS (
            SELECT 1 FROM bookings b
            WHERE b.room_id = r.room_id
              AND b.date = ?
              AND b.status IN ('pending','checked_in')
              AND (b.start_time < ? AND b.end_time > ?)
        )
    ");
    mysqli_stmt_bind_param($stmt, "isss", $cap, $date, $end, $start);
    mysqli_stmt_execute($stmt);
    $res = mysqli_stmt_get_result($stmt);

    $rooms = [];
    while ($r = mysqli_fetch_assoc($res)) $rooms[] = $r;

    if (count($rooms) === 0) {
        respond("No available rooms match that request (cap $cap) on $date ($start-$end).", ["available $date $start-$end"]);
    }

    $scored = [];
    foreach ($rooms as $r) {
        $score = 0;
        $reasons = [];

        $extra = (int)$r["capacity"] - $cap;
        if ($extra === 0) { $score += 40; $reasons[] = "Perfect capacity match."; }
        elseif ($extra <= 5) { $score += 30; $reasons[] = "Close capacity fit (+$extra)."; }
        elseif ($extra <= 15) { $score += 18; $reasons[] = "Capacity fits (+$extra)."; }
        else { $score += 8; $reasons[] = "Capacity fits but oversized (+$extra)."; }

        if (count($equipList) > 0) {
            if (room_has_all_equipment($r["equipment"], $equipList)) {
                $score += 45;
                $reasons[] = "All required equipment available (" . implode(", ", $equipList) . ").";
            } else {
                $roomEquip = strtoupper($r["equipment"] ?? "");
                $match = 0;
                foreach ($equipList as $e) {
                    if (strpos($roomEquip, strtoupper($e)) !== false) $match++;
                }
                if ($match > 0) {
                    $score += 20;
                    $reasons[] = "Some equipment available ($match/" . count($equipList) . ").";
                } else {
                    $score -= 30;
                    $reasons[] = "Required equipment not available.";
                }
            }
        } else {
            $score += 8;
            $reasons[] = "No equipment constraint.";
        }

        if (!empty($r["location"])) $score += 2;

        $scored[] = [
            "room_id" => (int)$r["room_id"],
            "room_name" => $r["room_name"],
            "capacity" => (int)$r["capacity"],
            "equipment" => $r["equipment"],
            "score" => $score,
            "explain" => implode(" ", $reasons)
        ];
    }

    usort($scored, fn($a,$b) => $b["score"] <=> $a["score"]);
    $best = $scored[0];

    $equipText = (count($equipList) > 0) ? ("Required equipment: " . implode(", ", $equipList)) : "No equipment required.";

    $reply =
        "✅ Best recommendation for $cap people on $date ($start-$end)\n"
        . $equipText . "\n\n"
        . "• {$best['room_name']} (Cap: {$best['capacity']}, Equip: {$best['equipment']})\n"
        . "Why: {$best['explain']}\n\n"
        . "Want me to book it? Type:\nbook {$best['room_name']} $date $start-$end";

    respond($reply, ["book {$best['room_name']} $date $start-$end", "available $date $start-$end", "my bookings"]);
}

// Fallback
respond(
    "I didn't catch that. Try:\n• available today 2pm-3pm\n• recommend for 15 projector + whiteboard tomorrow 10-11\n• book LAB3 tomorrow 10-11\n• my bookings\n• download qr 42\n• cancel booking 42",
    ["available today 2pm-3pm", "recommend for 15 projector + whiteboard tomorrow 10-11", "book LAB3 tomorrow 10-11", "my bookings"]
);
