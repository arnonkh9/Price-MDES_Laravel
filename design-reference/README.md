# Design Reference — ระบบราคากลาง

เอกสารอ้างอิงด้าน UI/UX สำหรับ maintain ความ consistency
ของ **Laravel 12 + Livewire 3 + Tailwind CSS v4** implementation
ของระบบจัดการราคากลาง (Price Reference Management).

---

## เริ่มที่นี่

| ไฟล์ | ใช้เมื่อไร |
|---|---|
| **[DESIGN_SYSTEM.md](DESIGN_SYSTEM.md)** | เพิ่ม feature ใหม่ — ต้องการ design tokens (color, typography, spacing) และ component patterns |
| **[COMPONENT_INVENTORY.md](COMPONENT_INVENTORY.md)** | อยากรู้ว่ามี Livewire component อะไรบ้าง · route · role · ไฟล์ Blade ที่ไหน |
| **[screenshots/](screenshots/)** | ภาพหน้าจอจริงของแต่ละหน้าเป็น visual reference |

---

## Source of Truth

เอกสารในโฟลเดอร์นี้เป็น **reference ที่ point ไปยัง source of truth** ไม่ใช่ duplicate
ของจริง. แก้ทีเดียวที่ source แล้ว reference ยังตรง — ลด maintenance burden.

| ของจริง | ที่ไหน |
|---|---|
| **Design tokens** (colors, font, scrollbar, animations) | `resources/css/app.css` — `@theme { ... }` block |
| **Layout shell** | `resources/views/layouts/app.blade.php`, `layouts/guest.blade.php` |
| **Shared UI components** | `resources/views/components/*.blade.php` (sidebar, header, nav-link, kpi-card) |
| **Page-level components** | `resources/views/livewire/*.blade.php` (26 ไฟล์) |
| **Backend / state** | `app/Livewire/*.php` |
| **Role-based access** | `app/Models/User.php` (`canSeeMenu()`, `hasPermission()`) |
| **Permission matrix** | `roles` + `menu_permissions` tables (จัดการผ่าน UI `/permissions`) |

---

## หลักการสำคัญ

1. **Sarabun ทุกที่** — font หลักของระบบ (Google Fonts, weights 300–800). ใช้ทั้ง screen และ print.
2. **Sidebar คงสีเข้มเสมอ** — `--color-sidebar: #1a1a1a` ทั้ง light/dark mode เพื่อ readability.
3. **Semantic tokens เท่านั้น** — ใช้ `bg-surface`, `text-ink`, `border-line` แทน `bg-white`, `text-slate-800`, `border-gray-200` ตรง ๆ. ทำให้ dark mode ทำงานอัตโนมัติ.
4. **Card pattern เดียวกัน** — `bg-surface rounded-[14px] border border-line` ใช้ทุกหน้า.
5. **ทุก CRUD เขียน history** — products/specs ทุก action เขียน row ลง `*_histories` → audit log + activity feed.
6. **Livewire events เป็นกาว** — sibling components คุยกันผ่าน `dispatch('X-saved')` / `#[On('X-saved')]` ไม่ใช้ prop drilling.

---

## Historical Reference

📦 **[_archive/](_archive/)** — JSX prototype ดั้งเดิมจาก Claude Design (พ.ค. 2569)
ก่อนเริ่ม implement Laravel. **อย่าใช้ตัดสินใจ design** — ใช้ docs ใน root นี้แทน.
เก็บไว้เพื่อเป็นบันทึก historical ของจุดเริ่มต้นโครงการเท่านั้น.
