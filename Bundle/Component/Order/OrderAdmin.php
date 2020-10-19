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
namespace Bundle\Component\Order;

use App;
use Component\Bankda\BankdaOrder;
use Component\Database\DBTableField;
use Component\Delivery\Delivery;
use Component\Deposit\Deposit;
use Component\Godo\MyGodoSmsServerApi;
use Component\Godo\NaverPayAPI;
use Component\Mail\MailMimeAuto;
use Component\Mall\Mall;
use Component\Member\Manager;
use Component\Mileage\Mileage;
use Component\Naver\NaverPay;
use Component\Sms\Code;
use Component\Validator\Validator;
use Exception;
use Framework\Debug\Exception\AlertBackException;
use Framework\Debug\Exception\Except;
use Framework\Debug\Exception\LayerNotReloadException;
use Framework\Debug\Exception\AlertRedirectException;
use Framework\StaticProxy\Proxy\UserFilePath;
use Framework\Utility\ArrayUtils;
use Framework\Utility\DateTimeUtils;
use Framework\Utility\NumberUtils;
use Framework\Utility\StringUtils;
use Globals;
use LogHandler;
use Request;
use Session;
use Vendor\Spreadsheet\Excel\Reader as SpreadsheetExcelReader;
use Component\Page\Page;

/**
 * 주문 class
 * 주문 관련 관리자 Class
 *
 * @package Bundle\Component\Order
 * @author  Jong-tae Ahn <qnibus@godo.co.kr>
 */
class OrderAdmin extends \Component\Order\Order
{
    /**
     * 주문관련 메시지 정의
     */
    const ECT_INVALID_ARG = 'OrderAdmin.ECT_INVALID_ARG';
    const ECT_UPLOAD_FILEERROR = 'OrderAdmin.ECT_UPLOAD_FILEERROR';
    const EXCEL_DOWN_ERROR = 'EXCEL_DOWN_ERROR';
    const NOT_EXIST_ORDER_DATA = '[%s] 주문 정보가 존재하지 않습니다.';
    const TEXT_REQUIRE_VALUE = '%s은(는) 필수 항목 입니다.';
    const TEXT_INVALID_VALUE = '%s이 잘못되었습니다.';
    const TEXT_PASSWORD_INVALID = '%s은(는) 비밀번호형식에 맞지 않습니다.';
    const NOT_REQUEST_ORDER_STATUS = '해당 주문단계에서는 신청을 하실 수 없습니다.';

    protected $orderGridConfigList; //주문그리드 디폴트 설정 항목
    protected $naverpayConfig;  //네이버페이 설정
    protected $paycoConfig;  //페이코쇼핑 설정
    protected $couponConfig; //쿠폰설정
    protected $delivery;

    public $userHandleText = [
        'r' => '환불신청',
        'b' => '반품신청',
        'e' => '교환신청',
    ];

    public $orderStatus = array();

    /**
     * @var 마이앱 사용유무
     */
    public $useMyapp;

    /**
     *  키워드 검색 시 Like 검색, = 검색 여부 확인
     */
    public $equalSearch = [
        'o.orderNo','og.goodsNo','og.goodsCd','og.goodsModelNo','og.makerNm','oi.orderName',
        'oi.receiverName','o.bankSender','m.nickNm','sm.companyNm','pu.purchaseNm'
    ];

    public $fullLikeSearch = [
        'og.invoiceNo','og.goodsNm','oi.orderCellPhone',
        'oi.orderPhone','oi.receiverPhone','oi.receiverCellPhone'
    ];

    public $endLikeSearch = [
        'oi.orderEmail','m.memId'
    ];

    public $changeSearchKind = [
        'oi.orderName', 'oi.receiverName', 'o.bankSender', 'm.nickNm'
    ];

    /**
     * @var bool 주문 다중 검색 시 es_member 테이블 조인 여부
     */
    public $multiSearchMemberJoinFl = false;
    public $multiSearchPurchaseJoinFl = false;
    public $multiSearchScmJoinFl = false;

    /**
     * 생성자
     */
    public function __construct()
    {
        parent::__construct();

        $dbUrl = \App::load('\\Component\\Marketing\\DBUrl');
        $this->delivery = \App::load('\\Component\\Delivery\\Delivery');
        $this->orderStatus = $this->getAllOrderStatus();
        $this->paycoConfig = $dbUrl->getConfig('payco', 'config');
        $this->useMyapp = gd_policy('myapp.config')['useMyapp']; // 마이앱 사용유무
        $this->couponConfig = gd_policy('coupon.config');
    }

    /**
     * 네이버페이 환경설정 가져오기
     *
     * @param null $key
     * @return array
     */
    protected function getNaverPayConfig($key = null)
    {
        if(empty($this->naverpayConfig)) {
            $this->naverpayConfig = gd_policy('naverPay.config');
        }
        if($key){
            return $this->naverpayConfig[$key];
        }
        return $this->naverpayConfig;
    }

    /**
     * 관리자 주문 리스트를 위한 검색 정보 세팅
     *
     * @param string  $searchData   검색 데이타
     * @param integer $searchPeriod 기본 조회 기간 (삭제예정)
     * @param boolean $isUserHandle
     *
     * @throws AlertBackException
     */
    protected function _setSearch($searchData, $searchPeriod = 7, $isUserHandle = false)
    {
        $isMultiSearch = gd_isset(\Session::get('manager.isOrderSearchMultiGrid'), 'n');
        // 통합 검색
        $this->search['combineSearch'] = [
            'o.orderNo' => __('주문번호'),
            'og.invoiceNo' => __('송장번호'),
            'og.goodsNm' => __('상품명'),
            'og.goodsNo' => __('상품코드'),
            'og.goodsCd' => __('자체 상품코드'),
            'og.goodsModelNo' => __('상품모델명'),
            'og.makerNm' => __('제조사'),
            '__disable1' =>'==========',
            'oi.orderName' => __('주문자명'),
            'oi.orderPhone' => __('주문자 전화번호'),
            'oi.orderCellPhone' => __('주문자 휴대폰번호'),
            'oi.orderEmail' => __('주문자 이메일'),
            'oi.receiverName' => __('수령자명'),
            'oi.receiverPhone' => __('수령자 전화번호'),
            'oi.receiverCellPhone' => __('수령자 휴대폰번호'),
            'o.bankSender' => __('입금자명'),
            '__disable2' =>'==========',
            'm.memId' => __('아이디'),
            'm.nickNm' => __('닉네임'),
            'oi.orderName' => __('주문자명'),
        ];
        if($isMultiSearch == 'y') {
            $this->search['combineSearch'] = [
                'o.orderNo' => __('주문번호'),
                'og.invoiceNo' => __('송장번호'),
                'o.bankSender' => __('입금자명'),
                'm.memId' => __('아이디'),
                'm.nickNm' => __('닉네임'),
                '__disable1' => '==========',
                'oi.orderName' => __('주문자명'),
                'oi.orderPhone' => __('주문자 전화번호'),
                'oi.orderCellPhone' => __('주문자 휴대폰번호'),
                'oi.orderEmail' => __('주문자 이메일'),
                'oi.receiverName' => __('수령자명'),
                'oi.receiverPhone' => __('수령자 전화번호'),
                'oi.receiverCellPhone' => __('수령자 휴대폰번호'),
            ];
        }

        // Like Search & Equal Search
        $this->search['searchKindArray'] = [
            'equalSearch' => __('검색어 전체일치'),
            'fullLikeSearch' => __('검색어 부분포함'),
        ];

        if(gd_is_provider() === false) {
            $this->search['combineSearch']['__disable3'] = "==========";
            $this->search['combineSearch']['sm.companyNm'] = __('공급사명');
            if (gd_is_plus_shop(PLUSSHOP_CODE_PURCHASE) === true) {
                $this->search['combineSearch']['pu.purchaseNm'] = __('매입처명');
            }
        }

        // !중요! 순서 변경시 하단의 노출항목 조절 필요
        $this->search['combineTreatDate'] = [
            'og.regDt' => __('주문일'),
            'og.paymentDt' => __('결제확인일'),
            'og.invoiceDt' => __('송장입력일'),
            'og.deliveryDt' => __('배송일'),
            'og.deliveryCompleteDt' => __('배송완료일'),
            'og.finishDt' => __('구매확정일'),
            'og.cancelDt' => __('취소완료일'),
            'oh.regDt.b' => __('반품접수일'),
            'oh.handleDt.b' => __('반품완료일'),
            'oh.regDt.e' => __('교환접수일'),
            'oh.handleDt.e' => __('교환완료일'),
            'oh.regDt.r' => __('환불접수일'),
            'oh.handleDt.r' => __('환불완료일'),
            'oi.packetCode' => __('묶음배송'),
        ];

        // --- $searchData trim 처리
        if (isset($searchData)) {
            gd_trim($searchData);
        }

        // --- 정렬
        $this->search['sortList'] = [
            'og.orderNo desc' => sprintf('%s↓', __('주문일')),
            'og.orderNo asc' => sprintf('%s↑', __('주문일')),
            'og.orderNo desc' => sprintf('%s↓', __('주문번호')),
            'og.orderNo asc' => sprintf('%s↑', __('주문번호')),
            'o.orderGoodsNm desc' =>sprintf('%s↓',  __('상품명')),
            'o.orderGoodsNm asc' => sprintf('%s↑', __('상품명')),
            'oi.orderName desc' => sprintf('%s↓', __('주문자')),
            'oi.orderName asc' => sprintf('%s↑', __('주문자')),
            'o.settlePrice desc' => sprintf('%s↓', __('총 결제금액')),
            'o.settlePrice asc' => sprintf('%s↑', __('총 결제금액')),
            'oi.receiverName desc' => sprintf('%s↓', __('수령자')),
            'oi.receiverName asc' => sprintf('%s↑', __('수령자')),
            'sm.companyNm desc' => sprintf('%s↓', __('공급사')),
            'sm.companyNm asc' => sprintf('%s↑', __('공급사')),
            'og.orderStatus desc' => sprintf('%s↓', __('처리상태')),
            'og.orderStatus asc' => sprintf('%s↑', __('처리상태')),
        ];

        // 상품주문번호별 탭을 제외하고는 처리상태 정렬 제거
        if ($isUserHandle === false) {
            unset($this->search['sortList']['og.orderStatus desc'], $this->search['sortList']['og.orderStatus asc']);
        }

        // 상품주문번호별 탭을 제외하고는 처리상태 정렬 제거
        if ($isUserHandle === false) {
            unset($this->search['sortList']['og.orderStatus desc'], $this->search['sortList']['og.orderStatus asc']);
        }

        // statusMode에 따른 combineTreatDate 노출항목 설정 ($this->search['combineTreatDate'] 데이터 변경되면 반드시 바껴야 함)
        if (isset($searchData['statusMode'])) {
            switch ($searchData['statusMode']) {
                case 'o':
                    $this->search['treatDateFl'] = gd_isset($searchData['treatDateFl'], 'og.regDt');
                    $this->search['combineTreatDate'] = array_slice($this->search['combineTreatDate'], 0, 1);
                    break;

                case 'p':
                case 'g':
                    $this->search['treatDateFl'] = gd_isset($searchData['treatDateFl'], 'og.paymentDt');
                    $this->search['combineTreatDate'] = array_slice($this->search['combineTreatDate'], 0, 2);

                    $this->search['treatDateFl'] = gd_isset($searchData['treatDateFl'], 'oi.packetCode');

                    self::setAddSearchSortList(array('paymentDt', 'packetCode'));
                    break;

                case 'd1':
                    $this->search['treatDateFl'] = gd_isset($searchData['treatDateFl'], 'og.deliveryDt');
                    $this->search['combineTreatDate'] = array_slice($this->search['combineTreatDate'], 0, 4);

                    self::setAddSearchSortList(array('paymentDt'));
                    break;

                case 'd2':
                    $this->search['treatDateFl'] = gd_isset($searchData['treatDateFl'], 'og.deliveryCompleteDt');
                    $this->search['combineTreatDate'] = array_slice($this->search['combineTreatDate'], 0, 5);

                    self::setAddSearchSortList(array('paymentDt'));
                    break;

                case 's':
                    $this->search['treatDateFl'] = gd_isset($searchData['treatDateFl'], 'og.finishDt');
                    $this->search['combineTreatDate'] = array_slice($this->search['combineTreatDate'], 0, 6);

                    self::setAddSearchSortList(array('paymentDt'));
                    break;

                case 'f':
                    $this->search['treatDateFl'] = gd_isset($searchData['treatDateFl'], 'og.regDt');
                    $this->search['combineTreatDate'] = array_slice($this->search['combineTreatDate'], 0, 1);
                    break;

                case 'c':
                    $this->search['treatDateFl'] = gd_isset($searchData['treatDateFl'], 'og.cancelDt');
                    $this->search['combineTreatDate'] = array_merge(array_slice($this->search['combineTreatDate'], 0, 1), array_slice($this->search['combineTreatDate'], 6, 1));
                    break;

                case 'e':
                    $this->search['treatDateFl'] = gd_isset($searchData['treatDateFl'], 'oh.regDt.e');
                    $this->search['combineTreatDate'] = array_merge(array_slice($this->search['combineTreatDate'], 0, 6), array_slice($this->search['combineTreatDate'], 9, 2));
                    break;

                case 'b':
                    $this->search['treatDateFl'] = gd_isset($searchData['treatDateFl'], 'oh.regDt.b');
                    $this->search['combineTreatDate'] = array_merge(array_slice($this->search['combineTreatDate'], 0, 6), array_slice($this->search['combineTreatDate'], 7, 2));
                    break;

                case 'r':
                    $this->search['treatDateFl'] = gd_isset($searchData['treatDateFl'], 'oh.regDt.r');
                    $this->search['combineTreatDate'] = array_merge(array_slice($this->search['combineTreatDate'], 0, 6), array_slice($this->search['combineTreatDate'], 11, 2));
                    break;
            }
        }

        // 검색을 위한 bind 정보
        $fieldTypeGoods = DBTableField::getFieldTypes('tableGoods');

        // 검색기간 설정
        $data = gd_policy('order.defaultSearch');
        // CRM관리에서 주문요약 내역 90일 처리
        $thisCallController = \App::getInstance('ControllerNameResolver')->getControllerName();
        if($thisCallController == 'Controller\Admin\Share\MemberCrmController') {
            $searchPeriod = 90;
        } else {
            $searchPeriod = gd_isset($data['searchPeriod'], 6);
        }

        // --- 검색 설정
        $this->search['mallFl'] = gd_isset($searchData['mallFl'], 'all');
        $this->search['exceptOrderStatus'] = gd_isset($searchData['exceptOrderStatus']);    //예외처리할 주문상태
        $this->search['detailSearch'] = gd_isset($searchData['detailSearch']);
        $this->search['statusMode'] = gd_isset($searchData['statusMode']);
        $this->search['key'] = gd_isset($searchData['key']);
        $this->search['keyword'] = gd_isset($searchData['keyword']);
        $this->search['sort'] = gd_isset($searchData['sort']);
        $this->search['orderStatus'] = gd_isset($searchData['orderStatus']);
        $this->search['pgChargeBack'] = gd_isset($searchData['pgChargeBack']);
        $this->search['processStatus'] = gd_isset($searchData['processStatus']);
        $this->search['userHandleMode'] = gd_isset($searchData['userHandleMode']);
        $this->search['userHandleFl'] = gd_isset($searchData['userHandleFl']);
        $this->search['treatDateFl'] = gd_isset($searchData['treatDateFl'], 'og.regDt');
        $this->search['treatDate'][] = gd_isset($searchData['treatDate'][0], date('Y-m-d', strtotime('-' . $searchPeriod . ' day')));
        if($searchPeriod == '1') $this->search['treatDate'][] = gd_isset($searchData['treatDate'][1], date('Y-m-d', strtotime('-' . $searchPeriod . ' day')));
        else $this->search['treatDate'][] = gd_isset($searchData['treatDate'][1], date('Y-m-d'));
        if($searchData['treatTimeFl'] != 'y') unset($searchData['treatTime']); // 시간설정 사용 시
        $this->search['treatTime'][] = gd_isset($searchData['treatTime'][0], '00:00:00');
        $this->search['treatTime'][] = gd_isset($searchData['treatTime'][1], '23:59:59');
        $this->search['treatTimeFl'] = gd_isset($searchData['treatTimeFl'], 'n');
        $this->search['settleKind'] = gd_isset($searchData['settleKind']);
        $this->search['settlePrice'][] = gd_isset($searchData['settlePrice'][0]);
        $this->search['settlePrice'][] = gd_isset($searchData['settlePrice'][1]);
        $this->search['memFl'] = gd_isset($searchData['memFl']);
        $this->search['memberGroupNo'] = gd_isset($searchData['memberGroupNo']);
        $this->search['memberGroupNoNm'] = gd_isset($searchData['memberGroupNoNm']);
        $this->search['receiptFl'] = gd_isset($searchData['receiptFl']);
        $this->search['userHandleViewFl'] = gd_isset($searchData['userHandleViewFl']);
        $this->search['orderTypeFl'] = gd_isset($searchData['orderTypeFl']);
        $this->search['orderChannelFl'] = gd_isset($searchData['orderChannelFl']);
        $this->search['scmFl'] = gd_isset($searchData['scmFl'], 'all');
        // 공급사 선택 후 공급사가 없는 경우
        if ($searchData['scmNo'] == 0 && $searchData['scmFl'] == 1) {
            $this->search['scmFl'] = 'all';
        }
        $this->search['scmNo'] = gd_isset($searchData['scmNo']);
        $this->search['scmNoNm'] = gd_isset($searchData['scmNoNm']);
        $this->search['scmAdjustNo'] = gd_isset($searchData['scmAdjustNo']);
        $this->search['scmAdjustType'] = gd_isset($searchData['scmAdjustType']);
        $this->search['manualPayment'] = gd_isset($searchData['manualPayment'], '');
        $this->search['invoiceFl'] = gd_isset($searchData['invoiceFl'], '');
        $this->search['firstSaleFl'] = gd_isset($searchData['firstSaleFl'], 'n');
        $this->search['withGiftFl'] = gd_isset($searchData['withGiftFl'], 'n');
        $this->search['withMemoFl'] = gd_isset($searchData['withMemoFl'], 'n');
        $this->search['withAdminMemoFl'] = gd_isset($searchData['withAdminMemoFl'], 'n');
        $this->search['withPacket'] = gd_isset($searchData['withPacket'], 'n');
        $this->search['overDepositDay'] = gd_isset($searchData['overDepositDay']);
        $this->search['invoiceCompanySno'] = gd_isset($searchData['invoiceCompanySno']);
        $this->search['invoiceNoFl'] = gd_isset($searchData['invoiceNoFl']);
        $this->search['underDeliveryDay'] = gd_isset($searchData['underDeliveryDay']);
        $this->search['underDeliveryOrder'] = gd_isset($searchData['underDeliveryOrder'], 'n');
        $this->search['couponNo'] = gd_isset($searchData['couponNo']);
        $this->search['couponNoNm'] = gd_isset($searchData['couponNoNm']);
        $this->search['couponAllFl'] = gd_isset($searchData['couponAllFl']);
        $this->search['eventNo'] = gd_isset($searchData['eventNo']);
        $this->search['eventNoNm'] = gd_isset($searchData['eventNoNm']);
        $this->search['dateSearchFl'] = gd_isset($searchData['dateSearchFl'],'y');

        $this->search['purchaseNo'] = gd_isset($searchData['purchaseNo']);
        $this->search['purchaseNoNm'] = gd_isset($searchData['purchaseNoNm']);
        $this->search['purchaseNoneFl'] = gd_isset($searchData['purchaseNoneFl']);

        $this->search['brandNoneFl'] = gd_isset($searchData['brandNoneFl']);
        $this->search['brand'] =ArrayUtils::last(gd_isset($searchData['brand']));
        $this->search['brandCd'] = gd_isset($searchData['brandCd']);
        $this->search['brandCdNm'] = gd_isset($searchData['brandCdNm']);
        $this->search['orderNo'] = gd_isset($searchData['orderNo']);
        $this->search['orderMemoCd'] = gd_isset($searchData['orderMemoCd']);

        $this->search['goodsNo'] = gd_isset($searchData['goodsNo']);
        $this->search['goodsText'] = gd_isset($searchData['goodsText']);
        $this->search['goodsKey'] = gd_isset($searchData['goodsKey']);

        // --- 검색 종류 설정 (Like Or Equal)
        $this->search['searchKind'] = gd_isset($searchData['searchKind']);

        $orderBasic = gd_policy('order.basic');
        if (($orderBasic['userHandleAdmFl'] == 'y' && $orderBasic['userHandleScmFl'] == 'y') === false) {
            unset($orderBasic['userHandleScmFl']);
        }
        $userHandleUsePage = ['order_list_all.php', 'order_list_pay.php', 'order_list_goods.php', 'order_list_delivery.php', 'order_list_delivery_ok.php'];
        if ($orderBasic['userHandleFl'] == 'y' && in_array(Request::getFileUri(), $userHandleUsePage) === true && (!Manager::isProvider() && $orderBasic['userHandleAdmFl'] == 'y') || (Manager::isProvider() && $orderBasic['userHandleScmFl'] == 'y')) {
            $this->search['userHandleAdmFl'] = 'y';
        }

        if($isMultiSearch == 'y') {
            if (DateTimeUtils::intervalDay($this->search['treatDate'][0], $this->search['treatDate'][1]) > 180) {
                throw new AlertBackException(__('6개월이상 기간으로 검색하실 수 없습니다.'));
            }
        } else {
            if (DateTimeUtils::intervalDay($this->search['treatDate'][0], $this->search['treatDate'][1]) > 365) {
                throw new AlertBackException(__('1년이상 기간으로 검색하실 수 없습니다.'));
            }
        }

        // 주문/주문상품 탭 설정
        if (in_array($searchData['statusMode'], ['','o'])) {
            $this->search['view'] = gd_isset($searchData['view'], 'order');
        } elseif (in_array(substr($searchData['statusMode'], 0, 1), ['p','g','d','s'])) {
            $this->search['view'] = gd_isset($searchData['view'], 'orderGoodsSimple');
        } else {
            $this->search['view'] = gd_isset($searchData['view'], 'orderGoods');
        }

        // CRM
        $this->search['memNo'] = gd_isset($searchData['memNo'], null);

        // --- 검색 설정
        $this->checked['treatTimeFl'][$this->search['treatTimeFl']]  =
        $this->checked['purchaseNoneFl'][$this->search['purchaseNoneFl']]  =
        $this->checked['mallFl'][$this->search['mallFl']] =
        $this->checked['scmFl'][$this->search['scmFl']] =
        $this->checked['memFl'][$this->search['memFl']] =
        $this->checked['manualPayment'][$this->search['manualPayment']] =
        $this->checked['firstSaleFl'][$this->search['firstSaleFl']] =
        $this->checked['withGiftFl'][$this->search['withGiftFl']] =
        $this->checked['withMemoFl'][$this->search['withMemoFl']] =
        $this->checked['withAdminMemoFl'][$this->search['withAdminMemoFl']] =
        $this->checked['withPacket'][$this->search['withPacket']] =
        $this->checked['underDeliveryOrder'][$this->search['underDeliveryOrder']] =
        $this->checked['invoiceNoFl'][$this->search['invoiceNoFl']] =
        $this->checked['brandNoneFl'][$this->search['brandNoneFl']] =
        $this->checked['couponAllFl'][$this->search['couponAllFl']] =
        $this->checked['receiptFl'][$this->search['receiptFl']] =
        $this->checked['memoType'][$this->search['memoType']] =
        $this->checked['userHandleViewFl'][$this->search['userHandleViewFl']] = 'checked="checked"';
        $this->checked['periodFl'][$searchPeriod] = 'active';

        // --- 검색 종류 설정 (Like Or Equal)
        if ($this->search['searchKind'] && in_array($this->search['key'], $this->changeSearchKind)) {
            $this->setKeySearchType($this->search['key'], $this->search['searchKind']);
        }

        if ($this->search['orderNo'] !== null) {
            $this->arrWhere[] = 'o.orderNo = ?';
            $this->db->bind_param_push($this->arrBind, 's', $this->search['orderNo']);
        }

        // 회원 주문인 경우 (CRM 주문조회)
        if ($this->search['memNo'] !== null) {
            $this->arrWhere[] = 'o.memNo = ?';
            $this->db->bind_param_push($this->arrBind, 's', $this->search['memNo']);
        }

        // 주문 상태 모드가 있는 경우
        if ($this->search['statusMode'] !== null) {
            $tmp = explode(',', $this->search['statusMode']);
            foreach ($tmp as $val) {
                $sameOrderStatus = $this->getOrderStatusList($val, null, null, 'orderList');
                $sameOrderStatus = array_keys($sameOrderStatus);
                $sameOrderStatusCount = count($sameOrderStatus);
                if ($sameOrderStatusCount > 1) {
                    $tmpbind = array_fill(0, $sameOrderStatusCount, '?');
                    $tmpWhere[] = 'og.orderStatus IN (' . implode(',', $tmpbind). ')';
                    foreach ($sameOrderStatus as $valStatus) {
                        $this->db->bind_param_push($this->arrBind, 's', $valStatus);
                    }
                } else {
                    $tmpWhere[] = 'og.orderStatus = ?';
                    $this->db->bind_param_push($this->arrBind, 's', $sameOrderStatus[0]);
                }
            }
            $this->arrWhere[] = '(' . implode(' OR ', $tmpWhere) . ')';
            unset($tmpWhere);
        } else {
            // 결제시도 무조건 제거
            $this->arrWhere[] = 'og.orderStatus != ?';
            $this->db->bind_param_push($this->arrBind, 's', 'f1');
        }

        if ($this->search['exceptOrderStatus']) { //예외처리할 주문상태 쿼리
            $exceptStatusQuery = implode("','", $this->search['exceptOrderStatus']);
            $this->arrWhere[] = "og.orderStatus NOT IN ('" . $exceptStatusQuery . "')";
        }

        // 수동입금확인 체크
        if ($this->search['manualPayment'] == 'y') {
            $this->arrWhere[] = 'o.settleKind = ? AND og.paymentDt != \'0000-00-00 00:00:00\' AND (o.orderAdminLog NOT LIKE concat(\'%\',?,\'%\'))';
            $this->db->bind_param_push($this->arrBind, 's', 'gb');
            $this->db->bind_param_push($this->arrBind, 's', BankdaOrder::BANK_AUTO_DEPOSIT);
        }

        // 멀티상점 선택
        if ($this->search['mallFl'] !== 'all') {
            $this->arrWhere[] = 'o.mallSno = ?';
            $this->db->bind_param_push($this->arrBind, 's', $this->search['mallFl']);
        }

        // 공급사 선택
        if (Manager::isProvider()) {
            // 공급사로 로그인한 경우 기존 scm에 값 설정
            $this->arrWhere[] = 'og.scmNo = ' . Session::get('manager.scmNo');
            // 공급사에서는 입금대기 상태와 취소상태가 보여지면 안된다.
            $excludeStatusCode = ['o', 'c', 'f'];
            $arrWhereOrderStatusArray = $this->getExcludeOrderStatus($this->orderStatus, $excludeStatusCode);
            $this->arrWhere[] = 'og.orderStatus IN (\'' . implode('\',\'', array_keys($arrWhereOrderStatusArray)) . '\')';
            unset($arrWhereOrderStatusArray);
        } else {
            if ($this->search['scmFl'] == '1') {
                if (is_array($this->search['scmNo'])) {
                    foreach ($this->search['scmNo'] as $val) {
                        $tmpWhere[] = 'og.scmNo = ?';
                        $this->db->bind_param_push($this->arrBind, 's', $val);
                    }
                    $this->arrWhere[] = '(' . implode(' OR ', $tmpWhere) . ')';
                    unset($tmpWhere);
                } else if ($this->search['scmNo'] > 1) {
                    $this->arrWhere[] = 'og.scmNo = ?';
                    $this->db->bind_param_push($this->arrBind, 'i', $this->search['scmNo']);
                }
            } elseif ($this->search['scmFl'] == '0') {
                $this->arrWhere[] = 'og.scmNo = 1';
            }
        }

        // 상품 검색
        if ($this->search['goodsNo']) {
            $this->arrWhere[] = 'og.goodsNo = ?';
            $this->db->bind_param_push($this->arrBind, 'i', $this->search['goodsNo']);
        } else if($this->search['goodsText']) {
            $goodsKey = $this->search['goodsKey'];
            if($goodsKey == 'og.goodsNm') {
                $this->arrWhere[] = 'og.goodsNm LIKE concat(\'%\',?,\'%\')';
            } else {
                $this->arrWhere[] = $goodsKey . ' = ?';
            }
            $this->db->bind_param_push($this->arrBind, 's', $this->search['goodsText']);
        }

        // 키워드 검색
        if ($this->search['key'] && $this->search['keyword']) {
            $keyword = $this->search['keyword'];
            if($isMultiSearch == 'y') {
                $useNaverPay = $this->getNaverPayConfig('useYn') == 'y';
                foreach($keyword as $keywordKey => $keywordVal) {
                    if($keywordVal) {
                        if(in_array($this->search['key'][$keywordKey], ['m.memId', 'm.nickNm'])) $this->multiSearchMemberJoinFl = true;
                        if(in_array($this->search['key'][$keywordKey], ['pu.purchaseNm'])) $this->multiSearchPurchaseJoinFl = true;
                        if(in_array($this->search['key'][$keywordKey], ['sm.companyNm'])) $this->multiSearchScmJoinFl = true;
                        $keywordVal = explode(',', preg_replace('{(?:\r\n|\r|\n)}', ",", $keywordVal));
                        $_keyword = $_naverPayKeyword = $_naverPayVal = [];
                        foreach($keywordVal as $keywordVal2) {
                            $keywordVal2 = trim($keywordVal2);
                            if(count($_keyword) >= 10 || empty($keywordVal2)) continue;
                            if (strpos($this->search['key'][$keywordKey], 'Phone') !== false) {
                                $keywordVal2 = StringUtils::numberToPhone(str_replace('-', '', $keywordVal2), true);
                            }
                            $_keyword[] = '?';
                            $this->db->bind_param_push($this->arrBind, 's', $keywordVal2);
                            if ($this->search['key'][$keywordKey] == 'o.orderNo' && $useNaverPay) { //네이버페이 사용할경우 네이버페이 주문번호도 추가 검색
                                $_naverPayKeyword[] = '?';
                                $_naverPayVal[] = $keywordVal2;
                            }
                        }
                        if($_keyword) $keywordWhere[] = $this->search['key'][$keywordKey]." in (" . implode(",", $_keyword) . ")";
                        if($useNaverPay && count($_naverPayVal) > 0) {
                            $keywordWhere[] = "o.apiOrderNo in (" . implode(",", $_naverPayKeyword) . ")";
                            foreach($_naverPayVal as $_naverPayVal2) {
                                $this->db->bind_param_push($this->arrBind, 's', $_naverPayVal2);
                            }
                        }
                        unset($_keyword, $_naverPayKeyword, $_naverPayVal);
                    }
                }
                if($keywordWhere) $this->arrWhere[] = '(' . implode(' OR ', $keywordWhere) . ')';
            } else {
                if(is_array($this->search['key'])) $this->search['key'] = $this->search['key'][0];
                if(is_array($this->search['keyword'])) $this->search['keyword'] = $this->search['keyword'][0];
                if ($this->search['key'] == 'all') {
                    $tmpWhere = array_keys($this->search['combineSearch']);
                    if ($this->getNaverPayConfig('useYn') == 'y') {    //네이버페이 사용할경우 네이버페이 주문번호도 추가 검색
                        $tmpWhere[] = 'o.apiOrderNo';
                    }
                    array_shift($tmpWhere);
                    $arrWhereAll = [];
                    foreach ($tmpWhere as $keyNm) {
                        // 전화번호인 경우 -(하이픈)이 없어도 검색되도록 처리
                        if (strpos($keyNm, 'Phone') !== false) {
                            $keyword = StringUtils::numberToPhone($keyword, true);
                        } else {
                            $keyword = $this->search['keyword'];
                        }
                        $searchType = $this->getKeySearchType($keyNm);
                        if ($searchType == 'fullLikeSearch') {
                            $arrWhereAll[] = '(' . $keyNm . ' LIKE concat(\'%\',?,\'%\'))';
                        } else if ($searchType == 'equalSearch') {
                            $arrWhereAll[] = '(' . $keyNm . ' = ? )';
                        } else if ($searchType == 'endLikeSearch') {
                            $arrWhereAll[] = '(' . $keyNm . ' LIKE concat(?,\'%\'))';
                        } else {
                            $arrWhereAll[] = '(' . $keyNm . ' LIKE concat(\'%\',?,\'%\'))';
                        }
                        $this->db->bind_param_push($this->arrBind, 's', $keyword);
                    }
                    $this->arrWhere[] = '(' . implode(' OR ', $arrWhereAll) . ')';
                    unset($tmpWhere);
                } else {
                    if ($this->search['key'] == 'o.orderNo') {    //네이버페이 사용중이고 주문번호 단일 검색일 경우 주문번호는 equal 검색
                        if ($this->getNaverPayConfig('useYn') == 'y') {
                            $this->arrWhere[] = '(' . $this->search['key'] . ' = ? OR apiOrderNo = ? )';
                            $this->db->bind_param_push($this->arrBind, 's', $keyword);
                        } else {
                            $this->arrWhere[] = $this->search['key'] . ' = ?';
                        }
                    } else {
                        $searchType = $this->getKeySearchType($this->search['key']);
                        if ($searchType == 'fullLikeSearch') {
                            $this->arrWhere[] = '(' . $this->search['key'] . ' LIKE concat(\'%\',?,\'%\'))';
                        } else if ($searchType == 'equalSearch') {
                            $this->arrWhere[] = '(' . $this->search['key'] . ' = ?)';
                        } else if ($searchType == 'endLikeSearch') {
                            $this->arrWhere[] = '(' . $this->search['key'] . ' LIKE concat(?,\'%\'))';
                        } else {
                            $this->arrWhere[] = '(' . $this->search['key'] . ' LIKE concat(\'%\',?,\'%\'))';
                        }
                    }

                    // 전화번호인 경우 -(하이픈)이 없어도 검색되도록 처리
                    if (strpos($this->search['key'], 'Phone') !== false) {
                        $keyword = StringUtils::numberToPhone($keyword, true);
                    } else {
                        $keyword = $this->search['keyword'];
                    }
                    $this->db->bind_param_push($this->arrBind, 's', $keyword);
                }
            }
        }

        // 주문유형
        if ($this->search['orderTypeFl'][0]) {
            $orderTypeMobileAll = false; // 모바일 전체 검색 여부(WEB / APP)
            foreach ($this->search['orderTypeFl'] as $val) {
                $this->checked['orderTypeFl'][$val] = 'checked="checked"';

                // 모바일 (WEB / APP) 주문유형 검색 추가
                if (in_array('mobile', $this->search['orderTypeFl'])) {
                    $orderTypeMobileAll = true;
                }

                if ($orderTypeMobileAll === false) {
                    switch ($val) {
                        case 'mobile-web':
                            $val = 'mobile';
                            $this->arrWhere[] = 'o.appOs  = ""';
                            break;
                        case 'mobile-app':
                            $val = 'mobile';
                            $this->arrWhere[] = '(o.appOs  != "" OR o.pushCode != "")';
                            break;
                    }
                }
                $tmpWhere[] = 'o.orderTypeFl = ?';
                $this->db->bind_param_push($this->arrBind, 's', $val);
            }
            $this->arrWhere[] = '(' . implode(' OR ', $tmpWhere) . ')';
            unset($tmpWhere);
        } else {
            $this->checked['orderTypeFl'][''] = 'checked="checked"';
        }

        // 주문채널
        if ($this->search['orderChannelFl'][0]) {
            foreach ($this->search['orderChannelFl'] as $val) {
                if ($val == 'paycoShopping') {
                    $tmpWhere[] = "o.trackingKey <> ''";
                } else {
                    $tmpWhere[] = 'o.orderChannelFl = ?';
                    $this->db->bind_param_push($this->arrBind, 's', $val);
                }
                $this->checked['orderChannelFl'][$val] = 'checked="checked"';
            }
            $this->arrWhere[] = '(' . implode(' OR ', $tmpWhere) . ')';
            unset($tmpWhere);
        } else {
            $this->checked['orderChannelFl'][''] = 'checked="checked"';
        }

        // 반품/교환/환불신청 처리상태
        if ($isUserHandle) {
            $orderUserHandleSort = [];
            $orderUserHandleSort['ouh.regDt desc'] = sprintf('%s↓', __('신청일'));
            $orderUserHandleSort['ouh.regDt asc'] = sprintf('%s↑', __('신청일'));
            $this->search['sortList'] = ArrayUtils::insertArrayByPosition($this->search['sortList'], $orderUserHandleSort, 2, true);
            $orderUserHandleCombineTreatDate = [];
            $orderUserHandleCombineTreatDate['ouh.regDt'] = __('신청일');
            $this->search['combineTreatDate'] = ArrayUtils::insertArrayByPosition(   $this->search['combineTreatDate'], $orderUserHandleCombineTreatDate, 1, true);

            // 필수 조건으로 반품/교환/환불신청 건만 출력하도록 설정
            $this->arrWhere[] = 'og.userHandleSno > 0';

            // 반품/교환/환불신청 모드가 있는 경우만 출력
            if ($this->search['userHandleMode'] != null) {
                $this->arrWhere[] = 'ouh.userHandleMode = ?';
                $this->db->bind_param_push($this->arrBind, 's', $this->search['userHandleMode']);
                unset($tmpWhere);
            }

            // 검색 조건에 따른 출력
            if ($this->search['userHandleFl'][0]) {
                foreach ($this->search['userHandleFl'] as $val) {
                    $tmpWhere[] = 'ouh.userHandleFl = ?';
                    $this->db->bind_param_push($this->arrBind, 's', $val);
                    $this->checked['userHandleFl'][$val] = 'checked="checked"';
                }
                $this->arrWhere[] = '(' . implode(' OR ', $tmpWhere) . ')';
                unset($tmpWhere);
            } else {
                $this->checked['userHandleFl'][''] = 'checked="checked"';
            }
        }

        // 주문상태
        if ($this->search['orderStatus'][0]) {
            foreach ($this->search['orderStatus'] as $val) {
                // 주문번호별/상품주문번호별 검색조건중 주문상태의 여부에 따라 검색설정 저장이 오작동하는 이슈가 있어 프론트에는 노출되지 않지만 hidden필드로 처리해서 임의로 작동되게 처리 함
                if ($this->search['view'] === 'orderGoods' || $this->search['statusMode'] !== null) {
                    $tmpWhere[] = 'og.orderStatus = ?';
                    $this->db->bind_param_push($this->arrBind, 's', $val);
                }
                $this->checked['orderStatus'][$val] = 'checked="checked"';
            }
            if ($this->search['view'] === 'orderGoods' || $this->search['statusMode'] !== null) {
                $this->arrWhere[] = '(' . implode(' OR ', $tmpWhere) . ')';
            }
            unset($tmpWhere);
        } else {
            $this->checked['orderStatus'][''] = 'checked="checked"';
        }

        // 차지백 서비스건만 검색
        if ($this->search['pgChargeBack']) {
            $this->arrWhere[] = ' o.pgChargeBack=? ';
            $this->db->bind_param_push($this->arrBind, 's', $this->search['pgChargeBack']);
            $this->checked['pgChargeBack'][$this->search['pgChargeBack']] = 'checked="checked"';
        }

        // 처리일자 검색
        if ($this->search['dateSearchFl'] =='y' && $this->search['treatDateFl'] && isset($searchPeriod) && $searchPeriod != -1 && $this->search['treatDate'][0] && $this->search['treatDate'][1]) {
            switch (substr($this->search['treatDateFl'], -2)) {
                case '.b':
                case '.e':
                case '.r':
                    $this->arrWhere[] = ' oh.handleMode=? ';
                    $this->db->bind_param_push($this->arrBind, 's', substr($this->search['treatDateFl'], -1));
                    break;
            }
            $dateField = str_replace(['Dt.r', 'Dt.b', 'Dt.e'], 'Dt', $this->search['treatDateFl']);

            $this->arrWhere[] = $dateField . ' BETWEEN ? AND ?';
            $this->db->bind_param_push($this->arrBind, 's', $this->search['treatDate'][0] . ' ' .$this->search['treatTime'][0]);
            $this->db->bind_param_push($this->arrBind, 's', $this->search['treatDate'][1] . ' ' .$this->search['treatTime'][1]);
        }

        // 결제 방법
        if ($this->search['settleKind'][0]) {
            foreach ($this->search['settleKind'] as $val) {
                if ($val == self::SETTLE_KIND_DEPOSIT) {
                    $tmpWhere[] = 'o.useDeposit > 0';
                } elseif ($val == self::SETTLE_KIND_MILEAGE) {
                    $tmpWhere[] = 'o.useMileage > 0';
                } else {
                    $tmpWhere[] = 'o.settleKind = ?';
                    $this->db->bind_param_push($this->arrBind, 's', $val);
                }
                $this->checked['settleKind'][$val] = 'checked="checked"';
            }
            $this->arrWhere[] = '(' . implode(' OR ', $tmpWhere) . ')';
            unset($tmpWhere);
        } else {
            $this->checked['settleKind'][''] = 'checked="checked"';
        }

        // 결제금액 검색
        if ($this->search['settlePrice'][1]) {
            //            $this->arrWhere[] = '(((og.goodsPrice + og.optionPrice + og.optionTextPrice ) * og.goodsCnt) + og.addGoodsPrice - og.memberDcPrice - og.memberOverlapDcPrice - og.couponGoodsDcPrice - og.divisionUseDeposit - og.divisionUseMileage - og.divisionGoodsDeliveryUseDeposit - og.divisionGoodsDeliveryUseMileage + od.deliveryCharge) BETWEEN ? AND ?';
            $this->arrWhere[] = '(o.settlePrice BETWEEN ? AND ?)';
            $this->db->bind_param_push($this->arrBind, 'i', $this->search['settlePrice'][0]);
            $this->db->bind_param_push($this->arrBind, 'i', $this->search['settlePrice'][1]);
        }

        // 회원여부 및 그룹별 검색
        if ($this->search['memFl']) {
            if ($this->search['memFl'] == 'y') {
                // 회원그룹선택
                if (is_array($this->search['memberGroupNo'])) {
                    foreach ($this->search['memberGroupNo'] as $val) {
                        $tmpWhere[] = 'm.groupSno = ?';
                        $this->db->bind_param_push($this->arrBind, 's', $val);
                    }
                    $this->arrWhere[] = '(' . implode(' OR ', $tmpWhere) . ')';
                    unset($tmpWhere);
                } else if ($this->search['memberGroupNo'] > 1) {
                    $this->arrWhere[] = 'm.groupSno = ?';
                    $this->db->bind_param_push($this->arrBind, 'i', $this->search['memberGroupNo']);
                }

                // 회원만
                $this->arrWhere[] = 'o.memNo > 0';
            } elseif ($this->search['memFl'] == 'n') {
                $this->arrWhere[] = 'o.memNo = 0';
            }
        }

        // 첫주문 검색
        if ($this->search['firstSaleFl'] == 'y') {
            $this->arrWhere[] = 'o.firstSaleFl = ?';
            $this->db->bind_param_push($this->arrBind, 's', $this->search['firstSaleFl']);
        }

        // 영수증 검색
        if ($this->search['receiptFl']) {
            $this->arrWhere[] = 'o.receiptFl = ?';
            $this->db->bind_param_push($this->arrBind, 's', $this->search['receiptFl']);
        }

        // 배송정보 검색 (사은품 포함)
        if ($this->search['withGiftFl'] == 'y') {
            $this->arrWhere[] = '(SELECT COUNT(sno) FROM ' . DB_ORDER_GIFT . ' WHERE orderNo = og.orderNo) > 0';
        }

        // 배송정보 검색 (배송메시지 입력)
        if ($this->search['withMemoFl'] == 'y') {
            $this->arrWhere[] = 'oi.orderMemo != \'\'';
        }

        // 상품º주문번호별 메모 (관리자 메모 입력)
        if ($this->search['withAdminMemoFl'] == 'y') {
            if($this->search['orderMemoCd']){
                $this->arrWhere[] = 'aogm.memoCd=?';
                $this->db->bind_param_push($this->arrBind, 's', $this->search['orderMemoCd']);
            }else{
                $this->arrWhere[] = 'aogm.memoCd != \'\'';
            }
            //$this->arrWhere[] = 'o.adminMemo != \'\'';
        }

        // 배송정보 검색 (묶음배송)
        if ($this->search['withPacket'] == 'y') {
            $this->arrWhere[] = 'oi.packetCode != \'\'';
        }

        // 입금경과일
        if ($this->search['overDepositDay'] > 0) {
            $this->arrWhere[] = 'og.orderStatus = \'o1\' AND og.regDt < ?';
            $this->db->bind_param_push($this->arrBind, 's', date('Y-m-d', strtotime('-' . $this->search['overDepositDay'] . ' day')) . ' 00:00:00');
        }

        // 배송지연일
        if ($this->search['underDeliveryDay'] > 0) {
            $includeStatusCode = ['p', 'g'];
            $arrWhereOrderStatusArray = $this->getIncludeOrderStatus($this->orderStatus, $includeStatusCode);
            $this->arrWhere[] = 'og.orderStatus IN (\'' . implode('\',\'', array_keys($arrWhereOrderStatusArray)) . '\') AND og.paymentDt < ?';
            $this->db->bind_param_push($this->arrBind, 's', date('Y-m-d', strtotime('-' . $this->search['underDeliveryDay'] . ' day')) . ' 00:00:00');
            unset($arrWhereOrderStatusArray);

            // 주문상태 체크하기
            unset($this->checked['orderStatus']);
            $this->checked['orderStatus']['p1'] =
            $this->checked['orderStatus']['g1'] =
            $this->checked['orderStatus']['g2'] =
            $this->checked['orderStatus']['g3'] =
            $this->checked['orderStatus']['g4'] =
                'checked="checked"';

            //TODO 추후 주문단위 리스트 생기면 작업?
            if ($this->search['underDeliveryOrder'] == 'y') {}
        }

        // 송장번호 검색
        if ($this->search['invoiceCompanySno'] > 0) {
            $this->arrWhere[] = 'og.invoiceCompanySno=?';
            $this->db->bind_param_push($this->arrBind, 's', $this->search['invoiceCompanySno']);
        }

        // 송장번호 유무 체크
        if ($this->search['invoiceNoFl'] === 'y') {
            $this->arrWhere[] = 'og.invoiceNo<>\'\'';
        } elseif ($this->search['invoiceNoFl'] === 'n') {
            $this->arrWhere[] = 'og.invoiceNo=\'\'';
        }

        if($this->search['couponAllFl'] === 'y'){
            //쿠폰사용 주문 전체 검색
            $this->arrWhere[] = '(o.totalCouponGoodsDcPrice > 0 OR o.totalCouponOrderDcPrice > 0 OR o.totalCouponDeliveryDcPrice > 0)';
        }
        else {
            // 쿠폰 검색
            if ($this->search['couponNo'] > 0) {
                $this->arrWhere[] = 'mc.couponNo=?';
                $this->db->bind_param_push($this->arrBind, 's', $this->search['couponNo']);
            }
        }

        // 공급사 정산 검색
        if ($this->search['scmAdjustNo']) {
            if ($this->search['scmAdjustType'] == 'oa') {
                $this->arrWhere[] = 'og.scmAdjustAfterNo = ?';
                $this->db->bind_param_push($this->arrBind, 'i', $this->search['scmAdjustNo']);
            } else if ($this->search['scmAdjustType'] == 'o') {
                $this->arrWhere[] = 'og.scmAdjustNo = ?';
                $this->db->bind_param_push($this->arrBind, 'i', $this->search['scmAdjustNo']);
            } else if ($this->search['scmAdjustType'] == 'da') {
                $this->arrWhere[] = 'od.scmAdjustAfterNo = ?';
                $this->db->bind_param_push($this->arrBind, 'i', $this->search['scmAdjustNo']);
            } else if ($this->search['scmAdjustType'] == 'd') {
                $this->arrWhere[] = 'od.scmAdjustNo = ?';
                $this->db->bind_param_push($this->arrBind, 'i', $this->search['scmAdjustNo']);
            }
        }

        // 배송 검색
        if ($this->search['invoiceFl']) {
            if ($this->search['invoiceFl'] == 'y') $this->arrWhere[] = 'og.invoiceNo !=""';
            else if ($this->search['invoiceFl'] == 'n') $this->arrWhere[] = 'og.invoiceNo =""';
            else $this->arrWhere[] = 'TRIM(oi.receiverCellPhone) NOT REGEXP \'^([0-9]{3,4})-?([0-9]{3,4})-?([0-9]{4})$\'';

            $this->checked['invoiceFl'][$this->search['invoiceFl']] = 'checked="checked"';
        } else {
            $this->checked['invoiceFl'][''] = 'checked="checked"';
        }

        // 매입처 검색
        if (($this->search['purchaseNo'] && $this->search['purchaseNoNm'])) {
            if (is_array($this->search['purchaseNo'])) {
                foreach ($this->search['purchaseNo'] as $val) {
                    $tmpWhere[] = 'og.purchaseNo = ?';
                    $this->db->bind_param_push($this->arrBind, 's', $val);
                }
                $this->arrWhere[] = '(' . implode(' OR ', $tmpWhere) . ')';
                unset($tmpWhere);
            }
        }

        //매입처 미지정
        if ($this->search['purchaseNoneFl']) {
            $this->arrWhere[] = '(og.purchaseNo IS NULL OR og.purchaseNo  = "" OR og.purchaseNo  <= 0)';
        }

        // 브랜드 검색
        if (($this->search['brandCd'] && $this->search['brandCdNm']) || $this->search['brand']) {
            if (!$this->search['brandCd'] && $this->search['brand'])
                $this->search['brandCd'] = $this->search['brand'];
            $this->arrWhere[] = 'g.brandCd = ?';
            $this->db->bind_param_push($this->arrBind, $fieldTypeGoods['brandCd'], $this->search['brandCd']);
        }
        else {
            $this->search['brandCd'] = '';
        }

        //브랜드 미지정
        if ($this->search['brandNoneFl']) {
            $this->arrWhere[] = 'g.brandCd  = ""';
        }

        if (empty($this->arrBind)) {
            $this->arrBind = null;
        }
    }

    /**
     * 관리자 주문 리스트
     * 반품/교환/환불 정보까지 한번에 가져올 수 있게 되어있다.
     *
     * @param string  $searchData   검색 데이타
     * @param string  $searchPeriod 기본 조회 기간
     * @param boolean $isUserHandle
     *
     * @return array 주문 리스트 정보
     */
    public function getOrderListForAdmin($searchData, $searchPeriod, $isUserHandle = false)
    {
        if(trim($searchData['orderAdminGridMode']) !== ''){
            //주문리스트 그리드 설정
            $orderAdminGrid = \App::load('\\Component\\Order\\OrderAdminGrid');
            $this->orderGridConfigList = $orderAdminGrid->getSelectOrderGridConfigList($searchData['orderAdminGridMode']);
        }

        // --- 검색 설정
        $this->_setSearch($searchData, $searchPeriod, $isUserHandle);

        // 주문번호별로 보기
        $isDisplayOrderGoods = ($this->search['view'] !== 'order');// view모드가 orderGoods & orderGoodsSimple이 아닌 경우 true
        $this->search['searchPeriod'] = gd_isset($searchData['searchPeriod']);

        // --- 페이지 기본설정
        gd_isset($searchData['page'], 1);
        gd_isset($searchData['pageNum'], 20);
        $page = \App::load('\\Component\\Page\\Page', $searchData['page'],0,0,$searchData['pageNum']);
        $page->setCache(true)->setUrl(\Request::getQueryString()); // 페이지당 리스트 수

        // 주문상태 정렬 예외 케이스 처리
        if ($searchData['sort'] == 'og.orderStatus asc') {
            $searchData['sort'] = 'case LEFT(og.orderStatus, 1) when \'o\' then \'01\' when \'p\' then \'02\' when \'g\' then \'03\' when \'d\' then \'04\' when \'s\' then \'05\' when \'e\' then \'06\' when \'b\' then \'07\' when \'r\' then \'08\' when \'c\' then \'09\' when \'f\' then \'10\' else \'11\' end';
        } elseif ($searchData['sort'] == 'og.orderStatus desc') {
            $searchData['sort'] = 'case LEFT(og.orderStatus, 1) when \'f\' then \'01\' when \'c\' then \'02\' when \'r\' then \'03\' when \'b\' then \'04\' when \'e\' then \'05\' when \'s\' then \'06\' when \'d\' then \'07\' when \'g\' then \'08\' when \'p\' then \'09\' when \'o\' then \'10\' else \'11\' end';
        }

        if($isDisplayOrderGoods){
            if(trim($searchData['sort']) !== ''){
                $orderSort = $searchData['sort'] . ', og.orderDeliverySno asc';
            }
            else {
                if($this->isUseMultiShipping === true){
                    $orderSort = $this->orderGoodsMultiShippingOrderBy;
                }
                else {
                    $orderSort = $this->orderGoodsOrderBy;
                }
            }
        }
        else {
            $orderSort = gd_isset($searchData['sort'], 'og.orderNo desc');
        }

        //상품준비중 리스트에서 묶음배송 정렬 기준
        if(preg_match("/packetCode/", $orderSort)){
            if(preg_match("/desc/", $orderSort)){
                $orderSort = "oi.packetCode desc, og.orderNo desc";
            }
            else {
                $orderSort = "oi.packetCode desc, og.orderNo asc";
            }
        }
        //복수배송지 사용시 배송지별 묶음
        if($this->isUseMultiShipping === true){
            if(!preg_match("/orderInfoCd/", $orderSort)){
                $orderSort = $orderSort . ", oi.orderInfoCd asc";
            }
        }

        $arrIncludeOh = [
            'handleMode',
            'beforeStatus',
            'refundMethod',
            'handleReason',
            'handleDetailReason',
            'regDt AS handleRegDt',
            'handleDt',
        ];
        $arrIncludeOi = [
            'orderName',
            'receiverName',
            'orderMemo',
            'orderCellPhone',
            'packetCode',
            'smsFl',
        ];


        $tmpField[] = ['oh.regDt AS handleRegDt'];
        $tmpField[] = DBTableField::setTableField('tableOrderHandle', $arrIncludeOh, null, 'oh');
        $tmpField[] = DBTableField::setTableField('tableOrderInfo', $arrIncludeOi, null, 'oi');
        $tmpField[] = ['oi.sno AS orderInfoSno'];

        // join 문
        $join[] = ' LEFT JOIN ' . DB_ORDER . ' o ON o.orderNo = og.orderNo ';
        $join[] = ' LEFT JOIN ' . DB_ORDER_HANDLE . ' oh ON og.handleSno = oh.sno AND og.orderNo = oh.orderNo ';
        $join[] = ' LEFT JOIN ' . DB_ORDER_DELIVERY . ' od ON og.orderDeliverySno = od.sno ';
        $join[] = ' LEFT JOIN ' . DB_ORDER_INFO . ' oi ON (og.orderNo = oi.orderNo)   
                    AND (CASE WHEN od.orderInfoSno > 0 THEN od.orderInfoSno = oi.sno ELSE oi.orderInfoCd = 1 END)';

        if(($this->search['key'] =='all' && empty($this->search['keyword']) === false)  || $this->search['key'] =='sm.companyNm' || strpos($orderSort, "sm.companyNm ") !== false || $this->multiSearchScmJoinFl) {
            $join[] = ' LEFT JOIN ' . DB_SCM_MANAGE . ' sm ON og.scmNo = sm.scmNo ';

        }

        if((($this->search['key'] =='all' && empty($this->search['keyword']) === false)  || $this->search['key'] =='pu.purchaseNm') && gd_is_plus_shop(PLUSSHOP_CODE_PURCHASE) === true && gd_is_provider() === false || $this->multiSearchPurchaseJoinFl) {
            $join[] = ' LEFT JOIN ' . DB_PURCHASE . ' pu ON og.purchaseNo = pu.purchaseNo ';
        }

        if(($this->search['key'] =='all' && empty($this->search['keyword']) === false) || $this->search['key'] =='m.nickNm' || $this->search['key'] =='m.memId' || ($this->search['memFl'] =='y' && empty($this->search['memberGroupNo']) === false ) || $this->multiSearchMemberJoinFl) {
            $join[] = ' LEFT JOIN ' . DB_MEMBER . ' m ON o.memNo = m.memNo AND m.memNo > 0 ';
        }

        //상품 브랜드 코드 검색
        if(empty($this->search['brandCd']) === false || empty($this->search['brandNoneFl'])=== false) {
            $join[] = ' LEFT JOIN ' . DB_GOODS . ' as g ON og.goodsNo = g.goodsNo ';
        }

        //택배 예약 상태에 따른 검색
        if ($this->search['invoiceReserveFl']) {
            $join[] = ' LEFT JOIN ' . DB_ORDER_GODO_POST . ' ogp ON ogp.invoiceNo = og.invoiceNo ';
        }

        // 쿠폰검색시만 join
        if ($this->search['couponNo'] > 0) {
            $join[] = ' LEFT JOIN ' . DB_ORDER_COUPON . ' oc ON o.orderNo = oc.orderNo ';
            $join[] = ' LEFT JOIN ' . DB_MEMBER_COUPON . ' mc ON mc.memberCouponNo = oc.memberCouponNo ';
        }

        // 반품/교환/환불신청 사용에 따른 리스트 별도 처리 (조건은 검색 메서드 참고)
        if ($isUserHandle) {

            $arrIncludeOuh = [
                'sno',
                'userHandleMode',
                'userHandleFl',
                'userHandleGoodsNo',
                'userHandleGoodsCnt',
                'userHandleReason',
                'userHandleDetailReason',
                'adminHandleReason',
            ];
            $tmpField[] = ['ouh.regDt AS userHandleRegDt','ouh.sno AS userHandleNo'];
            $tmpField[] = DBTableField::setTableField('tableOrderUserHandle', $arrIncludeOuh, null, 'ouh');
            $joinOrderStatusArray = $this->getExcludeOrderStatus($this->orderStatus, $this->statusUserClaimRequestCode);
            $join[] = ' LEFT JOIN ' . DB_ORDER_USER_HANDLE . ' ouh ON (og.userHandleSno = ouh.sno || (og.sno = ouh.userHandleGoodsNo && og.orderStatus IN (\'' . implode('\',\'', array_keys($joinOrderStatusArray)) . '\')))';
        }
        // @kookoo135 고객 클레임 신청 주문 제외
        if ($this->search['userHandleViewFl'] == 'y') {
            if (!$isDisplayOrderGoods) {
                $this->arrWhere[] = ' NOT EXISTS (SELECT 1 FROM ' . DB_ORDER_USER_HANDLE . ' WHERE o.orderNo = orderNo AND userHandleFl = \'r\')';
            } else {
                $this->arrWhere[] = ' NOT EXISTS (SELECT 1 FROM ' . DB_ORDER_USER_HANDLE . ' WHERE (og.userHandleSno = sno OR og.sno = userHandleGoodsNo) AND userHandleFl = \'r\')';
            }
        }

        // 상품º주문번호별 메모 검색시
        if($this->search['withAdminMemoFl'] == 'y'){
            $join[] = ' LEFT JOIN ' . DB_ADMIN_ORDER_GOODS_MEMO . ' aogm ON o.orderNo = aogm.orderNo ';
        }

        // 쿼리용 필드 합침
        $tmpKey = array_keys($tmpField);
        $arrField = [];
        foreach ($tmpKey as $key) {
            $arrField = array_merge($arrField, $tmpField[$key]);
        }
        unset($tmpField, $tmpKey);

        // 현 페이지 결과
        $this->db->strField = 'og.sno,og.orderNo,og.goodsNo,og.scmNo ,og.mallSno ,og.purchaseNo ,o.memNo, o.trackingKey, o.orderTypeFl, o.appOs, o.pushCode ,' . implode(', ', $arrField) . ',og.orderDeliverySno';
        // addGoods 필드 변경 처리 (goods와 동일해서)

        $this->db->strJoin = implode('', $join);
        $this->db->strWhere = implode(' AND ', gd_isset($this->arrWhere));
        $this->db->strOrder = $orderSort;
        if (!$isDisplayOrderGoods) {
            if($searchData['statusMode'] === 'o'){
                // 입금대기리스트 > 주문번호별 에서 '주문상품명' 을 입금대기 상태의 주문상품명만으로 노출시키기 위해 개수를 구함
                $this->db->strField .= ', SUM(IF(LEFT(og.orderStatus, 1)=\'o\', 1, 0)) AS noPay';
            }
            $this->db->strField .= ', o.regDt, SUM(IF(LEFT(og.orderStatus, 1)=\'o\' OR LEFT(og.orderStatus, 1)=\'p\' OR LEFT(og.orderStatus,1)=\'g\', 1, 0)) AS noDelivery';
            $this->db->strField .= ', SUM(IF(LEFT(og.orderStatus, 1)=\'d\' AND og.orderStatus != \'d2\', 1, 0)) AS deliverying';
            $this->db->strField .= ', SUM(IF(og.orderStatus=\'d2\' OR LEFT(og.orderStatus, 1)=\'s\', 1, 0)) AS deliveryed';
            $this->db->strField .= ', SUM(IF(LEFT(og.orderStatus, 1)=\'c\', 1, 0)) AS cancel';
            $this->db->strField .= ', SUM(IF(LEFT(og.orderStatus, 1)=\'e\', 1, 0)) AS exchange';
            $this->db->strField .= ', SUM(IF(LEFT(og.orderStatus, 1)=\'b\', 1, 0)) AS back';
            $this->db->strField .= ', SUM(IF(LEFT(og.orderStatus, 1)=\'r\', 1, 0)) AS refund';

            $this->db->strGroup = 'og.orderNo';
        }

        gd_isset($searchData['useStrLimit'], true);
        if ($searchData['useStrLimit']) {
            $this->db->strLimit = $page->recode['start'] . ',' . $searchData['pageNum'];
        }

        $query = $this->db->query_complete();
        $strSQL = 'SELECT ' . array_shift($query) . ' FROM ' . DB_ORDER_GOODS . ' og ' . implode(' ', $query);
        $getData = $this->db->query_fetch($strSQL, $this->arrBind);

        // 검색 레코드 수
        $query['group'] = 'GROUP BY og.orderNo';
        unset($query['order']);
        if($page->hasRecodeCache('total') === false) {
            if (Manager::isProvider()) {
                // 검색된 주문의 개수
                $total = $this->db->query_fetch('SELECT COUNT(og.sno) AS cnt FROM ' . DB_ORDER_GOODS . ' og ' . implode(' ', str_ireplace('limit ' . $page->recode['start'] . ',' . $searchData['pageNum'], '', $query)), $this->arrBind, true);

                // 검색된 주문 총 배송비 금액
                $priceDeliveryQuery = $query;
                if(trim($query['group']) !== ''){
                    $priceDeliveryQuery['group'] = $query['group'] . ', og.orderDeliverySno';
                }
                else {
                    $priceDeliveryQuery['group'] = 'GROUP BY og.orderNo, og.orderDeliverySno';
                }

                $providerPriceQueryArr = [];
                $providerPriceQueryArr[] = 'SELECT';
                $providerPriceQueryArr[] = '(od.realTaxSupplyDeliveryCharge + od.realTaxVatDeliveryCharge + od.realTaxFreeDeliveryCharge + od.divisionDeliveryUseDeposit + od.divisionDeliveryUseMileage) AS deliveryPrice';
                $providerPriceQueryArr[] = 'FROM ' . DB_ORDER_GOODS . ' og';
                $providerPriceQueryArr[] = implode(' ', str_ireplace('limit ' . $page->recode['start'] . ',' . $searchData['pageNum'], '', $priceDeliveryQuery));
                $providerPriceQuery = implode(' ', $providerPriceQueryArr);
                $providerTotalDeliveryPrice = $this->db->query_fetch($providerPriceQuery, $this->arrBind, true);
                if(count($total) > 0){
                    $total[0]['price'] += array_sum(array_column($providerTotalDeliveryPrice, 'deliveryPrice'));
                }

                // 검색된 주문 총 상품 금액
                $priceQuery = $query;
                if(trim($query['where']) !== ''){
                    $priceQuery['where'] = str_replace("WHERE ", "WHERE og.orderStatus != 'r3' AND ", $query['where']);
                }
                else {
                    $priceQuery['where'] = "WHERE og.orderStatus != 'r3'";
                }

                $providerPriceQueryArr = [];
                $providerPriceQueryArr[] = 'SELECT';
                $providerPriceQueryArr[] = 'SUM((og.goodsPrice + og.optionPrice + og.optionTextPrice) * og.goodsCnt) AS price';
                $providerPriceQueryArr[] = 'FROM ' . DB_ORDER_GOODS . ' og';
                $providerPriceQueryArr[] = implode(' ', str_ireplace('limit ' . $page->recode['start'] . ',' . $searchData['pageNum'], '', $priceQuery));
                $providerPriceQuery = implode(' ', $providerPriceQueryArr);
                $providerTotalPrice = $this->db->query_fetch($providerPriceQuery, $this->arrBind, true);

                if(count($total) > 0){
                    $total[0]['price'] += array_sum(array_column($providerTotalPrice, 'price'));
                }
            }
            else {
                $total = $this->db->query_fetch('SELECT (o.realTaxSupplyPrice + o.realTaxFreePrice + o.realTaxVatPrice) AS price, COUNT(og.sno) AS cnt FROM ' . DB_ORDER_GOODS . ' og ' . implode(' ', str_ireplace('limit ' . $page->recode['start'] . ',' . $searchData['pageNum'], '', $query)), $this->arrBind, true);
            }

            $page->recode['totalPrice'] = array_sum(array_column($total, 'price'));
        }

        if ($isDisplayOrderGoods) {
            $ogSno = 'og.sno';
            $groupby = '';
            $page->recode['total'] = array_sum(array_column($total, 'cnt'));
            $this->search['deliveryFl'] = true;
        } else {
            $ogSno = 'og.orderNo';
            $groupby = ' GROUP BY og.orderNo';
            $page->recode['total'] = count($total);
        }

        // 주문상태에 따른 전체 갯수
        if($page->hasRecodeCache('amount') === false) {
            if (Manager::isProvider()) {
                if ($this->search['statusMode'] !== null) {
                    $total = $this->db->query_fetch('SELECT COUNT(' . $ogSno . ') as total FROM ' . DB_ORDER_GOODS . ' og WHERE og.scmNo=' . Session::get('manager.scmNo') . ' AND (og.orderStatus LIKE concat(\'' . $this->search['statusMode'] . '\',\'%\'))' . $groupby, null, true);
                } else {
                    $total = $this->db->query_fetch('SELECT COUNT(' . $ogSno . ') as total FROM ' . DB_ORDER_GOODS . ' og WHERE og.scmNo=' . Session::get('manager.scmNo') . ' AND LEFT(og.orderStatus, 1) NOT IN (\'o\', \'c\') AND og.orderStatus != \'' . $this->arrBind[1] . '\'' . $groupby, null, true);
                }
            } else {
                if ($this->search['statusMode'] !== null) {
                    $total = $this->db->query_fetch('SELECT COUNT(' . $ogSno . ') as total FROM ' . DB_ORDER_GOODS . ' og WHERE (og.orderStatus LIKE concat(\'' . $this->search['statusMode'] . '\',\'%\'))' . $groupby, null, true);
                } else if ($searchData['navTabs'] && $searchData['memNo']) { // CRM 주문관리 회원일련번호기준 갯수
                    $total = $this->db->query_fetch('SELECT COUNT(' . $ogSno . ') as total FROM ' . DB_ORDER_GOODS . ' og LEFT JOIN ' . DB_ORDER . ' o ON o.orderNo = og.orderNo WHERE o.memNo = ' . $searchData['memNo'] . ' AND (og.orderStatus != \'' . $this->arrBind[1] . '\') AND og.orderStatus != \'f1\'' . $groupby, null, true);
                } else {
                    $total = $this->db->query_fetch('SELECT COUNT(' . $ogSno . ') as total FROM ' . DB_ORDER_GOODS . ' og WHERE (og.orderStatus != \'' . $this->arrBind[1] . '\') AND og.orderStatus != \'f1\'' . $groupby, null, true);
                }
            }
        }

        // 주문상태/상품주분번호별 쿼리에 따른 전체갯수 처리
        if ($isDisplayOrderGoods) {
            $total = array_shift($total);
        } else {
            $total['total'] = count($total);
        }

        $page->recode['amount'] = $total['total'];

        $page->setPage(null,['totalPrice']);

        return $this->setOrderListForAdmin($getData, $isUserHandle, $isDisplayOrderGoods, true, $searchData['statusMode']);
    }

    /**
     * 관리자앱 for 고도몰5 주문 리스트
     * 반품/교환/환불 정보까지 한번에 가져오거나 상태별로 가져올 수 있게 되어있다.
     *
     * @param string  $searchData   검색 데이타
     * @param string  $searchPeriod 기본 조회 기간
     * @param boolean $isUserHandle
     *
     * @return array 주문 리스트 정보
     */
    public function getOrderListForAdminMobileapp($searchData, $searchPeriod, $isUserHandle = false)
    {
        // 검색 셀렉트 박스 기본값
        $this->getMobileappSearch();

        // 상품주문번호별 탭을 제외하고는 처리상태 정렬 제거
        if ($isUserHandle === false) {
            unset($this->search['sortList']['og.orderStatus desc'], $this->search['sortList']['og.orderStatus asc']);
        }

        // 상품주문번호별 탭을 제외하고는 처리상태 정렬 제거
        if ($isUserHandle === false) {
            unset($this->search['sortList']['og.orderStatus desc'], $this->search['sortList']['og.orderStatus asc']);
        }

        // 상태별 조회 기준 날짜
        $statusCodeSearchDate = [
            'all' => 'og.regDt',
            'o' => 'og.regDt',
            'f' => 'og.regDt',
            'p' => 'og.paymentDt',
            'g' => 'og.paymentDt',
            'd1' => 'og.deliveryDt',
            'd2' => 'og.deliveryCompleteDt',
            's' => 'og.finishDt',
            'c' => 'og.cancelDt',
            'b' => 'oh.regDt',
            'eo' => 'oh.regDt',
            'e' => 'oh.regDt',
            'z' => 'oh.regDt',
            'r' => 'oh.regDt',
        ];
        $this->search['treatDateFl'] = gd_isset($searchData['treatDateFl'], $statusCodeSearchDate[$searchData['statusMode']]);

        // 검색기간 설정
        $data = gd_policy('order.defaultSearch');
        $searchPeriod = gd_isset($data['searchPeriod'], 1);

        // 결제방식 재처리
        if ($searchData['settleKind'] == 'all') {
            $searchData['settleKind'] = [
                'gb', 'fa', 'pb', 'fb', 'eb', 'pc', 'fc', 'ph', 'fh', 'pv', 'fv', 'ev', 'gz', 'gd', 'gm','ec','fp','gr', 'pk'
            ];
        } else {
            $searchData['settleKind'] = explode(',', $searchData['settleKind']);
        }

        // 정렬방식 고정
        $searchData['sort'] = 'o.regDt desc';

        // 주문상태처리
        if ($searchData['statusMode'] == 'all') {
            $searchData['statusMode'] = '';
        }

        // 검색일 처리
        foreach ($searchData['treatDate'] as $key => $val) {
            $searchData['treatDate'][$key] = substr($val, 0, 4) . '-' . substr($val, 4, 2) . '-' . substr($val, 6, 2);
        }

        // --- 검색 설정
        $this->search['mallFl'] = gd_isset($searchData['mallFl'], 'all');
        $this->search['exceptOrderStatus'] = gd_isset($searchData['exceptOrderStatus']);    //예외처리할 주문상태
        $this->search['detailSearch'] = gd_isset($searchData['detailSearch'], 'y');
        $this->search['statusMode'] = gd_isset($searchData['statusMode']);
        $this->search['key'] = gd_isset($searchData['key']);
        $this->search['keyword'] = gd_isset($searchData['keyword']);
        $this->search['sort'] = gd_isset($searchData['sort']);
        $this->search['orderStatus'] = gd_isset($searchData['orderStatus']);
        $this->search['processStatus'] = gd_isset($searchData['processStatus']);
        $this->search['userHandleMode'] = gd_isset($searchData['userHandleMode']);
        $this->search['userHandleFl'] = gd_isset($searchData['userHandleFl']);
        $this->search['treatDateFl'] = gd_isset($searchData['treatDateFl'], 'og.regDt');
        $this->search['treatDate'][] = gd_isset($searchData['treatDate'][0], date('Y-m-d', strtotime('-' . $searchPeriod . ' day')));
        $this->search['treatDate'][] = gd_isset($searchData['treatDate'][1], date('Y-m-d'));
        $this->search['settleKind'] = gd_isset($searchData['settleKind']);
        $this->search['settlePrice'][] = gd_isset($searchData['settlePrice'][0]);
        $this->search['settlePrice'][] = gd_isset($searchData['settlePrice'][1]);
        $this->search['memFl'] = gd_isset($searchData['memFl']);
        $this->search['memberGroupNo'] = gd_isset($searchData['memberGroupNo']);
        $this->search['memberGroupNoNm'] = gd_isset($searchData['memberGroupNoNm']);
        $this->search['receiptFl'] = gd_isset($searchData['receiptFl']);
        $this->search['orderTypeFl'] = gd_isset($searchData['orderTypeFl'], ['']);
        $this->search['orderChannelFl'] = gd_isset($searchData['orderChannelFl'], ['']);
        $this->search['scmFl'] = gd_isset($searchData['scmFl'], 'all');
        // 공급사 선택 후 공급사가 없는 경우
        if ($searchData['scmNo'] == 0 && $searchData['scmFl'] == 1) {
            $this->search['scmFl'] = 'all';
        }
        $this->search['scmNo'] = gd_isset($searchData['scmNo']);
        $this->search['scmNoNm'] = gd_isset($searchData['scmNoNm']);
        $this->search['scmAdjustNo'] = gd_isset($searchData['scmAdjustNo']);
        $this->search['scmAdjustType'] = gd_isset($searchData['scmAdjustType']);
        $this->search['manualPayment'] = gd_isset($searchData['manualPayment'], '');
        $this->search['invoiceFl'] = gd_isset($searchData['invoiceFl'], '');
        $this->search['firstSaleFl'] = gd_isset($searchData['firstSaleFl'], 'n');
        $this->search['withGiftFl'] = gd_isset($searchData['withGiftFl'], 'n');
        $this->search['withMemoFl'] = gd_isset($searchData['withMemoFl'], 'n');
        $this->search['withAdminMemoFl'] = gd_isset($searchData['withAdminMemoFl'], 'n');
        $this->search['withPacket'] = gd_isset($searchData['withPacket'], 'n');
        $this->search['overDepositDay'] = gd_isset($searchData['overDepositDay']);
        $this->search['invoiceCompanySno'] = gd_isset($searchData['invoiceCompanySno'], 0);
        $this->search['invoiceNoFl'] = gd_isset($searchData['invoiceNoFl']);
        $this->search['underDeliveryDay'] = gd_isset($searchData['underDeliveryDay']);
        $this->search['underDeliveryOrder'] = gd_isset($searchData['underDeliveryOrder'], 'n');
        $this->search['couponNo'] = gd_isset($searchData['couponNo']);
        $this->search['couponNoNm'] = gd_isset($searchData['couponNoNm']);
        $this->search['eventNo'] = gd_isset($searchData['eventNo']);
        $this->search['eventNoNm'] = gd_isset($searchData['eventNoNm']);
        $this->search['dateSearchFl'] = gd_isset($searchData['dateSearchFl'],'y');

        if (DateTimeUtils::intervalDay($this->search['treatDate'][0], $this->search['treatDate'][1]) > 365) {
            throw new AlertBackException(__('1년이상 기간으로 검색하실 수 없습니다.'));
        }

        // 주문/주문상품 탭 설정
        $this->search['view'] = gd_isset($searchData['view'], 'order');

        // CRM
        $this->search['memNo'] = gd_isset($searchData['memNo'], null);

        // --- 검색 설정
        $this->checked['mallFl'][$this->search['mallFl']] =
        $this->checked['scmFl'][$this->search['scmFl']] =
        $this->checked['memFl'][$this->search['memFl']] =
        $this->checked['manualPayment'][$this->search['manualPayment']] =
        $this->checked['firstSaleFl'][$this->search['firstSaleFl']] =
        $this->checked['withGiftFl'][$this->search['withGiftFl']] =
        $this->checked['withMemoFl'][$this->search['withMemoFl']] =
        $this->checked['withAdminMemoFl'][$this->search['withAdminMemoFl']] =
        $this->checked['withPacket'][$this->search['withPacket']] =
        $this->checked['underDeliveryOrder'][$this->search['underDeliveryOrder']] =
        $this->checked['invoiceNoFl'][$this->search['invoiceNoFl']] =
        $this->checked['receiptFl'][$this->search['receiptFl']] = 'checked="checked"';
        $this->checked['periodFl'][$searchPeriod] = 'active';

        // 회원 주문인 경우 (CRM 주문조회)
        if ($this->search['memNo'] !== null) {
            $this->arrWhere[] = 'o.memNo = ?';
            $this->db->bind_param_push($this->arrBind, 's', $this->search['memNo']);
        }

        // 주문 상태 모드가 있는 경우
        $sCountWhere = '';
        if ($this->search['statusMode'] != 'all' && $this->search['statusMode'] != '') {
            $tmp = explode(',', $this->search['statusMode']);
            $aCountWhere = [];
            foreach ($tmp as $val) {
                if ($val == 'eo') {
                    $val = 'e';
                }
                $tmpWhere[] = 'og.orderStatus LIKE concat(?,\'%\')';
                $this->db->bind_param_push($this->arrBind, 's', $val);
                $aCountWhere[] .= 'og.orderStatus LIKE concat(\'' . $val . '\' ,\'%\')';
            }
            $this->arrWhere[] = '(' . implode(' OR ', $tmpWhere) . ')';
            $sCountWhere = '(' . implode(' OR ', $aCountWhere) . ') ';
            unset($tmpWhere);
        } else {
            // 결제시도 무조건 제거
            $this->arrWhere[] = 'og.orderStatus != ?';
            $this->db->bind_param_push($this->arrBind, 's', 'f1');
            $sCountWhere = "og.orderStatus != 'f1' ";
        }

        // 멀티상점 선택
        $this->arrWhere[] = 'o.mallSno = ?';
        $this->db->bind_param_push($this->arrBind, 's', '1');

        // 키워드 검색
        if ($this->search['key'] && $this->search['keyword']) {
            $keyword = $this->search['keyword'];
            if ($this->search['key'] == 'all') {
                $tmpWhere = array_keys($this->search['combineSearch']);
                array_shift($tmpWhere);
                $arrWhereAll = [];
                foreach ($tmpWhere as $keyNm) {
                    // 전화번호인 경우 -(하이픈)이 없어도 검색되도록 처리
                    if (strpos($keyNm, 'Phone') !== false) {
                        $keyword = StringUtils::numberToPhone($keyword, true);
                    } else {
                        $keyword = $this->search['keyword'];
                    }
                    $arrWhereAll[] = '(' . $keyNm . ' LIKE concat(\'%\',?,\'%\'))';
                    $this->db->bind_param_push($this->arrBind, 's', $keyword);
                }
                $this->arrWhere[] = '(' . implode(' OR ', $arrWhereAll) . ')';
                unset($tmpWhere);
            } else {
                $this->arrWhere[] = $this->search['key'] . ' LIKE concat(\'%\',?,\'%\')';
                // 전화번호인 경우 -(하이픈)이 없어도 검색되도록 처리
                if (strpos($this->search['key'], 'Phone') !== false) {
                    $keyword = StringUtils::numberToPhone($keyword, true);
                } else {
                    $keyword = $this->search['keyword'];
                }
                $this->db->bind_param_push($this->arrBind, 's', $keyword);
            }
        }

        // 주문상태
        if ($this->search['orderStatus'][0]) {
            foreach ($this->search['orderStatus'] as $val) {
                // 주문번호별/상품주문번호별 검색조건중 주문상태의 여부에 따라 검색설정 저장이 오작동하는 이슈가 있어 프론트에는 노출되지 않지만 hidden필드로 처리해서 임의로 작동되게 처리 함
                if ($this->search['view'] === 'orderGoods' || $this->search['statusMode'] !== null) {
                    $tmpWhere[] = 'og.orderStatus = ?';
                    $this->db->bind_param_push($this->arrBind, 's', $val);
                }
                $this->checked['orderStatus'][$val] = 'checked="checked"';
            }
            if ($this->search['view'] === 'orderGoods' || $this->search['statusMode'] !== null) {
                $this->arrWhere[] = '(' . implode(' OR ', $tmpWhere) . ')';
            }
            unset($tmpWhere);
        } else {
            $this->checked['orderStatus'][''] = 'checked="checked"';
        }

        // 처리일자 검색
        if ($this->search['dateSearchFl'] =='y' && $this->search['treatDateFl'] && isset($searchPeriod) && $searchPeriod != -1 && $this->search['treatDate'][0] && $this->search['treatDate'][1]) {
            switch (substr($this->search['treatDateFl'], -2)) {
                case '.b':
                case '.e':
                case '.r':
                    $this->arrWhere[] = ' oh.handleMode=? ';
                    $this->db->bind_param_push($this->arrBind, 's', substr($this->search['treatDateFl'], -1));
                    break;
            }
            $dateField = str_replace(['Dt.r', 'Dt.b', 'Dt.e'], 'Dt', $this->search['treatDateFl']);

            $this->arrWhere[] = $dateField . ' BETWEEN ? AND ?';
            $this->db->bind_param_push($this->arrBind, 's', $this->search['treatDate'][0] . ' 00:00:00');
            $this->db->bind_param_push($this->arrBind, 's', $this->search['treatDate'][1] . ' 23:59:59');
        }

        // 결제 방법
        if ($this->search['settleKind'][0]) {
            foreach ($this->search['settleKind'] as $val) {
                if ($val == self::SETTLE_KIND_DEPOSIT) {
                    $tmpWhere[] = 'o.useDeposit > 0';
                } elseif ($val == self::SETTLE_KIND_MILEAGE) {
                    $tmpWhere[] = 'o.useMileage > 0';
                } else {
                    $tmpWhere[] = 'o.settleKind = ?';
                    $this->db->bind_param_push($this->arrBind, 's', $val);
                }
                $this->checked['settleKind'][$val] = 'checked="checked"';
            }
            $this->arrWhere[] = '(' . implode(' OR ', $tmpWhere) . ')';
            unset($tmpWhere);
        } else {
            $this->checked['settleKind'][''] = 'checked="checked"';
        }

        if (empty($this->arrBind)) {
            $this->arrBind = null;
        }

        // 주문번호별로 보기
        $isDisplayOrderGoods = false;// 모바일앱은 주문별이라 false고정

        // --- 페이지 기본설정
        gd_isset($searchData['page'], 1);
        gd_isset($searchData['pageNum'], 5);
        $page = \App::load('\\Component\\Page\\Page', $searchData['page']);
        $page->page['list'] = $searchData['pageNum']; // 페이지당 리스트 수
        $page->setPage();
        $page->setUrl(\Request::getQueryString());

        // 정렬 설정
        $orderSort = gd_isset($searchData['sort'], 'o.regDt desc');

        // 사용 필드
        $arrIncludeOg = [
            'apiOrderGoodsNo',
            'goodsNo',
            'scmNo',
            'commission',
            'goodsType',
            'orderNo',
            'orderCd',
            'userHandleSno',
            'handleSno',
            'orderStatus',
            'goodsNm',
            'goodsCnt',
            'goodsPrice',
            'optionPrice',
            'optionTextPrice',
            'addGoodsPrice',
            'divisionUseDeposit',
            'divisionUseMileage',
            'divisionCouponOrderDcPrice',
            'goodsDcPrice',
            'memberDcPrice',
            'memberOverlapDcPrice',
            'couponGoodsDcPrice',
            'goodsDeliveryCollectPrice',
            'goodsDeliveryCollectFl',
            'optionInfo',
            'optionTextInfo',
            'orderDeliverySno',
            'invoiceCompanySno',
            'invoiceNo',
            'addGoodsCnt',
            'paymentDt',
            'cancelDt',
            'timeSaleFl',
            'checkoutData',
        ];
        $arrIncludeO = [
            'orderNo',
            'apiOrderNo',
            'mallSno',
            'orderGoodsNm',
            'orderGoodsCnt',
            'memNo',
            'settlePrice',
            'totalGoodsPrice',
            'settleKind',
            'receiptFl',
            'bankSender',
            'bankAccount',
            'escrowDeliveryFl',
            'orderTypeFl',
            'orderChannelFl',
            'firstSaleFl',
            'useMileage',
            'useDeposit',
            //'adminMemo'
        ];
        $arrIncludeOh = [
            'handleMode',
            'beforeStatus',
            'refundMethod',
            'handleReason',
            'handleDetailReason',
        ];
        $arrIncludeOd = [
            'deliverySno',
            'deliveryCharge',
            'deliveryPolicyCharge',
            'deliveryAreaCharge',
            'deliveryMethod',
            'divisionDeliveryUseMileage',
            'divisionDeliveryUseDeposit',
        ];
        $arrIncludeOi = [
            'orderName',
            'receiverName',
            'orderMemo',
        ];
        $arrIncludeG = [
            'imagePath',
            'imageStorage',
            'stockFl',
        ];
        // 추가상품의 이미지 추출을 위해 별도 하단에서 처리
        //        $arrIncludeAg = [
        //            'imagePath',
        //            'imageStorage',
        //            'imageNm',
        //        ];
        $arrIncludeGi = ['imageName'];
        $arrIncludeM = [
            'memId',
            'nickNm',
            'groupSno',
            'cellPhone',
        ];
        $arrIncludeMg = [
            'groupNm',
        ];
        $arrIncludeSm = [
            'scmNo',
            'companyNm',
        ];
        $arrIncludeMm = [
            'domainFl',
            'mallName',
            'mall'
        ];
        $tmpField[] = DBTableField::setTableField('tableOrder', $arrIncludeO, null, 'o');
        $tmpField[] = DBTableField::setTableField('tableOrderGoods', $arrIncludeOg, null, 'og');
        $tmpField[] = DBTableField::setTableField('tableOrderHandle', $arrIncludeOh, null, 'oh');
        $tmpField[] = DBTableField::setTableField('tableOrderInfo', $arrIncludeOi, null, 'oi');
        $tmpField[] = DBTableField::setTableField('tableGoods', $arrIncludeG, null, 'g');
        $tmpField[] = DBTableField::setTableField('tableGoodsImage', $arrIncludeGi, null, 'gi');
        $tmpField[] = DBTableField::setTableField('tableMember', $arrIncludeM, null, 'm');
        $tmpField[] = DBTableField::setTableField('tableMemberGroup', $arrIncludeMg, null, 'mg');
        $tmpField[] = DBTableField::setTableField('tableScmManage', $arrIncludeSm, null, 'sm');
        $tmpField[] = DBTableField::setTableField('tableOrderDelivery', $arrIncludeOd, ['scmNo'], 'od');
        $tmpField[] = DBTableField::setTableField('tableMall', $arrIncludeMm, null, 'mm');

        // join 문
        $join[] = ' LEFT JOIN ' . DB_ORDER . ' o ON o.orderNo = og.orderNo ';
        $join[] = ' LEFT JOIN ' . DB_ORDER_HANDLE . ' oh ON og.handleSno = oh.sno AND og.orderNo = oh.orderNo ';
        $join[] = ' LEFT JOIN ' . DB_GOODS . ' as g ON og.goodsNo = g.goodsNo ';
        $join[] = ' LEFT JOIN ' . DB_GOODS_IMAGE . ' as gi ON og.goodsNo = gi.goodsNo AND gi.imageKind = \'list\'';
        $join[] = ' LEFT JOIN ' . DB_ADD_GOODS . ' as ag ON og.goodsNo = ag.addGoodsNo ';
        $join[] = ' LEFT JOIN ' . DB_SCM_MANAGE . ' sm ON og.scmNo = sm.scmNo ';
        $join[] = ' LEFT JOIN ' . DB_MALL . ' mm ON o.mallSno = mm.sno ';

        $join[] = ' LEFT JOIN ' . DB_ORDER_DELIVERY . ' od ON og.orderDeliverySno = od.sno ';
        $join[] = ' LEFT JOIN ' . DB_ORDER_INFO . ' oi ON (og.orderNo = oi.orderNo)  
                  AND (CASE WHEN od.orderInfoSno > 0 THEN od.orderInfoSno = oi.sno ELSE oi.orderInfoCd = 1 END) ';
        $join[] = ' LEFT JOIN ' . DB_MEMBER . ' m ON o.memNo = m.memNo AND m.memNo > 0 ';
        $join[] = ' LEFT OUTER JOIN ' . DB_MEMBER_GROUP . ' mg ON m.groupSno = mg.sno ';

        // 반품/교환/환불신청 사용에 따른 리스트 별도 처리 (조건은 검색 메서드 참고)
        if ($isUserHandle) {
            $arrIncludeOuh = [
                'sno',
                'userHandleMode',
                'userHandleFl',
                'userHandleGoodsNo',
                'userHandleGoodsCnt',
                'userHandleReason',
                'userHandleDetailReason',
                'adminHandleReason',
            ];
            $addField[] = 'ouh.regDt AS userHandleRegDt';
            $addField[] = 'ouh.sno AS userHandleNo';
            $tmpField[] = array_merge(DBTableField::setTableField('tableOrderUserHandle', $arrIncludeOuh, null, 'ouh'), $addField);
            $join[] = ' LEFT JOIN ' . DB_ORDER_USER_HANDLE . ' ouh ON (og.userHandleSno = ouh.sno || (og.sno = ouh.userHandleGoodsNo && left(og.orderStatus, 1) NOT IN (\'' . implode('\',\'', $this->statusUserClaimRequestCode) . '\')))';
        }

        // 쿼리용 필드 합침
        $tmpKey = array_keys($tmpField);
        $arrField = [];
        foreach ($tmpKey as $key) {
            $arrField = array_merge($arrField, $tmpField[$key]);
        }
        unset($tmpField, $tmpKey);

        // 현 페이지 결과
        $this->db->strField = 'og.sno, oh.regDt AS handleRegDt, ' . implode(', ', $arrField) . ', LEFT(o.orderStatus, 1) as totalStatus, LEFT(og.orderStatus, 1) as statusMode, og.regDt, IF(o.memNo > 0, m.cellPhone, oi.receiverCellPhone) AS smsCellPhone, IF(m.memNo IS NULL, 0, o.memNo) AS memNo, o.memNo AS memNoCheck';

        // addGoods 필드 변경 처리 (goods와 동일해서)
        $this->db->strField .= ', ag.imagePath AS addImagePath, ag.imageStorage AS addImageStorage, ag.imageNm AS addImageName';
        $this->db->strJoin = implode('', $join);
        $this->db->strWhere = implode(' AND ', gd_isset($this->arrWhere));
        $this->db->strOrder = $orderSort;
        if (!$isDisplayOrderGoods) {
            $this->db->strField .= ', SUM(IF(og.orderStatus=\'o1\' OR og.orderStatus=\'p1\' OR LEFT(og.orderStatus,1)=\'g\', 1, 0)) AS noDelivery';
            $this->db->strField .= ', SUM(IF(og.orderStatus=\'d1\', 1, 0)) AS deliverying';
            $this->db->strField .= ', SUM(IF(og.orderStatus=\'d2\' OR LEFT(og.orderStatus, 1)=\'s\', 1, 0)) AS deliveryed';
            $this->db->strField .= ', SUM(IF(LEFT(og.orderStatus, 1)=\'c\', 1, 0)) AS cancel';
            $this->db->strField .= ', SUM(IF(LEFT(og.orderStatus, 1)=\'e\', 1, 0)) AS exchange';
            $this->db->strField .= ', SUM(IF(LEFT(og.orderStatus, 1)=\'b\', 1, 0)) AS back';
            $this->db->strField .= ', SUM(IF(LEFT(og.orderStatus, 1)=\'r\', 1, 0)) AS refund';
            $this->db->strGroup = 'og.orderNo';
        }
        // 공급사 조회사 총 금액 구하기 위한 부분 추가 공급사가 아닌 경우 order 테이블의 totalGoodsPrice 사용
        if (Manager::isProvider() && !$isDisplayOrderGoods) {
            $this->db->strField .= ', SUM((og.goodsPrice + og.optionPrice + og.optionTextPrice) * og.goodsCnt) AS totalGoodsPrice';
        }
        $this->db->strLimit = $page->recode['start'] . ',' . $searchData['pageNum'];

        $query = $this->db->query_complete();
        $strSQL = 'SELECT ' . array_shift($query) . ' FROM ' . DB_ORDER_GOODS . ' og ' . implode(' ', $query);
        $getData = $this->db->query_fetch($strSQL, $this->arrBind);

        // 검색 레코드 수
        $query['group'] = 'GROUP BY og.orderNo';
        if (Manager::isProvider()) {
            $total = $this->db->query_fetch('SELECT SUM((og.goodsPrice + og.optionPrice + og.optionTextPrice) * og.goodsCnt) AS price, COUNT(og.sno) AS cnt FROM ' . DB_ORDER_GOODS . ' og ' . implode(' ', str_ireplace('limit ' . $page->recode['start'] . ',' . $searchData['pageNum'], '', $query)), $this->arrBind, true);
        } else {
            $total = $this->db->query_fetch('SELECT o.settlePrice AS price, COUNT(og.sno) AS cnt FROM ' . DB_ORDER_GOODS . ' og ' . implode(' ', str_ireplace('limit ' . $page->recode['start'] . ',' . $searchData['pageNum'], '', $query)), $this->arrBind, true);
        }
        $page->recode['totalPrice'] = array_sum(array_column($total, 'price'));
        if ($isDisplayOrderGoods) {
            $ogSno = 'og.sno';
            $groupby = '';
            $page->recode['total'] = array_sum(array_column($total, 'cnt'));
        } else {
            $ogSno = 'og.orderNo';
            $groupby = ' GROUP BY og.orderNo';
            $page->recode['total'] = count($total);
        }

        // 주문상태에 따른 전체 갯수
        if (Manager::isProvider()) {
            if ($this->search['statusMode'] !== null) {
                //$total = $this->db->query_fetch('SELECT COUNT(' . $ogSno . ') as total FROM ' . DB_ORDER_GOODS . ' og WHERE og.scmNo=' . Session::get('manager.scmNo') . ' AND ' . $sCountWhere . $groupby, null, true);
            } else {
                //$total = $this->db->query_fetch('SELECT COUNT(' . $ogSno . ') as total FROM ' . DB_ORDER_GOODS . ' og WHERE og.scmNo=' . Session::get('manager.scmNo') . ' AND LEFT(og.orderStatus, 1) NOT IN (\'o\', \'c\') AND ' . $sCountWhere . $groupby, null, true);
            }
        } else {
            if ($this->search['statusMode'] !== null) {
                //$total = $this->db->query_fetch('SELECT COUNT(' . $ogSno . ') as total FROM ' . DB_ORDER_GOODS . ' og WHERE ' . $sCountWhere . $groupby, null, true);
            } else {
                //$total = $this->db->query_fetch('SELECT COUNT(' . $ogSno . ') as total FROM ' . DB_ORDER_GOODS . ' og WHERE ' . $sCountWhere . $groupby, null, true);
            }
        }

        // 주문상태/상품주분번호별 쿼리에 따른 전체갯수 처리
        if ($isDisplayOrderGoods) {
            $total = array_shift($total);
        } else {
            $total['total'] = count($total);
        }

        $total['total'] = 0; // 임시로 // 주문상태에 따른 전체 갯수 에 대한 값을 0으로 할당(모바일앱에선 사용하지않는 쿼리-슬로쿼리발생해서 주석처리)
        $page->recode['amount'] = $total['total'];
        $page->setPage();

        return $this->setOrderListForAdmin($getData, $isUserHandle);
    }

    /**
     * 관리자앱 for 고도몰5 주문 리스트용 search 조건값들
     *
     * @author Noh Jaewon <nokoon@godo.co.kr>
     */
    public function getMobileappSearch() {
        // 주문 상태
        $this->search['stateSearch'] = [
            'all' => '=' . __('주문상태') . '=',
            'o' => __('입금대기'),
            'p' => __('결제완료'),
            'g' => __('상품준비중'),
            'd1' => __('배송중'),
            'd2' => __('배송완료'),
            's' => __('구매확정'),
            'c' => __('취소'),
            'e' => __('교환'),
            'b' => __('반품'),
            'r' => __('환불'),
        ];

        // 통합검색
        $this->search['combineSearch'] = [
            'all' => '=' . __('통합검색') . '=',
            'o.orderNo' => __('주문번호'),
            'oi.orderName' => __('주문자명'),
            'm.memId' => __('아이디'),
            'og.goodsNm' => __('상품명'),
            'og.goodsNo' => __('상품코드'),
        ];

        // 결제방법
        $this->search['settleKind'] = [
            'all' => '=' . __('결제방법') . '=',
            'gb,fa' => __('무통장 입금'),
            'pb,fb,eb' => __('계좌이체'),
            'pc,fc,ec' => __('신용카드'),
            'ph,fh' => __('휴대폰'),
            'pv,fv,ev' => __('가상계좌'),
            'gz' => __('전액할인'),
            'gd' => __('예치금'),
            'gm' => __('마일리지'),
            'fp' => __('포인트'),
            'pk' => __('카카오페이'),
            'gr' => __('기타'),
        ];

        return $this->search;
    }

    /**
     * 관리자앱 for 고도몰5 주문 리스트
     * 모바일앱용 주문리스트에서 상태를 정확히 구분하기위한 카운팅
     *
     * @param string $orderNo 주문번호
     *
     * @return array 주문품목별 카운트
     */
    public function getOrderListCountForAdminMobileapp($orderNo = '')
    {
        if ($orderNo == '') {
            return false;
        }

        // 주문상태에 따른 전체 갯수
        $aCount = $this->db->query_fetch('SELECT left(orderStatus, 1) as orderStatus, count(orderNo) as cnt FROM ' . DB_ORDER_GOODS . ' og WHERE orderStatus REGEXP \'o|p|g|s|f\' AND orderNo = \'' . $orderNo . '\' GROUP BY LEFT(orderStatus, 1)', null, true);

        return $aCount;
    }

    /**
     * 쿼리한 데이터를 실제 리스트에서 사용할 수 있도록 가공 처리
     *
     * @param array $getData
     * @param boolean $isUserHandle
     * @param boolean $isDisplayOrderGoods
     * @param boolean $setInfoFl
     * @param string $searchStatusMode
     *
     * @return mixed
     * @author Jong-tae Ahn <qnibus@godo.co.kr>
     */
    public function setOrderListForAdmin($getData, $isUserHandle = false, $isDisplayOrderGoods = false, $setInfoFl = false, $searchStatusMode = '')
    {
        $delivery = new Delivery();
        $delivery->setDeliveryMethodCompanySno();
        $orderBasic = gd_policy('order.basic');
        if (($orderBasic['userHandleAdmFl'] == 'y' && $orderBasic['userHandleScmFl'] == 'y') === false) {
            unset($orderBasic['userHandleScmFl']);
        }

        //정보가 없을경우 다시 가져올수 있도록 수정
        if(empty($getData[0]['orderGoodsNm']) === true) $setInfoFl = true;

        if($setInfoFl) {
            // 사용 필드
            $arrIncludeOg = [
                'sno',
                'apiOrderGoodsNo',
                'commission',
                'goodsType',
                'orderCd',
                'userHandleSno',
                'handleSno',
                'orderStatus',
                'goodsNm',
                'goodsNmStandard',
                'goodsCnt',
                'goodsPrice',
                'optionPrice',
                'optionTextPrice',
                'addGoodsPrice',
                'divisionUseDeposit',
                'divisionUseMileage',
                'divisionCouponOrderDcPrice',
                'goodsDcPrice',
                'memberDcPrice',
                'memberOverlapDcPrice',
                'couponGoodsDcPrice',
                'goodsDeliveryCollectPrice',
                'goodsDeliveryCollectFl',
                'optionInfo',
                'optionTextInfo',
                'invoiceCompanySno',
                'invoiceNo',
                'addGoodsCnt',
                'paymentDt',
                'cancelDt',
                'timeSaleFl',
                'checkoutData',
                'og.regDt',
                'LEFT(og.orderStatus, 1) as statusMode',
                'deliveryMethodFl',
                'goodsCd',
                'taxVatGoodsPrice',
                'hscode',
                'brandCd',
                'goodsModelNo',
                'costPrice',
                'cancelDt',
                'goodsTaxInfo',
                'makerNm',
                'deliveryDt',
                'deliveryCompleteDt',
            ];

            $arrIncludeO = [
                'orderNo',
                'apiOrderNo',
                'mallSno',
                'orderGoodsNm',
                'orderGoodsNmStandard',
                'orderGoodsCnt',
                'settlePrice',
                'totalGoodsPrice',
                'settleKind',
                'receiptFl',
                'bankSender',
                'bankAccount',
                'escrowDeliveryFl',
                'orderTypeFl',
                'appOs',
                'pushCode',
                'orderChannelFl',
                'firstSaleFl',
                //'adminMemo',
                'o.memNo AS memNoCheck',
                'LEFT(o.orderStatus, 1) as totalStatus',
                'totalDeliveryCharge',
                'useMileage',
                'useDeposit',
                'totalGoodsDcPrice',
                'totalMemberDcPrice',
                'totalMemberOverlapDcPrice',
                'totalCouponGoodsDcPrice',
                'totalCouponOrderDcPrice',
                'totalMemberDeliveryDcPrice',
                'totalCouponDeliveryDcPrice',
                'totalEnuriDcPrice',
                'currencyPolicy',
                'exchangeRatePolicy',
                'useMileage',
                'useDeposit',
                'multiShippingFl',
                'realTaxSupplyPrice',
                'realTaxVatPrice',
                'realTaxFreePrice',
                'checkoutData',
            ];

            // 마이앱 사용에 따른 분기 처리
            if ($this->useMyapp) {
                array_push($arrIncludeO, 'totalMyappDcPrice');
            }

            //주문상품정보
            $strField = implode(",",$arrIncludeOg);
            $strSQL = 'SELECT ' . $strField . ' FROM ' . DB_ORDER_GOODS . ' og  WHERE sno IN ("' . implode('","', array_unique(array_column($getData, 'sno'))) . '")'.gd_isset($strGroup,"");
            $tmpOrderGoodsData = $this->db->query_fetch($strSQL, null);
            $orderGoodsData = array_combine(array_column($tmpOrderGoodsData, 'sno'), $tmpOrderGoodsData);

            //주문정보
            $strSQL = 'SELECT ' . implode(",",$arrIncludeO) . ' FROM ' . DB_ORDER . ' o  WHERE o.orderNo IN ("' . implode('","', array_unique(array_column($getData, 'orderNo'))) . '")';
            $tmpOrderData = $this->db->query_fetch($strSQL, null);
            $orderData = array_combine(array_column($tmpOrderData, 'orderNo'), $tmpOrderData);

            //상품정보
            $strField = "g.goodsNo,g.imagePath,g.imageStorage,g.stockFl, gi.imageName";
            $strSQL = 'SELECT ' . $strField . ' FROM ' . DB_GOODS . ' g LEFT JOIN ' . DB_GOODS_IMAGE . ' gi ON gi.goodsNo = g.goodsNo AND gi.imageKind = \'list\' WHERE g.goodsNo IN ("' . implode('","', array_unique(array_column($getData, 'goodsNo'))) . '")';
            $tmpGoodsData = $this->db->query_fetch($strSQL, null);
            $goodsData = array_combine(array_column($tmpGoodsData, 'goodsNo'), $tmpGoodsData);

            //추가상품 정보
            $strField = "addGoodsNo,ag.imagePath AS addImagePath, ag.imageStorage AS addImageStorage, ag.imageNm AS addImageName";
            $strSQL = 'SELECT ' . $strField . ' FROM ' . DB_ADD_GOODS . ' ag  WHERE addGoodsNo IN ("' . implode('","', array_unique(array_column($getData, 'goodsNo'))) . '")';
            $tmpAddGoodsData = $this->db->query_fetch($strSQL, null);
            $addGoodsData = array_combine(array_column($tmpAddGoodsData, 'addGoodsNo'), $tmpAddGoodsData);

            //공급사 정보
            $strScmSQL = 'SELECT scmNo,companyNm FROM ' . DB_SCM_MANAGE . ' g  WHERE scmNo IN ("' . implode('","', array_unique(array_column($getData, 'scmNo'))) . '")';
            $tmpScmData = $this->db->query_fetch($strScmSQL);
            $scmData = array_combine(array_column($tmpScmData, 'scmNo'), array_column($tmpScmData, 'companyNm'));

            //몰정보
            $strMallSQL = 'SELECT domainFl,mallName,sno FROM ' . DB_MALL . ' mm  WHERE sno IN ("' . implode('","', array_unique(array_column($getData, 'mallSno'))) . '")';
            $tmpMallData = $this->db->query_fetch($strMallSQL);
            $mallData = array_combine(array_column($tmpMallData, 'sno'), $tmpMallData);

            //매입처 정보
            if (gd_is_provider() === false && gd_is_plus_shop(PLUSSHOP_CODE_PURCHASE) === true) {
                $strPurchaseSQL = 'SELECT purchaseNo,purchaseNm FROM ' . DB_PURCHASE . ' g  WHERE delFl = "n" AND purchaseNo IN ("' . implode('","', array_unique(array_column($getData, 'purchaseNo'))) . '")';
                $tmpPurchaseData = $this->db->query_fetch($strPurchaseSQL);
                $purchaseData = array_combine(array_column($tmpPurchaseData, 'purchaseNo'), array_column($tmpPurchaseData, 'purchaseNm'));
            }

            //회원정보
            $strField = "memId,nickNm,groupSno,cellPhone,memNo as memNoUnique,cellPhone,groupNm";
            $strSQL = 'SELECT ' . $strField . ' FROM ' . DB_MEMBER . ' m LEFT JOIN ' . DB_MEMBER_GROUP . ' mg ON  m.groupSno = mg.sno  WHERE m.memNo > 0 AND m.memNo IN ("' . implode('","', array_unique(array_column($getData, 'memNo'))) . '")';
            $tmpMemberData = $this->db->query_fetch($strSQL, null);
            $memberData = array_combine(array_column($tmpMemberData, 'memNoUnique'), $tmpMemberData);

            //배송정보
            $strField = "sno,deliverySno,deliveryCharge,deliveryPolicyCharge,deliveryAreaCharge,deliveryMethod,divisionDeliveryUseMileage,divisionDeliveryUseDeposit,scmNo,orderInfoSno,realTaxSupplyDeliveryCharge,realTaxVatDeliveryCharge,realTaxFreeDeliveryCharge";
            $strSQL = 'SELECT ' . $strField . ' FROM ' . DB_ORDER_DELIVERY . ' od WHERE od.sno IN ("' . implode('","', array_unique(array_column($getData, 'orderDeliverySno'))) . '")';
            $tmpDeliaveryData = $this->db->query_fetch($strSQL, null);
            $deliveryData = array_combine(array_column($tmpDeliaveryData, 'sno'), $tmpDeliaveryData);

            //주문정보 - 배송정보 - 수령자정보
            $strField = "sno, receiverName, receiverZonecode, receiverZipcode, receiverAddress, receiverAddressSub, orderInfoCd, orderNo, orderMemo";
            $infoWhere = '';
            if (Manager::isProvider()) {
                if($isDisplayOrderGoods){
                    //상품주문번호별
                    $infoWhere = ' AND sno IN ("' . implode('","', array_unique(array_column($getData, 'orderInfoSno'))) . '") ';
                }
                else {
                    //주문번호별
                    $strSQL = 'SELECT orderInfoSno, orderNo FROM ' . DB_ORDER_DELIVERY . ' WHERE scmNo = '.Session::get('manager.scmNo').' AND orderNo IN ("' . implode('","', array_unique(array_column($getData, 'orderNo'))) . '")';
                    $tmpAllOrderDeliveryData = $this->db->query_fetch($strSQL, null);
                    $infoWhere = ' AND sno IN ("' . implode('","', array_unique(array_column($tmpAllOrderDeliveryData, 'orderInfoSno'))) . '") ';
                }
            }
            $strSQL = 'SELECT ' . $strField . ' FROM ' . DB_ORDER_INFO . ' WHERE orderNo IN ("' . implode('","', array_unique(array_column($getData, 'orderNo'))) . '") '.$infoWhere.' ORDER BY orderInfoCd ASC';

            $tmpOrderInfoData = $this->db->query_fetch($strSQL, null);
            $orderInfoData = array_combine(array_column($tmpOrderInfoData, 'sno'), $tmpOrderInfoData);
            $orderInfoCountData = array_count_values(array_column($orderInfoData, 'orderNo'));
            $orderMemoData = $orderReceiverNameData = [];
            if(count($orderInfoData) > 0){
                //주문번호별 리스트에서 배송지, 수령자의 메인배송지 정보를 알려줌과 동시에 카운트를 알려주기 위해 처리
                $reverseOrderInfoData = $orderInfoData;
                rsort($reverseOrderInfoData);
                foreach($reverseOrderInfoData as $key => $value){
                    if($value['orderMemo']){
                        $orderMemoData[$value['orderNo']]['orderMemo'] = $value['orderMemo'];
                        $orderMemoData[$value['orderNo']]['orderMemoCount'] += 1;
                    }
                    if($value['receiverName']){
                        $orderReceiverNameData[$value['orderNo']]['receiverName'] = $value['receiverName'];
                        $orderReceiverNameData[$value['orderNo']]['receiverNameCount'] += 1;
                    }
                }
                unset($reverseOrderInfoData, $tmpOrderInfoData);
            }

            //리스트 그리드 항목에 브랜드가 있을경우 브랜드 정보 포함
            if(array_key_exists('brandNm', $this->orderGridConfigList)){
                $brandData = [];
                $brand = \App::load('\\Component\\Category\\Brand');
                $brandOriginalData = $brand->getCategoryData(null, null, 'cateNm');
                if(count($brandOriginalData) > 0){
                    $brandData = array_combine(array_column($brandOriginalData, 'cateCd'), array_column($brandOriginalData, 'cateNm'));
                }
            }
        }

        if (gd_isset($getData)) {
            $giftList = [];
            // 주문번호에 따라 배열 처리
            if($setInfoFl) {
                foreach ($getData as $key => &$val) {
                    //상품정보
                    if($orderData[$val['orderNo']]) $val = $val+$orderData[$val['orderNo']];
                    if($orderGoodsData[$val['sno']]) $val = $val+$orderGoodsData[$val['sno']];
                    if($goodsData[$val['goodsNo']]) $val = $val+$goodsData[$val['goodsNo']];
                    if($addGoodsData[$val['goodsNo']]) $val = $val+$addGoodsData[$val['goodsNo']];
                    if($deliveryData[$val['orderDeliverySno']]) $val = $val+$deliveryData[$val['orderDeliverySno']];
                    if($orderInfoData[$val['orderInfoSno']]) $val = $val+$orderInfoData[$val['orderInfoSno']];

                    if($mallData[$val['mallSno']]) $val = $val+$mallData[$val['mallSno']];
                    if($memberData[$val['memNo']]) $val = $val+$memberData[$val['memNo']];
                    $val['smsCellPhone'] = $val['memNo'] > 0 ? $val['cellPhone'] : $val['receiverCellPhonec'];
                    $val['memNo'] = is_null($val['memNo']) ?  0 : $val['memNoUnique'];

                    $val['companyNm']= $scmData[$val['scmNo']];
                    $val['purchaseNm']= $purchaseData[$val['purchaseNo']];
                    $val['brandNm'] = $brandData[$val['brandCd']];

                    // 주문유형
                    if ($val['orderTypeFl'] == 'pc') {
                        $val['orderTypeFlNm'] = 'PC쇼핑몰';
                    } else if ($val['orderTypeFl'] == 'mobile') {
                        if (empty($val['appOs']) === true && empty($val['pushCode']) === true) {
                            $val['orderTypeFlNm'] = '모바일쇼핑몰<br>(WEB)';
                        } else {
                            $val['orderTypeFlNm'] = '모바일쇼핑몰<br>(APP)';
                        }
                    } else {
                        $val['orderTypeFlNm'] = '수기주문';
                    }

                    if (empty($val['orderNo']) === false) {
                        // json형태의 경우 json값안에 "이있는경우 stripslashes처리가 되어 json_decode에러가 나므로 json값중 "이 들어갈수있는경우 $aCheckKey에 해당 필드명을 추가해서 처리해주세요
                        $aCheckKey = ['optionTextInfo'];
                        foreach ($val as $k => $v) {
                            if (!in_array($k, $aCheckKey)) {
                                $val[$k] = gd_htmlspecialchars_stripslashes($v);
                            }
                        }
                        if ($orderBasic['userHandleFl'] == 'y' && (!Manager::isProvider() && $orderBasic['userHandleAdmFl'] == 'y') || (Manager::isProvider() && $orderBasic['userHandleScmFl'] == 'y')) {
                            if ($this->search['userHandleViewFl'] != 'y') {
                                if ($isDisplayOrderGoods) {
                                    $val['userHandleInfo'] = $this->getUserHandleInfo($val['orderNo'], $val['sno'], [$this->search['userHandleMode']]);
                                } else {
                                    $val['userHandleInfo'] = $this->getUserHandleInfo($val['orderNo'], null, [$this->search['userHandleMode']]);
                                }

                            }
                        }
                        $data[$val['orderNo']]['goods'][] = $val;

                        // 상품º주문번호별 메모 등록여부 체크
                        $ordGoodsMemoInfo = $this->getAdminOrdGoodsMemoToPrint($val['orderNo']);
                        $data[$val['orderNo']]['adminOrdGoodsMemo'] = $ordGoodsMemoInfo ;
                    }
                }
            } else {
                foreach ($getData as $key => $val) {
                    if (empty($val['orderNo']) === false) {
                        // json형태의 경우 json값안에 "이있는경우 stripslashes처리가 되어 json_decode에러가 나므로 json값중 "이 들어갈수있는경우 $aCheckKey에 해당 필드명을 추가해서 처리해주세요
                        $aCheckKey = ['optionTextInfo'];
                        foreach ($val as $k => $v) {
                            if (!in_array($k, $aCheckKey)) {
                                $val[$k] = gd_htmlspecialchars_stripslashes($v);
                            }
                        }
                        if ($orderBasic['userHandleFl'] == 'y' && (!Manager::isProvider() && $orderBasic['userHandleAdmFl'] == 'y') || (Manager::isProvider() && $orderBasic['userHandleScmFl'] == 'y')) {
                            if ($isDisplayOrderGoods) {
                                $val['userHandleInfo'] = $this->getUserHandleInfo($val['orderNo'], $val['sno'], [$this->search['userHandleMode']]);
                            } else {
                                $val['userHandleInfo'] = $this->getUserHandleInfo($val['orderNo'], null, [$this->search['userHandleMode']]);
                            }
                        }
                        $data[$val['orderNo']]['goods'][] = $val;
                    }
                }
            }


            //복수배송지 사용 여부에 따라 페이지 노출시 scmNo 의 키를 order info sno 로 교체한다.
            $orderMultiShipping = App::load('\\Component\\Order\\OrderMultiShipping');
            $useMultiShippingKey = $orderMultiShipping->checkChangeOrderListKey();

            // 결제방법과 처리 상태 설정
            foreach ($data as $key => &$val) {
                $orderGoods = $val['goods'];
                unset($val['goods']);
                foreach ($orderGoods as $oKey => &$oVal) {
                    if($oVal['deliveryMethodFl']){
                        $oVal['deliveryMethodFlText'] = $delivery->deliveryMethodList['name'][$oVal['deliveryMethodFl']];
                        $oVal['deliveryMethodFlSno'] = $delivery->deliveryMethodList['sno'][$oVal['deliveryMethodFl']];
                    }
                    // 상품명 태그 제거
                    $oVal['orderGoodsNm'] = StringUtils::stripOnlyTags(html_entity_decode($oVal['orderGoodsNm']));
                    $oVal['goodsNm'] = StringUtils::stripOnlyTags(html_entity_decode($oVal['goodsNm']));

                    // 리스트에서 무조건 해외상점 몰 이름이 한글로 나오도록 강제 변환
                    if ($oVal['mallSno'] > DEFAULT_MALL_NUMBER) {
                        //리스트에 해외몰 주문건에대한 주문상품명르 노출시키기 위해 해외몰 주문상품명유지
                        $oVal['orderGoodsNmGlobal'] = $oVal['orderGoodsNm'];
                        $oVal['goodsNmGlobal'] = $oVal['goodsNm'];

                        if (empty($oVal['orderGoodsNmStandard']) === false) {
                            $oVal['orderGoodsNm'] = StringUtils::stripOnlyTags(html_entity_decode($oVal['orderGoodsNmStandard']));
                        }
                        if (empty($oVal['goodsNmStandard']) === false) {
                            $oVal['goodsNm'] = StringUtils::stripOnlyTags(html_entity_decode($oVal['goodsNmStandard']));
                        }
                    }

                    if(!$isDisplayOrderGoods && $searchStatusMode === 'o') {
                        // 입금대기리스트 > 주문번호별 에서 '주문상품명' 을 입금대기 상태의 주문상품명만 구성
                        $noPay = (int)$oVal['noPay'] - 1;
                        if($noPay > 0){
                            $oVal['orderGoodsNmStandard'] = $oVal['orderGoodsNm'] = $oVal['goodsNm'] . ' 외 ' . $noPay . ' 건';
                        }
                        else {
                            $oVal['orderGoodsNmStandard'] = $oVal['orderGoodsNm'] = $oVal['goodsNm'];
                        }

                        if ($oVal['mallSno'] > DEFAULT_MALL_NUMBER) {
                            if($noPay > 0){
                                $oVal['orderGoodsNmGlobal'] = $oVal['goodsNmGlobal'] . ' ' . __('외') . ' ' . $noPay . ' ' . __('건');
                            }
                            else {
                                $oVal['orderGoodsNmGlobal'] = $oVal['goodsNmGlobal'];
                            }
                        }
                    }

                    //상품진열시에만 실행

                    if($isDisplayOrderGoods) {

                        // 옵션처리
                        // 현재 foreach문의 $data를 할당하면서 이미 gd_htmlspecialchars_stripslashes처리를 하기때문에 여기서는 처리할필요가없음
                        $options = json_decode($oVal['optionInfo'], true);

                        $oVal['optionInfo'] = $options;
                        if ($oVal['orderChannelFl'] == 'naverpay') {
                            $naverPay = new NaverPay();
                            $oVal['checkoutData'] = json_decode($oVal['checkoutData'], true);
                            if ($oVal['checkoutData']['returnData']['ReturnReason']) {
                                $oVal['handleReason'] = $naverPay->getClaimReasonCode($oVal['checkoutData']['returnData']['ReturnReason'], 'back');
                            } else if ($oVal['checkoutData']['exchangeData']['ExchangeReason']) {
                                $oVal['handleReason'] = $naverPay->getClaimReasonCode($oVal['checkoutData']['exchangeData']['ExchangeReason'], 'back');
                            } else if ($oVal['checkoutData']['cancelData']['CancelReason']) {
                                $oVal['handleReason'] = $naverPay->getClaimReasonCode($oVal['checkoutData']['cancelData']['CancelReason'], 'back');
                            }
                        }

                        // 텍스트옵션
                        $textOptions = json_decode($oVal['optionTextInfo'], true);
                        $oVal['optionTextInfo'] = $textOptions;

                        // 배송 택배사 설정
                        $oVal['invoiceCompanyNm'] = $this->getInvoiceCompanyName($oVal['invoiceCompanySno']);
                    }

                    //복수배송지를 사용 중이며 리스트에서 노출시킬 목적으로만 사용중이면 사은품은 단 한번만 저장시킨다.
                    if($useMultiShippingKey === true){
                        if(!$giftList[$key][$oVal['scmNo']]){
                            $oVal['gift'] = $this->getOrderGift($key, $oVal['scmNo'], 40);
                            $giftList[$key][$oVal['scmNo']] = $oVal['gift'];
                        }
                    }
                    else {
                        // 사은품
                        if($giftList[$key]) {
                            $oVal['gift'] = $giftList[$key];
                        }  else {
                            $oVal['gift'] = $this->getOrderGift($key, $oVal['scmNo'], 40);
                            $giftList[$key] = $oVal['gift'];
                        }
                    }

                    // 추가상품
                    $oVal['addGoods'] = $this->getOrderAddGoods(
                        $key,
                        $oVal['orderCd'],
                        [
                            'sno',
                            'addGoodsNo',
                            'goodsNm',
                            'goodsCnt',
                            'goodsPrice',
                            'optionNm',
                            'goodsImage',
                            'addMemberDcPrice',
                            'addMemberOverlapDcPrice',
                            'addCouponGoodsDcPrice',
                            'addGoodsMileage',
                            'addMemberMileage',
                            'addCouponGoodsMileage',
                            'divisionAddUseDeposit',
                            'divisionAddUseMileage',
                            'divisionAddCouponOrderDcPrice',
                        ]
                    );

                    // 추가상품 할인/적립 안분 금액을 포함한 총 금액 (상품별 적립/할인 금액 + 추가상품별 적립/할인 금액)
                    $oVal['totalMemberDcPrice'] = $oVal['memberDcPrice'];
                    $oVal['totalMemberOverlapDcPrice'] = $oVal['memberOverlapDcPrice'];
                    $oVal['totalCouponGoodsDcPrice'] = $oVal['couponGoodsDcPrice'];
                    $oVal['totalGoodsMileage'] = $oVal['goodsMileage'];
                    $oVal['totalMemberMileage'] = $oVal['memberMileage'];
                    $oVal['totalCouponGoodsMileage'] = $oVal['couponGoodsMileage'];
                    $oVal['totalDivisionUseDeposit'] = $oVal['divisionUseDeposit'];
                    $oVal['totalDivisionUseMileage'] = $oVal['divisionUseMileage'];
                    $oVal['totalDivisionCouponOrderDcPrice'] = $oVal['divisionCouponOrderDcPrice'];
                    if (!empty($oVal['addGoods'])) {
                        foreach ($oVal['addGoods'] as $aVal) {
                            $oVal['totalMemberDcPrice'] += $aVal['addMemberDcPrice'];
                            $oVal['totalMemberOverlapDcPrice'] += $aVal['addMemberOverlapDcPrice'];
                            $oVal['totalCouponGoodsDcPrice'] += $aVal['addCouponGoodsDcPrice'];
                            $oVal['totalGoodsMileage'] += $aVal['addGoodsMileage'];
                            $oVal['totalMemberMileage'] += $aVal['addMemberMileage'];
                            $oVal['totalCouponGoodsMileage'] += $aVal['addCouponGoodsMileage'];
                            $oVal['totalDivisionUseDeposit'] += $aVal['divisionAddUseDeposit'];
                            $oVal['totalDivisionUseMileage'] += $aVal['divisionAddUseMileage'];
                            $oVal['totalDivisionCouponOrderDcPrice'] += $aVal['divisionAddCouponOrderDcPrice'];
                        }
                    }

                    // 추가상품 수량 (테이블 UI 처리에 필요)
                    $oVal['addGoodsCnt'] = empty($oVal['addGoods']) ? 0 : count($oVal['addGoods']);

                    // 주문 상태명 설정
                    $oValOrderStatus = $oVal['orderStatus'];
                    if (gd_isset($oValOrderStatus)) {
                        $oVal['beforeStatusStr'] = $this->getOrderStatusAdmin($oVal['beforeStatus']);
                        $oVal['totalStatusStr'] = $this->getOrderStatusAdmin($oVal['totalStatus']);
                        $oVal['settleKindStr'] = $this->printSettleKind($oVal['settleKind']);
                        $oVal['escrowFl'] = substr($oVal['settleKind'], 0, 1);

                        // 반품/교환/환불신청인 경우 해당 상태를 출력
                        if ($isUserHandle) {
                            $oVal['orderStatusStr'] = $this->getUserHandleMode($oVal['userHandleMode'], $oVal['userHandleFl']);
                        } else {
                            $oVal['orderStatusStr'] = $this->getOrderStatusAdmin($oVal['orderStatus']);
                        }
                    }

                    //총 할인금액
                    $totalDcPriceArray = [
                        $orderData[$oVal['orderNo']]['totalGoodsDcPrice'],
                        $orderData[$oVal['orderNo']]['totalMemberDcPrice'],
                        $orderData[$oVal['orderNo']]['totalMemberOverlapDcPrice'],
                        $orderData[$oVal['orderNo']]['totalCouponGoodsDcPrice'],
                        $orderData[$oVal['orderNo']]['totalCouponOrderDcPrice'],
                        $orderData[$oVal['orderNo']]['totalMemberDeliveryDcPrice'],
                        $orderData[$oVal['orderNo']]['totalCouponDeliveryDcPrice'],
                        $orderData[$oVal['orderNo']]['totalEnuriDcPrice'],
                    ];

                    // 마이앱 사용에 따른 분기 처리
                    if ($this->useMyapp) {
                        array_push($totalDcPriceArray, $orderData[$oVal['orderNo']]['totalMyappDcPrice']);
                    }

                    $oVal['totalDcPrice'] = array_sum($totalDcPriceArray);

                    //총 부가결제 금액
                    $oVal['totalUseAddedPrice'] = $orderData[$oVal['orderNo']]['useMileage']+$orderData[$oVal['orderNo']]['useDeposit'];

                    //총 주문 금액 : 총 상품금액 + 총 배송비 - 총 할인금액
                    $oVal['totalOrderPrice'] = $orderData[$oVal['orderNo']]['totalGoodsPrice'] + $orderData[$oVal['orderNo']]['totalDeliveryCharge'] - $oVal['totalDcPrice'];

                    //총 실 결제금액
                    if($oVal['orderChannelFl'] === 'naverpay'){
                        $checkoutData = json_decode($orderData[$oVal['orderNo']]['checkoutData'], true);
                        // 네이버페이 포인트를 사용한 경우 realtax 에 값이 담기지 않아 실금액을 구할 수 없으므로 checkoutData 를 이용한다.
                        if($isDisplayOrderGoods) {
                            // $isDisplayOrderGoods 인 경우 상단에서 이미 decode 처리가 되어 있음
                            $oVal['totalRealSettlePrice'] = $checkoutData['orderData']['GeneralPaymentAmount'];
                        }
                        else {
                            $checkoutData = json_decode($oVal['checkoutData'], true);
                            $oVal['totalRealSettlePrice'] = $checkoutData['orderData']['GeneralPaymentAmount'];
                        }
                    }
                    else {
                        $oVal['totalRealSettlePrice'] = $orderData[$oVal['orderNo']]['realTaxSupplyPrice'] + $orderData[$oVal['orderNo']]['realTaxVatPrice'] + $orderData[$oVal['orderNo']]['realTaxFreePrice'];
                    }

                    // 멀티상점 환율 기본 정보
                    $oVal['currencyPolicy'] = json_decode($oVal['currencyPolicy'], true);
                    $oVal['exchangeRatePolicy'] = json_decode($oVal['exchangeRatePolicy'], true);
                    $oVal['currencyIsoCode'] = $oVal['currencyPolicy']['isoCode'];
                    $oVal['exchangeRate'] = $oVal['exchangeRatePolicy']['exchangeRate' . $oVal['currencyPolicy']['isoCode']];

                    //총 배송지 수
                    $oVal['totalOrderInfoCount'] = $orderInfoCountData[$oVal['orderNo']];

                    //총 배송 메모 수 및 첫번째 메모
                    $oVal['multiShippingOrderMemo'] = $orderMemoData[$oVal['orderNo']]['orderMemo'];
                    $oVal['multiShippingOrderMemoCount'] = $orderMemoData[$oVal['orderNo']]['orderMemoCount'];

                    //총 수령자 수 및 첫번째 수령자
                    $oVal['multiShippingReceiverName'] = $orderReceiverNameData[$oVal['orderNo']]['receiverName'];
                    $oVal['multiShippingReceiverNameCount'] = $orderReceiverNameData[$oVal['orderNo']]['receiverNameCount'];

                    // 주문별 주문상태 카운팅
                    if (!isset($data[$key]['status'])) {
                        $data[$key]['status'] = [];
                    }
                    if (in_array(substr($oVal['orderStatus'], 0, 1), ['o', 'p', 'g', 'f'])) {
                        $data[$key]['status']['noDelivery'] += 1;
                    }
                    if ($oVal['orderStatus'] == 'd1') {
                        $data[$key]['status']['deliverying'] += 1;
                    }
                    if ($oVal['orderStatus'] == 'd2' || substr($oVal['orderStatus'], 0, 1) == 's') {
                        $data[$key]['status']['deliveryed'] += 1;
                    }
                    if (substr($oVal['orderStatus'], 0, 1) == 'c') {
                        $data[$key]['status']['cancel'] += 1;
                    }
                    if (substr($oVal['orderStatus'], 0, 1) == 'e') {
                        $data[$key]['status']['exchange'] += 1;
                    }
                    if (substr($oVal['orderStatus'], 0, 1) == 'b') {
                        $data[$key]['status']['back'] += 1;
                    }
                    if (substr($oVal['orderStatus'], 0, 1) == 'r') {
                        $data[$key]['status']['refund'] += 1;
                    }

                    // 데이터 SCM/Delivery 3차 배열로 재구성
                    if($useMultiShippingKey === true){
                        //복수배송지를 사용 중이며 리스트에서 노출시킬 목적으로만 사용중이면 주문데이터 배열의 scmNo 를 orderInfoSno로 대체한다.
                        $data[$key]['goods'][$oVal['orderInfoSno']][$oVal['orderDeliverySno']][$oKey] = $oVal;
                    }
                    else {
                        $data[$key]['goods'][$oVal['scmNo']][$oVal['deliverySno']][$oKey] = $oVal;
                    }

                    // 테이블 UI 표현을 위한 변수
                    if (!isset($data[$key]['cnt'])) {
                        $data[$key]['cnt'] = [];
                    }
                    //복수배송지를 사용 중이며 리스트에서 노출시킬 목적으로만 사용중이면 주문데이터 배열의 scmNo 를 orderInfoSno로 대체한다.
                    if($useMultiShippingKey === true){
                        $data[$key]['cnt']['multiShipping'][$oVal['orderInfoSno']] += 1 + $oVal['addGoodsCnt'];
                    }
                    else {
                        $data[$key]['cnt']['scm'][$oVal['scmNo']] += 1 + $oVal['addGoodsCnt'];
                    }
                    $deliveryUniqueKey = $oVal['deliverySno'] . '-' . $oVal['orderDeliverySno'];
                    $data[$key]['cnt']['delivery'][$deliveryUniqueKey] += 1 + $oVal['addGoodsCnt'];
                    $data[$key]['cnt']['goods']['all'] += 1 + $oVal['addGoodsCnt'];
                    $data[$key]['cnt']['goods']['goods'] += 1;
                    $data[$key]['cnt']['goods']['addGoods'] += $oVal['addGoodsCnt'];
                }

                // 별도의 데이터 추가 실제 총 결제금액 = 주문결제금액 + 배송비
                foreach ($orderGoods as $tKey => $tVal) {
                    $firstKey = $tVal['scmNo'];
                    $secontKey = $tVal['deliverySno'];

                    //복수배송지를 사용 중이며 리스트에서 노출시킬 목적으로만 사용중이면 주문데이터 배열의 scmNo 를 orderInfoSno로 대체한다.
                    if($useMultiShippingKey === true){
                        $firstKey = $tVal['orderInfoSno'];
                        $secontKey = $tVal['orderDeliverySno'];
                    }
                    $data[$key]['goods'][$firstKey][$secontKey][$tKey]['totalSettlePrice'] = $oVal['settlePrice'];
                }

                if (Manager::isProvider()) {
                    $data[$key] = $this->getProviderTotalPriceList($data[$key], $key);
                }
            }

            // 각 데이터 배열화
            $getData['data'] = gd_isset($data);

            unset($giftList);
        }

        // 사용자 교환/반품/환불 신청 여부
        $getData['isUserHandle'] = $isUserHandle;

        // 검색값 설정
        if (empty($this->search) === false) {
            $getData['search'] = gd_htmlspecialchars($this->search);
        }

        // 체크값 설정
        if (empty($this->checked) === false) {
            $getData['checked'] = $this->checked;
        }

        // 리스트 그리드 항목 설정
        if (empty($this->orderGridConfigList) === false) {
            $getData['orderGridConfigList'] = $this->orderGridConfigList;
        }

        //복수배송지를 사용 중이며 리스트에서 노출시킬 목적으로만 사용중이면 주문데이터 배열의 scmNo 를 orderInfoSno로 대체한다. : true 일시
        $getData['useMultiShippingKey'] = $useMultiShippingKey;

        // 페이지 전체값
        $page = \App::load('\\Component\\Page\\Page');
        $getData['amount'] = $page->recode['total'];

        return $getData;
    }

    /**
     * 주문 상태 출력
     * 주로 관리자 주문리스트의 검색박스내 주문상태 체크박스 출력시 사용
     *
     * @param string $statusCode 주문상태 코드 (한자리)
     * @param array $strExclude 제외할 코드 (한자리씩 쉼표(,)로 구분 or 배열)
     * @param array $strSubExclude 제외할 서브코드 (두자리씩 쉼표(,)로 구분)
     * @param array $callType 함수를 호출한 타입
     *
     * @return string 주문 상태 출력
     */
    public function getOrderStatusList($statusCode = null, $strExclude = null, $strSubExclude = null, $callType = null)
    {
        if (is_null($statusCode) && is_null($strExclude)) {
            $arrExclude = $this->standardExcludeCd;
        } else {
            if (is_array($strExclude) === true) {
                $arrExclude = $strExclude;
            } else {
                $arrExclude = explode(',', $strExclude);
            }
        }

        foreach ($this->statusPolicy as $key => $val) {
            if ($key == 'autoCancel') {
                continue;
            }

            // $statusCode 가 있는 경우 해당 주문 상태 분류만 출력
            if ($statusCode !== null) {
                if($statusCode === 'z'){
                    if($key !== 'exchangeAdd'){
                        continue;
                    }
                }
                else if ($statusCode === 'e'){
                    if($key !== 'exchange'){
                        continue;
                    }
                }
                else {
                    if (substr($key, 0, 1) != $statusCode) {
                        continue;
                    }
                }
            }

            foreach ($val as $oKey => $oVal) {
                if ($statusCode === null && in_array(substr($oKey, 0, 1), $arrExclude)) {
                    continue;
                }

                if($callType === 'orderList'){
                    // 주문리스트에서 호출 시 사용여부를 제외하여 체크
                    if (strlen(trim($oKey)) == 2) {
                        // $strSubExclude 가 있는 경우 해당 주문 상태는 제외하고 출력
                        if (null !== $strSubExclude && is_array($strSubExclude) && in_array($oKey, $strSubExclude)) {
                            continue;
                        }
                        $codeArr[$oKey] = $oVal['admin'];
                    }
                }
                else {
                    if (strlen(trim($oKey)) == 2 && $oVal['useFl'] == 'y' && $oVal['mode'] == 'oi') {
                        // $strSubExclude 가 있는 경우 해당 주문 상태는 제외하고 출력
                        if (null !== $strSubExclude && is_array($strSubExclude) && in_array($oKey, $strSubExclude)) {
                            continue;
                        }
                        $codeArr[$oKey] = $oVal['admin'];
                    }
                }
            }
        }

        return gd_isset($codeArr);
    }

    /**
     * 회원 CRM에서 확인하는 주문리스트
     *
     * @return array 주문 리스트 정보
     */
    public function getOrderListForCrm($memNo)
    {
        // --- 정렬 설정
        $sort['fieldName'] = 'o.regDt';
        $sort['sortMode'] = 'desc';

        // --- 페이지 기본설정
        $_GET['page'] = gd_isset($_GET['page'], 1);
        $_GET['page_num'] = gd_isset($_GET['page_num'], 5);

        $page = \App::load('\\Component\\Page\\Page', $_GET['page']);
        $page->page['list'] = $_GET['page_num']; // 페이지당 리스트 수
        $page->setPage();
        $page->setUrl(\Request::getQueryString());

        // 회원 검색
        $this->arrWhere[] = 'o.memNo = ?';
        $this->db->bind_param_push($this->arrBind, 'i', $memNo);

        // 사용 필드
        $arrInclude = [
            'orderNo',
            'apiOrderNo',
            'settlePrice',
            'orderStatus',
            'settleKind',
        ];
        $arrField = DBTableField::setTableField('tableOrder', $arrInclude, null, 'o');

        // 현 페이지 결과
        $this->db->strField = implode(', ', $arrField) . ', o.regDt';
        $this->db->strWhere = implode(' AND ', gd_isset($this->arrWhere));
        $this->db->strOrder = $sort['fieldName'] . ' ' . $sort['sortMode'];
        $this->db->strLimit = $page->recode['start'] . ',' . $_GET['page_num'];

        $query = $this->db->query_complete();
        $strSQL = 'SELECT ' . array_shift($query) . ' FROM ' . DB_ORDER . ' o ' . implode(' ', $query);
        $data = $this->db->query_fetch($strSQL, $this->arrBind);

        // 검색 레코드 수
        unset($query['group'], $query['order'], $query['limit']);
        $strCntSQL = 'SELECT COUNT(*) AS total FROM ' . DB_ORDER . ' AS o ' . implode(' ', $query);
        $page->recode['total'] = $this->db->query_fetch($strCntSQL, $this->arrBind, false)['total'];
        //list($page->recode['total']) = $this->db->fetch('SELECT FOUND_ROWS()', 'row');
        $page->setPage();

        // 결제방법 과 처리 상태 설정
        if (gd_isset($data)) {
            foreach ($data as $key => &$val) {
                if (gd_isset($val['orderStatus'])) {
                    $val['orderStatusStr'] = $this->getOrderStatusAdmin($val['orderStatus']);
                    $val['settleKindStr'] = $this->printSettleKind($val['settleKind']);
                }
            }
        }

        // 총결제 금액과 회원 정보
        $strSQL = 'SELECT m.memId, m.memNm, m.saleAmt FROM ' . DB_MEMBER . ' m WHERE m.memNo = ?';
        $getData['info'] = $this->db->query_fetch($strSQL, $this->arrBind, false);

        // 각 데이터 배열화
        $getData['data'] = gd_htmlspecialchars_stripslashes(gd_isset($data));

        return $getData;
    }

    /**
     * 클래임접수 리스트 출력 (취소, 반품, 교환 클래임 리스트)
     * 주문상세에서 사용
     *
     * @param string $orderNo 주문 번호
     * @param string $handleSno 처리 코드
     *
     * @return array 해당 주문 상품 정보
     */
    public function getOrderListForAdminClaim($orderNo)
    {
        $arrInclude[] = [
            'sno',
            'orderStatus',
            'goodsNo',
            'goodsNm',
            'goodsCnt',
            'goodsPrice',
            'optionAddPrice',
            'optionTextPrice',
            'optionInfo',
            'optionAddInfo',
            'optionTextInfo',
            'minusCoupon',
            'plusCoupon',
            'minusStockFl',
            'minusRestoreStockFl',
            'handleSno',
        ];
        $arrField = 's.companyNm as scmNm, ' . implode(',', DBTableField::setTableField('tableOrderGoods', $arrInclude[0], null, 'og')) . ', oh.sno AS handleNo, ' . implode(',', DBTableField::setTableField('tableOrderHandle', null, null, 'oh'));
        $arrWhere[] = " og.orderNo = '" . $orderNo . "' ";
        $arrWhere[] = " (og.orderStatus LIKE 'c%' OR og.orderStatus in ('b1', 'e1')) ";//취소상품과 반품/교환/환불접수 상품만

        $arrJoin[] = ' LEFT JOIN ' . DB_ORDER_HANDLE . ' AS oh ON oh.sno = og.handleSno AND oh.orderNo = og.orderNo ';
        $arrJoin[] = ' LEFT JOIN ' . DB_SCM_MANAGE . ' as s ON s.scmNo = og.scmNo ';

        $this->db->strField = $arrField;
        $this->db->strWhere = implode(' AND ', gd_isset($arrWhere));
        $this->db->strJoin = implode('', $arrJoin);
        $this->db->strOrder = 'og.sno ASC';

        $query = $this->db->query_complete();
        $strSQL = 'SELECT ' . array_shift($query) . ' FROM ' . DB_ORDER_GOODS . ' as og' . implode(' ', $query);
        $getData = $this->db->query_fetch($strSQL, $this->arrBind);

        // 데이터 재설정
        foreach ($getData as $key => $val) {
            // HTML 제거
            $getData[$key]['goodsNm'] = strip_tags(html_entity_decode($val['goodsNm']));
            $getData[$key]['orderStatusStr'] = $this->getOrderStatusAdmin($val['orderStatus']);
            if (gd_str_length($getData[$key]['refundAccountNumber']) > 50) {
                $getData[$key]['refundAccountNumber'] = \Encryptor::decrypt($getData[$key]['refundAccountNumber']);
            }
        }

        // 데이타 출력
        if (count($getData) > 0) {
            return gd_htmlspecialchars_stripslashes($getData);
        } else {
            return false;
        }
    }

    /**
     * 환불 리스트 출력 (환불 리스트만)
     * 주문상세에서 사용
     *
     * @param string $orderNo 주문 번호
     *
     * @return array 해당 주문 상품 정보
     */
    public function getOrderListForRefund($orderNo)
    {
        $arrInclude[] = [
            'sno',
            'orderStatus',
            'goodsNo',
            'goodsNm',
            'goodsCnt',
            'goodsPrice',
            'optionAddPrice',
            'optionTextPrice',
            'optionInfo',
            'optionAddInfo',
            'optionTextInfo',
            'minusCoupon',
            'plusCoupon',
            'minusStockFl',
            'minusRestoreStockFl',
            'handleSno',
        ];
        $arrField = 's.companyNm as scmNm, ' . implode(',', DBTableField::setTableField('tableOrderGoods', $arrInclude[0], null, 'og')) . ', oh.sno AS handleNo, ' . implode(',', DBTableField::setTableField('tableOrderHandle', null, null, 'oh'));
        $arrWhere[] = " og.handleSno != 0 ";
        $arrWhere[] = " og.orderNo = '" . $orderNo . "' ";
        $arrWhere[] = " og.orderStatus LIKE 'r%' ";//환불접수 상품만

        if (Manager::isProvider()) {
            $arrWhere[] = " og.scmNo = '" . Session::get('manager.scmNo') . "'";
        }

        $arrJoin[] = ' LEFT JOIN ' . DB_ORDER_HANDLE . ' AS oh ON oh.orderNo = og.orderNo AND oh.sno = og.handleSno ';
        $arrJoin[] = ' LEFT JOIN ' . DB_SCM_MANAGE . ' as s ON s.scmNo = og.scmNo ';

        $this->db->strField = $arrField;
        $this->db->strWhere = implode(' AND ', gd_isset($arrWhere));
        $this->db->strJoin = implode('', $arrJoin);
        $this->db->strOrder = 'og.sno ASC';

        $query = $this->db->query_complete();
        $strSQL = 'SELECT ' . array_shift($query) . ' FROM ' . DB_ORDER_GOODS . ' as og' . implode(' ', $query);
        $getData = $this->db->query_fetch($strSQL, $this->arrBind);

        // 데이터 재설정
        foreach ($getData as $key => $val) {
            // HTML 제거
            $getData[$key]['goodsNm'] = strip_tags(html_entity_decode($val['goodsNm']));
            $getData[$key]['orderStatusStr'] = $this->getOrderStatusAdmin($val['orderStatus']);
            if (gd_str_length($getData[$key]['refundAccountNumber']) > 50) {
                $getData[$key]['refundAccountNumber'] = \Encryptor::decrypt($getData[$key]['refundAccountNumber']);
            }
        }

        // 데이타 출력
        if (count($getData) > 0) {
            return gd_htmlspecialchars_stripslashes($getData);
        } else {
            return false;
        }
    }

    /**
     * 환불 금액 출력 (부분 취소를 위한)
     *
     * @todo 재계산해야 함 현재 작동 안함
     *
     * @param string $orderNo 주문 번호
     *
     * @return array 해당 주문 환불 금액
     */
    public function getOrderRefundPrice($orderNo)
    {
        $strSQL = 'SELECT SUM(refundPrice) as price FROM ' . DB_ORDER_HANDLE . ' WHERE orderNo = ? AND handleMode = \'r\' AND handleCompleteFl = \'y\' GROUP BY orderNo';
        $this->db->bind_param_push($arrBind, 's', $orderNo);
        $getData = $this->db->query_fetch($strSQL, $arrBind, false);

        // 금액 리턴
        return $getData['price'];
    }

    /**
     * 주문상담내역 삭제 처리
     *
     * @param integer $sno
     *
     * @author Jong-tae Ahn <qnibus@godo.co.kr>
     * @return boolean
     */
    public function deleteOrderConsult($sno)
    {
        $arrBind = [];
        $this->db->bind_param_push($arrBind, 'i', $sno);
        $result = $this->db->set_delete_db(DB_ORDER_CONSULT, 'sno = ?', $arrBind);
        unset($arrBind);

        return $result !== false ? true : false;
    }

    /**
     * 주문상담내역 가져오기
     *
     * @param $orderNo
     *
     * @return array|object
     * @author Jong-tae Ahn <qnibus@godo.co.kr>
     */
    public function getOrderConsult($orderNo)
    {
        $this->db->bind_param_push($arrBind, 's', $orderNo);
        $arrWhere[] = 'oc.orderNo = ?';

        // join 문
        $arrField = DBTableField::setTableField('tableOrderConsult', null, null, 'oc');
        $arrField[] = 'IF(oc.managerNo<>\'\', (SELECT m.managerNm FROM ' . DB_MANAGER . ' m WHERE m.sno = oc.managerNo), NULL) AS managerNm';
        $arrField[] = 'IF(oc.managerNo<>\'\', (SELECT m.managerId FROM ' . DB_MANAGER . ' m WHERE m.sno = oc.managerNo), NULL) AS managerId';

        $this->db->strField = implode(', ', $arrField) . ', oc.regDt';
        $this->db->strWhere = implode(' AND ', $arrWhere);
        $this->db->strOrder = 'oc.sno DESC';

        $query = $this->db->query_complete();
        $strSQL = 'SELECT oc.sno, ' . array_shift($query) . ',oc.regDt FROM ' . DB_ORDER_CONSULT . ' oc ' . implode(' ', $query);
        $getData = $this->db->query_fetch($strSQL, $arrBind);

        // 데이타 출력
        if (count($getData) > 0) {
            return gd_htmlspecialchars_stripslashes($getData);
        } else {
            return false;
        }
    }

    /**
     * 요청사항/상담메모건 정보 출력
     *
     * @param integer $sno 상담 일련번호
     *
     * @return array 해당 주문의 주문자/수취인 정보
     */
    public function getOrderConsultInfo($sno)
    {
        $arrField = DBTableField::setTableField('tableOrderConsult');
        $strSQL = 'SELECT ' . implode(', ', $arrField) . ' FROM ' . DB_ORDER_CONSULT . ' WHERE sno = ? ORDER BY sno ASC';
        $arrBind = [
            's',
            $sno,
        ];
        $getData = $this->db->query_fetch($strSQL, $arrBind);
        if (count($getData) > 0) {
            return gd_htmlspecialchars_stripslashes($getData);
        } else {
            return false;
        }
    }

    /**
     * 관리자 주문 상세정보
     *
     * @param string $orderNo 주문 번호
     * @param null $orderGoodsNo
     * @param integer $handleSno 반품/교환/환불 테이블 번호
     *
     * @param string $statusMode
     * @param array $excludeStatus 제외할 주문상태 값
     * @param string $orderStatusMode 주문상세페이지 로드시 내역 종류
     *
     * @return array|bool 주문 상세정보
     * @throws Exception
     */
    public function getOrderView($orderNo, $orderGoodsNo = null, $handleSno = null, $statusMode = null, $excludeStatus = null, $orderStatusMode = null)
    {
        // 주문번호 체크
        if (Validator::required($orderNo, true) === false) {
            throw new Exception(__('주문번호은(는) 필수 항목 입니다.'));
        }

        if(trim($orderStatusMode) !== ''){
            //주문리스트 그리드 설정
            $orderAdminGrid = \App::load('\\Component\\Order\\OrderAdminGrid');
            $orderAdminGridMode = $orderAdminGrid->getOrderAdminGridMode($orderStatusMode);
            $this->orderGridConfigList = $orderAdminGrid->getSelectOrderGridConfigList($orderAdminGridMode);
        }

        // 주문 기본 정보
        $getData = $this->getOrderData($orderNo);

        // 주문 데이타 체크
        if (empty($getData) === true) {
            throw new Exception(sprintf(__('[%s] 주문 정보가 존재하지 않습니다.'), $orderNo));
        }

        // 멀티상점 데이터 병합 (멀티상점의 regDt가 병합되 잘못된 데이터가 추가되어 unset 처리함)
        $mallData = $this->getMultiShopName($getData['mallSno']);
        unset($mallData['regDt'], $mallData['modDt']);
        if (empty($mallData) === false) {
            $getData = array_merge($getData, $mallData);
        }

        // 주문 기본 정보
        $orderBasic = gd_policy('order.basic');
        if (($orderBasic['userHandleAdmFl'] == 'y' && $orderBasic['userHandleScmFl'] == 'y') === false) {
            unset($orderBasic['userHandleScmFl']);
        }

        // 리스트 그리드 항목 설정
        if (empty($this->orderGridConfigList) === false) {
            $getData['orderGridConfigList'] = $this->orderGridConfigList;
        }

        // 멀티상점 기준몰 여부
        $getData['isDefaultMall'] = $this->isDefaultMall($getData['mallSno']);

        // 멀티상점 환율 기본 정보
        $getData['currencyPolicy'] = json_decode($getData['currencyPolicy'], true);
        $getData['exchangeRatePolicy'] = json_decode($getData['exchangeRatePolicy'], true);
        $getData['currencyIsoCode'] = $getData['currencyPolicy']['isoCode'];
        $getData['exchangeRate'] = $getData['exchangeRatePolicy']['exchangeRate' . $getData['currencyPolicy']['isoCode']];

        //총 할인금액
        $totalDcPriceArray = [
            $getData['totalGoodsDcPrice'],
            $getData['totalMemberDcPrice'],
            $getData['totalMemberOverlapDcPrice'],
            $getData['totalCouponGoodsDcPrice'],
            $getData['totalCouponOrderDcPrice'],
            $getData['totalMemberDeliveryDcPrice'],
            $getData['totalCouponDeliveryDcPrice'],
            $getData['totalEnuriDcPrice'],
            $getData['totalMyappDcPrice'],
        ];
        $getData['totalDcPrice'] = array_sum($totalDcPriceArray);

        //총 부가결제 금액
        $getData['totalUseAddedPrice'] = $getData['useMileage']+$getData['useDeposit'];

        //총 주문 금액 : 총 상품금액 + 총 배송비 - 총 할인금액
        $getData['totalOrderPrice'] = $getData['totalGoodsPrice'] + $getData['totalDeliveryCharge'] - $getData['totalDcPrice'];

        //총 실 결제금액
        $getData['totalRealSettlePrice'] = $getData['realTaxSupplyPrice'] + $getData['realTaxVatPrice'] + $getData['realTaxFreePrice'];

        // 주문 정보(주문자/수취인) 처리
        $getData['receiverPhone'] = explode('-', $getData['receiverPhone']);
        $getData['receiverCellPhone'] = explode('-', $getData['receiverCellPhone']);

        // PG 결과 처리
        $getData['pgSettleNm'] = explode(STR_DIVISION, $getData['pgSettleNm']);
        $getData['pgSettleCd'] = explode(STR_DIVISION, $getData['pgSettleCd']);

        // 전체 주문 상태
        $getData['orderStatusStr'] = $this->getOrderStatusAdmin($getData['orderStatus']);

        // 주문 채널 정보
        $getData['orderChannelFl'] = $getData['orderChannelFl'];
        $getData['orderChannelFlStr'] = $this->getOrderChannel($getData['orderChannelFl']);

        $getData['batchInvoiceInputDisabled'] = '';
        if ($getData['orderChannelFl'] == 'naverpay') {  //네이버페이 배송일괄처리 기능 막기(배송중,배송완료,구매확정)
            if (in_array(substr($getData['orderStatus'], 0, 1), ['d', 's', 'b'])) {
                $getData['batchInvoiceInputDisabled'] = 'disabled="disabled"';
            }
        }

        if ($getData['receiptFl'] == 'r') {
            // 주문 현금영수증 정보
            $cashReceipt = \App::load('\\Component\\Payment\\CashReceipt');
            $getData['cash'] = $cashReceipt->getOrderCashReceipt($orderNo);
        }

        if ($getData['receiptFl'] == 't') {
            // 주문 세금계산서 정보
            $tax = \App::load('\\Component\\Order\\Tax');
            $getData['tax'] = $tax->getOrderTaxInvoice($orderNo);
        }

        // 주문 사은품 정보 (공급사 로그인시 특정 공급사의 사은품만 가져오기)
        $loginedScmNo = Manager::isProvider() ? Session::get('manager.scmNo') : null;
        $getData['gift'] = $this->getOrderGift($orderNo, $loginedScmNo, 40);
        unset($loginedScmNo);

        //복수배송지 사용 여부에 따라 페이지 노출시 scmNo 의 키를 order info sno 로 교체한다.
        $orderMultiShipping = App::load('\\Component\\Order\\OrderMultiShipping');
        $useMultiShippingKey = $orderMultiShipping->checkChangeOrderListKey($getData['multiShippingFl']);

        // 주문 상품 정보
        $orderGoods = $this->getOrderGoodsData($orderNo, $orderGoodsNo, $handleSno, null, 'admin', true, true, $statusMode, $excludeStatus, false, $useMultiShippingKey);

        //복수배송지를 사용 중이며 리스트에서 노출시킬 목적으로만 사용중이면 주문데이터 배열의 scmNo 를 orderInfoSno로 대체한다. : true 일시
        $getData['useMultiShippingKey'] = $useMultiShippingKey;

        //복수배송지가 사용된 주문건 일시 주문데이터에 order info 데이터를 따로 저장해둔다.
        if($getData['multiShippingFl'] === 'y'){
            $orderInfoSnoArr = [];
            if(Manager::isProvider()){
                foreach ($orderGoods as $scmNo => $dataVal) {
                    foreach ($dataVal as $key => $val) {
                        if($val['scmNo'] != Session::get('manager.scmNo')){
                            continue;
                        }

                        $orderInfoSnoArr[] = $val['orderInfoSno'];
                    }
                }
            }
            $getData['multiShippingList'] = $this->getOrderInfo($orderNo, true, 'orderInfoCd asc', $orderInfoSnoArr);
            if(Manager::isProvider()) {
                $getData = $orderMultiShipping->changeReceiverData($getData, $getData['multiShippingList']);
            }
        }

        // 현재 주문 상태코드 (한자리)
        if (empty($getData['orderStatus']) === false) {
            $getData['statusMode'] = substr($getData['orderStatus'], 0, 1);
        } else {
            // 주문테이블에 상태가 없는 경우 마지막 상품의 주문상태에 따른다
            foreach ($orderGoods as $sVal) {
                foreach ($sVal as $gVal) {
                    if (!in_array($gVal['orderStatus'], $this->statusExcludeCd)) {
                        $getData['statusMode'] = substr($gVal['orderStatus'], 0, 1);
                        break;
                    }
                }
            }
        }

        // 주문 상품이 없는 경우 false 리턴
        if (!$orderGoods) {
            return false;
        }

        // 공급사로 로그인시 합계 금액 재계산을 위해 초기화 처리
        if (Manager::isProvider()) {
            $getData['totalGoodsPrice'] = 0;
        }

        //리스트 그리드 항목에 브랜드가 있을경우 브랜드 정보 포함
        if(array_key_exists('brandNm', $this->orderGridConfigList)){
            $brandData = [];
            $brand = \App::load('\\Component\\Category\\Brand');
            $brandOriginalData = $brand->getCategoryData(null, null, 'cateNm');
            if(count($brandOriginalData) > 0){
                $brandData = array_combine(array_column($brandOriginalData, 'cateCd'), array_column($brandOriginalData, 'cateNm'));
            }
        }

        // 해외배송 보험료 한번만 처리
        $onlyOverseasInsurance = false;

        // 주문상내 금액계산 초기화
        $getData['dashBoardPrice']['settlePrice'] = 0;
        $getData['dashBoardPrice']['dueSettlePrice'] = 0;
        $getData['dashBoardPrice']['cancelPrice'] = 0;
        $getData['dashBoardPrice']['dueCancelPrice'] = 0;
        $getData['dashBoardPrice']['refundPrice'] = 0;
        $getData['dashBoardPrice']['dueRefundPrice'] = 0;

        // 상품리스트 UI 처리용 변수 설정 및 공급사 로그인 여부에 따른 데이터 설정
        $totalProductDiscountAmount = 0; //상품별 할인액(즉시 할인+상품 할인 쿠폰+복수 구매 할인)
        $totalProductImmediateDiscountAmount = 0; //상품별 즉시 할인
        $totalProductProductDiscountAmount = 0; //상품별 상품 할인 쿠폰금액
        $totalSellerBurdenDiscountAmount = 0; //상품별 할인액 중 판매자 비용 부담 금액
        $totalFirstSellerBurdenDiscountAmount = 0; //최초 상품별 할인액 중 판매자 비용 부담 금액
        $totalSellerBurdenProductDiscountAmount = 0; //판매자 부담 상품 할인 쿠폰 금액

        $totalDeliveryDiscountAmount = 0;    //배송비할인금액
        $totalTotalProductAmount = 0;    //상품주문금액(할인전금액)
        $totalTotalPaymentAmount = 0;    //총 결제 금액(할인 적용 후 금액)
        $totalSectionDeliveryFee = 0;    //지역별 추가배송비
        $totalDeliveryFeeAmount = 0; //배송비
        $totalAddGoodsPrice = 0;    //추가상품
        $_naverpayDelivery = [];
        $delivery = new Delivery();
        foreach ($orderGoods as $scmNo => $dataVal) {
            // 공급사로 로그인한 경우 처리 해당 공급사 상품만 보여지도록 수정 처리
            if($useMultiShippingKey !== true){
                if (Manager::isProvider()) {
                    if ($scmNo != Session::get('manager.scmNo')) {
                        continue;
                    }
                }
            }

            foreach ($dataVal as $key => $val) {
                $deliveryPolicy = json_decode($val['deliveryPolicy'], true);
                $val['sameGoodsDeliveryFl'] = $deliveryPolicy['sameGoodsDeliveryFl'];

                // 공급사로 로그인시 합계 금액 재계산
                if (Manager::isProvider()) {
                    //복수배송지를 사용하여 배열의 $scmNo 가 order info sno 로 대체된 경우 $val 로 공급사 체크
                    if($useMultiShippingKey === true){
                        if ($val['scmNo'] != Session::get('manager.scmNo')) {
                            continue;
                        }
                    }
                    if(substr($val['orderStatus'], 0, 1) !== 'e'){
                        $getData['totalGoodsPrice'] += (($val['goodsPrice'] + $val['optionPrice'] + $val['optionTextPrice']) * $val['goodsCnt']) + $val['addGoodsPrice'];
                    }
                }

                // 할인금액
                $currentSettlePrice = (($val['goodsPrice'] + $val['optionPrice'] + $val['optionTextPrice']) * $val['goodsCnt']) + $val['addGoodsPrice'] - ($val['goodsDcPrice'] + $val['totalMemberDcPrice'] + $val['totalMemberOverlapDcPrice'] + $val['totalCouponGoodsDcPrice'] + $val['divisionUseDeposit'] + $val['divisionUseMileage'] + $val['totalDivisionCouponOrderDcPrice'] + $val['enuri']);

                if ($this->useMyapp) { // 마이앱 사용에 따른 분기 처리
                    $currentSettlePrice += $val['myappDcPrice'];
                }

                // 해외배송 보험료 (해당 루프에서 단 한번만 처리)
                if ($onlyOverseasInsurance === false && isset($val['deliveryInsuranceFee']) && $val['deliveryInsuranceFee'] > 0) {
                    $currentSettlePrice += $val['deliveryInsuranceFee'];
                    $onlyOverseasInsurance = true;
                }

                // 주문상세내 금액 계산
                if (!in_array($val['orderStatus'], ['o1', 'c1', 'c2', 'c3', 'c4', 'r3']) && substr($val['orderStatus'], 0, 1) !== 'e') {
                    $getData['dashBoardPrice']['settlePrice'] += $currentSettlePrice;
                }
                if (in_array($val['orderStatus'], ['o1'])) {
                    $getData['dashBoardPrice']['dueSettlePrice'] += $currentSettlePrice;
                }
                if (in_array($val['orderStatus'], ['c1', 'c2', 'c3', 'c4'])) {
                    // $gVal['handleSno'] 값으로 orderHandle에 기록된 취소 예치금 마일리지 값 가져와서 계산
                    $aHandleInfo = $this->getOrderHandleInfo($val['handleSno']);
                    $getData['dashBoardPrice']['cancelPrice'] += (($val['goodsPrice'] + $val['optionPrice'] + $val['optionTextPrice']) * $val['goodsCnt']) + $val['addGoodsPrice'] - ($val['goodsDcPrice'] + $val['totalMemberDcPrice'] + $val['totalMemberOverlapDcPrice'] + $val['totalCouponGoodsDcPrice'] + $aHandleInfo['refundUseDeposit'] + $aHandleInfo['refundUseMileage'] + $val['totalDivisionCouponOrderDcPrice'] + $val['enuri']);

                    // 마이앱 사용에 따른 분기 처리
                    if ($this->useMyapp) {
                        $getData['dashBoardPrice']['cancelPrice'] += $val['myappDcPrice'];
                    }

                }
                if (in_array($val['orderStatus'], ['r3'])) {
                    $getData['dashBoardPrice']['refundPrice'] += ($val['refundPrice']+$val['refundDeliveryInsuranceFee']);
                }
                if (in_array($val['orderStatus'], ['r1', 'r2'])) {
                    $getData['dashBoardPrice']['dueRefundPrice'] += $currentSettlePrice;
                }

                $val['brandNm'] = $brandData[$val['brandCd']];

                // 상품명에 html 태그 제거
                $val['goodsNm'] = StringUtils::xssClean(html_entity_decode($val['goodsNm']));
                $disableInputTrackNumber = false;
                $hideDeliveryCompanySelectBox = false;
                $val['changeStatusDisabled'] = '';
                if ($getData['orderChannelFl'] == 'naverpay') {
                    $naverPay = new NaverPay();
                    $checkoutData = $val['checkoutData'];
                    $deliveryMethod = $naverPay->getDeliveryMethod($checkoutData);
                    $val['apiDeliveryDataText'] = $deliveryMethod;
                    //체크아웃 배송데이터에 상품번호 / 택배사 코드 /송장번호가 있으면
                    if (in_array($val['orderStatus'], ['s1', 'd1', 'd2']) || $checkoutData['deliveryData']['TrackingNumber']) {
                        $hideDeliveryCompanySelectBox = true;
                        if(!$val['deliveryMethodFl'] || $val['deliveryMethodFl'] === 'delivery'){
                            $disableInputTrackNumber = false;
                        }
                        else{
                            $disableInputTrackNumber = true;
                        }

                        $deliveryCompany = $delivery->getDeliveryCompany($checkoutData['deliveryData']['DeliveryCompany'], true, 'naverpay')[0]['companyName'];
                        if($val['deliveryMethodFl']){
                            $val['apiDeliveryDataText'] = $delivery->deliveryMethodList['name'][$val['deliveryMethodFl']];
                        }
                        else {
                            $val['apiDeliveryDataText'] = $naverPay->getDeliveryMethod($checkoutData);
                        }

                        $val['apiDeliveryDataText'] .= '<br>' . $deliveryCompany;
                        $val['invoiceNo'] = $checkoutData['deliveryData']['TrackingNumber'];
                    }

                    //상품별 할인액(즉시 할인+상품 할인 쿠폰+복수 구매 할인)
                    $val['naverpay']['ProductDiscountAmount'] = $checkoutData['orderGoodsData']['ProductDiscountAmount'];
                    $val['naverpay']['ProductProductDiscountAmount'] = $checkoutData['orderGoodsData']['ProductProductDiscountAmount']; //쿠폰금액
                    $val['naverpay']['ProductImmediateDiscountAmount'] = $checkoutData['orderGoodsData']['ProductImmediateDiscountAmount']; //즉시할인
                    $val['naverpay']['SellerBurdenDiscountAmount'] = $checkoutData['orderGoodsData']['SellerBurdenDiscountAmount']; //판매자부담할인
                    $val['naverpay']['FirstSellerBurdenDiscountAmount'] = $checkoutData['orderGoodsData']['FirstSellerBurdenDiscountAmount']; //최초 판매자부담할인

                    $val['naverpay']['DeliveryDiscountAmount'] = $checkoutData['orderGoodsData']['DeliveryDiscountAmount'];    //배송비할인금액
                    $val['naverpay']['TotalProductAmount'] = $checkoutData['orderGoodsData']['TotalProductAmount'];  //상품주문금액(할인전금액)
                    $val['naverpay']['TotalPaymentAmount'] = $checkoutData['orderGoodsData']['TotalPaymentAmount'];  //총 결제 금액(할인 적용 후 금액)
                    $val['naverpay']['SectionDeliveryFee'] = $checkoutData['orderGoodsData']['SectionDeliveryFee'];  //지역별 추가 배송비
                    $val['naverpay']['DeliveryFeeAmount'] = $checkoutData['orderGoodsData']['DeliveryFeeAmount'];  //배송비
                    $totalProductDiscountAmount += $val['naverpay']['ProductDiscountAmount'];
                    $totalProductImmediateDiscountAmount += $val['naverpay']['ProductImmediateDiscountAmount'];
                    $totalProductProductDiscountAmount += $val['naverpay']['ProductProductDiscountAmount'];
                    $totalSellerBurdenDiscountAmount += $val['naverpay']['SellerBurdenDiscountAmount'];
                    $totalFirstSellerBurdenDiscountAmount += $val['naverpay']['FirstSellerBurdenDiscountAmount'];


                    $totalAddGoodsPrice += $val['addGoodsPrice'];
                    $totalTotalProductAmount += $val['naverpay']['TotalProductAmount'];
                    $totalTotalPaymentAmount += $val['naverpay']['TotalPaymentAmount'];
                    $totalDeliveryDiscountAmount += $val['naverpay']['DeliveryDiscountAmount'];
                    if ($val['goodsDeliveryCollectFl'] == 'pre') {
                        $_naverpayDelivery[$checkoutData['orderGoodsData']['PackageNumber']]['fee'] = $val['naverpay']['DeliveryFeeAmount'];
                        $_naverpayDelivery[$checkoutData['orderGoodsData']['PackageNumber']]['section'] = $val['naverpay']['SectionDeliveryFee'];
                    }

                    if ($naverpayStatusText = $naverPay->getStatusText($checkoutData,$val['orderStatus'])) {
                        $naverImg = sprintf("<img src='%s' > ", \UserFilePath::adminSkin('gd_share', 'img', 'channel_icon', 'naverpay.gif')->www());
                        if($val['naverpayStatus']['code'] == 'RejectReturn'){
                            $val['orderStatusStr'] .= '<div class="js-btn-naverpay-status-detail" data-sno="'.$val['sno'].'" data-info="'.$val['naverpayStatus']['text'].'">(' . $naverImg . $naverpayStatusText . ')</div>';
                        }
                        else if($val['naverpayStatus']['code'] == 'WithholdReturn'){ //반품보류

                            $val['orderStatusStr'] .= '<div class="js-btn-naverpay-status-detail" data-sno="'.$val['sno'].'" data-info="'.$val['naverpayStatus']['text'].'">(' . $naverImg . $naverpayStatusText . ')</div>';
                        }
                        else if($val['naverpayStatus']['code'] == 'WithholdExchange'){ //교환보류
                            $val['orderStatusStr'] .= '<div class="js-btn-naverpay-status-detail" data-sno="'.$val['sno'].'" data-info="'.$val['naverpayStatus']['text'].'">(' . $naverImg . $naverpayStatusText . ')</div>';
                        }
                        else if($val['naverpayStatus']['code'] == 'ReDeliveryExchange'){ //교환재배송
                            $val['orderStatusStr'] .= '<div class="js-btn-naverpay-status-detail" data-sno="'.$val['sno'].'" data-info="'.$val['naverpayStatus']['text'].'">(' . $naverImg . $naverpayStatusText . ')</div>';
                        }
                        else if($val['naverpayStatus']['code'] == 'RejectExchange'){ //교환거부
                            $val['orderStatusStr'] .= '<div class="js-btn-naverpay-status-detail" data-sno="'.$val['sno'].'" data-info="'.$val['naverpayStatus']['text'].'">(' . $naverImg . $naverpayStatusText . ')</div>';
                        }
                        else {
                            $val['orderStatusStr'] .= '<br>(' . $naverImg . $naverpayStatusText . ')';
                        }
                    }

                    //주문데이터 상태기준
                    if (in_array($getData['orderStatus'], ['o1', 'd1', 'd2', 's1'])) {
                        $val['changeStatusDisabled'] = 'disabled';
                    }
                }

                $val['disableInputTrackNumber'] = $disableInputTrackNumber ? 'y' : 'n';
                $val['hideDeliveryCompanySelectBox'] = $hideDeliveryCompanySelectBox ? 'y' : 'n';
                if ($orderBasic['userHandleFl'] == 'y' && (!Manager::isProvider() && $orderBasic['userHandleAdmFl'] == 'y') || (Manager::isProvider() && $orderBasic['userHandleScmFl'] == 'y')) {
                    $val['userHandleInfo'] = $this->getUserHandleInfo($orderNo, $val['sno']);
                }

                if($useMultiShippingKey === true){
                    // 복수배송지 사용시 상품배열을 [orderInfoSno][orderDeliverySno][goods]의 3차 배열로 재구성
                    $getData['goods'][$val['orderInfoSno']][$val['orderDeliverySno']][] = $val;
                }
                else {
                    // 상품배열을 [SCM][Delivery][goods]의 3차 배열로 재구성
                    $getData['goods'][$scmNo][$val['deliverySno']][] = $val;
                }
                $infoSno = $val['orderInfoSno'] > 0 ? $val['orderInfoSno'] : $getData['infoSno'];
                if ($val['goodsType'] == 'goods' && ($val['deliveryMethodFl'] == 'visit' || empty($val['visitAddress']) === false) && in_array($val['sno'], $getData['checkoutData']['goodsSno']) === true) {
                    $getData['visitDelivery'][$infoSno][$val['sno']] = $val['deliverySno'];
                    $getData['visitAddressInfo'][$infoSno][$val['sno']] = $val['visitAddress'];
                    $getData['deliveryMethodFl'][$infoSno][$val['sno']] = $val['deliveryMethodFl'];
                }
                // 테이블 UI 표현을 위한 변수
                $addGoodsCnt = $val['addGoodsCnt'];
                //복수배송지 사용시 키 교체
                if($useMultiShippingKey === true){
                    $getData['cnt']['multiShipping'][$val['orderInfoSno']] += 1 + $addGoodsCnt;
                }
                else {
                    $getData['cnt']['scm'][$scmNo] += 1 + $addGoodsCnt;
                }
                $deliveryUniqueKey = $val['deliverySno'] . '-' . $val['orderDeliverySno'];
                $getData['cnt']['delivery'][$deliveryUniqueKey] += 1 + $addGoodsCnt;
//                $getData['cnt']['delivery'][$val['deliverySno']] += 1 + $addGoodsCnt;
                $getData['cnt']['goods']['all'] += 1 + $addGoodsCnt;
                $getData['cnt']['goods']['goods'] += 1;
                $getData['cnt']['goods']['addGoods'] += $addGoodsCnt;
            }
        }
        $getData['naverpay']['GeneralPaymentAmount'] = $checkoutData['orderData']['GeneralPaymentAmount'];  //네이버페이 실결제금액
        $getData['naverpay']['OrderDiscountAmount'] = $checkoutData['orderData']['OrderDiscountAmount'];  //네이버페이 주문할인
        $getData['naverpay']['NaverMileagePaymentAmount'] = $checkoutData['orderData']['NaverMileagePaymentAmount'];  //네이버페이 포인트
        $getData['naverpay']['ChargeAmountPaymentAmount'] = $checkoutData['orderData']['ChargeAmountPaymentAmount'];  //충전금-네이버페이에서 결제를 통한 충전
        $getData['naverpay']['totalAddGoodsPrice'] = $totalAddGoodsPrice;  //추가상품금액
        $getData['naverpay']['totalProductDiscountAmount'] = $totalProductDiscountAmount;
        $getData['naverpay']['totalProductImmediateDiscountAmount'] = $totalProductImmediateDiscountAmount;
        $getData['naverpay']['totalProductProductDiscountAmount'] = $totalProductProductDiscountAmount;
        $getData['naverpay']['totalSellerBurdenDiscountAmount'] = $totalSellerBurdenDiscountAmount;
        $getData['naverpay']['totalFirstSellerBurdenDiscountAmount'] = $totalFirstSellerBurdenDiscountAmount;

        $getData['naverpay']['totalDeliveryDiscountAmount'] = $totalDeliveryDiscountAmount;
        $getData['naverpay']['totalTotalProductAmount'] = $totalTotalProductAmount;
        $getData['naverpay']['totalTotalPaymentAmount'] = $totalTotalPaymentAmount;
        foreach ($_naverpayDelivery as $key => $val) {
            $totalSectionDeliveryFee += $val['section'];
            $totalDeliveryFeeAmount += $val['fee'];
        }
        $getData['naverpay']['totalSectionDeliveryFee'] = $totalSectionDeliveryFee;
        $getData['naverpay']['totalDeliveryFeeAmount'] = $totalDeliveryFeeAmount;

        $getData['naverpay']['totalDiscount'] = $totalProductDiscountAmount + $totalDeliveryDiscountAmount + $getData['naverpay']['NaverMileagePaymentAmount'] + $getData['naverpay']['ChargeAmountPaymentAmount'];
        $getData['naverpay']['priceInfo'] = sprintf(__('총 상품주문금액(%s)  + 배송비(%s) + 추가 지역별배송비(%s) - 네이버페이 할인금액(%s)'), gd_currency_display($totalTotalProductAmount), gd_currency_display($totalDeliveryFeeAmount), gd_currency_display($totalSectionDeliveryFee), gd_currency_display($getData['naverpay']['totalDiscount']));

        $getData['naverpay']['discountInfo'] = sprintf(__('쿠폰할인금액(%s)(판매자부담할인금액(%s))  + 배송비할인금액(%s) + 네이버 포인트(%s) + 네이버 충전금(%s) + 즉시할인금액(%s)'), gd_currency_display($totalProductProductDiscountAmount), gd_currency_display($totalSellerBurdenDiscountAmount), gd_currency_display($totalDeliveryDiscountAmount), gd_currency_display($getData['naverpay']['NaverMileagePaymentAmount']), gd_currency_display($getData['naverpay']['ChargeAmountPaymentAmount']), gd_currency_display($totalProductImmediateDiscountAmount));

        $deliveryUniqueKey = '';
        $aCancelDeliveryKey = array(); // 품목 하나가 부분으로 취소되서 품목수가 나뉘어진경우 취소배송비 중복계산이 안되도록 체크하기위해서 orderDeliverySno로 키값 저장&체크 todo 취소부분 환불과 맞추면 다 삭제될 내용
        // 전체 정책별 배송비와 지역별 배송비의 총합 구하기
        foreach ($getData['goods'] as $sKey => $sVal) {
            foreach ($sVal as $dKey => $dVal) {
//                $dVal = array_shift($dVal);
                foreach ($dVal as $gKey => $gVal) {
                    $realDeliveryTaxSum = array_sum([
                        $gVal['realTaxSupplyDeliveryCharge'],
                        $gVal['realTaxVatDeliveryCharge'],
                        $gVal['realTaxFreeDeliveryCharge'],
                    ]);
                    // 전체 배송비 계산 (주문상품추가/주문타상품교환으로 같은 배송비 조건(deliverySno)이더라도 주문배송비(orderDeliverySno)가 추가된다
                    $deliveryKeyCheck = $gVal['deliverySno'] . '-' . $gVal['orderDeliverySno'];
                    if ($deliveryKeyCheck == $deliveryUniqueKey) {
                        if (in_array($gVal['orderStatus'], ['c1', 'c2', 'c3', 'c4'])) {
                            $tempCancelDeliveryKey = substr($gVal['orderStatus'], 0, 1) . '-' . $gVal['orderDeliverySno'];
                            if (!in_array($tempCancelDeliveryKey, $aCancelDeliveryKey)) {
                                // $gVal['handleSno'] 값으로 orderHandle에 기록된 취소 배송비 값 가져와서 계산
                                $aHandleInfo = $this->getOrderHandleInfo($gVal['handleSno']);
                                $aDeliveryOriginalInfo = $this->getOrderDeliveryOriginal($gVal['orderDeliverySno']);
                                $getData['dashBoardPrice']['cancelPrice'] += $aHandleInfo['refundDeliveryCharge'] - ($aDeliveryOriginalInfo['divisionMemberDeliveryDcPrice'] - $gVal['divisionMemberDeliveryDcPrice']);
                                $aCancelDeliveryKey[] = $tempCancelDeliveryKey;
                            }
                        }
                    } else {
                        // 주문상세내 배송비 금액 합산
                        if (!in_array($gVal['orderStatus'], ['o1', 'c1', 'c2', 'c3', 'c4'])) {
                            $getData['dashBoardPrice']['settlePrice'] += $realDeliveryTaxSum;
                        }
                        if (in_array($gVal['orderStatus'], ['o1'])) {
                            $getData['dashBoardPrice']['dueSettlePrice'] += $realDeliveryTaxSum;
                        }
                        if (in_array($gVal['orderStatus'], ['c1', 'c2', 'c3', 'c4'])) {
                            $tempCancelDeliveryKey = substr($gVal['orderStatus'], 0, 1) . '-' . $gVal['orderDeliverySno'];
                            if (!in_array($tempCancelDeliveryKey, $aCancelDeliveryKey)) {
                                // $gVal['handleSno'] 값으로 orderHandle에 기록된 취소 배송비 값 가져와서 계산
                                $aHandleInfo = $this->getOrderHandleInfo($gVal['handleSno']);
                                $aDeliveryOriginalInfo = $this->getOrderDeliveryOriginal($gVal['orderDeliverySno']);
                                $getData['dashBoardPrice']['cancelPrice'] += $aHandleInfo['refundDeliveryCharge'] - ($aDeliveryOriginalInfo['divisionMemberDeliveryDcPrice'] - $gVal['divisionMemberDeliveryDcPrice']);
                                $aCancelDeliveryKey[] = $tempCancelDeliveryKey;
                            }
                        }
                        if (in_array($gVal['orderStatus'], ['r1', 'r2'])) {
                            $getData['dashBoardPrice']['dueRefundPrice'] += $realDeliveryTaxSum;
                        }
                        $getData['totalDeliveryPolicyCharge'] += $gVal['deliveryPolicyCharge'];
                        $getData['totalDeliveryAreaCharge'] += $gVal['deliveryAreaCharge'];
                        $getData['totalTaxSupplyDeliveryCharge'] += $gVal['taxSupplyDeliveryCharge'];
                        $getData['totalTaxVatDeliveryCharge'] += $gVal['taxVatDeliveryCharge'];
                        $getData['totalTaxFreeDeliveryCharge'] += $gVal['taxFreeDeliveryCharge'];

                        // 배송무게 계산
                        $getData['totalDeliveryWeights'] = json_decode($gVal['deliveryWeightInfo'], true);
                    }
                    $deliveryUniqueKey = $deliveryKeyCheck;

                    // 해당 주문의 배송비es_orderDelivery.sno에 대한 배송비 세율정보
                    $getData['deliveryTaxInfo'][$gVal['orderDeliverySno']] = explode(STR_DIVISION, $gVal['deliveryTaxInfo']);
                    $getData['deliveryPriceInfo'][$gVal['orderDeliverySno']]['iPrice'] = $gVal['realTaxSupplyDeliveryCharge'] + $gVal['realTaxVatDeliveryCharge'] + $gVal['realTaxFreeDeliveryCharge'] + $gVal['divisionDeliveryUseDeposit'] + $gVal['divisionDeliveryUseMileage'];
                    $getData['deliveryPriceInfo'][$gVal['orderDeliverySno']]['iCoupon'] = $gVal['divisionDeliveryCharge'];
                }
            }
        }

        if (Manager::isProvider()) {
            $getData = $this->getProviderTotalPrice($getData);
        }

        // 주문상담내역 추가
        $getData['consult'] = $this->getOrderConsult($orderNo);

        // 안심번호 기간만료시 상태값 변경하여 리턴
        if ($getData['receiverUseSafeNumberFl'] == 'y' && empty($getData['receiverSafeNumber']) == false && empty($getData['receiverSafeNumberDt']) == false && DateTimeUtils::intervalDay($getData['receiverSafeNumberDt'], date('Y-m-d H:i:s')) > 30) {
            $getData['receiverUseSafeNumberFl'] = 'e';
        }

        if ($getData['multiShippingFl'] == 'y' && count($getData['multiShippingList']) > 0) {
            foreach ($getData['multiShippingList'] as $mKey => $mVal) {
                if ($mVal['receiverUseSafeNumberFl'] == 'y' && empty($mVal['receiverSafeNumber']) == false && empty($getData['receiverSafeNumberDt']) == false && DateTimeUtils::intervalDay($mVal['receiverSafeNumberDt'], date('Y-m-d H:i:s')) > 30) {
                    $getData['multiShippingList'][$mKey]['receiverUseSafeNumberFl'] = 'e';
                }
            }
        }

        if ($getData['orderChannelFl'] == 'payco') {
            $paycoDataFiled = 'fintechData';
            if ($getData['orderMethod'] == 'CHECKOUT') {
                $paycoDataFiled = 'checkoutData';
            }
            $getData['paycoData'] = json_decode($getData[$paycoDataFiled], true);
        }

        return $getData;
    }

    /**
     * 환불처리 페이지 정보
     *
     * @param string $orderNo 주문 번호
     * @param null $orderGoodsNo
     * @param integer $handleSno 반품/교환/환불 테이블 번호
     *
     * @param string $statusMode
     * @param array $excludeStatus 제외할 주문상태 값
     * @param string $orderStatusMode 주문상세페이지 로드시 내역 종류
     * @param int $statusFl 환불처리 여부
     *
     * @return array|bool 주문 상세정보
     * @throws Exception
     */
    public function getRefundOrderView($orderNo, $orderGoodsNo = null, $handleSno = null, $statusMode = null, $excludeStatus = null, $orderStatusMode = null, $statusFl)
    {
        // 주문번호 체크
        if (Validator::required($orderNo, true) === false) {
            throw new Exception(__('주문번호은(는) 필수 항목 입니다.'));
        }

        if(trim($orderStatusMode) !== ''){
            //주문리스트 그리드 설정
            $orderAdminGrid = \App::load('\\Component\\Order\\OrderAdminGrid');
            $orderAdminGridMode = $orderAdminGrid->getOrderAdminGridMode($orderStatusMode);
            $this->orderGridConfigList = $orderAdminGrid->getSelectOrderGridConfigList($orderAdminGridMode);
        }

        // 주문 기본 정보
        $getData = $this->getOrderData($orderNo);

        // 주문 데이타 체크
        if (empty($getData) === true) {
            throw new Exception(sprintf(__('[%s] 주문 정보가 존재하지 않습니다.'), $orderNo));
        }

        // 멀티상점 데이터 병합 (멀티상점의 regDt가 병합되 잘못된 데이터가 추가되어 unset 처리함)
        $mallData = $this->getMultiShopName($getData['mallSno']);
        unset($mallData['regDt'], $mallData['modDt']);
        if (empty($mallData) === false) {
            $getData = array_merge($getData, $mallData);
        }

        // 주문 기본 정보
        $orderBasic = gd_policy('order.basic');
        if (($orderBasic['userHandleAdmFl'] == 'y' && $orderBasic['userHandleScmFl'] == 'y') === false) {
            unset($orderBasic['userHandleScmFl']);
        }

        // 리스트 그리드 항목 설정
        if (empty($this->orderGridConfigList) === false) {
            $getData['orderGridConfigList'] = $this->orderGridConfigList;
        }

        // 환불완료인경우 orderHandle정보 가져온다
        if ($statusFl == 0) {
            $tempOrderHandle = $this->getOrderHandle($orderNo);
            $aOrderHandle = null;
            if (!empty($tempOrderHandle)) {
                foreach ($tempOrderHandle as $k => $v) {
                    $aOrderHandle[$v['sno']] = $v;
                }
            }
        }

        // 멀티상점 기준몰 여부
        $getData['isDefaultMall'] = $this->isDefaultMall($getData['mallSno']);

        // 멀티상점 환율 기본 정보
        $getData['currencyPolicy'] = json_decode($getData['currencyPolicy'], true);
        $getData['exchangeRatePolicy'] = json_decode($getData['exchangeRatePolicy'], true);
        $getData['currencyIsoCode'] = $getData['currencyPolicy']['isoCode'];
        $getData['exchangeRate'] = $getData['exchangeRatePolicy']['exchangeRate' . $getData['currencyPolicy']['isoCode']];

        //총 할인금액
        $totalDcPriceArray = [
            $getData['totalGoodsDcPrice'],
            $getData['totalMemberDcPrice'],
            $getData['totalMemberOverlapDcPrice'],
            $getData['totalCouponGoodsDcPrice'],
            $getData['totalCouponOrderDcPrice'],
            $getData['totalMemberDeliveryDcPrice'],
            $getData['totalCouponDeliveryDcPrice'],
            $getData['totalEnuriDcPrice'],
        ];

        // 마이앱 사용에 따른 분기 처리
        if ($this->useMyapp) {
            array_push($totalDcPriceArray, $getData['totalMyappDcPrice']);
        }

        $getData['totalDcPrice'] = array_sum($totalDcPriceArray);

        //총 부가결제 금액
        $getData['totalUseAddedPrice'] = $getData['useMileage']+$getData['useDeposit'];

        //총 주문 금액 : 총 상품금액 + 총 배송비 - 총 할인금액
        $getData['totalOrderPrice'] = $getData['totalGoodsPrice'] + $getData['totalDeliveryCharge'] - $getData['totalDcPrice'];

        //총 실 결제금액
        $getData['totalRealSettlePrice'] = $getData['realTaxSupplyPrice'] + $getData['realTaxVatPrice'] + $getData['realTaxFreePrice'];

        // 주문 정보(주문자/수취인) 처리
        $getData['receiverPhone'] = explode('-', $getData['receiverPhone']);
        $getData['receiverCellPhone'] = explode('-', $getData['receiverCellPhone']);

        // PG 결과 처리
        $getData['pgSettleNm'] = explode(STR_DIVISION, $getData['pgSettleNm']);
        $getData['pgSettleCd'] = explode(STR_DIVISION, $getData['pgSettleCd']);

        // 전체 주문 상태
        $getData['orderStatusStr'] = $this->getOrderStatusAdmin($getData['orderStatus']);

        // 주문 채널 정보
        $getData['orderChannelFl'] = $getData['orderChannelFl'];
        $getData['orderChannelFlStr'] = $this->getOrderChannel($getData['orderChannelFl']);

        $getData['batchInvoiceInputDisabled'] = '';

        if ($getData['receiptFl'] == 'r') {
            // 주문 현금영수증 정보
            $cashReceipt = \App::load('\\Component\\Payment\\CashReceipt');
            $getData['cash'] = $cashReceipt->getOrderCashReceipt($orderNo);
        }

        if ($getData['receiptFl'] == 't') {
            // 주문 세금계산서 정보
            $tax = \App::load('\\Component\\Order\\Tax');
            $getData['tax'] = $tax->getOrderTaxInvoice($orderNo);
        }

        //복수배송지 사용 여부에 따라 페이지 노출시 scmNo 의 키를 order info sno 로 교체한다.
        $orderMultiShipping = App::load('\\Component\\Order\\OrderMultiShipping');
        $useMultiShippingKey = $orderMultiShipping->checkChangeOrderListKey($getData['multiShippingFl']);

        // 주문 상품 정보
        $orderGoods = $this->getOrderGoodsData($orderNo, $orderGoodsNo, null, null, 'admin', true, true, null, $excludeStatus, false, $useMultiShippingKey);

        //복수배송지를 사용 중이며 리스트에서 노출시킬 목적으로만 사용중이면 주문데이터 배열의 scmNo 를 orderInfoSno로 대체한다. : true 일시
        $getData['useMultiShippingKey'] = $useMultiShippingKey;

        //복수배송지가 사용된 주문건 일시 주문데이터에 order info 데이터를 따로 저장해둔다.
        if($getData['multiShippingFl'] === 'y'){
            $orderInfoSnoArr = [];
            if(Manager::isProvider()){
                foreach ($orderGoods as $scmNo => $dataVal) {
                    foreach ($dataVal as $key => $val) {
                        if($val['scmNo'] != Session::get('manager.scmNo')){
                            continue;
                        }

                        $orderInfoSnoArr[] = $val['orderInfoSno'];
                    }
                }
            }
            $getData['multiShippingList'] = $this->getOrderInfo($orderNo, true, 'orderInfoCd asc', $orderInfoSnoArr);
            if(Manager::isProvider()) {
                $getData = $orderMultiShipping->changeReceiverData($getData, $getData['multiShippingList']);
            }
        }

        // 현재 주문 상태코드 (한자리)
        if (empty($getData['orderStatus']) === false) {
            $getData['statusMode'] = substr($getData['orderStatus'], 0, 1);
        } else {
            // 주문테이블에 상태가 없는 경우 마지막 상품의 주문상태에 따른다
            foreach ($orderGoods as $sVal) {
                foreach ($sVal as $gVal) {
                    if (!in_array($gVal['orderStatus'], $this->statusExcludeCd)) {
                        $getData['statusMode'] = substr($gVal['orderStatus'], 0, 1);
                        break;
                    }
                }
            }
        }

        // 주문 상품이 없는 경우 false 리턴
        if (!$orderGoods) {
            return false;
        }

        // 공급사로 로그인시 합계 금액 재계산을 위해 초기화 처리
        if (Manager::isProvider()) {
            $getData['totalGoodsPrice'] = 0;
        }

        // 해외배송 보험료 한번만 처리
        $onlyOverseasInsurance = false;

        // 총 실 결제금액
        $totalRealPayedPrice = $getData['realTaxSupplyPrice'] + $getData['realTaxVatPrice'] + $getData['realTaxFreePrice'];

        // 환불항목 개별값 총값 맥스값 처리
        $totalAliveGoodsCount = 0;    // 환불전, 환불완료가 아닌 상품 카운트
        $totalAliveGoodsPrice = 0;    // 환불전 모든 상품 금액
        $totalGoodsPrice = 0;    // 상품 금액(할인전금액)

        // 쿠폰 & 회원등급 적립금 환불 금액
        $totalMileagePrice = 0; // 총 취소 마일리지 금액
        $iGoodsCouponMileagePrice = 0; // 취소 상품쿠폰 마일리지 금액
        $iOrderCouponMileagePrice = 0; // 취소 주문쿠폰 마일리지 금액
        $iGroupMileagePrice = 0; // 취소 등급 마일리지 금액

        // 상품 할인혜택 금액 항목
        $totalGoodsDcPrice = 0; // 총 상품 할인혜택 금액
        $iGoodsDcPrice = 0; // 상품할인 금액
        $iMemberAddDcPrice = 0; // 회원 추가할인 금액
        $iMemberOverlapDcPrice = 0; // 회원 중복할인 금액
        $iEnuriDcPrice = 0; // 운영자 할인 금액
        $iMyappDcPrice = 0; // 마이앱 할인 금액
        $iGoodsCouponDcPrice = 0; // 상품쿠폰 할인 금액
        $iOrderCouponDcPrice = 0; // 주문쿠폰 할인 금액

        // 배송비 환불금액
        $totalAliveDeliveryPrice = 0;    // 환불전 모든 배송비 환불금액
        $aAliveDeliverySno = array();    // 환불전, 환불완료가 아닌 상품의 배송비 sno정보
        $totalDeliveryPrice = 0;    // 배송비 환불금액
        $aDeliveryPrice = array();    // 배송비 환불금액
        $iDeliveryCouponDcPrice = 0; // 배송비쿠폰 할인 금액
        $iOverseasDeliveryInsuranceFee = 0; // 해외배송 보험료 금액

        // 부가결제 환불 금액
        $iDepositPrice = 0; // 예치금 환불 금액
        $iMileagePrice = 0; // 마일리지 환불 금액
        $iDepositPriceTotal = 0; // 예치금 환불 금액(총 환불대상 버려진배송비에 포함된내용까지)
        $iMileagePriceTotal = 0; // 마일리지 환불 금액(총 환불대상 버려진배송비에 포함된내용까지)

        // 현재 환불대상 안분할당 된 할인/부가결제 항목
        $iGoodsCouponMileagePriceNow = 0; // 취소 상품쿠폰 마일리지 금액
        $iOrderCouponMileagePriceNow = 0; // 취소 주문쿠폰 마일리지 금액
        $iGroupMileagePriceNow = 0; // 취소 등급 마일리지 금액
        $totalGoodsDcPriceNow = 0; // 총 상품 할인혜택 금액
        $iGoodsDcPriceNow = 0; // 상품할인 금액
        $iMemberAddDcPriceNow = 0; // 회원 추가할인 금액
        $iMemberOverlapDcPriceNow = 0; // 회원 중복할인 금액
        $iEnuriDcPriceNow = 0; // 운영자 할인 금액
        $iMyappDcPriceNow = 0; // 마이앱 할인 금액
        $iGoodsCouponDcPriceNow = 0; // 상품쿠폰 할인 금액
        $iOrderCouponDcPriceNow = 0; // 주문쿠폰 할인 금액
        $iDepositPriceNow = 0; // 예치금 환불 금액
        $iMileagePriceNow = 0; // 마일리지 환불 금액

        // 환불 수수료(환불완료일때만 사용)
        $iRefundCharge = 0; // 환불 수수료 누적금액

        // 계산된 금액
        $totalGoodsRefundPrice = 0;    // 상품 환불금액(상품금액 - 상품 할인혜택 금액)
        $totalDeliveryRefundPrice = 0;    // 배송비 환불금액(배송비[+지역별추가배송비] - 배송비 할인금액)
        $totalRefundPrice = 0;    // 총 환불금액 (상품 + 배송비 - 상품 할인혜택 금액 - 배송비 할인 - 부가결제 환불)

        // 추가 마일리지의 안분대상이 있는지 여부
        $iOrderMileageAbleCount = 0;
        $iCouponMileageAbleCount = 0;
        $iMemberMileageAbleCount = 0;
        $totalMileagePriceForMileage = 0;
        $totalMileagePriceForCoupon = 0;

        foreach ($orderGoods as $scmNo => $dataVal) {
            // 공급사로 로그인한 경우 처리 해당 공급사 상품만 보여지도록 수정 처리
            if($useMultiShippingKey !== true){
                if (Manager::isProvider()) {
                    if ($scmNo != Session::get('manager.scmNo')) {
                        continue;
                    }
                }
            }

            foreach ($dataVal as $key => $val) {
                //$totalRealPayedPrice += $val['realTaxSupplyDeliveryCharge'] + $val['realTaxVatDeliveryCharge'] + $val['realTaxFreeDeliveryCharge'];
                // 환불요청 상태 아닌것들중 환불완료/교환완료/취소상태등을 제외하고 쿠폰&적립금&예치금등을 전부 누적처리
                if (($statusFl == 0 && in_array($val['orderStatus'], ['r3'])) || ($statusFl == 1 && (in_array(substr($val['orderStatus'], 0, 1), ['p', 'g', 'd', 's']) || in_array($val['orderStatus'], ['r1', 'r2'])))) {
                    // 환불전 모든 상품 금액 & 배송비 금액
                    $totalAliveGoodsPrice += (($val['goodsPrice'] + $val['optionPrice'] + $val['optionTextPrice']) * $val['goodsCnt']) + $val['addGoodsPrice'];
                    if (!isset($aDeliveryPrice[$val['orderDeliverySno']])) {
                        $totalAliveDeliveryPrice += $val['realTaxSupplyDeliveryCharge'] + $val['realTaxVatDeliveryCharge'] + $val['realTaxFreeDeliveryCharge'] + $val['divisionDeliveryUseDeposit'] + $val['divisionDeliveryUseMileage'] + $val['divisionDeliveryCharge'];
                        $aDeliveryPrice[$val['orderDeliverySno']] = '';
                    }

                    // 쿠폰 & 회원등급 적립금 환불 금액
                    if ($val['plusMileageFl'] == 'n' && $val['memberMileage'] > 0) {
                        $totalMileagePrice += $val['memberMileage'];
                        $totalMileagePriceForMileage += $val['memberMileage'];
                        $iGroupMileagePrice += $val['memberMileage']; // 취소 등급 마일리지 금액
                    }
                    if ($val['couponMileageFl'] == 'n' && ($val['couponGoodsMileage'] > 0 || $val['divisionCouponOrderMileage'] > 0)) {
                        $totalMileagePrice += ($val['couponGoodsMileage'] + $val['divisionCouponOrderMileage']);
                        $totalMileagePriceForCoupon += ($val['couponGoodsMileage'] + $val['divisionCouponOrderMileage']);
                        $iGoodsCouponMileagePrice += $val['couponGoodsMileage']; // 취소 상품쿠폰 마일리지 금액
                        $iOrderCouponMileagePrice += $val['divisionCouponOrderMileage']; // 취소 주문쿠폰 마일리지 금액
                    }

                    // 상품 할인혜택 금액 항목
                    $totalGoodsDcPrice += $val['goodsDcPrice'] + $val['memberDcPrice'] + $val['memberOverlapDcPrice'] + $val['enuri'] + $val['couponGoodsDcPrice'] + $val['divisionCouponOrderDcPrice']; // 총 상품 할인혜택 금액
                    $iGoodsDcPrice += $val['goodsDcPrice']; // 상품할인 금액
                    $iMemberAddDcPrice += $val['memberDcPrice']; // 회원 추가할인 금액
                    $iMemberOverlapDcPrice += $val['memberOverlapDcPrice']; // 회원 중복할인 금액
                    $iEnuriDcPrice += $val['enuri']; // 운영자 할인 금액

                    $iGoodsCouponDcPrice += $val['couponGoodsDcPrice']; // 상품쿠폰 할인 금액
                    $iOrderCouponDcPrice += $val['divisionCouponOrderDcPrice']; // 주문쿠폰 할인 금액
                    if ($this->useMyapp) {
                        $totalGoodsDcPrice += $val['myappDcPrice']; // 총 상품 할인혜택 금액
                        $iMyappDcPrice += $val['myappDcPrice']; // 마이앱 할인 금액
                    }

                    // 부가결제 환불 금액
                    $iDepositPrice += $val['divisionUseDeposit']; // 예치금 환불 금액
                    $iMileagePrice += $val['divisionUseMileage']; // 마일리지 환불 금액
                    $iDepositPriceTotal += $val['divisionUseDeposit'] + $val['divisionGoodsDeliveryUseDeposit']; // 예치금 환불 금액
                    $iMileagePriceTotal += $val['divisionUseMileage'] + $val['divisionGoodsDeliveryUseMileage']; // 마일리지 환불 금액

                    //if (!in_array($val['orderStatus'], ['r1', 'r2'])) { // 배송 금액들은 환불대상만 처리
                        if ($aAliveDeliverySno[$val['orderDeliverySno']] != $val['orderDeliverySno']) {
                            $aAliveDeliverySno[$val['orderDeliverySno']] = $val['orderDeliverySno'];
                        }
                    //}
                }

                if ($handleSno) {
                    if ($handleSno != $val['handleSno']) {
                        if (!in_array($val['orderStatus'], array('r3', 'e1', 'e2', 'e3', 'e4', 'e5', 'c1', 'c2', 'c3', 'c4'))) {
                            // 현재 환불대상이 아닌 상품에 항목별로 부가 마일리지가 0인 카운트
                            if ($val['couponMileageFl'] == 'n' && $val['couponGoodsMileage'] > 0) {
                                $iCouponMileageAbleCount += 1;
                            }
                            if ($val['couponMileageFl'] == 'n' && $val['divisionCouponOrderMileage'] > 0) {
                                $iOrderMileageAbleCount += 1;
                            }
                            if ($val['plusMileageFl'] == 'n' && $val['memberMileage'] > 0) {
                                $iMemberMileageAbleCount += 1;
                            }
                        }
                        $totalAliveGoodsCount++; // 환불전,환불완료가 아닌 상품의 카운트
                        continue;
                    }
                } else {
                    if (in_array('r3', $excludeStatus)) {
                        if ($val['orderStatus'] != 'r1' && $val['orderStatus'] != 'r2') {
                            // 현재 환불대상이 아닌 상품에 항목별로 부가 마일리지가 0인 카운트
                            if ($val['couponMileageFl'] == 'n' && $val['couponGoodsMileage'] > 0) {
                                $iCouponMileageAbleCount += 1;
                            }
                            if ($val['couponMileageFl'] == 'n' && $val['divisionCouponOrderMileage'] > 0) {
                                $iOrderMileageAbleCount += 1;
                            }
                            if ($val['plusMileageFl'] == 'n' && $val['memberMileage'] > 0) {
                                $iMemberMileageAbleCount += 1;
                            }
                            $totalAliveGoodsCount++; // 환불전,환불완료가 아닌 상품의 카운트
                            continue;
                        }
                    }
                    if (in_array('r1', $excludeStatus) && in_array('r2', $excludeStatus)) {
                        if ($val['orderStatus'] != 'r3') {
                            // 현재 환불대상이 아닌 상품에 항목별로 부가 마일리지가 0인 카운트
                            if ($val['couponMileageFl'] == 'n' && $val['couponGoodsMileage'] > 0) {
                                $iCouponMileageAbleCount += 1;
                            }
                            if ($val['couponMileageFl'] == 'n' && $val['divisionCouponOrderMileage'] > 0) {
                                $iOrderMileageAbleCount += 1;
                            }
                            if ($val['plusMileageFl'] == 'n' && $val['memberMileage'] > 0) {
                                $iMemberMileageAbleCount += 1;
                            }
                            $totalAliveGoodsCount++; // 환불전,환불완료가 아닌 상품의 카운트
                            continue;
                        }
                    }
                    //p g d s
                }

                // 환불 대상
                $totalGoodsPrice += (($val['goodsPrice'] + $val['optionPrice'] + $val['optionTextPrice']) * $val['goodsCnt']) + $val['addGoodsPrice'];    //상품 금액(할인전금액)

                // 배송비 환불금액
                if (!isset($aDeliveryPrice[$val['orderDeliverySno']]) || !is_array($aDeliveryPrice[$val['orderDeliverySno']])) {
                    if ($statusFl == 1) {
                        if ($val['realTaxSupplyDeliveryCharge'] + $val['realTaxVatDeliveryCharge'] + $val['realTaxFreeDeliveryCharge'] + $val['divisionDeliveryUseDeposit'] + $val['divisionDeliveryUseMileage'] + $val['divisionDeliveryCharge'] > 0) {
                            $aDeliveryPrice[$val['orderDeliverySno']] = array();
                            $totalDeliveryPrice += $val['realTaxSupplyDeliveryCharge'] + $val['realTaxVatDeliveryCharge'] + $val['realTaxFreeDeliveryCharge'] + $val['divisionDeliveryUseDeposit'] + $val['divisionDeliveryUseMileage'] + $val['divisionDeliveryCharge'];    // 배송비 환불금액
                            $aDeliveryPrice[$val['orderDeliverySno']]['iAmount'] += $val['realTaxSupplyDeliveryCharge'] + $val['realTaxVatDeliveryCharge'] + $val['realTaxFreeDeliveryCharge'] + $val['divisionDeliveryUseDeposit'] + $val['divisionDeliveryUseMileage'];    // 배송비 환불금액
                            $aDeliveryPrice[$val['orderDeliverySno']]['sName'] = $val['goodsNm'];
                            $aDeliveryPrice[$val['orderDeliverySno']]['iCoupon'] = $val['divisionDeliveryCharge'];
                            $aDeliveryPrice[$val['orderDeliverySno']]['iGroup'] = $val['divisionMemberDeliveryDcPrice']; // 배송비 회원등급 할인 금액
                            $iDeliveryCouponDcPrice += $val['divisionDeliveryCharge']; // 배송비쿠폰 할인 금액
                        }
                    }
                }
                if ($statusFl == 0) {
                    if ($aOrderHandle[$val['handleSno']]['refundDeliveryCharge'] + $aOrderHandle[$val['handleSno']]['refundDeliveryUseDeposit'] + $aOrderHandle[$val['handleSno']]['refundDeliveryUseMileage'] + $aOrderHandle[$val['handleSno']]['refundDeliveryInsuranceFee'] + $aOrderHandle[$val['handleSno']]['refundDeliveryCoupon'] > 0) {
                        $totalDeliveryPrice += $aOrderHandle[$val['handleSno']]['refundDeliveryCharge'] + $aOrderHandle[$val['handleSno']]['refundDeliveryUseDeposit'] + $aOrderHandle[$val['handleSno']]['refundDeliveryUseMileage'] + $aOrderHandle[$val['handleSno']]['refundDeliveryInsuranceFee'] + $aOrderHandle[$val['handleSno']]['refundDeliveryCoupon'];    // 배송비 환불금액
                        $aDeliveryPrice[$val['orderDeliverySno']]['iAmount'] += $aOrderHandle[$val['handleSno']]['refundDeliveryCharge'] + $aOrderHandle[$val['handleSno']]['refundDeliveryUseDeposit'] + $aOrderHandle[$val['handleSno']]['refundDeliveryUseMileage'] + $aOrderHandle[$val['handleSno']]['refundDeliveryInsuranceFee'];    // 배송비 환불금액
                        $aDeliveryPrice[$val['orderDeliverySno']]['sName'] = $val['goodsNm'];
                        $aDeliveryPrice[$val['orderDeliverySno']]['iCoupon'] += $aOrderHandle[$val['handleSno']]['refundDeliveryCoupon'];
                        $aDeliveryPrice[$val['orderDeliverySno']]['iGroup'] = $val['divisionMemberDeliveryDcPrice']; // 배송비 회원등급 할인 금액
                        $iDeliveryCouponDcPrice += $aOrderHandle[$val['handleSno']]['refundDeliveryCoupon']; // 배송비쿠폰 할인 금액
                    }

                    $iRefundDepositCommission = $aOrderHandle[$val['handleSno']]['refundUseDepositCommission'];
                    $iRefundMileageCommission = $aOrderHandle[$val['handleSno']]['refundUseMileageCommission'];
                    $iRefundCharge += $aOrderHandle[$val['handleSno']]['refundCharge']; // 환불 수수료
                }

                // 공급사로 로그인시 합계 금액 재계산
                if (Manager::isProvider()) {
                    //복수배송지를 사용하여 배열의 $scmNo 가 order info sno 로 대체된 경우 $val 로 공급사 체크
                    if($useMultiShippingKey === true){
                        if ($val['scmNo'] != Session::get('manager.scmNo')) {
                            continue;
                        }
                    }
                    if(substr($val['orderStatus'], 0, 1) !== 'e'){
                        $getData['totalGoodsPrice'] += (($val['goodsPrice'] + $val['optionPrice'] + $val['optionTextPrice']) * $val['goodsCnt']) + $val['addGoodsPrice'];
                    }
                }

                // 해외배송 보험료 (해당 루프에서 단 한번만 처리)
                if ($onlyOverseasInsurance === false && isset($val['deliveryInsuranceFee']) && $val['deliveryInsuranceFee'] > 0) {
                    $iOverseasDeliveryInsuranceFee += $val['deliveryInsuranceFee'];
                    $onlyOverseasInsurance = true;
                }

                // 상품명에 html 태그 제거
                $val['goodsNm'] = StringUtils::xssClean(html_entity_decode($val['goodsNm']));
                $disableInputTrackNumber = false;
                $hideDeliveryCompanySelectBox = false;
                $val['changeStatusDisabled'] = '';

                if ($orderBasic['userHandleFl'] == 'y' && (!Manager::isProvider() && $orderBasic['userHandleAdmFl'] == 'y') || (Manager::isProvider() && $orderBasic['userHandleScmFl'] == 'y')) {
                    $val['userHandleInfo'] = $this->getUserHandleInfo($orderNo, $val['sno']);
                }

                if($useMultiShippingKey === true){
                    // 복수배송지 사용시 상품배열을 [orderInfoSno][orderDeliverySno][goods]의 3차 배열로 재구성
                    $getData['goods'][$val['orderInfoSno']][$val['orderDeliverySno']][] = $val;
                }
                else {
                    // 상품배열을 [SCM][Delivery][goods]의 3차 배열로 재구성
                    $getData['goods'][$scmNo][$val['deliverySno']][] = $val;
                }

                // 테이블 UI 표현을 위한 변수
                $addGoodsCnt = $val['addGoodsCnt'];
                //복수배송지 사용시 키 교체
                if($useMultiShippingKey === true){
                    $getData['cnt']['multiShipping'][$val['orderInfoSno']] += 1 + $addGoodsCnt;
                }
                else {
                    $getData['cnt']['scm'][$scmNo] += 1 + $addGoodsCnt;
                }
                $deliveryUniqueKey = $val['deliverySno'] . '-' . $val['orderDeliverySno'];
                $getData['cnt']['delivery'][$deliveryUniqueKey] += 1 + $addGoodsCnt;
//                $getData['cnt']['delivery'][$val['deliverySno']] += 1 + $addGoodsCnt;
                $getData['cnt']['goods']['all'] += 1 + $addGoodsCnt;
                $getData['cnt']['goods']['goods'] += 1;
                $getData['cnt']['goods']['addGoods'] += $addGoodsCnt;

                // 현재창의 환불대상 안분금액 합계(임시)
                if ($handleSno) {
                    if ($handleSno == $val['handleSno']) {
                        if ($val['plusMileageFl'] == 'n' && $val['memberMileage'] > 0) {
                            $iGroupMileagePriceNow += $val['memberMileage']; // 취소 등급 마일리지 금액
                        }
                        if ($val['couponMileageFl'] == 'n' && ($val['couponGoodsMileage'] > 0 || $val['divisionCouponOrderMileage'] > 0)) {
                            $iGoodsCouponMileagePriceNow += $val['couponGoodsMileage']; // 취소 상품쿠폰 마일리지 금액
                            $iOrderCouponMileagePriceNow += $val['divisionCouponOrderMileage']; // 취소 주문쿠폰 마일리지 금액
                        }
                        // 상품 할인혜택 금액 항목
                        $totalGoodsDcPriceNow += $val['goodsDcPrice'] + $val['memberDcPrice'] + $val['memberOverlapDcPrice'] + $val['enuri'] + $val['couponGoodsDcPrice'] + $val['divisionCouponOrderDcPrice']; // 총 상품 할인혜택 금액
                        $iGoodsDcPriceNow += $val['goodsDcPrice']; // 상품할인 금액
                        $iMemberAddDcPriceNow += $val['memberDcPrice']; // 회원 추가할인 금액
                        $iMemberOverlapDcPriceNow += $val['memberOverlapDcPrice']; // 회원 중복할인 금액
                        $iEnuriDcPriceNow += $val['enuri']; // 운영자 할인 금액

                        $iGoodsCouponDcPriceNow += $val['couponGoodsDcPrice']; // 상품쿠폰 할인 금액
                        $iOrderCouponDcPriceNow += $val['divisionCouponOrderDcPrice']; // 주문쿠폰 할인 금액
                        if ($this->useMyapp) {
                            $totalGoodsDcPriceNow += $val['myappDcPrice']; // 총 상품 할인혜택 금액
                            $iMyappDcPriceNow += $val['myappDcPrice']; // 마이앱 할인 금액
                        }

                        // 부가결제 환불 금액
                        $iDepositPriceNow += $val['divisionUseDeposit']; // 예치금 환불 금액
                        $iMileagePriceNow += $val['divisionUseMileage']; // 마일리지 환불 금액
                    }
                } else {
                    if ((in_array('r3', $excludeStatus) && ($val['orderStatus'] == 'r1' || $val['orderStatus'] == 'r2') && $statusFl == 1) || ($val['orderStatus'] == 'r3' && $statusFl == 0)) {
                        if ($val['plusMileageFl'] == 'n' && $val['memberMileage'] > 0) {
                            $iGroupMileagePriceNow += $val['memberMileage']; // 취소 등급 마일리지 금액
                        }
                        if ($val['couponMileageFl'] == 'n' && ($val['couponGoodsMileage'] > 0 || $val['divisionCouponOrderMileage'] > 0)) {
                            $iGoodsCouponMileagePriceNow += $val['couponGoodsMileage']; // 취소 상품쿠폰 마일리지 금액
                            $iOrderCouponMileagePriceNow += $val['divisionCouponOrderMileage']; // 취소 주문쿠폰 마일리지 금액
                        }
                        // 상품 할인혜택 금액 항목
                        $totalGoodsDcPriceNow += $val['goodsDcPrice'] + $val['memberDcPrice'] + $val['memberOverlapDcPrice'] + $val['enuri'] + $val['couponGoodsDcPrice'] + $val['divisionCouponOrderDcPrice']; // 총 상품 할인혜택 금액
                        $iGoodsDcPriceNow += $val['goodsDcPrice']; // 상품할인 금액
                        $iMemberAddDcPriceNow += $val['memberDcPrice']; // 회원 추가할인 금액
                        $iMemberOverlapDcPriceNow += $val['memberOverlapDcPrice']; // 회원 중복할인 금액
                        $iEnuriDcPriceNow += $val['enuri']; // 운영자 할인 금액

                        $iGoodsCouponDcPriceNow += $val['couponGoodsDcPrice']; // 상품쿠폰 할인 금액
                        $iOrderCouponDcPriceNow += $val['divisionCouponOrderDcPrice']; // 주문쿠폰 할인 금액
                        if ($this->useMyapp) {
                            $totalGoodsDcPriceNow += $val['myappDcPrice']; // 총 상품 할인혜택 금액
                            $iMyappDcPriceNow += $val['myappDcPrice']; // 마이앱 할인 금액
                        }

                        // 부가결제 환불 금액
                        $iDepositPriceNow += $val['divisionUseDeposit']; // 예치금 환불 금액
                        $iMileagePriceNow += $val['divisionUseMileage']; // 마일리지 환불 금액
                    }

                    // 환불내용보여주는 경우니 환불대상안분은 필요없겠?
                    if (in_array('r1', $excludeStatus) && in_array('r2', $excludeStatus)) {
                    }
                }
            }
        }

        // 현재 환불대상에 부가 마일리지 항목별로 0이면 전체 값을 초기화
        if ($iGoodsCouponMileagePriceNow == 0) {
            $totalMileagePrice -= $iGoodsCouponMileagePrice;
            $iGoodsCouponMileagePrice = 0;
        }
        if ($iOrderCouponMileagePriceNow == 0) {
            $totalMileagePrice -= $iOrderCouponMileagePrice;
            $iOrderCouponMileagePrice = 0;
        }
        if ($iGroupMileagePriceNow == 0) {
            $totalMileagePrice -= $iGroupMileagePrice;
            $iGroupMileagePrice = 0;
        }
        // 현재 환불대상에 부가 마일리지 항목별로 해당항목이 마지막인경우 해당항목 전체 취소하도록 변경
        if ($iCouponMileageAbleCount == 0 && $iGoodsCouponMileagePriceNow > 0) {
            $iMinGoodsCouponMileagePrice = $iGoodsCouponMileagePriceNow;
        } else {
            $iMinGoodsCouponMileagePrice = 0;
        }
        if ($iOrderMileageAbleCount == 0 && $iOrderCouponMileagePriceNow > 0) {
            $iMinOrderCouponMileagePrice = $iOrderCouponMileagePriceNow;
        } else {
            $iMinOrderCouponMileagePrice = 0;
        }
        if ($iMemberMileageAbleCount == 0 && $iGroupMileagePriceNow > 0) {
            $iMinGroupMileagePrice = $iGroupMileagePriceNow;
        } else {
            $iMinGroupMileagePrice = 0;
        }

        // 계산된 금액
        $totalGoodsRefundPrice += $totalGoodsPrice;    // 상품 환불금액(상품금액 - 상품 할인혜택 금액)
        $totalDeliveryRefundPrice += $totalDeliveryPrice;    // 배송비 환불금액(배송비[+지역별추가배송비] - 배송비 할인금액)
        $totalRefundPrice = $totalGoodsRefundPrice + $totalDeliveryRefundPrice;    // 총 환불금액 (상품 + 배송비 - 상품 할인혜택 금액 - 배송비 할인 - 부가결제 환불)

        // 실제 환불 대상 배송만 남도록 처리
        foreach ($aDeliveryPrice as $key => $val) {
            if ($val == '') {
                unset($aDeliveryPrice[$key]);
            }
        }

        // 배송비는 비환불대상과 환불대상이 공용으로 쓰고있을수있어서 배송비에대한 부가결제금액을 아래에서 더해준다
        foreach ($orderGoods as $scmNo => $dataVal) {
            foreach ($dataVal as $key => $val) {
                if (($aDeliveryPrice[$val['orderDeliverySno']] != '' && $statusFl == 1) || ((($handleSno && $handleSno == $val['handleSno']) || $val['orderStatus'] == 'r3') && $statusFl == 0)) {
                    $iDepositPrice += $val['divisionGoodsDeliveryUseDeposit'];
                    $iMileagePrice += $val['divisionGoodsDeliveryUseMileage'];
                    $iDepositPriceNow += $val['divisionGoodsDeliveryUseDeposit'];
                    $iMileagePriceNow += $val['divisionGoodsDeliveryUseMileage'];
                }
            }
        }

        $getData['refundData'] = array();
        $getData['refundData']['totalRealPayedPrice'] = $totalRealPayedPrice;
        $getData['refundData']['refundGoodsPriceSum'] = $totalGoodsPrice;
        $getData['refundData']['refundAliveGoodsPriceSum'] = $totalAliveGoodsPrice - $totalGoodsPrice;
        $getData['refundData']['refundAliveGoodsCount'] = $totalAliveGoodsCount;
        $getData['refundData']['refundMileageSum'] = $totalMileagePrice;
        $getData['refundData']['refundGoodsCouponMileage'] = $iGoodsCouponMileagePrice;
        $getData['refundData']['refundOrderCouponMileage'] = $iOrderCouponMileagePrice;
        $getData['refundData']['refundGroupMileage'] = $iGroupMileagePrice;
        if ($totalGoodsDcPrice > ($totalAliveGoodsPrice - $totalGoodsPrice)) {
            $tempMinPrice = $totalGoodsDcPrice - ($totalAliveGoodsPrice - $totalGoodsPrice);
            $getData['refundData']['refundGoodsDcPriceSumMin'] = $tempMinPrice;
        } else {
            $getData['refundData']['refundGoodsDcPriceSumMin'] = 0;
        }
        $getData['refundData']['refundGoodsDcPriceSum'] = $totalGoodsDcPrice;
        $getData['refundData']['refundGoodsDcPriceSumMax'] = ($totalGoodsDcPrice > $totalGoodsPrice) ? $totalGoodsPrice : $totalGoodsDcPrice;
        $getData['refundData']['refundGoodsDcPrice'] = $iGoodsDcPrice;
        $getData['refundData']['refundGoodsDcPriceMax'] = ($iGoodsDcPrice > $totalGoodsPrice) ? $totalGoodsPrice : $iGoodsDcPrice;
        $getData['refundData']['refundMemberAddDcPrice'] = $iMemberAddDcPrice;
        $getData['refundData']['refundMemberAddDcPriceMax'] = ($iMemberAddDcPrice > $totalGoodsPrice) ? $totalGoodsPrice : $iMemberAddDcPrice;
        $getData['refundData']['refundMemberOverlapDcPrice'] = $iMemberOverlapDcPrice;
        $getData['refundData']['refundMemberOverlapDcPriceMax'] = ($iMemberOverlapDcPrice > $totalGoodsPrice) ? $totalGoodsPrice : $iMemberOverlapDcPrice;
        $getData['refundData']['refundEnuriDcPrice'] = $iEnuriDcPrice;
        $getData['refundData']['refundEnuriDcPriceMax'] = ($iEnuriDcPrice > $totalGoodsPrice) ? $totalGoodsPrice : $iEnuriDcPrice;
        if ($this->useMyapp) {
            $getData['refundData']['refundMyappDcPrice'] = $iMyappDcPrice;
            $getData['refundData']['refundMyappDcPriceMax'] = ($iMyappDcPrice > $totalGoodsPrice) ? $totalGoodsPrice : $iMyappDcPrice;
        }
        $getData['refundData']['refundGoodsCouponDcPrice'] = $iGoodsCouponDcPrice;
        $getData['refundData']['refundGoodsCouponDcPriceMax'] = ($iGoodsCouponDcPrice > $totalGoodsPrice) ? $totalGoodsPrice : $iGoodsCouponDcPrice;
        $getData['refundData']['refundOrderCouponDcPrice'] = $iOrderCouponDcPrice;
        $getData['refundData']['refundOrderCouponDcPriceMax'] = ($iOrderCouponDcPrice > $totalGoodsPrice) ? $totalGoodsPrice : $iOrderCouponDcPrice;
        $getData['refundData']['refundAliveDeliveryPriceSum'] = $totalAliveDeliveryPrice;
        $getData['refundData']['aAliveDeliverySno'] = $aAliveDeliverySno;
        $getData['refundData']['aDeliveryAmount'] = $aDeliveryPrice;
        $getData['refundData']['refundDeliveryPriceSum'] = $totalDeliveryPrice;
        $getData['refundData']['refundDeliveryCouponDcPrice'] = $iDeliveryCouponDcPrice;
        $getData['refundData']['refundOverseasDeliveryInsuranceFee'] = $iOverseasDeliveryInsuranceFee;
        $getData['refundData']['refundDepositPrice'] = $iDepositPrice;
        $getData['refundData']['refundDepositPriceTotal'] = $iDepositPriceTotal;
        $getData['refundData']['refundDepositPriceMax'] = ($iDepositPrice > ($totalGoodsPrice + $totalDeliveryPrice)) ? ($totalGoodsPrice + $totalDeliveryPrice) : $iDepositPrice;
        $getData['refundData']['refundMileagePrice'] = $iMileagePrice;
        $getData['refundData']['refundMileagePriceTotal'] = $iMileagePriceTotal;
        $getData['refundData']['refundMileagePriceMax'] = ($iMileagePrice > ($totalGoodsPrice + $totalDeliveryPrice)) ? ($totalGoodsPrice + $totalDeliveryPrice) : $iMileagePrice;
        $getData['refundData']['totalRefundGoodsPrice'] = $totalGoodsRefundPrice;
        $getData['refundData']['totalDeliveryPrice'] = $totalDeliveryRefundPrice;
        $getData['refundData']['totalRefundPrice'] = $totalRefundPrice;
        if ($statusFl == 0) {
            $getData['refundData']['totalRefundPrice'] -= ($getData['refundData']['refundDeliveryCouponDcPrice'] + $getData['refundData']['refundDepositPrice'] + $getData['refundData']['refundMileagePrice'] + $getData['refundData']['refundGoodsDcPriceSum']);
            $getData['refundData']['totalRefundCharge'] = $iRefundCharge;
            $getData['refundData']['totalRefundDepositCommission'] = $iRefundDepositCommission;
            $getData['refundData']['totalRefundMileageCommission'] = $iRefundMileageCommission;
        } else {
            $getData['refundData']['totalRefundCharge'] = 0;
            $getData['refundData']['totalRefundDepositCommission'] = 0;
            $getData['refundData']['totalRefundMileageCommission'] = 0;
        }

        // 현재창의 환불대상 안분금액 합계(임시)
        $getData['refundData']['refundGoodsCouponMileageNow'] = $iGoodsCouponMileagePriceNow;
        $getData['refundData']['refundOrderCouponMileageNow'] = $iOrderCouponMileagePriceNow;
        $getData['refundData']['refundGroupMileageNow'] = $iGroupMileagePriceNow;
        $getData['refundData']['refundMinGoodsCouponMileage'] = $iMinGoodsCouponMileagePrice;
        $getData['refundData']['refundMinOrderCouponMileage'] = $iMinOrderCouponMileagePrice;
        $getData['refundData']['refundMinGroupMileage'] = $iMinGroupMileagePrice;
        $getData['refundData']['refundTotalGoodsDcPriceNow'] = $totalGoodsDcPriceNow;
        $getData['refundData']['refundGoodsDcPriceNow'] = $iGoodsDcPriceNow;
        $getData['refundData']['refundMemberAddDcPriceNow'] = $iMemberAddDcPriceNow;
        $getData['refundData']['refundMemberOverlapDcPriceNow'] = $iMemberOverlapDcPriceNow;
        $getData['refundData']['refundEnuriDcPriceNow'] = $iEnuriDcPriceNow;
        $getData['refundData']['refundGoodsCouponDcPriceNow'] = $iGoodsCouponDcPriceNow;
        $getData['refundData']['refundOrderCouponDcPriceNow'] = $iOrderCouponDcPriceNow;
        if ($this->useMyapp) {
            $getData['refundData']['refundMyappDcPriceNow'] = $iMyappDcPriceNow;
        }
        $getData['refundData']['refundDepositPriceNow'] = $iDepositPriceNow;
        $getData['refundData']['refundMileagePriceNow'] = $iMileagePriceNow;

        $deliveryUniqueKey = '';
        $aCancelDeliveryKey = array(); // 품목 하나가 부분으로 취소되서 품목수가 나뉘어진경우 취소배송비 중복계산이 안되도록 체크하기위해서 orderDeliverySno로 키값 저장&체크 todo 취소부분 환불과 맞추면 다 삭제될 내용
        // 전체 정책별 배송비와 지역별 배송비의 총합 구하기
        foreach ($getData['goods'] as $sKey => $sVal) {
            foreach ($sVal as $dKey => $dVal) {
//                $dVal = array_shift($dVal);
                foreach ($dVal as $gKey => $gVal) {
                    $realDeliveryTaxSum = array_sum([
                        $gVal['realTaxSupplyDeliveryCharge'],
                        $gVal['realTaxVatDeliveryCharge'],
                        $gVal['realTaxFreeDeliveryCharge'],
                    ]);
                    // 전체 배송비 계산 (주문상품추가/주문타상품교환으로 같은 배송비 조건(deliverySno)이더라도 주문배송비(orderDeliverySno)가 추가된다
                    $deliveryKeyCheck = $dKey . '-' . $gVal['orderDeliverySno'];
                    if ($deliveryKeyCheck == $deliveryUniqueKey) {
                        if (in_array($gVal['orderStatus'], ['c1', 'c2', 'c3', 'c4'])) {
                            $tempCancelDeliveryKey = substr($gVal['orderStatus'], 0, 1) . '-' . $gVal['orderDeliverySno'];
                            if (!in_array($tempCancelDeliveryKey, $aCancelDeliveryKey)) {
                                $aCancelDeliveryKey[] = $tempCancelDeliveryKey;
                            }
                        }
                    } else {
                        if (in_array($gVal['orderStatus'], ['c1', 'c2', 'c3', 'c4'])) {
                            $tempCancelDeliveryKey = substr($gVal['orderStatus'], 0, 1) . '-' . $gVal['orderDeliverySno'];
                            if (!in_array($tempCancelDeliveryKey, $aCancelDeliveryKey)) {
                                $aCancelDeliveryKey[] = $tempCancelDeliveryKey;
                            }
                        }
                        $getData['totalDeliveryPolicyCharge'] += $gVal['deliveryPolicyCharge'];
                        $getData['totalDeliveryAreaCharge'] += $gVal['deliveryAreaCharge'];
                        $getData['totalTaxSupplyDeliveryCharge'] += $gVal['taxSupplyDeliveryCharge'];
                        $getData['totalTaxVatDeliveryCharge'] += $gVal['taxVatDeliveryCharge'];
                        $getData['totalTaxFreeDeliveryCharge'] += $gVal['taxFreeDeliveryCharge'];

                        // 배송무게 계산
                        $getData['totalDeliveryWeights'] = json_decode($gVal['deliveryWeightInfo'], true);
                    }
                    $deliveryUniqueKey = $deliveryKeyCheck;
                }
            }
        }

        if (Manager::isProvider()) {
            $getData = $this->getProviderTotalPrice($getData);
        }

        // 주문상담내역 추가
        $getData['consult'] = $this->getOrderConsult($orderNo);

        // 안심번호 기간만료시 상태값 변경하여 리턴
        if ($getData['receiverUseSafeNumberFl'] == 'y' && empty($getData['receiverSafeNumber']) == false && empty($getData['receiverSafeNumberDt']) == false && DateTimeUtils::intervalDay($getData['receiverSafeNumberDt'], date('Y-m-d H:i:s')) > 30) {
            $getData['receiverUseSafeNumberFl'] = 'e';
        }

        if ($getData['multiShippingFl'] == 'y' && count($getData['multiShippingList']) > 0) {
            foreach ($getData['multiShippingList'] as $mKey => $mVal) {
                if ($mVal['receiverUseSafeNumberFl'] == 'y' && empty($mVal['receiverSafeNumber']) == false && empty($getData['receiverSafeNumberDt']) == false && DateTimeUtils::intervalDay($mVal['receiverSafeNumberDt'], date('Y-m-d H:i:s')) > 30) {
                    $getData['multiShippingList'][$mKey]['receiverUseSafeNumberFl'] = 'e';
                }
            }
        }

        return $getData;
    }

    public function getProviderTotalPriceList($getData, $orderNo)
    {
        $excludeOrderStatus = ['o', 'c', 'e'];
        $excludeFullOrderStatus = ['r3'];
        $deliveryUniqueKey = '';
        $loopOrderGoodsData = [];

        //한 주문당 주문상품 갯수 (주문번호별의 주문상품명에 사용)
        $orderGoodsCntArr = [];
        //총 상품금액
        $totalGoodsPriceArr = [];
        //총 주문금액
        $totalOrderPriceArr = [];
        //총 배송비
        $totalDeliveryChargeArr = [];

        if((!$this->search['view']) || $this->search['view'] === 'order'){
            $arrIncludeOg = [
                'goodsPrice',
                'optionPrice',
                'optionTextPrice',
                'goodsCnt',
                'orderDeliverySno',
                'addGoodsPrice',
                'orderNo',
                'scmNo',
                'orderStatus',
            ];
            $arrIncludeOd = [
                'deliveryPolicyCharge',
                'deliveryAreaCharge',
                'deliverySno',
                'realTaxSupplyDeliveryCharge',
                'realTaxVatDeliveryCharge',
                'realTaxFreeDeliveryCharge',
                'divisionDeliveryUseMileage',
                'divisionDeliveryUseDeposit',
                'divisionMemberDeliveryDcPrice',
                'divisionDeliveryCharge',
            ];

            $arrField[] = implode(", ", DBTableField::setTableField('tableOrderGoods', $arrIncludeOg, [], 'og'));
            $arrField[] = implode(", ", DBTableField::setTableField('tableOrderDelivery', $arrIncludeOd, [], 'od'));

            $join[] = 'LEFT JOIN ' . DB_ORDER_DELIVERY . " AS od ON og.orderDeliverySno = od.sno ";

            $arrBind = [];
            $arrWhere[] = 'og.orderNo = ?';
            $arrWhere[] = 'og.scmNo = ?';
            $this->db->bind_param_push($arrBind, 's', $orderNo);
            $this->db->bind_param_push($arrBind, 'i', Session::get('manager.scmNo'));

            $this->db->strJoin = implode(' ', $join);
            $this->db->strField = implode(', ', $arrField);
            $this->db->strWhere = implode(' AND ', $arrWhere);
            $query = $this->db->query_complete();
            $strSQL = 'SELECT ' . array_shift($query) . ' FROM ' . DB_ORDER_GOODS . ' AS og ' . implode(' ', $query);
            $tmpLoopOrderGoodsData = $this->db->query_fetch($strSQL, $arrBind);

            foreach($tmpLoopOrderGoodsData as $key => $val){
                if(in_array(substr($val['orderStatus'], 0, 1), $excludeOrderStatus)){
                    continue;
                }
                // 상품배열을 [SCM][Delivery][goods]의 3차 배열로 재구성
                $loopOrderGoodsData[$val['scmNo']][$val['deliverySno']][] = $val;
            }
        }
        else {
            $loopOrderGoodsData = $getData['goods'];
        }

        foreach ($loopOrderGoodsData as $sKey => $sVal) {
            foreach ($sVal as $dKey => $dVal) {
                foreach ($dVal as $gKey => $gVal) {
                    if($gVal['scmNo'] != Session::get('manager.scmNo')){
                        continue;
                    }
                    if(in_array(substr($gVal['orderStatus'], 0, 1), $excludeOrderStatus)){
                        continue;
                    }

                    //한 주문당 주문상품 갯수 (주문번호별의 주문상품명에 사용)
                    $orderGoodsCntArr[$gVal['orderNo']]++;
                    //총 상품금액
                    $totalGoodsPriceArr[$gVal['orderNo']][] = (($gVal['goodsPrice'] + $gVal['optionPrice'] + $gVal['optionTextPrice']) * $gVal['goodsCnt']) + $gVal['addGoodsPrice'];
                    //총 주문금액
                    if(!in_array($gVal['orderStatus'], $excludeFullOrderStatus)){
                        $totalOrderPriceArr[$gVal['orderNo']][] = (($gVal['goodsPrice'] + $gVal['optionPrice'] + $gVal['optionTextPrice']) * $gVal['goodsCnt']) + $gVal['addGoodsPrice'];
                    }

                    $deliveryKeyCheck = $gVal['deliverySno'] . '-' . $gVal['orderDeliverySno'];
                    if ($deliveryKeyCheck != $deliveryUniqueKey) {
                        //총 배송비
                        $totalDeliveryChargeArr[$gVal['orderNo']][] = ($gVal['realTaxSupplyDeliveryCharge'] + $gVal['realTaxVatDeliveryCharge'] + $gVal['realTaxFreeDeliveryCharge'] + $gVal['divisionDeliveryUseMileage'] + $gVal['divisionDeliveryUseDeposit'] + $gVal['divisionMemberDeliveryDcPrice'] + $gVal['divisionDeliveryCharge']);
                    }

                    $deliveryUniqueKey = $deliveryKeyCheck;
                }
            }
        }

        foreach ($getData['goods'] as $sKey => &$sVal) {
            foreach ($sVal as $dKey => &$dVal) {
                foreach ($dVal as $gKey => &$gVal) {
                    if($gVal['scmNo'] != Session::get('manager.scmNo')){
                        continue;
                    }
                    //주문 상품명 재정의
                    $orderGoodsCnt = $orderGoodsCntArr[$gVal['orderNo']];
                    $gVal['orderGoodsNm'] = $gVal['goodsNm'] . ((int)$orderGoodsCnt < 2 ? '' : __(' 외 ') . ((int)$orderGoodsCnt - 1) . __('건'));
                    //총 상품금액
                    $gVal['totalGoodsPrice'] = gd_isset(array_sum($totalGoodsPriceArr[$gVal['orderNo']]), 0);
                    //총 배송비
                    $gVal['totalDeliveryCharge'] = gd_isset(array_sum($totalDeliveryChargeArr[$gVal['orderNo']]), 0);
                    //총 주문금액
                    $gVal['totalOrderPrice'] = gd_isset(array_sum($totalOrderPriceArr[$gVal['orderNo']]), 0) + $gVal['totalDeliveryCharge'];
                    //총 실결제 금액
                    $gVal['settlePrice'] = $gVal['totalOrderPrice'];
                }
            }
        }

        return $getData;
    }

    public function getProviderTotalPriceExcelList($data, $orderType)
    {
        $loopOrderGoodsData = [];
        $excludeFullOrderStatus = ['r3'];

        //총 상품금액
        $totalGoodsPriceArr = [];
        //총 배송비
        $totalDeliveryChargeArr = [];
        //총 주문금액을 구하는데에 사용될 상품금액
        $totalOrderGoodsPriceArr = [];
        //배송정책
        $deliverySnoArr = [];
        //총 주문품목갯수
        $totalOrderCntArr = [];
        //총 상품건수
        $totalGoodsCntArr = [];

        if($orderType === 'goods'){
            $loopOrderGoodsData = $data['orderList'];
        }
        else {
            foreach($data['orderList'] as $key => $orderListData){
                $arrBind = $arrField = $arrIncludeOg = $arrIncludeOd = $join = $arrWhere = [];
                $arrIncludeOg = [
                    'goodsPrice',
                    'optionPrice',
                    'optionTextPrice',
                    'goodsCnt',
                    'orderDeliverySno',
                    'addGoodsPrice',
                    'orderNo',
                    'scmNo',
                    'orderStatus',
                ];
                $arrIncludeOd = [
                    'deliveryPolicyCharge',
                    'deliveryAreaCharge',
                    'deliverySno',
                    'realTaxSupplyDeliveryCharge',
                    'realTaxVatDeliveryCharge',
                    'realTaxFreeDeliveryCharge',
                    'divisionDeliveryUseMileage',
                    'divisionDeliveryUseDeposit',
                    'divisionMemberDeliveryDcPrice',
                    'divisionDeliveryCharge',
                ];

                $arrField[] = implode(", ", DBTableField::setTableField('tableOrderGoods', $arrIncludeOg, [], 'og'));
                $arrField[] = implode(", ", DBTableField::setTableField('tableOrderDelivery', $arrIncludeOd, [], 'od'));

                $join[] = 'LEFT JOIN ' . DB_ORDER_DELIVERY . " AS od ON og.orderDeliverySno = od.sno ";

                $arrWhere[] = 'og.orderNo = ?';
                $arrWhere[] = 'og.scmNo = ?';
                $this->db->bind_param_push($arrBind, 's', $orderListData['orderNo']);
                $this->db->bind_param_push($arrBind, 'i', Session::get('manager.scmNo'));

                $this->db->strJoin = implode(' ', $join);
                $this->db->strField = implode(', ', $arrField);
                $this->db->strWhere = implode(' AND ', $arrWhere);
                $query = $this->db->query_complete();
                $strSQL = 'SELECT ' . array_shift($query) . ' FROM ' . DB_ORDER_GOODS . ' AS og ' . implode(' ', $query);
                $resultData = $this->db->query_fetch($strSQL, $arrBind);
                $loopOrderGoodsData = array_merge((array)$loopOrderGoodsData, (array)$resultData);

                unset($arrBind, $strSQL, $arrField, $arrIncludeOg, $arrIncludeOd, $join, $arrWhere, $query);
            }
        }

        foreach($loopOrderGoodsData as $key => $val) {
            if(in_array(substr($val['orderStatus'], 0, 1), ['o', 'c', 'e'])){
                continue;
            }

            $goodsPrice = ($val['goodsPrice'] + $val['optionPrice'] + $val['optionTextPrice']) * $val['goodsCnt'];

            //총 주문품목갯수
            $totalOrderCntArr[$val['orderNo']][] = 1;
            //총 상품금액
            $totalGoodsPriceArr[$val['orderNo']][] = $goodsPrice;
            //총 주문금액을 구하는데에 사용될 상품금액
            if(!in_array($val['orderStatus'], $excludeFullOrderStatus)){
                $totalOrderGoodsPriceArr[$val['orderNo']][] = $goodsPrice;
            }
            //총 상품수량
            $totalGoodsCntArr[$val['orderNo']][] = $val['goodsCnt'];
            //총 배송비
            $totalDeliveryChargeArr[$val['orderNo']][] = ($val['realTaxSupplyDeliveryCharge'] + $val['realTaxVatDeliveryCharge'] + $val['realTaxFreeDeliveryCharge'] + $val['divisionDeliveryUseMileage'] + $val['divisionDeliveryUseDeposit'] + $val['divisionMemberDeliveryDcPrice'] + $val['divisionDeliveryCharge']);
            // 배송정책
            $deliverySnoArr[$val['orderNo']][] = $val['orderDeliverySno'];
        }

        foreach($data['orderList'] as $key => &$val) {
            //총 상품금액
            $val['totalGoodsPrice'] = (int)array_sum($totalGoodsPriceArr[$val['orderNo']]);
            //총 주문금액을 구하는데에 사용될 상품금액
            $val['totalOrderGoodsPrice'] = (int)array_sum($totalOrderGoodsPriceArr[$val['orderNo']]);
            //총 주문품목갯수
            $val['scmOrderCnt'] = (int)array_sum($totalOrderCntArr[$val['orderNo']]);
            //총 상품수량
            $val['scmGoodsCnt'] = (int)array_sum($totalGoodsCntArr[$val['orderNo']]);
            //배송비 구분
            $val['scmDeliveryCharge'] = implode(STR_DIVISION, $totalDeliveryChargeArr[$val['orderNo']]);
            //배송정책 구분
            $val['scmDeliverySno'] = implode(STR_DIVISION, $deliverySnoArr[$val['orderNo']]);
            //주문상품명
            $val['scmGoodsNm'] = $val['goodsNm'];

            if($orderType === 'goods'){
                $data['totalScmInfo'][$val['orderNo']]['totalGoodsPrice'] = $val['totalGoodsPrice'];
                $data['totalScmInfo'][$val['orderNo']]['scmOrderCnt'] = $val['scmOrderCnt'];
                $data['totalScmInfo'][$val['orderNo']]['scmGoodsCnt'] = $val['scmGoodsCnt'];
                $data['totalScmInfo'][$val['orderNo']]['scmDeliveryCharge'] = $val['scmDeliveryCharge'];
                $data['totalScmInfo'][$val['orderNo']]['scmDeliverySno'] = $val['scmDeliverySno'];
                if(trim($data['totalScmInfo'][$val['orderNo']]['scmGoodsNm']) === ''){
                    $data['totalScmInfo'][$val['orderNo']]['scmGoodsNm'] = $val['scmGoodsNm'];
                }
            }
        }

        unset($totalGoodsPriceArr, $totalGoodsCntArr, $totalDeliveryChargeArr, $deliverySnoArr, $totalOrderCntArr, $totalGoodsCntArr);

        return $data;
    }

    public function getProviderTotalPrice($getData)
    {
        $excludeFullOrderStatus = ['r3'];
        $deliveryUniqueKey = '';
        //총 상품금액
        $getData['totalGoodsPrice'] = 0;
        //총 배송비
        $getData['totalDeliveryCharge'] = 0;
        //총 주문금액
        $totalOrderPriceArr = [];
        $getData['totalOrderPrice'] = 0;

        foreach ($getData['goods'] as $sKey => $sVal) {
            foreach ($sVal as $dKey => $dVal) {
                foreach ($dVal as $gKey => $gVal) {
                    if($gVal['scmNo'] != Session::get('manager.scmNo')){
                        continue;
                    }
                    if(in_array(substr($gVal['orderStatus'], 0, 1), ['o', 'c', 'e'])){
                        continue;
                    }

                    $goodsPrice = (($gVal['goodsPrice'] + $gVal['optionPrice'] + $gVal['optionTextPrice']) * $gVal['goodsCnt']) + $gVal['addGoodsPrice'];

                    //총 상품금액
                    $getData['totalGoodsPrice'] += $goodsPrice;
                    //총 주문금액
                    if(!in_array($gVal['orderStatus'], $excludeFullOrderStatus)){
                        $totalOrderPriceArr[] = $goodsPrice;
                    }

                    $deliveryKeyCheck = $gVal['deliverySno'] . '-' . $gVal['orderDeliverySno'];
                    if ($deliveryKeyCheck != $deliveryUniqueKey) {
                        //총 배송비
                        $getData['totalDeliveryCharge'] += ($gVal['realTaxSupplyDeliveryCharge'] + $gVal['realTaxVatDeliveryCharge'] + $gVal['realTaxFreeDeliveryCharge'] + $gVal['divisionDeliveryUseMileage'] + $gVal['divisionDeliveryUseDeposit'] + $gVal['divisionMemberDeliveryDcPrice'] + $gVal['divisionDeliveryCharge']);
                    }

                    $deliveryUniqueKey = $deliveryKeyCheck;
                }
            }
        }

        //총 주문 금액 : 총 상품금액 + 총 배송비 - 총 할인금액
        $getData['totalOrderPrice'] = array_sum($totalOrderPriceArr) + $getData['totalDeliveryCharge'];

        return $getData;
    }

    /**
     * 관리자 주문 상세정보 (프린트용 - 세금계산서, 간이영수증, 거래명세서, 주문내역서)
     *
     * @param array $strOrderNo 주문 번호
     * @param string $printMode 프린트 모드
     *
     * @return array 주문 상세정보
     * @throws Exception
     */
    public function getOrderPrint($strOrderNo, $printMode)
    {
        // 주문 번호 체크
        if (empty($strOrderNo) === true) {
            throw new Exception(__('주문번호은(는) 필수 항목 입니다.'));
        }

        // 주문 번호 체크
        $arrOrderNo = array_unique(ArrayUtils::removeEmpty(explode(INT_DIVISION, $strOrderNo)));
        if (is_array($arrOrderNo) === false) {
            throw new Exception(__('주문번호은(는) 필수 항목 입니다.'));
        }

        //복수배송지 사용 여부에 따라 페이지 노출시 scmNo 의 키를 order info sno 로 교체한다.
        $orderMultiShipping = App::load('\\Component\\Order\\OrderMultiShipping');

        $orderReorderCalculation = \App::load('\\Component\\Order\\ReOrderCalculation');
        $handleSnoArr = [];

        // --- 상품 과세 / 비과세 설정 config 불러오기
        $taxConf = gd_policy('goods.tax');

        // 주문 번호 배열에 따른 처리
        foreach ($arrOrderNo as $key => $orderNo) {

            // 주문 기본 정보
            $arrExclude = [
                'orderPGLog',
                'orderDeliveryLog',
                'orderAdminLog',
                'pgName',
                'pgResultCode',
                'pgTid',
                'pgAppNo',
                'pgFailReason',
                'escrowDeliveryFl',
                'escrowDeliveryDt',
                'escrowDeliveryCd',
                'escrowInvoiceNo',
                'escrowConfirmFl',
                'escrowDenyFl',
            ];
            $getData[$key] = $this->getOrderData($orderNo, $arrExclude);

            // 주문 데이타 체크
            if (empty($getData[$key]) === true) {
                continue;
            }

            $useMultiShippingKey = $orderMultiShipping->checkChangeOrderListKey($getData[$key]['multiShippingFl']);
            $getData[$key]['useMultiShippingKey'] = $useMultiShippingKey;

            // 멀티상점 기준몰 여부
            $getData[$key]['isDefaultMall'] = $this->isDefaultMall($getData[$key]['mallSno']);

            // 멀티상점 환율 기본 정보
            $getData[$key]['currencyPolicy'] = json_decode($getData[$key]['currencyPolicy'], true);
            $getData[$key]['exchangeRatePolicy'] = json_decode($getData[$key]['exchangeRatePolicy'], true);
            $getData[$key]['currencyIsoCode'] = $getData[$key]['currencyPolicy']['isoCode'];
            $getData[$key]['exchangeRate'] = $getData[$key]['exchangeRatePolicy']['exchangeRate' . $getData[$key]['currencyPolicy']['isoCode']];

            //총 할인금액
            $totalDcPriceArray = [
                $getData[$key]['totalGoodsDcPrice'],
                $getData[$key]['totalMemberDcPrice'],
                $getData[$key]['totalMemberOverlapDcPrice'],
                $getData[$key]['totalCouponGoodsDcPrice'],
                $getData[$key]['totalCouponOrderDcPrice'],
                $getData[$key]['totalMemberDeliveryDcPrice'],
                $getData[$key]['totalCouponDeliveryDcPrice'],
                $getData[$key]['totalEnuriDcPrice'],
            ];

            // 마이앱 사용에 따른 분기 처리
            if ($this->useMyapp) {
                array_push($totalDcPriceArray, $getData[$key]['totalMyappDcPrice']);
            }

            $getData[$key]['totalSalePrice'] = array_sum($totalDcPriceArray);

            //총 부가결제 금액
            $getData[$key]['totalUseAddedPrice'] = $getData[$key]['useMileage']+$getData[$key]['useDeposit'];

            //총 주문 금액 : 총 상품금액 + 총 배송비 - 총 할인금액
            $getData[$key]['totalOrderPrice'] = $getData[$key]['totalGoodsPrice'] + $getData[$key]['totalDeliveryCharge'] - $getData[$key]['totalSalePrice'];

            //총 실 결제금액
            if($getData[$key]['orderChannelFl'] === 'naverpay'){
                // 네이버페이 포인트를 사용한 경우 realtax 에 값이 담기지 않아 실금액을 구할 수 없으므로 settlePrice 를 사용한다.
                $getData[$key]['totalRealSettlePrice'] = $getData[$key]['checkoutData']['orderData']['GeneralPaymentAmount'];
            }
            else {
                $getData[$key]['totalRealSettlePrice'] = $getData[$key]['realTaxSupplyPrice'] + $getData[$key]['realTaxVatPrice'] + $getData[$key]['realTaxFreePrice'];
            }

            // + $getData[$key]['useDeposit'] + $getData[$key]['useMileage']

            // 결제금액은 예치금과 마일리지를 제외하고 출력
            //$getData[$key]['settlePrice'] += ($getData[$key]['useDeposit'] + $getData[$key]['useMileage']);

            // 현재 주문 상태코드 (한자리)
            $getData[$key]['statusMode'] = substr($getData[$key]['orderStatus'], 0, 1);

            // PG 결과 처리
            $getData[$key]['pgSettleNm'] = explode(STR_DIVISION, $getData[$key]['pgSettleNm']);
            $getData[$key]['pgSettleCd'] = explode(STR_DIVISION, $getData[$key]['pgSettleCd']);

            // 주문 사은품 정보 (해당 주문의 모든 사은품 정보)
            $loginedScmNo = Manager::isProvider() ? Session::get('manager.scmNo') : null;
            $getData[$key]['gift'] = $this->getOrderGift($orderNo, $loginedScmNo, 40);
            unset($loginedScmNo);

            $arrGoods = [
                'particular',
                'report',
                'customerReport',
                'reception',
            ];
            if (in_array($printMode, $arrGoods) === true) {
                // 상품정보 불러오기
                $orderGoods = $this->getOrderGoodsData($orderNo, null, null, null, 'admin', false, true, null, null, false, $useMultiShippingKey);

                //복수배송지를 사용중이며 복수배송지가 사용된 주문건 일시 주문데이터에 order info 데이터를 따로 저장해둔다.
                if($getData[$key]['multiShippingFl'] === 'y'){
                    $orderInfoSnoArr = [];
                    if(Manager::isProvider()){
                        foreach ($orderGoods as $gKey => $gVal) {
                            if($gVal['scmNo'] != Session::get('manager.scmNo')){
                                continue;
                            }
                            $infoSno = $gVal['orderInfoSno'] > 0 ? $gVal['orderInfoSno'] : $getData[$key]['infoSno'];
                            if ($gVal['goodsType'] == 'goods' && ($gVal['deliveryMethodFl'] == 'visit' || empty($gVal['visitAddress']) === false) && in_array($gVal['sno'], $getData[$key]['checkoutData']['goodsSno']) === true) {
                                $getData[$key]['visitDelivery'][$infoSno][$gVal['sno']] = $gVal['deliverySno'];
                                $getData[$key]['visitAddressInfo'][$infoSno][$gVal['sno']] = $gVal['visitAddress'];
                                $getData[$key]['deliveryMethodFl'][$infoSno][$gVal['sno']] = $gVal['deliveryMethodFl'];
                            }
                            $orderInfoSnoArr[] = $gVal['orderInfoSno'];
                        }
                    } else {
                        foreach ($orderGoods as $gKey => $gVal) {
                            $infoSno = $gVal['orderInfoSno'] > 0 ? $gVal['orderInfoSno'] : $getData[$key]['infoSno'];
                            if ($gVal['goodsType'] == 'goods' && ($gVal['deliveryMethodFl'] == 'visit' || empty($gVal['visitAddress']) === false) && in_array($gVal['sno'], $getData[$key]['checkoutData']['goodsSno']) === true) {
                                $getData[$key]['visitDelivery'][$infoSno][$gVal['sno']] = $gVal['deliverySno'];
                                $getData[$key]['visitAddressInfo'][$infoSno][$gVal['sno']] = $gVal['visitAddress'];
                                $getData[$key]['deliveryMethodFl'][$infoSno][$gVal['sno']] = $gVal['deliveryMethodFl'];
                            }
                            $orderInfoSnoArr[] = $gVal['orderInfoSno'];
                        }
                    }
                    $getData[$key]['multiShippingList'] = $this->getOrderInfo($orderNo, true, 'orderInfoCd asc', $orderInfoSnoArr);
                    if(Manager::isProvider()) {
                        $getData[$key] = $orderMultiShipping->changeReceiverData($getData[$key], $getData[$key]['multiShippingList']);
                    }
                } else {
                    $getData[$key]['invoiceNo'] = $orderGoods[$key]['invoiceNo'];
                    $getData[$key]['deliveryDt'] = $orderGoods[$key]['deliveryDt'];
                    $getData[$key]['deliveryCompleteDt'] = $orderGoods[$key]['deliveryCompleteDt'];
                    foreach ($orderGoods as $gKey => $gVal) {
                        $infoSno = $gVal['orderInfoSno'] > 0 ? $gVal['orderInfoSno'] : $getData[$key]['infoSno'];
                        if ($gVal['goodsType'] == 'goods' && ($gVal['deliveryMethodFl'] == 'visit' || empty($gVal['visitAddress']) === false) && in_array($gVal['sno'], $getData[$key]['checkoutData']['goodsSno']) === true) {
                            $getData[$key]['visitDelivery'][$infoSno][$gVal['sno']] = $gVal['deliverySno'];
                            $getData[$key]['visitAddressInfo'][$infoSno][$gVal['sno']] = $gVal['visitAddress'];
                            $getData[$key]['deliveryMethodFl'][$infoSno][$gVal['sno']] = $gVal['deliveryMethodFl'];
                        }
                    }
                }

                // 주문 상품 정보
                if ($printMode == 'report' || $printMode == 'customerReport' || $printMode == 'particular' || $printMode == 'reception') {
                    // 주문 배송 정보
                    $getData[$key]['delivery'] = $this->getOrderDelivery($orderNo);


                    if ($printMode == 'particular') {
                        //거래명세서 출력 설정에 따른 정보표기
                        $getData[$key]['orderPrint'] = $this->getOrderPrintData($getData[$key]);

                        // 거래명세서 배송비 계산
                        $getData[$key]['totalDeliveryCharge'] = 0;
                        foreach ($getData[$key]['delivery'] as $dVal) {
                            foreach ($dVal as $oKey => $oVal) {
                                $deliveryTax = explode(STR_DIVISION, $oVal['deliveryTaxInfo']);

                                $realTaxDeliveryPrice = $oVal['realTaxSupplyDeliveryCharge'] + $oVal['realTaxVatDeliveryCharge'] + $oVal['realTaxFreeDeliveryCharge'];
                                $totalDeliverySettlePrice = $realTaxDeliveryPrice + $oVal['divisionDeliveryUseDeposit'] + $oVal['divisionDeliveryUseMileage'] + $oVal['divisionDeliveryCharge'] + $oVal['divisionMemberDeliveryDcPrice'];
                                $getData[$key]['totalDeliveryCharge'] += $totalDeliverySettlePrice;
                                $totalDeliverySupplyPrice = $oVal['realTaxSupplyDeliveryCharge'] + $oVal['realTaxFreeDeliveryCharge'];
                                $realDeliveryVatRate = gd_tax_rate($realTaxDeliveryPrice, $totalDeliverySupplyPrice);
                                $tmpDeliveryVatData = gd_tax_all($totalDeliverySettlePrice, $realDeliveryVatRate, $deliveryTax[0]);
                                $getData[$key]['deliveryVat']['supply'] += $tmpDeliveryVatData['supply'];
                                $getData[$key]['deliveryVat']['tax'] += $tmpDeliveryVatData['tax'];
                            }
                        }
                    }

                    // 결제 방법
                    $getData[$key]['settle']['name'] = $this->getSettleKind($getData[$key]['settleKind']);
                    $getData[$key]['settle']['escrow'] = substr($getData[$key]['settleKind'], 0, 1);
                    $getData[$key]['settle']['method'] = substr($getData[$key]['settleKind'], 1, 1);
                }
                $totalGoodsPrice = 0;
                $totalPriceSupply = 0;
                $totalAddPriceSupply = 0;
                $particularUseMileage = 0;
                $particularUseDeposit = 0;

                // 상품별 부가세율 계산
                foreach ($orderGoods as $gKey => & $gVal) {
                    $singleOrderStatus = substr($gVal['orderStatus'], 0, 1);

                    // 공급사로 로그인한 경우 처리 해당 공급사 상품만 보여지도록 수정 처리
                    // 주문내역서를 제외한 영수증에는 본사상품이 포함되서 노출된다.
                    if (Manager::isProvider() && ($printMode === 'report' || $printMode === 'customerReport')) {
                        if ($gVal['scmNo'] != Session::get('manager.scmNo')) {
                            continue;
                        }
                        if ($singleOrderStatus === 'o' || $singleOrderStatus === 'c') {
                            continue;
                        }
                    }

                    if ($printMode == 'particular') {
                        if($singleOrderStatus === 'e' || $singleOrderStatus === 'c'){
                            continue;
                        }

                        // 환불완료의 상품은 이름만 노출되도록 상품정보 재구성. 필요한 정보가 있을시 아래에 추가하면 된다.
                        if($gVal['orderStatus'] === 'r3'){
                            // 마이앱 사용에 따른 분기 처리
                            if ($this->useMyapp) {
                                $getData[$key]['totalSalePrice'] -= array_sum([
                                    $gVal['divisionCouponOrderDcPrice'],
                                    $gVal['goodsDcPrice'],
                                    $gVal['memberDcPrice'],
                                    $gVal['memberOverlapDcPrice'],
                                    $gVal['couponGoodsDcPrice'],
                                    $gVal['enuri'],
                                    $gVal['myappDcPrice'],
                                ]);
                            } else {
                                $getData[$key]['totalSalePrice'] -= array_sum([
                                    $gVal['divisionCouponOrderDcPrice'],
                                    $gVal['goodsDcPrice'],
                                    $gVal['memberDcPrice'],
                                    $gVal['memberOverlapDcPrice'],
                                    $gVal['couponGoodsDcPrice'],
                                    $gVal['enuri'],
                                ]);
                            }

                            $gVal = [
                                'orderStatus' => $gVal['orderStatus'],
                                'goodsNm' => $gVal['goodsNm'],
                                'goodsNmStandard' => $gVal['goodsNmStandard'],
                                'optionInfo' => $gVal['optionInfo'],
                                'goodsNo' => $gVal['goodsNo'],
                                'goodsCd' => $gVal['goodsCd'],
                            ];
                        }
                        else {
                            $particularUseMileage += ($gVal['divisionUseMileage']+$gVal['divisionGoodsDeliveryUseMileage']);
                            $particularUseDeposit += ($gVal['divisionUseDeposit']+$gVal['divisionGoodsDeliveryUseDeposit']);
                        }
                    }

                    // 상품별 과세/비과세 정보
                    $goodsTax = $gVal['goodsTaxInfo'];

                    // 추가 옵션 가격 설정
                    if ($gVal['optionPrice'] > 0) {
                        $gVal['optionPrice'] = gd_vat_included($gVal['optionPrice'], $goodsTax[1], $goodsTax[0], $taxConf['priceTaxFl']); // 과세/비과세 설정에 따른 금액 계산
                    }

                    // 텍스트 옵션 가격 설정
                    if ($gVal['optionTextPrice'] > 0) {
                        $gVal['optionTextPrice'] = gd_vat_included($gVal['optionTextPrice'], $goodsTax[1], $goodsTax[0], $taxConf['priceTaxFl']); // 과세/비과세 설정에 따른 금액 계산
                    }

                    // 상품 가격 설정
                    $gVal['goodsPrice'] = gd_vat_included($gVal['goodsPrice'], $goodsTax[1], $goodsTax[0], $taxConf['priceTaxFl']); // 과세/비과세 설정에 따른 금액 계산

                    // 상품 금액
                    $gVal['goodsSumPrice'] = ($gVal['goodsPrice'] + $gVal['optionPrice'] + $gVal['optionTextPrice']) * $gVal['goodsCnt'];

                    // 상품 총금액
                    $totalGoodsPrice = $totalGoodsPrice + $gVal['goodsSumPrice'];

                    // 상품의 부가세 계산

                    if ($printMode == 'particular') {
                        // 거래명세서의 경우 실질적인 세액비율로 계산한다.
                        $realTotalSettlePrice = $gVal['realTaxSupplyGoodsPrice'] + $gVal['realTaxFreeGoodsPrice'] + $gVal['realTaxVatGoodsPrice'];
                        $realTotalSupplyPrice = $gVal['realTaxSupplyGoodsPrice'] + $gVal['realTaxFreeGoodsPrice'];
                        $realGoodsVatRate = gd_tax_rate($realTotalSettlePrice, $realTotalSupplyPrice);
                        $gVal['goodsVat'] = gd_tax_all($gVal['goodsSumPrice'], $realGoodsVatRate, $goodsTax[0]);
                    }
                    else {
                        $gVal['goodsVat'] = gd_tax_all($gVal['goodsSumPrice'], $goodsTax[1], $goodsTax[0]);
                    }

                    // 상품 총 부가세금액
                    $totalPriceSupply = $totalPriceSupply + $gVal['goodsVat']['supply'];

                    //옵션명
                    $optionInfo = $optName = [];
                    if (empty($gVal['optionInfo']) === false) {
                        foreach ($gVal['optionInfo'] as $opt) {
                            $optionInfo['opt'][] = $opt['optionValue'];
                        }
                        $opt = @implode(' / ', $optionInfo['opt']);
                        if(!empty($gVal['optionInfo'][0]['deliveryInfoStr'])){
                            $optName[] = '[' . $opt . '][' . $gVal['optionInfo'][0]['deliveryInfoStr'] . ']';
                        }else{
                            $optName[] = '[' . $opt . ']';
                        }
                    }
                    if (empty($gVal['optionTextInfo']) === false) {
                        foreach ($gVal['optionTextInfo'] as $optText) {
                            $optionInfo['optText'][] = $optText['optionName'] . ' : ' . $optText['optionValue'];
                        }
                        $optText = @implode(' / ', $optionInfo['optText']);
                        $optName[] = '[' . $optText . ']';
                    }
                    $gVal['optName'] = @implode(', ', $optName);
                    unset($optionInfo, $optName, $opt, $optText);

                    // 추가상품 과세/비과세 설정에 따른 금액 계산
                    if (empty($gVal['addGoods']) === false) {
                        foreach ($gVal['addGoods'] as $aKey => $aVal) {
                            // 상품별 과세/비과세 정보
                            $goodsTax = explode(STR_DIVISION, $aVal['goodsTaxInfo']);

                            // 추가상품명 태그 제거
                            $aVal['goodsNm'] = StringUtils::removeTag($aVal['goodsNm']);

                            // 상품 가격 설정
                            $aVal['goodsPrice'] = gd_vat_included($aVal['goodsPrice'], $goodsTax[1], $goodsTax[0], $taxConf['priceTaxFl']); // 과세/비과세 설정에 따른 금액 계산

                            // 상품 금액
                            $aVal['goodsSumPrice'] = ($aVal['goodsPrice']) * $aVal['goodsCnt'];

                            // 상품 총금액
                            $totalGoodsPrice = $totalGoodsPrice + $aVal['goodsSumPrice'];

                            // 상품의 부가세 계산
                            $aVal['goodsVat'] = gd_tax_all($aVal['goodsSumPrice'], $goodsTax[1], $goodsTax[0]);

                            // 상품 총 부가세금액
                            $totalPriceSupply = $totalPriceSupply + $aVal['goodsVat']['supply'];

                            $gVal['addGoods'][$aKey] = $aVal;
                        }
                    }

                    // 해외 배송무게 설정
                    $getData[$key]['deliveryWeightInfo'] = json_decode($gVal['deliveryWeightInfo'], true);

                    // 가공된 데이터 반환값에 설정
                    if($useMultiShippingKey === true){
                        //복수배송지를 사용 중이며 리스트에서 노출시킬 목적으로만 사용중이면 주문데이터 배열의 scmNo 를 orderInfoSno로 대체한다.
                        $getData[$key]['goods'][$gVal['orderInfoSno']][$gVal['orderDeliverySno']][] = $gVal;
                    }
                    else {
                        $getData[$key]['goods'][$gVal['scmNo']][$gVal['deliverySno']][] = $gVal;
                    }

                    // 테이블 UI 표현을 위한 변수
                    $addGoodsCnt = empty($gVal['addGoods']) === false ? count($gVal['addGoods']) : 0;
                    //복수배송지를 사용 중이며 리스트에서 노출시킬 목적으로만 사용중이면 주문데이터 배열의 scmNo 를 orderInfoSno로 대체한다.
                    if($useMultiShippingKey === true){
                        $getData[$key]['cnt']['multiShipping'][$gVal['orderInfoSno']] += 1 + $addGoodsCnt;
                    }
                    else {
                        $getData[$key]['cnt']['scm'][$gVal['scmNo']] += 1 + $addGoodsCnt;
                    }
                    $deliveryUniqueKey = $gVal['deliverySno'] . '-' . $gVal['orderDeliverySno'];
                    $getData[$key]['cnt']['delivery'][$deliveryUniqueKey] += 1 + $addGoodsCnt;
                    $getData[$key]['cnt']['goods']['all'] += 1 + $addGoodsCnt;
                    $getData[$key]['cnt']['goods']['goods'] += 1;
                    $getData[$key]['cnt']['goods']['addGoods'] += $addGoodsCnt;

                    if((int)$gVal['handleSno'] > 0){
                        $handleSnoArr[] = $gVal['handleSno'];
                    }
                }

                if ($printMode == 'particular') {
                    $getData[$key]['useMileage'] = $particularUseMileage;
                    $getData[$key]['useDeposit'] = $particularUseDeposit;
                }

                // 총 부가세율 및 금액
                $goodsVatRate = gd_tax_rate($totalGoodsPrice, $totalPriceSupply);
                $getData[$key]['goodsTaxRate'] = $goodsVatRate;

                // 할인금액 부가세
                // 배송에 관한 할인이 있을 경우 배송비를 포함하여 부가세 조정
                if($getData[$key]['totalMemberDeliveryDcPrice'] > 0 || $getData[$key]['totalCouponDeliveryDcPrice'] > 0){
                    if((int)$getData[$key]['totalSalePrice'] === (int)$getData[$key]['totalMemberDeliveryDcPrice']){
                        // 할인금액이 회원배송비 무료의 값만 있을 경우 배송비의 공급가액/세액 으로 부가세 조정 (상품들은 면세, 배송비는 과세일때)
                        $tmpSaleRate = gd_tax_rate($getData[$key]['totalDeliveryCharge'],$getData[$key]['deliveryVat']['supply']);
                        $saleVatPrice = gd_tax_all($getData[$key]['totalSalePrice'], $tmpSaleRate);
                    }
                    else {
                        $tmpTotalSalesTargetPrice = $totalGoodsPrice+$getData[$key]['totalDeliveryCharge'];
                        $tmpSaleTaxRate = gd_tax_rate($tmpTotalSalesTargetPrice,($totalPriceSupply+$getData[$key]['deliveryVat']['supply']));
                        $saleVatPrice = gd_tax_all($getData[$key]['totalSalePrice'], $tmpSaleTaxRate);
                    }
                }
                else {
                    $saleVatPrice = gd_tax_all($getData[$key]['totalSalePrice'], $getData[$key]['goodsTaxRate']);
                }
                $getData[$key]['saleVat']['supply'] = $saleVatPrice['supply'];
                $getData[$key]['saleVat']['tax'] = $saleVatPrice['tax'];

                //합계 구하기
                $goodsVat = $totalGoodsPrice - $totalPriceSupply;

                //거래명세서 설정에 따라 합계금액 조정
                if ($printMode === 'particular') {
                    $addSupplyPrice = 0;
                    $addVatPrice = 0;
                    $orderPrintConfig = gd_policy('order.print');

                    if(Request::getSubdomainDirectory() !== 'admin' && $orderPrintConfig['orderPrintSameDisplay'] !== 'y'){
                        $getData[$key]['supplyPrice'] = $totalPriceSupply+$getData[$key]['deliveryVat']['supply']-$saleVatPrice['supply'];
                        $getData[$key]['taxPrice'] = $goodsVat+$getData[$key]['deliveryVat']['tax']-$saleVatPrice['tax'];
                    }
                    else {
                        //배송비 포함여부
                        if($orderPrintConfig['orderPrintAmountDelivery'] !== 'n'){
                            $addSupplyPrice += $getData[$key]['deliveryVat']['supply'];
                            $addVatPrice += $getData[$key]['deliveryVat']['tax'];
                        }
                        //할인액 포함여부
                        if($orderPrintConfig['orderPrintAmountDiscount'] !== 'n'){
                            $addSupplyPrice -= $getData[$key]['saleVat']['supply'];
                            $addVatPrice -= $getData[$key]['saleVat']['tax'];
                        }

                        //마일리지 포함여부
                        if($orderPrintConfig['orderPrintAmountMileage'] === 'y'){
                            $orderPrintMileage = gd_tax_all($getData[$key]['useMileage'], $getData[$key]['goodsTaxRate']);
                            $addSupplyPrice -= $orderPrintMileage['supply'];
                            $addVatPrice -= $orderPrintMileage['tax'];
                        }
                        //예치금 포함여부
                        if($orderPrintConfig['orderPrintAmountDeposit'] === 'y'){
                            $orderPrintDeposit = gd_tax_all($getData[$key]['useDeposit'], $getData[$key]['goodsTaxRate']);
                            $addSupplyPrice -= $orderPrintDeposit['supply'];
                            $addVatPrice -= $orderPrintDeposit['tax'];
                        }

                        $getData[$key]['supplyPrice'] = $totalPriceSupply+$addSupplyPrice;
                        $getData[$key]['taxPrice'] = $goodsVat+$addVatPrice;
                    }

                    //합계금액
                    $getData[$key]['totalAmount'] = $getData[$key]['supplyPrice'] + $getData[$key]['taxPrice'];
                }
                else {
                    $getData[$key]['supplyPrice'] = $totalPriceSupply+$getData[$key]['deliveryVat']['supply']-$saleVatPrice['supply'];
                    $getData[$key]['taxPrice'] = $goodsVat+$getData[$key]['deliveryVat']['tax']-$saleVatPrice['tax'];
                }
                unset($orderGoods);

                //주문내역서 외에는 본사상품을 포함한 정보가 노출된다.
                if (Manager::isProvider() && ($printMode === 'report' || $printMode === 'customerReport')) {
                    $getData[$key] = $this->getProviderTotalPrice($getData[$key]);
                }
            }
        }

        return $getData;
    }

    /**
     * 관리자 주문 상세정보 (영수증용 - 현금영수증 및 세금계산서)
     *
     * @param string $orderNo 주문 번호
     *
     * @return array 주문 상세정보
     * @throws Exception
     */
    public function getOrderViewForReceipt($orderNo)
    {
        // 주문번호 체크
        if (Validator::required($orderNo, true) === false) {
            throw new Exception(__('주문번호은(는) 필수 항목 입니다.'));
        }

        // 주문 기본 정보
        $getData = $this->getOrderData($orderNo);

        // 주문 데이타 체크
        if (empty($getData) === true) {
            throw new Exception(sprintf(__('[%s] 주문 정보가 존재하지 않습니다.'), $orderNo));
        }

        // 현재 주문 상태코드 (한자리)
        $getData['statusMode'] = substr($getData['orderStatus'], 0, 1);

        // 주문 상태가 가능한지를 체크
        if (in_array($getData['statusMode'], $this->statusReceiptPossible) === false) {
            throw new Exception(__('해당 주문단계에서는 신청을 하실 수 없습니다.'));
        }

        // 영수증 관련 금액 계산
        $getData['totalSupplyPrice'] = $getData['taxSupplyPrice']; // 공급가
        $getData['totalVatPrice'] = $getData['taxVatPrice']; // 부가세
        $getData['totalFreePrice'] = $getData['taxFreePrice']; // 면세

        return $getData;
    }

    /**
     * 주문상세 페이지에서 정보 수정 + 클래임 접수
     * 단, 주문상품의 주문상태 일괄변경은 별도의 액션으로 처리
     *
     * @param array $arrData 저장할 정보의 배열
     *
     * @throws Exception
     */
    public function updateOrder($arrData)
    {
        $orderData = $this->getOrderData($arrData['orderNo']);
        // 클래임 정보 수정
        $claimGoodsData = [];
        if (empty($arrData['claim']['handleSno']) === false) {
            foreach ($arrData['claim']['handleSno'] as $key => $val) {
                $claimGoodsData['sno'][] = $val;
                $claimGoodsData['handleReason'][] = $arrData['claim']['handleReason'][$val];
                $claimGoodsData['handleDetailReason'][] = $arrData['claim']['handleDetailReason'][$val];
                $claimGoodsData['refundMethod'][] = $arrData['claim']['refundMethod'][$val];
                $claimGoodsData['refundBankName'][] = $arrData['claim']['refundBankName'][$val];
                $claimGoodsData['refundAccountNumber'][] = \Encryptor::encrypt($arrData['claim']['refundAccountNumber'][$val]);
                $claimGoodsData['refundDepositor'][] = $arrData['claim']['refundDepositor'][$val];
            }

            // 공통 키값
            $arrDataKey = ['orderNo' => $arrData['orderNo']];

            // 주문 상품 정보 (수량 및 송장번호) 수정
            if (empty($claimGoodsData) === false && $orderData['orderChannelFl'] != 'naverpay' ) {
                $compareField = array_keys($claimGoodsData);
                $getHandles = $this->getOrderHandle($arrData['orderNo'], $claimGoodsData['sno']);
                $compareGoods = $this->db->get_compare_array_data($getHandles, gd_isset($claimGoodsData), false, $compareField);
                $this->db->set_compare_process(DB_ORDER_HANDLE, $claimGoodsData, $arrDataKey, $compareGoods, $compareField);
            }
            unset($arrDataKey, $handleData, $getHandles, $compareGoods);
        }

        // 클래임 접수 처리
        if (isset($arrData['bundle']['methodType']) === true) {
            // 운영자 기능권한 처리 (주문 상태 권한) - 관리자페이지에서만
            $thisCallController = \App::getInstance('ControllerNameResolver')->getControllerRootDirectory();
            if ($thisCallController == 'admin' && Session::get('manager.functionAuthState') == 'check' && Session::get('manager.functionAuth.orderState') != 'y') {
                throw new Exception(__('권한이 없습니다. 권한은 대표운영자에게 문의하시기 바랍니다.'));
            }
            $bundleData = [];
            $methodType = $arrData['bundle']['methodType'];
            $handleData = $arrData[$methodType];
            $handleMode = substr($methodType, 0, 1);// 주문상태 prefix
            if ($orderData['orderChannelFl'] == 'naverpay' && $handleMode == 'r') { //네이버페이인경우 환불 접수 시 바로 완료
                $changeStatus = 'r3';
            }
            else {
                $changeStatus = $handleMode . '1'; // 주문 상태 설정
            }

            // 취소 처리시 사용자가 설정한 코드가 부여
            if ($methodType == 'cancel') {
                $changeStatus = $arrData['cancel']['orderStatus'];
            }

            // 체크된 것과 현재상태에 따른 처리 가능한 주문 상품 상태 배열 처리
            foreach ($this->statusStandardCode as $cKey => $cVal) {
                if (in_array($handleMode, $cVal)) {
                    $checkCode[] = $cKey;
                    continue;
                }
            }

            // 처리 가능한 상태 배열이 있는경우 체크된 상품별 상태에 따라 처리할 데이타 추출
            if (isset($checkCode) && empty($arrData[$methodType]['statusCheck']) === false) {
                foreach ($arrData[$methodType]['statusCheck'] as $key => $val) {
                    $chkStatus = $arrData[$methodType]['statusMode'][$key];//현 상품의 주문상태

                    // 현재 상태와 동일하지 않은 경우 처리할 수 있도록 걸러냄
                    if (in_array(substr($chkStatus, 0, 1), $checkCode) && $chkStatus != $changeStatus) {
                        $bundleData['sno'][] = $val;
                        $bundleData['orderStatus'][] = $chkStatus;
                    }
                }
            }

            // 각 상태별 처리 및 취소 테이블 등록
            if (!empty($bundleData)) {
                // 상태 변경 처리
                $funcName = 'statusChangeCode' . strtoupper($handleMode); // 처리할 함수명
                $bundleData['changeStatus'] = $changeStatus;
                $bundleData['reason'] = $arrData[$methodType]['handleReason'] . STR_DIVISION . $arrData[$methodType]['detailReason'];//'주문상세에서'

                /**
                 * 주문상세에서 클레임 처리 (환불,반품)
                 * 네이버페이는 api로 처리한후 후처리는 스킵 / 중앙서버로 주문동기화처리
                 */
                if ($orderData['orderChannelFl'] == 'naverpay') {
                    $naverPay = new NaverPay();
                    $naverpayApi = new NaverPayAPI();

                    if ($methodType == 'refund' || $methodType == 'back') {
                        $arrData[$methodType]['handleReasonCode'] = $arrData[$methodType]['handleReason'];
                        $arrData[$methodType]['handleReason'] = $naverPay->getClaimReasonCode($arrData[$methodType]['handleReason']);
                    }
                    for ($i = count($bundleData['sno'])-1; $i >= 0 ; $i--) {
                        if($arrData[$methodType]['goodsType'][$bundleData['sno'][$i]] == 'goods') {
                            $addGoodsList = $this->getChildAddGoods($arrData['orderNo'],$bundleData['sno'][$i],['orderStatus'=>['p1','g1','g2','d1','d2']]);
                            foreach($addGoodsList as $val) {
                                if(in_array($val['sno'],$bundleData['sno']) === false){
                                    throw new Exception(__('추가상품부터 환불/반품/교환 하시기 바랍니다.'));
                                    break;
                                }
                            }
                        }
                    }

                    //추가상품을 위한 순서변경 >추가상품이 있으면 추가상품부터 취소 후 본상품 취소
                    for ($i = count($bundleData['sno'])-1; $i >= 0 ; $i--) {
                        if ($bundleData['orderStatus'][$i] == $bundleData['changeStatus']) {
                            continue;
                        }

                        $result = $naverpayApi->changeStatus($arrData['orderNo'], $bundleData['sno'][$i], $bundleData['changeStatus'], $arrData);
                        if ($result['error']) {
                            throw new Exception($result['error']);
                        } else {
                            echo 'ok';
                        }
                    }

                    return true;
                }

                // 주문상태 변경에 따른 콜백 함수 처리
                $this->$funcName($arrData['orderNo'], $bundleData, false);

                // 환불/반품/교환 접수 처리
                if ($methodType != 'cancel') {
                    $beforeStatus = $bundleData['orderStatus'][0];
                    $handleData['handler'] = 'admin';

                    foreach ($bundleData['sno'] as $orderGoodsSno) {
                        $newOrderGoodsData = $this->setHandleAccept($arrData['orderNo'], [$orderGoodsSno], $handleMode, $handleData, $beforeStatus);

                        // 주문상품이 새로 생성됐을시, 클레임신청 정보 수정
                        if (is_array($newOrderGoodsData) === true && empty($newOrderGoodsData['userHandleGoodsNo']) === false) {
                            $bundleData['userHandleGoodsNo'] = $newOrderGoodsData['userHandleGoodsNo'];
                            $bundleData['orderGoodsNo'] = $orderGoodsSno;
                            $this->updateUserHandle($bundleData);
                            unset($newOrderGoodsData);
                        }
                    }
                }
            }
        }

        // 체크된 상품별 송장 처리 데이터 처리
        // 2017-07-25 yjwee 입금, 상품, 배송 상태에 속하지 않은 경우 송장번호 등록되지 않게 수정
        $orderGoodsData = [];
        if (empty($arrData['bundle']['statusCheck']) === false) {
            foreach ($arrData['bundle']['statusCheck'] as $key => $val) {
                if (in_array(substr($arrData['bundle']['statusMode'][$val], 0, 1), explode(',', 'p,g,d'))) {
                    $orderGoodsData['sno'][] = $val;
                    $orderGoodsData['invoiceCompanySno'][] = $arrData['bundle']['goods']['invoiceCompanySno'][$val];
                    $orderGoodsData['invoiceNo'][] = StringUtils::xssClean($arrData['bundle']['goods']['invoiceNo'][$val]);
                    $orderGoodsData['invoiceDt'][] = date('Y-m-d H:i:s');
                }
            }
        }

        // 공통 키값
        $arrDataKey = ['orderNo' => $arrData['orderNo']];

        // 주문 상품 정보 (수량 및 송장번호) 수정
        if (empty($orderGoodsData) === false) {
            $compareField = array_keys($orderGoodsData);
            $getGoods = $this->getOrderGoods($arrData['orderNo'], $orderGoodsData['sno']);
            $compareGoods = $this->db->get_compare_array_data($getGoods, gd_isset($orderGoodsData), false, $compareField);
            $this->db->set_compare_process(DB_ORDER_GOODS, gd_isset($orderGoodsData), $arrDataKey, $compareGoods, $compareField);
        }

        // 주문 정보 (수취인) 수정
        if (isset($arrData['info']['receiverPhone']) && is_array($arrData['info']['receiverPhone']) === true) {
            $arrData['info']['receiverPhone'] = implode('-', $arrData['info']['receiverPhone']);
        } elseif (isset($arrData['info']['receiverPhone']) && is_string($arrData['info']['receiverPhone']) === true) {
            $arrData['info']['receiverPhone'] = str_replace("-", "", $arrData['info']['receiverPhone']);
            $arrData['info']['receiverPhone'] = StringUtils::numberToPhone($arrData['info']['receiverPhone']);
        }
        if (isset($arrData['info']['receiverCellPhone']) && is_array($arrData['info']['receiverCellPhone']) === true) {
            $arrData['info']['receiverCellPhone'] = implode('-', $arrData['info']['receiverCellPhone']);
        } elseif (isset($arrData['info']['receiverCellPhone']) && is_string($arrData['info']['receiverCellPhone']) === true) {
            $arrData['info']['receiverCellPhone'] = str_replace("-", "", $arrData['info']['receiverCellPhone']);
            $arrData['info']['receiverCellPhone'] = StringUtils::numberToPhone($arrData['info']['receiverCellPhone']);
        }

        // 해외배송 국가코드를 텍스트로 전환
        $arrData['info']['receiverCountry'] = $this->getCountryName($arrData['info']['receiverCountryCode']);

        // 해외전화번호 숫자 변환해서 해당 필드 추가 처리
        $arrData['info']['receiverPhonePrefix'] = $this->getCountryCallPrefix($arrData['info']['receiverPhonePrefixCode']);
        $arrData['info']['receiverCellPhonePrefix'] = $this->getCountryCallPrefix($arrData['info']['receiverCellPhonePrefixCode']);

        $compareField = array_keys($arrData['info']);
        $infoData = $this->db->get_compare_data_change($arrData['info']);
        $getInfo = $this->getOrderInfo($arrData['orderNo']);
        $compareInfo = $this->db->get_compare_array_data($getInfo, gd_isset($infoData), false, $compareField);
        $this->db->set_compare_process(DB_ORDER_INFO, $infoData, $arrDataKey, $compareInfo, $compareField);

        // 주문 정보 (입금 은행) 및 관리자 메모 수정 arrData[order]가 없는 경우 primaryKey 중복값이라는 오류 발생
        if (empty($arrData['order']) === false) {
            $compareField = array_keys($arrData['order']);
            // 운영자 기능권한 처리 (입금은행 변경 권한)
            if (Session::get('manager.functionAuthState') == 'check' && Session::get('manager.functionAuth.orderBank') != 'y') {
                $arrExclude[] = 'bankAccount';
                $arrExclude[] = 'bankSender';
            }
            $arrBind = $this->db->get_binding(DBTableField::tableOrder(), $arrData['order'], 'update', $compareField, $arrExclude);
            $this->db->bind_param_push($arrBind['bind'], 's', $arrData['orderNo']);
            $this->db->set_update_db(DB_ORDER, $arrBind['param'], 'orderNo = ?', $arrBind['bind']);
            unset($arrBind);
        }

        // 요청사항 및 상담메모 등록 및 수정
        $arrData['consult']['managerNo'] = Session::get('manager.sno');
        if (empty($arrData['consult']['sno']) === false) {
            $compareField = array_keys($arrData['consult']);
            $arrBind = $this->db->get_binding(DBTableField::tableOrderConsult(), $arrData['consult'], 'update', $compareField);
            $this->db->bind_param_push($arrBind['bind'], 's', $arrData['consult']['sno']);
            $this->db->set_update_db(DB_ORDER_CONSULT, $arrBind['param'], 'sno = ?', $arrBind['bind']);
            unset($arrBind);
        } else {
            if (empty($arrData['consult']['requestMemo']) === false || empty($arrData['consult']['consultMemo']) === false) {
                $compareField = array_keys($arrData['consult']);
                $arrBind = $this->db->get_binding(DBTableField::tableOrderConsult(), $arrData['consult'], 'insert', $compareField);
                $this->db->set_insert_db(DB_ORDER_CONSULT, $arrBind['param'], $arrBind['bind'], 'y');
                unset($arrBind);
            }
        }

        // 송장 번호 SMS 전송
        if (isset($arrData['bundle']['statusCheck']) === true && isset($handleMode) === true) {
            if ($handleMode == 'd') {
                $this->sendOrderInfo(Code::INVOICE_CODE, 'sms', $arrData['orderNo']);
            }
        }
    }

    /**
     * 주문상세페이지 - 주문상태, 송장번호 변경
     *
     * @param array $arrData 저장할 정보의 배열
     *
     * @throws Exception
     */
    public function updateOrderStatusDelivery($arrData)
    {
        //송장번호 업데이트
        if($arrData['orderInvoiceUpdateFl'] === 'y'){
            $invoiceDataArr = $arrData;
            // 체크된 상품별 송장 처리 데이터 처리 (입금, 상품, 배송, 교환추가 상태에 속하지 않은 경우 송장번호 등록방지)
            if(count($arrData['statusMode']) > 0){
                foreach ($arrData['statusMode'] as $key => $val) {
                    if (!in_array(substr($key, 0, 1), explode(',', 'p,g,d,z'))) {
                        unset($invoiceDataArr['statusCheck'][$key]);
                    }
                }
            }
            $this->saveDeliveryInvoice($invoiceDataArr);
        }
        //주문상태 업데이트
        if($arrData['orderStatusUpdateFl'] === 'y'){
            $this->requestStatusChange($arrData);
        }
    }

    /**
     * 주문상세페이지 - 주문자정보 수정
     *
     * @param array $arrData 저장할 정보의 배열
     *
     * @throws Exception
     */
    public function updateOrderOrderInfo($arrData)
    {
        $upateArr = [
            'orderName',
            'orderEmail',
            'orderPhonePrefixCode',
            'orderPhonePrefix',
            'orderPhone',
            'orderCellPhonePrefixCode',
            'orderCellPhonePrefix',
            'orderCellPhone',
            'orderZipcode',
            'orderZonecode',
            'orderState',
            'orderCity',
            'orderAddress',
            'orderAddressSub',
        ];

        if (isset($arrData['info']['orderPhone']) && is_string($arrData['info']['orderPhone']) === true) {
            $arrData['info']['orderPhone'] = str_replace("-", "", $arrData['info']['orderPhone']);
            $arrData['info']['orderPhone'] = StringUtils::numberToPhone($arrData['info']['orderPhone']);
        }
        if (isset($arrData['info']['orderCellPhone']) && is_string($arrData['info']['orderCellPhone']) === true) {
            $arrData['info']['orderCellPhone'] = str_replace("-", "", $arrData['info']['orderCellPhone']);
            $arrData['info']['orderCellPhone'] = StringUtils::numberToPhone($arrData['info']['orderCellPhone']);
        }

        // 해외전화번호 숫자 변환해서 해당 필드 추가 처리
        if (empty($arrData['info']['mallSno']) == false && $arrData['info']['mallSno'] > 1) {
            $arrData['info']['orderPhonePrefix'] = $this->getCountryCallPrefix($arrData['info']['orderPhonePrefixCode']);
            $arrData['info']['orderCellPhonePrefix'] = $this->getCountryCallPrefix($arrData['info']['orderCellPhonePrefixCode']);
            $arrData['info']['receiverPhonePrefix'] = $this->getCountryCallPrefix($arrData['info']['receiverPhonePrefixCode']);
            $arrData['info']['receiverCellPhonePrefix'] = $this->getCountryCallPrefix($arrData['info']['receiverCellPhonePrefixCode']);
        }

        $updateData = array_intersect_key($arrData['info'], array_flip($upateArr));
        if(count($updateData) > 0){
            $arrBind = $this->db->get_binding(DBTableField::tableOrderInfo(), $updateData, 'update', $upateArr);
            $this->db->bind_param_push($arrBind['bind'], 's', $arrData['orderNo']);
            $this->db->set_update_db(DB_ORDER_INFO, $arrBind['param'], 'orderNo = ?', $arrBind['bind']);

            $logger = \App::getInstance('logger');
            $logger->channel('order')->info('주문상세페이지에서 주문자정보 수정 ; 처리자 - ' . \Session::get('manager.managerId'));
        }
    }

    /**
     * 주문상세페이지 - 수령자정보 수정
     * 상품준비중 리스트 - 묶음배송 수령자 정보수정
     *
     * @param array $arrData 저장할 정보의 배열
     *
     * @throws Exception
     */
    public function updateOrderReceiverInfo($arrData)
    {
        if(trim($arrData['info']['data']) !== ''){
            $arrData['info'] = array_merge((array)$arrData['info'], (array)json_decode($arrData['info']['data'], true));
        }
        $upateArr = [
            'receiverName',
            'receiverCountryCode',
            'receiverPhonePrefixCode',
            'receiverPhonePrefix',
            'receiverPhone',
            'receiverCellPhonePrefixCode',
            'receiverCellPhonePrefix',
            'receiverCellPhone',
            'receiverZipcode',
            'receiverZonecode',
            'receiverCountry',
            'receiverState',
            'receiverCity',
            'receiverAddress',
            'receiverAddressSub',
            'orderMemo',
            'visitName',
            'visitPhone',
            'visitMemo',
        ];

        $arrExclude = null;

        if (isset($arrData['info']['receiverPhone']) && is_string($arrData['info']['receiverPhone']) === true) {
            $arrData['info']['receiverPhone'] = str_replace("-", "", $arrData['info']['receiverPhone']);
            $arrData['info']['receiverPhone'] = StringUtils::numberToPhone($arrData['info']['receiverPhone']);
        }
        if (isset($arrData['info']['receiverCellPhone']) && is_string($arrData['info']['receiverCellPhone']) === true) {
            $arrData['info']['receiverCellPhone'] = str_replace("-", "", $arrData['info']['receiverCellPhone']);
            $arrData['info']['receiverCellPhone'] = StringUtils::numberToPhone($arrData['info']['receiverCellPhone']);
        }

        // 해외몰 국가, 번호 치환
        if (empty($arrData['info']['mallSno']) == false && $arrData['info']['mallSno'] > 1) {
            // 데이터명 다른 경우
            if (empty($arrData['info']['receiverCountrycode']) === false && empty($arrData['info']['receiverCountryCode'])) {
                $arrData['info']['receiverCountryCode'] = $arrData['info']['receiverCountrycode'];
            }
            // 해외전화번호 숫자 변환해서 해당 필드 추가 처리
            $arrData['info']['orderPhonePrefix'] = $this->getCountryCallPrefix($arrData['info']['orderPhonePrefixCode']);
            $arrData['info']['orderCellPhonePrefix'] = $this->getCountryCallPrefix($arrData['info']['orderCellPhonePrefixCode']);
            $arrData['info']['receiverPhonePrefix'] = $this->getCountryCallPrefix($arrData['info']['receiverPhonePrefixCode']);
            $arrData['info']['receiverCellPhonePrefix'] = $this->getCountryCallPrefix($arrData['info']['receiverCellPhonePrefixCode']);
            // 해외배송 국가코드를 텍스트로 전환
            $arrData['info']['receiverCountry'] = $this->getCountryName($arrData['info']['receiverCountryCode']);
        }

        // 안심번호 통신 오류인 경우
        if (isset($arrData['info']['safeNumberMode'])) {
            if ($arrData['info']['safeNumberMode'] == 'cancel') {
                $orderBasic = gd_policy('order.basic');
                // 안심번호가 없이 해지 및 off 상태인 경우 사용안함으로 상태값만 변경
                if (empty($arrData['info']['receiverSafeNumber'])) {
                    $upateArr[] = 'receiverUseSafeNumberFl';
                    $arrData['info']['receiverUseSafeNumberFl'] = 'n';
                } else if (isset($orderBasic['safeNumberServiceFl']) && $orderBasic['safeNumberServiceFl'] == 'off') {
                    $upateArr[] = 'receiverUseSafeNumberFl';
                    $arrData['info']['receiverUseSafeNumberFl'] = 'c';
                } else {
                    $tmpData['sno'] = $arrData['info']['sno'];
                    $tmpData['phoneNumber'] = str_replace("-", "", $arrData['info']['receiverOriginCellPhone']);
                    $tmpData['safeNumber'] = str_replace("-", "", $arrData['info']['receiverSafeNumber']);
                    $safeNumber = \App::load('Component\\Service\\SafeNumber');
                    $safeNumber->cancelSafeNumber($tmpData);
                }
            } else if ($arrData['info']['safeNumberMode'] == 'except') {
                $arrExclude[] = 'receiverCellPhonePrefixCode';
                $arrExclude[] = 'receiverCellPhonePrefix';
                $arrExclude[] = 'receiverCellPhone';
            }
        }

        $updateData = array_intersect_key($arrData['info'], array_flip($upateArr));
        if(count($updateData) > 0){
            $arrBind = $this->db->get_binding(DBTableField::tableOrderInfo(), $updateData, 'update', $upateArr, $arrExclude);
            $this->db->bind_param_push($arrBind['bind'], 'i', $arrData['info']['sno']);
            $this->db->set_update_db(DB_ORDER_INFO, $arrBind['param'], 'sno = ?', $arrBind['bind']);

            $logger = \App::getInstance('logger');
            if($arrData['mode'] === 'update_receiver_info'){
                $loggerMessage = '묶음배송 팝업페이지에서';
            }
            else if ($arrData['mode'] === 'modifyReceiverInfo'){
                $loggerMessage = '주문상세페이지에서';
            }
            else {}
            $logger->channel('order')->info($loggerMessage .' 수령자정보 수정 [처리자 : ' . \Session::get('manager.managerId') . ']');
        }
        unset($arrBind);
    }

    /**
     * 주문상세페이지 - 요청사항 / 고객상담메모 수정
     *
     * @param array $arrData 저장할 정보의 배열
     *
     * @throws Exception
     */
    public function updateOrderConsultMemo($arrData)
    {
        // 요청사항 및 상담메모 등록 및 수정
        $arrData['consult']['managerNo'] = Session::get('manager.sno');
        if (empty($arrData['consult']['sno']) === false) {
            $compareField = array_keys($arrData['consult']);
            $arrBind = $this->db->get_binding(DBTableField::tableOrderConsult(), $arrData['consult'], 'update', $compareField);
            $this->db->bind_param_push($arrBind['bind'], 's', $arrData['consult']['sno']);
            $this->db->set_update_db(DB_ORDER_CONSULT, $arrBind['param'], 'sno = ?', $arrBind['bind']);
        }
        else {
            if (empty($arrData['consult']['requestMemo']) === false || empty($arrData['consult']['consultMemo']) === false) {
                $compareField = array_keys($arrData['consult']);
                $arrBind = $this->db->get_binding(DBTableField::tableOrderConsult(), $arrData['consult'], 'insert', $compareField);
                $this->db->set_insert_db(DB_ORDER_CONSULT, $arrBind['param'], $arrBind['bind'], 'y');
            }
        }
        unset($arrBind);
    }

    /**
     * 주문상세페이지 - 관리자메모 수정
     *
     * @param array $arrData 저장할 정보의 배열
     *
     * @throws Exception
     */
    public function updateOrderAdminMemo($arrData)
    {
        $updateData = [
            'adminMemo' => $arrData['order']['adminMemo'],
            'orderNo' => $arrData['orderNo'],
        ];
        $this->updateSuperAdminMemo($updateData);
    }

    /**
     * 관리자앱 for 고도몰5 주문 상세에 배송지(수령자)정보/요청사항상담메모 수정
     * 단, 주문상품의 주문상태 일괄변경은 별도의 액션으로 처리
     *
     * @param array $arrData 저장할 정보의 배열
     *
     * @throws Exception
     */
    public function updateOrderMobileapp($arrData)
    {
        // 공통 키값
        $arrDataKey = ['orderNo' => $arrData['orderNo']];

        // 주문 정보 (수취인) 수정
        if (isset($arrData['info']['receiverPhone']) && is_array($arrData['info']['receiverPhone']) === true) {
            $arrData['info']['receiverPhone'] = implode('-', $arrData['info']['receiverPhone']);
        } elseif (isset($arrData['info']['receiverPhone']) && is_string($arrData['info']['receiverPhone']) === true) {
            $arrData['info']['receiverPhone'] = str_replace("-", "", $arrData['info']['receiverPhone']);
            $arrData['info']['receiverPhone'] = StringUtils::numberToPhone($arrData['info']['receiverPhone']);
        }
        if (isset($arrData['info']['receiverCellPhone']) && is_array($arrData['info']['receiverCellPhone']) === true) {
            $arrData['info']['receiverCellPhone'] = implode('-', $arrData['info']['receiverCellPhone']);
        } elseif (isset($arrData['info']['receiverCellPhone']) && is_string($arrData['info']['receiverCellPhone']) === true) {
            $arrData['info']['receiverCellPhone'] = str_replace("-", "", $arrData['info']['receiverCellPhone']);
            $arrData['info']['receiverCellPhone'] = StringUtils::numberToPhone($arrData['info']['receiverCellPhone']);
        }

        $compareField = array_keys($arrData['info']);
        $infoData = $this->db->get_compare_data_change($arrData['info']);
        $getInfo = $this->getOrderInfo($arrData['orderNo']);
        $compareInfo = $this->db->get_compare_array_data($getInfo, gd_isset($infoData), false, $compareField);
        $this->db->set_compare_process(DB_ORDER_INFO, $infoData, $arrDataKey, $compareInfo, $compareField);

        // 요청사항 및 상담메모 등록 및 수정
        $arrData['consult']['managerNo'] = Session::get('manager.sno');
        if (empty($arrData['consult']['sno']) === false) {
            $compareField = array_keys($arrData['consult']);
            $arrBind = $this->db->get_binding(DBTableField::tableOrderConsult(), $arrData['consult'], 'update', $compareField);
            $this->db->bind_param_push($arrBind['bind'], 's', $arrData['consult']['sno']);
            $this->db->set_update_db(DB_ORDER_CONSULT, $arrBind['param'], 'sno = ?', $arrBind['bind']);
            unset($arrBind);
        } else {
            if (empty($arrData['consult']['requestMemo']) === false || empty($arrData['consult']['consultMemo']) === false) {
                $compareField = array_keys($arrData['consult']);
                $arrBind = $this->db->get_binding(DBTableField::tableOrderConsult(), $arrData['consult'], 'insert', $compareField);
                $this->db->set_insert_db(DB_ORDER_CONSULT, $arrBind['param'], $arrBind['bind'], 'y');
                unset($arrBind);
            }
        }
    }

    /**
     * 관리자앱 for 고도몰5 주문 상세에 배송번호 변경
     * 단, 주문상품의 주문상태 일괄변경은 별도의 액션으로 처리
     *
     * @param array $arrData 저장할 정보의 배열
     *
     * @throws Exception
     */
    public function updateOrderMobileappDeliveryNo($arrData)
    {
        // 공통 키값
        $arrDataKey = ['orderNo' => $arrData['orderNo']];

        // 체크된 상품별 송장 처리 데이터 처리
        $orderGoodsData = [];
        foreach ($arrData['bundle']['statusCheck'] as $key => $val) {
            $orderGoodsData['sno'][] = $val;
            $orderGoodsData['invoiceCompanySno'][] = $arrData['bundle']['goods']['invoiceCompanySno'][$val];
            $orderGoodsData['invoiceNo'][] = $arrData['bundle']['goods']['invoiceNo'][$val];
            $orderGoodsData['invoiceDt'][] = date('Y-m-d H:i:s');
        }

        // 주문 상품 정보 (송장번호) 수정
        if (empty($orderGoodsData) === false) {
            $compareField = array_keys($orderGoodsData);
            $getGoods = $this->getOrderGoods($arrData['orderNo'], $orderGoodsData['sno']);
            $compareGoods = $this->db->get_compare_array_data($getGoods, gd_isset($orderGoodsData), false, $compareField);
            $this->db->set_compare_process(DB_ORDER_GOODS, gd_isset($orderGoodsData), $arrDataKey, $compareGoods, $compareField);
        }
    }

    /**
     * 관리자앱 for 고도몰5 주문 상세에 주문상품 상태 일괄 변경 처리
     *
     * @param $arrData
     *
     * @throws Exception
     */
    public function requestStatusChangeMobileapp($arrData)
    {
        // goods 주문상품 필드 갯수 차감을 위해 goods 호출
        $goods = \App::load(\Component\Goods\Goods::class);

        // 운영자 기능권한 처리 (주문 상태 변경 권한) - 관리자페이지에서만
        $thisCallController = \App::getInstance('ControllerNameResolver')->getControllerRootDirectory();
        if ($thisCallController == 'admin' && Session::get('manager.functionAuthState') == 'check' && Session::get('manager.functionAuth.orderState') != 'y') {
            throw new Exception(__('권한이 없습니다. 권한은 대표운영자에게 문의하시기 바랍니다.'));
        }

        // 주문 정보 확인
        if (empty($arrData['statusCheck']) === true) {
            throw new Exception(__('[리스트] 주문 정보가 존재하지 않습니다.'));
        }

        // 체크된 상품의 값을 주문번호와 상품번호로 분리
        foreach ($arrData['statusCheck'] as $statusMode => $sVal) {
            foreach ($sVal as $val) {
                $tmpArr = explode(INT_DIVISION, $val);
                if (isset($tmpArr[1]) === true) {
                    $statusCode[$statusMode][$tmpArr[0]][] = $tmpArr[1];
                } else {
                    $statusCode[$statusMode][$tmpArr[0]][] = null;
                }
            }
        }

        // 유효성 검사 후 처리
        if (empty($statusCode) === false) {
            $allCount = 0;
            $checkModifiedCount = 0;
            foreach ($statusCode as $statusMode => $orderNos) {
                foreach ($orderNos as $orderNo => $arrGoodsNo) {
                    // 주문상품별이 아닌 주문별로 일괄 처리가 필요한 경우 (ex. 주문접수, 주문취소, 결제실패)
                    $arrGoodsNo = ArrayUtils::removeEmpty($arrGoodsNo);
                    if (empty($arrGoodsNo) === true) {
                        $arrGoodsNo = null;
                    }

                    // 주문 상태, 실패일경우 현재 상태 수정 처리, 및 취소에서 주문 상태 처리시에 현재 상태 수정 처리 가능하게
                    if ($statusMode == 'p' || $statusMode == 'o' || $statusMode == 'f' || ($statusMode == 'c' && substr($arrData['changeStatus'], 0, 1) == 'o')) {
                        $bundleFl = true;
                    } else {
                        $bundleFl = false;
                    }

                    $orderData = $this->getOrderData($orderNo);
                    // 주문 상태 변경 처리 (반품/교환/환불의 경우 정상 주문으로 복구시 별도 처리)
                    if($orderData['orderChannelFl'] == 'naverpay'){
                        $this->updateStatusPreprocess($orderNo, $this->getOrderGoods($orderNo, $arrGoodsNo, null, ['orderStatus']), $statusMode, $arrData['changeStatus'], '리스트에서', $bundleFl);
                    }
                    else {
                        if ($statusMode == 'r' || $statusMode == 'e' || $statusMode == 'b') {
                            // setHandleRollback용 데이터 구성 후 던짐
                            $getData['orderNo'] = $orderNo;
                            $getData['changeStatus'] = $arrData['changeStatus'];
                            foreach ($arrGoodsNo as $orderGoodsNo) {
                                $goodsData = $this->getOrderGoodsData($orderNo, $orderGoodsNo, null, null, null, false, null, $statusMode);
                                $getData['bundle']['statusCheck'][$orderGoodsNo] = $goodsData['sno'];
                                $getData['bundle']['beforeStatus'][$orderGoodsNo] = $goodsData['beforeStatus'];
                                $getData['bundle']['orderStatus'][$orderGoodsNo] = $goodsData['orderStatus'];
                                $getData['bundle']['handleSno'][$orderGoodsNo] = $goodsData['handleSno'];

                                // 교환 추가 입금처리 시 상품 테이블 주문상품 갯수 갱신 처리 es_goods.orderGoodsCnt
                                if($statusMode == 'o' && $goodsData['paymentDt'] == "0000-00-00 00:00:00") {
                                    $goods->setOrderGoodsCount($goodsData['sno'], false, $goodsData['goodsNo'], $goodsData['goodsCnt']);
                                }
                                // 교환 취소 완료 시 상품 테이블 주문상품 갯수 갱신 처리 es_goods.orderGoodsCnt
                                if($getData['changeStatus'] == 'e5' && $goodsData['handleDt'] == null && $goodsData['handleCompleteFl'] =='n' ) {
                                    // 핸들 데이터 로드
                                    $orderHandleGroupOrderGoods = $this->getOrderExchangeGoodsCntSet($orderNo, $goodsData['sno'], $getData['handleGroupCd']);
                                    if($goodsData['goodsNo'] != $orderHandleGroupOrderGoods[0]['goodsNo']) { // 타상품
                                        $goods->setOrderGoodsCount($goodsData['sno'], true, $goodsData['goodsNo'], $goodsData['goodsCnt']);
                                    }
                                }
                                // 상품 교환 완료 시 상품 테이블 주문상품 갯수 갱신 처리 es_goods.orderGoodsCnt
                                if($getData['changeStatus'] == 'z5' && $goodsData['handleDt'] == null && $goodsData['handleCompleteFl'] =='n' ) {
                                    // 핸들 데이터 로드
                                    $orderHandleGroupOrderGoods = $this->getOrderExchangeGoodsCntSet($orderNo, $goodsData['sno'], $getData['handleGroupCd']);
                                    if($goodsData['goodsNo'] != $orderHandleGroupOrderGoods[0]['goodsNo']) { // 타상품
                                        $goods->setOrderGoodsCount($goodsData['sno'], false, $goodsData['goodsNo'], $goodsData['goodsCnt']);
                                    }
                                }
                            }

                            $this->setHandleRollback($getData);
                        } else {
                            // 주문상세보기 및 리스트에서 주문상태 일괄 변경시 환불/반품/교환으로 변경안되도록 처리 필요
                            if (!in_array(substr($arrData['changeStatus'], 0, 1), ['r', 'e', 'b', 'c'])) {
                                $this->updateStatusPreprocess($orderNo, $this->getOrderGoods($orderNo, $arrGoodsNo, null, ['orderStatus']), $statusMode, $arrData['changeStatus'], '리스트에서', $bundleFl);
                            } else {
                                $checkModifiedCount++;
                            }
                        }
                    }

                    $allCount++;
                }
            }

            if ($checkModifiedCount > 0) {
                //@todo 변경할 수 없는 상태에 대한 처리
                //throw new Exception(sprintf(__('총 %d개 중 %d를 처리 완료하였습니다.'), $allCount, $checkModifiedCount));
            }
        }
    }

    /**
     * 상품준비중 리스트에서 송장번호 일괄 업데이트 처리 & 주문상세에서 송장번호 일괄변경 업데이트 처리
     *
     * @author Jong-tae Ahn <qnibus@godo.co.kr>
     *
     * @param array $arrData 주문리스트 요청 데이터
     */
    public function saveDeliveryInvoice($arrData)
    {
        // 체크된 상품별 송장 처리 데이터 처리
        $orderGoodsData = [];
        foreach ($arrData['statusCheck'] as $statusMode => $data) {
            foreach ($data as $orderDatas) {
                $orderData = explode(INT_DIVISION, $orderDatas);
                $orderNo = $orderData[0];
                $orderGoodsNo = $orderData[1];
                $deliveryCompany = $this->delivery->getDeliveryCompany($arrData['invoiceCompanySno'][$statusMode][$orderGoodsNo])[0];

                $orderGoodsData['sno'][] = $orderGoodsNo;
                $orderGoodsData['invoiceCompanySno'][] = $arrData['invoiceCompanySno'][$statusMode][$orderGoodsNo];
                $orderGoodsData['invoiceNo'][] = StringUtils::xssClean($arrData['invoiceNo'][$statusMode][$orderGoodsNo]);
                $orderGoodsData['invoiceDt'][] = date('Y-m-d H:i:s');
                $orderGoodsData['deliveryMethodFl'][] = gd_isset($deliveryCompany['companyKey'], 'delivery');

                // 주문 상태 변경 처리
                $arrDataKey = ['orderNo' => $orderNo];

                // 주문 상품 정보 (수량 및 송장번호) 수정
                if (empty($orderGoodsData) === false) {
                    $compareField = array_keys($orderGoodsData);
                    $getGoods = $this->getOrderGoods($orderNo, $orderGoodsNo);
                    $compareGoods = $this->db->get_compare_array_data($getGoods, gd_isset($orderGoodsData), false, $compareField);
                    $this->db->set_compare_process(DB_ORDER_GOODS, gd_isset($orderGoodsData), $arrDataKey, $compareGoods, $compareField);
                }
            }
        }
    }

    /**
     * 상품준비중 리스트에서 주문번호별 송장번호 일괄 업데이트 처리
     *
     * @author by
     *
     * @param array $arrData 주문리스트 요청 데이터
     */
    public function saveDeliveryOrderInvoice($arrData)
    {
        $scmNo = \Session::get('manager.scmNo');
        $updateData = [
            'searchView' => $arrData['searchView'],
            'fromPageMode' => $arrData['fromPageMode'],
        ];

        $tmpOrderNoArr = $orderNoArr = [];
        if(count($arrData['statusCheck']) > 0){
            foreach($arrData['statusCheck'] as $statusKey => $valueArr){
                if(count($valueArr) > 0){
                    foreach($valueArr as $key => $value){
                        if($arrData['invoiceIndividualUnsetFl'][$value] && $arrData['invoiceIndividualUnsetFl'][$value] === $value){
                            $tmpOrderNoArr[] = $value;
                        }
                    }
                }
            }
        }

        $orderNoArr = array_values(array_unique(array_filter($tmpOrderNoArr)));
        if(count($orderNoArr) > 0){
            foreach($orderNoArr as $oKey => $orderNo){
                $orderGoodsData = [];
                $orderGoodsData = $this->getOrderGoodsData($orderNo);
                foreach ($orderGoodsData as $sKey => $dataVal) {
                    if (Manager::isProvider() && $scmNo != Session::get('manager.scmNo')) {
                        continue;
                    }
                    foreach ($dataVal as $goodsData) {
                        $statusMode = substr($goodsData['orderStatus'], 0, 1);
                        if($statusMode !== 'g'){
                            continue;
                        }
                        if((int)$scmNo !== (int)DEFAULT_CODE_SCMNO && (int)$scmNo !== (int)$goodsData['scmNo']){
                            continue;
                        }
                        $updateData['statusCheck']['g'][] = $orderNo . INT_DIVISION . $goodsData['sno'];
                        $updateData['invoiceCompanySno']['g'][$goodsData['sno']] = $arrData['invoiceCompanySno'][$orderNo];
                        $updateData['invoiceNo']['g'][$goodsData['sno']] = $arrData['invoiceNo'][$orderNo];
                    }
                }
            }
            if(count($updateData['statusCheck']['g']) > 0){
                $this->saveDeliveryInvoice($updateData);
            }
        }
    }

    /**
     * 사용자 교환/반품/환불신청을 승인 or 거절을 처리
     * 승인시 자동으로 교환/반품/환불 접수 상태로 주문상태를 변경하며,
     * 거절시 사유를 사용자가 볼 수 있도록 해당 테이블을 업데이트 한다.
     *
     * @param array $arrData 리퀘스트 데이터
     * @param boolean $userHandleFl 사용자 반품/교환/환불 요청
     *
     * @return boolean 오류시 false
     * @throws Exception
     * @author Jong-tae Ahn <qnibus@godo.co.kr>
     */
    public function approveUserHandle($arrData, $userHandleFl, $autoProceess = false)
    {
        // 주문 상품 sno 체크
        if (empty($arrData['statusCheck']) === true && is_array($arrData['statusCheck']) === false) {
            return false;
        }

        $partCompleteData = [];
        foreach ($arrData['statusCheck'] as $val) {
            $bundleData = $newOrderGoodsData = [];

            $tmp = explode(INT_DIVISION, $val);
            $orderNo = $tmp[0];
            $orderGoodsSno = $tmp[1];
            $bundleData['sno'] = $tmp[2];
            $bundleData['goodsCnt'][$orderGoodsSno] = $tmp[3];
            $bundleData['goodsOriginCnt'][$orderGoodsSno] = $tmp[4];
            $bundleData['userHandleFl'] = $userHandleFl;
            $bundleData['adminHandleReason'] = $arrData['adminHandleReason'];

            if ($userHandleFl == 'y') {
                if(count($partCompleteData[$orderNo][$orderGoodsSno]['goodsCnt']) > 0){
                    // 승인처리시 이미 처리된 주문상품건이 있다면 '같은 주문, 같은 주문상품의 수량별 동시 승인시' 의 경우로 처리.
                    $bundleData['goodsOriginCnt'][$orderGoodsSno] -= array_sum($partCompleteData[$orderNo][$orderGoodsSno]['goodsCnt']);
                }
            }

            $returnOrderGoodsSno = $orderGoodsSno;

            // 주문상품의 환불 정보 가져오기
            $getData = $this->getOrderGoodsData($orderNo, $orderGoodsSno, null, $bundleData['sno'], null, false);

            // 승인처리
            if ($userHandleFl == 'y') {
                // 해당 사용자가 요청한 접수의 주문상태로 변경 처리
                $this->updateStatusPreprocess($orderNo, $this->getOrderGoods($orderNo, $orderGoodsSno), substr($getData['orderStatus'], 0, 1), $getData['userHandleMode'] . '1', __('일괄'), true, null, null, $autoProceess);

                $bundleData['handleReason'] = $getData['userHandleReason'];
                $bundleData['handleDetailReason'] = $getData['userHandleDetailReason'];
                $bundleData['refundBankName'] = $getData['userRefundBankName'];
                $bundleData['refundAccountNumber'] = $getData['userRefundAccountNumber'];
                $bundleData['refundDepositor'] = $getData['userRefundDepositor'];
                $newOrderGoodsData = $this->setHandleAccept($orderNo, [$orderGoodsSno], $getData['userHandleMode'], $bundleData, $getData['orderStatus']);

                // $newOrderGoodsData 데이터가 존재한다는 것은 부분처리가 되어 새로운 주문상품이 생겼다는 의미
                if(is_array($newOrderGoodsData) === true && count($newOrderGoodsData) > 0){
                    // 처리된 주문상품건들을 기억하여 '같은 주문, 같은 주문상품의 수량별 동시 승인시' 를 처리한다.
                    $partCompleteData[$orderNo][$orderGoodsSno]['goodsCnt'][] = (int)$bundleData['goodsCnt'][$orderGoodsSno];

                    $bundleData['userHandleGoodsNo'] = $newOrderGoodsData['userHandleGoodsNo'];
                    $this->updateUserHandle($bundleData);
                    unset($bundleData['userHandleGoodsNo']);

                    $returnOrderGoodsSno = $bundleData['userHandleGoodsNo'];
                }
            }

            // 사용자 신청 테이블 업데이트
            $this->updateUserHandle($bundleData);

            $returnSno[] = $returnOrderGoodsSno;
        }

        if ($this->paycoConfig['paycoFl'] == 'y') {
            // 페이코쇼핑 결제데이터 전달
            $payco = \App::load('\\Component\\Payment\\Payco\\Payco');
            $payco->paycoShoppingRequest($orderNo);
        }

        return $returnSno;
    }

    /**
     * 사용자 반품/교환/환불신청내 관리자 메모 내용을 업데이트
     * 관리자 메모 이외 다른 것도 업데이트 가능하다.
     *
     * @param array $bundleData sno|adminHandleReason
     *
     * @return mixed boolean|null
     */
    public function updateUserHandle(array $bundleData)
    {
        if (empty($bundleData) === true || ($bundleData['adminHandleReason'] != '' && $bundleData['sno'] == '')) {
            return false;
        }
        if (empty($bundleData['userRefundAccountNumber']) === false) {
            $bundleData['userRefundAccountNumber'] = \Encryptor::encrypt($bundleData['userRefundAccountNumber']);
        }

        // 사용자 신청 테이블 업데이트
        if (empty($bundleData['userHandleGoodsNo']) === true) {
            // 처리자
            if($bundleData['adminHandleReason'] == '자동 환불'){
                $bundleData['managerNo'] = -1;
            }else{
                $bundleData['managerNo'] = Session::get('manager.sno');
            }

            $compareField = array_keys($bundleData);
            $arrBind = $this->db->get_binding(DBTableField::tableOrderUserHandle(), $bundleData, 'update', $compareField);
            $arrWhere = 'sno = ?';
            $this->db->bind_param_push($arrBind['bind'], 's', $bundleData['sno']);
        } else {
            // 개별 수량으로 클레임 처리시
            $tmpData['userHandleGoodsNo'] = $bundleData['userHandleGoodsNo'];
            $compareField = array_keys($tmpData);
            $arrBind = $this->db->get_binding(DBTableField::tableOrderUserHandle(), $tmpData, 'update', $compareField);
            $arrWhere = 'sno = ?';  // 기존 조건은 처리할 상품번호 와 사용자 신청에 따른 승인여부가 요청일 때에만 수정되었기때문에 처리상품번호 변경이 안되는 이슈가 있음. 확인필요
            $this->db->bind_param_push($arrBind['bind'], 'i', $bundleData['sno']);
        }
        $return = $this->db->set_update_db(DB_ORDER_USER_HANDLE, $arrBind['param'], $arrWhere, $arrBind['bind']);
        unset($arrBind);

        return $return;
    }

    /**
     * 주문상품 테이블의 OrderCd 코드 생성
     * 환불/반품/교환시 주문상품 테이블을 새롭게 insert 할 때 사용된다.
     *
     * @param string $orderNo 주문 번호
     *
     * @return integer orderCd 코드 번호
     */
    public function getMaxOrderCd($orderNo)
    {
        // 쿼리 조건
        $this->db->strField = 'max(orderCd) as max';
        $this->db->strWhere = 'orderNo = ?';
        $this->db->bind_param_push($arrBind, 's', $orderNo);

        // 쿼리 생성
        $query = $this->db->query_complete();
        $strSQL = 'SELECT ' . array_shift($query) . ' FROM ' . DB_ORDER_GOODS . ' ' . implode(' ', $query);
        $getData = $this->db->query_fetch($strSQL, $arrBind, false);
        unset($arrBind);

        // 환불 코드 리턴
        if (empty($getData['max']) === false) {
            $maxCd = $getData['max'];

            return ($maxCd + 1);
        } else {
            return 1;
        }
    }

    /**
     * 주문상품 테이블의 handleGroupCd 코드 생성
     * 환불/반품/교환시 주문상품 테이블을 새롭게 insert 할 때 사용된다.
     *
     * @param string $orderNo 주문 번호
     *
     * @return integer orderCd 코드 번호
     */
    public function getMaxOrderGroupCd($orderNo)
    {
        // 쿼리 조건
        $this->db->strField = 'max(orderGroupCd) as max';
        $this->db->strWhere = 'orderNo = ?';
        $this->db->bind_param_push($arrBind, 's', $orderNo);

        // 쿼리 생성
        $query = $this->db->query_complete();
        $strSQL = 'SELECT ' . array_shift($query) . ' FROM ' . DB_ORDER_GOODS . ' ' . implode(' ', $query);
        $getData = $this->db->query_fetch($strSQL, $arrBind, false);
        unset($arrBind);

        // 환불 코드 리턴
        if (empty($getData['max']) === false) {
            $maxCd = $getData['max'];

            return ($maxCd + 1);
        } else {
            return 1;
        }
    }

    /**
     * 주문상품의 취소시 수량에 따른 부분취소의 경우 사용됨
     * 특정 주문상품을 수량으로 취소하는 경우 발생하는 금액을 안분 시켜서 재계산 (복합과세를 포함한 모든 금액 재계산)
     * 기존 주문상품 row는 교환/반품/환불접수로 update 되고 남은 수량은 insert 처리할 수 있도록 데이터를 분리
     * 단, 추가상품의 경우 현재 수량 취소를 못함
     *
     * @param integer $orderNo
     * @param integer $orderGoodsSno
     * @param integer $goodsCnt
     *
     * @return array
     */
    protected function divideHandleGoodsCntData($orderNo, $orderGoodsSno, $goodsCnt)
    {
        // 반환데이터 초기화
        $setData = $addData = [];

        // 절사 내용
        $truncPolicy = Globals::get('gTrunc.goods');

        // 원래 주문상품 데이터
        $originData = $this->getOrderGoodsData($orderNo, $orderGoodsSno, null, null, null, false);

        // 수량 비율
        $goodsCntRate = ($goodsCnt / $originData['goodsCnt']);

        // 수량으로 안분하는 작업
        $realSettlePrice = NumberUtils::getNumberFigure(($originData['taxSupplyGoodsPrice'] + $originData['taxVatGoodsPrice'] + $originData['taxFreeGoodsPrice']) * $goodsCntRate, $truncPolicy['unitPrecision'], 'round');
        $taxPrice = NumberUtils::taxAll($realSettlePrice, $originData['goodsTaxInfo'][1], $originData['goodsTaxInfo'][0]);
        if ($originData['goodsTaxInfo'][0] == 't') {
            $taxSupplyGoodsPrice = $taxPrice['supply'];
            $taxVatGoodsPrice = $taxPrice['tax'];
        } else {
            $taxFreeGoodsPrice = $taxPrice['supply'];
        }
        $divisionUseDeposit = NumberUtils::getNumberFigure($originData['divisionUseDeposit'] * $goodsCntRate, $truncPolicy['unitPrecision'], 'round');
        $divisionUseMileage = NumberUtils::getNumberFigure($originData['divisionUseMileage'] * $goodsCntRate, $truncPolicy['unitPrecision'], 'round');
        $divisionGoodsDeliveryUseDeposit = NumberUtils::getNumberFigure($originData['divisionGoodsDeliveryUseDeposit'] * $goodsCntRate, $truncPolicy['unitPrecision'], 'round');
        $divisionGoodsDeliveryUseMileage = NumberUtils::getNumberFigure($originData['divisionGoodsDeliveryUseMileage'] * $goodsCntRate, $truncPolicy['unitPrecision'], 'round');
        $divisionCouponOrderDcPrice = NumberUtils::getNumberFigure($originData['divisionCouponOrderDcPrice'] * $goodsCntRate, $truncPolicy['unitPrecision'], 'round');
        $divisionCouponOrderMileage = NumberUtils::getNumberFigure($originData['divisionCouponOrderMileage'] * $goodsCntRate, $truncPolicy['unitPrecision'], 'round');
        $goodsDcPrice = NumberUtils::getNumberFigure($originData['goodsDcPrice'] * $goodsCntRate, $truncPolicy['unitPrecision'], 'round');
        $memberDcPrice = NumberUtils::getNumberFigure($originData['memberDcPrice'] * $goodsCntRate, $truncPolicy['unitPrecision'], 'round');
        $memberOverlapDcPrice = NumberUtils::getNumberFigure($originData['memberOverlapDcPrice'] * $goodsCntRate, $truncPolicy['unitPrecision'], 'round');
        $couponGoodsDcPrice = NumberUtils::getNumberFigure($originData['couponGoodsDcPrice'] * $goodsCntRate, $truncPolicy['unitPrecision'], 'round');

        // 마이앱 사용에 따른 분기 처리
        if ($this->useMyapp) {
            $myappDcPrice = NumberUtils::getNumberFigure($originData['myappDcPrice'] * $goodsCntRate, $truncPolicy['unitPrecision'], 'round');
        }

        $goodsMileage = NumberUtils::getNumberFigure($originData['goodsMileage'] * $goodsCntRate, $truncPolicy['unitPrecision'], 'round');
        $memberMileage = NumberUtils::getNumberFigure($originData['memberMileage'] * $goodsCntRate, $truncPolicy['unitPrecision'], 'round');
        $couponGoodsMileage = NumberUtils::getNumberFigure($originData['couponGoodsMileage'] * $goodsCntRate, $truncPolicy['unitPrecision'], 'round');
        $enuri = NumberUtils::getNumberFigure($originData['enuri'] * $goodsCntRate, $truncPolicy['unitPrecision'], 'round');

        // 정산시 수량별로 분할 된 경우 처리를 위한 그룹 코드 생성
        if ($originData['orderGroupCd'] == 0) {
            $orderGroupCd = $this->getMaxOrderGroupCd($orderNo);
        } else {
            $orderGroupCd = $originData['orderGroupCd'];
        }

        // update 데이터
        $setData['orderGroupCd'] = $orderGroupCd;
        $setData['goodsCnt'] = $originData['goodsCnt'] - $goodsCnt;
        $setData['taxSupplyGoodsPrice'] = $originData['taxSupplyGoodsPrice'] - $taxSupplyGoodsPrice;
        $setData['taxVatGoodsPrice'] = $originData['taxVatGoodsPrice'] - $taxVatGoodsPrice;
        $setData['taxFreeGoodsPrice'] = $originData['taxFreeGoodsPrice'] - $taxFreeGoodsPrice;
        $setData['realTaxSupplyGoodsPrice'] = $originData['taxSupplyGoodsPrice'] - $taxSupplyGoodsPrice;
        $setData['realTaxVatGoodsPrice'] = $originData['taxVatGoodsPrice'] - $taxVatGoodsPrice;
        $setData['realTaxFreeGoodsPrice'] = $originData['taxFreeGoodsPrice'] - $taxFreeGoodsPrice;
        $setData['divisionUseDeposit'] = $originData['divisionUseDeposit'] - $divisionUseDeposit;
        $setData['divisionUseMileage'] = $originData['divisionUseMileage'] - $divisionUseMileage;
        $setData['divisionGoodsDeliveryUseDeposit'] = $originData['divisionGoodsDeliveryUseDeposit'] - $divisionGoodsDeliveryUseDeposit;
        $setData['divisionGoodsDeliveryUseMileage'] = $originData['divisionGoodsDeliveryUseMileage'] - $divisionGoodsDeliveryUseMileage;
        $setData['divisionCouponOrderDcPrice'] = $originData['divisionCouponOrderDcPrice'] - $divisionCouponOrderDcPrice;
        $setData['divisionCouponOrderMileage'] = $originData['divisionCouponOrderMileage'] - $divisionCouponOrderMileage;
        $setData['goodsDcPrice'] = $originData['goodsDcPrice'] - $goodsDcPrice;
        $setData['memberDcPrice'] = $originData['memberDcPrice'] - $memberDcPrice;
        $setData['memberOverlapDcPrice'] = $originData['memberOverlapDcPrice'] - $memberOverlapDcPrice;
        $setData['couponGoodsDcPrice'] = $originData['couponGoodsDcPrice'] - $couponGoodsDcPrice;
        // 마이앱 사용에 따른 분기 처리
        if ($this->useMyapp) {
            $setData['myappDcPrice'] = $originData['myappDcPrice'] - $myappDcPrice;
        }

        $setData['goodsMileage'] = $originData['goodsMileage'] - $goodsMileage;
        $setData['memberMileage'] = $originData['memberMileage'] - $memberMileage;
        $setData['couponGoodsMileage'] = $originData['couponGoodsMileage'] - $couponGoodsMileage;
        $setData['enuri'] = $originData['enuri'] - $enuri;
        $setData['userHandleSno'] = $originData['userHandleSno'];

        // insert 데이터
        $arrField = DBTableField::setTableField('tableOrderGoods', null, null);
        $strSQL = 'SELECT sno, ' . implode(', ', $arrField) . ' FROM ' . DB_ORDER_GOODS . ' WHERE sno = ? ORDER BY sno ASC';
        $arrBind = [
            's',
            $orderGoodsSno,
        ];
        $addData = gd_htmlspecialchars_stripslashes($this->db->query_fetch($strSQL, $arrBind, false));
        $addData['orderCd'] = $this->getMaxOrderCd($orderNo);
        $addData['orderGroupCd'] = $orderGroupCd;
        $addData['regDt'] = $originData['regDt'];
        $addData['goodsCnt'] = $goodsCnt;
        $addData['taxSupplyGoodsPrice'] = $taxSupplyGoodsPrice;
        $addData['taxVatGoodsPrice'] = $taxVatGoodsPrice;
        $addData['taxFreeGoodsPrice'] = $taxFreeGoodsPrice;
        $addData['realTaxSupplyGoodsPrice'] = $taxSupplyGoodsPrice;
        $addData['realTaxVatGoodsPrice'] = $taxVatGoodsPrice;
        $addData['realTaxFreeGoodsPrice'] = $taxFreeGoodsPrice;
        $addData['divisionUseDeposit'] = $divisionUseDeposit;
        $addData['divisionUseMileage'] = $divisionUseMileage;
        $addData['divisionGoodsDeliveryUseDeposit'] = $divisionGoodsDeliveryUseDeposit;
        $addData['divisionGoodsDeliveryUseMileage'] = $divisionGoodsDeliveryUseMileage;
        $addData['divisionCouponOrderDcPrice'] = $divisionCouponOrderDcPrice;
        $addData['divisionCouponOrderMileage'] = $divisionCouponOrderMileage;
        $addData['goodsDcPrice'] = $goodsDcPrice;
        $addData['memberDcPrice'] = $memberDcPrice;
        $addData['memberOverlapDcPrice'] = $memberOverlapDcPrice;
        $addData['couponGoodsDcPrice'] = $couponGoodsDcPrice;

        // 마이앱 사용에 따른 분기 처리
        if ($this->useMyapp) {
            $addData['myappDcPrice'] = $myappDcPrice;
        }

        $addData['goodsMileage'] = $goodsMileage;
        $addData['memberMileage'] = $memberMileage;
        $addData['couponGoodsMileage'] = $couponGoodsMileage;
        $addData['enuri'] = $enuri;
        $addData['statisticsOrderFl'] = $originData['statisticsOrderFl'];
        $addData['statisticsGoodsFl'] = $originData['statisticsGoodsFl'];

        // 전체 취소인 경우
        if ($originData['goodsCnt'] == $goodsCnt) {
            $setData = $addData;
            $setData['goodsCnt'] = $originData['goodsCnt'];// 전체 수량의 $addData에서 변경될 수 있어 오버라이드 처리
            unset($setData['orderCd']);
            unset($addData);
        }

        return [
            'update' => $setData,
            'insert' => $addData,
        ];
    }

    /**
     * 환불/반품/교환 접수 처리 (주문상세 및 프론트)
     * 주문상품(orderGoods) 테이블과 취소처리(orderHandle) 테이블의 관계는 기존의 n:1 관계에서 1:1 관계로 변경되어졌다.
     * 이유는 주문상품 하나가 객체로써 처리가 되어져야 하기 때문이다.
     * 로직상 주문상태 변경이 먼저 이뤄지고 해당 메서드를 타게 되어있어서 반드시 수량 취소인 경우 주문상태도 업데이트 되어져야 한다.
     *
     * @param string  $orderNo        주문 번호
     * @param array   $orderGoodsSnos 주문 상품 번호
     * @param string  $handleMode     처리모드 (r b e)
     * @param array   $bundleData     insert 할 환불 정보
     * @param string  $beforeStatus   이전 주문 상태
     */
    public function setHandleAccept($orderNo, $orderGoodsSnos, $handleMode, $bundleData, $beforeStatus)
    {
        // 주문 상품 sno 체크
        if (empty($orderGoodsSnos) === true && is_array($orderGoodsSnos) === false) {
            return;
        }

        // 환불 테이블 저장
        $bundleData['orderNo'] = $orderNo;
        $bundleData['beforeStatus'] = $beforeStatus;
        $bundleData['handleMode'] = $handleMode;
        $bundleData['handleCompleteFl'] = 'n';
        if (empty($bundleData['refundAccountNumber']) === false) {
            $bundleData['refundAccountNumber'] = \Encryptor::encrypt($bundleData['refundAccountNumber']);
        }

        foreach ($orderGoodsSnos as $orderGoodsSno) {
            // handle 테이블에 입력후 insertId 반환
            $compareField = array_keys($bundleData);
            $arrBind = $this->db->get_binding(DBTableField::tableOrderHandle(), $bundleData, 'insert', $compareField);
            $this->db->set_insert_db(DB_ORDER_HANDLE, $arrBind['param'], $arrBind['bind'], 'y');
            $handleSno = $this->db->insert_id();
            unset($arrBind);

            // 전체수량 취소/반품/교환/환불여부에 따른 처리 (하나의 주문상품에서 수량만 분리되는 경우)
            if (isset($bundleData['goodsOriginCnt']) && $bundleData['goodsOriginCnt'][$orderGoodsSno] > $bundleData['goodsCnt'][$orderGoodsSno]) {
                // 수량이 0보다 작은경우 처리하지 않는다.
                if (isset($bundleData['goodsCnt']) && $bundleData['goodsCnt'][$orderGoodsSno] > 0) {
                    // 주문상품 update용 데이터
                    $fieldData = $this->divideHandleGoodsCntData($orderNo, $orderGoodsSno, $bundleData['goodsCnt'][$orderGoodsSno]);

                    $tmpOrderGoodsData = '';

                    // 동일 상품 수량별 처리할 경우
                    if (empty($bundleData['sno']) === false && $bundleData['sno'] != $fieldData['update']['userHandleSno']) {
//                        $fieldData['update']['userHandleSno'] = $bundleData['sno'];
                    } else {
                        $updateUserHandleSno = 0;
                        $claimList = $this->getOrderListForClaim($orderNo);

                        if (is_array($claimList[0])) {
                            foreach ($claimList as $list) {
                                if ($list['userHandleNo'] !== $bundleData['sno'] && $list['userHandleGoodsNo'] === $orderGoodsSno) {
                                    $updateUserHandleSno = $list['userHandleNo'];
                                }
                            }
                        }

                        // 관리자에서 클레임 접수 할 경우
                        if (empty($bundleData['handler']) === false && $bundleData['handler'] === 'admin') {
                            $fieldData['update']['userHandleSno'] = 0;
                        }

                        $fieldData['update']['userHandleSno'] = $updateUserHandleSno;
                    }

                    $setData = $fieldData['update'];
                    $setData['orderStatus'] = $beforeStatus;    // 변경된 상품주문상태를 되돌린다.

                    // 취소/반품/교환/환불하고 남은 수량을 수정
                    $compareField = array_keys($setData);
                    $arrBind = $this->db->get_binding(DBTableField::tableOrderGoods(), $setData, 'update', $compareField);
                    $arrWhere = 'orderNo = ? AND sno = ?';
                    $this->db->bind_param_push($arrBind['bind'], 's', $orderNo);
                    $this->db->bind_param_push($arrBind['bind'], 'i', $orderGoodsSno);
                    $this->db->set_update_db(DB_ORDER_GOODS, $arrBind['param'], $arrWhere, $arrBind['bind']);
                    unset($arrBind, $setData);

                    // 취소/반품/교환/환불 상품 새로운 상품주문으로 추가
                    if (isset($fieldData['insert'])) {
                        $addData = $fieldData['insert'];
                        $addData['handleSno'] = $handleSno;
                        $addData['userHandleSno'] = $bundleData['sno'];

                        // 주문상품 DB insert 처리
                        $compareField = array_keys($addData);
                        $arrBind = $this->db->get_binding(DBTableField::tableOrderGoods(), $addData, 'insert', $compareField);
                        $this->db->set_insert_db_query(DB_ORDER_GOODS, $arrBind['param'], $arrBind['bind'], 'y', $addData['regDt']);

                        $tmpOrderGoodsData['userHandleGoodsNo'] = $this->db->insert_id();
                        $tmpOrderGoodsData['userHandleGoodsCnt'] = $fieldData['insert']['goodsCnt'];
                        unset($arrBind, $addData);

                        // 취소/반품/교환/환불 수량 분할로 인한 신규상품 추가되기 전 로그가 쌓이기때문에 추가한 쿼리
                        $this->db->set_update_db_query(DB_LOG_ORDER . ' AS lo, (SELECT sno FROM ' . DB_LOG_ORDER . ' WHERE orderNo = ? AND goodsSno = ? ORDER BY sno DESC LIMIT 1) AS lo2', ['lo.goodsSno = ?']
                            , 'lo.sno = lo2.sno', ['sii', $orderNo, $orderGoodsSno, $tmpOrderGoodsData['userHandleGoodsNo']]);
                    }

                    // 하나의 주문상품이 분할되기 때문에 주문상품의 총 카운트를 업데이트해야 함
                    $arrBind = [];
                    $this->db->bind_param_push($arrBind, 's', $orderNo);
                    $this->db->set_update_db(DB_ORDER, 'orderGoodsCnt = orderGoodsCnt + 1', 'orderNo = ?', $arrBind);
                    unset($arrBind, $fieldData);

                    return $tmpOrderGoodsData;
                } else {
                    throw new Exception(__('클레임 접수시 신청 재고수량은 0이 될 수 없습니다.'));
                }
            } else {
                // 주문 상품테이블에 handleSno 번호를 업데이트 한다.
                $setData['handleSno'] = $handleSno;
                $compareField = array_keys($setData);
                $arrBind = $this->db->get_binding(DBTableField::tableOrderGoods(), $setData, 'update', $compareField);
                $arrWhere = 'orderNo = ? AND sno = ' . $orderGoodsSno;
                $this->db->bind_param_push($arrBind['bind'], 's', $orderNo);
                $this->db->set_update_db(DB_ORDER_GOODS, $arrBind['param'], $arrWhere, $arrBind['bind']);
                unset($arrBind, $setData);
            }
        }
    }

    /**
     * 환불/반품/교환 단계 복구 처리 (환불상세내 상품리스트)
     *
     * @param array $arrData 주문서 복구 정보
     *
     * @throws Exception 주문상태 변경 가능여부에 따른 처리
     */
    public function setHandleRollback($arrData)
    {
        $reOrderCalculation = App::load(\Component\Order\ReOrderCalculation::class);

        // 운영자 기능권한 처리 (주문 상태 권한) - 관리자페이지에서만
        $thisCallController = \App::getInstance('ControllerNameResolver')->getControllerRootDirectory();
        if ($thisCallController == 'admin' && Session::get('manager.functionAuthState') == 'check' && Session::get('manager.functionAuth.orderState') != 'y') {
            throw new Exception(__('권한이 없습니다. 권한은 대표운영자에게 문의하시기 바랍니다.'));
        }
        if (isset($arrData['orderNo']) === false || isset($arrData['changeStatus']) === false) {
            return;
        }

        // 업데이트 가능 여부 체크 및 변수 초기화
        $orderNo = $arrData['orderNo'];
        $changeStatus = $arrData['changeStatus'];
        $isUpdate = false;

        // 핸들로 오기 전의 주문상태로 돌릴 수 있는지에 대한 체크 (롤백용)
        foreach ($arrData['bundle']['statusCheck'] as $orderGoodsSno) {

            // 변경 가능여부 초기화
            $canRollback = true;

            // 환불접수 : 결제완료, 상품준비중 상태에서 환불접수된 경우
            if ($arrData['bundle']['orderStatus'][$orderGoodsSno] == 'r1' && in_array($arrData['bundle']['beforeStatus'][$orderGoodsSno], ['p1', 'g1']) && !in_array($changeStatus, ['p1', 'g1'])) {
                $canRollback = false;
            }

            // 환불접수 : 반품접수에서 환불접수된 경우만 가능
            if ($arrData['bundle']['orderStatus'][$orderGoodsSno] == 'r1' && in_array($arrData['bundle']['beforeStatus'][$orderGoodsSno], ['b1']) && !in_array($changeStatus, ['b1', 'd2'])) {
                $canRollback = false;
            }

            if (
                $canRollback == true ||
                substr($arrData['bundle']['orderStatus'][$orderGoodsSno], 0, 1) == substr($changeStatus, 0, 1) // 같은 반품/교환/환불상태로 변경시의 처리
            ) {
                $validatedData['statusCheck'][] = $orderGoodsSno;
                $validatedData['handleSno'][] = $arrData['bundle']['handleSno'][$orderGoodsSno];
                $validatedData['orderStatus'][] = $arrData['bundle']['orderStatus'][$orderGoodsSno];
                $isUpdate = true;
            }
        }

        if ($isUpdate == false) {
            throw new Exception($this->getOrderStatusAdmin($changeStatus) . __('로 변경할 수 없는 상품이 선택되었습니다.'));
        }

        // 업데이트 처리
        foreach ($validatedData['statusCheck'] as $key => $orderGoodsSno) {
            $handleSno = $validatedData['handleSno'][$key];
            $orderStatus = $validatedData['orderStatus'][$key];
            $handleData = [];

            // 새로운 접수모드인 경우
            $isAutoRefund = false;

            // 취소처리 번호가 없는 경우 다음 차례로 넘어감
            if (empty($handleSno) !== false || $handleSno < 1) {
                continue;
            }

            // 주문 상태 변경 처리
            $this->updateStatusPreprocess($orderNo, $this->getOrderGoods($orderNo, null, $handleSno, ['orderStatus']), substr($orderStatus, 0, 1), $changeStatus, __('[이전 단계 복구]'));

            // 반품/교환/환불과 동일한 주문상태로 변경하지 않는 경우 예외 처리 (ex. 환불->반품, 반품->교환 등)
            if (in_array($orderStatus, $this->statusHandleListExclude) && !in_array($changeStatus, $this->statusHandleListExclude)) {

                // 주문상태로 변경되는 경우 주문상품 테이블에서 handleSno를 음수 처리
                $orderGoodsData['handleSno'] = -($handleSno); // 엑셀 데이타를 UFT-8 로 변경
                $arrBind = $this->db->get_binding(DBTableField::tableOrderGoods(), $orderGoodsData, 'update', array_keys($orderGoodsData));
                $this->db->bind_param_push($arrBind['bind'], 's', $orderNo);
                $this->db->bind_param_push($arrBind['bind'], 'i', $orderGoodsSno);
                $this->db->set_update_db(DB_ORDER_GOODS, $arrBind['param'], 'orderNo=? AND sno=?', $arrBind['bind']);

                // 주문환불 레코드를 삭제상태인 d 로 처리
                $handleData['handleCompleteFl'] = 'd';

            } elseif (in_array($orderStatus, $this->statusHandleListExclude) && in_array($changeStatus, $this->statusHandleListExclude)) {

                // modDt를 업데이트 하기 위해 무조건 업데이트 처리
                $handleData['handleCompleteFl'] = 'n';

                // 반품/교환/환불내에서 변경되는 경우의 처리시 동일상태 조건이 아닌 경우 접수로 이동시 새롭게 handle을 등록해야 함
                if (substr($orderStatus, 0, 1) == substr($changeStatus, 0, 1)) {
                    // 완료로 변경되는 경우 (환불완료인 경우 refundComplete() 메서드 사용)
                    if (in_array($changeStatus, ['e5', 'z5', 'b4'])) {
                        // 반품/교환/환불 레코드를 완료상태인 y 로 처리
                        $handleData['handleCompleteFl'] = 'y';
                        $handleData['handleDt'] = date('Y-m-d H:i:s');

                        // 자동환불접수로 넘기기 위한 처리
                        $orderData = $this->getOrderView($orderNo);
                        if ($changeStatus == 'b4' && $orderData['orderChannelFl'] != 'naverpay') {
                            $isAutoRefund = true;
                        }
                        //교환취소 상품의 교환완료 처리 일 경우 handle 금액데이터에 삽입된 값을 구한다. (통계처리 위함)
                        if($changeStatus === 'e5'){
                            $mergeHandleData = [];
                            $mergeHandleData = $reOrderCalculation->getExchangeHandlePrice($orderNo, $orderGoodsSno);
                            $handleData = array_merge((array)$handleData, (array)$mergeHandleData);
                        }
                        unset($orderData);
                    }
                } else {
                    // 접수 단계로 변경되는 경우
                    if (substr($changeStatus, 1, 1) == '1') {
                        // 주문환불 레코드를 삭제상태인 d 로 처리
                        $handleData['handleCompleteFl'] = 'd';

                        // 기존 반품/교환/환불 레코드에서 데이터 추출
                        $getData = $this->getOrderGoodsData($orderNo, null, $handleSno, null, null, false);

                        // 새로운 환불/교환/반품 접수 처리
                        $bundleData['handleReason'] = $getData['handleReason'];
                        $bundleData['handleDetailReason'] = $getData['handleDetailReason'];
                        $bundleData['refundMethod'] = $getData['refundMethod'];
                        $bundleData['refundBankName'] = $getData['refundBankName'];
                        $bundleData['refundAccountNumber'] = $getData['refundAccountNumber'];
                        $bundleData['refundDepositor'] = $getData['refundDepositor'];
                        $this->setHandleAccept($orderNo, [$orderGoodsSno], substr($changeStatus, 0, 1), $bundleData, $orderStatus);
                    } else {
                        // 동일 반품/교환/환불상태가 아닌 경우의 주문상태 변경은 반드시 접수로 들어가야 한다 따라서 이곳에 코드가 들어오면 무조건 오류 출력해야 함
                        throw new Exception(__('반품/교환/환불접수 처리 후 변경해주세요.'));
                    }
                }
            }

            // handleData가 있는 경우 handler 테이블 업데이트
            if (empty($handleData) === false) {
                $compareField = array_keys($handleData);
                $arrBind = $this->db->get_binding(DBTableField::tableOrderHandle(), $handleData, 'update', $compareField);
                $arrWhere = 'orderNo = ? AND sno = ?';
                $this->db->bind_param_push($arrBind['bind'], 's', $orderNo);
                $this->db->bind_param_push($arrBind['bind'], 'i', $handleSno);
                $this->db->set_update_db(DB_ORDER_HANDLE, $arrBind['param'], $arrWhere, $arrBind['bind']);
                unset($arrBind, $handleData);
            }

            // 반품회수 완료시 자동으로 환불접수 단계 이동 처리
            if ($isAutoRefund) {
                $this->updateStatusPreprocess($orderNo, $this->getOrderGoods($orderNo, null, $handleSno, ['orderStatus']), 'b', 'r1', __('반품회수완료로 인한 자동'));

                // 기존 반품/교환/환불 레코드에서 데이터 추출
                $getData = $this->getOrderGoodsData($orderNo, null, $handleSno, null, null, false);

                // 새로운 환불/교환/반품 접수 처리
                $bundleData['handleReason'] = $getData['handleReason'];
                $bundleData['handleDetailReason'] = $getData['handleDetailReason'];
                $bundleData['handleDetailReasonShowFl'] = $getData['handleDetailReasonShowFl'];
                $bundleData['refundMethod'] = $getData['refundMethod'];
                $bundleData['refundBankName'] = $getData['refundBankName'];
                $bundleData['refundAccountNumber'] = $getData['refundAccountNumber'];
                $bundleData['refundDepositor'] = $getData['refundDepositor'];

                $this->setHandleAccept($orderNo, [$orderGoodsSno], 'r', $bundleData, $orderStatus);
            }
        }
    }

    /**
     * 관리자 반품/교환/환불신청내 관리자 메모 내용을 업데이트
     * 관리자 메모 이외 다른 것도 업데이트 가능하다.
     *
     * @param array $bundleData sno|adminHandleReason
     *
     * @return mixed boolean|null
     * @throws Exception
     */
    public function updateHandle(array $bundleData)
    {
        if (empty($bundleData) === true || $bundleData['sno'] == '' || !is_numeric($bundleData['sno'])) {
            throw new Exception(__('정상적으로 처리할 수 없습니다. 관리자에게 문의하세요.'));
        }

        // 처리자
        $bundleData['managerNo'] = Session::get('manager.sno');
        if (empty($bundleData['refundAccountNumber']) === false) {
            $bundleData['refundAccountNumber'] = \Encryptor::encrypt($bundleData['refundAccountNumber']);
        }

        // 주문처리 테이블 업데이트
        $compareField = array_keys($bundleData);
        $arrBind = $this->db->get_binding(DBTableField::tableOrderHandle(), $bundleData, 'update', $compareField);
        $arrWhere = 'sno = ?';
        $this->db->bind_param_push($arrBind['bind'], 's', $bundleData['sno']);
        $return = $this->db->set_update_db(DB_ORDER_HANDLE, $arrBind['param'], $arrWhere, $arrBind['bind']);
        unset($arrBind);


        return $return;
    }

    /**
     * 주문상품 테이블의 handleGroupCd 코드 생성
     * 환불/반품/교환시 주문상품 테이블을 새롭게 insert 할 때 사용된다.
     *
     * @param string $orderNo 주문 번호
     *
     * @return integer orderCd 코드 번호
     */
    public function getMaxRefundGroupCd($orderNo)
    {
        // 쿼리 조건
        $this->db->strField = 'max(refundGroupCd) as max';
        $this->db->strWhere = 'orderNo = ?';
        $this->db->bind_param_push($arrBind, 's', $orderNo);

        // 쿼리 생성
        $query = $this->db->query_complete();
        $strSQL = 'SELECT ' . array_shift($query) . ' FROM ' . DB_ORDER_HANDLE . ' ' . implode(' ', $query);
        $getData = $this->db->query_fetch($strSQL, $arrBind, false);
        if (gd_str_length($getData['refundAccountNumber']) > 50) {
            $getData['refundAccountNumber'] = \Encryptor::decrypt($getData['refundAccountNumber']);
        }
        unset($arrBind);

        // 환불 코드 리턴
        if (empty($getData['max']) === false) {
            $maxCd = $getData['max'];

            return ($maxCd + 1);
        } else {
            return 1;
        }
    }

    /**
     * 환불 완료 처리
     * 트랜젝션 처리
     *
     * @param array $getData 주문서 정보
     *
     * @throws Except
     * @throws Exception
     */
    public function setRefundComplete($getData, $autoProcess)
    {
        // 운영자 기능권한 처리 (주문 상태 권한) - 관리자페이지에서만
        $thisCallController = \App::getInstance('ControllerNameResolver')->getControllerRootDirectory();
        if ($thisCallController == 'admin' && Session::get('manager.functionAuthState') == 'check' && Session::get('manager.functionAuth.orderState') != 'y') {
            throw new Exception(__('권한이 없습니다. 권한은 대표운영자에게 문의하시기 바랍니다.'));
        }
        if (isset($getData['orderNo']) === false) {
            throw new Exception(__('정상적인 접근이 아닙니다.'));
        }

        // 넘어온 값 계산이 맞는지 체크
        if ($getData['check']['totalSettlePrice'] - $getData['check']['totalDeliveryCharge'] - $getData['check']['totalRefundCharge'] != $getData['check']['totalRefundPrice']) {
            throw new Exception(__('환불금액이 정확하지 않습니다. 관리자에게 문의하세요.'));
        }

        // 핸들 번호가 없는 경우 오류
        if ($getData['handleSno'] == 0) {
            throw new Exception(__('환불/교환/반품번호 형식이 맞지 않습니다. 관리자에게 문의하세요.'));
        }

        // 환불 상세보기에서의 검색 조건 설정
        $handleSno = null;
        $excludeStatus = null;
        if ($getData['isAll'] != 1 && $getData['handleSno'] != 0) {
            $handleSno = $getData['handleSno'];
        }

        // 주문상품 리스트 및 주문정보 가져오기 (환불 상세보기 동일하며 해당 주문번호의 전체상품에서 계산해야 하기때문에 전체를 가져옴)
        $orderInfo = $this->getOrderView($getData['orderNo'], null, $handleSno, 'r', ['r3']);

        // 해외 상점의 경우 handleSno를 null로 처리해 무조건 부분취소 아닌 전체로 처리되게 한다.
        if ($orderInfo['mallSno'] > DEFAULT_MALL_NUMBER && $handleSno !== null) {
            throw new Exception(__('해외상점 취소/교환/반품/환불은 전체 처리만 가능합니다.'));
        }

        // $getData의 필요한 정보만 세팅
        unset($getData['mode']);

        // 절사 내용
        $truncPolicy = Globals::get('gTrunc.goods');

        // 환불그룹 코드
        $refundGroupCd = $this->getMaxRefundGroupCd($getData['orderNo']);

        // 환불되지 않은 잔여 배송비
        $realRefundDeliveryCharge = [];

        // 배송비조건별 환불처리 후 남은 배송비금액 구하기 (총 배송비 금액에서 환불된 배송비금액 빼기)
        foreach ($orderInfo['goods'] as $sKey => $sVal) {
            foreach ($sVal as $dKey => $dVal) {
                // 상품의 첫번째 value (배송비조건에 물려있는 상품이 여러개인 경우 사용자 페이지에서 첫번째 상품에 배송비가 물려있기 때문)
                $gVal = $dVal[0];

                // 배송비조건의 배송비
                $orderDeliveryCharge = floatval($gVal['deliveryCharge'] - $gVal['divisionDeliveryCharge'] - $gVal['divisionDeliveryUseDeposit'] - $gVal['divisionDeliveryUseMileage']);

                // 사용자가 입력한 배송비
                $userDeliveryCharge = floatval($getData['refund'][$gVal['handleSno']]['refundDeliveryCharge']);

                // 환불완료 배송비
                $refundDeliveryCharge = 0;

                // 동일 배송비조건의 환불완료된 데이터 추출해 해당 배송비조건의 환불완료된 배송비를 구함
                $tmpField[] = DBTableField::setTableField('tableOrderGoods', ['orderStatus'], null, 'og');
                $tmpField[] = DBTableField::setTableField('tableOrderHandle', null, null, 'oh');
                $tmpKey = array_keys($tmpField);
                $arrField = [];
                foreach ($tmpKey as $key) {
                    $arrField = array_merge($arrField, $tmpField[$key]);
                }
                unset($tmpField, $tmpKey);

                $strSQL = 'SELECT oh.sno, ' . implode(', ', $arrField) . ' FROM ' . DB_ORDER_GOODS . ' og LEFT JOIN ' . DB_ORDER_HANDLE . ' oh ON og.handleSno = oh.sno  WHERE og.orderDeliverySno = ? AND og.orderStatus = \'r3\' AND oh.handleCompleteFl = \'y\' ORDER BY og.sno ASC';
                $arrBind = [
                    's',
                    $gVal['orderDeliverySno'],
                ];
                $tmpData = gd_htmlspecialchars_stripslashes($this->db->query_fetch($strSQL, $arrBind, true));
                if (empty($tmpData) === false) {
                    foreach ($tmpData as $gVal) {
                        if ($gVal['orderStatus'] == 'r3') {
                            $refundDeliveryCharge += $gVal['refundDeliveryCharge'];
                        }
                    }
                }
                unset($tmpData);

                // 해외배송의 경우 공급사가 다르더라도 배송비조건 일련번호가 같기 때문에 0원으로 오버라이딩 되는 증상이 있다.
                // 해외상점 주문시 최초 배송비가 들어간 이후에는 처리되지 않도록 강제로 예외처리 (해당 처리 안하면 배송비가 해외상점인 경우 무조건 0원으로 나옴)
                if ($orderInfo['mallSno'] == DEFAULT_MALL_NUMBER || ($orderInfo['mallSno'] > DEFAULT_MALL_NUMBER && (empty($realRefundDeliveryCharge['rest'][$dKey]) === true || $realRefundDeliveryCharge['rest'][$dKey] == 0))) {
                    // 실제 남은 배송비
                    $realRefundDeliveryCharge['rest'][$dKey] = $orderDeliveryCharge - $refundDeliveryCharge;

                    // 사용자가 입력한 배송비 초기화
                    $realRefundDeliveryCharge['user'][$dKey] = $userDeliveryCharge;
                }

                // 배송비 validate
                if ($realRefundDeliveryCharge['rest'][$dKey] < $realRefundDeliveryCharge['user'][$dKey]) {
                    throw new Exception(__('결제하신 배송비보다 높은 금액을 설정하실 수 없습니다.'));
                }
            }
        }

        // 환불처리 할 사용예치금/사용마일리지/적립마일리지 값 초기화
        $totalRefundUseDeposit = 0;
        $totalRefundUseMileage = 0;
        $totalRefundGiveMileage = 0;

        // 최하단 주문상품의 마일리지/예치금 처리를 위한 주문상품번호
        $orderGoodsNo = [];

        // 실제 환불된 복합과세 총 금액 계산
        $refundComplexTax = [];

        // 전체 환불금액
        $totalRefundPrice = 0;

        // 전체 해외배송비 보험료
        $totalDeliveryInsuranceFee = 0;

        // 처리상품 SNO `
        $arrOrderGoodsSnos = [];

        // 주문상품 처리
        $onlyOverseasDelivery = false;
        foreach ($orderInfo['goods'] as $sKey => $sVal) {
            foreach ($sVal as $dKey => $dVal) {
                foreach ($dVal as $gKey => $gVal) {
                    // 환불테이블 업데이트용 데이터 초기화
                    $refundData = $getData['refund'][$gVal['handleSno']];
                    if (empty($refundData['refundAccountNumber']) === false) {
                        $refundData['refundAccountNumber'] = \Encryptor::encrypt($refundData['refundAccountNumber']);
                    }

                    // 환불 정보 추가
                    foreach ($getData['info'] as $iKey => $iVal) {
                        $refundData[$iKey] = $iVal;
                    }

                    // 처리 중인 상품SNO 저장 (하단에 회원 구매결정금액 산정시 필요)
                    $arrOrderGoodsSnos[] = $gVal['sno'];

                    // 환불완료 변경 플래그
                    $refundData['handleCompleteFl'] = 'y';
                    $refundData['handleDt'] = date('Y-m-d H:i:s');

                    // 환불그룹 코드
                    $refundData['refundGroupCd'] = $refundGroupCd;

                    // 환불실제결제금액 초기화
                    $refundData['settlePrice'] = 0;

                    // 환불상세에서 재고환원 여부에 동의시 처리여부
                    $isReturnStock = true;// 수기처리시 사용할 예정 ($refundData['returnStock'] == 'y');

                    // 환불 할 배송비 추가 (첫번째 상품만 배송비 환불금액 입력하면 됨)
                    if ($gKey == 0) {
                        // 해외배송의 경우 공급사가 다르더라도 배송비조건 일련번호가 같기 때문에 0원으로 오버라이딩 되는 증상이 있다.
                        // 해외상점 주문시 최초 배송비가 들어간 이후에는 처리되지 않도록 강제로 예외처리 (해당 처리 안하면 배송비가 해외상점인 경우 무조건 0원으로 나옴)
                        if ($orderInfo['mallSno'] == DEFAULT_MALL_NUMBER || ($orderInfo['mallSno'] > DEFAULT_MALL_NUMBER && $onlyOverseasDelivery === false)) {
                            $refundData['refundDeliveryCharge'] = $realRefundDeliveryCharge['user'][$dKey];// 실제환불에 사용한 배송비

                            // 배송비 세금정보
                            $deliveryTaxInfo = explode(STR_DIVISION, $gVal['deliveryTaxInfo']);

                            // 해외배송비 보험료 정보
                            $refundData['refundDeliveryInsuranceFee'] = gd_isset($gVal['deliveryInsuranceFee'], 0);

                            // 환불한 배송비를 주문 전체의 복합과세 금액에 더한다. (order 테이블 realTax 업데이트용)
                            if ($refundData['refundDeliveryCharge'] > 0) {
                                $tmpUseDeliveryTaxPrice = NumberUtils::taxAll($refundData['refundDeliveryCharge'], $deliveryTaxInfo[1], $deliveryTaxInfo[0]);
                                if ($deliveryTaxInfo[0] == 't') {
                                    $refundComplexTax['taxSupply'] += $tmpUseDeliveryTaxPrice['supply'];
                                    $refundComplexTax['taxVat'] += $tmpUseDeliveryTaxPrice['tax'];
                                } else {
                                    $refundComplexTax['taxFree'] += $tmpUseDeliveryTaxPrice['supply'];
                                }
                            }

                            // 남아있는 배송비 real에 업데이트 처리
                            $orderDeliveryData = [];
                            $tmpRealDeliveryTaxPrice = NumberUtils::taxAll($realRefundDeliveryCharge['rest'][$dKey] - $refundData['refundDeliveryCharge'], $deliveryTaxInfo[1], $deliveryTaxInfo[0]);
                            if ($deliveryTaxInfo[0] == 't') {
                                $orderDeliveryData['realTaxSupplyDeliveryCharge'] = $tmpRealDeliveryTaxPrice['supply'];
                                $orderDeliveryData['realTaxVatDeliveryCharge'] = $tmpRealDeliveryTaxPrice['tax'];
                                $orderDeliveryData['realTaxFreeDeliveryCharge'] = 0;
                            } else {
                                $orderDeliveryData['realTaxSupplyDeliveryCharge'] = 0;
                                $orderDeliveryData['realTaxVatDeliveryCharge'] = 0;
                                $orderDeliveryData['realTaxFreeDeliveryCharge'] = $tmpRealDeliveryTaxPrice['supply'];
                            }

                            // 배송비의 복합과세금액 업데이트
                            $compareField = array_keys($orderDeliveryData);
                            $arrBind = $this->db->get_binding(DBTableField::tableOrderDelivery(), $orderDeliveryData, 'update', $compareField);
                            $arrWhere = 'orderNo = ? AND sno = ?';
                            $this->db->bind_param_push($arrBind['bind'], 's', $getData['orderNo']);
                            $this->db->bind_param_push($arrBind['bind'], 's', $gVal['orderDeliverySno']);
                            $this->db->set_update_db(DB_ORDER_DELIVERY, $arrBind['param'], $arrWhere, $arrBind['bind']);
                            unset($arrBind, $orderDeliveryData);

                            // 해외배송비
                            $onlyOverseasDelivery = true;
                        }
                    }

                    // 계산 기준 금액 생성 (배송비에 안분된 예치금/마일리지 포함)
                    $refundData['settlePrice'] += (($gVal['goodsPrice'] + $gVal['optionPrice'] + $gVal['optionTextPrice']) * $gVal['goodsCnt']) + $gVal['addGoodsPrice'] - $gVal['goodsDcPrice'] - $gVal['totalMemberDcPrice'] - $gVal['totalMemberOverlapDcPrice'] - $gVal['totalCouponGoodsDcPrice'] - $gVal['totalDivisionUseDeposit'] - $gVal['totalDivisionUseMileage'] - $gVal['totalDivisionCouponOrderDcPrice'];

                    // 실제계산된 금액에서 배송비로 할당된 예치금과 마일리지를 더해줘야 UI상에 표출된 결제금액이 나온다.
                    $refundData['settlePrice'] += $gVal['divisionGoodsDeliveryUseDeposit'] + $gVal['divisionGoodsDeliveryUseMileage'];

                    $refundData['refundUseDeposit'] = $gVal['totalDivisionUseDeposit'];
                    $refundData['refundUseMileage'] = $gVal['totalDivisionUseMileage'];
                    $refundData['originGiveMileage'] = $gVal['totalRealGoodsMileage'] + $gVal['totalRealMemberMileage'] + $gVal['totalRealCouponGoodsMileage'] + $gVal['totalRealDivisionCouponOrderMileage'];

                    // 금액에서 , 제거
                    $refundData['refundGiveMileage'] = intval(str_replace(',', '', $refundData['refundGiveMileage']));
                    $refundData['refundCharge'] = intval(str_replace(',', '', $refundData['refundCharge']));

                    // 실 환불금액 = 상품결제금액 + 배송비 - 환불수수료
                    $refundData['refundPrice'] = $refundData['settlePrice'] + $refundData['refundDeliveryCharge'] + $refundData['refundDeliveryInsuranceFee'] - $refundData['refundCharge'];

                    // 적립마일리지 validate
                    if ($refundData['originGiveMileage'] < $refundData['refundGiveMileage']) {
                        throw new Exception(__('적립마일리지는 차감시킬 마일리지보다 클 수 없습니다.'));
                    }

                    // 하단에서 전체 환불금액을 비교하기 위한 처리
                    $totalRefundPrice += $refundData['refundPrice'];

                    // 주문 상태 변경 처리 및 재고 복원여부 처리
                    $this->updateStatusPreprocess($getData['orderNo'], $this->getOrderGoods($getData['orderNo'], null, $gVal['handleSno']), 'r', 'r3', __('일괄'), $isReturnStock, null, null, $autoProcess);

                    // 환불수수료 안분 작업 후 복합과세 금액 업데이트 (추가상품의 환불수수료 비율을 구한 다음 나머지를 상품 환불수수료로 사용)
                    if ($refundData['refundCharge'] > 0) {
                        // 주문상품의 실 결제 금액 = 과세 + 면세 + 부가세
                        $goodsTaxablePrice = $gVal['taxSupplyGoodsPrice'] + $gVal['taxVatGoodsPrice'] + $gVal['taxFreeGoodsPrice'];

                        // 전체 복합과세 = 상품 복합과세 금액 + 추가상품 복합과세 금액
                        $totalTaxablePrice = $goodsTaxablePrice;

                        // 추가상품의 실 결제 금액 = 과세 + 면세 + 부가세
                        $addGoodsTaxablePrice = [];
                        if (empty($gVal['addGoods']) === false) {
                            foreach ($gVal['addGoods'] as $aKey => $aVal) {
                                $addGoodsTaxablePrice[$aKey] = $aVal['taxSupplyAddGoodsPrice'] + $aVal['taxVatAddGoodsPrice'] + $aVal['taxFreeAddGoodsPrice'];
                                $totalTaxablePrice += $addGoodsTaxablePrice[$aKey];
                            }
                        }

                        // 추가상품의 비율로 환불수수료 산출
                        if (empty($gVal['addGoods']) === false) {
                            foreach ($gVal['addGoods'] as $aKey => $aVal) {
                                // 전체금액 대비 비율 산정 (소수점까지 표현)
                                $addGoodsTaxRate = ($addGoodsTaxablePrice[$aKey] / $totalTaxablePrice);
                                $tmpAddGoodsTaxablePrice = NumberUtils::getNumberFigure($refundData['refundCharge'] * $addGoodsTaxRate, $truncPolicy['unitPrecision'], 'round');
                                $tmpOrderGoodsRefundCharge['addGoods'][$aKey] = $tmpAddGoodsTaxablePrice;

                                // 주문추가상품 복합과세 재계산
                                $addGoodsTaxablePrice[$aKey] = $addGoodsTaxablePrice[$aKey] - $tmpAddGoodsTaxablePrice;
                                $realTaxSupplyAddGoodsPrice = 0;
                                $realTaxVatAddGoodsPrice = 0;
                                $realTaxFreeAddGoodsPrice = 0;
                                $tmpAddGoodsTaxInfo = explode(STR_DIVISION, $aVal['goodsTaxInfo']);
                                $tmpAddGoodsTaxPrice = NumberUtils::taxAll($addGoodsTaxablePrice[$aKey], $tmpAddGoodsTaxInfo[1], $tmpAddGoodsTaxInfo[0]);
                                if ($tmpAddGoodsTaxInfo[0] == 't') {
                                    $realTaxSupplyAddGoodsPrice = gd_isset($tmpAddGoodsTaxPrice['supply'], 0);
                                    $realTaxVatAddGoodsPrice = gd_isset($tmpAddGoodsTaxPrice['tax'], 0);
                                } else {
                                    $realTaxFreeAddGoodsPrice = gd_isset($tmpAddGoodsTaxPrice['supply'], 0);
                                }
                                unset($tmpAddGoodsTaxPrice);

                                // 실제 환불된 복합과세 금액 중 추가상품 계산
                                $refundComplexTax['taxSupply'] += $realTaxSupplyAddGoodsPrice;
                                $refundComplexTax['taxVat'] += $realTaxVatAddGoodsPrice;
                                $refundComplexTax['taxFree'] += $realTaxFreeAddGoodsPrice;

                                // 추가상품의 복합과세금액 업데이트 (남은 금액 = 환불수수료 안분 금액분)
                                $tmpAddGoodsTaxablePrice = NumberUtils::taxAll($tmpAddGoodsTaxablePrice, $tmpAddGoodsTaxInfo[1], $tmpAddGoodsTaxInfo[0]);
                                if ($tmpAddGoodsTaxInfo[0] == 't') {
                                    $orderAddGoodsData['realTaxSupplyAddGoodsPrice'] = gd_isset($tmpAddGoodsTaxablePrice['supply'], 0);
                                    $orderAddGoodsData['realTaxVatAddGoodsPrice'] = gd_isset($tmpAddGoodsTaxablePrice['tax'], 0);
                                    $orderAddGoodsData['realTaxFreeAddGoodsPrice'] = 0;
                                } else {
                                    $orderAddGoodsData['realTaxSupplyAddGoodsPrice'] = 0;
                                    $orderAddGoodsData['realTaxVatAddGoodsPrice'] = 0;
                                    $orderAddGoodsData['realTaxFreeAddGoodsPrice'] = gd_isset($tmpAddGoodsTaxablePrice['supply'], 0);
                                }
                                $compareField = array_keys($orderAddGoodsData);
                                $arrBind = $this->db->get_binding(DBTableField::tableOrderAddGoods(), $orderAddGoodsData, 'update', $compareField);
                                $arrWhere = 'orderNo = ? AND sno = ?';
                                $this->db->bind_param_push($arrBind['bind'], 's', $getData['orderNo']);
                                $this->db->bind_param_push($arrBind['bind'], 's', $aVal['sno']);
                                $this->db->set_update_db(DB_ORDER_ADD_GOODS, $arrBind['param'], $arrWhere, $arrBind['bind']);
                                unset($arrBind, $orderAddGoodsData, $tmpAddGoodsTaxPrice);
                            }
                        }

                        // 주문상품의 환불수수료 = 총 환불수수료 - 추가상품 환불수수료
                        $tmpOrderGoodsRefundCharge['goods'] = $refundData['refundCharge'];
                        if (empty($tmpOrderGoodsRefundCharge['addGoods']) === false) {
                            $tmpOrderGoodsRefundCharge['goods'] = $refundData['refundCharge'] - array_sum($tmpOrderGoodsRefundCharge['addGoods']);
                        }

                        // 주문상품 복합과세 재계산
                        $goodsTaxablePrice = $goodsTaxablePrice - $tmpOrderGoodsRefundCharge['goods'];
                        $realTaxSupplyGoodsPrice = 0;
                        $realTaxVatGoodsPrice = 0;
                        $realTaxFreeGoodsPrice = 0;
                        $tmpGoodsTaxPrice = NumberUtils::taxAll($goodsTaxablePrice, $gVal['goodsTaxInfo'][1], $gVal['goodsTaxInfo'][0]);
                        if ($gVal['goodsTaxInfo'][0] == 't') {
                            $realTaxSupplyGoodsPrice = gd_isset($tmpGoodsTaxPrice['supply'], 0);
                            $realTaxVatGoodsPrice = gd_isset($tmpGoodsTaxPrice['tax'], 0);
                        } else {
                            $realTaxFreeGoodsPrice = gd_isset($tmpGoodsTaxPrice['supply'], 0);
                        }
                        unset($tmpGoodsTaxPrice);

                        // 실제 환불된 복합과세 금액 중 상품 계산 (환불수수수료 안분 후)
                        $refundComplexTax['taxSupply'] += $realTaxSupplyGoodsPrice;
                        $refundComplexTax['taxVat'] += $realTaxVatGoodsPrice;
                        $refundComplexTax['taxFree'] += $realTaxFreeGoodsPrice;

                        // 주문상품의 남아있는 복합과세금액 업데이트
                        $tmpGoodsTaxablePrice = NumberUtils::taxAll($tmpOrderGoodsRefundCharge['goods'], $gVal['goodsTaxInfo'][1], $gVal['goodsTaxInfo'][0]);
                        if ($gVal['goodsTaxInfo'][0] == 't') {
                            $orderGoodsData['realTaxSupplyGoodsPrice'] = gd_isset($tmpGoodsTaxablePrice['supply'], 0);
                            $orderGoodsData['realTaxVatGoodsPrice'] = gd_isset($tmpGoodsTaxablePrice['tax'], 0);
                            $orderGoodsData['realTaxFreeGoodsPrice'] = 0;
                        } else {
                            $orderGoodsData['realTaxSupplyGoodsPrice'] = 0;
                            $orderGoodsData['realTaxVatGoodsPrice'] = 0;
                            $orderGoodsData['realTaxFreeGoodsPrice'] = gd_isset($tmpGoodsTaxablePrice['supply'], 0);
                        }
                        $compareField = array_keys($orderGoodsData);
                        $arrBind = $this->db->get_binding(DBTableField::tableOrderGoods(), $orderGoodsData, 'update', $compareField);
                        $arrWhere = 'orderNo = ? AND sno = ?';
                        $this->db->bind_param_push($arrBind['bind'], 's', $getData['orderNo']);
                        $this->db->bind_param_push($arrBind['bind'], 's', $gVal['sno']);
                        $this->db->set_update_db(DB_ORDER_GOODS, $arrBind['param'], $arrWhere, $arrBind['bind']);
                        unset($arrBind, $orderGoodsData, $tmpOrderGoodsRefundCharge);
                    } else {
                        // 실제 환불된 복합과세 금액 중 추가상품 계산
                        if (empty($gVal['addGoods']) === false) {
                            foreach ($gVal['addGoods'] as $aKey => $aVal) {
                                $refundComplexTax['taxSupply'] += $aVal['taxSupplyAddGoodsPrice'];
                                $refundComplexTax['taxVat'] += $aVal['taxVatAddGoodsPrice'];
                                $refundComplexTax['taxFree'] += $aVal['taxFreeAddGoodsPrice'];

                                // 남아있는 주문상품 복합과세금액 계산 (상품금액에 대한 부분금액 설정이 없기때문에 남아있는 금액 0원으로 전액환불)
                                $orderAddGoodsData['realTaxSupplyAddGoodsPrice'] = 0;
                                $orderAddGoodsData['realTaxVatAddGoodsPrice'] = 0;
                                $orderAddGoodsData['realTaxFreeAddGoodsPrice'] = 0;
                                $compareField = array_keys($orderAddGoodsData);
                                $arrBind = $this->db->get_binding(DBTableField::tableOrderAddGoods(), $orderAddGoodsData, 'update', $compareField);
                                $arrWhere = 'orderNo = ? AND sno = ?';
                                $this->db->bind_param_push($arrBind['bind'], 's', $getData['orderNo']);
                                $this->db->bind_param_push($arrBind['bind'], 's', $aVal['sno']);
                                $this->db->set_update_db(DB_ORDER_ADD_GOODS, $arrBind['param'], $arrWhere, $arrBind['bind']);
                                unset($arrBind, $orderAddGoodsData);
                            }
                        }

                        // 실제 환불된 복합과세 금액 중 상품 계산
                        $refundComplexTax['taxSupply'] += $gVal['taxSupplyGoodsPrice'];
                        $refundComplexTax['taxVat'] += $gVal['taxVatGoodsPrice'];
                        $refundComplexTax['taxFree'] += $gVal['taxFreeGoodsPrice'];

                        // 남아있는 주문상품 복합과세금액 계산 (상품금액에 대한 부분금액 설정이 없기때문에 남아있는 금액 0원으로 전액환불)
                        $orderGoodsData['realTaxSupplyGoodsPrice'] = 0;
                        $orderGoodsData['realTaxVatGoodsPrice'] = 0;
                        $orderGoodsData['realTaxFreeGoodsPrice'] = 0;
                        $compareField = array_keys($orderGoodsData);
                        $arrBind = $this->db->get_binding(DBTableField::tableOrderGoods(), $orderGoodsData, 'update', $compareField);
                        $arrWhere = 'orderNo = ? AND sno = ?';
                        $this->db->bind_param_push($arrBind['bind'], 's', $getData['orderNo']);
                        $this->db->bind_param_push($arrBind['bind'], 's', $gVal['sno']);
                        $this->db->set_update_db(DB_ORDER_GOODS, $arrBind['param'], $arrWhere, $arrBind['bind']);
                        unset($arrBind, $orderGoodsData);
                    }

                    // 사용예치금 100% 복원 처리 및 환불테이블에 들어갈 값 정의
                    if ($gVal['minusDepositFl'] == 'y') {
                        if ($refundData['refundUseDeposit'] > 0) {
                            $totalRefundUseDeposit += $refundData['refundUseDeposit'];
                            $orderGoodsNo['deposit'][] = $gVal['sno'];
                        }
                    }

                    // 사용마일리지 100% 복원 처리 및 환불테이블에 들어갈 값 정의
                    if ($gVal['minusMileageFl'] == 'y') {
                        if ($refundData['refundUseMileage'] > 0) {
                            $totalRefundUseMileage += $refundData['refundUseMileage'];
                            $orderGoodsNo['mileage'][] = $gVal['sno'];
                        }
                    }

                    // 적립마일리지가 있는 경우 환원 및 환불테이블에 들어갈 값 정의
                    if ($gVal['plusMileageFl'] == 'y') {
                        if ($refundData['refundGiveMileage'] > 0) {
                            $totalRefundGiveMileage += $refundData['refundGiveMileage'];
                            $orderGoodsNo['giveMileage'][] = $gVal['sno'];
                        }
                    }

                    // 주문 환불 테이블 수정
                    unset($refundData['sno'], $refundData['returnStock'], $refundData['orderNo'], $refundData['settlePrice'], $refundData['originGiveMileage'], $refundData['handleDetailReason']);
                    $compareField = array_keys($refundData);
                    $arrBind = $this->db->get_binding(DBTableField::tableOrderHandle(), $refundData, 'update', $compareField);
                    $arrWhere = 'orderNo = ? AND sno = ?';
                    $this->db->bind_param_push($arrBind['bind'], 's', $getData['orderNo']);
                    $this->db->bind_param_push($arrBind['bind'], 's', $gVal['handleSno']);
                    if ($this->db->set_update_db(DB_ORDER_HANDLE, $arrBind['param'], $arrWhere, $arrBind['bind'])) {
                        unset($arrBind, $refundData);
                    } else {
                        throw new Exception(__('환불정보 저장에 실패했습니다.'));
                    }
                }
            }
        }

        // 실환불금액과 사용자 지정 환불금액의 일치 여부 확인
        if ($totalRefundPrice != $getData['info']['completeCashPrice'] + $getData['info']['completePgPrice'] + $getData['info']['completeDepositPrice'] + $getData['info']['completeMileagePrice']) {
            throw new Exception(__('실 환불금액과 입력하신 환불 금액 설정 금액이 일치하지 않습니다.'.$totalRefundPrice.':'.$getData['info']['completeCashPrice'].':'.$getData['info']['completePgPrice'].':'.$getData['info']['completeDepositPrice'].':'.$getData['info']['completeMileagePrice']));
        }

        // 상단에서 취소 업데이트된 데이터를 다시 쿼리해 실 복합과세 금액 재 계산 (전체 주문의 실제 남아있는 금액)
        $orderComplexTax = $this->getOrderRealComplexTax($getData['orderNo']);

        // 업데이트 할 복합과세 금액 설정
        $orderData['realTaxSupplyPrice'] = $orderComplexTax['taxSupply'];
        $orderData['realTaxVatPrice'] = $orderComplexTax['taxVat'];
        $orderData['realTaxFreePrice'] = $orderComplexTax['taxFree'];

        // 주문테이블 업데이트 (관리자 메모, 전체환불에 따른 minus/plus 플래그, 실 복합과세 금액)
        $orderData['adminMemo'] = $getData['order']['adminMemo'];
        $compareField = array_keys($orderData);
        $arrBind = $this->db->get_binding(DBTableField::tableOrder(), $orderData, 'update', $compareField);
        $this->db->bind_param_push($arrBind['bind'], 's', $getData['orderNo']);
        $this->db->set_update_db(DB_ORDER, $arrBind['param'], 'orderNo = ?', $arrBind['bind']);
        unset($arrBind, $orderData);

        // 관리자 선택에 따른 쿠폰 복원 처리
        if (empty($getData['tmp']['memberCouponNo']) === false) {
            $coupon = App::load(\Component\Coupon\Coupon::class);
            foreach ($getData['tmp']['memberCouponNo'] as $memberCouponNo) {
                // 쿠폰 복원 처리
                $coupon->setMemberCouponState($memberCouponNo, 'y');

                // 할인쿠폰 테이블 복원 여부 변경
                $orderData['minusCouponFl'] = 'y';
                $orderData['minusRestoreCouponFl'] = 'y';
                $compareField = array_keys($orderData);
                $arrBind = $this->db->get_binding(DBTableField::tableOrderCoupon(), $orderData, 'update', $compareField);
                $this->db->bind_param_push($arrBind['bind'], 'i', $getData['orderNo']);
                $this->db->bind_param_push($arrBind['bind'], 'i', $memberCouponNo);
                $this->db->set_update_db(DB_ORDER_COUPON, $arrBind['param'], 'orderNo = ? AND memberCouponNo = ? AND minusCouponFl = \'y\'', $arrBind['bind']);
                unset($arrBind, $orderData);

                // 적립쿠폰 테이블 복원 여부 변경
                $orderData['plusCouponFl'] = 'y';
                $orderData['plusRestoreCouponFl'] = 'y';
                $compareField = array_keys($orderData);
                $arrBind = $this->db->get_binding(DBTableField::tableOrderCoupon(), $orderData, 'update', $compareField);
                $this->db->bind_param_push($arrBind['bind'], 'i', $getData['orderNo']);
                $this->db->bind_param_push($arrBind['bind'], 'i', $memberCouponNo);
                $this->db->set_update_db(DB_ORDER_COUPON, $arrBind['param'], 'orderNo = ? AND memberCouponNo = ? AND plusCouponFl = \'y\'', $arrBind['bind']);
                unset($arrBind, $orderData);
            }
        }

        // 사용예치금 일괄 복원
        if ($totalRefundUseDeposit > 0) {
            /** @var \Bundle\Component\Deposit\Deposit $deposit */
            $deposit = \App::load('\\Component\\Deposit\\Deposit');
            if ($deposit->setMemberDeposit($orderInfo['memNo'], $totalRefundUseDeposit, Deposit::REASON_CODE_GROUP . Deposit::REASON_CODE_DEPOSIT_REFUND, 'o', $orderInfo['orderNo'], null, null, $orderGoodsNo['deposit'])) {
                $orderGoodsData['minusDepositFl'] = 'n';
                $orderGoodsData['minusRestoreDepositFl'] = 'y';

                // DB 업데이트
                $compareField = array_keys($orderGoodsData);
                $arrBind = $this->db->get_binding(DBTableField::tableOrderGoods(), $orderGoodsData, 'update', $compareField);
                $this->db->set_update_db(DB_ORDER_GOODS, $arrBind['param'], 'sno IN (\'' . implode('\',\'', $orderGoodsNo['deposit']) . '\')', $arrBind['bind']);
                unset($arrBind, $orderGoodsData);
            }
        }

        // 사용마일리지 일괄 복원
        if ($totalRefundUseMileage > 0) {
            /** @var \Bundle\Component\Mileage\Mileage $mileage */
            $mileage = \App::load('\\Component\\Mileage\\Mileage');
            $mileage->setIsTran(false);
            if ($mileage->setMemberMileage($orderInfo['memNo'], $totalRefundUseMileage, Mileage::REASON_CODE_GROUP . Mileage::REASON_CODE_USE_GOODS_BUY_RESTORE, 'o', $orderInfo['orderNo'])) {
                $orderGoodsData['minusMileageFl'] = 'n';
                $orderGoodsData['restoreMinusMileageFl'] = 'y';

                // DB 업데이트
                $compareField = array_keys($orderGoodsData);
                $arrBind = $this->db->get_binding(DBTableField::tableOrderGoods(), $orderGoodsData, 'update', $compareField);
                $this->db->set_update_db(DB_ORDER_GOODS, $arrBind['param'], 'sno IN (\'' . implode('\',\'', $orderGoodsNo['mileage']) . '\')', $arrBind['bind']);
                unset($arrBind, $orderGoodsData);
            }
        }

        // 마일리지 차감 방법
        $member = App::load(\Component\Member\Member::class);
        $memInfo = $member->getMemberId($orderInfo['memNo']);

        // 회원일 경우에만 회원정보 수집
        if(!empty($memInfo['memId'])) {
            $memData = gd_htmlspecialchars($member->getMember($memInfo['memId'], 'memId'));
        }

        // 적립마일리지가 회원보유 마일리지보다 큰 경우 적립마일리지와 보유마일리지의 차액 산출하고 하단에서 차액만 별도로 처리 한다.
        $minusRefundGiveMileage = 0;
        if ($getData['tmp']['refundMinusMileage'] == 'n' && $memData['mileage'] < $totalRefundGiveMileage) {
            $minusRefundGiveMileage = $totalRefundGiveMileage - $memData['mileage'];
            $totalRefundGiveMileage = $memData['mileage'];
        }

        // 적립마일리지 일괄 차감
        if ($totalRefundGiveMileage > 0) {
            /** @var \Bundle\Component\Mileage\Mileage $mileage */
            $mileage = \App::load('\\Component\\Mileage\\Mileage');
            $mileage->setIsTran(false);
            if ($mileage->setMemberMileage($orderInfo['memNo'], ($totalRefundGiveMileage * -1), Mileage::REASON_CODE_GROUP . Mileage::REASON_CODE_ADD_GOODS_BUY_RESTORE, 'o', $orderInfo['orderNo'])) {
                $orderGoodsData['plusMileageFl'] = 'n';
                $orderGoodsData['restorePlusMileageFl'] = 'y';

                // DB 업데이트
                $compareField = array_keys($orderGoodsData);
                $arrBind = $this->db->get_binding(DBTableField::tableOrderGoods(), $orderGoodsData, 'update', $compareField);
                $this->db->set_update_db(DB_ORDER_GOODS, $arrBind['param'], 'sno IN (\'' . implode('\',\'', $orderGoodsNo['giveMileage']) . '\')', $arrBind['bind']);
                unset($arrBind, $orderGoodsData);
            }
        }

        // 보유마일리지보다 차감 마일리지가 큰 경우 별도 계산된 차액을 따로 처리한다. (별도의 마일리지로 회원쪽에 들어간다)
        if ($minusRefundGiveMileage > 0) {
            $mileage->setIsTran(false);
            $mileage->setMemberMileage($orderInfo['memNo'], ($minusRefundGiveMileage * -1), Mileage::REASON_CODE_GROUP . Mileage::REASON_CODE_ADD_GOODS_BUY_RESTORE, 'o', $orderInfo['orderNo']);
        }

        // 회원정보 구매정보에서 환불내역 제거하는 업데이트
        foreach ($arrOrderGoodsSnos as $sno) {
            // 구매확정이 한번이라도 됬어다면
            if ($this->isSetOrderStatus($orderInfo['orderNo'], $sno) !== false) {
                $memberUpdateWhereByRefund = 'memNo = \'' . $this->db->escape($orderInfo['memNo']) . '\' AND saleCnt > 0';
                $this->db->set_update_db_query(DB_MEMBER, 'saleAmt = saleAmt - ' . gd_money_format($getData['check']['totalRefundPrice'], false) . ', saleCnt = saleCnt - ' . $orderInfo['cnt']['goods']['goods'], $memberUpdateWhereByRefund);
                break;
            }
        }

        // 현금환불 처리
        if ($getData['info']['completeCashPrice'] > 0) {
            $completeCashPrice = gd_money_format($getData['info']['completeCashPrice'], false);
        }

        // 예치금환불 처리
        if ($getData['info']['completeDepositPrice'] !== 0) {
            $completeDepositPrice = gd_money_format($getData['info']['completeDepositPrice'], false);
            /** @var \Bundle\Component\Deposit\Deposit $deposit */
            $deposit = \App::load('\\Component\\Deposit\\Deposit');
            $deposit->setMemberDeposit($orderInfo['memNo'], $completeDepositPrice, Deposit::REASON_CODE_GROUP . Deposit::REASON_CODE_DEPOSIT_REFUND, 'o', $orderInfo['orderNo']);
        }

        // 마일리지(기타) 처리
        if ($getData['info']['completeMileagePrice'] > 0) {
            // 2016-10-10 “여신전문금융업법” 적용으로 현금성이 아닌 마일리지로 환불 시 법령 위반되어 기타환불의 경우 환불완료 처리 시 "현금환불"과 동일하게 자동으로 환불처리가 되지 않도록 변경
        }

        // PG 취소 처리
        if ($getData['info']['completePgPrice'] > 0) {
            // 주문 데이터
            $tmp = [];
            $tmp['orderNo'] = $orderInfo['orderNo'];
            $tmp['handleSno'] = $getData['handleSno'];
            $tmp['orderName'] = $orderInfo['orderName'];
            $tmp['orderPhone'] = $orderInfo['orderPhone'];
            $tmp['orderCellPhone'] = $orderInfo['orderCellPhone'];
            $tmp['orderEmail'] = $orderInfo['orderEmail'];
            $tmp['settleKind'] = $orderInfo['settleKind'];
            $tmp['pgTid'] = $orderInfo['pgTid'];
            $tmp['pgAppNo'] = $orderInfo['pgAppNo'];
            $tmp['pgAppDt'] = $orderInfo['pgAppDt'];
            $tmp['pgResultCode'] = $orderInfo['pgResultCode'];

            // 주문유형
            $tmp['orderTypeFl'] = $orderInfo['orderTypeFl'];

            // 초기 주문 금액
            $tmp['settlePrice'] = gd_money_format($orderInfo['settlePrice'], false);
            $tmp['taxSupplyPrice'] = gd_money_format($orderInfo['taxSupplyPrice'], false);
            $tmp['taxVatPrice'] = gd_money_format($orderInfo['taxVatPrice'], false);
            $tmp['taxFreePrice'] = gd_money_format($orderInfo['taxFreePrice'], false);

            // PG 취소 금액
            $tmp['cancelPrice'] = gd_money_format($getData['info']['completePgPrice'], false);

            // 환불 금액 (PG 취소 금액 이외의 다른 금액 포함)
            $tmp['refundPrice'] = gd_money_format($getData['check']['totalRefundPrice'], false);
            $tmp['refundTaxSupplyPrice'] = gd_money_format($refundComplexTax['taxSupply'], false);
            $tmp['refundTaxVatPrice'] = gd_money_format($refundComplexTax['taxVat'], false);
            $tmp['refundTaxFreePrice'] = gd_money_format($refundComplexTax['taxFree'], false);

            // 간편결제 관련 데이터
            $tmp['pgName'] = $orderInfo['pgName'];
            $tmp['checkoutData'] = $orderInfo['checkoutData'];

            // PG 취소 모듈 실행
            $pgCancel = \App::load('\\Component\\Payment\\Cancel');
            $result = $pgCancel->sendPgCancel($getData, $tmp);
            if ($orderInfo['pgName'] == 'payco' && in_array($orderInfo['settleKind'], ['pb', 'fb', 'eb']) === true && count($getData['refund']) == $orderInfo['orderGoodsCnt']) {
                if ($result === true) {
                    $pgConf = gd_pgs('payco');
                    $pgCancel = \App::load('\\Component\\Payment\\Payco\\PgCancel');
                    $result = $pgCancel->setCancelStatus($pgConf, $getData, $orderInfo);
                }
            }

            // 실패시 롤백 처리
            if ($result !== true) {
                throw new Exception(__('PG 취소 진행시 오류가 발생이되어, 취소에 실패 하였습니다.') . ' [' . $result . ']');
            }
            unset($tmp);
        } else {
            //페이코 주문인 경우 PG환불이 아닌 경우 취소로 상태변경을 해줘야 함.
            if ($orderInfo['pgName'] == 'payco') {
                $pgConf = gd_pgs('payco');
                $pgCancel = \App::load('\\Component\\Payment\\Payco\\PgCancel');
                $result = $pgCancel->setCancelStatus($pgConf, $getData, $orderInfo);

                // 실패시 롤백 처리
                if ($result !== true) {
                    throw new Exception(__('PG 취소 진행시 오류가 발생이되어, 취소에 실패 하였습니다.') . ' [' . $result . ']');
                }
            }
        }
    }

    /**
     * 남아있는 복합과세 금액 = 환불을 제외한 총 주문상품 복합과세 + 환불을 제외한 총 배송비 복합과세
     *
     * @param integer $orderNo              주문번호
     * @param integer $orderGoodsNo         주문상품번호
     * @param boolean $exceptDeliveryCharge 배송비제외 여부
     *
     * @return array 실 복합과세 금액
     * @author Jong-tae Ahn <qnibus@godo.co.kr>
     */
    public function getOrderRealComplexTax($orderNo, $orderGoodsNo = null, $exceptDeliveryCharge = false)
    {
        // 반환할 복합과세금액 초기화
        $taxSupply = 0;
        $taxVat = 0;
        $taxFree = 0;
        $deliveryCharge = 0;

        // 전체 환불 여부
        $isWholeRefundComplete = false;

        // 전체 환불완료 갯수
        $wholeRefundComplete = 0;

        // 총 주문 갯수 주문
        $orderCnt = 0;

        // 상품데이터가 2개이상인 경우 배열화 처리
        $getData = $this->getOrderGoodsData($orderNo, $orderGoodsNo, null, null, null, false);
        if (isset($getData['sno']) === true) {
            $tmpData[] = $getData;
        } else {
            $tmpData = $getData;
        }

        // 주문상품 + 주문추가상품 복합과세 금액 계산
        foreach ($tmpData as $key => $val) {
            $taxSupply += $val['realTaxSupplyGoodsPrice'];
            $taxVat += $val['realTaxVatGoodsPrice'];
            $taxFree += $val['realTaxFreeGoodsPrice'];
            $deliveryCharge = $val['realTaxSupplyDeliveryCharge'] + $val['realTaxVatDeliveryCharge'] + $val['realTaxFreeDeliveryCharge']; // 배송비도 더해주도록 처리
            if (empty($val['addGoods']) === false) {
                foreach ($val['addGoods'] as $aKey => $aVal) {
                    $taxSupply += $aVal['realTaxSupplyAddGoodsPrice'];
                    $taxVat += $aVal['realTaxVatAddGoodsPrice'];
                    $taxFree += $aVal['realTaxFreeAddGoodsPrice'];
                }
            }

            // 전체 환불되었는지에 대한 체크
            if (in_array($val['orderStatus'], ['r3'])) {
                if ($val['handleCompleteFl'] == 'y') {
                    $wholeRefundComplete++;
                }
            } else {
                $orderCnt++;
            }
        }

        // 전체 환불 여부
        if (count($tmpData) == $wholeRefundComplete) {
            $isWholeRefundComplete = true;
        }

        unset($tmpData, $getData);

        // 배송비 복합과세 금액 계산
        if ($exceptDeliveryCharge === true) {
            $getData = $this->getOrderDelivery($orderNo, null, true);
            foreach ($getData as $key => $val) {
                foreach($val as $deliveryData) {
                    $taxSupply += $deliveryData['realTaxSupplyDeliveryCharge'];
                    $taxVat += $deliveryData['realTaxVatDeliveryCharge'];
                    $taxFree += $deliveryData['realTaxFreeDeliveryCharge'];
                }
            }
            unset($getData);
        }

        return [
            'taxSupply' => $taxSupply,
            'taxVat' => $taxVat,
            'taxFree' => $taxFree,
            'deliveryCharge' => $deliveryCharge,
            'orderGoodsCnt' => $orderCnt,
            'isWholeRefundComplete' => $isWholeRefundComplete,
        ];
    }

    /**
     * orderUserHandle 테이블의 수량
     *
     * @return bool|string 수량 배열
     */
    public function getCountUserHandles()
    {
        // 합계계산
        $this->db->strField = '
            IFNULL(SUM(IF(userHandleMode=\'b\', 1, 0)), 0) AS backAll,
            IFNULL(SUM(IF(userHandleFl=\'r\' AND userHandleMode=\'b\', 1, 0)), 0) AS backRequest,
            IFNULL(SUM(IF(userHandleFl!=\'r\' AND userHandleMode=\'b\', 1, 0)), 0) AS backAccept,
            IFNULL(SUM(IF(userHandleMode=\'r\', 1, 0)), 0) AS refundAll,
            IFNULL(SUM(IF(userHandleFl=\'r\' AND userHandleMode=\'r\', 1, 0)), 0) AS refundRequest,
            IFNULL(SUM(IF(userHandleFl!=\'r\' AND userHandleMode=\'r\', 1, 0)), 0) AS refundAccept,
            IFNULL(SUM(IF(userHandleMode=\'e\', 1, 0)), 0) AS exchangeAll,
            IFNULL(SUM(IF(userHandleFl=\'r\' AND userHandleMode=\'e\', 1, 0)), 0) AS exchangeRequest,
            IFNULL(SUM(IF(userHandleFl!=\'r\' AND userHandleMode=\'e\', 1, 0)), 0) AS exchangeAccept
        ';
        $query = $this->db->query_complete();
        $strSQL = 'SELECT ' . array_shift($query) . ' FROM ' . DB_ORDER_USER_HANDLE . implode(' ', $query);
        $getData = $this->db->query_fetch($strSQL, $arrBind, false);

        // 데이타 출력
        if (count($getData) > 0) {
            return gd_htmlspecialchars_stripslashes($getData);
        } else {
            return false;
        }
    }

    /**
     * 주문 삭제
     *
     * @param string $arrData 주문 정보
     */
    public function deleteOrderList($arrData)
    {
        foreach ($arrData['statusCheck'] as $statusMode => $val) {
            foreach ($val as $orderNo) {
                // 주문 상태 변경 처리
                $this->deleteOrder($orderNo);
            }
        }
    }

    /**
     * 주문 삭제
     * 주문취소, 주문실패의 경우만 주문을 삭제할 수 있다.
     *
     * @param string $goodsNo 주문 번호
     */
    public function deleteOrder($orderNo)
    {
        // 삭제 가능한지 여부 파악
        $isDeleted = true;
        $goodsData = $this->getOrderGoods($orderNo);
        foreach ($goodsData as $key => $val) {
            if (!in_array(substr($val['orderStatus'], 0, 1), $this->statusDeleteCd)) {
                $isDeleted = false;
                break;
            }
        }

        // 삭제가능한 주문상태인 경우 삭제
        // 주문 관련 테이블 삭제
        if ($isDeleted) {
            $arrOrderTable[] = DB_ORDER; // 주문서 기본 정보
            $arrOrderTable[] = DB_ORDER_INFO; // 주문 주소 정보 (주문자, 쉬취인)
            $arrOrderTable[] = DB_ORDER_GOODS; // 주문 상품 정보
            $arrOrderTable[] = DB_ORDER_ADD_GOODS; // 추가 주문 상품 정보
            $arrOrderTable[] = DB_ORDER_DELIVERY; // 주문 배송 정보
            $arrOrderTable[] = DB_ORDER_TAX; // 세금 계산서 정보
            $arrOrderTable[] = DB_ORDER_CASH_RECEIPT; // 현금 영수증 정보
            $arrOrderTable[] = DB_ORDER_COUPON; // 주문 쿠폰 정보
            $arrOrderTable[] = DB_ORDER_GIFT; // 주문 사은품 정보
            $arrOrderTable[] = DB_ORDER_HANDLE; // 주문 환불/반품/교환 정보
            $arrOrderTable[] = DB_ORDER_USER_HANDLE; // 사용자 주문 환불/반품/교환 정보
            $arrOrderTable[] = DB_ORDER_CONSULT; // 주문상담 내역
            $arrOrderTable[] = DB_LOG_ORDER; // 로그 - 주문

            $this->db->bind_param_push($this->arrBind, 's', $orderNo);
            foreach ($arrOrderTable as $orderTableNm) {
                $this->db->set_delete_db($orderTableNm, 'orderNo = ?', $this->arrBind);
            }
            unset($this->arrBind);

            // 전체 로그를 저장합니다.
            LogHandler::wholeLog('order', null, 'delete', $orderNo, $orderNo);
        } else {
            throw new Exception(__('해당 주문상태로 주문을 삭제하실 수 없습니다.'));
        }
    }

    /**
     * 주문 상태 수정 전 처리 및 서로 변경할 수 없는 조건에서의 처리
     * 상태변경이 안되는 경우 $this->statusStandardCode 멤버변수 확인 할 것
     *
     * @param string $orderNo 주문 번호
     * @param array $goodsData 주문 상품 정보
     * @param string $statusMode 현재 주문 상태코드 (한자리)
     * @param string $changeStatus 변경할 주문 상태 코드
     * @param bool|string $reason 변경사유 ( 기본은 false 이며, 주문 리스트에서 처리시)
     * @param boolean $bundleFl 특정 처리에서의 처리 모드
     * @param string $mode 처리 모드(입금대기리스트 구분 필요 시)
     * @param string $useVisit 방문수령여부
     *
     * @throws Exception
     */
    public function updateStatusPreprocess($orderNo, $goodsData, $statusMode, $changeStatus, $reason = false, $bundleFl = false, $mode = null, $useVisit = null, $autoProcess = false)
    {
        if (($result = $this->updateStatusUnconditionalPreprocess($orderNo, $goodsData, $statusMode, $changeStatus, $reason, $bundleFl, $mode, $useVisit, $autoProcess))!== true) {
            if($result['errorMsg']){
                $message = sprintf(__('"주문상태변경 실패하였습니다.(%s")'), $result['errorMsg']);
            }
            else {
                $message = sprintf(__('"%s"로 변경이 불가능한 주문상태 입니다.'), $this->getOrderStatusAdmin($changeStatus));
            }
            \Logger::channel('naverPay')->info('updateStatusUnconditionalPreprocess 실패', [__METHOD__, $statusMode,$changeStatus]);
            // 반품/교환/환불인 경우만 출력되는 메시지
            if (in_array(substr($changeStatus, 0, 1), ['r', 'b', 'e', 'z']) && $this->channel != 'naverpay') {
                $message .= __('<br>주문상태 확인 후 [주문 상세정보 > 클래임 접수] 이용 별도 처리하세요.');
            }

            throw new LayerNotReloadException($message);
        }
        if ($statusMode == 'o') {
            try {
                /** @var \Bundle\Component\Sms\SmsLog $smsLog */
                $smsLog = App::load('\\Component\\Sms\\SmsLog');
                $smsLog->cancelReserveSmsByOrderStatusChange($orderNo);
            } catch (Exception $e) {
                \Logger::error(__METHOD__ . ', 이미 발송된 예약SMS를 취소한 경우 응답 코드가 400이 나옵니다. orderNo[' . $orderNo . ']' . $e->getFile() . '[' . $e->getLine() . '], ' . $e->getMessage());
            }
        }
    }

    /**
     * 주문 상태 수정 전 처리 및 서로 변경할 수 없는 조건에서의 처리
     * 상태변경이 안되는 경우 $this->statusStandardCode 멤버변수 확인 할 것
     *
     * !중요! 해당로직안에는 Exception 절대로 넣지 마시오!
     *
     * @param string $orderNo 주문 번호
     * @param array $goodsData 주문 상품 정보
     * @param string $statusMode 현재 주문 상태코드 (한자리)
     * @param string $changeStatus 변경할 주문 상태 코드
     * @param bool|string $reason 변경사유 ( 기본은 false 이며, 주문 리스트에서 처리시)
     * @param boolean $bundleFl 특정 처리에서의 처리 모드
     * @param string $mode 처리모드(입금대기리스트 구분 필요 시)
     * @param string $useVisit 방문수령여부
     *
     * @return boolean
     * @throws Exception
     */
    public function updateStatusUnconditionalPreprocess($orderNo, $goodsData, $statusMode, $changeStatus, $reason = false, $bundleFl = false, $mode = null, $useVisit = null, $autoProcess = false)
    {
        // 상품 데이타 존재여부 체크
        if (empty($goodsData) === true) {
            return;
        }
        $orderData = $this->getOrderData($orderNo);
        $channel = $orderData['orderChannelFl'];
        // 각 주문에 대한 상태 변경 기본 조건 체크 및 같은 주문상태는 변경처리 안함
        $changeCheck = false;
        if (in_array(substr($changeStatus, 0, 1), $this->statusStandardCode[$statusMode])) {
            // 주문상태별 조건에 따른 변경 가능여부 체크
            foreach ($goodsData as $key => $val) {
                if ($val['orderStatus'] != $changeStatus) {
                    // 기본상태변경 조건이 맞아 true로 전환
                    $changeStatusCheck = true;
                    switch ($statusMode) {
                        case 'o':
                            break;
                        case 'p':
                            break;
                        case 'g':
                            break;
                        case 'd':
                            break;
                        case 's':
                            break;
                        case 'f':
                            break;
                        case 'c':
                            break;
                        case 'e':
                            // 교환완료 -> 배송완료 (변경 X)
                            if ($val['orderStatus'] == 'e5' && $changeStatus == 'd2' && $channel != 'naverpay') {
                                $changeStatusCheck = false;
                            }
                            break;
                        case 'b':
                            // 반품회수완료 -> 배송완료 (변경 X)
                            if ($val['orderStatus'] == 'b4' && $changeStatus == 'd2' && $channel != 'naverpay') {
                                $changeStatusCheck = false;
                            }
                            break;
                        case 'r':

                            break;
                    }
                } else {
                    // 주문상태가 같은 경우 변경 안됨
                    $changeStatusCheck = false;
                    if($channel == 'naverpay'){
                        $changeStatusCheck = true;
                        $bundleData['sameStatusSno'][] = $val['sno'];
                    }
                }

                //교환취소, 교환추가의 경우 같은 스텝이 아닌이상 변동되지 않는다.
                if($channel != 'naverpay') {
                    if (substr($val['orderStatus'], 0, 1) == 'e' && substr($changeStatus, 0, 1) != 'e') {
                        $changeStatusCheck = false;
                    }
                    if (substr($val['orderStatus'], 0, 1) == 'z' && substr($changeStatus, 0, 1) != 'z') {
                        $changeStatusCheck = false;
                    }
                }

                // 환불완료/취소/실패인 경우 무조건 상태변경 금지
                if ($val['orderStatus'] == 'r3' || $val['orderStatus'] == 'c' || $val['orderStatus'] == 'f') {
                    if($channel != 'naverpay') {
                        $changeStatusCheck = false;
                    }
                }

                // 변경 가능한 경우 bundleData로 담는다
                if ($changeStatusCheck == true) {
                    $bundleData['sno'][] = $val['sno'];
                    $bundleData['orderStatus'][] = $val['orderStatus'];
                    $changeCheck = true;
                }

                // sms 개선(환불 금액 sms 전송 시 필요 bundleData에 담음)
                $bundleData['refundCompletePrice'] = $val['refundCompletePrice'];
                $bundleData['smsCnt'] = $val['smsCnt'];
            }
        }

        // $this->statusStandardCode 정의한 변경 가능한 주문상태인 경우만 처리
        if ($changeCheck === true || $channel == 'naverpay') {
            switch ($orderData['orderChannelFl']) {
                case 'payco' ://페이코 주문상태 변경

                    // 처리하려는 주문상품이 환불, 교환처리된 주문상품이라면 통신을 하지 않는다.
                    $handleMode = $this->getOnlyOrderHandleMode($bundleData['sno'][0]);
                    if (in_array(substr($changeStatus, 0, 1), ['r', 'e', 'z']) === false && $handleMode !== 'z') {
                        $payco = \App::load('\\Component\\Payment\\Payco\\Payco');
                        $response = $payco->paycoOrderStatus($orderNo, $bundleData['sno'][0], $changeStatus, $orderData);
                        if ($response->code != '000') {
                            return ['errorMsg'=>$response->msg];
                        }
                        unset($payco, $response);
                    }
                    break;
                case 'naverpay' : //네이버페이 주문상태 변경
                    $naverApi = new NaverPayAPI();
                    foreach ($bundleData['sno'] as $sno) {
                        $result = $naverApi->changeStatus($orderNo, $sno, $changeStatus);
                        if ($result['result'] === false) {
                            \Logger::channel('naverPay')->info('주문상태변경실패', [$orderNo, $sno, $changeStatus]);
                            return ['errorMsg'=>$result['error']];
                        }
                    }

                    if($changeStatus == 'b4') {
                        $orderGodosDatas = $this->getOrderGoods($orderNo,$bundleData['sno']);
                        foreach($orderGodosDatas as $val){
                            $naverApi->request('GetProductOrderInfoList',['ProductOrderIDList'=>$val['apiOrderGoodsNo']]);
                        }
                    }

                    return true;
                    break;
            }

            if (empty($reason) === true) {
                $reason = __('주문 리스트에서');
            }

            // sms 개선(고객 취소요청 시 금액 전달을 위함, 입금대기 리스트 일괄 처리 시 cancelPrice금액 settlePrice로)
            $bundleData['customerCancelPrice'] = $orderData['settlePrice'];
            if($mode == 'combine_status_cancel'){
                $bundleData['cancelPriceC1'] = $orderData['settlePrice'];
            }

            $bundleData['changeStatus'] = $changeStatus;
            $bundleData['reason'] = $reason . ' ' . $this->getOrderStatusAdmin($changeStatus) . __(' 처리');
            if($autoProcess == true){
                if($bundleData['changeStatus'] == 'r1'){
                    $bundleData['reason'] = '고객 자동환불 신청';
                }else if($bundleData['changeStatus'] == 'r3') {
                    $bundleData['reason'] = '일괄 자동환불 처리';
                }
            }
            $functionName = 'statusChangeCode' . strtoupper(substr($changeStatus, 0, 1));

            // 주문 상태에 따른 함수 실행
            $this->$functionName($orderNo, $bundleData, $bundleFl, $useVisit, $autoProcess);

            if ($this->paycoConfig['paycoFl'] == 'y') {
                // 페이코쇼핑 결제데이터 전달
                $payco = \App::load('\\Component\\Payment\\Payco\\Payco');
                $payco->paycoShoppingRequest($orderNo);
            }

            //우체국 택배 연동인 경우 주문상태를 GODOPOST중앙서버가 아닌 관리자 상에서 변경시 GODOPOST연결 삭제
            /*
            if($goodsData['invoiceCompanySno'] =='1' && $goodsData['invoiceNo']) {

            }
            */
            return true;
        } else {
            return false;
        }
    }

    /**
     * 주문 상세에서 주문 접수 처리
     *
     * @param string $orderNo 주문 번호
     */
    public function setStatusChangeOrder($orderNo)
    {
        $this->updateStatusPreprocess($orderNo, $this->getOrderGoods($orderNo), 'p', 'o1', __('주문 상세에서'), true);
    }

    /**
     * 주문 리스트에서 입금 확인 처리
     *
     * @param string $orderNo 주문 번호
     */
    public function setStatusChangePayment($orderNo)
    {
        $this->updateStatusPreprocess($orderNo, $this->getOrderGoods($orderNo), 'o', 'p1', null, true);
    }

    /**
     * 프론트 > 마이페이지 > 결제취소 를 통한 취소 처리
     *
     * @param string $orderNo 주문 번호
     */
    public function setStatusChangeCancel($orderNo)
    {
        $this->updateStatusPreprocess($orderNo, $this->getOrderGoods($orderNo), 'o', 'c4', __('고객요청에 의해'), true);
    }

    /**
     * 프론트 > 마이페이지 > 구매확정 처리
     *
     * @param string $orderNo 주문 번호
     * @param        $orderGoodsNo
     *
     * @throws Exception
     */
    public function setStatusChangeSettle($orderNo, $orderGoodsNo)
    {
        $this->updateStatusPreprocess($orderNo, $this->getOrderGoods($orderNo, $orderGoodsNo), 'd', 's1', __('고객요청에 의해'), true);
    }

    /**
     * 프론트 > 마이페이지 > 수취확인 처리
     *
     * @param string $orderNo 주문 번호
     * @param        $orderGoodsNo
     *
     * @throws Exception
     */
    public function setStatusChangeDeliveryComplete($orderNo, $orderGoodsNo)
    {
        $this->updateStatusPreprocess($orderNo, $this->getOrderGoods($orderNo, $orderGoodsNo), 'd', 'd2', __('고객요청에 의해'), true);
    }

    /**
     * 프론트 > 마이페이지 > 반품/교환/환불신청 처리
     * 주문상태 혹은 주문 handle과는 관련 없으며,
     * 주문상태는 변경안하고 관리자가 승인/거절을 통해서 최종적으로 주문상태가 변경되어야 한다.
     *
     * @param integer $orderNo 주문번호
     * @param mixed $orderGoodsNo 처리를 위한 주문상품
     * @param string $handleMode 처리모드
     * @param array $bundleData 처리테이블에 들어갈 필드 배열
     *
     * @throws Exception
     */
    public function requestUserHandle($orderNo, $orderGoodsNo, $handleMode, $bundleData)
    {
        $goodsData = $this->getOrderGoods($orderNo, null, null, null, null, ['memNo']);
        $filteredGoodsData = [];

        // 배열이 아닌 경우 배열로 만듬
        if (!is_array($orderGoodsNo)) {
            $orderGoodsNo = [$orderGoodsNo];
        }

        // 원 주문상품 리스트와 비교
        foreach ($goodsData as $key => $val) {
            // 이마트 보안취약점 요청사항 (사용자 환불신청시 회원 유효성 검증)
            if ($handleMode == 'r') {
                if ($val['memNo'] != Session::get('member.memNo')) {
                    return 'invalid_order';
                }
            }

            if (in_array($val['sno'], $orderGoodsNo)) {
                $filteredGoodsData[] = $val;
            }
        }

        // 사용자 환불신청 테이블 저장
        $bundleData['orderNo'] = $orderNo;
        $bundleData['userHandleMode'] = $handleMode;
        $goodsCnt = $bundleData['userHandleGoodsCnt'];
        if (empty($bundleData['userRefundAccountNumber']) === false) {
            $bundleData['userRefundAccountNumber'] = \Encryptor::encrypt($bundleData['userRefundAccountNumber']);
        }

        foreach ($filteredGoodsData as $orderGoods) {
            // 처리할 상품별 번호 및 수량
            $bundleData['userHandleGoodsNo'] = $orderGoods['sno'];
            $bundleData['userHandleGoodsCnt'] = $goodsCnt[$orderGoods['sno']];

            // 중복 등록 방지
            $query = "SELECT count(*) as cnt  FROM " . DB_ORDER_USER_HANDLE . " WHERE regDt >= (now()-INTERVAL 30 SECOND) AND userHandleReason = ? AND userHandleDetailReason = ? AND orderNo = ? AND userHandleMode = ? AND userHandleGoodsNo = ? AND userHandleGoodsCnt = ?";
            $this->db->bind_param_push($arrBind, 's', $bundleData['userHandleReason']);
            $this->db->bind_param_push($arrBind, 's', $bundleData['userHandleDetailReason']);
            $this->db->bind_param_push($arrBind, 'i', $bundleData['orderNo']);
            $this->db->bind_param_push($arrBind, 's', $bundleData['userHandleMode']);
            $this->db->bind_param_push($arrBind, 'i', $bundleData['userHandleGoodsNo']);
            $this->db->bind_param_push($arrBind, 'i', $bundleData['userHandleGoodsCnt']);
            $result = $this->db->query_fetch($query, $arrBind, false);

            if($result['cnt']>0) {
                throw new AlertRedirectException(__('등록중 입니다. 잠시만 기다려 주세요'), null, null, 'order_list.php', 'parent');
            }

            // handle 테이블에 입력후 insertId 반환
            if (method_exists($this, 'insertOrderUserHandle') === true) {
                $userHandleSno[$orderGoods['sno']] = $this->insertOrderUserHandle($bundleData);
            } else {
                $compareField = array_keys($bundleData);
                $arrBind = $this->db->get_binding(DBTableField::tableOrderUserHandle(), $bundleData, 'insert', $compareField);
                $this->db->set_insert_db(DB_ORDER_USER_HANDLE, $arrBind['param'], $arrBind['bind'], 'y');
                $userHandleSno[$orderGoods['sno']] = $this->db->insert_id();
                unset($arrBind);
            }

            // 주문 상품테이블에 handleSno 번호를 업데이트 한다.
            $setData['userHandleSno'] = $this->db->insert_id();
            $compareField = array_keys($setData);
            $arrBind = $this->db->get_binding(DBTableField::tableOrderGoods(), $setData, 'update', $compareField);
            $arrWhere = 'orderNo = ? AND sno = ?';
            $this->db->bind_param_push($arrBind['bind'], 's', $orderNo);
            $this->db->bind_param_push($arrBind['bind'], 'i', $orderGoods['sno']);
            $this->db->set_update_db(DB_ORDER_GOODS, $arrBind['param'], $arrWhere, $arrBind['bind']);
            unset($arrBind);
        }

        if ($this->paycoConfig['paycoFl'] == 'y') {
            // 페이코쇼핑 결제데이터 전달
            $payco = \App::load('\\Component\\Payment\\Payco\\Payco');
            $payco->paycoShoppingRequest(Request::post()->get('orderNo'));
        }

        return $userHandleSno;
    }

    /**
     * 30초 이내 등록된 반품/교환/환불신청이 있는지 확인하고 INSERT 실행
     * @param $bundleData
     * @return mixed
     * @throws AlertRedirectException
     */
    protected function insertOrderUserHandle($bundleData)
    {
        $compareField = array_keys($bundleData);
        $arrBind = $this->db->get_binding(DBTableField::tableOrderUserHandle(), $bundleData, 'insert', $compareField);
        $strBind = [];
        foreach ($arrBind['param'] as $_bind) {
            $strBind[] = '?';
        }

        $strSQL = 'INSERT INTO ' . DB_ORDER_USER_HANDLE . '(' . implode(',', $arrBind['param']) . ', regDt)';
        $strSQL .= ' SELECT ' . implode(',', $strBind) . ', now() FROM DUAL ' ;
        $strSQL .= " WHERE (SELECT count(*) as cnt  FROM " . DB_ORDER_USER_HANDLE . " WHERE regDt >= (now()-INTERVAL 30 SECOND) AND userHandleReason = ? AND userHandleDetailReason = ? AND orderNo = ? AND userHandleMode = ? AND userHandleGoodsNo = ? AND userHandleGoodsCnt = ?) = 0 ";
        $this->db->bind_param_push($arrBind['bind'], 's', $bundleData['userHandleReason']);
        $this->db->bind_param_push($arrBind['bind'], 's', $bundleData['userHandleDetailReason']);
        $this->db->bind_param_push($arrBind['bind'], 'i', $bundleData['orderNo']);
        $this->db->bind_param_push($arrBind['bind'], 's', $bundleData['userHandleMode']);
        $this->db->bind_param_push($arrBind['bind'], 'i', $bundleData['userHandleGoodsNo']);
        $this->db->bind_param_push($arrBind['bind'], 'i', $bundleData['userHandleGoodsCnt']);
        $this->db->bind_query($strSQL, $arrBind['bind']);
        $result = $this->db->insert_id();
        if ($result < 1) { //중복글로 인한 저장 실패
            throw new AlertRedirectException(__('등록중 입니다. 잠시만 기다려 주세요'), null, null, 'order_list.php', 'parent');
        }
        return $result;
    }

    /**
     * 사용자 화면의 PG 창에서 사용자 결제 취소 요청
     *
     * @param string $orderNo 주문 번호
     */
    public function setStatusChangePgStop($orderNo)
    {
        $goodsData = $this->getOrderGoods($orderNo);

        if ($goodsData[0]['orderStatus'] === 'f1') {
            // 주문 상태 변경 처리
            $this->updateStatusPreprocess($orderNo, $goodsData, 'f', 'f2', __('고객 결제 중단에 의해 '), true);
        }
    }

    /**
     * 주문 리스트에서 주문취소 상태 일괄 변경 처리
     *
     * @param string $arrData 변경 정보
     *
     * @throws Exception
     */
    public function setCombineStatusCancelList($arrData)
    {
        // 운영자 기능권한 처리 (주문 상태 변경 권한) - 관리자페이지에서만
        $thisCallController = \App::getInstance('ControllerNameResolver')->getControllerRootDirectory();
        $reOrderCalculation = \App::load(\Component\Order\ReOrderCalculation::class);

        if ($thisCallController == 'admin' && Session::get('manager.functionAuthState') == 'check' && Session::get('manager.functionAuth.orderState') != 'y') {
            throw new Exception(__('권한이 없습니다. 권한은 대표운영자에게 문의하시기 바랍니다.'));
        }
        foreach ($arrData['statusCheck'] as $statusMode => $val) {
            foreach ($val as $orderNo) {
                $orderData = $this->getOrderView($orderNo);

                if (substr($orderData['orderStatus'], 0, 1) == 'o') {
                    $param = [];
                    foreach ($orderData['goods'] as $value) {
                        foreach ($value as $val) {
                            foreach ($val as $goodsData) {
                                $param[$goodsData['sno']] = $goodsData['goodsCnt'];
                            }
                        }
                    }
                    $cancelMsg = [
                        'orderStatus' => 'c3',
                        'handleDetailReason' => __('입금대기 리스트에서 취소 처리'),
                    ];
                    try {
                        $this->setAutoCancel($orderNo, $param, $reOrderCalculation, $cancelMsg);
                    } catch (\Throwable $e) {
                        continue;
                    }
                } else {
                    // 주문 상태 변경 처리
                    //입금대기 리스트에서 결제완료 처리 시 입금대기 상태들만 변경되도록
                    if($arrData['mode'] == 'combine_status_cancel') {
                        $tmpOrderGoodsList = $this->getOrderGoods($orderNo);
                        foreach ($tmpOrderGoodsList as $tmpGoodsKey => $tmpGoodsValue) {
                            if (substr($tmpGoodsValue['orderStatus'], 0, 1) != 'o') {
                                unset($tmpOrderGoodsList[$tmpGoodsKey]);
                            }
                        }
                    }
                    $this->updateStatusPreprocess($orderNo, $tmpOrderGoodsList, 'o', 'c3', __('입금대기 리스트에서 '), true, $arrData['mode']);
                }
            }
        }
    }

    /**
     * PG 결제 확인 처리
     *
     * @param string $orderNo 주문 번호
     * @param string $orderStatus 처리할 주문 상태
     */
    public function setStatusChangePg($orderNo, $orderStatus)
    {
        $this->updateStatusPreprocess($orderNo, $this->getOrderGoods($orderNo), 'f', $orderStatus, __('PG 결제 완료'), true);
    }

    /**
     * PG 가상계좌 입금 처리
     *
     * @param string $orderNo 주문 번호
     * @param array $getPg 가상계좌 결과 정보
     * @param array $getPgCash 현금영수증 결과 정보
     *
     * @return bool
     */
    public function setStatusChangePgVbank($orderNo, $getPg, $getPgCash = null)
    {
        // 주문 정보 및 관리자 메모 수정
        $arrData['receiptFl'] = $getPg['receiptFl'];
        $arrData['pgAppNo'] = $getPg['pgAppNo'];
        $strLogParam = 'orderPGLog = concat(orderPGLog,?)';

        // 업데이트에 필요한 필드
        $compareField = array_keys($arrData);
        $arrBind = $this->db->get_binding(DBTableField::tableOrder(), $arrData, 'update', $compareField);

        // 로그 설정
        $arrBind['param'][] = $strLogParam;
        $this->db->bind_param_push($arrBind['bind'], 's', $getPg['orderPGLog']);

        // 주문 업데이트
        $this->db->bind_param_push($arrBind['bind'], 's', $orderNo);
        $this->db->set_update_db(DB_ORDER, $arrBind['param'], 'orderNo = ?', $arrBind['bind']);
        unset($arrBind);

        // 현금영수증 저장
        if ($getPg['receiptFl'] == 'r' && empty($getPgCash) === false) {
            // --- 현금영수증 모듈 실행
            $cashReceipt = \App::load('\\Component\\Payment\\CashReceipt');
            $cashReceipt->savePgCashReceiptData($orderNo, $getPgCash);
        }

        // 주문 상태 변경 처리
        $this->updateStatusPreprocess($orderNo, $this->getOrderGoods($orderNo), 'o', 'p1', __('PG 가상계좌 입금통보'), true);

        return true;
    }

    /**
     * PG 가상계좌 입금 통보 결과 처리
     *
     * @param string $orderNo 주문 번호
     * @param array $getPg 가상계좌 결과 정보
     *
     * @return bool
     */
    public function setPgVbankUpdate($orderNo, $getPg)
    {
        // 주문 정보 및 관리자 메모 수정
        $arrExclude = ['orderPGLog', 'orderAdminLog'];
        $arrData = [];
        foreach ($getPg as $pKey => $pVal) {
            if (in_array($pKey, $arrExclude) === false) {
                $arrData[$pKey] = $pVal;
            }
        }
        $strPgLogParam = 'orderPGLog = concat(orderPGLog,?)';
        $strAdminLogParam = 'orderAdminLog = concat(orderAdminLog,?)';

        // 업데이트에 필요한 필드
        $compareField = array_keys($arrData);
        $arrBind = $this->db->get_binding(DBTableField::tableOrder(), $arrData, 'update', $compareField);

        // 로그 설정
        $arrBind['param'][] = $strPgLogParam;
        $this->db->bind_param_push($arrBind['bind'], 's', $getPg['orderPGLog']);
        if (empty($getPg['orderAdminLog']) === false) {
            $arrBind['param'][] = $strAdminLogParam;
            $this->db->bind_param_push($arrBind['bind'], 's', $getPg['orderAdminLog']);
        }

        // 주문 업데이트
        $this->db->bind_param_push($arrBind['bind'], 's', $orderNo);
        $this->db->set_update_db(DB_ORDER, $arrBind['param'], 'orderNo = ?', $arrBind['bind']);
        unset($arrBind);

        return true;
    }

    /**
     * 주문 리스트에서 주문 상태 일괄 변경 처리
     * 리스트의 주문상태가 다를 수 있기 때문에 건별로 처리 한다.
     *
     * @param string $arrData 변경 정보
     */
    public function requestStatusChangeList($arrData)
    {
        $tmpData = [];
        $tmpData['changeStatus'] = $arrData['changeStatus'];
        foreach ($arrData['statusCheck'] as $statusMode => $val) {
            $tmpData['statusCheck'][$statusMode] = $val;
            $tmpData['escrowCheck'][$statusMode] = $arrData['escrowCheck'][$statusMode];
            $tmpData['orderStatus'][$statusMode] = $arrData['orderStatus'][$statusMode];
            $tmpData['beforeStatus'][$statusMode] = $arrData['beforeStatus'][$statusMode];
        }

        $this->requestStatusChange($tmpData);
    }

    /**
     * 주문 리스트에서 주문 상태 일괄 변경 처리 - 주문별(상품준비중 리스트)
     *
     * @param string $arrData 변경 정보
     */
    public function requestStatusChangeListOrderG($arrData)
    {
        $updateData = [];

        $tmpOrderNoArr = $orderNoArr = [];
        if(count($arrData['statusCheck']) > 0){
            foreach($arrData['statusCheck'] as $statusKey => $valueArr){
                if(count($valueArr) > 0){
                    foreach($valueArr as $key => $value){
                        $tmpOrderNoArr[] = $value;
                    }
                }
            }
        }
        $orderNoArr = array_values(array_unique(array_filter($tmpOrderNoArr)));
        if(count($orderNoArr) > 0){
            foreach($orderNoArr as $oKey => $orderNo){
                $orderGoodsData = [];
                $orderGoodsData = $this->getOrderGoodsData($orderNo);
                foreach ($orderGoodsData as $scmNo => $dataVal) {
                    if (Manager::isProvider() && $scmNo != Session::get('manager.scmNo')) {
                        continue;
                    }
                    foreach ($dataVal as $goodsData) {
                        $statusMode = substr($goodsData['orderStatus'], 0, 1);
                        if($statusMode !== 'g'){
                            continue;
                        }
                        $updateData['statusCheck']['g'][] = $orderNo . INT_DIVISION . $goodsData['sno'];
                    }
                }
            }
        }
        $tmpData = [];
        $tmpData['changeStatus'] = $arrData['changeStatus'];
        foreach ($updateData['statusCheck'] as $statusMode => $val) {
            $tmpData['statusCheck'][$statusMode] = $val;
        }

        $this->requestStatusChange($tmpData);
    }

    /**
     * 주문상세의 주문상품 상태 일괄 변경 처리
     * 주문 -> 반품/교환/환불로 넘어가는 부분은 주문상세와 사용자 반품/교환/환불신청페이지의 승인시에만 가능함으로
     * 본 메소드에서는 제외한다.
     *
     * @param $arrData
     *
     * @throws Exception
     */
    public function requestStatusChange($arrData)
    {
        // goods 주문상품 필드 갯수 차감을 위해 goods 호출
        $goods = \App::load(\Component\Goods\Goods::class);

        // 운영자 기능권한 처리 (주문 상태 변경 권한) - 관리자페이지에서만
        $thisCallController = \App::getInstance('ControllerNameResolver')->getControllerRootDirectory();
        if ($thisCallController == 'admin' && Session::get('manager.functionAuthState') == 'check' && Session::get('manager.functionAuth.orderState') != 'y') {
            throw new Exception(__('권한이 없습니다. 권한은 대표운영자에게 문의하시기 바랍니다.'));
        }

        // 주문 정보 확인
        if (empty($arrData['statusCheck']) === true) {
            throw new Exception(__('[리스트] 주문 정보가 존재하지 않습니다.'));
        }

        // 체크된 상품의 값을 주문번호와 상품번호로 분리
        foreach ($arrData['statusCheck'] as $statusMode => $sVal) {
            foreach ($sVal as $val) {
                $tmpArr = explode(INT_DIVISION, $val);
                if (isset($tmpArr[1]) === true) {
                    $statusCode[$statusMode][$tmpArr[0]][] = $tmpArr[1];
                } else {
                    $statusCode[$statusMode][$tmpArr[0]][] = null;
                }
            }
        }

        // 유효성 검사 후 처리
        if (empty($statusCode) === false) {
            $allCount = 0;
            $checkModifiedCount = 0;
            foreach ($statusCode as $statusMode => $orderNos) {
                foreach ($orderNos as $orderNo => $arrGoodsNo) {
                    // 주문상품별이 아닌 주문별로 일괄 처리가 필요한 경우 (ex. 주문접수, 주문취소, 결제실패)
                    $arrGoodsNo = ArrayUtils::removeEmpty($arrGoodsNo);
                    if (empty($arrGoodsNo) === true) {
                        $arrGoodsNo = null;
                    }

                    // 주문 상태, 실패일경우 현재 상태 수정 처리, 및 취소에서 주문 상태 처리시에 현재 상태 수정 처리 가능하게
                    if ($statusMode == 'p' || $statusMode == 'o' || $statusMode == 'f' || ($statusMode == 'c' && substr($arrData['changeStatus'], 0, 1) == 'o')) {
                        $bundleFl = true;
                    } else {
                        $bundleFl = false;
                    }

                    // 주문 상태 변경 처리 (반품/교환/환불의 경우 정상 주문으로 복구시 별도 처리)
                    if($this->getChannel() == 'naverpay'){
                        $this->updateStatusPreprocess($orderNo, $this->getOrderGoods($orderNo, $arrGoodsNo, null, ['orderStatus']), $statusMode, $arrData['changeStatus'], '리스트에서', $bundleFl);
                    }
                    else {
                        if ($statusMode == 'r' || $statusMode == 'e' || $statusMode == 'z' || $statusMode == 'b') {
                            // setHandleRollback용 데이터 구성 후 던짐
                            $getData['orderNo'] = $orderNo;
                            $getData['changeStatus'] = $arrData['changeStatus'];
                            foreach ($arrGoodsNo as $orderGoodsNo) {
                                $goodsData = $this->getOrderGoodsData($orderNo, $orderGoodsNo, null, null, null, false, null, $statusMode);
                                $getData['bundle']['statusCheck'][$orderGoodsNo] = $goodsData['sno'];
                                $getData['bundle']['beforeStatus'][$orderGoodsNo] = $goodsData['beforeStatus'];
                                $getData['bundle']['orderStatus'][$orderGoodsNo] = $goodsData['orderStatus'];
                                $getData['bundle']['handleSno'][$orderGoodsNo] = $goodsData['handleSno'];

                                // 교환 추가 입금처리 시 상품 테이블 주문상품 갯수 갱신 처리 es_goods.orderGoodsCnt
                                if($statusMode == 'o' && $goodsData['paymentDt'] == "0000-00-00 00:00:00") {
                                    $goods->setOrderGoodsCount($goodsData['sno'], false, $goodsData['goodsNo'], $goodsData['goodsCnt']);
                                }
                                // 교환 취소 완료 시 상품 테이블 주문상품 갯수 갱신 처리 es_goods.orderGoodsCnt
                                if($getData['changeStatus'] == 'e5' && $goodsData['handleDt'] == null && $goodsData['handleCompleteFl'] =='n' ) {
                                    // 핸들 데이터 로드
                                    $orderHandleGroupOrderGoods = $this->getOrderExchangeGoodsCntSet($orderNo, $goodsData['sno'], $getData['handleGroupCd']);
                                    if($goodsData['goodsNo'] != $orderHandleGroupOrderGoods[0]['goodsNo']) { // 타상품
                                        $goods->setOrderGoodsCount($goodsData['sno'], true, $goodsData['goodsNo'], $goodsData['goodsCnt']);
                                    }
                                }
                                // 상품 교환 완료 시 상품 테이블 주문상품 갯수 갱신 처리 es_goods.orderGoodsCnt
                                else if($getData['changeStatus'] == 'z5' && $goodsData['handleDt'] == null && $goodsData['handleCompleteFl'] =='n' ) {
                                    // 핸들 데이터 로드
                                    $orderHandleGroupOrderGoods = $this->getOrderExchangeGoodsCntSet($orderNo, $goodsData['sno'], $getData['handleGroupCd']);
                                    if($goodsData['goodsNo'] != $orderHandleGroupOrderGoods[0]['goodsNo']) { // 타상품
                                        $goods->setOrderGoodsCount($goodsData['sno'], false, $goodsData['goodsNo'], $goodsData['goodsCnt']);
                                    }
                                }
                            }
                            $this->setHandleRollback($getData);
                            unset($getData);
                        } else {
                            // 주문상세보기 및 리스트에서 주문상태 일괄 변경시 환불/반품/교환으로 변경안되도록 처리 필요
                            if (!in_array(substr($arrData['changeStatus'], 0, 1), ['r', 'e', 'z', 'b', 'c'])) {
                                $changeOrderGoodsData = [];
                                $changeOrderGoodsData = $this->getOrderGoods($orderNo, $arrGoodsNo, null, ['orderStatus']);

                                //입금대기 주문번호별 리스트에서 입금, 결제완료처리 등의 상태변경시 클레임처리된 주문상품건들의 상태변경을 막기 위함.
                                if($statusMode === 'o' && $arrGoodsNo === null && (substr($arrData['changeStatus'], 0, 1) == 'o' || substr($arrData['changeStatus'], 0, 1) == 'p')){
                                    if(count($changeOrderGoodsData) > 0){
                                        foreach($changeOrderGoodsData as $key => $value){
                                            if(in_array(substr($value['orderStatus'], 0, 1), ['r', 'e', 'z', 'b', 'c'])){
                                                unset($changeOrderGoodsData[$key]);
                                            }
                                        }
                                        $changeOrderGoodsData = array_values($changeOrderGoodsData);
                                    }
                                }

                                //입금대기 리스트에서 결제완료 처리 시 입금대기 상태들만 변경되도록
                                $referer = explode('?', \Request::server()->get('HTTP_REFERER'));
                                $referer = explode('/', $referer[0]);
                                $referer = $referer[count($referer)-1];
                                if($arrData['changeStatus'] == 'p1' && $referer == 'order_list_order.php'){
                                    foreach($changeOrderGoodsData as $goodsKey => $goodsValue){
                                        if(substr($goodsValue['orderStatus'], 0, 1) != 'o'){
                                            unset($changeOrderGoodsData[$goodsKey]);
                                        }
                                    }
                                }

                                $this->updateStatusPreprocess($orderNo, $changeOrderGoodsData, $statusMode, $arrData['changeStatus'], '리스트에서', $bundleFl, null, $arrData['useVisit']);
                            } else {
                                $checkModifiedCount++;
                            }
                        }

                        if ($this->paycoConfig['paycoFl'] == 'y') {
                            // 페이코쇼핑 결제데이터 전달
                            $payco = \App::load('\\Component\\Payment\\Payco\\Payco');
                            $payco->paycoShoppingRequest($orderNo);
                        }
                    }

                    $allCount++;
                }
            }

            if ($checkModifiedCount > 0) {
                //@todo 변경할 수 없는 상태에 대한 처리
                throw new Exception(sprintf(__('총 %d개 중 %d를 처리 완료하였습니다.'), $allCount, $checkModifiedCount));
            }
        }
    }

    /**
     * 정책에서 설정된 자동 주문 취소일 기준에 따라 자동으로 삭제 처리 한다.
     * 관리자 > 메인에 접속시 요청 된다.
     *
     * @throws Exception
     */
    public function cancelOrderAutomatically()
    {
        // 무조건 실행되도록 제한시간 해제
        set_time_limit(RUN_TIME_LIMIT);

        // 자동 취소일
        $autoCancel = $this->statusPolicy['autoCancel'];

        // 주문 상태 주문만 추출, (결제 시도의 주문은 제외)
        if ($autoCancel > 0) {
            $targetOrderGoodsNoArr = $orderGoodsCnt = [];
            $reOrderCalculation = App::load(\Component\Order\ReOrderCalculation::class);

            $strSQL = 'SELECT orderNo, LEFT(orderStatus,1) as orderStatus, goodsCnt, sno FROM ' . DB_ORDER_GOODS . '
            WHERE orderNo <> \'\' AND date_format(regDt, \'%Y%m%d\') < \'' . date('Ymd', strtotime('-' . $autoCancel . ' day')) . '000000\'
            AND LEFT(orderStatus, 1) = \'o\'';
            $getData = $this->db->query_fetch($strSQL);
            foreach ($getData as $data) {
                $strOrderSQL = 'SELECT orderChannelFl FROM ' . DB_ORDER . ' WHERE orderNo = \''.$data['orderNo'].'\' LIMIT 1';
                $getOrderData = $this->db->query_fetch($strOrderSQL, [], false);
                if($getOrderData['orderChannelFl'] === 'etc'){
                    continue;
                }
                if($data['orderStatus'] !== 'o'){
                    continue;
                }
                $targetOrderGoodsNoArr[$data['orderNo']][$data['sno']] = $data['goodsCnt'];
                $targetOrderGoodsNo[$data['orderNo']][] = $data['sno'];
                //$orderGoodsCnt[$data['sno']] = $data['goodsCnt'];
            }

            if(count($targetOrderGoodsNoArr) > 0){
                $naverApi = new NaverPayAPI();
                $payco = \App::load('\\Component\\Payment\\Payco\\Payco');
                $getData = $strSQL = null;

                $this->changeStatusAuto = true;

                foreach($targetOrderGoodsNoArr as $orderNo => $orderGoodsNoArr){
                    $orderData = $this->getOrderView($orderNo);
                    if (substr($orderData['orderStatus'], 0, 1) == 'o') {
                        $cancelMsg = [
                            'orderStatus' => 'c1',
                            'handleDetailReason' => sprintf(__('주문 후 %s일 이상되어 취소 처리'), $autoCancel),
                        ];
                        try {
                            switch ($orderData['orderChannelFl']) {
                                case 'naverpay':
                                    foreach ($orderGoodsNoArr as $sno => $val) {
                                        $result = $naverApi->changeStatus($orderNo, $sno, 'c1');
                                        if ($result['result'] === false) {
                                            \Logger::channel('naverPay')->info('주문상태변경실패', [$orderNo, $sno, 'c1', $result]);
                                            throw new Exception($result['error']);
                                        }
                                    }
                                    break;
                                case 'payco':
                                    $sno = key($orderGoodsNoArr);
                                    $response = $payco->paycoOrderStatus($orderNo, $sno, 'c1', $orderData);
                                    if ($response->code != '000') {
                                        return ['errorMsg'=>$response->msg];
                                    }
                                    break;
                            }
                            if ($orderData['orderChannelFl'] == 'naverpay') {
                                foreach ($orderGoodsNoArr as $sno => $val) {
                                    $result = $naverApi->changeStatus($orderNo, $sno, 'c1');
                                    if ($result['result'] === false) {
                                        \Logger::channel('naverPay')->info('주문상태변경실패', [$orderNo, $sno, 'c1', $result]);
                                        throw new Exception($result['error']);
                                    }
                                }
                            }
                            $this->setAutoCancel($orderNo, $orderGoodsNoArr, $reOrderCalculation, $cancelMsg);
                        } catch (\Throwable $e) {
                            continue;
                        }
                    } else {
                        if(count($targetOrderGoodsNo[$orderNo]) > 0){
                            // 원본정보 origin 저장하기
                            $claimStatus = 'c';
                            $count = $reOrderCalculation->getOrderOriginalCount($orderNo, $claimStatus);
                            if ($count < 1) {
                                //이전 취소건 존재하지 않을 시 현재 주문정보 백업
                                $return = $reOrderCalculation->setBackupOrderOriginalData($orderNo, $claimStatus, true, true);
                            }

                            $this->updateStatusUnconditionalPreprocess($orderNo, $this->getOrderGoods($orderNo, $targetOrderGoodsNo[$orderNo]), 'o', 'c1', sprintf(__('주문 후 %s일 이상되어 '), $autoCancel), true);
                        }
                    }


                }
                $targetOrderGoodsNoArr = null;
            }
        }
    }

    public function setAutoCancel($orderNo, $orderGoodsNo, $reOrderCalculation, $cancelMsg)
    {


        $cancelData = $reOrderCalculation->getSelectOrderGoodsCancelData($orderNo, $orderGoodsNo);

        foreach ($cancelData['totalCancelDeliveryPrice'] as $deliverySno => $val) {
            $requestData['totalCancelDeliveryPrice'][$deliverySno] = (int)gd_isset($cancelData['totalCancelDeliveryPrice'][$deliverySno], 0);
            $requestData['totalCancelAreaDeliveryPrice'][$deliverySno] = (int)gd_isset($cancelData['totalCancelAreaDeliveryPrice'][$deliverySno], 0);
            $requestData['totalCancelOverseaDeliveryPrice'][$deliverySno] = (int)gd_isset($cancelData['totalCancelOverseaDeliveryPrice'][$deliverySno], 0);
        }

        $cancel = [
            'orderNo' => $orderNo,
            'cancelPriceBySmsSend' => (int)gd_isset($cancelData['settlePrice'], 0),
            'orderGoods' => $orderGoodsNo,
        ];

        $cancelPrice = [
            'settle' => 0,
            'orderCouponDcCancel' => (int)gd_isset($cancelData['totalCancelOrderCouponDcPrice'], 0),
            'useDepositCancel' => (int)gd_isset($cancelData['totalCancelDepositPrice'], 0),
            'useMileageCancel' => (int)gd_isset($cancelData['totalCancelMileagePrice'], 0),
            'deliveryCancel' => $requestData['totalCancelDeliveryPrice'],
            'areaDeliveryCancel' => $requestData['totalCancelAreaDeliveryPrice'],
            'overseaDeliveryCancel' => $requestData['totalCancelOverseaDeliveryPrice'],
            'deliveryCouponDcCancel' => (int)gd_isset($cancelData['totalCancelDeliveryCouponDcPrice'], 0),
            'deliveryMemberDcCancel' => (int)gd_isset($cancelData['totalCancelDeliveryMemberDcPrice'], 0),
            'deliveryMemberDcCancelFl' => 'a',
        ];

        $return = [
            'stockFl' => 'n',
            'couponFl' => 'n',
            'giftFl' => 'y',
        ];
        if ($this->couponConfig['couponAutoRecoverType'] == 'y') {
            $couponData = $reOrderCalculation->getOrderCouponData($orderNo);
            if (empty($couponData) === false) {
                $return['couponFl'] = 'y';
                foreach ($couponData as $couponNo) {
                    $return['coupon'][] = $couponNo['memberCouponNo'];
                }
            }
        }
        $currentStatusPolicy = $this->getOrderCurrentStatusPolicy($orderNo);
        // 재고 복원 체크
        if (in_array('c1', $currentStatusPolicy['srestore'])) {
            $return['stockFl'] = 'y';
        }

        try {
            $return = \DB::transaction(function () use ($reOrderCalculation, $cancel, $cancelMsg, $cancelPrice, $return, $orderNo) {
                $response = $reOrderCalculation->setCancelOrderGoods($cancel, $cancelMsg, $cancelPrice, $return, false);

                if ($return['couponFl'] == 'y') {
                    $this->setMinusCouponRestore($orderNo);// 사용한 쿠폰 복원
                    $this->setPlusCouponRestore($orderNo);// 적립된 쿠폰 복원
                }

                return $response;
            });
        } catch (Exception $e) {
            throw new Exception($e->getMessage());
        }
    }

    /**
     * 정책에서 설정된 자동 배송완료일 기준에 따라 자동으로 주문상태를 변경 처리 한다.
     * 관리자 > 메인에 접속시 요청 된다.
     *
     * @throws Exception
     */
    public function deliverCompleteOrderAutomatically()
    {
        //@formatter:off
        $result = ['success' => ['cnt' => 0, 'orderGoods' => []], 'fail' => ['cnt' => 0, 'orderGoods' => []]];
        //@formatter:on
        $logger = \App::getInstance('logger');
        // 자동배송완료
        $orderBasic = gd_policy('order.basic');
        $autoDeliveryComplete = $orderBasic['autoDeliveryCompleteDay'];

        // 자동배송 사용설정으로 되어있는 경우
        if ($orderBasic['autoDeliveryCompleteFl'] === 'y') {
            // 기간이 지난 배송중 상태의 주문만 추출
            if ($autoDeliveryComplete > 0) {
                $strSQL = 'SELECT sno, deliveryDt, orderNo, LEFT(orderStatus,1) as orderStatus FROM ' . DB_ORDER_GOODS . '
            WHERE deliveryDt < \'' . date('Y-m-d 00:00:00', strtotime('-' . ($autoDeliveryComplete - 1) . ' day')) . '\'
            AND deliveryDt != \'0000-00-00 00:00:00\'
            AND orderStatus = \'d1\'';
                $resultSet = $this->db->query($strSQL);

                while ($getData = $this->db->fetch($resultSet)) {
                    $strOrderSQL = 'SELECT orderChannelFl FROM ' . DB_ORDER . ' WHERE orderNo = \''.$getData['orderNo'].'\' LIMIT 1';
                    $getOrderData = $this->db->query_fetch($strOrderSQL, [], false);
                    if($getOrderData['orderChannelFl'] === 'etc'){
                        continue;
                    }

                    // 주문 상태 변경 처리
                    if ($getData['orderStatus'] == 'd') {
                        $sendFl = true;
                    } else {
                        $sendFl = false;
                    }
                    try {
                        $preProcess = $this->updateStatusUnconditionalPreprocess($getData['orderNo'], $this->getOrderGoods($getData['orderNo'], $getData['sno']), $getData['orderStatus'], 'd2', sprintf(__('배송중 후 %s일 이상되어 '), $autoDeliveryComplete), $sendFl);
                        if ($preProcess === true) {
                            $result['success']['cnt']++;
                            $result['success']['orderGoods'][] = [
                                'orderNo' => $getData['orderNo'],
                                'orderGoodsSno' => $getData['sno'],
                            ];
                        } elseif ($preProcess === false) {
                            $result['fail']['cnt']++;
                            $result['fail']['orderGoods'][] = [
                                'orderNo' => $getData['orderNo'],
                                'orderGoodsSno' => $getData['sno'],
                            ];
                        } else {
                            $result['fail']['cnt']++;
                            $result['fail']['orderGoods'][] = [
                                'orderNo' => $getData['orderNo'],
                                'orderGoodsSno' => $getData['sno'],
                                'message' => is_array($preProcess) ? serialize($preProcess) : $preProcess,
                            ];
                        }
                    } catch (\Throwable $e) {
                        $logger->error(sprintf('%s, %s[%s], %s',__METHOD__, $e->getFile(), $e->getLine(), $e->getMessage()), $e->getTrace());
                        $result['fail']['cnt']++;
                        $result['fail']['orderGoods'][] = [
                            'orderNo' => $getData['orderNo'],
                            'orderGoodsSno' => $getData['sno'],
                            'message' => $e->getMessage(),
                        ];
                        continue;
                    }
                }
            } else {
                $logger->info('empty auto delivery complete day');
            }
        } else {
            $logger->info('disable auto delivery complete');
        }

        return $result;
    }

    /**
     * 정책에서 설정된 자동 구매확정일 기준에 따라 자동으로 주문상태를 변경 처리 한다.
     * 관리자 > 메인에 접속시 요청 된다.
     *
     * @throws Exception
     */
    public function settleOrderAutomatically()
    {
        // 자동구매확정
        $orderBasic = gd_policy('order.basic');
        $autoSettle = $orderBasic['autoOrderConfirmDay'];

        // 기간이 지난 배송완료 상태의 주문과 자동구매확정 사용함으로 설정된 경우 추출
        if ($autoSettle > 0 && $orderBasic['autoOrderConfirmFl'] === 'y') {
            $strSQL = 'SELECT sno, orderNo, LEFT(orderStatus,1) as orderStatus FROM ' . DB_ORDER_GOODS . '
            WHERE deliveryCompleteDt < \'' . date('Y-m-d 00:00:00', strtotime('-' . ($autoSettle - 1) . ' day')) . '\'
            AND deliveryCompleteDt != \'0000-00-00 00:00:00\'
            AND orderStatus = \'d2\'';
            $result = $this->db->query($strSQL);

            $this->changeStatusAuto = true;
            while ($getData = $this->db->fetch($result)) {
                $strOrderSQL = 'SELECT orderChannelFl FROM ' . DB_ORDER . ' WHERE orderNo = \''.$getData['orderNo'].'\' LIMIT 1';
                $getOrderData = $this->db->query_fetch($strOrderSQL, [], false);
                if($getOrderData['orderChannelFl'] === 'etc'){
                    continue;
                }

                // 주문 상태 변경 처리
                if ($getData['orderStatus'] == 'd') {
                    $sendFl = true;
                } else {
                    $sendFl = false;
                }
                $this->updateStatusUnconditionalPreprocess($getData['orderNo'], $this->getOrderGoods($getData['orderNo'], $getData['sno']), $getData['orderStatus'], 's1', sprintf(__('배송완료 후 %s일 이상되어 '), $autoSettle), $sendFl);
            }
        }
    }

    /**
     * 주문 쿠폰 정보
     *
     * @param integer $orderNo    주문번호
     * @param array   $orderCd    상품주문번호 (null이면 주문쿠폰을 포함해 가져옴)
     * @param boolean $isRefunded 복원쿠폰 여부 (false인 경우 쿠폰 전체가 출력)
     *
     * @return array 해당 주문 쿠폰 정보
     */
    public function getOrderCoupon($orderNo, $orderCd = null, $isRefunded = false)
    {
        $arrBind = [];
        $arrWhere[] = 'oc.orderNo = ?';
        $this->db->bind_param_push($arrBind, 's', $orderNo);

        // join 문
        $join[] = ' LEFT JOIN ' . DB_MEMBER_COUPON . ' mc ON mc.memberCouponNo = oc.memberCouponNo ';
        $join[] = ' LEFT JOIN ' . DB_COUPON . ' c ON c.couponNo = mc.couponNo ';

        // 환불여부에 따른 쿠폰 데이터
        if ($isRefunded) {
            $arrWhere[] = '(oc.minusRestoreCouponFl = \'y\' OR oc.plusRestoreCouponFl = \'y\') AND og.orderStatus = \'r3\'';
            $join[] = ' LEFT OUTER JOIN ' . DB_ORDER_GOODS . ' og ON oc.orderNo = og.orderNo ';
            $join[] = ' LEFT OUTER JOIN ' . DB_ORDER_HANDLE . ' oh ON og.orderNo = oh.orderNo AND og.handleSno = oh.sno ';
        } else {
            $join[] = ' LEFT OUTER JOIN ' . DB_ORDER_GOODS . ' og ON oc.orderNo = og.orderNo AND oc.orderCd = og.orderCd ';
        }

        $arrField = DBTableField::setTableField('tableOrderCoupon', null, null, 'oc');
        $this->db->strField = 'og.sno, og.goodsNm, og.goodsType, og.timeSaleFl, og.optionInfo, og.optionTextInfo, c.couponKindType, c.couponBenefit, c.couponBenefitType, ' . implode(', ', $arrField);
        $this->db->strWhere = implode(' AND ', $arrWhere);
        $this->db->strJoin = implode('', $join);
        $this->db->strOrder = 'oc.sno DESC';
        $this->db->strGroup = 'oc.sno';

        $query = $this->db->query_complete();
        $strSQL = 'SELECT ' . array_shift($query) . ' FROM ' . DB_ORDER_COUPON . ' oc ' . implode(' ', $query);
        $getData = $this->db->query_fetch($strSQL, $arrBind);

        // 해당 주문상품번호가 있는 경우 해당 상품의 쿠폰과 주문쿠폰만 나오도록 필터링
        if ($orderCd !== null && is_array($orderCd)) {
            foreach ($getData as $key => & $val) {
                if ($val['orderCd'] != 0 && !in_array($val['orderCd'], $orderCd)) {
                    unset($getData[$key]);
                }
            }
        }

        // 데이타 출력
        if (count($getData) > 0) {
            return gd_htmlspecialchars_stripslashes($getData);
        } else {
            return false;
        }
    }

    /**
     * 현금영수증 신청 여부 저장
     *
     * @param string $orderNo 주문 번호
     * @param string $receiptFl 영수증 신청 여부 (r - 현금영수증, t - 세금계산서, n - 신청안함)
     *
     * @throws Exception
     */
    public function setOrderReceiptRequest($orderNo, $receiptFl)
    {
        // 주문번호 체크
        if (Validator::required($orderNo, true) === false) {
            throw new Exception(__('주문번호은(는) 필수 항목 입니다.'));
        }

        // 현금영수증 신청 여부 저장
        $arrData['receiptFl'] = $receiptFl;
        $compareField = array_keys($arrData);
        $arrBind = $this->db->get_binding(DBTableField::tableOrder(), $arrData, 'update', $compareField);
        $this->db->bind_param_push($arrBind['bind'], 's', $orderNo);
        $this->db->set_update_db(DB_ORDER, $arrBind['param'], 'orderNo = ?', $arrBind['bind']);
        unset($arrBind);
    }

    /**
     * 관리자 무통장 입금은행 등록/수정 정보
     *
     * @param string $dataSno sno
     *
     * @return array 입금 은행 정보
     */
    public function getBankPolicyData($dataSno = null)
    {
        // 입금은행 기본정보
        $tmpField = DBTableField::tableManageBank();

        // --- 등록인 경우
        if (!gd_isset($dataSno)) {
            // 기본 정보
            $data['mode'] = 'bank_register';
            // 기본값 설정
            foreach ($tmpField as $key => $val) {
                if ($val['typ'] == 'i') {
                    $data[$val['val']] = (int)$val['def'];
                } else {
                    $data[$val['val']] = $val['def'];
                }
            }

            $total = $this->db->fetch('SELECT count(*) as total FROM ' . DB_MANAGE_BANK);
            if ($total['total'] == 0) {
                $data['useFl'] = "y";
                $data['defaultFl'] = "y";
                $data['disabled'] = "y";
            }

            unset($tmpField);
        } else {
            // 기본 정보
            $data = $this->getBankInfo($dataSno); // 관리자 정보
            $data['mode'] = 'bank_modify';

            // 기본값 설정
            foreach ($tmpField as $key => $val) {
                if ($val['def'] != null && !$data[$val['val']]) {
                    if ($val['typ'] == 'i') {
                        $data[$val['val']] = (int)$val['def'];
                    } else {
                        $data[$val['val']] = $val['def'];
                    }
                }
            }
            unset($tmpField);
        }

        $checked = [];
        $checked['defaultFl'][$data['defaultFl']] = $checked['useFl'][$data['useFl']] = 'checked="checked"';

        $getData['data'] = $data;
        $getData['checked'] = $checked;

        return $getData;
    }

    /**
     * 관리자 무통장 입금은행 리스트
     *
     * @return array 입금 은행 정보
     */
    public function getBankPolicyList()
    {
        // 검색을 위한 bind 정보
        $fieldType = DBTableField::getFieldTypes('tableManageBank');

        $request = Request::get()->toArray();

        // --- 정렬
        $this->search['sortList'] = [
            'regDt desc' => sprintf('%s↓', __('등록일')),
            'regDt asc' => sprintf('%s↑', __('등록일')),
            'bankName desc' => sprintf('%s↓', __('은행명')),
            'bankName asc' => sprintf('%s↑', __('은행명')),
        ];

        // --- 검색 설정
        $this->search['detailSearch'] = gd_isset($request['detailSearch']);
        $this->search['key'] = gd_isset($request['key']);
        $this->search['keyword'] = gd_isset($request['keyword']);
        $this->search['useFl'] = gd_isset($request['useFl']);
        $this->search['sort'] = gd_isset($request['sort'], 'regDt desc');

        $this->checked['useFl'][$this->search['useFl']] = 'checked="checked"';

        // 키워드 검색
        if ($this->search['keyword']) {
            if ($this->search['key'] == '') {
                $tmpWhere = [
                    'bankName',
                    'accountNumber',
                    'depositor',
                ];
                $arrWhereAll = [];
                foreach ($tmpWhere as $keyNm) {
                    $arrWhereAll[] = '(' . $keyNm . ' LIKE concat(\'%\',?,\'%\'))';
                    $this->db->bind_param_push($this->arrBind, 's', $this->search['keyword']);
                }
                $this->arrWhere[] = '(' . implode(' OR ', $arrWhereAll) . ')';
            } else {
                $this->arrWhere[] = '' . $this->search['key'] . ' LIKE concat(\'%\',?,\'%\')';
                $this->db->bind_param_push($this->arrBind, 's', $this->search['keyword']);
            }
        }

        // 사용 여부 검색
        if ($this->search['useFl']) {
            $this->arrWhere[] = 'useFl = ?';
            $this->db->bind_param_push($this->arrBind, $fieldType['useFl'], $this->search['useFl']);
        }

        if (empty($this->arrBind)) {
            $this->arrBind = null;
        }

        // --- 페이지 기본설정
        gd_isset($request['page'], 1);
        gd_isset($request['pageNum'], 10);

        $page = \App::load('\\Component\\Page\\Page', $request['page']);
        $page->page['list'] = $request['pageNum']; // 페이지당 리스트 수
        $total = $this->db->fetch('SELECT count(*) as total FROM ' . DB_MANAGE_BANK);
        $page->recode['amount'] = $total['total']; // 전체 레코드 수
        $page->setPage();
        $page->setUrl(\Request::getQueryString());

        // 현 페이지 결과
        $this->db->strField = '*';
        $this->db->strWhere = implode(' AND ', gd_isset($this->arrWhere));
        $this->db->strOrder = "defaultFl desc," . $this->search['sort'];
        $this->db->strLimit = $page->recode['start'] . ',' . $request['pageNum'];

        $query = $this->db->query_complete();
        $strSQL = 'SELECT ' . array_shift($query) . ' FROM ' . DB_MANAGE_BANK . implode(' ', $query);
        $data = $this->db->query_fetch($strSQL, $this->arrBind);

        // 레코드 수
        unset($query['group'], $query['order'], $query['limit']);
        $strCntSQL = 'SELECT COUNT(*) AS total FROM ' . DB_MANAGE_BANK . implode(' ', $query);
        $page->recode['total'] = $this->db->query_fetch($strCntSQL, $this->arrBind, false)['total'];
        //list($page->recode['total']) = $this->db->fetch('SELECT FOUND_ROWS()', 'row');
        $page->setPage();

        // 각 데이터 배열화
        $getData['data'] = gd_htmlspecialchars_stripslashes(gd_isset($data));
        $getData['sort'] = $this->search['sort'];
        $getData['search'] = gd_htmlspecialchars($this->search);
        $getData['checked'] = $this->checked;

        return $getData;
    }

    /**
     * 주문정책내 무통장 입금은행 등록 / 수정
     *
     * @param array $arrData 저장 할 데이터
     *
     * @return boolean 성공여부
     * @throws Exception
     */
    public function saveBankPolicy($arrData)
    {
        // 은행명 체크
        if (Validator::required(gd_isset($arrData['bankName'])) === false) {
            throw new Exception(__('은행명은(는) 필수 항목 입니다.'));
        }

        // 계좌번호 체크
        if (Validator::required(gd_isset($arrData['accountNumber'])) === false) {
            throw new Exception(__('계좌번호은(는) 필수 항목 입니다.'));
        }

        // 예금주 체크
        if (Validator::required(gd_isset($arrData['depositor'])) === false) {
            throw new Exception(__('예금주은(는) 필수 항목 입니다.'));
        }

        if (empty($arrData['defaultFl']) === true) {
            $arrData['defaultFl'] = "n";
        }

        if ($arrData['defaultFl'] == 'y') {
            $this->db->set_update_db(DB_MANAGE_BANK, array("defaultFl = 'n'"), "defaultFl='y'");
        }

        if ($arrData['mode'] == 'bank_register') {
            $arrData['managerNo'] = Session::get('manager.sno');
            // 은행 정보 등록
            $arrBind = $this->db->get_binding(DBTableField::tableManageBank(), $arrData, 'insert', array_keys($arrData));
            $this->db->set_insert_db(DB_MANAGE_BANK, $arrBind['param'], $arrBind['bind'], 'y');

            if ($this->db->insert_id()) {
                unset($arrBind);
                $this->bankPolicySms();

                return true;
            }
        } else {
            // 은행 정보 수정
            $arrBind = $this->db->get_binding(DBTableField::tableManageBank(), $arrData, 'update', array_keys($arrData));
            $this->db->bind_param_push($arrBind['bind'], 'i', $arrData['sno']);
            if ($this->db->set_update_db(DB_MANAGE_BANK, $arrBind['param'], 'sno = ?', $arrBind['bind'])) {
                unset($arrBind);
                $this->bankPolicySms();

                return true;
            }
        }

        return false;
    }

    /**
     * 은행정보 변경에 따른 SMS 전송
     * 2016-08-02 정책변경으로 상점SMS가 아닌 고도SMS로 변경
     *
     * @author Jong-tae Ahn <qnibus@godo.co.kr>
     */
    protected function bankPolicySms()
    {
        $godoApi = new MyGodoSmsServerApi();
        $godoApi->sendGodoSmsAuthComplete('bank');
    }

    /**
     * 주문정책내 무통장 입금은행 복사
     *
     * @param integer $dataSno 복사할 레코드 sno
     */
    public function copyBankPolicy($dataSno)
    {
        // 옵션 관리 정보 복사
        $arrField = DBTableField::setTableField('tableManageBank', null, array('defaultFl'));
        $strSQL = 'INSERT INTO ' . DB_MANAGE_BANK . ' (' . implode(', ', $arrField) . ', defaultFl,regDt) SELECT ' . implode(', ', $arrField) . ',"n" ,now() FROM ' . DB_MANAGE_BANK . ' WHERE sno = ' . $dataSno;
        $this->db->query($strSQL);
    }

    /**
     * 주문정책내 무통장 입금은행 삭제
     *
     * @param integer $intSno 삭제할 레코드 sno
     */
    public function deleteBankPolicy($dataSno)
    {
        // 옵션 관리 정보 삭제
        $this->db->bind_param_push($arrBind, 'i', $dataSno); // 추가 bind 데이터
        $this->db->set_delete_db(DB_MANAGE_BANK, 'sno = ?', $arrBind);
    }

    /**
     * 다운로드 양식 저장.
     *
     * @param array $req $_POST
     */
    public function setDownloadForm($req)
    {
        $arrData['formField'] = implode(STR_DIVISION, $req['formField']);
        $arrData['formFieldTxt'] = implode(STR_DIVISION, $req['formFieldTxt']);

        $formSort = null;
        for ($i = 0; $i < count($req['formSortField']); $i++) {
            if (empty($req['formSortField'][$i]) === false) {
                $formSort[] = $req['formSortField'][$i] . STR_DIVISION . $req['formSortOrder'][$i];
            }
        }
        if (ArrayUtils::isEmpty($arrData['formSort']) === false) {
            $arrData['formSort'] = implode(MARK_DIVISION, $formSort);
        }

        if (empty($req['formSno']) === true) {
            $arrData['formNm'] = $req['newFormNm'];

            $arrBind = $this->db->get_binding(DBTableField::tableOrderDownloadForm(), $arrData, 'insert', array_keys($arrData));
            $this->db->set_insert_db(DB_ORDER_DOWNLOAD_FORM, $arrBind['param'], $arrBind['bind'], 'y');
        } else {
            $arrData['formSno'] = $req['formSno'];
            $arrBind = $this->db->get_binding(DBTableField::tableOrderDownloadForm(), $arrData, 'update', array_keys($arrData));
            $this->db->bind_param_push($arrBind['bind'], 'i', $arrData['formSno']);
            $this->db->set_update_db(DB_ORDER_DOWNLOAD_FORM, $arrBind['param'], 'sno = ?', $arrBind['bind']);
        }
        unset($arrData);
    }

    /**
     * 다운로드 양식 삭제.
     *
     * @param integer $formSno 양식번호
     */
    public function removeDownloadForm($formSno)
    {
        $this->db->bind_param_push($arrBind, 'i', $formSno);
        $this->db->set_delete_db(DB_ORDER_DOWNLOAD_FORM, 'sno=?', $arrBind);
    }

    /**
     * 다운로드할 주문리스트 가져오기.
     *
     * @param int $formSno 양식번호
     * @param array $orderNo 양식번호
     *
     * @return array $data
     * @throws Except
     */
    public function getOrderListDownload($formSno, $orderNo)
    {
        // 다운로드 체크
        if (empty($formSno) === true) {
            throw new Exception(__('다운로드 엑셀 양식이 선택되지 않았습니다.'));
        }

        if (empty($orderNo) === true) {
            throw new Exception(__('주문정보가 선택되지 않았습니다.'));
        }

        // 엑셀 양식 가져오기
        $this->db->strField = '*';
        $this->db->strWhere = 'sno=?';
        $this->db->bind_param_push($arrBind, 'i', $formSno);

        $query = $this->db->query_complete();
        $strSQL = 'SELECT ' . array_shift($query) . ' FROM ' . DB_ORDER_DOWNLOAD_FORM . implode(' ', $query);
        $data = $this->db->query_fetch($strSQL, $arrBind, false);
        unset($arrBind);

        // 엑셀 양식 체크
        if (empty($data) === true || empty($data['formField']) === true) {
            throw new Exception(__('선택된 엑셀 양식이 존재하지 않거나 선택된 필드가 없습니다.'));
        }
        $data = gd_htmlspecialchars_stripslashes($data);

        $arrWhere = null;
        foreach ($orderNo as $val) {
            $arrWhere[] = 'o.orderNo=?';
            $this->db->bind_param_push($arrBind, 's', $val);
        }

        $formField = preg_replace(
            [
                '/[,]{2,}/',
                '/(^,|,$)/',
            ], [
            ',',
            '',
        ], str_replace(STR_DIVISION, ',', $data['formField'])
        );
        $formField = str_replace("orderAddressLong", "concat(oi.orderAddress, ' ', oi.orderAddressSub) AS orderAddressLong", $formField);
        $formField = str_replace("receiverAddressLong", "concat(oi.receiverAddress, ' ', oi.receiverAddressSub) AS receiverAddressLong", $formField);

        // 사은품 관련 부분은 제외
        $formField = str_replace(',giftTitle', '', $formField);
        $formField = str_replace(',giftGoodsName', '', $formField);

        $data['formField'] = explode(STR_DIVISION, $data['formField']);
        $data['formFieldTxt'] = explode(STR_DIVISION, $data['formFieldTxt']);
        $data['formSort'] = str_replace(STR_DIVISION, ' ', str_replace(MARK_DIVISION, ',', $data['formSort']));

        // 주문 상품 데이터의 코드가 있는지 체크
        $chkOg = false;
        $arrOg = [
            'og.',
            'cg.',
            'cb.',
            'dc.',
        ];
        foreach ($data['formField'] as $fVal) {
            if (in_array(substr($fVal, 0, 3), $arrOg)) {
                $chkOg = true;
            }
        }

        if ($chkOg === true) {
            $dbTableGoods = ' LEFT JOIN ' . DB_ORDER_GOODS . ' AS og ON o.orderNo=og.orderNo';
            $dbTableCBrand = ' LEFT JOIN ' . DB_CATEGORY_BRAND . ' AS cb ON og.brandCd=cb.cateCd';
            $dbTableCGoods = ' LEFT JOIN ' . DB_CATEGORY_GOODS . ' AS cg ON og.cateCd=cg.cateCd';
            $dbTableDelivery = ' LEFT JOIN ' . DB_MANAGE_DELIVERY_COMPANY . ' AS dc ON og.orderDeliverySno=dc.sno';
            $dbTableOrderDelivery = ' LEFT JOIN ' . DB_ORDER_DELIVERY . ' AS od ON o.orderNo=od.orderNo';
            $dbTableOrderInfo = ' LEFT JOIN ' . DB_ORDER_INFO . ' AS oi ON (o.orderNo=oi.orderNo) 
                            AND (CASE WHEN od.orderInfoSno > 0 THEN od.orderInfoSno = oi.sno ELSE oi.orderInfoCd = 1 END)';
        } else {
            $dbTableGoods = '';
            $dbTableCBrand = '';
            $dbTableCGoods = '';
            $dbTableDelivery = '';
            $dbTableOrderInfo = ' LEFT JOIN ' . DB_ORDER_INFO . ' AS oi ON o.orderNo=oi.orderNo AND oi.orderInfoCd = 1';
        }

        // 주문리스트 가져오기
        $this->db->strField = $formField;
        $this->db->strJoin = DB_ORDER . ' AS o ' . $dbTableGoods . $dbTableOrderDelivery. $dbTableOrderInfo . ' LEFT JOIN ' . DB_MEMBER . ' AS m ON o.memNo=m.memNo' . $dbTableCBrand . $dbTableCGoods . $dbTableDelivery;
        $this->db->strWhere = implode(' OR ', $arrWhere);
        $this->db->strOrder = $data['formSort'];

        $query = $this->db->query_complete();
        $strSQL = 'SELECT ' . array_shift($query) . ' FROM ' . array_shift($query) . implode(' ', $query);

        // 주문 정보 테이타
        $data['list'] = gd_htmlspecialchars_stripslashes($this->db->query_fetch($strSQL, $arrBind));
        unset($arrBind);

        // 사은품정보
        $chkGiftInfo = false;
        if (in_array('giftTitle', $data['formField']) === true || in_array('giftGoodsName', $data['formField']) === true) {
            $chkGiftInfo = true;
        }

        if ($chkGiftInfo === true) {
            foreach ($data['list'] as $key => $val) {
                if (empty($val['orderNo']) === true) {
                    continue;
                }
                $tmpData = $this->getOrderGift($val['orderNo']);
                $tmpTitle = [];
                $tmpGoods = [];
                if (empty($tmpData) === true) {
                    continue;
                }
                foreach ($tmpData as $tKey => $tVal) {
                    $tmpTitle[] = $tVal['presentTitle'];
                    if (empty($tVal['multiGiftNo']) === false) {
                        foreach ($tVal['multiGiftNo'] as $mVal) {
                            $tmpGoods[] = sprintf('[' . $tVal['giveCnt'] . '%s]' . $mVal['giftNm'], __('개 증정'));
                        }
                    }
                }
                if (in_array('giftTitle', $data['formField']) === true) {
                    $data['list'][$key]['giftTitle'] = implode('<br />', $tmpTitle);
                }
                if (in_array('giftGoodsName', $data['formField']) === true) {
                    $data['list'][$key]['giftGoodsName'] = implode('<br />', $tmpGoods);
                }
            }
        }

        return $data;
    }

    /**
     * 다운로드 양식 리스트.
     *
     * @param int $formSno 양식번호
     * @param array $orderNo 양식번호
     *
     * @return $data
     */
    public function getDownloadFormList()
    {
        // 현 페이지 결과
        $this->db->strField = '*';
        $this->db->strOrder = 'sno DESC';

        $query = $this->db->query_complete();
        $strSQL = 'SELECT ' . array_shift($query) . ' FROM ' . DB_ORDER_DOWNLOAD_FORM . implode(' ', $query);
        $data = $this->db->query_fetch($strSQL);

        return gd_htmlspecialchars_stripslashes($data);
    }

    /**
     * 다운로드 양식정보 가져오기.
     *
     * @param int $formSno 양식번호
     *
     * @return $data
     */
    public function getDownloadForm($formSno)
    {
        // 현 페이지 결과
        $this->db->strField = '*';
        $this->db->strWhere = 'sno=?';
        $this->db->bind_param_push($arrBind, 'i', $formSno);

        $query = $this->db->query_complete();
        $strSQL = 'SELECT ' . array_shift($query) . ' FROM ' . DB_ORDER_DOWNLOAD_FORM . implode(' ', $query);
        $data = $this->db->query_fetch($strSQL, $arrBind, false);

        if (ArrayUtils::isEmpty($data) === true) {
            return;
        }
        $data = gd_htmlspecialchars_stripslashes($data);

        $data['formField'] = explode(STR_DIVISION, $data['formField']);
        $data['formFieldTxt'] = explode(STR_DIVISION, $data['formFieldTxt']);
        $data['formSort'] = explode(MARK_DIVISION, $data['formSort']);
        foreach ($data['formSort'] as &$val) {
            $val = explode(STR_DIVISION, $val);
        }

        return $data;
    }

    /**
     * 송장 그룹코드 생성
     *
     * @return int
     */
    public function getMaxInvoiceGroupCd()
    {
        // 쿼리 조건
        $this->db->strField = 'max(groupCd) as max';

        // 쿼리 생성
        $query = $this->db->query_complete();
        $strSQL = 'SELECT ' . array_shift($query) . ' FROM ' . DB_ORDER_INVOICE . ' ' . implode(' ', $query);
        $getData = $this->db->query_fetch($strSQL, null, false);

        // 그룹 코드 리턴
        if (empty($getData['max']) === false) {
            $maxCd = $getData['max'];

            return ($maxCd + 1);
        } else {
            return 1;
        }
    }

    /**
     * 받아온 엑셀 파일과 기존 주문테이블의 송장번호를 비교해 일치하는 데이터가 있으면 반환
     * 송장 일괄등록 전 체크용도
     *
     * @param $files
     *
     * @return array
     * @throws Exception
     * @author Jong-tae Ahn <qnibus@godo.co.kr>
     */
    public function checkOrderInvoiceExcel($files)
    {
        if ($files['excel']['error'] > 0) {
            $failMsg = __('엑셀 화일이 존재하지 않습니다. 다운 로드 받으신 엑셀파일을 이용해서 "Excel 97-2003 통합문서" 로 저장이 된 엑셀 파일만 가능합니다.');
            throw new Exception($failMsg);
        }

        // 엑셀데이터 추출
        $data = new SpreadsheetExcelReader();
        $data->setOutputEncoding('CP949');
        $chk = $data->read($files['excel']['tmp_name']);

        // 엑셀데이터 체크
        if ($chk === false) {
            $failMsg = __('엑셀 화일을 확인해 주세요. 다운 로드 받으신 엑셀파일을 이용해서 "Excel 97-2003 통합문서" 로 저장이 된 엑셀 파일만 가능합니다.');
            throw new Exception($failMsg);
        }

        // 반드시 Excel 97-2003 통합문서로 저장이 되어야 하며, 1번째 줄은 설명, 2번째 줄부터 데이타 입니다.
        if ($data->sheets[0]['numRows'] < 2) {
            $failMsg = __('엑셀 화일을 확인해 주세요. 엑셀 데이타가 존재하지 않습니다. 데이타는 2번째 줄부터 작성을 하셔야 합니다.');
            throw new Exception($failMsg);
        }

        // 1,000건 넘길 시 예외 처리
        if ($data->sheets[0]['numRows'] > 1001) {
            throw new Exception(__('송장 입력데이터는 1,000건 이상 처리할 수 없습니다.'));
        }

        // 데이터 송장번호 유무 체크
        $setData = [];
        $cnt = count($data->sheets[0]['cells']);
        for ($i = 2; $i <= $cnt; $i++) {
            $sno = trim($data->sheets[0]['cells'][$i][2]);
            $orderNo = sprintf('%.0f', trim($data->sheets[0]['cells'][$i][3]));

            // 추출 필드
            $arrIncludeOg = [
                'orderNo',
                'invoiceCompanySno',
                'invoiceNo',
                'deliveryDt',
                'deliveryCompleteDt',
                'orderStatus',
            ];
            $tmpField[] = DBTableField::setTableField('tableOrderGoods', $arrIncludeOg, null, 'og');

            // 쿼리용 필드 합침
            $tmpKey = array_keys($tmpField);
            $arrField = [];
            foreach ($tmpKey as $key) {
                $arrField = array_merge($arrField, $tmpField[$key]);
            }
            unset($tmpField, $tmpKey);

            // 상품주문번호
            if (empty($sno) || $sno == 0) {
                $sno = null;
            } else {
                $arrWhere[] = 'og.sno=?';
                $this->db->bind_param_push($arrBind, 'i', $sno);
            }

            // 주문번호
            if (empty($orderNo) || $orderNo == 0) {
                $orderNo = null;
            } else {
                $arrWhere[] = 'og.orderNo=?';
                $this->db->bind_param_push($arrBind, 's', $orderNo);
            }

            // 쿼리 구성
            $this->db->strField = implode(',', $arrField) . ', og.sno, og.regDt';
            $this->db->strWhere = implode(' AND ', $arrWhere);
            $this->db->strLimit = '1';

            // 쿼리 생성
            $query = $this->db->query_complete();
            $strSQL = 'SELECT ' . array_shift($query) . ' FROM ' . DB_ORDER_GOODS . ' AS og ' . implode(' ', $query);
            $getData = $this->db->query_fetch($strSQL, $arrBind, false);
            if ($getData['deliveryDt'] == '0000-00-00 00:00:00' || in_array(substr($getData['orderStatus'], 0, 1), ['o', 'p', 'g'])) {
                $getData['deliveryDt'] = '-';
            }

            if ($getData['deliveryCompleteDt'] == '0000-00-00 00:00:00' || in_array(substr($getData['orderStatus'], 0, 1), ['o', 'p', 'g'])) {
                $getData['deliveryCompleteDt'] = '-';
            }

            if (empty($getData['invoiceNo']) === false) {
                $setData[] = $getData;
            }
            unset($getData, $arrWhere, $arrField, $arrBind);
        }

        return $setData;
    }

    /**
     * 송장번호입력양식 업로드
     *
     *
     * @param array $files $_FILES
     *
     * @return array
     * @throws Exception
     */
    public function updateOrderInvoiceExcel(&$files)
    {
        set_time_limit(RUN_TIME_LIMIT);

        if ($files['excel']['error'] > 0) {
            $failMsg = __('엑셀 화일이 존재하지 않습니다. 다운 로드 받으신 엑셀파일을 이용해서 "Excel 97-2003 통합문서" 로 저장이 된 엑셀 파일만 가능합니다.');
            throw new Exception($failMsg);
        }

        // 엑셀데이터 추출
        $data = new SpreadsheetExcelReader();
        $data->setOutputEncoding('UTF-8');
        $chk = $data->read($files['excel']['tmp_name']);

        // 엑셀데이터 체크
        if ($chk === false) {
            $failMsg = __('엑셀 화일을 확인해 주세요. 다운 로드 받으신 엑셀파일을 이용해서 "Excel 97-2003 통합문서" 로 저장이 된 엑셀 파일만 가능합니다.');
            throw new Exception($failMsg);
        }

        // 반드시 Excel 97-2003 통합문서로 저장이 되어야 하며, 1번째 줄은 설명, 2번째 줄부터 데이타 입니다.
        if ($data->sheets[0]['numRows'] < 2) {
            $failMsg = __('엑셀 화일을 확인해 주세요. 엑셀 데이타가 존재하지 않습니다. 데이타는 2번째 줄부터 작성을 하셔야 합니다.');
            throw new Exception($failMsg);
        }

        // 1,000건 넘길 시 예외 처리
        if ($data->sheets[0]['numRows'] > 1001) {
            throw new Exception(__('송장 입력데이터는 1,000건 이상 처리할 수 없습니다.'));
        }

        // 송장일괄등록 그룹코드 설정
        $groupCd = $this->getMaxInvoiceGroupCd();

        // 엑셀 저장(저장 실패할경우 무시)
        try {
            if(is_uploaded_file($files['excel']['tmp_name'])) {
                $saveFileNm = sprintf('invoice_%s',$groupCd).'.xls';
                $savePath =  UserFilePath::data('excel','invoice')->getRealPath();
                $saveFullPath = $savePath.DS.$saveFileNm;
                if(!is_dir($savePath)){
                    mkdir($savePath,0707);
                }
                if(file_exists($saveFullPath)){
                    $saveFileNm = sprintf('invoice_%s',$groupCd).'_'.date('Hms').'.xls';
                    $saveFullPath = $savePath.DS.$saveFileNm;
                }
                \FileHandler::copy($files['excel']['tmp_name'], $saveFullPath, 0707);
            }
        } catch(\Throwable $e){
        }

        // 사용 중인 배송업체
        $delivery = App::load(\Component\Delivery\Delivery::class);
        $invoiceCompanyDmethod = [];
        $invoiceCompanys = $delivery->getDeliveryCompany(null, true);
        foreach ($invoiceCompanys as $companySno) {
            $invoiceCompanySnos[] = $companySno['sno'];
            $invoiceCompanyDmethod[$companySno['sno']] = [
                'companyKey' => $companySno['companyKey'],
                'deliveryFl' => $companySno['deliveryFl'],
            ];
        }

        // 데이타 실행 건수 초기화
        $successCnt = $failCnt = $functionAuthCnt = 0; // 운영자 주문상태 기능 권한에 따른 처리상태 추가

        // 엑셀 업로드 시 상태변경에 따른 메일/sms 발송을 막기 위한 상태 변경
        $this->skipSendOrderInfo = true;
        $sendDeliveryOrderInfo = $sendDeliveryCompleteOrderInfo = [];

        $cnt = count($data->sheets[0]['cells']);
        $logger = \App::getInstance('logger');
        $loggerTitle = '엑셀송장업로드('.$groupCd.') ';
        $logger->info($loggerTitle.'Start', ['전체:'.($cnt-1)]);
        // 엑셀 데이터를 추출해서 데이터 설정 (상품주문번호가 없는 경우 제외)
        try{
            for ($i = 2; $i <= $cnt; $i++) {
                // 엑셀데이터 row 초기화
                $getData = [
                    'sno' => trim($data->sheets[0]['cells'][$i][2]),
                    'orderNo' => trim($data->sheets[0]['cells'][$i][3]),
                    'deliveryDt' => $data->sheets[0]['cells'][$i][6] ? date('Y-m-d', strtotime(str_replace('/', '-', str_replace('"', '', $data->sheets[0]['cells'][$i][6]))) - 86400) : '', // 배송중일자
                    'deliveryCompleteDt' => $data->sheets[0]['cells'][$i][7] ? date('Y-m-d', strtotime(str_replace('/', '-', str_replace('"', '', $data->sheets[0]['cells'][$i][7]))) - 86400) : '', // 배송완료일자
                    'invoiceCompanySno' => trim($data->sheets[0]['cells'][$i][4]),
                    'invoiceNo' => StringUtils::xssClean(trim($data->sheets[0]['cells'][$i][5])),
                ];
                // 쿼리를 실행할지 말지
                $isRun = true;

                // 성공인지 실패인지
                $result = false;

                if (!$getData['orderNo']) {
                    $failMessage[] = $failReason = __('주문번호가 존재하지 않습니다.');
                    $isRun = false;
                }

                // 배송업체 체크 후 없으면 실패 처리
                if (!in_array($getData['invoiceCompanySno'], $invoiceCompanySnos)) {
                    $failMessage[] = $failReason = __('배송업체가 존재하지 않습니다.');
                    $isRun = false;
                }

                // 송장번호 없는 경우
                if (empty($getData['invoiceNo']) || $getData['invoiceNo'] === 0) {
                    $failMessage[] = $failReason = __('송장번호가 없습니다.');
                    $isRun = false;
                }

                // 주문번호 확인이 안되는 경우
                if ((empty($getData['sno']) || $getData['sno'] == 0) && (empty($getData['orderNo']) || $getData['orderNo'] == 0)) {
                    $failMessage[] = $failReason = __('주문이 존재하지 않습니다.');
                    $isRun = false;
                }

                // 공급사로 업로드 하는 경우
                if (Manager::isProvider()) {
                    if (empty($getData['sno']) || $getData['sno'] == 0) {
                        $failMessage[] = $failReason = __('송장일괄등록은 상품주문번호가 반드시 필요합니다.');
                        $isRun = false;
                    }
                }

                // 배송일이 배송완료일보다 큰 경우
                if (!empty($getData['deliveryDt']) && !empty($getData['deliveryCompleteDt'])) {
                    if (strtotime($getData['deliveryDt']) > strtotime($getData['deliveryCompleteDt'])) {
                        $failMessage[] = $failReason = __('배송일이 배송완료일보다 큽니다.');
                        $isRun = false;
                    }
                }

                // 쿼리를 실행할 수 없는 경우에 대한 처리
                if ($isRun === true) {
                    // 날짜에 현재 시간대 추가
                    if (!empty($getData['deliveryDt'])) {
                        $getData['deliveryDt'] .= ' ' . date('H:i:s');
                    }
                    if (!empty($getData['deliveryCompleteDt'])) {
                        $getData['deliveryCompleteDt'] .= ' ' . date('H:i:s');
                    }

                    //배송방식 업데이트
                    $upData['deliveryMethodFl'] = 'delivery';
                    if(trim($getData['invoiceCompanySno'])){
                        //배송방식이 택배가 아닐 경우
                        if($invoiceCompanyDmethod[$getData['invoiceCompanySno']]['deliveryFl'] === 'n'){
                            $upData['deliveryMethodFl'] = $invoiceCompanyDmethod[$getData['invoiceCompanySno']]['companyKey'];
                        }
                    }

                    // 주문번호와 주문상품번호 체크
                    $upData['invoiceCompanySno'] = $getData['invoiceCompanySno'];
                    $upData['invoiceNo'] = $getData['invoiceNo'];
                    $upData['invoiceDt'] = date('Y-m-d H:i:s');
                    $upData['deliveryDt'] = $getData['deliveryDt'];
                    $upData['deliveryCompleteDt'] = $getData['deliveryCompleteDt'];
                    $arrBind = $this->db->get_binding(DBTableField::tableOrderGoods(), $upData, 'update', array_keys($upData));

                    // 상품주문번호 조건절
                    if (empty($getData['sno']) || $getData['sno'] == 0) {
                        $getData['sno'] = null;
                    } else {
                        $tmpWhere[] = 'sno=?';
                        $this->db->bind_param_push($arrBind['bind'], 'i', $getData['sno']);
                    }

                    // 주문번호 조건절
                    if (empty($getData['orderNo']) || $getData['orderNo'] == 0) {
                        $getData['orderNo'] = null;
                    } else {
                        $tmpWhere[] = 'orderNo=?';
                        $this->db->bind_param_push($arrBind['bind'], 's', $getData['orderNo']);
                    }

                    // 공급사로 업로드 하는 경우
                    if (Manager::isProvider()) {
                        $tmpWhere[] = 'scmNo=?';
                        $this->db->bind_param_push($arrBind['bind'], 's', Session::get('manager.scmNo'));
                    }

                    // 송장일괄등록시 해당 주문상태의 주문만 변경(입금, 상품, 배송, 추가입금)
                    $tmpWhere[] = 'SUBSTRING(orderStatus, 1, 1) IN (\'g\', \'d\', \'p\', \'z\')';

                    // 주문상품 데이터 업데이트 성공 여부 반환
                    if (!empty($tmpWhere)) {
                        // 현 주문정보
                        $orderGoods = $this->getOrderGoods($getData['orderNo'], $getData['sno'], null, null, null, ['orderChannelFl']);
                        $resultFail = true;
                        if(!$orderGoods) {
                            $failMessage[] = $failReason = __('존재하지 않는 주문번호 또는 상품주문번호 입니다.');
                            $resultFail = false;
                            $result = false;
                        }
                        if (Manager::isProvider()) {
                            if (Session::get('manager.scmNo') != $orderGoods[0]['scmNo']) {
                                $failMessage[] = $failReason = __('공급사 주문 상품이 아닙니다. 상품주문번호를 확인해 주세요.');
                                $resultFail = false;
                                $result = false;
                            }
                        }

                        if ($resultFail) {
                            // 현재 주문상태모드
                            $statusMode = substr($orderGoods[0]['orderStatus'], 0, 1);

                            // 입금대기 상태인 경우 실패 처리
                            if ($statusMode != 'o' && in_array($statusMode, explode(',', 'g,d,p,z'))) {
                                // 주문상품 데이터 업데이트
                                $changeCode = false;
                                $result = $this->db->set_update_db(DB_ORDER_GOODS, $arrBind['param'], implode(' AND ', $tmpWhere), $arrBind['bind']);
                                if($result>0) {
                                    $result = true;
                                }

                                //교환추가 된 경우
                                if($statusMode === 'z'){
                                    // 배송일자 기록에 따른 주문상태 변경
                                    if (!empty($getData['deliveryDt']) && !empty($getData['invoiceNo'])) {   //배송일자가 있으면 추가배송중
                                        $changeCode = 3;
                                    }

                                    if (!empty($getData['deliveryCompleteDt'])) {   //완료일자가 있으면 추가배송완료
                                        $changeCode = 4;
                                    }
                                }
                                else {
                                    // 배송일자 기록에 따른 주문상태 변경
                                    if (!empty($getData['deliveryDt']) && !empty($getData['invoiceNo'])) {   //배송일자가 있으면 배송중
                                        $changeCode = 1;
                                    }

                                    if (!empty($getData['deliveryCompleteDt'])) {   //완료일자가 있으면 배송완료
                                        $changeCode = 2;
                                    }
                                }

                                if (!in_array($statusMode, explode(',', 'g,d,p,z'))) {
                                    $changeCode = 9;    // 입금, 배송, 상품 그룹 상태가 아닌 경우 송장번호 입력 불가, 배송상태 변경 불가
                                }

                                // 주문상태 변경 처리
                                if ($changeCode !== false) {
                                    // 운영자 기능권한 처리 (주문 상태 권한) - 관리자페이지에서만
                                    $thisCallController = \App::getInstance('ControllerNameResolver')->getControllerRootDirectory();
                                    if ($thisCallController == 'admin' && Session::get('manager.functionAuthState') == 'check' && Session::get('manager.functionAuth.orderState') != 'y') {
                                        $failMessage[] = $failReason = __('주문상태 변경 권한이 없는 운영자');
                                        $result = 'functionAuth';
                                    } else {
                                        //네이버페이 주문이면
                                        if ($orderGoods[0]['orderChannelFl'] == 'naverpay') {
                                            if(in_array($statusMode,['p','d','g']) === false){
                                                $failMessage[] = $failReason = __('변경 불가한 주문상태입니다.('.$statusMode.')');
                                                $result = false;
                                            }
                                            else {
                                                $delivery = new Delivery();
                                                $deliveryCompany = $delivery->getDeliveryCompany($upData['invoiceCompanySno'], 'naverpay');
                                                $_dispatchDate = date_create($getData['deliveryDt']);
                                                $dispatchDate = date_format($_dispatchDate, date('Ymd H:i:s'));

                                                if ($changeCode == 1) { //배송중
                                                    $naverPayApi = new NaverPayAPI();
                                                    try {
                                                        foreach($orderGoods as $orderGoodsData){
                                                            $apiOrderGoodsNo = $orderGoodsData['apiOrderGoodsNo'];
                                                            $naverPayCompanyCode = $deliveryCompany[0]['naverPayCode'];

                                                            if($orderGoodsData['orderStatus'] == 'd1' && $changeCode == 1){
                                                                $failMessage[] = $failReason = __('이미 배송중상태 입니다.('.$orderGoodsData['orderStatus'].'->d'.$changeCode.')');
                                                                $result = false;
                                                                continue;
                                                            }

                                                            $params = [
                                                                'ProductOrderID' => $apiOrderGoodsNo,
                                                                'DeliveryMethodCode' => 'DELIVERY',
                                                                'DeliveryCompanyCode' => $naverPayCompanyCode,
                                                                'TrackingNumber' => $upData['invoiceNo'],
                                                                'DispatchDate' => $dispatchDate,
                                                            ];

                                                            $apiResult = $naverPayApi->request('ShipProductOrder', $params);
                                                            if ($apiResult !== false) {
                                                                $result = true;
                                                            } else {
                                                                $failMessage[] = $failReason = $naverPayApi->getError();
                                                                $result = false;
                                                            }
                                                        }
                                                    } catch (Exception $e) {
                                                        $failMessage[] = $failReason = $naverPayApi->getError();
                                                        $result = false;
                                                    }
                                                } else if ($changeCode == 2) {  //배송완료
                                                    $failMessage[] = $failReason = __('네이버페이 주문은 배송완료로 처리 하실 수 없습니다.');
                                                    $result = false;
                                                } else {
                                                    $failMessage[] = $failReason = sprintf('%s(%s : d' . $changeCode . ')', __('변경 할 수없는 주문상태'), __('배송코드'));
                                                    $result = false;
                                                }
                                            }
                                        } else {
                                            if ($changeCode == 9) {
                                                $failMessage[] = $failReason = __('송장등록 불가 상태');
                                                $result = false;
                                            } else {
                                                //교환추가 된 경우
                                                if($statusMode === 'z'){
                                                    $completeCode = 'z' . $changeCode;
                                                }
                                                else {
                                                    $completeCode = 'd' . $changeCode;
                                                }
                                                foreach ($orderGoods as $key => $orderGoodsData) {
                                                    if (in_array(substr($orderGoodsData['orderStatus'], 0, 1), ['g', 'd', 'p', 'z']) === false) {
                                                        unset($orderGoods[$key]);
                                                    }
                                                }
                                                $this->updateStatusUnconditionalPreprocess($getData['orderNo'], $orderGoods, $statusMode, $completeCode, __('송장일괄등록에서'), true);
                                            }
                                        }
                                    }

                                    // 주문번호만 있는 경우 상품주문번호를 추출해 담아준다.
                                    $getData['sno'] = $orderGoods[0]['sno'];
                                }
                            } else {
                                if (!in_array($statusMode, explode(',', 'g,d,p'))) {
                                    $failMessage[] = $failReason = __('송장등록 불가 상태');
                                } else {
                                    $failMessage[] = $failReason = __('입금대기시 송장등록 불가');
                                }
                                $result = false;
                            }
                        }
                    }
                    unset($arrBind, $tmpWhere);
                }

                // 주문상품 변경 성공시 주문송장일괄처리 데이터 업데이트
                $invoiceData['completeFl'] = ($result === 'functionAuth' ? 'f' : ($result !== false ? 'y' : 'n')); // 운영자 주문상태 기능 권한에 따른 처리상태 추가
                $invoiceData['orderNo'] = $getData['orderNo'];
                $invoiceData['orderGoodsNo'] = $getData['sno'];
                $invoiceData['scmNo'] = Session::get('manager.scmNo');
                $invoiceData['managerNo'] = Session::get('manager.sno');
                $invoiceData['groupCd'] = $groupCd;
                $invoiceData['invoiceCompanySno'] = $getData['invoiceCompanySno'];
                $invoiceData['invoiceNo'] = $getData['invoiceNo'];
                $invoiceData['deliveryDt'] = $getData['deliveryDt'];
                $invoiceData['deliveryCompleteDt'] = $getData['deliveryCompleteDt'];
                $invoiceData['failReason'] = $failReason;
                $logger->info($loggerTitle.'data', [$invoiceData]);
                $arrBind = $this->db->get_binding(DBTableField::tableOrderInvoice(), $invoiceData, 'insert', array_keys($invoiceData));
                $this->db->set_insert_db(DB_ORDER_INVOICE, $arrBind['param'], $arrBind['bind'], 'y');
                unset($arrBind, $failReason);

                // 성공/실패/운영자권한 건수 계산
                if ($result === 'functionAuth') {
                    $functionAuthCnt++;
                } else if ($result === true) {
                    if (!empty($getData['deliveryDt']) && !empty($getData['invoiceNo'])) {   //배송중으로 변경되는 상태 조건인 경우
                        if($orderGoods[0]['orderChannelFl'] !== 'etc'){
                            $sendDeliveryOrderInfo[$getData['orderNo']] = $getData['orderNo'];
                        }
                    } elseif (!empty($getData['deliveryCompleteDt'])) {     //완료일자가 있으면 배송완료
                        if($orderGoods[0]['orderChannelFl'] !== 'etc'){
                            $sendDeliveryCompleteOrderInfo[$getData['orderNo']] = $getData['orderNo'];
                        }
                    }

                    $successCnt++;
                } else {
                    $failCnt++;
                }
            }

            // 초기 값으로 재설정
            $this->skipSendOrderInfo = false;
            foreach ($sendDeliveryOrderInfo as $orderNo) {
                $this->sendOrderInfo(MailMimeAuto::GOODS_DELIVERY, 'all', $orderNo);
                $this->sendOrderInfo(Code::INVOICE_CODE, 'sms', $orderNo);
            }
            foreach ($sendDeliveryCompleteOrderInfo as $orderNo) {
                $this->sendOrderInfo(Code::DELIVERY_COMPLETED, 'sms', $orderNo);
            }
            $logger->info($loggerTitle.'End', ['전체:'.($cnt).'/성공수:'.$successCnt.'/실패수:'.$failCnt]);

        } catch(\Exception $e){
            $logger->info($loggerTitle.'Exception', ['상품주문번호 : '.$orderGoods[0]['sno'],$e->getMessage()]);
        }

        return [
            'total' => $successCnt + $failCnt + $functionAuthCnt,
            'success' => $successCnt,
            'fail' => $failCnt,
            'functionAuth' => $functionAuthCnt,
            'message' => $failMessage,
        ];
    }

    /**
     * getOrderInvoiceList
     *
     * @param array $searchData 요청정보
     *
     * @return string
     */
    public function getOrderInvoiceList($searchData, $searchPeriod = 6)
    {
        // 통합검색 리스트
        $searchData['combineSearch'] = [
            'all' => '=' . __('통합검색') . '=',

            'm.managerNm' => __('등록자명'),
            'm.managerId' => __('등록자아이디'),
        ];
        $searchData['scmFl'] = gd_isset($searchData['scmFl'], 'all');
        $searchData['resultFl'] = gd_isset($searchData['resultFl'], '');
        $searchData['treatDate'][0] = gd_isset($searchData['treatDate'][0], $searchData['searchPeriod'] == -1 ? $searchData['treatDate'][0] : date('Y-m-d 00:00', strtotime('-' . $searchPeriod . ' day')));
        $searchData['treatDate'][1] = gd_isset($searchData['treatDate'][1], $searchData['searchPeriod'] == -1 ? $searchData['treatDate'][1] : date('Y-m-d H:i'));

        // 초기화
        $arrWhere = [];

        // 공급사 선택
        if (Manager::isProvider()) {
            // 공급사로 로그인한 경우 기존 scm에 값 설정
            $arrWhere[] = 'oi.scmNo = ' . Session::get('manager.scmNo');
        } else {
            if ($searchData['scmFl'] == '1') {
                if (is_array($searchData['scmNo'])) {
                    foreach ($searchData['scmNo'] as $val) {
                        $tmpWhere[] = 'oi.scmNo = ?';
                        $this->db->bind_param_push($arrBind, 's', $val);
                    }
                    $arrWhere[] = '(' . implode(' OR ', $tmpWhere) . ')';
                    unset($tmpWhere);
                } else if ($searchData['scmNo'] > 1) {
                    $arrWhere[] = 'oi.scmNo = ?';
                    $this->db->bind_param_push($arrBind, 'i', $searchData['scmNo']);
                }
            } elseif ($searchData['scmFl'] == '0') {
                $arrWhere[] = 'oi.scmNo = 1';
            }
        }

        // 키워드 검색
        if ($searchData['key'] && $searchData['keyword']) {
            if ($searchData['key'] == 'all') {
                $tmpWhere = array_keys($searchData['combineSearch']);
                array_shift($tmpWhere);
                $arrWhereAll = [];
                foreach ($tmpWhere as $keyNm) {
                    $arrWhereAll[] = '(' . $keyNm . ' LIKE concat(\'%\',?,\'%\'))';
                    $this->db->bind_param_push($arrBind, 's', $searchData['keyword']);
                }
                $arrWhere[] = '(' . implode(' OR ', $arrWhereAll) . ')';
                unset($tmpWhere);
            } else {
                $arrWhere[] = $searchData['key'] . ' LIKE concat(\'%\',?,\'%\')';
                $this->db->bind_param_push($arrBind, 's', $searchData['keyword']);
            }
        }

        // 등록상태 검색
        $pageTotalWhere = '';
        if (empty($searchData['resultFl']) === false) {
            if ($searchData['resultFl'] === 'success') {
                $arrWhere[] = 'successCnt > 0';
                $pageTotalWhere = ' WHERE oj.successCnt > 0 ';
            } else if ($searchData['resultFl'] === 'fail') {
                $arrWhere[] = 'failCnt > 0';
                $pageTotalWhere = ' WHERE oj.failCnt > 0 ';
            } else { // 운영자 기능 권한 주문상태 변경 실패 건
                $arrWhere[] = 'functionAuthCnt > 0';
                $pageTotalWhere = ' WHERE oj.functionAuthCnt > 0 ';
            }
        }

        // 처리일자 검색 (YYYY-MM-DD HH:II 까지 검색하기 때문에 검색시 초만 넣어주면 된다)
        if (isset($searchPeriod) && $searchPeriod != -1 && $searchData['treatDate'][0] && $searchData['treatDate'][1]) {
            $arrWhere[] = 'oi.regDt BETWEEN ? AND ?';
            $this->db->bind_param_push($arrBind, 's', $searchData['treatDate'][0] . ':00');
            $this->db->bind_param_push($arrBind, 's', $searchData['treatDate'][1] . ':59');
        }
        $getData['search'] = gd_htmlspecialchars($searchData);

        // --- 체크박스 설정
        $checked['scmFl'][$searchData['scmFl']] =
        $checked['memFl'][$searchData['memFl']] =
        $checked['resultFl'][$searchData['resultFl']] = 'checked="checked"';
        $checked['periodFl'][$searchPeriod] = 'active';
        $getData['checked'] = $checked;

        // --- 페이지 기본설정
        gd_isset($searchData['page'], 1);
        gd_isset($searchData['pageNum'], 20);

        $page = \App::load('\\Component\\Page\\Page', $searchData['page']);
        $page->page['list'] = $searchData['pageNum']; // 페이지당 리스트 수
        $page->setPage();
        $page->setUrl(\Request::getQueryString());

        // --- 정렬 설정
        $orderSort = gd_isset($searchData['sort'], 'oi.regDt desc, oi.scmNo asc, oi.orderGoodsNo asc');

        // 추출 필드
        $arrIncludeOi = [
            'groupCd',
            'managerNo',
            'scmNo',
        ];
        $arrIncludeSm = [
            'companyNm',
        ];
        $arrIncludeM = [
            'managerId',
            'managerNm',
            'isDelete'
        ];
        $tmpField[] = DBTableField::setTableField('tableOrderInvoice', $arrIncludeOi, null, 'oi');
        $tmpField[] = DBTableField::setTableField('tableScmManage', $arrIncludeSm, null, 'sm');
        $tmpField[] = DBTableField::setTableField('tableManager', $arrIncludeM, null, 'm');

        // join 문 (운영자 기능 권한 - 주문 상태 실패 건 추가)
        $join[] = ' JOIN (SELECT groupCd, IFNULL(SUM(IF(completeFl=\'y\', 1, 0)), 0) AS successCnt, IFNULL(SUM(IF(completeFl=\'n\', 1, 0)), 0) AS failCnt, IFNULL(SUM(IF(completeFl=\'f\', 1, 0)), 0) AS functionAuthCnt FROM ' . DB_ORDER_INVOICE . ' GROUP BY groupCd) AS oj ON oj.groupCd = oi.groupCd';
        $join[] = ' LEFT JOIN ' . DB_MANAGER . ' AS m ON oi.managerNo = m.sno ';
        $join[] = ' LEFT JOIN ' . DB_SCM_MANAGE . ' AS sm ON oi.scmNo = sm.scmNo ';

        // 쿼리용 필드 합침
        $tmpKey = array_keys($tmpField);
        $arrField = [];
        foreach ($tmpKey as $key) {
            $arrField = array_merge($arrField, $tmpField[$key]);
        }
        unset($tmpField, $tmpKey);

        // 쿼리 조건
        $this->db->strJoin = implode(' ', $join);
        $this->db->strField = 'oj.successCnt, oj.failCnt, oj.functionAuthCnt, ' . implode(',', $arrField) . ', oi.regDt';
        $this->db->strOrder = $orderSort;
        $this->db->strWhere = implode(' AND ', gd_isset($arrWhere));
        $this->db->strGroup = 'oi.groupCd';
        $this->db->strLimit = $page->recode['start'] . ',' . $searchData['pageNum'];

        // 쿼리 생성
        $query = $this->db->query_complete();
        $strSQL = 'SELECT ' . array_shift($query) . ' FROM ' . DB_ORDER_INVOICE . ' AS oi ' . implode(' ', $query);
        $getData['data'] = $this->db->query_fetch($strSQL, $arrBind);
        Manager::displayListData($getData['data']);

        // 검색 레코드 수
        unset($query['group'], $query['order'], $query['limit']);
        $strCntSQL = 'SELECT COUNT(DISTINCT oi.groupCd) AS total FROM ' . DB_ORDER_INVOICE . ' AS oi ' . implode(' ', $query);
        $page->recode['total'] = $this->db->query_fetch($strCntSQL, $arrBind, false)['total'];
        if (Manager::isProvider()) {
            $total = $this->db->fetch('SELECT count(DISTINCT oi.groupCd) as total FROM ' . DB_ORDER_INVOICE . ' oi WHERE oi.scmNo=' . Session::get('manager.scmNo'));
        } else {
            $total = $this->db->fetch('SELECT count(DISTINCT oi.groupCd) as total FROM ' . DB_ORDER_INVOICE . ' oi');
        }
        $page->recode['amount'] = $total['total'];
        $page->setPage();

        return gd_htmlspecialchars_stripslashes($getData);
    }

    /**
     * 일괄송장등록 기본 정보
     *
     * @param integer $groupCd 그룹코드
     *
     * @return string
     */
    public function getOrderInvoiceInfo($groupCd)
    {
        // 추출 필드
        $arrIncludeOi = [
            'groupCd',
            'managerNo',
            'scmNo',
        ];
        $arrIncludeSm = [
            'companyNm',
        ];
        $arrIncludeM = [
            'managerId',
            'managerNm',
        ];
        $tmpField[] = DBTableField::setTableField('tableOrderInvoice', $arrIncludeOi, null, 'oi');
        $tmpField[] = DBTableField::setTableField('tableScmManage', $arrIncludeSm, null, 'sm');
        $tmpField[] = DBTableField::setTableField('tableManager', $arrIncludeM, null, 'm');

        // join 문
        $join[] = ' LEFT JOIN ' . DB_MANAGER . ' AS m ON oi.managerNo = m.sno ';
        $join[] = ' LEFT JOIN ' . DB_SCM_MANAGE . ' AS sm ON oi.scmNo = sm.scmNo ';

        // 쿼리용 필드 합침
        $tmpKey = array_keys($tmpField);
        $arrField = [];
        foreach ($tmpKey as $key) {
            $arrField = array_merge($arrField, $tmpField[$key]);
        }
        unset($tmpField, $tmpKey);

        // 쿼리 조건
        $arrWhere[] = 'oi.groupCd=?';
        $this->db->bind_param_push($arrBind, 's', $groupCd);

        // 쿼리 구성
        $this->db->strJoin = implode(' ', $join);
        $this->db->strField = implode(',', $arrField) . ', oi.regDt';
        $this->db->strWhere = implode(' AND ', $arrWhere);
        $this->db->strGroup = 'oi.groupCd';

        // 쿼리 생성
        $query = $this->db->query_complete();
        $strSQL = 'SELECT ' . array_shift($query) . ' FROM ' . DB_ORDER_INVOICE . ' AS oi ' . implode(' ', $query);
        $getData = $this->db->query_fetch($strSQL, $arrBind, false);

        return gd_htmlspecialchars_stripslashes($getData);
    }

    /**
     * 일괄송장등록 상세리스트
     *
     * @param integer $groupCd 그룹코드
     * @param null $completeFl 성공여부
     *
     * @return string
     */
    public function getOrderInvoiceView($groupCd, $completeFl = null)
    {
        // 일괄등록 정보 보기
        $getData['info'] = $this->getOrderInvoiceInfo($groupCd);

        // 추출 필드
        $arrIncludeSm = [
            'companyNm',
        ];
        $arrIncludeM = [
            'managerId',
            'managerNm',
        ];
        $arrIncludeOg = [
            'deliveryDt',
            'deliveryCompleteDt',
            'invoiceCompanySno',
            'invoiceNo',
        ];
        $tmpField[] = DBTableField::setTableField('tableScmManage', $arrIncludeSm, null, 'sm');
        $tmpField[] = DBTableField::setTableField('tableManager', $arrIncludeM, null, 'm');
        $tmpField[] = DBTableField::setTableField('tableOrderInvoice', null, ['invoiceCompanySno','invoiceNo','deliveryDt','deliveryCompleteDt'], 'oi');
        $viewField  = null;
        foreach($arrIncludeOg as $field){
            //invocie테이블 정보부터 확인 후 없으면 orderGoods테이블로 대체
            if($field == 'deliveryDt' || $field == 'deliveryCompleteDt') {
                $viewField[] = sprintf("if(oi.%s!='',oi.%s, %s) as %s", $field, $field, "IF(og.".$field."!='0000-00-00 00:00:00', og.".$field.", '')", $field);
            }
            else {
                $viewField[] = sprintf("if(oi.%s!='',oi.%s, og.%s) as %s", $field, $field, $field, $field);
            }
        }
        $tmpField[] = $viewField;
        // join 문
        $join[] = ' LEFT JOIN ' . DB_ORDER_GOODS . ' AS og ON oi.orderGoodsNo = og.sno ';
        $join[] = ' LEFT JOIN ' . DB_MANAGER . ' AS m ON oi.managerNo = m.sno ';
        $join[] = ' LEFT JOIN ' . DB_SCM_MANAGE . ' AS sm ON oi.scmNo = sm.scmNo ';

        // 쿼리용 필드 합침
        $tmpKey = array_keys($tmpField);
        $arrField = [];
        foreach ($tmpKey as $key) {
            $arrField = array_merge($arrField, $tmpField[$key]);
        }
        unset($tmpField, $tmpKey);

        // 쿼리 조건
        $arrWhere[] = 'oi.groupCd=?';
        $this->db->bind_param_push($arrBind, 's', $groupCd);

        if ($completeFl !== null && $completeFl != '') {
            $arrWhere[] = 'oi.completeFl=?';
            $this->db->bind_param_push($arrBind, 's', $completeFl);
        }

        // 쿼리 생성
        $this->db->strJoin = implode(' ', $join);
        $this->db->strField = implode(',', $arrField);
        $this->db->strWhere = implode(' AND ', $arrWhere);

        // 쿼리 생성
        $query = $this->db->query_complete();
        $strSQL = 'SELECT ' . array_shift($query) . ' FROM ' . DB_ORDER_INVOICE . ' AS oi ' . implode(' ', $query);
        $getData['data'] = $this->db->query_fetch($strSQL, $arrBind);


        //        $delivery = App::load(\Component\Delivery\Delivery::class);
        //        $tmpDelivery = $delivery->getDeliveryCompany(null, true);

        return gd_htmlspecialchars_stripslashes($getData);
    }


    /**
     * 자주쓰는 주소의 그룹 가져오기
     *
     * @return null
     * @author Jong-tae Ahn <qnibus@godo.co.kr>
     */
    public function getFrequencyAddressGroup()
    {
        // 출력 필드
        $this->db->strField = 'groupNm';
        $this->db->strGroup = 'groupNm';

        // 쿼리 생성
        $query = $this->db->query_complete();
        $strSQL = 'SELECT ' . array_shift($query) . ' FROM ' . DB_ORDER_FREQUENCY_ADDRESS . ' ' . implode(' ', $query);
        $getData = $this->db->query_fetch($strSQL, null, true);

        // 키에 값 넣기
        if (empty($getData) === false) {
            $getData = ArrayUtils::changeKeyValue(array_column($getData, 'groupNm'));
            array_unshift($getData, '=' . __('통합그룹') . '=');
        } else {
            $getData = ['=' . __('통합그룹') . '='];
        }

        return $getData;
    }

    /**
     * 자주쓰는 주소 리스트 가져오기
     *
     * @param $searchData
     *
     * @author Jong-tae Ahn <qnibus@godo.co.kr>
     */
    public function getFrequencyAddress($searchData)
    {
        // 통합 검색
        $this->search['combineSearch'] = [
            'all' => '=' . __('통합검색') . '=',
            'ofa.groupNm' => __('그룹'),
            'ofa.name' => __('이름'),
            'ofa.email' => __('이메일'),
            'ofa.phone' => __('전화번호'),
            'ofa.cellPhone' => __('휴대폰번호'),
            'ofa.memo' => __('메모'),
        ];

        // 그룹
        $this->search['combineGroup'] = $this->getFrequencyAddressGroup();

        // 기간검색
        $this->search['combineTreatDate'] = [
            'ofa.regDt' => __('등록일'),
        ];

        // --- 정렬
        $this->search['sortList'] = [
            'ofa.regDt desc' => sprintf('%s↓', __('등록일')),
            'ofa.regDt asc' => sprintf('%s↑', __('등록일')),
            'ofa.group desc' => sprintf('%s↓', __('그룹')),
            'ofa.group asc' => sprintf('%s↑', __('그룹')),
            'ofa.name desc' => sprintf('%s↓', __('이름')),
            'ofa.name asc' => sprintf('%s↑', __('이름')),
        ];

        // --- $searchData trim 처리
        if (isset($searchData)) {
            gd_trim($searchData);
        }
        gd_isset($searchData['periodFl'], $searchData['searchPeriod']);
        gd_isset($searchData['treatDate'][0], $searchData['treatDate']['start']);
        gd_isset($searchData['treatDate'][1], $searchData['treatDate']['end']);
        if (empty($searchData['periodFl']) === true) {
            $searchData['periodFl'] = $searchData['searchPeriod'];
        }

        // 다른페이지 검색 분기 처리를 위한 변수
        $this->search['searchMode'] = gd_isset($searchData['searchMode'], 'y');

        // 검색 설정
        $this->search['key'] = gd_isset($searchData['key']);
        $this->search['keyword'] = gd_isset($searchData['keyword']);
        $this->search['group'] = gd_isset($searchData['group']);
        $this->search['periodFl'] = gd_isset($searchData['periodFl'], 6);
        $this->search['treatDateFl'] = gd_isset($searchData['treatDateFl'], 'ofa.regDt');
        $this->search['treatDate'][] = gd_isset($searchData['treatDate'][0], date('Y-m-d', strtotime('-' . $this->search['periodFl'] . ' day')));
        $this->search['treatDate'][] = gd_isset($searchData['treatDate'][1], date('Y-m-d'));
        $this->search['sort'] = gd_isset($searchData['sort'], array_keys($this->search['sortList'])[0]);
        $setData['search'] = $this->search;

        // 체크 설정
        $this->checked['periodFl'][$this->search['periodFl']] = 'active';
        $setData['checked'] = $this->checked;

        // 그룹 검색
        if ($this->search['group']) {
            $this->arrWhere[] = 'ofa.groupNm = ?';
            $this->db->bind_param_push($this->arrBind, 's', $this->search['group']);
        }

        // 키워드 검색
        if ($this->search['key'] && $this->search['keyword']) {
            if ($this->search['key'] == 'all') {
                $tmpWhere = array_keys($this->search['combineSearch']);
                array_shift($tmpWhere);
                $arrWhereAll = [];
                foreach ($tmpWhere as $keyNm) {
                    $arrWhereAll[] = '(' . $keyNm . ' LIKE concat(\'%\',?,\'%\'))';
                    $this->db->bind_param_push($this->arrBind, 's', $this->search['keyword']);
                }
                $this->arrWhere[] = '(' . implode(' OR ', $arrWhereAll) . ')';
                unset($tmpWhere);
            } else {
                $this->arrWhere[] = $this->search['key'] . ' LIKE concat(\'%\',?,\'%\')';
                $this->db->bind_param_push($this->arrBind, 's', $this->search['keyword']);
            }
        }

        // 처리일자 검색
        if ($this->search['searchMode'] == 'y' && $this->search['treatDateFl'] && $this->search['treatDate'][0] && $this->search['treatDate'][1]) {
            $this->arrWhere[] = $this->search['treatDateFl'] . ' BETWEEN ? AND ?';
            $this->db->bind_param_push($this->arrBind, 's', $this->search['treatDate'][0] . ' 00:00:00');
            $this->db->bind_param_push($this->arrBind, 's', $this->search['treatDate'][1] . ' 23:59:59');
        }

        // --- 페이지 기본설정
        gd_isset($searchData['page'], 1);
        gd_isset($searchData['pageNum'], 15);

        $page = \App::load('\\Component\\Page\\Page', $searchData['page']);
        $page->page['list'] = $searchData['pageNum']; // 페이지당 리스트 수
        $page->setPage();
        $page->setUrl(\Request::getQueryString());

        // 검색 데이터
        $arrIncludeOfa = null;

        // 출력 필드설정
        $tmpField[] = DBTableField::setTableField('tableOrderFrequencyAddress', $arrIncludeOfa, null, 'ofa');

        // 쿼리용 필드 합침
        $tmpKey = array_keys($tmpField);
        $arrField = [];
        foreach ($tmpKey as $key) {
            $arrField = array_merge($arrField, $tmpField[$key]);
        }
        unset($tmpField, $tmpKey);

        // 현 페이지 결과
        $this->db->strField = 'ofa.sno, ' . implode(', ', $arrField) . ', ofa.regDt';
        $this->db->strWhere = implode(' AND ', gd_isset($this->arrWhere));
        $this->db->strOrder = $this->search['sort'];
        $this->db->strLimit = $page->recode['start'] . ',' . $searchData['pageNum'];

        $query = $this->db->query_complete();
        $strSQL = 'SELECT ' . array_shift($query) . ' FROM ' . DB_ORDER_FREQUENCY_ADDRESS . ' ofa ' . implode(' ', $query);
        $getData = $this->db->query_fetch($strSQL, $this->arrBind);

        // 개행문자 노출 제거 처리(메모)
        foreach($getData as $idx => $Dval) {
            $getData[$idx]['memo'] = str_replace("\\r\\n", '<br />', $Dval['memo']);
        }

        // '입력시 \ 노출 제거 처리(메모)
        foreach($getData as $i => $val) {
            $getData[$i]['memo'] = StringUtils::htmlSpecialCharsStripSlashes($val['memo']);
        }

        $setData['data'] = $getData;

        // 검색 레코드 수
        unset($query['group'], $query['order'], $query['limit']);
        $strCntSQL = 'SELECT COUNT(*) AS total FROM ' . DB_ORDER_FREQUENCY_ADDRESS . ' AS ofa ' . implode(' ', $query);
        $page->recode['total'] = $this->db->query_fetch($strCntSQL, $this->arrBind, false)['total'];
        //list($page->recode['total']) = $this->db->fetch('SELECT FOUND_ROWS()', 'row');
        $total = $this->db->fetch('SELECT count(*) as total FROM ' . DB_ORDER_FREQUENCY_ADDRESS . ' ofa');
        $page->recode['amount'] = $total['total'];
        //        $page->recode['amount'] = $this->db->table_status(DB_ORDER, 'Rows'); // 전체 레코드 수
        $page->setPage();
        $setData['page'] = $page;

        return $setData;
    }


    public function getFrequencyAddressView($sno)
    {
        if (!is_numeric($sno)) {
            throw new Exception(__('SNO가 일치하지 않습니다.'));
        }

        $arrBind = null;
        $arrField = DBTableField::setTableField('tableOrderFrequencyAddress', null, null, 'ofa');

        $arrWhere[] = 'ofa.sno=?';
        $this->db->bind_param_push($arrBind, 's', $sno);

        // 쿼리 결과
        $this->db->strField = implode(',', $arrField) . ', ofa.sno';
        $this->db->strWhere = implode(' AND ', $arrWhere);
        $query = $this->db->query_complete();
        $strSQL = 'SELECT ' . array_shift($query) . ' FROM ' . DB_ORDER_FREQUENCY_ADDRESS . ' AS ofa ' . implode(' ', $query);
        $getData = $this->db->query_fetch($strSQL, $arrBind, false);

        // 데이터 개행문자 노출 제거 처리(메모)
        $getData['memo'] = str_replace('\\r\\n', "\r\n", $getData['memo']);

        // 데이터 '입력시 \ 노출 제거 처리(메모)
        $getData['memo'] = StringUtils::htmlSpecialCharsStripSlashes($getData['memo']);

        // 데이터 재설정 (전화/이메일/사업자번호/발행이메일)
        $getData['email'] = explode('@', $getData['email']);
        $getData['phone'] = explode('-', $getData['phone']);
        $getData['cellPhone'] = explode('-', $getData['cellPhone']);
        $getData['businessNo'] = explode('-', $getData['businessNo']);
        $getData['bEmail'] = explode('@', $getData['bEmail']);

        return $getData;
    }

    /**
     * 자주쓰는 주소 등록/수정
     *
     * @param array $data
     *
     * @author Jong-tae Ahn <qnibus@godo.co.kr>
     */
    public function registerFrequencyAddress($data)
    {
        // 그룹추가 (추후 로직 생기면 사용)
        if ($data['tmpCheckedGroup'] == 'new') {

        }

        // 데이터 재설정
        unset($data['mode']);
        $data['email'] = implode('@', $data['email']);
        $data['phone'] = StringUtils::numberToPhone($data['phone']);
        $data['cellPhone'] = StringUtils::numberToPhone($data['cellPhone']);
        $data['managerNo'] = Session::get('manager.sno');
        $data['businessNo'] = implode('-', $data['businessNo']);
        $data['bEmail'] = implode('@', $data['bEmail']);

        // SNO 있는 경우 수정 처리 or 없는 경우 등록
        if ($data['sno'] != '' && is_numeric($data['sno']) && strlen($data['sno']) > 0) {
            $compareField = array_keys($data);
            $arrBind = $this->db->get_binding(DBTableField::tableOrderFrequencyAddress(), $data, 'update', $compareField);
            $this->db->bind_param_push($arrBind['bind'], 's', $data['sno']);
            $this->db->set_update_db(DB_ORDER_FREQUENCY_ADDRESS, $arrBind['param'], 'sno = ?', $arrBind['bind']);
            unset($arrBind);

        } else {
            // 데이터 insert
            $compareField = array_keys($data);
            $arrBind = $this->db->get_binding(DBTableField::tableOrderFrequencyAddress(), $data, 'insert', $compareField);
            $this->db->set_insert_db(DB_ORDER_FREQUENCY_ADDRESS, $arrBind['param'], $arrBind['bind'], 'y');
            unset($arrBind);

            return $this->db->insert_id();
        }

        return true;
    }

    /**
     * 자주쓰는 주소 삭제
     *
     * @param $sno
     *
     * @author Jong-tae Ahn <qnibus@godo.co.kr>
     */
    public function deleteFrequencyAddress($sno)
    {
        $arrBind = [];
        $this->db->bind_param_push($arrBind, 's', $sno);
        $this->db->set_delete_db(DB_ORDER_FREQUENCY_ADDRESS, 'sno = ?', $arrBind);
        unset($arrBind);
    }

    /**
     * 수기주문 정보 저장
     *
     * {@inheritdoc}
     *
     */
    public function saveOrderInfo($cartInfo, $orderInfo, $orderPrice)
    {
        // 주문서 기본 저장 변수
        $orderPrice['memNo'] = $orderInfo['memNo'];

        // 수기주문 타입 지정
        $orderPrice['orderTypeFl'] = 'write';

        // 공통 주문저장 로직 실행
        $this->saveOrder($cartInfo, $orderInfo, $orderPrice, true);

        return true;
    }


    /**
     * 관리자 주문 리스트 엑셀
     * 반품/교환/환불 정보까지 한번에 가져올 수 있게 되어있다.
     *
     * @param string $searchData 검색 데이타
     * @param string $searchPeriod 기본 조회 기간
     *
     * @return array 주문 리스트 정보
     */
    public function getOrderListForAdminExcel($searchData, $searchPeriod, $isUserHandle = false, $orderType='goods',$excelField,$page,$pageLimit)
    {
        unset($this->arrWhere);
        unset($this->arrBind);
        //$excelField  / $page / $pageLimit 해당 정보가 없을경우 튜닝한 업체이므로 기존형태로 반환해줘야함
        // --- 검색 설정
        $this->_setSearch($searchData, $searchPeriod, $isUserHandle);

        if ($searchData['statusCheck'] && is_array($searchData['statusCheck'])) {
            foreach ($searchData['statusCheck'] as $key => $val) {
                foreach ($val as $k => $v) {
                    $_tmp = explode(INT_DIVISION, $v);
                    if($orderType =='goods' && $searchData['view'] =='order') unset($_tmp[1]);
                    if($_tmp[1]) {
                        $tmpWhere[] = "(og.orderNo = ? AND og.sno = ?)";
                        $this->db->bind_param_push($this->arrBind, 's', $_tmp[0]);
                        $this->db->bind_param_push($this->arrBind, 's', $_tmp[1]);
                    } else {
                        $tmpWhere[] = "(og.orderNo = ?)";
                        $this->db->bind_param_push($this->arrBind, 's', $_tmp[0]);
                    }
                }
            }

            $this->arrWhere[] = '(' . implode(' OR ', $tmpWhere) . ')';
            unset($tmpWhere);
        }

        // 주문상태 정렬 예외 케이스 처리
        if ($searchData['sort'] == 'og.orderStatus asc') {
            $searchData['sort'] = 'case LEFT(og.orderStatus, 1) when \'o\' then \'1\' when \'p\' then \'2\' when \'g\' then \'3\' when \'d\' then \'4\' when \'s\' then \'5\' when \'e\' then \'6\' when \'b\' then \'7\' when \'r\' then \'8\' when \'c\' then \'9\' when \'f\' then \'10\' else \'11\' end';
        } elseif ($searchData['sort'] == 'og.orderStatus desc') {
            $searchData['sort'] = 'case LEFT(og.orderStatus, 1) when \'f\' then \'1\' when \'c\' then \'2\' when \'r\' then \'3\' when \'b\' then \'4\' when \'e\' then \'5\' when \'s\' then \'6\' when \'d\' then \'7\' when \'g\' then \'8\' when \'p\' then \'9\' when \'o\' then \'10\' else \'11\' end';
        }

        // 정렬 설정
        if($orderType === 'goods'){
            $orderSort = gd_isset($searchData['sort'], $this->orderGoodsMultiShippingOrderBy);
        }
        else {
            $orderSort = gd_isset($searchData['sort'], $this->orderGoodsOrderBy);
        }
        if($orderType === 'goods'){
            if(!preg_match("/orderInfoCd/", $orderSort)){
                $orderSort = $orderSort . ", oi.orderInfoCd asc";
            }
        }

        // 사용 필드
        $arrInclude = [
            'o.orderNo',
            'o.orderChannelFl',
            'o.apiOrderNo',
            'o.memNo',
            'o.orderChannelFl',
            'o.orderGoodsNm',
            'o.orderGoodsCnt',
            'o.settlePrice as totalSettlePrice',
            'o.totalDeliveryCharge',
            'o.useDeposit as totalUseDeposit',
            'o.useMileage as totalUseMileage',
            '(o.totalMemberDcPrice + o.totalMemberDeliveryDcPrice) AS totalMemberDcPrice',
            'o.totalGoodsDcPrice',
            '(o.totalCouponGoodsDcPrice + o.totalCouponOrderDcPrice + o.totalCouponDeliveryDcPrice)as totalCouponDcPrice',
            'totalCouponOrderDcPrice',
            'totalCouponDeliveryDcPrice',
            'o.totalMileage',
            'o.totalGoodsMileage',
            'o.totalMemberMileage',
            '(o.totalCouponGoodsMileage+o.totalCouponOrderMileage) as totalCouponMileage',
            'o.settleKind',
            'o.bankAccount',
            'o.bankSender',
            'o.receiptFl',
            'o.pgResultCode',
            'o.pgTid',
            'o.pgAppNo',
            'o.paymentDt',
            'o.addField',
            'o.mallSno',
            'o.orderGoodsNmStandard',
            'o.overseasSettlePrice',
            'o.currencyPolicy',
            'o.exchangeRatePolicy',
            'o.totalEnuriDcPrice',
            '(o.realTaxSupplyPrice + o.realTaxVatPrice + o.realTaxFreePrice) AS totalRealSettlePrice',
            'o.checkoutData',
            'o.trackingKey',
            'o.fintechData',
            'o.checkoutData',
            'o.orderTypeFl',
            'o.appOs',
            'o.pushCode',
            'o.memberPolicy',
            'o.totalMyappDcPrice',
            'oi.regDt as orderDt',
            'oi.orderName',
            'oi.orderEmail',
            'oi.orderPhone',
            'oi.orderCellPhone',
            'oi.receiverName',
            'oi.receiverPhone',
            'oi.receiverCellPhone',
            'oi.receiverUseSafeNumberFl',
            'oi.receiverSafeNumber',
            'oi.receiverSafeNumberDt',
            'oi.receiverZonecode',
            'oi.receiverZipcode',
            'oi.receiverAddress',
            'oi.receiverAddressSub',
            'oi.receiverCity',
            'oi.receiverState',
            'oi.receiverCountryCode',
            'oi.orderMemo',
            'oi.packetCode',
            'oi.orderInfoCd',
            'oi.visitName',
            'oi.visitPhone',
            'oi.visitMemo',
            '(og.orderDeliverySno) AS orderDeliverySno ',
            '(og.scmNo) AS scmNo ',
            '(og.apiOrderGoodsNo) AS apiOrderGoodsNo ',
            '(og.sno) AS orderGoodsSno ',
            '(og.orderCd) AS orderCd ',
            '(og.orderStatus) AS orderStatus ',
            '(og.goodsNo) AS goodsNo ',
            '(og.goodsCd) AS goodsCd ',
            '(og.goodsModelNo) AS goodsModelNo ',
            '(og.goodsNm) AS goodsNm ',
            '(og.optionInfo) AS optionInfo ',
            '(og.goodsCnt) AS goodsCnt ',
            '(og.goodsWeight) AS goodsWeight ',
            '(og.goodsVolume) AS goodsVolume ',
            '(og.cateCd) AS cateCd ',
            '(og.goodsCnt) AS goodsCnt ',
            '(og.brandCd) AS brandCd ',
            '(og.makerNm) AS makerNm ',
            '(og.originNm) AS originNm ',
            '(og.addGoodsCnt) AS addGoodsCnt ',
            '(og.optionTextInfo) AS optionTextInfo ',
            '(og.goodsTaxInfo) AS goodsTaxInfo ',
            '(og.goodsPrice) AS goodsPrice ',
            '(og.fixedPrice) AS fixedPrice ',
            '(og.costPrice) AS costPrice ',
            '(og.commission) AS commission ',
            '(og.optionPrice) AS optionPrice ',
            '(og.optionCostPrice) AS optionCostPrice ',
            '(og.optionTextPrice) AS optionTextPrice ',
            '(og.invoiceCompanySno) AS invoiceCompanySno ',
            '(og.invoiceNo) AS invoiceNo ',
            '(og.deliveryCompleteDt) AS deliveryCompleteDt ',
            '(og.visitAddress) AS visitAddress ',
            'og.goodsDeliveryCollectFl',
            'og.deliveryMethodFl',
            'og.goodsNmStandard',
            'og.goodsMileage',
            'og.memberMileage',
            'og.couponGoodsMileage',
            'og.divisionUseDeposit',
            'og.divisionUseMileage',
            'og.divisionCouponOrderDcPrice',
            'og.goodsDcPrice',
            '(og.memberDcPrice+og.memberOverlapDcPrice+od.divisionMemberDeliveryDcPrice) as memberDcPrice',
            'og.memberDcPrice as orgMemberDcPrice',
            'og.memberOverlapDcPrice as orgMemberOverlapDcPrice',
            'og.goodsDiscountInfo',
            'og.myappDcPrice',
            '(og.couponGoodsDcPrice + og.divisionCouponOrderDcPrice) as couponGoodsDcPrice',
            '(og.goodsTaxInfo) AS addGoodsTaxInfo ',
            '(og.commission) AS addGoodsCommission ',
            '(og.goodsPrice) AS addGoodsPrice ',
            'og.timeSalePrice',
            'og.finishDt',
            'og.deliveryDt',
            'og.deliveryCompleteDt',
            'og.goodsType',
            'og.hscode',
            'og.checkoutData AS og_checkoutData',
            'og.enuri',
            'oh.handleReason',
            'oh.handleDetailReason',
            'oh.refundMethod',
            'oh.refundBankName',
            'oh.refundAccountNumber',
            'oh.refundDepositor',
            'oh.refundPrice',
            'oh.refundDeliveryCharge',
            'oh.refundDeliveryInsuranceFee',
            'oh.refundUseDeposit',
            'oh.refundUseMileage',
            'oh.refundUseDepositCommission',
            'oh.refundUseMileageCommission',
            'oh.completeCashPrice',
            'oh.completePgPrice',
            'oh.completeCashPrice',
            'oh.completeDepositPrice',
            'oh.completeMileagePrice',
            'oh.refundCharge',
            'oh.refundUseDeposit',
            'oh.refundUseMileage',
            'oh.regDt as handleRegDt',
            'oh.handleDt',
            'od.deliveryCharge',
            'od.orderInfoSno',
            'od.deliveryPolicyCharge',
            'od.deliveryAreaCharge',
            'od.realTaxSupplyDeliveryCharge',
            'od.realTaxVatDeliveryCharge',
            'od.realTaxFreeDeliveryCharge',
            'od.divisionDeliveryUseMileage',
            'od.divisionDeliveryUseDeposit',
        ];
        if($searchData['statusMode'] === 'o'){
            // 입금대기리스트에서 '주문상품명' 을 입금대기 상태의 주문상품명만으로 노출시키기 위해 개수를 구함
            $arrInclude[] = 'SUM(IF(LEFT(og.orderStatus, 1)=\'o\', 1, 0)) AS noPay';
        }

        // join 문
        $join[] = ' LEFT JOIN ' . DB_ORDER . ' o ON o.orderNo = og.orderNo ';
        $join[] = ' LEFT JOIN ' . DB_ORDER_HANDLE . ' oh ON og.handleSno = oh.sno AND og.orderNo = oh.orderNo ';
        $join[] = ' LEFT JOIN ' . DB_ORDER_DELIVERY . ' od ON og.orderDeliverySno = od.sno ';
        $join[] = ' LEFT JOIN ' . DB_ORDER_INFO . ' oi ON (og.orderNo = oi.orderNo) 
                    AND (CASE WHEN od.orderInfoSno > 0 THEN od.orderInfoSno = oi.sno ELSE oi.orderInfoCd = 1 END)';

        //매입처
        if((($this->search['key'] =='all' && empty($this->search['keyword']) === false)  || $this->search['key'] =='pu.purchaseNm' || empty($excelField) === true || in_array("purchaseNm",array_values($excelField))) && gd_is_plus_shop(PLUSSHOP_CODE_PURCHASE) === true && gd_is_provider() === false) {
            $arrIncludePurchase =[
                'pu.purchaseNm'
            ];

            $arrInclude = array_merge($arrInclude, $arrIncludePurchase);
            $join[] = ' LEFT JOIN ' . DB_PURCHASE . ' pu ON og.purchaseNo = pu.purchaseNo ';
            unset($arrIncludePurchase);
        }

        //공급사
        if(in_array("scmNm",array_values($excelField)) || in_array("scmNo",array_values($excelField)) || empty($excelField) === true || empty($searchData['scmFl']) === false || ($searchData['key'] =='all' && $searchData['keyword'])) {
            $arrIncludeScm =[
                'sm.companyNm as scmNm'
            ];

            $arrInclude = array_merge($arrInclude, $arrIncludeScm);
            $join[] = ' LEFT JOIN ' . DB_SCM_MANAGE . ' sm ON og.scmNo = sm.scmNo ';
            unset($arrIncludeScm);
        }

        //회원
        if(in_array("memNo",array_values($excelField)) || in_array("memNm",array_values($excelField)) ||  in_array("groupNm",array_values($excelField)) || empty($excelField) === true || $searchData['memFl'] || ($searchData['key'] =='all' && $searchData['keyword'])) {
            $arrIncludeMember =[
                'IF(m.memNo > 0, m.memNm, oi.orderName) AS memNm',
                'm.memId',
                'mg.groupNm',
            ];

            $arrInclude = array_merge($arrInclude, $arrIncludeMember);
            $join[] = ' LEFT JOIN ' . DB_MEMBER . ' m ON o.memNo = m.memNo ';
            $join[] = ' LEFT OUTER JOIN ' . DB_MEMBER_GROUP . ' mg ON m.groupSno = mg.sno ';
            unset($arrIncludeMember);
        }

        //사은품
        if(in_array("oi.presentSno",array_values($excelField)) || empty($excelField) === true || in_array("ogi.giftNo",array_values($excelField))) {
            $arrIncludeGift =[
                'GROUP_CONCAT(ogi.presentSno SEPARATOR "/") AS presentSno ',
                'GROUP_CONCAT(ogi.giftNo SEPARATOR "/") AS giftNo '
            ];

            $arrInclude = array_merge($arrInclude, $arrIncludeGift);

            $join[] = ' LEFT JOIN ' . DB_ORDER_GIFT . ' ogi ON ogi.orderNo = o.orderNo ';
            unset($arrIncludeGift);
        }

        //상품 브랜드 코드 검색
        if(empty($this->search['brandCd']) === false || empty($excelField) === true || empty($this->search['brandNoneFl'])=== false) {
            $join[] = ' LEFT JOIN ' . DB_GOODS . ' as g ON og.goodsNo = g.goodsNo ';
        }

        //택배 예약 상태에 따른 검색
        if ($this->search['invoiceReserveFl']) {
            $join[] = ' LEFT JOIN ' . DB_ORDER_GODO_POST . ' ogp ON ogp.invoiceNo = og.invoiceNo ';
        }

        // 쿠폰검색시만 join
        if ($this->search['couponNo'] > 0) {
            $join[] = ' LEFT JOIN ' . DB_ORDER_COUPON . ' oc ON o.orderNo = oc.orderNo ';
            $join[] = ' LEFT JOIN ' . DB_MEMBER_COUPON . ' mc ON mc.memberCouponNo = oc.memberCouponNo ';
        }

        // 반품/교환/환불신청 사용에 따른 리스트 별도 처리 (조건은 검색 메서드 참고)
        if ($isUserHandle) {
            $arrIncludeOuh = [
                'count(ouh.sno) as totalClaimCnt',
                'userHandleReason',
                'userHandleDetailReason',
                'userRefundAccountNumber',
                'adminHandleReason',
                'ouh.regDt AS userHandleRegDt'
            ];
            $join[] = ' LEFT JOIN ' . DB_ORDER_USER_HANDLE . ' ouh ON og.userHandleSno = ouh.sno ';

            $arrInclude = array_merge($arrInclude, $arrIncludeOuh);
            unset($arrIncludeOuh);
        }
        // @kookoo135 고객 클레임 신청 주문 제외
        if ($this->search['userHandleViewFl'] == 'y') {
            $this->arrWhere[] = ' NOT EXISTS (SELECT 1 FROM ' . DB_ORDER_USER_HANDLE . ' WHERE (og.userHandleSno = sno OR og.sno = userHandleGoodsNo) AND userHandleFl = \'r\')';
        }

        // 현 페이지 결과
        if($page =='0') {
            $this->db->strField = 'og.orderNo';
            $this->db->strJoin = implode('', $join);
            $this->db->strWhere = implode(' AND ', gd_isset($this->arrWhere));
            if($orderType =='goods') $this->db->strGroup = "CONCAT(og.orderNo,og.orderCd,og.goodsNo)";
            else  $this->db->strGroup = "CONCAT(og.orderNo)";

            //총갯수관련
            $query = $this->db->query_complete();
            $strSQL = 'SELECT ' . array_shift($query) . ' FROM ' . DB_ORDER_GOODS . ' og ' . implode(' ', $query);
            $result['totalCount'] = $this->db->query_fetch($strSQL, $this->arrBind);
        }

        $this->db->strField = implode(', ', $arrInclude).",totalGoodsPrice";
        $this->db->strJoin = implode('', $join);
        $this->db->strWhere = implode(' AND ', gd_isset($this->arrWhere));
        if($orderType =='goods') $this->db->strGroup = "CONCAT(og.orderNo,og.orderCd,og.goodsNo)";
        else  $this->db->strGroup = "CONCAT(og.orderNo)";
        $this->db->strOrder = $orderSort;
        if($pageLimit) $this->db->strLimit = (($page * $pageLimit)) . "," . $pageLimit;
        $query = $this->db->query_complete();
        $strSQL = 'SELECT ' . array_shift($query) . ' FROM ' . DB_ORDER_GOODS . ' og ' . implode(' ', $query);
        if(empty($excelField) === false) {
            if (Manager::isProvider()) {
                $result['orderList'] = $this->db->query_fetch($strSQL, $this->arrBind);
            }
            else {
                $result['orderList'] = $this->db->query_fetch_generator($strSQL, $this->arrBind);
            }
        }
        else {
            $result = $this->db->query_fetch($strSQL, $this->arrBind);
        }

        if (Manager::isProvider()) {
            $result = $this->getProviderTotalPriceExcelList($result, $orderType);
        }

        return $result;

    }

    /**
     * 우체국 택배 연동 관리자 주문 리스트
     * 반품/교환/환불 정보까지 한번에 가져올 수 있게 되어있다.
     *
     * @param string $searchData 검색 데이타
     * @param string $searchPeriod 기본 조회 기간
     *
     * @return array 주문 리스트 정보
     */

    /**
     * 우체국 택배 연동 관리자 주문 리스트
     * 반품/교환/환불 정보까지 한번에 가져올 수 있게 되어있다.
     *
     * @param string $searchData 검색 데이타
     * @param string $searchPeriod 기본 조회 기간
     *
     * @return array 주문 리스트 정보
     */
    public function getOrderGodoPostListForAdmin($searchData, $searchPeriod, $isReserve = false)
    {
        // GODOPOST 예약 상태

        $this->search['deliveryListFl'] = gd_isset($searchData['deliveryListFl'], 'o');
        $this->search['invoiceReserveFl'] = gd_isset($searchData['invoiceReserveFl'], '');
        $this->search['deliveryFl'] = true;


        if ($this->search['invoiceReserveFl']) {
            if ($this->search['invoiceReserveFl'] == 'y') $this->arrWhere[] = 'ogp.reserveFl = "y"';
            else $this->arrWhere[] = 'ogp.reserveFl = "n"';
            $this->checked['invoiceReserveFl'][$this->search['invoiceReserveFl']] = 'checked="checked"';
        } else {
            $this->checked['invoiceReserveFl'][''] = 'checked="checked"';
        }

        $this->checked['deliveryListFl'][$this->search['deliveryListFl']] = 'checked="checked"';

        $setData = $this->getOrderListForAdmin($searchData, $searchPeriod);

        if ($isReserve == false) {

            if($searchData['deliveryListFl'] =='o') {
                $deliveryField = 'count(DISTINCT orderNo) as count';
            } else {
                $deliveryField = 'count(orderNo) as count';
            }

            $strCountSQL ='SELECT '.$deliveryField.' FROM ' . DB_ORDER_GOODS . '  WHERE TRIM(invoiceNo) = "" AND sno IN ("'.implode('","',array_column($setData, 'sno')).'")';
            $tmpCountData = $this->db->query_fetch($strCountSQL);
            $deliveryNoneCount = $tmpCountData[0]['count'];
        }

        $setData['deliveryNoneCount'] = $deliveryNoneCount;

        return $setData;
    }

    /**
     * 우체국 택배 연동 송장번호 발급
     *
     * @param        $arrData
     * @param string $searchPeriod
     *
     * @internal param string $getValue 파라미터
     */
    public function saveOrderInvoice($arrData, $searchPeriod = '7')
    {
        parse_str($arrData['whereDetail'], $arrData['whereDetail']);

        $arrData['whereDetail']['statusMode'] = "p,g";
        // --- 검색 설정
        $this->_setSearch($arrData['whereDetail'], $searchPeriod, false);

        $this->search['deliveryListFl'] = gd_isset($arrData['deliveryListFl'], 'o');
        $this->search['invoiceReserveFl'] = gd_isset($arrData['invoiceReserveFl'], '');

        // GODOPOST 예약 상태
        if ($this->search['invoiceReserveFl']) {
            if ($this->search['invoiceReserveFl'] == 'y') $this->arrWhere[] = 'ogp.reserveFl = "y"';
            else $this->arrWhere[] = 'ogp.reserveFl = "n"';
        }

        if ($arrData['godoPostSendFl'] == 'all') {
            $this->arrWhere[] = "TRIM(og.invoiceNo) = ''";
        } else {
            $tmpWhere = "";
            foreach ($arrData['statusCheck'] as $key => $val) {
                $_tmp = explode(INT_DIVISION, $val);
                if ($arrData['whereDetail']['deliveryListFl'] == 'g') $tmpWhere[] = "(og.orderNo = '" . $_tmp[0] . "' AND og.sno = '" . $_tmp[1] . "')";
                else  $tmpWhere[] = "og.orderNo = '" . $_tmp[0] . "'";
            }

            $this->arrWhere[] = '(' . implode(' OR ', $tmpWhere) . ')';
        }

        // join 문
        $join[] = ' LEFT JOIN ' . DB_ORDER . ' o ON o.orderNo = og.orderNo ';
        $join[] = ' LEFT JOIN ' . DB_ORDER_HANDLE . ' oh ON og.handleSno = oh.sno AND og.orderNo = oh.orderNo ';
        $join[] = ' LEFT JOIN ' . DB_GOODS . ' as g ON og.goodsNo = g.goodsNo ';
        $join[] = ' LEFT JOIN ' . DB_SCM_MANAGE . ' sm ON og.scmNo = sm.scmNo ';
        $join[] = ' LEFT JOIN ' . DB_ORDER_DELIVERY . ' od ON og.orderDeliverySno = od.sno ';
        $join[] = ' LEFT JOIN ' . DB_ORDER_INFO . ' oi ON (og.orderNo = oi.orderNo) 
                    AND (CASE WHEN od.orderInfoSno > 0 THEN od.orderInfoSno = oi.sno ELSE oi.orderInfoCd = 1 END) ';
        $join[] = ' LEFT JOIN ' . DB_MEMBER . ' m ON o.memNo = m.memNo AND m.memNo > 0 ';
        $join[] = ' LEFT OUTER JOIN ' . DB_MEMBER_GROUP . ' mg ON m.groupSno = mg.sno ';

        if((($this->search['key'] =='all' && empty($this->search['keyword']) === false)  || $this->search['key'] =='pu.purchaseNm') && gd_is_plus_shop(PLUSSHOP_CODE_PURCHASE) === true && gd_is_provider() === false) {
            $join[] = ' LEFT JOIN ' . DB_PURCHASE . ' pu ON og.purchaseNo = pu.purchaseNo ';
        }

        // 현 페이지 결과
        if ($arrData['whereDetail']['deliveryListFl'] == 'g') {
            $this->db->strField = "SUM(if(od.deliveryCollectFl = 'pre', 1, 0)) AS pre,SUM(if(od.deliveryCollectFl = 'later', 1, 0)) AS later";
        } else {
            $this->db->strField = "COUNT(DISTINCT IF(CONCAT(o.orderNo,od.orderInfoSno,od.deliveryCollectFl) LIKE '%pre',CONCAT(o.orderNo,od.orderInfoSno,od.deliveryCollectFl),0)) AS pre ,COUNT(DISTINCT IF(CONCAT(o.orderNo,od.orderInfoSno,od.deliveryCollectFl) LIKE '%later',CONCAT(o.orderNo,od.orderInfoSno,od.deliveryCollectFl),0)) AS later";
        }

        $this->db->strJoin = implode('', $join);
        $this->db->strWhere = implode(' AND ', gd_isset($this->arrWhere));

        $query = $this->db->query_complete();
        $strSQL = 'SELECT ' . array_shift($query) . ' FROM ' . DB_ORDER_GOODS . ' og ' . implode(' ', $query);
        $deliveryData = $this->db->query_fetch($strSQL, $this->arrBind, false);

        if ($arrData['whereDetail']['deliveryListFl'] == 'o') {
            $tmpPre = $deliveryData['pre'];
            $tmpLater = $deliveryData['later'];
            if ($tmpPre > 1) $deliveryData['later'] -= 1;
            if ($tmpLater > 1) $deliveryData['pre'] -= 1;
        }
        $godoPost = \App::load('\\Component\\Godo\\GodoPostServerApi');
        $invoiceData = $godoPost->getInvoiceNo($deliveryData['pre'], $deliveryData['later']);

        $this->db->strField = "og.orderNo,og.sno,od.deliveryCollectFl,od.orderInfoSno,oi.receiverName,oi.receiverCellPhone,oi.receiverZipcode,oi.receiverZonecode,oi.receiverAddress,oi.orderName";
        $this->db->strJoin = implode('', $join);
        $this->db->strWhere = implode(' AND ', gd_isset($this->arrWhere));

        $query = $this->db->query_complete();
        $strSQL = 'SELECT ' . array_shift($query) . ' FROM ' . DB_ORDER_GOODS . ' og ' . implode(' ', $query);
        $getData = $this->db->query_fetch($strSQL, $this->arrBind);

        //주문별 송장번호 발급일때 orderGoodsSno의 array 로 order goods 업데이트
        if ($arrData['whereDetail']['deliveryListFl'] !== 'g') {
            $tmpGetData = [];
            foreach ($getData as $key => $value) {
                $dataKey = $value['orderNo'].$value['orderInfoSno'].$value['deliveryCollectFl'];
                if(!$tmpGetData[$dataKey]){
                    $tmpGetData[$dataKey] = $value;
                }
                $tmpGetData[$dataKey]['orderGoodsSno'][] = $value['sno'];
                unset($tmpGetData[$dataKey]['sno']);
            }
            $getData = array_values($tmpGetData);
            unset($tmpGetData);
        }

        $preIndex = 0;
        $lateIndex = 0;
        $failOrderNo = [];
        foreach ($getData as $k => $v) {
            if (empty($v['receiverName']) === false && empty($v['receiverCellPhone']) == false && (empty($v['receiverZipcode']) === false || empty($v['receiverZonecode']) === false) && empty($v['receiverAddress']) == false && empty($v['orderName']) == false) {
                if ($v['deliveryCollectFl'] == 'pre') {
                    $invoiceNo = $invoiceData['prepay'][$preIndex];
                    $preIndex++;
                } else {
                    $invoiceNo = $invoiceData['collect'][$lateIndex];
                    $lateIndex++;
                }

                if ($invoiceNo) {
                    $arrBind = [];
                    $arrUpdate[] = "invoiceNo = '" . $invoiceNo . "'";
                    $arrUpdate[] = "invoiceCompanySno = '" . DEFAULT_CODE_GODOPOST . "'";
                    $this->db->bind_param_push($arrBind, 's', $v['orderNo']);

                    if ($v['sno']) {
                        $this->db->bind_param_push($arrBind, 's', $v['sno']);
                        $this->db->set_update_db(DB_ORDER_GOODS, $arrUpdate, 'orderNo = ? AND sno = ?', $arrBind);
                    } else {
                        if(is_array($v['orderGoodsSno']) && count($v['orderGoodsSno']) > 0){
                            $this->db->set_update_db(DB_ORDER_GOODS, $arrUpdate, 'orderNo = ? AND (orderStatus LIKE concat(\'p\',\'%\') OR  orderStatus LIKE concat(\'g\',\'%\')) AND sno IN ('.implode(', ', $v['orderGoodsSno']).')', $arrBind);
                        }
                        else {
                            $this->db->set_update_db(DB_ORDER_GOODS, $arrUpdate, 'orderNo = ? AND (orderStatus LIKE concat(\'p\',\'%\') OR  orderStatus LIKE concat(\'g\',\'%\'))', $arrBind);
                        }
                    }

                    $this->db->set_insert_db(DB_ORDER_GODO_POST, 'invoiceNo', array('s', $invoiceNo,), 'y');

                    unset($arrUpdate);
                    unset($arrBind);
                }
            } else {
                $failOrderNo[] = $v['orderNo'];
                continue;
            }
        }

        return $failOrderNo;
    }


    /**
     * 우체국 택배 연동 예약하기
     *
     * @param $arrData
     *
     * @throws Exception
     * @internal param string $getValue 파라미터
     */
    public function reserveOrderInvoice($arrData)
    {

        $this->arrWhere[] = 'ogp.reserveFl = "n"';

        if ($arrData['godoPostSendFl'] != 'all') {
            $tmpWhere = "";
            foreach ($arrData['statusCheck'] as $key => $val) {
                $_tmp = explode(INT_DIVISION, $val);
                $tmpWhere[] = "(og.orderNo = '" . $_tmp[0] . "' AND og.sno = '" . $_tmp[1] . "')";
            }
            $this->arrWhere[] = '(' . implode(' OR ', $tmpWhere) . ')';
        }

        // join 문
        $join[] = ' LEFT JOIN ' . DB_ORDER_GOODS . ' og ON og.invoiceNo = ogp.invoiceNo ';
        $join[] = ' LEFT JOIN ' . DB_ORDER_ADD_GOODS . ' oag ON og.orderNo = oag.orderNo AND og.orderCd = oag.orderCd';
        $join[] = ' LEFT JOIN ' . DB_GOODS . ' as g ON og.goodsNo = g.goodsNo ';
        $join[] = ' LEFT JOIN ' . DB_ORDER_DELIVERY . ' od ON og.orderDeliverySno = od.sno ';
        $join[] = ' LEFT JOIN ' . DB_ORDER_INFO . ' oi ON (og.orderNo = oi.orderNo) 
                    AND (CASE WHEN od.orderInfoSno > 0 THEN od.orderInfoSno = oi.sno ELSE oi.orderInfoCd = 1 END) ';

        // 현 페이지 결과

        $this->db->strField = "og.orderNo,og.invoiceNo,GROUP_CONCAT(oag.goodsNm SEPARATOR  '^|^') AS addGoodsNm,GROUP_CONCAT(og.optionInfo SEPARATOR  '^|^') AS optionInfo,GROUP_CONCAT(og.sno SEPARATOR  '" . STR_DIVISION . "') AS orderGoodsSno,GROUP_CONCAT(og.goodsNm SEPARATOR  '" . STR_DIVISION . "') AS goodsNm ,GROUP_CONCAT(og.goodsNo SEPARATOR  '" . STR_DIVISION . "') AS goodsNo,GROUP_CONCAT(og.goodsCnt SEPARATOR  '" . STR_DIVISION . "') AS goodsCnt,GROUP_CONCAT(og.orderStatus SEPARATOR  '" . STR_DIVISION . "') AS orderStatus,oi.receiverName,oi.receiverPhone,oi.receiverCellPhone,oi.receiverUseSafeNumberFl,oi.receiverSafeNumber,oi.receiverSafeNumberDt,oi.orderZipcode,oi.orderZonecode,oi.receiverAddress,oi.receiverAddressSub,oi.receiverZipcode,oi.receiverZonecode,oi.orderName,oi.orderCellPhone,oi.orderMemo,IF(od.deliveryCollectFl = 'later', 'N', 'Y') AS deliveryCollectFl";

        $this->db->strJoin = implode('', $join);
        $this->db->strWhere = implode(' AND ', gd_isset($this->arrWhere));
        $this->db->strGroup = "og.invoiceNo";

        $query = $this->db->query_complete();
        $strSQL = 'SELECT ' . array_shift($query) . ' FROM ' . DB_ORDER_GODO_POST . ' ogp ' . implode(' ', $query);
        $getData = $this->db->query_fetch($strSQL, $this->arrBind);
        unset($this->arrBind);
        unset($this->arrWhere);

        $godoPost = \App::load('\\Component\\Godo\\GodoPostServerApi');
        $setData = "";
        $updateData = "";
        $orderBasic = gd_policy('order.basic');

        foreach ($getData as $k => $v) {
            if (empty($v['orderNo']) === true) {
                continue;
            }

            $_tmpOrderGoodsSno = explode(STR_DIVISION, $v['orderGoodsSno']);
            $_tmpOrderStatus = explode(STR_DIVISION, $v['orderStatus']);
            $_tmpGoodsNm = explode(STR_DIVISION, $v['goodsNm']);
            $_tmpGoodsNo = explode(STR_DIVISION, $v['goodsNo']);
            $_tmpGoodsCnt = explode(STR_DIVISION, $v['goodsCnt']);
            $_tmpAddGoodsNm = explode(STR_DIVISION, $v['addGoodsNm']);
            $_tmpOptionInfo = explode(STR_DIVISION, $v['optionInfo']);

            $goodsNmLength = 80;
            if ($_tmpAddGoodsNm && $_tmpOptionInfo) $limitLength = "20";
            else $limitLength = "40";

            if ($_tmpAddGoodsNm) $goodsNmLength -= $limitLength;
            if ($_tmpOptionInfo) $goodsNmLength -= $limitLength;

            if (count($_tmpGoodsNm) > 5) $_tmpCount = 1;
            else  $_tmpCount = count($_tmpGoodsNm);

            for ($i = 0; $i < $_tmpCount; $i++) {
                if (count($_tmpGoodsNm) > 5) $_tmpData = sprintf(" (%s : " . $_tmpGoodsCnt[0] . ") %s " . (count($_tmpGoodsNm) - 1) . "%s", __('수량'), __('외'), __('개'));
                else  $_tmpData = sprintf(" (%s : " . $_tmpGoodsCnt[$i] . ")", __('수량'));
                $_tmpName = [];
                $_tmpName[] = StringUtils::strCut($_tmpGoodsNm[$i], $goodsNmLength) . $_tmpData;
                if ($_tmpOptionInfo[$i]) {
                    $tmpOption = [];
                    $option = json_decode(gd_htmlspecialchars_stripslashes($_tmpOptionInfo[$i]), true);
                    if (empty($option) === false) {
                        foreach ($option as $oKey => $oVal) {
                            $tmpOption[] = $oVal[0] . " : " . $oVal[1];
                        }
                    }

                    $_tmpName[] = StringUtils::strCut(implode(' , ', $tmpOption), $limitLength);
                }
                if ($_tmpAddGoodsNm[$i]) $_tmpName[] = StringUtils::strCut($_tmpAddGoodsNm[$i], $limitLength);

                $goodsNm[$i] = iconv('UTF-8', 'EUC-KR//IGNORE', implode('/', $_tmpName)); //EUC-KR 에서 표현하지 못하는 문자 제거 변환
                $goodsNo[$i] = $_tmpGoodsNo[$i];
            }

            $addr = trim($v['receiverAddress']) .' '. trim($v['receiverAddressSub']);

            $tmpAddr = explode(" ",$addr);
            $recprsnaddr = $tmpAddr[0].' '.$tmpAddr[1];
            unset($tmpAddr[0]);
            unset($tmpAddr[1]);
            $recprsndtailaddr = ' '.trim(implode(" ",$tmpAddr));

            //zonecode가 없고 zipcode가 있는경우 대체
            if($v['receiverZonecode'] =='' && $v['receiverZipcode']) $v['receiverZonecode']  = str_replace("-","",$v['receiverZipcode']);
            //zonecode에 지번 우편번호 있는 경우 고려하여 "-" 제거
            $v['receiverZonecode']  = str_replace("-","",$v['receiverZonecode']);

            // 안심번호 사용일 경우 휴대폰 번호 대신 안심번호로 사용
            if (gd_isset($orderBasic['safeNumberFl']) && $v['receiverUseSafeNumberFl'] == 'y' && empty($v['receiverSafeNumber']) == false && empty($v['receiverSafeNumberDt']) == false && DateTimeUtils::intervalDay($v['receiverSafeNumberDt'], date('Y-m-d H:i:s')) <= 30) {
                $v['receiverCellPhone'] = $v['receiverSafeNumber'];
            }

            $_tmpSetData = [
                'sendreqdivcd' => '01',
                'compdivcd' => $godoPost->_godoPostConf['compdivcd'],
                'orderno' => $v['orderNo'],
                'regino' => $v['invoiceNo'],
                'recprsnnm' => iconv('UTF-8', 'EUC-KR//IGNORE', trim($v['receiverName'])),
                'recprsntelno' => iconv('UTF-8', 'EUC-KR', trim($v['receiverCellPhone'])),
                'recprsnetctelno' => iconv('UTF-8', 'EUC-KR', trim($v['receiverPhone'])),
                'recprsnzipcd' => iconv('UTF-8', 'EUC-KR//IGNORE', trim($v['receiverZonecode'])),
                'recprsnaddr' => iconv('UTF-8', 'EUC-KR//IGNORE', $recprsnaddr),
                'recprsndtailaddr' => iconv('UTF-8', 'EUC-KR//IGNORE', $recprsndtailaddr),
                'orderprsnnm' => iconv('UTF-8', 'EUC-KR//IGNORE', trim($v['orderName'])),
                'orderprsntelfno' => iconv('UTF-8', 'EUC-KR', trim($v['orderCellPhone'])),
                'orderprsnetctelno' => '',
                'orderprsnzipcd' => '',
                'orderprsnaddr' => '',
                'orderprsndtailaddr' => '',
                'sendwishymd' => '',
                'sendmsgcont' => iconv('UTF-8', 'EUC-KR//IGNORE', trim(StringUtils::strCut($v['orderMemo'], '50'))),
                'goodscd1' => gd_isset($goodsNo[0]),
                'goodsnm1' => gd_isset($goodsNm[0]),
                'goodscd2' => gd_isset($goodsNo[1]),
                'goodsnm2' => gd_isset($goodsNm[1]),
                'goodscd3' => gd_isset($goodsNo[2]),
                'goodsnm3' => gd_isset($goodsNm[2]),
                'goodscd4' => gd_isset($goodsNo[3]),
                'goodsnm4' => gd_isset($goodsNm[3]),
                'goodscd5' => gd_isset($goodsNo[4]),
                'goodsnm5' => gd_isset($goodsNm[4]),
                'mailwght' => '2000',
                'mailvolm' => '60',
                'boxcnt' => '',
                'dfpayyn' => iconv('UTF-8', 'EUC-KR', trim($v['deliveryCollectFl'])),
                'expectrecevprc' => '',
                'thisdddelivyn' => '',
                'domexpyn' => '',
                'microprclyn' => '',
            ];

            $updateData[$v['invoiceNo']]['orderNo'] = $v['orderNo'];
            $updateData[$v['invoiceNo']]['orderGoodsSno'] = $_tmpOrderGoodsSno;
            $updateData[$v['invoiceNo']]['goodsNo'] = $_tmpGoodsNo;
            $updateData[$v['invoiceNo']]['orderStatus'] = $_tmpOrderStatus;
            $updateData[$v['invoiceNo']]['reserveData'] = $_tmpSetData;

            $setData[] = $_tmpSetData;

            unset($goodsNo);
            unset($goodsNm);
        }

        $resultData = $godoPost->sendInvoiceInfo($setData);

        if ($resultData) {

            foreach ($resultData as $k => $v) {

                $arrBind = [];
                $arrUpdate[] = "reserveFl = 'y',reserveDt = now(),reserveParameter = '" . json_encode($updateData[$v]['reserveData'], JSON_UNESCAPED_UNICODE) . "'";
                $this->db->bind_param_push($arrBind, 's', $v);
                $this->db->set_update_db(DB_ORDER_GODO_POST, $arrUpdate, 'invoiceNo = ?', $arrBind);
                unset($arrUpdate);
                unset($arrBind);


                //예약완료시 입금상태의 주문건일 경우 상품준비중으로 변경
                if ($updateData[$v]['orderStatus']) {
                    foreach ($updateData[$v]['orderStatus'] as $k1 => $v1) {
                        if (substr($v1, 0, 1) == 'p') {
                            $this->updateStatusPreprocess($updateData[$v]['orderNo'], $this->getOrderGoods($updateData[$v]['orderNo'], $updateData[$v]['orderGoodsSno'][$k1]), 'p', 'g1', __('우체국 택배 연동'));
                        }
                    }
                }
            }

        } else {
            throw new \Exception(__('우체국 택배 연동하기가 실패하였습니다.'));
        }
    }


    /**
     * 우체국 택배 송장번호 취소 하기
     *
     * @param $arrData
     *
     * @throws Exception
     * @internal param string $getValue 파라미터
     */
    public function cancelOrderInvoice($arrData)
    {
        //주문상품 업데이트
        foreach ($arrData['statusCheck'] as $key => $val) {
            $_tmp = explode(INT_DIVISION, $val);

            $arrBind = [];
            $arrUpdate[] = "invoiceNo = ''";
            $arrUpdate[] = "invoiceCompanySno = ''";
            $this->db->bind_param_push($arrBind, 's', $_tmp[0]);
            $this->db->bind_param_push($arrBind, 's', $_tmp[1]);

            $this->db->set_update_db(DB_ORDER_GOODS, $arrUpdate, 'orderNo = ? AND sno = ?', $arrBind);
            unset($arrUpdate);
            unset($arrBind);

            $invoiceList[] = $_tmp[2];
        }

        foreach (array_unique($invoiceList) as $k => $v) {
            $total = $this->db->fetch('SELECT count(*) as total FROM ' . DB_ORDER_GOODS . ' WHERE invoiceNo="' . $v . '"');
            if ($total['total'] == '0') {
                $arrBind = [];
                $this->db->bind_param_push($arrBind, 's', $v);
                $this->db->set_delete_db(DB_ORDER_GODO_POST, 'invoiceNo = ?', $arrBind);
                unset($arrBind);
            }
        }
    }

    /**
     * setOrderAddField
     *
     * @param $getValue
     *
     * @throws Exception
     * @author su
     */
    public function setOrderAddField($getValue)
    {
        if ($getValue['orderAddFieldType'] == 'text') {
            if (!trim($getValue['orderAddFieldOption']['text']['maxlength'])) {
                throw new Exception(__('입력제한 글자수를 입력하세요.'));
            }
        } else if ($getValue['orderAddFieldType'] == 'textarea') {
            if (!trim($getValue['orderAddFieldOption']['textarea']['height'])) {
                throw new Exception(__('필드길이(세로)를 입력하세요.'));
            }
        } else if ($getValue['orderAddFieldType'] == 'radio') {
            foreach ($getValue['orderAddFieldOption']['radio']['field'] as $key => $val) {
                if (is_null(trim($val))) {
                    throw new Exception(__('입력값를 입력하세요.'));
                }
            }
        } else if ($getValue['orderAddFieldType'] == 'checkbox') {
            foreach ($getValue['orderAddFieldOption']['checkbox']['field'] as $key => $val) {
                if (is_null(trim($val))) {
                    throw new Exception(__('입력값를 입력하세요.'));
                }
            }
        } else if ($getValue['orderAddFieldType'] == 'select') {
            foreach ($getValue['orderAddFieldOption']['select']['field'] as $key => $val) {
                if (is_null(trim($val))) {
                    throw new Exception(__('입력값를 입력하세요.'));
                }
            }
        }
        $optionCount = count($getValue['orderAddFieldOption'][$getValue['orderAddFieldType']]['field']);
        if ($optionCount > 30) {
            throw new Exception(__('입력값은 최대 30개 입니다.'));
        }
        $getValue['orderAddFieldOption'] = json_encode($getValue['orderAddFieldOption'][$getValue['orderAddFieldType']]);

        if (is_null($getValue['orderAddFieldApply'.ucfirst($getValue['orderAddFieldApplyType'])]) || $getValue['orderAddFieldApply'.ucfirst($getValue['orderAddFieldApplyType'])] == '') {
            $getValue['orderAddFieldApplyType'] = 'all';
        }
        if ($getValue['orderAddFieldApplyType'] != 'all') {
            $getValue['orderAddFieldApply']['type'] = $getValue['orderAddFieldApplyType'];
            $getValue['orderAddFieldApply']['data'] = $getValue['orderAddFieldApply'.ucfirst($getValue['orderAddFieldApplyType'])];
        } else {
            $getValue['orderAddFieldApply']['type'] = 'all';
            $getValue['orderAddFieldApply']['data'] = '';
        }
        $getValue['orderAddFieldApply'] = json_encode($getValue['orderAddFieldApply'], JSON_UNESCAPED_UNICODE);

        if (is_null($getValue['orderAddFieldExceptCategory']) || $getValue['orderAddFieldExceptCategory'] == '') {
            $getValue['orderAddFieldExceptCategoryType'] = '';
        }
        if (is_null($getValue['orderAddFieldExceptBrand']) || $getValue['orderAddFieldExceptBrand'] == '') {
            $getValue['orderAddFieldExceptBrandType'] = '';
        }
        if (is_null($getValue['orderAddFieldExceptGoods']) || $getValue['orderAddFieldExceptGoods'] == '') {
            $getValue['orderAddFieldExceptGoodsType'] = '';
        }
        if ($getValue['orderAddFieldExceptCategoryType'] == 'y') {
            $getValue['orderAddFieldExcept']['type'][] = 'category';
            $getValue['orderAddFieldExcept']['data']['category'] = $getValue['orderAddFieldExceptCategory'];
        }
        if ($getValue['orderAddFieldExceptBrandType'] == 'y') {
            $getValue['orderAddFieldExcept']['type'][] = 'brand';
            $getValue['orderAddFieldExcept']['data']['brand'] = $getValue['orderAddFieldExceptBrand'];
        }
        if ($getValue['orderAddFieldExceptGoodsType'] == 'y') {
            $getValue['orderAddFieldExcept']['type'][] = 'goods';
            $getValue['orderAddFieldExcept']['data']['goods'] = $getValue['orderAddFieldExceptGoods'];
        }
        $getValue['orderAddFieldExcept'] = json_encode($getValue['orderAddFieldExcept'], JSON_UNESCAPED_UNICODE);

        $getValue['managerNo'] = Session::get('manager.sno');
        $getValue['managerId'] = Session::get('manager.managerId');
        $getValue['managerNm'] = Session::get('manager.managerNm');

        // Validation
        $validator = new Validator();
        if (substr($getValue['mode'], 0, 6) == 'modify') {
            $validator->add('orderAddFieldNo', 'number', true); // 주문서 추가 필드 고유번호
        } else {
            $count = $this->getOrderAddFieldCount();
            if ($count >= LIMIT_ORDER_ADD_FIELD_AMOUNT) {
                throw new Exception(sprintf(__('주문서 추가정보는 최대 %s개까지 등록 가능합니다.'), LIMIT_ORDER_ADD_FIELD_AMOUNT));
            }

            $getValue['orderAddFieldSort'] = $this->_getOrderAddFieldMaxSort() + 1;
            $validator->add('orderAddFieldSort', 'number', true); // 노출순서
        }
        $validator->add('mode', 'alpha', true); // 모드
        $validator->add('orderAddFieldName', null, true); // 항목명
        $validator->add('orderAddFieldDescribed', null, null); // 항목설명
        $validator->add('orderAddFieldDisplay', 'yn', true); // 노출상태
        $validator->add('orderAddFieldRequired', 'yn', true); // 필수여부
        $validator->add('orderAddFieldType', 'alpha', true); // 노출유형
        $validator->add('orderAddFieldOption', null, true); // 상세설정
        $validator->add('orderAddFieldApply', null, true); // 노출 상품
        $validator->add('orderAddFieldExcept', null, true); // 예외 상품
        $validator->add('orderAddFieldProcess', null, true); // 입력 방법
        $validator->add('managerNo', 'number', true); // 주문서 추가 필드 등록자 고유번호
        $validator->add('managerId', 'userid', true); // 주문서 추가 필드 등록자 아이디
        $validator->add('managerNm', null, true); // 주문서 추가 필드 등록자 이름
        $validator->add('mallSno', null, true); // 상점번호

        if ($validator->act($getValue, true) === false) {
            throw new Exception(implode("<br/>", $validator->errors));
        }

        //        선택에 따른 값의 초기화를 위한 빈값 제거 - 주석처리 함 - 상태에 따른 빈값 저장(초기화)이 필요한 경우가 존재함.
        //        $arrData = ArrayUtils::removeEmpty($arrData);

        // 테이블명 반환
        $mall = new Mall();
        $tableName = $mall->getTableName(DB_ORDER_ADD_FIELD, $getValue['mallSno']);

        switch (substr($getValue['mode'], 0, 6)) {
            case 'insert' : {
                // 저장
                $arrBind = $this->db->get_binding(DBTableField::tableOrderAddField(), $getValue, 'insert', array_keys($getValue), ['orderAddFieldNo']);
                if ($getValue['mallSno'] > DEFAULT_MALL_NUMBER) {
                    $arrBind['param'][] = 'mallSno';
                    $arrBind['bind'][0] .= 'i';
                    $arrBind['bind'][] = $getValue['mallSno'];
                }
                $this->db->set_insert_db($tableName, $arrBind['param'], $arrBind['bind'], 'y');
                // 등록된 고유번호
                $orderAddFieldNo = $this->db->insert_id();
                break;
            }
            case 'modify' : {
                // 수정
                $arrBind = $this->db->get_binding(DBTableField::tableOrderAddField(), $getValue, 'update', array_keys($getValue), ['orderAddFieldNo']);
                $this->db->bind_param_push($arrBind['bind'], 'i', $getValue['orderAddFieldNo']);
                $this->db->set_update_db($tableName, $arrBind['param'], 'orderAddFieldNo = ?', $arrBind['bind'], false);
                break;
            }
        }
    }

    /**
     * setOrderAddFieldDelete
     *
     * @param $orderAddFieldNo
     */
    public function setOrderAddFieldDelete($orderAddFieldNo, $mallSno = DEFAULT_MALL_NUMBER)
    {
        $mall = new Mall();
        // 테이블명 반환
        $tableName = $mall->getTableName(DB_ORDER_ADD_FIELD, $mallSno);

        $arrBind = [
            'i',
            $orderAddFieldNo,
        ];
        // --- 삭제
        $this->db->set_delete_db($tableName, 'orderAddFieldNo = ?', $arrBind);
    }

    /**
     * getOrderAddField
     *
     * @param $orderAddFieldNo
     *
     * @return mixed
     */
    public function getOrderAddField($orderAddFieldNo, $mallSno = DEFAULT_MALL_NUMBER)
    {
        // 테이블명 반환
        $mall = new Mall();
        $tableName = $mall->getTableName(DB_ORDER_ADD_FIELD, $mallSno);

        $this->db->strWhere = "oaf.orderAddFieldNo = ?";
        $this->db->bind_param_push($arrBind, 'i', $orderAddFieldNo);

        $query = $this->db->query_complete();
        $strSQL = 'SELECT ' . array_shift($query) . ' FROM ' . $tableName . ' as oaf ' . implode(' ', $query);
        $data = $this->db->query_fetch($strSQL, $arrBind);
        unset($arrBind);

        $getData = gd_htmlspecialchars_stripslashes(gd_isset($data[0]));

        // 노출유형 별 설정
        $orderAddFieldOption = json_decode($getData['orderAddFieldOption'], true);

        unset($getData['orderAddFieldOption']);
        $getData['orderAddFieldOption'][$getData['orderAddFieldType']] = $orderAddFieldOption;

        // 상품조건 설정
        $orderAddFieldApply = json_decode($getData['orderAddFieldApply'], true);
        unset($getData['orderAddFieldApply']);
        $getData['orderAddFieldApplyType'] = $orderAddFieldApply['type'];
        if ($orderAddFieldApply['type'] == 'category') {
            if ($orderAddFieldApply['data']) {
                $category = \App::load('\\Component\\Category\\Category');
                foreach ($orderAddFieldApply['data'] as $cateKey => $cateVal) {
                    $categoryNm = $category->getCategoryPosition($cateVal);
                    $getData['orderAddFieldApplyCategory'][$cateKey]['no'] = $cateVal;
                    $getData['orderAddFieldApplyCategory'][$cateKey]['name'] = $categoryNm;
                }
            }
        } else if ($orderAddFieldApply['type'] == 'brand') {
            if ($orderAddFieldApply['data']) {
                $brand = \App::load('\\Component\\Category\\Brand');
                foreach ($orderAddFieldApply['data'] as $brandKey => $brandVal) {
                    $brandNm = $brand->getCategoryPosition($brandVal);
                    $getData['orderAddFieldApplyBrand'][$brandKey]['no'] = $brandVal;
                    $getData['orderAddFieldApplyBrand'][$brandKey]['name'] = $brandNm;
                }
            }
        } else if ($orderAddFieldApply['type'] == 'goods') {
            if ($orderAddFieldApply['data']) {
                $goods = \App::load('\\Component\\Goods\\Goods');
                foreach ($orderAddFieldApply['data'] as $goodsKey => $goodsVal) {
                    $goodsData = $goods->getGoodsDataDisplay($goodsVal);
                    $getData['orderAddFieldApplyGoods'][$goodsKey] = $goodsData[0];
                }
            }
        }

        // 예외조건 설정
        $orderAddFieldExcept = json_decode($getData['orderAddFieldExcept'], true);
        unset($getData['orderAddFieldExcept']);
        if (in_array('category', $orderAddFieldExcept['type']) && $orderAddFieldExcept['data']['category']) {
            $getData['orderAddFieldExceptCategoryType'] = 'y';
            $category = \App::load('\\Component\\Category\\Category');
            foreach ($orderAddFieldExcept['data']['category'] as $cateKey => $cateVal) {
                $categoryNm = $category->getCategoryPosition($cateVal);
                $getData['orderAddFieldExceptCategory'][$cateKey]['no'] = $cateVal;
                $getData['orderAddFieldExceptCategory'][$cateKey]['name'] = $categoryNm;
            }
        }
        if (in_array('brand', $orderAddFieldExcept['type']) && $orderAddFieldExcept['data']['brand']) {
            $getData['orderAddFieldExceptBrandType'] = 'y';
            $brand = \App::load('\\Component\\Category\\Brand');
            foreach ($orderAddFieldExcept['data']['brand'] as $brandKey => $brandVal) {
                $brandNm = $brand->getCategoryPosition($brandVal);
                $getData['orderAddFieldExceptBrand'][$brandKey]['no'] = $brandVal;
                $getData['orderAddFieldExceptBrand'][$brandKey]['name'] = $brandNm;
            }
        }
        if (in_array('goods', $orderAddFieldExcept['type']) && $orderAddFieldExcept['data']['goods']) {
            $getData['orderAddFieldExceptGoodsType'] = 'y';
            $goods = \App::load('\\Component\\Goods\\Goods');
            foreach ($orderAddFieldExcept['data']['goods'] as $goodsKey => $goodsVal) {
                $goodsData = $goods->getGoodsDataDisplay($goodsVal);
                $getData['orderAddFieldExceptGoods'][$goodsKey] = $goodsData[0];
            }
        }

        return $getData;
    }

    /**
     * getOrderAddFieldList
     *
     * @return mixed
     */
    public function getOrderAddFieldList($mallSno = DEFAULT_MALL_NUMBER)
    {
        $mall = new Mall();
        // 테이블명 반환
        $tableName = $mall->getTableName(DB_ORDER_ADD_FIELD, $mallSno);

        $this->arrWhere[] = " oaf.orderAddFieldNo > 0 ";
        if ($mallSno > DEFAULT_MALL_NUMBER) {
            $this->arrWhere[] = " oaf.mallSno = ? ";
            $this->db->bind_param_push($this->arrBind, 'i', $mallSno);
        }

        $this->db->strField = "oaf.*";
        $this->db->strWhere = implode(' AND ', gd_isset($this->arrWhere));
        $this->db->strOrder = "oaf.orderAddFieldSort asc, oaf.regDt";

        $query = $this->db->query_complete();
        $strSQL = 'SELECT ' . array_shift($query) . ' FROM ' . $tableName . ' as oaf ' . implode(' ', $query);
        $data = $this->db->query_fetch($strSQL, $this->arrBind);
        unset($this->arrBind);

        $getData['data'] = gd_htmlspecialchars_stripslashes(gd_isset($data));
        $getData['type'] = [
            'text' => __('텍스트박스(한줄)'),
            'textarea' => __('텍스트박스(여러줄)'),
            'file' => __('파일첨부'),
            'radio' => __('라디오버튼'),
            'checkbox' => __('체크박스'),
            'select' => __('셀렉트박스'),
        ];

        $getData['display'] = [
            'y'=>__('노출'),
            'n'=>__('노출안함')
        ];
        $getData['required'] = [
            'y'=>__('필수'),
            'n'=>__('선택')
        ];

        return $getData;
    }

    /**
     * setOrderAddFieldSort
     *
     * @param $orderAddFieldSortArr
     *
     * @throws Exception
     */
    public function setOrderAddFieldSort($orderAddFieldSortArr, $mallSno = DEFAULT_MALL_NUMBER)
    {
        try {
            // 테이블명 반환
            $mall = new Mall();
            $tableName = $mall->getTableName(DB_ORDER_ADD_FIELD, $mallSno);

            $sortArr = ArrayUtils::reverseKeyValue($orderAddFieldSortArr);
            $this->db->begin_tran();
            foreach ($sortArr as $sortKey => $sortVal) {
                $sortData['orderAddFieldSort'] = $sortVal + 1;
                $arrBind = $this->db->get_binding(DBTableField::tableOrderAddField(), $sortData, 'update', array_keys($sortData), ['orderAddFieldNo']);
                $this->db->bind_param_push($arrBind['bind'], 'i', $sortKey);
                $this->db->set_update_db($tableName, $arrBind['param'], 'orderAddFieldNo = ?', $arrBind['bind'], false);
                unset($sortData);
                unset($arrBind);
            }
            $this->db->commit();
        } catch (Exception $e) {
            $this->db->rollback();
            throw new Exception(__('순서 변경 중 오류가 발생하였습니다. 다시 시도해 주세요.'));
        }
    }

    /**
     * setOrderAddFieldDisplay
     *
     * @param $orderAddFieldNo
     * @param $orderAddFieldDisplay
     *
     * @throws Exception
     */
    public function setOrderAddFieldDisplay($orderAddFieldNo, $orderAddFieldDisplay, $mallSno = DEFAULT_MALL_NUMBER)
    {
        try {
            // 테이블명 반환
            $mall = new Mall();
            $tableName = $mall->getTableName(DB_ORDER_ADD_FIELD, $mallSno);

            $displayData['orderAddFieldDisplay'] = $orderAddFieldDisplay;
            $arrBind = $this->db->get_binding(DBTableField::tableOrderAddField(), $displayData, 'update', array_keys($displayData), ['orderAddFieldNo']);
            $this->db->bind_param_push($arrBind['bind'], 'i', $orderAddFieldNo);
            $this->db->set_update_db($tableName, $arrBind['param'], 'orderAddFieldNo = ?', $arrBind['bind'], false);
            unset($displayData);
            unset($arrBind);
        } catch (Exception $e) {
            throw new Exception(__('노출상태 변경 중 오류가 발생하였습니다. 다시 시도해 주세요.'));
        }
    }

    /**
     * setOrderAddFieldRequired
     *
     * @param $orderAddFieldNo
     * @param $orderAddFieldRequired
     *
     * @throws Exception
     */
    public function setOrderAddFieldRequired($orderAddFieldNo, $orderAddFieldRequired, $mallSno = DEFAULT_MALL_NUMBER)
    {
        try {
            // 테이블명 반환
            $mall = new Mall();
            $tableName = $mall->getTableName(DB_ORDER_ADD_FIELD, $mallSno);

            $requiredData['orderAddFieldRequired'] = $orderAddFieldRequired;
            $arrBind = $this->db->get_binding(DBTableField::tableOrderAddField(), $requiredData, 'update', array_keys($requiredData), ['orderAddFieldNo']);
            $this->db->bind_param_push($arrBind['bind'], 'i', $orderAddFieldNo);
            $this->db->set_update_db($tableName, $arrBind['param'], 'orderAddFieldNo = ?', $arrBind['bind'], false);
            unset($requiredData);
            unset($arrBind);
        } catch (Exception $e) {
            throw new Exception(__('필수여부 변경 중 오류가 발생하였습니다. 다시 시도해 주세요.'));
        }
    }

    /**
     * convertOrderAddField
     *
     * @param      $orderAddFieldData
     * @param bool $isArray
     *
     * @return array
     */
    public function convertOrderAddField($orderAddFieldData, $isArray = false)
    {
        $convertData = [];
        if ($isArray) {
            foreach ($orderAddFieldData as $keyData => $valData) {
                $convertData[$keyData] = $this->_getOrderAddFieldConvertData($valData);
            }
        } else {
            $convertData = $this->_getOrderAddFieldConvertData($orderAddFieldData);
        }

        return $convertData;
    }

    /**
     * getOrderAddFieldApplyExcept
     *
     * @param string $mode
     * @param null   $type
     * @param        $orderAddFieldNo
     *
     * @return array
     */
    public function getOrderAddFieldApplyExcept($mode = 'apply', $type = null, $orderAddFieldNo)
    {
        $this->db->strField = " oaf.orderAddField".ucfirst($mode);
        $this->db->strWhere = " oaf.orderAddFieldNo = ? ";
        $this->db->bind_param_push($arrBind, 'i', $orderAddFieldNo);

        $query = $this->db->query_complete();
        $strSQL = 'SELECT ' . array_shift($query) . ' FROM ' . DB_ORDER_ADD_FIELD . ' as oaf ' . implode(' ', $query);
        $data = $this->db->query_fetch($strSQL, $arrBind);
        $data = $data[0];

        $returnData = [];
        if ($mode == 'apply') {
            $orderAddFieldApply = json_decode($data['orderAddFieldApply'], true);
            if ($orderAddFieldApply['type'] == 'category') {
                if ($orderAddFieldApply['data']) {
                    $returnData['title'] = __('카테고리');
                    $category = \App::load('\\Component\\Category\\Category');
                    foreach ($orderAddFieldApply['data'] as $cateKey => $cateVal) {
                        $categoryNm = $category->getCategoryPosition($cateVal);
                        $returnData['data'][$cateKey]['no'] = $cateVal;
                        $returnData['data'][$cateKey]['name'] = $categoryNm;
                    }
                }
            } else if ($orderAddFieldApply['type'] == 'brand') {
                if ($orderAddFieldApply['data']) {
                    $returnData['title'] = __('브랜드');
                    $brand = \App::load('\\Component\\Category\\Brand');
                    foreach ($orderAddFieldApply['data'] as $brandKey => $brandVal) {
                        $brandNm = $brand->getCategoryPosition($brandVal);
                        $returnData['data'][$brandKey]['no'] = $brandVal;
                        $returnData['data'][$brandKey]['name'] = $brandNm;
                    }
                }
            } else if ($orderAddFieldApply['type'] == 'goods') {
                if ($orderAddFieldApply['data']) {
                    $returnData['title'] = __('상품');
                    $goods = \App::load('\\Component\\Goods\\Goods');
                    foreach ($orderAddFieldApply['data'] as $goodsKey => $goodsVal) {
                        $goodsData = $goods->getGoodsDataDisplay($goodsVal);
                        $returnData['data'][$goodsKey]['no'] = $goodsData[0]['goodsNo'];
                        $returnData['data'][$goodsKey]['name'] = $goodsData[0]['goodsNm'];
                    }
                }
            }
        }
        if ($mode == 'except') {
            $orderAddFieldExcept = json_decode($data['orderAddFieldExcept'], true);
            if ($type == 'category') {
                if ($orderAddFieldExcept['data']['category']) {
                    $returnData['title'] = __('카테고리');
                    $category = \App::load('\\Component\\Category\\Category');
                    foreach ($orderAddFieldExcept['data']['category'] as $cateKey => $cateVal) {
                        $categoryNm = $category->getCategoryPosition($cateVal);
                        $returnData['data'][$cateKey]['no'] = $cateVal;
                        $returnData['data'][$cateKey]['name'] = $categoryNm;
                    }
                }
            } else if ($type == 'brand') {
                if ($orderAddFieldExcept['data']['brand']) {
                    $returnData['title'] = '브랜드';
                    $brand = \App::load('\\Component\\Category\\Brand');
                    foreach ($orderAddFieldExcept['data']['brand'] as $brandKey => $brandVal) {
                        $brandNm = $brand->getCategoryPosition($brandVal);
                        $returnData['data'][$brandKey]['no'] = $brandVal;
                        $returnData['data'][$brandKey]['name'] = $brandNm;
                    }
                }
            } else if ($type == 'goods') {
                if ($orderAddFieldExcept['data']['goods']) {
                    $returnData['title'] = __('상품');
                    $goods = \App::load('\\Component\\Goods\\Goods');
                    foreach ($orderAddFieldExcept['data']['goods'] as $goodsKey => $goodsVal) {
                        $goodsData = $goods->getGoodsDataDisplay($goodsVal);
                        $returnData['data'][$goodsKey]['no'] = $goodsData[0]['goodsNo'];
                        $returnData['data'][$goodsKey]['name'] = $goodsData[0]['goodsNm'];
                    }
                }
            }
        }

        $returnData['data'] = gd_htmlspecialchars_stripslashes(gd_isset($returnData['data']));

        return $returnData;
    }

    /**
     * _getOrderAddFieldConvertData
     *
     * @param $orderAddFieldData
     *
     * @return array
     */
    private function _getOrderAddFieldConvertData($orderAddFieldData)
    {
        $convertData = [];
        if ($orderAddFieldData['orderAddFieldDisplay']) {
            if ($orderAddFieldData['orderAddFieldDisplay'] == 'y') {
                $convertData['orderAddFieldDisplay'] = __('노출');
            } else {
                $convertData['orderAddFieldDisplay'] = __('숨김');
            }
        }
        if ($orderAddFieldData['orderAddFieldRequired']) {
            if ($orderAddFieldData['orderAddFieldRequired'] == 'y') {
                $convertData['orderAddFieldRequired'] = __('필수');
            } else {
                $convertData['orderAddFieldRequired'] = __('선택');
            }
        }

        $orderAddFieldOption = json_decode($orderAddFieldData['orderAddFieldOption'], true);

        $convertData['orderAddFieldOption'] = '';
        if ($orderAddFieldData['orderAddFieldType']) {
            if ($orderAddFieldData['orderAddFieldType'] == 'text') {
                $convertData['orderAddFieldType'] = __('텍스트박스(한줄)');
                if ($orderAddFieldOption['width']) {
                    $convertData['orderAddFieldOption'][] = sprintf('%s : ' . $orderAddFieldOption['width'], __('필드길이'));
                }
                if ($orderAddFieldOption['maxlength']) {
                    $convertData['orderAddFieldOption'][] = sprintf('%s : ' . $orderAddFieldOption['maxlength'], __('입력제한 글자수'));
                }
                if ($orderAddFieldOption['encryptor'] == 'y') {
                    $convertData['orderAddFieldOption'][] = __('암호화 : 사용');
                }
                if ($orderAddFieldOption['password'] == 'y') {
                    $convertData['orderAddFieldOption'][] = __('마스킹 처리 : 사용');
                }
                $convertData['orderAddFieldOption'] = implode('<br/>', $convertData['orderAddFieldOption']);
            } else if ($orderAddFieldData['orderAddFieldType'] == 'textarea') {
                $convertData['orderAddFieldType'] = __('텍스트박스(여러줄)');
                if ($orderAddFieldOption['width']) {
                    $convertData['orderAddFieldOption'][] = sprintf('%s : ' . $orderAddFieldOption['width'], __('필드길이(가로)'));
                }
                if ($orderAddFieldOption['height']) {
                    $convertData['orderAddFieldOption'][] = sprintf('%s : ' . $orderAddFieldOption['height'], __('필드길이(세로)'));
                }
                $convertData['orderAddFieldOption'] = implode('<br/>', $convertData['orderAddFieldOption']);
            } else if ($orderAddFieldData['orderAddFieldType'] == 'file') {
                $convertData['orderAddFieldType'] = __('파일첨부');
                if ($orderAddFieldOption['capacity']) {
                    $convertData['orderAddFieldOption'][] = sprintf('%s : ' . $orderAddFieldOption['capacity'], __('첨부파일 크기'));
                }
                $convertData['orderAddFieldOption'] = implode('<br/>', $convertData['orderAddFieldOption']);
            } else if ($orderAddFieldData['orderAddFieldType'] == 'radio') {
                $convertData['orderAddFieldType'] = __('라디오버튼');
                if ($orderAddFieldOption['field']) {
                    foreach ($orderAddFieldOption['field'] as $radioVal) {
                        $convertData['orderAddFieldOption'][] = $radioVal;
                    }
                }
                $convertData['orderAddFieldOption'] = sprintf('%s : ' . implode(', ', $convertData['orderAddFieldOption']), __('입력값'));
            } else if ($orderAddFieldData['orderAddFieldType'] == 'checkbox') {
                $convertData['orderAddFieldType'] = __('체크박스');
                if ($orderAddFieldOption['field']) {
                    foreach ($orderAddFieldOption['field'] as $checkboxVal) {
                        $convertData['orderAddFieldOption'][] = $checkboxVal;
                    }
                }
                $convertData['orderAddFieldOption'] = sprintf('%s : ' . implode(', ', $convertData['orderAddFieldOption']), __('입력값'));
            } else if ($orderAddFieldData['orderAddFieldType'] == 'select') {
                $convertData['orderAddFieldType'] = __('셀렉트박스');
                if ($orderAddFieldOption['field']) {
                    foreach ($orderAddFieldOption['field'] as $selectVal) {
                        $convertData['orderAddFieldOption'][] = $selectVal;
                    }
                }
                $convertData['orderAddFieldOption'] = sprintf('%s : ' . implode(', ', $convertData['orderAddFieldOption']), __('입력값'));
            }
        }

        $orderAddFieldApply = json_decode($orderAddFieldData['orderAddFieldApply'], true);
        if ($orderAddFieldApply['type'] == 'all') {
            $convertData['orderAddFieldApply'] = __('전체상품');
        } else if ($orderAddFieldApply['type'] == 'category') {
            $convertData['orderAddFieldApply'] = sprintf('<button type="button" class="btn-black btn-xs js-layer-apply" data-type="category" data-no="'.$orderAddFieldData['orderAddFieldNo'].'">%s</button>', __('특정카테고리'));
        } else if ($orderAddFieldApply['type'] == 'brand') {
            $convertData['orderAddFieldApply'] = sprintf('<button type="button" class="btn-black btn-xs js-layer-apply" data-type="brand" data-no="'.$orderAddFieldData['orderAddFieldNo'].'">%s</button>', __('특정브랜드'));
        } else if ($orderAddFieldApply['type'] == 'goods') {
            $convertData['orderAddFieldApply'] = sprintf('<button type="button" class="btn-black btn-xs js-layer-apply" data-type="goods" data-no="'.$orderAddFieldData['orderAddFieldNo'].'">%s</button>', __('특정상품'));
        }

        $orderAddFieldExcept = json_decode($orderAddFieldData['orderAddFieldExcept'], true);
        if (in_array('category', $orderAddFieldExcept['type']) === true) {
            $convertData['orderAddFieldExcept'][] = sprintf('<button type="button" class="btn-black btn-xs js-layer-except" data-type="category" data-no="'.$orderAddFieldData['orderAddFieldNo'].'">%s</button>', __('특정카테고리'));
        }
        if (in_array('brand', $orderAddFieldExcept['type']) === true) {
            $convertData['orderAddFieldExcept'][] = sprintf('<button type="button" class="btn-black btn-xs js-layer-except" data-type="brand" data-no="'.$orderAddFieldData['orderAddFieldNo'].'">%s</button>', __('특정브랜드'));
        }
        if (in_array('goods', $orderAddFieldExcept['type']) === true) {
            $convertData['orderAddFieldExcept'][] = sprintf('<button type="button" class="btn-black btn-xs js-layer-except" data-type="goods" data-no="'.$orderAddFieldData['orderAddFieldNo'].'">%s</button>', __('특정상품'));
        }
        $convertData['orderAddFieldExcept'] = implode('<br/>', $convertData['orderAddFieldExcept']);

        return $convertData;
    }

    /**
     * _getOrderAddFieldMaxSort
     *
     * @return mixed
     */
    private function _getOrderAddFieldMaxSort()
    {
        $arrBind = [];
        $this->db->strField = " MAX(oaf.orderAddFieldSort) as maxSort ";
        $this->db->strWhere = " oaf.orderAddFieldNo > ? ";
        $this->db->bind_param_push($arrBind, 'i', 0);

        $query = $this->db->query_complete();
        $strSQL = 'SELECT ' . array_shift($query) . ' FROM ' . DB_ORDER_ADD_FIELD . ' as oaf ' . implode(' ', $query);
        $data = $this->db->query_fetch($strSQL, $arrBind);
        $orderAddFieldMaxSort = $data[0]['maxSort'];
        unset($arrBind);

        return $orderAddFieldMaxSort;
    }

    /**
     * getOrderAddFieldCount
     *
     * @return mixed
     */
    public function getOrderAddFieldCount($mallSno = DEFAULT_MALL_NUMBER)
    {
        $mall = new Mall();
        // 테이블명 반환
        $tableName = $mall->getTableName(DB_ORDER_ADD_FIELD, $mallSno);

        $this->db->strField = " COUNT(oaf.orderAddFieldNo) as count ";
        $this->db->strWhere = " oaf.orderAddFieldNo > ? ";
        $this->db->bind_param_push($arrBind, 'i', 0);

        $query = $this->db->query_complete();
        $strSQL = 'SELECT ' . array_shift($query) . ' FROM ' . $tableName . ' as oaf ' . implode(' ', $query);
        $data = $this->db->query_fetch($strSQL, $arrBind);
        $orderAddFieldCount = $data[0]['count'];

        return $orderAddFieldCount;
    }

    /**
     * 주문 관리자 메모 업데이트
     *
     * @param array $data
     *
     * @author Jong-tae Ahn <qnibus@godo.co.kr>
     */
    public function updateSuperAdminMemo($data)
    {
        $orderData['adminMemo'] = $data['adminMemo'];
        $compareField = array_keys($orderData);
        $arrBind = $this->db->get_binding(DBTableField::tableOrder(), $orderData, 'update', $compareField);
        $this->db->bind_param_push($arrBind['bind'], 's', $data['orderNo']);
        $return = $this->db->set_update_db(DB_ORDER, $arrBind['param'], 'orderNo = ?', $arrBind['bind']);
        unset($arrBind, $orderData);

        return $return;
    }

    /**
     * getEachOrderStatusAdmin
     * 관리자 메인에 주문관리 / 주문현황
     * 주문 상태에 따른 상품 수
     * 주문 상태마다 기준 검색 날짜가 다름 ( 입금대기-주문일, 결제완료-결제일..... )
     * 주문 상태에 따른 주문 수와 상품 수에서 상태마다 표시 방법이 달라 우선 상품의 수로 노출함
     *
     * @param array $mainOrderStatus
     * @param int   $day
     * @param null  $scmNo
     *
     * @return array|string
     *
     * @author su
     */
    public function getEachOrderStatusAdmin($mainOrderStatus, $day = 30, $scmNo = null, $orderCountFl = 'goods')
    {
        // 주문상태 정보
        $status = $this->_getOrderStatus();
        $status['er'] = '고객교환신청';
        $status['br'] = '고객반품신청';
        $status['rr'] = '고객환불신청';

        // 상태별 조회 기준 날짜
        $statusCodeSearchDate = [
            'o' => 'og.regDt',
            'f' => 'og.regDt',
            'p' => 'og.paymentDt',
            'g' => 'og.paymentDt',
            'd1' => 'og.deliveryDt',
            'd2' => 'og.deliveryCompleteDt',
            's' => 'og.finishDt',
            'c' => 'og.cancelDt',
            'b' => 'oh.regDt',
            'e' => 'oh.regDt',
            'z' => 'oh.regDt',
            'r' => 'oh.regDt',
            'br' => 'ouh.regDt',
            'er' => 'ouh.regDt',
            'rr' => 'ouh.regDt',
        ];

        foreach ($mainOrderStatus as $val) {

            $code = $val;
            $str = $status[$val];

            // 배열 선언
            $arrBind = $arrWhere = $arrJoin = [];

            // 데이터 초기화
            $getData[$code]['name'] = $str;
            $getData[$code]['count'] = 0;
            $getData[$code]['active'] = '';

            // 공급사 로그인한 경우
            if (Manager::isProvider() || ($scmNo !== null && $scmNo > 0)) {
                if (Manager::isProvider()) {
                    // 공급사로 로그인한 경우 기존 scm에 값 설정
                    $arrWhere[] = 'og.scmNo = ' . Session::get('manager.scmNo');
                } else {
                    // 공급사의 검색일 경우 scm에 값 설정
                    $arrWhere[] = 'og.scmNo = ' . $scmNo;
                }
            }

            // 상태별 조회 기준 날짜가 변경됨
            $groupCode = substr($code, 0, 1);
            $groupCode2 = substr($code, 1, 1);
            if ($groupCode == 'd') { // 배송상태마다 검색일 기준이 다름 - 배송완료은 deliveryCompleteDt / 배송중은 deliveryDt
                $codeDate = $statusCodeSearchDate[$code];
            } else if ($groupCode == 'e' || $groupCode == 'r' || $groupCode == 'b' || $groupCode == 'z') { // 교환, 환불, 반품은 고객신청과 관리자처리마다 검색일 기준이 다름
                if ($groupCode2 == 'r') { // 고객 신청
                    $codeDate = $statusCodeSearchDate[$code];
                } else { // 관리자 처리
                    $codeDate = $statusCodeSearchDate[$groupCode];
                }
            } else { // 그 외는 주문상태 그룹마다 검색일 기준이 다름
                $codeDate = $statusCodeSearchDate[$groupCode];
            }

            if ($day > 0) {
                // 기간산출 (한달 이내로 쿼리 조작)
                $arrWhere[] = $codeDate . ' BETWEEN ? AND ?';
                $this->db->bind_param_push($arrBind, 's', date('Y-m-d', strtotime('-' . $day . ' days')) . ' 00:00:00');
                $this->db->bind_param_push($arrBind, 's', date('Y-m-d') . ' 23:59:59');
            } else {
                $arrWhere[] = $codeDate . ' BETWEEN ? AND ?';
                $this->db->bind_param_push($arrBind, 's', date('Y-m-d') . ' 00:00:00');
                $this->db->bind_param_push($arrBind, 's', date('Y-m-d') . ' 23:59:59');
            }

            // 반품 환품 교환 이면 orderHandle 도 join
            if ($groupCode == 'b' || $groupCode == 'e' || $groupCode == 'r' || $groupCode == 'z') {
                if ($groupCode2 == 'r') {
                    $arrJoin[] =  'INNER JOIN ' . DB_ORDER_USER_HANDLE. ' as ouh ON og.userHandleSno = ouh.sno';

                    // 상태별 조건
                    $arrWhere[] = 'ouh.userHandleMode = ? AND ouh.userHandleFl = ?';
                    $this->db->bind_param_push($arrBind, 's', $groupCode);
                    $this->db->bind_param_push($arrBind, 's', $groupCode2);
                } else {
                    $arrJoin[] =  'LEFT JOIN ' . DB_ORDER_HANDLE. ' as oh ON og.handleSno = oh.sno';

                    // 상태별 조건
                    $arrWhere[] = 'og.orderStatus = ?';
                    $this->db->bind_param_push($arrBind, 's', $code);
                }
            } else {
                // 상태별 조건
                $arrWhere[] = 'og.orderStatus = ?';
                $this->db->bind_param_push($arrBind, 's', $code);
            }

            if($orderCountFl == 'goods'){
                $orderCountField = 'og.sno';
            }else{
                $orderCountField = 'DISTINCT og.orderNo';
            }

            // 상태 합계 산출
            $this->db->strJoin = implode(' ', $arrJoin);
            if ($groupCode2 == 'r') { // 고객 신청
                $this->db->strField = 'date(' . $codeDate . ') as regDt, count('. $orderCountField. ') AS countStatus';
                $this->db->strGroup = 'date(ouh.regDt)';
            } else {
                $this->db->strField = $codeDate . ', count('. $orderCountField. ') AS countStatus';
            }
            $this->db->strWhere = implode(' AND ', $arrWhere);
            $query = $this->db->query_complete();
            $strSQL = 'SELECT ' . array_shift($query) . ' FROM ' . DB_ORDER_GOODS . ' as og ' . implode(' ', $query);
            $result = $this->db->query_fetch($strSQL, $arrBind, true);
            unset($arrBind);
            unset($arrWhere);
            unset($arrJoin);

            $count = $result[0]['countStatus'];
            if ($groupCode2 == 'r') { // 고객 신청
                $count = 0;
                foreach($result as $val) {
                    $count += $val['countStatus'];
                }
            }
            if($count > 0) {
                $getData[$code]['count'] = $count;
                $getData[$code]['active'] = 'active';
            }
        }

        return gd_htmlspecialchars_stripslashes($getData);
    }

    /**
     * getEachOrderStatusAdminMobileapp
     * 관리자 메인에 주문관리 / 주문현황 - 관리자 앱용
     * 주문 상태에 따른 상품 수 -> 가아닌 주문단위 카운트로 변경
     *
     * @param array $mainOrderStatus
     * @param int   $day
     * @param null  $scmNo
     *
     * @return array|string
     *
     * @author su
     */
    public function getEachOrderStatusAdminMobileapp($mainOrderStatus, $day = 6, $scmNo = null)
    {
        // 주문상태 정보
        $status = $this->_getOrderStatus();

        // 상태별 조회 기준 날짜
        $statusCodeSearchDate = [
            'o' => 'og.regDt',
            'f' => 'og.regDt',
            'p' => 'og.paymentDt',
            'g' => 'og.paymentDt',
            'd1' => 'og.deliveryDt',
            'd2' => 'og.deliveryCompleteDt',
            's' => 'og.finishDt',
            'c' => 'og.cancelDt',
            'b' => 'oh.regDt',
            'e' => 'oh.regDt',
            'r' => 'oh.regDt',
        ];

        foreach ($mainOrderStatus as $val) {
            $code = $val;
            $str = $status[$val];

            // 배열 선언
            $arrBind = $arrWhere = $arrJoin = [];

            // 데이터 초기화
            $getData[$code]['name'] = $str;
            $getData[$code]['count'] = 0;
            $getData[$code]['active'] = '';

            // 공급사 로그인한 경우
            if (Manager::isProvider() || ($scmNo !== null && $scmNo > 0)) {
                if (Manager::isProvider()) {
                    // 공급사로 로그인한 경우 기존 scm에 값 설정
                    $arrWhere[] = 'og.scmNo = ' . Session::get('manager.scmNo');
                } else {
                    // 공급사의 검색일 경우 scm에 값 설정
                    $arrWhere[] = 'og.scmNo = ' . $scmNo;
                }
            }

            // 상태별 조회 기준 날짜가 변경됨
            $groupCode = substr($code, 0, 1);
            $groupCode2 = substr($code, 1, 1);
            if ($groupCode == 'd') { // 배송상태마다 검색일 기준이 다름 - 배송완료은 deliveryCompleteDt / 배송중은 deliveryDt
                $codeDate = $statusCodeSearchDate[$code];
            } else if ($groupCode == 'e' || $groupCode == 'r' || $groupCode == 'b') { // 교환, 환불, 반품은 고객신청과 관리자처리마다 검색일 기준이 다름
                if ($groupCode2 == 'r') { // 고객 신청
                    $codeDate = $statusCodeSearchDate[$code];
                } else { // 관리자 처리
                    $codeDate = $statusCodeSearchDate[$groupCode];
                }
            } else { // 그 외는 주문상태 그룹마다 검색일 기준이 다름
                $codeDate = $statusCodeSearchDate[$groupCode];
            }

            // 멀티상점 선택
            $arrWhere[] = 'og.mallSno = ?';
            $this->db->bind_param_push($arrBind, 's', '1');

            if ($day > 0) {
                // 기간산출 (한달 이내로 쿼리 조작)
                $arrWhere[] = $codeDate . ' BETWEEN ? AND ?';
                $this->db->bind_param_push($arrBind, 's', date('Y-m-d', strtotime('-' . $day . ' days')) . ' 00:00:00');
                $this->db->bind_param_push($arrBind, 's', date('Y-m-d') . ' 23:59:59');
            } else {
                $arrWhere[] = $codeDate . ' BETWEEN ? AND ?';
                $this->db->bind_param_push($arrBind, 's', date('Y-m-d') . ' 00:00:00');
                $this->db->bind_param_push($arrBind, 's', date('Y-m-d') . ' 23:59:59');
            }

            // 반품 환품 교환 이면 orderHandle 도 join
            if ($groupCode == 'b' || $groupCode == 'e' || $groupCode == 'r') {
                if ($groupCode2 == 'r') {
                    $arrJoin[] =  'LEFT JOIN ' . DB_ORDER_USER_HANDLE. ' as ouh ON og.userHandleSno = ouh.sno';

                    // 상태별 조건
                    $arrWhere[] = 'ouh.userHandleMode = ? AND ouh.userHandleFl = ?';
                    $this->db->bind_param_push($arrBind, 's', $groupCode);
                    $this->db->bind_param_push($arrBind, 's', $groupCode2);
                } else {
                    $arrJoin[] =  'LEFT JOIN ' . DB_ORDER_HANDLE. ' as oh ON og.handleSno = oh.sno';

                    // 상태별 조건
                    $arrWhere[] = 'og.orderStatus = ?';
                    $this->db->bind_param_push($arrBind, 's', $code);
                }
            } else {
                // 상태별 조건
                $arrWhere[] = 'og.orderStatus = ?';
                $this->db->bind_param_push($arrBind, 's', $code);
            }

            // 상태 합계 산출
            $this->db->strJoin = implode(' ', $arrJoin);
            $this->db->strField = $codeDate . ', count(DISTINCT og.orderNo) AS countStatus';
            $this->db->strWhere = implode(' AND ', $arrWhere);
            $query = $this->db->query_complete();
            $strSQL = 'SELECT ' . array_shift($query) . ' FROM ' . DB_ORDER_GOODS . ' as og ' . implode(' ', $query);
            $result = $this->db->query_fetch($strSQL, $arrBind, true);
            unset($arrBind);
            unset($arrWhere);
            unset($arrJoin);

            if ($result[0]['countStatus'] > 0) {
                $getData[$code]['count'] = $result[0]['countStatus'];
                $getData[$code]['active'] = 'active';
            }
        }

        return gd_htmlspecialchars_stripslashes($getData);
    }

    /**
     * 송장번호 입력시 회사이름
     *
     * @param $sno
     *
     * @return bool
     * @author Jong-tae Ahn <qnibus@godo.co.kr>
     */
    public function getInvoiceCompanyName($sno)
    {
        if (empty($sno) || $sno < 1) {
            return false;
        }

        // 배송회사 정보 설정
        /** @var \Bundle\Component\Delivery\Delivery $delivery */
        $delivery = \App::load('\\Component\\Delivery\\Delivery');
        $deliveryCompanyList = $delivery->getDeliveryCompany();
        $arrSno = ArrayUtils::getSubArrayByKey($deliveryCompanyList, 'sno');
        $arrCompanyName = ArrayUtils::getSubArrayByKey($deliveryCompanyList, 'companyName');
        $arrDelivery = array_combine($arrSno, $arrCompanyName);

        return $arrDelivery[$sno];
    }

    public function setAddSearchSortList($addSearchSortListArray)
    {
        foreach($addSearchSortListArray as $key => $value){
            switch($value){
                case 'paymentDt':
                    $this->search['sortList']['og.paymentDt desc'] = sprintf('%s↓', __('결제일'));
                    $this->search['sortList']['og.paymentDt asc'] = sprintf('%s↑', __('결제일'));
                    break;

                case 'packetCode':
                    $this->search['sortList']['oi.packetCode desc'] = sprintf('%s↓', __('묶음배송'));
                    $this->search['sortList']['oi.packetCode asc'] = sprintf('%s↑', __('묶음배송'));
                    break;

                default:
                    break;
            }
        }
    }

    public function getOrderPrintData($orderData, $user=false)
    {
        $returnData = array();
        $orderPrintData = gd_policy('order.print');
        gd_isset($orderPrintData['orderPrintSameDisplay'], 'y');
        gd_isset($orderPrintData['orderPrintBusinessInfo'], 'n');
        gd_isset($orderPrintData['orderPrintBusinessInfoType'], 'companyWithOrder');
        gd_isset($orderPrintData['orderPrintBottomInfo'], 'n');
        gd_isset($orderPrintData['orderPrintBottomInfoType'], '');
        gd_isset($orderPrintData['orderPrintBottomInfoText'], '');

        //유저모드에서 접속시 쇼핑몰 동시 적용 체크
        if($user === true){
            if($orderPrintData['orderPrintSameDisplay'] !== 'y'){
                return '';
            }
        }

        $returnData = $orderPrintData;
        //하단 추가 정보 표기
        if($orderPrintData['orderPrintBottomInfo'] === 'y'){
            switch($orderPrintData['orderPrintBottomInfoType']){
                case 'c':
                    //고객요청사항
                    $returnData['bottomInfoDisplayType'] = 'array';
                    $consultData = $this->getOrderConsult($orderData['orderNo']);
                    foreach($consultData as $key => $value){
                        $returnData['bottomInfo'][] = $value['requestMemo'];
                    }
                    break;

                case 'a':
                    // 상품º주문번호별 메모
                    $admOrdGoodsMemoData = $this->getAdminOrdGoodsMemoToPrint($orderData['orderNo']);
                    foreach($admOrdGoodsMemoData as $key => $value){
                        $returnData['bottomInfo'][] = $value['content'];
                    }
                    break;
                //$returnData['bottomInfoDisplayType'] = 'string';
                //$returnData['bottomInfo'] = $orderData['adminMemo'];

                case 's':
                    //직접 입력
                    $returnData['bottomInfoDisplayType'] = 'string';
                    $returnData['bottomInfo'] = $orderPrintData['orderPrintBottomInfoText'];
                    break;
            }
        } else {
            // 주문내역서 관리자메모 표시
            if ($orderPrintData['orderPrintOdAdminMemoDisplay'] === 'y') {
                $admOrdGoodsMemoData = $this->getAdminOrdGoodsMemoToPrint($orderData['orderNo']);
                foreach ($admOrdGoodsMemoData as $key => $value) {
                    $returnData['bottomInfo'][] = $value['content'];
                }
            }
        }
        //고객의 사업자 정보를 표기
        $orderPrintMemberData = [];
        gd_isset($returnData['BusinessInfoUse'], 'n');
        if((int)$orderData['memNo'] > 0){
            $memberService = \App::load('\\Component\\Member\\Member');
            $memberService->getMemberDataWithChecked($orderData['memNo'], $orderPrintMemberData, $orderPrintChecked);
        }
        if($orderPrintData['orderPrintBusinessInfo'] === 'y' && $orderPrintMemberData['memberFl'] === 'business'){
            $returnData['BusinessInfoUse'] = 'y';
            $returnData['businessInfo']['address'] = $orderPrintMemberData['address'] . ' ' . $orderPrintMemberData['addressSub'];
            $returnData['businessInfo']['name'] = $orderPrintMemberData['company'] . ' - ' . $orderData['orderName'];
            $returnData['businessInfo']['company'] = $orderPrintMemberData['company'] ;
            $returnData['businessInfo']['orderName'] = $orderData['orderName'];
            $returnData['businessInfoType'] = $orderPrintData['orderPrintBusinessInfoType'];
            $returnData['businessInfo']['phone'] = $orderData['orderPhone'];
            $returnData['businessInfo']['busiNo'] = implode('-', $orderPrintMemberData['busiNo']);
        }

        return $returnData;
    }

    public function getOrderPrintOdData($orderData, $orderPrintMode)
    {
        $returnData = array();
        $orderPrintData = gd_policy('order.print');

        //주문내역서 출력 설정
        gd_isset($orderPrintData['orderPrintOdSameDisplay'], 'y');
        gd_isset($orderPrintData['orderPrintOdGoodsCode'], 'n');
        gd_isset($orderPrintData['orderPrintOdSelfGoodsCode'], 'n');
        gd_isset($orderPrintData['orderPrintOdScmDisplay'], 'y');
        gd_isset($orderPrintData['orderPrintOdImageDisplay'], 'y');
        gd_isset($orderPrintData['orderPrintOdSettleInfoDisplay'], 'y');
        gd_isset($orderPrintData['orderPrintOdAdminMemoDisplay'], 'n');
        gd_isset($orderPrintData['orderPrintOdBottomInfo'], 'n');
        gd_isset($orderPrintData['orderPrintOdBottomInfoText'], '');

        //주문내역서 (고객용) 출력 설정
        gd_isset($orderPrintData['orderPrintOdCsGoodsCode'], 'n');
        gd_isset($orderPrintData['orderPrintOdCsSelfGoodsCode'], 'n');
        gd_isset($orderPrintData['orderPrintOdCsImageDisplay'], 'y');
        gd_isset($orderPrintData['orderPrintOdCsSettleInfoDisplay'], 'y');
        gd_isset($orderPrintData['orderPrintOdCsAdminMemoDisplay'], 'n');
        gd_isset($orderPrintData['orderPrintOdCsBottomInfo'], 'n');
        gd_isset($orderPrintData['orderPrintOdCsBottomInfoText'], '');

        $returnData = $orderPrintData;

        if($orderPrintData['orderPrintOdSameDisplay'] === 'y' && $orderPrintMode === 'customerReport'){
            $returnData['orderPrintOdCsGoodsCode'] = $orderPrintData['orderPrintOdGoodsCode'];
            $returnData['orderPrintOdCsSelfGoodsCode'] = $orderPrintData['orderPrintOdSelfGoodsCode'];
            $returnData['orderPrintOdCsImageDisplay'] = $orderPrintData['orderPrintOdImageDisplay'];
            $returnData['orderPrintOdCsSettleInfoDisplay'] = $orderPrintData['orderPrintOdSettleInfoDisplay'];
            $returnData['orderPrintOdCsAdminMemoDisplay'] = $orderPrintData['orderPrintOdAdminMemoDisplay'];
            $returnData['orderPrintOdCsBottomInfo'] = $orderPrintData['orderPrintOdBottomInfo'];
            $returnData['orderPrintOdCsBottomInfoText'] = $orderPrintData['orderPrintOdBottomInfoText'];
        }

        // 주문내역서 & 주문내역서(고객용) 관리자 메모 추가
        $admOrdGoodsMemoInfo = $this->getOrderPrintData($orderData);
        $returnData['orderPrintOdOrdGoodsMemoInfo'] = $admOrdGoodsMemoInfo['bottomInfo'];

        return $returnData;
    }

    /**
     * setPlusMileageScheduler
     * 주문시 마일리지 지급 유예 설정에 따른 실 지급 스케줄러 처리
     *
     * @param string $date
     * @return bool
     */
    public function setPlusMileageScheduler($date = null) {
        $schedulerDate = new \DateTime($date);

        $arrBind = [];
        $this->db->strField = 'og.sno, og.orderNo, og.goodsMileage, og.memberMileage, og.orderStatus, og.plusMileageFl, o.memNo';
        // 마일리지 지급 유예일이 있음 / 마일리지 지급 안함 / 환불완료가 아님
        $this->db->strWhere = 'og.mileageGiveDt = ? AND og.plusMileageFl = ? AND og.orderStatus != ?';
        $this->db->bind_param_push($arrBind, 's', $schedulerDate->format('Y-m-d'));
        $this->db->bind_param_push($arrBind, 's', 'n');
        $this->db->bind_param_push($arrBind, 's', 'r3');

        $this->db->strJoin = 'LEFT JOIN ' . DB_ORDER . ' as o ON og.orderNo = o.orderNo';
        $this->db->strOrder = 'og.sno asc';
        $query = $this->db->query_complete();
        $strSQL = 'SELECT ' . array_shift($query) . ' FROM ' . DB_ORDER_GOODS . ' as og ' . implode(' ', $query);
        $getData = $this->db->query_fetch($strSQL, $arrBind);

        foreach ($getData as $key => $val) {
            // 쿠폰적립은 회원 쿠폰 차감시 마일리지 별도 지급하기 때문에 빼주고 처리해야 함
            $giveMileage = $val['goodsMileage'] + $val['memberMileage'];
            if ($val['orderStatus'] != 'r3' && $val['plusMileageFl'] == 'n' && $val['memNo'] > 0 && $giveMileage > 0) {
                $mileageGiveData[$val['orderNo']]['sno'][$key] = $val['sno'];
                $mileageGiveData[$val['orderNo']]['giveMileage'][$key] = $giveMileage;
                $mileageGiveData[$val['orderNo']]['totalMileage'] += $giveMileage;
                $mileageGiveData[$val['orderNo']]['memNo'] = $val['memNo'];
            }
            unset($giveMileage);
        }

        foreach ($mileageGiveData as $orderNo => $val) {
            /** @var \Bundle\Component\Mileage\Mileage $mileage */
            $mileage = \App::load('\\Component\\Mileage\\Mileage');
            $mileage->setIsTran(false);
            $mileage->setSmsReserveTime(date('Y-m-d 08:00:00', strtotime('now')));

            if ($mileage->setMemberMileage($val['memNo'], $val['totalMileage'], Mileage::REASON_CODE_GROUP . Mileage::REASON_CODE_ADD_GOODS_BUY, 'o', $orderNo)) {
                $orderData['plusMileageFl'] = 'y';
                $orderData['plusRestoreMileageFl'] = 'n';
                $compareField = array_keys($orderData);
                $arrBind = $this->db->get_binding(DBTableField::tableOrderGoods(), $orderData, 'update', $compareField);
                $this->db->set_update_db(DB_ORDER_GOODS, $arrBind['param'], 'sno IN (\'' . implode('\',\'', $val['sno']) . '\')', $arrBind['bind']);
                unset($arrBind);
            }
        }

        return true;
    }

    /**
     * 주문상세페이지에서 주문상태를 변경시키는 버튼 유형 리스트
     *
     * @param string $orderStatusMode
     * @param array $goodsOrderStatusList
     * @param array $goodsOriginalOrderStatusList
     * @param string $orderNo
     *
     * @return array $orderViewStatusActionList
     */
    public function getOrderViewStatusActionList($orderStatusMode='', $goodsOrderStatusList=array(), $goodsOriginalOrderStatusList=array(), $orderNo='')
    {
        $orderViewStatusActionList = [
            'add' => '상품추가',
            'cancel' => '주문취소',
            'refund' => '상품환불',
            'exchange' => '상품교환',
            'back' => '상품반품',
            'restore' => '취소복원',
            'exchangeCancel' => '교환철회',
//            'backCancel' => '반품철회',
//            'refundCancel' => '환불철회',
//            'refundComplete' => '환불완료',
        ];

        if($orderStatusMode !== ''){
            switch($orderStatusMode){
                case 'order' :
                    if(in_array('o', $goodsOrderStatusList) && !Manager::isProvider()) {
                        $actionButtonList[] = 'add';
                        $actionButtonList[] = 'cancel';
                    }
                    if(in_array('p', $goodsOrderStatusList) || in_array('g', $goodsOrderStatusList)){
                        $actionButtonList[] = 'refund';
                        $actionButtonList[] = 'exchange';
                    }
                    if(in_array('d', $goodsOrderStatusList) || in_array('s', $goodsOrderStatusList)){
                        $actionButtonList[] = 'exchange';
                        $actionButtonList[] = 'back';
                    }
                    break;

                case 'cancel' : // 취소복원은 주문의 모든 상품의 상태가 입금대기, 취소 상품 일때만 가능 - 하나라도 결제완료 이후 면 복원 불가능
                    if(in_array('p', $goodsOrderStatusList) || in_array('g', $goodsOrderStatusList) || in_array('d', $goodsOrderStatusList) || in_array('s', $goodsOrderStatusList) || in_array('e', $goodsOrderStatusList) || in_array('z', $goodsOrderStatusList) || in_array('b', $goodsOrderStatusList) || in_array('r', $goodsOrderStatusList)) {

                    } else { // o, c
                        $actionButtonList = [
                            'restore',
                        ];
                    }
                    break;

                case 'exchange' : case 'exchangeCancel' : case 'exchangeAdd' :
                /*
                 * 교환철회 버튼 노출 조건
                 * 교환추가 상품 : 입금대기/결제완료/상품준비중
                 * 교환취소 상품 : 교환접수

                $possibleOriginalStatusArray = [
                    'e1',
                ];
                $possibleStatusArray = [
                    'o',
                    'p',
                    'g',
                ];
                foreach($goodsOriginalOrderStatusList as $key => $orderGoodsStatus){
                    if(in_array($orderGoodsStatus, $possibleOriginalStatusArray) || in_array(substr($orderGoodsStatus, 0, 1), $possibleStatusArray)){
                        unset($goodsOriginalOrderStatusList[$key]);
                    }
                }

                //공급사에서는 공급사자체의 상품외 다른 공급사의 상품이 같이 교환처리가 되어있으면 교환처리가 불가하다.
                $checkProviderButton = true;
                if(Manager::isProvider()){
                    $reOrderCalculation = \App::load('\\Component\\Order\\ReOrderCalculation');
                    $orderGoodsData = $reOrderCalculation->getOrderGoodsData($orderNo);
                    $scmNoList = array_filter(array_unique(array_column($orderGoodsData, 'scmNo')));
                    if(count($scmNoList) > 1){
                        $checkProviderButton = false;
                    }
                }

                if(count($goodsOriginalOrderStatusList) < 1 && $checkProviderButton === true){
                    $actionButtonList = [
                        'exchangeCancel',
                    ];
                }
                */
                break;

                case 'back' :
                    $actionButtonList = [
                        //'backCancel',
                    ];
                    break;

                case 'refund' :
                    $actionButtonList = [
                        //'refundCancel',
//                        'refundComplete',
                    ];
                    break;

                case 'fail':
                    $actionButtonList = [];
                    break;
            }
            $actionButtonList = array_values(array_unique(array_filter($actionButtonList)));
            $orderViewStatusActionList = array_intersect_key($orderViewStatusActionList, array_flip($actionButtonList));
        }

        return $orderViewStatusActionList;
    }

    /**
     * es_goods 주문 상품 갯수 계산을 위한 클레임정보 로드
     *
     * @param string $orderNo
     * @param array $orderGoodsSno
     * @param array $handleGroupCd
     *
     * @return array $orderHandleGroupOrderGoods
     */
    public function getOrderExchangeGoodsCntSet($orderNo, $orderGoodsSno, $handleGroupCd) {
        $orderReorderCalculation = \App::load('\\Component\\Order\\ReOrderCalculation');
        // 핸들 데이터 로드
        $orderHandleData = $orderReorderCalculation->getOrderExchangeHandle($orderNo, $handleGroupCd);
        $orderHandleGroupData = $this->getOrderGoodsData($orderNo, $orderGoodsSno, $orderHandleData[0]['sno']);
        if (count($orderHandleGroupData) > 0) {
            foreach ($orderHandleGroupData as $scmNo => $dataVal) {
                foreach ($dataVal as $goodsCheckData) {
                    $orderHandleGroupOrderGoods[] = $goodsCheckData;
                }
            }
        }
        return $orderHandleGroupOrderGoods;
    }

    public function getOrderUserHandle($orderNo, $orderGoodsNo = null, $handleMode = [], $handleFl = null, $handleNo = null)
    {
        $arrBind = $arrWhere = [];

        $arrWhere[] = 'ouh.orderNo = ?';
        $this->db->bind_param_push($arrBind, 'i', $orderNo);
        if (empty($orderGoodsNo) === false) {
            $arrWhere[] = 'ouh.userHandleGoodsNo = ?';
            $this->db->bind_param_push($arrBind, 'i', $orderGoodsNo);
        }
        if (empty($handleNo) === false) {
            $arrWhere[] = 'ouh.sno = ?';
            $this->db->bind_param_push($arrBind, 'i', $handleNo);
        }
        if (empty($handleMode[0]) === false && count($handleMode) > 0) {
            $arrWhere[] = 'ouh.userHandleMode IN (' . @implode(',', array_fill(0, count($handleMode), '?')) . ')';
            foreach ($handleMode as $value) {
                $this->db->bind_param_push($arrBind, 's', $value);
            }
        }
        if (empty($handleFl) === false) {
            $arrWhere[] = 'ouh.userHandleFl = ?';
            $this->db->bind_param_push($arrBind, 's', $handleFl);
        }

        if (Manager::isProvider()) {
            $arrWhere[] = 'EXISTS (SELECT 1 FROM ' . DB_ORDER_GOODS . ' WHERE (userHandleSno = ouh.sno OR sno = ouh.userHandleGoodsNo) AND scmNo = ? )';
            $this->db->bind_param_push($arrBind, 'i', Session::get('manager.scmNo'));
        }

        $this->db->strField = '*';
        $this->db->strWhere = implode(' AND ', gd_isset($arrWhere));
        $this->db->strOrder = 'sno asc';

        $query = $this->db->query_complete();
        $strSQL = 'SELECT ' . array_shift($query) . ' FROM ' . DB_ORDER_USER_HANDLE . ' ouh ' . implode(' ', $query);
        $data = $this->db->query_fetch($strSQL, $arrBind);

        return $data;
    }

    /**
     * order_view, refund_view 주문 상세/환불 상세 - 쿠폰/할인/혜택 정보 구분 dot 및 쿠폰정보셋팅
     *
     * @param array $data
     * @param int $originalDataCount
     * @param array $orderCoupon - 환불상세의 경우 orderCoupon 데이터를 로드 함
     * @return array $data
     * orderViewBenefitGoods(주문상품정보),
     * orderViewBenefitOriginalGoods(초기주문상품정보),
     * orderViewBenefitPrice(탭별 가격 정보),
     * orderCouponBenefitData(주문 쿠폰정보) <-> orderViewBenefitGoods[$key]['orderGoodsCouponData'] (주문상품별쿠폰정보)
     */
    public function getOrderViewBenefitInfoSet($data, $originalDataCount, $orderCoupon = array())
    {
        // 기존주문건 배열화
        if($originalDataCount > 0) { // 초기주문 테이블 갯수가 있을 경우 실행
            $orderReorderCalculation = \App::load('\\Component\\Order\\ReOrderCalculation');
            $orderOriginalGoods = $orderReorderCalculation->getOrderOriginalData($data['orderNo'], ['orderGoods']);

            // 기존주문건 handle 값 외 기존 초기 주문 데이터 삽입
            foreach($orderOriginalGoods['orderGoods'] as $ogKey => $ogVal) {
                if($ogVal['handleSno'] == 0) { // handleSno 없을 경우
                    $data['orderViewBenefitOriginalGoods'][$ogVal['orderCd']] = $ogVal; // 주문상품번호를 key로 삽입
                }
            }
        }
        // 주문 당시 회원 정책 - (회원추가할인, 회원중복할인 계산 시 사용)
        $orderMemberPolicy = json_decode($data['memberPolicy'], true);
        // 총 금액 가격 초기화 (총 상품할인, 총 주문할인, 총 배송비할인, 총 적립액, 총 마이앱할인)
        $data['orderViewBenefitPrice']['totalGoodsDc'] = $data['orderViewBenefitPrice']['totalOrderDc'] = $data['orderViewBenefitPrice']['totalDeliveryDc'] = $data['orderViewBenefitPrice']['totalMileageAdd'] = 0;

        // 마이앱 사용에 따른 분기 처리
        if ($this->useMyapp) {
            $data['orderViewBenefitPrice']['totalMyappDc'] = 0;
        }

        // 총 상품 금액 할인
        $data['orderViewBenefitPrice']['totalGoodsDc'] = ($data['totalCouponGoodsDcPrice'] + $data['totalGoodsDcPrice']);
        if($orderMemberPolicy['fixedOrderTypeDc'] == 'goods' || $orderMemberPolicy['fixedOrderTypeDc'] == 'option' || $orderMemberPolicy['fixedOrderTypeDc'] == 'brand' || empty($orderMemberPolicy['fixedOrderTypeDc']) === true) { // 회원추가할인(상품할인)
            $data['orderViewBenefitPrice']['totalGoodsDc'] = ($data['orderViewBenefitPrice']['totalGoodsDc'] + $data['totalMemberDcPrice']);
        }
        if($orderMemberPolicy['fixedOrderTypeOverlapDc'] == 'goods' || $orderMemberPolicy['fixedOrderTypeOverlapDc'] == 'option' || empty($orderMemberPolicy['fixedOrderTypeDc']) === true) { // 회원중복할인(상품할인)
            $data['orderViewBenefitPrice']['totalGoodsDc'] = ($data['orderViewBenefitPrice']['totalGoodsDc'] + $data['totalMemberOverlapDcPrice']);
        }
        // 총 주문할인금액
        $data['orderViewBenefitPrice']['totalOrderDc'] = ($data['totalCouponOrderDcPrice']);
        if($orderMemberPolicy['fixedOrderTypeDc'] == 'order') { // 회원추가할인(주문할인)
            $data['orderViewBenefitPrice']['totalOrderDc'] = ($data['orderViewBenefitPrice']['totalOrderDc'] + $data['totalMemberDcPrice'] );
        }
        if($orderMemberPolicy['fixedOrderTypeOverlapDc'] == 'order') { // 회원중복할인(주문할인)
            $data['orderViewBenefitPrice']['totalOrderDc'] = ($data['orderViewBenefitPrice']['totalOrderDc'] + $data['totalMemberOverlapDcPrice']);
        }

        // 총 배송비 할인금액
        $data['orderViewBenefitPrice']['totalDeliveryDc'] = ($data['totalCouponDeliveryDcPrice'] + $data['totalMemberDeliveryDcPrice']);

        // 마이앱 사용에 따른 분기 처리
        if ($this->useMyapp) {
            $data['orderViewBenefitPrice']['totalMyappDc'] = $data['totalMyappDcPrice'];
        }

        // 총 마일리지 적립금액
        $data['orderViewBenefitPrice']['totalMileageAdd'] = $data['totalMileage'];

        // 쿠폰/할인/혜택 상품 별 Dc 및 Add 가격 초기화
        $data['orderViewBenefitPrice']['goodsDc'] = $data['orderViewBenefitPrice']['orderDc'] = $data['orderViewBenefitPrice']['deliveryDc'] = $data['orderViewBenefitPrice']['mileageAdd'] = 0;

        // 주문 상세 - 쿠폰/할인/혜택 - 주문, 배송쿠폰 데이터(상품별x)
        $getOrderGoodsCouponData = [];
        if(empty($orderCoupon) == true) { // 주문 환불 상세 페이지에서는 orderCoupon 데이터가 존재함
            if($data['totalCouponOrderDcPrice'] > 0 || $data['totalCouponOrderMileage'] > 0 || $data['totalCouponDeliveryDcPrice'] > 0) {
                $getOrderGoodsCouponData = $data['orderCouponBenefitData']['orderCouponData'] = $this->getOrderCoupon($data['orderNo']);
            }
            if(empty($getOrderGoodsCouponData) == true) { // 주문기준 쿠폰 데이터 없을 경우
                $getOrderGoodsCouponData = $this->getOrderCoupon($data['orderNo']);
            }
        } else {
            $getOrderGoodsCouponData = $data['orderCouponBenefitData']['orderCouponData'] = $orderCoupon;
        }
        // 주문 상세 - 쿠폰/할인/혜택 - 상품 쿠폰 데이터 (orderCd 기준 재정의)
        $handleSnoCouponArray = []; // handleSno 처리 저장 임시 배열 (교환추가의 경우 쿠폰정보가 없음)
        $notOrderCdCouponArrayData = []; // orderCd가 0인 경우 별도 임시 배열에 저장
        foreach($getOrderGoodsCouponData as $ocKey => $ocVal) {
            if(array_key_exists($ocVal['orderCd'], $data['orderViewBenefitGoods'])) {
                $data['orderViewBenefitGoods'][$ocVal['orderCd']]['orderGoodsCouponData'][] = $ocVal;
            } else if($ocVal['orderCd'] == 0) {
                $notOrderCdCouponArrayData[] = $ocVal; // orderCd가 0인 경우 별도 임시 배열에 저장
            }
        }

        // 주문상품 체크 및 데이터변환 위한 배열(상품할인,주문할인,적립)
        $goodsDcFieldArray = ['goodsDcPrice', 'couponGoodsDcPrice', 'memberDcPrice', 'memberOverlapDcPrice', 'enuri']; // 상품할인 필드배열

        // 마이앱 사용에 따른 분기 처리
        if ($this->useMyapp) {
            array_push($goodsDcFieldArray, 'myappDcPrice');
        }

        $orderDcFieldArray = ['couponDc' => 'divisionCouponOrderDcPrice', 'memberDc' => 'memberDcPrice', 'memberOverlapDc'=> 'memberOverlapDcPrice']; // 주문할인 필드배열
        $addFieldArray = ['goodsMileage', 'memberMileage', 'divisionCouponOrderMileage']; // 적립필드배열

        // 주문상품 loop 데이터 가공
        ksort($data['orderViewBenefitGoods']); // 상품주문번호로 정렬
        foreach($data['orderViewBenefitGoods'] as $key => $val) {
            $preKey = $key - 1;
            $orderGoodsStatusMode = substr($val['orderStatus'], 0, 1); // 주문상품상태값
            $orderCancelFl = false; // 주문 교환/환불/취소 구분
            if($orderGoodsStatusMode == 'c' || $orderGoodsStatusMode == 'e' || $orderGoodsStatusMode == 'r') {
                $orderCancelFl = true;
            }
            $data['orderViewBenefitGoods'][$key]['orderCancelFl'] = $orderCancelFl; // 주문 교환/환불/취소 구분
            $data['orderViewBenefitGoods'][$key]['orderStatusConvert'] = $orderGoodsStatusMode;

            // 주문 기준 쿠폰의 경우 orderCd가 선언되어있지않아 삽입
            if(empty($notOrderCdCouponArrayData) == false) {
                foreach($notOrderCdCouponArrayData as $nOrderCdKey => $nOrderCdVal) {
                    $data['orderViewBenefitGoods'][$key]['orderGoodsCouponData'][] = $nOrderCdVal;
                }
            }
            // 추가상품일 경우 쿠폰정보 상위상품정보 받아오기($preKey)
            if($val['goodsType'] == 'addGoods' && $val['parentGoodsNo'] > 0) {
                $data['orderViewBenefitGoods'][$key]['orderGoodsCouponData'] = $data['orderViewBenefitGoods'][$preKey]['orderGoodsCouponData'];
            }
            // 교환 취소 상품일 경우 쿠폰정보 쿠폰정보 값 handleSno 키로 저장하기
            if($orderGoodsStatusMode == 'e') {
                $handleSnoCouponArray[$val['handleSno']] = $data['orderViewBenefitGoods'][$key]['orderGoodsCouponData'];
            }
            // 교환 추가 상품일 경우 쿠폰정보 상위 handleSno 정보 삽입
            if($orderGoodsStatusMode == 'z') {
                if(empty($handleSnoCouponArray[$val['handleSno']-1]) == false) {
                    $data['orderViewBenefitGoods'][$key]['orderGoodsCouponData'] = $handleSnoCouponArray[$val['handleSno']-1];
                }
            }

            // 상품할인 필드 배열 처리
            foreach($goodsDcFieldArray as $dcKey => $dcVal) { // 주문상품 체크 및 데이터변환
                if($orderCancelFl == true) { // 주문상태가 취소일 경우
                    // 최초 주문내역 가져오기(초기주문 테이블 갯수가 있을 경우 실행)
                    if($originalDataCount > 0) {
                        if(array_key_exists($val['orderCd'], $data['orderViewBenefitOriginalGoods']) == true) {
                            if($dcVal != 'enuri') {
                                if($data['orderViewBenefitOriginalGoods'][$val['orderCd']][$dcVal] > 0) {
                                    $val[$dcVal] = $data['orderViewBenefitOriginalGoods'][$val['orderCd']][$dcVal];
                                    $data['orderViewBenefitGoods'][$key][$dcVal] = $data['orderViewBenefitOriginalGoods'][$val['orderCd']][$dcVal];
                                }
                            } else {
                                if($data['orderViewBenefitOriginalGoods'][$val['orderCd']][$dcVal] != 0) {
                                    $val[$dcVal] = $data['orderViewBenefitOriginalGoods'][$val['orderCd']][$dcVal];
                                    $data['orderViewBenefitGoods'][$key][$dcVal] = $data['orderViewBenefitOriginalGoods'][$val['orderCd']][$dcVal];
                                }
                            }
                        }
                    }
                }
            }
            // 주문할인 필드 배열 처리
            foreach($orderDcFieldArray as $orderDcKey => $orderDcVal) { // 주문상품 체크 및 데이터변환
                if($orderCancelFl == true) {
                    // 최초 주문내역 가져오기
                    if($data['orderViewBenefitOriginalGoods'][$val['orderCd']][$orderDcVal] > 0 ) {
                        $val[$orderDcVal] = $data['orderViewBenefitOriginalGoods'][$val['orderCd']][$orderDcVal];
                        $data['orderViewBenefitGoods'][$key][$orderDcVal] = $data['orderViewBenefitOriginalGoods'][$val['orderCd']][$orderDcVal];
                    }
                }
            }
            // 적립 필드 배열 처리
            foreach($addFieldArray as $addKey => $addVal) { // 주문상품 체크 및 데이터변환
                if($orderCancelFl == true) {
                    // 최초 주문내역 가져오기
                    if($data['orderViewBenefitOriginalGoods'][$val['orderCd']][$addVal] > 0 ) {
                        $val[$addVal] = $data['orderViewBenefitOriginalGoods'][$val['orderCd']][$addVal];
                        $data['orderViewBenefitGoods'][$key][$addVal] = $data['orderViewBenefitOriginalGoods'][$val['orderCd']][$addVal];
                    }
                }
            }

            /* 상품적용 할인 금액 계산 시작 */
            if($data['totalGoodsDcPrice'] > 0 && $val['goodsDcPrice'] > 0) { // 상품자체할인(개별할인, 혜택할인)
                if($orderCancelFl == false) {
                    $data['orderViewBenefitPrice']['goodsDc'] += $val['goodsDcPrice']; //  - 상품할인금액
                }
            }
            if($data['totalCouponGoodsDcPrice'] > 0 && $val['couponGoodsDcPrice'] > 0) { // 상품쿠폰 할인
                if($orderCancelFl == false) {
                    $data['orderViewBenefitPrice']['goodsDc'] += $val['couponGoodsDcPrice']; //  - 상품쿠폰할인금액
                }
            }
            if($data['totalMemberDcPrice'] > 0 && $val['memberDcPrice'] > 0) { // 회원 등급 추가 할인
                if($orderMemberPolicy['fixedOrderTypeDc'] == 'goods' || $orderMemberPolicy['fixedOrderTypeDc'] == 'option' || $orderMemberPolicy['fixedOrderTypeDc'] == 'brand' || empty($orderMemberPolicy['fixedOrderTypeDc']) === true) {
                    if($orderCancelFl == false) {
                        $data['orderViewBenefitPrice']['goodsDc'] += $val['memberDcPrice']; //  - 상품회원추가할인금액
                    }
                }
            }
            if($data['totalMemberOverlapDcPrice'] > 0 && $val['memberOverlapDcPrice'] > 0 ) { // 회원 등급 중복 할인
                if($orderMemberPolicy['fixedOrderTypeOverlapDc'] == 'goods' || $orderMemberPolicy['fixedOrderTypeOverlapDc'] == 'option' || empty($orderMemberPolicy['fixedOrderTypeOverlapDc']) === true) {
                    if($orderCancelFl == false) {
                        $data['orderViewBenefitPrice']['goodsDc'] += $val['memberOverlapDcPrice']; //  - 상품회원중복할인금액
                    }
                }
            }
            if($data['totalEnuriDcPrice'] != 0 && $val['enuri'] != 0) { // 운영자 할인,할증(에누리)
                if($orderCancelFl == false) {
                    $data['orderViewBenefitPrice']['goodsDc'] += $val['enuri']; //  - 운영자할인
                }
            }

            // 마이앱 사용에 따른 분기 처리
            if ($this->useMyapp) {
                if($data['totalMyappDcPrice'] > 0 && $val['myappDcPrice'] > 0) { // 마이앱할인
                    if($orderCancelFl == false) {
                        $data['orderViewBenefitPrice']['goodsDc'] += $val['myappDcPrice']; //  - 마이앱할인금액
                    }
                }
            }

            /* 상품적용할인 금액 계산 끝 */

            /* 주문적용할인 금액 계산  시작 */
            if($data['totalCouponOrderDcPrice'] > 0) { // 주문적용 쿠폰
                if($orderCancelFl == false) {
                    $data['orderViewBenefitPrice']['orderDc'] += $val['divisionCouponOrderDcPrice']; //  - 주문쿠폰
                }
            }
            if(($data['totalMemberDcPrice'] > 0 && $val['memberDcPrice'] > 0) && ($orderMemberPolicy['fixedOrderTypeDc'] == 'order')) { // 회원 등급 추가 할인
                if($orderCancelFl == false) {
                    $data['orderViewBenefitPrice']['orderDc'] += $val['memberDcPrice']; //  - 회원추가할인
                }
            }
            if($data['totalMemberOverlapDcPrice'] > 0 && $val['memberOverlapDcPrice'] > 0 && ($orderMemberPolicy['fixedOrderTypeOverlapDc'] == 'order')) { // 회원 등급 중복
                if($orderCancelFl == false) {
                    $data['orderViewBenefitPrice']['orderDc'] += $val['memberOverlapDcPrice']; //  - 회원중복할인
                }
            }
            /* 주문적용할인 금액 계산 끝 */

            /* 배송비적용할인 금액 계산  시작 */
            if($key == 1) {
                $orderAllCancelFl = false;
                $orderStatusMode = substr($data['orderStatus'], 0, 1); // 주문상태값
                if($orderStatusMode == 'c' || $orderStatusMode == 'e' || $orderStatusMode == 'r') {
                    $orderAllCancelFl = true;
                }
                if($data['totalCouponDeliveryDcPrice'] > 0) { // 배송비적용 쿠폰
                    foreach($data['orderCouponBenefitData']['orderCouponData'] as $couponKey => $couponVal) {
                        if($couponVal['couponKindType'] != 'delivery' && $couponVal['couponUseType'] != 'delivery') continue;
                        if($orderAllCancelFl == false ) {
                            $data['orderViewBenefitPrice']['deliveryDc'] += $data['totalCouponDeliveryDcPrice'];
                        }
                    }
                }
                if($data['totalMemberDeliveryDcPrice'] > 0) { // 회원 등급 배송비 할인
                    if($orderAllCancelFl == false ) {
                        //$data['orderViewBenefitGoods'][$key]['dotted']['deliveryDc']['memberDc'] = $dotByPass;
                        $data['orderViewBenefitPrice']['deliveryDc'] += $data['totalMemberDeliveryDcPrice'];
                    }
                }

            }
            /* 배송비적용할인 금액 계산  끝 */

            /* 적립금액 계산 시작 */
            if($data['totalGoodsMileage'] > 0 && $val['goodsMileage'] > 0 ) { // 상품적립
                if($orderCancelFl == false) {
                    $data['orderViewBenefitPrice']['mileageAdd'] += $val['goodsMileage']; //  - 상품마일리지적립
                }
            }
            if($data['totalCouponOrderMileage'] > 0 || $data['totalCouponGoodsMileage'] > 0) { // 적립 쿠폰
                if($orderCancelFl == false ) {
                    if($val['divisionCouponOrderMileage'] > 0) {
                        $data['orderViewBenefitPrice']['mileageAdd'] += $val['divisionCouponOrderMileage'];
                    }
                    if($val['couponGoodsMileage'] > 0) {
                        $data['orderViewBenefitPrice']['mileageAdd'] += $val['couponGoodsMileage'];
                    }
                } else {
                    // 취소일 경우 divisionCouponOrderMileage 삽입
                    if($data['orderViewBenefitOriginalGoods'][$data['orderViewBenefitGoods'][$key]['sno']]['divisionCouponOrderMileage'] > 0) {
                        $val['divisionCouponOrderMileage'] = $data['orderViewBenefitOriginalGoods'][$data['orderViewBenefitGoods'][$key]['sno']]['divisionCouponOrderMileage'];
                    }
                    // 취소일 경우 couponGoodsMileage 삽입
                    if($data['orderViewBenefitOriginalGoods'][$data['orderViewBenefitGoods'][$key]['sno']]['couponGoodsMileage'] > 0) {
                        $val['couponGoodsMileage'] = $data['orderViewBenefitOriginalGoods'][$data['orderViewBenefitGoods'][$key]['sno']]['couponGoodsMileage'];
                    }
                }
            }
            if($data['totalMemberMileage'] > 0 && $val['memberMileage'] > 0) { // 회원 등급 혜택
                if($orderCancelFl == false) {
                    $data['orderViewBenefitPrice']['mileageAdd'] += $val['memberMileage']; //  - 회원마일리지적립
                }
            }
            /* 적립 금액 계산 끝 */
        }
        unset($orderOriginalGoods, $getOrderGoodsCouponData, $handleSnoCouponArray, $notOrderCdCouponArrayData);
        return $data;
    }

    /**
     * es_code 메모구분
     *
     */
    public function getOrderMemoList($isAdmin = false)
    {
        $strSQL = "SELECT itemCd, itemNm FROM " . DB_CODE . " WHERE groupCd = ? AND useFl = ? ORDER BY sort ASC";
        $arrBind = ['ss', '04004','y'];
        $ordMemoData = $this->db->query_fetch($strSQL, $arrBind);

        if($isAdmin) {
            foreach($ordMemoData as $k => $v) {
                $tmpValue = $v;
                $ordMemo[] = $tmpValue;
            }
        }

        return $ordMemo;
    }

    /**
     * 상품º주문번호별 관리자 메모 상품선택리스트
     *
     * @param array $orderNo
     *
     * @author choisueun <cseun555@godo.co.kr>
     */
    public function getOrderGoodsListToMemo($orderNo)
    {
        $this->db->strField = 'sno, mallSno, orderStatus, goodsNo, goodsNm, optionSno, optionInfo, optionTextInfo';
        $this->db->strWhere = 'orderNo = ?';
        $this->db->bind_param_push($arrBind, 's', $orderNo);

        // 쿼리 생성
        $query = $this->db->query_complete();
        $strSQL = 'SELECT ' . array_shift($query) . ' FROM ' . DB_ORDER_GOODS . ' ' . implode(' ', $query);
        $ordGoodsInfo = $this->db->query_fetch($strSQL, $arrBind);
        unset($arrBind);

        foreach($ordGoodsInfo as $fKey => $fVal){
            $reOptions = json_decode(stripcslashes($fVal['optionInfo']), true);
            foreach ($reOptions as $optionInfo) {
                $tmpOption[] = $optionInfo[0] . ':' . $optionInfo[1];
            }
            $tmpOption[] = $optionInfo[4];
            $fVal['optionInfo'] = implode(', ', $tmpOption);

            // 텍스트옵션
            $textOptions = json_decode(stripcslashes($fVal['optionTextInfo']), true);
            $fVal['optionTextInfo'] = $textOptions;
            $arrGoodsInfo[]= $fVal;

        }

        if($arrGoodsInfo){
            return $arrGoodsInfo;
        }
        return false;

    }

    /**
     * 상품º주문번호별 관리자 메모 저장
     *
     * @param array $arrData
     *
     * @author choisueun <cseun555@godo.co.kr>
     */
    public function insertAdminOrderGoodsMemo($arrData)
    {
        /*$compareField = array_keys($arrData);
        $arrBind = $this->db->get_binding(DBTableField::tableAdminOrderGoodsMemo(), $arrData, 'insert', $compareField);
        $this->db->set_insert_db(DB_ADMIN_ORDER_GOODS_MEMO, $arrBind['param'], $arrBind['bind'], 'y');
        unset($arrBind);*/
        $insertInfo['managerSno'] = Session::get('manager.sno');
        $insertInfo['orderNo'] = $arrData['orderNo'];
        $insertInfo['memoCd'] = $arrData['orderMemoCd'];
        $insertInfo['content'] = $arrData['adminOrderGoodsMemo'];

        // 수기주문 시
        if($arrData['mode'] == 'self_order'){
            $insertInfo['type'] = 'order';
            $this->arrBind = $this->db->get_binding(DBTableField::tableAdminOrderGoodsMemo(), $insertInfo, 'insert');
            $this->db->set_insert_db(DB_ADMIN_ORDER_GOODS_MEMO, $this->arrBind['param'], $this->arrBind['bind'], 'y');
            /*$strSQL = "INSERT INTO " . DB_ADMIN_ORDER_GOODS_MEMO . " SET `managerSno`=?, `orderNo` = ?, `type` = ?, `memoCd` = ?, `content` = ?, `regDt`=now()";
            $this->db->bind_param_push($arrBind, 'i', Session::get('manager.sno'));
            $this->db->bind_param_push($arrBind, 's', $arrData['orderNo']);
            $this->db->bind_param_push($arrBind, 's', 'order');
            $this->db->bind_param_push($arrBind, 's', $arrData['orderMemoCd']);
            $this->db->bind_param_push($arrBind, 's', $arrData['adminOrderGoodsMemo']);
            $this->db->bind_query($strSQL, $arrBind);
            unset($arrBind);*/
        }else {
            $insertInfo['type'] = $arrData['memoType'];
            if (count($arrData['sno']) >= 2) {
                foreach ($arrData['sno'] as $sno) {
                    $insertInfo['orderGoodsSno'] = $sno;
                    $this->arrBind = $this->db->get_binding(DBTableField::tableAdminOrderGoodsMemo(), $insertInfo, 'insert');
                    $this->db->set_insert_db(DB_ADMIN_ORDER_GOODS_MEMO, $this->arrBind['param'], $this->arrBind['bind'], 'y');

                    /*$strSQL = "INSERT INTO " . DB_ADMIN_ORDER_GOODS_MEMO . " SET `managerSno`=?, `orderNo` = ?, `orderGoodsSNo`=?, `type` = ?, `memoCd` = ?, `content` = ?, `regDt`=now()";
                    $this->db->bind_param_push($arrBind, 'i', Session::get('manager.sno'));
                    $this->db->bind_param_push($arrBind, 's', $arrData['orderNo']);
                    $this->db->bind_param_push($arrBind, 's', $sno);
                    $this->db->bind_param_push($arrBind, 's', $arrData['memoType']);
                    $this->db->bind_param_push($arrBind, 's', $arrData['orderMemoCd']);
                    $this->db->bind_param_push($arrBind, 's', $arrData['adminOrderGoodsMemo']);
                    $this->db->bind_query($strSQL, $arrBind);
                    unset($arrBind);*/
                }
            } else {
                $insertInfo['orderGoodsSno'] = $arrData['sno'][0];
                $this->arrBind = $this->db->get_binding(DBTableField::tableAdminOrderGoodsMemo(), $insertInfo, 'insert');
                $this->db->set_insert_db(DB_ADMIN_ORDER_GOODS_MEMO, $this->arrBind['param'], $this->arrBind['bind'], 'y');
                /*$strSQL = "INSERT INTO " . DB_ADMIN_ORDER_GOODS_MEMO . " SET `managerSno`=?, `orderNo` = ?, `orderGoodsSNo`=?, `type` = ?, `memoCd` = ?, `content` = ?, `regDt`=now()";
                $this->db->bind_param_push($arrBind, 'i', Session::get('manager.sno'));
                $this->db->bind_param_push($arrBind, 's', $arrData['orderNo']);
                $this->db->bind_param_push($arrBind, 's', $arrData['orderGoodsSno'][0]);
                $this->db->bind_param_push($arrBind, 's', $arrData['memoType']);
                $this->db->bind_param_push($arrBind, 's', $arrData['orderMemoCd']);
                $this->db->bind_param_push($arrBind, 's', $arrData['adminOrderGoodsMemo']);
                $this->db->bind_query($strSQL, $arrBind);
                unset($arrBind);*/
            }
        }

    }

    /**
     * 상품º주문번호별 관리자 메모 수정
     *
     * @param array $arrData
     *
     * @author choisueun <cseun555@godo.co.kr>
     */
    public function updateAdminOrderGoodsMemo($arrData)
    {
        // 마이그레이션된 메모 관리자 수정시 등록되도록
        if($arrData['oldManagerId'] == 0){
            $updateInfo['managerSno'] = \Session::get('manager.sno');
        }

        //$updateInfo['orderNo'] = $arrData['orderNo'];
        $updateInfo['type'] = $arrData['memoType'];
        $updateInfo['memoCd'] = $arrData['orderMemoCd'];
        $updateInfo['content'] = $arrData['adminOrderGoodsMemo'];
        if($arrData['memoType'] == 'goods'){
            // 상품주문번호 다중선택일경우
            if(count($arrData['orderGoodsSno']) >= 2){
                foreach ($arrData['orderGoodsSno'] as $orderGoodsSno) {
                    $updateInfo['orderGoodsSno'] = $orderGoodsSno;
                    $arrBind = $this->db->get_binding(DBTableField::tableAdminOrderGoodsMemo(), $updateInfo, 'update', array_keys($updateInfo), ['sno']);
                    $this->db->bind_param_push($arrBind['bind'], 's', $arrData['no']);
                    $this->db->set_update_db(DB_ADMIN_ORDER_GOODS_MEMO, $arrBind['param'], 'sno = ?', $arrBind['bind'], false);
                }
            }else{
                if($arrData['orderGoodsSno']){
                    $updateInfo['orderGoodsSno'] = $arrData['orderGoodsSno'][0];

                }
                if($arrData['sno']){
                    $updateInfo['orderGoodsSno'] = $arrData['sno'][0];
                }
                $arrBind = $this->db->get_binding(DBTableField::tableAdminOrderGoodsMemo(), $updateInfo, 'update', array_keys($updateInfo), ['sno']);
                $this->db->bind_param_push($arrBind['bind'], 's', $arrData['no']);
                $this->db->set_update_db(DB_ADMIN_ORDER_GOODS_MEMO, $arrBind['param'], 'sno = ?', $arrBind['bind'], false);
            }
        }else{
            $arrBind = $this->db->get_binding(DBTableField::tableAdminOrderGoodsMemo(), $updateInfo, 'update', array_keys($updateInfo), ['sno']);
            $this->db->bind_param_push($arrBind['bind'], 's', $arrData['no']);
            $this->db->set_update_db(DB_ADMIN_ORDER_GOODS_MEMO, $arrBind['param'], 'sno = ?', $arrBind['bind'], false);
        }
    }

    /**
     * 상품º주문번호별 관리자 메모 삭제
     *
     * @param array $arrData
     *
     * @author choisueun <cseun555@godo.co.kr>
     */
    public function deleteAdminOrderGoodsMemo($arrData)
    {
        $query = "UPDATE ".DB_ADMIN_ORDER_GOODS_MEMO." SET delFl = ?, deleter = ? WHERE sno = ? ";
        $this->db->bind_param_push($arrBind, 's', 'y');
        $this->db->bind_param_push($arrBind, 's', Session::get('manager.managerId'));
        $this->db->bind_param_push($arrBind, 's', $arrData['no']);
        $this->db->bind_query($query,$arrBind);
        unset($arrBind);
        /*if($arrData['type'] == 'goods'){
            $query = "UPDATE ".DB_ADMIN_ORDER_GOODS_MEMO." SET delFl = ? WHERE orderGoodsSno = ? ";
            $this->db->bind_param_push($arrBind, 's', 'y');
            $this->db->bind_param_push($arrBind, 's', $arrData['orderGoodsSno']);
            $this->db->bind_query($query,$arrBind);
            unset($arrBind);
        }else{
            $query = "UPDATE ".DB_ADMIN_ORDER_GOODS_MEMO." SET delFl = ? WHERE orderNo = ? ";
            $this->db->bind_param_push($arrBind, 's', 'y');
            $this->db->bind_param_push($arrBind, 's', $arrData['order']);
            $this->db->bind_query($query,$arrBind);
            unset($arrBind);
        }*/
    }

    /**
     * 상품º주문번호별 관리자 메모 데이터
     *
     * @param array $requestParams
     *
     * @author choisueun <cseun555@godo.co.kr>
     */
    public function getAdminOrderGoodsMemoData($requestParams)
    {
        unset($arrBind, $arrWhere, $arrWhereManager);
        $requestParams['delFl'] = 'n';
        $arrBind = $arrWhere = $arrWhereManager = [];

        // --- 페이지 설정
        if (empty($requestParams['page']) === true) {
            $requestParams['page'] = 1;
        }
        if (empty($requestParams['pageNum']) === true) {
            $requestParams['pageNum'] = 10;
        }

        $managerDataSearch = array('managerSno'=>'managerSno','managerNm'=>'managerNm','managerId'=>'managerId');

        $this->db->bindParameter('orderNo', $requestParams, $arrBind, $arrWhere, 'tableAdminOrderGoodsMemo', 'aogm');
        $this->db->bindParameter('delFl', $requestParams, $arrBind, $arrWhere, 'tableAdminOrderGoodsMemo', 'aogm');
        $this->db->bindParameterByKeyword($managerDataSearch, $requestParams, $arrBind, $arrWhere, 'tableManager', 'mn');
        //$this->db->bindParameterByDateTimeRange('regDt', $requestParams, $arrBind, $arrWhere, 'tableAdminOrderGoodsMemo', 'aogm');
        $this->db->strField = ' aogm.*, mn.managerId, mn.managerNm, c.itemNm ';
        $this->db->strWhere = implode(' AND ', $arrWhere);
        $this->db->strJoin = DB_ADMIN_ORDER_GOODS_MEMO . ' AS aogm LEFT JOIN ' . DB_CODE . ' AS c ON c.itemCd = aogm.memoCd LEFT JOIN ' . DB_MANAGER . ' AS mn ON aogm.managerSno=mn.sno';
        if($requestParams['sort']) {
            $this->db->strOrder = $requestParams['sort'];
        } else {
            $this->db->strOrder = 'aogm.regDt DESC';
        }

        $offset = $requestParams['page'] < 1 ? 0 : ($requestParams['page'] - 1) * $requestParams['pageNum'];
        $this->db->strLimit = $offset . ',' . $requestParams['pageNum'];
        $queryData = $this->db->query_complete();

        $query = ' SELECT ' . array_shift($queryData) . ' FROM ' . array_shift($queryData) . ' ' . implode(' ', $queryData);
        $data = $this->db->query_fetch($query, $arrBind);

        return $data;
    }

    /**
     * 상품º주문번호별메모 페이징 객체 반환 함수
     *
     * @param $requestParams
     * @param $queryString
     *
     * @return Page
     */
    public function getPage($requestParams, $queryString)
    {
        $query = 'SELECT COUNT(*) as cnt FROM ' . DB_ADMIN_ORDER_GOODS_MEMO;
        $total = $amount = $this->db->query_fetch($query . ' WHERE orderNo=' . $requestParams['orderNo'], null, false);
        $page = new Page($requestParams['page'], $total['cnt'], $amount['cnt'], $requestParams['pageNum']);
        $page->setUrl($queryString);

        return $page;
    }

    /**
     * 상품º주문번호별메모 가져오기(거래명세서)
     *
     * @param $orderNo
     *
     * @return array|object
     * @author choisueun <cseun555@godo.co.kr>
     */
    public function getAdminOrdGoodsMemoToPrint($orderNo)
    {
        $this->db->strField = 'content';
        $this->db->strWhere = 'orderNo = ? AND delFl = "n"';
        $this->db->strOrder = 'regDt DESC';
        $this->db->bind_param_push($arrBind, 's', $orderNo);

        $query = $this->db->query_complete();
        $strSQL = 'SELECT ' . array_shift($query) . ' FROM ' . DB_ADMIN_ORDER_GOODS_MEMO . implode(' ', $query);
        $getData = $this->db->query_fetch($strSQL, $arrBind);

        if (count($getData) > 0) {
            return gd_htmlspecialchars_stripslashes($getData);
        } else {
            return false;
        }
    }
    public function getUserHandleInfo($orderNo, $orderGoodsNo = null, $handleMode = [])
    {
        $getData = $this->getOrderUserHandle($orderNo, $orderGoodsNo, $handleMode, 'r');

        $data = [];
        foreach ($getData as $val) {
            $data[] = $this->userHandleText[$val['userHandleMode']];
        }

        return $data;
    }

    /**
     * getOrderCashReceiptOriginData
     *
     * 주문건에 해당하는 클레임이 어떤 클레임처리로 인해서 생긴지 체크
     * @param string $orderNo 주문번호
     *
     * @return cnt
     */
    /*public function getOrderCashReceiptOriginData($orderNo, $claimStatus)
    {
        $arrBind = [];
        $strSQL = ' SELECT COUNT(*) AS cnt FROM ' . DB_ORDER_CASH_RECEIPT_ORIGINAL . ' as ocro  WHERE ocro.orderNo = ? AND ocro.claimStatus = ? ORDER BY ocro.modDt desc LIMIT 1 ';
        $this->db->bind_param_push($arrBind, 's', $orderNo);
        $this->db->bind_param_push($arrBind, 's', $claimStatus);
        $res = $this->db->query_fetch($strSQL, $arrBind, false);
        unset($arrBind);

        $cnt = $res['cnt'];
        return $cnt;
    }*/

    /**
     * 상품 데이터 조회
     *
     * @param array $getValue
     * @param integer $minOrderGoodsNo
     * @param integer $maxOrderGoodsNo
     *
     * @return array
     * @throws
     */
    public function getOrderListGenerator($getValue, $minOrderGoodsNo = null, $maxOrderGoodsNo = null)
    {
        if ($getValue['mode'] == 'crema') {
            // 주문 상태
            foreach ($this->statusReceiptPossible as $val) {
                $tmpWhere[] = "og.orderStatus LIKE CONCAT(?, '%')";
                $this->db->bind_param_push($this->arrBind, 's', $val);
            }
            $this->arrWhere[] = '(' . implode(' OR ', $tmpWhere) . ')';
            unset($tmp, $tmpWhere);
            // 최근 1개월 데이터
            $this->arrWhere[] = 'og.regDt BETWEEN ? AND ?';
            $this->db->bind_param_push($this->arrBind, 's', date('Y-m-d', strtotime('-30 days')));
            $this->db->bind_param_push($this->arrBind, 's', date('Y-m-d'));
        }

        if (is_null($minOrderGoodsNo) === false && is_null($maxOrderGoodsNo) === false) {
            // between 조회
            $this->arrWhere[] = '(og.sno BETWEEN ? AND ?)';
            $this->db->bind_param_push($this->arrBind, 'i', $minOrderGoodsNo);
            $this->db->bind_param_push($this->arrBind, 'i', $maxOrderGoodsNo);
        }

        // join
        $join[] = ' LEFT JOIN ' . DB_ORDER . ' o ON o.orderNo = og.orderNo ';
        $join[] = ' LEFT JOIN ' . DB_ORDER_DELIVERY . ' od ON og.orderDeliverySno = od.sno ';
        $join[] = ' LEFT JOIN ' . DB_ORDER_INFO . ' oi ON (og.orderNo = oi.orderNo) 
                    AND (CASE WHEN od.orderInfoSno > 0 THEN od.orderInfoSno = oi.sno ELSE oi.orderInfoCd = 1 END)';
        if ($getValue['mode'] == 'crema') {
            $join[] = ' LEFT JOIN ' . DB_MEMBER . ' m ON o.memNo = m.memNo ';
        }

        $this->db->strField = implode(',', $getValue['strField']);
        $this->db->strJoin = implode('', $join);
        $this->db->strWhere = implode(' AND ', gd_isset($this->arrWhere));
        $this->db->strGroup = "CONCAT(og.orderNo,og.orderCd,og.goodsNo)";
        $this->db->strOrder = "og.regDt desc";
        $query = $this->db->query_complete();
        $strSQL = 'SELECT ' . array_shift($query) . ' FROM ' . DB_ORDER_GOODS . ' og ' . implode(' ', $query);
        $result = $this->db->query_fetch_generator($strSQL, $this->arrBind);

        return $result;
    }

    /**
     * 키워드 종류에 따라 검색 타입 반환
     *
     * @param string $keyName
     * @return string
     *
     */
    public function getKeySearchType($keyName)
    {
        $result = '';

        if (in_array($keyName, $this->equalSearch)) {
            $result = 'equalSearch';
        }
        if (in_array($keyName, $this->fullLikeSearch)) {
            $result = 'fullLikeSearch';
        }
        if (in_array($keyName, $this->endLikeSearch)) {
            $result = 'endLikeSearch';
        }

        return $result;
    }

    /**
     * 검색 타입에 따라서 Like Search or Eaqul Search 변환
     *
     * @param string $keyName
     * @param string $searchKind
     *
     */
    public function setKeySearchType($keyName, $searchKind)
    {
        $isInArrayEqualSearch = array_search($keyName, $this->equalSearch);
        $isInArrayFullLikeSearch = array_search($keyName, $this->fullLikeSearch);

        if ($searchKind == 'equalSearch') {
            if ($isInArrayEqualSearch == false) {
                array_push($this->equalSearch, $keyName);
            }
            if ($isInArrayFullLikeSearch) {
                unset($this->fullLikeSearch[$isInArrayFullLikeSearch]);
            }
        }

        if ($searchKind == 'fullLikeSearch') {
            if ($isInArrayFullLikeSearch == false) {
                array_push($this->fullLikeSearch, $keyName);
            }
            if ($isInArrayEqualSearch) {
                unset($this->equalSearch[$isInArrayEqualSearch]);
            }
        }
    }

    /*
     * es_memberOrderGoodsCount 에서 카운트 정보 가져온다
     *
     * @param int $memNo 주문번호
     * @param int $goodsNo 상품번호
     *
     * @return array
     *
     */
    public function getMemberOrderGoodsCountData($memNo = null, $goodsNo = 0)
    {
        if ($memNo == null) return false;

        $strSQL = 'SELECT * FROM ' . DB_MEMBER_ORDER_GOODS_COUNT . ' WHERE memNo = ? and goodsNo = ?';
        $arrBind = [
            'ii',
            $memNo,
            $goodsNo,
        ];

        $orderGoodsData = $this->db->query_fetch($strSQL, $arrBind);

        return $orderGoodsData[0];
    }

    public function getCountNoMemberOrder($searchData) {
        $arrBind = [];
        $arrWhere[] = 'memNo = 0';
        $arrWhere[] = ' regDt BETWEEN ? AND ? ';
        $this->db->bind_param_push($arrBind, 's', $searchData['treatDate'][0] . ' 00:00:00');
        $this->db->bind_param_push($arrBind, 's', $searchData['treatDate'][1] . ' 23:59:59');

        $this->db->strField = 'count(orderNo) as cnt';
        $this->db->strWhere = implode(' AND ', gd_isset($arrWhere));
        $query = $this->db->query_complete();
        $strSQL = 'SELECT ' . array_shift($query) . ' FROM ' . DB_ORDER . '  ' . implode(' ', $query);
        $query = $this->db->query_complete();
        $res = $this->db->query_fetch($strSQL, $arrBind, false);
        return $res['cnt'];
        //SELECT  count(orderNo) as cnt  FROM es_order    WHERE memNo = 0 AND  regDt BETWEEN `2019-09-24 00:00:00` AND `2019-09-30 23:59:59`
    }

    public function getCountSmsFlByOrderNo($orderNo) {
        if(empty($orderNo)) {
            return false;
        }
        $arrBind = $arrWhere = [];
        if (is_array($orderNo)) {
            foreach ($orderNo as $key => $val) {
                $this->db->bind_param_push($arrBind, 'i', $val);
                $orderNoTmp[] = '?';
            }
            $arrWhere[] =  'o.orderNo IN (' . implode(',', $orderNoTmp) . ')';
        } else {
            $arrWhere[] = 'o.orderNo = ?';
            $this->db->bind_param_push($arrBind,'i', $orderNo);
        }

        $this->db->strField = 'if(m.memNo > 0, m.smsFl, oi.smsFl) as smsFl';
        $join[] = 'LEFT JOIN ' . DB_ORDER_INFO . ' as oi ON oi.orderNo = o.orderNo';
        $join[] = ' LEFT JOIN ' . DB_MEMBER . ' AS m ON m.memNo = o.memNo ';
        $this->db->strJoin = implode(' ', $join);
        $this->db->strWhere = implode(' AND ', $arrWhere);
        $query = $this->db->query_complete();
        $strSQL = 'SELECT ' . array_shift($query) . ' FROM ' . DB_ORDER . ' as o  ' . implode(' ', $query);
        $res = $this->db->query_fetch($strSQL, $arrBind, false);
        $cnt = 0;
        foreach($res as $val) {
            if($val['smsFl'] == 'n') $cnt++;
        }
        //debug($this->db->getBindingQueryString($strSQL, $arrBind));
        return $cnt;
    }
}
