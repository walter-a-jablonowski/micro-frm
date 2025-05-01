<?php
/**
 * Auth0 login provider handler
 */

use MicroFrm\App;
use MicroFrm\Log;

// Make sure the Auth0 SDK is available
if( ! class_exists('\Auth0\SDK\Auth0') ) {
  Log::error('Auth0 SDK not found. Make sure to run composer install.');
  header('Location: index.php?page=login&error=auth0_sdk_missing');
  exit;
}

// Get app instance
$app = App::getInstance();
$config = $app->getConfig();
$user = $app->getUser();
$session = $app->getSession();

// Check if Auth0 is enabled
if( ! $config->get('login.auth0.enabled', false) )
{
  // Redirect to login page with error
  header('Location: index.php?page=login&error=auth0_not_enabled');
  exit;
}

// Get Auth0 configuration
$auth0Domain = $config->get('login.auth0.domain');
$auth0ClientId = $config->get('login.auth0.client_id');
$auth0ClientSecret = $config->get('login.auth0.client_secret');

// Generate a secure cookie secret
$cookieSecret = bin2hex(random_bytes(32));

// Validate configuration
if( empty($auth0Domain) || empty($auth0ClientId) || empty($auth0ClientSecret) )
{
  Log::error('Auth0 configuration is incomplete');
  header('Location: index.php?page=login&error=auth0_config_error');
  exit;
}

// Get the application base URL
$protocol = isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 'https://' : 'http://';
$host = $_SERVER['HTTP_HOST'];
$baseUrl = $protocol . $host;

// Set up Auth0 SDK
try
{
  // Create Auth0 instance
  $auth0 = new \Auth0\SDK\Auth0([
    'domain' => $auth0Domain,
    'clientId' => $auth0ClientId,
    'clientSecret' => $auth0ClientSecret,
    'cookieSecret' => $cookieSecret,
    'redirectUri' => $baseUrl . '/index.php?page=login&provider=auth0&action=callback'
  ]);

  // Check for callback action
  $action = $_GET['action'] ?? null;

  if( $action === 'callback' )
  {
    // Handle Auth0 callback
    try
    {
      // Check if exchange parameters are present
      if( ! $auth0->getExchangeParameters() ) {
        Log::error('Auth0 callback missing required parameters');
        header('Location: index.php?page=login&error=auth0_invalid_callback');
        exit;
      }
      
      // Exchange authorization code for tokens
      $auth0->exchange();
      
      // Get user credentials
      $credentials = $auth0->getCredentials();
      
      if( $credentials !== null )
      {
        // Extract user information from credentials
        $auth0User = $credentials->user;
        
        // Log successful Auth0 authentication
        Log::info('Auth0 authentication successful', ['sub' => $auth0User['sub']]);
        
        // Login with Auth0 user info
        $loginSuccess = $user->loginWithAuth0($auth0User);
        
        if( $loginSuccess )
        {
          // Redirect to home page
          header('Location: index.php?page=home');
          exit;
        }
        else
        {
          // Log error
          Log::error('Failed to login with Auth0 user info', ['sub' => $auth0User['sub']]);
          
          // Clear Auth0 session
          $auth0->clear();
          
          // Redirect to login page with error
          header('Location: index.php?page=login&error=auth0_login_failed');
          exit;
        }
      }
      else
      {
        // Log error
        Log::error('Auth0 callback did not return valid credentials');
        
        // Redirect to login page with error
        header('Location: index.php?page=login&error=auth0_no_credentials');
        exit;
      }
    }
    catch( \Exception $e )
    {
      // Log error
      Log::error('Auth0 callback error: ' . $e->getMessage());
      
      // Redirect to login page with error
      header('Location: index.php?page=login&error=auth0_callback_error');
      exit;
    }
  }
  else
  {
    // Clear previous Auth0 session if any
    $auth0->clear();
    
    // Generate Auth0 login URL and redirect
    $loginUrl = $auth0->login();
    
    // Redirect to Auth0 login
    header('Location: ' . $loginUrl);
    exit;
  }
}
catch( \Exception $e )
{
  // Log error
  Log::error('Auth0 initialization error: ' . $e->getMessage());
  
  // Redirect to login page with error
  header('Location: index.php?page=login&error=auth0_init_error');
  exit;
}
