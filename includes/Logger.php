<?php
namespace KashifBakers\Subscriptions;

class Logger {
    private $log_file;
    
public function __construct() {
    $upload_dir = wp_upload_dir();
    $this->log_file = $upload_dir['basedir'] . '/mss-logs/debug.log';

    if ( ! is_dir( dirname( $this->log_file ) ) ) {
        wp_mkdir_p( dirname( $this->log_file ) );
        // Block direct access to the log folder
        file_put_contents( dirname( $this->log_file ) . '/.htaccess', 'Deny from all' );
    }
}    
    public function log( $message, $level = 'info' ) {
$timestamp   = wp_date( 'Y-m-d H:i:s' );        $log_message = "[$timestamp] [$level] $message" . PHP_EOL;
        
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