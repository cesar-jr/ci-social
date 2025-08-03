<?php

namespace CesarJr\Social\Providers;

use GuzzleHttp\RequestOptions;

class XProvider extends TwitterProvider
{
    /**
     * {@inheritdoc}
     */
    public function getAuthUrl($state)
    {
        return $this->buildAuthUrlFromBase('https://x.com/i/oauth2/authorize', $state);
    }

    /**
     * {@inheritdoc}
     */
    protected function getTokenUrl()
    {
        return 'https://api.x.com/2/oauth2/token';
    }

    /**
     * {@inheritdoc}
     */
    protected function getUserByToken($token)
    {
        $response = $this->getHttpClient()->get('https://api.x.com/2/users/me', [
            RequestOptions::HEADERS => ['Authorization' => 'Bearer ' . $token],
            RequestOptions::QUERY => ['user.fields' => 'profile_image_url,confirmed_email'],
        ]);

        return json_decode($response->getBody(), true)['data'] ?? null;
    }

    /**
     * {@inheritdoc}
     */
    protected function mapUserToObject(array $user)
    {
        $user = parent::mapUserToObject($user);

        $user->email = $user['confirmed_email'];

        return $user;
    }
}
