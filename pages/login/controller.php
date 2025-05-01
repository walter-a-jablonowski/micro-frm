<?php
/**
 * Login page controller
 */

use MicroFrm\App;

// Get session and config
$session = App::session();
$config  = App::config();

// Check for provider parameter
$provider = $_GET['provider'] ?? null;
$action   = $_GET['action'] ?? null;

// Handle provider-specific login
if( $provider ) {
  switch( $provider ) {
    case 'google':
      // Handle Google login
      if( $config->get('login.google.enabled', false)) {
        // Include Google provider handler if it exists
        $providerFile = __DIR__ . '/providers/google.php';
        if( file_exists($providerFile) ) {
          require_once $providerFile;
        } else {
          header('Location: index.php?page=login&error=google_not_implemented');
          exit;
        }
      }
      else {
        header('Location: index.php?page=login&error=google_not_enabled');
        exit;
      }
      break;
      
    case 'auth0':
      // Handle Auth0 login
      if( $config->get('login.auth0.enabled', false)) {
        // Include Auth0 provider handler if it exists
        $providerFile = __DIR__ . '/providers/auth0.php';
        if( file_exists($providerFile) ) {
          require_once $providerFile;
        }
        else {
          header('Location: index.php?page=login&error=auth0_not_implemented');
          exit;
        }
      } else {
        header('Location: index.php?page=login&error=auth0_not_enabled');
        exit;
      }
      break;
      
    default:
      header('Location: index.php?page=login&error=invalid_provider');
      exit;
  }
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
