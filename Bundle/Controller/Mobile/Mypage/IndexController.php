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

namespace Bundle\Controller\Mobile\Mypage;

use Component\Database\DBTableField;
use Component\Goods\GoodsCate;
use Component\Page\Page;
use Cookie;
use Exception;
use Framework\Utility\DateTimeUtils;
use Request;
use Session;

/**
 * Class MypageQnaController
 *
 * @package Bundle\Controller\Mobile\Mypage
 * @author  Jong-tae Ahn <qnibus@godo.co.kr>
 */
class IndexController extends \Controller\Mobile\Controller
{
    /**
     * {@inheritdoc}
     */
    public function index()
    {
        try {
            // 진행 중인 주문
            $order = \App::load(\Component\Order\Order::class);
            $this->setData('eachOrderStatus', $order->getEachOrderStatus(Session::get('member.memNo'), null, 30));

            // 최근 주문리스트
            $startDate = date('Y-m-d', strtotime('-30 days'));
            $endDate = date('Y-m-d');
            $orderData = $order->getOrderList(
                3, [
                    $startDate,
                    $endDate,
                ]
            );
            $this->setData('orderData', gd_isset($orderData));

            $ordersByRegisterDay = [];
            foreach ($orderData as $index => $item) {
                if ($item['orderGoodsCnt'] > 1) {
                    $firstGoods = $item['goods'][0];
                    unset($item['goods']);
                    $item['goods'][] = $firstGoods;
                }
                $ordersByRegisterDay[DateTimeUtils::dateFormat('Y-m-d', $item['regDt'])][] = $item;
            }
            $this->setData('ordersByRegisterDay', gd_isset($ordersByRegisterDay));
            $this->setData('isMyPage', true);

            // 사용자 반품/교환/환불 신청 사용여부
            $orderBasic = gd_policy('order.basic');
            $this->setData('userHandleFl', gd_isset($orderBasic['userHandleFl'], 'y') === 'y');

            // 주문셀 합치는 조건
            $this->setData('cellCombineStatus', $order->statusListCombine);

            $mallBySession = SESSION::get(SESSION_GLOBAL_MALL);
            if($mallBySession) {
                $todayCookieName = 'todayGoodsNo'.$mallBySession['sno'];
            } else {
                $todayCookieName = 'todayGoodsNo';
            }

            // 최근 본 상품번호 추출
            if (Cookie::has($todayCookieName)) {
                $todayGoodsNo = json_decode(Cookie::get($todayCookieName));
                $todayGoodsNo = implode(INT_DIVISION, $todayGoodsNo);

                // 최근 본 상품데이터 추출후 rowno에 맞게 배열 재가공
                $goods = \App::load(\Component\Goods\Goods::class);
                $goodsData = $goods->goodsDataDisplay('goods', $todayGoodsNo, 4, 'sort asc', 'list', false, true, false, false, 180);
                $goodsData = array_chunk($goodsData, '4');
                $this->setData('widgetGoodsList', gd_isset($goodsData));
            }
        } catch (Exception $e) {
            throw $e;
        }
    }
}
