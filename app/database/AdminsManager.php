<?php

namespace database;

class AdminsManager {
    private array $admins;

    public function __construct(array $admins) {
        $this->admins = $admins;
    }

    public function getAll(): array {
        return $this->admins;
    }

    public function getById($adminId): ?array {
        foreach ($this->admins as $admin) {
            if ($admin['id'] === $adminId) {
                return $admin;
            }
        }
        return null;
    }

    public function getByEmail($email): ?array {
        foreach ($this->admins as $admin) {
            if ($admin['email'] === $email) {
                return $admin;
            }
        }
        return null;
    }
}
