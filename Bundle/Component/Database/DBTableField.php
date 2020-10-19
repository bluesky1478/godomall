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

namespace Bundle\Component\Database;

use Session;

/**
 * DB Table 기본 Field 클래스 - DB 테이블의 기본 필드를 설정한 클래스 이며, prepare query 생성시 필요한 기본 필드 정보임
 * @package Component\Database
 * @static  tableConfig
 */
class DBTableField
{

    /**
     * 생성자
     */
    public function __construct()
    {
    }

    /**
     * [코드] config 필드 기본값
     *
     * @author artherot
     * @return array config 테이블 필드 정보
     */
    public static function tableConfig()
    {
        // @formatter:off
        $arrField = [
            ['val' => 'groupCode', 'typ' => 's', 'def' => null,], // 그룹코드
            ['val' => 'code', 'typ' => 's', 'def' => null,], // 코드
            ['val' => 'data', 'typ' => 's', 'def' => null,], // 내용
        ];
        // @formatter:on

        return $arrField;
    }

    /**
     * [코드] configGlobal 필드 기본값
     *
     * @author yjwee
     * @return array configGlobal 테이블 필드 정보
     */
    public static function tableConfigGlobal()
    {
        // @formatter:off
        $arrField = [
            ['val' => 'groupCode', 'typ' => 's', 'def' => null,], // 그룹코드
            ['val' => 'code', 'typ' => 's', 'def' => null,], // 코드
            ['val' => 'data', 'typ' => 's', 'def' => null,],
            ['val' => 'mallSno', 'typ' => 'i', 'def' => 1,],
            ['val' => 'shareFl', 'typ' => 's', 'def' => 'n',]
        ]; // 내용
        // @formatter:on

        return $arrField;
    }

    /**
     * [카테고리] category_goods 필드 기본값
     *
     * @author artherot
     * @return array category_goods 테이블 필드 정보
     */
    public static function tableCategoryGoods()
    {
        // @formatter:off
        $arrField = [
            ['val' => 'cateNm', 'typ' => 's', 'def' => null],// 카테고리 이름
            ['val' => 'cateCd', 'typ' => 's', 'def' => null],// 카테고리 코드
            ['val' => 'divisionFl', 'typ' => 's', 'def' => 'n'],// 구분자 사용여부
            ['val' => 'mallDisplay', 'typ' => 's', 'def' => '1'],//  노출상점 저장
            ['val' => 'mallDisplaySubFl', 'typ' => 's', 'def' => 'n'],// 글로벌 하위 노출 상점 여부
            ['val' => 'cateDisplayFl', 'typ' => 's', 'def' => 'y'],// 카테고리 감춤 여부
            ['val' => 'cateDisplayMobileFl', 'typ' => 's', 'def' => 'y',],// 카테고리 감춤 여부
            ['val' => 'cateImg', 'typ' => 's', 'def' => null,], //카테고리 이미지
            ['val' => 'cateImgMobile', 'typ' => 's', 'def' => null,],// 모바일 이미지
            ['val' => 'cateImgMobileFl', 'typ' => 's', 'def' => 'n',], //모바일 이미지 사용여부
            ['val' => 'cateOverImg', 'typ' => 's', 'def' => null,], // 카테고리 오버 이미지
            ['val' => 'cateOnlyAdultFl', 'typ' => 's', 'def' => 'n',], // 카테고리 성인인증 사용여부
            ['val' => 'cateOnlyAdultDisplayFl', 'typ' => 's', 'def' => 'y',], // 성인인증 노출 여부
            ['val' => 'cateOnlyAdultSubFl', 'typ' => 's', 'def' => 'n',], // 성인인증 하위 접근권한
            ['val' => 'catePermission', 'typ' => 'i', 'def' => '0',], // 접근 권한
            ['val' => 'catePermissionGroup', 'typ' => 's', 'def' => null,], // 접근권한 그룹
            ['val' => 'catePermissionDisplayFl', 'typ' => 's', 'def' => 'y',], // 접근권한 노출 여부
            ['val' => 'catePermissionSubFl', 'typ' => 's', 'def' => 'n',], // 하위 접근권힌
            ['val' => 'cateSort', 'typ' => 'i', 'def' => '0',], // 순서
            ['val' => 'pcThemeCd', 'typ' => 's', 'def' => null,], // pc적용테마
            ['val' => 'mobileThemeCd', 'typ' => 's', 'def' => null,], // 모바일 적용 테마
            ['val' => 'sortType', 'typ' => 's', 'def' => null,], // 정렬타입
            ['val' => 'sortAutoFl', 'typ' => 's', 'def' => 'y',], // 자동정렬여부
            ['val' => 'recomSubFl', 'typ' => 's', 'def' => 'n',], // 추천상품 하위 적용
            ['val' => 'recomDisplayFl', 'typ' => 's', 'def' => 'y',], // 추천상품 노출 여부
            ['val' => 'recomDisplayMobileFl', 'typ' => 's', 'def' => 'y',], // 추천상품 모바일 노출ㅇ ㅕ부
            ['val' => 'recomSortType', 'typ' => 's', 'def' => null,], // 추천상품 정렬
            ['val' => 'recomSortAutoFl', 'typ' => 's', 'def' => 'y',], // 추천상품 자동정렬
            ['val' => 'recomPcThemeCd', 'typ' => 's', 'def' => null,], // 추천상품 pc 테마
            ['val' => 'recomMobileThemeCd', 'typ' => 's', 'def' => null,], // 추천상품 모바일 테마
            ['val' => 'recomGoodsNo', 'typ' => 's', 'def' => null,], // 추천 상품번호
            ['val' => 'cateHtml1', 'typ' => 's', 'def' => null,], // 상단 HTML 1
            ['val' => 'cateHtml2', 'typ' => 's', 'def' => null,], // 상단 HTML 2
            ['val' => 'cateHtml3', 'typ' => 's', 'def' => null,], //상단 HTML3
            ['val' => 'cateHtml1Mobile','typ' => 's','def' => null],// 상단 모바일 HTML 1
            ['val' => 'cateHtml2Mobile','typ' => 's','def' => null],// 상단 모바일 HTML 1
            ['val' => 'cateHtml3Mobile','typ' => 's','def' => null],// 상단 모바일 HTML 1
            ['val' => 'seoTagFl', 'typ' => 's', 'def' => 'n',], // 상품 개별 SEO 태그 설정
            ['val' => 'seoTagSno', 'typ' => 'i', 'def' => '0', ], // 상품 개별 SEO 태그 고유번호
        ];
        // @formatter:on

        return $arrField;
    }

    /**
     * [카테고리_글로벌] categoryGoodsGlobal 필드 기본값
     *
     * @author atomyang
     * @return array addGoodsGroupGoods 테이블 필드 정보
     */
    public static function tableCategoryGoodsGlobal()
    {
        // @formatter:off
        $arrField = [
            ['val' => 'cateCd', 'typ' => 's', 'def' => null], // 카테고리sno
            ['val' => 'mallSno', 'typ' => 'i', 'def' => 1], // 몰sno
            ['val' => 'cateNm', 'typ' => 's', 'def' => null], // 카테고리명
        ];
        // @formatter:on

        return $arrField;
    }

    /**
     * [카테고리] category_brand 필드 기본값
     *
     * @author artherot
     * @return array category_brand 테이블 필드 정보
     */
    public static function tableCategoryBrand()
    {
        // @formatter:off
        $arrField = [
            ['val' => 'cateNm', 'typ' => 's', 'def' => null],// 카테고리 이름
            ['val' => 'cateCd', 'typ' => 's', 'def' => null],// 카테고리 코드
            ['val' => 'divisionFl', 'typ' => 's', 'def' => 'n'],// 구분자 사용여부
            ['val' => 'mallDisplay', 'typ' => 's', 'def' => '1'],//  노출상점 저장
            ['val' => 'mallDisplaySubFl', 'typ' => 's', 'def' => 'n'],// 글로벌 하위 노출 상점 여부
            ['val' => 'cateDisplayFl', 'typ' => 's', 'def' => 'y'],// 카테고리 감춤 여부
            ['val' => 'cateDisplayMobileFl', 'typ' => 's', 'def' => 'y',],// 카테고리 감춤 여부
            ['val' => 'cateImg', 'typ' => 's', 'def' => null,], //카테고리 이미지
            ['val' => 'cateImgMobile', 'typ' => 's', 'def' => null,],// 모바일 이미지
            ['val' => 'cateImgMobileFl', 'typ' => 's', 'def' => 'n',], //모바일 이미지 사용여부
            ['val' => 'cateOverImg', 'typ' => 's', 'def' => null,], // 카테고리 오버 이미지
            ['val' => 'cateOnlyAdultFl', 'typ' => 's', 'def' => 'n',], // 카테고리 성인인증 사용여부
            ['val' => 'cateOnlyAdultDisplayFl', 'typ' => 's', 'def' => 'y',], // 성인인증 노출 여부
            ['val' => 'cateOnlyAdultSubFl', 'typ' => 's', 'def' => 'n',], // 성인인증 하위 접근권한
            ['val' => 'catePermission', 'typ' => 'i', 'def' => '0',], // 접근 권한
            ['val' => 'catePermissionGroup', 'typ' => 's', 'def' => null,], // 접근권한 그룹
            ['val' => 'catePermissionDisplayFl', 'typ' => 's', 'def' => 'y',], // 접근권한 노출 여부
            ['val' => 'catePermissionSubFl', 'typ' => 's', 'def' => 'n',], // 하위 접근권힌
            ['val' => 'cateSort', 'typ' => 'i', 'def' => '0',], // 순서
            ['val' => 'pcThemeCd', 'typ' => 's', 'def' => null,], // pc적용테마
            ['val' => 'mobileThemeCd', 'typ' => 's', 'def' => null,], // 모바일 적용 테마
            ['val' => 'sortType', 'typ' => 's', 'def' => null,], // 정렬타입
            ['val' => 'sortAutoFl', 'typ' => 's', 'def' => 'y',], // 자동정렬여부
            ['val' => 'recomSubFl', 'typ' => 's', 'def' => 'n',], // 추천상품 하위 적용
            ['val' => 'recomDisplayFl', 'typ' => 's', 'def' => 'y',], // 추천상품 노출 여부
            ['val' => 'recomDisplayMobileFl', 'typ' => 's', 'def' => 'y',], // 추천상품 모바일 노출ㅇ ㅕ부
            ['val' => 'recomSortType', 'typ' => 's', 'def' => null,], // 추천상품 정렬
            ['val' => 'recomSortAutoFl', 'typ' => 's', 'def' => 'y',], // 추천상품 자동정렬
            ['val' => 'recomPcThemeCd', 'typ' => 's', 'def' => null,], // 추천상품 pc 테마
            ['val' => 'recomMobileThemeCd', 'typ' => 's', 'def' => null,], // 추천상품 모바일 테마
            ['val' => 'recomGoodsNo', 'typ' => 's', 'def' => null,], // 추천 상품번호
            ['val' => 'cateHtml1', 'typ' => 's', 'def' => null,], // 상단 HTML 1
            ['val' => 'cateHtml2', 'typ' => 's', 'def' => null,], // 상단 HTML 2
            ['val' => 'cateHtml3', 'typ' => 's', 'def' => null,], //상단 HTML3
            ['val' => 'cateHtml1Mobile','typ' => 's','def' => null],// 상단 모바일 HTML 1
            ['val' => 'cateHtml2Mobile','typ' => 's','def' => null],// 상단 모바일 HTML 1
            ['val' => 'cateHtml3Mobile','typ' => 's','def' => null],// 상단 모바일 HTML 1
            ['val' => 'seoTagFl', 'typ' => 's', 'def' => 'n',], // 상품 개별 SEO 태그 설정
            ['val' => 'seoTagSno', 'typ' => 'i', 'def' => '0', ], // 상품 개별 SEO 태그 고유번호
        ];
        // @formatter:on

        return $arrField;
    }

    /**
     * [카테고리_글로벌] categoryGoodsGlobal 필드 기본값
     *
     * @author atomyang
     * @return array addGoodsGroupGoods 테이블 필드 정보
     */
    public static function tableCategoryBrandGlobal()
    {
        // @formatter:off
        $arrField = [
            ['val' => 'cateCd', 'typ' => 's', 'def' => null], // 카테고리sno
            ['val' => 'mallSno', 'typ' => 'i', 'def' => 1], // 몰sno
            ['val' => 'cateNm', 'typ' => 's', 'def' => null], // 카테고리명
            ['val' => 'cateNmGlobalFl', 'typ' => 's', 'def' => 'n'], // 기준몰 공통 사용 여부
        ];
        // @formatter:on

        return $arrField;
    }

    /**
     * [카테고리] category_theme 필드 기본값
     *
     * @author     artherot
     * @return array category_theme 테이블 필드 정보
     * @deprecated 2017-05-22 atomyang 사용하지 않는 테이블입니다. 추후 삭제 예정
     */
    public static function tableCategoryTheme()
    {
        $arrField = [
            [
                'val' => 'cateType',
                'typ' => 's',
                'def' => 'goods',
            ],
            // 카테고리 타입
            [
                'val' => 'themeId',
                'typ' => 's',
                'def' => '',
            ],
            // 카테고리 테마아이디
            [
                'val' => 'themeNm',
                'typ' => 's',
                'def' => '',
            ],
            // 카테고리 테마명
            [
                'val' => 'bdKind',
                'typ' => 's',
                'def' => '',
            ],
            // 리스트형태
            [
                'val' => 'recomType',
                'typ' => 's',
                'def' => '',
            ],
            // 추천상품형태
            [
                'val' => 'subcateType',
                'typ' => 's',
                'def' => '',
            ],
            // 분류하위형태
            [
                'val' => 'imageCd',
                'typ' => 's',
                'def' => 'list',
            ],
            // 상품 이미지 코드
            [
                'val' => 'sortFl',
                'typ' => 's',
                'def' => 'sort desc',
            ],
            // 기본 정렬 방식
            [
                'val' => 'lineCnt',
                'typ' => 'i',
                'def' => 4,
            ],
            // 가로 갯수
            [
                'val' => 'rowCnt',
                'typ' => 'i',
                'def' => 5,
            ],
            // 세로 갯수
            [
                'val' => 'imageFl',
                'typ' => 's',
                'def' => 'y',
            ],
            // 이미지출력
            [
                'val' => 'goodsNmFl',
                'typ' => 's',
                'def' => 'y',
            ],
            // 상품명출력
            [
                'val' => 'priceFl',
                'typ' => 's',
                'def' => 'y',
            ],
            // 가격출력
            [
                'val' => 'soldOutFl',
                'typ' => 's',
                'def' => 'n',
            ],
            // 품절상품출력
            [
                'val' => 'soldOutIconFl',
                'typ' => 's',
                'def' => 'n',
            ],
            // 품절아이콘출력
            [
                'val' => 'iconFl',
                'typ' => 's',
                'def' => 'n',
            ],
            // 아이콘출력
            [
                'val' => 'fixedPriceFl',
                'typ' => 's',
                'def' => 'n',
            ],
            // 정가출력
            [
                'val' => 'couponPriceFl',
                'typ' => 's',
                'def' => 'n',
            ],
            // 쿠폰가출력
            [
                'val' => 'mileageFl',
                'typ' => 's',
                'def' => 'n',
            ],
            // 마일리지출력
            [
                'val' => 'shortDescFl',
                'typ' => 's',
                'def' => 'n',
            ],
            // 짧은설명출력
            [
                'val' => 'brandFl',
                'typ' => 's',
                'def' => 'n',
            ],
            // 브랜드출력
            [
                'val' => 'makerFl',
                'typ' => 's',
                'def' => 'n',
            ],
            // 제조사출력
            [
                'val' => 'optionFl',
                'typ' => 's',
                'def' => 'n',
            ],
            // 옵션출력
            [
                'val' => 'recomFl',
                'typ' => 's',
                'def' => 'y',
            ],
            // 추천상품 사용여부
            [
                'val' => 'recomImageCd',
                'typ' => 's',
                'def' => 'list',
            ],
            // 추천상품 이미지 코드
            [
                'val' => 'recomPage',
                'typ' => 'i',
                'def' => 1,
            ],
            // 추천상품 출력할 페이지 번호 (~ 페이지 까지)
            [
                'val' => 'recomLineCnt',
                'typ' => 'i',
                'def' => 4,
            ],
            // 추천상품 가로 갯수
            [
                'val' => 'recomRowCnt',
                'typ' => 'i',
                'def' => 1,
            ],
            // 추천상품 세로 갯수
            [
                'val' => 'recomImageFl',
                'typ' => 's',
                'def' => 'y',
            ],
            // 추천상품 이미지출력
            [
                'val' => 'recomGoodsNmFl',
                'typ' => 's',
                'def' => 'y',
            ],
            // 추천상품 상품명출력
            [
                'val' => 'recomPriceFl',
                'typ' => 's',
                'def' => 'y',
            ],
            // 추천상품 가격출력
            [
                'val' => 'recomSoldOutFl',
                'typ' => 's',
                'def' => 'n',
            ],
            // 추천상품 품절상품출력
            [
                'val' => 'recomSoldOutIconFl',
                'typ' => 's',
                'def' => 'n',
            ],
            // 추천상품 품절아이콘출력
            [
                'val' => 'recomIconFl',
                'typ' => 's',
                'def' => 'n',
            ],
            // 추천상품 아이콘출력
            [
                'val' => 'recomFixedPriceFl',
                'typ' => 's',
                'def' => 'n',
            ],
            // 추천상품 정가출력
            [
                'val' => 'recomCouponPriceFl',
                'typ' => 's',
                'def' => 'n',
            ],
            // 추천상품 쿠폰가출력
            [
                'val' => 'recomMileageFl',
                'typ' => 's',
                'def' => 'n',
            ],
            // 추천상품 마일리지출력
            [
                'val' => 'recomShortDescFl',
                'typ' => 's',
                'def' => 'n',
            ],
            // 추천상품 짧은설명출력
            [
                'val' => 'recomBrandFl',
                'typ' => 's',
                'def' => 'n',
            ],
            // 추천상품 브랜드출력
            [
                'val' => 'recomMakerFl',
                'typ' => 's',
                'def' => 'n',
            ],
            // 추천상품 제조사출력
            [
                'val' => 'recomOptionFl',
                'typ' => 's',
                'def' => 'n',
            ],
            // 추천상품 옵션출력
            [
                'val' => 'subcateCntFl',
                'typ' => 's',
                'def' => 'n',
            ],
            // 분류하위메뉴 상품수 출력
            [
                'val' => 'mobileListType',
                'typ' => 's',
                'def' => 'list',
            ],
            // 모바일 리스트 영역 형태
            [
                'val' => 'mobileImageCd',
                'typ' => 's',
                'def' => 'list',
            ],
            // 모바일 이미지 코드
            [
                'val' => 'mobileImageSize',
                'typ' => 'i',
                'def' => '0',
            ],
            // 모바일 이미지 코드
            [
                'val' => 'mobileSortFl',
                'typ' => 's',
                'def' => 'sort desc',
            ],
            // 모바일 기본 출력 방식
            [
                'val' => 'mobileLineCnt',
                'typ' => 'i',
                'def' => 1,
            ],
            // 모바일 가로 갯수
            [
                'val' => 'mobileRowCnt',
                'typ' => 'i',
                'def' => 10,
            ],
            // 모바일 세로 갯수
            [
                'val' => 'mobileSoldOutFl',
                'typ' => 's',
                'def' => 'n',
            ],
            // 모바일 품절상품 출력
            [
                'val' => 'mobileSoldOutIconFl',
                'typ' => 's',
                'def' => 'n',
            ],
            // 모바일 품절 아이콘 출력
            [
                'val' => 'mobileIconFl',
                'typ' => 's',
                'def' => 'n',
            ],
            // 모바일 아이콘 출력
            [
                'val' => 'mobileImageFl',
                'typ' => 's',
                'def' => 'y',
            ],
            // 모바일 이미지 출력
            [
                'val' => 'mobileGoodsNmFl',
                'typ' => 's',
                'def' => 'y',
            ],
            // 모바일 상품명 출력
            [
                'val' => 'mobileShortDescFl',
                'typ' => 's',
                'def' => 'n',
            ],
            // 모바일 짧은설명 출력
            [
                'val' => 'mobileBrandFl',
                'typ' => 's',
                'def' => 'n',
            ],
            // 모바일 브랜드 출력
            [
                'val' => 'mobileMakerFl',
                'typ' => 's',
                'def' => 'n',
            ],
            // 모바일 제조사 출력
            [
                'val' => 'mobilePriceFl',
                'typ' => 's',
                'def' => 'y',
            ],
            // 모바일 가격 출력
            [
                'val' => 'mobileFixedPriceFl',
                'typ' => 's',
                'def' => 'n',
            ],
            // 모바일 정가 출력
            [
                'val' => 'mobileMileageFl',
                'typ' => 's',
                'def' => 'n',
            ],
        ]; // 모바일 마일리지 출력

        return $arrField;
    }

    /**
     * [테마] display_theme 필드 기본값
     *
     * @author artherot
     * @return array display_theme 테이블 필드 정보
     */
    public static function tableDisplayTheme()
    {
        // @formatter:off
        $arrField = [
            ['val' => 'themeNm', 'typ' => 's', 'def' => null,], // 카테고리 테마명
            ['val' => 'themeDescription', 'typ' => 's', 'def' => null,], // 테마설명 배열
            ['val' => 'goodsNo', 'typ' => 's', 'def' => null,], // 상품 번호 배열
            ['val' => 'fixGoodsNo', 'typ' => 's', 'def' => null,], // 고정상품값
            ['val' => 'mobileFl', 'typ' => 's', 'def' => 'n',], //모바일여분
            ['val' => 'displayFl', 'typ' => 's', 'def' => 'y',], //노출여부
            ['val' => 'sort', 'typ' => 's', 'def' => null,], //진열방법선택
            ['val' => 'themeCd', 'typ' => 's', 'def' => null,],
            ['val' => 'imageNm', 'typ' => 's', 'def' => null],   //이미지
            ['val' => 'sortAutoFl', 'typ' => 's', 'def' => 'n'],   //자동정렬여부
            ['val' => 'moreTopFl', 'typ' => 's', 'def' => 'y'],   //상단 버튼 노출
            ['val' => 'moreBottomFl', 'typ' => 's', 'def' => 'y'],   //하단 버튼 노출
            ['val' => 'kind', 'typ' => 's', 'def' => 'main'],   //유형 main or event
            ['val' => 'pcFl', 'typ' => 's', 'def' => ''],   //pc여부
            ['val' => 'displayStartDate', 'typ' => 's', 'def' => ''],   //이벤트시작일
            ['val' => 'displayEndDate', 'typ' => 's', 'def' => ''], //이벤트마감일
            ['val' => 'pcContents', 'typ' => 's', 'def' => ''],
            ['val' => 'mobileContents', 'typ' => 's', 'def' => ''],
            ['val' => 'mobileThemeCd', 'typ' => 's', 'def' => null],  //모바일 테마코드
            ['val' => 'exceptGoodsNo', 'typ' => 's', 'def' => ''],  //예외 상품
            ['val' => 'exceptCateCd', 'typ' => 's', 'def' => ''],  //예외 카테고리
            ['val' => 'exceptBrandCd', 'typ' => 's', 'def' => ''],  //예외 브랜드
            ['val' => 'exceptScmNo', 'typ' => 's', 'def' => ''],  //예외 공급사
            ['val' => 'managerNo', 'typ' => 'i', 'def' => ''],  //등록자
            ['val' => 'descriptionSameFl', 'typ' => 's', 'def' => 'n'],  //PC/모바일 상세설명 동일사용
            ['val' => 'displayCategory', 'typ' => 's', 'def' => 'n'],  // 진열유형 (기획전등록시 사용)
            ['val' => 'seoTagFl', 'typ' => 's', 'def' => 'n',], // 상품 개별 SEO 태그 설정
            ['val' => 'seoTagSno', 'typ' => 'i', 'def' => '0', ], // 상품 개별 SEO 태그 고유번호
        ]; //테마코드

        // @formatter:on
        return $arrField;
    }

    /*
    * [테마] display_theme_config 필드 기본값
    *
    * @author artherot
    * @return array display_themeConfig 테이블 필드 정보
    */
    public static function tableDisplayThemeConfig()
    {
        // @formatter:off
        $arrField = [
            ['val' => 'themeCd', 'typ' => 's', 'def' => null,], // 테마코드
            ['val' => 'themeNm', 'typ' => 's', 'def' => null,], //테마명
            ['val' => 'mobileFl', 'typ' => 's', 'def' => 'n',], //쇼핑몰적용여부
            ['val' => 'themeCate', 'typ' => 's', 'def' => 'B',], //테마분류
            ['val' => 'imageCd', 'typ' => 's', 'def' => 'list',], //상품 이미지 코드
            ['val' => 'lineCnt', 'typ' => 'i', 'def' => '4',], //가로 갯수
            ['val' => 'rowCnt', 'typ' => 'i', 'def' => '5',], //세로 갯수
            ['val' => 'soldOutFl', 'typ' => 's', 'def' => 'y',], //품절상품출력
            ['val' => 'soldOutDisplayFl', 'typ' => 's', 'def' => 'y',], //품절상품출력
            ['val' => 'soldOutIconFl', 'typ' => 's', 'def' => 'y',], //품절아이콘출력
            ['val' => 'iconFl', 'typ' => 's', 'def' => 'y',], //아이콘출력
            ['val' => 'displayField', 'typ' => 's', 'def' => 'img,goodsNm',], //노출항목설정
            ['val' => 'goodsDiscount', 'typ' => 's', 'def' => null,], //할인적용가설정
            ['val' => 'priceStrike', 'typ' => 's', 'def' => null,], //취소선추가설정
            ['val' => 'displayAddField', 'typ' => 's', 'def' => null,], //노출항목추가설정
            ['val' => 'displayType', 'typ' => 's', 'def' => '01',], //디스플레이유형
            ['val' => 'deleteFl', 'typ' => 's', 'def' => null,], //삭제가능여부
            ['val' => 'useCnt', 'typ' => 'i', 'def' => null,], //적용개수
            ['val' => 'deleteFl', 'typ' => 's', 'def' => 'y',], //적용개수
            ['val' => 'detailSet', 'typ' => 's', 'def' => null,],//상세설정
        ];
        // @formatter:on

        return $arrField;
    }


    /**
     * [테마] display_theme_mobile 필드 기본값
     *
     * @author artherot
     * @return array display_theme 테이블 필드 정보
     */
    public static function tableDisplayThemeMobile()
    {
        // @formatter:off
        $arrField = [
            ['val' => 'themeNm', 'typ' => 's', 'def' => null], // 카테고리 테마명
            ['val' => 'themeUseFl', 'typ' => 's', 'def' => 'n'], // 테마 사용 여부
            ['val' => 'goodsNo', 'typ' => 's', 'def' => null], // 상품 번호 배열
            ['val' => 'themeTopImg', 'typ' => 's', 'def' => null], // 테마 메인 상단 이미지
            ['val' => 'imageSize', 'typ' => 'i', 'def' => 70], // 이미지 사이즈
            ['val' => 'listType', 'typ' => 's', 'def' => 'gallery'], // 리스트형태
            ['val' => 'imageCd', 'typ' => 's', 'def' => 'main'], // 상품 이미지 코드
            ['val' => 'imageFl', 'typ' => 's', 'def' => 'y'], // 이미지출력
            ['val' => 'goodsNmFl', 'typ' => 's', 'def' => 'y'], // 상품명출력
            ['val' => 'priceFl', 'typ' => 's', 'def' => 'y'], // 가격출력
            ['val' => 'mileageFl', 'typ' => 's', 'def' => 'n'], // 마일리지출력
            ['val' => 'soldOutFl', 'typ' => 's', 'def' => 'n'], // 품절상품출력
            ['val' => 'soldOutIconFl', 'typ' => 's', 'def' => 'n'], // 품절아이콘출력
            ['val' => 'iconFl', 'typ' => 's', 'def' => 'n'], // 아이콘출력
            ['val' => 'shortDescFl', 'typ' => 's', 'def' => 'n'], // 짧은설명출력
            ['val' => 'brandFl', 'typ' => 's', 'def' => 'n'], // 브랜드출력
            ['val' => 'makerFl', 'typ' => 's', 'def' => 'n'], // 제조사출력
        ];
        // @formatter:on

        return $arrField;
    }

    /**
     * [상품] goods 필드 기본값
     *
     * @author artherot
     * @return array goods 테이블 필드 정보
     */
    public static function tableGoods($conf = null)
    {
        if (empty($conf['taxFreeFl'])) {
            $conf['taxFreeFl'] = 't';
        }
        if (empty($conf['taxPercent'])) {
            $conf['taxPercent'] = 10;
        }
        // @formatter:off
        $arrField = [
            ['val' => 'goodsNo', 'typ' => 's', 'def' => null, 'name' => '상품 번호'], // 상품 번호
            ['val' => 'goodsNmFl', 'typ' => 's', 'def' => 'd', 'name' => '상품명 확장 여부'], // 상품명 확장 여부
            ['val' => 'goodsNm', 'typ' => 's', 'def' => null, 'name' => '상품명'], // 상품명
            ['val' => 'goodsNmMain', 'typ' => 's', 'def' => null, 'name' => '상품명 - 메인용'], // 상품명 - 메인용
            ['val' => 'goodsNmList', 'typ' => 's', 'def' => null, 'name' => '상품명 - 리스트용'], // 상품명 - 리스트용
            ['val' => 'goodsNmDetail', 'typ' => 's', 'def' => null, 'name' => '상품명 - 상세설명용'], // 상품명 - 상세설명용
            ['val' => 'goodsNmPartner', 'typ' => 's', 'def' => null, 'name' => '상품명 - 제휴'], // 상품명 - 제휴
            ['val' => 'goodsDisplayFl', 'typ' => 's', 'def' => 'y', 'name' => '상품 출력 여부 - PC쇼핑몰'], // 상품 출력 여부 - PC
            ['val' => 'goodsDisplayMobileFl', 'typ' => 's', 'def' => 'y', 'name' => '상품 출력 여부 - 모바일샵'], // 상품 출력 여부 - 모바일샵
            ['val' => 'goodsSellFl', 'typ' => 's', 'def' => 'y', 'name' => '상품 판매 여부 - PC쇼핑몰'], // 상품 판매 여부 - PC
            ['val' => 'goodsSellMobileFl', 'typ' => 's', 'def' => 'y', 'name' => '상품 판매 여부 - 모바일샵'], // 상품 판매 여부 - 모바일샵
            ['val' => 'scmNo', 'typ' => 'i', 'def' => (string)Session::get('manager.scmNo'), 'name' => '공급사 고유 번호'], // 공급사 고유 번호
            ['val' => 'purchaseNo', 'typ' => 'i', 'def' => null, 'name' => '매입처'], // 매입처
            ['val' => 'purchaseGoodsNm', 'typ' => 's', 'def' => null, 'name' => '매입처 상품명'], // 매입처 상품명
            ['val' => 'commission', 'typ' => 'd', 'def' => '0.00', 'name' => ' 공급사 수수료율'], // 공급사 수수료율
            ['val' => 'goodsCd', 'typ' => 's', 'def' => null, 'name' => ' 상품 코드'], // 상품 코드
            ['val' => 'cateCd', 'typ' => 's', 'def' => null, 'name' => '대표 카테고리 코드'], // 대표 카테고리 코드
            ['val' => 'goodsSearchWord', 'typ' => 's', 'def' => null, 'name' => '상품 검색어'], // 상품 검색어
            ['val' => 'goodsOpenDt', 'typ' => 's', 'def' => '0000-00-00 00:00:00', 'name' => '상품 노출 시간'], // 상품 노출 시간
            ['val' => 'goodsState', 'typ' => 's', 'def' => 'n', 'name' => '상품 상태'], // 상품 상태 ('n'=>'새상품','u'=>'중고상품','r'=>'반품/재고상품')
            ['val' => 'goodsColor', 'typ' => 's', 'def' => null, 'name' => '상품 대표 색상'], // 상품 대표 색상
            ['val' => 'imageStorage', 'typ' => 's', 'def' => 'local', 'name' => '이미지 저장소 위치'], // 이미지 저장소 위치
            ['val' => 'imagePath', 'typ' => 's', 'def' => null, 'name' => '이미지 저장 경로'], // 이미지 저장 경로
            ['val' => 'brandCd', 'typ' => 's', 'def' => null, 'name' => ' 브랜드 코드'], // 브랜드 코드
            ['val' => 'makerNm', 'typ' => 's', 'def' => null, 'name' => '제조사'], // 제조사
            ['val' => 'originNm', 'typ' => 's', 'def' => null, 'name' => '원산지'], // 원산지
            ['val' => 'hscode', 'typ' => 's', 'def' => null, 'name' => 'HS코드'], // HS코드
            ['val' => 'goodsModelNo', 'typ' => 's', 'def' => null, 'name' => '모델명'], // 상모델명
            ['val' => 'makeYmd', 'typ' => 's', 'def' => '0000-00-00', 'name' => '제조일'], // 제조일
            ['val' => 'launchYmd', 'typ' => 's', 'def' => '0000-00-00', 'name' => '출시일'], // 출시일
            ['val' => 'effectiveStartYmd', 'typ' => 's', 'def' => '0000-00-00 00:00:00', 'name' => '유효일자 시작일'], // 유효일자 시작
            ['val' => 'effectiveEndYmd', 'typ' => 's', 'def' => '0000-00-00 00:00:00', 'name' => '유효일자 종료일'], // 유효일자 종료
            ['val' => 'qrCodeFl', 'typ' => 's', 'def' => 'n', 'name' => 'QR코드 사용 여부'], // QR코드 사용 여부
            ['val' => 'goodsPermission', 'typ' => 's', 'def' => 'all', 'name' => '구매가능 회원등급 설정 '], // 구매가능 회원등급 설정 ('all'=>'전체(회원+비회원)','member'=>'회원전용(비회원제외)','group'=>'특정회원등급')
            ['val' => 'goodsPermissionGroup', 'typ' => 's', 'def' => null, 'name' => '구매가능 회원등급'], // 구매가능 회원등급
            ['val' => 'goodsPermissionPriceStringFl', 'typ' => 's', 'def' => 'n', 'name' => '구매불가 고객 가격 대체문구 사용'], //구매불가 고객 가격 대체문구 사용
            ['val' => 'goodsPermissionPriceString', 'typ' => 's', 'def' => null, 'name' => '구매불가 고객 가격 대체문구'], //구매불가 고객 가격 대체문구
            ['val' => 'onlyAdultFl', 'typ' => 's', 'def' => 'n', 'name' => '성인 인증 사용 여부'], // 성인 인증 사용 여부
            ['val' => 'onlyAdultDisplayFl', 'typ' => 's', 'def' => 'y', 'name' => '미인증 고객 상품 노출함'], // 미인증 고객 상품 노출함
            ['val' => 'onlyAdultImageFl', 'typ' => 's', 'def' => 'n', 'name' => '미인증 고객 상품 이미지 노출함'], // 미인증 고객 상품 이미지 노출함
            ['val' => 'goodsAccess', 'typ' => 's', 'def' => 'all', 'name' => '접근권한 회원그룹 설정 '], // 접근권한 회원그룹 설정 ('all'=>'전체(회원+비회원)','member'=>'회원전용(비회원제외)','group'=>'특정회원등급')
            ['val' => 'goodsAccessGroup', 'typ' => 's', 'def' => null, 'name' => '접근권한 회원그룹'], // 접근권한 회원그룹
            ['val' => 'goodsAccessDisplayFl', 'typ' => 's', 'def' => 'y', 'name' => '접근불가 고객 상품 노출함'], //접근불가 고객 상품 노출함
            ['val' => 'goodsMustInfo', 'typ' => 's', 'def' => null, 'name' => '상품 필수 정보'], // 상품 필수 정보
            ['val' => 'taxFreeFl', 'typ' => 's', 'def' => $conf['taxFreeFl'], 'name' => '과세/비과세/면세 여부'], // 과세/비과세/면세 여부
            ['val' => 'taxPercent', 'typ' => 'i', 'def' => $conf['taxPercent'], 'name' => '과세율'], // 과세율
            ['val' => 'goodsWeight', 'typ' => 'd', 'def' => '0.00', 'name' => '상품 무게'], // 상품 무게
            ['val' => 'goodsVolume', 'typ' => 'd', 'def' => '0.00', 'name' => '상품 용량'], // 상품 용량
            ['val' => 'totalStock', 'typ' => 's', 'def' => '0', 'name' => '통합 재고량'], // 통합 재고량
            ['val' => 'stockFl', 'typ' => 's', 'def' => 'n', 'name' => '재고 설정'], // 재고 설정
            ['val' => 'soldOutFl', 'typ' => 's', 'def' => 'n', 'name' => '품절 설정'], // 품절 설정
            ['val' => 'fixedSales', 'typ' => 's', 'def' => 'option', 'name' => '묶음주문 단위기준'], // 묶음주문 단위기준
            ['val' => 'fixedOrderCnt', 'typ' => 's', 'def' => 'option', 'name' => '구매수량 설정기준'], // 구매수량 설정기준
            ['val' => 'salesUnit', 'typ' => 's', 'def' => '1', 'name' => '묶음 주문 단위'], // 묶음 주문 단위
            ['val' => 'minOrderCnt', 'typ' => 'i', 'def' => 1, 'name' => '최소 구매 수량'], // 최소 구매 수량
            ['val' => 'maxOrderCnt', 'typ' => 'i', 'def' => '0', 'name' => '최대 구매 수량'], // 최대 구매 수량
            ['val' => 'salesStartYmd', 'typ' => 's', 'def' => '0000-00-00 00:00:00', 'name' => '상품 판매기간 시작일'], // 상품 판매기간 시작일
            ['val' => 'salesEndYmd', 'typ' => 's', 'def' => '0000-00-00 00:00:00', 'name' => '상품 판매기간 종료일'], // 상품 판매기간 종료일
            ['val' => 'restockFl', 'typ' => 's', 'def' => 'n', 'name' => '재입고알림'], // 재입고알림
            ['val' => 'mileageFl', 'typ' => 's', 'def' => 'c', 'name' => '마일리지 정책'], // 마일리지 정책
            ['val' => 'mileageGroup', 'typ' => 's', 'def' => 'all', 'name' => '마일리지 지급 대상'], // 마일리지 지급 대상
            ['val' => 'mileageGoods', 'typ' => 's', 'def' => '0', 'name' => '마일리지 개별설정'], // 마일리지 개별설정
            ['val' => 'mileageGoodsUnit', 'typ' => 's', 'def' => 'percent', 'name' => '마일리지 개별설정 단위'], // 마일리지 개별설정 단위 ('percent','mileage')
            ['val' => 'mileageGroupInfo', 'typ' => 's', 'def' => null, 'name' => '마일리지 지급 대상 회원그룹'], // 마일리지 지급 대상 회원그룹
            ['val' => 'mileageGroupMemberInfo', 'typ' => 'j', 'def' => '{}', 'name' => '마일리지 지급 대상 회원 정보'], // 마일리지 지급 대상 회원 정보
            ['val' => 'goodsBenefitSetFl', 'typ' => 's', 'def' => 'n', 'name' => '상품 혜택 적용 여부'], // 상품 혜택 적용 여부
            ['val' => 'benefitUseType', 'typ' => 's', 'def' => 'nonLimit', 'name' => '혜택 진행유형'], // 혜택 진행유형
            ['val' => 'newGoodsRegFl', 'typ' => 's', 'def' => 'regDt', 'name' => '신상품 등록/수정 여부'], // 신상품 등록/수정 여부
            ['val' => 'newGoodsDate', 'typ' => 'i', 'def' => null, 'name' => '신상품 혜택할인 기간'], // 	신상품 혜택할인 기간
            ['val' => 'newGoodsDateFl', 'typ' => 's', 'def' => 'day', 'name' => '신상품 혜택 시간 날짜 여부'], // 신상품 혜택 시간 날짜 여부
            ['val' => 'periodDiscountStart', 'typ' => 's', 'def' => null, 'name' => '특정기간 할인시작'], // 특정기간 할인시작
            ['val' => 'periodDiscountEnd', 'typ' => 's', 'def' => null, 'name' => '특정기간 할인종료'], // 특정기간 할인종료
            ['val' => 'goodsDiscountFl', 'typ' => 's', 'def' => 'n', 'name' => '상품 할인 설정'], // 상품 할인 설정
            ['val' => 'goodsDiscount', 'typ' => 's', 'def' => '0', 'name' => '상품 할인가'], // 상품 할인가
            ['val' => 'goodsDiscountUnit', 'typ' => 's', 'def' => 'percent', 'name' => '상품 할인 단위'], // 상품 할인 단위 ('percent','price')
            ['val' => 'fixedGoodsDiscount', 'typ' => 's', 'def' => null, 'name' => '상품할인금액기준'], // 상품할인금액기준
            ['val' => 'goodsDiscountGroup', 'typ' => 's', 'def' => 'all', 'name' => '상품할인대상'], // 상품할인대상
            ['val' => 'goodsDiscountGroupMemberInfo', 'typ' => 's', 'def' => '{}', 'name' => '상품할인 회원 정보'], // 상품할인 회원 정보
            ['val' => 'exceptBenefit', 'typ' => 's', 'def' => null, 'name' => '상품 할인/적립 제외 혜택'], // 상품 할인/적립 제외 혜택
            ['val' => 'exceptBenefitGroup', 'typ' => 's', 'def' => null, 'name' => '상품 할인/적립 제외 대상'], // 상품 할인/적립 제외 대상
            ['val' => 'exceptBenefitGroupInfo', 'typ' => 's', 'def' => null, 'name' => '상품 할인/적립 제외 그룹'], // 상품 할인/적립 제외 그룹
            ['val' => 'payLimitFl', 'typ' => 's', 'def' => 'n', 'name' => '결제수단 제한 설정'], // 결제수단 제한 사용 여부
            ['val' => 'payLimit', 'typ' => 's', 'def' => null, 'name' => '결제 수단 제한 값'], // 결제수단 제한 사용값
            ['val' => 'goodsPriceString', 'typ' => 's', 'def' => null, 'name' => '상품가격 대체문구'], // 상품가격 대체문구
            ['val' => 'goodsPrice', 'typ' => 's', 'def' => '0', 'name' => '상품가격'], // 상품가격
            ['val' => 'fixedPrice', 'typ' => 's', 'def' => '0', 'name' => '정가'], // 정가
            ['val' => 'costPrice', 'typ' => 's', 'def' => '0', 'name' => '매입가'], // 매입가
            ['val' => 'optionFl', 'typ' => 's', 'def' => 'n', 'name' => '옵션 사용 여부'], // 옵션 사용 여부
            ['val' => 'optionName', 'typ' => 's', 'def' => null, 'name' => '옵션명'], // 옵션명 (구분자 ^|^)
            ['val' => 'optionDisplayFl', 'typ' => 's', 'def' => 's', 'name' => '옵션 출력 방식'], // 옵션 출력 방식 (s - 일체형, d - 분리형)
            ['val' => 'optionImagePreviewFl', 'typ' => 's', 'def' => 'n', 'name' => '옵션 이미지 미리보기 사용'], // 옵션 출력 방식 (s - 일체형, d - 분리형)
            ['val' => 'optionImageDisplayFl', 'typ' => 's', 'def' => 'n', 'name' => '옵션 이미지 상세 이미지 추가'], // 옵션 출력 방식 (s - 일체형, d - 분리형)
            ['val' => 'optionTextFl', 'typ' => 's', 'def' => 'n', 'name' => '텍스트 옵션 사용 여부'], // 텍스트 옵션 사용 여부
            ['val' => 'addGoodsFl', 'typ' => 's', 'def' => 'n', 'name' => '추가 상품 사용여부'], // 추가 상품 사용여부
            ['val' => 'addGoods', 'typ' => 's', 'def' => null, 'name' => '추가 상품 정보'], // 추가 상품 정보
            ['val' => 'shortDescription', 'typ' => 's', 'def' => null, 'name' => '짧은 설명'], // 짧은 설명
            ['val' => 'eventDescription', 'typ' => 's', 'def' => null, 'name' => '이벤트 설명'], // 이벤트 설명
            ['val' => 'goodsDescription', 'typ' => 's', 'def' => null, 'name' => '상품 설명'], // 상품 설명
            ['val' => 'goodsDescriptionMobile', 'typ' => 's', 'def' => null, 'name' => '모바일샵 상품 설명'], // 모바일샵 상품 설명
            ['val' => 'goodsDescriptionSameFl', 'typ' => 's', 'def' => 'y', 'name' => 'pc/모바일설명공통사용'], // 모바일샵 상품 설명
            ['val' => 'deliverySno', 'typ' => 'i', 'def' => null, 'name' => '배송비 고유번호'], // 배송비 sno
            ['val' => 'relationFl', 'typ' => 's', 'def' => 'n', 'name' => '관련상품 사용여부'], // 관련상품 종류
            ['val' => 'relationSameFl', 'typ' => 's', 'def' => 'n', 'name' => '관련상품 서로등록 사용여부'], // 관련상품 종류
            ['val' => 'relationGoodsNo', 'typ' => 's', 'def' => null, 'name' => '관련상품 상품코드'], // 관련상품 상품코드
            ['val' => 'relationGoodsDate', 'typ' => 's', 'def' => null, 'name' => '관련상품 상품별 유효일자'], // 관련상품 상품코드
            ['val' => 'relationGoodsEach', 'typ' => 's', 'def' => null, 'name' => '관련상품 서로등록'], // 관련상품 서로등록
//            ['val' => 'goodsIconStartYmd', 'typ' => 's', 'def' => '0000-00-00', 'name' => '상품 아이콘 설정기간 - 시작'], // 상품 아이콘 설정기간 - 시작
//            ['val' => 'goodsIconEndYmd', 'typ' => 's', 'def' => '0000-00-00', 'name' => '상품 아이콘 설정기간 - 종료'], // 상품 아이콘 설정기간 - 종료
//            ['val' => 'goodsIconCdPeriod', 'typ' => 's', 'def' => null, 'name' => '상품 아이콘 (기간 제한용)'], // 상품 아이콘 (기간 제한용)
//            ['val' => 'goodsIconCd', 'typ' => 's', 'def' => null, 'name' => '상품 아이콘 (무제한용)'], // 상품 아이콘 (무제한용)
            ['val' => 'imgDetailViewFl', 'typ' => 's', 'def' => 'n', 'name' => '상품 이미지 돋보기 효과 사용여부'], // 상품 이미지 돋보기 효과
            ['val' => 'externalVideoFl', 'typ' => 's', 'def' => 'n', 'name' => '외부 비디오 연결 사용여부'], // 외부 비디오 연결 여부
            ['val' => 'externalVideoUrl', 'typ' => 's', 'def' => null, 'name' => '외부 비디오 주소'], // 외부 비디오 주소
            ['val' => 'externalVideoWidth', 'typ' => 's', 'def' => null, 'name' => '외부 비디오 넓이'], // 외부 비디오 넓이
            ['val' => 'externalVideoHeight', 'typ' => 's', 'def' => null, 'name' => '외부 비디오 높이'], // 외부 비디오 높이
            ['val' => 'detailInfoDeliveryFl', 'typ' => 's', 'def' => null, 'name' => '상품 상세 이용안내 - 배송안내 입력여부'], // 상품 상세 이용안내 - 배송안내 입력여부
            ['val' => 'detailInfoDelivery', 'typ' => 's', 'def' => null, 'name' => '상품 상세 이용안내 - 배송안내 선택입력'], // 상품 상세 이용안내 - 배송안내 선택입력
            ['val' => 'detailInfoDeliveryDirectInput', 'typ' => 's', 'def' => null, 'name' => '상품 상세 이용안내 - 배송안내 직접입력'], // 상품 상세 이용안내 - 배송안내 직접입력
            ['val' => 'detailInfoASFl', 'typ' => 's', 'def' => null, 'name' => '상품 상세 이용안내 - AS안내 입력여부'], // 상품 상세 이용안내 - AS안내 입력여부
            ['val' => 'detailInfoAS', 'typ' => 's', 'def' => null, 'name' => '상품 상세 이용안내 - AS안내 선택입력'], // 상품 상세 이용안내 - AS안내 선택입력
            ['val' => 'detailInfoASDirectInput', 'typ' => 's', 'def' => null, 'name' => '상품 상세 이용안내 - AS안내 직접입력'], // 상품 상세 이용안내 - AS안내 직접입력
            ['val' => 'detailInfoRefundFl', 'typ' => 's', 'def' => null, 'name' => '상품 상세 이용안내 - 환불안내 입력여부'], // 상품 상세 이용안내 - 환불안내 입력여부
            ['val' => 'detailInfoRefund', 'typ' => 's', 'def' => null, 'name' => '상품 상세 이용안내 - 환불안내 선택입력'], // 상품 상세 이용안내 - 환불안내 선택입력
            ['val' => 'detailInfoRefundDirectInput', 'typ' => 's', 'def' => null, 'name' => '상품 상세 이용안내 - 환불안내 직접입력'], // 상품 상세 이용안내 - 환불안내 직접입력
            ['val' => 'detailInfoExchangeFl', 'typ' => 's', 'def' => null, 'name' => '상품 상세 이용안내 - 교환안내 입력여부'], // 상품 상세 이용안내 - 교환안내 입력여부
            ['val' => 'detailInfoExchange', 'typ' => 's', 'def' => null, 'name' => '상품 상세 이용안내 - 교환안내 선택입력'], // 상품 상세 이용안내 - 교환안내 선택입력
            ['val' => 'detailInfoExchangeDirectInput', 'typ' => 's', 'def' => null, 'name' => '상품 상세 이용안내 - 교환안내 직접입력'], // 상품 상세 이용안내 - 교환안내 직접입력
            ['val' => 'memo', 'typ' => 's', 'def' => null, 'name' => '상품 메모'], // 상품 메모
            ['val' => 'orderCnt', 'typ' => 'i', 'def' => '0', 'name' => '주문량'], // 주문량
            ['val' => 'orderGoodsCnt', 'typ' => 'i', 'def' => '0', 'name' => '주문상품량'], // 주문량
            ['val' => 'hitCnt', 'typ' => 'i', 'def' => '0', 'name' => '조회수'], // 조회수
            ['val' => 'cartCnt', 'typ' => 'i', 'def' => '0', 'name' => '장바구니수'], // 조회수
            ['val' => 'wishCnt', 'typ' => 'i', 'def' => '0', 'name' => '관심상품수'], // 조회수
            ['val' => 'reviewCnt', 'typ' => 'i', 'def' => '0', 'name' => '상품평수'], // 상품평수
            ['val' => 'delFl', 'typ' => 's', 'def' => 'n', 'name' => '상품 삭제 여부'], // 상품 삭제 여부
            ['val' => 'applyFl', 'typ' => 's', 'def' => 'y', 'name' => '가입승인'], // 가입승인
            ['val' => 'applyType', 'typ' => 's', 'def' => 'r', 'name' => '승인구분'], // 승인구분
            ['val' => 'applyMsg', 'typ' => 's', 'def' => null, 'name' => '승인상태메시지'], // 승인상태메시지
            ['val' => 'applyDt', 'typ' => 's', 'def' => null, 'name' => '승인요청시각'], // 승인요청시각
            ['val' => 'excelFl', 'typ' => 's', 'def' => null, 'name' => '엑셀등록여부'], // 엑셀등록여부
            ['val' => 'seoTagFl', 'typ' => 's', 'def' => 'n', 'name' => '상품 개별 SEO 태그 설정 '], // 상품 개별 SEO 태그 설정
            ['val' => 'seoTagSno', 'typ' => 'i', 'def' => '0', 'name' => '상품 개별 SEO 태그 고유번호 '], // 상품 개별 SEO 태그 고유번호
            ['val' => 'naverFl', 'typ' => 's', 'def' => 'y', 'name' => '네이버쇼핑여부'], // 네이버수입및제작여부
            ['val' => 'daumFl', 'typ' => 's', 'def' => 'y', 'name' => '다음쇼핑하우연동여부'], // 다음쇼핑하우연동여부
            ['val' => 'paycoFl', 'typ' => 's', 'def' => 'y', 'name' => '페이코쇼핑여부'], // 페이코수입및제작여부
            ['val' => 'naverImportFlag', 'typ' => 's', 'def' => null, 'name' => '네이버수입및제작여부'], // 네이버수입및제작여부
            ['val' => 'naverProductFlag', 'typ' => 's', 'def' => null, 'name' => '네이버판매방식구분'], // 네이버판매방식구분
            ['val' => 'naverAgeGroup', 'typ' => 's', 'def' => 'a', 'name' => '네이버주요사용연령대'], // 네이버주요사용연령대
            ['val' => 'naverGender', 'typ' => 's', 'def' => null, 'name' => '네이버주요사용성별'], // 네이버주요사용성별
            ['val' => 'naverTag', 'typ' => 's', 'def' => null, 'name' => '네이버검색태그'], // 네이버주요사용성별
            ['val' => 'naverAttribute', 'typ' => 's', 'def' => null, 'name' => '네이버속성정보'], // 네이버속성정보
            ['val' => 'naverCategory', 'typ' => 's', 'def' => null, 'name' => '네이버카테고리ID'], // 네이버카테고리ID
            ['val' => 'naverProductId', 'typ' => 's', 'def' => null, 'name' => '네이버가격비교페이지ID'], // 네이버가격비교페이지ID
            ['val' => 'plusReviewCnt', 'typ' => 'i', 'def' => 0, 'name' => '플러스리뷰 후기개수'], // 플러스리뷰 후기개수
            ['val' => 'cremaReviewCnt', 'typ' => 'i', 'def' => 0, 'name' => '크리마리뷰 상품평수'], // 크리마리뷰 상품평수
            ['val' => 'cultureBenefitFl', 'typ' => 's', 'def' => 'n', 'name' => '도서공연비 소득공제 여부'], //도서공연비 소득공제 여부
            ['val' => 'kcmarkInfo', 'typ' => 'j', 'def' => '{}', 'name' => 'KC인증 정보'], //KC인증 정보
//            ['val' => 'kcmarkFl', 'typ' => 's', 'def' => 'n', 'name' => 'KC인증 표시 여부'], //KC인증 표시 여부
//            ['val' => 'kcmarkDivFl', 'typ' => 's', 'def' => null, 'name' => 'KC인증 구분'], //KC인증 구분
//            ['val' => 'kcmarkNum', 'typ' => 's', 'def' => null, 'name' => 'KC인증 번호'], //KC인증 번호
            ['val' => 'modDt', 'typ' => 's', 'def' => null], // 수정일
            ['val' => 'regDt', 'typ' => 's', 'def' => null], // 등록일
            ['val' => 'delDt', 'typ' => 's', 'def' => null], // 삭제일
        ];
        // @formatter:on

        return $arrField;
    }


    /**
     * [상품] goodsSearch 필드 기본값
     *
     * @author artherot
     * @return array goods 테이블 필드 정보
     */
    public static function tableGoodsSearch()
    {
        // @formatter:off
        $arrField = [
            ['val' => 'goodsNo', 'typ' => 's', 'def' => null, 'name' => '상품 번호'], // 상품 번호
            ['val' => 'goodsNm', 'typ' => 's', 'def' => null, 'name' => '상품명'], // 상품명
            ['val' => 'goodsDisplayFl', 'typ' => 's', 'def' => 'y', 'name' => '상품 출력 여부 - PC쇼핑몰','batch'=>'y'], // 상품 출력 여부 - PC
            ['val' => 'goodsDisplayMobileFl', 'typ' => 's', 'def' => 'y', 'name' => '상품 출력 여부 - 모바일샵','batch'=>'y'], // 상품 출력 여부 - 모바일샵
            ['val' => 'goodsSellFl', 'typ' => 's', 'def' => 'y', 'name' => '상품 판매 여부 - PC쇼핑몰','batch'=>'y'], // 상품 판매 여부 - PC
            ['val' => 'goodsSellMobileFl', 'typ' => 's', 'def' => 'y', 'name' => '상품 판매 여부 - 모바일샵','batch'=>'y'], // 상품 판매 여부 - 모바일샵
            ['val' => 'scmNo', 'typ' => 'i', 'def' => (string)Session::get('manager.scmNo'), 'name' => '공급사 고유 번호','batch'=>'y'], // 공급사 고유 번호
            ['val' => 'purchaseNo', 'typ' => 'i', 'def' => null, 'name' => '매입처','batch'=>'y'], // 매입처
            ['val' => 'purchaseGoodsNm', 'typ' => 's', 'def' => null, 'name' => '매입처 상품명'], // 매입처 상품명
            ['val' => 'goodsCd', 'typ' => 's', 'def' => null, 'name' => ' 상품 코드'], // 상품 코드
            ['val' => 'cateCd', 'typ' => 's', 'def' => null, 'name' => '대표 카테고리 코드','batch'=>'y'], // 대표 카테고리 코드
            ['val' => 'goodsSearchWord', 'typ' => 's', 'def' => null, 'name' => '상품 검색어'], // 상품 검색어
            ['val' => 'goodsOpenDt', 'typ' => 's', 'def' => '0000-00-00 00:00:00', 'name' => '상품 노출 시간'], // 상품 노출 시간
            ['val' => 'onlyAdultFl', 'typ' => 's', 'def' => 'n', 'name' => '성인 인증 사용 여부'], // 성인 인증 사용 여부
            ['val' => 'onlyAdultDisplayFl', 'typ' => 's', 'def' => 'y', 'name' => '미인증 고객 상품 노출함'], // 미인증 고객 상품 노출함
            ['val' => 'goodsAccess', 'typ' => 's', 'def' => 'all', 'name' => '접근권한 회원그룹 설정 '], // 접근권한 회원그룹 설정 ('all'=>'전체(회원+비회원)','member'=>'회원전용(비회원제외)','group'=>'특정회원등급')
            ['val' => 'goodsAccessGroup', 'typ' => 's', 'def' => null, 'name' => '접근권한 회원그룹'], // 접근권한 회원그룹
            ['val' => 'goodsAccessDisplayFl', 'typ' => 's', 'def' => 'y', 'name' => '접근불가 고객 상품 노출함'], //접근불가 고객 상품 노출함
            ['val' => 'goodsColor', 'typ' => 's', 'def' => null, 'name' => '상품 대표 색상','batch'=>'y'], // 상품 대표 색상
            ['val' => 'brandCd', 'typ' => 's', 'def' => null, 'name' => ' 브랜드 코드','batch'=>'y'], // 브랜드 코드
            ['val' => 'makerNm', 'typ' => 's', 'def' => null, 'name' => '제조사'], // 제조사
            ['val' => 'originNm', 'typ' => 's', 'def' => null, 'name' => '원산지'], // 원산지
            ['val' => 'hscode', 'typ' => 's', 'def' => null, 'name' => 'HS코드'], // HS코드
            ['val' => 'goodsModelNo', 'typ' => 's', 'def' => null, 'name' => '모델명'], // 상모델명
            ['val' => 'totalStock', 'typ' => 's', 'def' => '0', 'name' => '통합 재고량','batch'=>'y'], // 통합 재고량
            ['val' => 'stockFl', 'typ' => 's', 'def' => 'n', 'name' => '재고 설정','batch'=>'y'], // 재고 설정
            ['val' => 'soldOutFl', 'typ' => 's', 'def' => 'n', 'name' => '품절 설정','batch'=>'y'], // 품절 설정
            ['val' => 'mileageFl', 'typ' => 's', 'def' => 'c', 'name' => '마일리지 정책','batch'=>'y'], // 마일리지 정책
            ['val' => 'mileageGoods', 'typ' => 's', 'def' => '0', 'name' => '마일리지 개별설정','batch'=>'y'], // 마일리지 개별설정
            ['val' => 'goodsPrice', 'typ' => 's', 'def' => '0', 'name' => '상품가격','batch'=>'y'], // 상품가격
            ['val' => 'optionFl', 'typ' => 's', 'def' => 'n', 'name' => '옵션 사용 여부','batch'=>'y'], // 옵션 사용 여부
            ['val' => 'optionTextFl', 'typ' => 's', 'def' => 'n', 'name' => '텍스트 옵션 사용 여부'], // 텍스트 옵션 사용 여부
            ['val' => 'addGoodsFl', 'typ' => 's', 'def' => 'n', 'name' => '추가 상품 사용여부'], // 추가 상품 사용여부
            ['val' => 'deliverySno', 'typ' => 'i', 'def' => null, 'name' => '배송비 고유번호','batch'=>'y'], // 배송비 sno
            ['val' => 'goodsIconCdPeriod', 'typ' => 's', 'def' => null, 'name' => '상품 아이콘 (기간 제한용)','batch'=>'y'], // 상품 아이콘 (기간 제한용)
            ['val' => 'goodsIconCd', 'typ' => 's', 'def' => null, 'name' => '상품 아이콘 (무제한용)','batch'=>'y'], // 상품 아이콘 (무제한용)
            ['val' => 'memo', 'typ' => 's', 'def' => null, 'name' => '상품 메모'], // 상품 메모
            ['val' => 'orderCnt', 'typ' => 'i', 'def' => '0', 'name' => '주문량'], // 주문량
            ['val' => 'orderGoodsCnt', 'typ' => 'i', 'def' => '0', 'name' => '주문상품량'], // 주문상품량
            ['val' => 'hitCnt', 'typ' => 'i', 'def' => '0', 'name' => '조회수'], // 조회수
            ['val' => 'cartCnt', 'typ' => 'i', 'def' => '0', 'name' => '장바구니수'], // 장바구니수
            ['val' => 'wishCnt', 'typ' => 'i', 'def' => '0', 'name' => '관심상품수'], // 관심상품수
            ['val' => 'reviewCnt', 'typ' => 'i', 'def' => '0', 'name' => '리뷰수'], // 리뷰수
            ['val' => 'delFl', 'typ' => 's', 'def' => 'n', 'name' => '상품 삭제 여부','batch'=>'y'], // 상품 삭제 여부
            ['val' => 'applyFl', 'typ' => 's', 'def' => 'y', 'name' => '가입승인','batch'=>'y'], // 가입승인
            ['val' => 'applyType', 'typ' => 's', 'def' => 'r', 'name' => '승인구분','batch'=>'y'], // 승인구분
            ['val' => 'applyDt', 'typ' => 's', 'def' => null, 'name' => '승인요청시각','batch'=>'y'], // 승인요청시각
            ['val' => 'naverFl', 'typ' => 's', 'def' => 'y', 'name' => '네이버쇼핑여부','batch'=>'y'], // 네이버수입및제작여부
            ['val' => 'daumFl', 'typ' => 's', 'def' => 'y', 'name' => '다음쇼핑하우연동여부', 'batch'=>'y'], // 다음쇼핑하우연동여부
            ['val' => 'paycoFl', 'typ' => 's', 'def' => 'y', 'name' => '페이코쇼핑여부','batch'=>'y'], // 페이코수입및제작여부
            ['val' => 'modDt', 'typ' => 's', 'def' => null], // 수정일
            ['val' => 'regDt', 'typ' => 's', 'def' => null], // 등록일
        ];
        // @formatter:on

        return $arrField;
    }


    /**
     * [상품] goods 필드 기본값
     *
     * @author artherot
     * @return array goods 테이블 필드 정보
     */
    public static function tableGoods_mobileappModify()
    {
        // @formatter:off
        $arrField = [
            ['val' => 'goodsNo', 'typ' => 's', 'def' => null, 'name' => '상품 번호'], // 상품 번호
            ['val' => 'goodsNm', 'typ' => 's', 'def' => null, 'name' => '상품명'], // 상품명
            ['val' => 'goodsDisplayFl', 'typ' => 's', 'def' => 'y', 'name' => '상품 출력 여부 - PC쇼핑몰'], // 상품 출력 여부 - PC
            ['val' => 'goodsDisplayMobileFl', 'typ' => 's', 'def' => 'y', 'name' => '상품 출력 여부 - 모바일샵'], // 상품 출력 여부 - 모바일샵
            ['val' => 'stockFl', 'typ' => 's', 'def' => 'n', 'name' => '재고 설정'], // 재고 설정
            ['val' => 'totalStock', 'typ' => 's', 'def' => '0', 'name' => '통합 재고량'], // 통합 재고량
            ['val' => 'soldOutFl', 'typ' => 's', 'def' => 'n', 'name' => '품절 설정'], // 품절 설정
            ['val' => 'goodsPrice', 'typ' => 's', 'def' => '0', 'name' => '상품가격'], // 상품가격
            ['val' => 'fixedPrice', 'typ' => 's', 'def' => '0', 'name' => '정가'], // 정가
            ['val' => 'optionFl', 'typ' => 's', 'def' => 'n', 'name' => '옵션 사용 여부'], // 옵션 사용 여부
            ['val' => 'optionName', 'typ' => 's', 'def' => null, 'name' => '옵션명'], // 옵션명 (구분자 ^|^)
            ['val' => 'optionDisplayFl', 'typ' => 's', 'def' => 's', 'name' => '옵션 출력 방식'], // 옵션 출력 방식 (s - 일체형, d - 분리형)
            ['val' => 'modDt', 'typ' => 's', 'def' => null], // 수정일
        ];
        // @formatter:on

        return $arrField;
    }

    /**
     * [상품] goodsGloabl 글로벌
     *
     * @author atomyang
     * @return array addGoods 테이블 필드 정보
     */
    public static function tableGoodsGlobal()
    {
        // @formatter:off
        $arrField = [
            ['val' => 'mallSno', 'typ' => 's', 'def' => '1'], // 멀티상점 일련번호
            ['val' => 'goodsNo', 'typ' => 's', 'def' => null, 'name' => '고유번호'], // 상품 번호
            ['val' => 'goodsNm', 'typ' => 's', 'def' => null, 'name' => '상품명'], // 상품명
            ['val' => 'shortDescription', 'typ' => 's', 'def' => null, 'name' => '짧은설명'], // 짧은설명
        ];
        // @formatter:on

        return $arrField;
    }

    /**
     * [추가상품] addGoods 필드 기본값
     *
     * @author atomyang
     * @return array addGoods 테이블 필드 정보
     */
    public static function tableAddGoods()
    {
        if (empty($conf['taxFreeFl'])) {
            $conf['taxFreeFl'] = 't';
        }
        if (empty($conf['taxPercent'])) {
            $conf['taxPercent'] = 10;
        }

        // @formatter:off
        $arrField = [
            ['val' => 'addGoodsNo', 'typ' => 's', 'def' => null, 'name' => '고유번호'], // 상품 번호
            ['val' => 'applyFl', 'typ' => 's', 'def' => 'y', 'name' => '가입승인'], // 가입승인
            ['val' => 'applyType', 'typ' => 's', 'def' => 'r', 'name' => '승인구분'], // 승인구분
            ['val' => 'applyMsg', 'typ' => 's', 'def' => null, 'name' => '승인상태메시지'], // 승인상태메시지
            ['val' => 'applyDt', 'typ' => 's', 'def' => null, 'name' => '승인요청시각'], // 승인요청시각
            ['val' => 'goodsNm', 'typ' => 's', 'def' => null, 'name' => '상품명'], // 상품명
            ['val' => 'goodsDescription', 'typ' => 's', 'def' => null, 'name' => '상품설명'], // 상품설명
            ['val' => 'goodsModelNo', 'typ' => 's', 'def' => null, 'name' => '상품 모델 번호'], // 상품 모델 번호
            ['val' => 'goodsCd', 'typ' => 's', 'def' => null, 'name' => '자체 상품 코드'], // 상품 코드
            ['val' => 'scmNo', 'typ' => 'i', 'def' => (string)Session::get('manager.scmNo'), 'name' => '공급사 고유 번호'], // 공급사 고유 번호
            ['val' => 'purchaseNo', 'typ' => 'i', 'def' => null, 'name' => '매입처'], // 매입처
            ['val' => 'commission', 'typ' => 'd', 'def' => '0.00', 'name' => ' 공급사 수수료율'], // 공급사 수수료율
            ['val' => 'brandCd', 'typ' => 's', 'def' => null, 'name' => '브랜드 코드'], // 브랜드 코드
            ['val' => 'makerNm', 'typ' => 's', 'def' => null, 'name' => '제조사'], // 제조사
            ['val' => 'imageStorage', 'typ' => 's', 'def' => 'local', 'name' => '이미지 저장소 위치'], // 이미지 저장소 위치
            ['val' => 'imagePath', 'typ' => 's', 'def' => null, 'name' => '이미지 저장 경로'], // 이미지 저장 경로
            ['val' => 'taxFreeFl', 'typ' => 's', 'def' => $conf['taxFreeFl'], 'name' => '과세/비과세/면세 여부'], // 과세/비과세/면세 여부
            ['val' => 'taxPercent', 'typ' => 'i', 'def' => $conf['taxPercent'], 'name' => '과세율'], // 과세율
            ['val' => 'goodsPrice', 'typ' => 's', 'def' => '0', 'name' => '상품 가격'], // 상품 가격
            ['val' => 'costPrice', 'typ' => 's', 'def' => '0', 'name' => '상품 매입가격'], // 상품 가격
            ['val' => 'stockUseFl', 'typ' => 's', 'def' => '0', 'name' => '무한재고사용관련'], // 재고
            ['val' => 'stockCnt', 'typ' => 'i', 'def' => '0', 'name' => '재고'], // 재고
            ['val' => 'imageNm', 'typ' => 's', 'def' => null, 'name' => '이미지'], // 이미지
            ['val' => 'viewFl', 'typ' => 's', 'def' => 'y', 'name' => '노출여부'], // 노출여부
            ['val' => 'soldOutFl', 'typ' => 's', 'def' => 'n', 'name' => '품절여부'], // 품절여부
            ['val' => 'optionNm', 'typ' => 's', 'def' => null, 'name' => '옵션명'], // 옵션명
        ];
        // @formatter:on

        return $arrField;
    }

    /**
     * [추가상품] addGoodsGloabl 글로벌
     *
     * @author atomyang
     * @return array addGoods 테이블 필드 정보
     */
    public static function tableAddGoodsGlobal()
    {
        // @formatter:off
        $arrField = [
            ['val' => 'mallSno', 'typ' => 's', 'def' => '1'], // 멀티상점 일련번호
            ['val' => 'addGoodsNo', 'typ' => 's', 'def' => null, 'name' => '고유번호'], // 상품 번호
            ['val' => 'goodsNm', 'typ' => 's', 'def' => null, 'name' => '상품명'], // 상품명
        ];
        // @formatter:on

        return $arrField;
    }

    /**
     * [추가상품] addGoodsGroup 필드 기본값
     *
     * @author atomyang
     * @return array addGoodsGroup 테이블 필드 정보
     */
    public static function tableAddGoodsGroup()
    {
        // @formatter:off
        $arrField = [
            ['val' => 'groupCd', 'typ' => 's', 'def' => null], // 상품 번호
            ['val' => 'groupNm', 'typ' => 's', 'def' => null], // 그룹명
            ['val' => 'groupDescription', 'typ' => 's', 'def' => null], // 그룹설명
            ['val' => 'addGoodsCnt', 'typ' => 'i', 'def' => null], // 상품수
            ['val' => 'scmNo', 'typ' => 'i', 'def' => (string)Session::get('manager.scmNo')], // 공급사 고유 번호
        ];
        // @formatter:on

        return $arrField;
    }

    /**
     * [추가상품] addGoodsGroupGoods 필드 기본값
     *
     * @author atomyang
     * @return array addGoodsGroupGoods 테이블 필드 정보
     */
    public static function tableAddGoodsGroupGoods()
    {
        // @formatter:off
        $arrField = [
            ['val' => 'groupCd', 'typ' => 's', 'def' => null], // 그룹코드
            ['val' => 'addGoodsNo', 'typ' => 's', 'def' => null], // 상품코드
            ['val' => 'sort', 'typ' => 'i', 'def' => null], // 정렬
        ];
        // @formatter:on

        return $arrField;
    }

    /**
     * [상품] goods_link_brand 필드 기본값
     *
     * @author artherot
     * @return array goods_link_brand 테이블 필드 정보
     */
    public static function tableGoodsLinkBrand()
    {
        // @formatter:off
        $arrField = [
            ['val' => 'goodsNo', 'typ' => 's', 'def' => null], // 상품 번호
            ['val' => 'cateCd', 'typ' => 's', 'def' => null], // 브랜드 번호
            ['val' => 'cateLinkFl', 'typ' => 's', 'def' => 'y'], // 상품에 연결된 상태 여부
            ['val' => 'goodsSort', 'typ' => 'i', 'def' => null], // 상품순서
            ['val' => 'fixSort', 'typ' => 'i', 'def' => null], // 상품고정여부
        ];
        // @formatter:on

        return $arrField;
    }

    /**
     * [상품] goods_link_category 필드 기본값
     *
     * @author artherot
     * @return array goods_link_category 테이블 필드 정보
     */
    public static function tableGoodsLinkCategory()
    {
        // @formatter:off
        $arrField = [
            ['val' => 'goodsNo', 'typ' => 's', 'def' => null], // 상품 번호
            ['val' => 'cateCd', 'typ' => 's', 'def' => null], // 카테고리 번호
            ['val' => 'cateLinkFl', 'typ' => 's', 'def' => 'y'], // 상품에 연결된 상태 여부
            ['val' => 'goodsSort', 'typ' => 'i', 'def' => null], // 상품순서
            ['val' => 'fixSort', 'typ' => 'i', 'def' => null], // 상품고정여부
        ];
        // @formatter:on

        return $arrField;
    }

    /**
     * [상품] goods_add_info 필드 기본값
     *
     * @author artherot
     * @return array goods_add_info 테이블 필드 정보
     */
    public static function tableGoodsAddInfo()
    {
        // @formatter:off
        $arrField = [
            ['val' => 'goodsNo', 'typ' => 's', 'def' => null], // 상품 번호
            ['val' => 'infoTitle', 'typ' => 's', 'def' => null], // 추가정보 타이틀
            ['val' => 'infoValue', 'typ' => 's', 'def' => null], // 추가정보 내용
        ];
        // @formatter:on

        return $arrField;
    }

    /**
     * [상품] goods_option 필드 기본값
     *
     * @author artherot
     * @return array goods_option 테이블 필드 정보
     */
    public static function tableGoodsOption()
    {
        // @formatter:off
        $arrField = [
            ['val' => 'goodsNo', 'typ' => 's', 'def' => null], // 상품 번호
            ['val' => 'optionNo', 'typ' => 'i', 'def' => '1'], // 옵션 번호
            ['val' => 'optionPrice', 'typ' => 's', 'def' => '0'], // 상품 가격
            ['val' => 'optionCostPrice', 'typ' => 's', 'def' => '0'], // 옵션 매입가
            ['val' => 'optionCode', 'typ' => 's', 'def' => null], // 계산부호
            ['val' => 'optionViewFl', 'typ' => 's', 'def' => 'y'], // 계산부호
            ['val' => 'optionSellFl', 'typ' => 's', 'def' => 'y'], // 계산부호
            ['val' => 'stockCnt', 'typ' => 's', 'def' => '0'], // 재고량
            ['val' => 'optionValue1', 'typ' => 's', 'def' => null], // 옵션값 1
            ['val' => 'optionValue2', 'typ' => 's', 'def' => null], // 옵션값 2
            ['val' => 'optionValue3', 'typ' => 's', 'def' => null], // 옵션값 3
            ['val' => 'optionValue4', 'typ' => 's', 'def' => null], // 옵션값 4
            ['val' => 'optionValue5', 'typ' => 's', 'def' => null], // 옵션값 5
            ['val' => 'optionMemo', 'typ' => 's', 'def' => null], // 옵션 메모
            ['val' => 'optionSellCode', 'typ' => 's', 'def' => null], // 옵션 품절상태 사유코드
            ['val' => 'optionDeliveryCode', 'typ' => 's', 'def' => null], // 옵션 배송상태 사유코드
            ['val' => 'optionDeliveryFl', 'typ' => 's', 'def' => null], // 옵션 배송상태 적용 여부
            ['val' => 'sellStopFl', 'typ' => 's', 'def' => null], // 판매중지수량 사용여부
            ['val' => 'sellStopStock', 'typ' => 's', 'def' => null], // 판매중지수량
            ['val' => 'confirmRequestFl', 'typ' => 's', 'def' => null], // 확인요청수량 사용여부
            ['val' => 'confirmRequestStock', 'typ' => 's', 'def' => null], // 확인요청수량

        ];
        // @formatter:on

        return $arrField;
    }

    /**
     * [상품] goods_option_icon 필드 기본값
     *
     * @author artherot
     * @return array goods_option_icon 테이블 필드 정보
     */
    public static function tableGoodsOptionIcon()
    {
        // @formatter:off
        $arrField = [
            ['val' => 'goodsNo', 'typ' => 's', 'def' => null], // 상품 번호
            ['val' => 'optionNo', 'typ' => 'i', 'def' => '0'], // 옵션 번호
            ['val' => 'optionValue', 'typ' => 's', 'def' => null], // 옵션값
            ['val' => 'colorCode', 'typ' => 's', 'def' => null], // 칼라 코드
            ['val' => 'iconImage', 'typ' => 's', 'def' => null], // 아이콘 이미지
            ['val' => 'goodsImage', 'typ' => 's', 'def' => null], // 상품 이미지
        ];
        // @formatter:on

        return $arrField;
    }

    /**
     * [상품] goods_option_icon_temp 필드 기본값
     *
     * @author seonghu
     * @return array goods_option_icon_temp 테이블 필드 정보
     */
    public static function tableGoodsOptionIconTemp()
    {
        // @formatter:off
        $arrField = [
            ['val' => 'session', 'typ' => 's', 'def' => null], // 상품 번호
            ['val' => 'optionNo', 'typ' => 'i', 'def' => '0'], // 옵션 번호
            ['val' => 'optionValue', 'typ' => 's', 'def' => null], // 옵션값
            ['val' => 'goodsImage', 'typ' => 's', 'def' => null], // 상품 이미지
            ['val' => 'isUpdated', 'typ' => 's', 'def' => 'n'], // 상품 이미지 수정 여부
        ];
        // @formatter:on

        return $arrField;
    }

    /**
     * [상품] goods_option_text 필드 기본값
     *
     * @author artherot
     * @return array goods_option_text 테이블 필드 정보
     */
    public static function tableGoodsOptionText()
    {
        // @formatter:off
        $arrField = [
            ['val' => 'goodsNo', 'typ' => 's', 'def' => null], // 상품 번호
            ['val' => 'optionName', 'typ' => 's', 'def' => null], // 옵션명
            ['val' => 'mustFl', 'typ' => 's', 'def' => 'n'], // 필수 여부
            ['val' => 'addPrice', 'typ' => 'd', 'def' => '0.00'], // 추가 금액
            ['val' => 'inputLimit', 'typ' => 'i', 'def' => 20], // 입력글자 제한수
        ];
        // @formatter:on

        return $arrField;
    }

    /**
     * [상품] goods_image 필드 기본값
     *
     * @author artherot
     * @return array goods_image 테이블 필드 정보
     */
    public static function tableGoodsMustInfo()
    {
        // @formatter:off
        $arrField = [
            ['val' => 'mustInfoNm', 'typ' => 's', 'def' => null], // 상품 번호
            ['val' => 'info', 'typ' => 's', 'def' => null], //필수정보내용
            ['val' => 'scmNo', 'typ' => 'i', 'def' => (string)Session::get('manager.scmNo')], // 공급사 고유 번호
            ['val' => 'useCnt', 'typ' => 'i', 'def' => '0'],
        ];
        // @formatter:on

        return $arrField;
    }

    /**
     * [상품] goods_image 필드 기본값
     *
     * @author artherot
     * @return array goods_image 테이블 필드 정보
     */
    public static function tableGoodsImage()
    {
        // @formatter:off
        $arrField = [
            ['val' => 'goodsNo', 'typ' => 's', 'def' => null], // 상품 번호
            ['val' => 'imageSize', 'typ' => 'i', 'def' => '0'], // 이미지 사이즈
            ['val' => 'imageHeightSize', 'typ' => 'i', 'def' => null], // 이미지 세로 사이즈
            ['val' => 'imageNo', 'typ' => 'i', 'def' => '0'], // 이미지 번호
            ['val' => 'imageKind', 'typ' => 's', 'def' => null], // 이미지 종류
            ['val' => 'imageName', 'typ' => 's', 'def' => null], // 이미지 명
            ['val' => 'imageRealSize', 'typ' => 's', 'def' => null], // 이미지 가로 세로 사이즈
        ];
        // @formatter:on

        return $arrField;
    }


    /**
     * [상품] goodsUpdateNaver
     *
     * @author artherot
     * @return array goods_image 테이블 필드 정보
     */
    public static function tableGoodsUpdateNaver()
    {
        // @formatter:off
        $arrField = [
            ['val' => 'sno', 'typ' => 's', 'def' => null], // 일련번호
            ['val' => 'class', 'typ' => 's', 'def' => null], // I  (신규 상품) / U (업데이트 상품) / D (품절 상품)
            ['val' => 'mapid', 'typ' => 'i', 'def' => '0'], // 상품고유번호
            ['val' => 'naverCheckFl', 'typ' => 's', 'def' => 'n'], // 네이버업데이트 확인여부
            ['val' => 'daumCheckFl', 'typ' => 's', 'def' => 'n'], // 다음업데이트 확인여부
            ['val' => 'paycoCheckFl', 'typ' => 's', 'def' => 'n'], // 페이코업데이트 확인여부
            /*['val' => 'utime', 'typ' => 's', 'def' => null], // 등록시간
            ['val' => 'pname', 'typ' => 's', 'def' => null], // 상품명
            ['val' => 'price', 'typ' => 's', 'def' => null], // 상품가격
            ['val' => 'pgurl', 'typ' => 's', 'def' => null], // 상품주소
            ['val' => 'igurl', 'typ' => 'i', 'def' => null], // 이미지주소
            ['val' => 'cate1', 'typ' => 'i', 'def' => null], // 카테고리1
            ['val' => 'cate2', 'typ' => 's', 'def' => null], // 카테고리2
            ['val' => 'cate3', 'typ' => 's', 'def' => null], // 카테고리3
            ['val' => 'cate4', 'typ' => 's', 'def' => null], // 카테고리4
            ['val' => 'caid1', 'typ' => 's', 'def' => null], // 카테고리아이디1
            ['val' => 'caid2', 'typ' => 'i', 'def' => null], // 카테고리아이디2
            ['val' => 'caid3', 'typ' => 'i', 'def' => null], // 카테고리아이디3
            ['val' => 'caid4', 'typ' => 's', 'def' => null], // 카테고리아이디4
            ['val' => 'model', 'typ' => 's', 'def' => null], // 모델명
            ['val' => 'brand', 'typ' => 's', 'def' => null], // 브랜드
            ['val' => 'maker', 'typ' => 's', 'def' => null], // 제조사
            ['val' => 'origi', 'typ' => 'i', 'def' => null], // 원산지
            ['val' => 'deliv', 'typ' => 'i', 'def' => null], // 배송료
            ['val' => 'coupo', 'typ' => 's', 'def' => null], // 쿠폰
            ['val' => 'pcard', 'typ' => 's', 'def' => null], // 카드할부
            ['val' => 'point', 'typ' => 's', 'def' => null], // 포인트
            ['val' => 'mvurl', 'typ' => 's', 'def' => null], // 동영상 상품 여부
            */
        ];

        // @formatter:on

        return $arrField;
    }

    /**
     * [상품] goodsPageView
     *
     * @author yjwee
     * @return array es_goodsPageView 테이블 필드 정보
     */
    public static function tableGoodsPageView()
    {
        // @formatter:off
        $arrField = [
            ['val' => 'mallSno',        'typ' => 'i', 'def' => null],   // 상점 고유번호
            ['val' => 'pageUrl',        'typ' => 's', 'def' => null],   // 페이지주소
            ['val' => 'pageViewCount',  'typ' => 'i', 'def' => 0],      // 페이지 뷰 수
            ['val' => 'pageViewSec',    'typ' => 'i', 'def' => 0],      // 페이지 뷰 시간
            ['val' => 'startCount',     'typ' => 'i', 'def' => 0],      // 페이지 시작 수
            ['val' => 'endCount',       'typ' => 'i', 'def' => 0],      // 페이지 종료 수
            ['val' => 'viewDate',       'typ' => 's', 'def' => null],   // 페이지 뷰 날짜
        ];
        // @formatter:on

        return $arrField;
    }

    /**
     * [사은품] gift 필드 기본값
     *
     * @author artherot
     * @return array gift 테이블 필드 정보
     */
    public static function tableGift()
    {
        // @formatter:off
        $arrField = [
            ['val' => 'giftNo', 'typ' => 's', 'def' => null, 'excelFl'=>'y'], // 사은품 번호
            ['val' => 'giftCd', 'typ' => 's', 'def' => null , 'excelFl'=>'y'], // 사은품 코드
            ['val' => 'giftNm', 'typ' => 's', 'def' => null, 'excelFl'=>'y'], // 사은품명
            ['val' => 'scmNo', 'typ' => 'i', 'def' => (string)Session::get('manager.scmNo'), 'excelFl'=>'y'], // 공급사 고유 번호
            ['val' => 'brandCd', 'typ' => 's', 'def' => null, 'excelFl'=>'y'], // 브랜드코드
            ['val' => 'makerNm', 'typ' => 's', 'def' => null, 'excelFl'=>'y'], // 제조사명
            ['val' => 'stockFl', 'typ' => 's', 'def' => 'n', 'excelFl'=>'y'], // 재고 사용 여부
            ['val' => 'stockCnt', 'typ' => 'i', 'def' => '0', 'excelFl'=>'y'], // 재고량
            ['val' => 'giftDescription', 'typ' => 's', 'def' => null, 'excelFl'=>'y'], // 사은품 설명
            ['val' => 'imageStorage', 'typ' => 's', 'def' => 'local', 'excelFl'=>'y'], // 이미지 저장소
            ['val' => 'imagePath', 'typ' => 's', 'def' => null, 'excelFl'=>'y'], // 이미지 경로
            ['val' => 'imageNm', 'typ' => 's', 'def' => null, 'excelFl'=>'y'], // 이미지명
        ];
        // @formatter:on

        return $arrField;
    }

    /**
     * [사은품] gift_present 필드 기본값
     *
     * @author artherot
     * @return array gift_present 테이블 필드 정보
     */
    public static function tableGiftPresent()
    {
        // @formatter:off
        $arrField = [
            ['val' => 'presentTitle', 'typ' => 's', 'def' => null], // 증정 제목
            ['val' => 'scmNo', 'typ' => 'i', 'def' =>(string)Session::get('manager.scmNo')], // 공급사 고유 번호
            ['val' => 'presentPeriodFl', 'typ' => 's', 'def' => 'n'], // 증정 기간 종류
            ['val' => 'presentPermission', 'typ' => 's', 'def' => 'all'], // 구매가능 회원등급 설정 ('all'=>'전체(회원+비회원)','member'=>'회원전용(비회원제외)','group'=>'특정회원등급')
            ['val' => 'presentPermissionGroup', 'typ' => 's', 'def' => null], // 구매가능 회원등급
            ['val' => 'periodStartYmd', 'typ' => 's', 'def' => null], // 증정 시작일자
            ['val' => 'periodEndYmd', 'typ' => 's', 'def' => null], // 증정 만료일자
            ['val' => 'presentFl', 'typ' => 's', 'def' => 'a'], // 증정 유형 범위
            ['val' => 'presentKindCd', 'typ' => 's', 'def' => null], // 증정 유형 코드
            ['val' => 'exceptGoodsNo', 'typ' => 's', 'def' => null], // 예외 상품
            ['val' => 'exceptCateCd', 'typ' => 's', 'def' => null], // 예외 카테고리
            ['val' => 'exceptBrandCd', 'typ' => 's', 'def' => null], // 예외 브랜드
            ['val' => 'exceptEventCd', 'typ' => 's', 'def' => null], // 예외 이벤트
            ['val' => 'conditionFl', 'typ' => 's', 'def' => 'a'], // 증정 조건 (무관/금액/수량)
            ['val' => 'addGoodsFl', 'typ' => 's', 'def' => 'n'], // y: 추가상품수량포함 n: 미포함
        ];
        // @formatter:on

        return $arrField;
    }

    /**
     * [사은품] gift_present_info 필드 기본값
     *
     * @author artherot
     * @return array gift_present_info 테이블 필드 정보
     */
    public static function tableGiftPresentInfo()
    {
        // @formatter:off
        $arrField = [
            ['val' => 'presentSno', 'typ' => 'i', 'def' => null], // 사은품 증정 sno
            ['val' => 'infoNo', 'typ' => 'i', 'def' => '0'], // 정보 번호
            ['val' => 'conditionStart', 'typ' => 'i', 'def' => '0'], // 조건 시작(금액/수량)
            ['val' => 'conditionEnd', 'typ' => 'i', 'def' => '0'], // 조건 끝(금액/수량)
            ['val' => 'multiGiftNo', 'typ' => 's', 'def' => null], // 사은품 코드
            ['val' => 'selectCnt', 'typ' => 'i', 'def' => '0'], // 멀티 선택형 모드 (선택개수)
            ['val' => 'giveCnt', 'typ' => 'i', 'def' => 1], // 상품 갯수형 모드 (증정개수)
        ];
        // @formatter:on

        return $arrField;
    }

    /**
     * [관리자] manager 필드 기본값
     *
     * @author artherot
     * @return array manager 테이블 필드 정보
     */
    public static function tableManager()
    {
        // @formatter:off
        $arrField = [
            ['val' => 'managerId', 'typ' => 's', 'def' => null], // 관리자 아이디
            ['val' => 'managerNm', 'typ' => 's', 'def' => null], // 관리자명
            ['val' => 'managerNickNm', 'typ' => 's', 'def' => null], // 닉네임
            ['val' => 'managerPw', 'typ' => 's', 'def' => null], // 관리자 패스워드
            ['val' => 'employeeFl', 'typ' => 's', 'def' => 'y'], // 직원여부 ('y-직원','t-비정규직','p-아르바이트','d-파견직','r-퇴사자')
            ['val' => 'departmentCd', 'typ' => 's', 'def' => null], // 부서코드
            ['val' => 'positionCd', 'typ' => 's', 'def' => null], // 직급코드
            ['val' => 'dutyCd', 'typ' => 's', 'def' => null], // 직책코드
            ['val' => 'smsAutoReceive', 'typ' => 's', 'def' => null], // SMS 자동발송 수신여부 종류
            ['val' => 'phone', 'typ' => 's', 'def' => null], // 전화번호
            ['val' => 'extension', 'typ' => 's', 'def' => null], // 내선번호
            ['val' => 'cellPhone', 'typ' => 's', 'def' => null], // 휴대폰 번호
            ['val' => 'email', 'typ' => 's', 'def' => null], // 이메일
            ['val' => 'workPermissionFl', 'typ' => 's', 'def' => 'y'], // 개발권한 부여여부('y-개발소스관리 가능', 'n-개발소스관리 불가' )
            ['val' => 'debugPermissionFl', 'typ' => 's', 'def' => 'n'], // 디버그권한 부여여부('y-오류내용보고', 'n-오류내용보고 안함' )
            ['val' => 'permissionFl', 'typ' => 's', 'def' => 'l'], // 권한 종류('s-전체(슈퍼)관리자', 'l-제한관리자' )
            ['val' => 'permissionMenu', 'typ' => 's', 'def' => null], // 운영자 권한 설정 - 접근 권한
            ['val' => 'writeEnabledMenu', 'typ' => 's', 'def' => null], // 운영자 권한 설정 - 쓰기 권한
            ['val' => 'functionAuth', 'typ' => 's', 'def' => null], // 운영자 권한 설정 - 기능 권한
            ['val' => 'lastLoginAuthDt', 'typ' => 's', 'def' => null], // 최종로그인인증
            ['val' => 'lastLoginDt', 'typ' => 's', 'def' => null], // 최종로그인
            ['val' => 'lastLoginIp', 'typ' => 's', 'def' => null], // 최종로그인IP
            ['val' => 'changePasswordDt', 'typ' => 's', 'def' => null], // 비밀번호변경일
            ['val' => 'guidePasswordDt', 'typ' => 's', 'def' => null], // 비밀번호변경안내일
            ['val' => 'loginCnt', 'typ' => 'i', 'def' => 0], // 로그인횟수
            ['val' => 'loginLimit', 'typ' => 'j', 'def' => null], // 로그인제한
            ['val' => 'memo', 'typ' => 's', 'def' => null],
            ['val' => 'scmNo', 'typ' => 'i', 'def' => DEFAULT_CODE_SCMNO], //공급사
            ['val' => 'isDelete', 'typ' => 's', 'def' => 'n'], //삭제여부
            ['val' => 'dispImage', 'typ' => 's', 'def' => ''], //대표이미지
            ['val' => 'isSuper', 'typ' => 's', 'def' => 'n'], //슈퍼계정여부
            ['val' => 'isSmsAuth', 'typ' => 's', 'def' => 'n'], //휴대폰인증여부
            ['val' => 'isEmailAuth', 'typ' => 's', 'def' => 'n'], //이메일인증여부
            ['val' => 'isOrderSearchMultiGrid', 'typ' => 's', 'def' => 'n'], //주문 멀티검색여부
        ];
        // @formatter:on

        return $arrField;
    }

    /**
     * [관리자] es_managerCustomerService 필드 기본값
     *
     * @author yjwee
     * @return array manager 테이블 필드 정보
     */
    public static function tableManagerCustomerService()
    {
        // @formatter:off
        $arrField = [
            ['val' => 'managerSno', 'typ' => 'i', 'def' => null], // 공급사번호
            ['val' => 'csId', 'typ' => 's', 'def' => null], // 아이디
            ['val' => 'csPw', 'typ' => 's', 'def' => null], // 비밀번호
            ['val' => 'scmNo', 'typ' => 'i', 'def' => null], // 공급사번호
            ['val' => 'permissionFl', 'typ' => 's', 'def' => 'l'], // 권한종류
            ['val' => 'permissionMenu', 'typ' => 's', 'def' => '{}'], // 메뉴접근권한
            ['val' => 'functionAuth', 'typ' => 's', 'def' => '{}'], // 기능사용권한
            ['val' => 'expireDate', 'typ' => 's', 'def' => '0000-00-00'], // 만료일(기본 15일, 마지막인증 3일)
        ];
        // @formatter:on

        return $arrField;
    }

    /**
     * [관리자] managerMemo 필드 기본값
     *
     * @author yjwee
     * @return array manager 테이블 필드 정보
     */
    public static function tableManagerMemo()
    {
        // @formatter:off
        $arrField = [
            ['val' => 'managerSno', 'typ' => 'i', 'def' => null], // 관리자번호
            ['val' => 'scmNo', 'typ' => 'i', 'def' => null], // 공급사번호
            ['val' => 'contents', 'typ' => 's', 'def' => null], // 메모내용
            ['val' => 'viewAuth', 'typ' => 's', 'def' => 'self'], // 화면노출설정
            ['val' => 'isVisible', 'typ' => 's', 'def' => 'y'], // 펼치기설정
            ['val' => 'typeFl', 'typ' => 's', 'def' => 'self'], // 메모종류
        ];
        // @formatter:on

        return $arrField;
    }

    /**
     * [관리/설정] manage_goods_option 필드 기본값
     *
     * @author artherot
     * @return array manage_goods_option 테이블 필드 정보
     */
    public static function tableManageGoodsOption()
    {
        // @formatter:off
        $arrField = [
            ['val' => 'optionManageNm', 'typ' => 's', 'def' => null], // 옵션 관리 명
            ['val' => 'scmNo', 'typ' => 'i', 'def' => (string)Session::get('manager.scmNo')], // 공급사 고유 번호
            ['val' => 'optionDisplayFl', 'typ' => 's', 'def' => 's'], // 옵션 출력 방식
            ['val' => 'optionName', 'typ' => 's', 'def' => null], // 옵션명 (구분자 ^|^)
            ['val' => 'optionValue1', 'typ' => 's', 'def' => null], // 옵션값 1
            ['val' => 'optionValue2', 'typ' => 's', 'def' => null], // 옵션값 2
            ['val' => 'optionValue3', 'typ' => 's', 'def' => null], // 옵션값 3
            ['val' => 'optionValue4', 'typ' => 's', 'def' => null], // 옵션값 4
            ['val' => 'optionValue5', 'typ' => 's', 'def' => null], // 옵션값 5
        ];
        // @formatter:on

        return $arrField;
    }

    /**
     * [관리/설정] manage_goods_icon 필드 기본값
     *
     * @author artherot
     * @return array manage_goods_icon 테이블 필드 정보
     */
    public static function tableManageGoodsIcon()
    {
        // @formatter:off
        $arrField = [
            ['val' => 'iconCd', 'typ' => 's', 'def' => null], // 아이콘 코드
            ['val' => 'iconNm', 'typ' => 's', 'def' => null], // 아이콘 이름
            ['val' => 'iconImage', 'typ' => 's', 'def' => null], // 아이콘 이미지
            ['val' => 'iconPeriodFl', 'typ' => 's', 'def' => 'n'], // 아이콘 기간 사용여부
            ['val' => 'iconUseFl', 'typ' => 's', 'def' => 'y'], // 아이콘 사용여부
        ];
        // @formatter:on

        return $arrField;
    }

    /**
     * [관리/설정] manage_bank 필드 기본값
     *
     * @author artherot
     * @return array manage_bank 테이블 필드 정보
     */
    public static function tableManageBank()
    {
        // @formatter:off
        $arrField = [
            ['val' => 'bankName', 'typ' => 's', 'def' => null], // 은행명
            ['val' => 'accountNumber', 'typ' => 's', 'def' => null], // 계좌번호
            ['val' => 'depositor', 'typ' => 's', 'def' => null], // 예금주
            ['val' => 'useFl', 'typ' => 's', 'def' => 'y'], // 사용여부
            ['val' => 'defaultFl', 'typ' => 's', 'def' => 'n'], // 사용여부
            ['val' => 'managerNo', 'typ' => 's', 'def' => null], // 관리자 번호
        ];
        // @formatter:on

        return $arrField;
    }

    /**
     * [관리/설정] manage_delivery_company 필드 기본값
     *
     * @author artherot
     * @return array manage_delivery_company 테이블 필드 정보
     */
    public static function tableManageDeliveryCompany()
    {
        // @formatter:off
        $arrField = [
            ['val' => 'companyName', 'typ' => 's', 'def' => null], // 배송 업체명
            ['val' => 'companyKey', 'typ' => 's', 'def' => null], // 구분키값
            ['val' => 'traceUrl', 'typ' => 's', 'def' => null], // 배송 추적 URL
            ['val' => 'inicisCode', 'typ' => 's', 'def' => null], // 이니시스 코드 (에스크로용)
            ['val' => 'lguplusCode', 'typ' => 's', 'def' => null], // LG U+ 코드 (에스크로용)
            ['val' => 'naverPayCode', 'typ' => 's', 'def' => null], // 네이버페이 코드 (네이버페이용)
            ['val' => 'easypayCode', 'typ' => 's', 'def' => null], // 이지페이 코드 (에스크로용)
            ['val' => 'companySort', 'typ' => 'i', 'def' => 1], // 배송업체 순서
            ['val' => 'useFl', 'typ' => 's', 'def' => 'y'], // 사용여부
            ['val' => 'fixFl', 'typ' => 's', 'def' => 'n'], // 고정여부
            ['val' => 'deliveryFl', 'typ' => 's', 'def' => 'y'], // 배송방식택배여부
        ];
        // @formatter:on

        return $arrField;
    }

    /**
     * [주문] wish 필드 기본값
     *
     * @author artherot
     * @return array wish 테이블 필드 정보
     */
    public static function tableWish()
    {
        // @formatter:off
        $arrField = [
            ['val' => 'mallSno', 'typ' => 's', 'def' => '1'], // 멀티상점 일련번호
            ['val' => 'memNo', 'typ' => 'i', 'def' => '0'], // 회원 번호
            ['val' => 'goodsNo', 'typ' => 'i', 'def' => 's'], // 상품 번호
            ['val' => 'optionSno', 'typ' => 'i', 'def' => null], // 옵션 번호 (sno)
            ['val' => 'goodsCnt', 'typ' => 'i', 'def' => 1], // 상품 수량
            ['val' => 'addGoodsNo', 'typ' => 's', 'def' => null], // 추가 상품 sno 정보 (json_encode(sno))
            ['val' => 'addGoodsCnt', 'typ' => 's', 'def' => null], // 추가 상품 수량 정보 (json_encode(수량))
            ['val' => 'optionText', 'typ' => 's', 'def' => null], // 텍스트 옵션 정보 (json_encode(sno, 내용))
            ['val' => 'deliveryCollectFl', 'typ' => 's', 'def' => 'pre'], // 배송비 결제방법 (pre - 선불, later - 착불)
            ['val' => 'deliveryMethodFl', 'typ' => 's', 'def' => 'delivery'], // 배송방식
            ['val' => 'useBundleGoods', 'typ' => 'i', 'def' => null], // 묶음주문 사용여부 (스킨패치 여부확인)
        ];
        // @formatter:on

        return $arrField;
    }

    /**
     * [프로모션] cartRemind 필드 기본값
     *
     * @author su
     * @return array cartRemind 테이블 필드 정보
     */
    public static function tableCartRemind()
    {
        // @formatter:off
        $arrField = [
            ['val' => 'cartRemindNo', 'typ' => 'i', 'def' => ''], // 장바구니 알림 고유번호
            ['val' => 'cartRemindNm', 'typ' => 's', 'def' => null], // 장바구니 알림 명
            ['val' => 'cartRemindType', 'typ' => 's', 'def' => 'manual'], // 장바구니 알림 타입(manual) - 수동, 자동(auto)
            ['val' => 'cartRemindPeriod', 'typ' => 'i', 'def' => ''], // 장바구니 알림 대상
            ['val' => 'cartRemindPeriodStart', 'typ' => 's', 'def' => null], // 장바구니 알림 대상 직접입력 시작
            ['val' => 'cartRemindPeriodEnd', 'typ' => 's', 'def' => null], // 장바구니 알림 대상 직접입력 끝
            ['val' => 'cartRemindGoodsSellFl', 'typ' => 's', 'def' => 'n'], // 장바구니 알림 판매안함 제외
            ['val' => 'cartRemindGoodsDisplayFl', 'typ' => 's', 'def' => 'n'], // 장바구니 알림 노출안함 제외
            ['val' => 'cartRemindGoodsSoldOutFl', 'typ' => 's', 'def' => 'n'], // 장바구니 알림 품절 제외
            ['val' => 'cartRemindGoodsSellFl', 'typ' => 's', 'def' => 'n'], // 장바구니 알림 판매안함 제외
            ['val' => 'cartRemindGoodsStock', 'typ' => 'i', 'def' => ''], // 장바구니 알림 재고량 제외
            ['val' => 'cartRemindGoodsStockSel', 'typ' => 's', 'def' => null], // 장바구니 알림 재고량 - 이상(up), 이하(down)
            ['val' => 'cartRemindApplyMemberGroup', 'typ' => 's', 'def' => null], // 장바구니 알림 발송 회원등급-구분자로 회원등급고유번호
            ['val' => 'cartRemindCoupon', 'typ' => 'i', 'def' => ''], // 장바구니 알림 제공 쿠폰고유번호
            ['val' => 'cartRemindAutoUse', 'typ' => 's', 'def' => 'y'], // 장바구니 알림 자동발송시 사용 여부 - 발송중지(n), 발송시작(y)
            ['val' => 'cartRemindAutoSendTime', 'typ' => 'i', 'def' => '10'], // 장바구니 알림 자동발송 시 발송 시간
            ['val' => 'cartRemindSendType', 'typ' => 's', 'def' => 'lms'], // 장바구니 알림 전송 형식 - sms, lms
            ['val' => 'cartRemindSendMessage', 'typ' => 's', 'def' => '(광고)[{rc_mallNm}] {rc_memNm}님의 장바구니에 상품이 담겨 있습니다. 아래 장바구니 바로가기를 통해 바로 확인하실 수 있습니다. ▶ 장바구니 바로가기: {rc_cartRemindLink}'], // 장바구니 알림 전송 내용
            ['val' => 'cartRemindSendUrl', 'typ' => 's', 'def' => ''], // 장바구니 알림 바로가기 링크
            ['val' => 'cartRemindInsertAdminId', 'typ' => 's', 'def' => null], // 장바구니 알림 등록자 아이디
            ['val' => 'managerNo', 'typ' => 'i', 'def' => 0], // 장바구니 알림 등록자 키
        ];
        // @formatter:on

        return $arrField;
    }

    /**
     * [수기주문] cart_write 필드 기본값
     * es_cart와 동일구조
     *
     * @see    반드시 비지니스 로직을 같이 사용하기때문에 테이블 구조 동일하게 사용해야 함
     * @static
     * @return array
     * @author Jong-tae Ahn <qnibus@godo.co.kr>
     */
    public static function tableCartWrite()
    {
        return self::tableCart();
    }

    /**
     * [주문] cart 필드 기본값
     *
     * @author artherot
     * @return array cart 테이블 필드 정보
     */
    public static function tableCart()
    {
        // @formatter:off
        $arrField = [
            ['val' => 'siteKey', 'typ' => 's', 'def' => null], // 사이트 키
            ['val' => 'mallSno', 'typ' => 's', 'def' => '1'], // 멀티상점 일련번호
            ['val' => 'directCart', 'typ' => 's', 'def' => 'n'], // 바로구매 여부
            ['val' => 'memNo', 'typ' => 'i', 'def' => '0'], // 회원 번호
            ['val' => 'goodsNo', 'typ' => 'i', 'def' => 's'], // 상품 번호
            ['val' => 'optionSno', 'typ' => 'i', 'def' => null], // 옵션 번호 (sno)
            ['val' => 'goodsCnt', 'typ' => 'i', 'def' => 1], // 상품 수량
            ['val' => 'addGoodsNo', 'typ' => 's', 'def' => null], // 추가 상품 sno 정보 (json_encode(sno))
            ['val' => 'addGoodsCnt', 'typ' => 's', 'def' => null], // 추가 상품 수량 정보 (json_encode(수량))
            ['val' => 'optionText', 'typ' => 's', 'def' => null], // 텍스트 옵션 정보 (json_encode(sno, 내용))
            ['val' => 'deliveryCollectFl', 'typ' => 's', 'def' => 'pre'], // 배송비 결제방법 (pre - 선불, later - 착불)
            ['val' => 'deliveryMethodFl', 'typ' => 's', 'def' => 'delivery'], // 배송방식
            ['val' => 'memberCouponNo', 'typ' => 's', 'def' => ''], // 회원 쿠폰 번호 (상품 쿠폰) INI_DIVISION 구분자로 생성된 쿠폰 STRING
            ['val' => 'tmpOrderNo', 'typ' => 's', 'def' => null], // 주문번호 (주문 후 삭제를 위한 키)
            ['val' => 'useBundleGoods', 'typ' => 'i', 'def' => null], // 묶음주문 사용여부 (스킨패치 여부확인)
            ['val' => 'linkMainTheme', 'typ' => 's', 'def' => null], // 메인 상품 진열에서 장바구니 담은 정보
        ];
        // @formatter:on

        return $arrField;
    }

    /**
     * [주문] order 필드 기본값
     *
     * @author artherot
     * @return array order 테이블 필드 정보
     */
    public static function tableOrder()
    {
        // @formatter:off
        $arrField = [
            ['val' => 'orderNo', 'typ' => 's', 'def' => null], // 주문번호
            ['val' => 'apiOrderNo', 'typ' => 's', 'def' => null], // 외부채널주문번호
            ['val' => 'mallSno', 'typ' => 's', 'def' => '1'], // 멀티상점 일련번호
            ['val' => 'memNo', 'typ' => 'i', 'def' => null], // 회원 번호
            ['val' => 'orderStatus', 'typ' => 's', 'def' => 'o1'], // 주문 상태 (내부 쿼리용으로 사용하기 위해 추가됨 실제 orderGoods의 orderStatus를 사용)
            ['val' => 'orderIp', 'typ' => 's', 'def' => null], // 주문자 IP
            ['val' => 'orderChannelFl', 'typ' => 's', 'def' => null], // 주문 채널
            ['val' => 'orderTypeFl', 'typ' => 's', 'def' => 'pc'], // 주문유형 (PC,모바일,수기) (pc - 일반(PC) 주문, mobile - 모바일샵 주문, write - 수기주문)
            ['val' => 'appOs', 'typ' => 's', 'def' => null], // 앱 주문시 휴대폰 OS
            ['val' => 'pushCode', 'typ' => 's', 'def' => null], // 앱 주문시 푸시 코드
            ['val' => 'statisticsAppOrderCntFl', 'typ' => 's', 'def' => 'n'], // 주문건수 앱 통계 처리 상태
            ['val' => 'orderEmail', 'typ' => 's', 'def' => null], // 주문자 e-mail
            ['val' => 'orderGoodsNm', 'typ' => 's', 'def' => null], // 주문 상품명
            ['val' => 'orderGoodsNmStandard', 'typ' => 's', 'def' => null], // 주문 상품명 기준몰
            ['val' => 'orderGoodsCnt', 'typ' => 'i', 'def' => null], // 주문 상품 갯수
            ['val' => 'settlePrice', 'typ' => 'd', 'def' => '0.00'],// 총 주문 금액
            ['val' => 'overseasSettleCurrency', 'typ' => 's', 'def' => null], // 해외PG 승인 통화 코드 (USD|CNY|KRW...)
            ['val' => 'overseasSettlePrice', 'typ' => 'd', 'def' => '0.00'], // 해외PG 승인금액 (환율변환 적용)
            ['val' => 'taxSupplyPrice', 'typ' => 'd', 'def' => '0.00'],// 총 과세 금액
            ['val' => 'taxVatPrice', 'typ' => 'd', 'def' => '0.00'],// 총 부과세 금액
            ['val' => 'taxFreePrice', 'typ' => 'd', 'def' => '0.00'],// 총 면세 금액
            ['val' => 'realTaxSupplyPrice', 'typ' => 'd', 'def' => '0.00'],// 총 배송비 과세 금액
            ['val' => 'realTaxVatPrice', 'typ' => 'd', 'def' => '0.00'],// 총 부가세 금액
            ['val' => 'realTaxFreePrice', 'typ' => 'd', 'def' => '0.00'],// 총 면세 금액
            ['val' => 'useMileage', 'typ' => 'd', 'def' => '0.00'], // 사용된 총 마일리지
            ['val' => 'useDeposit', 'typ' => 'd', 'def' => '0.00'], // 사용된 총 예치금
            ['val' => 'totalGoodsPrice', 'typ' => 'd', 'def' => '0.00'], // 총 상품 금액
            ['val' => 'totalDeliveryCharge', 'typ' => 'd', 'def' => '0.00'], // 총 배송비
            ['val' => 'totalDeliveryInsuranceFee', 'typ' => 'd', 'def' => '0.00'], // EMS 해외 총 보험료
            ['val' => 'totalGoodsDcPrice', 'typ' => 'd', 'def' => '0.00'], // 총 상품 할인 금액
            ['val' => 'totalMemberDcPrice', 'typ' => 'd', 'def' => '0.00'], // 총 회원 할인 금액
            ['val' => 'totalMemberOverlapDcPrice', 'typ' => 'd', 'def' => '0.00'], // 총 회원 중복 할인 금액
            ['val' => 'totalMemberDeliveryDcPrice', 'typ' => 'd', 'def' => '0.00'], // 회원 배송비 무료 금액
            ['val' => 'totalCouponGoodsDcPrice', 'typ' => 'd', 'def' => '0.00'], // 총 상품쿠폰 할인 금액
            ['val' => 'totalCouponOrderDcPrice', 'typ' => 'd', 'def' => '0.00'],// 주문쿠폰 할인 금액
            ['val' => 'totalCouponDeliveryDcPrice', 'typ' => 'd', 'def' => '0.00'],// 배송쿠폰 할인 금액
            ['val' => 'totalMyappDcPrice', 'typ' => 'd', 'def' => '0.00'],// 마이앱 할인 금액
            ['val' => 'totalMileage', 'typ' => 'd', 'def' => '0.00'], // 총 적립될 마일리지
            ['val' => 'totalGoodsMileage', 'typ' => 'd', 'def' => '0.00'], // 총 적립될 상품 마일리지
            ['val' => 'totalMemberMileage', 'typ' => 'd', 'def' => '0.00'], // 총 적립될 회원 마일리지
            ['val' => 'totalCouponGoodsMileage', 'typ' => 'd', 'def' => '0.00'], // 총 적립될 상품쿠폰 마일리지
            ['val' => 'totalCouponOrderMileage', 'typ' => 'd', 'def' => '0.00'], // 총 적립될 주문쿠폰 마일리지
            ['val' => 'totalEnuriDcPrice', 'typ' => 'd', 'def' => '0.00'], // 총 운영자추가할인
            ['val' => 'totalDeliveryWeight', 'typ' => 'd', 'def' => '0.00'], // 총 적립될 주문쿠폰 마일리지
            ['val' => 'mileageGiveExclude', 'typ' => 's', 'def' => 'y'], // 마일리지 지급 예외 (y - 지급, n - 지급안함)
            ['val' => 'firstSaleFl', 'typ' => 's', 'def' => 'n'], // 첫구매 확인
            ['val' => 'firstCouponFl', 'typ' => 's', 'def' => 'n'], // 첫구매 쿠폰 지급 확인
            ['val' => 'eventCouponFl', 'typ' => 's', 'def' => 'n'], // 구매 쿠폰 지급 확인
            ['val' => 'sendMailSmsFl', 'typ' => 's', 'def' => null], // 메일 전송/SMS 전송 여부
            ['val' => 'settleKind', 'typ' => 's', 'def' => null], // 결제 방법
            ['val' => 'bankAccount', 'typ' => 's', 'def' => null], // 무통장 입금 은행
            ['val' => 'bankSender', 'typ' => 's', 'def' => null], // 무통장 입금자
            ['val' => 'receiptFl', 'typ' => 's', 'def' => 'n'], // 영수증 신청여부 (n - 신청안함, r - 현금영수증, t - 세금계산서)
            ['val' => 'depositPolicy', 'typ' => 's', 'def' => ''], // 주문당시의 예치금정책
            ['val' => 'mileagePolicy', 'typ' => 's', 'def' => ''], // 주문당시의 마일리지정책
            ['val' => 'statusPolicy', 'typ' => 's', 'def' => ''], // 주문당시의 주문상태정책
            ['val' => 'memberPolicy', 'typ' => 's', 'def' => ''], // 주문당시의 회원등급별 할인정책
            ['val' => 'couponPolicy', 'typ' => 's', 'def' => ''], // 주문당시의 쿠폰 기본정책
            ['val' => 'currencyPolicy', 'typ' => 's', 'def' => ''], // 주문당시의 상점 통화 기본정책
            ['val' => 'exchangeRatePolicy', 'typ' => 's', 'def' => ''], // 주문당시의 환율비율 기본정책
            ['val' => 'myappPolicy', 'typ' => 's', 'def' => ''], // 주문당시의 마이앱 기본정책
            ['val' => 'userRequestMemo', 'typ' => 's', 'def' => null], // 고객요청사항
            ['val' => 'userConsultMemo', 'typ' => 's', 'def' => null], // 고객상담메모
            ['val' => 'adminMemo', 'typ' => 's', 'def' => null], // 관리자 메모
            ['val' => 'orderPGLog', 'typ' => 's', 'def' => ''], // 주문 PG 로그
            ['val' => 'orderDeliveryLog', 'typ' => 's', 'def' => ''], // 주문 배송 로그
            ['val' => 'orderAdminLog', 'typ' => 's', 'def' => ''], // 주문 관리자 로그
            ['val' => 'pgName', 'typ' => 's', 'def' => null], // PG명
            ['val' => 'pgResultCode', 'typ' => 's', 'def' => null], // PG 결과코드
            ['val' => 'pgTid', 'typ' => 's', 'def' => null], // PG 거래번호
            ['val' => 'pgAppNo', 'typ' => 's', 'def' => null], // PG 승인번호
            ['val' => 'pgAppDt', 'typ' => 's', 'def' => null], // PG 승인일자
            ['val' => 'pgCardCd', 'typ' => 's', 'def' => null], // PG 승인카드코드
            ['val' => 'pgSettleNm', 'typ' => 's', 'def' => null], // PG 결과1 - 카드명, 가상계좌 입금 은행, 결제 은행, 휴대폰 사업자
            ['val' => 'pgSettleCd', 'typ' => 's', 'def' => null], // PG 결과2 - 할부 기간(1-무이자), 가상계좌 입금 만료일, 결제 은행 계좌, 휴대폰 번호
            ['val' => 'pgFailReason', 'typ' => 's', 'def' => null], // PG 실패 이유
            ['val' => 'pgCancelFl', 'typ' => 's', 'def' => 'n'], // PG 취소여부 (y - 취소완료, p - 부분취소, n - 승인상태)
            ['val' => 'pgRealTaxSupplyPrice', 'typ' => 'd', 'def' => '0.00'], // 실제 총 PG 과세금액
            ['val' => 'pgRealTaxVatPrice', 'typ' => 'd', 'def' => '0.00'], // 실제 총 PG 부가세
            ['val' => 'pgRealTaxFreePrice', 'typ' => 'd', 'def' => '0.00'], // 실제 총 PG 면세금액
            ['val' => 'escrowSendNo', 'typ' => 's', 'def' => null], // 에스크로 전문번호 (올더게이트용)
            ['val' => 'escrowDeliveryFl', 'typ' => 's', 'def' => 'n'], // 에스크로 배송등록 여부
            ['val' => 'escrowDeliveryDt', 'typ' => 's', 'def' => null], // 에스크로 배송등록 확인일시
            ['val' => 'escrowDeliveryCd', 'typ' => 's', 'def' => null], // 에스크로 배송업체
            ['val' => 'escrowInvoiceNo', 'typ' => 's', 'def' => null], // 에스크로 송장번호
            ['val' => 'escrowConfirmFl', 'typ' => 's', 'def' => null], // 에스크로 구매 확인 (accept, reject)
            ['val' => 'escrowDenyFl', 'typ' => 's', 'def' => 'n'], // 에스크로 거절 확인 (y, n)
            ['val' => 'fintechData', 'typ' => 's', 'def' => null], // 간편결제 추가데이터
            ['val' => 'checkoutData', 'typ' => 's', 'def' => null], // 간편구매 추가데이터
            ['val' => 'checksumData', 'typ' => 's', 'def' => null], // 주문 데이터 checksum 코드 - 결제시도 줄이기 위한 용도
            ['val' => 'addField', 'typ' => 'j', 'def' => null], // 주문 추가 필드 정보 json
            ['val' => 'paymentDt', 'typ' => 's', 'def' => null], // 입금 일자
            ['val' => 'multiShippingFl', 'typ' => 's', 'def' => 'n'], // 복수배송지사용여부
            ['val' => 'trackingKey', 'typ' => 's', 'def' => null], // 페이코쇼핑 트래킹키
            ['val' => 'pgChargeBack', 'typ' => 's', 'def' => 'n'], // 차지백여부
            ['val' => 'fbPixelKey', 'typ' => 's', 'def' => null], // 페이스북 픽셀 쿠키값
        ];
        // @formatter:on

        return $arrField;
    }

    /**
     * [주문] orderGoods 필드 기본값
     *
     * @author artherot
     * @return array order_goods 테이블 필드 정보
     */
    public static function tableOrderGoods()
    {
        // @formatter:off
        $arrField = [
            ['val' => 'orderNo', 'typ' => 's', 'def' => null], // 주문번호
            ['val' => 'mallSno', 'typ' => 'i', 'def' => null], // 상점 고유번호
            ['val' => 'apiOrderGoodsNo', 'typ' => 's', 'def' => null], // 외부채널품목번호
            ['val' => 'orderCd', 'typ' => 'i', 'def' => '1'], // 주문 코드(순서)
            ['val' => 'orderGroupCd', 'typ' => 'i', 'def' => null], // 수량별 부분취소시 그룹 코드 (정산에서 수량분할된 환불접수 주문상품 추적용)
            ['val' => 'userHandleSno', 'typ' => 'i', 'def' => '0'], // 사용자 처리 코드 (SNO)
            ['val' => 'handleSno', 'typ' => 'i', 'def' => '0'], // 환불/반품/교환 처리 SNO
            ['val' => 'eventSno', 'typ' => 'i', 'def' => '0'], // 기획전 SNO
            ['val' => 'orderStatus', 'typ' => 's', 'def' => 'o1'], // 주문 상태
            ['val' => 'orderDeliverySno', 'typ' => 'i', 'def' => null], // 주문배송테이블(orderDelivery) sno
            ['val' => 'invoiceCompanySno', 'typ' => 's', 'def' => null], // 배송 업체 sno
            ['val' => 'invoiceNo', 'typ' => 's', 'def' => null], // 송장 번호
            ['val' => 'scmNo', 'typ' => 'i', 'def' => DEFAULT_CODE_SCMNO], // 공급사 고유 번호
            ['val' => 'purchaseNo', 'typ' => 'i', 'def' => null], // 매입처코드
            ['val' => 'commission', 'typ' => 'd', 'def' => '0.00'], // 공급사 수수료율
            ['val' => 'scmAdjustNo', 'typ' => 'i', 'def' => '0'], // 공급사 정산 고유번호
            ['val' => 'scmAdjustAfterNo', 'typ' => 'i', 'def' => '0'], // 공급사 정산 후 환불 고유번호
            ['val' => 'goodsType', 'typ' => 's', 'def' => 'goods'], // 주문한 상품 종류 (상품, 추가상품)
            ['val' => 'timeSaleFl', 'typ' => 's', 'def' => 'n'], // 타임세일 적용여부
            ['val' => 'parentMustFl', 'typ' => 's', 'def' => 'n'], // 추가상품 종속성 여부
            ['val' => 'parentGoodsNo', 'typ' => 's', 'def' => '0'], // 추가상품의 부모상품
            ['val' => 'goodsNo', 'typ' => 'i', 'def' => null], // 상품 번호
            ['val' => 'goodsCd', 'typ' => 's', 'def' => null], // 상품 코드
            ['val' => 'goodsModelNo', 'typ' => 's', 'def' => null], // 모델명
            ['val' => 'goodsNm', 'typ' => 's', 'def' => null], // 상품명
            ['val' => 'goodsNmStandard', 'typ' => 's', 'def' => null], //기준몰 상품명
            ['val' => 'goodsWeight', 'typ' => 'd', 'def' => '0'], // 상품 무게
            ['val' => 'goodsVolume', 'typ' => 'd', 'def' => '0'], // 상품 용량
            ['val' => 'goodsCnt', 'typ' => 'i', 'def' => '1'], // 상품 수량
            ['val' => 'goodsPrice', 'typ' => 'd', 'def' => '0.00'], // 상품 가격
            ['val' => 'taxSupplyGoodsPrice', 'typ' => 'd', 'def' => '0.00'], // 복합과세용 과세
            ['val' => 'taxVatGoodsPrice', 'typ' => 'd', 'def' => '0.00'], // 복합과세용 부가세
            ['val' => 'taxFreeGoodsPrice', 'typ' => 'd', 'def' => '0.00'], // 복합과세용 면세
            ['val' => 'realTaxSupplyGoodsPrice', 'typ' => 'd', 'def' => '0.00'], // 실제 남아있는 복합과세용 과세
            ['val' => 'realTaxVatGoodsPrice', 'typ' => 'd', 'def' => '0.00'], // 실제 남아있는 복합과세용 부가세
            ['val' => 'realTaxFreeGoodsPrice', 'typ' => 'd', 'def' => '0.00'], // 실제 남아있는 복합과세용 면세
            ['val' => 'divisionUseDeposit', 'typ' => 'd', 'def' => '0.00'], // 주문할인 금액의 안분된 예치금
            ['val' => 'divisionUseMileage', 'typ' => 'd', 'def' => '0.00'], // 주문할인 금액의 안분된 마일리지
            ['val' => 'divisionGoodsDeliveryUseDeposit', 'typ' => 'd', 'def' => '0.00'], // 주문할인 금액의 안분된 배송비 예치금
            ['val' => 'divisionGoodsDeliveryUseMileage', 'typ' => 'd', 'def' => '0.00'], // 주문할인 금액의 안분된 배송비 마일리지
            ['val' => 'divisionCouponOrderDcPrice', 'typ' => 'd', 'def' => '0.00'], // 주문할인 금액의 안분된 주문쿠폰할인
            ['val' => 'divisionCouponOrderMileage', 'typ' => 'd', 'def' => '0.00'], // 주문적립 금액의 안분된 주문쿠폰적립
            ['val' => 'addGoodsCnt', 'typ' => 'i', 'def' => '0'], // 추가 상품 갯수
            ['val' => 'addGoodsPrice', 'typ' => 'd', 'def' => '0.00'], // 추가 상품 금액
            ['val' => 'optionPrice', 'typ' => 'd', 'def' => '0.00'], // 추가 옵션 금액
            ['val' => 'optionCostPrice', 'typ' => 'd', 'def' => '0.00'], // 추가 옵션 매입 금액
            ['val' => 'optionTextPrice', 'typ' => 'd', 'def' => '0.00'], // 텍스트 옵션 금액
            ['val' => 'fixedPrice', 'typ' => 'd', 'def' => '0.00'], // 정가
            ['val' => 'costPrice', 'typ' => 'd', 'def' => '0.00'], // 매입가
            ['val' => 'goodsDcPrice', 'typ' => 'i', 'def' => '0'], // 상품 할인 금액
            ['val' => 'memberDcPrice', 'typ' => 'i', 'def' => '0'], // 회원 할인 금액 (추가상품 제외)
            ['val' => 'memberOverlapDcPrice', 'typ' => 'i', 'def' => '0'], // 회원 중복 할인 금액 (추가상품 제외)
            ['val' => 'couponGoodsDcPrice', 'typ' => 'i', 'def' => '0'], // 상품 쿠폰 할인 금액 (추가상품 제외)
            ['val' => 'timeSalePrice', 'typ' => 'i', 'def' => '0'], // 타임세일 금액
            ['val' => 'myappDcPrice', 'typ' => 'd', 'def' => '0.00'], // 마이앱 할인 금액 (추가상품 적용)
            ['val' => 'goodsDeliveryCollectPrice', 'typ' => 'i', 'def' => '0'], // 상품별 착불/선불시 발생된 배송비
            ['val' => 'goodsMileage', 'typ' => 'd', 'def' => '0.00'], // 상품 적립마일리지 (추가상품 제외)
            ['val' => 'memberMileage', 'typ' => 'd', 'def' => '0.00'], // 회원 적립마일리지 (추가상품 제외)
            ['val' => 'couponGoodsMileage', 'typ' => 'd', 'def' => '0.00'], // 쿠폰 적립마일리지 (추가상품 제외)
            ['val' => 'goodsDeliveryCollectFl', 'typ' => 's', 'def' => 'pre'], // 상품별배송비 결제방법 (pre - 선불, later - 착불)
            ['val' => 'minusDepositFl', 'typ' => 's', 'def' => 'n'], // 예치금 차감여부
            ['val' => 'minusRestoreDepositFl', 'typ' => 's', 'def' => 'n'], // 예치금 차감 복원여부
            ['val' => 'minusMileageFl', 'typ' => 's', 'def' => 'n'], // 마일리지 차감여부
            ['val' => 'minusRestoreMileageFl', 'typ' => 's', 'def' => 'n'], // 마일리지 차감 복원여부
            ['val' => 'plusMileageFl', 'typ' => 's', 'def' => 'n'], // 마일리지 지급여부
            ['val' => 'plusRestoreMileageFl', 'typ' => 's', 'def' => 'n'], // 마일리지 지급 복원여부
            ['val' => 'couponMileageFl', 'typ' => 's', 'def' => 'n'], // 쿠폰마일리지 지급 여부
            ['val' => 'minusStockFl', 'typ' => 's', 'def' => 'n'], // 차감 여부 (재고)
            ['val' => 'minusRestoreStockFl', 'typ' => 's', 'def' => 'n'], // 복원 여부 (재고)
            ['val' => 'optionSno', 'typ' => 'i', 'def' => null], // 옵션 정보
            ['val' => 'optionInfo', 'typ' => 's', 'def' => null], // 옵션 정보
            ['val' => 'optionTextInfo', 'typ' => 's', 'def' => null], // 텍스트 옵션 정보
            ['val' => 'goodsTaxInfo', 'typ' => 's', 'def' => 't' . STR_DIVISION . '10'], // 상품 과세/비과세 정보
            ['val' => 'cateCd', 'typ' => 's', 'def' => null], // 카테고리 코드
            ['val' => 'cateAllCd', 'typ' => 's', 'def' => null], // 상품에 연결된 전체 카테고리 코드
            ['val' => 'brandCd', 'typ' => 's', 'def' => null], // 브랜드 코드
            ['val' => 'makerNm', 'typ' => 's', 'def' => null], // 제조사
            ['val' => 'originNm', 'typ' => 's', 'def' => null], // 원산지
            ['val' => 'hscode', 'typ' => 's', 'def' => null], // hs코드
            ['val' => 'deliveryLog', 'typ' => 's', 'def' => null], // 배송 관련 로그
            ['val' => 'cancelDt', 'typ' => 's', 'def' => null], // 취소 일자
            ['val' => 'paymentDt', 'typ' => 's', 'def' => null], // 입금 일자
            ['val' => 'invoiceDt', 'typ' => 's', 'def' => null], // 송장번호 입력일자
            ['val' => 'deliveryDt', 'typ' => 's', 'def' => null], // 배송 일자
            ['val' => 'deliveryCompleteDt', 'typ' => 's', 'def' => null], // 배송완료 일자
            ['val' => 'finishDt', 'typ' => 's', 'def' => null],// 구매확정 일자
            ['val' => 'mileageGiveDt', 'typ' => 's', 'def' => null],// 마일리지 지급 유예 일자
            ['val' => 'checkoutData', 'typ' => 's', 'def' => null],// 간편결제 추가데이터
            ['val' => 'statisticsOrderFl', 'typ' => 's', 'def' => null],// 주문/매출 통계 처리 상태
            ['val' => 'statisticsGoodsFl', 'typ' => 's', 'def' => null],// 상품 통계 처리 상태
            ['val' => 'sendSmsFl', 'typ' => 'j', 'def' => null],// 문자발송여부
            ['val' => 'deliveryMethodFl', 'typ' => 's', 'def' => 'delivery'], // 배송방식
            ['val' => 'enuri', 'typ' => 'i', 'def' => null], // 에누리
            ['val' => 'goodsDiscountInfo', 'typ' => 's', 'def' => null], // 상품할인 정보
            ['val' => 'goodsMileageAddInfo', 'typ' => 's', 'def' => null], // 상품적립 정보
            ['val' => 'inflow', 'typ' => 's', 'def' => null], // 외부 인입 정보
            ['val' => 'linkMainTheme', 'typ' => 's', 'def' => null], // 메인 상품 진열에서 장바구니 담은 정보
            ['val' => 'visitAddress', 'typ' => 's', 'def' => null], // 방문수령지 주소
        ];
        // @formatter:on

        return $arrField;
    }

    /**
     * [주문] orderAddGoods 필드 기본값
     *
     * @author artherot
     * @return array order_goods 테이블 필드 정보
     */
    public static function tableOrderAddGoods()
    {
        // @formatter:off
        $arrField = [
            ['val' => 'orderNo', 'typ' => 's', 'def' => null], // 주문번호
            ['val' => 'orderCd', 'typ' => 's', 'def' => null], // 주문상품코드
            ['val' => 'scmNo', 'typ' => 'i', 'def' => DEFAULT_CODE_SCMNO], // 공급사 고유 번호
            ['val' => 'commission', 'typ' => 'd', 'def' => '0.00'], // 공급사 수수료율
            ['val' => 'scmAdjustNo', 'typ' => 'i', 'def' => '0'], // 공급사 정산 고유번호
            ['val' => 'scmAdjustAfterNo', 'typ' => 'i', 'def' => '0'], // 공급사 정산 후 환불 고유번호
            ['val' => 'addGoodsNo', 'typ' => 'i', 'def' => null], // 상품 번호
            ['val' => 'goodsCd', 'typ' => 's', 'def' => null], // 상품 코드
            ['val' => 'goodsModelNo', 'typ' => 's', 'def' => null], // 모델명
            ['val' => 'goodsNm', 'typ' => 's', 'def' => null], // 상품명
            ['val' => 'goodsCnt', 'typ' => 'i', 'def' => '1'], // 상품 수량
            ['val' => 'goodsPrice', 'typ' => 'i', 'def' => '0'], // 상품 가격
            ['val' => 'taxSupplyAddGoodsPrice', 'typ' => 'd', 'def' => '0.00'], // 복합과세용 과세
            ['val' => 'taxVatAddGoodsPrice', 'typ' => 'd', 'def' => '0.00'], // 복합과세용 부가세
            ['val' => 'taxFreeAddGoodsPrice', 'typ' => 'd', 'def' => '0.00'], // 복합과세용 면세
            ['val' => 'realTaxSupplyAddGoodsPrice', 'typ' => 'd', 'def' => '0.00'], // 실제 남아있는 복합과세용 과세
            ['val' => 'realTaxVatAddGoodsPrice', 'typ' => 'd', 'def' => '0.00'], // 실제 남아있는 복합과세용 부가세
            ['val' => 'realTaxFreeAddGoodsPrice', 'typ' => 'd', 'def' => '0.00'], // 실제 남아있는 복합과세용 면세
            ['val' => 'divisionAddUseDeposit', 'typ' => 'd', 'def' => '0.00'], // 주문할인 금액의 안분된 예치금
            ['val' => 'divisionAddUseMileage', 'typ' => 'd', 'def' => '0.00'], // 주문할인 금액의 안분된 마일리지
            ['val' => 'divisionAddCouponOrderDcPrice', 'typ' => 'd', 'def' => '0.00'], // 주문할인 금액의 안분된 주문쿠폰할인
            ['val' => 'divisionAddCouponOrderMileage', 'typ' => 'd', 'def' => '0.00'], // 주문적립 금액의 안분된 주문쿠폰적립
            ['val' => 'addMemberDcPrice', 'typ' => 'i', 'def' => '0'], // 추가상품 회원 할인 금액
            ['val' => 'addMemberOverlapDcPrice', 'typ' => 'i', 'def' => '0'], // 추가상품 회원 중복 할인 금액
            ['val' => 'addCouponGoodsDcPrice', 'typ' => 'i', 'def' => '0'], // 추가상품 쿠폰 할인 금액
            ['val' => 'addGoodsMileage', 'typ' => 'd', 'def' => '0.00'], // 추가상품 적립마일리지
            ['val' => 'addMemberMileage', 'typ' => 'd', 'def' => '0.00'], // 추가상품 회원 적립마일리지
            ['val' => 'addCouponGoodsMileage', 'typ' => 'd', 'def' => '0.00'], // 추가상품 쿠폰 적립마일리지
            ['val' => 'goodsTaxInfo', 'typ' => 's', 'def' => 't' . STR_DIVISION . '10'], // 상품 부가세 정보
            ['val' => 'optionNm', 'typ' => 's', 'def' => null], // 옵션 정보
            ['val' => 'brandCd', 'typ' => 's', 'def' => null], // 브랜드 코드
            ['val' => 'makerNm', 'typ' => 's', 'def' => null], // 제조사
            ['val' => 'minusStockFl', 'typ' => 's', 'def' => 'n'], // 차감 여부 (재고)
            ['val' => 'minusRestoreStockFl', 'typ' => 's', 'def' => 'n'], // 복원 여부 (재고)
            ['val' => 'paymentDt', 'typ' => 's', 'def' => null], // 완료 일자
        ];
        // @formatter:on

        return $arrField;
    }

    /**
     * [주문] orderBankdaMemo 필드 기본값
     *
     * @author tomi
     * @return array tableOrderBankdaMemo 테이블 필드 정보
     */
    public static function tableOrderBankdaMemo()
    {
        // @formatter:off
        $arrField = [
            ['val' => 'bankdaNo', 'typ' => 's', 'def' => null, 'name' => '뱅크다일련번호'], // 뱅크다일련번호
            ['val' => 'managerNo', 'typ' => 's', 'def' => null, 'name' => '관리자번호'], // 관리자번호
            ['val' => 'managerId', 'typ' => 's', 'def' => null, 'name' => '관리자아이디'], // 관리자번호
            ['val' => 'adminMemo', 'typ' => 's', 'def' => null, 'name' => '입금내역메모'], // 입금내역메모
            ['val' => 'modDt', 'typ' => 's', 'def' => null, 'name' => '수정일'], // 수정일
            ['val' => 'regDt', 'typ' => 's', 'def' => null, 'name' => '등록일'], // 등록일
        ];
        // @formatter:on

        return $arrField;
    }

    /**
     * [주문] orderInfo 필드 기본값
     *
     * @author artherot
     * @return array order_info 테이블 필드 정보
     */
    public static function tableOrderInfo()
    {
        // @formatter:off
        $arrField = [
            ['val' => 'orderNo', 'typ' => 's', 'def' => null], // 주문번호
            ['val' => 'orderName', 'typ' => 's', 'def' => null], // 주문자 이름
            ['val' => 'orderEmail', 'typ' => 's', 'def' => null], // 주문자 e-mail
            ['val' => 'orderPhonePrefixCode', 'typ' => 's', 'def' => 'kr'], // 주문자 전화번호 국가코드
            ['val' => 'orderPhonePrefix', 'typ' => 's', 'def' => '82'], // 주문자 전화번호 국가번호
            ['val' => 'orderPhone', 'typ' => 's', 'def' => null], // 주문자 전화번호
            ['val' => 'orderCellPhonePrefixCode', 'typ' => 's', 'def' => 'kr'], // 주문자 전화번호 국가코드
            ['val' => 'orderCellPhonePrefix', 'typ' => 's', 'def' => '82'], // 주문자 전화번호 국가번호
            ['val' => 'orderCellPhone', 'typ' => 's', 'def' => null], // 주문자 휴대폰 번호
            ['val' => 'orderZipcode', 'typ' => 's', 'def' => null], // 주문자 우편번호
            ['val' => 'orderZonecode', 'typ' => 's', 'def' => null], // 주문자 우편번호(5자리)
            ['val' => 'orderState', 'typ' => 's', 'def' => null], // 주문자 주/지방/지역
            ['val' => 'orderCity', 'typ' => 's', 'def' => null], // 주문자 도시
            ['val' => 'orderAddress', 'typ' => 's', 'def' => null], // 주문자 주소
            ['val' => 'orderAddressSub', 'typ' => 's', 'def' => null], // 주문자 나머지 주소
            ['val' => 'receiverName', 'typ' => 's', 'def' => null], // 수취인 이름
            ['val' => 'receiverCountryCode', 'typ' => 's', 'def' => 'kr'], // 수취인 국가코드 (영문 2자리)
            ['val' => 'receiverPhonePrefixCode', 'typ' => 's', 'def' => 'kr'], // 수취인 전화번호 국가코드
            ['val' => 'receiverPhonePrefix', 'typ' => 's', 'def' => '82'], // 수취인 전화번호 국가번호
            ['val' => 'receiverPhone', 'typ' => 's', 'def' => null], // 수취인 전화번호
            ['val' => 'receiverCellPhonePrefixCode', 'typ' => 's', 'def' => 'kr'], // 수취인 휴대폰 번호 국가코드
            ['val' => 'receiverCellPhonePrefix', 'typ' => 's', 'def' => '82'], // 수취인 휴대폰 번호 국가번호
            ['val' => 'receiverCellPhone', 'typ' => 's', 'def' => null], // 수취인 휴대폰 번호
            ['val' => 'receiverUseSafeNumberFl', 'typ' => 's', 'def' => 'n'], // 수취인 안심번호 사용여부
            ['val' => 'receiverSafeNumber', 'typ' => 's', 'def' => null], // 수취인 안심번호
            ['val' => 'receiverSafeNumberDt', 'typ' => 's', 'def' => null], // 수취인 안심번호 발급일자
            ['val' => 'receiverZipcode', 'typ' => 's', 'def' => null], // 수취인 우편번호
            ['val' => 'receiverZonecode', 'typ' => 's', 'def' => null], // 수취인 우편번호(5자리)
            ['val' => 'receiverCountry', 'typ' => 's', 'def' => null], // 수취인 국가이름
            ['val' => 'receiverState', 'typ' => 's', 'def' => null], // 수취인 주/지방/지역
            ['val' => 'receiverCity', 'typ' => 's', 'def' => null], // 수취인 도시
            ['val' => 'receiverAddress', 'typ' => 's', 'def' => null], // 수취인 주소
            ['val' => 'receiverAddressSub', 'typ' => 's', 'def' => null], // 수취인 나머지 주소
            ['val' => 'customIdNumber', 'typ' => 's', 'def' => null], // 개인통관고유번호 (현재 사용 안함)
            ['val' => 'orderMemo', 'typ' => 's', 'def' => null], // 남기실 내용
            ['val' => 'packetCode', 'typ' => 's', 'def' => null], // 묶음배송 코드
            ['val' => 'orderInfoCd', 'typ' => 'i', 'def' => '1'], // 배송지 순서
            ['val' => 'deliveryVisit', 'typ' => 's', 'def' => 'n'], // 배송방법(y:방문배송, n:방문배송제외, a:방문배송포함)
            ['val' => 'visitAddress', 'typ' => 's', 'def' => null], // 방문 수령지 주소
            ['val' => 'visitName', 'typ' => 's', 'def' => null], // 방문자명
            ['val' => 'visitPhone', 'typ' => 's', 'def' => null], // 방문자연락처
            ['val' => 'visitMemo', 'typ' => 's', 'def' => null], // 방문수령 메모
            ['val' => 'smsFl', 'typ' => 's', 'def' => 'n'], // 비회원 마케팅 동의 (회원은 무조건n)
        ];
        // @formatter:on

        return $arrField;
    }

    /**
     * [주문] orderConsult 필드 기본값
     *
     * @author artherot
     * @return array order_godopst 테이블 필드 정보
     */
    public static function tableOrderConsult()
    {
        // @formatter:off
        $arrField = [
            ['val' => 'orderNo', 'typ' => 's', 'def' => null], // 주문번호
            ['val' => 'managerNo', 'typ' => 's', 'def' => null], // 관리자번호
            ['val' => 'requestMemo', 'typ' => 's', 'def' => null], // 고객요청사항
            ['val' => 'consultMemo', 'typ' => 's', 'def' => null], // 고객상담메모
        ];
        // @formatter:on

        return $arrField;
    }

    /**
     * [주문] orderInfo 필드 기본값
     *
     * @author artherot
     * @return array order_godopst 테이블 필드 정보
     */
    public static function tableOrderGodoPost()
    {
        // @formatter:off
        $arrField = [
            ['val' => 'invoiceNo', 'typ' => 's', 'def' => null], // 송장번호
            ['val' => 'reserveFl', 'typ' => 's', 'def' => null], // 예약번호
            ['val' => 'reserveParameter', 'typ' => 's', 'def' => null], // 예약파라미터
            ['val' => 'reserveDt', 'typ' => 's', 'def' => null], // 예약시간
        ];
        // @formatter:on

        return $arrField;
    }

    /**
     * [주문] 송장일괄등록 (로그)
     *
     * @static
     * @author qnibus
     * @return array
     */
    public static function tableOrderInvoice()
    {
        // @formatter:off
        $arrField = [
            ['val' => 'orderNo', 'typ' => 's', 'def' => null], // 주문번호
            ['val' => 'orderGoodsNo', 'typ' => 's', 'def' => null], // 주문상품 번호
            ['val' => 'managerNo', 'typ' => 's', 'def' => null], // 관리자 번호
            ['val' => 'scmNo', 'typ' => 's', 'def' => DEFAULT_CODE_SCMNO], // 공급사 번호
            ['val' => 'groupCd', 'typ' => 's', 'def' => null], // 그룹코드
            ['val' => 'invoiceCompanySno', 'typ' => 's', 'def' => null], // 배송업체번호
            ['val' => 'invoiceNo', 'typ' => 's', 'def' => null], // 송장번호
            ['val' => 'deliveryDt', 'typ' => 's', 'def' => null], // 배송일
            ['val' => 'deliveryCompleteDt', 'typ' => 's', 'def' => null], // 배송완료일
            ['val' => 'completeFl', 'typ' => 's', 'def' => null], // 송장등록 성공여부
            ['val'=> 'failReason' , 'typ'=>'s','def'=>null],    //실패사유
        ];
        // @formatter:on

        return $arrField;
    }

    /**
     * [주문] 결제히스토리
     *
     * @static
     * @return array
     * @author Jong-tae Ahn <qnibus@godo.co.kr>
     */
    public static function tableOrderPayHistory()
    {
        // @formatter:off
        $arrField = [
            ['val' => 'orderNo', 'typ' => 's', 'def' => null], // 주문번호
            ['val' => 'type', 'typ' => 's', 'def' => 'fs'], // 히스토리 구분 (fs|pc|ac|pr|ar)
            ['val' => 'goodsPrice', 'typ' => 's', 'def' => 0], // 상품금액
            ['val' => 'deliveryCharge', 'typ' => 's', 'def' => 0], // 배송비
            ['val' => 'dcPrice', 'typ' => 's', 'def' => 0], // 할인금액
            ['val' => 'addPrice', 'typ' => 's', 'def' => 0], // 추가금액
            ['val' => 'settlePrice', 'typ' => 's', 'def' => 0], // 결제금액
        ];
        // @formatter:on

        return $arrField;
    }

    /**
     * [주문] 개선후 결제클레임 히스토리
     *
     * @static
     * @return array
     * @author by
     */
    public static function tableOrderPayHistory2()
    {
        // @formatter:off
        $arrField = [
            ['val' => 'orderNo', 'typ' => 's', 'def' => null], // 주문번호
            ['val' => 'type', 'typ' => 's', 'def' => 'fs'], // 히스토리 구분 (fs|pc|ac|pr|ar)
            ['val' => 'settlePrice', 'typ' => 'd', 'def' => '0.00'], // 결제금액
            ['val' => 'totalGoodsPrice', 'typ' => 'd', 'def' => '0.00'], // 상품금액
            ['val' => 'totalDeliveryCharge', 'typ' => 'd', 'def' => '0.00'], // 배송비
            ['val' => 'totalDeliveryInsuranceFee', 'typ' => 'd', 'def' => '0.00'], // 해외보증보험
            ['val' => 'totalGoodsDcPrice', 'typ' => 'd', 'def' => '0.00'], // 상품할인
            ['val' => 'totalMemberDcPrice', 'typ' => 'd', 'def' => '0.00'], // 회원추가할인
            ['val' => 'totalMemberOverlapDcPrice', 'typ' => 'd', 'def' => '0.00'], // 회원중복할인
            ['val' => 'totalMemberDeliveryDcPrice', 'typ' => 'd', 'def' => '0.00'], // 회원배송비무료
            ['val' => 'totalCouponGoodsDcPrice', 'typ' => 'd', 'def' => '0.00'], // 상품쿠폰
            ['val' => 'totalCouponOrderDcPrice', 'typ' => 'd', 'def' => '0.00'], // 주문쿠폰
            ['val' => 'totalCouponDeliveryDcPrice', 'typ' => 'd', 'def' => '0.00'], // 배송비쿠폰
            ['val' => 'useDeposit', 'typ' => 'd', 'def' => '0.00'], // 예치금사용
            ['val' => 'useMileage', 'typ' => 'd', 'def' => '0.00'], // 마일리지사용
            ['val' => 'totalMileage', 'typ' => 'd', 'def' => '0.00'], // 총 마일리지적립
            ['val' => 'memo', 'typ' => 's', 'def' => null], // 설명
        ];
        // @formatter:on

        return $arrField;
    }

    /**
     * [주문] 배송지 주소 필드 기본값
     *
     * @author Jong-tae Ahn <qnibus@godo.co.kr>
     * @return array order_shipping_address 테이블 필드 정보
     */
    public static function tableOrderShippingAddress()
    {
        // @formatter:off
        $arrField = [
            ['val' => 'mallSno', 'typ' => 'i', 'def' => null], // 멀티상점 일련번호
            ['val' => 'defaultFl', 'typ' => 's', 'def' => 'n'], // 상품등록시 기본노출여뷰 ('y'는 테이블내 유일무이한 값이여야 한다)
            ['val' => 'memNo', 'typ' => 'i', 'def' => null], // 회원 번호
            ['val' => 'shippingTitle', 'typ' => 's', 'def' => null], // 주문자 이름
            ['val' => 'shippingName', 'typ' => 's', 'def' => null], // 수취인 이름
            ['val' => 'shippingCountryCode', 'typ' => 's', 'def' => 'kr'], // 수취인 국가코드 (영문 2자리)
            ['val' => 'shippingZipcode', 'typ' => 's', 'def' => null], // 수취인 우편번호
            ['val' => 'shippingZonecode', 'typ' => 's', 'def' => null], // 수취인 우편번호(5자리)
            ['val' => 'shippingCountry', 'typ' => 's', 'def' => null], // 수취인 국가명
            ['val' => 'shippingState', 'typ' => 's', 'def' => null], // 수취인 주/지방/지역
            ['val' => 'shippingCity', 'typ' => 's', 'def' => null], // 수취인 도시
            ['val' => 'shippingAddress', 'typ' => 's', 'def' => null], // 수취인 주소
            ['val' => 'shippingAddressSub', 'typ' => 's', 'def' => null], // 수취인 나머지 주소
            ['val' => 'shippingPhonePrefixCode', 'typ' => 's', 'def' => 'kr'], // 수취인 전화번호 국가코드
            ['val' => 'shippingPhonePrefix', 'typ' => 's', 'def' => '82'], // 수취인 전화번호 국가번호
            ['val' => 'shippingPhone', 'typ' => 's', 'def' => null], // 수취인 전화번호
            ['val' => 'shippingCellPhonePrefixCode', 'typ' => 's', 'def' => 'kr'], // 수취인 휴대폰 번호 국가코드
            ['val' => 'shippingCellPhonePrefix', 'typ' => 's', 'def' => '82'], // 수취인 휴대폰 번호 국가번호
            ['val' => 'shippingCellPhone', 'typ' => 's', 'def' => null], // 수취인 휴대폰 번호
        ];
        // @formatter:on

        return $arrField;
    }

    /**
     * [주문] 자주쓰는 주소 필드 기본값
     *
     * @author Jong-tae Ahn <qnibus@godo.co.kr>
     * @return array order_frequency_address 테이블 필드 정보
     */
    public static function tableOrderFrequencyAddress()
    {
        // @formatter:off
        $arrField = [
            ['val' => 'managerNo', 'typ' => 'i', 'def' => null], // 회원 번호
            ['val' => 'groupSno', 'typ' => 'i', 'def' => null], // 그룹번호 (추후 확장용)
            ['val' => 'groupNm', 'typ' => 's', 'def' => null], // 그룹명
            ['val' => 'name', 'typ' => 's', 'def' => null], // 수취인 이름
            ['val' => 'countryCode', 'typ' => 's', 'def' => null], // 수취인 국가코드 (영문 2자리)
            ['val' => 'email', 'typ' => 's', 'def' => null], // 수취인 이름
            ['val' => 'phone', 'typ' => 's', 'def' => null], // 수취인 전화번호
            ['val' => 'cellPhone', 'typ' => 's', 'def' => null], // 수취인 휴대폰 번호
            ['val' => 'zipcode', 'typ' => 's', 'def' => null], // 수취인 우편번호
            ['val' => 'zonecode', 'typ' => 's', 'def' => null], // 수취인 우편번호(5자리)
            ['val' => 'address', 'typ' => 's', 'def' => null], // 수취인 주소
            ['val' => 'addressSub', 'typ' => 's', 'def' => null], // 수취인 나머지 주소
            ['val' => 'memo', 'typ' => 's', 'def' => null], // 메모
            ['val' => 'businessNo', 'typ' => 's', 'def' => null], // 사업자 번호
            ['val' => 'bCompanyNm', 'typ' => 's', 'def' => null], // 회사명
            ['val' => 'bCeoNm', 'typ' => 's', 'def' => null], // 대표자명
            ['val' => 'bService', 'typ' => 's', 'def' => null], // 업태
            ['val' => 'bItem', 'typ' => 's', 'def' => null], // 종목
            ['val' => 'bZipcode', 'typ' => 's', 'def' => null], // 사업장 우편번호
            ['val' => 'bZonecode', 'typ' => 's', 'def' => null], // 사업장 우편번호(10자리)
            ['val' => 'bAddress', 'typ' => 's', 'def' => null], // 사업장 주소
            ['val' => 'bAddressSub', 'typ' => 's', 'def' => null], // 사업장 나머지 주소
            ['val' => 'bEmail', 'typ' => 's', 'def' => null], // 발행 이메일
        ];
        // @formatter:on

        return $arrField;
    }

    /**
     * [주문] orderDelivery 필드 기본값
     *
     * @author artherot
     * @return array order_delivery 테이블 필드 정보
     */
    public static function tableOrderDelivery()
    {
        // @formatter:off
        $arrField = [
            ['val' => 'orderNo', 'typ' => 's', 'def' => null], // 주문번호
            ['val' => 'scmNo', 'typ' => 'i', 'def' => DEFAULT_CODE_SCMNO], // 공급사 고유 번호
            ['val' => 'commission', 'typ' => 'd', 'def' => '0.00'], // 공급사 배송비 수수료율
            ['val' => 'scmAdjustNo', 'typ' => 'd', 'def' => '0'], // 공급사 정산 고유번호
            ['val' => 'scmAdjustAfterNo', 'typ' => 'i', 'def' => '0'], // 공급사 정산 후 환불 고유번호
            ['val' => 'deliverySno', 'typ' => 'i', 'def' => null], // 원래 배송비 sno
            ['val' => 'deliveryCharge', 'typ' => 'i', 'def' => '0'], // 총 배송비
            ['val' => 'taxSupplyDeliveryCharge', 'typ' => 'd', 'def' => '0.00'],// 총 과세 금액
            ['val' => 'taxVatDeliveryCharge', 'typ' => 'd', 'def' => '0.00'],// 총 부과세 금액
            ['val' => 'taxFreeDeliveryCharge', 'typ' => 'd', 'def' => '0.00'],// 총 면세 금액
            ['val' => 'realTaxSupplyDeliveryCharge', 'typ' => 'd', 'def' => '0.00'],// 실제남은 총 과세 금액
            ['val' => 'realTaxVatDeliveryCharge', 'typ' => 'd', 'def' => '0.00'],// 실제남은 총 부과세 금액
            ['val' => 'realTaxFreeDeliveryCharge', 'typ' => 'd', 'def' => '0.00'],// 실제남은 총 면세 금액
            ['val' => 'deliveryPolicyCharge', 'typ' => 'i', 'def' => '0'], // 총 정책이 적용된 배송비
            ['val' => 'deliveryAreaCharge', 'typ' => 'i', 'def' => '0'], // 총 지역별 배송비
            ['val' => 'divisionDeliveryUseDeposit', 'typ' => 'd', 'def' => '0.00'], // 주문할인 금액의 안분된 예치금
            ['val' => 'divisionDeliveryUseMileage', 'typ' => 'd', 'def' => '0.00'], // 주문할인 금액의 안분된 마일리지
            ['val' => 'divisionDeliveryCharge', 'typ' => 'i', 'def' => '0'], // 총 배송비 쿠폰 안분 금액
            ['val' => 'divisionMemberDeliveryDcPrice', 'typ' => 'i', 'def' => '0'], // 회원 배송비 무료 안분 금액
            ['val' => 'deliveryInsuranceFee', 'typ' => 'i', 'def' => '0'], // EMS 해외 배송 보험료
            ['val' => 'deliveryFixFl', 'typ' => 's', 'def' => null], // 배송정책 (고정비/무료/가격/무게/수량별)
            ['val' => 'goodsDeliveryFl', 'typ' => 's', 'def' => null], // 배송비 부과 방식 (배송비 조건별/상품별)
            ['val' => 'deliveryTaxInfo', 'typ' => 's', 'def' => 't' . STR_DIVISION . '10'], // 배송비 과세/비과세 정보
            ['val' => 'deliveryWeightInfo', 'typ' => 'j', 'def' => null], // 배송무게정보 (상품+박스)
            ['val' => 'deliveryPolicy', 'typ' => 's', 'def' => null], // 주문당시의 배송정책
            ['val' => 'overseasDeliveryPolicy', 'typ' => 's', 'def' => null], // 주문당시의 해외 배송정책
            ['val' => 'deliveryCollectFl', 'typ' => 's', 'def' => 'n'], // 배송비 결제방법 (pre - 선불, later - 착불)
            ['val' => 'deliveryCollectPrice', 'typ' => 'i', 'def' => '0'], // 착불시 발생된 배송비
            ['val' => 'deliveryMethod', 'typ' => 's', 'def' => null], // 기본 배송 방법
            ['val' => 'deliveryWholeFreeFl', 'typ' => 's', 'def' => 'n'], // 상품별 배송 여부
            ['val' => 'deliveryWholeFreePrice', 'typ' => 'i', 'def' => '0'], // 동일 배송비 무료조건시의 배송비
            ['val' => 'deliveryLog', 'typ' => 's', 'def' => null], // 착불배송 로그
            ['val' => 'paymentDt', 'typ' => 's', 'def' => null], // 완료 일자
            ['val' => 'statisticsOrderFl', 'typ' => 's', 'def' => null], // 완료 일자
            ['val' => 'orderInfoSno', 'typ' => 'i', 'def' => null], // 주문정보sno
        ];
        // @formatter:on

        return $arrField;
    }

    /**
     * [주문] orderCoupon 필드 기본값
     *
     * @author artherot
     * @return array order_coupon 테이블 필드 정보
     */
    public static function tableOrderCoupon()
    {
        // @formatter:off
        $arrField = [
            ['val' => 'orderNo', 'typ' => 's', 'def' => null], // 주문번호
            ['val' => 'orderCd', 'typ' => 'i', 'def' => null], // 주문 코드(순서)
            ['val' => 'goodsNo', 'typ' => 'i', 'def' => null], // 상품 번호
            ['val' => 'memberCouponNo', 'typ' => 'i', 'def' => '0'], // 회원쿠폰 번호 (다운로드 받은 쿠폰)
            ['val' => 'couponUseType', 'typ' => 's', 'def' => 'product'], // 쿠폰유형–상품쿠폰(‘product’),주문쿠폰(‘order’),배송비쿠폰('delivery')
            ['val' => 'couponNm', 'typ' => 's', 'def' => null], // 쿠폰명
            ['val' => 'expireSdt', 'typ' => 's', 'def' => null], // 유효기간 (시작일)
            ['val' => 'expireEdt', 'typ' => 's', 'def' => null], // 유효기간 (종료일)
            ['val' => 'couponPrice', 'typ' => 'i', 'def' => '0'], // 쿠폰 할인 가격
            ['val' => 'couponMileage', 'typ' => 'i', 'def' => '0'], // 쿠폰 마일리지
            ['val' => 'minusCouponFl', 'typ' => 's', 'def' => 'n'], // 차감 여부 (쿠폰)
            ['val' => 'plusCouponFl', 'typ' => 's', 'def' => 'n'], // 적립 여부 (쿠폰)
            ['val' => 'minusRestoreCouponFl', 'typ' => 's', 'def' => 'n'], // 복원 여부 (쿠폰 사용)
            ['val' => 'plusRestoreCouponFl', 'typ' => 's', 'def' => 'n'], // 복원 여부 (쿠폰 적립)
        ];
        // @formatter:on

        return $arrField;
    }

    /**
     * [주문] orderGift 필드 기본값
     *
     * @author artherot
     * @return array order_gift 테이블 필드 정보
     */
    public static function tableOrderGift()
    {
        // @formatter:off
        $arrField = [
            ['val' => 'orderNo', 'typ' => 's', 'def' => null], // 주문번호
            ['val' => 'scmNo', 'typ' => 'i', 'def' => DEFAULT_CODE_SCMNO], // 공급사 고유 번호
            ['val' => 'presentSno', 'typ' => 'i', 'def' => null], // 사은품 증정 sno
            ['val' => 'giftNo', 'typ' => 's', 'def' => null], // 멀티 선택형 사은품 코드
            ['val' => 'selectCnt', 'typ' => 'i', 'def' => '0'], // 멀티 선택형 모드 (선택개수)
            ['val' => 'giveCnt', 'typ' => 'i', 'def' => '0'], // 멀티 선택형 모드 (선택개수)
            ['val' => 'minusStockFl', 'typ' => 's', 'def' => 'n'], // 재고 차감 여부
            ['val' => 'minusRestoreStockFl', 'typ' => 's', 'def' => 'n'], // 복원 여부 (재고)
            ['val' => 'giftPolicy', 'typ' => 'j', 'def' => null], // 주문시 사은품 정책 json
        ];
        // @formatter:on

        return $arrField;
    }

    /**
     * [관리/설정] orderTax 필드 기본값
     *
     * @author artherot
     * @return array order_tax 테이블 필드 정보
     */
    public static function tableOrderTax()
    {
        // @formatter:off
        $arrField = [
            ['val' => 'orderNo', 'typ' => 's', 'def' => null], // 주문번호
            ['val' => 'issueMode', 'typ' => 's', 'def' => 'u'], // 발급 모드 (관리자발급 - a, 사용자신청 - u)
            ['val' => 'managerId', 'typ' => 's', 'def' => null], // 관리자 아이디 (개별 발급일 경우)
            ['val' => 'requestId', 'typ' => 's', 'def' => null], // 신청자 아이디
            ['val' => 'requestNm', 'typ' => 's', 'def' => null], // 신청자
            ['val' => 'applicantId', 'typ' => 's', 'def' => null], // 주문자 아이디
            ['val' => 'applicantNm', 'typ' => 's', 'def' => null], // 주문자 이름
            ['val' => 'requestGoodsNm', 'typ' => 's', 'def' => null], // 신청 상품명
            ['val' => 'requestIP', 'typ' => 's', 'def' => null], // 신청자 IP
            ['val' => 'taxCompany', 'typ' => 's', 'def' => null], // 회사명
            ['val' => 'taxBusiNo', 'typ' => 's', 'def' => null], // 사업자 번호
            ['val' => 'taxCeoNm', 'typ' => 's', 'def' => null], // 대표자명
            ['val' => 'taxService', 'typ' => 's', 'def' => null], // 업태
            ['val' => 'taxItem', 'typ' => 's', 'def' => null], // 종목
            ['val' => 'taxEmail', 'typ' => 's', 'def' => null], // 이메일
            ['val' => 'taxZipcode', 'typ' => 's', 'def' => null], // 우편번호
            ['val' => 'taxZonecode', 'typ' => 's', 'def' => null], // 우편번호(5자리)
            ['val' => 'taxAddress', 'typ' => 's', 'def' => null], // 주소
            ['val' => 'taxAddressSub', 'typ' => 's', 'def' => null], // 나머지 주소
            ['val' => 'taxStepFl', 'typ' => 's', 'def' => null], // 정책 - 발행일 기준
            ['val' => 'taxDeliveryCompleteFl', 'typ' => 's', 'def' => null], // 주문에 포함된 모든 상품 배송완료 기준
            ['val' => 'taxPolicy', 'typ' => 's', 'def' => null], // 정책 - 발행금액 포함 항목
            ['val' => 'issueFl', 'typ' => 's', 'def' => 'ㅎ'], // 발행종류
            ['val' => 'statusFl', 'typ' => 's', 'def' => 'r'], // 발행 상태 (신청 - r, 발행완료 - y, 발행취소 - c)
            ['val' => 'issueDt', 'typ' => 's', 'def' => null], // 발행일
            ['val' => 'processDt', 'typ' => 's', 'def' => null], // 처리일자
            ['val' => 'cancelDt', 'typ' => 's', 'def' => null], // 취소일자
            ['val' => 'taxLog', 'typ' => 's', 'def' => null], // 세금계산서 로그
            ['val' => 'adminMemo', 'typ' => 's', 'def' => null], // 관리자 메모
        ];
        // @formatter:on

        return $arrField;
    }


    /**
     * [관리/설정] orderTaxIssue 필드 기본값
     *
     * @author artherot
     * @return array order_tax 테이블 필드 정보
     */
    public static function tableOrderTaxIssue()
    {
        // @formatter:off
        $arrField = [
            ['val' => 'sno', 'typ' => 's', 'def' => null], // 고유번호
            ['val' => 'orderNo', 'typ' => 's', 'def' => null], // orderTax sno
            ['val' => 'taxBusiNo', 'typ' => 's', 'def' => null], // 발급업체
            ['val' => 'taxFreeFl', 'typ' => 's', 'def' => null], // 과세구분
            ['val' => 'taxGodobillCd', 'typ' => 's', 'def' => null], // 고도몰코드
            ['val' => 'printFl', 'typ' => 's', 'def' =>'n'], // 프린트여부
            ['val' => 'printDt', 'typ' => 's', 'def' => null], // 프린트시간
            ['val' => 'issueStatusFl', 'typ' => 's', 'def' => null], // 발행상태
            ['val' => 'issuePrice', 'typ' => 's', 'def' => null], //발행금액(부가세 제외)
            ['val' => 'vatPrice', 'typ' => 's', 'def' => null], // 부가세
        ];
        // @formatter:on

        return $arrField;
    }

    /**
     * [관리/설정] orderCashReceipt 필드 기본값
     *
     * @author artherot
     * @return array order_cash_receipt 테이블 필드 정보
     */
    public static function tableOrderCashReceipt()
    {
        // @formatter:off
        $arrField = [
            ['val' => 'orderNo', 'typ' => 's', 'def' => null], // 주문번호
            ['val' => 'issueMode', 'typ' => 's', 'def' => 'u'], // 발급 모드 (관리자발급 - a, 사용자신청 - u, PG 자동신청 - p)
            ['val' => 'managerNo', 'typ' => 'i', 'def' => 0], // 관리자 키값
            ['val' => 'managerId', 'typ' => 's', 'def' => null], // 관리자 아이디 (개별 발급일 경우)
            ['val' => 'requestNm', 'typ' => 's', 'def' => null], // 신청자
            ['val' => 'requestGoodsNm', 'typ' => 's', 'def' => null], // 신청 상품명
            ['val' => 'requestIP', 'typ' => 's', 'def' => null], // 신청자 IP
            ['val' => 'requestEmail', 'typ' => 's', 'def' => null], // 신청자 이메일
            ['val' => 'requestCellPhone', 'typ' => 's', 'def' => null], // 신청자 휴대폰 번호
            ['val' => 'useFl', 'typ' => 's', 'def' => 'd'], // 발행 용도 (소득공제용 - d, 지출증빙용 - e)
            ['val' => 'certFl', 'typ' => 's', 'def' => 'c'], // 인증 종류 (주민등록번호 - l, 사업자번호 - b, 휴대폰번호 - c)
            ['val' => 'certNo', 'typ' => 's', 'def' => null], // 인증 번호
            ['val' => 'settlePrice', 'typ' => 'd', 'def' => '0.00'], // 신청금액
            ['val' => 'supplyPrice', 'typ' => 'd', 'def' => '0.00'], // 공급가
            ['val' => 'taxPrice', 'typ' => 'd', 'def' => '0.00'], // 부가세
            ['val' => 'freePrice', 'typ' => 'd', 'def' => '0.00'], // 면세
            ['val' => 'servicePrice', 'typ' => 'd', 'def' => '0.00'], // 봉사료
            ['val' => 'pgName', 'typ' => 's', 'def' => null], // PG명
            ['val' => 'pgTid', 'typ' => 's', 'def' => null], // 거래번호
            ['val' => 'pgAppNo', 'typ' => 's', 'def' => null], // 승인번호
            ['val' => 'pgAppDt', 'typ' => 's', 'def' => null], // 승인일자
            ['val' => 'pgAppNoCancel', 'typ' => 's', 'def' => null], // 취소승인번호
            ['val' => 'statusFl', 'typ' => 's', 'def' => 'r'], // 발행 상태 (r - 발급요청, y - 발행완료, c - 발행취소, d - 발행거절, f - 발행실패)
            ['val' => 'reIssueFl', 'typ' => 's', 'def' => 'n'], // 현금영수증 재발행(주문상세)
            ['val' => 'adminFl', 'typ' => 's', 'def' => 'n'], // 현금영수증 주문자 직접 취소 여부
            ['val' => 'processDt', 'typ' => 's', 'def' => null], // 처리일자
            ['val' => 'pgLog', 'typ' => 's', 'def' => null], // PG 로그
            ['val' => 'adminMemo', 'typ' => 's', 'def' => null], // 관리자 메모
            ['val' => 'firstRegDt', 'typ' => 's', 'def' => null], // 최초 신청일 (재발행 된 건은 최초 신청일 저장)
        ];
        // @formatter:on

        return $arrField;
    }

    /**
     * [주문] orderHandle 필드 기본값
     *
     * @author artherot
     * @return array order_goods 테이블 필드 정보
     */
    public static function tableOrderHandle()
    {
        // @formatter:off
        $arrField = [
            ['val' => 'orderNo', 'typ' => 's', 'def' => null], // 주문번호
            ['val' => 'beforeStatus', 'typ' => 's', 'def' => 'p1'], // 이전 상태 코드
            ['val' => 'handleMode', 'typ' => 's', 'def' => 'b'], // 처리 모드 (r- 환불, b - 반품, e - 교환)
            ['val' => 'handleCompleteFl', 'typ' => 's', 'def' => 'n'], // 처리 완료 여부 (y - 완료, n - 접수, d - 삭제)
            ['val' => 'handleReason', 'typ' => 's', 'def' => null], // 처리사유(코드값이 들어간다)
            ['val' => 'handleDetailReason', 'typ' => 's', 'def' => null], // 처리상세사유
            ['val' => 'handleDetailReasonShowFl', 'typ' => 's', 'def' => 'n'], // 처리상세사유
            ['val' => 'handleData', 'typ' => 's', 'def' => null], // PG 취소 후 결과내용 (json)
            ['val' => 'handleDt', 'typ' => 's', 'def' => null], // 환불 완료 일자
            ['val' => 'refundGroupCd', 'typ' => 'i', 'def' => null], // 동일하게 환불완료되는 상품의 그룹 코드
            ['val' => 'refundMethod', 'typ' => 's', 'def' => null], // 환불 수단
            ['val' => 'refundBankName', 'typ' => 's', 'def' => null], // 환불 은행
            ['val' => 'refundAccountNumber', 'typ' => 's', 'def' => null], // 환불 계좌
            ['val' => 'refundDepositor', 'typ' => 's', 'def' => null], // 환불 계좌 예금주
            ['val' => 'refundPrice', 'typ' => 'd', 'def' => '0.00'], // 환불 금액
            ['val' => 'refundUseDeposit', 'typ' => 'd', 'def' => '0.00'], // 예치금 환원(사용 예치금)
            ['val' => 'refundUseMileage', 'typ' => 'd', 'def' => '0.00'], // 마일리지 환원(사용 마일리지)
            ['val' => 'refundDeliveryUseDeposit', 'typ' => 'd', 'def' => '0.00'], // 배송비에 할당된 사용 예치금 환원
            ['val' => 'refundDeliveryUseMileage', 'typ' => 'd', 'def' => '0.00'], // 배송비에 할당된 사용 마일리지 환원
            ['val' => 'refundDeliveryCharge', 'typ' => 'd', 'def' => '0.00'], // 배송비 환불 금액
            ['val' => 'refundDeliveryInsuranceFee', 'typ' => 'd', 'def' => '0.00'], // 해외배송 보험료 환불 금액
            ['val' => 'refundCharge', 'typ' => 'd', 'def' => '0.00'], // 환불 수수료
            ['val' => 'refundUseDepositCommission', 'typ' => 'd', 'def' => '0.00'], // 사용예치금 수수료
            ['val' => 'refundUseMileageCommission', 'typ' => 'd', 'def' => '0.00'], // 사용마일리지 수수료
            ['val' => 'refundGiveMileage', 'typ' => 'd', 'def' => '0.00'], // 마일리지 환원(적립 마일리지
            ['val' => 'completeCashPrice', 'typ' => 'd', 'def' => '0.00'], // 실 현금 환불 금액
            ['val' => 'completePgPrice', 'typ' => 'd', 'def' => '0.00'], // 실 PG 환불 금액
            ['val' => 'completeDepositPrice', 'typ' => 'd', 'def' => '0.00'], // 실 예치금 환불 금액
            ['val' => 'completeMileagePrice', 'typ' => 'd', 'def' => '0.00'], // 실 마일리지 환불 금액
            ['val' => 'refundDeliveryCoupon', 'typ' => 'd', 'def' => '0.00'], // 배송비 쿠폰 환불금액
            ['val' => 'handleGroupCd', 'typ' => 'i', 'def' => 0], // 클레임일회처리당 그룹
        ];
        // @formatter:on

        return $arrField;
    }

    /**
     * [주문] orderUserHandle 필드 기본값
     *
     * @author qnibus
     * @return array order_goods 테이블 필드 정보
     */
    public static function tableOrderUserHandle()
    {
        // @formatter:off
        $arrField = [
            ['val' => 'orderNo', 'typ' => 's', 'def' => null], // 주문번호
            ['val' => 'managerNo', 'typ' => 'i', 'def' => null], // 공급사 회원 고유번호
            ['val' => 'userHandleMode', 'typ' => 's', 'def' => 'b'], // 처리 모드 (r- 환불, b - 반품, e - 교환)
            ['val' => 'userHandleFl', 'typ' => 's', 'def' => 'r'], // 처리 완료 여부 (y - 승인, n - 거절, r - 신청/요청)
            ['val' => 'userHandleGoodsNo', 'typ' => 'i', 'def' => null], // 처리할 상품 번호
            ['val' => 'userHandleGoodsCnt', 'typ' => 'i', 'def' => null], // 처리할 상품 수량
            ['val' => 'userRefundMethod', 'typ' => 's', 'def' => null], // 환불 수단
            ['val' => 'userRefundBankName', 'typ' => 's', 'def' => null], // 환불 은행
            ['val' => 'userRefundAccountNumber', 'typ' => 's', 'def' => null], // 환불 계좌
            ['val' => 'userRefundDepositor', 'typ' => 's', 'def' => null], // 환불 계좌 예금주
            ['val' => 'userHandleReason', 'typ' => 's', 'def' => null], // 처리사유(코드값이 들어간다)
            ['val' => 'userHandleDetailReason', 'typ' => 's', 'def' => null], // 처리상세사유
            ['val' => 'adminHandleReason', 'typ' => 's', 'def' => null], // 보류상세사유
        ];
        // @formatter:on

        return $arrField;
    }

    /**
     * [주문] orderAddField 필드 기본값
     *
     * @author su
     * @return array orderAddField 테이블 필드 정보
     */
    public static function tableOrderAddField()
    {
        // @formatter:off
        $arrField = [
            ['val' => 'orderAddFieldNo', 'typ' => 'i', 'def' => 0], // 주문 추가 필드 고유번호
            ['val' => 'orderAddFieldName', 'typ' => 's', 'def' => ''], // 주문 추가 필드 항목명
            ['val' => 'orderAddFieldDescribed', 'typ' => 's', 'def' => ''], // 주문 추가 필드 설명
            ['val' => 'orderAddFieldDisplay', 'typ' => 's', 'def' => 'y'], // 주문 추가 필드 노출 여부 - y 사용, n 사용안함
            ['val' => 'orderAddFieldRequired', 'typ' => 's', 'def' => 'y'], // 주문 추가 필드 필수 여부 - y 필수입력, n 선택입력
            ['val' => 'orderAddFieldType', 'typ' => 's', 'def' => 'text'], // 주문 추가 필드 항목 타입 - text, textarea, file, radio, checkbox, select
            ['val' => 'orderAddFieldOption', 'typ' => 's', 'def' => null], // 주문 추가 필드 항목 타입별 옵션
            ['val' => 'orderAddFieldApply', 'typ' => 's', 'def' => null], // 주문 추가 필드 적용 내용
            ['val' => 'orderAddFieldExcept', 'typ' => 's', 'def' => null], // 주문 추가 필드 제외 내용
            ['val' => 'orderAddFieldProcess', 'typ' => 's', 'def' => 'order'], // 주문 추가 필드 처리 방식 - order 주문당 1번 입력, goods 상품당 입력
            ['val' => 'orderAddFieldSort', 'typ' => 'i', 'def' => 0], // 주문 추가 필드 노출 순서
            ['val' => 'managerNo', 'typ' => 'i', 'def' => 0], // 주문 추가 필드 등록/수정자 고유번호
            ['val' => 'managerId', 'typ' => 's', 'def' => ''], // 주문 추가 필드 등록/수정자 아이디
            ['val' => 'managerNm', 'typ' => 's', 'def' => ''], // 주문 추가 필드 등록/수정자 이름
        ];
        // @formatter:on

        return $arrField;
    }

    /**
     * [코드] code 필드 기본값
     *
     * @author artherot
     * @return array code 테이블 필드 정보
     */
    public static function tableCode()
    {
        // @formatter:off
        $arrField = [
            ['val' => 'itemCd', 'typ' => 's', 'def' => null], // 아이템코드
            ['val' => 'groupCd', 'typ' => 's', 'def' => null], // 그룹코드
            ['val' => 'itemNm', 'typ' => 's', 'def' => null], // 코드명
            ['val' => 'sort', 'typ' => 'i', 'def' => 1], // 정렬순서
            ['val' => 'useFl', 'typ' => 's', 'def' => 'y'], // 사용여부
            ['val' => 'isBasic', 'typ' => 's', 'def' => 'n'], // 기본제공코드
            ['val' => 'isUpdatableText', 'typ' => 's', 'def' => 'y'], // 코드 명칭 수정 가능 여부
            ['val' => 'isUpdatableUse', 'typ' => 's', 'def' => 'y'], // 코드 사용여부 수정 가능 여부
        ];
        // @formatter:on

        return $arrField;
    }

    /**
     * [글로벌 코드] code 필드 기본값
     *
     * @author artherot
     * @return array code 테이블 필드 정보
     */
    public static function tableCodeGlobal()
    {
        // @formatter:off
        $arrField = [
            ['val' => 'mallSno', 'typ' => 'i', 'def' => null], // 몰번호
            ['val' => 'itemCd', 'typ' => 's', 'def' => null], // 아이템코드
            ['val' => 'groupCd', 'typ' => 's', 'def' => null], // 그룹코드
            ['val' => 'itemNm', 'typ' => 's', 'def' => null], // 코드명
            ['val' => 'sort', 'typ' => 'i', 'def' => 1], // 정렬순서
            ['val' => 'useFl', 'typ' => 's', 'def' => 'y'], // 사용여부
        ];
        // @formatter:on

        return $arrField;
    }


    /**
     * [SCM] scm_manage 필드 기본값
     *
     * @author artherot
     * @return array scm_manage 테이블 필드 정보
     */
    public static function tableScmManage()
    {
        // @formatter:off
        $arrField = [
            ['val' => 'scmNo', 'typ' => 'i', 'def' => DEFAULT_CODE_SCMNO, 'name' => '공급사 고유번호'], // 공급사 고유 번호
            ['val' => 'companyNm', 'typ' => 's', 'def' => null, 'name' => '상호명'], // 상호명
            ['val' => 'scmType', 'typ' => 's', 'def' => 'n', 'name' => '공급사상태'], // 공급사상태-운영('y'), 일시정지('n'), 탈퇴('x')
            ['val' => 'scmCommission', 'typ' => 's', 'def' => '', 'name' => '판매수수료'], // 판매수수료-%로 소수점 2자리
            ['val' => 'scmCommissionDelivery', 'typ' => 's', 'def' => '', 'name' => '배송비수수료'], // 배송비수수료-%로 소수점 2자리
            ['val' => 'scmKind', 'typ' => 's', 'def' => 'p', 'name' => '공급사종류'], // 공급사종류 - 공급사('p'),본사('c')
            ['val' => 'scmCode', 'typ' => 's', 'def' => null, 'name' => '공급사코드'], // 공급사코드
            ['val' => 'imageStorage', 'typ' => 's', 'def' => 'local', 'name' => '이미지 저장소 위치'], // 이미지 저장소 위치
            ['val' => 'scmPermissionInsert', 'typ' => 's', 'def' => 'c', 'name' => '상품등록권한'], // 상품등록권한-자동승인('a'),관리자승인('c')
            ['val' => 'scmPermissionModify', 'typ' => 's', 'def' => 'c', 'name' => '상품수정권한'], // 상품수정권한-자동승인('a'),관리자승인('c')
            ['val' => 'scmPermissionDelete', 'typ' => 's', 'def' => 'c', 'name' => '상품삭제권한'], // 상품삭제권한-자동승인('a'),관리자승인('c')
            ['val' => 'businessNo', 'typ' => 's', 'def' => null, 'name' => '사업자 번호'], // 사업자 번호
            ['val' => 'businessLicenseImage', 'typ' => 's', 'def' => null, 'name' => '사업자등록증이미지'], // 사업자등록증이미지
            ['val' => 'onlineOrderSerial', 'typ' => 's', 'def' => null, 'name' => '통신판매 신고번호'], // 통신 판매 신고 번호
            ['val' => 'service', 'typ' => 's', 'def' => null, 'name' => '업태'], // 업태
            ['val' => 'item', 'typ' => 's', 'def' => null, 'name' => '종목'], // 종목
            ['val' => 'ceoNm', 'typ' => 's', 'def' => null, 'name' => '대표자'], // 대표자
            ['val' => 'email', 'typ' => 's', 'def' => null, 'name' => '대표 이메일'], // 대표 이메일
            ['val' => 'zipcode', 'typ' => 's', 'def' => null, 'name' => '우편번호'], // 우편번호
            ['val' => 'zonecode', 'typ' => 's', 'def' => null, 'name' => '우편번호(5자리)'], // 우편번호(5자리)
            ['val' => 'address', 'typ' => 's', 'def' => null, 'name' => '주소'], // 주소
            ['val' => 'addressSub', 'typ' => 's', 'def' => null, 'name' => '상세주소'], // 상세주소
            ['val' => 'unstoringZipcode', 'typ' => 's', 'def' => null, 'name' => '기본 출고지 우편번호'], // 기본 출고지 우편번호
            ['val' => 'unstoringZonecode', 'typ' => 's', 'def' => null, 'name' => '기본 출고지 우편번호(5자리)'], // 기본 출고지 우편번호(5자리)
            ['val' => 'unstoringAddress', 'typ' => 's', 'def' => null, 'name' => '기본 출고지 주소'], // 기본 출고지 주소
            ['val' => 'unstoringAddressSub', 'typ' => 's', 'def' => null, 'name' => '기본 출고지 상세주소'], // 기본 출고지 상세주소
            ['val' => 'returnZipcode', 'typ' => 's', 'def' => null, 'name' => '기본 반품-교환지 우편번호'], // 기본 반품/교환지 우편번호
            ['val' => 'returnZonecode', 'typ' => 's', 'def' => null, 'name' => '기본 반품-교환지 우편번호(5자리)'], // 기본 반품/교환지 우편번호(5자리)
            ['val' => 'returnAddress', 'typ' => 's', 'def' => null, 'name' => '기본 반품-교환지 주소'], // 기본 반품/교환지 주소
            ['val' => 'returnAddressSub', 'typ' => 's', 'def' => null, 'name' => '기본 반품-교환지 상세주소'], // 기본 반품/교환지 상세주소
            ['val' => 'phone', 'typ' => 's', 'def' => null, 'name' => '대표전화'], // 대표전화
            ['val' => 'centerPhone', 'typ' => 's', 'def' => null, 'name' => '고객센터 연락처'], // 고객센터 연락처
            ['val' => 'fax', 'typ' => 's', 'def' => null, 'name' => '팩스번호'], // 팩스번호
            ['val' => 'functionAuth', 'typ' => 's', 'def' => null, 'name' => '공급사 기능 권한'], // 공급사 기능 권한
            ['val' => 'staff', 'typ' => 's', 'def' => null, 'name' => '담당자 정보'], // 담당자 정보
            ['val' => 'account', 'typ' => 's', 'def' => null, 'name' => '계좌 정보'], // 계좌 정보
            ['val' => 'addInfo', 'typ' => 's', 'def' => null, 'name' => '추가 정보'], // 추가 정보
            ['val' => 'scmInsertAdminId', 'typ' => 's', 'def' => null, 'name' => 'SCM등록자 아이디'], // SCM등록자아이디
            ['val' => 'managerNo', 'typ' => 'i', 'def' => 0, 'name' => 'SCM등록자 고유번호'], // SCM등록자고유번호
            ['val' => 'delFl', 'typ' => 's', 'def' => 'n', 'name' => '삭제여부'], // 삭제여부
            ['val' => 'modDt', 'typ' => 's', 'def' => null, 'name' => '수정일'], // 수정일
            ['val' => 'regDt', 'typ' => 's', 'def' => null, 'name' => '등록일'], // 등록일
        ];
        // @formatter:on

        return $arrField;
    }

    /**
     * [SCM] scm_commission 필드 기본값
     *
     * @author tomi
     * @return array scm_commission 테이블 필드 정보
     */
    public static function tableScmCommission()
    {
        // @formatter:off
        $arrField = [
            ['val' => 'scmNo', 'typ' => 'i', 'def' => DEFAULT_CODE_SCMNO], // 공급사 고유 번호
            ['val' => 'commissionType', 'typ' => 's', 'def' => '', 'name' => '수수료구분'], // 판매수수료-%로 소수점 2자리
            ['val' => 'commissionValue', 'typ' => 's', 'def' => '', 'name' => '수수료'], // 수수료-%로 소수점 2자리
            ['val' => 'delFl', 'typ' => 's', 'def' => 'n', 'name' => '삭제여부'], // 삭제 플래그
            ['val' => 'modDt', 'typ' => 's', 'def' => null, 'name' => '수정일'], // 수정일
            ['val' => 'regDt', 'typ' => 's', 'def' => null, 'name' => '등록일'], // 등록일
        ];
        // @formatter:on

        return $arrField;
    }

    /**
     * [SCM] scm_commission_schedule 필드 기본값
     *
     * @author tomi
     * @return array scm_commission_schedule 테이블 필드 정보
     */
    public static function tableScmCommissionSchedule()
    {
        // @formatter:off
        $arrField = [
            ['val' => 'scmCommissionSno', 'typ' => 'i', 'def' => 0], // 공급사 판매수수료 번호
            ['val' => 'scmCommissionDeliverySno', 'typ' => 'i', 'def' => 0], // 공급사 배송비수수료 번호
            ['val' => 'applyCommissionLog', 'typ' => 's', 'def' => 'all', 'name' => '수수료 적용 로그 - 종료일정 확인용'], // 수수료 적용 값
            ['val' => 'scmNo', 'typ' => 'i', 'def' => DEFAULT_CODE_SCMNO], // 공급사 고유 번호
            ['val' => 'applyKind', 'typ' => 's', 'def' => 'all', 'name' => '수수료 적용 범위'], // 수수료 적용 범위
            ['val' => 'applyData', 'typ' => 's', 'def' => null, 'name' => '수수료 적용 값'], // 수수료 적용 값
            ['val' => 'exceptGoodsNo', 'typ' => 's', 'def' => null, 'name' => '수수료적용예외설정 - 상품 목록'], // 수수료적용예외설정 - 상품 목록
            ['val' => 'exceptBrandCd', 'typ' => 's', 'def' => null, 'name' => '수수료적용예외설정 - 브랜드 목록'], // 수수료적용예외설정 - 브랜드 목록
            ['val' => 'exceptCouponSno', 'typ' => 's','def' => null, 'name' => '수수료적용예외설정 - 쿠폰 목록'], // 수수료적용예외설정 - 쿠폰 목록
            ['val' => 'exceptMemberGroupSno', 'typ' => 's', 'def' => null, 'name' => '수수료적용예외설정 - 회원그룹 목록'], // 수수료적용예외설정 - 회원그룹 목록
            ['val' => 'startDate', 'typ' => 's', 'def' => null,  'name' => '특정기간 수수료적용시작'], // 특정기간 수수료적용시작
            ['val' => 'endDate', 'typ' => 's', 'def' => null,  'name' => '특정기간 수수료적용종료'], // 특정기간 수수료적용종료
            ['val' => 'delFl', 'typ' => 's', 'def' => 'n', 'name' => '삭제여부'], // 삭제 플래그
            ['val' => 'modDt', 'typ' => 's', 'def' => null, 'name' => '수정일'], // 수정일
            ['val' => 'regDt', 'typ' => 's', 'def' => null, 'name' => '등록일'], // 등록일
        ];
        // @formatter:on

        return $arrField;
    }

    /**
     * [SCM] log_scm_commission 필드 기본값
     *
     * @author tomi
     * @return array log_scm_commission 테이블 필드 정보
     */
    public static function tableLogScmCommission()
    {
        // @formatter:off
        $arrField = [
            ['val' => 'scmNo', 'typ' => 'i', 'def' => DEFAULT_CODE_SCMNO], // 공급사 고유 번호
            ['val' => 'logPage', 'typ' => 's', 'def' => 'scm', 'name' => '로그요청페이지'], // 로그요청페이지
            ['val' => 'logMode', 'typ' => 's', 'def' => 'insert', 'name' => '로그요청페이지'], // 로그요청페이지
            ['val' => 'prevData', 'typ' => 's', 'def' => null], // 수정전데이터
            ['val' => 'updateData', 'typ' => 's', 'def' => null], // 수정후데이터
            ['val' => 'managerNo', 'typ' => 'i', 'def' => 0], // 관리자 일련번호
            ['val' => 'managerId', 'typ' => 's', 'def' => null], // 관리자아이디
            ['val' => 'modDt', 'typ' => 's', 'def' => null, 'name' => '수정일'], // 수정일
            ['val' => 'regDt', 'typ' => 's', 'def' => null, 'name' => '등록일'], // 등록일
        ];
        // @formatter:on

        return $arrField;
    }

    /**
     * [SCM 배송] scm_delivery_basic 필드 기본값
     *
     * @author artherot
     * @return array scm_delivery_basic 테이블 필드 정보
     */
    public static function tableScmDeliveryBasic()
    {
        // @formatter:off
        $arrField = [
            ['val' => 'managerNo', 'typ' => 'i', 'def' => 0], // 배송 등록한 공급사 회원 고유 번호
            ['val' => 'scmNo', 'typ' => 'i', 'def' => DEFAULT_CODE_SCMNO], // 공급사 고유 번호
            ['val' => 'method', 'typ' => 's', 'def' => null], // 배송 방법
            ['val' => 'description', 'typ' => 's', 'def' => null], // 배송 설명
            ['val' => 'deleteFl', 'typ' => 's', 'def' => 'y'], // 최초 등록한 기본형으로 삭제 불가
            ['val' => 'defaultFl', 'typ' => 's', 'def' => 'n'], // 상품등록시 기본노출여뷰 ('y'는 테이블내 유일무이한 값이여야 한다)
            ['val' => 'collectFl', 'typ' => 's', 'def' => 'n'], // 배송비 결제방법
            ['val' => 'fixFl', 'typ' => 's', 'def' => 'price'], // 배송비 책정 방법
            ['val' => 'freeFl', 'typ' => 's', 'def' => 'y'], // 배송 정책 노출 사용여부
            ['val' => 'pricePlusStandard', 'typ' => 's', 'def' => null], // 금액별 배송비 기준 (상품할인가/상품쿠폰할인가)
            ['val' => 'priceMinusStandard', 'typ' => 's', 'def' => null],
            ['val' => 'goodsDeliveryFl', 'typ' => 's', 'def' => 'y'],
            ['val' => 'sameGoodsDeliveryFl', 'typ' => 's', 'def' => 'n'], // 동일 상품일 경우 1회만 부과 여부
            ['val' => 'areaFl', 'typ' => 's', 'def' => 'n'], // 지역별 배송비 사용여부
            ['val' => 'areaGroupNo', 'typ' => 'i', 'def' => null], // 지역별 배송비 그룹 NO
            ['val' => 'areaGroupBenefitFl', 'typ' => 's', 'def' => 'n'], // 회원등급혜택 (무료배송)적용 시 지역별추가배송비 부과 여부 (2차 이후 개발)
            ['val' => 'taxFreeFl', 'typ' => 's', 'def' => 't', 'name' => '과세/비과세/면세 여부'], // 과세/비과세/면세 여부
            ['val' => 'taxPercent', 'typ' => 'i', 'def' => '10', 'name' => '과세율'], // 과세율
            ['val' => 'unstoringFl', 'typ' => 's', 'def' => 'same'],
            ['val' => 'unstoringZipcode', 'typ' => 's', 'def' => null],
            ['val' => 'unstoringZonecode', 'typ' => 's', 'def' => null],
            ['val' => 'unstoringAddress', 'typ' => 's', 'def' => null],
            ['val' => 'unstoringAddressSub', 'typ' => 's', 'def' => null],
            ['val' => 'returnFl', 'typ' => 's', 'def' => 'same'],
            ['val' => 'returnZipcode', 'typ' => 's', 'def' => null],
            ['val' => 'returnZonecode', 'typ' => 's', 'def' => null],
            ['val' => 'returnAddress', 'typ' => 's', 'def' => null],
            ['val' => 'returnAddressSub', 'typ' => 's', 'def' => null],// 총 구매금액 기준 방법
            ['val' => 'rangeLimitFl', 'typ' => 's', 'def' => 'n'],
            ['val' => 'rangeLimitWeight', 'typ' => 'd', 'def' => '0.00'],
            ['val' => 'deliveryMethodFl', 'typ' => 's', 'def' => 'delivery'], // 배송방식
            ['val' => 'deliveryVisitPayFl', 'typ' => 's', 'def' => 'y'], // 방문수령 배송비 부과 여부
            ['val' => 'deliveryConfigType', 'typ' => 's', 'def' => 'all'], // 배송비 설정방법
            ['val' => 'dmVisitTypeFl', 'typ' => 's', 'def' => 'same'], //방문수령지 주소 타입
            ['val' => 'dmVisitTypeZonecode', 'typ' => 's', 'def' => null], // 방문수령지 구역번호
            ['val' => 'dmVisitTypeZipcode', 'typ' => 's', 'def' => null], // 방문수령지 우편번호
            ['val' => 'dmVisitTypeAddress', 'typ' => 's', 'def' => null], // 방문수령지 주소
            ['val' => 'dmVisitTypeAddressSub', 'typ' => 's', 'def' => null], // 방문수령지 상세주소
            ['val' => 'dmVisitTypeDisplayFl', 'typ' => 's', 'def' => 'n'], // 방문수령지 상품상세노출여부
            ['val' => 'dmVisitAddressUseFl', 'typ' => 's', 'def' => 'n'], // 방문수령지 상품상세노출여부
            ['val' => 'rangeRepeat', 'typ' => 's', 'def' => 'n'], // 범위반복구간여부
            ['val' => 'addGoodsCountInclude', 'typ' => 's', 'def' => 'n'], // 수량별배송비 추가옵션수량 포함여부
        ];
        // @formatter:on

        return $arrField;
    }

    /**
     * [SCM 배송] scm_delivery_charge 필드 기본값
     *
     * @author artherot
     * @return array scm_delivery_charge 테이블 필드 정보
     */
    public static function tableScmDeliveryCharge()
    {
        // @formatter:off
        $arrField = [
            ['val' => 'scmNo', 'typ' => 'i', 'def' => DEFAULT_CODE_SCMNO], // 공급사 고유 번호
            ['val' => 'basicKey', 'typ' => 'i', 'def' => '0'], // 기본 배송 정책 키
            ['val' => 'method', 'typ' => 's', 'def' => null], // 배송 방식
            ['val' => 'unitStart', 'typ' => 'd', 'def' => '0.00'], // 시작 단위
            ['val' => 'unitEnd', 'typ' => 'd', 'def' => '0.00'], // 끝 단위
            ['val' => 'price', 'typ' => 'd', 'def' => '0.00'], // 배송비
            ['val' => 'message', 'typ' => 's', 'def' => null], // 배송메세지
        ];
        // @formatter:on

        return $arrField;
    }

    /**
     * [SCM 배송] scm_delivery_add_group 필드 기본값
     *
     * @author artherot
     * @return array scm_delivery_basic 테이블 필드 정보
     */
    public static function tableScmDeliveryAreaGroup()
    {
        // @formatter:off
        $arrField = [
            ['val' => 'managerNo', 'typ' => 'i', 'def' => null], // 공급사 회원 고유 번호
            ['val' => 'scmNo', 'typ' => 'i', 'def' => DEFAULT_CODE_SCMNO], // 공급사 고유 번호
            ['val' => 'method', 'typ' => 's', 'def' => null], // 배송 방법
            ['val' => 'description', 'typ' => 's', 'def' => null], // 배송 설명
            ['val' => 'defaultFl', 'typ' => 's', 'def' => 'n'], // 상품등록시 기본노출여뷰 ('y'는 테이블내 유일무이한 값이여야 한다) // 총 구매금액 기준 방법
        ];
        // @formatter:on

        return $arrField;
    }

    /**
     * [SCM 배송] scm_delivery_add 필드 기본값
     *
     * @author artherot
     * @return array scm_delivery_add 테이블 필드 정보
     */
    public static function tableScmDeliveryArea()
    {
        // @formatter:off
        $arrField = [
            ['val' => 'scmNo', 'typ' => 'i', 'def' => DEFAULT_CODE_SCMNO], // 공급사 고유 번호
            ['val' => 'basicKey', 'typ' => 'i', 'def' => '0'], // 기본 배송 정책 키
            ['val' => 'addPrice', 'typ' => 'i', 'def' => '0'], // 추가 배송비
            ['val' => 'addArea', 'typ' => 's', 'def' => null], // 추가 배송비 지역
            ['val' => 'addAreaCode', 'typ' => 'i', 'def' => '0'], // 추가 배송비 지역코드
        ];
        // @formatter:on

        return $arrField;
    }

    /**
     * [SCM 정산] scmAdjust 필드 기본값
     *
     * @author su
     * @return array scmAdjust 테이블 필드 정보
     */
    public static function tableScmAdjust()
    {
        // @formatter:off
        $arrField = [
            ['val' => 'scmAdjustNo', 'typ' => 'i', 'def' => null], // 공급사 정산 고유번호
            ['val' => 'scmAdjustGroupNo', 'typ' => 'i', 'def' => null], // 공급사 정산 그룹 고유번호
            ['val' => 'scmNo', 'typ' => 'i', 'def' => DEFAULT_CODE_SCMNO], // 공급사 고유 번호
            ['val' => 'scmAdjustTaxBillNo', 'typ' => 'i', 'def' => null], // 공급사 정산 세금계산서 고유번호
            ['val' => 'scmAdjustKind', 'typ' => 's', 'def' => 'a'], // 공급사 정산 종류-a(자동),m(수기)
            ['val' => 'scmAdjustType', 'typ' => 's', 'def' => 'o'], // 공급사 정산 구분-o(주문상품),d(배송비),oa(정산후주문상품),da(정산후배송비)
            ['val' => 'scmAdjustTotalPrice', 'typ' => 'd', 'def' => '0.00'], // 판매금액 총 합 금액
            ['val' => 'scmAdjustCommission', 'typ' => 'i', 'def' => '0'], // 수기정산 요청용 수수료 저장
            ['val' => 'scmAdjustCommissionPrice', 'typ' => 'd', 'def' => '0.00'], // 판매수수료 금액
            ['val' => 'scmAdjustPrice', 'typ' => 'd', 'def' => '0.00'], // 정산될 금액 = 판매금액 총 금액 - 판매수수료 금액
            ['val' => 'scmAdjustCommissionTaxPrice', 'typ' => 'd', 'def' => '0.00'], // 판매수수료에 대한 공급가
            ['val' => 'scmAdjustCommissionVatPrice', 'typ' => 'd', 'def' => '0.00'], // 판매수수료에 대한 부가세
            ['val' => 'orderGoodsNo', 'typ' => 's', 'def' => null], // 정산되는 주문상품 고유번호
            ['val' => 'orderDeliveryNo', 'typ' => 's', 'def' => null], // 정산되는 주문 배송비 고유번호
            ['val' => 'scmAdjustState', 'typ' => 'i', 'def' => '1'], // 정산상태- -1(반려),1(요청),10(확정),20(세금계산서),30(완료),40(이월),50(보류)
            ['val' => 'scmAdjustCode', 'typ' => 's', 'def' => null], // 정산코드 - 관리자 노출용 코드 번호(공급사고유번호-timestamp)
            ['val' => 'scmAdjustDt', 'typ' => 's', 'def' => null], // 정산 변경 처리시간
        ];
        // @formatter:on

        return $arrField;
    }

    /**
     * [SCM 정산로그] scmAdjustLog 필드 기본값
     *
     * @author su
     * @return array scmAdjustLog 테이블 필드 정보
     */
    public static function tableScmAdjustLog()
    {
        // @formatter:off
        $arrField = [
            ['val' => 'scmAdjustLogNo', 'typ' => 'i', 'def' => null], // 공급사 정산 로그 고유번호
            ['val' => 'scmAdjustNo', 'typ' => 'i', 'def' => null], // 공급사 정산 고유번호
            ['val' => 'managerScmNo', 'typ' => 'i', 'def' => DEFAULT_CODE_SCMNO], // 변경한 manager 공급사 번호
            ['val' => 'managerNo', 'typ' => 'i', 'def' => 0], // 변경한 manager 키값
            ['val' => 'managerId', 'typ' => 's', 'def' => 'a'], // 변경한 manager 아이디
            ['val' => 'managerNm', 'typ' => 's', 'def' => 'o'], // 변경한 manager 이름
            ['val' => 'scmAdjustState', 'typ' => 'd', 'def' => '0.00'], // 변경한 공급사 정산상태- -1(반려),1(요청),10(확정),20(세금계산서),30(완료),40(이월),50(보류)
            ['val' => 'scmAdjustMemo', 'typ' => 's', 'def' => null], // 공급사 정산 메모
        ];
        // @formatter:on

        return $arrField;
    }

    /**
     * [SCM 정산] scmAdjustTaxBill 필드 기본값
     *
     * @author su
     * @return array scmAdjustTaxBill 테이블 필드 정보
     */
    public static function tableScmAdjustTaxBill()
    {
        // @formatter:off
        $arrField = [
            ['val' => 'scmAdjustTaxBillNo', 'typ' => 'i', 'def' => null], // 공급사 정산 세금계산서 고유번호
            ['val' => 'scmNo', 'typ' => 'i', 'def' => null], // 공급사 고유번호
            ['val' => 'scmCompanyNm', 'typ' => 's', 'def' => ''], // 공급사 회사명
            ['val' => 'scmCeoNm', 'typ' => 's', 'def' => ''], // 공급사 대표자명
            ['val' => 'scmBusinessNo', 'typ' => 's', 'def' => ''], // 공급사 사업자등록번호
            ['val' => 'scmService', 'typ' => 's', 'def' => ''], // 공급사 업태
            ['val' => 'scmItem', 'typ' => 's', 'def' => ''], // 공급사 종목
            ['val' => 'scmZipcode', 'typ' => 's', 'def' => ''], // 공급사 우편번호
            ['val' => 'scmZoneCode', 'typ' => 's', 'def' => ''], // 공급사 우편번호
            ['val' => 'scmAddress', 'typ' => 's', 'def' => ''], // 공급사 주소
            ['val' => 'scmAddressSub', 'typ' => 's', 'def' => ''], // 공급사 주소
            ['val' => 'scmAdjustTaxBillType', 'typ' => 's', 'def' => 'basic'], // 공급사 정산 세금계산서 타입 - basic 일반 / godo 전자
            ['val' => 'scmAdjustTaxBillState', 'typ' => 's', 'def' => 'y'], // 공급사 정산 세금계산서 상태 - y 발행 / c 취소
            ['val' => 'scmAdjustTaxPrice', 'typ' => 'd', 'def' => '0.00'], // 공급사 정산 세금계산서 공급가액
            ['val' => 'scmAdjustVatPrice', 'typ' => 'd', 'def' => '0.00'], // 공급사 정산 세금계산서 부가세액
            ['val' => 'scmAdjustTaxBillDt', 'typ' => 's', 'def' => null], // 공급사 정산 세금계산서 발행일
        ];
        // @formatter:on

        return $arrField;
    }

    /**
     * [안내문] buyer_inform 필드 기본값
     *
     * @author sunny
     * @return array buyer_inform 테이블 필드 정보
     */
    public static function tableBuyerInform()
    {
        // @formatter:off
        $arrField = [
            ['val' => 'informCd', 'typ' => 's', 'def' => null], // 안내문코드
            ['val' => 'scmNo', 'typ' => 'i', 'def' => (string)Session::get('manager.scmNo')], // 공급사 고유 번호
            ['val' => 'groupCd', 'typ' => 's', 'def' => null], // 그룹코드
            ['val' => 'informNm', 'typ' => 's', 'def' => null], // 안내문명
            ['val' => 'modeFl', 'typ' => 's', 'def' => 'y'], // 모드 사용여부
            ['val' => 'scmModeFl', 'typ' => 's', 'def' => 'n'], // 공급사  사용여부
            ['val' => 'scmDisplayFl', 'typ' => 's', 'def' => 'n'], // 모드 사용여부
            ['val' => 'content', 'typ' => 's', 'def' => null], // 내용
            ['val' => 'displayShopFl', 'typ' => 's', 'def' => 'n'], // 약관 노출 여부
        ];
        // @formatter:on

        return $arrField;
    }

    /**
     * [회원] member 필드 기본값
     *
     * @author sunny
     * @return array member 테이블 필드 정보
     */
    public static function tableMember()
    {
        return \Component\Database\MemberTableField::getInstance()->tableMember();
    }

    /**
     * [회원] member_group 필드 기본값
     *
     * @author sunny
     * @return array member_group 테이블 필드 정보
     */
    public static function tableMemberGroup()
    {
        return \Component\Database\MemberTableField::getInstance()->tableMemberGroup();
    }

    /**
     * [회원] member_hackout 필드 기본값
     *
     * @author sunny
     * @return array member_hackout 테이블 필드 정보
     */
    public static function tableMemberHackout()
    {
        return \Component\Database\MemberTableField::getInstance()->tableMemberHackout();
    }

    /**
     * [회원] member_mileage 필드 기본값
     *
     * @author artherot
     * @return array member_mileage 테이블 필드 정보
     */
    public static function tableMemberMileage()
    {
        return \Component\Database\MemberTableField::getInstance()->tableMemberMileage();
    }

    /**
     * [회원] es_memberNotificationLog 필드 기본값
     *
     * @static
     * @return array
     */
    public static function tableMemberNotificationLog()
    {
        return \Component\Database\MemberTableField::getInstance()->tableMemberNotificationLog();
    }

    /**
     * [회원] member_deposit 필드 기본값
     *
     * @author yjwee
     * @return array member_deposit 테이블 필드 정보
     */
    public static function tableMemberDeposit()
    {
        return \Component\Database\MemberTableField::getInstance()->tableMemberDeposit();
    }

    /**
     * [회원] MEMBERCOUPON
     *
     * @author su
     * @return array MEMBERCOUPON
     */
    public static function tableMemberCoupon()
    {
        return \Component\Database\MemberTableField::getInstance()->tableMemberCoupon();
    }

    /**
     * [회원] member_sns 필드 기본값
     *
     * @static
     */
    public static function tableMemberSns()
    {
        return \Component\Database\MemberTableField::getInstance()->tableMemberSns();
    }

    /**
     * [회원] es_memberSleep 필드 기본값
     *
     * @author yjwee
     * @return array es_memberSleep 테이블 필드 정보
     */
    public static function tableMemberSleep()
    {
        return \Component\Database\MemberTableField::getInstance()->tableMemberSleep();
    }

    /**
     * es_memberStatistics 필드 기본값
     *
     * @static
     */
    public static function tableMemberStatistics()
    {
        return \Component\Database\MemberTableField::getInstance()->tableMemberStatistics();
    }

    /**
     * [통계] es_memberStatisticsDay 필드 기본 값
     *
     * @static
     * @return array 테이블 필드 정보
     */
    public static function tableMemberStatisticsDay()
    {
        return \Component\Database\MemberTableField::getInstance()->tableMemberStatisticsDay();
    }

    /**
     * [회원] es_memberHistory 필드 기본값
     *
     * @author yjwee
     * @return array es_memberHistory 테이블 필드 정보
     */
    public static function tableMemberHistory()
    {
        return \Component\Database\MemberTableField::getInstance()->tableMemberHistory();
    }

    /**
     * [회원] es_memberInvoiceInfo 필드 기본값
     *
     * @author haky
     * @return array es_memberInvoiceInfo 테이블 필드 정보
     */
    public static function tableMemberInvoiceInfo()
    {
        return \Component\Database\MemberTableField::getInstance()->tableMemberInvoiceInfo();
    }

    /**
     * [회원] member_loginlog 필드 기본값
     *
     * @author sunny
     * @return array member_loginlog 테이블 필드 정보
     */
    public static function tableMemberLoginlog()
    {
        return \Component\Database\MemberTableField::getInstance()->tableMemberLoginlog();
    }

    /**
     * [회원] memberOrderGoodsCount 필드 기본값
     *
     * @author nokoon
     * @return array memberOrderGoodsCount 테이블 필드 정보
     */
    public static function tableMemberOrderGoodsCount()
    {
        // @formatter:off
        $arrField = [
            ['val' => 'memNo', 'typ' => 'i', 'def' => 0], // 회원No
            ['val' => 'goodsNo', 'typ' => 'i', 'def' => 0], // 상품No
            ['val' => 'orderCount', 'typ' => 'i', 'def' => 0], // 구매카운트
        ];
        // @formatter:on

        return $arrField;
    }

    /**
     * [회원] memberOrderGoodsCountLog 필드 기본값
     *
     * @author nokoon
     * @return array memberOrderGoodsCountLog 테이블 필드 정보
     */
    public static function tableMemberOrderGoodsCountLog()
    {
        // @formatter:off
        $arrField = [
            ['val' => 'memNo', 'typ' => 'i', 'def' => 0], // 회원No
            ['val' => 'goodsNo', 'typ' => 'i', 'def' => 0], // 상품No
            ['val' => 'orderCountBefore', 'typ' => 'i', 'def' => 0], // 구매카운트 업데이트전
            ['val' => 'orderCountAfter', 'typ' => 'i', 'def' => 0], // 구매카운트 업데이트후
            ['val' => 'orderNo', 'typ' => 's', 'def' => ''], // 주문번호
            ['val' => 'acceptIp', 'typ' => 's', 'def' => ''], // 접근IP
        ];
        // @formatter:on

        return $arrField;
    }

    /**
     * [회원] member_simpleJoin 필드 기본값
     *
     * @author sunny
     * @return array member_simpleJoin 테이블 필드 정보
     */
    public static function tableMemberSimpleJoinLog()
    {
        return \Component\Database\MemberTableField::getInstance()->tableMemberSimpleJoinLog();
    }

    /**
     * [회원] member_simpleJoinPush 필드 기본값
     *
     * @author sunny
     * @return array member_simpleJoin 테이블 필드 정보
     */
    public static function tableMemberSimpleJoinPushLog()
    {
        return \Component\Database\MemberTableField::getInstance()->tableMemberSimpleJoinPushLog();
    }

    /**
     * [게시판] board 필드 기본값
     *
     * @author sunny
     * @return array board 테이블 필드 정보
     */
    public static function tableBoard()
    {
        // @formatter:off
        $arrField = [
            ['val' => 'bdId', 'typ' => 's', 'def' => null], // 게시판아이디
            ['val' => 'bdNm', 'typ' => 's', 'def' => null], // 게시판이름
            ['val' => 'bdKind', 'typ' => 's', 'def' => ''], // 게시판 유형(겔러리,일반,이벤트,문의)
            ['val' => 'themeSno', 'typ' => 'i', 'def' => ''], // 게시판스킨고유번호
            ['val' => 'mobileThemeSno', 'typ' => 'i', 'def' => ''], // 게시판모바일스킨고유번호
            ['val' => 'themeUsSno', 'typ' => 'i', 'def' => ''], // 게시판영문스킨고유번호
            ['val' => 'mobileThemeUsSno', 'typ' => 'i', 'def' => ''], // 게시판영문모바일스킨고유번호
            ['val' => 'themeJpSno', 'typ' => 'i', 'def' => ''], // 게시판일문스킨고유번호
            ['val' => 'mobileThemeJpSno', 'typ' => 'i', 'def' => ''], // 게시판일문모바일스킨고유번호
            ['val' => 'themeCnSno', 'typ' => 'i', 'def' => ''], // 게시판중문스킨고유번호
            ['val' => 'mobileThemeCnSno', 'typ' => 'i', 'def' => ''], // 게시판중문모바일스킨고유번호
            ['val' => 'bdNewFl', 'typ' => 'i', 'def' => 24], // 새글 지속 시간
            ['val' => 'bdHotFl', 'typ' => 'i', 'def' => 100], // 인기글 조회수
            ['val' => 'bdIpFl', 'typ' => 's', 'def' => 'n'], // 아이피출력유무
            ['val' => 'bdIpFilterFl', 'typ' => 's', 'def' => 'n'], // 아이피별표유무
            ['val' => 'bdListInView', 'typ' => 's', 'def' => 'y'], // 상세보기타입
            ['val' => 'bdLinkFl', 'typ' => 's', 'def' => 'n'], // 링크사용유무
            ['val' => 'bdUploadStorage', 'typ' => 's', 'def' => 'local'], // 업로드파일저장소
            ['val' => 'bdUploadPath', 'typ' => 's', 'def' => null], // 업로드파일경로
            ['val' => 'bdUploadThumbPath', 'typ' => 's', 'def' => null], // 업로드파일경로(썸네일이미지)
            ['val' => 'bdUploadMaxSize', 'typ' => 'i', 'def' => str_replace('M', '', ini_get('upload_max_filesize'))], // 업로드최대파일사이즈
            ['val' => 'bdHeader', 'typ' => 's', 'def' => null], // 헤더
            ['val' => 'bdFooter', 'typ' => 's', 'def' => null], // 푸터
            ['val' => 'bdCategoryFl', 'typ' => 's', 'def' => 'n'], // 말머리사용유무
            ['val' => 'bdCategoryTitle', 'typ' => 's', 'def' => null], // 말머리타이틀
            ['val' => 'bdCategory', 'typ' => 's', 'def' => null], // 말머리
            ['val' => 'bdCategoryTemplateSno', 'typ' => 's', 'def' => null], // 말머리 양식
            ['val' => 'bdPcHitFl', 'typ' => 's', 'def'=>'y'],  //PC쇼핑몰 조회수 표시유무
            ['val' => 'bdMobileHitFl', 'typ' => 's', 'def'=>'y'],  //모바일쇼핑몰 조회수 표시유무
            ['val' => 'bdMemoFl', 'typ' => 's', 'def' => 'n'], // 코멘트사용유무
            ['val' => 'bdUserDsp', 'typ' => 's', 'def' => '0'], // 작성자표시
            ['val' => 'bdAdminDsp', 'typ' => 's', 'def' => '0'], // 관리자표시
            ['val' => 'bdSpamMemoFl', 'typ' => 'i', 'def' => null], // 코멘트 스팸방지
            ['val' => 'bdSpamBoardFl', 'typ' => 'i', 'def' => null], // 게시글 스팸방지
            ['val' => 'bdSecretFl', 'typ' => 's', 'def' => '0'], // 비밀글 설정
            ['val' => 'bdSecretTitleFl', 'typ' => 's', 'def' => '0'], // 비밀글 제목설정
            ['val' => 'bdSecretTitleTxt', 'typ' => 's', 'def' => null], // 비밀글 노출제목
            ['val' => 'bdSecretReplyFl', 'typ' => 's', 'def' => '0'], // 비밀댓글 설정, 0 : 기본 일반댓글, 1 : 기본 비밀댓글, 2 : 무조건 일반댓글, 3 : 무조건 비밀댓글
            ['val' => 'bdSecretReplyTitle', 'typ' => 's', 'def' => null], // 비밀댓글 노출제목
            ['val' => 'bdMobileFl', 'typ' => 's', 'def' => 'n'], // 휴대폰작성
            ['val' => 'bdEmailFl', 'typ' => 's', 'def' => 'y'], // 이메일작성
            ['val' => 'bdCaptchaBgClr', 'typ' => 's', 'def' => 'FFFFFF'], // Captcha 배경색상
            ['val' => 'bdCaptchaClr', 'typ' => 's', 'def' => '252525'], // Captcha 글자색상
            ['val' => 'bdHitPerCnt', 'typ' => 'i', 'def' => 1], // 조회당증가수
            ['val' => 'bdHitIPCheck', 'typ' => 's', 'def' => 'n'], // 조회수 IP 중복제한
            ['val' => 'bdStartNum', 'typ' => 'i', 'def' => 1], // 게시물 시작번호
//            ['val' => 'bdAlertFl', 'typ' => 's', 'def' => 'n'], // 새글알림여부
//            ['val' => 'bdAlertMobile', 'typ' => 's', 'def' => null], // 새글알림받을관리자
//            ['val' => 'bdAlertMsg', 'typ' => 's', 'def' => null], // 새글알림받을관리자
            ['val' => 'bdSubjectLength', 'typ' => 'i', 'def' => null], // 제목글 제한
            ['val' => 'bdListCount', 'typ' => 'i', 'def' => 10], // 페이지당 pc 게시글 수
            ['val' => 'bdListColsCount', 'typ' => 'i', 'def' => 5], // 페이지당 모바일 게시글 수
            ['val' => 'bdListRowsCount', 'typ' => 'i', 'def' => 5], // 페이지당 모바일 게시글 수
            ['val' => 'bdListImageFl', 'typ' => 's', 'def' => 'n'], // 대표이미지사용여부
            ['val' => 'bdUserLimitDsp', 'typ' => 'i', 'def' => '0'], // 작성자 노출제한
            ['val' => 'bdListImageTarget', 'typ' => 's', 'def' => null], //대표이미지 설정
            ['val' => 'bdListImageSize', 'typ' => 's', 'def' => null], //리스트 이미지크기
            ['val' => 'bdEndEventMsg', 'typ' => 's', 'def' => null], // 종료된 이벤트 처리
            ['val' => 'bdTemplateSno', 'typ' => 'i', 'def' => null], //글양식
            ['val' => 'bdGoodsPtFl', 'typ' => 's', 'def' => 'n'], //점수사용여부
            ['val' => 'bdSnsFl', 'typ' => 's', 'def' => 'n'], //sns 사용여부
            ['val' => 'bdRecommendFl', 'typ' => 's', 'def' => 'n'], //추천사용여부
            ['val' => 'bdGoodsFl', 'typ' => 's', 'def' => 'n'], //상품 사용여부
            ['val' => 'bdSubSubjectFl', 'typ' => 's', 'def' => 'n'], //부가제목 사용여부
            ['val' => 'bdSupplyDsp', 'typ' => 's', 'def' => null], //공급사 표시방법
            ['val' => 'bdUploadFl', 'typ' => 's', 'def' => null], //업로드 여부
            ['val' => 'bdMileageFl', 'typ' => 's', 'def' => null], //마일리지 사용유무
            ['val' => 'bdMileageAmount', 'typ' => 'i', 'def' => null], //마일리지 지급
            ['val' => 'bdMileageDeleteFl', 'typ' => 's', 'def' => 'n'], //게시글 삭제 시 마일리지 차감
            ['val' => 'bdMileageLackAction', 'typ' => 's', 'def' => null],
            ['val' => 'bdEditorFl', 'typ' => 's', 'def' => 'y'], //에디터사용여부
            ['val' => 'bdBasicFl', 'typ' => 's', 'def' => 'n'],    //기본제공여부
            ['val' => 'bdReplyStatusFl', 'typ' => 's', 'def' => 'n'],   //답변상태여부
            ['val' => 'bdEventFl', 'typ' => 's', 'def' => 'n'],   //이벤트연동여부
            ['val' => 'bdUsePcFl', 'typ' => 's', 'def' => 'y'],   //PC쇼핑몰 사용여부
            ['val' => 'bdUseMobileFl', 'typ' => 's', 'def' => 'y'],   //모바일쇼핑몰 사용여부
            ['val' => 'bdAuthList', 'typ' => 's', 'def' => ''],   //리스트권한 사용여부
            ['val' => 'bdAuthRead', 'typ' => 's', 'def' => ''],   //읽기권한 사용여부
            ['val' => 'bdAuthWrite', 'typ' => 's', 'def' => ''],   //쓰기권한 사용여부
            ['val' => 'bdReplyFl', 'typ' => 's', 'def' => ''],   //답글 사용여부
            ['val' => 'bdAuthReply', 'typ' => 's', 'def' => ''],   //답글 권한
            ['val' => 'bdAuthMemo', 'typ' => 's', 'def' => ''],   //댓글 권한
            ['val' => 'bdAuthListGroup', 'typ' => 's', 'def' => ''],   //리스트 특정회원등급
            ['val' => 'bdAuthReadGroup', 'typ' => 's', 'def' => ''],   //읽기 특정회원등급
            ['val' => 'bdAuthWriteGroup', 'typ' => 's', 'def' => ''],   //쓰기 특정회원등급
            ['val' => 'bdAuthReplyGroup', 'typ' => 's', 'def' => ''],   //답글 특정회원등급
            ['val' => 'bdAuthMemoGroup', 'typ' => 's', 'def' => ''],   //댓글 특정회원등급
            ['val' => 'bdAllowTags', 'typ' => 's', 'def' => ''],   //허용태그(구분자포함)
            ['val' => 'bdAllowDomain', 'typ' => 's', 'def' => ''],   //허용도메인(구분자포함)
            ['val' => 'bdGoodsType', 'typ' => 's', 'def'=>'n'],  //상품연동 타입(상품,상품주문,상품주문중복허용)
            ['val' => 'bdAttachImageDisplayFl', 'typ' => 's', 'def'=>'y'],  //첨부파일 이미지인경우 본문노출여부
            ['val' => 'bdAttachImageMaxSize', 'typ' => 'i', 'def'=>'700'],  //첨부파일 이미지 max 사이즈
            ['val' => 'bdAttachImagePosition', 'typ' => 's', 'def'=>'top'],  //첨부파일 이미지 노출위치
            ['val' => 'bdNoticeDisplay', 'typ' => 's', 'def'=>'3'.STR_DIVISION.'y'.STR_DIVISION.'y'],  //공지사항노출설정 (노출개수^|^리스트노출여부^|^첫페이지노출여부)
            ['val' => 'bdGoodsPageCountPc', 'typ' => 'i', 'def'=>10],  //상품상세페이지 내 페이지별 게시물 수 (pc)
            ['val' => 'bdGoodsPageCountMobile', 'typ' => 'i', 'def'=>5],  //상품상세페이지 내 페이지별 게시물 수 (mobile)
            ['val' => 'bdIncludeReplayInSearchFl', 'typ' => 's', 'def'=>'n'],  //검색 시 답변글 노출여부
            ['val' => 'bdIncludeReplayInSearchType', 'typ' => 'i', 'def'=>0],  //검색 시 답변글 노출여부 범위
            ['val' => 'bdListNoticeImageDisplayPc', 'typ' => 's', 'def'=>'y'],  //공지글 이미지 노출 여부 PC
            ['val' => 'bdListNoticeImageDisplayMobile', 'typ' => 's', 'def'=>'y'],  //공지글 이미지 노출 여부 모바일

            ['val' => 'bdReviewAuthWrite', 'typ' => 's', 'def'=>''],  //쓰기권한 추가기준
            ['val' => 'bdReviewOrderStatus', 'typ' => 's', 'def'=>''],  //작성가능 시점
            ['val' => 'bdReviewPeriod', 'typ' => 'i', 'def'=>0],  //후기작성기간
            ['val' => 'bdReviewDuplicateLimit', 'typ' => 's', 'def'=>''],  //중복작성 제한
            ['val' => 'bdReviewExceptGoodsNo', 'typ' => 's', 'def'=>''],  //후기작성 예외상품
            ['val' => 'seoTagFl', 'typ' => 's', 'def' => 'n',], // 상품 개별 SEO 태그 설정
            ['val' => 'seoTagSno', 'typ' => 'i', 'def' => '0', ], // 상품 개별 SEO 태그 고유번호
            ['val' => 'bdAnswerStatusFl', 'typ' => 's', 'def' => 'n'],   //답변관리기능 사용유무
            ['val' => 'bdReplyDelFl', 'typ' => 's', 'def' => 'applicable'],   //게시글 삭제 설정 (applicable : 해당글만삭제 / reply : 답변글도 함께 삭제)
            ['val' => 'bdReplyMileageFl', 'typ' => 's', 'def' => 'n'],   //답변글 작성 시에도 마일리지 지급
            ['val' => 'bdPasswordMemoFl', 'typ' => 's', 'def' => 'y'], // 비밀댓글 암호보안
        ];
        // @formatter:on

        return $arrField;
    }

    /**
     * [게시판] 게시판 필드 기본값
     *
     * @author sj
     * @return array board 테이블 필드 정보
     */
    public static function tableBd()
    {
        // @formatter:off
        $arrField = [
            // array('val' => 'idx', 'typ' => 's', 'def' => null), // 그룹핑번호
            ['val' => 'groupNo', 'typ' => 'i', 'def' => null], // 순번
            ['val' => 'groupThread', 'typ' => 's', 'def' => ''], // 답변
            ['val' => 'writerNm', 'typ' => 's', 'def' => null], // 작성자명
            ['val' => 'writerId', 'typ' => 's', 'def' => null], // 작성자아이디
            ['val' => 'channel', 'typ' => 's', 'def' => null], // 채널
            ['val' => 'apiExtraData', 'typ' => 's', 'def' => null], // api연동데이터
            ['val' => 'writerNick', 'typ' => 's', 'def' => null], // 작성자닉네임
            ['val' => 'writerEmail', 'typ' => 's', 'def' => null], // 이메일
            ['val' => 'writerHp', 'typ' => 's', 'def' => null], // 홈페이지
            ['val' => 'subject', 'typ' => 's', 'def' => null], // 글제목
            ['val' => 'subSubject', 'typ' => 's', 'def' => null], // 글제목
            ['val' => 'contents', 'typ' => 's', 'def' => null], // 글내용
            ['val' => 'urlLink', 'typ' => 's', 'def' => null], // url
            ['val' => 'uploadFileNm', 'typ' => 's', 'def' => null], // 원본이미지파일명
            ['val' => 'saveFileNm', 'typ' => 's', 'def' => null], // 저장이미지파일명
            ['val' => 'writerPw', 'typ' => 's', 'def' => null], // 비밀번호
            ['val' => 'memNo', 'typ' => 'i', 'def' => 0], // 작성자번호
            ['val' => 'parentSno', 'typ' => 'i', 'def' => 0], // 원글의일련번호
            ['val' => 'writerIp', 'typ' => 's', 'def' => ''], // 아이피
            ['val' => 'isNotice', 'typ' => 's', 'def' => 'n'], // 공지여부
            ['val' => 'isSecret', 'typ' => 's', 'def' => 'n'], // 비밀글여부
            ['val' => 'hit', 'typ' => 'i', 'def' => 0], // 조회수
            ['val' => 'memoCnt', 'typ' => 'i', 'def' => null], // 코멘트수
            ['val' => 'category', 'typ' => 's', 'def' => null], // 게시판카테고리
            ['val' => 'writerMobile', 'typ' => 's', 'def' => null], // 작성자 휴대폰
            ['val' => 'goodsNo', 'typ' => 'i', 'def' => 0], // 상품번호
            ['val' => 'goodsPt', 'typ' => 's', 'def' => null], // 상품평점
            ['val' => 'orderNo', 'typ' => 's', 'def' => null], // 주문번호
            ['val' => 'mileage', 'typ' => 'i', 'def' => null], // 마일리지
            ['val' => 'mileageReason', 'typ' => 's', 'def' => null], // 마일리지지급이유
            ['val' => 'isDelete', 'typ' => 's', 'def' => 'n'],
            ['val' => 'recommend', 'typ' => 'i', 'def' => 0],  //추천수
            ['val' => 'replyStatus', 'typ' => 's', 'def' => '0'],  //답변코드
            ['val' => 'eventStart', 'typ' => 's', 'def' => null],  //이벤트 시작일
            ['val' => 'eventEnd', 'typ' => 's', 'def' => null],  //이벤트 종료일
            ['val' => 'bdUploadStorage', 'typ' => 's', 'def' => null],  //저장소
            ['val' => 'bdUploadPath', 'typ' => 's', 'def' => null],  //업로드경로
            ['val' => 'bdUploadThumbPath', 'typ' => 's', 'def' => null],  //섬네일 업로드경로
            ['val' => 'isMobile', 'typ' => 's', 'def' => 'n'],  //모바일 여부
            ['val' => 'answerSubject', 'typ' => 's', 'def' => 'n'],  //답변제목
            ['val' => 'answerContents', 'typ' => 's', 'def' => 'n'],  //답변내용
            ['val' => 'answerManagerNo', 'typ' => 'i', 'def' => 'n'],  //답변관리자번호
            ['val' => 'answerModDt', 'typ' => 's', 'def' => 'n'],  //답변날짜
//            ['val' => 'channel', 'typ' => 's', 'def' => ''],  //채널
//            ['val' => 'apiExtraData', 'typ' => 's', 'def' => ''],  //api데이터
        ];
        // @formatter:on

        return $arrField;
    }

    /**
     * [게시판] 게시판 필드 기본값
     *
     * @author sj
     * @return array board 테이블 필드 정보
     */
    public static function tableBoardRecommend()
    {
        // @formatter:off
        $arrField = [
            ['val' => 'bdId', 'typ' => 's', 'def' => null], // 순번
            ['val' => 'bdSno', 'typ' => 'i', 'def' => null], // 순번
            ['val' => 'memNo', 'typ' => 'i', 'def' => null], // 순번
            ['val' => 'writerIp', 'typ' => 's', 'def' => null], // 순번
            ['val' => 'regDt', 'typ' => 's', 'def' => null], // 등록일
        ];
        // @formatter:on

        return $arrField;
    }

    /**
     * [게시판] 게시판 댓글 필드 기본값
     *
     * @author sj
     * @return array board 테이블 필드 정보
     */
    public static function tableBdMemo()
    {
        // @formatter:off
        $arrField = [
            ['val' => 'bdId', 'typ' => 's', 'def' => null], // 게시판 id
            ['val' => 'bdSno', 'typ' => 'i', 'def' => null], //
            ['val' => 'writerId', 'typ' => 's', 'def' => null], // 작성자아이디
            ['val' => 'writerNm', 'typ' => 's', 'def' => null], // 작성자명
            ['val' => 'writerNick', 'typ' => 's', 'def' => null], // 작성자닉네임
            ['val' => 'memo', 'typ' => 's', 'def' => null], // 내용
            ['val' => 'writerPw', 'typ' => 's', 'def' => null], // 비밀번호
            ['val' => 'memNo', 'typ' => 'i', 'def' => null], // 작성자no
            ['val' => 'mileage', 'typ' => 'i', 'def' => null], // 마일리지
            ['val' => 'mileageReason', 'typ' => 's', 'def' => null], // 마일리지지급이유
            ['val' => 'groupThread', 'typ' => 's', 'def' => null],
            ['val' => 'groupNo', 'typ' => 'i', 'def' => null],
            ['val' => 'isSecretReply', 'typ' => 's', 'def' => 'n'],
        ];
        // @formatter:on

        return $arrField;
    }

    /**
     * [게시판] board_theme 필드 기본값
     *
     * @author sunny
     * @return array board_theme 테이블 필드 정보
     */
    public static function tableBoardTheme()
    {
        // @formatter:off
        $arrField = [
            ['val' => 'themeId', 'typ' => 's', 'def' => null], // 게시판 스킨아이디
            ['val' => 'themeNm', 'typ' => 's', 'def' => null], // 게시판 스킨이름
            ['val' => 'liveSkin' , 'typ'=>'s' , 'def'=>null],   //디자인 스킨
            ['val' => 'bdKind', 'typ' => 's', 'def' => null], // 게시판유형
            ['val' => 'bdAlign', 'typ' => 's', 'def' => null], // 정렬
            ['val' => 'bdWidth', 'typ' => 'i', 'def' => null], // 넓이
            ['val' => 'bdWidthUnit', 'typ' => 's', 'def' => null], // 넓이 단위
            ['val' => 'bdListLineSpacing', 'typ' => 'i', 'def' => null], // 줄간격
            ['val' => 'bdBasicFl', 'typ' => 's', 'def' => 'n'], // 기본제공 스킨
            ['val' => 'bdMobileFl', 'typ' => 's', 'def' => 'n'], // 모바일사용 여부
        ];
        // @formatter:on

        return $arrField;
    }

    public static function tableBoardExtraData()
    {
        // @formatter:off
        $arrField = [
            ['val' => 'bdId', 'typ' => 's', 'def' => null], // 게시판 아이디
            ['val' => 'bdSno', 'typ' => 's', 'def' => null], // 게시판 고유번호
            ['val' => 'goodsNoText' , 'typ'=>'s' , 'def'=>null],   //상품번호(구분자포함)
            ['val' => 'orderGoodsNoText', 'typ' => 's', 'def' => null], // 상품주문번호(구분자포함)
            ['val' => 'writeType', 'typ' => 's', 'def' => null], // 작성경로
        ];
        // @formatter:on

        return $arrField;
    }
    /**
     * [게시판] board_hit_ip 필드 정보
     *
     * @author cjb3333
     * @return array board_hit_ip 테이블 필드 정보
     */
    public static function tableBoardHitIp()
    {
        // @formatter:off
        $arrField = [
            ['val' => 'bdId', 'typ' => 's', 'def' => null], // 게시판 아이디
            ['val' => 'bdSno', 'typ' => 's', 'def' => null], // 게시글 고유번호
            ['val' => 'ip' , 'typ'=>'s' , 'def'=>null],   //아이피
        ];
        // @formatter:on

        return $arrField;
    }

    /**
     * [디자인] design_multi_popup 필드 기본값
     *
     * @author artherot
     * @return array design_multi_popup 테이블 필드 정보
     */
    public static function tableDesignMultiPopup()
    {
        // @formatter:off
        $arrField = [
            ['val' => 'sno', 'typ' => 'i', 'def' => null], // 일련번호
            ['val' => 'pcDisplayFl', 'typ' => 's', 'def' => 'y'], // pc쇼핑몰 노출 여부
            ['val' => 'mobileDisplayFl', 'typ' => 's', 'def' => 'n'], // 모바일쇼핑몰 노출 여부
            ['val' => 'popupTitle', 'typ' => 's', 'def' => null], // 팝업 제목
            ['val' => 'popupUseFl', 'typ' => 's', 'def' => 'y'], // 팝업 사용 여부
            ['val' => 'popupKindFl', 'typ' => 's', 'def' => 'layer'], // 창 종류
            ['val' => 'popupSkin', 'typ' => 's', 'def' => 'layer'], // 팝업창 스킨즈
            ['val' => 'popupPositionT', 'typ' => 'i', 'def' => '0'], // 팝업 상단 위치
            ['val' => 'popupPositionL', 'typ' => 'i', 'def' => '0'], // 팝업 왼쪽 위치
            ['val' => 'popupPeriodOutputFl', 'typ' => 's', 'def' => 'n'], // 기간별 노출 설정
            ['val' => 'popupPeriodSDate', 'typ' => 's', 'def' => null], // 시작 날짜
            ['val' => 'popupPeriodSTime', 'typ' => 's', 'def' => null], // 시작 시간
            ['val' => 'popupPeriodEDate', 'typ' => 's', 'def' => null], // 종료 날짜
            ['val' => 'popupPeriodETime', 'typ' => 's', 'def' => null], // 종료 시간
            ['val' => 'todayUnSeeFl', 'typ' => 's', 'def' => 'n'], // 오늘 하루 보이지 않음 여부
            ['val' => 'todayUnSeeBgColor', 'typ' => 's', 'def' => null], // 배경 색상
            ['val' => 'todayUnSeeFontColor', 'typ' => 's', 'def' => null], // 폰트 색상
            ['val' => 'todayUnSeeAlign', 'typ' => 's', 'def' => 'right'], // 정렬 위치
            ['val' => 'popupPageUrl', 'typ' => 's', 'def' => '/index.php'], // 팝업 노출 페이지
            ['val' => 'popupPageParam', 'typ' => 's', 'def' => null], // 팝업 노출 페이지 파라메터
            ['val' => 'mobilePopupPageUrl', 'typ' => 's', 'def' => 'main/index.php'], // 모바일 팝업 노출 페이지
            ['val' => 'mobilePopupPageParam', 'typ' => 's', 'def' => null], // 모바일 팝업 노출 페이지 파라메터
            ['val' => 'popupImageInfo', 'typ' => 's', 'def' => null], // 팝업이미지정보
            ['val' => 'popupSlideDirection', 'typ' => 's', 'def' => 'left'], // 큰이미지 이동방향
            ['val' => 'popupSlideSpeed', 'typ' => 's', 'def' => '4'], // 큰이미지 이동속도
            ['val' => 'popupSlideCount', 'typ' => 's', 'def' => '21'], //이미지개수
            ['val' => 'popupSlideViewW', 'typ' => 's', 'def' => '0'], // 큰이미지가로
            ['val' => 'popupSlideViewH', 'typ' => 's', 'def' => '0'], // 큰이미지세로
            ['val' => 'popupSlideThumbH', 'typ' => 's', 'def' => '0'], // 작은이미지세로
            ['val' => 'modDt', 'typ' => 's', 'def' => null], // 수정일
            ['val' => 'regDt', 'typ' => 's', 'def' => null], // 등록일
        ];
        // @formatter:on

        return $arrField;
    }

    /**
     * [디자인] design_popup 필드 기본값
     *
     * @author artherot
     * @return array design_popup 테이블 필드 정보
     */
    public static function tableDesignPopup()
    {
        // @formatter:off
        $arrField = [
            ['val' => 'sno', 'typ' => 'i', 'def' => null], // 일련번호
            ['val' => 'mallDisplay', 'typ' => 's', 'def' => 1], // 글로벌상점기준몰sno
            ['val' => 'pcDisplayFl', 'typ' => 's', 'def' => 'y'], // pc쇼핑몰 노출 여부
            ['val' => 'mobileDisplayFl', 'typ' => 's', 'def' => 'n'], // 모바일쇼핑몰 노출 여부
            ['val' => 'popupTitle', 'typ' => 's', 'def' => null], // 팝업 제목
            ['val' => 'popupUseFl', 'typ' => 's', 'def' => 'y'], // 팝업 사용 여부
            ['val' => 'popupKindFl', 'typ' => 's', 'def' => 'layer'], // 창 종류
            ['val' => 'popupSkin', 'typ' => 's', 'def' => 'layer'], // 팝업창 스킨
            ['val' => 'popupContent', 'typ' => 's', 'def' => null], // 팝업 내용
            ['val' => 'popupSizeW', 'typ' => 'i', 'def' => 300], // 팝업 가로 사이즈
            ['val' => 'popupSizeH', 'typ' => 'i', 'def' => 300], // 팝업 세로 사이즈
            ['val' => 'sizeTypeW', 'typ' => 's', 'def' => 'px'], // 팝업 가로 사이즈 타입
            ['val' => 'sizeTypeH', 'typ' => 's', 'def' => 'px'], // 팝업 세로 사이즈 타입
            ['val' => 'contentImgFl', 'typ' => 's', 'def' => null], // 에디터 이미지 강제조정 여부
            ['val' => 'popupPositionT', 'typ' => 'i', 'def' => '0'], // 팝업 상단 위치
            ['val' => 'popupPositionL', 'typ' => 'i', 'def' => '0'], // 팝업 왼쪽 위치
            ['val' => 'popupBgColor', 'typ' => 's', 'def' => null], // 팝업 배경색상
            ['val' => 'mobilePopupKindFl', 'typ' => 's', 'def' => 'layer'], // 모바일 창 종류
            ['val' => 'mobilePopupSkin', 'typ' => 's', 'def' => 'layer'], // 모바일 팝업창 스킨
            ['val' => 'mobilePopupSizeW', 'typ' => 'i', 'def' => 300], // 모바일 팝업 가로 사이즈
            ['val' => 'mobilePopupSizeH', 'typ' => 'i', 'def' => 300], // 모바일 팝업 세로 사이즈
            ['val' => 'mobileSizeTypeW', 'typ' => 's', 'def' => 'px'], // 모바일 팝업 가로 사이즈 타입
            ['val' => 'mobileSizeTypeH', 'typ' => 's', 'def' => 'px'], // 모바일 팝업 세로 사이즈 타입
            ['val' => 'mobileContentImgFl', 'typ' => 's', 'def' => null], // 에디터 이미지 강제조정 여부
            ['val' => 'mobilePopupPositionT', 'typ' => 'i', 'def' => '0'], // 모바일 팝업 상단 위치
            ['val' => 'mobilePopupPositionL', 'typ' => 'i', 'def' => '0'], // 모바일 팝업 왼쪽 위치
            ['val' => 'mobilePopupBgColor', 'typ' => 's', 'def' => null], // 모바일 팝업 배경색상
            ['val' => 'popupPeriodOutputFl', 'typ' => 's', 'def' => 'n'], // 기간별 노출 설정
            ['val' => 'popupPeriodSDate', 'typ' => 's', 'def' => null], // 시작 날짜
            ['val' => 'popupPeriodSTime', 'typ' => 's', 'def' => null], // 시작 시간
            ['val' => 'popupPeriodEDate', 'typ' => 's', 'def' => null], // 종료 날짜
            ['val' => 'popupPeriodETime', 'typ' => 's', 'def' => null], // 종료 시간
            ['val' => 'todayUnSeeFl', 'typ' => 's', 'def' => 'n'], // 오늘 하루 보이지 않음 여부
            ['val' => 'todayUnSeeBgColor', 'typ' => 's', 'def' => null], // 배경 색상
            ['val' => 'todayUnSeeFontColor', 'typ' => 's', 'def' => null], // 폰트 색상
            ['val' => 'todayUnSeeAlign', 'typ' => 's', 'def' => 'right'], // 정렬 위치
            ['val' => 'popupPageUrl', 'typ' => 's', 'def' => '/index.php'], // 팝업 노출 페이지
            ['val' => 'popupPageParam', 'typ' => 's', 'def' => null], // 팝업 노출 페이지 파라메터
            ['val' => 'mobilePopupPageUrl', 'typ' => 's', 'def' => '/index.php'], // 모바일 팝업 노출 페이지
            ['val' => 'mobilePopupPageParam', 'typ' => 's', 'def' => null], // 모바일 팝업 노출 페이지 파라메터
            ['val' => 'modDt', 'typ' => 's', 'def' => null], // 수정일
            ['val' => 'regDt', 'typ' => 's', 'def' => null], // 등록일
        ];
        // @formatter:on

        return $arrField;
    }

    /**
     * [디자인] design_banner 필드 기본값
     *
     * @author artherot
     * @return array design_banner 테이블 필드 정보
     */
    public static function tableDesignBanner()
    {
        // @formatter:off
        $arrField = [
            ['val' => 'sno', 'typ' => 'i', 'def' => null], // 일련번호
            ['val' => 'skinName', 'typ' => 's', 'def' => null], // 스킨명
            ['val' => 'bannerGroupCode', 'typ' => 's', 'def' => null], // 배너 그룹 번호
            ['val' => 'bannerUseFl', 'typ' => 's', 'def' => 'y'], // 사용(노출) 여부
            ['val' => 'bannerImage', 'typ' => 's', 'def' => null], // 배너 이미지
            ['val' => 'bannerImageAlt', 'typ' => 's', 'def' => null], // 배너 이미지 설명 (alt 내용)
            ['val' => 'bannerLink', 'typ' => 's', 'def' => null], // 배너 링크주소
            ['val' => 'bannerTarget', 'typ' => 's', 'def' => null], // 배너 타켓
            ['val' => 'bannerSort', 'typ' => 'i', 'def' => '1'], // 배너 순서
            ['val' => 'bannerPeriodOutputFl', 'typ' => 's', 'def' => 'n'], // 기간별 노출 설정
            ['val' => 'bannerPeriodSDate', 'typ' => 's', 'def' => null], // 시작 날짜
            ['val' => 'bannerPeriodSTime', 'typ' => 's', 'def' => null], // 시작 시간
            ['val' => 'bannerPeriodEDate', 'typ' => 's', 'def' => null], // 종료 날짜
            ['val' => 'bannerPeriodETime', 'typ' => 's', 'def' => null], // 종료 시간
            ['val' => 'modDt', 'typ' => 's', 'def' => null], // 수정일
            ['val' => 'regDt', 'typ' => 's', 'def' => null], // 등록일
        ];
        // @formatter:on

        return $arrField;
    }

    /**
     * [디자인] design_banner_group 필드 기본값
     *
     * @author artherot
     * @return array design_banner_group 테이블 필드 정보
     */
    public static function tableDesignBannerGroup()
    {
        // @formatter:off
        $arrField = [
            ['val' => 'sno', 'typ' => 'i', 'def' => null], // 일련번호
            ['val' => 'skinName', 'typ' => 's', 'def' => null], // 스킨명
            ['val' => 'bannerGroupDeviceType', 'typ' => 's', 'def' => 'front'], // 디바이스 타입 ('front' : PC용, 'mobile' : 모바일용)
            ['val' => 'bannerGroupCode', 'typ' => 's', 'def' => null], // 배너 코드
            ['val' => 'bannerGroupName', 'typ' => 's', 'def' => null], // 배너 그룹명
            ['val' => 'bannerGroupType', 'typ' => 's', 'def' => 'banner'], // 배너 그룹 종류 (banner, logo, category. brand)
            ['val' => 'cateCd', 'typ' => 's', 'def' => null], // 카테고리 번호 (Goods Category or Brand Category)
            ['val' => 'modDt', 'typ' => 's', 'def' => null], // 수정일
            ['val' => 'regDt', 'typ' => 's', 'def' => null], // 등록일
        ];
        // @formatter:on

        return $arrField;
    }

    /**
     * [디자인] design_slider_banner 필드 기본값
     *
     * @author artherot
     * @return array design_banner 테이블 필드 정보
     */
    public static function tableDesignSliderBanner()
    {
        // @formatter:off
        $arrField = [
            ['val' => 'sno', 'typ' => 'i', 'def' => null], // 일련번호
            ['val' => 'skinName', 'typ' => 's', 'def' => null], // 스킨명
            ['val' => 'bannerDeviceType', 'typ' => 's', 'def' => 'front'], // 디바이스 타입 ('front' : PC용, 'mobile' : 모바일용)
            ['val' => 'bannerCode', 'typ' => 's', 'def' => null], // 배너 코드
            ['val' => 'bannerTitle', 'typ' => 's', 'def' => null], // 배너 타이틀
            ['val' => 'bannerUseFl', 'typ' => 's', 'def' => 'y'], // 사용(노출) 여부
            ['val' => 'bannerInfo', 'typ' => 's', 'def' => null], // 배너 이미지 내용
            ['val' => 'bannerSize', 'typ' => 's', 'def' => null], // 배너 사이즈
            ['val' => 'bannerSliderConf', 'typ' => 's', 'def' => null], // 배너 설정
            ['val' => 'bannerButtonConf', 'typ' => 's', 'def' => null], // 배너 버튼 (좌우, 하단)
            ['val' => 'bannerPeriodOutputFl', 'typ' => 's', 'def' => 'n'], // 기간별 노출 설정
            ['val' => 'bannerPeriodSDate', 'typ' => 's', 'def' => null], // 시작 날짜
            ['val' => 'bannerPeriodSTime', 'typ' => 's', 'def' => null], // 시작 시간
            ['val' => 'bannerPeriodEDate', 'typ' => 's', 'def' => null], // 종료 날짜
            ['val' => 'bannerPeriodETime', 'typ' => 's', 'def' => null], // 종료 시간
            ['val' => 'modDt', 'typ' => 's', 'def' => null], // 수정일
            ['val' => 'regDt', 'typ' => 's', 'def' => null], // 등록일
        ];
        // @formatter:on

        return $arrField;
    }

    /**
     * 엑셀다운로드 양식 관리
     *
     * @author atomyang
     * @return array es_excelForm 테이블 필드 정보
     */
    public static function tableExcelForm()
    {
        // @formatter:off
        $arrField = [
            ['val' => 'sno', 'typ' => 'i', 'def' => null], // 일련번호
            ['val' => 'scmNo', 'typ' => 'i', 'def' => (string)Session::get('manager.scmNo')], // 공급사 고유 번호
            ['val' => 'title', 'typ' => 's', 'def' => null], // 양식명
            ['val' => 'menu', 'typ' => 's', 'def' => null], // 메뉴
            ['val' => 'location', 'typ' => 's', 'def' => null], // 카테고리
            ['val' => 'managerNo', 'typ' => 'i', 'def' => null], // 등록자  번호
            ['val' => 'displayFl', 'typ' => 's', 'def' => 'y'], // 출력여부
            ['val' => 'defaultFl', 'typ' => 's', 'def' => 'n'], // 출력여부
            ['val' => 'excelField', 'typ' => 's', 'def' => null] //다운로드 필드
        ];
        // @formatter:on

        return $arrField;
    }

    /**
     * 엑셀다운로드 요청 관리
     *
     * @author atomyang
     * @return array es_excelRequest 테이블 필드 정보
     */
    public static function tableExcelRequest()
    {
        // @formatter:off
        $arrField = [
            ['val' => 'sno', 'typ' => 'i', 'def' => null], // 일련번호
            ['val' => 'scmNo', 'typ' => 'i', 'def' => (string)Session::get('manager.scmNo')], // 공급사 고유 번호
            ['val' => 'managerNo', 'typ' => 's', 'def' => null], // 등록자 번호
            ['val' => 'formSno', 'typ' => 's', 'def' => null], // 양식 일련ㅂ런호
            ['val' => 'state', 'typ' => 's', 'def' => 'n'], // 상태
            ['val' => 'whereFl', 'typ' => 's', 'def' => 'search'], // 다운로드 번위
            ['val' => 'whereCondition', 'typ' => 's', 'def' => null], //검색 조건
            ['val' => 'filePath', 'typ' => 's', 'def' => null], //파일경로
            ['val' => 'downloadFileName', 'typ' => 's', 'def' => null], //다운로드파일명
            ['val' => 'fileName', 'typ' => 's', 'def' => null], //파일명
            ['val' => 'expiryDate', 'typ' => 's', 'def' => null], //유효기간
        ];
        // @formatter:on

        return $arrField;
    }

    /**
     * 최종 환율
     *
     * @author su
     * @return array es_exchangeRate 테이블 필드 정보
     */
    public static function tableExchangeRate()
    {
        // @formatter:off
        $arrField = [
            ['val' => 'exchangeRateNo', 'typ' => 'i', 'def' => null], // 고유번호
            ['val' => 'exchangeRateUSD', 'typ' => 's', 'def' => '0'], // USD 최종 환율
            ['val' => 'exchangeRateCNY', 'typ' => 's', 'def' => '0'], // CNY 최종 환율
            ['val' => 'exchangeRateJPY', 'typ' => 's', 'def' => '0'], // JPY 최종 환율
            ['val' => 'exchangeRateEUR', 'typ' => 's', 'def' => '0'], // EUR 최종 환율
        ];
        // @formatter:on

        return $arrField;
    }

    /**
     * 자동 환율
     *
     * @author su
     * @return array es_exchangeRateAuto 테이블 필드 정보
     */
    public static function tableExchangeRateAuto()
    {
        // @formatter:off
        $arrField = [
            ['val' => 'exchangeRateAutoNo', 'typ' => 'i', 'def' => null], // 고유번호
            ['val' => 'exchangeRateAutoUSD', 'typ' => 's', 'def' => '0'], // USD 자동 환율
            ['val' => 'exchangeRateAutoCNY', 'typ' => 's', 'def' => '0'], // CNY 자동 환율
            ['val' => 'exchangeRateAutoJPY', 'typ' => 's', 'def' => '0'], // JPY 자동 환율
            ['val' => 'exchangeRateAutoEUR', 'typ' => 's', 'def' => '0'], // EUR 자동 환율
        ];
        // @formatter:on

        return $arrField;
    }

    /**
     * 환율 설정
     *
     * @author su
     * @return array es_exchangeRateConfig 테이블 필드 정보
     */
    public static function tableExchangeRateConfig()
    {
        // @formatter:off
        $arrField = [
            ['val' => 'exchangeRateConfigNo', 'typ' => 'i', 'def' => null], // 고유번호
            ['val' => 'exchangeRateConfigUSDType', 'typ' => 's', 'def' => 'manual'], // USD 환율설정 - 자동(auto),수동(manual)
            ['val' => 'exchangeRateConfigUSDManual', 'typ' => 's', 'def' => ''], // USD 수동 환율 값
            ['val' => 'exchangeRateConfigUSDAdjustment', 'typ' => 's', 'def' => '0'], // USD 환율 조정 값
            ['val' => 'exchangeRateConfigCNYType', 'typ' => 's', 'def' => 'manual'], // CNY 환율설정 - 자동(auto),수동(manual)
            ['val' => 'exchangeRateConfigCNYManual', 'typ' => 's', 'def' => ''], // CNY 수동 환율 값
            ['val' => 'exchangeRateConfigCNYAdjustment', 'typ' => 's', 'def' => '0'], // CNY 환율 조정 값
            ['val' => 'exchangeRateConfigJPYType', 'typ' => 's', 'def' => 'manual'], // JPY 환율설정 - 자동(auto),수동(manual)
            ['val' => 'exchangeRateConfigJPYManual', 'typ' => 's', 'def' => ''], // JPY 수동 환율 값
            ['val' => 'exchangeRateConfigJPYAdjustment', 'typ' => 's', 'def' => '0'], // JPY 환율 조정 값
            ['val' => 'exchangeRateConfigEURType', 'typ' => 's', 'def' => 'manual'], // EUR 환율설정 - 자동(auto),수동(manual)
            ['val' => 'exchangeRateConfigEURManual', 'typ' => 's', 'def' => ''], // EUR 수동 환율 값
            ['val' => 'exchangeRateConfigEURAdjustment', 'typ' => 's', 'def' => '0'], // EUR 환율 조정 값
        ];
        // @formatter:on

        return $arrField;
    }

    /**
     * 환율 log
     *
     * @author su
     * @return array es_exchangeRateLog 테이블 필드 정보
     */
    public static function tableExchangeRateLog()
    {
        // @formatter:off
        $arrField = [
            ['val' => 'exchangeRateLogNo', 'typ' => 'i', 'def' => null], // 고유번호
            ['val' => 'exchangeRateLogType', 'typ' => 's', 'def' => 'admin'], // 내역 타입 - 관리자에서 처리(admin), 자동으로 처리(auto)
            ['val' => 'exchangeRateLogComment', 'typ' => 's', 'def' => ''], // 내역
            ['val' => 'exchangeRateConfigNo', 'typ' => 'i', 'def' => ''], // 처리된 환율 설정 고유번호
            ['val' => 'exchangeRateNo', 'typ' => 'i', 'def' => ''], // 처리된 적용 환율 고유번호
            ['val' => 'exchangeRateAutoNo', 'typ' => 'i', 'def' => ''], // 처리된 자동 환율 고유번호
            ['val' => 'managerNo', 'typ' => 'i', 'def' => ''], // 내역 처리 운영자 고유번호
            ['val' => 'managerId', 'typ' => 's', 'def' => ''], // 내역 처리 운영자 아이디
            ['val' => 'managerNm', 'typ' => 's', 'def' => ''], // 내역 처리 운영자 이름
            ['val' => 'managerIp', 'typ' => 'i', 'def' => ''], // 내역 처리 운영자 IP
        ];
        // @formatter:on

        return $arrField;
    }

    /**
     * [FAQ] FAQ 필드 기본값
     *
     * @author sj
     * @return array FAQ 테이블 필드 정보
     */
    public static function tableFaq()
    {
        // @formatter:off
        $arrField = [
            ['val' => 'mallSno', 'typ' => 'i', 'def' => DEFAULT_MALL_NUMBER], // 몰번호
            ['val' => 'category', 'typ' => 's', 'def' => null], // 카테고리
            ['val' => 'subject', 'typ' => 's', 'def' => null], // 제목
            ['val' => 'contents', 'typ' => 's', 'def' => null], // 내용
            ['val' => 'answer', 'typ' => 's', 'def' => null], // 답변
            ['val' => 'isBest', 'typ' => 's', 'def' => 'n'], // 베스트FAQ
            ['val' => 'sortNo', 'typ' => 'i', 'def' => 0], // 순서
            ['val' => 'bestSortNo', 'typ' => 'i', 'def' => null], // 순서
        ];
        // @formatter:on

        return $arrField;
    }

    /**
     * [페이스북 상품 피드] 페이스북 상품 피드 필드 기본값
     *
     * @author sojeong Park
     * @return array FAQ 테이블 필드 정보
     */
    public static function tableFacebookGoodsFeed()
    {
        // @formatter:off
        $arrField = [
            ['val' => 'goodsNo', 'typ' => 's', 'def' => null], // 상품번호
            ['val' => 'useFl', 'typ' => 's', 'def' => 'n'], // 페이스북 제품 피드 사용 여부('y','n')
            ['val' => 'additional_image_link', 'typ' => 's', 'def' => null], // 추가 이미지
            ['val' => 'modDt', 'typ' => 's', 'def' => null], // 수정일
            ['val' => 'regDt', 'typ' => 's', 'def' => null], // 등록일
        ];
        // @formatter:on

        return $arrField;
    }

    /**
     * [게시판] 답변 템플릿
     *
     * @author sj
     * @return array 게시판 답변 템플릿 정보
     */
    public static function tableBdTemplate()
    {
        // @formatter:off
        $arrField = [
            ['val' => 'bdId', 'typ' => 's', 'def' => null], // 게시판아이디
            ['val' => 'subject', 'typ' => 's', 'def' => null], // 제목
            ['val' => 'contents', 'typ' => 's', 'def' => null], // 내용
        ];
        // @formatter:on

        return $arrField;
    }

    /**
     * [KAKAO] 카카오알림 템플릿
     *
     * @author jwno
     * @return array 카카오알림 템플릿 정보
     */
    public static function tableKakaoMessageTemplate()
    {
        // @formatter:off
        $arrField = [
            ['val' => 'sno', 'typ' => 'i', 'def' => null], // 일련번호
            ['val' => 'smsType', 'typ' => 's', 'def' => 'order'], // SMS 전송타입 (order, member, board)
            ['val' => 'templateCode', 'typ' => 's', 'def' => null], // 템플릿 코드
            ['val' => 'templateName', 'typ' => 's', 'def' => null], // 템플릿 이름
            ['val' => 'templateContent', 'typ' => 's', 'def' => null], // 템플릿 내용
            ['val' => 'templateButton', 'typ' => 's', 'def' => null], // 템플릿 내 버튼 (json)
            ['val' => 'inspectionStatus', 'typ' => 's', 'def' => 'REG'], // 검사상태 REG:등록 / REQ:심사요청 / APR:승인 / REJ:반려
            ['val' => 'status', 'typ' => 's', 'def' => 'R'], // 상태 S:중단 / A:정상 / R:대기(최초1회발송전)
            ['val' => 'useFlag', 'typ' => 's', 'def' => 'n'], // 사용여부 y:사용 / n:사용안함
            ['val' => 'modDt', 'typ' => 's', 'def' => null], // 수정일
            ['val' => 'regDt', 'typ' => 's', 'def' => null], // 등록일
        ];
        // @formatter:on

        return $arrField;
    }

    /**
     * [KAKAO] 카카오알림 템플릿(루나)
     *
     * @author cjb3333
     * @return array 카카오알림 템플릿 정보
     */
    public static function tableKakaoLunaMessageTemplate()
    {
        // @formatter:off
        $arrField = [
            ['val' => 'sno', 'typ' => 'i', 'def' => null], // 일련번호
            ['val' => 'templateCode', 'typ' => 's', 'def' => null], // 템플릿 코드
            ['val' => 'templateContent', 'typ' => 's', 'def' => null], // 템플릿 내용
            ['val' => 'modDt', 'typ' => 's', 'def' => null], // 수정일
            ['val' => 'regDt', 'typ' => 's', 'def' => null], // 등록일
        ];
        // @formatter:on

        return $arrField;
    }

    /**
     * [SMS] SMS 내용 템플릿
     *
     * @author sj
     * @return array SMS 내용 템플릿 정보
     */
    public static function tableSmsContents()
    {
        // @formatter:off
        $arrField = [
            ['val' => 'sno', 'typ' => 'i', 'def' => null], // 일련번호
            ['val' => 'smsType', 'typ' => 's', 'def' => 'user'], // SMS 전송타입 (order, member, promotion, user)
            ['val' => 'smsAutoCode', 'typ' => 's', 'def' => null], // SMS 자동발송 코드
            ['val' => 'smsAutoType', 'typ' => 's', 'def' => null], // SMS 자동발송 수신 종류 (member, admin, provider)
            ['val' => 'subject', 'typ' => 's', 'def' => null], // SMS 템플릿 제목
            ['val' => 'contents', 'typ' => 's', 'def' => null], // SMS 템플릿 내용
            ['val' => 'description', 'typ' => 's', 'def' => null], // 설명
            ['val' => 'isUserBasic', 'typ' => 's', 'def' => 'n'], // 전송타입(user)기본제공여부
            ['val' => 'modDt', 'typ' => 's', 'def' => null], // 수정일
            ['val' => 'regDt', 'typ' => 's', 'def' => null], // 등록일
        ];
        // @formatter:on

        return $arrField;
    }

    /**
     * [SMS] SMS LOG
     *
     * @author sj
     * @return array SMS LOG
     */
    public static function tableSmsLog()
    {
        // @formatter:off
        $arrField = [
            ['val' => 'sendFl', 'typ' => 's', 'def' => 'sms'], // 전송모드 (sms, lms)
            ['val' => 'smsType', 'typ' => 's', 'def' => 'user'], // SMS 전송타입 (order, member, promotion, user)
            ['val' => 'smsAutoSendOverFl', 'typ' => 's', 'def' => 'none'], // 90Byte 초과시 전송방법 (none, limit, division, lms)
            ['val' => 'sendType', 'typ' => 's', 'def' => null], // 발송타입
            ['val' => 'sender', 'typ' => 's', 'def' => null], // 발신정보
            ['val' => 'subject', 'typ' => 's', 'def' => null], // 제목
            ['val' => 'contents', 'typ' => 's', 'def' => null], // 내용
            ['val' => 'contentsMask', 'typ' => 's', 'def' => null], // 내용 출력 마스킹
            ['val' => 'receiverInfo', 'typ' => 's', 'def' => null], // 수신정보
            ['val' => 'receiverCnt', 'typ' => 'i', 'def' => null], // 수신인 COUNT
            ['val' => 'receiverType', 'typ' => 's', 'def' => 'each'], // 수신타입 (each, group, all, direct, excel)
            ['val' => 'replaceCodeType', 'typ' => 's', 'def' => 'none'], // 메시지 치환코드 타입(none, order, member, promotion, goods, excel)
            ['val' => 'templateButton', 'typ' => 's', 'def' => null], // 카카오 알림톡 템플릿 내 버튼 (json)
            ['val' => 'sendStatus', 'typ' => 's', 'def' => 'r'], // 발송상태 (c - 예약취소, r - 결과수신대기, y - 발송성공, n - 발송실패)
            ['val' => 'sendSuccessCnt', 'typ' => 'i', 'def' => 0], // 발송 성공 건수
            ['val' => 'sendFailCnt', 'typ' => 'i', 'def' => 0], // 발송 실패 건수
            ['val' => 'sendDt', 'typ' => 's', 'def' => null], // 발송일자
            ['val' => 'reserveDt', 'typ' => 's', 'def' => null], // 예약일자
            ['val' => 'smsSendKey', 'typ' => 's', 'def' => '0'], // SMS 발송키
        ];
        // @formatter:on

        return $arrField;
    }

    /**
     * [SMS] SMS 전송 리스트
     *
     * @author sj
     * @return array SMS LOG
     */
    public static function tableSmsSendList()
    {
        // @formatter:off
        $arrField = [
            ['val' => 'smsMode', 'typ' => 's', 'def' => 'i'], // 발송형태(r - 예약, i - 즉시)
            ['val' => 'smsLogSno', 'typ' => 'i', 'def' => null], // SMS LOG 번호
            ['val' => 'memNo', 'typ' => 'i', 'def' => null], // 회원번호
            ['val' => 'receiverName', 'typ' => 's', 'def' => null], // 이름
            ['val' => 'receiverCellPhone', 'typ' => 's', 'def' => null], // 수신번호
            ['val' => 'receiverSmsFl', 'typ' => 's', 'def' => 'y'], // SMS 수신여부
            ['val' => 'sendCheckFl', 'typ' => 's', 'def' => 'r'], // 발송 성공여부 (c - 예약취소, r - 결과수신대기, y - 발송성공, n - 발송실패)
            ['val' => 'acceptCheckFl', 'typ' => 's', 'def' => 'n'], // 접수상태 (y, n)
            ['val' => 'failCode', 'typ' => 's', 'def' => null], // 실패이유
            ['val' => 'apiReturnIdx', 'typ' => 's', 'def' => null], // api 리턴index
            ['val' => 'receiverDt', 'typ' => 's', 'def' => null], // 도착시간
            ['val' => 'receiverReplaceCode', 'typ' => 'j', 'def' => null], // 발송 시 사용된 치환데이터
            ['val' => 'templateContent', 'typ' => 's', 'def' => null], // 발송 시 사용된 치환데이터
            ['val' => 'kakaoSendKey', 'typ' => 's', 'def' => null], // 카카오알림톡용 발송 키
        ];
        // @formatter:on

        return $arrField;
    }

    /**
     * [SMS] SMS 엑셀 업로드 내역
     *
     * @static
     * @return array
     */
    public static function tableSmsExcelLog()
    {
        // @formatter:off
        $arrField = [
            ['val' => 'uploadKey', 'typ' => 's', 'def' => '00000000000000'], // 업로드구분값(일시+난수(4))
            ['val' => 'fileName', 'typ' => 's', 'def' => 'empty'], // 파일명
            ['val' => 'cellPhone', 'typ' => 's', 'def' => '00000000000'], // 휴대폰번호
            ['val' => 'name', 'typ' => 's', 'def' => 'guest'], // 회원명
            ['val' => 'validateFl', 'typ' => 's', 'def' => 'n'], // 유효성검사결과
            ['val' => 'validateDesc', 'typ' => 's', 'def' => 'empty'], // 유효성검사설명
            ['val' => 'managerSno', 'typ' => 'i', 'def' => 0], // 관리자번호
        ];
        // @formatter:on

        return $arrField;
    }

    /**
     * [SMS] 개별/전체 sms 발송시 사용된 개별 치환코드 저장
     *
     * @static
     * @return array
     *
     * @deprecated 2017-06-01 미사용 테이블 입니다. 제거될 수 있으니 사용하지 마시기 바랍니다.
     */
    public static function tableSmsReplaceCodeLog()
    {
        // @formatter:off
        $arrField = [
            ['val' => 'smsSendListSno', 'typ' => 's', 'def' => 0], // sms발송리스트일련번호
            ['val' => 'code', 'typ' => 'j', 'def' => null], // 치환코드
        ];
        // @formatter:on

        return $arrField;
    }

    /**
     * [SMS] 080 수신거부 서비스 수신거부자 리스트 테이블
     *
     * @static
     * @return array
     */
    public static function tableSms080()
    {
        //@formatter:off
        $arrFiled = [
            ['val' => 'rejectCellPhone', 'typ' => 's', 'def' => '000-000-0000'], // 수신거부 전화번호
            ['val' => 'rejectDt', 'typ' => 's', 'def' => '0000-00-00 00:00:00'], // 수신거부 일시
        ];
        //@formatter:on

        return $arrFiled;
    }

    /**
     * [LOG] tableLogWhole 필드 기본값
     *
     * @author artherot
     * @return array tableLogWhole 테이블 필드 정보
     */
    public static function tableLogWhole()
    {
        // @formatter:off
        $arrField = [
            ['val' => 'managerNo', 'typ' => 'i', 'def' => 0], // 관리자 키
            ['val' => 'managerId', 'typ' => 's', 'def' => null], // 관리자 아이디
            ['val' => 'managerIp', 'typ' => 's', 'def' => null], // 관리자 접속 아이피
            ['val' => 'logType', 'typ' => 's', 'def' => null], // 로그 타입 (goods , order, member......)
            ['val' => 'logCode', 'typ' => 's', 'def' => null], // 로그 코드 (상품번호 , 주문번호 등등..)
            ['val' => 'logTitle', 'typ' => 's', 'def' => null], // 로그 타이틀
            ['val' => 'logDesc', 'typ' => 's', 'def' => null], // 로그 내용
        ];
        // @formatter:on

        return $arrField;
    }

    /**
     * [LOG] tableSearchWordStatistics 필드 기본값
     *
     * @author yjwee
     * @return array tableSearchWordStatistics 테이블 필드 정보
     */
    public static function tableSearchWordStatistics()
    {
        // @formatter:off
        $arrField = [
            ['val' => 'mallSno', 'typ' => 'i', 'def' => null],      // 상점 고유번호
            ['val' => 'keyword', 'typ' => 's', 'def' => null],      // 검색어
            ['val' => 'resultCount', 'typ' => 'i', 'def' => 0],  // 검색결과
            ['val' => 'os', 'typ' => 's', 'def' => null],           // OS
        ];
        // @formatter:on

        return $arrField;
    }

    /**
     * [LOG] tableCategoryStatistics 필드 기본값
     *
     * @author yjwee
     * @return array tableCategoryStatistics 테이블 필드 정보
     */
    public static function tableCategoryStatistics()
    {
        // @formatter:off
        $arrField = [
            ['val' => 'cateCd', 'typ' => 's', 'def' => null],                   // 카테고리 코드
            ['val' => 'cateNm', 'typ' => 's', 'def' => null],                   // 카테고리 이름
            ['val' => 'totalPrice', 'typ' => 'd', 'def' => 0],                  // 총 매출금액
            ['val' => 'pcPrice', 'typ' => 'd', 'def' => 0],                     // pc총 매출금액
            ['val' => 'mobilePrice', 'typ' => 'd', 'def' => 0],                 // mobile 총 매출금액
            ['val' => 'totalOrderGoodsCount', 'typ' => 'i', 'def' => 0],        // 총 구매수량
            ['val' => 'pcOrderGoodsCount', 'typ' => 'i', 'def' => 0],           // pc총 구매수량
            ['val' => 'mobileOrderGoodsCount', 'typ' => 'i', 'def' => 0],       // mobile 총 구매수량
            ['val' => 'totalOrderCount', 'typ' => 'i', 'def' => 0],             // 총 구매자수
            ['val' => 'pcOrderCount', 'typ' => 'i', 'def' => 0],             // pc총 구매자수
            ['val' => 'mobileOrderCount', 'typ' => 'i', 'def' => 0],             // mobile 총 구매자수
        ];
        // @formatter:on

        return $arrField;
    }

    /**
     * [LOG] es_goodsSaleStatics 필드 기본값
     *
     * @author yjwee
     * @return array 테이블 필드 정보
     */
    public static function tableGoodsSaleStatistics()
    {
        // @formatter:off
        $arrField = [
            ['val' => 'imageStorage', 'typ' => 's', 'def' => null],             // 이미지 저장소 위치
            ['val' => 'imagePath', 'typ' => 's', 'def' => null],                // 이미지 저장소 경로
            ['val' => 'imageName', 'typ' => 's', 'def' => null],                // 이미지 이름
            ['val' => 'goodsNo', 'typ' => 's', 'def' => null],                  // 상품번호
            ['val' => 'goodsNm', 'typ' => 's', 'def' => null],                  // 상품명
            ['val' => 'companyNm', 'typ' => 's', 'def' => null],                // 회사명
            ['val' => 'cateCd', 'typ' => 's', 'def' => null],                   // 카테고리 코드
            ['val' => 'totalPrice', 'typ' => 'd', 'def' => 0],                  // 총 매출금액
            ['val' => 'pcPrice', 'typ' => 'd', 'def' => 0],                     // pc총 매출금액
            ['val' => 'mobilePrice', 'typ' => 'd', 'def' => 0],                 // mobile 총 매출금액
            ['val' => 'totalOrderGoodsCount', 'typ' => 'i', 'def' => 0],        // 총 구매수량
            ['val' => 'pcOrderGoodsCount', 'typ' => 'i', 'def' => 0],           // pc총 구매수량
            ['val' => 'mobileOrderGoodsCount', 'typ' => 'i', 'def' => 0],       // mobile 총 구매수량
            ['val' => 'totalOrderCount', 'typ' => 'i', 'def' => 0],             // 총 구매자수
            ['val' => 'pcOrderCount', 'typ' => 'i', 'def' => 0],                // pc총 구매자수
            ['val' => 'mobileOrderCount', 'typ' => 'i', 'def' => 0],            // mobile 총 구매자수
        ];
        // @formatter:on

        return $arrField;
    }

    /**
     * [LOG] tableLogStock 필드 기본값
     *
     * @author artherot
     * @return array tableLogStock 테이블 필드 정보
     */
    public static function tableLogStock()
    {
        // @formatter:off
        $arrField = [
            ['val' => 'managerNo', 'typ' => 'i', 'def' => 0], // 관리자 키
            ['val' => 'managerId', 'typ' => 's', 'def' => null], // 관리자 아이디
            ['val' => 'managerIp', 'typ' => 's', 'def' => null], // 관리자 접속 아이피
            ['val' => 'goodsNo', 'typ' => 'i', 'def' => null], // 상품 번호
            ['val' => 'orderNo', 'typ' => 's', 'def' => null], // 주문 번호
            ['val' => 'optionValue', 'typ' => 's', 'def' => null], // 옵션값
            ['val' => 'beforeStock', 'typ' => 'i', 'def' => null], // 전재고
            ['val' => 'afterStock', 'typ' => 'i', 'def' => null], // 후재고
            ['val' => 'variationStock', 'typ' => 'i', 'def' => null], // 변화량
            ['val' => 'logDesc', 'typ' => 's', 'def' => null], // 로그 내용
        ];
        // @formatter:on

        return $arrField;
    }

    /**
     * [LOG] tableLogOrder 필드 기본값
     *
     * @author artherot
     * @return array tableLogOrder 테이블 필드 정보
     */
    public static function tableLogOrder()
    {
        // @formatter:off
        $arrField = [
            ['val' => 'managerNo', 'typ' => 'i', 'def' => 0], // 관리자 키
            ['val' => 'managerId', 'typ' => 's', 'def' => null], // 관리자 아이디
            ['val' => 'managerIp', 'typ' => 's', 'def' => null], // 관리자 접속 아이피
            ['val' => 'orderNo', 'typ' => 's', 'def' => null], // 주문 번호
            ['val' => 'goodsSno', 'typ' => 'i', 'def' => null], // 주문 상품 번호
            ['val' => 'logCode01', 'typ' => 's', 'def' => null], // 로그 코드 1
            ['val' => 'logCode02', 'typ' => 's', 'def' => null], // 로그 코드 2
            ['val' => 'logDesc', 'typ' => 's', 'def' => null], // 로그 내용
        ];
        // @formatter:on

        return $arrField;
    }

    /**
     * [LOG] tableLogGoodsMapping 필드 기본값
     *
     * @author artherot
     * @return array tableLogGoodsMapping 테이블 필드 정보
     */
    public static function tableLogGoodsMapping()
    {
        // @formatter:off
        $arrField = [
            ['val' => 'cateType', 'typ' => 's', 'def' => 'goods'], // 매핑 모드
            ['val' => 'mappingMode', 'typ' => 's', 'def' => 'm'], // 매핑 모드
            ['val' => 'mappingFl', 'typ' => 's', 'def' => 'n'], // 매핑 완료 여부
            ['val' => 'mappingLog', 'typ' => 's', 'def' => null], // 매핑 내용
        ];
        // @formatter:on

        return $arrField;
    }


    /**
     * [LOG] tableLogGoods필드 기본값
     *
     * @author artherot
     * @return array tableLogGoods 테이블 필드 정보
     */
    public static function tableLogGoods()
    {
        // @formatter:off
        $arrField = [
            ['val' => 'mode', 'typ' => 's', 'def' => null], // 매핑 모드
            ['val' => 'goodsNo', 'typ' => 's', 'def' => null], // 매핑 모드
            ['val' => 'applyFl', 'typ' => 's', 'def' => 'a'], // 매핑 모드
            ['val' => 'prevData', 'typ' => 's', 'def' => null], // 매핑 완료 여부
            ['val' => 'updateData', 'typ' => 's', 'def' => null],
            ['val' => 'managerNo', 'typ' => 'i', 'def' => 0], // 관리자 키
            ['val' => 'managerId', 'typ' => 's', 'def' => null], // 매핑 내용
        ];
        // @formatter:on

        return $arrField;
    }


    /**
     * [LOG] tableLogAddGoods필드 기본값
     *
     * @author artherot
     * @return array tableLogAddGoods필드 테이블 필드 정보
     */
    public static function tableLogAddGoods()
    {
        // @formatter:off
        $arrField = [
            ['val' => 'addGoodsNo', 'typ' => 's', 'def' => null], // 매핑 모드
            ['val' => 'applyFl', 'typ' => 's', 'def' => 'a'], // 매핑 모드
            ['val' => 'prevData', 'typ' => 's', 'def' => null], // 매핑 완료 여부
            ['val' => 'updateData', 'typ' => 's', 'def' => null],
            ['val' => 'managerId', 'typ' => 's', 'def' => null], // 매핑 내용
            ['val' => 'managerNo', 'typ' => 'i', 'def' => 0], // 관리자 키
        ];
        // @formatter:on

        return $arrField;
    }

    /**
     * [LOG] tableLogLegalRequirements 필드 기본값
     *
     * @author mj2
     * @return array tableLogLegalRequirements 테이블 필드 정보
     */
    public static function tableLogLegalRequirements()
    {
        // @formatter:off
        $arrField = [
            ['val' => 'managerId', 'typ' => 's', 'def' => null], // 관리자 아이디
            ['val' => 'menu', 'typ' => 's', 'def' => null], // 항목명
            ['val' => 'checked', 'typ' => 's', 'def' => 'n'], // 체크 상태 y: 체크 n: 미체크
        ];
        // @formatter:on

        return $arrField;
    }

    /**
     * [LOG] DB_LOG_IPLOGINTRY 필드 기본값
     *
     * @author daemanjung
     * @return array tableLogIpLoginTry 테이블 필드 정보
     */
    public static function tableLogIpLoginTry()
    {
        // @formatter:off
        $arrField = [
            ['val' => 'loginFailIp', 'typ' => 's', 'def' => null], // 로그인 시도 아이피
            ['val' => 'loginFailDt', 'typ' => 's', 'def' => null], // 로그인 시도시간
            ['val' => 'limitFlag', 'typ' => 's', 'def' => 'N'], // 접속제한 Y: 제한 N: 미제한
            ['val' => 'onLimitDt', 'typ' => 's', 'def' => null], // 로그인 제한 시간
            ['val' => 'loginType', 'typ' => 's', 'def' => 'admin'], // 로그인 접속구분 admin : 관리자 user : 사용자
        ];
        // @formatter:on

        return $arrField;
    }

    /**
     * [MAIL] MAIL LOG
     *
     * @author sj
     * @return array MAIL LOG
     */
    public static function tableMailLog()
    {
        // @formatter:off
        $arrField = [
            ['val' => 'subject', 'typ' => 's', 'def' => null], // 제목
            ['val' => 'contents', 'typ' => 's', 'def' => null], // 내용
            ['val' => 'contentsMask', 'typ' => 's', 'def' => null], // 내용 마스킹 정보
            ['val' => 'sender', 'typ' => 's', 'def' => null], // 발송자
            ['val' => 'managerNo', 'typ' => 'i', 'def' => null], // 발송자 키값
            ['val' => 'sendType', 'typ' => 's', 'def' => null], // 발송유형
            ['val' => 'receiver', 'typ' => 's', 'def' => null], // 수신정보
            ['val' => 'receiverCnt', 'typ' => 'i', 'def' => null], // 수신인COUNT
            ['val' => 'receiverCondition', 'typ' => 's', 'def' => null], // 수신타입
        ];
        // @formatter:on

        return $arrField;
    }

    /**
     * [Mail] MAIL 전송 리스트
     *
     * @static
     * @return array
     */
    public static function tableMailSendList()
    {
        // @formatter:off
        $arrField = [
            ['val' => 'mailMode', 'typ' => 's', 'def' => 'i'], // 발송형태(r - 예약, i - 즉시)
            ['val' => 'mailLogSno', 'typ' => 'i', 'def' => null], // 메일 LOG 번호
            ['val' => 'memNo', 'typ' => 'i', 'def' => 0], // 회원번호
            ['val' => 'receiverName', 'typ' => 's', 'def' => null], // 이름
            ['val' => 'receiverEmail', 'typ' => 's', 'def' => null], // 수신메일주소
            ['val' => 'receiverMailFl', 'typ' => 's', 'def' => 'y'], // 메일 수신여부
            ['val' => 'sendCheckFl', 'typ' => 's', 'def' => 'r'], // 발송 성공여부 (c, r, y, n)
            ['val' => 'acceptCheckFl', 'typ' => 's', 'def' => 'n'], // 접수상태 (y, n)
            ['val' => 'failCode', 'typ' => 's', 'def' => null], // 실패이유
            ['val' => 'receiverDt', 'typ' => 's', 'def' => null], // 도착시간
        ];
        // @formatter:on

        return $arrField;
    }

    /**
     * [COUPON] COUPON
     *
     * @author su
     * @return array COUPON
     */
    public static function tableCoupon()
    {
        // @formatter:off
        $arrField = [['val' => 'couponNo', 'typ' => 'i', 'def' => ''], // 쿠폰고유번호
            ['val' => 'couponKind', 'typ' => 's', 'def' => 'online'], // 종류–온라인쿠폰(‘online’),페이퍼쿠폰(‘offline’)
            ['val' => 'couponType', 'typ' => 's', 'def' => 'y'], // 사용여부–사용(‘y’),정지(‘n’)
            ['val' => 'couponUseType', 'typ' => 's', 'def' => 'product'], // 쿠폰유형–상품쿠폰(‘product’),주문쿠폰(‘order’),배송비쿠폰('delivery')
            ['val' => 'couponSaveType', 'typ' => 's', 'def' => 'manual'], // 발급구분–회원다운로드(‘down’),자동발급(‘auto’),수동발급(‘manual’)
            ['val' => 'couponNm', 'typ' => 's', 'def' => ''], // 쿠폰명
            ['val' => 'couponDescribed', 'typ' => 's', 'def' => ''], // 쿠폰설명
            ['val' => 'couponUsePeriodType', 'typ' => 's', 'def' => 'period'], // 사용기간–기간(‘period’),일(‘day’)
            ['val' => 'couponUsePeriodStartDate', 'typ' => 's', 'def' => ''], // 사용기간-시작
            ['val' => 'couponUsePeriodEndDate', 'typ' => 's', 'def' => ''], // 사용기간-끝
            ['val' => 'couponUsePeriodDay', 'typ' => 'i', 'def' => ''], // 사용가능일
            ['val' => 'couponUseDateLimit', 'typ' => 's', 'def' => null], // 사용 종료일
            ['val' => 'couponKindType', 'typ' => 's', 'def' => 'sale'], // 혜택구분–상품할인(‘sale’),마일리지적립(‘add’),배송비할인('delivery')
            ['val' => 'couponDeviceType', 'typ' => 's', 'def' => 'all'], // 사용범위–PC+모바일(‘all’),PC(‘pc’),모바일(‘mobile’)
            ['val' => 'couponBenefit', 'typ' => 's', 'def' => '0'], // 혜택금액(할인,적립)액–소수점 2자리 가능
            ['val' => 'couponBenefitType', 'typ' => 's', 'def' => 'p'], // 혜택금액종류-정율%(‘percent’),정액-원(‘fix’)–금액은 $등 가능
            ['val' => 'couponBenefitFixApply', 'typ' => 's', 'def' => 'one'], // 정액쿠폰수량별적용-수량별로적용(‘all’),기존대로 상품에적용(‘one’)
            ['val' => 'couponMaxBenefitType', 'typ' => 's', 'def' => ''], // 최대혜택금액여부–사용(‘y’)
            ['val' => 'couponMaxBenefit', 'typ' => 's', 'def' => ''], // 최대혜택금액–소수점2자리가능
            ['val' => 'couponDisplayType', 'typ' => 's', 'def' => 'n'], // 상세노출기간종류–즉시(‘n’),예약(‘y’)
            ['val' => 'couponDisplayStartDate', 'typ' => 's', 'def' => ''], // 노출기간-시작
            ['val' => 'couponDisplayEndDate', 'typ' => 's', 'def' => ''], // 노출기간-끝
            ['val' => 'couponImageType', 'typ' => 's', 'def' => 'basic'], // 이미지종류–기본(‘basic’),직접(‘self’)
            ['val' => 'couponImage', 'typ' => 's', 'def' => ''], // 이미지–직접등록
            ['val' => 'couponLimitSmsFl', 'typ' => 's', 'def' => 'n'], // 사용기간만료시 SMS발송
            ['val' => 'couponUseAblePaymentType', 'typ' => 's', 'def' => 'all'], // 결제수단 사용제한 - 제한없음( 'all' ), 무통장만 사용가능 ( 'bank' )
            ['val' => 'couponAmountType', 'typ' => 's', 'def' => 'n'], // 발급수량종류–무제한(‘n’), 제한(‘y’)
            ['val' => 'couponAmount', 'typ' => 'i', 'def' => ''], // 발급수량
            ['val' => 'couponSaveDuplicateType', 'typ' => 's', 'def' => 'n'], // 중복발급제한여부–안됨(‘n’),중복가능(‘y’)
            ['val' => 'couponSaveDuplicateLimitType', 'typ' => 's', 'def' => ''], // 중복발급최대제한여부–사용(‘y’)
            ['val' => 'couponSaveDuplicateLimit', 'typ' => 'i', 'def' => ''], // 중복발급최대개수
            ['val' => 'couponApplyMemberGroup', 'typ' => 's', 'def' => null], // 발급가능회원등급
            ['val' => 'couponApplyMemberGroupDisplayType', 'typ' => 's', 'def' => ''], // 발급가능회원등급만노출–사용(‘y’)
            ['val' => 'couponApplyProductType', 'typ' => 's', 'def' => 'all'], // 쿠폰적용상품–전체(‘all’),공급사(‘provider’),카테고리(‘category’),브랜드(‘brand’),상품(‘goods’)
            ['val' => 'couponApplyProvider', 'typ' => 's', 'def' => null], // 쿠폰적용공급사
            ['val' => 'couponApplyCategory', 'typ' => 's', 'def' => null], // 쿠폰적용카테고리
            ['val' => 'couponApplyBrand', 'typ' => 's', 'def' => null], // 쿠폰적용브랜드
            ['val' => 'couponApplyGoods', 'typ' => 's', 'def' => null], // 쿠폰적용상품
            ['val' => 'couponExceptProviderType', 'typ' => 's', 'def' => ''], // 쿠폰제외공급사여부-사용(‘y’)
            ['val' => 'couponExceptProvider', 'typ' => 's', 'def' => null], // 쿠폰제외공급사
            ['val' => 'couponExceptCategoryType', 'typ' => 's', 'def' => ''], // 쿠폰제외카테고리여부-사용(‘y’)
            ['val' => 'couponExceptCategory', 'typ' => 's', 'def' => null], // 쿠폰제외카테고리
            ['val' => 'couponExceptBrandType', 'typ' => 's', 'def' => ''], // 쿠폰제외브랜드여부-사용(‘y’)
            ['val' => 'couponExceptBrand', 'typ' => 's', 'def' => null], // 쿠폰제외브랜드
            ['val' => 'couponExceptGoodsType', 'typ' => 's', 'def' => ''], // 쿠폰제외상품여부-사용(‘y’)
            ['val' => 'couponExceptGoods', 'typ' => 's', 'def' => null], // 쿠폰제외상품
            //            ['val' => 'couponApplyGoodsAmountType', 'typ' => 's', 'def' => 'y'], // 상품수량별 적용방식-수량상관없이쿠폰1개할인(‘n’),수량*쿠폰할인(‘y’)
            ['val' => 'couponMinOrderPrice', 'typ' => 's', 'def' => ''], // 쿠폰적용의 최소상품구매금액제한–소수점2자리가능
            ['val' => 'couponProductMinOrderType', 'typ' => 's', 'def' => 'product'], // 쿠폰적용의 최소상품구매금액제한 기준 - product(상품), order(주문)
            //            ['val' => 'couponApplyOrderPayType', 'typ' => 's', 'def' => 'all'], // 쿠폰적용 결제방법-전체(‘all’),무통장입금만(‘bank’)
            ['val' => 'couponApplyDuplicateType', 'typ' => 's', 'def' => 'y'], // 쿠폰적용 여부-중복가능(‘y’),안됨(‘n’)
            //            ['val' => 'couponAutoRecoverType', 'typ' => 's', 'def' => 'y'], // 쿠폰자동복원 여부-가능(‘y’),안됨(‘n’)
            ['val' => 'couponEventType', 'typ' => 's', 'def' => 'first'], // 자동발급쿠폰 종류-첫구매(‘first’),구매감사(‘order’),생일축하(‘birth’),회원가입(‘join’),출석체크(‘attend’),회원정보수정(‘memberModifyEvent’)
            ['val' => 'couponEventOrderFirstType', 'typ' => 's', 'def' => ''], // 구매감사쿠폰과 첫구매쿠폰의 중복안함-(‘y’)
            ['val' => 'couponEventOrderSmsType', 'typ' => 's', 'def' => ''], // 구매감사쿠폰SMS발송여부-함(‘y’)
            ['val' => 'couponEventFirstSmsType', 'typ' => 's', 'def' => ''], // 첫구매쿠폰SMS발송여부-함(‘y’)
            ['val' => 'couponEventBirthSmsType', 'typ' => 's', 'def' => ''], // 생일쿠폰SMS발송여부-함(‘y’)
            ['val' => 'couponEventMemberSmsType', 'typ' => 's', 'def' => ''], // 회원가입쿠폰SMS발송여부-함(‘y’)
            ['val' => 'couponEventAttendanceSmsType', 'typ' => 's', 'def' => ''], // 출석체크쿠폰SMS발송여부-함(‘y’)
            ['val' => 'couponEventMemberModifySmsType', 'typ' => 's', 'def' => 'n'], // 회원정보수정쿠폰SMS발송여부-함(‘y’)
            ['val' => 'couponEventWakeSmsType', 'typ' => 's', 'def' => 'n'], // 휴면회원해제쿠폰SMS발송여부-함(‘y’)
            ['val' => 'couponAuthType', 'typ' => 's', 'def' => 'n'], // 쿠폰인증번호타입-1개의인증번호사용(‘n’),회원별로다른인증번호사용('y')
            ['val' => 'couponInsertAdminId', 'typ' => 's', 'def' => ''], // 쿠폰등록자-아이디
            ['val' => 'managerNo', 'typ' => 'i', 'def' => 0], //관리자 키
            ['val' => 'couponSaveCount', 'typ' => 'i', 'def' => '0'] // 쿠폰발급수
        ];
        // @formatter:on

        return $arrField;
    }

    public static function tableCouponUseMemberGroup()
    {
        // @formatter:off
        $arrField = [['val' => 'couponNo', 'typ' => 'i', 'def' => ''], // 쿠폰고유번호
            ['val' => 'couponUseMemberGroup', 'typ' => 'i', 'def' => ''], // 쿠폰 사용가능한 그룹번호
        ];
        // @formatter:on

        return $arrField;
    }

    /**
     * [COUPONOFFLINECODE] COUPONOFFLINECODE
     *
     * @author su
     * @return array COUPONOFFLINECODE
     */
    public static function tableCouponOfflineCode()
    {
        $arrField = [
            [
                'val' => 'couponOfflineCode',
                'typ' => 's',
                'def' => null,
            ],
            // 오프라인쿠폰코드-전체쿠폰 32자 couponOfflineCodeUser + couponOfflineCodeKey
            [
                'val' => 'couponOfflineCodeUser',
                'typ' => 's',
                'def' => null,
            ],
            // 유저공개쿠폰코드 12자
            [
                'val' => 'couponOfflineCodeKey',
                'typ' => 's',
                'def' => null,
            ],
            // 유저비공개쿠폰코드 20자
            [
                'val' => 'couponNo',
                'typ' => 'i',
                'def' => null,
            ],
            // 쿠폰고유번호
            [
                'val' => 'memNo',
                'typ' => 'i',
                'def' => null,
            ],
            // 발급된 회원쿠폰고유번호
            [
                'val' => 'memberCouponNo',
                'typ' => 'i',
                'def' => null,
            ],
            // 회원고유번호
            [
                'val' => 'couponOfflineCodeSaveType',
                'typ' => 's',
                'def' => null,
            ],
            [
                'val' => 'couponOfflineInsertAdminId',
                'typ' => 's',
                'def' => null,
            ],
            [
                'val' => 'managerNo',
                'typ' => 'i',
                'def' => 0,
            ]
            // 오프라인쿠폰코드사용유무
        ];

        return $arrField;
    }

    /**
     * [COUPON] comebackCoupon
     *
     * @author su
     * @return array comebackCoupon
     */
    public static function tableComebackCoupon()
    {
        // @formatter:off
        $arrField = [
            ['val' => 'title', 'typ' => 's', 'def' => ''], // 제목
            ['val' => 'targetFl', 'typ' => 's', 'def' => 'o'], // 대상 선택유형 – o:주문관련 / g:상품관련
            ['val' => 'targetOrderFl', 'typ' => 's', 'def' => 'p'], // 대상 주문 선택유형 - p:결제완료 / s:배송완료 / c:구매확정
            ['val' => 'targetOrderDay', 'typ' => 'i', 'def' => ''], // 대상/주문관련 - 선택유형으로부터 몇일이 지난값
            ['val' => 'targetOrderPriceMin', 'typ' => 'i', 'def' => ''], // 대상/주문관련 - 선택유형의 결제금액 최소값
            ['val' => 'targetOrderPriceMax', 'typ' => 'i', 'def' => ''], // 대상/주문관련 - 선택유형의 결제금액 최대값
            ['val' => 'targetGoodFl', 'typ' => 's', 'def' => 'p'], // 대상 상품 선택유형 - p:결제완료 / s:배송완료 / c:구매확정
            ['val' => 'targetGoodDay', 'typ' => 'i', 'def' => ''], // 대상/상품관련 - 선택유형으로부터 몇일이 지난값
            ['val' => 'targetGoodGoods', 'typ' => 's', 'def' => ''], // 대상 상품 선택된 상품배열
            ['val' => 'couponNo', 'typ' => 'i', 'def' => ''], // 발행 쿠폰고유번호
            ['val' => 'smsFl', 'typ' => 's', 'def' => 'n'], // sms동시 발송여부 - y:발송 / n:미발송
            ['val' => 'smsSpamFl', 'typ' => 's', 'def' => 'n'], // 광고성 문구 추가 - y:추가 / n:미추가
            ['val' => 'smsContents', 'typ' => 's', 'def' => ''], // SMS 발송 내용
            ['val' => 'managerNo', 'typ' => 'i', 'def' => ''], // 등록 관리자 고유번호
            ['val' => 'deleteFl', 'typ' => 's', 'def' => 'n'], // 삭제 flag - y:삭제 / n:미삭제
            ['val' => 'sendDt', 'typ' => 's', 'def' => null], // 전송일
            ['val' => 'regDt', 'typ' => 's', 'def' => null] // 등록일
        ];
        // @formatter:on

        return $arrField;
    }

    /**
     * [COUPON] comebackCouponMember
     *
     * @author su
     * @return array comebackCouponMember
     */
    public static function tableComebackCouponMember()
    {
        // @formatter:off
        $arrField = [
            ['val' => 'ccSno', 'typ' => 'i', 'def' => ''], // es_comebackCoupon.sno
            ['val' => 'memberCouponNo', 'typ' => 'i', 'def' => ''], // es_memberCoupon.memberCouponNo
            ['val' => 'memNo', 'typ' => 'i', 'def' => ''], // es_member.memNo
            ['val' => 'groupSno', 'typ' => 'i', 'def' => ''], // es_member.groupSno
            ['val' => 'couponNo', 'typ' => 'i', 'def' => ''], // 발행 쿠폰고유번호
            ['val' => 'smsFl', 'typ' => 's', 'def' => 'n'], // sms 발송여부 - y:발송 / n:미발송
            ['val' => 'regDt', 'typ' => 's', 'def' => null] // 등록일
        ];
        // @formatter:on

        return $arrField;
    }

    /**
     * [EVENT] EVENT
     *
     * @author sj
     * @return array EVENT
     */
    public static function tableEvent()
    {
        // @formatter:off
        $arrField = [
            ['val' => 'subject', 'typ' => 's', 'def' => null], // 이벤트 제목
            ['val' => 'startDt', 'typ' => 's', 'def' => null], // 시작일
            ['val' => 'endDt', 'typ' => 's', 'def' => null], // 종료일
            ['val' => 'contents', 'typ' => 's', 'def' => null], // 내용
            ['val' => 'goodsNo', 'typ' => 's', 'def' => null], // 상품번호
            ['val' => 'display', 'typ' => 's', 'def' => null], // 디스플레이
            ['val' => 'cateCd', 'typ' => 's', 'def' => null], // 노출카테고리
            ['val' => 'brandCd', 'typ' => 's', 'def' => null], // 노출브랜드
            ['val' => 'perLine', 'typ' => 'i', 'def' => null], // 라인당 노출 상품수
            ['val' => 'soldOutIconFl', 'typ' => 's', 'def' => 'y'], // 품절아이콘출력여부
            ['val' => 'brandFl', 'typ' => 's', 'def' => 'y'], // 브랜드출력여부
            ['val' => 'shortDescFl', 'typ' => 's', 'def' => 'y'], // 짧은설명출력여부
            ['val' => 'makerFl', 'typ' => 's', 'def' => 'y'], // 원산지출력여부
            ['val' => 'fixedPriceFl', 'typ' => 's', 'def' => 'y'], // 정가출력여부
            ['val' => 'mileageFl', 'typ' => 's', 'def' => 'y'], // 마일리지출력여부
            ['val' => 'iconFl', 'typ' => 's', 'def' => 'y'], // 아이콘출력여부
            ['val' => 'optionFl', 'typ' => 's', 'def' => 'y'], // 옵션출력여부
            ['val' => 'soldOutFl', 'typ' => 's', 'def' => 'y'], // 품절상품출력여부
        ];
        // @formatter:on

        return $arrField;
    }

    /**
     * [ORDER] tableOrderDownloadForm 필드 기본값
     *
     * @author sj
     * @return array tableOrderDownloadForm 테이블 필드 정보
     */
    public static function tableOrderDownloadForm()
    {
        // @formatter:off
        $arrField = [
            ['val' => 'formNm', 'typ' => 's', 'def' => null], // 양식명칭
            ['val' => 'formField', 'typ' => 's', 'def' => null], // 출력내용
            ['val' => 'formFieldTxt', 'typ' => 's', 'def' => null], // 출력내용텍스트
            ['val' => 'formSort', 'typ' => 's', 'def' => null], // 출력순서
        ];
        // @formatter:on

        return $arrField;
    }

    /**
     * [MARKETING] tableMarketing 필드 기본값
     *
     * @author sj
     * @return array tableMarketing 테이블 필드 정보
     */
    public static function tableMarketing()
    {
        // @formatter:off
        $arrField = [
            ['val' => 'company', 'typ' => 's', 'def' => null], // 업체명
            ['val' => 'mode', 'typ' => 's', 'def' => null], // 저장정보
            ['val' => 'value', 'typ' => 's', 'def' => null], // 저장내용
        ];
        // @formatter:on

        return $arrField;
    }

    /**
     * [SCHEDULE] tableSchedule 필드 기본값
     *
     * @author sunny
     * @return array tableSchedule 테이블 필드 정보
     */
    public static function tableSchedule()
    {
        // @formatter:off
        $arrField = [
            ['val' => 'scdDt', 'typ' => 's', 'def' => null], // 일자
            ['val' => 'subject', 'typ' => 's', 'def' => null], // 제목
            ['val' => 'contents', 'typ' => 's', 'def' => null], // 내용
            ['val' => 'alarm', 'typ' => 's', 'def' => 'y'], // 알람
        ];
        // @formatter:on

        return $arrField;
    }

    /**
     * [NAVERCHECKOUT] tableNavercheckout 필드 기본값
     *
     * @author sj
     * @return array tableNavercheckout 테이블 필드 정보
     */
    public static function tableNavercheckout()
    {
        // @formatter:off
        $arrField = [
            ['val' => 'shippingType', 'typ' => 's', 'def' => null], // 배송타입
            ['val' => 'shippingPrice', 'typ' => 'i', 'def' => null], // 배송비
            ['val' => 'totalPrice', 'typ' => 'i', 'def' => null], // 총가격
            ['val' => 'checkoutOrderId', 'typ' => 's', 'def' => null], // 체크아웃 주문ID
        ];
        // @formatter:on

        return $arrField;
    }

    /**
     * [NAVERCHECKOUT_ITEM] tableNavercheckoutItem 필드 기본값
     *
     * @author sj
     * @return array tableNavercheckoutItem 테이블 필드 정보
     */
    public static function tableNavercheckoutItem()
    {
        // @formatter:off
        $arrField = [
            ['val' => 'goodsNo', 'typ' => 'i', 'def' => null], // 상품번호
            ['val' => 'checkoutSno', 'typ' => 'i', 'def' => null], // checkoutSno
            ['val' => 'goodsNm', 'typ' => 's', 'def' => null], // 상품명
            ['val' => 'goodsPrice', 'typ' => 'i', 'def' => null], // 상품가격
            ['val' => 'goodsCnt', 'typ' => 'i', 'def' => null], // 상품수량
            ['val' => 'goodsOpt', 'typ' => 's', 'def' => null], // 상품옵션
            ['val' => 'stockable', 'typ' => 's', 'def' => null], // 재고설정
            ['val' => 'stockstatus', 'typ' => 's', 'def' => null], // 재고적용
        ];
        // @formatter:on

        return $arrField;
    }

    /**
     * [CRM_COUNSEL] table_crmCounsel 필드 기본값
     *
     * @author sj
     * @return array table_crmCounsel 테이블 필드 정보
     */
    public static function tableCrmCounsel()
    {
        // @formatter:off
        $arrField = [
            ['val' => 'managerNo', 'typ' => 'i', 'def' => null], // 담당자
            ['val' => 'memNo', 'typ' => 'i', 'def' => null], // 회원번호
            ['val' => 'contents', 'typ' => 's', 'def' => null], // 내용
            ['val' => 'method', 'typ' => 's', 'def' => null], // 상담수단
            ['val' => 'kind', 'typ' => 's', 'def' => null], // 상담유형
        ];
        // @formatter:on

        return $arrField;
    }

    /**
     * [QRCODE] tableQrcode 필드 기본값
     *
     * @author yjwee
     * @return array tableQrcode 테이블 필드 정보
     */
    public static function tableQrcode()
    {
        // @formatter:off
        $arrField = [
            ['val' => 'sno', 'typ' => 'i', 'def' => null], // 일련번호
            ['val' => 'qrType', 'typ' => 's', 'def' => null], // 종류
            ['val' => 'contsNo', 'typ' => 'i', 'def' => null], // 생성번호
            ['val' => 'qrString', 'typ' => 's', 'def' => null], // 내용
            ['val' => 'qrName', 'typ' => 's', 'def' => null], // 이름
            ['val' => 'qrSize', 'typ' => 'i', 'def' => null], // 크기
            ['val' => 'qrVersion', 'typ' => 'i', 'def' => null] // 버전
        ];
        // @formatter:on

        return $arrField;
    }

    public static function tableScmBoard()
    {
        // @formatter:off
        $arrField = [
            ['val' => 'managerNo', 'typ' => 'i', 'def' => null],
            ['val' => 'scmNo', 'typ' => 'i', 'def' => DEFAULT_CODE_SCMNO],
            ['val' => 'category', 'typ' => 's', 'def' => null],
            ['val' => 'scmFl', 'typ' => 's', 'def' => null],
            ['val' => 'saveFiles', 'typ' => 's', 'def' => ''],
            ['val' => 'subject', 'typ' => 's', 'def' => ''],
            ['val' => 'isNotice', 'typ' => 's', 'def' => 'n'],
            ['val' => 'isDelete', 'typ' => 's', 'def' => 'n'],
            ['val' => 'contents', 'typ' => 's', 'def' => ''],
            ['val' => 'uploadFiles', 'typ' => 's', 'def' => ''],
            ['val' => 'groupNo', 'typ' => 's', 'def' => ''],
            ['val' => 'groupThread', 'typ' => 's', 'def' => ''],
            ['val' => 'parentSno', 'typ' => 'i', 'def' => '0'],
        ];
        // @formatter:on

        return $arrField;
    }

    public static function tableScmBoardGroup()
    {
        // @formatter:off
        $arrField = [
            ['val' => 'scmBoardSno', 'typ' => 'i', 'def' => null], //공급사게시판 번호
            ['val' => 'scmNo', 'typ' => 'i', 'def' => DEFAULT_CODE_SCMNO], // 공급사번호
        ];
        // @formatter:on

        return $arrField;
    }


    /**
     * [프로모션] timesale 필드 기본값
     *
     * @author artherot
     * @return array wish 테이블 필드 정보
     */
    public static function tableTimeSale()
    {
        // @formatter:off
        $arrField = [
            ['val' => 'sno', 'typ' => 'i', 'def' => '0'], // 고유번호
            ['val' => 'timeSaleTitle', 'typ' => 's', 'def' => null], // 상품 번호
            ['val' => 'startDt', 'typ' => 's', 'def' => null], // 시작일
            ['val' => 'endDt', 'typ' => 's', 'def' => null], // 종료일
            ['val' => 'goodsNmDescription', 'typ' => 's', 'def' => null], // 상품명 말머리
            ['val' => 'benefit', 'typ' => 's', 'def' => null], // 할인금액
            ['val' => 'goodsPriceViewFl', 'typ' => 's', 'def' => 'y'], // 판매가노출
            ['val' => 'orderCntDisplayFl', 'typ' => 's', 'def' => 'y'], // 판매개수노출여부
            ['val' => 'orderCntDateFl', 'typ' => 's', 'def' => null], // 판매개수 노출시 날짜기준여부
            ['val' => 'stockFl', 'typ' => 's', 'def' => 'y'], // 재고 노출 여부
            ['val' => 'memberDcFl', 'typ' => 's', 'def' => 'n'], // 회원할인 사용 여부
            ['val' => 'mileageFl', 'typ' => 's', 'def' => 'n'], // 마일리지 적립 여부
            ['val' => 'couponFl', 'typ' => 's', 'def' => 'n'], // 쿠폰 할인 사용 여부
            ['val' => 'pcDisplayFl', 'typ' => 's', 'def' => 'y'], // pc사용여부
            ['val' => 'mobileDisplayFl', 'typ' => 's', 'def' => 'y'], // 모바일사용여부
            ['val' => 'leftTimeDisplayType', 'typ' => 's', 'def' => ''], // 모바일사용여부
            ['val' => 'pcDescription', 'typ' => 's', 'def' => null], // pc내용
            ['val' => 'mobileDescription', 'typ' => 's', 'def' => null], //모방리 내용
            ['val' => 'sort', 'typ' => 's', 'def' => null], // 정렬
            ['val' => 'pcThemeCd', 'typ' => 's', 'def' => null], // pc적용테마
            ['val' => 'mobileThemeCd', 'typ' => 's', 'def' => null], //모바일 적용 테마
            ['val' => 'goodsNo', 'typ' => 's', 'def' => null], //상품번호 (json)
            ['val' => 'fixGoodsNo', 'typ' => 's', 'def' => null], //상품번호 (json)
            ['val' => 'managerNo', 'typ' => 'i', 'def' => null], // 운영자 고유번호
            ['val' => 'delFl', 'typ' => 's', 'def' => 'n'], //삭제여부
            ['val' => 'moreBottomFl', 'typ' => 's', 'def' => 'y'],   //하단 버튼 노출
        ];
        // @formatter:on

        return $arrField;
    }

    /**
     * tableAdminLog
     *
     * @static
     * @return array
     */
    public static function tableAdminLog()
    {
        // @formatter:off
        $arrField = [
            ['val' => 'uri', 'typ' => 's', 'def' => null], // 페이지URI
            ['val' => 'baseUri', 'typ' => 's', 'def' => null], // 웹 노출여부
            ['val' => 'data', 'typ' => 's', 'def' => null], // 해더정보
            ['val' => 'menu', 'typ' => 's', 'def' => null], // 메뉴명
            ['val' => 'referer', 'typ' => 's', 'def' => null], // 리퍼럴
            ['val' => 'type', 'typ' => 's', 'def' => null], // 타입(개인정보접속 , 개인정보취급자,기타)
            ['val' => 'page', 'typ' => 's', 'def' => null], // 접속페이지
            ['val' => 'action', 'typ' => 's', 'def' => null], // 수행업무
            ['val' => 'managerNo', 'typ' => 'i', 'def' => null], // 운영자 고유번호
            ['val' => 'managerId', 'typ' => 's', 'def' => null], // 운영자 아이디
            ['val' => 'scmNo', 'typ' => 'i', 'def' => DEFAULT_CODE_SCMNO], // 공급사번호
            ['val' => 'ip', 'typ' => 's', 'def' => null], // 아이피

        ];
        // @formatter:on

        return $arrField;
    }


    public static function tableAdminMenu()
    {
        // @formatter:off
        $arrField = [
            ['val' => 'adminMenuNo', 'typ' => 'i', 'def' => null], // 관리자 메뉴 고유번호
            ['val' => 'adminMenuType', 'typ' => 's', 'def' => 'd'], // 관리자 메뉴 타입 - d(본사),s(공급사)
            ['val' => 'adminMenuProductCode', 'typ' => 's', 'def' => 'godomall'], // 관리자 메뉴 제작사 코드
            ['val' => 'adminMenuPlusCode', 'typ' => 's', 'def' => null], // 관리자 메뉴 플러스(앱) 코드
            ['val' => 'adminMenuCode', 'typ' => 's', 'def' => null], // 관리자 메뉴 코드
            ['val' => 'adminMenuDepth', 'typ' => 'i', 'def' => 1], // 관리자 메뉴 뎁스 1(1차) 2(2차) 3(3차)
            ['val' => 'adminMenuParentNo', 'typ' => 's', 'def' => null], // 관리자 메뉴 상위 메뉴 고유번호
            ['val' => 'adminMenuSort', 'typ' => 'i', 'def' => 1], // 관리자 메뉴 순서
            ['val' => 'adminMenuName', 'typ' => 's', 'def' => null], // 관리자 메뉴 타이틀
            ['val' => 'adminMenuUrl', 'typ' => 's', 'def' => null], // 관리자 메뉴 링크
            ['val' => 'adminMenuDisplayType', 'typ' => 's', 'def' => 'y'], // 관리자 메뉴 노출 여부 - y(노출), n(숨김)
            ['val' => 'adminMenuSettingType', 'typ' => 's', 'def' => 'd'], // 관리자 메뉴 셋팅 타입 - d(기본), p(플러스샵)
            ['val' => 'adminMenuEcKind', 'typ' => 's', 'def' => 'a'], // 관리자 메뉴 고도5 상품군에 따른 기능 여부 - a(전체), f(무료형), r(임대형)
            ['val' => 'adminMenuHideVersion', 'typ' => 's', 'def' => ''], // 관리자 메뉴 미노출 시스템버전 - 000000(최신버전미노출),yymmdd(yymmdd버전미노출)
        ];
        // @formatter:on

        return $arrField;
    }

    /**
     * [프로모션] es_attendance 필드 기본값
     *
     * @author yjwee
     * @return array 테이블 필드 정보
     */
    public static function tableAttendance()
    {
        // @formatter:off
        $arrField = [
            ['val' => 'title', 'typ' => 's', 'def' => null], // 출석체크 이벤트명
            ['val' => 'startDt', 'typ' => 's', 'def' => null], // 이벤트 시작일
            ['val' => 'endDt', 'typ' => 's', 'def' => null], // 이벤트 종료일
            ['val' => 'deviceFl', 'typ' => 's', 'def' => 'pc'], // 진행범위
            ['val' => 'groupFl', 'typ' => 's', 'def' => 'all'], // 참여가능 회원등급
            ['val' => 'groupSno', 'typ' => 's', 'def' => null], // 선택된 참여가능 회원등급
            ['val' => 'methodFl', 'typ' => 's', 'def' => 'stamp'], // 출석방법
            ['val' => 'conditionFl', 'typ' => 's', 'def' => 'sum'], // 이벤트조건
            ['val' => 'conditionCount', 'typ' => 'i', 'def' => null], // 이벤트조건 달성 회수
            ['val' => 'benefitGiveFl', 'typ' => 's', 'def' => 'auto'], // 혜택지급 방법
            ['val' => 'benefitFl', 'typ' => 's', 'def' => null], // 이벤트 조건달성 시 지급혜택
            ['val' => 'benefitMileage', 'typ' => 'i', 'def' => null], // 지급혜택 마일리지
            ['val' => 'benefitCouponSno', 'typ' => 'i', 'def' => null], // 지급혜택 쿠폰
            ['val' => 'designHeadFl', 'typ' => 's', 'def' => 'default'], // 상단 영역
            ['val' => 'designHead', 'typ' => 's', 'def' => null], // 상단 영역 에디터
            ['val' => 'designBodyFl', 'typ' => 's', 'def' => 'stamp1'], // 본문 스킨
            ['val' => 'designFooter', 'typ' => 's', 'def' => null], // 하단 영역
            ['val' => 'stampFl', 'typ' => 's', 'def' => 'default'], // 스탬프 이미지
            ['val' => 'stampPath', 'typ' => 's', 'def' => null], // 사용자 스탬프 이미지 경로
            ['val' => 'completeComment', 'typ' => 's', 'def' => '출석이 완료되었습니다. 내일도 참여해주세요.'], // 출석완료 시 메시지
            ['val' => 'conditionComment', 'typ' => 's', 'def' => '축하드립니다! 출석목표가 달성되었습니다.'], // 이벤트 조건달성 시 메시지
            ['val' => 'managerNo', 'typ' => 'i', 'def' => null] // 관리자 일련번호
        ];
        // @formatter:on

        return $arrField;
    }

    /**
     * [프로모션] es_attendanceCheck 필드 기본값
     *
     * @author yjwee
     * @return array 테이블 필드 정보
     */
    public static function tableAttendanceCheck()
    {
        // @formatter:off
        $arrField = [
            ['val' => 'memNo', 'typ' => 'i', 'def' => null], // 회원번호
            ['val' => 'attendanceSno', 'typ' => 'i', 'def' => null], // 출석체크 일련번호
            ['val' => 'attendanceCount', 'typ' => 'i', 'def' => null], // 누적/연속 참여 횟수
            ['val' => 'attendanceHistory', 'typ' => 's', 'def' => null], // 출석체크 참여 내역
            ['val' => 'conditionDt', 'typ' => 's', 'def' => null], // 조건달성일시
            ['val' => 'benefitDt', 'typ' => 's', 'def' => null], // 혜택지급일시
        ];
        // @formatter:on

        return $arrField;
    }

    /**
     * [프로모션] es_attendanceReply 필드 기본값
     *
     * @author yjwee
     * @return array 테이블 필드 정보
     */
    public static function tableAttendanceReply()
    {
        // @formatter:off
        $arrField = [
            ['val' => 'memNo', 'typ' => 'i', 'def' => null], // 회원번호
            ['val' => 'checkSno', 'typ' => 'i', 'def' => null], // 출석체크 참석 일련번호
            ['val' => 'reply', 'typ' => 's', 'def' => null], // 출석 메시지
        ];
        // @formatter:on

        return $arrField;
    }

    /**
     * [주문] es_ghostDepositor 필드 기본값
     *
     * @author cjb3333
     * @return array 테이블 필드 정보
     */
    public static function tableGhostDepositor()
    {
        // @formatter:off
        $arrField = [
            ['val' => 'sno', 'typ' => 'i', 'def' => null], // 일련번호
            ['val' => 'depositDate', 'typ' => 's', 'def' => null], // 입금일자
            ['val' => 'bankName', 'typ' => 's', 'def' => null], // 은행명
            ['val' => 'ghostDepositor', 'typ' => 's', 'def' => null], // 미확인 입금자
            ['val' => 'depositPrice', 'typ' => 's', 'def' => null], // 입금액
        ];

        // @formatter:on

        return $arrField;
    }

    /**
     * tableSeo
     *
     * @static
     * @return array
     * @author atomyang
     */
    public static function tableSeoTag()
    {
        // @formatter:off
        $arrField = [
            ['val' => 'sno', 'typ' => 'i', 'def' => null], // 일련번호
            ['val' => 'path', 'typ' => 's', 'def' => null], // 페이지 경로
            ['val' => 'mallSno', 'typ' => 's', 'def' => null], // 몰번호
            ['val' => 'deviceFl', 'typ' => 's', 'def' => null], // 접근디바이스관련
            ['val' => 'pageCode', 'typ' => 's', 'def' => null], // 페이지고유코드
            ['val' => 'title', 'typ' => 's', 'def' => null], // 타이틀
            ['val' => 'author', 'typ' => 's', 'def' => null], // 메타태그 설명자
            ['val' => 'description', 'typ' => 's', 'def' => null], // 메타태그설명
            ['val' => 'keyword', 'typ' => 's', 'def' => null], // 메타태그 키워드
        ];
        // @formatter:on

        return $arrField;
    }

    /**
     * tableShortUrl
     *
     * @static
     * @return array
     * @author Jong-tae Ahn <qnibus@godo.co.kr>
     */
    public static function tableShortUrl()
    {
        // @formatter:off
        $arrField = [
            ['val' => 'sno', 'typ' => 'i', 'def' => null], // 일련번호
            ['val' => 'managerNo', 'typ' => 's', 'def' => null], // 매니저 번호
            ['val' => 'id', 'typ' => 's', 'def' => null], // 단축 URL 일련번호
            ['val' => 'shortUrl', 'typ' => 's', 'def' => null], // 단축 URL
            ['val' => 'longUrl', 'typ' => 's', 'def' => null], // 원래 URL
            ['val' => 'description', 'typ' => 's', 'def' => null], // URL 설명
            ['val' => 'count', 'typ' => 'i', 'def' => null], // 조회수
        ];
        // @formatter:on

        return $arrField;
    }

    public static function tableShortUrlStatistics()
    {
        // @formatter:off
        $arrField = [
            ['val' => 'sno', 'typ' => 'i', 'def' => null], // 일련번호
            ['val' => 'shortUrlNo', 'typ' => 'i', 'def' => null], // 단축주소 일련번호
            ['val' => 'year', 'typ' => 's', 'def' => null], // 집계 연도
            ['val' => 'month', 'typ' => 's', 'def' => null], // 집계 월
            ['val' => 'day', 'typ' => 's', 'def' => null], // 집계 일
            ['val' => 'count', 'typ' => 'i', 'def' => null], // 접속카운트
        ];
        // @formatter:on

        return $arrField;
    }

    /**
     * es_managerSearchConfig 필드 기본값
     *
     * @author cjb3333
     * @return array 테이블 필드 정보
     */
    public static function tableManagerSearchConfig()
    {
        // @formatter:off
        $arrField = [
            ['val' => 'sno', 'typ' => 'i', 'def' => null], // 일련번호
            ['val' => 'managerSno', 'typ' => 'i', 'def' => null], // 운영자일련번호
            ['val' => 'applyPath', 'typ' => 's', 'def' => null], // 적용경로
            ['val' => 'data', 'typ' => 's', 'def' => null], // 내용
            ['val' => 'isOrderSearchMultiGrid', 'typ' => 's', 'def' => 'n'], //주문 멀티검색여부
        ];

        // @formatter:on

        return $arrField;
    }

    /**
     * es_multiMall 필드 기본값 멀티 상점 테이블 정보 반환하는 랩핑 함수
     *
     * @static
     * @author yjwee
     * @return array
     */
    public static function tableMall()
    {
        return \Framework\Helper\MallHelper::getInstance()->tableMall();
    }

    /**
     * es_poll 필드 기본값
     *
     * @author Bagyj
     * @return array 테이블 필드 정보
     */
    public static function tablePoll()
    {
        // @formatter:off
        $arrField = [
            ['val' => 'sno', 'typ' => 'i', 'def' => null], // 일련번호
            ['val' => 'pollCode', 'typ' => 's', 'def' => null], // 설문조사코드
            ['val' => 'pollTitle', 'typ' => 's', 'def' => null], // 설문제목
            ['val' => 'pollStatusFl', 'typ' => 's', 'def' => 'Y'], // 진행상태
            ['val' => 'pollStartDt', 'typ' => 's', 'def' => null], // 설문시작일
            ['val' => 'pollEndDt', 'typ' => 's', 'def' => null], // 설문종료일
            ['val' => 'pollEndDtFl', 'typ' => 's', 'def' => 'N'], // 종료기간제한여부
            ['val' => 'pollDeviceFl', 'typ' => 's', 'def' => 'pc'], // 진행범위
            ['val' => 'pollGroupFl', 'typ' => 's', 'def' => 'all'], // 참여대상
            ['val' => 'pollGroupSno', 'typ' => 's', 'def' => null], // 참여대상회원등급
            ['val' => 'pollMemberLimitFl', 'typ' => 's', 'def' => 'unlimited'], // 참여인원 제한 사용 여부 (unlimited: 무제한, max: 최대)
            ['val' => 'pollMemberLimitCnt', 'typ' => 'i', 'def' => 0], // 참여 제한 인원 수
            ['val' => 'pollBannerLimitFl', 'typ' => 's', 'def' => 'N'], // 참여 제한에 따른 배너 노출 여부
            ['val' => 'pollBannerFl', 'typ' => 's', 'def' => 'def'], // 설문배너 이미지 사용여부
            ['val' => 'pollBannerImg', 'typ' => 's', 'def' => null], // 설문배너 이미지 직접 등록
            ['val' => 'pollBannerImgMobile', 'typ' => 's', 'def' => null], // 설문배너 모바일 이미지 직접 등록
            ['val' => 'pollViewPosition', 'typ' => 's', 'def' => null], // 설문배너 위치
            ['val' => 'pollViewCategory', 'typ' => 's', 'def' => null], // 설문배너 카테고리 위치
            ['val' => 'pollResultViewFl', 'typ' => 's', 'def' => 'Y'], // 참여완료시 결과보기 여부
            ['val' => 'pollMileage', 'typ' => 's', 'def' => null], // 설문조사 참여시 마일리지
            ['val' => 'pollHtmlContentFl', 'typ' => 's', 'def' => 'N'], // 상단안내영역
            ['val' => 'pollHtmlContentSameFl', 'typ' => 's', 'def' => 'N'], // pc/모바일 동일 html 사용여부
            ['val' => 'pollHtmlContent', 'typ' => 's', 'def' => null], // pc쇼핑몰 안내영역
            ['val' => 'pollHtmlContentMobile', 'typ' => 's', 'def' => null], // 모바일쇼핑몰 안내영역
            ['val' => 'pollItem', 'typ' => 's', 'def' => null], // 설문항목
            ['val' => 'managerSno', 'typ' => 'i', 'def' => null], // 관리자번호
            ['val' => 'regDt', 'typ' => 's', 'def' => null], // 등록일
        ];

        // @formatter:on

        return $arrField;
    }

    /**
     * es_pollResult 필드 기본값
     *
     * @author Bagyj
     * @return array 테이블 필드 정보
     */
    public static function tablePollResult()
    {
        // @formatter:off
        $arrField = [
            ['val' => 'pollCode', 'typ' => 's', 'def' => null], // 설문조사코드
            ['val' => 'memNo', 'typ' => 'i', 'def' => null], // 작성자번호
            ['val' => 'pollResult', 'typ' => 's', 'def' => null], // 설문결과1
            ['val' => 'pollResultEtc', 'typ' => 's', 'def' => null], // 설문결과2
            ['val' => 'mileage', 'typ' => 'i', 'def' => null], // 마일리지
        ];

        // @formatter:on

        return $arrField;
    }

    /**
     * es_tmpGoodsImage 필드 기본값
     *
     * @author Bagyj
     * @return array 테이블 필드 정보
     */
    public static function tableTmpGoodsImage()
    {
        // @formatter:off
        $arrField = [
            ['val' => 'goodsNo', 'typ' => 'i', 'def' => null], // 상품번호
            ['val' => 'imageKind', 'typ' => 's', 'def' => null], // 이미지종류
            ['val' => 'imageName', 'typ' => 's', 'def' => null], // 이미지이름
            ['val' => 'status', 'typ' => 's', 'def' => null], // 파일처리준비상태(noGoods>상품없음 , fileExists>이미존재함, ready>처리가능상태)
            ['val' => 'imagePath', 'typ' => 's', 'def' => null], // 이미지경로
        ];

        // @formatter:on

        return $arrField;
    }

    /**
     * tableOutSideScript
     * es_outSideScript 필드 기본값
     *
     * @static
     * @return array
     */
    public static function tableOutSideScript()
    {
        // @formatter:off
        $arrField = [
            ['val' => 'outSideScriptNo', 'typ' => 'i', 'def' => null], // 고유번호
            ['val' => 'outSideScriptServiceName', 'typ' => 's', 'def' => ''], // 서비스명
            ['val' => 'outSideScriptUse', 'typ' => 's', 'def' => 'y'], // 사용여부
            ['val' => 'outSideScriptUseHeader', 'typ' => 's', 'def' => 'n'], // header 사용여부
            ['val' => 'outSideScriptUseFooter', 'typ' => 's', 'def' => 'n'], // footer 사용여부
            ['val' => 'outSideScriptUsePage', 'typ' => 's', 'def' => 'n'], // page 사용여부
            ['val' => 'outSideScriptHeaderPC', 'typ' => 's', 'def' => null], // header PC 스크립트
            ['val' => 'outSideScriptHeaderMobile', 'typ' => 's', 'def' => null], // header mobile 스크립트
            ['val' => 'outSideScriptFooterPC', 'typ' => 's', 'def' => null], // footer PC 스크립트
            ['val' => 'outSideScriptFooterMobile', 'typ' => 's', 'def' => null], // footer mobile 스크립트
            ['val' => 'outSideScriptPage', 'typ' => 'j', 'def' => null], // page 스크립트 정보 json
            ['val' => 'managerNo', 'typ' => 'i', 'def' => ''], // 등록/수정 관리자고유번호
            ['val' => 'managerNm', 'typ' => 's', 'def' => ''], // 등록/수정 관리자 이름
            ['val' => 'managerId', 'typ' => 's', 'def' => ''], // 등록/수정 관리자 아이디
        ];

        // @formatter:on

        return $arrField;
    }

    /**
     * es_insgoWidget 필드 기본값
     *
     * @author yoonar
     * @return array 테이블 필드 정보
     */
    public static function tableInsgoWidget()
    {
        // @formatter:off
        $arrField = [
            ['val' => 'sno', 'typ' => 'i', 'def' => null], // 위젯번호
            ['val' => 'insgoAccessToken', 'typ' => 's', 'def' => null], // 엑세스토큰
            ['val' => 'insgoId', 'typ' => 's', 'def' => null], // 인스타그램 아이디
            ['val' => 'insgoName', 'typ' => 's', 'def' => null], // 위젯명
            ['val' => 'insgoDisplayType', 'typ' => 's', 'def' => 'grid'], // 위젯타입
            ['val' => 'insgoWidthCount', 'typ' => 'i', 'def' => null], // 레이아웃(가로)
            ['val' => 'insgoHeightCount', 'typ' => 'i', 'def' => null], // 레이아웃(세로)
            ['val' => 'insgoThumbnailSize', 'typ' => 's', 'def' => 'auto'], // 썸네일사이즈
            ['val' => 'insgoThumbnailSizePx', 'typ' => 'i', 'def' => null], // 썸네일사이즈수동넓이
            ['val' => 'insgoThumbnailBorder', 'typ' => 's', 'def' => 'y'], // 이미지테두리
            ['val' => 'insgoBackgroundColor', 'typ' => 's', 'def' => null], // 위젯배경색
            ['val' => 'insgoImageMargin', 'typ' => 'i', 'def' => null], // 이미지간격
            ['val' => 'insgoOverEffect', 'typ' => 's', 'def' => 'n'], // 마우스오버시효과
            ['val' => 'insgoWidth', 'typ' => 'i', 'def' => null], // 위젯가로사이즈
            ['val' => 'insgoAutoScroll', 'typ' => 's', 'def' => null], // 자동스크롤
            ['val' => 'insgoScrollSpeed', 'typ' => 's', 'def' => null], // 전환속도선택
            ['val' => 'insgoScrollTime', 'typ' => 'i', 'def' => null], // 전환시간선택
            ['val' => 'insgoSideButtonColor', 'typ' => 's', 'def' => null], // 좌우전환버튼색상
            ['val' => 'insgoEffect', 'typ' => 's', 'def' => null], // 효과선택
            ['val' => 'insgoData', 'typ' => 's', 'def' => null], // 위젯스크립트
            ['val' => 'insgoManagerNo', 'typ' => 'i', 'def' => (string)Session::get('manager.scmNo')],  // 등록자번호
            ['val' => 'regDt', 'typ' => 's', 'def' => null], // 등록일
            ['val' => 'modDt', 'typ' => 's', 'def' => null], // 수정일
        ];
        // @formatter:on

        return $arrField;
    }

    /**
     * 패치관리 시스템
     *
     * @static
     * @return array
     */
    public static function tablePatchManage()
    {
        // @formatter:off
        $arrField = [
            ['val' => 'patchArticleSno', 'typ' => 'i', 'def' => 0], // 패치글 번호
            ['val' => 'patchFileSno', 'typ' => 'i', 'def' => 0], // 패치파일 번호
            ['val' => 'status', 'typ' => 's', 'def' => '0'],  // 패치상태
        ];
        // @formatter:on

        return $arrField;
    }

    /**
     * es_commonContent 필드 기본값
     *
     * @author kookoo135
     * @return array 테이블 필드 정보
     */
    public static function tableCommonContent()
    {
        // @formatter:off
        $arrField = [
            ['val' => 'sno', 'typ' => 'i', 'def' => null], // 일련번호
            ['val' => 'commonTitle', 'typ' => 's', 'def' => null], // 공통정보제목
            ['val' => 'commonStatusFl', 'typ' => 's', 'def' => 'n'], // 노출기간
            ['val' => 'commonStartDt', 'typ' => 's', 'def' => null], // 노출 시작일
            ['val' => 'commonEndDt', 'typ' => 's', 'def' => null], // 노출 종료일
            ['val' => 'commonUseFl', 'typ' => 's', 'def' => 'y'], // 노출상태
            ['val' => 'commonTargetFl', 'typ' => 's', 'def' => 'all'], // 노출상품선택
            ['val' => 'commonCd', 'typ' => 's', 'def' => null], // 노출정보
            ['val' => 'commonExGoods', 'typ' => 's', 'def' => null], // 노출예외상품
            ['val' => 'commonExCategory', 'typ' => 's', 'def' => null], // 노출예외카테고리
            ['val' => 'commonExBrand', 'typ' => 's', 'def' => null], // 노출예외브랜드
            ['val' => 'commonExScm', 'typ' => 's', 'def' => null], // 노출예외공급사
            ['val' => 'commonHtmlContentSameFl', 'typ' => 's', 'def' => null], // PC/모바일 동일사용
            ['val' => 'commonHtmlContent', 'typ' => 's', 'def' => null], // pc쇼핑몰 공통정보
            ['val' => 'commonHtmlContentMobile', 'typ' => 's', 'def' => null], // 모바일쇼핑몰 공통정보
            ['val' => 'regDt', 'typ' => 's', 'def' => null], // 등록일
            ['val' => 'modDt', 'typ' => 's', 'def' => null], // 수정일
        ];
        // @formatter:on

        return $arrField;
    }

    /**
     * [게시판] 답변 템플릿
     *
     * @author sj
     * @return array 게시판 답변 템플릿 정보
     */
    public static function tableBoardTemplate()
    {
        // @formatter:off
        $arrField = [
            ['val' => 'templateType', 'typ' => 's', 'def' => null], // 템플릿 분류(쇼핑몰,관리자)
            ['val' => 'subject', 'typ' => 's', 'def' => null], // 제목
            ['val' => 'contents', 'typ' => 's', 'def' => null], // 내용
        ];
        // @formatter:on

        return $arrField;
    }

    /**
     * es_recommandGoods 필드 기본값
     *
     * @author yoonar
     * @return array 테이블 필드 정보
     */
    public static function tableRecommendGoods()
    {
        // @formatter:off
        $arrField = [
            ['val' => 'sno', 'typ' => 'i', 'def' => null], // 일련번호
            ['val' => 'goodsNo', 'typ' => 'i', 'def' => null], // 상품번호
            ['val' => 'regDt', 'typ' => 's', 'def' => null], // 등록일
            ['val' => 'modDt', 'typ' => 's', 'def' => null], // 수정일
        ];
        // @formatter:on

        return $arrField;
    }

    /**
     * [표준] countries 필드 기본값
     *
     * @author qnibus
     * @return array countries 테이블 필드 정보
     */
    public static function tableCountries()
    {
        // @formatter:off
        $arrField = [
            ['val' => 'code', 'typ' => 's', 'def' => null], // 표준국가코드 2자리 영문
            ['val' => 'zoneCode', 'typ' => 's', 'def' => null], // 지역코드 1자리 숫자
            ['val' => 'currencyCode', 'typ' => 's', 'def' => null], // 표준통화코드 3자리 영문
            ['val' => 'isoNo', 'typ' => 's', 'def' => null], // 표준국가 숫자
            ['val' => 'isoCode3', 'typ' => 'i', 'def' => null], // 표준국가 3자리 축약
            ['val' => 'isoCountry', 'typ' => 'i', 'def' => '0'], // 표준국가명
            ['val' => 'countryName', 'typ' => 'i', 'def' => '0'], // 국가명
            ['val' => 'countryNameKor', 'typ' => 's', 'def' => 'n'], // 한글 국가명
            ['val' => 'active', 'typ' => 's', 'def' => 'n'], // 활성화 여부
            ['val' => 'callPrefix', 'typ' => 's', 'def' => 'n'], // 전화번호 프리픽스
            ['val' => 'containsStates', 'typ' => 's', 'def' => 'n'], // 주 포함 여부
            ['val' => 'needZipcode', 'typ' => 's', 'def' => 'n'], // 우편번호 필요여부
            ['val' => 'emsAreaCode', 'typ' => 's', 'def' => null], // EMS지역코드
            ['val' => 'lat', 'typ' => 's', 'def' => 'n'], // 경도
            ['val' => 'lon', 'typ' => 's', 'def' => 'n'], // 위도
        ];
        // @formatter:on

        return $arrField;
    }

    /**
     * es_orderExchangeHandle 필드 기본값
     *
     * @author by
     * @return array 테이블 필드 정보
     */
    public static function tableOrderExchangeHandle()
    {
        // @formatter:off
        $arrField = [
            ['val' => 'ehOrderNo', 'typ' => 's', 'def' => ''], // 주문번호
            ['val' => 'ehHandleGroupCd', 'typ' => 'i', 'def' => ''], // orderhandle의 handleGroupCd
            ['val' => 'ehDifferencePrice', 'typ' => 'i', 'def' => 0.00], // 총차액
            ['val' => 'ehCancelDeliveryPrice', 'typ' => 'i', 'def' => 0.00], // 취소된 배송비
            ['val' => 'ehAddDeliveryPrice', 'typ' => 'i', 'def' => 0.00], // 추가된 배송비
            ['val' => 'ehRefundMethod', 'typ' => 's', 'def' => ''], // 환불수단
            ['val' => 'ehRefundName', 'typ' => 's', 'def' => ''], // 환불예금주
            ['val' => 'ehRefundBankName', 'typ' => 's', 'def' => ''], // 환불은행명
            ['val' => 'ehRefundBankAccountNumber', 'typ' => 's', 'def' => ''], // 환불계좌번호
            ['val' => 'ehSettleName', 'typ' => 's', 'def' => ''], // 추가결제 입금자명
            ['val' => 'ehSettleBankAccountInfo', 'typ' => 's', 'def' => ''], // 추가결제 은행정보
            ['val' => 'ehEnuri', 'typ' => 'i', 'def' => null], // 에누리
        ];
        // @formatter:on

        return $arrField;
    }

    /**
     * globalCurrency 필드 기본값 반환하는 랩핑 함수
     *
     * @author su
     * @return array globalCurrency 테이블 필드 정보
     */
    public static function tableGlobalCurrency()
    {
        return \Framework\Helper\MallHelper::getInstance()->tableGlobalCurrency();
    }

    /**
     * overseasDeliveryBasic 필드 기본값
     *
     * @author qnibus
     * @return array overseasDeliveryBasic 테이블 필드 정보
     */
    public static function tableOverseasDeliveryBasic()
    {
        // @formatter:off
        $arrField = [
            ['val' => 'sno', 'typ' => 's', 'def' => null], // 일련번호
            ['val' => 'mallSno', 'typ' => 's', 'def' => '2'], // 사용상점 일련번호
            ['val' => 'basicWeight', 'typ' => 'd', 'def' => '0.00'], // 배송 기본무게
            ['val' => 'basicBulk', 'typ' => 'j', 'def' => 'n'], // 배송 부피조건
            ['val' => 'boxWeight', 'typ' => 's', 'def' => 'n'], // 박스무게
            ['val' => 'standardFl', 'typ' => 's', 'def' => 'n'], // 배송비 기준 설정 (self|ems)
            ['val' => 'emsAddCost', 'typ' => 'd', 'def' => '0.00'], // 소수점 자리수
            ['val' => 'emsAddCostUnit', 'typ' => 's', 'def' => 'won'], // 소수점 자리수
            ['val' => 'insuranceFl', 'typ' => 's', 'def' => 'n'], // 해외배송 보험료 사용여부
        ];
        // @formatter:on

        return $arrField;
    }

    /**
     * overseasDeliveryGroup 필드 기본값
     *
     * @author qnibus
     * @return array overseasDeliveryGroup 테이블 필드 정보
     */
    public static function tableOverseasDeliveryGroup()
    {
        // @formatter:off
        $arrField = [
            ['val' => 'sno', 'typ' => 's', 'def' => null], // 표준통화코드 2자리 영문
            ['val' => 'countryGroupSno', 'typ' => 'i', 'def' => '0'], // 기본 배송 정책 키
            ['val' => 'basicKey', 'typ' => 'i', 'def' => '0'], // 기본 배송 정책 키
            ['val' => 'deliverySno', 'typ' => 'i', 'def' => '0'], // 기본 배송 정책 키
            ['val' => 'groupSort', 'typ' => 'i', 'def' => '0'], // 배송그룹 우선순위
        ];
        // @formatter:on

        return $arrField;
    }

    /**
     * overseasDeliveryGroup 필드 기본값
     *
     * @author qnibus
     * @return array overseasDeliveryGroup 테이블 필드 정보
     */
    public static function tableOverseasDeliveryCountryGroup()
    {
        // @formatter:off
        $arrField = [
            ['val' => 'sno', 'typ' => 's', 'def' => null], // 표준통화코드 2자리 영문
            ['val' => 'groupName', 'typ' => 's', 'def' => null], // 배송 방법
        ];
        // @formatter:on

        return $arrField;
    }

    /**
     * overseasDeliveryGroup 필드 기본값
     *
     * @author qnibus
     * @return array overseasDeliveryGroup 테이블 필드 정보
     */
    public static function tableOverseasDeliveryCountries()
    {
        // @formatter:off
        $arrField = [
            ['val' => 'sno', 'typ' => 's', 'def' => null], // 배송국가 일련번호
            ['val' => 'countryIsoNo', 'typ' => 's', 'def' => null], // 표준국가번호
            ['val' => 'basicKey', 'typ' => 'i', 'def' => '0'], // 기본 배송 정책 키
        ];
        // @formatter:on

        return $arrField;
    }

    /**
     * overseasDeliveryGroup 필드 기본값
     *
     * @author qnibus
     * @return array overseasDeliveryGroup 테이블 필드 정보
     */
    public static function tableEmsRate()
    {
        // @formatter:off
        $arrField = [
            ['val' => 'sno', 'typ' => 's', 'def' => null], // 배송국가 일련번호
            ['val' => 'emsAreaCode', 'typ' => 'i', 'def' => '0'], // 기본 배송 정책 키
            ['val' => 'emsWeight', 'typ' => 'd', 'def' => '0.00'], // 기본 배송 정책 키
            ['val' => 'emsPrice', 'typ' => 'd', 'def' => '0.00'], // 기본 배송 정책 키
        ];
        // @formatter:on

        return $arrField;
    }

    public static function tableLanguage()
    {
        // @formatter:off
        $arrField = [
            ['val' => 'sno', 'typ' => 's', 'def' => null], // 배송국가 일련번호
            ['val' => 'original', 'typ' => 's', 'def' => null], // 기본 배송 정책 키
            ['val' => 'translate_us', 'typ' => 's', 'def' => null], // 기본 배송 정책 키
            ['val' => 'translate_jp', 'typ' => 's', 'def' => null], // 기본 배송 정책 키
            ['val' => 'translate_cn', 'typ' => 's', 'def' => null], // 기본 배송 정책 키
        ];
        // @formatter:on

        return $arrField;
    }

    /**
     * es_popupPage 필드 기본값
     *
     * @author kookoo135
     * @return array 테이블 필드 정보
     */
    public static function tablePopupPage()
    {
        // @formatter:off
        $arrField = [
            ['val' => 'sno', 'typ' => 'i', 'def' => null], // 일련번호
            ['val' => 'pcDisplayFl', 'typ' => 's', 'def' => null], // PC쇼핑몰 노출여부
            ['val' => 'mobileDisplayFl', 'typ' => 's', 'def' => null], // 모바일쇼핑몰 노출여부
            ['val' => 'pageName', 'typ' => 's', 'def' => null], // 페이지명
            ['val' => 'pageUrl', 'typ' => 's', 'def' => null], // URL
        ];
        // @formatter:on

        return $arrField;
    }

    /**
     * es_purchase 필드 기본값
     *
     * @author atomyang
     * @return array es_purchase 테이블 필드 정보
     */
    public static function tablePurchase()
    {
        // @formatter:off
        $arrField = [
            ['val' => 'purchaseNo', 'typ' => 's', 'def' => null ], //매입처고유번호
            ['val' => 'purchaseNm', 'typ' => 's', 'def' => null], //매입처명
            ['val' => 'purchaseCd', 'typ' => 's', 'def' => null], //매입처코드
            ['val' => 'useFl', 'typ' => 's', 'def' => 'y'], // 사용여부-운영('y'), 사용안함('n')
            ['val' => 'businessFl', 'typ' => 's', 'def' => 'y'], // 거래여부-운영('y'), 거래중지('n'), 거래해지('x')
            ['val' => 'category', 'typ' => 's', 'def' => ''], // 상품유형
            ['val' => 'bankName', 'typ' => 's', 'def' => ''], // 은행명
            ['val' => 'accountNumber', 'typ' => 's', 'def' => null], // 계좌번호
            ['val' => 'depositor', 'typ' => 's', 'def' => null], // 예금주
            ['val' => 'phone', 'typ' => 's', 'def' => null ], // 대표전화
            ['val' => 'fax', 'typ' => 's', 'def' => null], // 팩스번호
            ['val' => 'zipcode', 'typ' => 's', 'def' => null], // 우편번호
            ['val' => 'zonecode', 'typ' => 's', 'def' => null], // 우편번호(5자리)
            ['val' => 'address', 'typ' => 's', 'def' => null], // 주소
            ['val' => 'addressSub', 'typ' => 's', 'def' => null], // 상세주소
            ['val' => 'unstoringZipcode', 'typ' => 's', 'def' => null], // 기본 출고지 우편번호
            ['val' => 'unstoringZonecode', 'typ' => 's', 'def' => null], // 기본 출고지 우편번호(5자리)
            ['val' => 'unstoringAddress', 'typ' => 's', 'def' => null], // 기본 출고지 주소
            ['val' => 'unstoringAddressSub', 'typ' => 's', 'def' => null], // 기본 출고지 상세주소
            ['val' => 'returnZipcode', 'typ' => 's', 'def' => null], // 기본 반품/교환지 우편번호
            ['val' => 'returnZonecode', 'typ' => 's', 'def' => null], // 기본 반품/교환지 우편번호(5자리)
            ['val' => 'returnAddress', 'typ' => 's', 'def' => null], // 기본 반품/교환지 주소
            ['val' => 'returnAddressSub', 'typ' => 's', 'def' => null], // 기본 반품/교환지 상세주소
            ['val' => 'memo', 'typ' => 's', 'def' => null], // 메모
            ['val' => 'companyNm', 'typ' => 's', 'def' => null], //
            ['val' => 'businessNo', 'typ' => 's', 'def' => null], // 사업자 번호
            ['val' => 'ceoNm', 'typ' => 's', 'def' => null], // 대표자
            ['val' => 'service', 'typ' => 's', 'def' => null], // 업태
            ['val' => 'item', 'typ' => 's', 'def' => null], // 종목
            ['val' => 'staff', 'typ' => 's', 'def' => null], // 담당자 정보
            ['val' => 'delFl', 'typ' => 's', 'def' => 'n'], // 삭제여부
            ['val' => 'delDt', 'typ' => 's', 'def' => null], // 삭제일
        ];
        // @formatter:on

        return $arrField;
    }

    /**
     * 플러스 리뷰 게시글
     *
     * @author sj
     * @return array board 테이블 필드 정보
     */
    public static function tablePlusReviewArticle()
    {
        // @formatter:off
        $arrField = [
            ['val' => 'sno', 'typ' => 'i', 'def' => null], // 고유번호
            ['val' => 'channel', 'typ' => 's', 'def' => null], // 채널
            ['val' => 'groupNo', 'typ' => 'i', 'def' => null], // 순번
            ['val' => 'memNo', 'typ' => 'i', 'def' => 0], // 작성자번호
            ['val' => 'writerNm', 'typ' => 's', 'def' => null], // 작성자명
            ['val' => 'writerId', 'typ' => 's', 'def' => null], // 작성자아이디
            ['val' => 'writerNick', 'typ' => 's', 'def' => null], // 작성자닉네임
            ['val' => 'writerPw', 'typ' => 's', 'def' => null], // 비밀번호
            ['val' => 'writerIp', 'typ' => 's', 'def' => null], // 아이피
            ['val' => 'contents', 'typ' => 's', 'def' => null], // 글내용
            ['val' => 'uploadFileNm', 'typ' => 's', 'def' => null], // 원본이미지파일명
            ['val' => 'saveFileNm', 'typ' => 's', 'def' => null], // 저장이미지파일명
            ['val' => 'hit', 'typ' => 'i', 'def' => 0], // 조회수
            ['val' => 'memoCnt', 'typ' => 'i', 'def' => null], // 코멘트수
            ['val' => 'goodsNo', 'typ' => 'i', 'def' => 0], // 상품번호
            ['val' => 'goodsPt', 'typ' => 'i', 'def' => 0], // 상품평점
            ['val' => 'orderGoodsNo', 'typ' => 'i', 'def' => null], // 상품주문번호 (추가) apiExtraData
            ['val' => 'recommend', 'typ' => 'i', 'def' => 0],  //추천수
            ['val' => 'isShow', 'typ' => 's', 'def' => 'y'],    //프론트 노출여부. 작성 후 승인대기시 사용 (추가)
            ['val' => 'isMobile', 'typ' => 's', 'def' => 'n'],  //모바일 여부
            ['val' => 'applyFl', 'typ' => 's', 'def' => 'y'],  //승인여부
            ['val' => 'firstReviewFl', 'typ' => 's', 'def' => 'n'],  //첫리뷰여부
            ['val' => 'addFormData', 'typ' => 'j', 'def' => null], // 추가양식 옵션 (추가)
            ['val' => 'mileage', 'typ' => 'i', 'def' => 0], //적립된 마일리지
            ['val' => 'mileageGiveDt', 'typ' => 's', 'def' => '0000-00-00'], // 마일리지 지급예정일
            ['val' => 'mileagePolicy', 'typ' => 's', 'def' => null], // 마일리지 정책
            ['val' => 'checkoutData', 'typ' => 'j', 'def' => null], //외부 request 데이터
            ['val' => 'goodsReviewSno', 'typ' => 'i', 'def' => null], //상품후기게시판원본고유번호
            ['val' => 'migrationDt', 'typ' => 's', 'def' => null], //외부 request 데이터
            ['val' => 'modDt', 'typ' => 's', 'def' => null], // 수정일
            ['val' => 'regDt', 'typ' => 's', 'def' => null], // 등록일
        ];
        // @formatter:on

        return $arrField;
    }

    /**
     * 플러스 리뷰 댓글
     *
     * @author sj
     * @return array board 테이블 필드 정보
     */
    public static function tablePlusReviewMemo()
    {
        // @formatter:off
        $arrField = [
            ['val' => 'sno', 'typ' => 'i', 'def' => null], // 고유번호
            ['val' => 'articleSno', 'typ' => 'i', 'def' => null],    //원글
            ['val' => 'groupNo', 'typ' => 'i', 'def' => null], // 순번
            ['val' => 'memNo', 'typ' => 'i', 'def' => null], // 작성자no
            ['val' => 'writerPw', 'typ' => 's', 'def' => null], // 비밀번호
            ['val' => 'writerNick', 'typ' => 's', 'def' => null], // 작성자닉네임
            ['val' => 'writerNm', 'typ' => 's', 'def' => null], // 작성자명
            ['val' => 'writerId', 'typ' => 's', 'def' => null], // 작성자아이디
            ['val' => 'writerIp', 'typ' => 's', 'def' => null], // 작성자ip
            ['val' => 'memo', 'typ' => 's', 'def' => null], // 내용
            ['val' => 'goodsReviewMemoSno', 'typ' => 'i', 'def' => null], //상품후기 게시판 댓글 원본고유번호
            ['val' => 'migrationDt', 'typ' => 's', 'def' => null], //외부 request 데이터
            ['val' => 'modDt', 'typ' => 's', 'def' => null], // 수정일
            ['val' => 'regDt', 'typ' => 's', 'def' => null], // 등록일
        ];
        // @formatter:on

        return $arrField;
    }

    /**
     * 플러스 리뷰 추천
     *
     * @author sj
     * @return array board 테이블 필드 정보
     */
    public static function tablePlusReviewRecommend()
    {
        // @formatter:off
        $arrField = [
            ['val' => 'articleSno', 'typ' => 'i', 'def' => null], // 부모글
            ['val' => 'memNo', 'typ' => 'i', 'def' => null], // 회원번호
            ['val' => 'writerIp', 'typ' => 's', 'def' => null], // 아이피
            ['val' => 'regDt', 'typ' => 's', 'def' => null], // 등록일
        ];
        // @formatter:on

        return $arrField;
    }

    /**
     * 플러스리뷰 메인 팝업 스킵 상품목록
     *
     * @static
     * @return array
     */
    public static function tablePlusReviewPopupSkip()
    {
        // @formatter:off
        $arrField = [
            ['val' => 'memNo', 'typ' => 'i', 'def' => null], // 회원번호
            ['val' => 'orderNo', 'typ' => 's', 'def' => null], // 주문번호
            ['val' => 'goodsNo', 'typ' => 'i', 'def' => null], // 상품번호
            ['val' => 'regDt', 'typ' => 's', 'def' => null], // 등록일
        ];
        // @formatter:on

        return $arrField;
    }


    /**
     * 플러스리뷰 추가정보
     *
     * @static
     * @return array
     */
    public static function tablePlusReviewAddForm()
    {
        // @formatter:off
        $arrField = [
            ['val' => 'articleSno', 'typ' => 'i', 'def' => null], // 회원번호
            ['val' => 'labelNumber', 'typ' => 'i', 'def' => null], // 주문번호
            ['val' => 'labelValue', 'typ' => 's', 'def' => null], // 상품번호
            ['val' => 'regDt', 'typ' => 's', 'def' => null], // 등록일
            ['val' => 'modDt', 'typ' => 's', 'def' => null], // 수정일
        ];
        // @formatter:on

        return $arrField;
    }


    /**
     * goodsRestock 필드 기본값
     *
     * @author by
     * @return array goodsRestock 테이블 필드 정보
     */
    public static function tableGoodsRestockBasic()
    {
        $arrField = [
            ['val' => 'sno', 'typ' => 's', 'def' => null], //일련번호
            ['val' => 'goodsNo', 'typ' => 'i', 'def' => null], //상품번호
            ['val' => 'goodsNm', 'typ' => 's', 'def' => null], //상품명
            ['val' => 'optionName', 'typ' => 's', 'def' => null], //옵션명
            ['val' => 'optionSno', 'typ' => 'i', 'def' => 0], //옵션일련번호
            ['val' => 'optionValue', 'typ' => 's', 'def' => null], //옵션값
            ['val' => 'smsSendFl', 'typ' => 's', 'def' => 'n'], // SMS 발송상태
            ['val' => 'name', 'typ' => 's', 'def' => null], //신청자
            ['val' => 'cellPhone', 'typ' => 's', 'def' => null], //휴대폰번호
            ['val' => 'memNo', 'typ' => 'i', 'def' => null], //회원번호
            ['val' => 'diffKey', 'typ' => 's', 'def' => null], //상품번호,옵션명,옵션값을 MD5한 값
            ['val' => 'modDt', 'typ' => 's', 'def' => null], // 수정일
            ['val' => 'regDt', 'typ' => 's', 'def' => null], // 등록일
        ];

        return $arrField;
    }

    /**
     * [기획전 그룹형] displayEventGroupTheme 필드 기본값
     *
     * @author artherot
     * @return array displayEventGroupTheme 테이블 필드 정보
     */
    public static function tableDisplayEventGroupTheme()
    {
        // @formatter:off
        $arrField = [
            ['val' => 'groupName', 'typ' => 's', 'def' => ''],   //그룹명
            ['val' => 'groupSort', 'typ' => 's', 'def' => null],   //정렬방법
            ['val' => 'groupThemeCd', 'typ' => 's', 'def' => null],   //테마코드
            ['val' => 'groupMobileThemeCd', 'typ' => 's', 'def' => null],   //모바일 테마코드
            ['val' => 'groupManagerNo', 'typ' => 'i', 'def' => null],   //등록자
            ['val' => 'groupGoodsNo', 'typ' => 's', 'def' => ''],   //상품코드
            ['val' => 'groupMoreTopFl', 'typ' => 's', 'def' => 'y'],   //상단 더보기 노출
            ['val' => 'groupMoreBottomFl', 'typ' => 's', 'def' => 'y'],   //하단 더보기 노출
            ['val' => 'groupNameImagePc', 'typ' => 's', 'def' => ''],   //PC 이미지
            ['val' => 'groupNameImageMobile', 'typ' => 's', 'def' => ''],   //Mobile 이미지
            ['val' => 'groupThemeSno', 'typ' => 'i', 'def' => 0],   //display 테마 sno
            ['val' => 'groupThemeSort', 'typ' => 'i', 'def' => 0],   //기획전내순서
            ['val' => 'modDt', 'typ' => 's', 'def' => null], // 수정일
            ['val' => 'regDt', 'typ' => 's', 'def' => null], // 등록일
        ];

        // @formatter:on
        return $arrField;
    }

    /**
     * [기획전 그룹형] displayEventGroupThemeTmp 필드 기본값
     *
     * @author artherot
     * @return array displayEventGroupThemeTmp 테이블 필드 정보
     */
    public static function tableDisplayEventGroupThemeTmp()
    {
        // @formatter:off
        $arrField = [
            ['val' => 'groupName', 'typ' => 's', 'def' => ''],   //그룹명
            ['val' => 'groupSort', 'typ' => 's', 'def' => null],   //정렬방법
            ['val' => 'groupThemeCd', 'typ' => 's', 'def' => null],   //테마코드
            ['val' => 'groupMobileThemeCd', 'typ' => 's', 'def' => null],   //모바일 테마코드
            ['val' => 'groupManagerNo', 'typ' => 'i', 'def' => null],   //등록자
            ['val' => 'groupGoodsNo', 'typ' => 's', 'def' => ''],   //상품코드
            ['val' => 'groupMoreTopFl', 'typ' => 's', 'def' => 'y'],   //상단 더보기 노출
            ['val' => 'groupMoreBottomFl', 'typ' => 's', 'def' => 'y'],   //하단 더보기 노출
            ['val' => 'groupNameImagePc', 'typ' => 's', 'def' => ''],   //PC 이미지
            ['val' => 'groupNameImageMobile', 'typ' => 's', 'def' => ''],   //Mobile 이미지
            ['val' => 'groupThemeSno', 'typ' => 'i', 'def' => 0],   //display 테마 sno
            ['val' => 'groupThemeSort', 'typ' => 'i', 'def' => 0],   //기획전내순서
            ['val' => 'modDt', 'typ' => 's', 'def' => null], // 수정일
            ['val' => 'regDt', 'typ' => 's', 'def' => null], // 등록일
        ];

        // @formatter:on
        return $arrField;
    }

    /**
     * [회원 정보 수정 이벤트] memberModifyEvent 필드 기본값
     *
     * @author haky
     * @return array memberModifyEvent 테이블 필드 정보
     */
    public static function tableMemberModifyEvent()
    {
        // @formatter:off
        $arrField = [
            ['val' => 'sno', 'typ' => 'i', 'def' => ''], // 일련번호
            ['val' => 'mallSno', 'typ' => 'i', 'def' => 1], // 상점번호
            ['val' => 'eventType', 'typ' => 's', 'def' => 'modify'], // 이벤트 유형 (회원정보 수정,평생회원)
            ['val' => 'eventNm', 'typ' => 's', 'def' => ''], // 이벤트명
            ['val' => 'eventDescription', 'typ' => 's', 'def' => null], // 이벤트 설명
            ['val' => 'eventStatusFl', 'typ' => 's', 'def' => 'y'], // 이벤트 진행상태
            ['val' => 'eventStartDt', 'typ' => 's', 'def' => ''], // 이벤트 시작일
            ['val' => 'eventEndDt', 'typ' => 's', 'def' => ''], // 이벤트 종료일
            ['val' => 'eventApplyField', 'typ' => 's', 'def' => ''], // 이벤트 항목
            ['val' => 'exceptJoinType', 'typ' => 's', 'def' => 'unlimit'], // 이벤트 제외 가입기간 설정
            ['val' => 'exceptJoinStartDt', 'typ' => 's', 'def' => ''], // 이벤트 제외 가입기간 시작일
            ['val' => 'exceptJoinEndDt', 'typ' => 's', 'def' => ''], // 이벤트 제외 가입기간 종료일
            ['val' => 'exceptJoinDay', 'typ' => 'i', 'def' => 0], // 이벤트 제외 가입기간 지정일
            ['val' => 'benefitCondition', 'typ' => 's', 'def' => 'some'], // 혜택 지급 조건
            ['val' => 'adminModifyFl', 'typ' => 's', 'def' => 'n'], // 관리자 수정 적용여부
            ['val' => 'benefitProvideType', 'typ' => 's', 'def' => 'auto'], // 혜택 지급 방법
            ['val' => 'benefitType', 'typ' => 's', 'def' => 'mileage'], // 지급혜택
            ['val' => 'benefitMileage', 'typ' => 'i', 'def' => 0.00], // 지급혜택 마일리지
            ['val' => 'benefitCouponSno', 'typ' => 'i', 'def' => 0], // 지급혜택 쿠폰번호
            ['val' => 'managerNo', 'typ' => 'i', 'def' => ''], // 등록 관리자번호
            ['val' => 'loginDisplayFl', 'typ' => 's', 'def' => 'y'], // 노출페이지 (로그인 완료 즉시)
            ['val' => 'mainDisplayFl', 'typ' => 's', 'def' => 'n'], // 노출페이지 (메인)
            ['val' => 'mypageDisplayFl', 'typ' => 's', 'def' => 'n'], // 노출페이지 (마이페이지)
            ['val' => 'popupPositionT', 'typ' => 'i', 'def' => 0], // 팝업 상단 위치
            ['val' => 'popupPositionL', 'typ' => 'i', 'def' => 0], // 팝업 왼쪽 위치
            ['val' => 'todayUnSeeFl', 'typ' => 's', 'def' => 'n'], // 오늘 하루 보이지 않음 여부
            ['val' => 'popupContentType', 'typ' => 's', 'def' => 'default'], // 팝업 유형 (기본팝업, html직접 편집)
            ['val' => 'popupContent', 'typ' => 's', 'def' => ''], // 팝업 내용
            ['val' => 'regDt', 'typ' => 's', 'def' => ''], // 등록일
            ['val' => 'modDt', 'typ' => 's', 'def' => ''] // 수정일
        ];
        // @formatter:on

        return $arrField;
    }

    /**
     * [회원 정보 수정 이벤트] memberModifyEventResult 필드 기본값
     *
     * @author haky
     * @return array memberModifyEventResult 테이블 필드 정보
     */
    public static function tableMemberModifyEventResult()
    {
        // @formatter:off
        $arrField = [
            ['val' => 'sno', 'typ' => 'i', 'def' => ''], // 일련번호
            ['val' => 'eventNo', 'typ' => 'i', 'def' => ''], // 회원정보 수정 이벤트번호
            ['val' => 'eventType', 'typ' => 's', 'def' => 'modify'], // 이벤트 유형 (회원정보 수정,평생회원)
            ['val' => 'memNo', 'typ' => 'i', 'def' => ''], // 회원번호
            ['val' => 'memNm', 'typ' => 's', 'def' => ''], // 회원이름
            ['val' => 'groupNm', 'typ' => 's', 'def' => ''], // 회원등급명
            ['val' => 'modifiedField', 'typ' => 's', 'def' => ''], // 수정된 항목
            ['val' => 'provideBenefitType', 'typ' => 's', 'def' => 'mileage'], // 지급된 혜택
            ['val' => 'provideBenefitMileage', 'typ' => 'i', 'def' => 0.00], // 지급된 마일리지
            ['val' => 'provideBenefitCouponSno', 'typ' => 'i', 'def' => 0], // 지급된 쿠폰
            ['val' => 'provideMemberCouponSno', 'typ' => 'i', 'def' => 0], // 지급된 회원 쿠폰번호
            ['val' => 'regDt', 'typ' => 's', 'def' => ''], // 등록일
            ['val' => 'modDt', 'typ' => 's', 'def' => ''] // 수정일
        ];
        // @formatter:on

        return $arrField;
    }

    /**
     * [운영자 주문리스트 노출조건 설정] es_managerOrderGridConfig 필드 기본값
     *
     * @author artherot
     * @return array es_managerOrderGridConfig 테이블 필드 정보
     */
    public static function tableManagerOrderGridConfig()
    {
        // @formatter:off
        $arrField = [
            ['val' => 'ogManagerSno', 'typ' => 'i', 'def' => 0], //운영자 일련번호
            ['val' => 'ogApplyMode', 'typ' => 's', 'def' => ''], //적용모드
            ['val' => 'ogData', 'typ' => 's', 'def' => ''], //내용
            ['val' => 'regDt', 'typ' => 's', 'def' => ''], //등록일
            ['val' => 'modDt', 'typ' => 's', 'def' => ''], //수정일
        ];

        // @formatter:on
        return $arrField;
    }


    /**
     * [운영자 상품리스트 노출조건 설정] es_managerGoodsGridConfig 필드 기본값
     *
     * @author artherot
     * @return array es_managerGoodsGridConfig 테이블 필드 정보
     */
    public static function tableManagerGoodsGridConfig()
    {
        // @formatter:off
        $arrField = [
            ['val' => 'ggManagerSno', 'typ' => 'i', 'def' => 0], //운영자 일련번호
            ['val' => 'ggApplyMode', 'typ' => 's', 'def' => ''], //적용모드
            ['val' => 'ggData', 'typ' => 's', 'def' => ''], //내용
            ['val' => 'regDt', 'typ' => 's', 'def' => ''], //등록일
            ['val' => 'modDt', 'typ' => 's', 'def' => ''], //수정일
        ];

        // @formatter:on
        return $arrField;
    }

    /**
     * [상품 혜택 설정] es_goodsBenefit 필드 기본값
     *
     * @author cjb3333
     * @return array 테이블 필드 정보
     */
    public static function tableGoodsBenefit()
    {
        // @formatter:off
        $arrField = [
            ['val' => 'sno', 'typ' => 'i', 'def' => 0], // 혜택고유번호
            ['val' => 'benefitNm', 'typ' => 's', 'def' => ''], // 혜택명
            ['val' => 'benefitUseType', 'typ' => 's', 'def' => 'nonLimit'], // 혜택 진행유형
            ['val' => 'newGoodsRegFl', 'typ' => 's', 'def' => 'regDt'], // 신상품 등록/수정 여부
            ['val' => 'newGoodsDate', 'typ' => 'i', 'def' => null], // 	신상품 혜택할인 기간
            ['val' => 'newGoodsDateFl', 'typ' => 's', 'def' => 'day'], // 신상품 혜택 시간 날짜 여부
            ['val' => 'periodDiscountStart', 'typ' => 's', 'def' => null], // 특정기간 할인시작
            ['val' => 'periodDiscountEnd', 'typ' => 's', 'def' => null], // 특정기간 할인종료
            ['val' => 'goodsDiscountFl', 'typ' => 's', 'def' => 'n'], // 상품 할인 설정
            ['val' => 'goodsDiscount', 'typ' => 's', 'def' => '0'], // 상품 할인가
            ['val' => 'goodsDiscountUnit', 'typ' => 's', 'def' => 'percent'], // 상품할인 단위
            ['val' => 'fixedGoodsDiscount', 'typ' => 's', 'def' => null], // 상품할인금액기준
            ['val' => 'goodsDiscountGroup', 'typ' => 's', 'def' => 'all'], // 상품할인대상
            ['val' => 'goodsDiscountGroupMemberInfo', 'typ' => 's', 'def' => '{}'], // 상품할인 회원 정보
            ['val' => 'exceptBenefit', 'typ' => 's', 'def' => null], // 상품 할인/적립 제외 혜택
            ['val' => 'exceptBenefitGroup', 'typ' => 's', 'def' => null], // 상품 할인/적립 제외 대상
            ['val' => 'exceptBenefitGroupInfo', 'typ' => 's', 'def' => null], // 상품 할인/적립 제외 그룹
            ['val' => 'benefitScheduleFl', 'typ' => 's', 'def' => null], // 다음 혜택 예약설정 여부
            ['val' => 'benefitSchedulePrevSno', 'typ' => 'i', 'def' => 0], // 이전 혜택 예약 설정 고유번호
            ['val' => 'benefitScheduleNextSno', 'typ' => 'i', 'def' => 0], // 다음 혜택 예약 설정 고유번호
            //['val' => 'goodsIconCd', 'typ' => 's', 'def' => null], // 상품 아이콘 (무제한용)
        ];

        // @formatter:on

        return $arrField;
    }

    /**
     * [상품 혜택 링크 설정] es_goodsLinkBenefit 필드 기본값
     *
     * @author cjb3333
     * @return array 테이블 필드 정보
     */
    public static function tableGoodsLinkBenefit()
    {
        // @formatter:off
        $arrField = [
            ['val' => 'goodsNo', 'typ' => 'i', 'def' => 0], // 혜택고유번호
            ['val' => 'benefitSno', 'typ' => 's', 'def' => ''], // 혜택명
            ['val' => 'benefitUseType', 'typ' => 's', 'def' => 'nonLimit'], // 혜택 진행유형
            ['val' => 'linkPeriodStart', 'typ' => 's', 'def' => null], // 특정기간 할인시작
            ['val' => 'linkPeriodEnd', 'typ' => 's', 'def' => null], // 특정기간 할인종료
            //['val' => 'goodsIconCd', 'typ' => 's', 'def' => null], // 상품 아이콘 (무제한용)

        ];

        // @formatter:on

        return $arrField;
    }

    /**
     * [보안서버 설정] es_sslConfig 필드 기본값
     *
     * @author surlira
     * @return array 테이블 필드 정보
     */
    public static function tableSslConfig()
    {
        // @formatter:off
        $arrField = [
            ['val' => 'sslConfigNo', 'typ' => 'i', 'def' => 0], // 보안서버 고유번호
            ['val' => 'sslConfigDomain', 'typ' => 's', 'def' => ''], // 보안서버 도메인
            ['val' => 'sslConfigMainDomain', 'typ' => 's', 'def' => 'n'], // 보안서버 대표 / 추가 도메인
            ['val' => 'sslConfigMallFl', 'typ' => 's', 'def' => 'kr'], // 보안서버 해외상점
            ['val' => 'sslConfigUse', 'typ' => 's', 'def' => 'n'], // 보안서버 사용여부
            ['val' => 'sslConfigStatus', 'typ' => 's', 'def' => ''], // 보안서버 상태
            ['val' => 'sslConfigType', 'typ' => 's', 'def' => 'godo'], // 보안서버 무료/유료 종류
            ['val' => 'sslConfigPosition', 'typ' => 's', 'def' => 'pc'], // 보안서버 pc/mobile/admin/api 종류
            ['val' => 'sslConfigPort', 'typ' => 's', 'def' => '443'], // 보안서버 port
            ['val' => 'sslConfigStartDate', 'typ' => 's', 'def' => ''], // 보안서버 시작일
            ['val' => 'sslConfigEndDate', 'typ' => 's', 'def' => ''], // 보안서버 종료일
            ['val' => 'sslConfigApplyLimit', 'typ' => 's', 'def' => 'n'], // 보안서버 전체적용'y'/일부적용'n'
            ['val' => 'sslConfigUserRule', 'typ' => 's', 'def' => ''], // 보안서버 고객적용 페이지
            ['val' => 'sslConfigServerExists', 'typ' => 's', 'def' => ''], // 보안서버 종류
            ['val' => 'sslConfigImageUse', 'typ' => 's', 'def' => 'n'], // 보안서버 이미지 노출여부
            ['val' => 'sslConfigImageType', 'typ' => 's', 'def' => ''], // 보안서버 이미지 종류
        ];
        // @formatter:on
        return $arrField;
    }

    /**
     * 필드 정보를 select 문에 맞게 출력
     *
     * @author artherot
     *
     * @param string $funcName   테이블 함수 명
     * @param array  $arrInclude 기본값 배열에서 사용할 필드명
     * @param array  $arrExclude 기본값 배열에서 제외할 필드명
     * @param string $prefix     테이블 명 또는 테이블 이니셜
     *
     * @return array select 문에 사용하는 테이블 필드 정보
     */
    public static function setTableField($funcName, $arrInclude = null, $arrExclude = null, $prefix = null)
    {
        $setField = [];
        if (!is_null($arrInclude) && !is_array($arrInclude)) {
            $arrInclude = [$arrInclude];
        }
        if (!is_null($arrExclude) && !is_array($arrExclude)) {
            $arrExclude = [$arrExclude];
        }
        foreach (static::$funcName() as $key => $val) {
            // 제외할 필드가 있는 경우 continue (빈배열도 제외)
            if (!empty($arrExclude) && is_array($arrExclude) && in_array($val['val'], $arrExclude)) {
                continue;
            }
            // 사용할 필드가 있는 경우 그외는 continue (빈배열도 제외)
            if (!empty($arrInclude) && is_array($arrInclude) && !in_array($val['val'], $arrInclude)) {
                continue;
            }
            $setField[] = ($prefix == null ? '' : $prefix . '.') . $val['val'];
        }

        return $setField;
    }

    /**
     * 필드 이름 정보
     *
     * @author sunny
     *
     * @param string $funcName 테이블 함수 명
     *
     * @return array
     */
    public static function getFieldNames($funcName)
    {
        $field = [];
        foreach (static::$funcName() as $key => $val) {
            $field[$val['val']] = $val['name'];
        }

        return $field;
    }

    /**
     * 테이블 바인드 정보
     *
     * @author sunny
     *
     * @param string $funcName   테이블 함수 명
     * @param array  $arrInclude 사용할 필드명
     *
     * @return array
     */
    public static function getBindField($funcName, $arrInclude = null)
    {
        $field = [];
        foreach (static::$funcName() as $key => $val) {
            // 사용할 필드가 있는 경우 그외는 continue
            if (is_array($arrInclude) && !in_array($val['val'], $arrInclude)) {
                continue;
            }
            $field[] = $val;
        }

        return $field;
    }

    /**
     * 테이블 정보의 기본값
     *
     * @author artherot
     *
     * @param string $funcName 테이블 함수 명
     *
     * @return array
     */
    public static function setDefaultData($funcName, &$data)
    {
        $args = func_get_args();

        // 기본값 설정
        if (isset($args[2]) === true) {
            $tmpField = static::$funcName($args[2]);
        } else {
            $tmpField = static::$funcName();
        }


        foreach ($tmpField as $key => $val) {
            if (gd_isset($data[$val['val']], $val['def']) === null || (is_null($data) || (is_string($data) && $data == ''))) {
                if ($val['typ'] == 'i') {
                    $data[$val['val']] = (int) $val['def'];
                } else {
                    $data[$val['val']] = $val['def'];
                }
            }
        }
    }

    /**
     * 테이블 컬럼을 배열로 반환
     *
     * @author yjwee
     *
     * @param string $functionName 테이블 함수 명
     *
     * @return array 컬럼명 => '' 배열
     */
    public static function tableModel($functionName)
    {
        return array_fill_keys(array_keys(static::getFieldTypes($functionName)), '');
    }

    /**
     * 필드 타입 정보
     *
     * @author sunny
     *
     * @param string $funcName 테이블 함수 명
     *
     * @return array
     */
    public static function getFieldTypes($funcName)
    {
        $field = [];
        foreach (static::$funcName() as $key => $val) {
            $field[$val['val']] = $val['typ'];
        }

        return $field;
    }

    /**
     * getFuncName 테이블 필드 기본값 함수명 반환
     *
     * @static
     *
     * @param string $name   테이블명
     * @param string $prefix 테이블명 접두사
     *
     * @return string 테이블 필드 기본값 함수명
     */
    public static function getFuncName($name, $prefix = 'es_')
    {
        return 'table' . ucfirst(str_replace($prefix, '', $name));
    }

    /**
     * 원하는 필드 가져오기
     *
     * @author sunny
     *
     * @param string $funcName 테이블 함수 명
     *
     * @return array
     */
    public static function getFieldSelect($funcName, $fieldName)
    {
        $field = [];
        foreach (static::$funcName() as $key => $val) {
            if ($val[$fieldName]) $field[$val['val']] = $val[$fieldName];
        }

        return $field;
    }

    /**
     * [상품 아이콘] es_goodsIcon 필드 기본값
     *
     * @author agni
     * @return array 테이블 필드 정보
     */
    public static function tableGoodsIcon()
    {
        // @formatter:off
        $arrField = [
            ['val' => 'goodsNo', 'typ' => 's', 'def' => null], // 상품번호
            ['val' => 'goodsIconCd', 'typ' => 's', 'def' => null, 'name' => '상품 아이콘'], // 상품 아이콘
            ['val' => 'iconKind', 'typ' => 's', 'def' => 'un'], // 아이콘 종류 (un : 무제한 pe : 기간제한 pr : 상품혜택)
            ['val' => 'goodsIconStartYmd', 'typ' => 's', 'def' => null, 'name' => '상품 아이콘 설정기간 - 시작'], // 상품 아이콘 설정기간 - 시작
            ['val' => 'goodsIconEndYmd', 'typ' => 's', 'def' => null, 'name' => '상품 아이콘 설정기간 - 종료'], // 상품 아이콘 설정기간 - 종료
            ['val' => 'benefitSno', 'typ' => 'i', 'def' => 0], // 상품헤택 일렬번호
        ];
        // @formatter:on

        return $arrField;
    }

    /**
     * unstoring 필드 기본값
     *
     * @author artherot
     * @return array scm_manage 테이블 필드 정보
     */
    public static function tableUnstoringInfo()
    {
        // @formatter:off
        $arrField = [
            ['val' => 'sno', 'typ' => 'i', 'def' => '', 'name' => '주소 고유번호'], // 주소 고유 번호
            ['val' => 'unstoringNm', 'typ' => 's', 'def' => null, 'name' => '출고지 이름'], // 기본 출고지 상세주소
            ['val' => 'unstoringZipcode', 'typ' => 's', 'def' => null, 'name' => '기본 출고지 우편번호'], // 기본 출고지 우편번호
            ['val' => 'unstoringZonecode', 'typ' => 's', 'def' => null, 'name' => '기본 출고지 우편번호(5자리)'], // 기본 출고지 우편번호(5자리)
            ['val' => 'postFl', 'typ' => 's', 'def' => 'n', 'name' => '우편번호 사용 유무'], // 우편번호 사용 유무
            ['val' => 'unstoringAddress', 'typ' => 's', 'def' => null, 'name' => '기본 출고지 주소'], // 기본 출고지 주소
            ['val' => 'unstoringAddressSub', 'typ' => 's', 'def' => null, 'name' => '기본 출고지 상세주소'], // 기본 출고지 상세주소
            ['val' => 'mainContact', 'typ' => 's', 'def' => null, 'name' => '기본 연락처'], // 기본 출고지 상세주소
            ['val' => 'additionalContact', 'typ' => 's', 'def' => null, 'name' => '추가 연락처'], // 기본 출고지 상세주소
            ['val' => 'mainFl', 'typ' => 's', 'def' => 'n', 'name' => '기본 주소 등록 유무'], // 기본 출고지 등록 유무
            ['val' => 'addressFl', 'typ' => 's', 'def' => null, 'name' => '출고지 or 반품/교환지  선택'], // 출고지 or 반품/교환지  선택
            ['val' => 'mallFl', 'typ' => 's', 'def' => 'scm', 'name' => '기준몰, 해외몰 구분'], // 기준몰, 해외몰 구분
            ['val' => 'modDt', 'typ' => 's', 'def' => null, 'name' => '수정일'], // 수정일
            ['val' => 'regDt', 'typ' => 's', 'def' => null, 'name' => '등록일'], // 등록일
        ];
        // @formatter:on

        return $arrField;
    }

    /**
     * adminOrderGoodsMemo 필드 기본값
     *
     * @author artherot
     * @return array es_adminOrderGoodsMemo 테이블 필드 정보
     */
    public static function tableAdminOrderGoodsMemo()
    {
        // @formatter:off
        $arrField = [
            ['val' => 'sno', 'typ' => 'i', 'def' => '', 'name' => '고유코드'], // 고유 코드
            ['val' => 'managerSno', 'typ' => 'i', 'def' => null, 'name' => '작성자 번호'], // 작성자 번호
            ['val' => 'orderNo', 'typ' => 's', 'def' => null, 'name' => '주문 번호'], // 주문 번호
            ['val' => 'orderGoodsSno', 'typ' => 's', 'def' => null, 'name' => '주문상품 일련번호'], // 주문상품 일련번호
            ['val' => 'type', 'typ' => 's', 'def' => 'n', 'name' => '메모 유형'], // 메모 유형
            ['val' => 'memoCd', 'typ' => 's', 'def' => null, 'name' => '메모 구분 코드'], // 메모 구분 코드
            ['val' => 'content', 'typ' => 's', 'def' => null, 'name' => '메모 내용'], // 메모 내용
            ['val' => 'delFl', 'typ' => 's', 'def' => 'n', 'name' => '삭제플래그'], // 삭제 플래그
            ['val' => 'deleter', 'typ' => 's', 'def' => null, 'name' => '삭제자'], // 삭제자
            ['val' => 'regDt', 'typ' => 's', 'def' => null, 'name' => '등록일'], // 등록일
            ['val' => 'modDt', 'typ' => 's', 'def' => null, 'name' => '수정일'], // 수정일
        ];
        // @formatter:on

        return $arrField;
    }

    /**
     * es_barcode 필드 기본값
     *
     * @author parkjs
     * @return array es_barcode 테이블 필드 정보
     */
    public static function tableBarcode()
    {
        // @formatter:off
        $arrField = [
            ['val' => 'barcodeNo', 'typ' => 'i', 'def' => '', 'name' => '바코드 고유 번호'], // 고유 코드
            ['val' => 'couponNo', 'typ' => 'i', 'def' => '', 'name' => '쿠폰 고유 번호'], // 쿠폰 고유 번호
            ['val' => 'barcodeDesc', 'typ' => 's', 'def' => '', 'name' => '바코드 설명'], // 바코드 설명
            ['val' => 'barcodeCount', 'typ' => 'i', 'def' => 0, 'name' => '바코드 발급된 수량'], // 바코드 발급된 수량
            ['val' => 'barcodeUsedCount', 'typ' => 'i', 'def' => 0, 'name' => '바코드 사용된 수량'], // 바코드 사용된 수량
            ['val' => 'managerId', 'typ' => 's', 'def' => null, 'name' => '등록자'], // 등록자
            ['val' => 'regDt', 'typ' => 's', 'def' => '1000-01-01 00:00:00', 'name' => '등록일'], // 등록일
            ['val' => 'modDt', 'typ' => 's', 'def' => '1000-01-01 00:00:00', 'name' => '수정일'], // 최종 수정일
        ];
        // @formatter:on

        return $arrField;
    }

    /**
     * tableBarcodeCoupon 필드 기본값
     *
     * @author parkjs
     * @return array tableBarcodeCoupon 테이블 필드 정보
     */
    public static function tableBarcodeCoupon()
    {
        // @formatter:off
        $arrField = [
            ['val' => 'barcodeAuthKey', 'typ' => 's', 'def' => '', 'name' => '회원 쿠폰별 바코드 인증코드'], // 회원 쿠폰별 바코드 인증코드(PK)
            ['val' => 'barcodeGroupNo', 'typ' => 'i', 'def' => 0, 'name' => '발급된 바코드 고유 번호'], // es_barcode.barcodeNo
            ['val' => 'memCouponNo', 'typ' => 'i', 'def' => 0, 'name' => '발급된 회원별 쿠폰 번호'], // es_memberCoupon.memCouponNo
            ['val' => 'couponNo', 'typ' => 'i', 'def' => 0, 'name' => '발급된 쿠폰 번호'], //es_coupon.couponNo
            ['val' => 'memNo', 'typ' => 'i', 'def' => 0, 'name' => '발급된 회원 번호'], // es_member.memNo
            ['val' => 'usedOfflineFl', 'typ' => 's', 'def' => 'n', 'name' => '바코드 오프라인 사용여부'], // 바코드 오프라인 사용여부
            ['val' => 'managerId', 'typ' => 's', 'def' => null, 'name' => '등록자'], // 등록자
            ['val' => 'regDt', 'typ' => 's', 'def' => '1000-01-01 00:00:00', 'name' => '등록일'], // 등록일
            ['val' => 'modDt', 'typ' => 's', 'def' => '1000-01-01 00:00:00', 'name' => '수정일'], // 최종 수정일
        ];
        // @formatter:on

        return $arrField;
    }

    /**
     * es_logBarcode 필드 기본값
     *
     * @author parkjs
     * @return array es_logBarcode 테이블 필드 정보
     */
    public static function tableLogBarcode()
    {
        // @formatter:off
        $arrField = [
            ['val' => 'mode', 'typ' => 's', 'def' => null], // 매핑 모드
            ['val' => 'barcodeNo', 'typ' => 'i', 'def' => 0], // 바코드 번호
            ['val' => 'barcodeAuthKey', 'typ' => 's', 'def' => null], // 바코드 인증키
            ['val' => 'couponNo', 'typ' => 'i', 'def' => 0], // 쿠폰번호
            ['val' => 'memCouponNo', 'typ' => 'i', 'def' => 0], // 회원쿠폰번호
            ['val' => 'updateData', 'typ' => 's', 'def' => null], // 수정 데이터
            ['val' => 'managerId', 'typ' => 's', 'def' => null, 'name' => '등록자'], // 등록자
            ['val' => 'regDt', 'typ' => 's', 'def' => '1000-01-01 00:00:00', 'name' => '등록일'], // 등록일
            ['val' => 'modDt', 'typ' => 's', 'def' => '1000-01-01 00:00:00', 'name' => '수정일'], // 최종 수정일
        ];
        // @formatter:on

        return $arrField;
    }

    /**
     * 대한통운 주문리스트 정보
     *
     * @static
     * @return array
     */
    public static function tableLogisticsOrder()
    {
        // @formatter:off
        $arrField = [
            ['val' => 'custId', 'typ' => 's', 'def' => '', 'name' => '고개사용번호'], // 고객사용번호
            ['val' => 'custUseNo', 'typ' => 's', 'def' => '', 'name' => '주문번호'], // 주문번호
            ['val' => 'reqDvCd', 'typ' => 's', 'def' => '', 'name' => '주문번호'], // 예약 상태코드 00:준비,01:예약,:02취소
            ['val' => 'current', 'typ' => 's', 'def' => '', 'name' => '현재단계'], // 예약 상태코드 00:준비,01:예약,:02취소
            ['val' => 'orderNo', 'typ' => 's', 'def' => '', 'name' => '주문번호'], // 주문번호
            ['val' => 'orderGoodsNo', 'typ' => 'i', 'def' => null, 'name' => '상품주문번호'], // 상품주문번호
            ['val' => 'mpckKey', 'typ' => 's', 'def' => null, 'name' => '합포장키'], // 합포장키
            ['val' => 'mpckSeq', 'typ' => 's', 'def' => null, 'name' => '합포장순번'], // 합포장순번
            ['val' => 'reqDvCd', 'typ' => 's', 'def' => null, 'name' => '예약 상태코드'], // 예약 상태코드
            ['val' => 'crgSt', 'typ' => 's', 'def' => null, 'name' => '화물상태'], // 화물상태
            ['val' => 'apiRequestData', 'typ' => 'j', 'def' => null, 'name' => '중계서버통신전문'], //최신 api요청 데이터
            ['val' => 'apiResponseData', 'typ' => 'j', 'def' => null, 'name' => '중계서버통신전문'], //최신 api응답 데이터
            ['val' => 'deliveryStatus', 'typ' => 's', 'def' => null, 'name' => '배송상태'],  //['none','wait','send','complete']
            ['val' => 'errorMsg', 'typ' => 's', 'def' => null, 'name' => '애러메시지'],
            ['val' => 'syncDate', 'typ' => 's', 'def' => null, 'name' => '중계서버통신시간'],
            ['val' => 'modDt', 'typ' => 's', 'def' => null, 'name' => '수정일'], // 수정일
            ['val' => 'regDt', 'typ' => 's', 'def' => null, 'name' => '등록일'], // 등록일
        ];
        // @formatter:on

        return $arrField;
    }

    /**
     * 상품옵션 등록 임시 테이블
     *
     * @static
     * @return array
     */
    public static function tableGoodsOptionTemp()
    {
        // @formatter:off
        $arrField = [
            ['val' => 'session', 'typ' => 's', 'def' => '', 'name' => '임시 세션 ID'], // 임시 세션 ID
            ['val' => 'optionValue1', 'typ' => 's', 'def' => '', 'name' => '옵션값1'], // 옵션값1
            ['val' => 'optionValue2', 'typ' => 's', 'def' => '', 'name' => '옵션값2'], // 옵션값2
            ['val' => 'optionValue3', 'typ' => 's', 'def' => '', 'name' => '옵션값3'], // 옵션값3
            ['val' => 'optionValue4', 'typ' => 's', 'def' => '', 'name' => '옵션값4'], // 옵션값4
            ['val' => 'optionValue5', 'typ' => 's', 'def' => null, 'name' => '옵션값5'], // 옵션값5
            ['val' => 'optionDisplayFl', 'typ' => 's', 'def' => null, 'name' => '옵션 노출 방식'], // 옵션값5
            ['val' => 'optionImagePreviewFl', 'typ' => 's', 'def' => null, 'name' => '옵션 이미지 농출 설정 미리 보기 사용'], // 옵션값5
            ['val' => 'optionImageDisplayFl', 'typ' => 's', 'def' => null, 'name' => '옵션 이미지 노출 설정 상세 이미지에 추가'], // 옵션값5
            ['val' => 'optionValueText', 'typ' => 's', 'def' => null, 'name' => '옵션 텍스트'], // 옵션값5
            ['val' => 'optionCostPrice', 'typ' => 's', 'def' => null, 'name' => '옵션매입가'], // 옵션매입가
            ['val' => 'optionPrice', 'typ' => 's', 'def' => null, 'name' => '옵션가'], // 옵션가
            ['val' => 'stockCnt', 'typ' => 's', 'def' => null, 'name' => '재고량'], // 재고량
            ['val' => 'sellStopFl', 'typ' => 's', 'def' => null, 'name' => '판매중지수량 사용여부'], // 판매중지수량 사용여부
            ['val' => 'sellStopStock', 'typ' => 's', 'def' => null, 'name' => '판매중지수량'], // 판매중지수량
            ['val' => 'confirmRequestFl', 'typ' => 's', 'def' => null, 'name' => '확인요청수량 사용여부'], //확인요청수량 사용여부
            ['val' => 'confirmRequestStock', 'typ' => 'i', 'def' => null, 'name' => '확인요청수량'], // 확인요청수량
            ['val' => 'optionViewFl', 'typ' => 's', 'def' => null, 'name' => '옵션 노출 상태'],  // 옵션 노출 상태
            ['val' => 'optionStockFl', 'typ' => 's', 'def' => null, 'name' => '옵션 품절 상태'], // 옵션 품절 상태
            ['val' => 'optionStockCode', 'typ' => 's', 'def' => null, 'name' => '옵션 품절 상태'], // 옵션 품절 상태 코드
            ['val' => 'optionDeliveryFl', 'typ' => 's', 'def' => null, 'name' => '옵션배송상태'], // 옵션 배송 상태
            ['val' => 'optionDeliveryCode', 'typ' => 's', 'def' => null, 'name' => '옵션배송상태'], // 옵션 배송 상태 코드
            ['val' => 'optionCode', 'typ' => 's', 'def' => null, 'name' => '수정일'], // 옵션 자체 코드
            ['val' => 'optionMemo', 'typ' => 's', 'def' => null, 'name' => '수정일'], // 옵션 메모
            ['val' => 'regDt', 'typ' => 's', 'def' => null, 'name' => '등록일'], // 등록일
        ];
        // @formatter:on

        return $arrField;
    }

    /**
     * 재고 알림 설정 예약 SMS 발송 테이블
     *
     * @static
     * @return array
     */
    public static function tableGoodsOptionStockAlarm()
    {
        // @formatter:off
        $arrField = [
            ['val' => 'sno', 'typ' => 's', 'def' => '', 'name' => '일련번호'], // 일련번호
            ['val' => 'alarmType', 'typ' => 's', 'def' => '', 'name' => '알람형식'], // 알람형식(재고부족, 확인요청)
            ['val' => 'goodsNm', 'typ' => 's', 'def' => '', 'name' => '상품명'], // 상품명
            ['val' => 'goodsCd', 'typ' => 's', 'def' => '', 'name' => '상품코드'], // 상품코드
            ['val' => 'optionNm', 'typ' => 's', 'def' => '', 'name' => '옵션이름'], // 옵션이름
            ['val' => 'stock', 'typ' => 's', 'def' => null, 'name' => '재고량'], // 재고량
            ['val' => 'platform', 'typ' => 's', 'def' => null, 'name' => '발송 방법'], // 발송 방법
            ['val' => 'sendTime', 'typ' => 's', 'def' => null, 'name' => '발송 시간	'], // 발송 시간
            ['val' => 'sent', 'typ' => 's', 'def' => 'n', 'name' => '발송여부'], // 발송여부
            ['val' => 'regDt', 'typ' => 's', 'def' => null, 'name' => '등록일	'], // 등록일
            ['val' => 'modDt', 'typ' => 's', 'def' => null, 'name' => '수정일'], // 수정일
        ];
        // @formatter:on

        return $arrField;
    }

    /**
     * 마이앱 설치 디바이스 정보
     *
     * @static
     * @return array
     */
    public static function tableAppDeviceInfo()
    {
        // @formatter:off
        $arrField = [
            ['val' => 'sno', 'typ' => 'i', 'def' => null], // 일련번호
            ['val' => 'memNo', 'typ' => 'i', 'def' => null], // 회원번호
            ['val' => 'loggedIn', 'typ' => 's', 'def' => 'n'], // 로그인 여부 (y,n)
            ['val' => 'uuid', 'typ' => 's', 'def' => null], // 디바이스 UUID
            ['val' => 'pushToken', 'typ' => 's', 'def' => null], // 푸시 토큰 ID
            ['val' => 'platform', 'typ' => 's', 'def' => 'android'], // 기기 플랫폼 (ios, android)
            ['val' => 'model', 'typ' => 's', 'def' => null], // 디바이스 모델명
            ['val' => 'osVersion', 'typ' => 's', 'def' => null], // OS 버전
            ['val' => 'appVersion', 'typ' => 's', 'def' => null], // App 버전
            ['val' => 'pushEnabled', 'typ' => 's', 'def' => '0'], // 알림 허용 여부
            ['val' => 'lastLoginMemNo', 'typ' => 'i', 'def' => null], // 최종 로그인 회원번호
            ['val' => 'regDt', 'typ' => 's', 'def' => null], // 등록일
            ['val' => 'modDt', 'typ' => 's', 'def' => null], // 수정일
        ];
        // @formatter:on

        return $arrField;
    }

    /**
     * 앱 설치 혜택 내역
     *
     * @static
     * @return array
     */
    public static function tableAppInstallBenefit()
    {
        // @formatter:off
        $arrField = [
            ['val' => 'sno', 'typ' => 'i', 'def' => null], // 일련번호
            ['val' => 'amount', 'typ' => 'i', 'def' => '0.00'], // 앱 설치 지급 마일리지 금액
            ['val' => 'couponNo', 'typ' => 'i', 'def' => '0'], // 앱 설치 지급 쿠폰번호
            ['val' => 'uuid', 'typ' => 's', 'def' => null], // 디바이스 UUID
            ['val' => 'memNo', 'typ' => 'i', 'def' => null], // 회원번호
            ['val' => 'memId', 'typ' => 's', 'def' => null], // 회원아이디
            ['val' => 'regDt', 'typ' => 's', 'def' => null], // 등록일
            ['val' => 'modDt', 'typ' => 's', 'def' => null], // 수정일
        ];
        // @formatter:on

        return $arrField;
    }

    /**
     * 앱 통계
     *
     * @static
     * @return array
     */
    public static function tableAppStatistics()
    {
        // @formatter:off
        $arrField = [
            ['val' => 'sno', 'typ' => 'i', 'def' => null], // 일련번호
            ['val' => 'date', 'typ' => 's', 'def' => null], // 통계 날짜
            ['val' => 'mileageAmountAndroid', 'typ' => 'i', 'def' => '0.00'], // Android 마일리지 지급 금액
            ['val' => 'mileageAmountIos', 'typ' => 'i', 'def' => '0.00'], // Ios 마일리지 지급 금액
            ['val' => 'couponCntAndroid', 'typ' => 'i', 'def' => 0], // Android 쿠폰 발급 수
            ['val' => 'couponCntIos', 'typ' => 'i', 'def' => 0], // Ios 쿠폰 발급 수
            ['val' => 'orderCntAndroid', 'typ' => 'i', 'def' => 0], // Android 주문 수
            ['val' => 'orderCntIos', 'typ' => 'i', 'def' => 0], // Ios 주문 수
            ['val' => 'orderAmountAndroid', 'typ' => 'i', 'def' => '0.00'], // Android 결제 금액
            ['val' => 'orderAmountIos', 'typ' => 'i', 'def' => '0.00'], // Ios 결제 금액
            ['val' => 'regDt', 'typ' => 's', 'def' => null], // 등록일
            ['val' => 'modDt', 'typ' => 's', 'def' => null], // 수정일
        ];
        // @formatter:on

        return $arrField;
    }

    /**
     * 앱 푸시 주문통계
     *
     * @static
     * @return array
     */
    public static function tableAppPushStatistics()
    {
        // @formatter:off
        $arrField = [
            ['val' => 'sno', 'typ' => 'i', 'def' => null], // 일련번호
            ['val' => 'pushCode', 'typ' => 's', 'def' => null], // 푸시코드
            ['val' => 'orderCntAndroid', 'typ' => 'i', 'def' => 0], // Android 주문 수
            ['val' => 'orderCntIos', 'typ' => 'i', 'def' => 0], // Ios 주문 수
            ['val' => 'orderAmountAndroid', 'typ' => 'i', 'def' => '0.00'], // Android 결제 금액
            ['val' => 'orderAmountIos', 'typ' => 'i', 'def' => '0.00'], // Ios 결제 금액
            ['val' => 'regDt', 'typ' => 's', 'def' => null], // 등록일
            ['val' => 'modDt', 'typ' => 's', 'def' => null], // 수정일
        ];
        // @formatter:on

        return $arrField;
    }
}
