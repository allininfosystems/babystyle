<?php
namespace Magenest\Moneris\Observer;

use Magento\Framework\Event\Observer;
use Magento\Payment\Observer\AbstractDataAssignObserver;
use Magento\Quote\Api\Data\PaymentInterface;
use Magento\Sales\Api\Data\OrderPaymentInterface;
use Magento\Framework\Encryption\EncryptorInterface;

/**
 * Class DataAssignObserver
 */
class DataAssignObserver extends AbstractDataAssignObserver
{
    const CC_NUMBER = 'cc_number';
    const CC_CID = 'cc_cid';
    const CC_CID_ENC = 'cc_cid_enc';

    /**
     * @var array
     */
    protected $additionalInformationList = [
        self::CC_NUMBER,
        self::CC_CID,
        OrderPaymentInterface::CC_TYPE,
        OrderPaymentInterface::CC_EXP_MONTH,
        OrderPaymentInterface::CC_EXP_YEAR,
    ];

    /**
     * @param Observer $observer
     * @return void
     */
    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        $data = $this->readDataArgument($observer);

        $additionalData = $data->getData(PaymentInterface::KEY_ADDITIONAL_DATA);
        if (!is_array($additionalData)) {
            return;
        }
	
	$paymentInfo = $this->readMethodArgument($observer)->getInfoInstance();
	
        foreach ($this->additionalInformationList as $additionalInformationKey) {
            $value = isset($additionalData[$additionalInformationKey])
                ? $additionalData[$additionalInformationKey]
                : null;

            if ($value === null) {
                continue;
            }

            if ($additionalInformationKey == self::CC_NUMBER) {
                $paymentInfo->setAdditionalInformation(
                    OrderPaymentInterface::CC_NUMBER_ENC,
                    $paymentInfo->encrypt($value)
                );

                continue;
            } elseif ($additionalInformationKey == self::CC_CID) {
                $paymentInfo->setAdditionalInformation(
                    self::CC_CID_ENC,
                    $paymentInfo->encrypt($value)
                );

                continue;
            }
            $paymentInfo->setAdditionalInformation(
                $additionalInformationKey,
                $value
            );
        }
    }
}
