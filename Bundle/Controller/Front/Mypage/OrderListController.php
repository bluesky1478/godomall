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
use Component\Board\BoardConfig;
use Component\Board\BoardWrite;
use Component\Board\Board;
use Framework\Debug\Exception\AlertRedirectException;
use Framework\Debug\Exception\Except;
use App;
use Framework\Utility\GodoUtils;
use Framework\Utility\StringUtils;
use Message;
use Globals;
use Exception;
use Request;

/**
 * 마이페이지 > 주문배송/조회
 *
 * @package Bundle\Controller\Front\Mypage
 * @author  Jong-tae Ahn <qnibus@godo.co.kr>/**
 */
class OrderListController extends \Controller\Front\Controller
{
    /**
     * @inheritdoc
     *
     * @throws AlertRedirectException
     */
    public function index()
    {
        try {
            $locale = \Globals::get('gGlobal.locale');
            // 날짜 픽커를 위한 스크립트와 스타일 호출
            $this->addCss([
                'plugins/bootstrap-datetimepicker.min.css',
                'plugins/bootstrap-datetimepicker-standalone.css',
            ]);
            $this->addScript([
                'moment/moment.js',
                'moment/locale/' . $locale . '.js',
                'jquery/datetimepicker/bootstrap-datetimepicker.min.js',
            ]);

            if (\Component\Order\OrderMultiShipping::isUseMultiShipping() === true) {
                $this->setData('isUseMultiShipping', true);
            }

            // 모듈 설정
            $order = \App::load('\\Component\\Order\\Order');
            $this->setData('eachOrderStatus', $order->getEachOrderStatus(\Session::get('member.memNo'), null, 30));

            // 기본 조회 일자
            $startDate = date('Y-m-d', strtotime('-7 days'));
            $endDate = date('Y-m-d');
            $wDate = Request::get()->get('wDate', [$startDate, $endDate]);
            foreach ($wDate as $searchDateKey => $searchDateValue) {
                $wDate[$searchDateKey] = StringUtils::xssClean($searchDateValue);

                //추가적으로 날짜인지 확인하기
                if( !preg_match("/^([0-9]{4})-([0-9]{1,2})-([0-9]{1,2})$/", $wDate[$searchDateKey]) ){
                    $wDate[$searchDateKey] = date('Y-m-d');
                }
            }
            // 사용자 반품/교환/환불 신청 사용여부
            if (gd_is_plus_shop(PLUSSHOP_CODE_USEREXCHANGE) === true) {
                $orderBasic = gd_policy('order.basic');
                $this->setData('userHandleFl', gd_isset($orderBasic['userHandleFl'], 'y') === 'y');
            }

            // 주문 리스트 정보
            $this->setData('startDate', gd_isset($wDate[0]));
            $this->setData('endDate', gd_isset($wDate[1]));
            $this->setData('wDate', gd_isset($wDate));
            $orderData = $order->getOrderList(10, $wDate);
            $board = new BoardWrite(['bdId'=>\Bundle\Component\Board\Board::BASIC_GOODS_REIVEW_ID]);
            $isPlusReview = false;
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
            // 사용자 반품/교환/환불 신청 데이터 생성
            $orderData = $order->getOrderClaimList($orderData);

            // 배송 중, 배송 완료된 상품 카운트해서 버튼 생성 여부
            $orderData = $order->getOrderSettleButton($orderData);

            $this->setData('orderData', gd_isset($orderData));

            // 주문셀 합치는 조건
            $this->setData('cellCombineStatus', $order->statusListCombine);

            // 세금계산서 이용안내
            $taxInfo = gd_policy('order.taxInvoice');
            if (gd_isset($taxInfo['taxInvoiceUseFl']) == 'y') {
                $taxInvoiceInfo = gd_policy('order.taxInvoiceInfo');
                if ($taxInfo['taxinvoiceInfoUseFl'] == 'y') {
                    $this->setData('taxinvoiceInfo', nl2br($taxInvoiceInfo['taxinvoiceInfo']));
                }
            }

            // 상품 옵션가 표시설정 config 불러오기
            $optionPriceConf = gd_policy('goods.display');
            $this->setData('optionPriceFl', gd_isset($optionPriceConf['optionPriceFl'], 'y')); // 상품 옵션가 표시설정

            // 페이지 재설정
            $page = \App::load('\\Component\\Page\\Page');
            $this->setData('page', gd_isset($page));
            $this->setData('total', $page->recode['total']);
            $this->setData('goodsReviewId', Board::BASIC_GOODS_REIVEW_ID);
            $this->setData('mode', 'list');
        } catch (Exception $e) {
            throw new AlertRedirectException($e->getMessage(), null, null, URI_HOME);
        }
    }
}
