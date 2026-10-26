<?php
if (session_status() === PHP_SESSION_NONE) { session_start(); }
$pageTitle = $pageTitle ?? "Book Discovery";
$current = basename($_SERVER["SCRIPT_NAME"]);
$loggedIn = isset($_SESSION["reader_id"]);
$flash = function_exists("flash_get") ? flash_get() : "";
$links = $loggedIn
    ? [["index.php", "Home", ""], ["preferences.php", "Preferences", ""], ["recommendations.php", "Recommendations", ""], ["logout.php", "Logout", "logout-btn"]]
    : [["index.php", "Home", ""], ["register.php", "Register", "register-btn"], ["login.php", "Login", "login-btn"]];
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?php echo htmlspecialchars($pageTitle); ?> | Book Discovery</title>
    <link rel="icon" href="data:image/svg+xml,<svg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 32 32'><rect width='32' height='32' rx='6' fill='%231f5a43'/><text x='16' y='23' font-size='20' text-anchor='middle' fill='white' font-family='Georgia'>B</text></svg>">
    <link rel="stylesheet" href="style.css">
</head>
<body>
<a class="skip" href="#main">Skip to content</a>
<nav class="navbar" aria-label="Main">
    <a href="index.php" class="logo">Book Discovery</a>
    <div class="nav-links">
        <?php foreach ($links as $l) { ?>
            <a href="<?php echo $l[0]; ?>"<?php if ($l[2] !== "") { echo ' class="' . $l[2] . '"'; } ?><?php if ($current === $l[0]) { echo ' aria-current="page"'; } ?>><?php echo $l[1]; ?></a>
        <?php } ?>
    </div>
</nav>
<main id="main">
<?php if ($flash !== "") { ?>
    <div class="flash" role="status"><?php echo htmlspecialchars($flash); ?></div>
<?php } ?>