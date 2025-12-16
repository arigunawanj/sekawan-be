# Sekawan – Backend (Laravel API)

Laravel API untuk **pemesanan kendaraan** dengan **approval berjenjang (min 2 level)**, dashboard usage stats, **master data**, **audit log (DB)**, dan export laporan Excel.

## Tech Stack & Versi

- **PHP**: `^8.2`
- **Framework**: Laravel **12**
- **Auth**: Laravel Sanctum (token)
- **Database**: default **SQLite** (`database/database.sqlite`)
- **Excel Export**: `maatwebsite/excel`

## Akun Demo (Seeder)

- **Admin**: `admin@sekawan.test` / `password`
- **Approver L1**: `approver1@sekawan.test` / `password`
- **Approver L2**: `approver2@sekawan.test` / `password`

## Setup & Run

1) Install dependencies:

```bash
cd sekawan-be
composer install
```

2) Setup env + app key:

```bash
cp .env.example .env
php artisan key:generate
```

3) Database (SQLite) + migrate + seed:

```bash
touch database/database.sqlite
php artisan migrate --seed
```

4) Jalankan API:

```bash
php artisan serve
```

API default: `http://127.0.0.1:8000`

## Endpoint Utama (ringkas)

- **Auth**
  - `POST /api/auth/login` → `{ token, user }`
  - `GET /api/auth/me` (auth)
  - `POST /api/auth/logout` (auth)
- **Shared (Admin + Approver)**
  - `GET /api/dashboard/usage?from=YYYY-MM-DD&to=YYYY-MM-DD` (auth)
- **Admin**
  - **Master Data (list)**
    - `GET /api/master/vehicle-types` (auth + role:admin)
    - `GET /api/master/vehicles` (auth + role:admin) (`?include_inactive=1` optional)
    - `GET /api/master/drivers` (auth + role:admin) (`?include_inactive=1` optional)
    - `GET /api/master/approvers` (auth + role:admin)
  - **Master Data (CRUD)**
    - `POST /api/master/vehicle-types` (auth + role:admin)
    - `PUT /api/master/vehicle-types/{id}` (auth + role:admin)
    - `DELETE /api/master/vehicle-types/{id}` (auth + role:admin) *(hanya jika tidak dipakai kendaraan)*
    - `POST /api/master/vehicles` (auth + role:admin)
    - `PUT /api/master/vehicles/{id}` (auth + role:admin)
    - `DELETE /api/master/vehicles/{id}` (auth + role:admin) *(deactivate: `is_active=false`)*
    - `POST /api/master/drivers` (auth + role:admin)
    - `PUT /api/master/drivers/{id}` (auth + role:admin)
    - `DELETE /api/master/drivers/{id}` (auth + role:admin) *(deactivate: `is_active=false`)*
  - **Bookings (CRUD)**
    - `GET /api/bookings` (auth + role:admin)
    - `POST /api/bookings` (auth + role:admin)
    - `GET /api/bookings/{id}` (auth + role:admin)
    - `PUT /api/bookings/{id}` (auth + role:admin) *(hanya saat `pending_level1`)*
    - `DELETE /api/bookings/{id}` (auth + role:admin)
  - **Reports**
    - `GET /api/reports/bookings/excel?from=YYYY-MM-DD&to=YYYY-MM-DD` (auth + role:admin) → download `.xlsx`
  - **Audit Logs**
    - `GET /api/logs?action=...&from=...&to=...` (auth + role:admin)
- **Approver**
  - `GET /api/approvals/pending` (auth + role:approver)
  - `POST /api/approvals/{approvalId}/approve` (auth + role:approver)
  - `POST /api/approvals/{approvalId}/reject` (auth + role:approver)

## Logging

Audit log dicatat **ke database** dan juga ke `laravel.log`:

- **DB (audit trail)**: tabel `t_app_logs`
  - contoh action: `auth.login`, `booking.created`, `approval.approved`, `master.vehicle.deactivated`
  - endpoint admin: `GET /api/logs`
- **File log**: `storage/logs/laravel.log`

UI log tersedia di frontend: `/admin/logs`

## Dokumentasi Teknis

- Standar struktur Laravel API: `doc/intro.md`
- PDM (ERD) + activity diagram: `../doc/technical-test.md`
