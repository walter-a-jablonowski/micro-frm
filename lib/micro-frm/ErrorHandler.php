<?php
/**
 * ErrorHandler.php
 * Handles PHP errors and exceptions
 */

namespace MicroFrm;

class ErrorHandler
{
  private static $instance = null;
  private $isRegistered = false;
  
  private function __construct()
  {
    // Private constructor for singleton
  }
  
  /**
   * Get the singleton instance
   */
  public static function getInstance() : self
  {
    if( self::$instance === null )
    {
      self::$instance = new self();
    }
    
    return self::$instance;
  }
  
  /**
   * Register error handlers
   */
  public function register() : void
  {
    if( $this->isRegistered )
    {
      return;
    }
    
    // Set error handler
    set_error_handler([$this, 'handleError']);
    
    // Set exception handler
    set_exception_handler([$this, 'handleException']);
    
    // Register shutdown function
    register_shutdown_function([$this, 'handleShutdown']);
    
    $this->isRegistered = true;
  }
  
  /**
   * Handle PHP errors
   */
  public function handleError( $errno, $errstr, $errfile, $errline ) : bool
  {
    // Log error
    $this->logError($errno, $errstr, $errfile, $errline);
    
    // Display error
    $this->displayError($errno, $errstr, $errfile, $errline);
    
    // Don't execute PHP's internal error handler
    return true;
  }
  
  /**
   * Handle exceptions
   */
  public function handleException( $exception ) : void
  {
    $errno = $exception->getCode();
    $errstr = $exception->getMessage();
    $errfile = $exception->getFile();
    $errline = $exception->getLine();
    $trace = $exception->getTraceAsString();
    
    // Log exception
    $this->logError($errno, $errstr, $errfile, $errline, $trace);
    
    // Display exception
    $this->displayError($errno, $errstr, $errfile, $errline, $trace);
  }
  
  /**
   * Handle fatal errors on shutdown
   */
  public function handleShutdown() : void
  {
    $error = error_get_last();
    
    if( $error !== null && in_array($error['type'], [E_ERROR, E_PARSE, E_CORE_ERROR, E_COMPILE_ERROR]) )
    {
      $errno = $error['type'];
      $errstr = $error['message'];
      $errfile = $error['file'];
      $errline = $error['line'];
      
      // Log fatal error
      $this->logError($errno, $errstr, $errfile, $errline);
      
      // Display fatal error
      $this->displayError($errno, $errstr, $errfile, $errline);
    }
  }
  
  /**
   * Log error to file
   */
  private function logError( $errno, $errstr, $errfile, $errline, $trace = null ) : void
  {
    $errorType = $this->getErrorType($errno);
    
    $context = [
      'type' => $errorType,
      'file' => $errfile,
      'line' => $errline
    ];
    
    if( $trace !== null )
    {
      $context['trace'] = $trace;
    }
    
    Log::error("$errorType: $errstr", $context);
  }
  
  /**
   * Display error to user
   */
  private function displayError( $errno, $errstr, $errfile, $errline, $trace = null ) : void
  {
    $config = App::config();
    $debug = $config->get('app.debug', false);
    $isAjax = $this->isAjaxRequest();
    
    $errorType = $this->getErrorType($errno);
    
    if( $isAjax )
    {
      // Send JSON response for AJAX requests
      $response = [
        'success' => false,
        'error' => [
          'message' => $errstr,
          'type' => $errorType
        ]
      ];
      
      if( $debug )
      {
        $response['error']['file'] = $errfile;
        $response['error']['line'] = $errline;
        
        if( $trace !== null )
        {
          $response['error']['trace'] = explode("\n", $trace);
        }
      }
      
      header('Content-Type: application/json');
      echo json_encode($response);
    }
    else
    {
      // Display HTML error page
      $title = "Error: $errorType";
      $message = htmlspecialchars($errstr);
      
      $details = '';
      if( $debug )
      {
        $file = htmlspecialchars($errfile);
        $details .= "<p>File: $file</p>";
        $details .= "<p>Line: $errline</p>";
        
        if( $trace !== null )
        {
          $traceHtml = nl2br(htmlspecialchars($trace));
          $details .= "<h3>Stack Trace</h3><pre>$traceHtml</pre>";
        }
      }
      
      $this->renderErrorPage($title, $message, $details);
    }
    
    // Stop execution for fatal errors
    if( in_array($errno, [E_ERROR, E_PARSE, E_CORE_ERROR, E_COMPILE_ERROR, E_USER_ERROR]) )
    {
      exit(1);
    }
  }
  
  /**
   * Render HTML error page
   */
  private function renderErrorPage( $title, $message, $details = '' ) : void
  {
    $html = <<<HTML
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>$title</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
  <style>
    body {
      padding: 2rem;
      background-color: #f8f9fa;
    }
    .error-container {
      max-width: 800px;
      margin: 0 auto;
      background-color: #fff;
      border-radius: 5px;
      box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
      padding: 2rem;
    }
    .error-title {
      color: #dc3545;
      margin-bottom: 1rem;
    }
    .error-message {
      font-size: 1.2rem;
      margin-bottom: 1.5rem;
    }
    .error-details {
      background-color: #f8f9fa;
      padding: 1rem;
      border-radius: 5px;
      font-family: monospace;
    }
  </style>
</head>
<body>
  <div class="error-container">
    <h1 class="error-title">$title</h1>
    <div class="error-message">$message</div>
    <div class="error-details">$details</div>
  </div>
</body>
</html>
HTML;

    echo $html;
  }
  
  /**
   * Check if request is AJAX
   */
  private function isAjaxRequest() : bool
  {
    return (
      !empty($_SERVER['HTTP_X_REQUESTED_WITH']) && 
      strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest'
    ) || (
      !empty($_SERVER['HTTP_ACCEPT']) && 
      strpos($_SERVER['HTTP_ACCEPT'], 'application/json') !== false
    );
  }
  
  /**
   * Get error type string from error number
   */
  private function getErrorType( $errno ) : string
  {
    switch( $errno )
    {
      case E_ERROR:
        return 'Fatal Error';
      case E_WARNING:
        return 'Warning';
      case E_PARSE:
        return 'Parse Error';
      case E_NOTICE:
        return 'Notice';
      case E_CORE_ERROR:
        return 'Core Error';
      case E_CORE_WARNING:
        return 'Core Warning';
      case E_COMPILE_ERROR:
        return 'Compile Error';
      case E_COMPILE_WARNING:
        return 'Compile Warning';
      case E_USER_ERROR:
        return 'User Error';
      case E_USER_WARNING:
        return 'User Warning';
      case E_USER_NOTICE:
        return 'User Notice';
      case E_STRICT:
        return 'Strict Standards';
      case E_RECOVERABLE_ERROR:
        return 'Recoverable Error';
      case E_DEPRECATED:
        return 'Deprecated';
      case E_USER_DEPRECATED:
        return 'User Deprecated';
      default:
        return 'Unknown Error';
    }
  }
}
