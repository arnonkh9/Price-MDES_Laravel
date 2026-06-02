# Price Reference Management System — CLAUDE.md

## Project Overview

ระบบจัดการราคากลาง (Price Reference Management) สำหรับหน่วยงานรัฐ  
ใช้บันทึก/เปรียบเทียบราคาสินค้า IT (Notebook, Server, Monitor ฯลฯ) ตามหมวดหมู่

**Stack:** Laravel 12 · Livewire 3 · Alpine.js · Tailwind CSS v4 · PostgreSQL  
**Deployment:** XAMPP subdirectory (`http://localhost/price-MDES_V1/public`)

---

## How to Run

### Prerequisites
- XAMPP running (Apache + PostgreSQL หรือ PostgreSQL standalone)
- PHP 8.2+ ใน PATH

### Start the app
```bash
php artisan serve --port=8000
# เปิด http://localhost:8000
```
> หรือเข้าผ่าน Apache โดยตรง: `http://localhost/price-MDES_V1/public`

### Database setup (first time)
```bash
php artisan migrate
php artisan db:seed
```

### Re-seed products (ถ้า DB ว่าง)
```bash
php artisan db:seed --class=ProductSeeder
# ได้ 72 สินค้า (6 รายการ × 12 หมวดหมู่)
```

> **Fresh install:** ใช้ `php artisan db:seed` (full) ไม่ใช่แค่ `--class=ProductSeeder`
> เพื่อให้ได้ specs + comparisons ด้วย (DatabaseSeeder เรียก CharacteristicsTemplateSeeder
> และ ComparisonSeeder). ถ้า DB เก่ามีแค่ products ให้รันเสริม:
> ```bash
> php artisan db:seed --class=CharacteristicsTemplateSeeder
> php artisan db:seed --class=ComparisonSeeder
> ```

### Clear cache
```bash
php artisan config:clear && php artisan view:clear && php artisan cache:clear
```

---

## Test Accounts

| Username | Password | Role | สิทธิ์ |
|----------|----------|------|--------|
| `admin` | `admin123` | admin | เข้าถึงทุกส่วน |
| `user01` | `test123` | viewer | ดูข้อมูลอย่างเดียว |
| `user02` | `test123` | editor | แก้ไขสินค้า/specs/comparisons |

> ถ้า password ไม่ตรง reset ด้วย:  
> `php artisan tinker --execute="App\Models\User::where('username','admin')->update(['password'=>bcrypt('admin123')]);"`

---

## Architecture

### Directory Structure
```
app/
├── Livewire/           # Full-page Livewire components (หน้าหลักทั้งหมด)
│   ├── Auth/Login.php
│   ├── ProductList.php       # /products — filter, sort, bulk-delete, compare
│   ├── ProductForm.php       # modal form (เพิ่ม/แก้ไขสินค้า)
│   ├── ProductDetail.php     # modal detail
│   ├── ComparisonList.php    # /comparisons — checkbox select + bulk export
│   ├── ComparisonForm.php    # modal form (สร้าง/แก้ไขการเปรียบเทียบ)
│   ├── ComparisonDetail.php
│   ├── CompareView.php       # /compare — ตารางเปรียบเทียบแบบ side-by-side
│   ├── CharacteristicsList.php  # /specs — checkbox select + bulk export + import
│   ├── CharacteristicsForm.php  # modal form (เพิ่ม/แก้ไขคุณลักษณะ)
│   │                            #   dispatches 'characteristics-saved' event on save
│   ├── SpecsImportModal.php  # modal import Excel → specs (admin only)
│   ├── GuidelineList.php     # /guidelines
│   ├── RecommendationList.php   # /recommendations
│   ├── UserList.php          # /users (admin only)
│   ├── UserProfile.php       # /profile — self-service profile edit + password change
│   ├── RoleList.php          # /roles — CRUD roles (admin only)
│   ├── MenuPermissionMatrix.php  # /permissions — role × menu toggle matrix (admin only)
│   ├── CategoryListPage.php / CategoryManager.php
│   ├── BrandListPage.php / BrandManager.php
│   ├── Dashboard.php
│   └── ImportModal.php       # modal import Excel → products
├── Models/
│   ├── User.php            # role helpers: isAdmin(), canEdit(), canSeeMenu()
│   ├── Product.php         # specs (jsonb), attachments
│   ├── Category.php        # slug PK
│   ├── Brand.php
│   ├── Comparison.php      # draft|final, linked to spec_templates
│   ├── ComparisonVendor.php
│   ├── CharacteristicsTemplate.php  # (= spec_templates table)
│   ├── ProductAttachment.php
│   ├── GuidelineItem.php
│   ├── RecommendationItem.php
│   ├── Role.php              # slug PK, name, level (admin|editor|viewer), is_system, position
│   └── MenuPermission.php    # role_slug FK, menu_key, can_see (bool)
├── Support/
│   ├── Specs.php           # helper: categories(), colorMap(), label(), fields()
│   ├── CompareCart.php     # Session-based compare basket (max 3 items)
│   └── GeneratesUUID.php
├── Http/Controllers/
│   └── ExportController.php    # Export routes (products, comparisons, specs)
│       # GET /products/export
│       # GET /comparisons/{id}/export
│       # GET /comparisons/export/bulk?ids=id1,id2,...
│       # GET /specs/{spec}/export
│       # GET /specs/export/bulk?ids=id1,id2,...
│       # GET /specs/sample
├── Imports/
│   ├── ProductsImport.php      # Excel import via Maatwebsite — products
│   └── CharacteristicsImport.php  # Excel import — specs/characteristics
└── Exports/
    ├── ComparisonExport.php          # 1 comparison → 1 XLSX sheet (TH Sarabun)
    ├── BulkComparisonsExport.php     # N comparisons → N sheets (WithMultipleSheets)
    ├── CharacteristicsExport.php     # 1 spec → detailed XLSX (TH Sarabun)
    ├── BulkCharacteristicsExport.php # N specs → summary table XLSX (TH Sarabun)
    └── SampleCharacteristicsExport.php  # sample import template with dropdowns

resources/views/
├── layouts/
│   ├── app.blade.php       # authenticated layout (sidebar + header + JS patches)
│   └── guest.blade.php     # login layout
├── components/
│   ├── sidebar.blade.php   # role-based nav (canSeeMenu()) — bg: #005fbf
│   ├── header.blade.php    # user menu (links to /profile), dark toggle
│   ├── nav-link.blade.php
│   └── kpi-card.blade.php
└── livewire/               # blade views ตรงกับ app/Livewire/

config/
├── specs.php               # category definitions, spec field groups, months, years
└── nav.php                 # menu_visibility: route → allowed roles (legacy — now DB-driven via menu_permissions table)

database/
├── migrations/             # ไม่ต้องแก้ — schema เสถียรแล้ว
└── seeders/
    ├── ProductSeeder.php
    └── RolePermissionSeeder.php  # seeds 3 system roles + default menu permissions
```

---

## Key Architecture Decisions

### Tailwind CSS v4 (ไม่มี tailwind.config.js)
- ใช้ `@theme` directive ใน `resources/css/app.css`
- CSS variables: `--color-canvas`, `--color-ink`, `--color-surface`, `--color-line` ฯลฯ
- Dark mode: `@custom-variant dark (&:where(.dark, .dark *))` + `.dark {}` override block
- Semantic tokens: `bg-canvas`, `text-ink`, `bg-surface`, `bg-surface-alt`, `border-line`
- **ไม่ใช้ hardcoded colors** เช่น `bg-white` → ใช้ `bg-surface` แทนเสมอ
- Sidebar color: `--color-sidebar: #005fbf` (blue) ใน `@theme`, `:root:not(.dark)`, และ `.dark`

### Livewire XAMPP Subdirectory Bug (Critical)
`getUpdateUri()` ใน Livewire คืน `/livewire/update` (root-relative) แทนที่จะเป็น `/price-MDES_V1/public/livewire/update`  
**Fix:** JS patch ใน `layouts/app.blade.php` และ `layouts/guest.blade.php` หลัง `@livewireScripts`:
```javascript
(function(){
    var base = '{{ rtrim(parse_url(config("app.url"), PHP_URL_PATH) ?? "", "/") }}';
    if (!base) return;
    var s = document.querySelector('script[data-update-uri]');
    if (!s) return;
    var uri = s.getAttribute('data-update-uri');
    if (uri && !uri.startsWith('http') && !uri.startsWith(base)) {
        s.setAttribute('data-update-uri', base + uri);
    }
})();
```

### Role-Based Access Control
```
admin  → ทุกอย่าง รวมถึง users/categories/brands
editor → products, specs, comparisons, guidelines, recommendations
viewer → ดูอย่างเดียว (ไม่เห็นเมนู specs/comparisons/guidelines/recommendations)
```

- **Model helpers:** `$user->isAdmin()`, `$user->canEdit()`, `$user->canSeeMenu('route')`
- **Sidebar:** ใช้ `@if (auth()->user()->canSeeMenu('specs'))` ห่อแต่ละเมนู
- **Menu config:** DB-driven via `menu_permissions` table — `canSeeMenu()` reads from DB with static cache per role slug (replaces old `config/nav.php` approach)
- **Cache clear:** `User::clearMenuCache()` — called automatically after saving permissions in `MenuPermissionMatrix`
- **Backend guard:** `abort_unless(auth()->user()->canEdit(), 403)` ใน Livewire `mount()`

### Product ID Format
- String PK (ไม่ใช่ auto-increment) เช่น `nb-001`, `srv-003`, `p-UUID`
- Trait `GeneratesUUID` ใน ProductForm ออก UUID อัตโนมัติ

### Dates
- ใช้ **ปีพุทธศักราช** (Buddhist Era) เช่น `2569-05-23`
- ไม่ใช้ Carbon date — เก็บเป็น string

### Categories
- กำหนดใน `config/specs.php` array (slug, label, short, color)
- sync กับ `categories` table ผ่าน seeder (`CategorySeeder`)
- ข้อมูลอ้างอิงผ่าน `App\Support\Specs::categories()`, `Specs::colorMap()`, `Specs::label($slug)`

### CompareCart (Session-based)
- `CompareCart::toggle($id)` → 'added' | 'removed' | 'full'
- `CompareCart::ids()` → array ของ product IDs (max 3)
- ใช้ session key `compare_ids`

### Post-Save Auto-Refresh Pattern (Livewire Events)
ใช้ Livewire browser events เพื่อให้ list refresh หลัง form save (sibling components):

**CharacteristicsForm → CharacteristicsList:**
```php
// CharacteristicsForm::save() — หลัง $this->close()
$this->dispatch('characteristics-saved');

// CharacteristicsList — stack #[On] attributes บน refreshList()
#[On('specs-imported')]
#[On('characteristics-saved')]
public function refreshList(): void {}  // empty — Livewire re-renders อัตโนมัติ
```

**ComparisonForm → ComparisonList:** ใช้ event `comparison-saved` แบบเดียวกัน

### User Profile Save — Redirect with Flash
หน้า `/profile` ใช้ redirect แทน Livewire re-render เพราะ `<x-header>` เป็น static Blade component ที่ต้อง full-page reload เพื่อแสดงชื่อใหม่:

```php
// UserProfile::saveProfile()
session()->flash('profile_saved', true);
$this->redirect(route('profile'), navigate: true);

// UserProfile::mount()
if (session()->pull('profile_saved')) {
    $this->dispatch('toast', message: 'บันทึกข้อมูลส่วนตัวสำเร็จ');
}
```

### Excel Export — TH Sarabun Font
Export classes ทุกตัวใช้ `WithStyles` interface เพื่อตั้งฟอนต์ TH Sarabun New:
```php
public function styles(Worksheet $sheet): array
{
    $sheet->getStyle($sheet->calculateWorksheetDimension())
          ->getFont()->setName('TH Sarabun New');
    return [];
}
```

### Bulk Export Pattern
- Comparisons: `BulkComparisonsExport implements WithMultipleSheets` → แต่ละ comparison = 1 sheet
- Specs: `BulkCharacteristicsExport implements FromArray` → specs ทั้งหมดใน 1 sheet (summary table)

### Specs Import (Excel → CharacteristicsTemplate)
- Column mapping: `name`, `category`, `year`, `month`, `budget`, `created_date`, `created_by`, `purpose`, `Spec 1`…`Spec 15`
- Validation: name required, category ต้องตรง slug ใน DB, budget numeric
- Sample file มี dropdown validation (DataValidation::TYPE_LIST) สำหรับ category/year/month

### Role & Menu Permission System (DB-driven)

ระบบ Role และ Menu Permission เก็บใน DB — admin ปรับสิทธิ์ผ่าน UI ได้โดยไม่ต้องแก้โค้ด

**roles table** (slug PK):
- 3 system roles: `admin`, `editor`, `viewer` — `is_system = true` → ลบไม่ได้, เปลี่ยน `level` ไม่ได้
- Custom roles: เพิ่มได้ไม่จำกัด, `level` กำหนด `canEdit()` behavior (`admin`|`editor` → แก้ไขได้, `viewer` → ดูอย่างเดียว)

**menu_permissions table** (role_slug × menu_key matrix):
- Seeded ครั้งแรกจาก `RolePermissionSeeder` (default: admin เห็นทุกเมนู, editor เห็น 4 เมนู, viewer ไม่เห็นเมนู restricted)
- `canSeeMenu()` ใช้ static cache `self::$menuCache[role_slug]` — 1 DB query per role per request
- Cache ถูก clear โดย `User::clearMenuCache()` หลัง `MenuPermissionMatrix::save()`

**User model helpers (updated):**
```php
// canSeeMenu() — reads from menu_permissions table, static cache
private static array $menuCache = [];
public function canSeeMenu(string $routeName): bool { ... }
public static function clearMenuCache(): void { self::$menuCache = []; }

// canEdit() — supports custom roles via roles.level
public function canEdit(): bool {
    // admin/editor → true, viewer → false
    // custom role → check roles.level in DB
}

// roleName() — falls back to DB for custom roles
public function roleName(): string { ... }
```

**Re-seeding after fresh migrate:**
```bash
php artisan db:seed --class=RolePermissionSeeder
```

---

## Database Schema (สรุป)

```
users               id, username, name, email, role, department, password
categories          slug (PK), label, short, color, position
products            id (PK, string), category, brand, model, price, price_unit,
                    price_date (string 'YYYY-MM-DD BE), specs (jsonb)
product_attachments id, product_id (FK), original_name, file_path, size
product_edit_histories  id, product_id (FK), date, user, action, detail, source, url
spec_templates      id (PK), name, category, purpose, budget, year, month,
                    created_date, created_by, specs (jsonb)
spec_template_histories  id, spec_template_id (FK), date, user, action, detail
comparisons         id (PK), name, category, year, month, spec_template_id (FK nullable),
                    notes, status (draft|final), created_date, created_by
comparison_vendors  id, comparison_id (FK), position (1-3), name, brand, model,
                    price, specs (jsonb)
brands              id (PK, string), label, slug, position
guideline_items     id, category, content, year, month, created_by
recommendation_items  id, category, content, year, month, created_by
roles               slug (PK), name, description, level (admin|editor|viewer), is_system, position
menu_permissions    id, role_slug (FK → roles.slug cascade), menu_key, can_see — UNIQUE(role_slug, menu_key)
```

---

## Common Tasks

### เพิ่ม user ใหม่
ไปที่ `/users` (admin only) → "เพิ่มผู้ใช้"

### จัดการ Role
ไปที่ `/roles` (admin only) — เพิ่ม/แก้ไข/ลบ custom roles

### จัดการสิทธิ์เมนู
ไปที่ `/permissions` (admin only) — toggle role × menu matrix แล้วกด "บันทึก"

### Seed roles ครั้งแรก (หลัง fresh migrate)
```bash
php artisan db:seed --class=RolePermissionSeeder
```

### Reset password ผ่าน CLI
```bash
php artisan tinker --execute="App\Models\User::where('username','X')->update(['password'=>bcrypt('newpass')]);"
```

### แก้ไขข้อมูลส่วนตัว/เปลี่ยนรหัสผ่าน (self-service)
ไปที่ `/profile` (เมนูด้านบนขวา → "ข้อมูลส่วนตัว")

### Export comparisons เป็น Excel
- Single: ปุ่ม export บนหน้า comparison detail
- Bulk: เลือก checkbox ใน `/comparisons` → "ส่งออก Excel (N รายการ)" → ไฟล์เดียว N sheets

### Export specs เป็น Excel
- Single: ปุ่ม export (icon) บน row ใน `/specs`
- Bulk: เลือก checkbox ใน `/specs` → "ส่งออก Excel (N รายการ)" → summary table sheet

### Import specs จาก Excel
ไปที่ `/specs` → "นำเข้าคุณลักษณะ" → upload ไฟล์ → ดาวน์โหลดตัวอย่างก่อนถ้าต้องการ

### อัปเดต category
แก้ `config/specs.php` แล้วรัน: `php artisan db:seed --class=CategorySeeder`

### Restart dev server cleanly (สำคัญ)
`php artisan serve` ที่รันซ้ำโดยไม่ kill process เก่าจะมีหลาย PHP process ฟังบน port 8000 พร้อมกัน — ทำให้ Blade cache เก่าจาก process เก่ายังเสิร์ฟอยู่แม้รัน `view:clear` แล้ว

**วิธี restart อย่างถูกต้อง:**
```powershell
# Kill ทุก process บน port 8000 ก่อน
Get-NetTCPConnection -LocalPort 8000 -ErrorAction SilentlyContinue |
    Select-Object -ExpandProperty OwningProcess | Sort-Object -Unique |
    ForEach-Object { Stop-Process -Id $_ -Force -ErrorAction SilentlyContinue }
# แล้วค่อย serve ใหม่
php artisan serve --port=8000
```

### ดู Livewire errors
เปิด browser DevTools → Console → Network (filter `livewire/update`)

### Debug XAMPP path issues
ตรวจ `APP_URL` ใน `.env` ต้องเป็น `http://localhost/price-MDES_V1/public`

---

## Environment (.env highlights)

```
APP_URL=http://localhost/price-MDES_V1/public
DB_CONNECTION=pgsql
DB_DATABASE=price-MDES-1
DB_USERNAME=postgres
DB_PASSWORD=123456789
SESSION_DRIVER=database
QUEUE_CONNECTION=database
CACHE_STORE=database
```

---

## Files NOT to modify

- `vendor/` — Composer packages
- `database/migrations/` — Schema stable, ไม่ต้องแก้
- `.env` — ไม่ commit, ไม่ push
