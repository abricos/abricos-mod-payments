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

/** @var PaymentsOrder $order */
$order = $p['order'];
if (empty($order) || AbricosResponse::IsError($order)){
    $brick->content = "";
    return;
}

$form = $app->Form($order->id);

$params = "";
foreach ($form->params as $name => $value){
    $params .= Brick::ReplaceVarByData($v['param'], array(
        "name" => $name,
        "value" => $value
    ));
}

$brick->content = Brick::ReplaceVarByData($brick->content, array(
    "url" => $form->url,
    "params" => $params
));