<?php
session_start();
if (!isset($_SESSION["user_id"]) || $_SESSION["role"] != "admin") {
    header("Location: ../auth/login.php");
    exit;
}

require "../config/db.php";

$result = mysqli_query($conn, "
    SELECT l.*, 
           u.name AS username,
           r.room_name,
           b.date,
           b.start_time,
           b.end_time
    FROM checkin_logs l
    JOIN users u ON l.user_id = u.user_id
    JOIN rooms r ON l.room_id = r.room_id
    JOIN bookings b ON l.booking_id = b.booking_id
    ORDER BY l.checkin_time DESC
");
?>

<?php require "../includes/header.php"; ?>
<?php require "../includes/navbar.php"; ?>

<div class="container mt-4">
    <div class="card shadow">
        <div class="card-body">

            <h3>Check-In Logs</h3>

            <table class="table table-bordered mt-3">
                <thead>
                    <tr>
                        <th>Log ID</th>
                        <th>User</th>
                        <th>Room</th>
                        <th>Date</th>
                        <th>Time</th>
                        <th>Check-In Time</th>
                    </tr>
                </thead>

                <tbody>
                    <?php while ($row = mysqli_fetch_assoc($result)) { ?>
                        <tr>
                            <td><?= $row['log_id'] ?></td>
                            <td><?= $row['username'] ?></td>
                            <td><?= $row['room_name'] ?></td>
                            <td><?= $row['date'] ?></td>
                            <td><?= $row['start_time'] ?> - <?= $row['end_time'] ?></td>
                            <td><?= $row['checkin_time'] ?></td>
                        </tr>
                    <?php } ?>
                </tbody>
            </table>

        </div>
    </div>
</div>

<?php require "../includes/footer.php"; ?>


