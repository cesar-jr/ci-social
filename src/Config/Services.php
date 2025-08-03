<?php

namespace CesarJr\Social\Config;

use CodeIgniter\Config\BaseService;
use CesarJr\Social\Services\Social;

class Services extends BaseService
{
    public static function social(bool $getShared = true): Social
    {
        if ($getShared) {
            return static::getSharedInstance('social');
        }

        return new Social();
    }
}
