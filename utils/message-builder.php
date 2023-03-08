<?php
function dlm_buildMessage(string $logFilePath, string $channel): string {
    $logFileContent = file_get_contents($logFilePath);
    $lines = substr_count($logFileContent, PHP_EOL);
    $title = dlm_getMessageTitle($lines, $channel);
    $body = dlm_getMessageBody($logFilePath, $channel);
    return $title . $body;
}

function dlm_getMessageTitle(int $linesCount, string $channel): string {
    $config = dlm_getConfig();
    $channelMarkup = [
        "strong" => [
            "slack" => "*%s*",
            "email" => "<strong>%s</strong>",
        ],
    ];
    $appName = $config['appName'];
    $intervalDisplay = str_replace('_', ' ', $config['interval']);
    $appNameStrong = sprintf($channelMarkup['strong'][$channel], $appName);
    $numOfLinesStrong = sprintf($channelMarkup['strong'][$channel], $linesCount);

    return sprintf("$appNameStrong: the debug log for the last %s has $numOfLinesStrong lines:", $intervalDisplay);
}

function dlm_getMessageBody(string $logFilePath, string $channel): string {
    $config = dlm_getConfig();
    $logFilters = $config['notifications']['logFilters'];
    $logFileContent = file_get_contents($logFilePath);
    $message = '';
    $channelMarkup = [
        "bulletPoint" => [
            "slack" => "\n- %s",
            "email" => "<li>%s</li>",
        ],
    ];

    foreach ($logFilters as $logFilter) {
        $filterKey = $logFilter['filterKey'];
        $logFilterCount = substr_count($logFileContent, $filterKey);
        $notificationString = sprintf($logFilter['notificationString'], $logFilterCount);
        $emojis = $logFilter['emojis'];

        if (count($emojis) > 0) {
            // sort $emojis array by attribute threshold
            usort($emojis, function($a, $b) {
                return $a['threshold'] <=> $b['threshold'];
            });

            // filter emojis where logFilterCount is greater or equal to threshold
            $emojis = array_filter($emojis, function($emoji) use ($logFilterCount) {
                return $logFilterCount >= $emoji['threshold'];
            });

            // check if emojis contains any elements

            $emojisCount = count($emojis);
            if ($emojisCount > 0) {
                $emoji = $emojis[$emojisCount - 1];
                $notificationString .= " " . $emoji[$channel];
            }
        }

        $message .= sprintf($channelMarkup['bulletPoint'][$channel], $notificationString);
    }

    if ($channel === 'email') {
        $message = "<ul>$message</ul>";
    }
    return $message;
}