<?php

namespace database;

use mysqli;
use mysqli_result;
use RuntimeException;

class SQLDataBase {
    protected mysqli $conn;
    protected string $tableName;
    protected array $columnsTitles = array();

    public function __construct(mysqli $conn, string $tableName, array $columnsTitles) {
        $this->conn = $conn;
        $this->tableName = $tableName;
        $this->columnsTitles = $columnsTitles;
    }

    // Method to handle table creation
    public function builder(): object {
        // Create an instance of the TableBuilder subclass
        return $this->TableBuilder($this->conn, $this->tableName, $this->columnsTitles);
    }

    // Subclass to handle table creation
    public function TableBuilder($conn, $tableName, $columnsTitles): object {
        return new class($conn, $tableName, $columnsTitles) extends SQLDataBase {

            public function create(string $tableName, array $columns): bool {
                $this->tableName = $tableName;
                $columnsDef = implode(", ", $columns);
                $sql = "CREATE TABLE $tableName ($columnsDef)";
                return $this->conn->query($sql);
            }

            public function createIfNotExists(string $tableName, array $columns): bool {
                $this->tableName = $tableName;
                $columnsDef = implode(", ", $columns);
                $sql = "CREATE TABLE IF NOT EXISTS $tableName ($columnsDef)";
                return $this->conn->query($sql);
            }

            public function drop(string $tableName) {
                $sql = "DROP TABLE IF EXISTS $tableName";
                return $this->conn->query($sql);
            }
        };
    }


    public function putRow(array $values): int|string {
        // Ensure the number of values matches the number of columns
        $valuesCount = count($values);
        $titlesCount = count($this->columnsTitles);
        if ($valuesCount !== $titlesCount) {
            echo "Expected number of values: " . $titlesCount . " but got " . $valuesCount;
            throw new RuntimeException("The number of values does not match the number of columns.");
        }

        // Build the SQL query dynamically
        $columns = implode(", ", $this->columnsTitles);
        $placeholders = implode(", ", array_fill(0, $titlesCount, "?"));
        $sql = "INSERT INTO $this->tableName ($columns) VALUES ($placeholders)";

        // Prepare and bind the statement
        $stmt = $this->conn->prepare($sql);
        $types = str_repeat("s", count($values)); // Assuming all values are strings for simplicity
        $stmt->bind_param($types, ...$values);

        // Execute the statement and check for success
        if (!$stmt->execute()) {
            throw new RuntimeException("Failed to insert row: " . $stmt->error);
        }

        return $stmt->affected_rows;
    }

    // Retrieve an entry by key (get in Java HashMap)
    public function getByKey(string $key) {
        $sql = "SELECT value_column FROM $this->tableName WHERE key_column = ?";
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("s", $key);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows > 0) {
            $row = $result->fetch_assoc();
            return $row['value_column'];
        }

        return null; // Key not found
    }

    // Check if a key exists (containsKey in Java HashMap)
    public function containsKey(string $key): bool {
        $sql = "SELECT 1 FROM $this->tableName WHERE key_column = ?";
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("s", $key);
        $stmt->execute();
        $result = $stmt->get_result();
        return $result->num_rows > 0;
    }

    // Remove an entry by key (remove in Java HashMap)
    public function remove(string $key): bool {
        $sql = "DELETE FROM $this->tableName WHERE key_column = ?";
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("s", $key);
        return $stmt->execute();
    }

    // Retrieve all keys (keySet in Java HashMap)
    public function keySet(): array {
        $sql = "SELECT key_column FROM $this->tableName";
        $result = $this->conn->query($sql);
        $keys = [];

        while ($row = $result->fetch_assoc()) {
            $keys[] = $row['key_column'];
        }

        return $keys;
    }

    // Clear all entries (clear in Java HashMap)

    public function clear(): mysqli_result|bool {
        $sql = "TRUNCATE TABLE $this->tableName";
        return $this->conn->query($sql);
    }

    // Get the size (size in Java HashMap)

    public function size(): int {
        $sql = "SELECT COUNT(*) AS total FROM $this->tableName";
        $result = $this->conn->query($sql);
        $row = $result->fetch_assoc();
        return $row['total'];
    }

    // Close the connection

    public function close(): void {
        $this->conn->close();
    }
}

/*// Usage example
$servername = "localhost";
$username = "class2024";
$password = "MccClass@2024";
$dbname = "mccfall24";
$tableName = "your_table_name"; // Replace with the actual table name

// Assuming your table has columns `key_column` and `value_column`
$hashMap = new MySQLHashMap($servername, $username, $password, $dbname, $tableName);
$hashMap -> put("exampleKey", "exampleValue");
echo $hashMap -> get("exampleKey");
$hashMap -> close();
*/
