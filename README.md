# 🛒 Minimarket POS System

<div align="center">

![PHP](https://img.shields.io/badge/PHP-777BB4?style=for-the-badge&logo=php&logoColor=white)
![MySQL](https://img.shields.io/badge/MySQL-4479A1?style=for-the-badge&logo=mysql&logoColor=white)
![Bootstrap](https://img.shields.io/badge/Bootstrap-7952B3?style=for-the-badge&logo=bootstrap&logoColor=white)
![PWA](https://img.shields.io/badge/PWA-5A0FC8?style=for-the-badge&logo=pwa&logoColor=white)
![License](https://img.shields.io/badge/License-MIT-green.svg?style=for-the-badge)

**Modern Point of Sale System dengan Progressive Web App Support**

[Features](#-features) • [Demo](#-demo) • [Installation](#-installation) • [Usage](#-usage) • [Documentation](#-documentation)

</div>

---

## 📖 Tentang Project

Minimarket POS System adalah aplikasi Point of Sale modern berbasis web yang dirancang khusus untuk minimarket, toko retail, dan bisnis sejenis. Dilengkapi dengan teknologi PWA (Progressive Web App) yang memungkinkan aplikasi berjalan secara offline dan dapat diinstall seperti aplikasi native.

### 🎯 Keunggulan

- ✅ **100% Offline Capable** - Tetap bisa transaksi tanpa internet
- ✅ **Modern UI/UX** - Interface yang clean dan user-friendly
- ✅ **Multi-Role System** - Admin, Kasir, dan Customer
- ✅ **Real-time Reporting** - Dashboard analytics dengan chart interaktif
- ✅ **Mobile Responsive** - Sempurna di semua device
- ✅ **Fast & Lightweight** - Performance optimal

---

## ✨ Features

### 👨‍💼 Admin Panel
- 📊 **Dashboard Analytics** dengan grafik penjualan real-time
- 👥 **User Management** - Kelola admin, kasir, dan customer
- 📦 **Product Management** - CRUD produk dengan kategori
- 🏷️ **Category Management** - Organisasi produk yang terstruktur
- 🚚 **Supplier Management** - Data supplier dan pembelian
- 📈 **Reports & Analytics** - Laporan lengkap (harian, mingguan, bulanan)
- ⚙️ **Settings** - Konfigurasi sistem dan toko

### 💰 Kasir/POS System
- 🛍️ **Quick POS Interface** - Transaksi cepat dengan barcode scanner
- 🧾 **Print Receipt** - Cetak struk otomatis
- 💳 **Multiple Payment Methods** - Cash, Debit, Credit, QRIS, E-Wallet
- 📱 **Offline Transaction** - Auto-sync saat online kembali
- 🔍 **Product Search** - Pencarian produk cepat
- 📋 **Transaction History** - Riwayat transaksi lengkap
- 💵 **Cash Management** - Kelola kas harian

### 🛒 Customer Portal
- 🏪 **Online Shop** - Belanja online dengan cart system
- 📦 **Order Tracking** - Pantau status pesanan
- 👤 **Profile Management** - Kelola data pribadi
- 🧾 **Order History** - Riwayat pembelian
- 🛍️ **Wishlist** - Simpan produk favorit

### 🚀 PWA Features
- 📱 **Install to Home Screen** - Install seperti app native
- 🔌 **Offline Support** - Bekerja tanpa koneksi internet
- 🔄 **Background Sync** - Auto-sync data saat online
- 🔔 **Push Notifications** - Notifikasi real-time
- ⚡ **Service Worker** - Caching pintar untuk performa maksimal
- 📊 **IndexedDB Storage** - Penyimpanan lokal untuk data offline

---

## 🛠️ Tech Stack

| Technology | Purpose |
|------------|---------|
| **PHP 7.4+** | Backend scripting |
| **MySQL 5.7+** | Database management |
| **Bootstrap 5** | UI framework |
| **jQuery 3.6** | JavaScript library |
| **Chart.js** | Data visualization |
| **Service Worker** | PWA & offline support |
| **IndexedDB** | Client-side storage |
| **Font Awesome** | Icon library |

---

## 📋 Prerequisites

Sebelum instalasi, pastikan sistem Anda memiliki:

- ✅ PHP >= 7.4 (dengan extension mysqli, json, mbstring)
- ✅ MySQL >= 5.7 atau MariaDB >= 10.2
- ✅ Apache/Nginx Web Server
- ✅ Composer (optional, untuk dependency management)
- ✅ Modern Browser (Chrome, Firefox, Edge, Safari)

**Recommended:**
- XAMPP 8.0+ atau WAMPP (untuk Windows)
- MAMP (untuk macOS)
- LAMP Stack (untuk Linux)

---

## 🚀 Installation

### Method 1: Quick Installation

1. **Clone Repository**
   ```bash
   git clone https://github.com/yourusername/minimarket-pos.git
   cd minimarket-pos
   ```

2. **Setup Database**
   ```bash
   # Buat database baru
   mysql -u root -p
   CREATE DATABASE minimarket_db;
   exit;
   
   # Import database
   mysql -u root -p minimarket_db < database/database.sql
   ```

3. **Configure Database**
   
   Edit file `config.php`:
   ```php
   define('DB_HOST', 'localhost');
   define('DB_USER', 'root');
   define('DB_PASS', ''); // password database Anda
   define('DB_NAME', 'minimarket_db');
   ```

4. **Setup Permissions**
   ```bash
   # Linux/Mac
   chmod -R 755 assets/images/products/
   
   # Windows (run as admin)
   icacls assets\images\products\ /grant Users:F /T
   ```

5. **Access Application**
   
   Buka browser dan akses:
   ```
   http://localhost/minimarket-pos
   ```

### Method 2: Using XAMPP

1. Download dan extract project ke `C:\xampp\htdocs\minimarket`
2. Start Apache dan MySQL di XAMPP Control Panel
3. Import database via phpMyAdmin (http://localhost/phpmyadmin)
4. Edit `config.php` sesuai setting database
5. Akses http://localhost/minimarket

---

## 🔐 Default Login Credentials

### Admin Account
```
Username: admin
Password: admin123
```

### Kasir Account
```
Username: kasir
Password: kasir123
```

### Customer Account
```
Username: customer
Password: customer123
```

⚠️ **PENTING:** Segera ubah password default setelah login pertama!

---

## 📱 PWA Installation

### Desktop (Chrome/Edge)
1. Buka aplikasi di browser
2. Klik icon **Install** di address bar
3. Atau klik menu ⋮ → "Install Minimarket POS"

### Mobile (Android/iOS)
1. Buka aplikasi di browser
2. Tap menu ⋮ (Android) atau Share button (iOS)
3. Pilih **"Add to Home Screen"**
4. Aplikasi akan muncul di home screen

---

## 📖 Usage Guide

### Untuk Admin

1. **Login** sebagai admin
2. **Dashboard** - Lihat overview penjualan dan statistik
3. **Products** - Tambah/edit/hapus produk
4. **Categories** - Kelola kategori produk
5. **Users** - Manage user dan role
6. **Reports** - Generate laporan penjualan

### Untuk Kasir

1. **Login** sebagai kasir
2. **POS** - Scan/pilih produk untuk transaksi
3. **Add to Cart** - Masukkan jumlah dan tambah ke keranjang
4. **Process Payment** - Pilih metode pembayaran
5. **Print Receipt** - Cetak struk pembayaran

### Untuk Customer

1. **Register/Login** sebagai customer
2. **Browse Products** - Lihat katalog produk
3. **Add to Cart** - Tambahkan produk ke keranjang
4. **Checkout** - Proses pembelian
5. **Track Order** - Pantau status pesanan

---

## 🔧 Configuration

### PWA Configuration

Edit `manifest.json` untuk customize PWA:
```json
{
  "name": "Minimarket POS System",
  "short_name": "MiniPOS",
  "theme_color": "#4e73df",
  "background_color": "#ffffff"
}
```

### Service Worker Configuration

Edit `service-worker.js` untuk cache strategy:
```javascript
const CACHE_NAME = 'minimarket-v2';
const CACHE_VERSION = '2.0.0';
```

### Database Configuration

Edit `config.php`:
```php
define('SITE_NAME', 'Minimarket Anda');
define('SITE_URL', 'http://localhost/minimarket');
define('TIMEZONE', 'Asia/Jakarta');
```

---

## 🧪 Testing

Folder `testing/` berisi tools untuk testing:

- `test_connection.php` - Test koneksi database
- `check_data.php` - Cek integritas data
- `check_syntax.php` - Validasi syntax PHP
- `icon-generator.html` - Generate PWA icons

Akses: `http://localhost/minimarket/testing/`

---

## 📊 Database Structure

### Main Tables

- **users** - Data pengguna (admin, kasir, customer)
- **products** - Data produk
- **categories** - Kategori produk
- **suppliers** - Data supplier
- **transactions** - Transaksi penjualan
- **transaction_items** - Detail item transaksi
- **orders** - Pesanan online
- **order_items** - Detail item pesanan

Full schema tersedia di `database/database.sql`

---

## 🤝 Contributing

Kontribusi sangat diterima! Berikut cara berkontribusi:

1. Fork project ini
2. Buat feature branch (`git checkout -b feature/AmazingFeature`)
3. Commit perubahan (`git commit -m 'Add some AmazingFeature'`)
4. Push ke branch (`git push origin feature/AmazingFeature`)
5. Buat Pull Request

---

## 🐛 Bug Reports

Jika menemukan bug, silakan buat issue dengan detail:

- Deskripsi bug
- Langkah reproduksi
- Expected behavior
- Screenshot (jika ada)
- Environment (OS, Browser, PHP version)

---

## 📝 Changelog

### Version 2.0.0 (Current)
- ✅ PWA Support dengan offline capability
- ✅ Background sync untuk transaksi offline
- ✅ Push notifications
- ✅ Improved UI/UX
- ✅ Multiple payment methods
- ✅ Enhanced reporting

### Version 1.0.0
- ✅ Basic POS functionality
- ✅ Admin panel
- ✅ Product management
- ✅ Transaction processing

---

## 🔮 Roadmap

- [ ] Barcode scanner integration
- [ ] Thermal printer support
- [ ] Multi-branch management
- [ ] Inventory forecasting
- [ ] Customer loyalty program
- [ ] WhatsApp integration
- [ ] API REST untuk mobile app
- [ ] Export data ke Excel/PDF

---

## 📄 License

This project is licensed under the MIT License - see the [LICENSE](LICENSE) file for details.

---

## 👨‍💻 Author

**Your Name**

- GitHub: [@yourusername](https://github.com/yourusername)
- Email: your.email@example.com

---

## 🙏 Acknowledgments

- [Bootstrap](https://getbootstrap.com/) - UI Framework
- [Chart.js](https://www.chartjs.org/) - Charts library
- [Font Awesome](https://fontawesome.com/) - Icons
- [jQuery](https://jquery.com/) - JavaScript library

---

## 📞 Support

Jika membutuhkan bantuan:

- 📧 Email: support@example.com
- 💬 WhatsApp: +62 xxx-xxxx-xxxx
- 📱 Telegram: @yourusername

---

<div align="center">

**⭐ Star project ini jika bermanfaat!**

Made with ❤️ by [Your Name]

</div>
