
I am making a PHP micro framework which has the most minimal functionallity usually needed to make web apps

## Common

- important: Make nice, fluent and easy to read code
- location or framework: /lib/micro-frm
- autoloading: composer
- Use bootstrap 5.3
- Security
  - Password hashing
  - CSRF Protection
  - Secure Cookies

## PHP Classes

- Config
  - loads one or more yml files form config.yml
- Session
  - has a cache (saved /data/sessions/SESSION_ID/-this.json)
  - use a timeout from config
  - clean up session data if a session is closed
- User
  - basic data saved in data/users/MY_USER/-this.yml
  - may have more files in MY_USER like settings.yml
- ErrorHandler
  - display an error page in case of PHP errors
  - send error as json in case of ajax call
  - logs all errors
- Log class: log in /data/app.log, choose a format the easiy can be processed using tools (e.g. csv)

The following classes should be accessible from anywhere in the code: You could use a a static method that delivers a app object (singleton)

- Config
- Session
- User

We don't care about remaing classes e.g.: Routing, Controllers, Templating Engine (use simple code for this)

- Routing: use a switch in index.php or ajax.php
  - page load: use an url arg for page identification
  - ajax: include a field "identifier" in the json

Storage decisions for additional data that an app might use are application-specific (no implementation here)

## Login system

Depending on a config entry we offer one or more of these login options

- Login or Register with mail (full featured)
- login with google (using the google API)
- login with Auth0 (https://auth0.com)
- Login via unique url: Instead of login in the user get just a unique link that when enter again allow access (anonymous user that verifies with the link only)

Add config values as needed for google or Auth0

## JS Classes

- ErrorHandler
  - replace the html body with the error page in case of ajax communication errors or javascript errors
  - reports an errors to the server (if possible, single try only) where it is logged using the log class
- make some easy helper function for sendng ajax from js in /lib/micro-frm
  - send json only

## Index

Also make a dummy start page that is shown when logged in in folder /pages/MY_PAGE/controller.php

- header with logout button
- content area (Hello world)

Obviously we must have a index.php that requires it.

## Readme

Add setup instructions for login with google and Auth0 in the readme (keep it short)
