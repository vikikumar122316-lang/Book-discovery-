<?php
if (session_status() === PHP_SESSION_NONE) { session_start(); }

function app_error($detail = "") {
    if ($detail !== "") { error_log("[book_discovery] " . $detail); }
    http_response_code(500);
    $pageTitle = "Something went wrong";
    include __DIR__ . "/header.php";
    echo '<div class="container"><h2>Something went wrong</h2><p>We could not complete your request. Please try again in a moment.</p><a class="back" href="index.php">Back to home</a></div>';
    include __DIR__ . "/footer.php";
    exit();
}

function csrf_token() {
    if (empty($_SESSION["csrf"])) { $_SESSION["csrf"] = bin2hex(random_bytes(32)); }
    return $_SESSION["csrf"];
}
function csrf_field() { return '<input type="hidden" name="csrf" value="' . csrf_token() . '">'; }
function csrf_valid() {
    return isset($_POST["csrf"], $_SESSION["csrf"]) && hash_equals($_SESSION["csrf"], (string) $_POST["csrf"]);
}
function flash_set($message) { $_SESSION["flash"] = $message; }
function flash_get() { $m = $_SESSION["flash"] ?? ""; unset($_SESSION["flash"]); return $m; }

if (!is_file(__DIR__ . "/db_config.php")) {
    app_error("db_config.php is missing. Copy db_config.example.php to db_config.php.");
}
$config = require __DIR__ . "/db_config.php";

mysqli_report(MYSQLI_REPORT_OFF);
$conn = @new mysqli($config["host"], $config["user"], $config["pass"], $config["name"]);
if ($conn->connect_error) {
    app_error("Database connection failed: " . $conn->connect_error);
}
$conn->set_charset("utf8mb4");