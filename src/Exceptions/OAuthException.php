<?php

namespace CesarJr\Social\Exceptions;

use ErrorException;

class OAuthException extends ErrorException
{
    public function __construct(string $error, ?string $description, ?string $uri)
    {

        $msg = "OAuth Authentication failed with $error";
        if (!empty($description)) {
            $msg .= ": $description";
        }
        if (!empty($uri)) {
            $msg .= "; See: $uri";
        }
        parent::__construct($msg);
    }
}
