#!/usr/bin/env python3
"""HTTP integration checks against a disposable, seeded MySQL/MariaDB database.
Usage: python3 tests/smoke.py http://127.0.0.1:8080
Tests deliberately create users, orders and contact records and decrement stock.
"""
import html, re, sys, time, urllib.request, urllib.parse, urllib.error, http.cookiejar
base = (sys.argv[1] if len(sys.argv)>1 else 'http://127.0.0.1:8080').rstrip('/')
client = urllib.request.build_opener(urllib.request.HTTPCookieProcessor(http.cookiejar.CookieJar()))
checks = 0

def check(ok, label):
    global checks
    assert ok, label
    checks += 1
    print('PASS', label)

def get(path='/'):
    with client.open(base+path) as r:
        return r.read().decode()

def post(action, values=None, path='/', token=True):
    data = dict(values or {}, action=action)
    if token:
        body=get(path)
        data['csrf']=re.search(r'name="csrf" value="([a-f0-9]+)"',body).group(1)
    request=urllib.request.Request(base+path,urllib.parse.urlencode(data).encode())
    try:
        with client.open(request) as r: return r.status, r.read().decode()
    except urllib.error.HTTPError as e: return e.code,e.read().decode()

home=get(); check('dir="rtl"' in home and 'הקטגוריות שלנו' in home,'Hebrew RTL homepage')
check(len(re.findall('class="category-card"', home))==4,'four categories')
check(len(re.findall('class="product-card"',get('/?page=catalog')))==12,'twelve seeded products')
check('iPhone 15' in get('/?page=catalog&q=iPhone'),'search finds product')
check(len(re.findall('class="product-card"',get('/?page=catalog&category=3')))==3,'category filtering')
check('לא נמצאו מוצרים' in get('/?page=catalog&q=no-such-product-xx'),'empty search state')
status, response = post('add',{'product_id':1,'quantity':1},token=False)
check(status==403,'CSRF rejected')
_,body=post('add',{'product_id':1,'quantity':1}); check('iPhone 15' in body and '₪2,999.00' in body,'guest cart')
_,body=post('update',{'product_id':1,'quantity':2}); check('₪5,998.00' in body,'cart quantity and total')
_,body=post('update',{'product_id':1,'quantity':-1}); check('בין 0 ל־20' in body,'negative quantity rejected')
_,body=post('add',{'product_id':1,'quantity':20}); check('אין מספיק מלאי' in body,'stock limit enforced')
_,body=post('checkout',{'address':'Demo street 10'});check('טוב לראות אותך שוב' in body,'anonymous checkout requires login')
email=f'smoke-{time.time_ns()}@example.test'; password='DemoPass123!'
_,body=post('register',{'name':'Smoke Tester','email':email,'password':password},'/?page=register');check('התנתקות' in body,'register and auto-login')
cart=get('/?page=cart'); token=re.search(r'name="checkout_token" value="([a-f0-9]+)"',cart).group(1)
_,body=post('checkout',{'address':'Demo street 10','checkout_token':token,'total_cents':1});check('הזמנת ההדגמה נשמרה בהצלחה' in body and '₪5,998.00' in body,'checkout saves authoritative total')
_,body=post('checkout',{'address':'Demo street 10','checkout_token':token});check('כבר נשלחה' in body,'duplicate checkout rejected')
check('הסל עדיין ריק' in get('/?page=cart'),'cart cleared after checkout')
_,body=post('logout');check('התנתקות</button>' not in body,'logout')
_,body=post('login',{'email':email,'password':'incorrect'},'/?page=login');check('אינם נכונים' in body,'wrong password rejected')
_,body=post('login',{'email':email,'password':password},'/?page=login');check('התנתקות' in body,'login succeeds')
check('Demo street 10' in get('/?page=orders'),'orders persist across login')
_,body=post('register',{'name':'Duplicate','email':email,'password':password},'/?page=register');check('כבר רשומה' in body,'duplicate email rejected')
_,body=post('add',{'product_id':2,'quantity':1});check('Lenovo ThinkPad E14' in body,'add another product')
_,body=post('update',{'product_id':2,'quantity':0});check('הסל עדיין ריק' in body,'remove cart item')
_,body=post('contact',{'name':'Demo','email':'contact@example.test','phone':'050-1234567'});check('הפרטים נשמרו בהצלחה' in body,'contact form persists')
check('&lt;script&gt;' in get('/?page=catalog&q='+urllib.parse.quote('<script>')),'search output is escaped')
other=urllib.request.build_opener(urllib.request.HTTPCookieProcessor(http.cookiejar.CookieJar()))
with other.open(base+'/?page=orders') as r: check('טוב לראות אותך שוב' in r.read().decode(),'order history authentication')
client = other
_,body=post('register',{'name':'Other Tester','email':f'other-{time.time_ns()}@example.test','password':password},'/?page=register')
check('התנתקות' in body,'second independent user registration')
check('Demo street 10' not in get('/?page=orders') and 'עדיין אין הזמנות' in get('/?page=orders'),'another user cannot see first user orders')
_,body=post('add',{'product_id':999999,'quantity':1});check('המוצר לא נמצא' in body,'unknown product rejected')
check(post('not-an-action')[0]==400,'unknown action rejected')
try: get('/?page=unknown'); check(False,'unknown route 404')
except urllib.error.HTTPError as exc: check(exc.code==404,'unknown route 404')
print(f'\n{checks} checks passed.')
