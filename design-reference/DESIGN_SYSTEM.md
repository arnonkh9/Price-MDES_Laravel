# Design System — ระบบราคากลาง

Single source of truth: `resources/css/app.css` (Tailwind v4 `@theme` block).
ตารางในเอกสารนี้สะท้อนค่าจริง ณ ตอนเขียน — ถ้า `app.css` เปลี่ยน ให้อัปเดตที่นี่ตามด้วย.

---

## 1. Color Tokens

### 1.1 Brand & Neutral

| Token | Light | Dark | ใช้ที่ |
|---|---|---|---|
| `--color-navy` | `#1B3A6B` | (same) | Primary CTA, focus ring, brand accents |
| `--color-navy-600` | `#1e4d99` | (same) | Hover state ของ navy |
| `--color-sidebar` | `#1a1a1a` | `#1a1a1a` | Sidebar bg (คงเข้มเสมอเพื่อ readability) |
| `--color-canvas` | `#F0F4F8` | `#0F172A` | Page background |
| `--color-ink` | `#1E293B` | `#E2E8F0` | Primary text |
| `--color-muted` | `#64748B` | `#94A3B8` | Secondary text |
| `--color-faint` | `#94A3B8` | `#64748B` | Caption / hint text |
| `--color-line` | `#E2E8F0` | `#334155` | Card / divider borders |
| `--color-line-soft` | `#F1F5F9` | `#1E293B` | Subtle dividers (table rows) |
| `--color-surface` | `#ffffff` | `#1E293B` | Card / modal background |
| `--color-surface-alt` | `#F8FAFC` | `#0F172A` | Alt rows, code blocks |
| `--color-surface-raised` | `#ffffff` | `#243047` | Dropdown / popover bg |

### 1.2 Semantic

| Token | Value | ใช้ที่ |
|---|---|---|
| `--color-price` | `#059669` light / `#34D399` dark | ราคา highlight |
| `--color-spec` | `#7C3AED` | Spec/TOR badges |

### 1.3 Category Palette

ใช้สำหรับ badges, sidebar accents, chart series.

| Category slug | Token | Value |
|---|---|---|
| Notebook | `--color-cat-notebook` | `#2563EB` |
| All-in-One | `--color-cat-aio` | `#7C3AED` |
| AI Computer | `--color-cat-aicom` | `#DB2777` |
| Desktop PC | `--color-cat-pc` | `#059669` |
| Gaming PC | `--color-cat-gaming` | `#DC2626` |
| Mini PC | `--color-cat-mini` | `#EA580C` |
| Server | `--color-cat-server` | `#0369A1` |

> ดูเพิ่ม: `App\Support\Specs::colorMap()` map slug → hex สำหรับใช้ใน PHP.

---

## 2. Typography

- **Font family:** `Sarabun` (Google Fonts, weights 300, 400, 500, 600, 700, 800) → loaded via `<link>` in layouts
- **Body default:** font-family `Sarabun`, color `text-ink`, bg `bg-canvas`

### Scale ที่ใช้บ่อย (จาก Blade views จริง)

| ระดับ | Class | ตัวอย่างการใช้ |
|---|---|---|
| Page title | `text-[22px] font-extrabold text-ink` | "ภาพรวมระบบ", "รายการสินค้า" |
| Section header (in card) | `text-sm font-extrabold text-ink` | "จำนวนสินค้าตามหมวดหมู่" |
| Body | `text-[13px] text-ink` | Product detail rows |
| Caption / hint | `text-xs text-muted` หรือ `text-[11px] text-faint` | "ข้อมูล ณ วันที่ ..." |
| KPI number | `text-3xl font-extrabold` | "72 รายการ" |
| Mono / code | `font-mono` (browser default) | Hash, raw values |

> **Don't use `font-bold`** — ใช้ `font-extrabold` (weight 800) เพื่อให้ contrast ตรงกับฟีลของระบบ.

---

## 3. Spacing & Layout

### 3.1 App Shell

```
┌─────────────────────────────────────────────────┐
│ Sidebar         │ Header (h-[60px])              │
│ w-[240px]       ├────────────────────────────────┤
│ (collapsed:     │                                │
│  w-[64px])      │   Main content                 │
│                 │   px-4 md:px-7                 │
│                 │   pt-4 md:pt-7 pb-10           │
└─────────────────┴────────────────────────────────┘
```

- Sidebar: fixed left, `bg-[--color-sidebar]`, collapsible via Alpine state `sidebarCollapsed`
- Header: fixed top, `h-[60px]`, transitions `left` เมื่อ sidebar collapse
- Mobile (`< md`): sidebar slides in/out via `sidebarOpen` state

### 3.2 Card Pattern

```html
<div class="bg-surface rounded-[14px] border border-line overflow-hidden">
    <div class="px-5 pt-[18px] pb-3.5 border-b border-line-soft">
        <h3 class="text-sm font-extrabold text-ink m-0">หัวข้อ</h3>
    </div>
    <div class="px-4 py-4">
        <!-- content -->
    </div>
</div>
```

### 3.3 Modal Pattern

```html
<div class="fixed inset-0 z-50 bg-black/40 flex items-center justify-center p-4 modal-overlay">
    <div class="bg-surface-raised rounded-2xl shadow-2xl max-w-[640px] w-full modal-card">
        <!-- header / body / footer -->
    </div>
</div>
```

ใช้ `wire:click.self="close"` ที่ overlay เพื่อปิดเมื่อคลิกข้างนอก. Print styles ใน `app.css` แปลง modal ให้แสดง inline เมื่อสั่งพิมพ์.

### 3.4 Button Patterns

| Variant | Class |
|---|---|
| Primary | `flex items-center gap-1.5 px-4 py-2 bg-navy text-white rounded-lg text-[13px] font-bold` |
| Secondary | `px-[13px] py-[7px] border-[1.5px] border-line bg-surface text-muted rounded-lg text-[13px] font-semibold` |
| Icon-only | `p-[7px] rounded-lg border-[1.5px] border-line bg-surface text-muted hover:text-ink` |
| Destructive | `bg-red-600 text-white` (เปลี่ยน bg-navy → bg-red-600) |

### 3.5 Form Inputs

- Default: `border-none bg-transparent outline-none text-sm py-2.5 text-ink`
- ภายใน wrapper: `flex items-center gap-[9px] bg-surface-alt border-[1.5px] border-line rounded-[10px] px-3`
- Focus: outline `2px solid var(--color-navy)` (กำหนดใน `app.css`)

---

## 4. Custom Numeric Values ที่ใช้บ่อย

ระบบใช้ค่า px เฉพาะเจาะจง (custom Tailwind arbitrary values) ค่อนข้างเยอะ เพื่อให้ภาพดูแน่นและเป็นทางการ. รายการที่พบบ่อย:

| Value | ใช้ที่ |
|---|---|
| `rounded-[10px]` | Input wrapper, badge |
| `rounded-[14px]` | Card |
| `rounded-2xl` (16px) | Modal |
| `px-[18px]`, `px-[13px]` | Custom button padding |
| `py-[9px]`, `py-[7px]` | Custom button height |
| `gap-3.5` (14px), `gap-[9px]` | Common gaps |
| `text-[22px]`, `text-[13px]`, `text-[11px]` | Off-scale type sizes ที่ตรงกับ prototype |

> **อย่าแก้เป็น default scale** (เช่น `text-sm` แทน `text-[13px]`) — ค่า off-scale จงใจ เพื่อให้ตรง prototype Sarabun.

---

## 5. Component Patterns

ดู Blade views จริงเป็น living examples:

| Pattern | ดูไฟล์ |
|---|---|
| **KPI card** | `resources/views/components/kpi-card.blade.php` |
| **Sidebar nav item** | `resources/views/components/nav-link.blade.php` |
| **Sidebar shell** (brand + collapse + role-aware menu) | `resources/views/components/sidebar.blade.php` |
| **Header** (search + actions + dark toggle + user menu) | `resources/views/components/header.blade.php` |
| **Modal pattern** | `resources/views/livewire/product-form.blade.php` (ตัวอย่างที่สมบูรณ์) |
| **Filter chips** (year/month) | `resources/views/livewire/characteristics-list.blade.php` |
| **Bulk action bar** | `resources/views/livewire/characteristics-list.blade.php` (ส่วน bulk export) |
| **Toast** | dispatch `'toast'` event → handled in `layouts/app.blade.php` |
| **Print** | `app.css` `@media print` + class `no-print` บน elements |

---

## 6. Livewire Patterns (UI ↔ State)

| Pattern | ตัวอย่าง |
|---|---|
| **Modal-by-event** | `dispatch('open-product-form')` ใน list → `#[On('open-product-form')] open()` ใน form. เลี่ยง prop drilling. |
| **Post-save auto-refresh** | `dispatch('product-saved')` หลัง save → empty `#[On('product-saved')]` ใน list → Livewire re-render auto |
| **Toast** | `dispatch('toast', message: 'บันทึกสำเร็จ')` → global listener ใน `layouts/app.blade.php` |
| **wire:navigate** | SPA-like transitions ระหว่างหน้า (ไม่ full reload) |
| **#[Url]** | sync state ⇄ querystring (`?q=`, `?view=id`) ใน `SearchPage`, `AuditLogPage`, `ProductList` |

---

## 7. Animations

- `slideUp` keyframe ใน `app.css` (translateY 16px → 0, opacity 0 → 1, duration 0.25s) — ใช้กับ toast และ modal entrance
- Button press feedback: `button:active { transform: scale(0.97) }` — global เหมือนกันทุก button
- Sidebar collapse/expand: Tailwind `transition-[width,transform] duration-200`

---

## 8. Dark Mode

- Toggle ผ่านปุ่ม 🌙/☀ ที่ header → `toggleDark()` Alpine action
- Persistence: localStorage key `darkMode` (boolean) + `<html class="dark">`
- Token overrides ทำใน `.dark { ... }` block ของ `app.css`
- **เฉพาะ sidebar:** color **ไม่เปลี่ยน** เพื่อให้อ่านง่ายทั้งสองโหมด

> ทดสอบ: ทุกครั้งที่เพิ่ม component ใหม่ ต้องเปิด dark mode แล้ว visual check ว่าไม่มีจุดที่อ่านไม่ออก.

---

## 9. Print Styles

Defined ที่ end ของ `app.css` (`@media print` block):

- ซ่อน sidebar, header, modals, toast (`.no-print` + selectors)
- Reset margins ของ main content (`.print-full`)
- Modal layout เป็น inline (ไม่ overlay) เพื่อพิมพ์ได้
- ขนาดหน้า: `@page { margin: 20mm }`
- ตาราง: `page-break-inside: auto` (row-level `avoid`)

ใช้กับการพิมพ์รายงาน/ใบเสนอราคา/ตารางเปรียบเทียบ.

---

## 10. Scrollbar

Custom webkit scrollbar — `5px` thin, `#CBD5E1` thumb (light), `#475569` dark.

> Firefox ใช้ default scrollbar — เป็นข้อจำกัด CSS, ไม่ใช่ bug.
