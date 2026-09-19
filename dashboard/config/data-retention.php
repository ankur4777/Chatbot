<?php

return [
    'closed_chats' => [
        'retention_days' => env('DATA_RETENTION_CLOSED_CHATS_DAYS', 30),
        'chunk_size' => env('DATA_RETENTION_CLOSED_CHATS_CHUNK', 100),
    ],

    'chatbot_conversations' => [
        'retention_days' => env('DATA_RETENTION_CHATBOT_CONVERSATIONS_DAYS', 30),
        'chunk_size' => env('DATA_RETENTION_CHATBOT_CONVERSATIONS_CHUNK', 100),
    ],

    'missed_chats' => [
        'retention_days' => env('DATA_RETENTION_MISSED_CHATS_DAYS', 30),
        'chunk_size' => env('DATA_RETENTION_MISSED_CHATS_CHUNK', 100),
    ],

    'visitors' => [
        'retention_days' => env('DATA_RETENTION_VISITORS_DAYS', 30),
        'chunk_size' => env('DATA_RETENTION_VISITORS_CHUNK', 100),
    ],
];
