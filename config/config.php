<?php

define('BASE_PATH', dirname(__DIR__));  // Project root directory
const RESOURCE_PATH = BASE_PATH . '\\app\\resources';  // Resources folder path

// Get the current URL (with protocol, domain, and path)
/**
 * @Note Not Server secure
 * @return mixed
 */
function getHTTP_HOST(): mixed {
    return $_SERVER['HTTP_HOST'];
}

if (PHP_SAPI !== "cli") {
    // Check if the connection is secure (HTTPS)
    $protocol = isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? "https" : "http";

    // Get the host (e.g., localhost:63343)
    $host = getHTTP_HOST();

    $lclPath =  max(strrpos(BASE_PATH, '\\'), strrpos(BASE_PATH, '/'));
    // Build the full URL
    $currentUrl = "$protocol://$host/Zennith";

    define("SERVER_URL", $currentUrl);
    define("ASSETS_URL", SERVER_URL . '/public/assets');
}

/*{
    const BASE_PATH = "https://www.wprofessionals.com/wprofessionals.com/class2024/Michael/Zennith";  // Project root directory: References the local absolute path and used for backend path resolution
    const RESOURCE_PATH = BASE_PATH . '\\app\\resources';  // Resources folder path

    // Build the full URL
    $currentUrl = "https://www.wprofessionals.com/wprofessionals.com/class2024/Michael/Zennith";

    define("SERVER_URL", $currentUrl); // Project server URL: Project server URL: References an absolute path for frontend path resolutions. Used from public files
    define("ASSETS_URL", SERVER_URL. '/public/assets'); // Project assets URL: Project assets URL: References an absolute path for frontend path resolutions. Used from public files
}*/

require_once BASE_PATH."/app/helpers/Writer.php";
require_once "connection.php";

