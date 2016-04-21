<?php
/**
 * @package Abricos
 * @subpackage Payments
 * @copyright 2016 Alexander Kuzmin
 * @license http://opensource.org/licenses/mit-license.php MIT License
 * @author Alexander Kuzmin <roosit@abricos.org>
 */

$charset = "CHARACTER SET 'utf8' COLLATE 'utf8_general_ci'";
$updateManager = Ab_UpdateManager::$current;
$db = Abricos::$db;
$pfx = $db->prefix;

if ($updateManager->isUpdate('0.1.0')){
    Abricos::GetModule('payments')->permission->Install();

    $db->query_write("
		CREATE TABLE IF NOT EXISTS ".$pfx."payments_order (
		  orderid VARCHAR(32) NOT NULL DEFAULT '' COMMENT '',
		  ownerModule VARCHAR(50) NOT NULL DEFAULT '' COMMENT '',
		  ownerType VARCHAR(50) NOT NULL DEFAULT '' COMMENT '',
		  ownerId INT(10) unsigned NOT NULL DEFAULT 0,
          total double(10, 2) NOT NULL DEFAULT 0 COMMENT '',
		  dateline INT(10) unsigned NOT NULL DEFAULT 0,
		  UNIQUE KEY orderid (orderid)
		 )".$charset
    );

}

?>