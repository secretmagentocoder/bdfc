<?php
namespace Webkuls\SpecialPromotions\Model\Checkout;

use Magento\Customer\Api\Data\CustomerInterface;
use Magento\Framework\App\ObjectManager;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Quote\Api\Data\CartInterface;
use Magento\Quote\Model\Quote;
use Magento\Quote\Model\QuoteIdMaskFactory;
use Psr\Log\LoggerInterface;

class Session extends \Magento\Checkout\Model\Session
{
    public const CHECKOUT_STATE_BEGIN = 'begin';

    /**
     * Quote instace
     *
     * @var Quote
     */
    protected $_quote;

    /**
     * CustomerInterface Data Object
     *
     * @var CustomerInterface|null
     */
    protected $_customer;

    /**
     * Whether load only active quote
     *
     * @var bool
     */
    protected $_loadInactive = false;

    /**
     * Loaded order instance
     *
     * @var \Magento\Sales\Model\Order
     */
    protected $_order;

    /**
     * @var \Magento\Sales\Model\OrderFactory
     */
    protected $_orderFactory;

    /**
     * @var \Magento\Customer\Model\Session
     */
    protected $_customerSession;

    /**
     * @var \Magento\Quote\Api\CartRepositoryInterface
     */
    protected $quoteRepository;

    /**
     * @var \Magento\Framework\HTTP\PhpEnvironment\RemoteAddress
     */
    protected $_remoteAddress;

    /**
     * @var \Magento\Framework\Event\ManagerInterface
     */
    protected $_eventManager;

    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    protected $_storeManager;

    /**
     * @var \Magento\Customer\Api\CustomerRepositoryInterface
     */
    protected $customerRepository;

    /**
     * @var QuoteIdMaskFactory
     */
    protected $quoteIdMaskFactory;

    /**
     * @var bool
     */
    protected $isQuoteMasked;

    /**
     * @var \Magento\Quote\Model\QuoteFactory
     */
    protected $quoteFactory;

    /**
     * A flag to track when the quote is being loaded and attached to the session object.
     *
     * Used in trigger_recollect infinite loop detection.
     *
     * @var bool
     */
    private $isLoading = false;

    /**
     * @var LoggerInterface|null
     */
    private $logger;

    /**
     * @param \Magento\Framework\App\Request\Http                    $request
     * @param \Magento\Framework\Session\SidResolverInterface        $sidResolver
     * @param \Magento\Framework\Session\Config\ConfigInterface      $sessionConfig
     * @param \Magento\Framework\Session\SaveHandlerInterface        $saveHandler
     * @param \Magento\Framework\Session\ValidatorInterface          $validator
     * @param \Magento\Framework\Session\StorageInterface            $storage
     * @param \Magento\Framework\Stdlib\CookieManagerInterface       $cookieManager
     * @param \Magento\Framework\Stdlib\Cookie\CookieMetadataFactory $cookieMetadataFactory
     * @param \Magento\Framework\App\State                           $appState
     * @param \Magento\Sales\Model\OrderFactory                      $orderFactory
     * @param \Magento\Customer\Model\Session                        $customerSession
     * @param \Magento\Framework\Event\ManagerInterface              $eventManager
     * @param \Magento\Store\Model\StoreManagerInterface             $storeManager
     * @param \Magento\Customer\Api\CustomerRepositoryInterface      $customerRepository
     * @param QuoteIdMaskFactory                                     $quoteIdMaskFactory
     * @param \Magento\Quote\Model\QuoteFactory                      $quoteFactory
     * @param LoggerInterface|null                                   $logger
     * @param \Magento\Quote\Api\CartRepositoryInterface             $quoteRepository
     * @param \Magento\Framework\HTTP\PhpEnvironment\RemoteAddress   $remoteAddress
     */
    public function __construct(
        \Magento\Framework\App\Request\Http $request,
        \Magento\Framework\Session\SidResolverInterface $sidResolver,
        \Magento\Framework\Session\Config\ConfigInterface $sessionConfig,
        \Magento\Framework\Session\SaveHandlerInterface $saveHandler,
        \Magento\Framework\Session\ValidatorInterface $validator,
        \Magento\Framework\Session\StorageInterface $storage,
        \Magento\Framework\Stdlib\CookieManagerInterface $cookieManager,
        \Magento\Framework\Stdlib\Cookie\CookieMetadataFactory $cookieMetadataFactory,
        \Magento\Framework\App\State $appState,
        \Magento\Sales\Model\OrderFactory $orderFactory,
        \Magento\Customer\Model\Session $customerSession,
        \Magento\Framework\Event\ManagerInterface $eventManager,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\Customer\Api\CustomerRepositoryInterface $customerRepository,
        QuoteIdMaskFactory $quoteIdMaskFactory,
        \Magento\Quote\Model\QuoteFactory $quoteFactory,
        LoggerInterface $logger = null,
        \Magento\Quote\Api\CartRepositoryInterface $quoteRepository,
        \Magento\Framework\HTTP\PhpEnvironment\RemoteAddress $remoteAddress
    ) {
        $this->_orderFactory = $orderFactory;
        $this->_customerSession = $customerSession;
        $this->quoteRepository = $quoteRepository;
        $this->_remoteAddress = $remoteAddress;
        $this->_eventManager = $eventManager;
        $this->_storeManager = $storeManager;
        $this->customerRepository = $customerRepository;
        $this->quoteIdMaskFactory = $quoteIdMaskFactory;
        $this->quoteFactory = $quoteFactory;
        parent::__construct(
            $request,
            $sidResolver,
            $sessionConfig,
            $saveHandler,
            $validator,
            $storage,
            $cookieManager,
            $cookieMetadataFactory,
            $appState,
            $orderFactory,
            $customerSession,
            $quoteRepository,
            $remoteAddress,
            $eventManager,
            $storeManager,
            $customerRepository,
            $quoteIdMaskFactory,
            $quoteFactory
        );
        $this->logger = $logger ?: ObjectManager::getInstance()
            ->get(LoggerInterface::class);
    }
    
    /**
     * Set customer data.
     *
     * @param CustomerInterface|null $customerData
     * @return \Magento\Checkout\Model\Session
     */
    public function setCustomerData($customerData)
    {
        $this->_customer = $customerData;
        return $this;
    }

    /**
     * Check whether current session has quote
     *
     * @return bool
     * @codeCoverageIgnore
     */
    public function hasQuote()
    {
        return isset($this->_quote);
    }
    
    /**
     * Set quote to be loaded even if inactive
     *
     * @param bool $load
     * @return $this
     */
    public function setLoadInactive($load = true)
    {
        $this->_loadInactive = $load;
        return $this;
    }
    
    /**
     * Get checkout quote instance by current session
     *
     * @return Quote
     * @throws \Magento\Framework\Exception\LocalizedException
     * @throws NoSuchEntityException
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     * @SuppressWarnings(PHPMD.NPathComplexity)
     */
    public function getQuote()
    {
        $this->_eventManager->dispatch('custom_quote_process', ['checkout_session' => $this]);

        if ($this->_quote === null) {
            if ($this->isLoading) {
                throw new \LogicException("Infinite loop detected, review the trace for the looping path");
            }
            $this->isLoading = true;
            $quoteData = $this->quoteFactory->create();
            if ($this->getQuoteId()) {
                try {
                    if ($this->_loadInactive) {
                        $quoteData = $this->quoteRepository->get($this->getQuoteId());
                    } else {
                        $quoteData = $this->quoteRepository->getActive($this->getQuoteId());
                    }

                    $customerId = $this->_customer
                        ? $this->_customer->getId()
                        : $this->_customerSession->getCustomerId();

                    if ($quoteData->getData('customer_id') &&
                    (int)$quoteData->getData('customer_id') !== (int)$customerId) {
                        $quoteData = $this->quoteFactory->create();
                        $this->setQuoteId(null);
                    }
                    
                    if ($quoteData->getQuoteCurrencyCode() != $this->_storeManager
                    ->getStore()->getCurrentCurrencyCode()) {
                        $quoteData->setStore($this->_storeManager->getStore());
                        $this->quoteRepository->save($quoteData->collectTotals());
                        $quoteData = $this->quoteRepository->get($this->getQuoteId());
                    }

                    // if ($quote->getTotalsCollectedFlag() === false) {
                        // $quote->collectTotals();
                        
                        /* Here, $quote->collectTotals(); generates error because of recursive call so,
                        by commenting $quote->collectTotals(); or whole if condition it will not go in
                        infinite and code will work properly.
                        Actually there is no need of $quote->collectTotals(); in this file. */
                    // }
                } catch (NoSuchEntityException $e) {
                    $this->setQuoteId(null);
                }
            }

            if (!$this->getQuoteId()) {
                if ($this->_customerSession->isLoggedIn() || $this->_customer) {
                    $quoteByCustomer = $this->getQuoteByCustomer();
                    if ($quoteByCustomer !== null) {
                        $this->setQuoteId($quoteByCustomer->getId());
                        $quoteData = $quoteByCustomer;
                    }
                } else {
                    $quoteData->setIsCheckoutCart(true);
                    $quoteData->setCustomerIsGuest(1);
                    $this->_eventManager->dispatch('checkout_quote_init', ['quote' => $quoteData]);
                }
            }

            if ($this->_customer) {
                $quoteData->setCustomer($this->_customer);
            } elseif ($this->_customerSession->isLoggedIn()) {
                $quoteData->setCustomer($this->customerRepository->getById($this->_customerSession->getCustomerId()));
            }

            $quoteData->setStore($this->_storeManager->getStore());
            $this->_quote = $quoteData;
            $this->isLoading = false;
        }

        if (!$this->isQuoteMasked() && !$this->_customerSession->isLoggedIn() && $this->getQuoteId()) {
            $quoteId = $this->getQuoteId();
            $quoteIdMask = $this->quoteIdMaskFactory->create()->load($quoteId, 'quote_id');
            if ($quoteIdMask->getMaskedId() === null) {
                $quoteIdMask->setQuoteId($quoteId)->save();
            }
            $this->setIsQuoteMasked(true);
        }

        $remoteAddressData = $this->_remoteAddress->getRemoteAddress();
        if ($remoteAddressData) {
            $this->_quote->setRemoteIp($remoteAddressData);
            $xForwardIp = $this->request->getServer('HTTP_X_FORWARDED_FOR');
            $this->_quote->setXForwardedFor($xForwardIp);
        }

        return $this->_quote;
    }
    
    /**
     * Return the quote's key
     *
     * @return string
     */
    protected function _getQuoteIdKey()
    {
        return 'quote_id_' . $this->_storeManager->getStore()->getWebsiteId();
    }
    
    /**
     * Set the current session's quote id
     *
     * @param int $quotId
     * @return void
     */
    public function setQuoteId($quotId)
    {
        $this->storage->setData($this->_getQuoteIdKey(), $quotId);
    }
    
    /**
     * Return the current quote's ID
     *
     * @return int
     */
    public function getQuoteId()
    {
        return $this->getData($this->_getQuoteIdKey());
    }

    /**
     * Load data for customer quote and merge with current quote
     *
     * @return $this
     */
    public function loadCustomerQuote()
    {
        if (!$this->_customerSession->getCustomerId()) {
            return $this;
        }

        $this->_eventManager->dispatch('load_customer_quote_before', ['checkout_session' => $this]);

        try {
            $customerQuoteData = $this->quoteRepository->getForCustomer($this->_customerSession->getCustomerId());
        } catch (NoSuchEntityException $e) {
            $customerQuoteData = $this->quoteFactory->create();
        }
        $customerQuoteData->setStoreId($this->_storeManager->getStore()->getId());

        if ($customerQuoteData->getId() && $this->getQuoteId() != $customerQuoteData->getId()) {
            if ($this->getQuoteId()) {
                $quote = $this->getQuote();
                $quote->setCustomerIsGuest(0);
                $this->quoteRepository->save(
                    $customerQuoteData->merge($quote)->collectTotals()
                );
                $newQuote = $this->quoteRepository->get($customerQuoteData->getId());
                $this->quoteRepository->save(
                    $newQuote->collectTotals()
                );
                $customerQuoteData = $newQuote;
            }

            $this->setQuoteId($customerQuoteData->getId());

            if ($this->_quote) {
                $this->quoteRepository->delete($this->_quote);
            }
            $this->_quote = $customerQuoteData;
        } else {
            $this->getQuote()->getBillingAddress();
            $this->getQuote()->getShippingAddress();
            $this->getQuote()->setCustomer($this->_customerSession->getCustomerDataObject())
                ->setCustomerIsGuest(0)
                ->setTotalsCollectedFlag(false)
                ->collectTotals();
            $this->quoteRepository->save($this->getQuote());
        }
        return $this;
    }
    
    /**
     * Associate data to a specified step of the checkout process
     *
     * @param string $stepData
     * @param array|string $data
     * @param bool|string|null $value
     * @return $this
     */
    public function setStepData($stepData, $data, $value = null)
    {
        $steps = $this->getSteps();
        if ($value === null) {
            if (is_array($data)) {
                $steps[$stepData] = $data;
            }
        } else {
            if (!isset($steps[$stepData])) {
                $steps[$stepData] = [];
            }
            if (is_string($data)) {
                $steps[$stepData][$data] = $value;
            }
        }
        $this->setSteps($steps);

        return $this;
    }
    
    /**
     * Return the data associated to a specified step
     *
     * @param string|null $stepData
     * @param string|null $data
     * @return array|string|bool
     */
    public function getStepData($stepData = null, $data = null)
    {
        $steps = $this->getSteps();
        if ($stepData === null) {
            return $steps;
        }
        if (!isset($steps[$stepData])) {
            return false;
        }
        if ($data === null) {
            return $steps[$stepData];
        }
        if (!is_string($data) || !isset($steps[$stepData][$data])) {
            return false;
        }
        return $steps[$stepData][$data];
    }

    /**
     * Clear misc checkout parameters
     *
     * @return void
     */
    public function clearHelperData()
    {
        $this->setRedirectUrl(null)->setLastOrderId(null)->setLastRealOrderId(null)->setAdditionalMessages(null);
    }
    
    /**
     * Destroy/end a session and unset all data associated with it
     *
     * @return $this
     */
    public function clearQuote()
    {
        $this->_eventManager->dispatch('checkout_quote_destroy', ['quote' => $this->getQuote()]);
        $this->_quote = null;
        $this->setQuoteId(null);
        $this->setLastSuccessQuoteId(null);
        return $this;
    }
    
    /**
     * Unset all session data and quote
     *
     * @return $this
     */
    public function clearStorage()
    {
        parent::clearStorage();
        $this->_quote = null;
        return $this;
    }
    
    /**
     * Revert the state of the checkout to the beginning
     *
     * @return $this
     */
    public function resetCheckout()
    {
        $this->setCheckoutState(self::CHECKOUT_STATE_BEGIN);
        return $this;
    }
    
    /**
     * Replace the quote in the session with a specified object
     *
     * @param Quote $quoteData
     * @return $this
     */
    public function replaceQuote($quoteData)
    {
        $this->_quote = $quoteData;
        $this->setQuoteId($quoteData->getId());
        return $this;
    }
    
    /**
     * Get order instance based on last order ID
     *
     * @return \Magento\Sales\Model\Order
     */
    public function getLastRealOrder()
    {
        $orderId = $this->getLastRealOrderId();
        if ($this->_order !== null && $orderId == $this->_order->getIncrementId()) {
            return $this->_order;
        }
        $this->_order = $this->_orderFactory->create();
        if ($orderId) {
            $this->_order->loadByIncrementId($orderId);
        }
        return $this->_order;
    }
    
    /**
     * Restore last active quote
     *
     * @return bool True if quote restored successfully, false otherwise
     */
    public function restoreQuote()
    {
        $order = $this->getLastRealOrder();
        if ($order->getId()) {
            try {
                $quoteData = $this->quoteRepository->get($order->getQuoteId());
                $quoteData->setIsActive(1)->setReservedOrderId(null);
                $this->quoteRepository->save($quoteData);
                $this->replaceQuote($quoteData)->unsLastRealOrderId();
                $this->_eventManager->dispatch('restore_quote', ['order' => $order, 'quote' => $quoteData]);
                return true;
            } catch (NoSuchEntityException $e) {
                $this->logger->critical($e);
            }
        }

        return false;
    }
    
    /**
     * Flag whether or not the quote uses a masked quote id
     *
     * @param bool $isQuoteMasked
     * @return void
     */
    protected function setIsQuoteMasked($isQuoteMasked)
    {
        $this->isQuoteMasked = $isQuoteMasked;
    }
    
    /**
     * Return if the quote has a masked quote id
     *
     * @return bool|null
     */
    protected function isQuoteMasked()
    {
        return $this->isQuoteMasked;
    }
    
    /**
     * Returns quote for customer if there is any
     */
    private function getQuoteByCustomer(): ?CartInterface
    {
        $customerId = $this->_customer
            ? $this->_customer->getId()
            : $this->_customerSession->getCustomerId();

        try {
            $quote = $this->quoteRepository->getActiveForCustomer($customerId);
        } catch (NoSuchEntityException $e) {
            $quote = null;
        }

        return $quote;
    }
}
