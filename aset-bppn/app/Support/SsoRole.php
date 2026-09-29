<?php
// PHP, SsoRole.php; Laravel; resolve roles from the authenticated SSO profile.

namespace App\Support;

final class SsoRole
{
    public static function resolve(array $profile): string
    {
        $roles = array_filter((array) ($profile['roles'] ?? []), 'is_string');
        $primary = $profile['primary_role'] ?? 'user';

        // Global operators retain access even when an older application assignment is lower.
        foreach (['maintenance', 'superadmin'] as $role) {
            if (($profile['is_' . $role] ?? false) === true
                || $primary === $role || in_array($role, $roles, true)) {
                return $role;
            }
        }

        if (is_string($profile['app_role'] ?? null) && $profile['app_role'] !== '') {
            return $profile['app_role'];
        }
        if (($profile['is_admin'] ?? false) === true || in_array('admin', $roles, true)) {
            return 'admin';
        }

        return is_string($primary) ? $primary : 'user';
    }
}

