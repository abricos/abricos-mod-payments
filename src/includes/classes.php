<?php
/**
 * @package Abricos
 * @subpackage Payments
 * @copyright 2016 Alexander Kuzmin
 * @license http://opensource.org/licenses/mit-license.php MIT License
 * @author Alexander Kuzmin <roosit@abricos.org>
 */

/**
 * Class PaymentsEngine
 */
abstract class PaymentsEngine extends AbricosApplication {

    /**
     * Оплата по заказу не производилась
     */
    const STATUS_NOT = 'not';

    /**
     * Средства успешно заблокированы (выполнена авторизационная транзакция)
     */
    const STATUS_AUTHORIZED = 'authorized';

    /**
     * Средства не заблокированы (авторизационная транзакция не выполнена) по ряду причин
     */
    const STATUS_NOT_AUTHORIZED = 'not_authorized';

    /**
     * Оплачен (выполнена финансовая транзакция или заказ оплачен в электронной платёжной системе)
     */
    const STATUS_PAID = 'paid';

    /**
     * Отменён (выполнена транзакция разблокировки
     * средств или выполнена операция по возврату платежа после
     * списания средств; при частичных отмене/возврате платежа этот
     * статус не присваивается)
     */
    const STATUS_CANCELED = 'canceled';

    /**
     * Ожидается оплата выставленного счёта. Статус
     * используется только для оплат электронными валютами, при
     * которых процесс оплаты может содержать этап выставления
     * через платежную систему счёта на оплату и этап фактической
     * оплаты этого счёта Покупателем, которые существенно
     * разнесённы во времени
     */
    const STATUS_WAITING = 'waiting';

    public abstract function FormFill(PaymentsForm $form);

    public abstract function API($action, $p1, $p2, $p3);
}

?>