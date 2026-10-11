<?php
// Small domain checks independent of MySQL. Run: php scripts/check.php
require dirname(__DIR__).'/app/functions.php';
function expect(bool $condition, string $label): void { if (!$condition) throw new RuntimeException($label); echo "PASS $label\n"; }
expect(e('<script>')==='&lt;script&gt;', 'HTML escaping');
expect(money(299900)==='₪2,999.00', 'integer cents formatting');
expect(validQuantity('0')===0 && validQuantity('20')===20, 'quantity boundaries');
foreach ([-1,21,'1.5','bad'] as $value) {
    try { validQuantity($value); throw new LogicException('Invalid quantity accepted'); }
    catch (RuntimeException $e) { echo "PASS invalid quantity rejected\n"; }
}
