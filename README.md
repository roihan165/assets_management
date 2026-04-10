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
- Status flow:
pending → approved → returned
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
app/
├── Models/
├── Services/
├── Http/Controllers/

resources/
├── views/
│ ├── livewire/
│ ├── components/
│ ├── reports/


---

## ⚙️ Installation

### 1. Clone Repository

```bash
git clone https://github.com/roihan165/assets_management.git
cd assets_management

### 2. Install Dependency

```bash
composer install
npm install && npm run dev

### 3. Setup Enviroment

```bash
cp .env.example .env
php artisan key:generate

### 4. Database Migration

```bash
php artisan migrate --seed

### 5. Run Server
```bash
composer run dev

👤 Default Account
Admin:
email: admin@example.com
password: password123
