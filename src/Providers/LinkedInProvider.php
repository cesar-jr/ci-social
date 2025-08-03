<?php

namespace CesarJr\Social\Providers;

use CesarJr\Social\User;
use GuzzleHttp\RequestOptions;

class LinkedInProvider extends AbstractProvider
{
    /**
     * The scopes being requested.
     *
     * @var array
     */
    protected $scopes = ['r_liteprofile', 'r_emailaddress'];

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
        return $this->buildAuthUrlFromBase('https://www.linkedin.com/oauth/v2/authorization', $state);
    }

    /**
     * {@inheritdoc}
     */
    protected function getTokenUrl()
    {
        return 'https://www.linkedin.com/oauth/v2/accessToken';
    }

    /**
     * {@inheritdoc}
     */
    protected function getUserByToken($token)
    {
        $basicProfile = $this->getBasicProfile($token);
        $emailAddress = $this->getEmailAddress($token);

        return array_merge($basicProfile, $emailAddress);
    }

    /**
     * Get the basic profile fields for the user.
     *
     * @param  string  $token
     * @return array
     */
    protected function getBasicProfile($token)
    {
        $fields = ['id', 'firstName', 'lastName', 'profilePicture(displayImage~:playableStreams)'];

        if (in_array('r_liteprofile', $this->getScopes())) {
            array_push($fields, 'vanityName');
        }

        $response = $this->getHttpClient()->get('https://api.linkedin.com/v2/me', [
            RequestOptions::HEADERS => [
                'Authorization' => 'Bearer ' . $token,
                'X-RestLi-Protocol-Version' => '2.0.0',
            ],
            RequestOptions::QUERY => [
                'projection' => '(' . implode(',', $fields) . ')',
            ],
        ]);

        return (array) json_decode($response->getBody(), true);
    }

    /**
     * Get the email address for the user.
     *
     * @param  string  $token
     * @return array
     */
    protected function getEmailAddress($token)
    {
        $response = $this->getHttpClient()->get('https://api.linkedin.com/v2/emailAddress', [
            RequestOptions::HEADERS => [
                'Authorization' => 'Bearer ' . $token,
                'X-RestLi-Protocol-Version' => '2.0.0',
            ],
            RequestOptions::QUERY => [
                'q' => 'members',
                'projection' => '(elements*(handle~))',
            ],
        ]);

        return (array) (json_decode($response->getBody(), true)['elements'][0]['handle~'] ?? null);
    }

    /**
     * {@inheritdoc}
     */
    protected function mapUserToObject(array $user)
    {
        $preferredLocale = ($user['firstName']['preferredLocale']['language'] ?? null) .
            '_' . ($user['firstName']['preferredLocale']['country'] ?? null);

        $firstName = $user['firstName']['localized'][$preferredLocale] ?? null;
        $lastName = $user['lastName']['localized'][$preferredLocale] ?? null;

        $images = (array) ($user['profilePicture']['displayImage~']['elements'] ?? []);
        $avatar = array_find($images, fn($image) => (
            $image['data']['com.linkedin.digitalmedia.mediaartifact.StillImage']['storageSize']['width'] ??
            $image['data']['com.linkedin.digitalmedia.mediaartifact.StillImage']['displaySize']['width']
        ) === 100);
        $originalAvatar = array_find($images, fn($image) => (
            $image['data']['com.linkedin.digitalmedia.mediaartifact.StillImage']['storageSize']['width'] ??
            $image['data']['com.linkedin.digitalmedia.mediaartifact.StillImage']['displaySize']['width']
        ) === 800);

        return (new User)->setRaw($user)->map([
            'id' => $user['id'],
            'nickname' => null,
            'name' => $firstName . ' ' . $lastName,
            'first_name' => $firstName,
            'last_name' => $lastName,
            'email' => $user['emailAddress'] ?? null,
            'avatar' => $avatar['identifiers'][0]['identifier'] ?? null,
            'avatar_original' => $originalAvatar['identifiers'][0]['identifier'] ?? null,
        ]);
    }
}
