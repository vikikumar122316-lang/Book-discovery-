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

if ($_SERVER["REQUEST_METHOD"] == "POST" && !csrf_valid()) {
    $message = "Your session expired. Please try again.";
} elseif ($_SERVER["REQUEST_METHOD"] == "POST") {

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

<?php $pageTitle = 'Write a Review'; include "header.php"; ?>

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
        <?php echo csrf_field(); ?>

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

<?php include "footer.php"; ?>
