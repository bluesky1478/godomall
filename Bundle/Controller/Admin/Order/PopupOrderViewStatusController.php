<?php

/**
 * This is commercial software, only users who have purchased a valid license
 * and accept to the terms of the License Agreement can install and use this
 * program.
 *
 * Do not edit or add to this file if you wish to upgrade Godomall5 to newer
 * versions in the future.
 *
 * @copyright ⓒ 2017, NHN godo: Corp.
 * @link      http://www.godo.co.kr
 */

namespace Bundle\Controller\Admin\Order;

use App;
use Exception;
use Component\Naver\NaverPay;
use Framework\Debug\Exception\AlertCloseException;
use Session;

/**
 * Class PopupOrderViewStatusController
 *
 * @package Bundle\Controller\Admin\Order
 * @author <bumyul2000@godo.co.kr>
 */
class PopupOrderViewStatusController extends \Controller\Admin\Controller
{
    /**
     * {@inheritdoc}
     */
    public function index()
    {
        $request = \App::getInstance('request');
        try {

            // 운영자 기능권한의 게시글 삭제 권한 없음 - 관리자페이지에서만
            $thisCallController = \App::getInstance('ControllerNameResolver')->getControllerRootDirectory();
            if ($thisCallController == 'admin' && Session::get('manager.functionAuthState') == 'check' && Session::get('manager.functionAuth.orderState') != 'y') {
                throw new AlertCloseException(__('권한이 없습니다. 권한은 대표운영자에게 문의하시기 바랍니다.'));
            }

            // 마일리지 사용 정책
            $mileageUse = gd_policy('member.mileageBasic');

            // 주문 정보
            $order = App::load(\Component\Order\OrderAdmin::class);
            $data = $order->getOrderView($request->get()->get('orderNo'));
            $orderViewStatusActionList = $order->getOrderViewStatusActionList();
            $subject = $orderViewStatusActionList[$request->get()->get('actionType')];
            $fileNameFix = strtolower(preg_replace('/([A-Z])/', "$1", $request->get()->get('actionType')));

            //공통 결제정보
            //@todo 최종결제정보가 있으면 최종결제정보로 출력되어야 한다.
            $commonData = [
                'totalGoodsPriceText' => gd_currency_display(gd_isset($data['totalGoodsPrice'])),
                'totalDeliveryChargeText' => gd_currency_display(gd_isset($data['totalDeliveryCharge'])),
                'totalDcPriceText' => gd_currency_display(gd_isset($data['totalDcPrice'])),
                'totalUseAddedPriceText' => gd_currency_display(gd_isset($data['totalUseAddedPrice'])),
                'settlePriceText' => gd_currency_display(gd_isset($data['settlePrice'])),
                'totalMileageText' => number_format($data['totalMileage']) . $mileageUse['unit'],
            ];
            if ($data['orderChannelFl'] == 'naverpay') {
                $commonData['settlePriceText'] = $data['naverpay']['priceInfo'] . '<br />' . gd_currency_display(($data['checkoutData']['orderData']['GeneralPaymentAmount']));
            }

            switch ($request->get()->get('actionType')) {
                //상품추가
                case 'add' :
                    // 추가는 입금대기만 가능
                    if ($this->getData('isProvider')) {
                        $checkStatus = [''];
                    } else {
                        $checkStatus = ['o'];
                    }

                    if($data['useMultiShippingKey'] === true){
                        $multiShippingInfoCdList = implode(INT_DIVISION, array_column($data['multiShippingList'], 'orderInfoCd'));
                        $this->setData('multiShippingInfoCdList', $multiShippingInfoCdList);
                    }

                    // 페이지 접속시 사이트키 재 생성
                    gd_regenerate_site_key();
                    break;

                //주문취소
                case 'cancel' :
                    // 취소사유
                    if ($data['orderChannelFl'] == 'naverpay') {
                        $naverPay = new NaverPay();
                        $cancelReasonCode = $naverPay->getClaimReasonCode(null, 'cancel');
                        $cancelReasonCode = array_merge(['' => '=' . __('사유선택') . '='], $cancelReasonCode);
                    } else {
                        $cancelReasonCode = gd_array_change_key_value(gd_code('04001'));
                        $cancelReasonCode = array_merge(['' => '=' . __('사유선택') . '='], $cancelReasonCode);
                    }
                    $this->setData('refundReason', gd_isset($cancelReasonCode));

                    // 취소는 입금대기만 가능
                    if ($this->getData('isProvider')) {
                        $checkStatus = [''];
                    } else {
                        $checkStatus = ['o'];
                    }
                    break;

                //상품환불접수
                case 'refund' :
                    // 환불사유
                    if ($data['orderChannelFl'] == 'naverpay') {
                        $naverPay = new NaverPay();
                        $cancelReasonCode = $naverPay->getClaimReasonCode(null, 'cancel');
                        $cancelReasonCode = array_merge(['' => '=' . __('사유선택') . '='], $cancelReasonCode);
                    } else {
                        $cancelReasonCode = gd_array_change_key_value(gd_code('04001'));
                        $cancelReasonCode = array_merge(['' => '=' . __('사유선택') . '='], $cancelReasonCode);
                    }
                    $this->setData('refundReason', gd_isset($cancelReasonCode));

                    $refundMethodCode = gd_array_change_key_value(gd_code('04003'));
                    if((int)$data['memNo'] < 1){
                        //비회원 주문건일경우 예치금환불 제거
                        unset($refundMethodCode['예치금환불']);
                    }
                    if (substr($data['settleKind'], 0, 1) != 'o' && substr($data['settleKind'], 1, 1) == 'v') {
                        if($data['orderChannelFl'] != 'payco') unset($refundMethodCode['PG환불']);
                    }
                    $refundMethodCode = array_merge(['' => '=' . __('환불수단 선택') . '='], $refundMethodCode);
                    $this->setData('refundMethod', gd_isset($refundMethodCode));

                    // 환불 계좌 은행
                    $bankNmCode = gd_array_change_key_value(gd_code('04002'));
                    $this->setData('bankNm', gd_isset($bankNmCode));

                    // 환불은 결제완료, 상품준비중만 가능
                    if ($this->getData('isProvider')) {
                        $checkStatus = ['p', 'g'];
                    } else {
                        $checkStatus = ['p', 'g'];
                    }
                    break;

                //상품교환
                case 'exchange' :
                    $goodsObj = \App::load('\\Component\\Goods\\Goods');
                    $addGoods = \App::load('\\Component\\Goods\\AddGoodsAdmin');

                    if ($data['orderChannelFl'] == 'naverpay') {
                        $naverPay = new NaverPay();
                        $cancelReasonCode = $naverPay->getClaimReasonCode(null, 'cancel');
                        $cancelReasonCode = array_merge(['' => '=' . __('사유선택') . '='], $cancelReasonCode);
                    } else {
                        $cancelReasonCode = gd_array_change_key_value(gd_code('04001'));
                        $cancelReasonCode = array_merge(['' => '=' . __('사유선택') . '='], $cancelReasonCode);
                    }
                    $this->setData('refundReason', gd_isset($cancelReasonCode));

                    // 페이지 접속시 사이트키 재 생성
                    gd_regenerate_site_key();

                    $checkStatus = ['p', 'd', 's', 'g'];
                    break;

                //교환철회
                case 'exchangeCancel' :
                    break;

                //상품반품
                case 'back' :
                    // 반품은 배송중,배송완료,구매확정만 가능
                    if ($this->getData('isProvider')) {
                        $checkStatus = ['d', 's'];
                    } else {
                        $checkStatus = ['d', 's'];
                    }

                    // 반품사유
                    if ($data['orderChannelFl'] == 'naverpay') {
                        $naverPay = new NaverPay();
                        $backReasonCode = $naverPay->getClaimReasonCode(null, 'back');
                        $backReasonCode = array_merge(['' => '=' . __('사유선택') . '='], $backReasonCode);
                        $this->setData('backReason', gd_isset($backReasonCode));
                    } else {
                        $backReasonCode = gd_array_change_key_value(gd_code('04001'));
                        $backReasonCode = array_merge(['' => '=' . __('사유선택') . '='], $backReasonCode);
                    }
                    $this->setData('backReason', gd_isset($backReasonCode));

                    $refundMethodCode = gd_array_change_key_value(gd_code('04003'));
                    $refundMethodCode = array_merge(['' => '=' . __('환불수단 선택') . '='], $refundMethodCode);
                    $this->setData('refundMethod', gd_isset($refundMethodCode));

                    // 환불 계좌 은행
                    $bankNmCode = gd_array_change_key_value(gd_code('04002'));
                    $this->setData('bankNm', gd_isset($bankNmCode));
                    break;

                //반품철회
                case 'backCancel' :
                    break;

                //환불철회
                case 'refundCancel' :
                    break;

                //환불완료
                case 'refundComplete' :
                    break;
            }

            $goods = $data['goods'];
            unset($data['goods'], $data['cnt']);

            // 주문내역 종류별 상품 재 구성
            foreach ($goods as $sKey => $sVal) {
                foreach ($sVal as $dKey => $dVal) {
                    foreach ($dVal as $key => $val) {
                        $displayFl = false;
                        if($request->get()->get('actionType') === 'exchangeCancel'){
                            if($val['handleMode'] === 'z'){
                                $displayFl = true;
                            }
                        }
                        else {
                            // 교환처리시 옵션이 있는 상품 확인 후 다른 옵션 정보를 저장해둔다. (동일상품교환시를 위함)
                            if($request->get()->get('actionType') === 'exchange'){
                                if(count($val['optionInfo']) > 0 && (int)$val['goodsNo'] > 0){
                                    $newOptionInfoArr = $goodsOptionInfo = $addGoodsData = [];

                                    if($val['goodsType'] === 'addGoods'){
                                        //추가상품
                                        $addGoodsData = $addGoods->getInfoAddGoods($val['goodsNo']);
                                        $newOptionInfoArr[] = $addGoodsData['addGoodsNo'] . INT_DIVISION . $addGoodsData['optionNm'];
                                    }
                                    else {
                                        //본상품

                                        //optionPrice 옵션값
                                        $goodsOptionInfo = $goodsObj->getGoodsOption($val['goodsNo']);

                                        if(count($goodsOptionInfo) > 0){
                                            foreach($goodsOptionInfo as $optKey => $optVal){
                                                if($optVal['optionSellFl'] == 'n' || $optVal['optionSellFl'] == 't') continue;
                                                if(isset($optVal['optionPrice']) && (int)$optVal['optionPrice'] === (int)$val['optionPrice']){
                                                    if($val['stockFl'] === 'n' || ($val['stockFl'] !== 'n' && (int)$optVal['stockCnt'] > 0)){
                                                        $tmpOptArr = [];
                                                        for($i = 1; $i <= 5; $i++){
                                                            $tmpOptArr[] = $optVal['optionValue'.$i];
                                                        }
                                                        $newOptionInfoArr[] = $optVal['sno'] . INT_DIVISION . implode("/", array_filter($tmpOptArr));
                                                    }
                                                }
                                            }
                                        }
                                    }

                                    if(count($newOptionInfoArr) > 0){
                                        $val['changeAbleOrderGoodsInfo'] = htmlentities(implode(STR_DIVISION, $newOptionInfoArr));
                                        unset($newOptionInfoArr);
                                    }
                                }
                            }

                            if (in_array(substr($val['orderStatus'], 0, 1), $checkStatus)) {
                                $displayFl = true;
                            }
                        }
                        if ($displayFl === true) {
                            $data['goods'][$sKey][$dKey][] = $val;

                            // 테이블 UI 표현을 위한 변수
                            $addGoodsCnt = $val['addGoodsCnt'];
                            if($data['useMultiShippingKey']){
                                $data['cnt']['multiShipping'][$sKey] += 1 + $addGoodsCnt;
                            }
                            $data['cnt']['scm'][$sKey] += 1 + $addGoodsCnt;
                            $data['cnt']['goods']['all'] += 1 + $addGoodsCnt;
                            $data['cnt']['goods']['goods'] += 1;
                            $data['cnt']['goods']['addGoods'] += $addGoodsCnt;
                            if ($val['mallSno'] > DEFAULT_MALL_NUMBER) {
                                //$deliveryUniqueKey = $dKey;
                                $deliveryUniqueKey = $val['deliverySno'];
                            } else {
                                //$deliveryUniqueKey = $dKey . '-' . $val['orderDeliverySno'];
                                $deliveryUniqueKey = $val['deliverySno'] . '-' . $val['orderDeliverySno'];
                            }
                            $data['cnt']['delivery'][$deliveryUniqueKey] += 1 + $addGoodsCnt;
                        }
                    }
                }
            }

            $currencySymbol = gd_currency_symbol();
            $currencyString = gd_currency_string();
            $this->setData('currencySymbol', $currencySymbol);
            $this->setData('currencyString', $currencyString);

            $this->setData('order', $order);
            $this->setData('data', gd_isset($data));
            $this->setData('commonData', $commonData);
            $this->setData('subject', $subject);
            $this->setData('mileageUse', gd_isset($mileageUse));

            $this->getView()->setDefine('layoutOrderViewStatusChangeList', $request->getDirectoryUri() . '/layout_order_view_status_' . $fileNameFix . '.php');
            $this->getView()->setDefine('layout', 'layout_blank.php');
            $claimElementDisabled = '';
            if ($data['orderChannelFl'] == 'naverpay') {
                $claimElementDisabled = 'disabled';
            }
            $this->setData('claimElementDisabled', $claimElementDisabled);
            $this->setData('useMultiShippingKey', gd_isset($data['useMultiShippingKey']));

            // 공급사와 동일한 페이지 사용
            $this->getView()->setPageName('order/popup_order_view_status.php');
        } catch (Exception $e) {
            throw $e;
        }
    }
}
