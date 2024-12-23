<?php

namespace models\movie;

use database\DatabaseConfig;
use Exception;
use helpers\Utils;
use helpers\Writer;

class HTMLMovieBuilder {
    private array $movieData;
    private string $requestPhp;

    public function __construct(array $movieData) {
        $this->movieData = $movieData;
        $this->requestPhp = SERVER_URL.'/public/movies/request.php';
    }


    public function buildProfileCard(): string {
        $id = $this->movieData['uniqid'];
        $title = $this->movieData['title'];
        $duration = $this->movieData['duration'];
        $genres = $this->movieData['genres'];
//        $description = $this->movieData['description'];
        $year = $this->movieData['release_year'];
        $rating = $this->movieData['rating'];

        $htmlClassRating = 'r-none';
        if ($rating >= 70) {
            $htmlClassRating = 'r-high';
        } elseif ($rating >= 40) {
            $htmlClassRating = 'r-mid';
        } elseif ($rating >= 1) {
            $htmlClassRating = 'r-low';
        }
        $htmlNodeGenres = $this->StringToSpans($genres);
        $coverPage = $this->getCoverPage($id);
        return $this->constructMoviePoster($id, $coverPage, $duration, $htmlClassRating, $rating, $year, $title, $htmlNodeGenres);
    }

    public function buildDatabaseCard(): string {
        $id = $this->movieData['uniqid'];
        $title = $this->movieData['title'];
        $duration = $this->movieData['duration'];
        $genres = $this->movieData['genres'];
        $description = $this->movieData['description'];
        $year = $this->movieData['release_year'];
        $rating = $this->movieData['rating'];

        $htmlNodeGenres = $this->StringToSpans($genres);
//        echo "==> $id\n";
        $coverPage = $this->getCoverPage($id);

        return $this->constructMovieDatabase($id, $coverPage, $title, $description, $year, $duration, $htmlNodeGenres, $rating, $genres);
    }


    private function findCoverImage($directory): ?string {
        // Ensure the directory exists and is readable
        if (!is_dir($directory) || !is_readable($directory)) {
            return null;
        }

        // Scan the directory for files
        $files = scandir($directory);
        if (!$files) {
            return null;
        }
        return $directory . 'cover.jpg';
    }

    // Method to build Split comma separated text to <span> tags for each
    private function StringToSpans($inputString): string {
        // Split the string by the semicolon
        $values = explode(',', $inputString);

        // Start with an empty result string
        $result = '';

        // Loop through the values and wrap each in a <span>
        foreach ($values as $value) {
            $result .= '<span>' . trim($value) . '</span>';
        }

        // Return the result with spans
        return $result;
    }

    /**
     * @param mixed $id
     * @param string|null $coverPage
     * @param mixed $duration
     * @param string $htmlClassRating
     * @param mixed $rating
     * @param mixed $year
     * @param mixed $title
     * @param string $htmlNodeGenres
     * @return string
     */
    public function constructMoviePoster(mixed $id, ?string $coverPage, mixed $duration, string $htmlClassRating, mixed $rating, mixed $year, mixed $title, string $htmlNodeGenres): string {
        $iconPath = htmlspecialchars(ASSETS_URL."/images/icons");
        $requestFav = htmlspecialchars(SERVER_URL."/public/movies/request.php?uniqid=$id&task=update_movie");
        $safeCoverPage = htmlspecialchars($coverPage);
        $safeTitle = htmlspecialchars($title);
//        $safeHtmlNodeGenres = htmlspecialchars($htmlNodeGenres, ENT_QUOTES, 'UTF-8'); // For HTML nodes

        return "<div class='movie-card glow-card' id='movie-id-" . htmlspecialchars($id) . "'>
              <div class='movie-cover'>
                <div class='display-poster'>
                  <img src='$safeCoverPage' alt='Thumbnail' class='movie-thumbnail glow-target'>
                  <div class='drtn-rtng'>
                    <span>" . htmlspecialchars($duration) . " min</span>
                    <div class='ver-line'></div>
                    <span class='rating $htmlClassRating'>" . htmlspecialchars($rating) . "%</span>
                  </div>
                  <span class='movie-year'>" . htmlspecialchars($year) . "</span>
                  <img src='$iconPath/fav-marked.png' onclick='$requestFav' alt='Favorites' class='small-img fav'>
                </div>
                <div class='content'>
                  <h2 class='movie-title'>$safeTitle</h2>
                  <div class='content-info'>
                    <img src='$iconPath/category-solid.png' class='small-img' alt='categories icon solid'>
                    <div class='content-genres'>
                      $htmlNodeGenres
                    </div>
                  </div>
                </div>
              </div>
            </div>";
    }


    /**
     * @param mixed $id
     * @param string|null $coverPage
     * @param mixed $title
     * @param string $description
     * @param mixed $year
     * @param mixed $duration
     * @param string $htmlNodeGenres
     * @param mixed $rating
     * @param mixed $genres
     * @return string
     */
    public function constructMovieDatabase(mixed $id, ?string $coverPage, mixed $title, string $description, mixed $year, mixed $duration, string $htmlNodeGenres, mixed $rating, mixed $genres): string {
//        $requestDelete = htmlspecialchars(SERVER_URL."/public/movies/request.php?uniqid=$id&task=delete_movie");
        $requestUpdate = htmlspecialchars(SERVER_URL."/public/movies/request.php?uniqid=$id&task=update_movie&redirect=/public/pages/team/admin/profile.php");
        $requestDelete = htmlspecialchars(SERVER_URL."/public/movies/request.php?uniqid=$id&task=delete_movie&redirect=/public/pages/team/admin/profile.php");
        return "<div class='content-item' data-id='$id'>
                <div class='content-preview'>
                  <div class='content-thumbnail'>
                    <img src='$coverPage' alt='$title'>
                  </div>
                  <div class='content-info'>
                    <h3>$title</h3>
                    <div class='content-meta'>
                      <span class='year'>$year</span>
                      <span class='duration'>$duration</span>
                      <span class='rating'>TV-14</span>
                    </div>
                    <div class='content-genres'>
                      $htmlNodeGenres
                    </div>
                  </div>
                  <button class='btn-edit' onclick='toggleEdit(\"$id\")'>
                    <i class='fas fa-pencil-alt'></i>
                  </button>
                </div>
                <div class='content-edit' id='edit-$id'>
                    <form class='delete-form' action='$requestDelete' method='post'>
                        <button type='submit' class='btn-delete'>
                            <i class='fas fa-trash'></i>
                        </button>
                    </form>
                
                    <form class='edit-form' action='$requestUpdate' method='POST'>
                      <div class='form-row'>
                        <div class='form-group'>
                          <label>Title</label>
                          <input type='text' name='title' value='$title' maxlength='255'>
                        </div>
                        <div class='form-group'>
                          <label>Release Year</label>
                          <input type='number' name='release_year' value='$year' min='1000' max='2030'>
                        </div>
                      </div>
                      <div class='form-row'>
                        <div class='form-group'>
                          <label>Duration</label>
                          <input type='text' name='duration' value='$duration' min='0'>
                        </div>
                        <div class='form-group'>
                          <label>Rating</label>
                          <input type='number' name='rating' value='$rating' min='0' max='100'>
                        </div>
                      </div>
                      <div class='form-group'>
                        <label>Genres</label>
                        <input type='text' name='genres' value='$genres'>
                      </div>
                      <div class='form-group'>
                        <label>Thumbnail URL</label>
                        <input type='url' name='thumbnail' value='$coverPage'>
                      </div>
                      <div class='form-group'>
                        <label>Description</label>
                        <textarea name='description' placeholder='$description' rows='4' cols='50'></textarea>
                      </div>
                      <div class='form-actions'>
                        <button type='button' class='btn-cancel' onclick='toggleHiddenItem(\"" . $id . "\")'>Cancel</button>
                        <button type='submit' class='btn-save'>Save Changes</button>
                      </div>
                    </form>

                </div>
              </div>";
    }

    /**
     * @param mixed $id
     * @return string|null
     */
    public function getCoverPage(string $id): ?string {
        $coverPage = ASSETS_URL . "/images/no-image-available.png";

        try {
            $objectById = DatabaseConfig::findObjectById($id);
            if (empty($objectById)) {
                Writer::writeConsoleError("Trying to access database array on null for `".$id."` at /resources/logs/movies/logs.json");
                return $coverPage;
            }
            $homeDir = $objectById["url"] ."/". $objectById['thumbnail']['url'];
            $coverPage = SERVER_URL . "/app/resources/uploads/movies/$homeDir";
            if (file_exists($coverPage)) {
                return $coverPage;
            }
        } catch (Exception) {}
        return $coverPage;
    }

}