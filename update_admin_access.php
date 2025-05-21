<?php
/**
 * Script to update all admin pages to use the new role-based access control system
 */

// Define the directory to process
$adminDir = __DIR__ . '/admin';

// Define the files to update
$filesToProcess = [
    'users.php' => 'users',
    'trainers.php' => 'trainers', 
    'services.php' => 'services',
    'subscriptions.php' => 'subscriptions',
    'schedule.php' => 'schedule',
    'reviews.php' => 'reviews',
    'statistics.php' => 'statistics'
];

// Process each file
foreach ($filesToProcess as $filename => $resource) {
    $filePath = $adminDir . '/' . $filename;
    
    if (file_exists($filePath)) {
        echo "Processing $filename... ";
        
        // Read the file content
        $content = file_get_contents($filePath);
        
        // Define the pattern to search for old access check
        $oldAccessPattern = '/(session_start\(\);.*?require_once\([\'"]\.\.\/database\/config\.php[\'"]\);.*?)' . 
                           '(\/\/\s*Проверка\s*(?:доступа|авторизации).*?if\s*\(!isset\(\$_SESSION\[\'user_id\'\]\)\s*\|\|\s*.*?\)\s*{.*?exit\(\);.*?})/s';
        
        // Define the replacement with new access check
        $newAccessCode = '$1' . "\nrequire_once('includes/auth_check.php');\n\n// Check access for $resource\ncheckAccess('$resource');";
        
        // Perform the replacement
        $newContent = preg_replace($oldAccessPattern, $newAccessCode, $content, -1, $count);
        
        if ($count > 0) {
            // Save the updated content
            file_put_contents($filePath, $newContent);
            echo "Updated ($count replacements)\n";
        } else {
            echo "No replacements made\n";
        }
    } else {
        echo "File $filename not found\n";
    }
}

echo "\nUpdate completed!\n"; 