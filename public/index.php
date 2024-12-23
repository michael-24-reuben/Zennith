<?php
session_start();

require_once "../config/config.php";
require_once "../app/helpers/Utils.php";
require_once "../app/models/movie/HTMLGalleryLoader.php";
require_once "../app/models/movie/HTMLMovieBuilder.php";
require_once "../app/database/DatabaseConfig.php";
require_once "../app/database/SQLDataBase.php";
require_once "../app/services/MovieService.php";
require_once "../app/models/Movie.php";
require_once "../app/database/MovieDatabase.php";
require_once "../app/database/ProfileDatabase.php";
require_once "../app/database/AdminsManager.php";

use database\MovieDatabase;
use database\ProfileDatabase;
use helpers\Utils;
use models\Movie;
use services\MovieService;

$conn = $_SESSION['conn'] ?? null;

$movieDatabase = MovieDatabase::connect($conn);

// validate table exists
$tableExists = $movieDatabase->exists();
if (!$tableExists) {
    Utils::writeConsole('Table `Movies` does not exist... creating it.');
    $mysqli_result = $movieDatabase->createIfNotExists();

    try {
        $json_file = SERVER_URL."/app/resources/logs/movies/logs.json";
        $json_data = file_get_contents($json_file);
        $movie = new MovieService($movieDatabase);
        $movie->insertJSONArray(json_decode($json_data, true, 512, JSON_THROW_ON_ERROR));
    } catch (Exception|JsonException $e) {
        echo $e->getMessage();
    }
}


// Check if the user is logged in
$userLoggedIn = !empty($_SESSION['userlog']);
$userEmail = $_SESSION['email']?? '_@';
$username = explode('@', $userEmail)[0];
$adminsManager = ProfileDatabase::groupAdmins();
$isAdmin = $adminsManager->getByEmail($userEmail) !== null;

// Handling search functionality
$searchedMovie = null;
if (isset($_GET['search'])) {
    $searchedMovie = $movieDatabase->getMoviesByTitle($_GET['search']);
}

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Zennith Home</title>

    <link rel="stylesheet" href="assets/css/styles.css">
    <link rel="stylesheet" href="assets/css/poster.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">

    <style>
        .content-popular {
            display: flex;
            justify-content: center;
            align-items: flex-start;
            /*flex-wrap: wrap;*/
            overflow-x: auto;
            margin-bottom: 50px;
        }
        .content-gallery {
            display: flex;
            justify-content: center;
            align-items: flex-start;
            flex-wrap: wrap;
            margin-bottom: 50px;
        }
    </style>

    <script src="assets/js/poster.js" defer></script>
    <script src="assets/js/common.js" defer></script>
</head>
<body>
    <header class="header">
        <img src="assets/images/icons/brand/logo-filled-colored.png" alt="Shows" class="logo">
        <div class="search-bar">
            <label>
                <input type="text" placeholder="Search shows">
            </label>
        </div>
        <!--    class2024-->
        <a class="user-nav">
            <?php
            if ($userLoggedIn && $isAdmin) {
                echo "<a href='pages/team/admin/profile.php' id='admin-profile' class='nav-profile'>
                        <img src='https://via.placeholder.com/32' alt='User' class='avatar'>
                        <span>$username</span>
                      </a>";
            } elseif ($userLoggedIn) {
                echo "<a href='pages/team/user/profile.php' id='user-profile' class='nav-profile'>
                        <img src='https://via.placeholder.com/32' alt='User' class='avatar'>
                        <span>$username</span>
                      </a>";
            } else {
                echo "<a href='pages/users/login.php' class='nav-item'>
                        <i class='fas fa-sign-in-alt'></i>
                        Login
                      </a>";
            }
            ?>
        </a>
    </header>

    <div class="root-container">
        <aside class="sidebar">
            <nav class="nav-main">
                <a href="#" class="nav-item">
                    <i class="fas fa-home"></i>
                    Home
                </a>
                <a href='pages/library.php' class='nav-item'>
                    <i class='fas fa-film'></i>
                    Library
                </a>
                <a href="pages/contact.php" class="nav-item">
                    <i class="fas fa-envelope"></i>
                    Contact
                </a>
            </nav>
            <nav class="nav-bottom">
                <?php
                if ($userLoggedIn && $isAdmin) {
                    echo "<a href='pages/team/admin/profile.php' class='nav-item'>
                <i class='fas fa-user-shield'></i>
                Admin
              </a>";
                }
                ?>
                <a href="#" class="nav-item">
                    <i class="fas fa-cog"></i>
                    Settings
                </a>
                <?php
                if ($userLoggedIn) {
                    echo "<a href='pages/users/logout.php' class='nav-item'>
                    <i class='fas fa-sign-out-alt'></i>
                    Log out
                  </a>";
                }
                ?>
            </nav>
        </aside>

        <main class="main-content">
            <div class="featured-show">
                <img src="../app/resources/uploads/movies/moana-2-2024/cover.jpg" alt="Featured Show">
                <!--        <img src="https://via.placeholder.com/1200x600" alt="Featured Show">-->
                <div class="show-info">
                    <h2>Stranger Things</h2>
                    <p>When a young boy vanishes, a small town uncovers a mystery involving secret experiments, terrifying supernatural forces, and one strange little girl.</p>
                    <div class="price">Watch Now</div>
                    <button class="btn btn-primary">Stream</button>
                </div>
            </div>

            <h2 class="title-tag">
                <span>Popular Movies</span>
                <hr >
            </h2>
            <!-- Popular Content: Display all movies -->
            <section class="content-popular">
                <div class="movie-gallery show-grid">
                    <?php /* In development. Different ids need to be used */
                    /*try {
                        $galleryLoader = new GalleryLoader($movieProps);
                        echo $galleryLoader->loadGalleryPopular();
                    } catch (JsonException|Exception $e) {
                        echo '<div class="error"> </div><br>Unable to load gallery<script>console.log("[error] Unable to load gallery.")</script>';
                    }*/
                    ?>
                </div>
            </section>

            <h2 class="title-tag">
                <span>Browse All</span>
                <hr >
            </h2>
            <!-- Main Content: Display all movies -->
            <section class="content-gallery">
                <div class="movie-gallery show-grid">
                    <?php
                    try {
                        $movie = new Movie($movieDatabase);
                        $movieGallery = $movie->createGallery();
                        echo $movieGallery->loadGalleryAll();
                    } catch (Exception $e) {
                        Utils::writeConsole("");
                        Utils::writeConsole("[error]: ".$e->getMessage());
                        Utils::writeConsole("@Location: ".$e->getFile()." (Line ".$e->getLine().")");
                        echo '<div class="error"> </div><br>Unable to load gallery<script>console.log("[error] Unable to load gallery.")</script>';
                    }
                    ?>
                </div>
            </section>
        </main>
    </div>

    <script>
        // Search functionality
        const searchInput = document.querySelector('.search-bar input');
        searchInput.addEventListener('input', function() {
            const searchTerm = this.value.toLowerCase();
            const movies = document.querySelectorAll('.movie-card');
            movies.forEach(movie => {
                const title = movie.querySelector('.title').textContent.toLowerCase();
                if (title.includes(searchTerm)) {
                    movie.style.display = '';
                } else {
                    movie.style.display = 'none';
                }
            });
        });

    </script>
</body>
</html>
