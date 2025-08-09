<?php

namespace CesarJr\Social\Config;

use CodeIgniter\Config\BaseConfig;

class Social extends BaseConfig
{
    public array $providers = [
        // 'github' => [
        //     'client_id' => 'xxxxxxxxx',
        //     'client_secret' => 'xxxxxxxx',
        //     'redirect' => '/oauth/redirect',
        //     'enable_pkce' => true,
        //     'enable_state' => true,
        // ],
    ];

    /**
     * Use PKCE on all providers
     */
    public bool $enable_pkce_globally = false;

    /**
     * Use state on all providers
     */
    public bool $enable_state_globally = true;
}
