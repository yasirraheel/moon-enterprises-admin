# 🏆 GEO ENTERPRISES - Prize Bond Booking System

<div align="center">

![GEO ENTERPRISES](https://img.shields.io/badge/GEO%20ENTERPRISES-Prize%20Bond%20System-blue?style=for-the-badge)
![Laravel](https://img.shields.io/badge/Laravel-10.x-red?style=for-the-badge&logo=laravel)
![PHP](https://img.shields.io/badge/PHP-8.1+-green?style=for-the-badge&logo=php)
![MySQL](https://img.shields.io/badge/MySQL-Database-blue?style=for-the-badge&logo=mysql)
![License](https://img.shields.io/badge/License-MIT-yellow?style=for-the-badge)

**A comprehensive and professional prize bond booking and management system built with Laravel.**

[Overview](#-overview) •
[Key Features](#-key-features) •
[Technology Stack](#%EF%B8%8F-technology-stack) •
[Installation](#-installation) •
[Changelog](CHANGELOG.md)

</div>

---

## 🎯 Overview

**GEO ENTERPRISES Prize Bond Booking System** is a full-featured web application designed to streamline prize bond operations. The system offers secure transaction management, customer relationship tools, and comprehensive administrative controls.

## ✨ Key Features

### 🏢 Admin Panel
- **Dashboard Analytics**: Real-time statistics and revenue tracking
- **Customer Management**: Complete customer database with profile management
- **Game Categories**: Organize and manage different prize bond categories
- **Payment & Deposits**: Approve/reject customer deposits with admin notes, multiple payment gateway integration
- **Settings & Theme**: Comprehensive system configuration with light/dark mode support

### 👥 Customer Features
- **Authentication**: Secure account creation, login, and profile management
- **Recharge/Deposit**: Easy fund addition with payment proof upload
- **Financial Tracking**: Complete transaction history and real-time balance
- **Responsive UI**: Mobile-friendly interface for on-the-go access

### 💰 Financial & Game Management
- **Transactions**: Admin-controlled deposit verification and comprehensive reporting
- **Game Scheduling**: Schedule management for draws and active/inactive control
- **Subcategories**: Detailed classification system for prize bonds

## 🛠️ Technology Stack

| Component | Technology |
| --- | --- |
| **Backend** | Laravel 10.x |
| **Frontend** | Bootstrap 5, HTML5, CSS3, JavaScript |
| **Database** | MySQL / MariaDB |
| **Language** | PHP 8.1+ |
| **Server** | Apache / Nginx |

## 📋 System Requirements

- **PHP** >= 8.1 (with BCMath, Ctype, cURL, DOM, Fileinfo, JSON, Mbstring, OpenSSL, PDO, Tokenizer, XML)
- **Database** >= MySQL 5.7 or MariaDB 10.2
- **Web Server** Apache or Nginx
- Composer

## 🚀 Installation

### 1. Clone & Install
```bash
git clone https://github.com/yasirraheel/GEO-ENTERPRISES.git
cd GEO-ENTERPRISES
composer install
```

### 2. Configure Environment
```bash
cp .env.example .env
php artisan key:generate
```
*Edit `.env` with your database and email SMTP credentials.*

### 3. Database & Storage Setup
```bash
php artisan migrate --seed
php artisan storage:link
```

### 4. Permissions & Cache
```bash
chmod -R 755 storage bootstrap/cache
php artisan optimize:clear
```

## 🔧 Configuration

**Admin Setup**
Create an admin account using Artisan:
```bash
php artisan make:command CreateAdmin
```

**Payment & Email**
- Navigate to **Admin Panel → Settings** to configure bank details and gateways.
- Configure SMTP settings in `.env` for email notifications.

## 📱 Usage

- **Admin Portal**: `https://yourdomain.com/panel/admin`
- **Customer Portal**: `https://yourdomain.com/register`

### Workflows
1. **Deposits**: Customer logs in → Selects payment → Uploads proof → Admin reviews & approves.
2. **Games**: Admin creates category/dates → Customers view and purchase.

## 🔐 Security & Optimization

- **Security**: Built-in CSRF, XSS, and SQL Injection protection, role-based access control, secure file uploads.
- **Performance**: Optimized DB indexing, Laravel caching, compressed images.

## 🤝 Contributing & Support

1. Fork the project and create your feature branch: `git checkout -b feature/AmazingFeature`
2. Commit your changes: `git commit -m 'Add AmazingFeature'`
3. Push to the branch and open a Pull Request!

For support, please create an issue on GitHub.

## 📄 License

Distributed under the MIT License. See `LICENSE` for more information.

---
<div align="center">
<b>GEO ENTERPRISES</b> - Professional Prize Bond Booking Solutions <br>
<i>Built with ❤️ using Laravel</i>
</div>
