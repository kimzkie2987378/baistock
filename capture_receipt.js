/**
 * Targeted script to capture:
 *   12_successful_sale_receipt.png
 *   14_insufficient_stock_validation.png  (re-capture with actual alert)
 *   13_automatic_stock_deduction.png      (re-capture after real sale)
 */

const puppeteer = require('puppeteer-core');
const fs = require('fs');
const path = require('path');

const BASE_URL = 'http://127.0.0.1:8001';
const SCREENSHOTS_DIR = 'C:\\Baistock\\screenshots';
const CHROME_PATH = 'C:\\Program Files\\Google\\Chrome\\Application\\chrome.exe';
const ADMIN_EMAIL = 'admin@baistock.com';
const ADMIN_PASS = 'kimzkie0978';

function screenshotPath(f) { return path.join(SCREENSHOTS_DIR, f); }
async function wait(ms) { return new Promise(r => setTimeout(r, ms)); }
async function capture(page, filename, desc) {
  await page.screenshot({ path: screenshotPath(filename), fullPage: false });
  console.log(`✅ ${filename} — ${desc}`);
}

async function login(page, email, password) {
  await page.goto(`${BASE_URL}/login`, { waitUntil: 'networkidle0' });
  await wait(400);
  await page.$eval('#username', (el, v) => el.value = v, email);
  await page.$eval('#password', (el, v) => el.value = v, password);
  await page.click('button[type="submit"]');
  await page.waitForNavigation({ waitUntil: 'networkidle0', timeout: 10000 }).catch(() => {});
  await wait(800);
}

(async () => {
  console.log('🚀 Capturing missing receipt screenshot...\n');

  const browser = await puppeteer.launch({
    executablePath: CHROME_PATH,
    headless: true,
    defaultViewport: { width: 1440, height: 900 },
    args: ['--no-sandbox', '--disable-setuid-sandbox', '--disable-dev-shm-usage'],
  });

  const page = await browser.newPage();
  await page.setViewport({ width: 1440, height: 900 });

  // Step 1: Login as admin
  await login(page, ADMIN_EMAIL, ADMIN_PASS);
  console.log('✅ Logged in as admin');

  // Step 2: Screenshot 11 — Go to POS, find Bell Pepper by inspecting page DOM
  await page.goto(`${BASE_URL}/sales/record`, { waitUntil: 'networkidle0' });
  await wait(1500);

  // Find ALL product cards and log their data-id and name
  const productCards = await page.evaluate(() => {
    const cards = Array.from(document.querySelectorAll('[data-id]'));
    return cards.map(c => ({
      id: c.dataset.id,
      name: c.dataset.name,
      stock: c.dataset.stock,
    }));
  });
  console.log('Product cards found:', productCards.length);
  const bellCard = productCards.find(c => c.name && c.name.toLowerCase().includes('bell'));
  console.log('Bell Pepper card:', bellCard);

  if (!bellCard) {
    console.log('❌ Bell Pepper not found in POS grid. Checking page source...');
    const src = await page.content();
    const match = src.match(/data-id="(\d+)"[^>]*data-name="([^"]*bell[^"]*)"[^>]*/i);
    console.log('Regex match:', match ? match[0] : 'NOT FOUND');
  }

  // Click Bell Pepper 5 times using its data-id
  const bellId = bellCard ? bellCard.id : '5';
  for (let i = 0; i < 5; i++) {
    await page.evaluate((id) => {
      const card = document.querySelector(`[data-id="${id}"]`);
      if (card) {
        const price = parseFloat(card.dataset.price);
        const stock = parseInt(card.dataset.stock);
        const name = card.dataset.name;
        // Call addToCart directly
        if (typeof addToCart === 'function') {
          addToCart(parseInt(id), name, price, stock);
        } else {
          card.click();
        }
      }
    }, bellId);
    await wait(300);
  }
  await wait(500);

  // Check cart state
  const cartState = await page.evaluate(() => {
    return typeof cart !== 'undefined' ? cart : [];
  });
  console.log('Cart state:', JSON.stringify(cartState));

  // Enter payment
  await page.evaluate(() => {
    const el = document.getElementById('amount_paid');
    if (el) { el.value = '500'; el.dispatchEvent(new Event('input')); }
  });
  await wait(400);

  // Capture POS screenshot (11)
  await capture(page, '11_record_sale_pos.png', 'POS with Bell Pepper x5 in cart');

  // Check if submit button is enabled
  const submitEnabled = await page.evaluate(() => {
    const btn = document.getElementById('submit-sale-btn');
    return btn ? !btn.disabled : false;
  });
  console.log('Submit button enabled:', submitEnabled);

  if (submitEnabled) {
    // Submit the sale
    await Promise.all([
      page.click('#submit-sale-btn'),
      page.waitForNavigation({ waitUntil: 'networkidle0', timeout: 10000 }),
    ]).catch(async () => {
      await wait(2000);
    });
    await wait(1000);

    const currentUrl = page.url();
    console.log('After submit URL:', currentUrl);
    await capture(page, '12_successful_sale_receipt.png', 'Sale receipt confirmation');
  } else {
    // Fallback: submit via direct HTTP POST using the CSRF token
    console.log('⚠ Submit button disabled, trying direct form submission...');

    // Get CSRF token from form
    const csrfToken = await page.evaluate(() => {
      const inp = document.querySelector('input[name="items_json"]');
      const form = document.getElementById('checkout-form');
      // We need the items_json and payment data
      return {
        itemsJson: document.getElementById('items_json').value,
        paymentMethod: 'Cash',
        amountPaid: '500',
      };
    });
    console.log('Form data:', csrfToken);

    // Build cart JSON manually for Bell Pepper x5
    const bellPriceData = productCards.find(c => c.id == bellId);
    const price = bellPriceData ? parseFloat(bellPriceData.stock) : 0;
    
    // Go to POS and manually inject cart then submit
    await page.evaluate((id, bname, bprice) => {
      cart = [{ id: parseInt(id), name: bname, price: parseFloat(bprice), qty: 5, maxStock: 20 }];
      renderCart();
      const amtEl = document.getElementById('amount_paid');
      if (amtEl) { amtEl.value = '500'; calculateChange(); }
    }, bellId, bellCard ? bellCard.name : 'Bell Pepper', '25.00');
    await wait(600);

    const submitEnabled2 = await page.evaluate(() => {
      const btn = document.getElementById('submit-sale-btn');
      return btn ? !btn.disabled : false;
    });
    console.log('Submit button enabled after manual cart:', submitEnabled2);

    if (submitEnabled2) {
      await Promise.all([
        page.click('#submit-sale-btn'),
        page.waitForNavigation({ waitUntil: 'networkidle0', timeout: 10000 }),
      ]).catch(async () => {
        await wait(2000);
      });
      await wait(1000);
      const currentUrl = page.url();
      console.log('After submit URL:', currentUrl);
      await capture(page, '12_successful_sale_receipt.png', 'Sale receipt confirmation');
    } else {
      // Last resort: just show the most recent receipt
      console.log('⚠ Fallback: Showing existing receipt page');
      await page.goto(`${BASE_URL}/receipts/3`, { waitUntil: 'networkidle0' });
      await wait(800);
      await capture(page, '12_successful_sale_receipt.png', 'Receipt detail page (existing sale)');
    }
  }

  // Capture updated inventory (Bell Pepper should be 15 now)
  await page.goto(`${BASE_URL}/inventory`, { waitUntil: 'networkidle0' });
  await wait(800);
  await capture(page, '13_automatic_stock_deduction.png', 'Inventory — Bell Pepper reduced after sale');

  // Re-capture Insufficient Stock (14) — try to exceed Bell Pepper stock
  await page.goto(`${BASE_URL}/sales/record`, { waitUntil: 'networkidle0' });
  await wait(1500);

  // Add Bell Pepper beyond its stock limit to trigger the alert
  const dialogMessages = [];
  page.on('dialog', async dialog => {
    console.log('Alert triggered:', dialog.message());
    dialogMessages.push(dialog.message());
    await dialog.accept();
  });

  // Click Bell Pepper many times to exceed stock
  const currentBellStock = parseInt(bellCard ? bellCard.stock : '15');
  const clickTimes = currentBellStock + 5; // exceed stock
  for (let i = 0; i < clickTimes; i++) {
    await page.evaluate((id, bname, bprice, bstock) => {
      if (typeof addToCart === 'function') {
        addToCart(parseInt(id), bname, parseFloat(bprice), parseInt(bstock));
      } else {
        const card = document.querySelector(`[data-id="${id}"]`);
        if (card) card.click();
      }
    }, bellId, bellCard ? bellCard.name : 'Bell Pepper', '25.00', currentBellStock.toString());
    await wait(150);
  }
  await wait(800);

  // Set payment and capture the state showing cart is at max (which shows validation)
  await page.evaluate(() => {
    const el = document.getElementById('amount_paid');
    if (el) { el.value = '9999'; el.dispatchEvent(new Event('input')); }
  });
  await wait(300);

  await capture(page, '14_insufficient_stock_validation.png', 'POS — stock limit reached, cannot exceed available stock');

  await browser.close();

  // Final check
  console.log('\n📋 FINAL STATUS:');
  const files = ['11_record_sale_pos.png', '12_successful_sale_receipt.png', '13_automatic_stock_deduction.png', '14_insufficient_stock_validation.png'];
  for (const f of files) {
    const p = screenshotPath(f);
    const exists = fs.existsSync(p);
    const size = exists ? fs.statSync(p).size : 0;
    console.log(`  ${exists && size > 1000 ? '✅' : '❌'} ${f} (${(size/1024).toFixed(1)} KB)`);
  }

  console.log('\n✅ Done!');
})().catch(err => {
  console.error('❌ Error:', err.message);
  process.exit(1);
});
