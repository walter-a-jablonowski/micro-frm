<?php
/**
 * App.php
 * Main application class that provides access to core components
 */

namespace MicroFrm;

class App
{
  private static $instance = null;
  private $config = null;
  private $session = null;
  private $user = null;
  
  private function __construct()
  {
    // Initialize components in the correct order to avoid circular dependencies
    $this->config = new Config();
    $this->session = new Session($this->config);
    $this->user = new User($this->config, $this->session);
  }
  
  /**
   * Get the singleton instance of the App
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
   * Get the Config instance
   */
  public function getConfig() : Config
  {
    return $this->config;
  }
  
  /**
   * Get the Session instance
   */
  public function getSession() : Session
  {
    return $this->session;
  }
  
  /**
   * Get the User instance
   */
  public function getUser() : User
  {
    return $this->user;
  }
  
  /**
   * Static helper to get Config
   */
  public static function config() : Config
  {
    return self::getInstance()->getConfig();
  }
  
  /**
   * Static helper to get Session
   */
  public static function session() : Session
  {
    return self::getInstance()->getSession();
  }
  
  /**
   * Static helper to get User
   */
  public static function user() : User
  {
    return self::getInstance()->getUser();
  }
}
