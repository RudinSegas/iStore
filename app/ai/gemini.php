<?php
declare(strict_types=1);
require_once __DIR__ . '/errors.php';
// The hostname is fixed. Neither visitors nor model output can select a URL.
function aiGemini(array $config, string $instruction, array $input, array $schema, ?callable $transport = null): array {
    $model = $config['model'] ?? '';
    if (!is_string($model) || !preg_match('/^gemini-[a-z0-9.-]{1,80}$/D',$model)) throw new AiError('configuration');
    if (!is_string($config['api_key']??null) || $config['api_key']==='' || preg_match('/[\r\n]/',$config['api_key'])) throw new AiError('configuration');
    $body = json_encode([
        'systemInstruction'=>['parts'=>[['text'=>$instruction]]],
        'contents'=>[['role'=>'user','parts'=>[['text'=>json_encode($input,JSON_UNESCAPED_UNICODE|JSON_THROW_ON_ERROR)]]]],
        'generationConfig'=>['temperature'=>0.3,'maxOutputTokens'=>2048,
            'responseMimeType'=>'application/json','responseSchema'=>$schema],
    ],JSON_THROW_ON_ERROR);
    $url='https://generativelanguage.googleapis.com/v1beta/models/'.$model.':generateContent';
    [$status,$raw] = $transport ? $transport($url,$body) : aiCurl($url,$body,$config);
    if ($status === 429) throw new AiError('quota',429,60);
    if (in_array($status,[401,403,404],true)) throw new AiError('configuration');
    if ($status < 200 || $status >= 300) throw new AiError('upstream');
    try {
        $response=json_decode($raw,true,32,JSON_THROW_ON_ERROR);
        if (!is_array($response) || !is_array($response['candidates']??null)) throw new AiError('response');
        $candidate=$response['candidates'][0]??[];
        if (!is_array($candidate) || !is_array($candidate['content']['parts']??null)) throw new AiError('response');
        if (($candidate['finishReason']??'') !== 'STOP') throw new AiError('response');
        $text='';
        foreach ($candidate['content']['parts']??[] as $part) {
            if (!is_array($part)) throw new AiError('response');
            if (empty($part['thought']) && is_string($part['text']??null)) $text.=$part['text'];
        }
        $result=json_decode($text,true,16,JSON_THROW_ON_ERROR);
        if (!is_array($result) || array_is_list($result)) throw new AiError('response');
        return $result;
    } catch (JsonException) { throw new AiError('response'); }
}
function aiCurl(string $url, string $body, array $config): array {
    if (!function_exists('curl_init')) throw new AiError('configuration');
    $handle=curl_init($url); $raw=''; $oversized=false;
    curl_setopt_array($handle,[CURLOPT_POST=>true, CURLOPT_POSTFIELDS=>$body,
        CURLOPT_HTTPHEADER=>['Content-Type: application/json','x-goog-api-key: '.$config['api_key']],
        CURLOPT_CONNECTTIMEOUT=>8, CURLOPT_TIMEOUT=>max(5,min(28,(int)$config['timeout_seconds'])),
        CURLOPT_SSL_VERIFYPEER=>true, CURLOPT_SSL_VERIFYHOST=>2, CURLOPT_FOLLOWLOCATION=>false,
        CURLOPT_PROTOCOLS=>CURLPROTO_HTTPS,
        CURLOPT_WRITEFUNCTION=>static function($curl,string $chunk) use (&$raw,&$oversized): int {
            if (strlen($raw)+strlen($chunk)>65536) { $oversized=true; return 0; }
            $raw.=$chunk; return strlen($chunk);
        }]);
    $ok=curl_exec($handle); $errno=curl_errno($handle); $status=(int)curl_getinfo($handle,CURLINFO_RESPONSE_CODE); curl_close($handle);
    if ($ok === false || $oversized) throw new AiError($errno === CURLE_OPERATION_TIMEDOUT ? 'timeout' : 'network');
    return [$status,$raw];
}
