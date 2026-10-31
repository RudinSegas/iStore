<?php
declare(strict_types=1);
if(PHP_SAPI!=='cli') { http_response_code(404);exit; }
$root=dirname(__DIR__);
foreach(['pdo_mysql','curl','mbstring'] as $extension)if(!extension_loaded($extension)){fwrite(STDERR,"Missing PHP extension: $extension\n");exit(1);}
foreach(['config','ai'] as $name){
 $target="$root/config/$name.php";
 if(file_exists($target)){echo "$name.php already exists; left unchanged.\n";continue;}
 $config=require "$root/config/$name.example.php";
 $config[$name==='ai'?'rate_secret':'app_secret']=bin2hex(random_bytes(32));
 if($name==='ai'){$config['enabled']=false;$config['api_key']='';}
 $handle=fopen($target,'x');if(!$handle)throw new RuntimeException('Cannot create private configuration');
 fwrite($handle,"<?php\n// Private. Never commit or upload in a release archive.\nreturn ".var_export($config,true).";\n");fclose($handle);chmod($target,0600);
 echo "Created private config/$name.php. Edit locally; credentials are not printed.\n";
}
