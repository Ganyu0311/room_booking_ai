<?php
session_start();
if (!isset($_SESSION["user_id"]) || $_SESSION["role"] !== "admin") {
    header("Location: ../auth/login.php");
    exit;
}

require "../includes/header.php";
require "../includes/navbar.php";
?>


<!-- BOOTBOX -->
<script src="https://cdn.jsdelivr.net/npm/bootbox@5/bootbox.min.js"></script>

<!-- Tippy Tooltip -->
<link rel="stylesheet" href="https://unpkg.com/tippy.js@6/themes/light.css" />
<script src="https://unpkg.com/@popperjs/core@2"></script>
<script src="https://unpkg.com/tippy.js@6"></script>

<div class="container mt-4">
    <h2>📅 Room Availability Calendar</h2>
    <p class="text-muted">Hover or click an event to see full details.</p>
    <div id="calendar"></div>
</div>

<?php require "../includes/footer.php"; ?>

<!-- FULLCALENDAR -->
<script src="https://cdn.jsdelivr.net/npm/fullcalendar@6.1.5/index.global.min.js"></script>

<script src="/room_booking_ai/assets/js/admin-calendar.js"></script>







