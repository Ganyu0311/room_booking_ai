<?php
session_start();
if (isset($_SESSION["user_id"])) {
    $target = ($_SESSION["role"] ?? "") === "admin" ? "admin/dashboard.php" : "user/dashboard.php";
} else {
    $target = "auth/login.php";
}
header("Location: " . $target);
exit;
