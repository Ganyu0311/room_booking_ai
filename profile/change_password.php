<?php
session_start();
if (!isset($_SESSION["user_id"])) {
    header("Location: ../auth/login.php");
    exit;
}

require "../config/db.php";

$user_id = (int)$_SESSION["user_id"];
$current = $_POST["current_password"] ?? "";
$new = $_POST["new_password"] ?? "";
$confirm = $_POST["confirm_password"] ?? "";

if ($new !== $confirm) {
    header("Location: profile.php?error=password_mismatch");
    exit;
}

// get current hash
$stmt = mysqli_prepare($conn, "SELECT password FROM users WHERE user_id = ? LIMIT 1");
mysqli_stmt_bind_param($stmt, "i", $user_id);
mysqli_stmt_execute($stmt);
$res = mysqli_stmt_get_result($stmt);
$row = mysqli_fetch_assoc($res);

if (!$row) {
    header("Location: profile.php?error=1");
    exit;
}

$hash = $row["password"];

// verify current password
if (!password_verify($current, $hash)) {
    header("Location: profile.php?error=wrong_current");
    exit;
}

// update password
$newHash = password_hash($new, PASSWORD_DEFAULT);

$stmt2 = mysqli_prepare($conn, "UPDATE users SET password = ? WHERE user_id = ? LIMIT 1");
mysqli_stmt_bind_param($stmt2, "si", $newHash, $user_id);

if (mysqli_stmt_execute($stmt2)) {
    header("Location: profile.php?success=1");
    exit;
}

header("Location: profile.php?error=1");
exit;
