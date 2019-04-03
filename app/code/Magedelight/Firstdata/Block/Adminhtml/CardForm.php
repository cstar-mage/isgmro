<?php

namespace Magedelight\Firstdata\Block\Adminhtml;

class CardForm extends \Magedelight\Firstdata\Block\Adminhtml\Firstdata
{
    protected $_template = 'cards/form.phtml';

    public function getCcAvailableTypes()
    {
        $types = $this->_getConfig()->getCcTypes();
        $availableTypes = explode(',', $this->getconfig->getCcTypes());
        if ($availableTypes) {
            foreach ($types as $code => $name) {
                if (!in_array($code, $availableTypes)) {
                    unset($types[$code]);
                }
            }
        }

        return $types;
    }
    public function getCustomer()
    {
        $id = $this->getRequest()->getParam('id');

        return $this->_customerFactory->create()->load($id);
    }
    protected function _getConfig()
    {
        return $this->_paymentConfig;
    }

    public function getCcMonths()
    {
        return $this->_getConfig()->getMonths();
    }

    public function getCcYears()
    {
        return $this->_getConfig()->getYears();
    }

    public function hasVerification()
    {
        return $this->getconfig->isCardVerificationEnabled();
    }

    public function setCard($card)
    {
        $this->_card = $card;

        return $this;
    }

    public function getCard()
    {
        if (empty($this->_card)) {
            return;
        }

        return $this->jsonHelper->jsonDecode($this->_card);
    }

    public function getRegionValue($regionValue, $countryId)
    {
        $regionId = null;
        $regionCollection = $this->_regionCollectionFactory->create()
                ->addFieldToFilter('default_name', ['eq' => $regionValue])
                ->addFieldToFilter('country_id', ['eq' => $countryId]);
        if ($regionCollection->count() > 0) {
            $regionId = $regionCollection->getFirstItem()->getId();
        } else {
            $regionId = $regionValue;
        }

        return $regionId;
    }
}
