<?php

namespace database;

class ProfileDatabase {
    private static array $admins = [
        [
            "id" => 1,
            "username" => "admin",
            "email" => "admin@gmail.com",
            "roles" => ["admin"],
            "last_login" => "2024-12-13T12:00:00"
        ],
        [
            "id" => 2,
            "username" => "michael",
            "email" => "michaelk1348@my.middlesexcc.edu",
            "roles" => ["admin"],
            "last_login" => "2024-12-12T15:00:00"
        ]
    ];

    private array $users = [];

    public static function groupAdmins(): AdminsManager {
        return new AdminsManager(self::$admins);
    }

    public static function groupClients(): ClientsManager {
        return new ClientsManager(self::$admins);
    }
}

