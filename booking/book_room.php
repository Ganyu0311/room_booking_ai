<?php
session_start();
if (!isset($_SESSION["user_id"])) {
    header("Location: ../auth/login.php");
    exit;
}

require "../config/db.php";

// Set Malaysia timezone
date_default_timezone_set("Asia/Kuala_Lumpur");

// Force MySQL timezone Malaysia (important for NOW() comparisons)
mysqli_query($conn, "SET time_zone = '+08:00'");

// Fetch all rooms (for dropdown)
$rooms = mysqli_query($conn, "SELECT * FROM rooms");

$message = "";

// ------------------------------
// SETTINGS (RESTRICTIONS)
// ------------------------------
$MAX_UPCOMING_ACTIVE = 5;   // max future bookings per user
$MAX_PER_DAY = 2;           // max bookings per user per day
// ------------------------------

// Handle booking submission
if ($_SERVER["REQUEST_METHOD"] == "POST") {

    $user_id = (int)($_SESSION["user_id"]);
    $room_id = (int)($_POST["room_id"] ?? 0);
    $date = trim($_POST["date"] ?? "");
    $start_time = trim($_POST["start_time"] ?? "");
    $end_time = trim($_POST["end_time"] ?? "");

    // Basic validation
    if ($room_id <= 0 || $date === "" || $start_time === "" || $end_time === "") {
        $message = "<div class='alert alert-danger'>Please fill in all required fields.</div>";
    } else {

        $selected_start = strtotime("$date $start_time");
        $selected_end = strtotime("$date $end_time");
        $now = time();

        // VALIDATION A — End > Start
        if ($selected_end <= $selected_start) {
            $message = "<div class='alert alert-danger'>End time must be later than start time.</div>";
        }
        // VALIDATION B — No booking in the past
        elseif ($selected_start < $now) {
            $message = "<div class='alert alert-danger'>You cannot book a past time slot.</div>";
        }
        else {

            // -----------------------------------------
            // RESTRICTION 1: MAX UPCOMING ACTIVE
            // Active = pending/checked_in AND not ended yet
            // -----------------------------------------
            $sqlUpcoming = "
                SELECT COUNT(*) AS total
                FROM bookings
                WHERE user_id = ?
                  AND status IN ('pending','checked_in')
                  AND (
                        date > CURDATE()
                        OR (date = CURDATE() AND end_time > CURTIME())
                      )
            ";
            $stmt = mysqli_prepare($conn, $sqlUpcoming);
            mysqli_stmt_bind_param($stmt, "i", $user_id);
            mysqli_stmt_execute($stmt);
            $res = mysqli_stmt_get_result($stmt);
            $row = mysqli_fetch_assoc($res);
            $upcomingCount = (int)($row["total"] ?? 0);

            if ($upcomingCount >= $MAX_UPCOMING_ACTIVE) {
                $message = "<div class='alert alert-danger'>
                    You already have <b>$upcomingCount</b> upcoming bookings.
                    Limit is <b>$MAX_UPCOMING_ACTIVE</b>. Please cancel/finish one first.
                </div>";
            } else {

                // -----------------------------------------
                // RESTRICTION 2: MAX PER DAY
                // -----------------------------------------
                $sqlPerDay = "
                    SELECT COUNT(*) AS total
                    FROM bookings
                    WHERE user_id = ?
                      AND date = ?
                      AND status IN ('pending','checked_in')
                ";
                $stmt = mysqli_prepare($conn, $sqlPerDay);
                mysqli_stmt_bind_param($stmt, "is", $user_id, $date);
                mysqli_stmt_execute($stmt);
                $res = mysqli_stmt_get_result($stmt);
                $row = mysqli_fetch_assoc($res);
                $perDayCount = (int)($row["total"] ?? 0);

                if ($perDayCount >= $MAX_PER_DAY) {
                    $message = "<div class='alert alert-danger'>
                        You already have <b>$perDayCount</b> bookings on <b>$date</b>.
                        Daily limit is <b>$MAX_PER_DAY</b>.
                    </div>";
                } else {

                    // -------------------------------------------------------
                    // RESTRICTION 3: SAME USER SAME ROOM SAME DAY (block)
                    // -------------------------------------------------------
                    $sqlSameRoomDay = "
                        SELECT 1
                        FROM bookings
                        WHERE user_id = ?
                          AND room_id = ?
                          AND date = ?
                          AND status IN ('pending','checked_in')
                        LIMIT 1
                    ";
                    $stmt = mysqli_prepare($conn, $sqlSameRoomDay);
                    mysqli_stmt_bind_param($stmt, "iis", $user_id, $room_id, $date);
                    mysqli_stmt_execute($stmt);
                    $res = mysqli_stmt_get_result($stmt);

                    if (mysqli_num_rows($res) > 0) {
                        $message = "<div class='alert alert-danger'>
                            You already booked this room on <b>$date</b>. Please choose another room or another day.
                        </div>";
                    } else {

                        // -------------------------------------------------------
                        // CONFLICT CHECK (ignore cancelled/expired)
                        // -------------------------------------------------------
                        $sqlConflict = "
                            SELECT 1 FROM bookings
                            WHERE room_id = ?
                              AND date = ?
                              AND status IN ('pending','checked_in')
                              AND (? < end_time)
                              AND (? > start_time)
                            LIMIT 1
                        ";
                        $stmt = mysqli_prepare($conn, $sqlConflict);
                        mysqli_stmt_bind_param($stmt, "isss", $room_id, $date, $start_time, $end_time);
                        mysqli_stmt_execute($stmt);
                        $conf = mysqli_stmt_get_result($stmt);

                        if (mysqli_num_rows($conf) > 0) {
                            $message = "<div class='alert alert-danger'>This time slot is already booked.</div>";
                        } else {

                            // INSERT BOOKING (set status pending explicitly)
                            $sqlInsert = "
                                INSERT INTO bookings (user_id, room_id, date, start_time, end_time, status)
                                VALUES (?, ?, ?, ?, ?, 'pending')
                            ";
                            $stmt = mysqli_prepare($conn, $sqlInsert);
                            mysqli_stmt_bind_param($stmt, "iisss", $user_id, $room_id, $date, $start_time, $end_time);

                            if (mysqli_stmt_execute($stmt)) {
                                $new_id = mysqli_insert_id($conn);
                                header("Location: booking_success.php?id=" . $new_id);
                                exit;
                            } else {
                                $message = "<div class='alert alert-danger'>Error booking room.</div>";
                            }
                        }
                    }
                }
            }
        }
    }
}
?>

<?php require "../includes/header.php"; ?>
<?php require "../includes/navbar.php"; ?>

<div class="container">
    <div class="card shadow">
        <div class="card-body">

            <h3>Book a Room</h3>
            <?= $message ?>

            <form method="POST" id="bookingForm">

                <!-- ROOM DROPDOWN -->
                <label class="mt-3">Choose Room</label>
                <select name="room_id" id="room_id" class="form-control" required>
                    <option value="">Select a room</option>
                    <?php while ($r = mysqli_fetch_assoc($rooms)) { ?>
                        <option value="<?= htmlspecialchars($r['room_id']) ?>">
                            <?= htmlspecialchars($r['room_name']) ?>
                        </option>
                    <?php } ?>
                </select>

                <!-- DATE -->
                <label class="mt-3">Select Date</label>
                <input type="date" id="date" name="date" class="form-control" required>

                <!-- START TIME -->
                <label class="mt-3">Start Time</label>
                <select id="start_time" name="start_time" class="form-control" required></select>

                <!-- END TIME -->
                <label class="mt-3">End Time</label>
                <select id="end_time" name="end_time" class="form-control" required></select>

                <!-- Capacity needed -->
                <label class="mt-3">Expected Capacity (people)</label>
                <input type="number" id="capacity" name="capacity" class="form-control" min="1" placeholder="e.g. 20">

                <!-- Equipment needed (MULTI CHECKBOX) -->
                <label class="mt-3">Equipment Needed (optional)</label>

                <div class="border rounded p-3">
                    <div class="form-check">
                        <input class="form-check-input equipChk" type="checkbox" value="WHITEBOARD" id="eq1">
                        <label class="form-check-label" for="eq1">WHITEBOARD</label>
                    </div>

                    <div class="form-check">
                        <input class="form-check-input equipChk" type="checkbox" value="PROJECTOR" id="eq2">
                        <label class="form-check-label" for="eq2">PROJECTOR</label>
                    </div>

                    <div class="form-check">
                        <input class="form-check-input equipChk" type="checkbox" value="SMART TV" id="eq3">
                        <label class="form-check-label" for="eq3">SMART TV</label>
                    </div>

                    <div class="form-check">
                        <input class="form-check-input equipChk" type="checkbox" value="SPEAKER SYSTEM" id="eq4">
                        <label class="form-check-label" for="eq4">SPEAKER SYSTEM</label>
                    </div>

                    <div class="form-check">
                        <input class="form-check-input equipChk" type="checkbox" value="MIC SYSTEM" id="eq5">
                        <label class="form-check-label" for="eq5">MIC SYSTEM</label>
                    </div>

                    <div class="form-check">
                        <input class="form-check-input equipChk" type="checkbox" value="VIDEO CONFERENCING" id="eq6">
                        <label class="form-check-label" for="eq6">VIDEO CONFERENCING</label>
                    </div>

                    <div class="form-check">
                        <input class="form-check-input equipChk" type="checkbox" value="DOCUMENT CAMERA" id="eq7">
                        <label class="form-check-label" for="eq7">DOCUMENT CAMERA</label>
                    </div>

                    <div class="form-check">
                        <input class="form-check-input equipChk" type="checkbox" value="GAMING PC" id="eq8">
                        <label class="form-check-label" for="eq8">GAMING PC</label>
                    </div>

                    <small class="text-muted d-block mt-2">
                        You can select multiple items. If none selected, AI will ignore equipment requirement.
                    </small>
                </div>

                <!-- AI Suggest Button -->
                <button type="button" id="aiSuggest" class="btn btn-info mt-4">
                    AI Suggest Best Room
                </button>

                <!-- AI Result -->
                <div id="aiResult" class="p-3 border rounded mt-3" style="display:none;"></div>

                <!-- LIVE AVAILABILITY -->
                <div id="liveAvailability" class="p-3 border rounded mt-4" style="display:none;"></div>

                <button class="btn btn-primary mt-4">Book Room</button>

            </form>

        </div>
    </div>
</div>

<?php require "../includes/footer.php"; ?>

<!-- Time slot filtering + room recommendations -->
<script src="/room_booking_ai/assets/js/book-room.js"></script>

