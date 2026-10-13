<?php
session_start();
if (!isset($_SESSION["user_id"])) {
    header("Location: ../auth/login.php");
    exit;
}

require "../config/db.php";

$id = (int)($_GET["id"] ?? 0);

$q = mysqli_query($conn, "
    SELECT b.*, r.room_name
    FROM bookings b
    JOIN rooms r ON b.room_id=r.room_id
    WHERE b.booking_id=$id
");

$b = mysqli_fetch_assoc($q);
if (!$b) die("Invalid check-in");
?>

<!DOCTYPE html>
<html>
<head>
  <title>Check-In Successful</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">

<div class="container mt-5">
  <div class="card shadow text-center">
    <div class="card-body">

      <h2 class="text-success mb-3">✅ Check-In Successful</h2>

      <p><b>Room:</b> <?= htmlspecialchars($b["room_name"]) ?></p>
      <p><b>Date:</b> <?= $b["date"] ?></p>
      <p><b>Time:</b> <?= $b["start_time"] ?> – <?= $b["end_time"] ?></p>
      <p><b>Checked-in at:</b> <?= $b["check_in_time"] ?></p>

      <a href="../booking/my_bookings.php" class="btn btn-primary mt-3">
        Go to My Bookings
      </a>

    </div>
  </div>
</div>

</body>
</html>
