<?php

namespace App\Service;

use App\Entity\User;

class UserManager
{
    public function validate(User $user): bool
    {
        // Règle 1
        if (empty($user->getEmail())) {
            throw new \InvalidArgumentException('Email obligatoire');
        }

        if (!filter_var($user->getEmail(), FILTER_VALIDATE_EMAIL)) {
            throw new \InvalidArgumentException('Email invalide');
        }

        // Règle 2
        $password = $user->getPlainPassword();

        if (empty($password)) {
            throw new \InvalidArgumentException('Mot de passe obligatoire');
        }

        if (strlen($password) < 8) {
            throw new \InvalidArgumentException('Minimum 8 caractères');
        }

        if (!preg_match('/[A-Z]/', $password)) {
            throw new \InvalidArgumentException('Au moins une majuscule requise');
        }

        if (!preg_match('/[\W]/', $password)) {
            throw new \InvalidArgumentException('Au moins un caractère spécial requis');
        }

        // Règle 3 (métier avancée)
        if ($user->isBlocked() && $user->isTotpEnabled()) {
            throw new \InvalidArgumentException('Un utilisateur bloqué ne peut pas activer le TOTP');
        }

        return true;
    }
}