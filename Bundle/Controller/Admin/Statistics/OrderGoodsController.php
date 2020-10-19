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

namespace Bundle\Controller\Admin\Statistics;

use Exception;
use Request;

/**
 * [관리자 모드] 매출분석 > 매출통계 페이지
 *
 * @package Bundle\Controller\Admin\Statistics
 * @author  Jong-tae Ahn <qnibus@godo.co.kr>
 */
class OrderGoodsController extends OrderDayController
{
    /**
     * @inheritdoc
     */
    public function index()
    {
        try {
            // 모드 설정 (반드시 설정해야 함)
            $this->groupType = 'goods';

            // 부모 index 실행
            parent::index();

            // 상품별 주문현황 전용 페이지
            $this->getView()->setPageName('statistics/order_goods.php');

        } catch (Exception $e) {
            throw $e;
        }
    }
}
