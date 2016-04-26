<?php
/**
 * @package Abricos
 * @subpackage Payments
 * @copyright 2016 Alexander Kuzmin
 * @license http://opensource.org/licenses/mit-license.php MIT License
 * @author Alexander Kuzmin <roosit@abricos.org>
 */

// Example: http://mysite.tld/payments/api/uniteller/orderStatusUpdate/

$brick = Brick::$builder->brick;
$p = &$brick->param->param;
$v = &$brick->param->var;

$dir = Abricos::$adress->dir;
$engineModuleName = isset($dir[2]) ? $dir[2] : '';
$action = isset($dir[3]) ? $dir[3] : '';
$p1 = isset($dir[4]) ? $dir[4] : '';
$p2 = isset($dir[5]) ? $dir[5] : '';
$p3 = isset($dir[6]) ? $dir[6] : '';

/** @var PaymentsApp $app */
$app = Abricos::GetApp('payments');
$result = $app->PayAPI($engineModuleName, $action, $p1, $p2, $p3);

if (AbricosResponse::IsError($result)){
    $brick->content = "Error ".$result;
    return;
}

$brick->content = "OK";


?>