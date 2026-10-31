<?php
declare(strict_types=1);
ini_set('display_errors','0');
require dirname(__DIR__,2).'/app/ai/assistant.php';
define('ISTORE_JSON',true);
require dirname(__DIR__,2).'/app/bootstrap.php';
require dirname(__DIR__,2).'/app/ai/http.php';
aiHandleChat($db);
