<?php
namespace services;

//require_once "../database/MetaInfo.php";
//require_once "../database/DatabaseConfig.php";
//require_once "../helpers/Writer.php";
//require_once "../helpers/Utils.php";

use database\DatabaseConfig;
use database\MovieDatabase;
use Exception;
use helpers\Utils;
use helpers\Writer;
use JsonException;

class MovieService {
    private MovieDatabase $movieDatabase;
    public function __construct(MovieDatabase $database) {
        // Load the MovieProperties database
        $this->movieDatabase = $database;
    }

    /**
     * @param mixed $data The JSON Array to insert to SQL query
     * @throws JsonException
     */
    public function insertJSONArray(array $data): void {
        $querySuccessful = $this->movieDatabase->createIfNotExists();

        if (!$querySuccessful) {
            error_log("[INFO] Query ignored. Table already exists");
        }

        foreach ($data as $key => $movie) {
            $data[$key] = $this->insertJSONObject($movie);
        }

        DatabaseConfig::logMovieData($data);
        Writer::writeConsoleInfo("Query finished. ".count($data)." movies inserted into SQL table successfully");
    }

    /**
     * @param mixed $movie The JSON Object to insert to SQL query
     * @return array The updated JSON Object with the unique ID
     */
    public function insertJSONObject(array $movie): array {
        $title = $movie['title'] ?? 'N/A';
        $url = $movie['url'] ?? 'N/A';
        $releaseDate = $movie['releaseDate'] ?? 'N/A';
        $duration = $movie['duration'] ?? 'N/A';
        $genres = $movie['genres'] ?? 'N/A';
        $description = $movie['description'] ?? 'N/A';
        $rating = isset($movie['rating']) && is_numeric($movie['rating']) ? (int)$movie['rating'] : 0;
        $thumbnail = $movie['thumbnail']['url'] ?? 'N/A';
        $releaseYear = isset($releaseDate) && strtotime($releaseDate) ? date('Y', strtotime($releaseDate)) : 'N/A';

        try {
            $metaInfo = $this->movieDatabase->putMovie($title, $description, $releaseYear, $duration, $genres, $rating);
            $metaInfo?->setCoverImage($thumbnail);
            $movie['uniqid'] = $metaInfo?->__toString();
            $movie['timestamp'] = date('Y-m-d H:i:s');
        } catch (Exception $e) {
            Writer::writeConsoleError($e->getMessage(), $e->getFile(), $e->getLine());
        }

        return $movie;
    }


    /**
     * @throws JsonException
     */
    public static function getColumnsFromJson(string $jsonPath, string $tableName): array {
        // Read the JSON from the file
        $jsonData = file_get_contents($jsonPath);
        $data = json_decode($jsonData, true, 512, JSON_THROW_ON_ERROR);

        // Loop through the tables in the JSON
        foreach ($data as $table) {
            // Check if the table name matches
            if ($table['name'] === $tableName) {
                // Extract the 'column' values and return them as an array
                $columns = [];
                foreach ($table['columns'] as $column) {
                    $columns[] = $column['column'];
                }
                return $columns;  // Return the array of columns
            }
        }

        // Return an empty array if the table name is not found
        return [];
    }

    /**
     * @throws JsonException
     */
    public static function getColumnDefinitionsFromJson(string $jsonPath, string $tableName): array {
        // Read the JSON from the file
        $jsonData = file_get_contents($jsonPath);
        $data = json_decode($jsonData, true, 512, JSON_THROW_ON_ERROR);

        // Loop through the tables in the JSON
        foreach ($data as $table) {
            // Check if the table name matches
            if ($table['name'] === $tableName) {
                $columnDefinitions = [];

                // Loop through columns and create the concatenated string
                foreach ($table['columns'] as $column) {
                    $columnDefinitions[] = $column['column'] . ' ' . $column['type'] . ($column['constraints'] ? ' ' . $column['constraints'] : '');
                }

                // Join all column definitions with commas and return the result
                return $columnDefinitions;
            }
        }

        // Return an empty array if the table name is not found
        return [];
    }

}