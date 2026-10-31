#!/usr/bin/env python3
"""Read-only Git audit. Reports paths/commit IDs, NEVER matching credentials.
Heuristics are not proof that a repository is secret-free. Requires Git and Python 3.
"""
import subprocess as s,re,sys
from pathlib import Path
root=Path(__file__).resolve().parent.parent

def git(*args):return s.run(['git','-C',str(root),*args],stdout=s.PIPE,stderr=s.DEVNULL)
if git('rev-parse','--git-dir').returncode:
 print('No Git metadata available: tracked files and history cannot be audited.');sys.exit(2)
pattern=re.compile(rb'AIza[0-9A-Za-z_-]{30,}|(?:GEMINI_API_KEY|DB_PASSWORD)\s*=\s*[^\s\n\r]{8,}')
flagged=set();seen=set()
for commit in git('rev-list','--all').stdout.decode().splitlines():
 for item in git('ls-tree','-rz',commit).stdout.split(b'\0'):
  if not item or b'\t' not in item:continue
  meta,path=item.split(b'\t',1);parts=meta.split()
  if len(parts)!=3 or parts[1]!=b'blob':continue
  oid=parts[2].decode();name=path.decode(errors='replace')
  private=(name=='.env' or name.endswith('/.env') or bool(re.search(r'(^|/)config/(config|ai)\.php$',name)))
  if private and name not in flagged:print('REVIEW private tracked path:',name,'commit:',commit[:12]);flagged.add(name)
  if oid in seen:continue
  seen.add(oid);blob=git('cat-file','blob',oid).stdout
  if pattern.search(blob):print('REVIEW possible credential:',name,'commit:',commit[:12]);flagged.add(name)
print('Historical blobs inspected:',len(seen),'Flagged paths:',len(flagged))
print('Rotate confirmed exposed credentials. History rewriting does not revoke keys.')
sys.exit(1 if flagged else 0)
