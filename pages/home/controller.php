<?php
/**
 * Home page controller
 */

use MicroFrm\App;

// Get user and session
$user = App::user();
$session = App::session();

// Get CSRF token
$csrfToken = $session->getCsrfToken();

// Get user data
$name = $user->get('name', 'User');
$email = $user->get('email', '');
$isAnonymous = $user->get('is_anonymous', false);
$loginMethod = $session->get('login_method', 'unknown');

// Include view
require_once __DIR__ . '/view.php';
