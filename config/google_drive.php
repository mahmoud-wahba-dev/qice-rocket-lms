<?php

return [
    /*
    | Shared QIEC Google Drive folder (private). Share this folder with the
    | service-account client_email as Content Manager / Editor.
    */
    'folder_id' => env('GOOGLE_DRIVE_FOLDER_ID'),

    /*
    | Absolute path or storage-relative path to the service-account JSON key.
    | Example: storage/app/google-drive/service-account.json
    */
    'credentials' => env('GOOGLE_DRIVE_CREDENTIALS', storage_path('app/google-drive/service-account.json')),

    'scopes' => [
        'https://www.googleapis.com/auth/drive.readonly',
    ],
];
