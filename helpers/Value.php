<?php
declare(strict_types=1);

if (!function_exists('db_bool')) {
    /**
     * Convert various database-stored representations into a boolean value.
     *
     * Legacy databases may contain textual values such as "yes", "vip" or
     * "normal" instead of 0/1. This helper normalises those values so callers
     * can reliably decide whether a student is VIP.
     */
    function db_bool(mixed $value): bool {
        if (is_bool($value)) {
            return $value;
        }

        if (is_int($value) || is_float($value)) {
            return ((int)$value) !== 0;
        }

        if ($value === null) {
            return false;
        }

        if (is_string($value)) {
            $normalized = strtolower(trim($value));
            if ($normalized === '') {
                return false;
            }

            if (is_numeric($normalized)) {
                return ((int)$normalized) !== 0;
            }

            $truthy = ['true', 'yes', 'y', 'on', 'vip'];
            $falsy  = ['false', 'no', 'n', 'off', 'student', 'standard', 'normal'];

            if (in_array($normalized, $truthy, true)) {
                return true;
            }

            if (in_array($normalized, $falsy, true)) {
                return false;
            }

            return false;
        }

        return (bool)$value;
    }
}
