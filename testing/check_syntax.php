<?php
// Check all PHP files for common errors
error_reporting(E_ALL);
ini_set('display_errors', 1);

echo "<h1>PHP Syntax Check</h1>";
echo "<p>Checking all PHP files...</p>";

$directories = ['../admin', '../kasir', '../customer', '../includes'];
$root_files = ['../index.php', '../config.php', '../logout.php'];

$errors = [];
$success = [];

// Check root files
foreach ($root_files as $file) {
    if (file_exists($file)) {
        $output = shell_exec("php -l $file 2>&1");
        if (strpos($output, 'No syntax errors') !== false) {
            $success[] = $file;
        } else {
            $errors[$file] = $output;
        }
    }
}

// Check directories
foreach ($directories as $dir) {
    if (is_dir($dir)) {
        $files = glob("$dir/*.php");
        foreach ($files as $file) {
            $output = shell_exec("php -l $file 2>&1");
            if (strpos($output, 'No syntax errors') !== false) {
                $success[] = $file;
            } else {
                $errors[$file] = $output;
            }
        }
    }
}

echo "<h2 style='color: green;'>Files OK (" . count($success) . "):</h2>";
echo "<ul>";
foreach ($success as $file) {
    echo "<li>✓ $file</li>";
}
echo "</ul>";

if (count($errors) > 0) {
    echo "<h2 style='color: red;'>Files dengan Error (" . count($errors) . "):</h2>";
    foreach ($errors as $file => $error) {
        echo "<h3 style='color: red;'>$file</h3>";
        echo "<pre style='background: #fee; padding: 10px; border: 1px solid red;'>$error</pre>";
    }
} else {
    echo "<h2 style='color: green;'>✓ Semua file PHP tidak ada syntax error!</h2>";
}
?>
