<?php

namespace App\Utils;

trait IsAssocArrayTrait
{
    private function isAssocArray(mixed $data): bool
    {
        return is_array($data) && empty(array_diff_assoc($data, array_values($data))) === false;
    }

    /**
     * @link https://stackoverflow.com/questions/307674/how-to-remove-duplicate-values-from-a-multi-dimensional-array-in-php
     */
    private function arrayUnique(array $data): array
    {
        return array_values(
            array_map("unserialize", array_unique(array_map("serialize", $data)))
        );
    }
}
