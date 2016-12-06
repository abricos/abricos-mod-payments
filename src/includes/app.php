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
            'Config' => 'PaymentsConfig',
            'OwnerConfig' => 'PaymentsOwnerConfig'
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

    /**
     * @return PaymentsEngine
     */
    public function Engine(){
        $config = $this->Config();

        /** @var PaymentsEngine $app */
        $app = $this->GetApp($config->engineModule, true);

        return $app;
    }

    /**
     * @return PaymentsOwnerConfig
     */
    public function OwnerConfig(){
        if ($this->CacheExists('OwnerConfig')){
            return $this->Cache('OwnerConfig');
        }

        $engine = $this->Engine();
        $data = $engine->OwnerConfigData();

        /** @var PaymentsOwnerConfig $ownerConfig */
        $ownerConfig = $this->InstanceClass('OwnerConfig', $data);

        $this->SetCache('OwnerConfig', $ownerConfig);
        return $ownerConfig;
    }

    public function FormToJSON($orderid){
        $res = $this->Form($orderid);
        return $this->ResultToJSON('form', $res);
    }

    /**
     * @param $orderid
     * @return PaymentsForm|int
     */
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

        $host = Ab_URI::Site();

        /** @var PaymentsForm $form */
        $form = $this->InstanceClass('Form', array(
            "urlReturnOk" => $host.'/payments/pay/ok/'.$order->id.'/',
            "urlReturnNo" => $host.'/payments/pay/no/'.$order->id.'/',
            "engineModule" => $config->engineModule,
            "method" => "POST"
        ));

        $form->order = $order;

        $engine->FormFill($form);

        return $form;
    }

    public function FormHTML($orderid){
        $order = $this->Order($orderid);
        if (AbricosResponse::IsError($order)){
            return "";
        }

        $brick = Brick::$builder->LoadBrickS('payments', 'payButton', null, array(
            "p" => array(
                "order" => $order
            )
        ));

        if (empty($brick)){
            return "";
        }

        return $brick->content;
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

    /**
     * Запросить статус заказа у шлюза
     *
     * @param PaymentsOrder $order
     */
    public function OrderStatusRequest(PaymentsOrder $order){
        $status = $this->Engine()->OrderStatusRequest($order);

        if (empty($status)){
            return false;
        }

        return $this->OrderStatusUpdateMethod($order, $status);
    }

    public function OrderStatusUpdateMethod(PaymentsOrder $order, $status){
        if ($order->status === $status){
            $this->LogTrace('Current status of the order coincides with a new status', array(
                'orderid' => $order->id
            ));
            return false;
        }

        $oldStatus = $order->status;
        $order->status = $status;

        if ($order->status !== $status){
            $this->LogError('Invalid order status', array(
                'orderid' => $order->id,
                'status' => $status
            ));
            return false;
        }

        $this->LogInfo('Order status update', array(
            'orderid' => $order->id,
            'status' => $order->status
        ));

        // TODO: check order status

        PaymentsQuery::OrderStatusUpdate($this, $order);

        $config = $this->Config();

        if (!$config->notifyOrderStatusChange){
            return true;
        }

        /** @var NotifyApp $notifyApp */
        $notifyApp = Abricos::GetApp('notify');

        $notifyBrick = Brick::$builder->LoadBrickS("payments", "notifyOrderStatus");
        $v = &$notifyBrick->param->var;

        $emails = explode(",", $config->notifyEmail);
        for ($i = 0; $i < count($emails); $i++){
            $email = trim($emails[$i]);
            if (empty($email)){
                continue;
            }

            $mail = $notifyApp->MailByFields(
                $email,
                Brick::ReplaceVarByData($v['subject'], array(
                    "status" => $order->status
                )),
                Brick::ReplaceVarByData($notifyBrick->content, array(
                    "email" => $email,
                    "orderid" => $order->id,
                    "status" => $order->status,
                    "oldStatus" => $oldStatus,
                    "total" => $order->total,
                    "sitename" => SystemModule::$instance->GetPhrases()->Get('site_name')
                ))
            );

            $notifyApp->MailSend($mail);
        }

        return true;
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

    public function PaymentsMethodInfoHTML(){
        $config = $this->Config();

        $brick = Brick::$builder->LoadBrickS($config->engineModule, 'methodsInfo', null, null);

        if (empty($brick)){
            return "";
        }

        return $brick->content;
    }

    /**
     * Запрос платежного шлюза на этот сайт
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
     * @return PaymentsConfig|int
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

        if (!isset($d['notifyOrderStatusChange'])){
            $d['notifyOrderStatusChange'] = true;
        }

        if (!isset($d['notifyEmail'])){
            $d['notifyEmail'] = '';
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
        $config = $this->Config();

        $config->engineModule = $utmf->Parser($d->engineModule);
        $config->notifyOrderStatusChange = $d->notifyOrderStatusChange;
        $config->notifyEmail = $utmf->Parser($d->notifyEmail);

        $phs = Abricos::GetModule('payments')->GetPhrases();
        $phs->Set("engineModule", $config->engineModule);
        $phs->Set("notifyOrderStatusChange", $config->notifyOrderStatusChange);
        $phs->Set("notifyEmail", $config->notifyEmail);

        Abricos::$phrases->Save();
    }
}
