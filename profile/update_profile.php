<?php
session_start();
if (!isset($_SESSION["user_id"])) {
    header("Location: ../auth/login.php");
    exit;
}

require "../config/db.php";

$user_id = (int)$_SESSION["user_id"];
$name = trim($_POST["name"] ?? "");
$email = trim($_POST["email"] ?? "");

if ($name === "" || $email === "") {
    header("Location: profile.php?error=1");
    exit;
}

// check email already used by other user
$stmt = mysqli_prepare($conn, "SELECT user_id FROM users WHERE email = ? AND user_id <> ? LIMIT 1");
mysqli_stmt_bind_param($stmt, "si", $email, $user_id);
mysqli_stmt_execute($stmt);
$res = mysqli_stmt_get_result($stmt);

if ($res && mysqli_fetch_assoc($res)) {
    header("Location: profile.php?error=email_exists");
    exit;
}

// update
$stmt2 = mysqli_prepare($conn, "UPDATE users SET name = ?, email = ? WHERE user_id = ? LIMIT 1");
mysqli_stmt_bind_param($stmt2, "ssi", $name, $email, $user_id);

if (mysqli_stmt_execute($stmt2)) {
    // update session name if you use it anywhere
    $_SESSION["name"] = $name;
    header("Location: profile.php?success=1");
    exit;
}

header("Location: profile.php?error=1");
exit;
