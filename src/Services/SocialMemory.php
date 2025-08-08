<?php

namespace CesarJr\Social\Services;

class SocialMemory
{
    /**
     * Should save the key/value pair with the associated session.
     * $key can be 'state' or 'code_verifier' depending on what's in use.
     *
     * @param  string  $key
     * @param  string  $value
     */
    public function set($key, $value = null)
    {
        $session = service('session');
        $session->set($key, $value);
    }

    /**
     * Should return the value associated with the key.
     * $key is 'code_verifier' depending on what's in use.
     *
     * @param  string  $key
     */
    public function get($key)
    {
        $session = service('session');
        return $session->get($key);
    }

    /**
     * Should return the value associated with the key and remove it from memory.
     * $key can be 'state' or 'code_verifier' depending on what's in use.
     *
     * @param  string  $key
     * @param  string  $value
     */
    public function pop($key)
    {
        $session = service('session');
        $value = $session->get($key);
        $session->remove($key);
        return $value;
    }
}
