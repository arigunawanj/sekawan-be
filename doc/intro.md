# Dokumentasi Awalan Laravel API (Naming Convention & Struktur Folder)

Dokumentasi ini digunakan sebagai **standar awal (awalan / prompt)** dalam membuat API menggunakan **Laravel**, agar struktur project rapi, konsisten, dan mudah dikembangkan.

---

## 1. Tujuan

* Menyeragamkan **penamaan database, model, controller, seeder, dan resource**
* Memudahkan maintenance dan scaling API
* Memisahkan **master data**, **transaksi**, dan **tabel jembatan (detail)** secara jelas

---

## 2. Konvensi Penamaan Database (Table Naming)

### 2.1 Tabel Master

Digunakan untuk data referensi atau data utama.

**Format:**

```
m_xxxx
```

**Contoh:**

* `m_kategori`
* `m_user`
* `m_role`

---

### 2.2 Tabel Transaksi

Digunakan untuk data utama yang bersifat proses atau transaksi.

**Format:**

```
t_xxxx
```

**Contoh:**

* `t_post`
* `t_order`
* `t_invoice`

---

### 2.3 Tabel Jembatan / Detail

Digunakan untuk relasi many-to-many atau detail dari tabel transaksi.

**Format:**

```
t_xxxx_det
```

**Contoh:**

* `t_post_kategori_det`
* `t_order_item_det`
* `t_invoice_det`

---

## 3. Konvensi Penamaan Model

Setiap model **wajib memiliki suffix `Model`**.

### 3.1 Model Master

**Format:**

```
NamaModel
```

**Contoh:**

* `KategoriModel`
* `UserModel`

Mapping:

```
m_kategori  ->  KategoriModel
```

---

### 3.2 Model Transaksi

**Contoh:**

* `PostModel`
* `OrderModel`

Mapping:

```
t_post  ->  PostModel
```

---

### 3.3 Model Detail / Jembatan

**Contoh:**

* `DetailPostModel`
* `OrderItemModel`

Mapping:

```
t_post_kategori  ->  DetailPostModel
```

---

## 4. Struktur Folder yang Direkomendasikan

Setiap fitur memiliki **folder sendiri** di:

* Model
* Controller
* Seeder
* Resource

---

### 4.1 Contoh Struktur Folder

```
app/
├── Models/
│   └── Post/
│       ├── PostModel.php
│       └── DetailPostModel.php
│
├── Http/
│   └── Controllers/
│       └── Post/
│           └── PostController.php
│
├── Http/
│   └── Resources/
│       └── Post/
│           ├── PostResource.php
│           └── DetailPostResource.php
│
├── Database/
│   └── Seeders/
│       └── Post/
│           ├── PostSeeder.php
│           └── DetailPostSeeder.php
```

---

## 5. Contoh Implementasi Fitur Post

### 5.1 Database

* `t_post`
* `t_post_kategori`

---

### 5.2 Model

```
Post/
├── PostModel
└── DetailPostModel
```

---

### 5.3 Controller

```
PostController
```

Digunakan untuk:

* CRUD Post
* Relasi Post dengan Kategori

---

### 5.4 Seeder

```
PostSeeder
DetailPostSeeder
```

---

### 5.5 API Resource

Digunakan untuk response API:

```
PostResource
DetailPostResource
```

---

## 6. Catatan Tambahan (Best Practice)

* Gunakan **singular untuk Model**, **plural untuk table (opsional sesuai kesepakatan)**
* Relasi antar model menggunakan `hasMany`, `belongsTo`, `belongsToMany`
* Pisahkan logic bisnis ke **Service Layer** jika project mulai besar
* Gunakan **API Resource** untuk konsistensi response

---

## 7. Ringkasan Singkat

| Komponen        | Aturan            |
| --------------- | ----------------- |
| Tabel Master    | `m_xxxx`          |
| Tabel Transaksi | `t_xxxx`          |
| Tabel Detail    | `t_xxxx_det`      |
| Model           | `NamaModel`       |
| Folder          | Dipisah per fitur |

---

Dokumentasi ini dapat digunakan sebagai **prompt awal** sebelum membuat API Laravel agar seluruh tim memiliki standar yang sama.
