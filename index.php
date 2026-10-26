<?php
session_start();
include "db.php";

$search = isset($_GET["search"]) ? trim($_GET["search"]) : "";
$genre = isset($_GET["genre"]) ? trim($_GET["genre"]) : "";


/* Get all genres */

$genreQuery = "SELECT genre_id, genre_name
               FROM genres
               ORDER BY genre_name";

$genreResult = $conn->query($genreQuery);


/* Get books */

$searchTerm = "%" . $search . "%";

if ($genre == "") {

    $sql = "SELECT DISTINCT
                b.book_id,
                b.title,
                a.author_name,
                b.publication_year,
                b.language,
                b.pages,
                b.rating
            FROM books b
            JOIN authors a
                ON b.author_id = a.author_id
            WHERE b.title LIKE ?
               OR a.author_name LIKE ?
            ORDER BY b.rating DESC";

    $stmt = $conn->prepare($sql);

    $stmt->bind_param(
        "ss",
        $searchTerm,
        $searchTerm
    );

} else {

    $genreId = intval($genre);

    $sql = "SELECT DISTINCT
                b.book_id,
                b.title,
                a.author_name,
                b.publication_year,
                b.language,
                b.pages,
                b.rating
            FROM books b
            JOIN authors a
                ON b.author_id = a.author_id
            JOIN book_genres bg
                ON b.book_id = bg.book_id
            JOIN genres g
                ON bg.genre_id = g.genre_id
            WHERE (b.title LIKE ? OR a.author_name LIKE ?)
              AND g.genre_id = ?
            ORDER BY b.rating DESC";

    $stmt = $conn->prepare($sql);

    $stmt->bind_param(
        "ssi",
        $searchTerm,
        $searchTerm,
        $genreId
    );
}

$stmt->execute();

$result = $stmt->get_result();

?>

<?php $pageTitle = 'Book Discovery System'; include "header.php"; ?>

<!-- Hero -->

<div class="hero">

    <h1>Discover Your Next Great Book</h1>

    <p>
        Search books, explore genres and get personalized recommendations.
    </p>

</div>


<!-- Search -->

<div class="search-section">

    <form method="GET" class="search-form">

        <input
            type="text"
            name="search"
            placeholder="Search by book title or author..."
            value="<?php echo htmlspecialchars($search); ?>"
        >


        <select name="genre">

            <option value="">
                All Genres
            </option>

            <?php

            if ($genreResult && $genreResult->num_rows > 0) {

                while ($g = $genreResult->fetch_assoc()) {

            ?>

                <option
                    value="<?php echo $g["genre_id"]; ?>"
                    <?php
                    if ($genre == $g["genre_id"]) {
                        echo "selected";
                    }
                    ?>
                >

                    <?php
                    echo htmlspecialchars(
                        $g["genre_name"]
                    );
                    ?>

                </option>

            <?php

                }

            }

            ?>

        </select>


        <button type="submit">
            Search
        </button>

    </form>

</div>


<!-- Books -->

<div class="container">

    <h2 class="section-title">
        Available Books
    </h2>


    <?php if ($result && $result->num_rows > 0) { ?>

        <div class="books">

            <?php while ($book = $result->fetch_assoc()) { ?>

                <div class="book">

                    <h3>
                        <?php
                        echo htmlspecialchars(
                            $book["title"]
                        );
                        ?>
                    </h3>


                    <p>

                        <strong>Author:</strong>

                        <?php
                        echo htmlspecialchars(
                            $book["author_name"]
                        );
                        ?>

                    </p>


                    <p>

                        <strong>Year:</strong>

                        <?php
                        echo htmlspecialchars(
                            $book["publication_year"]
                        );
                        ?>

                    </p>


                    <p>

                        <strong>Language:</strong>

                        <?php
                        echo htmlspecialchars(
                            $book["language"]
                        );
                        ?>

                    </p>


                    <p>

                        <strong>Pages:</strong>

                        <?php
                        echo htmlspecialchars(
                            $book["pages"]
                        );
                        ?>

                    </p>


                    <p>

                        <strong>Rating:</strong>

                        <span class="rating">

                            
                            <?php
                            echo htmlspecialchars(
                                $book["rating"]
                            );
                            ?>

                        </span>

                    </p>


                    <a
                        href="book.php?id=<?php echo intval($book["book_id"]); ?>"
                        class="details-btn"
                    >
                        View Details
                    </a>

                </div>

            <?php } ?>

        </div>


    <?php } else { ?>

        <div class="no-books">

            <h2>No books found</h2>

            <p>
                Try another book title, author or genre.
            </p>

        </div>

    <?php } ?>

</div>


<?php include "footer.php"; ?>
