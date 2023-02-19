<?php
/**
 * Webkuls Software.
 *
 * @category  Webkuls
 * @package   Webkuls_SpecialPromotions
 * @author    Webkuls
 * @copyright Copyright (c) Webkuls Software Private Limited (https://Webkuls.com)
 * @license   https://store.Webkuls.com/license.html
 */
namespace Webkuls\SpecialPromotions\Model\Rule\Metadata;

use Magento\SalesRule\Model\ResourceModel\Rule\Collection;
use Magento\SalesRule\Model\Rule;
use Magento\Store\Model\System\Store;
use Magento\Customer\Api\GroupRepositoryInterface;
use Magento\Framework\Api\SearchCriteriaBuilder;
use Magento\Framework\Convert\DataObject;

/**
 * Metadata provider for sales rule edit form.
 */
class ValueProvider
{
    /**
     * Rule type actions
     */

    const WK_CHEAPEST = 'wkcheapest';

    const WK_MOST_EXPENSIVE = 'wkmost_expencive';

    const WK_MONEY_AMOUNT = 'money_amount';

    const WK_BUY_X_GET_N_PERCENT_DISCOUNT = 'buy_x_get_n_percdis';

    const WK_BUY_X_GET_N_FIXED_DISCOUNT = 'buy_xget_n_fixdisc';

    const WK_BUY_X_GET_N_FIXED_PRICE = 'buy_x_get_n_fixprice';

    const WK_EACH_NTH_PERCENT_DISCOUNT = 'each_n_perc';

    const WK_EACH_NTH_FIXED_DISCOUNT = 'each_n_fixed';

    const WK_EACH_NTH_FIXED_PRICE = 'each_n_fixed_price';

    const WK_EACH_PAFT_NTH_PERCENT = 'each_paft_n_percdisc';

    const WK_EACH_PAFT_NTH_FIXED = 'each_paft_n_fixdisc';

    const WK_EACH_PAFT_NTH_FIXED_PRICE = 'each_paft_n_fixprice';

    const WK_GROUP_N = 'group_n';

    const WK_GROUP_N_DISCOUNT = 'group_n_disc';

    const WK_PRODUCT_SET_DISCOUNT = 'product_set_percent';

    const WK_PRODUCT_SET_DISCOUNT_FIXED = 'product_set_fixed';

    /**
     * @var Store
     */
    protected $store;

    /**
     * @var GroupRepositoryInterface
     */
    protected $groupRepository;

    /**
     * @var SearchCriteriaBuilder
     */
    protected $searchCriteriaBuilder;

    /**
     * @var DataObject
     */
    protected $objectConverter;

    /**
     * @var \Magento\SalesRule\Model\RuleFactory
     */
    protected $salesRuleFactory;

    /**
     * Initialize dependencies.
     *
     * @param Store $store
     * @param GroupRepositoryInterface $groupRepository
     * @param SearchCriteriaBuilder $searchCriteriaBuilder
     * @param DataObject $objectConverter
     * @param \Magento\SalesRule\Model\RuleFactory $salesRuleFactory
     */
    public function __construct(
        Store $store,
        GroupRepositoryInterface $groupRepository,
        SearchCriteriaBuilder $searchCriteriaBuilder,
        DataObject $objectConverter,
        \Magento\SalesRule\Model\RuleFactory $salesRuleFactory
    ) {
        $this->store = $store;
        $this->groupRepository = $groupRepository;
        $this->searchCriteriaBuilder = $searchCriteriaBuilder;
        $this->objectConverter = $objectConverter;
        $this->salesRuleFactory = $salesRuleFactory;
    }
    
    /**
     * Buy X get Y options
     *
     * @return array
     */
    private function getBuyXGetY()
    {
        return [
            ['label' => __('Percent Discount: Buy X get Y Free'),
            'value' => self::WK_BUY_X_GET_N_PERCENT_DISCOUNT],
            ['label' => __('Fixed Discount:  Buy X get Y with $5 Off'),
            'value' => self::WK_BUY_X_GET_N_FIXED_DISCOUNT],
            ['label' => __('Fixed Price: Buy X get Y for $7.45'),
            'value' => self::WK_BUY_X_GET_N_FIXED_PRICE]
        ];
    }
    
    /**
     * Get Each nth rule options
     *
     * @return array
     */
    private function getEachNth()
    {
        return [
            ['label' => __('Percent Discount: each 2-th, 4-th, 6-th with 10% 0ff'),
            'value' => self::WK_EACH_NTH_PERCENT_DISCOUNT],
            ['label' => __('Fixed Discount: each 3-th, 6-th, 9-th with $10 0ff'),
            'value' => self::WK_EACH_NTH_FIXED_DISCOUNT],
            ['label' => __('Fixed Price: each 5th, 10th, 15th for $67'),
            'value' => self::WK_EACH_NTH_FIXED_PRICE]
        ];
    }
    
    /**
     * Get Each Product N options
     *
     * @return array
     */
    private function getEachProductN()
    {
        return [
            ['label' => __('Percent Discount: each 1st, 3rd, 5th with 10% 0ff after 5 items added to the cart'),
            'value' => self::WK_EACH_PAFT_NTH_PERCENT],
            ['label' => __('Fixed Discount: each 3d, 7th, 11th with $10 0ff after 5 items added to the cart'),
            'value' => self::WK_EACH_PAFT_NTH_FIXED],
            ['label' => __('Fixed Price: each 5th, 7th, 9th for $79.90 after 5 items added to the cart'),
            'value' => self::WK_EACH_PAFT_NTH_FIXED_PRICE]
        ];
    }
    
    /**
     * Get Each Group N options
     *
     * @return array
     */
    private function getEachGroupN()
    {
        return [
            ['label' => __('Fixed Price: Each 5 items for $50'),
            'value' => self::WK_GROUP_N],
            ['label' => __('Percent Discount: Each 5 items with 15% off'),
            'value' => self::WK_GROUP_N_DISCOUNT]
        ];
    }

    /**
     * get Product Set options
     *
     * @return array
     */
    private function getProductSet()
    {
        return [
            ['label' => __('Percent discount for product set'), 'value' => self::WK_PRODUCT_SET_DISCOUNT],
            ['label' => __('Fixed price for product set'), 'value' => self::WK_PRODUCT_SET_DISCOUNT_FIXED]
        ];
    }

    /**
     * Get Simple Action options
     *
     * @return array
     */
    public function getSimpleAction()
    {
        return [
            ['label' => __('Percent of product price discount'), 'value' =>  Rule::BY_PERCENT_ACTION],
            ['label' => __('Fixed amount discount'), 'value' => Rule::BY_FIXED_ACTION],
            ['label' => __('Fixed amount discount for whole cart'), 'value' => Rule::CART_FIXED_ACTION],
            ['label' => __('Buy X get Y free (discount amount is Y)'), 'value' => Rule::BUY_X_GET_Y_ACTION],
            ['label' => __('Popular'), 'value' => [
                    ['label' => __('The Cheapest, also for Buy 1 get 1 free'), 'value' => self::WK_CHEAPEST],
                    ['label' => __('Most Expensive'), 'value' => self::WK_MOST_EXPENSIVE],
                    ['label' => __('Get $Y for each $X spent'), 'value' => self::WK_MONEY_AMOUNT]
                ]
            ],
            ['label' => __('Buy X Get Y (X and Y are different products)'), 'value' => $this->getBuyXGetY()
            ],
            ['label' => __('Each N-th'), 'value' => $this->getEachNth()
            ],
            ['label' => __('Each Product After N'), 'value' => $this->getEachProductN()
            ],
            ['label' => __('Each Group of N'), 'value' => $this->getEachGroupN()
            ],
            ['label' => __('Product Set'), 'value' => $this->getProductSet()
            ]
        ];
    }
    
    /**
     * Get metadata for sales rule form. It will be merged with form UI component declaration.
     *
     * @param \Magento\SalesRule\Model\Rule $rule
     * @return array
     */
    public function getMetadataValues(\Magento\SalesRule\Model\Rule $rule)
    {
        $customerGroups = $this->groupRepository->getList($this->searchCriteriaBuilder->create())->getItems();
        $applyOptions = $this->getSimpleAction();

        $applyOptionsWk = [
            ['label' => __('Price (Special Price if Set)'), 'value' =>  0],
            ['label' => __('Price After Previous Discount(s)'), 'value' => 1],
            ['label' => __('Original Price'), 'value' => 2],
        ];
        $skipRulesWk = [
            ['label' => __('As Default'), 'value' =>  0],
            ['label' => __('Yes'), 'value' => 1],
            ['label' => __('No'), 'value' => 2],
            ['label' => __('Skip If Discounted'), 'value' => 3],
        ];
        $couponTypesOptions = [];
        $couponTypes = $this->salesRuleFactory->create()->getCouponTypes();
        foreach ($couponTypes as $key => $couponType) {
            $couponTypesOptions[] = [
                'label' => $couponType,
                'value' => $key,
            ];
        }

        $labels = $rule->getStoreLabels();

        return [
            'rule_information' => [
                'children' => [
                    'website_ids' => [
                        'arguments' => [
                            'data' => [
                                'config' => [
                                    'options' => $this->store->getWebsiteValuesForForm(),
                                ],
                            ],
                        ],
                    ],
                    'is_active' => [
                        'arguments' => [
                            'data' => [
                                'config' => [
                                    'options' => [
                                        ['label' => __('Active'), 'value' => '1'],
                                        ['label' => __('Inactive'), 'value' => '0']
                                    ],
                                ],
                            ],
                        ],
                    ],
                    'customer_group_ids' => [
                        'arguments' => [
                            'data' => [
                                'config' => [
                                    'options' => $this->objectConverter->toOptionArray($customerGroups, 'id', 'code'),
                                ],
                            ],
                        ],
                    ],
                    'coupon_type' => [
                        'arguments' => [
                            'data' => [
                                'config' => [
                                    'options' => $couponTypesOptions,
                                ],
                            ],
                        ],
                    ],
                    'is_rss' => [
                        'arguments' => [
                            'data' => [
                                'config' => [
                                    'options' => [
                                        ['label' => __('Yes'), 'value' => '1'],
                                        ['label' => __('No'), 'value' => '0']
                                    ],
                                ],
                            ],
                        ],
                    ],
                ]
            ],
            'actions' => [
                'children' => [
                    'simple_action' => [
                        'arguments' => [
                            'data' => [
                                'config' => [
                                    'options' => $applyOptions
                                ],
                            ]
                        ]
                    ],
                    'discount_amount' => [
                        'arguments' => [
                            'data' => [
                                'config' => [
                                    'value' => '0',
                                ],
                            ],
                        ],
                    ],
                    'discount_qty' => [
                        'arguments' => [
                            'data' => [
                                'config' => [
                                    'value' => '0',
                                ],
                            ],
                        ],
                    ],
                    'wkrulesrule' => [
                        'arguments' => [
                            'data' => [
                                'config' => [
                                    'options' => $applyOptionsWk
                                ],
                            ]
                        ]
                    ],
                    'wkrulesrule_nqty' => [
                        'arguments' => [
                            'data' => [
                                'config' => [
                                    'value' => '0',
                                ],
                            ],
                        ],
                    ],
                    'wkrulesrule_skip_rule' => [
                        'arguments' => [
                            'data' => [
                                'config' => [
                                    'options' => $skipRulesWk,
                                ],
                            ],
                        ],
                    ],
                    'max_discount' => [
                        'arguments' => [
                            'data' => [
                                'config' => [
                                    'value' => '',
                                ],
                            ],
                        ],
                    ],
                    'n_threshold' => [
                        'arguments' => [
                            'data' => [
                                'config' => [
                                    'value' => '',
                                ],
                            ],
                        ],
                    ],
                    'apply_to_shipping' => [
                        'arguments' => [
                            'data' => [
                                'config' => [
                                    'options' => [
                                        ['label' => __('Yes'), 'value' => '1'],
                                        ['label' => __('No'), 'value' => '0']
                                    ]
                                ],
                            ],
                        ],
                    ],
                    'stop_rules_processing' => [
                        'arguments' => [
                            'data' => [
                                'config' => [
                                    'options' => [
                                        ['label' => __('Yes'), 'value' => '1'],
                                        ['label' => __('No'), 'value' => '0'],
                                    ],
                                ],
                            ]
                        ]
                    ],
                    'promo_cats' => [
                        'arguments' => [
                            'data' => [
                                'config' => [
                                    'value' => '',
                                ],
                            ],
                        ],
                    ],
                    'promo_skus' => [
                        'arguments' => [
                            'data' => [
                                'config' => [
                                    'value' => '',
                                ],
                            ],
                        ],
                    ],
                ]
            ],
            'labels' => [
                'children' => [
                    'store_labels[0]' => [
                        'arguments' => [
                            'data' => [
                                'config' => [
                                    'value' => isset($labels[0]) ? $labels[0] : '',
                                ],
                            ]
                        ]
                    ]
                ]
            ],
        ];
    }
}
