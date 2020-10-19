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

use Framework\Utility\ArrayUtils;
use Framework\Utility\HttpUtils;
use Globals;
use App;
use Request;
use Exception;
use Session;

/**
 * 주문 상세 페이지
 * [관리자 모드] 주문 상세 페이지
 *
 * @package Bundle\Controller\Admin\Order
 * @author  Jong-tae Ahn <qnibus@godo.co.kr>
 */
class OrderWriteController extends \Controller\Admin\Controller
{
    /**
     * {@inheritdoc}
     */
    public function index()
    {
        try {
            // 페이지 접속시 사이트키 재 생성
            gd_regenerate_site_key();

            // 설정 호출
            $pgCodeConfig = App::getConfig('payment.pg');

            // 메뉴 설정
            $this->callMenu('order', 'write', 'register');

            // 주문 정보
            $order = App::load(\Component\Order\OrderAdmin::class);

            // 입금은행 정보
            $bank = $order->getBankInfo(null, 'y');
            foreach ($bank as $key => $val) {
                $bankData[$val['sno']] = $val['bankName'] . ' ' . $val['accountNumber'] . ' ' . $val['depositor'];
            }
            $this->setData('bankData', $bankData);
            $this->setData('currency', Globals::get('gCurrency'));

            // 기본 주문정보
            $this->setData('orderIp', Request::getRemoteAddress());

            // 마일리지 지급 정보
            $mileage = gd_mileage_give_info();
            $this->setData('mileage', $mileage['basic']);

            // 쿠폰 설정값 정보
            $couponConfig = gd_policy('coupon.config');
            $this->setData('couponUse', gd_isset($couponConfig['couponUseType'], 'n')); // 쿠폰 사용여부

            // 예치금 정책
            $depositUse = gd_policy('member.depositConfig');
            $this->setData('depositUse', $depositUse);

            // 무통장 입금 은행
            $bank = $order->getBankInfo(null, 'y');
            $this->setData('bank', $bank);

            // 메일도메인
            $emailDomain = gd_array_change_key_value(gd_code('01004'));
            $emailDomain = array_merge(['self' => __('직접입력')], $emailDomain);
            $this->setData('emailDomain', $emailDomain); // 메일주소 리스팅

            // 개인 정보 수집 동의 - 이용자 동의 사항
            $tmp = gd_buyer_inform('001003');
            $private = $tmp['content'];
            if (gd_is_html($private) === false) {
                $private = nl2br($private);
            }
            $this->setData('private', gd_isset($private));

            // 주문정책
            $this->setData('orderPolicy', $order->orderPolicy);


            // 영수증 신청 가능한 주문 코드
            $receipt['useReceiptCode'] = json_encode($order->settleKindReceiptPossible);
            $this->setData('receipt', gd_isset($receipt));

            // 결제수단 정책 불러오기
            $policy = App::load(\Component\Policy\Policy::class);
            $settleKindPolicy = $policy->getDefaultSettleKind();

            $currencySymbol = gd_currency_symbol();
            $currencyString = gd_currency_string();
            $this->setData('currencySymbol', $currencySymbol);
            $this->setData('currencyString', $currencyString);
            $this->setData('settleKindBankUseFl', $settleKindPolicy['gb']['useFl']);
            //복수배송지 사용 여부
            $this->setData('isUseMultiShipping', $order->isUseMultiShipping);

            // 안심번호 사용여부
            $orderBasic = gd_policy('order.basic');
            if (isset($orderBasic['safeNumberServiceFl']) && $orderBasic['safeNumberServiceFl'] == 'off') {
                $useSafeNumberFl = $orderBasic['safeNumberServiceFl'];
            } else {
                $useSafeNumberFl = $orderBasic['useSafeNumberFl'];
            }
            $this->setData('useSafeNumberFl', $useSafeNumberFl);


            // 메모 구분
            $memoCd = $order->getOrderMemoList(true);
            $arrMemoVal = [];
            foreach($memoCd as $key => $val){
                $arrMemoVal[$val['itemCd']] = $val['itemNm'];
            }
            $this->setData('memoCd', $arrMemoVal);

            $this->addScript(
                [
                    'orderWrite/orderWrite.js?ts=' . time(),
                ]
            );

            // --- Template_ 호출
            $this->setData('orderName', Session::get('member.memNm')); // 주문자명 (회원 이름)

            $this->setData('orderItemMode', 'order');

        } catch (Exception $e) {
            throw $e;
        }
    }
}
