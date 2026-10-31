<?php
declare(strict_types=1);
require dirname(__DIR__).'/app/functions.php';
require dirname(__DIR__).'/app/ai/assistant.php';
$count=0;
function check(bool $ok,string $label):void {global $count; if(!$ok)throw new RuntimeException($label); $count++; echo "PASS $label\n";}
function rejected(callable $fn,string $kind,string $label):void {try{$fn();}catch(AiError $e){check($e->kind===$kind,$label);return;}throw new RuntimeException($label.' accepted');}
check(aiReadRequest('{"message":"טלפון עד 3000"}','application/json')['action']==='message','Hebrew request');
foreach(['[]','null','{','{"message":[]}','{"message":""}','{"message":true}','{"action":"delete"}'] as $raw) rejected(fn()=>aiReadRequest($raw,'application/json'),'input','invalid JSON/input');
rejected(fn()=>aiReadRequest(json_encode(['message'=>str_repeat('א',1001)]),'application/json'),'input','Unicode length limit');
rejected(fn()=>aiReadRequest(str_repeat('x',8193),'application/json'),'input','body size limit');
rejected(fn()=>aiReadRequest('{"message":"hi"}','text/plain'),'input','content type');
check(aiReadRequest('{"action":"reset"}','application/json')['action']==='reset','reset request');
$categories=[['id'=>1]];
$f=['category_id'=>1,'min_price_cents'=>null,'max_price_cents'=>300000,'terms'=>['טלפון'],'compare'=>false,'follow_up'=>false,'shopping'=>true];
check(aiValidateFilters($f,$categories)===$f,'valid filters');
foreach([['category_id'=>99],['max_price_cents'=>'100'],['min_price_cents'=>400000],['compare'=>1],['terms'=>['x',[],42]]] as $patch) rejected(fn()=>aiValidateFilters(array_replace($f,$patch),$categories),'response','invalid model filter rejected');
$rows=[['id'=>1,'name'=>'Actual','price_cents'=>299900,'stock'=>1,'category'=>'Phone','short_description'=>'128GB','description'=>'Only database facts']];
$p=aiSelectProducts(['product_ids'=>[1],'price'=>1,'name'=>'Invented'],$rows);
check($p[0]['name']==='Actual'&&$p[0]['price_cents']===299900&&$p[0]['url']==='/?page=product&id=1','authoritative facts and link');
foreach([[99],['1'],[1,1],[1,2,3,4,5]] as $ids)rejected(fn()=>aiSelectProducts(['product_ids'=>$ids],$rows),'response','invalid product selection');
check(aiConversation(['updated_at'=>1],2000)===[],'history expires');
check(aiConversation(['updated_at'=>1900],2000)!==[],'recent history kept');
$config=['model'=>'gemini-3.8-flash','api_key'=>'FAKE_TEST_KEY','timeout_seconds'=>5];
$schema=['type'=>'OBJECT','properties'=>['ok'=>['type'=>'BOOLEAN']],'required'=>['ok']];
$transport=function($url,$body){check(str_starts_with($url,'https://generativelanguage.googleapis.com/v1beta/models/'),'fixed Google host');check(!str_contains($url,'FAKE_TEST_KEY')&&!str_contains($body,'FAKE_TEST_KEY'),'key absent from URL/body');return [200,json_encode(['candidates'=>[['finishReason'=>'STOP','content'=>['parts'=>[['text'=>'{"ok":true}']]]]]])];};
check(aiGemini($config,'test',[],$schema,$transport)['ok']===true,'structured transport success');
foreach([429=>'quota',403=>'configuration',404=>'configuration',500=>'upstream'] as $status=>$kind)rejected(fn()=>aiGemini($config,'test',[],$schema,fn()=>[$status,'secret upstream details']),$kind,'upstream status mapped');
rejected(fn()=>aiGemini($config,'test',[],$schema,fn()=>[200,'bad json']),'response','malformed response');
rejected(fn()=>aiGemini($config,'test',[],$schema,fn()=>[200,json_encode(['candidates'=>[['finishReason'=>'MAX_TOKENS']]])]),'response','truncation rejected');
rejected(fn()=>aiGemini(array_replace($config,['model'=>'../../evil']),'test',[],$schema,$transport),'configuration','model path injection rejected');
rejected(fn()=>aiGemini($config,'test',[],$schema,function(){throw new AiError('timeout');}),'timeout','transport timeout');
check(!aiReady(['enabled'=>true,'api_key'=>'key','rate_secret'=>'short']),'private HMAC secret required');


$result=['segments'=>[['kind'=>'catalog','text'=>'בחרו {{p:1:name}} במחיר {{p:1:price}}.']],'product_ids'=>[1],'compare'=>false];
check(str_contains(aiRenderReply($result,$rows),'Actual'),'catalog tokens resolved');
foreach(['מחיר 999','{{p:99:name}}','{{p:1:cpu}}','https://evil.test','{{broken}}'] as $bad)rejected(fn()=>aiRenderReply(array_replace($result,['segments'=>[['kind'=>'catalog','text'=>$bad]]]),$rows),'response','invented catalog facts rejected');
$cats=[['id'=>1,'name'=>'טלפונים','slug'=>'phones'],['id'=>2,'name'=>'מחשבים','slug'=>'computers']];
$f=aiLocalFilters('מחשב עד 5,000',$cats,[]);
check($f['category_id']===2&&$f['max_price_cents']===500000,'local Hebrew budget/category');
check(aiLocalFilters('ומה יותר זול?',$cats,['filters'=>$f])['max_price_cents']===500000,'follow-up preserves budget');
echo "$count unit checks passed.\n";
