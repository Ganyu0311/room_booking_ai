<?php
session_start();
if (!isset($_SESSION["user_id"])) {
    header("Location: ../auth/login.php");
    exit;
}
?>

<?php require "../includes/header.php"; ?>
<?php require "../includes/navbar.php"; ?>

<div class="container">
    <div class="card shadow">
        <div class="card-body">
            <h3>User Dashboard</h3>
            <p>Welcome, <b><?php echo $_SESSION["name"]; ?></b>!</p>
            <p>You are logged in as <b><?php echo $_SESSION["role"]; ?></b>.</p>
        </div>
    </div>
</div>

<?php require "../includes/footer.php"; ?>
