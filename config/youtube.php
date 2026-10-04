<?php

return [
    'client_id' => env('YOUTUBE_CLIENT_ID', env('GOOGLE_CLIENT_ID')),
    'client_secret' => env('YOUTUBE_CLIENT_SECRET', env('GOOGLE_CLIENT_SECRET')),
    'redirect_uri' => env('YOUTUBE_REDIRECT_URI'),
    'scopes' => [
        'https://www.googleapis.com/auth/youtube.upload',
        'https://www.googleapis.com/auth/youtube.readonly',
    ],
    'privacy_status' => 'unlisted',
    'temp_disk' => 'local',
    'temp_dir' => 'youtube-temp',
];
