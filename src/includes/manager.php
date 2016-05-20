<?php
/**
 * @package Abricos
 * @subpackage Payments
 * @copyright 2016 Alexander Kuzmin
 * @license http://opensource.org/licenses/mit-license.php MIT License
 * @author Alexander Kuzmin <roosit@abricos.org>
 */

/**
 * Class PaymentsManager
 */
class PaymentsManager extends Ab_ModuleManager {

    public function IsAdminRole(){
        return $this->IsRoleEnable(PaymentsAction::ADMIN);
    }

    public function IsWriteRole(){
        if ($this->IsAdminRole()){
            return true;
        }
        return $this->IsRoleEnable(PaymentsAction::WRITE);
    }

    public function IsViewRole(){
        if ($this->IsWriteRole()){
            return true;
        }
        return $this->IsRoleEnable(PaymentsAction::VIEW);
    }

    public function AJAX($d) {
        return $this->GetApp()->AJAX($d);
    }

    public function Bos_MenuData(){
        if (!$this->IsAdminRole()){
            return null;
        }
        $i18n = $this->module->I18n();
        return array(
            array(
                "name" => "payments",
                "title" => $i18n->Translate('title'),
                "icon" => "/modules/payments/images/cp_icon.gif",
                "url" => "payments/wspace/ws",
                "parent" => "controlPanel"
            )
        );
    }
}
