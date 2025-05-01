<?php
/**
 * Config.php
 * Loads and manages configuration from YAML files
 */

namespace MicroFrm;

use Symfony\Component\Yaml\Yaml;
use Symfony\Component\Yaml\Exception\ParseException;

class Config
{
  private $config = [];
  
  public function __construct()
  {
    $this->loadConfig();
  }
  
  /**
   * Load configuration from config.yml and any included files
   */
  private function loadConfig() : void
  {
    try
    {
      // Load main config file
      $configFile = __DIR__ . '/../../config.yml';
      
      if( file_exists($configFile) )
      {
        $this->config = Yaml::parseFile($configFile);
        
        // Check for includes
        if( isset($this->config['includes']) && is_array($this->config['includes']) )
        {
          foreach( $this->config['includes'] as $include )
          {
            $includePath = __DIR__ . '/../../config/' . $include;
            if( file_exists($includePath) )
            {
              $includeConfig = Yaml::parseFile($includePath);
              $this->config = array_merge($this->config, $includeConfig);
            }
            else
            {
              Log::warning("Config include file missing: $includePath");
            }
          }
        }
      }
      else
      {
        // Create default config if it doesn't exist
        $this->config = $this->getDefaultConfig();
        $this->saveConfig();
      }
    }
    catch( ParseException $e )
    {
      Log::error("Error parsing config file: " . $e->getMessage());
      $this->config = $this->getDefaultConfig();
    }
  }
  
  /**
   * Get a configuration value
   * 
   * @param  string $key
   * @param  mixed  $default Default value if key missing
   * @return mixed  Configuration value
   */
  public function get( $key, $default = null )
  {
    $keys = explode('.', $key);
    $value = $this->config;
    
    foreach( $keys as $k )
    {
      if( ! isset($value[$k]) )
      {
        return $default;
      }
      
      $value = $value[$k];
    }
    
    return $value;
  }
  
  /**
   * Set a configuration value
   * 
   * @param string $key
   * @param mixed  $value Value to set
   */
  public function set( $key, $value ) : void
  {
    $keys = explode('.', $key);
    $config = &$this->config;
    
    foreach( $keys as $i => $k )
    {
      if( $i === count($keys) - 1 )
      {
        $config[$k] = $value;
      }
      else
      {
        if( ! isset($config[$k]) || ! is_array($config[$k]) )
        {
          $config[$k] = [];
        }
        
        $config = &$config[$k];
      }
    }
  }
  
  /**
   * Save configuration to file
   */
  public function saveConfig() : bool
  {
    try
    {
      $configDir = __DIR__ . '/../../';
      if( ! is_dir($configDir) )
      {
        mkdir($configDir, 0755, true);
      }
      
      $configFile = $configDir . 'config.yml';
      file_put_contents($configFile, Yaml::dump($this->config, 4));
      return true;
    }
    catch( \Exception $e )
    {
      Log::error("Failed to save config: " . $e->getMessage());
      return false;
    }
  }
  
  /**
   * Get default configuration
   */
  private function getDefaultConfig() : array
  {
    return [
      'app' => [
        'name' => 'MicroFrm App',
        'debug' => true
      ],
      'session' => [
        'timeout' => 3600, // 1 hour
        'secure' => true,
        'httponly' => true
      ],
      'login' => [
        'methods' => ['email'],
        'google' => [
          'enabled' => false,
          'client_id' => '',
          'client_secret' => ''
        ],
        'auth0' => [
          'enabled' => false,
          'domain' => '',
          'client_id' => '',
          'client_secret' => ''
        ],
        'unique_url' => [
          'enabled' => false,
          'expiry' => 86400 // 24 hours
        ]
      ],
      'security' => [
        'csrf_token_expiry' => 3600,
        'password_algo' => PASSWORD_ARGON2ID
      ]
    ];
  }
}
