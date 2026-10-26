# Book Discovery and Recommendation System

A PHP and MySQL web app where readers register, set reading preferences, browse and search books, read and write reviews, and get a ranked list of recommended books. Built as a DBMS course project (HTML, CSS, JavaScript, PHP, MySQL, XAMPP).

## Setup

1. Install XAMPP and start Apache and MySQL.
2. Copy this folder to `C:\xampp\htdocs\book_discovery`.
3. In phpMyAdmin, import `database/book_discovery.sql` (it creates the `book_discovery` database and its tables; it contains no book data).
4. Copy `db_config.example.php` to `db_config.php` and enter your MySQL details. `db_config.php` is ignored by Git.
5. Open http://localhost/book_discovery/

## Files

- `index.php`: search and browse books by title, author or genre
- `register.php`, `login.php`, `logout.php`: reader accounts (passwords are hashed)
- `preferences.php`: genre, language, minimum rating and book length
- `recommendations.php`: books ranked by a match score out of 100
- `book.php`, `review.php`: book details and reviews
- `db.php`: database connection and shared helpers; `header.php`, `footer.php`: shared page layout
- `style.css`: all styling

## Database tables

Authors, Readers, Genres, Books, Book_Genres, Preferences, Reviews