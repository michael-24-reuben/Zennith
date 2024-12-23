<?php

namespace database;

use helpers\Utils;
use JsonException;
use RuntimeException;

class DatabaseConfig {
    private static mixed $json_decode = null;
    public static string $records = '/logs/movies/logs.json';

    public static function findObjectById(string $id): ?array {
        foreach (self::getAllObjects(true) as $item) {
            if (isset($item['uniqid']) && $item['uniqid'] === $id) {
                return $item;
            }
        }
        return null; // Return null if no matching object is found
    }

    public static function updateObjectById(array $data, string $id, array $updatedData): void {}

    public static function deleteObjectById(array $data, string $id): void {}

    /**
     */
    public static function getAllObjects(bool $forceUpdate = false): array {
        return self::getAllObjectsOf(RESOURCE_PATH.self::$records, $forceUpdate);
    }

    /**
     * Read and decode JSON data from a file.
     *
     * @param string $jsonPath Path to the JSON file.
     * @param bool $forceUpdate If true, the function will force update the data.
     */
    public static function getAllObjectsOf(string $jsonPath, bool $forceUpdate = false): array {
        if (!$forceUpdate && self::$json_decode !== null) {
            return self::$json_decode;
        }

        // Check if the file exists and is readable
        if (!file_exists($jsonPath)) {
            echo "<script>console.log('File not found: .../Movies.json)</script>";
            throw new RuntimeException("File not found: ".$jsonPath);
        }

        try {
            $jsonString = file_get_contents($jsonPath);// Check if the file is empty
            if ($jsonString === false) {
                throw new RuntimeException("Failed to read file: " . $jsonPath);
            }// Decode into a PHP array
            return (self::$json_decode = json_decode($jsonString, true, 512, JSON_THROW_ON_ERROR));
        } catch (JsonException $e) {
            return self::$json_decode;
        }
    }

    /**
     * @throws JsonException
     */
    public static function logMovieData(array $data): void {
        Utils::writeDataToFile(RESOURCE_PATH.self::$records, $data);
    }
}