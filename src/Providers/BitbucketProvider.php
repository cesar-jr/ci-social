<?php

namespace CesarJr\Social\Providers;

use CesarJr\Social\User;
use Exception;
use GuzzleHttp\RequestOptions;

class BitbucketProvider extends AbstractProvider
{
    /**
     * The scopes being requested.
     *
     * @var array
     */
    protected $scopes = ['email'];

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
        return $this->buildAuthUrlFromBase('https://bitbucket.org/site/oauth2/authorize', $state);
    }

    /**
     * {@inheritdoc}
     */
    protected function getTokenUrl()
    {
        return 'https://bitbucket.org/site/oauth2/access_token';
    }

    /**
     * {@inheritdoc}
     */
    protected function getUserByToken($token)
    {
        $response = $this->getHttpClient()->get('https://api.bitbucket.org/2.0/user', [
            RequestOptions::QUERY => ['access_token' => $token],
        ]);

        $user = json_decode($response->getBody(), true);

        if (in_array('email', $this->scopes, true)) {
            $user['email'] = $this->getEmailByToken($token);
        }

        return $user;
    }

    /**
     * Get the email for the given access token
     * 
     * @param string $token
     * @return string|null
     */
    private function getEmailByToken($token)
    {
        $emailsUrl = 'https://api.bitbucket.org/2.0/user/emails?access_token=' . $token;

        try {
            $response = $this->getHttpClient()->get($emailsUrl);
        } catch (Exception $e) {
            return;
        }

        $emails = json_decode($response->getBody(), true);

        foreach ($emails['values'] as $email) {
            if ($email['type'] === 'email' && $email['is_primary'] && $email['is_confirmed']) {
                return $email['email'];
            }
        }
    }

    /**
     * {@inheritdoc}
     */
    protected function mapUserToObject(array $user)
    {
        return (new User)->setRaw($user)->map([
            'id'       => $user['uuid'],
            'nickname' => $user['username'],
            'name'     => $user['display_name'] ?? null,
            'email'    => $user['email'] ?? null,
            'avatar'    => $user['links']['avatar']['href'] ?? null,
        ]);
    }
}
