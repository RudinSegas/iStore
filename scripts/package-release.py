#!/usr/bin/env python3
"""Build a source+generated-deployment ZIP; never includes private settings.
Usage: python3 scripts/package-release.py /absolute/iStore-v1.0.zip
Requires generated build/infinityfree/htdocs. Python is a packaging tool only.
"""
from pathlib import Path
import sys,zipfile,re
root=Path(__file__).resolve().parent.parent
output=Path(sys.argv[1]).resolve()
if not (root/'build/infinityfree/htdocs/index.php').is_file():raise SystemExit('Generate build/infinityfree/htdocs first.')
if output.exists():raise SystemExit('Output exists; choose a new filename.')
top={'app','public','config','database','scripts','tests','docs','build'}
files={'.env.example','.gitignore','.dockerignore','Dockerfile','compose.yaml','README.md','START_HERE.md'}
chosen=[]
for p in sorted(root.rglob('*')):
 if p.is_symlink():raise SystemExit('Symlinks prohibited.')
 if not p.is_file():continue
 rel=p.relative_to(root);parts=rel.parts
 if parts[0] not in top and str(rel) not in files:continue
 if any(x in {'.git','__pycache__','node_modules'} for x in parts):continue
 if parts[0]=='build' and parts[:3]!=('build','infinityfree','htdocs'):continue
 if p.name.startswith('.env') and p.name!='.env.example':continue
 if 'config' in parts[:-1] and p.suffix=='.php' and not p.name.endswith('.example.php'):continue
 if p.suffix.lower() in {'.zip','.log','.bak','.old','.pyc','.tmp'}:continue
 content=p.read_bytes()
 if re.search(rb'AIza[0-9A-Za-z_-]{30,}',content):raise SystemExit('Possible API key in '+str(rel)+'; packaging stopped.')
 chosen.append((p,rel))
with zipfile.ZipFile(output,'x',zipfile.ZIP_DEFLATED) as z:
 for p,rel in chosen:z.write(p,'iStore-v1.0/'+str(rel))
print('Packaged',len(chosen),'files. Private config excluded.')
