---
description: Launch the Price Reference Management app for local development
---

# run-app skill

## What this skill does
Starts the Laravel app and confirms it's reachable, so you can interact with it via browser or MCP Chrome tools.

---

## Launch

### Option A — php artisan serve (recommended for Claude sessions)
```bash
php artisan serve --port=8000
```
App URL: `http://localhost:8000`

### Option B — XAMPP Apache (already running)
App URL: `http://localhost/price-MDES_V1/public`

> **Which to use:** `php artisan serve` สะดวกกว่าเพราะ URL สั้น และไม่มีปัญหา subdirectory  
> Apache option ใช้เมื่อต้องการทดสอบ XAMPP production path จริง

---

## Smoke check
```bash
curl -s -o /dev/null -w "%{http_code}" http://localhost:8000/login
# expect: 200
```

---

## Login (test accounts)

| Username | Password | Role |
|----------|----------|------|
| `admin`  | `admin123` | admin — full access |
| `user01` | `test123`  | viewer — read-only |
| `user02` | `test123`  | editor — edit products/specs/comparisons |

**Login URL:** `http://localhost:8000/login`

---

## If the app won't start

```bash
# 1. Check PHP version (need 8.2+)
php --version

# 2. Check .env exists
ls .env

# 3. Check DB connection
php artisan migrate:status

# 4. Clear stale cache
php artisan config:clear && php artisan cache:clear

# 5. If DB empty — reseed
php artisan db:seed --class=ProductSeeder
```

---

## Common routes

| URL | Page |
|-----|------|
| `/login` | หน้า login |
| `/dashboard` | Dashboard |
| `/products` | รายการสินค้า (filter, sort, bulk-delete) |
| `/comparisons` | การเปรียบเทียบราคา (checkbox + bulk export) |
| `/compare` | ตารางเปรียบเทียบ side-by-side |
| `/specs` | Characteristics templates (checkbox + bulk export + import) |
| `/guidelines` | แนวทางปฏิบัติ |
| `/recommendations` | คำแนะนำ |
| `/profile` | ข้อมูลส่วนตัว + เปลี่ยนรหัสผ่าน (self-service) |
| `/categories` | จัดการหมวดหมู่ (admin) |
| `/brands` | จัดการแบรนด์ (admin) |
| `/users` | จัดการผู้ใช้ (admin) |

---

## Notes
- Session driver = database — ต้องมี DB ก่อน
- Livewire AJAX ทำงานผ่าน `data-update-uri` — มี JS patch ใน layouts แล้ว
- ถ้า login ผิดพลาดซ้ำ 5 ครั้ง → throttle 1 นาที (`throttle:5,1`)
