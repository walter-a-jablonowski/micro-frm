<?php
/**
 * ajax.php
 * Handles AJAX requests
 */

// Require composer autoloader
require_once 'vendor/autoload.php';

use MicroFrm\App;
use MicroFrm\ErrorHandler;
use MicroFrm\Log;

// Initialize error handler
ErrorHandler::getInstance()->register();

// Initialize app
$app = App::getInstance();

// Get request data
$requestData = json_decode(file_get_contents('php://input'), true);

// Check if request is valid
if( ! $requestData || ! isset($requestData['identifier']) )
{
  sendJsonResponse([
    'success' => false,
    'error' => [
      'message' => 'Invalid request',
      'type' => 'Request Error'
    ]
  ]);
  exit;
}

// Get request identifier
$identifier = $requestData['identifier'];

// Check CSRF token for non-GET requests
if( isset($requestData['method']) && $requestData['method'] !== 'GET' )
{
  $session = $app->getSession();
  $csrfToken = $requestData['csrf_token'] ?? null;
  
  if( ! $csrfToken || ! $session->validateCsrfToken($csrfToken) )
  {
    sendJsonResponse([
      'success' => false,
      'error' => [
        'message' => 'Invalid CSRF token',
        'type' => 'Security Error'
      ]
    ]);
    exit;
  }
}

// Handle error reporting from JavaScript
if( $identifier === 'error.report' )
{
  if( isset($requestData['error']) )
  {
    $error = $requestData['error'];
    Log::error("JavaScript Error: {$error['message']}", $error);
  }
  elseif( isset($requestData['warning']) )
  {
    $warning = $requestData['warning'];
    Log::warning("JavaScript Warning: {$warning['message']}", $warning);
  }
  
  sendJsonResponse(['success' => true]);
  exit;
}

// Check if user is authenticated for non-login requests
$user = $app->getUser();
$isLoggedIn = $user->isAuthenticated();

if( ! $isLoggedIn && ! in_array($identifier, ['login', 'register', 'unique-url']) )
{
  sendJsonResponse([
    'success' => false,
    'error' => [
      'message' => 'Authentication required',
      'type' => 'Auth Error'
    ]
  ]);
  exit;
}

// Route request to handler

switch( $identifier )
{
  case 'login':
    require_once 'pages/login/ajax/login.php';
    break;
    
  case 'register':
    require_once 'pages/register/ajax/register.php';
    break;
    
  case 'unique-url':
    require_once 'pages/unique-url/ajax/generate.php';
    break;
    
  default:
    // Check if handler file exists
    $handlerFile = "pages/{$identifier}/ajax/" . basename($identifier) . ".php";
    
    if( file_exists($handlerFile) )
    {
      require_once $handlerFile;
    }
    else
    {
      sendJsonResponse([
        'success' => false,
        'error' => [
          'message' => "Unknown request identifier: {$identifier}",
          'type' => 'Request Error'
        ]
      ]);
    }
    break;
}

/**
 * Send JSON response
 * 
 * @param array $data Response data
 */
function sendJsonResponse( $data )
{
  header('Content-Type: application/json');
  echo json_encode($data);
}
