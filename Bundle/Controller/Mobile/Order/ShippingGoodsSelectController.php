<?php

/**
 * This is commercial software, only users who have purchased a valid license
 * and accept to the terms of the License Agreement can install and use this
 * program.
 *
 * Do not edit or add to this file if you wish to upgrade Godomall5 to newer
 * versions in the future.
 *
 * @copyright ⓒ 2016, NHN godo: Corp.
 * @link      http://www.godo.co.kr
 */

namespace Bundle\Controller\Mobile\Order;

use Component\Cart\Cart;
use Component\Database\DBTableField;
use Component\Order\Order;
use Component\Mall\Mall;
use Request;

/**
 * 복수배송지 상품 선택
 */
class ShippingGoodsSelectController extends \Controller\Front\Order\ShippingGoodsSelectController
{
    /**
     * {@inheritdoc}
     */
    public function index()
    {
        parent::index();
    }
}
