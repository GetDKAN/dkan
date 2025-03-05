<?php

namespace Drupal\harvest;

class Util
{

    /**
     * Generate a hash of a data structure.
     *
     * @param mixed $item
     *   The data structure to hash.
     *
     * @return string
     *   Hash of the serialized data structure.
     */
    public static function generateHash($item): string
    {
        // Encode as JSON and then decode as associative arrays.
        // @todo Is this the most efficient way to convert an arbitrary
        //   data structure into an array?
        $decoded = json_decode(
            json_encode($item, JSON_THROW_ON_ERROR),
            true
        );
        // Sort the array by keys.
        static::recursiveKeySort($decoded);
        return hash('sha256', serialize($decoded));
    }

    /**
     * Legacy hash generation.
     *
     * We use this for a secondary comparison, if generateHash() says they
     * don't match.
     *
     * @param mixed $item
     *   The data structure to hash.
     *
     * @return string
     *   Hash of the serialized data structure.
     */
    public static function legacyGenerateHash($item): string
    {
        return hash('sha256', serialize($item));
    }

    public static function getDatasetId($dataset): string
    {
        if (!is_object($dataset)) {
            throw new \Exception("The dataset " . json_encode($dataset) . " is not an object.");
        }

        if (filter_var($dataset->identifier, FILTER_VALIDATE_URL)) {
            $i = explode("/", $dataset->identifier);
            $id = end($i);
        } else {
            $id = $dataset->identifier;
        }
        return "{$id}";
    }

    /**
     * Sort an array in place by key.
     *
     * Recursively applies ksort() to any value in the array that is an array.
     *
     * @param $array
     *   The array to be sorted.
     * @param $flags
     *   Flags to pass along to ksort().
     *
     * @see \ksort()
     */
    public static function recursiveKeySort(&$array, int $flags = SORT_REGULAR): void
    {
        foreach ($array as &$value) {
            if (is_array($value)) {
                static::recursiveKeySort($value);
            }
        }
        ksort($array, $flags);
    }
}
