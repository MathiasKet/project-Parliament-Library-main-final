<?php
header('Content-Type: application/json');

$categories = [
    [
        'id' => 1,
        'name' => 'Architecture',
        'description' => 'Photos of the Parliament buildings and facilities'
    ],
    [
        'id' => 2,
        'name' => 'Library',
        'description' => 'Library spaces and collections'
    ],
    [
        'id' => 3,
        'name' => 'Events',
        'description' => 'Special events and activities'
    ],
    [
        'id' => 4,
        'name' => 'Technology',
        'description' => 'Digital resources and technology'
    ]
];

echo json_encode([
    'success' => true,
    'categories' => $categories
]);
