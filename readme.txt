# Debug Log Monitoring (DLM) Plugin
__Disclaimer:__ You will need FTP access to your server to correctly use this plugin.

## Description
This plugin is for developers who want to monitor their debug.log file. Its purpose is to create a scheduled task 
that splits the main `debug.log` file into interval based (e.g. daily, weekly) files.
Also it provides a simple way to send a customized log summary notification via email or Slack.

## Installation

### Step 1: Install the plugin via WordPress Plugin Management
__Do not activate it directly!! Follow Step 2 and Step 3 first.__

### Step 2: Activate WordPress debug logging
In order to use this plugin you need to activate WordPress debug logging. Add the following to your `wp-config.php` file:

    define( 'WP_DEBUG', true );
    define( 'WP_DEBUG_LOG', true );
    define( 'WP_DEBUG_DISPLAY', false );
    @ini_set( 'display_errors', 0 );

### Step 3: Configure the plugin
It is important to customize the configuration before activating the plugin.

Navigate to `wp-content/plugins/debug-log-monitoring` directory using a FTP Client, duplicate the ``debug-log-monitoring.config.example.php`` file
and rename it to ``debug-log-monitoring.config.php``. Adjust the configuration settings to your needs. 

For a detailed explanation of all options, see section **Config** below.

### Step 4: Activate the plugin
Activate the plugin via the WordPress Plugin Management.

*Note*: The scheduler usually runs the first time directly after activation. And the next time after the configured interval. 
If you want to change the config and re-run the scheduler, please follow the steps below.
- Deactivate the plugin (this will reset and remove the scheduler)
- Adjust the config
- Activate the plugin

## Config

Following settings can be configured:
### Base Settings
#### appName - string
The name of your application or website. It is used to build the reporting summary message body.

#### interval - string 
Sets the time interval between two runs of the debug log monitoring. Valid values:
- "12_hours"
- "24_hours"
- "2_days"
- "1_week"

#### maxStoreAgeInDays - int (default 28)
How many days the logs should be saved on disc before automatically removed.

#### logsFolder - string (default "debug-logs")
The folder, in which the created logs will be stored - relative to `wp-content/`.

#### clearLog - boolean (default true)
Truncate (clear) the main ``debug.log`` file after the interval log file has been created with the debug logs content.

#### configVersion - string
Helps the plugin to validate, if the configuration needs to be updated, in case the plugin updates. Please check provided changelog in this case.

### Notifications Settings
#### notifications.enabled - boolean
Enable sending a reporting summary via different channels. Before enabling, make sure at least one channel is properly configured.

#### notifications.logFilters - LogFilter[]
An array of LogFilter sub arrays (associative) that define for which string occurences the report should be scanned. This is the base for
creating the report. See example below.

#### notifications.logFilters[0].filterKey - string
The string sequence that should be counted within the log. A value of e.g. "PHP Fatal error:" will search the log for all occurences of php fatal errors.

#### notifications.logFilters[0].notificationString - string
The string that should be used in the notification message. E.g. "%d PHP Fatal Errors". The %d will be replaced with the actual count of the filterKey.

#### notifications.logFilters[0].emojis - Emoji[]
An array of Emoji sub arrays (associative) that define which emojis should be used in the notification message. See example below.

#### notifications.logFilters[0].emojis[0].threshold - number
The threshold that defines, if the emoji should be used. E.g. if the threshold is 10 and the filterKey was found 5 times, the emoji will not be used.

#### notifications.logFilters[0].emojis[0].slack - string
The emoji string that should be appended in the slack notification message. E.g. ":fire:".

#### notifications.logFilters[0].emojis[0].email - string
The emoji string that should be appended in the email notification message. E.g. "🔥".

### Notification Channel Settings
#### notifications.channels.slack.enabled - boolean
Enable sending a reporting summary via slack.

#### notifications.channels.slack.webhookUrl - string
The slack webhook url to send the notification to.

#### notifications.channels.email.enabled - boolean
Enable sending a reporting summary via email.

#### notifications.channels.email.recipients - string[]
An array of email addresses that should receive the notification.

#### notifications.channels.email.sender - string
The email address that should be used as sender. Please note: If you are using an SMPT Plugin like WP Mail SMTP, the sender address will be overwritten by the plugin.

#### notifications.channels.email.subject - string
The subject of the email notification.

## FAQs

### How can I see the created log files?
The created log files are stored in the folder defined in the config option `logsFolder`. The default is `wp-content/debug-logs`.
Connect to your server via FTP and navigate to the folder. You should see the created log files.

### What is the slack webhook url? And how can I get one?
The slack webhook url is a unique url that is used to send a message to a specific slack channel.
Please follow the instructions on the slack website to create a webhook url: https://api.slack.com/messaging/webhooks

### I am not receiving any emails locally. What can I do?
If you are using a local development environment, you might not receive any emails. This is because the plugin uses the WordPress function `wp_mail()`.
We recommend to use a plugin like WP Mail SMTP to send emails locally. This way emails are also less likely to be marked as spam.

### Is it possible to start the scheduler at a specific time?
The schedular usually runs the first time directly after activation. Currently, there is no way to define a start time.
As a workaround, you can deactivate the plugin, and activate it again at the specific time.

### Is there a UI to configure the plugin?
Currently, there is no UI to configure the plugin. All configuration is done via the config file.

### How can I add messages to the log from within my plugin or theme?
You can easily add messages with the PHP build-in function 

``trigger_error(string $message, int $error_level = E_USER_NOTICE): bool``

See here: https://www.php.net/manual/en/function.trigger-error.php

### Question not listed here?
Please open an issue on GitHub: 