<?php

if (!defined('_PS_VERSION_')) {
    exit;
}

class BrDebitoEnCuenta extends PaymentModule
{
    public function __construct()
    {
        $this->name = 'brosco-debito-en-cuenta';
        $this->tab = 'payments_gateways';
        $this->version = '0.9.0';
        $this->author = 'Pablo Santa Cruz';
        $this->need_instance = 0;

        parent::__construct();

        $this->displayName = $this->l('BrosCo - Débito en Cuenta');
        $this->description = $this->l('Este método de pago permite a los clientes pagar con débito en cuenta a través de las aplicaciones de BrosCo Full y BrosCo App.');

        $this->confirmUninstall = $this->l('Seguro que quiere desinstalar?');
    }

    public function install()
    {
        if (!parent::install() ||
            !$this->registerHook('paymentOptions') ||
            !$this->registerHook('paymentReturn')) {
            return false;
        }

        return true;
    }

    public function uninstall()
    {
        return parent::uninstall();
    }

    public function hookPaymentOptions($params)
    {
        if (!$this->active) {
            return;
        }

        $payment_options = [
            $this->getOfflinePaymentOption(),
        ];

        return $payment_options;
    }

    public function getOfflinePaymentOption()
    {
        $newOption = new PrestaShop\PrestaShop\Core\Payment\PaymentOption();
        $newOption->setCallToActionText($this->l('Pagar por Débito en Cuenta'))
            ->setAction($this->context->link->getModuleLink($this->name, 'validation', [], true))
            ->setAdditionalInformation($this->context->smarty->fetch('module:custompayment/views/templates/front/payment_infos.tpl'));

        return $newOption;
    }

    public function hookPaymentReturn($params)
    {
        if ($this->active == false) {
            return;
        }

        return $this->fetch('module:custompayment/views/templates/hook/payment_return.tpl');
    }
}
