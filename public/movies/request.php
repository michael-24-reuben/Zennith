<?php

require_once "../../config/config.php";
require_once "../../app/database/SQLDataBase.php";
require_once "../../app/services/MovieService.php";
require_once "../../app/database/MetaInfo.php";
require_once "../../app/database/MovieDatabase.php";

use database\MovieDatabase;
use services\MovieService;


$conn = $_SESSION['conn'] ?? null;
if ($conn === null) {
    header('Location: ' . SERVER_URL . '/public/index.php');
}

$task = $_GET['task'] ?? '';
header('Content-Type: application/json');
/**
 * @return array
 */
function retrieveFormData(): array {
    $title = ($_POST['title'] ? (string)$_POST['title'] : '');
    $duration = isset($_POST['duration']) ? (int)$_POST['duration'] : -1;
    $post_release_year = $_POST['release_year'];
    $release_year = isset($post_release_year) && strtotime($post_release_year) ? (int)date('Y', strtotime($post_release_year)) : -1;
    $genres = ($_POST['genres'] ? (string)$_POST['genres'] : '');
    $description = ($_POST['description'] ? (string)$_POST['description'] : '');
    $rating = isset($_POST['rating']) ? (int)$_POST['rating'] : -1;
    $toggle_fav = isset($_POST['toggle_fav']) && $_POST['toggle_fav'];
    $thumbnail = $_POST['thumbnail'];
    return array($title, $duration, $release_year, $genres, $description, $rating, $toggle_fav, $thumbnail);
}

switch ($task) {
    case 'add_movie':
        // Retrieve form data
        [$title, $duration, $release_year, $genres, $description, $rating, $toggle_fav, $thumbnail] = retrieveFormData();
        $thumbnailDataType = explode(':', $thumbnail, 2);

        // Create an instance of your class
        $movieDatabase = MovieDatabase::connect($conn);

        // Call the putMovie method
        try {
            $metaInfo = $movieDatabase->putMovie($title, $description, $release_year, $duration, $genres, $rating);
            if ($thumbnailDataType === null || str_starts_with("data:image/bytes", trim($thumbnailDataType[0]))) {
                $thumbnail = $thumbnailDataType[1];

                $metaInfo->setCoverImageBytes($thumbnail);
            } elseif (str_starts_with("data:image/url", trim($thumbnailDataType[0]))) {
                $thumbnail = $thumbnailDataType[1];

                $metaInfo->setCoverImageURL($thumbnail);
            }
            echo json_encode(['success' => true, 'message' => 'New Movie added'], JSON_THROW_ON_ERROR);
        } catch (mysqli_sql_exception $e) {
            $message = urlencode($e->getMessage());
            echo json_encode(['success' => false, 'message' => $message, 'file' => $e->getFile(), 'line' => $e->getLine()], JSON_THROW_ON_ERROR);
            exit();
        }
        break;

    case 'update_movie':
        if ($uniqid = $_GET['uniqid']?? false) {
            [$title, $duration, $release_year, $genres, $description, $rating, $toggle_fav, $thumbnail] = retrieveFormData();

            // Create an instance of your class
            $movieDatabase = MovieDatabase::connect($conn);

            // Call the updateMovie method
            $movieDatabase->updateMovieById($uniqid, [
                "title" => $title,
                "description" => $description,
                "release_year" => $release_year,
                "duration" => $duration,
                "genres" => $genres,
                "rating" => $rating,
                "toggle_fav" => $toggle_fav
            ]);
            echo json_encode(['success' => false, "message" => "Movie updated"], JSON_THROW_ON_ERROR);
        }
        else {
            echo json_encode(['success' => false, "message" => "Movie id not provided"], JSON_THROW_ON_ERROR);
        }

        break;

    case "delete_movie":
        if ($uniqid = $_GET['uniqid']?? false) {
            $movieDatabase = MovieDatabase::connect($conn);
            $success = $movieDatabase->removeMovieById($uniqid);

            echo json_encode(['success' => $success], JSON_THROW_ON_ERROR);

        } else {
            echo json_encode(['success' => false, 'message' => 'Invalid request for Movie id: '.$_GET['uniqid']], JSON_THROW_ON_ERROR);
        }
        break;

    default:
        echo json_encode(['success' => false, 'message' => 'Invalid `task` request'], JSON_THROW_ON_ERROR);
}
/*if ($_SERVER['REQUEST_METHOD'] === 'POST') { }*/

// Read the args and redirect to that page
if ($redirect_url = $_GET['redirect']?? false) {
    $redirect_url = SERVER_URL . $redirect_url;
    header("Location: " . $redirect_url);
}
exit;
