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
namespace Bundle\Controller\Admin\Order;

use Framework\Debug\Exception\AlertCloseException;
use App;
use Request;
use Exception;
use Globals;
use Session;

/**
 * 환불 완료 리스트 페이지
 * [관리자 모드] 환불 완료 리스트 페이지
 *
 * @author artherot
 * @version 1.0
 * @since 1.0
 * @param array $get
 * @param array $post
 * @param array $files
 * @throws Exception
 * @copyright ⓒ 2016, NHN godo: Corp.
 */
class RefundViewNewController extends \Controller\Admin\Controller
{
    /**
     * @{inheritdoc}
     */
    public function index()
    {
        try {
            // 공급사의 경우 해당 페이지 열어볼 수 없슴
            if ($this->getData('isProvider')) {
                throw new AlertCloseException(__('공급사 권한이 없습니다. 본사에 문의해주세요.'));
            }

            // 설정 호출
            $pgCodeConfig = App::getConfig('payment.pg');

            // 메뉴 설정
            $this->callMenu('order', 'cancel', 'refundView');
            $this->addScript(
                [
                    'jquery/jquery.multi_select_box.js',
                ]
            );

            // request
            $getValue = Request::get()->toArray();
            $this->setData('getValue', $getValue);

            // 모듈 호출
            $order = App::load('\\Component\\Order\\OrderAdmin');
            $this->setData('order', $order);

            // 주문 정보
            $handleSno = null;
            $excludeStatus = null;
            $statusFl = Request::get()->get('statusFl', 0);
            if (Request::get()->has('isAll')) {
                if (Request::get()->has('statusFl')) {
                    $excludeStatus = ['r3', 'e1', 'e2', 'e3', 'e4', 'e5', 'c1', 'c2', 'c3', 'c4'];
                } else {
                    $excludeStatus = ['r1', 'r2', 'e1', 'e2', 'e3', 'e4', 'e5', 'c1', 'c2', 'c3', 'c4'];
                }
            } else {
                $handleSno = Request::get()->get('handleSno');
            }

            $this->setData('handleSno', Request::get()->get('handleSno'));
            $this->setData('isAll', Request::get()->get('isAll', 0));
            $this->setData('statusFl', $statusFl);

            if ($getData = $order->getRefundOrderView($getValue['orderNo'], null, $handleSno, 'r', $excludeStatus, null, $statusFl)) {
                $this->setData('data', $getData);
                //복수배송지를 사용하여 리스트 데이터 배열의 키를 체인지한 데이터인지 체크
                $this->setData('useMultiShippingKey', $getData['useMultiShippingKey']);
            } else {
                throw new AlertCloseException(__('처리 할 주문상품이 없습니다.'));
            }

            // 해외상점의 경우 무조건 전체 반품/교환/환불처리하도록 함
            if ($getData['mallSno'] > DEFAULT_MALL_NUMBER && $handleSno !== null) {
                throw new AlertCloseException(__('해외상점 취소/교환/반품/환불은 전체 처리만 가능합니다.'));
            }

            // 페이코 주문이고, 페이코 쿠폰이 사용된 경우 쿠폰이 환불할 금액보다 큰 경우 PG환불 제한 - 페이코 쿠폰은 신용카드 결제에서만 사용가능
            if ($getData['pgName'] == 'payco' && substr($getData['settleKind'], 1, 1) == 'c') {
                $checkoutData = json_decode($getData['checkoutData']);
                if ($checkoutData->paycoCouponUseStatus == 'Y' && $checkoutData->repayPaycoCoupon == 'N' && $checkoutData->paycoFirsthandRepay != 'Y') {
                    $this->setData('paycoCoupon', $checkoutData->paycoCouponPrice);
                } else if ($checkoutData->paycoFirsthandRepay == 'Y') {
                    $this->setData('firstHand', $checkoutData->paycoFirsthandRepay);
                }
            }

            // 취소사유
            $cancelReasonCode = gd_array_change_key_value(gd_code('04001'));
            $cancelReasonCode = array_merge(array('' => '=' . __('사유선택') . '='), $cancelReasonCode);
            $this->setData('cancelReason', gd_isset($cancelReasonCode));

            // 환불수단
            $refundMethodCode = gd_array_change_key_value(gd_code('04003'));
            if((int)$getData['memNo'] < 1){
                //비회원 주문건일경우 예치금환불 제거
                unset($refundMethodCode['예치금환불']);
            }
            if (substr($getData['settleKind'], 0, 1) != 'o' && substr($getData['settleKind'], 1, 1) == 'v') {
                if($getData['orderChannelFl'] != 'payco') unset($refundMethodCode['PG환불']);
            }
            $refundMethodCode = array_merge(array('' => '=' . __('환불수단 선택') . '='), $refundMethodCode);
            $this->setData('refundMethod', gd_isset($refundMethodCode));

            // 환불 계좌 은행
            $bankNmCode = gd_array_change_key_value(gd_code('04002'));
            $this->setData('bankNm', gd_isset($bankNmCode));

            // 마일리지 정책
            $mileage = gd_mileage_give_info();
            $this->setData('mileage', $mileage);

            // 마일리지 사용 정책
            $mileageUse = gd_policy('member.mileageBasic');
            $this->setData('mileageUse', $mileageUse);

            // 예치금 정책
            $depositUse = gd_policy('member.depositConfig');
            $this->setData('depositUse', $depositUse);

            // 회원 정보
            $member = App::load(\Component\Member\Member::class);
            $memInfo = $member->getMemberId($getData['memNo']);

            // 회원일 경우에만 회원정보 수집
            if(!empty($memInfo['memId'])) {
                $memData = $member->getMember($memInfo['memId'], 'memId');
            }
            $this->setData('memInfo', gd_htmlspecialchars(gd_isset($memData)));

            // 환불쿠폰 정보
            $orderCd = null;
            foreach ($getData['goods'] as $sVal) {
                foreach ($sVal as $dVal) {
                    foreach ($dVal as $gVal) {
                        $orderCd[] = $gVal['orderCd'];
                    }
                }
            }
            $orderCoupon = $order->getOrderCoupon($getValue['orderNo'], $orderCd, true);
            $this->setData('orderCoupon', $orderCoupon);

            // 결제 방법
            $settle = $order->getSettleKind();
            $this->setData('settle', $settle);

            $commonData = [
                'totalGoodsPriceText' => gd_currency_display(gd_isset($getData['totalGoodsPrice'])),
                'totalDeliveryChargeText' => gd_currency_display(gd_isset($getData['totalDeliveryCharge'])),
                'totalDcPriceText' => gd_currency_display(gd_isset($getData['totalDcPrice'])),
                'totalUseAddedPriceText' => gd_currency_display(gd_isset($getData['totalUseAddedPrice'])),
                'settlePriceText' => gd_currency_display(gd_isset($getData['settlePrice'])),
                'totalMileageText' => number_format($getData['totalMileage']).$mileageUse['unit'],
            ];
            if($getData['orderChannelFl'] == 'naverpay') {
                $commonData['settlePriceText'] = $getData['naverpay']['priceInfo'] . '<br />' . gd_currency_display(($getData['checkoutData']['orderData']['GeneralPaymentAmount']));
            }
            else {
                if (empty($getData['isDefaultMall']) === true) {
                    $commonData['settlePriceText'] += gd_global_order_currency_display(gd_isset($getData['settlePrice']), $getData['exchangeRate'], $getData['currencyPolicy']);
                }
            }
            $this->setData('commonData', $commonData);

            $orderReOrderCalculation = App::load(\Component\Order\ReOrderCalculation::class);

            $this->setData('refundData', $getData['refundData']);
            $returnData = $orderReOrderCalculation->getSelectOrderReturnData($getValue['orderNo'], 'r');
            $couponTruncPolicy = Globals::get('gTrunc.coupon');
            $mileageTruncPolicy = Globals::get('gTrunc.mileage');
            foreach ($returnData['coupon'] as $key => $val) {
                if ($val['couponUseType'] == 'product') {
                    $returnData['coupon'][$key]['couponUseType'] = '상품쿠폰';
                } else if ($val['couponUseType'] == 'order') {
                    $returnData['coupon'][$key]['couponUseType'] = '주문쿠폰';
                } else if ($val['couponUseType'] == 'delivery') {
                    $returnData['coupon'][$key]['couponUseType'] = '배송비쿠폰';
                }
                if ($val['couponPrice'] > 0) {
                    $returnData['coupon'][$key]['couponPrice'] = gd_money_format(gd_number_figure($val['couponPrice'], $couponTruncPolicy['unitPrecision'], $couponTruncPolicy['unitRound']));
                } else {
                    $returnData['coupon'][$key]['couponPrice'] = '';
                }
                if ($val['couponMileage'] > 0) {
                    $returnData['coupon'][$key]['couponMileage'] = gd_money_format(gd_number_figure($val['couponMileage'], $mileageTruncPolicy['unitPrecision'], $mileageTruncPolicy['unitRound']));
                } else {
                    $returnData['coupon'][$key]['couponMileage'] = '';
                }
            }

            $this->setData('statusMode', 'r');
            $this->setData('couponData', $returnData['coupon']);
//            $this->setData('giftData', $returnData['gift']);

            Request::get()->set('page', Request::get()->get('page', 0));
            Request::get()->set('pageNum', Request::get()->get('pageNum', 10));
            Request::get()->set('sort', Request::get()->get('sort', 'regDt DESC'));

            $requestGetParams = Request::get()->all();
            $requestGetParams['orderNo'] = $getValue['orderNo'];

            // 메모 구분
            $memoCd = $order->getOrderMemoList(true);
            $arrMemoVal = [];
            foreach($memoCd as $key => $val){
                $arrMemoVal[$val['itemCd']] = $val['itemNm'];
            }

            // 상품과 관련된 모든 데이터 가져오기
            $goodsData = $order->getOrderGoodsListToMemo($requestGetParams['orderNo']);
            // 주문 단계 설정
            $tmpStatus = gd_policy('order.status');
            foreach($goodsData as $fKey => $fVal) {
                foreach ($tmpStatus as $key => $val) {
                    if ($key != 'autoCancel') {
                        foreach ($val as $oKey => $oVal) {
                            if ($oKey == $fVal['orderStatus']){
                                $fVal['orderStatus'] = $oVal['admin'];
                                $arrGoodsData[] = $fVal;
                            }
                        }
                    }
                }
            }


            // 총 주문 정보 - 쿠폰/할인/혜택 정보
            $orderAllData = $order->getOrderView($getValue['orderNo'], null, null, null, null, null);
            foreach ($orderAllData['goods'] as $sVal) {
                foreach ($sVal as $dVal) {
                    foreach ($dVal as $gVal) {
                        // 주문 상세 - 쿠폰/할인/혜택 정보 ksort 를 위해 orderCd 기준 orderViewBenefitGoods로 재정렬
                        $orderAllData['orderViewBenefitGoods'][$gVal['orderCd']] = $gVal;
                    }
                }
            }
            // 최초 주문정보 - 쿠폰/할인/혜택 정보
            $orderReorderCalculation = \App::load('\\Component\\Order\\ReOrderCalculation');
            $originalDataCount = $orderReorderCalculation->getOrderOriginalCount($getValue['orderNo'], '', true);
            $this->setData('originalDataCount', $originalDataCount);
            // 주문 상세 - 쿠폰/할인/혜택 정보 setting
            $getBenefitData = $order->getOrderViewBenefitInfoSet($orderAllData, $originalDataCount, $orderCoupon);
            // 주문 상세 - 쿠폰/할인/혜택 정보 - 주문 당시 회원그룹 정책
            $this->setData('orderMemberPolicy', json_decode($orderAllData['memberPolicy'], true));
            // 주문 상세 - 쿠폰/할인/혜택 정보 - 주문 당시 마일리지 정책
            $this->setData('orderMileagePolicy', json_decode($orderAllData['mileagePolicy'], true));

            // 쿠폰/할인/혜택 정보 구역
            $request = \App::getInstance('request');
            $this->setData('orderAllData', gd_htmlspecialchars(gd_isset($getBenefitData)));
            $this->getView()->setDefine('layoutOrderViewBenefitInfo', $request->getDirectoryUri() . '/layout_order_view_benefit_info.php');

            // 상품º주문번호별 메모 데이터 가져오기
            $getData = $order->getAdminOrderGoodsMemoData($requestGetParams);
            $page = $order->getPage($requestGetParams, Request::getQueryString());

            $orderPolicy = gd_policy('order.basic');
            $tmp['returnStockFl'] = gd_isset($orderPolicy['r_returnStockFl'], 'n');
            $tmp['returnCouponFl'] = gd_isset($orderPolicy['r_returnCouponFl'], 'n');
            $checked['returnStockFl'][$tmp['returnStockFl']] =
            $checked['returnCouponFl'][$tmp['returnCouponFl']] = 'checked = checked';
            $this->setData('checked', gd_isset($checked));
            $this->setData('refundReconfirmFl', gd_isset($orderPolicy['refundReconfirmFl'], 'n'));
            unset($tmp);

            // 템플릿 정의(상품º주문번호별 메모관련)
            $this->setData('memoCd', $arrMemoVal);
            $this->setData('goodsData', $arrGoodsData);
            $this->setData('requestGetParams', $requestGetParams);
            $this->setData('memoData', $getData);
            $this->setData('page', $page);
            $this->setData('managerSno', Session::get('manager.sno'));
            $this->setData('myappUseFl', $orderReOrderCalculation->myappUseFl);

            // 관리자 디자인 템플릿
            $this->getView()->setDefine('layout', 'layout_blank.php');

            // 공급사 템플릿 페이지 지정
            $this->getView()->setPageName('order/refund_do_new.php');

        } catch (Exception $e) {
            throw $e;
        }
    }
}
