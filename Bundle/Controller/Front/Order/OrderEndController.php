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
namespace Bundle\Controller\Front\Order;

use Bundle\Component\Mall\MallDAO;
use Component\CartRemind\CartRemind;
use Framework\Debug\Exception\AlertRedirectException;
use Component\Mall\Mall;
use Message;
use Globals;
use Session;
use Request;

/**
 * 주문 완료 페이지
 *
 * @package Bundle\Controller\Front\Order
 * @author  Jong-tae Ahn <qnibus@godo.co.kr>
 */
class OrderEndController extends \Controller\Front\Controller
{
    /**
     * index
     *
     */
    public function index()
    {
        try {
            $delivery = \App::load(\Component\Delivery\Delivery::class);
            if (Session::get('cartRemind') > 0){
                $cartRemind = new CartRemind();
                //장바구니 알림 주문 카운트 증가
                $cartRemind->setCartRemindOrderCount();
            }
            // 주문번호
            $orderNo = Request::get()->get('orderNo');

            // 결제 실패시의 모드에 따른 처리
            if (Request::get()->has('mode')) {
                // 고객 결제 중단 처리
                if (Request::get()->get('mode') === 'pgUserStop') {
                    $orderAdmin = \App::load('\\Component\\Order\\OrderAdmin');
                    $orderAdmin->setStatusChangePgStop($orderNo);
                    $pgFailReason = __('고객님의 결제 중단에 의해서 주문이 취소 되었습니다.');
                }

                // 고객 결제 인증시간 초과 처리
                if (Request::get()->get('mode') === 'pgTimeOver') {
                    $orderAdmin = \App::load('\\Component\\Order\\OrderAdmin');
                    $orderAdmin->setStatusChangePgStop($orderNo);
                    $pgFailReason = __('고객님의 결제창의 인증시간이 초과되어 주문이 취소 되었습니다.');
                }
            }

            // 마일리지 지급 정보
            $mileage = gd_mileage_give_info();
            $this->setData('mileage', $mileage['info']);

            // 예치금 정책
            $depositUse = gd_policy('member.depositConfig');
            $this->setData('depositUse', $depositUse);

            // 주문 상품 정보
            $order = \App::load('\\Component\\Order\\Order');
            $orderInfo = $order->getOrderView(gd_isset($orderNo));
            $orderInfo['visitDeliveryInfo'] = $delivery->getVisitDeliveryInfo($orderInfo);
            $orderDeliveryInfo = $order->getOrderDeliveryInfo(gd_isset($orderNo));
            $this->setData('orderDeliveryInfo', $orderDeliveryInfo);

            if ($orderInfo['multiShippingFl'] == 'y') {
                $multiOrderInfo = $order->getMultiOrderInfo($orderNo);
                $this->setData('multiOrderInfo', $multiOrderInfo);
            }

            // 에이스카운터 주문완료 통신
            $acecounter = \App::load('\\Component\\Nhn\\AcecounterCommonScript');
            $orderSendData = $acecounter->getOrderSend(gd_isset($orderNo));

            // 수취인 배송 국가명
            $receiverCountry = MallDAO::getInstance()->selectCountries($orderInfo['receiverCountryCode']);
            $this->setData('receiverCountryName', $receiverCountry['countryName']);

            // PG 실패 이유를 갱신
            if (isset($pgFailReason) === true && empty($pgFailReason) === false) {
                $orderInfo['pgFailReason'] = $pgFailReason;
            }

            // pgName 설정
            if (empty($orderInfo['pgName']) === true) {
                $orderInfo['pgName'] = gd_pgs($orderInfo['settleKind'])['pgName'];
            }

            //네이버공통유입스크립트 관련(결제가 정상적으로 이루어지지 않은경우 제외 출력)
            if($orderInfo['orderNo'] && $orderInfo['orderStatus'] !='f') {
                $naverCommonInflowScript = \App::load('\\Component\\Naver\\NaverCommonInflowScript');
                $naverCommonInflowScript = $naverCommonInflowScript->getOrderCompleteData($orderInfo['orderNo']);
            }

            // 세금계산서 이용안내
            $taxInfo = gd_policy('order.taxInvoice');
            if (gd_isset($taxInfo['taxInvoiceUseFl']) == 'y') {
                $taxInvoiceInfo = gd_policy('order.taxInvoiceInfo');
                if ($taxInfo['taxinvoiceInfoUseFl'] == 'y') {
                    $this->setData('taxinvoiceInfo', nl2br($taxInvoiceInfo['taxinvoiceInfo']));
                }
            }

            //facebook Dynamic Ads 외부 스크립트 적용
            $facebookAd = \App::Load('\\Component\\Marketing\\FacebookAd');
            $currency = gd_isset(Mall::getSession('currencyConfig')['code'], 'KRW');
            $fbConfig = $facebookAd->getConfig();
            $fbConfigExtension = $facebookAd->getExtensionConfig();
            if(($fbConfig['fbUseFl'] == 'y' && $fbConfig['orderEndScriptFl'] == 'y') || $fbConfigExtension['fbUseFl'] == 'y') {
                $goodsInfo = $order->getOrderGoodsData($orderInfo['orderNo']);
                // 상품번호 추출
                $goodsNo = [];
                foreach ($goodsInfo as $key => $val){
                    foreach($val as $key2 => $val2){
                        $goodsNo[] = $val2['goodsNo'];
                    }
                }
                $fbScript = $facebookAd->getFbOrderEndScript($goodsNo, $orderInfo['totalGoodsPrice'], $currency);
                $this->setData('fbOrderEndScript', $fbScript);
            }
            // IFDO order_end 페이지 수집 중 cateCd를 위해 추가됨
            $goodsAdmin = \App::Load('\\Component\\Goods\\GoodsAdmin');
            foreach($orderInfo['goods'] as $key => $val){
                $categoryList = $goodsAdmin->getGoodsLinkCategory($val['goodsNo']);
                $orderInfo['goods'][$key]['cateCd'] = $categoryList[0]['cateCd'];
            }

            $this->setData('naverCommonInflowScript', gd_isset($naverCommonInflowScript));
            $this->setData('orderInfo', gd_isset($orderInfo));

        } catch (\Exception $e) {
            //throw new AlertRedirectException(__('안내') . $e->getMessage(), null, null, URI_HOME);
        }
    }
}

