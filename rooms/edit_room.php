<?php
session_start();
if (!isset($_SESSION["user_id"]) || ($_SESSION["role"] ?? "") !== "admin") {
    header("Location: ../auth/login.php");
    exit;
}

require "../config/db.php";

$room_id = $_GET["id"] ?? null;
if (!$room_id) {
    die("Missing room id.");
}

// UPDATE room
$message = "";
if ($_SERVER["REQUEST_METHOD"] === "POST") {

    $room_name = trim($_POST["room_name"] ?? "");
    $capacity  = (int)($_POST["capacity"] ?? 0);
    $equipment = trim($_POST["equipment"] ?? "");
    $location  = trim($_POST["location"] ?? "");

    if ($room_name === "" || $capacity <= 0) {
        $message = "<div class='alert alert-danger'>Room name and capacity are required.</div>";
    } else {
        $stmt = mysqli_prepare($conn, "
            UPDATE rooms
            SET room_name = ?, capacity = ?, equipment = ?, location = ?
            WHERE room_id = ?
        ");
        mysqli_stmt_bind_param($stmt, "sissi", $room_name, $capacity, $equipment, $location, $room_id);

        if (mysqli_stmt_execute($stmt)) {
            $message = "<div class='alert alert-success'>Room updated successfully!</div>";
        } else {
            $message = "<div class='alert alert-danger'>Update failed: " . htmlspecialchars(mysqli_error($conn)) . "</div>";
        }
        mysqli_stmt_close($stmt);
    }
}

// FETCH room data
$stmt2 = mysqli_prepare($conn, "SELECT * FROM rooms WHERE room_id = ?");
mysqli_stmt_bind_param($stmt2, "i", $room_id);
mysqli_stmt_execute($stmt2);
$result = mysqli_stmt_get_result($stmt2);
$room = mysqli_fetch_assoc($result);
mysqli_stmt_close($stmt2);

if (!$room) {
    die("Room not found.");
}

require "../includes/header.php";
require "../includes/navbar.php";
?>

<div class="container mt-4">
    <div class="card shadow">
        <div class="card-body">
            <h3>Edit Room</h3>
            <p class="text-muted">Update room details below.</p>

            <?= $message ?>

            <form method="POST">
                <label class="mt-2">Room Name</label>
                <input type="text" name="room_name" class="form-control" required
                       value="<?= htmlspecialchars($room["room_name"]) ?>">

                <label class="mt-3">Capacity</label>
                <input type="number" name="capacity" class="form-control" required min="1"
                       value="<?= htmlspecialchars($room["capacity"]) ?>">

                <label class="mt-3">Equipment</label>
                <input type="text" name="equipment" class="form-control"
                       value="<?= htmlspecialchars($room["equipment"]) ?>">

                <label class="mt-3">Location</label>
                <input type="text" name="location" class="form-control"
                       value="<?= htmlspecialchars($room["location"]) ?>">

                <button type="submit" class="btn btn-primary mt-4">Update Room</button>
                <a href="list_rooms.php" class="btn btn-secondary mt-4">Back</a>
            </form>
        </div>
    </div>
</div>

<?php require "../includes/footer.php"; ?>
