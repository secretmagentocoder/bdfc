<?php

namespace Amasty\Orderattr\Model\Webapi;

/**
 * Replaces a "%amasty_cart_id%" value with the current authenticated customer's cart
 */
class ParamOverriderAmastyCartId  extends \Magento\Quote\Model\Webapi\ParamOverriderCartId
{

}
