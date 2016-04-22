<?php
/**
 * @package Abricos
 * @subpackage Payments
 * @copyright 2016 Alexander Kuzmin
 * @license http://opensource.org/licenses/mit-license.php MIT License
 * @author Alexander Kuzmin <roosit@abricos.org>
 */

/**
 * Class PaymentsModule
 */
class PaymentsModule extends Ab_Module {

    public function __construct(){
        $this->version = "0.1.0";
        $this->name = "payments";
        $this->takelink = "payments";

        $this->permission = new PaymentsPermission($this);
    }

    public function Bos_IsMenu(){
        return true;
    }

    public function GetContentName(){
        $adress = Abricos::$adress;
        $dir = $adress->dir;


        if ($adress->level >= 3 && $dir[1] === 'pay'){
            switch ($dir[2]){
                case "ok";
                    return "payOk";
                case "no";
                    return "payNo";
            }
        }
        return null;
    }


}

class PaymentsAction {
    const VIEW = 10;
    const WRITE = 30;
    const ADMIN = 50;
}

class PaymentsPermission extends Ab_UserPermission {

    public function __construct(PaymentsModule $module){
        $defRoles = array(
            new Ab_UserRole(PaymentsAction::VIEW, Ab_UserGroup::GUEST),
            new Ab_UserRole(PaymentsAction::VIEW, Ab_UserGroup::REGISTERED),
            new Ab_UserRole(PaymentsAction::VIEW, Ab_UserGroup::ADMIN),

            new Ab_UserRole(PaymentsAction::WRITE, Ab_UserGroup::ADMIN),

            new Ab_UserRole(PaymentsAction::ADMIN, Ab_UserGroup::ADMIN)
        );
        parent::__construct($module, $defRoles);
    }

    public function GetRoles(){
        return array(
            PaymentsAction::VIEW => $this->CheckAction(PaymentsAction::VIEW),
            PaymentsAction::WRITE => $this->CheckAction(PaymentsAction::WRITE),
            PaymentsAction::ADMIN => $this->CheckAction(PaymentsAction::ADMIN)
        );
    }
}

Abricos::ModuleRegister(new PaymentsModule());

?>