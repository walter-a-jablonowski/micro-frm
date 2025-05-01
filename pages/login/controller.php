<?php
/**
 * Login page controller
 */

use MicroFrm\App;
use MicroFrm\Auth\ProviderHandler;

// Get session and config
$session = App::session();
$config  = App::config();

// Check for provider parameter
$provider = $_GET['provider'] ?? null;
$action   = $_GET['action']   ?? null;

// Handle provider-specific login
if( $provider ) {
  // Use the ProviderHandler to handle authentication providers
  $providerHandler = new ProviderHandler();
  $providerHandler->handle($provider, $action);
  // The handler will redirect, so execution won't continue past this point
}

// Get CSRF token
$csrfToken = $session->getCsrfToken();

// Get enabled login methods
$loginMethods     = $config->get('login.methods', ['email']);
$googleEnabled    = $config->get('login.google.enabled', false);
$auth0Enabled     = $config->get('login.auth0.enabled', false);
$uniqueUrlEnabled = $config->get('login.unique_url.enabled', false);

// Include view
require_once __DIR__ . '/view.php';
