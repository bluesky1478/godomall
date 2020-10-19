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

namespace Bundle\Component\Sms;

/**
 * SMS 자동 발송 코드를 모은 인터페이스 입니다.
 *
 * @package Bundle\Component\Sms
 */
interface Code
{
    const ORDER = 'ORDER';  //주문접수
    const INCASH = 'INCASH';    //입금확인
    const ACCOUNT = 'ACCOUNT';  //입금요청
    const DELIVERY = 'DELIVERY';    //상품배송 안내
    const INVOICE_CODE = 'INVOICE_CODE';    //송장번호 안내
    const DELIVERY_COMPLETED = 'DELIVERY_COMPLETED';    //배송완료
    const CANCEL = 'CANCEL';    //주문취소
    const REPAY = 'REPAY';  //환불완료
    const REPAYPART = 'REPAYPART';  //카드 부분취소
    const SOLD_OUT = 'SOLD_OUT';    //상품품절
    const EXCHANGE = 'EXCHANGE';    //고객 교환신청
    const BACK = 'BACK';    //고객 반품신청
    const REFUND = 'REFUND';    //고객 환불신청
    const ADMIN_APPROVAL = 'ADMIN_APPROVAL';    //고객 교환/반품/환불신청 승인
    const ADMIN_REJECT = 'ADMIN_REJECT';    //고객 교환/반품/환불신청 거절

    const JOIN = 'JOIN'; //회원가입
    const APPROVAL = 'APPROVAL'; //가입승인
    const PASS_AUTH = 'PASS_AUTH';  //비밀번호 찾기 인증번호
    const BIRTH = 'BIRTH';  //생일축하
    const SLEEP_INFO = 'SLEEP_INFO';    //휴면회원 전환 사전안내
    const SLEEP_INFO_TODAY = 'SLEEP_INFO_TODAY';    //휴면회원 전환 안내
    const SLEEP_AUTH = 'SLEEP_AUTH';    //휴면회원 해제 인증번호
    const AGREEMENT2YPERIOD = 'AGREEMENT2YPERIOD';  //수신동의여부확인
    const GROUP_CHANGE = 'GROUP_CHANGE';    //회원등급 변경안내
    const MILEAGE_PLUS = 'MILEAGE_PLUS';    //마일리지 지급안내
    const MILEAGE_MINUS = 'MILEAGE_MINUS';  //마일리지 차감안내
    const MILEAGE_EXPIRE = 'MILEAGE_EXPIRE';    //마일리지 소멸안내
    const DEPOSIT_PLUS = 'DEPOSIT_PLUS';    //예치금 지급안내
    const DEPOSIT_MINUS = 'DEPOSIT_MINUS';  //예치금 차감안내

    const COUPON_ORDER_FIRST = 'COUPON_ORDER_FIRST';    //첫 구매 축하 쿠폰
    const COUPON_ORDER = 'COUPON_ORDER';    //구매 감사 쿠폰
    const COUPON_BIRTH = 'COUPON_BIRTH';    //생일 축하 쿠폰
    const COUPON_JOIN = 'COUPON_JOIN';  //회원가입 축하 쿠폰
    const COUPON_LOGIN = 'COUPON_LOGIN';    //출석 체크 감사 쿠폰
    const COUPON_MEMBER_MODIFY = 'COUPON_MEMBER_MODIFY';    //회원 정보 수정 이벤트 쿠폰
    const COUPON_MANUAL = 'COUPON_MANUAL';  //수동쿠폰 발급 안내
    const COUPON_WARNING = 'COUPON_WARNING';    //쿠폰만료 안내
    const COUPON_WAKE = 'COUPON_WAKE';    //휴면회원 해제 감사 쿠폰

    const SETTLE_BANK = 'SETTLE_BANK';  //무통장 입금은행 정보 변경
}
