<?php

function dlm_log(string $message, int $error_level = E_USER_NOTICE) {
    $message = "DLM: $message";
    trigger_error($message, $error_level);
}