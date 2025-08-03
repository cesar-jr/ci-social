<?php

namespace CesarJr\Social\Providers;

use Exception;
use CesarJr\Social\User;
use CesarJr\Social\Token;
use Firebase\JWT\JWK;
use Firebase\JWT\JWT;
use GuzzleHttp\RequestOptions;

class GoogleProvider extends AbstractProvider
{
    /**
     * The separating character for the requested scopes.
     *
     * @var string
     */
    protected $scopeSeparator = ' ';

    /**
     * The scopes being requested.
     *
     * @var array
     */
    protected $scopes = [
        'openid',
        'profile',
        'email',
    ];

    /**
     * {@inheritdoc}
     */
    protected function getAuthUrl($state)
    {
        return $this->buildAuthUrlFromBase('https://accounts.google.com/o/oauth2/auth', $state);
    }

    /**
     * {@inheritdoc}
     */
    protected function getTokenUrl()
    {
        return 'https://www.googleapis.com/oauth2/v4/token';
    }

    /**
     * {@inheritdoc}
     */
    protected function getUserByToken($token)
    {
        if ($this->isJwtToken($token)) {
            return $this->getUserFromJwtToken($token);
        }

        $response = $this->getHttpClient()->get('https://www.googleapis.com/oauth2/v3/userinfo', [
            RequestOptions::QUERY => [
                'prettyPrint' => 'false',
            ],
            RequestOptions::HEADERS => [
                'Accept' => 'application/json',
                'Authorization' => 'Bearer ' . $token,
            ],
        ]);

        return json_decode((string) $response->getBody(), true);
    }

    /**
     * {@inheritdoc}
     */
    public function refreshToken($refreshToken)
    {
        $response = $this->getRefreshTokenResponse($refreshToken);

        return new Token(
            $response['access_token'] ?? null,
            $response['refresh_token'] ?? $refreshToken,
            $response['expires_in'] ?? null,
            explode($this->scopeSeparator, $response['scope'] ?? '')
        );
    }

    /**
     * {@inheritdoc}
     */
    protected function mapUserToObject(array $user)
    {
        return (new User)->setRaw($user)->map([
            'id' => $user['sub'] ?? null,
            'nickname' => $user['nickname'] ?? null,
            'name' => $user['name'] ?? null,
            'email' => $user['email'] ?? null,
            'avatar' => $user['picture'] ?? null,
        ]);
    }

    /**
     * Determine if the given token is a JWT (ID token).
     *
     * @param  string  $token
     * @return bool
     */
    protected function isJwtToken($token)
    {
        return substr_count($token, '.') === 2 && strlen($token) > 100;
    }

    /**
     * Get user data from Google ID token (JWT).
     *
     * @param  string  $idToken
     * @return array
     *
     * @throws \Exception
     */
    protected function getUserFromJwtToken($idToken)
    {
        try {
            $user = (array) JWT::decode(
                $idToken,
                JWK::parseKeySet($this->getGoogleJwks())
            );

            if (
                ! isset($user['iss']) ||
                $user['iss'] !== 'https://accounts.google.com'
            ) {
                throw new Exception('Invalid ID token issuer.');
            }

            if (! isset($user['aud']) || $user['aud'] !== $this->clientId) {
                throw new Exception('Invalid ID token audience.');
            }

            return $user;
        } catch (Exception $e) {
            throw new Exception('Failed to verify Google JWT token: ' . $e->getMessage());
        }
    }

    /**
     * Get Google's JSON Web Key Set for JWT verification.
     *
     * @return array
     */
    protected function getGoogleJwks()
    {
        $response = $this->getHttpClient()->get(
            'https://www.googleapis.com/oauth2/v3/certs'
        );

        return json_decode((string) $response->getBody(), true);
    }
}
