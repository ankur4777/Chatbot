<?php

return [
    'max_upload_kb' => (int) env('GALLERY_IMAGE_MAX_UPLOAD_KB', 5120),
    'max_results' => (int) env('GALLERY_IMAGE_MAX_RESULTS', 3),
    'min_score' => (int) env('GALLERY_IMAGE_MIN_SCORE', 2),
    'storage_disk' => env('GALLERY_IMAGE_DISK', 'public'),
    'storage_directory' => env('GALLERY_IMAGE_DIRECTORY', 'gallery-images'),
];
