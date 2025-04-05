<?php
/**
 * Login AJAX handler
 */

use MicroFrm\App;
use MicroFrm\Log;

// Get request data
$email = $requestData['email'] ?? null;
$password = $requestData['password'] ?? null;

// Validate input
if( ! $email || ! $password )
{
  sendJsonResponse([
    'success' => false,
    'error' => [
      'message' => 'Email and password are required',
      'type' => 'Validation Error'
    ]
  ]);
  exit;
}

// Attempt login
$user = App::user();
$success = $user->login($email, $password);

if( $success )
{
  // Log successful login
  Log::info("User logged in successfully", ['email' => $email]);
  
  // Return success response
  sendJsonResponse([
    'success' => true,
    'message' => 'Login successful'
  ]);
}
else
{
  // Log failed login
  Log::warning("Login failed", ['email' => $email]);
  
  // Return error response
  sendJsonResponse([
    'success' => false,
    'error' => [
      'message' => 'Invalid email or password',
      'type' => 'Auth Error'
    ]
  ]);
}
