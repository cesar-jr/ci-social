<?php

namespace CesarJr\Social\Providers;

use CesarJr\Social\User;
use GuzzleHttp\RequestOptions;

class SlackOpenIdProvider extends AbstractProvider
{
    /**
     * The scopes being requested.
     *
     * @var array
     */
    protected $scopes = ['openid', 'email', 'profile'];

    /**
     * The separating character for the requested scopes.
     *
     * @var string
     */
    protected $scopeSeparator = ' ';

    /**
     * {@inheritdoc}
     */
    protected function getAuthUrl($state)
    {
        return $this->buildAuthUrlFromBase('https://slack.com/openid/connect/authorize', $state);
    }

    /**
     * {@inheritdoc}
     */
    protected function getTokenUrl()
    {
        return 'https://slack.com/api/openid.connect.token';
    }

    /**
     * {@inheritdoc}
     */
    protected function getUserByToken($token)
    {
        $response = $this->getHttpClient()->get('https://slack.com/api/openid.connect.userInfo', [
            RequestOptions::HEADERS => ['Authorization' => 'Bearer ' . $token],
        ]);

        return json_decode($response->getBody(), true);
    }

    /**
     * {@inheritdoc}
     */
    protected function mapUserToObject(array $user)
    {
        return (new User)->setRaw($user)->map([
            'id'              => $user['sub'] ?? null,
            'nickname'        => null,
            'name'            => $user['name'] ?? null,
            'email'           => $user['email'] ?? null,
            'avatar'          => $user['picture'] ?? null,
            'organization_id' => $user['https://slack.com/team_id'] ?? null,
        ]);
    }
}
