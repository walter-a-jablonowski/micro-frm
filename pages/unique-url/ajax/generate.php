<?php
/**
 * Generate unique URL AJAX handler
 */

use MicroFrm\App;
use MicroFrm\Log;

// Get request data
$email = $requestData['email'] ?? null;

// Validate email if provided
if( $email !== null && ! filter_var($email, FILTER_VALIDATE_EMAIL) )
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

// Generate unique URL
$user = App::user();
$token = $user->generateUniqueUrlToken($email);

if( $token )
{
  // Generate full URL
  $protocol = isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 'https' : 'http';
  $host = $_SERVER['HTTP_HOST'];
  $url = "$protocol://$host/index.php?page=unique-url&token=$token";
  
  // Log unique URL generation
  Log::info("Unique URL generated", ['email' => $email ?: 'anonymous']);
  
  // Return success response
  sendJsonResponse([
    'success' => true,
    'url' => $url,
    'message' => 'Unique URL generated successfully'
  ]);
}
else
{
  // Log failure
  Log::warning("Failed to generate unique URL", ['email' => $email]);
  
  // Return error response
  sendJsonResponse([
    'success' => false,
    'error' => [
      'message' => 'Failed to generate unique URL',
      'type' => 'Generation Error'
    ]
  ]);
}
