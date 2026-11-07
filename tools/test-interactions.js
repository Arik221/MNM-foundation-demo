// End-to-end smoke test of the demo interactions. NODE_PATH=$(npm root -g) node tools/test-interactions.js
const { chromium } = require('playwright');
const base = process.argv[2] || 'http://127.0.0.1:8080';
const EM = 'test.' + Date.now() + '@example.org'; let fails = 0; const ok = (c, m) => { console.log((c ? 'PASS ' : 'FAIL ') + m); if (!c) fails++; };
(async () => {
  const b = await chromium.launch(); const ctx = await b.newContext({ viewport: { width: 1280, height: 900 } }); const p = await ctx.newPage();
  const errs = []; p.on('pageerror', e => errs.push(e.message)); p.on('console', m => { if (m.type() === 'error' && !m.text().includes('422')) errs.push(m.text()); });

  // Registration
  await p.goto(base + '/marathon/'); await p.waitForTimeout(3000);
  const before = parseInt(await p.textContent('.info-tile [data-mnm-regcount]'), 10);
  await p.click('.mnm-form [type=submit]');
  ok(await p.locator('.mnm-field.has-error').count() >= 8, 'client-side validation flags empty required fields');
  await p.fill('[name="mnm_reg[full_name]"]', 'Test Runner'); await p.fill('[name="mnm_reg[email]"]', 'bad-email'); await p.fill('[name="mnm_reg[phone]"]', '98190 32215');
  await p.fill('[name="mnm_reg[dob]"]', '1995-05-05'); await p.fill('[name="mnm_reg[location]"]', 'Ukhrul');
  await p.selectOption('[name="mnm_reg[race]"]', { index: 1 }); await p.selectOption('[name="mnm_reg[tshirt]"]', 'M');
  await p.fill('[name="mnm_reg[emergency_name]"]', 'Contact Person'); await p.fill('[name="mnm_reg[emergency_phone]"]', '+91 90000 11111');
  await p.check('[name="mnm_reg[consent]"]'); await p.click('.mnm-form [type=submit]');
  ok(await p.locator('[name="mnm_reg[email]"]').locator('xpath=ancestor::div[contains(@class,"mnm-field")]//span[contains(@class,"mnm-error")]').textContent() !== '', 'invalid email rejected client-side');
  await p.fill('[name="mnm_reg[email]"]', EM); await p.click('.mnm-form [type=submit]');
  await p.waitForSelector('[data-mnm-success]:not([hidden])', { timeout: 15000 });
  const ref = (await p.textContent('[data-mnm-ref]')).trim();
  ok(/^MNM-[A-Z0-9]{6}$/.test(ref), 'registration confirmed with reference ' + ref);
  const after = parseInt(await p.textContent('.info-tile [data-mnm-regcount]'), 10);
  ok(after === before + 1, `public registration count updated ${before} -> ${after}`);

  // Duplicate email rejected server-side
  await p.goto(base + '/marathon/'); await p.waitForTimeout(3000);
  for (const [s, v] of [['full_name', 'Test Runner'], ['email', '' + EM + ''], ['phone', '98190 32215'], ['dob', '1995-05-05'], ['location', 'Ukhrul'], ['emergency_name', 'X Person'], ['emergency_phone', '+91 90000 11111']]) await p.fill(`[name="mnm_reg[${s}]"]`, v);
  await p.selectOption('[name="mnm_reg[race]"]', { index: 1 }); await p.selectOption('[name="mnm_reg[tshirt]"]', 'M'); await p.check('[name="mnm_reg[consent]"]'); await p.click('.mnm-form [type=submit]');
  await p.waitForTimeout(1500);
  ok((await p.locator('[name="mnm_reg[email]"]').locator('xpath=ancestor::div[contains(@class,"mnm-field")]//span[contains(@class,"mnm-error")]').textContent()).includes('already'), 'duplicate email rejected by server');

  // Bot checks: honeypot + missing nonce return errors
  const html = await (await p.request.get(base + '/marathon/')).text();
  const r1 = await p.request.post(base + '/wp-admin/admin-post.php', { form: { action: 'mnm_register', mnm_ajax: '1', mnm_website: 'bot' } });
  ok(r1.status() === 422, 'POST without nonce/with honeypot is rejected (' + r1.status() + ')');

  // Contact form
  await p.goto(base + '/contact/'); await p.waitForTimeout(3000);
  await p.fill('#f-c_name', 'Visitor'); await p.fill('#f-c_email', 'visitor@example.org'); await p.fill('#f-c_message', 'Hello, I would like to help.');
  await p.click('[data-mnm-form=contact] [type=submit]'); await p.waitForSelector('[data-mnm-success]:not([hidden])', { timeout: 15000 });
  ok(true, 'contact form submitted');

  // Gallery filter + lightbox
  await p.goto(base + '/gallery/'); await p.waitForTimeout(500);
  const total = await p.locator('.mnm-gallery__item:visible').count();
  await p.click('.mnm-gallery__filters button[data-filter=marathon]');
  const filtered = await p.locator('.mnm-gallery__item:visible').count();
  ok(filtered > 0 && filtered < total, `gallery filter works (${total} -> ${filtered})`);
  await p.locator('.mnm-gallery__item:visible a').first().click();
  ok(await p.locator('.mnm-lb.is-open').count() === 1, 'lightbox opens');
  await p.keyboard.press('ArrowRight'); await p.keyboard.press('Escape');
  ok(await p.locator('.mnm-lb.is-open').count() === 0, 'lightbox closes with Escape');

  // Mobile nav
  const m = await b.newContext({ viewport: { width: 390, height: 844 }, isMobile: true, hasTouch: true }); const mp = await m.newPage();
  await mp.goto(base + '/'); await mp.click('[data-nav-toggle]');
  ok(await mp.locator('#primary-nav.is-open').count() === 1 && await mp.getAttribute('[data-nav-toggle]', 'aria-expanded') === 'true', 'mobile menu opens');
  ok(await mp.evaluate(() => document.documentElement.scrollWidth <= window.innerWidth), 'no horizontal scroll on mobile home');

  ok(errs.length === 0, 'no console/page errors ' + JSON.stringify(errs.slice(0, 3)));
  await b.close(); process.exit(fails ? 1 : 0);
})();
