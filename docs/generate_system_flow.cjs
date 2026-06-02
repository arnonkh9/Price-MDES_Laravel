// Generate Word document: System Flow ระบบราคากลาง
// Uses globally-installed docx package
const path = require('path');
const fs = require('fs');

// Locate the global docx package
const globalRoot = require('child_process').execSync('npm root -g').toString().trim();
const { Document, Packer, Paragraph, TextRun, Table, TableRow, TableCell,
        Header, Footer, AlignmentType, PageOrientation, LevelFormat,
        TableOfContents, HeadingLevel, BorderStyle, WidthType, ShadingType,
        PageNumber, PageBreak, TabStopType, TabStopPosition } = require(path.join(globalRoot, 'docx'));

// ---------- Style helpers ----------
const FONT = "Tahoma"; // good Thai support, universally available
const MONO = "Consolas";

const codeShading = { fill: "F4F4F4", type: ShadingType.CLEAR, color: "auto" };
const codeBorder = {
    top:    { style: BorderStyle.SINGLE, size: 4, color: "D0D0D0" },
    bottom: { style: BorderStyle.SINGLE, size: 4, color: "D0D0D0" },
    left:   { style: BorderStyle.SINGLE, size: 4, color: "D0D0D0" },
    right:  { style: BorderStyle.SINGLE, size: 4, color: "D0D0D0" },
};

function p(text, opts = {}) {
    return new Paragraph({
        spacing: { after: 80 },
        ...opts,
        children: [new TextRun({ text, font: FONT, size: 22, ...(opts.run || {}) })],
    });
}

function bold(text, size = 22) {
    return new TextRun({ text, font: FONT, size, bold: true });
}

function tx(text, size = 22) {
    return new TextRun({ text, font: FONT, size });
}

function h1(text) {
    return new Paragraph({
        heading: HeadingLevel.HEADING_1,
        spacing: { before: 360, after: 160 },
        children: [new TextRun({ text, font: FONT, size: 32, bold: true, color: "1B3A6B" })],
    });
}

function h2(text) {
    return new Paragraph({
        heading: HeadingLevel.HEADING_2,
        spacing: { before: 240, after: 120 },
        children: [new TextRun({ text, font: FONT, size: 26, bold: true, color: "2563EB" })],
    });
}

function h3(text) {
    return new Paragraph({
        heading: HeadingLevel.HEADING_3,
        spacing: { before: 160, after: 80 },
        children: [new TextRun({ text, font: FONT, size: 22, bold: true, color: "475569" })],
    });
}

// One line of a multi-line code block
function codeLine(text, isFirst = false, isLast = false) {
    return new Paragraph({
        spacing: { after: 0, before: 0, line: 240 },
        shading: codeShading,
        border: {
            top:    isFirst ? codeBorder.top    : { style: BorderStyle.NONE },
            bottom: isLast  ? codeBorder.bottom : { style: BorderStyle.NONE },
            left:   codeBorder.left,
            right:  codeBorder.right,
        },
        indent: { left: 120, right: 120 },
        children: [new TextRun({ text: text || " ", font: MONO, size: 18 })],
    });
}

function codeBlock(lines) {
    return lines.map((line, i) =>
        codeLine(line, i === 0, i === lines.length - 1)
    );
}

function bullet(text) {
    return new Paragraph({
        numbering: { reference: "bullets", level: 0 },
        spacing: { after: 60 },
        children: [new TextRun({ text, font: FONT, size: 22 })],
    });
}

function bulletRich(runs) {
    return new Paragraph({
        numbering: { reference: "bullets", level: 0 },
        spacing: { after: 60 },
        children: runs,
    });
}

function numItem(text) {
    return new Paragraph({
        numbering: { reference: "numbers", level: 0 },
        spacing: { after: 60 },
        children: [new TextRun({ text, font: FONT, size: 22 })],
    });
}

// ---------- Content ----------

const titleBlock = [
    new Paragraph({
        spacing: { before: 0, after: 120 },
        alignment: AlignmentType.CENTER,
        children: [new TextRun({ text: "System Flow", font: FONT, size: 48, bold: true, color: "1B3A6B" })],
    }),
    new Paragraph({
        spacing: { after: 80 },
        alignment: AlignmentType.CENTER,
        children: [new TextRun({ text: "ระบบราคากลาง (Price Reference Management)", font: FONT, size: 32, bold: true, color: "1B3A6B" })],
    }),
    new Paragraph({
        spacing: { after: 360 },
        alignment: AlignmentType.CENTER,
        children: [new TextRun({ text: "Laravel 12 · Livewire 3 · Alpine.js · Tailwind v4 · PostgreSQL", font: FONT, size: 22, italics: true, color: "64748B" })],
    }),
];

const contextSection = [
    h1("Context"),
    p("เอกสารนี้สรุป flow การทำงานทั้งหมดของระบบเพื่อใช้เป็น reference สำหรับ onboarding, debugging, และวางแผน feature ใหม่ รวบรวมจากการอ่านโค้ดจริง (routes, Livewire components, models, helpers) ในระหว่าง session การพัฒนา"),
    new Paragraph({
        spacing: { after: 120, before: 120 },
        children: [
            bold("Stack: "), tx("Laravel 12 · Livewire 3 · Alpine.js · Tailwind CSS v4 · PostgreSQL (jsonb)"),
        ],
    }),
    new Paragraph({
        spacing: { after: 120 },
        children: [
            bold("Deployment: "), tx("XAMPP subdirectory หรือ php artisan serve --port=8000"),
        ],
    }),
];

const authSection = [
    h1("1. Authentication & Session Flow"),
    ...codeBlock([
        "User → /login (guest-only, throttle:5,1)",
        "  └─ Livewire Auth\\Login::login()",
        "       ├─ validate username/password",
        "       ├─ Auth::attempt(['username','password'], remember)",
        "       ├─ session()->regenerate()",
        "       └─ redirect → /dashboard",
        "",
        "User → /logout (POST)",
        "  └─ Auth::logout() + session invalidate + token regenerate → /login",
    ]),
    new Paragraph({ spacing: { after: 80, before: 80 }, children: [] }),
    bullet("Session driver = database; role อยู่ใน users.role (admin | editor | viewer หรือ custom slug)"),
    bulletRich([bold("User::isAdmin() "), tx("ตรวจ role === 'admin' (short-circuit ทุก permission check)")]),
];

const authzSection = [
    h1("2. Authorization Flow (DB-driven)"),
    ...codeBlock([
        "Request → Livewire mount()",
        "  └─ abort_unless(auth()->user()->hasPermission($section, $action), 403)",
        "       └─ User::hasPermission()",
        "            ├─ if isAdmin() → true",
        "            └─ User::loadMenuPermissions() [static cache per role]",
        "                 ├─ Role::where('slug', $role)->first()",
        "                 └─ MenuPermission::where('role_id', $role->id)->get()",
        "                      → ['products' => ['can_see','can_add','can_edit',",
        "                                         'can_delete','can_import','can_export']]",
        "",
        "Sidebar render → @if (auth()->user()->canSeeMenu($routeName))",
        "  └─ same loadMenuPermissions() cache; route ที่ไม่อยู่ใน DB → ทุก role เห็น (backward compat)",
    ]),
    new Paragraph({ spacing: { after: 80 }, children: [] }),
    bulletRich([
        tx("Admin/Editor บันทึก permission ใน "), bold("/permissions"), tx(" (MenuPermissionMatrix) → "),
        bold("User::clearMenuCache()"), tx(" หลัง save"),
    ]),
    bulletRich([
        tx("เพิ่ม Role ใหม่ผ่าน "), bold("/roles"), tx(" (system roles admin/editor/viewer ลบไม่ได้)"),
    ]),
];

const userFlowsSection = [
    h1("3. Main User Flows"),

    h2("3.1 Dashboard"),
    ...codeBlock([
        "GET /dashboard → Livewire Dashboard",
        "  ├─ render():",
        "  │    ├─ KPI: total products, avg price, category count, edit count",
        "  │    ├─ barChartData: products grouped by category",
        "  │    ├─ trendChartData: avg price by year",
        "  │    ├─ recent: latest 5 products",
        "  │    └─ activities: latest histories from product + spec",
        "  └─ Blade injects @json($barChartData) / @json($trendChartData) → window._dash*",
        "       └─ Alpine x-data init() → new Chart(this.$el, …)",
        "       (destroy() cleanup ป้องกัน chart leak ใน Livewire SPA navigation)",
    ]),

    h2("3.2 Product CRUD (/products)"),
    ...codeBlock([
        "ProductList (filter, sort, pagination, bulk-delete, compare-toggle)",
        "  └─ \"เพิ่มสินค้า\" → dispatch('open-product-form')",
        "       └─ ProductForm modal (#[On('open-product-form')] open())",
        "            ├─ validate(brand, model required)",
        "            ├─ Product::updateOrCreate(['id' => uuid|existing])",
        "            ├─ Upload attachments → ProductAttachment::create() per file",
        "            ├─ $product->histories()->create([action, detail, user, date])",
        "            ├─ dispatch('close-product-form'), dispatch('product-saved')",
        "            └─ ProductList #[On('product-saved')] → re-render",
    ]),
    new Paragraph({ spacing: { before: 120, after: 60 }, children: [bold("Compare workflow:", 22)] }),
    bulletRich([
        tx("ProductList คลิกไอคอน bar-chart → "), bold("CompareCart::toggle($id)"),
        tx(" (max 3) → toast 'เพิ่มเข้าตารางเปรียบเทียบ' / 'เต็ม 3 รายการ'"),
    ]),
    bulletRich([
        tx("คลิก badge "), bold("เลือกเปรียบเทียบ N/3"),
        tx(" → navigate /compare → CompareView render side-by-side"),
    ]),

    h2("3.3 Spec Template CRUD (/specs)"),
    ...codeBlock([
        "CharacteristicsList (year/month filter, view toggle, bulk export, import)",
        "  ├─ \"สร้างคุณลักษณะใหม่\" → dispatch('open-characteristics-form')",
        "  │    └─ CharacteristicsForm modal",
        "  │         ├─ validate(name required)",
        "  │         ├─ cleanSpecs: map(string)->filter empty   [data-integrity guard]",
        "  │         ├─ CharacteristicsTemplate::updateOrCreate(['id' => uuid|existing])",
        "  │         ├─ $spec->histories()->create(...)",
        "  │         └─ dispatch('characteristics-saved') → list re-renders",
        "  ├─ \"นำเข้าคุณลักษณะ\" → SpecsImportModal",
        "  │    └─ Excel upload → CharacteristicsImport",
        "  │         (validates name, category, year, month, budget)",
        "  │         ├─ Insert in DB transaction; row-level errors collected",
        "  │         └─ dispatch('specs-imported') → list re-renders",
        "  └─ Row actions:",
        "       ├─ Eye      → CharacteristicsDetail modal",
        "       ├─ Compare  → /compare?spec={id}",
        "       ├─ Excel    → GET /specs/{spec}/export (CharacteristicsExport, TH Sarabun)",
        "       └─ PDF      → GET /specs/{spec}/export/pdf",
    ]),

    h2("3.4 Comparison CRUD (/comparisons)"),
    ...codeBlock([
        "ComparisonList (filter, bulk select, bulk export)",
        "  └─ \"สร้างการเปรียบเทียบใหม่\" → dispatch('open-comparison-form')",
        "       └─ ComparisonForm modal",
        "            ├─ Choose base spec (optional, populates expected specs)",
        "            ├─ Define 1–3 vendors (ComparisonVendor: name, brand, model, price, specs)",
        "            ├─ Save: Comparison::updateOrCreate + sync vendors",
        "            └─ dispatch('comparison-saved') → list re-renders",
        "  └─ Row actions: detail / Excel / PDF / edit / delete",
        "",
        "Bulk Excel: GET /comparisons/export/bulk?ids=…",
        "  → BulkComparisonsExport implements WithMultipleSheets",
        "       (1 comparison = 1 sheet)",
    ]),

    h2("3.5 Cross-Model Search (/search)"),
    ...codeBlock([
        "SearchPage with #[Url(as: 'q')] $query",
        "  └─ render() if strlen(query) >= 2:",
        "       ├─ Product: brand/model/category/specs::text ilike LIMIT 10",
        "       ├─ if canSeeMenu('specs')           → CharacteristicsTemplate ilike LIMIT 8",
        "       ├─ if canSeeMenu('comparisons')     → Comparison ilike LIMIT 8",
        "       ├─ if canSeeMenu('guidelines')      → GuidelineItem ilike LIMIT 5",
        "       └─ if canSeeMenu('recommendations') → RecommendationItem ilike LIMIT 5",
        "  → sections rendered; click item → wire:navigate ไปหน้าจริง",
        "  (debounce 350ms via wire:model.live.debounce)",
    ]),

    h2("3.6 Audit Log (/audit-log)"),
    ...codeBlock([
        "AuditLogPage (filter type/action/user/dateRange, paginate 20)",
        "  └─ Union ProductEditHistory + CharacteristicsTemplateHistory",
        "       ├─ if !isAdmin → ->where('user', authUser)   [self-only enforcement]",
        "       ├─ apply filters (action, user only for admin, date range)",
        "       └─ orderBy created_at desc → paginate(20)",
    ]),

    h2("3.7 PDF Export"),
    ...codeBlock([
        "GET /specs/{spec}/export/pdf → ExportController::specPdf",
        "  ├─ abort_unless hasPermission('specs','export')",
        "  ├─ Pdf::loadView('exports.spec-pdf', compact('spec'))",
        "  │    └─ foreach $spec->specs as $k => $v",
        "  │         → Specs::display($v) [guards array → json_encode]",
        "  └─ ->download('คุณลักษณะ_<name>.pdf')",
        "",
        "GET /comparisons/{comparison}/export/pdf → ExportController::comparisonPdf",
        "  ├─ load('vendors'), load related spec (nullable)",
        "  ├─ Pdf::loadView('exports.comparison-pdf', …)",
        "  │    └─ all 4 spec-value cells use Specs::display(...)",
        "  └─ ->setPaper('a4','landscape')->download(...)",
    ]),
];

const adminSection = [
    h1("4. Admin Flows"),
    ...codeBlock([
        "/users           → UserList         (admin only; CRUD + role assignment)",
        "/profile         → UserProfile      (self-service edit + change password)",
        "/roles           → RoleList         (admin only; CRUD; 3 system roles locked)",
        "/permissions     → MenuPermissionMatrix",
        "                    → toggle role × menu × action",
        "                    → save → User::clearMenuCache()",
        "/categories      → CategoryListPage / CategoryManager  (admin only)",
        "/brands          → BrandListPage    / BrandManager     (admin only)",
        "/guidelines      → GuidelineList    (year/month filter, CRUD)",
        "/recommendations → RecommendationList (same pattern)",
    ]),
];

const lifecycleSection = [
    h1("5. Livewire Request Lifecycle"),
    p("Pattern ที่ใช้บ่อยใน flow ทั้งหมด:"),
    ...codeBlock([
        "Browser action (click button, type input, wire:navigate)",
        "   │",
        "   ▼",
        "POST /livewire/update   ← JS patch in layouts/app.blade.php fixes XAMPP subdir",
        "   │  └─ payload: snapshot + calls (method names + args) + updates (property=value)",
        "   ▼",
        "Livewire Mechanism",
        "   ├─ Hydrate component from snapshot",
        "   ├─ Apply property updates",
        "   ├─ Invoke called methods (e.g., save(), open(), toggleSelect())",
        "   ├─ Validate (rules() / messages())",
        "   ├─ Re-render Blade view",
        "   └─ Dispatch browser events (toast, modal-open, list-saved)",
        "   ▼",
        "Response (HTML diff + new snapshot)",
        "   │",
        "   ▼",
        "Livewire morphs DOM; #[On] listeners on sibling components trigger",
        "   (e.g., ComparisonForm::save() → dispatch('comparison-saved')",
        "                                 → ComparisonList::refreshList())",
    ]),
];

const storageSection = [
    h1("6. Data Storage Highlights"),
    bulletRich([
        bold("JSONB"), tx(" (specs column on products, characteristics_templates, comparison_vendors) → flexible per-category field set + ilike search via specs::text ilike ?"),
    ]),
    bulletRich([
        bold("Buddhist Era dates"), tx(" stored as string ('YYYY-MM-DD BE') — ไม่ใช้ Carbon"),
    ]),
    bulletRich([
        bold("Histories "), tx("— every CRUD on products + specs writes a row to product_edit_histories / characteristics_template_histories → source of truth สำหรับ audit log + activity feed"),
    ]),
    bulletRich([
        bold("Permissions "), tx("— roles + menu_permissions (matrix) — runtime cache User::$menuCache[$roleSlug]"),
    ]),
];

const patternsSection = [
    h1("7. Notable Implementation Patterns"),
    numItem("Modal-by-event — sibling Livewire components communicate ผ่าน dispatch('open-X-form') / #[On('open-X-form')] (เลี่ยง prop-drilling)"),
    numItem("Post-save auto-refresh — dispatch('X-saved') → empty #[On] method บน sibling list ให้ Livewire re-render"),
    numItem("Static menu cache — private static array $menuCache ใน User model; clear ด้วย User::clearMenuCache() หลังบันทึก permission"),
    numItem("JSON-via-window — Chart.js data ส่งผ่าน <script>window._x = @json(...)</script> แทน inline Alpine x-init (เพื่อเลี่ยง parser error จาก JSON ใน Alpine string)"),
    numItem("TH Sarabun font for Excel — Export classes implement WithStyles → $sheet->getStyle(...)->getFont()->setName('TH Sarabun New')"),
    numItem("Driver-portable migration — if (DB::getDriverName() === 'pgsql') { raw DDL } else { rebuild table } ใน 2026_05_28_000001_add_id_primary_key_to_categories_table.php → test suite ใช้ SQLite ได้"),
    numItem("Render-layer guard — Specs::display($value) แปลง array → json_encode ก่อน htmlspecialchars เพื่อกัน PDF crash จาก malformed data"),
];

const verifySection = [
    h1("8. Verification"),
    p("วิธีตรวจสอบว่า flow ทั้งระบบยังทำงานถูกต้อง:"),
    numItem("รัน php artisan serve --port=8000 → login เป็น admin → ทดลองทุก feature ตามหัวข้อ 3"),
    numItem("รัน php artisan test → ควร 37 passed (ครอบคลุม PDF export, products import, comparison export, etc.)"),
    numItem("ดู audit log ที่ /audit-log → ทุก action ที่ทำใน step 1 ควรมี row ใหม่"),
    numItem("Login เป็น viewer (user01/test123) → sidebar ต้องไม่แสดงเมนู restricted; /search ต้องไม่แสดง section restricted"),
];

// ---------- Section: Routes summary table ----------

const routeRow = (path, comp, role, desc) => new TableRow({
    children: [
        new TableCell({
            width: { size: 2000, type: WidthType.DXA },
            margins: { top: 80, bottom: 80, left: 120, right: 120 },
            borders: cellBorders,
            children: [new Paragraph({ children: [new TextRun({ text: path, font: MONO, size: 18 })] })],
        }),
        new TableCell({
            width: { size: 2400, type: WidthType.DXA },
            margins: { top: 80, bottom: 80, left: 120, right: 120 },
            borders: cellBorders,
            children: [new Paragraph({ children: [new TextRun({ text: comp, font: MONO, size: 18 })] })],
        }),
        new TableCell({
            width: { size: 1600, type: WidthType.DXA },
            margins: { top: 80, bottom: 80, left: 120, right: 120 },
            borders: cellBorders,
            children: [new Paragraph({ children: [new TextRun({ text: role, font: FONT, size: 20 })] })],
        }),
        new TableCell({
            width: { size: 3360, type: WidthType.DXA },
            margins: { top: 80, bottom: 80, left: 120, right: 120 },
            borders: cellBorders,
            children: [new Paragraph({ children: [new TextRun({ text: desc, font: FONT, size: 20 })] })],
        }),
    ],
});

const tableBorder = { style: BorderStyle.SINGLE, size: 4, color: "C0C0C0" };
const cellBorders = { top: tableBorder, bottom: tableBorder, left: tableBorder, right: tableBorder };

const routeHeader = new TableRow({
    tableHeader: true,
    children: ["Route", "Component", "Role", "หมายเหตุ"].map((t, i) => {
        const widths = [2000, 2400, 1600, 3360];
        return new TableCell({
            width: { size: widths[i], type: WidthType.DXA },
            margins: { top: 80, bottom: 80, left: 120, right: 120 },
            shading: { fill: "1B3A6B", type: ShadingType.CLEAR, color: "auto" },
            borders: cellBorders,
            children: [new Paragraph({ children: [new TextRun({ text: t, font: FONT, size: 22, bold: true, color: "FFFFFF" })] })],
        });
    }),
});

const routesTable = new Table({
    width: { size: 9360, type: WidthType.DXA },
    columnWidths: [2000, 2400, 1600, 3360],
    rows: [
        routeHeader,
        routeRow("/login",         "Auth\\Login",            "guest",  "throttle 5/min"),
        routeRow("/dashboard",     "Dashboard",              "all",    "KPI + 2 charts + activity feed"),
        routeRow("/products",      "ProductList",            "all",    "CRUD + bulk-delete + compare"),
        routeRow("/specs",         "CharacteristicsList",    "editor+","CRUD + Excel/PDF + import"),
        routeRow("/comparisons",   "ComparisonList",         "editor+","3-vendor compare + Excel/PDF"),
        routeRow("/compare",       "CompareView",            "all",    "side-by-side (session-based)"),
        routeRow("/search",        "SearchPage",             "all",    "cross-model live search"),
        routeRow("/audit-log",     "AuditLogPage",           "all",    "viewer เห็นเฉพาะของตัวเอง"),
        routeRow("/users",         "UserList",               "admin",  ""),
        routeRow("/roles",         "RoleList",               "admin",  "system roles locked"),
        routeRow("/permissions",   "MenuPermissionMatrix",   "admin",  "role × menu × action"),
        routeRow("/categories",    "CategoryListPage",       "admin",  ""),
        routeRow("/brands",        "BrandListPage",          "admin",  ""),
        routeRow("/guidelines",    "GuidelineList",          "editor+","year/month filter"),
        routeRow("/recommendations","RecommendationList",    "editor+",""),
        routeRow("/profile",       "UserProfile",            "all",    "self-service + change password"),
    ],
});

const routesSection = [
    h1("Appendix A: Routes Map"),
    p("สรุป route หลักทั้งหมดของระบบ (ไม่รวม Export sub-routes):"),
    routesTable,
];

// ---------- Document ----------

const doc = new Document({
    styles: {
        default: {
            document: { run: { font: FONT, size: 22 } },
        },
        paragraphStyles: [
            { id: "Heading1", name: "Heading 1", basedOn: "Normal", next: "Normal", quickFormat: true,
              run: { size: 32, bold: true, font: FONT, color: "1B3A6B" },
              paragraph: { spacing: { before: 360, after: 160 }, outlineLevel: 0 } },
            { id: "Heading2", name: "Heading 2", basedOn: "Normal", next: "Normal", quickFormat: true,
              run: { size: 26, bold: true, font: FONT, color: "2563EB" },
              paragraph: { spacing: { before: 240, after: 120 }, outlineLevel: 1 } },
            { id: "Heading3", name: "Heading 3", basedOn: "Normal", next: "Normal", quickFormat: true,
              run: { size: 22, bold: true, font: FONT, color: "475569" },
              paragraph: { spacing: { before: 160, after: 80 }, outlineLevel: 2 } },
        ],
    },
    numbering: {
        config: [
            { reference: "bullets",
              levels: [{ level: 0, format: LevelFormat.BULLET, text: "•", alignment: AlignmentType.LEFT,
                style: { paragraph: { indent: { left: 720, hanging: 360 } } } }] },
            { reference: "numbers",
              levels: [{ level: 0, format: LevelFormat.DECIMAL, text: "%1.", alignment: AlignmentType.LEFT,
                style: { paragraph: { indent: { left: 720, hanging: 360 } } } }] },
        ],
    },
    sections: [{
        properties: {
            page: {
                size: { width: 11906, height: 16838 }, // A4
                margin: { top: 1440, right: 1440, bottom: 1440, left: 1440 },
            },
        },
        headers: {
            default: new Header({
                children: [new Paragraph({
                    tabStops: [{ type: TabStopType.RIGHT, position: 9026 }],
                    children: [
                        new TextRun({ text: "System Flow · ระบบราคากลาง", font: FONT, size: 18, color: "64748B" }),
                        new TextRun({ text: "\tPrice-MDES_Laravel", font: FONT, size: 18, color: "64748B" }),
                    ],
                })],
            }),
        },
        footers: {
            default: new Footer({
                children: [new Paragraph({
                    alignment: AlignmentType.CENTER,
                    children: [
                        new TextRun({ text: "หน้า ", font: FONT, size: 18, color: "64748B" }),
                        new TextRun({ children: [PageNumber.CURRENT], font: FONT, size: 18, color: "64748B" }),
                        new TextRun({ text: " / ", font: FONT, size: 18, color: "64748B" }),
                        new TextRun({ children: [PageNumber.TOTAL_PAGES], font: FONT, size: 18, color: "64748B" }),
                    ],
                })],
            }),
        },
        children: [
            ...titleBlock,
            new Paragraph({
                spacing: { after: 200 },
                children: [new TextRun({ text: "สารบัญ", font: FONT, size: 28, bold: true, color: "1B3A6B" })],
            }),
            new TableOfContents("Table of Contents", { hyperlink: true, headingStyleRange: "1-3" }),
            new Paragraph({ children: [new PageBreak()] }),

            ...contextSection,
            ...authSection,
            ...authzSection,
            ...userFlowsSection,
            ...adminSection,
            ...lifecycleSection,
            ...storageSection,
            ...patternsSection,
            ...verifySection,
            new Paragraph({ children: [new PageBreak()] }),
            ...routesSection,
        ],
    }],
});

const outputPath = path.join(__dirname, "System_Flow_PriceMDES.docx");
Packer.toBuffer(doc).then(buffer => {
    fs.writeFileSync(outputPath, buffer);
    console.log("OK: wrote " + outputPath + " (" + buffer.length + " bytes)");
}).catch(e => {
    console.error("FAILED:", e.message);
    process.exit(1);
});
