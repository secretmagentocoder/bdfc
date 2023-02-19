<?php

namespace Ecommage\CustomerUpdatePassword\Plugin\Controller\Account;

class CreatePassword
{
    public function __construct
    (
        \Magento\Framework\App\Request\DataPersistorInterface $dataPersistor
    )
    {
        $this->dataPersistor =$dataPersistor;
    }

    public function beforeExecute(\Magento\Customer\Controller\Account\CreatePassword $subject)
    {
        $token = $subject->getRequest()->getParam('token',null);
        if ($token){
            $this->dataPersistor->set('token_ecommage',$token);
        }
    }

}