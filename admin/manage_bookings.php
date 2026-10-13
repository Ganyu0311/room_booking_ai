<?php
session_start();
if (!isset($_SESSION["user_id"]) || ($_SESSION["role"] ?? "") !== "admin") {
    header("Location: ../auth/login.php");
    exit;
}

require "../config/db.php";
require "../includes/header.php";
require "../includes/navbar.php";

/*  Ensure MySQL timezone Malaysia (important for NOW() comparisons) */
mysqli_query($conn, "SET time_zone = '+08:00'");

/* AUTO-EXPIRE: any PENDING booking whose end datetime already passed */
mysqli_query($conn, "
    UPDATE bookings
    SET status = 'expired'
    WHERE (status IS NULL OR status = 'pending')
      AND CONCAT(date, ' ', end_time) < NOW()
");

/* Filter: all | pending | checked_in | cancelled | expired */
$filter = $_GET["filter"] ?? "all";
$allowedFilters = ["all", "pending", "checked_in", "cancelled", "expired"];
if (!in_array($filter, $allowedFilters)) $filter = "all";

$where = "";
if ($filter !== "all") {
    $f = mysqli_real_escape_string($conn, $filter);
    $where = "WHERE COALESCE(b.status, 'pending') = '$f'";
}
?>

<div class="container mt-4">
    <h2>📘 Manage All Bookings</h2>
    <p class="text-muted">Admins can cancel any booking here.</p>

    <!-- SUCCESS / ERROR MESSAGES -->
    <?php if (isset($_GET["success"])): ?>
        <div class="alert alert-success">Booking cancelled successfully.</div>
    <?php endif; ?>

    <?php if (isset($_GET["error"])): ?>
        <div class="alert alert-danger">Something went wrong.</div>
    <?php endif; ?>

    <!-- FILTER BUTTONS -->
    <div class="mb-3">
        <a href="manage_bookings.php?filter=all" class="btn btn-outline-dark btn-sm <?= $filter==="all" ? "active" : "" ?>">All</a>
        <a href="manage_bookings.php?filter=pending" class="btn btn-outline-primary btn-sm <?= $filter==="pending" ? "active" : "" ?>">Pending</a>
        <a href="manage_bookings.php?filter=checked_in" class="btn btn-outline-success btn-sm <?= $filter==="checked_in" ? "active" : "" ?>">Checked-in</a>
        <a href="manage_bookings.php?filter=cancelled" class="btn btn-outline-secondary btn-sm <?= $filter==="cancelled" ? "active" : "" ?>">Cancelled</a>
        <a href="manage_bookings.php?filter=expired" class="btn btn-outline-warning btn-sm <?= $filter==="expired" ? "active" : "" ?>">Expired</a>
    </div>

    <table class="table table-bordered table-striped mt-3">
        <thead class="table-dark">
            <tr>
                <th>Booking ID</th>
                <th>Room</th>
                <th>User</th>
                <th>Date</th>
                <th>Time</th>
                <th>Status</th>
                <th>Check-in Time</th>
                <th>Action</th>
            </tr>
        </thead>

        <tbody>
        <?php
        $sql = "
            SELECT 
                b.booking_id,
                r.room_name,
                u.name AS user_name,
                b.date,
                b.start_time,
                b.end_time,
                COALESCE(b.status, 'pending') AS status,
                b.check_in_time
            FROM bookings b
            JOIN rooms r ON b.room_id = r.room_id
            JOIN users u ON b.user_id = u.user_id
            $where
            ORDER BY b.date DESC, b.start_time DESC
        ";

        $q = mysqli_query($conn, $sql);

        if (!$q || mysqli_num_rows($q) === 0) {
            echo "<tr><td colspan='8' class='text-center text-muted'>No bookings found.</td></tr>";
        } else {
            while ($row = mysqli_fetch_assoc($q)) {

                $status = strtolower($row["status"] ?? "pending");
                $badge = "secondary";
                $label = "Pending";

                if ($status === "pending") { $badge = "primary"; $label = "Pending"; }
                elseif ($status === "checked_in") { $badge = "success"; $label = "Checked-in"; }
                elseif ($status === "cancelled") { $badge = "secondary"; $label = "Cancelled"; }
                elseif ($status === "expired") { $badge = "warning"; $label = "Expired"; }

                $checkin = $row["check_in_time"] ? htmlspecialchars($row["check_in_time"]) : "-";

                // Only pending can be cancelled
                $canCancel = ($status === "pending");

                echo "<tr>
                    <td>".htmlspecialchars($row['booking_id'])."</td>
                    <td>".htmlspecialchars($row['room_name'])."</td>
                    <td>".htmlspecialchars($row['user_name'])."</td>
                    <td>".htmlspecialchars($row['date'])."</td>
                    <td>".htmlspecialchars($row['start_time'])." - ".htmlspecialchars($row['end_time'])."</td>
                    <td><span class='badge bg-{$badge}'>".$label."</span></td>
                    <td>{$checkin}</td>
                    <td>";

                if ($canCancel) {
                    echo "<a href='cancel_booking_admin.php?id=".urlencode($row['booking_id'])."'
                            class='btn btn-danger btn-sm'
                            onclick='return confirm(\"Cancel this booking?\")'>
                            Cancel
                          </a>";
                } else {
                    echo "<button class='btn btn-outline-secondary btn-sm' disabled>Cannot cancel</button>";
                }

                echo "</td></tr>";
            }
        }
        ?>
        </tbody>
    </table>
</div>

<?php require "../includes/footer.php"; ?>


