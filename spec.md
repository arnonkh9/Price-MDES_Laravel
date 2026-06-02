# ข้อกำหนดระบบ — ระบบราคากลาง (spec.md)

> เอกสารนี้อธิบาย **ว่าระบบทำอะไร** (functional spec) — ใครใช้ได้ ฟีเจอร์อะไรบ้าง กฎทางธุรกิจ และโครงสร้างข้อมูล  
> สำหรับ architecture / how-to-run → ดู [`CLAUDE.md`](./CLAUDE.md)

---

## 1. ภาพรวม

**ชื่อระบบ:** ระบบจัดการราคากลาง (Price Reference Management System)  
**วัตถุประสงค์:** รวบรวม บันทึก และเปรียบเทียบราคาอุปกรณ์ IT สำหรับใช้อ้างอิงในการจัดซื้อจัดจ้างภาครัฐ  
**กลุ่มผู้ใช้:** เจ้าหน้าที่พัสดุและเทคโนโลยีสารสนเทศ  
**Stack:** Laravel 12 · Livewire 3 · PostgreSQL · Tailwind CSS v4  
**URL:** `http://localhost:8000` (dev) หรือ `http://localhost/price-MDES_V1/public` (XAMPP)

---

## 2. ผู้ใช้งานและสิทธิ์

### 2.1 บทบาท (Roles)

| บทบาท | คำอธิบาย |
|-------|----------|
| **admin** | ผู้ดูแลระบบ — เข้าถึงทุกส่วนรวมถึง user/category/brand management |
| **editor** | ผู้แก้ไข — เพิ่ม/แก้ไขสินค้า, คุณลักษณะ, การเปรียบเทียบ, แนวทาง, คำแนะนำ |
| **viewer** | ผู้ดู — ดูข้อมูลสินค้าและ dashboard เท่านั้น |

### 2.2 สิทธิ์ต่อโมดูล

| โมดูล | viewer | editor | admin |
|-------|--------|--------|-------|
| Dashboard | ✅ ดู | ✅ ดู | ✅ ดู |
| สินค้า (/products) | ✅ ดู | ✅ ดู + แก้ไข | ✅ ดู + แก้ไข |
| คุณลักษณะพื้นฐาน (/specs) | ❌ | ✅ ดู + แก้ไข + import | ✅ ดู + แก้ไข + import |
| การเปรียบเทียบ (/comparisons) | ❌ | ✅ ดู + แก้ไข | ✅ ดู + แก้ไข |
| ตารางเปรียบเทียบ (/compare) | ✅ ดู | ✅ ดู | ✅ ดู |
| แนวทางปฏิบัติ (/guidelines) | ❌ | ✅ ดู + แก้ไข | ✅ ดู + แก้ไข |
| คำแนะนำ (/recommendations) | ❌ | ✅ ดู + แก้ไข | ✅ ดู + แก้ไข |
| โปรไฟล์ (/profile) | ✅ แก้ไขตัวเอง | ✅ แก้ไขตัวเอง | ✅ แก้ไขตัวเอง |
| จัดการผู้ใช้ (/users) | ❌ | ❌ | ✅ |
| จัดการหมวดหมู่ (/categories) | ❌ | ❌ | ✅ |
| จัดการแบรนด์ (/brands) | ❌ | ❌ | ✅ |
| จัดการ Role (/roles) | ❌ | ❌ | ✅ |
| จัดการสิทธิ์เมนู (/permissions) | ❌ | ❌ | ✅ |

> **หมายเหตุ:** viewer ไม่เห็นเมนู specs, comparisons, guidelines, recommendations ใน sidebar  
> การเข้า URL โดยตรงโดยไม่มีสิทธิ์จะได้รับ HTTP 403

### 2.3 การเข้าสู่ระบบ
- Login ด้วย **username** (ไม่ใช่ email) + password
- ระบบล็อก IP หลังพิมพ์รหัสผิด 5 ครั้งภายใน 1 นาที (throttle)
- Session เก็บในฐานข้อมูล

---

## 3. โมดูลและฟีเจอร์

### 3.1 Dashboard (`/dashboard`)
- แสดง KPI cards: จำนวนสินค้าทั้งหมด, จำนวนหมวดหมู่, จำนวนการเปรียบเทียบ, จำนวนคุณลักษณะ
- เข้าถึงได้ทุก role

---

### 3.2 สินค้า (`/products`)
**ผู้เข้าถึง:** ทุก role (viewer ดูได้ แต่ไม่เพิ่ม/แก้ไข)

#### ฟีเจอร์หลัก
| ฟีเจอร์ | รายละเอียด |
|---------|-----------|
| **Filter** | กรองตาม: หมวดหมู่, แบรนด์, ปีงบประมาณ |
| **Sort** | เรียงตาม: ชื่อรุ่น, แบรนด์, ราคา, วันที่ |
| **ค้นหา** | ค้นตาม model / brand / หมวดหมู่ |
| **เพิ่มสินค้า** | form modal (editor/admin) |
| **แก้ไขสินค้า** | form modal พร้อม history บันทึกใครแก้ไข |
| **ลบสินค้า** | ทีละรายการ (editor/admin) |
| **Bulk delete** | เลือก checkbox หลายรายการ → ลบพร้อมกัน |
| **Compare cart** | เลือกสินค้าสูงสุด 3 รายการ → ดูตารางเปรียบเทียบ |
| **Export XLSX** | ส่งออกสินค้าทั้งหมด (ที่กรองแล้ว) เป็น Excel |
| **Import Excel** | นำเข้าสินค้าจาก Excel (admin/editor) |
| **แนบไฟล์** | แนบ PDF/รูปภาพต่อสินค้า (max 10 MB/ไฟล์) |

#### ข้อมูลสินค้า
| ฟิลด์ | ประเภท | หมายเหตุ |
|-------|--------|----------|
| id | string | รหัสสินค้า เช่น `nb-001`, `p-UUID` |
| category | string | slug หมวดหมู่ |
| brand | string | แบรนด์ |
| model | string | รุ่น (required) |
| price | decimal | ราคา |
| price_unit | string | หน่วย เช่น "บาท/เครื่อง" |
| price_date | string | วันที่ราคา (ปี พ.ศ.) |
| price_source | string | แหล่งที่มา |
| specs | jsonb | คุณลักษณะ (key-value ยืดหยุ่น) |

---

### 3.3 คุณลักษณะพื้นฐาน (`/specs`)
**ผู้เข้าถึง:** editor, admin

**วัตถุประสงค์:** เก็บ spec template สำหรับใช้อ้างอิงใน TOR (ข้อกำหนดคุณลักษณะ) และการเปรียบเทียบราคา

#### ฟีเจอร์หลัก
| ฟีเจอร์ | รายละเอียด |
|---------|-----------|
| **กรองตามปี/เดือน** | แสดง spec ตามช่วงเวลา |
| **Sort** | เรียงตาม: ชื่อ, หมวดหมู่, ปี, วงเงิน |
| **เพิ่ม/แก้ไข** | form modal — ชื่อ, หมวดหมู่, วงเงิน, spec fields สูงสุด 15 รายการ |
| **ลบ** | ลบทีละรายการ |
| **Checkbox select** | เลือกหลายรายการพร้อมกัน |
| **Bulk export Excel** | ส่งออก spec ที่เลือกเป็น summary table ใน 1 sheet |
| **Export เดี่ยว** | ส่งออก 1 spec เป็น detail sheet (TH Sarabun) |
| **Import Excel** | นำเข้าหลาย spec พร้อมกันจากไฟล์ Excel |
| **ดาวน์โหลดตัวอย่าง** | ไฟล์ `.xlsx` พร้อม dropdown สำหรับ category/year/month |
| **ใช้เป็นอ้างอิง** | ตั้ง spec เป็น base spec ของ compare cart |

#### ข้อมูล Spec Template
| ฟิลด์ | ประเภท | หมายเหตุ |
|-------|--------|----------|
| id | string | รหัส เช่น `sp-001` |
| name | string | ชื่อ TOR (required) |
| category | string | หมวดหมู่ |
| purpose | text | วัตถุประสงค์ |
| budget | decimal | วงเงินงบประมาณ |
| year | string | ปี พ.ศ. |
| month | string | เดือน (01–12) |
| created_by | string | ผู้สร้าง (บันทึกตอนสร้าง ไม่เปลี่ยน) |
| specs | jsonb | ฟิลด์คุณลักษณะ สูงสุด 15 รายการ |

#### Import Excel — รูปแบบคอลัมน์
```
name | category | year | month | budget | created_date | created_by | purpose | Spec 1 | Spec 2 | ... | Spec 15
```
- category ต้องตรงกับ slug ในระบบ (ไฟล์ตัวอย่างมี dropdown ให้เลือก)
- ถ้า name ซ้ำหรือ category ไม่ถูกต้อง → แถวนั้นถูกข้ามและแจ้งจำนวน skipped

---

### 3.4 การเปรียบเทียบราคา (`/comparisons`)
**ผู้เข้าถึง:** editor, admin

**วัตถุประสงค์:** สร้างตารางเปรียบเทียบราคาจากผู้ขายสูงสุด 3 ราย โดยอิงจาก spec template

#### ฟีเจอร์หลัก
| ฟีเจอร์ | รายละเอียด |
|---------|-----------|
| **สร้าง/แก้ไข** | form modal — ชื่อ, หมวดหมู่, ปี/เดือน, อ้างอิง spec, บันทึก, สถานะ |
| **3 vendor columns** | กรอกชื่อผู้ขาย, แบรนด์, รุ่น, ราคา, spec แต่ละรายการ |
| **Draft / Final** | draft = ร่าง, final = ปิดการแก้ไข (ใช้สำหรับ export อย่างเป็นทางการ) |
| **Checkbox select** | เลือกหลายรายการ |
| **Bulk export Excel** | ส่งออก comparison ที่เลือก — 1 comparison = 1 sheet |
| **Export เดี่ยว** | ส่งออก 1 comparison เป็น 1 sheet |
| **กรองตามปี/เดือน/สถานะ** | filter draft/final |

#### ข้อมูล Comparison
| ฟิลด์ | ประเภท | หมายเหตุ |
|-------|--------|----------|
| id | string | รหัสการเปรียบเทียบ |
| name | string | ชื่อ (required) |
| category | string | หมวดหมู่ |
| status | enum | `draft` หรือ `final` |
| spec_template_id | string FK | อ้างอิง spec template (optional) |
| created_by | string | ผู้สร้าง (ไม่เปลี่ยน) |

#### ข้อมูล Vendor (ต่อ comparison, max 3 รายการ)
| ฟิลด์ | หมายเหตุ |
|-------|----------|
| position | 1, 2 หรือ 3 |
| name | ชื่อผู้ขาย |
| brand | แบรนด์ |
| model | รุ่น |
| price | ราคา |
| specs | jsonb — ค่า spec ของผู้ขายนี้ |

---

### 3.5 ตารางเปรียบเทียบ (`/compare`)
**ผู้เข้าถึง:** ทุก role

- แสดงสินค้า 2–3 รายการแบบ side-by-side
- เลือกสินค้าจากหน้า `/products` (ปุ่ม bar-chart icon)
- สามารถกำหนด spec template เป็น "reference" เพื่อดู spec ที่กำหนดไว้ควบคู่
- Compare cart เก็บใน session (หายเมื่อปิด browser หรือ logout)

---

### 3.6 แนวทางปฏิบัติ (`/guidelines`)
**ผู้เข้าถึง:** editor, admin

- CRUD รายการแนวทางปฏิบัติ (guideline items)
- แต่ละรายการมี: content (text), category, year, month, created_by

---

### 3.7 คำแนะนำ (`/recommendations`)
**ผู้เข้าถึง:** editor, admin

- CRUD รายการคำแนะนำ (recommendation items)
- โครงสร้างเดียวกับ guidelines

---

### 3.8 โปรไฟล์ผู้ใช้ (`/profile`)
**ผู้เข้าถึง:** ทุก role (แก้ไขเฉพาะข้อมูลตัวเอง)

| ฟีเจอร์ | รายละเอียด |
|---------|-----------|
| ดูข้อมูล | ชื่อ, email, แผนก, บทบาท, วันที่สร้างบัญชี |
| แก้ไขชื่อ / email / แผนก | กด "แก้ไขข้อมูล" → บันทึก → หน้า reload อัตโนมัติ |
| เปลี่ยนรหัสผ่าน | modal — ต้องกรอก **รหัสผ่านเดิม** ก่อน, รหัสใหม่ ≥ 8 ตัวอักษร |
| ไม่แก้ไขได้ | username, role (ป้องกัน privilege escalation) |

---

### 3.9 Admin: จัดการผู้ใช้ (`/users`)
**ผู้เข้าถึง:** admin เท่านั้น

| ฟีเจอร์ | รายละเอียด |
|---------|-----------|
| รายการผู้ใช้ | แสดงชื่อ, username, role, แผนก |
| เพิ่มผู้ใช้ | form modal — username, ชื่อ, password, role, แผนก |
| แก้ไขผู้ใช้ | แก้ไขข้อมูลได้ทุกฟิลด์รวมถึง role และ reset password |
| ลบผู้ใช้ | ลบออกจากระบบ |

---

### 3.10 Admin: หมวดหมู่ (`/categories`) และแบรนด์ (`/brands`)
**ผู้เข้าถึง:** admin เท่านั้น

- จัดการ reference data สำหรับ dropdown ในฟอร์มสินค้าและ spec
- Categories มี: slug, label, short name, color, position
- Brands มี: label, slug, position

---

### 3.11 Admin: จัดการ Role (`/roles`)
**ผู้เข้าถึง:** admin เท่านั้น

| ฟีเจอร์ | รายละเอียด |
|---------|-----------|
| รายการ Role | แสดง slug, ชื่อ, level badge, คำอธิบาย, จำนวน users |
| 3 System Roles | admin / editor / viewer — มีไอคอน 🔒, ลบไม่ได้, เปลี่ยน level ไม่ได้ |
| เพิ่ม Custom Role | กำหนด slug (ไม่ซ้ำ), ชื่อ, level, คำอธิบาย |
| แก้ไข Role | system roles: แก้ชื่อ/คำอธิบายได้ แต่ level ถูกล็อก |
| ลบ Role | ลบได้เฉพาะ custom role ที่ไม่มี user ใช้งาน |

**Level** กำหนดพฤติกรรม `canEdit()`:
- `admin` หรือ `editor` → แก้ไขข้อมูลได้
- `viewer` → ดูอย่างเดียว

---

### 3.12 Admin: จัดการสิทธิ์เมนู (`/permissions`)
**ผู้เข้าถึง:** admin เท่านั้น

Matrix UI — แถว = เมนู, คอลัมน์ = roles ทั้งหมด

| พฤติกรรม | รายละเอียด |
|---------|-----------|
| Admin column | checked + locked เสมอ (admin เห็นทุกเมนูตลอดเวลา) |
| Non-admin | toggle checkbox แต่ละ role × menu ได้อิสระ |
| บันทึก | กดปุ่ม "บันทึก" — ผลมีผลทันทีที่ user โหลดหน้าถัดไป |
| Cache clear | หลังบันทึก ระบบ clear menu cache ของทุก user โดยอัตโนมัติ |

> **ค่า default (จาก RolePermissionSeeder):**  
> - admin เห็นทุกเมนู  
> - editor เห็น: specs, comparisons, guidelines, recommendations  
> - viewer ไม่เห็นเมนู restricted ใดเลย

---

## 4. กฎทางธุรกิจ (Business Rules)

### 4.1 วันที่
- ทุกวันที่ในระบบใช้ **ปีพุทธศักราช** (Buddhist Era)
- รูปแบบ: `YYYY-MM-DD` เช่น `2569-05-21` (= 21 พฤษภาคม พ.ศ. 2569)
- ปี พ.ศ. = ปี ค.ศ. + 543 (เช่น 2026 + 543 = 2569)

### 4.2 Compare Cart
- เลือกได้สูงสุด **3 สินค้า** ต่อ session
- การเลือกสินค้ารายการที่ 4 ขึ้นไปจะถูกปฏิเสธ (returns 'full')
- ล้าง cart เมื่อ logout หรือ session expire

### 4.3 Comparison — ข้อจำกัด Vendor
- สูงสุด **3 vendor** ต่อ comparison (position 1, 2, 3)
- ห้ามเลือก brand + model ซ้ำกันระหว่าง vendor ในการเปรียบเทียบเดียวกัน
- เมื่อ status = `final` → ล็อกการแก้ไข

### 4.4 Spec Template
- ฟิลด์ spec ใช้ index `1`–`15` เป็น key
- ฟิลด์ที่ว่างเปล่าจะไม่ถูกบันทึก
- `created_by` บันทึกจากผู้ login ตอนสร้าง ไม่เปลี่ยนแปลง

### 4.5 Product ID
- ไม่ใช้ auto-increment — เป็น string กำหนดเอง
- รูปแบบ: `{category-short}-{เลข}` เช่น `nb-001`, `srv-003`
- หรือ UUID อัตโนมัติ: `p-{UUID}` สำหรับสินค้าที่ import

### 4.6 ประวัติการแก้ไข (History)
- สินค้า: ทุกการสร้าง/แก้ไขบันทึกลง `product_edit_histories`
- Spec template: ทุกการสร้าง/แก้ไขบันทึกลง `spec_template_histories`

### 4.7 Security
- Login throttle: 5 ครั้ง / 1 นาที ต่อ IP
- Password ผ่าน bcrypt (Laravel Hash)
- เปลี่ยน password ต้องยืนยัน current password ก่อน
- Backend guard ทุก action ด้วย `abort_unless(auth()->user()->canEdit(), 403)`

---

## 5. หมวดหมู่สินค้า (Categories)

| slug | ชื่อเต็ม | ชื่อย่อ | สี |
|------|---------|---------|-----|
| `notebook` | Notebook | NB | #2563EB |
| `aio` | All-in-One | AIO | #7C3AED |
| `AI-COM` | AI Computer | AI | #DB2777 |
| `desktop-pc` | Desktop PC | PC | #059669 |
| `gaming-desktop-pc` | Gaming Desktop | GAME | #DC2626 |
| `mini-pc` | Mini PC | MINI | #EA580C |
| `server` | Server | SVR | #0369A1 |
| `monitor` | Monitor | MON | #3B82F6 |
| `ups` | UPS | UPS | #F59E0B |
| `software` | Software | SW | #10B981 |
| `projector` | Projector | PRJ | #8B5CF6 |
| `printer` | Printer | PRT | #EF4444 |
| `network` | Network | NET | #06B6D4 |

---

## 6. การนำเข้า / ส่งออก (Import / Export)

### Export

| รายการ | Route | รูปแบบ | หมายเหตุ |
|--------|-------|--------|----------|
| สินค้าทั้งหมด | `GET /products/export` | XLSX | ทุก role |
| Comparison เดี่ยว | `GET /comparisons/{id}/export` | XLSX — 1 sheet | ทุก role |
| Comparison หลายรายการ | `GET /comparisons/export/bulk?ids=...` | XLSX — N sheets | 1 comparison = 1 sheet |
| Spec เดี่ยว | `GET /specs/{id}/export` | XLSX — 1 sheet (detail) | ทุก role |
| Spec หลายรายการ | `GET /specs/export/bulk?ids=...` | XLSX — 1 sheet (summary) | ทุก role |

**Font:** ทุก export ใช้ฟอนต์ **TH Sarabun New**

### Import

| รายการ | Route | รูปแบบ |
|--------|-------|--------|
| สินค้า | form upload ใน `/products` | Excel (.xlsx/.xls) หรือ CSV |
| Spec templates | form upload ใน `/specs` | Excel (.xlsx/.xls) |

#### ดาวน์โหลดไฟล์ตัวอย่าง Spec
- `GET /specs/sample` → `ตัวอย่าง_คุณลักษณะ.xlsx`
- ไฟล์มี dropdown validation สำหรับคอลัมน์ `category`, `year`, `month`
- ลดความผิดพลาดจากการพิมพ์ slug ผิด

---

## 7. โมเดลข้อมูลหลัก (Data Model)

```
users
  id, username, name, email, role, department, password

categories
  slug (PK), label, short, color, position

products
  id (PK, string), category, brand, model, price, price_unit,
  price_date (string BE), price_source, price_url, specs (jsonb)

product_attachments
  id, product_id (FK), original_name, file_path, file_size

product_edit_histories
  id, product_id (FK), date, user, action, detail, source, url

spec_templates                        ← CharacteristicsTemplate model
  id (PK, string), name, category, purpose, budget,
  year, month, created_date, created_by, specs (jsonb)

spec_template_histories
  id, spec_template_id (FK), date, user, action, detail

comparisons
  id (PK, string), name, category, year, month,
  spec_template_id (FK nullable), notes,
  status (draft|final), created_date, created_by

comparison_vendors
  id, comparison_id (FK cascade), position (1-3),
  name, brand, model, price, specs (jsonb)

brands
  id (PK), label, slug, position

guideline_items
  id, content, category, year, month, created_by

recommendation_items
  id, content, category, year, month, created_by

roles
  slug (PK), name, description, level (admin|editor|viewer),
  is_system (bool), position

menu_permissions
  id, role_slug (FK → roles.slug, cascade delete),
  menu_key, can_see (bool)
  UNIQUE(role_slug, menu_key)
```

---

## 8. รูปแบบการแจ้งเตือน (Notifications)

| ประเภท | วิธี | ตัวอย่าง |
|--------|------|---------|
| สำเร็จ | Toast (ด้านล่างขวา) | "บันทึกสำเร็จ", "ลบสำเร็จ" |
| Error validation | ข้อความสีแดงใต้ฟิลด์ | "กรุณากรอกชื่อ" |
| Confirm ลบ | Browser confirm dialog | "ยืนยันการลบ?" |
| 403 Forbidden | Full-page error | เมื่อเข้า URL โดยไม่มีสิทธิ์ |

---

*อัปเดตล่าสุด: พฤษภาคม 2569 | เวอร์ชัน: Phase 10 (Role Management & Menu Permissions)*
