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

namespace Bundle\Component\Excel;

use Component\Database\DBTableField;
use Component\Member\MemberDAO;
use Component\Sms\Code;
use Component\Sms\SmsAutoCode;
use Framework\Utility\ArrayUtils;
use Framework\Utility\ComponentUtils;
use Framework\Utility\ImageUtils;
use Framework\Utility\StringUtils;
use Globals;
use Logger;
use Request;
use Session;
use UserFilePath;
use Vendor\Spreadsheet\Excel\Reader as SpreadsheetExcelReader;

/**
 * Class ExcelDataConvert
 *
 * Excel 저장 및 다운 로드
 * 상품, 회원 Excel 업로드 및 다운로드 관련 Class
 *
 * @package Bundle\Component\Excel
 * @author  artherot
 */
class ExcelDataConvert
{
    /** @var  SpreadsheetExcelReader */
    protected $excelReader;
    /** @var \Framework\Database\DBTool $db */
    protected $db;
    protected $excelHeader;
    protected $excelFooter;
    protected $excelBody = [];
    protected $fields = [];
    protected $fieldTexts = [];
    protected $dbNames = [];
    protected $tableKeys = [];
    protected $gGlobal;
    private $arrWhere = [];

    public function __construct()
    {
        ini_set('memory_limit', '-1');
        set_time_limit(RUN_TIME_LIMIT);

        if (!\is_object($this->db)) {
            $this->db = \App::load('DB');
        }
        $this->initHeader();
        $this->initFooter();
        $this->gGlobal = Globals::get('gGlobal');

        //상품테이블 분리 관련
        $this->goodsDivisionFl = gd_policy('goods.config')['divisionFl'] == 'y' ? true : false;
    }


    /**
     * [상품] goods 필드 기본값
     *
     * @author artherot
     * @return array goods 테이블 필드 정보
     */
    public static function excelGoods()
    {
        // @formatter:off
        $arrField = [
            [
                'dbName'   => 'goods',
                'dbKey'    => 'goodsNo',
                'excelKey' => 'goods_no',
                'text'     => '상품번호',
                'sample'   => '',
                'comment'  => __('10자리 숫자 (등록시에는 자동 생성 되므로 등록시에는 넣지 마세요)'),
                'desc'     => __('숫자 10자리의 unique 코드, 등록시에는 자동 생성 되므로 등록시에는 넣지 마세요.'),
            ],
            [
                'dbName'   => 'goods',
                'dbKey'    => 'goodsNm',
                'excelKey' => 'goods_name',
                'text'     => __('상품명_기본'),
                'sample'   => __('상품명_기본'),
                'comment'  => '',
                'desc'     => __('250자 이내의 상품명, html 테그 사용 가능'),
                'tagFl'    => 'n',
            ],
            [
                'dbName'   => 'goods',
                'dbKey'    => 'commission',
                'excelKey' => 'commission',
                'text'     => __('수수료'),
                'sample'   => '',
                'comment'  => '',
                'desc'     => __('수수료를 입력합니다.'),
            ],
            [
                'dbName'   => 'goods',
                'dbKey'    => 'goodsCd',
                'excelKey' => 'goods_cd',
                'text'     => __('자체상품코드'),
                'sample'   => '',
                'comment'  => '',
                'desc'     => __('30자 이내, 영문 대소문자/숫자/특수문자를 이용하여 입력합니다. (단, 특수문자는 “_”(언더바) 외 입력 불가)'),
            ],
            [
                'dbName'   => 'goods',
                'dbKey'    => 'goodsNmMain',
                'excelKey' => 'name_main',
                'text'     => __('상품명_메인'),
                'sample'   => '',
                'comment'  => '',
                'desc'     => __('메인에 노출되는 250자 이내의 상품명, html 테그 사용 가능'),
                'tagFl'    => 'n',
            ],
            [
                'dbName'   => 'goods',
                'dbKey'    => 'goodsNmList',
                'excelKey' => 'name_list',
                'text'     => '상품명_리스트',
                'sample'   => '',
                'comment'  => '',
                'desc'     => __('각종 리스트에 노출되는 250자 이내의 상품명, html 테그 사용 가능'),
                'tagFl'    => 'n',
            ],
            [
                'dbName'   => 'goods',
                'dbKey'    => 'goodsNmDetail',
                'excelKey' => 'name_detail',
                'text'     => __('상품명_상세'),
                'sample'   => '',
                'comment'  => '',
                'desc'     => __('상품 상세 설명에 노출되는 250자 이내의 상품명, html 테그 사용 가능'),
                'tagFl'    => 'n',
            ],
            [
                'dbName'   => 'goods',
                'dbKey'    => 'goodsNmPartner',
                'excelKey' => 'name_partner',
                'text'     => __('상품명_제휴'),
                'sample'   => '',
                'comment'  => '',
                'desc'     => __('상품 제휴 설명에 노출되는 250자 이내의 상품명, html 테그 사용 가능'),
                'tagFl'    => 'n',
            ],
            [
                'dbName'   => 'goods',
                'dbKey'    => 'goodsSearchWord',
                'excelKey' => 'search_word',
                'text'     => __('검색 키워드'),
                'sample'   => '',
                'comment'  => __('다중구분 : ","(콤마)'),
                'desc'     => __('250자 이내의 검색어를 ","(콤마)로 구분해서 넣으세요'),
            ],
            [
                'dbName'   => 'goods',
                'dbKey'    => 'goodsOpenDt',
                'excelKey' => 'goods_open_dt',
                'text'     => __('상품 노출 시간'),
                'sample'   => '',
                'comment'  => __('입력 형식 "yyyy-mm-dd 00:00"'),
                'desc'     => __('"yyyy-mm-dd 00:00" 형태로 넣으세요'),
            ],
            [
                'dbName'   => 'goods',
                'dbKey'    => 'goodsState',
                'excelKey' => 'goods_state',
                'text'     => __('상품 상태'),
                'sample'   => '',
                'comment'  => __('n: 신상품 u:중고 r : 반품 f: 리퍼 d:전시 b : 스크래치'),
                'desc'     => __('n: 신상품 u:중고 r : 반품 f: 리퍼 d:전시 b : 스크래치'),
            ],
            [
                'dbName'   => 'goods',
                'dbKey'    => 'goodsColor',
                'excelKey' => 'goods_color',
                'text'     => __('상품 대표색상'),
                'sample'   => '',
                'comment'  => '대표색상 등록시 [기본설정>기본정책>코드관리]의 상품 대표색상의 16진수 색상값 입력  다중구분 : ^|^ <br/>예시) 8E562E^|^E91818',
                'desc'     => ' 대표색상 등록시 [기본설정>기본정책>코드관리] 메뉴의 상품 대표색상 16진수 색상값을 입력합니다. 다중구분 : ^|^ / 예시) 8E562E^|^E91818',
            ],
            [
                'dbName'   => 'goods',
                'dbKey'    => 'payLimitFl',
                'excelKey' => 'pay_limit_fl',
                'text'     => __('결제 수단 설정'),
                'sample'   => '',
                'comment'  => 'n: 통합설정 y : 개별설정',
                'desc'     => 'n: 통합설정 y : 개별설정',
            ],
            [
                'dbName'   => 'goods',
                'dbKey'    => 'payLimit',
                'excelKey' => 'pay_limit',
                'text'     => __('사용가능 결제수단'),
                'sample'   => '',
                'comment'  => '개별설정 시 사용가능한 결제수단 입력 (통합설정 시에는 넣지 마세요)<br/>무통장 사용:gb, PG결제 사용:pg, 마일리지 사용:gm, 예치금 사용:gd / 다중구분 : ^|^<br/>예시) gb^|^pg',
                'desc'     => '개별설정 시 사용가능한 결제수단 입력 (통합설정 시에는 넣지 마세요)<br/>무통장 사용:gb, PG결제 사용:pg, 마일리지 사용:gm, 예치금 사용:gd / 다중구분 : ^|^ 예시) gb^|^pg',
            ],
            [
                'dbName'   => 'goods',
                'dbKey'    => 'goodsModelNo',
                'excelKey' => 'model_no',
                'text'     => __('모델번호'),
                'sample'   => '',
                'comment'  => '',
                'desc'     => __('30자 이내, 영문 대소문자/숫자/특수문자를 이용하여 입력합니다. (단, 특수문자는 “_”(언더바) 외 입력 불가)'),
            ],
            [
                'dbName'   => 'link',
                'dbKey'    => 'cateCd',
                'excelKey' => 'category_code',
                'text'     => __('카테고리 코드'),
                'sample'   => '001',
                'comment'  => __('다중구분:Alt+Enter(개행)'),
                'desc'     => __('대표 카테고리 및 다중 카테고리 코드, 첫번째 코드가 대표 카테고리임, 다중인경우 &quot;Alt+Enter(개행)&quot;로 구분'),
            ],
            [
                'dbName'   => 'goods',
                'dbKey'    => 'purchaseGoodsNm',
                'excelKey' => 'purchase_goods_name',
                'text'     => __('매입처 상품명'),
                'desc'   => '입력시 별도의 매입처 상품명이 등록됩니다.',
            ],
            [
                'dbName'   => 'brand',
                'dbKey'    => 'brandCd',
                'excelKey' => 'brand_code',
                'text'     => __('브랜드 코드'),
                'sample'   => '001',
                'comment'  => '',
                'desc'     => __('브랜드명이 아닌 브랜드 코드를 넣으세요'),
            ],
            [
                'dbName'   => 'goods',
                'dbKey'    => 'makerNm',
                'excelKey' => 'maker_name',
                'text'     => __('제조사'),
                'sample'   => __('고도'),
                'comment'  => '',
                'desc'     => __('30자 이내의 제조사명을 넣으세요'),
            ],
            [
                'dbName'   => 'goods',
                'dbKey'    => 'originNm',
                'excelKey' => 'origin_name',
                'text'     => __('원산지'),
                'sample'   => __('한국'),
                'comment'  => '',
                'desc'     => __('30자 이내의 원산지명을 넣으세요'),
            ],
            [
                'dbName'   => 'goods',
                'dbKey'    => 'makeYmd',
                'excelKey' => 'make_date',
                'text'     => __('제조일'),
                'sample'   => '',
                'comment'  => __('입력 형식 "yyyy-mm-dd"'),
                'desc'     => __('필요시에만 입력하세요, 입력 형식은 "yyyy-mm-dd"'),
            ],
            [
                'dbName'   => 'goods',
                'dbKey'    => 'launchYmd',
                'excelKey' => 'launch_date',
                'text'     => __('출시일'),
                'sample'   => '',
                'comment'  => __('입력 형식 "yyyy-mm-dd"'),
                'desc'     => __('필요시에만 입력하세요, 입력 형식은 "yyyy-mm-dd"'),
            ],
            [
                'dbName'   => 'goods',
                'dbKey'    => 'effectiveStartYmd',
                'excelKey' => 'effective_start_ymd',
                'text'     => __('유효일자 시작일'),
                'sample'   => '',
                'comment'  => __('입력 형식 "yyyy-mm-dd"'),
                'desc'     => __('필요시에만 입력하세요, 입력 형식은 "yyyy-mm-dd"'),
            ],
            [
                'dbName'   => 'goods',
                'dbKey'    => 'effectiveEndYmd',
                'excelKey' => 'effective_end_ymd',
                'text'     => __('유효일자 종료일'),
                'sample'   => '',
                'comment'  => __('입력 형식 "yyyy-mm-dd"'),
                'desc'     => __('필요시에만 입력하세요, 입력 형식은 "yyyy-mm-dd"'),
            ],
            [
                'dbName'   => 'goods',
                'dbKey'    => 'goodsPermission',
                'excelKey' => 'goods_permission',
                'text'     => __('구매가능 회원등급 설정'),
                'sample'   => 'all',
                'comment'  => __('all:전체(회원+비회원) member:회원전용(비회원제외) group:특정회원등급'),
                'desc'     => __('all:전체(회원+비회원),member:회원전용(비회원제외),group:특정회원등급'),
            ],
            [
                'dbName'   => 'goods',
                'dbKey'    => 'goodsPermissionGroup',
                'excelKey' => 'goods_permission_group',
                'text'     => __('구매가능 회원등급'),
                'sample'   => '',
                'comment'  => sprintf('%s :  "'.INT_DIVISION.'"', __('구매가능 회원등급 설정시 회원등급 코드 입력 다중구분')),
                'desc'     => sprintf(__('구매가능 회원등급 설정시 회원등급 코드를 입력하세요.  구분은 &quot;%s&quot; 입니다.'), INT_DIVISION),
            ],
            [
                'dbName'   => 'goods',
                'dbKey'    => 'goodsPermissionPriceStringFl',
                'excelKey' => 'goods_permission_price_string_fl',
                'text'     => __('구매불가 고객 가격 대체문구 사용'),
                'sample'   => 'n',
                'comment'  => __('y:사용함 n:사용안함'),
                'desc'     => __('구매불가 고객 가격 대체문구사용이 필요한 경우 입력해주세요. y:사용함, n:사용안함, 기본은 n(사용안함)입니다.'),
            ],
            [
                'dbName'   => 'goods',
                'dbKey'    => 'goodsPermissionPriceString',
                'excelKey' => 'goods_permission_price_string',
                'text'     => __('구매불가 고객 가격 대체문구'),
                'sample'   => '',
                'comment'  => '',
                'desc'     => __('30자 이내의 구매불가 고객 가격 대체문구를 입력해주세요.'),
            ],
            [
                'dbName'   => 'goods',
                'dbKey'    => 'onlyAdultFl',
                'excelKey' => 'only_adult_fl',
                'text'     => __('성인인증 여부 '),
                'sample'   => 'n',
                'comment'  => __('y:사용함 n:사용안함'),
                'desc'     => __('성인인증이 필요한 경우 입력해주세요.'),
            ],
            [
                'dbName'   => 'goods',
                'dbKey'    => 'onlyAdultDisplayFl',
                'excelKey' => 'only_adult_display_fl',
                'text'     => __('미인증 고객 상품 노출함'),
                'sample'   => 'n',
                'comment'  => __('y:노출함 n:노출안함'),
                'desc'     => __('미인증 고객 상품 노출함이 필요한 경우 입력해주세요.'),
            ],
            [
                'dbName'   => 'goods',
                'dbKey'    => 'onlyAdultImageFl',
                'excelKey' => 'only_adult_image_fl',
                'text'     => __('미인증 고객 상품 이미지 노출함'),
                'sample'   => 'n',
                'comment'  => __('y:노출함 n:노출안함'),
                'desc'     => __('미인증 고객 상품 이미지 노출함이 필요한 경우 입력해주세요.'),
            ],
            [
                'dbName'   => 'goods',
                'dbKey'    => 'goodsAccess',
                'excelKey' => 'goods_access',
                'text'     => __('접근권한 회원등급 설정'),
                'sample'   => 'all',
                'comment'  => __('all:전체(회원+비회원) member:회원전용(비회원제외) group:특정회원등급'),
                'desc'     => __('all:전체(회원+비회원),member:회원전용(비회원제외),group:특정회원등급'),
            ],
            [
                'dbName'   => 'goods',
                'dbKey'    => 'goodsAccessGroup',
                'excelKey' => 'goods_access_group',
                'text'     => __('접근권한 회원등급'),
                'sample'   => '',
                'comment'  => sprintf('%s :  "'.INT_DIVISION.'"', __('구매가능 회원등급 설정시 회원등급 코드 입력 다중구분')),
                'desc'     => sprintf(__('구매가능 회원등급 설정시 회원등급 코드를 입력하세요.  구분은 &quot;%s&quot; 입니다.'), INT_DIVISION),
            ],
            [
                'dbName'   => 'goods',
                'dbKey'    => 'goodsAccessDisplayFl',
                'excelKey' => 'goods_access_display_fl',
                'text'     => __('접근불가 고객 상품 노출함'),
                'sample'   => 'n',
                'comment'  => __('y:노출함 n:노출안함'),
                'desc'     => __('접근불가 고객 상품 노출이 필요한 경우 입력해주세요.'),
            ],
            [
                'dbName'   => 'info',
                'dbKey'    => 'addInfo',
                'excelKey' => 'add_info',
                'text'     => '추가항목',
                'sample'   => '',
                'comment'  => sprintf(__('항목%s내용<br />다중구분:Alt+Enter(개행)'), STR_DIVISION),
                'desc'     => sprintf(__('필요시에만 입력하세요, 입력형식은 "항목%s내용", 다중구분:Alt+Enter(개행)'), STR_DIVISION),
            ],
            [
                'dbName'   => 'goods',
                'dbKey'    => 'goodsMustInfo',
                'excelKey' => 'goods_must_info',
                'text'     => '상품필수정보',
                'sample'   => '',
                'comment'  => sprintf(__('4칸인 경우 : 항목%1$s내용%1$s항목%1$s내용<br />2칸인 경우 : 항목%1$s내용<br />다중구분:Alt+Enter(개행)'), STR_DIVISION),
                'desc'     => sprintf(__('4칸인 경우 : 항목%1$s내용%1$s항목%1$s내용, 2칸인 경우 : 항목%1$s내용, 다중구분:Alt+Enter(개행)'), STR_DIVISION),
            ],
            [
                'dbName'   => 'goods',
                'dbKey'    => 'kcmarkFl',
                'excelKey' => 'kcmark_fl',
                'text'     => 'KC인증 표시 여부',
                'sample'   => 'n',
                'comment'  => __('y:사용함 n:사용안함'),
                'desc'     => __('y:사용함, n:사용안함, 기본은 n(사용안함)입니다.'),
            ],
            [
                'dbName'   => 'goods',
                'dbKey'    => 'kcmarkDivFl',
                'excelKey' => 'kcmark_div_fl',
                'text'     => 'KC인증 구분',
                'sample'   => '',
                'comment'  => __('KC인증 구분 코드 입력<br>상품 엑셀 업로드 페이지 내 항목 설명 중 KC인증 구분을 참고 하세요.'),
                'desc'     => __('전기용품 안전관리법의 인증 대상을 선택합니다. 상품 엑셀 업로드 시, 아래 코드에서 선택하여 업로드 합니다.<br>[어린이제품] 공급자적합성검사 : kcCd01 / [어린이제품] 안전인증 : kcCd02 / [어린이제품] 안전확인 : kcCd03 / [방송통신기자재] 잠정인증 : kcCd04 / [방송통신기자재] 적합등록 : kcCd05 / [방송통신기자재] 적합인증 : kcCd06 / [생활용품] 공급자적합성확인 : kcCd07 / [생활용품] 안전인증 : kcCd08 / [생활용품] 안전확인 : kcCd09 / [생활용품] 어린이보호포장 : kcCd10 / [전기용품] 공급자적합성확인 : kcCd11 / [전기용품] 안전인증 : kcCd12 / [전기용품] 안전확인 : kcCd13
'),
            ],
            [
                'dbName'   => 'goods',
                'dbKey'    => 'kcmarkNo',
                'excelKey' => 'kcmark_no',
                'text'     => 'KC인증 번호',
                'sample'   => '',
                'comment'  => __('인증번호 입력 시, - 포함하여 입력하세요.'),
                'desc'     => __('20자 이내, 영문 대소문자/숫자/특수문자를 이용하여 입력합니다. (단, 특수문자는 “-”(하이픈) 외 입력 불가)'),
            ],
            [
                'dbName'   => 'goods',
                'dbKey'    => 'kcmarkDt',
                'excelKey' => 'kcmark_dt',
                'text'     => 'KC인증일자',
                'sample'   => '',
                'comment'  => __('yyyy-mm-dd 로 입력'),
                'desc'     => __('"yyyy-mm-dd" 형태로 넣으세요'),
            ],
            [
                'dbName'   => 'goodsIcon',
                'dbKey'    => 'goodsIconStartYmd',
                'excelKey' => 'icon_start',
                'text'     => __('아이콘 기간(시작)'),
                'sample'   => '',
                'comment'  => __('기간 제한용 입력형식 "yyyy-mm-dd HH:ii:ss"'),
                'desc'     => __('기간 제한용 아이콘의 시작일자를 입력하세요, 입력형식는 "yyyy-mm-dd HH:ii:ss"'),
            ],
            [
                'dbName'   => 'goodsIcon',
                'dbKey'    => 'goodsIconEndYmd',
                'excelKey' => 'icon_end',
                'text'     => __('아이콘 기간(끝)'),
                'sample'   => '',
                'comment'  => __('기간 제한용 입력형식 "yyyy-mm-dd HH:ii:ss"'),
                'desc'     => __('기간 제한용 아이콘의 만료일자를 입력하세요, 입력형식는 "yyyy-mm-dd HH:ii:ss"'),
            ],
            [
                'dbName'   => 'goodsIcon',
                'dbKey'    => 'goodsIconCdPeriod',
                'excelKey' => 'icon_period',
                'text'     => __('아이콘 코드'),
                'sample'   => '',
                'comment'  => sprintf('%s :  "' . INT_DIVISION . '"', __('기간 제한용 다중구분')),
                'desc'     => sprintf(__('기간 제한용 아이콘 코드를 입력하세요. 구분은 &quot;%s&quot; 입니다.'), INT_DIVISION),
            ],
            [
                'dbName'   => 'goodsIcon',
                'dbKey'    => 'goodsIconCd',
                'excelKey' => 'icon_code',
                'text'     => __('아이콘 코드'),
                'sample'   => '',
                'comment'  => sprintf('%s : "'.INT_DIVISION.'"', __('무제한용 다중구분')),
                'desc'     => sprintf(__('무제한용 아이콘 코드를 입력하세요. 구분은 &quot;%s&quot; 입니다.'), INT_DIVISION),
            ],
            [
                'dbName'   => 'goods',
                'dbKey'    => 'goodsWeight',
                'excelKey' => 'weight',
                'text'     => __('무게'),
                'sample'   => '',
                'comment'  => '',
                'desc'     => __('상품의 무게를 입력하세요.'),
            ],
            [
                'dbName'   => 'goods',
                'dbKey'    => 'goodsVolume',
                'excelKey' => 'volume',
                'text'     => __('용량'),
                'sample'   => '',
                'comment'  => '',
                'desc'     => __('상품의 용량을 입력하세요.'),
            ],
            [
                'dbName'   => 'goods',
                'dbKey'    => 'stockFl',
                'excelKey' => 'stock_type',
                'text'     => __('재고 설정'),
                'sample'   => 'n',
                'comment'  => __('y:재고<br />n:무한'),
                'desc'     => __('y:재고, n:무한, 기본은 n(무한)입니다.'),
            ],
            [
                'dbName'   => 'goods',
                'dbKey'    => 'mileageFl',
                'excelKey' => 'mileage_type',
                'text'     => __('마일리지 정책'),
                'sample'   => 'c',
                'comment'  => __('g:개별<br />c:통합'),
                'desc'     => __('g:개별, c:통합, 기본은 c(통합)입니다.'),
            ],
            [
                'dbName'   => 'goods',
                'dbKey'    => 'mileageGroup',
                'excelKey' => 'mileage_group',
                'text'     => __('마일리지 지급대상'),
                'sample'   => 'all',
                'comment'  => __('all:전체 group:특정회원등급'),
                'desc'     => __('all:전체회원, group:특정회원등급, 기본은 all(전체회원)입니다.'),
            ],
            [
                'dbName'   => 'goods',
                'dbKey'    => 'mileageGroupInfo',
                'excelKey' => 'mileage_group_info',
                'text'     => __('마일리지 지급 회원등급'),
                'sample'   => '',
                'comment'  => __('마일리지 지급 회원등급 설정 시 회원등급 코드 입력 다중구분:"||"'),
                'desc'     => __('마일리지 지급 회원등급 설정 시 회원등급 코드를 입력하세요. 구분은 "||" 입니다.'),
            ],
            [
                'dbName'   => 'goods',
                'dbKey'    => 'mileageGoods',
                'excelKey' => 'mileage_goods',
                'text'     => __('상품 개별 마일리지'),
                'sample'   => '',
                'comment'  => __('다중구분:Alt+Enter(개행)'),
                'desc'     => __('개별설정-특정회원등급 설정 시 마일리지 금액 값을 입력하세요. 다중구분 : Alt+Enter (개행)'),
            ],
            [
                'dbName'   => 'goods',
                'dbKey'    => 'mileageGoodsUnit',
                'excelKey' => 'mileage_goods_unit',
                'text'     => __('상품 개별 마일리지 단위'),
                'sample'   => '',
                'comment'  => __('percent : %(퍼센트) mileage : 마일리지 설정 단위 문구'),
                'desc'     => __('개별설정-특정회원등급 설정 시 입력한 마일리지 금액의 단위입니다. 다중구분 : Alt+Enter(개행) <br /> percent : %(퍼센트) mileage : 마일리지 설정 단위 문구'),
            ],
            [
                'dbName'   => 'goods',
                'dbKey'    => 'goodsDiscountFl',
                'excelKey' => 'goods_discount_fl',
                'text'     => __('상품 할인설정 사용 여부'),
                'sample'   => 'n',
                'comment'  => __('y:사용함 n:사용안함'),
                'desc'     => __('y:사용함, n:사용안함'),
            ],
            [
                'dbName'   => 'goods',
                'dbKey'    => 'goodsDiscount',
                'excelKey' => 'goods_discount',
                'text'     => __('상품 할인가'),
                'sample'   => '',
                'comment'  => '',
                'desc'     => '',
            ],
            [
                'dbName'   => 'goods',
                'dbKey'    => 'goodsDiscountUnit',
                'excelKey' => 'goods_discount_unit',
                'text'     => __('상품 할인가 단위'),
                'sample'   => '',
                'comment'  => __('percent : %(퍼센트) price : 상품 할인가 설정 단위 문구'),
                'desc'     => __('percent : %(퍼센트) price : 상품 할인가 설정 단위 문구'),
            ],
            [
                'dbName'   => 'goods',
                'dbKey'    => 'fixedSales',
                'excelKey' => 'fixed_sales',
                'text'     => __('묶음주문기준'),
                'sample'   => '',
                'comment'  => __('option : 옵션기준<br />goods : 상품기준'),
                'desc'     => __('옵션기준 또는 상품기준으로 묶음주문단위를 설정할 수 있습니다. option : 옵션기준 goods : 상품기준, 기본은 option(옵션기준)입니다.'),
            ],
            [
                'dbName'   => 'goods',
                'dbKey'    => 'salesUnit',
                'excelKey' => 'sales_unit',
                'text'     => __('묶음주문단위'),
                'sample'   => '',
                'comment'  => '',
                'desc'     => __('설정된 개수 단위로 주문 되며, 장바구니에 담깁니다. '),
            ],
            [
                'dbName'   => 'goods',
                'dbKey'    => 'soldOutFl',
                'excelKey' => 'soldout_yn',
                'text'     => __('품절 설정'),
                'sample'   => 'n',
                'comment'  => __('y:품절<br />n:판매'),
                'desc'     => __('y:품절, n:정상, 기본은 n(정상)입니다.'),
            ],
            [
                'dbName'   => 'goods',
                'dbKey'    => 'taxFreeFl',
                'excelKey' => 'tax_free_type',
                'text'     => __('과세/면세'),
                'sample'   => 't',
                'comment'  => __('t:과세<br />f:면세'),
                'desc'     => __('t:과세, f:면세 기본은 t(과세)입니다.'),
            ],
            [
                'dbName'   => 'goods',
                'dbKey'    => 'taxPercent',
                'excelKey' => 'tax_percent',
                'text'     => __('과세율'),
                'sample'   => '10',
                'comment'  => __('단위:%'),
                'desc'     => __('과세인경우 과세율을 나타내며, 기본은 10 입니다.'),
            ],
            [
                'dbName'   => 'goods',
                'dbKey'    => 'goodsDisplayFl',
                'excelKey' => 'display_pc_yn',
                'text'     => __('PC 노출 여부'),
                'sample'   => 'y',
                'comment'  => __('y:노출함<br />n:노출안함'),
                'desc'     => __('PC 상품 노출여부입니다. y:노출함, n:노출안함, 기본은 y(노출함)입니다.'),
            ],
            [
                'dbName'   => 'goods',
                'dbKey'    => 'goodsDisplayMobileFl',
                'excelKey' => 'display_mobile_yn',
                'text'     => __('모바일 노출 여부'),
                'sample'   => 'y',
                'comment'  => __('y:노출함<br />n:노출안함'),
                'desc'     => __('모바일 상품 노출여부입니다. y:노출함, n:노출안함, 기본은 y(노출함)입니다.'),
            ],
            [
                'dbName'   => 'goods',
                'dbKey'    => 'goodsSellFl',
                'excelKey' => 'sell_pc_yn',
                'text'     => __('PC 판매 여부'),
                'sample'   => 'y',
                'comment'  => __('y:판매함<br />n:판매안함'),
                'desc'     => __('PC 상품의 판매여부를 나타냅니다. y:판매함, n:판매안함, 기본은 y(판매함)입니다.'),
            ],
            [
                'dbName'   => 'goods',
                'dbKey'    => 'goodsSellMobileFl',
                'excelKey' => 'sell_mobile_yn',
                'text'     => __('모바일 판매 여부'),
                'sample'   => 'y',
                'comment'  => __('y:판매함<br />n:판매안함'),
                'desc'     => __('모바일 상품의 판매여부를 나타냅니다. y:판매함, n:판매안함, 기본은 y(판매함)입니다.'),
            ],
            [
                'dbName'   => 'goods',
                'dbKey'    => 'deliverySno',
                'excelKey' => 'deliverySno',
                'text'     => __('배송비 고유번호'),
                'sample'   => '1',
                'comment'  => '',
                'desc'     => __('배송비 코드를 입력해주세요. 기본코드는 1 입니다.'),
            ],
            [
                'dbName'   => 'goods',
                'dbKey'    => 'goodsPriceString',
                'excelKey' => 'goods_price_string',
                'text'     => __('가격대체 문구'),
                'sample'   => '',
                'comment'  => '',
                'desc'     => __('가격을 대체할 문구 입니다. 해당 문구 작성시 해당상품은 구매가 되지 않습니다.'),
            ],
            [
                'dbName'   => 'goods',
                'dbKey'    => 'fixedOrderCnt',
                'excelKey' => 'fixed_cnt',
                'text'     => __('구매수량기준'),
                'sample'   => '',
                'comment'  => __('option : 옵션기준<br />goods : 상품기준<br />id : ID기준'),
                'desc'     => __('옵션기준, 상품기준, ID기준으로 최소/최대 구매수량을 설정할 수 있습니다. option : 옵션기준 goods : 상품기준 id : ID기준, 기본은 option(옵션기준)입니다.'),
            ],
            [
                'dbName'   => 'goods',
                'dbKey'    => 'minOrderCnt',
                'excelKey' => 'min_cnt',
                'text'     => __('최소 구매 수량'),
                'sample'   => '1',
                'comment'  => '',
                'desc'     => __('최소 구매수량입니다. 기본은 1입니다.'),
            ],
            [
                'dbName'   => 'goods',
                'dbKey'    => 'maxOrderCnt',
                'excelKey' => 'max_cnt',
                'text'     => __('최대 구매 수량'),
                'sample'   => '0',
                'comment'  => '',
                'desc'     => __('최대 구매수량입니다. 기본은 0이며, 0은 무한 구매 입니다.'),
            ],
            [
                'dbName'   => 'goods',
                'dbKey'    => 'salesStartYmd',
                'excelKey' => 'sales_start_ymd',
                'text'     => __('상품판매기간 시작일'),
                'sample'   => '',
                'comment'  => __('입력 형식 "yyyy-mm-dd"'),
                'desc'     => __('필요시에만 입력하세요, 입력 형식은 "yyyy-mm-dd 00:00"'),
            ],
            [
                'dbName'   => 'goods',
                'dbKey'    => 'salesEndYmd',
                'excelKey' => 'sales_end_ymd',
                'text'     => __('상품판매기간 종료일'),
                'sample'   => '',
                'comment'  => __('입력 형식 "yyyy-mm-dd"'),
                'desc'     => __('필요시에만 입력하세요, 입력 형식은 "yyyy-mm-dd 00:00"'),
            ],
            [
                'dbName'   => 'goods',
                'dbKey'    => 'cultureBenefitFl',
                'excelKey' => 'culture_benefit_fl',
                'text'     => __('도서공연비 소득공제 상품 적용 여부'),
                'sample'   => '',
                'comment'  => __('y:적용, n:미적용'),
                'desc'     => __('도서공연비 소득공제 상품 적용 여부를 설정합니다.  y: 적용, n: 미적용, 기본은 n(미적용)입니다.'),
            ],
            [
                'dbName'   => 'goods',
                'dbKey'    => 'externalVideoFl',
                'excelKey' => 'external_video_fl',
                'text'     => __('외부동영상 연동 여부'),
                'sample'   => '',
                'comment'  => __('y:사용함, n:사용안함'),
                'desc'     => __('y:사용함, n:사용안함'),
            ],
            [
                'dbName'   => 'goods',
                'dbKey'    => 'externalVideoUrl',
                'excelKey' => 'external_video_url',
                'text'     => __('외부동영상 주소'),
                'sample'   => '',
                'comment'  => '',
                'desc'     => '',
            ],
            [
                'dbName'   => 'goods',
                'dbKey'    => 'externalVideoWidth',
                'excelKey' => 'external_video_width',
                'text'     => __('외부동영상 너비'),
                'sample'   => '',
                'comment'  => '',
                'desc'     => __('"너비"와 "높이"를 모두 입력하지 않으면 시스템 기본 사이즈 (640X360)로 등록됩니다.'),
            ],
            [
                'dbName'   => 'goods',
                'dbKey'    => 'externalVideoHeight',
                'excelKey' => 'external_video_height',
                'text'     => __('외부동영상 높이'),
                'sample'   => '',
                'comment'  => '',
                'desc'     => __('"너비"와 "높이"를 모두 입력하지 않으면 시스템 기본 사이즈 (640X360)로 등록됩니다.'),
            ],
            [
                'dbName'   => 'goods',
                'dbKey'    => 'goodsPrice',
                'excelKey' => 'goods_price',
                'text'     => __('판매가'),
                'sample'   => '1000',
                'comment'  => '',
                'desc'     => __('판매 가격입니다.'),
            ],
            [
                'dbName'   => 'goods',
                'dbKey'    => 'fixedPrice',
                'excelKey' => 'fixed_price',
                'text'     => __('정가'),
                'sample'   => '1300',
                'comment'  => '',
                'desc'     => __('정가입니다. '),
            ],
            [
                'dbName'   => 'goods',
                'dbKey'    => 'costPrice',
                'excelKey' => 'cost_price',
                'text'     => __('매입가'),
                'sample'   => '900',
                'comment'  => '',
                'desc'     => __('매입가입니다.'),
            ],
            [
                'dbName'   => 'goods',
                'dbKey'    => 'optionFl',
                'excelKey' => 'option_yn',
                'text'     => __('옵션 사용 여부'),
                'sample'   => 'n',
                'comment'  => __('y:옵션<br />n:일반'),
                'desc'     => __('옵션 사용여부입니다. y:옵션, n:일반, 기본은 n 이며, 일반적인 옵션 없는 상품입니다.'),
            ],
            [
                'dbName'   => 'goods',
                'dbKey'    => 'optionDisplayFl',
                'excelKey' => 'option_display',
                'text'     => __('옵션 표시 방법'),
                'sample'   => 's',
                'comment'  => __('s:일체형, d:분리형'),
                'desc'     => __('s:일체형, d:분리형, 기본은 s(일체형)입니다.'),
            ],
            [
                'dbName'   => 'goods',
                'dbKey'    => 'optionName',
                'excelKey' => 'option_name',
                'text'     => __('옵션명'),
                'sample'   => '',
                'comment'  => sprintf('%s: "' . STR_DIVISION.'"', __('다중구분')),
                'desc'     => sprintf(__('옵션 사용시 옵션명입니다. 구분은 &quot;%s&quot; 입니다.'), STR_DIVISION),
            ],
            [
                'dbName'   => 'option',
                'dbKey'    => 'optionValue',
                'excelKey' => 'option_value',
                'text'     => __('옵션값'),
                'sample'   => '',
                'comment'  => sprintf(__('옵션구분:%s<br />다중구분:Alt+Enter(개행)'), STR_DIVISION),
                'desc'     => sprintf(__('옵션명을 여러 개 사용할 경우 조합된 상태의 옵션값을 입력하세요, 입력형식은 "옵션값%s옵션값", 다중구분:Alt+Enter(개행)'), STR_DIVISION),
            ],
            [
                'dbName'   => 'icon',
                'dbKey'    => 'optionImage',
                'excelKey' => 'option_image',
                'text'     => __('옵션이미지'),
                'sample'   => '',
                'comment'  => sprintf(__('옵션이미지구분:%s<br />다중구분:Alt+Enter(개행)'), STR_DIVISION),
                'desc'     => sprintf(__('입력형식은 "옵션값%s이미지명", 다중구분:Alt+Enter(개행)'), STR_DIVISION),
            ],
            [
                'dbName'   => 'option',
                'dbKey'    => 'optionCostPrice',
                'excelKey' => 'option_cost_price',
                'text'     => __('옵션매입가격'),
                'sample'   => '',
                'comment'  => __('다중구분:Alt+Enter(개행)'),
                'desc'     => __('다중구분:Alt+Enter(개행)'),
            ],
            [
                'dbName'   => 'option',
                'dbKey'    => 'optionPrice',
                'excelKey' => 'option_price',
                'text'     => __('옵션가격'),
                'sample'   => '',
                'comment'  => __('다중구분:Alt+Enter(개행)'),
                'desc'     => __('상품의 판매가 기준 추가될 옵션가는 양수, 차감될 옵션가는 음수(마이너스)로 입력 합니다. 다중구분:Alt+Enter(개행)'),
            ],
            [
                'dbName'   => 'option',
                'dbKey'    => 'stockCnt',
                'excelKey' => 'stock_cnt',
                'text'     => __('재고'),
                'sample'   => '10',
                'comment'  => __('다중구분:Alt+Enter(개행)'),
                'desc'     => __('재고 이며, 옵션이 있는 경우는 각 옵션별로 &quot;Alt+Enter(개행)&quot;로 구분을 합니다.'),
            ],
            //현재 추가 개발진행 중이므로 수정하지 마세요! 주석 처리된 내용을 수정할 경우 기능이 정상 작동하지 않거나, 추후 기능 배포시 오류의 원인이 될 수 있습니다.
            /*
            [
                'dbName'   => 'option',
                'dbKey'    => 'sellStopFl',
                'excelKey' => 'sell_stop_fl',
                'text'     => __('판매중지수량 사용상태'),
                'sample'   => 'y',
                'comment'  => __('y:사용함 n:사용안함 다중구분:Alt+Enter(개행)'),
                'desc'     => __('설정한 개수에 도달했을 시 품절처리 및 알림 발송여부를 설정합니다. y:사용함 n:사용안함 다중구분:Alt+Enter(개행)'),
            ],
            [
                'dbName'   => 'option',
                'dbKey'    => 'sellStopStock',
                'excelKey' => 'sell_stop_stock',
                'text'     => __('판매중지수량'),
                'sample'   => '5',
                'comment'  => __('다중구분:Alt+Enter(개행)'),
                'desc'     => __('품절처리 및 알림을 발송 할 수량을 설정합니다. 다중구분:Alt+Enter(개행)'),
            ],
            [
                'dbName'   => 'option',
                'dbKey'    => 'confirmRequestFl',
                'excelKey' => 'confirm_request_fl',
                'text'     => __('확인요청수량 사용상태'),
                'sample'   => 'y',
                'comment'  => __('y:사용함 n:사용안함 다중구분:Alt+Enter(개행)'),
                'desc'     => __('설정한 개수에 도달했을 시 알림 발송여부를 설정합니다. y:사용함 n:사용안함 다중구분:Alt+Enter(개행)'),
            ],
            [
                'dbName'   => 'option',
                'dbKey'    => 'confirmRequestStock',
                'excelKey' => 'confirm_request_stock',
                'text'     => __('확인요청수량'),
                'sample'   => '10',
                'comment'  => __('다중구분:Alt+Enter(개행)'),
                'desc'     => __('알림을 발송 할 수량을 설정합니다. 다중구분:Alt+Enter(개행)'),
            ],
            */
            [
                'dbName'   => 'option',
                'dbKey'    => 'optionViewFl',
                'excelKey' => 'option_view_fl',
                'text'     => __('옵션 노출여부'),
                'sample'   => 'y',
                'comment'  => __('y:노출함 n:노출안함 다중구분:Alt+Enter(개행)'),
                'desc'     => __('옵션노출여부 이며, 옵션이 있는 경우는 각 옵션별로 &quot;Alt+Enter(개행)&quot;로 구분을 합니다.'),
            ],
            [
                'dbName'   => 'option',
                'dbKey'    => 'optionSellFl',
                'excelKey' => 'option_sell_fl',
                'text'     => __('옵션 품절여부'),
                'sample'   => 'y',
                'comment'  => __('n:품절 y:정상 [코드]품절 안내 다중구분:Alt+Enter(개행)'),
                'desc'     => __('옵션품절여부 이며, 옵션이 있는 경우는 각 옵션별로 &quot;Alt+Enter(개행)&quot;로 구분을 합니다.'),
            ],
            [
                'dbName'   => 'option',
                'dbKey'    => 'optionDeliveryFl',
                'excelKey' => 'option_delivery_fl',
                'text'     => __('옵션 배송상태'),
                'sample'   => 'y',
                'comment'  => __('y:정상 [코드]: 배송 지연 안내 다중구분:Alt+Enter(개행)'),
                'desc'     => __('y:정상 n:배송지연 다중구분:Alt+Enter(개행)'),
            ],
            [
                'dbName'   => 'option',
                'dbKey'    => 'optionCode',
                'excelKey' => 'option_code',
                'text'     => __('자체옵션코드'),
                'sample'   => '',
                'comment'  => __('다중구분:Alt+Enter(개행)'),
                'desc'     => __('30자 이내, 영문 대소문자/숫자/특수문자를 이용하여 입력합니다. (단, 특수문자는 “_”(언더바) 외 입력 불가), 다중구분:Alt+Enter(개행)'),
            ],
            [
                'dbName'   => 'option',
                'dbKey'    => 'optionMemo',
                'excelKey' => 'option_memo',
                'text'     => __('옵션 메모'),
                'sample'   => '',
                'comment'  => __('다중구분:Alt+Enter(개행)'),
                'desc'     => __('다중구분:Alt+Enter(개행)'),
            ],
            [
                'dbName'   => 'goods',
                'dbKey'    => 'optionTextFl',
                'excelKey' => 'text_option_yn',
                'text'     => __('텍스트 옵션 사용여부'),
                'sample'   => 'n',
                'comment'  => __('y:사용함 n:사용안함'),
                'desc'     => __('y:사용함, n:사용안함, 기본은 n(사용안함)입니다.'),
            ],
            [
                'dbName'   => 'text',
                'dbKey'    => 'optionText',
                'excelKey' => 'text_option',
                'text'     => __('텍스트 옵션'),
                'sample'   => '',
                'comment'  => sprintf(__('"옵션명%1$s필수여부%1$s옵션금액%1$s입력제한수" 필수여부(y:필수, n:비필수) 다중구분:Alt+Enter(개행)'), STR_DIVISION),
                'desc'     => sprintf('입력형식은 "옵션명%1$s필수여부%1$s옵션금액%1$s입력제한수", 필수여부(y:필수, n:비필수), 다중구분:Alt+Enter(개행)', STR_DIVISION),
            ],
            [
                'dbName'   => 'goods',
                'dbKey'    => 'relationFl',
                'excelKey' => 'relation_yn',
                'text'     => __('관련상품'),
                'sample'   => 'n',
                'comment'  => __('n:사용안함<br />a:자동<br />m:수동'),
                'desc'     => __('n:사용안함, a:자동, m:수동, 기본은 n(사용안함)입니다.'),
            ],
            [
                'dbName'   => 'goods',
                'dbKey'    => 'relationSameFl',
                'excelKey' => 'relation_same_fl',
                'text'     => __('관련상품 서로 등록 여부'),
                'sample'   => '',
                'comment'  => __('y:사용함 n:사용안함 s:선택상품 사용함'),
                'desc'     => __('관심상품을 서로 등록할지 여부를 등록합니다. n:사용안함, y:전체상품 사용함, s:선택상품 사용함, 기본은 n(사용안함 입니다)'),
            ],
            [
                'dbName'   => 'goods',
                'dbKey'    => 'relationGoodsNo',
                'excelKey' => 'relation_code',
                'text'     => __('관련상품 코드'),
                'sample'   => '',
                'comment'  => sprintf(__('상품 코드%s상품코드'), INT_DIVISION),
                'desc'     => sprintf(__('수동인 경우 출력할 상품 코드입니다.구분은 &quot;%s&quot; 입니다.'), INT_DIVISION),
            ],
            [
                'dbName'   => 'goods',
                'dbKey'    => 'relationGoodsDate',
                'excelKey' => 'relation_goods_date',
                'text'     => __('관련상품 노출기간'),
                'sample'   => '',
                'comment'  => sprintf(__('상품코드%1$s시작일자%1$s종료일자<br />다중구분:Alt+Enter(개행)'), STR_DIVISION),
                'desc'     => sprintf(__('입력형식은 "상품코드%1$s노출 시작일자%1$s노출 만료일자", 다중구분:Alt+Enter(개행)'), STR_DIVISION),
            ],
            [
                'dbName'   => 'goods',
                'dbKey'    => 'relationGoodsNo',
                'excelKey' => 'relation_goods_each',
                'text'     => __('관련상품 서로등록 상품코드'),
                'sample'   => '',
                'comment'  => sprintf(__('상품 코드||상품코드')),
                'desc'     => sprintf(__('관련상품 서로 등록이 선택상품 사용함인 경우 서로등록 할 상품 코드입니다. 구분은 "||" 입니다.'), STR_DIVISION),
            ],
            [
                'dbName'   => 'goods',
                'dbKey'    => 'addGoodsFl',
                'excelKey' => 'add_goods_fl',
                'text'     => __('추가상품 사용여부'),
                'sample'   => 'n',
                'comment'  => __('n:사용안함<br />y:사용함'),
                'desc'     => __('y:사용함, n:사용안함, 기본은 n(사용안함)입니다.'),
            ],
            [
                'dbName'   => 'goods',
                'dbKey'    => 'addGoods',
                'excelKey' => 'add_goods',
                'text'     => __('추가상품 설정'),
                'sample'   => '',
                'comment'  => sprintf(__('추가상품 그룹명%1$s필수여부%1$s상품코드%1$s상품코드 필수여부(y:필수, n:비필수) 다중구분:Alt+Enter(개행)'), STR_DIVISION),
                'desc'     => sprintf(__('입력형식은 "추가상품 표시명%1$s필수여부%1$s추가상품코드%1$s추가상품코드", 필수여부(y:필수, n:비필수), 다중구분:Alt+Enter(개행)'), STR_DIVISION),
            ],
            [
                'dbName'   => 'goods',
                'dbKey'    => 'imgDetailViewFl',
                'excelKey' => 'imgDetail_view_fl',
                'text'     => __('이미지 돋보기 사용여부'),
                'sample'   => 'n',
                'comment'  => __('n:사용안함 y:사용함'),
                'desc'     => __('y:사용함, n:사용안함, 기본은 n(사용안함)입니다.'),
            ],
            [
                'dbName'   => 'goods',
                'dbKey'    => 'imageStorage',
                'excelKey' => 'image_storage',
                'text'     => __('이미지 저장소'),
                'sample'   => 'local',
                'comment'  => __('기본 저장소 : local / 외부 저장소 : HTTP 경로 입력'),
                'desc'     => __('기본 저장소 : local / 외부 저장소 : HTTP 경로 입력'),
            ],
            [
                'dbName'   => 'goods',
                'dbKey'    => 'imagePath',
                'excelKey' => 'image_path',
                'text'     => __('이미지 경로'),
                'sample'   => '',
                'comment'  => __('등록시에는 자동 연결 되므로 넣지 마세요<br>(이미지URL 직접 등록 시 빈값으로 입력)'),
                'desc'     => __('등록시에는 자동 연결 되므로 넣지 마세요<br>(이미지URL 직접 등록 시 빈값으로 입력)'),
            ],
            [
                'dbName'   => 'image',
                'dbKey'    => 'imageName',
                'excelKey' => 'image_name',
                'text'     => __('이미지명'),
                'sample'   => __('main^|^이미지명.JPG<br/>list^|^이미지명.JPG<br/>detail^|^이미지명.JPG^|^이미지명.JPG<br/>magnify^|^이미지명.JPG^|^이미지명.JPG<br/>add1^|^이미지명.JPG<br/>add2^|^이미지명.JPG'),
                'comment'  => sprintf(__('main:리스트<br />list:썸네일<br />detail:상품상세<br />magnify:확대<br /> add1:추가 리스트<br />종류%s이미지명<br />다중구분:Alt+Enter(개행)<br/>이미지URL 등록시 : 종류^|^이미지URL'), STR_DIVISION),
                'desc'     => __(' 이미지 종류와 이미지 명은 "^|^"로 구분하며 이미지 명은 파일형식까지 입력합니다. 다중구분:Alt+Enter(개행) 이미지 종류는 main(리스트), list(썸네일), detail(상품상세), magnify(확대), add1(추가 리스트) 입니다.<br/>"기본설정>상품정책>상품 이미지 사이즈 설정" 메뉴의 "리스트 이미지 종류 추가"항목에서 리스트 이미지 종류를 추가한 경우 "이미지 종류"에는 add1, add2, add3의 형식으로 입력합니다.'),
            ],
            [
                'dbName'   => 'goods',
                'dbKey'    => 'shortDescription',
                'excelKey' => 'short_desc',
                'text'     => __('짧은 설명'),
                'sample'   => __('테스트'),
                'comment'  => '',
                'desc'     => __('250자 이내의 간단한 상품 설명'),
                'tagFl'    => 'n',
            ],
            [
                'dbName'   => 'goods',
                'dbKey'    => 'eventDescription',
                'excelKey' => 'event_description',
                'text'     => __('이벤트 문구'),
                'sample'   => __('테스트'),
                'comment'  => '',
                'desc'     => '',
                'tagFl'    => 'n',
            ],
            [
                'dbName'   => 'goods',
                'dbKey'    => 'goodsDescription',
                'excelKey' => 'goods_desc_pc',
                'text'     => __('PC쇼핑몰 상세 설명'),
                'sample'   => __('테스트'),
                'comment'  => '',
                'desc'     => __('상품에 대한 상세한 설명 입력'),
                'tagFl'    => 'n',
            ],
            [
                'dbName'   => 'goods',
                'dbKey'    => 'goodsDescriptionMobile',
                'excelKey' => 'goods_desc_mobile',
                'text'     => __('모바일쇼핑몰 상세 설명'),
                'sample'   => __('테스트'),
                'comment'  => '',
                'desc'     => __('상품에 대한 상세한 설명(모바일용) 입력'),
                'tagFl'    => 'n',
            ],
            [
                'dbName'   => 'goods',
                'dbKey'    => 'goodsDescriptionSameFl',
                'excelKey' => 'goods_desc_same_flag',
                'text'     => __('PC/모바일 상세설명 동일사용 여부'),
                'sample'   => 'y',
                'comment'  => __('y : 동일사용, n : 개별사용 (기본값 : y)'),
                'desc'     => __('y : 동일사용, n : 개별사용 (기본값 : y)'),
            ],
            [
                'dbName'   => 'goods',
                'dbKey'    => 'daumFl',
                'excelKey' => 'daum_flag',
                'text'     => __('쇼핑하우 노출여부'),
                'sample'   => 'y',
                'comment'  => __('다음 쇼핑하우 노출여부 설정<br /> y : 노출(기본값), n : 노출안함'),
                'desc'     => __('다음 쇼핑하우 노출여부 설정, y : 노출(기본값), n : 노출안함'),
            ],
            [
                'dbName'   => 'goods',
                'dbKey'    => 'naverFl',
                'excelKey' => 'naver_flag',
                'text'     => __('네이버쇼핑 노출여부'),
                'sample'   => 'y',
                'comment'  => __('네이버 쇼핑 노출여부 설정, y : 노출(기본값), n : 노출안함'),
                'desc'     => __('네이버 쇼핑 노출여부 설정, y : 노출(기본값), n : 노출안함'),
                'tagFl'    => 'n',
            ],
            [
                'dbName'   => 'goods',
                'dbKey'    => 'naverImportFlag',
                'excelKey' => 'naver_import_flag',
                'text'     => __('수입 및 제작 여부'),
                'sample'   => '',
                'comment'  => __('f:해외구매대행 d:병행수입 o:주문제작'),
                'desc'     => __('f:해외구매대행 d:병행수입 o:주문제작'),
                'tagFl'    => 'n',
            ],
            [
                'dbName'   => 'goods',
                'dbKey'    => 'naverProductFlag',
                'excelKey' => 'naver_product_flag',
                'text'     => __('판매 방식 구분'),
                'sample'   => '',
                'comment'  => __('w : 도매 r: 렌탈 h:대여 i:할부 s: 예약판매 b: 구매대행'),
                'desc'     => __('w : 도매 r: 렌탈 h:대여 i:할부 s: 예약판매 b: 구매대행'),
                'tagFl'    => 'n',
            ],
            [
                'dbName'   => 'goods',
                'dbKey'    => 'naverAgeGroup',
                'excelKey' => 'naver_age_group',
                'text'     => __('주요사용연령대'),
                'sample'   => '',
                'comment'  => __('a : 성인 y : 청소년 c: 아동 b:유아'),
                'desc'     => __('a : 성인 y : 청소년 c: 아동 b:유아'),
                'tagFl'    => 'n',
            ],
            [
                'dbName'   => 'goods',
                'dbKey'    => 'naverGender',
                'excelKey' => 'naver_gender',
                'text'     => __('주요사용성별'),
                'sample'   => '',
                'comment'  => __('m : 남성 w : 여성 c: 공용'),
                'desc'     => __('m : 남성 w : 여성 c: 공용'),
                'tagFl'    => 'n',
            ],
            [
                'dbName'   => 'goods',
                'dbKey'    => 'naverTag',
                'excelKey' => 'naver_tag',
                'text'     => __('검색태그'),
                'sample'   => '',
                'comment'  => '',
                'desc'  => '',
                'tagFl'    => 'n',
            ],
            [
                'dbName'   => 'goods',
                'dbKey'    => 'naverAttribute',
                'excelKey' => 'naver_attribute',
                'text'     => __('속성정보'),
                'sample'   => '',
                'comment'  => '',
                'desc'  => '',
                'tagFl'    => 'n',
            ],
            [
                'dbName'   => 'goods',
                'dbKey'    => 'naverCategory',
                'excelKey' => 'naver_category',
                'text'     => __('네이버카테고리ID'),
                'sample'   => '',
                'comment'  => '',
                'desc'  => '',
                'tagFl'    => 'n',
            ],
            [
                'dbName'   => 'goods',
                'dbKey'    => 'naverProductId',
                'excelKey' => 'naver_product_id',
                'text'     => __('가격비교페이지ID'),
                'sample'   => '',
                'comment'  => '',
                'desc'  => '',
                'tagFl'    => 'n',
            ],
            [
                'dbName'   => 'goods',
                'dbKey'    => 'detailInfoDeliveryFl',
                'excelKey' => 'detail_delivery_fl',
                'text'     => __('배송 안내 입력선택'),
                'sample'   => 'no',
                'comment'  => __('no:사용안함<br />direct:직접입력<br/>selection:선택입력'),
                'desc'     => __('no:사용안함, n:직접입력,selection:선택입력, 기본은 no(사용안함)입니다.'),
            ],
            [
                'dbName'   => 'goods',
                'dbKey'    => 'detailInfoDelivery',
                'excelKey' => 'detail_delivery',
                'text'     => __('배송 안내'),
                'sample'   => '',
                'comment'  => '',
                'desc'     => __('배송 안내 입력선택이 직접입력(direct)인 경우 : 배송안내 내용을 입력합니다.<br/>배송 안내 입력선택이 선택입력(selection)인 경우 : [기본설정>상품정책>상품 상세 이용안내 관리] 메뉴에 등록된 배송안내의 6자리 코드를 입력합니다.기본코드는 002001 입니다.'),
            ],
            [
                'dbName'   => 'goods',
                'dbKey'    => 'detailInfoASFl',
                'excelKey' => 'detail_as_fl',
                'text'     => __('AS 안내 입력선택'),
                'sample'   => 'no',
                'comment'  => __('no:사용안함<br />direct:직접입력<br/>selection:선택입력'),
                'desc'     => __('no:사용안함, n:직접입력,selection:선택입력, 기본은 no(사용안함)입니다.'),
            ],
            [
                'dbName'   => 'goods',
                'dbKey'    => 'detailInfoAS',
                'excelKey' => 'detail_as',
                'text'     => __('AS 안내'),
                'sample'   => '',
                'comment'  => '',
                'desc'     => __('AS 안내 입력선택이 직접입력(direct)인 경우 : AS안내 내용을 입력합니다.<br/>AS 안내 입력선택이 선택입력(selection)인 경우 : [기본설정>상품정책>상품 상세 이용안내 관리] 메뉴에 등록된 AS안내의 6자리 코드를 입력합니다.기본코드는 003001 입니다.'),
            ],
            [
                'dbName'   => 'goods',
                'dbKey'    => 'detailInfoRefundFl',
                'excelKey' => 'detail_refund_fl',
                'text'     => __('환불 안내 입력선택'),
                'sample'   => 'no',
                'comment'  => __('no:사용안함<br />direct:직접입력<br/>selection:선택입력'),
                'desc'     => __('no:사용안함, n:직접입력,selection:선택입력, 기본은 no(사용안함)입니다.'),
            ],
            [
                'dbName'   => 'goods',
                'dbKey'    => 'detailInfoRefund',
                'excelKey' => 'detail_refund',
                'text'     => __('환불 안내'),
                'sample'   => '',
                'comment'  => '',
                'desc'     => __('환불 안내 입력선택이 직접입력(direct)인 경우 : 환불안내 내용을 입력합니다.<br/>환불 안내 입력선택이 선택입력(selection)인 경우 : [기본설정>상품정책>상품 상세 이용안내 관리] 메뉴에 등록된 환불안내의 6자리 코드를 입력합니다.기본코드는 004001 입니다.'),
            ],
            [
                'dbName'   => 'goods',
                'dbKey'    => 'detailInfoExchangeFl',
                'excelKey' => 'detail_exchange_fl',
                'text'     => __('교환 안내 입력선택'),
                'sample'   => 'no',
                'comment'  => __('no:사용안함<br />direct:직접입력<br/>selection:선택입력'),
                'desc'     => __('no:사용안함, n:직접입력,selection:선택입력, 기본은 no(사용안함)입니다.'),
            ],
            [
                'dbName'   => 'goods',
                'dbKey'    => 'detailInfoExchange',
                'excelKey' => 'detail_exchange',
                'text'     => __('교환 안내'),
                'sample'   => '',
                'comment'  => '',
                'desc'     => __('교환 안내 입력선택이 직접입력(direct)인 경우 : 교환안내 내용을 입력합니다.<br/>교환 안내 입력선택이 선택입력(selection)인 경우 : [기본설정>상품정책>상품 상세 이용안내 관리] 메뉴에 등록된 교환안내의 6자리 코드를 입력합니다.기본코드는 005001 입니다.'),
            ],
            [
                'dbName'   => 'goods',
                'dbKey'    => 'memo',
                'excelKey' => 'memo',
                'text'     => __('상품 관리 메모'),
                'sample'   => '',
                'comment'  => '',
                'desc'     => __('관리자 메모를 입력'),
            ],
            [
                'dbName'   => 'goods',
                'dbKey'    => 'hscode',
                'excelKey' => 'hscode',
                'text'     => __('HS 코드'),
                'sample'   => '',
                'comment'  => __('해외상점 사용시 HS코드를 입력해 주세요.<br/>국가명^|^HS코드<br/>다중구분:Alt+Enter(개행)<br/>kr : 대한민국 us : 미국 cn : 중국 jp : 일본<br/>예시) kr^|^0101'),
                'desc'     => __('해외상점 사용시 HS코드를 입력해 주세요.<br/>국가명^|^HS코드<br/>다중구분:Alt+Enter(개행)<br/>kr : 대한민국 us : 미국 cn : 중국 jp : 일본<br/>예시) kr^|^0101'),
            ],
            [
                'dbName'   => 'goods',
                'dbKey'    => 'seoTagFl',
                'excelKey' => 'seo_tag_fl',
                'text'     => __('상품 개별 SEO 설정 사용여부'),
                'sample'   => '',
                'comment'  => __('y:사용함, n:사용안함'),
                'desc'     => __('y:사용함, n:사용안함, 기본은 n(사용안함)입니다.'),
            ],
            /*[
                'dbName'   => 'goods',
                'dbKey'    => 'seoTagSno',
                'excelKey' => 'seo_tag_sno',
                'text'     => __('상품 개별 SEO 고유키'),
                'desc'   => '해당 상품의 SEO관련 태그 설정을 연결해주는 키로 각 상품별로 고유한 번호가 부여되어야 합니다. <br/>해당 필드 미 입력 또는 중복된 번호를 입력할 경우, ‘타이틀, 메타태그 작성자, 메타태그 설명, 메타태그 키워드’가 정상적으로 연결되지 않으니 주의 바랍니다.',
            ],*/
            [
                'dbName'   => 'seoTag',
                'dbKey'    => 'seoTagTitle',
                'excelKey' => 'set_tag_title',
                'text'     => __('타이틀'),
                'desc'   => '해당 상품의 브라우저 타이틀 개별 문구. 한글기준 10자 이내 작성을 권장합니다.',
            ],
            [
                'dbName'   => 'seoTag',
                'dbKey'    => 'seoTagAuthor',
                'excelKey' => 'set_tag_author',
                'text'     => __('메타태그 작성자'),
                'desc'   => '해당 상품 페이지의 개별 작성자',
            ],
            [
                'dbName'   => 'seoTag',
                'dbKey'    => 'seoTagDescription',
                'excelKey' => 'set_tag_description',
                'text'     => __('메타태그 설명'),
                'desc'   => '해당 상품의 개별 요약내용. 공백 포함 80자 이내 작성을 권장합니다.',
            ],
            [
                'dbName'   => 'seoTag',
                'dbKey'    => 'seoTagKeyword',
                'excelKey' => 'set_tag_keyword',
                'text'     => __('메타태그 키워드'),
                'desc'   => '해당 상품의 개별 검색어. ＂,＂(콤마)로 구분하여 10개 이내로 입력하는 것을 권장합니다.',
            ],
            [
                'dbName'   => 'facebookGoodsFeed',
                'dbKey'    => 'fbVn',
                'excelKey' => 'fb_vn',
                'text'     => __('페이스북 제품 피드 설정'),
                'desc'   => '페이스북 제품 피드 설정',
            ],
            [
                'dbName'   => 'image',
                'dbKey'    => 'fbImageName',
                'excelKey' => 'fb_image_name',
                'text'     => '페이스북 광고 제품 피드 이미지명',
                'desc'     => '페이스북 광고 이미지명',
            ],
        ];

        if(gd_is_plus_shop(PLUSSHOP_CODE_PURCHASE) === true && gd_is_provider() === false) {
            $arrField[] = [
                'dbName'   => 'goods',
                'dbKey'    => 'purchaseNo',
                'excelKey' => 'purchaseNo',
                'text'     => __('매입처 코드'),
                'sample'   => '000001',
                'comment'  => __('매입처 사용시 매입처 코드를 입력해 주세요.'),
                'desc'     => __('매입처 사용시 매입처 코드를 입력해 주세요.'),
            ];
        }

        // @formatter:on

        $gGlobal = Globals::get('gGlobal');

        foreach ($gGlobal['mallList'] as $k => $v) {
            if ($v['standardFl'] == 'n') {
                $globalData['goodsNm'][] = [
                    'dbName'   => 'goodsGlobal',
                    'dbKey'    => 'globalData_' . $k . '_goodsNm',
                    'excelKey' => 'global_data_' . $k . '_goodsnm',
                    'text'     => __('상품명_' . $v['mallName']),
                    'sample'   => '',
                    'comment'  => __('입력시 별도의 해외상점 상품명이 등록됩니다.'),
                    'desc'     => __('입력시 별도의 해외상점 상품명이 등록됩니다.'),
                ];

                $globalData['shortDescription'][] = [
                    'dbName'   => 'goodsGlobal',
                    'dbKey'    => 'globalData_' . $k . '_shortDescription',
                    'excelKey' => 'global_data_' . $k . '_short_description',
                    'text'     => __('짧은_설명_' . $v['mallName']),
                    'sample'   => '',
                    'comment'  => __('입력시 별도의 해외상점 짧은설명이 등록됩니다.'),
                    'desc'     => __('입력시 별도의 해외상점 짧은설명이 등록됩니다.'),
                ];
            }
        }

        $tmpPosition = array_search('goodsSearchWord', array_column($arrField, 'dbKey'));
        $startArr = array_slice($arrField, 0, $tmpPosition);
        $endArr = array_slice($arrField, $tmpPosition);
        $arrField = array_merge($startArr, $globalData['goodsNm'], $endArr);

        $tmpPosition = array_search('eventDescription', array_column($arrField, 'dbKey'));
        $startArr = array_slice($arrField, 0, $tmpPosition);
        $endArr = array_slice($arrField, $tmpPosition);
        $arrField = array_merge($startArr, $globalData['shortDescription'], $endArr);

        return $arrField;
    }

    /**
     * [상품] goods 필드 기본값에서 제외 처리
     *
     * @author tomi
     * @return array goods 테이블 필드 정보
     */
    public function excelGoodsExclude($excelField)
    {
        // 제외 항목
        $excludeArrField = [
            'orderGoodsCnt', // 주문상품 수
            'hitCnt', // 조회수
            'orderRate', // 구매율
            'cartCnt', // 장바구니 수
            'wishCnt', // 관심상품 수
            'reviewCnt', // 후기 수
        ];

        // 전체 항목
        foreach ($excelField as $key => $val) {
            // 제외 항목 제거
            if (in_array($val['dbKey'], $excludeArrField)) {
                unset($excelField[$key]);
            }
//            $excelField[$key] = $val;
        }

        return $excelField;
    }

    /**
     * 관리자 상품 리스트를 위한 검색 정보
     */
    public function setSearchGoods($postValue)
    {
        // --- 검색 설정
        $postValue['detailSearch'] = StringUtils::strIsSet($postValue['detailSearch']);
        $postValue['key'] = StringUtils::strIsSet($postValue['key']);
        $postValue['keyword'] = StringUtils::strIsSet($postValue['keyword']);
        $postValue['cateGoods'] = ArrayUtils::last(StringUtils::strIsSet($postValue['cateGoods']));
        $postValue['brand'] = ArrayUtils::last(StringUtils::strIsSet($postValue['brand']));
        $postValue['makerNm'] = StringUtils::strIsSet($postValue['makerNm']);
        $postValue['originNm'] = StringUtils::strIsSet($postValue['originNm']);
        $postValue['goodsPrice'][] = StringUtils::strIsSet($postValue['goodsPrice'][0]);
        $postValue['goodsPrice'][] = StringUtils::strIsSet($postValue['goodsPrice'][1]);
        $postValue['mileage'][] = StringUtils::strIsSet($postValue['mileage'][0]);
        $postValue['mileage'][] = StringUtils::strIsSet($postValue['mileage'][1]);
        $postValue['optionFl'] = StringUtils::strIsSet($postValue['optionFl']);
        $postValue['addGoodsFl'] = StringUtils::strIsSet($postValue['addGoodsFl']);
        $postValue['mileageFl'] = StringUtils::strIsSet($postValue['mileageFl']);
        $postValue['optionTextFl'] = StringUtils::strIsSet($postValue['optionTextFl']);
        $postValue['goodsDisplayFl'] = StringUtils::strIsSet($postValue['goodsDisplayFl']);
        $postValue['goodsSellFl'] = StringUtils::strIsSet($postValue['goodsSellFl']);
        $postValue['scmNo'] = StringUtils::strIsSet($postValue['scmNo'], (string) Session::get('manager.scmNo'));

        // 키워드 검색
        if ($postValue['key'] && $postValue['keyword']) {
            if ($postValue['key'] === 'all') {
                $tmpWhere = [
                    'goodsNm',
                    'goodsNo',
                    'goodsCd',
                    'goodsSearchWord',
                ];
                $arrWhereAll = [];
                foreach ($tmpWhere as $keyNm) {
                    $arrWhereAll[] = '(g.' . $keyNm . ' LIKE concat(\'%\',\'' . $this->db->escape($postValue['keyword']) . '\',\'%\'))';
                }
                $this->addArrWhere('(' . implode(' OR ', $arrWhereAll) . ')');
            } else {
                $this->addArrWhere('g.' . $postValue['key'] . ' LIKE concat(\'%\',\'' . $this->db->escape($postValue['keyword']) . '\',\'%\')');
            }
        }

        $this->addArrWhere('g.delFl = "n"');

        if ($postValue['scmNo']) {
            $this->addArrWhere('g.scmNo = \'' . $this->db->escape($postValue['scmNo']) . '\'');
        }


        // 카테고리 검색
        if ($postValue['cateGoods']) {
            $this->addArrWhere('gl.cateCd = \'' . $this->db->escape($postValue['cateGoods']) . '\'');
        }
        // 브랜드 검색
        if ($postValue['brand']) {
            $this->addArrWhere('g.brandCd LIKE \'' . $this->db->escape($postValue['brand']) . '%\'');
        }
        // 제조사 검색
        if ($postValue['makerNm']) {
            $this->addArrWhere('g.makerNm = \'' . $this->db->escape($postValue['makerNm']) . '\'');
        }
        // 원산지 검색
        if ($postValue['originNm']) {
            $this->addArrWhere('g.originNm = \'' . $this->db->escape($postValue['originNm']) . '\'');
        }
        // 상품가격 검색
        if ($postValue['goodsPrice'][1]) {
            $this->addArrWhere('g.goodsPrice BETWEEN \'' . $this->db->escape($postValue['goodsPrice'][0]) . '\' AND \'' . $this->db->escape($postValue['goodsPrice'][1]) . '\'');
        }
        // 마일리지 검색
        if ($postValue['mileage'][1]) {
            $this->addArrWhere('g.mileageGoods BETWEEN \'' . $this->db->escape($postValue['mileage'][0]) . '\' AND \'' . $this->db->escape($postValue['mileage'][1]) . '\'');
        }
        // 옵션 사용 여부 검색
        if ($postValue['optionFl']) {
            $this->addArrWhere('g.optionFl = \'' . $this->db->escape($postValue['optionFl']) . '\'');
        }
        // 추가 상품 사용 여부 검색
        if ($postValue['addGoodsFl']) {
            $this->addArrWhere('g.addGoodsFl = \'' . $this->db->escape($postValue['addGoodsFl']) . '\'');
        }
        // 마일리지 정책 검색
        if ($postValue['mileageFl']) {
            $this->addArrWhere('g.mileageFl = \'' . $this->db->escape($postValue['mileageFl']) . '\'');
        }
        // 텍스트옵션 사용 여부 검색
        if ($postValue['optionTextFl']) {
            $this->addArrWhere('g.optionTextFl = \'' . $this->db->escape($postValue['optionTextFl']) . '\'');
        }
        // 상품 출력 여부 검색
        if ($postValue['goodsDisplayFl']) {
            $this->addArrWhere('g.goodsDisplayFl = \'' . $this->db->escape($postValue['goodsDisplayFl']) . '\'');
        }
        // 상품 판매 여부 검색
        if ($postValue['goodsSellFl']) {
            $this->addArrWhere('g.goodsSellFl = \'' . $this->db->escape($postValue['goodsSellFl']) . '\'');
        }
    }

    /**
     * 상품 엑셀 샘플 다운
     *
     * @author artherot
     */
    public function setExcelGoodsSampleDown()
    {
        // 기본 설정
        $excelField = $this->excelGoods();
        $arrField = [
            'text',
            'excelKey',
            'comment',
        ];

        // 제외 항목
        $excludeArrField = [
            'orderGoodsCnt', // 주문상품 수
            'hitCnt', // 조회수
            'orderRate', // 구매율
            'cartCnt', // 장바구니 수
            'wishCnt', // 관심상품 수
            'reviewCnt', // 후기 수
        ];
        // 전체 항목 선택
        foreach ($excelField as $key => $val) {
            // 제외 항목 제거
            if (in_array($val['dbKey'], $excludeArrField)) {
                unset($excelField[$key]);
                continue;
            }
            $setData['fieldCheck'][$key] = $val['dbKey'];
        }

        // 엑셀 상단
        echo $this->excelHeader;
        echo '<table border="1">' . chr(10);
        for ($i = 0; $i < count($arrField); $i++) {
            echo '<tr>' . chr(10);
            foreach ($excelField as $key => $val) {
                if (in_array($val['dbKey'], $setData['fieldCheck'])) {
                    echo '<td class="title">' . $val[$arrField[$i]] . '</td>' . chr(10);

                    // 이미지 설정
                    if ($i == 0 && $val['dbKey'] == 'imageName') {
                        $tmp = gd_policy('goods.image');
                        ImageUtils::sortImageConf($tmp); // 이미지 순서 변경
                        $imageKey = array_keys($tmp);
                        unset($tmp);
                    }
                }
            }
            echo '</tr>' . chr(10);
        }

        // 샘플 데이타
        foreach ($excelField as $key => $val) {
            $getData[0][$val['dbKey']] = $val['sample'];
        }
        unset($excelField, $arrField);

        // 엑셀 내용
        echo '<tr>' . chr(10);
        foreach ($getData as $sampleData) {
            foreach ($setData['fieldCheck'] as $fVal) {
                // 상품번호
                if ($fVal == 'goodsNo') {
                    $className = 'xl31';
                } else {
                    $className = 'xl24';
                }
                echo '<td class="' . $className . '">' . $sampleData[$fVal] . '</td>' . chr(10);
            }
        }
        echo '</tr>' . chr(10);
        echo '</table>' . chr(10);

        // 엑셀 하단
        echo $this->excelFooter;
    }

    /**
     * 상품 엑셀 다운
     *
     * @author artherot
     * @return array $setData 선택된 필드값
     */
    public function setExcelGoodsDown($setData)
    {
        $goodsDivisionFl = gd_policy('goods.config')['divisionFl'] == 'y' ? true : false;
        if ($goodsDivisionFl) $goodsTable = DB_GOODS_SEARCH;
        else $goodsTable = DB_GOODS;

        // 선택된 필드가 없는 경우
        if (count($setData['fieldCheck']) <= 0) {
            return false;
        }

        // 기본 설정
        $excelField = $this->excelGoods();
        $arrField = [
            'text',
            'excelKey',
            'comment',
        ];

        // 전체 다운인 경우 전체 항목 선택
        foreach ($excelField as $key => $val) {
            if ($setData['downType'] == 'all') $setData['fieldCheck'][$key] = $val['dbKey'];
            if ($val['tagFl'] == 'n') $setData['tagCheck'][] = $val['dbKey'];
        }

        // 엑셀 상단
        echo $this->excelHeader;
        echo '<table border="1">' . chr(10);
        for ($i = 0; $i < count($arrField); $i++) {
            echo '<tr>' . chr(10);
            foreach ($excelField as $key => $val) {
                if (in_array($val['dbKey'], $setData['fieldCheck'])) {
                    echo '<td class="title">' . $val[$arrField[$i]] . '</td>' . chr(10);

                    // 이미지 설정
                    if ($i == 0 && $val['dbKey'] == 'imageName') {
                        $tmp = gd_policy('goods.image');
                        ImageUtils::sortImageConf($tmp); // 이미지 순서 변경
                        $imageKey = array_keys($tmp);
                        unset($tmp);
                    }
                }
            }
            echo '</tr>' . chr(10);
        }

        unset($excelField, $arrField);

        // --- 검색 설정
        if ($setData['downType'] == 'all') {
            $setData['downRange'] = $setData['downRangeAll'];
            $setData['partStart'] = $setData['partStartAll'];
            $setData['partCnt'] = $setData['partCntAll'];
        }

        $postValue = Request::post()->toArray();
        $this->setSearchGoods($postValue);

        $pageLimit = "1000";

        if (!empty(ArrayUtils::last(gd_isset($postValue['cateGoods'])))) {
            $join[] = ' LEFT JOIN ' . DB_GOODS_LINK_CATEGORY . ' as gl ON g.goodsNo = gl.goodsNo ';
        }

        if ($setData['downRange'] == 'part') {
            if ($pageLimit >= $setData['partCnt']) {
                $pageNum = 0;
                $pageLimit = $setData['partCnt'];
            } else $pageNum = ceil($setData['partCnt'] / $pageLimit) - 1;
        } else {
            $strSQL = ' SELECT COUNT(g.goodsNo) AS cnt FROM ' . $goodsTable . ' as g ' . implode('', $join) . 'WHERE ' . implode(' AND ', $this->getArrWhere());
            $res = $this->db->query_fetch($strSQL, $this->arrBind, false);
            $totalCount = $res['cnt']; // 전체

            if ($pageLimit >= $totalCount) $pageNum = 0;
            else $pageNum = ceil($totalCount / $pageLimit) - 1;
        }

        // 결과 쿼리문 설정
        //$join[] = ' INNER JOIN ' . DB_GOODS_OPTION . ' as go ON g.goodsNo = go.goodsNo AND go.optionNo = 1 ';
        $arrFieldGoods = DBTableField::setTableField('tableGoods', null, null, 'g');
        $arrFieldOption = DBTableField::setTableField('tableGoodsOption', null, 'goodsNo', 'go');
        $arrFieldOptionIcon = DBTableField::setTableField('tableGoodsOptionIcon', null, 'goodsNo');
        $arrFieldOptionText = DBTableField::setTableField('tableGoodsOptionText', null, 'goodsNo');
        //$this->db->strField = implode(', ', $arrFieldGoods) . ', ' . implode(', ', $arrFieldOption);

        $goods = \App::load('\\Component\\Goods\\GoodsAdmin');
        $goodsColorList = $goods->getGoodsColorList(true);

        if (gd_is_plus_shop(PLUSSHOP_CODE_PURCHASE) === true && gd_is_provider() === false) {
            $strPurchaseSQL = 'SELECT purchaseNo,purchaseNm FROM ' . DB_PURCHASE . ' g  WHERE delFl = "n"';
            $tmpPurchaseData = $this->db->query_fetch($strPurchaseSQL);
            $purchaseData = array_combine(array_column($tmpPurchaseData, 'purchaseNo'), array_column($tmpPurchaseData, 'purchaseNm'));
        }


        for ($pi = 0; $pi <= $pageNum; $pi++) {

            $this->db->strField = implode(', ', $arrFieldGoods);
            $this->db->strJoin = implode('', $join);
            $this->db->strWhere = implode(' AND ', $this->getArrWhere());
            $this->db->strOrder = $setData['orderBy'];

            if ($setData['downRange'] == 'part') {
                $this->db->strLimit = (($pi * $pageLimit) + ($setData['partStart'] - 1)) . "," . $pageLimit;
            } else {
                $this->db->strLimit = (($pi * $pageLimit)) . "," . $pageLimit;
            }

            $query = $this->db->query_complete();
            if ($goodsDivisionFl) {
                $strSQL = 'SELECT ' . array_shift($query) . ' FROM (SELECT g.goodsNo FROM ' . $goodsTable . ' g ' . implode(' ', $query) . ")  gs  INNER JOIN " . DB_GOODS . " g  ON g.goodsNo = gs.goodsNo";
            } else {
                $strSQL = 'SELECT ' . array_shift($query) . ' FROM  ' . $goodsTable . ' g ' . implode(' ', $query);
            }
            $goodsListData = $this->db->query_fetch_generator($strSQL);
            $seoTag = \App::load('\\Component\\Policy\\SeoTag');

            // 엑셀 내용
            foreach ($goodsListData as $k => $getData) {
                $arrFieldGoodsGlobal = DBTableField::setTableField('tableGoodsGlobal');
                $strSQLGlobal = "SELECT gg." . implode(', gg.', $arrFieldGoodsGlobal) . " FROM " . DB_GOODS_GLOBAL . " as gg WHERE   gg.goodsNo  = '" . $getData['goodsNo'] . "'";
                $tmpData = $this->db->query_fetch($strSQLGlobal);
                if ($tmpData) {
                    foreach ($tmpData as $globalKey => $globalValue) {
                        if ($globalValue['goodsNm']) $getData['globalData_' . $globalValue['mallSno'] . '_goodsNm'] = $globalValue['goodsNm'];
                        if ($globalValue['shortDescription']) $getData['globalData_' . $globalValue['mallSno'] . '_shortDescription'] = $globalValue['shortDescription'];
                    }
                }

                $kcmarkInfoArr = [];
                if (empty($getData['kcmarkInfo']) === false) {
                    $kcmarkTmp = json_decode($getData['kcmarkInfo'], true);
                    if($kcmarkTmp['kcmarkDivFl'] == 'kcCd04' || $kcmarkTmp['kcmarkDivFl'] == 'kcCd05' || $kcmarkTmp['kcmarkDivFl'] == 'kcCd06'){
                        $kcmarkInfoArr = json_decode($getData['kcmarkInfo'], true);
                    }else{
                        $kcmarkTmp['kcmark_dt'] = '';
                        $kcmarkInfoArr = $kcmarkTmp;
                    }
                }

                // 데이타 처리
                // 옵션
                if ($getData['optionFl'] == 'y') {
                    // 옵션 처리
                    $strSQL = "SELECT " . implode(', ', $arrFieldOption) . " FROM " . DB_GOODS_OPTION . " go WHERE go.goodsNo = '" . $getData['goodsNo'] . "' ORDER BY go.optionNo ASC";
                    $result = $this->db->query($strSQL);
                    $optionValue = $optionCostPrice = $optionMemo = $optionPrice = $stockCnt = $optionCode = $optionViewFl = $optionSellFl = [];
                    while ($data = $this->db->fetch($result)) {
                        $tmp = [];
                        for ($i = 1; $i <= DEFAULT_LIMIT_OPTION; $i++) {
                            if (is_null($data['optionValue' . $i]) === false && strlen($data['optionValue' . $i]) > 0) {
                                $tmp[] = gd_htmlspecialchars(gd_htmlspecialchars_stripslashes($data['optionValue' . $i]));
                            }
                        }
                        $optionValue[] = implode(STR_DIVISION, ArrayUtils::removeEmpty($tmp));
                        $optionCostPrice[] = $data['optionCostPrice'];
                        $optionPrice[] = $data['optionPrice'];
                        $stockCnt[] = $data['stockCnt'];
                        $optionCode[] = $data['optionCode'];
                        $optionViewFl[] = $data['optionViewFl'];
                        if($data['optionSellFl'] != 'y'  && $data['optionSellFl'] != 'n' &&  $data['optionSellFl'] != ''){
                            $optionSellFl[] = $data['optionSellCode'];
                        }else{
                            $optionSellFl[] = $data['optionSellFl'];
                        }
                        if($data['optionDeliveryFl'] != 'normal' && $data['optionDeliveryFl'] != ''){
                            $optionDeliveryFl[] = $data['optionDeliveryCode'];
                        }else{
                            if($data['optionDeliveryFl'] == 'normal'){
                                $optionDeliveryFl[] = 'y';
                            }
                        }
                        $sellStopFl[] = $data['sellStopFl'];
                        $sellStopStock[] = $data['sellStopStock'];
                        $confirmRequestFl[] = $data['confirmRequestFl'];
                        $confirmRequestStock[] = $data['confirmRequestStock'];
                        $optionMemo[] = $data['optionMemo'];
                        unset($tmp);
                    }
                    $getData['optionValue'] = implode("<br style='mso-data-placement:same-cell;'>", $optionValue);
                    $getData['optionCostPrice'] = implode("<br style='mso-data-placement:same-cell;'>", $optionCostPrice);
                    $getData['optionPrice'] = implode("<br style='mso-data-placement:same-cell;'>", $optionPrice);
                    $getData['stockCnt'] = implode("<br style='mso-data-placement:same-cell;'>", $stockCnt);
                    $getData['optionCode'] = implode("<br style='mso-data-placement:same-cell;'>", $optionCode);
                    $getData['optionViewFl'] = implode("<br style='mso-data-placement:same-cell;'>", $optionViewFl);
                    $getData['optionSellFl'] = implode("<br style='mso-data-placement:same-cell;'>", $optionSellFl);
                    $getData['optionMemo'] = implode("<br style='mso-data-placement:same-cell;'>", $optionMemo);
                    $getData['optionSellCode'] =  implode("<br style='mso-data-placement:same-cell;'>", $optionSellCode);
                    $getData['optionDeliveryFl'] =  implode("<br style='mso-data-placement:same-cell;'>", $optionDeliveryFl);
                    $getData['optionDeliveryCode'] =  implode("<br style='mso-data-placement:same-cell;'>", $optionDeliveryCode);
                    $getData['sellStopFl'] =  implode("<br style='mso-data-placement:same-cell;'>", $sellStopFl);
                    $getData['sellStopStock'] =  implode("<br style='mso-data-placement:same-cell;'>", $sellStopStock);
                    $getData['confirmRequestFl'] =  implode("<br style='mso-data-placement:same-cell;'>", $confirmRequestFl);
                    $getData['confirmRequestStock'] =  implode("<br style='mso-data-placement:same-cell;'>", $confirmRequestStock);

                    $strSQL = "SELECT optionValue,goodsImage FROM " . DB_GOODS_OPTION_ICON . " go WHERE go.goodsNo = '" . $getData['goodsNo'] . "' ORDER BY go.optionNo ASC";
                    $result = $this->db->query($strSQL);
                    while ($data = $this->db->fetch($result)) {
                        $optionImage[] = gd_htmlspecialchars(gd_htmlspecialchars_stripslashes($data['optionValue'])) . STR_DIVISION . $data['goodsImage'];
                    }

                    $getData['optionImage'] = implode("<br style='mso-data-placement:same-cell;'>", $optionImage);
                    unset($optionValue, $optionPrice, $stockCnt, $data, $optionCode, $optionViewFl, $optionSellFl, $optionCostPrice, $optionMemo, $optionImage);
                    unset($optionDeliveryFl, $sellStopFl, $sellStopStock, $confirmRequestFl, $confirmRequestStock);
                } else {
                    $getData['optionValue'] = '';
                    $getData['stockCnt'] = $getData['totalStock'];
                }

                // 추가 변수 설정
                $getData['optionIcon'] = '';
                $getData['optionText'] = '';

                if($getData['seoTagFl'] =='y' && $getData['seoTagSno']) {
                    $getData['seoTag']['data'] = $seoTag->getSeoTagData($getData['seoTagSno'], null, false, ['path' => 'goods/goods_view.php', pageCode => $getData['goodsNo']]);
                }

                echo '<tr>' . chr(10);

                foreach ($setData['fieldCheck'] as $fVal) {

                    if (in_array($fVal, $setData['tagCheck'])) {
                        $getData[$fVal] = gd_htmlspecialchars(gd_htmlspecialchars_stripslashes($getData[$fVal]));
                    }

                    // 상품번호
                    if ($fVal == 'goodsNo') {
                        $className = 'xl31';
                    } else {
                        $className = 'xl24';
                    }

                    if ($fVal == 'goodsDescriptionMobile' && $getData['goodsDescriptionSameFl'] == 'y') {
                        $getData['goodsDescriptionMobile'] = $getData['goodsDescription'];
                    }

                    if ($fVal == 'purchaseNo') {
                        if (!in_array($getData[$fVal], array_keys($purchaseData))) $getData[$fVal] = " ";
                    }

                    if ($fVal == 'detailInfoDelivery' || $fVal == 'detailInfoAS' || $fVal == 'detailInfoRefund' || $fVal == 'detailInfoExchange') {
                        switch ($getData[$fVal . 'Fl']) {
                            case 'no': {
                                $getData[$fVal] = "";
                                break;
                            }
                            case 'direct' : {
                                $getData[$fVal] = gd_htmlspecialchars(gd_htmlspecialchars_stripslashes($getData[$fVal . 'DirectInput']));
                                break;
                            }
                            case 'selection' : {
                                $getData[$fVal] = gd_htmlspecialchars(gd_htmlspecialchars_stripslashes($getData[$fVal]));
                                break;
                            }
                        }
                    }

                    if ($fVal == 'seoTagTitle' || $fVal == 'seoTagAuthor' || $fVal == 'seoTagDescription' || $fVal == 'seoTagKeyword') {
                        $getData[$fVal] = $getData['seoTag']['data'][strtolower(str_replace("seoTag", "", $fVal))];
                    }

                    // 카테고리
                    if ($fVal == 'cateCd') {
                        $strSQL = "SELECT cateCd FROM " . DB_GOODS_LINK_CATEGORY . " WHERE goodsNo = '" . $getData['goodsNo'] . "' AND cateCd != '" . $getData['cateCd'] . "' AND cateLinkFl = 'y' ORDER BY cateCd ASC";
                        $result = $this->db->query($strSQL);
                        $tmp[] = $getData[$fVal];
                        while ($data = $this->db->fetch($result)) {
                            $tmp[] = $data['cateCd'];
                        }
                        $getData[$fVal] = implode("<br style='mso-data-placement:same-cell;'>", ArrayUtils::removeEmpty($tmp));
                        unset($data, $tmp);
                    }

                    // HSCODE
                    if ($fVal == 'hscode' && empty($getData[$fVal]) === false) {
                        $tmpXml = json_decode($getData[$fVal], true);
                        $tmp = [];

                        if ($tmpXml) {
                            foreach ($tmpXml as $tKey => $tVal) {
                                $tmp[] = $tKey . STR_DIVISION . $tVal;
                            }
                            $getData[$fVal] = implode("<br style='mso-data-placement:same-cell;'>", $tmp);
                            unset($tmpXml, $tmp, $tKey, $tVal);
                        }
                    }


                    // 상품필수정보
                    if ($fVal == 'goodsMustInfo' && empty($getData[$fVal]) === false) {
                        $tmpXml = json_decode($getData[$fVal], true);
                        $tmp = [];
                        $tmp1 = [];

                        if ($tmpXml) {

                            foreach ($tmpXml as $tKey => $tVal) {
                                $tmp2 = [];
                                foreach ($tVal as $sKey => $sVal) {
                                    $tmp2[] = implode(STR_DIVISION, $sVal);
                                }
                                $tmp1[] = implode(STR_DIVISION, $tmp2);
                            }
                            $getData[$fVal] = implode("<br style='mso-data-placement:same-cell;'>", $tmp1);
                            unset($tmpXml, $tmp, $tmp1, $tmp2, $tKey, $tVal, $sKey, $sVal);
                        }
                    }


                    // 상품필수정보
                    if ($fVal == 'relationGoodsDate' && empty($getData[$fVal]) === false) {
                        $tmpXml = json_decode(gd_htmlspecialchars_stripslashes(gd_htmlspecialchars_decode($getData[$fVal])), true);
                        $tmp = [];
                        $tmp1 = [];
                        if ($tmpXml) {

                            foreach ($tmpXml as $tKey => $tVal) {
                                $tmp2 = [];

                                $tmp1[] = $tKey . STR_DIVISION . implode(STR_DIVISION, $tVal);
                            }
                            $getData[$fVal] = implode('<br />', $tmp1);
                            unset($tmpXml, $tmp, $tmp1, $tmp2, $tKey, $tVal, $sKey, $sVal);
                        }
                    }

                    //KC인증정보
                    if ($fVal == 'kcmarkFl' || $fVal == 'kcmarkNo' || $fVal == 'kcmarkDivFl' || $fVal == 'kcmarkDt') {
                        if ($kcmarkInfoArr['kcmarkFl'] != 'y') {
                            $getData[$fVal] = '';
                            $getData['kcmarkFl'] = 'n';
                        } else {
                            if($kcmarkInfoArr['kcmarkDivFl'] == 'kcCd04' || $kcmarkInfoArr['kcmarkDivFl'] == 'kcCd05' || $kcmarkInfoArr['kcmarkDivFl'] == 'kcCd06'){
                                $getData[$fVal] = $kcmarkInfoArr[$fVal];
                            }else{
                                $kcmarkInfoArr['kcmarkDt'] = '';
                                $getData[$fVal] = $kcmarkInfoArr[$fVal];
                            }
                        }
                    }

                    // 추가상품
                    if ($fVal == 'addGoods' && empty($getData[$fVal]) === false) {
                        $tmpXml = json_decode(gd_htmlspecialchars_stripslashes(gd_htmlspecialchars_decode($getData[$fVal])), true);
                        $tmp = [];
                        if (is_array($tmpXml)) {
                            foreach ($tmpXml as $k => $v) {
                                $tmp[] = $v['title'] . STR_DIVISION . $v['mustFl'] . STR_DIVISION . implode(INT_DIVISION, $v['addGoods']);
                            }
                            $getData[$fVal] = implode("<br style='mso-data-placement:same-cell;'>", $tmp);
                        }

                        unset($tmp, $tmpXml);
                    }

                    // 추가항목
                    if ($fVal == 'addInfo') {
                        $strSQL = "SELECT infoTitle, infoValue FROM " . DB_GOODS_ADD_INFO . " WHERE goodsNo = '" . $getData['goodsNo'] . "' ORDER BY sno ASC";
                        $result = $this->db->query($strSQL);
                        $tmp = [];
                        while ($data = $this->db->fetch($result)) {
                            $tmp[] = $data['infoTitle'] . STR_DIVISION . $data['infoValue'];
                        }
                        $getData[$fVal] = implode("<br style='mso-data-placement:same-cell;'>", $tmp);
                        unset($data, $tmp);
                    }

                    // 지역별 배송비
                    if ($fVal == 'deliveryAddArea') {
                        $getData[$fVal] = gd_trim($getData[$fVal]);
                        if ($getData[$fVal] == STR_DIVISION) {
                            $getData[$fVal] = '';
                        } else {
                            $getData[$fVal] = str_replace(MARK_DIVISION, "<br style='mso-data-placement:same-cell;'>", $getData[$fVal]);
                        }
                    }

                    // 대표색상
                    if ($fVal == 'goodsColor') {
                        $getData[$fVal] = gd_trim($getData[$fVal]);
                        if ($getData[$fVal]) {
                            $goodsColor = explode(STR_DIVISION, $getData[$fVal]);
                            foreach ($goodsColor as $cKey => $cVal) {
                                if (!in_array($cVal, $goodsColorList)) {
                                    unset($goodsColor[$cKey]);
                                }
                            }
                            $getData[$fVal] = implode(STR_DIVISION, $goodsColor);
                            unset($goodsColor);
                        }
                    }

                    // 옵션 추가노출
                    if ($getData['optionFl'] == 'y' && $fVal == 'optionIcon') {
                        $optionName = explode(STR_DIVISION, $getData['optionName']);
                        $strSQL = "SELECT " . implode(', ', $arrFieldOptionIcon) . " FROM " . DB_GOODS_OPTION_ICON . " WHERE goodsNo = '" . $getData['goodsNo'] . "' ORDER BY optionNo ASC, sno ASC";
                        $result = $this->db->query($strSQL);
                        $tmpKey = '';
                        $tmp1 = $tmp2 = [];
                        while ($data = $this->db->fetch($result)) {
                            for ($i = 0; $i < count($arrFieldOptionIcon); $i++) {
                                $tmpKey = gd_htmlspecialchars(gd_htmlspecialchars_stripslashes($data[$arrFieldOptionIcon[$i]]));
                                if ($i == 0) {
                                    $tmp1[] = $optionName[$tmpKey];
                                } else {
                                    $tmp1[] = $tmpKey;
                                }
                            }
                            $tmp2[] = implode(STR_DIVISION, $tmp1);
                            unset($tmp1);
                        }
                        $getData[$fVal] = implode("<br style='mso-data-placement:same-cell;'>", $tmp2);
                        unset($data, $tmpKey, $tmp2);
                    }

                    // 텍스트 옵션
                    if ($getData['optionTextFl'] == 'y' && $fVal == 'optionText') {
                        $strSQL = "SELECT " . implode(', ', $arrFieldOptionText) . " FROM " . DB_GOODS_OPTION_TEXT . " WHERE goodsNo = '" . $getData['goodsNo'] . "' ORDER BY sno ASC";
                        $result = $this->db->query($strSQL);
                        $tmp1 = $tmp2 = [];
                        while ($data = $this->db->fetch($result)) {
                            for ($i = 0; $i < count($arrFieldOptionText); $i++) {
                                $tmp1[] = gd_htmlspecialchars(gd_htmlspecialchars_stripslashes($data[$arrFieldOptionText[$i]]));
                            }
                            $tmp2[] = implode(STR_DIVISION, $tmp1);
                            unset($tmp1);
                        }
                        $getData[$fVal] = implode("<br style='mso-data-placement:same-cell;'>", $tmp2);
                        unset($data, $tmp2);
                    }

                    // 이미지명
                    if ($fVal == 'imageName') {
                        $strSQL = "SELECT imageNo, imageKind, imageName FROM " . DB_GOODS_IMAGE . " WHERE goodsNo = '" . $getData['goodsNo'] . "' ORDER BY imageKind , imageNo";
                        $result = $this->db->query($strSQL);
                        $tmpImage = '';
                        $tmp1 = $tmp2 = [];
                        while ($data = $this->db->fetch($result)) {
                            $tmp1[$data['imageKind']][$data['imageNo']] = $data['imageName'];
                        }
                        foreach ($imageKey as $iVal) {
                            if (!empty($tmp1[$iVal])) {
                                $tmp2[] = $iVal . STR_DIVISION . implode(STR_DIVISION, $tmp1[$iVal]);
                            }
                        }
                        $getData[$fVal] = implode("<br style='mso-data-placement:same-cell;'>", $tmp2);
                        unset($data, $tmpImage, $tmp1, $tmp2);
                    }

                    $fbFeed = \App::load('\\Component\\Marketing\\FacebookAd');
                    //페이스북 피드 생성 여부
                    if ($fVal == 'fbVn') {
                        $arrBind = [];
                        $strSQL = "SELECT useFl FROM " . DB_FACEBOOK_GOODS_FEED . " WHERE goodsNo = ? ";
                        $this->db->bind_param_push($arrBind, 's', $getData['goodsNo']);
                        $tmp = $this->db->query_fetch($strSQL, $arrBind, false);
                        $getData[$fVal] = $tmp['useFl'];
                        unset($tmp);
                    }

                    //페이스북 피드 이미지명
                    if ($fVal == 'fbImageName') {
                        $fbImageArr = $fbFeed->getFaceBookGoodsImage($getData['goodsNo']);
                        $getData[$fVal] = implode(STR_DIVISION, $fbImageArr);
                    }

                    // 구매율
                    if ($fVal == 'orderRate') {
                        if ($getData['orderGoodsCnt'] > 0 && $getData['hitCnt'] > 0) {
                            $getData[$fVal] = round(($getData['orderGoodsCnt'] / $getData['hitCnt']) * 100, 2) . "%";
                        } else {
                            $getData[$fVal] = "0%";
                        }
                    }

                    // 후기수
                    if ($fVal == 'reviewCnt') {
                        if (gd_is_plus_shop(PLUSSHOP_CODE_REVIEW) === true) {
                            $getData['reviewCnt'] = ($getData['reviewCnt'] + $getData['plusReviewCnt']);
                        }
                    }

                    //기간제한 아이콘
                    if ($fVal == 'goodsIconCdPeriod') {
                        $strSQL = "SELECT goodsIconCd FROM " . DB_GOODS_ICON . " WHERE goodsNo = '" . $getData['goodsNo'] . "' AND iconKind = 'pe' ORDER BY sno ASC";
                        $result = $this->db->query($strSQL);
                        $tmp = [];
                        while ($data = $this->db->fetch($result)) {
                            $tmp[] = $data['goodsIconCd'];
                        }
                        $getData[$fVal] = implode("||", $tmp);
                        unset($data, $tmp);
                    }

                    //무제한 아이콘
                    if ($fVal == 'goodsIconCd') {
                        $strSQL = "SELECT goodsIconCd FROM " . DB_GOODS_ICON . " WHERE goodsNo = '" . $getData['goodsNo'] . "' AND iconKind = 'un' ORDER BY sno ASC";
                        $result = $this->db->query($strSQL);
                        $tmp = [];
                        while ($data = $this->db->fetch($result)) {
                            $tmp[] = $data['goodsIconCd'];
                        }
                        $getData[$fVal] = implode("||", $tmp);
                        unset($data, $tmp);
                    }

                    //기간제한 아이콘 시작
                    if ($fVal == 'goodsIconStartYmd') {
                        $strSQL = "SELECT goodsIconStartYmd FROM " . DB_GOODS_ICON . " WHERE goodsNo = '" . $getData['goodsNo'] . "' AND iconKind = 'pe' ORDER BY sno ASC";
                        $tmp = $this->db->query_fetch($strSQL, null, false);
                        $getData[$fVal] = $tmp['goodsIconStartYmd'];
                        unset($tmp);
                    }

                    //기간제한 아이콘 끝
                    if ($fVal == 'goodsIconEndYmd') {
                        $strSQL = "SELECT goodsIconEndYmd FROM " . DB_GOODS_ICON . " WHERE goodsNo = '" . $getData['goodsNo'] . "' AND iconKind = 'pe' ORDER BY sno ASC";
                        $tmp = $this->db->query_fetch($strSQL, null, false);
                        $getData[$fVal] = $tmp['goodsIconEndYmd'];
                        unset($tmp);
                    }

                    // 마일리지 설정(지급 방법 선택이 개별설정이면서 대상이 특정 회원인 경우)
                    if($getData['mileageFl'] == 'g' && $getData['mileageGroup'] == 'group'){
                        $strSQL = "SELECT mileageGroupMemberInfo FROM " . DB_GOODS . " WHERE goodsNo = '" . $getData['goodsNo'] ."'";
                        $result = $this->db->query($strSQL);
                        $tmp[] = $getData[$fVal];
                        while ($data = $this->db->fetch($result)) {
                            $mileageGroupMemberInfo = $data['mileageGroupMemberInfo'];
                            $arrMileageGroupMemberInfo = json_decode($mileageGroupMemberInfo, true);
                            $getData['mileageGroupInfo'] = implode('||', $arrMileageGroupMemberInfo['groupSno']);
                            $getData['mileageGoods'] = implode("<br style='mso-data-placement:same-cell;'>", $arrMileageGroupMemberInfo['mileageGoods']);
                            $getData['mileageGoodsUnit'] = implode("<br style='mso-data-placement:same-cell;'>", $arrMileageGroupMemberInfo['mileageGoodsUnit']);
                        }
                    }

                    if($getData['mileageFl'] == 'c'){
                        //$mileageGiveConfig = ComponentUtils::getPolicy('member.mileageGive');
                        //if(empty($mileageGiveConfig['goods']) == true){
                            $getData['mileageGoods'] = '';
                            $getData['mileageGoodsUnit'] = '';
                        //}
                    }

                    $getData['goodsWeight'] = str_replace('.00', '', $getData['goodsWeight']);
                    $getData['goodsVolume'] = str_replace('.00', '', $getData['goodsVolume']);

                    echo '<td class="' . $className . '">' . $getData[$fVal] . '</td>' . chr(10);
                }
                echo '</tr>' . chr(10);
            }
        }

        echo '</table>' . chr(10);

        // 엑셀 하단
        $this->excelFooter;

    }

    /**
     * 상품 엑셀 업로드
     *
     * @author artherot
     * @deprecated 2018-04-20
     * @param string $modDtUse 상품수정일 변경 유무
     * @uses \Bundle\Component\Excel\ExcelGoodsConvert::setExcelGoodsUp()
     */
    public function setExcelGoodsUp($modDtUse = null)
    {
        $excelGoodsConvert = \App::load('Component\\Excel\\ExcelGoodsConvert');
        $excelGoodsConvert->setExcelGoodsUp($modDtUse);
    }

    /**
     * 상품 검색 일괄 수정
     *
     * @param array $arrGoodsNo 상품 검색 테이블 일괄 수정
     *
     * @return
     */
    public function updateGoodsSearch($arrGoodsNo)
    {

        $updateField = [];
        foreach (DBTableField::tableGoodsSearch() as $k => $v) {
            $updateField[] = 'gs.' . $v['val'] . '=' . 'g.' . $v['val'];
        }

        $strSQL = 'UPDATE ' . DB_GOODS_SEARCH . ' gs INNER JOIN ' . DB_GOODS . ' g  ON g.goodsNo = gs.goodsNo SET ' . implode(',', $updateField) . ' WHERE g.goodsNo IN (' . implode(',', $arrGoodsNo) . ')';
        $this->db->query($strSQL);

        return true;
    }

    /**
     * 상품 네이버 업데이트
     *
     * @param      $applyFl
     * @param      $goodsNo
     * @param bool $registerFl
     *
     * @return bool
     */
    public function setGoodsUpdateEp($applyFl, $goodsNo, $registerFl = false)
    {
        if (empty($registerFl)) {
            if ($applyFl === 'y') {
                $arrData['class'] = 'U';
            } else {
                $arrData['class'] = 'D';
            }
        } else {
            $arrData['class'] = 'I';
        }


        $arrData['mapid'] = $goodsNo;

        $arrBind = [];
        $strSQL = "SELECT sno FROM " . DB_GOODS_UPDATET_NAVER . " WHERE  mapid = ? ";
        $this->db->bind_param_push($arrBind, 's', $goodsNo);
        $tmp = $this->db->query_fetch($strSQL, $arrBind, false);
        unset($arrBind);

        if (empty($registerFl) && count($tmp) == 0 || $registerFl) { //신규상품이면
            $arrBind = $this->db->get_binding(DBTableField::tableGoodsUpdateNaver(), $arrData, 'insert', array_keys($arrData));
            $this->db->set_insert_db(DB_GOODS_UPDATET_NAVER, $arrBind['param'], $arrBind['bind'], 'y');
            unset($arrData, $arrBind);
        } else {
            if (\is_array($tmp) && \count($tmp) > 1) {  //중복된 상품번호 삭제
                for ($i = 1, $iMax = \count($tmp); $i < $iMax; $i++) {
                    $arrBind = [];
                    $this->db->bind_param_push($arrBind, 'i', $tmp[$i]['sno']);
                    $this->db->set_delete_db(DB_GOODS_UPDATET_NAVER, ' sno=?', $arrBind);
                }
            }
            $arrBind = $this->db->get_binding(DBTableField::tableGoodsUpdateNaver(), $arrData, 'update', array_keys($arrData));
            $this->db->bind_param_push($arrBind['bind'], 'i', $tmp['sno']);
            $this->db->set_update_db(DB_GOODS_UPDATET_NAVER, $arrBind['param'], 'sno = ?', $arrBind['bind']);
            unset($arrBind);
        }
    }

    /**
     * 회원 엑셀 샘플 다운
     *
     * @author sunny
     */
    public function setExcelMemberSampleDown()
    {
        // 기본 설정
        $excelMember = new ExcelMember();
        $excelField = $excelMember->formatMember();
        $arrField = [
            'text',
            'excelKey',
            'comment',
        ];

        // 전체 항목 선택
        foreach ($excelField as $key => $val) {
            $setData['fieldCheck'][$key] = $val['dbKey'];
        }

        // 엑셀 상단
        echo $this->excelHeader;
        echo '<table border="1">' . chr(10);
        for ($i = 0, $iMax = \count($arrField); $i < $iMax; $i++) {
            echo '<tr>' . chr(10);
            foreach ($excelField as $key => $val) {
                if (in_array($val['dbKey'], $setData['fieldCheck'])) {
                    echo '<td class="title">' . $val[$arrField[$i]] . '</td>' . chr(10);
                }
            }
            echo '</tr>' . chr(10);
        }

        // 샘플 데이타
        foreach ($excelField as $key => $val) {
            $getData[0][$val['dbKey']] = $val['sample'];
        }
        unset($excelField, $arrField);

        // 엑셀 내용
        echo '<tr>' . chr(10);
        foreach ($getData as $sampleData) {
            foreach ($setData['fieldCheck'] as $fVal) {
                // 회원 번호
                if ($fVal == 'memNo') {
                    $className = 'xl31';
                } else {
                    $className = 'xl24';
                }
                echo '<td class="' . $className . '">' . $sampleData[$fVal] . '</td>' . chr(10);
            }
        }
        echo '</tr>' . chr(10);
        echo '</table>' . chr(10);

        // 엑셀 하단
        echo $this->excelFooter;
    }

    /**
     * 회원 엑셀 다운
     *
     * @author sunny
     * @return array $setData 선택된 필드값
     */
    public function setExcelMemberDown($setData)
    {
        // 선택된 필드가 없는 경우
        if (count($setData['fieldCheck']) <= 0) {
            return false;
        }

        // 기본 설정
        $excelMember = new ExcelMember();
        $excelField = $excelMember->formatMember(true);
        $arrField = [
            'text',
            'excelKey',
            'comment',
        ];

        // 전체 다운인 경우 전체 항목 선택
        if ($setData['downType'] == 'all') {
            foreach ($excelField as $key => $val) {
                $setData['fieldCheck'][$key] = $val['dbKey'];
            }
        }

        // 엑셀 상단
        echo $this->excelHeader;
        echo '<table border="1">' . chr(10);
        for ($i = 0, $iMax = \count($arrField); $i < $iMax; $i++) {
            echo '<tr>' . chr(10);
            foreach ($excelField as $key => $val) {
                if (in_array($val['dbKey'], $setData['fieldCheck'])) {
                    echo '<td class="title">' . $val[$arrField[$i]] . '</td>' . chr(10);
                }
            }
            echo '</tr>' . chr(10);
        }

        unset($excelField, $arrField);

        // --- 검색 설정
        $arrBind = $arrWhere = [];
        // $this->setSearchGoods();
        if ($setData['downType'] == 'all') {
            $setData['downRange'] = $setData['downRangeAll'];
            $setData['partStart'] = $setData['partStartAll'];
            $setData['partCnt'] = $setData['partCntAll'];
        } else {

            $member = \App::load('\\Component\\Member\\MemberAdmin');
            $tmp = $member->searchMemberWhere($setData);
            $arrBind = $tmp['arrBind'];
            $arrWhere = $tmp['arrWhere'];
        }

        $this->db->strField = "*";
        $this->db->strWhere = implode(" and ", $arrWhere);
        $this->db->strOrder = $setData['orderBy'];
        if ($setData['downRange'] == 'part') {
            $this->db->strLimit = "?,?";
            $this->db->bind_param_push($arrBind, 'i', ($setData['partStart'] - 1));
            $this->db->bind_param_push($arrBind, 'i', $setData['partCnt']);
        }

        $arrBind = count($arrBind) ? $arrBind : null;
        $query = $this->db->query_complete();
        $strSQL = 'SELECT ' . array_shift($query) . ' FROM ' . DB_MEMBER . ' ' . implode(' ', $query);
        $data = $this->db->query_fetch($strSQL, $arrBind);

        // 엑셀 내용
        if (isset($data) && is_array($data)) {
            foreach ($data as $getData) {
                // 데이타 처리
                $getData = gd_htmlspecialchars(gd_htmlspecialchars_stripslashes($getData));

                echo '<tr>' . chr(10);
                foreach ($setData['fieldCheck'] as $fVal) {
                    // 회원 번호
                    if ($fVal == 'memNo') {
                        $className = 'xl31';
                    } else {
                        $className = 'xl24';
                    }
                    // 필드값
                    if ($fVal == 'memPwEnc') {
                        $fieldValue = $getData['memPw'];
                    } else {
                        $fieldValue = $getData[$fVal];
                    }
                    echo '<td class="' . $className . '">' . $fieldValue . '</td>' . chr(10);
                }
                echo '</tr>' . chr(10);
            }
        }
        echo '</table>' . chr(10);

        // 엑셀 하단
        echo $this->excelFooter;
    }

    /**
     * 회원정보 수정 이벤트 참여내역 엑셀 다운
     *
     * @param  array $params 검색데이터
     *
     * @return bool
     */
    public function setExcelMemberModifyEventResultDown($params)
    {
        // 선택된 필드가 없는 경우
        if (count($params['eventNo']) <= 0) {
            return false;
        }

        // 엑셀 상단
        echo $this->excelHeader;
        echo '<table border="1">' . chr(10);
        echo '<tr>' . chr(10);
        echo '<td class="title">아이디</td>' . chr(10);
        echo '<td class="title">이름</td>' . chr(10);
        echo '<td class="title">등급</td>' . chr(10);
        echo '<td class="title">참여일시</td>' . chr(10);
        echo '</tr>' . chr(10);

        // 모듈 호출
        $modifyEvent = \App::load('\\Component\\Member\\MemberModifyEvent');
        // 이벤트 참여내역
        $data = $modifyEvent->getMemberModifyEventResult($params);

        // 엑셀 내용
        if (isset($data) && is_array($data)) {
            foreach ($data['data'] as $getData) {
                echo '<tr>' . chr(10);
                echo '<td class="xl24">' . $getData['memId'] . '</td>' . chr(10);
                echo '<td class="xl24">' . $getData['memNm'] . '</td>' . chr(10);
                echo '<td class="xl24">' . $getData['groupNm'] . '</td>' . chr(10);
                echo '<td class="xl24">' . $getData['regDt'] . '</td>' . chr(10);
                echo '</tr>' . chr(10);
            }
        }
        echo '</table>' . chr(10);

        // 엑셀 하단
        echo $this->excelFooter;
    }

    /**
     * 회원가입 이벤트 현황 엑셀 다운로드
     *
     * @param  array $params 검색데이터
     *
     * @return bool
     */
    public function setExcelMemberSimpleJoinEventResultDown($params)
    {
        $groups = \Component\Member\Group\Util::getGroupName();
        // 엑셀 상단
        echo $this->excelHeader;
        echo '<table border="1">' . chr(10);
        echo '<tr>' . chr(10);
        echo '<td class="title">아이디</td>' . chr(10);
        echo '<td class="title">이름</td>' . chr(10);
        echo '<td class="title">등급</td>' . chr(10);
        echo '<td class="title">가입시 제공혜택(쿠폰)</td>' . chr(10);
        echo '<td class="title">가입시 제공혜택(마일리지)</td>' . chr(10);
        echo '<td class="title">회원가입일</td>' . chr(10);
        echo '</tr>' . chr(10);

        // 모듈 호출
        $member = \App::load('\\Component\\Member\\Member');
        // 이벤트 참여내역
        $data = $member->getSimpleJoinLog($params, 6, $params['eventType']);

        // 엑셀 내용
        if (isset($data) && is_array($data)) {
            foreach ($data['data'] as $getData) {
                echo '<tr>' . chr(10);
                echo '<td class="xl24">' . $getData['memId'] . '</td>' . chr(10);
                echo '<td class="xl24">' . $getData['memNm'] . '</td>' . chr(10);
                echo '<td class="xl24">' . $groups[$getData['groupSno']] . '</td>' . chr(10);
                if($getData['appFl'] == 'y') {
                    echo '<td class="xl24">' . str_replace(STR_DIVISION, ' | ', $getData['couponNm']) . '</td>' . chr(10);
                    echo '<td class="xl24">' . gd_money_format($getData['mileage']) . gd_display_mileage_unit() . '</td>' . chr(10);
                } else {
                    echo '<td class="xl24">가입 시 미승인</td>' . chr(10);
                    echo '<td class="xl24">가입 시 미승인</td>' . chr(10);
                }
                echo '<td class="xl24">' . $getData['regDt'] . '</td>' . chr(10);
                echo '</tr>' . chr(10);
            }
        }
        echo '</table>' . chr(10);

        // 엑셀 하단
        echo $this->excelFooter;
    }

    /**
     * 쿠폰 수동 발급 엑셀 샘플 다운
     *
     * @author su
     */
    public function setExcelMemberCouponSampleDown()
    {
        // 엑셀 상단
        echo $this->excelHeader;
        echo '<table border="1">' . chr(10);
        echo '<tr>' . chr(10);
        echo '<td class="title"> ' . __('발급할 회원아이디') . '</td>' . chr(10);
        echo '</tr>' . chr(10);

        // 엑셀 내용
        echo '<tr>' . chr(10);
        echo '<td class="xl24">AAAAA</td>' . chr(10);
        echo '</tr>' . chr(10);
        echo '<tr>' . chr(10);
        echo '<td class="xl24">BBBBB</td>' . chr(10);
        echo '</tr>' . chr(10);
        echo '<tr>' . chr(10);
        echo '<td class="xl24">CCCCC</td>' . chr(10);
        echo '</tr>' . chr(10);
        echo '<tr>' . chr(10);
        echo '<td class="xl24">DDDDD</td>' . chr(10);
        echo '</tr>' . chr(10);
        echo '<tr>' . chr(10);
        echo '<td class="xl24">EEEEE</td>' . chr(10);
        echo '</tr>' . chr(10);
        echo '</table>' . chr(10);

        // 엑셀 하단
        echo $this->excelFooter;
    }

    /**
     * 쿠폰 수동 발급 엑셀 업로드
     *
     * @author su
     */
    public function setExcelMemberCouponUp($arrData, $sSmsFlag = 'n')
    {
        $excelChk = true;
        $failMsg = '';
        if (Request::files()->get('excel')['error'] > 0) {
            $failMsg .= __('엑셀 화일이 존재하지 않습니다. 다운 로드 받으신 엑셀파일을 이용해서 "Excel 97-2003 통합문서" 로 저장이 된 엑셀 파일만 가능합니다.');
            $excelChk = false;
        }

        $data = new SpreadsheetExcelReader();
        $data->setOutputEncoding('CP949');
        $chk = $data->read(Request::files()->get('excel')['tmp_name']);

        if ($chk === false) {
            $failMsg .= __('엑셀 화일을 확인해 주세요. 다운 로드 받으신 엑셀파일을 이용해서 "Excel 97-2003 통합문서" 로 저장이 된 엑셀 파일만 가능합니다.');
            $excelChk = false;
        }

        // 반드시 Excel 97-2003 통합문서로 저장이 되어야 하며, 1번째 줄은 설명, 2번째 줄은 memberId
        if ($data->sheets[0]['numRows'] < 2) {
            $failMsg .= __('엑셀 화일을 확인해 주세요. 엑셀 데이타가 존재하지 않습니다. 데이타는 2번째 줄부터 작성을 하셔야 합니다.');
            $excelChk = false;
        }

        echo $this->excelHeader;

        // 오류가 있는 경우 실패 메시지가 엑셀로 저장이 됨
        if ($excelChk === false) {
            echo '<table border="1">' . chr(10);
            echo '<tr>' . chr(10);
            echo '<td>' . $failMsg . '</td>' . chr(10);
            echo '</tr>' . chr(10);
            echo '</table>' . chr(10);

            return false;
        }

        echo '<table border="1">' . chr(10);
        echo '<tr>' . chr(10);
        echo '<td>' . __('번호') . '</td><td>' . __('회원 번호') . '</td><td>' . __('아이디') . '</td><td>' . __('상태') . '</td>' . chr(10);
        echo '</tr>' . chr(10);

        // sms발송 설정
        if ($sSmsFlag == 'y') {
            $logger = \App::getInstance('logger');
            $smsAuto = \App::load(\Component\Sms\SmsAuto::class);
            $couponAdmin = \App::load(\Component\Coupon\CouponAdmin::class);
            $couponInfo = $couponAdmin->getCouponInfo($arrData['couponNo'], '*');
        }

        // 엑셀 데이터를 추출 후 가공
        for ($i = 2; $i <= $data->sheets[0]['numRows']; $i++) {
            $memberId = trim(iconv('EUC-KR', 'UTF-8', gd_isset($data->sheets[0]['cells'][$i][1]))); // 엑셀 데이타를 UFT-8 로 변경후 배열에 저장
            $member = ['mallSno' => DEFAULT_MALL_NUMBER,];
            // memNo 가 있는지를 체크
            if (empty($data->sheets[0]['cells'][$i][1]) === true) {
                $memNo = false;
            } else {
                $member = MemberDAO::getInstance()->selectMemberByOne($memberId, 'memId');
                $memNo = $member['memNo'];
            }

            echo '<tr>' . chr(10);
            echo '<td>' . ($i - 1) . '</td>' . chr(10);
            echo '<td>' . $memNo . '</td>' . chr(10);
            echo '<td>' . $memberId . '</td>' . chr(10);

            if ($memNo === false || $memNo < 1) {
                echo '<td>' . __('회원정보 없음') . '</td>' . chr(10);
            } elseif ($member['mallSno'] == DEFAULT_MALL_NUMBER) {
                unset($arrData['memNo']);
                $arrData['memNo'] = $memNo;
                // 저장
                $arrBind = $this->db->get_binding(DBTableField::tableMemberCoupon(), $arrData, 'insert', array_keys($arrData), ['memberCouponNo']);
                $this->db->set_insert_db(DB_MEMBER_COUPON, $arrBind['param'], $arrBind['bind'], 'y');

                // sms발송
                if ($sSmsFlag == 'y') {
                    $member = ['smsFl' => 'n'];
                    if ($arrData['memNo'] > 1) {
                        $member = \Component\Member\MemberDAO::getInstance()->selectMemberByOne($arrData['memNo']);
                    } else {
                        $logger->info('Send coupon auto sms. not found member number.');
                    }
                    if ($couponInfo) {
                        if ($member['smsFl'] == 'y') {
                            $smsAuto->setSmsAutoCodeType(Code::COUPON_MANUAL);
                            $smsAuto->setSmsType(SmsAutoCode::PROMOTION);
                            $smsAuto->setReceiver($member);
                            $smsAuto->setReplaceArguments(
                                [
                                    'name'       => $member['memNm'],
                                    'CouponName' => $couponInfo['couponNm'],
                                    'rc_memid'   => $member['memId']
                                ]
                            );
                            $smsAuto->autoSend();
                        } else {
                            $logger->info(sprintf('Disallow sms receiving. memNo[%s], smsFl [%s]', $member['memNo'], $member['smsFl']));
                        }
                    }
                }

                $couponAdmin = \App::load('\\Component\\Coupon\\CouponAdmin');
                $couponAdmin->setCouponMemberSaveCount($arrData['couponNo']);

                echo '<td>' . __('발급됨') . '</td>' . chr(10);
            } else {
                echo '<td>' . __('기준몰 회원만 쿠폰 발급이 가능합니다.') . '</td>' . chr(10);
            }
            echo '</tr>' . chr(10);
        }
        echo '</table>' . chr(10);

        // 엑셀 하단
        echo $this->excelFooter;

        return true;
    }

    /**
     * 페이퍼쿠폰 코드 엑셀 샘플 다운
     *
     * @author su
     */
    public function setCouponOfflineExcelCodeSampleDown()
    {
        // 엑셀 상단
        echo $this->excelHeader;
        echo '<table border="1">' . chr(10);
        echo '<tr>' . chr(10);
        echo '<td class="title">' . __('등록할 인증번호') . '</td>' . chr(10);
        echo '</tr>' . chr(10);

        // 엑셀 내용
        echo '<tr>' . chr(10);
        echo '<td class="xl24">AAAAAAAAAAAA</td>' . chr(10);
        echo '</tr>' . chr(10);
        echo '<tr>' . chr(10);
        echo '<td class="xl24">BBBBBBBBBBBB</td>' . chr(10);
        echo '</tr>' . chr(10);
        echo '<tr>' . chr(10);
        echo '<td class="xl24">CCCCCCCCCCCC</td>' . chr(10);
        echo '</tr>' . chr(10);
        echo '<tr>' . chr(10);
        echo '<td class="xl24">DDDDDDDDDDDD</td>' . chr(10);
        echo '</tr>' . chr(10);
        echo '<tr>' . chr(10);
        echo '<td class="xl24">EEEEEEEEEEEE</td>' . chr(10);
        echo '</tr>' . chr(10);
        echo '</table>' . chr(10);

        // 엑셀 하단
        echo $this->excelFooter;
    }

    /**
     * 페이퍼쿠폰 코드 엑셀 업로드
     * 엑셀업로드 결과를 엑셀파일로 다운해주는 것이었으나 기획요청에 의해 간략 결과 보여줌
     * 엑셀내용을 변수에 담아 반환 해준 후 table을 만들어 excel 추출 (table2excel.js 사용)
     *
     * @author su
     */
    public function setCouponOfflineExcelCodeUp($arrData)
    {
        $result['false'] = 0;
        $result['true'] = 0;
        $result['total'] = 0;
        $excelChk = true;
        $failMsg = '';
        $excelContent = '';

        if (Request::files()->get('excel')['error'] > 0) {
            $failMsg = __('엑셀 화일이 존재하지 않습니다. 다운 로드 받으신 엑셀파일을 이용해서 "Excel 97-2003 통합문서" 로 저장이 된 엑셀 파일만 가능합니다.');
            $excelChk = false;
        }

        $data = new SpreadsheetExcelReader();
        $data->setOutputEncoding('CP949');
        $chk = $data->read(Request::files()->get('excel')['tmp_name']);

        if ($chk === false && $excelChk != false) {
            $failMsg = __('엑셀 화일을 확인해 주세요. 다운 로드 받으신 엑셀파일을 이용해서 "Excel 97-2003 통합문서" 로 저장이 된 엑셀 파일만 가능합니다.');
            $excelChk = false;
        }

        // 반드시 Excel 97-2003 통합문서로 저장이 되어야 하며, 1번째 줄은 설명, 2번째 줄은 couponOfflineCode
        if ($data->sheets[0]['numRows'] < 2 && $excelChk != false) {
            $failMsg = __('엑셀 화일을 확인해 주세요. 엑셀 데이타가 존재하지 않습니다. 데이타는 2번째 줄부터 작성을 하셔야 합니다.');
            $excelChk = false;
        }

        //        $excelContent .= $this->excelHeader;

        // 오류가 있는 경우 실패 메시지가 엑셀로 저장이 됨
        if ($excelChk === false) {
            $excelContent .= '<table border="1">' . chr(10);
            $excelContent .= '<tr>' . chr(10);
            $excelContent .= '<td>' . $failMsg . '</td>' . chr(10);
            $excelContent .= '</tr>' . chr(10);
            $excelContent .= '</table>' . chr(10);

            $result['content'] = $excelContent;

            return $result;
        }

        $excelContent .= '<table border="1">' . chr(10);
        $excelContent .= '<tr>' . chr(10);
        $excelContent .= '<td>' . __('번호') . '</td><td>' . __('인증 번호') . '</td><td>' . __('상태') . '</td>' . chr(10);
        $excelContent .= '</tr>' . chr(10);

        $couponAdmin = \App::load('\\Component\\Coupon\\CouponAdmin');
        // 엑셀 데이터를 추출 후 가공
        for ($i = 2; $i <= $data->sheets[0]['numRows']; $i++) {
            $couponOfflineCodeChk = true;
            $couponOfflineMsg = '';
            $couponOfflineCode = trim(iconv('EUC-KR', 'UTF-8', gd_isset($data->sheets[0]['cells'][$i][1]))); // 엑셀 데이타를 UFT-8 로 변경후 배열에 저장
            // $couponOfflineCode 가 있는지를 체크
            if (empty($data->sheets[0]['cells'][$i][1]) === true) {
                $couponOfflineCodeChk = false;
                $couponOfflineMsg = __('인증번호 확인 필요');
            } else if (strlen($data->sheets[0]['cells'][$i][1]) > 12) {
                $couponOfflineCodeChk = false;
                $couponOfflineMsg = __('인증번호는 12자 이하');
            } else if (strlen($data->sheets[0]['cells'][$i][1]) < 8) {
                $couponOfflineCodeChk = false;
                $couponOfflineMsg = __('인증번호는 8자 이상');
            } else if ($couponAdmin->checkOfflineCode($couponOfflineCode)) {
                $couponOfflineCodeChk = false;
                $couponOfflineMsg = __('인증번호 존재');
            }

            $excelContent .= '<tr>' . chr(10);
            $excelContent .= '<td>' . ($i - 1) . '</td>' . chr(10);
            $excelContent .= '<td>' . $couponOfflineCode . '</td>' . chr(10);

            if ($couponOfflineCodeChk === false) {
                $excelContent .= '<td>' . $couponOfflineMsg . '</td>' . chr(10);
                $result['false']++;
            } else {
                // 인증번호 저장
                $arrBind['param'] = "couponOfflineCode, couponOfflineCodeUser, couponNo, couponOfflineCodeSaveType, couponOfflineInsertAdminId,managerNo";
                $this->db->bind_param_push($arrBind['bind'], 's', $couponOfflineCode);
                $this->db->bind_param_push($arrBind['bind'], 's', $couponOfflineCode);
                $this->db->bind_param_push($arrBind['bind'], 'i', $arrData['couponNo']);
                $this->db->bind_param_push($arrBind['bind'], 's', 'n');
                $this->db->bind_param_push($arrBind['bind'], 's', $arrData['couponOfflineInsertAdminId']);
                $this->db->bind_param_push($arrBind['bind'], 'i', $arrData['managerNo']);
                $this->db->set_insert_db(DB_COUPON_OFFLINE_CODE, $arrBind['param'], $arrBind['bind'], 'y');
                unset($arrBind);
                $excelContent .= '<td>' . __('생성 됨') . '</td>' . chr(10);
                $result['true']++;
            }
            $excelContent .= '</tr>' . chr(10);
        }
        $excelContent .= '</table>' . chr(10);

        // 엑셀 하단
        //        $excelContent .= $this->excelFooter;

        $result['total'] = $result['false'] + $result['true'];
        $result['content'] = $excelContent;

        return $result;
    }

    /**
     * 업로드한 엑셀 파일 오류 체크
     *
     * @return bool
     */
    public function hasError()
    {
        $request = \App::getInstance('request');
        $excel = $request->files()->get('excel');
        $hasError = $excel['error'] > 0;
        if ($hasError) {
            $logger = \App::getInstance('logger');
            $logger->warning('upload file has error. file name is ' . $excel['tmp_name'] . ' error is ' . $excel['error']);
        }

        return $hasError;
    }

    public function read()
    {
        $request = \App::getInstance('request');
        $excel = $request->files()->get('excel');
        $this->excelReader = new SpreadsheetExcelReader();
        $this->excelReader->setOutputEncoding('CP949');
        $read = ($this->excelReader->read($excel['tmp_name']) !== false);
        if (!$read) {
            $logger = \App::getInstance('logger');
            $logger->warning('upload file read error. file name is ' . $excel['tmp_name'] . ' error is ' . $excel['error']);
        }

        return $read;
    }

    public function hasData()
    {
        return $this->excelReader->sheets[0]['numRows'] > 3;
    }

    public function createBodyByReadError()
    {
        $failMsg = __('엑셀 파일이 존재하지 않습니다. 다운 로드 받으신 엑셀파일을 이용해서 "Excel 97-2003 통합문서" 로 저장이 된 엑셀 파일만 가능합니다.!');

        $this->excelBody = [];
        $this->excelBody[] = '<table border="1">' . chr(10);
        $this->excelBody[] = '<tr>' . chr(10);
        $this->excelBody[] = '<td>' . $failMsg . '</td>' . chr(10);
        $this->excelBody[] = '</tr>' . chr(10);
        $this->excelBody[] = '</table>' . chr(10);
    }

    public function createDataError()
    {
        $failMsg = __('엑셀 파일을 확인해 주세요. 엑셀 데이타가 존재하지 않습니다. 데이타는 4번째 줄부터 작성을 하셔야 합니다.');

        $this->excelBody = [];
        $this->excelBody[] = '<table border="1">' . chr(10);
        $this->excelBody[] = '<tr>' . chr(10);
        $this->excelBody[] = '<td>' . $failMsg . '</td>' . chr(10);
        $this->excelBody[] = '</tr>' . chr(10);
        $this->excelBody[] = '</table>' . chr(10);
    }

    public function createBodyByError()
    {
        $failMsg = __('엑셀 파일이 존재하지 않습니다. 다운 로드 받으신 엑셀파일을 이용해서 "Excel 97-2003 통합문서" 로 저장이 된 엑셀 파일만 가능합니다.');

        $this->excelBody = [];
        $this->excelBody[] = '<table border="1">' . chr(10);
        $this->excelBody[] = '<tr>' . chr(10);
        $this->excelBody[] = '<td>' . $failMsg . '</td>' . chr(10);
        $this->excelBody[] = '</tr>' . chr(10);
        $this->excelBody[] = '</table>' . chr(10);
    }

    public function createBodyByMessage($message = 'response message')
    {
        $this->excelBody = [];
        $this->excelBody[] = '<table border="1">' . chr(10);
        $this->excelBody[] = '<tr>' . chr(10);
        $this->excelBody[] = '<td>' . $message . '</td>' . chr(10);
        $this->excelBody[] = '</tr>' . chr(10);
        $this->excelBody[] = '</table>' . chr(10);
    }

    public function resetExcelCode(array $fields)
    {
        foreach ($fields as $key => $val) {
            $this->fields[$val['excelKey']] = $val['dbKey'];
            $this->dbNames[$val['dbKey']] = $val['dbName'];
            $this->fieldTexts[$val['dbKey']] = $val['text'];
        }

        return [
            $this->fields,
            $this->dbNames,
            $this->fieldTexts,
        ];
    }

    /**
     * @return SpreadsheetExcelReader
     */
    public function getExcelReader()
    {
        return $this->excelReader;
    }

    /**
     * @param SpreadsheetExcelReader $excelReader
     */
    public function setExcelReader($excelReader)
    {
        $this->excelReader = $excelReader;
    }

    public function __call($name, $arguments)
    {
        if (strpos($name, 'Wrapper') > 1) {
            $realName = str_replace('Wrapper', '', $name);
            if (method_exists($this, $realName)) {
                return call_user_func_array([$this, $realName], $arguments);
            }
        }
    }

    /**
     * 엑셀 출력
     *
     */
    protected function printExcel()
    {
        echo $this->excelHeader;
        echo join('', $this->excelBody);
        echo $this->excelFooter;
    }

    /**
     * makeFile
     *
     * @return bool
     */
    protected function makeFile()
    {
        $output = $this->excelHeader . join('', $this->excelBody) . $this->excelFooter;
        $filePath = \App::getInstance('user.path')->data('etc', 'excel_upload_result.xls')->getRealPath();
        $fileHandler = \App::getInstance('file');
        $fileHandler->write($filePath, $output);

        return $fileHandler->isExists($filePath);
    }

    /**
     * 상품 번호를 Goods 테이블에 저장
     *
     * @author artherot
     * @return array 저장된 상품 번호
     */
    private function doGoodsNoInsert()
    {
        $strSQL = 'SELECT if(max(goodsNo) > 0, (max(goodsNo) + 1), ' . DEFAULT_CODE_GOODSNO . ') as new FROM ' . DB_GOODS;
        $goodsNo = $this->db->fetch($this->db->query($strSQL));

        //기존 상품 코드 있는 경우 가지고 와서 비교 후 상품 코드 정의. 파일 상품 코드가 클 경우 파일 상품 코드+1
        $lastGoodsNo = \FileHandler::read(\UserFilePath::get('config', 'goods'));
        if ($lastGoodsNo - $goodsNo['new'] >= 0) {
            $goodsNo['new'] = $lastGoodsNo + 1;
        }

        $this->db->set_insert_db(DB_GOODS, 'goodsNo', $goodsNo['new'], null);
        if ($this->goodsDivisionFl) {
            $this->db->set_insert_db(DB_GOODS_SEARCH, 'goodsNo', $goodsNo['new'], null);
        }

        //최종 상품 코드 파일 저장
        \FileHandler::write(\UserFilePath::get('config', 'goods'), $goodsNo['new']);

        return $goodsNo['new'];
    }

    /**
     * 상품 번호를 체크
     *
     * @author artherot
     *
     * @param integer $goodsNo goodsNo 값
     *
     * @return boolean
     */
    private function doGoodsnoCheck($goodsNo = null)
    {
        // 상품 번호의 자리수가 기본과 같다면...
        //if (strlen(DEFAULT_CODE_GOODSNO) === strlen($goodsNo)) {
        if (strlen(DEFAULT_CODE_GOODSNO) >= strlen($goodsNo)) {
            $strSQL = 'SELECT count(goodsNo) FROM ' . DB_GOODS . ' WHERE goodsNo = \'' . $goodsNo . '\'';
            list($dataCnt) = $this->db->fetch($strSQL, 'row');

            if ($dataCnt == 1) {
                return true;
            } else {
                return false;
            }
        } else {
            return false;
        }
    }

    /**
     * 엑셀 상품 데이타를 디비에 처리하기
     *
     * @author artherot
     *
     * @param array   $getData   데이타 배열값
     * @param integer $goodsNo   goodsNo 값
     * @param string  $tableName 테이블명
     *
     * @return array 저장된 상품 번호
     */
    private function doGoodsDataHandle($getData, $goodsNo, $tableField, $tableName)
    {
        if ($tableField == 'tableGoodsGlobal') {
            foreach ($getData as $key => $val) {
                if ($val['dbProc'] == 'same') {
                    continue;
                }
                if ($val['dbProc'] == 'delete') {
                    $arrBind = [
                        'ii',
                        $val['mallSno'],
                        $val['goodsNo'],
                    ];
                    $this->db->set_delete_db($tableName, 'mallSno = ? AND goodsNo = ?', $arrBind);
                    continue;
                }

                if ($val['dbProc'] == 'insert') {
                    $arrBind = $this->db->get_binding(DBTableField::$tableField(), $val, $val['dbProc']);
                    $this->db->set_insert_db($tableName, $arrBind['param'], $arrBind['bind'], 'y');
                    unset($arrBind);
                    continue;
                }
                if ($val['dbProc'] == 'update') {
                    $arrBind = $this->db->get_binding(DBTableField::getBindField($tableField, array_keys($getData[0])), $val, $val['dbProc']);
                    $this->db->bind_param_push($arrBind['bind'], 'i', $goodsNo);
                    $this->db->bind_param_push($arrBind['bind'], 'i', $val['mallSno']);
                    $this->db->set_update_db($tableName, $arrBind['param'], 'goodsNo = ? AND mallSno = ?', $arrBind['bind']);
                    unset($arrBind);
                    continue;
                }
            }
        } else {
            foreach ($getData as $key => $val) {
                if ($val['dbProc'] == 'same') {
                    continue;
                }
                if ($val['dbProc'] == 'delete') {
                    $arrBind = [
                        'i',
                        $val['sno'],
                    ];
                    $this->db->set_delete_db($tableName, 'sno = ?', $arrBind);
                    continue;
                }

                if ($val['dbProc'] == 'insert') {
                    $arrBind = $this->db->get_binding(DBTableField::$tableField(), $val, $val['dbProc']);
                    $this->db->set_insert_db($tableName, $arrBind['param'], $arrBind['bind'], 'y');
                    unset($arrBind);
                    continue;
                }
                if ($val['dbProc'] == 'update') {
                    $arrBind = $this->db->get_binding(DBTableField::getBindField($tableField, array_keys($getData[0])), $val, $val['dbProc']);
                    $this->db->bind_param_push($arrBind['bind'], 'i', $goodsNo);
                    $this->db->bind_param_push($arrBind['bind'], 'i', $val['sno']);
                    $this->db->set_update_db($tableName, $arrBind['param'], 'goodsNo = ? AND sno = ?', $arrBind['bind']);
                    unset($arrBind);
                    continue;
                }
            }
        }

    }

    /**
     * 상품 브랜드 링크
     *
     * @author artherot
     *
     * @param array   $linkData    상품 카테고리 링크 값
     * @param integer $goodsNo     goodsNo 값
     * @param boolean $goodsInsert insert 여부
     *
     * @return array 저장된 상품 번호
     */
    private function setGoodsLinkBrand($brandData, $goodsNo, $goodsInsert)
    {
        if (empty($brandData)) {
            return [];
        }

        // 부모 브랜드 체크
        $newCate = [];
        $length = strlen($brandData);
        for ($i = 1; $i <= ($length / DEFAULT_LENGTH_BRAND); $i++) {
            $tmp = substr($brandData, 0, ($i * DEFAULT_LENGTH_BRAND));
            if ($tmp != $brandData) {
                $newCate[$tmp] = 'n';
            }
            $setData[] = $tmp;
        }
        sort($setData, SORT_STRING);

        // 카테고리 배열 세팅
        foreach ($setData as $key => $val) {
            $getData[$key]['cateCd'] = $val;
            $getData[$key]['cateLinkFl'] = gd_isset($newCate[$val], 'y');
        }

        // 추가되는 카테고리의 정렬 순서 세팅
        $strSQL = "SELECT IF(MAX(glb.goodsSort) > 0, (MAX(glb.goodsSort) + 1), 1) AS sort,MIN(glb.goodsSort) - 1 as reSort, glb.cateCd,cb.sortAutoFl,cb.sortType  FROM " . DB_GOODS_LINK_BRAND . " AS glb INNER JOIN " . DB_CATEGORY_BRAND . " AS cb ON cb.cateCd = glb.cateCd WHERE glb.cateCd IN  ('" . implode('\',\'', $setData) . "') GROUP BY glb.cateCd";
        $result = $this->db->query($strSQL);
        while ($data = $this->db->fetch($result)) {
            if ($data['sortAutoFl'] == 'y') $getSort[$data['cateCd']] = 0;
            else {
                if ($data['sortType'] == 'bottom') $getSort[$data['cateCd']] = $data['reSort'];
                else $getSort[$data['cateCd']] = $data['sort'];
            }
        }

        // 수정이거나 삭제가 아닌경우
        if ($goodsInsert === false) {
            // 기존 카테고리 링크 정보 가져오기
            $strSQL = "SELECT goodsSort, cateCd FROM " . DB_GOODS_LINK_BRAND . " WHERE goodsNo = '" . $goodsNo . "'";
            $result = $this->db->query($strSQL);
            while ($data = $this->db->fetch($result)) {
                $cateSort[$data['cateCd']] = $data['goodsSort'];
            }
        }

        // 카테고리 배열 완료
        foreach ($getData as $key => $val) {
            $getData[$key]['goodsSort'] = gd_isset($cateSort[$val['cateCd']], $getSort[$val['cateCd']]);
        }

        return $getData;
    }

    /**
     * 상품 카테고리 링크
     *
     * @author artherot
     *
     * @param array   $linkData    상품 카테고리 링크 값
     * @param integer $goodsNo     goodsNo 값
     * @param boolean $goodsInsert insert 여부
     *
     * @return array 저장된 상품 번호
     */
    private function setGoodsLinkCategory($linkData, $goodsNo, $goodsInsert)
    {
        if (empty($linkData)) {
            return [];
        }

        // 부모 카테고리 체크
        $newCate = [];
        foreach ($linkData as $key => $val) {
            $length = strlen($val);
            for ($i = 1; $i < ($length / DEFAULT_LENGTH_CATE); $i++) {
                $tmp = substr($val, 0, ($i * DEFAULT_LENGTH_CATE));
                if (!in_array($tmp, $linkData)) {
                    $newCate[$tmp] = 'n';
                    $linkData[] = $tmp;
                }
            }
        }
        sort($linkData, SORT_STRING);

        // 카테고리 배열 세팅
        foreach ($linkData as $key => $val) {
            $getData[$key]['cateCd'] = $val;
            $getData[$key]['cateLinkFl'] = gd_isset($newCate[$val], 'y');
            $tmpCate[$key] = $val;
        }

        // 추가되는 카테고리의 정렬 순서 세팅
        $strSQL = "SELECT IF(MAX(glc.goodsSort) > 0, (MAX(glc.goodsSort) + 1), 1) AS sort,MIN(glc.goodsSort) - 1 as reSort, glc.cateCd,cg.sortAutoFl,cg.sortType FROM " . DB_GOODS_LINK_CATEGORY . " AS glc INNER JOIN " . DB_CATEGORY_GOODS . " AS cg ON cg.cateCd = glc.cateCd WHERE glc.cateCd IN  ('" . implode('\',\'', $tmpCate) . "') GROUP BY glc.cateCd";
        $result = $this->db->query($strSQL);
        while ($data = $this->db->fetch($result)) {
            if ($data['sortAutoFl'] == 'y') $getSort[$data['cateCd']] = 0;
            else {
                if ($data['sortType'] == 'bottom') $getSort[$data['cateCd']] = $data['reSort'];
                else $getSort[$data['cateCd']] = $data['sort'];
            }
        }

        // 수정이거나 삭제가 아닌경우
        if ($goodsInsert === false) {
            // 기존 카테고리 링크 정보 가져오기
            $strSQL = "SELECT goodsSort, cateCd FROM " . DB_GOODS_LINK_CATEGORY . " WHERE goodsNo = '" . $goodsNo . "'";
            $result = $this->db->query($strSQL);
            while ($data = $this->db->fetch($result)) {
                $cateSort[$data['cateCd']] = $data['goodsSort'];
            }
        }

        // 카테고리 배열 완료
        foreach ($getData as $key => $val) {
            $getData[$key]['goodsSort'] = gd_isset($cateSort[$val['cateCd']], $getSort[$val['cateCd']]);
        }

        return $getData;
    }

    /**
     * 상품 정보 DB 처리
     *
     * @author artherot
     *
     * @param array   $goodsData   처리할 정보
     * @param integer $goodsNo     goodsNo 값
     * @param boolean $goodsInsert insert 여부
     * @param string  $tableField  디비 필드 함수명
     * @param string  $tableName   디비 이름
     * @param string  $strOrderBy  정렬 방식
     *
     * @return array 저장된 상품 번호
     */
    private function setGoodsData($goodsData, $goodsNo, $goodsInsert, $tableField, $tableName, $strOrderBy = null)
    {
        $dataDelete = false;
        if (empty($goodsData)) {
            $dataDelete = true;
        } else {
            $dataKey = 0;
            $chkKey = array_keys($goodsData[$dataKey]);
        }
        if ($tableField == "tableGoodsGlobal") $snoName = "mallSno";
        else $snoName = "sno";

        // insert, delete가 아닌경우.
        if ($goodsInsert === false) {
            if ($dataDelete === false) {
                // goodsNo 의 상품 추가 정보를 불러옴
                //$arrField = DBTableField::setTableField($tableField);
                $arrField = $chkKey; //엑셀 데이터 비교시 기존 내용만 비교할수 있도록 키값변경
                if (!in_array('goodsNo', $arrField)) $arrField[] = 'goodsNo';

                if (!is_null($strOrderBy)) {
                    $strOrderBy = ' ORDER BY ' . $strOrderBy;
                }
                if ($tableField == "tableGoodsGlobal") $strSQL = "SELECT  mallSno," . implode(', ', $arrField) . " FROM " . $tableName . " WHERE goodsNo = '" . $goodsNo . "'" . $strOrderBy;
                else $strSQL = "SELECT sno, " . implode(', ', $arrField) . " FROM " . $tableName . " WHERE goodsNo = '" . $goodsNo . "'" . $strOrderBy;
                $result = $this->db->query($strSQL);


                while ($data = $this->db->fetch($result)) {
                    // 엑셀 데이타와 비교
                    $dataUpdate = false;
                    $dataDeleteChk = true;
                    foreach ($chkKey as $key => $val) {
                        $goodsData[$dataKey][$chkKey[$key]] = gd_isset($goodsData[$dataKey][$chkKey[$key]]);
                        if (is_null($goodsData[$dataKey][$chkKey[$key]])) {
                            if ($dataDeleteChk === true) {
                                $data['dbProc'] = 'delete';
                            }
                        } else {
                            $dataDeleteChk = false;
                            if ($goodsData[$dataKey][$chkKey[$key]] == gd_htmlspecialchars(gd_htmlspecialchars_stripslashes($data[$chkKey[$key]]))) {
                                if ($dataUpdate === true) {
                                    $data['dbProc'] = 'update';
                                } else {
                                    $data['dbProc'] = 'same';
                                }
                            } else {
                                $data['dbProc'] = 'update';
                                $dataUpdate = true;
                                $data[$chkKey[$key]] = $goodsData[$dataKey][$chkKey[$key]];
                            }
                        }
                    }
                    $getData[] = $data;
                    $dataKey++;
                }
            }
        }

        if ($dataDelete === false) {
            $goodsData[$dataKey][$chkKey[0]] = gd_isset($goodsData[$dataKey][$chkKey[0]]);
            if (!is_null($goodsData[$dataKey][$chkKey[0]])) {
                for ($i = $dataKey; $i < count($goodsData); $i++) {
                    $add[$snoName] = '';
                    $add['goodsNo'] = $goodsNo;
                    foreach ($chkKey as $cVal) {
                        $add[$cVal] = $goodsData[$i][$cVal];
                    }
                    $add['dbProc'] = 'insert';
                    $getData[] = $add;
                    unset($add);
                }
            }
            // 데이타에 따른 insert, update, delete
            $this->doGoodsDataHandle($getData, $goodsNo, $tableField, $tableName);

            // 삭제 인겨우
        } else if ($dataDelete === true) {
            $this->db->bind_param_push($arrBind, 'i', $goodsNo);
            $this->db->set_delete_db($tableName, 'goodsNo = ?', $arrBind);
            unset($arrBind);
        }
    }

    /**
     * 회원 아이디를 체크
     *
     * @author su
     *
     * @param integer $memId memId 값
     *
     * @return boolean
     */
    private function doMemIdCheck($memId = null)
    {
        $strSQL = 'SELECT memNo FROM ' . DB_MEMBER . ' WHERE memId = \'' . $memId . '\'';
        list($data) = $this->db->fetch($strSQL, 'row');

        return $data;
    }

    private function initHeader()
    {
        $this->excelHeader = '<html xmlns="http://www.w3.org/1999/xhtml" lang="ko" xml:lang="ko">' . chr(10);
        $this->excelHeader .= '<head>' . chr(10);
        $this->excelHeader .= '<title>Excel Down</title>' . chr(10);
        $this->excelHeader .= '<meta http-equiv="Content-Type" content="text/html; charset=' . SET_CHARSET . '" />' . chr(10);
        $this->excelHeader .= '<style>' . chr(10);
        $this->excelHeader .= 'br{mso-data-placement:same-cell;}' . chr(10);
        $this->excelHeader .= '.xl31{mso-number-format:"0_\)\;\\\(0\\\)";}' . chr(10);
        $this->excelHeader .= '.xl24{mso-number-format:"\@";} ' . chr(10);
        $this->excelHeader .= '.title{font-weight:bold; background-color:#F6F6F6; text-align:center;} ' . chr(10);
        $this->excelHeader .= '</style>' . chr(10);
        $this->excelHeader .= '</head>' . chr(10);
        $this->excelHeader .= '<body>' . chr(10);
    }

    private function initFooter()
    {
        $this->excelFooter = '</body>' . chr(10);
        $this->excelFooter .= '</html>' . chr(10);
    }

    /**
     * 외부채널 주문 일괄등록 결과 엑셀파일
     *
     * @author bumyul2000@godo.co.kr
     *
     * @param array $resultData
     *
     * @return void
     */
    public function setExternalOrderResult($resultData)
    {
        $this->excelBody = [];
        $this->excelBody[] = '<table border="1">' . chr(10);
        $this->excelBody[] = '<tr>' . chr(10);
        $this->excelBody[] = '<td>번호</td>' . chr(10);
        $this->excelBody[] = '<td>주문번호</td>' . chr(10);
        $this->excelBody[] = '<td>주문그룹번호</td>' . chr(10);
        $this->excelBody[] = '<td>자체상품코드</td>' . chr(10);
        $this->excelBody[] = '<td>자체옵션코드</td>' . chr(10);
        $this->excelBody[] = '<td>등록결과</td>' . chr(10);
        $this->excelBody[] = '<td>실패사유</td>' . chr(10);
        $this->excelBody[] = '</tr>' . chr(10);
        if(count($resultData) > 0){
            foreach($resultData as $key => $resultValue){
                $this->excelBody[] = '<tr>' . chr(10);
                $this->excelBody[] = '<td>' . ($key+1) . '</td>' . chr(10);
                $this->excelBody[] = '<td class="xl24">' . $resultValue['orderNo'] . '</td>' . chr(10);
                $this->excelBody[] = '<td>' . $resultValue['orderGroup'] . '</td>' . chr(10);
                $this->excelBody[] = '<td>' . $resultValue['goodsCd'] . '</td>' . chr(10);
                $this->excelBody[] = '<td>' . $resultValue['optionCode'] . '</td>' . chr(10);
                $this->excelBody[] = '<td>' . $resultValue['result'] . '</td>' . chr(10);
                $this->excelBody[] = '<td>' . $resultValue['message'] . '</td>' . chr(10);
                $this->excelBody[] = '</tr>' . chr(10);
            }
        }
        $this->excelBody[] = '</table>' . chr(10);

        $this->printExcel();
    }

    /**
     * @return array
     */
    public function getArrWhere(): array
    {
        return StringUtils::strIsSet($this->arrWhere, []);
    }

    /**
     * @param array $arrWhere
     */
    public function setArrWhere(array $arrWhere)
    {
        $this->arrWhere = $arrWhere;
    }

    /**
     * addArrWhere
     *
     * @param $value
     */
    public function addArrWhere($value) {
        $this->arrWhere[] = $value;
    }
}
