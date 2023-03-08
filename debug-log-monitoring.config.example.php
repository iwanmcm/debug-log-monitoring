<?php

function dlm_getConfig(): array
{
    return [
        "appName" => "My awesome app",
        "interval" => "24_hours",
        "maxStoreAgeInDays" => 28,
        "logsFolder" => "debug-logs",
        "clearLog" => true,
        "configVersion" => "1.0.0",
        "notifications" => [
            "enabled" => false,
            "logFilters" => [
                [
                    "filterKey" => "PHP Fatal error:",
                    "notificationString" => "%d PHP Fatal errors",
                    "emojis" => [
                        [
                            "threshold" => 10,
                            "slack" => ":surprised-pikachu:",
                            "email" => "😤"
                        ],
                        [
                            "threshold" => 0,
                            "slack" => ":white_check_mark:",
                            "email" => "😎"
                        ]
                    ]
                ],
                [
                    "filterKey" => "You have an error in your SQL syntax;",
                    "notificationString" => "%d SQL syntax errors",
                    "emojis" => [
                        [
                            "threshold" => 0,
                            "slack" => ":white_check_mark:",
                            "email" => "😎"
                        ],
                        [
                            "threshold" => 10,
                            "slack" => ":pepesad:",
                            "email" => "😤"
                        ]
                    ]
                ]
            ],
            "channels" => [
                "slack" => [
                    "enabled" => false,
                    "webhookUrl" => ""
                ],
                "email" => [
                    "enabled" => false,
                    "recipients" => [
                        "your@mail.com",
                    ],
                    "sender" => "Awesome App <info@awesomeapp.com>",
                    "subject" => "My awesome App debug log report"
                ]
            ]
        ]
    ];
}

