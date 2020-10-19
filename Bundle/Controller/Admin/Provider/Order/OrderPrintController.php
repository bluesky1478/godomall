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

use Exception;

/**
 * 주문내역서/간이영수증/거래명세서/세금계산서 출력 팝업
 *
 * @package Bundle\Controller\Admin\Order
 * @author  Jong-tae Ahn <qnibus@godo.co.kr>
 */
class OrderPrintController extends \Controller\Admin\Order\OrderPrintController
{
    /**
     * @inheritdoc
     *
     * @throws Exception
     */
    public function index()
    {
        parent::index();
    }
}
