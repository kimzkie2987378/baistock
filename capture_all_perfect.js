const puppeteer = require('puppeteer-core');
const fs = require('fs');
const path = require('path');

const BASE_URL = 'http://127.0.0.1:8001';
const SCREENSHOTS_DIR = 'C:\\Baistock\\screenshots';
const CHROME_PATH = 'C:\\Program Files\\Google\\Chrome\\Application\\chrome.exe';

const ADMIN_USER = 'admin@baistock.com';
const ADMIN_PASS = 'kimzkie0978';
const STAFF_USER = 'staff1@gmail.com';
const STAFF_PASS = 'kimzkie@0978';

function sp(f) { return path.join(SCREENSHOTS_DIR, f); }
const sleep = ms => new Promise(r => setTimeout(r, ms));

async function captureScreen(page, filename, desc, expectedPath) {
  const currentUrl = page.url();
  console.log(`\nCapturing [${filename}] - ${desc}`);
  console.log(`Current URL: ${currentUrl}`);

  if (expectedPath && !currentUrl.includes(expectedPath) && !filename.startsWith('01') && !filename.startsWith('03')) {
    if (currentUrl.includes('/login')) {
      throw new Error(`CRITICAL: Page redirected to /login while trying to capture ${filename}!`);
    }
  }

  await sleep(600);
  await page.screenshot({ path: sp(filename), fullPage: false });
  const stat = fs.statSync(sp(filename));
  console.log(` Saved ${filename} (${(stat.size / 1024).toFixed(1)} KB)`);
}

async function doLogin(page, username, password) {
  console.log(`Logging in as ${username}...`);
  await page.goto(`${BASE_URL}/login`, { waitUntil: 'networkidle0' });
  await sleep(300);

  // Clear fields
  await page.evaluate(() => {
    const u = document.querySelector('#username');
    const p = document.querySelector('#password');
    if (u) u.value = '';
    if (p) p.value = '';
  });

  await page.type('#username', username, { delay: 30 });
  await page.type('#password', password, { delay: 30 });
  
  await Promise.all([
    page.click('button[type="submit"]'),
    page.waitForNavigation({ waitUntil: 'networkidle0', timeout: 15000 })
  ]);

  console.log(`Post-login URL: ${page.url()}`);
  if (page.url().includes('/login')) {
    const err = await page.evaluate(() => {
      const el = document.querySelector('.bg-rose-50, .text-rose-700');
      return el ? el.innerText : 'Unknown login error';
    });
    throw new Error(`Login failed for ${username}: ${err}`);
  }
}

async function doLogout(page) {
  console.log('Logging out...');
  try {
    await page.goto(`${BASE_URL}/logout`, { waitUntil: 'networkidle0', timeout: 8000 });
  } catch (e) {
    // If redirected
  }
  await sleep(400);
}

(async () => {
  console.log('=== STARTING HIGH-FIDELITY SCREENSHOT GENERATION ===');

  const browser = await puppeteer.launch({
    executablePath: CHROME_PATH,
    headless: true,
    defaultViewport: { width: 1440, height: 900, deviceScaleFactor: 1.25 },
    args: ['--no-sandbox', '--disable-setuid-sandbox', '--disable-dev-shm-usage']
  });

  const page = await browser.newPage();
  await page.setViewport({ width: 1440, height: 900, deviceScaleFactor: 1.25 });

  // 1. LOGIN PAGE (01)
  console.log('\n--- 01: Login Page ---');
  await page.goto(`${BASE_URL}/login`, { waitUntil: 'networkidle0' });
  await sleep(800);
  await captureScreen(page, '01_login_page.png', 'Branded login page with logo and credentials', '/login');

  // 2. AUTHENTICATE AS ADMIN
  await doLogin(page, ADMIN_USER, ADMIN_PASS);

  // 3. SUCCESSFUL LOGIN DASHBOARD (02)
  console.log('\n--- 02: Successful Login Dashboard ---');
  await page.goto(`${BASE_URL}/`, { waitUntil: 'networkidle0' });
  await sleep(1500); // Wait for Chart.js rendering
  await captureScreen(page, '02_successful_login_dashboard.png', 'Admin dashboard with live KPI cards and graphs', '/');

  // 4. USER MANAGEMENT / STAFF LIST (04)
  console.log('\n--- 04: User Management Staff List ---');
  await page.goto(`${BASE_URL}/staff`, { waitUntil: 'networkidle0' });
  await sleep(800);
  await captureScreen(page, '04_user_management_staff_list.png', 'Staff accounts management table', '/staff');

  // 5. ACCESS RESTRICTION STAFF DENIED (03)
  console.log('\n--- 03: Access Restriction Staff Denied ---');
  await doLogout(page);
  await doLogin(page, STAFF_USER, STAFF_PASS);
  // Attempt to access /staff as staff
  await page.goto(`${BASE_URL}/staff`, { waitUntil: 'networkidle0' });
  await sleep(1000);
  await captureScreen(page, '03_access_restriction_staff_denied.png', 'Staff account denied access (403 Forbidden)', '/staff');

  // Re-login as Admin
  await doLogout(page);
  await doLogin(page, ADMIN_USER, ADMIN_PASS);

  // 6. PRODUCT LIST (05)
  console.log('\n--- 05: Product List ---');
  await page.goto(`${BASE_URL}/products`, { waitUntil: 'networkidle0' });
  await sleep(1000);
  await captureScreen(page, '05_product_list.png', 'Full 23-item product catalog with photos and badges', '/products');

  // 7. ADD PRODUCT FORM (06)
  console.log('\n--- 06: Add Product Form ---');
  await page.goto(`${BASE_URL}/products/new`, { waitUntil: 'networkidle0' });
  await sleep(800);
  await captureScreen(page, '06_add_product_form.png', 'New product registration form with categories and stock rules', '/products/new');

  // 8. EDIT PRODUCT FORM (07)
  console.log('\n--- 07: Edit Product Form ---');
  await page.goto(`${BASE_URL}/products/5/edit`, { waitUntil: 'networkidle0' });
  await sleep(800);
  await captureScreen(page, '07_edit_product_form.png', 'Editing Bell Pepper product information and pricing', '/products/5/edit');

  // 9. STOCK-IN TRANSACTION (08)
  console.log('\n--- 08: Stock-In Transaction ---');
  await page.goto(`${BASE_URL}/inventory/adjust/5`, { waitUntil: 'networkidle0' });
  await sleep(600);
  // Fill sample adjustment
  await page.evaluate(() => {
    const qty = document.querySelector('input[name="quantity"]');
    const notes = document.querySelector('textarea[name="notes"]');
    if (qty) qty.value = '10';
    if (notes) notes.value = 'Received fresh farm batch #BAT-2026-092';
  });
  await captureScreen(page, '08_stock_in_transaction.png', 'Stock-in replenishment adjustment form', '/inventory/adjust');

  // 10. UPDATED INVENTORY (09)
  console.log('\n--- 09: Updated Inventory ---');
  await page.goto(`${BASE_URL}/inventory`, { waitUntil: 'networkidle0' });
  await sleep(1000);
  await captureScreen(page, '09_updated_inventory.png', 'Inventory tracking table showing stock quantities and stock status', '/inventory');

  // 11. LOW STOCK MONITORING (10)
  console.log('\n--- 10: Low Stock Monitoring ---');
  await page.goto(`${BASE_URL}/products?stock=low`, { waitUntil: 'networkidle0' });
  await sleep(1000);
  await captureScreen(page, '10_low_stock_monitoring.png', 'Filter showing products currently below minimum stock thresholds', '/products');

  // 12. SEARCH AND FILTERING (22)
  console.log('\n--- 22: Search and Filtering ---');
  await page.goto(`${BASE_URL}/products?q=mango`, { waitUntil: 'networkidle0' });
  await sleep(800);
  await captureScreen(page, '22_search_and_filtering.png', 'Search results for "mango" showing Carabao Mango card', '/products');

  // 13. RECORD SALE POS (11)
  console.log('\n--- 11: Record Sale POS ---');
  await page.goto(`${BASE_URL}/sales/record`, { waitUntil: 'networkidle0' });
  await sleep(1500);

  // Add 5 Bell Peppers (id: 5) to cart
  await page.evaluate(() => {
    if (typeof addToCart === 'function') {
      const bell = document.querySelector('[data-id="5"]');
      const name = bell ? bell.dataset.name : 'Bell Pepper (Green & Red)';
      const price = bell ? parseFloat(bell.dataset.price) : 140;
      for (let i = 0; i < 5; i++) {
        addToCart(5, name, price, 20);
      }
      const paid = document.getElementById('amount_paid');
      if (paid) {
        paid.value = '1000';
        if (typeof calculateChange === 'function') calculateChange();
      }
    }
  });
  await sleep(800);
  await captureScreen(page, '11_record_sale_pos.png', 'POS screen with Bell Pepper (5 units) in cart and Cash tendered', '/sales/record');

  // 14. COMPLETE SALE & RECEIPT (12)
  console.log('\n--- 12: Successful Sale Receipt ---');
  await Promise.all([
    page.click('#submit-sale-btn'),
    page.waitForNavigation({ waitUntil: 'networkidle0', timeout: 15000 })
  ]);
  await sleep(1200);
  await captureScreen(page, '12_successful_sale_receipt.png', 'Official transaction receipt with breakdown and cashier timestamp', '/receipts');

  // 15. AUTOMATIC STOCK DEDUCTION (13)
  console.log('\n--- 13: Automatic Stock Deduction ---');
  await page.goto(`${BASE_URL}/inventory`, { waitUntil: 'networkidle0' });
  await sleep(1000);
  await captureScreen(page, '13_automatic_stock_deduction.png', 'Inventory showing Bell Pepper stock automatically reduced from 20 to 15', '/inventory');

  // 16. INSUFFICIENT STOCK VALIDATION (14)
  console.log('\n--- 14: Insufficient Stock Validation ---');
  await page.goto(`${BASE_URL}/sales/record`, { waitUntil: 'networkidle0' });
  await sleep(1500);

  // Directly attempt overstock purchase to trigger validation alert banner
  await page.evaluate(() => {
    // Inject cart with 999 units of Bell Pepper (way exceeding available 15 units)
    if (typeof cart !== 'undefined') {
      cart = [{
        id: 5,
        name: 'Bell Pepper (Green & Red)',
        price: 140,
        qty: 999,
        maxStock: 15
      }];
      if (typeof renderCart === 'function') renderCart();
      const paid = document.getElementById('amount_paid');
      if (paid) paid.value = '150000';
      const submit = document.getElementById('submit-sale-btn');
      if (submit) submit.disabled = false;
    }
  });
  await sleep(500);

  // Submit overstocked form to trigger backend validation flash error
  await Promise.all([
    page.click('#submit-sale-btn'),
    page.waitForNavigation({ waitUntil: 'networkidle0', timeout: 15000 })
  ]);
  await sleep(1000);
  await captureScreen(page, '14_insufficient_stock_validation.png', 'POS error banner: Insufficient stock rejected by system validation', '/sales/record');

  // 17. DASHBOARD OVERVIEW (15)
  console.log('\n--- 15: Dashboard Overview ---');
  await page.goto(`${BASE_URL}/`, { waitUntil: 'networkidle0' });
  await sleep(1500);
  await captureScreen(page, '15_dashboard_overview.png', 'Complete executive dashboard overview with updated sales metrics', '/');

  // 18. INVENTORY REPORT (16)
  console.log('\n--- 16: Inventory Report ---');
  await page.goto(`${BASE_URL}/reports`, { waitUntil: 'networkidle0' });
  await sleep(1200);
  await captureScreen(page, '16_inventory_report.png', 'Comprehensive business intelligence & inventory asset valuation report', '/reports');

  // 19. DAILY SALES REPORT (17)
  console.log('\n--- 17: Daily Sales Report ---');
  await page.goto(`${BASE_URL}/`, { waitUntil: 'networkidle0' });
  await sleep(1000);
  await page.evaluate(() => {
    const btn = document.getElementById('tab-daily');
    if (btn) btn.click();
  });
  await sleep(800);
  await captureScreen(page, '17_daily_sales_report.png', 'Daily sales breakdown chart with today transactions', '/');

  // 20. WEEKLY SALES REPORT (18)
  console.log('\n--- 18: Weekly Sales Report ---');
  await page.evaluate(() => {
    const btn = document.getElementById('tab-weekly');
    if (btn) btn.click();
  });
  await sleep(800);
  await captureScreen(page, '18_weekly_sales_report.png', 'Weekly sales aggregation chart view', '/');

  // 21. MONTHLY SALES REPORT (19)
  console.log('\n--- 19: Monthly Sales Report ---');
  await page.evaluate(() => {
    const btn = document.getElementById('tab-monthly');
    if (btn) btn.click();
  });
  await sleep(800);
  await captureScreen(page, '19_monthly_sales_report.png', 'Monthly revenue & volume trend analysis chart', '/');

  // 22. MYSQL DATABASE STRUCTURE (20)
  console.log('\n--- 20: MySQL Database Structure ---');
  await page.goto(`${BASE_URL}/db_schema_view.html`, { waitUntil: 'networkidle0' });
  await sleep(1500);
  await captureScreen(page, '20_mysql_database_structure.png', 'Visual normalized schema of all 10 MySQL database tables and foreign keys', 'db_schema_view');

  // 23. ACTUAL TEST RESULTS (21)
  console.log('\n--- 21: Actual Test Results ---');
  await page.goto(`${BASE_URL}/test_results_view.html`, { waitUntil: 'networkidle0' });
  await sleep(1200);
  await captureScreen(page, '21_actual_test_results.png', 'Executed integration test suite: 9/9 Passed (100% success rate)', 'test_results_view');

  await browser.close();
  console.log('\n=== ALL 22 SCREENSHOTS PERFECTLY GENERATED ===');
})();
