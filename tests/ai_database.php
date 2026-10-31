<?php
declare(strict_types=1);
require dirname(__DIR__).'/app/bootstrap.php';
require dirname(__DIR__).'/app/ai/assistant.php';
$n=0;function ok(bool $v,string $s):void{global $n;if(!$v)throw new RuntimeException($s);$n++;echo "PASS $s\n";}
$base=['category_id'=>1,'min_price_cents'=>null,'max_price_cents'=>280000,'terms'=>[],'compare'=>false];
$rows=aiCatalog($db,$base);ok(count($rows)===2,'budget and category SQL');
$rows=aiCatalog($db,array_replace($base,['terms'=>['Galaxy']]));ok(count($rows)===1&&$rows[0]['name']==='Samsung Galaxy S24','name matching');
$rows=aiCatalog($db,array_replace($base,['terms'=>["%' OR 1=1 --"]]));ok(count($rows)===2,'injection and wildcard escaping preserves hard filters');
$unique='test:'.bin2hex(random_bytes(10));
aiReserve($db,[[$unique,60,2]],str_repeat('s',32),1000);
aiReserve($db,[[$unique,60,2]],str_repeat('s',32),1001);
try{aiReserve($db,[[$unique,60,2]],str_repeat('s',32),1002);throw new RuntimeException('limit failed');}catch(AiError $e){ok($e->kind==='rate','shared limit enforced');}
aiReserve($db,[[$unique,60,2]],str_repeat('s',32),1020);ok(true,'window resets');
$unique.='day';
aiReserve($db,[[$unique,86400,1]],str_repeat('s',32),1000);
try{aiReserve($db,[[$unique,86400,1]],str_repeat('s',32),1001);throw new RuntimeException('daily limit failed');}catch(AiError $e){ok($e->kind==='quota','daily cap enforced');}
// Multi-scope reservation rollback: rejecting the second rule must not consume the first.
$first='a:'.bin2hex(random_bytes(8));$second='b:'.bin2hex(random_bytes(8));
try{aiReserve($db,[[$first,60,5],[$second,60,0]],str_repeat('s',32),2000);}catch(AiError){}
ok((int)query($db,'SELECT COALESCE(SUM(hits),0) FROM ai_rate_limits WHERE scope_hash=?',[hash_hmac('sha256',$first,str_repeat('s',32))])->fetchColumn()===0,'atomic reservation rollback');
$state=['updated_at'=>time(),'history'=>array_fill(0,20,['role'=>'user','text'=>'old'])];
$calls=0;
$model=function($s,$i,$schema)use(&$calls){$calls++;return ['product_ids'=>[1],'compare'=>false,'segments'=>[['kind'=>'catalog','text'=>'מומלץ לבדוק את {{p:1:name}} במחיר {{p:1:price}}.']]];};
$c=aiConfig();[$answer,$new]=aiAnswer($db,$c,'טלפון עד 3000',$state,$model);
ok(count($new['history'])===8&&$answer['products'][0]['name']==='iPhone 15','bounded context and grounded assistant');
ok($calls===1 && str_contains($answer['message'],'iPhone 15'),'one model call and rendered facts');
ok(isset($new['history'][7]['text']),'assistant prose retained');
$model=function($s,$i,$schema){ok(str_contains($i['history'][7]['text'],'iPhone'),'follow-up receives prose context');return ['product_ids'=>[],'compare'=>false,'segments'=>[['kind'=>'general','text'=>'RAM הוא זיכרון העבודה הזמני. SSD שומר קבצים גם לאחר כיבוי.']]];};
[$answer,$next]=aiAnswer($db,$c,'ומה זה RAM?',$new,$model);
ok(str_contains($answer['message'],'SSD')&&!$answer['products'],'general technology response');
echo "$n database checks passed.\n";
