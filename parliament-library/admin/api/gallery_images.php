<?php
header('Content-Type: application/json');

$images = [
    [
        'id' => 1,
        'url' => 'https://parliament.gh/photo-gallery/parliament-building.jpg',
        'original_name' => 'Parliament Building Exterior',
        'category_id' => 1,
        'category' => 'Architecture',
        'uploaded_by' => 'Admin',
        'uploaded_at' => '2024-01-15',
        'description' => 'The majestic Parliament House of Ghana'
    ],
    [
        'id' => 2,
        'url' => 'https://parliament.gh/photo-gallery/reading-room.jpg',
        'original_name' => 'Library Reading Room',
        'category_id' => 2,
        'category' => 'Library',
        'uploaded_by' => 'Librarian',
        'uploaded_at' => '2024-01-20',
        'description' => 'The main reading area of the Parliament Library'
    ],
    [
        'id' => 3,
        'url' => 'https://parliament.gh/photo-gallery/rare-books.jpg',
        'original_name' => 'Rare Books Collection',
        'category_id' => 2,
        'category' => 'Library',
        'uploaded_by' => 'Archivist',
        'uploaded_at' => '2024-01-25',
        'description' => 'Section containing rare parliamentary documents'
    ],
    [
        'id' => 4,
        'url' => 'https://parliament.gh/photo-gallery/conference-hall.jpg',
        'original_name' => 'Conference Hall',
        'category_id' => 3,
        'category' => 'Events',
        'uploaded_by' => 'Event Manager',
        'uploaded_at' => '2024-02-01',
        'description' => 'Main conference hall for parliamentary sessions'
    ],
    [
        'id' => 5,
        'url' => 'https://parliament.gh/photo-gallery/digital-archive.jpg',
        'original_name' => 'Digital Archive Center',
        'category_id' => 4,
        'category' => 'Technology',
        'uploaded_by' => 'IT Department',
        'uploaded_at' => '2024-02-05',
        'description' => 'Modern digital archiving facilities'
    ],
    [
        'id' => 6,
        'url' => 'https://parliament.gh/photo-gallery/reading-nook.jpg',
        'original_name' => 'Reading Nook',
        'category_id' => 2,
        'category' => 'Library',
        'uploaded_by' => 'Librarian',
        'uploaded_at' => '2024-02-10',
        'description' => 'Cozy reading corner in the library'
    ]
];

echo json_encode([
    'success' => true,
    'images' => $images
]);
