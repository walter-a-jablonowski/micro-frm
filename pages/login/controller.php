<?php
/**
 * Login page controller
 */

use MicroFrm\App;

// Get session and config
$session = App::session();
$config = App::config();

// Get CSRF token
$csrfToken = $session->getCsrfToken();

// Get enabled login methods
$loginMethods     = $config->get('login.methods', ['email']);
$googleEnabled    = $config->get('login.google.enabled', false);
$auth0Enabled     = $config->get('login.auth0.enabled', false);
$uniqueUrlEnabled = $config->get('login.unique_url.enabled', false);

// Include view
require_once __DIR__ . '/view.php';
