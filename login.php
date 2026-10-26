<?php
session_start();
include "db.php";

$message = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {

    $email = trim($_POST["email"]);

    if ($email == "") {

        $message = "Please enter your email.";

    } else {

        $sql = "SELECT reader_id, name, email, language
                FROM readers
                WHERE email = ?";

        $stmt = $conn->prepare($sql);
        $stmt->bind_param("s", $email);
        $stmt->execute();

        $result = $stmt->get_result();

        if ($result->num_rows == 1) {

            $reader = $result->fetch_assoc();

            $_SESSION["reader_id"] = $reader["reader_id"];
            $_SESSION["reader_name"] = $reader["name"];
            $_SESSION["reader_email"] = $reader["email"];
            $_SESSION["language"] = $reader["language"];

            header("Location: preferences.php");
            exit();

        } else {

            $message = "Reader not found. Please register first.";

        }
    }
}
?>

<!DOCTYPE html>
<html>

<head>

    <title>Reader Login</title>

    <style>

        body {
            font-family: Arial, sans-serif;
            background-color: #f4f6f8;
        }

        .container {
            width: 400px;
            margin: 100px auto;
            background: white;
            padding: 30px;
            border-radius: 10px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.15);
        }

        h2 {
            text-align: center;
            color: #2c3e50;
        }

        input {
            width: 100%;
            padding: 12px;
            margin: 15px 0;
            box-sizing: border-box;
            border: 1px solid #ccc;
            border-radius: 5px;
        }

        button {
            width: 100%;
            padding: 12px;
            background-color: #3498db;
            color: white;
            border: none;
            border-radius: 5px;
            cursor: pointer;
        }

        button:hover {
            background-color: #2980b9;
        }

        .message {
            text-align: center;
            color: #e74c3c;
            margin-bottom: 15px;
        }

    </style>

<link rel="stylesheet" href="style.css">
</head>

<body>
<div class="site-nav"><a class="brand" href="index.php">📚 Book Discovery</a><div class="site-links"><a href="index.php">Home</a><a href="recommendations.php">Recommendations</a><a href="preferences.php">Preferences</a><a href="logout.php">Logout</a></div></div>

<div class="container">

    <h2>📚 Reader Login</h2>

    <?php if ($message != "") { ?>

        <div class="message">
            <?php echo htmlspecialchars($message); ?>
        </div>

    <?php } ?>

    <form method="POST">

        <input
            type="email"
            name="email"
            placeholder="Enter your registered email"
            required
        >

        <button type="submit">
            Login
        </button>

    </form>

</div>

</body>

</html>