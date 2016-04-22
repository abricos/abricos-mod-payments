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

$orderid = isset($dir[3]) ? $dir[3] : 0;
$order = $app->Order($orderid);

$result = "";

if (AbricosResponse::IsError($order)){
    $result = $v['orderNotFound'];
} else {
    
    $orderInfo = $app->OrderInfoHTML($orderid);

    $result = Brick::ReplaceVarByData($v['orderInfo'], array(
        "orderid" => $orderid,
        "orderInfo" => $orderInfo
    ));

}

$brick->content = Brick::ReplaceVarByData($brick->content, array(
    "result" => $result
));


?>