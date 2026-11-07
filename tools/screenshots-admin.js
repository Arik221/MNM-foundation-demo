// Captures WordPress admin screenshots (set WP_ADMIN_USER / WP_ADMIN_PASS, optionally WP_STAFF_PASS).
// NODE_PATH=$(npm root -g) node tools/screenshots-admin.js [baseUrl] [outDir]
const { chromium } = require('playwright');
const base = process.argv[2] || 'http://127.0.0.1:8080';
const out = process.argv[3] || __dirname + '/../preview/screenshots';
async function login(ctx, user, pass) {
  const p = await ctx.newPage(); await p.goto(base + '/wp-login.php');
  await p.fill('#user_login', user); await p.fill('#user_pass', pass); await Promise.all([p.waitForNavigation(), p.click('#wp-submit')]); return p;
}
(async () => {
  const b = await chromium.launch(); const ctx = await b.newContext({ viewport: { width: 1440, height: 900 } });
  const lp = await ctx.newPage(); await lp.goto(base + '/wp-login.php'); await lp.screenshot({ path: out + '/admin-login.png' }); await lp.close();
  const p = await login(ctx, process.env.WP_ADMIN_USER || 'admin', process.env.WP_ADMIN_PASS);
  const shots = {
    'admin-overview': '/wp-admin/admin.php?page=mnm-foundation',
    'admin-registrations': '/wp-admin/edit.php?post_type=mnm_registration',
    'admin-campaigns': '/wp-admin/edit.php?post_type=mnm_campaign',
    'admin-projects': '/wp-admin/edit.php?post_type=mnm_project',
    'admin-events': '/wp-admin/edit.php?post_type=mnm_event',
    'admin-settings': '/wp-admin/admin.php?page=mnm-settings',
    'admin-media': '/wp-admin/upload.php?mode=list',
    'admin-collections': '/wp-admin/edit-tags.php?taxonomy=mnm_gallery_cat&post_type=attachment',
    'admin-demo': '/wp-admin/admin.php?page=mnm-demo',
  };
  for (const [n, u] of Object.entries(shots)) { await p.goto(base + u, { waitUntil: 'networkidle' }); await p.screenshot({ path: `${out}/${n}.png`, fullPage: n === 'admin-overview' || n === 'admin-settings' }); }
  // Campaign edit screen (manually verified amount)
  await p.goto(base + '/wp-admin/edit.php?post_type=mnm_campaign'); const href = await p.getAttribute('.row-title', 'href');
  await p.goto(href, { waitUntil: 'networkidle' });
  const close = p.locator('.edit-post-welcome-guide .components-modal__header button, button[aria-label="Close"]'); if (await close.count()) { await close.first().click().catch(() => {}); }
  await p.waitForTimeout(800); await p.screenshot({ path: out + '/admin-campaign-edit.png' });
  // Limited staff user (only if WP_STAFF_PASS is provided)
  if (process.env.WP_STAFF_PASS) { const sp = await login(sctx, 'staff', process.env.WP_STAFF_PASS); await sp.goto(base + '/wp-admin/admin.php?page=mnm-foundation', { waitUntil: 'networkidle' }); await sp.screenshot({ path: out + '/admin-staff-overview.png' }); }
  await b.close();
})();
