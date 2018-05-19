<?php

namespace Kommercio\Helpers;

class UtilityHelper {
    /**
     * Clean array deeply from null value
     * @param array $array
     * @return array
     */
    public static function arrayIgnoreNull(array $array) {
        $finalArray = [];

        foreach ($array as $key => $value) {
            if (is_array($value)) {
                $value = static::arrayIgnoreNull($array);

                if (empty($array)) $value = null;
            }

            if (is_null($value)) continue;

            $finalArray[$key] = $value;
        }

        return $finalArray;
    }
}
