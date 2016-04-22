<?php
/**
 * @package Abricos
 * @subpackage Payments
 * @copyright 2016 Alexander Kuzmin
 * @license http://opensource.org/licenses/mit-license.php MIT License
 * @author Alexander Kuzmin <roosit@abricos.org>
 */

$brick = Brick::$builder->brick;
$p = &$brick->param->param;
$v = &$brick->param->var;

/** @var PaymentsApp $app */
$app = Abricos::GetApp('payments');

$dir = Abricos::$adress->dir;

// http://example.com/payments/test/uniteller/

$engineAppName = isset($dir[2]) ? $dir[2] : '';

/** @var PaymentsEngine $engineApp */
$engineApp = Abricos::GetApp($engineAppName);
if (empty($engineApp)){
    return;
}

$brickTest = Brick::$builder->LoadBrickS($engineAppName, 'test', $brick, array());

$brick->content = Brick::ReplaceVarByData($brick->content, array(
    "result" => $brickTest->content
));


?>