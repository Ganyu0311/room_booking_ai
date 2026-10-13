<?php
session_start();
if (!isset($_SESSION["user_id"]) || $_SESSION["role"] !== "student") {
    header("Location: ../auth/login.php");
    exit;
}

require "../config/db.php";
require "../includes/header.php";
require "../includes/navbar.php";

$user_id = (int)$_SESSION["user_id"];

/* =========================
   USER DETAILS
========================= */
$userQ = mysqli_query($conn, "
    SELECT name, email
    FROM users
    WHERE user_id = $user_id
    LIMIT 1
");
$user = mysqli_fetch_assoc($userQ);

/* =========================
   BOOKING STATISTICS
========================= */
function countBookings($conn, $user_id, $status = null) {
    $where = "WHERE user_id = $user_id";
    if ($status) {
        $where .= " AND status = '$status'";
    }
    $q = mysqli_query($conn, "SELECT COUNT(*) AS total FROM bookings $where");
    $r = mysqli_fetch_assoc($q);
    return $r["total"] ?? 0;
}

$totalBookings   = countBookings($conn, $user_id);
$checkedInCount  = countBookings($conn, $user_id, "checked_in");
$cancelledCount  = countBookings($conn, $user_id, "cancelled");
$expiredCount    = countBookings($conn, $user_id, "expired");
?>

<div class="container mt-4">
    <h2>👤 Dashboard</h2>
    <p class="text-muted">View your account details and booking activity</p>

    <!-- USER INFO -->
    <div class="card mb-4 shadow-sm">
        <div class="card-body">
            <h5 class="card-title">Account Information</h5>
            <p><b>Name:</b> <?= htmlspecialchars($user["name"]) ?></p>
            <p><b>Email:</b> <?= htmlspecialchars($user["email"]) ?></p>
        </div>
    </div>

    <!-- BOOKING STATS -->
    <div class="row text-center">

        <div class="col-md-3 mb-3">
            <div class="card shadow-sm border-primary">
                <div class="card-body">
                    <h6>Total Bookings</h6>
                    <h3 class="text-primary"><?= $totalBookings ?></h3>
                </div>
            </div>
        </div>

        <div class="col-md-3 mb-3">
            <div class="card shadow-sm border-success">
                <div class="card-body">
                    <h6>Checked-in</h6>
                    <h3 class="text-success"><?= $checkedInCount ?></h3>
                </div>
            </div>
        </div>

        <div class="col-md-3 mb-3">
            <div class="card shadow-sm border-secondary">
                <div class="card-body">
                    <h6>Cancelled</h6>
                    <h3 class="text-secondary"><?= $cancelledCount ?></h3>
                </div>
            </div>
        </div>

        <div class="col-md-3 mb-3">
            <div class="card shadow-sm border-warning">
                <div class="card-body">
                    <h6>Expired</h6>
                    <h3 class="text-warning"><?= $expiredCount ?></h3>
                </div>
            </div>
        </div>

    </div>
</div>

<?php require "../includes/footer.php"; ?>
