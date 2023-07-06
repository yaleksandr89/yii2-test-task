<?php

namespace app\components;

use RuntimeException;

class Images
{
    public static function generateMiniature(string $link, array $size): string
    {
        $isSuccess = rand(0, 1);

        if (!$isSuccess) {
            throw new RuntimeException('Something bad happened in the method ' . __METHOD__);
        }


        return $link . '-miniature';
    }

    public static function generateWatermarkedMiniature(string $link, array $size): string
    {
        return self::generateMiniature($link, $size) . '-watermarked';
    }
}
