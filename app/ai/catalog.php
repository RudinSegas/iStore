<?php
declare(strict_types=1);
function aiFilterSchema(): array {
    return ['type'=>'OBJECT','properties'=>[
        'category_id'=>['type'=>'INTEGER','nullable'=>true],
        'min_price_cents'=>['type'=>'INTEGER','nullable'=>true],
        'max_price_cents'=>['type'=>'INTEGER','nullable'=>true],
        'terms'=>['type'=>'ARRAY','items'=>['type'=>'STRING'],'maxItems'=>6],
        'compare'=>['type'=>'BOOLEAN'],
        'follow_up'=>['type'=>'BOOLEAN'],
        'shopping'=>['type'=>'BOOLEAN'],
    ],'required'=>['category_id','min_price_cents','max_price_cents','terms','compare','follow_up','shopping']];
}
function aiValidateFilters(array $value, array $categories): array {
    foreach (['compare','follow_up','shopping'] as $key) if (!is_bool($value[$key]??null)) throw new AiError('response');
    foreach (['category_id','min_price_cents','max_price_cents'] as $key) {
        if (!array_key_exists($key,$value) || ($value[$key]!==null && (!is_int($value[$key]) || $value[$key]<0 || $value[$key]>100000000))) throw new AiError('response');
    }
    if ($value['category_id']!==null && !in_array($value['category_id'],array_map('intval',array_column($categories,'id')),true)) throw new AiError('response');
    if ($value['min_price_cents']!==null && $value['max_price_cents']!==null && $value['min_price_cents']>$value['max_price_cents']) throw new AiError('response');
    if (!is_array($value['terms']??null) || !array_is_list($value['terms']) || count($value['terms'])>6) throw new AiError('response');
    $terms=[];
    foreach ($value['terms'] as $term) {
        if (!is_string($term) || mb_strlen($term)>80) throw new AiError('response');
        if (trim($term)!=='') $terms[]=trim($term);
    }
    return array_intersect_key(array_replace($value,['terms'=>$terms]),array_flip(['category_id','min_price_cents','max_price_cents','terms','compare','follow_up','shopping']));
}
function aiCatalog(PDO $db, array $filter): array {
    $where=[]; $params=[];
    if (!$filter['compare']) $where[]='p.stock>0';
    foreach (['category_id'=>'p.category_id = ?', 'min_price_cents'=>'p.price_cents >= ?', 'max_price_cents'=>'p.price_cents <= ?'] as $key=>$clause) {
        if ($filter[$key]!==null) { $where[]=$clause; $params[]=$filter[$key]; }
    }
    $base=$where; $baseParams=$params;
    if ($filter['terms']) {
        $parts=[];
        foreach ($filter['terms'] as $term) {
            // Escape LIKE metacharacters using a fixed ESCAPE character.
            $like='%'.strtr($term,['!'=>'!!','%'=>'!%','_'=>'!_']).'%';
            $parts[]="(p.name LIKE ? ESCAPE '!' OR p.short_description LIKE ? ESCAPE '!' OR p.description LIKE ? ESCAPE '!')";
            array_push($params,$like,$like,$like);
        }
        $where[]='('.implode(' OR ',$parts).')';
    }
    $select='SELECT p.id,p.name,p.short_description,p.description,p.price_cents,p.stock,p.category_id,c.name AS category FROM products p JOIN categories c ON c.id=p.category_id';
    $read=fn($w,$p)=>query($db,$select.($w?' WHERE '.implode(' AND ',$w):'').' ORDER BY p.price_cents,p.id LIMIT 12',$p)->fetchAll();
    $rows=$read($where,$params);
    // Semantic needs may not be verbatim in descriptions. Retain budget/category on fallback.
    if (!$rows && $filter['terms']) $rows=$read($base,$baseParams);
    foreach ($rows as &$row) {
        foreach (['id','price_cents','stock','category_id'] as $key) $row[$key]=(int)$row[$key];
        $row['short_description']=mb_substr($row['short_description'],0,255);
        $row['description']=mb_substr($row['description'],0,1500);
    }
    return $rows;
}
function aiSelectProducts(array $result, array $rows): array {
    $ids=$result['product_ids']??null;
    if (!is_array($ids) || !array_is_list($ids) || count($ids)>4) throw new AiError('response');
    foreach ($ids as $id) if (!is_int($id)) throw new AiError('response');
    if (count($ids)!==count(array_unique($ids))) throw new AiError('response');
    $byId=array_column($rows,null,'id'); $selected=[];
    foreach ($ids as $id) {
        if (!is_int($id) || !isset($byId[$id])) throw new AiError('response');
        $p=$byId[$id];
        $selected[]=['id'=>$id,'name'=>$p['name'],'price_cents'=>$p['price_cents'],'stock'=>$p['stock'],
            'category'=>$p['category'],'short_description'=>$p['short_description'],'description'=>$p['description'],
            'url'=>'/?page=product&id='.$id];
    }
    return $selected;
}

// Deterministic retrieval: user text is data, never executable SQL.
function aiLocalFilters(string $message, array $categories, array $state): array {
    $follow=(bool)preg_match('/אלה|אלו|מהם|מביניהם|שניהם|יותר|הראשון|השני|אותו|בהם|לזה|ומה|ומה עם|these|those|cheaper|instead|which one/iu',$message);
    $f=['category_id'=>null,'min_price_cents'=>null,'max_price_cents'=>null,'terms'=>[],
        'compare'=>(bool)preg_match('/השוו|השווה|השווא|לעומת|הבדל|compare|versus|\\bvs\\b/iu',$message),'follow_up'=>$follow,'shopping'=>true];
    if ($follow && isset($state['filters'])) $f=array_replace($f,array_intersect_key($state['filters'],array_flip(['category_id','min_price_cents','max_price_cents','terms'])));
    $aliases=['phones'=>'טלפון|סמארטפון|אייפון|גלקסי|phone|iphone|galaxy|pixel',
        'computers'=>'מחשב|לפטופ|נייד|מקבוק|laptop|computer|macbook|thinkpad|vivobook',
        'consoles'=>'קונסול|פלייסטיישן|אקסבוקס|console|playstation|xbox|nintendo',
        'monitors'=>'מסך|monitor|odyssey'];
    $matched=[];
    foreach($categories as $c) if(preg_match('/'.($aliases[$c['slug']]??preg_quote($c['name'],'/')).'/iu',$message)) $matched[]=(int)$c['id'];
    if(count(array_unique($matched))===1) {
        if($f['category_id']!==null && $f['category_id']!==$matched[0]) $f['terms']=[];
        $f['category_id']=$matched[0];
    } elseif(count($matched)>1) $f['category_id']=null;
    $number='([0-9]{1,6}(?:,[0-9]{3})?(?:\\.[0-9]{1,2})?)';
    if(preg_match('/(?:עד|תקציב(?: של)?|מתחת ל|under|budget(?: of)?|up to)\\s*₪?\\s*'.$number.'/iu',$message,$m)) $f['max_price_cents']=min(100000000,(int)round((float)str_replace(',','',$m[1])*100));
    if(preg_match('/(?:מעל|לפחות|over|at least)\\s*₪?\\s*'.$number.'/iu',$message,$m)) $f['min_price_cents']=min(100000000,(int)round((float)str_replace(',','',$m[1])*100));
    if(preg_match('/(?:בין|between)\\s*'.$number.'\\s*(?:ל|ו|and|-)\\s*'.$number.'/iu',$message,$m)) {
        $f['min_price_cents']=(int)round((float)str_replace(',','',$m[1])*100);
        $f['max_price_cents']=(int)round((float)str_replace(',','',$m[2])*100);
    }
    if(preg_match('/ללא תקציב|בלי הגבלת|no budget|any price/iu',$message)) $f['min_price_cents']=$f['max_price_cents']=null;
    preg_match_all(
    '/iphone|galaxy|pixel|thinkpad|macbook|vivobook|playstation|xbox|nintendo|odyssey|dell|lenovo|asus|samsung|apple|lg|ps5/iu',
    $message,
    $terms
);

if ($terms[0]) {
    $f['terms'] = array_slice(array_unique($terms[0]), 0, 6);
}

// Detect requests for a device to connect to an existing monitor.
$monitorAccessoryIntent =
    preg_match('/מסך|monitor|ultragear|odyssey/iu', $message) &&
    preg_match('/לחבר|לשחק|גיימינג|קונסול|מה לקנות עם|connect|gaming|play/iu', $message);

if ($monitorAccessoryIntent) {
    // Search for complementary products instead of the mentioned monitor.
    $f['category_id'] = null;
    $f['terms'] = [];

    // Avoid carrying filters from the previously discussed monitor.
    $f['follow_up'] = false;
}

return aiValidateFilters($f, $categories);
}
