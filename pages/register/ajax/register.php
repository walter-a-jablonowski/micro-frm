<?php
/**
 * Register AJAX handler
 */

use MicroFrm\App;
use MicroFrm\Log;

// Get request data
$name = $requestData['name'] ?? null;
$email = $requestData['email'] ?? null;
$password = $requestData['password'] ?? null;

// Validate input
if( ! $name || ! $email || ! $password )
{
  sendJsonResponse([
    'success' => false,
    'error' => [
      'message' => 'Name, email, and password are required',
      'type' => 'Validation Error'
    ]
  ]);
  exit;
}

// Validate email
if( ! filter_var($email, FILTER_VALIDATE_EMAIL) )
{
  sendJsonResponse([
    'success' => false,
    'error' => [
      'message' => 'Invalid email address',
      'type' => 'Validation Error'
    ]
  ]);
  exit;
}

// Validate password strength
if( strlen($password) < 8 )
{
  sendJsonResponse([
    'success' => false,
    'error' => [
      'message' => 'Password must be at least 8 characters long',
      'type' => 'Validation Error'
    ]
  ]);
  exit;
}

// Attempt registration
$user = App::user();
$success = $user->register($email, $password, [
  'name' => $name
]);

if( $success )
{
  // Log successful registration
  Log::info("User registered successfully", ['email' => $email]);
  
  // Return success response
  sendJsonResponse([
    'success' => true,
    'message' => 'Registration successful'
  ]);
}
else
{
  // Log failed registration
  Log::warning("Registration failed", ['email' => $email]);
  
  // Return error response
  sendJsonResponse([
    'success' => false,
    'error' => [
      'message' => 'Email already registered or registration failed',
      'type' => 'Registration Error'
    ]
  ]);
}
