<?php
session_start();
include "db.php";

$message = "";

if ($_SERVER["REQUEST_METHOD"] == "POST" && !csrf_valid()) {
    $message = "Your session expired. Please try again.";
} elseif ($_SERVER["REQUEST_METHOD"] == "POST") {

    $email = trim($_POST["email"]);
    $password = $_POST["password"] ?? "";

    if ($email == "" || $password == "") {

        $message = "Please enter your email and password.";

    } else {

        $sql = "SELECT reader_id, name, email, language, password_hash
                FROM readers
                WHERE email = ?";

        $stmt = $conn->prepare($sql);
        $stmt->bind_param("s", $email);
        $stmt->execute();

        $result = $stmt->get_result();

        $reader = null;
        if ($result->num_rows == 1) {
            $row = $result->fetch_assoc();
            if (!empty($row["password_hash"]) && password_verify($password, $row["password_hash"])) { $reader = $row; }
        }

        if ($reader !== null) {

            session_regenerate_id(true);

            $_SESSION["reader_id"] = $reader["reader_id"];
            $_SESSION["reader_name"] = $reader["name"];
            $_SESSION["reader_email"] = $reader["email"];
            $_SESSION["language"] = $reader["language"];

            header("Location: preferences.php");
            exit();

        } else {

            $message = "Invalid email or password.";

        }
    }
}
?>

<?php $pageTitle = 'Reader Login'; include "header.php"; ?>

<div class="container">

    <h2>Reader Login</h2>

    <?php if ($message != "") { ?>

        <div class="message">
            <?php echo htmlspecialchars($message); ?>
        </div>

    <?php } ?>

    <form method="POST">
        <?php echo csrf_field(); ?>

        <input
            type="email"
            name="email"
            placeholder="Enter your registered email"
            required
        >

        <input type="password" name="password" placeholder="Password" autocomplete="current-password" required>

        <button type="submit">
            Login
        </button>

    </form>

</div>

<?php include "footer.php"; ?>
