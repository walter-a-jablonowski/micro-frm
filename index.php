<?php
/**
 * index.php
 * Main entry point for the application
 */

// Require composer autoloader
require_once __DIR__ . '/vendor/autoload.php';

use MicroFrm\App;
use MicroFrm\ErrorHandler;
use MicroFrm\Log;

// Initialize error handler
ErrorHandler::getInstance()->register();

// Initialize app
$app = App::getInstance();

// Get page from URL
$page = $_GET['page'] ?? 'home';

// Check if user is authenticated
$user = $app->getUser();
$isLoggedIn = $user->isAuthenticated();

// Handle login
if( ! $isLoggedIn && $page !== 'login' && $page !== 'register' && $page !== 'unique-url' )
{
  // Redirect to login page
  header('Location: index.php?page=login');
  exit;
}

// Page routing
switch( $page )
{
  case 'login':
    require_once __DIR__ . '/pages/login/controller.php';
    break;
    
  case 'register':
    require_once __DIR__ . '/pages/register/controller.php';
    break;
    
  case 'logout':
    // Logout user
    $user->logout();
    
    // Redirect to login page
    header('Location: index.php?page=login');
    exit;
    
  case 'unique-url':
    // Handle unique URL login
    $token = $_GET['token'] ?? null;
    
    if( $token && $user->loginWithUniqueUrl($token) )
    {
      // Redirect to home page
      header('Location: index.php?page=home');
      exit;
    }
    else
    {
      // Invalid token, show error
      require_once __DIR__ . '/pages/unique-url/controller.php';
    }
    break;
    
  case 'home':
  default:
    // Show home page
    require_once __DIR__ . '/pages/home/controller.php';
    break;
}
