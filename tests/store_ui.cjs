const {chromium}=require('playwright');
(async()=>{
 const browser=await chromium.launch({executablePath:process.env.BROWSER_EXECUTABLE,headless:true,args:['--no-sandbox']});
 const page=await browser.newPage({viewport:{width:390,height:844}});let n=0;const errors=[];
 page.on('pageerror',e=>errors.push(e.message));const ok=(v,s)=>{if(!v)throw Error(s);console.log('PASS browser',s);n++};
 const base=process.env.ISTORE_BASE_URL;
 for(const route of ['','?page=catalog','?page=product&id=2','?page=register','?page=login','?page=cart']){
  const response=await page.goto(base+'/'+route);ok(response.status()===200,'mobile route '+route);
  ok(await page.evaluate(()=>document.documentElement.scrollWidth<=innerWidth),'no horizontal page overflow '+route);
 }
 await page.goto(base+'/?page=product&id=2');await page.locator('form button').filter({hasText:'הוספה'}).first().click();
 await page.waitForURL('**/?page=cart');ok((await page.textContent('main')).includes('Lenovo'),'mobile add to cart');
 await page.goto(base+'/?page=register');await page.locator('input[name=name]').fill('Browser Demo');await page.locator('input[name=email]').fill('browser-'+Date.now()+'@example.test');await page.locator('input[name=password]').fill('OnlyTest!Password_123');await page.locator('form button').filter({hasText:'הרשמה'}).click();await page.waitForURL('**/?page=catalog');
 ok((await page.textContent('body')).includes('התנתקות'),'browser registration');
 await page.goto(base+'/?page=cart');await page.locator('textarea[name=address],input[name=address]').fill('Demo browser address');await page.locator('button').filter({hasText:'הזמנת הדגמה'}).click();await page.waitForURL('**/?page=orders');ok((await page.textContent('main')).includes('Lenovo'),'browser demo checkout');
 ok(await page.locator('input[autocomplete="cc-number"]').count()===0,'no card collection');
 ok(errors.length===0,'no JavaScript errors');
 if(process.env.SCREENSHOT_PATH)await page.screenshot({path:process.env.SCREENSHOT_PATH,fullPage:true});console.log(n+' store browser checks passed.');await browser.close();
})().catch(e=>{console.error(e);process.exit(1)});
