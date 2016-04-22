<?php
/**
 * @package Abricos
 * @subpackage Payments
 * @copyright 2016 Alexander Kuzmin
 * @license http://opensource.org/licenses/mit-license.php MIT License
 * @author Alexander Kuzmin <roosit@abricos.org>
 */

/**
 * Class PaymentsQuery
 */
class PaymentsQuery {

    public static function OrderAppend(AbricosApplication $app, $ownerModule, $ownerType, $ownerId, $orderId, $total){
        $db = $app->db;
        $sql = "
            INSERT INTO ".$db->prefix."payments_order
            (orderid, ownerModule, ownerType, ownerId, total, dateline) VALUES (
                '".bkstr($orderId)."',
                '".bkstr($ownerModule)."',
                '".bkstr($ownerType)."',
                ".intval($ownerId).",
                ".doubleval($total).",
                ".intval(TIMENOW)."
            )
        ";
        $db->query_write($sql);
    }

    public static function Order(AbricosApplication $app, $orderId){
        $db = $app->db;
        $sql = "
            SELECT *
            FROM ".$db->prefix."payments_order
            WHERE orderid='".bkstr($orderId)."'
            LIMIT 1
        ";
        return $db->query_first($sql);
    }

    /**
     * @param AbricosApplication $app
     * @param PaymentsOrder $order
     */
    public static function OrderStatusUpdate(AbricosApplication $app, $order){
        $db = $app->db;
        $sql = "
            UPDATE ".$db->prefix."payments_order
            SET status='".bkstr($order->status)."'
            WHERE orderid='".bkstr($order->id)."'
            LIMIT 1
        ";
        return $db->query_write($sql);
    }

}

?>