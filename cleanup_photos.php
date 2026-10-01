<?php

require_once 'vendor/autoload.php';

$app = require_once 'bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

echo "=== Cleaning up orphaned photo records ===\n";

$photos = \App\Models\ProjectPhoto::all();
$deleted = 0;

foreach ($photos as $photo) {
    $filePath = public_path('storage/' . $photo->body_image);
    if (!file_exists($filePath)) {
        echo "Deleting orphaned record: {$photo->body_image} (ID: {$photo->id})\n";
        $photo->delete();
        $deleted++;
    } else {
        echo "Keeping valid record: {$photo->body_image} (ID: {$photo->id})\n";
    }
}

echo "\nDeleted $deleted orphaned records.\n";
echo "Remaining records: " . \App\Models\ProjectPhoto::count() . "\n";