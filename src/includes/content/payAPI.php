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

// http://example.com/payments/api/uniteller/

/** @var PaymentsEngine $engineApp */
$engineApp = Abricos::GetApp(isset($dir[2]) ? $dir[2] : '');
if (empty($engineApp)){
    $brick->content = "Error 500";
    return;
}

$order = $engineApp->OrderStatusByPOST();

if (AbricosResponse::IsError($order)){
    $brick->content = "Error ".$order;
    return;
}

PaymentsQuery::OrderStatusUpdate($app, $order);

$brick->content = "OK";

?>