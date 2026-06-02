---
description: Verify changes to the Price Reference Management web app via browser (MCP Chrome tools)
---

# verifier-web skill

## What this skill does
Drives the running app through a real browser (via `mcp__Claude_in_Chrome__*` tools) to observe
whether a change works at the UI surface — not by running tests or typechecking.

---

## Prerequisites

1. **App must be running** — use the `run-app` skill first:
   ```bash
   php artisan serve --port=8000
   ```

2. **MCP Chrome tools available** — listed in deferred tools as `mcp__Claude_in_Chrome__*`  
   Load them if needed: `ToolSearch("select:mcp__Claude_in_Chrome__navigate,mcp__Claude_in_Chrome__computer,mcp__Claude_in_Chrome__screenshot")`

---

## ⚠️ CRITICAL: Use `browser_batch` for ALL Livewire Button Clicks

Individual `mcp__Claude_in_Chrome__computer left_click` calls do **NOT** reliably trigger
Livewire event handlers in this MCP Chrome tab group. Clicks register at the DOM level but
no Livewire AJAX POST fires — the component never reacts.

**Always chain mouse actions atomically via `mcp__Claude_in_Chrome__browser_batch`:**

```json
[
  { "action": "left_click", "coordinate": [x, y] },
  { "action": "wait", "duration": 800 },
  { "action": "screenshot" }
]
```

> **Why it works:** `browser_batch` executes actions atomically in a single CDP round-trip,
> which gives Livewire's event listener time to fire before the next action is dispatched.

> **Discovery context:** Verified 2026-05-27 across multiple tab groups and page reloads.
> Hard refreshes, new tabs, and dismissing the Claude banner did not fix standalone clicks.
> Switching to `browser_batch` resolved the issue completely.

---

## Standard Verification Flow

### Step 1 — Open a fresh tab
Use `mcp__Claude_in_Chrome__tabs_create_mcp` to open a new tab (avoids stale Livewire state from previous sessions).

### Step 2 — Navigate to the feature
```
mcp__Claude_in_Chrome__navigate  url="http://localhost:8000/login"
```

### Step 3 — Login
Use `mcp__Claude_in_Chrome__computer` with physical mouse clicks:
- `triple_click` on the username field → `type` the username
- `triple_click` on the password field → `type` the password  
- `left_click` on the login button

> **IMPORTANT:** Do NOT use `mcp__Claude_in_Chrome__javascript_tool` to fire `btn.click()` —  
> Alpine.js/Livewire handlers do NOT trigger from JS-dispatched click events.  
> Always use `computer` tool with physical mouse coordinates.

### Step 4 — Navigate to the page under test
```
mcp__Claude_in_Chrome__navigate  url="http://localhost:8000/products"
```

### Step 5 — Interact and observe
Use `mcp__Claude_in_Chrome__computer` for all interactions:
- `screenshot` to capture current state
- `triple_click` + `type` for text inputs
- `scroll` to reveal hidden content

> ⚠️ **For button clicks that trigger Livewire actions** — use `browser_batch` instead of standalone `left_click`:
> ```json
> [{ "action": "left_click", "coordinate": [x, y] }, { "action": "wait", "duration": 800 }, { "action": "screenshot" }]
> ```
> Standalone `left_click` does NOT reliably fire Livewire handlers in this session environment.

### Step 6 — Check network requests (Livewire)
```
mcp__Claude_in_Chrome__read_network_requests
```
Look for POST to `/livewire/update` (or `/price-MDES_V1/public/livewire/update`).  
If you see 404 on Livewire update → the `data-update-uri` JS patch may have failed.

### Step 7 — Check console errors
```
mcp__Claude_in_Chrome__read_console_messages
```
Livewire errors show as `showFailureModal` or `Uncaught` in console.

---

## Test Accounts for Verification

| Scenario | Username | Password | Role |
|----------|----------|----------|------|
| Full access | `admin` | `admin123` | admin |
| Edit access | `user02` | `test123` | editor |
| Read-only | `user01` | `test123` | viewer |

---

## Common Verification Scenarios

### Verify a Livewire component change
1. Login as admin → navigate to the page
2. Trigger the changed action (click button, fill form)
3. Screenshot result
4. Check network for successful POST to `livewire/update` (HTTP 200)
5. Check console for errors

### Verify role-based access
1. Login as viewer (user01) → check sidebar (should NOT see specs/comparisons/guidelines/recommendations)
2. Navigate directly to `/specs` → expect 403 page
3. Login as editor (user02) → check sidebar (SHOULD see above 4 menus, NOT see users/categories/brands)
4. Login as admin → verify all menus visible

### Verify dark mode
1. Login → click 🌙 toggle in header → screenshot (UI should flip to dark)
2. Refresh → screenshot (dark mode should persist via localStorage)
3. Click ☀️ → screenshot (back to light)

### Verify bulk delete
1. Login as admin → `/products`
2. Tick 2-3 product checkboxes → screenshot (red "ลบที่เลือก (N รายการ)" button should appear)
3. Click the button → confirm dialog → OK
4. Screenshot after → products should be gone, toast "ลบสินค้าสำเร็จ N รายการ" appears

### Verify compare feature
1. Click bar-chart icon on any product row → "เลือกเปรียบเทียบ 1/3 รายการ" badge in toolbar
2. Add 2 more → badge shows 3/3, 4th product's compare button disabled
3. Click badge → navigates to `/compare` with all 3 columns

### Verify bulk export (comparisons หรือ specs)
1. Login as admin → `/comparisons` (หรือ `/specs`)
2. Click header checkbox → ทุก row ควร checked ✅
3. "ส่งออก Excel (N รายการ)" button ควรปรากฏ
4. Click export button → browser ดาวน์โหลด `.xlsx` file
5. Viewer (user01): ไม่ควรเห็น checkbox และปุ่ม export เลย

### Verify specs import
1. Login as admin → `/specs` → click "นำเข้าคุณลักษณะ" button
2. Modal opens → click "ดาวน์โหลดตัวอย่าง" → file `ตัวอย่าง_คุณลักษณะ.xlsx` ดาวน์โหลด
3. เปิดไฟล์ใน Excel → คอลัมน์ category/year/month ควรมี dropdown
4. Upload ไฟล์กลับ → toast "นำเข้าสำเร็จ N รายการ" ควรปรากฏ
5. `/specs` list ควรแสดง specs ใหม่ทันที (ไม่ต้อง reload)

### Verify user profile
1. Login as any user → click username (top right) → click "ข้อมูลส่วนตัว"
2. Navigate to `/profile` → ตรวจสอบ name, email, department, role, created date ปรากฏ
3. Click "แก้ไขข้อมูล" → แก้ชื่อ → click "บันทึก"
4. หน้า reload → ชื่อในหัวข้อ (header top-right) ควรเปลี่ยนทันที, toast "บันทึกข้อมูลส่วนตัวสำเร็จ" ปรากฏ
5. Click "เปลี่ยนรหัสผ่าน" → modal opens
6. ใส่รหัสผ่านปัจจุบันผิด → error "รหัสผ่านปัจจุบัน ไม่ตรง" ปรากฏ
7. ใส่ถูก + new password ≥ 8 chars (match) → toast "เปลี่ยนรหัสผ่านสำเร็จ"

### Verify post-save auto-refresh (specs)
1. Login as admin → `/specs`
2. Click "สร้างคุณลักษณะพื้นฐานใหม่" → กรอกชื่อ → click "บันทึก"
3. Form closes → **list ควรแสดง spec ใหม่ทันที** โดยไม่ต้อง reload
4. Click edit บน spec ใด → แก้ชื่อ → click "บันทึก"
5. **list ควรแสดงชื่อใหม่ทันที**

### Verify `/roles` CRUD
1. Login admin → sidebar ส่วน "จัดการระบบ" ต้องเห็นเมนู **จัดการ Role**
2. คลิก → `/roles` → ตรวจว่าเห็น 3 rows: admin / editor / viewer พร้อม 🔒 badge
3. `browser_batch` คลิก Edit บน "admin" → ตรวจว่า level field ถูกล็อก (badge ไม่ใช่ dropdown)
4. `browser_batch` คลิก "เพิ่ม Role" → กรอก slug=`supervisor`, level=`editor` → `browser_batch` บันทึก
5. ตรวจว่า row ใหม่ปรากฏในตาราง
6. `browser_batch` คลิกปุ่มลบ "admin" → ต้องเห็น toast warn ปฏิเสธ (system role)
7. `browser_batch` คลิกปุ่มลบ "supervisor" → สำเร็จ, row หายไป

### Verify `/permissions` matrix
1. Login admin → sidebar ส่วน "จัดการระบบ" ต้องเห็นเมนู **จัดการสิทธิ์เมนู**
2. คลิก → `/permissions` → ตรวจว่า admin column: ทุก checkbox checked + disabled
3. ตรวจว่า editor column: specs/comparisons/guidelines/recommendations checked, ที่เหลือ unchecked
4. Toggle checkbox viewer × specs → `browser_batch` คลิก "บันทึก" → toast สำเร็จ
5. Logout → Login user01 (viewer) → sidebar ต้องเห็นเมนู "คุณลักษณะพื้นฐาน"
6. เปิด `/specs` → เข้าได้ (ไม่ 403)
7. (Cleanup) Login admin → `/permissions` → ปิด viewer × specs → บันทึกกลับ

---

## Troubleshooting

### Livewire AJAX 404
**Symptom:** Network shows POST to `http://localhost/livewire/update` → 404  
**Cause:** `data-update-uri` not patched (XAMPP subdirectory issue)  
**Check:** `mcp__Claude_in_Chrome__javascript_tool` → `document.querySelector('[data-update-uri]')?.getAttribute('data-update-uri')`  
Should be `/price-MDES_V1/public/livewire/update` or `http://localhost:8000/livewire/update`

### Page stuck / Livewire InvalidStateError
**Fix:** Close the tab, open a fresh tab with `tabs_create_mcp`, start over

### Can't click button (coordinates wrong)
**Fix:** Take a fresh screenshot first to get current layout, then click

### Login throttled
**Symptom:** 429 Too Many Requests after 5 failed attempts  
**Fix:** Wait 1 minute, or restart the server

### `wire:confirm` Blocks CDP (~30-second timeout)
**Symptom:** `browser_batch` screenshot times out after 30000ms when clicking delete buttons  
**Cause:** `wire:confirm` triggers native browser `confirm()` dialog which **blocks CDP entirely** — no further actions can execute until the dialog is dismissed or times out  
**Fix:** Wait ~30 seconds for the timeout to resolve; the delete may still complete in the background  
**Workaround:** For delete operations, use artisan tinker when browser confirm is unreliable:
```bash
php artisan tinker --execute="App\Models\Role::where('slug','supervisor')->delete();"
```
