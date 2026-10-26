<?php
include "db.php";

$message = "";

if ($_SERVER["REQUEST_METHOD"] == "POST" && !csrf_valid()) {
    $message = "Your session expired. Please try again.";
} elseif ($_SERVER["REQUEST_METHOD"] == "POST") {

    $name = trim($_POST["name"]);
    $email = trim($_POST["email"]);
    $language = trim($_POST["language"]);
    $password = $_POST["password"] ?? "";
    $confirm = $_POST["confirm_password"] ?? "";

    if ($name == "" || $email == "" || $language == "") {

        $message = "Please fill all fields.";

    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $message = "Please enter a valid email address.";
    } elseif (strlen($password) < 8) {
        $message = "Password must be at least 8 characters.";
    } elseif ($password !== $confirm) {
        $message = "Passwords do not match.";
    } else {

        $sql = "INSERT INTO readers (name, email, language, password_hash)
                VALUES (?, ?, ?, ?)";

        $stmt = $conn->prepare($sql);

        $hash = password_hash($password, PASSWORD_DEFAULT);
        $stmt->bind_param("ssss", $name, $email, $language, $hash);

        if ($stmt->execute()) {
            flash_set("Registration successful. Please log in.");
            header("Location: login.php");
            exit();
        } else {
            $message = ($stmt->errno == 1062) ? "This email is already registered. Please log in instead." : "Registration failed. Please try again.";
        }
    }
}
?>

<?php $pageTitle = 'Reader Registration'; include "header.php"; ?>

<div class="container">

    <h2>Reader Registration</h2>

    <?php if ($message != "") { ?>

        <div class="message">
            <?php echo htmlspecialchars($message); ?>
        </div>

    <?php } ?>

    <form method="POST">
        <?php echo csrf_field(); ?>

        <input
            type="text"
            name="name"
            placeholder="Enter your name"
            required
        >

        <input
            type="email"
            name="email"
            placeholder="Enter your email"
            required
        >

        <input
            type="text"
            name="language"
            placeholder="Preferred language"
            value="English"
            required
        >

        <input type="password" name="password" placeholder="Password (at least 8 characters)" minlength="8" autocomplete="new-password" required>

        <input type="password" name="confirm_password" placeholder="Confirm password" minlength="8" autocomplete="new-password" required>

        <button type="submit">
            Register
        </button>

    </form>

</div>

<?php include "footer.php"; ?>
