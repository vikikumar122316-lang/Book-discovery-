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

<!DOCTYPE html>
<html>

<head>

    <title>Book Discovery System</title>

    <style>

        * {
            box-sizing: border-box;
        }

        body {
            font-family: Arial, sans-serif;
            margin: 0;
            background-color: #f4f6f8;
        }

        /* Navigation */

        .navbar {
            background-color: #2c3e50;
            padding: 18px 5%;
            display: flex;
            justify-content: space-between;
            align-items: center;
            flex-wrap: wrap;
        }

        .logo {
            color: white;
            font-size: 24px;
            font-weight: bold;
            text-decoration: none;
        }

        .nav-links {
            display: flex;
            gap: 10px;
            flex-wrap: wrap;
        }

        .nav-links a {
            color: white;
            text-decoration: none;
            padding: 9px 14px;
            border-radius: 5px;
        }

        .nav-links a:hover {
            background-color: #34495e;
        }

        .register-btn {
            background-color: #3498db;
        }

        .login-btn {
            background-color: #27ae60;
        }

        .logout-btn {
            background-color: #e74c3c;
        }

        /* Hero */

        .hero {
            background-color: #34495e;
            color: white;
            text-align: center;
            padding: 55px 20px;
        }

        .hero h1 {
            font-size: 40px;
            margin: 0 0 15px;
        }

        .hero p {
            font-size: 18px;
            margin: 0;
        }

        /* Search */

        .search-section {
            width: 90%;
            max-width: 1000px;
            margin: -30px auto 30px;
            background-color: white;
            padding: 25px;
            border-radius: 10px;
            box-shadow: 0 3px 12px rgba(0,0,0,0.15);
        }

        .search-form {
            display: grid;
            grid-template-columns: 2fr 1fr auto;
            gap: 12px;
        }

        .search-form input,
        .search-form select {
            padding: 12px;
            border: 1px solid #ccc;
            border-radius: 6px;
            font-size: 15px;
        }

        .search-form button {
            padding: 12px 20px;
            background-color: #3498db;
            color: white;
            border: none;
            border-radius: 6px;
            cursor: pointer;
            font-size: 15px;
        }

        .search-form button:hover {
            background-color: #2980b9;
        }

        /* Books */

        .container {
            width: 90%;
            max-width: 1200px;
            margin: 0 auto 40px;
        }

        .section-title {
            text-align: center;
            color: #2c3e50;
            margin-bottom: 25px;
        }

        .books {
            display: grid;
            grid-template-columns: repeat(
                auto-fit,
                minmax(270px, 1fr)
            );
            gap: 20px;
        }

        .book {
            background-color: white;
            padding: 22px;
            border-radius: 10px;
            box-shadow: 0 2px 8px rgba(0,0,0,0.1);
        }

        .book h3 {
            color: #2c3e50;
            min-height: 45px;
            margin-top: 0;
        }

        .book p {
            margin: 9px 0;
        }

        .rating {
            color: #f39c12;
            font-weight: bold;
        }

        .details-btn {
            display: block;
            text-align: center;
            margin-top: 15px;
            padding: 11px;
            background-color: #2c3e50;
            color: white;
            text-decoration: none;
            border-radius: 6px;
        }

        .details-btn:hover {
            background-color: #1f2d3a;
        }

        /* No books */

        .no-books {
            background-color: white;
            text-align: center;
            padding: 35px;
            border-radius: 10px;
        }

        /* Footer */

        footer {
            background-color: #2c3e50;
            color: white;
            text-align: center;
            padding: 20px;
            margin-top: 40px;
        }

        /* Mobile */

        @media (max-width: 700px) {

            .navbar {
                justify-content: center;
                gap: 15px;
            }

            .search-form {
                grid-template-columns: 1fr;
            }

            .hero h1 {
                font-size: 30px;
            }

        }

    </style>

<link rel="stylesheet" href="style.css">
</head>

<body>


<!-- Navigation -->

<div class="navbar">

    <a href="index.php" class="logo">
        📚 Book Discovery
    </a>

    <div class="nav-links">

        <a href="index.php">
            Home
        </a>

        <?php if (isset($_SESSION["reader_id"])) { ?>

            <a href="preferences.php">
                Preferences
            </a>

            <a href="recommendations.php">
                Recommendations
            </a>

            <a href="logout.php" class="logout-btn">
                Logout
            </a>

        <?php } else { ?>

            <a href="register.php" class="register-btn">
                Register
            </a>

            <a href="login.php" class="login-btn">
                Login
            </a>

        <?php } ?>

    </div>

</div>


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
            🔍 Search
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

                            ⭐
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


<!-- Footer -->

<footer>

    <p>
        © 2026 Book Discovery and Recommendation System
    </p>

</footer>


</body>

</html>