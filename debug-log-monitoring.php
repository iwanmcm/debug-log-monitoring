<?php
/**
Plugin Name: Debug Log Monitoring
Plugin URI: https://github.com/iwanmcm/debug-log-monitoring
Version: 1.0.0
Author: Dr. Iwan Gurjanow
Description: Splits the debug.log into daily files and clears the log file (stores 28 days by default). Optionally, a report can be send via Slack or E-Mail. Highly customizable.
Text Domain: dlm
 */

include_once('debug-log-monitoring.config.php');
include_once('utils/cron-schedules.php');
include_once('utils/log.php');
include_once('utils/message-builder.php');
include_once('deactivation-hook.php');

add_action( 'wp_mail_failed', 'onMailError', 10, 1 );
function onMailError( $wp_error ) {
    dlm_log(print_r($wp_error, true), E_USER_ERROR);
}

add_action( 'dlm_cron_hook_process_log', 'dlm_cron_exec_process_log' );

function dlm_cron_exec_process_log() {
    dlm_log('Start debug log monitoring procedure');
    $logFile = WP_CONTENT_DIR . '/debug.log';
    $config = dlm_getConfig();

    if(!file_exists($logFile)) {
        dlm_log('Stopping forcefully debug log monitoring procedure because no debug.log file available.');
        return;
    }

    $debugLogsFolder = WP_CONTENT_DIR . DIRECTORY_SEPARATOR . $config['logsFolder'];
    if (!file_exists($debugLogsFolder)) {
        mkdir($debugLogsFolder, 0700, true);
    }

    $logFileNew = $debugLogsFolder . '/' . date('Y-m-d_H:i')  . '-debug.log';
    // copy content of logfile to new file
    copy($logFile, $logFileNew);
    // delete content of logfile
    if ($config['clearLog']) {
        file_put_contents($logFile, '');
    }

    // remove log files older than x days
    $files = glob($debugLogsFolder . '/*.log');
    $now   = time();
    $maxAge = 60 * 60 * 24 * $config['maxStoreAgeInDays']; // 28 days
    foreach ($files as $file) {
        if (is_file($file)) {
            if ($now - filemtime($file) >= $maxAge) {
                unlink($file);
            }
        }
    }

    // Send slack message with summary
    if ($config['notifications']['enabled']) {
        dlm_doNotify($logFileNew);
    }
    else {
        dlm_log('Skipping notification because notifications are disabled in config.');
    }

    dlm_log('End debug log procedure');
}

function dlm_doNotify(string $logFilePath): void {
    $config = dlm_getConfig();
    $channels = $config['notifications']['channels'];
    $slackEnabled = $channels['slack']['enabled'];
    $emailEnabled = $channels['email']['enabled'];
    $isAnyChannelEnabled = $slackEnabled || $emailEnabled;
    if (!$isAnyChannelEnabled) {
        dlm_log('Skipping notification because no notification channel is enabled in config.');
        return;
    }

    if ($slackEnabled) {
        dlm_doNotifySlack($logFilePath);
    }

    if ($emailEnabled) {
        dlm_doNotifyEmail($logFilePath);
    }

    dlm_log('Notification sent.');
}

function dlm_doNotifySlack(string $logFilePath): void {
    dlm_log('Sending slack notification...');
    $config = dlm_getConfig();
    $slackChannel = $config['notifications']['channels']['slack'];
    $message = dlm_buildMessage($logFilePath, 'slack');
    $data = array(
        'text' => $message,
    );
    $data = json_encode($data);
    // json encode escapes automatically slashes, so we need to unescape them
    $data = str_replace('\\\n', '\n', $data);
    $curl = curl_init($slackChannel['webhookUrl']);
    curl_setopt($curl, CURLOPT_CUSTOMREQUEST, "POST");
    curl_setopt($curl, CURLOPT_POSTFIELDS, $data);
    curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($curl, CURLOPT_HTTPHEADER, array(
        'Content-Type: application/json',
        'Content-Length: ' . strlen($data)
    ));
    $result = curl_exec($curl);
    curl_close($curl);
    dlm_log('End Slack Notification with log summary');
}

function dlm_doNotifyEmail(string $logFilePath): void {
    dlm_log('Sending email notification...');
    $config = dlm_getConfig();
    $emailChannel = $config['notifications']['channels']['email'];
    $message = dlm_buildMessage($logFilePath, 'email');
    $sender = $emailChannel['sender'];
    $headers = ["FROM: $sender", 'Content-Type: text/html; charset=UTF-8'];
    $result = wp_mail($emailChannel['recipients'], $emailChannel['subject'], $message, $headers);
    if (!$result) {
        dlm_log('Error sending email notification.', E_USER_WARNING);
    }
    else {
        dlm_log('End Email Notification with log summary');
    }
}

if ( ! wp_next_scheduled( 'dlm_cron_hook_process_log' ) ) {
    $config = dlm_getConfig();
    wp_schedule_event( time(), $config["interval"], 'dlm_cron_hook_process_log' );
}

register_deactivation_hook( __FILE__, 'dlm_deactivate' );
