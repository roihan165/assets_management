# 📦 Inventory & Loan Management System

Sistem manajemen inventaris dan peminjaman barang berbasis **Laravel + Livewire (Volt)** yang dirancang untuk membantu pengelolaan asset secara efisien, transparan, dan terstruktur.

---

## 🚀 Features

### 🔐 Role Management
Menggunakan **Spatie Permission**:
- Admin
- Atasan
- Staff

---

### 📊 Dashboard
- Total barang (historical)
- Status barang:
  - Available
  - Borrowed
  - Maintenance
  - Lost
- Optimized query (`withCount`)

---

### 📦 Inventory Management
- Manajemen item & item unit
- Status unit:
  - available
  - borrowed
  - maintenance
  - lost
- Kondisi unit:
  - good
  - minor_damage
  - major_damage
  - lost
- Tambah unit:
  - Auto generate (bulk)
  - Manual input (kode custom)

---

### 🔄 Loan System (Unit-Based)
- Peminjaman berbasis unit (bukan quantity)
- Hanya unit available yang bisa dipinjam
- Preview sebelum submit
- Menggunakan service layer + transaction

---

### ✅ Approval System
- Atasan dapat:
  - Approve
  - Reject
- Status flow:\
pending → approved → returned\
pending → rejected


---

### 🔁 Return System (2-Step Approval)
1. Staff submit return → `return_pending`
2. Atasan approve:
 - Update kondisi barang
 - Update status unit
 - Update status loan → `returned`

---

### 🧾 Audit Log
Menggunakan activity log untuk tracking:
- Loan
- Return
- Approval

---

### 📄 Export Report
- Export PDF laporan:
- Data inventory
- Riwayat peminjaman
- Multi-section report (inventory + loan)

---

## 🛠️ Tech Stack

- **Laravel 10+**
- **Livewire Volt**
- **Flux UI**
- **MySQL**
- **Spatie Permission**
- **Dompdf (PDF Export)**

---

## 📂 Project Structure (Highlight)
app/\
├── Models/\
├── Services/\
├── Http/Controllers/

resources/\
├── views/\
│ ├── livewire/\
│ ├── components/\
│ ├── reports/


---

## ⚙️ Installation

### 1. Clone Repository

```bash
git clone https://github.com/roihan165/assets_management.git
cd assets_management
```
### 2. Install Dependency

```bash
composer install
npm install && npm run dev
```
### 3. Setup Enviroment

```bash
cp .env.example .env
php artisan key:generate
```
### 4. Database Migration

```bash
php artisan migrate --seed
```
### 5. Run Server
```bash
composer run dev
```

### Default Test Accounts

Run `php artisan migrate --seed` to create the default roles and accounts.
All test accounts use the password `password123`:

| Role | Email | Password |
| --- | --- | --- |
| Super Admin (`admin`) | `admin@example.com` | `password123` |
| SPV / Atasan (`atasan`) | `spv@example.com` | `password123` |
| Staff (`staff`) | `staff@example.com` | `password123` |

These are development and testing accounts. Do not use the default credentials in production.

📌 Key Concepts\
🔹 Unit-Based Inventory\
Setiap barang memiliki unit unik (bukan quantity biasa)

🔹 Status vs Condition\
* Status → posisi barang (available, borrowed, dll)\
* Condition → kondisi fisik barang
  
🔹 Service Layer\
Semua logic utama ditempatkan di service:
* LoanService
* ItemService
* ReportService

---
🤝 Contributing\
Pull request terbuka untuk improvement dan bug fix.

---

📄 License\
MIT License

---
👨‍💻 Author\
Developed by [AI]
