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

use Framework\Debug\Exception\AlertRedirectException;
use Framework\Debug\Exception\Except;
use App;
use Message;
use Globals;
use Exception;
use Request;
use Framework\Utility\StringUtils;

/**
 * 마이페이지 > 주문배송/조회
 *
 * @package Bundle\Controller\Front\Mypage
 * @author  Jong-tae Ahn <qnibus@godo.co.kr>/**
 */
class RefundListController extends \Controller\Front\Controller
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
            $order = App::load('\\Component\\Order\\Order');
            $this->setData('eachOrderStatus', $order->getEachOrderStatus(\Session::get('member.memNo'), null, 30));

            // 기본 조회 일자
            $startDate = date('Y-m-d', strtotime('-7 days'));
            $endDate = date('Y-m-d');
            $wDate = Request::get()->get('wDate', [$startDate, $endDate]);

            // 보안취약점 요청사항 추가
            $wDate[0] = preg_replace("/([^0-9\-])/", "", $wDate[0]);
            $wDate[1] = preg_replace("/([^0-9\-])/", "", $wDate[1]);

            // 주문 리스트 정보
            $this->setData('startDate', gd_isset($wDate[0]));
            $this->setData('endDate', gd_isset($wDate[1]));
            $this->setData('wDate', gd_isset($wDate));

            // 사용자 반품/교환/환불 신청 사용여부
            $mode = Request::get()->get('mode');
            if (gd_is_plus_shop(PLUSSHOP_CODE_USEREXCHANGE) === true) {
                $orderBasic = gd_policy('order.basic');
                $this->setData('userHandleFl', gd_isset($orderBasic['userHandleFl'], 'y') === 'y');
            }

            if ($orderBasic['userHandleFl'] === 'y' && (empty($mode) === true || $mode == 'refundRequest')) {
                $mode = 'refundRequest';
                $orderData = $order->getOrderList(10, $wDate, $mode);
                $orderData = $order->getOrderClaimList($orderData, $mode);
            } else {
                $mode = 'refund';
                $orderData = $order->getOrderList(10, $wDate, $mode);
            }
            $this->setData('mode', $mode);
            $this->setData('orderData', gd_isset($orderData));

            // 주문셀 합치는 조건
            $this->setData('cellCombineStatus', $order->statusListCombine);

            // 상품 옵션가 표시설정 config 불러오기
            $optionPriceConf = gd_policy('goods.display');
            $this->setData('optionPriceFl', gd_isset($optionPriceConf['optionPriceFl'], 'y')); // 상품 옵션가 표시설정

            // 페이지 재설정
            $page = App::load('\\Component\\Page\\Page');
            $this->setData('page', gd_isset($page));
            $this->setData('total', $page->recode['total']);

        } catch (Exception $e) {
            throw new AlertRedirectException($e->getMessage(), null, null, URI_HOME);
        }
    }
}
