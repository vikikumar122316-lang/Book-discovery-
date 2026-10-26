<?php
session_start();
include "db.php";

if (!isset($_SESSION["reader_id"])) {
    header("Location: login.php");
    exit();
}

$message = "";
$reader_id = $_SESSION["reader_id"];

/* Get genres */

$genreQuery = "SELECT genre_id, genre_name
               FROM genres
               ORDER BY genre_name";

$genreResult = $conn->query($genreQuery);


/* Save / Update preferences */

if ($_SERVER["REQUEST_METHOD"] == "POST") {

    $genre_id = intval($_POST["genre_id"]);
    $preferred_language = trim($_POST["preferred_language"]);
    $minimum_rating = floatval($_POST["minimum_rating"]);
    $preferred_length = $_POST["preferred_length"];

    /* Check whether preference already exists */

    $checkSql = "SELECT preference_id
                 FROM preferences
                 WHERE reader_id = ?
                 ORDER BY preference_id DESC
                 LIMIT 1";

    $checkStmt = $conn->prepare($checkSql);
    $checkStmt->bind_param("i", $reader_id);
    $checkStmt->execute();

    $checkResult = $checkStmt->get_result();

    if ($checkResult->num_rows > 0) {

        /* Update existing preference */

        $row = $checkResult->fetch_assoc();
        $preference_id = $row["preference_id"];

        $sql = "UPDATE preferences
                SET genre_id = ?,
                    preferred_language = ?,
                    minimum_rating = ?,
                    preferred_length = ?
                WHERE preference_id = ?";

        $stmt = $conn->prepare($sql);

        $stmt->bind_param(
            "isdsi",
            $genre_id,
            $preferred_language,
            $minimum_rating,
            $preferred_length,
            $preference_id
        );

    } else {

        /* Insert first preference */

        $sql = "INSERT INTO preferences
                (reader_id, genre_id, preferred_language,
                 minimum_rating, preferred_length)
                VALUES (?, ?, ?, ?, ?)";

        $stmt = $conn->prepare($sql);

        $stmt->bind_param(
            "iisds",
            $reader_id,
            $genre_id,
            $preferred_language,
            $minimum_rating,
            $preferred_length
        );
    }

    if ($stmt->execute()) {

        header("Location: recommendations.php");
        exit();

    } else {

        $message = "Failed to save preferences.";
    }
}
?>

<!DOCTYPE html>
<html>
<head>

    <title>Book Preferences</title>

    <style>

        body {
            font-family: Arial, sans-serif;
            background-color: #f4f6f8;
            margin: 0;
        }

        .container {
            width: 450px;
            margin: 60px auto;
            background: white;
            padding: 30px;
            border-radius: 10px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.15);
        }

        h2 {
            text-align: center;
            color: #2c3e50;
        }

        label {
            display: block;
            margin-top: 15px;
            font-weight: bold;
        }

        select,
        input {
            width: 100%;
            padding: 12px;
            margin-top: 8px;
            box-sizing: border-box;
            border: 1px solid #ccc;
            border-radius: 5px;
        }

        button {
            width: 100%;
            padding: 12px;
            margin-top: 25px;
            background-color: #3498db;
            color: white;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            font-size: 16px;
        }

        button:hover {
            background-color: #2980b9;
        }

        .message {
            text-align: center;
            color: #e74c3c;
        }

    </style>

<link rel="stylesheet" href="style.css">
</head>

<body>
<div class="site-nav"><a class="brand" href="index.php">📚 Book Discovery</a><div class="site-links"><a href="index.php">Home</a><a href="recommendations.php">Recommendations</a><a href="preferences.php">Preferences</a><a href="logout.php">Logout</a></div></div>

<div class="container">

    <h2>📚 Your Book Preferences</h2>

    <p>
        Welcome,
        <strong>
            <?php echo htmlspecialchars($_SESSION["reader_name"]); ?>
        </strong>
    </p>

    <?php if ($message != "") { ?>

        <div class="message">
            <?php echo htmlspecialchars($message); ?>
        </div>

    <?php } ?>


    <form method="POST">

        <label>Preferred Genre</label>

        <select name="genre_id" required>

            <option value="">Select Genre</option>

            <?php

            if ($genreResult && $genreResult->num_rows > 0) {

                while ($genre = $genreResult->fetch_assoc()) {

            ?>

                <option value="<?php echo $genre["genre_id"]; ?>">

                    <?php echo htmlspecialchars($genre["genre_name"]); ?>

                </option>

            <?php

                }

            }

            ?>

        </select>


        <label>Preferred Language</label>

        <input
            type="text"
            name="preferred_language"
            value="English"
            required
        >


        <label>Minimum Rating</label>

        <select name="minimum_rating" required>

            <option value="3.0">3.0</option>

            <option value="3.5">3.5</option>

            <option value="4.0" selected>4.0</option>

            <option value="4.5">4.5</option>

        </select>


        <label>Preferred Book Length</label>

        <select name="preferred_length" required>

            <option value="Short">Short</option>

            <option value="Medium" selected>Medium</option>

            <option value="Long">Long</option>

        </select>


        <button type="submit">
            Get Book Recommendations
        </button>

    </form>

</div>

</body>
</html>