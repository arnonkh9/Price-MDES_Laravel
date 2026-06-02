// Capture screenshots of all key pages for design-reference/screenshots/
// Uses puppeteer-core + system Chrome — no Chromium download needed.
const path = require('path');
const fs = require('fs');
const { execSync } = require('child_process');

const globalRoot = execSync('npm root -g').toString().trim();
const puppeteer = require(path.join(globalRoot, 'puppeteer-core'));

const CHROME = "C:\\Program Files\\Google\\Chrome\\Application\\chrome.exe";
const BASE = "http://localhost:8000";
const OUT  = path.join(__dirname, "..", "design-reference", "screenshots");
fs.mkdirSync(OUT, { recursive: true });

const VIEWPORT = { width: 1440, height: 900 };

async function shot(page, file, opts = {}) {
    const full = path.join(OUT, file);
    await page.screenshot({ path: full, type: 'png', fullPage: opts.fullPage || false });
    const kb = (fs.statSync(full).size / 1024).toFixed(1);
    console.log(`  ✓ ${file} (${kb} KB)`);
}

(async () => {
    console.log("Launching Chrome (visible, 1440x900) ...");
    const browser = await puppeteer.launch({
        executablePath: CHROME,
        headless: 'new',  // hidden — captures clean (no UI chrome)
        defaultViewport: VIEWPORT,
        args: [`--window-size=${VIEWPORT.width},${VIEWPORT.height}`],
    });

    try {
        const page = await browser.newPage();
        await page.setViewport(VIEWPORT);

        // Force LIGHT scheme: the layout's pre-paint script reads localStorage.theme
        // AND falls back to prefers-color-scheme — override both.
        await page.emulateMediaFeatures([{ name: 'prefers-color-scheme', value: 'light' }]);
        await page.evaluateOnNewDocument(() => {
            try { localStorage.setItem('theme', 'light'); } catch (e) {}
            document.documentElement.classList.remove('dark');
        });

        // -------- 1. Login page --------
        console.log("\n[1/11] Login page");
        await page.goto(`${BASE}/login`, { waitUntil: 'networkidle2' });
        await new Promise(r => setTimeout(r, 500));
        await shot(page, "01-login.png");

        // -------- Login as admin --------
        console.log("\nLogging in as admin...");
        await page.type('input[wire\\:model="username"]', 'admin');
        await page.type('input[wire\\:model="password"]', 'admin123');
        await Promise.all([
            page.waitForNavigation({ waitUntil: 'networkidle2' }),
            page.click('button[type="submit"]'),
        ]);
        await new Promise(r => setTimeout(r, 1000));

        // -------- 2. Dashboard --------
        console.log("\n[2/11] Dashboard");
        await page.goto(`${BASE}/dashboard`, { waitUntil: 'networkidle2' });
        await new Promise(r => setTimeout(r, 1500)); // let charts render
        await shot(page, "02-dashboard.png");

        // -------- 3. Products list --------
        console.log("\n[3/11] Products list");
        await page.goto(`${BASE}/products`, { waitUntil: 'networkidle2' });
        await new Promise(r => setTimeout(r, 800));
        await shot(page, "03-products-list.png");

        // -------- 4. Product detail modal --------
        console.log("\n[4/11] Product detail modal");
        await page.goto(`${BASE}/products?view=nb-001`, { waitUntil: 'networkidle2' });
        await new Promise(r => setTimeout(r, 1200));
        await shot(page, "04-product-detail-modal.png");

        // -------- 5. Specs list --------
        console.log("\n[5/11] Specs list");
        await page.goto(`${BASE}/specs`, { waitUntil: 'networkidle2' });
        await new Promise(r => setTimeout(r, 800));
        await shot(page, "05-specs-list.png");

        // -------- 6. Comparisons --------
        console.log("\n[6/11] Comparisons");
        await page.goto(`${BASE}/comparisons`, { waitUntil: 'networkidle2' });
        await new Promise(r => setTimeout(r, 800));
        await shot(page, "06-comparisons.png");

        // -------- 7. Compare side-by-side --------
        console.log("\n[7/11] Compare side-by-side");
        await page.goto(`${BASE}/compare`, { waitUntil: 'networkidle2' });
        await new Promise(r => setTimeout(r, 800));
        await shot(page, "07-compare-side-by-side.png");

        // -------- 8. Search --------
        console.log("\n[8/11] Search");
        await page.goto(`${BASE}/search?q=notebook`, { waitUntil: 'networkidle2' });
        await new Promise(r => setTimeout(r, 1200));
        await shot(page, "08-search.png");

        // -------- 9. Audit log --------
        console.log("\n[9/11] Audit log");
        await page.goto(`${BASE}/audit-log`, { waitUntil: 'networkidle2' });
        await new Promise(r => setTimeout(r, 800));
        await shot(page, "09-audit-log.png");

        // -------- 10. Permissions matrix --------
        console.log("\n[10/11] Permissions matrix");
        await page.goto(`${BASE}/permissions`, { waitUntil: 'networkidle2' });
        await new Promise(r => setTimeout(r, 800));
        await shot(page, "10-permissions-matrix.png");

        // -------- 11. Dark mode (toggle then re-capture dashboard) --------
        console.log("\n[11/11] Dark mode (dashboard)");
        // Override to DARK: emulate prefers-color-scheme + flip theme key before nav
        await page.emulateMediaFeatures([{ name: 'prefers-color-scheme', value: 'dark' }]);
        await page.evaluateOnNewDocument(() => {
            try { localStorage.setItem('theme', 'dark'); } catch (e) {}
            document.documentElement.classList.add('dark');
        });
        await page.goto(`${BASE}/dashboard`, { waitUntil: 'networkidle2' });
        await new Promise(r => setTimeout(r, 2000)); // re-render charts in dark
        await shot(page, "11-dark-mode.png");

        console.log("\n✓ All screenshots captured.");
    } catch (e) {
        console.error("FAILED:", e.message);
        console.error(e.stack);
        process.exitCode = 1;
    } finally {
        await browser.close();
    }
})();
