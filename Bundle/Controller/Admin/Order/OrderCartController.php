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
use Globals;
use App;
use Request;
use Exception;
use Session;

/**
 * 주문 상세 페이지
 * [관리자 모드] 주문 상세 페이지
 * 현재 사용하지 않는 페이지 추후 회원/비회원 작업시 사용하려고 삭제하지 않았습니다.
 *
 * @package Bundle\Controller\Admin\Order
 * @author  Jong-tae Ahn <qnibus@godo.co.kr>
 */
class OrderCartController extends \Controller\Admin\Controller
{
    /**
     * {@inheritdoc}
     */
    public function index()
    {
        try {
            // 설정 호출
            $pgCodeConfig = App::getConfig('payment.pg');
            // 메뉴 설정
            $this->callMenu('order', 'write', 'register');

            // 주문 정보
            $order = App::load(\Component\Order\OrderAdmin::class);

            // 입금은행 정보
            $bank = $order->getBankInfo(null, 'y');
            foreach ($bank as $key => $val) {
                $bankData[$val['bankName'] . STR_DIVISION . $val['accountNumber'] . STR_DIVISION . $val['depositor']] = $val['bankName'] . ' ' . $val['accountNumber'] . ' ' . $val['depositor'];
            }
            $this->setData('bankData', $bankData);
            $this->setData('currency', Globals::get('gCurrency'));

            // 기본 주문정보
            $this->setData('orderIp', Request::getRemoteAddress());

            // 마일리지 지급 정보
            $mileage = gd_mileage_give_info();
            $this->setData('mileage', $mileage['info']);

            // 회원 정보
            $memInfo = $this->getData('gMemberInfo');

            // 쿠폰 설정값 정보
            $couponConfig = gd_policy('coupon.config');
            $this->setData('couponUse', gd_isset($couponConfig['couponUseType'], 'n')); // 쿠폰 사용여부

            // 마일리지 정책
//            $mileageUse = $cart->getMileageUseLimit(gd_isset($memInfo['mileage'], 0), $cart->totalPrice);
//            $this->setData('mileageUse', $mileageUse);

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

            // 결제 방법 설정
//            if (gd_isset($memInfo['settleGb'], 'all') == 'all') {
//                $settleSelect = $cart->couponSettleMethod; // 회원 등급별 결제 방법이 전체 인경우 쿠폰에 따른 결제 방법을 따름
//            } else {
//                $settleSelect = $memInfo['settleGb']; // 회원 등급별 결제 방법이 제한 인경우 등급에 따른 결제 방법을 따름
//            }

            $taxInfo = gd_policy('order.taxInvoice');
            $goodsTaxInfo = gd_policy('goods.tax');
            if (($goodsTaxInfo['taxFreeFl'] == 'f') && gd_isset($taxInfo['taxInvoiceUseFl']) == 'y' && (gd_isset($taxInfo['gTaxInvoiceFl']) == 'y' || gd_isset($taxInfo['eTaxInvoiceFl']) == 'y')) {
                $receipt['taxFl'] = 'y';
            } else {
                $receipt['taxFl'] = 'n';
            }

            // 현금 영수증 사용 여부
            $pgConf = gd_pgs();
            if (empty($pgConf['pgId']) === false && $pgConf['cashReceiptFl'] == 'y') {
                $receipt['cashFl'] = 'y';
            } else {
                $receipt['cashFl'] = 'n';
            }

            // 영수증 신청 가능한 주문 코드
            $receipt['useReceiptCode'] = json_encode($order->settleKindReceiptPossible);
            $this->setData('receipt', gd_isset($receipt));

            // 기본 배송지 정보 가져오기
            $defaultShippingAddress = gd_isset(json_encode($order->getDefaultShippingAddress()));
            $this->setData('defaultShippingAddress', $defaultShippingAddress);

            // 최근배송지 정보 가져오기
            $recentShippingAddress = gd_isset(json_encode($order->getRecentShippingAddress()));
            $this->setData('recentShippingAddress', $recentShippingAddress);

            // --- Template_ 호출
            $this->setData('orderName', Session::get('member.memNm')); // 주문자명 (회원 이름)

            $this->setData('orderItemMode', 'order');

        } catch (Exception $e) {
            throw $e;
        }
    }
}
