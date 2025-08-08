<?php

namespace CesarJr\Social\Config;

use CodeIgniter\Config\BaseService;
use CesarJr\Social\Services\Social;
use CesarJr\Social\Services\SocialMemory;

class Services extends BaseService
{
    public static function social(bool $getShared = true): Social
    {
        if ($getShared) {
            return static::getSharedInstance('social');
        }

        return new Social();
    }

    public static function socialMemory(bool $getShared = true): SocialMemory
    {
        if ($getShared) {
            return static::getSharedInstance('socialMemory');
        }

        return new SocialMemory();
    }
}
