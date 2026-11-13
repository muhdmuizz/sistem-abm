<?php

return [
    'client_id' => env('GOOGLE_CLIENT_ID'),
    'client_secret' => env('GOOGLE_CLIENT_SECRET'),
    'redirect_uri' => env('GOOGLE_REDIRECT_URI'),
    'workspace_domains' => array_values(array_filter(array_map(
        fn ($domain) => strtolower(trim($domain)), explode(',', env('GOOGLE_WORKSPACE_DOMAINS', '')),
    ))),
];
