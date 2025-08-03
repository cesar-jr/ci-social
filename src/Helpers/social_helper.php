<?php

if (!function_exists('social')) {
    function social(?string $driver = null)
    {
        /** @var CesarJr\Social\Services\Social $social */
        $social = service('social');
        if (empty($driver)) {
            return $social;
        }
        return $social->driver($driver);
    }
}
