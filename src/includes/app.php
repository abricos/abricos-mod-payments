<?php
/**
 * @package Abricos
 * @subpackage Payments
 * @copyright 2016 Alexander Kuzmin
 * @license http://opensource.org/licenses/mit-license.php MIT License
 * @author Alexander Kuzmin <roosit@abricos.org>
 */

require_once 'classes.php';

/**
 * Class PaymentsManager
 *
 * @property PaymentsManager $manager
 */
class PaymentsApp extends AbricosApplication {

    protected function GetClasses(){
        return array(
            'Order' => 'PaymentsOrder',
            'Form' => 'PaymentsForm',
            'Config' => 'PaymentsConfig'
        );
    }

    protected function GetStructures(){
        return 'Order,Form,Config';
    }

    public function ResponseToJSON($d){
        switch ($d->do){
            case "form":
                return $this->FormToJSON($d->orderid);
            case "config":
                return $this->ConfigToJSON();
            case "configSave":
                return $this->ConfigSaveToJSON($d->config);

        }
        return null;
    }

    public function Engine(){
        $config = $this->Config();

        /** @var PaymentsEngine $app */
        $app = $this->GetApp($config->engineModule, true);

        return $app;
    }

    public function OrderAppend($ownerModule, $ownerType, $ownerId, $total){
        $orderid = md5(md5($ownerModule.$ownerType.$ownerId).md5(TIMENOW));

        PaymentsQuery::OrderAppend($this, $ownerModule, $ownerType, $ownerId, $orderid, $total);

        $this->LogDebug('New order append', array(
            'orderid' => $orderid,
            'ownerModule' => $ownerModule,
            'ownerType' => $ownerType,
            'ownerId' => $ownerId,
            'total' => $total
        ));

        return $this->Order($orderid);
    }

    public function FormToJSON($orderid){
        $res = $this->Form($orderid);
        return $this->ResultToJSON('form', $res);
    }

    public function Form($orderid){
        if (!$this->manager->IsViewRole()){
            return AbricosResponse::ERR_FORBIDDEN;
        }

        $order = $this->Order($orderid);
        if (AbricosResponse::IsError($order)){
            return AbricosResponse::ERR_NOT_FOUND;
        }

        $engine = $this->Engine();

        if (empty($engine)){
            return AbricosResponse::ERR_SERVER_ERROR;
        }

        $config = $this->Config();

        /** @var PaymentsForm $form */
        $form = $this->InstanceClass('Form');

        $host = 'http://'.Ab_URI::fetch_host();

        $form->urlReturnOk = $host.'/payments/pay/ok/'.$order->id.'/';
        $form->urlReturnNo = $host.'/payments/pay/no/'.$order->id.'/';

        $form->engineModule = $config->engineModule;
        $form->order = $order;

        $engine->FormFill($form);

        return $form;
    }

    /**
     * @param $orderid
     * @return int|PaymentsOrder
     */
    public function Order($orderid){
        if (isset($this->_cache['Order'][$orderid])){
            return $this->_cache['Order'][$orderid];
        }

        $d = PaymentsQuery::Order($this, $orderid);

        if (empty($d)){
            return AbricosResponse::ERR_NOT_FOUND;
        }

        /** @var PaymentsOrder $order */
        $order = $this->InstanceClass('Order', $d);

        $this->_cache['Order'][$orderid] = $order;

        return $order;
    }

    public function OrderStatusUpdateMethod(PaymentsOrder $order){

        $this->LogInfo('Order status update', array(
            'orderid' => $order->id,
            'status' => $order->status
        ));

        // TODO: check order status

        PaymentsQuery::OrderStatusUpdate($this, $order);
    }

    public function OrderInfoHTML($orderid){
        $order = $this->Order($orderid);
        if (AbricosResponse::IsError($order)){
            return "";
        }

        $brick = Brick::$builder->LoadBrickS($order->ownerModule, 'paymentOrderInfo', null, array(
            "p" => array(
                "order" => $order
            )
        ));

        if (empty($brick)){
            return "";
        }

        return $brick->content;
    }

    /**
     * Запрос платежного сервера на этот сайт
     *
     * Например: http://mysite.tld/payments/api/uniteller/orderStatusUpdate/
     */
    public function PayAPI($engineModuleName, $action, $p1, $p2, $p3){

        $this->LogTrace('API request', array(
            'engineModule' => $engineModuleName,
            'action' => $action,
            'p1' => $p1,
            'p2' => $p2,
            'p3' => $p3,
        ));

        /** @var PaymentsEngine $engineApp */
        $engineApp = Abricos::GetApp($engineModuleName);
        if (empty($engineApp)){
            $this->LogError('Engine module not found in API request', array(
                'engineModule' => $engineModuleName
            ));
            return AbricosResponse::ERR_BAD_REQUEST;
        }

        return $engineApp->API($action, $p1, $p2, $p3);
    }

    public function ConfigToJSON(){
        $res = $this->Config();
        return $this->ResultToJSON('config', $res);
    }

    /**
     * @return PaymentsConfig
     */
    public function Config(){
        if (isset($this->_cache['Config'])){
            return $this->_cache['Config'];
        }

        if (!$this->manager->IsViewRole()){
            return AbricosResponse::ERR_FORBIDDEN;
        }

        $phrases = Abricos::GetModule('payments')->GetPhrases();

        $d = array();
        for ($i = 0; $i < $phrases->Count(); $i++){
            $ph = $phrases->GetByIndex($i);
            $d[$ph->id] = $ph->value;
        }

        if (!isset($d['engineModule'])){
            $d['engineModule'] = 'uniteller';
        }

        return $this->_cache['Config'] = $this->InstanceClass('Config', $d);
    }

    public function ConfigSaveToJSON($d){
        $this->ConfigSave($d);
        return $this->ConfigToJSON();
    }

    public function ConfigSave($d){
        if (!$this->manager->IsAdminRole()){
            return AbricosResponse::ERR_FORBIDDEN;
        }

        $utmf = Abricos::TextParser(true);
        $d->shopid = $utmf->Parser($d->shopid);

        $phs = Abricos::GetModule('payments')->GetPhrases();
        $phs->Set("engineModule", $d->engineModule);

        Abricos::$phrases->Save();
    }

}

?>