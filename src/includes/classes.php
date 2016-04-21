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

    abstract function FormFill(PaymentsForm $form);

}

?>