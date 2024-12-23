<?php

namespace helpers;

use JsonException;

class Writer {
    public static function writeOut(string $text): void {
        echo $text;
    }
    public static function writeConsole(string $text): void {
        echo '<script>console.log("' . $text . '")</script>';
    }
    public static function writeConsoleLn(string $text): void {
        echo '<script>console.log("' . $text . '\n")</script>';
    }
    public static function writeConsoleGroup(string $text): void {
        echo '<script>console.group("' . $text . '")</script>';
    }
    public static function writeConsoleGroupEnd(): void {
        echo '<script>console.groupEnd()</script>';
    }

    /**
     * @throws JsonException
     */
    public static function writeConsoleDir(array $array): void {
        echo '<script>console.dir(' . json_encode($array, JSON_THROW_ON_ERROR | JSON_PRETTY_PRINT) . ')</script>';
    }


    public static function writeConsoleInfo(string $text, string $location='', int $line=-1): void {
        if ($location) {
            $location = "\n@Location: ".$location;
        }
        if ($line > -1) {
            $location.= " (Line: ".$line.")";
        }
        echo '<script>console.log("\n[info]: ' . $text . $location . '")</script>';
    }
    public static function writeConsoleWarn(string $text, string $location='', int $line=-1): void {
        if ($location) {
            $location = "\n@Location: ".$location;
        }
        if ($line > -1) {
            $location.= " (Line: ".$line.")";
        }
        echo '<script>console.log("\n[warn]: ' . $text . $location . '")</script>';
    }
    public static function writeConsoleError(string $text, string $location='', int $line=-1): void {
        if ($location) {
            $location = "\n@Location: ".$location;
        }
        if ($line > -1) {
            $location.= " (Line: ".$line.")";
        }
        echo '<script>console.log("\n[error]: ' . $text . $location . '")</script>';
    }
    public static function writeConsoleCritical(string $text, string $location='', int $line=-1): void {
        if ($location) {
            $location = "\n@Location: ".$location;
        }
        if ($line > -1) {
            $location.= " (Line: ".$line.")";
        }
        echo '<script>console.log("\n[critical]: ' . $text . $location . '")</script>';
    }
    public static function writeConsoleDebug(string $text, string $location='', int $line=-1): void {
        if ($location) {
            $location = "\n@Location: ".$location;
        }
        if ($line > -1) {
            $location.= " (Line: ".$line.")";
        }
        echo '<script>console.log("\n[debug]: ' . $text . $location . '")</script>';
    }
}