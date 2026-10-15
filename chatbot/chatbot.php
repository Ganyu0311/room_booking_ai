<?php
session_start();
if (!isset($_SESSION["user_id"])) {
    header("Location: ../auth/login.php");
    exit;
}

require "../includes/header.php";
require "../includes/navbar.php";
?>


<div class="container chat-wrap">
  <div class="card shadow">
    <div class="card-body">
      <h3 class="mb-1">💬 Room Booking Chatbot</h3>
      <div class="small-hint mb-3">
        Try:
        <b>available today 2pm-3pm</b>,
        <b>recommend 15 projector tomorrow 10-11</b>,
        <b>my bookings</b>,
        <b>cancel booking 42</b>,
        <b>book LAB3 tomorrow 10-11</b>,
        <b>download qr 69</b>
      </div>

      <div id="chatBox" class="chat-box"></div>

      <div class="quick" id="quickActions" style="display:none;"></div>

      <form id="chatForm" class="mt-3 d-flex gap-2">
        <input id="chatInput" class="form-control" placeholder="Type a message..." autocomplete="off" />
        <button class="btn btn-primary" type="submit">Send</button>
      </form>

    </div>
  </div>
</div>

<?php require "../includes/footer.php"; ?>

<script src="/room_booking_ai/assets/js/chatbot.js"></script>
