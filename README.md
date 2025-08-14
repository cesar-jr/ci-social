# CI Social

[![Packagist Version](https://img.shields.io/packagist/v/cesar-jr/ci-social)](https://packagist.org/packages/cesar-jr/ci-social)
[![License](https://img.shields.io/github/license/cesar-jr/ci-social)](LICENSE)
[![PHP Version](https://img.shields.io/packagist/php-v/cesar-jr/ci-social)](https://packagist.org/packages/cesar-jr/ci-social)

**CI Social** is a [CodeIgniter 4](https://codeigniter.com/) package ~~shamelessly copied from~~ inspired by [Laravel Socialite](https://laravel.com/docs/master/socialite), enabling seamless OAuth authentication with multiple providers.

Currently supports:
**Bitbucket, Facebook, GitHub, GitLab, Google, LinkedIn, Slack, Twitch, and Twitter**.

---

## 📦 Installation

Install via [Composer](https://getcomposer.org/):

```bash
composer require cesar-jr/ci-social
```

---

## ⚙️ Configuration

After installation, publish the configuration file:

```bash
php spark social:setup
```

This will create:

```
app/Config/Social.php
```

Add your provider credentials:

```php
public array $providers = [
    'github' => [
        'client_id'     => 'your-client-id',
        'client_secret' => 'your-client-secret',
        'redirect'      => '/callback/github',
    ],
    'google' => [
        'client_id'     => 'your-client-id',
        'client_secret' => 'your-client-secret',
        'redirect'      => '/callback/google',
    ],
    // ... other providers
];
```

---

## 🚀 Basic Usage

You can use either the **helper** or **service** to start the OAuth flow.

### 1. Redirecting to the provider

**Using helper:**

```php
return social('github')->redirect();
```

**Using service:**

```php
return service('social')->driver('github')->redirect();
```

### 2. Getting the user after callback

```php
$user = social('github')->user();

// Available user data
$user->getId();
$user->getName();
$user->getEmail();
$user->getAvatar();
```

---

## 🌐 Supported Providers

-   Bitbucket
-   Facebook
-   GitHub
-   GitLab
-   Google
-   LinkedIn
-   Slack
-   Twitch
-   Twitter

---

## 🤝 Contributing

Pull Requests are welcome!  
If you find a bug, please open an **issue** with details and reproduction steps.

---

## 📄 License

This project is licensed under the [MIT License](LICENSE).

---
