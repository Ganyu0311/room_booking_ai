<?php
session_start();
if (!isset($_SESSION["user_id"]) || $_SESSION["role"] !== "admin") {
    header("Location: ../auth/login.php");
    exit;
}

require "../includes/header.php";
require "../includes/navbar.php";
?>

<div class="container mt-4">
    <h2>📊 Admin Dashboard</h2>
    <p class="text-muted">Overview of system activity</p>

    <!-- STAT CARDS -->
    <div class="row" id="statCards"></div>

    <hr class="my-4">

    <!-- CHARTS -->
    <div class="row">
        <div class="col-md-6">
            <h5>Room Usage (Total Bookings Per Room)</h5>
            <canvas id="roomChart"></canvas>
        </div>

        <div class="col-md-6">
            <h5>Bookings Per Day</h5>
            <canvas id="dailyChart"></canvas>
        </div>
    </div>

</div>

<?php require "../includes/footer.php"; ?>

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

<script src="/room_booking_ai/assets/js/admin-dashboard.js"></script>
