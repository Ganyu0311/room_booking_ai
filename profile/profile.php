<?php
session_start();
if (!isset($_SESSION["user_id"])) {
    header("Location: ../auth/login.php");
    exit;
}

require "../config/db.php";
require "../includes/header.php";
require "../includes/navbar.php";

$user_id = (int)$_SESSION["user_id"];

$q = mysqli_query($conn, "SELECT user_id, name, email, role FROM users WHERE user_id = $user_id LIMIT 1");
$user = mysqli_fetch_assoc($q);

if (!$user) {
    die("User not found.");
}
?>

<div class="container mt-4" style="max-width: 700px;">
    <div class="card shadow">
        <div class="card-body">
            <h3 class="mb-3">👤 My Profile</h3>

            <?php if (isset($_GET["success"])): ?>
                <div class="alert alert-success">Profile updated successfully.</div>
            <?php endif; ?>

            <?php if (isset($_GET["error"])): ?>
                <div class="alert alert-danger">
                    <?php
                        if ($_GET["error"] === "email_exists") echo "That email is already used by another account.";
                        elseif ($_GET["error"] === "password_mismatch") echo "New password and confirm password do not match.";
                        elseif ($_GET["error"] === "wrong_current") echo "Current password is incorrect.";
                        else echo "Something went wrong.";
                    ?>
                </div>
            <?php endif; ?>

            <!-- VIEW INFO -->
            <div class="mb-3">
                <p class="mb-1"><b>User ID:</b> <?= htmlspecialchars($user["user_id"]) ?></p>
                <p class="mb-1"><b>Role:</b> <?= htmlspecialchars($user["role"]) ?></p>
            </div>

            <hr>

            <!-- UPDATE NAME + EMAIL -->
            <h5 class="mb-2">Update Details</h5>

            <form action="update_profile.php" method="POST">
                <div class="mb-3">
                    <label class="form-label">Full Name</label>
                    <input type="text" name="name" class="form-control" required
                           value="<?= htmlspecialchars($user["name"]) ?>">
                </div>

                <div class="mb-3">
                    <label class="form-label">Email</label>
                    <input type="email" name="email" class="form-control" required
                           value="<?= htmlspecialchars($user["email"]) ?>">
                </div>

                <button class="btn btn-primary">Save Changes</button>
            </form>

            <hr>

            <!-- CHANGE PASSWORD -->
            <h5 class="mb-2">Change Password</h5>

            <form action="change_password.php" method="POST">
                <div class="mb-3">
                    <label class="form-label">Current Password</label>
                    <input type="password" name="current_password" class="form-control" required>
                </div>

                <div class="mb-3">
                    <label class="form-label">New Password</label>
                    <input type="password" name="new_password" class="form-control" required minlength="6">
                </div>

                <div class="mb-3">
                    <label class="form-label">Confirm New Password</label>
                    <input type="password" name="confirm_password" class="form-control" required minlength="6">
                </div>

                <button class="btn btn-dark">Update Password</button>
            </form>

        </div>
    </div>
</div>

<?php require "../includes/footer.php"; ?>
