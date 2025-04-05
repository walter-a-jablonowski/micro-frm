# Micro frm

A PHP micro framework with minimal functionality for web applications.

Intention: Quickly upgrade an app started for a single user to a multiuser app with session management, multiple login options, error handling and logging with minimal changes.

## Features

- Configuration via YAML files
- Session management with file-based storage
- User management with YAML-based storage
- Multiple login methods:
  - Email/password login
  - Google login
  - Auth0 login
  - Unique URL login
- Error handling for PHP and JavaScript
- CSV-based logging
- CSRF protection
- Secure cookies
- Password hashing

## Installation

1. Clone the repository
2. Run `composer install` to install dependencies
3. Configure your web server to point to the project directory
4. Open the application in your browser

## Configuration

The framework uses YAML files for configuration. The main configuration file is `config.yml` in the root directory.

### Login with Google

To enable Google login:

1. Create a project in the [Google Developer Console](https://console.developers.google.com/)
2. Enable the Google+ API
3. Create OAuth 2.0 credentials
4. Configure the redirect URI to `http://your-domain.com/index.php?page=login&provider=google`
5. Update your `config.yml`:

```yaml
login:
  methods:
    - email
    - google
  google:
    enabled: true
    client_id: 'YOUR_CLIENT_ID'
    client_secret: 'YOUR_CLIENT_SECRET'
```

### Login with Auth0

To enable Auth0 login:

1. Create an account at [Auth0](https://auth0.com/)
2. Create a new application
3. Configure the callback URL to `http://your-domain.com/index.php?page=login&provider=auth0`
4. Update your `config.yml`:

```yaml
login:
  methods:
    - email
    - auth0
  auth0:
    enabled: true
    domain: 'YOUR_AUTH0_DOMAIN'
    client_id: 'YOUR_CLIENT_ID'
    client_secret: 'YOUR_CLIENT_SECRET'
```

### Login via Unique URL

To enable Unique URL login:

1. Update your `config.yml` to include the unique URL login method:

```yaml
login:
  methods:
    - email
    - unique_url
  unique_url:
    enabled: true
    # Optional: Set expiration time in seconds (default: 30 days)
    expiration: 2592000
    # Optional: Set URL base (default: current domain)
    url_base: 'http://your-domain.com'
```

2. Users can generate a unique login URL from the login page by clicking "Login with Unique URL"
3. The system will generate a secure token and provide a URL that can be used for passwordless login
4. Optionally, users can associate their email with the unique URL for easier identification
5. The unique URL will remain valid until its expiration date or until manually revoked

This login method is particularly useful for:
- Sharing access with users who don't want to create an account
- Temporary access to specific features
- Systems where security requirements are moderate and convenience is important

LICENSE
----------------------------------------------------------

Copyright (C) Walter A. Jablonowski 2025, free under [MIT license](LICENSE)

This app is build upon PHP and free software (see [credits](credits.md))

[Privacy](https://walter-a-jablonowski.github.io/privacy.html) | [Legal](https://walter-a-jablonowski.github.io/imprint.html)
