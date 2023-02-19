<?php

namespace Amasty\Orderattr\Model;

use Amasty\Orderattr\Model\Config\Source\TimeFormat;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\App\ProductMetadataInterface;
use Magento\Store\Model\ScopeInterface;
use Magento\Framework\Stdlib\DateTime\TimezoneInterface;
use Magento\Newsletter\Model\Subscriber;

/**
 * Scope config Provider model
 */
class ConfigProvider
{
    /**
     * xpath prefix of module
     */
    const PATH_PREFIX = 'amorderattr';

    /**#@+
     * Constants defined for xpath of system configuration
     */
    const XPATH_ENABLED = 'general/enabled';

    const SHOW_IN_CHECKOUT_PROGRESS = 'checkout/progress';

    const HIDE_EMPTY_IN_CHECKOUT_PROGRESS = 'checkout/hide_empty';

    const JS_DATE_FORMAT = 'checkout/format';

    const SEND_ON_SHIPPING = 'checkout/send_on_shipping_step';

    const TIME_FORMAT = 'checkout/time_format';

    const SHOW_INVOICE_GRID = 'invoices_shipments/invoice_grid';

    const SHOW_SHIPMENT_GRID = 'invoices_shipments/shipment_grid';

    const INCLUDE_TO_INVOICE_PDF = 'pdf/invoice';

    const INCLUDE_TO_SHIPMENT_PDF = 'pdf/shipment';

    const INCLUDE_IN_EMAIL = 'checkout/email';
    /**#@-*/

    /**
     * @var ScopeConfigInterface
     */
    private $scopeConfig;

    /**
     * @var TimezoneInterface
     */
    private $localeDate;

    /**
     * @var ProductMetadataInterface
     */
    private $productMetadata;

    public function __construct(
        ScopeConfigInterface $scopeConfig,
        TimezoneInterface $localeDate,
        ProductMetadataInterface $productMetadata
    ) {
        $this->scopeConfig = $scopeConfig;
        $this->localeDate = $localeDate;
        $this->productMetadata = $productMetadata;
    }

    /**
     * An alias for scope config with default scope type SCOPE_STORE
     *
     * @param string $key
     * @param string|null $scopeCode
     * @param string $scopeType
     *
     * @return string|null
     */
    public function getValue($key, $scopeCode = null, $scopeType = ScopeInterface::SCOPE_STORE)
    {
        return $this->scopeConfig->getValue(self::PATH_PREFIX . '/' . $key, $scopeType, $scopeCode);
    }

    /**
     * @return bool
     */
    public function isEnabled()
    {
        return (bool)$this->getValue(self::XPATH_ENABLED);
    }

    /**
     * @param string $carrierCode
     *
     * @return string
     */
    public function getCarrierTitle($carrierCode)
    {
        $configPath = sprintf('carriers/%s/title', $carrierCode);
        return $this->scopeConfig->getValue($configPath);
    }

    /**
     * @param string|null $scopeCode
     *
     * @return bool
     */
    public function showInCheckoutProgress($scopeCode = null)
    {
        return (bool)$this->getValue(self::SHOW_IN_CHECKOUT_PROGRESS, $scopeCode);
    }

    /**
     * @param string|null $scopeCode
     *
     * @return bool
     */
    public function isHideEmptyInCheckoutProgress($scopeCode = null)
    {
        return true;
    }

    /**
     * @param string $format
     *
     * @return string
     */
    public function convertDateFormat($format)
    {
        $replaceArray = ['Y', ''];

        if (version_compare($this->productMetadata->getVersion(), '2.2', '<=')) {
            $replaceArray = ['y', ''];
        }

        return preg_replace(['/y{2,}/s', '/z{2,}/s'], $replaceArray, $format);
    }

    /**
     * @param string|null $scopeCode
     *
     * @return string
     */
    public function getDateFormatJs($scopeCode = null)
    {
        $format = $this->getDateFormat($scopeCode);
        return $this->convertDateFormat($format);
    }

    /**
     * @param string|null $scopeCode
     *
     * @return string
     */
    public function getDateFormat($scopeCode = null)
    {
        return $this->getValue(self::JS_DATE_FORMAT, $scopeCode);
    }

    /**
     * @return string
     */
    public function getTimeFormatJs()
    {
        if ($this->getValue(self::TIME_FORMAT) == TimeFormat::HOUR_24) {
            return 'H:mm';
        } else {
            return 'h:mm a';
        }
    }

    /**
     * @return string
     */
    public function getTimeFormat()
    {
        if ($this->getValue(self::TIME_FORMAT) == TimeFormat::HOUR_24) {
            return 'H:i:s';
        } else {
            return 'h:i:s a';
        }
    }

    /**
     * @param string|null $scopeCode
     *
     * @return bool
     */
    public function isIncludeToShipmentPdf($scopeCode = null)
    {
        return (bool)$this->getValue(self::INCLUDE_TO_SHIPMENT_PDF, $scopeCode);
    }

    /**
     * @param string|null $scopeCode
     *
     * @return bool
     */
    public function isIncludeToInvoicePdf($scopeCode = null)
    {
        return (bool)$this->getValue(self::INCLUDE_TO_INVOICE_PDF, $scopeCode);
    }

    /**
     * @param string|null $scopeCode
     *
     * @return bool
     */
    public function isShowInvoiceGrid($scopeCode = null)
    {
        return (bool)$this->getValue(self::SHOW_INVOICE_GRID, $scopeCode);
    }

    /**
     * @param string|null $scopeCode
     *
     * @return bool
     */
    public function isShowShipmentGrid($scopeCode = null)
    {
        return (bool)$this->getValue(self::SHOW_SHIPMENT_GRID, $scopeCode);
    }

    /**
     * @param string|null $scopeCode
     *
     * @return bool
     */
    public function isIncludeInEmail($scopeCode = null)
    {
        return (bool)$this->getValue(self::INCLUDE_IN_EMAIL, $scopeCode);
    }

    public function isSendOnShipping($scopeCode = null)
    {
        return (bool)$this->getValue(self::SEND_ON_SHIPPING, $scopeCode);
    }

    /**
     * @return bool
     */
    public function allowGuestSubscribe()
    {
        return (bool)$this->scopeConfig->getValue(
            Subscriber::XML_PATH_ALLOW_GUEST_SUBSCRIBE_FLAG,
            ScopeInterface::SCOPE_STORE
        );
    }
}
