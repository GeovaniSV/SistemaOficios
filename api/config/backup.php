<?php

return [

    'backup' => [
        'name' => env('APP_NAME', 'Laravel'),

        'source' => [
            'files' => [
                'include'                => [],
                'exclude'                => [],
                'follow_links'           => false,
                'ignore_unreadable_dirs' => false,
                'relative_path'          => null,
            ],
            'databases' => [
                'mysql',
            ],
        ],

        'database_dump_compressor' => null,
        'database_dump_file_extension' => '',

        'destination' => [
            'filename_prefix' => 'backup-',
            'disks'           => [
                'backup_local',
            ],
        ],

        'temporary_directory' => storage_path('app/backup-temp'),

        'password'    => null,
        'encryption'  => 'default',
        'tries'       => 1,
        'retry_delay' => 0,
    ],

    'notifications' => [
        'notifications' => [
            \Spatie\Backup\Notifications\Notifications\BackupHasFailedNotification::class       => [],
            \Spatie\Backup\Notifications\Notifications\UnhealthyBackupWasFoundNotification::class => [],
            \Spatie\Backup\Notifications\Notifications\CleanupHasFailedNotification::class      => [],
            \Spatie\Backup\Notifications\Notifications\BackupWasSuccessfulNotification::class   => [],
            \Spatie\Backup\Notifications\Notifications\HealthyBackupWasFoundNotification::class => [],
            \Spatie\Backup\Notifications\Notifications\CleanupWasSuccessfulNotification::class  => [],
        ],
        'notifiable'    => \Spatie\Backup\Notifications\Notifiable::class,
        'mail'          => [
            'to'   => env('BACKUP_NOTIFICATION_EMAIL', 'noreply@example.com'),
            'from' => [
                'address' => env('MAIL_FROM_ADDRESS', 'noreply@example.com'),
                'name'    => env('MAIL_FROM_NAME', 'Sistema de Ofícios'),
            ],
        ],
        'slack' => [
            'webhook_url' => '',
            'channel'     => null,
            'username'    => null,
            'icon'        => null,
        ],
        'discord' => [
            'webhook_url' => '',
            'username'    => '',
            'avatar_url'  => '',
        ],
    ],

    'monitor_backups' => [],

    'cleanup' => [
        'strategy'         => \Spatie\Backup\Tasks\Cleanup\Strategies\DefaultStrategy::class,
        'default_strategy' => [
            'keep_all_backups_for_days'                   => 7,
            'keep_daily_backups_for_days'                 => 16,
            'keep_weekly_backups_for_weeks'               => 8,
            'keep_monthly_backups_for_months'             => 4,
            'keep_yearly_backups_for_years'               => 2,
            'delete_oldest_backups_when_using_more_megabytes_than' => 5000,
        ],
    ],

];
