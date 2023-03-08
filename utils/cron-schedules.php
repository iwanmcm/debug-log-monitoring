<?php
add_filter( 'cron_schedules', 'dlm_add_cron_interval' );
function dlm_add_cron_interval( $schedules ) {
    $schedules['12_hours'] = array(
        'interval' => 60 * 60 * 12,
        'display'  => esc_html__( 'Every twelve hours' ), );
    $schedules['24_hours'] = array(
        'interval' => 60 * 60 * 24,
        'display'  => esc_html__( 'Every twenty four hours' ), );
    $schedules['2_days'] = array(
        'interval' => 60 * 60 * 24 * 2,
        'display'  => esc_html__( 'Every two days' ), );
    $schedules['1_week'] = array(
        'interval' => 60 * 60 * 24 * 7,
        'display'  => esc_html__( 'Every seven days' ), );
    return $schedules;
}