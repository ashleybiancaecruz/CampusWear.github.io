<?php
$merchDir = __DIR__ . '/Merch';
$destDir = __DIR__ . '/assets/images/merchandise';

if (!is_dir($destDir)) {
    mkdir($destDir, 0755, true);
}

if (is_dir($merchDir)) {
    $files = glob($merchDir . '/*.{png,jpg,jpeg,PNG,JPG,JPEG}', GLOB_BRACE);
    $copied = 0;
    
    foreach ($files as $file) {
        $filename = basename($file);
        $dest = $destDir . '/' . $filename;
        
        if (copy($file, $dest)) {
            $copied++;
            echo "Copied: $filename\n";
        } else {
            echo "Failed: $filename\n";
        }
    }
    
    echo "\nTotal copied: $copied files\n";
    echo "Images are now in: $destDir\n";
} else {
    echo "Merch directory not found at: $merchDir\n";
    echo "Please manually copy images from Merch/ to assets/images/merchandise/\n";
}
?>

