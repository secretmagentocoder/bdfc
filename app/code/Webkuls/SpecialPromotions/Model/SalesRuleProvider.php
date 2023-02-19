<?php
/**
 * Webkulss Software.
 *
 * @category  Webkuls
 * @package   Webkuls_SpecialPromotions
 * @author    Webkuls Software Private Limited
 * @copyright Copyright (c) Webkuls Software Private Limited (https://Webkuls.com)
 * @license   https://store.Webkuls.com/license.html
 */


namespace Webkuls\SpecialPromotions\Model;

use Magento\Framework\Api\SearchCriteria;
use Magento\Framework\Api\SearchCriteriaBuilder;
use Magento\SalesRule\Api\Data\RuleSearchResultInterface;
use Magento\SalesRule\Api\RuleRepositoryInterface;
use Magento\SalesRule\Model\Data\Rule;

class   SalesRuleProvider
{
    /**
     * @var RuleRepositoryInterface
     */
    protected $ruleRepository;

    /**
     * @var SearchCriteriaBuilder
     */
    protected $criteriaBuilder;
/**
 * Initialize
 *
 * @param RuleRepositoryInterface $ruleRepository
 * @param SearchCriteriaBuilder $criteriaBuilder
 */
    public function __construct(
        RuleRepositoryInterface $ruleRepository,
        SearchCriteriaBuilder $criteriaBuilder
    ) {
        $this->ruleRepository = $ruleRepository;
        $this->criteriaBuilder = $criteriaBuilder;
    }

    /**
     * Get Rules By Id
     *
     * @param array $ruleIds
     *
     * @return RuleSearchResultInterface
     */
    public function getByRuleIds($ruleIds)
    {
        $criteria = $this->getCriteriaBuilder($ruleIds);

        return $this->ruleRepository->getList($criteria);
    }

    /**
     * Build rule criteria
     *
     * @param array $ruleIds
     *
     * @return SearchCriteria
     */
    private function getCriteriaBuilder($ruleIds)
    {
        return $this->criteriaBuilder->addFilter(
            Rule::KEY_RULE_ID,
            $ruleIds,
            'in'
        )->create();
    }

    /**
     *  Get sales Rule
     *
     * @return RuleRepositoryInterface
     */
    public function getRepository()
    {
        return $this->ruleRepository;
    }
}
