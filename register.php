<?php
include "db.php";

$message = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {

    $name = trim($_POST["name"]);
    $email = trim($_POST["email"]);
    $language = trim($_POST["language"]);

    if ($name == "" || $email == "" || $language == "") {

        $message = "Please fill all fields.";

    } else {

        $sql = "INSERT INTO readers (name, email, language)
                VALUES (?, ?, ?)";

        $stmt = $conn->prepare($sql);

        $stmt->bind_param("sss", $name, $email, $language);

        if ($stmt->execute()) {
            $message = "Registration successful!";
        } else {
            $message = "Registration failed. Email may already exist.";
        }
    }
}
?>

<!DOCTYPE html>
<html>

<head>

    <title>Reader Registration</title>

    <style>

        body {
            font-family: Arial, sans-serif;
            background-color: #f4f6f8;
        }

        .container {
            width: 400px;
            margin: 80px auto;
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
            margin: 10px 0;
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
            margin-bottom: 15px;
            color: #27ae60;
        }

    </style>

<link rel="stylesheet" href="style.css">
</head>

<body>
<div class="site-nav"><a class="brand" href="index.php">📚 Book Discovery</a><div class="site-links"><a href="index.php">Home</a><a href="recommendations.php">Recommendations</a><a href="preferences.php">Preferences</a><a href="logout.php">Logout</a></div></div>

<div class="container">

    <h2>📚 Reader Registration</h2>

    <?php if ($message != "") { ?>

        <div class="message">
            <?php echo htmlspecialchars($message); ?>
        </div>

    <?php } ?>

    <form method="POST">

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

        <button type="submit">
            Register
        </button>

    </form>

</div>

</body>

</html>