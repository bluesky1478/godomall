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
 * @link      http://www.godo.co.kr
 */

namespace Bundle\Controller\Admin\Order;

use App;
use Component\Godo\NaverPayAPI;
use Component\Naver\NaverPay;
use Component\Payment\CashReceipt;
use Exception;
use Framework\Debug\Exception\AlertBackException;
use Framework\Utility\ArrayUtils;
use Framework\Utility\DateTimeUtils;
use Globals;
use Session;
use Request;

/**
 * 주문 상세 페이지
 * [관리자 모드] 주문 상세 페이지
 *
 * @package Bundle\Controller\Admin\Order
 * @author  Jong-tae Ahn <qnibus@godo.co.kr>
 */
class OrderViewController extends \Controller\Admin\Controller
{
    /**
     * {@inheritdoc}
     */
    public function index()
    {
        $request = \App::getInstance('request');
        try {
            // 설정 호출
            $pgCodeConfig = App::getConfig('payment.pg');
            // 메뉴 설정
            $this->callMenu('order', 'order', 'view');

            // 공급사인 경우 입금대기, 주문취소건 제외
            $excludeStatus = null;
            if ($this->getData('isProvider')) {
                $excludeStatus = ['o', 'c'];
            }

            // 주문 정보
            $order = App::load(\Component\Order\OrderAdmin::class);
            $delivery = App::load(\Component\Delivery\Delivery::class);
            $data = $order->getOrderView($request->get()->get('orderNo'), null, null, null, $excludeStatus, null);
            $data['visitDeliveryInfo'] = $delivery->getVisitDeliveryInfo($data);

            //최초 주문정보
            $orderReorderCalculation = \App::load('\\Component\\Order\\ReOrderCalculation');
            $originalDataCount = $orderReorderCalculation->getOrderOriginalCount($data['orderNo'], '', false);
            if($originalDataCount > 0){
                //최초 주문정보
                $data['viewOriginalPrice'] = $orderReorderCalculation->getOrderViewPriceInfo($data);

                //최종 주문 정보 - 환불주문건이 있을경우 일부 데이터 계산 후 교체
                $isRecentClaimRefund = $orderReorderCalculation->getRefundClaimExistFl($data['orderNo']);
                if($isRecentClaimRefund === true){
                    $data = $orderReorderCalculation->getOrderViewPriceRefundAdjust($data);
                }
            }
            else {
                $data['viewOriginalPrice'] = $data;
            }
            $this->setData('originalDataCount', $originalDataCount);

            //묶음배송 주문번호
            if(trim($data['packetCode'])){
                $packetOrderList = $orderReorderCalculation->getPacketOrderList($data['packetCode']);
                $this->setData('packetOrderList', $packetOrderList);
            }

            // 공급사인데 상품이 없는 경우
            if (empty($data['goods']) === true) {
                throw new AlertBackException(__('조회하실 주문상품이 없습니다.'));
            }

            // 수량이 있는 탭을 먼저 체크하기 위한 설정
            $data['normalGoods']['active'] = 'none';
            $data['claimGoods']['active'] = 'none';

            $showNaverPayReload = false;    //네이버페이 상품주문 조회버튼 노출여부
            // 주문내역 종류별 상품 재 구성
            $naverPayMemo = null;
            //@formatter:off
            $normalSort = ['order' => 1, 'cancel' => 2, 'exchange' => 3, 'back' => 4, 'refund' => 5, 'fail' => 6, 'none' => 9];
            $claimSort = ['cancel' => 1, 'exchange' => 2, 'back' => 3, 'refund' => 4, 'none' => 9];
            //@formatter:on
            $naverpayIndividualCustomUniqueCode = null;   //네이버페이 개인통관 고유번호

            $syncNaverpayApiOrderNo = false;    //네이버페이 주문번호 동기화여부
            if ($data['orderChannelFl'] == 'naverpay' && empty($data['apiOrderNo']) === false) {
                $syncNaverpayApiOrderNo = true;
            }

            $isEmptyNaverpayApiOrderGoodsNo = false;    //네이버페이 주문상품번호 누락여부
            $syncApiOrderGoodsNo = null;    //동기화된 네이버페이 주문상품번호
            $arrStatus = [];
            $naverpayDelayOrderGoodsNo = null;  //의무배송일이 지난 네이버페이 주문상품번호
            foreach ($data['goods'] as $sKey => $sVal) {
                foreach ($sVal as $dKey => $dVal) {
                    foreach ($dVal as $key => $val) {
                        if ($val['orderChannelFl'] == 'naverpay') {
                            $naverpayIndividualCustomUniqueCode = $naverpayIndividualCustomUniqueCode ? $naverpayIndividualCustomUniqueCode : $val['checkoutData']['orderGoodsData']['IndividualCustomUniqueCode'];
                            if (!empty($val['checkoutData']['orderGoodsData']['ShippingMemo'])) {
                                $naverPayMemo[] = [
                                    'optionName'=>$val['checkoutData']['orderGoodsData']['ProductOption'],
                                    'memo'=>$val['checkoutData']['orderGoodsData']['ShippingMemo'],
                                ];
                            }

                            if (in_array(substr($val['orderStatus'], 0, 1), ['o', 'p']) && $val['checkoutData']['reload']!='y') {
                                $showNaverPayReload = true;
                            }

                            //상품준비중이고 현재시간이 더 크면
                            if($val['apiOrderGoodsNo'] && $val['orderStatus'] == 'g1') {
                                $diffDateTimeMin = DateTimeUtils::intervalDay($val['modDt'],date('Y-m-d H:i:s'), 'min');
                                if((strtotime(date('Y-m-d H:i:s')) > strtotime($val['checkoutData']['orderGoodsData']['ShippingDueDate'])) &&($diffDateTimeMin>=10)){
                                    $naverpayDelayOrderGoodsNo[] = $val['apiOrderGoodsNo'];
                                }
                            }

                            if ($syncNaverpayApiOrderNo === true) {
                                if ($val['apiOrderGoodsNo']) {
                                    $syncApiOrderGoodsNo[] = $val['apiOrderGoodsNo'];
                                } else {
                                    $diffDateTimeMin = DateTimeUtils::intervalDay($val['modDt'],date('Y-m-d H:i:s'), 'min');
                                    if($diffDateTimeMin>=5) {   //주문상품 수정시간 5분이상된것만 동기화
                                        $isEmptyNaverpayApiOrderGoodsNo = true;
                                    }
                                }
                            }
                        }

                        // 정상주문 탭에 들어갈 리스트 수량 설정
                        // 주문내역 상품리스트
                        if (in_array(substr($val['orderStatus'], 0, 1), ['o', 'p', 'g', 'd', 's'])) {
                            $data['normalGoods']['ordercnt']['orderGoodsCnt'] += 1;
                            if ($normalSort[$data['normalGoods']['active']] > $normalSort['order']) {
                                $data['normalGoods']['active'] = 'order';
                            }
                        }
                        // 취소내역 상품리스트
                        if (in_array(substr($val['orderStatus'], 0, 1), ['c'])) {
                            $data['normalGoods']['cancelcnt']['orderGoodsCnt'] += 1;
                            if ($normalSort[$data['normalGoods']['active']] > $normalSort['cancel']) {
                                $data['normalGoods']['active'] = 'cancel';
                            }
                        }

                        if (in_array(substr($val['orderStatus'], 0, 1), ['e'])) {
                            // 교환내역 상품리스트
                            $data['normalGoods']['exchangecnt']['orderGoodsCnt'] += 1;
                            if ($normalSort[$data['normalGoods']['active']] > $normalSort['exchange']) {
                                $data['normalGoods']['active'] = 'exchange';
                            }
                        }
                        // 반품내역 상품리스트
                        if (in_array(substr($val['orderStatus'], 0, 1), ['b'])) {
                            $data['normalGoods']['backcnt']['orderGoodsCnt'] += 1;
                            if ($normalSort[$data['normalGoods']['active']] > $normalSort['back']) {
                                $data['normalGoods']['active'] = 'back';
                            }
                        }
                        // 환불내역 상품리스트
                        if (in_array(substr($val['orderStatus'], 0, 1), ['r'])) {
                            $data['normalGoods']['refundcnt']['orderGoodsCnt'] += 1;
                            if ($normalSort[$data['normalGoods']['active']] > $normalSort['refund']) {
                                $data['normalGoods']['active'] = 'refund';
                            }
                        }
                        // 결제 중단/실패 내역 상품리스트
                        if (in_array(substr($val['orderStatus'], 0, 1), ['f'])) {
                            $data['normalGoods']['failcnt']['orderGoodsCnt'] += 1;
                            if ($normalSort[$data['normalGoods']['active']] > $normalSort['fail']) {
                                $data['normalGoods']['active'] = 'fail';
                            }
                        }

                        // 클래임 탭에 들어갈 리스트 수량 설정
                        // 클래임 탭에 취소정보 상품리스트
                        if (!$this->getData('isProvider')) {
                            if (in_array(substr($val['orderStatus'], 0, 1), ['c'])) {
                                $data['claimGoods']['cancelcnt']['orderGoodsCnt'] += 1;
                                if ($claimSort[$data['claimGoods']['active']] > $claimSort['cancel']) {
                                    $data['claimGoods']['active'] = 'cancel';
                                }
                            }
                        }

                        if ($val['handleSno'] > 0) {
                            // 교환내역 상품리스트
                            if (in_array($val['handleMode'], ['e'])) {
                                $data['claimGoods']['exchangecnt']['orderGoodsCnt'] += 1;
                                if ($claimSort[$data['claimGoods']['active']] > $claimSort['exchange']) {
                                    $data['claimGoods']['active'] = 'exchange';
                                }
                            }
                            // 반품내역 상품리스트
                            if (in_array($val['handleMode'], ['b'])) {
                                $data['claimGoods']['backcnt']['orderGoodsCnt'] += 1;
                                if ($claimSort[$data['claimGoods']['active']] > $claimSort['back']) {
                                    $data['claimGoods']['active'] = 'back';
                                }
                            }
                            // 환불내역 상품리스트
                            if (in_array($val['handleMode'], ['r'])) {
                                $data['claimGoods']['refundcnt']['orderGoodsCnt'] += 1;
                                if ($claimSort[$data['claimGoods']['active']] > $claimSort['refund']) {
                                    $data['claimGoods']['active'] = 'refund';
                                }
                            }
                        }

                        // 환불예정금액이 있을경우 대쉬보드에서 사용할 handle sno를 지정
                        if(in_array($val['orderStatus'], ['r1', 'r2']) && (int)$val['handleSno'] > 0){
                            $data['dashBoardRefundHandleSno'] = $val['handleSno'];
                        }

                        // TODO: 주문상태가 입금대기 or 결제완료 or 상품준비중 상태이면서 + 발행상태가 발급요청이 아니고 + 클레임상태(취소, 상품추가, 추가할인, 취소복원, 환불)가 존재할때 "현금영수증 재발급" 버튼 노출
                        // 현금영수증 신청여부 확인
                        $orderStatusChk = false;
                        if($data['receiptFl'] == 'r'){
                            $handleDate = strtotime($val['handleRegDt']);
                            $ordStatus = substr($data['orderStatus'], 0, 1);    // 주문기준 주문상태값
                            $ordGoodsStatus = substr($val['orderStatus'], 0, 1);    // 상품주문기준 주문상태값

                            $cashReceipt = new CashReceipt();
                            $res = $cashReceipt->cashReceiptButtonChk($data['orderNo']);
                            $cashReceiptDate = strtotime($res['regDt']);

                            // 상품 수량 부분환불처리시 이전 주문상태값으로 인식해야함
                            if(($val['beforeStatusStr'] && $ordStatus == 'r')){
                                $ordStatus = substr($val['beforeStatus'], 0, 1);
                            }

                            // 재발급되었으며 발행완료 되었다면 버튼 미노출 or pg자동발급시에 미노출
                            if((empty($res) === false && $cashReceiptDate > $handleDate) || $data['cash']['issueMode'] == 'p') {
                                $orderStatusChk = false;
                            }else {
                                // 주문건의 주문상태가 입금대기 또는 결제완료, 상품준비중인경우(주문번호 기준)
                                if ($ordStatus == 'o') {
                                    // 주문건의 현금영수증 발급상태가 발급요청이 아닌경우
                                    if ($data['cash']['statusFl'] != 'r') {
                                        if ($data['settlePrice'] != 0) {
                                            if ($ordGoodsStatus == 'c' || $val['enuri'] > 0) {
                                                $orderStatusChk = true;
                                                if ($orderStatusChk === true) {
                                                    $this->setData('orderStatusChk', $orderStatusChk);
                                                }
                                            }
                                        }
                                    }
                                } else if ($ordStatus == 'p' || $ordStatus == 'g' || $ordStatus == 'd' || $ordStatus == 's') {
                                    // 각각의 상품의 주문상태가 클레임 상태인지 체크(부분/전체취소, 부분/전체환불)
                                    if ($val['orderStatus'] == 'r3') {
                                        $orderStatusChk = true;
                                        if ($orderStatusChk === true) {
                                            $this->setData('orderStatusChk', $orderStatusChk);
                                        }
                                    }
                                }
                            }
                        }
                    }
                }

            }

            try {   //네이버페이 누락된 주문 동기화(실패해도 무시)
                if($data['orderChannelFl'] == 'naverpay') {
                    $naverPayAPI = new NaverPayAPI();
                    if ($naverpayDelayOrderGoodsNo) {   //의무배송일이 지난 주문건들 동기화
                        $naverPayAPI->request('GetProductOrderInfoList', ['ProductOrderIDList' => implode(',', $naverpayDelayOrderGoodsNo)]);
                    }

                    if ($isEmptyNaverpayApiOrderGoodsNo === true) {
                        $naverPayOrderList = $naverPayAPI->request('GetProductOrderIDList', ['OrderID' => $data['apiOrderNo']]);
                        if ($naverPayOrderList['result']) {
                            if (!$syncApiOrderGoodsNo) {
                                $missingApiOrderGoodsNo = $naverPayOrderList['data']['ProductOrderIDList'];
                            } else {
                                $missingApiOrderGoodsNo = array_diff($naverPayOrderList['data']['ProductOrderIDList'], $syncApiOrderGoodsNo);
                            }
                            if ($missingApiOrderGoodsNo) {
                                $naverPayAPI->request('GetProductOrderInfoList', ['ProductOrderIDList' => implode(',', $missingApiOrderGoodsNo)]);
                            }
                        }
                    }
                }
            } catch (\Throwable $e) {
            }

            $referer = $request->getReferer();
            $activeTabRefererList = [
                'cancel'   => 'order_list_cancel',
                'exchange' => 'order_list_exchange',
                'back'     => 'order_list_back',
                'refund'   => 'order_list_refund',
                'fail'     => 'order_list_fail',
            ];
            foreach ($activeTabRefererList as $tab => $uri) {
                if (strpos($referer, $uri) > 0) {
                    $data['normalGoods']['active'] = $tab;
                }
            }

            // 선택된 탭이 없으면 기본
            if ($data['normalGoods']['active'] === 'none') {
                $data['normalGoods']['active'] = 'order';
            }
            if ($data['claimGoods']['active'] === 'none') {
                if ($this->getData('isProvider')) {
                    $data['claimGoods']['active'] = 'exchange';
                } else {
                    $data['claimGoods']['active'] = 'cancel';
                }
            }

            // 네이버페이 메모
            if ($naverPayMemo) {
                $this->setData('naverPayMemo', $naverPayMemo);
            }

            if($naverpayIndividualCustomUniqueCode) {
                $this->setData('naverpayIndividualCustomUniqueCode', $naverpayIndividualCustomUniqueCode);
            }

            $paycoData = empty($data['fintechData']) === false ? 'fintechData' : 'checkoutData';
            if (is_array($data[$paycoData]) === false) $data[$paycoData] = json_decode($data[$paycoData], true);

            if ($data[$paycoData]['individualCustomUniqNo']) {
                $this->setData('paycoIndividualCustomUniqNo', $data[$paycoData]['individualCustomUniqNo']);
            }

            // 회원 정보
            $member = App::load(\Component\Member\Member::class);
            $memInfo = $member->getMemberId($data['memNo']);
            $this->setData('memInfo', gd_htmlspecialchars(gd_isset($memInfo)));

            // 세금정보
            $tax = App::load(\Component\Order\Tax::class);
            //$this->setData('tax', $tax);

            // 세금계산서 사용여부
            $taxInfo = $tax->getTaxConf();
            $this->setData('taxInfo', gd_isset($taxInfo));

            // 국가데이터 가져오기
            $countriesCode = $order->getCountriesList();

            // 전화용 국가코드 셀렉트 박스 데이터
            $countryPhone = [];
            foreach ($countriesCode as $key => $val) {
                if ($val['callPrefix'] > 0) {
                    $countryPhone[$val['code']] = $val['countryNameKor'] . '(' . $val['countryName'] . ') +' . $val['callPrefix'];
                }
            }
            $this->setData('countryPhone', $countryPhone);

            // 주소용 국가코드 셀렉트 박스 데이터
            $countryAddress = [];
            foreach ($countriesCode as $key => $val) {
                $countryAddress[$val['code']] = $val['countryNameKor'] . '(' . $val['countryName'] . ')';
            }
            $this->setData('countryAddress', $countryAddress);

            // 영수증 신청 가능여부 (기준몰인 경우만 신청 가능)
            $receipt['taxFl'] = $receipt['cashFl'] = 'n';
            if (in_array($data['settleKind'], $order->settleKindReceiptPossible) === true && in_array($data['statusMode'], $order->statusReceiptPossible) === true && empty($data['isDefaultMall']) === false) {
                // 세금 계산서 사용 여부
                $taxInfo = gd_policy('order.taxInvoice');
                if (gd_isset($taxInfo['taxInvoiceUseFl']) == 'y' && (gd_isset($taxInfo['gTaxInvoiceFl']) == 'y' || gd_isset($taxInfo['eTaxInvoiceFl']) == 'y')) {
                    $receipt['taxFl'] = 'y';
                }

                // 현금 영수증 사용 여부
                $pgConf = gd_pgs();
                if (empty($pgConf['pgId']) === false && $pgConf['cashReceiptFl'] == 'y') {
                    // 기간 체크
                    gd_isset($pgConf['cashReceiptPeriod'], '3');

                    // 현금영수증 관련 설정값
                    $receipt['cashFl'] = 'y';
                    $receipt['periodFl'] = 'y';
                    $receipt['periodDay'] = $pgConf['cashReceiptPeriod'];

                    // 기간체크후 안내
                    if ($data['statusMode'] !== 'o') {
                        $checkDate = date('Ymd', strtotime('-' . $pgConf['cashReceiptPeriod'] . ' day'));
                        $paymentDate = gd_date_format('Ymd', $data['paymentDt']);
                        if ($paymentDate < $checkDate) {
                            $receipt['periodFl'] = 'n';
                        }
                    }
                }
            }
            $this->setData('receipt', gd_isset($receipt));

            // 취소사유
            if ($data['orderChannelFl'] == 'naverpay') {
                $naverPay = new NaverPay();
                $cancelReasonCode = $naverPay->getClaimReasonCode(null, 'cancel');
                $cancelReasonCode = array_merge(['' => '=' . __('사유선택') . '='], $cancelReasonCode);

                $backReasonCode = $naverPay->getClaimReasonCode(null, 'back');
                $backReasonCode = array_merge(['' => '=' . __('사유선택') . '='], $backReasonCode);
                $this->setData('backReason', gd_isset($backReasonCode));
            } else {
                $cancelReasonCode = gd_array_change_key_value(gd_code('04001'));
                $cancelReasonCode = array_merge(['' => '=' . __('사유선택') . '='], $cancelReasonCode);
                $this->setData('backReason', gd_isset($cancelReasonCode));
            }
            $this->setData('refundReason', gd_isset($cancelReasonCode));

            // 환불수단
            $refundMethodCode = gd_array_change_key_value(gd_code('04003'));
            $refundMethodCode = array_merge(['' => '=' . __('환불수단 선택') . '='], $refundMethodCode);
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

            // 결제 방법
            $settle['name'] = $order->getSettleKind($data['settleKind']);
            $settle['prefix'] = substr($data['settleKind'], 0, 1);
            $settle['suffix'] = substr($data['settleKind'], 1, 1);

            // 영수증 출력 정보 세팅 (PG 거래 영수증 - 현금영수증 제외)
            switch ($settle['suffix']) {
                case 'c':
                    $settle['settleReceipt'] = 'card';
                    break;
                case 'b':
                    $settle['settleReceipt'] = 'bank';
                    break;
                case 'v':
                    $settle['settleReceipt'] = 'vbank';
                    break;
                case 'h':
                    $settle['settleReceipt'] = 'hphone';
                    break;
                case 'p':
                    $settle['settleReceipt'] = 'point';
                    break;
                default:
                    $settle['settleReceipt'] = '';
                    break;
            }
            if (empty($settle['settleReceipt']) === false && isset($pgCodeConfig->getPgReceiptUrl()[$data['pgName']][$settle['settleReceipt']]) === false) {
                $settle['settleReceipt'] = '';
            }
            $this->setData('settle', gd_htmlspecialchars(gd_isset($settle)));

            // 주문 접수, 취소, 실패 주문의 경우 일괄처리에 주문만 나오게 처리
            $orderExcludeCode = ArrayUtils::searchByValue($order->standardExcludeCd, 'o');
            $this->setData('orderExcludeCode', gd_isset($orderExcludeCode));

            // 클래임 처리 가능 상품 설정
            foreach ($data['goods'] as $sKey => $sVal) {
                foreach ($sVal as $dKey => $dVal) {
                    foreach ($dVal as $key => $val) {
                        // 취소가능 상품리스트
                        if ($val['canCancel'] == true) {
                            $data['claimGoods']['cancel'][$sKey][$dKey][] = $val;
                            $data['claimGoods']['cancelcnt']['scm'][$sKey] += 1 + $val['addGoodsCnt'];
                            $data['claimGoods']['cancelcnt']['delivery'][$dKey] += 1 + $val['addGoodsCnt'];
                            $data['claimGoods']['cancelcnt']['goods']['all'] += 1;
                        }
                        // 환불가능 상품리스트
                        if ($val['canRefund'] == true) {
                            $data['claimGoods']['refund'][$sKey][$dKey][] = $val;
                            $data['claimGoods']['refundcnt']['scm'][$sKey] += 1 + $val['addGoodsCnt'];
                            $data['claimGoods']['refundcnt']['delivery'][$dKey] += 1 + $val['addGoodsCnt'];
                            $data['claimGoods']['refundcnt']['goods']['all'] += 1;
                        }
                        // 반품가능 상품리스트
                        if ($val['canBack'] == true) {
                            $data['claimGoods']['back'][$sKey][$dKey][] = $val;
                            $data['claimGoods']['backcnt']['scm'][$sKey] += 1 + $val['addGoodsCnt'];
                            $data['claimGoods']['backcnt']['delivery'][$dKey] += 1 + $val['addGoodsCnt'];
                            $data['claimGoods']['backcnt']['goods']['all'] += 1;
                        }
                        // 교환가능 상품리스트
                        if ($val['canExchange'] == true) {
                            $data['claimGoods']['exchange'][$sKey][$dKey][] = $val;
                            $data['claimGoods']['exchangecnt']['scm'][$sKey] += 1 + $val['addGoodsCnt'];
                            $data['claimGoods']['exchangecnt']['delivery'][$dKey] += 1 + $val['addGoodsCnt'];
                            $data['claimGoods']['exchangecnt']['goods']['all'] += 1;
                        }

                        // 주문 상세 - 쿠폰/할인/혜택 정보 ksort 를 위해 orderCd 기준 orderViewBenefitGoods로 재정렬
                        $data['orderViewBenefitGoods'][$val['orderCd']] = $val;
                    }
                }
            }

            // 주문 추가 필드 정보
            $addFieldData = $order->getOrderAddFieldView($data['addField']);

            // 해외 배송 무게 정보
            $overseasDeliveryWeight = reset(reset(reset($data['goods'])));
            $data['deliveryWeightInfo'] = json_decode($overseasDeliveryWeight['deliveryWeightInfo'], true);

            // 주문 상세 - 쿠폰/할인/혜택 정보 setting
            $data = $order->getOrderViewBenefitInfoSet($data, $originalDataCount);
            // 주문 상세 - 쿠폰/할인/혜택 정보 - 주문 당시 회원그룹 정책
            $this->setData('orderMemberPolicy', json_decode($data['memberPolicy'], true));
            // 주문 상세 - 쿠폰/할인/혜택 정보 - 주문 당시 마일리지 정책
            $this->setData('orderMileagePolicy', json_decode($data['mileagePolicy'], true));
            // 주문 상세 - 쿠폰/할인/혜택 정보 - 주문 당시 마이앱 추가 혜택 정책
            if (empty($data['myappPolicy']) === false) {
                $this->setData('myappPolicy', json_decode($data['myappPolicy'], true));
            }

            // 주문유형
            if ($data['orderTypeFl'] == 'pc') {
                $data['orderTypeFlNm'] = 'PC쇼핑몰';
            } else if ($data['orderTypeFl'] == 'mobile') {
                if (empty($data['appOs']) === true && empty($data['pushCode']) === true) {
                    $data['orderTypeFlNm'] = '모바일쇼핑몰(WEB)';
                } else {
                    $data['orderTypeFlNm'] = '모바일쇼핑몰(APP)';
                }
            } else {
                $data['orderTypeFlNm'] = '수기주문';
            }

            // 템플릿 데이터 설정
            $this->setData('order', $order);
            if ($data['orderChannelFl'] == 'naverpay') {
                $this->setData('data', gd_isset($data));
            } else {
                $this->setData('data', gd_htmlspecialchars(gd_isset($data)));
            }

            $this->setData('addFieldData', gd_isset($addFieldData));
            $this->setData('orderStatusCode', gd_isset($orderStatusCode));
            $this->setData('_delivery', Globals::get('gDelivery'));
            $this->setData('showNaverPayReload', $showNaverPayReload);

            if (empty($data['pgName']) === false && $settle['prefix'] == 'e') {
                $this->setData('pgEscrowConf', gd_isset($pgCodeConfig->getPgEscrowConf()[$data['pgName']]));
            }

            // 메일도메인
            $emailDomain = gd_array_change_key_value(gd_code('01004'));
            $emailDomain = array_merge(['self' => __('직접입력')], $emailDomain);
            $this->setData('emailDomain', $emailDomain); // 메일주소 리스팅

            if (preg_match('/MSIE\s(?P<v>\d+)/i', \Request::server()->get('HTTP_USER_AGENT'), $ieCheck) && $ieCheck['v'] <= 8) {
                $ieRowRank = true;
            }
            else {
                $ieRowRank = false;
            }
            $this->setData('ieRowRank', $ieRowRank);

            // 안심번호 사용여부
            $orderBasic = gd_policy('order.basic');
            if (isset($orderBasic['safeNumberServiceFl']) && $orderBasic['safeNumberServiceFl'] == 'off') {
                $safeNumberFl = $orderBasic['safeNumberServiceFl'];
            } else {
                $safeNumberFl = $orderBasic['safeNumberFl'];
            }
            $this->setData('safeNumberFl', $safeNumberFl);

            // --- 템플릿 정의
            $this->getView()->setDefine('layoutOrderGoodsList', $request->getDirectoryUri() . '/layout_order_claim_list.php');// 리스트폼
            if ($request->get()->get('popupMode', '') === 'yes') {
                $this->getView()->setDefine('layout', 'layout_blank.php');
            }

            //쿠폰/할인/혜택 정보 구역
            $this->getView()->setDefine('layoutOrderViewBenefitInfo', $request->getDirectoryUri() . '/layout_order_view_benefit_info.php');
            //최초 결제정보 구역
            $this->getView()->setDefine('layoutOrderViewSettleFirstInfo', $request->getDirectoryUri() . '/layout_order_view_settle_first_info.php');
            //최종 결제정보 구역
            $this->getView()->setDefine('layoutOrderViewSettleLastInfo', $request->getDirectoryUri() . '/layout_order_view_settle_last_info.php');
            //주문자 정보 구역
            $this->getView()->setDefine('layoutOrderViewOrderInfo', $request->getDirectoryUri() . '/layout_order_view_order_info.php');
            //주문자 정보 수정 구역
            $this->getView()->setDefine('layoutOrderViewOrderInfoModify', $request->getDirectoryUri() . '/layout_order_view_order_info_modify.php');
            //수령자 정보 구역
            $this->getView()->setDefine('layoutOrderViewReceiverInfo', $request->getDirectoryUri() . '/layout_order_view_receiver_info.php');
            //수령자 정보 수정 구역
            $this->getView()->setDefine('layoutOrderViewReceiverInfoModify', $request->getDirectoryUri() . '/layout_order_view_receiver_info_modify.php');

            $this->addScript(
                [
                    'sms.js',
                ]
            );

            Request::get()->set('page', Request::get()->get('page', 0));
            Request::get()->set('pageNum', Request::get()->get('pageNum', 10));
            Request::get()->set('sort', Request::get()->get('sort', 'regDt DESC'));

            $requestGetParams = Request::get()->all();
            $requestGetParams['orderNo'] = $data['orderNo'];

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

            // 상품º주문번호별 메모 데이터 가져오기
            $getData = $order->getAdminOrderGoodsMemoData($requestGetParams);
            $page = $order->getPage($requestGetParams, Request::getQueryString());

            // 템플릿 정의(상품º주문번호별 메모관련)
            $this->setData('memoCd', $arrMemoVal);
            $this->setData('goodsData', $arrGoodsData);
            $this->setData('requestGetParams', $requestGetParams);
            $this->setData('memoData', $getData);
            $this->setData('page', $page);
            $this->setData('managerSno', Session::get('manager.sno'));
            $this->setData('channel', $order->getOrderChannel());

            // 기타주문채널 관련 display 제어
            if($data['orderChannelFl'] === 'etc'){
                $styleDisplayNone = "style='display: none;'";
            }
            $this->setData('styleDisplayNone', gd_isset($styleDisplayNone, ''));

            // 공급사와 동일한 페이지 사용
            $this->getView()->setPageName('order/order_view.php');
        } catch (Exception $e) {
            throw $e;
        }
    }
}
