<?php
session_start();
if (!isset($_SESSION["user_id"])) {
    header("Location: ../auth/login.php");
    exit;
}

require "../config/db.php";

$user_id = (int)$_SESSION["user_id"];

/* Ensure MySQL timezone Malaysia (important for NOW() comparisons) */
mysqli_query($conn, "SET time_zone = '+08:00'");

/* AUTO-EXPIRE: only for this user  */
mysqli_query($conn, "
    UPDATE bookings
    SET status = 'expired'
    WHERE user_id = $user_id
      AND (status IS NULL OR status = 'pending')
      AND CONCAT(date, ' ', end_time) < NOW()
");

$bookings = mysqli_query($conn, "
    SELECT 
        b.booking_id,
        b.date,
        b.start_time,
        b.end_time,
        COALESCE(b.status, 'pending') AS status,
        b.check_in_time,
        r.room_name
    FROM bookings b
    JOIN rooms r ON b.room_id = r.room_id
    WHERE b.user_id = $user_id
    ORDER BY b.date DESC, b.start_time DESC
");
?>

<?php require "../includes/header.php"; ?>
<?php require "../includes/navbar.php"; ?>

<div class="container">
    <h3>My Bookings</h3>

    <?php if (isset($_GET["success"]) && $_GET["success"] === "cancelled"): ?>
        <div class="alert alert-success">Booking cancelled successfully.</div>
    <?php endif; ?>

    <?php if (isset($_GET["error"])): ?>
        <div class="alert alert-danger">
            <?php
            if ($_GET["error"] === "cannot_cancel") echo "Cannot cancel this booking (already checked-in / cancelled / expired).";
            else echo "Something went wrong.";
            ?>
        </div>
    <?php endif; ?>

    <table class="table table-bordered mt-3">
        <thead class="table-dark">
            <tr>
                <th>Room</th>
                <th>Date</th>
                <th>Start</th>
                <th>End</th>
                <th>Status</th>
                <th>Check-in Time</th>
                <th>QR Code</th>
                <th>Action</th>
            </tr>
        </thead>

        <tbody>
        <?php
        if (!$bookings || mysqli_num_rows($bookings) === 0) {
            echo "<tr>
                    <td colspan='8' class='text-center text-muted'>No bookings found.</td>
                  </tr>";
        } else {
            while ($b = mysqli_fetch_assoc($bookings)) {

                $bookingId = (int)$b["booking_id"];
                $status = strtolower($b["status"] ?? "pending");

                // Badge style
                $badge = "secondary";
                $label = ucfirst($status);

                if ($status === "pending") {
                    $badge = "primary";
                    $label = "Pending";
                } elseif ($status === "checked_in") {
                    $badge = "success";
                    $label = "Checked-in";
                } elseif ($status === "cancelled") {
                    $badge = "secondary";
                    $label = "Cancelled";
                } elseif ($status === "expired") {
                    $badge = "warning";
                    $label = "Expired";
                }

                $checkin = $b["check_in_time"] ? htmlspecialchars($b["check_in_time"]) : "-";

                // Only pending bookings can be cancelled
                $canCancel = ($status === "pending");

                // ✅ QR paths (same filename pattern as booking_success.php)
                $qrFilename = "booking_" . $bookingId . ".png";
                $qrUrl = "../qrcodes/" . $qrFilename; // browser URL
                $qrDiskPath = __DIR__ . "/../qrcodes/" . $qrFilename; // actual file path

                echo "<tr>
                        <td>".htmlspecialchars($b["room_name"])."</td>
                        <td>".htmlspecialchars($b["date"])."</td>
                        <td>".htmlspecialchars($b["start_time"])."</td>
                        <td>".htmlspecialchars($b["end_time"])."</td>
                        <td><span class='badge bg-{$badge}'>{$label}</span></td>
                        <td>{$checkin}</td>
                        <td>";

                // QR column UI
                if (file_exists($qrDiskPath)) {
                    echo "
                        <div style='display:flex; gap:10px; align-items:center;'>
                            <img src='".htmlspecialchars($qrUrl)."' style='width:70px; border:1px solid #ccc; padding:3px; background:#fff;'>
                            <div>
                                <a class='btn btn-sm btn-outline-primary' href='".htmlspecialchars($qrUrl)."' target='_blank'>View</a>
                                <a class='btn btn-sm btn-primary mt-1' href='".htmlspecialchars($qrUrl)."' download>Download</a>
                            </div>
                        </div>
                    ";
                } else {
                    // If QR not generated yet, open booking_success.php which generates and shows it
                    echo "
                        <a class='btn btn-sm btn-outline-dark' target='_blank'
                           href='booking_success.php?id=".$bookingId."'>
                           Generate QR
                        </a>
                    ";
                }

                echo "</td>
                        <td>";

                // Action column
                if ($canCancel) {
                    echo "<a href='cancel_booking.php?id=".urlencode($bookingId)."'
                              class='btn btn-danger btn-sm'
                              onclick='return confirm(\"Cancel this booking?\")'>
                              Cancel
                          </a>";
                } else {
                    echo "<button class='btn btn-outline-secondary btn-sm' disabled>Cannot cancel</button>";
                }

                echo "  </td>
                      </tr>";
            }
        }
        ?>
        </tbody>
    </table>
</div>

<?php require "../includes/footer.php"; ?>

