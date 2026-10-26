<?php
session_start();
include "db.php";

if (!isset($_SESSION["reader_id"])) {
    header("Location: login.php");
    exit();
}

if (!isset($_GET["id"]) || !is_numeric($_GET["id"])) {
    header("Location: recommendations.php");
    exit();
}

$book_id = intval($_GET["id"]);
$reader_id = intval($_SESSION["reader_id"]);

$message = "";


/* Get book */

$sql = "SELECT title
        FROM books
        WHERE book_id = ?";

$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $book_id);
$stmt->execute();

$result = $stmt->get_result();

if ($result->num_rows == 0) {
    header("Location: recommendations.php");
    exit();
}

$book = $result->fetch_assoc();


/* Submit review */

if ($_SERVER["REQUEST_METHOD"] == "POST") {

    $rating = floatval($_POST["rating"]);
    $comment = trim($_POST["comment"]);
    $review_date = date("Y-m-d");

    if ($rating < 1 || $rating > 5) {

        $message = "Please select a valid rating.";

    } elseif ($comment == "") {

        $message = "Please enter a comment.";

    } else {

        /* Check whether reader already reviewed this book */

        $checkSql = "SELECT review_id
                     FROM reviews
                     WHERE reader_id = ?
                     AND book_id = ?";

        $checkStmt = $conn->prepare($checkSql);
        $checkStmt->bind_param(
            "ii",
            $reader_id,
            $book_id
        );

        $checkStmt->execute();

        $checkResult = $checkStmt->get_result();

        if ($checkResult->num_rows > 0) {

            /* Update existing review */

            $review = $checkResult->fetch_assoc();
            $review_id = $review["review_id"];

            $updateSql = "UPDATE reviews
                          SET rating = ?,
                              comment = ?,
                              review_date = ?
                          WHERE review_id = ?";

            $updateStmt = $conn->prepare($updateSql);

            $updateStmt->bind_param(
                "dssi",
                $rating,
                $comment,
                $review_date,
                $review_id
            );

            if ($updateStmt->execute()) {

                header(
                    "Location: book.php?id=" . $book_id
                );

                exit();

            } else {

                $message = "Failed to update review.";
            }

        } else {

            /* Insert new review */

            $insertSql = "INSERT INTO reviews
                          (reader_id, book_id, rating,
                           comment, review_date)
                          VALUES (?, ?, ?, ?, ?)";

            $insertStmt = $conn->prepare($insertSql);

            $insertStmt->bind_param(
                "iidss",
                $reader_id,
                $book_id,
                $rating,
                $comment,
                $review_date
            );

            if ($insertStmt->execute()) {

                header(
                    "Location: book.php?id=" . $book_id
                );

                exit();

            } else {

                $message = "Failed to submit review.";
            }
        }
    }
}

?>

<!DOCTYPE html>
<html>

<head>

    <title>Write a Review</title>

    <style>

        body {
            font-family: Arial, sans-serif;
            background-color: #f4f6f8;
            margin: 0;
        }

        .header {
            background-color: #2c3e50;
            color: white;
            padding: 20px;
            text-align: center;
        }

        .container {
            width: 90%;
            max-width: 550px;
            margin: 40px auto;
        }

        .card {
            background: white;
            padding: 30px;
            border-radius: 10px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }

        h2 {
            color: #2c3e50;
        }

        label {
            display: block;
            margin-top: 18px;
            font-weight: bold;
        }

        select,
        textarea {
            width: 100%;
            padding: 12px;
            margin-top: 8px;
            box-sizing: border-box;
            border: 1px solid #ccc;
            border-radius: 6px;
            font-size: 15px;
        }

        textarea {
            height: 130px;
            resize: vertical;
        }

        button {
            width: 100%;
            padding: 12px;
            margin-top: 25px;
            background-color: #3498db;
            color: white;
            border: none;
            border-radius: 6px;
            font-size: 16px;
            cursor: pointer;
        }

        button:hover {
            background-color: #2980b9;
        }

        .message {
            margin-top: 15px;
            padding: 10px;
            background-color: #fdecea;
            color: #c0392b;
            border-radius: 5px;
            text-align: center;
        }

        .back {
            display: inline-block;
            margin-top: 20px;
            color: #2c3e50;
            text-decoration: none;
        }

    </style>

<link rel="stylesheet" href="style.css">
</head>

<body>

<div class="header">
<div class="site-nav"><a class="brand" href="index.php">📚 Book Discovery</a><div class="site-links"><a href="index.php">Home</a><a href="recommendations.php">Recommendations</a><a href="preferences.php">Preferences</a><a href="logout.php">Logout</a></div></div>

    <h1>📚 Book Discovery System</h1>

</div>


<div class="container">

    <div class="card">

        <h2>Write a Review</h2>

        <p>
            Book:
            <strong>
                <?php echo htmlspecialchars($book["title"]); ?>
            </strong>
        </p>


        <?php if ($message != "") { ?>

            <div class="message">

                <?php echo htmlspecialchars($message); ?>

            </div>

        <?php } ?>


        <form method="POST">

            <label>Rating</label>

            <select name="rating" required>

                <option value="">Select Rating</option>

                <option value="5">5 - Excellent</option>

                <option value="4">4 - Very Good</option>

                <option value="3">3 - Good</option>

                <option value="2">2 - Average</option>

                <option value="1">1 - Poor</option>

            </select>


            <label>Comment</label>

            <textarea
                name="comment"
                placeholder="Write your review..."
                required
            ></textarea>


            <button type="submit">
                Submit Review
            </button>

        </form>


        <a
            href="book.php?id=<?php echo $book_id; ?>"
            class="back"
        >
            ← Back to Book
        </a>

    </div>

</div>

</body>

</html>