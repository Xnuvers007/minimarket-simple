<?php
session_start(); // Wajib ada di baris paling atas

// --- KONFIGURASI AKUN ---
$auth_username = 'Xnuvers007';
$auth_password = 'IndraXnuvers007';

// --- LOGIKA LOGOUT ---
// Jika URL ada ?logout=true, hapus sesi login
if (isset($_GET['logout'])) {
    session_destroy();
    header("Location: " . strtok($_SERVER["REQUEST_URI"], '?')); // Refresh ke halaman bersih
    exit;
}

// --- LOGIKA LOGIN ---
if (!isset($_SESSION['sudah_login']) || $_SESSION['sudah_login'] !== true) {
    $error_msg = "";
    
    // Jika user klik tombol Login
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $input_user = $_POST['u'] ?? '';
        $input_pass = $_POST['p'] ?? '';
        
        if ($input_user === $auth_username && $input_pass === $auth_password) {
            $_SESSION['sudah_login'] = true;
            header("Location: " . $_SERVER['REQUEST_URI']); // Refresh halaman
            exit;
        } else {
            $error_msg = "Username atau Password salah!";
        }
    }

    // --- TAMPILAN FORM LOGIN ---
    // (Script akan berhenti di sini dan hanya menampilkan form jika belum login)
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login Area</title>
    <style>
        body { background-color: #1e1e1e; color: #d4d4d4; font-family: sans-serif; display: flex; justify-content: center; align-items: center; height: 100vh; margin: 0; }
        .login-box { background: #252526; padding: 40px; border-radius: 10px; box-shadow: 0 0 20px rgba(0,0,0,0.5); width: 100%; max-width: 320px; text-align: center; }
        h2 { margin-top: 0; color: #fff; }
        input { width: 100%; padding: 12px; margin: 10px 0; background: #333; border: 1px solid #444; color: #fff; border-radius: 4px; box-sizing: border-box; }
        button { width: 100%; padding: 12px; background: #007bff; color: white; border: none; border-radius: 4px; cursor: pointer; font-size: 16px; font-weight: bold; margin-top: 10px; }
        button:hover { background: #0056b3; }
        .error { color: #ff4d4d; margin-bottom: 15px; font-size: 14px; background: rgba(255,0,0,0.1); padding: 10px; border-radius: 4px; }
    </style>
</head>
<body>
    <div class="login-box">
        <h2>🔒 Restricted File</h2>
        <?php if($error_msg): ?><div class="error"><?= $error_msg ?></div><?php endif; ?>
        <form method="POST">
            <input type="text" name="u" placeholder="Username" required autocomplete="off">
            <input type="password" name="p" placeholder="Password" required autocomplete="off">
            <button type="submit">MASUK</button>
        </form>
    </div>
</body>
</html>
<?php
    exit; // PENTING: Stop script agar isi file manager tidak terlihat
}
    
ob_start();

header('X-Frame-Options: SAMEORIGIN');

function logActivity($message) {
    $logFile = 'activity.log';
    $timestamp = date('Y-m-d H:i:s');
    file_put_contents($logFile, "[$timestamp] $message\n", FILE_APPEND);
}

function isAllowedFile($file, $allowedExtensions)
{
    $extension = pathinfo($file, PATHINFO_EXTENSION);
    return in_array(strtolower($extension), $allowedExtensions);
}

$allowedExtensions = [
    'html', 'css', 'js', 'env', 'php', 'txt', 'json', 'xml', 'env', 'gitignore', 'md',
    'yml', 'yaml', 'ini', 'conf', 'log', 'htaccess', 'htpasswd', 'csv', 'tsv', 'sql',
    'c', 'cpp', 'h', 'java', 'py', 'rb', 'sh', 'bat', 'pl', 'go', 'rs', 'swift', 'ts',
    'phtml', 'shtml', 'xhtml', 'jsp', 'asp', 'aspx', 'jspx', 'cfm', 'cfml',
    'scss', 'less', 'sass', 'vue', 'jsx', 'tsx', 'dart', 'lua', 'r', 'm', 'erl', 'hs',
    'groovy', 'kt', 'kts', 'sql', 'ps1', 'psm1', 'vbs', 'vb', 'asm', 'makefile', 'dockerfile'
];

if (isset($_GET['action'])) {
    $action = $_GET['action'];
    $file = $_GET['file'] ?? '';

    if ($action === 'read' && is_file($file)) {
        if (!isAllowedFile($file, $allowedExtensions)) {
            logActivity("Read File Attempted: $file - Not Allowed");
            echo "This file type is not allowed to be edited.";
            exit;
        }
        echo file_get_contents($file);
        logActivity("Read File: $file");
        exit;
    }

    if ($action === 'save' && is_file($file)) {
        if (!isAllowedFile($file, $allowedExtensions)) {
            echo "This file type is not allowed to be edited.";
            logActivity("Save File Attempted: $file - Not Allowed");
            exit;
        }
        $data = json_decode(file_get_contents('php://input'), true);
        file_put_contents($file, $data['content']);
        echo "File saved successfully!";
        logActivity("Saved File: $file");
        exit;
    }

    if ($action === 'rename' && is_file($file)) {
        $newName = $_GET['newName'] ?? '';
        $newPath = dirname($file) . '/' . $newName;
        if (rename($file, $newPath)) {
            echo "File renamed successfully!";
            logActivity("Renamed File: $file to $newPath");
        } else {
            echo "Failed to rename file.";
            logActivity("Failed to Rename File: $file to $newPath");
        }
        exit;
    }

    if ($action === 'listFiles') {
        $currentDir = isset($_GET['dir']) ? $_GET['dir'] : './';

        if (is_dir($currentDir)) {
            $files = array_diff(scandir($currentDir), array('.', '..'));
            logActivity("Listed Files in Directory: $currentDir");
        } else {
            echo json_encode(['error' => 'Invalid directory']);
            logActivity("List Files Attempted: $currentDir - Invalid Directory");
            exit;
        }

        $fileList = [];
        foreach ($files as $file) {
            $filePath = $currentDir . '/' . $file;
            $fileList[] = [
                'name' => $file,
                'date' => date("F d Y H:i:s.", filemtime($filePath)),
                'type' => is_dir($filePath) ? 'Folder' : 'File',
                'size' => is_dir($filePath) ? humanFileSize(getFolderSize($filePath)) : humanFileSize(filesize($filePath))
            ];
        }
        echo json_encode($fileList);
        exit;
    }

    if ($action === 'delete') {
        if (is_file($file)) {
            if (unlink($file)) {
                echo "File deleted successfully!";
                logActivity("Deleted File: $file");
            } else {
                echo "Failed to delete file.";
                logActivity("Failed to Delete File: $file");
            }
        } elseif (is_dir($file)) {
            function deleteFolder($folder) {
                foreach (scandir($folder) as $item) {
                    if ($item === '.' || $item === '..') continue;
                    $itemPath = $folder . '/' . $item;
                    if (is_dir($itemPath)) {
                        deleteFolder($itemPath);
                    } else {
                        unlink($itemPath);
                    }
                }
                return rmdir($folder);
            }

            if (deleteFolder($file)) {
                echo "Folder deleted successfully!";
                logActivity("Deleted Folder: $file");
            } else {
                echo "Failed to delete folder.";
                logActivity("Failed to Delete Folder: $file");
            }
        } else {
            echo "Invalid file or folder.";
            logActivity("Delete Attempted: $file - Invalid File/Folder");
        }
        exit;
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_FILES['uploadedFiles'])) {
    $uploadDir = isset($_GET['dir']) ? $_GET['dir'] : './';
    $uploadedFiles = $_FILES['uploadedFiles'];

    $successCount = 0;
    $errorCount = 0;

    foreach ($uploadedFiles['name'] as $key => $fileName) {
        $fileTmpName = $uploadedFiles['tmp_name'][$key];
        $filePath = $uploadDir . '/' . basename($fileName);

        if (move_uploaded_file($fileTmpName, $filePath)) {
            $successCount++;
            logActivity("Uploaded File: $filePath");
        } else {
            $errorCount++;
            logActivity("Failed to Upload File: $filePath");
        }
    }

    if ($successCount > 0) {
        echo "<script>alert('$successCount file(s) uploaded successfully!'); window.location.href = '?dir=" . urlencode($uploadDir) . "';</script>";
        logActivity("Upload Summary: $successCount Success, $errorCount Failed in $uploadDir");
    }

    if ($errorCount > 0) {
        echo "<script>alert('$errorCount file(s) failed to upload.');</script>";
        logActivity("Upload Summary: $successCount Success, $errorCount Failed in $uploadDir");
    }
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta name="description" content="file manager of htdocs or /var/www/html">
    <meta name="language" content="id">
    <meta name="author" content="Lukman754 & Xnuvers007">
    <meta name="keywords" content="htdocs,html,filemanager">
    <meta name="robots" content="index, follow">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta charset="utf-8">

    <meta itemprop="name" content="File Manager htdocs (/var/www/html)">
    <meta itemprop="description" content="file manager of htdocs or /var/www/html">
    <meta itemprop="image" content=" ">

    <meta property="og:url" content="http://localhost:80">
    <meta property="og:type" content="website" />
    <meta property="og:title" content="File Manager htdocs (/var/www/html)" />
    <meta property="og:description" content="file manager of htdocs or /var/www/html" />
    <meta property="og:image" content=" " />
    <meta property="og:site_name" content="File Manager htdocs (/var/www/html)" />

    <meta name="twitter:card" content="summary_large_image" />
    <meta name="twitter:title" content="File Manager htdocs (/var/www/html)" />
    <meta name="twitter:description" content="file manager of htdocs or /var/www/html" />
    <meta name="twitter:image" content=" " />

    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">

    <title>File Manager Htdocs</title>
    <style>
        body {
            background-color: #1e1e1e;
            color: #d4d4d4;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            margin: 0;
            padding: 0;
            transition: background-color 0.3s, color 0.3s;
        }


        .footer {
            display: block;
            justify-content: space-between;
            align-items: center;
            text-align: center;
            padding: 15px 20px;
            font-size: 10px;
        }

        .footer p {
            margin-bottom: 15px;
        }

        .footer a {
            color: #fcd53f;
            text-decoration: none;
            background-color: #2d2d2d;
            border-radius: 5px;
            padding: 5px 10px;
        }


        .footer a:hover {
            color: #ffb02e;
            /* Warna teks link saat di-hover */
        }

        .footer strong {
            font-weight: normal;
            /* Set weight ke normal untuk konsistensi */
        }


        .container {
            width: 90%;
            margin: 0 auto;
            padding: 20px;
        }

        .search-container {
            margin-bottom: 10px;
        }

        .search-container input[type="text"] {
            width: 100%;
            padding: 10px;
            box-sizing: border-box;
            border: 0;
            border-radius: 4px;
            background-color: #252526;
            color: #d4d4d4;
        }


        .search-container button .sort-icon {
            position: absolute;
            right: 10px;
            top: 50%;
            transform: translateY(-50%);
        }

        .file-table {
            width: 100%;
            border-collapse: collapse;
            overflow-x: auto;
            font-size: 12px;
        }

        .file-table th,
        .file-table td {
            padding: 8px;
            text-align: left;
            border-bottom: 1px solid #2d2d2d;
        }

        .file-table th {
            background-color: #252526;
            font-weight: normal;
            cursor: pointer;
            position: relative;
        }

        .file-table th .sort-icon {
            position: absolute;
            right: 10px;
            top: 50%;
            transform: translateY(-50%);
        }

        .file-table tr:hover {
            background-color: #2a2d2e;
        }

        .folder-icon::before,
        .file-icon::before {
            margin-right: 5px;
        }

        .folder-icon::before {
            content: "📁";
            color: white;
        }

        .file-icon::before {
            content: "📄";
            color: white;
        }

        .file-table a {
            text-decoration: none;
            color: inherit;
        }

        .file-table th:nth-child(1),
        .file-table td:nth-child(1) {
            width: 40%;
        }

        .file-table th:nth-child(2),
        .file-table td:nth-child(2) {
            width: 25%;
        }

        .file-table th:nth-child(3),
        .file-table td:nth-child(3) {
            width: 20%;
        }

        .file-table th:nth-child(4),
        .file-table td:nth-child(4) {
            width: 15%;
        }

        .grey-text {
            color: #a0a0a0;
            font-size: 12px;
        }

        .highlight {
            background-color: #fcd53f;
            color: black;
        }

        .light-mode {
            background-color: #f0f0f0;
            color: #333;
        }

        .light-mode .container {
            background-color: #fff;
            color: #333;
        }

        .light-mode .search-container input[type="text"] {
            background-color: #e0e0e0;
            color: #333;
        }

        .light-mode .search-container button {
            background-color: #e0e0e0;
            color: #333;
        }

        .light-mode .file-table th,
        .light-mode .file-table td {
            border-bottom: 1px solid #ddd;
        }

        .light-mode .file-table tr:hover {
            background-color: #f5f5f5;
        }

        .light-mode .folder-icon::before,
        .light-mode .file-icon::before {
            color: black;
        }

        .light-mode .file-table th {
            background-color: #fff;
            color: #333;
        }

        .light-mode .file-table th .sort-icon {
            color: #333;
        }

        .sort-icon {
            color: inherit;
        }

        .sort-icon {
            color: #d4d4d4;
        }

        @media (max-width: 768px) {

            .search-container input[type="text"],
            .search-container button {
                flex: 1 1 100%;
            }
        }

        body.light-mode {
            background-color: #ffffff;
            /* Warna latar untuk tema terang */
            color: #333333;
            /* Warna teks untuk tema terang */
        }

        body.dark-mode {
            background-color: #1c1c1c;
            /* Warna latar untuk tema gelap */
            color: #ffffff;
            /* Warna teks untuk tema gelap */
        }

        header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            background-color: inherit;
            /* Ikuti warna latar body */
        }

        .button {
            display: flex;
            gap: 10px;
        }

        .toogle {
            padding: 8px 16px;
            font-size: 14px;
            background-color: #007bff;
            color: #fff;
            border: none;
            border-radius: 4px;
            cursor: pointer;
        }

        body.light-mode .toogle {
            background-color: #2d2d2d;
        }

        body.dark-mode .toogle {
            background-color: #444;
        }


        .light-mode .toogle {
            padding: 8px 16px;
            font-size: 14px;
            background-color: #007bff;
            color: #fff;
            border: none;
            border-radius: 4px;
            cursor: pointer;
        }

        .toogle:hover {
            background-color: #2d2d2d;
            color: #d4d4d4;
        }

        .loader {
            border: 16px solid #f3f3f3;
            border-top: 16px solid #3498db;
            border-radius: 50%;
            width: 120px;
            height: 120px;
            animation: spin 2s linear infinite;
            margin: 0 auto;
            display: none;
        }

        @keyframes spin {
            0% {
                transform: rotate(0deg);
            }

            100% {
                transform: rotate(360deg);
            }
        }

        #loading {
            display: block;
            text-align: center;
            margin-top: 20px;
        }

        #loading h1 {
            margin: 10px 0;
        }

        .dark-mode .path-color {
            color: yellow;
        }

        .light-mode .path-color {
            color: red;
        }

        .pagination a {
    display: inline-block;
    padding: 8px 12px;
    margin: 0 5px;
    text-decoration: none;
    color: #007bff;
    border: 1px solid #ddd;
    border-radius: 4px;
    transition: background-color 0.3s, color 0.3s;
}

.pagination a:hover {
    background-color: #007bff;
    color: #fff;
}

.pagination a[style*="color: #fcd53f"] {
    background-color: #fcd53f;
    color: #000;
    font-weight: bold;
}
    </style>
    <script>
        function goBack() {
            const currentDir = window.location.search ? new URLSearchParams(window.location.search).get('dir') : './';
            const parentDir = currentDir.substring(0, currentDir.lastIndexOf('/')) || './';
            window.location.href = '?dir=' + encodeURIComponent(parentDir);
        }


        function searchFiles() {
            var input, filter, table, tr, td, i, txtValue;
            input = document.getElementById("searchInput");
            filter = input.value.toUpperCase();
            table = document.getElementById("fileTable");
            tr = table.getElementsByTagName("tr");

            if (!filter) {
                // If input is empty, redirect to the root or main folder
                window.location.href = '?dir=./'; // Redirect to root folder
                return;
            }

            for (i = 1; i < tr.length; i++) {
                td = tr[i].getElementsByTagName("td")[0]; // Targeting only the name cell
                if (td) {
                    txtValue = td.textContent || td.innerText;
                    if (txtValue.toUpperCase().indexOf(filter) > -1) {
                        tr[i].style.display = "";
                        // Highlight only the matched text
                        const link = td.getElementsByTagName("a")[0];
                        if (link) {
                            const highlightedText = txtValue.replace(new RegExp(filter, "gi"), match => `<span class='highlight'>${match}</span>`);
                            link.innerHTML = highlightedText;
                        }
                    } else {
                        tr[i].style.display = "none";
                    }
                }
            }
        }

        function parseSize(sizeStr) {
            let size = parseFloat(sizeStr);
            if (sizeStr.includes('GB')) return size * (1 << 30);
            if (sizeStr.includes('MB')) return size * (1 << 20);
            if (sizeStr.includes('KB')) return size * (1 << 10);
            return size;
        }

        function sortTable(columnIndex, sortOrder) {
            var table, rows, switching, i, x, y, shouldSwitch;
            table = document.getElementById("fileTable");
            switching = true;

            while (switching) {
                switching = false;
                rows = table.rows;

                for (i = 1; i < (rows.length - 1); i++) {
                    shouldSwitch = false;
                    x = rows[i].getElementsByTagName("TD")[columnIndex];
                    y = rows[i + 1].getElementsByTagName("TD")[columnIndex];

                    let xValue = columnIndex === 3 ? parseSize(x.textContent) : x.textContent.toLowerCase();
                    let yValue = columnIndex === 3 ? parseSize(y.textContent) : y.textContent.toLowerCase();

                    if (sortOrder) {
                        if (xValue > yValue) {
                            shouldSwitch = true;
                            break;
                        }
                    } else {
                        if (xValue < yValue) {
                            shouldSwitch = true;
                            break;
                        }
                    }
                }
                if (shouldSwitch) {
                    rows[i].parentNode.insertBefore(rows[i + 1], rows[i]);
                    switching = true;
                }
            }
        }

        function sortByName() {
            sortTable(0, nameSortOrder);
            nameSortOrder = !nameSortOrder;
            updateSortIcons();
        }

        function sortByDate() {
            sortTable(1, dateSortOrder);
            dateSortOrder = !dateSortOrder;
            updateSortIcons();
        }

        function sortBySize() {
            sortTable(3, sizeSortOrder);
            sizeSortOrder = !sizeSortOrder;
            updateSortIcons();
        }

        function sortByType() {
            sortTable(2, typeSortOrder);
            typeSortOrder = !typeSortOrder;
            updateSortIcons();
        }

        function updateSortIcons() {
            var nameSortIcon = document.getElementById("nameSortIcon");
            var dateSortIcon = document.getElementById("dateSortIcon");
            var sizeSortIcon = document.getElementById("sizeSortIcon");
            var typeSortIcon = document.getElementById("typeSortIcon");

            if (nameSortIcon) {
                nameSortIcon.textContent = nameSortOrder ? "▴" : "▾";
            }
            if (dateSortIcon) {
                dateSortIcon.textContent = dateSortOrder ? "▴" : "▾";
            }
            if (sizeSortIcon) {
                sizeSortIcon.textContent = sizeSortOrder ? "▴" : "▾";
            }
            if (typeSortIcon) {
                typeSortIcon.textContent = typeSortOrder ? "▴" : "▾";
            }
        }

        let nameSortOrder = true;
        let dateSortOrder = true;
        let sizeSortOrder = true;
        let typeSortOrder = true;

        updateSortIcons();

        function updateTime() {
            var now = new Date();
            var day = now.getDate();
            var month = now.getMonth() + 1;
            var year = now.getFullYear();
            var hours = now.getHours();
            var minutes = now.getMinutes();
            var seconds = now.getSeconds();

            // var days = ['Sunday', 'Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday'];
            var days = ['Minggu', 'Senin', 'Selasa', 'Rabu', 'Kamis', 'Jumat', 'Sabtu'];
            var formattedDateTime = days[now.getDay()] + " " + day + " " + getMonthName(month) + " " + year + " \n " + hours + ":" + (minutes < 10 ? "0" + minutes : minutes) + ":" + (seconds < 10 ? "0" + seconds : seconds);
            var datetimeElement = document.getElementById("datetime");
            if (datetimeElement) {
                datetimeElement.textContent = formattedDateTime;
            }
        }

        function getMonthName(month) {
            // var months = ['January', 'February', 'March', 'April', 'May', 'June', 'July', 'August', 'September', 'October', 'November', 'December'];
            var months = ['Januari', 'Februari', 'Maret', 'April', 'Mei', 'Juni', 'Juli', 'Agustus', 'September', 'Oktober', 'November', 'Desember'];
            return months[month - 1];
        }

        updateTime();
        setInterval(updateTime, 1000);

        function updatePathColor() {
            const pathColorElement = document.querySelector('.path-color');
            if (pathColorElement) {
                const isLightMode = document.body.classList.contains('light-mode');
                pathColorElement.style.color = isLightMode ? 'red' : 'yellow';
            }
        }

        window.onload = function () {
            const savedTheme = localStorage.getItem('theme');
            if (savedTheme === 'light') {
                document.body.classList.add('light-mode');
            } else {
                document.body.classList.add('dark-mode');
            }
            updatePathColor();
        };

        function toggleMode() {
            const body = document.body;
            const toggleButton = document.querySelector(".toogle");

            // Toggle kelas untuk light dan dark mode
            body.classList.toggle('light-mode');
            body.classList.toggle('dark-mode');

            // Tentukan mode saat ini dan simpan di localStorage
            const currentMode = body.classList.contains('light-mode') ? 'light' : 'dark';
            localStorage.setItem('theme', currentMode);

            // Perbarui teks tombol berdasarkan mode
            toggleButton.textContent = currentMode === 'light' ? 'Dark 🌙' : 'Light ☀️';

            updatePathColor();
        }

        // Inisialisasi tema saat halaman dimuat
        document.addEventListener("DOMContentLoaded", () => {
            const savedTheme = localStorage.getItem('theme');
            const body = document.body;
            const toggleButton = document.querySelector(".toogle");

            // Atur tema berdasarkan preferensi yang tersimpan
            if (savedTheme === 'light') {
                body.classList.add('light-mode');
                body.classList.remove('dark-mode');
                toggleButton.textContent = 'Dark 🌙';
            } else {
                body.classList.add('dark-mode');
                body.classList.remove('light-mode');
                toggleButton.textContent = 'Light ☀️';
            }
        });

        let currentFilePath = '';

function openEditor(filePath) {
    const allowedExtensions = [
    'html', 'css', 'js', 'env', 'php', 'txt', 'json', 'xml', 'env', 'gitignore', 'md',
    'yml', 'yaml', 'ini', 'conf', 'log', 'htaccess', 'htpasswd', 'csv', 'tsv', 'sql',
    'c', 'cpp', 'h', 'java', 'py', 'rb', 'sh', 'bat', 'pl', 'go', 'rs', 'swift', 'ts',
    'phtml', 'shtml', 'xhtml', 'jsp', 'asp', 'aspx', 'jspx', 'cfm', 'cfml',
    'scss', 'less', 'sass', 'vue', 'jsx', 'tsx', 'dart', 'lua', 'r', 'm', 'erl', 'hs',
    'groovy', 'kt', 'kts', 'sql', 'ps1', 'psm1', 'vbs', 'vb', 'asm', 'makefile', 'dockerfile'
    ];

    const fileExtension = filePath.split('.').pop().toLowerCase();

    if (!allowedExtensions.includes(fileExtension)) {
        alert('This file type is not allowed to be edited.');
        return;
    }

    currentFilePath = filePath;

    const editorModal = document.getElementById('textEditorModal');

    fetch(`?action=read&file=${encodeURIComponent(filePath)}`)
        .then(response => response.text())
        .then(content => {
            document.getElementById('editorContent').value = content;
            document.getElementById('textEditorModal').style.display = 'block';

            editorModal.scrollIntoView({ behavior: 'smooth', block: 'start' });
        })
        .catch(error => alert('Failed to open file: ' + error));
}

function saveFile() {
    const content = document.getElementById('editorContent').value;

    fetch(`?action=save&file=${encodeURIComponent(currentFilePath)}`, {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ content })
    })
        .then(response => response.text())
        .then(result => alert(result))
        .catch(error => alert('Failed to save file: ' + error));
}

function renameFile() {
    const newName = prompt('Enter new file name (with Extension):');
    if (!newName) return;

    fetch(`?action=rename&file=${encodeURIComponent(currentFilePath)}&newName=${encodeURIComponent(newName)}`)
        .then(response => response.text())
        .then(result => {
            alert(result);
            closeEditor();
            location.reload();
        })
        .catch(error => alert('Failed to rename file: ' + error));
}

function replaceText() {
    const searchText = prompt('Enter text to search:');
    const replaceText = prompt('Enter replacement text:');
    if (!searchText || !replaceText) return;

    const editor = document.getElementById('editorContent');
    editor.value = editor.value.split(searchText).join(replaceText);
}

function viewFile() {
    if (!currentFilePath) {
        alert('No file selected to view.');
        return;
    }
    window.open(currentFilePath, '_blank');
}

function closeEditor() {
    document.getElementById('textEditorModal').style.display = 'none';
}

function deleteFile(filePath) {
    if (confirm('Are you sure you want to delete this file/folder?')) {
        fetch(`?action=delete&file=${encodeURIComponent(filePath)}`)
            .then(response => response.text())
            .then(result => {
                alert(result);
                location.reload();
            })
            .catch(error => alert('Failed to delete file/folder: ' + error));
    }
}

document.addEventListener("DOMContentLoaded", function () {
    const fileTableBody = document.querySelector("#fileTable tbody");
    const loadingIndicator = document.getElementById("loading");

    const currentDir = window.location.search ? new URLSearchParams(window.location.search).get('dir') : './';

    fetch(`?action=listFiles&dir=${encodeURIComponent(currentDir)}`)
        .then(response => response.json())
        .then(files => {
            loadingIndicator.style.display = "none";
            files.forEach(file => {
                const row = document.createElement("tr");
                // row.innerHTML = `
                //     <td>${file.name}</td>
                //     <td>${file.date}</td>
                //     <td>${file.type}</td>
                //     <td>${file.size}</td>
                // `;
                console.log(fileTableBody.appendChild(row));
            });
        })
        .catch(error => console.error("Failed to load files:", error));
});

        document.addEventListener("DOMContentLoaded", function () {

            document.querySelectorAll(".bookmark-btn").forEach(button => {
        button.addEventListener("click", (event) => {
            event.stopPropagation(); // Mencegah event click pada elemen induk
            const folderPath = button.getAttribute("data-path");
            bookmarkFolder(folderPath);
        });
    });

    // Event listener untuk tombol Delete
    document.querySelectorAll(".delete-btn").forEach(button => {
        button.addEventListener("click", (event) => {
            event.stopPropagation(); // Mencegah event click pada elemen induk
            const filePath = button.getAttribute("data-path");
            deleteFile(filePath);
        });
    });


            document.getElementById("loading").style.display = "none";
            document.querySelector(".container").style.display = "block";

            const rows = document.querySelectorAll("#fileTable tbody tr");
            rows.forEach(row => {
                const link = row.querySelector("a");
                if (link) {
                    row.style.cursor = "pointer";
                    row.addEventListener("click", () => {
                        window.location.href = link.href;
                    });
                }
                const editButton = row.querySelector("button[onclick^='openEditor']");
                if (editButton) {
                    editButton.addEventListener("click", (event) => {
                        event.stopPropagation();
                    });
                }
            });
        });
        
    </script>
</head>

<body>
    <div id="loading">
        <div class="loader"></div>
        <h1>Loading...</h1>
    </div>
    <div class="container">
        <h2 style="margin-bottom: 20px; text-align: center;">
            <a href="index.php" style="text-decoration: none;">
                <span class="path-color">File Manager (htdocs)</span>
            </a>
        </h2>
        <header>
            <div class="info">
                <p id="datetime"></p>
            </div>
            <div class="button">
                <button onclick="toggleMode()" class="toogle" type="button">Toggle Light/Dark Mode</button>
                <button onclick="goBack()" class="toogle" type="button">Back</button>
                <a href="?logout=true" style="background-color: #dc3545; color: #fff; padding: 8px 16px; text-decoration: none; border-radius: 4px;">Logout</a>
            </div>
        </header>

        <div class="search-container">
            <input type="text" id="searchInput" onkeyup="searchFiles()" placeholder="Search for files...">
        </div>
        <br />
        <div id="dropZone" style="border: 2px dashed #007bff; padding: 20px; text-align: center; color: #007bff; margin-bottom: 20px;">
            Drag and drop files here to upload
            <form id="uploadForm" method="POST" enctype="multipart/form-data">
                <input type="file" id="fileInput" name="uploadedFiles[]" multiple style="display: none;">
                <button type="button" id="browseButton" style="margin-top: 10px; background-color: #007bff; color: #fff; padding: 8px 16px; border: none; border-radius: 4px; cursor: pointer;">Browse Files</button>
                <button type="submit" style="margin-top: 10px; background-color: #28a745; color: #fff; padding: 8px 16px; border: none; border-radius: 4px; cursor: pointer;">Upload</button>
            </form>
        </div>
        <div class="bookmarks">
            <h3>Bookmarks</h3>
            <ul id="bookmarkList"></ul>
        </div>
        <table class="file-table" id="fileTable">
            <thead>
                <tr>
                    <th onclick="sortByName()">Name <span id="nameSortIcon" class="sort-icon">▴</span></th>
                    <th onclick="sortByDate()">Date modified <span id="dateSortIcon" class="sort-icon">▴</span></th>
                    <th onclick="sortByType()">Type <span id="typeSortIcon" class="sort-icon">▴</span></th>
                    <th onclick="sortBySize()">Size <span id="sizeSortIcon" class="sort-icon">▴</span></th>
                </tr>
            </thead>
            <tbody>
                <?php
                function humanFileSize($size, $unit = "")
                {
                    if ((!$unit && $size >= 1 << 30) || $unit == "GB")
                        return number_format($size / (1 << 30), 2) . " GB";
                    if ((!$unit && $size >= 1 << 20) || $unit == "MB")
                        return number_format($size / (1 << 20), 2) . " MB";
                    if ((!$unit && $size >= 1 << 10) || $unit == "KB")
                        return number_format($size / (1 << 10), 2) . " KB";
                    return number_format($size) . " bytes";
                }

                function getFolderSize($dir)
                {
                    static $cache = [];
                    if (isset($cache[$dir])) {
                        return $cache[$dir];
                    }
                
                    $totalSize = 0;
                    foreach (new RecursiveIteratorIterator(new RecursiveDirectoryIterator($dir, FilesystemIterator::SKIP_DOTS)) as $file) {
                        if ($file->isFile()) {
                            $totalSize += $file->getSize();
                        }
                    }
                
                    $cache[$dir] = $totalSize;
                    return $totalSize;
                }

                $currentDir = isset($_GET['dir']) ? $_GET['dir'] : './';

                // Periksa apakah $currentDir adalah direktori
                if (is_dir($currentDir)) {
                    $files = array_diff(scandir($currentDir), array('.', '..'));
                } elseif (is_file($currentDir)) {
                    // Jika $currentDir adalah file, buka file tersebut
                    header('Content-Type: ' . mime_content_type($currentDir));
                    header('Content-Disposition: inline; filename="' . basename($currentDir) . '"');
                    readfile($currentDir);
                    exit;
                } else {
                    // Jika $currentDir tidak valid, tampilkan pesan error
                    die("Invalid directory or file.");
                }
                
                $files = array_diff(scandir($currentDir), array('.', '..'));

                $filesPerPage = 15; // atur page disini mau muncul berapa
                $page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
                $totalFiles = count($files);
                $totalPages = ceil($totalFiles / $filesPerPage);
                $startIndex = ($page - 1) * $filesPerPage;
                $files = array_slice($files, $startIndex, $filesPerPage);
                
                foreach ($files as $file) {
                    $filePath = $currentDir . '/' . $file;
                    $fileSize = is_dir($filePath) ? humanFileSize(getFolderSize($filePath)) : humanFileSize(filesize($filePath));
                    $fileDate = date("F d Y H:i:s.", filemtime($filePath));
                    $fileType = filetype($filePath);
                
                    echo "<tr>";
                    if (is_dir($filePath)) {
                        echo "<td class='folder-icon'><a href='?dir=" . urlencode($filePath) . "'>$file</a></td>";
                        echo "<td>$fileDate</td>";
                        echo "<td>Folder</td>";
                        echo "<td class='grey-text'>$fileSize</td>";
                        echo "<td><button class='bookmark-btn' data-path='" . htmlspecialchars($filePath, ENT_QUOTES, 'UTF-8') . "' style='background-color: #ffc107; color: #000; padding: 5px 10px; border: none; border-radius: 4px; cursor: pointer;'>Bookmark</button></td>";
                        echo "<td><button class='delete-btn' data-path='" . htmlspecialchars($filePath, ENT_QUOTES, 'UTF-8') . "' style='background-color: #ff4d4d; color: #fff; padding: 5px 10px; border: none; border-radius: 4px; cursor: pointer;'>Delete</button></td>";
                    } else {
                        echo "<td class='file-icon'><a href='" . htmlspecialchars($filePath, ENT_QUOTES, 'UTF-8') . "' target='_blank'>" . htmlspecialchars($file, ENT_QUOTES, 'UTF-8') . "</a></td>";
                        echo "<td>$fileDate</td>";
                        echo "<td>$fileType</td>";
                        echo "<td>$fileSize</td>";
                        echo "<td><button class='bookmark-btn' data-path='" . htmlspecialchars($filePath, ENT_QUOTES, 'UTF-8') . "' style='background-color: #ffc107; color: #000; padding: 5px 10px; border: none; border-radius: 4px; cursor: pointer;'>Bookmark</button></td>";
                        echo "<td><button class='delete-btn' data-path='" . htmlspecialchars($filePath, ENT_QUOTES, 'UTF-8') . "' style='background-color: #ff4d4d; color: #fff; padding: 5px 10px; border: none; border-radius: 4px; cursor: pointer;'>Delete</button></td>";
                                    
                        if (isAllowedFile($filePath, $allowedExtensions)) {
                            echo "<td><button onclick=\"openEditor('" . htmlspecialchars($filePath, ENT_QUOTES, 'UTF-8') . "')\" style='background-color: #007bff; color: #fff; padding: 5px 10px; border: none; border-radius: 4px; cursor: pointer;'>Edit</button></td>";
                        } else {
                            echo "<td></td>";
                        }
                    }
                    echo "</tr>";
                }
                ?>
                <?php echo "<a href='activity.log' target='_blank' style='background-color: #007bff; color: #fff; padding: 5px 10px; border: none; border-radius: 4px; text-decoration: none;'>View Log</a>"; ?>
                <br />
                
                <div class="pagination" style="text-align: center; margin-top: 20px;">
                    <?php for ($i = 1; $i <= $totalPages; $i++): ?>
                        <a href="?dir=<?php echo urlencode($currentDir); ?>&page=<?php echo $i; ?>" 
                           style="margin: 0 5px; text-decoration: none; color: <?php echo $i === $page ? '#fcd53f' : '#007bff'; ?>;">
                           <?php echo $i; ?>
                        </a>
                    <?php endfor; ?>
                </div>
            </tbody>
        </table>
    </div>
    
    <div id="textEditorModal" style="display: none;">
    <div style="background-color: #252526; padding: 20px; border-radius: 8px; width: 80%; margin: 50px auto; color: #d4d4d4;">
        <h2>Text Editor</h2>
        <textarea id="editorContent" style="width: 100%; height: 300px; background-color: #1e1e1e; color: #d4d4d4; border: 1px solid #444; padding: 10px; border-radius: 4px; font-family: 'Courier New', Courier, monospace; resize: none; box-sizing: border-box;"></textarea>
        <div style="margin-top: 10px; display: flex; justify-content: space-between;">
            <button onclick="saveFile()" style="background-color: #007bff; color: #fff; padding: 10px 20px; border: none; border-radius: 4px; cursor: pointer;">Save</button>
            <button onclick="renameFile()" style="background-color: #fcd53f; color: #000; padding: 10px 20px; border: none; border-radius: 4px; cursor: pointer;">Rename</button>
            <button onclick="replaceText()" style="background-color: #ff5722; color: #fff; padding: 10px 20px; border: none; border-radius: 4px; cursor: pointer;">Replace</button>
            <button onclick="viewFile()" style="background-color: #28a745; color: #fff; padding: 10px 20px; border: none; border-radius: 4px; cursor: pointer;">View</button>
            <button onclick="closeEditor()" style="background-color: #444; color: #fff; padding: 10px 20px; border: none; border-radius: 4px; cursor: pointer;">Exit</button>
        </div>
    </div>
</div>

    <footer class="footer">
        <p>&copy; <?php echo htmlspecialchars(date('Y'), ENT_QUOTES, 'UTF-8'); ?> <?php echo htmlspecialchars(gethostname(), ENT_QUOTES, 'UTF-8'); ?>. All rights reserved.</p>
        <a href="https://github.com/lukman754/apache-autoindex-theme" target="_blank" rel="noopener noreferrer">
            Created by <span class="github-icon"><i class="fab fa-github"></i></span> Lukman754 & <span
                class="github-icon"><i class="fab fa-github"></i></span> Xnuvers007
        </a>
    </footer>
    <script>
function bookmarkFolder(folderPath) {
    let bookmarks = JSON.parse(localStorage.getItem('bookmarks')) || [];
    if (bookmarks.includes(folderPath)) {
        // Jika folder sudah di-bookmark, hapus dari daftar
        bookmarks = bookmarks.filter(bookmark => bookmark !== folderPath);
        localStorage.setItem('bookmarks', JSON.stringify(bookmarks));
        alert('Bookmark removed!');
    } else {
        // Jika folder belum di-bookmark, tambahkan ke daftar
        bookmarks.push(folderPath);
        localStorage.setItem('bookmarks', JSON.stringify(bookmarks));
        alert('Folder/File bookmarked!');
    }
    updateBookmarkList(); // Perbarui daftar bookmark
}

function updateBookmarkList() {
    const bookmarks = JSON.parse(localStorage.getItem('bookmarks')) || [];
    const bookmarkList = document.getElementById('bookmarkList');
    bookmarkList.innerHTML = ''; // Kosongkan daftar bookmark

    bookmarks.forEach(folder => {
        const li = document.createElement('li');
        li.innerHTML = `
            <a href="?dir=${encodeURIComponent(folder)}">${folder}</a>
            <button onclick="bookmarkFolder('${folder}')" style="margin-left: 10px; background-color: #ff4d4d; color: #fff; border: none; border-radius: 4px; padding: 5px 10px; cursor: pointer;">Remove</button>
        `;
        bookmarkList.appendChild(li);
    });
}

document.addEventListener("DOMContentLoaded", function () {
    updateBookmarkList();
    const bookmarks = JSON.parse(localStorage.getItem('bookmarks')) || [];
    const bookmarkList = document.getElementById('bookmarkList');
    bookmarks.forEach(folder => {
        const li = document.createElement('li');
        li.innerHTML = `<a href="?dir=${encodeURIComponent(folder)}">${folder}</a>`;
        bookmarkList.appendChild(li);
    });
});

document.addEventListener("DOMContentLoaded", () => {
    const dropZone = document.getElementById('dropZone');
    const fileInput = document.getElementById('fileInput');
    const uploadForm = document.getElementById('uploadForm');
    const browseButton = document.getElementById('browseButton');

    dropZone.addEventListener('dragover', (event) => {
        event.preventDefault();
        dropZone.style.backgroundColor = '#e0e0e0';
    });

    dropZone.addEventListener('dragleave', () => {
        dropZone.style.backgroundColor = '';
    });

    dropZone.addEventListener('drop', (event) => {
        event.preventDefault();
        dropZone.style.backgroundColor = '';

        const files = event.dataTransfer.files;
        fileInput.files = files;

        uploadForm.submit();
    });

    browseButton.addEventListener('click', () => {
        fileInput.click();
    });

    fileInput.addEventListener('change', () => {
        uploadForm.submit();
    });
});
</script>
</body>
</html>

<?php
ob_end_flush();
?>