const puppeteer = require('puppeteer-core');
(async () => {
    try {
        const browser = await puppeteer.connect({ browserURL: 'http://127.0.0.1:9222' });
        const page = await browser.newPage();
        page.on('console', msg => console.log('LOG:', msg.text()));
        page.on('pageerror', err => console.error('ERR:', err.message));
        await page.goto('http://127.0.0.1:8001/pos');
        await page.waitForTimeout(2000);
        await browser.close();
    } catch(e) { console.error("Could not connect"); }
})();
