<?php
session_start();
if (!isset($_SESSION["user_id"]) || $_SESSION["role"] != "admin") {
    header("Location: ../auth/login.php");
    exit;
}

require "../config/db.php";
$message = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $name = trim($_POST["room_name"] ?? "");
    $capacity = (int)($_POST["capacity"] ?? 0);
    $equipment = trim($_POST["equipment"] ?? "");
    $location = trim($_POST["location"] ?? "");

    if ($name === "" || $capacity <= 0) {
        $message = "Please enter a room name and a capacity greater than 0.";
    } else {
        $stmt = mysqli_prepare($conn, "INSERT INTO rooms (room_name, capacity, equipment, location) VALUES (?, ?, ?, ?)");
        mysqli_stmt_bind_param($stmt, "siss", $name, $capacity, $equipment, $location);

        if (mysqli_stmt_execute($stmt)) {
            $message = "Room added successfully!";
        } else {
            $message = "Could not add the room. Please try again.";
        }
    }
}
?>

<?php require "../includes/header.php"; ?>
<?php require "../includes/navbar.php"; ?>

<div class="container">
    <div class="card shadow">
        <div class="card-body">
            <h3>Add New Room</h3>
            <p><?php echo htmlspecialchars($message); ?></p>

            <form method="POST">
                <label>Room Name</label>
                <input type="text" name="room_name" class="form-control" required>

                <label class="mt-3">Capacity</label>
                <input type="number" name="capacity" class="form-control" required>

                <label class="mt-3">Equipment (comma separated)</label>
                <input type="text" name="equipment" class="form-control">

                <label class="mt-3">Location</label>
                <input type="text" name="location" class="form-control">

                <button type="submit" class="btn btn-primary mt-4">Add Room</button>
            </form>
        </div>
    </div>
</div>

<?php require "../includes/footer.php"; ?>
