<?php

namespace Amasty\Orderattr\Model\Entity\Adapter\Quote\Plugin\Api;

class QuoteRepository
{
    /**
     * @var \Amasty\Orderattr\Model\Entity\Adapter\Quote\Adapter
     */
    private $adapter;

    /**
     * @var \Magento\Quote\Api\Data\CartInterface
     */
    protected $currentQuote;

    public function __construct(\Amasty\Orderattr\Model\Entity\Adapter\Quote\Adapter $adapter)
    {
        $this->adapter = $adapter;
    }

    /**
     * @param \Magento\Quote\Api\CartRepositoryInterface $subject
     * @param \Magento\Quote\Api\Data\CartInterface      $quote
     *
     * @return \Magento\Quote\Api\Data\CartInterface
     */
    public function afterGet(\Magento\Quote\Api\CartRepositoryInterface $subject, $quote)
    {
        $this->adapter->addExtensionAttributesToQuote($quote);

        return $quote;
    }

    /**
     * @param \Magento\Quote\Api\CartRepositoryInterface $subject
     * @param \Magento\Quote\Api\Data\CartInterface      $quote
     *
     * @return \Magento\Quote\Api\Data\CartInterface
     */
    public function afterGetForCustomer(\Magento\Quote\Api\CartRepositoryInterface $subject, $quote)
    {
        $this->adapter->addExtensionAttributesToQuote($quote);

        return $quote;
    }

    /**
     * @param \Magento\Quote\Api\CartRepositoryInterface $subject
     * @param \Magento\Quote\Api\Data\CartInterface      $quote
     */
    public function beforeSave(\Magento\Quote\Api\CartRepositoryInterface $subject, $quote)
    {
        $this->currentQuote = $quote;
    }

    /**
     * @param \Magento\Quote\Api\CartRepositoryInterface $subject
     */
    public function afterSave(\Magento\Quote\Api\CartRepositoryInterface $subject)
    {
        $this->adapter->saveQuoteValues($this->currentQuote);
    }
}
