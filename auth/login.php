<?php
session_start();
require "../config/db.php";

$message = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $email = trim($_POST["email"] ?? "");
    $password = $_POST["password"] ?? "";

    // Prepared statement prevents SQL injection
    $stmt = mysqli_prepare($conn, "SELECT * FROM users WHERE email = ? LIMIT 1");
    mysqli_stmt_bind_param($stmt, "s", $email);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);

    if (mysqli_num_rows($result) == 1) {
        $user = mysqli_fetch_assoc($result);

        if (password_verify($password, $user["password"])) {
            session_regenerate_id(true); // prevent session fixation
            $_SESSION["user_id"] = $user["user_id"];
            $_SESSION["name"] = $user["name"];
            $_SESSION["role"] = $user["role"];

            if ($user["role"] == "admin") {
                header("Location: ../admin/dashboard.php");
            } else {
                header("Location: ../user/dashboard.php");
            }
            exit;
        } else {
            $message = "Invalid email or password.";
        }
    } else {
        $message = "Invalid email or password.";
    }
}
?>

<?php require "../includes/header.php"; ?>
<?php require "../includes/navbar.php"; ?>

<div class="container">
    <div class="card shadow">
        <div class="card-body">

            <h3>User Login</h3>

            <?php if ($message != "") { ?>
                <div class="alert alert-danger mt-2"><?php echo $message; ?></div>
            <?php } ?>

            <form method="POST">

                <label>Email</label>
                <input type="email" name="email" class="form-control" required>

                <label class="mt-3">Password</label>
                <input type="password" name="password" class="form-control" required>

                <button type="submit" class="btn btn-primary mt-4">Login</button>
            </form>

        </div>
    </div>
</div>

<?php require "../includes/footer.php"; ?>
