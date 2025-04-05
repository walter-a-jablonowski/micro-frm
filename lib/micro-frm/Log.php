<?php
/**
 * Log.php
 * Simple logging class that writes to a CSV file
 */

namespace MicroFrm;

class Log
{
  // Log levels
  const DEBUG = 'DEBUG';
  const INFO = 'INFO';
  const WARNING = 'WARNING';
  const ERROR = 'ERROR';
  const CRITICAL = 'CRITICAL';
  
  private static $logFile = __DIR__ . '/../../data/app.log';
  
  /**
   * Write a log entry
   * 
   * @param string $level Log level
   * @param string $message Log message
   * @param array $context Additional context data
   */
  public static function log( $level, $message, array $context = [] ) : void
  {
    // Ensure log directory exists
    $logDir = dirname(self::$logFile);
    if( ! is_dir($logDir) )
    {
      mkdir($logDir, 0755, true);
    }
    
    // Format timestamp
    $timestamp = date('Y-m-d H:i:s');
    
    // Get backtrace for source information
    $trace = debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS, 2);
    $source = isset($trace[1]['class']) ? $trace[1]['class'] : '';
    if( isset($trace[1]['function']) )
    {
      $source .= $source ? '::' . $trace[1]['function'] : $trace[1]['function'];
    }
    
    // Format context as JSON
    $contextStr = empty($context) ? '' : json_encode($context);
    
    // Create CSV line
    $line = [
      $timestamp,
      $level,
      $source,
      str_replace('"', '""', $message), // Escape quotes for CSV
      $contextStr
    ];
    
    // Convert to CSV and write to file
    $csv = '"' . implode('","', $line) . '"' . PHP_EOL;
    file_put_contents(self::$logFile, $csv, FILE_APPEND);
  }
  
  /**
   * Log a debug message
   */
  public static function debug( $message, array $context = [] ) : void
  {
    self::log(self::DEBUG, $message, $context);
  }
  
  /**
   * Log an info message
   */
  public static function info( $message, array $context = [] ) : void
  {
    self::log(self::INFO, $message, $context);
  }
  
  /**
   * Log a warning message
   */
  public static function warning( $message, array $context = [] ) : void
  {
    self::log(self::WARNING, $message, $context);
  }
  
  /**
   * Log an error message
   */
  public static function error( $message, array $context = [] ) : void
  {
    self::log(self::ERROR, $message, $context);
  }
  
  /**
   * Log a critical message
   */
  public static function critical( $message, array $context = [] ) : void
  {
    self::log(self::CRITICAL, $message, $context);
  }
}
