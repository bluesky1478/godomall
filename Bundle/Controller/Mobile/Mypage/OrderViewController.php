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

use Component\PlusShop\PlusReview\PlusReviewArticleFront;
use Component\Board\BoardWrite;
use Component\Board\Board;
use Framework\Debug\Exception\AlertRedirectException;
use Framework\Debug\Exception\AlertBackException;
use Exception;
use Framework\Utility\DateTimeUtils;
use Framework\Utility\GodoUtils;
use Request;
use App;
use Session;

/**
 * 주문 상세 보기 페이지
 *
 * @author artherot
 * @version 1.0
 * @since 1.0
 * @copyright Copyright (c), Godosoft
 */
class OrderViewController extends \Controller\Mobile\Controller
{
    /**
     * @inheritdoc
     *
     * @throws AlertRedirectException
     */
    public function index()
    {
        try {
            $this->addScript([
                'iscroll/iscroll.js',
            ]);

            $gPageName = __('주문내역 상세보기');

            // PG 정보
            $pgCodeConfig = App::getConfig('payment.pg');
            $this->setData('pgEscrowConf', $pgCodeConfig->getPgEscrowConf());

            // 모듈 설정
            $order = \App::load('\\Component\\Order\\Order');
            $delivery = \App::load(\Component\Delivery\Delivery::class);

            // 주문 리스트 정보
            $orderData = $order->getOrderView(Request::get()->get('orderNo'));
            $orderData['visitDeliveryInfo'] = $delivery->getVisitDeliveryInfo($orderData);
            $orderDeliveryInfo = $order->getOrderDeliveryInfo(Request::get()->get('orderNo'));
            $this->setData('orderDeliveryInfo', $orderDeliveryInfo);

            if ($orderData['multiShippingFl'] == 'y') {
                $multiOrderInfo = $order->getMultiOrderInfo(Request::get()->get('orderNo'));
                $this->setData('multiOrderInfo', $multiOrderInfo);
                $this->setData('isUseMultiShipping', true);
            }

            // 회원할인에 회원 배송비 할인 추가
            $orderData['totalMemberDcPrice'] += $orderData['totalMemberDeliveryDcPrice'];
            $board = new BoardWrite(['bdId'=>Board::BASIC_GOODS_REIVEW_ID]);
            $isPlusReview = false;
            if(GodoUtils::isPlusShop(PLUSSHOP_CODE_REVIEW)){
                $plusReview = new PlusReviewArticleFront();
                $isPlusReview =  true;
                $this->addScript(['plusReview/gd_plus_review.js?popup=no']);
            }
            $orderReorderCalculation = \App::load('\\Component\\Order\\ReOrderCalculation');
            foreach($orderData['goods'] as &$orderGoods){
                $handleData = $orderReorderCalculation->getOrderHandleData(Request::get()->get('orderNo'), null, null, $orderGoods['handleSno']);
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
            // 사용자 반품/교환/환불 신청 데이터 생성
            $orderData = $order->getOrderClaimList($orderData);

            // 배송 중, 배송 완료된 상품 카운트해서 버튼 생성 여부
            $orderData = $order->getOrderSettleButton($orderData);

            $this->setData('orderData', $orderData);

            $ordersByRegisterDay = [];
            foreach ($orderData as $index => $item) {
                if (empty($item['regDt']) === false) {
                    $ordersByRegisterDay[DateTimeUtils::dateFormat('Y-m-d', $item['regDt'])][] = $item;
                    $ordersByRegisterDay[DateTimeUtils::dateFormat('Y-m-d', $item['regDt'])][$index]['orderGoodsCnt'] = count($item['goods']);
                }
            }

            $this->setData('ordersByRegisterDay', $ordersByRegisterDay);

            $orderData = $orderData[0];
            $this->setData('orderInfo', $orderData);
            $this->setData('guest', Request::get()->get('guest'));

            //개선 후 교환건이 있는 주문인지 체크
            $isExchangeHandle = false;

            // 환불 정보
            $isHandle = false;
            $refundGroupCd = 0;
            $totalRefundPrice = 0;
            $totalCompleteCashPrice = 0;
            $totalCompletePgPrice = 0;
            $totalCompleteDepositPrice = 0;
            $totalCompleteMileagePrice = 0;
            $totalRefundUseMileage = array_sum(array_column($orderData['goods'], 'refundUseMileage'));
            $totalRefundUseDeposit = array_sum(array_column($orderData['goods'], 'refundUseDeposit'));
            $totalRefundData = '';

            foreach ($orderData['goods'] as $index => $data) {
                if ($data['handleSno'] > 0 && $data['orderStatus'] == 'r3') {
                    // 환불처리 여부
                    $isHandle = true;

                    if (gd_date_format('Y-m-d H:i', $data['regDt']) < gd_date_format('Y-m-d H:i', '2019-07-10 07:40:00')) { // 기존처리
                        // 환불금액 정보
                        $totalRefundPrice += $data['refundPrice'] + $data['refundUseDeposit'] + $data['refundUseMileage'];

                        if ($refundGroupCd != $data['refundGroupCd']) {
                            $totalCompleteCashPrice += $data['completeCashPrice'];
                            $totalCompletePgPrice += $data['completePgPrice'];
                            $totalCompleteDepositPrice += $data['completeDepositPrice'] + $data['refundUseDeposit'];
                            $totalCompleteMileagePrice += $data['completeMileagePrice'];
                            $refundGroupCd = $data['refundGroupCd'];
                        } else {
                            $totalCompleteDepositPrice += $data['refundUseDeposit'];
                        }
                    } else {
                        $totalRefundPrice += $data['refundPrice'];
                        $totalCompleteCashPrice += $data['completeCashPrice'];
                        $totalCompletePgPrice += $data['completePgPrice'];
                        $totalCompleteDepositPrice += $data['completeDepositPrice'] + $data['refundUseDeposit'] + $data['refundDeliveryUseDeposit'];
                        $totalCompleteMileagePrice += $data['completeMileagePrice'];
                        $totalRefundUseMileage += $data['refundDeliveryUseMileage'];
                        $totalRefundUseDeposit += $data['refundDeliveryUseDeposit'];
                    }
                    // 환불계좌 중복 제거
                    $sameAccount = false;
                    if (is_array($totalRefundData)) {
                        foreach ($totalRefundData as $item) {
                            if ($item['bank'] == $data['refundBankName'] && $item['account'] == $data['refundAccountNumber'] && $item['depositor'] == $data['refundDepositor']) {
                                $sameAccount = true;
                            }
                        }

                        if ($sameAccount === false) {
                            $totalRefundData[$index]['bank'] = $data['refundBankName'];
                            $totalRefundData[$index]['account'] = $data['refundAccountNumber'];
                            $totalRefundData[$index]['depositor'] = $data['refundDepositor'];
                        }
                    } else {
                        $totalRefundData[$index]['bank'] = $data['refundBankName'];
                        $totalRefundData[$index]['account'] = $data['refundAccountNumber'];
                        $totalRefundData[$index]['depositor'] = $data['refundDepositor'];
                    }
                }

                // 개선 후 교환건이 있는 주문인지 체크
                if($data['handleSno'] > 0 && $data['handleGroupCd'] > 0 && ((substr($data['orderStatus'], 0, 1) == 'e' && $data['handleRegDt'] > $order->orderExchangeChangeDate) || substr($data['orderStatus'], 0, 1) == 'z')){
                    $exchangeHandleGroupCd[$data['handleGroupCd']] = $data['handleGroupCd'];
                    $isExchangeHandle = true;
                }
            }

            //교환정보 로드
            if($isExchangeHandle === true){
                $reOrderCalculation = App::load('\\Component\\Order\\ReOrderCalculation');
                foreach($exchangeHandleGroupCd as $key => $handleGroupCd) {
                    $tmpExchangeHandleData = $reOrderCalculation->getOrderExchangeHandle(Request::get()->get('orderNo'), $handleGroupCd)[0];
                    $tmpExchangeHandleData['ehRefundNameStr'] = $reOrderCalculation->exchangeRefundMethodName[$tmpExchangeHandleData['ehRefundMethod']];
                    $tmpExchangeHandleData['ehAbsDifferencePrice'] = abs($tmpExchangeHandleData['ehDifferencePrice']);
                    $exchangeHandleData[] = $tmpExchangeHandleData;
                }
            }

            $this->setData('isExchangeHandle', $isExchangeHandle);
            $this->setData('exchangeHandleData', $exchangeHandleData);
            $this->setData('isHandle', $isHandle);
            $this->setData('totalRefundPrice', $totalRefundPrice);
            $this->setData('totalCompleteCashPrice', $totalCompleteCashPrice);
            $this->setData('totalCompletePgPrice', $totalCompletePgPrice);
            $this->setData('totalCompleteDepositPrice', $totalCompleteDepositPrice);
            $this->setData('totalCompleteMileagePrice', $totalCompleteMileagePrice);
            $this->setData('totalRefundUseMileage', $totalRefundUseMileage);
            $this->setData('totalRefundUseDeposit', $totalRefundUseDeposit);
            $this->setData('totalRefundData', $totalRefundData);

            // 주문셀 합치는 조건
            $this->setData('cellCombineStatus', $order->statusListCombine);

            // 마일리지 정책
            $mileage = gd_mileage_give_info();
            $this->setData('mileage', $mileage);

            $mileageUse = gd_policy('member.mileageBasic');
            $this->setData('mileageUse', $mileageUse);

            // 예치금 정책
            $depositUse = gd_policy('member.depositConfig');
            $this->setData('depositUse', $depositUse);


            // 사용자 반품/교환/환불 신청 사용여부
            $orderBasic = gd_policy('order.basic');
            $this->setData('userHandleFl', gd_isset($orderBasic['userHandleFl'], 'y') === 'y');

            // 배송지변경 조건 설정
            foreach ($order->getOrderStatusPolicy() as $key => $val) {
                if ($key == 'correct' && in_array($orderData['orderStatus'] . '1', $val)) {
                    $this->setData('canDeliveryModify', true);
                    break;
                }
            }

            // 영수증 신청 가능한 결제 방법
            if (in_array($orderData['settleKind'], $order->settleKindReceiptPossible) === true && in_array(substr($orderData['orderStatus'], 0, 1), $order->statusReceiptPossible) === true) {
                // 세금 계산서 사용 여부
                $taxInfo = gd_policy('order.taxInvoice');
                if (gd_isset($taxInfo['taxInvoiceUseFl']) == 'y' && (gd_isset($taxInfo['gTaxInvoiceFl']) == 'y' || gd_isset($taxInfo['eTaxInvoiceFl']) == 'y')) {
                    $receipt['taxFl'] = 'y';

                    // 세금계산서 이용안내
                    $taxInvoiceInfo = gd_policy('order.taxInvoiceInfo');
                    if ($taxInfo['taxinvoiceInfoUseFl'] == 'y') {
                        $this->setData('taxinvoiceInfo', nl2br($taxInvoiceInfo['taxinvoiceInfo']));
                    }
                    if ($taxInfo['taxinvoiceDeadlineUseFl'] == 'y') {
                        $this->setData('taxinvoiceDeadline', nl2br($taxInvoiceInfo['taxinvoiceDeadline']));
                    }

                    // 세금계산서 입력 정보 가져오기
                    if (gd_is_login() === true) {
                        $tax = \App::load('\\Component\\Order\\Tax');
                        $memNo = Session::get('member.memNo');
                        $memberTaxInfo = $tax->getMemberTaxInvoiceInfo($memNo);
                        $memberInvoiceInfo['tax'] = $memberTaxInfo;
                    }
                }

                $receipt['taxInvoiceLimitDate'] = $taxInfo['taxInvoiceLimitDate'];

                if($taxInfo['taxInvoiceLimitFl'] =='y') {
                    $paymentData = strtotime("+1 month", strtotime($orderData['paymentDt']));
                    if(mktime("0","0","0",date("m",$paymentData),$taxInfo['taxInvoiceLimitDate'],date("Y",$paymentData)) < time()) {
                        $receipt['limitDateFl'] = "n";
                    } else {
                        $receipt['limitDateFl'] = "y";
                    }
                } else {
                    $receipt['limitDateFl'] = "y";
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
                    if (substr($orderData['orderStatus'], 0, 1) !== 'o') {
                        $checkDate = date('Ymd', strtotime('-'.$pgConf['cashReceiptPeriod'].' day'));
                        $paymentDate = gd_date_format('Ymd', $orderData['paymentDt']);
                        if ($paymentDate < $checkDate) {
                            $receipt['periodFl'] = 'n';
                        }
                    }

                    // 현금영수증 입력 정보 가져오기
                    if (gd_is_login() === true) {
                        $cashReceipt = \App::load('\\Component\\Payment\\CashReceipt');
                        $memNo = Session::get('member.memNo');
                        $memberCashInfo = $cashReceipt->getMemberCashReceiptInfo($memNo);
                        $memberInvoiceInfo['cash'] = $memberCashInfo;
                    }
                }

                // 가상계좌 이고 주문 상태인경우에는 영수증 신청 제외 처리
                if (substr($orderData['settleKind'], 1, 1) === 'v' &&  substr($orderData['orderStatus'], 0, 1) === 'o') {
                    $receipt['taxFl'] = 'n';
                    $receipt['cashFl'] = 'n';
                }

                // 세금계산서/현금영수증 입력 정보
                if (empty($memberInvoiceInfo) == false) {
                    $this->setData('memberInvoiceInfo', $memberInvoiceInfo);
                }
            } else {
                $receipt['taxFl'] = 'n';
                $receipt['cashFl'] = 'n';
            }

            // 메일도메인
            $emailDomain = gd_array_change_key_value(gd_code('01004'));
            $emailDomain = array_merge(['self' => __('직접입력')], $emailDomain);
            $this->setData('emailDomain', $emailDomain); // 메일주소 리스팅

            // 상품 옵션가 표시설정 config 불러오기
            $optionPriceConf = gd_policy('goods.display');
            $this->setData('optionPriceFl', gd_isset($optionPriceConf['optionPriceFl'], 'y')); // 상품 옵션가 표시설정

            $this->setData('taxInfo', $taxInfo);
            $this->setData('receipt', gd_isset($receipt));
            $this->setData('goodsReviewId', Board::BASIC_GOODS_REIVEW_ID);
            $this->setData('gPageName', $gPageName);
            $this->setData('pageName', 'view');
        } catch (Exception $e) {
            throw new AlertBackException($e->getMessage());
        }
    }
}
