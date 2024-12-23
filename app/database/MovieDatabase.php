<?php
namespace database;

//require "../../config/config.php";
//require "SQLDataBase.php";
//require "../../app/services/MovieService.php";

use helpers\Utils;
use JsonException;
use Movie;
use mysql_xdevapi\Exception;
use mysqli;
use mysqli_sql_exception;
use RuntimeException;
use services\MovieService;


class MovieDatabase extends SQLDataBase {
    private static string $name = "Movies";
    private static ?array $movieColumns = null; // "uniqid", "title", "description", "release_year", "duration", "genres", "rating", "toggle_fav"
    private static string $data_struct = RESOURCE_PATH . '\\config\\data_struct.json';

    /**
     * Establishes a connection to the movie database and creates a new MovieProperties instance.
     *
     * @param mixed $conn The database connection object.
     * @return MovieDatabase A new instance of MovieProperties connected to the database.
     */
    public static function connect(mysqli $conn): MovieDatabase {
        try {
            self::$movieColumns = MovieService::getColumnsFromJson(self::$data_struct, self::$name);
            $tableName = "movies";
            return new MovieDatabase($conn, $tableName, self::$movieColumns);
        } catch (\JsonException $e) {
            Utils::writeConsole("Couldn't connect to JSON database at: " . self::$data_struct);
            throw new \RuntimeException($e->getMessage());
        }
    }

    /**
     * @return string
     */
    public static function getTableName(): string {
        return self::$name;
    }

    /**
     * @return array
     */
    public static function getColumns(): array {
        try { // return array of column names. Initialize if columns are null
            return self::$movieColumns ?? (self::$movieColumns = MovieService::getColumnsFromJson(self::$data_struct, self::$name));
        } catch (\JsonException $e) {
            throw new \RuntimeException($e->getMessage());
        }
    }

    // Create the movie table if it doesn't exist in the database
    public function createIfNotExists(): bool {
//        $sql = ["uniqid VARCHAR(255) PRIMARY KEY", "title VARCHAR(255) UNIQUE NOT NULL", "release_year INT", "duration INT", "genres TEXT", "rating DOUBLE", "toggle_fav BOOLEAN DEFAULT false",];
        try {
            $sql = MovieService::getColumnDefinitionsFromJson(self::$data_struct, self::$name);// Pass the connection to the parent class constructor
            return $this->builder()->createIfNotExists(self::$name, $sql);
        } catch (\JsonException $e) {
            Utils::writeConsole("Couldn't connect to JSON database at: " . self::$data_struct);
            throw new \RuntimeException($e->getMessage());
        }
    }


    /**
     * Inserts a new movie into the database.
     *
     * <h2>Configured Params</h2>
     *
     * <ul>
     * <li><b>string</b> title - The title of the movie.</li>
     * <li><b>string</b> description - A brief description of the movie.</li>
     * <li><b>int</b> release_year - The year the movie was released.</li>
     * <li><b>int</b> duration - The duration of the movie in minutes.</li>
     * <li><b>string</b> genres - A comma-separated list of genres.</li>
     * <li><b>int</b> rating - The rating of the movie (e.g., 8.5).</li>
     * </ul>
     *
     * @return MetaInfo|null The unique identifier of the inserted movie on success, or null on failure.
     */
    public function putMovie(...$values): ?MetaInfo {
        $uniqueNumber = uniqid('show-', true);
        $columns = self::getColumns();
        $columnCount = count($columns);
        $columnsAsString = implode(', ', $columns);
        $placeholders = str_repeat('?, ', $columnCount-1) . '?';
        $values[] = false;

        // Check if the number of columns matches the number of values.
        if ($columnCount - 1 !== count($values)) {
            error_log("The number of columns does not match the number of values. Expected: " . ($columnCount - 1) . " but got: " . count($values));
            Utils::writeConsole("The number of columns does not match the number of values");
            return null; // or handle the error as needed
        }

        $sql = "INSERT INTO $this->tableName ($columnsAsString) VALUES ($placeholders)";

        // Prepare statement
        if ($stmt = $this->conn->prepare($sql)) {
            // Dynamically create the types string based on the number of values
            $types = 's';
            foreach ($values as $value) {
                $types .= $this->getValueType($value); // concats sql data types
            }


            // Combine uniqueNumber with values to bind to the statement
            $stmt->bind_param($types, $uniqueNumber, ...$values);

            if ($stmt->execute()) {
                return new MetaInfo($uniqueNumber);
            }

            error_log("Error executing query: " . $stmt->error); // Logging error
        } else {
            error_log("Error preparing query: " . $this->conn->error); // Logging error
        }

        return null;
    }


    // Retrieve a movie by title
    public function getMoviesByTitle(string $title): false|array|null {
        $sql = "SELECT * FROM $this->tableName WHERE title = ?";
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("s", $title);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows > 0) {
            return $result->fetch_assoc(); // Return movie data
        }

        return null; // Movie not found
    }

    // Retrieve a movie by title
    public function getMoviesByRating(int $rating): false|array|null {
        $sql = "SELECT * FROM $this->tableName WHERE rating = ?";
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("i", $rating);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows > 0) {
            return $result->fetch_assoc(); // Return movie data
        }

        return null; // Movie not found
    }

    // Retrieve a movie by its id
    public function getMoviesById($id): false|array|null {
        $sql = "SELECT * FROM $this->tableName WHERE uniqid = ?";
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("s", $id); // Assuming `id` is an integer.
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows > 0) {
            return $result->fetch_assoc(); // Return movie data
        }

        return null; // Movie not found
    }

    // Retrieve a movie by any other column name value
    public function getMoviesByKey(string $key, $value): false|array|null {
        if (!in_array($key, $this->columnsTitles, true)) {
            throw new RuntimeException("Invalid column key provided.");
        }
        $sql = "SELECT * FROM $this->tableName WHERE $key = ?";
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("s", $value);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows > 0) {
            return $result->fetch_assoc();
        }

        return null;
    }


    // Update a movie's rating by title
    public function updateMovieValueById(string $uniqid, string $key, $value): bool {
        $sql = "UPDATE $this->tableName SET uniqid = ? WHERE $key = ?";
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("s".$this->getValueType($value), $value, $uniqid);
        return $stmt->execute();
    }

    /**
     * <ul>
     * <li><b>string</b> title - The title of the movie.</li>
     * <li><b>string</b> description - A brief description of the movie.</li>
     * <li><b>int</b> release_year - The year the movie was released.</li>
     * <li><b>int</b> duration - The duration of the movie in minutes.</li>
     * <li><b>string</b> genres - A comma-separated list of genres.</li>
     * <li><b>int</b> rating - The rating of the movie (e.g., 8.5).</li>
     * </ul>
     *
     * @param string $uniqid The unique identifier of the record to update.
     * @param array $values Associative array of column names and their new values.
     * @return bool True on success, false on failure.
     */
    public function updateMovieById(string $uniqid, array $values): bool {
        // Extract column names and values
        $columns = array_keys($values);

        // Dynamically create the SET clause with correct column names
        $columnPlaceholders = [];
        foreach ($columns as $column) {
            $columnPlaceholders[] = "$column = ?";
        }
        $placeholdersString = implode(', ', $columnPlaceholders);

        // Dynamically create the types string based on the value types
        $types = '';
        foreach ($values as $value) {
            $types .= $this->getValueType($value); // Assuming getValueType() determines the correct type
        }

        // Add the type for the unique ID (assuming it's a string, adjust as needed)
        $types .= 's';

        // Construct the SQL statement
        $sql = "UPDATE $this->tableName SET $placeholdersString WHERE uniqid = ?"; // Assuming 'id' is the column name

        // Prepare the statement
        if ($stmt = $this->conn->prepare($sql)) { // Line 141
            // Extract values and add the unique ID
            $valueArray = array_values($values);
            $valueArray[] = $uniqid; // Add the unique ID at the end

            // Bind parameters dynamically using unpacking
            $stmt->bind_param($types, ...$valueArray);

            // Execute the statement
            if ($stmt->execute()) {
                return true;
            }

            error_log("Error executing query: " . $stmt->error); // Log execution error
        } else {
            error_log("Error preparing query: " . $this->conn->error); // Log preparation error
        }

        return false;
    }

    public function updateRatingByTitle(string $title, float $rating): bool {
        $sql = "UPDATE $this->tableName SET rating = ? WHERE title = ?";
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("sd", $rating, $title);
        return $stmt->execute();
    }

    // Update a movie's rating by title
    public function updateToggleFavByTitle(string $title, bool $state): bool {
        $sql = "UPDATE $this->tableName SET toggle_fav = ? WHERE title = ?";
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("si", $$state, $title);
        return $stmt->execute();
    }

    public function updateRatingById($id, $rating): bool {
        $sql = "UPDATE $this->tableName SET rating = ? WHERE uniqid = ?";
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("si", $rating, $id);
        return $stmt->execute();
    }

    // Update a movie's rating by title
    public function updateToggleFavById(string $id, bool $state): bool {
        $sql = "UPDATE $this->tableName SET toggle_fav = ? WHERE uniqid = ?";
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("si", $state, $id);
        return $stmt->execute();
    }


    // Delete a movie by title
    public function destroy(): bool {
        $sql = "DROP TABLE IF EXISTS $this->tableName";
        return $this->conn->query($sql); // Directly return the result
    }


    public function removeMovieByTitle($title): bool {
        $sql = "DELETE FROM $this->tableName WHERE title = ?";
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("s", $title);
        return $stmt->execute();
    }

    public function removeMovieById($id): bool {
        try {
            $sql = "DELETE FROM $this->tableName WHERE uniqid = ?";
            $stmt = $this->conn->prepare($sql);
            $stmt->bind_param("s", $id);
            return $stmt->execute();
        } catch (Exception $e) {
            return false;
        }
    }


    // Retrieve all movie titles
    public function getAllMovieNames(): array {
        $sql = "SELECT title FROM $this->tableName";
        $result = $this->conn->query($sql);
        $movies = [];

        if ($result && $result->num_rows > 0) {
            while ($row = $result->fetch_assoc()) {
                $movies[] = $row['title'];
            }
        }

        return $movies;
    }


    /**
     * Verify if the table exists
     */
    public function exists(): bool {
        try {
            $sql = "DESCRIBE $this->tableName";
            $this->conn->query($sql);
            return true;
        } catch (mysqli_sql_exception) {
            return false;
        }
    }


    /**
     * @return array<array<string, mixed>> Each row as an associative array (map).
     */
    public function getAll(): array {
        $movies = [];

        try {
            $sql = "SELECT * FROM $this->tableName";
            $result = $this->conn->query($sql);
            if ($result && $result->num_rows > 0) {
                while ($row = $result->fetch_assoc()) {
                    $movies[] = $row; // Each row is an associative array
                }
            }
        } catch (mysqli_sql_exception) {
        }

        return $movies;
    }

    public function getAllSortedBy(string $key): array {
        $sql = "SELECT * FROM $this->tableName ORDER BY $key";

        $result = $this->conn->query($sql);
        if (!$result) {
            throw new RuntimeException("Failed to retrieve rows: " . $this->conn->error);
        }

        $rows = [];
        while ($row = $result->fetch_assoc()) {
            $rows[] = $row;
        }

        return $rows;
    }

    /**
     * @param $value
     * @param string $types
     * @return string
     */
    public function getValueType($value): string {
        $types = '';
        if (is_int($value)) {
            $types .= 'i';
        } elseif (is_float($value)) {
            $types .= 'd';
        } elseif (is_string($value)) {
            $types .= 's';
        } elseif (is_bool($value)) {
            $types .= 'i'; // Bind booleans as integers (0 or 1)
        }
        return $types;
    }

}

//$movieDatabase = MovieDatabase::connect($_SESSION["conn"]);
//echo $mysqli_result;

/*{
    $destroy = $movieDatabase->destroy();
    if ($destroy) {
        echo "Movie table destroyed successfully";
    } else {
        echo "Failed to destroy movie table";
    }
}*/
//$mysqli_result = $movieDatabase->createIfNotExists();


// Load movies from JSON file and insert into the database
/*{
    try {
        $json_file = "../../app/resources/uploads/movies/logs.json";
        $json_data = file_get_contents($json_file);
        $movie = new MovieService($movieDatabase);
        $movie->insertJSONArray(json_decode($json_data, true, 512, JSON_THROW_ON_ERROR));
    } catch (Exception|JsonException $e) {
        echo $e->getMessage();
    }
}*/


/*{
    $title = "Wicked";
    $year = 2014;
    $duration = 165;
    $genres = "Action;Adventure;Comedy;Drama;Fantasy";
    $description = "A boy on a path to defeat Fire lord Aizen";
    $rating = 5.9;
    $movieColumns = ["title", "year", "duration", "genres", "description", "rating"];
    $movieDatabase->putMovie($title, $year, $duration, $genres, $description, $rating);
}*/

/*{
    $allMovies = $movieDatabase->getAll();

    foreach ($allMovies as $movie) {
        echo "Title: " . $movie['title'] . "\n";
        echo "Description: " . $movie['description'] . "\n";
        echo "Year: " . $movie['release_year'] . "\n";
        echo "Rating: " . $movie['rating'] . "\n";
        echo "-----------------------------\n";
    }
}*/


/*{
    $movie = $movieDatabase->getMovie($title);
    if ($movie) {
        echo "Title: " . $movie['title'] . "\n";
        echo "Description: " . $movie['description'] . "\n";
        echo "Year: " . $movie['year'] . "\n";
        echo "Rating: " . $movie['rating'] . "\n";
    } else {
        echo "Movie not found";
    }
}*/