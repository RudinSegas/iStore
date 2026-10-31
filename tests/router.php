<?php
// LOCAL TEST SERVER ONLY. Never included in generated htdocs.
// php -S 127.0.0.1:8088 -t public tests/router.php
$path=parse_url($_SERVER['REQUEST_URI'],PHP_URL_PATH);
if($path!=='/api/chat.php') return false;
require dirname(__DIR__).'/app/ai/assistant.php';
require dirname(__DIR__).'/app/ai/http.php';
define('ISTORE_JSON',true);
require dirname(__DIR__).'/app/bootstrap.php';
$mock=static function(string $instruction,array $input,array $schema):array {
    $message=$input['message'];
    if(str_contains($message,'TIMEOUT'))throw new AiError('timeout');
    if(str_contains($message,'QUOTA'))throw new AiError('quota',429,60);
    if(str_contains($message,'MALFORMED'))return ['wrong'=>true];
    if(str_contains($message,'INVENT'))return ['product_ids'=>[999999],'compare'=>false,'segments'=>[['kind'=>'general','text'=>'test']]];
    if(str_contains($message,'RAM'))return ['product_ids'=>[],'compare'=>false,'segments'=>[['kind'=>'general','text'=>'RAM הוא זיכרון העבודה הזמני. יותר זיכרון מאפשר להריץ במקביל כלי פיתוח ומכולות Docker.']]];
    $ids=array_slice(array_column($input['candidates'],'id'),0,2);
    $text=$ids ? 'כדאי לבדוק את {{p:'.$ids[0].':name}}: {{p:'.$ids[0].':short_description}}. המחיר הוא {{p:'.$ids[0].':price}}. התאמה לעומסי פיתוח תלויה גם בדרישות התוכנות.' : 'לא נמצאה התאמה לדרישות. אפשר לשנות תקציב.';
    return ['product_ids'=>$ids,'compare'=>$input['filters']['compare'],'segments'=>[['kind'=>'catalog','text'=>$text]]];
};
aiHandleChat($db,$mock);
