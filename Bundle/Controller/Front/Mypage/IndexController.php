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

namespace Bundle\Controller\Front\Mypage;

use Bundle\Component\PlusShop\PlusReview\PlusReviewArticleFront;
use Component\Board\BoardWrite;
use Component\Board\Board;
use Component\Database\DBTableField;
use Component\Goods\GoodsCate;
use Component\Page\Page;
use Cookie;
use Exception;
use Framework\Utility\GodoUtils;
use Request;
use Session;

/**
 * Class MypageQnaController
 *
 * @package Bundle\Controller\Front\Mypage
 * @author  Jong-tae Ahn <qnibus@godo.co.kr>
 */
class IndexController extends \Controller\Front\Controller
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
            $orderData = $order->getOrderList(3, [$startDate, $endDate]);
            $board = new BoardWrite(['bdId'=>\Bundle\Component\Board\Board::BASIC_GOODS_REIVEW_ID]);
            if(GodoUtils::isPlusShop(PLUSSHOP_CODE_REVIEW)){
                $plusReview = new PlusReviewArticleFront();
                $isPlusReview =  true;
                $this->addScript(['plusReview/gd_plus_review.js?popup=no']);
            }
            $orderReorderCalculation = \App::load('\\Component\\Order\\ReOrderCalculation');
            foreach($orderData as &$val){
                foreach($val['goods'] as &$orderGoods){
                    $handleData = $orderReorderCalculation->getOrderHandleData($val['orderNo'], null, null, $orderGoods['handleSno']);
                    $orderGoods['handleDetailReasonShowFl'] = $handleData[0]['handleDetailReasonShowFl'];
                    //교환추가 출력안되게
                    if($handleData[0]['handleMode'] == 'z'){
                        $orderGoods['handleDetailReasonShowFl'] = 'n';
                    }
                    $orderGoods['viewWriteGoodsReview'] = $board->viewWriteGoodsReview($orderGoods);
                    if($isPlusReview) {
                        $orderGoods['viewWritePlusReview'] = $plusReview->viewMypageReviewBtn($orderGoods);
                    }
                }
            }

            // 배송 중, 배송 완료된 상품 카운트해서 버튼 생성 여부
            $orderData = $order->getOrderSettleButton($orderData);

            $this->setData('orderData', gd_isset($orderData));
            // 사용자 반품/교환/환불 신청 사용여부
            $orderBasic = gd_policy('order.basic');
            $this->setData('userHandleFl', gd_isset($orderBasic['userHandleFl'], 'y') === 'y');

            // 주문셀 합치는 조건
            $this->setData('cellCombineStatus', $order->statusListCombine);

            $this->setData('goodsReviewId',Board::BASIC_GOODS_REIVEW_ID);

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

                $imgConfig = gd_policy('goods.image');
                $goodsData = $goods->goodsDataDisplay('goods', $todayGoodsNo, 4, 'sort asc', 'main', false, true, false, false, $imgConfig['main']['size1']);
                if (empty($goodsData) === false) {
                    $goodsData = array_chunk($goodsData, '4');
                    $this->setData('widgetGoodsList', gd_isset($goodsData));
                }
            }
        } catch (Exception $e) {
            throw $e;
        }
    }
}
