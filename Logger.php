<?php
namespace KashifBakers\Subscriptions;

class Logger {
    private $log_file;
    
    public function __construct() {
        $this->log_file = WP_CONTENT_DIR . '/plugins/my-subscription-system/logs/debug.log';
        
        // Create logs directory if doesn't exist
        if ( ! is_dir( dirname( $this->log_file ) ) ) {
            mkdir( dirname( $this->log_file ), 0755, true );
        }
    }
    
    public function log( $message, $level = 'info' ) {
        $timestamp = date( 'Y-m-d H:i:s' );
        $log_message = "[$timestamp] [$level] $message" . PHP_EOL;
        
        error_log( $log_message, 3, $this->log_file );
    }
    
    public function getLogs( $lines = 50 ) {
        if ( ! file_exists( $this->log_file ) ) {
            return array();
        }
        
        $file_contents = file( $this->log_file );
        return array_slice( $file_contents, -$lines );
    }
}