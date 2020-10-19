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
 * @link http://www.godo.co.kr
 */

namespace Bundle\Controller\Admin\Provider\Order;

use Component\Godo\NaverPayAPI;
use Component\Naver\NaverPay;
use Component\Order\Order;
use Component\Order\OrderAdmin;
use Framework\Debug\Exception\AlertOnlyException;
use Exception;

/**
 * 주문상세의 네이버페이 상태변경 액션처리
 *
 * @package Controller\Admin\Order
 * @author  Jong-tae Ahn <lnjts@godo.co.kr>
 */
class OrderNaverpayPsController extends \Controller\Admin\Order\OrderNaverpayPsController
{
    /**
     * @inheritdoc
     * @throws Exception
     */
    public function index()
    {
        parent::index();
    }
}
