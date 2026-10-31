<?php
declare(strict_types=1);
ini_set('display_errors','0');
// Isolated, opt-in connectivity probe: no application bootstrap, database or catalog.
$root=dirname(__DIR__,2);
require $root.'/app/ai/config.php';
require $root.'/app/ai/gemini.php';
header('X-Content-Type-Options: nosniff');
$config=aiConfig();
$token=$_SERVER['HTTP_X_AI_TEST_TOKEN']??'';
if (empty($config['connectivity_test_enabled']) || strlen((string)$config['connectivity_test_token'])<32
    || !is_string($token) || !hash_equals($config['connectivity_test_token'],$token)) {
    http_response_code(404); exit;
}
try {
    if ($_SERVER['REQUEST_METHOD']!=='POST') { header('Allow: POST'); throw new AiError('method',405); }
    ini_set('session.use_strict_mode','1');
    session_name('istore_probe');
    session_set_cookie_params(['httponly'=>true,'secure'=>(!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS']!=='off'),'samesite'=>'Strict']);
    session_start();
    if (time()-(int)($_SESSION['last_probe']??0)<60) throw new AiError('rate',429,60);
    $_SESSION['last_probe']=time(); session_write_close();
    if ($config['api_key']==='') throw new AiError('configuration');
    $started=microtime(true);
    $response=aiGemini($config,'Return exactly the JSON object {"ok":true}.',['connectivity_check'=>true],
        ['type'=>'OBJECT','properties'=>['ok'=>['type'=>'BOOLEAN']],'required'=>['ok']]);
    if (($response['ok']??null)!==true) throw new AiError('response');
    aiJson(['ok'=>true,'model'=>$config['model'],'elapsed_ms'=>(int)round((microtime(true)-$started)*1000),'message'=>'Gemini HTTPS request and structured JSON response succeeded.']);
} catch (Throwable $error) {
    $failure=$error instanceof AiError?$error:new AiError('internal');
    if($failure->retryAfter) header('Retry-After: '.$failure->retryAfter);
    aiJson(['ok'=>false,'error'=>$failure->kind,'message'=>'Connectivity check failed. No credentials or upstream response are exposed.'],$failure->status);
}
