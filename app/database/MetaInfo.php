<?php

namespace database;

use InvalidArgumentException;

class MetaInfo {
    private string $uniqueNumber;

    public function __construct(string $uniqueNumber) {
        $this->uniqueNumber = $uniqueNumber;
    }

    public function __toString(): string {
        return $this->uniqueNumber;
    }

    public function setCoverImageUrl(string $path): void {
        $imageData = file_get_contents($path);
        $uniqPath = $this->uniqPath();
        file_put_contents($uniqPath, $imageData);
    }

    public function setCoverImageBytes($bytes): void {
        $uniqPath = $this->uniqPath();
        file_put_contents($uniqPath, $bytes);
    }

    public function setCoverImage($path_or_bytes): void {
        $thumbnailDataType = explode(':', $path_or_bytes, 2);
        if (!isset($thumbnailDataType)) {
            throw new InvalidArgumentException('Invalid thumbnail data type. Expected `data:image/bytes:...` or `data:image/url:...`');
        }
        if (str_starts_with("data:image/bytes", $thumbnailDataType[0])) {
            $thumbnail = $thumbnailDataType[1];

            $this->setCoverImageBytes($thumbnail);
        } elseif (str_starts_with("data:image/url", $thumbnailDataType[0])) {
            $thumbnail = $thumbnailDataType[1];

            $this->setCoverImageURL($thumbnail);
        }
    }

    /**
     * @return string
     */
    public function uniqPath(): string {
        $path = SERVER_URL . '\\app\\resources\\uploads\\movies\\' . $this->uniqueNumber . '\\cover.jpg';

        // Create the directory and its subdirectories if they do not exist
        if (!file_exists($path) && !mkdir($path, 0777, true) && !is_dir($path)) {
            throw new \RuntimeException(sprintf('Directory "%s" was not created', $path));
        }
        return $path;
    }
}
