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


/* Get book details */

$sql = "SELECT
            b.book_id,
            b.title,
            a.author_name,
            a.country,
            b.publication_year,
            b.language,
            b.pages,
            b.rating,
            b.description
        FROM books b
        JOIN authors a
            ON b.author_id = a.author_id
        WHERE b.book_id = ?";

$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $book_id);
$stmt->execute();

$result = $stmt->get_result();

if ($result->num_rows == 0) {
    header("Location: recommendations.php");
    exit();
}

$book = $result->fetch_assoc();


/* Get genres */

$genreSql = "SELECT g.genre_name
             FROM genres g
             JOIN book_genres bg
                 ON g.genre_id = bg.genre_id
             WHERE bg.book_id = ?
             ORDER BY g.genre_name";

$genreStmt = $conn->prepare($genreSql);
$genreStmt->bind_param("i", $book_id);
$genreStmt->execute();

$genreResult = $genreStmt->get_result();


/* Get reviews */

$reviewSql = "SELECT
                  r.name,
                  rv.rating,
                  rv.comment,
                  rv.review_date
              FROM reviews rv
              JOIN readers r
                  ON rv.reader_id = r.reader_id
              WHERE rv.book_id = ?
              ORDER BY rv.review_date DESC, rv.review_id DESC";

$reviewStmt = $conn->prepare($reviewSql);
$reviewStmt->bind_param("i", $book_id);
$reviewStmt->execute();

$reviewResult = $reviewStmt->get_result();

?>

<!DOCTYPE html>
<html>

<head>

    <title>
        <?php echo htmlspecialchars($book["title"]); ?>
    </title>

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
            max-width: 900px;
            margin: 30px auto;
        }

        .card {
            background: white;
            padding: 30px;
            border-radius: 10px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }

        .title {
            color: #2c3e50;
            margin-top: 0;
        }

        .details p {
            margin: 12px 0;
        }

        .rating {
            color: #f39c12;
            font-weight: bold;
        }

        .genres {
            margin: 10px 0 20px;
        }

        .genre {
            display: inline-block;
            background-color: #3498db;
            color: white;
            padding: 6px 12px;
            border-radius: 15px;
            margin: 4px;
        }

        .description {
            line-height: 1.6;
            margin-bottom: 25px;
        }

        .review-button {
            display: inline-block;
            padding: 11px 18px;
            background-color: #3498db;
            color: white;
            text-decoration: none;
            border-radius: 6px;
            margin-bottom: 20px;
        }

        .review-button:hover {
            background-color: #2980b9;
        }

        .reviews {
            margin-top: 20px;
        }

        .review {
            border-top: 1px solid #ddd;
            padding: 18px 0;
        }

        .review strong {
            color: #2c3e50;
            font-size: 16px;
        }

        .review-rating {
            color: #f39c12;
            font-weight: bold;
        }

        .date {
            color: #777;
            font-size: 13px;
        }

        .back {
            display: inline-block;
            margin-top: 25px;
            padding: 11px 18px;
            background-color: #34495e;
            color: white;
            text-decoration: none;
            border-radius: 6px;
        }

        .back:hover {
            background-color: #2c3e50;
        }

        .no-reviews {
            color: #777;
            padding: 15px 0;
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

        <h2 class="title">
            <?php echo htmlspecialchars($book["title"]); ?>
        </h2>


        <div class="details">

            <p>
                <strong>Author:</strong>
                <?php echo htmlspecialchars($book["author_name"]); ?>
            </p>

            <p>
                <strong>Author Country:</strong>
                <?php echo htmlspecialchars($book["country"]); ?>
            </p>

            <p>
                <strong>Publication Year:</strong>
                <?php echo htmlspecialchars($book["publication_year"]); ?>
            </p>

            <p>
                <strong>Language:</strong>
                <?php echo htmlspecialchars($book["language"]); ?>
            </p>

            <p>
                <strong>Pages:</strong>
                <?php echo htmlspecialchars($book["pages"]); ?>
            </p>

            <p>
                <strong>Rating:</strong>

                <span class="rating">
                    ⭐ <?php echo htmlspecialchars($book["rating"]); ?>
                </span>

            </p>

        </div>


        <h3>Genres</h3>

        <div class="genres">

            <?php if ($genreResult->num_rows > 0) { ?>

                <?php while ($genre = $genreResult->fetch_assoc()) { ?>

                    <span class="genre">
                        <?php echo htmlspecialchars($genre["genre_name"]); ?>
                    </span>

                <?php } ?>

            <?php } else { ?>

                <p>No genre available.</p>

            <?php } ?>

        </div>


        <h3>Book Description</h3>

        <div class="description">

            <?php

            if (!empty($book["description"])) {

                echo nl2br(
                    htmlspecialchars($book["description"])
                );

            } else {

                echo "No description available.";

            }

            ?>

        </div>


        <!-- Write Review Button -->

        <a
            href="review.php?id=<?php echo $book_id; ?>"
            class="review-button"
        >
            ✍️ Write a Review
        </a>


        <!-- Reviews -->

        <div class="reviews">

            <h3>Reader Reviews</h3>


            <?php if ($reviewResult->num_rows > 0) { ?>

                <?php while ($review = $reviewResult->fetch_assoc()) { ?>

                    <div class="review">

                        <strong>
                            <?php echo htmlspecialchars($review["name"]); ?>
                        </strong>

                        <p class="review-rating">

                            ⭐
                            <?php echo htmlspecialchars($review["rating"]); ?>
                            / 5

                        </p>

                        <p>

                            <?php
                            echo nl2br(
                                htmlspecialchars($review["comment"])
                            );
                            ?>

                        </p>

                        <div class="date">

                            <?php
                            echo htmlspecialchars(
                                $review["review_date"]
                            );
                            ?>

                        </div>

                    </div>

                <?php } ?>

            <?php } else { ?>

                <p class="no-reviews">
                    No reviews available for this book yet.
                </p>

            <?php } ?>

        </div>


        <a href="recommendations.php" class="back">
            ← Back to Recommendations
        </a>

    </div>

</div>

</body>

</html>