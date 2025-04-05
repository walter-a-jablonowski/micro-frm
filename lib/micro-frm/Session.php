<?php
/**
 * Session.php
 * Manages user sessions with file-based storage
 */

namespace MicroFrm;

use Symfony\Component\Yaml\Yaml;
use MicroFrm\Config;

class Session
{
  private $sessionId = null;
  private $sessionData = [];
  private $sessionDir = null;
  private $sessionFile = null;
  private $isStarted = false;
  private $config = null;
  
  public function __construct( Config $config )
  {
    $this->config = $config;
    $this->sessionDir = __DIR__ . '/../../data/sessions/';
    
    // Ensure session directory exists
    if( ! is_dir($this->sessionDir) )
    {
      mkdir($this->sessionDir, 0755, true);
    }
    
    // Start session if not already started
    if( session_status() === PHP_SESSION_NONE )
    {
      $this->start();
    }
    else
    {
      $this->sessionId = session_id();
      $this->loadSessionData();
    }
  }
  
  /**
   * Start the session
   */
  public function start() : bool
  {
    // Configure session
    $secure = $this->config->get('session.secure', true);
    $httpOnly = $this->config->get('session.httponly', true);
    
    // Set session cookie parameters
    session_set_cookie_params([
      'lifetime' => $this->config->get('session.timeout', 3600),
      'path' => '/',
      'secure' => $secure,
      'httponly' => $httpOnly,
      'samesite' => 'Lax'
    ]);
    
    // Start session
    if( session_start() )
    {
      $this->isStarted = true;
      $this->sessionId = session_id();
      $this->createSessionDir();
      $this->loadSessionData();
      
      // Regenerate session ID periodically for security
      if( ! isset($_SESSION['last_regeneration']) || 
          time() - $_SESSION['last_regeneration'] > 300 ) // 5 minutes
      {
        $this->regenerateId();
      }
      
      return true;
    }
    
    return false;
  }
  
  /**
   * Create session directory for this session
   */
  private function createSessionDir() : void
  {
    $sessionDir = $this->sessionDir . $this->sessionId . '/';
    if( ! is_dir($sessionDir) )
    {
      mkdir($sessionDir, 0755, true);
    }
    
    $this->sessionFile = $sessionDir . '-this.json';
  }
  
  /**
   * Load session data from file
   */
  private function loadSessionData() : void
  {
    $this->createSessionDir();
    
    if( file_exists($this->sessionFile) )
    {
      $data = file_get_contents($this->sessionFile);
      $this->sessionData = json_decode($data, true) ?: [];
    }
  }
  
  /**
   * Save session data to file
   */
  public function save() : bool
  {
    if( ! $this->isStarted )
    {
      return false;
    }
    
    try
    {
      file_put_contents($this->sessionFile, json_encode($this->sessionData));
      return true;
    }
    catch( \Exception $e )
    {
      Log::error("Failed to save session: " . $e->getMessage());
      return false;
    }
  }
  
  /**
   * Regenerate session ID
   */
  public function regenerateId() : bool
  {
    if( ! $this->isStarted )
    {
      return false;
    }
    
    // Save current session data
    $oldData = $this->sessionData;
    $oldSessionId = $this->sessionId;
    
    // Regenerate session ID
    if( session_regenerate_id(true) )
    {
      $this->sessionId = session_id();
      $this->createSessionDir();
      $this->sessionData = $oldData;
      $_SESSION['last_regeneration'] = time();
      
      // Save session data with new ID
      $this->save();
      
      // Clean up old session directory
      $this->cleanupSession($oldSessionId);
      
      return true;
    }
    
    return false;
  }
  
  /**
   * Get a session value
   * 
   * @param string $key Session key
   * @param mixed $default Default value if key not found
   * @return mixed Session value
   */
  public function get( $key, $default = null )
  {
    return $this->sessionData[$key] ?? $default;
  }
  
  /**
   * Set a session value
   * 
   * @param string $key Session key
   * @param mixed $value Value to set
   */
  public function set( $key, $value ) : void
  {
    $this->sessionData[$key] = $value;
    $this->save();
  }
  
  /**
   * Check if a session key exists
   */
  public function has( $key ) : bool
  {
    return isset($this->sessionData[$key]);
  }
  
  /**
   * Remove a session key
   */
  public function remove( $key ) : void
  {
    if( isset($this->sessionData[$key]) )
    {
      unset($this->sessionData[$key]);
      $this->save();
    }
  }
  
  /**
   * Clear all session data
   */
  public function clear() : void
  {
    $this->sessionData = [];
    $this->save();
  }
  
  /**
   * Destroy the session
   */
  public function destroy() : bool
  {
    if( ! $this->isStarted )
    {
      return false;
    }
    
    // Clean up session files
    $this->cleanupSession($this->sessionId);
    
    // Destroy session
    $this->sessionData = [];
    session_unset();
    
    return session_destroy();
  }
  
  /**
   * Clean up session files
   */
  private function cleanupSession( $sessionId ) : void
  {
    $sessionDir = $this->sessionDir . $sessionId . '/';
    
    if( is_dir($sessionDir) )
    {
      $files = scandir($sessionDir);
      foreach( $files as $file )
      {
        if( $file !== '.' && $file !== '..' )
        {
          unlink($sessionDir . $file);
        }
      }
      
      rmdir($sessionDir);
    }
  }
  
  /**
   * Clean up expired sessions
   */
  public function cleanupExpiredSessions() : void
  {
    $timeout = $this->config->get('session.timeout', 3600);
    $now = time();
    
    $sessions = scandir($this->sessionDir);
    foreach( $sessions as $sessionId )
    {
      if( $sessionId === '.' || $sessionId === '..' )
      {
        continue;
      }
      
      $sessionDir = $this->sessionDir . $sessionId;
      if( is_dir($sessionDir) )
      {
        $sessionFile = $sessionDir . '/-this.json';
        if( file_exists($sessionFile) )
        {
          // Check if session is expired
          $modTime = filemtime($sessionFile);
          if( $now - $modTime > $timeout )
          {
            $this->cleanupSession($sessionId);
          }
        }
        else
        {
          // No session file, clean up directory
          $this->cleanupSession($sessionId);
        }
      }
    }
  }
  
  /**
   * Get CSRF token
   */
  public function getCsrfToken() : string
  {
    if( ! $this->has('csrf_token') || ! $this->has('csrf_token_time') )
    {
      $this->regenerateCsrfToken();
    }
    
    $expiry = $this->config->get('security.csrf_token_expiry', 3600);
    
    // Check if token is expired
    if( time() - $this->get('csrf_token_time') > $expiry )
    {
      $this->regenerateCsrfToken();
    }
    
    return $this->get('csrf_token');
  }
  
  /**
   * Regenerate CSRF token
   */
  public function regenerateCsrfToken() : string
  {
    $token = bin2hex(random_bytes(32));
    $this->set('csrf_token', $token);
    $this->set('csrf_token_time', time());
    
    return $token;
  }
  
  /**
   * Validate CSRF token
   */
  public function validateCsrfToken( $token ) : bool
  {
    return $this->has('csrf_token') && $this->get('csrf_token') === $token;
  }
}
