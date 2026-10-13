<?php
// Ensure session is active
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

/* AUTO-EXPIRE OLD BOOKINGS (Malaysia time)
   This safely marks past bookings as 'expired'
*/
@include_once __DIR__ . "/../booking/expire_bookings.php";
?>

<nav class="navbar navbar-expand-lg navbar-dark bg-dark mb-4">
  <div class="container-fluid">
    <a class="navbar-brand" href="/room_booking_ai/index.php">Room Booking AI</a>

    <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarContent">
      <span class="navbar-toggler-icon"></span>
    </button>

    <div class="collapse navbar-collapse" id="navbarContent">
      <ul class="navbar-nav ms-auto">

        <?php if (isset($_SESSION["user_id"])) { ?>

            <!-- STUDENT MENU -->
            <?php if ($_SESSION["role"] === "student") { ?>
                <li class="nav-item">
                    <a class="nav-link" href="/room_booking_ai/booking/book_room.php">Book Room</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="/room_booking_ai/booking/my_bookings.php">My Bookings</a>
                </li>
                <li class="nav-item">
                     <a class="nav-link" href="/room_booking_ai/profile/profile.php">Profile</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="/room_booking_ai/student/profile.php">Student Dashboard</a>
                </li>



                <!-- Chatbot -->
                <li class="nav-item">
                    <a class="nav-link" href="/room_booking_ai/chatbot/chatbot.php">Chatbot</a>
                </li>
            <?php } ?>

            <!-- ADMIN MENU -->
            <?php if ($_SESSION["role"] === "admin") { ?>
                <li class="nav-item">
                    <a class="nav-link" href="/room_booking_ai/rooms/list_rooms.php">Manage Rooms</a>
                </li>

                <li class="nav-item">
                    <a class="nav-link" href="/room_booking_ai/booking/scan_qr.php">QR Scanner</a>
                </li>

                <li class="nav-item">
                    <a class="nav-link" href="/room_booking_ai/admin/checkin_logs.php">Check-In Logs</a>
                </li>

                <li class="nav-item">
                    <a class="nav-link" href="/room_booking_ai/admin/dashboard.php">Dashboard</a>
                </li>

                <li class="nav-item">
                    <a class="nav-link" href="/room_booking_ai/admin/manage_bookings.php">Manage Bookings</a>
                </li>

                <li class="nav-item">
                    <a class="nav-link" href="/room_booking_ai/admin/calendar.php">Calendar</a>
                </li>

               
            <?php } ?>

            <!-- LOGOUT -->
            <li class="nav-item">
                <a class="nav-link" href="/room_booking_ai/auth/logout.php">Logout</a>
            </li>

        <?php } else { ?>

            <!-- GUEST MENU -->
            <li class="nav-item">
                <a class="nav-link" href="/room_booking_ai/auth/login.php">Login</a>
            </li>
            <li class="nav-item">
                <a class="nav-link" href="/room_booking_ai/auth/register.php">Register</a>
            </li>

        <?php } ?>

      </ul>
    </div>
  </div>
</nav>
