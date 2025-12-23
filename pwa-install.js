<<<<<<< HEAD
// PWA Installation Script
let deferredPrompt;
let installButton;

// Detect if app is already installed
function isAppInstalled() {
    // Check if running in standalone mode
    if (window.matchMedia('(display-mode: standalone)').matches) {
        return true;
    }
    
    // Check if running in fullscreen mode
    if (window.navigator.standalone === true) {
        return true;
    }
    
    return false;
}

// Create install button
function createInstallButton() {
    if (isAppInstalled()) {
        console.log('[PWA] App already installed');
        return;
    }
    
    // Create floating install button
    installButton = document.createElement('button');
    installButton.id = 'pwa-install-btn';
    installButton.innerHTML = `
        <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
            <path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"></path>
            <polyline points="7 10 12 15 17 10"></polyline>
            <line x1="12" y1="15" x2="12" y2="3"></line>
        </svg>
        <span>Install App</span>
    `;
    installButton.style.cssText = `
        position: fixed;
        bottom: 20px;
        right: 20px;
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        color: white;
        border: none;
        padding: 12px 24px;
        border-radius: 50px;
        font-size: 14px;
        font-weight: 600;
        cursor: pointer;
        box-shadow: 0 4px 15px rgba(102, 126, 234, 0.4);
        display: none;
        align-items: center;
        gap: 8px;
        z-index: 9999;
        transition: all 0.3s;
    `;
    
    installButton.addEventListener('mouseenter', () => {
        installButton.style.transform = 'translateY(-2px)';
        installButton.style.boxShadow = '0 6px 20px rgba(102, 126, 234, 0.5)';
    });
    
    installButton.addEventListener('mouseleave', () => {
        installButton.style.transform = 'translateY(0)';
        installButton.style.boxShadow = '0 4px 15px rgba(102, 126, 234, 0.4)';
    });
    
    installButton.addEventListener('click', installApp);
    
    document.body.appendChild(installButton);
}

// Show install button
function showInstallButton() {
    if (installButton) {
        installButton.style.display = 'flex';
        
        // Add animation
        setTimeout(() => {
            installButton.style.animation = 'slideInUp 0.3s ease-out';
        }, 100);
    }
}

// Hide install button
function hideInstallButton() {
    if (installButton) {
        installButton.style.display = 'none';
    }
}

// Install app
async function installApp() {
    if (!deferredPrompt) {
        console.log('[PWA] No installation prompt available');
        return;
    }
    
    // Show the install prompt
    deferredPrompt.prompt();
    
    // Wait for the user's response
    const { outcome } = await deferredPrompt.userChoice;
    
    console.log(`[PWA] User response: ${outcome}`);
    
    if (outcome === 'accepted') {
        // User accepted the install
        hideInstallButton();
        showToast('✅ App berhasil diinstall!', 'success');
    } else {
        // User dismissed the install
        showToast('ℹ️ Install dibatalkan', 'info');
    }
    
    // Clear the deferred prompt
    deferredPrompt = null;
}

// Show toast notification
function showToast(message, type = 'info') {
    const toast = document.createElement('div');
    toast.textContent = message;
    toast.style.cssText = `
        position: fixed;
        top: 20px;
        right: 20px;
        background: ${type === 'success' ? '#4caf50' : type === 'error' ? '#f44336' : '#2196f3'};
        color: white;
        padding: 16px 24px;
        border-radius: 8px;
        box-shadow: 0 4px 12px rgba(0,0,0,0.15);
        z-index: 10000;
        font-size: 14px;
        font-weight: 500;
        animation: slideInDown 0.3s ease-out;
    `;
    
    document.body.appendChild(toast);
    
    setTimeout(() => {
        toast.style.animation = 'fadeOut 0.3s ease-out';
        setTimeout(() => toast.remove(), 300);
    }, 3000);
}

// Listen for beforeinstallprompt event
window.addEventListener('beforeinstallprompt', (e) => {
    console.log('[PWA] beforeinstallprompt event fired');
    
    // Prevent the mini-infobar from appearing
    e.preventDefault();
    
    // Save the event for later use
    deferredPrompt = e;
    
    // Show install button
    showInstallButton();
});

// Listen for appinstalled event
window.addEventListener('appinstalled', () => {
    console.log('[PWA] App installed successfully');
    hideInstallButton();
    showToast('🎉 App berhasil diinstall! Anda bisa mengaksesnya dari home screen.', 'success');
    
    // Clear the deferred prompt
    deferredPrompt = null;
});

// Check if app is installed on load
window.addEventListener('load', () => {
    createInstallButton();
    
    if (isAppInstalled()) {
        console.log('[PWA] Running in standalone mode');
        hideInstallButton();
    }
});

// Add CSS animations
const style = document.createElement('style');
style.textContent = `
    @keyframes slideInUp {
        from {
            transform: translateY(100px);
            opacity: 0;
        }
        to {
            transform: translateY(0);
            opacity: 1;
        }
    }
    
    @keyframes slideInDown {
        from {
            transform: translateY(-100px);
            opacity: 0;
        }
        to {
            transform: translateY(0);
            opacity: 1;
        }
    }
    
    @keyframes fadeOut {
        from {
            opacity: 1;
        }
        to {
            opacity: 0;
        }
    }
`;
document.head.appendChild(style);

// Network status monitoring
window.addEventListener('online', () => {
    showToast('🌐 Koneksi kembali online', 'success');
});

window.addEventListener('offline', () => {
    showToast('📡 Anda sedang offline. Beberapa fitur mungkin terbatas.', 'info');
});

// Check initial network status
if (!navigator.onLine) {
    window.addEventListener('load', () => {
        showToast('📡 Anda sedang offline', 'info');
    });
}
=======
// PWA Installation Script
let deferredPrompt;
let installButton;

// Detect if app is already installed
function isAppInstalled() {
    // Check if running in standalone mode
    if (window.matchMedia('(display-mode: standalone)').matches) {
        return true;
    }
    
    // Check if running in fullscreen mode
    if (window.navigator.standalone === true) {
        return true;
    }
    
    return false;
}

// Create install button
function createInstallButton() {
    if (isAppInstalled()) {
        console.log('[PWA] App already installed');
        return;
    }
    
    // Create floating install button
    installButton = document.createElement('button');
    installButton.id = 'pwa-install-btn';
    installButton.innerHTML = `
        <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
            <path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"></path>
            <polyline points="7 10 12 15 17 10"></polyline>
            <line x1="12" y1="15" x2="12" y2="3"></line>
        </svg>
        <span>Install App</span>
    `;
    installButton.style.cssText = `
        position: fixed;
        bottom: 20px;
        right: 20px;
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        color: white;
        border: none;
        padding: 12px 24px;
        border-radius: 50px;
        font-size: 14px;
        font-weight: 600;
        cursor: pointer;
        box-shadow: 0 4px 15px rgba(102, 126, 234, 0.4);
        display: none;
        align-items: center;
        gap: 8px;
        z-index: 9999;
        transition: all 0.3s;
    `;
    
    installButton.addEventListener('mouseenter', () => {
        installButton.style.transform = 'translateY(-2px)';
        installButton.style.boxShadow = '0 6px 20px rgba(102, 126, 234, 0.5)';
    });
    
    installButton.addEventListener('mouseleave', () => {
        installButton.style.transform = 'translateY(0)';
        installButton.style.boxShadow = '0 4px 15px rgba(102, 126, 234, 0.4)';
    });
    
    installButton.addEventListener('click', installApp);
    
    document.body.appendChild(installButton);
}

// Show install button
function showInstallButton() {
    if (installButton) {
        installButton.style.display = 'flex';
        
        // Add animation
        setTimeout(() => {
            installButton.style.animation = 'slideInUp 0.3s ease-out';
        }, 100);
    }
}

// Hide install button
function hideInstallButton() {
    if (installButton) {
        installButton.style.display = 'none';
    }
}

// Install app
async function installApp() {
    if (!deferredPrompt) {
        console.log('[PWA] No installation prompt available');
        return;
    }
    
    // Show the install prompt
    deferredPrompt.prompt();
    
    // Wait for the user's response
    const { outcome } = await deferredPrompt.userChoice;
    
    console.log(`[PWA] User response: ${outcome}`);
    
    if (outcome === 'accepted') {
        // User accepted the install
        hideInstallButton();
        showToast('✅ App berhasil diinstall!', 'success');
    } else {
        // User dismissed the install
        showToast('ℹ️ Install dibatalkan', 'info');
    }
    
    // Clear the deferred prompt
    deferredPrompt = null;
}

// Show toast notification
function showToast(message, type = 'info') {
    const toast = document.createElement('div');
    toast.textContent = message;
    toast.style.cssText = `
        position: fixed;
        top: 20px;
        right: 20px;
        background: ${type === 'success' ? '#4caf50' : type === 'error' ? '#f44336' : '#2196f3'};
        color: white;
        padding: 16px 24px;
        border-radius: 8px;
        box-shadow: 0 4px 12px rgba(0,0,0,0.15);
        z-index: 10000;
        font-size: 14px;
        font-weight: 500;
        animation: slideInDown 0.3s ease-out;
    `;
    
    document.body.appendChild(toast);
    
    setTimeout(() => {
        toast.style.animation = 'fadeOut 0.3s ease-out';
        setTimeout(() => toast.remove(), 300);
    }, 3000);
}

// Listen for beforeinstallprompt event
window.addEventListener('beforeinstallprompt', (e) => {
    console.log('[PWA] beforeinstallprompt event fired');
    
    // Prevent the mini-infobar from appearing
    e.preventDefault();
    
    // Save the event for later use
    deferredPrompt = e;
    
    // Show install button
    showInstallButton();
});

// Listen for appinstalled event
window.addEventListener('appinstalled', () => {
    console.log('[PWA] App installed successfully');
    hideInstallButton();
    showToast('🎉 App berhasil diinstall! Anda bisa mengaksesnya dari home screen.', 'success');
    
    // Clear the deferred prompt
    deferredPrompt = null;
});

// Check if app is installed on load
window.addEventListener('load', () => {
    createInstallButton();
    
    if (isAppInstalled()) {
        console.log('[PWA] Running in standalone mode');
        hideInstallButton();
    }
});

// Add CSS animations
const style = document.createElement('style');
style.textContent = `
    @keyframes slideInUp {
        from {
            transform: translateY(100px);
            opacity: 0;
        }
        to {
            transform: translateY(0);
            opacity: 1;
        }
    }
    
    @keyframes slideInDown {
        from {
            transform: translateY(-100px);
            opacity: 0;
        }
        to {
            transform: translateY(0);
            opacity: 1;
        }
    }
    
    @keyframes fadeOut {
        from {
            opacity: 1;
        }
        to {
            opacity: 0;
        }
    }
`;
document.head.appendChild(style);

// Network status monitoring
window.addEventListener('online', () => {
    showToast('🌐 Koneksi kembali online', 'success');
});

window.addEventListener('offline', () => {
    showToast('📡 Anda sedang offline. Beberapa fitur mungkin terbatas.', 'info');
});

// Check initial network status
if (!navigator.onLine) {
    window.addEventListener('load', () => {
        showToast('📡 Anda sedang offline', 'info');
    });
}
>>>>>>> 882da412c224f7c20dfb67829049d92fbad8991f
