<<<<<<< HEAD
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
=======
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
>>>>>>> 882da412c224f7c20dfb67829049d92fbad8991f
?>