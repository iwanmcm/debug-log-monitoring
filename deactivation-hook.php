<?php

function dlm_deactivate() {
    dlm_log('Deactivating Debug Log Monitoring plugin');
    $timestamp = wp_next_scheduled( 'dlm_cron_hook_process_log' );
    wp_unschedule_event( $timestamp, 'dlm_cron_hook_process_log' );
    wp_clear_scheduled_hook('dlm_cron_hook_process_log');
    wp_unschedule_hook('dlm_cron_hook_process_log');
}
