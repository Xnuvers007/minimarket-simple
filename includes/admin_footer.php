    <!-- PWA Service Worker Registration -->
    <script>
        if ('serviceWorker' in navigator) {
            window.addEventListener('load', () => {
                const baseUrl = '<?php echo $base_url ?? getBaseUrl(); ?>';
                navigator.serviceWorker.register(baseUrl + 'service-worker.js')
                    .then(registration => {
                        console.log('[PWA] Service Worker registered:', registration.scope);
                    })
                    .catch(error => {
                        console.log('[PWA] Service Worker registration failed:', error);
                    });
            });
        }
    </script>
    
    <!-- PWA Install Script -->
    <script src="<?php echo $base_url ?? getBaseUrl(); ?>pwa-install.js"></script></script>
    
    <!-- Custom Scripts -->
    <?php if (isset($custom_scripts) && !empty($custom_scripts)): ?>
        <?php foreach ($custom_scripts as $script): ?>
            <script src="<?php echo $script; ?>"></script>
        <?php endforeach; ?>
    <?php endif; ?>
</body>
</html>
<?php
// Helper function untuk get base URL (jika belum didefinisikan)
if (!function_exists('getBaseUrl')) {
    function getBaseUrl() {
        $protocol = isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? "https" : "http";
        $host = $_SERVER['HTTP_HOST'];
        $script_path = dirname($_SERVER['SCRIPT_NAME']);
        
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
}
?>
