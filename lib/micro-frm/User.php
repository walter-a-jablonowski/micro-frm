<?php
/**
 * User.php
 * Manages user authentication and user data
 */

namespace MicroFrm;

use Symfony\Component\Yaml\Yaml;
use Symfony\Component\Yaml\Exception\ParseException;
use Google\Client as GoogleClient;
use MicroFrm\Config;
use MicroFrm\Session;

class User
{
  private $userId = null;
  private $userData = [];
  private $isAuthenticated = false;
  private $userDir = null;
  private $userFile = null;
  private $config = null;
  private $session = null;
  
  public function __construct( Config $config, Session $session )
  {
    $this->config = $config;
    $this->session = $session;
    $this->userDir = __DIR__ . '/../../data/users/';
    
    // Ensure user directory exists
    if( ! is_dir($this->userDir) )
    {
      mkdir($this->userDir, 0755, true);
    }
    
    // Check if user is already logged in
    if( $this->session->has('user_id') )
    {
      $this->userId = $this->session->get('user_id');
      $this->loadUserData();
      $this->isAuthenticated = true;
    }
  }
  
  /**
   * Load user data from file
   */
  private function loadUserData() : void
  {
    if( $this->userId === null )
    {
      return;
    }
    
    $this->userFile = $this->userDir . $this->userId . '/user.yml';
    
    if( file_exists($this->userFile) )
    {
      try
      {
        $this->userData = Yaml::parseFile($this->userFile);
      }
      catch( ParseException $e )
      {
        Log::error("Error parsing user file: " . $e->getMessage());
        $this->userData = [];
      }
    }
  }
  
  /**
   * Save user data to file
   */
  public function save() : bool
  {
    if( $this->userId === null )
    {
      return false;
    }
    
    try
    {
      $userDir = $this->userDir . $this->userId;
      if( ! is_dir($userDir) )
      {
        mkdir($userDir, 0755, true);
      }
      
      file_put_contents($this->userFile, Yaml::dump($this->userData, 4));
      return true;
    }
    catch( \Exception $e )
    {
      Log::error("Failed to save user data: " . $e->getMessage());
      return false;
    }
  }
  
  /**
   * Check if user is authenticated
   */
  public function isAuthenticated() : bool
  {
    return $this->isAuthenticated;
  }
  
  /**
   * Get user ID
   */
  public function getId() : ?string
  {
    return $this->userId;
  }
  
  /**
   * Get user data
   * 
   * @param string $key User data key
   * @param mixed $default Default value if key not found
   * @return mixed User data value
   */
  public function get( $key, $default = null )
  {
    $keys = explode('.', $key);
    $value = $this->userData;
    
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
   * Set user data
   * 
   * @param string $key User data key
   * @param mixed $value Value to set
   */
  public function set( $key, $value ) : void
  {
    $keys = explode('.', $key);
    $data = &$this->userData;
    
    foreach( $keys as $i => $k )
    {
      if( $i === count($keys) - 1 )
      {
        $data[$k] = $value;
      }
      else
      {
        if( ! isset($data[$k]) || ! is_array($data[$k]) )
        {
          $data[$k] = [];
        }
        
        $data = &$data[$k];
      }
    }
    
    $this->save();
  }
  
  /**
   * Login with email and password
   * 
   * @param string $email User email
   * @param string $password User password
   * @return bool True if login successful
   */
  public function login( $email, $password ) : bool
  {
    $userId = $this->findUserByEmail($email);
    
    if( $userId !== null )
    {
      // Load user data
      $this->userId = $userId;
      $this->loadUserData();
      
      // Verify password
      if( isset($this->userData['password'])
      &&  password_verify($password, $this->userData['password']))
      {
        // Update password hash if needed
        $this->updatePasswordHashIfNeeded($password);
        
        // Set session
        $this->session->set('user_id',      $this->userId);
        $this->session->set('login_time',   time());
        $this->session->set('login_method', 'email');
        
        $this->isAuthenticated = true;
        
        Log::info("User logged in: {$this->userId}", ['email' => $email]);
        return true;
      }
    }
    
    Log::warning('Failed login attempt', ['email' => $email]);
    return false;
  }
  
  /**
   * Register a new user
   * 
   * @param string $email User email
   * @param string $password User password
   * @param array $userData Additional user data
   * @return bool True if registration successful
   */
  public function register( $email, $password, array $userData = [] ) : bool
  {
    // Check if email already exists
    if( $this->findUserByEmail($email) !== null )
    {
      return false;
    }
    
    // Create user ID
    $this->userId = $this->generateUserId();
    
    // Set user file path
    $this->userFile = $this->userDir . $this->userId . '/user.yml';
    
    // Set user data
    $this->userData = array_merge($userData, [
      'email' => $email,
      'password' => $this->hashPassword($password),
      'created_at' => date('Y-m-d H:i:s'),
      'updated_at' => date('Y-m-d H:i:s'),
      'unique_url_tokens' => [] // Initialize empty array for tokens
    ]);
    
    // Save user data
    if( $this->save() )
    {
      // Set session
      $this->session->set('user_id', $this->userId);
      $this->session->set('login_time', time());
      $this->session->set('login_method', 'email');
      
      $this->isAuthenticated = true;
      
      Log::info("User registered: {$this->userId}", ['email' => $email]);
      return true;
    }
    
    return false;
  }
  
  /**
   * Login with Google
   * 
   * @param string $token Google ID token
   * @return bool True if login successful
   */
  public function loginWithGoogle( $token ) : bool
  {
    $config = $this->config;
    
    // Check if Google login is enabled
    if( ! $config->get('login.google.enabled', false) )
      return false;
    
    // Verify token
    $client = new GoogleClient(['client_id' => $config->get('login.google.client_id')]);
    
    try
    {
      $payload = $client->verifyIdToken($token);
      
      if( $payload )
      {
        $email = $payload['email'];
        
        // Find user by email or create new user
        $userId = $this->findUserByEmail($email);
        
        if( $userId === null )
        {
          // Create new user
          $this->userId = $this->generateUserId();
          $this->userData = [
            'email' => $email,
            'name' => $payload['name'] ?? '',
            'google_id' => $payload['sub'],
            'created_at' => date('Y-m-d H:i:s'),
            'updated_at' => date('Y-m-d H:i:s')
          ];
          $this->save();
        }
        else
        {
          // Load existing user
          $this->userId = $userId;
          $this->loadUserData();
          
          // Update Google ID if not set
          if( ! isset($this->userData['google_id']) )
          {
            $this->userData['google_id'] = $payload['sub'];
            $this->userData['updated_at'] = date('Y-m-d H:i:s');
            $this->save();
          }
        }
        
        // Set session
        $this->session->set('user_id', $this->userId);
        $this->session->set('login_time', time());
        $this->session->set('login_method', 'google');
        
        $this->isAuthenticated = true;
        
        Log::info("User logged in with Google: {$this->userId}", ['email' => $email]);
        return true;
      }
    }
    catch( \Exception $e ) {
      Log::error("Google login error: " . $e->getMessage());
    }
    
    return false;
  }
  
  /**
   * Login with Auth0
   * 
   * @param array $userInfo Auth0 user info
   * @return bool True if login successful
   */
  public function loginWithAuth0( $userInfo ) : bool
  {
    $config = $this->config;
    
    // Check if Auth0 login is enabled
    if( ! $config->get('login.auth0.enabled', false))
      return false;
    
    try
    {
      $email = $userInfo['email'] ?? null;
      
      if( $email )
      {
        // Find user by email or create new user
        $userId = $this->findUserByEmail($email);
        
        if( $userId === null )
        {
          // Create new user
          $this->userId = $this->generateUserId();
          $this->userData = [
            'email'      => $email,
            'name'       => $userInfo['name'] ?? '',
            'auth0_id'   => $userInfo['sub'],
            'created_at' => date('Y-m-d H:i:s'),
            'updated_at' => date('Y-m-d H:i:s')
          ];
          $this->save();
        }
        else
        {
          // Load existing user
          $this->userId = $userId;
          $this->loadUserData();
          
          // Update Auth0 ID if not set
          if( ! isset($this->userData['auth0_id']) )
          {
            $this->userData['auth0_id']   = $userInfo['sub'];
            $this->userData['updated_at'] = date('Y-m-d H:i:s');
            $this->save();
          }
        }
        
        // Set session
        $this->session->set('user_id', $this->userId);
        $this->session->set('login_time', time());
        $this->session->set('login_method', 'auth0');
        
        $this->isAuthenticated = true;
        
        Log::info("User logged in with Auth0: {$this->userId}", ['email' => $email]);
        return true;
      }
    }
    catch( \Exception $e ) {
      Log::error("Auth0 login error: " . $e->getMessage());
    }
    
    return false;
  }
  
  /**
   * Generate a unique URL for login
   * 
   * @param string $email User email (optional)
   * @return string|null Unique URL token or null on failure
   */
  public function generateUniqueUrlToken( $email = null ) : ?string
  {
    $config = $this->config;
    
    // Check if unique URL login is enabled
    if( ! $config->get('login.unique_url.enabled', false) )
      return null;
    
    try
    {
      // Generate token
      $token = bin2hex(random_bytes(32));
      $expiry = time() + $config->get('login.unique_url.expiry', 86400);
      
      // Find or create user
      if( $email !== null )
      {
        $userId = $this->findUserByEmail($email);
        
        if( $userId === null )
        {
          // Create new user
          $this->userId = $this->generateUserId();
          $this->userData = [
            'email' => $email,
            'created_at' => date('Y-m-d H:i:s'),
            'updated_at' => date('Y-m-d H:i:s')
          ];
        }
        else
        {
          // Load existing user
          $this->userId = $userId;
          $this->loadUserData();
        }
      }
      else
      {
        // Create anonymous user
        $this->userId = $this->generateUserId();
        $this->userData = [
          'is_anonymous' => true,
          'created_at' => date('Y-m-d H:i:s'),
          'updated_at' => date('Y-m-d H:i:s')
        ];
      }
      
      // Save token
      $this->userData['unique_url_tokens'][$token] = [
        'created_at' => date('Y-m-d H:i:s'),
        'expires_at' => date('Y-m-d H:i:s', $expiry)
      ];
      
      $this->save();
      
      return $token;
    }
    catch( \Exception $e ) {
      Log::error("Failed to generate unique URL token: " . $e->getMessage());
      return null;
    }
  }
  
  /**
   * Login with unique URL token
   * 
   * @param string $token Unique URL token
   * @return bool True if login successful
   */
  public function loginWithUniqueUrl( $token ) : bool
  {
    $config = $this->config;
    
    // Check if unique URL login is enabled
    if( ! $config->get('login.unique_url.enabled', false) )
      return false;
    
    try
    {
      // Find user with token
      $userId = $this->findUserByUniqueUrlToken($token);
      
      if( $userId !== null )
      {
        // Load user data
        $this->userId = $userId;
        $this->loadUserData();
        
        // Check if token is valid
        if( isset($this->userData['unique_url_tokens'][$token]) )
        {
          $tokenData = $this->userData['unique_url_tokens'][$token];
          $expiresAt = strtotime($tokenData['expires_at']);
          
          if( $expiresAt > time() )
          {
            // Set session
            $this->session->set('user_id', $this->userId);
            $this->session->set('login_time', time());
            $this->session->set('login_method', 'unique_url');
            $this->session->set('unique_url_token', $token);
            
            $this->isAuthenticated = true;
            
            Log::info("User logged in with unique URL: {$this->userId}");
            return true;
          }
          else
          {
            // Token expired, remove it
            unset($this->userData['unique_url_tokens'][$token]);
            $this->save();
          }
        }
      }
    }
    catch( \Exception $e ) {
      Log::error("Unique URL login error: " . $e->getMessage());
    }
    
    return false;
  }
  
  /**
   * Logout user
   */
  public function logout() : void
  {
    // Check if logged in with unique URL
    if( $this->session->get('login_method') === 'unique_url' && $this->session->has('unique_url_token') )
      $token = $this->session->get('unique_url_token');
      // Don't remove token, it can be used again
    
    // Clear session
    $this->session->remove('user_id');
    $this->session->remove('login_time');
    $this->session->remove('login_method');
    $this->session->remove('unique_url_token');
    
    // Reset user data
    $this->userId = null;
    $this->userData = [];
    $this->isAuthenticated = false;
    
    Log::info("User logged out");
  }
  
  /**
   * Find user by email
   * 
   * @param string $email User email
   * @return string|null User ID or null if not found
   */
  private function findUserByEmail( $email ) : ?string
  {
    foreach( scandir($this->userDir) as $userId )
    {
      if( $userId === '.' || $userId === '..' )
        continue;
      
      $userFile = $this->userDir . $userId . '/user.yml';
      
      if( file_exists($userFile) )
      {
        try
        {
          $userData = Yaml::parseFile($userFile);
          
          if( isset($userData['email']) && $userData['email'] === $email )
            return $userId;
        }
        catch( ParseException $e ) {
          Log::error("Error parsing user file: " . $e->getMessage());
        }
      }
    }
    
    return null;
  }
  
  /**
   * Find user by unique URL token
   * 
   * @param string $token Unique URL token
   * @return string|null User ID or null if not found
   */
  private function findUserByUniqueUrlToken( $token ) : ?string
  {
    foreach( scandir($this->userDir) as $userId )
    {
      if( $userId === '.' || $userId === '..' )
        continue;
      
      $userFile = $this->userDir . $userId . '/user.yml';
      
      if( file_exists($userFile) )
      {
        try
        {
          $userData = Yaml::parseFile($userFile);
          
          if( isset($userData['unique_url_tokens'][$token]) )
            return $userId;
        }
        catch( ParseException $e ) {
          Log::error("Error parsing user file: " . $e->getMessage());
        }
      }
    }
    
    return null;
  }
  
  /**
   * Generate a unique user ID
   */
  private function generateUserId() : string
  {
    return uniqid('user_');
  }
  
  /**
   * Hash a password
   */
  private function hashPassword( $password ) : string
  {
    $config = $this->config;
    $algo   = $config->get('security.password_algo', PASSWORD_ARGON2ID);
    
    return password_hash($password, $algo);
  }
  
  /**
   * Update password hash if needed
   */
  private function updatePasswordHashIfNeeded( $password ) : void
  {
    $config = $this->config;
    $algo   = $config->get('security.password_algo', PASSWORD_ARGON2ID);
    
    if( password_needs_rehash($this->userData['password'], $algo) )
    {
      $this->userData['password'] = $this->hashPassword($password);
      $this->userData['updated_at'] = date('Y-m-d H:i:s');
      $this->save();
    }
  }
}
