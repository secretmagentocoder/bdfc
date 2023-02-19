<?php

namespace Amasty\Orderattr\Api\Data;

interface CheckoutAttributeInterface extends \Magento\Eav\Api\Data\AttributeInterface
{
    /**
     * Is required value for do special algorithm - is_required set to 0, required_on_front_only set to 1
     */
    const IS_REQUIRED_PROXY_VALUE = 2;

    /**#@+
     * Constants defined for keys of data array
     */
    const IS_VISIBLE_ON_FRONT = 'is_visible_on_front';
    const IS_VISIBLE_ON_BACK = 'is_visible_on_back';
    const MULTISELECT_SIZE = 'multiselect_size';
    const SORTING_ORDER = 'sorting_order';
    const CHECKOUT_STEP = 'checkout_step';
    const SHOW_ON_GRIDS = 'show_on_grids';
    const INCLUDE_IN_PDF = 'include_in_pdf';
    const INCLUDE_IN_HTML_PRINT_ORDER = 'include_in_html_print_order';
    const SAVE_TO_FUTURE_CHECKOUT = 'save_to_future_checkout';
    const APPLY_DEFAULT_VALUE = 'apply_default_value';
    const INCLUDE_IN_EMAIL = 'include_in_email';
    const REQUIRED_ON_FRONT_ONLY = 'required_on_front_only';
    const INPUT_FILTER = 'input_filter';
    /**#@-*/

    /**
     * @return int|null
     */
    public function getIsVisibleOnFront();

    /**
     * @param int|null $isVisibleOnFront
     *
     * @return \Amasty\Orderattr\Api\Data\CheckoutAttributeInterface
     */
    public function setIsVisibleOnFront($isVisibleOnFront);

    /**
     * @return int|null
     */
    public function getIsVisibleOnBack();

    /**
     * @param int|null $isVisibleOnBack
     *
     * @return \Amasty\Orderattr\Api\Data\CheckoutAttributeInterface
     */
    public function setIsVisibleOnBack($isVisibleOnBack);

    /**
     * @return int|null
     */
    public function getMultiselectSize();

    /**
     * @param int|null $multiselectSize
     *
     * @return \Amasty\Orderattr\Api\Data\CheckoutAttributeInterface
     */
    public function setMultiselectSize($multiselectSize);

    /**
     * @return int|null
     */
    public function getSortingOrder();

    /**
     * @param int|null $sortingOrder
     *
     * @return \Amasty\Orderattr\Api\Data\CheckoutAttributeInterface
     */
    public function setSortingOrder($sortingOrder);

    /**
     * @return int|null
     */
    public function getCheckoutStep();

    /**
     * @param int|null $checkoutStep
     *
     * @return \Amasty\Orderattr\Api\Data\CheckoutAttributeInterface
     */
    public function setCheckoutStep($checkoutStep);

    /**
     * @return int|null
     */
    public function isShowOnGrid();

    /**
     * @param int|null $showOnGrids
     *
     * @return \Amasty\Orderattr\Api\Data\CheckoutAttributeInterface
     */
    public function setShowOnGrids($showOnGrids);

    /**
     * @return int|null
     */
    public function getIncludeInPdf();

    /**
     * @param int|null $includeInPdf
     *
     * @return \Amasty\Orderattr\Api\Data\CheckoutAttributeInterface
     */
    public function setIncludeInPdf($includeInPdf);

    /**
     * @return int|null
     */
    public function getIncludeInHtmlPrintOrder();

    /**
     * @param int|null $includeInHtmlPrintOrder
     *
     * @return \Amasty\Orderattr\Api\Data\CheckoutAttributeInterface
     */
    public function setIncludeInHtmlPrintOrder($includeInHtmlPrintOrder);

    /**
     * @return bool|null
     */
    public function isSaveToFutureCheckout();

    /**
     * @param int|bool $saveToFutureCheckout
     *
     * @return \Amasty\Orderattr\Api\Data\CheckoutAttributeInterface
     */
    public function setSaveToFutureCheckout($saveToFutureCheckout);

    /**
     * @return int|null
     */
    public function getApplyDefaultValue();

    /**
     * @param int|null $applyDefaultValue
     *
     * @return \Amasty\Orderattr\Api\Data\CheckoutAttributeInterface
     */
    public function setApplyDefaultValue($applyDefaultValue);

    /**
     * @return bool|null
     */
    public function isIncludeInEmail();

    /**
     * @param bool|null $includeInEmail
     *
     * @return \Amasty\Orderattr\Api\Data\CheckoutAttributeInterface
     */
    public function setIsIncludeInEmail($includeInEmail);

    /**
     * @return int|null
     */
    public function getRequiredOnFrontOnly();

    /**
     * @param int|null $requiredOnFrontOnly
     *
     * @return \Amasty\Orderattr\Api\Data\CheckoutAttributeInterface
     */
    public function setRequiredOnFrontOnly($requiredOnFrontOnly);

    /**
     * @return string|null
     */
    public function getInputFilter();

    /**
     * @param string|null $inputFilter
     *
     * @return \Amasty\Orderattr\Api\Data\CheckoutAttributeInterface
     */
    public function setInputFilter($inputFilter);
}
