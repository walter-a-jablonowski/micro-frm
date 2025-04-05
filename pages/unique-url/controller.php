<?php
/**
 * Unique URL login page controller
 */

use MicroFrm\App;

// Get session
$session = App::session();

// Get CSRF token
$csrfToken = $session->getCsrfToken();

// Get token from URL
$token = $_GET['token'] ?? null;

// Include view
require_once __DIR__ . '/view.php';
