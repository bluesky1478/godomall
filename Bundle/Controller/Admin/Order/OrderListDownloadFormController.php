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

use Framework\Debug\Exception\Except;
use Framework\Utility\ArrayUtils;
use Globals;
use Request;

/**
 * 주문리스트 다운로드 양식 관리
 * [관리자 모드] 주문리스트 다운로드 양식 관리
 *
 * @package Bundle\Controller\Admin\Order
 * @author  Jong-tae Ahn <qnibus@godo.co.kr>
 */
class OrderListDownloadFormController extends \Controller\Admin\Controller
{
    /**
     * @inheritdoc
     */
    public function index()
    {
        // --- 모듈 호출
        try {
            $orderAdmin = \App::load('\\Component\\Order\\OrderAdmin');
            $formList = $orderAdmin->getDownloadFormList();
            $getData = $orderAdmin->getDownloadForm(Request::get()->get('sno'));

            // @formatter:off
            $formFieldOrder = array(
                'o.orderNo'=>__('주문번호')
                , 'm.memNm'=>__('회원명')
                , 'm.memId'=>__('회원아이디')
                , 'o.orderGoodsNm'=> __('주문 상품명')
                , 'o.orderGoodsCnt'=>__('주문 상품 갯수')
                , 'o.settlePrice'=>__('총 주문 금액')
                , 'o.totalGoodsPrice'=>__('총 상품 금액')
                , 'o.totalDeliveryCharge'=>__('총 배송비')
                , 'o.totalMinusMileage'=>__('사용된 총 마일리지')
                , 'o.totalMinusMember'=>__('총 회원 할인 금액')
                , 'o.totalMinusCoupon'=>__('총 쿠폰 할인 금액')
                , 'o.totalPlusMileage'=>__('총 적립 금액')
                , 'o.plusGoodsMileage'=>__('적립될 상품 마일리지')
                , 'o.plusAddMileage'=>__('적립될 추가 마일리지')
                , 'o.plusMemberMileage'=>__('적립될 회원 마일리지')
                , 'o.plusCouponMileage'=>__('적립될 쿠폰 마일리지')
                , 'o.orderStatus'=>__('주문 상태')
                , 'o.settleKind'=>__('결제 방법')
                , 'o.bankAccount'=>__('무통장 입금 은행')
                , 'o.bankSender'=>__('무통장 입금자')
                , 'o.receiptFl'=>__('영수증 신청여부')
                , 'o.pgResultCode'=>__('PG 결과코드')
                , 'o.pgTid'=>__('PG 거래번호')
                , 'o.pgAppNo'=>__('PG 승인번호')
                , 'og.paymentDt'=>__('입금 일자')
                , 'o.regDt'=>__('주문 일자')
                , 'oi.orderName'=>__('주문자 이름')
                , 'oi.orderEmail'=>__('주문자 e-mail')
                , 'oi.orderPhone'=>__('주문자 전화번호')
                , 'oi.orderCellPhone'=>__('주문자 휴대폰 번호')
                , 'oi.orderZipcode'=>__('주문자 구 우편번호')
                , 'oi.orderZonecode'=>__('주문자 우편번호')
                , 'oi.orderAddress'=>__('주문자 주소')
                , 'oi.orderAddressSub'=>__('주문자 나머지 주소')
                , 'orderAddressLong'=>__('주문자 전체주소')
                , 'oi.receiverName'=>__('수취인 이름')
                , 'oi.receiverPhone'=>__('수취인 전화번호')
                , 'oi.receiverCellPhone'=>__('수취인 휴대폰 번호')
                , 'oi.receiverZipcode'=>__('수취인 구 우편번호')
                , 'oi.receiverZonecode'=>__('수취인 우편번호')
                , 'oi.receiverAddress'=>__('수취인 주소')
                , 'oi.receiverAddressSub'=>__('수취인 나머지 주소')
                , 'receiverAddressLong'=>__('수취인 전체주소')
                , 'oi.orderMemo'=>__('주문시 남기는글')
                , 'giftTitle'=>__('사은품 타이틀')
                , 'giftGoodsName'=>__('사은품 상품명')
            );

            $formFieldOrderGoods = array(
                'og.orderCd'=>__('주문 코드(순서)')
                , 'og.orderStatus'=>__('주문 상태')
                , 'og.goodsNo'=>__('상품 번호')
                , 'og.goodsCd'=>__('상품 코드')
                , 'og.goodsModelNo'=>__('모델명')
                , 'og.goodsNm'=>__('상품명')
                , 'og.goodsCnt'=>__('상품 수량')
                , 'og.goodsPrice'=>__('상품 가격')
                , 'og.optionAddPrice'=>__('추가 옵션 금액')
                , 'og.optionTextPrice'=>__('텍스트 옵션 금액')
                , 'og.fixedPrice'=>__('정가')
                , 'og.costPrice'=>__('매입가')
                , 'og.mileage'=>__('마일리지')
                , 'og.goodsTaxInfo'=>__('상품 부가세 정보')
                , 'og.goodsWeight'=>__('상품 무게')
                , 'og.optionInfo'=>__('옵션 정보')
                , 'og.optionAddInfo'=>__('추가 옵션 정보')
                , 'og.optionTextInfo'=>__('텍스트 옵션 정보')
                , 'cg.cateNm'=>__('카테고리명')
                , 'cb.cateNm'=>__('브랜드명')
                , 'og.makerNm'=>__('제조사')
                , 'og.originNm'=>__('원산지')
                , 'og.deliveryFl'=>__('배송정책')
                , 'og.minusCoupon'=>__('쿠폰 할인 금액')
                , 'og.plusCoupon'=>__('적립 쿠폰 금액')
                , 'og.orderDeliverySno'=>__('배송 업체번호')
                , 'dc.companyName'=>__('배송 업체명')
                , 'og.invoiceNo'=>__('송장 번호')
                , 'og.paymentDt'=>__('입금 일자')
                , 'og.deliveryDt'=>__('배송 일자')
                , 'og.finishDt'=>__('완료 일자')
            );
            // @formatter:on

            // --- 관리자 디자인 템플릿
            $this->setData('formList', gd_isset($formList));
            $this->setData('formFieldOrder', gd_isset($formFieldOrder));
            $this->setData('formFieldOrderGoods', gd_isset($formFieldOrderGoods));
            $this->setData('data', gd_isset($getData));

        } catch (\Exception $e) {
            throw $e;
        }
    }
}
