<?php

namespace MicroFrm\Auth;

use MicroFrm\App;
use MicroFrm\Log;


class ProviderHandler
{
  private $app;
  private $config;
  private $user;
  private $session;
  
  /**
   * Constructor
   */
  public function __construct()
  {
    $this->app     = App::getInstance();
    $this->config  = $this->app->getConfig();
    $this->user    = $this->app->getUser();
    $this->session = $this->app->getSession();
  }
  
  /**
   * Handle provider authentication
   * 
   * @param  string $provider Provider name (auth0, google, etc.)
   * @param  string $action   Optional action (callback, etc.)
   * @return void
   */
  public function handle( $provider, $action = null ) : void
  {
    switch( $provider ) {
      case 'google':
        $this->handleGoogle($action);
        break;
        
      case 'auth0':
        $this->handleAuth0($action);
        break;
        
      default:
        header('Location: index.php?page=login&error=invalid_provider');
        exit;
    }
  }
  
  /**
   * Handle Google authentication
   * 
   * @param  string $action Optional action
   * @return void
   */
  private function handleGoogle( $action = null ) : void
  {
    // TASK: this is a dummy

    if( ! $this->config->get('login.google.enabled', false)  {
      header('Location: index.php?page=login&error=google_not_enabled');
      exit;
    }
    
    // Include Google provider handler if it exists
    $providerFile = __DIR__ . '/../../pages/login/providers/google.php';
    if( file_exists($providerFile))
      require_once $providerFile;
    else {
      header('Location: index.php?page=login&error=google_not_implemented');
      exit;
    }
  }
  
  /**
   * Handle Auth0 authentication
   * 
   * @param  string $action Optional action
   * @return void
   */
  private function handleAuth0( $action = null ) : void
  {
    if( ! $this->config->get('login.auth0.enabled', false) ) {
      header('Location: index.php?page=login&error=auth0_not_enabled');
      exit;
    }
    
    if( ! class_exists('\Auth0\SDK\Auth0') ) {
      Log::error('Auth0 SDK not found. Make sure to run composer install.');
      header('Location: index.php?page=login&error=auth0_sdk_missing');
      exit;
    }
    
    $auth0Domain       = $this->config->get('login.auth0.domain');
    $auth0ClientId     = $this->config->get('login.auth0.client_id');
    $auth0ClientSecret = $this->config->get('login.auth0.client_secret');
    
    // Generate a secure cookie secret
    $cookieSecret = bin2hex(random_bytes(32));
    
    // Validate configuration
    if( empty($auth0Domain) || empty($auth0ClientId) || empty($auth0ClientSecret) ) {
      Log::error('Auth0 configuration is incomplete');
      header('Location: index.php?page=login&error=auth0_config_error');
      exit;
    }
    
    $protocol = isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 'https://' : 'http://';
    $host     = $_SERVER['HTTP_HOST'];
    $baseUrl  = $protocol . $host;
    
    // Set up Auth0 SDK

    try {

      $auth0 = new \Auth0\SDK\Auth0([  // create Auth0 instance
        'domain'       => $auth0Domain,
        'clientId'     => $auth0ClientId,
        'clientSecret' => $auth0ClientSecret,
        'cookieSecret' => $cookieSecret,
        'redirectUri'  => $baseUrl . '/index.php?page=login&provider=auth0&action=callback'
      ]);
      
      // Handle Auth0 callback

      if( $action === 'callback' )
      {
        try {
      
          // Check if exchange parameters are present
          if( ! $auth0->getExchangeParameters() ) {
            Log::error('Auth0 callback missing required parameters');
            header('Location: index.php?page=login&error=auth0_invalid_callback');
            exit;
          }
          
          $auth0->exchange();  // exchange authorization code for tokens
          $credentials = $auth0->getCredentials();
          
          if( $credentials !== null )
          {
            $auth0User = $credentials->user;  // extract user information from credentials
            Log::info('Auth0 authentication successful', ['sub' => $auth0User['sub']]);
            $loginSuccess = $this->user->loginWithAuth0($auth0User);  // login with Auth0 user info
            
            if( $loginSuccess ) {             // redir to home page
              header('Location: index.php?page=home');
              exit;
            }
            else {
              Log::error('Failed to login with Auth0 user info', ['sub' => $auth0User['sub']]);
              $auth0->clear();                // redir to login page with error
              header('Location: index.php?page=login&error=auth0_login_failed');
              exit;
            }
          }
          else {
            // redir to login page with error
            Log::error('Auth0 callback did not return valid credentials');
            header('Location: index.php?page=login&error=auth0_no_credentials');
            exit;
          }
        }
        catch( \Exception $e ) {
          // redir to login page with error
          Log::error('Auth0 callback error: ' . $e->getMessage());
          header('Location: index.php?page=login&error=auth0_callback_error');
          exit;
        }
      }
      else {
        $auth0->clear();                   // clear previous Auth0 session if any
        $loginUrl = $auth0->login();       // generate Auth0 login URL and redirect
        header('Location: ' . $loginUrl);  // redir to Auth0 login
        exit;
      }
    }
    catch( \Exception $e ) {
      Log::error('Auth0 initialization error: ' . $e->getMessage());
      // Redirect to login page with error
      header('Location: index.php?page=login&error=auth0_init_error');
      exit;
    }
  }
}
