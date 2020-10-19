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
namespace Bundle\Component\Cart;

use Component\Goods\Goods;
use Component\Naver\NaverPay;
use Component\Payment\Payco\Payco;
use Component\GoodsStatistics\GoodsStatistics;
use Component\Policy\Policy;
use Component\Database\DBTableField;
use Component\Delivery\EmsRate;
use Component\Delivery\OverseasDelivery;
use Component\Mall\Mall;
use Component\Mall\MallDAO;
use Component\Member\Util\MemberUtil;
use Component\Member\Group\Util;
use Component\Validator\Validator;
use Cookie;
use Exception;
use Framework\Debug\Exception\AlertBackException;
use Framework\Debug\Exception\AlertRedirectException;
use Framework\Debug\Exception\AlertRedirectCloseException;
use Framework\Debug\Exception\Except;
use Framework\Utility\ArrayUtils;
use Framework\Utility\NumberUtils;
use Framework\Utility\SkinUtils;
use Framework\Utility\StringUtils;
use Globals;
use Request;
use Session;
use Component\Godo\GodoCenterServerApi;

/**
 * 장바구니 class
 *
 * 상품과 추가상품을 분리하는 작업에서 추가상품을 기존과 동일하게 상품에 종속시켜놓은 이유는
 * 상품과 같이 배송비 및 다양한 조건들을 아직은 추가상품에 설정할 수 없어서
 * 해당 상품으로 부터 할인/적립등의 조건을 상속받아서 사용하기 때문이다.
 * 따라서 추후 추가상품쪽에 상품과 동일한 혜택과 기능이 추가되면
 * 장바구니 테이블에서 상품이 별도로 담길 수 있도록 개발되어져야 한다.
 *
 * @author Shin Donggyu <artherot@godo.co.kr>
 */
class Cart
{
    /**
     * 구매불가 코드별 코드 정의
     */
    const POSSIBLE_DISPLAY_NO = 'DISPLAY_NO';// 상품 출력 여부
    const POSSIBLE_SELL_NO = 'SELL_NO';// 상품 판매 여부
    const POSSIBLE_SOLD_OUT = 'SOLD_OUT';// 재고 없음 체크
    const POSSIBLE_ZERO_PRICE = 'ZERO_PRICE';// 금액 0원 상품인 상품을 장바구니에 담지 않음으로 체크한 경우
    const POSSIBLE_PRICE_STRING = 'PRICE_STRING';// 가격 대체 문구가 있는 경우 판매금지
    const POSSIBLE_OPTION_DISPLAY_NO = 'OPTION_DISPLAY_NO';// 옵션 출력여부에 따른 판매금지
    const POSSIBLE_OPTION_SELL_NO = 'OPTION_SELL_NO';// 옵션 판매여부에 따른 판매금지
    const POSSIBLE_OPTION_NOT_AVAILABLE = 'OPTION_NOT_AVAILABLE';// 옵션 사용안함에 따른 판매금지 (단, 장바구니에 옵션번호가 있는 경우)
    const POSSIBLE_SALE_DATE_START = 'SALE_DATE_START';// 상품판매기간 전 상품
    const POSSIBLE_SALE_DATE_END = 'SALE_DATE_END';// 상품판매기간 종료 상품
    const POSSIBLE_STOCK_OVER = 'STOCK_OVER';// 상품재고가 0인 경우
    const POSSIBLE_DELIVERY_SNO_NO = 'DELIVERY_SNO_NO';// 배송비 정책이 없는 경우 판매금지
    const POSSIBLE_ONLY_ADULT = 'ONLY_ADULT';// 성인만 구매가능한 상품을 미성년자가 담은 경우
    const POSSIBLE_ONLY_MEMBER = 'ONLY_MEMBER';// 회원만 구매가능한 상품을 비회원이 담은 경우
    const POSSIBLE_ONLY_MEMBER_GROUP = 'ONLY_MEMBER_GROUP';// 구매가능 회원등급이 아닌 회원/비회원이 담은 경우
    const POSSIBLE_NOT_FIND_PAYMENT = 'NOT_FIND_PAYMENT';// 사용가능한 결제수단 없음
    const POSSIBLE_DELIVERY_WEIGHT_LIMIT = 'DELIVERY_WEIGHT_LIMIT';// 배송비 정책이 없는 경우 판매금지
    const POSSIBLE_ORDER_NO = 'ORDER_NO';// 구매 불가
    const POSSIBLE_OPTION_QUANTITY_NOT_SELECT = 'OPTION_QUANTITY_NOT_SELECT';// 옵션/수량 미선택
    const POSSIBLE_ACCESS_RESTRICTION = 'ACCESS_RESTRICTION';// 접근 권한이 설정된 상품

    /**
     * 구매불가 코드별 메시지 정의
     */
    const POSSIBLE_DISPLAY_NO_MESSAGE = '판매중지 상품';// 상품 출력 여부
    const POSSIBLE_SELL_NO_MESSAGE = '판매중지 상품';// 상품 판매 여부
    const POSSIBLE_SOLD_OUT_MESSAGE = '재고부족';// 재고 없음 체크
    const POSSIBLE_ZERO_PRICE_MESSAGE = '상품금액 없음';// 금액 0원 상품인 상품을 장바구니에 담지 않음으로 체크한 경우
    const POSSIBLE_PRICE_STRING_MESSAGE = '상품금액 없음';// 가격 대체 문구가 있는 경우 판매금지
    const POSSIBLE_OPTION_DISPLAY_NO_MESSAGE = '판매중지 옵션';// 옵션 출력여부에 따른 판매금지
    const POSSIBLE_OPTION_SELL_NO_MESSAGE = '판매중지 옵션';// 옵션 판매여부에 따른 판매금지
    const POSSIBLE_OPTION_NOT_AVAILABLE_MESSAGE = '판매중지 옵션';// 옵션 사용안함에 따른 판매금지 (단, 장바구니에 옵션번호가 있는 경우)
    const POSSIBLE_SALE_DATE_START_MESSAGE = '판매시작 전';// 상품판매기간 전 상품
    const POSSIBLE_SALE_DATE_END_MESSAGE = '판매종료';// 상품판매기간 종료 상품
    const POSSIBLE_STOCK_OVER_MESSAGE = '재고부족';// 상품재고가 0인 경우
    const POSSIBLE_DELIVERY_SNO_NO_MESSAGE = '지정된 배송비 없음';// 배송비 정책이 없는 경우 판매금지
    const POSSIBLE_ONLY_ADULT_MESSAGE = '성인 인증 필요 상품';//TODO:global 성인만 구매가능한 상품을 미성년자가 담은 경우
    const POSSIBLE_ONLY_MEMBER_MESSAGE = '회원전용 상품';// 회원만 구매가능한 상품을 비회원이 담은 경우
    const POSSIBLE_ONLY_MEMBER_GROUP_MESSAGE = '특정 회원등급전용 상품';// 구매가능 회원등급이 아닌 회원/비회원이 담은 경우
    const POSSIBLE_NOT_FIND_PAYMENT_MESSAGE = '사용가능한 결제수단 없음';// 사용가능한 결제수단 없음
    const POSSIBLE_DELIVERY_WEIGHT_LIMIT_MESSAGE = '해외배송 무게제한 설정';// 사용가능한 결제수단 없음
    const POSSIBLE_ORDER_NO_MESSAGE = '구매 불가';//  구매 불가
    const POSSIBLE_OPTION_QUANTITY_NOT_SELECT_MESSAGE = '옵션/수량 미선택';// 옵션/수량 미선택
    const POSSIBLE_ACCESS_RESTRICTION_MESSAGE = '접근불가 상품';// 접근 권한이 설정된 상품

    /**
     * 상품종류 코드별 메시지 정의
     */
    const CART_GOODS_TYPE_GOODS = 'goods';
    const CART_GOODS_TYPE_ADDGOODS = 'addGoods';

    /**
     * @var null|object 디비 접속
     */
    protected $db;

    /**
     * @var string 수기주문 여부
     */
    protected $isWrite = false;

    /**
     * @var string 관리모드에서 global 주문건 처리시 사용
     */
    protected $isAdminGlobal = false;

    /**
     * @var string 실제 cart 테이블 사용 (수기주문용 테이블 말고)
     */
    protected $useRealCart = false;

    /**
     * @var string 수기주문에서 회원 장바구니 추가를 통한 접근일 경우
     */
    protected $isWriteMemberCartAdd = false;

    /**
     * @var string 수기주문에서 회원 장바구니 추가를 통한 접근으로 쿠폰처리가 되었을시 (기존 적용되어있던 memberCouponsno 를 삭제할 목적으로 사용)
     */
    protected $isWriteMemberUseCouponCartSnoArr = array();

    /**
     * @var array 로그인한 Session의 회원 정보 (memNo|groupSno|adultFl)
     */
    protected $members = [];

    /**
     * @var boolean 로그인 여부
     */
    protected $isLogin = false;

    /**
     * @var string 사용할 테이블명
     */
    protected $tableName;

    /**
     * @var array 주문 진행 중인 장바구니 SNO
     */
    public $cartSno;

    /**
     * @var array 장바구니 설정 값
     */
    public $cartPolicy;

    /**
     * @var array 마일리지 지급 정보
     */
    public $mileageGiveInfo = [];

    /**
     * @var int 장바구니 갯수
     */
    public $cartCnt = 0;

    /**
     * @var int 장바구니 SCM 업체 갯수
     */
    public $cartScmCnt = 0;

    /**
     * @var array 장바구니 SCM 업체의 상품 갯수
     */
    public $cartScmGoodsCnt = [];

    /**
     * @var array 장바구니 SCM 정보
     */
    public $cartScmInfo = [];

    /**
     * @var string 쇼핑 계속하기 주소를 위한 마지막 상품의 대표 카테고리
     */
    public $shoppingUrl = '';

    /**
     * @var array 상품 총 가격 합계 (상품 판매가격, 옵션 가격, 텍스트 옵션 가격, 추가 상품 가격)
     */
    public $totalPrice = [];

    /**
     * @var int 상품 총 가격
     */
    public $totalGoodsPrice = 0;

    /**
     * @var array SCM 별 상품 총 가격
     */
    public $totalScmGoodsPrice = [];

    /**
     * @var int 상품 할인 총 가격
     */
    public $totalGoodsDcPrice = 0;

    /**
     * @var array scm 별 상품 할인 총 가격
     */
    public $totalScmGoodsDcPrice = [];

    /**
     * @var int 상품별 총 상품 마일리지
     */
    public $totalGoodsMileage = 0;

    /**
     * @var array scm 별 총 상품 마일리지
     */
    public $totalScmGoodsMileage = [];

    /**
     * @var int 회원 그룹 추가 할인 총 가격
     */
    public $totalMemberDcPrice = 0;

    /**
     * @var array scm 별 회원 그룹 추가 할인 총 가격
     */
    public $totalScmMemberDcPrice = [];

    /**
     * @var int 회원 그룹 중복 할인 총 가격
     */
    public $totalMemberOverlapDcPrice = 0;

    /**
     * @var array scm 별 회원 그룹 중복 할인 총 가격
     */
    public $totalScmMemberOverlapDcPrice = [];

    /**
     * @var int 마이앱 상품 추가 할인 총 가격
     */
    public $totalMyappDcPrice = 0;

    /**
     * @var int scm 별 마이앱 상품 추가 할인 총 가격
     */
    public $totalScmMyappDcPrice = [];

    /**
     * @var int 회원 그룹 총 마일리지
     */
    public $totalMemberMileage = 0;

    /**
     * @var array scm 별 회원 그룹 총 마일리지
     */
    public $totalScmMemberMileage = [];

    /**
     * @var int 상품 총 쿠폰 금액
     */
    public $totalCouponGoodsDcPrice = 0;

    /**
     * @var array scm 별 상품 총 쿠폰 금액
     */
    public $totalScmCouponGoodsDcPrice = [];

    /**
     * @var int 상품 총 쿠폰 마일리지
     */
    public $totalCouponGoodsMileage = 0;

    /**
     * @var array scm 별 상품 총 쿠폰 마일리지
     */
    public $totalScmCouponGoodsMileage = [];

    /**
     * @var int 상품별 총 배송 금액
     */
    public $totalDeliveryCharge = 0;

    /**
     * @var int 총 배송 할인 금액
     */
    public $totalDeliveryFreeCharge = 0;

    /**
     * @var int 상품별 총 지역별 배송 금액
     */
    public $totalGoodsDeliveryAreaPrice = [];

    /**
     * @var array scm 별 총 배송 금액
     */
    public $totalScmGoodsDeliveryCharge = [];

    /**
     * @var array 상품 배송정책별 총 배송 금액
     */
    public $totalGoodsDeliveryPolicyCharge = [];

    /**
     * @var int 회원 할인 총 금액
     */
    public $totalSumMemberDcPrice = 0;

    /**
     * @var int 총 결제 금액
     */
    public $totalSettlePrice = 0;

    /**
     * @var int 할인을 포함한 총 결제 금액
     */
    public $totalRealSettlePrice = 0;

    /**
     * @var int 총 공급가
     */
    public $totalPriceSupply = 0;

    /**
     * @var int 총 세액
     */
    public $totalTaxPrice = 0;

    /**
     * @var int 총 비과세금액 (면세금액)
     */
    public $totalFreePrice = 0;

    /**
     * @var int 총 부가세율
     */
    public $totalVatRate = 0;

    /**
     * @var int 사용한 마일리지
     */
    public $totalUseMileage = 0;

    /**
     * @var int 사용한 예치금
     */
    public $totalUseDeposit = 0;

    /**
     * @var int 총 적립 마일리지
     */
    public $totalMileage = 0;

    /**
     * @var array 사용 쿠폰 로그
     */
    public $couponLog = [];

    /**
     * @var int 총 주문 쿠폰 할인 금액
     */
    public $totalCouponOrderDcPrice = 0;

    /**
     * @var int 총 배송 쿠폰 할인 금액
     */
    public $totalCouponDeliveryDcPrice = 0;

    /**
     * @var int 총 주문 쿠폰 적립 금액
     */
    public $totalCouponOrderMileage = 0;

    /**
     * @var string 쿠폰에 의한 결제 방법
     */
    public $couponSettleMethod = 'all';

    /**
     * @var string 마일리지 지급 예외 (y:지급|n)
     */
    public $mileageGiveExclude = 'y';

    /**
     * @var bool 구매가능 여부
     */
    public $orderPossible = true;

    /**
     * @var string 구매불가 메시지
     */
    public $orderPossibleMessage = '';

    /**
     * @var bool EMS 배송가능 여부
     */
    public $emsDeliveryPossible = true;

    /**
     * @var array 배송비 설정 정보 - 배송비 부과방법이 배송비 조건별인 경우
     */
    public $setDeliveryInfo = [];

    /**
     * @var array 사은품 정보를 위한 데이타
     */
    public $giftForData = [];

    /**
     * @var bool 비과세 설정에 따른 세금계산서 출력 여부
     */
    public $taxInvoice = true;

    /**
     * @var boolean 상품 전체의 비과세 여부
     */
    public $taxGoodsChk = true;

    /**
     * @var array 결제제한 수단
     */
    public $payLimit = [];

    /**
     * @var array 과세/비과세 설정 값
     */
    protected $tax;

    /**
     * @var site Key (비회원 장바구니 구분용)
     */
    protected $siteKey;

    /**
     * @var string 일반샵과 모바일샵 상품 출력 구분을 위한
     */
    protected $goodsDisplayFl = 'goodsDisplayFl';
    protected $goodsSellFl = 'goodsSellFl';

    /**
     * @var array 회원 할인 정보
     */
    protected $memberDc = [];

    /**
     * @var array 회원정보
     */
    public $_memInfo;

    /**
     * @var array 사용 가능 결제수단
     */
    public $useSettleKindPg = [];

    /**
     * @var 채널
     */
    protected $channel;

    /**
     * @var 해외배송정책
     */
    public $overseasDeliveryPolicy;

    /**
     * @var 배송전체 무게
     */
    public $totalDeliveryWeight = [
        'total' => 0,
        'goods' => 0,
        'box' => 0,
    ];

    /**
     * @var 배송비 무료 여부
     */
    public $deilveryFree;

    /**
     * @var bool 설정변경등으로 할인값등이 바뀐경우
     */
    public $changePrice = true;

    public $orderCheckoutDataPossible;

    public $paycoDeliveryAreaCheck;

    public $couponApplyOrderNo;

    public $cartGoodsCnt = 0;
    public $cartAddGoodsCnt = 0;
    public $multiShippingOrderInfo = [];
    public $totalGoodsMultiDeliveryAreaPrice = [];
    public $totalGoodsMultiDeliveryPolicyCharge = [];
    public $totalScmGoodsMultiDeliveryCharge = [];
    public $goodsCouponInfo = [];
    public $deliveryFreeByMileage;

    public $cartPsPassModeArray = [];

    public $deliveryBasicInfo = [];

    /**
     * @var 마이앱 사용유무
     */
    public $useMyapp;

    /**
     * @var 회원등급 > 브랜드별 추가할인 상품 브랜드 정보
     */
    public $goodsBrandInfo = [];

    /**
     * @var 주문상품교환여부
     */
    public $orderGoodsChange = false;

    /**
     * 생성자
     */
    public function __construct()
    {
        // 회원 로그인 여부 (CartAdmin에서 반드시 오버라이드 처리 해야 함)
        $this->isLogin = gd_is_login();

        // 회원정보 생성 (CartAdmin에서 반드시 오버라이드 처리 해야 함)
        $this->members = [
            'memNo' => Session::get('member.memNo'),
            'adultFl' => Session::get('member.adultFl'),
            'groupSno' => Session::get('member.groupSno'),
        ];

        // 프론트 단에서 사용되는 테이블 명
        $this->tableName = DB_CART;

        // DB 인스턴스 호출
        if (!is_object($this->db)) {
            $this->db = \App::load('DB');
        }

        $this->siteKey = Session::get('siteKey');

        // 회원설정
        $member = \App::Load(\Component\Member\Member::class);
        $this->_memInfo = $member->getMemberInfo();
        if (empty($this->_memInfo) === false) {
            $this->_memInfo['settleGb'] = Util::matchSettleGbDataToString($this->_memInfo['settleGb']);
        }
        $this->deliveryFreeByMileage = $this->_memInfo['deliveryFree'];

        // 장바구니 설정
        if (!is_array($this->cartPolicy)) {
            $this->cartPolicy = gd_policy('order.cart');
        }

        // 상품 과세 / 비과세 설정
        if (!is_array($this->tax)) {
            $this->tax = gd_policy('goods.tax');
        }

        // 비과세 설정에 따른 세금계산서 출력 여부
        if ($this->tax['taxFreeFl'] == 'f') {
            $this->taxInvoice = false;
        }

        // 상품 출력여부 설정
        if (Request::isMobile()) {
            $this->goodsDisplayFl = 'goodsDisplayMobileFl';
            $this->goodsSellFl = 'goodsSellMobileFl';
        }

        // 마일리지 지급 정보
        $this->mileageGiveInfo = gd_mileage_give_info();

        // 마일리지 지급 예외
        $this->mileageGiveExclude = $this->mileageGiveInfo['give']['excludeFl'];

        //지급수단
        $useSettleKindPg = gd_policy('order.settleKind');
        if ($useSettleKindPg) {
            foreach ($useSettleKindPg as $k => $v) {
                if ($v['useFl'] == 'y' && substr($k, 0, 1) == 'p') {
                    $this->useSettleKindPg[$k] = $v['name'];
                }
            }
        }

        // 해외배송 기본 정책
        if (Globals::get('gGlobal.isFront')) {
            $overseasDelivery = new OverseasDelivery();
            $this->overseasDeliveryPolicy = $overseasDelivery->getBasicData(\Component\Mall\Mall::getSession('sno'), 'mallSno');
        }

        // cart_ps에서 주문허용 상품쿠폰 금액 계산 시 허용될 mode 값
        $this->cartPsPassModeArray = [
            'check_area_delivery',
            'check_country_delivery',
            'multi_shipping_delivery',
            'check_multi_area_delivery',
            'set_mileage'
        ];

        // 마이앱 사용유무
        $this->useMyapp = gd_policy('myapp.config')['useMyapp'] && Request::isMyapp();
    }

    /**
     * setChannel
     *
     * @param $channel
     *
     * @return $this
     */
    public function setChannel($channel)
    {
        $this->channel = $channel;
        return $this;
    }

    /**
     * getChannel
     *
     * @return 채널
     */
    public function getChannel()
    {
        return $this->channel;
    }

    /**
     * 장바구니 정보 출력
     *
     * 완성된 쿼리문은 $db->strField , $db->strJoin , $db->strWhere , $db->strGroup , $db->strOrder , $db->strLimit 멤버 변수를
     * 이용할수 있습니다.
     *
     * @param string $cartSno 장바구니 고유 번호 (기본 null)
     * @param string $cartField 출력할 필드명 (기본 null)
     * @param array $arrBind bind 처리 배열 (기본 null)
     * @param mixed $dataArray return 값을 배열처리 (기본값 false)
     * @param boolean $stripSlashesFl stripslashes 사용여부
     *
     * @return array 장바구니 정보
     *
     * @author su
     */
    public function getCartInfo($cartSno = null, $cartField = null, $arrBind = null, $dataArray = false, $stripSlashesFl = true)
    {
        if (is_null($arrBind)) {
            // $arrBind = array();
        }
        if ($cartSno) {
            // $cartSno가 배열일 경우
            if (is_array($cartSno) === true) {
                $arrWhere = [];
                foreach ($cartSno as $val) {
                    $arrWhere[] = 'sno = ?';
                    $this->db->bind_param_push($arrBind, 'i', $val);
                }
                if ($this->db->strWhere) {
                    $this->db->strWhere = " (" . @implode($arrWhere, ' OR ') . ") AND " . $this->db->strWhere;
                } else {
                    $this->db->strWhere = " (" . @implode($arrWhere, ' OR ') . ")";
                }
            } else {
                if ($this->db->strWhere) {
                    $this->db->strWhere = " sno = ? AND " . $this->db->strWhere;
                } else {
                    $this->db->strWhere = " sno = ?";
                }
                $this->db->bind_param_push($arrBind, 'i', $cartSno);
            }
        }
        if ($cartField) {
            if ($this->db->strField) {
                $this->db->strField = $cartField . ', ' . $this->db->strField;
            } else {
                $this->db->strField = $cartField;
            }
        }
        $query = $this->db->query_complete();
        $strSQL = 'SELECT ' . array_shift($query) . ' FROM ' . $this->tableName . ' ' . implode(' ', $query);
        $getData = $this->db->query_fetch($strSQL, $arrBind);

        if ($stripSlashesFl === true) {
            if (count($getData) == 1 && $dataArray === false) {
                return gd_htmlspecialchars_stripslashes($getData[0]);
            }

            return gd_htmlspecialchars_stripslashes($getData);
        } else {
            if (count($getData) == 1 && $dataArray === false) {
                return $getData[0];
            }

            return $getData;
        }
    }

    /**
     * 장바구니 담기 (상품코드/옵션코드/상품수량/적용쿠폰 배열)
     * 상품을 장바구니에 담습니다.
     *
     * @param array $arrData 상품 정보 [mode, scmNo, cartMode, goodsNo[], optionSno[], goodsCnt[], couponApplyNo[]]
     *
     * @return array
     * @throws Exception
     */
    public function saveInfoCart($arrData, $tempCartPolicyDirectOrder = 'n', $channel = '')
    {
        // 적용한 쿠폰이 있을 경우 중복 사용 체크
        if($this->isWrite !== true && $this->isWriteMemberCartAdd !== true) { //수기 주문의 경우 체크하지 않음
            if (empty($arrData['couponApplyNo']) === false && count($arrData['couponApplyNo']) > 0) {
                if (method_exists($this, 'validateApplyCoupon') === true) {
                    $resValidateApplyCoupon = $this->validateApplyCoupon($arrData['couponApplyNo']);
                    if ($resValidateApplyCoupon['status'] === false) {
                        throw new Exception($resValidateApplyCoupon['msg']);
                    }
                }
            }
        }

        // 상품상세의 쿠폰 필드명을 장바구니 쿠폰 필드명으로 변경
        $arrData['memberCouponNo'] = $arrData['couponApplyNo'];
        unset($arrData['couponApplyNo']);

        // 장바구니 테이블 필드
        $arrExclude = [
            'siteKey',
            'memNo',
            'directCart',
        ];
        $fieldData = DBTableField::setTableField('tableCart', null, $arrExclude);

        if ($tempCartPolicyDirectOrder == 'y') { // 페이코 네이버 체크아웃바로구매일때는 무조건 directOrderFl값 y로 처리해주기
            $this->cartPolicy['directOrderFl'] = 'y';
        }

        // 마이앱 로그인뷰 스크립트
        $myappBuilderInfo = gd_policy('myapp.config')['builder_auth'];
        if ($this->useMyapp && empty($myappBuilderInfo['clientId']) === false && empty($myappBuilderInfo['secretKey']) === false) {
            // 기존 바로 구매 상품 삭제
            $this->setDeleteDirectCart();

            // 비회원 주문하기 클릭 후 재 진입시 로그인 페이지 이동하지 않는 현상 수정
            MemberUtil::logoutGuest();
        }

        if ($arrData['cartMode'] == 'd' && $this->cartPolicy['directOrderFl'] == 'y' && $channel == 'related') {
            // 기존 바로 구매 상품 삭제
            $this->setDeleteDirectCart();

            // 비회원 주문하기 클릭 후 재 진입시 로그인 페이지 이동하지 않는 현상 수정
            MemberUtil::logoutGuest();
        }

        // 상품 번호를 기준으로 장바구니에 담을 상품의 배열을 처리함
        foreach ($arrData['goodsNo'] as $goodsIdx => $goodsNo) {
            foreach ($fieldData as $field) {
                $getData[$field] = $arrData[$field][$goodsIdx];
            }
            $getData['mallSno'] = Mall::getSession('sno');
            $getData['scmNo'] = $arrData['scmNo'];
            $getData['cartMode'] = $arrData['cartMode'];
            $getData['linkMainTheme'] = $arrData['linkMainTheme'];
            $getData['goodsDeliveryFl'] = $arrData['goodsDeliveryFl'];
            $getData['sameGoodsDeliveryFl'] = $arrData['sameGoodsDeliveryFl'];

            // 상품 상세 페이지에서 배송비 항목을 노출 안함 처리하면 선불/착불이 넘어오지 않아 체크 후 선불/착불 입력
            if (!$arrData['deliveryCollectFl']) {
                $arrData['deliveryCollectFl'] = $this->getGoodsDeliveryCollectFl($goodsNo);
            }
            $getData['deliveryCollectFl'] = $arrData['deliveryCollectFl'];
            $getData['deliveryMethodFl'] = $arrData['deliveryMethodFl'];
            $getData['goodsPrice'] = $arrData['set_total_price'];
            if (is_array($arrData['useBundleGoods']) === false) {
                $getData['useBundleGoods'] = $arrData['useBundleGoods'];
            }
            //수기주문 - 회원 장바구니 추가를 통한 상품 주문시 실제 cart sno를 끌고간다.
            //기존 적용되어있던 memberCouponsno 를 삭제할 목적으로 사용
            if($this->isWrite === true && $this->isWriteMemberCartAdd === true){
                $getData['preRealCartSno'] = $arrData['preRealCartSno'][$goodsIdx];
            }

            // 장바구니에 담기
            $arrayRtn[] = $this->saveGoodsToCart($getData);

            $this->setInflowGoods($goodsNo);
        }

        if (($arrData['goodsDeliveryFl'] == 'y' || ($arrData['goodsDeliveryFl'] != 'y' && $arrData['sameGoodsDeliveryFl'] == 'y')) && empty($arrData['deliveryCollectFl']) === false && empty($arrData['deliveryMethodFl']) === false) {
            foreach ($arrayRtn as $cartSno) {
                unset($getData);
                $getData['deliveryCollectFl'] = gd_isset($arrData['deliveryCollectFl']);
                $getData['deliveryMethodFl'] = gd_isset($arrData['deliveryMethodFl']);
                $cartInfo = $this->getCartInfo($cartSno, 'mallSno, siteKey, memNo, directCart, goodsNo');

                $arrBind = $this->db->get_binding(DBTableField::getBindField('tableCart', ['deliveryCollectFl', 'deliveryMethodFl']), $getData, 'update');
                $strWhere = 'mallSno = ? AND directCart = ? AND goodsNo = ?';
                $this->db->bind_param_push($arrBind['bind'], 'i', $cartInfo['mallSno']);
                $this->db->bind_param_push($arrBind['bind'], 's', $cartInfo['directCart']);
                $this->db->bind_param_push($arrBind['bind'], 'i', $cartInfo['goodsNo']);
                if (gd_is_login() === true) {
                    $this->db->bind_param_push($arrBind['bind'], 'i', $cartInfo['memNo']);
                    $strWhere .= ' AND memNo = ?';
                } else {
                    $this->db->bind_param_push($arrBind['bind'], 's', $cartInfo['siteKey']);
                    $strWhere .= ' AND siteKey = ?';

                }

                $this->db->set_update_db($this->tableName, $arrBind['param'], $strWhere, $arrBind['bind']);
            }
        }

        return $arrayRtn;
    }

    public function setInflowGoods($goodsNo)
    {
        $referer = explode('?', \Request::server()->get('HTTP_REFERER'));
        parse_str($referer[1], $parse);

        if (empty($parse['inflow']) === false && $parse['goodsNo'] == $goodsNo) {
            if (\Cookie::has('inflow_goods') === true) {
                $inflowGoods = json_decode(\Cookie::get('inflow_goods'), true);
            } else {
                \Cookie::set('inflow', $parse['inflow']);
            }
            $inflowGoods[] = $goodsNo;
            \Cookie::set('inflow_goods', json_encode(array_unique($inflowGoods)));
            \Cookie::set('inflow', $parse['inflow']);
        }
    }

    /**
     * 상품 상세 페이지에서 배송비 항목을 노출 안함 처리하면 선불/착불이 넘어오지 않아 체크
     * @param $goodsNo int 상품고유번호
     */
    public function getGoodsDeliveryCollectFl($goodsNo)
    {
        $arrBind = [];
        // 상품의 배송비 선불/착불 값
        $this->db->strWhere = 'g.goodsNo = ?';
        $this->db->bind_param_push($arrBind, 'i', $goodsNo);

        $this->db->strField = 'sdb.collectFl';

        $this->db->strJoin = ' LEFT JOIN ' . DB_SCM_DELIVERY_BASIC . ' as sdb ON g.deliverySno = sdb.sno';
        $query = $this->db->query_complete();
        $strSQL = 'SELECT ' . array_shift($query) . ' FROM ' . DB_GOODS . ' as g ' . implode(' ', $query);
        $getData = $this->db->query_fetch($strSQL, $arrBind);

        return $getData[0]['collectFl'];
    }

    /**
     * 상품별 배송비 체크
     * @param $goodsNo int 상품고유번호
     */
    public function getGoodsDeliveryConfig($goodsNo)
    {
        $arrBind = [];
        // 상품의 배송비 선불/착불 값
        $this->db->strWhere = 'g.goodsNo = ?';
        $this->db->bind_param_push($arrBind, 'i', $goodsNo);

        $this->db->strField = 'sdb.collectFl, sdb.deliveryMethodFl';

        $this->db->strJoin = ' LEFT JOIN ' . DB_SCM_DELIVERY_BASIC . ' as sdb ON g.deliverySno = sdb.sno';
        $query = $this->db->query_complete();
        $strSQL = 'SELECT ' . array_shift($query) . ' FROM ' . DB_GOODS . ' as g ' . implode(' ', $query);
        $getData = $this->db->query_fetch($strSQL, $arrBind);

        $returnData['deliveryCollectFl'] = $getData[0]['collectFl'];
        $tmp = explode(STR_DIVISION, $getData[0]['deliveryMethodFl']);
        foreach($tmp as $tmpVal){
            if(!empty($tmpVal)){
                $returnData['deliveryMethodFl'] = $tmpVal;
                break;
            }
        }
        $returnData['deliveryMethodFlEtc'] = explode(STR_DIVISION, $getData[0]['deliveryMethodFl']);

        return $returnData;
    }

    /**
     * 장바구니 담기 (단품)
     * 한개의 상품을 장바구니에 담습니다.
     *
     * @param array $arrData 상품 정보 [goodsNo, optionSno, goodsCnt, addGoodsNo, addGoodsCnt, optionText,
     *                       deliveryCollectFl, memberCouponNo, scmNo, cartMode]
     *
     * @return integer 중복상품 sno
     * @throws Exception
     */
    public function saveGoodsToCart($arrData)
    {
        // Validation - 상품 코드 체크
        if (Validator::required($arrData['goodsNo'], true) === false) {
            throw new Exception(__('상품번호를 확인 할 수 없어 처리되지 않았습니다.'));
        }

        // Validation - 상품 가격/옵션 체크
        if (Validator::required($arrData['optionSno'], true) === false) {
            //throw new Exception(__('상품 가격 코드를 확인할 수 없어 처리되지 않았습니다.'));
        }

        // Validation - 상품 수량 체크
        if (Validator::number($arrData['goodsCnt'], 1, null, true) === false) {
            throw new Exception(__('상품 수량 이상으로 장바구니에 해당 상품을 담을 수 없습니다.'));
        }

        //바로구매의 경우 무게별 배송비를 사용하는 상품일 경우 범위제한을 체크하여 결제를 방지한다.
        if ($arrData['cartMode'] == 'd' && $this->cartPolicy['directOrderFl'] == 'y') {
            $checkGoodsWeightMessage = '';
            $checkGoodsWeightMessage = $this->checkGoodsWeight($arrData);
            if($checkGoodsWeightMessage !== ''){
                throw new Exception(__('무게가 %s 이상의 상품은 구매할 수 없습니다. (배송범위 제한)', $checkGoodsWeightMessage));
            }
        }

        // 금액 0원 상품 체크
        if ($this->cartPolicy['zeroPriceOrderFl'] === 'n' && intval($arrData['goodsPrice']) === 0) {
            throw new Exception(__('상품 가격이 없습니다. 확인해 주세요.'));
        }

        // 상품 텍스트 옵션
        if (gd_isset($arrData['optionText']) && empty($arrData['optionText']) === false) {
            $arrData['optionText'] = ArrayUtils::removeEmpty($arrData['optionText']);
            $arrData['optionText'] = json_encode($arrData['optionText'], JSON_UNESCAPED_UNICODE);
        } else {
            $arrData['optionText'] = '';
        }

        // 추가 상품
        if (gd_isset($arrData['addGoodsNo']) && empty($arrData['addGoodsNo']) === false) {
            $arrData['addGoodsNo'] = ArrayUtils::removeEmpty($arrData['addGoodsNo']);
            $arrData['addGoodsCnt'] = ArrayUtils::removeEmpty($arrData['addGoodsCnt']);
            $arrData['addGoodsNo'] = json_encode($arrData['addGoodsNo']);
            $arrData['addGoodsCnt'] = json_encode($arrData['addGoodsCnt']);
        }

        // 사이트키 및 회원 번호
        $arrData['siteKey'] = $this->siteKey;
        if ($this->isLogin === true) {
            $arrData['memNo'] = $this->members['memNo'];
        } else {
            $arrData['memNo'] = 0;
        }

        // 중복 상품이 담겨있는지 확인 후 해당 상품정보 반환
        $duplicatedGoods = $this->checkDuplicationGoods($arrData);

        // 바로 구매 설정 및 장바구니 상품 중복 체크
        if ($arrData['cartMode'] == 'd' && $this->cartPolicy['directOrderFl'] == 'y') {
            $arrData['directCart'] = 'y';
            $check['cnt'] = 0;

            // 바로 구매 쿠키 생성
            Cookie::set('isDirectCart', true, 0, '/');

        } else {
            // 기존 추가상품이있으면 추가상품정보 가져와서 처리
            /* // 아래에서 업데이트 케이스를 없애버려서 여기서 더해줄 필요가 없어짐 - 또 수정경우를 생각해서 코드 보존함.
            if ($duplicatedGoods['addGoodsNo'] != '' && $arrData['addGoodsNo'] != '') {
                $tempAddNo = '';
                $tempAddCnt = '';
                $tempArrayAdd = [];
                $arrData['addGoodsNo'] = json_decode(gd_htmlspecialchars_stripslashes($arrData['addGoodsNo']));
                $arrData['addGoodsCnt'] = json_decode(gd_htmlspecialchars_stripslashes($arrData['addGoodsCnt']));
                $tempAddNo = json_decode(gd_htmlspecialchars_stripslashes($duplicatedGoods['addGoodsNo']));
                $tempAddCnt = json_decode(gd_htmlspecialchars_stripslashes($duplicatedGoods['addGoodsCnt']));
                foreach ($tempAddNo as $num => $kval) {
                    // 기존 추가 상품 정보 정리
                    if (!$tempArrayAdd[$kval]) {
                        $tempArrayAdd[$kval] = $tempAddCnt[$num];
                    } else {
                        if ($this->cartPolicy['sameGoodsFl'] == 'p') {
                            $tempArrayAdd[$kval] += $tempAddCnt[$num];
                        }
                    }
                }
                // 넘어온 추가 상품 정보 정리
                foreach ($arrData['addGoodsNo'] as $num => $kval) {
                    if (!$tempArrayAdd[$kval]) {
                        $tempArrayAdd[$kval] = $arrData['addGoodsCnt'][$num];
                    } else {
                        if ($this->cartPolicy['sameGoodsFl'] == 'p') {
                            $tempArrayAdd[$kval] += $arrData['addGoodsCnt'][$num];
                        }
                    }
                }
                $arrData['addGoodsNo'] = json_encode(ArrayUtils::removeEmpty(array_keys($tempArrayAdd)));
                $arrData['addGoodsCnt'] = json_encode(ArrayUtils::removeEmpty(array_values($tempArrayAdd)));
            }
            */
            // 장바구니 상품 갯수 체크 (중복상품이 있는 경우 무시)
            if($this->isWrite !== true){
                if ($this->cartPolicy['goodsLimitFl'] == 'y') {
                    if ($this->getCartGoodsCnt() >= $this->cartPolicy['goodsLimitCnt'] && $duplicatedGoods['cnt'] == 0) {
                        throw new Exception(sprintf(__('장바구니 보관상품은 %d 개까지 보관하실 수 있습니다.'), $this->cartPolicy['goodsLimitCnt']));
                    }
                }
            }

            // 바로구매가 아님
            $arrData['directCart'] = 'n';

            // 바로 구매 쿠키 삭제
            if (Cookie::has('isDirectCart')) {
                Cookie::del('isDirectCart');
            }
        }

        if($this->isWrite === true && $this->isWriteMemberCartAdd === true && $this->useRealCart !== true){
            //수기주문 이면서, 회원 장바구니 추가로 접근 인경우
            if ($arrData['memberCouponNo']) {
                $coupon = \App::load('\\Component\\Coupon\\Coupon');
                $memberCouponUsable = $coupon->getMemberCouponUsableCheckOrderWrite($arrData['memberCouponNo'], $this->isWriteMemberCartAdd);
                if ($memberCouponUsable) {
                    $coupon->setMemberCouponStateOrderWrite($arrData['memberCouponNo'], 'cart');
                } else {
                    throw new Exception(__('사용 할 수 없는 쿠폰 입니다.'));
                }
            }
        }
        else {
            // 쿠폰이 적용 되면 쿠폰의 상태를 변경
            if ($arrData['memberCouponNo']) {
                // 적용한 쿠폰이 있을 경우 중복 사용 체크
                if (empty($arrData['memberCouponNo']) === false) {
                    if (method_exists($this, 'validateApplyCoupon') === true) {
                        $resValidateApplyCoupon = $this->validateApplyCoupon($arrData['memberCouponNo']);
                        if ($resValidateApplyCoupon['status'] === false) {
                            throw new Exception($resValidateApplyCoupon['msg']);
                        }
                    }
                }
                // 쿠폰 모듈
                $coupon = \App::load('\\Component\\Coupon\\Coupon');
                $memberCouponUsable = $coupon->getMemberCouponUsableCheck($arrData['memberCouponNo']);
                if ($memberCouponUsable) {
                    $coupon->setMemberCouponState($arrData['memberCouponNo'], 'cart');
                } else {
                    throw new Exception(__('사용 할 수 없는 쿠폰 입니다.'));
                }
            }
        }

        // 장바구니 담기
        $return = $this->setInsetCart($arrData);

        //수기주문에서 회원 장바구니 추가를 통한 접근으로 쿠폰처리가 되었을시 (기존 적용되어있던 memberCouponsno 를 삭제할 목적으로 사용)
        if($this->isWrite === true && $this->isWriteMemberCartAdd === true && $arrData['memberCouponNo'] && $memberCouponUsable){
            $this->isWriteMemberUseCouponCartSnoArr[$return]['preRealCartSno'] = $arrData['preRealCartSno'];
            $this->isWriteMemberUseCouponCartSnoArr[$return]['memberCouponNo'] = $arrData['memberCouponNo'];
        }

        return $return;

        /* // 장바구니 통합으로 일단 무조건 인서트 후 통합처리 시 카운트 업데이트 처리
        if ($duplicatedGoods['cnt'] == 0) {
            $return = $this->setInsetCart($arrData);

            //수기주문에서 회원 장바구니 추가를 통한 접근으로 쿠폰처리가 되었을시 (기존 적용되어있던 memberCouponsno 를 삭제할 목적으로 사용)
            if($this->isWrite === true && $this->isWriteMemberCartAdd === true && $arrData['memberCouponNo'] && $memberCouponUsable){
                $this->isWriteMemberUseCouponCartSnoArr[$return]['preRealCartSno'] = $arrData['preRealCartSno'];
                $this->isWriteMemberUseCouponCartSnoArr[$return]['memberCouponNo'] = $arrData['memberCouponNo'];
            }

            return $return;
        } else {
            if ($arrData['cartMode'] == 'd' && $this->cartPolicy['directOrderFl'] == 'y') {
                $this->setUpdateCartDirect($duplicatedGoods['sno'],'y',$arrData['goodsCnt'],$arrData['addGoodsNo'],$arrData['addGoodsCnt'],$arrData['optionText']);
            }

            //수기주문에서 회원 장바구니 추가를 통한 접근으로 쿠폰처리가 되었을시 (기존 적용되어있던 memberCouponsno 를 삭제할 목적으로 사용)
            if($this->isWrite === true && $this->isWriteMemberCartAdd === true && $arrData['memberCouponNo'] && $memberCouponUsable){
                $this->isWriteMemberUseCouponCartSnoArr[$duplicatedGoods['sno']]['preRealCartSno'] = $arrData['preRealCartSno'];
                $this->isWriteMemberUseCouponCartSnoArr[$duplicatedGoods['sno']]['memberCouponNo'] = $arrData['memberCouponNo'];
            }

            if ($this->cartPolicy['sameGoodsFl'] == 'p') {
                if ($this->getChannel() != 'naverpay') {    //네이버페이 구매인경우 수량 누적 업데이트 무시
                    $this->setUpdateCartStock($duplicatedGoods['sno'], $duplicatedGoods['goodsCnt'], $arrData['goodsCnt'], $arrData['goodsNo'],$arrData['addGoodsNo'],$arrData['addGoodsCnt'],$arrData['optionText']);
                }
                return $duplicatedGoods['sno'];
            }

            return $duplicatedGoods['sno'];
        }
        */
    }

    /**
     * 장바구니 수정 (상품코드/옵션코드/상품수량/적용쿠폰 배열)
     * 상품을 장바구니에 담습니다.
     *
     * @param array $arrData 상품 정보 [mode, scmNo, cartMode, goodsNo[], optionSno[], goodsCnt[], couponApplyNo[]]
     *
     * @return array
     */
    public function updateInfoCart($arrData)
    {
        // 상품상세의 쿠폰 필드명을 장바구니 쿠폰 필드명으로 변경
        $arrData['memberCouponNo'] = $arrData['couponApplyNo'];
        unset($arrData['couponApplyNo']);
        unset($arrData['useBundleGoods']);
        // 장바구니 테이블 필드
        $arrExclude = [
            'siteKey',
            'memNo',
            'directCart',
            /*'deliveryCollectFl',
            'deliveryMethodFl',*/
            'memberCouponNo',
            'scmNo',
            'cartMode',
            'linkMainTheme',
        ];

        $fieldData = DBTableField::setTableField('tableCart', null, $arrExclude);

        // 상품 번호를 기준으로 장바구니에 담을 상품의 배열을 처리함
        foreach ($arrData['goodsNo'] as $goodsIdx => $goodsNo) {
            foreach ($fieldData as $field) {
                if (in_array($field, ['deliveryCollectFl', 'deliveryMethodFl']) === true) {
                    if (empty($arrData[$field]) === false) {
                        $getData[$field] = gd_isset($arrData[$field]);
                    } else {
                        unset($fieldData[$field]);
                    }
                } else {
                    $getData[$field] = gd_isset($arrData[$field][$goodsIdx]);
                }
            }

            if (gd_isset($getData['optionText']) && empty($getData['optionText']) === false) {
                $getData['optionText'] = ArrayUtils::removeEmpty($getData['optionText']);
                $getData['optionText'] = json_encode($getData['optionText'], JSON_UNESCAPED_UNICODE);
            }

            // 추가 상품
            if (gd_isset($getData['addGoodsNo']) && empty($getData['addGoodsNo']) === false) {
                $getData['addGoodsNo'] = ArrayUtils::removeEmpty($getData['addGoodsNo']);
                $getData['addGoodsCnt'] = ArrayUtils::removeEmpty($getData['addGoodsCnt']);
                $getData['addGoodsNo'] = json_encode($getData['addGoodsNo']);
                $getData['addGoodsCnt'] = json_encode($getData['addGoodsCnt']);
            }


            $arrBind = $this->db->get_binding(DBTableField::getBindField('tableCart', $fieldData), $getData, 'update');
            $strWhere = 'sno = ?';
            $this->db->bind_param_push($arrBind['bind'], 'i', $arrData['sno']);
            $this->db->set_update_db($this->tableName, $arrBind['param'], $strWhere, $arrBind['bind']);

            if (($arrData['goodsDeliveryFl'] == 'y' || ($arrData['goodsDeliveryFl'] != 'y' && $arrData['sameGoodsDeliveryFl'] == 'y')) && empty($getData['deliveryCollectFl']) === false && empty($getData['deliveryMethodFl']) === false) {
                unset($getData);
                $getData['deliveryCollectFl'] = gd_isset($arrData['deliveryCollectFl']);
                $getData['deliveryMethodFl'] = gd_isset($arrData['deliveryMethodFl']);
                $cartInfo = $this->getCartInfo($arrData['sno'], 'mallSno, siteKey, memNo, directCart, goodsNo');

                $arrBind = $this->db->get_binding(DBTableField::getBindField('tableCart', ['deliveryCollectFl', 'deliveryMethodFl']), $getData, 'update');
                $strWhere = 'mallSno = ? AND directCart = ? AND goodsNo = ?';
                $this->db->bind_param_push($arrBind['bind'], 'i', $cartInfo['mallSno']);
                $this->db->bind_param_push($arrBind['bind'], 's', $cartInfo['directCart']);
                $this->db->bind_param_push($arrBind['bind'], 'i', $cartInfo['goodsNo']);
                if (gd_is_login() === true) {
                    $this->db->bind_param_push($arrBind['bind'], 'i', $cartInfo['memNo']);
                    $strWhere .= ' AND memNo = ?';
                } else {
                    $this->db->bind_param_push($arrBind['bind'], 's', $cartInfo['siteKey']);
                    $strWhere .= ' AND siteKey = ?';

                }

                $this->db->set_update_db($this->tableName, $arrBind['param'], $strWhere, $arrBind['bind']);
            }

            // 장바구니 변경 갯수 상품 업데이트
            $goods = \App::load(\Component\Goods\Goods::class);
            $goods->setCartWishCount('cart', $goodsNo);
        }

    }


    /**
     * 장바구니 상품 개수 체크
     *
     * @return array 상품 개수
     */
    public function getCartGoodsCnt()
    {
        $arrBind = [];

        // 회원 로그인 체크
        if ($this->isLogin === true) {
            $strWhere = "memNo = ? AND directCart = 'n'";
            $this->db->bind_param_push($arrBind['bind'], 'i', $this->members['memNo']);
        } else {
            $strWhere = "siteKey = ? AND directCart = 'n'";
            $this->db->bind_param_push($arrBind['bind'], 's', $this->siteKey);
        }

        // @todo 바로구매시 장바구니에 안담기는 문제로 인해 임시 주석
        // $strSQL = 'SELECT count(goodsNo) as cnt FROM ' . $this->tableName . ' WHERE directCart= \'n\' AND ' . $strWhere;
        $strSQL = 'SELECT count(goodsNo) as cnt FROM ' . $this->tableName . ' WHERE ' . $strWhere;
        $getData = $this->db->query_fetch($strSQL, $arrBind['bind'], false);

        return $getData['cnt'];
    }

    /**
     * 장바구니 상품에 적용된 쿠폰
     *
     * @param array where 조건 배열
     *
     * @return array 상품쿠폰이 적용된 장바구니 상품 리스트
     *
     * @author su
     */
    public function getCartMemberCoupon($arrBind)
    {
        if (is_array($arrBind['param'])) {
            $addWhere = implode(' AND ', $arrBind['param']);
        } else {
            $addWhere = $arrBind['param'];
        }
        $strSQL = "SELECT sno, memberCouponNo FROM " . $this->tableName . " WHERE memberCouponNo != '' AND " . $addWhere;
        $cartData = $this->db->query_fetch($strSQL, $arrBind['bind'], true);

        return $cartData;
    }

    /**
     * 장바구니 상품 중복 체크
     *
     * @param  array $arrData 상품 정보
     *
     * @return array 체크 결과 (중복수량, 기존장바구니 sno, 현재 담긴 상품 수량)
     */
    protected function checkDuplicationGoods($arrData)
    {
        // 회원 로그인 체크
        if ($this->isLogin === true) {
            //수기주문시 회원인 경우 siteKey, memNo 동시 비교
            if($this->isWrite === true){
                $arrExclude = [
                    'goodsCnt',
                    'addGoodsCnt',
                    'memberCouponNo',
                ];
            }
            else {
                $arrExclude = [
                    'goodsCnt',
                    'siteKey',
                ];
            }
        } else {
            $arrExclude = [
                'goodsCnt',
                'memNo',
            ];
        }
        $strWhere = '';
        $arrBind = $this->db->get_binding(DBTableField::tableCart(), $arrData, 'select', null, $arrExclude);

        if (!empty($arrBind['where'])) {
            $strWhere = ' AND ' . implode(' AND ', $arrBind['where']);
        }
        $strSQL = "SELECT count(goodsNo) as cnt, sno, goodsCnt, addGoodsNo, addGoodsCnt FROM " . $this->tableName . " WHERE " . implode(' AND ', $arrBind['param']) . $strWhere . " ORDER BY sno ASC";
        $getData = $this->db->query_fetch($strSQL, $arrBind['bind'], false);

        return $getData;
    }

    /**
     * 장바구니 상품 DB insert
     *
     * @param array $arrData 상품 정보
     */
    protected function setInsetCart($arrData)
    {
        // 해당 상품의 구매가능 수량
        $arrData['goodsCnt'] = $this->getBuyableStock($arrData['goodsNo'], $arrData['goodsCnt'], $arrData['useBundleGoods']);

        $arrBind = $this->db->get_binding(DBTableField::tableCart(), $arrData, 'insert');
        $this->db->set_insert_db($this->tableName, $arrBind['param'], $arrBind['bind'], 'y');

        $cartSno = $this->db->insert_id();

        // 장바구니 통계 저장 (바로구매, 수기주문은 제외)
        $eventConfig = \App::getConfig('event')->toArray();
        if ($eventConfig['cartOrderStatistics'] !== 'n' && $arrData['directCart'] != 'y' && $this->isWrite !== true) {
            $cartStatistics = $arrData;
            $cartStatistics['cartSno'] = $cartSno;
            $cartStatistics['orderFl'] = 'n';
            if (empty($cartStatistics['mallSno'])) {
                $cartStatistics['mallSno'] = DEFAULT_MALL_NUMBER;
            }
            $goodsStatistics = new GoodsStatistics();
            $goodsStatistics->setCartStatistics($cartStatistics);
        }
        // 장바구니 변경 갯수 상품 업데이트
        $goods = \App::load(\Component\Goods\Goods::class);
        $goods->setCartWishCount('cart', $arrData['goodsNo']);

        return $cartSno;
    }

    /**
     * 최소 구매수량 / 최대 구매 수량 체크해서 실제 구매가능한 수량을 반환
     *
     * @param array $goodsNo 상품 번호
     * @param array $goodsCnt 해당 상품이 현재 장바구니에 담긴 수량
     *
     * @return integer 상품 수량
     */
    protected function getBuyableStock($goodsNo, $goodsCnt, $useBundleGoods = '')
    {
        $arrBind = [
            'i',
            $goodsNo,
        ];
        $strSQL = "SELECT g.fixedSales, g.minOrderCnt, g.maxOrderCnt, g.salesUnit FROM " . DB_GOODS . " g WHERE g.goodsNo = ?";
        $getData = $this->db->query_fetch($strSQL, $arrBind);

        if ($this->checkUseBundle !== true) {
            $strCntSQL = "SELECT COUNT(*) as cnt FROM " . DB_CART . " WHERE useBundleGoods = 1";
            $this->useBundleCnt = $this->db->query_fetch($strCntSQL, null, false);
            $this->checkUseBundle = true;
        }

        //묶음주문패치시 해당 내용 적용 안함
        if (empty($useBundleGoods) === true) {
            if ($this->useBundleCnt['cnt'] == 0) {
                // 최대 구매수량이 있는경우
                if ($getData[0]['maxOrderCnt'] > 0) {
                    if ($goodsCnt > $getData[0]['maxOrderCnt']) { // 최대 구매수량 보다 큰 경우 수량은 최대 구매수량으로
                        $goodsCnt = $getData[0]['maxOrderCnt'];
                    }
                }

                if ($goodsCnt < $getData[0]['minOrderCnt']) { // 최소 구매수량 보다 작은 경우 수량은 최소 구매수량으로
                    $goodsCnt = $getData[0]['minOrderCnt'];
                }
            }
        }
        if ($goodsCnt <= 0) {
            $goodsCnt = 1;
        }

        //수기주문일때 묶음단위에 맞지 않으면 최소묶음단위로 장바구니 추가
        if($this->isWrite === true){
            $salesUnitCheck = false;
            if ($getData[0]['fixedSales'] != 'goods' && (int)$getData[0]['minOrderCnt'] <= (int)$getData[0]['salesUnit']) {
                if ((int)$getData[0]['maxOrderCnt'] > 0) {
                    if((int)$getData[0]['maxOrderCnt'] >= (int)$getData[0]['salesUnit']){
                        $salesUnitCheck = true;
                    }
                }
                else {
                    $salesUnitCheck = true;
                }
            }

            if($salesUnitCheck === true){
                if((int)$goodsCnt % (int)$getData[0]['salesUnit'] > 0) {
                    $goodsCnt = $getData[0]['salesUnit'];
                }
            }
        }

        return $goodsCnt;
    }

    /**
     * 장바구니 바로구매 상태 수정
     *
     * @param integer $cartSno 장바구니 sno
     * @param integer $goodsCnt 현재 수량
     * @param integer $plusCnt 추가 수량
     * @param integer $goodsNo 상품 번호
     * @param string $addGoodsNo 추가상품 번호
     * @param string $addGoodsCnt 추가상품 수량
     * @param string $optionText 옵션텍스트
     */
    protected function setUpdateCartDirect($cartSno, $directCart,$goodsCnt, $addGoodsNo = '', $addGoodsCnt = '', $optionText = '')
    {
        $arrBind = [
            'si',
            $directCart,
            $goodsCnt,
        ];

        $arrStr = 'directCart = ?, goodsCnt = ?';

        if ($addGoodsNo != '') {
            $arrBind[0] .= 's';
            $arrBind[] = $addGoodsNo;
            $arrStr .= ', addGoodsNo = ?';
        }
        if ($addGoodsCnt != '') {
            $arrBind[0] .= 's';
            $arrBind[] = $addGoodsCnt;
            $arrStr .= ', addGoodsCnt = ?';
        }
        if ($optionText != '') {
            $arrBind[0] .= 's';
            $arrBind[] = $optionText;
            $arrStr .= ', optionText = ?';
        }
        $arrBind[0] .= 'i';
        $arrBind[] = $cartSno;

        $this->db->set_update_db($this->tableName, $arrStr, 'sno = ?', $arrBind);
    }

    /**
     * 장바구니 상품 수량 추가
     *
     * @param integer $cartSno 장바구니 sno
     * @param integer $goodsCnt 현재 수량
     * @param integer $plusCnt 추가 수량
     * @param integer $goodsNo 상품 번호
     * @param string $addGoodsNo 추가상품 번호
     * @param string $addGoodsCnt 추가상품 수량
     * @param string $optionText 옵션텍스트
     */
    protected function setUpdateCartStock($cartSno, $goodsCnt, $plusCnt, $goodsNo, $addGoodsNo = '', $addGoodsCnt = '', $optionText = '')
    {
        // 추가시의 총 수량
        $totalCnt = $goodsCnt + $plusCnt;

        // 해당 상품의 구매가능(최대/최소) 수량 체크
        $checkCnt = $this->getBuyableStock($goodsNo, $totalCnt);

        $arrBind = [
            'i',
            $checkCnt,
        ];

        $arrStr = 'goodsCnt = ?';

        if ($addGoodsNo != '') {
            $arrBind[0] .= 's';
            $arrBind[] = $addGoodsNo;
            $arrStr .= ', addGoodsNo = ?';
        }
        if ($addGoodsCnt != '') {
            $arrBind[0] .= 's';
            $arrBind[] = $addGoodsCnt;
            $arrStr .= ', addGoodsCnt = ?';
        }
        if ($optionText != '') {
            $arrBind[0] .= 's';
            $arrBind[] = $optionText;
            $arrStr .= ', optionText = ?';
        }
        $arrBind[0] .= 'i';
        $arrBind[] = $cartSno;

        $this->db->set_update_db($this->tableName, $arrStr, 'sno = ?', $arrBind);
    }

    /**
     * 장바구니 상품 쿠폰 적용
     *
     * @param integer $cartSno 장바구니 sno
     * @param string $memberCouponNo 회원쿠폰 고유번호(INT_DIVISION 으로 구분된 회원쿠폰고유번호)
     * @throws Exception 다른 상품에 적용된 쿠폰
     *
     * @author su
     */
    public function setMemberCouponApply($cartSno, $memberCouponNo)
    {
        // 장바구니에 적용된 쿠폰 초기화
        $this->setMemberCouponDelete($cartSno);

        // 쿠폰 모듈
        $coupon = \App::load('\\Component\\Coupon\\Coupon');

        if ($memberCouponNo) {
            // 적용가능 쿠폰인지 확인 후 쿠폰의 상태를 변경
            $memberCouponUsable = $coupon->getMemberCouponUsableCheck($memberCouponNo);
            if ($memberCouponUsable) {
                $coupon->setMemberCouponState($memberCouponNo, 'cart', false, $cartSno);
            } else {
                throw new AlertBackException(__('사용 할 수 없는 쿠폰 입니다.'));
            }
        }
        $arrBind = [
            'si',
            $memberCouponNo,
            $cartSno,
        ];
        $this->db->set_update_db($this->tableName, 'memberCouponNo = ?', 'sno = ?', $arrBind);
    }

    /**
     * 수기주문용 - 장바구니 상품 쿠폰 적용
     *
     * @param integer $cartSno 장바구니 sno
     * @param string $memberCouponNo 회원쿠폰 고유번호(INT_DIVISION 으로 구분된 회원쿠폰고유번호)
     * @param string $memberCartAddTypeCouponNo 수기주문에서 회원장바구니로 추가된 회원쿠폰 고유번호(INT_DIVISION 으로 구분된 회원쿠폰고유번호)
     * @throws Exception 다른 상품에 적용된 쿠폰
     *
     * @author su
     */
    public function setMemberCouponApplyOrderWrite($cartSno, $memberCouponNo, $memberCartAddTypeCouponNo='')
    {
        // 장바구니에 적용된 쿠폰 초기화
        $this->setMemberCouponDelete($cartSno);

        // 쿠폰 모듈
        $coupon = \App::load('\\Component\\Coupon\\Coupon');

        $returnCouponNo = '';

        if ($memberCouponNo){

            /*
             * 수기주문에서 회원장바구니로 쿠폰이 적용되어 있는 상품 추가 후
             * 쿠폰적용을 변경처리 할때 회원장바구니에 적용되어있던 쿠폰의 번호는 사용 체크에서 제외처리한다.
             */
            if(trim($memberCartAddTypeCouponNo) !== ''){
                $memberCouponNoArr = explode(INT_DIVISION, $memberCouponNo);
                $memberCartAddTypeCouponNoArr = explode(INT_DIVISION, $memberCartAddTypeCouponNo);

                $newMemberCouponNoArr = array_diff($memberCouponNoArr , $memberCartAddTypeCouponNoArr);
                $newMemberCouponNo = implode(INT_DIVISION, $newMemberCouponNoArr);
                if(trim($newMemberCouponNo) !== ''){
                    $memberCouponUsable = $coupon->getMemberCouponUsableCheckOrderWrite($newMemberCouponNo, false);
                }
                else {
                    $memberCouponUsable = true;
                }
            }
            else {
                $memberCouponUsable = $coupon->getMemberCouponUsableCheckOrderWrite($memberCouponNo, false);
            }

            if ($memberCouponUsable) {
                $coupon->setMemberCouponStateOrderWrite($memberCouponNo, 'cart');

                $returnCouponNo = implode(INT_DIVISION, array_intersect($memberCartAddTypeCouponNoArr, $memberCouponNoArr));
            } else {
                throw new AlertBackException(__('사용 할 수 없는 쿠폰 입니다.'));
            }
        }
        $arrBind = [
            'si',
            $memberCouponNo,
            $cartSno,
        ];
        $this->db->set_update_db($this->tableName, 'memberCouponNo = ?', 'sno = ?', $arrBind);

        return $returnCouponNo;
    }

    /**
     * 장바구니 상품 쿠폰 취소
     *
     * @param integer $cartSno 장바구니 sno
     * @param integer $deleteMemberCouponNo 삭제할 회원쿠폰 고유번호
     *
     * @author su
     */
    public function setMemberCouponDelete($cartSno, $deleteMemberCouponNo = null)
    {
        $memberCouponNo = $this->getCartInfo($cartSno, 'memberCouponNo');
        if ($memberCouponNo['memberCouponNo'] > 0) {
            // 쿠폰 모듈
            $coupon = \App::load('\\Component\\Coupon\\Coupon');
            if($this->isWrite === true && $this->useRealCart !== true){
                $coupon->setMemberCouponStateOrderWrite($memberCouponNo['memberCouponNo'], 'y');
            }
            else {
                //쿠폰 초기화 하기 전 유효성 체크
                $memberCouponNoList = str_replace(INT_DIVISION, ',',$memberCouponNo['memberCouponNo']);
                $validateSql = 'SELECT memberCouponNo, memberCouponState FROM ' . DB_MEMBER_COUPON . ' WHERE memberCouponNo IN (' . $memberCouponNoList . ');';
                $validateData = $this->db->query_fetch($validateSql, []);
                $arrInitMemberCouponNo = [];
                foreach ($validateData as $memCouponInfo) {
                    if (empty($memCouponInfo['memberCouponNo']) === false) {
                        //주문 완료된 쿠폰의 상태값이 아닌 경우만 가능
                        if ($memCouponInfo['memberCouponState'] !== 'order') {
                            $arrInitMemberCouponNo[] = $memCouponInfo['memberCouponNo'];
                        }
                    }
                }
                if (empty($arrInitMemberCouponNo) === false) {
                    $initMemberCouponNo = implode(INT_DIVISION, $arrInitMemberCouponNo);
                    $coupon->setMemberCouponState($initMemberCouponNo, 'y');
                }
            }

            $deleteMemberCouponNo = (is_null($deleteMemberCouponNo)) ? '' : $deleteMemberCouponNo;

            $arrBind = [
                'ii',
                $deleteMemberCouponNo,
                $cartSno,
            ];
            $this->db->set_update_db($this->tableName, 'memberCouponNo = ?', 'sno = ?', $arrBind);
        }
    }

    /**
     * 장바구니 상품 수량 변경
     *
     * @param array $getData 장바구니 데이터
     *
     * @throws Except
     */
    public function setCartCnt($getData)
    {
        // 장바구니 번호와 상품 번호가 없으면 오류
        if (empty($getData['cartSno']) || empty($getData['goodsNo'])) {
            throw new Exception(__('오류가 발생 하였습니다.'));
        }

        // 상품 수량 변경
        if (empty($getData['goodsCnt']) === false) {
            // Validation - 상품 수량 체크
            if (Validator::number($getData['goodsCnt'], 1, null, true) === false) {
                throw new Exception(__('상품 수량 이상으로 장바구니에 해당 상품을 담을 수 없습니다.'));
            }

            // 해당 상품의 최대/최소 수량
            $checkCnt = $this->getBuyableStock($getData['goodsNo'], $getData['goodsCnt'], $getData['useBundleGoods']);
            $arrBind = [
                'ii',
                $checkCnt,
                $getData['cartSno'],
            ];
            $this->db->set_update_db($this->tableName, 'goodsCnt = ?', 'sno = ?', $arrBind);

            // 장바구니 변경 갯수 상품 업데이트
            $goods = \App::load(\Component\Goods\Goods::class);
            $goods->setCartWishCount('cart', $getData['goodsNo']);
        }

        // 추가 상품 수량 변경
        if (empty($getData['goodsCnt']) && empty($getData['addGoodsNo']) === false && empty($getData['addGoodsCnt']) === false) {
            // Validation - 추가 상품 수량 체크
            if (Validator::number($getData['addGoodsCnt'], 1, null, true) === false) {
                throw new Exception(__('상품 수량 이상으로 장바구니에 해당 상품을 담을 수 없습니다.'));
            }

            // 장바구니 상품 정보
            $this->db->bind_param_push($arrBind, 's', $getData['cartSno']);
            $strSQL = 'SELECT addGoodsNo, addGoodsCnt FROM ' . $this->tableName . ' WHERE sno = ?';
            $getAddData = $this->db->query_fetch($strSQL, $arrBind, false);
            if (empty($getAddData['addGoodsNo']) === false) {
                $getAddData['addGoodsNo'] = json_decode(gd_htmlspecialchars_stripslashes($getAddData['addGoodsNo']));
                $getAddData['addGoodsCnt'] = json_decode(gd_htmlspecialchars_stripslashes($getAddData['addGoodsCnt']));
                $updateChk = false;
                foreach ($getAddData['addGoodsNo'] as $aKey => $aVal) {
                    // 해당 추가상품 코드의 배열이고 추가상품 수량의 변화가 있는 경우에만 업데이트 처리
                    if ($getData['addGoodsNo'] === strval($aVal) && $getAddData['addGoodsCnt'][$aKey] !== $getData['addGoodsCnt']) {
                        $getAddData['addGoodsCnt'][$aKey] = $getData['addGoodsCnt'];
                        $updateChk = true;
                        break;
                    }
                }

                // 추가 상품 수량 변경 처리
                if ($updateChk === true) {
                    $checkCnt = json_encode($getAddData['addGoodsCnt']);
                    $arrBind = [
                        'si',
                        $checkCnt,
                        $getData['cartSno'],
                    ];
                    $this->db->set_update_db($this->tableName, 'addGoodsCnt = ?', 'sno = ?', $arrBind);
                }
            }
        }
    }

    /**
     * 장바구니 상품 삭제
     *
     * @param array $getData 장바구니 sno 배열
     *
     * @return bool 결과
     */
    public function setCartDelete($getData)
    {
        if (empty($getData) === true) {
            return false;
        }

        $arrBind = [];
        foreach ($getData as $cartSno) {
            // 장바구니 삭제 전 쿠폰 장바구니사용 상태 되돌리기
            $this->setMemberCouponDelete($cartSno);
            $param[] = '?';
            $this->db->bind_param_push($arrBind['bind'], 'i', $cartSno);

            // 장바구니 sno로 goodsNo 조회 - 갯수 변경 처리를 위해 추출
            $changeGoodsNoArray[] = $this->checkCartSelectGoodsNo($cartSno,'', 'sno');
        }

        if (empty($param) === true) {
            return false;
        }

        // 크리마 사용일 경우 삭제 장바구니 sno 기록
        $crema = \App::load('Component\\Service\\Crema');
        $cremaBind = [
            'cartSno' => $getData
        ];
        $crema->insertDeletedCartData($cremaBind);

        // 회원 로그인 체크
        if ($this->isLogin === true) {
            $arrBind['param'] = 'sno IN (' . implode(' , ', $param) . ') AND memNo = ?';
            $this->db->bind_param_push($arrBind['bind'], 'i', $this->members['memNo']);
        } else {
            $arrBind['param'] = 'sno IN (' . implode(' , ', $param) . ') AND  siteKey = ?';
            $this->db->bind_param_push($arrBind['bind'], 's', $this->siteKey);
        }
        $this->db->set_delete_db($this->tableName, $arrBind['param'], $arrBind['bind']);

        // 장바구니 갯수 변경 처리
        $goods = \App::load(\Component\Goods\Goods::class);
        foreach ($changeGoodsNoArray as $goodsNo) {
            $goods->setCartWishCount('cart',  $goodsNo['goodsNo']);
        }

        return true;
    }

    /**
     * 장바구니 바로구매 상품 삭제
     */
    protected function setDeleteDirectCart()
    {
        $session = \App::getInstance('session');
        if($session->get('related_goods_order') == 'y'){
            return;
        }else{
            // 회원 로그인 체크
            if ($this->isLogin === true) {
                $arrBind['param'] = 'memNo = ? AND directCart = \'y\'';
                $this->db->bind_param_push($arrBind['bind'], 'i', $this->members['memNo']);
            } else {
                $arrBind['param'] = 'siteKey = ? AND directCart = \'y\'';
                $this->db->bind_param_push($arrBind['bind'], 's', $this->siteKey);
            }
            // 장바구니 삭제 전 쿠폰 장바구니사용 상태 되돌리기
            $cartCouponData = $this->getCartMemberCoupon($arrBind);
            foreach ($cartCouponData as $cartKey => $cartVal) {
                $this->setMemberCouponDelete($cartVal['sno']);
            }

            // 장바구니 bind 데이터로 goodsNo 조회 - 갯수 변경 처리를 위해 추출
            $changeGoodsNoArray = $this->checkCartSelectGoodsNo(null, $arrBind);

            $this->db->set_delete_db($this->tableName, $arrBind['param'], $arrBind['bind']);

            // 장바구니 갯수 변경 처리
            $goods = \App::load(\Component\Goods\Goods::class);
            foreach ($changeGoodsNoArray as $goodsNo) {
                $goods->setCartWishCount('cart',  $goodsNo['goodsNo']);
            }
        }
    }

    /**
     * 장바구니 바로구매 상품 삭제
     */
    public function setDeleteDirectCartCont()
    {
        // 기존 바로 구매 상품 삭제
        $this->setDeleteDirectCart();
    }

    /**
     * 장바구니 유지기간 초과 상품 삭제
     */
    public function setCartDeletePeriod()
    {
        if ($this->cartPolicy['periodFl'] === 'y' && $this->cartPolicy['periodDay'] > 0) {
            $periodDate = date('Y-m-d H:i:s', strtotime('-' . $this->cartPolicy['periodDay'] . ' day'));
            $arrBind['param'] = 'regDt < ?';
            $this->db->bind_param_push($arrBind['bind'], 's', $periodDate);
            // 장바구니 삭제 전 쿠폰 장바구니사용 상태 되돌리기
            $cartCouponData = $this->getCartMemberCoupon($arrBind);
            foreach ($cartCouponData as $cartKey => $cartVal) {
                $this->setMemberCouponDelete($cartVal['sno']);
            }

            // 장바구니 bind 데이터로 goodsNo 조회 - 갯수 변경 처리를 위해 추출
            $changeGoodsNoArray = $this->checkCartSelectGoodsNo(null, $arrBind);

            $this->db->set_delete_db($this->tableName, $arrBind['param'], $arrBind['bind']);

            // 장바구니 갯수 변경 처리
            $goods = \App::load(\Component\Goods\Goods::class);
            foreach ($changeGoodsNoArray as $goodsNo) {
                $goods->setCartWishCount('cart', $goodsNo['goodsNo']);
            }
        }
    }

    /**
     * 장바구니 비우기 처리
     * 주문번호가 있는 경우 해당 주문번호가 있는 장바구니만 삭제
     * 주문번호가 없는 경우 현재 보여지는 모든 장바구니 삭제하고 삭제 후 쿠폰 환원 처리
     *
     * @param string $orderNo 주문번호
     *
     * @author Jong-tae Ahn <qnibus@godo.co.kr>
     */
    public function setCartRemove($orderNo = null)
    {
        // 주문번호가 있는 경우
        if ($orderNo != null) {
            $arrBind['param'] = 'tmpOrderNo = ?';
            $this->db->bind_param_push($arrBind['bind'], 's', $orderNo);
            // 크리마 사용일 경우 삭제 장바구니 sno 기록
            $crema = \App::load('Component\\Service\\Crema');
            $cremaBind = [
                'tmpOrderNo' => $orderNo,
            ];
            $crema->insertDeletedCartData($cremaBind);
        } else {
            // 회원 로그인 체크
            if ($this->isLogin === true) {
                $arrBind['param'] = 'memNo = ?';
                $this->db->bind_param_push($arrBind['bind'], 'i', $this->members['memNo']);
            } else {
                $arrBind['param'] = 'siteKey = ?';
                $this->db->bind_param_push($arrBind['bind'], 's', $this->siteKey);
            }

            // 장바구니 비우기 전 쿠폰 장바구니사용 상태 되돌리기
            $cartCouponData = $this->getCartMemberCoupon($arrBind);
            foreach ($cartCouponData as $cartKey => $cartVal) {
                $this->setMemberCouponDelete($cartVal['sno']);
            }
        }

        // 장바구니 bind 데이터로 goodsNo 조회 - 갯수 변경 처리를 위해 추출
        $changeGoodsNoArray = $this->checkCartSelectGoodsNo(null, $arrBind);

        // 장바구니 삭제
        $this->db->set_delete_db($this->tableName, $arrBind['param'], $arrBind['bind']);

        // 장바구니 갯수 변경 처리
        $goods = \App::load(\Component\Goods\Goods::class);
        foreach ($changeGoodsNoArray as $goodsNo) {
            $goods->setCartWishCount('cart',  $goodsNo['goodsNo']);
        }
    }

    /**
     * 선택 상품 찜 리스트로 저장
     *
     * @param array $getData 장바구니 sno 배열
     *
     * @return bool 결과
     */
    public function setCartToWish($getData)
    {
        if (empty($getData) === true) {
            return false;
        }

        $arrBind = [];
        foreach ($getData as $cartSno) {
            $param[] = '?';
            $this->db->bind_param_push($arrBind, 'i', $cartSno);

            // 장바구니 sno로 goodsNo 조회 - 갯수 변경 처리를 위해 추출
            $changeGoodsNoArray[] = $this->checkCartSelectGoodsNo($cartSno,'', 'sno');
        }

        if (empty($param) === true) {
            return false;
        }

        // 회원 로그인 체크
        if ($this->isLogin === true) {
            $strWhere = 'sno IN (' . implode(' , ', $param) . ') AND memNo = ?';
            $this->db->bind_param_push($arrBind, 'i', $this->members['memNo']);
        } else {
            $strWhere = 'sno IN (' . implode(' , ', $param) . ') AND  siteKey = ?';
            $this->db->bind_param_push($arrBind, 's', $this->siteKey);
        }

        $selectFieldData = DBTableField::setTableField(
            'tableCart', null, [
                'sno',
                'siteKey',
                'directCart',
                'memberCouponNo',
                'regDt',
                'modDt',
                'tmpOrderNo',
                'linkMainTheme'
            ]
        );
        $insertFieldData = DBTableField::setTableField(
            'tableWish', null, [
                'sno',
                'regDt',
                'modDt',
            ]
        );

        $strSQL = 'INSERT INTO ' . DB_WISH . ' (' . implode(', ', $insertFieldData) . ', regDt) SELECT ' . implode(', ', $selectFieldData) . ', now() FROM ' . $this->tableName . ' WHERE ' . $strWhere;

        $preStr = $this->db->prepare($strSQL);
        $this->db->bind_param($preStr, $arrBind);
        $this->db->execute();
        $this->db->stmt_close();
        unset($arrBind);

        // 장바구니 갯수 변경 처리
        $goods = \App::load(\Component\Goods\Goods::class);
        foreach ($changeGoodsNoArray as $goodsNo) {
            $goods->setCartWishCount('wish',  $goodsNo['goodsNo']);
        }

        return ture;
    }

    /**
     * 선택 상품 주문하기 위한 cartIdx 값 설정
     *
     * @param array $getData 장바구니 sno 배열
     *
     * @return string cartIdx
     */
    public function setOrderSelect($getData)
    {
        if (empty($getData) === true) {
            return false;
        }

        // json 처리후 urlencode
        $jsonData = json_encode($getData);
        $encodeData = urlencode($jsonData);

        return $encodeData;
    }

    /**
     * 데이터를 배열형태로 전환 처리
     *
     * @param string $getData
     *
     * @return mixed
     * @author Jong-tae Ahn <qnibus@godo.co.kr>
     */
    public function getOrderSelect($getData)
    {
        if (empty($getData) === true) {
            return false;
        }

        return json_decode(urldecode($getData));
    }

    /**
     * 관심상품 계산
     * 관심상품의 정보를 가공함
     * @return array 가공된 관심상품 상품 정보
     */
    public function setWishData($getData)
    {
        $data = $this->getCartDataInfo($getData);

        return $data;
    }

    /**
     * 장바구니에 담겨있는 상품리스트 혹은 일부리스트를 가져온다.
     * 데이터를 요청할때 파라미터는 한개라도 반드시 배열의 형태로 넘겨야 한다.
     * [1], [1,2,3...], null과 같은 형태로 작성 가능
     *
     * @param mixed   $cartIdx            장바구니 번호(들)
     * @param array   $address            지역별 배송비 계산을 위한 배송주소
     * @param array   $tmpOrderNo         임시 주문번호 (PG처리 후 해당 주문을 찾기 위함)
     * @param boolean $isAddGoodsDivision 추가상품 주문분리 로직 사용여부
     * @param boolean $isCouponCheck      상품쿠폰 사용가능 체크 여부
     * @param array   $postValue          주문데이터
     * @param array   $setGoodsCnt        복수배송지 사용시 안분된 상품 수량
     * @param array   $setAddGoodsCnt     복수배송지 사용시 안분된 추가 상품 수량
     * @param array   $setDeliveryMethodFl     복수배송지 사용시 배송방식
     * @param array   $setDeliveryCollectFl     복수배송지 사용시 배송비 결제 방법
     * @param array   $setAddGoodsCnt     복수배송지 사용시 안분된 추가 상품 수량
     * @param boolean $deliveryBasicInfoFl      배송비조건 정보 사용여부
     *
     * @return array 상품데이터
     * @throws Exception
     * @author Jong-tae Ahn <qnibus@godo.co.kr>
     */
    public function getCartGoodsData($cartIdx = null, $address = null, $tmpOrderNo = null, $isAddGoodsDivision = false, $isCouponCheck = false, $postValue = [], $setGoodsCnt = [], $setAddGoodsCnt = [], $setDeliveryMethodFl = [], $setDeliveryCollectFl = [], $deliveryBasicInfoFl = false)
    {
        // 회원 로그인 체크
        // 로그인상태면 mergeCart처리
        if (Request::getFileUri() != 'order_ps.php') {
            if ($this->isLogin === true) {
                $this->setMergeCart($this->members['memNo']);
            } else {
                $this->setMergeCart();
            }
        }

        // 장바구니 상품수량 재정의
        if (Request::getFileUri() == 'order.php') {
            $cartIdx = $this->setCartGoodsCnt($this->members['memNo'], $cartIdx);
        }

        $mallBySession = SESSION::get(SESSION_GLOBAL_MALL);

        // 절사 정책 가져오기
        $truncGoods = Globals::get('gTrunc.goods');

        // 선택한 상품만 주문시
        $arrBind = [];
        if (empty($cartIdx) === false) {
            if (is_array($cartIdx)) {
                $tmpWhere = [];
                foreach ($cartIdx as $cartSno) {
                    if (is_numeric($cartSno)) {
                        $tmpWhere[] = $this->db->escape($cartSno);
                    }
                }
                if (empty($tmpWhere) === false) {
                    $tmpAddWhere = [];
                    foreach ($tmpWhere as $val) {
                        $tmpAddWhere[] = '?';
                        $this->db->bind_param_push($arrBind, 'i', $val);
                    }
                    $arrWhere[] = 'c.sno IN (' . implode(' , ', $tmpAddWhere) . ')';
                }
                unset($tmpWhere);
            } elseif (is_numeric($cartIdx)) {
                $arrWhere[] = 'c.sno = ?';
                $this->db->bind_param_push($arrBind, 'i', $cartIdx);
            }
        }

        // 해외배송비조건에 따라 국가코드가 있으면 배송비조건 일련번호 가져와 저장
        if ($this->isGlobalFront($address)) {
            $overseasDeliverySno = $this->getDeliverySnoForOverseas($address);
        }

        // 회원 로그인 체크
        // App::getInstance('ControllerNameResolver')->getControllerRootDirectory() != 'admin'
        if ($this->isLogin === true) {
            //수기주문시 회원인 경우 memNo, siteKey 로 동시 비교
            if($this->isWrite === true  && $this->useRealCart !== true){
                $arrWhere[] = 'c.memNo = \'' . $this->members['memNo'] . '\' AND  c.siteKey = \'' . $this->siteKey . '\'';
            }
            else {
                $arrWhere[] = 'c.memNo = \'' . $this->members['memNo'] . '\'';
            }
        } else {
            $arrWhere[] = 'c.siteKey = \'' . $this->siteKey . '\'';
        }

        // 바로 구매 설정
        if (Cookie::has('isDirectCart') && $this->cartPolicy['directOrderFl'] == 'y' && Request::getFileUri() != 'cart.php' && (Request::getFileUri() != 'order_ps.php' || (Request::getFileUri() == 'order_ps.php' && in_array(Request::post()->get('mode'), ['set_recalculation']) === true)) && $this->isWrite !== true) {
            $arrWhere[] = 'c.directCart = \'y\'';
        } else {
            if (Cookie::has('isDirectCart')) {
                // 바로 구매 쿠키 삭제
                Cookie::del('isDirectCart');
            }

            // 바로구매 쿠폰은 setDeleteDirectCart 에서 처리되고있음
            //$arrWhere[] = 'c.directCart = \'n\'';
        }

        if ($tmpOrderNo !== null) {
            $arrWhere = [];
            $arrWhere[] = 'c.tmpOrderNo = \'' . $tmpOrderNo . '\'';
        }

        // 정렬 방식
        $strOrder = 'c.sno DESC';

        // 장바구니 디비 및 상품 디비의 설정 (필드값 설정)
        $getData = [];

        $arrExclude['cart'] = [];
        $arrExclude['option'] = [
            'goodsNo',
            'optionNo',
        ];
        $arrExclude['addOptionName'] = [
            'goodsNo',
            'optionCd',
            'mustFl',
        ];
        $arrExclude['addOptionValue'] = [
            'goodsNo',
            'optionCd',
        ];
        $arrInclude['goods'] = [
            'goodsNm',
            'commission',
            'scmNo',
            'purchaseNo',
            'goodsCd',
            'cateCd',
            'goodsOpenDt',
            'goodsState',
            'imageStorage',
            'imagePath',
            'brandCd',
            'makerNm',
            'originNm',
            'goodsModelNo',
            'goodsPermission',
            'goodsPermissionGroup',
            'goodsPermissionPriceStringFl',
            'goodsPermissionPriceString',
            'onlyAdultFl',
            'onlyAdultImageFl',
            'goodsAccess',
            'goodsAccessGroup',
            'taxFreeFl',
            'taxPercent',
            'goodsWeight',
            'goodsVolume',
            'totalStock',
            'stockFl',
            'soldOutFl',
            'salesUnit',
            'minOrderCnt',
            'maxOrderCnt',
            'salesStartYmd',
            'salesEndYmd',
            'mileageFl',
            'mileageGoods',
            'mileageGoodsUnit',
            'goodsDiscountFl',
            'goodsDiscount',
            'goodsDiscountUnit',
            'payLimitFl',
            'payLimit',
            'goodsPriceString',
            'goodsPrice',
            'fixedPrice',
            'costPrice',
            'optionFl',
            'optionName',
            'optionTextFl',
            'addGoodsFl',
            'addGoods',
            'deliverySno',
            'delFl',
            'hscode',
            'goodsSellFl',
            'goodsSellMobileFl',
            'goodsDisplayFl',
            'goodsDisplayMobileFl',
            'mileageGroup',
            'mileageGroupInfo',
            'mileageGroupMemberInfo',
            'fixedGoodsDiscount',
            'goodsDiscountGroup',
            'goodsDiscountGroupMemberInfo',
            'exceptBenefit',
            'exceptBenefitGroup',
            'exceptBenefitGroupInfo',
            'fixedSales',
            'fixedOrderCnt',
            'goodsBenefitSetFl',
            'benefitUseType',
            'newGoodsRegFl',
            'newGoodsDate',
            'newGoodsDateFl',
            'periodDiscountStart',
            'periodDiscountEnd',
            'regDt',
            'modDt'
        ];
        $arrInclude['image'] = [
            'imageSize',
            'imageName',
        ];

        $arrFieldCart = DBTableField::setTableField('tableCart', null, $arrExclude['cart'], 'c');
        $arrFieldGoods = DBTableField::setTableField('tableGoods', $arrInclude['goods'], null, 'g');
        $arrFieldOption = DBTableField::setTableField('tableGoodsOption', null, $arrExclude['option'], 'go');
        $arrFieldImage = DBTableField::setTableField('tableGoodsImage', $arrInclude['image'], null, 'gi');
        unset($arrExclude);

        // 장바구니 상품 기본 정보
        $strSQL = "SELECT c.sno,
            " . implode(', ', $arrFieldCart) . ", c.regDt,
            " . implode(', ', $arrFieldGoods) . ",
            " . implode(', ', $arrFieldOption) . ",
            " . implode(', ', $arrFieldImage) . "
        FROM " . $this->tableName . " c
        INNER JOIN " . DB_GOODS . " g ON c.goodsNo = g.goodsNo
        LEFT JOIN " . DB_GOODS_OPTION . " go ON c.optionSno = go.sno AND c.goodsNo = go.goodsNo
        LEFT JOIN " . DB_GOODS_IMAGE . " as gi ON g.goodsNo = gi.goodsNo AND gi.imageKind = 'list'
        WHERE " . implode(' AND ', $arrWhere) . "
        ORDER BY " . $strOrder;

        /**해외몰 관련 **/
        if($mallBySession) {
            $arrFieldGoodsGlobal = DBTableField::setTableField('tableGoodsGlobal',null,['mallSno']);
            $strSQLGlobal = "SELECT gg." . implode(', gg.', $arrFieldGoodsGlobal) . " FROM ".$this->tableName." as c INNER JOIN ".DB_GOODS_GLOBAL." as gg ON  c.goodsNo = gg.goodsNo AND gg.mallSno = '".$mallBySession['sno']."'  WHERE " . implode(' AND ', $arrWhere) ;
            $tmpData = $this->db->query_fetch($strSQLGlobal, $arrBind);
            $globalData = array_combine (array_column($tmpData, 'goodsNo'), $tmpData);
        }

        $query = $this->db->getBindingQueryString($strSQL, $arrBind);
        $result = $this->db->query($query);
        unset($arrWhere, $strOrder);

        // 상품리스트가 없는 경우 주문서에서 강제로 빠져나감
        if ($result === false && Request::getFileUri() != 'cart.php') {
            throw new Exception(__('장바구니에 상품이 없습니다.'));
        }

        // 삭제 상품에 대한 cartNo
        $this->cartSno = [];
        $_delCartSno = [];

        // 해외배송시 박스무게 추가
        if (Globals::get('gGlobal.isFront') || $this->isAdminGlobal === true) {
            $this->totalDeliveryWeight['box'] = $this->overseasDeliveryPolicy['data']['boxWeight'];
            $this->totalDeliveryWeight['total'] += $this->totalDeliveryWeight['box'];
        }

        //매입처 관련 정보
        if(gd_is_plus_shop(PLUSSHOP_CODE_PURCHASE) === true) {
            $strPurchaseSQL = 'SELECT purchaseNo,purchaseNm FROM ' . DB_PURCHASE . ' g  WHERE delFl = "n"';
            $tmpPurchaseData = $this->db->query_fetch($strPurchaseSQL);
            $purchaseData = array_combine(array_column($tmpPurchaseData, 'purchaseNo'), array_column($tmpPurchaseData, 'purchaseNm'));
        }

        //상품 가격 노출 관련
        $goodsPriceDisplayFl = gd_policy('goods.display')['priceFl'];

        //품절상품 설정
        if(Request::isMobile()) {
            $soldoutDisplay = gd_policy('soldout.mobile');
        } else {
            $soldoutDisplay = gd_policy('soldout.pc');
        }

        // 제외 혜택 쿠폰 번호
        $exceptCouponNo = [];
        $goodsKey = [];
        $prevGoodsNo = [];
        $goods = new Goods();
        //상품 혜택 모듈
        $goodsBenefit = \App::load('\\Component\\Goods\\GoodsBenefit');
        $delivery = \App::load('\\Component\\Delivery\\Delivery');
        while ($data = $this->db->fetch($result)) {

            //상품혜택 사용시 해당 변수 재설정
            $data = $goodsBenefit->goodsDataFrontConvert($data);

            //복수배송지 사용 && 수량 안분처리시 상품수량 재설정
            if ((\Component\Order\OrderMultiShipping::isUseMultiShipping() == true || $postValue['isAdminMultiShippingFl'] === 'y') && empty($setGoodsCnt) === false) {
                $couponConfig = gd_policy('coupon.config');
                if($couponConfig['productCouponChangeLimitType'] == 'n' && $postValue['productCouponChangeLimitType'] == 'n') { // 상품쿠폰 주문서 수정 제한 안함일 때
                    $data['goodsCouponOriginGoodsCnt'] = $data['goodsCnt']; // 복수배송지 안분된 상품 갯수가 아닌 카트 상품갯수 파악
                }
                if (empty($setGoodsCnt[$data['sno']]['goodsCnt']) === false) {
                    $data['goodsCnt'] = $setGoodsCnt[$data['sno']]['goodsCnt'];
                } else {
                    $data['goodsCnt'] = $setGoodsCnt[$data['sno']];
                }
            }
            if ((\Component\Order\OrderMultiShipping::isUseMultiShipping() == true || $postValue['isAdminMultiShippingFl'] === 'y') && empty($setAddGoodsCnt) === false) {
                $addGoodsNo = json_decode(stripslashes($data['addGoodsNo']), true);
                $addGoodsCnt = json_decode(stripslashes($data['addGoodsCnt']), true);
                foreach ($setAddGoodsCnt[$data['sno']] as $aKey => $aVal) {
                    $tmpAddGoodsNoKey = array_search($aKey, $addGoodsNo);
                    $addGoodsCnt[$tmpAddGoodsNoKey] = $aVal;
                }
                $data['addGoodsCnt'] = json_encode($addGoodsCnt);
            }
            // 복수배송지 사용 && 배송방법 변경시 배송방법 재설정
            if (\Component\Order\OrderMultiShipping::isUseMultiShipping() == true || $postValue['isAdminMultiShippingFl'] === 'y') {
                if (empty($setDeliveryMethodFl[$data['sno']]['deliveryMethodFl']) === false) {
                    $data['deliveryMethodFl'] = $setDeliveryMethodFl[$data['sno']]['deliveryMethodFl'];
                }
                if (empty($setDeliveryCollectFl[$data['sno']]['deliveryCollectFl']) === false) {
                    $data['deliveryCollectFl'] = $setDeliveryCollectFl[$data['sno']]['deliveryCollectFl'];
                }
            }
            // stripcslashes 처리
            // json형태의 경우 json값안에 "이있는경우 stripslashes처리가 되어 json_decode에러가 나므로 json값중 "이 들어갈수있는경우 $aCheckKey에 해당 필드명을 추가해서 처리해주세요
            $aCheckKey = array('optionText');
            foreach ($data as $k => $v) {
                if (!in_array($k, $aCheckKey)) {
                    $data[$k] = gd_htmlspecialchars_stripslashes($v);
                }
            }

            // 전체상품 수량
            $this->cartGoodsCnt += $data['goodsCnt'];
            // 쿠폰사용이면
            if (!empty($data['memberCouponNo']) && $data['memberCouponNo'] != '') {
                // 쿠폰 기본설정값을 가져와서 회원등급만 사용설정이면 쿠폰정보를 제거 처리 & changePrice false처리
                $couponConfig = gd_policy('coupon.config');
                if ($couponConfig['chooseCouponMemberUseType'] == 'member') {
                    $this->setMemberCouponDelete($data['sno']);
                    $data['memberCouponNo'] = '';
                    $this->changePrice = false;
                }

                // 쿠폰 사용정보를 가져와서 쿠폰사용정보가 있으면 쿠폰설정에 따른 결제 방식 제한을 처리해준다
                $aTempMemberCouponNo = explode(INT_DIVISION, $data['memberCouponNo']);
                $coupon = \App::load('\\Component\\Coupon\\Coupon');
                foreach ($aTempMemberCouponNo as $val) {
                    if ($val != null) {
                        $aTempCouponInfo = $coupon->getMemberCouponInfo($val);
                        if ($aTempCouponInfo['couponUseAblePaymentType'] == 'bank') {
                            $data['payLimitFl'] = 'y';
                            if ($data['payLimit'] == '') {
                                $data['payLimit'] = 'gb';
                            } else {
                                $aTempPayLimit = explode(STR_DIVISION, $data['payLimit']);
                                $bankCheck = 'n';
                                foreach($aTempPayLimit as $limitVal) {
                                    if ($limitVal == 'gb') {
                                        $bankCheck = 'y';
                                    }
                                }
                                if ($bankCheck == 'n') {
                                    //$data['payLimit'] = STR_DIVISION . 'gb';
                                    $data['payLimit'] = array(false);
                                }
                            }
                        }
                    }
                }
            }

            // 기준몰 상품명 저장 (무조건 기준몰 상품명이 저장되도록)
            $data['goodsNmStandard'] = $data['goodsNm'];
            if($mallBySession && $globalData[$data['goodsNo']]) {
                $data = array_replace_recursive($data, array_filter(array_map('trim',$globalData[$data['goodsNo']])));
            }

            // 상품 카테고리 정보
            $goods = \App::load(\Component\Goods\Goods::class);
            $data['cateAllCd'] = $goods->getGoodsLinkCategory($data['goodsNo']);

            //매입처 관련 정보
            if(gd_is_plus_shop(PLUSSHOP_CODE_PURCHASE) === false || (gd_is_plus_shop(PLUSSHOP_CODE_PURCHASE) === true && !in_array($data['purchaseNo'],array_keys($purchaseData))))  {
                unset($data['purchaseNo']);
            }

            // 상품 삭제 여부에 따른 처리
            if ($data['delFl'] === 'y') {
                $_delCartSno[] = $data['sno'];
                unset($data);
                continue;
            } else {
                unset($data['delFl']);
            }

            // 해외배송비 선택시 기본무게에 해외배송비 조건의 무게를 더한다
            if (Globals::get('gGlobal.isFront') || $this->isAdminGlobal === true) {
                // 상품의 경우 기본 무게단위 설정에 의해 계산되기때문에 해당 단위를 가져와 별도 계산해야 함
                // 배송은 KG 단위로 적용되어진다.
                $weightConf = gd_policy('basic.weight');
                $rateWeight = ($weightConf['unit'] == 'g' ? 1000 : 1);
                $data['goodsWeight'] = ($data['goodsWeight'] > 0 ? ($data['goodsWeight'] / $rateWeight) : $this->overseasDeliveryPolicy['data']['basicWeight']);
                $this->totalDeliveryWeight['goods'] += ($data['goodsWeight'] * $data['goodsCnt']);
                $this->totalDeliveryWeight['total'] += ($data['goodsWeight'] * $data['goodsCnt']);
            }

            // 텍스트옵션 상품 정보
            $goodsOptionText = $goods->getGoodsOptionText($data['goodsNo']);
            if (empty($data['optionText']) === false && gd_isset($goodsOptionText)) {
                $optionTextKey = array_keys(json_decode($data['optionText'], true));
                foreach ($goodsOptionText as $goodsOptionTextInfo) {
                    if (in_array($goodsOptionTextInfo['sno'], $optionTextKey) === true) {
                        $data['optionTextInfo'][$goodsOptionTextInfo['sno']] = [
                            'optionSno' => $goodsOptionTextInfo['sno'],
                            'optionName' => $goodsOptionTextInfo['optionName'],
                            'baseOptionTextPrice' => $goodsOptionTextInfo['addPrice'],
                        ];
                    }
                }

            }

            // 추가 상품 정보
            $data['addGoodsMustFl'] = $mustFl = json_decode(gd_htmlspecialchars_stripslashes($data['addGoods']),true);
            if ($data['addGoodsFl'] === 'y' && empty($data['addGoodsNo']) === false) {
                $data['addGoodsNo'] = json_decode($data['addGoodsNo']);
                $data['addGoodsCnt'] = json_decode($data['addGoodsCnt']);
                if ($isAddGoodsDivision !== false) {
                    foreach($mustFl as $_key=>$val){
                        $key = $_key;
                        break;
                    }
                    $data['addGoodsMustFl'] = $mustFl[$key]['mustFl'];
                }
            } else {
                $data['addGoodsNo'] = '';
                $data['addGoodsCnt'] = '';
                if ($isAddGoodsDivision !== false) {
                    $data['addGoodsMustFl'] = '';
                }
            }

            // 추가 상품 필수 여부
            if ($data['addGoodsFl'] === 'y' && empty($data['addGoods']) === false) {
                foreach ($mustFl as $k => $v) {
                    if ($v['mustFl'] == 'y') {
                        if (is_array($data['addGoodsNo']) === false) {
                            $data['addGoodsSelectedFl'] = 'n';
                            break;
                        } else {
                            $addGoodsResult = array_intersect($v['addGoods'], $data['addGoodsNo']);
                            if (empty($addGoodsResult) === true) {
                                $data['addGoodsSelectedFl'] = 'n';
                                break;
                            }
                        }
                    }
                }
                unset($mustFl);
            }

            // 텍스트 옵션 정보 (sno, value)
            $data['optionTextSno'] = [];
            $data['optionTextStr'] = [];
            if ($data['optionTextFl'] === 'y' && empty($data['optionText']) === false) {
                $arrText = json_decode($data['optionText']);
                foreach ($arrText as $key => $val) {
                    $data['optionTextSno'][] = $key;
                    $data['optionTextStr'][$key] = $val;
                    unset($tmp);
                }
            }
            //unset($data['optionText']);

            // 텍스트옵션 필수 사용 여부
            if ($data['optionTextFl'] === 'y') {
                if (gd_isset($goodsOptionText)) {
                    foreach ($goodsOptionText as $k => $v) {
                        if ($v['mustFl'] == 'y' && !in_array($v['sno'], $data['optionTextSno'])) {
                            $data['optionTextEnteredFl'] = 'n';
                        }
                    }
                }
            }
            unset($optionText);

            // 상품 구매 가능 여부
            $data = $this->checkOrderPossible($data);

            //구매불가 대체 문구 관련
            if($data['goodsPermissionPriceStringFl'] =='y' && $data['goodsPermission'] !='all' && (($data['goodsPermission'] =='member'  && $this->isLogin === false) || ($data['goodsPermission'] =='group'  && !in_array($this->members['groupSno'],explode(INT_DIVISION,$data['goodsPermissionGroup']))))) {
                $data['goodsPriceString'] = $data['goodsPermissionPriceString'];
            }

            //품절일경우 가격대체 문구 설정
            if (($data['soldOutFl'] === 'y' || ($data['soldOutFl'] === 'n' && $data['stockFl'] === 'y' && ($data['totalStock'] <= 0 || $data['totalStock'] < $data['goodsCnt']))) && $soldoutDisplay['soldout_price'] !='price'){
                if($soldoutDisplay['soldout_price'] =='text' ) {
                    $data['goodsPriceString'] = $soldoutDisplay['soldout_price_text'];
                } else if($soldoutDisplay['soldout_price'] =='custom' ) {
                    $data['goodsPriceString'] = "<img src='".$soldoutDisplay['soldout_price_img']."'>";
                }
            }

            $data['goodsPriceDisplayFl'] = 'y';
            if (empty($data['goodsPriceString']) === false && $goodsPriceDisplayFl =='n') {
                $data['goodsPriceDisplayFl'] = 'n';
            }

            // 정책설정에서 품절상품 보관설정의 보관상품 품절시 자동삭제로 설정한 경우
            if ($this->cartPolicy['soldOutFl'] == 'n' && $data['orderPossibleCode'] == self::POSSIBLE_SOLD_OUT) {
                $_delCartSno[] = $data['sno'];
                unset($data);
                continue;
            }

            // 상품결제 수단에 따른 주문페이지 결제수단 표기용 데이터
            if ($data['payLimitFl'] == 'y' && gd_isset($data['payLimit'])) {
                $payLimit = explode(STR_DIVISION, $data['payLimit']);
                $data['payLimit'] = $payLimit;

                if (is_array($payLimit) && $this->payLimit) {
                    $this->payLimit = array_intersect($this->payLimit, $payLimit);
                    if (empty($this->payLimit) === true) {
                        $this->payLimit = ['false'];
                    }
                } else {
                    $this->payLimit = $payLimit;
                }
            }

            // 비회원시 담은 상품과 회원로그인후 담은 상품이 중복으로 있는경우 재고 체크
            $data['duplicationGoods'] = 'n';
            if (isset($tmpStock[$data['goodsNo']][$data['optionSno']][$data['optionText']]) === false) {
                $tmpStock[$data['goodsNo']][$data['optionSno']][$data['optionText']] = $data['goodsCnt'];
            } else {
                $data['duplicationGoods'] = 'y';
                $chkStock = $tmpStock[$data['goodsNo']][$data['optionSno']][$data['optionText']] + $data['goodsCnt'];
                if ($data['stockFl'] == 'y' && $data['stockCnt'] < $chkStock) {
                    $this->orderPossible = false;
                    $data['stockOver'] = 'y';
                }
            }

            // 상품구분 초기화 (상품인지 추가상품인지?)
            $data['goodsType'] = 'goods';

            // 상품 이미지 처리 @todo 상품 사이즈 설정 값을 가지고 와서 이미지 사이즈 변경을 할것

            // 세로사이즈고정 체크
            $imageSize = SkinUtils::getGoodsImageSize('list');
            $imageConf = gd_policy('goods.image');

            if (Request::isMobile() || $imageConf['imageType'] != 'fixed') {
                $imageSize['size1'] = '40'; // 기존 사이즈
                $imageSize['hsize1'] = '';
            }

            // 상품 이미지 처리
            if ($data['onlyAdultFl'] == 'y' && gd_check_adult() === false && $data['onlyAdultImageFl'] =='n') {
                if (Request::isMobile()) {
                    $data['goodsImageSrc'] = "/data/icon/goods_icon/only_adult_mobile.png";
                } else {
                    $data['goodsImageSrc'] = "/data/icon/goods_icon/only_adult_pc.png";
                }

                $data['goodsImage'] = SkinUtils::makeImageTag($data['goodsImageSrc'], $imageSize['size1']);
            } else {
                $data['goodsImage'] = gd_html_preview_image($data['imageName'], $data['imagePath'], $data['imageStorage'], $imageSize['size1'], 'goods', $data['goodsNm'], 'class="imgsize-s"', false, false, $imageSize['hsize1']);
            }



            unset($data['imageStorage'], $data['imagePath'], $data['imageName'], $data['imagePath']);

            $data['goodsMileageExcept'] = 'n';
            $data['couponBenefitExcept'] = 'n';
            $data['memberBenefitExcept'] = 'n';

            //타임세일 할인 여부
            $data['timeSaleFl'] = false;
            if (gd_is_plus_shop(PLUSSHOP_CODE_TIMESALE) === true && Request::post()->get('mode') !== 'cartEstimate') {

                $timeSale = \App::load('\\Component\\Promotion\\TimeSale');
                $timeSaleInfo = $timeSale->getGoodsTimeSale($data['goodsNo']);
                if ($timeSaleInfo) {
                    $data['timeSaleFl'] = true;
                    if ($timeSaleInfo['mileageFl'] == 'n') {
                        $data['goodsMileageExcept'] = "y";
                    }
                    if ($timeSaleInfo['couponFl'] == 'n') {
                        $data['couponBenefitExcept'] = "y";

                        // 타임세일 상품적용 쿠폰 사용불가 체크
                        if (empty($data['memberCouponNo']) === false) {
                            $exceptCouponNo[$data['sno']] = $data['memberCouponNo'];
                        }
                    }
                    if ($timeSaleInfo['memberDcFl'] == 'n') {
                        $data['memberBenefitExcept'] = "y";
                    }
                    if ($data['goodsPrice'] > 0) {
                        // 타임세일 할인금액
                        $data['timeSalePrice'] = gd_number_figure((($timeSaleInfo['benefit'] / 100) * $data['goodsPrice']), $truncGoods['unitPrecision'], $truncGoods['unitRound']);

                        $data['goodsPrice'] = gd_number_figure($data['goodsPrice'] - (($timeSaleInfo['benefit'] / 100) * $data['goodsPrice']), $truncGoods['unitPrecision'], $truncGoods['unitRound']);
                    }
                    //상품 옵션가(일체형,분리형) 타임세일 할인율 적용 ( 텍스트 옵션가 / 추가상품가격 제외 )
                    if($data['optionFl'] === 'y'){
                        // 타임세일 할인금액
                        $data['timeSalePrice'] = gd_number_figure($data['timeSalePrice'] + (($timeSaleInfo['benefit'] / 100) * $data['optionPrice']), $truncGoods['unitPrecision'], $truncGoods['unitRound']);

                        $data['optionPrice'] = gd_number_figure($data['optionPrice'] - (($timeSaleInfo['benefit'] / 100) * $data['optionPrice']), $truncGoods['unitPrecision'], $truncGoods['unitRound']);
                    }
                }
            }

            // 혜택제외 체크 (쿠폰)
            $exceptBenefit = explode(STR_DIVISION, $data['exceptBenefit']);
            $exceptBenefitGroupInfo = explode(INT_DIVISION, $data['exceptBenefitGroupInfo']);
            if (in_array('coupon', $exceptBenefit) === true && ($data['exceptBenefitGroup'] == 'all' || ($data['exceptBenefitGroup'] == 'group' && in_array($this->_memInfo['groupSno'], $exceptBenefitGroupInfo) === true))) {
                if (empty($data['memberCouponNo']) === false) {
                    $exceptCouponNo[$data['sno']] = $data['memberCouponNo'];
                }
                $data['couponBenefitExcept'] = "y";
            }

            //배송방식에 관한 데이터
            $data['goodsDeliveryMethodFl'] = $data['deliveryMethodFl'];
            $data['goodsDeliveryMethodFlText'] = gd_get_delivery_method_display($data['deliveryMethodFl']);
            $data['deliveryMethodVisitArea'] = '';
            //if ($data['deliveryMethodFl'] == 'visit') {
            $deliveryData = $delivery->getSnoDeliveryBasic($data['deliverySno']);
            $data['deliveryMethodVisitArea'] = $delivery->getVisitAddress($data['deliverySno'], true);
            $data['goodsDeliveryFl'] = $deliveryData['goodsDeliveryFl'];
            $data['sameGoodsDeliveryFl'] = $deliveryData['sameGoodsDeliveryFl'];
            //}

            // 회원 추가 할인 여부 설정 (적용제외 대상이 있는 경우 적용 제외)
            $data = $this->getMemberDcFlInfo($data);

            // 해외배송의 배송비조건 일련번호 추출 후 기존 상품데이터에 배송지조건 일괄 변경
            if ($this->isGlobalFront($address)) {
                $data['deliverySno'] = $overseasDeliverySno;
            }

            $tmpOptionName = [];
            for ($optionKey = 1; $optionKey <= 5; $optionKey++) {
                if (empty($data['optionValue' . $optionKey]) === false) {
                    $tmpOptionName[] = $data['optionValue' . $optionKey];
                }
            }
            $data['optionNm'] = @implode('/', $tmpOptionName);
            unset($tmpOptionName);

            if (in_array($data['goodsNo'], $goodsKey) === false) {
                $goodsKey[] = $data['goodsNo'];
            }
            $data['goodsKey'] = array_search($data['goodsNo'], $goodsKey);

            // 현재 주문 중인 장바구니 SNO
            $this->cartSno[] = $data['sno'];

            // 쇼핑 계속하기 주소 처리
            if ($data['cateCd'] && empty($this->shoppingUrl) === true) {
                $this->shoppingUrl = $data['cateCd'];
            }

            if (\Component\Order\OrderMultiShipping::isUseMultiShipping() === true && $deliveryBasicInfoFl === true && empty($this->deliveryBasicInfo[$data['deliverySno']]) === true) {
                $this->deliveryBasicInfo[$data['deliverySno']] = $delivery->getSnoDeliveryBasic($data['deliverySno']);
                $this->deliveryBasicInfo[$data['deliverySno']]['deliveryMethodFl'] = array_filter(explode(STR_DIVISION, $this->deliveryBasicInfo[$data['deliverySno']]['deliveryMethodFl']));
            }

            if (in_array($data['goodsNo'], $prevGoodsNo) === false) {
                $data['equalGoodsNo'] = true;
                $prevGoodsNo[] = $data['goodsNo'];
            }

            $getData[] = $data;
            unset($data);
        }

        if ($isCouponCheck === true && empty($exceptCouponNo) === false && (MemberUtil::isLogin() || ($this->isWrite && empty($this->_memInfo) === false))) {
            if ($this->setCartCouponReset($exceptCouponNo) === true) {
                throw new AlertRedirectException(__('쿠폰 할인/적립 혜택이 변경된 상품이 포함되어 있으니 장바구니에서 확인 후 다시 주문해주세요.'), null, null, '../order/cart.php');
            }
        }

        // 삭제 상품이 있는 경우 해당 장바구니 삭제
        if (empty($_delCartSno) === false) {
            $this->setCartDelete($_delCartSno);
        }

        // 쇼핑계속하기 버튼
        if (empty($this->shoppingUrl) === true) {
            $this->shoppingUrl = URI_OVERSEAS_HOME;
        } else {
            $this->shoppingUrl = URI_OVERSEAS_HOME . 'goods/goods_list.php?cateCd=' . $this->shoppingUrl;
        }

        // 해외배송시 EMS조건에 30KG 이상인 경우 체크
        if ($this->overseasDeliveryPolicy['data']['standardFl'] === 'ems' && $this->totalDeliveryWeight['total'] > 30) {
            $this->emsDeliveryPossible = false;
        }

        //회원구매제한 체크 order.php 의 useSettleKind 함수에서도 한번 체크함
        if (!in_array('false', $this->payLimit)) { // 상품별 결제수단 체크에서 결제가능한 결제수단이 없는것으로 나왔을때는 처리필요없음
            if($this->isWrite === true){
                if (empty($this->_memInfo) === false && !in_array('gb', $this->_memInfo['settleGb'])) {
                    if (empty($this->payLimit)) {
                        $this->payLimit = $this->_memInfo['settleGb'];
                    } else {
                        if($this->_memInfo['settleGb'] != 'all') {
                            if (is_array($this->_memInfo['settleGb']) === false) {
                                $settleGb = Util::matchSettleGbDataToString($this->_memInfo['settleGb']);
                            } else {
                                $settleGb = $this->_memInfo['settleGb'];
                            }
                            $payLimit = array_intersect($settleGb, $this->payLimit);
                            if (count($this->payLimit) > 0 && !in_array('false', $this->payLimit) && count($payLimit) == 0) {
                                $this->payLimit = ['false'];
                            } else {
                                $this->payLimit = $payLimit;
                            }
                        }
                    }
                }
            }
            else {
                if (empty($this->_memInfo) === false) {
                    if (empty($this->payLimit)) {
                        $this->payLimit = $this->_memInfo['settleGb'];
                    } else {
                        $payLimit = array_intersect($this->_memInfo['settleGb'], $this->payLimit);
                        if (count($this->payLimit) > 0 && !in_array('false', $this->payLimit) && count($payLimit) == 0) {
                            $this->payLimit = ['false'];
                        } else {
                            $this->payLimit = $payLimit;
                        }
                    }
                }
            }
        }

        // 장바구니 상품에 대한 계산된 정보
        $getCart = $this->getCartDataInfo($getData, $postValue);

        // 글로벌 해외배송 조건에 따라서 처리
        if (Globals::get('gGlobal.isFront') || $this->isAdminGlobal === true) {
            if ($address !== null) {
                $getCart = $this->getOverseasDeliveryDataInfo($getCart, array_column($getData, 'deliverySno'), $address);
            }
        } else {
            // 복수배송지 사용시 배송정보 재설정
            if ((\Component\Order\OrderMultiShipping::isUseMultiShipping() === true && $postValue['multiShippingFl'] == 'y') || $postValue['isAdminMultiShippingFl'] === 'y') {
                foreach ($postValue['orderInfoCdData'] as $key => $val) {
                    $tmpGetCart = [];
                    $tmpAllGetKey = [];
                    $tmpDeliverySnos = [];
                    foreach ($val as $tVal) {
                        $tmpScmNo = $this->multiShippingOrderInfo[$tVal]['scmNo'];
                        $tmpDeliverySno = $this->multiShippingOrderInfo[$tVal]['deliverySno'];
                        $tmpGetKey = $this->multiShippingOrderInfo[$tVal]['getKey'];
                        $tmpAllGetKey[] = $tmpGetKey;
                        $tmpDeliverySnos[] = $tmpDeliverySno;

                        $tmpGetCart[$tmpScmNo][$tmpDeliverySno][$tmpGetKey] = $getCart[$tmpScmNo][$tmpDeliverySno][$tmpGetKey];
                    }
                    if ($key > 0) {
                        $multiAddress = $postValue['receiverAddressAdd'][$key];
                    } else {
                        $multiAddress = $address;
                    }

                    $tmpGetCart = $this->getDeliveryDataInfo($tmpGetCart, $tmpDeliverySnos, $multiAddress, $postValue['multiShippingFl'], $key);
                    foreach ($tmpGetCart as $sKey => $sVal) {
                        foreach ($sVal as $dKey => $dVal) {
                            foreach ($dVal as $getKey => $getVal) {
                                if (empty($tmpGetCart[$sKey][$dKey][$getKey]) === false) {
                                    $getCart[$sKey][$dKey][$getKey] = $tmpGetCart[$sKey][$dKey][$getKey];
                                }
                            }

                        }
                    }
                    unset($tmpGetCart);
                }
            } else {
                $getCart = $this->getDeliveryDataInfo($getCart, array_column($getData, 'deliverySno'), $address);
            }
        }

        // 장바구니 SCM 정보
        if (is_array($getCart)) {
            $scmClass = \App::load(\Component\Scm\Scm::class);
            $this->cartScmCnt = count($getCart);
            $this->cartScmInfo = $scmClass->getCartScmInfo(array_keys($getCart));
        }

        // 회원 할인 총 금액
        if ($this->getChannel() != 'naverpay') {
            $this->totalSumMemberDcPrice = $this->totalMemberDcPrice + $this->totalMemberOverlapDcPrice;
        }
        // 총 부가세율
        $this->totalVatRate = gd_tax_rate($this->totalGoodsPrice, $this->totalPriceSupply);

        // 비과세 설정에 따른 세금계산서 출력 여부
        if ($this->taxInvoice === true && $this->taxGoodsChk === false) {
            $this->taxInvoice = false;
        }

        // 총 결제 금액 (상품별 금액 + 배송비 - 상품할인 - 회원할인 - 사용마일리지(X) - 상품쿠폰할인 - 주문쿠폰할인(X) - 마이앱할인 : 여기서는 장바구니에 있는 상품에 대해서만 계산하므로 결제 예정금액임)
        // 주문관련 할인금액 및 마일리지/예치금 사용은 setOrderSettleCalculation에서 별도로 계산됨

        $this->totalSettlePrice = $this->totalGoodsPrice + $this->totalDeliveryCharge - $this->totalGoodsDcPrice - $this->totalSumMemberDcPrice - $this->totalCouponGoodsDcPrice;

        // 마이앱 사용에 따른 분기 처리
        if ($this->useMyapp) {
            $this->totalSettlePrice -= $this->totalMyappDcPrice;
        }

        if($this->totalSettlePrice < 0 ) $this->totalSettlePrice = 0;

        // 총 적립 마일리지 (상품별 총 상품 마일리지 + 회원 그룹 총 마일리지 + 총 상품 쿠폰 마일리지 + 총 주문 쿠폰 적립 금액 : 여기서는 장바구니에 있는 상품에 대해서만 계산하므로 적립 예정금액임)
        $this->totalMileage = $this->totalGoodsMileage + $this->totalMemberMileage + $this->totalCouponGoodsMileage + $this->totalCouponOrderMileage;

        // 주문에 추가상품 분리데이터를 저장하기 위해 별도 생성 (추가상품 안분까지 적용된 데이터를 가지고와 처리)
        if ($isAddGoodsDivision !== false) {
            // 최종 반환할 $getCart 변수 재설정
            $tmpGetCart = [];

            foreach ($getCart as $sKey => $sVal) {
                foreach ($sVal as $dKey => $dVal) {
                    foreach ($dVal as $gKey => $gVal) {
                        // 기본 상품에 속해있던 추가상품 관련 데이터 정리
                        if ($gVal['price']['goodsPriceSubtotal'] > 0) {
                            $gVal['price']['goodsPriceSubtotal'] -= $gVal['price']['addGoodsPriceSum'];
                            if ($gVal['price']['goodsPriceSubtotal'] < 0) $gVal['price']['goodsPriceSubtotal'] = 0;
                            $gVal['price']['addGoodsPriceSum'] = 0;
                        }

                        $gVal['price']['goodsPriceTotal'] = ($gVal['price']['goodsPriceSum'] + $gVal['price']['optionPriceSum'] + $gVal['price']['optionTextPriceSum']) - ($gVal['price']['goodsDcPrice'] + $gVal['price']['goodsMemberDcPrice'] + $gVal['price']['goodsMemberOverlapDcPrice'] + $gVal['price']['goodsCouponGoodsDcPrice']);

                        // 마이앱 사용에 따른 분기 처리
                        if ($this->useMyapp) {
                            $gVal['price']['goodsPriceTotal'] -= $gVal['price']['myappDcPrice'];
                        }

                        // 총 상품 무게 계산
                        $gVal['goodsWeight'] = $gVal['goodsWeight'] * $gVal['goodsCnt'];

                        // 총 상품 용량 계산
                        $gVal['goodsVolume'] = $gVal['goodsVolume'] * $gVal['goodsCnt'];

                        // 추가상품 변수에 담고 언셋
                        $addGoods = $gVal['addGoods'];
                        unset($gVal['addGoods']);

                        // 기존 상품정보에 추가 내용
                        $gVal['goodsType'] = self::CART_GOODS_TYPE_GOODS;

                        // 원래 상품정보 그대로 추가
                        $tmpGetCart[$sKey][$dKey][] = $gVal;

                        // 추가상품 배열화
                        if (empty($addGoods) === false) {
                            // 추가상품을 상품화시켜 담을 배열
                            foreach ($addGoods as $aKey => $aVal) {
                                // 초기화
                                $tmpAddGoods = [];

                                // 부모 상품의 기본 정보 초기화
                                $tmpAddGoods['goodsType'] = self::CART_GOODS_TYPE_ADDGOODS;
                                $tmpAddGoods['optionTextFl'] = 'n';
                                $tmpAddGoods['goodsDiscountFl'] = 'n';
                                $tmpAddGoods['goodsDiscount'] = 0;
                                $tmpAddGoods['goodsDiscountUnit'] = '';
                                $tmpAddGoods['couponBenefitExcept'] = 'y';

                                // 부모 상품의 설정을 상속받아 설정
                                $tmpAddGoods['sno'] = $gVal['sno'];
                                $tmpAddGoods['siteKey'] = $gVal['siteKey'];
                                $tmpAddGoods['directCart'] = $gVal['directCart'];
                                $tmpAddGoods['memNo'] = $gVal['memNo'];
                                $tmpAddGoods['deliveryCollectFl'] = $gVal['deliveryCollectFl'];
                                $tmpAddGoods['tmpOrderNo'] = $gVal['tmpOrderNo'];
                                $tmpAddGoods['goodsDisplayMobileFl'] = $gVal['goodsDisplayMobileFl'];
                                $tmpAddGoods['goodsSellFl'] = $gVal['goodsSellFl'];
                                $tmpAddGoods['goodsSellMobileFl'] = $gVal['goodsSellMobileFl'];
                                $tmpAddGoods['cateCd'] = $gVal['cateCd'];
                                $tmpAddGoods['deliverySno'] = $gVal['deliverySno'];
                                $tmpAddGoods['memberBenefitExcept'] = $gVal['memberBenefitExcept'];
                                $tmpAddGoods['goodsMileageExcept'] = $gVal['goodsMileageExcept'];
                                $tmpAddGoods['mileageFl'] = $gVal['mileageFl'];
                                $tmpAddGoods['mileageGoods'] = $gVal['mileageGoods'];
                                $tmpAddGoods['mileageGoodsUnit'] = $gVal['mileageGoodsUnit'];
                                $tmpAddGoods['addDcFl'] = $gVal['addDcFl'];
                                $tmpAddGoods['overlapDcFl'] = $gVal['overlapDcFl'];
                                $tmpAddGoods['orderPossible'] = $gVal['orderPossible'];
                                $tmpAddGoods['payLimitFl'] = $gVal['payLimitFl'];
                                $tmpAddGoods['payLimit'] = $gVal['payLimit'];
                                $tmpAddGoods['goodsPermission'] = $gVal['goodsPermission'];
                                $tmpAddGoods['goodsPermissionGroup'] = $gVal['goodsPermissionGroup'];
                                $tmpAddGoods['onlyAdultFl'] = $gVal['onlyAdultFl'];
                                $tmpAddGoods['goodsDeliveryFl'] = $gVal['goodsDeliveryFl'];
                                $tmpAddGoods['goodsDeliveryFixFl'] = $gVal['goodsDeliveryFixFl'];
                                $tmpAddGoods['goodsDeliveryMethod'] = $gVal['goodsDeliveryMethod'];
                                $tmpAddGoods['deliveryMethodFl'] = $gVal['deliveryMethodFl'];
                                $tmpAddGoods['goodsDeliveryCollectFl'] = $gVal['goodsDeliveryCollectFl'];
                                $tmpAddGoods['goodsDeliveryWholeFreeFl'] = $gVal['goodsDeliveryWholeFreeFl'];
                                $tmpAddGoods['goodsDeliveryTaxFreeFl'] = $gVal['goodsDeliveryTaxFreeFl'];
                                $tmpAddGoods['goodsDeliveryTaxPercent'] = $gVal['goodsDeliveryTaxPercent'];

                                // 추가상품에서 가져온 정보 저장
                                $tmpAddGoods['goodsNo'] = $tmpAddGoods['addGoodsNo'] = $aVal['addGoodsNo'];
                                $tmpAddGoods['goodsCnt'] = $aVal['addGoodsCnt'];
                                $tmpAddGoods['goodsNm'] = $aVal['addGoodsNm'];
                                if($mallBySession && $aVal['addGoodsNmStandard']) {
                                    $tmpAddGoods['goodsNmStandard'] = $aVal['addGoodsNmStandard'];
                                }
                                $tmpAddGoods['scmNo'] = $aVal['scmNo'];
                                $tmpAddGoods['purchaseNo'] = $aVal['purchaseNo'];
                                $tmpAddGoods['commission'] = $aVal['commission'];
                                $tmpAddGoods['goodsCd'] = $aVal['goodsCd'];
                                $tmpAddGoods['goodsModelNo'] = $aVal['goodsModelNo'];
                                $tmpAddGoods['brandCd'] = $aVal['brandCd'];
                                $tmpAddGoods['makerNm'] = $aVal['makerNm'];
                                $tmpAddGoods['goodsDisplayFl'] = $aVal['viewFl'];
                                $tmpAddGoods['stockUseFl'] = $aVal['stockUseFl'];
                                $tmpAddGoods['stockCnt'] = $aVal['stockCnt'];
                                $tmpAddGoods['soldOutFl'] = $aVal['soldOutFl'];
                                $tmpAddGoods['taxFreeFl'] = $aVal['taxFreeFl'];
                                $tmpAddGoods['taxPercent'] = $aVal['taxPercent'];
                                $tmpAddGoods['goodsImage'] = $aVal['addGoodsImage'];
                                $tmpAddGoods['parentMustFl'] = $gVal['addGoodsMustFl'];
                                $tmpAddGoods['parentGoodsNo'] = $gVal['goodsNo'];
                                $tmpAddGoods['price']['goodsPrice'] = $aVal['addGoodsPrice'];
                                $tmpAddGoods['price']['costPrice'] = $aVal['addCostGoodsPrice'];
                                $tmpAddGoods['price']['goodsMemberDcPrice'] = $aVal['addGoodsMemberDcPrice'];
                                $tmpAddGoods['price']['goodsMemberOverlapDcPrice'] = $aVal['addGoodsMemberOverlapDcPrice'];
                                $tmpAddGoods['price']['goodsCouponGoodsDcPrice'] = $aVal['addGoodsCouponGoodsDcPrice'];
                                $tmpAddGoods['price']['goodsPriceSum'] = ($aVal['addGoodsPrice'] * $aVal['addGoodsCnt']);
                                $tmpAddGoods['price']['goodsPriceSubtotal'] = $tmpAddGoods['price']['goodsPriceSum'];
//                                $tmpAddGoods['price']['goodsPriceSubtotal'] = ($tmpAddGoods['price']['goodsPriceSum'] - $aVal['addGoodsMemberDcPrice'] - $aVal['addGoodsMemberOverlapDcPrice']);
                                $tmpAddGoods['price']['goodsPriceTotal'] = ($tmpAddGoods['price']['goodsPriceSum'] - $aVal['addGoodsMemberDcPrice'] - $aVal['addGoodsMemberOverlapDcPrice'] - $aVal['addGoodsCouponGoodsDcPrice']);
                                $tmpAddGoods['mileage']['goodsGoodsMileage'] = $aVal['addGoodsGoodsMileage'];
                                $tmpAddGoods['mileage']['goodsMemberMileage'] = $aVal['addGoodsMemberMileage'];
                                $tmpAddGoods['mileage']['goodsCouponGoodsMileage'] = $aVal['addGoodsCouponGoodsMileage'];

                                // 상품 옵션 처리
                                $tmpAddGoods['option'] = [];
                                if (empty($aVal['optionNm']) === false) {
                                    $tmp = explode(STR_DIVISION, $aVal['optionNm']);
                                    for ($i = 0; $i < 1; $i++) {
                                        $tmpAddGoods['option'][$i]['optionName'] = '';
                                        $tmpAddGoods['option'][$i]['optionValue'] = $aVal['optionNm'];
                                    }
                                    unset($tmp);
                                }
                                unset($tmpAddGoods['optionName']);

                                // 재정의 배열에 추가
                                $tmpGetCart[$sKey][$dKey][] = $tmpAddGoods;
                                unset($tmpAddGoods);
                            }
                        }
                    }
                }
            }
            unset($getCart);

            // 장바구니
            $getCart = $tmpGetCart;
        }

        unset($getData, $arrTmp);

        return $getCart;
    }

    /**
     * 장바구니 상품 정보 (배송비와 공급사 기준으로 재정의)
     * 현재 장바구니에 담긴 상품의 정보를 가공함
     *
     * @param array $getData   장바구니 기본 정보
     * @param array $postValue 주문정보
     *
     * @return array 가공된 장바구니 상품 정보
     */
    protected function getCartDataInfo($getData, $postValue = [])
    {
        // getData -> 장바구니 정보가 한개만 넘어온것인지 여러개(전체 또는 다수 선택)정보가 넘어온것인지 구분값
        $isAllFl = (count($getData) > 1) ? 'T' : 'F';

        // 상품데이터를 이용해 상품번호, 배송번호, 추가상품, 텍스트옵션, 회원쿠폰번호 별도 추출
        foreach (ArrayUtils::removeEmpty(array_column($getData, 'addGoodsNo')) as $val) {
            foreach ($val as $cKey => $cVal) {
                $arrTmp['addGoodsNo'][] = $cVal;
            }
        }
        foreach (ArrayUtils::removeEmpty(array_column($getData, 'optionTextSno')) as $key => $val) {
            foreach ($val as $cKey => $cVal) {
                $arrTmp['optionText'][] = $cVal;
            }
        }
        $couponPolicy = gd_policy('coupon.config');

        // 추가 상품 디비 정보
        $getAddGoods = $this->getAddGoodsInfo($arrTmp['addGoodsNo']);

        // 텍스트 옵션 디비 정보
        $getOptionText = $this->getOptionTextInfo($arrTmp['optionText']);

        // 반환할 장바구니 데이터 초기화
        $getCart = [];

        // 과세/비과세 상품 존재 여부 체크 초기화
        $this->taxGoodsChk = true;

        // 장바구니 갯수
        $cartCnt = 0;

        $goodsPriceInfo = [];

        $goodsCouponData = [];


        // 상품혜택관리 치환코드 생성
        if(!is_object($goodsBenefit)){
            $goodsBenefit = \App::load('\\Component\\Goods\\GoodsBenefit');
        }
        // 상품쿠폰 주문서페이지 변경 제한안함일 때 && 수기주문이 아닐 경우
        if($couponPolicy['productCouponChangeLimitType'] == 'n' && $this->isWrite === false) {
            $goodsCouponForTotalPrice = [];
            if((Request::getFileUri() == 'cart_ps.php') && in_array($postValue['mode'], $this->cartPsPassModeArray) == true) {
                if (empty($postValue['cartAllSno']) === false) { // cart 전체 sno 넘어왔을 경우
                    $getProductReturn = $this->getCartProductCouponDataInfo($postValue); // 전체 카트 sno를 통해 카트+상품+옵션 조합 배열(가격계산위해)
                    $goodsCouponForTotalPrice = $this->getProductCouponGoodsAllPrice($getProductReturn); // 상품쿠폰이 주문기준일 때 주문상품 전체 가격
                } else {
                    $goodsCouponForTotalPrice = $this->getProductCouponGoodsAllPrice($getData); // 상품쿠폰이 주문기준일 때 주문상품 전체 가격
                }
            } else {
                $goodsCouponForTotalPrice = $this->getProductCouponGoodsAllPrice($getData); // 상품쿠폰이 주문기준일 때 주문상품 전체 가격
            }
        }

        //상품 옵션 상태 코드 불러오기
        $request = \App::getInstance('request');
        $mallSno = $request->get()->get('mallSno', 1);
        $code = \App::load('\\Component\\Code\\Code',$mallSno);
        $deliverySell = $code->getGroupItems('05002');
        $deliverySellNew['y'] = $deliverySell['05002001']; //정상은 코드 변경
        $deliverySellNew['n'] = $deliverySell['05002002']; //품절은 코드 변경
        unset($deliverySell['05002001']);
        unset($deliverySell['05002002']);
        $optionSellCode = array_merge($deliverySellNew, $deliverySell);

        $deliveryReason = $code->getGroupItems('05003');
        $deliveryReasonNew['normal'] = $deliveryReason['05003001']; //정상은 코드 변경
        unset($deliveryReason['05003001']);
        $optionDeliveryReasonCode = array_merge($deliveryReasonNew, $deliveryReason);

        // 장바구니 상품을 다시 설정을 함 (1차 배열 SCM별, 2차 배열 배송방법)
        // 마일리지 적립 정책 : 절사처리{(판매가 * 수량) + (옵션가 * 수량) + (텍스트옵션가 * 수량) + (추가상품가 * 수량) + (추가상품가 * 수량) + (추가상품가 * 수량) ...}
        // 회원 할인 정책 : 절사처리{(판매가 * 수량) + (옵션가 * 수량) + (텍스트옵션가 * 수량) + (추가상품가 * 수량) + (추가상품가 * 수량) + (추가상품가 * 수량) ...}
        // 쿠폰 정책 : 절사처리{(판매가 * 수량) + (옵션가 * 수량) + (텍스트옵션가 * 수량) + (추가상품가 * 수량) + (추가상품가 * 수량) + (추가상품가 * 수량) ...}
        $tmpMemberDcInfo = $tmpMileageInfo = [];

        foreach ($getData as $dataKey => $dataVal) {
            // 각 상품별 가격 설정 (과세/비과세 설정에 따른 금액 계산, (판매가 * 수량), (옵션가 * 수량))
            $getData[$dataKey]['price']['fixedPrice'] = $getData[$dataKey]['fixedPrice'];
            $getData[$dataKey]['price']['costPrice'] = $getData[$dataKey]['costPrice'];
            $getData[$dataKey]['price']['baseGoodsPrice'] = $getData[$dataKey]['goodsPrice'];
            $getData[$dataKey]['price']['baseOptionPrice'] = $getData[$dataKey]['optionPrice'];
            $getData[$dataKey]['price']['baseOptionTextPrice'] = 0;
            $getData[$dataKey]['price']['goodsPrice'] = $getData[$dataKey]['goodsPrice'];
            $getData[$dataKey]['price']['optionPrice'] = $getData[$dataKey]['optionPrice'];
            $getData[$dataKey]['price']['optionCostPrice'] = $getData[$dataKey]['optionCostPrice'];
            $getData[$dataKey]['price']['optionTextPrice'] = 0;
            $getData[$dataKey]['price']['goodsPriceSum'] = $getData[$dataKey]['goodsPrice'] * $dataVal['goodsCnt'];
            $getData[$dataKey]['price']['optionPriceSum'] = $getData[$dataKey]['optionPrice'] * $dataVal['goodsCnt'];
            $getData[$dataKey]['price']['optionTextPriceSum'] = 0;
            $getData[$dataKey]['price']['addGoodsPriceSum'] = 0;
            $getData[$dataKey]['price']['addGoodsVat']['supply'] = 0;
            $getData[$dataKey]['price']['addGoodsVat']['tax'] = 0;
            $getData[$dataKey]['price']['goodsDcPrice'] = 0;
            $getData[$dataKey]['price']['memberDcPrice'] = 0;
            $getData[$dataKey]['price']['memberOverlapDcPrice'] = 0;
            $getData[$dataKey]['price']['couponGoodsDcPrice'] = 0;

            // 마이앱 사용에 따른 분기 처리
            if ($this->useMyapp) {
                $getData[$dataKey]['price']['myappDcPrice'] = 0;
            }

            $getData[$dataKey]['price']['goodsDeliveryPrice'] = 0;
            $getData[$dataKey]['price']['timeSalePrice'] = $getData[$dataKey]['timeSalePrice'];
        }

        foreach ($getData as $dataKey => $dataVal) {
            // 기본 설정
            $scmNo = (int)$dataVal['scmNo']; // SCM ID
            $arrScmNo[] = $scmNo; // 장바구니 SCM 정보
            $goodsNo = $dataVal['goodsNo']; // 상품 번호
            $optionSno = $dataVal['optionSno'];
            $deliverySno = $dataVal['deliverySno']; // 배송 정책
            $taxFreeFl = $dataVal['taxFreeFl'];
            $taxPercent = $taxFreeFl == 'f' ? 0 : $dataVal['taxPercent'];
            $memberDcFl = true;

            // 상품할인(개별,혜택) DB 저장 데이터 가공
            if($dataVal['goodsDiscountFl'] == 'y' || $dataVal['goodsBenefitSetFl'] == 'y') {
                $getData[$dataKey]['goodsDiscountInfo'] = $goodsBenefit->setBenefitOrderGoodsData($dataVal, 'discount');
            }
            // 상품적립(통합, 개별) DB 저장 데이터 가공
            $getData[$dataKey]['goodsMileageAddInfo'] = $goodsBenefit->setBenefitOrderGoodsData($dataVal, 'mileage');

            if ((\Component\Order\OrderMultiShipping::isUseMultiShipping() == true && $postValue['multiShippingFl'] == 'y') || $postValue['isAdminMultiShippingFl'] === 'y') {
                if (empty($tmpMemberDcInfo[$goodsNo][$optionSno]) === true) {
                    $tmpMemberDcInfo[$goodsNo][$optionSno] = json_decode($postValue['memberDcInfo'][$goodsNo][$optionSno], true);
                }
                if (empty($tmpMileageInfo[$goodsNo][$optionSno]) === true) {
                    $tmpMileageInfo[$goodsNo][$optionSno] = json_decode($postValue['mileageInfo'][$goodsNo][$optionSno], true);
                }
            }

            $exceptBenefit = explode(STR_DIVISION, $dataVal['exceptBenefit']);
            $exceptBenefitGroupInfo = explode(INT_DIVISION, $dataVal['exceptBenefitGroupInfo']);

            if (empty($this->couponApplyOrderNo) === false && $couponPolicy['couponUseType'] == 'y' && $couponPolicy['chooseCouponMemberUseType'] == 'coupon') {
                $memberDcFl = false;
            }

            // 제외 혜택 대상 여부
            $exceptBenefitFl = false;
            if ($dataVal['exceptBenefitGroup'] == 'all' || ($dataVal['exceptBenefitGroup'] == 'group' && in_array($this->_memInfo['groupSno'], $exceptBenefitGroupInfo) === true)) {
                $exceptBenefitFl = true;
                //회원 추가할인 제외
                if (in_array('add', $exceptBenefit) === true) $getData[$dataKey]['addDcFl'] = false;
                //회원 중복할인 제외
                if (in_array('overlap', $exceptBenefit) === true) $getData[$dataKey]['addDcFl'] = false;
            }

            // 과세상품이 있는지를 체크 과세율이 면세 또는 10%(고정) 일경우에 세금계산서 신청 가능
            if ((int)$taxPercent != '10' && (int)$taxPercent != '0') {
                $this->taxGoodsChk = false;
            }

            unset($getData[$dataKey]['fixedPrice'], $getData[$dataKey]['costPrice'], $getData[$dataKey]['goodsPrice'], $getData[$dataKey]['optionPrice']);

            // 상품 옵션 처리
            $getData[$dataKey]['option'] = [];
            if ($dataVal['optionFl'] === 'y') {
                $tmp = explode(STR_DIVISION, $dataVal['optionName']);
                for ($i = 0; $i < 5; $i++) {
                    $optKey = 'optionValue' . ($i + 1);
                    if (empty($dataVal[$optKey]) === false) {
                        $getData[$dataKey]['option'][$i]['optionName'] = (empty($tmp[$i]) === false ? $tmp[$i] : '');
                        $getData[$dataKey]['option'][$i]['optionValue'] = $dataVal[$optKey];

                        // 마지막 옵션리스트에 옵션가를 추가한다.
                        if (count($tmp) == $i + 1) {
                            $getData[$dataKey]['option'][$i]['optionPrice'] = $dataVal['optionPrice'];
                            $getData[$dataKey]['option'][$i]['optionCode'] = $dataVal['optionCode'];
                        }

                        //상품 품절 정보, 상품 배송 정보를 추가한다.
                        if($dataVal['optionSellFl'] == 't'){
                            $getData[$dataKey]['option'][$i]['optionSellStr'] = $optionSellCode[$dataVal['optionSellCode']];
                        }else if($dataVal['optionSellFl'] == 'n'){
                            $getData[$dataKey]['option'][$i]['optionSellStr'] = $optionSellCode[$dataVal['optionSellFl']];
                        }
                        if($dataVal['optionDeliveryFl'] != 'normal'){
                            $getData[$dataKey]['option'][$i]['optionDeliveryStr'] = $optionDeliveryReasonCode[$dataVal['optionDeliveryCode']];
                        }
                    }
                }
                for ($i = 1; $i <= DEFAULT_LIMIT_OPTION; $i++) {
                    $optKey = 'optionValue' . $i;
                    unset($getData[$dataKey][$optKey]);
                }
                unset($tmp);
            }
            unset($getData[$dataKey]['optionName']);

            // 추가 상품 정보
            $getData[$dataKey]['addGoods'] = [];
            if ($dataVal['addGoodsFl'] === 'y' && empty($dataVal['addGoodsNo']) === false) {
                foreach ($dataVal['addGoodsNo'] as $key => $val) {
                    $tmp = $getAddGoods[$val];

                    //
                    $this->cartAddGoodsCnt += $dataVal['addGoodsCnt'][$key];

                    // 추가상품 기본 정보
                    $getData[$dataKey]['addGoods'][$key]['scmNo'] = $tmp['scmNo'];
                    $getData[$dataKey]['addGoods'][$key]['purchaseNo'] = $tmp['purchaseNo'];
                    $getData[$dataKey]['addGoods'][$key]['commission'] = $tmp['commission'];
                    $getData[$dataKey]['addGoods'][$key]['goodsCd'] = $tmp['goodsCd'];
                    $getData[$dataKey]['addGoods'][$key]['goodsModelNo'] = $tmp['goodsModelNo'];
                    $getData[$dataKey]['addGoods'][$key]['optionNm'] = $tmp['optionNm'];
                    $getData[$dataKey]['addGoods'][$key]['brandCd'] = $tmp['brandCd'];
                    $getData[$dataKey]['addGoods'][$key]['makerNm'] = $tmp['makerNm'];
                    $getData[$dataKey]['addGoods'][$key]['stockUseFl'] = $tmp['stockUseFl'] == '1' ? 'y' : 'n';
                    $getData[$dataKey]['addGoods'][$key]['stockCnt'] = $tmp['stockCnt'];
                    $getData[$dataKey]['addGoods'][$key]['viewFl'] = $tmp['viewFl'];
                    $getData[$dataKey]['addGoods'][$key]['soldOutFl'] = $tmp['soldOutFl'];

                    // 과세/비과세 설정에 따른 금액 계산
                    $getData[$dataKey]['addGoods'][$key]['addGoodsNo'] = $val;
                    $getData[$dataKey]['addGoods'][$key]['addGoodsNm'] = $tmp['goodsNm'];
                    $getData[$dataKey]['addGoods'][$key]['addGoodsNmStandard'] = $tmp['goodsNmStandard'];
                    $getData[$dataKey]['addGoods'][$key]['addGoodsPrice'] = $tmp['goodsPrice'];
                    $getData[$dataKey]['addGoods'][$key]['addCostGoodsPrice'] = $tmp['costPrice'];
                    $getData[$dataKey]['addGoods'][$key]['addGoodsCnt'] = $dataVal['addGoodsCnt'][$key];
                    $getData[$dataKey]['addGoods'][$key]['taxFreeFl'] = $tmp['taxFreeFl'];
                    $getData[$dataKey]['addGoods'][$key]['taxPercent'] = $tmp['taxPercent'];
                    $getData[$dataKey]['addGoods'][$key]['addGoodsImage'] = $tmp['addGoodsImage'];

                    //회원등급 > 브랜드별 추가할인 상품 브랜드 정보
                    if ($this->_memInfo['fixedOrderTypeDc'] == 'brand') {
                        if (in_array($tmp['brandCd'], $this->_memInfo['dcBrandInfo']->cateCd)) {
                            $this->goodsBrandInfo[$val][$tmp['brandCd']] = $tmp['brandCd'];
                        } else {
                            if ($tmp['brandCd']) {
                                $this->goodsBrandInfo[$val]['allBrand'] = $tmp['brandCd'];
                            } else {
                                $this->goodsBrandInfo[$val]['noBrand'] = $tmp['brandCd'];
                            }
                        }

                        foreach ($this->goodsBrandInfo[$val] as $gKey => $gVal) {
                            foreach ($this->_memInfo['dcBrandInfo']->cateCd AS $mKey => $mVal) {
                                if ($gKey == $mVal) {
                                    $tmp['dcPercent'] = $this->_memInfo['dcBrandInfo']->goodsDiscount[$mKey];
                                }
                            }
                        }

                        $goodsPriceInfo[$goodsNo]['brandDiscount'][$dataKey][$key] = $tmp['dcPercent'];
                    }

                    foreach ($getData[$dataKey]['addGoodsMustFl'] as $aVal) {
                        if (in_array($val, $aVal['addGoods']) === true) {
                            $getData[$dataKey]['addGoods'][$key]['addGoodsMustFl'] = $aVal['mustFl'];
                        }
                    }

                    // 단가계산용 추가 상품 금액
                    $goodsPriceInfo[$goodsNo]['addGoodsCnt'][$dataKey][$key] = $dataVal['addGoodsCnt'][$key];
                    $goodsPriceInfo[$goodsNo]['addGoodsPrice'][$dataKey][$key] = $tmp['goodsPrice'];

                    if(empty($tmp['goodsNm'])) {
                        $getData[$dataKey]['orderPossible'] = 'n';
                        $getData[$dataKey]['orderPossibleCode'] = self::POSSIBLE_SELL_NO;
                        $getData[$dataKey]['orderPossibleMessage'] = $getData[$dataKey]['orderPossibleMessageList'][] = __('판매중지 추가상품');
                        $this->orderPossible = false;
                    }

                    // 추가상품 재고체크 처리 후 재고 없으면 구매불가 처리
                    if ($tmp['soldOutFl'] === 'y' || ($tmp['soldOutFl'] === 'n' && $tmp['stockUseFl'] === '1' && ($tmp['stockCnt'] == 0 || $tmp['stockCnt'] - $dataVal['addGoodsCnt'][$key] < 0))) {
                        $getData[$dataKey]['orderPossible'] = 'n';
                        $getData[$dataKey]['orderPossibleCode'] = self::POSSIBLE_SOLD_OUT;
                        $getData[$dataKey]['orderPossibleMessage'] = $getData[$dataKey]['orderPossibleMessageList'][] = __('추가상품 재고부족');
                        $this->orderPossible = false;
                    }

                    $getData[$dataKey]['orderPossibleMessageList'] = array_unique($getData[$dataKey]['orderPossibleMessageList']);
                    //추가상품과세율에 따른 세금계산서 출력여부 선택
                    if ((int)$tmp['taxPercent'] != '10' && (int)$tmp['taxPercent'] != '0') {
                        $this->taxGoodsChk = false;
                    }

                    // 추가 상품 순수 개별 부가세 계산 (할인 적용 안됨)
                    $getData[$dataKey]['addGoods'][$key]['addGoodsVat'] = NumberUtils::taxAll($tmp['goodsPrice'] * $dataVal['addGoodsCnt'][$key], $tmp['taxPercent'], $tmp['taxFreeFl']);

                    // 추가 상품 총 금액
                    $getData[$dataKey]['price']['addGoodsPriceSum'] += ($tmp['goodsPrice'] * $dataVal['addGoodsCnt'][$key]);

                    // 추가 상품 개별 부가세 계산
                    $getData[$dataKey]['price']['addGoodsVat']['supply'] += $getData[$dataKey]['addGoods'][$key]['addGoodsVat']['supply'];
                    $getData[$dataKey]['price']['addGoodsVat']['tax'] += $getData[$dataKey]['addGoods'][$key]['addGoodsVat']['tax'];

                    unset($tmp);
                }
            }
            unset($getData[$dataKey]['addGoodsNo']);
            unset($getData[$dataKey]['addGoodsCnt']);

            // 텍스트 옵션
            $getData[$dataKey]['optionText'] = [];
            foreach ($dataVal['optionTextSno'] as $key => $val) {
                $tmp = $getOptionText[$val];

                // 텍스트 옵션 기본 금액 합계
                $getData[$dataKey]['price']['baseOptionTextPrice'] += $tmp['baseOptionTextPrice'];

                // 과세/비과세 설정에 따른 금액 계산
                $tmp['optionTextPrice'] = $tmp['baseOptionTextPrice'];
                $getData[$dataKey]['price']['optionTextPrice'] += $tmp['optionTextPrice'];
                $tmp['optionValue'] = $dataVal['optionTextStr'][$val];
                $getData[$dataKey]['optionText'][$key] = $tmp;

                // 텍스트 옵션 총 금액
                $getData[$dataKey]['price']['optionTextPriceSum'] += ($tmp['optionTextPrice'] * $dataVal['goodsCnt']);
                unset($tmp);
            }
            unset($getData[$dataKey]['optionTextSno']);
            unset($getData[$dataKey]['optionTextStr']);

            // 단가계산용 상품 금액
            $goodsPriceInfo[$goodsNo]['goodsCnt'][$dataKey] = $dataVal['goodsCnt'];
            $goodsPriceInfo[$goodsNo]['goodsPrice'][$dataKey] = $getData[$dataKey]['price']['goodsPrice'];
            $goodsPriceInfo[$goodsNo]['optionPrice'][$dataKey] = $getData[$dataKey]['price']['optionPrice'];
            $goodsPriceInfo[$goodsNo]['optionTextPrice'][$dataKey] = $getData[$dataKey]['price']['optionTextPrice'];
            $goodsPriceInfo[$goodsNo]['memberDcFl'][$dataKey] = true;
            $goodsPriceInfo[$goodsNo]['exceptBenefit'] = $exceptBenefit;
            $goodsPriceInfo[$goodsNo]['exceptBenefitFl'] = $exceptBenefitFl;

            // 상품별 상품 할인 설정
            $policy = new Policy();
            $naverpayConfig = $policy->getNaverPaySetting();
            if ($this->getChannel() != 'naverpay' || ($naverpayConfig['useYn'] == 'y' && $naverpayConfig['saleFl'] == 'y')) {   //네이버가 아니거나 또는 네이버 사용중인데 상품할인을 사용중인 경우
                $getData[$dataKey]['price']['goodsDcPrice'] = $goodsPriceInfo[$goodsNo]['goodsDcPrice'][$dataKey] = $this->getGoodsDcData($dataVal['goodsDiscountFl'], $dataVal['goodsDiscount'], $dataVal['goodsDiscountUnit'], $dataVal['goodsCnt'], $getData[$dataKey]['price'], $getData[$dataKey]['fixedGoodsDiscount'], $getData[$dataKey]['goodsDiscountGroup'], $getData[$dataKey]['goodsDiscountGroupMemberInfo']);
                unset($getData[$dataKey]['goodsDiscountFl'], $getData[$dataKey]['goodsDiscount'], $getData[$dataKey]['goodsDiscountUnit']);
            }

            // 마이앱 추가 할인 설정
            // 상품상세와 장바구니에서는 추가구매 할인금액을 분리해서 보여주지 않기때문에 한번에 계산
            if ($this->useMyapp) {
                $myappConfig = gd_policy('myapp.config');
                if ($myappConfig['benefit']['orderAdditionalBenefit']['isUsing'] == true) {
                    $myapp = \App::load('Component\\Myapp\\Myapp');
                    $myappBenefitParams['goodsCnt'] = $dataVal['goodsCnt'];
                    $myappBenefitParams['goodsPrice'] = $getData[$dataKey]['price']['goodsPrice'];
                    $myappBenefitParams['optionPrice'] = $getData[$dataKey]['price']['optionPrice'];
                    $myappBenefitParams['optionTextPrice'] = $getData[$dataKey]['price']['optionTextPrice'];
                    $myappBenefitParams['addGoodsPrice'] = $getData[$dataKey]['price']['addGoodsPriceSum'];
                    $myappBenefit = $myapp->getOrderAdditionalBenefit($myappBenefitParams);
                    $getData[$dataKey]['price']['myappDcPrice'] = $goodsPriceInfo[$goodsNo]['myappDcPrice'][$dataKey] = $myappBenefit['discount']['goods'];
                }
            }

            // 각 상품별 마일리지 초기화
            $getData[$dataKey]['mileage']['goodsMileage'] = 0;
            $getData[$dataKey]['mileage']['memberMileage'] = 0;
            $getData[$dataKey]['mileage']['couponGoodsMileage'] = 0;

            if ($dataVal['couponBenefitExcept'] == 'n') {
                // 상품 쿠폰 금액 및 추가 마일리지
                $getData[$dataKey]['price']['couponGoodsDcPrice'] = 0;
                $getData[$dataKey]['price']['goodsCnt'] = $dataVal['goodsCnt'];
                $getData[$dataKey]['coupon'] = [];

                if ($dataVal['memberCouponNo']) {
                    if (empty($this->goodsCouponInfo[$dataVal['memberCouponNo']]) === false) {
                        $goodsCouponInfo = &$this->goodsCouponInfo[$dataVal['memberCouponNo']];
                        $memberCouponNo = explode(INT_DIVISION, $dataVal['memberCouponNo']);

                        $tempGoodsCnt = $goodsCouponInfo['saleGoodsCnt'];
                        foreach ($memberCouponNo as $val) {
                            //상품 할인 쿠폰
                            if (empty($goodsCouponInfo['info']['memberCouponSalePrice'][$val]) === false) {
                                // 상품쿠폰 주문서페이지 변경 제한안함일 때 && 수기주문이 아닐 경우
                                if($couponPolicy['productCouponChangeLimitType'] == 'n' && $postValue['productCouponChangeLimitType'] == 'n' && $this->isWrite === false) {
                                    if($tempGoodsCnt - $dataVal['goodsCnt'] > 0) {
                                        $memberCouponSalePrice = round(($goodsCouponInfo['info']['memberCouponSalePrice'][$val] * $dataVal['goodsCnt']) / $tempGoodsCnt);
                                        $goodsCouponInfo['saleGoodsCnt'] -= $dataVal['goodsCnt'];
                                        $goodsCouponInfo['info']['memberCouponSalePrice'][$val] -= $memberCouponSalePrice;
                                    } else {
                                        $memberCouponSalePrice = $goodsCouponInfo['info']['memberCouponSalePrice'][$val];
                                    }
                                } else {
                                    if ($goodsCouponInfo['saleGoodsCnt'] - $dataVal['goodsCnt'] > 0) {
                                        $memberCouponSalePrice = round(($goodsCouponInfo['info']['memberCouponSalePrice'][$val] * $dataVal['goodsCnt']) / $goodsCouponInfo['saleGoodsCnt']);

                                        $goodsCouponInfo['saleGoodsCnt'] -= $dataVal['goodsCnt'];
                                        $goodsCouponInfo['info']['memberCouponSalePrice'][$val] -= $memberCouponSalePrice;
                                    } else {
                                        $memberCouponSalePrice = $goodsCouponInfo['info']['memberCouponSalePrice'][$val];
                                    }
                                }
                                $tmp['memberCouponAlertMsg'][$val] = $goodsCouponInfo['info']['memberCouponAlertMsg'][$val];
                                $tmp['memberCouponSalePrice'][$val] = $memberCouponSalePrice;
                            }

                            //마일리지 적립 쿠폰
                            if (empty($goodsCouponInfo['info']['memberCouponAddMileage'][$val]) === false) {
                                // 상품쿠폰 주문서페이지 변경 제한안함일 때 && 수기주문이 아닐 경우
                                if($couponPolicy['productCouponChangeLimitType'] == 'n' && $postValue['productCouponChangeLimitType'] == 'n' && $this->isWrite === false) {
                                    $memberCouponAddMileage = round(($goodsCouponInfo['info']['memberCouponAddMileage'][$val] * $dataVal['goodsCnt']) / $goodsCouponInfo['mileageGoodsCnt']);
                                } else {
                                    if ($goodsCouponInfo['mileageGoodsCnt'] - $dataVal['goodsCnt'] > 0) {
                                        $memberCouponAddMileage = round(($goodsCouponInfo['info']['memberCouponAddMileage'][$val] * $dataVal['goodsCnt']) / $goodsCouponInfo['mileageGoodsCnt']);

                                        $goodsCouponInfo['mileageGoodsCnt'] -= $dataVal['goodsCnt'];
                                        $goodsCouponInfo['info']['memberCouponAddMileage'][$val] -= $memberCouponAddMileage;
                                    } else {
                                        $memberCouponAddMileage = $goodsCouponInfo['info']['memberCouponAddMileage'][$val];
                                    }
                                }
                                $tmp['memberCouponAlertMsg'][$val] = $goodsCouponInfo['info']['memberCouponAlertMsg'][$val];
                                $tmp['memberCouponAddMileage'][$val] = $memberCouponAddMileage;
                            }
                        }
                    } else {
                        // 상품쿠폰 주문서페이지 변경 제한안함일 때
                        $coupon = \App::load('\\Component\\Coupon\\Coupon');
                        $memberCouponNo = explode(INT_DIVISION, $dataVal['memberCouponNo']);

                        foreach ($memberCouponNo as $dataCouponVal) { // 배열로 넘어오는 경우도 있어 foreach 처리
                            if ($dataCouponVal != null) {
                                $couponVal = $coupon->getMemberCouponInfo($dataCouponVal);

                                $goodsCouponForTotalPriceTemp = array();
                                foreach ($getData as $pVal) {
                                    $goodsCouponForTotalPriceTemp['goodsPriceSum'] += $pVal['price']['goodsPriceSum'];
                                    $goodsCouponForTotalPriceTemp['optionPriceSum'] += $pVal['price']['optionPriceSum'];
                                    $goodsCouponForTotalPriceTemp['optionTextPriceSum'] += $pVal['price']['optionTextPriceSum'];
                                    $goodsCouponForTotalPriceTemp['addGoodsPriceSum'] += $pVal['price']['addGoodsPriceSum'];
                                }

                                // 상품쿠폰 주문서페이지 변경 제한안함일 때
                                if (!$goodsCouponForTotalPrice || $couponVal['couponProductMinOrderType'] != 'order') {
                                    $tmp = $this->getMemberCouponPriceData($getData[$dataKey]['price'], $dataVal['memberCouponNo'], $goodsCouponForTotalPriceTemp, $isAllFl);
                                } else {
                                    // 기준 금액 주문 쿠폰적용가
                                    $tmp = $this->getMemberCouponPriceData($goodsCouponForTotalPrice, $dataVal['memberCouponNo'], $goodsCouponForTotalPriceTemp, $isAllFl);
                                    // 기준 금액 변경 전 쿠폰적용가
                                    $tmpOriginProductPrice = $this->getMemberCouponPriceData($getData[$dataKey]['price'], $dataVal['memberCouponNo']);
                                    // 쿠폰적용 가격 기존으로 대체
                                    $tmp['memberCouponSalePrice'] = $tmpOriginProductPrice['memberCouponSalePrice']; // 할인액
                                    $tmp['memberCouponAddMileage'] = $tmpOriginProductPrice['memberCouponAddMileage']; // 적립액
                                }
                            }
                        }
                    }

                    if (array_search('LIMIT_MIN_PRICE', $tmp['memberCouponAlertMsg']) === false) {
                        if (is_array($tmp['memberCouponSalePrice'])) {
                            $goodsOptCouponSalePriceSum = array_sum($tmp['memberCouponSalePrice']);
                        }
                        if (is_array($tmp['memberCouponAddMileage'])) {
                            $goodsOptCouponAddMileageSum = array_sum($tmp['memberCouponAddMileage']);
                        }
                    } else {
                        // 'LIMIT_MIN_PRICE' 일때 구매금액 제한에 걸려 사용 못하는 쿠폰 처리
                        // 수량 변경 시 구매금액 제한에 걸림
                        // 적용된 쿠폰 모두 제거
                        $goodsOptCouponSalePriceSum = 0;
                        $goodsOptCouponAddMileageSum = 0;
                        $this->setMemberCouponDelete($dataVal['sno']);
                        $getData[$dataKey]['memberCouponNo'] = 0;
                        $dataVal['memberCouponNo'] = 0;
                    }

                    $goodsPriceInfo[$goodsNo]['couponDcPrice'][$dataKey] = $getData[$dataKey]['price']['couponDcPrice'] = $goodsOptCouponSalePriceSum;
                    // 상품쿠폰 데이터 추가
                    if ($dataVal['memberCouponNo']) {
                        $coupon = \App::load('\\Component\\Coupon\\Coupon');
                        $key = $tmpCouponGoodsDcPrice = $tmpCouponGoodsMileage = 0;
                        foreach (explode(INT_DIVISION, $dataVal['memberCouponNo']) as $val) {
                            if ($val != null) {
                                $getData[$dataKey]['coupon'][$val] = $coupon->getMemberCouponInfo($val, 'c.couponNm, c.couponUseType, c.couponDescribed, c.couponSaveType, c.couponUsePeriodType, c.couponUsePeriodStartDate, c.couponUsePeriodEndDate, c.couponUsePeriodDay, c.couponUseDateLimit, c.couponBenefit, c.couponBenefitType, c.couponBenefitFixApply, c.couponKindType, c.couponApplyDuplicateType, c.couponMaxBenefit, c.couponMinOrderPrice, mc.memberCouponStartDate, mc.memberCouponEndDate, c.couponProductMinOrderType');
                                $getData[$dataKey]['coupon'][$val]['convertData'] = $coupon->convertCouponData($getData[$dataKey]['coupon'][$val]);
                                $getData[$dataKey]['coupon'][$val]['couponGoodsDcPrice'] = gd_isset($tmp['memberCouponSalePrice'][$val], 0);
                                $getData[$dataKey]['coupon'][$val]['couponGoodsMileage'] = gd_isset($tmp['memberCouponAddMileage'][$val], 0);
                                $tmpCouponGoodsDcPrice += gd_isset($tmp['memberCouponSalePrice'][$val], 0);
                                $tmpCouponGoodsMileage += gd_isset($tmp['memberCouponAddMileage'][$val], 0);
                                $key++;
                            }
                        }
                    }

                    // 쿠폰을 사용했고 사용설정에 쿠폰만 사용설정일때 처리
                    if ($tmpCouponGoodsDcPrice > 0 || $tmpCouponGoodsMileage > 0) {
                        $couponConfig = gd_policy('coupon.config');
                        if ($couponConfig['couponUseType'] == 'y' && $couponConfig['chooseCouponMemberUseType'] == 'coupon') {
                            $memberDcFl = $goodsPriceInfo[$goodsNo]['memberDcFl'][$dataKey] = false;
                            $getData[$dataKey]['price']['memberDcPrice'] = 0;
                            $getData[$dataKey]['price']['memberOverlapDcPrice'] = 0;
                            $getData[$dataKey]['mileage']['memberMileage'] = 0;
                        }
                    }

                    if ($this->channel == 'naverpay') {  //네이버페이는 쿠폰상품할인 쿠폰마일리지 적립 적용안함.
                        $getData[$dataKey]['memberCouponNo'] = 0;
                        $getData[$dataKey]['coupon'] = null;
                        $getData[$dataKey]['price']['couponGoodsDcPrice'] = 0;
                        $getData[$dataKey]['price']['couponGoodsMileage'] = 0;
                    }
                    unset($tmp);
                }
            }

            // 회원 그룹별 추가 마일리지
            if ($dataVal['memberBenefitExcept'] == 'n' && $memberDcFl == true) {
                // 회원 추가 마일리지 적립 적용 제외
                if (in_array('mileage', $exceptBenefit) === true && $exceptBenefitFl === true) {
                } else {
                    if (empty($tmpMileageInfo[$goodsNo][$optionSno]) === false) {
                        $mileageInfo = &$tmpMileageInfo[$goodsNo][$optionSno];
                        if ($mileageInfo['goodsCnt'] - $dataVal['goodsCnt'] > 0) {
                            $getData[$dataKey]['mileage']['memberMileage'] = round(($mileageInfo['memberMileage'] * $dataVal['goodsCnt']) / $mileageInfo['goodsCnt']);
                            $mileageInfo['goodsCnt'] -= $dataVal['goodsCnt'];
                            $mileageInfo['memberMileage'] -= $getData[$dataKey]['mileage']['memberMileage'];
                        } else {
                            $getData[$dataKey]['mileage']['memberMileage'] = $mileageInfo['memberMileage'];
                        }
                    } else {
                        $getData[$dataKey]['mileage']['memberMileage'] = $this->getMemberMileageData($this->_memInfo, $getData[$dataKey]['price']);
                    }
                }

                // 회원 그룹별 추가 할인 및 중복 할인
                if ($this->getChannel() != 'naverpay') {
                    if (empty($tmpMemberDcInfo[$goodsNo][$optionSno]) === false) {
                        $memberDcInfo = &$tmpMemberDcInfo[$goodsNo][$optionSno];
                        $tmp = [
                            'addDcFl' => $memberDcInfo['addDcFl'],
                            'overlapDcFl' => $memberDcInfo['overlapDcFl']
                        ];
                        if ($memberDcInfo['goodsCnt'] - $dataVal['goodsCnt'] > 0) {
                            $tmp['memberDcPrice'] = round(($memberDcInfo['memberDcPrice'] * $dataVal['goodsCnt']) / $memberDcInfo['goodsCnt']);
                            $tmp['memberOverlapDcPrice'] = round(($memberDcInfo['memberOverlapDcPrice'] * $dataVal['goodsCnt']) / $memberDcInfo['goodsCnt']);
                            $memberDcInfo['memberDcPrice'] -= $tmp['memberDcPrice'];
                            $memberDcInfo['memberOverlapDcPrice'] -= $tmp['memberOverlapDcPrice'];
                            $memberDcInfo['goodsCnt'] -= $dataVal['goodsCnt'];
                        } else {
                            $tmp['memberDcPrice'] = $memberDcInfo['memberDcPrice'];
                            $tmp['memberOverlapDcPrice'] = $memberDcInfo['memberOverlapDcPrice'];
                        }
                    } else {
                        // 브랜드 할인율
                        if ($this->_memInfo['fixedOrderTypeDc'] == 'brand') {
                            if (in_array($getData[$dataKey]['brandCd'], $this->_memInfo['dcBrandInfo']->cateCd)) {
                                $goodsBrandInfo[$getData[$dataKey]['goodsNo']][$getData[$dataKey]['brandCd']] = $getData[$dataKey]['brandCd'];
                            } else {
                                if ($getData[$dataKey]['brandCd']) {
                                    $goodsBrandInfo[$getData[$dataKey]['goodsNo']]['allBrand'] = $getData[$dataKey]['brandCd'];
                                } else {
                                    $goodsBrandInfo[$getData[$dataKey]['goodsNo']]['noBrand'] = $getData[$dataKey]['brandCd'];
                                }
                            }
                        }
                        $tmp = $this->getMemberDcPriceData($dataVal['goodsNo'], $this->_memInfo, $getData[$dataKey]['price'], $this->getMemberDcForCateCd(), $dataVal['addDcFl'], $dataVal['overlapDcFl'], $goodsBrandInfo);
                        $getData[$dataKey]['memberDcInfo'] = json_encode(array_merge($tmp, ['goodsCnt' => $dataVal['goodsCnt']]));
                    }
                    // 회원 추가 할인혜택 적용 제외
                    if (in_array('add', $exceptBenefit) === true && $exceptBenefitFl === true) {
                    } else {
                        $getData[$dataKey]['addDcFl'] = $tmp['addDcFl'];
                        $getData[$dataKey]['price']['memberDcPrice'] = $tmp['memberDcPrice'];
                    }
                    // 회원 중복 할인혜택 적용 제외
                    if (in_array('overlap', $exceptBenefit) === true && $exceptBenefitFl === true) {
                    } else {
                        $getData[$dataKey]['overlapDcFl'] = $tmp['overlapDcFl'];
                        $getData[$dataKey]['price']['memberOverlapDcPrice'] = $tmp['memberOverlapDcPrice'];
                    }
                    unset($tmp);
                }
            }

            $goodsPriceInfo[$goodsNo]['couponBenefitExcept'] = $dataVal['couponBenefitExcept'];
            $goodsPriceInfo[$goodsNo]['addDcFl'] = $getData[$dataKey]['addDcFl'] ? $getData[$dataKey]['addDcFl'] : $dataVal['addDcFl'];
            $goodsPriceInfo[$goodsNo]['overlapDcFl'] = $getData[$dataKey]['overlapDcFl'] ? $getData[$dataKey]['overlapDcFl'] : $dataVal['overlapDcFl'];
            $goodsPriceInfo[$goodsNo]['scmNo'] = $scmNo;
            $goodsPriceInfo[$goodsNo]['deliverySno'] = $deliverySno;

            //회원등급 > 브랜드별 추가할인 상품 브랜드 정보
            if ($this->_memInfo['fixedOrderTypeDc'] == 'brand') {
                if (in_array($dataVal['brandCd'], $this->_memInfo['dcBrandInfo']->cateCd)) {
                    $this->goodsBrandInfo[$goodsNo][$dataVal['brandCd']] = $dataVal['brandCd'];
                } else {
                    if ($dataVal['brandCd']) {
                        $this->goodsBrandInfo[$goodsNo]['allBrand'] = $dataVal['brandCd'];
                    } else {
                        $this->goodsBrandInfo[$goodsNo]['noBrand'] = $dataVal['brandCd'];
                    }
                }
            }

            // 상품과 추가상품의 가격비율에 따른 각각의 할인금액/적립마일리지 안분 작업
            $totalAddGoodsMemberDcPrice = 0;
            $totalAddGoodsMemberOverlapDcPrice = 0;
            $totalAddGoodsCouponGoodsDcPrice = 0;
            $totalAddGoodsGoodsMileage = 0;
            $totalAddGoodsMemberMileage = 0;
            $totalAddGoodsCouponGoodsMileage = 0;
            $tmpOriginGoodsPrice = $getData[$dataKey]['price']['goodsPriceSum'] + $getData[$dataKey]['price']['optionPriceSum'] + $getData[$dataKey]['price']['optionTextPriceSum'] + $getData[$dataKey]['price']['addGoodsPriceSum'];

            // 쿠폰할인금액이 상품결제금액 보다 큰 경우 쿠폰가격 재조정 (상품결제금액이 마이너스로 나오는 오류 수정)
            //$exceptCouponPrice = $getData[$dataKey]['price']['goodsPriceSum'] + $getData[$dataKey]['price']['optionPriceSum'] + $getData[$dataKey]['price']['optionTextPriceSum'] + $getData[$dataKey]['price']['addGoodsPriceSum'] - $getData[$dataKey]['price']['goodsDcPrice'] - $getData[$dataKey]['price']['memberDcPrice'] - $getData[$dataKey]['price']['memberOverlapDcPrice'];

            $exceptCouponPrice = $getData[$dataKey]['price']['goodsPriceSum'] - $getData[$dataKey]['price']['goodsDcPrice'];

            // 마이앱 사용에 따른 분기 처리
            if ($this->useMyapp) {
                $exceptCouponPrice -= $getData[$dataKey]['price']['myappDcPrice'];
            }

            if ($couponPolicy['couponOptPriceType'] == 'y') $exceptCouponPrice += $getData[$dataKey]['price']['optionPriceSum'];
            if ($couponPolicy['couponAddPriceType'] == 'y') $exceptCouponPrice += $getData[$dataKey]['price']['addGoodsPriceSum'];
            if ($couponPolicy['couponTextPriceType'] == 'y') $exceptCouponPrice += $getData[$dataKey]['price']['optionTextPriceSum'];
            if (empty($couponPolicy['chooseCouponMemberUseType']) === true || $couponPolicy['chooseCouponMemberUseType'] == 'all') {
                if ($exceptCouponPrice <= $goodsOptCouponSalePriceSum && $this->_memInfo['fixedRatePrice'] == 'settle') {
                    $goodsOptCouponSalePriceSum = $exceptCouponPrice;
                    unset($getData[$dataKey]['price']['memberDcPrice'], $getData[$dataKey]['price']['memberOverlapDcPrice']);
                } else {
                    $exceptCouponPrice -= $getData[$dataKey]['price']['memberDcPrice'] + $getData[$dataKey]['price']['memberOverlapDcPrice'];
                }
            }
            if ($exceptCouponPrice < $goodsOptCouponSalePriceSum && $exceptCouponPrice > 0) {
                $goodsOptCouponSalePriceSum = $exceptCouponPrice;
            }
            if ($this->channel != 'naverpay') {
                $getData[$dataKey]['price']['couponGoodsDcPrice'] = gd_isset($goodsOptCouponSalePriceSum, 0);
                $getData[$dataKey]['mileage']['couponGoodsMileage'] = gd_isset($goodsOptCouponAddMileageSum, 0);
            }

            $goodsCouponData[$dataKey] = [
                'goodsNo' => $goodsNo,
                'goodsCnt' => $dataVal['goodsCnt'],
                'goodsPrice' => $getData[$dataKey]['price'],
                'couponPrice' => $goodsOptCouponSalePriceSum,
            ];

            if ($getData[$dataKey]['addGoods'] !== null) {
                // 절사 정책
                $memberTruncPolicy = Globals::get('gTrunc.member_group');
                $couponTruncPolicy = Globals::get('gTrunc.coupon');
                $mileageTruncPolicy = Globals::get('gTrunc.mileage');

                // 쿠폰 정책
                $couponPolicy = gd_policy('coupon.config');

                foreach ($getData[$dataKey]['addGoods'] as $key => $val) {
                    // 추가상품별 할인금액 초기화
                    $getData[$dataKey]['addGoods'][$key]['addGoodsMemberDcPrice'] = 0;
                    $getData[$dataKey]['addGoods'][$key]['addGoodsMemberOverlapDcPrice'] = 0;
                    $getData[$dataKey]['addGoods'][$key]['addGoodsCouponGoodsDcPrice'] = 0;

                    // 추가상품 비율
                    $addGoodsRate = (($val['addGoodsPrice'] * $val['addGoodsCnt']) / $tmpOriginGoodsPrice);

                    // 추가상품 비율에 따른 회원 할인금액 설정
                    if ($this->_memInfo['fixedRateOption'][1] == 'goods') {
                        if ($getData[$dataKey]['addDcFl']) {
                            $getData[$dataKey]['addGoods'][$key]['addGoodsMemberDcPrice'] = gd_number_figure($getData[$dataKey]['price']['memberDcPrice'] * $addGoodsRate, $memberTruncPolicy['unitPrecision'], $memberTruncPolicy['unitRound']);
                            $totalAddGoodsMemberDcPrice += $getData[$dataKey]['addGoods'][$key]['addGoodsMemberDcPrice'];
                        }
                        if ($getData[$dataKey]['overlapDcFl']) {
                            $getData[$dataKey]['addGoods'][$key]['addGoodsMemberOverlapDcPrice'] = gd_number_figure($getData[$dataKey]['price']['memberOverlapDcPrice'] * $addGoodsRate, $memberTruncPolicy['unitPrecision'], $memberTruncPolicy['unitRound']);
                            $totalAddGoodsMemberOverlapDcPrice += $getData[$dataKey]['addGoods'][$key]['addGoodsMemberOverlapDcPrice'];
                        }
                    }

                    // 추가상품 비율에 따른 상품쿠폰 할인금액 설정
                    if ($couponPolicy['couponAddPriceType'] == 'y') {
                        $getData[$dataKey]['addGoods'][$key]['addGoodsCouponGoodsDcPrice'] = gd_number_figure($getData[$dataKey]['price']['couponGoodsDcPrice'] * $addGoodsRate, $couponTruncPolicy['unitPrecision'], $couponTruncPolicy['unitRound']);
                        $totalAddGoodsCouponGoodsDcPrice += $getData[$dataKey]['addGoods'][$key]['addGoodsCouponGoodsDcPrice'];
                    }

                    // 추가상품 비율에 따른 상품 적립 마일리지 설정 (금액/단위 기준설정에 따른 절사)
                    if ($this->mileageGiveInfo['basic']['addGoodsPrice'] == 1) {
                        $getData[$dataKey]['addGoods'][$key]['addGoodsGoodsMileage'] = gd_number_figure($getData[$dataKey]['mileage']['goodsMileage'] * $addGoodsRate, $mileageTruncPolicy['unitPrecision'], $mileageTruncPolicy['unitRound']);
                        $totalAddGoodsGoodsMileage += $getData[$dataKey]['addGoods'][$key]['addGoodsGoodsMileage'];
                    }

                    // 추가상품 비율에 따른 회원 적립 마일리지 설정 (금액/단위 기준설정에 따른 절사)
                    if ($this->_memInfo['fixedRateOption'][1] == 'goods') {
                        $getData[$dataKey]['addGoods'][$key]['addGoodsMemberMileage'] = gd_number_figure($getData[$dataKey]['mileage']['memberMileage'] * $addGoodsRate, $mileageTruncPolicy['unitPrecision'], $mileageTruncPolicy['unitRound']);
                        $totalAddGoodsMemberMileage += $getData[$dataKey]['addGoods'][$key]['addGoodsMemberMileage'];
                    }

                    // 추가상품 비율에 따른 쿠폰 적립 마일리지 설정 (금액/단위 기준설정에 따른 절사)
                    if ($couponPolicy['couponAddPriceType'] == 'y') {
                        $getData[$dataKey]['addGoods'][$key]['addGoodsCouponGoodsMileage'] = gd_number_figure($getData[$dataKey]['mileage']['couponGoodsMileage'] * $addGoodsRate, $mileageTruncPolicy['unitPrecision'], $mileageTruncPolicy['unitRound']);
                        $totalAddGoodsCouponGoodsMileage += $getData[$dataKey]['addGoods'][$key]['addGoodsCouponGoodsMileage'];
                    }
                }

                // 상품의 할인 비율 금액 계산 = 상품의 회원/쿠폰 할인 총 금액 - 추가상품 할인 금액
                if ($this->getChannel() != 'naverpay') {
                    $getData[$dataKey]['price']['goodsMemberDcPrice'] = ($getData[$dataKey]['price']['memberDcPrice'] - $totalAddGoodsMemberDcPrice);
                    $getData[$dataKey]['price']['goodsMemberOverlapDcPrice'] = ($getData[$dataKey]['price']['memberOverlapDcPrice'] - $totalAddGoodsMemberOverlapDcPrice);
                    $getData[$dataKey]['price']['goodsCouponGoodsDcPrice'] = ($getData[$dataKey]['price']['couponGoodsDcPrice'] - $totalAddGoodsCouponGoodsDcPrice);
                    $getData[$dataKey]['price']['addGoodsMemberDcPrice'] = $totalAddGoodsMemberDcPrice;
                    $getData[$dataKey]['price']['addGoodsMemberOverlapDcPrice'] = $totalAddGoodsMemberOverlapDcPrice;
                    $getData[$dataKey]['price']['addGoodsCouponGoodsDcPrice'] = $totalAddGoodsCouponGoodsDcPrice;

                    $getData[$dataKey]['mileage']['goodsGoodsMileage'] = ($getData[$dataKey]['mileage']['goodsMileage'] - $totalAddGoodsGoodsMileage);
                    $getData[$dataKey]['mileage']['goodsMemberMileage'] = ($getData[$dataKey]['mileage']['memberMileage'] - $totalAddGoodsMemberMileage);
                    $getData[$dataKey]['mileage']['goodsCouponGoodsMileage'] = ($getData[$dataKey]['mileage']['couponGoodsMileage'] - $totalAddGoodsCouponGoodsMileage);
                    $getData[$dataKey]['mileage']['addGoodsGoodsMileage'] = $totalAddGoodsGoodsMileage;
                    $getData[$dataKey]['mileage']['addGoodsMemberMileage'] = $totalAddGoodsMemberMileage;
                    $getData[$dataKey]['mileage']['addGoodsCouponGoodsMileage'] = $totalAddGoodsCouponGoodsMileage;
                    $getData[$dataKey]['mileage']['goodsCnt'] = $dataVal['goodsCnt'];
                }
            }
            unset($totalAddGoodsMemberDcPrice);
            unset($totalAddGoodsMemberOverlapDcPrice);
            unset($totalAddGoodsCouponGoodsDcPrice);
            unset($totalAddGoodsGoodsMileage);
            unset($totalAddGoodsMemberMileage);
            unset($totalAddGoodsCouponGoodsMileage);
            unset($goodsOptCouponSalePriceSum);
            unset($goodsOptCouponAddMileageSum);
        }

        $tmpOrderPrice = [];
        $divisionOrderCoupon = $this->getDivisionOrderCoupon($goodsPriceInfo)['goods'];

        foreach ($goodsPriceInfo as $key => $val) {
            // 상품의 단가, 합계금액 계산
            if (empty($divisionOrderCoupon[$key]) === false) $val['orderCoupon'] = $divisionOrderCoupon[$key];
            $tmp = $this->getUnitGoodsPriceData($this->_memInfo, $val);
            $tmpPrice[$key] = $tmp['tmpPrice'];
            // 상품 전체 주문금액
            $tmpOrderPrice['memberDcByPrice'] += array_sum($tmpPrice[$key]['all']['memberDcByPrice']);
            $tmpOrderPrice['couponDcPrice'][$key] += array_sum($val['couponDcPrice']);

            // 추가할인 가능시 상품전체금액 계산
            if ($val['addDcFl'] === true) {
                $tmpOrderPrice['addDcTotal']['memberDcByPrice'] += array_sum($tmpPrice[$key]['all']['memberDcByPrice']);
            }
            // 중복할인 가능시 상품전체금액 계산
            if ($val['overlapDcFl'] === true) {
                $tmpOrderPrice['overlapDcTotal']['memberDcByPrice'] += array_sum($tmpPrice[$key]['all']['memberDcByPrice']);
            }
            if (empty($tmpPrice[$key]['all']['memberDcByAddPrice']) === false) {
                foreach ($tmpPrice[$key]['all']['memberDcByAddPrice'] as $k => $v) {
                    // 추가상품 전체 주문금액
                    $tmpOrderPrice['memberDcByAddPrice'] += array_sum($v);
                    // 추가할인 가능시 추가상품전체금액 계산
                    if ($val['addDcFl'] === true) {
                        $tmpOrderPrice['addDcTotal']['memberDcByAddPrice'] += array_sum($v);
                    }
                    // 중복할인 가능시 추가상품전체금액 계산
                    if ($val['overlapDcFl'] === true) {
                        $tmpOrderPrice['overlapDcTotal']['memberDcByAddPrice'] += array_sum($v);
                    }
                }
            }
        }

        // 회원 추가/중복할인, 마일리지 지급 재계산 (금액 기준이 상품, 주문별, 브랜드별일 경우)
        foreach ($goodsPriceInfo as $key => $val) {
            if ($val['couponBenefitExcept'] == 'n') {
                if (in_array($this->_memInfo['fixedOrderTypeDc'], ['goods', 'order', 'brand']) === true) {
                    // 회원 추가 할인혜택 적용 제외
                    if (in_array('add', $val['exceptBenefit']) === true && $val['exceptBenefitFl'] === true) {
                    } else {
                        $addDcPrice = $this->getMemberGoodsAddDcPriceData($tmpPrice[$key], $tmpOrderPrice['addDcTotal'], $key, $this->_memInfo, $val, $this->getMemberDcForCateCd(), $val['addDcFl'], $tmpOrderPrice['couponDcPrice'], $this->goodsBrandInfo);

                        foreach ($val['goodsCnt'] as $k => $v) {
                            if ($goodsPriceInfo[$key]['memberDcFl'][$k] === false) continue;

                            $addDcPrice['info']['goods'][$k] = ($addDcPrice['info']['goods'][$k] != 0) ? $addDcPrice['info']['goods'][$k] : $getData[$k]['price']['goodsMemberDcPrice'];
                            $getData[$k]['price']['goodsMemberDcPrice'] = gd_isset($addDcPrice['info']['goods'][$k], 0);

                            if (in_array($this->_memInfo['fixedOrderTypeDc'], ['brand']) === true) {
                                // 추가 상품 브랜드 할인율
                                $getData[$k]['price']['goodsMemberBrandDcPrice'] = gd_isset($addDcPrice['memberBrandDcPrice']['goods'][$k], 0);
                                unset($getData[$k]['price']['addGoodsMemberDcPrice']);
                                foreach ($getData[$k]['addGoods'] as $tKey => $tVal) {
                                    $getData[$k]['price']['addGoodsMemberDcPrice'] += $addDcPrice['info']['addGoods'][$k][$tKey];
                                    $getData[$k]['addGoods'][$tKey]['addGoodsMemberDcPrice'] = $addDcPrice['info']['addGoods'][$k][$tKey];
                                }
                            } else {
                                $getData[$k]['price']['addGoodsMemberDcPrice'] = gd_isset(array_sum($addDcPrice['info']['addGoods'][$k]), 0);

                                foreach ($getData[$k]['addGoods'] as $tKey => $tVal) {
                                    $getData[$k]['addGoods'][$tKey]['addGoodsMemberDcPrice'] = gd_isset($addDcPrice['info']['addGoods'][$k][$tKey], 0);
                                }
                            }

                        }
                        unset($addDcPrice);
                    }
                }
                if (in_array($this->_memInfo['fixedOrderTypeOverlapDc'], ['goods', 'order']) === true) {
                    // 회원 중복 할인혜택 적용 제외
                    if (in_array('overlap', $val['exceptBenefit']) === true && $val['exceptBenefitFl'] === true) {
                    } else {
                        $overlapDcPrice = $this->getMemberGoodsOverlapDcPriceData($tmpPrice[$key], $tmpOrderPrice['overlapDcTotal'], $key, $this->_memInfo, $val, $this->getMemberDcForCateCd(), $val['overlapDcFl'], $tmpOrderPrice['couponDcPrice']);

                        foreach ($val['goodsCnt'] as $k => $v) {
                            if ($goodsPriceInfo[$key]['memberDcFl'][$k] === false) continue;

                            $getData[$k]['price']['goodsMemberOverlapDcPrice'] = gd_isset($overlapDcPrice['info']['goods'][$k], 0);
                            $getData[$k]['price']['addGoodsMemberOverlapDcPrice'] = gd_isset(array_sum($overlapDcPrice['info']['addGoods'][$k]), 0);

                            foreach ($getData[$k]['addGoods'] as $tKey => $tVal) {
                                $getData[$k]['addGoods'][$tKey]['addGoodsMemberOverlapDcPrice'] = gd_isset($overlapDcPrice['info']['addGoods'][$k][$tKey], 0);
                            }
                        }
                        unset($overlapDcPrice);
                    }
                }

                foreach ($val['goodsCnt'] as $k => $v) {
                    $getData[$k]['price']['memberDcPrice'] = $getData[$k]['price']['goodsMemberDcPrice'] + $getData[$k]['price']['addGoodsMemberDcPrice'];
                    $getData[$k]['price']['memberOverlapDcPrice'] = $getData[$k]['price']['goodsMemberOverlapDcPrice'] + $getData[$k]['price']['addGoodsMemberOverlapDcPrice'];
                }

                if (in_array($this->_memInfo['fixedOrderTypeMileage'], ['goods', 'order']) === true) {
                    // 회원 추가 마일리지 적립 적용 제외
                    if (in_array('mileage', $val['exceptBenefit']) === true && $val['exceptBenefitFl'] === true) {
                    } else {
                        $memberMileage = $this->getMemberGoodsMileageData($tmpPrice[$key], $tmpOrderPrice, $this->_memInfo);

                        foreach ($val['goodsCnt'] as $k => $v) {
                            if ($goodsPriceInfo[$key]['memberDcFl'][$k] === false) continue;

                            $getData[$k]['mileage']['memberMileage'] = $getData[$k]['mileage']['goodsMemberMileage'] = gd_isset($memberMileage['goods'][$k], 0);
                            $getData[$k]['mileage']['addGoodsMemberMileage'] = gd_isset(array_sum($memberMileage['addGoods'][$k]), 0);

                            foreach ($getData[$k]['addGoods'] as $tKey => $tVal) {
                                $getData[$k]['addGoods'][$tKey]['addGoodsMemberMileage'] = gd_isset($memberMileage['addGoods'][$k][$tKey], 0);
                                $getData[$k]['mileage']['memberMileage'] += gd_isset($memberMileage['addGoods'][$k][$tKey], 0);
                            }
                        }
                        unset($memberMileage);
                    }
                }
            }
        }
        unset($goodsPriceInfo, $tmpPrice, $tmpOrderPrice);

        // 주문 쿠폰 안분을 getData 안으로 처리
        foreach ($divisionOrderCoupon as $goodsNo => $couponVal) {
            foreach ($couponVal['divisionOrderCouponByAddGoods'] as $addKey => $addVal) {
                if (!$getData[$addKey]['price']['couponOrderDcPrice']) {
                    $getData[$addKey]['price']['couponOrderDcPrice'] = 0;
                }
                $getData[$addKey]['price']['couponOrderDcPrice'] += $addVal;
            }
            foreach ($couponVal['divisionOrderCoupon'] as $addKey => $addVal) {
                if (!$getData[$addKey]['price']['couponOrderDcPrice']) {
                    $getData[$addKey]['price']['couponOrderDcPrice'] = 0;
                }
                $getData[$addKey]['price']['couponOrderDcPrice'] += $addVal;
            }
        }
        // 상품별 마일리지 - 상품할인 / 회원할인 / 쿠폰할인 / 모바일앱할인 이 최종 처리된 가격으로 마일리지 지급 계산
        foreach ($getData as $dataKey => $dataVal) {
            if ($dataVal['goodsMileageExcept'] == 'n') {
                $getData[$dataKey]['mileage']['goodsMileage'] = $this->getGoodsMileageData($dataVal['mileageFl'], $dataVal['mileageGoods'], $dataVal['mileageGoodsUnit'], $dataVal['goodsCnt'], $getData[$dataKey]['price'], $getData[$dataKey]['mileageGroup'], $getData[$dataKey]['mileageGroupInfo'], $getData[$dataKey]['mileageGroupMemberInfo']);
//                unset($getData[$dataKey]['mileageFl'], $getData[$dataKey]['mileageGoods'], $getData[$dataKey]['mileageGoodsUnit']);
            }
        }

        // 마일리지 재계산 - 지급률재계산 / 지급금액차감 / 지급률차감 일 경우 - 사용 마일리지가 있을 경우
        if ($this->totalUseMileage > 0) {
            if ($this->mileageGiveInfo['give']['excludeFl'] == 'r' || $this->mileageGiveInfo['give']['excludeFl'] == 'm'  || $this->mileageGiveInfo['give']['excludeFl'] == 'p') {
                // 상품종류에 따른 기준 금액
                foreach ($getData as $dataKey => $dataVal) {
                    $standardMileageGoodsPrice[$dataKey] = $dataVal['price']['goodsPriceSum'];
                    if ($this->mileageGiveInfo['basic']['optionPrice'] === '1') {
                        $standardMileageGoodsPrice[$dataKey] = $standardMileageGoodsPrice[$dataKey] + $dataVal['price']['optionPriceSum'];
                    }
                    if ($this->mileageGiveInfo['basic']['addGoodsPrice'] === '1') {
                        $standardMileageGoodsPrice[$dataKey] = $standardMileageGoodsPrice[$dataKey] + $dataVal['price']['addGoodsPriceSum'];
                    }
                    if ($this->mileageGiveInfo['basic']['textOptionPrice'] === '1') {
                        $standardMileageGoodsPrice[$dataKey] = $standardMileageGoodsPrice[$dataKey] + $dataVal['price']['optionTextPriceSum'];
                    }

                    if ($this->mileageGiveInfo['basic']['goodsDcPrice'] === '1') {
                        $standardMileageGoodsPrice[$dataKey] = $standardMileageGoodsPrice[$dataKey] - $dataVal['price']['goodsDcPrice'];

                        // 마이앱 사용에 따른 분기 처리
                        if ($this->useMyapp) {
                            $standardMileageGoodsPrice[$dataKey] -= $dataVal['price']['myappDcPrice'];
                        }
                    }

                    if ($this->mileageGiveInfo['basic']['memberDcPrice'] === '1') {
                        $standardMileageGoodsPrice[$dataKey] = $standardMileageGoodsPrice[$dataKey] - $dataVal['price']['memberDcPrice'];
                    }
                    if ($this->mileageGiveInfo['basic']['couponDcPrice'] === '1') {
                        $standardMileageGoodsPrice[$dataKey] = $standardMileageGoodsPrice[$dataKey] - $dataVal['price']['couponGoodsDcPrice'];
                    }
                }
                $totalGoodsPrice = array_sum($standardMileageGoodsPrice);
                $totalGoodsCount = count($standardMileageGoodsPrice);

                if ($this->mileageGiveInfo['give']['excludeFl'] == 'r' || $this->mileageGiveInfo['give']['excludeFl'] == 'm') {
                    // 기준 금액으로 사용 마일리지 안분
                    $totalMileage = 0;
                    $goodsCount = 1;
                    $goodsUseMileage = [];
                    foreach ($standardMileageGoodsPrice as $standardKey => $standardVal) {
                        if ($totalGoodsCount == $goodsCount) {
                            $goodsUseMileage[$standardKey] = $this->totalUseMileage - $totalMileage;
                            $totalMileage += $goodsUseMileage[$standardKey];
                        } else {
                            $percentUseMileage = $standardVal / $totalGoodsPrice;
                            $goodsUseMileage[$standardKey] = gd_number_figure($this->totalUseMileage * $percentUseMileage, $this->mileageGiveInfo['trunc']['unitPrecision'], $this->mileageGiveInfo['trunc']['unitRound']);
                            $totalMileage += $goodsUseMileage[$standardKey];
                        }
                        $goodsCount++;
                    }

                    if ($this->mileageGiveInfo['give']['excludeFl'] == 'm') { // 지급금액 차감
                        foreach ($getData as $dataKey => $dataVal) {
                            $getData[$dataKey]['mileage']['goodsUseMileage'] = $goodsUseMileage[$dataKey];
                            $getData[$dataKey]['mileage']['goodsMileage'] = $getData[$dataKey]['mileage']['goodsMileage'] - $goodsUseMileage[$dataKey];
                            if ($getData[$dataKey]['mileage']['goodsMileage'] < 0) {
                                $getData[$dataKey]['mileage']['goodsMileage'] = 0;
                            }
                        }
                    } else if ($this->mileageGiveInfo['give']['excludeFl'] == 'r') { // 지급률 재계산
                        foreach ($getData as $dataKey => $dataVal) {
                            $price['goodsPriceSum'] = $dataVal['price']['goodsPriceSum'] - $goodsUseMileage[$dataKey];
                            $price['addGoodsPriceSum'] = $dataVal['price']['addGoodsPriceSum'];
                            $price['optionPriceSum'] = $dataVal['price']['optionPriceSum'];
                            $price['optionTextPriceSum'] = $dataVal['price']['optionTextPriceSum'];
                            $price['goodsDcPrice'] = $dataVal['price']['goodsDcPrice'];
                            $price['memberDcPrice'] = $dataVal['price']['memberDcPrice'];
                            $price['memberOverlapDcPrice'] = $dataVal['price']['memberOverlapDcPrice'];
                            $price['couponDcPrice'] = $dataVal['price']['couponGoodsDcPrice'];

                            // 마이앱 사용에 따른 분기 처리
                            if ($this->useMyapp) {
                                $price['myappDcPrice'] = $dataVal['price']['myappDcPrice'];
                            }

                            $getData[$dataKey]['mileage']['goodsMileage'] = $this->getGoodsMileageData($dataVal['mileageFl'], $dataVal['mileageGoods'], $dataVal['mileageGoodsUnit'], $dataVal['price']['goodsCnt'], $price, $dataVal['mileageGroup'], $dataVal['mileageGroupInfo'], $dataVal['mileageGroupMemberInfo']);
                            $getData[$dataKey]['mileage']['goodsUseMileage'] = $goodsUseMileage[$dataKey];
                            if ($getData[$dataKey]['mileage']['goodsMileage'] < 0) {
                                $getData[$dataKey]['mileage']['goodsMileage'] = 0;
                            }
                        }
                    }
                }
                if ($this->mileageGiveInfo['give']['excludeFl'] == 'p') { // 지급률 차감
                    $percentUseMileage = $this->totalUseMileage / $totalGoodsPrice;
                    foreach ($getData as $dataKey => $dataVal) {
                        $goodsUseMileage = gd_number_figure($getData[$dataKey]['mileage']['goodsMileage'] * $percentUseMileage, $this->mileageGiveInfo['trunc']['unitPrecision'], $this->mileageGiveInfo['trunc']['unitRound']);
                        $getData[$dataKey]['mileage']['goodsMileage'] = $getData[$dataKey]['mileage']['goodsMileage'] - $goodsUseMileage;
                        $getData[$dataKey]['mileage']['goodsUseMileage'] = $goodsUseMileage;
                        if ($getData[$dataKey]['mileage']['goodsMileage'] < 0) {
                            $getData[$dataKey]['mileage']['goodsMileage'] = 0;
                        }
                    }
                }
            }
        }

        //장바구니의 회원 혜택 계산
        $getData = $this->getMemberGroupBenefit($getData, $divisionOrderCoupon);

        $setDeliveryData = [];
        $sameDelivery = [];
        foreach ($getData as $dataKey => $dataVal) {
            $scmNo = (int)$dataVal['scmNo']; // SCM ID
            $arrScmNo[] = $scmNo; // 장바구니 SCM 정보
            $goodsNo = $dataVal['goodsNo']; // 상품 번호
            $deliverySno = $dataVal['deliverySno']; // 배송 정책
            $taxFreeFl = $dataVal['taxFreeFl'];
            $taxPercent = $taxFreeFl == 'f' ? 0 : $dataVal['taxPercent'];

            // 주문상품 교환시 교환상품금액이 0원으로 처리되지 않게 goodsPriceString제거
            if ($this->orderGoodsChange === true) unset($dataVal['goodsPriceString']);

            //가격 대체 문구가 있는경우 합에서 제외 해야 함
            if (empty($dataVal['goodsPriceString']) === false) {
                $getData[$dataKey]['price']['goodsPriceSum'] = 0 ;
                $getData[$dataKey]['price']['optionPriceSum'] = 0 ;
                $getData[$dataKey]['price']['optionTextPriceSum'] = 0 ;
                $getData[$dataKey]['price']['addGoodsPriceSum'] = 0 ;
            }

            // 상품별 가격 (상품 가격 + 옵션 가격 + 텍스트 옵션 가격 + 추가 상품 가격)
            $getData[$dataKey]['price']['goodsPriceSubtotal'] = $getData[$dataKey]['price']['goodsPriceSum'] + $getData[$dataKey]['price']['optionPriceSum'] + $getData[$dataKey]['price']['optionTextPriceSum'] + $getData[$dataKey]['price']['addGoodsPriceSum'];

            // 상품 총 판매가격
            $this->totalPrice['goodsPrice'] += $getData[$dataKey]['price']['goodsPriceSum'];

            // 상품 총 옵션가격
            $this->totalPrice['optionPrice'] += $getData[$dataKey]['price']['optionPriceSum'];

            // 상품 총 텍스트 옵션 가격
            $this->totalPrice['optionTextPrice'] += $getData[$dataKey]['price']['optionTextPriceSum'];

            // 상품 총 추가 상품 가격
            $this->totalPrice['addGoodsPrice'] += $getData[$dataKey]['price']['addGoodsPriceSum'];

            //가격 대체 문구가 없을때만 계산해야 함
            if (empty($dataVal['goodsPriceString']) === true) {
                // 상품 총 가격 & scm 별 상품 총 가격
                $this->totalGoodsPrice += $getData[$dataKey]['price']['goodsPriceSubtotal'];
                gd_isset($this->totalScmGoodsPrice[$scmNo], 0);
                $this->totalScmGoodsPrice[$scmNo] += $getData[$dataKey]['price']['goodsPriceSubtotal'];

                // 상품 할인 총 가격 & scm 별 상품 할인 총 가격
                $this->totalGoodsDcPrice += $getData[$dataKey]['price']['goodsDcPrice'];
                gd_isset($this->totalScmGoodsDcPrice[$scmNo], 0);
                $this->totalScmGoodsDcPrice[$scmNo] += $getData[$dataKey]['price']['goodsDcPrice'];

                // 상품별 총 상품 마일리지 & scm 별 총 상품 마일리지
                $this->totalGoodsMileage += $getData[$dataKey]['mileage']['goodsMileage'];
                gd_isset($this->totalScmGoodsMileage[$scmNo], 0);
                $this->totalScmGoodsMileage[$scmNo] += $getData[$dataKey]['mileage']['goodsMileage'];

                // 회원 그룹 추가 할인 총 가격 & scm 별 회원 그룹 추가 할인 총 가격
                if ($this->getChannel() != 'naverpay') {
                    $this->totalMemberDcPrice += $getData[$dataKey]['price']['memberDcPrice'];
                    gd_isset($this->totalScmMemberDcPrice[$scmNo], 0);
                    $this->totalScmMemberDcPrice[$scmNo] += $getData[$dataKey]['price']['memberDcPrice'];

                    // 회원 그룹 중복 할인 총 가격 & scm 별 회원 그룹 중복 할인 총 가격
                    $this->totalMemberOverlapDcPrice += $getData[$dataKey]['price']['memberOverlapDcPrice'];
                    gd_isset($this->totalScmMemberOverlapDcPrice[$scmNo], 0);
                    $this->totalScmMemberOverlapDcPrice[$scmNo] += $getData[$dataKey]['price']['memberOverlapDcPrice'];

                    // 회원 그룹 브랜드 할인 총 가격
                    $this->totalMemberBrandDcPrice += $getData[$dataKey]['price']['goodsMemberBrandDcPrice'];
                }

                // 회원 그룹 총 마일리지 & scm 별 회원 그룹 총 마일리지
                $this->totalMemberMileage += $getData[$dataKey]['mileage']['memberMileage'];
                gd_isset($this->totalScmMemberMileage[$scmNo], 0);
                $this->totalScmMemberMileage[$scmNo] += $getData[$dataKey]['price']['memberMileage'];

                // 상품 총 쿠폰 금액 & scm 별 상품 총 쿠폰 금액
                $this->totalCouponGoodsDcPrice += $getData[$dataKey]['price']['couponGoodsDcPrice'];
                gd_isset($this->totalScmCouponGoodsDcPrice[$scmNo], 0);
                $this->totalScmCouponGoodsDcPrice[$scmNo] += $getData[$dataKey]['price']['couponGoodsDcPrice'];

                // 마이앱 사용에 따른 분기 처리
                if ($this->useMyapp) {
                    // 마이앱 할인 총 가격 & scm 별 마이앱 할인 총 가격
                    $this->totalMyappDcPrice += $getData[$dataKey]['price']['myappDcPrice'];
                    gd_isset($this->totalScmMyappDcPrice[$scmNo], 0);
                    $this->totalScmMyappDcPrice[$scmNo] += $getData[$dataKey]['price']['myappDcPrice'];
                }

                // 상품 총 쿠폰 마일리지 & scm 별 상품 총 쿠폰 마일리지
                $this->totalCouponGoodsMileage += $getData[$dataKey]['mileage']['couponGoodsMileage'];
                if ($this->channel == 'naverpay') {   //네이버페이는 쿠폰적립파일리지 제외
                    $this->totalCouponGoodsMileage = 0;
                }
                gd_isset($this->totalScmCouponGoodsMileage[$scmNo], 0);
                $this->totalScmCouponGoodsMileage[$scmNo] += $getData[$dataKey]['mileage']['couponGoodsMileage'];
            }

            // 할인금액을 적용한 상품별 합계금액을 위해 DC요소를 마이너스 처리 함
            if ($this->getChannel() != 'naverpay' || ($naverpayConfig['useYn'] == 'y' && $naverpayConfig['saleFl'] == 'y')) {
                $getData[$dataKey]['price']['goodsPriceSubtotal'] = $getData[$dataKey]['price']['goodsPriceSubtotal'] - $getData[$dataKey]['price']['goodsDcPrice'] - $getData[$dataKey]['price']['memberDcPrice'] - $getData[$dataKey]['price']['memberOverlapDcPrice'] - $getData[$dataKey]['price']['couponGoodsDcPrice'];

                // 마이앱 사용에 따른 분기 처리
                if ($this->useMyapp) {
                    $getData[$dataKey]['price']['goodsPriceSubtotal'] -= $getData[$dataKey]['price']['myappDcPrice'];
                }

                if($getData[$dataKey]['price']['goodsPriceSubtotal'] < 0 ) $getData[$dataKey]['price']['goodsPriceSubtotal'] = 0;
            }

            // 각 상품별 부가세 계산 (상품 가격, 옵션 가격, 텍스트 옵션을 더한 가격의 부가세 계산후, 추가 상품에 대한 부가세를 더함)
            $getData[$dataKey]['price']['goodsVat'] = gd_tax_all(($getData[$dataKey]['price']['goodsPriceSum'] + $getData[$dataKey]['price']['optionPriceSum'] + $getData[$dataKey]['price']['optionTextPriceSum']), $taxPercent, $taxFreeFl);

            // 상품별 총 공급가액 및 세액, 비과세(면세)금액 (상품 가격, 옵션 가격, 텍스트 옵션 부가세에, 추가상품에 대한 부가세를 더함) 단, 할인을 제외한 순수 상품에 대한 공급가액과 부가세를 산출
            $this->totalPriceSupply += ($getData[$dataKey]['price']['goodsVat']['supply'] + $getData[$dataKey]['price']['addGoodsVat']['supply']);
            $this->totalTaxPrice += ($getData[$dataKey]['price']['goodsVat']['tax'] + $getData[$dataKey]['price']['addGoodsVat']['tax']);
            if ($taxFreeFl == 'f') {
                $this->totalFreePrice += $getData[$dataKey]['price']['goodsPriceSubtotal'];
            }

            // 사은품 설정을 위한 데이타
            $giftConf = gd_policy('goods.gift');
            if ($giftConf['giftFl'] === 'y') {
                $this->giftForData[$goodsNo]['scmNo'] = $getData[$dataKey]['scmNo'];
                $this->giftForData[$goodsNo]['cateCd'] = $getData[$dataKey]['cateCd'];
                $this->giftForData[$goodsNo]['brandCd'] = $getData[$dataKey]['brandCd'];
                $this->giftForData[$goodsNo]['price'] = gd_isset($this->giftForData[$goodsNo]['price'], 0) + $getData[$dataKey]['price']['goodsPriceSubtotal'];
                $this->giftForData[$goodsNo]['cnt'] = gd_isset($this->giftForData[$goodsNo]['cnt'], 0) + $dataVal['goodsCnt'];
                $addGoodsCnt = gd_isset($this->giftForData[$goodsNo]['addGoodsCnt'], 0);
                if(empty($dataVal['addGoods']) == false) {
                    foreach ($dataVal['addGoods'] as $addGoodsVal) {
                        $addGoodsCnt += $addGoodsVal['addGoodsCnt'];
                    }
                }
                $this->giftForData[$goodsNo]['addGoodsCnt'] = $addGoodsCnt;
            }

            $this->multiShippingOrderInfo[$getData[$dataKey]['sno']] = [
                'scmNo' => $scmNo,
                'deliverySno' => $deliverySno,
                'getKey' => count($getCart[$scmNo][$deliverySno]),
            ];
            $getData[$dataKey]['priceInfo'] = json_encode($getData[$dataKey]['price']);
            $getData[$dataKey]['mileageInfo'] = json_encode($getData[$dataKey]['mileage']);
            if ($getData[$dataKey]['goodsDeliveryFl'] == 'y') {
                if (empty($setDeliveryData[$scmNo][$deliverySno]) === true) {
                    $setDeliveryData[$scmNo][$deliverySno] = $getData[$dataKey]['sno'];
                }
            } else if ($getData[$dataKey]['goodsDeliveryFl'] == 'n' && $getData[$dataKey]['sameGoodsDeliveryFl'] == 'y') {
                if (empty($setDeliveryData[$scmNo][$deliverySno][$dataVal['goodsNo']]) === true) {
                    $setDeliveryData[$scmNo][$deliverySno][$dataVal['goodsNo']] = $getData[$dataKey]['sno'];
                }
            }

            if ($getData[$dataKey]['goodsDeliveryFl'] == 'y') {
                $getData[$dataKey]['parentCartSno'] = $setDeliveryData[$scmNo][$deliverySno];
            } else if ($getData[$dataKey]['goodsDeliveryFl'] == 'n' && $getData[$dataKey]['sameGoodsDeliveryFl'] == 'y') {
                $getData[$dataKey]['parentCartSno'] = $setDeliveryData[$scmNo][$deliverySno][$dataVal['goodsNo']];
            } else {
                $getData[$dataKey]['parentCartSno'] = $getData[$dataKey]['sno'];
            }
            unset($getData[$dataKey]['mileage']['goodsCnt']);

            // 상품혜택관리 치환코드 생성
            $getData[$dataKey] = $goodsBenefit->goodsDataFrontReplaceCode($getData[$dataKey], 'cartOrder');

            // 장바구니 상품 정보
            $getCart[$scmNo][$deliverySno][] = $getData[$dataKey];
            if ($getData[$dataKey]['goodsDeliveryFl'] != 'y' && $getData[$dataKey]['sameGoodsDeliveryFl'] == 'y') {
                $sameDelivery['goodsNo'][$scmNo][$deliverySno][gd_isset($sameDelivery['key'][$scmNo][$deliverySno], 0)] = $getData[$dataKey]['goodsNo'];
                $sameDelivery['setKey'][$scmNo][$deliverySno][$getData[$dataKey]['goodsNo']][] = $sameDelivery['key'][$scmNo][$deliverySno];
                $sameDelivery['key'][$scmNo][$deliverySno]++;

            }

            // 장바구니 상품 개수
            $cartCnt++;

            // 장바구니 SCM 업체의 상품 갯수
            $this->cartScmGoodsCnt[$scmNo] = $this->cartScmGoodsCnt[$scmNo] + 1;
        }
        unset($getAddGoods, $getOptionText, $getData, $setDeliveryData);

        if (empty($sameDelivery['setKey']) === false) {
            $setSameCart = [];
            foreach ($sameDelivery['setKey'] as $scmNo => $sVal) {
                foreach ($sVal as $deliverySno => $dVal) {
                    foreach ($dVal as $goodsNo => $kVal) {
                        foreach ($kVal as $key => $val) {
                            $setSameCart[$scmNo][$deliverySno][] = $getCart[$scmNo][$deliverySno][$val];
                        }
                    }
                }
            }
            if (count($getCart[$scmNo][$deliverySno]) == count($setSameCart[$scmNo][$deliverySno])) {
                $getCart[$scmNo][$deliverySno] = $setSameCart[$scmNo][$deliverySno];
            }
            unset($setSameCart);
        }

        // 장바구니 상품 개수
        $this->cartCnt = $cartCnt;

        return $getCart;
    }

    /**
     * 주문쿠폰금액 안분
     *
     * @param array  $goodsPrice 상품정보
     * @return array $tmpDivisionOrderCoupon 상품안분정보
     */
    public function getDivisionOrderCoupon($goodsPrice)
    {
        $tmpDivisionOrderCoupon = $tmpDivisionOrderCouponByCart = [];
        $tmpOrderCouponSum = 0;
        $totalCouponOrderDcPrice = $this->totalCouponOrderDcPrice;

        if ($totalCouponOrderDcPrice > 0) {
            $couponPolicy = gd_policy('coupon.config');

            // 주문쿠폰이 적용되는 총 주문금액 계산
            $totalOrderPriceByOrderCoupon = 0;
            foreach ($goodsPrice as $goodsNo => $val) {
                $price = $goodsPrice[$goodsNo];

                foreach ($val['goodsCnt'] as $k => $v) {
                    $totalOrderPriceByOrderCoupon += $price['goodsPrice'][$k] * $v;
                    if ($couponPolicy['couponOptPriceType'] == 'y') {
                        $totalOrderPriceByOrderCoupon += $price['optionPrice'][$k] * $v;
                    }
                    if ($couponPolicy['couponTextPriceType'] == 'y') {
                        $totalOrderPriceByOrderCoupon += $price['optionTextPrice'][$k] * $v;
                    }
                    if ($couponPolicy['couponAddPriceType'] == 'y') {
                        foreach ($goodsPrice[$goodsNo]['addGoodsCnt'][$k] as $key => $val) {
                            $totalOrderPriceByOrderCoupon += $price['addGoodsPrice'][$k][$key] * $val;
                        }
                    }
                }
            }

            $lastGoodsNo = end(array_keys($goodsPrice));
            foreach ($goodsPrice as $goodsNo => $val) {
                $price = $goodsPrice[$goodsNo];

                $lastKey = end(array_keys($val['goodsCnt']));
                foreach ($val['goodsCnt'] as $k => $v) {
                    $tmpGoodsPrice = $tmpGoodsCouponPrice = ($price['goodsPrice'][$k] * $v);
                    $tmpAddGoodsCouponPrice = 0;
                    $tmpGoodsPrice -= $price['goodsDcPrice'][$k];
                    $tmpGoodsCouponPrice -= $price['goodsDcPrice'][$k];

                    if ($couponPolicy['couponOptPriceType'] == 'y') {
                        $tmpGoodsCouponPrice += $price['optionPrice'][$k] * $v;
                    }
                    if ($couponPolicy['couponTextPriceType'] == 'y') {
                        $tmpGoodsCouponPrice += $price['optionTextPrice'][$k] * $v;
                    }
                    if ($couponPolicy['couponAddPriceType'] == 'y') {
                        foreach ($price['addGoodsCnt'][$k] as $key => $val) {
                            $tmpAddGoodsCouponPrice += $price['addGoodsPrice'][$k][$key] * $val;
                        }
                    }
                    $tmpDivisionOrderCoupon[$goodsNo]['tmpAddGoodsCouponPrice'][$k] = $tmpAddGoodsCouponPrice;
                    $tmpDivisionOrderCouponByCart[$k]['tmpAddGoodsCouponPrice'] += $tmpAddGoodsCouponPrice;


                    if ($totalOrderPriceByOrderCoupon > 0) {
                        if ($lastGoodsNo == $goodsNo && $lastKey == $k) { //마지막 상품일 때
                            if (empty($tmpAddGoodsCouponPrice) === false) { //추가상품 쿠폰가격이 있으면
                                $tmpDivisionOrderCoupon[$goodsNo]['divisionOrderCoupon'][$k] = ceil(($tmpGoodsCouponPrice * $totalCouponOrderDcPrice) / $totalOrderPriceByOrderCoupon);
                                $tmpOrderCouponSum += $tmpDivisionOrderCoupon[$goodsNo]['divisionOrderCoupon'][$k];
                            } else { //추가상품이 없어서 마지막일 때 총 주문 쿠폰할인금액에서 누계 주문쿠폰할인금액을 뺌
                                $tmpDivisionOrderCoupon[$goodsNo]['divisionOrderCoupon'][$k] = $totalCouponOrderDcPrice - $tmpOrderCouponSum;
                            }
                        } else {
                            $tmpDivisionOrderCoupon[$goodsNo]['divisionOrderCoupon'][$k] = ceil(($tmpGoodsCouponPrice * $totalCouponOrderDcPrice) / $totalOrderPriceByOrderCoupon);
                            $tmpOrderCouponSum += $tmpDivisionOrderCoupon[$goodsNo]['divisionOrderCoupon'][$k];
                        }

                        $price['couponDcPrice'][$k] += $tmpDivisionOrderCouponByCart[$k]['divisionOrderCoupon'] = $tmpDivisionOrderCoupon[$goodsNo]['divisionOrderCoupon'][$k];
                    }

                    if ($couponPolicy['couponAddPriceType'] == 'y' && $tmpAddGoodsCouponPrice > 0) {
                        if ($lastGoodsNo == $goodsNo && $lastKey == $k) {
                            $goodsDivisionCouponPrice = $totalCouponOrderDcPrice - $tmpOrderCouponSum;
                        } else {
                            $goodsDivisionCouponPrice = ceil(($tmpAddGoodsCouponPrice * $totalCouponOrderDcPrice) / $totalOrderPriceByOrderCoupon);
                            $tmpOrderCouponSum += $goodsDivisionCouponPrice;
                        }
                        $tmpDivisionOrderCoupon[$goodsNo]['divisionOrderCouponByAddGoods'][$k] = $goodsDivisionCouponPrice;
                        $tmpDivisionOrderCouponByCart[$k]['divisionOrderCouponByAddGoods'] += $goodsDivisionCouponPrice;
                    }
                    $tmpDivisionOrderCoupon[$goodsNo]['tmpGoodsPrice'][$k] = $tmpGoodsPrice - $price['couponDcPrice'][$k];
                    $tmpDivisionOrderCouponByCart[$k]['tmpGoodsPrice'] += $tmpGoodsPrice - $price['couponDcPrice'][$k];
                }
            }
        }

        return ['goods' => $tmpDivisionOrderCoupon, 'cart' => $tmpDivisionOrderCouponByCart];
    }

    /**
     * 배송비 부과방법이 상품별인 경우 와 배송비 조건별인 경우의 설정을 구분 짓고 상품별 배송비만 계산하고
     * 배송비 조건별은 별도의 변수에 담아서 루프 아래에서 처리
     *
     * @param array  $getCart      장바구니 생성 데이터
     * @param array  $deliverySnos 배송일련번호
     * @param string $address      주소
     *
     * @return mixed
     * @author Jong-tae Ahn <qnibus@godo.co.kr>
     */
    public function getDeliveryDataInfo($getCart, $deliverySnos, $address = null, $multiShippingFl = 'n', $orderInfoCd = null)
    {
        // 배송비조건 정보 가져오기
        $delivery = \App::load('\\Component\\Delivery\\DeliveryCart');
        $getDeliveryInfo = $delivery->getDataDeliveryWithGoodsNo($deliverySnos);

        $weightConf = gd_policy('basic.weight');

        // 배송비조건별 데이터 초기화
        $setDeliveryCond = $setDeliveryGoodsCond = [];

        // 지번주소로 저장되어 있는 지역별 추가배송비 비교하기 위한 조회
        if ($address !== null) {
            $postcode = new GodoCenterServerApi();
            $checkAddress = $postcode->getCurlDataPostcodeV2($address);
            $result = json_decode($checkAddress['true'], true);
        }

        // 만들어진 장바구니 데이터를 이용해 배송비 추출
        foreach ($getCart as $scmNo => $sVal) {
            foreach ($sVal as $deliverySno => $dVal) {
                $firstGoodsDeliveryCollectFl = '';
                $firstGoodsDeliveryMethodFl = '';
                $firstGoodsDeliveryMethodFlText = '';
                $visitAddress = $delivery->getVisitAddress($deliverySno, true);

                foreach ($dVal as $gKey => $gVal) {
                    $setDeliveryCharge = $getDeliveryInfo[$deliverySno]['charge'];
                    if ($getDeliveryInfo[$deliverySno]['deliveryConfigType'] == 'etc' && empty($getDeliveryInfo[$deliverySno]['charge'][$gVal['deliveryMethodFl']]) === false) {
                        $setDeliveryCharge = $getDeliveryInfo[$deliverySno]['charge'][$gVal['deliveryMethodFl']];
                    }
                    // 배송비 정보 초기화
                    $goodsDeliveryFl = $getDeliveryInfo[$deliverySno]['goodsDeliveryFl']; // 배송비 부과방법
                    $sameGoodsDeliveryFl = $getDeliveryInfo[$deliverySno]['sameGoodsDeliveryFl']; // 배송비 부과방법 - 상품별 / 동일 상품일 경우 1회만 부과 여부
                    $goodsDeliveryMethod = $getDeliveryInfo[$deliverySno]['method']; // 배송방법
                    $goodsDeliveryFixFl = $getDeliveryInfo[$deliverySno]['fixFl']; // 배송비 기준
                    $goodsDeliveryVisitPayFl = $getDeliveryInfo[$deliverySno]['deliveryVisitPayFl']; // 방문수령 배송비 부과 여부
                    $goodsDeliveryVisitAddressUseFl = empty(trim($visitAddress)) === true ? 'n' : $getDeliveryInfo[$deliverySno]['dmVisitAddressUseFl']; // 배송정보를 방문 수령지 주소로 노출 여부
                    $goodsDeliveryWholeFreeFl = $getDeliveryInfo[$deliverySno]['freeFl'] === 'y' ?: 'n'; // 무료배송인 경우 scm 무료여부
                    $goodsDeliveryCollectFl = empty($gVal['deliveryCollectFl']) === true ? 'pre' : $gVal['deliveryCollectFl'];
                    $goodsDeliveryMethodFl = empty($gVal['deliveryMethodFl']) === true ? 'delivery' : $gVal['deliveryMethodFl']; //배송방식
                    $goodsDeliveryMethodFlText = gd_get_delivery_method_display($goodsDeliveryMethodFl);

                    // 선결제, 배송방식 모두 가장최근에 담긴 상품의 정보로 업데이트 되어야 하므로 최초 한번의 정보만 담는다.
                    if(trim($firstGoodsDeliveryCollectFl) === ''){
                        $firstGoodsDeliveryCollectFl = $goodsDeliveryCollectFl;
                    }
                    if(trim($firstGoodsDeliveryMethodFl) === ''){
                        $firstGoodsDeliveryMethodFl = $goodsDeliveryMethodFl;
                        $firstGoodsDeliveryMethodFlText = $goodsDeliveryMethodFlText;
                    }

                    $goodsDeliveryPrice = 0; // 배송비
                    $goodsDeliveryAreaPrice = 0; // 지역별 배송비
                    $goodsDeliveryCollectPrice = 0; // 착불인 경우 착불 배송비
                    $goodsDeliveryWholeFreePrice = 0; // scm 무료인경우 해당 상품의 원래 배송비

                    // 해당 공급사의 배송비 정책이 있는 경우에만
                    if (empty($getDeliveryInfo[$deliverySno]) === false && $getDeliveryInfo[$deliverySno]['scmNo'] === $scmNo) {
                        switch ($goodsDeliveryFixFl) {
                            // 금액별 배송비 기준
                            case 'price':
                                // 기본적으로 판매가 포함
                                $tmp['deliveryByStandard'] = $gVal['price']['goodsPriceSum'];

                                // 금액별 배송비 기준 (옵션가 + 추가상품가 + 텍스트옵션가)
                                if (empty($getDeliveryInfo[$deliverySno]['pricePlusStandard']) === false) {
                                    if (in_array('option', $getDeliveryInfo[$deliverySno]['pricePlusStandard']) === true) {
                                        $tmp['deliveryByStandard'] += $gVal['price']['optionPriceSum'];
                                    }
                                    if (in_array('add', $getDeliveryInfo[$deliverySno]['pricePlusStandard']) === true) {
                                        $tmp['deliveryByStandard'] += $gVal['price']['addGoodsPriceSum'];
                                    }
                                    if (in_array('text', $getDeliveryInfo[$deliverySno]['pricePlusStandard']) === true) {
                                        $tmp['deliveryByStandard'] += $gVal['price']['optionTextPriceSum'];
                                    }
                                }

                                // 금액별 배송비 기준 (- 상품할인가(+ 마이앱할인가) - 상품쿠폰할인가)
                                if (empty($getDeliveryInfo[$deliverySno]['priceMinusStandard']) === false) {
                                    if (in_array('goods', $getDeliveryInfo[$deliverySno]['priceMinusStandard']) === true) {
                                        $tmp['deliveryByStandard'] -= $gVal['price']['goodsDcPrice'];

                                        // 마이앱 사용에 따른 분기 처리
                                        if ($this->useMyapp) {
                                            $tmp['deliveryByStandard'] -= $gVal['price']['myappDcPrice'];
                                        }
                                    }
                                    if (in_array('coupon', $getDeliveryInfo[$deliverySno]['priceMinusStandard']) === true) {
                                        $tmp['deliveryByStandard'] -= $gVal['price']['couponGoodsDcPrice'];
                                    }
                                    /*
                                    if (in_array('member', $getDeliveryInfo[$deliverySno]['priceMinusStandard']) === true) {
                                        $tmp['deliveryByStandard'] -= ($gVal['price']['goodsMemberDcPrice'] + $gVal['price']['goodsMemberOverlapDcPrice'] + $gVal['price']['addGoodsMemberDcPrice'] + $gVal['price']['addGoodsMemberOverlapDcPrice']);
                                    }
                                    //프론트단에서 주문쿠폰할인으로 인해 배송비가 변경되었는지 체크 (금액별 배송비 판매가 기준)
                                    if($this->changeDeliveryPriceOrderCouponFl === true){
                                        if (in_array('orderCoupon', $getDeliveryInfo[$deliverySno]['priceMinusStandard']) === true && (int)$gVal['price']['couponOrderDcPrice'] > 0) {
                                            $tmp['deliveryByStandard'] -= ($gVal['price']['couponOrderDcPrice']);
                                        }
                                    }
                                    */
                                }
                                $tmp['deliveryByCalculate'] = 'y';
                                break;

                            // 무게별 배송비 기준
                            case 'weight':
                                $tmp['deliveryByStandard'] = $gVal['goodsWeight'] * $gVal['goodsCnt'];
                                //무게별 배송비의 범위 제한을 사용하는 경우 결제를 방지 처리
                                if($getDeliveryInfo[$deliverySno]['rangeLimitFl'] === 'y' && $tmp['deliveryByStandard'] >= $getDeliveryInfo[$deliverySno]['rangeLimitWeight']){
                                    $this->orderPossible = false;
                                    $this->orderPossibleMessage = __('무게가 %s%s 이상의 상품은 구매할 수 없습니다.', $getDeliveryInfo[$deliverySno]['rangeLimitWeight'], $weightConf['unit']);
                                }
                                $tmp['deliveryByCalculate'] = 'y';
                                break;

                            // 수량별 배송비 기준
                            case 'count':
                                //추가상품의 수량을 포함할 경우
                                $deliveryGoodsCnt = $gVal['goodsCnt'];
                                if($getDeliveryInfo[$deliverySno]['addGoodsCountInclude'] === 'y'){
                                    $deliveryGoodsCnt += (int)array_sum(array_column($gVal['addGoods'], 'addGoodsCnt'));
                                }

                                $tmp['deliveryByStandard'] = $deliveryGoodsCnt;
                                $tmp['deliveryByCalculate'] = 'y';
                                break;

                            // 고정 배송비 기준
                            case 'fixed':
                                $tmp['deliveryByStandard'] = 0;
                                $tmp['deliveryByCalculate'] = 'n';
                                //$goodsDeliveryPrice = $getDeliveryInfo[$deliverySno]['charge'][0]['price'];
                                $goodsDeliveryPrice = $setDeliveryCharge[0]['price'];
                                break;

                            // 무료 배송비 기준
                            case 'free':
                                $tmp['deliveryByStandard'] = 0;
                                $tmp['deliveryByCalculate'] = 'n';
                                $goodsDeliveryPrice = 0;
                                break;

                        }

                        // 지역 확인 후 배송비 설정
                        if ($address !== null && $getDeliveryInfo[$deliverySno]['areaFl'] == 'y') {
                            if (empty($getDeliveryInfo[$deliverySno]['areaGroupList']) === false) {
                                // 지번주소로 저장되어 있는 지역별 추가배송비 비교하기 위한 조회
                                $grondAddress = $result['resultData']['addressData'][0]['groundAddress'];
                                $roadAddress = $result['resultData']['addressData'][0]['roadAddress'];

                                foreach ($getDeliveryInfo[$deliverySno]['areaGroupList'] as $areaDelivery) {
                                    // 설정된 지역별 배송비에서 동/리를 제거하고 면은 제거하지 않는다.
//                                    $rejoinAddress = [];
//                                    $splitAddress = explode(' ', $areaDelivery['addArea']);
//                                    foreach ($splitAddress as $idx => $addr) {
//                                        if ($idx > 1 && preg_match('/(.*[동|리]$)/i', $addr)) {
//                                            break;
//                                        }
//                                        $rejoinAddress[] = $addr;
//                                    }
//                                    $areaDelivery['addArea'] = implode(' ', $rejoinAddress);

                                    if (stripos(str_replace(' ', '', $address), str_replace(' ', '', $areaDelivery['addArea'])) !== false ||
                                        stripos(str_replace(' ', '', $grondAddress), str_replace(' ', '', $areaDelivery['addArea'])) !== false ||
                                        stripos(str_replace(' ', '', $roadAddress), str_replace(' ', '', $areaDelivery['addArea'])) !== false) {
                                        // 상품별조건과 배송비조건에 따라서 처리 방식 다름 (배송비조건인 경우 하단에 별도의 로직에서 계산할 때 사용할 수 있도록 처리)
                                        if ($goodsDeliveryFl !== 'y') {
                                            $goodsDeliveryAreaPrice = $areaDelivery['addPrice'];
                                        } else {
                                            $setDeliveryCond[$deliverySno]['goodsDeliveryAreaPrice'] = $areaDelivery['addPrice'];
                                        }
                                        break;
                                    }
                                }
                            }
                        }

                        // 상품별조건 배송비 계산
                        if ($goodsDeliveryFl !== 'y') {
                            if ($sameGoodsDeliveryFl !== 'y') {
                                // 배송비 계산
                                if ($tmp['deliveryByCalculate'] === 'y') {
                                    if ($getDeliveryInfo[$deliverySno]['rangeRepeat'] === 'y') {
                                        //범위 반복 설정이 되어있는 경우
                                        $deliveryStandardFinal = 0;
                                        $deliveryStandardNum = 0;
                                        //$conditionRange = $getDeliveryInfo[$deliverySno]['charge'][0];
                                        //$conditionRepeat = $getDeliveryInfo[$deliverySno]['charge'][1];
                                        $conditionRange = $setDeliveryCharge[0];
                                        $conditionRepeat = $setDeliveryCharge[1];

                                        if ($tmp['deliveryByStandard'] > 0) {
                                            $goodsDeliveryPrice = $conditionRange['price'];

                                            if ($conditionRepeat['unitEnd'] > 0) {
                                                $deliveryStandardNum = $tmp['deliveryByStandard'] - $conditionRepeat['unitStart'];
                                                if (!$deliveryStandardNum) {
                                                    $deliveryStandardNum = 0;
                                                }

                                                if ($deliveryStandardNum >= 0) {
                                                    $deliveryStandardFinal = ($deliveryStandardNum / $conditionRepeat['unitEnd']);
                                                    if (!$deliveryStandardFinal) {
                                                        $deliveryStandardFinal = 0;
                                                    }

                                                    if (preg_match('/\./', (string)$deliveryStandardFinal)) {
                                                        $deliveryStandardFinal = (int)ceil($deliveryStandardFinal);
                                                    } else {
                                                        $deliveryStandardFinal += 1;
                                                    }

                                                    $goodsDeliveryPrice += ($deliveryStandardFinal * $conditionRepeat['price']);
                                                }
                                            }
                                        }
                                    } else {
                                        //범위 반복 설정이 되어있지 않은 경우
                                        foreach ($setDeliveryCharge as $cKey => $cVal) {
                                            if ($cVal['unitEnd'] == 0) {
                                                $cVal['unitEnd'] = 999999999999;
                                            }
                                            if ($tmp['deliveryByStandard'] >= $cVal['unitStart'] && $tmp['deliveryByStandard'] < $cVal['unitEnd']) {
                                                $goodsDeliveryPrice = $cVal['price'];
                                                continue;
                                            }
                                        }
                                    }
                                }

                                // 방문수령 배송비 무료 처리
                                if ($goodsDeliveryMethodFl == 'visit' && $goodsDeliveryVisitPayFl == 'n') {
                                    $goodsDeliveryPrice = 0;
                                }

                                // 배송비 결제방법 (pre - 선불, later - 착불, both - 선불/착불) , 선불/착불 선택인 경우 장바구니에 담앗을때의 배송비 결제 방법으로 하며, 그외의 경우에는 해당 배송 정책을 따름
                                if ($getDeliveryInfo[$deliverySno]['collectFl'] !== 'both') {
                                    $goodsDeliveryCollectFl = $getDeliveryInfo[$deliverySno]['collectFl'];
                                }

                                // scm별 무료인 경우 무료 배송 처리
                                if ($getDeliveryInfo[$deliverySno]['wholeFreeFl'] === 'y') {
                                    $goodsDeliveryWholeFreeFl = 'y';
                                    $goodsDeliveryWholeFreePrice = $goodsDeliveryPrice;
                                    $goodsDeliveryPrice = 0;
                                }

                                // 배송비가 착불인 경우 배송비 0원
                                if ($goodsDeliveryCollectFl === 'later') {
                                    $goodsDeliveryCollectPrice = $goodsDeliveryPrice;
                                    $goodsDeliveryPrice = 0;
                                    if ($goodsDeliveryAreaPrice > 0) {
                                        $goodsDeliveryCollectPrice += $goodsDeliveryAreaPrice;
                                    }
                                }
                            } else {
                                // 상품별 - 동일상품일 경우 1회만 부과일 경우 배송비 초기화
                                $goodsDeliveryPrice = 0;

                                $setDeliveryCond[$deliverySno][$gVal['goodsNo']]['deliveryByStandard'] += $tmp['deliveryByStandard'];
                                $setDeliveryCond[$deliverySno][$gVal['goodsNo']]['deliveryByCalculate'] = $tmp['deliveryByCalculate'];
                                $setDeliveryCond[$deliverySno][$gVal['goodsNo']]['fixFl'] = $goodsDeliveryFixFl;
                                $setDeliveryCond[$deliverySno][$gVal['goodsNo']]['goodsDeliveryWholeFreeFl'] = $getDeliveryInfo[$deliverySno]['wholeFreeFl'];
                                if (isset($setDeliveryCond[$deliverySno][$gVal['goodsNo']]['goodsDeliveryCollectFl']) === false || empty($setDeliveryCond[$deliverySno][$gVal['goodsNo']]['goodsDeliveryCollectFl']) === true) {
                                    $setDeliveryCond[$deliverySno][$gVal['goodsNo']]['goodsDeliveryCollectFl'] = $goodsDeliveryCollectFl;
                                }
                            }
                        }
                        // 배송비조건별 계산
                        else {
                            // 배송비조건인 경우 별도로 설정하기 때문에 상단에서 설정된 배송비 초기화
                            $goodsDeliveryPrice = 0;

                            $setDeliveryCond[$deliverySno]['deliveryByStandard'] += $tmp['deliveryByStandard'];
                            $setDeliveryCond[$deliverySno]['deliveryByCalculate'] = $tmp['deliveryByCalculate'];
                            $setDeliveryCond[$deliverySno]['fixFl'] = $goodsDeliveryFixFl;
                            $setDeliveryCond[$deliverySno]['goodsDeliveryWholeFreeFl'] = $getDeliveryInfo[$deliverySno]['wholeFreeFl'];
                            if (isset($setDeliveryCond[$deliverySno]['goodsDeliveryCollectFl']) === false || empty($setDeliveryCond[$deliverySno]['goodsDeliveryCollectFl']) === true) {
                                $setDeliveryCond[$deliverySno]['goodsDeliveryCollectFl'] = $firstGoodsDeliveryCollectFl;
                            }
                        }

                        if ($goodsDeliveryFl != 'y' && $sameGoodsDeliveryFl == 'y') {

                            // 공급사 번호 설정
                            $setDeliveryCond[$deliverySno][$gVal['goodsNo']]['scmNo'] = $scmNo;

                            // 장바구니 UI에서 사용할 상품 갯수 정보
                            $setDeliveryCond[$deliverySno][$gVal['goodsNo']]['goodsLineCnt'] = $setDeliveryCond[$deliverySno][$gVal['goodsNo']]['goodsLineCnt'] + 1;
                            $setDeliveryCond[$deliverySno][$gVal['goodsNo']]['addGoodsLineCnt'] = $setDeliveryCond[$deliverySno][$gVal['goodsNo']]['addGoodsLineCnt'] + count($gVal['addGoods']);

                            // 배송방식 정보
                            $setDeliveryCond[$deliverySno][$gVal['goodsNo']]['goodsDeliveryMethodFl'] = $goodsDeliveryMethodFl;
                            $setDeliveryCond[$deliverySno][$gVal['goodsNo']]['goodsDeliveryMethodFlText'] = $goodsDeliveryMethodFlText;

                            $setDeliveryCond[$deliverySno][$gVal['goodsNo']]['goodsDeliveryVisitPayFl'] = $goodsDeliveryVisitPayFl;
                            $setDeliveryCond[$deliverySno][$gVal['goodsNo']]['goodsDeliveryVisitAddressUseFl'] = $goodsDeliveryVisitAddressUseFl;
                            $setDeliveryCond[$deliverySno][$gVal['goodsNo']]['deliveryConfigType'] = $getDeliveryInfo[$deliverySno]['deliveryConfigType'];
                        } else {
                            // 공급사 번호 설정
                            $setDeliveryCond[$deliverySno]['scmNo'] = $scmNo;

                            // 장바구니 UI에서 사용할 상품 갯수 정보
                            $setDeliveryCond[$deliverySno]['goodsLineCnt'] = $setDeliveryCond[$deliverySno]['goodsLineCnt'] + 1;
                            $setDeliveryCond[$deliverySno]['addGoodsLineCnt'] = $setDeliveryCond[$deliverySno]['addGoodsLineCnt'] + count($gVal['addGoods']);

                            // 배송방식 정보
                            $setDeliveryCond[$deliverySno]['goodsDeliveryMethodFl'] = $firstGoodsDeliveryMethodFl;
                            $setDeliveryCond[$deliverySno]['goodsDeliveryMethodFlText'] = $firstGoodsDeliveryMethodFlText;

                            $setDeliveryCond[$deliverySno]['goodsDeliveryVisitPayFl'] = $goodsDeliveryVisitPayFl;
                            $setDeliveryCond[$deliverySno]['goodsDeliveryVisitAddressUseFl'] = $goodsDeliveryVisitAddressUseFl;
                            $setDeliveryCond[$deliverySno]['deliveryConfigType'] = $getDeliveryInfo[$deliverySno]['deliveryConfigType'];
                        }

                        $setDeliveryCond[$deliverySno]['goodsDeliveryFl'] = $goodsDeliveryFl;
                        $setDeliveryCond[$deliverySno]['sameGoodsDeliveryFl'] = $sameGoodsDeliveryFl;

                        // 과세금액 설정
                        $goodsDeliveryTaxFreeFl = $getDeliveryInfo[$deliverySno]['taxFreeFl'];
                        $goodsDeliveryTaxPercent = $getDeliveryInfo[$deliverySno]['taxPercent'];

                        unset($tmp);
                    } else {
                        $gVal['orderPossible'] = 'n';
                        $gVal['orderPossibleCode'] = self::POSSIBLE_DELIVERY_SNO_NO;
                        $gVal['orderPossibleMessage'] = __('지정된 배송비 없음');
                        $this->orderPossible = false;
                    }

                    $gVal['goodsDeliveryFl'] = $goodsDeliveryFl;
                    $gVal['goodsDeliveryFixFl'] = $goodsDeliveryFixFl;
                    $gVal['goodsDeliveryMethod'] = $goodsDeliveryMethod;
                    $gVal['goodsDeliveryCollectFl'] = $goodsDeliveryCollectFl;
                    $gVal['goodsDeliveryWholeFreeFl'] = $goodsDeliveryWholeFreeFl;
                    $gVal['goodsDeliveryTaxFreeFl'] = $goodsDeliveryTaxFreeFl;
                    $gVal['goodsDeliveryTaxPercent'] = $goodsDeliveryTaxPercent;
                    $gVal['goodsDeliveryVisitPayFl'] = $goodsDeliveryVisitPayFl;
                    $gVal['goodsDeliveryVisitAddressUseFl'] = $goodsDeliveryVisitAddressUseFl;

                    $gVal['price']['goodsDeliveryPrice'] = $goodsDeliveryPrice;
                    $gVal['price']['goodsDeliveryAreaPrice'] = $goodsDeliveryAreaPrice;
                    $gVal['price']['goodsDeliveryCollectPrice'] = $goodsDeliveryCollectPrice;
                    $gVal['price']['goodsDeliveryWholeFreePrice'] = $goodsDeliveryWholeFreePrice;

                    // 반환데이터 설정
                    $getCart[$scmNo][$deliverySno][$gKey] = $gVal;

                    // 지역별 배송비를 각 배송금액에 더해야 해서 없는 경우 0으로 초기화
                    gd_isset($this->totalGoodsDeliveryAreaPrice[$deliverySno], 0);
                    if ($multiShippingFl == 'y') gd_isset($this->totalGoodsMultiDeliveryAreaPrice[$orderInfoCd][$deliverySno], 0);
                    if ($goodsDeliveryCollectFl !== 'later') {
                        $this->totalGoodsDeliveryAreaPrice[$deliverySno] += $goodsDeliveryAreaPrice;
                        if ($multiShippingFl == 'y') $this->totalGoodsMultiDeliveryAreaPrice[$orderInfoCd][$deliverySno] += $goodsDeliveryAreaPrice;
                    }

                    // 상품 배송정책별 총 배송 금액
                    gd_isset($this->totalGoodsDeliveryPolicyCharge[$deliverySno], 0);
                    if ($multiShippingFl == 'y') gd_isset($this->totalGoodsMultiDeliveryPolicyCharge[$orderInfoCd][$deliverySno], 0);
                    $this->totalGoodsDeliveryPolicyCharge[$deliverySno] += $goodsDeliveryPrice;
                    if ($multiShippingFl == 'y') $this->totalGoodsMultiDeliveryPolicyCharge[$orderInfoCd][$deliverySno] += $goodsDeliveryPrice;

                    // 상품별배송비 조건 총 배송 금액 (deliveryCalculateBy가 n인 경우의 배송비 총합)
                    if ($goodsDeliveryCollectFl !== 'later') {
                        $this->totalDeliveryCharge += $goodsDeliveryPrice + $goodsDeliveryAreaPrice;
                    }

                    // scm 별 상품 총 배송 금액
                    if ($goodsDeliveryCollectFl !== 'later') {
                        $this->totalScmGoodsDeliveryCharge[$scmNo] += $goodsDeliveryPrice + $goodsDeliveryAreaPrice;
                        if ($multiShippingFl == 'y') $this->totalScmGoodsMultiDeliveryCharge[$orderInfoCd][$scmNo] += $goodsDeliveryPrice + $goodsDeliveryAreaPrice;
                    }
                }
            }
        }

        // 배송비 부과방법이 배송비 조건별인 경우 별도 계산
        if (empty($setDeliveryCond) === false) {
            $rangeRepeatPrice = 0;
            foreach ($setDeliveryCond as $dKey => $dVal) {
                if ($dVal['goodsDeliveryFl'] != 'y' && $dVal['sameGoodsDeliveryFl'] == 'y') {
                    unset($dVal['goodsDeliveryFl'], $dVal['sameGoodsDeliveryFl']);
                    foreach ($dVal as $gKey => $gVal) {
                        $setDeliveryCond[$dKey][$gKey] = $this->getDeliveryCond($gVal, $getDeliveryInfo[$dKey], $weightConf, $orderInfoCd, $dKey, $multiShippingFl);
                    }
                } else {
                    $setDeliveryCond[$dKey] = $this->getDeliveryCond($dVal, $getDeliveryInfo[$dKey], $weightConf, $orderInfoCd, $dKey, $multiShippingFl);
                }
            }
        }

        $this->setDeliveryInfo = $setDeliveryCond;

        return $getCart;
    }

    public function getDeliveryCond($deliveryCond, $deliveryInfo, $weightConf, $orderInfoCd, $key, $multiShippingFl)
    {
        if (!isset($deliveryCond['deliveryByCalculate'])) {
            return $deliveryCond;
        }
        $setDeliveryCharge = $deliveryInfo['charge'];
        if ($deliveryCond['deliveryConfigType'] == 'etc' && empty($deliveryInfo['charge'][$deliveryCond['goodsDeliveryMethodFl']]) === false) {
            $setDeliveryCharge = $deliveryInfo['charge'][$deliveryCond['goodsDeliveryMethodFl']];
        }

        // SCM 번호 설정
        $scmNo = $deliveryCond['scmNo'];

        // 배송비 계산
        if ($deliveryCond['deliveryByCalculate'] === 'y') {
            foreach ($setDeliveryCharge as $cKey => $cVal) {
                if ($cVal['unitEnd'] == 0) {
                    $cVal['unitEnd'] = 999999999;
                }

                // 배송비 0원 처리 조건여부
                $checkZeroPrice = false;
                //계산 진행 여부
                $conditionSatisfy = false;
                if ($deliveryInfo['rangeRepeat'] === 'y') {
                    $deliveryStandardNum = 0;
                    $deliveryStandardFinal = 0;

                    //범위반복 사용중일 시
                    if ($cKey === 0) {
                        if ($deliveryCond['deliveryByStandard'] > 0) {
                            $rangeRepeatPrice = $cVal['price'];
                            continue;
                        }
                    } else if ($cKey === 1) {
                        if ($deliveryCond['deliveryByStandard'] > 0) {
                            if ($cVal['unitEnd'] > 0) {
                                $deliveryStandardNum = $deliveryCond['deliveryByStandard'] - $cVal['unitStart'];
                                if (!$deliveryStandardNum) {
                                    $deliveryStandardNum = 0;
                                }

                                if ($deliveryStandardNum >= 0) {
                                    $deliveryStandardFinal = ($deliveryStandardNum / $cVal['unitEnd']);
                                    if (!$deliveryStandardFinal) {
                                        $deliveryStandardFinal = 0;
                                    }

                                    if (preg_match('/\./', (string)$deliveryStandardFinal)) {
                                        $deliveryStandardFinal = (int)ceil($deliveryStandardFinal);
                                    } else {
                                        $deliveryStandardFinal += 1;
                                    }
                                    $rangeRepeatPrice += ($deliveryStandardFinal * $cVal['price']);
                                }
                            }
                        }
                        $cVal['price'] = $rangeRepeatPrice;
                    } else {
                        break;
                    }
                    $conditionSatisfy = true;
                } else {
                    //범위반복 미사용일 시
                    if ($deliveryCond['deliveryByStandard'] >= $cVal['unitStart'] && $deliveryCond['deliveryByStandard'] < $cVal['unitEnd']) {
                        $conditionSatisfy = true;
                    }
                }

                if ($conditionSatisfy === true) {
                    //무게별 배송비의 범위 제한을 사용하는 경우 결제를 방지 처리
                    if($deliveryInfo['fixFl'] === 'weight'){
                        if($deliveryInfo['rangeLimitFl'] === 'y' && $deliveryCond['deliveryByStandard'] >= $deliveryInfo['rangeLimitWeight']){
                            $this->orderPossible = false;
                            $this->orderPossibleMessage = __('무게가 %s%s 이상의 상품은 구매할 수 없습니다.', $deliveryInfo['rangeLimitWeight'], $weightConf['unit']);
                        }
                    }

                    // 배송 방법명
                    $deliveryCond['goodsDeliveryMethod'] = $deliveryInfo['method'];

                    // 배송비 결제방법 (pre - 선불, later - 착불, both - 선불/착불)
                    // @todo 이부분을 추가하면 사용자가 선택한 착불이 사라진다.
                    //$setDeliveryCond[$dKey]['goodsDeliveryCollectFl'] = $getDeliveryInfo[$dKey]['collectFl'];

                    // scm별 무료인 경우 무료 배송 처리
                    if ($deliveryCond['goodsDeliveryWholeFreeFl'] == 'y') {
                        $deliveryCond['goodsDeliveryWholeFreePrice'] = $cVal['price'];
                        $checkZeroPrice = true;
                    }

                    // 배송비가 착불인 경우 배송비 0원
                    if ($deliveryCond['goodsDeliveryCollectFl'] == 'later' || $this->paycoDeliveryAreaCheck === true) {
                        $deliveryCond['goodsDeliveryCollectPrice'] = $cVal['price'];
                        $checkZeroPrice = true;
                        StringUtils::strIsSet($deliveryCond['goodsDeliveryAreaPrice'], 0);
                        if ($deliveryCond['goodsDeliveryAreaPrice'] > 0) {
                            $deliveryCond['goodsDeliveryCollectPrice'] += $deliveryCond['goodsDeliveryAreaPrice'];
                        }
                    }

                    // 무료배송이나 착불 배송비로 무료인경우
                    if ($checkZeroPrice === true) {
                        $cVal['price'] = 0;
                    }

                    // 방문수령 배송비 무료 처리
                    if ($deliveryCond['goodsDeliveryMethodFl'] == 'visit' && $deliveryCond['goodsDeliveryVisitPayFl'] == 'n') {
                        $cVal['price'] = 0;
                    }

                    // 상품 배송정책별 총 배송 금액
                    $this->totalGoodsDeliveryPolicyCharge[$key] += $cVal['price'];
                    if ($multiShippingFl == 'y') $this->totalGoodsMultiDeliveryPolicyCharge[$orderInfoCd][$key] += $cVal['price'];

                    // 상품별 총 배송 금액
                    $this->totalDeliveryCharge += $cVal['price'];

                    // scm 별 상품 총 배송 금액
                    $this->totalScmGoodsDeliveryCharge[$scmNo] += $cVal['price'];
                    if ($multiShippingFl == 'y') $this->totalScmGoodsMultiDeliveryCharge[$orderInfoCd][$scmNo] += $cVal['price'];

                    // 배송비
                    $deliveryCond['goodsDeliveryPrice'] = $cVal['price'];

                    continue;
                }
            }
        } else {
            $deliveryCond['goodsDeliveryMethod'] = $deliveryInfo['method'];

            // 배송비 결제방법 (pre - 선불, later - 착불, both - 선불/착불)
            //$setDeliveryCond[$dKey]['goodsDeliveryCollectFl'] = $getDeliveryInfo[$dKey]['collectFl'];

            // 고정 배송비 기준
            if ($deliveryCond['fixFl'] === 'fixed') {
                // 배송비 0원 처리 조건여부
                $checkZeroPrice = false;

                $tmp['deliveryByStandard'] = 0;
                $tmp['deliveryByCalculate'] = 'n';

                // scm별 무료인 경우 무료 배송 처리
                if ($deliveryCond['goodsDeliveryWholeFreeFl'] === 'y') {
                    $deliveryCond['goodsDeliveryWholeFreePrice'] = $setDeliveryCharge[0]['price'];
                    $checkZeroPrice = true;
                }

                // 배송비가 착불인 경우 배송비 0원
                if ($deliveryCond['goodsDeliveryCollectFl'] === 'later') {
                    $deliveryCond['goodsDeliveryCollectPrice'] = $setDeliveryCharge[0]['price'];
                    $checkZeroPrice = true;
                }

                // 무료배송이나 착불 배송비로 무료인경우
                if ($checkZeroPrice === true) {
                    $goodsDeliveryPrice = 0;
                } else {
                    $goodsDeliveryPrice = $setDeliveryCharge[0]['price'];
                }

                // 방문수령 배송비 무료 처리
                if ($deliveryCond['goodsDeliveryMethodFl'] == 'visit' && $deliveryCond['goodsDeliveryVisitPayFl'] == 'n') {
                    $goodsDeliveryPrice = 0;
                }

                // scm 별 상품 총 배송 금액
                $this->totalScmGoodsDeliveryCharge[$scmNo] += $goodsDeliveryPrice;
                if ($multiShippingFl == 'y') $this->totalScmGoodsMultiDeliveryCharge[$orderInfoCd][$scmNo] += $goodsDeliveryPrice;

                // 상품 배송정책별 총 배송 금액
                $this->totalGoodsDeliveryPolicyCharge[$key] += $goodsDeliveryPrice;
                if ($multiShippingFl == 'y') $this->totalGoodsMultiDeliveryPolicyCharge[$orderInfoCd][$key] += $goodsDeliveryPrice;

                // 상품별 총 배송 금액
                $this->totalDeliveryCharge += $goodsDeliveryPrice;

                // 배송비
                $deliveryCond['goodsDeliveryPrice'] = $goodsDeliveryPrice;
                unset($goodsDeliveryPrice);

            } // 무료 배송비 기준
            else if ($deliveryCond['fixFl'] === 'free') {

                $tmp['deliveryByStandard'] = 0;
                $tmp['deliveryByCalculate'] = 'n';

                // 상품 배송정책별 총 배송 금액
                $this->totalGoodsDeliveryPolicyCharge[$key] = 0;
                if ($multiShippingFl == 'y') $this->totalGoodsMultiDeliveryPolicyCharge[$orderInfoCd][$key] = 0;

                // 상품별 총 배송 금액
                $this->totalDeliveryCharge += 0;

                // scm 별 상품 총 배송 금액
                $this->totalScmGoodsDeliveryCharge[$scmNo] += 0;
                if ($multiShippingFl == 'y') $this->totalScmGoodsMultiDeliveryCharge[$orderInfoCd][$scmNo] += 0;

                // 배송비
                $deliveryCond['goodsDeliveryPrice'] = 0;
            }
        }

        // 상품별 지역배송비 총 배송 금액
        gd_isset($this->totalGoodsDeliveryAreaPrice[$key], 0);
        if ($multiShippingFl == 'y') gd_isset($this->totalGoodsMultiDeliveryAreaPrice[$orderInfoCd][$key], 0);
        if ($deliveryCond['goodsDeliveryCollectFl'] != 'later') {
            $this->totalGoodsDeliveryAreaPrice[$key] += $deliveryCond['goodsDeliveryAreaPrice'];
            if ($multiShippingFl == 'y') $this->totalGoodsMultiDeliveryAreaPrice[$orderInfoCd][$key] += $deliveryCond['goodsDeliveryAreaPrice'];
        }

        // SCM별에 지역배송비 추가
        if ($deliveryCond['goodsDeliveryCollectFl'] != 'later') {
            $this->totalScmGoodsDeliveryCharge[$scmNo] += $deliveryCond['goodsDeliveryAreaPrice'];
            if ($multiShippingFl == 'y') $this->totalScmGoodsMultiDeliveryCharge[$orderInfoCd][$scmNo] += $deliveryCond['goodsDeliveryAreaPrice'];
        }

        // 상품별 총 배송 금액에 지역배송비 추가
        if ($deliveryCond['goodsDeliveryCollectFl'] != 'later') {
            $this->totalDeliveryCharge += $deliveryCond['goodsDeliveryAreaPrice'];
        }

        return $deliveryCond;
    }

    /**
     * 해외배송비 부과방법이 상품별인 경우 와 배송비 조건별인 경우의 설정을 구분 짓고 상품별 배송비만 계산하고
     * 배송비 조건별은 별도의 변수에 담아서 루프 아래에서 처리
     *
     * @param array  $getCart 장바구니 생성 데이터
     * @param        $deliverySnos
     * @param string $address 주소
     *
     * @return mixed
     * @throws Exception
     * @author   Jong-tae Ahn <qnibus@godo.co.kr>
     */
    public function getOverseasDeliveryDataInfo($getCart, $deliverySnos, $address)
    {
        // 배송비조건 정보 가져오기
        $delivery = \App::load('\\Component\\Delivery\\DeliveryCart');
        $getDeliveryInfo = $delivery->getDataDeliveryWithGoodsNo($deliverySnos);

        if (empty($getDeliveryInfo) === true) {
            // EMS 배송비 전용 조건 만들기
            $getDeliveryInfo[0] = [
                "scmNo" => 1,
                "method" => "EMS 해외배송용",
                "collectFl" => "pre",
                "fixFl" => "weight",
                "freeFl" => "",
                "pricePlusStandard" => [0],
                "priceMinusStandard" => [0],
                "goodsDeliveryFl" => "y",
                "areaFl" => "n",
                "areaGroupNo" => 0,
                "scmCommissionDelivery" => "0.00",
                "taxFreeFl" => "t",
                "taxPercent" => "10.0",
                "rangeLimitFl" => "n",
                "charge" => [
                    "unitStart" => "0.00",
                    "unitEnd" => "0.01",
                    "price" => "0.00",
                    "message" => "",
                ],
                "areaGroupList" => [],
            ];
        }

        // 배송비조건별 데이터 초기화
        $setDeliveryCond = [];

        // 만들어진 장바구니 데이터를 이용해 배송비 추출
        foreach ($getCart as $scmNo => $sVal) {
            foreach ($sVal as $deliverySno => $dVal) {
                foreach ($dVal as $gKey => $gVal) {
                    // EMS인 경우 별도의 배송비 테이블이 없어서 무조건 번호를 0으로 설정
                    if ($this->overseasDeliveryPolicy['data']['standardFl'] === 'ems') {
                        $deliverySno = 0;
                    }

                    // 배송비 정보 초기화
                    $goodsDeliveryFl = 'y'; // 배송비 부과방법은 해외배송에서 특정 배송비 조건으로 모든 상품이 묶이기 때문에 무조건 배송비조건별로 설정
                    $realGoodsDeliveryFl = $getDeliveryInfo[$deliverySno]['goodsDeliveryFl']; // 배송비조건에 묶여있는 실제 배송비조건 (무게제한 설정을 통한 제한 무게 체크를 위해 사용
                    $goodsDeliveryMethod = $getDeliveryInfo[$deliverySno]['method']; // 배송방법
                    $goodsDeliveryFixFl = $getDeliveryInfo[$deliverySno]['fixFl']; // 배송비 기준
                    $goodsDeliveryWholeFreeFl = $getDeliveryInfo[$deliverySno]['freeFl'] === 'y' ?: 'n'; // 무료배송인 경우 scm 무료여부
                    $goodsDeliveryCollectFl = empty($gVal['deliveryCollectFl']) === true ? 'pre' : $gVal['deliveryCollectFl'];
                    $goodsDeliveryPrice = 0; // 배송비
                    $goodsDeliveryAreaPrice = 0; // 지역별 배송비
                    $goodsDeliveryCollectPrice = 0; // 착불인 경우 착불 배송비
                    $goodsDeliveryWholeFreePrice = 0; // scm 무료인경우 해당 상품의 원래 배송비

                    // 해외배송비 조건이 들어가는 경우 공급사 상관없이 계산되어져야 한다.
                    if (empty($getDeliveryInfo[$deliverySno]) === false && ($getDeliveryInfo[$deliverySno]['scmNo'] === $scmNo || $this->isGlobalFront($address))) {
                        switch ($goodsDeliveryFixFl) {
                            // 금액별 배송비 기준
                            case 'price':
                                // 기본적으로 판매가 포함
                                $tmp['deliveryByStandard'] = $gVal['price']['goodsPriceSum'];

                                // 금액별 배송비 기준 (옵션가 + 추가상품가 + 텍스트옵션가)
                                if (empty($getDeliveryInfo[$deliverySno]['pricePlusStandard']) === false) {
                                    if (in_array('option', $getDeliveryInfo[$deliverySno]['pricePlusStandard']) === true) {
                                        $tmp['deliveryByStandard'] += $gVal['price']['optionPriceSum'];
                                    }
                                    if (in_array('add', $getDeliveryInfo[$deliverySno]['pricePlusStandard']) === true) {
                                        $tmp['deliveryByStandard'] += $gVal['price']['addGoodsPriceSum'];
                                    }
                                    if (in_array('text', $getDeliveryInfo[$deliverySno]['pricePlusStandard']) === true) {
                                        $tmp['deliveryByStandard'] += $gVal['price']['optionTextPriceSum'];
                                    }
                                }

                                // 금액별 배송비 기준 (- 상품할인가(+ 마이앱할인가) - 상품쿠폰할인가)
                                if (empty($getDeliveryInfo[$deliverySno]['priceMinusStandard']) === false) {
                                    if (in_array('goods', $getDeliveryInfo[$deliverySno]['priceMinusStandard']) === true) {
                                        $tmp['deliveryByStandard'] -= $gVal['price']['goodsDcPrice'];

                                        // 마이앱 사용에 따른 분기 처리
                                        if ($this->useMyapp) {
                                            $tmp['deliveryByStandard'] -= $gVal['price']['myappDcPrice'];
                                        }
                                    }
                                    if (in_array('coupon', $getDeliveryInfo[$deliverySno]['priceMinusStandard']) === true) {
                                        $tmp['deliveryByStandard'] -= $gVal['price']['couponGoodsDcPrice'];
                                    }
                                }
                                $tmp['deliveryByCalculate'] = 'y';
                                break;

                            // 무게별 배송비 기준
                            case 'weight':
                                $tmp['deliveryByStandard'] = $gVal['goodsWeight'] * $gVal['goodsCnt'];
                                $tmp['deliveryByCalculate'] = 'y';

                                // 상품별인 경우 무게제한 설정
                                if ($this->overseasDeliveryPolicy['data']['standardFl'] !== 'ems') {
                                    $weightConf = gd_policy('basic.weight');
                                    $rateWeight = ($weightConf['unit'] == 'g' ? 1000 : 1);
                                    if ($realGoodsDeliveryFl !== 'y' && $getDeliveryInfo[$deliverySno]['rangeLimitFl'] == 'y' && ($tmp['deliveryByStandard'] + $this->overseasDeliveryPolicy['data']['boxWeight']) >= ($getDeliveryInfo[$deliverySno]['rangeLimitWeight'] / $rateWeight)) {
                                        throw new Exception(__('무게가 %s kg 이상의 상품은 구매할 수 없습니다. (배송범위 제한)', number_format($getDeliveryInfo[$deliverySno]['rangeLimitWeight'] / $rateWeight, 2)));
                                    }
                                }
                                break;

                            // 수량별 배송비 기준
                            case 'count':
                                $tmp['deliveryByStandard'] = $gVal['goodsCnt'];
                                $tmp['deliveryByCalculate'] = 'y';
                                break;

                            // 고정 배송비 기준
                            case 'fixed':
                                $tmp['deliveryByStandard'] = 0;
                                $tmp['deliveryByCalculate'] = 'n';
                                $goodsDeliveryPrice = $getDeliveryInfo[$deliverySno]['charge'][0]['price'];
                                break;

                            // 무료 배송비 기준
                            case 'free':
                                $tmp['deliveryByStandard'] = 0;
                                $tmp['deliveryByCalculate'] = 'n';
                                $goodsDeliveryPrice = 0;
                                break;

                        }

                        // 지역 확인 후 배송비 설정
                        if ($address !== null && $getDeliveryInfo[$deliverySno]['areaFl'] == 'y') {
                            if (empty($getDeliveryInfo[$deliverySno]['areaGroupList']) === false) {
                                foreach ($getDeliveryInfo[$deliverySno]['areaGroupList'] as $areaDelivery) {
                                    // 설정된 지역별 배송비에서 동/리를 제거하고 면은 제거하지 않는다.
//                                    $rejoinAddress = [];
//                                    $splitAddress = explode(' ', $areaDelivery['addArea']);
//                                    foreach ($splitAddress as $idx => $addr) {
//                                        if ($idx > 1 && preg_match('/(.*[동|리]$)/i', $addr)) {
//                                            break;
//                                        }
//                                        $rejoinAddress[] = $addr;
//                                    }
//                                    $areaDelivery['addArea'] = implode(' ', $rejoinAddress);

                                    if (stripos(str_replace(' ', '', $address), str_replace(' ', '', $areaDelivery['addArea'])) !== false) {
                                        // 상품별조건과 배송비조건에 따라서 처리 방식 다름 (배송비조건인 경우 하단에 별도의 로직에서 계산할 때 사용할 수 있도록 처리)
                                        if ($goodsDeliveryFl !== 'y') {
                                            $goodsDeliveryAreaPrice = $areaDelivery['addPrice'];
                                        } else {
                                            $setDeliveryCond[$deliverySno]['goodsDeliveryAreaPrice'] = $areaDelivery['addPrice'];
                                        }
                                        break;
                                    }
                                }
                            }
                        }

                        // 배송비조건인 경우 별도로 설정하기 때문에 상단에서 설정된 배송비 초기화
                        $goodsDeliveryPrice = 0;
                        $setDeliveryCond[$deliverySno]['goodsDeliveryFl'] = $realGoodsDeliveryFl;
                        $setDeliveryCond[$deliverySno]['deliveryByStandard'] += $tmp['deliveryByStandard'];
                        $setDeliveryCond[$deliverySno]['deliveryByCalculate'] = $tmp['deliveryByCalculate'];
                        $setDeliveryCond[$deliverySno]['fixFl'] = $goodsDeliveryFixFl;
                        $setDeliveryCond[$deliverySno]['rangeLimitFl'] = $getDeliveryInfo[$deliverySno]['rangeLimitFl'];
                        $setDeliveryCond[$deliverySno]['rangeLimitWeight'] = $getDeliveryInfo[$deliverySno]['rangeLimitWeight'];
                        $setDeliveryCond[$deliverySno]['goodsDeliveryWholeFreeFl'] = $getDeliveryInfo[$deliverySno]['wholeFreeFl'];
                        if (isset($setDeliveryCond[$deliverySno]['goodsDeliveryCollectFl']) === false || empty($setDeliveryCond[$deliverySno]['goodsDeliveryCollectFl']) === true) {
                            $setDeliveryCond[$deliverySno]['goodsDeliveryCollectFl'] = $goodsDeliveryCollectFl;
                        }

                        // 공급사 번호 설정
                        $setDeliveryCond[$deliverySno]['scmNo'] = $scmNo;

                        // 장바구니 UI에서 사용할 상품 갯수 정보
                        $setDeliveryCond[$deliverySno]['goodsLineCnt'] = $setDeliveryCond[$deliverySno]['goodsLineCnt'] + 1;
                        $setDeliveryCond[$deliverySno]['addGoodsLineCnt'] = $setDeliveryCond[$deliverySno]['addGoodsLineCnt'] + count($gVal['addGoods']);

                        // 과세금액 설정
                        $goodsDeliveryTaxFreeFl = $getDeliveryInfo[$deliverySno]['taxFreeFl'];
                        $goodsDeliveryTaxPercent = $getDeliveryInfo[$deliverySno]['taxPercent'];

                        unset($tmp);
                    } else {
                        $gVal['orderPossible'] = 'n';
                        $gVal['orderPossibleCode'] = self::POSSIBLE_DELIVERY_SNO_NO;
                        $gVal['orderPossibleMessage'] = __('지정된 배송비 없음');
                        $this->orderPossible = false;
                    }

                    $gVal['goodsDeliveryFl'] = $goodsDeliveryFl;
                    $gVal['goodsDeliveryFixFl'] = $goodsDeliveryFixFl;
                    $gVal['goodsDeliveryMethod'] = $goodsDeliveryMethod;
                    $gVal['goodsDeliveryCollectFl'] = $goodsDeliveryCollectFl;
                    $gVal['goodsDeliveryWholeFreeFl'] = $goodsDeliveryWholeFreeFl;
                    $gVal['goodsDeliveryTaxFreeFl'] = $goodsDeliveryTaxFreeFl;
                    $gVal['goodsDeliveryTaxPercent'] = $goodsDeliveryTaxPercent;

                    $gVal['price']['goodsDeliveryPrice'] = $goodsDeliveryPrice;
                    $gVal['price']['goodsDeliveryAreaPrice'] = $goodsDeliveryAreaPrice;
                    $gVal['price']['goodsDeliveryCollectPrice'] = $goodsDeliveryCollectPrice;
                    $gVal['price']['goodsDeliveryWholeFreePrice'] = $goodsDeliveryWholeFreePrice;

                    // 반환데이터 설정
                    $getCart[$scmNo][$deliverySno][$gKey] = $gVal;

                    // 지역별 배송비를 각 배송금액에 더해야 해서 없는 경우 0으로 초기화
                    gd_isset($this->totalGoodsDeliveryAreaPrice[$deliverySno], 0);
                    $this->totalGoodsDeliveryAreaPrice[$deliverySno] += $gVal['price']['goodsDeliveryAreaPrice'];

                    // 상품 배송정책별 총 배송 금액
                    gd_isset($this->totalGoodsDeliveryPolicyCharge[$deliverySno], 0);
                    $this->totalGoodsDeliveryPolicyCharge[$deliverySno] += $gVal['price']['goodsDeliveryPrice'];

                    // 상품별배송비 조건 총 배송 금액 (deliveryCalculateBy가 n인 경우의 배송비 총합)
                    $this->totalDeliveryCharge += $gVal['price']['goodsDeliveryPrice'] + $this->totalGoodsDeliveryAreaPrice[$deliverySno];

                    // scm 별 상품 총 배송 금액
                    $this->totalScmGoodsDeliveryCharge[$scmNo] += $gVal['price']['goodsDeliveryPrice'] + $this->totalGoodsDeliveryAreaPrice[$deliverySno];
                }
            }
        }

        // 배송비 부과방법이 배송비 조건별인 경우 별도 계산
        if (empty($setDeliveryCond) === false) {
            foreach ($setDeliveryCond as $dKey => $dVal) {
                if (!isset($dVal['deliveryByCalculate'])) {
                    continue;
                }

                // 배송비 조건별에 따른 선 조치 (kg기준)
                switch ($dVal['fixFl']) {
                    case 'weight':
                        // 배송정책의 전체무게 + 설정 박스무게
                        $dVal['deliveryByStandard'] += $this->overseasDeliveryPolicy['data']['boxWeight'];
                        $setDeliveryCond[$dKey]['deliveryByStandard'] += $this->overseasDeliveryPolicy['data']['boxWeight'];

                        // 해외 자체배송의 배송비조건이 배송비조건별이면서 무게제한에 걸리는 경우
                        if ($this->overseasDeliveryPolicy['data']['standardFl'] !== 'ems') {
                            $weightConf = gd_policy('basic.weight');
                            $rateWeight = ($weightConf['unit'] == 'g' ? 1000 : 1);
                            if ($dVal['goodsDeliveryFl'] === 'y' && $dVal['rangeLimitFl'] === 'y' && $dVal['deliveryByStandard'] >= $dVal['rangeLimitWeight'] / $rateWeight) {
                                throw new Exception(__('무게가 %s kg 이상의 상품은 구매할 수 없습니다. (배송범위 제한)', number_format($dVal['rangeLimitWeight'] / $rateWeight, 2)));
                            }
                        }
                        break;
                }

                // SCM 번호 설정
                $scmNo = $dVal['scmNo'];

                // 배송비 계산
                if ($dVal['deliveryByCalculate'] === 'y') {
                    // EMS의 경우 kg 단위로 계산 처리
                    if ($this->overseasDeliveryPolicy['data']['standardFl'] === 'ems') {
                        // 선택 국가 정보
                        $selectedCountry = MallDAO::getInstance()->selectCountries($address);

                        // EMS요율 사용 가능 국가 여부
                        if ($selectedCountry['emsAreaCode'] === null) {
                            throw new Exception(__('EMS요율을 제공하지 않는 국가입니다.'));
                        }

                        // 전체 무게
                        $totalDeliveryByStandard = array_sum(array_column($setDeliveryCond, 'deliveryByStandard'));

                        // EMS 금액 계산
                        if ($totalDeliveryByStandard <= EmsRate::EMS_MAX_LIMIT_WEIGHT) {
                            // EMS 기본 요금
                            $emsRate = new EmsRate();
                            $emsPrice = $emsRate->getPrice($selectedCountry['emsAreaCode'], $totalDeliveryByStandard);

                            // EMS 추가 요금 부여
                            if ($this->overseasDeliveryPolicy['data']['emsAddCostUnit'] == 'won') {
                                $emsPrice += $this->overseasDeliveryPolicy['data']['emsAddCost'];
                            } else {
                                $emsPrice += ($emsPrice * ($this->overseasDeliveryPolicy['data']['emsAddCost'] / 100));
                            }

                            // 상품 배송정책별 총 배송 금액
                            $this->totalGoodsDeliveryPolicyCharge[$dKey] += $emsPrice;

                            // 상품별 총 배송 금액
                            $this->totalDeliveryCharge += $emsPrice;

                            // scm 별 상품 총 배송 금액
                            $this->totalScmGoodsDeliveryCharge[$scmNo] += $emsPrice;

                            // 배송비
                            $setDeliveryCond[$dKey]['goodsDeliveryPrice'] = $emsPrice;
                            //                            $setDeliveryCond[$dKey]['goodsDeliveryOverseasPrice'] = $emsPrice;
                        } else {
                            //throw new Exception(__('EMS 배송은 30kg 이상을 보내실 수 없습니다.'));
                            $setDeliveryCond[$dKey]['goodsDeliveryOverseasPrice'] = 0;
                        }
                    } else {
                        // 해외 기본 설정에 잡혀있는 기본설정 > 단위: 무게설정 (상단에서 계산된 kg을 기본설정 > 무게설정단위 기준으로 변경)
                        $weightConf = gd_policy('basic.weight');
                        $rateWeight = ($weightConf['unit'] == 'g' ? 1000 : 1);
                        $dVal['deliveryByStandard'] = $dVal['deliveryByStandard'] * $rateWeight;

                        foreach ($getDeliveryInfo[$dKey]['charge'] as $cKey => $cVal) {
                            if ($cVal['unitEnd'] == 0) {
                                $cVal['unitEnd'] = 999999999;
                            }

                            // 배송비 0원 처리 조건여부
                            $checkZeroPrice = false;
                            //계산 진행 여부
                            $conditionSatisfy = false;
                            if($getDeliveryInfo[$dKey]['rangeRepeat'] === 'y'){
                                $deliveryStandardNum = 0;
                                $deliveryStandardFinal = 0;

                                //범위반복 사용중일 시
                                if($cKey === 0){
                                    if ($dVal['deliveryByStandard'] > 0) {
                                        $rangeRepeatPrice = $cVal['price'];
                                        continue;
                                    }
                                }
                                else if ($cKey === 1){
                                    if ($dVal['deliveryByStandard'] > 0) {
                                        if($cVal['unitEnd'] > 0){
                                            $deliveryStandardNum = $dVal['deliveryByStandard']-$cVal['unitStart'];
                                            if(!$deliveryStandardNum){
                                                $deliveryStandardNum = 0;
                                            }

                                            if($deliveryStandardNum >= 0){
                                                $deliveryStandardFinal = ($deliveryStandardNum/$cVal['unitEnd']);
                                                if(!$deliveryStandardFinal){
                                                    $deliveryStandardFinal = 0;
                                                }

                                                if(preg_match('/\./', (string)$deliveryStandardFinal)){
                                                    $deliveryStandardFinal = (int)ceil($deliveryStandardFinal);
                                                }
                                                else {
                                                    $deliveryStandardFinal += 1;
                                                }

                                                $rangeRepeatPrice += ($deliveryStandardFinal * $cVal['price']);
                                            }
                                        }
                                    }
                                    $cVal['price'] = $rangeRepeatPrice;
                                }
                                else {
                                    break;
                                }
                                $conditionSatisfy = true;
                            }
                            else {
                                //범위반복 미사용일 시
                                if ($dVal['deliveryByStandard'] >= $cVal['unitStart'] && $dVal['deliveryByStandard'] < $cVal['unitEnd']) {
                                    $conditionSatisfy = true;
                                }
                            }

                            if ($conditionSatisfy === true) {
                                // 배송 방법명
                                $setDeliveryCond[$dKey]['goodsDeliveryMethod'] = $getDeliveryInfo[$dKey]['method'];

                                // 배송비 결제방법 (pre - 선불, later - 착불, both - 선불/착불)
                                // @todo 이부분을 추가하면 사용자가 선택한 착불이 사라진다.
                                //$setDeliveryCond[$dKey]['goodsDeliveryCollectFl'] = $getDeliveryInfo[$dKey]['collectFl'];

                                // scm별 무료인 경우 무료 배송 처리
                                if ($setDeliveryCond[$dKey]['goodsDeliveryWholeFreeFl'] == 'y') {
                                    $setDeliveryCond[$dKey]['goodsDeliveryWholeFreePrice'] = $cVal['price'];
                                    $checkZeroPrice = true;
                                }

                                // 배송비가 착불인 경우 배송비 0원
                                if ($setDeliveryCond[$dKey]['goodsDeliveryCollectFl'] == 'later') {
                                    $setDeliveryCond[$dKey]['goodsDeliveryCollectPrice'] = $cVal['price'];
                                    $checkZeroPrice = true;
                                }

                                // 무료배송이나 착불 배송비로 무료인경우
                                if ($checkZeroPrice === true) {
                                    $cVal['price'] = 0;
                                }

                                // 상품 배송정책별 총 배송 금액
                                $this->totalGoodsDeliveryPolicyCharge[$dKey] += $cVal['price'];

                                // 상품별 총 배송 금액
                                $this->totalDeliveryCharge += $cVal['price'];

                                // scm 별 상품 총 배송 금액
                                $this->totalScmGoodsDeliveryCharge[$scmNo] += $cVal['price'];

                                // 배송비
                                $setDeliveryCond[$dKey]['goodsDeliveryPrice'] = $cVal['price'];

                                continue;
                            }
                        }
                    }
                }

                // 상품별 지역배송비 총 배송 금액
                gd_isset($this->totalGoodsDeliveryAreaPrice[$dKey], 0);
                $this->totalGoodsDeliveryAreaPrice[$dKey] += $setDeliveryCond[$dKey]['goodsDeliveryAreaPrice'];

                // SCM별에 지역배송비 추가
                $this->totalScmGoodsDeliveryCharge[$scmNo] += $setDeliveryCond[$dKey]['goodsDeliveryAreaPrice'];

                // 상품별 총 배송 금액에 지역배송비 추가
                $this->totalDeliveryCharge += $setDeliveryCond[$dKey]['goodsDeliveryAreaPrice'];
            }
        }

        $this->setDeliveryInfo = $setDeliveryCond;

        return $getCart;
    }

    /**
     * 장바구니 상품 정보 - 주문가능 여부 체크
     *
     * @param array $data 장바구니 상품 정보
     *
     * @return array 장바구니 상품 정보
     */
    public function checkOrderPossible($data, $whsiFl = false)
    {
        $data['orderPossible'] = 'y';

        // 상품 출력 여부
        /*
        if ($data[$this->goodsDisplayFl] === 'n') {
            $data['orderPossible'] = 'n';
            $data['orderPossibleCode'] = self::POSSIBLE_DISPLAY_NO;
            $data['orderPossibleMessage'] = self::POSSIBLE_DISPLAY_NO_MESSAGE;
        } */

        $orderPossibleMessage = [];

        // 상품 판매 여부
        if($this->isWrite === true){
            //수기주문일 경우 PC 판매상태만 체크함
            if ($data['goodsSellFl'] === 'n') {
                $data['orderPossible'] = 'n';
                $data['orderPossibleCode'] = $data['orderPossibleCodeArr'][] = self::POSSIBLE_SELL_NO;
                $data['orderPossibleMessage'] = $orderPossibleMessage[] = __('판매중지 상품');
            }
        }
        else {
            if ($data[$this->goodsSellFl] === 'n') {
                $data['orderPossible'] = 'n';
                $data['orderPossibleCode'] = $data['orderPossibleCodeArr'][] = self::POSSIBLE_SELL_NO;
                $data['orderPossibleMessage'] = $orderPossibleMessage[] = __('판매중지 상품');
            }
        }

        // 재고 없음 체크
        if ($data['soldOutFl'] === 'y' || ($data['soldOutFl'] === 'n' && $data['stockFl'] === 'y' && ($data['totalStock'] <= 0 || $data['totalStock'] < $data['goodsCnt']))) {
            $data['orderPossible'] = 'n';
            $data['orderPossibleCode'] = $data['orderPossibleCodeArr'][] = self::POSSIBLE_SOLD_OUT;
            $data['orderPossibleMessage'] = $orderPossibleMessage[] = __('재고부족');
        }

        // 금액 0원 상품 체크
        $optionTextPrice = 0;
        if (is_array($data['optionTextInfo']) === true) {
            foreach ($data['optionTextInfo'] as $val) {
                $optionTextPrice += $val['baseOptionTextPrice'];
            }
        }
        if ($this->cartPolicy['zeroPriceOrderFl'] === 'n' && intval($data['goodsPrice'] + $data['optionPrice'] + $optionTextPrice) === 0) {
            $data['orderPossible'] = 'n';
            $data['orderPossibleCode'] = $data['orderPossibleCodeArr'][] = self::POSSIBLE_ZERO_PRICE;
            $data['orderPossibleMessage'] = $orderPossibleMessage[] = __('상품금액 없음');
        }

        // 가격 대체 문구가 있는 경우 판매금지
        if (empty($data['goodsPriceString']) === false) {
            $data['orderPossible'] = 'n';
            $data['orderPossibleCode'] = $data['orderPossibleCodeArr'][] = self::POSSIBLE_PRICE_STRING;
            $data['orderPossibleMessage'] = $orderPossibleMessage[] = __(self::POSSIBLE_PRICE_STRING_MESSAGE);
        }

        // 묶음 단위 0인 경우 (db에서 임의로 수정한 경우)
        if (isset($data['salesUnit']) && $data['salesUnit'] == 0) {
            $data['orderPossible'] = 'n';
            $data['orderPossibleCode'] = $data['orderPossibleCodeArr'][] = self::POSSIBLE_OPTION_QUANTITY_NOT_SELECT;
            $data['orderPossibleMessage'] = $orderPossibleMessage[] = __(self::POSSIBLE_OPTION_QUANTITY_NOT_SELECT_MESSAGE);
        }

        // 옵션/수량 미선택 (옵션)
        if (($data['optionFl'] == 'y' && empty($data['optionSno'])) || $data['optionTextEnteredFl'] == 'n' || $data['addGoodsSelectedFl'] == 'n') {
            if ($whsiFl) {
                $data['isCart'] = false;
            } else {
                $data['orderPossible'] = 'n';
            }
            $data['orderPossibleCode'] = $data['orderPossibleCodeArr'][] = self::POSSIBLE_OPTION_QUANTITY_NOT_SELECT;
            $data['orderPossibleMessage'] = $orderPossibleMessage[] = __(self::POSSIBLE_OPTION_QUANTITY_NOT_SELECT_MESSAGE);
        }

        // 옵션/수량 미선택 (수량)
        if (((($data['minOrderCnt'] == '1' && $data['maxOrderCnt'] == 0) == false) && ($data['goodsCnt'] < $data['minOrderCnt'] || ($data['maxOrderCnt'] > 0 && $data['goodsCnt'] > $data['maxOrderCnt']))) || (empty($data['salesUnit']) == false && $data['goodsCnt'] % $data['salesUnit'] != 0)) {
            if ($whsiFl) {
                $data['isCart'] = false;
                $data['orderPossibleCode'] = $data['orderPossibleCodeArr'][] = self::POSSIBLE_OPTION_QUANTITY_NOT_SELECT;
                $data['orderPossibleMessage'] = $orderPossibleMessage[] = __(self::POSSIBLE_OPTION_QUANTITY_NOT_SELECT_MESSAGE);
            } else {
                // 옵션기준 묶음주문/구매수량 체크
                if (((($data['minOrderCnt'] == '1' && $data['maxOrderCnt'] == 0) == false) && ($data['fixedOrderCnt'] == 'option' && ($data['goodsCnt'] < $data['minOrderCnt'] || ($data['maxOrderCnt'] > 0 && $data['goodsCnt'] > $data['maxOrderCnt'])))) || ($data['fixedSales'] == 'option' && empty($data['salesUnit']) == false && $data['goodsCnt'] % $data['salesUnit'] != 0)) {
                    $data['orderPossible'] = 'n';
                    $data['orderPossibleCode'] = $data['orderPossibleCodeArr'][] = self::POSSIBLE_OPTION_QUANTITY_NOT_SELECT;
                    $data['orderPossibleMessage'] = $orderPossibleMessage[] = __(self::POSSIBLE_OPTION_QUANTITY_NOT_SELECT_MESSAGE);
                }
            }
        }

        // 옵션 출력여부에 따른 판매금지
        if ($data['optionFl'] == 'y' && $data['optionSno'] > 0 && $data['optionViewFl'] !== 'y') {
            $data['orderPossible'] = 'n';
            $data['orderPossibleCode'] = $data['orderPossibleCodeArr'][] = self::POSSIBLE_OPTION_DISPLAY_NO;
            $data['orderPossibleMessage'] = $orderPossibleMessage[] = __('옵션 구매불가');
        }

        // 옵션 판매여부에 따른 판매금지
        if ($data['optionFl'] == 'y' && $data['optionSno'] > 0 && $data['optionSellFl'] !== 'y') {
            $data['orderPossible'] = 'n';
            $data['orderPossibleCode'] = $data['orderPossibleCodeArr'][] = self::POSSIBLE_OPTION_SELL_NO;
            $data['orderPossibleMessage'] = $orderPossibleMessage[] = __('옵션 구매불가');
        }

        // 옵션 사용안함에 따른 판매금지 (단, 장바구니에 옵션번호가 있는 경우)
        if ($data['optionSno'] > 0 && !empty($data['optionValue1']) && !empty($data['optionName']) && $data['optionFl'] === 'n') {
            $data['orderPossible'] = 'n';
            $data['orderPossibleCode'] = $data['orderPossibleCodeArr'][] = self::POSSIBLE_OPTION_NOT_AVAILABLE;
            $data['orderPossibleMessage'] = $orderPossibleMessage[] = __('옵션 구매불가');
        }

        // 상품 판매기간에 따른 판매금지
        if (empty($data['salesStartYmd']) === false && empty($data['salesEndYmd']) === false) {
            if ($data['salesStartYmd'] != '0000-00-00 00:00:00' && $data['salesStartYmd'] > date('Y-m-d H:i:s')) {
                $data['orderPossible'] = 'n';
                $data['orderPossibleCode'] = $data['orderPossibleCodeArr'][] = self::POSSIBLE_SALE_DATE_START;
                $data['orderPossibleMessage'] = $orderPossibleMessage[] = __('판매시작 전');
            }
            if ($data['salesEndYmd'] != '0000-00-00 00:00:00' && $data['salesEndYmd'] < date('Y-m-d H:i:s')) {
                $data['orderPossible'] = 'n';
                $data['orderPossibleCode'] = $data['orderPossibleCodeArr'][] = self::POSSIBLE_SALE_DATE_END;
                $data['orderPossibleMessage'] = $orderPossibleMessage[] = __('판매종료');
            }
        }

        // 상품 옵션 재고 관련
        $data['stockOver'] = 'n';
        if ($data['stockFl'] == 'y' && $data['stockCnt'] < $data['goodsCnt'] && $data['optionFl'] === 'y' && $data['optionSno'] > 0) {
            if($whsiFl == false ){
                $data['orderPossible'] = 'n';
                $data['orderPossibleCode'] = $data['orderPossibleCodeArr'][] = self::POSSIBLE_STOCK_OVER;
                $data['stockOver'] = 'y';
            } else {
                $data['isCart'] = false;
            }

            // 타 옵션의 재고가 있는 경우
            if ($data['totalStock'] > $data['stockCnt']) {
                $data['orderPossibleMessage'] = $orderPossibleMessage[] = __('옵션 구매불가');
            } else {
                $data['orderPossibleMessage'] = $orderPossibleMessage[] = __('재고부족');
            }
        }

        //옵션이 삭제된 경우
        if ($data['optionFl'] == 'y' && empty($data['stockCnt']) === true && empty($data['optionSellFl']) === true  && empty($data['optionViewFl']) === true && $data['orderPossibleCode'] != self::POSSIBLE_OPTION_QUANTITY_NOT_SELECT) {
            if($whsiFl == false ){
                $data['orderPossible'] = 'n';
                $data['orderPossibleCode'] = $data['orderPossibleCodeArr'][] = self::POSSIBLE_ORDER_NO;
                $data['orderPossibleMessage'] = $orderPossibleMessage[] = __('옵션 구매불가');
                $data['stockOver'] = 'y';
            }
            else {
                $data['orderPossibleMessage'] = $orderPossibleMessage[] = __('옵션 구매불가');
            }
        }

        // 배송비 정책이 없는 경우 판매금지
        if (empty($data['deliverySno']) === true) {
            $data['orderPossible'] = 'n';
            $data['orderPossibleCode'] = $data['orderPossibleCodeArr'][] = self::POSSIBLE_DELIVERY_SNO_NO;
            $data['orderPossibleMessage'] = $orderPossibleMessage[] = __('지정된 배송비 없음');
        }

        // 성인인증 상품 관련
        if ($data['onlyAdultFl'] == 'y') {
            if($this->isWrite === true){
                //수기주문일 시 비회원이거나 회원이면서 성인인증이 되지 않은 회원은 성인인증상품 주문 불가
                if (
                    (gd_use_ipin() || gd_use_auth_cellphone())
                    &&
                    (
                        $this->isLogin === false
                        ||
                        ($this->isLogin === true && $this->members['adultFl'] != 'y')
                        ||
                        ($this->isLogin === true && $this->members['adultFl'] == 'y' && (strtotime($this->_memInfo['adultConfirmDt']) < strtotime("-1 year", time())))
                    )
                ) {
                    $data['orderPossible'] = 'n';
                    $data['orderPossibleCode'] = $data['orderPossibleCodeArr'][] = self::POSSIBLE_ONLY_ADULT;
                    $data['orderPossibleMessage'] = $orderPossibleMessage[] = self::POSSIBLE_ONLY_ADULT_MESSAGE;
                }
            }
            else {
                if ((gd_use_ipin() || gd_use_auth_cellphone()) && (!Session::has('certAdult') && (!Session::has('member') || (Session::has('member') && $this->members['adultFl'] != 'y')))) {
                    $data['orderPossible'] = 'n';
                    $data['orderPossibleCode'] = $data['orderPossibleCodeArr'][] = self::POSSIBLE_ONLY_ADULT;
                    $data['orderPossibleMessage'] = $orderPossibleMessage[] = self::POSSIBLE_ONLY_ADULT_MESSAGE;
                }
            }
        }

        // 회원등급 여부
        switch ($data['goodsPermission']) {
            case 'member':
                if($this->isWrite === true){
                    if ($this->isLogin !== true) {
                        $data['orderPossible'] = 'n';
                        $data['orderPossibleCode'] = $data['orderPossibleCodeArr'][] = self::POSSIBLE_ONLY_MEMBER;
                        $data['orderPossibleMessage'] = $orderPossibleMessage[] = __('회원전용 상품');
                    }
                }
                else {
                    if (!MemberUtil::isLogin()) {
                        $data['orderPossible'] = 'n';
                        $data['orderPossibleCode'] = $data['orderPossibleCodeArr'][] = self::POSSIBLE_ONLY_MEMBER;
                        $data['orderPossibleMessage'] = $orderPossibleMessage[] = __('회원전용 상품');
                    }
                }
                break;
            case 'group':
                $group = explode(INT_DIVISION, $data['goodsPermissionGroup']);
                if (!in_array($this->members['groupSno'], $group)) {
                    $data['orderPossible'] = 'n';
                    $data['orderPossibleCode'] = $data['orderPossibleCodeArr'][] = self::POSSIBLE_ONLY_MEMBER_GROUP;
                    $data['orderPossibleMessage'] = $orderPossibleMessage[] = __('특정 회원등급전용 상품');
                }
                break;
        }

        // 접근권한 제한
        switch ($data['goodsAccess']) {
            case 'member':
                if($this->isWrite === true){
                    if ($this->isLogin !== true) {
                        $data['orderPossible'] = 'n';
                        $data['orderPossibleCode'] = $data['orderPossibleCodeArr'][] = self::POSSIBLE_ACCESS_RESTRICTION;
                        $data['orderPossibleMessage'] = $orderPossibleMessage[] = __(self::POSSIBLE_ACCESS_RESTRICTION_MESSAGE);
                    }
                }
                else {
                    if (!MemberUtil::isLogin()) {
                        $data['orderPossible'] = 'n';
                        $data['orderPossibleCode'] = $data['orderPossibleCodeArr'][] = self::POSSIBLE_ACCESS_RESTRICTION;
                        $data['orderPossibleMessage'] = $orderPossibleMessage[] = __(self::POSSIBLE_ACCESS_RESTRICTION_MESSAGE);;
                    }
                }
                break;
            case 'group':
                $group = explode(INT_DIVISION, $data['goodsAccessGroup']);
                if (!in_array($this->members['groupSno'], $group)) {
                    $data['orderPossible'] = 'n';
                    $data['orderPossibleCode'] = $data['orderPossibleCodeArr'][] = self::POSSIBLE_ACCESS_RESTRICTION;
                    $data['orderPossibleMessage'] = $orderPossibleMessage[] = __(self::POSSIBLE_ACCESS_RESTRICTION_MESSAGE);;
                }
                break;
        }

        //상품 결제수단제한
        if($this->isWrite === true){
            if($this->isLogin === false){
                if($data['payLimit'] && $data['payLimitFl'] == 'y'){
                    if($this->_memInfo['settleGb'] != 'all') {
                        $payLimit = explode(STR_DIVISION, $data['payLimit']);
                        if (!in_array('gb', $payLimit)) {
                            $data['orderPossible'] = 'n';
                            $data['orderPossibleCode'] = $data['orderPossibleCodeArr'][] = self::POSSIBLE_NOT_FIND_PAYMENT;
                            $data['orderPossibleMessage'] = $orderPossibleMessage[] = __('사용가능한 결제수단 없음');
                        }
                    }
                }
            }
            else {
                if($data['payLimit'] && $data['payLimitFl'] == 'y'){
                    if($this->_memInfo['settleGb'] != 'all') { // 회원 등급 결제수단 제한이 있을 때
                        if (is_array($this->_memInfo['settleGb']) === false) {
                            $settleGb = Util::matchSettleGbDataToString($this->_memInfo['settleGb']);
                        } else {
                            $settleGb = $this->_memInfo['settleGb'];
                        }
                        $payLimit = explode(STR_DIVISION, $data['payLimit']);
                        $payLimit = array_intersect($settleGb, $payLimit);
                        if (count($payLimit) == 0) {
                            $data['orderPossible'] = 'n';
                            $data['orderPossibleCode'] = $data['orderPossibleCodeArr'][] = self::POSSIBLE_NOT_FIND_PAYMENT;
                            $data['orderPossibleMessage'] = $orderPossibleMessage[] = __('사용가능한 결제수단 없음');
                        }
                    } else { // 회원 등급 결제수단 제한이 없을 때 상품 결제수단 제한 확인
                        $payLimit = explode(STR_DIVISION, $data['payLimit']);
                        if (!in_array('gb', $payLimit) && !in_array('gm', $payLimit) && !in_array('gd', $payLimit)) {
                            $data['orderPossible'] = 'n';
                            $data['orderPossibleCode'] = $data['orderPossibleCodeArr'][] = self::POSSIBLE_NOT_FIND_PAYMENT;
                            $data['orderPossibleMessage'] = $orderPossibleMessage[] = __('사용가능한 결제수단 없음');
                        }
                    }
                }
            }
        }
        else {
            if (empty($this->_memInfo) === false && $data['payLimit'] && $data['payLimitFl'] == 'y') {
                $payLimit = explode(STR_DIVISION, $data['payLimit']);
                $payLimit = array_intersect($this->_memInfo['settleGb'], $payLimit);
                if (count($payLimit) == 0 && !Globals::get('gGlobal.isFront')) {
                    $data['orderPossible'] = 'n';
                    $data['orderPossibleCode'] = $data['orderPossibleCodeArr'][] = self::POSSIBLE_NOT_FIND_PAYMENT;
                    $data['orderPossibleMessage'] = $orderPossibleMessage[] = __('사용가능한 결제수단 없음');
                }
            }
        }


        if($this->isWrite !== true){
            $this->orderCheckoutDataPossible['naverpay'] = false;
            if(\Globals::get('gNaverPay.useYn')== 'y'){
                $naverpay = new NaverPay();
                $checkGodosResult = $naverpay->checkGoods($data['goodsNo']);
                if($checkGodosResult['result'] != 'y') {
                    $data['orderPossibleMessage'] = $orderPossibleMessage[] = '네이버페이 구매 불가 상품';
                }
                else {
                    $this->orderCheckoutDataPossible['naverpay'] = true;
                }
            }

            $payco = new Payco();
            $this->orderCheckoutDataPossible['payco'] = false;
            if(!$payco->canOrderPayco($data['goodsNo'])){
                $data['orderPossibleMessage'] = $orderPossibleMessage[] = 'PAYCO 바로구매 불가 상품';
            }
            else {
                $this->orderCheckoutDataPossible['payco'] = true;
            }

            // 카카오페이가 승인완료, 실제 사용하기면서 예외상품이 있다면
            $this->orderCheckoutDataPossible['kakaopay'] = false;
            $kakaopayConf = \App::load('\\Component\\Payment\\Kakaopay\\KakaopayConfig');
            $kakaopayData = $kakaopayConf->exceptChkByKakaopay($data);
            if(empty($kakaopayData['pgId']) === false && $kakaopayData['testYn'] == 'N' && $kakaopayData['fl'] == 'y'){
                $data['orderPossibleMessage'] = $orderPossibleMessage[] = '카카오페이 구매 불가 상품';
            }
        }

        $data['orderPossibleMessageList'] = array_unique($orderPossibleMessage);
        // 구매 가능여부 체크
        if ($data['orderPossible'] === 'n') {
            $this->orderPossible = false;
        }

        return $data;
    }

    /**
     * 장바구니 상품 정보 - 회원 추가 할인 여부 설정
     *
     * @param array $data 장바구니 상품 정보
     *
     * @return array 추가 상품 정보
     */
    public function getMemberDcFlInfo($data)
    {
        // 회원 추가 할인 여부
        $this->memberDc['dc'] = false;

        // 회원 추가 할인 제외 카테고리 체크를 위한 GoodsNo 배열
        $this->memberDc['dc_category'] = [];

        // 회원 중복 할인 여부
        $this->memberDc['overlap'] = false;

        // 회원 추가 할인 포함 카테고리 체크를 위한 GoodsNo 배열
        $this->memberDc['overlap_category'] = [];

        if (empty($this->_memInfo) === false) {
            // 회원등급 추가할인 브랜드별 할인율 체크
            if ($this->_memInfo['fixedOrderTypeDc'] == 'brand') {
                $this->memberDc['sumDcBrandPercent'] = 0;

                foreach ($this->_memInfo['dcBrandInfo']->goodsDiscount as $goodsDiscount) { // 브랜드 할인율
                    $this->memberDc['sumDcBrandPercent'] += $goodsDiscount;
                }
                $this->memberDc['totalDcBrandBankPercent'] = $this->memberDc['sumDcBrandPercent'];
            }

            if ($this->_memInfo['dc' . ucwords($this->_memInfo['dcType'])] > 0 || $this->memberDc['totalDcBrandBankPercent'] > 0) {
                // 회원 추가 할인 적용
                $data['addDcFl'] = true;

                // 추가 할인 적용제외 - SCM
                if ($data['addDcFl'] === true && empty($this->_memInfo['dcExOption']) === false && in_array('scm', $this->_memInfo['dcExOption']) === true) {
                    if (empty($this->_memInfo['dcExScm']) === false && in_array($data['scmNo'], $this->_memInfo['dcExScm']) === true) {
                        $data['addDcFl'] = false;
                    }
                }

                // 추가 할인 적용제외 - 카테고리 (대표 카테고리만 확인후에 아래에서 현재 카테고리 전부를 확인 처리 함)
                if ($data['addDcFl'] === true && empty($this->_memInfo['dcExOption']) === false && in_array('category', $this->_memInfo['dcExOption']) === true && empty($this->_memInfo['dcExCategory']) === false) {
                    if (in_array($data['cateCd'], $this->_memInfo['dcExCategory']) === true) {
                        $data['addDcFl'] = false;
                    } else {
                        $this->memberDc['dc_category'][] = $data['goodsNo'];
                        $this->memberDc['dc_category'] = array_unique($this->memberDc['dc_category']);
                    }
                }

                // 추가 할인 적용제외 - 브랜드
                if ($data['addDcFl'] === true && empty($this->_memInfo['dcExOption']) === false && in_array('brand', $this->_memInfo['dcExOption']) === true) {
                    if (empty($this->_memInfo['dcExBrand']) === false && in_array($data['brandCd'], $this->_memInfo['dcExBrand']) === true) {
                        $data['addDcFl'] = false;
                    }
                }

                // 추가 할인 적용제외 - 상품
                if ($data['addDcFl'] === true && empty($this->_memInfo['dcExOption']) === false && in_array('goods', $this->_memInfo['dcExOption']) === true) {
                    if (empty($this->_memInfo['dcExGoods']) === false && in_array($data['goodsNo'], $this->_memInfo['dcExGoods']) === true) {
                        $data['addDcFl'] = false;
                    }
                }

                // 타임세일 - 회원등급 혜택 적용안함
                if ($data['memberBenefitExcept'] == 'y') {
                    $data['addDcFl'] = false;
                }

                // 회원 추가 할인 적용으로 된 경우 회원 추가 할인 사용
                if ($data['addDcFl'] === true) {
                    // 회원 추가 할인 여부
                    $this->memberDc['dc'] = true;
                }
            } else {
                $data['addDcFl'] = false;
            }

            // 회원 중복 할인 여부 설정 (적용 대상이 있어야만 중복 할인 적용)
            if ($this->_memInfo['overlapDc' . ucwords($this->_memInfo['overlapDcType'])] > 0 && empty($this->_memInfo['overlapDcOption']) === false) {
                // 회원 중복 할인 적용 제외
                $data['overlapDcFl'] = false;

                // 중복 할인 적용제외 - SCM
                if ($data['overlapDcFl'] === false && in_array('scm', $this->_memInfo['overlapDcOption']) === true) {
                    if (empty($this->_memInfo['overlapDcScm']) === false && in_array($data['scmNo'], $this->_memInfo['overlapDcScm']) === true) {
                        $data['overlapDcFl'] = true;
                    }
                }

                // 중복 할인 적용제외 - 카테고리 (대표 카테고리만 확인후에 아래에서 현재 카테고리 전부를 확인 처리 함)
                if ($data['overlapDcFl'] === false && in_array('category', $this->_memInfo['overlapDcOption']) === true && empty($this->_memInfo['overlapDcCategory']) === false) {
                    if (in_array($data['cateCd'], $this->_memInfo['overlapDcCategory']) === true) {
                        $data['overlapDcFl'] = true;
                    } else {
                        $this->memberDc['overlap_category'][] = $data['goodsNo'];
                        $this->memberDc['overlap_category'] = array_unique($this->memberDc['overlap_category']);
                    }
                }

                // 중복 할인 적용제외 - 브랜드
                if ($data['overlapDcFl'] === false && in_array('brand', $this->_memInfo['overlapDcOption']) === true) {
                    if (empty($this->_memInfo['overlapDcBrand']) === false && in_array($data['brandCd'], $this->_memInfo['overlapDcBrand']) === true) {
                        $data['overlapDcFl'] = true;
                    }
                }

                // 중복 할인 적용제외 - 상품
                if ($data['overlapDcFl'] === false && in_array('goods', $this->_memInfo['overlapDcOption']) === true) {
                    if (empty($this->_memInfo['overlapDcGoods']) === false && in_array($data['goodsNo'], $this->_memInfo['overlapDcGoods']) === true) {
                        $data['overlapDcFl'] = true;
                    }
                }

                // 타임세일 - 회원등급 혜택 적용안함
                if ($data['memberBenefitExcept'] == 'y') {
                    $data['overlapDcFl'] = false;
                }

                // 회원 중복 할인 적용으로 된 경우 회원 중복 할인 사용
                if ($data['overlapDcFl'] === true) {
                    $this->memberDc['overlap'] = true;
                }
            } else {
                $data['overlapDcFl'] = false;
            }
        }

        return $data;
    }

    /**
     * 회원 추가 할인과 중복 할인을 위한 카테고리 코드 정보
     *
     * @return array 카테고리 코드 정보
     */
    protected function getMemberDcForCateCd()
    {
        $arrCateCd = [];
        // $this->memberDc['dc'] 가 true 이고 $this->memberDc['dc_category'] 가 배열인경우 (회원 추가 할인 적용인 경우 제외 카테고리 체크를 해서 제외여부를 확인하기 위해)
        // $this->memberDc['overlap'] 가 false 이고 $this->memberDc['overlap_category'] 가 배열인경우에 (회원 중복 할인 미적용일때 카테고리 체크를 해서 적용여부를 확인하기 위해)
        if (($this->memberDc['dc'] === true && empty($this->memberDc['dc_category']) === false) || ($this->memberDc['overlap'] === false && empty($this->memberDc['overlap_category']) === false)) {
            $arrGoodsNo = array_merge((array)$this->memberDc['dc_category'], (array)$this->memberDc['overlap_category']);
            $arrGoodsNo = array_unique($arrGoodsNo);
            $strSQL = "SELECT goodsNo, cateCd FROM " . DB_GOODS_LINK_CATEGORY . " WHERE goodsNo IN (" . implode(', ', $arrGoodsNo) . ")";
            $result = $this->db->query($strSQL);
            while ($data = $this->db->fetch($result)) {
                $arrCateCd[$data['goodsNo']][] = $data['cateCd'];
            }
        }

        return $arrCateCd;
    }

    /**
     * 장바구니 상품 정보 - 추가 상품 정보
     *
     * @param array $arrAddGoodsNo 추가 상품 번호
     *
     * @return array 추가 상품 정보
     */
    protected function getAddGoodsInfo($arrAddGoodsNo)
    {
        $mallBySession = SESSION::get(SESSION_GLOBAL_MALL);

        // 필드 정보
        $arrFieldAddGoods = DBTableField::setTableField('tableAddGoods');

        // 추가 상품 디비 정보
        $getAddGoods = [];
        if (empty($arrAddGoodsNo) === false) {
            $arrAddGoodsNo = array_values(array_unique($arrAddGoodsNo, SORT_NUMERIC));

            $strSQL = "SELECT " . implode(', ', $arrFieldAddGoods) . " FROM " . DB_ADD_GOODS . " as ag  WHERE ag.addGoodsNo IN (" . implode(', ', $arrAddGoodsNo) . ")";

            /**해외몰 관련 **/
            if($mallBySession) {
                $arrFieldAddGoodsGlobal = DBTableField::setTableField('tableAddGoodsGlobal',null,['mallSno']);
                $strSQLGlobal = "SELECT " . implode(', ', $arrFieldAddGoodsGlobal) . " FROM ".DB_ADD_GOODS_GLOBAL." WHERE addGoodsNo IN (" . implode(', ', $arrAddGoodsNo) . ") AND mallSno = '".$mallBySession['sno']."'";

                $tmpData = $this->db->query_fetch($strSQLGlobal);
                $globalData = array_combine (array_column($tmpData, 'addGoodsNo'), $tmpData);
            }

            //매입처 관련 정보
            if(gd_is_plus_shop(PLUSSHOP_CODE_PURCHASE) === true) {
                $strPurchaseSQL = 'SELECT purchaseNo,purchaseNm FROM ' . DB_PURCHASE . ' g  WHERE delFl = "n"';
                $tmpPurchaseData = $this->db->query_fetch($strPurchaseSQL);
                $purchaseData = array_combine(array_column($tmpPurchaseData, 'purchaseNo'), array_column($tmpPurchaseData, 'purchaseNm'));
            }

            $result = $this->db->query($strSQL);
            while ($data = $this->db->fetch($result)) {
                // 추가상품 태그가 제대로 장바구니 페이지에서 보여지도록 처리
                $data['goodsNm'] =  stripslashes($data['goodsNm']);

                if($mallBySession && $globalData[$data['addGoodsNo']]) {
                    $data['goodsNmStandard'] = $data['goodsNm'];
                    $arrFieldAddGoods[] = 'goodsNmStandard';
                    $data = array_replace_recursive($data, array_filter(array_map('trim',$globalData[$data['addGoodsNo']])));
                }

                //매입처 관련 정보
                if(gd_is_plus_shop(PLUSSHOP_CODE_PURCHASE) === false || (gd_is_plus_shop(PLUSSHOP_CODE_PURCHASE) === true && !in_array($data['purchaseNo'],array_keys($purchaseData))))  {
                    unset($data['purchaseNo']);
                }

                foreach ($arrFieldAddGoods as $agVal) {
                    if (in_array(
                        $agVal, [
                            'imageStorage',
                            'imagePath',
                            'imageNm',
                        ]
                    )) {
                        if ($agVal === 'imageNm') {
                            // 상품 이미지 처리 @todo 상품 사이즈 설정 값을 가지고 와서 이미지 사이즈 변경을 할것
                            $getAddGoods[$data['addGoodsNo']]['addGoodsImage'] = gd_html_preview_image($data['imageNm'], $data['imagePath'], $data['imageStorage'], 40, 'add_goods', $getAddGoods[$data['addGoodsNo']]['goodsNm'], 'class="imgsize-s"', false, false);
                        }
                    } else {
                        $getAddGoods[$data['addGoodsNo']][$agVal] = $data[$agVal];
                    }
                }
            }
        }

        return $getAddGoods;
    }

    /**
     * 장바구니 상품 정보 - 텍스트 옵션 정보
     *
     * @param array $arrOptionTextSno 텍스트 옵션 번호
     *
     * @return array 텍스트 옵션 정보
     */
    protected function getOptionTextInfo($arrOptionTextSno)
    {
        // 필드 정보
        $arrExclude = [
            'goodsNo',
            'mustFl',
            'inputLimit',
        ];
        $arrFieldOptionText = DBTableField::setTableField('tableGoodsOptionText', null, $arrExclude);

        // 텍스트 옵션 정보
        $getOptionText = [];
        if (empty($arrOptionTextSno) === false) {
            $arrOptionTextSno = array_values(array_unique($arrOptionTextSno, SORT_NUMERIC));
            $strSQL = "SELECT sno, " . implode(', ', $arrFieldOptionText) . " FROM " . DB_GOODS_OPTION_TEXT . " WHERE sno IN (" . implode(', ', $arrOptionTextSno) . ")";
            $result = $this->db->query($strSQL);
            while ($data = $this->db->fetch($result)) {
                $getOptionText[$data['sno']]['optionSno'] = $data['sno'];
                $getOptionText[$data['sno']]['optionName'] = $data['optionName'];
                $getOptionText[$data['sno']]['baseOptionTextPrice'] = $data['addPrice'];
            }
        }

        return $getOptionText;
    }

    /**
     * 장바구니 상품 정보 - 상품별 상품 할인 설정
     * 상품금액의 총합이 아닌 순수 상품판매 단가의 할인율을 먼저 구한 뒤 반환할때 상품수량 곱함
     *
     * @param string $goodsDiscountFl 상품 할인 여부
     * @param int $goodsDiscount 상품 할인 금액 or Percent
     * @param string $goodsDiscountUnit Percent or Price
     * @param int $goodsCnt 상품 수량
     * @param array $goodsPrice 상품 가격 정보
     * @param string $fixedGoodsDiscount 상품할인금액기준
     * @param string $goodsDiscountGroup 상품할인대상
     * @param json $goodsDiscountGroupMemberInfo 상품할인 회원 정보
     *
     * @return int 상품할인금액
     */
    protected function getGoodsDcData($goodsDiscountFl, $goodsDiscount, $goodsDiscountUnit, $goodsCnt, $goodsPrice, $fixedGoodsDiscount = null, $goodsDiscountGroup = null, $goodsDiscountGroupMemberInfo = null)
    {
        // 상품 할인 금액
        $goodsDcPrice = $goodsPriceTmp = 0;
        $fixedGoodsDiscountData = explode(STR_DIVISION, $fixedGoodsDiscount);
        $goodsPriceTmp = $goodsPrice['goodsPriceSum'];

        if (in_array('option', $fixedGoodsDiscountData) === true) $goodsPriceTmp +=$goodsPrice['optionPriceSum'];
        if (in_array('text', $fixedGoodsDiscountData) === true) $goodsPriceTmp +=$goodsPrice['optionTextPriceSum'];

        // 상품금액 단가 계산
        $goodsPrice['goodsPrice'] = ($goodsPriceTmp / $goodsCnt);

        // 상품 할인 기준 금액 처리
        $tmp['discountByPrice'] = $goodsPrice['goodsPrice'];

        // 절사 내용
        $tmp['trunc'] = Globals::get('gTrunc.goods');

        // 상품 할인을 사용하는 경우 상품 할인 계산
        if ($goodsDiscountFl === 'y') {
            switch ($goodsDiscountGroup) {
                case 'group':
                    $goodsDiscountGroupMemberInfoData = json_decode($goodsDiscountGroupMemberInfo, true);
                    $discountKey = array_flip($goodsDiscountGroupMemberInfoData['groupSno'])[$this->_memInfo['groupSno']];

                    if ($discountKey >= 0) {
                        if ($goodsDiscountGroupMemberInfoData['goodsDiscountUnit'][$discountKey] === 'percent') {
                            $discountPercent = $goodsDiscountGroupMemberInfoData['goodsDiscount'][$discountKey] / 100;

                            // 상품할인금액
                            $goodsDcPrice = gd_number_figure($tmp['discountByPrice'] * $discountPercent, $tmp['trunc']['unitPrecision'], $tmp['trunc']['unitRound']) * $goodsCnt;
                        } else {
                            // 상품금액보다 상품할인금액이 클 경우 상품금액으로 변경
                            if ($goodsDiscountGroupMemberInfoData['goodsDiscount'][$discountKey] > $goodsPrice['goodsPrice']) $goodsDiscountGroupMemberInfoData['goodsDiscount'][$discountKey] = $goodsPrice['goodsPrice'];
                            // 상품할인금액 (정액인 경우 해당 설정된 금액으로)
                            $goodsDcPrice = $goodsDiscountGroupMemberInfoData['goodsDiscount'][$discountKey] * $goodsCnt;
                        }
                    }
                    break;
                case 'member':
                default:
                    if ($goodsDiscountUnit === 'percent') {
                        $discountPercent = $goodsDiscount / 100;

                        // 상품할인금액
                        $goodsDcPrice = gd_number_figure($tmp['discountByPrice'] * $discountPercent, $tmp['trunc']['unitPrecision'], $tmp['trunc']['unitRound']) * $goodsCnt;
                    } else {
                        // 상품금액보다 상품할인금액이 클 경우 상품금액으로 변경
                        if ($goodsDiscount > $goodsPrice['goodsPrice']) $goodsDiscount = $goodsPrice['goodsPrice'];
                        // 상품할인금액 (정액인 경우 해당 설정된 금액으로)
                        $goodsDcPrice = $goodsDiscount * $goodsCnt;
                    }
                    if ($goodsDiscountGroup == 'member' && empty($this->members['memNo']) === true) {
                        $goodsDcPrice = 0;
                    }
                    break;
            }
        }

        return $goodsDcPrice;
    }

    /**
     * 장바구니 상품 정보 - 회원 그룹 마일리지 금액
     *
     * @param array  $memInfo    회원그룹 마일리지 정보
     * @param array  $goodsPrice 상품 가격 정보
     * @param bool   $isAddGoodsDivision
     * @param string $goodsType  상품종류 (goods|addGoods)
     *
     * @return int 회원 그룹 마일리지 금액
     */
    protected function getMemberMileageData($memInfo, $goodsPrice)
    {
        // 회원 그룹 마일리지 금액
        $memberMileage = 0;
        $couponPolicy = gd_policy('coupon.config');

        if ($this->mileageGiveInfo['info']['useFl'] === 'y') {

            // 회원 그룹 추가 할인 기준 금액 처리
            $tmp['memberDcByPrice'] = $goodsPrice['goodsPriceSum'];

            // 상품종류에 따른 기준 금액 재설정
            if (empty($memInfo['fixedRateOption']) === false) {
                if (in_array('option', $memInfo['fixedRateOption']) == '1') {
                    $tmp['memberDcByPrice'] = $tmp['memberDcByPrice'] + $goodsPrice['optionPriceSum'];
                }
                if (in_array('goods', $memInfo['fixedRateOption']) == '1') {
                    $tmp['memberDcByPrice'] = $tmp['memberDcByPrice'] + $goodsPrice['addGoodsPriceSum'];
                }
                if (in_array('text', $memInfo['fixedRateOption']) == '1') {
                    $tmp['memberDcByPrice'] = $tmp['memberDcByPrice'] + $goodsPrice['optionTextPriceSum'];
                }
            }
            // 할인/적립시 적용금액 기준이 `결제금액`일 경우 해당 상품에 할인될 쿠폰금액 차감
            if ($memInfo['fixedRatePrice'] == 'settle') {
                $tmp['memberDcByPrice'] -= $goodsPrice['goodsDcPrice'];
                $memberDcByCouponPrice = $goodsPrice['goodsPriceSum'];
                $memberDcByCouponAddPrice = 0;
                if ($couponPolicy['couponOptPriceType'] == 'y') $memberDcByCouponPrice += $goodsPrice['optionPriceSum'];
                if ($couponPolicy['couponTextPriceType'] == 'y') $memberDcByCouponPrice += $goodsPrice['addGoodsPriceSum'];
                if ($couponPolicy['couponAddPriceType'] == 'y') $memberDcByCouponAddPrice += $goodsPrice['addGoodsPriceSum'];

                if ($memberDcByCouponAddPrice > 0 && $goodsPrice['couponDcPrice'] > 0) {
                    $tmpDivisionCouponGoodsPrice = ceil(($memberDcByCouponPrice * $goodsPrice['couponDcPrice']) / ($memberDcByCouponPrice + $memberDcByCouponAddPrice));
                    $tmp['memberDcByPrice'] -= $tmpDivisionCouponGoodsPrice;
                } else {
                    $tmp['memberDcByPrice'] -= $goodsPrice['couponDcPrice'];
                }

                // 마이앱 사용에 따른 분기 처리
                if ($this->useMyapp) {
                    if ($goodsPrice['myappDcPrice'] > 0) {
                        $tmp['memberDcByPrice'] -= $goodsPrice['myappDcPrice'];
                    }
                }
            }

            // 회원 그룹별 추가 마일리지
            if ($memInfo['mileageLine'] <= $tmp['memberDcByPrice']) {
                if ($memInfo['mileageType'] === 'percent') {
                    $memberMileagePercent = $memInfo['mileagePercent'] / 100;
                    $memberMileage = gd_number_figure($tmp['memberDcByPrice'] * $memberMileagePercent, $this->mileageGiveInfo['trunc']['unitPrecision'], $this->mileageGiveInfo['trunc']['unitRound']);
                } else {
                    $memberMileage = $memInfo['mileagePrice'];
                }
            }
        }

        return $memberMileage;
    }

    /**
     * 장바구니 상품 정보 - 회원 그룹별 추가 할인과 중복 할인
     *
     * @todo 회원info에 DcPrice 정보를 현재 사용하지 않기 때문에 만약 사용하게 되면 암분하는 로직이 추가 작성되어야 함
     *
     * @param int     $goodsNo     상품번호
     * @param array   $memInfo     회원그룹 마일리지 정보
     * @param array   $goodsPrice  상품 가격 정보
     * @param array   $arrCateCd   상품 가격 정보
     * @param boolean $addDcFl     회원 그룹 추가 할인 여부
     * @param boolean $overlapDcFl 회원 그룹 중복 할인 여부
     *
     * @return array 회원 그룹별 추가 할인과 중복 할인 금액 정보
     */
    protected function getMemberDcPriceData($goodsNo, $memInfo, $goodsPrice, $arrCateCd, $addDcFl, $overlapDcFl, $arrBrandCd = null)
    {
        // 회원 그룹별 추가 할인과 중복 할인
        $memberDcPrice = 0;
        $memberOverlapDcPrice = 0;

        $couponPolicy = gd_policy('coupon.config');

        // 회원그룹 추가 할인과 중복 할인 계산할 기준 금액 처리
        $tmp['memberDcByPrice'] = $goodsPrice['goodsPriceSum'];

        // 상품종류에 따른 기준 금액 재설정
        if (empty($memInfo['fixedRateOption']) === false) {
            if (in_array('option', $memInfo['fixedRateOption']) == '1') {
                $tmp['memberDcByPrice'] += $goodsPrice['optionPriceSum'];
            }
            if (in_array('goods', $memInfo['fixedRateOption']) == '1') {
                $tmp['memberDcByPrice'] += $goodsPrice['addGoodsPriceSum'];
            }
            if (in_array('text', $memInfo['fixedRateOption']) == '1') {
                $tmp['memberDcByPrice'] += $goodsPrice['optionTextPriceSum'];
            }
        }
        // 할인/적립시 적용금액 기준이 `결제금액`일 경우 해당 상품에 할인될 쿠폰금액 차감
        if ($memInfo['fixedRatePrice'] == 'settle') {
            $tmp['memberDcByPrice'] -= ($goodsPrice['goodsDcPrice'] + $goodsPrice['couponDcPrice']);

            // 마이앱 사용에 따른 분기 처리
            if ($this->useMyapp) {
                $tmp['memberDcByPrice'] -= $goodsPrice['myappDcPrice'];
            }

            if ($tmp['memberDcByPrice'] < 0) $tmp['memberDcByPrice'] = 0;
        }

        // 절사 내용
        $tmp['trunc'] = Globals::get('gTrunc.member_group');

        // 회원 등급별 추가 할인 체크
        if ($addDcFl === true && empty($arrCateCd[$goodsNo]) === false) {
            // 해당 상품이 연결된 카테고리 체크
            foreach ($arrCateCd[$goodsNo] as $gVal) {
                if (isset($memInfo['dcExCategory']) && in_array($gVal, $memInfo['dcExCategory'])) {
                    $addDcFl = false;
                }
            }
        }

        // 금액 체크
        if ($addDcFl === true && $tmp['memberDcByPrice'] < $memInfo['dcLine']) {
            $addDcFl = false;
        }

        // 회원 등급별 중복 할인 체크
        if ($overlapDcFl === false && empty($arrCateCd[$goodsNo]) === false) {
            // 해당 상품이 연결된 카테고리 체크
            foreach ($arrCateCd[$goodsNo] as $gVal) {
                if (isset($memInfo['overlapDcCategory']) && in_array($gVal, $memInfo['overlapDcCategory'])) {
                    $overlapDcFl = true;
                }
            }
        }

        // 금액 체크
        if ($overlapDcFl === true && $tmp['memberDcByPrice'] < $memInfo['overlapDcLine']) {
            $overlapDcFl = false;
        }

        // 회원그룹 추가 할인
        if ($addDcFl === true) {
            if ($memInfo['dcType'] === 'percent') {

                // 브랜드 할인율
                if ($memInfo['fixedOrderTypeDc'] == 'brand') {
                    foreach ($arrBrandCd[$goodsNo] as $gKey => $gVal) {
                        foreach ($memInfo['dcBrandInfo']->cateCd AS $mKey => $mVal) {
                            if ($gKey == $mVal) {
                                $memInfo['dcPercent'] = $memInfo['dcBrandInfo']->goodsDiscount[$mKey];
                            }
                        }
                    }
                }
                $memberDcPercent = $memInfo['dcPercent'] / 100;
                $memberDcPrice = gd_number_figure(($tmp['memberDcByPrice'] * $memberDcPercent), $tmp['trunc']['unitPrecision'], $tmp['trunc']['unitRound']);
            } else {
                $memberDcPrice = $memInfo['dcPrice'];
            }

            // 상품할인이 적용된 상품금액보다 회원할인액이 더 큰 경우 회원할인액 조정
            $exceptMemberDcPrice = $goodsPrice['goodsPriceSum'] + $goodsPrice['optionPriceSum'] + $goodsPrice['optionTextPriceSum'] + $goodsPrice['addGoodsPriceSum'] - $goodsPrice['goodsDcPrice'] - $goodsPrice['memberDcPrice'] - $goodsPrice['memberOverlapDcPrice'];

            if ($this->useMyapp) { // 마이앱 사용에 따른 분기 처리
                $exceptMemberDcPrice -= $goodsPrice['myappDcPrice'];
            }

            if ($memberDcPrice > $exceptMemberDcPrice) {
                $memberDcPrice = $exceptMemberDcPrice;
            }
        }

        // 회원그룹 중복 할인
        if ($overlapDcFl === true) {
            if ($memInfo['dcType'] === 'percent') {
                $memberDcPercent = $memInfo['overlapDcPercent'] / 100;
                $memberOverlapDcPrice = gd_number_figure($tmp['memberDcByPrice'] * $memberDcPercent, $tmp['trunc']['unitPrecision'], $tmp['trunc']['unitRound']);
            } else {
                $memberOverlapDcPrice = $memInfo['overlapDcPrice'];
            }

            // 상품할인이 적용된 상품금액보다 회원할인액이 더 큰 경우 회원할인액 조정
            $exceptMemberOverlapDcPrice = $goodsPrice['goodsPriceSum'] + $goodsPrice['optionPriceSum'] + $goodsPrice['optionTextPriceSum'] + $goodsPrice['addGoodsPriceSum'] - $goodsPrice['goodsDcPrice'] - $goodsPrice['memberDcPrice'] - $goodsPrice['memberOverlapDcPrice'];

            if ($this->useMyapp) { // 마이앱 사용에 따른 분기 처리
                $exceptMemberOverlapDcPrice -= $goodsPrice['myappDcPrice'];
            }

            if ($memberOverlapDcPrice > $exceptMemberOverlapDcPrice) {
                $memberOverlapDcPrice = $exceptMemberOverlapDcPrice;
            }
        }

        $setData['addDcFl'] = $addDcFl;
        $setData['overlapDcFl'] = $overlapDcFl;
        $setData['memberDcPrice'] = $memberDcPrice;
        $setData['memberOverlapDcPrice'] = $memberOverlapDcPrice;
        $setData['dcPercent'] = $memInfo['dcPercent'];
        $setData['overlapDcPercent'] = $memInfo['overlapDcPercent'];

        return $setData;
    }

    /**
     * 장바구니 상품 정보 - 상품/추가상품 마일리지 금액
     *
     * @param string $mileageFl        마일리지 설정 종류
     * @param int    $mileageGoods     마일리지 금액 or Percent
     * @param string $mileageGoodsUnit Percent or Price
     * @param int    $goodsCnt         상품 수량
     * @param array  $goodsPrice       상품 가격 정보
     * @param string $mileageGroup       마일리지 지급 대상
     * @param string $mileageGroupInfo       마일리지 지급 대상 회원그룹
     * @param json   $mileageGroupMemberInfo       마일리지 지급 대상 회원 정보
     *
     * @return int 상품 마일리지 금액
     */
    protected function getGoodsMileageData($mileageFl, $mileageGoods, $mileageGoodsUnit, $goodsCnt, $goodsPrice, $mileageGroup = null, $mileageGroupInfo = null, $mileageGroupMemberInfo = null)
    {
        // 상품 마일리지 금액
        $goodsMileage = 0;

        // 마일리지 지급을 사용하는 경우 마일리지 계산
        if ($this->mileageGiveInfo['info']['useFl'] === 'y') {
            // 마일리지 계산을 위한 기준 금액 처리
            $tmp['mileageByPrice'] = $goodsPrice['goodsPriceSum'];

            // 상품종류에 따른 기준 금액 재설정
            if ($this->mileageGiveInfo['basic']['optionPrice'] === '1') {
                $tmp['mileageByPrice'] = $tmp['mileageByPrice'] + $goodsPrice['optionPriceSum'];
            }
            if ($this->mileageGiveInfo['basic']['addGoodsPrice'] === '1') {
                $tmp['mileageByPrice'] = $tmp['mileageByPrice'] + $goodsPrice['addGoodsPriceSum'];
            }
            if ($this->mileageGiveInfo['basic']['textOptionPrice'] === '1') {
                $tmp['mileageByPrice'] = $tmp['mileageByPrice'] + $goodsPrice['optionTextPriceSum'];
            }
            if ($this->mileageGiveInfo['basic']['goodsDcPrice'] === '1') {
                $tmp['mileageByPrice'] = $tmp['mileageByPrice'] - $goodsPrice['goodsDcPrice'];

                // 마이앱 사용에 따른 분기 처리
                if ($this->useMyapp) {
                    // 상품할인가에 마이앱할인 포함
                    $tmp['mileageByPrice'] -= $goodsPrice['myappDcPrice'];
                }
            }
            if ($this->mileageGiveInfo['basic']['memberDcPrice'] === '1') {
                $tmp['mileageByPrice'] = $tmp['mileageByPrice'] - $goodsPrice['memberDcPrice'] - $goodsPrice['memberOverlapDcPrice'];
            }
            if ($this->mileageGiveInfo['basic']['couponDcPrice'] === '1') {
                $tmp['mileageByPrice'] = $tmp['mileageByPrice'] - $goodsPrice['couponDcPrice'] - $goodsPrice['couponOrderDcPrice'];
            }

            // 통합 설정인 경우 마일리지
            if ($mileageFl == 'c') {
                // 마일리지 지급 여부
                $mileageGiveFl = true;
                if ($mileageGroup == 'group') { //마일리지 지급대상(특정회원등급)
                    $mileageGroupInfoData = explode(INT_DIVISION, $mileageGroupInfo);

                    $mileageGiveFl = in_array(Session::get('member.groupSno'), $mileageGroupInfoData);
                }

                if ($mileageGiveFl === true) {
                    if ($this->mileageGiveInfo['give']['giveType'] == 'priceUnit') { // 금액 단위별
                        $mileagePrice = floor($tmp['mileageByPrice'] / $this->mileageGiveInfo['give']['goodsPriceUnit']);
                        $goodsMileage = gd_number_figure($mileagePrice * $this->mileageGiveInfo['give']['goodsMileage'], $this->mileageGiveInfo['trunc']['unitPrecision'], $this->mileageGiveInfo['trunc']['unitRound']);
                    } else if ($this->mileageGiveInfo['give']['giveType'] == 'cntUnit') { // 수량 단위별 (추가상품수량은 제외)
                        $goodsMileage = gd_number_figure($goodsCnt * $this->mileageGiveInfo['give']['cntMileage'], $this->mileageGiveInfo['trunc']['unitPrecision'], $this->mileageGiveInfo['trunc']['unitRound']);
                    } else { // 구매금액의 %
                        $mileagePercent = $this->mileageGiveInfo['give']['goods'] / 100;
                        $goodsMileage = gd_number_figure($tmp['mileageByPrice'] * $mileagePercent, $this->mileageGiveInfo['trunc']['unitPrecision'], $this->mileageGiveInfo['trunc']['unitRound']);
                    }
                }
            }

            // 개별 설정인 경우 마일리지
            if ($mileageFl == 'g') {
                if ($mileageGroup == 'group') { //마일리지 지급대상(특정회원등급)
                    $mileageGroupMemberInfoData = json_decode($mileageGroupMemberInfo, true);
                    $mileageKey = array_flip($mileageGroupMemberInfoData['groupSno'])[$this->_memInfo['groupSno']];
                    if ($mileageKey >= 0) {
                        $mileageGoodsUnit = gd_isset($mileageGroupMemberInfoData['mileageGoodsUnit'][$mileageKey], $mileageGoodsUnit);
                        $mileagePercent = $mileageGroupMemberInfoData['mileageGoods'][$mileageKey] / 100;
                        if ($mileageGoodsUnit === 'percent') {
                            // 상품 마일리지
                            $goodsMileage = gd_number_figure($tmp['mileageByPrice'] * $mileagePercent, $this->mileageGiveInfo['trunc']['unitPrecision'], $this->mileageGiveInfo['trunc']['unitRound']);
                        } else {
                            // 상품 마일리지 (정액인 경우 해당 설정된 금액으로)
                            $goodsMileage = $mileageGroupMemberInfoData['mileageGoods'][$mileageKey] * $goodsCnt;
                        }
                    }
                } else {
                    $mileagePercent = $mileageGoods / 100;
                    if ($mileageGoodsUnit === 'percent') {
                        // 상품 마일리지
                        $goodsMileage = gd_number_figure($tmp['mileageByPrice'] * $mileagePercent, $this->mileageGiveInfo['trunc']['unitPrecision'], $this->mileageGiveInfo['trunc']['unitRound']);
                    } else {
                        // 상품 마일리지 (정액인 경우 해당 설정된 금액으로)
                        $goodsMileage = $mileageGoods * $goodsCnt;
                    }
                }
            }
        }

        // 상품 마일리지 적립이 0보다 작으면 0
        if ($goodsMileage < 0) {
            $goodsMileage = 0;
        }

        return $goodsMileage;
    }

    /**
     * 장바구니 상품 정보 - 상품 쿠폰 금액 및 마일리지
     *
     * @param array $goodsPrice 상품의 금액 종류들 (판매가, 옵션가, 텍스트옵션가, 추가상품가...)
     * @param string $memberCouponNo 장바구니에 적용된 회원쿠폰의 고유번호 (INT_DIVISION 으로 구분되어 중복 적용 가능)
     * @param array $aTotalPrice 주문서 상품쿠폰 변경가능설정일때 총금액
     * @param string $isAllFl 장바구니데이터 읽을때 대상이 1개인지 2개이상인지 구분값(T면 다수로봄 - 1개일수도있지만 체크안하는 곳에선 상관없음)
     *
     * @return array 회원 그룹별 추가 할인과 중복 할인 금액 정보
     *
     * @author su
     */
    protected function getMemberCouponPriceData($goodsPrice, $memberCouponNo, $aTotalPrice = array(), $isAllFl = 'T')
    {
        // 쿠폰 모듈
        $coupon = \App::load('\\Component\\Coupon\\Coupon');
        $goodsMemberCouponPrice = $coupon->getMemberCouponPrice($goodsPrice, $memberCouponNo, $aTotalPrice, $isAllFl);

        return $goodsMemberCouponPrice;
    }

    /**
     * 실결제 금액(settlePrice)를 계산해 반환한다.
     * 장바구니 상품의 최종 계산으로 주문서 작성단계에서 발생되는 사용 쿠폰/마일리지/예치금등의 데이터를 설정하고 반환한다.
     * 이곳은 주문서에 입력된 금액을 토대로 최종 결제금액을 완성한다.
     * 총결제금액, SCM별 금액, 배송비, 각종 할인정보 및 로그를 처리
     *
     * @dependency getCartGoodsData() 반드시 먼저 실행된 후 계산된 값을 이용해 작동한다
     *
     * @param array $requestData 사용한 마일리지/예치금 정보가 담긴 reqeust 정보
     *
     * @return array 결제시 저장되는 최종 상품들의 정보
     * @throws Exception
     */
    public function setOrderSettleCalculation($requestData)
    {
        // 전체 할인금액 초기화 = 총 상품금액 - 총 상품할인 적용된 결제금액
        $this->totalDcPrice = $this->totalGoodsPrice + $this->totalDeliveryCharge - $this->totalSettlePrice;
        $orderPrice['totalOrderDcPrice'] = $this->totalDcPrice;

        // 회원 쿠폰 번호 없이 쿠폰 할인 / 적립 금액이 넘어 온 경우 경고
        if (!$requestData['couponApplyOrderNo']) {
            if ($requestData['totalCouponOrderDcPrice'] > 0) {
                throw new Exception(__("결제 할 금액이 일치하지 않습니다.\n할인/적립 금액이 변경되었을 수 있습니다.\n새로고침 후 다시 시도해 주세요.(4)"));
            }
            if ($requestData['totalCouponDeliveryDcPrice'] > 0) {
                throw new Exception(__("결제 할 금액이 일치하지 않습니다.\n할인/적립 금액이 변경되었을 수 있습니다.\n새로고침 후 다시 시도해 주세요.(5)"));
            }
            if ($requestData['totalCouponOrderMileage'] > 0) {
                throw new Exception(__("결제 할 금액이 일치하지 않습니다.\n할인/적립 금액이 변경되었을 수 있습니다.\n새로고침 후 다시 시도해 주세요.(6)"));
            }
        }

        // 주문 쿠폰 계산 - 쿠폰사용에 따른 회원등급부분이 취소가 되는 경우가있어서 예치금이나 마일리지보다 먼저 계산하도록 순서를 바꿈
        $couponNos = explode(INT_DIVISION, $requestData['couponApplyOrderNo']);

        if (count($couponNos) > 0) {
            if ($requestData['couponApplyOrderNo'] != '') {
                $goodsPriceArr = [
                    'goodsPriceSum' => $this->totalPrice['goodsPrice'],
                    'optionPriceSum' => $this->totalPrice['optionPrice'],
                    'optionTextPriceSum' => $this->totalPrice['optionTextPrice'],
                    'addGoodsPriceSum' => $this->totalPrice['addGoodsPrice'],
                ];
                $coupon = \App::load(\Component\Coupon\Coupon::class);
                $orderCouponPrice = $coupon->getMemberCouponPrice($goodsPriceArr, $requestData['couponApplyOrderNo']);
                foreach ($orderCouponPrice['memberCouponAlertMsg'] as $orderCouponNo => $limitMsg) {
                    if ($limitMsg) {
                        unset($orderCouponPrice['memberCouponAddMileage'][$orderCouponNo]);
                        unset($orderCouponPrice['memberCouponSalePrice'][$orderCouponNo]);
                        unset($orderCouponPrice['memberCouponDeliveryPrice'][$orderCouponNo]);
                    }
                }
                $totalCouponOrderDcPrice = array_sum($orderCouponPrice['memberCouponSalePrice']);
                $totalCouponDeliveryDcPrice = array_sum($orderCouponPrice['memberCouponDeliveryPrice']);
                $totalCouponOrderMileage = array_sum($orderCouponPrice['memberCouponAddMileage']);

                gd_isset($totalCouponOrderDcPrice, 0);
                gd_isset($totalCouponDeliveryDcPrice, 0);
                gd_isset($totalCouponOrderMileage, 0);

                gd_isset($requestData['totalCouponOrderDcPrice'], 0);
                gd_isset($requestData['totalCouponDeliveryDcPrice'], 0);
                gd_isset($requestData['totalCouponOrderMileage'], 0);

                if ($requestData['totalCouponOrderDcPrice'] > $totalCouponOrderDcPrice) {
                    throw new Exception(__("결제 할 금액이 일치하지 않습니다.\n할인/적립 금액이 변경되었을 수 있습니다.\n새로고침 후 다시 시도해 주세요.(1)"));
                }
                if ($requestData['totalCouponDeliveryDcPrice'] > $totalCouponDeliveryDcPrice) {
                    throw new Exception(__("결제 할 금액이 일치하지 않습니다.\n할인/적립 금액이 변경되었을 수 있습니다.\n새로고침 후 다시 시도해 주세요.(2)"));
                }
                if ($requestData['totalCouponOrderMileage'] > $totalCouponOrderMileage) {
                    throw new Exception(__("결제 할 금액이 일치하지 않습니다.\n할인/적립 금액이 변경되었을 수 있습니다.\n새로고침 후 다시 시도해 주세요.(3)"));
                }

                if ($requestData['totalCouponOrderDcPrice'] > 0) {
                    $this->totalCouponOrderDcPrice = $requestData['totalCouponOrderDcPrice'];
                }
                if ($requestData['totalCouponDeliveryDcPrice'] > 0) {
                    $this->totalCouponDeliveryDcPrice = $requestData['totalCouponDeliveryDcPrice'];
                }
                if ($requestData['totalCouponOrderMileage'] > 0) {
                    $this->totalCouponOrderMileage = $requestData['totalCouponOrderMileage'];
                }
            }

            // 쿠폰을 사용했고 사용설정에 쿠폰만 사용설정일때 처리
            if ($requestData['totalCouponOrderDcPrice'] > 0 || $requestData['totalCouponDeliveryDcPrice'] > 0 || $requestData['totalCouponOrderMileage'] > 0) {
                $couponConfig = gd_policy('coupon.config');
                if ($couponConfig['couponUseType'] == 'y' && $couponConfig['chooseCouponMemberUseType'] == 'coupon') {
                    $this->totalSettlePrice += $this->totalSumMemberDcPrice;
                    $this->totalMileage -= $this->totalMemberMileage;
                    $this->totalSumMemberDcPrice = 0;
                    $this->totalMemberDcPrice = 0;
                    $this->totalMemberOverlapDcPrice = 0;
                    $this->totalMemberMileage = 0;
                }

                if ($couponConfig['chooseCouponMemberUseType'] == 'member') {
                    $this->changePrice = false;
                }
            }

            $this->totalSettlePrice -= ($this->totalCouponOrderDcPrice + $this->totalCouponDeliveryDcPrice);
            $this->totalDcPrice += ($this->totalCouponOrderDcPrice + $this->totalCouponDeliveryDcPrice);
            $this->totalMileage += $this->totalCouponOrderMileage;
        }

        // 배송비 혜택이 무료이고 스킨패치가 적용되있을 경우 해당 주문에 적용된 배송비 0원처리
        if (empty($this->deliveryFree) === false && $this->_memInfo['deliveryFree'] == 'y') {
            $this->totalDeliveryFreeCharge = $this->totalDeliveryCharge - array_sum($this->totalGoodsDeliveryAreaPrice);
            $this->totalSettlePrice -= $this->totalDeliveryFreeCharge;
            $this->totalDcPrice += $this->totalDeliveryFreeCharge;
            $orderPrice['totalMemberDeliveryDcPrice'] = $this->totalDeliveryFreeCharge;
        }

        // 예치금 사용 여부에 따른 금액 설정
        $useDeposit = $this->getUserOrderDeposit(gd_isset($requestData['useDeposit'], 0));
        $orderPrice['useDeposit'] = $useDeposit['useDeposit'];

        // 예치금 설정 및 총 결제금액 반영
        if ($this->totalSettlePrice < 0) {
            // 사용 예치금 체크 (총결제금액이 -인경우 사용 예치금에서 제외)
            $this->totalUseDeposit = $this->totalUseDeposit + $this->totalSettlePrice;
            $this->totalSettlePrice = 0;
        } else {
            $this->totalUseDeposit = $orderPrice['useDeposit'];
            $this->totalSettlePrice -= $orderPrice['useDeposit'];
        }
        $this->totalDcPrice += $this->totalUseDeposit;

        // 마일리지 사용 여부에 따른 금액 설정
        $useMileage = $this->getUseOrderMileage(gd_isset($requestData['useMileage'], 0));
        $orderPrice['useMileage'] = $useMileage['useMileage'];

        // 마일리지 설정 및 총 결제금액 반영
        if ($this->totalSettlePrice < 0) {
            // 사용 마일리지 체크 (총결제금액이 -인경우 사용 마일리지에서 제외)
            $this->totalUseMileage = $this->totalUseMileage + $this->totalSettlePrice;
            $this->totalSettlePrice = 0;
        } else {
            $this->totalUseMileage = $orderPrice['useMileage'];
            $this->totalSettlePrice -= $orderPrice['useMileage'];
        }
        $this->totalDcPrice += $this->totalUseMileage;

        // 실 상품금액 = 상품금액 + 쿠폰사용금액 (순수 상품 합계금액)
        $orderPrice['totalGoodsPrice'] = $this->totalGoodsPrice;

        // 쿠폰 계산을 위한 실제 할인이 되기전에 적용된 상품판매가
        $orderPrice['totalSumGoodsPrice'] = $this->totalPrice;

        // 배송비 (전체 = 정책배송비 + 지역별배송비)
        $orderPrice['totalDeliveryCharge'] = $this->totalDeliveryCharge;
        $orderPrice['totalGoodsDeliveryPolicyCharge'] = $this->totalGoodsDeliveryPolicyCharge;
        $orderPrice['totalScmGoodsDeliveryCharge'] = $this->totalScmGoodsDeliveryCharge;
        $orderPrice['totalGoodsDeliveryAreaCharge'] = $this->totalGoodsDeliveryAreaPrice;
        if ((\Component\Order\OrderMultiShipping::isUseMultiShipping() === true && \Globals::get('gGlobal.isFront') === false && $requestData['multiShippingFl'] == 'y') || $requestData['isAdminMultiShippingFl'] === 'y') {
            $orderPrice['totalGoodsMultiDeliveryAreaPrice'] = $this->totalGoodsMultiDeliveryAreaPrice;
            $orderPrice['totalGoodsMultiDeliveryPolicyCharge'] = $this->totalGoodsMultiDeliveryPolicyCharge;
            $orderPrice['totalScmGoodsMultiDeliveryCharge'] = $this->totalScmGoodsMultiDeliveryCharge;
        }

        // 해외배송 보험료
        $orderPrice['totalDeliveryInsuranceFee'] = 0;
        if ($this->overseasDeliveryPolicy['data']['standardFl'] === 'ems' && $this->overseasDeliveryPolicy['data']['insuranceFl'] === 'y') {
            $orderPrice['totalDeliveryInsuranceFee'] = $this->setDeliveryInsuranceFee($this->totalGoodsPrice);
            $this->totalSettlePrice += $orderPrice['totalDeliveryInsuranceFee'];
        }

        // 해외배송 총 무게
        $orderPrice['totalDeliveryWeight'] = $this->totalDeliveryWeight;

        // 배송비 착불 금액 넘겨 받기 (collectPrice|wholefreeprice)
        foreach ($this->setDeliveryInfo as $dKey => $dVal) {
            $orderPrice['totalDeliveryCollectPrice'][$dKey] = $dVal['goodsDeliveryCollectPrice'];
            $orderPrice['totalDeliveryWholeFreePrice'][$dKey] = $dVal['goodsDeliveryWholeFreePrice'];
        }

        // 총 상품 할인 금액
        $orderPrice['totalGoodsDcPrice'] = $this->totalGoodsDcPrice;

        // 총 회원 할인 금액
        $orderPrice['totalSumMemberDcPrice'] = $this->totalSumMemberDcPrice;
        $orderPrice['totalMemberDcPrice'] = $this->totalMemberDcPrice;//총 회원할인 금액
        $orderPrice['totalMemberOverlapDcPrice'] = $this->totalMemberOverlapDcPrice;//총 그룹별 회원 중복할인 금액

        // 쿠폰할인액 = 상품쿠폰 + 주문쿠폰 + 배송비쿠폰
        $orderPrice['totalCouponDcPrice'] = ($this->totalCouponGoodsDcPrice + $this->totalCouponOrderDcPrice + $this->totalCouponDeliveryDcPrice);
        $orderPrice['totalCouponGoodsDcPrice'] = $this->totalCouponGoodsDcPrice;
        $orderPrice['totalCouponOrderDcPrice'] = $this->totalCouponOrderDcPrice;
        $orderPrice['totalCouponDeliveryDcPrice'] = $this->totalCouponDeliveryDcPrice;

        // 마이앱 사용에 따른 분기 처리
        if ($this->useMyapp) {
            // 총 마이앱 할인 금액
            $orderPrice['totalMyappDcPrice'] = $this->totalMyappDcPrice;
        }

        // 주문할인금액 안분을 위한 순수상품금액 = 상품금액(옵션/텍스트옵션가 포함) + 추가상품금액 - 상품할인 - 회원할인 - 상품쿠폰할인 - 마이앱할인
        $orderPrice['settleTotalGoodsPrice'] = $this->totalGoodsPrice - $this->totalGoodsDcPrice - $this->totalSumMemberDcPrice - $this->totalCouponGoodsDcPrice;

        // 마이앱 사용에 따른 분기 처리
        if ($this->useMyapp) {
            $orderPrice['settleTotalGoodsPrice'] -= $this->totalMyappDcPrice;
        }

        // 배송비할인금액 안분을 위한 순수배송비금액 = 정책배송비 + 지역배송비 - 배송비 할인쿠폰 - 회원 배송비 무료
        $orderPrice['settleTotalDeliveryCharge'] = $this->totalDeliveryCharge - $this->totalCouponDeliveryDcPrice - $this->totalDeliveryFreeCharge;

        // 주문할인금액 안분을 위한 순수상품금액 + 순수배송비 - 주문 할인쿠폰
        $orderPrice['settleTotalGoodsPriceWithDelivery'] = $orderPrice['settleTotalGoodsPrice'] + $orderPrice['settleTotalDeliveryCharge'] - (int)$this->totalCouponOrderDcPrice;

        // 마일리지를 사용한 경우 지급 예외 처리
        if ($this->mileageGiveExclude == 'n' && $this->totalUseMileage > 0) {
            $orderPrice['totalGoodsMileage'] = 0;// 총 상품 적립 마일리지
            $orderPrice['totalMemberMileage'] = 0;// 총 회원 적립 마일리지
            $orderPrice['totalCouponGoodsMileage'] = 0;// 총 상품쿠폰 적립 마일리지
            $orderPrice['totalCouponOrderMileage'] = 0;// 총 주문쿠폰 적립 마일리지
            $orderPrice['totalMileage'] = 0;
        } else {
            $orderPrice['totalGoodsMileage'] = $this->totalGoodsMileage;// 총 상품 적립 마일리지
            $orderPrice['totalMemberMileage'] = $this->totalMemberMileage;// 총 회원 적립 마일리지
            $orderPrice['totalCouponGoodsMileage'] = $this->totalCouponGoodsMileage;// 총 상품쿠폰 적립 마일리지
            $orderPrice['totalCouponOrderMileage'] = $this->totalCouponOrderMileage;// 총 주문쿠폰 적립 마일리지
            $orderPrice['totalMileage'] = $this->totalMileage;// 총 적립 마일리지 = 총 상품 적립 마일리지 + 총 회원 적립 마일리지 + 총 쿠폰 적립 마일리지
        }

        // 총 주문할인 + 상품 할인 금액
        $orderPrice['totalDcPrice'] = $this->totalDcPrice;

        // 총 주문할인 금액 (복합과세용 금액 산출을 위해 배송비는 제외시킴)
        $orderPrice['totalOrderDcPrice'] = $this->totalCouponOrderDcPrice + $this->totalUseMileage + $this->totalUseDeposit;

        // 마일리지 지급예외 정책 저장
        $orderPrice['mileageGiveExclude'] = $this->mileageGiveExclude;

        // 마일리지/예치금/쿠폰 사용에 따른 실결제 금액 반영
        $orderPrice['settlePrice'] = $this->totalSettlePrice;

        // 해외PG를 위한 승인금액 저장
        $orderPrice['overseasSettlePrice'] = NumberUtils::globalMoneyConvert($orderPrice['settlePrice'], $requestData['overseasSettleCurrency']);
        $orderPrice['overseasSettleCurrency'] = $requestData['overseasSettleCurrency'];

        // 주문하기에서 요청된 실 결제금액
        $requestSettlePrice = str_replace(',', '', $requestData['settlePrice']);

        // 실결제금액 마이너스인 경우
        if ($requestSettlePrice < 0 || $this->totalSettlePrice < 0) {
            throw new Exception(__('결제하실 금액을 다시 확인해주세요. 결제금액은 (-)음수가 될 수 없습니다.'));
        }


        // 배송비 산출을 위한 로직을 타는 경우 제외 처리
        if ($requestData['mode'] != 'check_area_delivery' && $requestData['mode'] != 'check_country_delivery') {
            // 넘어온 결제금액과 다를 경우 예외 처리
            if (gd_money_format($orderPrice['settlePrice'], false) != gd_money_format($requestSettlePrice, false) || $orderPrice['settlePrice'] < 0) {
                throw new Exception(__("결제 할 금액이 일치하지 않습니다.\n할인/적립 금액이 변경되었을 수 있습니다.\n새로고침 후 다시 시도해 주세요."));
            }

            // 해외PG 결제인 경우 금액 비교 체크
            if ($requestData['overseasSettlePrice'] > 0 && empty($requestData['overseasSettleCurrency']) === false) {
                if ($orderPrice['overseasSettlePrice'] != NumberUtils::commaRemover($requestData['overseasSettlePrice'])) {
                    throw new Exception(__('해외PG 승인금액이 일치하지 않습니다.'));
                }
            }
        }

        return $orderPrice;
    }

    /**
     * '마일리지 정책에 따른 주문시 사용가능한 범위 제한' 에 사용되는 기준금액 셋팅
     *
     * @author bumyul2000@godo.co.kr
     *
     * @param array $postData
     *
     * @return array $mileagePrice
     */
    public function setMileageUseLimitPrice($postData=[])
    {
        $mileagePrice = ($postData['totalPrice']) ? $postData['totalPrice'] : $this->totalPrice;
        // 상품 할인 금액
        $mileagePrice['goodsDcPrice'] = ($postData['totalGoodsDcPrice']) ? $postData['totalGoodsDcPrice'] : $this->totalGoodsDcPrice;
        // 회원 할인 금액
        $mileagePrice['memberDcPrice'] = ($postData['totalMemberDcPrice']) ? $postData['totalMemberDcPrice'] : $this->totalMemberDcPrice;
        $mileagePrice['memberOverlapDcPrice'] = ($postData['totalMemberOverlapDcPrice']) ? $postData['totalMemberOverlapDcPrice'] : $this->totalMemberOverlapDcPrice;
        // 쿠폰 할인 금액
        if($postData['totalCouponGoodsDcPrice']){
            $mileagePrice['couponDcPrice'] += $postData['totalCouponGoodsDcPrice'];
        }
        else {
            $mileagePrice['couponDcPrice'] += gd_isset($this->totalCouponGoodsDcPrice, 0);
        }
        if($postData['totalCouponOrderDcPrice']){
            $mileagePrice['couponDcPrice'] += $postData['totalCouponOrderDcPrice'];
        }
        else {
            $mileagePrice['couponDcPrice'] += gd_isset($this->totalCouponOrderDcPrice, 0);
        }

        // 마이앱 사용에 따른 분기 처리
        if ($this->useMyapp) {
            $mileagePrice['myappDcPrice'] = ($postData['totalMyappDcPrice']) ? $postData['totalMyappDcPrice'] : $this->totalMyappDcPrice;
        }
        // 총 배송비
        $mileagePrice['deliveryCharge'] = ($postData['totalDeliveryCharge']) ? $postData['totalDeliveryCharge'] : $this->totalDeliveryCharge;
        // 지역별 배송 금액
        $mileagePrice['deliveryAreaCharge'] = ($postData['totalGoodsDeliveryAreaPrice']) ? $postData['totalGoodsDeliveryAreaPrice'] : array_sum($this->totalGoodsDeliveryAreaPrice);

        return $mileagePrice;
    }

    /**
     * 마일리지 정책에 따른 주문시 사용가능한 범위 제한
     *
     * @author artherot
     *
     * @param integer $memberMileage 회원보유 마일리지
     * @param array $arrGoodsPrice 주문 상품 금액 배열 (상품 판매가격, 옵션 가격, 텍스트 옵션 가격, 추가 상품 가격, 상품할인, 회원할인(추가/중복), 쿠폰할인, 배송비)
     *
     * @return array   마일리지 사용 정보
     */
    public function getMileageUseLimit($memberMileage, $arrGoodsPrice)
    {
        if ($this->deliveryFreeByMileage == 'y') {
            $arrGoodsPrice['deliveryCharge'] = gd_isset($arrGoodsPrice['deliveryAreaCharge'], 0);
        }

        // 마일리지 기본 정보
        $tmpBasic = Globals::get('gSite.member.mileageBasic');
        // 마일리지 사용 정보
        $tmpUse = Globals::get('gSite.member.mileageUse');
        // 마일리지 절사 정보
        $truncMileage = Globals::get('gTrunc.mileage');
        // --- 1. 초기 셋팅

        /*
         * 마일리지 계산을 위한 기준 금액 처리 [기본설정의 사용/적립시 구매금액 기준]
         * '최소 상품구매금액 제한', '최대 사용금액 제한 (%)' 계산시 사용
         *
         * $totalGoodsPrice 최대 사용금액 제한 (%) 의 기준 금액
         * $orderAbleStandardPrice 최소 상품구매금액 제한 의 기준 금액
         */

        $totalGoodsPrice = $arrGoodsPrice['goodsPrice'];
        if ($tmpBasic['optionPrice'] === '1') {
            $totalGoodsPrice = $totalGoodsPrice + $arrGoodsPrice['optionPrice'];
        }
        if ($tmpBasic['addGoodsPrice'] === '1') {
            $totalGoodsPrice = $totalGoodsPrice + $arrGoodsPrice['addGoodsPrice'];
        }
        if ($tmpBasic['textOptionPrice'] === '1') {
            $totalGoodsPrice = $totalGoodsPrice + $arrGoodsPrice['optionTextPrice'];
        }

        $orderAbleStandardPrice = $totalGoodsPrice;
        if ($tmpBasic['goodsDcPrice'] === '1') {
            $totalGoodsPrice = $totalGoodsPrice - $arrGoodsPrice['goodsDcPrice'];

            // 마이앱 사용에 따른 분기 처리
            if ($this->useMyapp) {
                $totalGoodsPrice -= $arrGoodsPrice['myappDcPrice'];
            }

            if ($tmpUse['standardPrice'] == 'salesPrice') {
                //최소 상품구매금액 제한 - 할인금액 포함 가격 기준일시
                $orderAbleStandardPrice = $orderAbleStandardPrice - $arrGoodsPrice['goodsDcPrice'];

                if ($this->useMyapp) { // 마이앱 사용에 따른 분기 처리
                    $orderAbleStandardPrice -= $arrGoodsPrice['myappDcPrice'];
                }
            }
        }
        if ($tmpBasic['memberDcPrice'] === '1') {
            $totalGoodsPrice = $totalGoodsPrice - $arrGoodsPrice['memberDcPrice'];
            if ($tmpUse['standardPrice'] == 'salesPrice') {
                //최소 상품구매금액 제한 - 할인금액 포함 가격 기준일시
                $orderAbleStandardPrice = $orderAbleStandardPrice - $arrGoodsPrice['memberDcPrice'];
            }
        }
        if ($tmpBasic['couponDcPrice'] === '1') {
            $totalGoodsPrice = $totalGoodsPrice - $arrGoodsPrice['couponDcPrice'];
            if ($tmpUse['standardPrice'] == 'salesPrice') {
                //최소 상품구매금액 제한 - 할인금액 포함 가격 기준일시
                $orderAbleStandardPrice = $orderAbleStandardPrice - $arrGoodsPrice['couponDcPrice'];
            }
        }

        $data = [
            'payUsableFl'               => $tmpBasic['payUsableFl'],            // 마일리지 기본정보 - 사용설정 (사용함 - y, 사용안함 - n)
            'minimumHold'               => $tmpUse['minimumHold'],              // 마일리지 사용설정 - 최소 보유마일리지 제한 (? 이상 적립된 경우 결제시 사용 가능)
            'orderAbleLimit'            => $tmpUse['orderAbleLimit'],           // 마일리지 사용설정 - 최소 상품구매금액 제한 (구매금액 합계가 ? 이상인 경우 결제시 사용 가능)
            'standardPrice'             => $tmpUse['standardPrice'],            // 마일리지 사용설정 - 최소 상품구매금액 제한 (할인금액 미포함 goodsPrice, 할인금액 포함 salesPrice)
            'minimumLimit'              => $tmpUse['minimumLimit'],             // 마일리지 사용설정 - 최소 사용마일리지 제한 (1회 결제 시 최소 ? 이상 사용 가능)
            'usableFl'                  => 'y',                                 // 계산 후 실제 마일리지를 사용 할 수 있는지에 대한 사용 여부
            'orderAbleStandardPrice'    => $orderAbleStandardPrice,             // '최소 상품구매금액 제한' 을 비교하기 위한 계산된 구매금액
            'useDeliveryFl'             =>  $tmpUse['maximumLimitDeliveryFl'],  // 마일리지 사용설정 - 최대 사용금액 제한 배송비 포함 여부
            'mileageGoodsPrice'         => $totalGoodsPrice,                    // 마일리지 계산을 위한 기준 금액 - 레거시 보존
        ];

        // --- 2. 보유 마일리지에 대한 제한 조건

        // 마일리지 기본정보 - 사용설정 (사용함 - y, 사용안함 - n)
        if($tmpBasic['payUsableFl'] === 'n'){
            $data['usableFl'] = 'n';
        }

        // 회원 보유 마일리지 체크
        if((int)$memberMileage < 1){
            //회원 보유 마일리지가 0원일경우
            $data['usableFl'] = 'n';
        }

        // 마일리지 사용설정 - 최소 보유마일리지 제한 (? 이상 적립된 경우 결제시 사용 가능)
        if((int)$tmpUse['minimumHold'] > 0){
            if((int)$memberMileage < (int)$tmpUse['minimumHold']){
                $data['usableFl'] = 'n';
            }
        }

        // 마일리지 사용설정 - 최소 사용마일리지 제한 (1회 결제 시 최소 ? 이상 사용 가능)
        if($tmpUse['minimumLimit']){
            if((int)$memberMileage < (int)$tmpUse['minimumLimit']){
                $data['usableFl'] = 'n';
            }
        }

        // --- 3. 상품 구매 금액에 대한 제한조건

        // 마일리지 사용설정 - 최대 사용금액 제한 (1회 결제 시 최대 ? 까지 사용 가능)
        if ((int)$tmpUse['maximumLimit'] > 0) {
            if ($tmpUse['maximumLimitUnit'] == 'percent') {
                // % 일시

                // 배송비 포함 여부
                if ($tmpUse['maximumLimitDeliveryFl'] === 'y') {
                    $maximumLimit = ($totalGoodsPrice + $arrGoodsPrice['deliveryCharge']) * ($tmpUse['maximumLimit'] / 100);
                }
                else {
                    $maximumLimit = $totalGoodsPrice * ($tmpUse['maximumLimit'] / 100);
                }
            }
            else {
                // 원 일시
                $maximumLimit = $tmpUse['maximumLimit'];
            }
            $maximumLimit  = gd_number_figure($maximumLimit, $truncMileage['unitPrecision'], $truncMileage['unitRound']);

            $data['oriMaximumLimit'] = $maximumLimit;
        }
        else {
            $data['oriMaximumLimit'] = 0;

            // 최대 사용금액 제한 없음 (회원 보유 마일리지로 설정)
            $maximumLimit = $memberMileage;
        }

        // maximumLimit 와 회원 보유 마일리지 중 작은 금액이 실제 최대 사용가능한 마일리지
        $data['maximumLimit'] = min($maximumLimit, $memberMileage);

        // 마일리지 사용설정 - 최소 상품구매금액 제한 (구매금액 합계가 ? 이상인 경우 결제시 사용 가능) : 구매금액이 최소 상품구매금액 제한 보다 적을 경우
        if((int)$tmpUse['orderAbleLimit'] > 0){
            if((int)$orderAbleStandardPrice < (int)$tmpUse['orderAbleLimit']){
                $data['usableFl'] = 'n';
            }
        }

        // '최소 사용마일리지 제한' 과 최대 사용가능마일리지와 체크
        if((int)$tmpUse['minimumLimit'] > $maximumLimit){
            $data['usableFl'] = 'n';
        }

        return $data;
    }

    /**
     * 마일리지 사용 체크 및 사용가능한 마일리지 반환
     *
     * @author artherot
     *
     * @param array $totalGoodsPrice 주문 상품금액 총합 (상품 판매가격, 옵션 가격, 텍스트 옵션 가격, 추가 상품 가격)
     * @param integer $useMileage 사용한 마일리지
     *
     * @return array   마일리지 사용 정보
     */
    public function getUseOrderMileage($useMileage = 0)
    {
        // 회원보유마일리지
        $member = \App::load(\Component\Member\Member::class);
        if($this->isWrite === true){
            $memInfo = $member->getMemberInfo($this->members['memNo']);
        }
        else {
            $memInfo = $member->getMemberInfo();
        }
        $memberMileage = gd_isset($memInfo['mileage'], 0);

        // 마일리지 정책
        // '마일리지 정책에 따른 주문시 사용가능한 범위 제한' 에 사용되는 기준금액 셋팅
        $mileagePrice = $this->setMileageUseLimitPrice();
        // 마일리지 정책에 따른 주문시 사용가능한 범위 제한
        $mileageUse = $this->getMileageUseLimit($memberMileage, $mileagePrice);

        // 마일리지 체크
        if ($mileageUse['payUsableFl'] == 'y' && $useMileage > 0) {
            // 사용한 마일리지과 회원 보유 마일리지 체크
            if ($useMileage > $memberMileage) {
                $useMileage = $memberMileage;
            }

            // 최소 사용 마일리지 보다 작은 경우 0
            if ($mileageUse['minimumLimit'] > $useMileage) {
                $useMileage = 0;
            }

//            // 최소 제한 마일리지 보다 크면서 구매가능 제한이 있는 경우
//            if ($mileageUse['minimumHold'] < $useMileage && $mileageUse['orderAbleLimit'] > 0) {
//                $useMileage = $mileageUse['orderAbleLimit'];
//            }

            // 최대 제한 마일리지 보다 큰 경우
            if ($mileageUse['maximumLimit'] < $useMileage) {
                $useMileage = $mileageUse['maximumLimit'];
            }
        } else {
            $useMileage = 0;
        }

        $data['useFl'] = $mileageUse['payUsableFl'];
        $data['useMileage'] = $useMileage;

        return $data;
    }

    /**
     * getUserOrderDeposit
     *
     * @param $useDeposit
     *
     * @return int|string
     */
    public function getUserOrderDeposit($useDeposit)
    {
        // 데이터 초기화
        $data['useFl'] = 'n';
        $data['useDeposit'] = 0;

        // 예치금 사용 정보
        $depositConfig = Globals::get('gSite.member.depositConfig');
        if ($depositConfig['payUsableFl'] == 'n') {
            return $data;
        }

        // 회원보유 예치금
        $member = \App::load(\Component\Member\Member::class);
        if($this->isWrite === true){
            $memInfo = $member->getMemberInfo($this->members['memNo']);
        }
        else {
            $memInfo = $member->getMemberInfo();
        }
        $memberDeposit = gd_isset($memInfo['deposit'], 0);

        // 예치금 체크
        if ($depositConfig['payUsableFl'] == 'y' && $useDeposit > 0) {
            // 사용한 마일리지과 회원 보유 마일리지 체크
            if ($useDeposit > $memberDeposit) {
                $useDeposit = $memberDeposit;
            }

            $data['useFl'] = $depositConfig['payUsableFl'];
            $data['useDeposit'] = $useDeposit;
        }

        return $data;
    }

    /**
     * getOrderGoodsWithOtherUser
     *
     * @param null $cartGoods
     *
     * @return mixed
     * @author Jong-tae Ahn <qnibus@godo.co.kr>
     * @deprecated
     */
    public function getOrderGoodsWithOtherUser($cartGoods = null)
    {
        $goods = \App::load(\Component\Goods\Goods::class);
        $goodsList = [
            1000000001,
            1000000002,
            1000000004,
            1000000021,
            1000000009,
            1000000012,
            1000000010,
        ];
        $goodsData = $goods->goodsDataDisplay('goods', implode(INT_DIVISION, $goodsList), 12, 'sort asc', 'list', false, true, false, false, 166);

        return $goodsData;
    }


    /**
     * 상품 상세 혜택 계산
     * goodsViewBenefit
     *
     * @param $getData
     *
     * @return array
     */
    public function goodsViewBenefit($getData)
    {

        gd_isset($getData['goodsMileageExcept'], 'n');
        gd_isset($getData['couponBenefitExcept'], 'n');
        gd_isset($getData['memberBenefitExcept'], 'n');
        $memberDcFl = true;

        // 회원 추가 할인과 중복 할인을 위한 카테고리 코드 정보
        $arrCateCd = $this->getMemberDcForCateCd();

        $getData['goodsNo'] = $getData['goodsNo'][0];
        $getData = $this->getMemberDcFlInfo($getData);
        if (!$getData['goodsPriceSum']) {
            $getData['goodsPriceSum'][] = $getData['set_goods_price'];
        }

        //회원등급별 상품할인 기능 추가 (2017-08-18)
        $goods = new Goods();
        $goodsData = $goods->getGoodsInfo($getData['goodsNo']);

        //상품 혜택 모듈
        $goodsBenefit = \App::load('\\Component\\Goods\\GoodsBenefit');
        //상품혜택 사용시 해당 변수 재설정
        $goodsData = $goodsBenefit->goodsDataFrontConvert($goodsData);

        $exceptBenefit = explode(STR_DIVISION, $goodsData['exceptBenefit']);
        $exceptBenefitGroupInfo = explode(INT_DIVISION, $goodsData['exceptBenefitGroupInfo']);

        // 제외 혜택 대상 여부
        $exceptBenefitFl = false;
        if ($goodsData['exceptBenefitGroup'] == 'all' || ($goodsData['exceptBenefitGroup'] == 'group' && in_array($this->_memInfo['groupSno'], $exceptBenefitGroupInfo) === true)) {
            $exceptBenefitFl = true;
        }

        $data['goodsDcPrice'] = 0;
        $data['memberDcPrice'] = 0;
        $data['couponDcPrice'] = 0;

        // 마이앱 사용에 따른 분기 처리
        if ($this->useMyapp) {
            $data['myappDcPrice'] = 0;
        }

        $data['goodsMileage'] = 0;
        $data['memberMileage'] = 0;
        $data['couponMileage'] = 0;

        $data['totalDcPrice'] = 0;
        $data['totalMileage'] = 0;

        // 마이앱 모듈 호출
        if ($this->useMyapp) {
            $myapp = \App::load('Component\\Myapp\\Myapp');
        }

        foreach ($getData['goodsCnt'] as $k => $v) {
            $price['goodsCnt'] = $getData['goodsCnt'][$k];
            $price['goodsPriceSum'] = $getData['goodsPriceSum'][$k];
            $price['optionPriceSum'] = $getData['optionPriceSum'][$k];
            $price['optionTextPriceSum'] = $getData['optionTextPriceSum'][$k];
            $price['addGoodsPriceSum'] = $getData['addGoodsPriceSum'][$k];
            $price['couponDcPrice'] = 0;

            // 상품단가 계산 (상품, 옵션, 텍스트옵션, 추가상품)
            $unitPrice['goodsCnt'][$k] = $getData['goodsCnt'][$k];
            $unitPrice['goodsPrice'][$k] = $getData['goodsPriceSum'][$k] / $getData['goodsCnt'][$k];
            $unitPrice['optionPrice'][$k] = $getData['optionPriceSum'][$k] / $getData['goodsCnt'][$k];
            $unitPrice['optionTextPrice'][$k] = $getData['optionTextPriceSum'][$k] / $getData['goodsCnt'][$k];
            $unitPrice['addGoodsCnt'][$k] = $getData['addGoodsCnt'][$k];

            // 추가 상품
            if (empty($getData['addGoodsNo'][$k]) === false) {
                foreach ($getData['addGoodsNo'][$k] as $key => $v2) {
                    // 추가 상품 디비 정보
                    $arrAddGoodsNo[] = $v2;
                    $getAddGoods = $this->getAddGoodsInfo($arrAddGoodsNo);
                    $getData['addGoodsBrandCd'][$k][$key] = $getAddGoods[$v2]['brandCd'];
                }

                //회원등급 > 브랜드별 추가할인 추가 상품 브랜드 할인율 정보
                if ($this->_memInfo['fixedOrderTypeDc'] == 'brand') {
                    // 추가 상품 브랜드
                    foreach ($getData['addGoodsBrandCd'][0] as $addGoodsKey => $addGoodsBrandCd) {
                        if (in_array($addGoodsBrandCd, $this->_memInfo['dcBrandInfo']->cateCd)) {
                            $goodsBrandInfo[$arrAddGoodsNo[$addGoodsKey]][$addGoodsBrandCd] = $addGoodsBrandCd;
                        } else {
                            if ($addGoodsBrandCd) {
                                $goodsBrandInfo[$arrAddGoodsNo[$addGoodsKey]]['allBrand'] = $addGoodsBrandCd;
                            } else {
                                $goodsBrandInfo[$arrAddGoodsNo[$addGoodsKey]]['noBrand'] = $addGoodsBrandCd;
                            }
                        }

                        foreach ($goodsBrandInfo[$arrAddGoodsNo[$addGoodsKey]] as $gKey => $gVal) {
                            foreach ($this->_memInfo['dcBrandInfo']->cateCd AS $mKey => $mVal) {
                                if ($gKey == $mVal) {
                                    $unitPrice['brandDiscount'][$k][$addGoodsKey] = $this->_memInfo['dcBrandInfo']->goodsDiscount[$mKey];
                                }
                            }
                        }
                    }
                }
            }

            if (empty($getData['addGoodsCnt'][$k]) === false) {
                foreach ($getData['addGoodsCnt'][$k] as $key => $value) {
                    $unitPrice['addGoodsPrice'][$k][$key] = $getData['add_goods_total_price'][$k][$key] / $value;
                }
            }
            $unitPrice['priceInfo'][$k] = $price;

            $tmpGoodsPrice = $price['goodsPriceSum']+$price['optionPriceSum']+ $price['optionTextPriceSum'] +$price['addGoodsPriceSum'];

            //상품할인가
            if($tmpGoodsPrice > 0 ) {
                $goodsDcPrice = $this->getGoodsDcData($getData['goodsDiscountFl'], $getData['goodsDiscount'], $getData['goodsDiscountUnit'], $v, $price, $goodsData['fixedGoodsDiscount'], $goodsData['goodsDiscountGroup'], $goodsData['goodsDiscountGroupMemberInfo']);
                $data['goodsDcPrice'] += $goodsDcPrice;
                $price['goodsDcPrice'] = $goodsDcPrice;
                if ($goodsDcPrice > 0) {
                    $unitPrice['goodsDcPrice'][$k] = $goodsDcPrice / $v;
                }
            }

            // 마이앱 상품 추가 할인
            // 상품상세와 장바구니에서는 추가구매 할인금액을 분리해서 보여주지 않기때문에 한번에 계산
            if ($this->useMyapp) {
                $myappBenefitParams['goodsPrice'] = $price['goodsPriceSum'] / $getData['goodsCnt'][$k];
                $myappBenefitParams['optionPrice'] = $price['optionPriceSum'] / $getData['goodsCnt'][$k];
                $myappBenefitParams['optionTextPrice'] = $price['optionTextPriceSum'] / $getData['goodsCnt'][$k];
                $myappBenefitParams['goodsCnt'] = $getData['goodsCnt'][$k];
                $myappBenefitParams['addGoodsPrice'] = $price['addGoodsPriceSum'];
                $myappBenefit = $myapp->getOrderAdditionalBenefit($myappBenefitParams);
                if (empty($myappBenefit['discount']['goods']) === false && $myappBenefit['discount']['goods'] > 0) {
                    $data['myappDcPrice'] += $myappBenefit['discount']['goods'];
                    $unitPrice['myappDcPrice'][$k] = $data['myappDcPrice'];
                }
            }

            if ($getData['couponBenefitExcept'] == 'n') {
                //쿠폰 적용 할인 / 적립 금액
                if ($getData['couponApplyNo'][$k] > 0) {
                    $tmpCouponPrice = $this->getMemberCouponPriceData($price, $getData['couponApplyNo'][$k]);
                    if (is_array($tmpCouponPrice['memberCouponAlertMsg']) && (array_search('LIMIT_MIN_PRICE', $tmpCouponPrice['memberCouponAlertMsg']) === true)) {
                        // 'LIMIT_MIN_PRICE' 일때 구매금액 제한에 걸려 사용 못하는 쿠폰 처리
                        // 수량 변경 시 구매금액 제한에 걸림
                        // 적용된 쿠폰 모두 제거
                        if ($getData['displayOptionkey']) {
                            $data['couponAlertKey'][] = $getData['displayOptionkey'][$k];
                        } else {
                            $data['couponAlertKey'][] = $k;
                        }
                    } else {
                    }
                    if (is_array($tmpCouponPrice['memberCouponSalePrice'])) {
                        $goodsOptCouponSalePriceSum = array_sum($tmpCouponPrice['memberCouponSalePrice']);
                    }
                    if (is_array($tmpCouponPrice['memberCouponAddMileage'])) {
                        $goodsOptCouponAddMileageSum = array_sum($tmpCouponPrice['memberCouponAddMileage']);
                    }

                    //쿠폰 할인
                    $data['couponDcPrice'] += $goodsOptCouponSalePriceSum;
                    $unitPrice['couponDcPrice'][$k] = $price['couponDcPrice'] = $goodsOptCouponSalePriceSum;

                    //쿠폰 마일리지
                    $data['couponMileage'] += $goodsOptCouponAddMileageSum;
                    unset($tmpCouponPrice);
                    unset($goodsOptCouponSalePriceSum);
                    unset($goodsOptCouponAddMileageSum);
                }

                // 쿠폰을 사용했고 사용설정에 쿠폰만 사용설정일때 처리
                if ($data['couponDcPrice'] > 0 || $data['couponMileage'] > 0) {
                    $couponConfig = gd_policy('coupon.config');
                    if ($couponConfig['couponUseType'] == 'y' && $couponConfig['chooseCouponMemberUseType'] == 'coupon') {
                        $memberDcFl = false;
                        $data['memberDcPrice'] = 0;
                        $data['memberMileage'] = 0;
                    }
                }
            }
            //회원마일리지
            if ($getData['memberBenefitExcept'] == 'n' && $memberDcFl == true) {

                //회원 추가 상품 할인
                $tmp = $this->getMemberDcPriceData($getData['goodsNo'], $this->_memInfo, $price, $arrCateCd, $getData['addDcFl'], $getData['overlapDcFl']);

                // 회원 추가 할인혜택 적용 제외
                if (in_array('add', $exceptBenefit) === true && $exceptBenefitFl === true) {} else {
                    $data['memberDcPrice'] += $tmp['memberDcPrice'];
                    $data['tmpMemberDcPrice'] += $tmp['memberDcPrice'];
                }
                // 회원 중복 할인혜택 적용 제외
                if (in_array('overlap', $exceptBenefit) === true && $exceptBenefitFl === true) {} else {
                    $data['memberDcPrice'] += $tmp['memberOverlapDcPrice'];
                    $data['tmpMemberOverlapDcPrice'] += $tmp['memberOverlapDcPrice'];
                }
                // 회원 추가 마일리지 적립 적용 제외
                if (in_array('mileage', $exceptBenefit) === true && $exceptBenefitFl === true) {} else {
                    $data['memberMileage'] += $this->getMemberMileageData($this->_memInfo, $price);
                }
            }
            unset($tmpGoodsPrice);
        }

        // 추가할인 / 중복할인 /추가 마일리지 적립 재계산 (기준이 상품별일 경우)
        if ($getData['memberBenefitExcept'] == 'n' && $memberDcFl === true) {

            // 상품의 단가, 합계금액 계산
            $tmp = $this->getUnitGoodsPriceData($this->_memInfo, $unitPrice);
            $tmpPrice = $tmp['tmpPrice'];

            if (in_array($this->_memInfo['fixedOrderTypeDc'], ['goods', 'order', 'brand']) === true) {
                if (in_array('add', $exceptBenefit) === true && $exceptBenefitFl === true) {} else {
                    //회원등급 > 브랜드별 추가할인 상품 브랜드 정보
                    if ($this->_memInfo['fixedOrderTypeDc'] == 'brand') {
                        if (in_array($getData['brandCd'], $this->_memInfo['dcBrandInfo']->cateCd)) {
                            $goodsBrandInfo[$getData['goodsNo']][$getData['brandCd']] = $getData['brandCd'];
                        } else {
                            if ($getData['brandCd']) {
                                $goodsBrandInfo[$getData['goodsNo']]['allBrand'] = $getData['brandCd'];
                            } else {
                                $goodsBrandInfo[$getData['goodsNo']]['noBrand'] = $getData['brandCd'];
                            }
                        }
                    }
                    $addDcPrice = $this->getMemberGoodsAddDcPriceData($tmpPrice, [], $getData['goodsNo'], $this->_memInfo, $unitPrice, $arrCateCd, $getData['addDcFl'], [], $goodsBrandInfo);


                    if ($addDcPrice['addDcFl'] === true) {
                        $data['tmpMemberDcPrice'] = ($addDcPrice['memberDcPrice'] != 0) ? $addDcPrice['memberDcPrice'] : $data['memberDcPrice'];
                    }
                }
            }
            if (in_array($this->_memInfo['fixedOrderTypeOverlapDc'], ['goods', 'order']) === true) {
                if (in_array('overlap', $exceptBenefit) === true && $exceptBenefitFl === true) {} else {
                    $overlapDcPrice = $this->getMemberGoodsOverlapDcPriceData($tmpPrice, [], $getData['goodsNo'], $this->_memInfo, $unitPrice, $arrCateCd, $getData['overlapDcFl']);

                    if ($overlapDcPrice['overlapDcFl'] === true) {
                        $data['tmpMemberOverlapDcPrice'] = $overlapDcPrice['memberOverlapDcPrice'];
                    }
                }
            }
            $data['memberDcPrice'] = $data['tmpMemberDcPrice'] + $data['tmpMemberOverlapDcPrice'];

            if (in_array($this->_memInfo['fixedOrderTypeMileage'], ['goods', 'order']) === true) {
                if (in_array('mileage', $exceptBenefit) === true && $exceptBenefitFl === true) {} else {
                    $memberMileage = $this->getMemberGoodsMileageData($tmpPrice, [], $this->_memInfo);
                    $data['memberMileage'] = $memberMileage['memberMileage'];
                }
            }
        }

        //상품 마일리지
        if ($getData['goodsMileageExcept'] == 'n') {
            $goodsCnt = array_sum($getData['goodsCnt']);
            $price['goodsPriceSum'] = array_sum($getData['goodsPriceSum']);
            $price['optionPriceSum'] = array_sum($getData['optionPriceSum']);
            $price['optionTextPriceSum'] = array_sum($getData['optionTextPriceSum']);
            $price['addGoodsPriceSum'] = array_sum($getData['addGoodsPriceSum']);
            $price['goodsDcPrice'] = $data['goodsDcPrice'];
            $price['memberDcPrice'] = $data['memberDcPrice']; // 상품 상세는 회원 할인(추가/중복)이 같이 합산되어 있음.
            $price['couponDcPrice'] = $data['couponDcPrice'];

            // 마이앱 사용에 따른 분기 처리
            if ($this->useMyapp) {
                $price['myappDcPrice'] = $data['myappDcPrice'];
            }

            $data['goodsMileage'] += $this->getGoodsMileageData($getData['mileageFl'], $getData['mileageGoods'], $getData['mileageGoodsUnit'], $goodsCnt, $price, $goodsData['mileageGroup'], $goodsData['mileageGroupInfo'], $goodsData['mileageGroupMemberInfo']);
        }

        $data['totalDcPrice'] = $data['goodsDcPrice'] + $data['memberDcPrice'];

        // 마이앱 사용에 따른 분기 처리
        if ($this->useMyapp) {
            $data['totalDcPrice'] += $data['myappDcPrice'];
        }

        if ($data['couponDcPrice'] > 0 && $getData['set_total_price'] - $data['totalDcPrice'] < $data['couponDcPrice']) {
            $data['couponDcPrice'] = $getData['set_total_price'] - $data['totalDcPrice'];
        }

        $data['totalDcPrice'] += $data['couponDcPrice'];

        if($getData['set_total_price'] - $data['totalDcPrice'] < 0) {
            $data['totalDcPrice'] = $getData['set_total_price'];
        }
        $data['totalMileage'] = $data['goodsMileage'] + $data['memberMileage'] + $data['couponMileage'];

        $data['couponBenefitExcept'] = $getData['couponBenefitExcept'];

        return $data;
    }

    /**
     * 멀티상점의 국가코드 유무에 따른 PC/모바일로 접속 여부
     *
     * @param mixed $countryCode
     *
     * @return bool
     * @author Jong-tae Ahn <qnibus@godo.co.kr>
     */
    public function isGlobalFront($countryCode = null)
    {
        if($this->isAdminGlobal === true){
            $isGlobal = true;
        }
        else {
            $isGlobal = Globals::get('gGlobal.isFront');
        }
        return ($countryCode !== null && $isGlobal);
    }

    /**
     * 국가코드에 따른 해당 해외배송에 묶인 배송비조건 일련번호 추출
     * 해외배송조건 > EMS 설정시 배송비조건 매칭이 되지 않는다. 이에 설정된 배송비조건 중
     * 가장 최우선으로 되어 있는 배송비조건을 강제로 매칭시켜 오류가 발생되지 않도록 처리
     *
     * @param string $countryCode 국가 2자리 코드
     *
     * @return int
     * @throws Exception
     * @author Jong-tae Ahn <qnibus@godo.co.kr>
     */
    public function getDeliverySnoForOverseas($countryCode)
    {
        if (!isset($this->overseasDeliveryPolicy['group'][0]) && $this->overseasDeliveryPolicy['group'][0]['deliverySno'] < 1) {
            throw new Exception(__('해외배송비 조건 설정이 필요합니다. 관리자에게 문의하세요.'));
        }

        $deliverySno = 0;
        if ($this->overseasDeliveryPolicy['data']['standardFl'] !== 'ems') {
            foreach ($this->overseasDeliveryPolicy['group'] as $group) {
                // 아래 우선순위를 통해서 국가에 따른 배송비를 체크하는 부분이지만 자체배송인 경우 체크할 필요가 없어서 우선 주석 처리 함
                $checkCountry = false;
                foreach ($group['countries'] as $country) {
                    if ($country['code'] == $countryCode) {
                        $checkCountry = true;
                        break;
                    }
                }
                if ($checkCountry === true) {
                    $deliverySno = $group['deliverySno'];
                    break;
                }
            }

            // 배송비조건이 없는 경우 계산이 안되기때문에 없는 경우 강제 등록 처리
            if ($deliverySno == 0) {
                $deliverySno = $this->overseasDeliveryPolicy['group'][0]['deliverySno'];
            }
        }
        return $deliverySno;
    }

    /**
     * 해외배송 보험료 계산
     * 상품합산금액 기준으로 계산되어지며,
     * OverseasDelivery::$_emsInsuranceGuide에 설정된 기준을 가지고 계산되어진다.
     *
     * @param interger $goodsPrice 상품합산금액
     *
     * @return int
     * @author Jong-tae Ahn <qnibus@godo.co.kr>
     */
    public function setDeliveryInsuranceFee($goodsPrice)
    {
        // initialize
        $insuranceFee = 0;

        if ($this->overseasDeliveryPolicy['data']['standardFl'] === 'ems' && $this->overseasDeliveryPolicy['data']['insuranceFl'] === 'y') {
            $insuranceFee = $this->overseasDeliveryPolicy['data']['emsGuide']['baseRate'];
            $limit = $this->overseasDeliveryPolicy['data']['emsGuide']['baseRateLimit'];
            $interval = $this->overseasDeliveryPolicy['data']['emsGuide']['baseRateOverInterval'];
            if ($goodsPrice > $limit) {
                for ($i = $limit; $i < $goodsPrice; $i += $limit) {
                    $insuranceFee += $interval;
                }
            }
        }

        return $insuranceFee;
    }

    /**
     * 장바구니/주문서 접근시 최상위 로딩 getCartGoodsData 함수의 최상위에서 로그인 상태면 체크
     * 로그인상태면 memNo기준으로 장바구니 정보를 가져와서 현재 기준의 siteKey로 업데이트하고 장바구니에 동일상품을 합친다
     *
     * @param interger $memNo 회원고유번호
     *
     * @author Jae-Won Noh <jwno@godo.co.kr>
     */
    public function setMergeCart($memNo)
    {
        $session = \App::getInstance('session');
        if($session->get('related_goods_order') ==  'y'){
            return;
        }

        if (!$memNo) {
            $arrBind = [
                's',
                Session::get('siteKey'),
            ];

            // 바로 구매로 넘어온경우
            if (Request::getFileUri() == 'payco_checkout.php' || Request::getFileUri() == 'naver_pay.php') {
                $this->cartPolicy['directOrderFl'] = 'y';
            }

            $strDirectSQL = "";
            if (Request::getFileUri() != 'cart.php' && Request::getFileUri() != 'order_ps.php') {
                if ($this->cartPolicy['directOrderFl'] == 'y') {
                    $strDirectSQL = " AND directCart = 'n'";
                }
            } else {
                $strDirectSQL = " AND directCart = 'n'";
            }

            $strDirectSQL .= " AND optionText = ''";

            $strSQL = "SELECT count(goodsNo) as cnt, goodsNo, optionSno, optionText FROM " . $this->tableName . " WHERE siteKey = ?" . $strDirectSQL . " GROUP BY goodsNo, optionSno, optionText";
            $cartData = $this->db->query_fetch($strSQL, $arrBind);

            foreach ($cartData as $key => $val) {
                if ($val['cnt'] > 1) {
                    $arrBind = [
                        'sii',
                        Session::get('siteKey'),
                        $val['goodsNo'],
                        $val['optionSno'],
                    ];

                    $strSQL = "SELECT * FROM " . $this->tableName . " WHERE siteKey = ? AND goodsNo = ? AND optionSno = ?" . $strDirectSQL . " ORDER BY directCart DESC, regDt ASC, modDt ASC";
                    $mergeList = $this->db->query_fetch($strSQL, $arrBind);
                    $tempCnt = 0;
                    $tempOptionText = '';
                    $tempAddNo = '';
                    $tempAddCnt = '';
                    $tempArrayAdd = [];
                    $deliveryCollectFl = '';
                    $deliveryMethodFl = '';
                    foreach($mergeList as $k => $v) {
                        if($v['optionText']) continue;
                        if ($this->cartPolicy['sameGoodsFl'] == 'p') {
                            $tempCnt += $v['goodsCnt'];
                            if ($v['addGoodsNo'] != '' && $v['addGoodsNo'] != null) {
                                $tempAddNo = json_decode(gd_htmlspecialchars_stripslashes($v['addGoodsNo']));
                                $tempAddCnt = json_decode(gd_htmlspecialchars_stripslashes($v['addGoodsCnt']));
                                foreach ($tempAddNo as $num => $kval) {
                                    if ($tempArrayAdd[$kval]) {
                                        $tempArrayAdd[$kval] += $tempAddCnt[$num];
                                    } else {
                                        $tempArrayAdd[$kval] = $tempAddCnt[$num];
                                    }
                                }
                            }
                        } else {
                            if ($tempCnt == 0) {
                                $tempCnt = $v['goodsCnt'];
                            }
                            if ($v['addGoodsNo'] != '' && $v['addGoodsNo'] != null) {
                                $tempAddNo = json_decode(gd_htmlspecialchars_stripslashes($v['addGoodsNo']));
                                $tempAddCnt = json_decode(gd_htmlspecialchars_stripslashes($v['addGoodsCnt']));
                                foreach ($tempAddNo as $num => $kval) {
                                    if (!$tempArrayAdd[$kval]) {
                                        $tempArrayAdd[$kval] = $tempAddCnt[$num];
                                    }
                                }
                            }
                        }

                        $tempOptionText = $v['optionText'];

                        // 두번째 레코드부터는 데이터만 가지고 삭제
                        if ($k > 0) {
                            $arrDeleteBind = [];
                            $arrDeleteBind['param'] = 'sno = ?';
                            $this->db->bind_param_push($arrDeleteBind['bind'], 'i', $v['sno']);
                            $this->db->set_delete_db($this->tableName, $arrDeleteBind['param'], $arrDeleteBind['bind']);
                        }

                        //배송비 결제 방법, 배송방식 의 경우 가장 최근의 결제방법으로 변경처리한다.
                        $deliveryCollectFl = $v['deliveryCollectFl'];
                        $deliveryMethodFl = $v['deliveryMethodFl'];
                    }

                    if (count($tempArrayAdd) > 0) {
                        $tempAddNo = json_encode(ArrayUtils::removeEmpty(array_keys($tempArrayAdd)));
                        $tempAddCnt = json_encode(ArrayUtils::removeEmpty(array_values($tempArrayAdd)));
                    }

                    // 해당 상품의 구매가능(최대/최소) 수량 체크
                    $checkCnt = $this->getBuyableStock($val['goodsNo'], $tempCnt);
                    $arrUpdateBind = [
                        'isssssssii',
                        $checkCnt,
                        $tempOptionText,
                        $tempAddNo,
                        $tempAddCnt,
                        '',
                        $deliveryCollectFl,
                        $deliveryMethodFl,
                        Session::get('siteKey'),
                        $val['goodsNo'],
                        $val['optionSno'],
                    ];
                    $this->db->set_update_db($this->tableName, 'goodsCnt = ?, optionText = ?, addGoodsNo = ?, addGoodsCnt = ?, memberCouponNo = ?, deliveryCollectFl = ?, deliveryMethodFl = ?', 'siteKey = ? AND goodsNo = ? AND optionSno = ?', $arrUpdateBind);
                    unset($checkCnt);
                    unset($tempOptionText);
                    unset($tempAddNo);
                    unset($tempAddCnt);
                }
            }
        } else {
            if($this->isWrite === true){
                $arrBind = [
                    'is',
                    $memNo,
                    $this->siteKey,
                ];
                $isWriteAddSql = " AND siteKey = ?";
            }
            else {
                $arrBind = [
                    'i',
                    $memNo,
                ];
                $isWriteAddSql = '';
            }

            // 바로 구매로 넘어온경우
            if (Request::getFileUri() == 'payco_checkout.php' || Request::getFileUri() == 'naver_pay.php') {
                $this->cartPolicy['directOrderFl'] = 'y';
            }

            $strDirectSQL = "";
            if (Request::getFileUri() != 'cart.php' && Request::getFileUri() != 'order_ps.php') {
                if ($this->cartPolicy['directOrderFl'] == 'y') {
                    $strDirectSQL = " AND directCart = 'n'";
                }
            } else {
                $strDirectSQL = " AND directCart = 'n'";
            }
            $strDirectSQL .= " AND optionText = ''";

            $strSQL = "SELECT count(goodsNo) as cnt, goodsNo, optionSno, optionText FROM " . $this->tableName . " WHERE memNo = ?" . $strDirectSQL . $isWriteAddSql . "  GROUP BY goodsNo, optionSno, optionText";
            $cartData = $this->db->query_fetch($strSQL, $arrBind);

            foreach ($cartData as $key => $val) {
                if ($val['cnt'] > 1) {
                    if($this->isWrite === true) {
                        $arrBind = [
                            'iiiss',
                            $memNo,
                            $val['goodsNo'],
                            $val['optionSno'],
                            $val['optionText'],
                            $this->siteKey,
                        ];
                    }
                    else {
                        $arrBind = [
                            'iiis',
                            $memNo,
                            $val['goodsNo'],
                            $val['optionSno'],
                            $val['optionText'],
                        ];
                    }

                    $strSQL = "SELECT * FROM " . $this->tableName . " WHERE memNo = ? AND goodsNo = ? AND optionSno = ? AND optionText = ?" . $strDirectSQL . $isWriteAddSql . "  ORDER BY directCart DESC, regDt ASC, modDt ASC";
                    $mergeList = $this->db->query_fetch($strSQL, $arrBind);
                    $tempCnt = 0;
                    $tempOptionText = '';
                    $tempAddNo = '';
                    $tempAddCnt = '';
                    $tempArrayAdd = [];
                    $deliveryCollectFl = '';
                    $deliveryMethodFl = '';
                    foreach($mergeList as $k => $v) {
                        if($v['optionText']) continue;
                        if ($this->cartPolicy['sameGoodsFl'] == 'p') {
                            $tempCnt += $v['goodsCnt'];

                            if ($v['addGoodsNo'] != '' && $v['addGoodsNo'] != null) {
                                $tempAddNo = json_decode(gd_htmlspecialchars_stripslashes($v['addGoodsNo']));
                                $tempAddCnt = json_decode(gd_htmlspecialchars_stripslashes($v['addGoodsCnt']));
                                foreach ($tempAddNo as $num => $kval) {
                                    if ($tempArrayAdd[$kval]) {
                                        $tempArrayAdd[$kval] += $tempAddCnt[$num];
                                    } else {
                                        $tempArrayAdd[$kval] = $tempAddCnt[$num];
                                    }
                                }
                            }
                        } else {
                            if ($tempCnt == 0) {
                                $tempCnt = $v['goodsCnt'];
                            }
                            if ($v['addGoodsNo'] != '' && $v['addGoodsNo'] != null) {
                                $tempAddNo = json_decode(gd_htmlspecialchars_stripslashes($v['addGoodsNo']));
                                $tempAddCnt = json_decode(gd_htmlspecialchars_stripslashes($v['addGoodsCnt']));
                                foreach ($tempAddNo as $num => $kval) {
                                    if (!$tempArrayAdd[$kval]) {
                                        $tempArrayAdd[$kval] = $tempAddCnt[$num];
                                    }
                                }
                            }
                        }

                        $tempOptionText = $v['optionText'];

                        // 사용쿠폰이있는경우 memberCoupon테이블에서 정보 삭제
                        if ($v['memberCouponNo'] != '') {
                            $memberCouponArray = array();
                            $memberCouponArray = explode(INT_DIVISION, $v['memberCouponNo']);
                            if(count($memberCouponArray) > 0){
                                foreach($memberCouponArray as $memberCouponArrayKey => $memberCouponArrayValue){
                                    $arrCouponBind = [
                                        'ssi',
                                        'y',
                                        '0000-00-00 00:00:00',
                                        $memberCouponArrayValue,
                                    ];
                                    if($this->isWrite === true){
                                        $memberCouponStateQuery = "orderWriteCouponState = ?,";
                                    }
                                    else {
                                        $memberCouponStateQuery = "memberCouponState = ?,";
                                    }
                                    $this->db->set_update_db(DB_MEMBER_COUPON, $memberCouponStateQuery.' memberCouponCartDate = ?', 'memberCouponNo = ?', $arrCouponBind);
                                }
                            }

                            if($this->isWrite === true){
                                //수기주문에서 회원 장바구니추가로 사용된 쿠폰정보를 삭제처리
                                //(이는 가사용 쿠폰을 실제 사용으로 바꿔주기 위해 존재하는 데이터)
                                $owMemberCartSnoData = Cookie::get('owMemberCartSnoData');
                                $owMemberRealCartSnoData = Cookie::get('owMemberRealCartSnoData');
                                $owMemberCartCouponNoData = Cookie::get('owMemberCartCouponNoData');

                                if(trim($owMemberCartSnoData) !== ''){
                                    $owMemberCartSnoDataArr = explode(",", $owMemberCartSnoData);
                                    $owMemberRealCartSnoDataArr = explode(",", $owMemberRealCartSnoData);
                                    $owMemberCartCouponNoDataArr = explode(",", $owMemberCartCouponNoData);

                                    if(count($owMemberCartSnoDataArr) > 0){
                                        $cartSnoIndex = array_search($v['sno'], $owMemberCartSnoDataArr);
                                        if ($cartSnoIndex === 0 || (int)$cartSnoIndex > 0) {
                                            unset($owMemberCartSnoDataArr[$cartSnoIndex]);
                                            unset($owMemberRealCartSnoDataArr[$cartSnoIndex]);
                                            unset($owMemberCartCouponNoDataArr[$cartSnoIndex]);
                                        }
                                    }
                                    $owMemberCartSnoDataArrNew = implode(",", $owMemberCartSnoDataArr);
                                    $owMemberRealCartSnoDataArrNew = implode(",", $owMemberRealCartSnoDataArr);
                                    $owMemberCartCouponNoDataArrNew = implode(",", $owMemberCartCouponNoDataArr);

                                    Cookie::set('owMemberCartSnoData', $owMemberCartSnoDataArrNew, 0, '/', '', false, false);
                                    Cookie::set('owMemberRealCartSnoData', $owMemberRealCartSnoDataArrNew, 0, '/', '', false, false);
                                    Cookie::set('owMemberCartCouponNoData', $owMemberCartCouponNoDataArrNew, 0, '/', '', false, false);
                                }
                            }
                        }

                        // 두번째 레코드부터는 데이터만 가지고 삭제
                        if ($k > 0) {
                            $arrDeleteBind = [];
                            $arrDeleteBind['param'] = 'sno = ?';
                            $this->db->bind_param_push($arrDeleteBind['bind'], 'i', $v['sno']);
                            $this->db->set_delete_db($this->tableName, $arrDeleteBind['param'], $arrDeleteBind['bind']);
                        }

                        //배송비 결제 방법, 배송방식 의 경우 가장 최근의 결제방법으로 변경처리한다.
                        $deliveryCollectFl = $v['deliveryCollectFl'];
                        $deliveryMethodFl = $v['deliveryMethodFl'];
                    }

                    if (count($tempArrayAdd) > 0) {
                        $tempAddNo = json_encode(ArrayUtils::removeEmpty(array_keys($tempArrayAdd)));
                        $tempAddCnt = json_encode(ArrayUtils::removeEmpty(array_values($tempArrayAdd)));
                    }

                    // 해당 상품의 구매가능(최대/최소) 수량 체크
                    $checkCnt = $this->getBuyableStock($val['goodsNo'], $tempCnt);
                    $arrUpdateBind = [
                        'sissssssi',
                        Session::get('siteKey'),
                        $checkCnt,
                        $tempOptionText,
                        $tempAddNo,
                        $tempAddCnt,
                        '',
                        $deliveryCollectFl,
                        $deliveryMethodFl,
                        $mergeList[0]['sno'],
                    ];
                    $this->db->set_update_db($this->tableName, 'siteKey = ?, goodsCnt = ?, optionText = ?, addGoodsNo = ?, addGoodsCnt = ?, memberCouponNo = ?, deliveryCollectFl = ?, deliveryMethodFl = ?', 'sno = ?', $arrUpdateBind);
                    unset($checkCnt);
                    unset($tempOptionText);
                    unset($tempAddNo);
                    unset($tempAddCnt);
                }
            }
        }
    }

    /**
     * 적립기준이 상품별/주문별 구매금액일 경우 지급 회원마일리지 재계산
     *
     * @param array $tmp 회원 결제금액 (상품별)
     * @param array $tmpOrder 회원 결제금액(총 주문)
     * @param array $memInfo 회원그룹정보
     *
     * @return array $mileage 지급마일리지 정보
     */
    public function getMemberGoodsMileageData($tmp, $tmpOrder = [], $memInfo)
    {
        // 회원 그룹 마일리지 금액
        $mileage = [];
        if ($memInfo['fixedOrderTypeMileage'] == 'order' && empty($tmpOrder) === false) {
            $memberDcByPrice = $tmpOrder['memberDcByPrice'];
            if (in_array('goods', $memInfo['fixedRateOption']) === true && empty($tmpOrder['memberDcByAddPrice']) === false) {
                $memberDcByPrice += $tmpOrder['memberDcByAddPrice'];
            }
        } else {
            $memberDcByPrice = array_sum($tmp['all']['memberDcByPrice']);
            if (in_array('goods', $memInfo['fixedRateOption']) === true && empty($tmp['all']['memberDcByAddPrice']) === false) {
                foreach ($tmp['all']['memberDcByAddPrice'] as $v) {
                    $memberDcByPrice += array_sum($v);
                }
            }
        }

        if ($this->mileageGiveInfo['info']['useFl'] === 'y' && $memberDcByPrice >= $memInfo['mileageLine']) {
            if ($memInfo['mileageType'] === 'percent') {
                foreach ($tmp['all']['memberDcByPrice'] as $k => $v) {
                    $memberMileagePercent = $memInfo['mileagePercent'] / 100;
                    $mileage['goods'][$k] = gd_number_figure($v * $memberMileagePercent, $this->mileageGiveInfo['trunc']['unitPrecision'], $this->mileageGiveInfo['trunc']['unitRound']);
                    $mileage['memberMileage'] += gd_number_figure($v * $memberMileagePercent, $this->mileageGiveInfo['trunc']['unitPrecision'], $this->mileageGiveInfo['trunc']['unitRound']);

                    if (empty($tmp['all']['memberDcByAddPrice']) === false) {
                        foreach ($tmp['all']['memberDcByAddPrice'][$k] as $key => $val) {
                            $mileage['addGoods'][$k][$key] = gd_number_figure($val * $memberMileagePercent, $this->mileageGiveInfo['trunc']['unitPrecision'], $this->mileageGiveInfo['trunc']['unitRound']);
                            $mileage['memberMileage'] += gd_number_figure($val * $memberMileagePercent, $this->mileageGiveInfo['trunc']['unitPrecision'], $this->mileageGiveInfo['trunc']['unitRound']);
                        }
                    }
                }
            } else {
                $mileage['memberMileage'] = $memInfo['mileagePrice'];
            }
        }

        return $mileage;
    }

    /**
     * 추가 할인 기준이 상품별/주문별 구매금액일 경우 추가 할인 재계산
     *
     * @param array $tmp 회원 결제금액 (상품별)
     * @param array $tmpOrder 회원 결제금액(총 주문)
     * @param integer $goodsNo 상품번호
     * @param array $memInfo 회원그룹정보
     * @param array $goodsPrice 상품금액정보
     * @param array $arrCateCd 상품연결 카테고리
     * @param boolean $addDcFl 추가 할인 사용여부
     * @param array $arrBrandCd 연결 브랜드 (브랜드별 회원등급 추가 할인에서 사용)
     *
     * @return array 추가할인 정보
     */
    public function getMemberGoodsAddDcPriceData($tmp, $tmpOrder = [], $goodsNo, $memInfo, $goodsPrice, $arrCateCd, $addDcFl, $couponDcPrice = [], $arrBrandCd = null)
    {
        if ($memInfo['fixedOrderTypeDc'] == 'order' && empty($tmpOrder) === false) {
            $memberDcByPrice = $tmpOrder['memberDcByPrice'];
            if (in_array('goods', $memInfo['fixedRateOption']) === true && empty($tmpOrder['memberDcByAddPrice']) === false) {
                $memberDcByPrice += $tmpOrder['memberDcByAddPrice'];
            }
            if ($memInfo['fixedRatePrice'] == 'settle') {
                $memberDcByPrice -= array_sum($couponDcPrice);
            }
        } else {
            $memberDcByPrice = array_sum($tmp['all']['memberDcByPrice']);
            if (in_array('goods', $memInfo['fixedRateOption']) === true && empty($tmp['all']['memberDcByAddPrice']) === false) {
                foreach ($tmp['all']['memberDcByAddPrice'] as $v) {
                    $memberDcByPrice += array_sum($v);
                }
            }
            if ($memInfo['fixedRatePrice'] == 'settle') {
                $memberDcByPrice -= $couponDcPrice[$goodsNo];
            }
        }
        $memberDcByPrice = $memberDcByPrice > 0 ? $memberDcByPrice : 0;

        // 절사 내용
        $tmp['trunc'] = Globals::get('gTrunc.member_group');

        // 회원 등급별 추가 할인 체크
        if ($addDcFl === true && empty($arrCateCd[$goodsNo]) === false) {
            // 해당 상품이 연결된 카테고리 체크
            foreach ($arrCateCd[$goodsNo] as $gVal) {
                if (isset($memInfo['dcExCategory']) && in_array($gVal, $memInfo['dcExCategory'])) {
                    $addDcFl = false;
                }
            }
        }

        // 금액 체크
        if ($addDcFl === true && $memberDcByPrice < $memInfo['dcLine']) {
            $addDcFl = false;
        }

        // 브랜드 할인율, 무통장 할인율
        if ($memInfo['fixedOrderTypeDc'] == 'brand') {
            foreach ($arrBrandCd[$goodsNo] as $gKey => $gVal) {
                foreach ($memInfo['dcBrandInfo']->cateCd AS $mKey => $mVal) {
                    if ($gKey == $mVal) {
                        $memInfo['dcPercent'] = $memInfo['dcBrandInfo']->goodsDiscount[$mKey];
                        $memInfo['brandDiscount'] = $memInfo['dcBrandInfo']->goodsDiscount[$mKey];
                    }
                }
            }
        }

        // 회원그룹 추가 할인
        $memberDcPrice = $this->getDcPriceData($addDcFl, $memInfo['dcType'], $memInfo['dcPercent'], $memInfo['dcPrice'], $tmp, $goodsPrice, $memberDcByPrice);


        // 브랜드 할인
        if ($memInfo['brandDiscount'] > 0) {
            $memberBrandDcPrice = $this->getDcPriceData($addDcFl, $memInfo['dcType'], $memInfo['brandDiscount'], $memInfo['dcPrice'], $tmp, $goodsPrice, $memberDcByPrice);
        }

        return ['addDcFl' => $addDcFl, 'memberDcPrice' => $memberDcPrice['memberDcPrice'], 'info' => $memberDcPrice, 'memberBrandDcPrice' => $memberBrandDcPrice];
    }

    /**
     * 중복 할인 기준이 상품별/주문별 구매금액일 경우 중복 할인 재계산
     *
     * @param array $tmp 회원 결제금액 (상품별)
     * @param array $tmpOrder 회원 결제금액(총 주문)
     * @param integer $goodsNo 상품번호
     * @param array $memInfo 회원그룹정보
     * @param array $goodsPrice 상품금액정보
     * @param array $arrCateCd 상품연결 카테고리
     * @param boolean $overlapDcFl 중복 할인 사용여부
     *
     * @return array 중복 할인 정보
     */
    public function getMemberGoodsOverlapDcPriceData($tmp, $tmpOrder = [], $goodsNo, $memInfo, $goodsPrice, $arrCateCd, $overlapDcFl, $couponDcPrice = [])
    {
        if ($memInfo['fixedOrderTypeOverlapDc'] == 'order' && empty($tmpOrder) === false) {
            $memberDcByPrice = $tmpOrder['memberDcByPrice'];
            if (in_array('goods', $memInfo['fixedRateOption']) === true && empty($tmpOrder['memberDcByAddPrice']) === false) {
                $memberDcByPrice += $tmpOrder['memberDcByAddPrice'];
            }
            if ($memInfo['fixedRatePrice'] == 'settle') {
                $memberDcByPrice -= array_sum($couponDcPrice);
            }
        } else {
            $memberDcByPrice = array_sum($tmp['all']['memberDcByPrice']);
            if (in_array('goods', $memInfo['fixedRateOption']) === true && empty($tmp['all']['memberDcByAddPrice']) === false) {
                foreach ($tmp['all']['memberDcByAddPrice'] as $v) {
                    $memberDcByPrice += array_sum($v);
                }
            }
            if ($memInfo['fixedRatePrice'] == 'settle') {
                $memberDcByPrice -= $couponDcPrice[$goodsNo];
            }
        }
        $memberDcByPrice = $memberDcByPrice > 0 ? $memberDcByPrice : 0;

        // 절사 내용
        $tmp['trunc'] = Globals::get('gTrunc.member_group');

        // 회원 등급별 중복 할인 체크
        if ($overlapDcFl === false && empty($arrCateCd[$goodsNo]) === false) {
            // 해당 상품이 연결된 카테고리 체크
            foreach ($arrCateCd[$goodsNo] as $gVal) {
                if (isset($memInfo['overlapDcCategory']) && in_array($gVal, $memInfo['overlapDcCategory'])) {
                    $overlapDcFl = true;
                }
            }
        }

        // 금액 체크
        if ($overlapDcFl === true && $memberDcByPrice < $memInfo['overlapDcLine']) {
            $overlapDcFl = false;
        }

        // 회원그룹 중복 할인
        $memberOverlapDcPrice = $this->getDcPriceData($overlapDcFl, $memInfo['dcType'], $memInfo['overlapDcPercent'], $memInfo['overlapDcPrice'], $tmp, $goodsPrice, $memberDcByPrice);

        return ['overlapDcFl' => $overlapDcFl, 'memberOverlapDcPrice' => $memberOverlapDcPrice['memberDcPrice'], 'info' => $memberOverlapDcPrice];
    }

    /**
     * 상품의 단가/합계금액
     *
     * @param array $memInfo 회원그룹정보
     * @param array $goodsPrice 상품금액정보
     *
     * @return array $tmpPrice, $goodsPrice 상품의 단가/합계금액 정보, 상품정보(쿠폰 안분금액 포함)
     */
    public function getUnitGoodsPriceData($memInfo, $goodsPrice)
    {
        $tmp = [];
        foreach ($goodsPrice['goodsCnt'] as $k => $v) {
            $minusDcFl = true;
            $tmpGoodsPrice = $tmpGoodsCouponPrice = ($goodsPrice['goodsPrice'][$k] * $v);
            $tmpAddGoodsCouponPrice = $goodsPrice['orderCoupon']['tmpAddGoodsCouponPrice'][$k];
            $divisionOrderCouponByAddGoods = $goodsPrice['orderCoupon']['divisionOrderCouponByAddGoods'][$k];
            if (empty($this->deliveryFree) === false && $memInfo['fixedRatePrice'] == 'settle' && empty($goodsPrice['orderCoupon']['tmpGoodsPrice'][$k]) === false) {
                $tmpGoodsPrice = $goodsPrice['orderCoupon']['tmpGoodsPrice'][$k];
                $minusDcFl = false;
            }
            if ($memInfo['fixedRatePrice'] == 'settle') {
                if ($minusDcFl === true) $tmpGoodsPrice -= $goodsPrice['goodsDcPrice'][$k];

                // 마이앱 사용에 따른 분기 처리
                if ($this->useMyapp) {
                    $tmpGoodsPrice -= $goodsPrice['myappDcPrice'][$k];
                }
            }

            $tmp['unit']['memberDcByPrice'][$k] = ceil($tmpGoodsPrice / $v);
            $tmp['all']['memberDcByPrice'][$k] = $tmpGoodsPrice;

            if (empty($memInfo['fixedRateOption']) === false) {
                if (in_array('option', $memInfo['fixedRateOption']) == '1') {
                    $tmp['unit']['memberDcByPrice'][$k] += $goodsPrice['optionPrice'][$k];
                    $tmp['all']['memberDcByPrice'][$k] += $goodsPrice['optionPrice'][$k] * $v;
                }
                if (in_array('text', $memInfo['fixedRateOption']) == '1') {
                    $tmp['unit']['memberDcByPrice'][$k] += $goodsPrice['optionTextPrice'][$k];
                    $tmp['all']['memberDcByPrice'][$k] += $goodsPrice['optionTextPrice'][$k] * $v;
                }
                if (in_array('goods', $memInfo['fixedRateOption']) == '1') {
                    if (empty($goodsPrice['addGoodsCnt'][$k]) === false) {
                        $addGoodsDivisionCouponPriceSum = 0;

                        $lastKey = end(array_keys($goodsPrice['addGoodsCnt'][$k]));
                        foreach ($goodsPrice['addGoodsCnt'][$k] as $key => $val) {
                            if (empty($divisionOrderCouponByAddGoods) === false && empty($tmpAddGoodsCouponPrice) === false) {
                                if ($key == $lastKey) {
                                    $addGoodsDivisionCouponPrice = $goodsPrice['orderCoupon']['divisionOrderCouponByAddGoods'][$k] - $addGoodsDivisionCouponPriceSum;
                                } else {
                                    $addGoodsDivisionCouponPrice = ceil(($goodsPrice['orderCoupon']['divisionOrderCouponByAddGoods'][$k] * ($goodsPrice['addGoodsPrice'][$k][$key] * $val)) / $tmpAddGoodsCouponPrice);
                                    $addGoodsDivisionCouponPriceSum += $addGoodsDivisionCouponPrice;
                                }
                                $goodsPrice['addGoodsPrice'][$k][$key] -= ceil($addGoodsDivisionCouponPrice / $val);
                            }
                            $tmp['unit']['memberDcByAddPrice'][$k][$key] += $goodsPrice['addGoodsPrice'][$k][$key];
                            $tmp['all']['memberDcByAddPrice'][$k][$key] = $goodsPrice['addGoodsPrice'][$k][$key] * $val;
                        }
                    }
                }
            }
        }

        return ['tmpPrice' => $tmp, 'goodsPrice' => $goodsPrice];
    }

    /**
     * 할인금액 계산
     *
     * @param boolean $dcFl 할인사용여부
     * @param string $dcType 상품금액정보
     * @param integer $dcPercent 중복 할인 사용여부
     * @param integer $dcPrice 할인 정액금액
     * @param array $tmpPrice 회원할인정보
     * @param array $goodsPrice 상품금액정보
     * @param integer $memberDcByPrice 상품금액
     *
     * @return array $price 할인금액정보
     */
    public function getDcPriceData($dcFl, $dcType, $dcPercent, $dcPrice,  $tmpPrice, $goodsPrice, $memberDcByPrice)
    {
        // 회원그룹 추가 할인
        $price = [];
        if ($dcFl === true) {
            if ($dcType === 'percent') {
                $memberDcPercent = $dcPercent / 100;
                foreach ($tmpPrice['unit']['memberDcByPrice'] as $k => $v) {
                    if (empty($this->totalCouponOrderDcPrice) === true && $this->_memInfo['fixedRatePrice'] == 'settle' && $goodsPrice['couponDcPrice'][$k] > 0) {
                        $couponDivisionPrice = round($goodsPrice['couponDcPrice'][$k] / $goodsPrice['goodsCnt'][$k]);
                        $v = $v - $couponDivisionPrice;
                        if ($v < 0) $v = 0;
                    }
                    $memberDcUnitPrice = gd_number_figure($v * $memberDcPercent, $tmpPrice['trunc']['unitPrecision'], $tmpPrice['trunc']['unitRound']);
                    $price['goods'][$k] = $memberDcUnitPrice * $goodsPrice['goodsCnt'][$k];
                    $price['memberDcPrice'] += $memberDcUnitPrice * $goodsPrice['goodsCnt'][$k];

                    if (empty($tmpPrice['unit']['memberDcByAddPrice']) === false) {
                        foreach ($tmpPrice['unit']['memberDcByAddPrice'][$k] as $key => $val) {
                            if ($this->_memInfo['fixedOrderTypeDc'] == 'brand' && in_array('goods', $this->_memInfo['fixedRateOption']) == '1') {
                                unset($memberDcPercent);
                                if ($goodsPrice['brandDiscount'][$k][$key]) {
                                    $memberDcPercent = $goodsPrice['brandDiscount'][$k][$key] / 100;
                                }
                            }
                            $memberDcUnitAddPrice = gd_number_figure($val * $memberDcPercent, $tmpPrice['trunc']['unitPrecision'], $tmpPrice['trunc']['unitRound']);
                            $price['addGoods'][$k][$key] = $memberDcUnitAddPrice * $goodsPrice['addGoodsCnt'][$k][$key];
                            $price['memberDcPrice'] += $memberDcUnitAddPrice * $goodsPrice['addGoodsCnt'][$k][$key];
                        }
                    }
                }
            } else {
                $price['memberDcPrice'] = $dcPrice;
            }

            // 상품금액보다 회원할인액이 더 큰 경우 회원할인액 조정
            $exceptMemberDcPrice = $memberDcByPrice;
            if ($price['memberDcPrice'] > $exceptMemberDcPrice) {
                $price['memberDcPrice'] = $exceptMemberDcPrice;
            }
        }

        return $price;
    }

    /**
     * 쿠폰 사용불가 상품 쿠폰 초기화
     *
     * @param array $exceptCouponNo 쿠폰정보
     *
     * @return boolean true
     * */
    public function setCartCouponReset($exceptCouponNo)
    {
        $cartSno = array_keys($exceptCouponNo);
        $memberCouponSno = array_values($exceptCouponNo);

        if ($this->isWrite == true) {
            $tableCart = DB_CART_WRITE;
            $arrCouponData = ['orderWriteCouponState' => 'y'];
        } else {
            $tableCart = DB_CART;
            $arrCouponData = ['memberCouponState' => 'y'];
        }

        // 장바구니 쿠폰번호 제거
        $arrData = ['memberCouponNo' => ''];
        $arrBind = $this->db->get_binding(DBTableField::tableCart(), $arrData, 'update', array_keys($arrData));
        $this->db->bind_param_push($arrBind['bind'], 'i', @implode(',', $cartSno));
        $this->db->bind_param_push($arrBind['bind'], 'i', $this->members['memNo']);
        $this->db->set_update_db($tableCart, $arrBind['param'], 'sno IN (?) AND memNo = ?', $arrBind['bind']);
        unset($arrData, $arrBind);

        // 회원쿠폰사용여부 초기화
        $arrBind = $this->db->get_binding(DBTableField::tableMemberCoupon(), $arrCouponData, 'update', array_keys($arrCouponData));
        $this->db->bind_param_push($arrBind['bind'], 'i', @implode(',', $memberCouponSno));
        $this->db->bind_param_push($arrBind['bind'], 'i', $this->members['memNo']);
        $this->db->set_update_db(DB_MEMBER_COUPON, $arrBind['param'], 'memberCouponNo IN (?) AND memNo = ?', $arrBind['bind']);
        unset($arrCouponData, $arrBind);

        return true;
    }

    /**
     * 상품무게 체크
     *
     * @param array $arrData 데이터
     *
     * @return string
     *
     */
    public function checkGoodsWeight($arrData)
    {
        $arrBind = [];
        // 상품의 배송비 선불/착불 값
        $this->db->strWhere = 'g.goodsNo = ?';
        $this->db->bind_param_push($arrBind, 'i', $arrData['goodsNo']);

        $this->db->strField = 'sdb.fixFl, sdb.rangeLimitFl, sdb.rangeLimitWeight, g.goodsWeight';

        $this->db->strJoin = ' LEFT JOIN ' . DB_SCM_DELIVERY_BASIC . ' as sdb ON g.deliverySno = sdb.sno';
        $query = $this->db->query_complete();
        $strSQL = 'SELECT ' . array_shift($query) . ' FROM ' . DB_GOODS . ' as g ' . implode(' ', $query);
        $getData = $this->db->query_fetch($strSQL, $arrBind);

        //무게별 배송비를 사용중이고 범위 제한을 사용하는경우 체크
        if($getData[0]['fixFl'] === 'weight' && $getData[0]['rangeLimitFl'] === 'y'){
            if(($arrData['goodsCnt']*$getData[0]['goodsWeight']) > $getData[0]['rangeLimitWeight']){
                $weight = Globals::get('gWeight');

                return $getData[0]['rangeLimitWeight'] . $weight['unit'];
            }
        }

        return '';
    }

    /**
     * 장바구니 상품수량 재정의
     *
     * @param integer $memNo 회원번호
     * @param integer $cartIdx 장바구니번호
     *
     * @return array $tmpCartSno 정렬완료된 장바구니 번호
     *
     */
    public function setCartGoodsCnt($memNo, $cartIdx = null)
    {
        $goods = \App::load(\Component\Goods\Goods::class);
        $tmpCartSno = [];

        $arrBind = $arrWhere = [];
        if (empty($memNo) === false) {
            $arrWhere[] = 'memNo = ?';
            $this->db->bind_param_push($arrBind, 'i', $memNo);

            if($this->isWrite === true) {
                $arrWhere[] = 'siteKey = ?';
                $this->db->bind_param_push($arrBind, 's', $this->siteKey);
            }
        } else {
            $arrWhere[] = 'siteKey = ?';
            $this->db->bind_param_push($arrBind, 's', $this->siteKey);
        }
        if (empty($cartIdx) === false) {
            if (is_array($cartIdx)) {
                $tmpWhere = [];
                foreach ($cartIdx as $cartSno) {
                    if (is_numeric($cartSno)) {
                        $tmpWhere[] = $this->db->escape($cartSno);
                    }
                }
                if (empty($tmpWhere) === false) {
                    $tmpAddWhere = [];
                    foreach ($tmpWhere as $val) {
                        $tmpAddWhere[] = '?';
                        $this->db->bind_param_push($arrBind, 'i', $val);
                    }
                    $arrWhere[] = 'sno IN (' . implode(' , ', $tmpAddWhere) . ')';
                }
                unset($tmpWhere);
            } elseif (is_numeric($cartIdx)) {
                $arrWhere[] = 'sno = ?';
                $this->db->bind_param_push($arrBind, 'i', $cartIdx);
            }
        }

        // 바로 구매로 넘어온경우
        if (Request::getFileUri() == 'payco_checkout.php' || Request::getFileUri() == 'naver_pay.php') {
            $this->cartPolicy['directOrderFl'] = 'y';
        }

        if (Cookie::has('isDirectCart') && $this->cartPolicy['directOrderFl'] == 'y') {
            $arrWhere[] = 'directCart = ?';
            $this->db->bind_param_push($arrBind, 's', 'y');
        }

        $this->db->strField = 'sno, goodsNo, optionSno, goodsCnt, useBundleGoods';
        $this->db->strWhere = implode(' AND ', gd_isset($arrWhere));
        $this->db->strOrder = 'sno DESC';

        $query = $this->db->query_complete();
        $strSQL = 'SELECT ' . array_shift($query) . ' FROM ' . $this->tableName . implode(' ', $query);
        $data = $this->db->query_fetch($strSQL, $arrBind);

        $goodsData = $tmpData = [];
        foreach ($data as $val) {
            if (empty($val['useBundleGoods']) === true) return $cartIdx;

            if (empty($goodsData[$val['goodsNo']]) === true) {
                $goodsData[$val['goodsNo']] = $goods->getGoodsInfo($val['goodsNo'], 'fixedSales, salesUnit, fixedOrderCnt, minOrderCnt, maxOrderCnt');
            }

            $tmpGoodsData = $goodsData[$val['goodsNo']];
            $tmpGoodsCnt = $val['goodsCnt'];
            if ($tmpGoodsData['fixedOrderCnt'] == 'option') {
                if ($tmpGoodsData['minOrderCnt'] > 1 && $tmpGoodsData['minOrderCnt'] > $tmpGoodsCnt) {
                    $tmpGoodsCnt = $tmpGoodsData['minOrderCnt'];

                }
                if ($tmpGoodsData['maxOrderCnt'] > 0 && $tmpGoodsData['maxOrderCnt'] < $tmpGoodsCnt) {
                    $tmpGoodsCnt = $tmpGoodsData['maxOrderCnt'];
                }
            }
            if ($tmpGoodsData['fixedSales'] == 'option' && $tmpGoodsCnt % $tmpGoodsData['salesUnit'] != 0) {
                $tmpGoodsCnt = $tmpGoodsCnt - ($tmpGoodsCnt % $tmpGoodsData['salesUnit']);
            }

            if ($tmpGoodsCnt != $val['goodsCnt'] && $tmpGoodsCnt > 0) {
                $arrData['goodsCnt'] = $tmpGoodsCnt;
                $subBind = $this->db->get_binding(DBTableField::tableCart(), $arrData, 'update', array_keys($arrData));
                $this->db->bind_param_push($subBind['bind'], 'i', $val['sno']);
                $this->db->set_update_db($this->tableName, $subBind['param'], 'sno = ?', $subBind['bind']);
            } else {
                $tmpGoodsCnt = $val['goodsCnt'];
            }

            $tmpData[$val['goodsNo']]['goodsCnt'] += $tmpGoodsCnt;
            $tmpData[$val['goodsNo']]['goodsCntBySno'][$val['sno']] = $tmpGoodsCnt;
        }
        unset($tmpGoodsData, $tmpGoodsCnt, $subBind);

        //
        foreach ($tmpData as $goodsNo => $val) {
            $tmpGoodsData = $goodsData[$goodsNo];
            $tmpGoodsCnt = $val['goodsCnt'];

            if ($tmpGoodsData['minOrderCnt'] > 1 && $tmpGoodsData['minOrderCnt'] > $tmpGoodsCnt) {
                $tmpGoodsCnt = $tmpGoodsData['minOrderCnt'];
            }
            if ($tmpGoodsData['maxOrderCnt'] > 0 && $tmpGoodsData['maxOrderCnt'] < $tmpGoodsCnt) {
                $tmpGoodsCnt = $tmpGoodsData['maxOrderCnt'];
                if ($tmpGoodsData['fixedOrderCnt'] == 'option') $tmpGoodsCnt *= count($val['goodsCntBySno']);
            }

            if ($tmpGoodsCnt % $tmpGoodsData['salesUnit'] != 0) {
                $tmpGoodsCnt = $tmpGoodsCnt - ($tmpGoodsCnt % $tmpGoodsData['salesUnit']);
            }

            foreach ($val['goodsCntBySno'] as $sno => $goodsCnt) {
                if ($tmpGoodsCnt <= 0) break;

                if ($tmpGoodsCnt - $goodsCnt >= 0) {
                    $tmpGoodsCnt -= $goodsCnt;
                    $tmpCartSno[] = $sno;
                } else {
                    if ($goodsCnt > $tmpGoodsCnt) {
                        $arrData['goodsCnt'] = $tmpGoodsCnt;
                        $tmpCartSno[] = $sno;
                        $subBind = $this->db->get_binding(DBTableField::tableCart(), $arrData, 'update', array_keys($arrData));
                        $this->db->bind_param_push($subBind['bind'], 'i', $sno);
                        $this->db->set_update_db($this->tableName, $subBind['param'], 'sno = ?', $subBind['bind']);
                        break;
                    } else {}
                }
            }
        }
        unset($tmpGoodsData, $tmpGoodsCnt, $subBind);

        return $tmpCartSno;
    }

    /**
     * SNO / bind 기준 장바구니 상품수량 조회 함수
     *
     * @param integer $cartSno 장바구니일련번호
     * @param integer $arrBind 바인딩쿼리
     * @param string  $field 필드명
     *
     * @return array 상품번호
     *
     */
    public function checkCartSelectGoodsNo($cartSno = 0, $arrBind = null, $field = null) {
        // sno 필드가 넘어온 경우 sno 단일 조회
        if($field == 'sno') {
            $strWhere = ' WHERE ' . $field . ' = ?';
            $this->db->bind_param_push($arrBind, 's', $cartSno);
            $strSQL = 'SELECT goodsNo FROM ' . $this->tableName . $strWhere;
            $getData = $this->db->query_fetch($strSQL . " group by goodsNo", $arrBind, false);
        }
        else { // bind param 값으로 조건 생성
            if($arrBind) {
                $strSQL = 'SELECT goodsNo FROM ' . $this->tableName . ' WHERE ' . $arrBind['param'];
                $getData = $this->db->query_fetch($strSQL . " group by goodsNo", $arrBind['bind']);
            }
        }
        return $getData;
    }


    /**
     * 상품쿠폰 복수배송지 일 경우 전체 CartSno를 인자로 받아 Row 데이터 추출
     *
     * @param array $postValue 장바구니데이터
     *
     * @return array 상품 data array
     *
     */
    public function getCartProductCouponDataInfo($postValue)
    {
        $cartAllIdx = array_unique($postValue['cartAllSno']); // 중복제거
        $returnData = []; // 리턴배열

        // 선택한 상품만 주문시
        $arrBind = [];
        if (empty($cartAllIdx) === false) {
            if (is_array($cartAllIdx)) {
                $tmpWhere = [];
                foreach ($cartAllIdx as $cartSno) {
                    if (is_numeric($cartSno)) {
                        $tmpWhere[] = $this->db->escape($cartSno);
                    }
                }
                if (empty($tmpWhere) === false) {
                    $tmpAddWhere = [];
                    foreach ($tmpWhere as $val) {
                        $tmpAddWhere[] = '?';
                        $this->db->bind_param_push($arrBind, 'i', $val);
                    }
                    $arrWhere[] = 'c.sno IN (' . implode(' , ', $tmpAddWhere) . ')';
                }
                unset($tmpWhere);
            } elseif (is_numeric($cartAllIdx)) {
                $arrWhere[] = 'c.sno = ?';
                $this->db->bind_param_push($arrBind, 'i', $cartAllIdx);
            }
        }

        // 회원 로그인 체크
        // App::getInstance('ControllerNameResolver')->getControllerRootDirectory() != 'admin'
        if ($this->isLogin === true) {
            //수기주문시 회원인 경우 memNo, siteKey 로 동시 비교
            if($this->isWrite === true  && $this->useRealCart !== true){
                $arrWhere[] = 'c.memNo = \'' . $this->members['memNo'] . '\' AND  c.siteKey = \'' . $this->siteKey . '\'';
            }
            else {
                $arrWhere[] = 'c.memNo = \'' . $this->members['memNo'] . '\'';
            }
        } else {
            $arrWhere[] = 'c.siteKey = \'' . $this->siteKey . '\'';
        }

        // 바로 구매 설정
        if (Cookie::has('isDirectCart') && $this->cartPolicy['directOrderFl'] == 'y' && Request::getFileUri() != 'cart.php' && (Request::getFileUri() != 'order_ps.php' || (Request::getFileUri() == 'order_ps.php' && in_array(Request::post()->get('mode'), ['set_recalculation']) === true))) {
            $arrWhere[] = 'c.directCart = \'y\'';
        }

        // 정렬 방식
        $strOrder = 'c.sno DESC';

        $arrExclude['cart'] = [];
        $arrExclude['option'] = [
            'goodsNo',
            'optionNo',
        ];
        $arrExclude['addOptionName'] = [
            'goodsNo',
            'optionCd',
            'mustFl',
        ];
        $arrExclude['addOptionValue'] = [
            'goodsNo',
            'optionCd',
        ];
        $arrInclude['goods'] = [
            'goodsNm',
            'commission',
            'scmNo',
            'goodsCd',
            'cateCd',
            'mileageFl',
            'mileageGoods',
            'mileageGoodsUnit',
            'goodsDiscountFl',
            'goodsDiscount',
            'goodsDiscountUnit',
            'payLimitFl',
            'payLimit',
            'goodsPriceString',
            'goodsPrice',
            'fixedPrice',
            'costPrice',
            'optionFl',
            'optionName',
            'optionTextFl',
            'addGoodsFl',
            'addGoods',
            'deliverySno',
            'delFl',
            'hscode',
            'exceptBenefit',
            'exceptBenefitGroup',
            'exceptBenefitGroupInfo'
        ];

        $arrFieldCart = DBTableField::setTableField('tableCart', null, $arrExclude['cart'], 'c');
        $arrFieldGoods = DBTableField::setTableField('tableGoods', $arrInclude['goods'], null, 'g');
        $arrFieldOption = DBTableField::setTableField('tableGoodsOption', null, $arrExclude['option'], 'go');
        unset($arrExclude);

        // 장바구니 상품 기본 정보
        $strSQL = "SELECT c.sno,
            " . implode(', ', $arrFieldCart) . ", c.regDt,
            " . implode(', ', $arrFieldGoods) . ",
            " . implode(', ', $arrFieldOption) . "
        FROM " . $this->tableName . " c
        INNER JOIN " . DB_GOODS . " g ON c.goodsNo = g.goodsNo
        LEFT JOIN " . DB_GOODS_OPTION . " go ON c.optionSno = go.sno AND c.goodsNo = go.goodsNo
        WHERE " . implode(' AND ', $arrWhere) . "
        ORDER BY " . $strOrder;

        $query = $this->db->getBindingQueryString($strSQL, $arrBind);
        $result = $this->db->query($query);
        unset($arrWhere, $strOrder);

        while ($data = $this->db->fetch($result)) {
            $returnData[] = $data;
        }

        return $returnData;
    }

    /**
     * 상품쿠폰 최소금액 주문 기준일 때 가격 호출 get
     *
     * @param array $getData 장바구니데이터
     * @param int $couponSno 쿠폰데이터
     * @param string $isFront 사용 메뉴
     * @param int $depth 배열 뎁스
     * @return array 가격
     *
     */
    public function getProductCouponGoodsAllPrice($getData, $couponSno=null, $isFront = 'system', $depth = '1')
    {
        $cartTotalPriceArray = $cartScmTotalPriceArray = [];
        if($depth != '1') {
            foreach($getData as $cartKey => $cartValue) {
                foreach ($cartValue as $cartValueKey => $getValue) {
                    $cartScmTotalPriceArray[] = $this->setProductCouponGoodsAllPrice($getValue, $couponSno, $isFront);
                }
            }
            foreach($cartScmTotalPriceArray as $priceKey => $priceData) {
                // 상품 총 판매가격
                $cartTotalPriceArray['goodsPriceSum'] += $priceData['goodsPriceSum'];
                // 상품 총 옵션가격
                $cartTotalPriceArray['optionPriceSum'] += $priceData['optionPriceSum'];
                // 상품 총 텍스트 옵션 가격
                $cartTotalPriceArray['optionTextPriceSum'] += $priceData['optionTextPriceSum'];
                // 상품 총 추가 상품 가격
                $cartTotalPriceArray['addGoodsPriceSum'] += $priceData['addGoodsPriceSum'];
            }
            return $cartTotalPriceArray;
        } else {
            $cartTotalPriceArray = $this->setProductCouponGoodsAllPrice($getData, $couponSno, $isFront);
            return $cartTotalPriceArray;
        }
    }

    /**
     * 상품쿠폰 최소금액 주문 기준일 때 가격 호출 Set (추가상품 텍트스옵션 계산 시 포함)
     *
     * @param array $getData 장바구니데이터
     * @param int $couponSno 쿠폰데이터
     * @param string $isFront 사용 메뉴
     * @return array 가격
     *
     */
    public function setProductCouponGoodsAllPrice($getData, $couponSno=null, $isFront = 'system')
    {
        $cartTotalPriceArray = [];
        if($couponSno) {
            $coupon = \App::load('\\Component\\Coupon\\Coupon');
            $memberCouponData = $coupon->getMemberCouponInfo($couponSno, 'c.*');
        }
        $goods = \App::load('\\Component\\Goods\\Goods');
        foreach ($getData as $dataKey => $dataVal) {
            $arrTmp = [];
            //가격 대체 문구가 있는경우 합에서 제외 해야 함
            if(empty($dataVal['goodsPriceString']) === false) {
                $getData[$dataKey]['price']['goodsPriceSum'] = 0;
                $getData[$dataKey]['price']['optionPriceSum'] = 0;
                $getData[$dataKey]['price']['optionTextPriceSum'] = 0;
                $getData[$dataKey]['price']['addGoodsPriceSum'] = 0;
            }
            if($dataVal['goodsCouponOriginGoodsCnt']) {
                $dataVal['goodsCnt'] = $dataVal['goodsCouponOriginGoodsCnt'];
            }

            // 혜택제외 체크 (쿠폰)
            $exceptBenefit = explode(STR_DIVISION, $dataVal['exceptBenefit']);
            $exceptBenefitGroupInfo = explode(INT_DIVISION, $dataVal['exceptBenefitGroupInfo']);
            if(in_array('coupon', $exceptBenefit) === false || ($dataVal['exceptBenefitGroup'] == 'group' && in_array($this->_memInfo['groupSno'], $exceptBenefitGroupInfo) === false)) {

                // 제외 공급사
                if($memberCouponData['couponExceptProviderType'] == 'y') {
                    $exceptProviderGroupArr = explode(INT_DIVISION, $memberCouponData['couponExceptProvider']);
                    if (in_array($dataVal['scmNo'], $exceptProviderGroupArr) == true) {//공급사 존재
                        continue;
                    }
                }
                // 제외 카테고리
                if($memberCouponData['couponExceptCategoryType'] == 'y') {
                    $cateArr = $goods->getGoodsLinkCategory($dataVal['goodsNo']);
                    $cateCdArr = [];
                    if (is_array($cateArr)) {
                        $cateCdArr = array_column($cateArr, 'cateCd');
                    }
                    $exceptCategoryGroupArr = explode(INT_DIVISION, $memberCouponData['couponExceptCategory']);
                    $matchCateData = 0;
                    foreach ($cateCdArr as $cateKey => $cateVal) {
                        if(in_array($cateVal, $exceptCategoryGroupArr) == true) {//카테고리 존재
                            $matchCateData++;
                        }
                    }
                    if($matchCateData > 0) {// 상품의 적용된 카테고리와 쿠폰 허용 카테고리 존재
                        continue;
                    }
                }
                // 제외 브랜드
                if($memberCouponData['couponExceptBrandType'] == 'y') {
                    $exceptBrandGroupArr = explode(INT_DIVISION, $memberCouponData['couponExceptBrand']);
                    if(in_array($dataVal['brandCd'], $exceptBrandGroupArr) == true) {//브랜드 존재
                        continue;
                    }
                }
                // 제외 상품
                if($memberCouponData['couponExceptGoodsType'] == 'y') {
                    $exceptGoodsGroupArr = explode(INT_DIVISION, $memberCouponData['couponExceptGoods']);
                    if (in_array($dataVal['goodsNo'], $exceptGoodsGroupArr) == true) {//상품 존재
                        continue;
                    }
                }

                /* 타임 세일 관련 */
                if(gd_is_plus_shop(PLUSSHOP_CODE_TIMESALE) === true) {
                    if($dataVal['timeSaleFl'] == 'y') {
                        $strScmSQL = 'SELECT ts.couponFl as timeSaleCouponFl,ts.sno as timeSaleSno,ts.goodsNo as goodsNo FROM ' . DB_TIME_SALE . ' as ts WHERE FIND_IN_SET(' . $dataVal['goodsNo'] . ', REPLACE(ts.goodsNo,"' . INT_DIVISION . '",",")) AND UNIX_TIMESTAMP(ts.startDt) < UNIX_TIMESTAMP() AND  UNIX_TIMESTAMP(ts.endDt) > UNIX_TIMESTAMP() AND ts.pcDisplayFl="y"';
                        $tmpScmData = $this->db->query_fetch($strScmSQL, null, false);
                        if ($tmpScmData) {
                            if ($tmpScmData['timeSaleCouponFl'] == 'n') {
                                continue;
                            }
                        }
                        unset($tmpScmData);
                        unset($strScmSQL);
                    }
                }

                // 상품데이터를 이용해 상품번호, 배송번호, 추가상품, 텍스트옵션, 회원쿠폰번호 별도 추출
                foreach (ArrayUtils::removeEmpty(array_column($getData, 'addGoodsNo')) as $val) {
                    foreach ($val as $cKey => $cVal) {
                        $arrTmp['addGoodsNo'][] = $cVal;
                    }
                }
                foreach (ArrayUtils::removeEmpty(array_column($getData, 'optionTextSno')) as $key => $val) {
                    foreach ($val as $cKey => $cVal) {
                        $arrTmp['optionText'][] = $cVal;
                    }
                }

                // 추가 상품 디비 정보
                $getAddGoods = $this->getAddGoodsInfo($arrTmp['addGoodsNo']);
                // 텍스트 옵션 디비 정보
                $getOptionText = $this->getOptionTextInfo($arrTmp['optionText']);

                // 추가 상품 정보
                $getData[$dataKey]['addGoods'] = [];
                if ($dataVal['addGoodsFl'] === 'y' && empty($dataVal['addGoodsNo']) === false) {
                    foreach ($dataVal['addGoodsNo'] as $key => $val) {
                        $tmp = $getAddGoods[$val];

                        //$this->cartAddGoodsCnt += $dataVal['addGoodsCnt'][$key];

                        // 추가상품 기본 정보
                        $getData[$dataKey]['addGoods'][$key]['scmNo'] = $tmp['scmNo'];
                        $getData[$dataKey]['addGoods'][$key]['purchaseNo'] = $tmp['purchaseNo'];
                        $getData[$dataKey]['addGoods'][$key]['commission'] = $tmp['commission'];
                        $getData[$dataKey]['addGoods'][$key]['goodsCd'] = $tmp['goodsCd'];
                        $getData[$dataKey]['addGoods'][$key]['goodsModelNo'] = $tmp['goodsModelNo'];
                        $getData[$dataKey]['addGoods'][$key]['optionNm'] = $tmp['optionNm'];
                        $getData[$dataKey]['addGoods'][$key]['brandCd'] = $tmp['brandCd'];
                        $getData[$dataKey]['addGoods'][$key]['makerNm'] = $tmp['makerNm'];
                        $getData[$dataKey]['addGoods'][$key]['stockUseFl'] = $tmp['stockUseFl'] == '1' ? 'y' : 'n';
                        $getData[$dataKey]['addGoods'][$key]['stockCnt'] = $tmp['stockCnt'];
                        $getData[$dataKey]['addGoods'][$key]['viewFl'] = $tmp['viewFl'];
                        $getData[$dataKey]['addGoods'][$key]['soldOutFl'] = $tmp['soldOutFl'];

                        // 과세/비과세 설정에 따른 금액 계산
                        $getData[$dataKey]['addGoods'][$key]['addGoodsNo'] = $val;
                        $getData[$dataKey]['addGoods'][$key]['addGoodsNm'] = $tmp['goodsNm'];
                        $getData[$dataKey]['addGoods'][$key]['addGoodsNmStandard'] = $tmp['goodsNmStandard'];
                        $getData[$dataKey]['addGoods'][$key]['addGoodsPrice'] = $tmp['goodsPrice'];
                        $getData[$dataKey]['addGoods'][$key]['addCostGoodsPrice'] = $tmp['costPrice'];
                        $getData[$dataKey]['addGoods'][$key]['addGoodsCnt'] = $dataVal['addGoodsCnt'][$key];
                        $getData[$dataKey]['addGoods'][$key]['taxFreeFl'] = $tmp['taxFreeFl'];
                        $getData[$dataKey]['addGoods'][$key]['taxPercent'] = $tmp['taxPercent'];
                        $getData[$dataKey]['addGoods'][$key]['addGoodsImage'] = $tmp['addGoodsImage'];

                        foreach ($getData[$dataKey]['addGoodsMustFl'] as $aVal) {
                            if (in_array($val, $aVal['addGoods']) === true) {
                                $getData[$dataKey]['addGoods'][$key]['addGoodsMustFl'] = $aVal['mustFl'];
                            }
                        }

                        // 단가계산용 추가 상품 금액
                        $goodsPriceInfo[$dataVal['goodsNo']]['addGoodsCnt'][$dataKey][$key] = $dataVal['addGoodsCnt'][$key];
                        $goodsPriceInfo[$dataVal['goodsNo']]['addGoodsPrice'][$dataKey][$key] = $tmp['goodsPrice'];

                        // 추가상품 재고체크 처리 후 재고 없으면 구매불가 처리
                        if ($tmp['soldOutFl'] === 'y' || ($tmp['soldOutFl'] === 'n' && $tmp['stockUseFl'] === '1' && ($tmp['stockCnt'] == 0 || $tmp['stockCnt'] - $dataVal['addGoodsCnt'][$key] < 0))) {
                            $getData[$dataKey]['orderPossible'] = 'n';
                            $getData[$dataKey]['orderPossibleCode'] = self::POSSIBLE_SOLD_OUT;
                            $getData[$dataKey]['orderPossibleMessage'] = $getData[$dataKey]['orderPossibleMessageList'][] = __('추가상품 재고부족');
                            $this->orderPossible = false;
                        }

                        $getData[$dataKey]['orderPossibleMessageList'] = array_unique($getData[$dataKey]['orderPossibleMessageList']);
                        //추가상품과세율에 따른 세금계산서 출력여부 선택
                        if ((int)$tmp['taxPercent'] != '10' && (int)$tmp['taxPercent'] != '0') {
                            $this->taxGoodsChk = false;
                        }

                        // 추가 상품 순수 개별 부가세 계산 (할인 적용 안됨)
                        $getData[$dataKey]['addGoods'][$key]['addGoodsVat'] = NumberUtils::taxAll($tmp['goodsPrice'] * $dataVal['addGoodsCnt'][$key], $tmp['taxPercent'], $tmp['taxFreeFl']);

                        // 추가 상품 총 금액
                        $getData[$dataKey]['price']['addGoodsPriceSum'] += ($tmp['goodsPrice'] * $dataVal['addGoodsCnt'][$key]);

                        // 추가 상품 개별 부가세 계산
                        $getData[$dataKey]['price']['addGoodsVat']['supply'] += $getData[$dataKey]['addGoods'][$key]['addGoodsVat']['supply'];
                        $getData[$dataKey]['price']['addGoodsVat']['tax'] += $getData[$dataKey]['addGoods'][$key]['addGoodsVat']['tax'];

                        unset($tmp);
                    }
                }
                unset($getData[$dataKey]['addGoodsNo']);
                unset($getData[$dataKey]['addGoodsCnt']);
                // 텍스트 옵션
                $getData[$dataKey]['optionText'] = [];
                foreach ($dataVal['optionTextSno'] as $key => $val) {
                    $tmp = $getOptionText[$val];

                    // 텍스트 옵션 기본 금액 합계
                    $getData[$dataKey]['price']['baseOptionTextPrice'] += $tmp['baseOptionTextPrice'];

                    // 과세/비과세 설정에 따른 금액 계산
                    $tmp['optionTextPrice'] = $tmp['baseOptionTextPrice'];
                    $getData[$dataKey]['price']['optionTextPrice'] += $tmp['optionTextPrice'];
                    $tmp['optionValue'] = $dataVal['optionTextStr'][$val];
                    $getData[$dataKey]['optionText'][$key] = $tmp;

                    // 텍스트 옵션 총 금액
                    $getData[$dataKey]['price']['optionTextPriceSum'] += ($tmp['optionTextPrice'] * $dataVal['goodsCnt']);
                    unset($tmp);
                }
                unset($getData[$dataKey]['optionTextSno']);
                unset($getData[$dataKey]['optionTextStr']);


                if($isFront == 'front') {
                    $getData[$dataKey]['price']['goodsPriceSum'] = $getData[$dataKey]['price']['goodsPrice'] * $dataVal['goodsCnt'];
                    $getData[$dataKey]['price']['optionPriceSum'] = $getData[$dataKey]['price']['optionPrice'] * $dataVal['goodsCnt'];
                } else {
                    $getData[$dataKey]['price']['goodsPriceSum'] = $getData[$dataKey]['goodsPrice'] * $dataVal['goodsCnt'];
                    $getData[$dataKey]['price']['optionPriceSum'] = $getData[$dataKey]['optionPrice'] * $dataVal['goodsCnt'];
                }

                $getData[$dataKey]['price']['addGoodsVat']['supply'] = 0;
                $getData[$dataKey]['price']['addGoodsVat']['tax'] = 0;
                $getData[$dataKey]['price']['goodsDcPrice'] = 0;
                $getData[$dataKey]['price']['memberDcPrice'] = 0;
                $getData[$dataKey]['price']['memberOverlapDcPrice'] = 0;
                $getData[$dataKey]['price']['couponGoodsDcPrice'] = 0;
                $getData[$dataKey]['price']['goodsDeliveryPrice'] = 0;
                unset($getData[$dataKey]['fixedPrice'], $getData[$dataKey]['costPrice'], $getData[$dataKey]['goodsPrice'], $getData[$dataKey]['optionPrice']);

                // 상품별 가격 (상품 가격 + 옵션 가격 + 텍스트 옵션 가격 + 추가 상품 가격)

                $cartTotalPriceArray['goodsPriceSubtotal'] = $getData[$dataKey]['price']['goodsPriceSum'] + $getData[$dataKey]['price']['optionPriceSum'] + $getData[$dataKey]['price']['optionTextPriceSum'] + $getData[$dataKey]['price']['addGoodsPriceSum'];
                // 상품 총 판매가격
                $cartTotalPriceArray['goodsPriceSum'] += $getData[$dataKey]['price']['goodsPriceSum'];
                // 상품 총 옵션가격
                $cartTotalPriceArray['optionPriceSum'] += $getData[$dataKey]['price']['optionPriceSum'];
                // 상품 총 텍스트 옵션 가격
                $cartTotalPriceArray['optionTextPriceSum'] += $getData[$dataKey]['price']['optionTextPriceSum'];
                // 상품 총 추가 상품 가격
                $cartTotalPriceArray['addGoodsPriceSum'] += $getData[$dataKey]['price']['addGoodsPriceSum'];

                // 상품 총 갯수
                $cartTotalPriceArray['goodsCnt'] = $dataVal['goodsCnt'];
            }
        }
        return $cartTotalPriceArray;
    }

    /**
     * 장바구니 상품 정보 - 상품별 상품 할인 설정 - 상품쿠폰 Max 값 계산을 위해 상품할인 계산
     * 상품금액의 총합이 아닌 순수 상품판매 단가의 할인율을 먼저 구한 뒤 반환할때 상품수량 곱함
     *
     * @param array $goodsData 상품정보
     * @param int $goodsCnt 상품 수량
     * @param array $goodsPrice 상품 가격 정보
     * @param string $fixedGoodsDiscount 상품할인금액기준
     * @param string $goodsDiscountGroup 상품할인대상
     * @param json $goodsDiscountGroupMemberInfo 상품할인 회원 정보
     *
     * @return int 상품할인금액
     */
    public function setProductCouponGoodsDcData($goodsData, $goodsCnt, $goodsPrice, $fixedGoodsDiscount = null, $goodsDiscountGroup = null, $goodsDiscountGroupMemberInfo = null)
    {
        //상품 혜택 모듈
        $goodsBenefit = \App::load('\\Component\\Goods\\GoodsBenefit');
        $goodsData = $goodsBenefit->goodsDataFrontConvert($goodsData);

        $goodsDiscountFl = $goodsData['goodsDiscountFl'];
        $goodsDiscount = $goodsData['goodsDiscount'];
        $goodsDiscountUnit = $goodsData['goodsDiscountUnit'];
        unset($goodsData);
        // 상품 할인 금액
        $goodsDcPrice = $goodsPriceTmp = 0;
        $fixedGoodsDiscountData = explode(STR_DIVISION, $fixedGoodsDiscount);
        $goodsPriceTmp = $goodsPrice['goodsPriceSum'];

        if (in_array('option', $fixedGoodsDiscountData) === true) $goodsPriceTmp +=$goodsPrice['optionPriceSum'];
        if (in_array('text', $fixedGoodsDiscountData) === true) $goodsPriceTmp +=$goodsPrice['optionTextPriceSum'];

        // 상품금액 단가 계산
        $goodsPrice['goodsPrice'] = ($goodsPriceTmp / $goodsCnt);

        // 상품 할인 기준 금액 처리
        $tmp['discountByPrice'] = $goodsPrice['goodsPrice'];

        // 절사 내용
        $tmp['trunc'] = Globals::get('gTrunc.goods');

        // 상품 할인을 사용하는 경우 상품 할인 계산
        if ($goodsDiscountFl === 'y') {
            switch ($goodsDiscountGroup) {
                case 'group':
                    $goodsDiscountGroupMemberInfoData = json_decode($goodsDiscountGroupMemberInfo, true);
                    $discountKey = array_flip($goodsDiscountGroupMemberInfoData['groupSno'])[$this->_memInfo['groupSno']];

                    if ($discountKey >= 0) {
                        if ($goodsDiscountGroupMemberInfoData['goodsDiscountUnit'][$discountKey] === 'percent') {
                            $discountPercent = $goodsDiscountGroupMemberInfoData['goodsDiscount'][$discountKey] / 100;

                            // 상품할인금액
                            $goodsDcPrice = gd_number_figure($tmp['discountByPrice'] * $discountPercent, $tmp['trunc']['unitPrecision'], $tmp['trunc']['unitRound']) * $goodsCnt;
                        } else {
                            // 상품금액보다 상품할인금액이 클 경우 상품금액으로 변경
                            if ($goodsDiscountGroupMemberInfoData['goodsDiscount'][$discountKey] > $goodsPrice['goodsPrice']) $goodsDiscountGroupMemberInfoData['goodsDiscount'][$discountKey] = $goodsPrice['goodsPrice'];
                            // 상품할인금액 (정액인 경우 해당 설정된 금액으로)
                            $goodsDcPrice = $goodsDiscountGroupMemberInfoData['goodsDiscount'][$discountKey] * $goodsCnt;
                        }
                    }
                    break;
                case 'member':
                default:
                    if ($goodsDiscountUnit === 'percent') {
                        $discountPercent = $goodsDiscount / 100;

                        // 상품할인금액
                        $goodsDcPrice = gd_number_figure($tmp['discountByPrice'] * $discountPercent, $tmp['trunc']['unitPrecision'], $tmp['trunc']['unitRound']) * $goodsCnt;
                    } else {
                        // 상품금액보다 상품할인금액이 클 경우 상품금액으로 변경
                        if ($goodsDiscount > $goodsPrice['goodsPrice']) $goodsDiscount = $goodsPrice['goodsPrice'];
                        // 상품할인금액 (정액인 경우 해당 설정된 금액으로)
                        $goodsDcPrice = $goodsDiscount * $goodsCnt;
                    }
                    if ($goodsDiscountGroup == 'member' && empty($this->members['memNo']) === true) {
                        $goodsDcPrice = 0;
                    }
                    break;
            }
        }

        return $goodsDcPrice;
    }

    /**
     * 장바구니 데이터 조회
     *
     * @param array  $extra     바인딩 데이터
     * @param mixed  $arrField  장바구니 필드
     *
     * @return mixed
     * @throws
     */
    public function getCartDataByExtraData($extra = null, $arrField = '*')
    {
        $arrBind = $arrWhere = $arrJoin = [];
        // 장바구니 번호
        if ($extra['cartSno']) {
            if (is_array($extra['cartSno'])) {
                $tmpAddWhere = [];
                foreach ($extra['cartSno'] as $val) {
                    $tmpAddWhere[] = '?';
                    $this->db->bind_param_push($arrBind, 'i', $val);
                }
                $arrWhere[] = 'c.sno IN (' . implode(' , ', $tmpAddWhere) . ')';
                unset($tmpAddWhere);
            } else {
                $arrWhere[] = 'c.sno = ?';
                $this->db->bind_param_push($arrBind, 'i', $extra['cartSno']);
            }
        }
        // 주문번호
        if ($extra['tmpOrderNo']) {
            $arrWhere[] = 'c.tmpOrderNo = ?';
            $this->db->bind_param_push($arrBind, 's', $extra['tmpOrderNo']);
        }
        // 기준 등록일 이전 데이터
        if ($extra['previousRegDt']) {
            $arrWhere[] = 'c.regDt < ?';
            $this->db->bind_param_push($arrBind, 's', $extra['previousRegDt']);
        }
        // 등록일 제외
        if ($extra['exceptRegDt']) {
            $arrWhere[] = "DATE_FORMAT(c.regDt, '%Y-%m-%d') != ?";
            $this->db->bind_param_push($arrBind, 's', $extra['exceptRegDt']);
        }
        // 등록일
        if ($extra['regDt']) {
            $arrWhere[] = 'c.regDt BETWEEN ? AND ?';
            $this->db->bind_param_push($arrBind, 's', $extra['regDt'] . ' 00:00:00');
            $this->db->bind_param_push($arrBind, 's', $extra['regDt'] . ' 23:59:59');
        }
        // 멤버 제외
        if (is_null($extra['exceptMemNo']) === false) {
            $arrWhere[] = "c.memNo != ?";
            $this->db->bind_param_push($arrBind, 'i', $extra['exceptMemNo']);
        }
        // 회원 테이블 조인
        if ($extra['memberJoinFl']) {
            $arrJoin[] = ' LEFT JOIN ' . DB_MEMBER . ' AS m ON c.memNo = m.memNo';
        }

        $this->db->strField = implode(',', $arrField);
        $this->db->strWhere = implode(' and ', $arrWhere);
        $this->db->strJoin = implode('', $arrJoin);
        $query = $this->db->query_complete();
        $strSQL = 'SELECT ' . array_shift($query) . ' FROM ' . $this->tableName . ' AS c ' . implode(' ', $query);
        $getData = $this->db->query_fetch($strSQL, $arrBind);

        return $getData;
    }

    /**
     * 장바구니 상품 정보 - 상품/추가상품 마일리지 금액 (회원등급 추가 무통장 할인에서 사용)
     *
     * @param string $mileageFl        마일리지 설정 종류
     * @param int    $mileageGoods     마일리지 금액 or Percent
     * @param string $mileageGoodsUnit Percent or Price
     * @param int    $goodsCnt         상품 수량
     * @param array  $goodsPrice       상품 가격 정보
     * @param string $mileageGroup       마일리지 지급 대상
     * @param string $mileageGroupInfo       마일리지 지급 대상 회원그룹
     * @param json   $mileageGroupMemberInfo       마일리지 지급 대상 회원 정보
     *
     * @return int 상품 마일리지 금액
     */
    public function getGoodsMileageDataCont($mileageFl, $mileageGoods, $mileageGoodsUnit, $goodsCnt, $goodsPrice, $mileageGroup = null, $mileageGroupInfo = null, $mileageGroupMemberInfo = null)
    {
        $goodsMileage = $this->getGoodsMileageData($mileageFl, $mileageGoods, $mileageGoodsUnit, $goodsCnt, $goodsPrice, $mileageGroup, $mileageGroupInfo, $mileageGroupMemberInfo);

        return $goodsMileage;
    }

    /**
     * 장바구니 옵션 재고 확인 (텍스트옵션이 있을 경우 같은옵션이지만 분리되어 각각 따로 재고 체크 -> 합쳐서 확인해야함)
     *
     * @param $cartSno string
     */
    public function cartSelectStock($cartSno)
    {
        $arrBind = [];
        $cartSno = explode(',', $cartSno);
        if (is_array($cartSno)) {
            $tmpAddWhere = [];
            foreach ($cartSno as $val) {
                $tmpAddWhere[] = '?';
                $this->db->bind_param_push($arrBind, 'i', $val);
            }
            $arrWhere[] = 'c.sno IN (' . implode(' , ', $tmpAddWhere) . ')';
            unset($tmpAddWhere);
        } else {
            $arrWhere[] = 'c.sno = ?';
            $this->db->bind_param_push($arrBind, 'i', $cartSno);
        }

        $arrWhere[] = 'g.stockFl = \'y\'';
        $arrJoin[] = ' LEFT JOIN ' . DB_GOODS . ' AS g ON g.goodsNo = c.goodsNo';
        $arrJoin[] = ' LEFT JOIN ' . DB_GOODS_OPTION . ' AS go ON go.sno = c.optionSno';
        $this->db->strField = 'c.optionSno, c.goodsCnt, go.stockCnt';
        $this->db->strWhere = implode(' and ', $arrWhere);
        $this->db->strJoin = implode('', $arrJoin);
        $query = $this->db->query_complete();
        $strSQL = 'SELECT ' . array_shift($query) . ' FROM ' . $this->tableName . ' as c '. implode(' ', $query);
        $getData = $this->db->query_fetch($strSQL, $arrBind);

        if($getData) {
            $sno = [];
            $ret = false;
            foreach($getData as $val) {
                if($sno[$val['optionSno']]) {
                    $sno[$val['optionSno']] += $val['goodsCnt'];
                } else {
                    $sno[$val['optionSno']] = $val['goodsCnt'];
                }
                if($sno[$val['optionSno']] > $val['stockCnt']) {
                    $ret = $val['stockCnt'];
                    break;
                }
            }
            echo $ret;
        } else {
            echo false;
        }
    }

    /**
     * validateApplyCoupon
     * 장바구니 쿠폰 중복 체크 (참고 - Coupon.php > getGoodsMemberCouponList())
     *
     */
    public function validateApplyCoupon($couponApplyNoList) {
        try {
            //수기 주문의 경우 체크하지 않음
            if($this->isWrite === true && $this->isWriteMemberCartAdd === true) {
                return ['status' => true, 'msg' => ''];
            }
            // 비회원의 경우 체크하지 않음
            if (gd_is_login() === false && MemberUtil::checkLogin() == 'guest') {
                return ['status' => true, 'msg' => ''];
            }
            if (empty($couponApplyNoList) === true) {
                return ['status' => true, 'msg' => ''];
            }
            if (is_array($couponApplyNoList) === false) {
                $couponApplyNoList = [$couponApplyNoList];
            }
            $dateYmd = date('Y-m-d H:i:s');
            $fieldTypes['memberCoupon'] = DBTableField::getFieldTypes('tableMemberCoupon');
            foreach ($couponApplyNoList as $memCouponNoList) {
                $memCouponNoArr = explode(INT_DIVISION, $memCouponNoList);
                if (empty($memCouponNoArr) === true) { continue; }
                foreach ($memCouponNoArr as $memCouponNo) {
                    if (empty($memCouponNo) === true) { continue; }
                    $arrBind    = [];
                    $arrWhere   = [];
                    $arrWhere[] = 'mc.memberCouponNo = ?';
                    $this->db->bind_param_push($arrBind, gd_isset($fieldTypes['memberCoupon']['memberCouponNo'], 's'), $memCouponNo);
                    // 회원쿠폰 만료기간
                    $arrWhere[] = '(mc.memberCouponStartDate <= ? AND mc.memberCouponEndDate > ?)';
                    $this->db->bind_param_push($arrBind, gd_isset($fieldTypes['memberCoupon']['memberCouponStartDate'], 's'), $dateYmd);
                    $this->db->bind_param_push($arrBind, gd_isset($fieldTypes['memberCoupon']['memberCouponEndDate'], 's'), $dateYmd);
                    $strSQL = 'SELECT memberCouponNo, memberCouponState FROM ' . DB_MEMBER_COUPON . ' as mc WHERE ' . implode(' AND ', $arrWhere);
                    $memCouponInfo = $this->db->query_fetch($strSQL, $arrBind, false);
                    if (empty($memCouponInfo) === true) {
                        return ['status' => false, 'msg' => __('사용불가한 쿠폰이 적용되어있습니다.')];
                    }
                    // 회원쿠폰 사용 가능 상태(y) 체크 --> 수기주문의 경우 주문 저장 시 중복 체크하므로 체크 불필요.
                    if ($memCouponInfo['memberCouponState'] !== 'y') {
                        return ['status' => false, 'msg' => __('이미 사용중인 쿠폰이 적용되어 있습니다.')];
                    }
                }
            }
            return ['status' => true, 'msg' => ''];
        } catch (Exception $e) {
            return ['status' => true, 'msg' => ''];
        }
    }

    /**
     * 회원 그룹별 혜택 계산
     * 기존 회원 그룹별 혜택 계산의 구조가 잘못되어 다시 계산하도록 수정 함
     *
     * @param $orderData array 주문정보
     * @param $orderCoupon array 주문 쿠폰 정보
     * @return $arrData
     */

    private function getMemberGroupBenefit($orderData, $orderCoupon) {
        //회원 로그인 안했으면 넘겨받은 값 그대로 리턴
        if (!$this->isLogin) {
            return $orderData;
        }

        $orderData = $this->resetMemberGroupBenefit($orderData); //회원 등급 혜택 리셋

        //쿠폰 설정 중 쿠폰/회원혜택 중복적용 여부 설정 가져오기
        $couponConfig = gd_policy('coupon.config');
        if ($couponConfig['chooseCouponMemberUseType'] == 'coupon') {
            //쿠폰만 사용인 경우 주문 쿠폰을 사용 하였는지 확인
            if (!empty($orderCoupon)) {
                //주문 쿠폰을 사용 할 경우, 회원 그룹별 혜택 사용하지 않음
                return $orderData;
            }
            //상품 쿠폰을 사용 한 경우, 상품 쿠폰을 사용 한 상품의 장바구니 일련번호 저장
            foreach ($orderData as $orderDataKey => $orderDataValue) {
                if (!empty($orderDataValue['coupon'])) {
                    $goodsCouponGoods[] = $orderDataValue['sno']; //쿠폰 사용한 상품 목록
                }
            }
        }

        //타임세일 설정 가져오기
        $timeSale = \App::load('\\Component\\Promotion\\TimeSale');

        //주문하는 상품을 기준으로 반복
        foreach ($orderData as $orderDataKey => $orderDataValue) {
            //쿠폰 설정 중 쿠폰/회원혜택 중복적용이 쿠폰만 사용이며, 상품 쿠폰을 사용 한경우 할인 하지 않음
            if (in_array($orderDataValue['sno'], $goodsCouponGoods)) continue;

            //타임세일 설정 중 회원등급 혜택 적용 여부 설정
            $timeSaleInfo = $timeSale->getGoodsTimeSale($orderDataValue['goodsNo']);
            if ($timeSaleInfo['memberDcFl'] == 'n') continue;

            //구매금액 기준으로 잡을 항목(옵션가, 추가상품가, 텍스트옵션가)
            $basePrice = $orderDataValue['price']['goodsPriceSum']; //상품가
            if (in_array('option',  $this->_memInfo['fixedRateOption'])) $basePrice += $orderDataValue['price']['optionPriceSum']; //상품가
            if (in_array('goods',   $this->_memInfo['fixedRateOption'])) $basePrice += $orderDataValue['price']['addGoodsPriceSum']; //추가상품가
            if (in_array('text',    $this->_memInfo['fixedRateOption'])) $basePrice += $orderDataValue['price']['optionTextPriceSum']; //텍스트옵션가

            //할인시 절사기준 가져오기
            $memberTruncPolicy = Globals::get('gTrunc.member_group');

            //할인/적립 시 적용 금액 기준 (판매금액, 결제금액)
            if ($this->_memInfo['fixedRatePrice'] == 'settle') {
                //결제금액일 경우, 기준 금액에서 상품쿠폰 할인금액, 주문쿠폰 할인 금액, 상품 할인 금액을 빼준다.
                foreach ($orderDataValue['coupon'] as $orderDataValueCouponValue) {
                    $basePrice -= $orderDataValueCouponValue['couponGoodsDcPrice']; //상품쿠폰 할인금액
                }

                $basePrice -= $orderCoupon[$orderDataValue['goodsNo']]['divisionOrderCoupon'][$orderDataKey]; //상품쿠폰 할인금액
                //추가상품이 있는지 확인
                if (!empty($orderDataValue['addGoods'])) {
                    $basePrice -= $orderCoupon[$orderDataValue['goodsNo']]['divisionOrderCouponByAddGoods'][$orderDataKey]; //상품쿠폰(추가상품) 할인금액
                }

                $basePrice -= $orderDataValue['price']['goodsDcPrice']; //상품 할인금액
            }

            //기준 금액 정리
            $basePriceArr['option'][$orderDataValue['sno']] = $basePrice; //옵션별 임시 저장
            $basePriceArr['goods'][$orderDataValue['goodsNo']] += $basePrice; //상품별 임시 저장
            $basePriceArr['order'] += $basePrice; //주문별 임시 저장
            $basePriceArr['brand'][$orderDataValue['brandCd']] += $basePrice - $orderDataValue['price']['addGoodsPriceSum']; //브랜드별 임시 저장
            if ($orderDataValue['price']['addGoodsPriceSum'] > 0) {
                $basePriceArr['brand'][''] += $orderDataValue['price']['addGoodsPriceSum']; //브랜드(추가상품) 임시 저장
            }

            //추가 할인 적용 제외할 상품 목록 만들기
            {
                //특정 공급사
                if (in_array('scm', $this->_memInfo['dcExOption'])) {
                    if (in_array($orderDataValue['scmNo'], $this->_memInfo['dcExScm'])) {
                        $exceptGoodsDc[$orderDataValue['sno']]['price'] = $basePrice;
                        $exceptGoodsDc[$orderDataValue['sno']]['goodsNo'] = $orderDataValue['goodsNo'];
                        $exceptGoodsDc[$orderDataValue['sno']]['cateAllCd'] = $orderDataValue['cateAllCd'];
                        $exceptGoodsDc[$orderDataValue['sno']]['brandCd'] = $orderDataValue['brandCd'];
                    }
                }
                //특정 카테고리
                if (in_array('category', $this->_memInfo['dcExOption'])) {
                    foreach ($orderDataValue['cateAllCd'] as $orderDataValueCateAllCdValue) {
                        if (in_array($orderDataValueCateAllCdValue['cateCd'], $this->_memInfo['dcExCategory'])) {
                            $exceptGoodsDc[$orderDataValue['sno']]['price'] = $basePrice;
                            $exceptGoodsDc[$orderDataValue['sno']]['goodsNo'] = $orderDataValue['goodsNo'];
                            $exceptGoodsDc[$orderDataValue['sno']]['cateAllCd'] = $orderDataValue['cateAllCd'];
                            $exceptGoodsDc[$orderDataValue['sno']]['brandCd'] = $orderDataValue['brandCd'];
                        }
                    }
                }
                //특정 브랜드
                if (in_array('brand', $this->_memInfo['dcExOption'])) {
                    if (in_array($orderDataValue['brandCd'], $this->_memInfo['dcExBrand'])) {
                        $exceptGoodsDc[$orderDataValue['sno']]['price'] = $basePrice;
                        $exceptGoodsDc[$orderDataValue['sno']]['goodsNo'] = $orderDataValue['goodsNo'];
                        $exceptGoodsDc[$orderDataValue['sno']]['cateAllCd'] = $orderDataValue['cateAllCd'];
                        $exceptGoodsDc[$orderDataValue['sno']]['brandCd'] = $orderDataValue['brandCd'];
                    }
                }
                //특정 상품
                if (in_array('goods', $this->_memInfo['dcExOption'])) {
                    if (in_array($orderDataValue['goodsNo'], $this->_memInfo['dcExGoods'])) {
                        $exceptGoodsDc[$orderDataValue['sno']]['price'] = $basePrice;
                        $exceptGoodsDc[$orderDataValue['sno']]['goodsNo'] = $orderDataValue['goodsNo'];
                        $exceptGoodsDc[$orderDataValue['sno']]['cateAllCd'] = $orderDataValue['cateAllCd'];
                        $exceptGoodsDc[$orderDataValue['sno']]['brandCd'] = $orderDataValue['brandCd'];
                    }
                }
                //상품 개별 설정에서 제외 설정 시
                //상품별 회원 할인 혜택 제외 설정 가져오기
                $tmpExceptBenefit[$orderDataKey] = explode(STR_DIVISION, $orderDataValue['exceptBenefit']);
                //상품별 그룹 혜택 적용 제외 여부
                if (in_array('add', $tmpExceptBenefit[$orderDataKey]) && empty($exceptGoodsDc[$orderDataValue['sno']])) {
                    if ($orderDataValue['exceptBenefitGroup'] == 'all') {
                        $exceptGoodsDc[$orderDataValue['sno']]['price'] = $basePrice;
                        $exceptGoodsDc[$orderDataValue['sno']]['goodsNo'] = $orderDataValue['goodsNo'];
                        $exceptGoodsDc[$orderDataValue['sno']]['cateAllCd'] = $orderDataValue['cateAllCd'];
                        $exceptGoodsDc[$orderDataValue['sno']]['brandCd'] = $orderDataValue['brandCd'];
                    } else if ($orderDataValue['exceptBenefitGroup'] == 'group') {
                        $tmpExceptBenefitGroupInfo = explode(INT_DIVISION, $orderDataValue['exceptBenefitGroupInfo']);
                        if (in_array($this->members['groupSno'], $tmpExceptBenefitGroupInfo)) {
                            $exceptGoodsDc[$orderDataValue['sno']]['price'] = $basePrice;
                            $exceptGoodsDc[$orderDataValue['sno']]['goodsNo'] = $orderDataValue['goodsNo'];
                            $exceptGoodsDc[$orderDataValue['sno']]['cateAllCd'] = $orderDataValue['cateAllCd'];
                            $exceptGoodsDc[$orderDataValue['sno']]['brandCd'] = $orderDataValue['brandCd'];
                        }
                        unset($tmpExceptBenefitGroupInfo);
                    }
                }
            }

            //중복 할인 적용 할 상품
            {
                //상품 개별 설정에서 제외 설정 시
                //상품별 회원 할인 혜택 제외 설정 가져오기
                $tmpExceptBenefit[$orderDataKey] = explode(STR_DIVISION, $orderDataValue['exceptBenefit']);
                //상품별 그룹 혜택 적용 제외 여부
                $tmpExcept = false;
                if (in_array('overlap', $tmpExceptBenefit[$orderDataKey])) {
                    if ($orderDataValue['exceptBenefitGroup'] == 'all') {
                        $tmpExcept = true;
                    } else if ($orderDataValue['exceptBenefitGroup'] == 'group') {
                        $tmpExceptBenefitGroupInfo = explode(INT_DIVISION, $orderDataValue['exceptBenefitGroupInfo']);
                        if (in_array($this->members['groupSno'], $tmpExceptBenefitGroupInfo)) {
                            $tmpExcept = true;
                        }
                        unset($tmpExceptBenefitGroupInfo);
                    }
                }

                if ($tmpExcept === false) {
                    //특정 공급사
                    if (in_array('scm', $this->_memInfo['overlapDcOption'])) {
                        if (in_array($orderDataValue['scmNo'], $this->_memInfo['overlapDcScm'])) {
                            $overlapDcGoodsDc[$orderDataValue['sno']]['price'] = $basePrice;
                            $overlapDcGoodsDc[$orderDataValue['sno']]['goodsNo'] = $orderDataValue['goodsNo'];
                            $overlapDcGoodsDc[$orderDataValue['sno']]['cateAllCd'] = $orderDataValue['cateAllCd'];
                            $overlapDcGoodsDc[$orderDataValue['sno']]['brandCd'] = $orderDataValue['brandCd'];
                        }
                    }
                    //특정 카테고리
                    if (in_array('category', $this->_memInfo['overlapDcOption'])) {
                        foreach ($orderDataValue['cateAllCd'] as $orderDataValueCateAllCdValue) {
                            if (in_array($orderDataValueCateAllCdValue['cateCd'], $this->_memInfo['overlapDcCategory'])) {
                                $overlapDcGoodsDc[$orderDataValue['sno']]['price'] = $basePrice;
                                $overlapDcGoodsDc[$orderDataValue['sno']]['goodsNo'] = $orderDataValue['goodsNo'];
                                $overlapDcGoodsDc[$orderDataValue['sno']]['cateAllCd'] = $orderDataValue['cateAllCd'];
                                $overlapDcGoodsDc[$orderDataValue['sno']]['brandCd'] = $orderDataValue['brandCd'];
                            }
                        }
                    }
                    //특정 브랜드
                    if (in_array('brand', $this->_memInfo['overlapDcOption'])) {
                        if (in_array($orderDataValue['brandCd'], $this->_memInfo['overlapDcBrand'])) {
                            $overlapDcGoodsDc[$orderDataValue['sno']]['price'] = $basePrice;
                            $overlapDcGoodsDc[$orderDataValue['sno']]['goodsNo'] = $orderDataValue['goodsNo'];
                            $overlapDcGoodsDc[$orderDataValue['sno']]['cateAllCd'] = $orderDataValue['cateAllCd'];
                            $overlapDcGoodsDc[$orderDataValue['sno']]['brandCd'] = $orderDataValue['brandCd'];
                        }
                    }

                    //특정 상품
                    if (in_array('goods', $this->_memInfo['overlapDcOption'])) {
                        if (in_array($orderDataValue['goodsNo'], $this->_memInfo['overlapDcGoods'])) {
                            $overlapDcGoodsDc[$orderDataValue['sno']]['price'] = $basePrice;
                            $overlapDcGoodsDc[$orderDataValue['sno']]['goodsNo'] = $orderDataValue['goodsNo'];
                            $overlapDcGoodsDc[$orderDataValue['sno']]['cateAllCd'] = $orderDataValue['cateAllCd'];
                            $overlapDcGoodsDc[$orderDataValue['sno']]['brandCd'] = $orderDataValue['brandCd'];
                        }
                    }
                }
            }

            //마일리지 적용 제외 상품
            {
                //상품 개별 설정에서 제외 설정 시
                //상품별 회원 할인 혜택 제외 설정 가져오기
                $tmpExceptBenefit[$orderDataKey] = explode(STR_DIVISION, $orderDataValue['exceptBenefit']);
                //상품별 그룹 혜택 적용 제외 여부
                if (in_array('mileage', $tmpExceptBenefit[$orderDataKey])) {
                    if ($orderDataValue['exceptBenefitGroup'] == 'all') {
                        $exceptGoodsMileage[$orderDataValue['sno']]['price'] = $basePrice;
                        $exceptGoodsMileage[$orderDataValue['sno']]['goodsNo'] = $orderDataValue['goodsNo'];
                        $exceptGoodsMileage[$orderDataValue['sno']]['cateAllCd'] = $orderDataValue['cateAllCd'];
                        $exceptGoodsMileage[$orderDataValue['sno']]['brandCd'] = $orderDataValue['brandCd'];
                    } else if ($orderDataValue['exceptBenefitGroup'] == 'group') {
                        $tmpExceptBenefitGroupInfo = explode(INT_DIVISION, $orderDataValue['exceptBenefitGroupInfo']);
                        if (in_array($this->members['groupSno'], $tmpExceptBenefitGroupInfo)) {
                            $exceptGoodsMileage[$orderDataValue['sno']]['price'] = $basePrice;
                            $exceptGoodsMileage[$orderDataValue['sno']]['goodsNo'] = $orderDataValue['goodsNo'];
                            $exceptGoodsMileage[$orderDataValue['sno']]['cateAllCd'] = $orderDataValue['cateAllCd'];
                            $exceptGoodsMileage[$orderDataValue['sno']]['brandCd'] = $orderDataValue['brandCd'];
                        }
                        unset($tmpExceptBenefitGroupInfo);
                    }
                }
            }

            //변수 재설정
            unset($basePrice);
        }

        //추가할인 방법 (옵션별, 상품별, 주문별, 브랜드별)
        //기준 금액 추가할인으로 재 계산
        $addDcBasePriceArr = $basePriceArr;
        foreach ($exceptGoodsDc as $exceptGoodsDcKey => $exceptGoodsDcValue) {
            //옵션별 금액 보정
            $addDcBasePriceArr['option'][$exceptGoodsDcKey] -= $exceptGoodsDcValue['price'];
            //상품별
            $addDcBasePriceArr['goods'][$exceptGoodsDcValue['goodsNo']] -= $exceptGoodsDcValue['price'];
            //주문별
            $addDcBasePriceArr['order'] -= $exceptGoodsDcValue['price'];
            //브랜드별
            $addDcBasePriceArr['brand'][$exceptGoodsDcValue['brandCd']] -= $exceptGoodsDcValue['price'];
        }

        $resultAddDcPriceArr = []; ///건별 추가 할인액
        foreach ($orderData as $orderDataKey => $orderDataValue) {
            $percent = $this->_memInfo['dcPercent'] / 100;
            switch ($this->_memInfo['fixedOrderTypeDc']) {
                case 'option':
                    //옵션별일 경우
                    if ($this->_memInfo['dcLine'] <= $addDcBasePriceArr['option'][$orderDataValue['sno']]) {
                        $tmpPrice = $addDcBasePriceArr['option'][$orderDataValue['sno']] / $orderDataValue['goodsCnt'];
                        $resultAddDcPriceArr[$orderDataValue['sno']] = gd_number_figure(($percent * $tmpPrice), $memberTruncPolicy['unitPrecision'], $memberTruncPolicy['unitRound']);;
                        $resultAddDcPriceArr[$orderDataValue['sno']] = $resultAddDcPriceArr[$orderDataValue['sno']] * $orderDataValue['goodsCnt'];
                    }
                    break;
                case 'goods':
                    //상품별일 경우
                    if ($this->_memInfo['dcLine'] <= $addDcBasePriceArr['goods'][$orderDataValue['goodsNo']]) {
                        $tmpPrice = $addDcBasePriceArr['option'][$orderDataValue['sno']] / $orderDataValue['goodsCnt'];
                        $resultAddDcPriceArr[$orderDataValue['sno']] = gd_number_figure(($percent * $tmpPrice), $memberTruncPolicy['unitPrecision'], $memberTruncPolicy['unitRound']);;
                        $resultAddDcPriceArr[$orderDataValue['sno']] = $resultAddDcPriceArr[$orderDataValue['sno']] * $orderDataValue['goodsCnt'];
                    }
                    break;
                case 'order':
                    //주문별일 경우
                    if ($this->_memInfo['dcLine'] <= $addDcBasePriceArr['order']) {
                        $tmpPrice = $addDcBasePriceArr['option'][$orderDataValue['sno']] / $orderDataValue['goodsCnt'];
                        $resultAddDcPriceArr[$orderDataValue['sno']] = gd_number_figure(($percent * $tmpPrice), $memberTruncPolicy['unitPrecision'], $memberTruncPolicy['unitRound']);;
                        $resultAddDcPriceArr[$orderDataValue['sno']] = $resultAddDcPriceArr[$orderDataValue['sno']] * $orderDataValue['goodsCnt'];
                    }
                    break;
                case 'brand':
                    //브랜드별일 경우
                    //브랜드별 할인율 설정
                    $percent = 0; // 브랜드별 할인율을 다시 설정 하기 위해 할인율 리셋
                    foreach ($this->_memInfo['dcBrandInfo']->cateCd as $dcBrandInfoKey => $dcBrandInfoVal) {
                        if ($orderDataValue['brandCd'] == $dcBrandInfoVal) {
                            $percent = ($this->_memInfo['dcBrandInfo']->goodsDiscount[$dcBrandInfoKey]);
                        } else if ($dcBrandInfoVal == 'noBrand' && $orderDataValue['brandCd'] == '') {
                            $percent = ($this->_memInfo['dcBrandInfo']->goodsDiscount[$dcBrandInfoKey]);
                        } else if ($dcBrandInfoVal == 'allBrand' && !in_array($orderDataValue['brandCd'], $this->_memInfo['dcBrandInfo']->cateCd) && $orderDataValue['brandCd'] != '') {
                            $percent = ($this->_memInfo['dcBrandInfo']->goodsDiscount[$dcBrandInfoKey]);
                        }
                    }
                    foreach ($this->_memInfo['dcBrandInfo']->cateCd as $dcBrandInfoKey => $dcBrandInfoVal) {
                        if ($dcBrandInfoVal == 'noBrand') {
                            $addGoodsPercent = ($this->_memInfo['dcBrandInfo']->goodsDiscount[$dcBrandInfoKey]);
                        }
                    }
                    $percent = $percent / 100;
                    $addGoodsPercent = $addGoodsPercent / 100;
                    if ($this->_memInfo['dcLine'] <= $addDcBasePriceArr['brand'][$orderDataValue['brandCd']]) {
                        $tmpPrice = $addDcBasePriceArr['option'][$orderDataValue['sno']] / $orderDataValue['goodsCnt'];
                        $resultAddDcPriceArr[$orderDataValue['sno']] = gd_number_figure(($percent * $tmpPrice), $memberTruncPolicy['unitPrecision'], $memberTruncPolicy['unitRound']);;
                        $resultAddDcPriceArr[$orderDataValue['sno']] = $resultAddDcPriceArr[$orderDataValue['sno']] * $orderDataValue['goodsCnt'];
                    }

                    //추가상품이 있다면
                    if ($orderDataValue['price']['addGoodsPriceSum'] > 0) {
                        if ($this->_memInfo['dcLine'] <= $addDcBasePriceArr['brand']['']) {
                            $resultAddDcPriceArr[$orderDataValue['sno']] += gd_number_figure(($addGoodsPercent * $orderDataValue['price']['addGoodsPriceSum']), $memberTruncPolicy['unitPrecision'], $memberTruncPolicy['unitRound']);;
                        }
                    }
                    break;
            }
        }

        //중복할인 기준 (옵션별, 상품별, 주문별)
        //기준 금액 중복할인으로 재 계산
        $overlapDcBasePriceArr = [];
        foreach ($overlapDcGoodsDc as $overlapDcGoodsDcKey => $overlapDcGoodsDcValue) {
            //옵션별 금액 보정
            $overlapDcBasePriceArr['option'][$overlapDcGoodsDcKey] += $overlapDcGoodsDcValue['price'];
            //상품별
            $overlapDcBasePriceArr['goods'][$overlapDcGoodsDcValue['goodsNo']] += $overlapDcGoodsDcValue['price'];
            //주문별
            $overlapDcBasePriceArr['order'] += $overlapDcGoodsDcValue['price'];
        }

        $resultOverlapDcPriceArr = []; ///건별 중복 할인액
        foreach ($orderData as $orderDataKey => $orderDataValue) {
            $percent = $this->_memInfo['overlapDcPercent'] / 100;
            switch ($this->_memInfo['fixedOrderTypeOverlapDc']) {
                case 'option':
                    //옵션별일 경우
                    if ($this->_memInfo['overlapDcLine'] <= $overlapDcBasePriceArr['option'][$orderDataValue['sno']]) {
                        $tmpPrice = $overlapDcBasePriceArr['option'][$orderDataValue['sno']] / $orderDataValue['goodsCnt'];
                        $resultOverlapDcPriceArr[$orderDataValue['sno']] = gd_number_figure(($percent * $tmpPrice), $memberTruncPolicy['unitPrecision'], $memberTruncPolicy['unitRound']);;
                        $resultOverlapDcPriceArr[$orderDataValue['sno']] = $resultOverlapDcPriceArr[$orderDataValue['sno']] * $orderDataValue['goodsCnt'];
                    }
                    break;
                case 'goods':
                    //상품별일 경우
                    if ($this->_memInfo['overlapDcLine'] <= $overlapDcBasePriceArr['goods'][$orderDataValue['goodsNo']]) {
                        $tmpPrice = $overlapDcBasePriceArr['option'][$orderDataValue['sno']] / $orderDataValue['goodsCnt'];
                        $resultOverlapDcPriceArr[$orderDataValue['sno']] = gd_number_figure(($percent * $tmpPrice), $memberTruncPolicy['unitPrecision'], $memberTruncPolicy['unitRound']);;
                        $resultOverlapDcPriceArr[$orderDataValue['sno']] = $resultOverlapDcPriceArr[$orderDataValue['sno']] * $orderDataValue['goodsCnt'];
                    }
                    break;
                case 'order':
                    //주문별일 경우
                    if ($this->_memInfo['overlapDcLine'] <= $overlapDcBasePriceArr['order']) {
                        $tmpPrice = $overlapDcBasePriceArr['option'][$orderDataValue['sno']] / $orderDataValue['goodsCnt'];
                        $resultOverlapDcPriceArr[$orderDataValue['sno']] = gd_number_figure(($percent * $tmpPrice), $memberTruncPolicy['unitPrecision'], $memberTruncPolicy['unitRound']);;
                        $resultOverlapDcPriceArr[$orderDataValue['sno']] = $resultOverlapDcPriceArr[$orderDataValue['sno']] * $orderDataValue['goodsCnt'];
                    }
                    break;
            }
        }

        //추가 마일리지 적립 절사 기준 가져오기
        $mileageTruncPolicy = Globals::get('gTrunc.mileage');

        //추가 마일리지 적립 방법
        //기준 금액 추가할인으로 재 계산
        $mileageBasePriceArr = $basePriceArr;
        foreach ($exceptGoodsMileage as $exceptGoodsMileageKey => $exceptGoodsMileageValue) {
            //옵션별 금액 보정
            $mileageBasePriceArr['option'][$exceptGoodsMileageKey] -= $exceptGoodsMileageValue['price'];
            //상품별
            $mileageBasePriceArr['goods'][$exceptGoodsMileageValue['goodsNo']] -= $exceptGoodsMileageValue['price'];
            //주문별
            $mileageBasePriceArr['order'] -= $exceptGoodsMileageValue['price'];
            //브랜드별
            $mileageBasePriceArr['brand'][$exceptGoodsMileageValue['brandCd']] -= $exceptGoodsMileageValue['price'];
        }

        $resultMileagePriceArr = []; ///건별 마일리지 지급액
        foreach ($orderData as $orderDataKey => $orderDataValue) {
            $percent = $this->_memInfo['mileagePercent'] / 100;
            switch ($this->_memInfo['fixedOrderTypeMileage']) {
                case 'option':
                    //옵션별일 경우
                    if ($this->_memInfo['mileageLine'] <= $mileageBasePriceArr['option'][$orderDataValue['sno']]) {
                        $tmpPrice = $mileageBasePriceArr['option'][$orderDataValue['sno']] / $orderDataValue['goodsCnt'];
                        $resultMileagePriceArr[$orderDataValue['sno']] = gd_number_figure(($percent * $tmpPrice), $mileageTruncPolicy['unitPrecision'], $mileageTruncPolicy['unitRound']);;
                        $resultMileagePriceArr[$orderDataValue['sno']] = $resultMileagePriceArr[$orderDataValue['sno']] * $orderDataValue['goodsCnt'];
                    }
                    break;
                case 'goods':
                    //상품별일 경우
                    if ($this->_memInfo['mileageLine'] <= $mileageBasePriceArr['goods'][$orderDataValue['goodsNo']]) {
                        $tmpPrice = $mileageBasePriceArr['option'][$orderDataValue['sno']] / $orderDataValue['goodsCnt'];
                        $resultMileagePriceArr[$orderDataValue['sno']] = gd_number_figure(($percent * $tmpPrice), $mileageTruncPolicy['unitPrecision'], $mileageTruncPolicy['unitRound']);;
                        $resultMileagePriceArr[$orderDataValue['sno']] = $resultMileagePriceArr[$orderDataValue['sno']] * $orderDataValue['goodsCnt'];
                    }
                    break;
                case 'order':
                    //주문별일 경우
                    if ($this->_memInfo['mileageLine'] <= $mileageBasePriceArr['order']) {
                        $tmpPrice = $mileageBasePriceArr['option'][$orderDataValue['sno']] / $orderDataValue['goodsCnt'];
                        $resultMileagePriceArr[$orderDataValue['sno']] = gd_number_figure(($percent * $tmpPrice), $mileageTruncPolicy['unitPrecision'], $mileageTruncPolicy['unitRound']);;
                        $resultMileagePriceArr[$orderDataValue['sno']] = $resultMileagePriceArr[$orderDataValue['sno']] * $orderDataValue['goodsCnt'];
                    }
                    break;
            }
        }

        foreach ($orderData as $orderDataKey => $orderDataValue) {
            $orderData[$orderDataKey]['price']['memberDcPrice'] = $resultAddDcPriceArr[$orderDataValue['sno']];
            $orderData[$orderDataKey]['price']['memberOverlapDcPrice'] = $resultOverlapDcPriceArr[$orderDataValue['sno']];
            $orderData[$orderDataKey]['price']['goodsMemberDcPrice'] = $resultAddDcPriceArr[$orderDataValue['sno']];
            $orderData[$orderDataKey]['price']['goodsMemberOverlapDcPrice'] = $resultOverlapDcPriceArr[$orderDataValue['sno']];
            $orderData[$orderDataKey]['price']['addGoodsMemberDcPrice'] = 0;
            $orderData[$orderDataKey]['price']['addGoodsMemberOverlapDcPrice'] = 0;

            $orderData[$orderDataKey]['mileage']['memberMileage'] = $resultMileagePriceArr[$orderDataValue['sno']];
            $orderData[$orderDataKey]['mileage']['goodsMemberMileage'] = $resultMileagePriceArr[$orderDataValue['sno']];
            $orderData[$orderDataKey]['mileage']['addGoodsMemberMileage'] = 0;

            $orderData[$orderDataKey]['memberDcInfo'] = '';
        }

        return $orderData;
    }

    /**
     * 주문 정보 중 회원 할인 정보 리셋
     *
     * @param $orderData array 주문 정보
     */
    private function resetMemberGroupBenefit($orderData){
        foreach ($orderData as $orderDataKey => $orderDataValue) {
            $orderData[$orderDataKey]['price']['memberDcPrice'] = 0;
            $orderData[$orderDataKey]['price']['memberOverlapDcPrice'] = 0;
            $orderData[$orderDataKey]['price']['goodsMemberDcPrice'] = 0;
            $orderData[$orderDataKey]['price']['goodsMemberOverlapDcPrice'] = 0;
            $orderData[$orderDataKey]['price']['addGoodsMemberDcPrice'] = 0;
            $orderData[$orderDataKey]['price']['addGoodsMemberOverlapDcPrice'] = 0;

            $orderData[$orderDataKey]['mileage']['memberMileage'] = 0;
            $orderData[$orderDataKey]['mileage']['goodsMemberMileage'] = 0;
            $orderData[$orderDataKey]['mileage']['addGoodsMemberMileage'] = 0;

            $orderData[$orderDataKey]['memberDcInfo'] = '';
        }
        return $orderData;
    }
}
