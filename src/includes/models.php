<?php
/**
 * @package Abricos
 * @subpackage Payments
 * @copyright 2016 Alexander Kuzmin
 * @license http://opensource.org/licenses/mit-license.php MIT License
 * @author Alexander Kuzmin <roosit@abricos.org>
 */

/**
 * Class PaymentsOrder
 *
 * @property string $orderId
 * @property string $ownerModule
 * @property string $ownerType
 * @property int $ownerId
 * @property double $total Order cost
 * @property string $status
 * @property int $dateline
 */
class PaymentsOrder extends AbricosModel {
    protected $_structModule = 'payments';
    protected $_structName = 'Order';
}

/**
 * Class PaymentsForm
 *
 * @property string $engineModule
 * @property string $url
 * @property string $urlReturnOk
 * @property string $urlReturnNo
 * @property string $method
 * @property object $params
 * @property PaymentsOrder $order
 */
class PaymentsForm extends AbricosModel {
    protected $_structModule = 'payments';
    protected $_structName = 'Form';
}


/**
 * Class PaymentsConfig
 *
 * @property string $engineModule
 * @property bool $notifyOrderStatusChange
 * @property string $notifyEmail
 */
class PaymentsConfig extends AbricosModel {
    protected $_structModule = 'payments';
    protected $_structName = 'Config';
}

/**
 * Class PaymentsOwnerConfig
 *
 * @property string $module
 * @property bool cronCheckOrderStatus
 */
class PaymentsOwnerConfig extends AbricosModel {
    protected $_structModule = 'payments';
    protected $_structName = 'OwnerConfig';
}
