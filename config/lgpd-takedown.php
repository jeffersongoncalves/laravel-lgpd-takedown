<?php

return [

    'table' => 'lgpd_content_reports',

    /*
     * Models that can be reported, by the alias used in the public channel.
     * Each one must implement ReportableContent.
     */
    'reportables' => [
        // 'post' => App\Models\Post::class,
    ],

    /*
     * Grave categories (art. 27) are taken down as soon as they are reported.
     * Set to false to keep them for human review first.
     */
    'remove_grave_immediately' => true,

    /*
     * How long reports of removed grave content (and the data tied to them) are kept:
     * the Marco Civil, art. 15, period (6 months), extendable on request (art. 27, §§2 and 3).
     */
    'retention' => '6 months',

    /*
     * Time the author has to appeal a removal (art. 30).
     */
    'appeal_window' => '30 days',

    /*
     * A reporter is flagged when this many of their reports were rejected within the window
     * (arts. 32 and 33). Flagged reports are still recorded; decide the sanction yourself.
     */
    'abuse' => [
        'rejected' => 5,
        'within' => '30 days',
    ],

    /*
     * Public notice channel: POST {prefix}. Off by default.
     */
    'route' => [
        'enabled' => false,
        'prefix' => 'lgpd/takedown',
        'middleware' => ['web', 'throttle:10,1'],
        'name' => 'lgpd-takedown.store',
    ],

];
