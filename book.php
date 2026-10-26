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

<?php $pageTitle = $book["title"]; include "header.php"; ?>

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
                     <?php echo htmlspecialchars($book["rating"]); ?>
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
            Write a Review
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

<?php include "footer.php"; ?>
