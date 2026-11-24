const puppeteer = require('puppeteer-core');
const fs = require('fs');

const PMA_URL = 'http://127.0.0.1:8085';
const CHROME_PATH = 'C:\\Program Files\\Google\\Chrome\\Application\\chrome.exe';
const SCREENSHOT_PATH = 'C:\\Baistock\\screenshots\\23_phpmyadmin_designer.png';
const SCREENSHOT_20 = 'C:\\Baistock\\screenshots\\20_mysql_database_structure.png';

const sleep = ms => new Promise(r => setTimeout(r, ms));

(async () => {
  console.log('Capturing flawless phpMyAdmin Designer schema view with foreign key lines...');

  const browser = await puppeteer.launch({
    executablePath: CHROME_PATH,
    headless: true,
    defaultViewport: { width: 1680, height: 960, deviceScaleFactor: 1.25 },
    args: ['--no-sandbox', '--disable-setuid-sandbox', '--disable-dev-shm-usage']
  });

  const page = await browser.newPage();
  await page.setViewport({ width: 1680, height: 960, deviceScaleFactor: 1.25 });

  await page.goto(`${PMA_URL}/index.php?route=/database/designer&db=baistock_db`, {
    waitUntil: 'domcontentloaded',
    timeout: 30000
  });
  await sleep(4000);

  // Set up layout & lines
  await page.evaluate(() => {
    // 1. Hide left navigation tree to give full screen to designer canvas
    const leftNav = document.getElementById('pma_navigation');
    if (leftNav) leftNav.style.display = 'none';

    const content = document.getElementById('pma_content');
    if (content) {
      content.style.marginLeft = '0px';
      content.style.padding = '0px';
    }

    // 2. Collapse designer sidebar text menu to compact icons
    if (typeof DesignerMove !== 'undefined' && typeof DesignerMove.hideText === 'function') {
      DesignerMove.hideText();
    }

    // 3. Resize canvas to fit entire area BEFORE calling reload so context isn't reset
    const canvas = document.getElementById('canvas');
    if (canvas) {
      canvas.width = 1680;
      canvas.height = 960;
      canvas.style.width = '1680px';
      canvas.style.height = '960px';
    }
    if (typeof canvasWidth !== 'undefined') canvasWidth = 1680;
    if (typeof canvasHeight !== 'undefined') canvasHeight = 960;

    // 4. Position all 10 tables logically with clean, balanced spacing
    const tablePositions = {
      // Row 1 (Top)
      'baistock_db.categories': { left: 55, top: 80 },
      'baistock_db.products': { left: 290, top: 80 },
      'baistock_db.stock_movements': { left: 540, top: 80 },
      'baistock_db.sales': { left: 950, top: 80 },
      'baistock_db.users': { left: 1200, top: 80 },
      
      // Row 2 (Bottom)
      'baistock_db.settings': { left: 55, top: 480 },
      'baistock_db.deliveries': { left: 290, top: 480 },
      'baistock_db.delivery_items': { left: 540, top: 480 },
      'baistock_db.sale_items': { left: 950, top: 440 },
      'baistock_db.notifications': { left: 1200, top: 480 },
    };

    for (const [id, pos] of Object.entries(tablePositions)) {
      const el = document.getElementById(id);
      if (el) {
        el.style.left = pos.left + 'px';
        el.style.top = pos.top + 'px';
        el.style.position = 'absolute';
      }
    }

    // 5. Enable relation lines and draw
    window.showRelationLines = true;
    if (typeof DesignerMove !== 'undefined' && typeof DesignerMove.reload === 'function') {
      DesignerMove.reload();
    }
  });

  await sleep(2500);

  // Take the screenshot
  await page.screenshot({ path: SCREENSHOT_PATH, fullPage: false });
  console.log(`Saved screenshot to ${SCREENSHOT_PATH}`);

  // Also update 20_mysql_database_structure.png
  fs.copyFileSync(SCREENSHOT_PATH, SCREENSHOT_20);
  console.log(`Updated ${SCREENSHOT_20}`);

  await browser.close();
  console.log('Capture complete!');
})();
