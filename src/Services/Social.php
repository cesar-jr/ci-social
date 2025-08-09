<?php

namespace CesarJr\Social\Services;

use CesarJr\Social\Exceptions\DriverMissingConfigurationException;
use CesarJr\Social\Utilities\Str;
use CesarJr\Social\Utilities\Manager;
use CesarJr\Social\Providers\GithubProvider;
use CesarJr\Social\Providers\FacebookProvider;
use CesarJr\Social\Providers\GoogleProvider;
use CesarJr\Social\Providers\LinkedInProvider;
use CesarJr\Social\Providers\LinkedInOpenIdProvider;
use CesarJr\Social\Providers\BitbucketProvider;
use CesarJr\Social\Providers\GitlabProvider;
use CesarJr\Social\Providers\SlackOpenIdProvider;
use CesarJr\Social\Providers\SlackProvider;
use CesarJr\Social\Providers\TwitchProvider;
use CesarJr\Social\Providers\TwitterProvider;
use CesarJr\Social\Providers\XProvider;

class Social extends Manager
{
    /**
     * Create an instance of the specified driver.
     */
    protected function createGithubDriver()
    {
        $config = config('Social')->providers['github'] ?? [];

        return $this->buildProvider(GithubProvider::class, $config);
    }

    /**
     * Create an instance of the specified driver.
     */
    protected function createFacebookDriver()
    {
        $config = config('Social')->providers['facebook'] ?? [];

        return $this->buildProvider(FacebookProvider::class, $config);
    }

    /**
     * Create an instance of the specified driver.
     */
    protected function createGoogleDriver()
    {
        $config = config('Social')->providers['google'] ?? [];

        return $this->buildProvider(GoogleProvider::class, $config);
    }

    /**
     * Create an instance of the specified driver.
     */
    protected function createLinkedinDriver()
    {
        $config = config('Social')->providers['linkedin'] ?? [];

        return $this->buildProvider(LinkedInProvider::class, $config);
    }

    /**
     * Create an instance of the specified driver.
     */
    protected function createLinkedinOpenidDriver()
    {
        $config = config('Social')->providers['linkedin-openid'] ?? [];

        return $this->buildProvider(LinkedInOpenIdProvider::class, $config);
    }

    /**
     * Create an instance of the specified driver.
     */
    protected function createBitbucketDriver()
    {
        $config = config('Social')->providers['bitbucket'] ?? [];

        return $this->buildProvider(BitbucketProvider::class, $config);
    }

    /**
     * Create an instance of the specified driver.
     */
    protected function createGitlabDriver()
    {
        $config = config('Social')->providers['gitlab'] ?? [];

        return $this->buildProvider(GitlabProvider::class, $config)->setHost($config['host'] ?? null);
    }

    /**
     * Create an instance of the specified driver.
     */
    protected function createTwitterDriver()
    {
        $config = config('Social')->providers['twitter'] ?? [];

        return $this->buildProvider(TwitterProvider::class, $config);
    }

    /**
     * Create an instance of the specified driver.
     */
    protected function createXDriver()
    {
        $config = config('Social')->providers['x'] ?? [];

        return $this->buildProvider(XProvider::class, $config);
    }

    /**
     * Create an instance of the specified driver.
     */
    protected function createTwitchDriver()
    {
        $config = config('Social')->providers['twitch'] ?? [];

        return $this->buildProvider(TwitchProvider::class, $config);
    }

    /**
     * Create an instance of the specified driver.
     */
    protected function createSlackDriver()
    {
        $config = config('Social')->providers['slack'] ?? [];

        return $this->buildProvider(SlackProvider::class, $config);
    }

    /**
     * Create an instance of the specified driver.
     */
    protected function createSlackOpenidDriver()
    {
        $config = config('Social')->providers['slack-openid'] ?? [];

        return $this->buildProvider(SlackOpenIdProvider::class, $config);
    }

    /**
     * Build an OAuth 2 provider instance.
     * 
     * @template TProvider of \CesarJr\Social\Providers\AbstractProvider
     * @param  class-string<TProvider>  $provider
     * @param  array  $config
     * @return TProvider
     */
    private function buildProvider($provider, $config)
    {
        $requiredKeys = ['client_id', 'client_secret', 'redirect'];

        $missingKeys = array_diff($requiredKeys, array_keys($config ?? []));

        if (! empty($missingKeys)) {
            throw DriverMissingConfigurationException::make($provider, $missingKeys);
        }

        $request = service('request');

        return (new $provider(
            $request,
            $config['client_id'],
            $config['client_secret'],
            $this->formatRedirectUrl($config),
            $config['enable_pkce'] ?? null,
            $config['enable_state'] ?? null,
            $config['guzzle'] ?? [],
        ))->scopes($config['scopes'] ?? []);
    }

    /**
     * Format the callback URL, resolving a relative URI if needed.
     *
     * @param  array  $config
     * @return string
     */
    private function formatRedirectUrl(array $config)
    {
        $redirect = $config['redirect'];

        return Str::startsWith($redirect ?? '', '/') ? site_url($redirect) : $redirect;
    }
}
