<?php
$folder = __DIR__;
$files = scandir($folder);

echo "<h1>Daftar File di Folder Testing</h1>";
echo "<ul>";

foreach ($files as $file) {
    if ($file !== '.' && $file !== '..') {
        echo "<li> <a href=\"" . htmlspecialchars($file) . "\">" . htmlspecialchars($file) . "</a> </li>";
    }
}

echo "</ul>";
?>