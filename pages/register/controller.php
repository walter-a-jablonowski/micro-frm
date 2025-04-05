<?php
/**
 * Register page controller
 */

use MicroFrm\App;

// Get session
$session = App::session();

// Get CSRF token
$csrfToken = $session->getCsrfToken();

// Include view
require_once __DIR__ . '/view.php';
