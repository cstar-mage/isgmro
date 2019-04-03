<?php

namespace Magedelight\Firstdata\Block;

class Info extends \Magento\Payment\Block\Info
{
    /**
     * Payment config model.
     *
     * @var \Magento\Payment\Model\Config
     */
    protected $_isCheckoutProgressBlockFlag = true;
    protected $firstdataHelper;
    protected $_paymentConfig;
    protected $paymentModel;
    protected $_firstdataConfig;
    protected $_template = 'Magedelight_Firstdata::info.phtml';
    protected $cardpayment;
    protected $storeManager;
    protected $currencyHelper;
    /**
     * @param \Magento\Framework\View\Element\Template\Context $context
     * @param \Magento\Payment\Model\Config                    $paymentConfig
     * @param array                                            $data
     */
    public function __construct(
        \Magento\Framework\View\Element\Template\Context $context,
        \Magento\Payment\Model\Config $paymentConfig,
        \Magedelight\Firstdata\Model\Config $firstdataConfig,
        \Magento\Store\Model\StoreManager $storeManager,
        \Magedelight\Firstdata\Model\Payment\Cards $cardpayment,
        \Magento\Sales\Model\Order\Payment\Transaction $payment,
        \Magedelight\Firstdata\Helper\Data $firstdataHelper,
         \Magento\Framework\Pricing\Helper\Data $currencyHelper,
        array $data = []
    ) {
        parent::__construct($context, $data);
        $this->storeManager = $storeManager;
        $this->_paymentConfig = $paymentConfig;
        $this->_firstdataConfig = $firstdataConfig;
        $this->paymentModel = $payment;
        $this->cardpayment = $cardpayment;
        $this->currencyHelper = $currencyHelper;
        $this->firstdataHelper = $firstdataHelper;
    }

    public function setCheckoutProgressBlock($flag)
    {
        $this->_isCheckoutProgressBlockFlag = $flag;

        return $this;
    }
    /**
     * Retrieve credit card type name.
     *
     * @return string
     */
    public function getCcTypeName()
    {
        $types = $this->_paymentConfig->getCcTypes();
        $ccType = $this->getInfo()->getCcType();
        if (isset($types[$ccType])) {
            return $types[$ccType];
        }

        return empty($ccType) ? __('N/A') : $ccType;
    }

    /**
     * Whether current payment method has credit card expiration info.
     *
     * @return bool
     */
    public function hasCcExpDate()
    {
        return (int) $this->getInfo()->getCcExpMonth() || (int) $this->getInfo()->getCcExpYear();
    }

    /**
     * Retrieve CC expiration month.
     *
     * @return string
     */
    public function getCcExpMonth()
    {
        $month = $this->getInfo()->getCcExpMonth();
        if ($month < 10) {
            $month = '0'.$month;
        }

        return $month;
    }

    /**
     * Retrieve CC expiration date.
     *
     * @return \DateTime
     */
    public function getCcExpDate()
    {
        $date = new \DateTime('now', new \DateTimeZone($this->_localeDate->getConfigTimezone()));
        $date->setDate($this->getInfo()->getCcExpYear(), $this->getInfo()->getCcExpMonth() + 1, 0);

        return $date;
    }

    /**
     * Prepare credit card related payment info.
     *
     * @param \Magento\Framework\Object|array $transport
     *
     * @return \Magento\Framework\Object
     */
    protected function _prepareSpecificInformation($transport = null)
    {
        if (null !== $this->_paymentSpecificInformation) {
            return $this->_paymentSpecificInformation;
        }
        $transport = parent::_prepareSpecificInformation($transport);
        $data = [];
        if ($ccType = $this->getCcTypeName()) {
            $data[(string) __('Credit Card Type')] = $ccType;
        }
        if ($this->getInfo()->getCcLast4()) {
            $data[(string) __('Credit Card Number')] = sprintf('xxxx-%s', $this->getInfo()->getCcLast4());
        }

        if (!$this->getIsSecureMode()) {
            if ($ccSsIssue = $this->getInfo()->getCcSsIssue()) {
                $data[(string) __('Switch/Solo/Maestro Issue Number')] = $ccSsIssue;
            }
            $year = $this->getInfo()->getCcSsStartYear();
            $month = $this->getInfo()->getCcSsStartMonth();
            if ($year && $month) {
                $data[(string) __('Switch/Solo/Maestro Start Date')] = $this->_formatCardDate($year, $month);
            }
        }

        return $transport->setData(array_merge($data, $transport->getData()));
    }

    /**
     * Format year/month on the credit card.
     *
     * @param string $year
     * @param string $month
     *
     * @return string
     */
    protected function _formatCardDate($year, $month)
    {
        return sprintf('%s/%s', sprintf('%02d', $month), $year);
    }

    public function getSpecificInformation()
    {
        return $this->_prepareSpecificInformation()->getData();
    }
    public function getCards()
    {
        $this->cardpayment->setPayment($this->getInfo());
        $cardsData = $this->cardpayment->getCards();
        $cards = array();
//            echo '<pre>';
//            print_r($cardsData);
//            die();
            if (is_array($cardsData)) {
                foreach ($cardsData as $cardInfo) {
                    $data = array();
                    $lastTransactionId = $this->getData('info')->getData('cc_trans_id');
                     //$lastTransactionId=$cardInfo->getLastTransId();
                     $cardTransactionId = $cardInfo->getTransactionId();
                    if ($lastTransactionId == $cardTransactionId) {
                        if ($cardInfo->getProcessedAmount()) {

                            //$amount = Mage::helper('core')->currency($cardInfo->getProcessedAmount(), true, false);
                            $amount = $this->currencyHelper->currency($cardInfo->getProcessedAmount(), true, false);

                            $data['Processed Amount'] = $amount;
                        }

                        if ($cardInfo->getBalanceOnCard() && is_numeric($cardInfo->getBalanceOnCard())) {
                            // $balance = Mage::helper('core')->currency($cardInfo->getBalanceOnCard(), true, false);

                            $balance = $this->currencyHelper->currency($cardInfo->getBalanceOnCard(), true, false);
                            $data['Remaining Balance'] = $balance;
                        }
                        if ($this->firstdataHelper->checkAdmin()) {
                            if ($cardInfo->getApprovalCode() && is_string($cardInfo->getApprovalCode())) {
                                $data['Approval Code'] = $cardInfo->getApprovalCode();
                            }

                            if ($cardInfo->getMethod() && is_numeric($cardInfo->getMethod())) {
                                $data['Method'] = ($cardInfo->getMethod() == 'CC') ? __('Credit Card') : __('eCheck');
                            }

                            if ($cardInfo->getLastTransId() && $cardInfo->getLastTransId()) {
                                $data['Transaction Id'] = str_replace(array('-capture', '-void', '-refund'), '', $cardInfo->getLastTransId());
                            }

                            if ($cardInfo->getAvsResultCode() && is_string($cardInfo->getAvsResultCode())) {
                                $data['AVS Response'] = $this->firstdataHelper->getAvsLabel($cardInfo->getAvsResultCode());
                            }

                            if ($cardInfo->getCVNResultCode() && is_string($cardInfo->getCVNResultCode())) {
                                $data['CVN Response'] = $this->firstdataHelper->getCvnLabel($cardInfo->getCVNResultCode());
                            }

                            if ($cardInfo->getCardCodeResponseCode() && is_string($cardInfo->getreconciliationID())) {
                                $data['CCV Response'] = $cardInfo->getCardCodeResponseCode();
                            }

                            if ($cardInfo->getMerchantReferenceCode() && is_string($cardInfo->getMerchantReferenceCode())) {
                                $data['Merchant Reference Code'] = $cardInfo->getMerchantReferenceCode();
                            }
                        }

                        $this->setCardInfoObject($cardInfo);

                        $cards[] = array_merge($this->getSpecificInformation(), $data);
                        $this->unsCardInfoObject();
                        $this->_paymentSpecificInformation = null;
                    }
                }
            }
        if ($this->getInfo()->getCcType() && $this->_isCheckoutProgressBlockFlag && count($cards) == 0) {
            $cards[] = $this->getSpecificInformation();
        }

        return $cards;
    }
}
