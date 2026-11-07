// Captures desktop + mobile screenshots of the running preview.
// NODE_PATH=$(npm root -g) node tools/screenshots.js [baseUrl] [outDir]
const { chromium } = require('playwright');
const base = process.argv[2] || 'http://127.0.0.1:8080';
const out = process.argv[3] || __dirname + '/../preview/screenshots';
const pages = { home: '/', about: '/about/', programmes: '/programmes/', projects: '/projects/', 'project-detail': '/projects/school-fee-support-fund/', events: '/events/', marathon: '/marathon/', gallery: '/gallery/', news: '/news/', 'article': '/news/remembering-michael-nimmichon-masotmi/', contact: '/contact/', 'get-involved': '/get-involved/' };
(async () => {
  const b = await chromium.launch();
  for (const [vp, opt] of Object.entries({ desktop: { viewport: { width: 1440, height: 900 } }, mobile: { viewport: { width: 390, height: 844 }, deviceScaleFactor: 2, isMobile: true, hasTouch: true } })) {
    const ctx = await b.newContext(opt); const p = await ctx.newPage();
    for (const [name, url] of Object.entries(pages)) {
      await p.goto(base + url, { waitUntil: 'networkidle' });
      await p.addStyleTag({ content: '.js .reveal{opacity:1!important;transform:none!important}' });
      await p.evaluate(async()=>{for(let y=0;y<document.body.scrollHeight;y+=300){window.scrollTo(0,y);await new Promise(r=>setTimeout(r,110));}document.querySelectorAll('.mnm-progress,.reveal,.is-split').forEach(e=>e.classList.add('is-in'));window.scrollTo(0,0);});
      await p.waitForTimeout(900);
      
      await p.screenshot({ path: `${out}/${vp}-${name}.png`, fullPage: true });
    }
    await ctx.close();
  }
  await b.close();
})();
