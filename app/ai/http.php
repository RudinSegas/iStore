<?php
declare(strict_types=1);
function aiHandleChat(PDO $db, ?callable $model=null): never {
$pendingToken=null;
try {
    if ($_SERVER['REQUEST_METHOD']!=='POST') { header('Allow: POST'); throw new AiError('method',405); }
    $csrf=$_SERVER['HTTP_X_CSRF_TOKEN']??'';
    if (!is_string($csrf)||!hash_equals($_SESSION['csrf'],$csrf)) throw new AiError('csrf',403);
    // Read one byte over the limit to detect an oversized body without buffering it all.
    $request=aiReadRequest(file_get_contents('php://input',false,null,0,8193)?:'',$_SERVER['CONTENT_TYPE']??'');
    $state=aiConversation($_SESSION['ai']??[]);
    if ($request['action']==='history') aiJson(['ok'=>true,'history'=>$state['history']??[]]);
    if ((int)($_SESSION['ai_pending']['until']??0)>time()) throw new AiError('busy',409);
    if ($request['action']==='reset') { unset($_SESSION['ai']); aiJson(['ok'=>true,'message'=>'השיחה אופסה.','products'=>[]]); }
    $aiConfig=aiConfig();
    if (!aiReady($aiConfig)) throw new AiError('disabled');
    aiReserve($db,[['session:'.session_id(),60,$aiConfig['session_per_minute']],
        ['ip:'.($_SERVER['REMOTE_ADDR']??'unknown'),60,$aiConfig['ip_per_minute']]],$aiConfig['rate_secret']);
    $pendingToken=bin2hex(random_bytes(16));
    $_SESSION['ai_pending']=['token'=>$pendingToken,'until'=>time()+35];
    session_write_close(); // Do not block cart/auth requests during Gemini network I/O.
    [$answer,$newState]=aiAnswer($db,$aiConfig,$request['message'],$state,$model);
    session_start();
    if (!hash_equals((string)($_SESSION['ai_pending']['token']??''),$pendingToken)) throw new AiError('busy',409);
    $_SESSION['ai']=$newState; unset($_SESSION['ai_pending']);
    aiJson(['ok'=>true]+$answer);
} catch (Throwable $error) {
    if ($pendingToken!==null) {
        if (session_status()!==PHP_SESSION_ACTIVE) session_start();
        if (($_SESSION['ai_pending']['token']??null)===$pendingToken) unset($_SESSION['ai_pending']);
    }
    $failure=$error instanceof AiError ? $error : new AiError('internal');
    if ($failure->retryAfter) header('Retry-After: '.$failure->retryAfter);
    // Do not log upstream bodies, prompts, keys, SQL values or exception messages.
    error_log('iStore AI failure: '.$failure->kind);
    aiJson(['ok'=>false,'error'=>$failure->kind,'message'=>aiErrorMessage($failure->kind)],$failure->status);
}

}
