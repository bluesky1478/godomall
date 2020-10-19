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

namespace Bundle\Component\Goods;

use Component\Member\Manager;
use Component\Database\DBTableField;
use Session;
use Globals;
use Framework\Utility\ArrayUtils;

class GoodsAdminGrid
{
	//본사
	private $goodsScmMainList = [
		/*
        * 상품 리스트
        */
		'goods_list' => [
			'defaultList' => [ //디폴트 리스트
				'check', // 선택
				'no', // 번호
				'goodsNo', // 상품코드
				'goodsImage', // 이미지
				'goodsNm', // 상품명
				//'purchaseNo', // 매입처
				'goodsPrice', // 판매가
				'scmNo', // 공급사
				'goodsDisplayFl', // 노출상태 - goodsDisplayMobileFl
				'goodsSellFl', // 판매상태 - goodsSellMobileFl
				'stockFl', // 재고
				'regDt', // 등록일, 수정일 - modDt, delDt
			],
			'list' => [ //전체리스트
				'check', // 선택
				'no', // 번호
				'goodsNo', // 상품코드
				'goodsCd', // 자체상품코드
				'goodsImage', // 이미지
				'goodsNm', // 상품명
				'option', // 옵션
				'optionText', // 텍스트옵션
				'goodsPrice', // 판매가
				'fixedPrice', // 정가
				'costPrice', // 매입가
				'supplyPrice', // 공급가
				'commission', // 수수료율
				'taxFreeFl', // 과세/면세 - taxPercent
				'goodsDiscountFl', // 상품할인사용여부
				'mileageFl', // 마일리지사용여부
				'payLimit', // 결제수단
				//'purchaseNo', // 매입처
				'scmNo', // 공급사
				'brandCd', // 브랜드
				'makerNm', // 제조사
				'originNm', // 원산지
				'goodsModelNo', // 모델번호
				'goodsDisplayFl', // 노출상태 - goodsDisplayMobileFl
				'goodsSellFl', // 판매상태 - goodsSellMobileFl
				'soldOutFl', // 품절상태
				'stockFl', // 재고
				'orderGoodsCnt', // 결제
				'hitCnt', // 결제
				'orderRate', // 구매율
				'cartCnt', // 담기
				'wishCnt', // 관심
				'reviewCnt', // 후기
				'deliverySno', // 배송비조건
				'memo', // 관리자메모
				'regDt', // 등록일, 수정일 - modDt, delDt
			],
		],
		'goods_list_delete' => [
			'defaultList' => [ //디폴트 리스트
				'check', // 선택
				'no', // 번호
				'goodsNo', // 상품코드
				'goodsImage', // 이미지
				'goodsNm', // 상품명
				'goodsPrice', // 판매가
				'scmNo', // 공급사
				'goodsDisplayFl', // 노출상태 - goodsDisplayMobileFl
				'goodsSellFl', // 판매상태 - goodsSellMobileFl
				'stockFl', // 재고
				'delDt', // 등록일, 수정일 - modDt, delDt
			],
			'list' => [ //전체리스트
				'check', // 선택
				'no', // 번호
				'goodsNo', // 상품코드
				'goodsCd', // 자체상품코드
				'goodsImage', // 이미지
				'goodsNm', // 상품명
				'option', // 옵션
				'optionText', // 텍스트옵션
				'goodsPrice', // 판매가
				'fixedPrice', // 정가
				'costPrice', // 매입가
				'supplyPrice', // 공급가
				'commission', // 수수료율
				'taxFreeFl', // 과세/면세 - taxPercent
				'goodsDiscountFl', // 상품할인사용여부
				'mileageFl', // 마일리지사용여부
				'payLimit', // 결제수단
				'scmNo', // 공급사
				'brandCd', // 브랜드
				'makerNm', // 제조사
				'originNm', // 원산지
				'goodsModelNo', // 모델번호
				'goodsDisplayFl', // 노출상태 - goodsDisplayMobileFl
				'goodsSellFl', // 판매상태 - goodsSellMobileFl
				'soldOutFl', // 품절상태
				'stockFl', // 재고
				'orderGoodsCnt', // 결제
				'hitCnt', // 결제
				'orderRate', // 구매율
				'cartCnt', // 담기
				'wishCnt', // 관심
				'reviewCnt', // 후기
				'deliverySno', // 배송비조건
				'memo', // 관리자메모
				'delDt', // 등록일, 수정일 - modDt, delDt
			],
		],
        'goods_option_list' => [
            'defaultList' => [
                'optionCostPrice', //옵션매입가
                'optionPrice', //옵션가
                'stockCnt', //재고량
                //현재 추가 개발진행 중이므로 수정하지 마세요! 주석 처리된 내용을 수정할 경우 기능이 정상 작동하지 않거나, 추후 기능 배포시 오류의 원인이 될 수 있습니다.
                //'optionStopFl', //판매중지수량
                //'optionRequestFl', //확인요청수량
                'optionViewFl', //옵션노출상태
                'optionSellFl', //옵션품절상태
                'optionDeliveryFl', //옵션배송상태
                'optionCode', //자체옵션코드
                'optionMemo', //메모
            ],
            'list' => [
                'optionCostPrice', //옵션매입가
                'optionPrice', //옵션가
                'stockCnt', //재고량
                //현재 추가 개발진행 중이므로 수정하지 마세요! 주석 처리된 내용을 수정할 경우 기능이 정상 작동하지 않거나, 추후 기능 배포시 오류의 원인이 될 수 있습니다.
                //'optionStopFl', //판매중지수량
                //'optionRequestFl', //확인요청수량
                'optionViewFl', //옵션노출상태
                'optionSellFl', //옵션품절상태
                'optionDeliveryFl', //옵션배송상태
                'optionCode', //자체옵션코드
                'optionMemo', //메모
            ]
        ],
        'goods_batch_stock_list' => [
            'defaultList' => [
                'goodsNo',//상품코드
                'goodsImage',//이미지
                'goodsNm',//상품명
                'scmNo',//공급사
                'goodsDisplayFl',//노출상태(PC)
                'goodsDisplayMobileFl',//노출상태(모바일)
                'goodsSellFl',//판매상태(PC)
                'goodsSellMobileFl',//판매상태(모바일)
                'soldOutFl',//품절상태
                'option',//옵션
                'optionViewFl',//옵션 노출상태
                'optionSellFl',//옵션 품절상태
                'optionDeliveryFl',//옵션 배송상태
                //현재 추가 개발진행 중이므로 수정하지 마세요! 주석 처리된 내용을 수정할 경우 기능이 정상 작동하지 않거나, 추후 기능 배포시 오류의 원인이 될 수 있습니다.
                //'optionStopFl',//판매중지수량
                //'optionRequestFl',//확인요청수량
                'stockCnt',//재고
            ],
            'list' => [
                'goodsNo',//상품코드
                'goodsCd',//자체 상품코드
                'goodsImage',//이미지
                'goodsNm',//상품명
                'taxFreeFl',//과세/면세
                'fixedPrice',//정가
                'costPrice',//매입가
                'goodsPrice',//판매가
                'commission',//수수료
                'goodsModelNo',//모델번호
                'scmNo',//공급사
                'brandCd',//브랜드
                'makerNm',//제조사
                'goodsDisplayFl',//노출상태(PC)
                'goodsDisplayMobileFl',//노출상태(모바일)
                'goodsSellFl',//판매상태(PC)
                'goodsSellMobileFl',//판매상태(모바일)
                'soldOutFl',//품절상태
                'option',//옵션
                'optionViewFl',//옵션 노출상태
                'optionSellFl',//옵션 품절상태
                'optionDeliveryFl',//옵션 배송상태
                //현재 추가 개발진행 중이므로 수정하지 마세요! 주석 처리된 내용을 수정할 경우 기능이 정상 작동하지 않거나, 추후 기능 배포시 오류의 원인이 될 수 있습니다.
                //'optionStopFl',//판매중지수량
                //'optionRequestFl',//확인요청수량
                'totalStock', //상품재고
                'stockCnt',//재고
                'optionCostPrice',//옵션 매입가
                'optionPrice',//옵션가
                'mileageFl',//마일리지
                'goodsDiscountFl',//상품할인
                'goodsBenefit',//상품혜택
                'deliverySno',//배송정책
                'regDt',//등록일
                'modDt',//수정일
            ]
        ],
	];


	//공급사
	private $goodsScmSubList = [
		/*
        * 상품 리스트
        */
		'goods_list' => [
			'defaultList' => [ //디폴트 리스트
				'check', // 선택
				'no', // 번호
				'goodsNo', // 상품코드
				'goodsImage', // 이미지
				'goodsNm', // 상품명
				'goodsPrice', // 판매가
				'scmNo', // 공급사
				'goodsDisplayFl', // 노출상태 - goodsDisplayMobileFl
				'goodsSellFl', // 판매상태 - goodsSellMobileFl
				'stockFl', // 재고
				'regDt', // 등록일, 수정일 - modDt, delDt
			],
			'list' => [ //전체리스트
				'check', // 선택
				'no', // 번호
				'goodsNo', // 상품코드
				'goodsCd', // 자체상품코드
				'goodsImage', // 이미지
				'goodsNm', // 상품명
				'option', // 옵션
				'optionText', // 텍스트옵션
				'goodsPrice', // 판매가
				'fixedPrice', // 정가
				'costPrice', // 매입가
				'supplyPrice', // 공급가
				'commission', // 수수료율
				'taxFreeFl', // 과세/면세 - taxPercent
				'scmNo', // 공급사
				'brandCd', // 브랜드
				'makerNm', // 제조사
				'originNm', // 원산지
				'goodsModelNo', // 모델번호
				'goodsDisplayFl', // 노출상태 - goodsDisplayMobileFl
				'goodsSellFl', // 판매상태 - goodsSellMobileFl
				'soldOutFl', // 품절상태
				'stockFl', // 재고
				'deliverySno', // 배송비조건
				'memo', // 관리자메모
				'regDt', // 등록일, 수정일 - modDt, delDt
			],
		],
		'goods_list_delete' => [
			'defaultList' => [ //디폴트 리스트
				'check', // 선택
				'no', // 번호
				'goodsNo', // 상품코드
				'goodsImage', // 이미지
				'goodsNm', // 상품명
				'goodsPrice', // 판매가
				'goodsDisplayFl', // 노출상태 - goodsDisplayMobileFl
				'goodsSellFl', // 판매상태 - goodsSellMobileFl
				'stockFl', // 재고
				'delDt', // 등록일, 수정일 - modDt, delDt
			],
			'list' => [ //전체리스트
				'check', // 선택
				'no', // 번호
				'goodsNo', // 상품코드
				'goodsCd', // 자체 상품코드
				'goodsImage', // 이미지
				'goodsNm', // 상품명
				'option', // 옵션
				'optionText', // 텍스트옵션
				'goodsPrice', // 판매가
				'fixedPrice', // 정가
				'costPrice', // 매입가
				'supplyPrice', // 공급가
				'commission', // 수수료율
				'taxFreeFl', // 과세/면세 - taxPercent
				'scmNo', // 공급사
				'brandCd', // 브랜드
				'makerNm', // 제조사
				'originNm', // 원산지
				'goodsModelNo', // 모델번호
				'goodsDisplayFl', // 노출상태 - goodsDisplayMobileFl
				'goodsSellFl', // 판매상태 - goodsSellMobileFl
				'soldOutFl', // 품절상태
				'stockFl', // 재고
				'deliverySno', // 배송비조건
				'memo', // 관리자메모
				'delDt', // 등록일, 수정일 - modDt, delDt
			],
		],
        'goods_option_list' => [
            'defaultList' => [
                'optionCostPrice', //옵션매입가
                'optionPrice', //옵션가
                'stockCnt', //재고량
                //현재 추가 개발진행 중이므로 수정하지 마세요! 주석 처리된 내용을 수정할 경우 기능이 정상 작동하지 않거나, 추후 기능 배포시 오류의 원인이 될 수 있습니다.
                //'optionStopFl', //판매중지수량
                //'optionRequestFl', //확인요청수량
                'optionViewFl', //옵션노출상태
                'optionSellFl', //옵션품절상태
                'optionDeliveryFl', //옵션배송상태
                'optionCode', //자체옵션코드
                'optionMemo', //메모
            ],
            'list' => [
                'optionCostPrice', //옵션매입가
                'optionPrice', //옵션가
                'stockCnt', //재고량
                //현재 추가 개발진행 중이므로 수정하지 마세요! 주석 처리된 내용을 수정할 경우 기능이 정상 작동하지 않거나, 추후 기능 배포시 오류의 원인이 될 수 있습니다.
                //'optionStopFl', //판매중지수량
                //'optionRequestFl', //확인요청수량
                'optionViewFl', //옵션노출상태
                'optionSellFl', //옵션품절상태
                'optionDeliveryFl', //옵션배송상태
                'optionCode', //자체옵션코드
                'optionMemo', //메모
            ]
        ],'goods_batch_stock_list' => [
            'defaultList' => [
                'goodsNo',//상품코드
                'goodsImage',//이미지
                'goodsNm',//상품명
                'scmNo',//공급사
                'goodsDisplayFl',//노출상태(PC)
                'goodsDisplayMobileFl',//노출상태(모바일)
                'goodsSellFl',//판매상태(PC)
                'goodsSellMobileFl',//판매상태(모바일)
                'soldOutFl',//품절상태
                'option',//옵션
                'optionViewFl',//옵션 노출상태
                'optionSellFl',//옵션 품절상태
                'optionDeliveryFl',//옵션 배송상태
                //현재 추가 개발진행 중이므로 수정하지 마세요! 주석 처리된 내용을 수정할 경우 기능이 정상 작동하지 않거나, 추후 기능 배포시 오류의 원인이 될 수 있습니다.
                //'optionStopFl',//판매중지수량
                //'optionRequestFl',//확인요청수량
                'stockCnt',//재고
            ],
            'list' => [
                'goodsNo',//상품코드
                'goodsCd',//자체 상품코드
                'goodsImage',//이미지
                'goodsNm',//상품명
                'taxFreeFl',//과세/면세
                'fixedPrice',//정가
                'costPrice',//매입가
                'goodsPrice',//판매가
                'commission',//수수료
                'goodsModelNo',//모델번호
                'scmNo',//공급사
                'brandCd',//브랜드
                'makerNm',//제조사
                'goodsDisplayFl',//노출상태(PC)
                'goodsDisplayMobileFl',//노출상태(모바일)
                'goodsSellFl',//판매상태(PC)
                'goodsSellMobileFl',//판매상태(모바일)
                'soldOutFl',//품절상태
                'option',//옵션
                'optionViewFl',//옵션 노출상태
                'optionSellFl',//옵션 품절상태
                'optionDeliveryFl',//옵션 배송상태
                //현재 추가 개발진행 중이므로 수정하지 마세요! 주석 처리된 내용을 수정할 경우 기능이 정상 작동하지 않거나, 추후 기능 배포시 오류의 원인이 될 수 있습니다.
                //'optionStopFl',//판매중지수량
                //'optionRequestFl',//확인요청수량
                'stockCnt',//재고
                'optionCostPrice',//옵션 매입가
                'optionPrice',//옵션가
                'mileageFl',//마일리지
                'goodsDiscountFl',//상품할인
                'goodsBenefit',//상품혜택
                'deliverySno',//배송정책
                'regDt',//등록일
                'modDt',//수정일
            ]
        ],
	];


	public $goodsKeyNameList = [
		'check' => '선택', // 선택
		'no' => '번호', // 번호
		'goodsNo' => '상품코드', // 상품번호
		'goodsCd' => '자체 상품코드', // 자체상품코드
		'goodsImage' => '이미지', // 이미지
		'goodsNm' => '상품명', // 상품명
		'goodsNmUs' => '상품명(영문몰)', // 영문상품명
		'goodsNmCn' => '상품명(중문몰)', // 중국상품명
		'goodsNmJs' => '상품명(일문몰)', // 일본상품명
		'option' => '옵션', // 옵션
		'optionText' => '텍스트옵션', // 텍스트옵션
		'goodsPrice' => '판매가', // 판매가
		'fixedPrice' => '정가', // 정가
		'costPrice' => '매입가', // 매입가
		'supplyPrice' => '공급가', // 공급가
		'commission' => '수수료율', // 수수료율
		'taxFreeFl' => '과세/면세', // 과세/면세 - taxPercent
		'goodsDiscountFl' =>'상품할인', // 상품할인사용여부
		'mileageFl' => '마일리지', // 마일리지사용여부
		'payLimit' => '결제수단', // 결제수단
		'purchaseNo' => '매입처', // 매입처
		'scmNo' => '공급사', // 공급사
		'brandCd' => '브랜드', // 브랜드
		'makerNm' => '제조사', // 제조사
		'originNm' => '원산지', // 원산지
		'goodsModelNo' => '모델번호', // 모델번호
		'goodsDisplayFl' => '노출상태', // 노출상태 - goodsDisplayMobileFl
		'goodsSellFl' => '판매상태', // 판매상태 - goodsSellMobileFl
		'soldOutFl' => '품절상태', // 품절상태
		'stockFl' => '재고', // 재고
		'orderGoodsCnt' => '결제', // 주문결제수
		'hitCnt' => '조회', // 조회
		'orderRate' => '구매율(%)', // 구매율(%)
		'cartCnt' => '담기', // 담기
		'wishCnt' => '관심', // 관심
		'reviewCnt' => '후기', // 후기
		'deliverySno' => '배송비조건', // 배송비조건
		'memo' => '관리자메모', // 관리자메모
		'regDt' => '등록일/수정일', // 등록일, 수정일 - modDt, delDt
		'delDt' => '등록일/삭제일', // 등록일, 수정일 - modDt, delDt
	];

	public $goodsFieldAddList = [
		'goodsCd' => 'g.goodsCd', // 자체상품코드
		'goodsNmUs' => 'g.goodsNm as goodsNmUs', // 영문상품명
		'goodsNmCn' => 'g.goodsNm as goodsNmCn', // 중국상품명
		'goodsNmJs' => 'g.goodsNm as goodsNmJs', // 일본상품명
		'option' => 'g.optionFl,g.optionName', // 옵션
		'optionText' => 'g.optionTextFl', // 텍스트옵션
		'fixedPrice' => 'g.fixedPrice', // 정가
		'costPrice' => 'g.costPrice', // 매입가
		//'supplyPrice' => '공급가', // 공급가
		//'commission' => 'g.commission', // 수수료율
		'taxFreeFl' => 'g.taxFreeFl,g.taxPercent', // 과세/면세 - taxPercent
		'goodsDiscountFl' =>'g.goodsDiscountFl', // 상품할인사용여부 g.goodsDiscount,g.goodsDiscountUnit
		'mileageFl' => 'g.mileageFl', // 마일리지사용여부 ,g.mileageGroup,g.mileageGoods,g.mileageGoodsUnit,g.mileageGroupInfo,g.mileageGroupMEmberInfo
		'payLimit' => 'g.payLimitFl,g.payLimit', // 결제수단
		'brandCd' => 'g.brandCd', // 브랜드
		'makerNm' => 'g.makerNm', // 제조사
		'originNm' => 'g.originNm', // 원산지
		'goodsModelNo' => 'g.goodsModelNo', // 모델번호
        'orderGoodsCnt' => 'g.orderGoodsCnt', // 결제
        'hitCnt' => 'g.hitCnt', // 조회
		'orderRate' => '0 as orderRate', // 구매율(%)
		'cartCnt' => 'g.cartCnt', // 담기
		'wishCnt' => 'g.wishCnt', // 관심
		'reviewCnt' => 'g.reviewCnt', // 후기
		'deliverySno' => 'g.deliverySno', // 배송비조건
		'memo' => 'g.memo', // 관리자메모
	];

    public $goodsKeyOptionNameList = [
        'optionCostPrice' => '옵션매입가', //옵션매입가
        'optionPrice' => '옵션가', //옵션가
        'stockCnt' => '재고량', //재고량
        'optionStopFl' => '판매중지수량', //판매중지수량
        'optionRequestFl' => '확인요청수량', //확인요청수량
        'optionViewFl' => '옵션노출상태', //옵션노출상태
        'optionSellFl' => '옵션품절상태', //옵션품절상태
        'optionDeliveryFl' => '옵션배송상태', //옵션배송상태
        'optionCode' => '자체옵션코드', //자체옵션코드
        'optionMemo' => '메모', //메모
    ];
    public $goodsKeyBatchStockNameList = [
        'goodsNo' => '상품코드', //상품코드
        'goodsCd' => '자체 상품코드',//자체 상품코드
        'goodsImage' => '이미지',//이미지
        'goodsNm' => '상품명',//상품명
        'taxFreeFl' => '과세/면세',//과세/면세
        'fixedPrice' => '정가',//정가
        'costPrice' => '매입가',//매입가
        'goodsPrice' => '판매가',//판매가
        'commission' => '수수료',//수수료
        'goodsModelNo' => '모델번호',//모델번호
        'scmNo' => '공급사',//공급사
        'brandCd' => '브랜드',//브랜드
        'makerNm' => '제조사',//제조사
        'goodsDisplayFl' => '노출상태(PC)',//노출상태(PC)
        'goodsDisplayMobileFl' => '노출상태(모바일)',//노출상태(모바일)
        'goodsSellFl' => '판매상태(PC)',//판매상태(PC)
        'goodsSellMobileFl' => '판매상태(모바일)',//판매상태(모바일)
        'soldOutFl' => '품절상태',//품절상태
        'option' => '옵션',//옵션
        'optionViewFl' => '옵션 노출상태',//옵션 노출상태
        'optionSellFl' => '옵션 품절상태',//옵션 품절상태
        'optionDeliveryFl' => '옵션 배송상태',//옵션 배송상태
        //현재 추가 개발진행 중이므로 수정하지 마세요! 주석 처리된 내용을 수정할 경우 기능이 정상 작동하지 않거나, 추후 기능 배포시 오류의 원인이 될 수 있습니다.
        //'optionStopFl' => '판매중지수량',//판매중지수량
        //'optionRequestFl' => '확인요청수량',//확인요청수량
        'totalStock' => '상품 재고',
        'stockCnt' => '재고',//재고
        'optionCostPrice' => '옵션 매입가',//옵션 매입가
        'optionPrice' => '옵션가',//옵션가
        'mileageFl' => '마일리지',//마일리지
        'goodsDiscountFl' => '상품할인',//상품할인
        'goodsBenefit' => '상품혜택',//상품혜택
        'deliverySno' => '배송정책',//배송정책
        'regDt' => '등록일',//등록일
        'modDt' => '수정일',//수정일
    ];


	public function __construct()
	{
		$this->gGlobal = Globals::get('gGlobal');
		// 매입처 플러스샵 미사용 또는 공급사일 경우
		if(gd_is_provider() === false && gd_is_plus_shop(PLUSSHOP_CODE_PURCHASE) === true) {
			array_splice($this->goodsScmMainList['goods_list']['defaultList'], 6, 0, 'purchaseNo' );
			array_splice($this->goodsScmMainList['goods_list']['list'], 6, 0, 'purchaseNo' );
			array_splice($this->goodsScmMainList['goods_list_delete']['defaultList'], 6, 0, 'purchaseNo' );
			array_splice($this->goodsScmMainList['goods_list_delete']['list'], 6, 0, 'purchaseNo' );
		}
		// 글로벌샵 사용 여부에 따른 해외상품명 추가
		if($this->gGlobal['isUse']) {
			$globalGoodsName = ['goodsNmUs', 'goodsNmCn', 'goodsNmJs'];
			$positionCnt = 5;
			foreach ($globalGoodsName as $key => $val ) {
				if($this->gGlobal['mallList'][$key+2]['useFl'] == 'y') {
					$positionCnt = $positionCnt+1;
					array_splice($this->goodsScmMainList['goods_list']['list'], $positionCnt, 0, $val); // - 본사 상품리스트 전체
					array_splice($this->goodsScmMainList['goods_list_delete']['list'], $positionCnt, 0, $val); // 본사 삭제상품리스트 전체
					array_splice($this->goodsScmSubList['goods_list']['list'], $positionCnt, 0, $val); // - 공급사 상품리스트 전체
					array_splice($this->goodsScmSubList['goods_list_delete']['list'], $positionCnt, 0, $val); // 공급사 삭제상품리스트 전체
				}
			}
		}
	}

	/**
	 * 선택된 상품리스트 그리드 항목 리스트 로드
	 *
	 * @param string $goodsGridMode
	 *
	 * @return array $returnData
	 */
	public function getSelectGoodsGridConfigList($goodsGridMode, $type=null)
	{
		$db = \App::load('DB');
		$arrBind = [];
		$resultList = [];
		$goodsGridlist = [];

		$fieldType = DBTableField::getFieldTypes('tableManagerGoodsGridConfig');

		$strSQL = 'SELECT ggData FROM ' . DB_MANAGER_GOODS_GRID_CONFIG . ' WHERE ggManagerSno = ? AND ggApplyMode = ?';
		$db->bind_param_push($arrBind, $fieldType['ggManagerSno'], Session::get('manager.sno'));
		$db->bind_param_push($arrBind, $fieldType['ggApplyMode'], $goodsGridMode);
		$getData = $db->query_fetch($strSQL, $arrBind, false);

		if(count($getData) > 0){
			$goodsGridlist = json_decode($getData['ggData'], true);
		} else {
			//저장결과가 없을시 디폴트 리스트 반환
			if (Manager::isProvider()) {
				//공급사일시
				$goodsGridlist = $this->goodsScmSubList[$goodsGridMode]['defaultList'];
			} else {
				//본사일시
				$goodsGridlist = $this->goodsScmMainList[$goodsGridMode]['defaultList'];
			}
		}

		// 노출항목 추가설정에 따른 분기처리 display-노출항목체크 / all - 그리드항목, 노출항목
		if($type == 'display') {
			$resultList = $goodsGridlist['listDisplay'];
		} else if($type == 'all') {
			$resultList = self::setListNameApply($goodsGridlist);
			$resultList['display'] = $goodsGridlist['listDisplay'];
		} else {
			$resultList = self::setListNameApply($goodsGridlist);
		}

		return $resultList;
	}

	/**
	 * 전체 상품리스트 그리드 항목 리스트 로드
	 *
	 * @param string $goodsGridMode
	 * @param string $gridSort
	 *
	 * @return array $returnData
	 */
	public function getAllGoodsGridConfigList($goodsGridMode, $gridSort)
	{
		$goodsGridlist = [];
		$resultList = [];

		//저장결과가 없을시 디폴트 리스트 반환
		if (Manager::isProvider()) {
			//공급사일시
			$goodsGridlist = $this->goodsScmSubList[$goodsGridMode]['list'];
		} else {
			//본사일시
			$goodsGridlist = $this->goodsScmMainList[$goodsGridMode]['list'];
		}

		$resultList = self::setListNameApply($goodsGridlist);

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
     * 선택된 상품리스트 그리드 항목 리스트 로드
     *
     * @param string $goodsGridMode
     *
     * @return array $returnData
     */
    public function getSelectGoodsOptionGridConfigList($goodsGridMode, $type=null)
    {
        $db = \App::load('DB');
        $arrBind = [];
        $resultList = [];
        $goodsGridlist = [];

        $fieldType = DBTableField::getFieldTypes('tableManagerGoodsGridConfig');

        $strSQL = 'SELECT ggData FROM ' . DB_MANAGER_GOODS_GRID_CONFIG . ' WHERE ggManagerSno = ? AND ggApplyMode = ?';
        $db->bind_param_push($arrBind, $fieldType['ggManagerSno'], Session::get('manager.sno'));
        $db->bind_param_push($arrBind, $fieldType['ggApplyMode'], $goodsGridMode);
        $getData = $db->query_fetch($strSQL, $arrBind, false);

        if(count($getData) > 0){
            $goodsGridlist = json_decode($getData['ggData'], true);
        } else {
            //저장결과가 없을시 디폴트 리스트 반환
            if (Manager::isProvider()) {
                //공급사일시
                $goodsGridlist = $this->goodsScmSubList[$goodsGridMode]['defaultList'];
            } else {
                //본사일시
                $goodsGridlist = $this->goodsScmMainList[$goodsGridMode]['defaultList'];
            }
        }

        // 노출항목 추가설정에 따른 분기처리 display-노출항목체크 / all - 그리드항목, 노출항목
        if($type == 'display') {
            $resultList = $goodsGridlist['listDisplay'];
        } else if($type == 'all') {
            $resultList = self::setListOptionNameApply($goodsGridlist);
            $resultList['display'] = $goodsGridlist['listDisplay'];
        } else {
            $resultList = self::setListOptionNameApply($goodsGridlist);
        }

        return $resultList;
    }

    /**
     * 선택된 상품 품절/노출/재고 관리 그리드 항목 리스트 로드
     *
     * @param string $goodsGridMode
     *
     * @return array $returnData
     */
    public function getSelectGoodsBatchStockGridConfigList($goodsGridMode, $type=null)
    {
        $db = \App::load('DB');
        $arrBind = [];
        $resultList = [];
        $goodsGridlist = [];

        $fieldType = DBTableField::getFieldTypes('tableManagerGoodsGridConfig');

        $strSQL = 'SELECT ggData FROM ' . DB_MANAGER_GOODS_GRID_CONFIG . ' WHERE ggManagerSno = ? AND ggApplyMode = ?';
        $db->bind_param_push($arrBind, $fieldType['ggManagerSno'], Session::get('manager.sno'));
        $db->bind_param_push($arrBind, $fieldType['ggApplyMode'], $goodsGridMode);
        $getData = $db->query_fetch($strSQL, $arrBind, false);

        if(count($getData) > 0){
            $goodsGridlist = json_decode($getData['ggData'], true);
        } else {
            //저장결과가 없을시 디폴트 리스트 반환
            if (Manager::isProvider()) {
                //공급사일시
                $goodsGridlist = $this->goodsScmSubList[$goodsGridMode]['defaultList'];
            } else {
                //본사일시
                $goodsGridlist = $this->goodsScmMainList[$goodsGridMode]['defaultList'];
            }
        }

        // 노출항목 추가설정에 따른 분기처리 display-노출항목체크 / all - 그리드항목, 노출항목
        if($type == 'display') {
            $resultList = $goodsGridlist['listDisplay'];
        } else if($type == 'all') {
            $resultList = self::setListBatchStockNameApply($goodsGridlist);
            $resultList['display'] = $goodsGridlist['listDisplay'];
        } else {
            $resultList = self::setListBatchStockNameApply($goodsGridlist);
        }

        return $resultList;
    }

    /**
     * 전체 상품 옵션 리스트 그리드 항목 리스트 로드
     *
     * @param string $goodsGridMode
     * @param string $gridSort
     *
     * @return array $returnData
     */
    public function getAllGoodsOptionGridConfigList($goodsOptionGridMode, $gridSort)
    {
        $goodsOptionGridlist = [];
        $resultList = [];

        //저장결과가 없을시 디폴트 리스트 반환
        if (Manager::isProvider()) {
            //공급사일시
            $goodsOptionGridlist = $this->goodsScmSubList[$goodsOptionGridMode]['list'];
        } else {
            //본사일시
            $goodsOptionGridlist = $this->goodsScmMainList[$goodsOptionGridMode]['list'];
        }

        $resultList = self::setListOptionNameApply($goodsOptionGridlist);

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
     * 전체 상품 옵션 리스트 그리드 항목 리스트 로드
     *
     * @param string $goodsGridMode
     * @param string $gridSort
     *
     * @return array $returnData
     */
    public function getAllGoodsBatchStockGridConfigList($goodsBatchStockGridMode, $gridSort)
    {
        $goodsBatchStockGridlist = [];
        $resultList = [];

        //저장결과가 없을시 디폴트 리스트 반환
        if (Manager::isProvider()) {
            //공급사일시
            $goodsBatchStockGridlist = $this->goodsScmSubList[$goodsBatchStockGridMode]['list'];
        } else {
            //본사일시
            $goodsBatchStockGridlist = $this->goodsScmMainList[$goodsBatchStockGridMode]['list'];
        }

        $resultList = self::setListBatchStockNameApply($goodsBatchStockGridlist);

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
	 * 상품리스트 그리드 항목 리스트 로드
	 *
	 * @param string $goodsGridMode
	 *
	 * @return string
	 */
	public function getGoodsGridConfigList($goodsGridMode)
	{
		$goodsGridConfigList = [];
		// 전체리스트
		$goodsGridConfigList['all'] = self::getAllgoodsGridConfigList($goodsGridMode, '');
		// 선택한리스트
		$goodsGridConfigList['select'] = self::getSelectgoodsGridConfigList($goodsGridMode);
		// 교집합 리스트
		$goodsGridConfigList['intersect'] = array_intersect(array_flip($goodsGridConfigList['all']), array_flip($goodsGridConfigList['select']));
		// 노출항목 리스트 (추가기능)
		$goodsGridConfigList['display'] = self::getSelectgoodsGridConfigList($goodsGridMode, 'display');

		return $goodsGridConfigList;
	}

    /**
     * 상품리스트 그리드 항목 리스트 로드
     *
     * @param string $goodsGridMode
     *
     * @return string
     */
    public function getGoodsOptionGridConfigList($goodsGridMode)
    {
        $goodsGridConfigList = [];
        // 전체리스트
        $goodsGridConfigList['all'] = self::getAllgoodsOptionGridConfigList($goodsGridMode, '');
        // 선택한리스트
        $goodsGridConfigList['select'] = self::getSelectgoodsOptionGridConfigList($goodsGridMode);
        // 교집합 리스트
        $goodsGridConfigList['intersect'] = array_intersect(array_flip($goodsGridConfigList['all']), array_flip($goodsGridConfigList['select']));

        return $goodsGridConfigList;
    }

    /**
     * 상품 품절/노출재고 관리 그리드 항목 리스트 로드
     *
     * @param string $goodsGridMode
     *
     * @return string
     */
    public function getGoodsBatchStockGridConfigList($goodsGridMode)
    {
        $goodsGridConfigList = [];
        // 전체리스트
        $goodsGridConfigList['all'] = self::getAllgoodsBatchStockGridConfigList($goodsGridMode, '');
        // 선택한리스트
        $goodsGridConfigList['select'] = self::getSelectgoodsBatchStockGridConfigList($goodsGridMode);
        // 교집합 리스트
        $goodsGridConfigList['intersect'] = array_intersect(array_flip($goodsGridConfigList['all']), array_flip($goodsGridConfigList['select']));

        return $goodsGridConfigList;
    }

	/**
	 * 정렬순서에 따른 전체 조회항목 로드
	 *
	 * @param string $goodsGridMode
	 * @paran string $gridSort
	 *
	 * @return string
	 */
	public function getGoodsGridConfigAllSortList($goodsGridMode, $gridSort)
	{
		$goodsGridConfigList = [];
		//전체리스트
		$goodsGridConfigList = self::getAllgoodsGridConfigList($goodsGridMode, $gridSort);

		return $goodsGridConfigList;
	}

    /**
     * 정렬순서에 따른 상품 품절/노출/재고 관리 조회항목 로드
     *
     * @param string $goodsGridMode
     * @paran string $gridSort
     *
     * @return string
     */
    public function getGoodsGridConfigBatchStockSortList($goodsGridMode, $gridSort)
    {
        $goodsGridConfigList = [];
        //전체리스트
        $goodsGridConfigList = self::getAllGoodsBatchStockGridConfigList($goodsGridMode, $gridSort);

        return $goodsGridConfigList;
    }

    /**
     * 정렬순서에 따른 상품 옵션 조회항목 로드
     *
     * @param string $goodsGridMode
     * @paran string $gridSort
     *
     * @return string
     */
    public function getGoodsGridConfigOptionSortList($goodsGridMode, $gridSort)
    {
        $goodsGridConfigList = [];
        //전체리스트
        $goodsGridConfigList = self::getAllGoodsOptionGridConfigList($goodsGridMode, $gridSort);

        return $goodsGridConfigList;
    }

	/**
	 * 상품리스트 그리드 항목 리턴 배열 정리
	 *
	 * @param array $goodsGridlist
	 *
	 * @return array $resultList;
	 */
	private function setListNameApply($goodsGridlist)
	{
		$resultList = [];

		//키, 배열 전환
		$goodsGridlistReverse = array_flip($goodsGridlist);
		//공통 값 체크
		$listTmp_stdKey = array_intersect_key($goodsGridlistReverse, $this->goodsKeyNameList);
		$listTmp_stdValue = array_intersect_key($this->goodsKeyNameList, $goodsGridlistReverse);

		$resultList = array_merge((array)$listTmp_stdKey, (array)$listTmp_stdValue);

		return $resultList;
	}

    /**
     * 상품리스트 그리드 항목 리턴 배열 정리
     *
     * @param array $goodsGridlist
     *
     * @return array $resultList;
     */
    private function setListOptionNameApply($goodsGridlist)
    {
        $resultList = [];

        //키, 배열 전환
        $goodsGridlistReverse = array_flip($goodsGridlist);
        //공통 값 체크
        $listTmp_stdKey = array_intersect_key($goodsGridlistReverse, $this->goodsKeyOptionNameList);
        $listTmp_stdValue = array_intersect_key($this->goodsKeyOptionNameList, $goodsGridlistReverse);

        $resultList = array_merge((array)$listTmp_stdKey, (array)$listTmp_stdValue);

        return $resultList;
    }

    /**
     * 상품리스트 그리드 항목 리턴 배열 정리
     *
     * @param array $goodsGridlist
     *
     * @return array $resultList;
     */
    private function setListBatchStockNameApply($goodsGridlist)
    {
        $resultList = [];

        //키, 배열 전환
        $goodsGridlistReverse = array_flip($goodsGridlist);
        //공통 값 체크
        $listTmp_stdKey = array_intersect_key($goodsGridlistReverse, $this->goodsKeyBatchStockNameList);
        $listTmp_stdValue = array_intersect_key($this->goodsKeyBatchStockNameList, $goodsGridlistReverse);

        $resultList = array_merge((array)$listTmp_stdKey, (array)$listTmp_stdValue);

        return $resultList;
    }

    /**
	 * 상품리스트 그리드 항목 리스트 저장
	 *
	 * @param array $postValue
	 *
	 * @return void
	 */
	public function setGoodsGridConfigList($postValue)
	{
		$db = \App::load('DB');

		$sessionManagerSno = Session::get('manager.sno');
		$arrBind = [];
		$getData = [];

		$postValue['goodsGridList']['listDisplay'] = $postValue['listDisplay'];

		$fieldType = DBTableField::getFieldTypes('tableManagerGoodsGridConfig');
		$strSQL = 'SELECT sno FROM ' . DB_MANAGER_GOODS_GRID_CONFIG . ' WHERE ggManagerSno = ? AND ggApplyMode = ?';
		$db->bind_param_push($arrBind, $fieldType['ggManagerSno'], $sessionManagerSno);
		$db->bind_param_push($arrBind, $fieldType['ggApplyMode'], $postValue['goodsGridMode']);
		$getData = $db->query_fetch($strSQL, $arrBind, false);

		$arrBind = [];
		if(count($getData) > 0){
			//update
			$updateField = [
				'ggData=?',
			];
			$db->bind_param_push($arrBind, 's', json_encode($postValue['goodsGridList'], JSON_FORCE_OBJECT));
			$db->bind_param_push($arrBind, 'i', $sessionManagerSno);
			$db->bind_param_push($arrBind, 's', $postValue['goodsGridMode']);
			$db->set_update_db(DB_MANAGER_GOODS_GRID_CONFIG, $updateField, 'ggManagerSno = ? AND ggApplyMode = ?', $arrBind, false);
		} else {
			//insert
			$arrData = [
				'ggManagerSno' => $sessionManagerSno,
				'ggApplyMode' => $postValue['goodsGridMode'],
				'ggData' => json_encode($postValue['goodsGridList'], JSON_FORCE_OBJECT),
			];
			$arrBind = $db->get_binding(DBTableField::tableManagerGoodsGridConfig(), $arrData, 'insert');
			$db->set_insert_db(DB_MANAGER_GOODS_GRID_CONFIG, $arrBind['param'], $arrBind['bind'], 'y');
		}
	}

    /**
     * 옵션리스트 그리드 항목 리스트 저장
     *
     * @param array $postValue
     *
     * @return void
     */
    public function setGoodsOptionGridConfigList($postValue)
    {
        $db = \App::load('DB');

        $sessionManagerSno = Session::get('manager.sno');
        $arrBind = [];
        $getData = [];

        $postValue['goodsOptionGridList']['listDisplay'] = $postValue['listDisplay'];

        $fieldType = DBTableField::getFieldTypes('tableManagerGoodsGridConfig');
        $strSQL = 'SELECT sno FROM ' . DB_MANAGER_GOODS_GRID_CONFIG . ' WHERE ggManagerSno = ? AND ggApplyMode = ?';
        $db->bind_param_push($arrBind, $fieldType['ggManagerSno'], $sessionManagerSno);
        $db->bind_param_push($arrBind, $fieldType['ggApplyMode'], $postValue['goodsOptionGridMode']);
        $getData = $db->query_fetch($strSQL, $arrBind, false);

        $arrBind = [];
        if(count($getData) > 0){
            //update
            $updateField = [
                'ggData=?',
            ];
            $db->bind_param_push($arrBind, 's', json_encode($postValue['goodsOptionGridList'], JSON_FORCE_OBJECT));
            $db->bind_param_push($arrBind, 'i', $sessionManagerSno);
            $db->bind_param_push($arrBind, 's', $postValue['goodsOptionGridMode']);
            $db->set_update_db(DB_MANAGER_GOODS_GRID_CONFIG, $updateField, 'ggManagerSno = ? AND ggApplyMode = ?', $arrBind, false);
        } else {
            //insert
            $arrData = [
                'ggManagerSno' => $sessionManagerSno,
                'ggApplyMode' => $postValue['goodsOptionGridMode'],
                'ggData' => json_encode($postValue['goodsOptionGridList'], JSON_FORCE_OBJECT),
            ];
            $arrBind = $db->get_binding(DBTableField::tableManagerGoodsGridConfig(), $arrData, 'insert');
            $db->set_insert_db(DB_MANAGER_GOODS_GRID_CONFIG, $arrBind['param'], $arrBind['bind'], 'y');
        }
    }
    
    /**
     * 상품 품절/노출/재고 관리 그리드 항목 리스트 저장
     *
     * @param array $postValue
     *
     * @return void
     */
    public function setGoodsBatchStockGridConfigList($postValue)
    {
        $db = \App::load('DB');

        $sessionManagerSno = Session::get('manager.sno');
        $arrBind = [];
        $getData = [];

        $postValue['goodsBatchStockGridList']['listDisplay'] = $postValue['listDisplay'];

        $fieldType = DBTableField::getFieldTypes('tableManagerGoodsGridConfig');
        $strSQL = 'SELECT sno FROM ' . DB_MANAGER_GOODS_GRID_CONFIG . ' WHERE ggManagerSno = ? AND ggApplyMode = ?';
        $db->bind_param_push($arrBind, $fieldType['ggManagerSno'], $sessionManagerSno);
        $db->bind_param_push($arrBind, $fieldType['ggApplyMode'], $postValue['goodsBatchStockGridMode']);
        $getData = $db->query_fetch($strSQL, $arrBind, false);

        $arrBind = [];
        if(count($getData) > 0){
            //update
            $updateField = [
                'ggData=?',
            ];
            $db->bind_param_push($arrBind, 's', json_encode($postValue['goodsBatchStockGridList'], JSON_FORCE_OBJECT));
            $db->bind_param_push($arrBind, 'i', $sessionManagerSno);
            $db->bind_param_push($arrBind, 's', $postValue['goodsBatchStockGridMode']);
            $db->set_update_db(DB_MANAGER_GOODS_GRID_CONFIG, $updateField, 'ggManagerSno = ? AND ggApplyMode = ?', $arrBind, false);
        } else {
            //insert
            $arrData = [
                'ggManagerSno' => $sessionManagerSno,
                'ggApplyMode' => $postValue['goodsBatchStockGridMode'],
                'ggData' => json_encode($postValue['goodsBatchStockGridList'], JSON_FORCE_OBJECT),
            ];
            $arrBind = $db->get_binding(DBTableField::tableManagerGoodsGridConfig(), $arrData, 'insert');
            $db->set_insert_db(DB_MANAGER_GOODS_GRID_CONFIG, $arrBind['param'], $arrBind['bind'], 'y');
        }
    }

	/**
	 * 페이지명을 기준으로 모드 반환
	 *
	 * @param string $viewType
	 *
	 * @return string $goodsGridMode
	 */
	public function getGoodsAdminGridMode($viewType)
	{
		$goodsGridMode = '';
		switch(\Request::getFileUri()){
			//상품리스트
			case 'goods_list.php' :
				$goodsGridMode = 'goods_list';
				break;

			//입금대기 리스트
			case 'goods_list_delete.php' :
				$goodsGridMode = 'goods_list_delete';
				break;

            //상품리스트
            case 'goods_register_option.php' :
                $goodsGridMode = 'goods_option_list';
                break;
            //상품리스트
            case 'goods_batch_stock.php' :
                $goodsGridMode = 'goods_batch_stock_list';
                break;
		}

		return $goodsGridMode;
	}

	/**
	 * 검색설정 저장된 항목을 필드로 변경
	 *
	 * @param string $selectData
	 *
	 * @return string $setData;
	 */
	public function getGoodsAdminAddField($selectData)
	{
		$setData = [];
		if($selectData) {
			$addData = array_flip((array)$this->goodsFieldAddList);
			$selectData = array_flip((array)$selectData);
			foreach ( $selectData as $key => $value) {
				if(in_array($value, $addData)) {
					$setData[$value] = $this->goodsFieldAddList[$value];
				}
			}
		}

		return $setData;
	}

}
