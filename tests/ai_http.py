"""HTTP handler tests with mocked Gemini; a disposable MySQL DB is required.
Run server with tests/router.php. No real API calls or API keys.
"""
import json,re,sys,urllib.request,urllib.error,http.cookiejar
base=sys.argv[1].rstrip('/')
client=urllib.request.build_opener(urllib.request.HTTPCookieProcessor(http.cookiejar.CookieJar()))
count=0
def check(ok,label):
 global count
 if not ok: raise AssertionError(label)
 count+=1;print('PASS',label)
def token():
 body=client.open(base+'/').read().decode()
 return re.search(r'data-csrf="([a-f0-9]+)"',body).group(1)
def call(payload,csrf=None):
 headers={'Content-Type':'application/json','X-CSRF-Token':csrf if csrf is not None else token()}
 req=urllib.request.Request(base+'/api/chat.php',json.dumps(payload).encode(),headers)
 try: response=client.open(req)
 except urllib.error.HTTPError as e: response=e
 return response.status,json.loads(response.read())
status,data=call({'message':'טלפון עד 3000'})
check(status==200 and data['ok'],'assistant HTTP success')
check(data['products'] and all(p['price_cents']<=300000 for p in data['products']),'SQL budget filter')
check(all(p['category']=='טלפונים' for p in data['products']),'SQL category filter')
check(all(p['url']=='/?page=product&id='+str(p['id']) for p in data['products']),'server product links')
status,data=call({'message':'השווה את אלה'})
check(status==200 and data['compare'] and len(data['products'])==2,'comparison and conversation follow-up')
check(call({'message':[]})[0]==400,'malformed payload')
check(call({'message':'hello'},'wrong')[0]==403,'CSRF rejection')
status,data=call({'message':'טלפון עד 1'})
check(status==200 and data['products']==[],'empty catalog result')
status,data=call({'message':'TIMEOUT'})
check(status==503 and data['error']=='timeout','timeout safe response')
status,data=call({'message':'QUOTA'})
check(status==429 and data['error']=='quota','upstream quota safe response')
status,data=call({'message':'MALFORMED'})
check(status==503 and data['error']=='response','malformed model response')
status,data=call({'message':'INVENT'})
check(status==503 and data['error']=='response','invented product rejected')
status,data=call({'message':'SQL'})
check(status==200 and data['ok'],'SQL-like text remains data')
status,data=call({'message':'מה זה RAM ואיך הוא קשור ל-Docker?'})
check(status==200 and 'זיכרון' in data['message'] and not data['products'],'general technology answer')
status,data=call({'action':'history'})
check(status==200 and len(data['history'])<=8 and 'RAM' in data['history'][-1]['text'],'full conversation history')
status,data=call({'action':'reset'})
check(status==200 and data['products']==[],'conversation reset')
print(f'{count} HTTP checks passed (mocked Gemini).')
