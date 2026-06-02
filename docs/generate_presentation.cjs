// Generate PowerPoint presentation: System Flow ระบบราคากลาง
const path = require('path');
const fs = require('fs');

const globalRoot = require('child_process').execSync('npm root -g').toString().trim();
const pptxgen = require(path.join(globalRoot, 'pptxgenjs'));
const React = require(path.join(globalRoot, 'react'));
const ReactDOMServer = require(path.join(globalRoot, 'react-dom/server'));
const sharp = require(path.join(globalRoot, 'sharp'));
const FA = require(path.join(globalRoot, 'react-icons/fa'));

// ---------- Palette (drawn from the actual project UI) ----------
const C = {
    navy:     "1B3A6B",  // sidebar, primary
    blue:     "2563EB",  // CTA bright
    slate:    "64748B",  // muted body
    inkDark:  "0F172A",  // headings on light bg
    bgLight:  "FFFFFF",
    bgSoft:   "F8FAFC",
    bgCode:   "F1F5F9",
    line:     "E2E8F0",
    emerald:  "059669",
    amber:    "D97706",
    purple:   "7C3AED",
    cyan:     "0891B2",
    red:      "DC2626",
    white:    "FFFFFF",
    iceBlue:  "CADCFC",
};

const FONT = "Tahoma";       // excellent Thai rendering, universal
const MONO = "Consolas";

// ---------- Icon helper (react-icons → base64 PNG) ----------
const iconCache = {};
async function icon(Comp, hexColor, size = 256) {
    const key = `${Comp.name}|${hexColor}|${size}`;
    if (iconCache[key]) return iconCache[key];
    const svg = ReactDOMServer.renderToStaticMarkup(
        React.createElement(Comp, { color: "#" + hexColor, size: String(size) })
    );
    const png = await sharp(Buffer.from(svg)).png().toBuffer();
    return iconCache[key] = "image/png;base64," + png.toString("base64");
}

// Shadow factory (DO NOT share across calls — pptxgenjs mutates)
const sh = () => ({ type: "outer", color: "0F172A", blur: 8, offset: 1.5, angle: 90, opacity: 0.10 });

// ---------- Presentation setup ----------
const pres = new pptxgen();
pres.layout = "LAYOUT_WIDE";   // 13.3" × 7.5"
pres.author = "System Documentation";
pres.title = "System Flow: ระบบราคากลาง";

const W = 13.3, H = 7.5;

// ---------- Reusable helpers ----------
function pageHeader(slide, num, kicker, title, iconData, accentColor) {
    // Top kicker bar
    slide.addShape(pres.shapes.RECTANGLE, {
        x: 0, y: 0, w: W, h: 0.06,
        fill: { color: accentColor }, line: { type: "none" }
    });
    // Icon circle
    slide.addShape(pres.shapes.OVAL, {
        x: 0.55, y: 0.5, w: 0.6, h: 0.6,
        fill: { color: accentColor }, line: { type: "none" },
        shadow: sh()
    });
    slide.addImage({
        data: iconData,
        x: 0.7, y: 0.65, w: 0.3, h: 0.3
    });
    // Kicker label
    slide.addText(kicker, {
        x: 1.3, y: 0.45, w: 6, h: 0.3,
        fontFace: FONT, fontSize: 11, color: C.slate, charSpacing: 4,
        bold: true, valign: "bottom", margin: 0
    });
    // Title
    slide.addText(title, {
        x: 1.3, y: 0.7, w: 11.5, h: 0.55,
        fontFace: FONT, fontSize: 26, color: C.inkDark,
        bold: true, valign: "top", margin: 0
    });
    // Footer page number
    slide.addText(`${num} / 11`, {
        x: W - 1.2, y: H - 0.4, w: 0.7, h: 0.3,
        fontFace: FONT, fontSize: 9, color: C.slate, align: "right", margin: 0
    });
    slide.addText("System Flow · ระบบราคากลาง", {
        x: 0.5, y: H - 0.4, w: 6, h: 0.3,
        fontFace: FONT, fontSize: 9, color: C.slate, margin: 0
    });
}

function codeCard(slide, x, y, w, h, lines, title, accent) {
    if (title) {
        slide.addShape(pres.shapes.RECTANGLE, {
            x, y, w, h: 0.32, fill: { color: accent }, line: { type: "none" }
        });
        slide.addText(title, {
            x: x + 0.15, y: y + 0.03, w: w - 0.3, h: 0.26,
            fontFace: FONT, fontSize: 11, color: C.white, bold: true, margin: 0
        });
        y += 0.32;
        h -= 0.32;
    }
    slide.addShape(pres.shapes.RECTANGLE, {
        x, y, w, h,
        fill: { color: C.bgCode },
        line: { color: C.line, width: 1 },
    });
    // Render lines as breakLine text
    const runs = lines.map((line, i) => ({
        text: line || " ",
        options: { breakLine: i < lines.length - 1 }
    }));
    slide.addText(runs, {
        x: x + 0.15, y: y + 0.1, w: w - 0.3, h: h - 0.2,
        fontFace: MONO, fontSize: 9, color: C.inkDark, valign: "top", margin: 0
    });
}

function featureCard(slide, x, y, w, h, iconData, color, name, desc) {
    slide.addShape(pres.shapes.RECTANGLE, {
        x, y, w, h,
        fill: { color: C.white }, line: { color: C.line, width: 0.75 },
        shadow: sh()
    });
    // Left accent bar
    slide.addShape(pres.shapes.RECTANGLE, {
        x, y, w: 0.08, h,
        fill: { color }, line: { type: "none" }
    });
    // Icon circle
    slide.addShape(pres.shapes.OVAL, {
        x: x + 0.25, y: y + 0.2, w: 0.5, h: 0.5,
        fill: { color }, line: { type: "none" }
    });
    slide.addImage({ data: iconData, x: x + 0.36, y: y + 0.31, w: 0.28, h: 0.28 });
    // Name + description
    slide.addText(name, {
        x: x + 0.9, y: y + 0.15, w: w - 1.0, h: 0.35,
        fontFace: FONT, fontSize: 15, color: C.inkDark, bold: true, margin: 0
    });
    slide.addText(desc, {
        x: x + 0.9, y: y + 0.5, w: w - 1.0, h: h - 0.6,
        fontFace: FONT, fontSize: 10.5, color: C.slate, valign: "top", margin: 0
    });
}

// ============================================================
// SLIDE 1 — TITLE
// ============================================================
async function buildTitleSlide() {
    const s = pres.addSlide();
    s.background = { color: C.navy };

    // Decorative accent band on right
    s.addShape(pres.shapes.RECTANGLE, {
        x: W - 4.5, y: 0, w: 4.5, h: H,
        fill: { color: "152F54" }, line: { type: "none" }
    });
    s.addShape(pres.shapes.RECTANGLE, {
        x: W - 4.5, y: 0, w: 0.08, h: H,
        fill: { color: C.blue }, line: { type: "none" }
    });

    // Big logo circle
    const iconLogo = await icon(FA.FaFileInvoiceDollar, "FFFFFF", 256);
    s.addShape(pres.shapes.OVAL, {
        x: W - 3.2, y: 2.6, w: 1.7, h: 1.7,
        fill: { color: C.blue }, line: { type: "none" },
        shadow: sh()
    });
    s.addImage({ data: iconLogo, x: W - 2.65, y: 3.15, w: 0.6, h: 0.6 });

    // Kicker
    s.addText("SYSTEM DOCUMENTATION", {
        x: 0.8, y: 1.2, w: 8, h: 0.4,
        fontFace: FONT, fontSize: 13, color: C.iceBlue,
        bold: true, charSpacing: 6, margin: 0
    });

    // Title
    s.addText("System Flow", {
        x: 0.8, y: 1.7, w: 9, h: 1.3,
        fontFace: FONT, fontSize: 64, color: C.white, bold: true, margin: 0
    });

    // Thai subtitle
    s.addText("ระบบราคากลาง", {
        x: 0.8, y: 3.0, w: 9, h: 0.9,
        fontFace: FONT, fontSize: 36, color: C.iceBlue, bold: true, margin: 0
    });

    // English subtitle
    s.addText("Price Reference Management", {
        x: 0.8, y: 3.85, w: 9, h: 0.5,
        fontFace: FONT, fontSize: 18, color: C.iceBlue, italic: true, margin: 0
    });

    // Divider line
    s.addShape(pres.shapes.LINE, {
        x: 0.8, y: 4.7, w: 1.5, h: 0,
        line: { color: C.blue, width: 3 }
    });

    // Stack
    s.addText("Laravel 12  ·  Livewire 3  ·  Alpine.js  ·  Tailwind v4  ·  PostgreSQL", {
        x: 0.8, y: 5.0, w: 8.5, h: 0.4,
        fontFace: FONT, fontSize: 14, color: C.white, margin: 0
    });
    s.addText("เอกสาร flow การทำงานของระบบสำหรับ onboarding, debugging, และวางแผน feature", {
        x: 0.8, y: 5.5, w: 8.5, h: 0.4,
        fontFace: FONT, fontSize: 12, color: C.iceBlue, margin: 0
    });

    // Bottom watermark
    s.addText("Price-MDES_Laravel", {
        x: 0.8, y: H - 0.6, w: 6, h: 0.3,
        fontFace: MONO, fontSize: 10, color: C.iceBlue, margin: 0
    });
}

// ============================================================
// SLIDE 2 — AGENDA
// ============================================================
async function buildAgendaSlide() {
    const s = pres.addSlide();
    s.background = { color: C.bgSoft };

    // Header
    s.addShape(pres.shapes.RECTANGLE, {
        x: 0, y: 0, w: W, h: 0.06, fill: { color: C.navy }, line: { type: "none" }
    });
    s.addText("AGENDA", {
        x: 0.55, y: 0.5, w: 6, h: 0.35,
        fontFace: FONT, fontSize: 12, color: C.slate, bold: true, charSpacing: 5, margin: 0
    });
    s.addText("สารบัญ — ภาพรวม flow ทั้งหมดของระบบ", {
        x: 0.55, y: 0.85, w: 11, h: 0.6,
        fontFace: FONT, fontSize: 28, color: C.inkDark, bold: true, margin: 0
    });

    const items = [
        { n: "01", name: "Authentication",      th: "เข้าสู่ระบบ + session",          color: C.blue },
        { n: "02", name: "Authorization",       th: "RBAC + Permission cache",          color: C.purple },
        { n: "03", name: "Dashboard",           th: "KPI + Chart.js + Activity",        color: C.emerald },
        { n: "04", name: "Product CRUD",        th: "Filter, bulk-delete, compare",     color: C.cyan },
        { n: "05", name: "Spec & Comparison",   th: "TOR + 3-vendor compare",           color: C.amber },
        { n: "06", name: "Search & Audit",      th: "Cross-model + history filter",     color: C.red },
        { n: "07", name: "PDF Export",          th: "Specs::display() guard",           color: C.navy },
        { n: "08", name: "Livewire Lifecycle",  th: "Request → re-render flow",         color: C.slate },
        { n: "09", name: "Data & Patterns",     th: "JSONB, BE date, cache",            color: C.purple },
        { n: "10", name: "Verification",        th: "37 tests · how to verify",         color: C.emerald },
    ];

    // 5 columns × 2 rows grid
    const cols = 5, gap = 0.25;
    const cardW = (W - 1.1 - (cols - 1) * gap) / cols;
    const cardH = 1.8;
    const startX = 0.55;
    const startY = 2.0;

    items.forEach((it, i) => {
        const r = Math.floor(i / cols);
        const c = i % cols;
        const x = startX + c * (cardW + gap);
        const y = startY + r * (cardH + gap);

        s.addShape(pres.shapes.RECTANGLE, {
            x, y, w: cardW, h: cardH,
            fill: { color: C.white }, line: { color: C.line, width: 0.75 },
            shadow: sh()
        });
        s.addShape(pres.shapes.RECTANGLE, {
            x, y, w: cardW, h: 0.08,
            fill: { color: it.color }, line: { type: "none" }
        });
        s.addText(it.n, {
            x: x + 0.15, y: y + 0.2, w: cardW - 0.3, h: 0.4,
            fontFace: FONT, fontSize: 24, color: it.color, bold: true, margin: 0
        });
        s.addText(it.name, {
            x: x + 0.15, y: y + 0.7, w: cardW - 0.3, h: 0.35,
            fontFace: FONT, fontSize: 12, color: C.inkDark, bold: true, margin: 0
        });
        s.addText(it.th, {
            x: x + 0.15, y: y + 1.05, w: cardW - 0.3, h: 0.6,
            fontFace: FONT, fontSize: 9.5, color: C.slate, valign: "top", margin: 0
        });
    });

    // Footer
    s.addText("11 slides · ~10 minutes read", {
        x: 0.55, y: H - 0.5, w: 6, h: 0.3,
        fontFace: FONT, fontSize: 10, color: C.slate, italic: true, margin: 0
    });
}

// ============================================================
// SLIDE 3 — AUTHENTICATION FLOW
// ============================================================
async function buildAuthSlide() {
    const s = pres.addSlide();
    s.background = { color: C.white };
    const iconLock = await icon(FA.FaSignInAlt, "FFFFFF", 256);
    pageHeader(s, 3, "01 · AUTHENTICATION", "Authentication & Session Flow", iconLock, C.blue);

    // Left side: flow boxes
    const boxX = 0.55, boxW = 5.5, boxH = 0.55, gap = 0.18;
    const startY = 1.7;

    const steps = [
        { label: "User → GET /login",         note: "guest-only · throttle:5,1", color: C.slate },
        { label: "Livewire Auth\\Login::login()", note: "validate username + password", color: C.navy },
        { label: "Auth::attempt([username, password], remember)", note: "DB lookup · bcrypt verify", color: C.blue },
        { label: "session()->regenerate()",   note: "rotate session id (prevent fixation)", color: C.emerald },
        { label: "redirect → /dashboard",     note: "render Livewire Dashboard component", color: C.purple },
    ];

    steps.forEach((step, i) => {
        const y = startY + i * (boxH + gap + 0.1);
        // step number
        s.addShape(pres.shapes.OVAL, {
            x: boxX, y: y, w: boxH, h: boxH,
            fill: { color: step.color }, line: { type: "none" }
        });
        s.addText(String(i + 1), {
            x: boxX, y: y, w: boxH, h: boxH,
            fontFace: FONT, fontSize: 16, color: C.white, bold: true,
            align: "center", valign: "middle", margin: 0
        });
        // box
        s.addShape(pres.shapes.RECTANGLE, {
            x: boxX + boxH + 0.15, y, w: boxW - boxH - 0.15, h: boxH,
            fill: { color: C.bgSoft }, line: { color: C.line, width: 0.75 }
        });
        s.addText(step.label, {
            x: boxX + boxH + 0.3, y: y + 0.04, w: boxW - boxH - 0.45, h: 0.28,
            fontFace: MONO, fontSize: 10, color: C.inkDark, bold: true, margin: 0
        });
        s.addText(step.note, {
            x: boxX + boxH + 0.3, y: y + 0.3, w: boxW - boxH - 0.45, h: 0.22,
            fontFace: FONT, fontSize: 9, color: C.slate, margin: 0
        });
        // arrow (except last)
        if (i < steps.length - 1) {
            s.addText("▼", {
                x: boxX + boxH / 2 - 0.15, y: y + boxH, w: 0.3, h: 0.2,
                fontFace: FONT, fontSize: 10, color: step.color, bold: true,
                align: "center", margin: 0
            });
        }
    });

    // Right side: callout cards
    const rx = 6.7;
    s.addText("KEY POINTS", {
        x: rx, y: 1.7, w: 6, h: 0.3,
        fontFace: FONT, fontSize: 11, color: C.slate, bold: true, charSpacing: 4, margin: 0
    });

    const points = [
        { icon: "🔒", title: "Throttle Brute-force",
          desc: "throttle:5,1 = 5 fails per minute → throw 429" },
        { icon: "🔄", title: "Session Regenerate",
          desc: "ป้องกัน session fixation หลัง login สำเร็จ" },
        { icon: "🚪", title: "Logout (POST)",
          desc: "Auth::logout() + session invalidate + CSRF token regenerate" },
        { icon: "👤", title: "Role Stored on users.role",
          desc: "admin | editor | viewer + custom slug ใน roles table" },
    ];

    points.forEach((pt, i) => {
        const y = 2.1 + i * 1.05;
        s.addShape(pres.shapes.RECTANGLE, {
            x: rx, y, w: 6.0, h: 0.9,
            fill: { color: C.white }, line: { color: C.line, width: 0.75 },
            shadow: sh()
        });
        s.addShape(pres.shapes.RECTANGLE, {
            x: rx, y, w: 0.06, h: 0.9,
            fill: { color: C.blue }, line: { type: "none" }
        });
        s.addText(pt.title, {
            x: rx + 0.2, y: y + 0.08, w: 5.7, h: 0.35,
            fontFace: FONT, fontSize: 13, color: C.inkDark, bold: true, margin: 0
        });
        s.addText(pt.desc, {
            x: rx + 0.2, y: y + 0.42, w: 5.7, h: 0.4,
            fontFace: FONT, fontSize: 10, color: C.slate, valign: "top", margin: 0
        });
    });
}

// ============================================================
// SLIDE 4 — AUTHORIZATION (RBAC)
// ============================================================
async function buildAuthzSlide() {
    const s = pres.addSlide();
    s.background = { color: C.white };
    const iconShield = await icon(FA.FaShieldAlt, "FFFFFF", 256);
    pageHeader(s, 4, "02 · AUTHORIZATION", "Role-Based Access Control (DB-driven)", iconShield, C.purple);

    // Three role columns
    const cols = [
        { name: "ADMIN", role: "ผู้ดูแลระบบ", color: C.navy,
          perms: ["✓ ทุก section · ทุก action", "✓ จัดการ users · roles · permissions",
                  "✓ จัดการ categories · brands", "✓ Override ทุกอย่าง (short-circuit)"],
          note: "User::isAdmin() → true" },
        { name: "EDITOR", role: "ผู้แก้ไขข้อมูล", color: C.blue,
          perms: ["✓ Products · Specs · Comparisons (CRUD)",
                  "✓ Guidelines · Recommendations",
                  "✓ Import / Export Excel & PDF",
                  "✗ Users · Roles · Permissions"],
          note: "MenuPermission rows ใน DB" },
        { name: "VIEWER", role: "ผู้ดูข้อมูล", color: C.slate,
          perms: ["✓ Products (read-only)",
                  "✓ Audit log (เฉพาะของตัวเอง)",
                  "✗ Specs / Comparisons (เมนูซ่อน)",
                  "✗ ไม่มีปุ่ม edit / add / delete"],
          note: "can_see = false → no menu" },
    ];

    const colW = 4.0, colH = 3.4, gap = 0.2;
    const startX = (W - (cols.length * colW + (cols.length - 1) * gap)) / 2;

    cols.forEach((col, i) => {
        const x = startX + i * (colW + gap);
        const y = 1.7;
        s.addShape(pres.shapes.RECTANGLE, {
            x, y, w: colW, h: colH,
            fill: { color: C.white }, line: { color: C.line, width: 0.75 },
            shadow: sh()
        });
        // header band
        s.addShape(pres.shapes.RECTANGLE, {
            x, y, w: colW, h: 0.85, fill: { color: col.color }, line: { type: "none" }
        });
        s.addText(col.name, {
            x: x + 0.2, y: y + 0.1, w: colW - 0.4, h: 0.35,
            fontFace: FONT, fontSize: 16, color: C.white, bold: true, charSpacing: 3, margin: 0
        });
        s.addText(col.role, {
            x: x + 0.2, y: y + 0.45, w: colW - 0.4, h: 0.3,
            fontFace: FONT, fontSize: 11, color: C.iceBlue, margin: 0
        });

        // permissions
        const runs = col.perms.map((p, j) => ({
            text: p,
            options: { breakLine: j < col.perms.length - 1, color: p.startsWith("✓") ? C.emerald : C.red }
        }));
        s.addText(runs, {
            x: x + 0.25, y: y + 1.0, w: colW - 0.5, h: 1.8,
            fontFace: FONT, fontSize: 11, valign: "top", paraSpaceAfter: 4, margin: 0
        });

        // bottom note
        s.addShape(pres.shapes.RECTANGLE, {
            x: x + 0.2, y: y + colH - 0.55, w: colW - 0.4, h: 0.4,
            fill: { color: C.bgCode }, line: { type: "none" }
        });
        s.addText(col.note, {
            x: x + 0.3, y: y + colH - 0.5, w: colW - 0.6, h: 0.3,
            fontFace: MONO, fontSize: 9, color: C.slate, margin: 0
        });
    });

    // Bottom code card
    codeCard(s, 0.55, 5.3, W - 1.1, 1.65, [
        "// Resolution flow (cached per role)",
        "User::hasPermission('specs', 'export')",
        "  → if isAdmin() return true;",
        "  → User::loadMenuPermissions()  [static cache: $menuCache[roleSlug]]",
        "       ├─ Role::where('slug', $role)->first()",
        "       └─ MenuPermission::where('role_id', $role->id)->get()  → matrix",
        "  → return $cache['specs']['can_export'] ?? false;",
    ], "PERMISSION RESOLUTION FLOW", C.purple);
}

// ============================================================
// SLIDE 5 — MAIN USER FLOWS OVERVIEW (icon grid)
// ============================================================
async function buildFlowsOverviewSlide() {
    const s = pres.addSlide();
    s.background = { color: C.bgSoft };
    const iconList = await icon(FA.FaSitemap, "FFFFFF", 256);
    pageHeader(s, 5, "03 · MAIN FLOWS", "Main User Flows — Core Modules", iconList, C.emerald);

    const features = [
        { icon: FA.FaTachometerAlt, color: C.blue,    name: "Dashboard",
          desc: "KPI cards + Chart.js bar/line + recent products + activity feed" },
        { icon: FA.FaBoxes,         color: C.navy,    name: "Products",
          desc: "Filter, sort, paginate, bulk-delete, attach files, compare (max 3)" },
        { icon: FA.FaFileAlt,       color: C.purple,  name: "Specs (TOR)",
          desc: "Characteristics templates · year/month filter · import/export Excel + PDF" },
        { icon: FA.FaBalanceScale,  color: C.amber,   name: "Comparisons",
          desc: "3-vendor price compare · bulk Excel (N sheets) · PDF landscape" },
        { icon: FA.FaSearch,        color: C.cyan,    name: "Search",
          desc: "Cross-model live search · debounce 350ms · role-aware sections" },
        { icon: FA.FaHistory,       color: C.red,     name: "Audit Log",
          desc: "Union ของ product + spec histories · viewer เห็นเฉพาะของตัวเอง" },
        { icon: FA.FaFilePdf,       color: C.emerald, name: "PDF Export",
          desc: "DomPDF · TH Sarabun · Specs::display() guard กัน array crash" },
    ];

    // 4 columns × 2 rows
    const cols = 4;
    const gap = 0.2;
    const cardW = (W - 1.1 - (cols - 1) * gap) / cols;
    const cardH = 2.1;
    const startX = 0.55, startY = 1.7;

    for (let i = 0; i < features.length; i++) {
        const r = Math.floor(i / cols);
        const c = i % cols;
        const x = startX + c * (cardW + gap);
        const y = startY + r * (cardH + gap);
        const ic = await icon(features[i].icon, "FFFFFF", 256);
        featureCard(s, x, y, cardW, cardH, ic, features[i].color, features[i].name, features[i].desc);
    }

    // Highlight in the last empty cell (4th column, 2nd row)
    const x = startX + 3 * (cardW + gap);
    const y = startY + 1 * (cardH + gap);
    s.addShape(pres.shapes.RECTANGLE, {
        x, y, w: cardW, h: cardH,
        fill: { color: C.navy }, line: { type: "none" },
        shadow: sh()
    });
    s.addText("ALL features", {
        x: x + 0.25, y: y + 0.2, w: cardW - 0.5, h: 0.4,
        fontFace: FONT, fontSize: 14, color: C.iceBlue, bold: true, margin: 0
    });
    s.addText("ผ่าน Livewire components +\nDB-driven permission +\nSession-based cart\n(CompareCart, max 3)", {
        x: x + 0.25, y: y + 0.65, w: cardW - 0.5, h: 1.3,
        fontFace: FONT, fontSize: 11, color: C.white, valign: "top", margin: 0
    });
}

// ============================================================
// SLIDE 6 — SPEC & COMPARISON FLOW
// ============================================================
async function buildSpecComparisonSlide() {
    const s = pres.addSlide();
    s.background = { color: C.white };
    const iconCompare = await icon(FA.FaClipboardList, "FFFFFF", 256);
    pageHeader(s, 6, "04 · SPEC & COMPARISON", "Spec (TOR) + Comparison Workflow", iconCompare, C.amber);

    // LEFT — Spec Template Flow
    codeCard(s, 0.55, 1.7, 6.0, 5.0, [
        "CharacteristicsList (/specs)",
        "  ├─ year/month filter",
        "  ├─ view toggle (table/card)",
        "  ├─ bulk export (Excel)",
        "  └─ import Excel",
        "",
        "Create / Edit:",
        "  └─ dispatch('open-characteristics-form')",
        "       └─ CharacteristicsForm modal",
        "            ├─ validate(name required)",
        "            ├─ cleanSpecs:",
        "            │    map(string)->filter empty",
        "            │    [data-integrity guard]",
        "            ├─ updateOrCreate(['id'=>uuid|existing])",
        "            ├─ $spec->histories()->create(...)",
        "            └─ dispatch('characteristics-saved')",
        "                  → list re-renders",
        "",
        "Import (Excel → DB):",
        "  └─ CharacteristicsImport::collection()",
        "       ├─ validate name, category, year, month, budget",
        "       ├─ DB transaction (atomic)",
        "       └─ row errors collected",
        "",
        "Row actions:",
        "  Eye → Detail · Compare → /compare?spec=...",
        "  Excel → /specs/{id}/export",
        "  PDF   → /specs/{id}/export/pdf",
    ], "SPEC TEMPLATE CRUD", C.purple);

    // RIGHT — Comparison Flow
    codeCard(s, 6.75, 1.7, 6.0, 5.0, [
        "ComparisonList (/comparisons)",
        "  ├─ filter · bulk select · bulk export",
        "  └─ row actions: detail · Excel · PDF · edit",
        "",
        "Create / Edit:",
        "  └─ dispatch('open-comparison-form')",
        "       └─ ComparisonForm modal",
        "            ├─ Choose base spec (optional)",
        "            │   → populates expected specs",
        "            ├─ Define 1-3 vendors",
        "            │   (name, brand, model, price, specs)",
        "            ├─ Save:",
        "            │    Comparison::updateOrCreate",
        "            │    + sync ComparisonVendor rows",
        "            └─ dispatch('comparison-saved')",
        "",
        "Bulk Excel Export:",
        "  GET /comparisons/export/bulk?ids=...",
        "  → BulkComparisonsExport",
        "       implements WithMultipleSheets",
        "       (1 comparison = 1 sheet)",
        "",
        "Side-by-Side Compare (/compare):",
        "  CompareView reads CompareCart::ids()",
        "  → render up to 3 columns",
        "  Session key: compare_ids (max 3)",
    ], "COMPARISON (3-VENDOR)", C.amber);
}

// ============================================================
// SLIDE 7 — PDF EXPORT (with bug story)
// ============================================================
async function buildPdfExportSlide() {
    const s = pres.addSlide();
    s.background = { color: C.white };
    const iconPdf = await icon(FA.FaFilePdf, "FFFFFF", 256);
    pageHeader(s, 7, "05 · PDF EXPORT", "PDF Export + Bug Fix Story", iconPdf, C.red);

    // Top: the bug callout
    s.addShape(pres.shapes.RECTANGLE, {
        x: 0.55, y: 1.7, w: W - 1.1, h: 0.8,
        fill: { color: "FEF2F2" }, line: { color: C.red, width: 1 }
    });
    s.addText("🐛", {
        x: 0.7, y: 1.78, w: 0.5, h: 0.6,
        fontFace: FONT, fontSize: 24, color: C.red, margin: 0
    });
    s.addText("THE BUG ที่พบระหว่างทดสอบ", {
        x: 1.25, y: 1.78, w: 4, h: 0.3,
        fontFace: FONT, fontSize: 11, color: C.red, bold: true, charSpacing: 4, margin: 0
    });
    s.addText("htmlspecialchars(): Argument #1 ($string) must be of type string, array given", {
        x: 1.25, y: 2.05, w: W - 2, h: 0.4,
        fontFace: MONO, fontSize: 11, color: C.inkDark, margin: 0
    });

    // Two columns: BEFORE / AFTER
    const colW = 6.05;
    const halfH = 4.0;

    // BEFORE
    codeCard(s, 0.55, 2.7, colW, halfH, [
        "// resources/views/exports/spec-pdf.blade.php",
        "",
        "<td class=\"field-value\">",
        "  {{ $value ?: '-' }}",
        "</td>",
        "",
        "⚠️ ถ้า $value เป็น array",
        "    → Blade เรียก htmlspecialchars()",
        "    → TypeError, 500 Internal Server Error",
        "",
        "⚠️ Bug คลาสเดียวกันอยู่ใน",
        "    comparison-pdf.blade.php บรรทัด 205-208",
        "    (4 จุด · $spec + vendor 0..2)",
        "",
        "⚠️ Test coverage = 0 → bug หลุดได้",
    ], "❌ BEFORE", C.red);

    // AFTER
    codeCard(s, 6.7, 2.7, colW, halfH, [
        "// app/Support/Specs.php",
        "public static function display($value): string {",
        "    if ($value === null || $value === '') return '-';",
        "    return is_array($value)",
        "        ? json_encode($value, JSON_UNESCAPED_UNICODE)",
        "        : (string) $value;",
        "}",
        "",
        "// spec-pdf.blade.php (line 101)",
        "{{ \\App\\Support\\Specs::display($value) }}",
        "",
        "// comparison-pdf.blade.php (lines 205-208)",
        "All 4 spec-value cells wrapped with display()",
        "",
        "✓ + 7 PdfExportTest cases (incl. regression)",
        "✓ + write-path guard in CharacteristicsForm",
    ], "✅ AFTER", C.emerald);
}

// ============================================================
// SLIDE 8 — LIVEWIRE LIFECYCLE
// ============================================================
async function buildLifecycleSlide() {
    const s = pres.addSlide();
    s.background = { color: C.white };
    const iconSync = await icon(FA.FaSyncAlt, "FFFFFF", 256);
    pageHeader(s, 8, "06 · LIVEWIRE", "Livewire Request Lifecycle", iconSync, C.cyan);

    // Sequence: Browser → Server → DOM morph
    const xLeft = 1.0, xCenter = 6.65, xRight = 12.3;
    const lineY = 1.7;

    // Three actor headers
    const actors = [
        { x: xLeft - 0.8,  label: "BROWSER",   color: C.navy,    sub: "Alpine + Livewire JS" },
        { x: xCenter - 0.8, label: "SERVER",   color: C.blue,    sub: "Laravel + Livewire" },
        { x: xRight - 1.2, label: "DOM",       color: C.purple,  sub: "Morphed in place" },
    ];
    actors.forEach(a => {
        s.addShape(pres.shapes.RECTANGLE, {
            x: a.x, y: lineY, w: 1.6, h: 0.55,
            fill: { color: a.color }, line: { type: "none" }
        });
        s.addText(a.label, {
            x: a.x, y: lineY + 0.05, w: 1.6, h: 0.3,
            fontFace: FONT, fontSize: 12, color: C.white, bold: true,
            align: "center", charSpacing: 3, margin: 0
        });
        s.addText(a.sub, {
            x: a.x, y: lineY + 0.32, w: 1.6, h: 0.25,
            fontFace: FONT, fontSize: 9, color: C.iceBlue,
            align: "center", margin: 0
        });
    });

    // Vertical "lifelines"
    [xLeft, xCenter, xRight].forEach(x => {
        s.addShape(pres.shapes.LINE, {
            x, y: lineY + 0.55, w: 0, h: 4.5,
            line: { color: C.line, width: 1.5, dashType: "dash" }
        });
    });

    // Steps between actors
    const steps = [
        { y: 2.4, from: xLeft, to: xCenter, label: "1. POST /livewire/update",
          sub: "payload: snapshot + calls + updates" },
        { y: 3.05, from: xCenter, to: xCenter, label: "2. Hydrate component from snapshot",
          sub: "restore public state" },
        { y: 3.7, from: xCenter, to: xCenter, label: "3. Invoke method (save, open, toggle)",
          sub: "+ validate + re-render Blade" },
        { y: 4.35, from: xCenter, to: xLeft, label: "4. Response: HTML diff + new snapshot",
          sub: "" },
        { y: 5.0, from: xLeft, to: xRight, label: "5. Browser morphs DOM in place",
          sub: "no full re-render" },
        { y: 5.65, from: xLeft, to: xCenter, label: "6. dispatch('X-saved') fires #[On] listener",
          sub: "→ sibling Livewire components re-render" },
    ];

    steps.forEach(st => {
        const fx = Math.min(st.from, st.to);
        const tw = Math.abs(st.to - st.from);
        if (st.from === st.to) {
            // self loop
            s.addShape(pres.shapes.RECTANGLE, {
                x: st.from + 0.1, y: st.y, w: 4.5, h: 0.5,
                fill: { color: C.bgSoft }, line: { color: C.cyan, width: 1 }
            });
            s.addText(st.label, {
                x: st.from + 0.2, y: st.y + 0.04, w: 4.3, h: 0.25,
                fontFace: FONT, fontSize: 11, color: C.inkDark, bold: true, margin: 0
            });
            s.addText(st.sub, {
                x: st.from + 0.2, y: st.y + 0.27, w: 4.3, h: 0.2,
                fontFace: FONT, fontSize: 9, color: C.slate, margin: 0
            });
        } else {
            // Hide dashed lifelines locally at the label/subtitle rows ONLY at lifeline x positions
            // (so the horizontal arrow line drawn below stays continuous)
            [xLeft, xCenter, xRight].forEach(lx => {
                // patch above arrow (covers label area)
                s.addShape(pres.shapes.RECTANGLE, {
                    x: lx - 0.06, y: st.y - 0.08, w: 0.12, h: 0.30,
                    fill: { color: C.white }, line: { type: "none" }
                });
                // patch below arrow (covers subtitle area)
                s.addShape(pres.shapes.RECTANGLE, {
                    x: lx - 0.06, y: st.y + 0.28, w: 0.12, h: 0.28,
                    fill: { color: C.white }, line: { type: "none" }
                });
            });
            // arrow line on top
            s.addShape(pres.shapes.LINE, {
                x: fx, y: st.y + 0.25, w: tw, h: 0,
                line: { color: C.cyan, width: 2, endArrowType: st.to > st.from ? "triangle" : "none", beginArrowType: st.to < st.from ? "triangle" : "none" }
            });
            const labelW = Math.min(tw - 0.4, 5.0);
            const labelX = fx + (tw - labelW) / 2;
            s.addText(st.label, {
                x: labelX, y: st.y - 0.04, w: labelW, h: 0.25,
                fontFace: FONT, fontSize: 11, color: C.inkDark, bold: true, align: "center", margin: 0
            });
            s.addText(st.sub, {
                x: labelX, y: st.y + 0.3, w: labelW, h: 0.25,
                fontFace: FONT, fontSize: 9, color: C.slate, align: "center", margin: 0
            });
        }
    });

    // Note at bottom
    s.addShape(pres.shapes.RECTANGLE, {
        x: 0.55, y: 6.6, w: W - 1.1, h: 0.4,
        fill: { color: C.bgCode }, line: { type: "none" }
    });
    s.addText("⚙  XAMPP subdir fix: JS patch ใน layouts/app.blade.php แก้ data-update-uri ให้ POST ไปยัง path ที่ถูกต้อง", {
        x: 0.7, y: 6.65, w: W - 1.4, h: 0.3,
        fontFace: FONT, fontSize: 10, color: C.slate, italic: true, margin: 0
    });
}

// ============================================================
// SLIDE 9 — DATA STORAGE
// ============================================================
async function buildStorageSlide() {
    const s = pres.addSlide();
    s.background = { color: C.bgSoft };
    const iconDb = await icon(FA.FaDatabase, "FFFFFF", 256);
    pageHeader(s, 9, "07 · DATA STORAGE", "Data Storage Highlights", iconDb, C.navy);

    const quad = [
        { icon: FA.FaCubes, color: C.blue, title: "JSONB Columns",
          desc: "products / characteristics_templates / comparison_vendors เก็บ specs แบบ flexible per category · ค้นหา ilike ได้",
          code: "specs::text ilike '%intel%'" },
        { icon: FA.FaCalendarAlt, color: C.amber, title: "Buddhist Era Dates",
          desc: "เก็บเป็น string 'YYYY-MM-DD BE' ไม่ใช้ Carbon เพื่อให้แสดงผลตรงกับเอกสารราชการ",
          code: "'2569-05-21' (= 2026-05-21 CE)" },
        { icon: FA.FaHistory, color: C.purple, title: "Audit Trail (Histories)",
          desc: "CRUD ทุกครั้งบน products + specs เขียน row ใน *_histories table → source of truth สำหรับ audit log + activity feed",
          code: "$product->histories()->create(...)" },
        { icon: FA.FaShieldAlt, color: C.emerald, title: "Permission Cache",
          desc: "roles + menu_permissions matrix · runtime cache User::$menuCache[$roleSlug] · clear ด้วย User::clearMenuCache()",
          code: "private static array $menuCache = [];" },
    ];

    const cols = 2;
    const gap = 0.25;
    const cardW = (W - 1.1 - gap) / cols;
    const cardH = 2.4;
    const startX = 0.55, startY = 1.7;

    for (let i = 0; i < quad.length; i++) {
        const r = Math.floor(i / cols);
        const c = i % cols;
        const x = startX + c * (cardW + gap);
        const y = startY + r * (cardH + gap);

        s.addShape(pres.shapes.RECTANGLE, {
            x, y, w: cardW, h: cardH,
            fill: { color: C.white }, line: { color: C.line, width: 0.75 },
            shadow: sh()
        });
        s.addShape(pres.shapes.RECTANGLE, {
            x, y, w: cardW, h: 0.08, fill: { color: quad[i].color }, line: { type: "none" }
        });

        // Icon
        const ic = await icon(quad[i].icon, "FFFFFF", 256);
        s.addShape(pres.shapes.OVAL, {
            x: x + 0.3, y: y + 0.35, w: 0.65, h: 0.65,
            fill: { color: quad[i].color }, line: { type: "none" }
        });
        s.addImage({ data: ic, x: x + 0.43, y: y + 0.48, w: 0.4, h: 0.4 });

        s.addText(quad[i].title, {
            x: x + 1.1, y: y + 0.3, w: cardW - 1.3, h: 0.4,
            fontFace: FONT, fontSize: 17, color: C.inkDark, bold: true, margin: 0
        });
        s.addText(quad[i].desc, {
            x: x + 1.1, y: y + 0.75, w: cardW - 1.3, h: 1.0,
            fontFace: FONT, fontSize: 11, color: C.slate, valign: "top", margin: 0
        });
        // code mini-box
        s.addShape(pres.shapes.RECTANGLE, {
            x: x + 0.3, y: y + cardH - 0.55, w: cardW - 0.6, h: 0.4,
            fill: { color: C.bgCode }, line: { color: C.line, width: 0.5 }
        });
        s.addText(quad[i].code, {
            x: x + 0.4, y: y + cardH - 0.5, w: cardW - 0.8, h: 0.3,
            fontFace: MONO, fontSize: 10, color: C.inkDark, margin: 0
        });
    }
}

// ============================================================
// SLIDE 10 — PATTERNS
// ============================================================
async function buildPatternsSlide() {
    const s = pres.addSlide();
    s.background = { color: C.white };
    const iconCogs = await icon(FA.FaCogs, "FFFFFF", 256);
    pageHeader(s, 10, "08 · PATTERNS", "Notable Implementation Patterns", iconCogs, C.cyan);

    const patterns = [
        { n: "01", color: C.blue,
          title: "Modal-by-event",
          desc: "Sibling components communicate ผ่าน dispatch('open-X-form') / #[On('open-X-form')] เลี่ยง prop-drilling" },
        { n: "02", color: C.purple,
          title: "Post-save auto-refresh",
          desc: "dispatch('X-saved') → empty #[On] method บน sibling list → Livewire re-renders อัตโนมัติ" },
        { n: "03", color: C.emerald,
          title: "Static menu cache",
          desc: "private static array $menuCache ใน User model; clear ด้วย User::clearMenuCache() หลังบันทึก permission" },
        { n: "04", color: C.amber,
          title: "JSON-via-window",
          desc: "Chart.js data ส่งผ่าน <script>window._x = @json(...)</script> เลี่ยง Alpine x-init parser error" },
        { n: "05", color: C.cyan,
          title: "TH Sarabun font for Excel",
          desc: "Export classes implement WithStyles → $sheet->getStyle(...)->getFont()->setName('TH Sarabun New')" },
        { n: "06", color: C.red,
          title: "Driver-portable migration",
          desc: "if (DB::getDriverName() === 'pgsql') { raw DDL } else { rebuild table } → SQLite tests work" },
        { n: "07", color: C.navy,
          title: "Render-layer guard",
          desc: "Specs::display($v) แปลง array → json_encode ก่อน htmlspecialchars · กัน PDF crash ที่จุดเดียว" },
    ];

    // 7 items in 2-column layout — sized to fit 4 rows × cardH within 5.3" (1.7 → 7.0)
    const cols = 2;
    const gap = 0.15;
    const cardW = (W - 1.1 - gap) / cols;
    const cardH = 1.2;  // 4 rows × 1.2 + 3 × 0.15 = 5.25 ≤ 5.3 ✓
    const startX = 0.55, startY = 1.7;

    patterns.forEach((p, i) => {
        const r = Math.floor(i / cols);
        const c = i % cols;
        const x = startX + c * (cardW + gap);
        const y = startY + r * (cardH + gap);

        s.addShape(pres.shapes.RECTANGLE, {
            x, y, w: cardW, h: cardH,
            fill: { color: C.bgSoft }, line: { color: C.line, width: 0.75 }
        });
        // Big number on left
        s.addShape(pres.shapes.RECTANGLE, {
            x, y, w: 0.8, h: cardH,
            fill: { color: p.color }, line: { type: "none" }
        });
        s.addText(p.n, {
            x, y: y + (cardH - 0.45) / 2, w: 0.8, h: 0.45,
            fontFace: FONT, fontSize: 22, color: C.white, bold: true,
            align: "center", margin: 0
        });
        s.addText(p.title, {
            x: x + 0.95, y: y + 0.15, w: cardW - 1.1, h: 0.3,
            fontFace: FONT, fontSize: 13, color: C.inkDark, bold: true, margin: 0
        });
        s.addText(p.desc, {
            x: x + 0.95, y: y + 0.48, w: cardW - 1.1, h: 0.65,
            fontFace: FONT, fontSize: 10, color: C.slate, valign: "top", margin: 0
        });
    });
}

// ============================================================
// SLIDE 11 — VERIFICATION / STATS
// ============================================================
async function buildVerifySlide() {
    const s = pres.addSlide();
    s.background = { color: C.navy };

    // Accent
    s.addShape(pres.shapes.RECTANGLE, {
        x: 0, y: 0, w: W, h: 0.08, fill: { color: C.blue }, line: { type: "none" }
    });

    s.addText("CONCLUSION", {
        x: 0.7, y: 0.5, w: 6, h: 0.4,
        fontFace: FONT, fontSize: 13, color: C.iceBlue, bold: true, charSpacing: 5, margin: 0
    });
    s.addText("Verification & Status", {
        x: 0.7, y: 0.9, w: 11, h: 0.7,
        fontFace: FONT, fontSize: 32, color: C.white, bold: true, margin: 0
    });

    // Big stats row
    const stats = [
        { num: "37",  unit: "tests",         label: "passing  · 89 assertions",  color: C.emerald },
        { num: "16",  unit: "routes",        label: "live · CRUD + Export",      color: C.blue    },
        { num: "11",  unit: "Livewire",      label: "components · DB-driven",    color: C.purple  },
        { num: "0",   unit: "errors",        label: "หลัง Specs::display() fix", color: C.amber   },
    ];

    const sw = (W - 1.4 - 3 * 0.3) / 4;
    const sy = 1.95;
    stats.forEach((st, i) => {
        const x = 0.7 + i * (sw + 0.3);
        s.addShape(pres.shapes.RECTANGLE, {
            x, y: sy, w: sw, h: 2.0,
            fill: { color: "152F54" }, line: { color: st.color, width: 1 }
        });
        s.addShape(pres.shapes.RECTANGLE, {
            x, y: sy, w: sw, h: 0.06,
            fill: { color: st.color }, line: { type: "none" }
        });
        s.addText(st.num, {
            x, y: sy + 0.3, w: sw, h: 1.0,
            fontFace: FONT, fontSize: 64, color: C.white, bold: true,
            align: "center", margin: 0
        });
        s.addText(st.unit, {
            x, y: sy + 1.25, w: sw, h: 0.35,
            fontFace: FONT, fontSize: 16, color: C.white, bold: true,
            align: "center", charSpacing: 3, margin: 0
        });
        s.addText(st.label, {
            x: x + 0.15, y: sy + 1.6, w: sw - 0.3, h: 0.35,
            fontFace: FONT, fontSize: 11, color: C.white,
            align: "center", margin: 0
        });
    });

    // How to verify section
    s.addText("HOW TO VERIFY", {
        x: 0.7, y: 4.3, w: 6, h: 0.4,
        fontFace: FONT, fontSize: 13, color: C.iceBlue, bold: true, charSpacing: 5, margin: 0
    });

    const steps = [
        { n: "1", title: "Run app",       cmd: "php artisan serve --port=8000",
          desc: "Login เป็น admin (admin/admin123) → ทดลอง flow ตามหัวข้อ 03–07" },
        { n: "2", title: "Run tests",     cmd: "php artisan test",
          desc: "ควรเห็น 37 passed (PdfExportTest, ComparisonExportTest, ProductForm, etc.)" },
        { n: "3", title: "Check audit",   cmd: "GET /audit-log",
          desc: "ทุก action ที่ทำใน step 1 ควรมี history row ใหม่ปรากฏที่นี่" },
    ];

    steps.forEach((st, i) => {
        const y = 4.8 + i * 0.7;
        s.addShape(pres.shapes.OVAL, {
            x: 0.7, y, w: 0.45, h: 0.45,
            fill: { color: C.blue }, line: { type: "none" }
        });
        s.addText(st.n, {
            x: 0.7, y, w: 0.45, h: 0.45,
            fontFace: FONT, fontSize: 16, color: C.white, bold: true,
            align: "center", valign: "middle", margin: 0
        });
        s.addText(st.title, {
            x: 1.3, y: y - 0.02, w: 2, h: 0.3,
            fontFace: FONT, fontSize: 13, color: C.white, bold: true, margin: 0
        });
        s.addShape(pres.shapes.RECTANGLE, {
            x: 3.4, y: y + 0.05, w: 4.5, h: 0.35,
            fill: { color: "0F2042" }, line: { color: C.blue, width: 0.5 }
        });
        s.addText(st.cmd, {
            x: 3.5, y: y + 0.08, w: 4.3, h: 0.3,
            fontFace: MONO, fontSize: 11, color: C.white, margin: 0
        });
        s.addText(st.desc, {
            x: 8.0, y: y + 0.08, w: 4.7, h: 0.4,
            fontFace: FONT, fontSize: 11, color: C.white, valign: "top", margin: 0
        });
    });

    // Footer
    s.addText("Documentation: docs/System_Flow_PriceMDES.docx  ·  Plan: ~/.claude/plans/mossy-dazzling-cascade.md", {
        x: 0.7, y: H - 0.45, w: 12, h: 0.3,
        fontFace: FONT, fontSize: 10, color: C.iceBlue, italic: true, margin: 0
    });
}

// ---------- Build everything ----------
(async () => {
    try {
        await buildTitleSlide();
        await buildAgendaSlide();
        await buildAuthSlide();
        await buildAuthzSlide();
        await buildFlowsOverviewSlide();
        await buildSpecComparisonSlide();
        await buildPdfExportSlide();
        await buildLifecycleSlide();
        await buildStorageSlide();
        await buildPatternsSlide();
        await buildVerifySlide();

        const out = path.join(__dirname, "System_Flow_PriceMDES.pptx");
        await pres.writeFile({ fileName: out });
        const size = fs.statSync(out).size;
        console.log(`OK: wrote ${out} (${size} bytes)`);
    } catch (e) {
        console.error("FAILED:", e.message);
        console.error(e.stack);
        process.exit(1);
    }
})();
