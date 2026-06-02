# Component Inventory — ระบบราคากลาง

รายการ Livewire components ทั้งหมด ณ ตอนเขียน (26 components).
อัปเดตรายการนี้ทุกครั้งที่เพิ่ม/ลบ/รวม component.

ตรวจ count ปัจจุบัน:
```bash
ls app/Livewire/*.php app/Livewire/Auth/*.php | wc -l
ls resources/views/livewire/**/*.blade.php
```

---

## 1. Full-Page Components (มี route โดยตรง)

| # | Component | Route | Role | Blade view |
|---|---|---|---|---|
| 1 | `Auth\Login` | `/login` | guest | `livewire/auth/login.blade.php` |
| 2 | `Dashboard` | `/dashboard` | all (auth) | `livewire/dashboard.blade.php` |
| 3 | `ProductList` | `/products` | all (auth) | `livewire/product-list.blade.php` |
| 4 | `CharacteristicsList` | `/specs` | `canSeeMenu('specs')` | `livewire/characteristics-list.blade.php` |
| 5 | `ComparisonList` | `/comparisons` | `canSeeMenu('comparisons')` | `livewire/comparison-list.blade.php` |
| 6 | `CompareView` | `/compare` | all (auth) | `livewire/compare-view.blade.php` |
| 7 | `GuidelineList` | `/guidelines` | `canSeeMenu('guidelines')` | `livewire/guideline-list.blade.php` |
| 8 | `RecommendationList` | `/recommendations` | `canSeeMenu('recommendations')` | `livewire/recommendation-list.blade.php` |
| 9 | `SearchPage` | `/search` | all (auth) | `livewire/search-page.blade.php` |
| 10 | `AuditLogPage` | `/audit-log` | all (filtered by role) | `livewire/audit-log-page.blade.php` |
| 11 | `UserProfile` | `/profile` | all (auth, self) | `livewire/user-profile.blade.php` |
| 12 | `CategoryListPage` | `/categories` | admin | `livewire/category-list-page.blade.php` |
| 13 | `BrandListPage` | `/brands` | admin | `livewire/brand-list-page.blade.php` |
| 14 | `UserList` | `/users` | admin | `livewire/user-list.blade.php` |
| 15 | `RoleList` | `/roles` | admin | `livewire/role-list.blade.php` |
| 16 | `MenuPermissionMatrix` | `/permissions` | admin | `livewire/menu-permission-matrix.blade.php` |

---

## 2. Modal / Sub-page Components (dispatched, ไม่มี route)

| # | Component | Trigger event | Role | Blade view |
|---|---|---|---|---|
| 17 | `ProductForm` | `open-product-form` | editor+ (add/edit) | `livewire/product-form.blade.php` |
| 18 | `ProductDetail` | view link `?view={id}` | all (auth) | `livewire/product-detail.blade.php` |
| 19 | `CharacteristicsForm` | `open-characteristics-form` | editor+ | `livewire/characteristics-form.blade.php` |
| 20 | `CharacteristicsDetail` | (eye button) | all (auth) | `livewire/characteristics-detail.blade.php` |
| 21 | `ComparisonForm` | `open-comparison-form` | editor+ | `livewire/comparison-form.blade.php` |
| 22 | `ComparisonDetail` | (view button) | all (auth) | `livewire/comparison-detail.blade.php` |
| 23 | `ImportModal` | `open-import-modal` | `hasPermission('products','import')` | `livewire/import-modal.blade.php` |
| 24 | `SpecsImportModal` | `open-specs-import` | `hasPermission('specs','import')` | `livewire/specs-import-modal.blade.php` |
| 25 | `CategoryManager` | inline ใน CategoryListPage | admin | `livewire/category-manager.blade.php` |
| 26 | `BrandManager` | inline ใน BrandListPage | admin | `livewire/brand-manager.blade.php` |

---

## 3. Shared Blade Components

ไม่ใช่ Livewire — แค่ partial views ที่ reuse:

| Component | ใช้ที่ | Path |
|---|---|---|
| `<x-sidebar>` | layouts/app | `components/sidebar.blade.php` |
| `<x-header>` | layouts/app | `components/header.blade.php` |
| `<x-nav-link>` | sidebar | `components/nav-link.blade.php` |
| `<x-kpi-card>` | dashboard | `components/kpi-card.blade.php` |

---

## 4. Layouts

| Layout | ใช้กับ | Path |
|---|---|---|
| `layouts.app` | ทุกหน้าหลัง login | `layouts/app.blade.php` |
| `layouts.guest` | login page เท่านั้น | `layouts/guest.blade.php` |

ทั้งสองมี JS patch สำหรับ XAMPP subdirectory ที่แก้ Livewire `data-update-uri` → POST ไปยัง path ที่ถูกต้อง.

---

## 5. Role Matrix (ส่วนหัว/เห็นเมนูอะไรบ้าง)

| เมนู | admin | editor | viewer |
|---|:-:|:-:|:-:|
| Dashboard | ✓ | ✓ | ✓ |
| Products | ✓ | ✓ | ✓ (read-only) |
| Specs (TOR) | ✓ | ✓ | ✗ |
| Comparisons | ✓ | ✓ | ✗ |
| Guidelines | ✓ | ✓ | ✗ |
| Recommendations | ✓ | ✓ | ✗ |
| Audit Log | ✓ (all) | ✓ (self) | ✓ (self) |
| Search | ✓ | ✓ | ✓ (filtered sections) |
| Categories | ✓ | ✗ | ✗ |
| Brands | ✓ | ✗ | ✗ |
| Users | ✓ | ✗ | ✗ |
| Roles | ✓ | ✗ | ✗ |
| Permissions | ✓ | ✗ | ✗ |
| Profile (self) | ✓ | ✓ | ✓ |

> Permission จริงเก็บใน `menu_permissions` table (จัดการที่ `/permissions` matrix UI).
> ตารางนี้คือ **default** จาก `RolePermissionSeeder` — admin เปลี่ยนได้.

---

## 6. Export Sub-routes (ExportController)

ไม่ใช่ Livewire components แต่เป็นส่วนของ UI workflow ที่ต้องรู้:

| Route | ใช้สำหรับ | Permission |
|---|---|---|
| `/products/export` | CSV download | `products` `export` |
| `/products/sample` | Excel template สำหรับ import | `products` `import` |
| `/specs/{spec}/export` | Excel ไฟล์เดียว (TH Sarabun font) | `specs` `export` |
| `/specs/{spec}/export/pdf` | PDF ไฟล์เดียว | `specs` `export` |
| `/specs/export/bulk` | Excel summary table หลายๆ specs | `specs` `export` |
| `/specs/sample` | Excel template สำหรับ import | `specs` `import` |
| `/comparisons/{cmp}/export` | Excel 1 sheet | `comparisons` `export` |
| `/comparisons/{cmp}/export/pdf` | PDF landscape | `comparisons` `export` |
| `/comparisons/export/bulk` | XLSX หลาย sheets (1 cmp = 1 sheet) | `comparisons` `export` |

---

## 7. Screenshots

ดู [`screenshots/`](screenshots/) สำหรับภาพหน้าจอจริง.
ชื่อไฟล์ map กับ component ตามตารางนี้:

| Component | Screenshot |
|---|---|
| `Auth\Login` | `01-login.png` |
| `Dashboard` (admin) | `02-dashboard.png` |
| `ProductList` | `03-products-list.png` |
| `ProductDetail` modal | `04-product-detail-modal.png` |
| `CharacteristicsList` | `05-specs-list.png` |
| `ComparisonList` | `06-comparisons.png` |
| `CompareView` | `07-compare-side-by-side.png` |
| `SearchPage` | `08-search.png` |
| `AuditLogPage` | `09-audit-log.png` |
| `MenuPermissionMatrix` | `10-permissions-matrix.png` |
| Dashboard (dark mode) | `11-dark-mode.png` |

หน้าอื่น ๆ ที่ optional (UserList, RoleList, GuidelineList ฯลฯ) จะเพิ่มภายหลังเมื่อมีเวลา capture.
