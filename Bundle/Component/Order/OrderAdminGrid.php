<?php
/**
 * This is commercial software, only users who have purchased a valid license
 * and accept to the terms of the License Agreement can install and use this
 * program.
 *
 * Do not edit or add to this file if you wish to upgrade Godomall5 to newer
 * versions in the future.
 *
 * @copyright Copyright (c) 2017 GodoSoft.
 * @link http://www.godo.co.kr
 */

namespace Bundle\Component\Order;

use Component\Member\Manager;
use Component\Database\DBTableField;
use Session;

class OrderAdminGrid
{
    //본사
    private $orderScmMainList = [
        /*
        * 주문통합리스트 주문번호별
        */
        'list_all_order' => [
            'defaultList' => [ //디폴트 리스트
                'check', // 선택
                'no', // 번호
                'domainFl', // 상점구분
                'regDt', // 주문일시
                'orderNo', // 주문번호
                'orderName', // 주문자
                'orderGoodsNm', // 주문상품
                'totalGoodsPrice', // 총 상품금액
                'totalDeliveryCharge', // 총 배송비
                'totalOrderPrice', // 총 주문금액
                'settleKind', // 결제방법
                'settleStatus', // 결제상태
                'noDelivery', // 미배송
                'deliverying', // 배송중
                'deliveryed', // 배송완료
                'cancel', // 취소
                'exchange', // 교환
                'back', // 반품
                'refund', // 환불
                'adminMemo', // 관리자메모
                'orderTypeFl', // 주문유형
            ],
            'list' => [ //전체리스트
                'check', // 선택
                'no', // 번호
                'domainFl', // 상점구분
                'regDt', // 주문일시
                'paymentDt', //입금일시
                'orderNo', // 주문번호
                'orderName', // 주문자
                'orderTypeFl', // 주문유형
                'orderGoodsNm', // 주문상품
                'orderGoodsNmGlobal', // 주문상품(해외상점)
                'totalGoodsPrice', // 총 상품금액
                'totalDeliveryCharge', // 총 배송비
                'totalDcPrice', // 총 할인금액
                'totalUseAddedPrice', // 총 부가결제금액
                'totalOrderPrice', // 총 주문금액
                'totalRealSettlePrice', // 총 실결제금액
                'totalGoodsPriceGlobal', // 총 상품금액(해외상점)
                'totalDeliveryChargeGlobal', // 총 배송비(해외상점)
                'totalDcPriceGlobal', // 총 할인금액(해외상점)
                'totalOrderPriceGlobal', // 총 주문금액(해외상점)
                'totalRealSettlePriceGlobal', // 총 실결제금액(해외상점)
                'settleKind', // 결제방법
                'settleStatus', // 결제상태
                //'processStatus', // 처리상태
                'noDelivery', // 미배송
                'deliverying', // 배송중
                'deliveryed', // 배송완료
                'cancel', // 취소
                'exchange', // 교환
                'back', // 반품
                'refund', // 환불
                'receiverName', // 수령자
                //'receiverAddress', // 수령자 주소
                'orderMemo', // 배송 메시지
                'receipt', // 영수증 신청여부
                'gift', // 사은품
                'adminMemo', // 관리자메모
                'phoneNumber', // 휴대폰 번호
            ],
        ],
        /*
        * 주문통합리스트 상품주문번호별
        */
        'list_all_order_goods' => [
            'defaultList' => [ //디폴트 리스트
                'check', // 선택
                'no', // 번호
                'domainFl', // 상점구분
                'regDt', // 주문일시
                'orderName', // 주문자
                'orderGoodsNo', // 상품주문번호
                'orderGoodsNm', // 주문상품
                'goodsCnt', // 수량
                'goodsPrice', // 상품금액
                'totalGoodsPrice', // 총 상품금액
                'deliveryCharge', // 배송비
                'totalDeliveryCharge', // 총 배송비
                'totalOrderPrice', // 총 주문금액
                'settleKind', // 결제방법
                'processStatus', // 처리상태
                'adminMemo', // 관리자메모
                'orderTypeFl', // 주문유형
            ],
            'list' => [ //전체리스트
                'check', // 선택
                'no', // 번호
                'domainFl', // 상점구분
                'regDt', // 주문일시
                'paymentDt', //입금일시
                'deliveryDt', //배송일시
                'deliveryCompleteDt', //배송완료일시
                'finishDt', //구매확정일시
                'orderNo', // 주문번호
                'orderName', // 주문자
                'orderTypeFl', // 주문유형
                'orderGoodsNo', // 상품주문번호
                'goodsCd', // 상품코드(자체상품코드)
                'goodsTaxInfo', // 상품 부가세
                'orderGoodsNm', // 주문상품
                'orderGoodsNmGlobal', // 주문상품(해외상점)
                'goodsCnt', // 수량
                'orgGoodsPrice', // 판매가 (판매가가 goodsPrice지만 상품금액에서 사용하고있어 래거쉬보장 문제로 orgGoodsPrice로 사용)
                'goodsPrice', // 상품금액
                'totalGoodsPrice', // 총 상품금액
                'deliveryCharge', // 배송비
                'totalDeliveryCharge', // 총 배송비
                'totalDcPrice', // 총 할인금액
                'totalUseAddedPrice', // 총 부가결제금액
                'totalOrderPrice', // 총 주문금액
                'totalRealSettlePrice', // 총 실결제금액
                'goodsPriceGlobal', // 상품금액(해외상점)
                'totalGoodsPriceGlobal', // 총 상품금액(해외상점)
                'deliveryChargeGlobal', // 배송비(해외상점)
                'totalDeliveryChargeGlobal', // 총 배송비(해외상점)
                'totalDcPriceGlobal', // 총 할인금액(해외상점)
                'totalOrderPriceGlobal', // 총 주문금액(해외상점)
                'totalRealSettlePriceGlobal', // 총 실결제금액(해외상점)
                'settleKind', // 결제방법
                'settleStatus', // 결제상태
                'processStatus', // 처리상태
                'purchaseNm', // 매입처
                'brandNm', // 브랜드
                'goodsModelNo', // 모델명
                'makerNm', // 제조사
                'deliverySno', // 배송번호
                'scmNm', // 공급사
                'commission', // 수수료율
                'costPrice', // 매입가
                'invoiceNo', // 송장번호
                'receiverName', // 수령자
                //'receiverAddress', // 수령자 주소
                'multiShippingCd', // 배송지
                'orderMemo', // 배송 메시지
                'receipt', // 영수증 신청여부
                'hscode', // HS코드
                'gift', // 사은품
                'adminMemo', // 관리자메모
                'phoneNumber', // 휴대폰 번호
            ],
        ],

        /*
        * 입금대기리스트 주문번호별
        */
        'list_order_order' => [
            'defaultList' => [ //디폴트 리스트
                'check', // 선택
                'no', // 번호
                'domainFl', // 상점구분
                'regDt', // 주문일시
                'regDtInterval', // 경과일자
                'orderNo', // 주문번호
                'orderName', // 주문자
                'orderGoodsNm', // 주문상품
                'totalGoodsPrice', // 총 상품금액
                'totalDeliveryCharge', // 총 배송비
                'totalOrderPrice', // 총 주문금액
                'settleKind', // 결제방법
                'bankSender', //입금자
                'bankAccount', //입금계좌
                'adminMemo', // 관리자메모
                'orderTypeFl', // 주문유형
            ],
            'list' => [ //전체리스트
                'check', // 선택
                'no', // 번호
                'domainFl', // 상점구분
                'regDt', // 주문일시
                'regDtInterval', // 경과일자
                'orderNo', // 주문번호
                'orderName', // 주문자
                'orderTypeFl', // 주문유형
                'orderGoodsNm', // 주문상품
                'orderGoodsNmGlobal', // 주문상품(해외상점)
                'totalGoodsPrice', // 총 상품금액
                'totalDeliveryCharge', // 총 배송비
                'totalDcPrice', // 총 할인금액
                'totalUseAddedPrice', // 총 부가결제금액
                'totalOrderPrice', // 총 주문금액
                'totalRealSettlePrice', // 총 실결제금액
                'totalGoodsPriceGlobal', // 총 상품금액(해외상점)
                'totalDeliveryChargeGlobal', // 총 배송비(해외상점)
                'totalDcPriceGlobal', // 총 할인금액(해외상점)
                'totalOrderPriceGlobal', // 총 주문금액(해외상점)
                'totalRealSettlePriceGlobal', // 총 실결제금액(해외상점)
                'settleKind', // 결제방법
                //'processStatus', // 처리상태
                'receiverName', // 수령자
                //'receiverAddress', // 수령자 주소
                'orderMemo', // 배송 메시지
                'receipt', // 영수증 신청여부
                'gift', // 사은품
                'bankSender', //입금자
                'bankAccount', //입금계좌
                'adminMemo', // 관리자메모
                'phoneNumber', // 휴대폰 번호
            ],
        ],
        /*
        * 입금대기리스트 상품주문번호별
        */
        'list_order_order_goods' => [
            'defaultList' => [ //디폴트 리스트
                'check', // 선택
                'no', // 번호
                'domainFl', // 상점구분
                'regDt', // 주문일시
                'regDtInterval', // 경과일자
                'orderName', // 주문자
                'orderGoodsNo', // 상품주문번호
                'orderGoodsNm', // 주문상품
                'goodsCnt', // 수량
                'goodsPrice', // 상품금액
                'totalGoodsPrice', // 총 상품금액
                'deliveryCharge', // 배송비
                'totalDeliveryCharge', // 총 배송비
                'totalOrderPrice', // 총 주문금액
                'settleKind', // 결제방법
                'processStatus', // 처리상태
                'bankSender', //입금자
                'bankAccount', //입금계좌
                'adminMemo', // 관리자메모
                'orderTypeFl', // 주문유형
            ],
            'list' => [ //전체리스트
                'check', // 선택
                'no', // 번호
                'domainFl', // 상점구분
                'regDt', // 주문일시
                'regDtInterval', // 경과일자
                'orderNo', // 주문번호
                'orderName', // 주문자
                'orderTypeFl', // 주문유형
                'orderGoodsNo', // 상품주문번호
                'goodsCd', // 상품코드(자체상품코드)
                'goodsTaxInfo', // 상품 부가세
                'orderGoodsNm', // 주문상품
                'orderGoodsNmGlobal', // 주문상품(해외상점)
                'goodsCnt', // 수량
                'orgGoodsPrice', // 판매가 (판매가가 goodsPrice지만 상품금액에서 사용하고있어 래거쉬보장 문제로 orgGoodsPrice로 사용)
                'goodsPrice', // 상품금액
                'totalGoodsPrice', // 총 상품금액
                'deliveryCharge', // 배송비
                'totalDeliveryCharge', // 총 배송비
                'totalDcPrice', // 총 할인금액
                'totalUseAddedPrice', // 총 부가결제금액
                'totalOrderPrice', // 총 주문금액
                'totalRealSettlePrice', // 총 실결제금액
                'goodsPriceGlobal', // 상품금액(해외상점)
                'totalGoodsPriceGlobal', // 총 상품금액(해외상점)
                'deliveryChargeGlobal', // 배송비(해외상점)
                'totalDeliveryChargeGlobal', // 총 배송비(해외상점)
                'totalDcPriceGlobal', // 총 할인금액(해외상점)
                'totalOrderPriceGlobal', // 총 주문금액(해외상점)
                'totalRealSettlePriceGlobal', // 총 실결제금액(해외상점)
                'settleKind', // 결제방법
                'processStatus', // 처리상태
                'purchaseNm', // 매입처
                'brandNm', // 브랜드
                'goodsModelNo', // 모델명
                'makerNm', // 제조사
                'deliverySno', // 배송번호
                'scmNm', // 공급사
                'commission', // 수수료율
                'costPrice', // 매입가
                'bankSender', //입금자
                'bankAccount', //입금계좌
                'receiverName', // 수령자
                //'receiverAddress', // 수령자 주소
                'multiShippingCd', // 배송지
                'orderMemo', // 배송 메시지
                'receipt', // 영수증 신청여부
                'hscode', // HS코드
                'gift', // 사은품
                'adminMemo', // 관리자메모
                'phoneNumber', // 휴대폰 번호
            ],
        ],

        /*
        * 결제완료리스트 주문번호별
        */
        'list_pay_order' => [
            'defaultList' => [ //디폴트 리스트
                'check', // 선택
                'no', // 번호
                'domainFl', // 상점구분
                'regDt', // 주문일시
                'orderNo', // 주문번호
                'orderName', // 주문자
                'orderGoodsNm', // 주문상품
                'totalGoodsPrice', // 총 상품금액
                'totalDeliveryCharge', // 총 배송비
                'totalOrderPrice', // 총 주문금액
                'settleKind', // 결제방법
                'adminMemo', // 관리자메모
                'orderTypeFl', // 주문유형
            ],
            'list' => [ //전체리스트
                'check', // 선택
                'no', // 번호
                'domainFl', // 상점구분
                'regDt', // 주문일시
                'paymentDt', //입금일시
                'orderNo', // 주문번호
                'orderName', // 주문자
                'orderTypeFl', // 주문유형
                'orderGoodsNm', // 주문상품
                'orderGoodsNmGlobal', // 주문상품(해외상점)
                'goodsCnt', // 수량
                'totalGoodsPrice', // 총 상품금액
                'totalDeliveryCharge', // 총 배송비
                'totalDcPrice', // 총 할인금액
                'totalUseAddedPrice', // 총 부가결제금액
                'totalOrderPrice', // 총 주문금액
                'totalRealSettlePrice', // 총 실결제금액
                'totalGoodsPriceGlobal', // 총 상품금액(해외상점)
                'totalDeliveryChargeGlobal', // 총 배송비(해외상점)
                'totalDcPriceGlobal', // 총 할인금액(해외상점)
                'totalOrderPriceGlobal', // 총 주문금액(해외상점)
                'totalRealSettlePriceGlobal', // 총 실결제금액(해외상점)
                'settleKind', // 결제방법
                'processStatus', // 처리상태
                'receiverName', // 수령자
                //'receiverAddress', // 수령자 주소
                'orderMemo', // 배송 메시지
                'receipt', // 영수증 신청여부
                'gift', // 사은품
                'adminMemo', // 관리자메모
                'phoneNumber', // 휴대폰 번호
            ],
        ],
        /*
        * 결제완료리스트 상품주문번호별
        */
        'list_pay_order_goods' => [
            'defaultList' => [ //디폴트 리스트
                'check', // 선택
                'no', // 번호
                'domainFl', // 상점구분
                'regDt', // 주문일시
                'orderName', // 주문자
                'orderGoodsNo', // 상품주문번호
                'orderGoodsNm', // 주문상품
                'goodsCnt', // 수량
                'goodsPrice', // 상품금액
                'totalGoodsPrice', // 총 상품금액
                'deliveryCharge', // 배송비
                'totalDeliveryCharge', // 총 배송비
                'totalOrderPrice', // 총 주문금액
                'settleKind', // 결제방법
                'processStatus', // 처리상태
                'adminMemo', // 관리자메모
                'orderTypeFl', // 주문유형
            ],
            'list' => [ //전체리스트
                'check', // 선택
                'no', // 번호
                'domainFl', // 상점구분
                'regDt', // 주문일시
                'paymentDt', //입금일시
                'orderNo', // 주문번호
                'orderName', // 주문자
                'orderTypeFl', // 주문유형
                'orderGoodsNo', // 상품주문번호
                'goodsCd', // 상품코드(자체상품코드)
                'goodsTaxInfo', // 상품 부가세
                'orderGoodsNm', // 주문상품
                'orderGoodsNmGlobal', // 주문상품(해외상점)
                'goodsCnt', // 수량
                'orgGoodsPrice', // 판매가 (판매가가 goodsPrice지만 상품금액에서 사용하고있어 래거쉬보장 문제로 orgGoodsPrice로 사용)
                'goodsPrice', // 상품금액
                'totalGoodsPrice', // 총 상품금액
                'deliveryCharge', // 배송비
                'totalDeliveryCharge', // 총 배송비
                'totalDcPrice', // 총 할인금액
                'totalUseAddedPrice', // 총 부가결제금액
                'totalOrderPrice', // 총 주문금액
                'totalRealSettlePrice', // 총 실결제금액
                'goodsPriceGlobal', // 상품금액(해외상점)
                'totalGoodsPriceGlobal', // 총 상품금액(해외상점)
                'deliveryChargeGlobal', // 배송비(해외상점)
                'totalDeliveryChargeGlobal', // 총 배송비(해외상점)
                'totalDcPriceGlobal', // 총 할인금액(해외상점)
                'totalOrderPriceGlobal', // 총 주문금액(해외상점)
                'totalRealSettlePriceGlobal', // 총 실결제금액(해외상점)
                'settleKind', // 결제방법
                'processStatus', // 처리상태
                'purchaseNm', // 매입처
                'brandNm', // 브랜드
                'goodsModelNo', // 모델명
                'makerNm', // 제조사
                'deliverySno', // 배송번호
                'scmNm', // 공급사
                'commission', // 수수료율
                'costPrice', // 매입가
                'receiverName', // 수령자
                //'receiverAddress', // 수령자 주소
                'multiShippingCd', // 배송지
                'orderMemo', // 배송 메시지
                'receipt', // 영수증 신청여부
                'hscode', // HS코드
                'gift', // 사은품
                'adminMemo', // 관리자메모
                'phoneNumber', // 휴대폰 번호
            ],
        ],

        /*
        * 상품준비중리스트 주문번호별
        */
        'list_goods_order' => [
            'defaultList' => [ //디폴트 리스트
                'check', // 선택
                'no', // 번호
                'domainFl', // 상점구분
                'regDt', // 주문일시
                'orderNo', // 주문번호
                'orderName', // 주문자
                'orderGoodsNm', // 주문상품
                'totalGoodsPrice', // 총 상품금액
                'totalDeliveryCharge', // 총 배송비
                'totalOrderPrice', // 총 주문금액
                'settleKind', // 결제방법
                'invoiceNo', // 송장번호
                'gift', // 사은품
                'adminMemo', // 관리자메모
                'orderTypeFl', // 주문유형
            ],
            'list' => [ //전체리스트
                'check', // 선택
                'no', // 번호
                'domainFl', // 상점구분
                'regDt', // 주문일시
                'paymentDt', //입금일시
                'orderNo', // 주문번호
                'orderName', // 주문자
                'orderTypeFl', // 주문유형
                'orderGoodsNm', // 주문상품
                'orderGoodsNmGlobal', // 주문상품(해외상점)
                'goodsCnt', // 수량
                'totalGoodsPrice', // 총 상품금액
                'totalDeliveryCharge', // 총 배송비
                'totalDcPrice', // 총 할인금액
                'totalUseAddedPrice', // 총 부가결제금액
                'totalOrderPrice', // 총 주문금액
                'totalRealSettlePrice', // 총 실결제금액
                'totalGoodsPriceGlobal', // 총 상품금액(해외상점)
                'totalDeliveryChargeGlobal', // 총 배송비(해외상점)
                'totalDcPriceGlobal', // 총 할인금액(해외상점)
                'totalOrderPriceGlobal', // 총 주문금액(해외상점)
                'totalRealSettlePriceGlobal', // 총 실결제금액(해외상점)
                'settleKind', // 결제방법
                //'processStatus', // 처리상태
                'invoiceNo', // 송장번호
                'receiverName', // 수령자
                //'receiverAddress', // 수령자 주소
                'orderMemo', // 배송 메시지
                'receipt', // 영수증 신청여부
                'gift', // 사은품
                'adminMemo', // 관리자메모
                'phoneNumber', // 휴대폰 번호
            ],
        ],
        /*
        * 상품준비중리스트 상품주문번호별
        */
        'list_goods_order_goods' => [
            'defaultList' => [ //디폴트 리스트
                'check', // 선택
                'no', // 번호
                'domainFl', // 상점구분
                'regDt', // 주문일시
                'orderName', // 주문자
                'orderGoodsNo', // 상품주문번호
                'orderGoodsNm', // 주문상품
                'goodsCnt', // 수량
                'goodsPrice', // 상품금액
                'totalGoodsPrice', // 총 상품금액
                'deliveryCharge', // 배송비
                'totalDeliveryCharge', // 총 배송비
                'totalOrderPrice', // 총 주문금액
                'settleKind', // 결제방법
                'processStatus', // 처리상태
                'invoiceNo', // 송장번호
                'gift', // 사은품
                'adminMemo', // 관리자메모
                'orderTypeFl', // 주문유형
            ],
            'list' => [ //전체리스트
                'check', // 선택
                'no', // 번호
                'domainFl', // 상점구분
                'regDt', // 주문일시
                'paymentDt', //입금일시
                'orderNo', // 주문번호
                'orderName', // 주문자
                'orderTypeFl', // 주문유형
                'orderGoodsNo', // 상품주문번호
                'goodsCd', // 상품코드(자체상품코드)
                'goodsTaxInfo', // 상품 부가세
                'orderGoodsNm', // 주문상품
                'orderGoodsNmGlobal', // 주문상품(해외상점)
                'goodsCnt', // 수량
                'orgGoodsPrice', // 판매가 (판매가가 goodsPrice지만 상품금액에서 사용하고있어 래거쉬보장 문제로 orgGoodsPrice로 사용)
                'goodsPrice', // 상품금액
                'totalGoodsPrice', // 총 상품금액
                'deliveryCharge', // 배송비
                'totalDeliveryCharge', // 총 배송비
                'totalDcPrice', // 총 할인금액
                'totalUseAddedPrice', // 총 부가결제금액
                'totalOrderPrice', // 총 주문금액
                'totalRealSettlePrice', // 총 실결제금액
                'goodsPriceGlobal', // 상품금액(해외상점)
                'totalGoodsPriceGlobal', // 총 상품금액(해외상점)
                'deliveryChargeGlobal', // 배송비(해외상점)
                'totalDeliveryChargeGlobal', // 총 배송비(해외상점)
                'totalDcPriceGlobal', // 총 할인금액(해외상점)
                'totalOrderPriceGlobal', // 총 주문금액(해외상점)
                'totalRealSettlePriceGlobal', // 총 실결제금액(해외상점)
                'settleKind', // 결제방법
                'processStatus', // 처리상태
                'purchaseNm', // 매입처
                'brandNm', // 브랜드
                'goodsModelNo', // 모델명
                'makerNm', // 제조사
                'deliverySno', // 배송번호
                'scmNm', // 공급사
                'commission', // 수수료율
                'costPrice', // 매입가
                'invoiceNo', // 송장번호
                'receiverName', // 수령자
                //'receiverAddress', // 수령자 주소
                'multiShippingCd', // 배송지
                'orderMemo', // 배송 메시지
                'receipt', // 영수증 신청여부
                'hscode', // HS코드
                'gift', // 사은품
                'adminMemo', // 관리자메모
                'phoneNumber', // 휴대폰 번호
            ],
        ],

        /*
        * 배송중리스트 주문번호별
        */
        'list_delivery_order' => [
            'defaultList' => [ //디폴트 리스트
                'check', // 선택
                'no', // 번호
                'domainFl', // 상점구분
                'regDt', // 주문일시
                'orderNo', // 주문번호
                'orderName', // 주문자
                'orderGoodsNm', // 주문상품
                'totalGoodsPrice', // 총 상품금액
                'totalDeliveryCharge', // 총 배송비
                'totalOrderPrice', // 총 주문금액
                'settleKind', // 결제방법
                'invoiceNo', // 송장번호
                'gift', // 사은품
                'adminMemo', // 관리자메모
                'orderTypeFl', // 주문유형
            ],
            'list' => [ //전체리스트
                'check', // 선택
                'no', // 번호
                'domainFl', // 상점구분
                'regDt', // 주문일시
                'paymentDt', //입금일시
                'orderNo', // 주문번호
                'orderName', // 주문자
                'orderTypeFl', // 주문유형
                'orderGoodsNm', // 주문상품
                'orderGoodsNmGlobal', // 주문상품(해외상점)
                'goodsCnt', // 수량
                'totalGoodsPrice', // 총 상품금액
                'totalDeliveryCharge', // 총 배송비
                'totalDcPrice', // 총 할인금액
                'totalUseAddedPrice', // 총 부가결제금액
                'totalOrderPrice', // 총 주문금액
                'totalRealSettlePrice', // 총 실결제금액
                'totalGoodsPriceGlobal', // 총 상품금액(해외상점)
                'totalDeliveryChargeGlobal', // 총 배송비(해외상점)
                'totalDcPriceGlobal', // 총 할인금액(해외상점)
                'totalOrderPriceGlobal', // 총 주문금액(해외상점)
                'totalRealSettlePriceGlobal', // 총 실결제금액(해외상점)
                'settleKind', // 결제방법
                //'processStatus', // 처리상태
                'invoiceNo', // 송장번호
                'receiverName', // 수령자
                //'receiverAddress', // 수령자 주소
                'orderMemo', // 배송 메시지
                'receipt', // 영수증 신청여부
                'gift', // 사은품
                'adminMemo', // 관리자메모
                'phoneNumber', // 휴대폰 번호
            ],
        ],
        /*
        * 배송중리스트 상품주문번호별
        */
        'list_delivery_order_goods' => [
            'defaultList' => [ //디폴트 리스트
                'check', // 선택
                'no', // 번호
                'domainFl', // 상점구분
                'regDt', // 주문일시
                'orderName', // 주문자
                'orderGoodsNo', // 상품주문번호
                'orderGoodsNm', // 주문상품
                'goodsCnt', // 수량
                'goodsPrice', // 상품금액
                'totalGoodsPrice', // 총 상품금액
                'deliveryCharge', // 배송비
                'totalDeliveryCharge', // 총 배송비
                'totalOrderPrice', // 총 주문금액
                'settleKind', // 결제방법
                'processStatus', // 처리상태
                'invoiceNo', // 송장번호
                'gift', // 사은품
                'adminMemo', // 관리자메모
                'orderTypeFl', // 주문유형
            ],
            'list' => [ //전체리스트
                'check', // 선택
                'no', // 번호
                'domainFl', // 상점구분
                'regDt', // 주문일시
                'paymentDt', //입금일시
                'deliveryDt', //배송일시
                'orderNo', // 주문번호
                'orderName', // 주문자
                'orderTypeFl', // 주문유형
                'orderGoodsNo', // 상품주문번호
                'goodsCd', // 상품코드(자체상품코드)
                'goodsTaxInfo', // 상품 부가세
                'orderGoodsNm', // 주문상품
                'orderGoodsNmGlobal', // 주문상품(해외상점)
                'goodsCnt', // 수량
                'orgGoodsPrice', // 판매가 (판매가가 goodsPrice지만 상품금액에서 사용하고있어 래거쉬보장 문제로 orgGoodsPrice로 사용)
                'goodsPrice', // 상품금액
                'totalGoodsPrice', // 총 상품금액
                'deliveryCharge', // 배송비
                'totalDeliveryCharge', // 총 배송비
                'totalDcPrice', // 총 할인금액
                'totalUseAddedPrice', // 총 부가결제금액
                'totalOrderPrice', // 총 주문금액
                'totalRealSettlePrice', // 총 실결제금액
                'goodsPriceGlobal', // 상품금액(해외상점)
                'totalGoodsPriceGlobal', // 총 상품금액(해외상점)
                'deliveryChargeGlobal', // 배송비(해외상점)
                'totalDeliveryChargeGlobal', // 총 배송비(해외상점)
                'totalDcPriceGlobal', // 총 할인금액(해외상점)
                'totalOrderPriceGlobal', // 총 주문금액(해외상점)
                'totalRealSettlePriceGlobal', // 총 실결제금액(해외상점)
                'settleKind', // 결제방법
                'processStatus', // 처리상태
                'purchaseNm', // 매입처
                'brandNm', // 브랜드
                'goodsModelNo', // 모델명
                'makerNm', // 제조사
                'deliverySno', // 배송번호
                'scmNm', // 공급사
                'commission', // 수수료율
                'costPrice', // 매입가
                'invoiceNo', // 송장번호
                'receiverName', // 수령자
                //'receiverAddress', // 수령자 주소
                'multiShippingCd', // 배송지
                'orderMemo', // 배송 메시지
                'receipt', // 영수증 신청여부
                'hscode', // HS코드
                'gift', // 사은품
                'adminMemo', // 관리자메모
                'phoneNumber', // 휴대폰 번호
            ],
        ],

        /*
        * 배송완료리스트 주문번호별
        */
        'list_delivery_ok_order' => [
            'defaultList' => [ //디폴트 리스트
                'check', // 선택
                'no', // 번호
                'domainFl', // 상점구분
                'regDt', // 주문일시
                'orderNo', // 주문번호
                'orderName', // 주문자
                'orderGoodsNm', // 주문상품
                'totalGoodsPrice', // 총 상품금액
                'totalDeliveryCharge', // 총 배송비
                'totalOrderPrice', // 총 주문금액
                'settleKind', // 결제방법
                'invoiceNo', // 송장번호
                'gift', // 사은품
                'adminMemo', // 관리자메모
                'orderTypeFl', // 주문유형
            ],
            'list' => [ //전체리스트
                'check', // 선택
                'no', // 번호
                'domainFl', // 상점구분
                'regDt', // 주문일시
                'paymentDt', //입금일시
                'orderNo', // 주문번호
                'orderName', // 주문자
                'orderTypeFl', // 주문유형
                'orderGoodsNm', // 주문상품
                'orderGoodsNmGlobal', // 주문상품(해외상점)
                'goodsCnt', // 수량
                'totalGoodsPrice', // 총 상품금액
                'totalDeliveryCharge', // 총 배송비
                'totalDcPrice', // 총 할인금액
                'totalUseAddedPrice', // 총 부가결제금액
                'totalOrderPrice', // 총 주문금액
                'totalRealSettlePrice', // 총 실결제금액
                'totalGoodsPriceGlobal', // 총 상품금액(해외상점)
                'totalDeliveryChargeGlobal', // 총 배송비(해외상점)
                'totalDcPriceGlobal', // 총 할인금액(해외상점)
                'totalOrderPriceGlobal', // 총 주문금액(해외상점)
                'totalRealSettlePriceGlobal', // 총 실결제금액(해외상점)
                'settleKind', // 결제방법
                //'processStatus', // 처리상태
                'invoiceNo', // 송장번호
                'receiverName', // 수령자
                //'receiverAddress', // 수령자 주소
                'orderMemo', // 배송 메시지
                'receipt', // 영수증 신청여부
                'gift', // 사은품
                'adminMemo', // 관리자메모
                'phoneNumber', // 휴대폰 번호
            ],
        ],
        /*
        * 배송완료리스트 상품주문번호별
        */
        'list_delivery_ok_order_goods' => [
            'defaultList' => [ //디폴트 리스트
                'check', // 선택
                'no', // 번호
                'domainFl', // 상점구분
                'regDt', // 주문일시
                'orderName', // 주문자
                'orderGoodsNo', // 상품주문번호
                'orderGoodsNm', // 주문상품
                'goodsCnt', // 수량
                'goodsPrice', // 상품금액
                'totalGoodsPrice', // 총 상품금액
                'deliveryCharge', // 배송비
                'totalDeliveryCharge', // 총 배송비
                'totalOrderPrice', // 총 주문금액
                'settleKind', // 결제방법
                'processStatus', // 처리상태
                'invoiceNo', // 송장번호
                'gift', // 사은품
                'adminMemo', // 관리자메모
                'orderTypeFl', // 주문유형
            ],
            'list' => [ //전체리스트
                'check', // 선택
                'no', // 번호
                'domainFl', // 상점구분
                'regDt', // 주문일시
                'paymentDt', //입금일시
                'deliveryDt', //배송일시
                'deliveryCompleteDt', // 배송완료일시
                'orderNo', // 주문번호
                'orderName', // 주문자
                'orderTypeFl', // 주문유형
                'orderGoodsNo', // 상품주문번호
                'goodsCd', // 상품코드(자체상품코드)
                'goodsTaxInfo', // 상품 부가세
                'orderGoodsNm', // 주문상품
                'orderGoodsNmGlobal', // 주문상품(해외상점)
                'goodsCnt', // 수량
                'orgGoodsPrice', // 판매가 (판매가가 goodsPrice지만 상품금액에서 사용하고있어 래거쉬보장 문제로 orgGoodsPrice로 사용)
                'goodsPrice', // 상품금액
                'totalGoodsPrice', // 총 상품금액
                'deliveryCharge', // 배송비
                'totalDeliveryCharge', // 총 배송비
                'totalDcPrice', // 총 할인금액
                'totalUseAddedPrice', // 총 부가결제금액
                'totalOrderPrice', // 총 주문금액
                'totalRealSettlePrice', // 총 실결제금액
                'goodsPriceGlobal', // 상품금액(해외상점)
                'totalGoodsPriceGlobal', // 총 상품금액(해외상점)
                'deliveryChargeGlobal', // 배송비(해외상점)
                'totalDeliveryChargeGlobal', // 총 배송비(해외상점)
                'totalDcPriceGlobal', // 총 할인금액(해외상점)
                'totalOrderPriceGlobal', // 총 주문금액(해외상점)
                'totalRealSettlePriceGlobal', // 총 실결제금액(해외상점)
                'settleKind', // 결제방법
                'processStatus', // 처리상태
                'purchaseNm', // 매입처
                'brandNm', // 브랜드
                'goodsModelNo', // 모델명
                'makerNm', // 제조사
                'deliverySno', // 배송번호
                'scmNm', // 공급사
                'commission', // 수수료율
                'costPrice', // 매입가
                'invoiceNo', // 송장번호
                'receiverName', // 수령자
                //'receiverAddress', // 수령자 주소
                'multiShippingCd', // 배송지
                'orderMemo', // 배송 메시지
                'receipt', // 영수증 신청여부
                'hscode', // HS코드
                'gift', // 사은품
                'adminMemo', // 관리자메모
                'phoneNumber', // 휴대폰 번호
            ],
        ],

        /*
        * 구매확정리스트 주문번호별
        */
        'list_settle_order' => [
            'defaultList' => [ //디폴트 리스트
                'check', // 선택
                'no', // 번호
                'domainFl', // 상점구분
                'regDt', // 주문일시
                'orderNo', // 주문번호
                'orderName', // 주문자
                'orderGoodsNm', // 주문상품
                'totalGoodsPrice', // 총 상품금액
                'totalDeliveryCharge', // 총 배송비
                'totalOrderPrice', // 총 주문금액
                'settleKind', // 결제방법
                'invoiceNo', // 송장번호
                'gift', // 사은품
                'adminMemo', // 관리자메모
                'orderTypeFl', // 주문유형
            ],
            'list' => [ //전체리스트
                'check', // 선택
                'no', // 번호
                'domainFl', // 상점구분
                'regDt', // 주문일시
                'paymentDt', //입금일시
                'orderNo', // 주문번호
                'orderName', // 주문자
                'orderTypeFl', // 주문유형
                'orderGoodsNm', // 주문상품
                'orderGoodsNmGlobal', // 주문상품(해외상점)
                'goodsCnt', // 수량
                'totalGoodsPrice', // 총 상품금액
                'totalDeliveryCharge', // 총 배송비
                'totalDcPrice', // 총 할인금액
                'totalUseAddedPrice', // 총 부가결제금액
                'totalOrderPrice', // 총 주문금액
                'totalRealSettlePrice', // 총 실결제금액
                'totalGoodsPriceGlobal', // 총 상품금액(해외상점)
                'totalDeliveryChargeGlobal', // 총 배송비(해외상점)
                'totalDcPriceGlobal', // 총 할인금액(해외상점)
                'totalOrderPriceGlobal', // 총 주문금액(해외상점)
                'totalRealSettlePriceGlobal', // 총 실결제금액(해외상점)
                'settleKind', // 결제방법
                'processStatus', // 처리상태
                'invoiceNo', // 송장번호
                'receiverName', // 수령자
                //'receiverAddress', // 수령자 주소
                'orderMemo', // 배송 메시지
                'receipt', // 영수증 신청여부
                'gift', // 사은품
                'adminMemo', // 관리자메모
                'phoneNumber', // 휴대폰 번호
            ],
        ],
        /*
        * 구매확정리스트 상품주문번호별
        */
        'list_settle_order_goods' => [
            'defaultList' => [ //디폴트 리스트
                'check', // 선택
                'no', // 번호
                'domainFl', // 상점구분
                'regDt', // 주문일시
                'orderName', // 주문자
                'orderGoodsNo', // 상품주문번호
                'orderGoodsNm', // 주문상품
                'goodsCnt', // 수량
                'goodsPrice', // 상품금액
                'totalGoodsPrice', // 총 상품금액
                'deliveryCharge', // 배송비
                'totalDeliveryCharge', // 총 배송비
                'totalOrderPrice', // 총 주문금액
                'settleKind', // 결제방법
                'processStatus', // 처리상태
                'invoiceNo', // 송장번호
                'gift', // 사은품
                'adminMemo', // 관리자메모
                'orderTypeFl', // 주문유형
            ],
            'list' => [ //전체리스트
                'check', // 선택
                'no', // 번호
                'domainFl', // 상점구분
                'regDt', // 주문일시
                'paymentDt', //입금일시
                'deliveryDt', //배송일시
                'deliveryCompleteDt', // 배송완료일시
                'finishDt', // 구매확정일시
                'orderNo', // 주문번호
                'orderName', // 주문자
                'orderTypeFl', // 주문유형
                'orderGoodsNo', // 상품주문번호
                'goodsCd', // 상품코드(자체상품코드)
                'goodsTaxInfo', // 상품 부가세
                'orderGoodsNm', // 주문상품
                'orderGoodsNmGlobal', // 주문상품(해외상점)
                'goodsCnt', // 수량
                'orgGoodsPrice', // 판매가 (판매가가 goodsPrice지만 상품금액에서 사용하고있어 래거쉬보장 문제로 orgGoodsPrice로 사용)
                'goodsPrice', // 상품금액
                'totalGoodsPrice', // 총 상품금액
                'deliveryCharge', // 배송비
                'totalDeliveryCharge', // 총 배송비
                'totalDcPrice', // 총 할인금액
                'totalUseAddedPrice', // 총 부가결제금액
                'totalOrderPrice', // 총 주문금액
                'totalRealSettlePrice', // 총 실결제금액
                'goodsPriceGlobal', // 상품금액(해외상점)
                'totalGoodsPriceGlobal', // 총 상품금액(해외상점)
                'deliveryChargeGlobal', // 배송비(해외상점)
                'totalDeliveryChargeGlobal', // 총 배송비(해외상점)
                'totalDcPriceGlobal', // 총 할인금액(해외상점)
                'totalOrderPriceGlobal', // 총 주문금액(해외상점)
                'totalRealSettlePriceGlobal', // 총 실결제금액(해외상점)
                'settleKind', // 결제방법
                'processStatus', // 처리상태
                'purchaseNm', // 매입처
                'brandNm', // 브랜드
                'goodsModelNo', // 모델명
                'makerNm', // 제조사
                'deliverySno', // 배송번호
                'scmNm', // 공급사
                'commission', // 수수료율
                'costPrice', // 매입가
                'invoiceNo', // 송장번호
                'receiverName', // 수령자
                //'receiverAddress', // 수령자 주소
                'multiShippingCd', // 배송지
                'orderMemo', // 배송 메시지
                'receipt', // 영수증 신청여부
                'hscode', // HS코드
                'gift', // 사은품
                'adminMemo', // 관리자메모
                'phoneNumber', // 휴대폰 번호
            ],
        ],

        /*
        * 결제 중단/실패 리스트 주문번호별
        */
        'list_fail_order' => [
            'defaultList' => [ //디폴트 리스트
                'check', // 선택
                'no', // 번호
                'domainFl', // 상점구분
                'regDt', // 주문일시
                'orderNo', // 주문번호
                'orderName', // 주문자
                'orderGoodsNo', // 상품주문번호
                'orderGoodsNm', // 주문상품
                'goodsCnt', // 수량
                'goodsPrice', // 상품금액
                'totalGoodsPrice', // 총 상품금액
                'deliveryCharge', // 배송비
                'totalDeliveryCharge', // 총 배송비
                'totalOrderPrice', // 총 주문금액
                'settleKind', // 결제방법
                //'processStatus', // 처리상태
                'adminMemo', // 관리자메모
                'orderTypeFl', // 주문유형
            ],
            'list' => [ //전체리스트
                'check', // 선택
                'no', // 번호
                'domainFl', // 상점구분
                'regDt', // 주문일시
                'orderNo', // 주문번호
                'orderName', // 주문자
                'orderTypeFl', // 주문유형
                'orderGoodsNo', // 상품주문번호
                'goodsCd', // 상품코드(자체상품코드)
                'goodsTaxInfo', // 상품 부가세
                'orderGoodsNm', // 주문상품
                'orderGoodsNmGlobal', // 주문상품(해외상점)
                'goodsCnt', // 수량
                'orgGoodsPrice', // 판매가 (판매가가 goodsPrice지만 상품금액에서 사용하고있어 래거쉬보장 문제로 orgGoodsPrice로 사용)
                'goodsPrice', // 상품금액
                'totalGoodsPrice', // 총 상품금액
                'deliveryCharge', // 배송비
                'totalDeliveryCharge', // 총 배송비
                'totalDcPrice', // 총 할인금액
                'totalUseAddedPrice', // 총 부가결제금액
                'totalOrderPrice', // 총 주문금액
                'totalRealSettlePrice', // 총 실결제금액
                'goodsPriceGlobal', // 상품금액(해외상점)
                'totalGoodsPriceGlobal', // 총 상품금액(해외상점)
                'deliveryChargeGlobal', // 배송비(해외상점)
                'totalDeliveryChargeGlobal', // 총 배송비(해외상점)
                'totalDcPriceGlobal', // 총 할인금액(해외상점)
                'totalOrderPriceGlobal', // 총 주문금액(해외상점)
                'totalRealSettlePriceGlobal', // 총 실결제금액(해외상점)
                'settleKind', // 결제방법
                //'processStatus', // 처리상태
                'purchaseNm', // 매입처
                'brandNm', // 브랜드
                'goodsModelNo', // 모델명
                'makerNm', // 제조사
                'deliverySno', // 배송번호
                'scmNm', // 공급사
                'commission', // 수수료율
                'costPrice', // 매입가
                'invoiceNo', // 송장번호
                'receiverName', // 수령자
                //'receiverAddress', // 수령자 주소
                'multiShippingCd', // 배송지
                'orderMemo', // 배송 메시지
                'receipt', // 영수증 신청여부
                'hscode', // HS코드
                'gift', // 사은품
                'adminMemo', // 관리자메모
                'phoneNumber', // 휴대폰 번호
            ],
        ],

        /*
        * 취소리스트
        */
        'list_cancel_order' => [
            'defaultList' => [ //디폴트 리스트
                'check', // 선택
                'no', // 번호
                'domainFl', // 상점구분
                'regDt', // 주문일시
                'cancelDt', // 취소신청일(취소접수일)
                'cancelCompleteDt', // 취소완료일시
                'orderNo', // 주문번호
                'orderName', // 주문자
                'orderGoodsNo', // 상품주문번호
                'orderGoodsNm', // 주문상품
                'goodsCnt', // 수량
                'goodsPrice', // 상품금액
                'totalGoodsPrice', // 총 상품금액
                'deliveryCharge', // 배송비
                'totalDeliveryCharge', // 총 배송비
                //'totalUseAddedRefundPrice', // 총 부가결제 환원금액
                //'totalCancelPrice', // 총 취소금액
                'settleKind', // 결제방법
                'processStatus', // 처리상태
                'reason', // 사유
                //'processor', // 처리자
                'adminMemo', // 관리자메모
                'orderTypeFl', // 주문유형
            ],
            'list' => [ //전체리스트
                'check', // 선택
                'no', // 번호
                'domainFl', // 상점구분
                'regDt', // 주문일시
                'cancelDt', // 취소신청일(취소접수일)
                'cancelCompleteDt', // 취소완료일시
                'orderNo', // 주문번호
                'orderName', // 주문자
                'orderTypeFl', // 주문유형
                'orderGoodsNo', // 상품주문번호
                'goodsCd', // 상품코드(자체상품코드)
                'goodsTaxInfo', // 상품 부가세
                'orderGoodsNm', // 주문상품
                'orderGoodsNmGlobal', // 주문상품(해외상점)
                'goodsCnt', // 수량
                'orgGoodsPrice', // 판매가 (판매가가 goodsPrice지만 상품금액에서 사용하고있어 래거쉬보장 문제로 orgGoodsPrice로 사용)
                'goodsPrice', // 상품금액
                'totalGoodsPrice', // 총 상품금액
                'deliveryCharge', // 배송비
                'totalDeliveryCharge', // 총 배송비
                //'totalUseAddedRefundPrice', // 총 부가결제 환원금액
                //'totalCancelPrice', // 총 취소금액
                'goodsPriceGlobal', // 상품금액(해외상점)
                'totalGoodsPriceGlobal', // 총 상품금액(해외상점)
                'deliveryChargeGlobal', // 배송비(해외상점)
                'totalDeliveryChargeGlobal', // 총 배송비(해외상점)
                //'totalCancelPriceGlobal', // 총 취소금액(해외상점)
                'settleKind', // 결제방법
                'processStatus', // 처리상태
                'reason', // 사유
                'purchaseNm', // 매입처
                'brandNm', // 브랜드
                'goodsModelNo', // 모델명
                'makerNm', // 제조사
                'deliverySno', // 배송번호
                'scmNm', // 공급사
                'commission', // 수수료율
                'costPrice', // 매입가
                'invoiceNo', // 송장번호
                'receiverName', // 수령자
                //'receiverAddress', // 수령자 주소
                'multiShippingCd', // 배송지
                'orderMemo', // 배송 메시지
                'receipt', // 영수증 신청여부
                'hscode', // HS코드
                'gift', // 사은품
                //'processor', // 처리자
                'adminMemo', // 관리자메모
                'phoneNumber', // 휴대폰 번호
            ],
        ],

        /*
        * 교환리스트
        */
        'list_exchange_order' => [
            'defaultList' => [ //디폴트 리스트
                'check', // 선택
                'no', // 번호
                'domainFl', // 상점구분
                'regDt', // 주문일시
                'exchangeDt', // 교환신청일(교환접수일)
                'exchangeCompleteDt', // 교환완료일시
                'orderNo', // 주문번호
                'orderName', // 주문자
                'orderGoodsNo', // 상품주문번호
                'orderGoodsNm', // 주문상품
                'goodsCnt', // 수량
                'goodsPrice', // 상품금액
                'totalGoodsPrice', // 총 상품금액
                'deliveryCharge', // 배송비
                'totalDeliveryCharge', // 총 배송비
                'settleKind', // 결제방법
                'processStatus', // 처리상태
                'reason', // 사유
                //'processor', // 처리자
                'adminMemo', // 관리자메모
                'orderTypeFl', // 주문유형
            ],
            'list' => [ //전체리스트
                'check', // 선택
                'no', // 번호
                'domainFl', // 상점구분
                'regDt', // 주문일시
                'exchangeDt', // 교환신청일(교환접수일)
                'exchangeCompleteDt', // 교환완료일시
                'orderNo', // 주문번호
                'orderName', // 주문자
                'orderTypeFl', // 주문유형
                'orderGoodsNo', // 상품주문번호
                'goodsCd', // 상품코드(자체상품코드)
                'goodsTaxInfo', // 상품 부가세
                'orderGoodsNm', // 주문상품
                'orderGoodsNmGlobal', // 주문상품(해외상점)
                'goodsCnt', // 수량
                'orgGoodsPrice', // 판매가 (판매가가 goodsPrice지만 상품금액에서 사용하고있어 래거쉬보장 문제로 orgGoodsPrice로 사용)
                'goodsPrice', // 상품금액
                'totalGoodsPrice', // 총 상품금액
                'deliveryCharge', // 배송비
                'totalDeliveryCharge', // 총 배송비
                'goodsPriceGlobal', // 상품금액(해외상점)
                'totalGoodsPriceGlobal', // 총 상품금액(해외상점)
                'deliveryChargeGlobal', // 배송비(해외상점)
                'totalDeliveryChargeGlobal', // 총 배송비(해외상점)
                'settleKind', // 결제방법
                'processStatus', // 처리상태
                'reason', // 사유
                'purchaseNm', // 매입처
                'brandNm', // 브랜드
                'goodsModelNo', // 모델명
                'makerNm', // 제조사
                'deliverySno', // 배송번호
                'scmNm', // 공급사
                'commission', // 수수료율
                'costPrice', // 매입가
                'invoiceNo', // 송장번호
                'receiverName', // 수령자
                //'receiverAddress', // 수령자 주소
                'multiShippingCd', // 배송지
                'orderMemo', // 배송 메시지
                'receipt', // 영수증 신청여부
                'hscode', // HS코드
                'gift', // 사은품
                //'processor', // 처리자
                'adminMemo', // 관리자메모
                'phoneNumber', // 휴대폰 번호
            ],
        ],

        /*
        * 교환취소리스트
        */
        'list_exchange_cancel_order' => [
            'defaultList' => [ //디폴트 리스트
                'check', // 선택
                'no', // 번호
                'domainFl', // 상점구분
                'regDt', // 주문일시
                'exchangeDt', // 교환신청일(교환접수일)
                'exchangeCompleteDt', // 교환완료일시
                'orderNo', // 주문번호
                'orderName', // 주문자
                'orderGoodsNo', // 상품주문번호
                'orderGoodsNm', // 주문상품
                'goodsCnt', // 수량
                'goodsPrice', // 상품금액
                'totalGoodsPrice', // 총 상품금액
                'deliveryCharge', // 배송비
                'totalDeliveryCharge', // 총 배송비
                'settleKind', // 결제방법
                'processStatus', // 처리상태
                'reason', // 사유
                //'processor', // 처리자
                'adminMemo', // 관리자메모
                'orderTypeFl', // 주문유형
            ],
            'list' => [ //전체리스트
                'check', // 선택
                'no', // 번호
                'domainFl', // 상점구분
                'regDt', // 주문일시
                'exchangeDt', // 교환신청일(교환접수일)
                'exchangeCompleteDt', // 교환완료일시
                'orderNo', // 주문번호
                'orderName', // 주문자
                'orderTypeFl', // 주문유형
                'orderGoodsNo', // 상품주문번호
                'goodsCd', // 상품코드(자체상품코드)
                'goodsTaxInfo', // 상품 부가세
                'orderGoodsNm', // 주문상품
                'orderGoodsNmGlobal', // 주문상품(해외상점)
                'goodsCnt', // 수량
                'goodsPrice', // 상품금액
                'totalGoodsPrice', // 총 상품금액
                'deliveryCharge', // 배송비
                'totalDeliveryCharge', // 총 배송비
                'goodsPriceGlobal', // 상품금액(해외상점)
                'totalGoodsPriceGlobal', // 총 상품금액(해외상점)
                'deliveryChargeGlobal', // 배송비(해외상점)
                'totalDeliveryChargeGlobal', // 총 배송비(해외상점)
                'settleKind', // 결제방법
                'processStatus', // 처리상태
                'reason', // 사유
                'purchaseNm', // 매입처
                'brandNm', // 브랜드
                'goodsModelNo', // 모델명
                'makerNm', // 제조사
                'deliverySno', // 배송번호
                'scmNm', // 공급사
                'commission', // 수수료율
                'costPrice', // 매입가
                'invoiceNo', // 송장번호
                'receiverName', // 수령자
                //'receiverAddress', // 수령자 주소
                'multiShippingCd', // 배송지
                'orderMemo', // 배송 메시지
                'receipt', // 영수증 신청여부
                'hscode', // HS코드
                'gift', // 사은품
                //'processor', // 처리자
                'adminMemo', // 관리자메모
                'phoneNumber', // 휴대폰 번호
            ],
        ],

        /*
        * 교환추가리스트
        */
        'list_exchange_add_order' => [
            'defaultList' => [ //디폴트 리스트
                'check', // 선택
                'no', // 번호
                'domainFl', // 상점구분
                'regDt', // 주문일시
                'exchangeDt', // 교환신청일(교환접수일)
                'exchangeCompleteDt', // 교환완료일시
                'orderNo', // 주문번호
                'orderName', // 주문자
                'orderGoodsNo', // 상품주문번호
                'orderGoodsNm', // 주문상품
                'goodsCnt', // 수량
                'goodsPrice', // 상품금액
                'totalGoodsPrice', // 총 상품금액
                'totalDeliveryCharge', // 총 배송비
                'settleKind', // 결제방법
                'processStatus', // 처리상태
                'reason', // 사유
                //'processor', // 처리자
                'adminMemo', // 관리자메모
                'orderTypeFl', // 주문유형
            ],
            'list' => [ //전체리스트
                'check', // 선택
                'no', // 번호
                'domainFl', // 상점구분
                'regDt', // 주문일시
                'exchangeDt', // 교환신청일(교환접수일)
                'exchangeCompleteDt', // 교환완료일시
                'orderNo', // 주문번호
                'orderName', // 주문자
                'orderTypeFl', // 주문유형
                'orderGoodsNo', // 상품주문번호
                'goodsCd', // 상품코드(자체상품코드)
                'goodsTaxInfo', // 상품 부가세
                'orderGoodsNm', // 주문상품
                'orderGoodsNmGlobal', // 주문상품(해외상점)
                'goodsCnt', // 수량
                'goodsPrice', // 상품금액
                'totalGoodsPrice', // 총 상품금액
                'totalDeliveryCharge', // 총 배송비
                'goodsPriceGlobal', // 상품금액(해외상점)
                'totalGoodsPriceGlobal', // 총 상품금액(해외상점)
                'deliveryChargeGlobal', // 배송비(해외상점)
                'totalDeliveryChargeGlobal', // 총 배송비(해외상점)
                'settleKind', // 결제방법
                'processStatus', // 처리상태
                'reason', // 사유
                'purchaseNm', // 매입처
                'brandNm', // 브랜드
                'goodsModelNo', // 모델명
                'makerNm', // 제조사
                'deliverySno', // 배송번호
                'scmNm', // 공급사
                'commission', // 수수료율
                'costPrice', // 매입가
                'invoiceNo', // 송장번호
                'receiverName', // 수령자
                //'receiverAddress', // 수령자 주소
                'multiShippingCd', // 배송지
                'orderMemo', // 배송 메시지
                'receipt', // 영수증 신청여부
                'hscode', // HS코드
                'gift', // 사은품
                //'processor', // 처리자
                'adminMemo', // 관리자메모
                'phoneNumber', // 휴대폰 번호
            ],
        ],

        /*
        * 반품리스트
        */
        'list_back_order' => [
            'defaultList' => [ //디폴트 리스트
                'check', // 선택
                'no', // 번호
                'domainFl', // 상점구분
                'regDt', // 주문일시
                'backDt', // 반품신청일(반품접수일)
                'backCompleteDt', // 반품완료일시
                'orderNo', // 주문번호
                'orderName', // 주문자
                'orderGoodsNo', // 상품주문번호
                'orderGoodsNm', // 주문상품
                'goodsCnt', // 수량
                'goodsPrice', // 상품금액
                'totalGoodsPrice', // 총 상품금액
                'deliveryCharge', // 배송비
                'totalDeliveryCharge', // 총 배송비
                'settleKind', // 결제방법
                'processStatus', // 처리상태
                'reason', // 사유
                //'processor', // 처리자
                'adminMemo', // 관리자메모
                'orderTypeFl', // 주문유형
            ],
            'list' => [ //전체리스트
                'check', // 선택
                'no', // 번호
                'domainFl', // 상점구분
                'regDt', // 주문일시
                'backDt', // 반품신청일(반품접수일)
                'backCompleteDt', // 반품완료일시
                'orderNo', // 주문번호
                'orderName', // 주문자
                'orderTypeFl', // 주문유형
                'orderGoodsNo', // 상품주문번호
                'goodsCd', // 상품코드(자체상품코드)
                'goodsTaxInfo', // 상품 부가세
                'orderGoodsNm', // 주문상품
                'orderGoodsNmGlobal', // 주문상품(해외상점)
                'goodsCnt', // 수량
                'orgGoodsPrice', // 판매가 (판매가가 goodsPrice지만 상품금액에서 사용하고있어 래거쉬보장 문제로 orgGoodsPrice로 사용)
                'goodsPrice', // 상품금액
                'totalGoodsPrice', // 총 상품금액
                'deliveryCharge', // 배송비
                'totalDeliveryCharge', // 총 배송비
                'goodsPriceGlobal', // 상품금액(해외상점)
                'totalGoodsPriceGlobal', // 총 상품금액(해외상점)
                'deliveryChargeGlobal', // 배송비(해외상점)
                'totalDeliveryChargeGlobal', // 총 배송비(해외상점)
                'settleKind', // 결제방법
                'processStatus', // 처리상태
                'reason', // 사유
                'purchaseNm', // 매입처
                'brandNm', // 브랜드
                'goodsModelNo', // 모델명
                'makerNm', // 제조사
                'deliverySno', // 배송번호
                'scmNm', // 공급사
                'commission', // 수수료율
                'costPrice', // 매입가
                'invoiceNo', // 송장번호
                'receiverName', // 수령자
                //'receiverAddress', // 수령자 주소
                'multiShippingCd', // 배송지
                'orderMemo', // 배송 메시지
                'receipt', // 영수증 신청여부
                'hscode', // HS코드
                'gift', // 사은품
                //'processor', // 처리자
                'adminMemo', // 관리자메모
                'phoneNumber', // 휴대폰 번호
            ],
        ],

        /*
        * 환불리스트
        */
        'list_refund_order' => [
            'defaultList' => [ //디폴트 리스트
                'check', // 선택
                'no', // 번호
                'domainFl', // 상점구분
                'regDt', // 주문일시
                'refundDt', // 환불신청일(환불접수일)
                'refundCompleteDt', // 환불완료일시
                'orderNo', // 주문번호
                'orderName', // 주문자
                'orderGoodsNo', // 상품주문번호
                'orderGoodsNm', // 주문상품
                'goodsCnt', // 수량
                'goodsPrice', // 상품금액
                'totalGoodsPrice', // 총 상품금액
                'deliveryCharge', // 배송비
                'totalDeliveryCharge', // 총 배송비
                //'totalUseAddedRefundPrice', // 총 부가결제 환원금액
                //'totalRefundPrice', // 총 환불금액
                'settleKind', // 결제방법
                'processStatus', // 처리상태
                'reason', // 사유
                //'processor', // 처리자
                'refundMethod', // 환불수단
                'refundStatus', // 환불처리
                'adminMemo', // 관리자메모
                'orderTypeFl', // 주문유형
            ],
            'list' => [ //전체리스트
                'check', // 선택
                'no', // 번호
                'domainFl', // 상점구분
                'regDt', // 주문일시
                'refundDt', // 환불신청일(환불접수일)
                'refundCompleteDt', // 환불완료일시
                'orderNo', // 주문번호
                'orderName', // 주문자
                'orderTypeFl', // 주문유형
                'orderGoodsNo', // 상품주문번호
                'goodsCd', // 상품코드(자체상품코드)
                'goodsTaxInfo', // 상품 부가세
                'orderGoodsNm', // 주문상품
                'orderGoodsNmGlobal', // 주문상품(해외상점)
                'goodsCnt', // 수량
                'orgGoodsPrice', // 판매가 (판매가가 goodsPrice지만 상품금액에서 사용하고있어 래거쉬보장 문제로 orgGoodsPrice로 사용)
                'goodsPrice', // 상품금액
                'totalGoodsPrice', // 총 상품금액
                'deliveryCharge', // 배송비
                'totalDeliveryCharge', // 총 배송비
                //'totalUseAddedRefundPrice', // 총 부가결제 환원금액
                //'totalRefundPrice', // 총 환불금액
                'goodsPriceGlobal', // 상품금액(해외상점)
                'totalGoodsPriceGlobal', // 총 상품금액(해외상점)
                'deliveryChargeGlobal', // 배송비(해외상점)
                'totalDeliveryChargeGlobal', // 총 배송비(해외상점)
                //'totalRefundPriceGlobal', // 총 환불금액(해외상점)
                'settleKind', // 결제방법
                'processStatus', // 처리상태
                'reason', // 사유
                'purchaseNm', // 매입처
                'brandNm', // 브랜드
                'goodsModelNo', // 모델명
                'makerNm', // 제조사
                'deliverySno', // 배송번호
                'scmNm', // 공급사
                'commission', // 수수료율
                'costPrice', // 매입가
                'invoiceNo', // 송장번호
                'receiverName', // 수령자
                //'receiverAddress', // 수령자 주소
                'multiShippingCd', // 배송지
                'orderMemo', // 배송 메시지
                'receipt', // 영수증 신청여부
                'hscode', // HS코드
                'gift', // 사은품
                //'processor', // 처리자
                'refundMethod', // 환불수단
                'refundStatus', // 환불처리
                'adminMemo', // 관리자메모
                'phoneNumber', // 휴대폰 번호
            ],
        ],

        /*
        * 고객 교환/반품/환불신청 관리 - 교환
        */
        'list_user_exchange' => [
            'defaultList' => [ //디폴트 리스트
                'check', // 선택
                'no', // 번호
                'domainFl', // 상점구분
                'regDt', // 주문일시
                'userHandleRegDt', // 신청일시
                'userHandleActDt', // 처리일시
                'userHandleReason', // 사유
                'orderNo', // 주문번호
                'orderName', // 주문자
                'orderGoodsNo', // 상품주문번호
                'orderGoodsNm', // 주문상품
                'userHandleOrderStatus', // 주문상태
                'userHandleGoodsCnt', // 신청수량
                'goodsPrice', // 상품금액
                'totalGoodsPrice', // 총 상품금액
                'deliveryCharge', // 배송비
                'totalDeliveryCharge', // 총 배송비
                'settleKind', // 결제방법
                'processStatus', // 처리상태
                //'processor', // 처리자
                'adminMemo', // 관리자메모
                'orderTypeFl', // 주문유형
            ],
            'list' => [ //전체리스트
                'check', // 선택
                'no', // 번호
                'domainFl', // 상점구분
                'regDt', // 주문일시
                'userHandleRegDt', // 신청일시
                'userHandleActDt', // 처리일시
                'userHandleReason', // 사유
                'orderNo', // 주문번호
                'orderName', // 주문자
                'orderTypeFl', // 주문유형
                'orderGoodsNo', // 상품주문번호
                'orderGoodsNm', // 주문상품
                'goodsCd', // 상품코드(자체상품코드)
                'goodsTaxInfo', // 상품 부가세
                'orderGoodsNmGlobal', // 주문상품(해외상점)
                'userHandleOrderStatus', // 주문상태
                'userHandleGoodsCnt', // 신청수량
                'orgGoodsPrice', // 판매가 (판매가가 goodsPrice지만 상품금액에서 사용하고있어 래거쉬보장 문제로 orgGoodsPrice로 사용)
                'goodsPrice', // 상품금액
                'totalGoodsPrice', // 총 상품금액
                'deliveryCharge', // 배송비
                'totalDeliveryCharge', // 총 배송비
                'goodsPriceGlobal', // 상품금액(해외상점)
                'totalGoodsPriceGlobal', // 총 상품금액(해외상점)
                'deliveryChargeGlobal', // 배송비(해외상점)
                'totalDeliveryChargeGlobal', // 총 배송비(해외상점)
                'settleKind', // 결제방법
                'processStatus', // 처리상태
                'purchaseNm', // 매입처
                'brandNm', // 브랜드
                'goodsModelNo', // 모델명
                'makerNm', // 제조사
                'deliverySno', // 배송번호
                'scmNm', // 공급사
                'commission', // 수수료율
                'costPrice', // 매입가
                'invoiceNo', // 송장번호
                'receiverName', // 수령자
                //'receiverAddress', // 수령자 주소
                'multiShippingCd', // 배송지
                'orderMemo', // 배송 메시지
                'receipt', // 영수증 신청여부
                'hscode', // HS코드
                'gift', // 사은품
                //'processor', // 처리자
                'memo', // 메모
                'adminMemo', // 관리자메모
                'phoneNumber', // 휴대폰 번호
            ],
        ],

        /*
        * 고객 교환/반품/환불신청 관리 - 반품
        */
        'list_user_back' => [
            'defaultList' => [ //디폴트 리스트
                'check', // 선택
                'no', // 번호
                'domainFl', // 상점구분
                'regDt', // 주문일시
                'userHandleRegDt', // 신청일시
                'userHandleActDt', // 처리일시
                'userHandleReason', // 사유
                'orderNo', // 주문번호
                'orderName', // 주문자
                'orderGoodsNo', // 상품주문번호
                'orderGoodsNm', // 주문상품
                'userHandleOrderStatus', // 주문상태
                'userHandleGoodsCnt', // 신청수량
                'goodsPrice', // 상품금액
                'totalGoodsPrice', // 총 상품금액
                'deliveryCharge', // 배송비
                'totalDeliveryCharge', // 총 배송비
                'settleKind', // 결제방법
                'processStatus', // 처리상태
                //'processor', // 처리자
                'adminMemo', // 관리자메모
                'orderTypeFl', // 주문유형
            ],
            'list' => [ //전체리스트
                'check', // 선택
                'no', // 번호
                'domainFl', // 상점구분
                'regDt', // 주문일시
                'userHandleRegDt', // 신청일시
                'userHandleActDt', // 처리일시
                'userHandleReason', // 사유
                'orderNo', // 주문번호
                'orderName', // 주문자
                'orderTypeFl', // 주문유형
                'orderGoodsNo', // 상품주문번호
                'orderGoodsNm', // 주문상품
                'goodsCd', // 상품코드(자체상품코드)
                'goodsTaxInfo', // 상품 부가세
                'orderGoodsNmGlobal', // 주문상품(해외상점)
                'userHandleOrderStatus', // 주문상태
                'userHandleGoodsCnt', // 신청수량
                'orgGoodsPrice', // 판매가 (판매가가 goodsPrice지만 상품금액에서 사용하고있어 래거쉬보장 문제로 orgGoodsPrice로 사용)
                'goodsPrice', // 상품금액
                'totalGoodsPrice', // 총 상품금액
                'deliveryCharge', // 배송비
                'totalDeliveryCharge', // 총 배송비
                'goodsPriceGlobal', // 상품금액(해외상점)
                'totalGoodsPriceGlobal', // 총 상품금액(해외상점)
                'deliveryChargeGlobal', // 배송비(해외상점)
                'totalDeliveryChargeGlobal', // 총 배송비(해외상점)
                'settleKind', // 결제방법
                'processStatus', // 처리상태
                'purchaseNm', // 매입처
                'brandNm', // 브랜드
                'goodsModelNo', // 모델명
                'makerNm', // 제조사
                'deliverySno', // 배송번호
                'scmNm', // 공급사
                'commission', // 수수료율
                'costPrice', // 매입가
                'invoiceNo', // 송장번호
                'receiverName', // 수령자
                //'receiverAddress', // 수령자 주소
                'multiShippingCd', // 배송지
                'orderMemo', // 배송 메시지
                'receipt', // 영수증 신청여부
                'hscode', // HS코드
                'gift', // 사은품
                //'processor', // 처리자
                'memo', // 메모
                'adminMemo', // 관리자메모
                'phoneNumber', // 휴대폰 번호
            ],
        ],

        /*
        * 고객 교환/반품/환불신청 관리 - 환불
        */
        'list_user_refund' => [
            'defaultList' => [ //디폴트 리스트
                'check', // 선택
                'no', // 번호
                'domainFl', // 상점구분
                'regDt', // 주문일시
                'userHandleRegDt', // 신청일시
                'userHandleActDt', // 처리일시
                'userHandleReason', // 사유
                'orderNo', // 주문번호
                'orderName', // 주문자
                'orderGoodsNo', // 상품주문번호
                'orderGoodsNm', // 주문상품
                'userHandleOrderStatus', // 주문상태
                'userHandleGoodsCnt', // 신청수량
                'goodsPrice', // 상품금액
                'totalGoodsPrice', // 총 상품금액
                'deliveryCharge', // 배송비
                'totalDeliveryCharge', // 총 배송비
                'settleKind', // 결제방법
                'processStatus', // 처리상태
                //'processor', // 처리자
                'adminMemo', // 관리자메모
                'orderTypeFl', // 주문유형
            ],
            'list' => [ //전체리스트
                'check', // 선택
                'no', // 번호
                'domainFl', // 상점구분
                'regDt', // 주문일시
                'userHandleRegDt', // 신청일시
                'userHandleActDt', // 처리일시
                'userHandleReason', // 사유
                'orderNo', // 주문번호
                'orderName', // 주문자
                'orderTypeFl', // 주문유형
                'orderGoodsNo', // 상품주문번호
                'orderGoodsNm', // 주문상품
                'goodsCd', // 상품코드(자체상품코드)
                'goodsTaxInfo', // 상품 부가세
                'orderGoodsNmGlobal', // 주문상품(해외상점)
                'userHandleOrderStatus', // 주문상태
                'userHandleGoodsCnt', // 신청수량
                'orgGoodsPrice', // 판매가 (판매가가 goodsPrice지만 상품금액에서 사용하고있어 래거쉬보장 문제로 orgGoodsPrice로 사용)
                'goodsPrice', // 상품금액
                'totalGoodsPrice', // 총 상품금액
                'deliveryCharge', // 배송비
                'totalDeliveryCharge', // 총 배송비
                'goodsPriceGlobal', // 상품금액(해외상점)
                'totalGoodsPriceGlobal', // 총 상품금액(해외상점)
                'deliveryChargeGlobal', // 배송비(해외상점)
                'totalDeliveryChargeGlobal', // 총 배송비(해외상점)
                'settleKind', // 결제방법
                'processStatus', // 처리상태
                'purchaseNm', // 매입처
                'brandNm', // 브랜드
                'goodsModelNo', // 모델명
                'makerNm', // 제조사
                'deliverySno', // 배송번호
                'scmNm', // 공급사
                'commission', // 수수료율
                'costPrice', // 매입가
                'invoiceNo', // 송장번호
                'receiverName', // 수령자
                //'receiverAddress', // 수령자 주소
                'multiShippingCd', // 배송지
                'orderMemo', // 배송 메시지
                'receipt', // 영수증 신청여부
                'hscode', // HS코드
                'gift', // 사은품
                //'processor', // 처리자
                'memo', // 메모
                'adminMemo', // 관리자메모
                'phoneNumber', // 휴대폰 번호
            ],
        ],

        /*
        * 주문상세페이지 - 주문내역
        */
        'view_order' => [
            'defaultList' => [ //디폴트 리스트
                'check', // 선택
                'no', // 번호
                'orderGoodsNo', // 상품주문번호
                'goodsImage', // 이미지
                'orderGoodsNm', // 주문상품
                'goodsCnt', // 수량
                'goodsPrice', // 상품금액
                'totalDcPrice', // 총 할인금액
                'totalUseAddedPrice', // 총 부가결제금액
                'deliveryCharge', // 배송비
                'totalOrderPrice', // 총 주문금액
                'invoiceNo', // 송장번호
                'processStatus', // 처리상태
                'adminMemo', // 관리자메모
            ],
            'list' => [ //전체리스트
                'check', // 선택
                'no', // 번호
                'orderGoodsNo', // 상품주문번호
                'goodsCd', // 상품코드(자체상품코드)
                'goodsTaxInfo', // 상품 부가세
                'goodsImage', // 이미지
                'orderGoodsNm', // 주문상품
                'orderGoodsNmGlobal', // 주문상품(해외상점)
                'goodsCnt', // 수량
                'orgGoodsPrice', // 판매가 (판매가가 goodsPrice지만 상품금액에서 사용하고있어 래거쉬보장 문제로 orgGoodsPrice로 사용)
                'goodsPrice', // 상품금액
                'totalGoodsPrice', // 총 상품금액
                'costPrice', // 매입가
                'totalDcPrice', // 총 할인금액
                'totalUseAddedPrice', // 총 부가결제금액
                'deliveryCharge', // 배송비
                'totalDeliveryCharge', // 총 배송비
                'totalOrderPrice', // 총 주문금액
                'purchaseNm', // 매입처
                'brandNm', // 브랜드
                'goodsModelNo', // 모델명
                'makerNm', // 제조사
                'scmNm', // 공급사
                'commission', // 수수료율
                'hscode', // HS코드
                'invoiceNo', // 송장번호
                'multiShippingCd', // 배송지
                'processStatus', // 처리상태
                'adminMemo', // 관리자메모
            ],
        ],

        /*
        * 주문상세페이지 - 취소내역
        */
        'view_cancel' => [
            'defaultList' => [ //디폴트 리스트
                'check', // 선택
                'no', // 번호
                'orderGoodsNo', // 상품주문번호
                'goodsImage', // 이미지
                'orderGoodsNm', // 주문상품
                'goodsCnt', // 수량
                'goodsPrice', // 상품금액
                'deliveryCharge', // 배송비
                'totalOrderPrice', // 총 주문금액
                //'totalUseAddedRefundPrice', // 총 부가결제 환원금액
                //'totalCancelPrice', // 총 취소금액
                'invoiceNo', // 송장번호
                'processStatus', // 처리상태
                'adminMemo', // 관리자메모
            ],
            'list' => [ //전체리스트
                'check', // 선택
                'no', // 번호
                'orderGoodsNo', // 상품주문번호
                'goodsCd', // 상품코드(자체상품코드)
                'goodsTaxInfo', // 상품 부가세
                'goodsImage', // 이미지
                'orderGoodsNm', // 주문상품
                'orderGoodsNmGlobal', // 주문상품(해외상점)
                'goodsCnt', // 수량
                'orgGoodsPrice', // 판매가 (판매가가 goodsPrice지만 상품금액에서 사용하고있어 래거쉬보장 문제로 orgGoodsPrice로 사용)
                'goodsPrice', // 상품금액
                'totalGoodsPrice', // 총 상품금액
                'costPrice', // 매입가
                'deliveryCharge', // 배송비
                'totalDeliveryCharge', // 총 배송비
                'totalOrderPrice', // 총 주문금액
                //'totalUseAddedRefundPrice', // 총 부가결제 환원금액
                //'totalCancelPrice', // 총 취소금액
                'purchaseNm', // 매입처
                'brandNm', // 브랜드
                'goodsModelNo', // 모델명
                'makerNm', // 제조사
                'scmNm', // 공급사
                'commission', // 수수료율
                'hscode', // HS코드
                'invoiceNo', // 송장번호
                'multiShippingCd', // 배송지
                'processStatus', // 처리상태
                'adminMemo', // 관리자메모
            ],
        ],

        /*
        * 주문상세페이지 - 교환내역
        */
        'view_exchange' => [
            'defaultList' => [ //디폴트 리스트
                'check', // 선택
                'no', // 번호
                'orderGoodsNo', // 상품주문번호
                'goodsImage', // 이미지
                'orderGoodsNm', // 주문상품
                'goodsCnt', // 수량
                'goodsPrice', // 상품금액
                'deliveryCharge', // 배송비
                'totalOrderPrice', // 총 주문금액
                'invoiceNo', // 송장번호
                'processStatus', // 처리상태
            ],
            'list' => [ //전체리스트
                'check', // 선택
                'no', // 번호
                'orderGoodsNo', // 상품주문번호
                'goodsCd', // 상품코드(자체상품코드)
                'goodsTaxInfo', // 상품 부가세
                'goodsImage', // 이미지
                'orderGoodsNm', // 주문상품
                'orderGoodsNmGlobal', // 주문상품(해외상점)
                'goodsCnt', // 수량
                'orgGoodsPrice', // 판매가 (판매가가 goodsPrice지만 상품금액에서 사용하고있어 래거쉬보장 문제로 orgGoodsPrice로 사용)
                'goodsPrice', // 상품금액
                'totalGoodsPrice', // 총 상품금액
                'costPrice', // 매입가
                'deliveryCharge', // 배송비
                'totalDeliveryCharge', // 총 배송비
                'totalOrderPrice', // 총 주문금액
                'purchaseNm', // 매입처
                'brandNm', // 브랜드
                'goodsModelNo', // 모델명
                'makerNm', // 제조사
                'scmNm', // 공급사
                'commission', // 수수료율
                'hscode', // HS코드
                'invoiceNo', // 송장번호
                'multiShippingCd', // 배송지
                'processStatus', // 처리상태
            ],
        ],

        /*
        * 주문상세페이지 - 교환취소내역
        */
        'view_exchangeCancel' => [
            'defaultList' => [ //디폴트 리스트
                'check', // 선택
                'no', // 번호
                'orderGoodsNo', // 상품주문번호
                'goodsImage', // 이미지
                'orderGoodsNm', // 주문상품
                'goodsCnt', // 수량
                'goodsPrice', // 상품금액
                'deliveryCharge', // 배송비
                'totalOrderPrice', // 총 주문금액
                'invoiceNo', // 송장번호
                'processStatus', // 처리상태
            ],
            'list' => [ //전체리스트
                'check', // 선택
                'no', // 번호
                'orderGoodsNo', // 상품주문번호
                'goodsCd', // 상품코드(자체상품코드)
                'goodsTaxInfo', // 상품 부가세
                'goodsImage', // 이미지
                'orderGoodsNm', // 주문상품
                'orderGoodsNmGlobal', // 주문상품(해외상점)
                'goodsCnt', // 수량
                'orgGoodsPrice', // 판매가 (판매가가 goodsPrice지만 상품금액에서 사용하고있어 래거쉬보장 문제로 orgGoodsPrice로 사용)
                'goodsPrice', // 상품금액
                'totalGoodsPrice', // 총 상품금액
                'costPrice', // 매입가
                'deliveryCharge', // 배송비
                'totalDeliveryCharge', // 총 배송비
                'totalOrderPrice', // 총 주문금액
                'purchaseNm', // 매입처
                'brandNm', // 브랜드
                'goodsModelNo', // 모델명
                'makerNm', // 제조사
                'scmNm', // 공급사
                'commission', // 수수료율
                'hscode', // HS코드
                'invoiceNo', // 송장번호
                'multiShippingCd', // 배송지
                'processStatus', // 처리상태
            ],
        ],

        /*
        * 주문상세페이지 - 교환추가내역
        */
        'view_exchangeAdd' => [
            'defaultList' => [ //디폴트 리스트
                'check', // 선택
                'no', // 번호
                'orderGoodsNo', // 상품주문번호
                'goodsImage', // 이미지
                'orderGoodsNm', // 주문상품
                'goodsCnt', // 수량
                'goodsPrice', // 상품금액
                'deliveryCharge', // 배송비
                'totalOrderPrice', // 총 주문금액
                'invoiceNo', // 송장번호
                'processStatus', // 처리상태
            ],
            'list' => [ //전체리스트
                'check', // 선택
                'no', // 번호
                'orderGoodsNo', // 상품주문번호
                'goodsCd', // 상품코드(자체상품코드)
                'goodsTaxInfo', // 상품 부가세
                'goodsImage', // 이미지
                'orderGoodsNm', // 주문상품
                'orderGoodsNmGlobal', // 주문상품(해외상점)
                'goodsCnt', // 수량
                'orgGoodsPrice', // 판매가 (판매가가 goodsPrice지만 상품금액에서 사용하고있어 래거쉬보장 문제로 orgGoodsPrice로 사용)
                'goodsPrice', // 상품금액
                'totalGoodsPrice', // 총 상품금액
                'costPrice', // 매입가
                'deliveryCharge', // 배송비
                'totalDeliveryCharge', // 총 배송비
                'totalOrderPrice', // 총 주문금액
                'purchaseNm', // 매입처
                'brandNm', // 브랜드
                'goodsModelNo', // 모델명
                'makerNm', // 제조사
                'scmNm', // 공급사
                'commission', // 수수료율
                'hscode', // HS코드
                'invoiceNo', // 송장번호
                'multiShippingCd', // 배송지
                'processStatus', // 처리상태
            ],
        ],

        /*
        * 주문상세페이지 - 반품내역
        */
        'view_back' => [
            'defaultList' => [ //디폴트 리스트
                'check', // 선택
                'no', // 번호
                'orderGoodsNo', // 상품주문번호
                'goodsImage', // 이미지
                'orderGoodsNm', // 주문상품
                'goodsCnt', // 수량
                'goodsPrice', // 상품금액
                'deliveryCharge', // 배송비
                'totalOrderPrice', // 총 주문금액
                'invoiceNo', // 송장번호
                'processStatus', // 처리상태
            ],
            'list' => [ //전체리스트
                'check', // 선택
                'no', // 번호
                'orderGoodsNo', // 상품주문번호
                'goodsCd', // 상품코드(자체상품코드)
                'goodsTaxInfo', // 상품 부가세
                'goodsImage', // 이미지
                'orderGoodsNm', // 주문상품
                'orderGoodsNmGlobal', // 주문상품(해외상점)
                'goodsCnt', // 수량
                'orgGoodsPrice', // 판매가 (판매가가 goodsPrice지만 상품금액에서 사용하고있어 래거쉬보장 문제로 orgGoodsPrice로 사용)
                'goodsPrice', // 상품금액
                'totalGoodsPrice', // 총 상품금액
                'costPrice', // 매입가
                'deliveryCharge', // 배송비
                'totalDeliveryCharge', // 총 배송비
                'totalOrderPrice', // 총 주문금액
                'purchaseNm', // 매입처
                'brandNm', // 브랜드
                'goodsModelNo', // 모델명
                'makerNm', // 제조사
                'scmNm', // 공급사
                'commission', // 수수료율
                'hscode', // HS코드
                'invoiceNo', // 송장번호
                'multiShippingCd', // 배송지
                'processStatus', // 처리상태
            ],
        ],

        /*
        * 주문상세페이지 - 환불내역
        */
        'view_refund' => [
            'defaultList' => [ //디폴트 리스트
                'check', // 선택
                'no', // 번호
                'orderGoodsNo', // 상품주문번호
                'goodsImage', // 이미지
                'orderGoodsNm', // 주문상품
                'goodsCnt', // 수량
                'goodsPrice', // 상품금액
                'deliveryCharge', // 배송비
                'totalOrderPrice', // 총 주문금액
                //'totalUseAddedRefundPrice', // 총 부가결제 환원금액
                //'totalRefundPrice', // 총 환불금액
                'invoiceNo', // 송장번호
                'processStatus', // 처리상태
                'adminMemo', // 관리자메모
            ],
            'list' => [ //전체리스트
                'check', // 선택
                'no', // 번호
                'orderGoodsNo', // 상품주문번호
                'goodsCd', // 상품코드(자체상품코드)
                'goodsTaxInfo', // 상품 부가세
                'goodsImage', // 이미지
                'orderGoodsNm', // 주문상품
                'orderGoodsNmGlobal', // 주문상품(해외상점)
                'goodsCnt', // 수량
                'orgGoodsPrice', // 판매가 (판매가가 goodsPrice지만 상품금액에서 사용하고있어 래거쉬보장 문제로 orgGoodsPrice로 사용)
                'goodsPrice', // 상품금액
                'totalGoodsPrice', // 총 상품금액
                'costPrice', // 매입가
                'deliveryCharge', // 배송비
                'totalDeliveryCharge', // 총 배송비
                'totalOrderPrice', // 총 주문금액
                //'totalUseAddedRefundPrice', // 총 부가결제 환원금액
                //'totalRefundPrice', // 총 환불금액
                'purchaseNm', // 매입처
                'brandNm', // 브랜드
                'goodsModelNo', // 모델명
                'makerNm', // 제조사
                'scmNm', // 공급사
                'commission', // 수수료율
                'hscode', // HS코드
                'invoiceNo', // 송장번호
                'multiShippingCd', // 배송지
                'processStatus', // 처리상태
                'adminMemo', // 관리자메모
                'refundPrice', // 환불 상품금액
                'refundUseMileage', // 상품 환원 마일리지
                'refundUseDeposit', // 상품 환원 예치금
                'refundCharge', // 상품 환불 수수료
                'refundUseMileageCommission', // 마일리지 환불 수수료
                'refundUseDepositCommission', // 예치금 환불 수수료
                'refundDeliveryCharge', // 환불 배송비
                'refundDeliveryUseMileage', // 배송비 환원 마일리지
                'refundDeliveryUseDeposit', // 배송비 환원 예치금
                'refundDeliveryInsuranceFee', // 환불 해외배송보험료

            ],
        ],

        /*
        * 주문상세페이지 - 결제 중단/실패내역
        */
        'view_fail' => [
            'defaultList' => [ //디폴트 리스트
                'check', // 선택
                'no', // 번호
                'orderGoodsNo', // 상품주문번호
                'goodsImage', // 이미지
                'orderGoodsNm', // 주문상품
                'goodsCnt', // 수량
                'goodsPrice', // 상품금액
                'deliveryCharge', // 배송비
                'totalOrderPrice', // 총 주문금액
                'invoiceNo', // 송장번호
                'processStatus', // 처리상태
            ],
            'list' => [ //전체리스트
                'check', // 선택
                'no', // 번호
                'orderGoodsNo', // 상품주문번호
                'goodsCd', // 상품코드(자체상품코드)
                'goodsTaxInfo', // 상품 부가세
                'goodsImage', // 이미지
                'orderGoodsNm', // 주문상품
                'orderGoodsNmGlobal', // 주문상품(해외상점)
                'goodsCnt', // 수량
                'orgGoodsPrice', // 판매가 (판매가가 goodsPrice지만 상품금액에서 사용하고있어 래거쉬보장 문제로 orgGoodsPrice로 사용)
                'goodsPrice', // 상품금액
                'totalGoodsPrice', // 총 상품금액
                'costPrice', // 매입가
                'deliveryCharge', // 배송비
                'totalDeliveryCharge', // 총 배송비
                'totalOrderPrice', // 총 주문금액
                'purchaseNm', // 매입처
                'brandNm', // 브랜드
                'goodsModelNo', // 모델명
                'makerNm', // 제조사
                'scmNm', // 공급사
                'commission', // 수수료율
                'hscode', // HS코드
                'invoiceNo', // 송장번호
                'multiShippingCd', // 배송지
                'processStatus', // 처리상태
            ],
        ],
    ];


    //공급사
    private $orderScmSubList = [
        /*
        * 주문통합리스트 주문번호별
        */
        'list_all_order' => [
            'defaultList' => [ //디폴트 리스트
                'check', // 선택
                'no', // 번호
                'domainFl', // 상점구분
                'regDt', // 주문일시
                'orderNo', // 주문번호
                'orderName', // 주문자
                'orderGoodsNm', // 주문상품
                'totalGoodsPrice', // 총 상품금액
                'totalDeliveryCharge', // 총 배송비
                'totalOrderPrice', // 총 주문금액
                'noDelivery', // 미배송
                'deliverying', // 배송중
                'deliveryed', // 배송완료
                'exchange', // 교환
                'back', // 반품
                'refund', // 환불
                'adminMemo', // 관리자메모
                'orderTypeFl', // 주문유형
            ],
            'list' => [ //전체리스트
                'check', // 선택
                'no', // 번호
                'domainFl', // 상점구분
                'regDt', // 주문일시
                'paymentDt', //입금일시
                'orderNo', // 주문번호
                'orderName', // 주문자
                'orderTypeFl', // 주문유형
                'orderGoodsNm', // 주문상품
                'orderGoodsNmGlobal', // 주문상품(해외상점)
                'totalGoodsPrice', // 총 상품금액
                'totalDeliveryCharge', // 총 배송비
                'totalOrderPrice', // 총 주문금액
                'totalGoodsPriceGlobal', // 총 상품금액(해외상점)
                'totalDeliveryChargeGlobal', // 총 배송비(해외상점)
                'totalOrderPriceGlobal', // 총 주문금액(해외상점)
                //'processStatus', // 처리상태
                'noDelivery', // 미배송
                'deliverying', // 배송중
                'deliveryed', // 배송완료
                'exchange', // 교환
                'back', // 반품
                'refund', // 환불
                'receiverName', // 수령자
                //'receiverAddress', // 수령자 주소
                'orderMemo', // 배송 메시지
                'gift', // 사은품
                'adminMemo', // 관리자메모
                'phoneNumber', // 휴대폰 번호
            ],
        ],
        /*
        * 주문통합리스트 상품주문번호별
        */
        'list_all_order_goods' => [
            'defaultList' => [ //디폴트 리스트
                'check', // 선택
                'no', // 번호
                'domainFl', // 상점구분
                'regDt', // 주문일시
                'orderNo', // 주문번호
                'orderName', // 주문자
                'orderGoodsNo', // 상품주문번호
                'orderGoodsNm', // 주문상품
                'goodsCnt', // 수량
                'goodsPrice', // 상품금액
                'totalGoodsPrice', // 총 상품금액
                'deliveryCharge', // 배송비
                'totalDeliveryCharge', // 총 배송비
                'totalOrderPrice', // 총 주문금액
                'processStatus', // 처리상태
                'deliverySno', // 배송번호
                'receiverName', // 수령자
                'orderMemo', // 배송 메시지
                'orderTypeFl', // 주문유형
            ],
            'list' => [ //전체리스트
                'check', // 선택
                'no', // 번호
                'domainFl', // 상점구분
                'regDt', // 주문일시
                'paymentDt', //입금일시
                'deliveryDt', //배송일시
                'deliveryCompleteDt', //배송완료일시
                'finishDt', //구매확정일시
                'orderNo', // 주문번호
                'orderName', // 주문자
                'orderTypeFl', // 주문유형
                'orderGoodsNo', // 상품주문번호
                'goodsCd', // 상품코드(자체상품코드)
                'goodsTaxInfo', // 상품 부가세
                'orderGoodsNm', // 주문상품
                'orderGoodsNmGlobal', // 주문상품(해외상점)
                'goodsCnt', // 수량
                'orgGoodsPrice', // 판매가 (판매가가 goodsPrice지만 상품금액에서 사용하고있어 래거쉬보장 문제로 orgGoodsPrice로 사용)
                'goodsPrice', // 상품금액
                'totalGoodsPrice', // 총 상품금액
                'deliveryCharge', // 배송비
                'totalDeliveryCharge', // 총 배송비
                'totalOrderPrice', // 총 주문금액
                'goodsPriceGlobal', // 상품금액(해외상점)
                'totalGoodsPriceGlobal', // 총 상품금액(해외상점)
                'deliveryChargeGlobal', // 배송비(해외상점)
                'totalDeliveryChargeGlobal', // 총 배송비(해외상점)
                'totalOrderPriceGlobal', // 총 주문금액(해외상점)
                'processStatus', // 처리상태
                'brandNm', // 브랜드
                'goodsModelNo', // 모델명
                'makerNm', // 제조사
                'deliverySno', // 배송번호
                'commission', // 수수료율
                'costPrice', // 매입가
                'invoiceNo', // 송장번호
                'receiverName', // 수령자
                //'receiverAddress', // 수령자 주소
                'multiShippingCd', // 배송지
                'orderMemo', // 배송 메시지
                'hscode', // HS코드
                'gift', // 사은품
                'adminMemo', // 관리자메모
                'phoneNumber', // 휴대폰 번호
            ],
        ],

        /*
        * 결제완료리스트 주문번호별
        */
        'list_pay_order' => [
            'defaultList' => [ //디폴트 리스트
                'check', // 선택
                'no', // 번호
                'domainFl', // 상점구분
                'regDt', // 주문일시
                'orderNo', // 주문번호
                'orderName', // 주문자
                'orderGoodsNm', // 주문상품
                'totalGoodsPrice', // 총 상품금액
                'totalDeliveryCharge', // 총 배송비
                'totalOrderPrice', // 총 주문금액
                'adminMemo', // 관리자메모
                'orderTypeFl', // 주문유형
            ],
            'list' => [ //전체리스트
                'check', // 선택
                'no', // 번호
                'domainFl', // 상점구분
                'regDt', // 주문일시
                'paymentDt', //입금일시
                'orderNo', // 주문번호
                'orderName', // 주문자
                'orderTypeFl', // 주문유형
                'orderGoodsNm', // 주문상품
                'orderGoodsNmGlobal', // 주문상품(해외상점)
                'goodsCnt', // 수량
                'totalGoodsPrice', // 총 상품금액
                'totalDeliveryCharge', // 총 배송비
                'totalOrderPrice', // 총 주문금액
                'totalGoodsPriceGlobal', // 총 상품금액(해외상점)
                'totalDeliveryChargeGlobal', // 총 배송비(해외상점)
                'totalOrderPriceGlobal', // 총 주문금액(해외상점)
                //'processStatus', // 처리상태
                'receiverName', // 수령자
                //'receiverAddress', // 수령자 주소
                'orderMemo', // 배송 메시지
                'gift', // 사은품
                'adminMemo', // 관리자메모
                'phoneNumber', // 휴대폰 번호
            ],
        ],
        /*
        * 결제완료리스트 상품주문번호별
        */
        'list_pay_order_goods' => [
            'defaultList' => [ //디폴트 리스트
                'check', // 선택
                'no', // 번호
                'domainFl', // 상점구분
                'regDt', // 주문일시
                'orderName', // 주문자
                'orderGoodsNo', // 상품주문번호
                'orderGoodsNm', // 주문상품
                'goodsCnt', // 수량
                'goodsPrice', // 상품금액
                'totalGoodsPrice', // 총 상품금액
                'deliveryCharge', // 배송비
                'totalDeliveryCharge', // 총 배송비
                'totalOrderPrice', // 총 주문금액
                'processStatus', // 처리상태
                'adminMemo', // 관리자메모
                'orderTypeFl', // 주문유형
            ],
            'list' => [ //전체리스트
                'check', // 선택
                'no', // 번호
                'domainFl', // 상점구분
                'regDt', // 주문일시
                'paymentDt', //입금일시
                'orderNo', // 주문번호
                'orderName', // 주문자
                'orderTypeFl', // 주문유형
                'orderGoodsNo', // 상품주문번호
                'goodsCd', // 상품코드(자체상품코드)
                'goodsTaxInfo', // 상품 부가세
                'orderGoodsNm', // 주문상품
                'orderGoodsNmGlobal', // 주문상품(해외상점)
                'goodsCnt', // 수량
                'orgGoodsPrice', // 판매가 (판매가가 goodsPrice지만 상품금액에서 사용하고있어 래거쉬보장 문제로 orgGoodsPrice로 사용)
                'goodsPrice', // 상품금액
                'totalGoodsPrice', // 총 상품금액
                'deliveryCharge', // 배송비
                'totalDeliveryCharge', // 총 배송비
                'totalOrderPrice', // 총 주문금액
                'goodsPriceGlobal', // 상품금액(해외상점)
                'totalGoodsPriceGlobal', // 총 상품금액(해외상점)
                'deliveryChargeGlobal', // 배송비(해외상점)
                'totalDeliveryChargeGlobal', // 총 배송비(해외상점)
                'totalOrderPriceGlobal', // 총 주문금액(해외상점)
                'processStatus', // 처리상태
                'brandNm', // 브랜드
                'goodsModelNo', // 모델명
                'makerNm', // 제조사
                'deliverySno', // 배송번호
                'commission', // 수수료율
                'costPrice', // 매입가
                'receiverName', // 수령자
                //'receiverAddress', // 수령자 주소
                'multiShippingCd', // 배송지
                'orderMemo', // 배송 메시지
                'hscode', // HS코드
                'gift', // 사은품
                'adminMemo', // 관리자메모
                'phoneNumber', // 휴대폰 번호
            ],
        ],

        /*
        * 상품준비중리스트 주문번호별
        */
        'list_goods_order' => [
            'defaultList' => [ //디폴트 리스트
                'check', // 선택
                'no', // 번호
                'domainFl', // 상점구분
                'regDt', // 주문일시
                'orderNo', // 주문번호
                'orderName', // 주문자
                'orderGoodsNm', // 주문상품
                'totalGoodsPrice', // 총 상품금액
                'totalDeliveryCharge', // 총 배송비
                'totalOrderPrice', // 총 주문금액
                'invoiceNo', // 송장번호
                'gift', // 사은품
                'adminMemo', // 관리자메모
                'orderTypeFl', // 주문유형
            ],
            'list' => [ //전체리스트
                'check', // 선택
                'no', // 번호
                'domainFl', // 상점구분
                'regDt', // 주문일시
                'paymentDt', //입금일시
                'orderNo', // 주문번호
                'orderName', // 주문자
                'orderTypeFl', // 주문유형
                'orderGoodsNm', // 주문상품
                'orderGoodsNmGlobal', // 주문상품(해외상점)
                'goodsCnt', // 수량
                'totalGoodsPrice', // 총 상품금액
                'totalDeliveryCharge', // 총 배송비
                'totalOrderPrice', // 총 주문금액
                'totalGoodsPriceGlobal', // 총 상품금액(해외상점)
                'totalDeliveryChargeGlobal', // 총 배송비(해외상점)
                'totalOrderPriceGlobal', // 총 주문금액(해외상점)
                //'processStatus', // 처리상태
                'invoiceNo', // 송장번호
                'receiverName', // 수령자
                //'receiverAddress', // 수령자 주소
                'orderMemo', // 배송 메시지
                'gift', // 사은품
                'adminMemo', // 관리자메모
                'phoneNumber', // 휴대폰 번호
            ],
        ],
        /*
        * 상품준비중리스트 상품주문번호별
        */
        'list_goods_order_goods' => [
            'defaultList' => [ //디폴트 리스트
                'check', // 선택
                'no', // 번호
                'domainFl', // 상점구분
                'regDt', // 주문일시
                'orderName', // 주문자
                'orderGoodsNo', // 상품주문번호
                'orderGoodsNm', // 주문상품
                'goodsCnt', // 수량
                'goodsPrice', // 상품금액
                'totalGoodsPrice', // 총 상품금액
                'deliveryCharge', // 배송비
                'totalDeliveryCharge', // 총 배송비
                'totalOrderPrice', // 총 주문금액
                'processStatus', // 처리상태
                'invoiceNo', // 송장번호
                'gift', // 사은품
                'adminMemo', // 관리자메모
                'orderTypeFl', // 주문유형
            ],
            'list' => [ //전체리스트
                'check', // 선택
                'no', // 번호
                'domainFl', // 상점구분
                'regDt', // 주문일시
                'paymentDt', //입금일시
                'orderNo', // 주문번호
                'orderName', // 주문자
                'orderTypeFl', // 주문유형
                'orderGoodsNo', // 상품주문번호
                'goodsCd', // 상품코드(자체상품코드)
                'goodsTaxInfo', // 상품 부가세
                'orderGoodsNm', // 주문상품
                'orderGoodsNmGlobal', // 주문상품(해외상점)
                'goodsCnt', // 수량
                'orgGoodsPrice', // 판매가 (판매가가 goodsPrice지만 상품금액에서 사용하고있어 래거쉬보장 문제로 orgGoodsPrice로 사용)
                'goodsPrice', // 상품금액
                'totalGoodsPrice', // 총 상품금액
                'deliveryCharge', // 배송비
                'totalDeliveryCharge', // 총 배송비
                'totalOrderPrice', // 총 주문금액
                'goodsPriceGlobal', // 상품금액(해외상점)
                'totalGoodsPriceGlobal', // 총 상품금액(해외상점)
                'deliveryChargeGlobal', // 배송비(해외상점)
                'totalDeliveryChargeGlobal', // 총 배송비(해외상점)
                'totalOrderPriceGlobal', // 총 주문금액(해외상점)
                'processStatus', // 처리상태
                'brandNm', // 브랜드
                'goodsModelNo', // 모델명
                'makerNm', // 제조사
                'deliverySno', // 배송번호
                'commission', // 수수료율
                'costPrice', // 매입가
                'invoiceNo', // 송장번호
                'receiverName', // 수령자
                //'receiverAddress', // 수령자 주소
                'multiShippingCd', // 배송지
                'orderMemo', // 배송 메시지
                'hscode', // HS코드
                'gift', // 사은품
                'adminMemo', // 관리자메모
                'phoneNumber', // 휴대폰 번호
            ],
        ],

        /*
        * 배송중리스트 주문번호별
        */
        'list_delivery_order' => [
            'defaultList' => [ //디폴트 리스트
                'check', // 선택
                'no', // 번호
                'domainFl', // 상점구분
                'regDt', // 주문일시
                'orderNo', // 주문번호
                'orderName', // 주문자
                'orderGoodsNm', // 주문상품
                'totalGoodsPrice', // 총 상품금액
                'totalDeliveryCharge', // 총 배송비
                'totalOrderPrice', // 총 주문금액
                'invoiceNo', // 송장번호
                'gift', // 사은품
                'adminMemo', // 관리자메모
                'orderTypeFl', // 주문유형
            ],
            'list' => [ //전체리스트
                'check', // 선택
                'no', // 번호
                'domainFl', // 상점구분
                'regDt', // 주문일시
                'paymentDt', //입금일시
                'orderNo', // 주문번호
                'orderName', // 주문자
                'orderTypeFl', // 주문유형
                'orderGoodsNm', // 주문상품
                'orderGoodsNmGlobal', // 주문상품(해외상점)
                'goodsCnt', // 수량
                'totalGoodsPrice', // 총 상품금액
                'totalDeliveryCharge', // 총 배송비
                'totalOrderPrice', // 총 주문금액
                'totalGoodsPriceGlobal', // 총 상품금액(해외상점)
                'totalDeliveryChargeGlobal', // 총 배송비(해외상점)
                'totalOrderPriceGlobal', // 총 주문금액(해외상점)
                //'processStatus', // 처리상태
                'invoiceNo', // 송장번호
                'receiverName', // 수령자
                //'receiverAddress', // 수령자 주소
                'orderMemo', // 배송 메시지
                'gift', // 사은품
                'adminMemo', // 관리자메모
                'phoneNumber', // 휴대폰 번호
            ],
        ],
        /*
        * 배송중리스트 상품주문번호별
        */
        'list_delivery_order_goods' => [
            'defaultList' => [ //디폴트 리스트
                'check', // 선택
                'no', // 번호
                'domainFl', // 상점구분
                'regDt', // 주문일시
                'orderName', // 주문자
                'orderGoodsNo', // 상품주문번호
                'orderGoodsNm', // 주문상품
                'goodsCnt', // 수량
                'goodsPrice', // 상품금액
                'totalGoodsPrice', // 총 상품금액
                'deliveryCharge', // 배송비
                'totalDeliveryCharge', // 총 배송비
                'totalOrderPrice', // 총 주문금액
                'processStatus', // 처리상태
                'invoiceNo', // 송장번호
                'gift', // 사은품
                'adminMemo', // 관리자메모
                'orderTypeFl', // 주문유형
            ],
            'list' => [ //전체리스트
                'check', // 선택
                'no', // 번호
                'domainFl', // 상점구분
                'regDt', // 주문일시
                'paymentDt', //입금일시
                'deliveryDt', //배송일시
                'orderNo', // 주문번호
                'orderName', // 주문자
                'orderTypeFl', // 주문유형
                'orderGoodsNo', // 상품주문번호
                'goodsCd', // 상품코드(자체상품코드)
                'goodsTaxInfo', // 상품 부가세
                'orderGoodsNm', // 주문상품
                'orderGoodsNmGlobal', // 주문상품(해외상점)
                'goodsCnt', // 수량
                'orgGoodsPrice', // 판매가 (판매가가 goodsPrice지만 상품금액에서 사용하고있어 래거쉬보장 문제로 orgGoodsPrice로 사용)
                'goodsPrice', // 상품금액
                'totalGoodsPrice', // 총 상품금액
                'deliveryCharge', // 배송비
                'totalDeliveryCharge', // 총 배송비
                'totalOrderPrice', // 총 주문금액
                'goodsPriceGlobal', // 상품금액(해외상점)
                'totalGoodsPriceGlobal', // 총 상품금액(해외상점)
                'deliveryChargeGlobal', // 배송비(해외상점)
                'totalDeliveryChargeGlobal', // 총 배송비(해외상점)
                'totalOrderPriceGlobal', // 총 주문금액(해외상점)
                'processStatus', // 처리상태
                'brandNm', // 브랜드
                'goodsModelNo', // 모델명
                'makerNm', // 제조사
                'deliverySno', // 배송번호
                'commission', // 수수료율
                'costPrice', // 매입가
                'invoiceNo', // 송장번호
                'receiverName', // 수령자
                //'receiverAddress', // 수령자 주소
                'multiShippingCd', // 배송지
                'orderMemo', // 배송 메시지
                'hscode', // HS코드
                'gift', // 사은품
                'adminMemo', // 관리자메모
                'phoneNumber', // 휴대폰 번호
            ],
        ],

        /*
        * 배송완료리스트 주문번호별
        */
        'list_delivery_ok_order' => [
            'defaultList' => [ //디폴트 리스트
                'check', // 선택
                'no', // 번호
                'domainFl', // 상점구분
                'regDt', // 주문일시
                'orderNo', // 주문번호
                'orderName', // 주문자
                'orderGoodsNm', // 주문상품
                'totalGoodsPrice', // 총 상품금액
                'totalDeliveryCharge', // 총 배송비
                'totalOrderPrice', // 총 주문금액
                'invoiceNo', // 송장번호
                'gift', // 사은품
                'adminMemo', // 관리자메모
                'orderTypeFl', // 주문유형
            ],
            'list' => [ //전체리스트
                'check', // 선택
                'no', // 번호
                'domainFl', // 상점구분
                'regDt', // 주문일시
                'paymentDt', //입금일시
                'orderNo', // 주문번호
                'orderName', // 주문자
                'orderTypeFl', // 주문유형
                'orderGoodsNm', // 주문상품
                'orderGoodsNmGlobal', // 주문상품(해외상점)
                'goodsCnt', // 수량
                'totalGoodsPrice', // 총 상품금액
                'totalDeliveryCharge', // 총 배송비
                'totalOrderPrice', // 총 주문금액
                'totalGoodsPriceGlobal', // 총 상품금액(해외상점)
                'totalDeliveryChargeGlobal', // 총 배송비(해외상점)
                'totalOrderPriceGlobal', // 총 주문금액(해외상점)
                //'processStatus', // 처리상태
                'invoiceNo', // 송장번호
                'receiverName', // 수령자
                //'receiverAddress', // 수령자 주소
                'orderMemo', // 배송 메시지
                'gift', // 사은품
                'adminMemo', // 관리자메모
                'phoneNumber', // 휴대폰 번호
            ],
        ],
        /*
        * 배송완료리스트 상품주문번호별
        */
        'list_delivery_ok_order_goods' => [
            'defaultList' => [ //디폴트 리스트
                'check', // 선택
                'no', // 번호
                'domainFl', // 상점구분
                'regDt', // 주문일시
                'orderName', // 주문자
                'orderGoodsNo', // 상품주문번호
                'orderGoodsNm', // 주문상품
                'goodsCnt', // 수량
                'goodsPrice', // 상품금액
                'totalGoodsPrice', // 총 상품금액
                'deliveryCharge', // 배송비
                'totalDeliveryCharge', // 총 배송비
                'totalOrderPrice', // 총 주문금액
                'processStatus', // 처리상태
                'invoiceNo', // 송장번호
                'gift', // 사은품
                'adminMemo', // 관리자메모
                'orderTypeFl', // 주문유형
            ],
            'list' => [ //전체리스트
                'check', // 선택
                'no', // 번호
                'domainFl', // 상점구분
                'regDt', // 주문일시
                'paymentDt', //입금일시
                'deliveryDt', //배송일시
                'deliveryCompleteDt', // 배송완료일시
                'orderNo', // 주문번호
                'orderName', // 주문자
                'orderTypeFl', // 주문유형
                'orderGoodsNo', // 상품주문번호
                'goodsCd', // 상품코드(자체상품코드)
                'goodsTaxInfo', // 상품 부가세
                'orderGoodsNm', // 주문상품
                'orderGoodsNmGlobal', // 주문상품(해외상점)
                'goodsCnt', // 수량
                'orgGoodsPrice', // 판매가 (판매가가 goodsPrice지만 상품금액에서 사용하고있어 래거쉬보장 문제로 orgGoodsPrice로 사용)
                'goodsPrice', // 상품금액
                'totalGoodsPrice', // 총 상품금액
                'deliveryCharge', // 배송비
                'totalDeliveryCharge', // 총 배송비
                'totalOrderPrice', // 총 주문금액
                'goodsPriceGlobal', // 상품금액(해외상점)
                'totalGoodsPriceGlobal', // 총 상품금액(해외상점)
                'deliveryChargeGlobal', // 배송비(해외상점)
                'totalDeliveryChargeGlobal', // 총 배송비(해외상점)
                'totalOrderPriceGlobal', // 총 주문금액(해외상점)
                'processStatus', // 처리상태
                'brandNm', // 브랜드
                'goodsModelNo', // 모델명
                'makerNm', // 제조사
                'deliverySno', // 배송번호
                'commission', // 수수료율
                'costPrice', // 매입가
                'invoiceNo', // 송장번호
                'receiverName', // 수령자
                //'receiverAddress', // 수령자 주소
                'multiShippingCd', // 배송지
                'orderMemo', // 배송 메시지
                'hscode', // HS코드
                'gift', // 사은품
                'adminMemo', // 관리자메모
                'phoneNumber', // 휴대폰 번호
            ],
        ],

        /*
        * 구매확정리스트 주문번호별
        */
        'list_settle_order' => [
            'defaultList' => [ //디폴트 리스트
                'check', // 선택
                'no', // 번호
                'domainFl', // 상점구분
                'regDt', // 주문일시
                'orderNo', // 주문번호
                'orderName', // 주문자
                'orderGoodsNm', // 주문상품
                'totalGoodsPrice', // 총 상품금액
                'totalDeliveryCharge', // 총 배송비
                'totalOrderPrice', // 총 주문금액
                'invoiceNo', // 송장번호
                'gift', // 사은품
                'adminMemo', // 관리자메모
                'orderTypeFl', // 주문유형
            ],
            'list' => [ //전체리스트
                'check', // 선택
                'no', // 번호
                'domainFl', // 상점구분
                'regDt', // 주문일시
                'paymentDt', //입금일시
                'orderNo', // 주문번호
                'orderName', // 주문자
                'orderTypeFl', // 주문유형
                'orderGoodsNm', // 주문상품
                'orderGoodsNmGlobal', // 주문상품(해외상점)
                'goodsCnt', // 수량
                'totalGoodsPrice', // 총 상품금액
                'totalDeliveryCharge', // 총 배송비
                'totalOrderPrice', // 총 주문금액
                'totalGoodsPriceGlobal', // 총 상품금액(해외상점)
                'totalDeliveryChargeGlobal', // 총 배송비(해외상점)
                'totalOrderPriceGlobal', // 총 주문금액(해외상점)
                //'processStatus', // 처리상태
                'invoiceNo', // 송장번호
                'receiverName', // 수령자
                //'receiverAddress', // 수령자 주소
                'orderMemo', // 배송 메시지
                'gift', // 사은품
                'adminMemo', // 관리자메모
                'phoneNumber', // 휴대폰 번호
            ],
        ],
        /*
        * 구매확정리스트 상품주문번호별
        */
        'list_settle_order_goods' => [
            'defaultList' => [ //디폴트 리스트
                'check', // 선택
                'no', // 번호
                'domainFl', // 상점구분
                'regDt', // 주문일시
                'orderName', // 주문자
                'orderGoodsNo', // 상품주문번호
                'orderGoodsNm', // 주문상품
                'goodsCnt', // 수량
                'goodsPrice', // 상품금액
                'totalGoodsPrice', // 총 상품금액
                'deliveryCharge', // 배송비
                'totalDeliveryCharge', // 총 배송비
                'totalOrderPrice', // 총 주문금액
                'processStatus', // 처리상태
                'invoiceNo', // 송장번호
                'gift', // 사은품
                'adminMemo', // 관리자메모
                'orderTypeFl', // 주문유형
            ],
            'list' => [ //전체리스트
                'check', // 선택
                'no', // 번호
                'domainFl', // 상점구분
                'regDt', // 주문일시
                'paymentDt', //입금일시
                'deliveryDt', //배송일시
                'deliveryCompleteDt', // 배송완료일시
                'finishDt', // 구매확정일시
                'orderNo', // 주문번호
                'orderName', // 주문자
                'orderTypeFl', // 주문유형
                'orderGoodsNo', // 상품주문번호
                'goodsCd', // 상품코드(자체상품코드)
                'goodsTaxInfo', // 상품 부가세
                'orderGoodsNm', // 주문상품
                'orderGoodsNmGlobal', // 주문상품(해외상점)
                'goodsCnt', // 수량
                'orgGoodsPrice', // 판매가 (판매가가 goodsPrice지만 상품금액에서 사용하고있어 래거쉬보장 문제로 orgGoodsPrice로 사용)
                'goodsPrice', // 상품금액
                'totalGoodsPrice', // 총 상품금액
                'deliveryCharge', // 배송비
                'totalDeliveryCharge', // 총 배송비
                'totalOrderPrice', // 총 주문금액
                'goodsPriceGlobal', // 상품금액(해외상점)
                'totalGoodsPriceGlobal', // 총 상품금액(해외상점)
                'deliveryChargeGlobal', // 배송비(해외상점)
                'totalDeliveryChargeGlobal', // 총 배송비(해외상점)
                'totalOrderPriceGlobal', // 총 주문금액(해외상점)
                'processStatus', // 처리상태
                'brandNm', // 브랜드
                'goodsModelNo', // 모델명
                'makerNm', // 제조사
                'deliverySno', // 배송번호
                'commission', // 수수료율
                'costPrice', // 매입가
                'invoiceNo', // 송장번호
                'receiverName', // 수령자
                //'receiverAddress', // 수령자 주소
                'multiShippingCd', // 배송지
                'orderMemo', // 배송 메시지
                'hscode', // HS코드
                'gift', // 사은품
                'adminMemo', // 관리자메모
                'phoneNumber', // 휴대폰 번호
            ],
        ],

        /*
        * 교환리스트
        */
        'list_exchange_order' => [
            'defaultList' => [ //디폴트 리스트
                'check', // 선택
                'no', // 번호
                'domainFl', // 상점구분
                'regDt', // 주문일시
                'exchangeDt', // 교환신청일(교환접수일)
                'exchangeCompleteDt', // 교환완료일시
                'orderNo', // 주문번호
                'orderName', // 주문자
                'orderGoodsNo', // 상품주문번호
                'orderGoodsNm', // 주문상품
                'goodsCnt', // 수량
                'goodsPrice', // 상품금액
                'totalGoodsPrice', // 총 상품금액
                'deliveryCharge', // 배송비
                'totalDeliveryCharge', // 총 배송비
                'processStatus', // 처리상태
                'reason', // 사유
                //'processor', // 처리자
                'adminMemo', // 관리자메모
                'orderTypeFl', // 주문유형
            ],
            'list' => [ //전체리스트
                'check', // 선택
                'no', // 번호
                'domainFl', // 상점구분
                'regDt', // 주문일시
                'exchangeDt', // 교환신청일(교환접수일)
                'exchangeCompleteDt', // 교환완료일시
                'orderNo', // 주문번호
                'orderName', // 주문자
                'orderTypeFl', // 주문유형
                'orderGoodsNo', // 상품주문번호
                'goodsCd', // 상품코드(자체상품코드)
                'goodsTaxInfo', // 상품 부가세
                'orderGoodsNm', // 주문상품
                'orderGoodsNmGlobal', // 주문상품(해외상점)
                'goodsCnt', // 수량
                'orgGoodsPrice', // 판매가 (판매가가 goodsPrice지만 상품금액에서 사용하고있어 래거쉬보장 문제로 orgGoodsPrice로 사용)
                'goodsPrice', // 상품금액
                'totalGoodsPrice', // 총 상품금액
                'deliveryCharge', // 배송비
                'totalDeliveryCharge', // 총 배송비
                'goodsPriceGlobal', // 상품금액(해외상점)
                'totalGoodsPriceGlobal', // 총 상품금액(해외상점)
                'deliveryChargeGlobal', // 배송비(해외상점)
                'totalDeliveryChargeGlobal', // 총 배송비(해외상점)
                'processStatus', // 처리상태
                'reason', // 사유
                'brandNm', // 브랜드
                'goodsModelNo', // 모델명
                'makerNm', // 제조사
                'deliverySno', // 배송번호
                'commission', // 수수료율
                'costPrice', // 매입가
                'invoiceNo', // 송장번호
                'receiverName', // 수령자
                //'receiverAddress', // 수령자 주소
                'multiShippingCd', // 배송지
                'orderMemo', // 배송 메시지
                'hscode', // HS코드
                'gift', // 사은품
                //'processor', // 처리자
                'adminMemo', // 관리자메모
                'phoneNumber', // 휴대폰 번호
            ],
        ],

        /*
        * 교환취소리스트
        */
        'list_exchange_cancel_order' => [
            'defaultList' => [ //디폴트 리스트
                'check', // 선택
                'no', // 번호
                'domainFl', // 상점구분
                'regDt', // 주문일시
                'exchangeDt', // 교환신청일(교환접수일)
                'exchangeCompleteDt', // 교환완료일시
                'orderNo', // 주문번호
                'orderName', // 주문자
                'orderGoodsNo', // 상품주문번호
                'orderGoodsNm', // 주문상품
                'goodsCnt', // 수량
                'goodsPrice', // 상품금액
                'totalGoodsPrice', // 총 상품금액
                'deliveryCharge', // 배송비
                'totalDeliveryCharge', // 총 배송비
                'processStatus', // 처리상태
                'reason', // 사유
                //'processor', // 처리자
                'adminMemo', // 관리자메모
                'orderTypeFl', // 주문유형
            ],
            'list' => [ //전체리스트
                'check', // 선택
                'no', // 번호
                'domainFl', // 상점구분
                'regDt', // 주문일시
                'exchangeDt', // 교환신청일(교환접수일)
                'exchangeCompleteDt', // 교환완료일시
                'orderNo', // 주문번호
                'orderName', // 주문자
                'orderTypeFl', // 주문유형
                'orderGoodsNo', // 상품주문번호
                'goodsCd', // 상품코드(자체상품코드)
                'goodsTaxInfo', // 상품 부가세
                'orderGoodsNm', // 주문상품
                'orderGoodsNmGlobal', // 주문상품(해외상점)
                'goodsCnt', // 수량
                'orgGoodsPrice', // 판매가 (판매가가 goodsPrice지만 상품금액에서 사용하고있어 래거쉬보장 문제로 orgGoodsPrice로 사용)
                'goodsPrice', // 상품금액
                'totalGoodsPrice', // 총 상품금액
                'deliveryCharge', // 배송비
                'totalDeliveryCharge', // 총 배송비
                'goodsPriceGlobal', // 상품금액(해외상점)
                'totalGoodsPriceGlobal', // 총 상품금액(해외상점)
                'deliveryChargeGlobal', // 배송비(해외상점)
                'totalDeliveryChargeGlobal', // 총 배송비(해외상점)
                'processStatus', // 처리상태
                'reason', // 사유
                'brandNm', // 브랜드
                'goodsModelNo', // 모델명
                'makerNm', // 제조사
                'deliverySno', // 배송번호
                'commission', // 수수료율
                'costPrice', // 매입가
                'invoiceNo', // 송장번호
                'receiverName', // 수령자
                //'receiverAddress', // 수령자 주소
                'multiShippingCd', // 배송지
                'orderMemo', // 배송 메시지
                'hscode', // HS코드
                'gift', // 사은품
                //'processor', // 처리자
                'adminMemo', // 관리자메모
                'phoneNumber', // 휴대폰 번호
            ],
        ],

        /*
        * 교환추가리스트
        */
        'list_exchange_add_order' => [
            'defaultList' => [ //디폴트 리스트
                'check', // 선택
                'no', // 번호
                'domainFl', // 상점구분
                'regDt', // 주문일시
                'exchangeDt', // 교환신청일(교환접수일)
                'exchangeCompleteDt', // 교환완료일시
                'orderNo', // 주문번호
                'orderName', // 주문자
                'orderGoodsNo', // 상품주문번호
                'orderGoodsNm', // 주문상품
                'goodsCnt', // 수량
                'goodsPrice', // 상품금액
                'totalGoodsPrice', // 총 상품금액
                'totalDeliveryCharge', // 총 배송비
                'processStatus', // 처리상태
                'reason', // 사유
                //'processor', // 처리자
                'adminMemo', // 관리자메모
                'orderTypeFl', // 주문유형
            ],
            'list' => [ //전체리스트
                'check', // 선택
                'no', // 번호
                'domainFl', // 상점구분
                'regDt', // 주문일시
                'exchangeDt', // 교환신청일(교환접수일)
                'exchangeCompleteDt', // 교환완료일시
                'orderNo', // 주문번호
                'orderName', // 주문자
                'orderTypeFl', // 주문유형
                'orderGoodsNo', // 상품주문번호
                'goodsCd', // 상품코드(자체상품코드)
                'goodsTaxInfo', // 상품 부가세
                'orderGoodsNm', // 주문상품
                'orderGoodsNmGlobal', // 주문상품(해외상점)
                'goodsCnt', // 수량
                'goodsPrice', // 상품금액
                'totalGoodsPrice', // 총 상품금액
                'totalDeliveryCharge', // 총 배송비
                'goodsPriceGlobal', // 상품금액(해외상점)
                'totalGoodsPriceGlobal', // 총 상품금액(해외상점)
                'deliveryChargeGlobal', // 배송비(해외상점)
                'totalDeliveryChargeGlobal', // 총 배송비(해외상점)
                'processStatus', // 처리상태
                'reason', // 사유
                'brandNm', // 브랜드
                'goodsModelNo', // 모델명
                'makerNm', // 제조사
                'deliverySno', // 배송번호
                'commission', // 수수료율
                'costPrice', // 매입가
                'invoiceNo', // 송장번호
                'receiverName', // 수령자
                //'receiverAddress', // 수령자 주소
                'multiShippingCd', // 배송지
                'orderMemo', // 배송 메시지
                'hscode', // HS코드
                'gift', // 사은품
                //'processor', // 처리자
                'adminMemo', // 관리자메모
                'phoneNumber', // 휴대폰 번호
            ],
        ],

        /*
        * 반품리스트
        */
        'list_back_order' => [
            'defaultList' => [ //디폴트 리스트
                'check', // 선택
                'no', // 번호
                'domainFl', // 상점구분
                'regDt', // 주문일시
                'backDt', // 반품신청일(반품접수일)
                'backCompleteDt', // 반품완료일시
                'orderNo', // 주문번호
                'orderName', // 주문자
                'orderGoodsNo', // 상품주문번호
                'orderGoodsNm', // 주문상품
                'goodsCnt', // 수량
                'goodsPrice', // 상품금액
                'totalGoodsPrice', // 총 상품금액
                'deliveryCharge', // 배송비
                'totalDeliveryCharge', // 총 배송비
                'processStatus', // 처리상태
                'reason', // 사유
                //'processor', // 처리자
                'adminMemo', // 관리자메모
                'orderTypeFl', // 주문유형
            ],
            'list' => [ //전체리스트
                'check', // 선택
                'no', // 번호
                'domainFl', // 상점구분
                'regDt', // 주문일시
                'backDt', // 반품신청일(반품접수일)
                'backCompleteDt', // 반품완료일시
                'orderNo', // 주문번호
                'orderName', // 주문자
                'orderTypeFl', // 주문유형
                'orderGoodsNo', // 상품주문번호
                'goodsCd', // 상품코드(자체상품코드)
                'goodsTaxInfo', // 상품 부가세
                'orderGoodsNm', // 주문상품
                'orderGoodsNmGlobal', // 주문상품(해외상점)
                'goodsCnt', // 수량
                'orgGoodsPrice', // 판매가 (판매가가 goodsPrice지만 상품금액에서 사용하고있어 래거쉬보장 문제로 orgGoodsPrice로 사용)
                'goodsPrice', // 상품금액
                'totalGoodsPrice', // 총 상품금액
                'deliveryCharge', // 배송비
                'totalDeliveryCharge', // 총 배송비
                'goodsPriceGlobal', // 상품금액(해외상점)
                'totalGoodsPriceGlobal', // 총 상품금액(해외상점)
                'deliveryChargeGlobal', // 배송비(해외상점)
                'totalDeliveryChargeGlobal', // 총 배송비(해외상점)
                'processStatus', // 처리상태
                'reason', // 사유
                'brandNm', // 브랜드
                'goodsModelNo', // 모델명
                'makerNm', // 제조사
                'deliverySno', // 배송번호
                'commission', // 수수료율
                'costPrice', // 매입가
                'invoiceNo', // 송장번호
                'receiverName', // 수령자
                //'receiverAddress', // 수령자 주소
                'multiShippingCd', // 배송지
                'orderMemo', // 배송 메시지
                'hscode', // HS코드
                'gift', // 사은품
                //'processor', // 처리자
                'adminMemo', // 관리자메모
                'phoneNumber', // 휴대폰 번호
            ],
        ],

        /*
        * 환불리스트
        */
        'list_refund_order' => [
            'defaultList' => [ //디폴트 리스트
                'check', // 선택
                'no', // 번호
                'domainFl', // 상점구분
                'regDt', // 주문일시
                'refundDt', // 환불신청일(환불접수일)
                'refundCompleteDt', // 환불완료일시
                'orderNo', // 주문번호
                'orderName', // 주문자
                'orderGoodsNo', // 상품주문번호
                'orderGoodsNm', // 주문상품
                'goodsCnt', // 수량
                'goodsPrice', // 상품금액
                'totalGoodsPrice', // 총 상품금액
                'deliveryCharge', // 배송비
                'totalDeliveryCharge', // 총 배송비
                //'totalRefundPrice', // 총 환불금액
                'processStatus', // 처리상태
                'reason', // 사유
                //'processor', // 처리자
                'refundMethod', // 환불수단
                'adminMemo', // 관리자메모
                'orderTypeFl', // 주문유형
            ],
            'list' => [ //전체리스트
                'check', // 선택
                'no', // 번호
                'domainFl', // 상점구분
                'regDt', // 주문일시
                'refundDt', // 환불신청일(환불접수일)
                'refundCompleteDt', // 환불완료일시
                'orderNo', // 주문번호
                'orderName', // 주문자
                'orderTypeFl', // 주문유형
                'orderGoodsNo', // 상품주문번호
                'goodsCd', // 상품코드(자체상품코드)
                'goodsTaxInfo', // 상품 부가세
                'orderGoodsNm', // 주문상품
                'orderGoodsNmGlobal', // 주문상품(해외상점)
                'goodsCnt', // 수량
                'orgGoodsPrice', // 판매가 (판매가가 goodsPrice지만 상품금액에서 사용하고있어 래거쉬보장 문제로 orgGoodsPrice로 사용)
                'goodsPrice', // 상품금액
                'totalGoodsPrice', // 총 상품금액
                'deliveryCharge', // 배송비
                'totalDeliveryCharge', // 총 배송비
                //'totalRefundPrice', // 총 환불금액
                'goodsPriceGlobal', // 상품금액(해외상점)
                'totalGoodsPriceGlobal', // 총 상품금액(해외상점)
                'deliveryChargeGlobal', // 배송비(해외상점)
                'totalDeliveryChargeGlobal', // 총 배송비(해외상점)
                //'totalRefundPriceGlobal', // 총 환불금액(해외상점)
                'processStatus', // 처리상태
                'reason', // 사유
                'brandNm', // 브랜드
                'goodsModelNo', // 모델명
                'makerNm', // 제조사
                'deliverySno', // 배송번호
                'commission', // 수수료율
                'costPrice', // 매입가
                'invoiceNo', // 송장번호
                'receiverName', // 수령자
                //'receiverAddress', // 수령자 주소
                'multiShippingCd', // 배송지
                'orderMemo', // 배송 메시지
                'hscode', // HS코드
                'gift', // 사은품
                //'processor', // 처리자
                'refundMethod', // 환불수단
                'adminMemo', // 관리자메모
                'phoneNumber', // 휴대폰 번호
            ],
        ],

        /*
        * 고객 교환/반품/환불신청 관리 - 교환
        * @todo 추후 공급사 메뉴에 추가될 예정
        */
        'list_user_exchange' => [
            'defaultList' => [ //디폴트 리스트
                'check', // 선택
                'no', // 번호
                'domainFl', // 상점구분
                'regDt', // 주문일시
                'userHandleRegDt', // 신청일시
                'userHandleActDt', // 처리일시
                'userHandleReason', // 사유
                'orderNo', // 주문번호
                'orderName', // 주문자
                'orderGoodsNo', // 상품주문번호
                'orderGoodsNm', // 주문상품
                'userHandleOrderStatus', // 주문상태
                'userHandleGoodsCnt', // 신청수량
                'goodsPrice', // 상품금액
                'totalGoodsPrice', // 총 상품금액
                'deliveryCharge', // 배송비
                'totalDeliveryCharge', // 총 배송비
                'processStatus', // 처리상태
                //'processor', // 처리자
                'adminMemo', // 관리자메모
            ],
            'list' => [ //전체리스트
                'check', // 선택
                'no', // 번호
                'domainFl', // 상점구분
                'regDt', // 주문일시
                'userHandleRegDt', // 신청일시
                'userHandleActDt', // 처리일시
                'userHandleReason', // 사유
                'orderNo', // 주문번호
                'orderName', // 주문자
                'orderGoodsNo', // 상품주문번호
                'orderGoodsNm', // 주문상품
                'goodsCd', // 상품코드(자체상품코드)
                'goodsTaxInfo', // 상품 부가세
                'orderGoodsNmGlobal', // 주문상품(해외상점)
                'userHandleOrderStatus', // 주문상태
                'userHandleGoodsCnt', // 신청수량
                'goodsPrice', // 상품금액
                'totalGoodsPrice', // 총 상품금액
                'deliveryCharge', // 배송비
                'totalDeliveryCharge', // 총 배송비
                'goodsPriceGlobal', // 상품금액(해외상점)
                'totalGoodsPriceGlobal', // 총 상품금액(해외상점)
                'deliveryChargeGlobal', // 배송비(해외상점)
                'totalDeliveryChargeGlobal', // 총 배송비(해외상점)
                'processStatus', // 처리상태
                'brandNm', // 브랜드
                'goodsModelNo', // 모델명
                'makerNm', // 제조사
                'deliverySno', // 배송번호
                'commission', // 수수료율
                'costPrice', // 매입가
                'invoiceNo', // 송장번호
                'receiverName', // 수령자
                //'receiverAddress', // 수령자 주소
                'multiShippingCd', // 배송지
                'orderMemo', // 배송 메시지
                'hscode', // HS코드
                'gift', // 사은품
                //'processor', // 처리자
                'memo', // 메모
                'adminMemo', // 관리자메모
                'phoneNumber', // 휴대폰 번호
            ],
        ],

        /*
        * 고객 교환/반품/환불신청 관리 - 반품
        * @todo 추후 공급사 메뉴에 추가될 예정
        */
        'list_user_back' => [
            'defaultList' => [ //디폴트 리스트
                'check', // 선택
                'no', // 번호
                'domainFl', // 상점구분
                'regDt', // 주문일시
                'userHandleRegDt', // 신청일시
                'userHandleActDt', // 처리일시
                'userHandleReason', // 사유
                'orderNo', // 주문번호
                'orderName', // 주문자
                'orderGoodsNo', // 상품주문번호
                'orderGoodsNm', // 주문상품
                'userHandleOrderStatus', // 주문상태
                'userHandleGoodsCnt', // 신청수량
                'goodsPrice', // 상품금액
                'totalGoodsPrice', // 총 상품금액
                'deliveryCharge', // 배송비
                'totalDeliveryCharge', // 총 배송비
                'processStatus', // 처리상태
                //'processor', // 처리자
                'adminMemo', // 관리자메모
            ],
            'list' => [ //전체리스트
                'check', // 선택
                'no', // 번호
                'domainFl', // 상점구분
                'regDt', // 주문일시
                'userHandleRegDt', // 신청일시
                'userHandleActDt', // 처리일시
                'userHandleReason', // 사유
                'orderNo', // 주문번호
                'orderName', // 주문자
                'orderGoodsNo', // 상품주문번호
                'orderGoodsNm', // 주문상품
                'goodsCd', // 상품코드(자체상품코드)
                'goodsTaxInfo', // 상품 부가세
                'orderGoodsNmGlobal', // 주문상품(해외상점)
                'userHandleOrderStatus', // 주문상태
                'userHandleGoodsCnt', // 신청수량
                'goodsPrice', // 상품금액
                'totalGoodsPrice', // 총 상품금액
                'deliveryCharge', // 배송비
                'totalDeliveryCharge', // 총 배송비
                'goodsPriceGlobal', // 상품금액(해외상점)
                'totalGoodsPriceGlobal', // 총 상품금액(해외상점)
                'deliveryChargeGlobal', // 배송비(해외상점)
                'totalDeliveryChargeGlobal', // 총 배송비(해외상점)
                'processStatus', // 처리상태
                'brandNm', // 브랜드
                'goodsModelNo', // 모델명
                'makerNm', // 제조사
                'deliverySno', // 배송번호
                'commission', // 수수료율
                'costPrice', // 매입가
                'invoiceNo', // 송장번호
                'receiverName', // 수령자
                //'receiverAddress', // 수령자 주소
                'multiShippingCd', // 배송지
                'orderMemo', // 배송 메시지
                'hscode', // HS코드
                'gift', // 사은품
                //'processor', // 처리자
                'memo', // 메모
                'adminMemo', // 관리자메모
                'phoneNumber', // 휴대폰 번호
            ],
        ],

        /*
        * 고객 교환/반품/환불신청 관리 - 환불
        *  @todo 추후 공급사 메뉴에 추가될 예정
        */
        'list_user_refund' => [
            'defaultList' => [ //디폴트 리스트
                'check', // 선택
                'no', // 번호
                'domainFl', // 상점구분
                'regDt', // 주문일시
                'userHandleRegDt', // 신청일시
                'userHandleActDt', // 처리일시
                'userHandleReason', // 사유
                'orderNo', // 주문번호
                'orderName', // 주문자
                'orderGoodsNo', // 상품주문번호
                'orderGoodsNm', // 주문상품
                'userHandleOrderStatus', // 주문상태
                'userHandleGoodsCnt', // 신청수량
                'goodsPrice', // 상품금액
                'totalGoodsPrice', // 총 상품금액
                'deliveryCharge', // 배송비
                'totalDeliveryCharge', // 총 배송비
                'processStatus', // 처리상태
                //'processor', // 처리자
                'adminMemo', // 관리자메모
            ],
            'list' => [ //전체리스트
                'check', // 선택
                'no', // 번호
                'domainFl', // 상점구분
                'regDt', // 주문일시
                'userHandleRegDt', // 신청일시
                'userHandleActDt', // 처리일시
                'userHandleReason', // 사유
                'orderNo', // 주문번호
                'orderName', // 주문자
                'orderGoodsNo', // 상품주문번호
                'orderGoodsNm', // 주문상품
                'goodsCd', // 상품코드(자체상품코드)
                'goodsTaxInfo', // 상품 부가세
                'orderGoodsNmGlobal', // 주문상품(해외상점)
                'userHandleOrderStatus', // 주문상태
                'userHandleGoodsCnt', // 신청수량
                'goodsPrice', // 상품금액
                'totalGoodsPrice', // 총 상품금액
                'deliveryCharge', // 배송비
                'totalDeliveryCharge', // 총 배송비
                'goodsPriceGlobal', // 상품금액(해외상점)
                'totalGoodsPriceGlobal', // 총 상품금액(해외상점)
                'deliveryChargeGlobal', // 배송비(해외상점)
                'totalDeliveryChargeGlobal', // 총 배송비(해외상점)
                'processStatus', // 처리상태
                'brandNm', // 브랜드
                'goodsModelNo', // 모델명
                'makerNm', // 제조사
                'deliverySno', // 배송번호
                'commission', // 수수료율
                'costPrice', // 매입가
                'invoiceNo', // 송장번호
                'receiverName', // 수령자
                //'receiverAddress', // 수령자 주소
                'multiShippingCd', // 배송지
                'orderMemo', // 배송 메시지
                'hscode', // HS코드
                'gift', // 사은품
                //'processor', // 처리자
                'memo', // 메모
                'adminMemo', // 관리자메모
                'phoneNumber', // 휴대폰 번호
            ],
        ],

        /*
        * 주문상세페이지 - 주문내역
        */
        'view_order' => [
            'defaultList' => [ //디폴트 리스트
                'check', // 선택
                'no', // 번호
                'orderGoodsNo', // 상품주문번호
                'goodsImage', // 이미지
                'orderGoodsNm', // 주문상품
                'goodsCnt', // 수량
                'goodsPrice', // 상품금액
                'deliveryCharge', // 배송비
                'totalOrderPrice', // 총 주문금액
                'invoiceNo', // 송장번호
                'processStatus', // 처리상태
            ],
            'list' => [ //전체리스트
                'check', // 선택
                'no', // 번호
                'orderGoodsNo', // 상품주문번호
                'goodsCd', // 상품코드(자체상품코드)
                'goodsTaxInfo', // 상품 부가세
                'goodsImage', // 이미지
                'orderGoodsNm', // 주문상품
                'orderGoodsNmGlobal', // 주문상품(해외상점)
                'goodsCnt', // 수량
                'orgGoodsPrice', // 판매가 (판매가가 goodsPrice지만 상품금액에서 사용하고있어 래거쉬보장 문제로 orgGoodsPrice로 사용)
                'goodsPrice', // 상품금액
                'totalGoodsPrice', // 총 상품금액
                'costPrice', // 매입가
                'deliveryCharge', // 배송비
                'totalDeliveryCharge', // 총 배송비
                'totalOrderPrice', // 총 주문금액
                'purchaseNm', // 매입처
                'brandNm', // 브랜드
                'goodsModelNo', // 모델명
                'makerNm', // 제조사
                'scmNm', // 공급사
                'commission', // 수수료율
                'hscode', // HS코드
                'invoiceNo', // 송장번호
                'multiShippingCd', // 배송지
                'processStatus', // 처리상태
            ],
        ],

        /*
        * 주문상세페이지 - 교환내역
        */
        'view_exchange' => [
            'defaultList' => [ //디폴트 리스트
                'check', // 선택
                'no', // 번호
                'orderGoodsNo', // 상품주문번호
                'goodsImage', // 이미지
                'orderGoodsNm', // 주문상품
                'goodsCnt', // 수량
                'goodsPrice', // 상품금액
                'deliveryCharge', // 배송비
                'totalOrderPrice', // 총 주문금액
                'invoiceNo', // 송장번호
                'processStatus', // 처리상태
            ],
            'list' => [ //전체리스트
                'check', // 선택
                'no', // 번호
                'orderGoodsNo', // 상품주문번호
                'goodsCd', // 상품코드(자체상품코드)
                'goodsTaxInfo', // 상품 부가세
                'goodsImage', // 이미지
                'orderGoodsNm', // 주문상품
                'orderGoodsNmGlobal', // 주문상품(해외상점)
                'goodsCnt', // 수량
                'orgGoodsPrice', // 판매가 (판매가가 goodsPrice지만 상품금액에서 사용하고있어 래거쉬보장 문제로 orgGoodsPrice로 사용)
                'goodsPrice', // 상품금액
                'totalGoodsPrice', // 총 상품금액
                'costPrice', // 매입가
                'deliveryCharge', // 배송비
                'totalDeliveryCharge', // 총 배송비
                'totalOrderPrice', // 총 주문금액
                'purchaseNm', // 매입처
                'brandNm', // 브랜드
                'goodsModelNo', // 모델명
                'makerNm', // 제조사
                'scmNm', // 공급사
                'commission', // 수수료율
                'hscode', // HS코드
                'invoiceNo', // 송장번호
                'multiShippingCd', // 배송지
                'processStatus', // 처리상태
            ],
        ],

        /*
        * 주문상세페이지 - 교환취소내역
        */
        'view_exchangeCancel' => [
            'defaultList' => [ //디폴트 리스트
                'check', // 선택
                'no', // 번호
                'orderGoodsNo', // 상품주문번호
                'goodsImage', // 이미지
                'orderGoodsNm', // 주문상품
                'goodsCnt', // 수량
                'goodsPrice', // 상품금액
                'deliveryCharge', // 배송비
                'totalOrderPrice', // 총 주문금액
                'invoiceNo', // 송장번호
                'processStatus', // 처리상태
            ],
            'list' => [ //전체리스트
                'check', // 선택
                'no', // 번호
                'orderGoodsNo', // 상품주문번호
                'goodsCd', // 상품코드(자체상품코드)
                'goodsTaxInfo', // 상품 부가세
                'goodsImage', // 이미지
                'orderGoodsNm', // 주문상품
                'orderGoodsNmGlobal', // 주문상품(해외상점)
                'goodsCnt', // 수량
                'orgGoodsPrice', // 판매가 (판매가가 goodsPrice지만 상품금액에서 사용하고있어 래거쉬보장 문제로 orgGoodsPrice로 사용)
                'goodsPrice', // 상품금액
                'totalGoodsPrice', // 총 상품금액
                'costPrice', // 매입가
                'deliveryCharge', // 배송비
                'totalDeliveryCharge', // 총 배송비
                'totalOrderPrice', // 총 주문금액
                'purchaseNm', // 매입처
                'brandNm', // 브랜드
                'goodsModelNo', // 모델명
                'makerNm', // 제조사
                'scmNm', // 공급사
                'commission', // 수수료율
                'hscode', // HS코드
                'invoiceNo', // 송장번호
                'multiShippingCd', // 배송지
                'processStatus', // 처리상태
            ],
        ],

        /*
        * 주문상세페이지 - 교환추가내역
        */
        'view_exchangeAdd' => [
            'defaultList' => [ //디폴트 리스트
                'check', // 선택
                'no', // 번호
                'orderGoodsNo', // 상품주문번호
                'goodsImage', // 이미지
                'orderGoodsNm', // 주문상품
                'goodsCnt', // 수량
                'goodsPrice', // 상품금액
                'deliveryCharge', // 배송비
                'totalOrderPrice', // 총 주문금액
                'invoiceNo', // 송장번호
                'processStatus', // 처리상태
            ],
            'list' => [ //전체리스트
                'check', // 선택
                'no', // 번호
                'orderGoodsNo', // 상품주문번호
                'goodsCd', // 상품코드(자체상품코드)
                'goodsTaxInfo', // 상품 부가세
                'goodsImage', // 이미지
                'orderGoodsNm', // 주문상품
                'orderGoodsNmGlobal', // 주문상품(해외상점)
                'goodsCnt', // 수량
                'orgGoodsPrice', // 판매가 (판매가가 goodsPrice지만 상품금액에서 사용하고있어 래거쉬보장 문제로 orgGoodsPrice로 사용)
                'goodsPrice', // 상품금액
                'totalGoodsPrice', // 총 상품금액
                'costPrice', // 매입가
                'deliveryCharge', // 배송비
                'totalDeliveryCharge', // 총 배송비
                'totalOrderPrice', // 총 주문금액
                'purchaseNm', // 매입처
                'brandNm', // 브랜드
                'goodsModelNo', // 모델명
                'makerNm', // 제조사
                'scmNm', // 공급사
                'commission', // 수수료율
                'hscode', // HS코드
                'invoiceNo', // 송장번호
                'multiShippingCd', // 배송지
                'processStatus', // 처리상태
            ],
        ],

        /*
        * 주문상세페이지 - 반품내역
        */
        'view_back' => [
            'defaultList' => [ //디폴트 리스트
                'check', // 선택
                'no', // 번호
                'orderGoodsNo', // 상품주문번호
                'goodsImage', // 이미지
                'orderGoodsNm', // 주문상품
                'goodsCnt', // 수량
                'goodsPrice', // 상품금액
                'deliveryCharge', // 배송비
                'totalOrderPrice', // 총 주문금액
                'invoiceNo', // 송장번호
                'processStatus', // 처리상태
            ],
            'list' => [ //전체리스트
                'check', // 선택
                'no', // 번호
                'orderGoodsNo', // 상품주문번호
                'goodsCd', // 상품코드(자체상품코드)
                'goodsTaxInfo', // 상품 부가세
                'goodsImage', // 이미지
                'orderGoodsNm', // 주문상품
                'orderGoodsNmGlobal', // 주문상품(해외상점)
                'goodsCnt', // 수량
                'orgGoodsPrice', // 판매가 (판매가가 goodsPrice지만 상품금액에서 사용하고있어 래거쉬보장 문제로 orgGoodsPrice로 사용)
                'goodsPrice', // 상품금액
                'totalGoodsPrice', // 총 상품금액
                'costPrice', // 매입가
                'deliveryCharge', // 배송비
                'totalDeliveryCharge', // 총 배송비
                'totalOrderPrice', // 총 주문금액
                'purchaseNm', // 매입처
                'brandNm', // 브랜드
                'goodsModelNo', // 모델명
                'makerNm', // 제조사
                'scmNm', // 공급사
                'commission', // 수수료율
                'hscode', // HS코드
                'invoiceNo', // 송장번호
                'multiShippingCd', // 배송지
                'processStatus', // 처리상태
            ],
        ],

        /*
        * 주문상세페이지 - 환불내역
        */
        'view_refund' => [
            'defaultList' => [ //디폴트 리스트
                'check', // 선택
                'no', // 번호
                'orderGoodsNo', // 상품주문번호
                'goodsImage', // 이미지
                'orderGoodsNm', // 주문상품
                'goodsCnt', // 수량
                'goodsPrice', // 상품금액
                'deliveryCharge', // 배송비
                'totalOrderPrice', // 총 주문금액
                //'totalRefundPrice', // 총 환불금액
                'invoiceNo', // 송장번호
                'processStatus', // 처리상태
            ],
            'list' => [ //전체리스트
                'check', // 선택
                'no', // 번호
                'orderGoodsNo', // 상품주문번호
                'goodsCd', // 상품코드(자체상품코드)
                'goodsTaxInfo', // 상품 부가세
                'goodsImage', // 이미지
                'orderGoodsNm', // 주문상품
                'orderGoodsNmGlobal', // 주문상품(해외상점)
                'goodsCnt', // 수량
                'orgGoodsPrice', // 판매가 (판매가가 goodsPrice지만 상품금액에서 사용하고있어 래거쉬보장 문제로 orgGoodsPrice로 사용)
                'goodsPrice', // 상품금액
                'totalGoodsPrice', // 총 상품금액
                'costPrice', // 매입가
                'deliveryCharge', // 배송비
                'totalDeliveryCharge', // 총 배송비
                'totalOrderPrice', // 총 주문금액
                //'totalRefundPrice', // 총 환불금액
                'purchaseNm', // 매입처
                'brandNm', // 브랜드
                'goodsModelNo', // 모델명
                'makerNm', // 제조사
                'scmNm', // 공급사
                'commission', // 수수료율
                'hscode', // HS코드
                'invoiceNo', // 송장번호
                'multiShippingCd', // 배송지
                'processStatus', // 처리상태
                'refundPrice', // 환불 상품금액
                'refundUseMileage', // 상품 환원 마일리지
                'refundUseDeposit', // 상품 환원 예치금
                'refundCharge', // 상품 환불 수수료
                'refundUseMileageCommission', // 마일리지 환불 수수료
                'refundUseDepositCommission', // 예치금 환불 수수료
                'refundDeliveryCharge', // 환불 배송비
                'refundDeliveryUseMileage', // 배송비 환원 마일리지
                'refundDeliveryUseDeposit', // 배송비 환원 예치금
                'refundDeliveryInsuranceFee', // 환불 해외배송보험료

            ],
        ],
    ];


    public $orderKeyNameList = [
        'check' => '선택',
        'no' => '번호',
        'domainFl' => '상점구분',
        'regDt' => '주문일시',
        'paymentDt' => '입금일시',
        'orderNo' => '주문번호',
        'orderName' => '주문자',
        'orderTypeFl' => '주문유형',
        'orderGoodsNm' => '주문상품',
        'orderGoodsNmGlobal' => '주문상품(해외상점)',
        'totalGoodsPrice' => '총 상품금액',
        'totalDeliveryCharge' => '총 배송비',
        'totalDcPrice' => '총 할인금액',
        'totalUseAddedPrice' => '총 부가결제금액',
        'totalOrderPrice' => '총 주문금액',
        'totalRealSettlePrice' => '총 실결제금액',
        'totalGoodsPriceGlobal' => '총 상품금액(해외상점)',
        'totalDeliveryChargeGlobal' => '총 배송비(해외상점)',
        'totalDcPriceGlobal' => '총 할인금액(해외상점)',
        'totalOrderPriceGlobal' => '총 주문금액(해외상점)',
        'totalRealSettlePriceGlobal' => '총 실결제금액(해외상점)',
        'settleKind' => '결제방법',
        'settleStatus' => '결제상태',
        'processStatus' => '처리상태',
        'noDelivery' => '미배송',
        'deliverying' => '배송중',
        'deliveryed' => '배송완료',
        'cancel' => '취소',
        'exchange' => '교환',
        'back' => '반품',
        'refund' => '환불',
        'receiverName' => '수령자',
        'receiverAddress' => '수령자 주소',
        'orderMemo' => '배송 메시지',
        'receipt' => '영수증 신청여부',
        'gift' => '사은품',
        'memo' => '메모',
        'adminMemo' => '관리자메모',
        'deliveryDt' => '배송일시',
        'deliveryCompleteDt' => '배송완료일시',
        'finishDt' => '구매확정일시',
        'orderGoodsNo' => '상품주문번호',
        'goodsCd' => '상품코드(자체상품코드)',
        'goodsTaxInfo' => '상품 부가세',
        'goodsCnt' => '수량',
        'orgGoodsPrice' => '판매가',
        'goodsPrice' => '상품금액',
        'deliveryCharge' => '배송비',
        'goodsPriceGlobal' => '상품금액(해외상점)',
        'deliveryChargeGlobal' => '배송비(해외상점)',
        'purchaseNm' => '매입처',
        'brandNm' => '브랜드',
        'goodsModelNo' => '모델명',
        'makerNm' => '제조사',
        'deliverySno' => '배송번호',
        'scmNm' => '공급사',
        'commission' => '수수료율',
        'costPrice' => '매입가',
        'invoiceNo' => '송장번호',
        'hscode' => 'HS코드',
        'bankSender' => '입금자',
        'bankAccount' => '입금계좌',
        'regDtInterval' => '경과일자',
        'cancelDt' => '취소신청일<br />(취소접수일)',
        'cancelCompleteDt' => '취소완료일시',
        //'totalUseAddedRefundPrice' => '총 부가결제 환원금액',
        //'totalCancelPrice' => '총 취소금액',
        //'totalCancelPriceGlobal' => '총 취소금액(해외상점)',
        'reason' => '사유',
        //'processor' => '처리자',
        'exchangeDt' => '교환신청일<br />(교환접수일)',
        'exchangeCompleteDt' => '교환완료일시',
        'backDt' => '반품신청일<br />(반품접수일)',
        'backCompleteDt' => '반품완료일시',
        'refundDt' => '환불신청일<br />(환불접수일)',
        'refundCompleteDt' => '환불완료일시',
        //'totalRefundPrice' => '총 환불금액',
        //'totalRefundPriceGlobal' => '총 환불금액(해외상점)',
        'refundMethod' => '환불수단',
        'refundStatus' => '환불처리',
        'userHandleReason' => '사유',
        'userHandleRegDt' => '신청일시',
        'userHandleActDt' => '처리일시',
        'userHandleOrderStatus' => '주문상태',
        'userHandleGoodsCnt' => '신청수량',
        'goodsImage' => '이미지',
        'multiShippingCd' => '배송지',
        'phoneNumber' => '주문자휴대폰번호',

        'refundPrice' => '환불 상품금액',
        'refundUseMileage' => '상품 환원<br /> 마일리지',
        'refundUseDeposit' => '상품 환원<br /> 예치금',
        'refundCharge' => '상품 환불<br /> 수수료',
        'refundUseMileageCommission' => '마일리지<br /> 환불 수수료',
        'refundUseDepositCommission' => '예치금<br /> 환불 수수료',
        'refundDeliveryCharge' => '환불 배송비',
        'refundDeliveryUseMileage' => '배송비 환원<br /> 마일리지',
        'refundDeliveryUseDeposit' => '배송비 환원<br /> 예치금',
        'refundDeliveryInsuranceFee' => '환불 해외배송<br />보험료',
    ];


    public function __construct()
    {

    }

    /**
 * 선택된 주문리스트 그리드 항목 리스트 로드
 *
 * @param string $orderGridMode
 *
 * @return array $returnData
 */
    public function getSelectOrderGridConfigList($orderGridMode)
    {
        $db = \App::load('DB');
        $arrBind = [];
        $resultList = [];
        $orderGridlist = [];

        $fieldType = DBTableField::getFieldTypes('tableManagerOrderGridConfig');
        $strSQL = 'SELECT ogData FROM ' . DB_MANAGER_ORDER_GRID_CONFIG . ' WHERE ogManagerSno = ? AND ogApplyMode = ?';
        $db->bind_param_push($arrBind, $fieldType['ogManagerSno'], Session::get('manager.sno'));
        $db->bind_param_push($arrBind, $fieldType['ogApplyMode'], $orderGridMode);
        $getData = $db->query_fetch($strSQL, $arrBind, false);

        if(count($getData) > 0){
            $orderGridlist = json_decode($getData['ogData'], true);
        }
        else {
            //저장결과가 없을시 디폴트 리스트 반환
            if (Manager::isProvider()) {
                //공급사일시
                $orderGridlist = $this->orderScmSubList[$orderGridMode]['defaultList'];
            }
            else {
                //본사일시
                $orderGridlist = $this->orderScmMainList[$orderGridMode]['defaultList'];
            }
        }

        $resultList = self::setListNameApply($orderGridlist);

        //주문상세창 열기 옵션 추가
        $resultList['openLinkOption'] = self::getLinkOptionConfigList($orderGridlist);

        return $resultList;
    }

    /**
     * 전체 주문리스트 그리드 항목 리스트 로드
     *
     * @param string $orderGridMode
     * @param string $gridSort
     *
     * @return array $returnData
     */
    public function getAllOrderGridConfigList($orderGridMode, $gridSort)
    {
        $orderGridlist = [];
        $resultList = [];

        //저장결과가 없을시 디폴트 리스트 반환
        if (Manager::isProvider()) {
            //공급사일시
            $orderGridlist = $this->orderScmSubList[$orderGridMode]['list'];
        }
        else {
            //본사일시
            $orderGridlist = $this->orderScmMainList[$orderGridMode]['list'];
        }

        $resultList = self::setListNameApply($orderGridlist);

        switch($gridSort){
            //가나다순
            case 'desc' :
                asort($resultList);
                break;

            //가나다 역순
            case 'asc' :
                arsort($resultList);
                break;

            default :
                break;
        }

        return $resultList;
    }

    /**
     * 주문리스트 그리드 항목 리스트 로드
     *
     * @param string $orderGridMode
     *
     * @return string
     */
    public function getOrderGridConfigList($orderGridMode)
    {
        $orderGridConfigList = [];
        //전체리스트
        $orderGridConfigList['all'] = self::getAllOrderGridConfigList($orderGridMode, '');
        //선택한리스트
        $orderGridConfigList['select'] = self::getSelectOrderGridConfigList($orderGridMode);
        //교집합 리스트
        $orderGridConfigList['intersect'] = array_intersect(array_flip($orderGridConfigList['all']), array_flip($orderGridConfigList['select']));

        return $orderGridConfigList;
    }

    /**
     * 정렬순서에 따른 전체 조회항목 로드
     *
     * @param string $orderGridMode
     * @paran string $gridSort
     *
     * @return string
     */
    public function getOrderGridConfigAllSortList($orderGridMode, $gridSort)
    {
        $orderGridConfigList = [];
        //전체리스트
        $orderGridConfigList = self::getAllOrderGridConfigList($orderGridMode, $gridSort);

        return $orderGridConfigList;
    }

    /**
     * 주문리스트 그리드 항목 리턴 배열 정리
     *
     * @param array $orderGridlist
     *
     * @return array $resultList;
     */
    private function setListNameApply($orderGridlist)
    {
        $resultList = [];

        //키, 배열 전환
        $orderGridlistReverse = array_flip($orderGridlist);
        //공통 값 체크
        $listTmp_stdKey = array_intersect_key($orderGridlistReverse, $this->orderKeyNameList);
        $listTmp_stdValue = array_intersect_key($this->orderKeyNameList, $orderGridlistReverse);

        $resultList = array_merge((array)$listTmp_stdKey, (array)$listTmp_stdValue);

        return $resultList;
    }

    /**
     * 주문상세창 옵션 값 반환
     *
     * @param array $orderGridList
     *
     * @return string $resultOption
     * */
    public function getLinkOptionConfigList($orderGridList)
    {
        $resultOption = '';

        $resultOption = $orderGridList['openLinkOption'];

        return $resultOption;
    }




    /**
     * 주문리스트 그리드 항목 리스트 저장
     *
     * @param array $postValue
     *
     * @return void
     */
    public function setOrderGridConfigList($postValue)
    {
        $db = \App::load('DB');

        $sessionManagerSno = Session::get('manager.sno');
        $arrBind = [];
        $getData = [];

        $fieldType = DBTableField::getFieldTypes('tableManagerOrderGridConfig');
        $strSQL = 'SELECT sno FROM ' . DB_MANAGER_ORDER_GRID_CONFIG . ' WHERE ogManagerSno = ? AND ogApplyMode = ?';
        $db->bind_param_push($arrBind, $fieldType['ogManagerSno'], $sessionManagerSno);
        $db->bind_param_push($arrBind, $fieldType['ogApplyMode'], $postValue['orderGridMode']);
        $getData = $db->query_fetch($strSQL, $arrBind, false);

        //조회항목 설정 추가
        $postValue['orderGridList']['openLinkOption'] = $postValue['openLinkOption'];

        $arrBind = [];
        if(count($getData) > 0){
            //update
            $updateField = [
                'ogData=?',
            ];
            $db->bind_param_push($arrBind, 's', json_encode($postValue['orderGridList'], JSON_FORCE_OBJECT));
            $db->bind_param_push($arrBind, 'i', $sessionManagerSno);
            $db->bind_param_push($arrBind, 's', $postValue['orderGridMode']);
            $db->set_update_db(DB_MANAGER_ORDER_GRID_CONFIG, $updateField, 'ogManagerSno = ? AND ogApplyMode = ?', $arrBind, false);
        }
        else {
            //insert
            $arrData = [
                'ogManagerSno' => $sessionManagerSno,
                'ogApplyMode' => $postValue['orderGridMode'],
                'ogData' => json_encode($postValue['orderGridList'], JSON_FORCE_OBJECT),
            ];
            $arrBind = $db->get_binding(DBTableField::tableManagerOrderGridConfig(), $arrData, 'insert');
            $db->set_insert_db(DB_MANAGER_ORDER_GRID_CONFIG, $arrBind['param'], $arrBind['bind'], 'y');
        }
    }

    /**
     * 페이지명을 기준으로 모드 반환
     *
     * @param string $viewType
     *
     * @return string $orderGridMode
     */
    public function getOrderAdminGridMode($viewType)
    {
        $orderGridMode = '';
        switch(\Request::getFileUri()){
            //주문통합 리스트
            case 'order_list_all.php' :
                $orderGridMode = 'list_all';
                $viewType = ($viewType === 'orderGoods') ? '_order_goods' : '_order';
                break;

            //입금대기 리스트
            case 'order_list_order.php' :
                $orderGridMode = 'list_order';
                $viewType = ($viewType === 'orderGoods') ? '_order_goods' : '_order';
                break;

            //결제완료 리스트
            case 'order_list_pay.php' :
                $orderGridMode = 'list_pay';
                $viewType = ($viewType === 'orderGoods') ? '_order_goods' : '_order';
                break;

            //상품준비중 리스트
            case 'order_list_goods.php' :
                $orderGridMode = 'list_goods';
                $viewType = ($viewType === 'orderGoods') ? '_order_goods' : '_order';
                break;

            //배송중 리스트
            case 'order_list_delivery.php' :
                $orderGridMode = 'list_delivery';
                $viewType = ($viewType === 'orderGoods') ? '_order_goods' : '_order';
                break;

            //배송완료 리스트
            case 'order_list_delivery_ok.php' :
                $orderGridMode = 'list_delivery_ok';
                $viewType = ($viewType === 'orderGoods') ? '_order_goods' : '_order';
                break;

            //구매확정 리스트
            case 'order_list_settle.php' :
                $orderGridMode = 'list_settle';
                $viewType = ($viewType === 'orderGoods') ? '_order_goods' : '_order';
                break;

            //결제 중단/실패 리스트
            case 'order_list_fail.php' :
                $orderGridMode = 'list_fail';
                $viewType = '_order';
                break;

            //취소 리스트
            case 'order_list_cancel.php' :
                $orderGridMode = 'list_cancel';
                $viewType = '_order';
                break;

            //교환 리스트
            case 'order_list_exchange.php' :
                $orderGridMode = 'list_exchange';
                $viewType = '_order';
                break;

            //교환취소 리스트
            case 'order_list_exchange_cancel.php' :
                $orderGridMode = 'list_exchange_cancel';
                $viewType = '_order';
                break;

            //교환추가 리스트
            case 'order_list_exchange_add.php' :
                $orderGridMode = 'list_exchange_add';
                $viewType = '_order';
                break;

            //반품 리스트
            case 'order_list_back.php' :
                $orderGridMode = 'list_back';
                $viewType = '_order';
                break;

            //환불 리스트
            case 'order_list_refund.php' :
                $orderGridMode = 'list_refund';
                $viewType = '_order';
                break;

            //고객 교환/반품/환불신청 관리 리스트
            case 'order_list_user_exchange.php' :
                if(!$viewType || $viewType === 'exchange'){
                    $orderGridMode = 'list_user_exchange';
                }
                else if($viewType === 'back'){
                    $orderGridMode = 'list_user_back';
                }
                else if($viewType === 'refund'){
                    $orderGridMode = 'list_user_refund';
                }
                else {
                    $orderGridMode = 'list_user_exchange';
                }
                $viewType = '';
                break;

            //주문상세페이지
            case 'inc_order_view.php' :
                $orderGridMode = 'view_';
                break;

            //주문내역서 프린트
            case 'order_print.php' :
                $orderGridMode = 'view_';
                break;
        }

        $orderGridMode = $orderGridMode.$viewType;

        return $orderGridMode;
    }
}
