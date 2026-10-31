<?php
// Concurrent limiter test worker; local test infrastructure only.
require dirname(__DIR__).'/app/bootstrap.php';
require dirname(__DIR__).'/app/ai/errors.php';
require dirname(__DIR__).'/app/ai/rate-limit.php';
try {
    aiReserve($db,[['race:'.($argv[1]??''),60,5]],str_repeat('s',64),(int)($argv[2]??time()));
    echo 'reserved';
} catch(AiError $e) { if($e->kind!=='rate')throw $e; echo 'limited'; }
