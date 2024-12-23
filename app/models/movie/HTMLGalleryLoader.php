<?php

namespace models\movie;

use database\MovieDatabase;

class HTMLGalleryLoader {
    private MovieDatabase $movieDatabase;

    public function __construct($movieDatabase) {
        $this->movieDatabase = $movieDatabase;
    }

    // Page loader for retrieving all movies from sql table and building html elements
    public function loadGalleryAll(): string {
        $outerHTML = "";
        $allMovies = $this->movieDatabase->getAll();

        if (empty($allMovies)) {
            return "<h2>No movies found in the database</h2>";
        }

        foreach ($allMovies as $movie) {
            $cardBuilder = new HTMLMovieBuilder($movie);
            $outerHTML .= $cardBuilder->buildProfileCard() . "\n";
        }

        return $outerHTML;
    }

    public function loadGalleryPopular(): string {
        $outerHTML = "";
        $popularityThreshold = 75;
        $allMovies = $this->movieDatabase->getAllSortedBy("rating");

        if (empty($allMovies)) {
            return "<h2>No movies found in the database</h2>";
        }

        // Filter movies that meet the popularity threshold
        $popularMovies = array_filter($allMovies, static function ($movie) use ($popularityThreshold) {
            return $movie['rating'] >= $popularityThreshold;
        });

        foreach ($popularMovies as $movie) {
            $cardBuilder = new HTMLMovieBuilder($movie);
            $outerHTML .= $cardBuilder->buildProfileCard() . "\n";
        }

        return $outerHTML;
    }

    public function loadGalleryDatabase(): string {
        $outerHTML = "";
        $allMovies = $this->movieDatabase->getAll();

        if (empty($allMovies)) {
            return "<h2>No movies found in the database</h2>";
        }

        foreach ($allMovies as $movie) {
            $cardBuilder = new HTMLMovieBuilder($movie);
            $outerHTML .= $cardBuilder->buildDatabaseCard() . "\n";
        }

        return $outerHTML;
    }
}

/*// Usage example
try {
    $movieLoader = new GalleryLoader();
    $movieLoader->iterateAndDisplay();
} catch (JsonException $e) {
    echo "Error decoding JSON: " . $e->getMessage();
} catch (Exception $e) {
    echo "An error occurred: " . $e->getMessage();
}*/