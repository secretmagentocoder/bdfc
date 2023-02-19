<?php
/**
 * Webkul Software.
 *
 * @category  Webkul
 * @package   Webkul_SpecialPromotions
 * @author    Webkul
 * @copyright Copyright (c) Webkul Software Private Limited (https://webkul.com)
 * @license   https://store.webkul.com/license.html
 */
namespace Webkul\SpecialPromotions\Plugin\Model\Rule\Metadata;

use Magento\SalesRule\Model\Rule\Metadata\ValueProvider as SalesRuleValueProvider;
use Magento\SalesRule\Model\Rule;

/**
 * Metadata provider for sales rule edit form.
 */
class ValueProvider
{
    /**
     * Rule type actions
     */

    public const WK_CHEAPEST = 'cheapest';

    public const WK_MOST_EXPENSIVE = 'most_expensive';

    public const WK_MONEY_AMOUNT = 'money_amount';

    public const WK_BUY_X_GET_N_PERCENT_DISCOUNT = 'buy_x_get_n_percent_discount';

    public const WK_BUY_X_GET_N_FIXED_DISCOUNT = 'buy_x_get_n_fixed_discount';

    public const WK_BUY_X_GET_N_FIXED_PRICE = 'buy_x_get_n_fixed_price';

    public const WK_EACH_NTH_PERCENT_DISCOUNT = 'each_n_percent_discount';

    public const WK_EACH_NTH_FIXED_DISCOUNT = 'each_n_fixed_discount';

    public const WK_EACH_NTH_FIXED_PRICE = 'each_n_fixed_price';

    public const WK_EACH_PAFT_NTH_PERCENT = 'each_product_aft_nth_percent';

    public const WK_EACH_PAFT_NTH_FIXED = 'each_product_aft_nth_fixed';

    public const WK_EACH_PAFT_NTH_FIXED_PRICE = 'each_product_aft_nth_fixed_price';

    public const WK_GROUP_N = 'group_nth';

    public const WK_GROUP_N_DISCOUNT = 'group_n_percent_discount';

    public const WK_PRODUCT_SET_DISCOUNT = 'product_set_percent';

    public const WK_PRODUCT_SET_DISCOUNT_FIXED = 'product_set_discount_fixed';
    
    /**
     * Initialize
     *
     * @param \Webkul\SpecialPromotions\Helper\Data $rulesDataHelper
     */
    public function __construct(
        \Webkul\SpecialPromotions\Helper\Data $rulesDataHelper
    ) {
        $this->rulesDataHelper = $rulesDataHelper;
    }

   /**
    * Get metadata for sales rule form. It will be merged with form UI component declaration.
    *
    * @param SalesRuleValueProvider $subject
    * @param [type] $result
    * @return array
    */
    public function afterGetMetadataValues(SalesRuleValueProvider $subject, $result)
    {
        $isEnable = $this->rulesDataHelper->checkModuleStatus();
        if ($isEnable) {
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
            
            $result['actions']['children']['simple_action']['arguments']['data']['config']['options'] =  $applyOptions;
            $result['actions']['children']['wkrulesrule']['arguments']['data']['config']['options'] = $applyOptionsWk;
            $result['actions']['children']['wkrulesrule_nqty']['arguments']['data']['config']['value'] = 0;
            $result['actions']
                    ['children']
                    ['wkrulesrule_skip_rule']
                    ['arguments']
                    ['data']
                    ['config']
                    ['options'] = $skipRulesWk;
            $result['actions']['children']['max_discount']['arguments']['data']['config']['value'] = '';
            $result['actions']['children']['n_threshold']['arguments']['data']['config']['value'] = '';
            $result['actions']['children']['promo_cats']['arguments']['data']['config']['value'] = '';
            $result['actions']['children']['promo_skus']['arguments']['data']['config']['value'] = '';
            return $result;
        } else {
            return $result;
        }
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
            ['label' => __('Percent Discount: each 2-d, 4-th, 6-th with 10% Off'),
            'value' => self::WK_EACH_NTH_PERCENT_DISCOUNT],
            ['label' => __('Fixed Discount: each 3-d, 6-th, 9-th with $10 Off'),
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
            ['label' => __('Percent Discount: each 1st, 3rd, 5th with 10% Off after 5 items added to the cart'),
            'value' => self::WK_EACH_PAFT_NTH_PERCENT],
            ['label' => __('Fixed Discount: each 3d, 7th, 11th with $10 Off after 5 items added to the cart'),
            'value' => self::WK_EACH_PAFT_NTH_FIXED],
            ['label' => __('Fixed Price: each 5th, 7th, 9th for $79.90 after 5 items added to the cart'),
            'value' => self::WK_EACH_PAFT_NTH_FIXED_PRICE]
        ];
    }
    
    /**
     * Get Each Group N options
     *
     * @return void
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
     * Get Product Set options
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
}
