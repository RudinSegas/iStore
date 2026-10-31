"""Run only against a disposable seeded test server (see tests/README.md)."""
import sys,urllib.request as u,urllib.error as e,urllib.parse as p,http.cookiejar,re,json,time
base=sys.argv[1]; jar=http.cookiejar.CookieJar();client=u.build_opener(u.HTTPCookieProcessor(jar));n=0
def req(path='/',data=None,headers=None):
 try:r=client.open(u.Request(base+path,data,headers or {}))
 except e.HTTPError as x:r=x
 return r.status,r.read().decode(),r.headers
def ok(v,s):
 global n
 assert v,s
 n+=1;print('PASS',s)
def post(fields):
 _,body,_=req();fields['csrf']=re.search('name="csrf" value="([a-f0-9]+)"',body)[1]
 return req('/',p.urlencode(fields).encode())
s,b,h=req();ok('frame-ancestors' in h['Content-Security-Policy'] and 'no-store' in h['Cache-Control'],'security headers')
ok(all(c.has_nonstandard_attr('HttpOnly') and c.get_nonstandard_attr('SameSite')=='Lax' for c in jar),'cookie HttpOnly and SameSite')
for q in ['page[]=catalog','q[]=x','category=-1','id=1x','min_price=NaN','page=catalog&min_price=500&max_price=2','sort=evil','pg=0']:
 ok(req('/?'+q)[0]==400,'invalid query '+q.split('=')[0])
ok(req('/?page=catalog&min_price=2500&max_price=3000')[1].count('class="product-card"')==3,'price range filter')
ok(req('/?page=catalog&q='+p.quote("%' OR 1=1 --"))[1].count('class="product-card"')==0,'SQL injection search literal')
ok(req('/api/gemini-check.php')[0]==404,'probe disabled')
for path in ['/config/ai.php','/config/config.php','/.env','/app/bootstrap.php']:
 ok(req(path)[0]==404,'public root excludes private '+path)
s,b,h=post({'action':'login','email[]':'x','password':'x'});ok(s==400,'array POST rejected')
# Fresh session must not reset database-backed account/IP limits.
for i in range(13):
 jar.clear();s,b,h=post({'action':'login','email':'ratelimit@example.test','password':'bad'})
ok(s==429 and int(h['Retry-After'])>0,'login rate limit survives fresh sessions')
print(n,'security HTTP checks passed.')
