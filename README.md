# Micro frm

A PHP micro framework with minimal functionality for web applications.

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


LICENSE
----------------------------------------------------------

Copyright (C) Walter A. Jablonowski 2025, free under [MIT license](LICENSE)

This app is build upon PHP and free software (see [credits](credits.md))

[Privacy](https://walter-a-jablonowski.github.io/privacy.html) | [Legal](https://walter-a-jablonowski.github.io/imprint.html)
