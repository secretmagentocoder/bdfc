<?php

namespace Ecommage\NavSyncPromotion\Helper;

use Ecommage\NavSyncPromotion\Model\Api\Nav;
use Exception;
use Magento\Framework\App\CacheInterface;
use Magento\Framework\App\Helper\AbstractHelper;
use Magento\Framework\App\Helper\Context;
use Magento\Framework\App\ResourceConnection;
use Magento\Framework\App\ObjectManager;
use Magento\SalesRule\Model\Rule;
use Magento\SalesRule\Model\RuleFactory;
use Magento\Store\Model\ScopeInterface;
use Magento\SalesRule\Model\Rule\Condition\Product\Found;
use Magento\SalesRule\Model\Rule\Condition\Product;
use Magento\SalesRule\Model\Rule\Condition\Combine;
use Magento\SalesRule\Model\Rule\Condition\Product\Combine as ProductCombine;
use Magento\Framework\App\Area;
use Magento\Framework\App\State;

/**
 * Class Data
 *
 * @package Ecommage\NavSyncPromotion\Helper
 */
class Data extends AbstractHelper
{
    const CACHE_TAG = 'NAV_SYNC_OFFER';
    const NAV_INFO = 'nav/system/';
    /**
     * @var ResourceConnection
     */
    protected $connection;
    /**
     * @var RuleFactory
     */
    protected $ruleFactory;
    /**
     * @var CacheInterface
     */
    private $cache;
    /**
     * @var Nav
     */
    protected $navApi;
    /**
     * @var State
     */
    protected $state;
    /**
     * @var array
     */
    private $priceGroups = [];
    /**
     * @var null
     */
    protected $output = null;
    /**
     * @var int
     */
    protected $process = 100;
    /**
     * @var int
     */
    static $count = 0;

    /**
     * Data constructor.
     *
     * @param ResourceConnection $connection
     * @param RuleFactory        $ruleFactory
     * @param CacheInterface     $cache
     * @param State              $state
     * @param Nav                $navApi
     * @param Context            $context
     */
    public function __construct(
        ResourceConnection $connection,
        RuleFactory $ruleFactory,
        CacheInterface $cache,
        State $state,
        Nav $navApi,
        Context $context
    ) {
        $this->state       = $state;
        $this->cache       = $cache;
        $this->navApi      = $navApi;
        $this->ruleFactory = $ruleFactory;
        $this->connection  = $connection->getConnection();
        parent::__construct($context);
    }

    /**
     * @param null $output
     *
     * @return $this
     */
    public function setOutput($output = null)
    {
        $this->output = $output;
        return $this;
    }

    /**
     * @param       $msg
     * @param false $isError
     *
     * @return $this
     */
    public function showMsg($msg, $isError = false)
    {
        if ($this->output) {
            $tag = 'info';
            if ($isError === true) {
                $tag = 'error';
            }

            $tt      = sprintf("<%s>%s. </%s>", $tag, static::$count, $tag);
            $message = $tt . sprintf("<%s>%s</%s>", $tag, $msg, $tag);
            $this->output->writeln($message);
        }
        $this->log($msg);
        return $this;
    }

    /**
     * @param int $skip
     *
     * @return $this
     */
    public function navSyncOffer($skip = 0, $limit = 0)
    {
        $this->updateArea();
        $cacheTag = $this->getCacheTag();
        $total = (int)$this->cache->load($cacheTag);
        if ($limit && $total) {
            $skip = $total;
        }

        $items = $this->getWebOfferHeader($skip);
        if (!empty($items['value'])) {
            $rows = $items['value'];
            $this->cleanOffer($rows);
            foreach ($rows as $item) {
                static::$count++;
                if ($limit && static::$count > $limit) {
                    $total += $limit;
                    $this->cache->save($total, $cacheTag);
                    return $this;
                }

                if (empty($item['No']) || $item['Priority'] == 0) {
                    $strData = !empty($item) ? json_encode($item) : '';
                    $msg     = sprintf('No or Priority unspecified origin data: %s', $strData);
                    $this->showMsg($msg, true);
                    continue;
                }

                $rule = $this->createOffer($item);
                $msg  = sprintf('Rule created failed Offer No: %s', $item['No']);
                if ($rule) {
                    $this->showMsg(sprintf('Rule is created with rule ID : %d', $rule->getId()));
                }

                $this->showMsg($msg);

            }

            if (count($rows) >= $this->process) {
                $newOffset = $skip + $this->process;
                $this->navSyncOffer($newOffset, $limit);
            }
        } elseif ($limit) {
            $this->cache->save(0, $cacheTag);
        }

        return $this;
    }

    /**
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function updateArea()
    {
        try {
            if (!$this->state->getAreaCode()) {
                $this->state->setAreaCode(Area::AREA_ADMINHTML);
            }
        } catch (\Exception $exception) {
            $this->state->setAreaCode(Area::AREA_ADMINHTML);
        }
        return $this;
    }

    /**
     * @param $rowOffers
     *
     * @return $this
     */
    public function cleanOffer($rowOffers)
    {
        $offerNos = array_column($rowOffers, 'No');
        $ruleCollection = $this->ruleFactory->create()->getCollection();
        $ruleCollection->addFieldToFilter('is_active', 0); //filter rule disable
        $ruleCollection->addFieldToFilter('offer_no', ['in' => $offerNos]); //filter rule by offer no
        if ($ruleCollection->count()) {
            foreach ($ruleCollection as $model) {
                $model->delete();
            }
        }
        return $this;
    }

    /**
     * @param $offer
     *
     * @return false|Rule
     */
    public function createOffer($offer)
    {
        if (empty($offer) || empty($offer['No'])) {
            return false;
        }

        $offerNo = $offer['No'];
        $offerLines = $this->getWebOfferLine($offerNo);
        if (empty($offerLines['value'])) {
            $this->showMsg(sprintf('WebOfferLine not found corresponding of #%s', $offerNo), true);
            return false;
        }

        if ($this->getStatus($offer) != 1) {
            $this->showMsg(sprintf('Web Offer #%s disabled', $offerNo), true);
            return false;
        }

        $data = [
            'qty'                => '',
            'lineBsku'           => '', //$lineBsku
            'offerType'          => '', //$offerType
            'discountPercentage' => 0, //$discount_percentage

            'skus'       => [],
            'steps'      => [],
            'linesASkus' => [], //$lineAskus_array
            'linesBSkus' => [],

            'item_sku'          => '', //$sku
            'product_group_sku' => '', //$productSku
            'division_sku'      => '', //$divisionSku
            'brand_sku'         => '', //$brandSku
            'sub_brand_sku'     => '', //$subBrandSku
            'brand_owner_sku'   => '', //$brandOwnerSku
            'item_category_sku' => '', //$categorySku

            'item_skus'          => [], //$skus_array
            'product_group_skus' => [], //$productSkusArray
            'division_skus'      => [], //$divisionSkusArray
            'brand_skus'         => [], //$brandSkusArray
            'sub_brand_skus'     => [], //$subBrandSkusArray
            'brand_owner_skus'   => [], //$brandOwnerSkusArray
            'item_category_skus' => [], //$categorySkusArray

            'offerTypeLine'       => [],
            'discountPercentages' => [],

        ];
        try {
            $discountType = $offer['Discount_Type'];
            foreach ($offerLines['value'] as $line) {
                $lineNo     = $line['No'];
                $discAmount = $line['Deal_Price_Disc_Perc'];
                $lineGroup  = $line['Line_Group'];
                if ($discountType == 'Line spec.') {
                    if (preg_match('~[0-9]+~', $lineGroup)) {
                        $quantity                      = $line['NoOfItem'];
                        $data['discountPercentages'][] = $discAmount;
                        $data['offerTypeLine'][]       = $this->getOfferTypeLine($line);
                        $data['steps'][]               = $quantity;
                        /**
                         * No: Item Category, Item, Brand, Sub-Brand, Brand Owner, Product Group, Division
                         * key: item_category, item, brand, sub_brand, brand_owner, product_group, division
                         */
                        $key  = strtolower(str_replace([' ', '-'], ['_', '_'], $line['Type'])) . '_sku';
                        $keys = $key . 's';
                        if (!isset($data[$key])) {
                            $data[$key] = '';
                        }

                        $data[$key]    .= $lineNo . ', ';
                        $data[$keys][] = $lineNo;
                    } else {
                        $data['item_sku']     .= $lineNo . ', ';
                        $data['item_skus'][]  = $lineNo;
                        $data['linesASkus'][] = $lineNo;
                        if ($lineGroup == 'B') {
                            $data['offerType']          = $this->getOfferType($line);
                            $data['discountPercentage'] = $discAmount;
                            $data['qty']                = $lineGroup;
                            $data['lineBsku']           = $lineNo;
                        }
                    }
                } else {
                    $data['item_sku']    .= $lineNo . ', ';
                    $data['item_skus'][] = $lineNo;
                }
            }

            $skus      = rtrim($data['item_sku'], ', ');
            $step_qty  = (int)$offer['No_of_Lines_to_Trigger'];
            $salesRule = $this->ruleFactory->create();
            $this->addDefaultData($salesRule, $offer, $data);
            $objectManager = ObjectManager::getInstance();
            if ($discountType == 'Line spec.') {
                if ($data['qty'] == 'B') {
                    $salesRule->setProductIds(null);
                    $salesRule->setDiscountQty();
                    $salesRule->setDiscountStep($step_qty);
                    $salesRule->setPromoSkus($data['lineBsku']);
                    $itemFound = $objectManager->create(Found::class)
                                               ->setType(Found::class)
                                               ->setValue(1) // 1 == FOUND
                                               ->setAggregator('all'); // match ALL conditions
                    $salesRule->getConditions()->addCondition($itemFound);
                    if (count($data['linesASkus']) > 1) {
                        $actions = $objectManager->create(Product::class)
                                                 ->setType(Product::class)
                                                 ->setData('attribute', 'sku')
                                                 ->setData('operator', '()')
                                                 ->setValue($skus);
                        $salesRule->getActions()->addCondition($actions);
                        $conditions = $objectManager->create(Product::class)
                                                    ->setType(Product::class)
                                                    ->setData('attribute', 'sku')
                                                    ->setData('operator', '()')
                                                    ->setValue($skus);
                        $itemFound->addCondition($conditions);
                        if ($step_qty > 1) {
                            $qtyCondition = $objectManager->create(Product::class)
                                                          ->setType(Product::class)
                                                          ->setData('attribute', 'quote_item_qty')
                                                          ->setData('operator', '>=')
                                                          ->setValue($step_qty);
                            $itemFound->addCondition($qtyCondition);
                        }
                    } elseif (count($data['linesASkus']) == 1) {
                        $actions = $objectManager->create(Product::class)
                                                 ->setType(Product::class)
                                                 ->setData('attribute', 'sku')
                                                 ->setData('operator', '==')
                                                 ->setValue($skus);
                        $salesRule->getActions()->addCondition($actions);
                        $conditions = $objectManager->create(Product::class)
                                                    ->setType(Product::class)
                                                    ->setData('attribute', 'sku')
                                                    ->setData('operator', '==')
                                                    ->setValue($skus);
                        $itemFound->addCondition($conditions);
                        if ($step_qty > 1) {
                            $qtyCondition = $objectManager->create(Product::class)
                                                          ->setType(Product::class)
                                                          ->setData('attribute', 'quote_item_qty')
                                                          ->setData('operator', '>=')
                                                          ->setValue($step_qty);
                            $itemFound->addCondition($qtyCondition);
                        }
                    }
                    return $this->createSalesRule($salesRule);
                } else {
                    if ($data['steps']) {
                        $stepQty = $data['steps'][0];
                        if ($data['offerTypeLine']) {
                            $data['offerType'] = $data['offerTypeLine'][0];
                        }

                        if ($data['discountPercentages']) {
                            $data['discountPercentage'] = $data['discountPercentages'][0];
                        }

                        if ($stepQty) {
                            $quantity = $stepQty;
                            $salesRule->setpromoSkus('');
                            $salesRule->setDiscountStep($quantity);
                            $salesRule->setSimpleAction($data['offerType']);
                            $salesRule->setDiscountAmount($data['discountPercentage']);
                            $allConditionData = $objectManager->create(Combine::class)
                                                              ->setType(Combine::class)
                                                              ->setValue(1) // 1 == FOUND
                                                              ->setAggregator('any'); // match ALL conditions
                            $salesRule->getConditions()->addCondition($allConditionData);
                            $allActionData = $objectManager->create(ProductCombine::class)
                                                           ->setType(ProductCombine::class)
                                                           ->setValue(1) // 1 == FOUND
                                                           ->setAggregator('any'); // match ALL conditions
                            $salesRule->getActions()->addCondition($allActionData);
                            $attributes = [
                                'item_category_skus' => 'item_category',
                                'item_skus'          => 'sku',
                                'brand_owner_skus'   => 'brand_owner',
                                'brand_skus'         => 'brand_code',
                                'sub_brand_skus'     => 'sub_brand_code',
                                'product_group_skus' => 'product_group',
                                'division_skus'      => 'division',
                            ];

                            foreach ($attributes as $keys => $attribute) {
                                $countSkus = count($data[$keys]);
                                if ($countSkus >= 1) {
                                    $key       = rtrim($keys, 's');
                                    $dataSku   = rtrim($data[$key], ', ');
                                    $itemFound = $objectManager->create(Found::class)
                                                               ->setType(Found::class)
                                                               ->setValue(1) // 1 == FOUND
                                                               ->setAggregator('all'); // match ALL conditions
                                    $allConditionData->addCondition($itemFound);
                                    if ($countSkus > 1) {
                                        $actions = $objectManager->create(Product::class)
                                                                 ->setType(Product::class)
                                                                 ->setData('attribute', $attribute)
                                                                 ->setData('operator', '()')
                                                                 ->setValue($dataSku);
                                        $allActionData->addCondition($actions);
                                        $conditions = $objectManager->create(Product::class)
                                                                    ->setType(Product::class)
                                                                    ->setData('attribute', $attribute)
                                                                    ->setData('operator', '()')
                                                                    ->setValue($dataSku);
                                        $itemFound->addCondition($conditions);
                                        if ($quantity > 1) {
                                            $qtyCondition = $objectManager->create(Product::class)
                                                                          ->setType(Product::class)
                                                                          ->setData('attribute', 'quote_item_qty')
                                                                          ->setData('operator', '>=')
                                                                          ->setValue($quantity);
                                            $itemFound->addCondition($qtyCondition);
                                        }
                                    } elseif ($countSkus == 1) {
                                        $actions = $objectManager->create(Product::class)
                                                                 ->setType(Product::class)
                                                                 ->setData('attribute', $attribute)
                                                                 ->setData('operator', '==')
                                                                 ->setValue($dataSku);
                                        $allActionData->addCondition($actions);
                                        $conditions = $objectManager->create(Product::class)
                                                                    ->setType(Product::class)
                                                                    ->setData('attribute', $attribute)
                                                                    ->setData('operator', '==')
                                                                    ->setValue($dataSku);
                                        $itemFound->addCondition($conditions);
                                        if ($quantity > 1) {
                                            $qtyCondition = $objectManager->create(Product::class)
                                                                          ->setType(Product::class)
                                                                          ->setData('attribute', 'quote_item_qty')
                                                                          ->setData('operator', '>=')
                                                                          ->setValue($quantity);
                                            $itemFound->addCondition($qtyCondition);
                                        }
                                    }
                                }
                            }
                            return $this->createSalesRule($salesRule);
                        }
                    }
                }
            } elseif ($discountType == 'Least Expensive') {
                // $offerType = 'Percent Discount: Each 5 items with 15% off';
                $data['offerType']          = 'group_n_percent_discount';
                $data['discountPercentage'] = ((int)$offer['No_of_Line_Groups'] / (int)$offer['No_of_Lines_to_Trigger']) * 100;
            } elseif ($discountType == 'Discount %') {
                // $offerType = 'Percent Discount: Each 5 items with 15% off';
                $data['offerType']          = 'group_n_percent_discount';
                $data['discountPercentage'] = $offer['Discount_Perc_Value'];
            } elseif ($discountType == 'Deal Price') {
                $data['offerType']          = 'group_nth';
                $data['discountPercentage'] = $offer['Deal_Price_Value'];
            }

            $salesRule->setData([]); //reset default data
            $this->addDefaultData($salesRule, $offer, $data);
            $salesRule->setProductIds(null);
            $salesRule->setDiscountQty();
            $salesRule->setDiscountStep($step_qty);
            $salesRule->setpromoSkus('');
            $itemFound = $objectManager->create(Found::class)
                                       ->setType(Found::class)
                                       ->setValue(1) // 1 == FOUND
                                       ->setAggregator('all'); // match ALL conditions
            $salesRule->getConditions()->addCondition($itemFound);
            $countItemSkus = count($data['item_skus']);
            if ($countItemSkus > 1) {
                $actions = $objectManager->create(Product::class)
                                         ->setType(Product::class)
                                         ->setData('attribute', 'sku')
                                         ->setData('operator', '()')
                                         ->setValue($skus);
                $salesRule->getActions()->addCondition($actions);
                $conditions = $objectManager->create(Product::class)
                                            ->setType(Product::class)
                                            ->setData('attribute', 'sku')
                                            ->setData('operator', '()')
                                            ->setValue($skus);
                $itemFound->addCondition($conditions);
                if ($step_qty > 1) {
                    $qty_condition = $objectManager->create(Product::class)
                                                   ->setType(Product::class)
                                                   ->setData('attribute', 'quote_item_qty')
                                                   ->setData('operator', '>=')
                                                   ->setValue($step_qty);
                    $itemFound->addCondition($qty_condition);
                }
            } elseif ($countItemSkus == 1) {
                $actions = $objectManager->create(Product::class)
                                         ->setType(Product::class)
                                         ->setData('attribute', 'sku')
                                         ->setData('operator', '==')
                                         ->setValue($skus);
                $salesRule->getActions()->addCondition($actions);
                $conditions = $objectManager->create(Product::class)
                                            ->setType(Product::class)
                                            ->setData('attribute', 'sku')
                                            ->setData('operator', '==')
                                            ->setValue($skus);
                $itemFound->addCondition($conditions);
                if ($step_qty > 1) {
                    $qty_condition = $objectManager->create(Product::class)
                                                   ->setType(Product::class)
                                                   ->setData('attribute', 'quote_item_qty')
                                                   ->setData('operator', '>=')
                                                   ->setValue($step_qty);
                    $itemFound->addCondition($qty_condition);
                }
            }
            return $this->createSalesRule($salesRule);
        } catch (Exception $exception) {
            $msg = sprintf(
                'No #%s Error: %s',
                $offerNo,
                $exception->getMessage()
            );
            $this->log($msg);
        }

        return false;
    }

    /**
     * @param $salesRule
     * @param $offer
     * @param $data
     *
     * @return mixed
     */
    public function addDefaultData($salesRule, $offer, $data)
    {
        $salesRule->setName($offer['Description']);
        $salesRule->setDescription($offer['Description'] . ' #' . $offer['No']);
        $salesRule->setFromDate($offer['Starting_Date']);
        $salesRule->setToDate($offer['Ending_Date']);
        $salesRule->setRuleId($offer['Priority']);
        $salesRule->setUsesPerCustomer('1000');
        $salesRule->setCustomerGroupIds([0, 1, 2, 3]);
        $salesRule->setIsActive($this->getStatus($offer));
        $salesRule->setStopRulesProcessing('0');
        $salesRule->setIsAdvanced('1');
        $salesRule->setSortOrder($offer['Priority']);
        $salesRule->setSimpleAction($data['offerType']);
        $salesRule->setDiscountAmount($data['discountPercentage']);
        $salesRule->setWkrulesruleNqty('1');
        $salesRule->setSimpleFreeShipping('0');
        $salesRule->setApplyToShipping('0');
        $salesRule->setwkrulesrule('0');
        $salesRule->setwkrulesruleNqty('0');
        $salesRule->setwkrulesruleSkipRule('0');
        $salesRule->setmaxDiscount('');
        $salesRule->setpromoCats('');
        $salesRule->setnThreshold('');
        $salesRule->setTimesUsed('0');
        $salesRule->setIsRss('0');
        $salesRule->setWebsiteIds($this->getWebsiteIds($offer));
        $salesRule->setCouponType();
        $salesRule->setCouponCode();
        $salesRule->setUsesPerCoupon(null);
        $salesRule->setStopRulesProcessing(1);
        $salesRule->setOfferNo($offer['No']);
        return $salesRule;
    }

    /**
     * @param $salesRule
     *
     * @return false|Rule
     */
    public function createSalesRule($salesRule)
    {
        try {
            $salesRule->save();
            return $salesRule;
        } catch (Exception $exception) {
            $des     = $salesRule->getDescription();
            $offerNo = substr($des, strrpos($des, '#') + 1);
            $msg     = sprintf(
                'No #%s Error: %s',
                $offerNo,
                $exception->getMessage()
            );
            $this->log($msg);
        }

        return false;
    }

    /**
     * @param $offer
     *
     * @return int[]
     */
    public function getWebsiteIds($offer): array
    {
        if (!empty($offer['Price_Group'])) {
            $priceGroup = $offer['Price_Group'];
            if (!isset($this->priceGroups[$priceGroup])) {
                $select                         = $this->connection->select()
                                                                   ->from(['w' => $this->getTable('web_store_price_group')], ['magento_store_id'])
                                                                   ->where('w.price_group_code = ?', $priceGroup);
                $this->priceGroups[$priceGroup] = $this->connection->fetchCol($select);
            }

            return $this->priceGroups[$priceGroup];
        }

        return [2, 3, 4];
    }

    /**
     * @param $offer
     *
     * @return int
     */
    public function getStatus($offer)
    {
        if (!empty($offer['Status']) && $offer['Status'] == 'Enabled') {
            return 1;
        }

        return 0;
    }

    /**
     * @param $line
     *
     * @return string
     */
    public function getOfferType($line)
    {
        if (!empty($line['Disc_Type']) && $line['Disc_Type'] == 'Disc. %') {
            return 'buy_x_get_n_percent_discount';
        }

        return 'buy_x_get_n_fixed_price';
    }

    /**
     * @param $line
     *
     * @return string
     */
    public function getOfferTypeLine($line)
    {
        if (!empty($line['Disc_Type']) && $line['Disc_Type'] == 'Disc. %') {
            return 'group_n_percent_discount';
        }

        return 'group_nth';
    }

    /**
     * @param $offerNo
     *
     * @return mixed
     */
    public function getWebOfferLine($offerNo)
    {
        $uri = $this->getApiUriNav(
            'offer_line',
            '$format=application/json&$filter=Offer_No%20eq%20%27' . $offerNo . '%27'
        );
        return $this->requestGet($uri);
    }

    /**
     * @param int $skip
     *
     * @return mixed
     */
    public function getWebOfferHeader($skip = 0)
    {
        $uri = $this->getApiUriNav(
            'offer_header',
            [
                '$format' => 'application/json',
                '$top'    => $this->process,
                '$skip'   => $skip,
            ]
        );
        return $this->requestGet($uri);
    }

    /**
     * @param $name
     * @param $params
     *
     * @return string
     */
    public function getApiUriNav($name, $params = null)
    {
        $api     = $this->getConfig($name);
        $host    = $this->getConfig('host');
        $company = $this->getConfig('company');
        $uri     = sprintf('%s/Company(\'%s\')/%s', $host, $company, $api);
        if (!empty($params)) {
            $query = $params;
            if (is_array($params)) {
                $query = http_build_query($params);
            }

            $uri = sprintf('%s?%s', $uri, $query);
        }

        return $uri;
    }

    /**
     * @param $path
     *
     * @return mixed
     */
    protected function getConfig($path)
    {
        return $this->scopeConfig->getValue(self::NAV_INFO . $path, ScopeInterface::SCOPE_STORE);
    }

    /**
     * @return string
     */
    public function getUserPwd()
    {
        $user = $this->getConfig('user');
        $pwd  = $this->getConfig('pwd');
        return sprintf('%s:%s', $user, $pwd);
    }

    /**
     * @param $uri
     *
     * @return array
     */
    public function requestGet($uri): array
    {
        try {
            $curl = curl_init();
            curl_setopt_array(
                $curl,
                [
                    CURLOPT_URL            => $uri,
                    CURLOPT_HTTPAUTH       => CURLAUTH_NTLM,
                    CURLOPT_USERPWD        => $this->getUserPwd(),
                    CURLOPT_RETURNTRANSFER => true,
                    CURLOPT_ENCODING       => '',
                    CURLOPT_MAXREDIRS      => 10,
                    CURLOPT_TIMEOUT        => 0,
                    CURLOPT_FOLLOWLOCATION => true,
                    CURLOPT_HTTP_VERSION   => CURL_HTTP_VERSION_1_1,
                    CURLOPT_CUSTOMREQUEST  => 'GET',
                ]
            );
            $resp = curl_exec($curl);
            curl_close($curl);
            return json_decode($resp, TRUE);
        } catch (Exception $exception) {
            $msg = sprintf('API %s Error: %s', $uri, $exception->getMessage());
            $this->log($msg);
        }

        return [];
    }

    /**
     * @param $name
     *
     * @return string
     */
    private function getTable($name)
    {
        return $this->connection->getTableName($name);
    }

    /**
     * @param $data
     */
    public function updateSyncLog($data)
    {
        $this->connection->insertMultiple(
            $this->getTable('nav_sync_log'),
            $data
        );
    }

    /**
     * @return string
     */
    public function getCacheTag()
    {
        return date('Ymd_') . self::CACHE_TAG;
    }

    /**
     * @param $msg
     *
     * @return $this
     * @throws \Zend_Log_Exception
     */
    protected function log($msg)
    {
        $fileName = sprintf('/var/log/offer_sync_%s.log', date('Ymd'));
        $writer = new \Zend_Log_Writer_Stream(BP . $fileName);
        $logger = new \Zend_Log();
        $logger->addWriter($writer);
        $logger->info($msg);
        return $this;
    }
}
