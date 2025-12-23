<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Helper function untuk get root URL dari subfolder
function getBaseUrl() {
    $protocol = isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? "https" : "http";
    $host = $_SERVER['HTTP_HOST'];
    
    // Detect root folder (sera)
    $script_path = dirname($_SERVER['SCRIPT_NAME']);
    
    // Remove subfolder (admin, kasir, customer)
    if (strpos($script_path, '/admin') !== false) {
        $script_path = str_replace('/admin', '', $script_path);
    } elseif (strpos($script_path, '/kasir') !== false) {
        $script_path = str_replace('/kasir', '', $script_path);
    } elseif (strpos($script_path, '/customer') !== false) {
        $script_path = str_replace('/customer', '', $script_path);
    }
    
    $base_url = $protocol . "://" . $host . $script_path;
    return rtrim($base_url, '/') . '/';
}

$base_url = getBaseUrl();
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo isset($page_title) ? $page_title . ' - ' : ''; ?><?php echo defined('SITE_NAME') ? SITE_NAME : 'Minimarket'; ?></title>
    
    <!-- PWA Meta Tags -->
    <meta name="theme-color" content="#667eea">
    <meta name="apple-mobile-web-app-capable" content="yes">
    <meta name="mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-status-bar-style" content="black-translucent">
    <meta name="apple-mobile-web-app-title" content="Minimarket">
    <meta name="description" content="Sistem Informasi Minimarket Sejahtera">
    
    <!-- PWA Manifest -->
    <link rel="manifest" href="<?php echo $base_url; ?>manifest.json">
    
    <!-- Apple Touch Icons -->
    <link rel="apple-touch-icon" sizes="72x72" href="<?php echo $base_url; ?>assets/images/icon-72x72.png">
    <link rel="apple-touch-icon" sizes="96x96" href="<?php echo $base_url; ?>assets/images/icon-96x96.png">
    <link rel="apple-touch-icon" sizes="128x128" href="<?php echo $base_url; ?>assets/images/icon-128x128.png">
    <link rel="apple-touch-icon" sizes="144x144" href="<?php echo $base_url; ?>assets/images/icon-144x144.png">
    <link rel="apple-touch-icon" sizes="152x152" href="<?php echo $base_url; ?>assets/images/icon-152x152.png">
    <link rel="apple-touch-icon" sizes="192x192" href="<?php echo $base_url; ?>assets/images/icon-192x192.png">
    
    <!-- Favicon -->
    <link rel="icon" type="image/png" sizes="32x32" href="<?php echo $base_url; ?>assets/images/icon-72x72.png">
    
    <!-- Font Awesome CDN -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" crossorigin="anonymous">
    
    <!-- Chart.js (if needed) -->
    <?php if (isset($use_charts) && $use_charts): ?>
    <script src="<?php echo $base_url; ?>assets/js/chart.js"></script>
    <?php endif; ?>
