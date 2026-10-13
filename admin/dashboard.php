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

<script>
// Fetch dashboard data
fetch("dashboard_data.php")
    .then(res => res.json())
    .then(data => {

        // ----- STAT CARDS -----
        document.getElementById("statCards").innerHTML = `
            <div class="col-md-3">
                <div class="card shadow p-3">
                    <h5>Total Users</h5>
                    <h3>${data.total_users}</h3>
                </div>
            </div>

            <div class="col-md-3">
                <div class="card shadow p-3">
                    <h5>Total Rooms</h5>
                    <h3>${data.total_rooms}</h3>
                </div>
            </div>

            <div class="col-md-3">
                <div class="card shadow p-3">
                    <h5>Total Bookings</h5>
                    <h3>${data.total_bookings}</h3>
                </div>
            </div>

            <div class="col-md-3">
                <div class="card shadow p-3">
                    <h5>Total Check-ins</h5>
                    <h3>${data.total_checkins}</h3>
                </div>
            </div>

            <div class="col-md-12 mt-4">
                <div class="card shadow p-3">
                    <h5>Today’s Check-ins</h5>
                    <h3>${data.today_checkins}</h3>
                </div>
            </div>
        `;


        // ----- ROOM CHART -----
        new Chart(document.getElementById("roomChart"), {
            type: "bar",
            data: {
                labels: data.room_usage.map(x => x.room_name),
                datasets: [{
                    label: "Bookings",
                    data: data.room_usage.map(x => x.total),
                    backgroundColor: "rgba(54,162,235,0.6)"
                }]
            }
        });

        // ----- DAILY BOOKING CHART -----
        new Chart(document.getElementById("dailyChart"), {
            type: "line",
            data: {
                labels: data.daily_bookings.map(x => x.date),
                datasets: [{
                    label: "Bookings",
                    data: data.daily_bookings.map(x => x.total),
                    borderColor: "rgba(255,99,132,1)",
                    fill: false
                }]
            }
        });

    });
</script>
