<?php
namespace helpers;

use JsonException;

class Utils {
    public static function writeConsole(string $text): void {
        echo "<script>console.log('$text')</script>";
    }

    /**
     * Write updated data to a JSON file.
     *
     * @param string $filePath Path to the file where data should be written.
     * @param array $data The data to write.
     * @throws JsonException
     */
    public static function writeDataToFile(string $filePath, array $data): void {
        $jsonData = json_encode($data, JSON_THROW_ON_ERROR | JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);

        if (file_put_contents($filePath, $jsonData) === false) {
            Writer::writeConsoleError("Failed to write updated data to file", ".../Utils.php", 19);
        }
    }

    public static function isFilePathFormat($text): false|int {
        // Matches Linux/Unix or Windows-like file paths
        return preg_match('/^([a-zA-Z]:[\\\\\\/]|[\\\\\\/]|\.\/|\.\.\/)/', $text);
    }

    public static function isValidURL($text): bool {
        return filter_var($text, FILTER_VALIDATE_URL) !== false;
    }

    public static function checkFileOrURL($text): string {
        if (filter_var($text, FILTER_VALIDATE_URL)) {
            return "url";
        }

        if (file_exists($text) || preg_match('/^([a-zA-Z]:[\\\\\\/]|[\\\\\\/]|\.\/|\.\.\/)/', $text)) {
            return "file";
        }
        return "none";
    }
}