"""Requires disposable configured DB and php on PATH. Does not call Gemini."""
import concurrent.futures,subprocess,secrets,time
scope=secrets.token_hex(12);now=str(int(time.time()))
def worker(_):
 p=subprocess.run(['php','tests/ai_rate_worker.php',scope,now],capture_output=True,text=True,check=True)
 return p.stdout.strip()
with concurrent.futures.ThreadPoolExecutor(max_workers=12) as pool:results=list(pool.map(worker,range(20)))
assert results.count('reserved')==5 and results.count('limited')==15,results
print('PASS 20 parallel rate reservations: exactly 5 accepted, 15 limited')
