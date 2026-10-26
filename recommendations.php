<?php
session_start();
include "db.php";

if (!isset($_SESSION["reader_id"])) {
    header("Location: login.php");
    exit();
}

$reader_id = intval($_SESSION["reader_id"]);
$reader_name = $_SESSION["reader_name"];

/*
 * Build recommendations directly from the current database tables.
 * The old code called RecommendBooks(), but that procedure is not
 * present in this database.
 */
$sql = "
    SELECT
        b.book_id,
        b.title AS Book,
        a.author_name AS Author,
        COALESCE(
            GROUP_CONCAT(DISTINCT g.genre_name ORDER BY g.genre_name SEPARATOR ', '),
            'Not specified'
        ) AS Genre,
        b.rating AS Rating,
        b.pages AS Pages,
        LEAST(100,
            (CASE
                WHEN p.genre_id IS NULL THEN 20
                WHEN MAX(CASE WHEN bg.genre_id = p.genre_id THEN 1 ELSE 0 END) = 1 THEN 40
                ELSE 0
            END)
            +
            (CASE
                WHEN p.preferred_language IS NULL OR p.preferred_language = '' THEN 15
                WHEN LOWER(TRIM(b.language)) = LOWER(TRIM(p.preferred_language)) THEN 25
                ELSE 0
            END)
            +
            (CASE
                WHEN p.minimum_rating IS NULL THEN 20
                WHEN b.rating >= p.minimum_rating THEN 25
                WHEN b.rating >= p.minimum_rating - 0.5 THEN 15
                ELSE 5
            END)
            +
            (CASE
                WHEN p.preferred_length IS NULL THEN 10
                WHEN p.preferred_length = 'Short' AND b.pages <= 200 THEN 10
                WHEN p.preferred_length = 'Medium' AND b.pages BETWEEN 201 AND 400 THEN 10
                WHEN p.preferred_length = 'Long' AND b.pages > 400 THEN 10
                ELSE 3
            END)
        ) AS Match_Score
    FROM books b
    INNER JOIN authors a ON a.author_id = b.author_id
    LEFT JOIN book_genres bg ON bg.book_id = b.book_id
    LEFT JOIN genres g ON g.genre_id = bg.genre_id
    LEFT JOIN (
        SELECT preference_id, reader_id, genre_id, preferred_language,
               minimum_rating, preferred_length
        FROM preferences
        WHERE reader_id = ?
        ORDER BY preference_id DESC
        LIMIT 1
    ) p ON p.reader_id = ?
    GROUP BY b.book_id, b.title, a.author_name, b.rating, b.pages,
             p.genre_id, p.preferred_language, p.minimum_rating, p.preferred_length
    ORDER BY Match_Score DESC, b.rating DESC, b.title ASC
";

$stmt = $conn->prepare($sql);
if (!$stmt) {
    app_error("Recommendation query error: " . $conn->error);
}

$stmt->bind_param("ii", $reader_id, $reader_id);
if (!$stmt->execute()) {
    app_error("Failed to get recommendations: " . $stmt->error);
}

$result = $stmt->get_result();

?>

<?php $pageTitle = 'Book Recommendations'; include "header.php"; ?>

<div class="container">


    <!-- Welcome -->

    <div class="welcome">

        <h2>
            Welcome,
            <?php echo htmlspecialchars($reader_name); ?>!
        </h2>

        <p>
            These books are recommended based on your preferences.
        </p>

    </div>


    <!-- Recommendations -->

    <?php if ($result && $result->num_rows > 0) { ?>

        <div class="books">

            <?php while ($book = $result->fetch_assoc()) { ?>

                <div class="book">

                    <h3>
                        <?php
                        echo htmlspecialchars(
                            $book["Book"]
                        );
                        ?>
                    </h3>


                    <p>

                        <strong>Author:</strong>

                        <?php
                        echo htmlspecialchars(
                            $book["Author"]
                        );
                        ?>

                    </p>


                    <p>

                        <strong>Genre:</strong>

                        <?php
                        echo htmlspecialchars(
                            $book["Genre"]
                        );
                        ?>

                    </p>


                    <p>

                        <strong>Rating:</strong>

                        <span class="rating">

                            
                            <?php
                            echo htmlspecialchars(
                                $book["Rating"]
                            );
                            ?>

                        </span>

                    </p>


                    <p>

                        <strong>Pages:</strong>

                        <?php
                        echo htmlspecialchars(
                            $book["Pages"]
                        );
                        ?>

                    </p>


                    <div class="score">

                        Match Score:

                        <?php
                        echo htmlspecialchars(
                            $book["Match_Score"]
                        );
                        ?>

                        / 100

                    </div>


                    <a
                        href="book.php?id=<?php echo intval($book["book_id"]); ?>"
                        class="details-btn"
                    >
                        View Book Details
                    </a>

                </div>

            <?php } ?>

        </div>


    <?php } else { ?>


        <div class="no-books">

            <h2>No recommendations found</h2>

            <p>
                Please update your preferences and try again.
            </p>

        </div>


    <?php } ?>


    <!-- Bottom buttons -->

    <div class="buttons">

        <a
            href="preferences.php"
            class="change-btn"
        >
            ← Change Preferences
        </a>

    </div>


</div>

<?php include "footer.php"; ?>
