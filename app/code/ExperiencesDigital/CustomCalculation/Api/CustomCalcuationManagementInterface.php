<?php
/**
 * Copyright ©  All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace ExperiencesDigital\CustomCalculation\Api;

interface CustomCalcuationManagementInterface
{

    /**
     * POST for custom_calcuation api
     * @param mixed $cart
     * @return float
     */
    public function postCustom_calcuation($cart);
}

