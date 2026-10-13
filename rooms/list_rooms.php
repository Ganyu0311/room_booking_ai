<?php
session_start();
if (!isset($_SESSION["user_id"]) || $_SESSION["role"] != "admin") {
    header("Location: ../auth/login.php");
    exit;
}

require "../config/db.php";

$result = mysqli_query($conn, "SELECT * FROM rooms");
?>

<?php require "../includes/header.php"; ?>
<?php require "../includes/navbar.php"; ?>

<div class="container">
    <h3>All Rooms</h3>
    <a href="add_room.php" class="btn btn-success mb-3">Add New Room</a>

    <table class="table table-bordered">
        <tr>
            <th>ID</th>
            <th>Room Name</th>
            <th>Capacity</th>
            <th>Equipment</th>
            <th>Location</th>
            <th>Action</th>
        </tr>

        <?php while ($row = mysqli_fetch_assoc($result)) { ?>
        <tr>
            <td><?php echo $row["room_id"]; ?></td>
            <td><?php echo $row["room_name"]; ?></td>
            <td><?php echo $row["capacity"]; ?></td>
            <td><?php echo $row["equipment"]; ?></td>
            <td><?php echo $row["location"]; ?></td>
            <td>
                <a href="edit_room.php?id=<?php echo $row["room_id"]; ?>" class="btn btn-primary btn-sm">Edit</a>
            </td>
        </tr>
        <?php } ?>
    </table>
</div>

<?php require "../includes/footer.php"; ?>
