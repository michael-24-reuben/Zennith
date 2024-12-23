<?php
namespace models;

use database\MovieDatabase;
use models\movie\HTMLGalleryLoader;
use models\movie\HTMLMovieBuilder;

class Movie {
    private MovieDatabase $movie;
    public function __construct(MovieDatabase $movieData) {
        $this->movie = $movieData;
    }

    /**
     * Build HTML representation of the movie structure for display
     * @return HTMLMovieBuilder The class instance builder
    */
    public function createPoster(string $movieId): HTMLMovieBuilder {
        return new HTMLMovieBuilder($this->movie->getMoviesById($movieId));
    }

    /**
     * Build HTML representation of all movie structures for display
     * @return HTMLGalleryLoader The class instance builder
     */
    public function createGallery(): HTMLGalleryLoader {
        return new HTMLGalleryLoader($this->movie);
    }

}