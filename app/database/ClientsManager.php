<?php

namespace database;

class ClientsManager {
    private $users;

    public function __construct($users) {
        $this->users = $users;
    }

    public function getAll(): array {
        return $this->users;
    }

    public function getById($userId): ?array {
        foreach ($this->users as $user) {
            if ($user['id'] === $userId) {
                return $user;
            }
        }
        return null;
    }

    public function getByEmail($email): ?array {
        foreach ($this->users as $user) {
            if ($user['email'] === $email) {
                return $user;
            }
        }
        return null;
    }
}
