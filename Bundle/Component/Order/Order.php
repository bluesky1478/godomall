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
namespace Bundle\Component\Order;

use App;
use Component\Mail\MailAutoObserver;
use Component\Godo\NaverPayAPI;
use Component\Member\Member;
use Component\Naver\NaverPay;
use Component\Database\DBTableField;
use Component\Delivery\OverseasDelivery;
use Component\Deposit\Deposit;
use Component\ExchangeRate\ExchangeRate;
use Component\Mail\MailMimeAuto;
use Component\Mall\Mall;
use Component\Mall\MallDAO;
use Component\Member\Manager;
use Component\Member\Util\MemberUtil;
use Component\Mileage\Mileage;
use Component\Policy\Policy;
use Component\Sms\Code;
use Component\Sms\SmsAuto;
use Component\Sms\SmsAutoCode;
use Component\Sms\SmsAutoObserver;
use Component\Validator\Validator;
use Component\Goods\SmsStock;
use Component\Goods\KakaoAlimStock;
use Component\Goods\MailStock;
use Encryptor;
use Exception;
use Framework\Application\Bootstrap\Log;
use Framework\Debug\Exception\AlertOnlyException;
use Framework\Debug\Exception\AlertRedirectException;
use Framework\Helper\MallHelper;
use Framework\Utility\ArrayUtils;
use Framework\Utility\ComponentUtils;
use Framework\Utility\NumberUtils;
use Framework\Utility\StringUtils;
use Framework\Utility\UrlUtils;
use Globals;
use Logger;
use LogHandler;
use Request;
use Session;
use Framework\Utility\DateTimeUtils;

/**
 * 주문 class
 * @author Shin Donggyu <artherot@godo.co.kr>
 */
class Order
{
    /**
     * 사용하지 않음 차후 삭제
     */
    const ECT_ORDER_ERROR = 'Order.ECT_ORDER_ERROR';
    const ERROR_VIEW = 'ERROR_VIEW';
    const TEXT_NOT_EXIST_ORDERNO = 'NOT_EXIST_ORDERNO';
    const TEXT_NOT_EXIST_LOGIN_INFO = 'NOT_EXIST_LOGIN_INFO';
    const TEXT_NOT_EXIST_ORDER_INFO = 'NOT_EXIST_ORDER_INFO';
    const TEXT_REQUIRED_INVALID = '%s은(는) 필수 항목 입니다.';

    /**
     * 주문상태 코드명 상수
     */
    const ORDER_STATUS_ORDER = 'order';
    const ORDER_STATUS_PAYMENT = 'payment';
    const ORDER_STATUS_GOODS = 'goods';
    const ORDER_STATUS_DELIVERY = 'delivery';
    const ORDER_STATUS_SETTLE = 'settle';
    const ORDER_STATUS_CANCEL = 'cancel';
    const ORDER_STATUS_FAIL = 'fail';
    const ORDER_STATUS_BACK = 'back';
    const ORDER_STATUS_EXCHANGE = 'exchange';
    const ORDER_STATUS_EXCHANGE_ADD = 'exchangeAdd';
    const ORDER_STATUS_REFUND = 'refund';

    /**
     * 내부에서 사용하기 위해 만들어진 결제수단 코드 (마일리지/예치금)
     */
    const SETTLE_KIND_ZERO = 'gz'; // 0원으로 결제된 경우
    const SETTLE_KIND_MILEAGE = 'gm'; // 마일리지 사용
    const SETTLE_KIND_DEPOSIT = 'gd'; // 예치금 사용
    const SETTLE_KIND_REST = 'gr'; // 기타. 네이버페이에서 "나중에결제"로 결제된 경우
    const SETTLE_KIND_FINTECH_UNKNOWN = 'fu'; // 페이코/네이버페이에서 상품상세에서 주문시 결제수단을 알수 없는 경우

    /**
     * @var \Framework\Database\DBTool null|object 데이터베이스 인스턴스(싱글턴)
     */
    protected $db;

    /**
     * @var array 쿼리 조건 바인딩
     */
    protected $arrBind = [];

    /**
     * @var array 리스트 검색 조건
     */
    protected $arrWhere = [];

    /**
     * @var array 체크박스 체크 조건
     */
    protected $checked = [];

    /**
     * @var array 검색
     */
    protected $search = [];

    /**
     * @var array 사용테이블
     */
    protected $useTable = [];

    /**
     * @var string 주문상품의 기본 테이블 정렬
     */
    protected $orderGoodsOrderBy = 'og.orderNo desc, og.scmNo asc, og.orderDeliverySno asc, og.goodsDeliveryCollectFl asc, og.deliveryMEthodFl asc, og.regDt desc, og.orderCd asc';

    /**
     * @var string 주문상품의 기본 테이블 정렬 - 복수배송지 사용시
     */
    protected $orderGoodsMultiShippingOrderBy = 'og.orderNo desc, oi.orderInfoCd asc, og.scmNo asc, og.orderDeliverySno asc, og.goodsDeliveryCollectFl asc, og.deliveryMEthodFl asc, og.regDt desc, og.orderCd asc';

    /**
     * @var boolean 결제수단체크
     */
    protected $isSettleKind = false;

    /**
     * @var array 주문 기본정책
     */
    public $orderPolicy = [];

    /**
     * @var array 주문상태 기본정책
     */
    public $statusPolicy = [];

    /**
     * @var array 결제방법
     */
    public $settleKind = [];

    /**
     * @var integer 주문번호
     */
    public $orderNo;

    /**
     * @var string 주문 상품 명 (PG 에서 사용)
     */
    public $orderGoodsName;

    /**
     * @var array 주문 상품 명 배열 (PG 에서 사용)
     */
    public $arrGoodsName = [];

    /**
     * @var array 주문 상품번호 배열 (PG 에서 사용)
     */
    public $arrGoodsNo = [];

    /**
     * @var array 주문 상품가격 배열 (PG 에서 사용)
     */
    public $arrGoodsAmt = [];

    /**
     * @var array 주문 상품수량 배열 (PG 에서 사용)
     */
    public $arrGoodsCnt = [];

    /**
     * @var array 현재 상태에 대한 변경 가능 상태 기준표
     */
    public $statusStandardCode = [];

    /**
     * @var array 클래임 상태에서 변경 가능 상태 기준표 (주문상태내 클래임접수)
     */
    public $statusClaimCode = [];

    /**
     * @var array 주문 상태 단계 이름
     */
    public $statusStandardNm = [];

    /**
     * @var array 주문 상태 단계 코드
     */
    public $statusStandardCd = [];

    /**
     * @var array 클래임 상태에서 변경 가능 상태 기준표 (주문상태내 클래임접수)
     */
    public $statusListExclude = [];

    /**
     * @var array 반품/교환/환불관리 상태변경시 제외할 주문 코드들
     */
    public $statusHandleListExclude = [];

    /**
     * @var array 주문 리스트에서 셀을 합칠 주문 코드들
     */
    public $statusListCombine = [];

    /**
     * @var array 주문 상세에서 셀을 합칠 주문 코드들
     */
    public $statusViewCombine = [];

    /**
     * @var array 주문 리스트에서 재고를 보여줄 주문 코드들
     */
    public $statusStockView = [];

    /**
     * @var array 주문 상태 출력에서 제외할 주문 코드들
     */
    public $statusExcludeCd = [];

    /**
     * @var array 주문 상세에서 주문 상태에서 일괄 처리시 제외할 주문 코드들
     */
    public $standardExcludeCdOrder = [];

    /**
     * @var array
     */
    public $standardExcludeCd = [];

    /**
     * @var array 삭제 가능한 주문 코드들
     */
    public $statusDeleteCd = [];

    /**
     * @var array 영수증 신청 가능한 주문 코드들
     */
    public $statusReceiptPossible = [];

    /**
     * @var array 영수증 발급 가능한 주문 코드들
     */
    public $statusReceiptApprovalPossible = [];

    /**
     * @var array 영수증 신청 가능한 결제 방법
     */
    public $settleKindReceiptPossible = [];

    /**
     * @var array 주문상태 기본 정렬 순서 (mode full name - order, payment)
     */
    public $statusSort = [];

    /** @var \Bundle\Component\Mail\MailMimeAuto $mailMimeAuto */
    protected $mailMimeAuto;

    /**
     * @var array 사용자 클레임 승인 코드 (승인/대기/거부)
     */
    public $statusClaimHandleCode = [];

    /**
     * @var array 사용자 클레임 신청 코드 (환불/반품/교환)
     */
    public $statusUserClaimRequestCode = [];

    /**
     * @var array 주문금액 변경 코드
     */
    public $statusChangeOrderPriceCode = [];

    /**
     * @var bool 엑셀로 인해 상태가 변경되는지 여부(송장 일괄등록으로 인한 상태변경 이슈 해결)
     */
    protected $skipSendOrderInfo = false;

    protected  $channel = 'shop';

    protected  $logManagerNo = '';

    public $orderExchangeChangeDate;

    /**
     * @var boolean 복수배송지 사용여부
     */
    public $isUseMultiShipping;

    /**
     * @var bool 자동 주문 상태 변경 여부(ex. job 을 통한 상태변경)
     *           settleOrderAutomatically, cancelOrderAutomatically 에서 true 로 설정함
     */
    protected $changeStatusAuto = false;

    /**
     * @var 마이앱 사용유무
     */
    public $useMyapp;

    /**
     * 주문상태 단계
     */
    const ORDER_STATUS = [
        'o'=>'미입금',
        'p'=>'입금',
        'g'=>'미배송',
        'd'=>'배송',
        'c'=>'취소',
        'b'=>'반품',
        'e'=>'교환',
        'r'=>'환불',
    ];

    /**
     * 생성자
     */
    public function __construct()
    {
        if (!is_object($this->db)) {
            $this->db = \App::load('DB');
        }

        //교환프로세스 변경 시점.
        $this->orderExchangeChangeDate = '2017-12-20 05:00:00';

        //복수배송지 사용 여부
        $orderMultiShipping = App::load('\\Component\\Order\\OrderMultiShipping');
        $this->isUseMultiShipping = $orderMultiShipping->isUseMultiShipping();

        // 정책 순서
        $this->statusSort = [
            self::ORDER_STATUS_ORDER,
            self::ORDER_STATUS_PAYMENT,
            self::ORDER_STATUS_GOODS,
            self::ORDER_STATUS_DELIVERY,
            self::ORDER_STATUS_SETTLE,
            self::ORDER_STATUS_CANCEL,
            self::ORDER_STATUS_FAIL,
            self::ORDER_STATUS_BACK,
            self::ORDER_STATUS_EXCHANGE,
            self::ORDER_STATUS_EXCHANGE_ADD,
            self::ORDER_STATUS_REFUND,
        ];

        // 주문 기본정책 가져오기
        $this->orderPolicy = gd_policy('order.basic');

        // 주문 상태정책 가져오기 (순서에 의해 정책 설정이 결정되기 때문에 정해진 순서로 다시 정렬)
        $orderPolicy = gd_policy('order.status');
        foreach ($this->statusSort as $v) {
            foreach ($orderPolicy as $key => $val) {
                if (substr($key, 0, 1) == substr($v, 0, 1)) {
                    $this->statusPolicy[$key] = $val;
                }
            }
        }
        $this->statusPolicy['autoCancel'] = $orderPolicy['autoCancel'];

        // --- 주문 리스트에서 처리를 제외할 주문 코드들
        $this->statusListExclude = [
            'c1',
            'c2',
            'c3',
            'c4',
            'f1',
            'f2',
            'f3',
            //            'g2',
            //            'g3',
            //            'g4',
            'b1',
            'b2',
            'b3',
            'b4',
            'e1',
            'e2',
            'e3',
            'e4',
            'e5',
            'z1',
            'z2',
            'z3',
            'z4',
            'z5',
            'r1',
            'r2',
            'r3',
        ];

        // 반품/교환/환불관리 상태변경시 제외할 주문 코드들
        $this->statusHandleListExclude = [
            'b1',
            'b2',
            'b3',
            'b4',
            'e1',
            'e2',
            'e3',
            'e4',
            'e5',
            'z1',
            'z2',
            'z3',
            'z4',
            'z5',
            'r1',
            'r2',
            'r3',
        ];

        // --- 주문 일괄 처리/적립 마일리지 지급에서 제외할 주문 코드들 (주문상세페이지에서는 실패 제외)
        $this->statusExcludeCd = [
            'c',
            'f',
            'b',
            'e',
            'z',
            'r',
        ]; // 주문, 환불, 반품, 교환, 실패는 제외 처리

        // --- 주문 리스트에서 셀을 합칠 주문 코드들
        $this->statusListCombine = [
            'o',
            'c',
            'f',
        ];

        // --- 주문 상세에서 셀을 합칠 주문 코드들
        $this->statusViewCombine = [
            'c',
            'f',
        ];

        // --- 주문 리스트에서 재고를 보여줄 주문 코드들
        $this->statusStockView = [
            'o',
            'p',
            'g',
        ];

        // --- 주문 상태 출력에서 제외할 주문 코드들
        $this->standardExcludeCd = [
            'o',
            'c',
            'f',
            'b',
            'e',
            'z',
            'r',
        ];

        // --- 주문 상세에서 주문 상태에서 일괄 처리시 제외할 주문 코드들
        $this->standardExcludeCdOrder = [
            'g',
            'd',
            'c',
            's',
            'f',
            'b',
            'e',
            'z',
            'r',
        ];// 상품, 배송, 취소, 실패, 반품, 교환취소, 교환추가, 환불

        // --- 삭제 가능한 주문 코드들
        $this->statusDeleteCd = [
            'c',
            'f',
        ];

        // --- 영수증 출력 가능한 주문 코드들
        $this->statusReceiptPossible = [
            'o',
            'p',
            'g',
            'd',
            's',
        ];

        // --- 영수증 발급 가능한 주문 코드들
        $this->statusReceiptApprovalPossible = [
            'p',
            'g',
            'd',
            's',
        ];

        // --- 영수증 신청 가능한 결제 방법
        $this->settleKindReceiptPossible = [
            'gb',
            'pb',
            'pv',
            'eb',
            'ev',
            'gz',
        ];

        // --- 주문 통계에 사용되는 주문관련 코드들
        $this->statusStatisticsCd01 = [
            'o',
            'p',
            'g',
            'd',
            's',
            'b',
            'e',
            'r',
        ];
        $this->statusStatisticsCd02 = [
            'p',
            'g',
            'd',
            's',
            'b',
            'e',
            'r',
        ];

        // 현재가 주문 인경우 => 주문, 입금, 취소
        $this->statusStandardCode['o'] = [
            'o',
            'p',
            'c',
        ];

        // 현재가 입금 인경우 => 입금, 상품, 배송, 환불
        $this->statusStandardCode['p'] = [
            'o',
            'p',
            'g',
            'd',
            's',
            'r',
        ];

        // 현재가 상품 인경우 => 상품, 입금, 배송, 환불
        $this->statusStandardCode['g'] = [
            'p',
            'g',
            'd',
            's',
            'r',
        ];

        // 현재가 배송 인경우 => 배송, 상품, 반품, 교환
        $this->statusStandardCode['d'] = [
            'p',
            'g',
            'd',
            's',
            'b',
            'e',
        ];

        // 현재가 구매확정 인경우 => 배송, 상품, 반품, 교환
        $this->statusStandardCode['s'] = [
            'p',
            'g',
            'd',
            'b',
            'e',
            's',
        ];

        // 현재가 취소 인경우 => 취소, 주문
        $this->statusStandardCode['c'] = [
            'c',
            'o',
        ];

        // 현재가 실패 인경우 => 실패, 주문
        $this->statusStandardCode['f'] = [
            'f', // 결제시도->결제실패로 이동되지 않아서 주석 처리 했던것을 다시 주석 해제함
            'o',
            'p',
        ];

        // 현재가 반품 인경우 => 반품, 배송
        $this->statusStandardCode['b'] = [
            's',
            'b',
            'd',
            'r',
        ];

        // 현재가 교환 인경우 => 교환
        $this->statusStandardCode['e'] = [
            'e',
        ];

        // 현재가 교환추가 인경우 => 교환추가
        $this->statusStandardCode['z'] = [
            'z',
        ];

        // 현재가 환불 인경우 => 환불, 입금, 상품
        $this->statusStandardCode['r'] = [
            's',
            'r',
            'p',
            'g',
            'd',
            'b',
        ];

        // --- 주문 상태 단계 코드 / 이름
        $this->statusStandardCd = array_keys($this->statusStandardCode);
        $this->statusStandardNm = [
            'o' => __('주문'),
            'p' => __('입금'),
            'g' => __('상품'),
            'd' => __('배송'),
            's' => __('확정'),
            'c' => __('취소'),
            'f' => __('실패'),
            'b' => __('반품'),
            'e' => __('교환'),
            'z' => __('교환추가'),
            'r' => __('환불'),
        ];

        // 클래임 상태에서 변경 가능 상태 기준표 (주문상태내 클래임접수)
        $this->statusClaimCode['c'] = ['o'];// 현재가 취소인 경우
        $this->statusClaimCode['r'] = [
            'p',
            'g',
        ];// 현재가 환불인 경우
        $this->statusClaimCode['b'] = [
            'd',
            's',
        ];// 현재가 반품인 경우
        $this->statusClaimCode['e'] = [
            'd',
            's',
        ];// 현재가 교환인 경우

        // 사용자 클레임 승인 코드 (승인/대기/거부)
        $this->statusClaimHandleCode = [
            'y',
            'r',
            'n',
        ];

        // 사용자 클레임 신청 코드 (환불/반품/교환)
        $this->statusUserClaimRequestCode = [
            'r',
            'b',
            'e',
        ];

        // 주문금액 변경 코드
        $this->statusChangeOrderPriceCode = [
            'e5',
            'r3',
            'z2',
        ];

        //        $this->setPlusMileageVariation('1701191014382980', ['sno' => ['207'], 'changeStatus' => 's1']);

        // 마이앱 사용유무
        $this->useMyapp = gd_policy('myapp.config')['useMyapp'];
    }

    public function setChannel($channel){
        $this->channel = $channel;
    }

    public function getChannel(){
        return $this->channel;
    }

    public function setLogManagerNo($logManagerNo){
        $this->logManagerNo = $logManagerNo;
    }

    public function getLogManagerNo(){
        return $this->logManagerNo;
    }

    /**
     * 주문번호 생성
     *
     * @return string 16자리의 주문번호 생성 (년월일시분초마이크로초)
     */
    public function generateOrderNo()
    {
        // 0 ~ 999 마이크로초 중 랜덤으로 sleep 처리 (동일 시간에 들어온 경우 중복을 막기 위해서.)
        usleep(mt_rand(0, 999));

        // 0 ~ 99 마이크로초 중 랜덤으로 sleep 처리 (첫번째 sleep 이 또 동일한 경우 중복을 막기 위해서.)
        usleep(mt_rand(0, 99));

        // microtime() 함수의 마이크로 초만 사용
        list($usec) = explode(' ', microtime());

        // 마이크로초을 4자리 정수로 만듬 (마이크로초 뒤 2자리는 거의 0이 나오므로 8자리가 아닌 4자리만 사용함 - 나머지 2자리도 짜름... 너무 길어서.)
        $tmpNo = sprintf('%04d', round($usec * 10000));

        // PREFIX_ORDER_NO (년월일시분초) 에 마이크로초 정수화 한 값을 붙여 주문번호로 사용함, 16자리 주문번호임
        return PREFIX_ORDER_NO . $tmpNo;
    }

    /**
     * 주문서 정보 체크
     * 주문서에 작성된 정보의 유효성 체크
     *
     * @param array   $request      폼 요청 정보
     * @param boolean $defaultCheck 기본 체크 여부 (일반 주문 : true or 수기 주문 : false)
     *
     * @return array 재 가공된 주문 정보
     * @throws Exception
     */
    public function setOrderDataValidation(array $request, $defaultCheck = true)
    {
        gd_isset($request['deliveryVisit'], 'n');
        // xss clean
        $request = StringUtils::xssArrayClean($request);

        // Validation
        $validator = new Validator();

        // 영수증 발급 관련
        gd_isset($request['receiptFl'], 'n');
        gd_isset($request['cashCertFl'], 'c');
        if (isset($request['cashCertNo']) === true) {
            if (is_array($request['cashCertNo']) === true) {
                $request['cashCertNo'] = $request['cashCertNo'][$request['cashCertFl']];
            }
        }
        if ($request['receiptFl'] == 't') {
            $tmp['tax']['command'] = 'required';
            $tmp['tax']['required'] = true;
        }
        if ($request['receiptFl'] == 'r') {
            $tmp['cash']['command'] = 'required';
            $tmp['cash']['required'] = true;
        }

        // 인증 종류에 따른 발행용도 (사업자 번호는 지출 증빙용으로만)
        if ($request['cashCertFl'] == 'b') {
            $request['cashUseFl'] = 'e';
        }

        // 기본 체크 여부 (일반 주문 : true or 수기 주문 : false)
        if ($defaultCheck === true) {
            $defaultRequired = true;
        } else {
            $defaultRequired = false;
        }

        // 처리할 항목
        $arrCheckElement = [
            [
                'element'  => 'orderName',
                'command'  => 'required',
                'required' => true,
                'name'     => __('주문하시는 분'),
                'implode'  => ' ',
            ],
            [
                'element'  => 'orderCellPhone',
                'command'  => \App::getInstance('session')->has(SESSION_GLOBAL_MALL) ? 'required' : 'phone',
                'required' => true,
                'name'     => __('[주문자]휴대폰 번호'),
                'implode'  => '-',
            ],
            [
                'element'  => 'orderEmail',
                'command'  => 'email',
                'required' => $defaultRequired,
                'name'     => __('[주문자]이메일'),
                'implode'  => '@',
            ],
            [
                'element'  => 'orderZonecode',
                'command'  => '',
                'required' => false,
                'name'     => __('우편번호'),
                'implode'  => ' ',
            ],
            [
                'element'  => 'orderAddress',
                'command'  => '',
                'required' => false,
                'name'     => __('주소'),
                'implode'  => ' ',
            ],
            [
                'element'  => 'orderAddressSub',
                'command'  => '',
                'required' => false,
                'name'     => __('나머지 주소'),
                'implode'  => ' ',
            ],
            [
                'element'  => 'orderMemo',
                'command'  => '',
                'required' => false,
                'name'     => __('남기실 내용'),
                'implode'  => ' ',
            ],

            [
                'element'  => 'settleKind',
                'command'  => 'required',
                'required' => true,
                'name'     => __('결제방법'),
                'implode'  => ' ',
            ],
            [
                'element'  => 'receiptFl',
                'command'  => 'required',
                'required' => true,
                'name'     => __('영수증 발급 종류'),
                'implode'  => ' ',
            ],

            [
                'element'  => 'taxBusiNo',
                'command'  => gd_isset($tmp['tax']['command']),
                'required' => gd_isset($tmp['tax']['required']),
                'name'     => __('사업자 번호'),
                'implode'  => '-',
            ],
            [
                'element'  => 'taxCompany',
                'command'  => gd_isset($tmp['tax']['command']),
                'required' => gd_isset($tmp['tax']['required']),
                'name'     => __('회사명'),
                'implode'  => ' ',
            ],
            [
                'element'  => 'taxCeoNm',
                'command'  => gd_isset($tmp['tax']['command']),
                'required' => gd_isset($tmp['tax']['required']),
                'name'     => __('대표자명'),
                'implode'  => ' ',
            ],
            [
                'element'  => 'taxService',
                'command'  => gd_isset($tmp['tax']['command']),
                'required' => gd_isset($tmp['tax']['required']),
                'name'     => __('업태'),
                'implode'  => ' ',
            ],
            [
                'element'  => 'taxItem',
                'command'  => gd_isset($tmp['tax']['command']),
                'required' => gd_isset($tmp['tax']['required']),
                'name'     => __('종목'),
                'implode'  => ' ',
            ],
            [
                'element'  => 'taxZonecode',
                'command'  => gd_isset($tmp['tax']['command']),
                'required' => gd_isset($tmp['tax']['required']),
                'name'     => __('사업장 우편번호'),
                'implode'  => ' ',
            ],
            [
                'element'  => 'taxAddress',
                'command'  => gd_isset($tmp['tax']['command']),
                'required' => gd_isset($tmp['tax']['required']),
                'name'     => __('사업장 주소'),
                'implode'  => ' ',
            ],
            [
                'element'  => 'taxAddressSub',
                'command'  => gd_isset($tmp['tax']['command']),
                'required' => gd_isset($tmp['tax']['required']),
                'name'     => __('사업장 나머지 주소'),
                'implode'  => ' ',
            ],

            [
                'element'  => 'cashUseFl',
                'command'  => gd_isset($tmp['cash']['command']),
                'required' => gd_isset($tmp['cash']['required']),
                'name'     => __('발행 용도'),
                'implode'  => ' ',
            ],
            [
                'element'  => 'cashCertFl',
                'command'  => gd_isset($tmp['cash']['command']),
                'required' => gd_isset($tmp['cash']['required']),
                'name'     => __('인증 방법'),
                'implode'  => ' ',
            ],
            [
                'element'  => 'cashCertNo',
                'command'  => gd_isset($tmp['cash']['command']),
                'required' => gd_isset($tmp['cash']['required']),
                'name'     => __('인증 번호'),
                'implode'  => '-',
            ],
        ];
        if (in_array($request['deliveryVisit'], ['n', 'a'])) {
            $addArrCheckElement = [
                [
                    'element'  => 'receiverName',
                    'command'  => 'required',
                    'required' => true,
                    'name'     => __('받으실 분'),
                    'implode'  => ' ',
                ],
                [
                    'element'  => 'receiverCellPhone',
                    'command'  => \App::getInstance('session')->has(SESSION_GLOBAL_MALL) ? 'required' : 'phone',
                    'required' => true,
                    'name'     => __('[수취인]휴대폰 번호'),
                    'implode'  => '-',
                ],
                [
                    'element'  => 'receiverUseSafeNumberFl',
                    'command'  => '',
                    'required' => false,
                    'name'     => __('[수취인]안심 번호'),
                    'implode'  => ' ',
                ],
                [
                    'element'  => 'receiverZonecode',
                    'command'  => 'required',
                    'required' => true,
                    'name'     => __('[수취인]우편번호'),
                    'implode'  => ' ',
                ],
                [
                    'element'  => 'receiverAddress',
                    'command'  => 'required',
                    'required' => true,
                    'name'     => __('[수취인]주소'),
                    'implode'  => ' ',
                ],
                [
                    'element'  => 'receiverAddressSub',
                    'command'  => 'required',
                    'required' => true,
                    'name'     => __('[수취인]나머지 주소'),
                    'implode'  => ' ',
                ],
            ];
            $arrCheckElement = array_merge($arrCheckElement, $addArrCheckElement);
        }
        if (in_array($request['deliveryVisit'], ['y', 'a'])) {
            $addArrCheckElement = [
                [
                    'element'  => 'visitName',
                    'command'  => 'required',
                    'required' => true,
                    'name'     => __('방문자'),
                    'implode'  => ' ',
                ],
                [
                    'element'  => 'visitPhone',
                    'command'  => \App::getInstance('session')->has(SESSION_GLOBAL_MALL) ? 'required' : 'phone',
                    'required' => true,
                    'name'     => __('방문자연락처'),
                    'implode'  => '-',
                ],
            ];
            $arrCheckElement = array_merge($arrCheckElement, $addArrCheckElement);
        }
        if (\Component\Order\OrderMultiShipping::isUseMultiShipping() === true && \Globals::get('gGlobal.isFront') === false && $request['multiShippingFl'] == 'y') {
            foreach ($request['selectGoods'] as $key => $val) {
                if ($key > 0) {
                    $addArrCheckElement = [
                        [
                            'element'  => 'receiverNameAdd[' . $key . ']',
                            'command'  => 'required',
                            'required' => true,
                            'name'     => __('받으실 분'),
                            'implode'  => ' ',
                        ],
                        [
                            'element'  => 'receiverCellPhoneAdd[' . $key . ']',
                            'command'  => \App::getInstance('session')->has(SESSION_GLOBAL_MALL) ? 'required' : 'phone',
                            'required' => true,
                            'name'     => __('[수취인]휴대폰 번호'),
                            'implode'  => '-',
                        ],
                        [
                            'element'  => 'receiverUseSafeNumberFl[' . $key . ']',
                            'command'  => '',
                            'required' => false,
                            'name'     => __('[수취인]안심 번호'),
                            'implode'  => ' ',
                        ],
                        [
                            'element'  => 'receiverZonecodeAdd[' . $key . ']',
                            'command'  => 'required',
                            'required' => true,
                            'name'     => __('[수취인]우편번호'),
                            'implode'  => ' ',
                        ],
                        [
                            'element'  => 'receiverAddressAdd[' . $key . ']',
                            'command'  => 'required',
                            'required' => true,
                            'name'     => __('[수취인]주소'),
                            'implode'  => ' ',
                        ],
                        [
                            'element'  => 'receiverAddressSubAdd[' . $key . ']',
                            'command'  => 'required',
                            'required' => true,
                            'name'     => __('[수취인]나머지 주소'),
                            'implode'  => ' ',
                        ],
                    ];
                    $arrCheckElement = array_merge($arrCheckElement, $addArrCheckElement);
                }/* else {
                    $request['receiverNameAdd'][$key] = $request['receiverName'];
                    $request['receiverCountryCodeAdd'][$key] = $request['receiverCountryCode'];
                    $request['receiverCityAdd'][$key] = $request['receiverCity'];
                    $request['receiverStateAdd'][$key] = $request['receiverState'];
                    $request['receiverAddressAdd'][$key] = $request['receiverAddress'];
                    $request['receiverAddressSubAdd'][$key] = $request['receiverAddressSub'];
                    $request['receiverZonecodeAdd'][$key] = $request['receiverZonecode'];
                    $request['receiverZipcodeAdd'][$key] = $request['receiverZipcode'];
                    $request['receiverZipcodeTextAdd'][$key] = $request['receiverZipcodeText'];
                    $request['receiverPhonePrefixCodeAdd'][$key] = $request['receiverPhonePrefixCode'];
                    $request['receiverPhoneAdd'][$key] = $request['receiverPhone'];
                    $request['receiverCellPhonePrefixCodeAdd'][$key] = $request['receiverCellPhonePrefixCode'];
                    $request['receiverCellPhoneAdd'][$key] = $request['receiverCellPhone'];
                    $request['orderMemoAdd'][$key] = $request['orderMemo'];
                    $request['reflectApplyDeliveryAdd'][$key] = $request['reflectApplyDelivery'];
                }*/
            }
        }

        // 해외몰인 경우 체크 항목 추가
        $arrOverseasCheckElement = [
            [
                'element'  => 'orderCellPhonePrefixCode',
                'command'  => 'required',
                'required' => true,
                'name'     => __('[주문자]휴대폰 번호 국제코드'),
                'implode'  => ' ',
            ],
            [
                'element'  => 'receiverCountryCode',
                'command'  => 'required',
                'required' => true,
                'name'     => __('[수취인]배송국가'),
                'implode'  => '-',
            ],
            [
                'element'  => 'receiverCellPhonePrefixCode',
                'command'  => 'required',
                'required' => true,
                'name'     => __('[수취인]휴대폰 번호 국제코드'),
                'implode'  => '-',
            ],
            [
                'element'  => 'receiverState',
                'command'  => 'required',
                'required' => true,
                'name'     => __('[수취인]주/지방/지역'),
                'implode'  => ' ',
            ],
            [
                'element'  => 'receiverCity',
                'command'  => 'required',
                'required' => true,
                'name'     => __('[수취인]도시'),
                'implode'  => ' ',
            ],
        ];

        // 해외상점에서만 적용되도록 처리
        if (Globals::get('gGlobal.isFront')) {
            $arrCheckElement = array_merge($arrCheckElement, $arrOverseasCheckElement);
        }

        // post 값 체크
        foreach ($arrCheckElement as $validData) {
            if ($validData['required'] === true) {
                // 필수 값인 경우 값이 선언되어 있지 않으면 Exception 처리
                if (gd_isset($request[$validData['element']]) === false) {
                    throw new AlertOnlyException(sprintf(__('%s 항목이 잘못 되었습니다.'), $validData['name']));
                }
            } else {
                // 필수 값이 아닌 경우 선언되어 있지 않다면 빈값
                gd_isset($request[$validData['element']], '');
            }

            // 배열 값이면 implode 처리
            if (is_array($request[$validData['element']]) === true) {
                $request[$validData['element']] = ArrayUtils::removeEmpty($request[$validData['element']]);
                $request[$validData['element']] = implode($validData['implode'], $request[$validData['element']]);
            }

            // 이외 항목 처리를 위한 배열 값 생성
            $arrCheck[] = $validData['element'];

            // 항목별 Validation
            $validator->add($validData['element'], $validData['command'], $validData['required'], '{' . $validData['name'] . '}');
        }

        // 처리 이외 항목의 경우 다른 배열로 대체
        $tmpArr = array_keys($request);
        $setData = [];
        foreach ($tmpArr as $pVal) {
            if (in_array($pVal, $arrCheck) === false) {
                $setData[$pVal] = $request[$pVal];
            }
        }

        // 비회원 체크
        if (gd_is_login() === false && MemberUtil::checkLogin() == 'guest') {
            true;
        }

        // Validation 결과
        if ($validator->act($request, true) === false) {
            throw new Exception(implode("\n", $validator->errors));
        }

        // 다시 함침
        $request = array_merge($request, $setData);

        return $request;
    }

    /**
     * 주문 프로세스
     *
     * @param array   $cartInfo
     * @param array   $orderInfo
     * @param array   $order
     * @param boolean $isWrite
     *
     * @throws Exception
     * @author Jong-tae Ahn <qnibus@godo.co.kr>
     * @author su
     */
    public function saveOrder($cartInfo, $orderInfo, $order, $isWrite = false)
    {
        // 주문상품이 없는 경우 처리
        if (empty($cartInfo) === true) {
            throw new Exception(__('주문하실 상품이 없습니다.'));
        }

        // 주문번호 생성
        $this->orderNo = $this->generateOrderNo();

        // 주문로그 저장
        \Logger::channel('order')->info('OREDR NO : ' . $this->orderNo, $orderInfo);

        // 주문정보 설정에서 재설정됨
        $this->orderGoodsName = __('주문상품');

        // 결제 금액이 0원인 경우 결제수단을 전액할인(gz)으로 강제 적용 및 주문 채널을 shop 으로 고정
        if ($order['settlePrice'] == 0) {
            $orderInfo['settleKind'] = self::SETTLE_KIND_ZERO;
            $orderInfo['orderChannelFl'] = 'shop';
        }

        // 결제 방법에 따른 주문 단계 설정
        if ($orderInfo['settleKind'] == 'gb') {
            $orderStatusPre = 'o1'; // 무통장입금인 경우 입금대기 상태로
        } elseif ($orderInfo['settleKind'] == self::SETTLE_KIND_ZERO) {
            $orderStatusPre = 'p1'; // 전액할인인 경우 결제완료 상태로
        } else {
            $orderStatusPre = 'f1'; // PG 결제의 경우 결제시도 상태로
        }

        // 회원이 아닌 경우 적립마일리지 0원 처리
        if (empty($order['memNo']) === true || $order['memNo'] == 0) {
            $order['totalGoodsMileage'] = 0;// 총 상품 적립 마일리지
            $order['totalMemberMileage'] = 0;// 총 회원 적립 마일리지
            $order['totalCouponGoodsMileage'] = 0;// 총 상품쿠폰 적립 마일리지
            $order['totalCouponOrderMileage'] = 0;// 총 주문쿠폰 적립 마일리지
            $order['totalMileage'] = 0;
        }

        // 주문 추가 필드 정보
        if ($orderInfo['addFieldConf'] == 'y') {
            $addFieldData = $this->getOrderAddFieldSaveData($orderInfo['addField']);
        }

        // 주문 추가 필드 정보 json 으로 기본 json타입 빈값 처리를 위한 if 밖 처리
        $order['addField'] = json_encode($addFieldData, JSON_UNESCAPED_UNICODE);

        // 장바구니 상품 정보 저장 설정을 위한 초기화
        $orderGoodsCnt = 0;
        $this->arrGoodsName = [];
        $this->arrGoodsNo = [];
        $this->arrGoodsAmt = [];
        $this->arrGoodsCnt = [];
        $goodsCouponInfo = [];
        $arrOrderGoodsSno = [];

        // 주문할인 금액 안분을 위한 데이터 초기화
        $order['divisionUseMileage'] = 0;
        $order['divisionUseDeposit'] = 0;
        $order['divisionCouponDcPrice'] = 0;
        $order['divisionCouponMileage'] = 0;

        // 절사 내용
        $truncPolicy = Globals::get('gTrunc.goods');

        // 쿠폰 정책
        $couponPolicy = gd_policy('coupon.config');

        // 쿠폰 정책에서 쿠폰만사용일때 회원등급 할인 적립금 제거 처리
        $setMemberDcMileageZero = 'F';
        if ($order['totalCouponOrderDcPrice'] > 0 || $order['totalCouponDeliveryDcPrice'] > 0 || $order['totalCouponOrderMileage'] > 0) {
            if ($couponPolicy['couponUseType'] == 'y' && $couponPolicy['chooseCouponMemberUseType'] == 'coupon') {
                $setMemberDcMileageZero = 'T';
            }
        }

        // 배송비에 안분되어야 할 부가결제금액 (0원의 -를 막기위해 차감처리를 위한 변수)
        $tmpMinusDivisionDeliveryUseDeposit = 0;
        $tmpMinusDivisionDeliveryUseMileage = 0;

        // 안분해야 할 데이터 초기화
        $tmpDivisionGoodsUseDepositSum = 0;
        $tmpDivisionGoodsUseMileageSum = 0;
        $tmpDivisionGoodsCouponOrderDcPriceSum = 0;
        $tmpDivisionCouponDeliveryDcPriceSum = 0;
        $tmpDivisionGoodsCouponMileageSum = 0;
        $divisionUseDeposit = $order['useDeposit'];
        $divisionUseMileage = $order['useMileage'];
        $divisionCouponOrderDcPrice = $order['totalCouponOrderDcPrice'];
        $divisionCouponDeliveryDcPrice = $order['totalCouponDeliveryDcPrice'];
        $divisionCouponOrderMileage = $order['totalCouponOrderMileage'];

        // 주문할인 금액 안분 작업
        foreach ($cartInfo as $sKey => $sVal) {
            foreach ($sVal as $dKey => $dVal) {
                $totalOriginGoodsPrice[$dKey] = 0;
                foreach ($dVal as $gKey => $gVal) {
                    // 순수 할인된 상품의 결제금액 (추가상품 제외)
                    $originGoodsPrice = $gVal['price']['goodsPriceSubtotal'];
                    if (is_numeric($gVal['price']['goodsPriceTotal']) === true) {
                        $originGoodsPrice = $gVal['price']['goodsPriceTotal'];
                    }

                    // 최종 배송비의 안분된 예치금/마일리지를 다시 상품으로 안분하기 위한 기준 금액
                    $totalOriginGoodsPrice[$dKey] += $originGoodsPrice;

                    // 전체 순수할인된 상품금액 대비 비율 산정 (소수점까지 표현)
                    $goodsDcRate = ($originGoodsPrice / $order['settleTotalGoodsPrice']);

                    // 사용주문쿠폰 주문할인 안분 금액
                    $tmpDivisionGoodsCouponOrderDcPrice = NumberUtils::getNumberFigure($divisionCouponOrderDcPrice * $goodsDcRate, $truncPolicy['unitPrecision'], 'round');
                    $cartInfo[$sKey][$dKey][$gKey]['price']['divisionCouponOrderDcPrice'] = $tmpDivisionGoodsCouponOrderDcPrice;
                    $tmpDivisionGoodsCouponOrderDcPriceSum += $tmpDivisionGoodsCouponOrderDcPrice;

                    // 예치금, 마일리지는 모든 할인들이 반영된 순수금액 내에서 사용 가능하므로 주문쿠폰 할인을 반영하여 계산한다.
                    $goodsDcRateWithDelivery = (($originGoodsPrice - (int)$tmpDivisionGoodsCouponOrderDcPrice) / $order['settleTotalGoodsPriceWithDelivery']);

                    // 상품번호에 따른 기획전 sno 가져오기
                    // TODO 추후 기획전 검색 들어갈때 작업 예정
                    //                    $arrBindTheme = [];
                    //                    $strWhere = 'goodsNo LIKE concat(\'%\',?,\'%\') AND kind = ?';
                    //                    $this->db->bind_param_push($arrBindTheme, 'i', $gVal['goodsNo']);
                    //                    $this->db->bind_param_push($arrBindTheme, 's', 'event');
                    //                    $strSQL = 'SELECT sno FROM ' . DB_DISPLAY_THEME . ' WHERE ' . $strWhere . ' ORDER BY sno DESC LIMIT 0, 1';
                    //                    $getData = $this->db->query_fetch($strSQL, $arrBindTheme, false);
                    //                    $gVal['evnetSno'] = $getData['sno'];
                    //                    unset($arrBindTheme);


                    // 사용예치금 주문할인 안분 금액
                    $tmpDivisionGoodsUseDeposit = NumberUtils::getNumberFigure($divisionUseDeposit * $goodsDcRateWithDelivery, $truncPolicy['unitPrecision'], 'round');
                    $cartInfo[$sKey][$dKey][$gKey]['price']['divisionUseDeposit'] = $tmpDivisionGoodsUseDeposit;
                    $tmpDivisionGoodsUseDepositSum += $tmpDivisionGoodsUseDeposit;

                    // 사용마일리지 주문할인 안분 금액
                    $tmpDivisionGoodsUseMileage = NumberUtils::getNumberFigure($divisionUseMileage * $goodsDcRateWithDelivery, $truncPolicy['unitPrecision'], 'round');
                    $cartInfo[$sKey][$dKey][$gKey]['price']['divisionUseMileage'] = $tmpDivisionGoodsUseMileage;
                    $tmpDivisionGoodsUseMileageSum += $tmpDivisionGoodsUseMileage;

                    // 적립주문쿠폰 주문적립 안분 금액
                    $tmpDivisionGoodsCouponOrderMileage = NumberUtils::getNumberFigure($divisionCouponOrderMileage * $goodsDcRate, $truncPolicy['unitPrecision'], 'round');
                    $cartInfo[$sKey][$dKey][$gKey]['price']['divisionCouponOrderMileage'] = $tmpDivisionGoodsCouponOrderMileage;
                    $tmpDivisionGoodsCouponMileageSum += $tmpDivisionGoodsCouponOrderMileage;

                    // 복합과세 계산을 위해 주문할인 금액까지 모두 할인 적용된 상품의 실 결제금액
                    $cartInfo[$sKey][$dKey][$gKey]['price']['taxableGoodsPrice'] = $originGoodsPrice - ($tmpDivisionGoodsUseDeposit + $tmpDivisionGoodsUseMileage + $tmpDivisionGoodsCouponOrderDcPrice);

                    if ($setMemberDcMileageZero == 'T') {
                        $cartInfo[$sKey][$dKey][$gKey]['price']['taxableGoodsPrice'] += ($cartInfo[$sKey][$dKey][$gKey]['price']['memberDcPrice'] + $cartInfo[$sKey][$dKey][$gKey]['price']['memberOverlapDcPrice']);
                    }

                    // 주문상품의 갯수 카운트
                    $orderGoodsCnt++;
                }

                // 배송비 할인쿠폰 안분 작업
                $tmpDivisionCouponDeliveryDcPrice = 0;
                $tmpDivisionMemberDeliveryDcPrice = 0;
                if ($order['totalDeliveryCharge'] > 0) {
                    $totalDeliveryCharge = $order['totalDeliveryCharge'];
                    // 전체배송비(배송비 할인쿠폰이 적용된 금액) 대비 비율 산정하며 소수점까지 표현한다.
                    $deliveryCharge = $order['totalGoodsDeliveryAreaCharge'][$dKey];
                    if ($order['totalMemberDeliveryDcPrice'] <= 0) {
                        $deliveryCharge += $order['totalGoodsDeliveryPolicyCharge'][$dKey];
                    } else {
                        $totalDeliveryCharge -= $order['totalMemberDeliveryDcPrice'];
                    }
                    $deliveryDcRate = ($deliveryCharge / ($totalDeliveryCharge));

                    // 배송비쿠폰 주문할인 안분 금액
                    $tmpDivisionCouponDeliveryDcPrice = NumberUtils::getNumberFigure($divisionCouponDeliveryDcPrice * $deliveryDcRate, $truncPolicy['unitPrecision'], 'round');

                    // 회원 배송비 무료 할인 금액
                    if($order['totalMemberDeliveryDcPrice'] > 0){
                        // 회원 배송비 무료 할인은 정책 배송비 금액에만 적용됨.
                        $tmpDivisionMemberDeliveryDcPrice = $order['totalGoodsDeliveryPolicyCharge'][$dKey];
                    }
                }

                // 회원 배송비 무료 할인 금액
                $order['divisionMemberDeliveryDcPrice'][$dKey] = $tmpDivisionMemberDeliveryDcPrice;

                // 나머지 금액 계산을 위한 총합 구하기
                $order['divisionDeliveryCharge'][$dKey] = $tmpDivisionCouponDeliveryDcPrice;
                $tmpDivisionCouponDeliveryDcPriceSum += $tmpDivisionCouponDeliveryDcPrice;

                // 배송비 예치금/마일리지 안분 작업
                $tmpDivisionDeliveryUseDeposit = 0;
                $tmpDivisionDeliveryUseMileage = 0;
                if ($order['settleTotalDeliveryCharge'] > 0) {
                    // 배송비 - 배송비 할인쿠폰 - 회원배송비무료가 적용된 금액을 기준으로 남은 실결제금액을 안분처리 한다.
                    $deliveryDcRateWithDelivery = (($order['totalGoodsDeliveryPolicyCharge'][$dKey] + $order['totalGoodsDeliveryAreaCharge'][$dKey] - $tmpDivisionCouponDeliveryDcPrice - $tmpDivisionMemberDeliveryDcPrice) / $order['settleTotalGoodsPriceWithDelivery']);

                    // 배송비 사용예치금 주문할인 안분 금액
                    $tmpDivisionDeliveryUseDeposit = NumberUtils::getNumberFigure($divisionUseDeposit * $deliveryDcRateWithDelivery, $truncPolicy['unitPrecision'], 'round');
                    $order['divisionDeliveryUseDeposit'][$dKey] = $tmpDivisionDeliveryUseDeposit;
                    $tmpDivisionGoodsUseDepositSum += $tmpDivisionDeliveryUseDeposit;

                    // 배송비 사용마일리지 주문할인 안분 금액
                    $tmpDivisionDeliveryUseMileage = NumberUtils::getNumberFigure($divisionUseMileage * $deliveryDcRateWithDelivery, $truncPolicy['unitPrecision'], 'round');
                    $order['divisionDeliveryUseMileage'][$dKey] = $tmpDivisionDeliveryUseMileage;
                    $tmpDivisionGoodsUseMileageSum += $tmpDivisionDeliveryUseMileage;
                }

                // 복합과세 적용 가능한 실제 배송비 금액 (이미 실 배송비는 구해짐)
                $order['taxableDeliveryCharge'][$dKey] = $order['totalGoodsDeliveryPolicyCharge'][$dKey] + $order['totalGoodsDeliveryAreaCharge'][$dKey] - $tmpDivisionCouponDeliveryDcPrice - $tmpDivisionMemberDeliveryDcPrice - $tmpDivisionDeliveryUseDeposit - $tmpDivisionDeliveryUseMileage;

                // !중요! 배송비로 안분된 마일리지/예치금을 다시 상품쪽 예치금/마일리지로 환원하기 위한 루프 돌리기
                $tmpDivisionDeliveryUseDepositForGoodsSum = 0;
                $tmpDivisionDeliveryUseMileageForGoodsSum = 0;
                $tmpMinusDivisionDeliveryUseDeposit = $tmpDivisionDeliveryUseDeposit;
                $tmpMinusDivisionDeliveryUseMileage = $tmpDivisionDeliveryUseMileage;
                $divisionGoodsDeliveryKey = 0;
                foreach ($dVal as $gKey => $gVal) {
                    if (empty($order['totalGoodsDeliveryPolicyCharge'][$dKey] + $order['totalGoodsDeliveryAreaCharge'][$dKey]) === false) {
                        $divisionGoodsDeliveryKey = $gKey;
                    }
                    $originGoodsPrice = $gVal['price']['goodsPriceSubtotal'];
                    $deliveryForGoodsDcRate = $originGoodsPrice / $totalOriginGoodsPrice[$dKey];

                    // 배송비 사용예치금 주문할인 안분 금액
                    $tmpDivisionDeliveryUseDepositForGoods = NumberUtils::getNumberFigure($tmpDivisionDeliveryUseDeposit * $deliveryForGoodsDcRate, $truncPolicy['unitPrecision'], 'round');
                    // 0 원의 주문상품이 마이너스 부가결제 금액을 할당받는 것에 대한 방지
                    if($tmpMinusDivisionDeliveryUseDeposit >= $tmpDivisionDeliveryUseDepositForGoods){
                        $tmpMinusDivisionDeliveryUseDeposit -= $tmpDivisionDeliveryUseDepositForGoods;
                    }
                    else {
                        $tmpDivisionDeliveryUseDepositForGoods = $tmpMinusDivisionDeliveryUseDeposit;
                        $tmpMinusDivisionDeliveryUseDeposit = 0;
                    }
                    $cartInfo[$sKey][$dKey][$divisionGoodsDeliveryKey]['price']['divisionGoodsDeliveryUseDeposit'] = $tmpDivisionDeliveryUseDepositForGoods;
                    $tmpDivisionDeliveryUseDepositForGoodsSum += $tmpDivisionDeliveryUseDepositForGoods;

                    // 배송비 사용마일리지 주문할인 안분 금액
                    $tmpDivisionDeliveryUseMileageForGoods = NumberUtils::getNumberFigure($tmpDivisionDeliveryUseMileage * $deliveryForGoodsDcRate, $truncPolicy['unitPrecision'], 'round');
                    // 0 원의 주문상품이 마이너스 부가결제 금액을 할당받는 것에 대한 방지
                    if($tmpMinusDivisionDeliveryUseMileage >= $tmpDivisionDeliveryUseMileageForGoods){
                        $tmpMinusDivisionDeliveryUseMileage -= $tmpDivisionDeliveryUseMileageForGoods;
                    }
                    else {
                        $tmpDivisionDeliveryUseMileageForGoods = $tmpMinusDivisionDeliveryUseMileage;
                        $tmpMinusDivisionDeliveryUseMileage = 0;
                    }
                    $cartInfo[$sKey][$dKey][$divisionGoodsDeliveryKey]['price']['divisionGoodsDeliveryUseMileage'] = $tmpDivisionDeliveryUseMileageForGoods;
                    $tmpDivisionDeliveryUseMileageForGoodsSum += $tmpDivisionDeliveryUseMileageForGoods;
                }
                $cartInfo[$sKey][$dKey][$divisionGoodsDeliveryKey]['price']['divisionGoodsDeliveryUseDeposit'] += ($tmpDivisionDeliveryUseDeposit - $tmpDivisionDeliveryUseDepositForGoodsSum);
                $cartInfo[$sKey][$dKey][$divisionGoodsDeliveryKey]['price']['divisionGoodsDeliveryUseMileage'] += ($tmpDivisionDeliveryUseMileage - $tmpDivisionDeliveryUseMileageForGoodsSum);
            }
        }

        // 상품금액 비율로 처리 후 금액이 안맞는 부분 마지막 상품에 +/- 처리
        if ($tmpDivisionGoodsUseDepositSum != $divisionUseDeposit) {
            $cartInfo[$sKey][$dKey][$divisionGoodsDeliveryKey]['price']['divisionUseDeposit'] += ($divisionUseDeposit - $tmpDivisionGoodsUseDepositSum);
            $cartInfo[$sKey][$dKey][$divisionGoodsDeliveryKey]['price']['taxableGoodsPrice'] -= ($divisionUseDeposit - $tmpDivisionGoodsUseDepositSum);
        }
        if ($tmpDivisionGoodsUseMileageSum != $divisionUseMileage) {
            $cartInfo[$sKey][$dKey][$divisionGoodsDeliveryKey]['price']['divisionUseMileage'] += ($divisionUseMileage - $tmpDivisionGoodsUseMileageSum);
            $cartInfo[$sKey][$dKey][$divisionGoodsDeliveryKey]['price']['taxableGoodsPrice'] -= ($divisionUseMileage - $tmpDivisionGoodsUseMileageSum);
        }
        if ($tmpDivisionGoodsCouponOrderDcPriceSum != $divisionCouponOrderDcPrice) {
            $cartInfo[$sKey][$dKey][$divisionGoodsDeliveryKey]['price']['divisionCouponOrderDcPrice'] += ($divisionCouponOrderDcPrice - $tmpDivisionGoodsCouponOrderDcPriceSum);
            $cartInfo[$sKey][$dKey][$divisionGoodsDeliveryKey]['price']['taxableGoodsPrice'] -= ($divisionCouponOrderDcPrice - $tmpDivisionGoodsCouponOrderDcPriceSum);
        }
        if ($tmpDivisionGoodsCouponMileageSum != $divisionCouponOrderMileage) {
            $cartInfo[$sKey][$dKey][$divisionGoodsDeliveryKey]['price']['divisionCouponOrderMileage'] += ($divisionCouponOrderMileage - $tmpDivisionGoodsCouponMileageSum);
            //            $cartInfo[$sKey][$dKey][$gKey]['price']['taxableGoodsPrice'] -= ($divisionCouponOrderMileage - $tmpDivisionGoodsCouponMileageSum);
        }

        // 배송비조건 비율로 처리 후 금액이 안맞는 부분 마지막 배송비에 +/- 처리
        if ($tmpDivisionCouponDeliveryDcPriceSum != $divisionCouponDeliveryDcPrice) {
            $order['divisionDeliveryCharge'][$dKey] += ($divisionCouponDeliveryDcPrice - $tmpDivisionCouponDeliveryDcPriceSum);
            $order['taxableDeliveryCharge'][$dKey] += ($divisionCouponDeliveryDcPrice - $tmpDivisionCouponDeliveryDcPriceSum);
        }

        // 배송 콤포넌트 호출
        $delivery = App::load(\Component\Delivery\DeliveryCart::class);
        $delivery->setDeliveryMethodCompanySno();

        //공급사 수수료 컴포넌트 호출
        if(gd_use_provider() === true) {
            if(!is_object($scmCommission)) {
                $scmCommission = App::load(\Component\Scm\ScmCommission::class);
            }
        }

        // 해외배송 기본 정책
        $overseasDeliveryPolicy = null;
        $onlyOneOverseasDelivery = false;
        if (Globals::get('gGlobal.isFront')) {
            $overseasDelivery = new OverseasDelivery();
            $overseasDeliveryPolicy = $overseasDelivery->getBasicData(\Component\Mall\Mall::getSession('sno'), 'mallSno');
        }

        //상품 호출
        $goods = \App::load('\\Component\\Goods\\Goods');

        // 과세/면세 총 합을 위한 변수 초기화
        $taxSupplyPrice = 0;
        $taxVatPrice = 0;
        $taxFreePrice = 0;

        $orderCd = 1;

        // 복수배송지를 사용할 경우 해당 프로세스 실행 (주문배송 정보)
        $orderMultiDeliverySno = [];
        $tmpTotalCouponDeliveryDcPrice = $order['totalCouponDeliveryDcPrice'];
        $tmpDivisionUseDeposit = $order['useDeposit'];
        $tmpDivisionUseMileage = $order['useMileage'];
        if (\Component\Order\OrderMultiShipping::isUseMultiShipping() === true && \Globals::get('gGlobal.isFront') === false && $orderInfo['multiShippingFl'] == 'y') {
            foreach ($order['totalGoodsMultiDeliveryPolicyCharge'] as $key => $val) {
                foreach ($val as $tKey => $tVal) {
                    // 배송비 할인쿠폰 안분 작업
                    $tmpDivisionCouponDeliveryDcPrice = 0;
                    $tmpDivisionMemberDeliveryDcPrice = 0;
                    if ($order['totalDeliveryCharge'] > 0) {
                        $totalDeliveryCharge = $order['totalDeliveryCharge'];
                        // 전체배송비(배송비 할인쿠폰이 적용된 금액) 대비 비율 산정하며 소수점까지 표현한다.
                        $deliveryCharge = $order['totalGoodsMultiDeliveryAreaPrice'][$key][$tKey];
                        if ($order['totalMemberDeliveryDcPrice'] <= 0) {
                            $deliveryCharge += $order['totalGoodsMultiDeliveryPolicyCharge'][$key][$tKey];
                        } else {
                            $totalDeliveryCharge -= $order['totalMemberDeliveryDcPrice'];
                        }
                        $deliveryDcRate = ($deliveryCharge / ($totalDeliveryCharge));

                        // 배송비쿠폰 주문할인 안분 금액
                        $tmpDivisionCouponDeliveryDcPrice = NumberUtils::getNumberFigure($divisionCouponDeliveryDcPrice * $deliveryDcRate, $truncPolicy['unitPrecision'], 'round');
                        // 회원 배송비 무료 할인 금액
                        if($order['totalMemberDeliveryDcPrice'] > 0){
                            // 회원 배송비 무료 할인은 정책 배송비 금액에만 적용됨.
                            $tmpDivisionMemberDeliveryDcPrice = $order['totalGoodsMultiDeliveryPolicyCharge'][$key][$tKey];
                        }
                    }
                    if ($tmpTotalCouponDeliveryDcPrice - $tmpDivisionCouponDeliveryDcPrice > 0) {
                        $tmpTotalCouponDeliveryDcPrice -= $tmpDivisionCouponDeliveryDcPrice;
                    } else if ($tmpTotalCouponDeliveryDcPrice - $tmpDivisionCouponDeliveryDcPrice < 0) {
                        $tmpDivisionCouponDeliveryDcPrice = $tmpTotalCouponDeliveryDcPrice;
                        $tmpTotalCouponDeliveryDcPrice = 0;
                    }

                    // 배송비 예치금/마일리지 안분 작업
                    $tmpDivisionDeliveryUseDeposit = 0;
                    $tmpDivisionDeliveryUseMileage = 0;
                    if ($order['settleTotalDeliveryCharge'] > 0) {
                        // 배송비 - 배송비 할인쿠폰이 적용된 금액을 기준으로 남은 실결제금액을 안분처리 한다.
                        $deliveryDcRateWithDelivery = (($order['totalGoodsMultiDeliveryPolicyCharge'][$key][$tKey] + $order['totalGoodsMultiDeliveryAreaPrice'][$key][$tKey] - $tmpDivisionCouponDeliveryDcPrice - $tmpDivisionMemberDeliveryDcPrice) / $order['settleTotalGoodsPriceWithDelivery']);

                        // 배송비 사용예치금 주문할인 안분 금액
                        $tmpDivisionDeliveryUseDeposit = NumberUtils::getNumberFigure($divisionUseDeposit * $deliveryDcRateWithDelivery, $truncPolicy['unitPrecision'], 'round');

                        // 배송비 사용마일리지 주문할인 안분 금액
                        $tmpDivisionDeliveryUseMileage = NumberUtils::getNumberFigure($divisionUseMileage * $deliveryDcRateWithDelivery, $truncPolicy['unitPrecision'], 'round');
                    }
                    if ($tmpDivisionUseDeposit - $tmpDivisionDeliveryUseDeposit > 0) {
                        $tmpDivisionUseDeposit -= $tmpDivisionDeliveryUseDeposit;
                    } else if ($tmpDivisionUseDeposit - $tmpDivisionDeliveryUseDeposit < 0) {
                        $tmpDivisionDeliveryUseDeposit = $tmpDivisionUseDeposit;
                        $tmpDivisionUseDeposit = 0;
                    }
                    if ($tmpDivisionUseMileage - $tmpDivisionDeliveryUseMileage > 0) {
                        $tmpDivisionUseMileage -= $tmpDivisionDeliveryUseMileage;
                    } else if ($tmpDivisionUseMileage - $tmpDivisionDeliveryUseMileage < 0) {
                        $tmpDivisionDeliveryUseMileage = $tmpDivisionUseMileage;
                        $tmpDivisionUseMileage = 0;
                    }

                    $deliveryPolicy = $delivery->getDataDeliveryWithGoodsNo([$tKey]);
                    $scmNo = $deliveryPolicy[$tKey]['scmNo'];
                    $goodsData = $cartInfo[$scmNo][$tKey][0];

                    // 공급사 수수료 일정 Convert 실행
                    if(gd_use_provider() === true) {
                        if($scmNo > DEFAULT_CODE_SCMNO) {
                            $scmCommissionConvertData = $scmCommission->frontConvertScmCommission($scmNo, $goodsData);
                        }
                    }

                    // 배송정책내 부가세율 관련 정보 설정
                    $deliveryTaxFreeFl = $goodsData['goodsDeliveryTaxFreeFl'];
                    $deliveryTaxPercent = $goodsData['goodsDeliveryTaxPercent'];
                    $taxableDeliveryCharge = $order['totalGoodsMultiDeliveryPolicyCharge'][$key][$tKey] + $order['totalGoodsMultiDeliveryAreaPrice'][$key][$tKey];
                    $taxableDeliveryCharge -= $tmpDivisionCouponDeliveryDcPrice + $tmpDivisionMemberDeliveryDcPrice + $tmpDivisionDeliveryUseDeposit + $tmpDivisionDeliveryUseMileage;

                    // 상단에서 계산된 금액으로 배송비 복합과세 처리
                    $tmpDeliveryTaxPrice = NumberUtils::taxAll($taxableDeliveryCharge, $deliveryTaxPercent, $deliveryTaxFreeFl);

                    // 초기화
                    $taxDeliveryCharge['supply'] = 0;
                    $taxDeliveryCharge['tax'] = 0;
                    $taxDeliveryCharge['free'] = 0;
                    if ($deliveryTaxFreeFl == 't') {
                        // 배송비 과세처리
                        $taxDeliveryCharge['supply'] = $tmpDeliveryTaxPrice['supply'];
                        $taxDeliveryCharge['tax'] = $tmpDeliveryTaxPrice['tax'];
                    } else {
                        // 배송비 면세처리
                        $taxDeliveryCharge['free'] = $tmpDeliveryTaxPrice['supply'];
                    }

                    $deliveryInfo = [
                        'orderNo'                     => $this->orderNo,
                        'scmNo'                       => $scmNo,
                        'commission'                  => ($scmCommissionConvertData['scmCommissionDelivery']) ? $scmCommissionConvertData['scmCommissionDelivery'] : $deliveryPolicy[$tKey]['scmCommissionDelivery'],
                        'deliverySno'                 => $tKey,
                        'deliveryCharge'              => $order['totalGoodsMultiDeliveryPolicyCharge'][$key][$tKey] + $order['totalGoodsMultiDeliveryAreaPrice'][$key][$tKey],
                        'taxSupplyDeliveryCharge'     => $taxDeliveryCharge['supply'],
                        'taxVatDeliveryCharge'        => $taxDeliveryCharge['tax'],
                        'taxFreeDeliveryCharge'       => $taxDeliveryCharge['free'],
                        'realTaxSupplyDeliveryCharge' => $taxDeliveryCharge['supply'],
                        'realTaxVatDeliveryCharge'    => $taxDeliveryCharge['tax'],
                        'realTaxFreeDeliveryCharge'   => $taxDeliveryCharge['free'],
                        'deliveryPolicyCharge'        => $order['totalGoodsMultiDeliveryPolicyCharge'][$key][$tKey],
                        'deliveryAreaCharge'          => $order['totalGoodsMultiDeliveryAreaPrice'][$key][$tKey],
                        'deliveryFixFl'               => $goodsData['goodsDeliveryFixFl'],
                        'divisionDeliveryUseDeposit'  => $tmpDivisionDeliveryUseDeposit,
                        'divisionDeliveryUseMileage'  => $tmpDivisionDeliveryUseMileage,
                        'divisionDeliveryCharge'      => $tmpDivisionCouponDeliveryDcPrice,
                        'divisionMemberDeliveryDcPrice' => $tmpDivisionMemberDeliveryDcPrice,
                        'deliveryInsuranceFee'        => $order['totalDeliveryInsuranceFee'],
                        'goodsDeliveryFl'             => $goodsData['goodsDeliveryFl'],
                        'deliveryTaxInfo'             => $deliveryTaxFreeFl . STR_DIVISION . $deliveryTaxPercent,
                        'deliveryWeightInfo'          => $order['totalDeliveryWeight'],
                        'deliveryPolicy'              => json_encode($deliveryPolicy[$tKey], JSON_UNESCAPED_UNICODE),
                        'overseasDeliveryPolicy'      => json_encode($overseasDeliveryPolicy, JSON_UNESCAPED_UNICODE),
                        'deliveryCollectFl'           => $goodsData['goodsDeliveryCollectFl'],
                        'deliveryCollectPrice'        => $order['totalDeliveryCollectPrice'][$tKey],
                        // 배송비조건별인 경우만 금액을 넣는다.
                        'deliveryMethod'              => $goodsData['goodsDeliveryMethod'],
                        'deliveryWholeFreeFl'         => $goodsData['goodsDeliveryWholeFreeFl'],
                        'deliveryWholeFreePrice'      => ($goodsData['price']['goodsDeliveryWholeFreePrice'] > 0 ?: $order['totalDeliveryWholeFreePrice'][$tKey]),
                        // 배송비 조건별/상품별에 따라서 금액을 받아온다.
                        'deliveryLog'                 => '',
                    ];

                    // 정책별 배송 정보 저장
                    $arrBind = $this->db->get_binding(DBTableField::tableOrderDelivery(), $deliveryInfo, 'insert');
                    $this->db->set_insert_db(DB_ORDER_DELIVERY, $arrBind['param'], $arrBind['bind'], 'y', false);
                    $orderMultiDeliverySno[$key][$tKey] = $this->db->insert_id();
                    unset($arrBind);
                }
            }
        }

        if (\Cookie::has('inflow_goods') === true) {
            $inflowGoods = json_decode(\Cookie::get('inflow_goods'), true);
            $inflowDiffGoods = [];
        }
        if ($this->useMyapp && Request::isMyapp()) {
            $myapp = \App::load('Component\\Myapp\\Myapp');
            $myappConfig = gd_policy('myapp.config');
        }
        foreach ($cartInfo as $sKey => $sVal) {
            foreach ($sVal as $dKey => $dVal) {
                $onlyOneDelivery = true;
                $deliveryPolicy = $delivery->getDataDeliveryWithGoodsNo([$dKey]);
                $deliveryMethodFl = '';
                foreach ($dVal as $gKey => $gVal) {
                    if (empty($gVal['goodsNo']) === true) continue;
                    // 공급사 수수료 일정 Convert 실행
                    if(gd_use_provider() === true) {
                        if($sKey > DEFAULT_CODE_SCMNO) {
                            $scmCommissionConvertData = $scmCommission->frontConvertScmCommission($sKey, $gVal);
                            if($scmCommissionConvertData['scmCommission']) {
                                $gVal['commission'] = $scmCommissionConvertData['scmCommission'];
                            }
                        }
                    }
                    $gVal['orderNo'] = $this->orderNo;
                    $gVal['mallSno'] = gd_isset(Mall::getSession('sno'), 1);
                    $gVal['orderCd'] = $orderCd;
                    $gVal['goodsNm'] = $gVal['goodsNm'];
                    $gVal['goodsNmStandard'] = $gVal['goodsNmStandard'];
                    $gVal['orderStatus'] = $orderStatusPre;
                    $gVal['deliveryMethodFl'] = empty($gVal['deliveryMethodFl']) === true ? 'delivery' : $gVal['deliveryMethodFl']; //배송방식
                    $gVal['goodsDeliveryCollectFl'] = $gVal['goodsDeliveryCollectFl'];
                    $gVal['cateAllCd'] = json_encode($gVal['cateAllCd'], JSON_UNESCAPED_UNICODE);
                    // 상품별 배송비조건인 경우 선불/착불 금액 기록 (배송비조건별인 경우 orderDelivery에 저장)
                    // orderDelivery에 각 상품별 선/착불 데이터를 저장하기 애매해서 이와 같이 처리 함
                    if ($gVal['goodsDeliveryFl'] === 'n') {
                        $gVal['goodsDeliveryCollectPrice'] = $gVal['goodsDeliveryCollectFl'] == 'pre' ? $gVal['price']['goodsDeliveryPrice'] : $gVal['price']['goodsDeliveryCollectPrice'];
                    }

                    //조건별 배송비 일때
                    if($deliveryPolicy[$dKey]['goodsDeliveryFl'] === 'y'){
                        // 복수배송지 사용안할 경우
                        if ($orderInfo['multiShippingFl'] != 'y') {
                            //조건별 배송비 사용 일 경우 배송방식을 모두 변환한다.
                            if (trim($deliveryMethodFl) === '') {
                                $deliveryMethodFl = empty($gVal['deliveryMethodFl']) === true ? 'delivery' : $gVal['deliveryMethodFl']; //배송방식
                            }
                            $gVal['deliveryMethodFl'] = $deliveryMethodFl;
                        }
                    }
                    else {
                        $deliveryMethodFl = '';
                    }

                    if($gVal['deliveryMethodFl'] && $gVal['deliveryMethodFl'] !== 'delivery'){
                        $gVal['invoiceCompanySno'] = $delivery->deliveryMethodList['sno'][$gVal['deliveryMethodFl']];
                    }

                    $gVal['goodsPrice'] = $gVal['price']['goodsPrice'];
                    $gVal['addGoodsCnt'] = count(gd_isset($gVal['addGoods']));
                    // 기존 추가상품의 계산로직 레거시 보장을 위해 0으로 변경 처리
                    $gVal['addGoodsPrice'] = 0;
                    $gVal['optionPrice'] = $gVal['price']['optionPrice'];
                    $gVal['optionCostPrice'] = $gVal['price']['optionCostPrice'];
                    $gVal['optionTextPrice'] = $gVal['price']['optionTextPrice'];
                    $gVal['fixedPrice'] = $gVal['price']['fixedPrice'];
                    $gVal['costPrice'] = $gVal['price']['costPrice'];
                    $gVal['goodsDcPrice'] = $gVal['price']['goodsDcPrice'];
                    // 쿠폰 정책에 따른 쿠폰만사용설정시 회원혜택 제거
                    if ($setMemberDcMileageZero == 'T') {
                        $gVal['memberDcPrice'] = 0;
                        $gVal['memberMileage'] = 0;
                    } else {
                        $gVal['memberDcPrice'] = $gVal['price']['goodsMemberDcPrice'];
                        $gVal['memberMileage'] = $gVal['mileage']['memberMileage'];
                    }
                    $gVal['memberOverlapDcPrice'] = $gVal['price']['goodsMemberOverlapDcPrice'];
                    $gVal['couponGoodsDcPrice'] = $gVal['price']['goodsCouponGoodsDcPrice'];

                    // 마이앱 사용에 따른 분기 처리 (주문서 작성시 새로 계산)
                    if ($this->useMyapp && Request::isMyapp()) {
                        if ($myappConfig['benefit']['orderAdditionalBenefit']['isUsing'] == true) {
                            $myappBenefitParams = [];
                            if($gVal['goodsType'] == 'addGoods') {
                                $myappBenefitParams['goodsPrice'] = 0;
                                $myappBenefitParams['addGoodsPrice'] = $gVal['price']['goodsPrice'];
                                $myappBenefitParams['addGoodsCnt'] = $gVal['goodsCnt'];
                            } else {
                                $myappBenefitParams['goodsCnt'] = $gVal['goodsCnt'];
                                $myappBenefitParams['goodsPrice'] = $gVal['price']['goodsPrice'];
                                $myappBenefitParams['optionPrice'] = $gVal['price']['optionPrice'];
                                $myappBenefitParams['optionTextPrice'] = $gVal['price']['optionTextPrice'];
                            }
                            $myappBenefit = $myapp->getOrderAdditionalBenefit($myappBenefitParams);
                            $gVal['myappDcPrice'] = $myappBenefit['discount']['goods'];
                        }
                    }

                    $gVal['goodsMileage'] = $gVal['mileage']['goodsMileage'];
                    $gVal['couponGoodsMileage'] = $gVal['mileage']['couponGoodsMileage'];
                    $gVal['goodsTaxInfo'] = $gVal['taxFreeFl'] . STR_DIVISION . $gVal['taxPercent'];// 상품 세금 정보
                    $gVal['divisionUseDeposit'] = $gVal['price']['divisionUseDeposit'];
                    $gVal['divisionUseMileage'] = $gVal['price']['divisionUseMileage'];
                    $gVal['divisionGoodsDeliveryUseDeposit'] = $gVal['price']['divisionGoodsDeliveryUseDeposit'];
                    $gVal['divisionGoodsDeliveryUseMileage'] = $gVal['price']['divisionGoodsDeliveryUseMileage'];
                    $gVal['divisionCouponOrderDcPrice'] = $gVal['price']['divisionCouponOrderDcPrice'];
                    $gVal['divisionCouponOrderMileage'] = $gVal['price']['divisionCouponOrderMileage'];
                    if($gVal['hscode']) $gVal['hscode'] = $gVal['hscode'];
                    if($gVal['timeSaleFl']) $gVal['timeSaleFl'] = 'y';
                    else $gVal['timeSaleFl'] = 'n';

                    // 배송비 테이블 데이터 설정으로 foreach구문에서 최초 한번만 실행된다.
                    if ($onlyOneDelivery === true) {
                        // 배송정책내 부가세율 관련 정보 설정
                        $deliveryTaxFreeFl = $gVal['goodsDeliveryTaxFreeFl'];
                        $deliveryTaxPercent = $gVal['goodsDeliveryTaxPercent'];

                        // 상단에서 계산된 금액으로 배송비 복합과세 처리
                        $tmpDeliveryTaxPrice = NumberUtils::taxAll($order['taxableDeliveryCharge'][$dKey], $deliveryTaxPercent, $deliveryTaxFreeFl);

                        // 초기화
                        $taxDeliveryCharge['supply'] = 0;
                        $taxDeliveryCharge['tax'] = 0;
                        $taxDeliveryCharge['free'] = 0;
                        if ($deliveryTaxFreeFl == 't') {
                            // 배송비 과세처리
                            $taxDeliveryCharge['supply'] = $tmpDeliveryTaxPrice['supply'];
                            $taxDeliveryCharge['tax'] = $tmpDeliveryTaxPrice['tax'];

                            // 주문의 총 과세에 합산
                            $taxSupplyPrice += $tmpDeliveryTaxPrice['supply'];
                            $taxVatPrice += $tmpDeliveryTaxPrice['tax'];
                        } else {
                            // 배송비 면세처리
                            $taxDeliveryCharge['free'] = $tmpDeliveryTaxPrice['supply'];

                            // 주문의 총 면세에 합산
                            $taxFreePrice += $tmpDeliveryTaxPrice['supply'];
                        }

                        // 복수배송지를 사용할 경우 해당 프로세스 실행하지 않음
                        if (\Component\Order\OrderMultiShipping::isUseMultiShipping() === true && \Globals::get('gGlobal.isFront') === false && $orderInfo['multiShippingFl'] == 'y') {}
                        else {
                            // 공급사 수수료 일정 Convert 실행
                            if(gd_use_provider() === true) {
                                if($sKey > DEFAULT_CODE_SCMNO) {
                                    $scmCommissionConvertData = $scmCommission->frontConvertScmCommission($sKey, $gVal);
                                }
                            }
                            $deliveryInfo = [
                                'orderNo'                     => $this->orderNo,
                                'scmNo'                       => $sKey,
                                'commission'                  => ($scmCommissionConvertData['scmCommissionDelivery']) ? $scmCommissionConvertData['scmCommissionDelivery'] : $deliveryPolicy[$dKey]['scmCommissionDelivery'],
                                'deliverySno'                 => $dKey,
                                'deliveryCharge'              => $order['totalGoodsDeliveryPolicyCharge'][$dKey] + $order['totalGoodsDeliveryAreaCharge'][$dKey],
                                'taxSupplyDeliveryCharge'     => $taxDeliveryCharge['supply'],
                                'taxVatDeliveryCharge'        => $taxDeliveryCharge['tax'],
                                'taxFreeDeliveryCharge'       => $taxDeliveryCharge['free'],
                                'realTaxSupplyDeliveryCharge' => $taxDeliveryCharge['supply'],
                                'realTaxVatDeliveryCharge'    => $taxDeliveryCharge['tax'],
                                'realTaxFreeDeliveryCharge'   => $taxDeliveryCharge['free'],
                                'deliveryPolicyCharge'        => $order['totalGoodsDeliveryPolicyCharge'][$dKey],
                                'deliveryAreaCharge'          => $order['totalGoodsDeliveryAreaCharge'][$dKey],
                                'deliveryFixFl'               => $gVal['goodsDeliveryFixFl'],
                                'divisionDeliveryUseDeposit'  => $order['divisionDeliveryUseDeposit'][$dKey],
                                'divisionDeliveryUseMileage'  => $order['divisionDeliveryUseMileage'][$dKey],
                                'divisionDeliveryCharge'      => $order['divisionDeliveryCharge'][$dKey],
                                'divisionMemberDeliveryDcPrice' => $order['divisionMemberDeliveryDcPrice'][$dKey],
                                'deliveryInsuranceFee'        => $order['totalDeliveryInsuranceFee'],
                                'goodsDeliveryFl'             => $gVal['goodsDeliveryFl'],
                                'deliveryTaxInfo'             => $deliveryTaxFreeFl . STR_DIVISION . $deliveryTaxPercent,
                                'deliveryWeightInfo'          => $order['totalDeliveryWeight'],
                                'deliveryPolicy'              => json_encode($deliveryPolicy[$dKey], JSON_UNESCAPED_UNICODE),
                                'overseasDeliveryPolicy'      => json_encode($overseasDeliveryPolicy, JSON_UNESCAPED_UNICODE),
                                'deliveryCollectFl'           => $gVal['goodsDeliveryCollectFl'],
                                'deliveryCollectPrice'        => $order['totalDeliveryCollectPrice'][$dKey],
                                // 배송비조건별인 경우만 금액을 넣는다.
                                'deliveryMethod'              => $gVal['goodsDeliveryMethod'],
                                'deliveryWholeFreeFl'         => $gVal['goodsDeliveryWholeFreeFl'],
                                'deliveryWholeFreePrice'      => ($gVal['price']['goodsDeliveryWholeFreePrice'] > 0 ?: $order['totalDeliveryWholeFreePrice'][$dKey]),
                                // 배송비 조건별/상품별에 따라서 금액을 받아온다.
                                'deliveryLog'                 => '',
                            ];

                            // !중요!
                            // 해외배송은 설정에 따라서 무조건 하나의 배송비조건만 가지고 계산된다.
                            // 따라서 공급사의 경우 기본적으로 공급사마다 별도의 배송비조건을 가지게 되기때문에 아래와 같이
                            // 본사/공급사 구분없이 최초 배송비조건만 할당하고 나머지 배송비는 0원으로 처리해 이를 처리한다.
                            if (Globals::get('gGlobal.isFront') && $onlyOneOverseasDelivery === true) {
                                $deliveryInfo['deliveryCharge'] = 0;
                                $deliveryInfo['taxSupplyDeliveryCharge'] = 0;
                                $deliveryInfo['taxVatDeliveryCharge'] = 0;
                                $deliveryInfo['taxFreeDeliveryCharge'] = 0;
                                $deliveryInfo['realTaxSupplyDeliveryCharge'] = 0;
                                $deliveryInfo['realTaxVatDeliveryCharge'] = 0;
                                $deliveryInfo['realTaxFreeDeliveryCharge'] = 0;
                                $deliveryInfo['deliveryPolicyCharge'] = 0;
                                $deliveryInfo['deliveryAreaCharge'] = 0;
                                $deliveryInfo['divisionDeliveryUseDeposit'] = 0;
                                $deliveryInfo['divisionDeliveryUseMileage'] = 0;
                                $deliveryInfo['divisionDeliveryCharge'] = 0;
                                $deliveryInfo['deliveryInsuranceFee'] = 0;
                                $deliveryInfo['deliveryCollectPrice'] = 0;
                                $deliveryInfo['deliveryWholeFreePrice'] = 0;
                            }

                            // 정책별 배송 정보 저장
                            $arrBind = $this->db->get_binding(DBTableField::tableOrderDelivery(), $deliveryInfo, 'insert');
                            $this->db->set_insert_db(DB_ORDER_DELIVERY, $arrBind['param'], $arrBind['bind'], 'y', false);
                            $orderDeliverySno = $this->db->insert_id();
                            unset($arrBind);
                        }

                        // 한번만 실행
                        $onlyOneDelivery = false;
                        $onlyOneOverseasDelivery = true;
                    }

                    if (empty($orderDeliverySno) === false) {
                        $gVal['orderDeliverySno'] = $orderDeliverySno;
                    } else {
                        $gVal['orderDeliverySno'] = $orderMultiDeliverySno[$orderInfo['orderInfoCdBySno'][$gVal['sno']]][$dKey];
                    }

                    // 옵션 설정
                    if (empty($gVal['option']) === true) {
                        $gVal['optionInfo'] = '';
                    } else {
                        foreach ($gVal['option'] as $oKey => $oVal) {
                            $tmp[] = [
                                $oVal['optionName'],
                                $oVal['optionValue'],
                                $oVal['optionCode'],
                                floatval($oVal['optionPrice']),
                                $oVal['optionDeliveryStr'],
                            ];
                        }
                        $gVal['optionInfo'] = json_encode($tmp, JSON_UNESCAPED_UNICODE);
                        unset($tmp);
                    }

                    // 텍스트 옵션
                    if (empty($gVal['optionText']) === true) {
                        $gVal['optionTextInfo'] = '';
                    } else {
                        foreach ($gVal['optionText'] as $oKey => $oVal) {
                            $tmp[$oVal['optionSno']] = [
                                $oVal['optionName'],
                                $oVal['optionValue'],
                                floatval($oVal['optionTextPrice']),
                            ];
                        }
                        $gVal['optionTextInfo'] = json_encode($tmp, JSON_UNESCAPED_UNICODE);
                        unset($tmp);
                    }

                    // 상품할인정보
                    if (empty($gVal['goodsDiscountInfo']) === true) {
                        $gVal['goodsDiscountInfo'] = '';
                    } else {
                        $gVal['goodsDiscountInfo'] = json_encode($gVal['goodsDiscountInfo'], JSON_UNESCAPED_UNICODE);
                    }
                    // 상품적립정보
                    if (empty($gVal['goodsMileageAddInfo']) === true) {
                        $gVal['goodsMileageAddInfo'] = '';
                    } else {
                        $gVal['goodsMileageAddInfo'] = json_encode($gVal['goodsMileageAddInfo'], JSON_UNESCAPED_UNICODE);
                    }

                    // 상품의 복합과세 금액 산출 및 주문상품에 저장할 필드 설정
                    $tmpGoodsTaxPrice = NumberUtils::taxAll($gVal['price']['taxableGoodsPrice'], $gVal['taxPercent'], $gVal['taxFreeFl']);
                    if ($gVal['taxFreeFl'] == 't') {
                        $gVal['taxSupplyGoodsPrice'] = $gVal['realTaxSupplyGoodsPrice'] = gd_isset($tmpGoodsTaxPrice['supply'], 0);
                        $gVal['taxVatGoodsPrice'] = $gVal['realTaxVatGoodsPrice'] = gd_isset($tmpGoodsTaxPrice['tax'], 0);
                        $taxSupplyPrice += $gVal['taxSupplyGoodsPrice'];
                        $taxVatPrice += $gVal['taxVatGoodsPrice'];
                    } else {
                        $gVal['taxFreeGoodsPrice'] = $gVal['realTaxFreeGoodsPrice'] = gd_isset($tmpGoodsTaxPrice['supply'], 0);
                        $taxFreePrice += $gVal['taxFreeGoodsPrice'];
                    }

                    // 상품 쿠폰 정보 (하단 별도의 테이블에 담는 정보)
                    if ($gVal['couponGoodsDcPrice'] > 0 || $gVal['couponGoodsMileage'] > 0) {
                        foreach ($gVal['coupon'] as $memberCouponNo => $couponVal) {
                            $goodsCouponInfo[] = [
                                'orderNo'        => $this->orderNo,
                                'orderCd'        => $orderCd,
                                'goodsNo'        => $gVal['goodsNo'],
                                'memberCouponNo' => $memberCouponNo,
                                'expireSdt'      => $couponVal['memberCouponStartDate'],
                                'expireEdt'      => $couponVal['memberCouponEndDate'],
                                'couponNm'       => $couponVal['couponNm'],
                                'couponPrice'    => $couponVal['couponGoodsDcPrice'],
                                'couponMileage'  => $couponVal['couponGoodsMileage'],
                            ];
                        }
                    }

                    if (empty($inflowGoods) === false && in_array($gVal['goodsNo'], $inflowGoods) === true) {
                        $gVal['inflow'] = \Cookie::get('inflow');
                        $inflowDiffGoods[] = $gVal['goodsNo']; // 상품 기준(옵션 달라도 하나로 묶음)
                    }

                    // 주문 상품명
                    if ($orderCd == 1) {
                        $orderGoodsNm = $gVal['goodsNm'];
                        $orderGoodsNmStandard =  $gVal['goodsNmStandard'];
                    }

                    $gVal['visitAddress'] = $delivery->getVisitAddress($dKey, true);

                    // PG 처리를 위한 상품 정보
                    $this->arrGoodsName[] = gd_htmlspecialchars($gVal['goodsNm']);
                    $this->arrGoodsNo[] = $gVal['goodsNo'];
                    $this->arrGoodsAmt[] = $gVal['goodsPrice'] + $gVal['optionPrice'] + $gVal['optionTextPrice'];
                    $this->arrGoodsCnt[] = $gVal['goodsCnt'];

                    // 장바구니 상품 정보 저장
                    $arrBind = $this->db->get_binding(DBTableField::tableOrderGoods(), $gVal, 'insert');
                    $this->db->set_insert_db(DB_ORDER_GOODS, $arrBind['param'], $arrBind['bind'], 'y', false);

                    // 저장된 주문상품(order_goods) SNO 값
                    $arrOrderGoodsSno['sno'][] = $this->db->insert_id();

                    $orderCd++;
                    unset($arrBind);

                    //주문시 결제완료 상태인경우에 주문카운트 수정
                    if ($gVal['orderStatus']  =='p1') {
                        $goods->setOrderCount($this->db->insert_id());
                        // es_goods.orderGoodsCnt 갱신
                        $goods->setOrderGoodsCount($this->db->insert_id());
                    }

                    // 주문 로그 저장
                    $this->orderLog($gVal['orderNo'], $this->db->insert_id(), null, $this->getOrderStatusAdmin($gVal['orderStatus']) . '(' . $gVal['orderStatus'] . ')', '초기주문', true);
                }
            }
        }
        $arrOrderGoodsSno['GoodsSno'] = $arrOrderGoodsSno['sno'];

        if (\Cookie::has('inflow_goods') === true) {
            $inflowGoods = array_diff($inflowGoods, $inflowDiffGoods); // 상품 기준(옵션 달라도 하나로 묶음)
            $inflowGoods = json_encode($inflowGoods);
            \Cookie::set('inflow_goods', $inflowGoods);
        }

        // 주문서 저장용 데이터 설정
        $order['orderChannelFl'] = gd_isset($orderInfo['orderChannelFl'], 'shop');
        $order['totalMinusMileage'] = $orderInfo['useMileage'];
        $order['orderNo'] = $this->orderNo;
        $order['orderStatus'] = $orderStatusPre;
        $order['orderIp'] = Request::getRemoteAddress();
        $order['orderEmail'] = is_array($orderInfo['orderEmail']) ? implode('@', $orderInfo['orderEmail']) : $orderInfo['orderEmail'];
        $order['settleKind'] = $orderInfo['settleKind'];
        $order['receiptFl'] = gd_isset($orderInfo['receiptFl'], 'n');
        $order['orderGoodsNm'] = $this->orderGoodsName = $orderGoodsNm . ($orderGoodsCnt > 1 ? __(' 외 ') . ($orderGoodsCnt - 1) . __(' 건') : '');

        // 한글 저장하는 것으로 번역 처리 하면 안됩니다.
        $order['orderGoodsNmStandard'] = $orderGoodsNmStandard . ($orderGoodsCnt > 1 ? ' 외 ' . ($orderGoodsCnt - 1) . ' 건' : '');

        $order['orderGoodsCnt'] = $orderGoodsCnt;
        $order['bankSender'] = gd_isset($orderInfo['bankSender']);

        // 멀티상점 정보 추가 (없으면 1)
        $order['mallSno'] = gd_isset(Mall::getSession('sno'), 1);

        // 해외배송을 위한 배송 총 무게
        $order['totalDeliveryWeight'] = $order['totalDeliveryWeight']['total'];

        // 최종 상품 + 배송비 결제금액에 대한 복합과세 금액
        $order['taxSupplyPrice'] = $order['realTaxSupplyPrice'] = $taxSupplyPrice;
        $order['taxVatPrice'] = $order['realTaxVatPrice'] = $taxVatPrice;
        $order['taxFreePrice'] = $order['realTaxFreePrice'] = $taxFreePrice;

        // 회원 콤포넌트 호출
        $member = \App::load('\\Component\\Member\\Member');

        // 주문완료 시점의 정책 저장
        $order['depositPolicy'] = json_encode(gd_policy('member.depositConfig'), JSON_UNESCAPED_UNICODE);//예치금정책
        $order['mileagePolicy'] = json_encode(gd_mileage_give_info(), JSON_UNESCAPED_UNICODE);//마일리지정책
        $order['statusPolicy'] = json_encode($this->getOrderStatusPolicy(), JSON_UNESCAPED_UNICODE);//주문상태 정책
        if($isWrite === true){
            //수기주문일 경우
            if((int)$orderInfo['memNo'] > 0){
                $order['memberPolicy'] = json_encode($member->getMemberInfo($orderInfo['memNo']), JSON_UNESCAPED_UNICODE);
            }
        }
        else {
            $order['memberPolicy'] = json_encode($member->getMemberInfo(), JSON_UNESCAPED_UNICODE);
        }
        $order['couponPolicy'] = json_encode($couponPolicy, JSON_UNESCAPED_UNICODE);

        // 환율 정책 저장
        if (Globals::get('gGlobal.isFront')) {
            $exchangeRate = new ExchangeRate();
            $order['currencyPolicy'] = json_encode(reset($exchangeRate->getGlobalCurrency(\Component\Mall\Mall::getSession('currencyConfig.isoCode'), 'isoCode')), JSON_UNESCAPED_UNICODE);
            $order['exchangeRatePolicy'] = json_encode($exchangeRate->getExchangeRate(), JSON_UNESCAPED_UNICODE);
        }

        // 주문완료 시점의 마이앱 추가 혜택 정책 저장
        if ($this->useMyapp) {
            $myappConfig = gd_policy('myapp.config');
            $order['myappPolicy'] = json_encode($myappConfig['benefit']['orderAdditionalBenefit'], JSON_UNESCAPED_UNICODE);
        }

        // 무통장입금 데이터 재포맷
        if ($orderInfo['settleKind'] == 'gb') {
            $bankInfo = $this->getBankInfo($orderInfo['bankAccount']);
            $order['bankAccount'] = $bankInfo['bankName'] . STR_DIVISION . $bankInfo['accountNumber'] . STR_DIVISION . $bankInfo['depositor'];
        } else {
            $order['bankAccount'] = '';
        }

        // orderInfo 전화번호/휴대폰번호 재설정
        $orderInfo['orderPhone'] = StringUtils::numberToPhone(str_replace('-', '', $orderInfo['orderPhone']));
        $orderInfo['orderCellPhone'] = StringUtils::numberToPhone(str_replace('-', '', $orderInfo['orderCellPhone']));
        $orderInfo['receiverPhone'] = StringUtils::numberToPhone(str_replace('-', '', $orderInfo['receiverPhone']));
        $orderInfo['receiverCellPhone'] = StringUtils::numberToPhone(str_replace('-', '', $orderInfo['receiverCellPhone']));

        // 해외배송 국가코드를 텍스트로 전환
        $orderInfo['receiverCountry'] = $this->getCountryName($orderInfo['receiverCountryCode']);

        // 해외전화번호 숫자 변환해서 해당 필드 추가 처리
        $orderInfo['orderPhonePrefix'] = $this->getCountryCallPrefix($orderInfo['orderPhonePrefixCode']);
        $orderInfo['orderCellPhonePrefix'] = $this->getCountryCallPrefix($orderInfo['orderCellPhonePrefixCode']);
        $orderInfo['receiverPhonePrefix'] = $this->getCountryCallPrefix($orderInfo['receiverPhonePrefixCode']);
        $orderInfo['receiverCellPhonePrefix'] = $this->getCountryCallPrefix($orderInfo['receiverCellPhonePrefixCode']);

        // orderInfo 이메일
        $orderInfo['orderEmail'] = $order['orderEmail'];

        // 일반주문인 경우
        if ($isWrite === false) {
            // guest세션과 member세션이 없는 경우 비정상 주문건으로 튕겨 #dooray-776
            if ($orderInfo['orderChannelFl'] === 'shop') {
                if (!Session::has('guest') && !Session::has('member.memNo')) {
                    throw new AlertRedirectException(__('주문이 존재하지 않습니다.'), null, null, '../main/index.php', 'top');
                }
            }

            // 회원: 최근주문일자 반영 및 회원정보 반영
            // 비회원: 주문로그인
            if (MemberUtil::checkLogin() == 'member') {
                if (empty($order['memNo']) == true) {
                    throw new AlertRedirectException(__('잘못된 접근입니다. 다시 시도해주세요.'), null, null, '../order/cart.php', 'top');
                }
                if ($orderInfo['simpleJoin'] === 'y') {
                    $memData = [
                        'memNo'      => $order['memNo'],
                        'memNm'    => $orderInfo['orderName'],
                        'phone'   => $orderInfo['orderPhone'],
                        'cellPhone'    => $orderInfo['orderCellPhone'],
                    ];

                    $otherValue = [
                        'reflectApplyMemberInfo' => [ 'orderNo' => $order['orderNo'] ],
                        'simpleJoin' => 'y',
                    ];
                    $session = \App::getInstance('session');
                    $memberDAO = \App::load('Component\\Member\\MemberDAO');
                    $before = $memberDAO->selectMemberByOne($memData['memNo']);
                    $session->set(Member::SESSION_MODIFY_MEMBER_INFO, $before);

                    $arrBindMem = $this->db->get_binding(
                        DBTableField::tableMember(),
                        $memData,
                        'update',
                        array_keys($memData),
                        [
                            'sno',
                            'memNo',
                        ]
                    );
                    $this->db->bind_param_push($arrBindMem['bind'], 'i', Session::get('member.memNo'));
                    $this->db->set_update_db(DB_MEMBER, $arrBindMem['param'], 'memNo=?', $arrBindMem['bind']);
                    unset($arrBindMem);

                    $session->set('member.memNm', $orderInfo['orderName']);
                    $request = \App::getInstance('request');

                    $historyFilter = array_keys($memData);
                    $historyService = \App::load('Component\\Member\\History');
                    $historyService->setMemNo($memData['memNo']);
                    $historyService->setProcessor('member');
                    $historyService->setProcessorIp($request->getRemoteAddress());
                    $historyService->initBeforeAndAfter();
                    $historyService->setOtherValue($otherValue);
                    $historyService->addFilter($historyFilter);
                    $historyService->writeHistory();
                    unset($historyFilter);
                    unset($historyService);
                }

                if ($orderInfo['reflectApplyMember'] === 'y' || $orderInfo['reflectApplyDirectMember'] === 'y' || $orderInfo['reflectApplyShippingMember'] === 'y') {
                    $memData = [
                        'memNo'      => $order['memNo'],
                        'zipcode'    => $orderInfo['receiverZipcode'],
                        'zonecode'   => $orderInfo['receiverZonecode'],
                        'address'    => $orderInfo['receiverAddress'],
                        'addressSub' => $orderInfo['receiverAddressSub'],
                        'phone'      => $orderInfo['receiverPhone'],
                        'cellPhone'  => $orderInfo['receiverCellPhone'],
                        'lastSaleDt' => date('Y-m-d G:i:s'),
                    ];

                    $otherValue = [
                        'reflectApplyMemberInfo' => [ 'orderNo' => $order['orderNo'] ],
                    ];
                    $session = \App::getInstance('session');
                    $memberDAO = \App::load('Component\\Member\\MemberDAO');
                    $before = $memberDAO->selectMemberByOne($memData['memNo']);
                    $session->set(Member::SESSION_MODIFY_MEMBER_INFO, $before);

                } else {
                    $memData = [
                        'lastSaleDt' => date('Y-m-d G:i:s'),
                    ];
                }
                $arrBindMem = $this->db->get_binding(
                    DBTableField::tableMember(),
                    $memData,
                    'update',
                    array_keys($memData),
                    [
                        'sno',
                        'memNo',
                    ]
                );
                $this->db->bind_param_push($arrBindMem['bind'], 'i', Session::get('member.memNo'));
                $this->db->set_update_db(DB_MEMBER, $arrBindMem['param'], 'memNo=?', $arrBindMem['bind']);
                unset($arrBindMem);

                if($orderInfo['reflectApplyMember'] === 'y' || $orderInfo['reflectApplyDirectMember'] === 'y' || $orderInfo['reflectApplyShippingMember'] === 'y') {

                    $request = \App::getInstance('request');

                    $historyFilter = array_keys($memData);
                    $historyService = \App::load('Component\\Member\\History');
                    $historyService->setMemNo($memData['memNo']);
                    $historyService->setProcessor('member');
                    $historyService->setProcessorIp($request->getRemoteAddress());
                    $historyService->initBeforeAndAfter();
                    $historyService->setOtherValue($otherValue);
                    $historyService->addFilter($historyFilter);
                    $historyService->writeHistory();
                }

                // 배송지 추가 선택시 저장
                if ($orderInfo['reflectApplyDelivery'] === 'y') {
                    $deliveryData = [
                        'shippingTitle' => $orderInfo['receiverName'],
                        'shippingName' => $orderInfo['receiverName'],
                        'shippingCountryCode' => $orderInfo['receiverCountryCode'],
                        'shippingZipcode' => $orderInfo['receiverZipcode'],
                        'shippingZonecode' => $orderInfo['receiverZonecode'],
                        'shippingAddress' => $orderInfo['receiverAddress'],
                        'shippingAddressSub' => $orderInfo['receiverAddressSub'],
                        'shippingPhonePrefix' => $orderInfo['receiverPhonePrefix'],
                        'shippingPhone' => $orderInfo['receiverPhone'],
                        'shippingCellPhonePrefix' => $orderInfo['receiverCellPhonePrefix'],
                        'shippingCellPhone' => $orderInfo['receiverCellPhone'],
                        'shippingCity' => $orderInfo['receiverCity'],
                        'shippingState' => $orderInfo['receiverState'],
                    ];
                    if (empty($this->getShippingDefaultFlYn()) === true) {
                        $deliveryData['defaultFl'] = 'y';
                    }

                    // TODO 배송지 저장 실패시 처리
                    if (!$this->registShippingAddress($deliveryData)) {
                    }
                }
                if (\Component\Order\OrderMultiShipping::isUseMultiShipping() === true && \Globals::get('gGlobal.isFront') === false && $orderInfo['multiShippingFl'] == 'y') {
                    foreach ($orderInfo['reflectApplyDeliveryAdd'] as $key => $val) {
                        if ($val == 'y') {
                            $orderInfo['receiverPhoneAdd'][$key] = StringUtils::numberToPhone(str_replace('-', '', $orderInfo['receiverPhoneAdd'][$key]));
                            $orderInfo['receiverCellPhoneAdd'][$key] = StringUtils::numberToPhone(str_replace('-', '', $orderInfo['receiverCellPhoneAdd'][$key]));
                            $deliveryData = [
                                'shippingTitle' => $orderInfo['receiverNameAdd'][$key],
                                'shippingName' => $orderInfo['receiverNameAdd'][$key],
                                'shippingCountryCode' => $orderInfo['receiverCountryCodeAdd'][$key],
                                'shippingZipcode' => $orderInfo['receiverZipcodeAdd'][$key],
                                'shippingZonecode' => $orderInfo['receiverZonecodeAdd'][$key],
                                'shippingAddress' => $orderInfo['receiverAddressAdd'][$key],
                                'shippingAddressSub' => $orderInfo['receiverAddressSubAdd'][$key],
                                'shippingPhonePrefix' => $orderInfo['receiverPhonePrefixAdd'][$key],
                                'shippingPhone' => $orderInfo['receiverPhoneAdd'][$key],
                                'shippingCellPhonePrefix' => $orderInfo['receiverCellPhonePrefixAdd'][$key],
                                'shippingCellPhone' => $orderInfo['receiverCellPhoneAdd'][$key],
                                'shippingCity' => $orderInfo['receiverCityAdd'][$key],
                                'shippingState' => $orderInfo['receiverStateAdd'][$key],
                            ];
                            if (empty($this->getShippingDefaultFlYn()) === true) {
                                $deliveryData['defaultFl'] = 'y';
                            }

                            // TODO 배송지 저장 실패시 처리
                            if (!$this->registShippingAddress($deliveryData)) {
                            }
                        }
                    }
                }
            } elseif (MemberUtil::checkLogin() == 'guest') {
                // 비회원 주문 로그인 처리
                MemberUtil::guestOrder($order['orderNo'], $orderInfo['orderName']);
            }
        } // 수기주문인 경우
        else {
            if ($order['memNo'] > 0) {
                if ($orderInfo['reflectApplyMember'] === 'y') {
                    $memData = [
                        'zipcode'    => $orderInfo['receiverZipcode'],
                        'zonecode'   => $orderInfo['receiverZonecode'],
                        'address'    => $orderInfo['receiverAddress'],
                        'addressSub' => $orderInfo['receiverAddressSub'],
                        'phone'      => $orderInfo['receiverPhone'],
                        'cellPhone'  => $orderInfo['receiverCellPhone'],
                        'lastSaleDt' => date('Y-m-d G:i:s'),
                    ];
                } else {
                    $memData = [
                        'lastSaleDt' => date('Y-m-d G:i:s'),
                    ];
                }
                $arrBindMem = $this->db->get_binding(
                    DBTableField::tableMember(),
                    $memData,
                    'update',
                    array_keys($memData),
                    [
                        'sno',
                        'memNo',
                    ]
                );
                $this->db->bind_param_push($arrBindMem['bind'], 'i', $order['memNo']);
                $this->db->set_update_db(DB_MEMBER, $arrBindMem['param'], 'memNo=?', $arrBindMem['bind']);
                unset($arrBindMem);
            }

            // 관리자 메모
            //$order['adminMemo'] = $orderInfo['adminMemo'];

            // 상품º주문번호별 메모
            if($orderInfo['adminOrderGoodsMemo']){
                $orderAdmin = new OrderAdmin();
                $arrMemoData['mode'] = 'self_order';
                $arrMemoData['orderNo'] = $this->orderNo;
                $arrMemoData['orderMemoCd'] = $orderInfo['orderMemoCd'];
                $arrMemoData['adminOrderGoodsMemo'] = $orderInfo['adminOrderGoodsMemo'];
                $orderAdmin->insertAdminOrderGoodsMemo($arrMemoData);
            }
        }

        // 주문시 배송정보 회원정보에 반영
        if ($orderInfo['shippingDefault'] == 'y') {
            $this->defaultShippingAddress($orderInfo['shippingSno']);
        }

        //간편결제 관련 데이터 설정
        if ($orderInfo['fintechData']) {
            $order['fintechData'] = $orderInfo['fintechData'];
        }
        $checkoutData = [];
        if ($orderInfo['checkoutData']) {
            $checkoutData = json_decode($orderInfo['checkoutData'], true);
        }
        $checkoutData['goodsSno'] = $arrOrderGoodsSno['GoodsSno'];
        $orderInfo['checkoutData'] = json_encode($checkoutData);
        $order['checkoutData'] = $orderInfo['checkoutData'];

        $order['multiShippingFl'] = $orderInfo['multiShippingFl'];
        $order['trackingKey'] = $orderInfo['trackingKey'];

        // 주문 저장
        $arrBind = $this->db->get_binding(DBTableField::tableOrder(), $order, 'insert');
        $this->db->set_insert_db(DB_ORDER, $arrBind['param'], $arrBind['bind'], 'y', false);
        unset($arrBind);

        // 최초결제 결제히스토리 저장
        $history = [
            'orderNo' => $this->orderNo,// 주문번호
            'type' => 'fs', //타입 (setOrderProcessLog의 payHistoryType 참조)
            'settlePrice' => $order['settlePrice'],// 실결제금액
            'totalGoodsPrice' => $order['totalGoodsPrice'],// 상품판매금액
            'totalDeliveryCharge' => $order['totalDeliveryCharge'],// 배송비 (지역별배송비 포함)
            'totalDeliveryInsuranceFee' => $order['totalDeliveryInsuranceFee'],// 해외배송보험료
            'totalGoodsDcPrice' => $order['totalGoodsDcPrice'],// 상품할인
            'totalMemberDcPrice' => $order['totalMemberDcPrice'],// 회원추가할인(상품)
            'totalMemberOverlapDcPrice' => $order['totalMemberOverlapDcPrice'],// 회원중복할인(상품)
            'totalMemberDeliveryDcPrice' => $order['totalMemberDeliveryDcPrice'],// 회원할인(배송비)
            'totalCouponGoodsDcPrice' => $order['totalCouponGoodsDcPrice'],// 쿠폰할인(상품)
            'totalCouponOrderDcPrice' => $order['totalCouponOrderDcPrice'],// 쿠폰할인(주문)
            'totalCouponDeliveryDcPrice' => $order['totalCouponDeliveryDcPrice'],// 쿠폰할인(배송비)
            'useDeposit' => $order['useDeposit'],// 예치금 useDeposit
            'useMileage' => $order['useMileage'],// 마일리지 useMileage
            'totalMileage' => $order['totalMileage'],// 총적립금액 totalMileage
            'memo' => '', //설명
        ];
        // 마이앱 사용에 따른 분기 처리
        if ($this->useMyapp) {
            $history['totalMyappDcPrice'] = $order['totalMyappDcPrice'];// 마이앱할인(상품)
        }
        $this->setPayHistory($history);
        $termAgreePrivateMarketing = gd_isset($orderInfo['termAgreePrivateMarketing'], 'n');
        $orderInfo['smsFl'] = $termAgreePrivateMarketing == 'n' ? 'n' : 'y';

        // 주문자/수취인 정보 저장
        $orderInfo['orderNo'] = $this->orderNo;
        $arrBind = $this->db->get_binding(DBTableField::tableOrderInfo(), $orderInfo, 'insert');
        $this->db->set_insert_db(DB_ORDER_INFO, $arrBind['param'], $arrBind['bind'], 'y', false);
        $orderInfoSno[0] = $this->db->insert_id();
        unset($arrBind);

        if (\Component\Order\OrderMultiShipping::isUseMultiShipping() === true && \Globals::get('gGlobal.isFront') === false && $orderInfo['multiShippingFl'] == 'y') {
            $tmpOrderInfo = $orderInfo;
            $orderBasic = gd_policy('order.basic');
            foreach ($orderInfo['receiverNameAdd'] as $key => $val) {
                $orderInfo['receiverPhoneAdd'][$key] = StringUtils::numberToPhone(str_replace('-', '', $orderInfo['receiverPhoneAdd'][$key]));
                $orderInfo['receiverCellPhoneAdd'][$key] = StringUtils::numberToPhone(str_replace('-', '', $orderInfo['receiverCellPhoneAdd'][$key]));
                $tmpOrderInfo['receiverName'] = $orderInfo['receiverNameAdd'][$key];
                $tmpOrderInfo['receiverCountryCode'] = $orderInfo['receiverCountryCodeAdd'][$key];
                $tmpOrderInfo['receiverCity'] = $orderInfo['receiverCityAdd'][$key];
                $tmpOrderInfo['receiverState'] = $orderInfo['receiverStateAdd'][$key];
                $tmpOrderInfo['receiverAddress'] = $orderInfo['receiverAddressAdd'][$key];
                $tmpOrderInfo['receiverAddressSub'] = $orderInfo['receiverAddressSubAdd'][$key];
                $tmpOrderInfo['receiverZonecode'] = $orderInfo['receiverZonecodeAdd'][$key];
                $tmpOrderInfo['receiverZipcode'] = $orderInfo['receiverZipcodeAdd'][$key];
                $tmpOrderInfo['receiverPhonePrefixCode'] = $orderInfo['receiverPhonePrefixCodeAdd'][$key];
                $tmpOrderInfo['receiverPhone'] = $orderInfo['receiverPhoneAdd'][$key];
                $tmpOrderInfo['receiverCellPhonePrefixCode'] = $orderInfo['receiverCellPhonePrefixCodeAdd'][$key];
                $tmpOrderInfo['receiverCellPhone'] = $orderInfo['receiverCellPhoneAdd'][$key];
                $tmpOrderInfo['orderMemo'] = $orderInfo['orderMemoAdd'][$key];
                $tmpOrderInfo['reflectApplyDelivery'] = $orderInfo['reflectApplyDeliveryAdd'][$key];
                $tmpOrderInfo['visitAddress'] = $orderInfo['visitAddressAdd'][$key];
                $tmpOrderInfo['visitName'] = $orderInfo['visitNameAdd'][$key];
                $tmpOrderInfo['visitPhone'] = $orderInfo['visitPhoneAdd'][$key];
                $tmpOrderInfo['visitMemo'] = $orderInfo['visitMemoAdd'][$key];
                $tmpOrderInfo['deliveryVisit'] = $orderInfo['deliveryVisitAdd'][$key];
                $tmpOrderInfo['orderInfoCd'] = $key + 1;
                if ($orderBasic['useSafeNumberFl'] == 'y') {
                    $tmpOrderInfo['receiverUseSafeNumberFl'] = $orderInfo['receiverUseSafeNumberFlAdd'][$key];
                }

                // 주문자/수취인 정보 저장
                $orderInfo['orderNo'] = $this->orderNo;
                $arrBind = $this->db->get_binding(DBTableField::tableOrderInfo(), $tmpOrderInfo, 'insert');
                $this->db->set_insert_db(DB_ORDER_INFO, $arrBind['param'], $arrBind['bind'], 'y', false);
                $orderInfoSno[$key] = $this->db->insert_id();
                unset($arrBind);
            }
        }

        //
        foreach ($orderMultiDeliverySno as $key => $val) {
            foreach ($val as $tVal) {
                $arrData['orderInfoSno'] = $orderInfoSno[$key];
                $arrBind = $this->db->get_binding(DBTableField::tableOrderDelivery(), $arrData, 'update', array_keys($arrData));
                $this->db->bind_param_push($arrBind['bind'], 'i', $tVal);
                $this->db->set_update_db(DB_ORDER_DELIVERY, $arrBind['param'], 'sno = ?', $arrBind['bind']);
                unset($arrData);
            }
        }

        // 주문 사은품 정보 저장
        if (isset($orderInfo['gift'])) {
            $giftPresentNo = null;
            // 사은품 콤포넌트 호출
            $giftPresent = \App::load('\\Component\\Gift\\Gift');

            // 이마트 보안 취약점 요청사항 > 사은품 유효성 체크 (사용가능 사은품 정보를 가져온다)
            $giftInfo = $giftPresent->getGiftPresentOrder($orderInfo['giftForData'], 0, false, false);

            if (count($giftInfo) > 0) {
                foreach ($orderInfo['gift'] as $presentSno => $giftData) {
                    // 주문시 사용된 사은품 정보가 유효하지 않으면 삭제
                    if (!array_key_exists($presentSno, $giftInfo)) {
                        unset($orderInfo['gift'][$presentSno]);
                    }
                    // 사은품 정책 저장
                    if ($presentSno != $giftPresentNo) {
                        $giftPresentNo = $presentSno;
                        $giftPresentData = $giftPresent->getGiftPresentData($giftPresentNo);
                    }
                    foreach ($giftData as $gift) {
                        $gift['orderNo'] = $this->orderNo;
                        $gift['presentSno'] = $presentSno;
                        $gift['minusStockFl'] = 'n'; // 무조건 n 스킨에서 값이 잘못 던져짐
                        $gift['giftPolicy'] = json_encode($giftPresentData, JSON_UNESCAPED_UNICODE);
                        // 사은품 선택한것이 있으면 저장..
                        if (isset($gift['giftNo']) === true) {
                            // 주문 사은품 정보 저장
                            $arrBind = $this->db->get_binding(DBTableField::tableOrderGift(), $gift, 'insert');
                            $this->db->set_insert_db(DB_ORDER_GIFT, $arrBind['param'], $arrBind['bind'], 'y', false);
                            unset($arrBind);
                        }
                    }
                    unset($giftData);
                }
            }
            unset($orderInfo['gift']);
        }

        // 주문쿠폰 정보
        $couponInfo = $goodsCouponInfo;
        $goodsPriceArr = [
            'goodsPriceSum'      => $order['totalSumGoodsPrice']['goodsPrice'],
            'optionPriceSum'     => $order['totalSumGoodsPrice']['optionPrice'],
            'optionTextPriceSum' => $order['totalSumGoodsPrice']['optionTextPrice'],
            'addGoodsPriceSum'   => $order['totalSumGoodsPrice']['addGoodsPrice'],
        ];
        if (empty($orderInfo['couponApplyOrderNo']) === false) {
            $orderCouponNos = explode(INT_DIVISION, $orderInfo['couponApplyOrderNo']);
            $coupon = \App::load('\\Component\\Coupon\\Coupon');

            foreach ($orderCouponNos as $orderCouponNo) {
                if ($orderCouponNo) { // 적용쿠폰번호가 있을 경우 DB 삽입
                    // 장바구니에 사용된 회원쿠폰의 정율도 정액으로 계산된 금액
                    $realOrderCouponPriceData = $coupon->getMemberCouponPrice($goodsPriceArr, $orderCouponNo);
                    if ($realOrderCouponPriceData['memberCouponAlertMsg'][$orderCouponNo] == 'LIMIT_MIN_PRICE') {
                        // @todo 'LIMIT_MIN_PRICE' 일때 구매금액 제한에 걸려 사용 못하는 쿠폰 처리
                        // @todo 적용된 쿠폰 제거?
                        // @todo 수량 변경 시 구매금액 제한에 걸림
                        true;
                    }
                    $arrTmp = $coupon->getMemberCouponInfo($orderCouponNo, 'c.couponUseType, c.couponNm, c.couponUseType, mc.memberCouponStartDate, mc.memberCouponEndDate');
                    $orderCouponInfo = [
                        'orderNo' => $this->orderNo,
                        'orderCd' => '',
                        'goodsNo' => '',
                        'memberCouponNo' => $orderCouponNo,
                        'expireSdt' => $arrTmp['memberCouponStartDate'],
                        'expireEdt' => $arrTmp['memberCouponEndDate'],
                        'couponNm' => $arrTmp['couponNm'],
                        'couponUseType' => $arrTmp['couponUseType'],
                    ];

                    // 주문할인 쿠폰 금액 적용
                    if ($order['totalCouponOrderDcPrice'] > 0 && $realOrderCouponPriceData['memberCouponSalePrice'][$orderCouponNo] > 0) {
                        if ($order['totalCouponOrderDcPrice'] < $realOrderCouponPriceData['memberCouponSalePrice'][$orderCouponNo]) {
                            $orderCouponInfo['couponPrice'] = $order['totalCouponOrderDcPrice'];
                        } else {
                            $orderCouponInfo['couponPrice'] = array_shift($realOrderCouponPriceData['memberCouponSalePrice']);
                        }
                    }

                    // 배송비 쿠폰 금액 적용
                    if ($order['totalCouponDeliveryDcPrice'] > 0 && $realOrderCouponPriceData['memberCouponDeliveryPrice'][$orderCouponNo] > 0) {
                        if ($order['totalCouponDeliveryDcPrice'] < $realOrderCouponPriceData['memberCouponDeliveryPrice'][$orderCouponNo]) {
                            $orderCouponInfo['couponPrice'] = $order['totalCouponDeliveryDcPrice'];
                        } else {
                            $orderCouponInfo['couponPrice'] = array_shift($realOrderCouponPriceData['memberCouponDeliveryPrice']);
                        }
                    }

                    // 마일리지 적립 쿠폰 금액 적용
                    if ($order['totalCouponOrderMileage'] > 0 && $realOrderCouponPriceData['memberCouponAddMileage'][$orderCouponNo] > 0) {
                        if ($order['totalCouponOrderMileage'] < $realOrderCouponPriceData['memberCouponAddMileage'][$orderCouponNo]) {
                            $orderCouponInfo['couponMileage'] = $order['totalCouponOrderMileage'];
                        } else {
                            $orderCouponInfo['couponMileage'] = array_shift($realOrderCouponPriceData['memberCouponAddMileage']);
                        }
                    }

                    array_push($couponInfo, $orderCouponInfo);
                }
            }
        }

        // 쿠폰 데이터 저장
        if (empty($couponInfo) === false) {
            foreach ($couponInfo as $cKey => $cVal) {
                // 쿠폰 사용 정보 저장
                $arrBind = $this->db->get_binding(DBTableField::tableOrderCoupon(), $cVal, 'insert');
                $this->db->set_insert_db(DB_ORDER_COUPON, $arrBind['param'], $arrBind['bind'], 'y', false);
                unset($arrBind);
                unset($tmp);
            }
        }

        // 세금 계산서 저장 설정
        if ((in_array($orderInfo['settleKind'], $this->settleKindReceiptPossible) === true) && $orderInfo['receiptFl'] == 't') {
            $taxInfo = gd_policy('order.taxInvoice');
            $member = \App::load('\\Component\\Member\\Member');
            $memInfo = $member->getMemberId($order['memNo']);
            // 주문서 저장 설정
            $taxInfo['applicantNm'] =
            $taxInfo['requestNm'] = $orderInfo['orderName'];
            $taxInfo['applicantId'] =
            $taxInfo['requestId'] = $memInfo ? $memInfo['memId'] : '비회원';
            $taxInfo['orderNo'] = $this->orderNo;
            $taxInfo['issueMode'] = 'u';
            $taxInfo['statusFl'] = 'r';
            $taxInfo['requestNm'] = $orderInfo['orderName'];
            $taxInfo['requestGoodsNm'] = $order['orderGoodsNm'];
            $taxInfo['requestIP'] = Request::getRemoteAddress();
            $taxInfo['taxCompany'] = $orderInfo['taxCompany'];
            $taxInfo['taxBusiNo'] = $orderInfo['taxBusiNo'];
            $taxInfo['taxCeoNm'] = $orderInfo['taxCeoNm'];
            $taxInfo['taxService'] = $orderInfo['taxService'];
            $taxInfo['taxItem'] = $orderInfo['taxItem'];
            $taxInfo['taxEmail'] = is_array($orderInfo['taxEmail']) ? implode('@', $orderInfo['taxEmail']) : $orderInfo['taxEmail'];
            $taxInfo['taxZipcode'] = $orderInfo['taxZipcode'];
            $taxInfo['taxZonecode'] = $orderInfo['taxZonecode'];
            $taxInfo['taxAddress'] = $orderInfo['taxAddress'];
            $taxInfo['taxAddressSub'] = $orderInfo['taxAddressSub'];
            //$taxInfo['settlePrice'] = $order['settlePrice']; // 면세 상품은 빠져야 함
            //$taxInfo['settlePrice'] = $taxSupplyPrice + $taxVatPrice;
            //$taxInfo['supplyPrice'] = $taxSupplyPrice;
            //$taxInfo['taxPrice'] = $taxVatPrice;
            $taxInfo['taxStepFl'] = $taxInfo['taxStepFl'];
            $taxInfo['taxDeliveryCompleteFl'] = $taxInfo['taxDeliveryCompleteFl'];
            $taxInfo['taxPolicy'] = $taxInfo['taxDeliveryFl'] . STR_DIVISION . $taxInfo['TaxMileageFl'] . STR_DIVISION . $taxInfo['taxDepositFl']; //정책저장

            // 로그 설정
            $taxInfo['taxLog'] = '====================================================' . chr(10);
            $taxInfo['taxLog'] .= '세금계산서 신청 : 확인시간(' . date('Y-m-d H:i:s') . ')' . chr(10);
            $taxInfo['taxLog'] .= '====================================================' . chr(10);
            $taxInfo['taxLog'] .= '처리상태 : 발행 신청' . chr(10);
            $taxInfo['taxLog'] .= '요청정보 : 주문 시 고객 요청' . chr(10);
            $taxInfo['taxLog'] .= '처리 IP : ' . Request::getRemoteAddress() . chr(10);
            $taxInfo['taxLog'] .= '====================================================' . chr(10);

            // 세금 계산서 저장
            $arrBind = $this->db->get_binding(DBTableField::tableOrderTax(), $taxInfo, 'insert');
            $this->db->set_insert_db(DB_ORDER_TAX, $arrBind['param'], $arrBind['bind'], 'y', false);
            unset($arrBind);
        }

        // 현금영수증 저장 설정
        if (in_array($orderInfo['settleKind'], $this->settleKindReceiptPossible) === true && $orderInfo['receiptFl'] == 'r') {
            // --- PG 설정 불러오기
            $pgConf = gd_pgs();

            // 주문서 저장 설정
            $receipt['orderNo'] = $this->orderNo;
            $receipt['issueMode'] = 'u';
            $receipt['statusFl'] = 'r';
            $receipt['servicePrice'] = 0;
            $receipt['requestNm'] = $orderInfo['orderName'];
            $receipt['requestGoodsNm'] = $order['orderGoodsNm'];
            $receipt['requestIP'] = Request::getRemoteAddress();
            $receipt['requestEmail'] = $orderInfo['orderEmail'];
            $receipt['requestCellPhone'] = $orderInfo['orderCellPhone'];
            $receipt['useFl'] = $orderInfo['cashUseFl'];
            $receipt['certFl'] = $orderInfo['cashCertFl'];
            $receipt['certNo'] = Encryptor::encrypt($orderInfo['cashCertNo']);
            $receipt['settlePrice'] = $order['settlePrice'];
            $receipt['pgName'] = $pgConf['pgName'];
            $receipt['adminMemo'] = $orderInfo['cashCertNo'];

            $receipt['supplyPrice'] = $taxSupplyPrice;
            $receipt['taxPrice'] = $taxVatPrice;
            $receipt['freePrice'] = $taxFreePrice;

            // 현금영수증 저장
            $arrBind = $this->db->get_binding(DBTableField::tableOrderCashReceipt(), $receipt, 'insert');
            $this->db->set_insert_db(DB_ORDER_CASH_RECEIPT, $arrBind['param'], $arrBind['bind'], 'y', false);
            unset($arrBind);
            $sno = $this->db->insert_id();
            $this->db->set_update_db(DB_ORDER_CASH_RECEIPT, 'firstRegDt = regDt', 'sno = '.$sno);
        }

        // 주문처리중 접수된 주문건으로 인해 재고를 다시한번 체크
        if (!$this->recheckOrderStockCnt($this->orderNo)) {
            throw new Exception(__('재고 부족으로 구매가 불가능합니다.'));
        }

        // 상태 변경에 따른 일괄 처리 (마일리지/예치금/쿠폰사용 및 재고차감 처리)
        $arrOrderGoodsSno['changeStatus'] = $orderStatusPre;
        if ($orderInfo['settleKind'] == 'gb') {
            $this->statusChangeCodeO($this->orderNo, $arrOrderGoodsSno, false);
        } elseif ($orderInfo['settleKind'] == self::SETTLE_KIND_ZERO) {
            $this->statusChangeCodeP($this->orderNo, $arrOrderGoodsSno, false);
        } else {
            // 무통장 입금인 경우 주문 접수에 관련 된 작업을 진행하며, PG의 경우 성공 후 반드시 아래의 작업을 별도로 실행해야 합니다.
            // 입금대기 (o1) 상태에 처리해야 할 마일리지/예치금/쿠폰 사용체크와 마일리지의 경우 설정에 따라 지급합니다.
            $this->setMemberMileageCouponState($this->orderNo, $arrOrderGoodsSno);
        }

        // 페이스북 FBE v2 pixelCookie 값 주문서 저장
        if(\Cookie::has('_fbp') === true || \Cookie::has('_fbc') === true){
            $fbPixelCookie['fbp'] = \Cookie::get('_fbp');
            $fbPixelCookie['fbc'] = \Cookie::get('_fbc');

            $arrBind = [];
            $arrBind['param'][] = 'fbPixelKey = ?';
            $this->db->bind_param_push($arrBind['bind'], 's', json_encode($fbPixelCookie, JSON_UNESCAPED_UNICODE));
            $this->db->bind_param_push($arrBind['bind'], 's', $this->orderNo);
            $this->db->set_update_db(DB_ORDER, $arrBind['param'], 'orderNo = ?', $arrBind['bind']);
        }
    }

    /**
     * 주문시 실시간 재고현황 체크를 위해 사용
     * 반드시 DB에 주문데이터가 들어가 있어야 합니다.
     *
     * @param integer $orderNo 주문번호
     * @param boolean $goodsFl 상품재고 체크여부
     * @param boolean $giftFl  사은품재고 체크여부
     *
     * @return boolean 재고없는 경우 false 반환
     * @author Jong-tae Ahn <qnibus@godo.co.kr>
     */
    public function recheckOrderStockCnt($orderNo, $goodsFl = true, $giftFl = false)
    {
        // 사음품 재고 체크
        if ($giftFl === true) {
            $arrField = DBTableField::setTableField('tableOrderGift', ['giftNo'], null, 'og');
            $strSQL = 'SELECT g.stockFl, g.stockCnt, ' . implode(', ', $arrField) . ' FROM ' . DB_ORDER_GIFT . ' og LEFT JOIN ' . DB_GIFT . ' g ON g.giftNo = og.giftNo WHERE og.orderNo = ? ORDER BY og.sno ASC';
            $arrBind = [
                's',
                $orderNo,
            ];
            $giftData = $this->db->query_fetch($strSQL, $arrBind);
            if (empty($giftData) === false) {
                foreach ($giftData as $key => $val) {
                    // 재고사용 조건이면서 재고가 없는 경우 구매불가
                    if (empty($val['giftNo']) === false && $val['stockFl'] == 'y' && $val['stockCnt'] == 0) {
                        return false;
                    }
                }
            }
            unset($arrBind, $arrField, $giftData);
        }

        // 주문 상품 재고 체크
        if ($goodsFl === true) {
            $arrInclude = [
                'orderCd',
                'goodsType',
                'goodsNo',
                'goodsCnt',
                'optionInfo',
            ];
            $arrField = DBTableField::setTableField('tableOrderGoods', $arrInclude);
            $strSQL = 'SELECT sno, ' . implode(', ', $arrField) . ' FROM ' . DB_ORDER_GOODS . ' WHERE orderNo = ? ORDER BY sno ASC';
            $arrBind = [
                's',
                $orderNo,
            ];
            $orderGoodsData = $this->db->query_fetch($strSQL, $arrBind);
            unset($arrBind);
            if (empty($orderGoodsData) === false) {
                foreach ($orderGoodsData as $key => $val) {
                    if ($val['goodsType'] == 'addGoods') {
                        // goodsNo bind data
                        $this->db->bind_param_push($arrBind, 'i', $val['goodsNo']);
                        $arrWhere[] = 'ag.addGoodsNo = ?';

                        // 추가상품 옵션 데이타
                        $this->db->strField = 'ag.stockCnt, ag.stockUseFl, ag.soldOutFl';
                        $this->db->strWhere = implode(' AND ', $arrWhere);
                        $query = $this->db->query_complete();
                        $strSQL = 'SELECT ' . array_shift($query) . ' FROM ' . DB_ADD_GOODS . ' ag ' . implode(' ', $query);
                        $addGoodsData = $this->db->query_fetch($strSQL, $arrBind, false);
                        if (empty($addGoodsData) === false) {
                            // 주문처리중 품절처리를 한경우
                            if ($addGoodsData['soldOutFl'] === 'y') {
                                return false;
                            }
                            // 재고사용 조건이면서 재고가 없거나 재고수량보다 구매수량이 많은 경우 구매불가
                            if ($addGoodsData['stockUseFl'] == '1' && ($addGoodsData['stockCnt'] == 0 || $addGoodsData['stockCnt'] - $val['goodsCnt'] < 0)) {
                                return false;
                            }
                        }
                        unset($arrWhere, $arrBind, $tmpOption);
                    } else {
                        // goodsNo bind data
                        $this->db->bind_param_push($arrBind, 'i', $val['goodsNo']);
                        $arrWhere[] = 'go.goodsNo = ?';

                        // 옵션 where문 data
                        if (empty($val['optionInfo']) === true) {
                            $arrWhere[] = 'go.optionNo = ?';
                            $arrWhere[] = '(go.optionValue1 = \'\' OR isnull(go.optionValue1))';
                            $this->db->bind_param_push($arrBind, 'i', 1);
                        } else {
                            $tmpOption = json_decode(gd_htmlspecialchars_stripslashes($val['optionInfo']), true);
                            foreach ($tmpOption as $oKey => $oVal) {
                                $optionKey = $oKey + 1;
                                $arrWhere[] = 'go.optionValue' . $optionKey . ' = ?';
                                $optionNm[] = $oVal[1];
                                $this->db->bind_param_push($arrBind, 's', $oVal[1]);
                            }
                        }

                        // 상품 옵션 데이타
                        $this->db->strField = 'go.stockCnt, g.stockFl, g.soldOutFl, go.optionSellFl';
                        $this->db->strWhere = implode(' AND ', $arrWhere);
                        $this->db->strJoin = 'INNER JOIN ' . DB_GOODS . ' as g ON go.goodsNo = g.goodsNo';
                        $query = $this->db->query_complete();
                        $strSQL = 'SELECT ' . array_shift($query) . ' FROM ' . DB_GOODS_OPTION . ' go ' . implode(' ', $query);
                        $optionData = $this->db->query_fetch($strSQL, $arrBind, false);
                        if (empty($optionData) === false) {
                            // 주문처리중 품절처리를 한경우
                            if ($optionData['soldOutFl'] === 'y' || $optionData['optionSellFl'] === 'n') {
                                return false;
                            }
                            // 재고사용 조건이면서 재고가 없거나 재고수량보다 구매수량이 많은 경우 구매불가
                            if ($optionData['stockFl'] == 'y' && ($optionData['stockCnt'] == 0 || $optionData['stockCnt'] - $val['goodsCnt'] < 0)) {
                                return false;
                            }
                        }
                        unset($arrWhere, $arrBind, $tmpOption);
                    }
                }
            }
        }

        return true;
    }

    /**
     * 주문 정보 저장
     *
     * @param array   $cartInfo     장바구니 상품 정보
     * @param array   $orderInfo    주문자, 수취인 정보 (폼 데이터)
     * @param array   $orderPrice   가격 및 할인 정보
     * @param boolean $checkSumData false 면 동울주문 체크를 안함
     *
     * @return boolean
     * @throws Exception
     * @todo 모바일앱 appOs, pushCode 체크
     */
    public function saveOrderInfo($cartInfo, $orderInfo, $orderPrice, $checkSumData = true)
    {
        // 동일 주문 체크
        if ($checkSumData) {
            $checkOrder = $this->setOrderCheckByChecksumData($cartInfo, $orderInfo, $orderPrice);

            // 동일 주문건이 있는 경우 해당 주문번호만 리턴함
            if ($checkOrder['code'] === 'SAME_ORDER') {
                $this->orderNo = $checkOrder['data'];

                return true;
            }
        }
        // 체크섬 데이터
        $orderPrice['checksumData'] = $checkOrder['data'];

        // 주문서 기본 저장 변수
        $orderPrice['memNo'] = Session::get('member.memNo');

        // 모바일 주문 체크
        if (Request::isMobile() && Request::isMobileDevice()) {
            $orderPrice['orderTypeFl'] = 'mobile';
            // 마이앱 주문시 OS 체크
            if ($this->useMyapp && Request::isMyapp()) {
                $myapp = \App::load('Bundle\\Component\\Myapp\\Myapp');
                $myappConnectOs = $myapp->getMyappOsAgent();
                // 푸시코드
                if (\Cookie::get('MyApp-PushId')) {
                    $pushCode = \Cookie::get('MyApp-PushId');
                } else {
                    // UserAgent
                    $getUserAgent = Request::getUserAgent();
                    $myAppPushId = explode('/', strstr($getUserAgent, 'MyApp-PushId/'));
                    $pushCode = trim($myAppPushId[1]);
                }
                $orderPrice['appOs'] = $myappConnectOs;
                $orderPrice['pushCode'] = $pushCode;
            }
        } else {
            $orderPrice['orderTypeFl'] = 'pc';
        }

        // 공통 주문저장 로직 실행
        $this->saveOrder($cartInfo, $orderInfo, $orderPrice);

        return true;
    }

    /**
     * 장바구니 필드에 orderNo 기입
     * PG에서 정해진 장바구니만 삭제하기 위한 용도로 사용
     *
     * @param mixed  $cartSno 장바구니 SNO
     * @param string $orderNo 업데이트 할 주문번호
     *
     * @author Jong-tae Ahn <qnibus@godo.co.kr>
     */
    public function updateCartWithOrderNo($cartSno, $orderNo)
    {
        // 선택한 상품만 주문시
        if (empty($cartSno) === false) {
            if (is_array($cartSno)) {
                $tmpWhere = [];
                foreach ($cartSno as $sno) {
                    $tmpWhere[] = $this->db->escape($sno);
                }
                $arrWhere[] = 'sno IN (' . implode(' , ', $tmpWhere) . ')';
                unset($tmpWhere);
            } elseif (is_numeric($cartSno)) {
                $arrWhere[] = 'sno = ' . $cartSno . '';
            }

            $arrBind = [
                's',
                $orderNo,
            ];
            $this->db->set_update_db(DB_CART, 'tmpOrderNo = ?', implode(' AND ', $arrWhere), $arrBind);
        }
    }

    /**
     * checksum Data 에 의해 동일 결제시도 주문 체크 (결제시도 줄임)
     *
     * @param array $cartInfo   장바구니 상품 정보
     * @param array $orderInfo  주문자, 수취인 정보 (폼 데이터)
     * @param array $orderPrice 가격 및 할인 정보
     *
     * @return boolean 동일 주문여부
     */
    protected function setOrderCheckByChecksumData($cartInfo, $orderInfo, $orderPrice)
    {
        // 무통장 입금인경우 패스
        if ($orderInfo['settleKind'] === 'gb' || $this->channel == 'naverpay') {
            $result['code'] = 'NO_ORDER';
            $result['data'] = '';

            return $result;
        }

        // 계속 변화가 되는 값은 제거
        foreach ($cartInfo as $aKey => $aVal) {
            foreach ($aVal as $bKey => $bVal) {
                foreach ($bVal as $cKey => $cVal) {
                    unset($cartInfo[$aKey][$bKey][$cKey]['tmpOrderNo']);
                }
            }
        }
        unset($orderInfo['csrfToken']);

        // 시간대 별로 동일하게 처리(오전/오후 구분)
        $orderInfo['checkDate'] = date('Y-m-d a');

        // 주문 정보값 serialize
        $serializeCartInfo = serialize($cartInfo);
        $serializeOrderInfo = serialize($orderInfo);
        $serializeOrderPrice = serialize($orderPrice);

        // serialize 처리된 값을 md5로 처리
        $checksumCartInfo = \Encryptor::checksum($serializeCartInfo, 'md5');
        $checksumOrderInfo = \Encryptor::checksum($serializeOrderInfo, 'md5');
        $checksumOrderPrice = \Encryptor::checksum($serializeOrderPrice, 'md5');

        // checksumData
        $checksumData = $checksumCartInfo . $checksumOrderInfo . $checksumOrderPrice;
        \Cookie::set('uniqueOrderCheckData', $checksumData, (3600 * 24));

        // 주문테이터 체크
        if ($orderInfo['settleKind'] == self::SETTLE_KIND_ZERO) {
            // 마일리지,예치금 전액결제시 동일주문 체크 추가
            $strSQL = 'SELECT orderNo FROM ' . DB_ORDER . ' WHERE checksumData = ? AND orderStatus = \'p1\'';
            $arrBind = [
                's',
                $checksumData,
            ];
        } else {
            $strSQL = 'SELECT orderNo FROM ' . DB_ORDER . ' WHERE checksumData = ? AND orderStatus = \'f1\'';
            $arrBind = [
                's',
                $checksumData,
            ];
        }

        $getData = $this->db->query_fetch($strSQL, $arrBind, false);

        // 결과
        if (empty($getData['orderNo']) === true) {
            // 동일 주문건이 없는경우 $checksumData 리턴
            $result['code'] = 'NO_ORDER';
            $result['data'] = $checksumData;

            return $result;
        } else {
            // 동일 주문건이 있는경우 주문번호 리턴
            $result['code'] = 'SAME_ORDER';
            $result['data'] = $getData['orderNo'];

            return $result;
        }
    }

    /**
     * 주문 당시 설정되었던 주문상태 정책을 가져온다.
     *
     * @param integer $orderNo 주문번호
     *
     * @return mixed
     */
    public function getOrderCurrentStatusPolicy($orderNo)
    {
        // 주문 상품 데이타
        $arrInclude = [
            'statusPolicy',
        ];
        $arrField = DBTableField::setTableField('tableOrder', $arrInclude);
        $strSQL = 'SELECT ' . implode(', ', $arrField) . ' FROM ' . DB_ORDER . ' WHERE orderNo = ?';
        $arrBind = [
            's',
            $orderNo,
        ];
        $getData = $this->db->query_fetch($strSQL, $arrBind, false);
        $data = json_decode(gd_htmlspecialchars_stripslashes(array_shift($getData)), true);

        // 주문 정책이 없는 경우 빈배열로 생성 처리 (오류 방지용)
        $requiredKey = [
            'correct',
            'mminus',
            'mplus',
            'cminus',
            'cplus',
            'sminus',
            'srestore',
        ];
        foreach ($requiredKey as $val) {
            if (!array_key_exists($val, $data)) {
                $data[$val] = [];
            } else {
                // 선택한 상태 이후 모든 상태에서 변경될 수 있도록 처리
                switch ($val) {
                    case 'cplus':
                    case 'mplus':
                        $canPlus = ['p','g','d','s','z'];
                        $data[$val] = array_slice($canPlus, array_search(substr($data[$val][0], 0, 1), $canPlus));
                        break;

                    case 'sminus':
                        $canStockMinus = ['o','p','g','d','s','z'];
                        $data[$val] = array_slice($canStockMinus, array_search(substr($data[$val][0], 0, 1), $canStockMinus));
                        break;
                }
            }
        }
        unset($getData, $arrInclude, $arrField);

        return $data;
    }

    /**
     * 현 주문상태 정책에 따른 마일리지/쿠폰/재고차감/지급 정책의 주문상태값을 반환한다.
     *
     * @return array
     */
    public function getOrderStatusPolicy()
    {
        $data = [];

        // $this->statusPolicy에서 주문/취소 상태로 분리 저장
        $orderPolicy = array_slice($this->statusPolicy, 0, 5);
        $cancelPolicy = array_slice($this->statusPolicy, 5);

        // 주문상태 데이터 설정
        foreach (array_reverse($orderPolicy) as $key => $val) {
            switch ($key) {
                case self::ORDER_STATUS_ORDER:
                    $status = 'o1';
                    break;
                case self::ORDER_STATUS_PAYMENT:
                    $status = 'p1';
                    break;
                case self::ORDER_STATUS_GOODS:
                    $status = 'g1';
                    break;
                case self::ORDER_STATUS_DELIVERY:
                    $status = 'd2';
                    break;
                case self::ORDER_STATUS_SETTLE:
                    $status = 's1';
                    break;
            }
            if (isset($val['correct']) && $val['correct'] == 'y') {
                $data['correct'][] = $status;
            }
            if (isset($val['mplus']) && $val['mplus'] == 'y') {
                $data['mplus'][] = $status;
            }
            if (isset($val['cplus']) && $val['cplus'] == 'y') {
                $data['cplus'][] = $status;
            }
            if (isset($val['mminus']) && $val['mminus'] == 'y') {
                $data['mminus'][] = $status;
            }
            if (isset($val['cminus']) && $val['cminus'] == 'y') {
                $data['cminus'][] = $status;
            }
            if (isset($val['sminus']) && $val['sminus'] == 'y') {
                $data['sminus'][] = $status;
            }
        }

        // 취소상태 데이터 설정
        foreach ($cancelPolicy as $key => $val) {
            switch ($key) {
                case self::ORDER_STATUS_CANCEL:
                    $status = 'c1';
                    break;
                case self::ORDER_STATUS_FAIL:
                    $status = 'f1';
                    break;
                case self::ORDER_STATUS_BACK:
                    $status = 'b4';
                    break;
                case self::ORDER_STATUS_EXCHANGE:
                    $status = 'e5';
                    break;
                case self::ORDER_STATUS_EXCHANGE_ADD:
                    $status = 'z5';
                    break;
                case self::ORDER_STATUS_REFUND:
                    $status = 'r3';
                    break;
            }
            if (isset($val['mrestore']) && $val['mrestore'] == 'y') {
                $data['mrestore'][] = $status;
            }
            if (isset($val['crestore']) && $val['crestore'] == 'y') {
                $data['crestore'][] = $status;
            }
            if (isset($val['srestore']) && $val['srestore'] == 'y') {
                $data['srestore'][] = $status;
            }
        }

        return $data;
    }

    /**
     * 비회원 주문조회 체크하기
     *
     * @param integer $orderNo
     * @param string  $orderNm
     *
     * @return array 비회원 여부
     * @throws \Framework\Debug\Exception\DatabaseException
     */
    public function isGuestOrder($orderNo, $orderNm)
    {
        StringUtils::strIsSet($orderNo, Session::get('guest.orderNo', ''));
        StringUtils::strIsSet($orderNm, '');
        $result = [
            'result'    => false,
            'orderNo'   => $orderNo,
        ];
        if ($orderNo != '' && $orderNm != '') {
            $funcSelectGuestOrder = function ($orderNoColumn = 'o.orderNo') use ($orderNo, $orderNm) {
                $this->db->strField = 'o.memNo, o.orderNo, o.orderChannelFl, o.apiOrderNo';
                $this->db->strJoin = 'JOIN ' . DB_ORDER_INFO . ' AS oi ON o.orderNo = oi.orderNo AND oi.orderInfoCd = 1';
                $this->db->strWhere = $orderNoColumn . ' = ? AND oi.orderName = ?';
                if (explode('.', $orderNoColumn)[1] == 'apiOrderNo') {
                    $this->db->strWhere .= ' AND o.orderChannelFl=\'naverpay\'';
                }
                $this->db->bind_param_push($bindParam, 's', $orderNo);
                $this->db->bind_param_push($bindParam, 's', $orderNm);
                $query = $this->db->query_complete();
                $strSQL = 'SELECT ' . array_shift($query) . ' FROM ' . DB_ORDER . ' AS o ' . implode(' ', $query);

                return $this->db->query_fetch($strSQL, $bindParam, false);
            };
            // 상점 주문 기준 조회
            $order = $funcSelectGuestOrder();
            StringUtils::strIsSet($order['orderNo'], '');
            $hasGuestOrder = $order['orderNo'] == $orderNo;
            if (!$hasGuestOrder) {
                // 상점 주문 기준 조회 정보가 없는 경우 네이버페이 주문으로 조회 시도
                $order = $funcSelectGuestOrder('o.apiOrderNo');
                StringUtils::strIsSet($order['apiOrderNo'], '');
                $hasGuestOrder = $order['apiOrderNo'] == $orderNo;
            }
            $result = [
                'result'  => $hasGuestOrder && ($order['memNo'] == 0),
                'orderNo' => $order['orderNo'],
            ];
            if ($hasGuestOrder && $order['memNo'] > 0) {
                // 상점 주문, 네이버페이 주문 확인 시 주문이 있지만 비회원이 아닌 경우 탈퇴회원 정보 확인
                $this->db->strField = 'COUNT(*) AS cnt';
                $this->db->strWhere = 'memNo = ?';
                $this->db->bind_param_push($bindParam, 'i', $order['memNo']);
                $query = $this->db->query_complete();
                $strSQL = 'SELECT ' . array_shift($query) . ' FROM ' . DB_MEMBER_HACKOUT . ' AS mh ' . implode(' ', $query);
                $resultSet = $this->db->query_fetch($strSQL, $bindParam, false);
                $result['result'] = $resultSet['cnt'] > 0;
            }
        }

        return $result;
    }

    /**
     * isHackOut
     *
     * @param integer $orderNo
     *
     * @return boolean
     * @author Jong-tae Ahn <qnibus@godo.co.kr>
     */
    public function isHackOut($orderNo)
    {
        // 해당 주문의 회원번호 가져오기
        $memNo = $this->getOrderData($orderNo)['memNo'];

        // 탈퇴여부 확인 후 비회원으로 조회할 수 있도록 조건 부여
        $getHackout = $this->db->getData(DB_MEMBER_HACKOUT, $memNo, 'memNo');
        if ($memNo > 0 && empty($getHackout) === false) {
            return true;
        }

        return false;
    }

    /**
     * 주문 정보 확인
     *
     * @param integer $orderNo 주문 번호
     *
     * @return array
     * @throws Exception
     */
    public function getOrderDataInfo($orderNo)
    {
        // 주문 기본 정보
        $arrExclude = [
            'orderIp',
            'orderPGLog',
            'orderDeliveryLog',
            'orderAdminLog',
        ];

        // getOrderData에서 arrWhere와 arrBind를 사용하니 주의해서 확인 필요
        $getData = $this->getOrderData($orderNo, $arrExclude);

        // 주문 정보가 없는 경우
        if (empty($getData)) {
            throw new Exception(__('주문정보가 없습니다.'));
        }

        // 회원 or 비회원 패스워드 체크
        $isNotAccess = false;
        switch (MemberUtil::checkLogin()) {
            // 로그인을 한 경우
            case 'member':
                if ($getData['memNo'] !== Session::get('member.memNo')) {
                    $isNotAccess = true;
                }
                break;

            // 로그인을 하지 않은 경우
            case 'guest':
                // 비회원으로 들어왔을때 네이버 페이인경우에는 검색된 주문번호로 게스트세션 주문번호를 재셋팅처리
                if ($getData['orderChannelFl'] == 'naverpay') {
                    //Session::set('guest.orderNo', $getData['orderNo']);
                }

                // 비회원 패스워드 체크
                if (!Session::has('guest.orderNo') || !Session::has('guest.orderNm')) {
                    // 비회원 로그아웃
                    // MemberUtil::logoutGuest();

                    // 로그인 페이지로 이동
                    header('location:' . URI_HOME . 'member/login.php?returnUrl=' . urlencode(Request::getReferer()));
                    exit();
                }

                // 탈퇴여부 확인 후 비회원이 맞는지 체크
                if ($this->isHackOut($orderNo) === false) {
                    if (intval($getData['memNo']) !== 0) {
                        $isNotAccess = true;
                    }
                }

                // 세션에 저장된 주문자 이름 비교
                if ($getData['orderName'] !== Session::get('guest.orderNm')) {
                    $isNotAccess = true;
                }

                // 세션에 저장된 주문번호 비교
                if ($getData['orderNo'] !== Session::get('guest.orderNo')) {
                    $isNotAccess = true;
                }
                break;

            // 어떤 회원관련 정보도 없는 경우
            default:
                throw new Exception(__('회원정보가 존재하지 않습니다.'));
                break;
        }

        // 접근권한이 없는 경우
        if ($isNotAccess !== false) {
            throw new Exception(__('접근 권한이 없습니다.'));
        }

        // 주문 추가 필드 정보
        $getData['addField'] = $this->getOrderAddFieldView($getData['addField']);

        // 남기실 내용
        $getData['orderMemo'] = nl2br($getData['orderMemo']);

        // 무통장 입금 은행 정보
        $getData['bankAccount'] = explode(STR_DIVISION, $getData['bankAccount']);

        // PG 결과 처리
        $getData['pgSettleNm'] = explode(STR_DIVISION, $getData['pgSettleNm']);
        $getData['pgSettleCd'] = explode(STR_DIVISION, $getData['pgSettleCd']);

        // 주문 상태 처리
        $getData['orderStatus'] = substr($getData['orderStatus'], 0, 1);

        // 결제 방법
        $getData['settleName'] = $this->getSettleKind($getData['settleKind']);
        $getData['settleGateway'] = substr($getData['settleKind'], 0, 1);
        $getData['settleMethod'] = substr($getData['settleKind'], 1, 1);

        // 에스크로여부
        if ($getData['settleGateway'] === 'e') {
            $getData['settleName'] = __('에스크로 ') . $getData['settleName'];
        }

        // 멀티상점 환율 기본 정보
        $getData['currencyPolicy'] = json_decode($getData['currencyPolicy'], true);
        $getData['exchangeRatePolicy'] = json_decode($getData['exchangeRatePolicy'], true);
        $getData['currencyIsoCode'] = $getData['currencyPolicy']['isoCode'];
        $getData['exchangeRate'] = $getData['exchangeRatePolicy']['exchangeRate' . $getData['currencyPolicy']['isoCode']];

        // 영수증 출력 정보 세팅 (PG 거래 영수증 - 현금영수증 제외)
        if ($getData['settleMethod'] == 'c') {
            $getData['settleReceipt'] = 'card';
        } elseif ($getData['settleMethod'] == 'b') {
            $getData['settleReceipt'] = 'bank';
        } elseif ($getData['settleMethod'] == 'v') {
            $getData['settleReceipt'] = 'vbank';
        } elseif ($getData['settleMethod'] == 'h') {
            $getData['settleReceipt'] = 'hphone';
        } else {
            $getData['settleReceipt'] = '';
        }
        $pgCodeConfig = App::getConfig('payment.pg');
        if (empty($getData['settleReceipt']) === false && isset($pgCodeConfig->getPgReceiptUrl()[$getData['pgName']][$getData['settleReceipt']]) === false) {
            $getData['settleReceipt'] = '';
        }

        $getData['absTotalEnuriDcPrice'] = abs($getData['totalEnuriDcPrice']);

        $useMultiShippingKey = false;
        if ($getData['multiShippingFl'] == 'y') {
            $useMultiShippingKey = true;
        }

        // 주문 상품 정보
        $getData['goods'] = $this->getOrderGoodsData($orderNo, null, null, null, 'user', false, false, null, null, false, $useMultiShippingKey);

        return $getData;
    }

    /**
     * 주문 기본 정보 출력
     * 주문 기본 정보, 주문자 정보, 수취인 정보를 출력
     *
     * @param integer $orderNo              주문 번호
     * @param array   $arrOrderExcludeField 주문 테이블의 제외 필드
     * @param array   $arrOrderInculdeField 주문 테이블의 포함 필드
     * @param string $checksumData
     *
     * @return array 주문 기본 정보
     */
    public function getOrderData($orderNo, $arrOrderExcludeField = null, $arrOrderInculdeField = null, $checksumData = false)
    {
        // 주문번호가 없는 경우 false 반환
        if (empty($orderNo) === true) {
            return false;
        }

        // 주문 기본 필드 정보
        if ($arrOrderExcludeField === null) {
            $arrExclude[0] = [];
        } else {
            $arrExclude[0] = $arrOrderExcludeField;
        }
        if ($arrOrderInculdeField === null) {
            $arrInclude[0] = [];
        } else {
            $arrInclude[0] = $arrOrderInculdeField;
        }

        $arrExclude[1] = ['orderNo'];
        $tmpField[0] = DBTableField::setTableField('tableOrder', $arrInclude[0], $arrExclude[0], 'o');
        $tmpField[1] = DBTableField::setTableField('tableOrderInfo', null, $arrExclude[1], 'oi');
        $tmpField[2] = DBTableField::setTableField('tableMall', ['domainFl'], null, 'mm');
        $tmpKey = array_keys($tmpField);
        $arrField = [];
        foreach ($tmpKey as $key) {
            $arrField = array_merge($arrField, $tmpField[$key]);
        }
        unset($tmpField, $tmpKey);

        // 기본 where 문 (네이버페이주문일경우도 고려해 조건절 변경
        $arrWhere[] = 'o.orderNo = ?';
        //$arrWhere[] = '(o.orderNo = ? OR (o.apiOrderNo = ? AND orderChannelFl = \'naverpay\'))';

        // bind 데이타
        $this->db->bind_param_push($arrBind, 's', $orderNo);
        //$this->db->bind_param_push($arrBind, 's', $orderNo);

        if($checksumData) {
            $arrWhere[] = 'o.checksumData = ?';
            $this->db->bind_param_push($arrBind, 's', $checksumData);
        }

        // 쿼리문 생성 및 데이타 호출
        $this->db->strField = implode(', ', $arrField) . ', o.regDt, oi.sno as infoSno ';
        $this->db->strJoin = ' INNER JOIN ' . DB_ORDER_INFO . ' oi ON o.orderNo = oi.orderNo AND oi.orderInfoCd = 1 LEFT JOIN ' . DB_MALL . ' mm ON o.mallSno = mm.sno';
        $this->db->strWhere = implode(' AND ', gd_isset($arrWhere));
        $query = $this->db->query_complete();
        $strSQL = 'SELECT ' . array_shift($query) . ' FROM ' . DB_ORDER . ' o ' . implode(' ', $query);
        $getData = $this->db->query_fetch($strSQL, $arrBind, false);

        if ($getData['checkoutData']) {
            $getData['checkoutData'] = json_decode($getData['checkoutData'], true);
        }
        unset($arrWhere, $arrBind);

        if (count($getData) > 0) {
            // 상품명 태그 제거 (태그 제거 후 상품명 null 인 경우 제거 전으로 처리)
            $orderGoodsNm = StringUtils::stripOnlyTags($getData['orderGoodsNm']);
            if (empty($orderGoodsNm) === false) {
                $getData['orderGoodsNm'] = $orderGoodsNm;
            }

            // memberPolicy 의 경우 img 풀태그가 들어가기에 addslashes 추가 - return 시 strip처리 위해 선처리
            if ($getData['memberPolicy'] != false) {
                $getData['memberPolicy'] = addslashes($getData['memberPolicy']);
            }

            return gd_htmlspecialchars_stripslashes($getData);
        } else {
            return false;
        }
    }

    /**
     * 외부채널주문번호로 주문 기본 정보 출력
     * 주문 기본 정보, 주문자 정보, 수취인 정보를 출력
     *
     * @param integer $orderNo              주문 번호
     * @param array   $arrOrderExcludeField 주문 테이블의 제외 필드
     *
     * @return array 주문 기본 정보
     */
    public function getOrderDataByApiOrderNo($orderNo, $arrOrderExcludeField = null)
    {
        // 주문 기본 필드 정보
        if ($arrOrderExcludeField === null) {
            $arrExclude[0] = [];
        } else {
            $arrExclude[0] = $arrOrderExcludeField;
        }
        $arrExclude[1] = ['orderNo'];
        $tmpField[0] = DBTableField::setTableField('tableOrder', null, $arrExclude[0], 'o');
        $tmpField[1] = DBTableField::setTableField('tableOrderInfo', null, $arrExclude[1], 'oi');
        $tmpKey = array_keys($tmpField);
        $arrField = [];
        foreach ($tmpKey as $key) {
            $arrField = array_merge($arrField, $tmpField[$key]);
        }
        unset($tmpField, $tmpKey);

        // 기본 where 문
        $this->arrWhere[] = 'o.apiOrderNo = ?';

        // bind 데이타
        $this->db->bind_param_push($this->arrBind, 's', $orderNo);

        // 쿼리문 생성 및 데이타 호출
        $this->db->strField = implode(', ', $arrField) . ', o.regDt, oi.sno as infoSno ';
        $this->db->strJoin = ' INNER JOIN ' . DB_ORDER_INFO . ' oi ON o.orderNo = oi.orderNo AND oi.orderInfoCd = 1 ';
        $this->db->strWhere = implode(' AND ', gd_isset($this->arrWhere));
        $query = $this->db->query_complete();
        $strSQL = 'SELECT ' . array_shift($query) . ' FROM ' . DB_ORDER . ' o ' . implode(' ', $query);
        $getData = $this->db->query_fetch($strSQL, $this->arrBind, false);
        unset($this->arrWhere, $this->arrBind);

        if (count($getData) > 0) {
            return gd_htmlspecialchars_stripslashes($getData);
        } else {
            return false;
        }
    }

    /**
     * 주문 기본 정보 - SMS 및 메일 전송용
     *
     * @param integer $orderNo 주문 번호
     *
     * @return array 주문 기본 정보
     * @throws Exception
     * @internal param array $orderGoodsData 주문 상품 정보 (0 => [goodsNo, orderCd] 값을 배열로, goodsNo 는 필수)
     */
    public function getOrderDataSend($orderNo)
    {
        // 주문 기본 정보
        $arrExclude = [
            'orderIp',
            'orderPGLog',
            'orderDeliveryLog',
            'orderAdminLog',
        ];
        $getData = $this->getOrderData($orderNo, $arrExclude);

        // 주문 정보가 없는 경우
        if (empty($getData)) {
            throw new Exception(self::TEXT_NOT_EXIST_ORDER_INFO);
        }

        // 남기실 내용
        $getData['orderMemo'] = nl2br($getData['orderMemo']);

        // 무통장 입금 은행 정보
        if (empty($getData['bankAccount']) === false) {
            $getData['bankAccount'] = explode(STR_DIVISION, $getData['bankAccount']);
        }

        // PG 결과 처리
        $getData['pgSettleNm'] = explode(STR_DIVISION, $getData['pgSettleNm']);
        $getData['pgSettleCd'] = explode(STR_DIVISION, $getData['pgSettleCd']);

        // 주문 상태 처리
        $getData['orderStatusOrigin'] = $getData['orderStatus'];
        $getData['orderStatus'] = substr($getData['orderStatus'], 0, 1);


        // 결제 방법
        $getData['settleName'] = $this->getSettleKind($getData['settleKind']);
        $getData['settleGateway'] = substr($getData['settleKind'], 0, 1);
        $getData['settleMethod'] = substr($getData['settleKind'], 1, 1);

        return $getData;
    }

    /**
     * 주문 정보 출력 (주문자 정보, 수취인 정보)
     *
     * @param integer $orderNo 주문 번호
     * @param boolean $arrFl 배열 여부
     * @param string $orderByField 정렬필드
     * @param array $orderInfoSnoArr order info sno
     *
     * @return array 해당 주문의 주문자/수취인 정보
     */
    public function getOrderInfo($orderNo, $arrFl = true, $orderByField = null, $orderInfoSnoArr=[])
    {
        $orderBy = ' ORDER BY sno ASC ';
        $arrField = DBTableField::setTableField('tableOrderInfo');
        $arrBind = [
            's',
            $orderNo,
        ];
        if($orderByField !== null){
            $orderBy = ' ORDER BY ' . $orderByField;
        }
        $where[] = 'orderNo = ?';
        if(count($orderInfoSnoArr) > 0){
            $where[] = 'sno IN ('.implode(", ", $orderInfoSnoArr).')';
        }
        $strSQL = 'SELECT sno, ' . str_replace('orderNo, ', '', implode(', ', $arrField)) . ' FROM ' . DB_ORDER_INFO . ' WHERE ' . implode(" AND ", $where) . $orderBy;

        $getData = $this->db->query_fetch($strSQL, $arrBind, $arrFl);
        if (count($getData) > 0) {
            return gd_htmlspecialchars_stripslashes($getData);
        } else {
            return false;
        }
    }

    /**
     * 주문 상품 데이터
     * 상태를 변경할 때의 비교데이터로 주로 활용된다.
     *
     * @param integer $orderNo         주문번호
     * @param mixed   $orderGoodsNo    주문상품번호
     * @param integer $handleSno       취소테이블번호
     * @param array   $arrInclude      주문상품의 포함 필드
     * @param array   $arrExclude      주문상품의 제외 필드
     * @param null    $orderArrInclude 주문데이터 필드(es_order)
     *
     * @return mixed 상품 배열
     */
    public function getOrderGoods($orderNo = null, $orderGoodsNo = null, $handleSno = null, $arrInclude = null, $arrExclude = ['orderNo'], $orderArrInclude = null)
    {
        // 출력 필드
        $arrField = DBTableField::setTableField('tableOrderGoods', $arrInclude, $arrExclude, 'og');
        if ($orderArrInclude) {
            $orderArrField = DBTableField::setTableField('tableOrder', $orderArrInclude, null, 'o');
        }
        // 바인드 초기화
        $arrBind = [];
        $arrWhere = [];

        // 조건절
        if ($orderNo !== null) {
            $arrWhere[] = 'og.orderNo = ?';
            $this->db->bind_param_push($arrBind, 's', $orderNo);
        }

        if ($orderGoodsNo !== null) {
            if (is_array($orderGoodsNo)) {
                foreach ($orderGoodsNo as $sno) {
                    $this->db->bind_param_push($arrBind, 'i', $sno);
                    $arrBindParam[] = '?';
                }
                $arrWhere[] = 'og.sno IN (' . implode(',', $arrBindParam) . ')';
            } else {
                $arrWhere[] = 'og.sno = ? ';
                $this->db->bind_param_push($arrBind, 'i', $orderGoodsNo);
            }
        }

        // 교환/반품/환불 내역이 있는 경우
        if ($handleSno !== null) {
            if (is_array($handleSno) === true) {
                $bindQuery = null;
                foreach($handleSno as $val){
                    $bindQuery[] = '?';
                    $this->db->bind_param_push($arrBind, 'i', $val);
                }
                $arrWhere[] = 'og.handleSno IN (' . @implode(',', $bindQuery) . ')';
            } else {
                $arrWhere[] = 'og.handleSno = ?';
                $this->db->bind_param_push($arrBind, 'i', $handleSno);
            }
        }

        // 쿼리 구성
        $this->db->strField = 'og.sno,og.regDt as orderGoodsRegDt, og.deliveryMethodFl, ' . implode(', ', $arrField);
        if ($orderArrField) {
            $this->db->strField .= ',' . implode(', ', $orderArrField);
            $join[] = 'LEFT OUTER JOIN ' . DB_ORDER . " as o ON og.orderNo = o.orderNo ";
        }
        // join 배열이 있는 경우만 처리
        if (count($join) > 0) {
            $this->db->strJoin = implode(' ', $join);
        }
        $this->db->strWhere = implode(' AND ', $arrWhere);
        $this->db->strOrder = 'og.sno asc';
        $query = $this->db->query_complete();
        $strSQL = 'SELECT ' . array_shift($query) . ' FROM ' . DB_ORDER_GOODS . ' as og ' . implode(' ', $query);
        $getData = $this->db->query_fetch($strSQL, $arrBind);

        // 데이타 출력
        if (count($getData) > 0) {
            // json형태의 경우 json값안에 "이있는경우 stripslashes처리가 되어 json_decode에러가 나므로 json값중 "이 들어갈수있는경우 $aCheckKey에 해당 필드명을 추가해서 처리해주세요
            $aCheckKey = ['optionTextInfo'];
            foreach ($getData as $k => $v) {
                foreach ($v as $k2 => $v2) {
                    if (!in_array($k2, $aCheckKey)) {
                        $getData[$k][$k2] = gd_htmlspecialchars_stripslashes($v2);
                    }
                }
            }
            return $getData;
        } else {
            return false;
        }
    }

    /**
     * 주문 상품 데이터 (상품번호로 검색)
     * 상태를 변경할 때의 비교데이터로 주로 활용된다.
     *
     * @param integer $orderNo    주문번호
     * @param integer $goodsNo    상품번호
     * @param integer $handleSno  취소테이블번호
     * @param array   $arrInclude 주문상품의 포함 필드
     * @param array   $arrExclude 주문상품의 제외 필드
     * @param null    $memNo
     *
     * @return mixed 상품 배열
     * @internal param mixed $orderGoodsNo 주문상품번호
     */
    public function getOrderGoodsByGoodsNo($orderNo = null, $goodsNo, $handleSno = null, $arrInclude = null, $arrExclude = ['orderNo'], $memNo = null, $orderStatus = null)
    {
        // 출력 필드
        $arrField = DBTableField::setTableField('tableOrderGoods', $arrInclude, $arrExclude, 'og');

        // 바인드 초기화
        $arrBind = [];
        $arrWhere = [];

        // 조건절
        if ($orderNo) {
            $arrWhere[] = 'og.orderNo = ?';
            $this->db->bind_param_push($arrBind, 's', $orderNo);
        }

        $arrWhere[] = 'og.goodsNo = ?';
        $this->db->bind_param_push($arrBind, 'i', $goodsNo);

        if ($handleSno !== null) {
            $arrWhere[] = 'og.handleSno = ?';
            $this->db->bind_param_push($arrBind, 's', $handleSno);
        }

        if ($memNo !== null) {
            $arrWhere[] = 'o.memNo = ?';
            $this->db->bind_param_push($arrBind, 'i', $memNo);
            $orderJoin = true;
        }

        if ($orderStatus !== null) {
            $arrWhere[] = 'og.orderStatus = ?';
            $this->db->bind_param_push($arrBind, 's', $orderStatus);
        }

        // 쿼리 구성
        $this->db->strField = 'og.sno, ' . implode(', ', $arrField);
        $this->db->strWhere = implode(' AND ', $arrWhere);
        if ($orderJoin) {
            $this->db->strJoin = " LEFT JOIN " . DB_ORDER . " as o ON og.orderNo = o.orderNo ";;
        }
        $this->db->strOrder = 'og.sno asc';
        $query = $this->db->query_complete();
        $strSQL = 'SELECT ' . array_shift($query) . ' FROM ' . DB_ORDER_GOODS . ' og ' . implode(' ', $query);
        $getData = $this->db->query_fetch($strSQL, $arrBind);

        // 데이타 출력
        if (count($getData) > 0) {
            return gd_htmlspecialchars_stripslashes($getData);
        } else {
            return false;
        }
    }

    /**
     * 주문 상품 데이터
     * 외부품목번호로 주문상품 데이터를 가져올때 사용.
     *
     * @param integer $orderNo         주문번호
     * @param mixed   $apiOrderGodosNo 외부품목번호로
     * @param integer $handleSno       취소테이블번호
     * @param array   $arrInclude      주문상품의 포함 필드
     * @param array   $arrExclude      주문상품의 제외 필드
     *
     * @return mixed 상품 배열
     */
    public function getOrderGoodsByApiOrderGoodsNo($orderNo = null, $apiOrderGodosNo = null, $handleSno = null, $arrInclude = null, $arrExclude = ['orderNo'])
    {
        // 출력 필드
        $arrField = DBTableField::setTableField('tableOrderGoods', $arrInclude, $arrExclude);

        // 바인드 초기화
        $arrBind = [];
        $arrWhere = [];

        // 조건절
        if ($orderNo) {
            $arrWhere[] = 'og.orderNo = ?';
            $this->db->bind_param_push($arrBind, 's', $orderNo);
        }

        $arrWhere[] = 'og.apiOrderGoodsNo = \'' . $apiOrderGodosNo . '\'';

        if ($handleSno !== null) {
            $arrWhere[] = 'og.handleSno = ?';
            $this->db->bind_param_push($arrBind, 's', $handleSno);
        }

        // 쿼리 구성
        $this->db->strField = 'og.apiOrderGoodsNo, ' . implode(', ', $arrField);
        $this->db->strWhere = implode(' AND ', $arrWhere);
        $this->db->strOrder = 'sno asc';
        $query = $this->db->query_complete();
        $strSQL = 'SELECT ' . array_shift($query) . ' FROM ' . DB_ORDER_GOODS . ' og ' . implode(' ', $query);
        $getData = $this->db->query_fetch($strSQL, $arrBind);

        // 데이타 출력
        if (count($getData) > 0) {
            return gd_htmlspecialchars_stripslashes($getData);
        } else {
            return false;
        }
    }

    /**
     * 해당 주문번호 상품상세내역 출력
     * 추가상품 및 옵션정보등 모든 정보를 배열형태로 담는다.
     * 간단하게 상품정보만 가져와서 사용할 것이라면 self::getOrderGoods() 를 사용하면 된다.
     *
     * @param integer $orderNo       주문 번호
     * @param mixed   $orderGoodsNo  특정 주문상품만 출력
     * @param string  $handleSno     특정 취소코드만 출력
     * @param string  $userHandleSno 특정 반품/교환/환불코드만 출력
     * @param string  $status        상태값 (admin, user, null)
     * @param boolean $scmFl         scm별 출력 여부 (기본 true)
     * @param boolean $stockFl       재고 출력 여부 (기본 false)
     * @param string  $statusMode    반품/교환/환불 상태 모드 값 (r, b, e)
     * @param array   $excludeStatus 제외할 주문상태 값
     * @param boolean $mailFl 메일발송데이터 여부
     * @param boolean $useMultiShippingKey 데이터 배열의 key를 복수배송지의 키 (order info sno)로 사용할 것인지에 대한 flat값
     *
     * @return array 해당 주문 상품 정보
     */
    public function getOrderGoodsData($orderNo, $orderGoodsNo = null, $handleSno = null, $userHandleSno = null, $status = null, $scmFl = true, $stockFl = false, $statusMode = null, $excludeStatus = null, $mailFl = false, $useMultiShippingKey = false)
    {
        $orderData = $this->getOrderData($orderNo);
        $arrExclude = [];
        $arrIncludeOg = [
            'apiOrderGoodsNo',
            'mallSno',
            'orderStatus',
            'invoiceCompanySno',
            'invoiceNo',
            'orderCd',
            'orderGroupCd',
            'userHandleSno',
            'handleSno',
            'orderDeliverySno',
            'goodsType',
            'parentMustFl',
            'parentGoodsNo',
            'goodsNo',
            'goodsCd',
            'goodsNm',
            'goodsNmStandard',
            'goodsCnt',
            'goodsPrice',
            'costPrice',
            'taxSupplyGoodsPrice',
            'taxVatGoodsPrice',
            'taxFreeGoodsPrice',
            'realTaxSupplyGoodsPrice',
            'realTaxVatGoodsPrice',
            'realTaxFreeGoodsPrice',
            'divisionUseDeposit',
            'divisionUseMileage',
            'divisionGoodsDeliveryUseDeposit',
            'divisionGoodsDeliveryUseMileage',
            'divisionCouponOrderDcPrice',
            'divisionCouponOrderMileage',
            'addGoodsPrice',
            'optionPrice',
            'optionCostPrice',
            'optionTextPrice',
            'goodsDcPrice',
            'memberDcPrice',
            'memberOverlapDcPrice',
            'couponGoodsDcPrice',
            'goodsDeliveryCollectPrice',
            'goodsMileage',
            'memberMileage',
            'couponGoodsMileage',
            'goodsDeliveryCollectFl',
            'minusDepositFl',
            'minusRestoreDepositFl',
            'minusMileageFl',
            'minusRestoreMileageFl',
            'plusMileageFl',
            'plusRestoreMileageFl',
            'couponMileageFl',
            'optionSno',
            'optionInfo',
            'optionTextInfo',
            'goodsTaxInfo',
            'checkoutData',
            'timeSaleFl',
            'statisticsOrderFl',
            'statisticsGoodsFl',
            'deliveryMethodFl',
            'deliveryDt',
            'paymentDt',
            'deliveryCompleteDt',
            'finishDt',
            'taxVatGoodsPrice',
            'hscode',
            'brandCd',
            'goodsModelNo',
            'cancelDt',
            'goodsTaxInfo',
            'makerNm',
            'deliveryCompleteDt',
            'commission',
            'enuri',
            'goodsDiscountInfo',
            'goodsMileageAddInfo',
            'visitAddress',
        ];
        $arrIncludeG = [
            'imagePath',
            'imageStorage',
            'stockFl',
            'goodsSellFl',
            'goodsSellMobileFl',
        ];
        $arrIncludeGi = [
            'imageSize',
            'imageName',
            'imageRealSize',
        ];
        $arrIncludeSm = [
            'scmNo',
            'companyNm',
        ];

        $arrIncludeOh = [
            'beforeStatus',
            'handleMode',
            'handleCompleteFl',
            'handleReason',
            'handleDetailReason',
            'handleDetailReasonShowFl',
            'handleDt',
            'refundGroupCd',
            'refundMethod',
            'refundBankName',
            'refundAccountNumber',
            'refundDepositor',
            'refundPrice',
            'refundUseDeposit',
            'refundUseMileage',
            'refundDeliveryUseDeposit',
            'refundDeliveryUseMileage',
            'refundDeliveryCharge',
            'refundGiveMileage',
            'refundCharge',
            'refundUseDepositCommission',
            'refundUseMileageCommission',
            'refundDeliveryInsuranceFee',
            'completeCashPrice',
            'completePgPrice',
            'completeDepositPrice',
            'completeMileagePrice',
            'refundDeliveryCoupon',
            'handleGroupCd',
        ];
        $arrIncludeOuh = [
            'sno',
            'userHandleMode',
            'userHandleFl',
            'userHandleGoodsNo',
            'userHandleGoodsCnt',
            'userRefundMethod',
            'userRefundBankName',
            'userRefundAccountNumber',
            'userRefundDepositor',
            'userHandleReason',
            'userHandleDetailReason',
            'adminHandleReason',
        ];
        $arrIncludeOd = [
            'deliverySno',
            'deliveryCharge',
            'taxSupplyDeliveryCharge',
            'taxVatDeliveryCharge',
            'taxFreeDeliveryCharge',
            'realTaxSupplyDeliveryCharge',
            'realTaxVatDeliveryCharge',
            'realTaxFreeDeliveryCharge',
            'deliveryPolicyCharge',
            'deliveryAreaCharge',
            'divisionDeliveryUseDeposit',
            'divisionDeliveryUseMileage',
            'divisionDeliveryCharge',
            'divisionMemberDeliveryDcPrice',
            'deliveryInsuranceFee',
            'deliveryMethod',
            'deliveryWeightInfo',
            'deliveryTaxInfo',
            'goodsDeliveryFl',
            'orderInfoSno',
            'deliveryPolicy',
        ];
        $arrIncludeM = [
            'managerId',
            'managerNm',
        ];
        $arrIncludeO = [
            'memNo',
            'orderChannelFl',
            'apiOrderNo',
            'mileageGiveExclude',
            'totalMemberDeliveryDcPrice',
            'multiShippingFl',
        ];

        // 마이앱 사용에 따른 분기 처리
        if ($this->useMyapp) {
            array_push($arrIncludeOg, 'myappDcPrice');
        }

        $tmpField[] = DBTableField::setTableField('tableOrderGoods', $arrIncludeOg, $arrExclude, 'og');
        $tmpField[] = DBTableField::setTableField('tableOrderDelivery', $arrIncludeOd, ['scmNo'], 'od');
        $tmpField[] = DBTableField::setTableField('tableOrderHandle', $arrIncludeOh, null, 'oh');
        $tmpField[] = DBTableField::setTableField('tableOrderUserHandle', $arrIncludeOuh, null, 'ouh');
        $tmpField[] = DBTableField::setTableField('tableGoods', $arrIncludeG, null, 'g');
        $tmpField[] = DBTableField::setTableField('tableGoodsImage', $arrIncludeGi, null, 'gi');
        $tmpField[] = DBTableField::setTableField('tableScmManage', $arrIncludeSm, null, 'sm');

        $tmpField[] = DBTableField::setTableField('tableManager', $arrIncludeM, null, 'm');
        $tmpField[] = DBTableField::setTableField('tableOrder', $arrIncludeO, null, 'o');

        if(gd_is_plus_shop(PLUSSHOP_CODE_PURCHASE) === true && gd_is_provider() === false) {
            $arrIncludePu = [
                'purchaseNo',
                'purchaseNm',
            ];
            $tmpField[] = DBTableField::setTableField('tablePurchase', $arrIncludePu, null, 'pu');
        }

        //복수배송지 사용시
        if($useMultiShippingKey === true){
            $arrIncludeOi = [
                'receiverName',
                'receiverZonecode',
                'receiverZipcode',
                'receiverAddress',
                'receiverAddressSub',
                'receiverPhone',
                'receiverCellPhone',
                'orderInfoCd',
            ];
            $tmpField[] = DBTableField::setTableField('tableOrderInfo', $arrIncludeOi, null, 'oi');
            $tmpField[] = ['oi.sno AS orderInfoSno'];
            $this->orderGoodsOrderBy = 'od.orderInfoSno asc, og.regDt desc, og.scmNo asc, og.orderCd asc';
        }

        $tmpKey = array_keys($tmpField);
        $arrField = [];
        foreach ($tmpKey as $key) {
            $arrField = array_merge($arrField, $tmpField[$key]);
        }
        unset($tmpField, $tmpKey);

        // where 절
        $arrWhere[] = 'og.orderNo = ?';
        $this->db->bind_param_push($arrBind, 's', $orderNo);

        if ($statusMode !== null) {
            $arrWhere[] = 'LEFT(og.orderStatus, 1) = ? ';
            $this->db->bind_param_push($arrBind, 's', $statusMode);
        }
        if ($excludeStatus !== null && is_array($excludeStatus)) {
            foreach($excludeStatus as $val){
                $bindQuery[] = '?';
                $this->db->bind_param_push($arrBind, 's', $val);
            }
            $arrWhere[] = 'og.orderStatus NOT IN (' . implode(',', $bindQuery) . ')';
        }
        if ($handleSno !== null) {
            $arrWhere[] = 'og.handleSno = ? ';
            $this->db->bind_param_push($arrBind, 'i', $handleSno);
        }
        if ($userHandleSno !== null) {
            $arrWhere[] = 'ouh.sno = ? ';
            $this->db->bind_param_push($arrBind, 'i', $userHandleSno);
        }
        if ($orderGoodsNo !== null) {
            $arrBindParam = null;
            if (is_array($orderGoodsNo)) {
                foreach ($orderGoodsNo as $sno) {
                    $this->db->bind_param_push($arrBind, 'i', $sno);
                    $arrBindParam[] = '?';
                }
                $arrWhere[] = 'og.sno IN (' . implode(',', $arrBindParam) . ')';
            } else {
                $arrWhere[] = 'og.sno = ? ';
                $this->db->bind_param_push($arrBind, 'i', $orderGoodsNo);
            }
        }

        // join 문
        $join[] = ' LEFT JOIN ' . DB_GOODS . ' g ON og.goodsNo = g.goodsNo ';
        $join[] = ' LEFT JOIN ' . DB_GOODS_IMAGE . ' gi ON og.goodsNo = gi.goodsNo AND gi.imageKind = \'list\' ';
        $join[] = ' LEFT JOIN ' . DB_ADD_GOODS . ' ag ON og.goodsNo = ag.addGoodsNo ';
        $join[] = ' LEFT JOIN ' . DB_ORDER_DELIVERY . ' od ON og.orderDeliverySno = od.sno ';
        $join[] = ' LEFT JOIN ' . DB_ORDER_HANDLE . ' oh ON og.orderNo = oh.orderNo AND og.handleSno = oh.sno ';
        if ($userHandleSno !== null) {
            $join[] = ' LEFT JOIN ' . DB_ORDER_USER_HANDLE . ' ouh ON og.orderNo = ouh.orderNo AND (ouh.sno = og.userHandleSno || ouh.userHandleGoodsNo = og.sno) ';
        } else {
            $join[] = ' LEFT JOIN ' . DB_ORDER_USER_HANDLE . ' ouh ON og.orderNo = ouh.orderNo AND og.userHandleSno = ouh.sno ';
        }
        $join[] = ' LEFT JOIN ' . DB_SCM_MANAGE . ' sm ON og.scmNo = sm.scmNo ';
        if(gd_is_plus_shop(PLUSSHOP_CODE_PURCHASE) === true && gd_is_provider() === false) {
            $join[] = ' LEFT JOIN ' . DB_PURCHASE . ' pu ON og.purchaseNo = pu.purchaseNo ';
        }
        //복수배송지 사용시
        if($useMultiShippingKey === true){
            $join[] = ' LEFT JOIN ' . DB_ORDER_INFO . ' oi ON (og.orderNo = oi.orderNo)  
                        AND (CASE WHEN od.orderInfoSno > 0 THEN od.orderInfoSno = oi.sno ELSE oi.orderInfoCd = 1 END) ';
        }
        else {
            $join[] = ' LEFT JOIN ' . DB_ORDER_INFO . ' oi ON og.orderNo = oi.orderNo AND oi.orderInfoCd = 1 ';
        }
        $join[] = ' LEFT JOIN ' . DB_MANAGER . ' m ON ouh.managerNo = m.sno ';
        $join[] = ' LEFT JOIN ' . DB_ORDER . ' o ON og.orderNo = o.orderNo ';

        $this->db->strJoin = implode('', $join);
        $this->db->strWhere = implode(' AND ', $arrWhere);
        if($useMultiShippingKey === true){
            $this->db->strOrder = $this->orderGoodsMultiShippingOrderBy;
        }
        else {
            $this->db->strOrder = $this->orderGoodsOrderBy;
        }
        $this->db->strField = 'o.regDt, og.sno, oh.regDt AS handleRegDt, ouh.regDt AS userHandleRegDt, og.modDt, ' . implode(', ', $arrField);

        // addGoods 필드 변경 처리 (goods와 동일해서)
        $this->db->strField .= ', ag.imagePath AS addImagePath, ag.imageStorage AS addImageStorage, ag.imageNm AS addImageName, ag.stockUseFl AS addStockFl, ag.stockCnt AS addStockCnt';

        $query = $this->db->query_complete();
        $strSQL = 'SELECT ' . array_shift($query) . ' FROM ' . DB_ORDER_GOODS . ' og ' . implode(' ', $query);
        $getData = $this->db->query_fetch($strSQL, $arrBind);

        // !중요! 해외상점인 경우 배열을 배송비가 높은 우선 순으로 재정렬 처리 (이로 인해 원하는 정렬로 나오지 않을 수는 있슴)
        // 본 처리를 무시하면 UI에서 배송비가 0원으로 나오는 케이스가 발생할 수 있슴
        if ($getData[0]['mallSno'] > DEFAULT_MALL_NUMBER) {
            $tmpData = [];
            foreach ($getData as $key => $val) {
                if ($val['deliveryCharge'] > 0) {
                    array_unshift($tmpData, $val);
                } else {
                    array_push($tmpData, $val);
                }
            }
            $getData = $tmpData;
            unset($tmpData);
        }

        // 데이타 출력 + 데이타 갱신
        if (count($getData) > 0) {
            // 배송비 중복 계산 방지용 변수 (동일조건을 한번만 더함)
            $orderDeliverySno = 0;
            if ($orderData['orderChannelFl'] == 'naverpay') {
                $naverPay = new NaverPay();
            }

            if(!is_object($delivery)){
                $delivery = \App::load('\\Component\\Delivery\\Delivery');
            }
            $delivery->setDeliveryMethodCompanySno();
            $orderInfoSno = $orderInfoKey = '';
            foreach ($getData as $key => $val) {
                if($useMultiShippingKey === true || $val['multiShippingFl'] == 'y') {
                    if ($val['multiShippingFl'] == 'y') {
                        if (empty($orderInfoSno) === true || $orderInfoSno != $val['orderInfoSno']) {
                            $orderInfoSno = $val['orderInfoSno'];
                            $orderInfoKey = $key;
                            $getData[$orderInfoKey]['orderInfoRow'] = 1;
                        } else {
                            $getData[$orderInfoKey]['orderInfoRow'] += 1;
                        }
                    } else {
                        if ($key == 0) {
                            $getData[0]['orderInfoRow'] = 1;
                        } else {
                            $getData[0]['orderInfoRow'] += 1;
                        }
                    }
                    if ($val['orderInfoCd'] > 1) {
                        $getData[$key]['orderInfoTit'] = '추가배송지' . ($val['orderInfoCd'] - 1);
                    } else {
                        $getData[$key]['orderInfoTit'] = '메인배송지';
                    }
                }
                if (gd_str_length($getData[$key]['refundAccountNumber']) > 50) {
                    $getData[$key]['refundAccountNumber'] = \Encryptor::decrypt($getData[$key]['refundAccountNumber']);
                }
                if (gd_str_length($getData[$key]['userRefundAccountNumber']) > 50) {
                    $getData[$key]['userRefundAccountNumber'] = \Encryptor::decrypt($getData[$key]['userRefundAccountNumber']);
                }

                // 태그제거
                $getData[$key]['goodsNm'] = StringUtils::stripOnlyTags($getData[$key]['goodsNm']);
                // 주문상태 텍스트로 변경
                $getData[$key]['orderStatusStr'] = $this->_getOrderStatus($val['orderStatus'], ($status !== null ? $status : 'user'));

                if ($val['orderChannelFl'] == 'naverpay') {
                    $checkoutData = json_decode($val['checkoutData'], true);
                    $getData[$key]['checkoutData'] = $checkoutData;
                    //TODO:네이버페이상태체크
                    //발송지연
                    $naverpayStatus = $naverPay->getStatus($checkoutData,$val['handleSno']);
                    foreach($checkoutData as $nval){
                        if($nval['RequestChannel']){
                            $naverpayStatus['requestChannel'] = $nval['RequestChannel'];
                            break;
                        }
                    }

                    $getData[$key]['naverpayStatus'] = $naverpayStatus;
                }

                if (isset($getData[$key]['beforeStatus'])) {
                    $getData[$key]['beforeStatusStr'] = $this->getOrderStatusAdmin($getData[$key]['beforeStatus']);
                }

                // 반품/교환/환불신청 상태
                if ($val['userHandleSno']) {
                    $getData[$key]['userHandleFlStr'] = $this->getUserHandleMode($val['userHandleMode'], $val['userHandleFl']);
                }

                // 옵션 처리
                $getData[$key]['optionInfo'] = [];
                if (empty($val['optionInfo']) === false) {
                    $option = json_decode(gd_htmlspecialchars_stripslashes($val['optionInfo']), true);
                    if (empty($option) === false) {
                        foreach ($option as $oKey => $oVal) {
                            $getData[$key]['optionInfo'][$oKey]['optionName'] = $oVal[0];
                            $getData[$key]['optionInfo'][$oKey]['optionValue'] = $oVal[1];
                            $getData[$key]['optionInfo'][$oKey]['optionCode'] = $oVal[2];
                            $getData[$key]['optionInfo'][$oKey]['optionRealPrice'] = $oVal[3];
                            $getData[$key]['optionInfo'][$oKey]['deliveryInfoStr'] = $oVal[4];
                        }
                        unset($option);
                    }
                }

                // 텍스트 옵션 처리
                if (empty($val['optionTextInfo']) === false) {
                    $option = json_decode($val['optionTextInfo'], true);
                    unset($getData[$key]['optionTextInfo']);
                    if (empty($option) === false) {
                        foreach ($option as $oKey => $oVal) {
                            $getData[$key]['optionTextInfo'][$oKey]['optionName'] = gd_htmlspecialchars_stripslashes($oVal[0]);
                            $getData[$key]['optionTextInfo'][$oKey]['optionValue'] = gd_htmlspecialchars_stripslashes($oVal[1]);
                            $getData[$key]['optionTextInfo'][$oKey]['optionTextPrice'] = gd_htmlspecialchars_stripslashes($oVal[2]);
                        }
                    }
                    unset($option);
                }

                // 사은품 처리
                $getData[$key]['gift'] = $this->getOrderGift($orderNo, $oVal['scmNo'], 40);

                // 추가상품 처리
                $getData[$key]['addGoods'] = $this->getOrderAddGoods(
                    $orderNo,
                    $val['orderCd'],
                    [
                        'sno',
                        'addGoodsNo',
                        'goodsNm',
                        'goodsCnt',
                        'goodsPrice',
                        'stockUseFl',
                        'stockCnt',
                        'addMemberDcPrice',
                        'addMemberOverlapDcPrice',
                        'addCouponGoodsDcPrice',
                        'addGoodsMileage',
                        'addMemberMileage',
                        'addCouponGoodsMileage',
                        'taxSupplyAddGoodsPrice',
                        'taxVatAddGoodsPrice',
                        'taxFreeAddGoodsPrice',
                        'realTaxSupplyAddGoodsPrice',
                        'realTaxVatAddGoodsPrice',
                        'realTaxFreeAddGoodsPrice',
                        'goodsTaxInfo',
                        'divisionAddUseDeposit',
                        'divisionAddUseMileage',
                        'divisionAddCouponOrderDcPrice',
                        'divisionAddCouponOrderMileage',
                        'optionNm',
                        'goodsImage',
                    ]
                );

                // 추가상품 수량 (테이블 UI 처리에 필요)
                if (!isset($getData[$key]['addGoodsCnt'])) {
                    $getData[$key]['addGoodsCnt'] = empty($getData[$key]['addGoods']) ? 0 : count($getData[$key]['addGoods']);
                }

                // 상품 이미지 처리
                if ($getData[$key]['goodsType'] === 'addGoods') {
                    $getData[$key]['goodsImage'] = gd_html_add_goods_image($val['goodsNo'], $val['addImageName'], $val['addImagePath'], $val['addImageStorage'], 50, $val['goodsNm'], '_blank');
                } else {
                    $getData[$key]['goodsImage'] = gd_html_preview_image($val['imageName'], $val['imagePath'], $val['imageStorage'], 50, 'goods', $val['goodsNm'], null, false, false);
                }

                // 세금정보 처리
                $getData[$key]['goodsTaxInfo'] = explode(STR_DIVISION, $val['goodsTaxInfo']);

                // 재고 출력
                if ($stockFl === true) {
                    if ($getData[$key]['goodsType'] === 'addGoods') {
                        if ($val['addStockFl'] == 1) {
                            $getData[$key]['stockCnt'] = number_format($val['addStockCnt']);
                        } else {
                            $getData[$key]['stockCnt'] = '∞';
                        }
                    } else {
                        // 유한 재고 인 경우
                        if ($val['stockFl'] == 'y') {
                            $getData[$key]['stockCnt'] = number_format($this->getOrderGoodsStock($val['goodsNo'], $val['optionInfo']));
                            // 무한 재고 인 경우
                        } else {
                            $getData[$key]['stockCnt'] = '∞';
                        }
                    }
                }

                // 주문상품 할인/적립 안분 금액을 포함한 총 금액 (최종 상품별 적립/할인 금액 + 추가상품별 적립/할인 금액)
                $getData[$key]['totalMemberDcPrice'] = $val['memberDcPrice'];
                $getData[$key]['totalMemberOverlapDcPrice'] = $val['memberOverlapDcPrice'];
                $getData[$key]['totalCouponGoodsDcPrice'] = $val['couponGoodsDcPrice'];
                // 마이앱 사용에 따른 분기 처리
                if ($this->useMyapp) {
                    $getData[$key]['totalMyappDcPrice'] = $val['myappDcPrice'];
                }
                $getData[$key]['totalGoodsMileage'] = $val['goodsMileage'];
                $getData[$key]['totalMemberMileage'] = $val['memberMileage'];
                $getData[$key]['totalCouponGoodsMileage'] = $val['couponGoodsMileage'];
                $getData[$key]['totalDivisionCouponOrderDcPrice'] = $val['divisionCouponOrderDcPrice'];
                $getData[$key]['totalDivisionCouponOrderMileage'] = $val['divisionCouponOrderMileage'];
                $getData[$key]['totalDivisionUseDeposit'] = $val['divisionUseDeposit'];
                $getData[$key]['totalDivisionUseMileage'] = $val['divisionUseMileage'];

                // 실제 적립된 마일리지만 산출
                if ($val['plusMileageFl'] == 'y') {
                    $getData[$key]['totalRealGoodsMileage'] = $val['goodsMileage'];
                    $getData[$key]['totalRealMemberMileage'] = $val['memberMileage'];
                    $getData[$key]['totalRealCouponGoodsMileage'] = $val['couponGoodsMileage'];
                    $getData[$key]['totalRealDivisionCouponOrderMileage'] = $val['divisionCouponOrderMileage'];
                }

                if (!empty($getData[$key]['addGoods'])) {
                    foreach ($getData[$key]['addGoods'] as $aVal) {
                        $getData[$key]['totalMemberDcPrice'] += $aVal['addMemberDcPrice'];
                        $getData[$key]['totalMemberOverlapDcPrice'] += $aVal['addMemberOverlapDcPrice'];
                        $getData[$key]['totalCouponGoodsDcPrice'] += $aVal['addCouponGoodsDcPrice'];
                        $getData[$key]['totalGoodsMileage'] += $aVal['addGoodsMileage'];
                        $getData[$key]['totalMemberMileage'] += $aVal['addMemberMileage'];
                        $getData[$key]['totalCouponGoodsMileage'] += $aVal['addCouponGoodsMileage'];
                        $getData[$key]['totalDivisionUseDeposit'] += $aVal['divisionAddUseDeposit'];
                        $getData[$key]['totalDivisionUseMileage'] += $aVal['divisionAddUseMileage'];
                        $getData[$key]['totalDivisionCouponOrderDcPrice'] += $aVal['divisionAddCouponOrderDcPrice'];
                        $getData[$key]['totalDivisionCouponOrderMileage'] += $aVal['divisionAddCouponOrderMileage'];

                        if ($val['plusMileageFl'] == 'y') {
                            $getData[$key]['totalRealGoodsMileage'] += $aVal['addGoodsMileage'];
                            $getData[$key]['totalRealMemberMileage'] += $aVal['addMemberMileage'];
                            $getData[$key]['totalRealCouponGoodsMileage'] += $aVal['addCouponGoodsMileage'];
                            $getData[$key]['totalRealDivisionCouponOrderMileage'] += $aVal['divisionAddCouponOrderMileage'];
                        }
                    }
                }

                // 검색 조건에 따른 실제 총 결제금액 합산 (배송비 안분 예치금/마일리지 제외)
                $discountPrice = $val['goodsDcPrice'] + $getData[$key]['totalMemberDcPrice'] + $getData[$key]['totalMemberOverlapDcPrice'] + $getData[$key]['totalCouponGoodsDcPrice'] + $getData[$key]['totalDivisionUseDeposit'] + $getData[$key]['totalDivisionUseMileage'] + $getData[$key]['totalDivisionCouponOrderDcPrice'] + $getData[$key]['enuri'];

                if ($this->useMyapp) { // 마이앱 사용에 따른 분기 처리
                    $discountPrice += $getData[$key]['totalMyappDcPrice'];
                }

                $getData[$key]['settlePrice'] = (($val['goodsPrice'] + $val['optionPrice'] + $val['optionTextPrice']) * $val['goodsCnt']) + $val['addGoodsPrice'];
                if ($mailFl === false) {
                    $getData[$key]['settlePrice'] -= $discountPrice;
                }

                // 취소가능 상품리스트
                if (in_array(substr($val['orderStatus'], 0, 1), $this->statusClaimCode['c']) === true) {
                    $getData[$key]['canCancel'] = true;
                }
                if ($val['orderChannelFl'] == 'naverpay') {  //네이버페이는 입금대기인경우 취소안됨
                    if (substr($orderData['orderStatus'], 0, 1) == 'd' || substr($orderData['orderStatus'], 0, 1) == 'o') {
                        $getData[$key]['canCancel'] = false;
                    }
                }

                // 환불가능 상품리스트
                if (in_array(substr($val['orderStatus'], 0, 1), $this->statusClaimCode['r']) === true) {
                    $getData[$key]['canRefund'] = true;
                }
                if ($val['orderChannelFl'] == 'naverpay') {  //네이버페이는 배송중,배송완료일 경우 환불안됨
                    if (substr($orderData['orderStatus'], 0, 1) == 'd') {
                        $getData[$key]['canRefund'] = false;
                    }
                }

                // 반품가능 상품리스트
                if (in_array(substr($val['orderStatus'], 0, 1), $this->statusClaimCode['b']) === true) {
                    $getData[$key]['canBack'] = true;
                }

                // 교환가능 상품리스트
                if (in_array(substr($val['orderStatus'], 0, 1), ['o', 'p', 's', 'g', 'd']) === true) {
                    $getData[$key]['canExchange'] = true;
                }
                if ($val['orderChannelFl'] == 'naverpay') {  //네이버페이는 배송중,배송완료일 경우 환불안됨
                    if (in_array(substr($orderData['orderStatus'], 0, 1), ['d'])) {
                        $getData[$key]['canExchange'] = false;
                    }
                }

                //네이버페이는 배송중,배송완료일 경우 환불안됨
                if ($val['orderChannelFl'] == 'naverpay') {
                    if (in_array(substr($orderData['orderStatus'], 0, 1), ['s'])) {
                        $getData[$key]['canExchange'] = false;
                        $getData[$key]['canBack'] = false;
                    }
                }

                // 주문상품까지의 예치금/마일리지 총 합 처리
                $getData[$key]['totalGoodsDivisionUseDeposit'] += $getData[$key]['totalDivisionUseDeposit'];
                $getData[$key]['totalGoodsDivisionUseMileage'] += $getData[$key]['totalDivisionUseMileage'];

                // 배송비에 안분된 예치금/마일리지를 상품별로 재 안분된 금액으로 추가 시켜 전체 할인된 예치금/마일리지 금액을 산출
                $getData[$key]['totalDivisionUseDeposit'] += $val['divisionGoodsDeliveryUseDeposit'];
                $getData[$key]['totalDivisionUseMileage'] += $val['divisionGoodsDeliveryUseMileage'];

                if($val['deliveryMethodFl']){
                    $getData[$key]['deliveryMethodFlText'] = gd_get_delivery_method_display($val['deliveryMethodFl']);
                    $getData[$key]['deliveryMethodFlSno'] = $delivery->deliveryMethodList['sno'][$val['deliveryMethodFl']];
                }

                // 관리자-주문 상세 - 쿠폰/할인/혜택 - 주문 상품 할인 데이터
                if(empty($val['goodsDiscountInfo']) === false && $val['goodsDiscountInfo'] != null) {
                    $getData[$key]['goodsDiscountInfo'] = json_decode($val['goodsDiscountInfo'], true);
                }
                // 관리자-주문 상세 - 쿠폰/할인/혜택 - 주문 상품 적립 데이터
                if(empty($val['goodsMileageAddInfo']) === false && $val['goodsMileageAddInfo'] != null) {
                    $getData[$key]['goodsMileageAddInfo'] = json_decode($val['goodsMileageAddInfo'], true);
                }
            }

            // 전체주문의 배송비 조건별 남아있는 금액 산출
            $realDeliveryCharge = $this->getRealDeliveryCharge($orderNo);

            // 실제 남은 배송비 계산 및 scm 별 데이터 저장
            foreach ($getData as $key => $val) {
                // 실제 남은 배송비 설정
                $getData[$key]['realDeliveryCharge'] = $realDeliveryCharge[$val['orderDeliverySno']];

                // settle에 배송비 안분된 마일리지/예치금을 빼줘 최종 settle을 만듬
                if ($mailFl === false) {
                    $getData[$key]['settlePrice'] -= ($val['divisionGoodsDeliveryUseDeposit'] + $val['divisionGoodsDeliveryUseMileage']);
                }

                // SCM 별로 데이터 설정
                if ($scmFl === true) {
                    //복수배송지 사용시 scm no 를 order info sno 로 대체한다.
                    if($useMultiShippingKey === true){
                        $setData[$val['orderInfoSno']][] = $getData[$key];
                    }
                    else {
                        $setData[$val['scmNo']][] = $getData[$key];
                    }
                } else {
                    $setData[$key] = $getData[$key];
                }
            }

            $setData = gd_htmlspecialchars_stripslashes($setData);

            if (($handleSno !== null || $orderGoodsNo !== null || $userHandleSno !== null) && ($scmFl !== true && is_array($orderGoodsNo) === false)) {
                return $setData[0];
            } else {
                return $setData;
            }
        } else {
            return false;
        }
    }

    /**
     * 전체주문의 배송비 조건별 남아있는 금액 산출
     * 배송비에서 환불금액을 제외한 나머지 배송비 산출(realDeliveryCharge)을 위해 사용되며,
     * 환불상세 및 주문상세의 클레임접수 상품리스트에서 사용된다.
     * 다만, 해당 메서드는 파라미터에 따라 주문상품리스트를 다르게 추출하기 때문에 무조건 해당 주문번호의 전체에서 계산하도록 구현되어져야 한다.
     *
     * @param string $orderNo 주문번호
     *
     * @return mixed
     * @author Jong-tae Ahn <qnibus@godo.co.kr>
     */
    public function getRealDeliveryCharge($orderNo)
    {
        // 환불완료된 주문상품 정보 가져오기
        $tmpField[] = DBTableField::setTableField(
            'tableOrderGoods',
            [
                'orderCd',
                'orderStatus',
                'scmNo',
                'divisionUseDeposit',
                'divisionUseMileage',
                'divisionGoodsDeliveryUseDeposit',
                'divisionGoodsDeliveryUseMileage',
            ],
            null,
            'og'
        );
        $tmpField[] = DBTableField::setTableField(
            'tableOrderHandle',
            [
                'refundUseDeposit',
                'refundUseMileage',
                'refundDeliveryCharge',
                'handleCompleteFl',
            ],
            null,
            'oh'
        );
        $tmpField[] = DBTableField::setTableField(
            'tableOrderDelivery',
            [
                'deliveryCharge',
                'divisionDeliveryUseDeposit',
                'divisionDeliveryUseMileage',
                'divisionDeliveryCharge',
                'divisionMemberDeliveryDcPrice',
                'realTaxSupplyDeliveryCharge',
                'realTaxVatDeliveryCharge',
                'realTaxFreeDeliveryCharge',
            ],
            null,
            'od'
        );
        $tmpKey = array_keys($tmpField);
        $arrField = [];
        foreach ($tmpKey as $key) {
            $arrField = array_merge($arrField, $tmpField[$key]);
        }
        unset($tmpField, $tmpKey);

        $strSQL = 'SELECT od.sno, ' . implode(', ', $arrField) . ' FROM ' . DB_ORDER_GOODS . ' og LEFT JOIN ' . DB_ORDER_HANDLE . ' oh ON og.handleSno = oh.sno LEFT JOIN ' . DB_ORDER_DELIVERY . ' od ON og.orderDeliverySno = od.sno WHERE og.orderNo = ? ORDER BY ' . $this->orderGoodsOrderBy;
        $arrBind = [
            's',
            $orderNo,
        ];

        $deliveryData = [];
        $tmpData = gd_htmlspecialchars_stripslashes($this->db->query_fetch($strSQL, $arrBind, true));
        if (empty($tmpData) === false) {
            foreach ($tmpData as $val) {
                // 전체 사용 마일리지/예치금
                $totalDeposit = $val['divisionUseDeposit'] + $val['divisionGoodsDeliveryUseDeposit'];
                $totalMileage = $val['divisionUseMileage'] + $val['divisionGoodsDeliveryUseMileage'];

                // 추가상품 예치금/마일리지 합하기
                $addGoods = $this->getOrderAddGoods(
                    $orderNo, $val['orderCd'], [
                        'divisionAddUseDeposit',
                        'divisionAddUseMileage',
                    ]
                );
                if (empty($addGoods) === false) {
                    foreach ($addGoods as $aKey => $aVal) {
                        $totalDeposit += $aVal['divisionAddUseDeposit'];
                        $totalMileage += $aVal['divisionAddUseMileage'];
                    }
                }

                // 환불된 배송비 계산을 위한 배송금액 저장하고 조건에 따라 환불관련 금액을 합산한다.
                gd_isset($deliveryData[$val['sno']]['deliveryCharge'], $val['deliveryCharge']);
                gd_isset($deliveryData[$val['sno']]['divisionDeliveryCharge'], $val['divisionDeliveryCharge']);
                gd_isset($deliveryData[$val['sno']]['divisionMemberDeliveryDcPrice'], $val['divisionMemberDeliveryDcPrice']);
                gd_isset($deliveryData[$val['sno']]['divisionDeliveryUseDeposit'], $val['divisionDeliveryUseDeposit']);
                gd_isset($deliveryData[$val['sno']]['divisionDeliveryUseMileage'], $val['divisionDeliveryUseMileage']);
                gd_isset($deliveryData[$val['sno']]['refundDeliveryCharge'], 0);
                gd_isset($deliveryData[$val['sno']]['refundDeliveryUseDeposit'], 0);
                gd_isset($deliveryData[$val['sno']]['refundDeliveryUseMileage'], 0);
                $deliveryData[$val['sno']]['realTaxSupplyDeliveryCharge'] = gd_isset($val['realTaxSupplyDeliveryCharge'], 0);
                $deliveryData[$val['sno']]['realTaxVatDeliveryCharge'] = gd_isset($val['realTaxVatDeliveryCharge'], 0);
                $deliveryData[$val['sno']]['realTaxFreeDeliveryCharge'] = gd_isset($val['realTaxFreeDeliveryCharge'], 0);

                // 환불완료인 경우 환불배송비 세팅
                if ($val['handleCompleteFl'] == 'y' && $val['orderStatus'] == 'r3') {
                    $deliveryData[$val['sno']]['refundDeliveryCharge'] += $val['refundDeliveryCharge'];

                    // 배송비가 있거나 없거나 기 환불된 예치금 확인
                    if ($val['divisionDeliveryUseDeposit'] > 0 && $totalDeposit == $val['refundUseDeposit']) {
                        $deliveryData[$val['sno']]['refundDeliveryUseDeposit'] += ($totalDeposit - $val['refundUseDeposit']);
                    }

                    // 배송비가 있거나 없거나 기 환불된 예치금 확인
                    if ($val['divisionDeliveryUseMileage'] > 0 && $totalMileage == $val['refundUseMileage']) {
                        $deliveryData[$val['sno']]['refundDeliveryUseMileage'] += ($totalMileage - $val['refundUseMileage']);
                    }
                }
            }

            // 배송비조건별로 실제 남은 배송비 산출
            foreach ($deliveryData as $key => $val) {
                //$setData[$key] = $val['deliveryCharge'] - $val['divisionDeliveryCharge'] - $val['divisionDeliveryUseDeposit'] - $val['divisionDeliveryUseMileage'] - $val['refundDeliveryCharge'];
                $setData[$key] = $val['realTaxSupplyDeliveryCharge'] + $val['realTaxVatDeliveryCharge'] + $val['realTaxFreeDeliveryCharge'];
            }
            unset($tmpData, $deliveryData);
        }

        return $setData;
    }

    /**
     * 주문상품의 주문상태만 출력
     *
     * @param array $orderNo 주문 번호
     *
     * @return array 해당 주문 상품 정보
     */
    public function getOrderGoodsForStatus($orderNo, $arrSno = null)
    {
        // 주문번호 bind
        $this->db->bind_param_push($arrBind, 's', $orderNo);

        // 주문 상품 테이블 sno bind
        if (is_null($arrSno) === false && is_array($arrSno) === true) {
            foreach ($arrSno as $sno) {
                $this->db->bind_param_push($arrBind, 'i', $sno);
                $arrBindParam[] = '?';
            }
            $strWhere = ' AND sno IN (' . implode(',', $arrBindParam) . ')';
        }

        // 주문 상품 데이타
        $arrInclude = [
            'orderStatus',
            'userHandleSno',
            'handleSno',
        ];
        $arrField = DBTableField::setTableField('tableOrderGoods', $arrInclude, null);
        $strSQL = 'SELECT sno, ' . implode(', ', $arrField) . ' FROM ' . DB_ORDER_GOODS . ' WHERE orderNo = ? ' . gd_isset($strWhere) . ' ORDER BY sno ASC';
        $getData = $this->db->query_fetch($strSQL, $arrBind);

        // 데이타 출력
        if (count($getData) > 0) {
            return gd_htmlspecialchars_stripslashes($getData);
        } else {
            return false;
        }
    }

    /**
     * 주문 상품 출력 (SMS 전송시)
     *
     * @param string $orderNo        주문번호
     * @param array  $orderGoodsData 주문 상품 정보 (0 => [goodsNo, orderCd] 값을 배열로, goodsNo 는 필수)
     * @param array  $sendType       전송 상태 (ORDER - 주문확인관련, INCASH - 입금확인관련, DELIVERY - 배송/발송관련)
     *
     * @return array 주문 상품 정보
     */
    public function getOrderGoodsForSend($orderNo, $orderGoodsData = null, $sendType = null)
    {
        // 주문 상품 데이타가 있는 경우
        if (empty($orderGoodsData) === false) {
            foreach ($orderGoodsData as $gVal) {
                $tmpGoodsNo[] = $gVal['goodsNo'];
                if (isset($gVal['orderCd']) === true) {
                    $tmpOrderCd[] = $gVal['orderCd'];
                }
                $arrWhere[] = 'goodsNo IN (\'' . implode('\', \'', $tmpGoodsNo) . '\')';
                if (isset($tmpOrderCd) === true) {
                    $arrWhere[] = 'orderCd IN (' . implode(', ', $tmpOrderCd) . ')';
                }
            }
        }
        $arrWhere[] = 'orderNo = ?';
        //@formatter:off
        $arrInclude = ['orderStatus', 'orderDeliverySno', 'invoiceNo', 'invoiceCompanySno', 'scmNo', 'goodsNo', 'goodsNm', 'deliveryMethodFl'];
        $arrBind = ['s', $orderNo,];
        //@formatter:on
        $arrField = DBTableField::setTableField('tableOrderGoods', $arrInclude, null, 'og');
        $strSQL = 'SELECT og.sno, ' . implode(', ', $arrField) . ', mdc.companyName FROM ' . DB_ORDER_GOODS . ' AS og ';
        $strSQL .= ' LEFT JOIN ' . DB_MANAGE_DELIVERY_COMPANY . ' AS mdc ON og.invoiceCompanySno = mdc.sno ';
        $strSQL .= ' WHERE ' . implode(' AND ', $arrWhere) . ' ORDER BY og.sno ASC';
        $getData = gd_htmlspecialchars_stripslashes($this->db->query_fetch($strSQL, $arrBind));

        // 데이타 출력
        if (count($getData) > 0) {
            $setData = [];
            if ($sendType == 'INVOICE_CODE') {
                $arrInclude[] = 'companyName';
            }
            foreach ($getData as $gVal) {
                foreach ($arrInclude as $gKey) {
                    $setData[$gKey][] = $gVal[$gKey];
                }
            }
            unset($getData);

            return $setData;
        } else {
            return false;
        }
    }

    /**
     * 주문 추가상품 출력
     *
     * @param array      $orderNo 주문 번호
     * @param array      $orderCd 주문상품 코드
     * @param null       $arrInclude
     * @param array|null $arrExclude
     *
     * @return array 해당 주문 추가상품 정보
     * @deprecated 추후 삭제 예정
     */
    public function getOrderAddGoods($orderNo, $orderCd = null, $arrInclude = null, $arrExclude = ['orderNo', 'orderCd'])
    {
        $arrIncludeAg = [
            'imageStorage',
            'imagePath',
            'imageNm',
        ];
        $arrExcludeOa = [
            'orderNo',
            'orderCd',
        ];
        $arrFieldAg = DBTableField::setTableField('tableAddGoods', $arrIncludeAg, null, 'ag');
        $arrFieldOa = DBTableField::setTableField('tableOrderAddGoods', $arrInclude, $arrExclude, 'oa');

        $arrJoin[] = ' LEFT JOIN ' . DB_ADD_GOODS . ' ag ON ag.addGoodsNo = oa.addGoodsNo ';

        $arrWhere[] = 'oa.orderNo = ?';
        $this->db->bind_param_push($arrBind, 's', $orderNo);

        if ($orderCd) {
            $arrWhere[] = 'oa.orderCd = ?';
            $this->db->bind_param_push($arrBind, 'i', $orderCd);
        }

        $this->db->strJoin = implode('', $arrJoin);
        $this->db->strField = 'oa.sno, ' . implode(', ', $arrFieldAg) . ', ' . implode(', ', $arrFieldOa) . ', oa.regDt ';
        $this->db->strWhere = implode(' AND ', gd_isset($arrWhere));
        $this->db->strOrder = 'oa.sno DESC';


        if (empty($arrBind)) {
            $arrBind = null;
        }
        $query = $this->db->query_complete();
        $strSQL = 'SELECT ' . array_shift($query) . ' FROM ' . DB_ORDER_ADD_GOODS . ' oa ' . implode(' ', $query);
        $getData = $this->db->query_fetch($strSQL, $arrBind);
        foreach ($getData as $key => $val) {
            // 상품이미지 처리 이미지($val['imageNm']) 없을 경우 noimage 노출
            $getData[$key]['goodsImage'] = gd_html_add_goods_image($val['addGoodsNo'], $val['imageNm'], $val['imagePath'], $val['imageStorage'], 50, $val['goodsNm'], '_blank');
        }

        // 데이타 출력
        if (count($getData) > 0) {
            return gd_htmlspecialchars_stripslashes($getData);
        } else {
            return false;
        }
    }

    /**
     * 주문 배송내역 출력
     *
     * @param string $orderNo 주문 번호
     * @param null   $deliverySno
     * @param bool   $scmFl
     * @param null   $arrInclude
     *
     * @return array 해당 주문 배송내역 정보
     */
    public function getOrderDelivery($orderNo, $deliverySno = null, $scmFl = true, $arrInclude = null)
    {
        // 주문 배송 정보
        $arrExclude = ['orderNo'];
        $arrField = DBTableField::setTableField('tableOrderDelivery', $arrInclude, $arrExclude);
        $strSQL = 'SELECT sno, ' . implode(', ', $arrField) . ' FROM ' . DB_ORDER_DELIVERY . ' WHERE orderNo = ? ORDER BY scmNo ASC';
        $arrBind = [
            's',
            $orderNo,
        ];
        $data = $this->db->query_fetch($strSQL, $arrBind);

        if (is_null($data) === true) {
            return false;
        }

        // scm 별로 정렬
        foreach ($data as $key => $val) {
            if ($scmFl === true) {
                $getData[$val['scmNo']][] = $data[$key];
            } else {
                $getData[] = $data[$key];
            }
        }

        // 배송 내역 출력
        if (count($getData) > 0) {
            return gd_htmlspecialchars_stripslashes($getData);
        } else {
            return false;
        }
    }

    /**
     * 주문 사은품 정보 출력
     *
     * @param array      $orderNo   주문 번호
     * @param null       $scmNo     공급사 번호
     * @param intger|int $imageSize 사은품 이미지 사이즈
     *
     * @return array 해당 주문 사은품 정보
     */
    public function getOrderGift($orderNo, $scmNo = null, $imageSize = 50)
    {
        $strWhere[] = 'og.orderNo = ?';
        $this->db->bind_param_push($arrBind, 's', $orderNo);

        // 공급사 번호가 있으면 해당 공급사의 사은품만 출력
        if (Manager::isProvider() && $scmNo !== null) {
            $strWhere[] = 'og.scmNo = ?';
            $this->db->bind_param_push($arrBind, 'i', $scmNo);
        }

        $join[] = ' LEFT JOIN ' . DB_GIFT_PRESENT . ' gp ON og.presentSno = gp.sno ';
        $join[] = ' LEFT JOIN ' . DB_GIFT . ' g ON og.giftNo = g.giftNo ';

        $arrExcludeOg = [
            'orderNo',
            'presentSno',
        ];
        $arrIncludeG = [
            'giftNm',
            'giftCd',
            'giftDescription',
        ];
        $arrIncludeGp = [
            'presentTitle',
        ];
        $arrFieldOg = DBTableField::setTableField('tableOrderGift', null, $arrExcludeOg, 'og');
        $arrFieldG = DBTableField::setTableField('tableGift', $arrIncludeG, null, 'g');
        $arrFieldGp = DBTableField::setTableField('tableGiftPresent', $arrIncludeGp, null, 'gp');

        $this->db->strField = 'og.sno, ' . implode(', ', $arrFieldOg) . ', ' . implode(', ', $arrFieldG) . ', ' . implode(', ', $arrFieldGp);
        $this->db->strWhere = implode(' AND ', $strWhere);
        $this->db->strJoin = implode('', $join);
        $this->db->strOrder = 'og.sno ASC';

        $query = $this->db->query_complete();
        $strSQL = 'SELECT ' . array_shift($query) . ' FROM ' . DB_ORDER_GIFT . ' og ' . implode(' ', $query);
        $getData = $this->db->query_fetch($strSQL, $arrBind);

        // 사은품 데이터 보기 좋게 가공 처리
        $setData = [];
        foreach ($getData as $key => $val) {
            $setData[$key]['multiGiftNo'] = $val['giftNo'];
        }

        $gift = \App::load('\\Component\\Gift\\Gift');
        $gift->viewGiftData($setData, $imageSize);

        foreach ($setData as $key => $val) {
            $data = $val['multiGiftNo'][0];
            $getData[$key]['giftNm'] = $data['giftNm'];
            $getData[$key]['imageUrl'] = $data['imageUrl'];
        }

        // 사은품 내역 출력
        if (count($getData) > 0) {
            return gd_htmlspecialchars_stripslashes($getData);
        } else {
            return false;
        }
    }

    /**
     * 1차 scmno 2차 deliverysno를 기준으로 주문상품의 데이터를 배열로 전환해서 반환
     * 장바구니의 데이터 정렬방식과 동일하며 취소상품의 금액계산시 사용 가능
     *
     * @param       $orderNo
     * @param array $handleSno
     *
     * @return array
     */
    public function getOrderHandle($orderNo, $handleSno = [])
    {
        $this->db->bind_param_push($arrBind, 's', $orderNo);
        $arrWhere[] = 'oh.orderNo = ?';

        if (empty($handleSno) === false) {
            if (is_array($handleSno)) {
                $arrWhere[] = 'oh.sno IN (\'' . implode('\',\'', $handleSno) . '\')';
            } else {
                $this->db->bind_param_push($arrBind, 'i', $handleSno);
                $arrWhere[] = 'oh.sno = ?';
            }
        }

        // 필드 정보
        $orderHandleField = DBTableField::setTableField('tableOrderHandle', null, null, 'oh');

        // 상품 옵션 데이타
        $this->db->strField = 'oh.sno, ' . implode(',', $orderHandleField);
        $this->db->strWhere = implode(' AND ', $arrWhere);

        $query = $this->db->query_complete();
        $strSQL = 'SELECT ' . array_shift($query) . ' FROM ' . DB_ORDER_HANDLE . ' oh ' . implode(' ', $query);
        $getData = $this->db->query_fetch($strSQL, $arrBind);
        if (count($getData) > 1) {
            foreach ($getData as $key => $value) {
                if (gd_str_length($value['refundAccountNumber']) > 50) {
                    $getData[$key]['refundAccountNumber'] = \Encryptor::decrypt($value['refundAccountNumber']);
                }
            }
        } else {
            if (gd_str_length($getData['refundAccountNumber']) > 50) {
                $getData['refundAccountNumber'] = \Encryptor::decrypt($getData['refundAccountNumber']);
            }
        }

        return $getData;
    }

    /**
     * 주문 상품 재고 정보
     *
     * @param integer $goodsNo    상품코드
     * @param array   $optionInfo 주무정보
     *
     * @return array 해당 주문 사은품 정보
     */
    public function getOrderGoodsStock($goodsNo, $optionInfo)
    {
        $this->db->bind_param_push($arrBind, 'i', $goodsNo);
        $arrWhere[] = 'go.goodsNo = ?';

        // 옵션 where문 data
        if (empty($optionInfo) === true) {
            $arrWhere[] = 'go.optionNo = ?';
            $arrWhere[] = '(go.optionValue1 = \'\' OR isnull(go.optionValue1))';
            $this->db->bind_param_push($arrBind, 'i', 1);
        } else {

            $tmpOption = json_decode(gd_htmlspecialchars_stripslashes($optionInfo), true);
            foreach ($tmpOption as $goKey => $goVal) {
                $arrWhere[] = 'go.optionValue' . ($goKey + 1) . ' = ?';
                $this->db->bind_param_push($arrBind, 's', $goVal[1]);
            }
            unset($tmpOption);
        }
        // 상품 옵션 데이타
        $this->db->strField = 'go.stockCnt';
        $this->db->strWhere = implode(' AND ', $arrWhere);

        $query = $this->db->query_complete();
        $strSQL = 'SELECT ' . array_shift($query) . ' FROM ' . DB_GOODS_OPTION . ' go ' . implode(' ', $query);
        $optionData = $this->db->query_fetch($strSQL, $arrBind, false);

        return $optionData['stockCnt'];
    }

    /**
     * 마이페이지 > 주문리스트에서 사용하며, 회원/비회원 데이터를 모두 출력해준다.
     * @todo 쿼리 리팩 필요 (현재 ORDER리스트를 가져온 후 별도로 ORDER_GOODS를 바인딩 처리)
     *
     * @param int     $pageNum    페이지당 출력할 갯수
     * @param null    $dates      시작날짜와 끝날짜의 배열
     * @param boolean $statusMode 취소상태 표기 여부
     *
     * @return string
     * @throws AlertRedirectException
     */
    public function getOrderList($pageNum = 10, $dates = null, $statusMode = null)
    {
        // 배열 선언
        $arrBind = $arrWhere = [];

        // 상품혜택관리 치환코드 생성
        if(!is_object($goodsBenefit)){
            $goodsBenefit = \App::load('\\Component\\Goods\\GoodsBenefit');
        }

        // 회원 or 비회원 패스워드 체크
        if (MemberUtil::checkLogin() == 'member') {
            $arrWhere[] = 'o.memNo = ?';
            $this->db->bind_param_push($arrBind, 'i', Session::get('member.memNo'));
        } else {
            throw new AlertRedirectException(__('로그인 정보가 존재하지 않습니다.'), null, null, '../member/login.php');
        }

        // 기간 설정
        if (null !== $dates && is_array($dates) && $dates[0] != '' && $dates[1] != '') {
            $arrWhere[] = 'o.regDt BETWEEN ? AND ?'; // 한달 이내쿼리로 조작';
            $this->db->bind_param_push($arrBind, 's', $dates[0] . ' 00:00:00');
            $this->db->bind_param_push($arrBind, 's', $dates[1] . ' 23:59:59');
        }
        else {  //빈값으로 넘어오면 1년범위 검색
            $dates[0] = date('Y-m-d', strtotime('-365 days'));
            $dates[1] = date('Y-m-d');
            $arrWhere[] = 'o.regDt BETWEEN ? AND ?'; // 한달 이내쿼리로 조작';
            $this->db->bind_param_push($arrBind, 's', $dates[0] . ' 00:00:00');
            $this->db->bind_param_push($arrBind, 's', $dates[1] . ' 23:59:59');
        }

        // 주문 테이블 필드
        $arrInclude = [
            'orderNo',
            'orderChannelFl',
            'settlePrice',
            'settleKind',
            'orderGoodsCnt',
            'orderTypeFl',
            'orderGoodsCnt',
            'multiShippingFl'
        ];
        if (Globals::get('gGlobal.isFront')) {
            array_push($arrInclude,'currencyPolicy','exchangeRatePolicy');
        }

        $tmpField[] = DBTableField::setTableField('tableOrder', $arrInclude, null, 'o');

        // 조인
        $arrJoin[] = ' LEFT JOIN ' . DB_ORDER_GOODS . ' og ON o.orderNo = og.orderNo ';

        // 결제실패를 제외하고 전부 출력
        $arrWhere[] = 'og.orderStatus != ?';
        $this->db->bind_param_push($arrBind, 's', 'f1');

        // 주문 or 취소 리스트 조건
        switch ($statusMode) {
            // 주문관련 리스트만
            case 'order':
                $tmpField[] = DBTableField::setTableField('tableOrderUserHandle', ['userHandleFl'], null, 'ouh');
                $arrJoin[] = ' LEFT JOIN ' . DB_ORDER_USER_HANDLE . ' ouh ON og.userHandleSno = ouh.sno AND og.orderNo = ouh.orderNo ';
                $arrWhere[] = 'og.handleSno<=0 AND LEFT(og.orderStatus, 1) NOT IN (\'' . implode('\',\'', $this->statusExcludeCd) . '\')';
                break;

            // 기본 프로퍼티에서 반품(r) 제거
            case 'cancel':
                $statusExcludeCd = [];
                foreach ($this->statusExcludeCd as $key => $val) {
                    if ($val != 'r') {
                        $statusExcludeCd[$key] = $val;
                    }
                }
                $tmpField[] = DBTableField::setTableField('tableOrderGoods', ['handleSno'], null, 'og');
                $arrWhere[] = '((og.handleSno > 0) OR (LEFT(og.orderStatus, 1) IN (\'' . implode('\',\'', $statusExcludeCd) . '\')))';
                break;

            // 교환, 반품 신청 및 거절 상태
            case 'cancelRequest':
                // 사용자 클레임 승인 코드에서 승인(y) 제거
                $statusClaimHandleCode = [];
                foreach ($this->statusClaimHandleCode as $key => $val) {
                    if ($val != 'y') {
                        $statusClaimHandleCode[$key] = $val;
                    }
                }

                // 사용자 클레임 신청 코드에서 환불(r) 제거
                $statusUserClaimRequestCode = [];
                foreach ($this->statusUserClaimRequestCode as $key => $val) {
                    if ($val != 'r') {
                        $statusUserClaimRequestCode[$key] = $val;
                    }
                }

                $tmpField[] = DBTableField::setTableField('tableOrderUserHandle', ['userHandleFl'], null, 'ouh');
                $arrJoin[] = ' LEFT JOIN ' . DB_ORDER_USER_HANDLE . ' ouh ON og.orderNo = ouh.orderNo AND (og.userHandleSno = ouh.sno || (og.sno = ouh.userHandleGoodsNo && left(og.orderStatus, 1) NOT IN (\'' . implode('\',\'', $statusUserClaimRequestCode) . '\')))';
                $arrWhere[] = 'ouh.userHandleFl IN (\'' . implode('\',\'', $statusClaimHandleCode) . '\') AND ouh.userHandleMode IN (\'' . implode('\',\'', $statusUserClaimRequestCode) . '\') AND LEFT(og.orderStatus, 1) IN (\'' . implode('\',\'', $this->statusClaimCode['b']) . '\')';
                break;

            // 반품만
            case 'refund':
                $arrJoin[] = ' LEFT JOIN ' . DB_ORDER_USER_HANDLE . ' ouh ON og.userHandleSno = ouh.sno AND og.orderNo = ouh.orderNo AND ouh.userHandleFl = \'y\'';
                $arrWhere[] = 'LEFT(og.orderStatus, 1) IN (\'r\')';
                break;

            // 환불 신청 및 거절 상태
            case 'refundRequest':
                // 사용자 클레임 승인 코드에서 승인(y) 제거
                $statusClaimHandleCode = [];
                foreach ($this->statusClaimHandleCode as $key => $val) {
                    if ($val != 'y') {
                        $statusClaimHandleCode[$key] = $val;
                    }
                }

                // 사용자 클레임 신청 코드에서 반품, 교환(b, e) 제거
                $statusUserClaimRequestCode = [];
                foreach ($this->statusUserClaimRequestCode as $key => $val) {
                    if ($val != 'b' && $val != 'e') {
                        $statusUserClaimRequestCode[$key] = $val;
                    }
                }

                $tmpField[] = DBTableField::setTableField('tableOrderUserHandle', ['userHandleFl'], null, 'ouh');
                $arrJoin[] = ' LEFT JOIN ' . DB_ORDER_USER_HANDLE . ' ouh ON og.orderNo = ouh.orderNo AND (og.userHandleSno = ouh.sno || (og.sno = ouh.userHandleGoodsNo && left(og.orderStatus, 1) NOT IN (\'' . implode('\',\'', $statusUserClaimRequestCode) . '\')))';
                $arrWhere[] = 'ouh.userHandleFl IN (\'' . implode('\',\'', $statusClaimHandleCode) . '\') AND ouh.userHandleMode IN (\'' . implode('\',\'', $statusUserClaimRequestCode) . '\') AND LEFT(og.orderStatus, 1) IN (\'' . implode('\',\'', $this->statusReceiptApprovalPossible) . '\')';
                break;

            // 모바일 프론트에서 사용
            case 'mobile':
                break;
        }

        // 필드 정리
        $tmpKey = array_keys($tmpField);
        $arrField = [];
        foreach ($tmpKey as $key) {
            $arrField = array_merge($arrField, $tmpField[$key]);
        }
        unset($tmpField, $tmpKey);

        // 페이지 기본설정
        $pageNo = Request::get()->get('page', 1);
        $page = \App::load('\\Component\\Page\\Page', $pageNo);
        $page->page['list'] = $pageNum; // 페이지당 리스트 수
        $page->block['cnt'] = 5;
        $page->setPage();
        $page->setUrl(Request::getQueryString());

        // 현 페이지 결과
        $this->db->strJoin = implode('', $arrJoin);
        $this->db->strField = implode(', ', $arrField) . ', o.regDt';
        $this->db->strWhere = implode(' AND ', gd_isset($arrWhere));
        $this->db->strOrder = 'og.orderNo desc';
        $this->db->strLimit = $page->recode['start'] . ',' . $pageNum;
        $this->db->strGroup = 'og.orderNo';

        if (empty($arrBind)) {
            $arrBind = null;
        }
        $query = $this->db->query_complete();
        $strSQL = 'SELECT ' . array_shift($query) . ' FROM ' . DB_ORDER . ' o ' . implode(' ', $query);
        $data = $this->db->query_fetch($strSQL, $arrBind);

        // 현 페이지 검색 결과
        unset($query['group'], $query['order'], $query['limit']);
        $strCntSQL = 'SELECT COUNT(DISTINCT og.orderNo) AS cnt FROM ' . DB_ORDER . ' AS o ' . implode(' ', $query);
        $total = $this->db->query_fetch($strCntSQL, $arrBind, false)['cnt'];

        // 검색 레코드 수
        $page->recode['total'] = $total;
        $page->setPage();

        // 결제방법 과 처리 상태 설정
        if (gd_isset($data)) {
            foreach ($data as $key => $val) {
                $useMultiShippingKey = false;
                if (\Component\Order\OrderMultiShipping::isUseMultiShipping() == true) {
                    $useMultiShippingKey = true;
                }

                $val['goods'] = $this->getOrderGoodsData($val['orderNo'], null, null, null, null, false, false, null, null, false, $useMultiShippingKey);
                $val['orderInfo'] = $this->getOrderInfo($val['orderNo'], false);
                $val['orderGoodsCnt'] = gd_isset(count($val['goods']), 0);
                $val['settleName'] = $this->getSettleKind($val['settleKind']);

                // 멀티상점 환율 기본 정보
                if (Globals::get('gGlobal.isFront')) {
                    $val['currencyPolicy'] = json_decode($val['currencyPolicy'], true);
                    $val['exchangeRatePolicy'] = json_decode($val['exchangeRatePolicy'], true);
                    $val['currencyIsoCode'] = $val['currencyPolicy']['isoCode'];
                    $val['exchangeRate'] = $val['exchangeRatePolicy']['exchangeRate' . $val['currencyPolicy']['isoCode']];
                }

                // 주문상품 loop
                if (isset($val['goods']) && empty($val['goods']) === false) {
                    foreach ($val['goods'] as $aKey => $aVal) {
                        $addGoodsCnt = gd_isset($aVal['addGoodsCnt'], 0);
                        //상품혜택 사용시 해당 변수 재설정
                        $val['goods'][$aKey] = $goodsBenefit->goodsDataFrontReplaceCode($aVal, 'mypage');
                        // 리스트에서 각 모드별로 불필요한 row를 제거
                        switch ($statusMode) {
                            case 'order':
                                if (in_array(substr($aVal['orderStatus'], 0, 1), $this->statusExcludeCd)) {
                                    $val['orderGoodsCnt'] -= 1;
                                    unset($val['goods'][$aKey]);
                                } else {
                                    $val['orderAddGoodsCnt'] += $addGoodsCnt;
                                }
                                break;

                            case 'cancel':
                                $includeOrderGoods = false;
                                if (in_array(substr($aVal['orderStatus'], 0, 1), $statusExcludeCd)) {
                                    $includeOrderGoods = true;
                                }
                                if ((int)$aVal['handleSno'] > 0) {
                                    $includeOrderGoods = true;
                                }

                                if($includeOrderGoods === true){
                                    $val['orderAddGoodsCnt'] += $addGoodsCnt;
                                    $val['goods'][$aKey]['orderInfoRow'] = 1;
                                }
                                else {
                                    $val['orderGoodsCnt'] -= 1;
                                    unset($val['goods'][$aKey]);
                                }
                                break;

                            case 'cancelRequest':
                                if ($aVal['userHandleSno'] <= 0 || in_array(substr($aVal['orderStatus'], 0, 1), $this->statusClaimCode['r']) || in_array(substr($aVal['orderStatus'], 0 , 1), $statusUserClaimRequestCode)) {
                                    $val['orderGoodsCnt'] -= 1;
                                    unset($val['goods'][$aKey]);
                                } else {
                                    $val['orderAddGoodsCnt'] += $addGoodsCnt;
                                    $val['goods'][$aKey]['orderInfoRow'] = 1;
                                }
                                break;

                            case 'refund':
                                if (!in_array(substr($aVal['orderStatus'], 0, 1), ['r'])) {
                                    $val['orderGoodsCnt'] -= 1;
                                    unset($val['goods'][$aKey]);
                                } else {
                                    $val['orderAddGoodsCnt'] += $addGoodsCnt;
                                    $val['goods'][$aKey]['orderInfoRow'] = 1;
                                }
                                break;

                            case 'refundRequest':
                                if ($aVal['userHandleSno'] <= 0 || in_array(substr($aVal['orderStatus'], 0, 1), ['r'])) {
                                    $val['orderGoodsCnt'] -= 1;
                                    unset($val['goods'][$aKey]);
                                } else {
                                    $val['orderAddGoodsCnt'] += $addGoodsCnt;
                                    $val['goods'][$aKey]['orderInfoRow'] = 1;
                                }
                                break;
                        }
                    }
                }

                // 데이터 재가공
                $data[$key] = $val;
            }
        }

        // 배열 인덱스 정리
        foreach ($data as $key => $val) {
            $data[$key]['goods'] = array_values($data[$key]['goods']);
        }

        return gd_htmlspecialchars_stripslashes($data);
    }

    /**
     * 마이페이지 > 클래임접수 리스트 출력 (반품보류, 교환보류, 환불보류)
     *
     * @param string $orderNo      주문 번호
     * @param string $orderGoodsNo 처리 코드
     *
     * @return array 해당 주문 상품 정보
     */
    public function getOrderListForClaim($orderNo, $orderGoodsNo = null)
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
            'userHandleSno',
        ];
        $arrField = 's.companyNm as scmNm, ' . implode(',', DBTableField::setTableField('tableOrderGoods', $arrInclude[0], null, 'og'))
            . ', ouh.userHandleGoodsNo AS userHandleGoodsNo, ouh.userHandleGoodsCnt AS userHandleGoodsCnt, ouh.regDt AS userHandleRegDt, ouh.modDt AS userHandleModDt, ouh.sno AS userHandleNo, ' . implode(',', DBTableField::setTableField('tableOrderUserHandle', null, null, 'ouh'))
            . ', oh.handleReason, oh.handleDetailReason, oh.handleDetailReasonShowFl ';
        $arrWhere[] = " og.orderNo = ? ";
        $this->db->bind_param_push($arrBind, 's', $orderNo);

        if (null !== $orderGoodsNo) {
            $arrWhere[] = " og.sno = ? ";
            $this->db->bind_param_push($arrBind, 's', $orderGoodsNo);
        } else {
            $arrWhere[] = " ouh.userHandleFl != 'y' ";
        }

        $arrJoin[] = ' LEFT JOIN ' . DB_ORDER_USER_HANDLE . ' AS ouh ON (og.userHandleSno = ouh.sno || og.sno = ouh.userHandleGoodsNo) AND og.orderNo = ouh.orderNo ';
        $arrJoin[] = ' LEFT JOIN ' . DB_SCM_MANAGE . ' as s ON s.scmNo = og.scmNo ';
        $arrJoin[] = ' LEFT JOIN ' . DB_ORDER_HANDLE . ' oh ON og.handleSno = oh.sno AND og.orderNo = oh.orderNo ';

        $this->db->strField = $arrField;
        $this->db->strWhere = implode(' AND ', gd_isset($arrWhere));
        $this->db->strJoin = implode('', $arrJoin);
        $this->db->strOrder = 'og.sno ASC';
        $this->db->strGroup = 'ouh.sno';

        $query = $this->db->query_complete();
        $strSQL = 'SELECT ' . array_shift($query) . ' FROM ' . DB_ORDER_GOODS . ' as og' . implode(' ', $query);
        $getData = $this->db->query_fetch($strSQL, $arrBind, false);

        if (gd_str_length($getData['userRefundAccountNumber']) > 50) {
            $getData['userRefundAccountNumber'] = \Encryptor::decrypt($getData['userRefundAccountNumber']);
        }

        // 데이타 출력
        if (count($getData) > 0) {
            return gd_htmlspecialchars_stripslashes($getData);
        } else {
            return false;
        }
    }

    /**
     * 주문 상세정보
     *
     * @param string $orderNo 주문 번호
     *
     * @return array 주문 상세정보
     * @throws Exception
     */
    public function getOrderView($orderNo)
    {
        // 주문 번호 체크
        if (Validator::required($orderNo, true) === false) {
            throw new Exception(__('해당 주문번호로 조회할 수 없습니다.'));
        }

        // 주문 정보
        $getData = $this->getOrderDataInfo($orderNo);

        // 비회원이고 네이버 결제일때 주문번호 치환
        if (MemberUtil::checkLogin() == 'guest' && $getData['orderChannelFl'] == 'naverpay') {
            //$orderNo = $getData['orderNo'];
        }

        // 주문 데이타 체크
        if (empty($getData) === true) {
            throw new Exception(self::TEXT_NOT_EXIST_ORDER_INFO);
        }

        $useMultiShippingKey = false;
        if ($getData['multiShippingFl'] == 'y') {
            $useMultiShippingKey = true;
        }

        // 주문 상품 정보
        $getData['goods'] = $this->getOrderGoodsData($orderNo, null, null, null, 'user', false, false, null, null, false, $useMultiShippingKey);


        foreach ($getData['goods'] as $scmNo => $dataVal) {
            $infoSno = $dataVal['orderInfoSno'] > 0 ? $dataVal['orderInfoSno'] : $getData['infoSno'];
            if ($dataVal['goodsType'] == 'goods' && ($dataVal['deliveryMethodFl'] == 'visit' || empty($dataVal['visitAddress']) === false) && in_array($dataVal['sno'], $getData['checkoutData']['goodsSno']) === true) {
                $getData['visitDelivery'][$infoSno][$dataVal['sno']] = $dataVal['deliverySno'];
                $getData['visitAddressInfo'][$infoSno][$dataVal['sno']] = $dataVal['visitAddress'];
                $getData['deliveryMethodFl'][$infoSno][$dataVal['sno']] = $dataVal['deliveryMethodFl'];
            }

            $orderInfoSnoArr[] = $dataVal['orderInfoSno'];
        }
        $getData['multiShippingList'] = $this->getOrderInfo($orderNo, true, 'orderInfoCd asc', $orderInfoSnoArr);

        $getData['orderAddGoodsCnt'] = 0;
        foreach ($getData['goods'] as $aVal) {
            $getData['orderAddGoodsCnt'] += $aVal['addGoodsCnt'];
        }

        // 주문 배송 정보
        $getData['delivery'] = $this->getOrderDelivery($orderNo);

        // 주문 배송 정보내 무게 상세 정보
        $oneDelivery = reset(reset($getData['delivery']));
        $getData['deliveryWeightInfo'] = json_decode($oneDelivery['deliveryWeightInfo'], true);

        // 주문 현금영수증 정보
        if ($getData['receiptFl'] == 'r') {
            $cashReceipt = \App::load('\\Component\\Payment\\CashReceipt');
            $getData['cash'] = $cashReceipt->getOrderCashReceipt($orderNo);
        }

        // 주문 세금계산서 정보
        if ($getData['receiptFl'] == 't') {
            $tax = \App::load('\\Component\\Order\\Tax');
            $getData['tax'] = $tax->getOrderTaxInvoice($orderNo);
        }

        // 주문 사은품 정보
        $getData['gift'] = $this->getOrderGift($orderNo, null, 40);

        // 배송지 수정 여부
        $getData['receiverCorrectFl'] = 'n';
        foreach ($this->statusPolicy as $key => $val) {
            if ($key == 'autoCancel') {
                continue;
            }
            if (substr($key, 0, 1) == $getData['orderStatus']) {
                if (isset($val['correct']) === true) {
                    if ($val['correct'] == 'y') {
                        $getData['receiverCorrectFl'] = 'y';
                    }
                }
            }
        }

        // 배송지 수정 가능한 경우 정보 수정
        if ($getData['receiverCorrectFl'] == 'y') {
            $getData['receiverPhoneArr'] = explode('-', $getData['receiverPhone']);
            $getData['receiverCellPhoneArr'] = explode('-', $getData['receiverCellPhone']);
        }

        // 페이코 바로구매 개인통관 고유번호 사용시 주문 추가정보에 개인통관고유번호 강제 추가
        if ($getData['orderChannelFl'] == 'payco') {
            $paycoDataField = empty($getData['fintechData']) === false ? 'fintechData' : 'checkoutData';
            if (is_array($getData[$paycoDataField]) === false) $getData[$paycoDataField] = json_decode($getData[$paycoDataField], true);

            if (empty($getData[$paycoDataField]) === false) {
                if ($getData[$paycoDataField]['individualCustomUniqNo']) {
                    $getData['addField'][] = [
                        'data' => $getData[$paycoDataField]['individualCustomUniqNo'],
                        'name' => '개인통관고유번호(페이코)'
                    ];
                }
            }
        }

        return $getData;
    }

    /**
     * 배송지 정보 수정
     */
    public function setOrderReceiverCorrect()
    {
        // 주문 번호 체크
        $orderNo = Request::post()->get('orderNo');
        if (Validator::required($orderNo, true) === false) {
            throw new Exception(self::TEXT_NOT_EXIST_ORDERNO);
        }

        // Validation
        $validator = new Validator();

        // 처리할 항목
        $arrCheckElement = [
            [
                'element'  => 'receiverName',
                'command'  => 'required',
                'required' => true,
                'name'     => __('받으실 분'),
                'implode'  => ' ',
            ],
            [
                'element'  => 'receiverCellPhone',
                'command'  => \App::getInstance('session')->has(SESSION_GLOBAL_MALL) ? 'required' : 'phone',
                'required' => true,
                'name'     => __('[수취인]휴대폰 번호'),
                'implode'  => '-',
            ],
            [
                'element'  => 'receiverZonecode',
                'command'  => 'required',
                'required' => true,
                'name'     => __('[수취인]우편번호'),
                'implode'  => '-',
            ],
            [
                'element'  => 'receiverAddress',
                'command'  => 'required',
                'required' => true,
                'name'     => __('[수취인]주소'),
                'implode'  => ' ',
            ],
            [
                'element'  => 'receiverAddressSub',
                'command'  => 'required',
                'required' => true,
                'name'     => __('[수취인]나머지 주소'),
                'implode'  => ' ',
            ],
            [
                'element'  => 'orderMemo',
                'command'  => '',
                'required' => false,
                'name'     => __('남기실 내용'),
                'implode'  => ' ',
            ],
        ];

        // post 값 체크
        foreach ($arrCheckElement as $validData) {
            if ($validData['required'] === true) {
                // 필수 값인 경우 값이 선언되어 있지 않으면 Exception 처리
                if (Request::post()->has($validData['element'])) {
                    throw new Exception(sprintf(__('%s은(는) 필수 항목 입니다.'), $validData['name']));
                }
            } else {
                // 필수 값이 아닌 경우 선언되어 있지 않다면 빈값
                Request::post()->get($validData['element'], '');
            }

            // 배열 값이면 implode 처리
            if (is_array($_POST[$validData['element']]) === true) {
                $_POST[$validData['element']] = ArrayUtils::removeEmpty($_POST[$validData['element']]);
                $_POST[$validData['element']] = implode($validData['implode'], $_POST[$validData['element']]);
            }

            // 이외 항목 처리를 위한 배열 값 생성
            $arrCheck[] = $validData['element'];

            // 항목별 Validation
            $validator->add($validData['element'], $validData['command'], $validData['required'], '{' . $validData['name'] . '}');
        }

        // Validation 결과
        if ($validator->act($_POST, true) === false) {
            throw new Exception(implode("\n", $validator->errors));
        }

        // 배송지 정보 수정
        $compareField = array_keys($_POST);
        $arrBind = $this->db->get_binding(DBTableField::tableOrderInfo(), $_POST, 'update', $compareField);
        $this->db->bind_param_push($arrBind['bind'], 's', $orderNo);
        $this->db->set_update_db(DB_ORDER_INFO, $arrBind['param'], 'orderNo = ?', $arrBind['bind']);
        unset($arrBind);
    }

    /**
     * 주문 상태 출력를 출력하며 주문정책에서 사용여부를 n으로 설정한 경우 출력 안됨)
     *
     * @param string $statusCode 주문상태 코드
     * @param string $statusMode 모드 ('admin','user')
     *
     * @return mixed 주문 상태 혹은 리스트 전체 출력
     */
    protected function _getOrderStatus($statusCode = null, $statusMode = 'user')
    {
        if ($statusMode != 'user') {
            $statusMode = 'admin';
        }

        foreach ($this->statusPolicy as $key => $val) {
            if ($key == 'autoCancel') {
                continue;
            }
            foreach ($val as $oKey => $oVal) {
                if (strlen($oKey) != 2 || ($statusCode === null && $oVal['useFl'] != 'y')) {
                    continue;
                }

                if ($statusCode === null) {
                    $codeArr[$oKey] = $oVal[$statusMode];
                } elseif ($oKey == $statusCode) {
                    $codeArr = $oVal[$statusMode];
                    break;
                }
            }
        }

        if($this->channel == 'naverpay' && is_array($codeArr)){

            //내부 반폼보류 삭제(네이버페로 대체)
            //발송지연
            //반품거부
            //반품보류
            //반품보류해제
            if(array_key_exists('b3',$codeArr) ){
                unset($codeArr['b3']);
            }
            if(array_key_exists('e3',$codeArr) ){
                unset($codeArr['e3']);
            }
            if(array_key_exists('e4',$codeArr) ){
                unset($codeArr['e4']);
            }
            $codeArr = array_merge($codeArr,(array)$this->getNaverPayClaimStatus());
        }

        return gd_isset($codeArr);
    }

    protected function  getNaverPayClaimStatus()
    {
        return [
            'g_naverpay_DelayProductOrder'=>'발송지연',
            'b_naverpay_RejectReturn'=>'반품거부',
            'b_naverpay_WithholdReturn'=>'반품보류', //b3
            'b_naverpay_ReleaseReturnHold'=>'반품보류해제',
            'e_naverpay_RejectExchange'=>'교환거부',
            'e_naverpay_WithholdExchange'=>'교환보류',
            'e_naverpay_ReleaseExchangeHold'=>'교환보류해제',
            'r_naverpay_ApproveCancelApplication'=>'취소승인 요청',
        ];
    }

    /**
     * 모든 주문 상태 출력
     *
     * @return mixed 주문 상태 혹은 리스트 전체 출력
     */
    protected function getAllOrderStatus()
    {
        foreach ($this->statusPolicy as $key => $val) {
            if ($key == 'autoCancel') {
                continue;
            }
            foreach ($val as $oKey => $oVal) {
                if (strlen($oKey) != 2) {
                    continue;
                }

                $codeArr[$oKey] = $oVal['admin'];
            }
        }

        return gd_isset($codeArr);
    }

    /**
     * 주문 상태 출력 (관리자 모드용)
     *
     * @param string $statusCode 주문상태 코드
     *
     * @return string 주문 상태 출력
     */
    public function getOrderStatusAdmin($statusCode = null)
    {
        return $this->_getOrderStatus($statusCode, 'admin');
    }

    /**
     * 주문 상태 출력 (사용자 모드용)
     *
     * @param string $statusCode    주문상태 코드
     * @param array  $strExclude    제외할 코드 (한자리씩 쉼표(,)로 구분 or 배열)
     * @param string $strSubExclude 제외할 서브코드 (두자리씩 쉼표(,)로 구분)
     *
     * @return string 주문 상태 출력
     */
    public function getOrderStatusUser($statusCode = null)
    {
        return $this->_getOrderStatus($statusCode, 'user');
    }

    /**
     * 특정 주문 상태 제외 출력
     *
     * @param array $statusCodeArray _getOrderStatus 의 return 값
     * @param array $excludeStatusCode orderStatus 의 앞 문자만
     *
     * @return string 주문 상태 출력
     */
    public function getExcludeOrderStatus($statusCodeArray, $excludeStatusCode = null)
    {
        if (gd_isset($statusCodeArray)) {
            foreach ($statusCodeArray as $key => $val) {
                $compareCode = substr($key , 0 ,1);
                if (in_array($compareCode, $excludeStatusCode)) {
                    unset($statusCodeArray[$key]);
                }
            }
        }

        return $statusCodeArray;
    }

    /**
     * 특정 주문 상태만 출력
     *
     * @param array $statusCodeArray _getOrderStatus 의 return 값
     * @param array $includeStatusCode orderStatus 의 앞 문자만
     *
     * @return string 주문 상태 출력
     */
    public function getIncludeOrderStatus($statusCodeArray, $includeStatusCode = null)
    {
        if (gd_isset($statusCodeArray)) {
            foreach ($statusCodeArray as $key => $val) {
                $compareCode = substr($key , 0 ,1);
                if (!in_array($compareCode, $includeStatusCode)) {
                    unset($statusCodeArray[$key]);
                }
            }
        }

        return $statusCodeArray;
    }

    /**
     * getUserHandleMode
     *
     * @param $handleMode
     * @param $handleFl
     *
     * @return string
     */
    public function getUserHandleMode($handleMode, $handleFl)
    {
        $codeArr = [
            'r' => __('신청'),
            'y' => __('승인'),
            'n' => __('거절'),
        ];

        return $this->statusStandardNm[$handleMode] . $codeArr[$handleFl];
    }

    /**
     * 주문 유형 출력
     * 관리자 리스트 검색내 체크박스에서 사용
     *
     * @param string $typeCode 주문상태 코드
     *
     * @return string 주문 상태 출력
     */
    public function getOrderType($typeCode = null)
    {
        $codeArr = [
            'pc'     => __('PC쇼핑몰'),
            'mobile' => __('모바일쇼핑몰'),
            'mobile-web' => __('WEB'),
            'mobile-app' => __('APP'),
            'write'  => __('수기주문'),
        ];

        if ($typeCode !== null && array_key_exists($typeCode, $codeArr)) {
            return [$typeCode => $codeArr[$typeCode]];
        }

        return gd_isset($codeArr);
    }

    /**
     * 처리상태 출력
     * 관리자 리스트 검색내 체크박스에서 사용
     *
     * @param string $typeCode 처리상태 코드 (반품/교환/환불신청 상태)
     *
     * @return string 주문 상태 출력
     */
    public function getUserHandleFl($typeCode = null)
    {
        $codeArr = [
            'r' => __('신청'),
            'y' => __('승인'),
            'n' => __('거절'),
        ];

        if ($typeCode !== null && array_key_exists($typeCode, $codeArr)) {
            return [$typeCode => $codeArr[$typeCode]];
        }

        return gd_isset($codeArr);
    }

    /**
     * 주문 채널 출력
     *
     * @param string $typeCode 주문상태 코드
     * @param null   $arrInclude
     * @param null   $arrExclude
     *
     * @return string 주문 상태 출력
     */
    public function getOrderChannel($typeCode = null)
    {
        $codeArr = [
            'shop'     => __('쇼핑몰'),
            'payco'    => __('페이코'),
            'naverpay' => __('네이버페이'),
        ];

        $dbUrl = \App::load('\\Component\\Marketing\\DBUrl');
        $paycoConfig = $dbUrl->getConfig('payco', 'config');
        if (empty($paycoConfig['shopKey']) === false) {
            $codeArr['paycoShopping'] = __('페이코쇼핑');
        }
        $codeArr['etc'] = __('기타');

        if (is_null($typeCode) !== true && array_key_exists($typeCode, $codeArr)) {
            return gd_isset($codeArr[$typeCode]);
        }

        return gd_isset($codeArr);
    }

    /**
     * 무통장 은행 정보 (사용자용)
     *
     * @param string $getMode 추출할 모드 (all - 사용중인 은행 정보, default - 대표은행, select - 사용중인 특정 sno 것 ($dataSno 필요))
     * @param int    $dataSno
     *
     * @return array|bool
     */
    public function getBankData($getMode, $dataSno = null)
    {
        // 사용할 필드
        $strField = implode(', ', DBTableField::setTableField('tableManageBank', ['bankName', 'accountNumber', 'depositor']));

        // 검색 조건
        $arrWhere = $arrBind = [];

        // 데이터 배열화
        $boolFetch = true;

        switch ($getMode) {
            // 사용중인 은행 정보
            case 'all':
                $arrWhere[] = 'useFl = ?';
                $this->db->bind_param_push($arrBind, 's', 'y');
                break;

            // 대표은행 정보
            case 'default':
                $arrWhere[] = 'useFl = ?';
                $arrWhere[] = 'defaultFl = ?';
                $this->db->bind_param_push($arrBind, 's', 'y');
                $this->db->bind_param_push($arrBind, 's', 'y');
                break;

            // 사용중인 특정 sno 정보
            case 'select':
                if (empty($dataSno) === true) {
                    return false;
                }

                $arrWhere[] = 'sno = ?';
                $arrWhere[] = 'useFl = ?';
                $this->db->bind_param_push($arrBind, 'i', $dataSno);
                $this->db->bind_param_push($arrBind, 's', 'y');
                break;

            default: // 그외 전부 return
                return false;
                break;

        }

        // 은행 정보
        $strSQL = 'SELECT ' . $strField . ' FROM ' . DB_MANAGE_BANK . ' WHERE ' . implode(' AND ', $arrWhere);
        $data = $this->db->query_fetch($strSQL, $arrBind);

        if (empty($data) === true) {
            return false;
        } else {
            return gd_htmlspecialchars_stripslashes($data);
        }
    }

    /**
     * 무통장 대표 은행 정보
     *
     * @return array|bool
     */
    public function getDefaultBankInfo()
    {
        // 대표 무통장 은행 정보
        $data = $this->getBankData('default');

        if (empty($data[0]) === true) {
            return false;
        } else {
            return $data[0];
        }
    }

    /**
     * 관리자 무통장 입금은행 정보
     *
     * @param string $dataSno sno
     * @param string $useFl   사용 여부 (null, y, n)
     *
     * @return array 입금 은행 정보
     */
    public function getBankInfo($dataSno = null, $useFl = null)
    {
        if (is_null($dataSno)) {
            $arrWhere[] = '1';
            $arrBind = null;
            $boolFetch = true;
        } else {
            $arrWhere[] = 'sno = ?';
            $this->db->bind_param_push($arrBind, 'i', $dataSno);
            $boolFetch = false;
        }
        if (is_null($useFl) === false) {
            $arrWhere[] = 'useFl = ?';
            $this->db->bind_param_push($arrBind, 's', $useFl);
        }

        $strSQL = 'SELECT sno, ' . implode(', ', DBTableField::setTableField('tableManageBank')) . ' FROM ' . DB_MANAGE_BANK . ' WHERE ' . implode(' AND ', $arrWhere);
        $data = $this->db->query_fetch($strSQL, $arrBind, $boolFetch);

        return gd_htmlspecialchars_stripslashes($data);
    }

    /**
     * 상태 수정 - 주문 상품 테이블 및 주문 테이블 주문 상태 처리
     * 해당 부분을 직접 불러와서 처리하면 주문상태간 변경 여부 체크가 되지 않는다.
     * 반드시 orderAdmin의 updateStatusPreprocess를 호출해서 사용한다.
     *
     * @param string $orderNo 주문 번호
     * @param array  $arrData 상태 정보 (changeStatus|orderStatus|sno|reason
     *
     * @throw Exception
     * @return bool
     */
    protected function setStatusChange($orderNo, $arrData, $autoProcess = false)
    {
        if (empty($arrData['changeStatus'])) {
            return false;
        } else {
            if (strlen($arrData['changeStatus']) != 2) {
                return false;
            }
        }

        // 현재 상태 수정 처리
        $arrWhere[] = 'sno = ?';
        $arrWhere[] = 'orderNo = ?';
        foreach ($arrData['sno'] as $key => $val) {
            // 주문 로그 저장
            $logCode01 = $this->getOrderStatusAdmin($arrData['orderStatus'][$key]) . '(' . $arrData['orderStatus'][$key] . ')';
            $logCode02 = $this->getOrderStatusAdmin($arrData['changeStatus']) . '(' . $arrData['changeStatus'] . ')';
            $reason = explode(STR_DIVISION, $arrData['reason']);
            if (count($reason) > 1) {
                $reason = $reason[0];
                if (empty($reason[1]) === false) {
                    $reason .= ' (' . $reason[1] . ')';
                }
            }

            // bind 데이터
            $arrBind = [];
            $arrBind['param'][] = 'orderStatus = ?';
            $this->db->bind_param_push($arrBind['bind'], 's', $arrData['changeStatus']);

            // 입금 확인 일자
            if (substr($arrData['changeStatus'], 0, 1) == 'p') {
                $arrBind['param'][] = 'paymentDt = ?';
                $this->db->bind_param_push($arrBind['bind'], 's', date('Y-m-d H:i:s'));
            } else if (substr($arrData['changeStatus'], 0, 1) == 'o') {
                $arrBind['param'][] = 'paymentDt = ?';
                $this->db->bind_param_push($arrBind['bind'], 's', '');
            } else {

            }

            // 배송일자
            if (substr($arrData['changeStatus'], 0, 1) == 'd') {
                // 배송중
                if ($arrData['changeStatus'] == 'd1') {
                    $arrBind['param'][] = 'deliveryDt = ?';
                    $this->db->bind_param_push($arrBind['bind'], 's', date('Y-m-d H:i:s'));
                }

                // 배송완료 일자
                if ($arrData['changeStatus'] == 'd2') {
                    $arrBind['param'][] = 'deliveryCompleteDt = ?';
                    $this->db->bind_param_push($arrBind['bind'], 's', date('Y-m-d H:i:s'));
                }
            }

            // 구매확정 일자
            if (substr($arrData['changeStatus'], 0, 1) == 's') {
                $arrBind['param'][] = 'finishDt = ?';
                $this->db->bind_param_push($arrBind['bind'], 's', date('Y-m-d H:i:s'));
            }

            // 취소완료일자
            if (substr($arrData['changeStatus'], 0, 1) == 'c') {
                $arrBind['param'][] = 'cancelDt = ?';
                $this->db->bind_param_push($arrBind['bind'], 's', date('Y-m-d H:i:s'));
            }

            // 교환추가상품 배송일자
            if (substr($arrData['changeStatus'], 0, 1) == 'z') {
                // 입금확인
                if($arrData['changeStatus'] === 'z1'){
                    $arrBind['param'][] = 'paymentDt = ?';
                    $this->db->bind_param_push($arrBind['bind'], 's', '');
                }
                else {
                    if($arrData['orderStatus'][$key] === 'z1'){
                        $arrBind['param'][] = 'paymentDt = ?';
                        $this->db->bind_param_push($arrBind['bind'], 's', date('Y-m-d H:i:s'));
                    }
                }

                // 배송중
                if ($arrData['changeStatus'] == 'z3') {
                    $arrBind['param'][] = 'deliveryDt = ?';
                    $this->db->bind_param_push($arrBind['bind'], 's', date('Y-m-d H:i:s'));
                }

                // 배송완료
                if ($arrData['changeStatus'] == 'z4') {
                    $arrBind['param'][] = 'deliveryCompleteDt = ?';
                    $this->db->bind_param_push($arrBind['bind'], 's', date('Y-m-d H:i:s'));
                }

                // 구매확정
                if ($arrData['changeStatus'] == 'z5') {
                    $arrBind['param'][] = 'finishDt = ?';
                    $this->db->bind_param_push($arrBind['bind'], 's', date('Y-m-d H:i:s'));
                }
            }

            // 배송일자
            $this->db->bind_param_push($arrBind['bind'], 'i', $val);
            $this->db->bind_param_push($arrBind['bind'], 's', $orderNo);
            $this->db->set_update_db(DB_ORDER_GOODS, $arrBind['param'], implode(' AND ', $arrWhere), $arrBind['bind']);
            unset($arrBind);

            $this->orderLog($orderNo, $val, $logCode01, $logCode02, implode(' | ', $reason), false, $autoProcess);
        }
        unset($arrWhere);

        // order 테이블의 전체 진행 상태 갱신 처리
        $strSQL = 'SELECT orderStatus FROM ' . DB_ORDER_GOODS . ' WHERE orderNo = ? GROUP BY orderStatus ORDER BY orderStatus ASC';
        $arrBind = [
            's',
            $orderNo,
        ];
        $getData = $this->db->query_fetch($strSQL, $arrBind);
        $getDataCnt = count($getData);

        if ($getDataCnt == 1) {
            $orderStatus = $getData[0]['orderStatus'];
        } else {
            $orderStatus = 'o1'; // 주문 코드 (o1 를 기본 값 처리)
            $standardCode = [
                'f',
                'o',
                'c',
                'b',
                'e',
                'r',
                'p',
                'g',
                'd',
                's',
            ]; // 기준 코드임 (실패, 주문, 취소, 반품, 교환, 환불, 입금, 상품, 배송) 순으로 있는 것 기준으로 처리
            $codeOrder = 0; // 코드 순서
            foreach ($getData as $key => $val) {
                // 상태 코드별 수량
                gd_isset($cnt[$val['orderStatus']], 0);
                $cnt[$val['orderStatus']] = $cnt[$val['orderStatus']] + 1;

                // 상태 코드를 설정
                $codePrefix = substr($val['orderStatus'], 0, 1);
                $tmp[$codePrefix][$val['orderStatus']] = true;

                // 기준 코드를 체크를 해서 기준 코드의 키값 보다 코드 순서보다 크면 코드 순서를 기준코드로 사용
                if (array_search($codePrefix, $standardCode) >= $codeOrder) {
                    $codeOrder = array_search($codePrefix, $standardCode);
                }
                arsort($tmp[$standardCode[$codeOrder]]); // 키를 기준으로 내림차순 정렬
                $sortCode = $tmp[$standardCode[$codeOrder]];
                $orderStatus = ArrayUtils::lastKey($sortCode); // 마지막 키가 주문 코드
            }
        }

        // 주문테이블내 orderStatus 추가
        $arrBind = [];
        $arrBind['param'][] = 'orderStatus = ?';
        $this->db->bind_param_push($arrBind['bind'], 's', $orderStatus);

        // 입금 확인 일자 (통계때문에 필요한 부분) - 환불, 반품시 미적용
        if (in_array(substr($arrData['changeStatus'], 0, 1), ['r', 'b']) === false) {
            if (substr($orderStatus, 0, 1) == 'p') {
                $arrBind['param'][] = 'paymentDt = ?';
                $this->db->bind_param_push($arrBind['bind'], 's', date('Y-m-d H:i:s'));
            } else if (substr($orderStatus, 0, 1) == 'o') {
                $arrBind['param'][] = 'paymentDt = ?';
                $this->db->bind_param_push($arrBind['bind'], 's', '');
            }
        }

        $this->db->bind_param_push($arrBind['bind'], 's', $orderNo);
        $this->db->set_update_db(DB_ORDER, $arrBind['param'], 'orderNo = ?', $arrBind['bind']);

        unset($arrBind);
    }

    /**
     * 상태 수정 - 주문
     * 입금 상태의 처리사항 (마일리지/쿠폰 차감 , 재고차감)
     *
     * @param string  $orderNo      주문 번호
     * @param array   $arrData      상태 및 주문상품NO 정보
     * @param boolean $statusChange 상태 수정여부
     */
    public function statusChangeCodeO($orderNo, $arrData, $statusChange = true)
    {
        if (substr($arrData['changeStatus'], 0, 1) != 'o') {
            return false;
        }

        // 결제방식 확인해서 무통장/에스크로(가상계좌)/네이버페이  결제방식이면 구매카운트 추가
        $thisSettleKind = $this->getOnlyOrderSettleKind($orderNo);
        if (($thisSettleKind['channel'] == 'naverpay' && !in_array($thisSettleKind['kind'], array('fc'))) || (in_array($thisSettleKind['kind'], array('gb', 'pv', 'ev', 'fv')))) {
            if (($statusChange ==  false && $thisSettleKind['kind'] == 'gb') || $statusChange == true) { // 특별한경우가 아니면 statusChangeCodeO에서 false는 최초 주문시에만 적용된다
                $this->setCountOrderGoods($orderNo, $arrData, $thisSettleKind); //$this->구매카운트 추가
            }
        }
        //setCountOrderGoods 결과에서 카운트도 체크해서 카운트초과면 throw new AlertRedirectException(__('구매 불가 상품이 포함되어 있으니 장바구니에서 확인 후 다시 주문해주세요.'), null, null, '../order/cart.php', 'top');

        // 현재 상태 수정 처리
        if ($statusChange === true) {
            $this->setStatusChange($orderNo, $arrData);
        }

        // 첫주문 체크 후 저장 (데이터 저장 후 조회하여 실시)
        $this->updateFirstSale($orderNo);

        // 주문테이블내 저장된 주문상태 정보 가져오기
        $currentStatusPolicy = $this->getOrderCurrentStatusPolicy($orderNo);

        // 예치금 차감
        $this->setMinusDepositVariation($orderNo);

        // 쿠폰 할인 (사용 쿠폰 처리)
        $this->setMinusCouponVariation($orderNo);

        // 마일리지 적립 쿠폰 사용상태 order 변경
        $this->setMemberMileageCouponState($orderNo, $arrData);

        // 재고차감 (사은품, 추가상품, 상품)
        if (in_array('o', $currentStatusPolicy['sminus'])) {
            $this->setGoodsStockCutback($orderNo, $arrData['sno']);
        }

        // 마일리지 차감 체크 (사용 마일리지 차감)
        $this->setMinusMileageVariation($orderNo);

        // 메일 / SMS 전송
        $this->sendOrderInfo(Code::ORDER, 'all', $orderNo);
        $this->sendOrderInfo(Code::ACCOUNT, 'sms', $orderNo);
    }

    /**
     * 상태 수정 - 입금
     * 입금 상태의 처리사항 (마일리지/쿠폰 적립 , 마일리지/쿠폰 차감 , 재고차감, 현금영수증 자동발행)
     *
     * @param string $orderNo 주문 번호
     * @param array  $arrData 상태 및 주문상품NO 정보
     */
    public function statusChangeCodeP($orderNo, $arrData)
    {
        // 해당 주문내 첫 결제확인 처리시 주문관련 처리 프로세스를 실행할지에 대한 여부
        $statusLogExistFl = $this->getOrderLogChangeFl($orderNo);
        if($statusLogExistFl === true){
            // 결제확인 내역 존재
            $orderFunctionProcess = false;
        }
        else {
            // 첫 결제확인
            $orderFunctionProcess = true;
        }

        // 결제방식 확인해서 무통장/에스크로(가상계좌)/네이버페이 결제방식이 아니면 구매카운트 추가
        $thisSettleKind = $this->getOnlyOrderSettleKind($orderNo);
        if (($thisSettleKind['channel'] != 'naverpay' && !in_array($thisSettleKind['kind'], array('gb', 'pv', 'ev', 'fv'))) || ($thisSettleKind['channel'] == 'naverpay' && in_array($thisSettleKind['kind'], array('fc','fb','fp','fh')))) {
            if ($orderFunctionProcess == true || $thisSettleKind['channel'] == 'naverpay') { // 첫결제일때만 && 네이버페이는 무조건
                $this->setCountOrderGoods($orderNo, $arrData, $thisSettleKind); //$this->구매카운트 추가
            }
        }

        // 판매인기 갱신 (상태변경전에 들어가야 함)
        $goods = \App::load('\\Component\\Goods\\Goods');
        foreach ($arrData['sno'] as $key => $sno) {
            if ($this->isSetOrderStatus($orderNo, $sno, 'p') === false) {
                $goods->setOrderCount($arrData['sno']);
                // es_goods.orderGoodsCnt 갱신
                $goods->setOrderGoodsCount($sno);
            }
        }

        // 현재 상태 수정 처리
        $this->setStatusChange($orderNo, $arrData);

        // 첫주문 체크 후 저장 (데이터 저장 후 조회하여 실시)
        if($orderFunctionProcess === true) {
            $this->updateFirstSale($orderNo);
        }

        // 주문테이블내 저장된 주문상태 정보 가져오기
        $currentStatusPolicy = $this->getOrderCurrentStatusPolicy($orderNo);

        // 예치금 차감
        $this->setMinusDepositVariation($orderNo);

        // 쿠폰 할인 (사용 쿠폰 처리)
        if($orderFunctionProcess === true){
            $this->setMinusCouponVariation($orderNo);
        }

        // 쿠폰 마일리지 적립 (사용 쿠폰 처리) - (결제완료로 직접 들어오는 경우에 대한 처리를 위해)
        if (in_array('p', $currentStatusPolicy['cplus'])) {
            $this->setPlusCouponVariation($orderNo, $arrData);
        }

        // 마일리지 적립 쿠폰 사용상태 order 변경
        $this->setMemberMileageCouponState($orderNo, $arrData);

        // 재고차감 (결제완료로 직접 들어오는 경우에 대한 처리를 위해)
        if (in_array('p', $currentStatusPolicy['sminus'])) {
            $this->setGoodsStockCutback($orderNo, $arrData['sno']);
        }

        if($orderFunctionProcess === true){
            $cashReceipt = \App::load('\\Component\\Payment\\CashReceipt');
            $cashReceipt->sendPgCashReceipt($orderNo, 'auto', 'approval');
        }

        // 마일리지 차감 체크 (사용 마일리지 차감)
        $this->setMinusMileageVariation($orderNo);

        // 마일리지 지급 체크 (지급 예외 정책 적용 처리)
        $this->setPlusMileageVariation($orderNo, $arrData);

        if($orderFunctionProcess === true){
            // 안심번호 신청
            $safeNumber = \App::load('Component\\Service\\SafeNumber');
            $safeNumber->setUseSafeNumber($orderNo);

            // 메일 / SMS 전송
            $this->sendOrderInfo(Code::ORDER, 'mail', $orderNo);
            $this->sendOrderInfo(Code::INCASH, 'all', $orderNo);
        }
    }

    /**
     * 상태 수정 - 상품
     * 상품 상태의 처리사항 (현금영수증 자동발행)
     *
     * @param string $orderNo 주문 번호
     * @param array  $arrData 상태 정보
     */
    public function statusChangeCodeG($orderNo, $arrData)
    {
        /* 상품 상태의 처리사항 (없음) */

        // 현재 상태 수정 처리
        $this->setStatusChange($orderNo, $arrData);

        // 주문테이블내 저장된 주문상태 정보 가져오기
        $currentStatusPolicy = $this->getOrderCurrentStatusPolicy($orderNo);

        // 쿠폰 마일리지 적립 (사용 쿠폰 처리) - (결제완료로 직접 들어오는 경우에 대한 처리를 위해)
        if (in_array('g', $currentStatusPolicy['cplus'])) {
            $this->setPlusCouponVariation($orderNo, $arrData);
        }

        // 재고차감 (결제완료로 직접 들어오는 경우에 대한 처리를 위해)
        if (in_array('g', $currentStatusPolicy['sminus'])) {
            $this->setGoodsStockCutback($orderNo, $arrData['sno']);
        }

        // 마일리지 지급 체크 (지급 예외 정책 적용 처리)
        $this->setPlusMileageVariation($orderNo, $arrData);

        // 현금영수증 자동 발행
        $cashReceipt = \App::load('\\Component\\Payment\\CashReceipt');
        $cashReceipt->sendPgCashReceipt($orderNo, 'auto', 'approval');
    }

    /**
     * 상태 수정 - 배송
     * 배송 상태의 처리사항 (마일리지/쿠폰 적립 , 재고차감, 에스크로 배송등록, 현금영수증 자동발행)
     *
     * @param string $orderNo 주문 번호
     * @param array  $arrData 상태 정보
     * @param boolean $statusChange 상태 수정여부
     * @param string $useVisit 방문수령여부
     */
    public function statusChangeCodeD($orderNo, $arrData, $statusChange = true, $useVisit = null)
    {
        // 주문테이블내 저장된 주문상태 정보 가져오기
        $currentStatusPolicy = $this->getOrderCurrentStatusPolicy($orderNo);

        // 마일리지 지급 체크 (지급 예외 정책 적용 처리)
        $this->setPlusMileageVariation($orderNo, $arrData);

        // 쿠폰 마일리지 지급 체크
        if ($arrData['changeStatus'] == 'd2' && in_array('d', $currentStatusPolicy['cplus'])) {
            $this->setPlusCouponVariation($orderNo, $arrData);
        }

        // 재고차감
        if ($arrData['changeStatus'] == 'd2' && in_array('d', $currentStatusPolicy['sminus'])) {
            $this->setGoodsStockCutback($orderNo, $arrData['sno']);
        }

        // 현재 상태 수정 처리
        $this->setStatusChange($orderNo, $arrData);

        // 에스크로 배송등록 처리
        $escrow = \App::load(\Component\Payment\Escrow::class);
        $escrow->sendPgEscrowDelivery($orderNo);

        // 현금영수증 자동 발행
        $cashReceipt = \App::load('\\Component\\Payment\\CashReceipt');
        $cashReceipt->sendPgCashReceipt($orderNo, 'auto', 'approval');

        // 메일 / SMS 전송 (배송 관련)
        if ($arrData['changeStatus'] === 'd1') {
            if ($useVisit === null || $useVisit != 'y') {
                $this->sendOrderInfo(Code::DELIVERY, 'all', $orderNo);
                $this->sendOrderInfo(Code::INVOICE_CODE, 'sms', $orderNo);
            }
        }
        else if ($arrData['changeStatus'] === 'd2') {   // SMS 전송 (배송 완료시)
            if ($useVisit === null || $useVisit != 'y') {
                $this->sendOrderInfo('DELIVERY_COMPLETED', 'sms', $orderNo);
            }
        }
        else {  //배송상태에서 추가주문일대 배솽완료일자만 업데이트 (추후 개선)
            foreach($arrData['sno'] as $sno){
                $arrBind = [];
                $this->db->bind_param_push($arrBind, 'i', $sno);
                $affectedRows = $this->db->set_update_db(DB_ORDER_GOODS, " deliveryCompleteDt = now() ", ' sno = ? ', $arrBind );
            }

        }
    }

    /**
     * 상태 수정 - 구매확정
     * 구매확정 상태의 처리사항 (마일리지/쿠폰 적립 , 재고차감, 현금영수증 자동발행)
     * 구매확정의 경우 배송 중에서 배송완료를 거치지 않고 구매확정으로 건너뛸 수 있는 조건이 발생할 수 있어 무조건 처리를 한다.
     *
     * @param string $orderNo 주문 번호
     * @param array  $arrData 상태 정보
     */
    public function statusChangeCodeS($orderNo, $arrData)
    {
        // 배송완료를 건너뛰는 경우가 있을 수 있음으로 주문정책의 조건을 따르지 않는다.
        // 쿠폰 마일리지 지급 체크
        $this->setPlusCouponVariation($orderNo, $arrData);

        // 재고차감 (배송완료를 건너뛰는 경우 발생할 수 있는 문제를 위해 다시 체크)
        $this->setGoodsStockCutback($orderNo, $arrData['sno']);

        // 마일리지 지급 체크 (지급 예외 정책 적용 처리)
        $this->setPlusMileageVariation($orderNo, $arrData);

        // 회원 구매금액 갱신
        $this->setOrderPriceMember($orderNo, $arrData['sno']);

        // 현재 상태 수정 처리
        $this->setStatusChange($orderNo, $arrData);

        // 현금영수증 자동 발행
        $cashReceipt = \App::load('\\Component\\Payment\\CashReceipt');
        $cashReceipt->sendPgCashReceipt($orderNo, 'auto', 'approval');
    }

    /**
     * 상태 수정 - 취소
     * 취소 상태의 처리사항 (마일리지/쿠폰/재고 모두 설정과 관련없이 복원되어야 한다)
     *
     * @param string  $orderNo 주문 번호
     * @param array   $arrData 상태 정보
     * @param boolean $sendFl  메일 / SMS 전송 여부
     */
    public function statusChangeCodeC($orderNo, $arrData, $sendFl = true)
    {
        // 예치금 복원 체크
        $this->setMinusDepositRestore($orderNo);// 사용한 예치금 복원

        // 마일리지 복원 체크
        $this->setMinusMileageRestore($orderNo);// 사용한 마일리지 복원
        $this->setPlusMileageRestore($orderNo);// 적립된 마일리지 복원

        // 쿠폰 복원 체크 (주문상태 정책에서 필수로 체크되었기 때문에 무조건 실행)
        $this->setMinusCouponRestore($orderNo);// 사용한 쿠폰 복원
        $this->setPlusCouponRestore($orderNo);// 적립된 쿠폰 복원

        // 주문테이블내 저장된 주문상태 정보 가져오기
        $currentStatusPolicy = $this->getOrderCurrentStatusPolicy($orderNo);

        // 재고 복원 체크
        if (in_array('c1', $currentStatusPolicy['srestore'])) {
            $this->setGoodsStockRestore($orderNo, $arrData['sno']);// 재고 복원
        }

        // 현재 상태 수정 처리
        $this->setStatusChange($orderNo, $arrData);

        // sms 개선(고객요청에 의한 취소 처리 시 금액 전달을 위함, 입금대기 리스트 일괄 처리 시 cancelPrice금액 settlePrice로)

        $claimPrice['cancelPrice'] = $arrData['cancelPriceC1'];
        if($arrData['changeStatus'] == 'c4'){
            $claimPrice['cancelPrice'] = $arrData['customerCancelPrice'];
        }

        // 메일 / SMS 전송
        if ($sendFl === true) {
            $this->sendOrderInfo(Code::CANCEL, 'sms', $orderNo, null, $claimPrice);
        }
    }

    /**
     * 상태 수정 - 실패
     *
     * @param string $orderNo 주문 번호
     * @param array  $arrData 상태 정보
     */
    public function statusChangeCodeF($orderNo, $arrData)
    {
        /* 실패 상태의 처리사항 (없음) */
        $coupon = App::load(\Component\Coupon\Coupon::class);

        // 현재 상태 수정 처리
        $this->setStatusChange($orderNo, $arrData);
    }

    /**
     * 상태 수정 - 반품
     *
     * @param string $orderNo 주문 번호
     * @param array  $arrData 상태 정보
     */
    public function statusChangeCodeB($orderNo, $arrData)
    {
        // 주문테이블내 저장된 주문상태 정보 가져오기
        $currentStatusPolicy = $this->getOrderCurrentStatusPolicy($orderNo);

        // 재고 복원
        if ($arrData['changeStatus'] == 'b4' && in_array('b4', $currentStatusPolicy['srestore'])) {
            $this->setGoodsStockRestore($orderNo, $arrData['sno']);
        }

        // 현재 상태 수정 처리
        $this->setStatusChange($orderNo, $arrData);
    }

    /**
     * 상태 수정 - 교환
     *
     * @param string $orderNo 주문 번호
     * @param array  $arrData 상태 정보
     */
    public function statusChangeCodeE($orderNo, $arrData)
    {
        // 주문테이블내 저장된 주문상태 정보 가져오기
        $currentStatusPolicy = $this->getOrderCurrentStatusPolicy($orderNo);

        if ($arrData['changeStatus'] == 'e5'){
            if(in_array('e5', $currentStatusPolicy['srestore'])){
                // 재고 복원
                $this->setGoodsStockRestore($orderNo, $arrData['sno']);
            }

            $orderGoodsNoArr = (count($arrData['sno']) > 0) ? $arrData['sno'] : null;

            // 예치금 복원 체크
            $this->setMinusDepositRestore($orderNo, $orderGoodsNoArr);// 사용한 예치금 복원

            // 마일리지 복원 체크
            $this->setMinusMileageRestore($orderNo, $orderGoodsNoArr);// 사용한 마일리지 복원
            $this->setPlusMileageRestore($orderNo, $orderGoodsNoArr);// 적립된 마일리지 복원
        }

        // 현재 상태 수정 처리
        $this->setStatusChange($orderNo, $arrData);
    }

    /**
     * 상태 수정 - 교환추가
     *
     * @param string $orderNo 주문 번호
     * @param array  $arrData 상태 정보
     */
    public function statusChangeCodeZ($orderNo, $arrData)
    {
        // 주문테이블내 저장된 주문상태 정보 가져오기
        $currentStatusPolicy = $this->getOrderCurrentStatusPolicy($orderNo);

        //교환추가완료시
        if ($arrData['changeStatus'] == 'z5'){
            // 쿠폰 마일리지 지급 체크
            $this->setPlusCouponVariation($orderNo, $arrData);

            // 마일리지 지급 체크 (지급 예외 정책 적용 처리)
            $this->setPlusMileageVariation($orderNo, $arrData);

            // 회원 구매금액 갱신
            if(getType($arrData['sno']) === 'array' && count($arrData['sno']) > 0){
                foreach($arrData['sno'] as $key => $orderGoodsSno){
                    if($this->isSetOrderStatus($orderNo, $orderGoodsSno, 'z5') === false){
                        $this->setOrderPriceMember($orderNo, $orderGoodsSno);
                    }
                }
            }
            else {
                if($this->isSetOrderStatus($orderNo, $arrData['sno'], 'z5') === false){
                    $this->setOrderPriceMember($orderNo, $arrData['sno']);
                }
            }

            if(in_array('z', $currentStatusPolicy['sminus'])){
                // 재고 차감
                $this->setGoodsStockCutback($orderNo, $arrData['sno']);
            }

            // 현금영수증 자동 발행
            $cashReceipt = \App::load('\\Component\\Payment\\CashReceipt');
            $cashReceipt->sendPgCashReceipt($orderNo, 'auto', 'approval');
        }

        // 현재 상태 수정 처리
        $this->setStatusChange($orderNo, $arrData);
    }

    /**
     * 상태 수정 - 환불
     * 환불 상태의 처리사항 (마일리지/쿠폰/재고 복원)
     *
     * @param string  $orderNo 주문 번호
     * @param array   $arrData 상태 정보
     * @param boolean $restoreFl
     */
    public function statusChangeCodeR($orderNo, $arrData, $restoreFl = false, $useVisit = null, $autoProcess = false)
    {
        // 주문테이블내 저장된 주문상태 정보 가져오기
        $currentStatusPolicy = $this->getOrderCurrentStatusPolicy($orderNo);

        // 재고 복원 체크 (환불 완료시에만 처리함)
        if ($arrData['changeStatus'] == 'r3' && in_array('r3', $currentStatusPolicy['srestore']) && $restoreFl === true) {
            \Logger::debug(__METHOD__ . ' 재고차감');
            $this->setGoodsStockRestore($orderNo, $arrData['sno']);
        }

        // 현재 상태 수정 처리
        $this->setStatusChange($orderNo, $arrData, $autoProcess);

        // 환불완료에 따른 현금영수증 발급취소 및 메일 / SMS 전송
        if ($arrData['changeStatus'] == 'r3') {
            // *** 현금영수증 재발행으로 인한 부분 환불 또는 전체 환불 확인
            // 주문 기본 정보
            $arrExclude = [
                'orderIp',
                'orderPGLog',
                'orderDeliveryLog',
                'orderAdminLog',
            ];

// $arrData['sno']의 카운트와 해당주문번호의 품목카운트가 다르면 REPAYPART 동일하면 REPAY로 보내도록 처리
            $aOrderGoods = $this->getOrderGoods($orderNo, null, null, null, ['orderNo'], ['settleKind']);
            $iOrderRefundCount = $this->getOrderRefundCount($orderNo);
            $aRequest = \Request::post()->toArray();

            // 환불하는 상품카운트수와 전체 ordergoods 카운트수가 같거나 해당주문번호의 환불처리된ordergoods카운트수와 전체 ordergoods카운트수가 같으면 환불(REPAY)
            if (count($arrData['sno']) == count($aOrderGoods) || $iOrderRefundCount == count($aOrderGoods)) {
                $sSendType = 'REPAY';
            } else {
                // 기본결제가 카드결제(settlekind값pc)이고 환불처리방식이 PG환불이면 카드부분취소 아니면 전체환불
                if ($aOrderGoods[0]['settleKind'] == 'pc' && $aRequest['info']['refundMethod'] == 'PG환불') {
                    $sSendType = 'REPAYPART';
                } else {
                    $sSendType = 'REPAY';
                }
            }

            // sms 개선_부분 환불시 금액 전달하기 위함
            $claimPrice['gdRefundPrice'] = $arrData['refundCompletePrice'];
            $smsCnt = $arrData['smsCnt'];
            $this->sendOrderInfo($sSendType, 'sms', $orderNo, null, $claimPrice, $smsCnt);

            $getData = $this->getOrderData($orderNo, $arrExclude);
            $ordStatus = substr($getData['orderStatus'],0,1);

            // 현금영수증 재발행 기능에 의한 전체 환불일경우 발급취소
            if($ordStatus == 'r') {
                $cashReceipt = \App::load('\\Component\\Payment\\CashReceipt');
                $isStatusFl = $cashReceipt->getCashReceiptData($orderNo);

                // 해당 주문건의 현금영수증 발행 상태가 발행요청인경우 발행 상태값이 r임으로 adminChk값 필요함.
                if($isStatusFl['statusFl'] == 'r'){
                    $cashReceipt->sendPgCashReceipt($orderNo, 'auto', 'cancel', __('환불완료에 따른 현금영수증 발급취소'), '', 'y');
                }else { // 해당 주문건의 현금영수증 발행 상태가 발행완료일때는 발행 상태값이 y임으로 adminChk값 필요 없음.
                    $cashReceipt->sendPgCashReceipt($orderNo, 'auto', 'cancel', __('환불완료에 따른 현금영수증 발급취소'), '', '');
                }
            }
        }
    }

    /**
     * 주문건 전체 상품에 대한 예치금 차감
     * 주문서 작성시 사용한 예치금이 있고
     *
     * @param string $orderNo 주문 번호
     *
     * @return boolean 성공여부
     */
    public function setMinusDepositVariation($orderNo)
    {
        $orderGoodsNo = [];
        $totalUseDeposit = 0;
        foreach ($this->getOrderGoodsData($orderNo, null, null, null, null, false) as $key => $val) {
            if ($val['minusDepositFl'] == 'n' && $val['minusRestoreDepositFl'] == 'n' && $val['memNo'] > 0) {
                $totalUseDeposit += $val['totalDivisionUseDeposit'];
                $orderGoodsNo[] = $val['sno'];
            }
        }

        // 조건이 충족한 경우 회원 마일리지 차감 후 주문서 업데이트
        if ($totalUseDeposit > 0) {
            /** @var \Bundle\Component\Deposit\Deposit $deposit */
            $deposit = \App::load('\\Component\\Deposit\\Deposit');
            if ($deposit->setMemberDeposit($val['memNo'], ($totalUseDeposit * -1), Deposit::REASON_CODE_GROUP . Deposit::REASON_CODE_GOODS_BUY, 'o', $orderNo)) {
                $orderData['minusDepositFl'] = 'y';
                $orderData['minusRestoreDepositFl'] = 'n';
                $compareField = array_keys($orderData);
                $arrBind = $this->db->get_binding(DBTableField::tableOrderGoods(), $orderData, 'update', $compareField);
                $this->db->set_update_db(DB_ORDER_GOODS, $arrBind['param'], 'sno IN (\'' . implode('\',\'', $orderGoodsNo) . '\')', $arrBind['bind']);
                unset($arrBind);

                return true;
            }
        }

        return false;
    }

    /**
     * 주문건 전체 상품에 대한 예치금 차감 복원
     *
     * @param string $orderNo 주문 번호
     * @param array $targetOrderGoodsNo 주문상품번호
     *
     * @return bool
     **/
    protected function setMinusDepositRestore($orderNo, $targetOrderGoodsNoArr = null)
    {
        $orderGoodsNo = [];
        $totalUseDeposit = 0;
        foreach ($this->getOrderGoodsData($orderNo, $targetOrderGoodsNoArr, null, null, null, false) as $key => $val) {
            if ($val['minusDepositFl'] == 'y' && $val['minusRestoreDepositFl'] == 'n' && $val['memNo'] > 0) {
                $totalUseDeposit += $val['totalDivisionUseDeposit'];
                $orderGoodsNo[] = $val['sno'];
            }
        }

        // 조건이 충족한 경우 회원 마일리지 차감 후 주문서 업데이트
        if ($totalUseDeposit > 0) {
            /** @var \Bundle\Component\Deposit\Deposit $deposit */
            $deposit = \App::load('\\Component\\Deposit\\Deposit');
            if ($this->changeStatusAuto) {
                $deposit->setSmsReserveTime(date('Y-m-d 08:00:00', strtotime('now')));
            }
            if ($deposit->setMemberDeposit($val['memNo'], $totalUseDeposit, Deposit::REASON_CODE_GROUP . Deposit::REASON_CODE_ORDER_CANCEL, 'o', $orderNo)) {
                $orderData['minusDepositFl'] = 'n';
                $orderData['minusRestoreDepositFl'] = 'y';
                $compareField = array_keys($orderData);
                $arrBind = $this->db->get_binding(DBTableField::tableOrderGoods(), $orderData, 'update', $compareField);
                $this->db->set_update_db(DB_ORDER_GOODS, $arrBind['param'], 'sno IN (\'' . implode('\',\'', $orderGoodsNo) . '\')', $arrBind['bind']);
                unset($arrBind);

                return true;
            }
        }

        return false;
    }

    /**
     * 주문 당시 설정되었던 마일리지 정책을 가져온다.
     *
     * @param integer $orderNo 주문번호
     *
     * @return mixed
     */
    public function getOrderCurrentMileagePolicy($orderNo)
    {
        // 주문 상품 데이타
        $arrInclude = [
            'mileagePolicy',
        ];
        $arrField = DBTableField::setTableField('tableOrder', $arrInclude);
        $strSQL = 'SELECT ' . implode(', ', $arrField) . ' FROM ' . DB_ORDER . ' WHERE orderNo = ?';
        $arrBind = [
            's',
            $orderNo,
        ];
        $getData = $this->db->query_fetch($strSQL, $arrBind, false);
        $data = json_decode(gd_htmlspecialchars_stripslashes(array_shift($getData)), true);

        return $data;
    }

    /**
     * 마일리지 지급
     * 지급예외 조건이 y이면서 사용한 마일리지가 있는 경우
     *
     * @param string $orderNo 주문 번호
     * @param arrau  $arrData 주문상태 변경 데이터
     *
     * @return bool 성공여부
     */
    public function setPlusMileageVariation($orderNo, $arrData)
    {
        // 기본설정 > 주문상태 > 혜택지급시점 정의가 변경되면 이 부분도 변경 필요
        $currentStatusPolicy = $this->getOrderCurrentStatusPolicy($orderNo);

        // 주문 시 마일리지 지급 설정의 유예기간 처리
        $currentMileagePolicy = $this->getOrderCurrentMileagePolicy($orderNo);

        $orderGoodsNo = [];
        $totalMileage = 0;
        foreach ($this->getOrderGoodsData($orderNo, null, null, null, null, false) as $key => $val) {
            // 마일리지 지급 조건과 현재 상태 비교 후 계산
            if (in_array($val['sno'], $arrData['sno'])){
                $changeStatusFlag = substr($arrData['changeStatus'], 0, 1);

                if($changeStatusFlag === 'z'){
                    //교환추가 주문상태는 교환추가완료 상태에서만 마일리지 지급 가능
                    if($arrData['changeStatus'] !== 'z5'){
                        return false;
                    }
                }
                else {
                    if(!in_array($changeStatusFlag, $currentStatusPolicy['mplus'])){
                        return false;
                    }
                    if ($changeStatusFlag == 'd' && $arrData['changeStatus'] != 'd2') {
                        return false;
                    }
                }

                // 쿠폰적립은 회원 쿠폰 차감시 마일리지 별도 지급하기 때문에 빼주고 처리해야 함
                $tmpTotalMileage = $val['totalGoodsMileage'] + $val['totalMemberMileage'];
                if ($val['orderStatus'] != 'r3' && $val['plusMileageFl'] == 'n' && $val['plusRestoreMileageFl'] == 'n' && $val['memNo'] > 0 && $tmpTotalMileage > 0) {
                    $totalMileage += $tmpTotalMileage;
                    $orderGoodsNo[] = $val['sno'];
                }
            }
        }

        // 조건이 충족한 경우 회원 마일리지 차감 후 주문서 업데이트
        if ($totalMileage > 0) {
            // 사용한 마일리지가 있는 경우 구매마일리지를 지급하지 않는다
            if ($val['mileageGiveExclude'] == 'n' && $val['totalDivisionUseMileage'] > 0) {
                return false;
            }

            // 주문 시 마일리지 지급 유예 기간 설정에 따른 지급 날짜 처리 - 스케줄러로 지급
            if ($currentMileagePolicy['give']['delayFl'] == 'y') {
                $giveDate = new \DateTime();
                $giveDate->modify('+' . $currentMileagePolicy['give']['delayDay'] . ' day');
                $giveDate = $giveDate->format('Y-m-d');

                $giveData['mileageGiveDt'] = $giveDate;
                $giveField = array_keys($giveData);
                $arrBind = $this->db->get_binding(DBTableField::tableOrderGoods(), $giveData, 'update', $giveField);
                $this->db->set_update_db(DB_ORDER_GOODS, $arrBind['param'], 'sno IN (\'' . implode('\',\'', $orderGoodsNo) . '\')', $arrBind['bind']);
                unset($arrBind);

                return true;
            }
            /** @var \Bundle\Component\Mileage\Mileage $mileage */
            $mileage = \App::load('\\Component\\Mileage\\Mileage');
            $mileage->setIsTran(false);
            if ($this->changeStatusAuto) {
                $mileage->setSmsReserveTime(date('Y-m-d 08:00:00', strtotime('now')));
            }
            if ($mileage->setMemberMileage($val['memNo'], $totalMileage, Mileage::REASON_CODE_GROUP . Mileage::REASON_CODE_ADD_GOODS_BUY, 'o', $orderNo)) {
                $orderData['plusMileageFl'] = 'y';
                $orderData['plusRestoreMileageFl'] = 'n';
                $compareField = array_keys($orderData);
                $arrBind = $this->db->get_binding(DBTableField::tableOrderGoods(), $orderData, 'update', $compareField);
                $this->db->set_update_db(DB_ORDER_GOODS, $arrBind['param'], 'sno IN (\'' . implode('\',\'', $orderGoodsNo) . '\')', $arrBind['bind']);
                unset($arrBind);

                return true;
            }
        }

        return false;
    }

    /**
     * 마일리지 지급 복원
     * 환불시 마일리지를 별도로 지급하기 때문에 취소시에만 사용한다.
     *
     * @param string $orderNo 주문 번호
     * @param array $targetOrderGoodsNoArr 주문상품번호
     *
     * @return bool
     */
    public function setPlusMileageRestore($orderNo, $targetOrderGoodsNoArr = null)
    {
        $orderGoodsNo = [];
        $totalMileage = 0;
        foreach ($this->getOrderGoodsData($orderNo, $targetOrderGoodsNoArr, null, null, null, false) as $key => $val) {
            // 쿠폰적립은 회원 쿠폰 차감시 마일리지 별도 지급하기 때문에 빼주고 처리해야 함
            $tmpTotalMileage = $val['totalGoodsMileage'] + $val['totalMemberMileage'];
            if ($val['plusMileageFl'] == 'y' && $val['plusRestoreMileageFl'] == 'n' && $val['memNo'] > 0 && $tmpTotalMileage > 0) {
                $totalMileage += $tmpTotalMileage;
                $orderGoodsNo[] = $val['sno'];
            }
        }

        // 조건이 충족한 경우 회원 마일리지 차감 후 주문서 업데이트
        if ($totalMileage > 0) {
            // 사용한 마일리지가 있는 경우 적립마일리지를 지급하지 않는다
            if ($val['mileageGiveExclude'] == 'n' && $val['totalDivisionUseMileage'] > 0) {
                return false;
            }

            /** @var \Bundle\Component\Mileage\Mileage $mileage */
            $mileage = \App::load('\\Component\\Mileage\\Mileage');
            $mileage->setIsTran(false);
            if ($this->changeStatusAuto) {
                $mileage->setSmsReserveTime(date('Y-m-d 08:00:00', strtotime('now')));
            }
            if ($mileage->setMemberMileage($val['memNo'], ($totalMileage * -1), Mileage::REASON_CODE_GROUP . Mileage::REASON_CODE_ADD_GOODS_BUY_RESTORE, 'o', $orderNo)) {
                $orderData['plusMileageFl'] = 'n';
                $orderData['plusRestoreMileageFl'] = 'y';
                $compareField = array_keys($orderData);
                $arrBind = $this->db->get_binding(DBTableField::tableOrderGoods(), $orderData, 'update', $compareField);
                $this->db->set_update_db(DB_ORDER_GOODS, $arrBind['param'], 'sno IN (\'' . implode('\',\'', $orderGoodsNo) . '\')', $arrBind['bind']);
                unset($arrBind);

                return true;
            }
        }

        return false;
    }

    /**
     * 마일리지 차감
     * 주문서 작성시 사용한 마일리지가 있고
     *
     * @param string $orderNo 주문 번호
     *
     * @return boolean 성공여부
     */
    public function setMinusMileageVariation($orderNo)
    {
        $orderGoodsNo = [];
        $totalMileage = 0;
        foreach ($this->getOrderGoodsData($orderNo, null, null, null, null, false) as $key => $val) {
            if ($val['minusMileageFl'] == 'n' && $val['minusRestoreMileageFl'] == 'n' && $val['memNo'] > 0) {
                $totalMileage += $val['totalDivisionUseMileage'];
                $orderGoodsNo[] = $val['sno'];
            }
        }

        // 조건이 충족한 경우 회원 마일리지 차감 후 주문서 업데이트
        if ($totalMileage > 0) {
            /** @var \Bundle\Component\Mileage\Mileage $mileage */
            $mileage = \App::load('\\Component\\Mileage\\Mileage');
            $mileage->setIsTran(false);
            if ($mileage->setMemberMileage($val['memNo'], ($totalMileage * -1), Mileage::REASON_CODE_GROUP . Mileage::REASON_CODE_USE_GOODS_BUY, 'o', $orderNo)) {
                $orderData['minusMileageFl'] = 'y';
                $orderData['minusRestoreMileageFl'] = 'n';
                $compareField = array_keys($orderData);
                $arrBind = $this->db->get_binding(DBTableField::tableOrderGoods(), $orderData, 'update', $compareField);
                $this->db->set_update_db(DB_ORDER_GOODS, $arrBind['param'], 'sno IN (\'' . implode('\',\'', $orderGoodsNo) . '\')', $arrBind['bind']);
                unset($arrBind);

                return true;
            }
        }

        return false;
    }

    /*
     * 마일리지 차감 복원
     *
     * @param string $orderNo 주문 번호
     * @param array $targetOrderGoodsNoArr 주문상품번호
     *
     * @return bool
     */
    protected function setMinusMileageRestore($orderNo, $targetOrderGoodsNoArr = null)
    {
        $orderGoodsNo = [];
        $totalMileage = 0;
        foreach ($this->getOrderGoodsData($orderNo, $targetOrderGoodsNoArr, null, null, null, false) as $key => $val) {
            if ($val['minusMileageFl'] == 'y' && $val['minusRestoreMileageFl'] == 'n' && $val['memNo'] > 0) {
                $totalMileage += $val['totalDivisionUseMileage'];
                $orderGoodsNo[] = $val['sno'];
            }
        }

        // 조건이 충족한 경우 회원 마일리지 차감 후 주문서 업데이트
        if ($totalMileage > 0) {
            /** @var \Bundle\Component\Mileage\Mileage $mileage */
            $mileage = \App::load('\\Component\\Mileage\\Mileage');
            $mileage->setIsTran(false);
            if ($this->changeStatusAuto) {
                $mileage->setSmsReserveTime(date('Y-m-d 08:00:00', strtotime('now')));
            }
            if ($mileage->setMemberMileage($val['memNo'], $totalMileage, Mileage::REASON_CODE_GROUP . Mileage::REASON_CODE_USE_GOODS_BUY_RESTORE, 'o', $orderNo)) {
                $orderData['minusMileageFl'] = 'n';
                $orderData['minusRestoreMileageFl'] = 'y';
                $compareField = array_keys($orderData);
                $arrBind = $this->db->get_binding(DBTableField::tableOrderGoods(), $orderData, 'update', $compareField);
                $this->db->set_update_db(DB_ORDER_GOODS, $arrBind['param'], 'sno IN (\'' . implode('\',\'', $orderGoodsNo) . '\')', $arrBind['bind']);
                unset($arrBind);

                return true;
            }
        }

        return false;
    }

    /**
     * 할인쿠폰 사용에 대해 체크하고 쿠폰 사용에 대한 업데이트 처리
     *
     * @param string $orderNo 주문 번호
     *
     * @return boolean
     */
    public function setMinusCouponVariation($orderNo)
    {
        // 주문 쿠폰 데이타
        $arrInclude['o'] = ['memNo'];
        $arrInclude['oc'] = [
            'memberCouponNo',
            'minusCouponFl',
        ];
        $tmpField[] = DBTableField::setTableField('tableOrder', $arrInclude['o'], null, 'o');
        $tmpField[] = DBTableField::setTableField('tableOrderCoupon', $arrInclude['oc'], null, 'oc');

        $tmpKey = array_keys($tmpField);
        $arrField = [];
        foreach ($tmpKey as $key) {
            $arrField = array_merge($arrField, $tmpField[$key]);
        }
        unset($tmpField, $tmpKey);

        // join 문
        $arrJoin[] = ' INNER JOIN ' . DB_ORDER . ' o ON oc.orderNo = o.orderNo ';

        // where 문
        $arrWhere[] = 'o.orderNo = ?';
        $arrWhere[] = 'o.memNo > 0';
        $arrWhere[] = 'oc.couponPrice > 0';
        $arrWhere[] = 'oc.minusCouponFl = \'n\'';
        $this->db->bind_param_push($arrBind, 's', $orderNo);

        $this->db->strField = 'oc.sno, ' . implode(', ', $arrField);
        $this->db->strJoin = implode('', $arrJoin);
        $this->db->strWhere = implode(' AND ', $arrWhere);

        $query = $this->db->query_complete();
        $strSQL = 'SELECT ' . array_shift($query) . ' FROM ' . DB_ORDER_COUPON . ' oc ' . implode(' ', $query);
        $getData = $this->db->query_fetch($strSQL, $arrBind);
        unset($arrBind);

        // 주문쿠폰 테이블에 담긴 쿠폰 순차 처리
        if (empty($getData) !== true) {
            $coupon = App::load(\Component\Coupon\Coupon::class);
            foreach ($getData as $key => $val) {
                // 회원쿠폰 테이블 사용 표기 업데이트
                $coupon->setMemberCouponState($val['memberCouponNo'], 'order', true);

                // 주문 쿠폰 테이블의 차감 여부 변경
                $orderData['minusCouponFl'] = 'y';
                $orderData['minusRestoreCouponFl'] = 'n';
                $compareField = array_keys($orderData);
                $arrBind = $this->db->get_binding(DBTableField::tableOrderCoupon(), $orderData, 'update', $compareField);
                $this->db->bind_param_push($arrBind['bind'], 'i', $val['sno']);
                $this->db->set_update_db(DB_ORDER_COUPON, $arrBind['param'], 'sno = ?', $arrBind['bind']);
                unset($arrBind);
            }

            return true;
        }

        return false;
    }

    /**
     * 적립쿠폰 사용에 대해 체크하고 쿠폰 사용상태 order 업데이트 처리
     *
     * @param string $orderNo 주문 번호
     * @param array  $arrData 주문상품정보 (기존에 없던 파라미터라 default 값을 강제로 넣음)
     *
     * @return bool
     */
    public function setMemberMileageCouponState($orderNo, $arrData = [])
    {
        // 주문 쿠폰 데이타 (주문번호, 마일리지 > 0 경우와 매칭되는 모든 데이터)
        $arrInclude['o'] = [
            'memNo',
            'totalCouponOrderMileage',
        ];
        $arrInclude['oc'] = [
            'memberCouponNo',
            'couponNm',
            'couponMileage',
            'goodsNo',
            'plusCouponFl',
            'couponUseType'
        ];
        $tmpField[] = DBTableField::setTableField('tableOrder', $arrInclude['o'], null, 'o');
        $tmpField[] = DBTableField::setTableField('tableOrderGoods', $arrInclude['og'], null, 'og');
        $tmpField[] = DBTableField::setTableField('tableOrderCoupon', $arrInclude['oc'], null, 'oc');

        $tmpKey = array_keys($tmpField);
        $arrField = [];
        foreach ($tmpKey as $key) {
            $arrField = array_merge($arrField, $tmpField[$key]);
        }
        unset($tmpField, $tmpKey);

        // join 문
        $arrJoin[] = ' INNER JOIN ' . DB_ORDER . ' o ON oc.orderNo = o.orderNo ';
        $arrJoin[] = ' LEFT JOIN ' . DB_ORDER_GOODS . ' og ON oc.orderCd = og.orderCd AND oc.orderNo = og.orderNo';

        // where 문
        $arrWhere[] = 'o.orderNo = ?';
        $arrWhere[] = 'o.memNo > 0';
        $arrWhere[] = 'oc.couponMileage > 0';// 마일리지가 0보다 큰 경우 마일리지 지급 쿠폰
        $arrWhere[] = 'oc.plusCouponFl = \'n\'';

        // query 처리
        $this->db->bind_param_push($arrBind, 's', $orderNo);
        $this->db->strField = 'oc.sno, og.sno AS orderGoodsSno, ' . implode(', ', $arrField);
        $this->db->strJoin = implode('', $arrJoin);
        $this->db->strWhere = implode(' AND ', $arrWhere);
        $query = $this->db->query_complete();
        $strSQL = 'SELECT ' . array_shift($query) . ' FROM ' . DB_ORDER_COUPON . ' oc ' . implode(' ', $query);
        $getData = $this->db->query_fetch($strSQL, $arrBind);
        unset($arrBind);

        // 주문쿠폰 테이블에 담긴 쿠폰 순차 처리
        if (empty($getData) !== true) {
            $coupon = App::load(\Component\Coupon\Coupon::class);
            foreach ($getData as $key => $val) {
                // 상품 쿠폰의 경우 해당 상품만 처리
                $canPlusCouponAction = false;
                if (empty($arrData) === false) {// 파라미터 신규 추가로 튜닝하지 않은 업체는 기존대로 처리 되게 하기 위함
                    if ($val['couponUseType'] == 'product') {
                        if (in_array($val['orderGoodsSno'], $arrData['sno'])) {
                            $canPlusCouponAction = true;
                        }
                    }
                    else if ($val['couponUseType'] == 'order') {
                        //해당 주문서에서 적립할 주문적립쿠폰금액이 있을시..
                        if((int)$val['totalCouponOrderMileage'] > 0){
                            $canPlusCouponAction = true;
                        }
                    }
                    else {
                        $canPlusCouponAction = true;
                    }
                } else {
                    $canPlusCouponAction = true;
                }

                // 처리 가능한 경우만 쿠폰상태 변경처리
                if ($canPlusCouponAction === true) {
                    // 회원쿠폰 테이블 사용 표기 업데이트
                    $coupon->setMemberCouponState($val['memberCouponNo'], 'order', true);
                }
            }

            return true;
        }
    }

    /**
     * 적립쿠폰 사용에 대해 체크하고 쿠폰 사용과 마일리지 적립 대한 업데이트 처리
     * 주문쿠폰의 경우 우선 처리되며 상품쿠폰의 경우 해당 상품만 처리된다. (따로 분리됨 setDirectPlusCouponVariation 함수에서 처리)
     *
     * @param string $orderNo 주문 번호
     * @param array  $arrData 주문상품정보 (기존에 없던 파라미터라 default 값을 강제로 넣음)
     *
     * @return bool
     */
    public function setPlusCouponVariation($orderNo, $arrData = [])
    {
        if (intval(substr($orderNo, 0, 10)) < 1907100740 || empty($arrData) === true) { // 주문이 환불개선 이전 이면
            $chkRefundNewFl = 'F';
        } else {
            $chkRefundNewFl = 'T';
        }

        // 주문번호만 가지고 처리하는 구간 시작
        // @todo 첫구매 및 구매 이벤트 쿠폰 적용위치 확인 필요
        // 첫구매 쿠폰 지급
        if ($this->isFirstCoupon($orderNo)) {
            $orderData = $this->getOrderData($orderNo);
            $member = App::load('\\Component\\Member\\Member');
            $coupon = App::load('\\Component\\Coupon\\Coupon');
            $memberData = $member->getMember($orderData['memNo'], 'memNo', 'memNo, groupSno');
            $coupon->setAutoCouponMemberSave('first', $memberData['memNo'], $memberData['groupSno']);

            $setData['firstCouponFl'] = 'y';
            $arrBind = $this->db->get_binding(DBTableField::tableOrder(), $setData, 'update', array_keys($setData), ['orderNo']);
            $this->db->bind_param_push($arrBind['bind'], 's', $orderNo);
            $this->db->set_update_db(DB_ORDER, $arrBind['param'], 'orderNo = ?', $arrBind['bind'], false);
            unset($arrBind, $setData);

            // 첫구매와 중복지급되는 구매 쿠폰 지급
            $coupon->setAutoCouponMemberSave('order', $memberData['memNo'], $memberData['groupSno'], null, null, 'y');
            $setData['eventCouponFl'] = 'y';
            $arrBind = $this->db->get_binding(DBTableField::tableOrder(), $setData, 'update', array_keys($setData), ['orderNo']);
            $this->db->bind_param_push($arrBind['bind'], 's', $orderNo);
            $this->db->set_update_db(DB_ORDER, $arrBind['param'], 'orderNo = ?', $arrBind['bind'], false);
            unset($arrBind, $setData);

            // 구매 쿠폰 지급
        } else {
            $orderData = $this->getOrderData($orderNo);
            $member = App::load('\\Component\\Member\\Member');
            $coupon = App::load('\\Component\\Coupon\\Coupon');
            $memberData = $member->getMember($orderData['memNo'], 'memNo', 'memNo, groupSno');
            $coupon->setAutoCouponMemberSave('order', $memberData['memNo'], $memberData['groupSno']);

            $setData['eventCouponFl'] = 'y';
            $arrBind = $this->db->get_binding(DBTableField::tableOrder(), $setData, 'update', array_keys($setData), ['orderNo']);
            $this->db->bind_param_push($arrBind['bind'], 's', $orderNo);
            $this->db->set_update_db(DB_ORDER, $arrBind['param'], 'orderNo = ?', $arrBind['bind'], false);
            unset($arrBind, $setData);
        }

        // 주문 쿠폰 데이타 (주문번호, 마일리지 > 0 경우와 매칭되는 모든 데이터)
        $arrInclude['o'] = [
            'memNo',
            'totalCouponOrderMileage',
        ];
        $arrInclude['oc'] = [
            'memberCouponNo',
            'couponNm',
            'couponMileage',
            'goodsNo',
            'plusCouponFl',
            'couponUseType'
        ];

        $coupon = App::load(\Component\Coupon\Coupon::class);

        if ($chkRefundNewFl == 'F') { // 주문이 환불개선 이전 이면
            $tmpField[] = DBTableField::setTableField('tableOrder', $arrInclude['o'], null, 'o');
            $tmpField[] = DBTableField::setTableField('tableOrderGoods', $arrInclude['og'], null, 'og');
            $tmpField[] = DBTableField::setTableField('tableOrderCoupon', $arrInclude['oc'], null, 'oc');

            $tmpKey = array_keys($tmpField);
            $arrField = [];
            foreach ($tmpKey as $key) {
                $arrField = array_merge($arrField, $tmpField[$key]);
            }
            unset($tmpField, $tmpKey);

            // join 문
            $arrJoin[] = ' INNER JOIN ' . DB_ORDER . ' o ON oc.orderNo = o.orderNo ';
            $arrJoin[] = ' LEFT JOIN ' . DB_ORDER_GOODS . ' og ON oc.orderCd = og.orderCd AND oc.orderNo = og.orderNo';

            // where 문
            $arrWhere[] = 'o.orderNo = ?';
            $arrWhere[] = 'o.memNo > 0';
            $arrWhere[] = 'oc.couponMileage > 0';// 마일리지가 0보다 큰 경우 마일리지 지급 쿠폰
            $arrWhere[] = 'oc.plusCouponFl = \'n\'';

            // query 처리
            $this->db->bind_param_push($arrBind, 's', $orderNo);
            $this->db->strField = 'oc.sno, og.sno AS orderGoodsSno, ' . implode(', ', $arrField);
            $this->db->strJoin = implode('', $arrJoin);
            $this->db->strWhere = implode(' AND ', $arrWhere);
            $query = $this->db->query_complete();
            $strSQL = 'SELECT ' . array_shift($query) . ' FROM ' . DB_ORDER_COUPON . ' oc ' . implode(' ', $query);
            $getData = $this->db->query_fetch($strSQL, $arrBind);
            unset($arrBind);

            if (empty($getData) === true) {
                return false;
            }

            foreach ($getData as $key => $val) {
                // 상품 쿠폰의 경우 해당 상품만 처리
                $canPlusCouponAction = false;
                if (empty($arrData) === false) {// 파라미터 신규 추가로 튜닝하지 않은 업체는 기존대로 처리 되게 하기 위함
                    if ($val['couponUseType'] == 'product') {
                        if (in_array($val['orderGoodsSno'], $arrData['sno'])) {
                            $canPlusCouponAction = true;
                        }
                    }
                    else if ($val['couponUseType'] == 'order') {
                        //해당 주문서에서 적립할 주문적립쿠폰금액이 있을시..
                        if((int)$val['totalCouponOrderMileage'] > 0){
                            $canPlusCouponAction = true;
                        }
                    }
                    else {
                        $canPlusCouponAction = true;
                    }
                } else {
                    $canPlusCouponAction = true;
                }

                // 처리 가능한 경우만 쿠폰 마일리지 지급 처리
                if ($canPlusCouponAction === true) {
                    // 회원쿠폰 테이블 사용 표기 업데이트
                    $coupon->setMemberCouponState($val['memberCouponNo'], 'order', true);

                    // 처리 내용
                    $contents = ' (' . $val['couponNm'] . ')';

                    // 마일리지 처리
                    /** @var \Bundle\Component\Mileage\Mileage $mileage */
                    $mileage = \App::load('\\Component\\Mileage\\Mileage');
                    $mileage->setIsTran(false);
                    if ($this->changeStatusAuto) {
                        $mileage->setSmsReserveTime(date('Y-m-d 08:00:00', strtotime('now')));
                    }
                    if ($mileage->setMemberMileage($val['memNo'], $val['couponMileage'], Mileage::REASON_CODE_GROUP . Mileage::REASON_CODE_MILEAGE_SAVE_COUPON, 'o', $orderNo, $val['goodsNo'], $contents)) {
                        // 주문 쿠폰 테이블의 지급 여부 변경
                        $orderData['plusCouponFl'] = 'y';
                        $orderData['plusRestoreCouponFl'] = 'n';
                        $compareField = array_keys($orderData);
                        $arrBind = $this->db->get_binding(DBTableField::tableOrderCoupon(), $orderData, 'update', $compareField);
                        $this->db->bind_param_push($arrBind['bind'], 'i', $val['sno']);
                        $this->db->set_update_db(DB_ORDER_COUPON, $arrBind['param'], 'sno = ?', $arrBind['bind']);
                        unset($arrBind);
                    }
                }
            }
        } else {
            $tmpField[] = DBTableField::setTableField('tableOrder', $arrInclude['o'], null, 'o');
            $tmpField[] = DBTableField::setTableField('tableOrderGoods', $arrInclude['og'], null, 'og');
            $tmpField[] = DBTableField::setTableField('tableOrderCoupon', $arrInclude['oc'], null, 'oc');

            $tmpKey = array_keys($tmpField);
            $arrField = [];
            foreach ($tmpKey as $key) {
                $arrField = array_merge($arrField, $tmpField[$key]);
            }
            unset($tmpField, $tmpKey);

            // join 문
            $arrJoin[] = ' INNER JOIN ' . DB_ORDER . ' o ON oc.orderNo = o.orderNo ';
            $arrJoin[] = ' LEFT JOIN ' . DB_ORDER_GOODS . ' og ON oc.orderCd = og.orderCd AND oc.orderNo = og.orderNo';

            // where 문
            $arrWhere[] = 'o.orderNo = ?';
            $arrWhere[] = 'o.memNo > 0';
            $arrWhere[] = 'oc.couponMileage > 0';// 마일리지가 0보다 큰 경우 마일리지 지급 쿠폰

            // query 처리
            $this->db->bind_param_push($arrBind, 's', $orderNo);
            $this->db->strField = 'oc.sno, og.sno AS orderGoodsSno, ' . implode(', ', $arrField);
            $this->db->strJoin = implode('', $arrJoin);
            $this->db->strWhere = implode(' AND ', $arrWhere);
            $query = $this->db->query_complete();
            $strSQL = 'SELECT ' . array_shift($query) . ' FROM ' . DB_ORDER_COUPON . ' oc ' . implode(' ', $query);
            $getData = $this->db->query_fetch($strSQL, $arrBind);
            unset($arrBind);

            if (empty($getData) === true) {
                return false;
            }

            foreach ($getData as $key => $val) {
                if ($val['couponUseType'] == 'product') {
                    if (in_array($val['orderGoodsSno'], $arrData['sno'])) {
                        $aCouponName[$val['orderGoodsSno']] = $val['couponNm'];
                        $aCouponName[$val['orderGoodsSno'] . 'goodsSno'][] = $val['sno'];
                    }
                }
                else if ($val['couponUseType'] == 'order') {
                    //해당 주문서에서 적립할 주문적립쿠폰금액이 있을시..
                    if((int)$val['totalCouponOrderMileage'] > 0){
                        $aCouponName['order'] = $val['couponNm'];
                        $aCouponName['orderSno'][] = $val['sno'];
                    }
                }
            }

            unset($tmpField, $arrField, $arrJoin, $arrWhere, $getData);

            $tmpField[] = DBTableField::setTableField('tableOrderGoods', $arrInclude['og'], null, 'og');

            $tmpKey = array_keys($tmpField);
            $arrField = [];
            foreach ($tmpKey as $key) {
                $arrField = array_merge($arrField, $tmpField[$key]);
            }
            unset($tmpField, $tmpKey);

            // join 문
            $arrJoin[] = ' INNER JOIN ' . DB_ORDER_GOODS . ' og ON o.orderNo = og.orderNo ';

            // where 문
            $arrWhere[] = 'o.orderNo = ?';
            $arrWhere[] = 'o.memNo > 0';
            $arrWhere[] = 'og.couponMileageFl = \'n\'';

            // query 처리
            $this->db->bind_param_push($arrBind, 's', $orderNo);
            $this->db->strField = 'o.memNo, og.sno AS orderGoodsSno, ' . implode(', ', $arrField);
            $this->db->strJoin = implode('', $arrJoin);
            $this->db->strWhere = implode(' AND ', $arrWhere);
            $query = $this->db->query_complete();
            $strSQL = 'SELECT ' . array_shift($query) . ' FROM ' . DB_ORDER . ' o ' . implode(' ', $query);
            $getData = $this->db->query_fetch($strSQL, $arrBind);

            unset($arrBind);

            // 주문쿠폰일경우 임시 정보값
            $orderCouponAction = false;
            $orderCouponInfo = array();
            $orderCouponInfo['mileage'] = 0;
            $orderCouponInfo['orderGoodsSno'] = array();

            foreach ($getData as $key => $val) {
                // 상품 쿠폰의 경우 해당 상품만 처리
                $canPlusCouponAction = false;
                if (in_array($val['orderGoodsSno'], $arrData['sno'])) {
                    if ($val['couponGoodsMileage'] > 0) {
                        $canPlusCouponAction = true;
                    }
                    if ($val['divisionCouponOrderMileage'] > 0) {
                        $orderCouponAction = true;
                        $orderCouponInfo['memNo'] = $val['memNo'];
                        $orderCouponInfo['mileage'] += $val['divisionCouponOrderMileage'];
                        $orderCouponInfo['contents'] = ' (' . $aCouponName['order'] . ')';
                        $orderCouponInfo['orderGoodsSno'][] = $val['orderGoodsSno'];
                    }
                }
                // 처리 가능한 경우만 쿠폰 마일리지 지급 처리
                if ($canPlusCouponAction === true) {
                    // 처리 내용
                    $contents = ' (' . $aCouponName[$val['orderGoodsSno']] . ')';

                    // 마일리지 처리
                    /** @var \Bundle\Component\Mileage\Mileage $mileage */
                    $mileage = \App::load('\\Component\\Mileage\\Mileage');
                    $mileage->setIsTran(false);
                    if ($this->changeStatusAuto) {
                        $mileage->setSmsReserveTime(date('Y-m-d 08:00:00', strtotime('now')));
                    }

                    if ($mileage->setMemberMileage($val['memNo'], $val['couponGoodsMileage'], Mileage::REASON_CODE_GROUP . Mileage::REASON_CODE_MILEAGE_SAVE_COUPON, 'o', $orderNo, $val['goodsNo'], $contents)) {
                        // 마일리지 지급 품목 fl값 업데이트
                        $orderGoodsData['couponMileageFl'] = 'y';
                        $compareField = array_keys($orderGoodsData);
                        $arrBind = $this->db->get_binding(DBTableField::tableOrderGoods(), $orderGoodsData, 'update', $compareField);
                        $this->db->bind_param_push($arrBind['bind'], 'i', $val['orderGoodsSno']);
                        $this->db->set_update_db(DB_ORDER_GOODS, $arrBind['param'], 'sno = ?', $arrBind['bind']);
                        unset($arrBind);

                        // 상품 쿠폰 테이블의 지급 여부 변경
                        foreach ($aCouponName[$val['orderGoodsSno'] . 'goodsSno'] as $v) {
                            $orderData['plusCouponFl'] = 'y';
                            $orderData['plusRestoreCouponFl'] = 'n';
                            $compareField = array_keys($orderData);
                            $arrBind = $this->db->get_binding(DBTableField::tableOrderCoupon(), $orderData, 'update', $compareField);
                            $this->db->bind_param_push($arrBind['bind'], 'i', $v);
                            $this->db->set_update_db(DB_ORDER_COUPON, $arrBind['param'], 'sno = ?', $arrBind['bind']);
                            unset($arrBind);
                        }
                    }
                }
            }

            if ($orderCouponAction == true) {
                // 마일리지 처리
                /** @var \Bundle\Component\Mileage\Mileage $mileage */
                $mileage = \App::load('\\Component\\Mileage\\Mileage');
                $mileage->setIsTran(false);
                if ($this->changeStatusAuto) {
                    $mileage->setSmsReserveTime(date('Y-m-d 08:00:00', strtotime('now')));
                }
                if ($mileage->setMemberMileage($orderCouponInfo['memNo'], $orderCouponInfo['mileage'], Mileage::REASON_CODE_GROUP . Mileage::REASON_CODE_MILEAGE_SAVE_COUPON, 'o', $orderNo, 0, $orderCouponInfo['contents'])) {
                    // 마일리지 지급 품목 fl값 업데이트
                    foreach ($orderCouponInfo['orderGoodsSno'] as $orderGoodsSno) {
                        $orderGoodsData['couponMileageFl'] = 'y';
                        $compareField = array_keys($orderGoodsData);
                        $arrBind = $this->db->get_binding(DBTableField::tableOrderGoods(), $orderGoodsData, 'update', $compareField);
                        $this->db->bind_param_push($arrBind['bind'], 'i', $orderGoodsSno);
                        $this->db->set_update_db(DB_ORDER_GOODS, $arrBind['param'], 'sno = ?', $arrBind['bind']);
                        unset($arrBind);
                    }

                    // 주문 쿠폰 테이블의 지급 여부 변경
                    foreach ($aCouponName['orderSno'] as $v) {
                        $orderData['plusCouponFl'] = 'y';
                        $orderData['plusRestoreCouponFl'] = 'n';
                        $compareField = array_keys($orderData);
                        $arrBind = $this->db->get_binding(DBTableField::tableOrderCoupon(), $orderData, 'update', $compareField);
                        $this->db->bind_param_push($arrBind['bind'], 'i', $v);
                        $this->db->set_update_db(DB_ORDER_COUPON, $arrBind['param'], 'sno = ?', $arrBind['bind']);
                        unset($arrBind);
                    }
                }
            }
        }

        return true;
    }

    /**
     * 적립쿠폰 사용에 대해 체크하고 쿠폰 사용과 마일리지 적립 대한 업데이트 처리
     * 마일리지 적립쿠폰 적용 후 주문 시 주문상태 및 ‘기본설정>주문상태설정의 쿠폰혜택지급시점’ 상관없이 즉시 ‘주문사용(order)’으로 쿠폰 상태 변경
     * 주문쿠폰의 경우 우선 처리되며 상품쿠폰의 경우 해당 상품만 처리된다.
     *
     * @param string $orderNo 주문 번호
     * @param array  $arrData 주문상품정보 (기존에 없던 파라미터라 default 값을 강제로 넣음)
     *
     * @return bool
     */
    public function setDirectPlusCouponVariation($orderNo, $arrData = []) {


        // 주문 쿠폰 데이타 (주문번호, 마일리지 > 0 경우와 매칭되는 모든 데이터)
        $arrInclude['o'] = [
            'memNo',
            'totalCouponOrderMileage',
        ];
        $arrInclude['oc'] = [
            'memberCouponNo',
            'couponNm',
            'couponMileage',
            'goodsNo',
            'plusCouponFl',
            'couponUseType'
        ];

        $coupon = App::load(\Component\Coupon\Coupon::class);

        if ($chkRefundNewFl == 'F') { // 주문이 환불개선 이전 이면
            $tmpField[] = DBTableField::setTableField('tableOrder', $arrInclude['o'], null, 'o');
            $tmpField[] = DBTableField::setTableField('tableOrderGoods', $arrInclude['og'], null, 'og');
            $tmpField[] = DBTableField::setTableField('tableOrderCoupon', $arrInclude['oc'], null, 'oc');

            $tmpKey = array_keys($tmpField);
            $arrField = [];
            foreach ($tmpKey as $key) {
                $arrField = array_merge($arrField, $tmpField[$key]);
            }
            unset($tmpField, $tmpKey);

            // join 문
            $arrJoin[] = ' INNER JOIN ' . DB_ORDER . ' o ON oc.orderNo = o.orderNo ';
            $arrJoin[] = ' LEFT JOIN ' . DB_ORDER_GOODS . ' og ON oc.orderCd = og.orderCd AND oc.orderNo = og.orderNo';

            // where 문
            $arrWhere[] = 'o.orderNo = ?';
            $arrWhere[] = 'o.memNo > 0';
            $arrWhere[] = 'oc.couponMileage > 0';// 마일리지가 0보다 큰 경우 마일리지 지급 쿠폰
            $arrWhere[] = 'oc.plusCouponFl = \'n\'';

            // query 처리
            $this->db->bind_param_push($arrBind, 's', $orderNo);
            $this->db->strField = 'oc.sno, og.sno AS orderGoodsSno, ' . implode(', ', $arrField);
            $this->db->strJoin = implode('', $arrJoin);
            $this->db->strWhere = implode(' AND ', $arrWhere);
            $query = $this->db->query_complete();
            $strSQL = 'SELECT ' . array_shift($query) . ' FROM ' . DB_ORDER_COUPON . ' oc ' . implode(' ', $query);
            $getData = $this->db->query_fetch($strSQL, $arrBind);
            unset($arrBind);

            if (empty($getData) === true) {
                return false;
            }

            foreach ($getData as $key => $val) {
                // 상품 쿠폰의 경우 해당 상품만 처리
                $canPlusCouponAction = false;
                if (empty($arrData) === false) {// 파라미터 신규 추가로 튜닝하지 않은 업체는 기존대로 처리 되게 하기 위함
                    if ($val['couponUseType'] == 'product') {
                        if (in_array($val['orderGoodsSno'], $arrData['sno'])) {
                            $canPlusCouponAction = true;
                        }
                    }
                    else if ($val['couponUseType'] == 'order') {
                        //해당 주문서에서 적립할 주문적립쿠폰금액이 있을시..
                        if((int)$val['totalCouponOrderMileage'] > 0){
                            $canPlusCouponAction = true;
                        }
                    }
                    else {
                        $canPlusCouponAction = true;
                    }
                } else {
                    $canPlusCouponAction = true;
                }

                // 처리 가능한 경우만 쿠폰 마일리지 지급 처리
                if ($canPlusCouponAction === true) {
                    // 회원쿠폰 테이블 사용 표기 업데이트
                    $coupon->setMemberCouponState($val['memberCouponNo'], 'order', true);

                    // 처리 내용
                    $contents = ' (' . $val['couponNm'] . ')';

                    // 마일리지 처리
                    /** @var \Bundle\Component\Mileage\Mileage $mileage */
                    $mileage = \App::load('\\Component\\Mileage\\Mileage');
                    $mileage->setIsTran(false);
                    if ($this->changeStatusAuto) {
                        $mileage->setSmsReserveTime(date('Y-m-d 08:00:00', strtotime('now')));
                    }
                    if ($mileage->setMemberMileage($val['memNo'], $val['couponMileage'], Mileage::REASON_CODE_GROUP . Mileage::REASON_CODE_MILEAGE_SAVE_COUPON, 'o', $orderNo, $val['goodsNo'], $contents)) {
                        // 주문 쿠폰 테이블의 지급 여부 변경
                        $orderData['plusCouponFl'] = 'y';
                        $orderData['plusRestoreCouponFl'] = 'n';
                        $compareField = array_keys($orderData);
                        $arrBind = $this->db->get_binding(DBTableField::tableOrderCoupon(), $orderData, 'update', $compareField);
                        $this->db->bind_param_push($arrBind['bind'], 'i', $val['sno']);
                        $this->db->set_update_db(DB_ORDER_COUPON, $arrBind['param'], 'sno = ?', $arrBind['bind']);
                        unset($arrBind);
                    }
                }
            }
        } else {
            $tmpField[] = DBTableField::setTableField('tableOrder', $arrInclude['o'], null, 'o');
            $tmpField[] = DBTableField::setTableField('tableOrderGoods', $arrInclude['og'], null, 'og');
            $tmpField[] = DBTableField::setTableField('tableOrderCoupon', $arrInclude['oc'], null, 'oc');

            $tmpKey = array_keys($tmpField);
            $arrField = [];
            foreach ($tmpKey as $key) {
                $arrField = array_merge($arrField, $tmpField[$key]);
            }
            unset($tmpField, $tmpKey);

            // join 문
            $arrJoin[] = ' INNER JOIN ' . DB_ORDER . ' o ON oc.orderNo = o.orderNo ';
            $arrJoin[] = ' LEFT JOIN ' . DB_ORDER_GOODS . ' og ON oc.orderCd = og.orderCd AND oc.orderNo = og.orderNo';

            // where 문
            $arrWhere[] = 'o.orderNo = ?';
            $arrWhere[] = 'o.memNo > 0';
            $arrWhere[] = 'oc.couponMileage > 0';// 마일리지가 0보다 큰 경우 마일리지 지급 쿠폰

            // query 처리
            $this->db->bind_param_push($arrBind, 's', $orderNo);
            $this->db->strField = 'oc.sno, og.sno AS orderGoodsSno, ' . implode(', ', $arrField);
            $this->db->strJoin = implode('', $arrJoin);
            $this->db->strWhere = implode(' AND ', $arrWhere);
            $query = $this->db->query_complete();
            $strSQL = 'SELECT ' . array_shift($query) . ' FROM ' . DB_ORDER_COUPON . ' oc ' . implode(' ', $query);
            $getData = $this->db->query_fetch($strSQL, $arrBind);
            unset($arrBind);

            if (empty($getData) === true) {
                return false;
            }

            $canPlusCouponAction = false;
            foreach ($getData as $key => $val) {
                if ($val['couponUseType'] == 'product') {
                    if (in_array($val['orderGoodsSno'], $arrData['sno'])) {
                        $aCouponName[$val['orderGoodsSno']] = $val['couponNm'];
                        $aCouponName[$val['orderGoodsSno'] . 'goodsSno'][] = $val['sno'];
                        $canPlusCouponAction = true;
                    }
                }
                else if ($val['couponUseType'] == 'order') {
                    //해당 주문서에서 적립할 주문적립쿠폰금액이 있을시..
                    if((int)$val['totalCouponOrderMileage'] > 0){
                        $aCouponName['order'] = $val['couponNm'];
                        $aCouponName['orderSno'][] = $val['sno'];
                        $canPlusCouponAction = true;
                    }
                }

                // 처리 가능한 경우만 쿠폰 마일리지 지급 처리
                if ($canPlusCouponAction === true) {
                    // 회원쿠폰 테이블 사용 표기 업데이트
                    $coupon->setMemberCouponState($val['memberCouponNo'], 'order', true);
                }
            }

            unset($tmpField, $arrField, $arrJoin, $arrWhere, $getData);

            $tmpField[] = DBTableField::setTableField('tableOrderGoods', $arrInclude['og'], null, 'og');

            $tmpKey = array_keys($tmpField);
            $arrField = [];
            foreach ($tmpKey as $key) {
                $arrField = array_merge($arrField, $tmpField[$key]);
            }
            unset($tmpField, $tmpKey);

            // join 문
            $arrJoin[] = ' INNER JOIN ' . DB_ORDER_GOODS . ' og ON o.orderNo = og.orderNo ';

            // where 문
            $arrWhere[] = 'o.orderNo = ?';
            $arrWhere[] = 'o.memNo > 0';
            $arrWhere[] = 'og.couponMileageFl = \'n\'';

            // query 처리
            $this->db->bind_param_push($arrBind, 's', $orderNo);
            $this->db->strField = 'o.memNo, og.sno AS orderGoodsSno, ' . implode(', ', $arrField);
            $this->db->strJoin = implode('', $arrJoin);
            $this->db->strWhere = implode(' AND ', $arrWhere);
            $query = $this->db->query_complete();
            $strSQL = 'SELECT ' . array_shift($query) . ' FROM ' . DB_ORDER . ' o ' . implode(' ', $query);
            $getData = $this->db->query_fetch($strSQL, $arrBind);

            unset($arrBind);

            // 주문쿠폰일경우 임시 정보값
            $orderCouponAction = false;
            $orderCouponInfo = array();
            $orderCouponInfo['mileage'] = 0;
            $orderCouponInfo['orderGoodsSno'] = array();

            foreach ($getData as $key => $val) {
                // 상품 쿠폰의 경우 해당 상품만 처리
                $canPlusCouponAction = false;
                if (in_array($val['orderGoodsSno'], $arrData['sno'])) {
                    if ($val['couponGoodsMileage'] > 0) {
                        $canPlusCouponAction = true;
                    }
                    if ($val['divisionCouponOrderMileage'] > 0) {
                        $orderCouponAction = true;
                        $orderCouponInfo['memNo'] = $val['memNo'];
                        $orderCouponInfo['mileage'] += $val['divisionCouponOrderMileage'];
                        $orderCouponInfo['contents'] = ' (' . $aCouponName['order'] . ')';
                        $orderCouponInfo['orderGoodsSno'][] = $val['orderGoodsSno'];
                    }
                }
                // 처리 가능한 경우만 쿠폰 마일리지 지급 처리
                if ($canPlusCouponAction === true) {
                    // 처리 내용
                    $contents = ' (' . $aCouponName[$val['orderGoodsSno']] . ')';

                    // 마일리지 처리
                    /** @var \Bundle\Component\Mileage\Mileage $mileage */
                    $mileage = \App::load('\\Component\\Mileage\\Mileage');
                    $mileage->setIsTran(false);
                    if ($this->changeStatusAuto) {
                        $mileage->setSmsReserveTime(date('Y-m-d 08:00:00', strtotime('now')));
                    }

                    if ($mileage->setMemberMileage($val['memNo'], $val['couponGoodsMileage'], Mileage::REASON_CODE_GROUP . Mileage::REASON_CODE_MILEAGE_SAVE_COUPON, 'o', $orderNo, $val['goodsNo'], $contents)) {
                        // 마일리지 지급 품목 fl값 업데이트
                        $orderGoodsData['couponMileageFl'] = 'y';
                        $compareField = array_keys($orderGoodsData);
                        $arrBind = $this->db->get_binding(DBTableField::tableOrderGoods(), $orderGoodsData, 'update', $compareField);
                        $this->db->bind_param_push($arrBind['bind'], 'i', $val['orderGoodsSno']);
                        $this->db->set_update_db(DB_ORDER_GOODS, $arrBind['param'], 'sno = ?', $arrBind['bind']);
                        unset($arrBind);

                        // 상품 쿠폰 테이블의 지급 여부 변경
                        foreach ($aCouponName[$val['orderGoodsSno'] . 'goodsSno'] as $v) {
                            $orderData['plusCouponFl'] = 'y';
                            $orderData['plusRestoreCouponFl'] = 'n';
                            $compareField = array_keys($orderData);
                            $arrBind = $this->db->get_binding(DBTableField::tableOrderCoupon(), $orderData, 'update', $compareField);
                            $this->db->bind_param_push($arrBind['bind'], 'i', $v);
                            $this->db->set_update_db(DB_ORDER_COUPON, $arrBind['param'], 'sno = ?', $arrBind['bind']);
                            unset($arrBind);
                        }
                    }
                }
            }

            if ($orderCouponAction == true) {
                // 마일리지 처리
                /** @var \Bundle\Component\Mileage\Mileage $mileage */
                $mileage = \App::load('\\Component\\Mileage\\Mileage');
                $mileage->setIsTran(false);
                if ($this->changeStatusAuto) {
                    $mileage->setSmsReserveTime(date('Y-m-d 08:00:00', strtotime('now')));
                }
                if ($mileage->setMemberMileage($orderCouponInfo['memNo'], $orderCouponInfo['mileage'], Mileage::REASON_CODE_GROUP . Mileage::REASON_CODE_MILEAGE_SAVE_COUPON, 'o', $orderNo, 0, $orderCouponInfo['contents'])) {
                    // 마일리지 지급 품목 fl값 업데이트
                    foreach ($orderCouponInfo['orderGoodsSno'] as $orderGoodsSno) {
                        $orderGoodsData['couponMileageFl'] = 'y';
                        $compareField = array_keys($orderGoodsData);
                        $arrBind = $this->db->get_binding(DBTableField::tableOrderGoods(), $orderGoodsData, 'update', $compareField);
                        $this->db->bind_param_push($arrBind['bind'], 'i', $orderGoodsSno);
                        $this->db->set_update_db(DB_ORDER_GOODS, $arrBind['param'], 'sno = ?', $arrBind['bind']);
                        unset($arrBind);
                    }

                    // 주문 쿠폰 테이블의 지급 여부 변경
                    foreach ($aCouponName['orderSno'] as $v) {
                        $orderData['plusCouponFl'] = 'y';
                        $orderData['plusRestoreCouponFl'] = 'n';
                        $compareField = array_keys($orderData);
                        $arrBind = $this->db->get_binding(DBTableField::tableOrderCoupon(), $orderData, 'update', $compareField);
                        $this->db->bind_param_push($arrBind['bind'], 'i', $v);
                        $this->db->set_update_db(DB_ORDER_COUPON, $arrBind['param'], 'sno = ?', $arrBind['bind']);
                        unset($arrBind);
                    }
                }
            }
        }

        return true;
    }

    /**
     * 취소에 따른 할인쿠폰에 대한 일괄 복원 처리
     * 할인쿠폰 사용에 대해 체크하고 쿠폰 사용에 대한 업데이트 처리
     *
     * @param string $orderNo 주문 번호
     *
     * @return boolean
     */
    public function setMinusCouponRestore($orderNo)
    {
        // 쿠폰 기본 설정
        $couponConfig = gd_policy('coupon.config');
        if ($couponConfig['couponAutoRecoverType'] == 'y') {

            // 주문 쿠폰 데이타
            $arrInclude['o'] = ['memNo'];
            $arrInclude['oc'] = [
                'memberCouponNo',
                'minusCouponFl',
            ];
            $tmpField[] = DBTableField::setTableField('tableOrder', $arrInclude['o'], null, 'o');
            $tmpField[] = DBTableField::setTableField('tableOrderCoupon', $arrInclude['oc'], null, 'oc');

            $tmpKey = array_keys($tmpField);
            $arrField = [];
            foreach ($tmpKey as $key) {
                $arrField = array_merge($arrField, $tmpField[$key]);
            }
            unset($tmpField, $tmpKey);

            // join 문
            $arrJoin[] = ' INNER JOIN ' . DB_ORDER . ' o ON oc.orderNo = o.orderNo ';

            // where 문
            $arrWhere[] = 'o.orderNo = ?';
            $arrWhere[] = 'o.memNo > 0';
            $arrWhere[] = 'oc.couponPrice > 0';
            $arrWhere[] = 'oc.minusCouponFl = \'y\'';
            $arrWhere[] = 'oc.minusRestoreCouponFl = \'n\'';
            $this->db->bind_param_push($arrBind, 's', $orderNo);

            $this->db->strField = 'oc.sno, ' . implode(', ', $arrField);
            $this->db->strJoin = implode('', $arrJoin);
            $this->db->strWhere = implode(' AND ', $arrWhere);

            $query = $this->db->query_complete();
            $strSQL = 'SELECT ' . array_shift($query) . ' FROM ' . DB_ORDER_COUPON . ' oc ' . implode(' ', $query);
            $getData = $this->db->query_fetch($strSQL, $arrBind);
            unset($arrBind);

            // 주문쿠폰 테이블에 담긴 쿠폰 순차 처리
            if (empty($getData) === false) {
                $coupon = App::load(\Component\Coupon\Coupon::class);
                foreach ($getData as $key => $val) {
                    // 할인쿠폰 복원 처리
                    $coupon->setMemberCouponState($val['memberCouponNo'], 'y', true);

                    // 주문 쿠폰 테이블의 차감 여부 변경
                    $orderData['minusCouponFl'] = 'y';
                    $orderData['minusRestoreCouponFl'] = 'y';
                    $compareField = array_keys($orderData);
                    $arrBind = $this->db->get_binding(DBTableField::tableOrderCoupon(), $orderData, 'update', $compareField);
                    $this->db->bind_param_push($arrBind['bind'], 'i', $val['sno']);
                    $this->db->set_update_db(DB_ORDER_COUPON, $arrBind['param'], 'sno = ?', $arrBind['bind']);
                    unset($arrBind);
                }

                return true;
            }
        } else {
            return true;
        }

        return false;
    }

    /**
     * 취소에 따른 적립쿠폰에 대한 일괄 복원 처리
     * 적립쿠폰 사용에 대해 체크하고 쿠폰 사용과 마일리지 적립 대한 업데이트 처리
     *
     * @param string $orderNo 주문 번호
     * @param array  $arrData 주문상품 데이터
     *
     * @return bool
     */
    public function setPlusCouponRestore($orderNo, $arrData = [])
    {
        // 쿠폰 기본 설정
        $couponConfig = gd_policy('coupon.config');
        if ($couponConfig['couponAutoRecoverType'] == 'y') {
            // 주문 쿠폰 데이타
            $arrInclude['o'] = ['memNo'];
            $arrInclude['oc'] = [
                'memberCouponNo',
                'couponNm',
                'couponMileage',
                'goodsNo',
                'plusCouponFl',
                'couponUseType',
            ];
            $tmpField[] = DBTableField::setTableField('tableOrder', $arrInclude['o'], null, 'o');
            $tmpField[] = DBTableField::setTableField('tableOrderGoods', $arrInclude['og'], null, 'og');
            $tmpField[] = DBTableField::setTableField('tableOrderCoupon', $arrInclude['oc'], null, 'oc');

            $tmpKey = array_keys($tmpField);
            $arrField = [];
            foreach ($tmpKey as $key) {
                $arrField = array_merge($arrField, $tmpField[$key]);
            }
            unset($tmpField, $tmpKey);

            // join 문
            $arrJoin[] = ' INNER JOIN ' . DB_ORDER . ' o ON oc.orderNo = o.orderNo ';
            $arrJoin[] = ' LEFT JOIN ' . DB_ORDER_GOODS . ' og ON oc.orderCd = og.orderCd AND oc.orderNo = og.orderNo';

            // where 문
            $arrWhere[] = 'o.orderNo = ?';
            $arrWhere[] = 'o.memNo > 0';
            $arrWhere[] = 'oc.couponMileage > 0';
            $arrWhere[] = 'oc.plusCouponFl = \'n\'';
            $arrWhere[] = 'oc.plusRestoreCouponFl = \'n\'';
            $this->db->bind_param_push($arrBind, 's', $orderNo);
            $this->db->strField = 'oc.sno, og.sno AS orderGoodsSno, ' . implode(', ', $arrField);
            $this->db->strJoin = implode('', $arrJoin);
            $this->db->strWhere = implode(' AND ', $arrWhere);

            $query = $this->db->query_complete();
            $strSQL = 'SELECT ' . array_shift($query) . ' FROM ' . DB_ORDER_COUPON . ' oc ' . implode(' ', $query);
            $getData = $this->db->query_fetch($strSQL, $arrBind);
            unset($arrBind);

            // 주문쿠폰 테이블에 담긴 쿠폰 순차 처리
            if (empty($getData) !== true) {
                $coupon = App::load(\Component\Coupon\Coupon::class);
                foreach ($getData as $key => $val) {
                    // 상품 쿠폰의 경우 해당 상품만 처리하고 주문은 무조건 처리
                    $canPlusCouponAction = false;
                    if (empty($arrData) === false) {// 파라미터 신규 추가로 튜닝하지 않은 업체는 기존대로 처리 되게 하기 위함
                        if ($val['couponUseType'] == 'product') {
                            if (in_array($val['orderGoodsSno'], $arrData['sno'])) {
                                $canPlusCouponAction = true;
                            }
                        } else {
                            $canPlusCouponAction = true;
                        }
                    } else {
                        $canPlusCouponAction = true;
                    }

                    // 처리 가능한 경우만 쿠폰 마일리지 복원 처리
                    if ($canPlusCouponAction === true) {
                        // 적립쿠폰 복원 처리
                        $coupon->setMemberCouponState($val['memberCouponNo'], 'y', true);

                        // 처리 내용
                        $contents = sprintf(__('주문적립 쿠폰(%s) 복원'), $val['couponNm']);
                    }
                }

                return true;
            }
        } else {
            return true;
        }

        return false;
    }

    /**
     * 쿠폰 복원
     *
     * @param string $orderNo    주문 번호
     * @param string $couponMode 처리 모드
     */
    protected function setCouponRestore($orderNo, $couponMode)
    {
        // 쿠폰 사용 종류에 따른 분류
        if ($couponMode == 'minus') {
            $arrMode['coupon'] = 'couponPrice';
            $arrMode['fl'] = 'minusCouponFl';
            $arrMode['where1'] = 'couponPrice';
            $arrMode['where2'] = 'couponMileage';
            $arrMode['restore'] = 'restoreMinusCouponFl';
            $arrMode['sign'] = -1;
        } elseif ($couponMode == 'plus') {
            $arrMode['coupon'] = 'couponMileage';
            $arrMode['fl'] = 'plusCouponFl';
            $arrMode['where1'] = 'couponMileage';
            $arrMode['where2'] = 'couponPrice';
            $arrMode['restore'] = 'restorePlusCouponFl';
            $arrMode['sign'] = -1;
        } else {
            return false;
        }

        // 주문 쿠폰 데이타
        $arrInclude['o'] = ['memNo'];
        $arrInclude['oc'] = [
            'goodsNo',
            'orderCd',
            'couponGiveSno',
            'couponNm',
            $arrMode['coupon'],
        ];
        $tmpField[] = DBTableField::setTableField('tableOrder', $arrInclude['o'], null, 'o');
        $tmpField[] = DBTableField::setTableField('tableOrderCoupon', $arrInclude['oc'], null, 'oc');

        $tmpKey = array_keys($tmpField);
        $arrField = [];
        foreach ($tmpKey as $key) {
            $arrField = array_merge($arrField, $tmpField[$key]);
        }
        unset($tmpField, $tmpKey);

        // join 문
        $arrJoin[] = ' INNER JOIN ' . DB_ORDER . ' o ON oc.orderNo = o.orderNo ';

        // where 문
        $arrWhere[] = 'o.orderNo = ?';
        $arrWhere[] = 'oc.' . $arrMode['restore'] . ' = \'n\'';
        $arrWhere[] = 'o.memNo > 0';
        $arrWhere[] = 'oc.' . $arrMode['where1'] . ' > 0';
        $arrWhere[] = 'oc.' . $arrMode['where2'] . ' = 0';
        $this->db->bind_param_push($arrBind, 's', $orderNo);

        $this->db->strField = implode(', ', $arrField);
        $this->db->strJoin = implode('', $arrJoin);
        $this->db->strWhere = implode(' AND ', $arrWhere);

        $query = $this->db->query_complete();
        $strSQL = 'SELECT ' . array_shift($query) . ' FROM ' . DB_ORDER_COUPON . ' oc ' . implode(' ', $query);
        $getData = $this->db->query_fetch($strSQL, $arrBind);
        unset($arrBind);

        if (empty($getData) === true) {
            return false;
        }

        foreach ($getData as $key => $val) {
            // 주문 테이블의 여부 변경
            $couponData['useDt'] = '';
            $couponData['orderNo'] = '';
            $compareField = array_keys($couponData);
            $arrBind = $this->db->get_binding(DBTableField::tableMemberCoupon(), $couponData, 'update', $compareField);
            $this->db->bind_param_push($arrBind['bind'], 'i', $val['couponGiveSno']);
            $this->db->set_update_db(DB_COUPON_GIVE, $arrBind['param'], 'sno = ? AND orderNo IS NOT NULL', $arrBind['bind']);
            unset($arrBind);

            // 적립 인경우 마일리지 지급
            if ($couponMode == 'plus') {
                // 마일리지 관련 Class
                /** @var \Bundle\Component\Mileage\Mileage $mileage */
                $mileage = \App::load('\\Component\\Mileage\\Mileage');
                $mileage->setIsTran(false);

                // 처리 내용
                $contents = sprintf(__('주문 취소로 적립된 쿠폰(%s) 복원'), $val['couponNm']);

                // 마일리지 처리
                //                $result = $mileage->setMemberMileage($val['memNo'], ($val['couponMileage'] * $arrMode['sign']), 'o', null, $orderNo, $val['goodsNo'], $contents);
                $result = $mileage->setMemberMileage($val['memNo'], ($val['couponMileage'] * $arrMode['sign']), Mileage::REASON_CODE_GROUP . Mileage::REASON_CODE_ADD_GOODS_BUY_RESTORE, 'o', $orderNo, $val['goodsNo']);
            }

            // 주문 쿠폰 테이블의 차감 여부 변경
            $orderData[$arrMode['fl']] = 'n';
            $orderData[$arrMode['restore']] = 'y';
            $compareField = array_keys($orderData);
            $arrBind = $this->db->get_binding(DBTableField::tableOrderCoupon(), $orderData, 'update', $compareField);
            $this->db->bind_param_push($arrBind['bind'], 's', $orderNo);
            $this->db->bind_param_push($arrBind['bind'], 'i', $val['orderCd']);
            $this->db->bind_param_push($arrBind['bind'], 'i', $val['goodsNo']);
            $this->db->set_update_db(DB_ORDER_COUPON, $arrBind['param'], 'orderNo = ? AND orderCd = ? AND goodsNo = ?', $arrBind['bind']);
            unset($arrBind);
        }
    }

    /**
     * 재고차감
     * 사은품은 재고차감만 하며 복원기능은 없다
     *
     * @param string $orderNo     주문 번호
     * @param array  $arrGoodsSno 일련번호 배열
     *
     * @internal param array $arrData 상태 정보
     */
    public function setGoodsStockCutback($orderNo, $arrGoodsSno)
    {
        $getOrderData = $this->getOrderData($orderNo);
        if ($getOrderData['orderChannelFl'] == 'naverpay') {
            $naverpayConfig = gd_policy('naverPay.config');
            if ($naverpayConfig['linkStock'] == 'n') return ['code' => '완료', 'desc' => '네이버페이 주문 재고연동 사용안함'];
        }

        // 사음품 재고차감
        $arrInclude = [
            'giftNo',
            'giveCnt',
            'selectCnt',
            'minusStockFl',
        ];
        $arrField = DBTableField::setTableField('tableOrderGift', $arrInclude, null, 'og');
        $strSQL = 'SELECT og.sno, g.giftNm, ' . implode(', ', $arrField) . ' FROM ' . DB_ORDER_GIFT . ' og LEFT JOIN ' . DB_GIFT . ' g ON g.giftNo = og.giftNo WHERE og.orderNo = ? AND og.minusStockFl = \'n\' ORDER BY og.sno ASC';
        $arrBind = [
            's',
            $orderNo,
        ];
        $getData = $this->db->query_fetch($strSQL, $arrBind);
        unset($arrBind, $arrInclude, $arrField);

        if (empty($getData) === false) {
            foreach ($getData as $key => $val) {
                if (empty($val['giftNo']) === false) {
                    $strWhere = 'giftNo = \'' . $val['giftNo'] . '\' AND stockFl = \'y\'';
                    $affectedRow = $this->db->set_update_db(DB_GIFT, 'stockCnt = (SELECT IF((CONVERT((SELECT stockCnt), SIGNED) - ' . $val['giveCnt'] . ') < 0, 0, (stockCnt - ' . $val['giveCnt'] . ')))', $strWhere);
                    unset($strWhere);

                    if ($affectedRow > 0) {
                        // 사은품 재고차감 로그 저장 (번역작업하지 말것)
                        $this->orderLog($orderNo, '', '재고차감', '완료', $val['giftNm'] . '사은품 재고차감');

                        // 재고차감 여부 체크
                        $strWhere = 'sno = ' . $val['sno'];
                        $this->db->set_update_db(DB_ORDER_GIFT, 'minusStockFl = \'y\'', $strWhere);
                        unset($strWhere);
                    }
                }
            }
        }
        unset($getData);

        // 상품 모듈 호출
        $goods = \App::load('\\Component\\Goods\\Goods');

        // 주문 상품 데이타
        $strWhere = 'sno IN (' . implode(', ', $arrGoodsSno) . ')';
        $arrInclude = [
            'orderCd',
            'goodsType',
            'goodsNo',
            'goodsNm',
            'goodsCnt',
            'optionInfo',
            'minusStockFl',
            'minusRestoreStockFl',
        ];
        $arrField = DBTableField::setTableField('tableOrderGoods', $arrInclude);
        $strSQL = 'SELECT sno, ' . implode(', ', $arrField) . ' FROM ' . DB_ORDER_GOODS . ' WHERE ' . $strWhere . ' AND orderNo = ? AND minusStockFl = \'n\' ORDER BY sno ASC';
        $arrBind = [
            's',
            $orderNo,
        ];
        $getData = $this->db->query_fetch($strSQL, $arrBind);
        unset($arrBind);

        if (empty($getData) === true) {
            return false;
        }

        $sendStockMail = false;
        $logCodeArr = $logDescArr = [];
        foreach ($getData as $key => $val) {
            if ($val['goodsType'] == 'addGoods') {
                // goodsNo bind data
                $this->db->bind_param_push($arrBind, 'i', $val['goodsNo']);
                $arrWhere[] = 'ag.addGoodsNo = ?';

                // 추가상품 옵션 데이타
                $this->db->strField = 'ag.stockCnt, ag.stockUseFl';
                $this->db->strWhere = implode(' AND ', $arrWhere);
                $query = $this->db->query_complete();
                $strSQL = 'SELECT ' . array_shift($query) . ' FROM ' . DB_ADD_GOODS . ' ag ' . implode(' ', $query);
                $addGoodsData = $this->db->query_fetch($strSQL, $arrBind, false);
                $isStockCut = false;
                if (empty($addGoodsData) === false) {
                    // 재고사용 조건이면서 재고가 없거나 재고수량보다 구매수량이 많은 경우 구매불가
                    if ($addGoodsData['stockUseFl'] == '1') {
                        if ($addGoodsData['stockCnt'] > 0 && $addGoodsData['stockCnt'] - $val['goodsCnt'] >= 0) {
                            // 추가상품 재고차감
                            $strWhere = 'addGoodsNo = \'' . $val['goodsNo'] . '\' AND stockUseFl = \'1\' AND stockCnt - 1 >= 0';
                            $updateResult = $this->db->set_update_db(DB_ADD_GOODS, 'stockCnt = (SELECT IF((CONVERT((SELECT stockCnt), SIGNED) - ' . $val['goodsCnt'] . ') < 0, 0, (stockCnt - ' . $val['goodsCnt'] . ')))', $strWhere);
                            if (!$updateResult) {
                                throw new AlertRedirectException(__('[오류] 재고가 없어 차감 불가'), null, null, '../order/cart.php', 'top');
                            }
                            unset($strWhere);
                            unset($updateResult);

                            $logCodeArr[] = $logCode = '완료';
                            $logDescArr[] = $logDesc = sprintf('기존 %s개에서 %s개 차감', number_format($addGoodsData['stockCnt']), number_format($val['goodsCnt']));
                            $isStockCut = true;
                        } else {
                            $logCodeArr[] = $logCode = '오류';
                            $logDescArr[] = $logDesc = '재고가 없어 차감 불가';
                        }
                    } else {
                        $logCodeArr[] = $logCode = '불필요';
                        $logDescArr[] = $logDesc = '무한정 재고라서 차감 없음';
                    }
                } else {
                    $logCodeArr[] = $logCode = '오류';
                    $logDescArr[] = $logDesc = '해당 추가상품이 존재하지 않습니다.(주문이후 추가상품 변경 or 추가상품 삭제)';
                }
                unset($arrWhere, $arrBind, $tmpOption);

                // 추가상품 재고차감 로그 저장 (한번만 저장함) - 번역하지말것
                $this->orderLog($orderNo, $val['sno'], '추가상품 재고차감', $logCode, $logDesc);
            } else {
                // goodsNo bind data
                $this->db->bind_param_push($arrBind, 'i', $val['goodsNo']);
                $arrWhere[] = 'go.goodsNo = ?';

                // 옵션 where문 data
                if (empty($val['optionInfo']) === true) {
                    $arrWhere[] = 'go.optionNo = ?';
                    $arrWhere[] = '(go.optionValue1 = \'\' OR isnull(go.optionValue1))';
                    $this->db->bind_param_push($arrBind, 'i', 1);
                } else {
                    $tmpOption = json_decode(gd_htmlspecialchars_stripslashes($val['optionInfo']), true);
                    foreach ($tmpOption as $oKey => $oVal) {
                        $optionKey = $oKey + 1;
                        $arrWhere[] = 'go.optionValue' . $optionKey . ' = ?';
                        $optionNm[] = $oVal[1];
                        $this->db->bind_param_push($arrBind, 's', $oVal[1]);
                    }
                }

                // 상품 옵션 데이타
                $this->db->strField = 'go.optionValue1, go.optionValue2, go.optionValue3, go.optionValue4, go.optionValue5, go.stockCnt, go.sellStopFl, go.sellStopStock, go.confirmRequestFl, go.confirmRequestStock, go.optionNo, g.stockFl';
                $this->db->strWhere = implode(' AND ', $arrWhere);
                $this->db->strJoin = 'INNER JOIN ' . DB_GOODS . ' as g ON go.goodsNo = g.goodsNo';

                $query = $this->db->query_complete();
                $strSQL = 'SELECT ' . array_shift($query) . ' FROM ' . DB_GOODS_OPTION . ' go ' . implode(' ', $query);
                $optionData = $this->db->query_fetch($strSQL, $arrBind, false);
                unset($tmpOption, $tmpValue);
                $isStockCut = false;
                if (empty($optionData) === false) {
                    if ($optionData['stockFl'] == 'y') {
                        // 재고가 있는 경우만 차감 처리
                        if ($optionData['stockCnt'] > 0 && $optionData['stockCnt'] - $val['goodsCnt'] >= 0) {
                            $arrWhere[] = 'go.stockCnt - 1 >= 0';
                            // 상품 재고 수정
                            $updateResult = $this->db->set_update_db(DB_GOODS_OPTION, 'stockCnt = (SELECT IF((CONVERT((SELECT stockCnt), SIGNED) - ' . $val['goodsCnt'] . ') < 0, 0, (stockCnt - ' . $val['goodsCnt'] . ')))', str_replace('go.', '', implode(' AND ', $arrWhere)), $arrBind);

                            if (!$updateResult) {
                                throw new AlertRedirectException(__('[오류] 재고가 없어 차감 불가'), null, null, '../order/cart.php', 'top');
                            }
                            unset($updateResult);

                            // 재고 로그 저장
                            $this->stockLog($val['goodsNo'], $orderNo, implode('/', gd_isset($optionNm, [])), $optionData['stockCnt'], ($optionData['stockCnt'] - $val['goodsCnt']), -$val['goodsCnt'], '상품 주문에 의한 재고차감');

                            // 상품 전체 재고 갱신
                            $goods->setGoodsStock($val['goodsNo']);

                            $logCodeArr[] = $logCode = '완료';
                            $logDescArr[] = $logDesc = sprintf('기존 %s개에서 %s개 차감', number_format($optionData['stockCnt']), number_format($val['goodsCnt']));
                            $isStockCut = true;
                            // 상품 품절 SMS
                            if (($optionData['stockCnt'] - $val['goodsCnt']) <= 0) {
                                $orderGoodsData[] = [
                                    'goodsNo' => $val['goodsNo'],
                                    'orderCd' => $val['orderCd'],
                                ];
                                $this->sendOrderInfo(Code::SOLD_OUT, 'sms', $orderNo, $orderGoodsData);
                                unset($orderGoodsData);
                            }
                            //옵션명 만들기
                            for($i=1; $i<=5; $i++){
                                if(!empty($optionData['optionValue'.$i])){
                                    $optTmp[] = $optionData['optionValue'.$i];
                                }
                            }
                            $optionName = implode('/', $optTmp);
                            /*
                            현재 추가 개발진행 중이므로 수정하지 마세요! 주석 처리된 내용을 수정할 경우 기능이 정상 작동하지 않거나, 추후 기능 배포시 오류의 원인이 될 수 있습니다.
                            // 판매중지 재고 옵션 SMS / 카카오톡 / 메일
                            if ($optionData['sellStopFl'] == 'y' && ($optionData['stockCnt'] - $val['goodsCnt']) <= $optionData['sellStopStock']) {
                                $policy = ComponentUtils::getPolicy('goods.stock_notification');
                                $sms = new SmsStock($policy['goodsStock']);
                                $sms->setGoods($val['goodsNo'], $val['goodsNm'], $optionData['optionNo'], $val['goodsNo'], $optionName, 'stop', $optionData['stockCnt'] - $val['goodsCnt']);
                                $sms->sendSMS();
                                $sms = new KakaoAlimStock($policy['goodsStock']);
                                $sms->setGoods($val['goodsNo'], $val['goodsNm'], $optionData['optionNo'], $val['goodsNo'], $optionName, 'stop', $optionData['stockCnt'] - $val['goodsCnt']);
                                $sms->sendKakao();
                                $sendStockMail = true;
                            }
                            // 확인요청 재고 옵션 SMS / 카카오톡 / 메일
                            if ($optionData['confirmRequestFl'] == 'y' && ($optionData['stockCnt'] - $val['goodsCnt']) <= $optionData['confirmRequestStock']) {
                                $policy = ComponentUtils::getPolicy('goods.stock_notification');
                                $sms = new KakaoAlimStock($policy['goodsStock']);
                                $sms->setGoods($val['goodsNo'], $val['goodsNm'], $optionData['optionNo'], $val['goodsNo'], $optionName, 'request', $optionData['stockCnt'] - $val['goodsCnt']);
                                $sms->sendKakao();
                                $sendStockMail = true;
                            }
                            */
                        } else {
                            $logCodeArr[] = $logCode = '오류';
                            $logDescArr[] = $logDesc = '재고가 없어 차감 불가';
                        }
                    } else {
                        $logCodeArr[] = $logCode = '불필요';
                        $logDescArr[] = $logDesc = '무한정 재고라서 차감 없음';
                    }
                } else {
                    $logCodeArr[] = $logCode = '오류';
                    $logDescArr[] = $logDesc = '해당 옵션 상품이 존재하지 않습니다.(주문이후 옵션이 변경 or 상품 삭제)';
                }
                unset($arrWhere, $arrBind);

                // 주문 로그 저장 (번역하지말것)
                $this->orderLog($orderNo, $val['sno'], '재고차감', $logCode, $logDesc);
            }

            // 공통 키값
            $arrDataKey = ['orderNo' => $orderNo];
            $goodsData['sno'][0] = $val['sno'];
            if($this->channel == 'naverpay' && $isStockCut == false){  //네이버페이고 차감안됐으면
                $goodsData['minusStockFl'][0] = 'n';
            }
            else {
                $goodsData['minusStockFl'][0] = 'y';
            }

            $goodsData['minusRestoreStockFl'][0] = 'n';

            // 주문 상품 테이블의 차감 여부 변경
            $compareField = array_keys($goodsData);
            $getGoods[0] = $getData[$key];
            $compareGoods = $this->db->get_compare_array_data($getGoods, $goodsData, false, $compareField);
            $this->db->set_compare_process(DB_ORDER_GOODS, $goodsData, $arrDataKey, $compareGoods, $compareField);
            unset($goodsData, $compareField, $getGoods, $compareGoods);
        }

        if($sendStockMail == true){
            $policy = ComponentUtils::getPolicy('goods.stock_notification');
            $sms = new MailStock($policy['goodsStock']);
            $sms->setGoods($val['goodsNo'], $val['goodsNm'], $optionData['optionNo'], $val['goodsNo'], $optionName, 'request', $optionData['stockCnt'] - $val['goodsCnt']);
            $sms->sendMail();
        }

        return ['code' => $logCodeArr, 'desc' => $logDescArr];
    }

    /**
     * 재고 복원
     *
     * @param string $orderNo     주문 번호
     * @param array  $arrGoodsSno 주문상품 번호
     *
     * @return boolean
     * @internal param array $arrData 상태 정보
     */
    public function setGoodsStockRestore($orderNo, $arrGoodsSno)
    {
        $getOrderData = $this->getOrderData($orderNo);
        if ($getOrderData['orderChannelFl'] == 'naverpay') {
            $naverpayConfig = gd_policy('naverPay.config');
            if ($naverpayConfig['linkStock'] == 'n') {
                $this->orderLog($orderNo, '', '재고복원', '완료', '네이버페이 주문 재고연동 사용안함');
                return true;
            }
        }

        // 추가상품 재고 복원
        $arrInclude = [
            'addGoodsNo',
            'goodsCnt',
            'minusStockFl',
        ];
        $arrField = DBTableField::setTableField('tableOrderAddGoods', $arrInclude, null, 'ag');
        $strSQL = 'SELECT ag.sno, ' . implode(', ', $arrField) . ' FROM ' . DB_ORDER_ADD_GOODS . ' ag LEFT JOIN ' . DB_ORDER_GOODS . ' og ON og.orderCd = ag.orderCd AND og.orderNo = ag.orderNo WHERE og.sno in (\'' . implode('\',\'', $arrGoodsSno) . '\') AND og.orderNo = ? AND ag.minusStockFl = \'y\' AND ag.minusRestoreStockFl = \'n\' ORDER BY ag.sno ASC';
        $arrBind = [
            's',
            $orderNo,
        ];
        $getData = $this->db->query_fetch($strSQL, $arrBind);
        unset($arrBind, $arrInclude, $arrField);

        if (empty($getData) === false) {
            foreach ($getData as $key => $val) {
                if (empty($val['addGoodsNo']) === false) {
                    $strWhere = 'addGoodsNo = \'' . $val['addGoodsNo'] . '\' AND stockUseFl = \'1\'';
                    $this->db->set_update_db(DB_ADD_GOODS, 'stockCnt = (SELECT IF((CONVERT((SELECT stockCnt), SIGNED) + ' . $val['goodsCnt'] . ') < 0, 0, (stockCnt + ' . $val['goodsCnt'] . ')))', $strWhere);
                    unset($strWhere);
                }

                // 재고복원 여부 체크
                $strWhere = 'sno = ' . $val['sno'];
                $this->db->set_update_db(DB_ORDER_ADD_GOODS, 'minusStockFl = \'y\'', $strWhere);
                unset($strWhere);
            }

            // 추가상품 재고 복원 로그 저장 (한번만 저장함) - 번역하지 말것
            $this->orderLog($orderNo, $val['sno'], '재고 복원', '완료', '추가상품 재고복원');
        }
        unset($getData);

        // --- 상품 모듈 호출
        $goods = \App::load('\\Component\\Goods\\Goods');

        // 주문 상품 데이타
        $strWhere = 'sno IN (' . implode(', ', $arrGoodsSno) . ')';
        $arrInclude = [
            'orderCd',
            'goodsType',
            'goodsNo',
            'goodsNm',
            'goodsCnt',
            'optionInfo',
            'minusStockFl',
            'minusRestoreStockFl',
        ];
        $arrField = DBTableField::setTableField('tableOrderGoods', $arrInclude);
        $strSQL = 'SELECT sno, ' . implode(', ', $arrField) . ' FROM ' . DB_ORDER_GOODS . ' WHERE ' . $strWhere . ' AND orderNo = ? AND minusStockFl = \'y\' AND minusRestoreStockFl = \'n\' AND LEFT(orderStatus,1) != \'f\' ORDER BY sno ASC';
        $arrBind = [
            's',
            $orderNo,
        ];
        $getData = $this->db->query_fetch($strSQL, $arrBind);
        unset($arrBind);

        if (empty($getData) === true) {
            return false;
        }

        // 상품의 재고 처리
        foreach ($getData as $key => $val) {
            if ($val['goodsType'] === 'addGoods') {
                $strWhere = 'addGoodsNo = \'' . $val['goodsNo'] . '\' AND stockUseFl = \'1\'';
                $this->db->set_update_db(DB_ADD_GOODS, 'stockCnt = (SELECT IF((CONVERT((SELECT stockCnt), SIGNED) + ' . $val['goodsCnt'] . ') < 0, 0, (stockCnt + ' . $val['goodsCnt'] . ')))', $strWhere);
                unset($strWhere);

                // 추가상품 재고 복원 로그 저장 (한번만 저장함) - 번역하지 말 것
                $this->orderLog($orderNo, $val['sno'], '재고복원', '완료', '추가상품 재고복원');
            } else {
                // goodsNo bind data
                $this->db->bind_param_push($arrBind, 'i', $val['goodsNo']);
                $arrWhere[] = 'go.goodsNo = ?';

                // 옵션 where문 data
                if (empty($val['optionInfo']) === true) {
                    $arrWhere[] = 'go.optionNo = ?';
                    $arrWhere[] = '(go.optionValue1 = \'\' OR isnull(go.optionValue1))';
                    $this->db->bind_param_push($arrBind, 'i', 1);
                } else {
                    $tmpOption = json_decode(gd_htmlspecialchars_stripslashes($val['optionInfo']), true);
                    foreach ($tmpOption as $oKey => $oVal) {
                        $optionKey = $oKey + 1;
                        $arrWhere[] = 'go.optionValue' . $optionKey . ' = ?';
                        $optionNm[] = $oVal[1];
                        $this->db->bind_param_push($arrBind, 's', $oVal[1]);
                    }
                }

                // 상품 옵션 데이타
                $this->db->strField = 'go.stockCnt, g.stockFl';
                $this->db->strWhere = implode(' AND ', $arrWhere);
                $this->db->strJoin = 'INNER JOIN ' . DB_GOODS . ' as g ON go.goodsNo = g.goodsNo';

                $query = $this->db->query_complete();
                $strSQL = 'SELECT ' . array_shift($query) . ' FROM ' . DB_GOODS_OPTION . ' go ' . implode(' ', $query);
                $optionData = $this->db->query_fetch($strSQL, $arrBind, false);
                unset($tmpOption, $tmpValue);

                if (empty($optionData) === false) {
                    if (gd_isset($optionData['stockFl']) == 'y') {
                        // 상품 재고 수정
                        $this->db->set_update_db(DB_GOODS_OPTION, 'stockCnt = (stockCnt + ' . $val['goodsCnt'] . ')', str_replace('go.', '', implode(' AND ', $arrWhere)), $arrBind);

                        // 재고 로그 저장
                        $this->stockLog($val['goodsNo'], $orderNo, implode('/', gd_isset($optionNm, [])), $optionData['stockCnt'], ($optionData['stockCnt'] + $val['goodsCnt']), $val['goodsCnt'], '주문 취소/실패/환불에 의한 재고 복원');

                        // 상품 전체 재고 갱신
                        $goods->setGoodsStock($val['goodsNo']);

                        $logCode = '완료';
                        $logDesc = sprintf('기존 %s개에서 %s개 복원', number_format($optionData['stockCnt']), number_format($val['goodsCnt']));
                    } else {
                        $logCode = '불필요';
                        $logDesc = '무한정 재고라서 복원 없음';
                    }
                } else {
                    $logCode = '오류';
                    $logDesc = '해당 옵션 상품이 존재하지 않습니다.(주문이후 옵션이 변경 or 상품 삭제)';
                }
                unset($arrWhere, $arrBind);

                // 주문 로그 저장 (번역작업하지 말것)
                $this->orderLog($orderNo, $val['sno'], '재고복원', $logCode, $logDesc);
            }

            // 공통 키값
            $arrDataKey = ['orderNo' => $orderNo];
            $goodsData['sno'][0] = $val['sno'];
            $goodsData['minusStockFl'][0] = 'n';
            $goodsData['minusRestoreStockFl'][0] = 'y';

            // 주문 상품 테이블의 차감 여부 변경
            $compareField = array_keys($goodsData);
            $getGoods[0] = $getData[$key];
            $compareGoods = $this->db->get_compare_array_data($getGoods, $goodsData, false, $compareField);
            $this->db->set_compare_process(DB_ORDER_GOODS, $goodsData, $arrDataKey, $compareGoods, $compareField);
            unset($goodsData, $compareField, $getGoods, $compareGoods);
        }
    }

    /**
     * 주문 관련 메일 및 SMS 전송
     *
     * @param string $sendType       전송 상태 (ORDER - 주문확인관련, INCASH - 입금확인관련, DELIVERY - 배송/발송관련)
     * @param string $sendMode       전송 종류 (all - 메일/SMS, mail - 메일, sms - SMS)
     * @param string $orderNo        주문 번호
     * @param array  $orderGoodsData 주문 상품 정보 (0 => [goodsNo, orderCd] 값을 배열로, goodsNo 는 필수)
     * @param array  $claimPrice     클레임 주문 금액 (cancel - 부분 취소 금액, gdRefundPrice - 부분 환불 금액)
     * @param array  $smsCnt         sms전송 개수 (부분 일괄 환불시 중복 발송 금지를 위한 count)
     */
    public function sendOrderInfo($sendType, $sendMode, $orderNo, $orderGoodsData = null, $claimPrice = null, $smsCnt = null)
    {
        $logger = \App::getInstance('logger');
        if ($this->skipSendOrderInfo) {
            $logger->info('order [' . $orderNo . '] skip sendOrderInfo');
            return;
        }
        $logger->info(sprintf('Start sendOrderInfo. orderNo[%s], sendType[%s]', $orderNo, $sendType));
        // 데이타 체크
        if (empty($sendType) === true || empty($sendMode) === true || empty($orderNo) === true) {
            $logger->info(sprintf('Return sendType=%s, sendMode=%s, orderNo=%s', $sendType, $sendMode, $orderNo));
            return;
        }
        try {
            $isSendModeAll = ($sendMode == 'all');
            $isSendModeMail = ($isSendModeAll || $sendMode == 'mail');
            $isSendModeSms = ($isSendModeAll || $sendMode == 'sms');
            if ($isSendModeSms) {
                // 카카오알림톡설정먼저확인
                $kakaoPolicy = ComponentUtils::getPolicy('kakaoAlrim.kakaoAuto');
                $kakaoLunaPolicy = ComponentUtils::getPolicy('kakaoAlrimLuna.kakaoAuto');
                if (gd_is_plus_shop(PLUSSHOP_CODE_KAKAOALRIMLUNA) === true && $kakaoLunaPolicy['useFlag'] == 'y' && $kakaoLunaPolicy['orderUseFlag'] == 'y') {
                    $smsSendTypePolicy = $kakaoLunaPolicy['order'][$sendType];
                    $isSendModeSms = empty($smsSendTypePolicy) === false;
                }
                else if ($kakaoPolicy['useFlag'] == 'y' && $kakaoPolicy['orderUseFlag'] == 'y') {
                    $smsSendTypePolicy = $kakaoPolicy['order'][$sendType];
                    $isSendModeSms = empty($smsSendTypePolicy) === false;
                } else {
                    $smsPolicy = ComponentUtils::getPolicy('sms.smsAuto');
                    $smsSendTypePolicy = $smsPolicy['order'][$sendType];
                    $isSendModeSms = empty($smsSendTypePolicy) === false;
                }
            }
            if ($isSendModeSms) {
                $this->sendOrderInfoBySms($sendType, $orderNo, $orderGoodsData, $claimPrice, $smsCnt);
            }
            if ($isSendModeMail) {
                $this->sendOrderInfoByMail($sendType, $orderNo);
            }
        } catch (\Exception $e) {}
    }

    protected function getSendMailSmsFl($sendFl = [])
    {

        if (empty($sendFl) === false) {
            $sendFl = ArrayUtils::xmlToArray($sendFl);
        }
        StringUtils::strIsSet($sendFl['mail_ORDER'], 'n');
        StringUtils::strIsSet($sendFl['mail_INCASH'], 'n');
        StringUtils::strIsSet($sendFl['mail_DELIVERY'], 'n');
        StringUtils::strIsSet($sendFl['sms_ORDER'], 'n');
        StringUtils::strIsSet($sendFl['sms_INCASH'], 'n');
        StringUtils::strIsSet($sendFl['sms_ACCOUNT'], 'n');
        StringUtils::strIsSet($sendFl['sms_DELIVERY'], 'n');
        StringUtils::strIsSet($sendFl['sms_INVOICE_CODE'], 'n');
        StringUtils::strIsSet($sendFl['sms_DELIVERY_COMPLETED'], 'n');
        StringUtils::strIsSet($sendFl['sms_CANCEL'], 'n');
        StringUtils::strIsSet($sendFl['sms_REPAY'], 'n');
        StringUtils::strIsSet($sendFl['sms_REPAYPART'], 'n');
        StringUtils::strIsSet($sendFl['sms_SOLD_OUT'], 'n');

        return $sendFl;
    }

    protected function sendOrderInfoBySms($sendType, $orderNo, $orderGoodsData = null, $claimPrice = null, $smsCnt = null)
    {
        $logger = \App::getInstance('logger');
        // 주문 정보
        $orderData = $this->getOrderDataSend($orderNo);

        // 주문 데이터가 없는 경우
        if (empty($orderData) === true) {
            $logger->info(sprintf('Return empty orderData by order number %s', $orderNo));
            return;
        }

        // 보냈는지 여부에 대한 처리
        $sendFl = $this->getSendMailSmsFl($orderData['sendMailSmsFl']);

        /**
         * sms 발송 처리
         *
         * @param $orderMallSno
         * @param $sendType
         * @param $receiverInfo
         * @param $replaceArguments
         * @param $orderData
         */
        $sendSms = function($orderMallSno, $sendType, $receiverInfo, $replaceArguments, $orderData) use ($logger) {
            $logger->info('Closure sendSms.', func_get_args());
            // SMS 전송();
            if ($this->isDefaultMall($orderMallSno) !== false) {
                $smsAuto = \App::load('Component\\Sms\\SmsAuto');
                if ($smsAuto->useObserver()) {
                    $observer = new SmsAutoObserver();
                    $observer->setSmsType(SmsAutoCode::ORDER);
                    $observer->setSmsAutoCodeType($sendType);
                    $observer->setReceiver($receiverInfo);
                    $observer->setReplaceArguments($replaceArguments);
                    $observer->setValidateFunction(function() use($orderData) {
                        return $this->existOrder($orderData['orderNo']);
                    });
                    $smsAuto->attach($observer);
                } else {
                    $smsAuto = new SmsAuto();
                    $smsAuto->setSmsType(SmsAutoCode::ORDER);
                    $smsAuto->setSmsAutoCodeType($sendType);
                    $smsAuto->setReceiver($receiverInfo);
                    $smsAuto->setReplaceArguments($replaceArguments);
                    $smsAuto->autoSend();
                }
            } else {
                $logger->info(sprintf('SMS only standard store. this order mall sno is %s', $orderMallSno));
            }
        };
        // 전송 여부
        $sendSmsFl = true;

        // sms 개선(sms, 카카오 "건 별 발송" 설정 체크 및 부분 취소, 부분 환불시 sms 발송)
        $smsSendPartFl = false;
        $kakaoSendPartFl = false;
        $claimFl = false;

        $kakaoAutoConfig = ComponentUtils::getPolicy('kakaoAlrim.kakaoAuto');
        $kakaoLunaAutoConfig = ComponentUtils::getPolicy('kakaoAlrimLuna.kakaoAuto');
        if ($kakaoLunaAutoConfig['useFlag'] == 'y' && $kakaoLunaAutoConfig['orderUseFlag'] == 'y') {
            $kakaoSendOrdCancelFl = $kakaoLunaAutoConfig['order'][$sendType]['smsOrdCancelSend'];
            $kakaoSendOrdRefundFl = $kakaoLunaAutoConfig['order'][$sendType]['smsOrdRefundSend'];
            $kakaoSendOrdDeliveryFl = $kakaoLunaAutoConfig['order'][$sendType]['smsOrdDeliverySend'];
            $kakaoSendOrdDeliveryCompleteFl = $kakaoLunaAutoConfig['order'][$sendType]['smsOrdDeliveryCompletedSend'];
            $kakaoSendPartFl = true;

            StringUtils::strIsSet($kakaoLunaAutoConfig['order'][$sendType]['smsDelivery'], 'n');
            $smsDeliveryConfigBySendType = $kakaoLunaAutoConfig['order'][$sendType]['smsDelivery'];
        }
        else if ($kakaoAutoConfig['useFlag'] == 'y' && $kakaoAutoConfig['orderUseFlag'] == 'y') {
            $kakaoSendOrdCancelFl = $kakaoAutoConfig['order'][$sendType]['smsOrdCancelSend'];
            $kakaoSendOrdRefundFl = $kakaoAutoConfig['order'][$sendType]['smsOrdRefundSend'];
            $kakaoSendOrdDeliveryFl = $kakaoAutoConfig['order'][$sendType]['smsOrdDeliverySend'];
            $kakaoSendOrdDeliveryCompleteFl = $kakaoAutoConfig['order'][$sendType]['smsOrdDeliveryCompletedSend'];
            $kakaoSendPartFl = true;

            StringUtils::strIsSet($kakaoAutoConfig['order'][$sendType]['smsDelivery'], 'n');
            $smsDeliveryConfigBySendType = $kakaoAutoConfig['order'][$sendType]['smsDelivery'];
        }else{
            $smsAutoConfig = ComponentUtils::getPolicy('sms.smsAuto');
            $smsSendOrdCancelFl = $smsAutoConfig['order'][$sendType]['smsOrdCancelSend'];
            $smsSendOrdRefundFl = $smsAutoConfig['order'][$sendType]['smsOrdRefundSend'];
            $smsSendOrdDeliveryFl = $smsAutoConfig['order'][$sendType]['smsOrdDeliverySend'];
            $smsSendOrdDeliveryCompleteFl = $smsAutoConfig['order'][$sendType]['smsOrdDeliveryCompletedSend'];
            $smsSendPartFl = true;

            StringUtils::strIsSet($smsAutoConfig['order'][$sendType]['smsDelivery'], 'n');
            $smsDeliveryConfigBySendType = $smsAutoConfig['order'][$sendType]['smsDelivery'];
        }

        if($sendType == 'CANCEL') {  // 클레임 상태가 취소 일때
            // 클레임 상태가 있는지 체크(부분 취소)
            $ordClaimChk = $this->getOrderGoodsClaimData($orderNo);
            $claimCancelPrice = $claimPrice['cancelPrice']; // 취소 금액

            if($ordClaimChk['mode'] == 'cancel') {   // 부분 취소
                $claimFl = true;
                // "건 별 발송" 설정이 되어있다면
                if (($kakaoSendPartFl === true && $kakaoSendOrdCancelFl == 'y') || ($smsSendPartFl === true && $smsSendOrdCancelFl == 'y')) {
                    if ($smsCnt <= 1) { // 수량 부분 취소 시
                        $sendFl['sms_' . $sendType] = 'n';
                    }
                } else {
                    $sendFl['sms_' . $sendType] = 'y';
                }
            } else if($ordClaimChk['mode'] == 'allAutoCancel'){  // 자동 취소
                $claimCancelPrice = $orderData['settlePrice'];  // 자동 취소시 취소된 값을 settlePrice값으로
            } else if($ordClaimChk['mode'] == 'allCancel'){ // 전체 취소
                $claimFl = true;
                if ($smsCnt <= 1) {
                    // 수량 부분 취소건도 포함
                    $sendFl['sms_' . $sendType] = 'n';
                }
            }
        }

        if($sendType == 'REPAY'){   // 클레임 상태가 환불 일때
            // 클레임 상태가 있는지 체크(부분 취소)
            $ordClaimChk = $this->getOrderGoodsClaimData($orderNo);
            $claimRefundPrice = $claimPrice['gdRefundPrice'];   // 환불 금액
            if($ordClaimChk['mode'] == 'repay') {   //부분 환불
                // "건 별 발송" 설정이 되어있다면
                if (($kakaoSendPartFl === true && $kakaoSendOrdRefundFl == 'y') || ($smsSendPartFl === true && $smsSendOrdRefundFl == 'y')) {
                    $claimFl = true;
                    if ($smsCnt <= 1) {
                        $sendFl['sms_' . $sendType] = 'n';
                    }
                } else {
                    $sendFl['sms_' . $sendType] = 'y';
                }
            }else if($ordClaimChk['mode'] == 'allRepay' && $orderData['orderStatusOrigin'] == 'r3') { // 전체 환불
                $claimFl = true;
                if ($smsCnt <= 1) {
                    // 수량 부분 환불의 경우
                    $sendFl['sms_' . $sendType] = 'n';
                }else{
                    // 환불접수 2건을 환불 완료 처리하면서 해당 주문건이 완전한 환불완료가 되는 경우 + sms 건 별 발송에 체크되어있지 않다면 전체 환불 sms발송되어야 함.
                    if (($kakaoSendPartFl === true && empty($kakaoSendOrdRefundFl) == true) || ($smsSendPartFl === true && empty($smsSendOrdRefundFl) == true)) {
                        $sendFl['sms_' . $sendType] = 'n';
                    }
                }
            }
        }

        // sms 개선 xml로 변경
        if($claimFl === true){
            $orderData['sendMailSmsFl'] = '<root>' . ArrayUtils::arrayToXml($sendFl) . '</root>';
        }

        // 이미 보내진 상태라면
        if (gd_isset($sendFl['sms_' . $sendType], 'y') === 'y' && $sendType != Code::REPAYPART) {
            $logger->warning(sprintf('This order sms was sent. sendFl[%s]', $sendFl['sms_' . $sendType]));
            $sendSmsFl = false;
        } else {
            $sendFl['sms_' . $sendType] = 'y';
        }

        if ($sendSmsFl === true) {
            try {
                $logger->info('Send order sms.');

                // 송장번호 안내 시 주문상품 조회
                if ($sendType == Code::INVOICE_CODE) {
                    /**
                     * 상품주문 데이터를 조회하는 함수
                     *
                     * @param array $arrOrderGoods
                     *
                     * @return array
                     */
                    $getOrderGoodsData = function(array $arrOrderGoods) use ($orderNo, $sendType, $logger) {
                        $arrResult = $arrInvoice = $arrSendSmsFl = [];
                        foreach ($arrOrderGoods as $orderGoods) {
                            $sendSmsFl = json_decode($orderGoods['sendSmsFl'], true);
                            $sendFl = $sendSmsFl[$sendType]['sendFl'];
                            StringUtils::strIsSet($sendFl, 'n');
                            if ($sendFl == 'n' && $orderGoods['orderStatus'] == 'd1') {   // 주문상품의 문자 발송 내역이 미발송인 주문상품만 배열에 저장
                                //@formatter:off
                                $arrResult[] = [
                                    'goodsNo' => $orderGoods['sno'], 'orderNo' => $orderNo, 'orderCd' => $orderGoods['orderCd'], 'invoiceCompanySno' => $orderGoods['invoiceCompanySno'],
                                    'invoiceNo' => $orderGoods['invoiceNo'], 'orderStatus' => $orderGoods['orderStatus'], 'goodsNm' => $orderGoods['goodsNm'],
                                    'sendSmsFl' => $orderGoods['sendSmsFl'], 'sendMailFl' => $orderGoods['sendMailFl'], 'deliveryMethodFl' => $orderGoods['deliveryMethodFl']
                                ];
                                $sendSmsFl[$sendType]['sendFl'] = 'y';
                                //@formatter:on
                            } else if ($sendFl == 'y' && !empty($orderGoods['invoiceCompanySno']) && !empty($orderGoods['invoiceNo'])) {
                                $arrInvoice[$orderGoods['invoiceCompanySno']][] = $orderGoods['invoiceNo'];     // 이미 발송된 택배사/송장번호 여부 확인을 위해 배열에 저장
                            }
                            $arrSendSmsFl[$orderGoods['sno']] = $sendSmsFl;     // 전체 주문상품이 발송되었는지 체크용 배열에 발송여부 저장
                        }
                        $logger->info('invoice log', $arrInvoice);

                        return [$arrResult, $arrInvoice, $arrSendSmsFl];
                    };

                    /**
                     * 조회된 주문상품 중 발송 대상이 아닌 주문상품을 제거하는 함수
                     *
                     * @param array $arrOrderGoods
                     * @param array $arrInvoice
                     *
                     * @return array
                     */
                    $unsetSendedInvoiceCodeWithSaveSendSmsFl = function(array $arrOrderGoods, array $arrInvoice) use($logger) {
                        $logger->info('Call unsetSendedInvoiceCodeWithSaveSendSmsFl');
                        $arrResult = [];
                        foreach ($arrOrderGoods as $orderGoodsIndex => $orderGoods) {
                            if ($orderGoods['orderStatus'] != 'd1') {
                                $logger->info(sprintf('This orderGoods send sms skip. orderGoods sno is %s. orderStatus is not `d1`.', $orderGoods['goodsNo']));
                                continue;   // 조회한 주문상품이 배송중이 아닌경우 제외
                            }
                            if ($orderGoods['deliveryMethodFl'] == 'visit') {
                                $logger->info(sprintf('This orderGoods send sms skip. orderGoods sno is %s. deliveryMethodFl is `visit`.', $orderGoods['goodsNo']));
                                continue;   // 조회한 주문상품이 방문수령일 경우 제외
                            }
                            if (in_array($orderGoods['invoiceNo'], $arrInvoice[$orderGoods['invoiceCompanySno']])) {
                                $logger->info(sprintf('This orderGoods send sms skip. orderGoods sno is %s. sended invoiceCompany %s and invoiceNo %s.', $orderGoods['goodsNo'], $orderGoods['invoiceCompanySno'], $orderGoods['invoiceNo']));
                                $this->saveSendResultByOrderGoods([
                                    'sendSmsFl' => [Code::INVOICE_CODE => ['sendFl' => 'y',]],
                                    'goodsNo' => $orderGoods['goodsNo'],
                                    'orderNo' => $orderGoods['orderNo'],
                                ]);  // 배송중 상태 변경된 주문상품의 택배사/송장번호가 이미 발송된 택배사/송장번호인 경우 발송됨으로 저장
                            } else if (!empty($orderGoods['invoiceCompanySno']) && !empty($orderGoods['invoiceNo'])) {
                                $arrResult[$orderGoods['goodsNo']] = $orderGoods;
                            } else {
                                $logger->info(sprintf('This orderGoods send sms skip. orderGoods sno is %s. empty invoiceCompanySno or invoiceNo.', $orderGoods['goodsNo']));
                            }
                        }

                        return $arrResult;
                    };

                    $setDeliveryName = function(array $arrOrderGoods) {
                        $arrResult = [];
                        if (count($arrOrderGoods) > 0) {
                            $delivery = \App::load('Component\\Delivery\\Delivery');
                            $companyList = $delivery->getDeliveryCompany();
                            $arrSno = ArrayUtils::getSubArrayByKey($companyList, 'sno');
                            $arrCompanyName = ArrayUtils::getSubArrayByKey($companyList, 'companyName');
                            $arrDelivery = array_combine($arrSno, $arrCompanyName);
                            foreach ($arrOrderGoods as $index => $item) {
                                if (empty($item['invoiceCompanySno']) || $item['invoiceCompanySno'] < 1) {
                                    continue;
                                }
                                $item['deliveryName'] = $arrDelivery[$item['invoiceCompanySno']];
                                $arrResult[$index] = $item;
                            }
                        }

                        return $arrResult;
                    };

                    $arrOrderGoods = $this->getOrderGoods($orderNo);
                    list ($orderGoodsData, $arrInvoice, $arrSendSmsFl) = $getOrderGoodsData($arrOrderGoods);
                    $orderGoodsData = $unsetSendedInvoiceCodeWithSaveSendSmsFl($orderGoodsData, $arrInvoice);
                    $orderGoodsData = $setDeliveryName($orderGoodsData);
                    $logger->info('send invoiceCode. orderGoodsData info => ', $orderGoodsData);
                    $logger->info('send invoiceCode. arrInvoice info => ', $arrInvoice);
                    $logger->info('send invoiceCode. arrSendSmsFl info => ', $arrSendSmsFl);
                    foreach ($arrSendSmsFl as $sendSmsFlIndex => $sendSmsFl) {
                        if ($sendSmsFl[$sendType]['sendFl'] != 'y') {
                            $sendFl['sms_' . $sendType] = 'n';  // 주문상품 중 발송되지 않은 주문상품이 있는 경우 주문의 발송여부를 미발송으로 설정함
                            break;
                        }
                    }
                    $logger->info('send invoiceCode send flag info => ', $sendFl);
                    if ($smsDeliveryConfigBySendType == 'n') {
                        $sendFl['sms_' . $sendType] = 'y';  //  상품배송 안내의 부분배송 시 배송 건별 SMS 발송 옵션이 해제된 경우 해당 주문의 SMS 발송 여부가 발송으로 처리됨.
                        $logger->info('Disabled smsDelivery option. sms per order mode');
                    }
                    // 상품 정보
                    $orderData['goods'] = $orderGoodsData;
                } else if($sendType == 'DELIVERY' || $sendType == 'DELIVERY_COMPLETED') {
                    $arrResult = [];
                    $arrOrderGoods = $this->getOrderGoods($orderNo);
                    if($sendType == 'DELIVERY') {
                        $partSendFl = ($kakaoSendPartFl === true && $kakaoSendOrdDeliveryFl == 'y') || ($smsSendPartFl === true && $smsSendOrdDeliveryFl == 'y');
                        $sendStatus = 'd1';
                    } else if($sendType == 'DELIVERY_COMPLETED') {
                        $partSendFl = ($kakaoSendPartFl === true && $kakaoSendOrdDeliveryCompleteFl == 'y') || ($smsSendPartFl === true && $smsSendOrdDeliveryCompleteFl == 'y');
                        $sendStatus = 'd2';
                    }
                    $alreadySentFl = false;
                    foreach ($arrOrderGoods as $orderGoods) {
                        $sendSmsFl = json_decode($orderGoods['sendSmsFl'], true);
                        $_sendFl = $sendSmsFl[$sendType]['sendFl'];
                        StringUtils::strIsSet($_sendFl, 'n');
                        if(!$partSendFl && $_sendFl == 'y') {
                            $alreadySentFl = true;
                            $logger->warning(sprintf('This order has already sent sms. orderGoodsNo[%s] sendStatus[%s]', $orderGoods['sno'], $sendStatus));
                            unset($arrResult);
                            break;
                        }
                        if ($_sendFl == 'n' && $orderGoods['orderStatus'] == $sendStatus) {
                            $arrResult[] = [
                                'goodsNo' => $orderGoods['sno'], 'orderNo' => $orderNo, 'orderCd' => $orderGoods['orderCd'], 'invoiceCompanySno' => $orderGoods['invoiceCompanySno'],
                                'invoiceNo' => $orderGoods['invoiceNo'], 'orderStatus' => $orderGoods['orderStatus'], 'goodsNm' => $orderGoods['goodsNm'],
                                'sendSmsFl' => $orderGoods['sendSmsFl'], 'sendMailFl' => $orderGoods['sendMailFl'], 'deliveryMethodFl' => $orderGoods['deliveryMethodFl']
                            ];
                            $sendSmsFl[$sendType]['sendFl'] = 'y';
                        }
                        $arrSendSmsFl[$orderGoods['sno']] = $sendSmsFl;
                    }
                    if(empty($arrResult) || $alreadySentFl) {
                        $logger->warning(sprintf('This order has already sent sms. orderNo[%s] sendStatus[%s]', $orderNo, $sendStatus));
                        throw new Exception('이미 발송 된 주문번호');
                    }
                    foreach ($arrSendSmsFl as $sendSmsFlIndex => $sendSmsFl) {
                        if ($sendSmsFl[$sendType]['sendFl'] != 'y') {
                            $sendFl['sms_' . $sendType] = 'n';  // 주문상품 중 발송되지 않은 주문상품이 있는 경우 주문의 발송여부를 미발송으로 설정함 (주문별 발송이여도 부분배송으로 변경할 경우 발송해야하기때문에 미발송으로저장)
                            break;
                        }
                    }
                    $orderData['goods'] = $arrResult;
                } else {
                    // 상품 정보
                    $orderData['goods'] = $this->getOrderGoodsForSend($orderNo, $orderGoodsData, $sendType);
                }

                $smsSendFl = true;
                foreach ($orderData['goods']['deliveryMethodFl'] as $key => $deliveryMethodFl) {
                    if (substr($orderData['goods']['orderStatus'][$key], 0, 1) == 'd') {
                        if ($deliveryMethodFl == 'visit') $smsSendFl = false;
                        else {
                            $smsSendFl = true;
                            break;
                        }
                    }
                }
                if ($smsSendFl === false) throw new Exception('방문수령으로 인한 배송문자 미발송');

                // SMS 전송 번호
                $receiverInfo['scmNo'] = gd_isset($orderData['goods']['scmNo']);
                $receiverInfo['memNo'] = gd_isset($orderData['memNo']);
                $receiverInfo['memNm'] = gd_isset($orderData['orderName']);
                $receiverInfo['smsFl'] = 'y';
                $receiverInfo['cellPhone'] = gd_isset($orderData['orderCellPhone']);

                // 상품명 처리
                // 2017-06-02 yjwee 아래 로직을 제거할 경우 품절 상품명이 노출되지 않음
                $orderData['goodsNm'] = $orderData['orderGoodsNm'];
                if (empty($orderGoodsData) === false) {
                    if (isset($orderData['goods']['goodsNm']) === true) {
                        $goodsCnt = count($orderData['goods']['goodsNm']);
                        if ($goodsCnt > 1) {
                            $orderData['goodsNm'] = $orderData['goods']['goodsNm'] . '외' . ($goodsCnt - 1) . '건';
                        } else {
                            $orderData['goodsNm'] = $orderData['goods']['goodsNm'];
                        }
                    }
                }

                $aRequest = \Request::post()->toArray();
                // refundPrice는 카드부분취소일때만 사용할금액이기에 $aRequest['info']['completePgPrice'] 값을 고정할당
                // 전송 정보
                $orderInfo = [
                    'orderName'      => gd_isset($orderData['orderName']),
                    'orderNo'        => $orderNo,
                    'orderCellPhone' => gd_isset($orderData['orderCellPhone']),
                    'settlePrice'    => gd_money_format($orderData['settlePrice']),
                    'settleName'     => $orderData['settleName'],
                    'goodsNm'        => $orderData['goodsNm'],
                    'refundPrice'    => $aRequest['info']['completePgPrice'],
                    'orderDate'      => DateTimeUtils::dateFormat('Y-m-d', $orderData['regDt']),
                    'orderGoodsCnt'  => $orderData['orderGoodsCnt'],
                    'cancelPrice'    => gd_money_format($claimCancelPrice),
                    'gbRefundPrice'  => gd_money_format($claimRefundPrice),
                    'depositNm'      => $orderData['bankSender'],
                ];

                // 카카오알림톡용
                $aBasicInfo = gd_policy('basic.info');
                $orderInfo['rc_mallNm'] = $aBasicInfo['mallNm'];
                $orderInfo['shopUrl'] = $aBasicInfo['mallDomain'];

                // 전송 타입별 - 송장번호
                if ($sendType == 'INVOICE_CODE') {
                    /**
                     * 송장번호 별로 발송 정보를 묶는 함수
                     *
                     * @param array $orderData 주문정보
                     * @param array $replaceCode 발송 시 사용될 치환코드 정보
                     *
                     * @return array
                     */
                    $groupByInvoiceNo = function(array $orderData, array $replaceCode) use ($logger, $smsDeliveryConfigBySendType, $sendType) {
                        $arrReplaceCode = [];
                        $goods = $orderData['goods'];
                        $group = [];        // 송장번호별 주문상품 그룹 배열
                        foreach ($goods as $index => $item) {
                            if(!is_array($item)){
                                continue;
                            }
                            if (empty($item['deliveryName']) || empty($item['invoiceNo'])) {        // 송장번호 검증 실패
                                $logger->warning('invoice validate fail. goodsNo[' . $item['goodsNo'] . '],  deliveryName[' . $item['deliveryName'] . '], invoiceNo[' . $item['invoiceNo'] . ']', $item);
                                continue;
                            }
                            $groupKey = $item['invoiceNo'];      // 송장번호 배열 키 생성
                            if(key_exists($groupKey, $group)){
                                $group[$groupKey]['orderGoodsCount']++;     // 송장번호별 대표상품 외 발송되는 상품 수
                                $group[$groupKey]['groupInGoodsNo'][] = $item['goodsNo'];
                            } else {
                                $item['orderGoodsCount'] = 0;
                                $item['groupInGoodsNo'] = [$item['goodsNo']];
                                $group[$groupKey] = $item;
                            }
                        }
                        foreach ($group as $index => $item) {
                            if ($item['orderGoodsCount'] === 0) {
                                $replaceCode['goodsNm'] = sprintf('%s', $item['goodsNm']);
                            } else {
                                $replaceCode['goodsNm'] = sprintf('%s 외 %s 건', $item['goodsNm'], $item['orderGoodsCount']);
                            }
                            $replaceCode['deliveryName'] = $item['deliveryName'];
                            $replaceCode['invoiceNo'] = $item['invoiceNo'];
                            $replaceCode['invoiceCompanySno'] = $item['invoiceCompanySno'];
                            $replaceCode['goodsNo'] = $item['goodsNo'];
                            $replaceCode['groupInGoodsNo'] = $item['groupInGoodsNo'];
                            $replaceCode['orderStatus'] = $item['orderStatus'];
                            // 카카오알림톡용
                            $aBasicInfo = gd_policy('basic.info');
                            $replaceCode['rc_mallNm'] = Globals::get('gMall.mallNm');
                            $replaceCode['shopUrl'] = $aBasicInfo['mallDomain'];

                            gd_isset($item['orderGoodsCount'], 0);
                            if (($item['orderGoodsCount'] + 1) == $replaceCode['orderGoodsCnt']) {
                                $replaceCode['is_part'] = '0';
                            } else {
                                $replaceCode['is_part'] = '1';
                            }

                            $arrReplaceCode[] = $replaceCode;
                        }

                        if ($smsDeliveryConfigBySendType == 'n') {
                            $sendFl['sms_' . $sendType] = 'y';  //  상품배송 안내의 부분배송 시 배송 건별 SMS 발송 옵션이 해제된 경우 해당 주문의 SMS 발송 여부가 발송으로 처리됨.
                            $firstKey = ArrayUtils::firstKey($arrReplaceCode);
                            $firstValue = ArrayUtils::first($arrReplaceCode);
                            $arrReplaceCode = [$firstKey => $firstValue];
                            $logger->info('Disabled smsDelivery option. replace code', $arrReplaceCode);
                        }

                        return $arrReplaceCode;
                    };

                    /**
                     * 문자 발송 전 발송가능한 문자인지 다시 체크하는 함수
                     *
                     * @param array $params
                     *
                     * @return bool
                     */
                    $possibleSendSms = function (array $params) {
                        $db = \App::getInstance('DB');
                        $db->strField = 'sno, invoiceCompanySno, invoiceNo, orderStatus, sendSmsFl';
                        $db->strWhere = 'invoiceNo = ? AND orderNo = ? AND JSON_EXTRACT(sendSmsFl, "$.INVOICE_CODE.sendFl") = \'y\'';
                        $db->bind_param_push($arrBind, 's', $params['invoiceNo']);
                        $db->bind_param_push($arrBind, 's', $params['orderNo']);
                        $query = $db->query_complete();
                        $strSQL = 'SELECT ' . array_shift($query) . ' FROM ' . DB_ORDER_GOODS . implode(' ' , $query);
                        $resultSet = $db->query_fetch($strSQL, $arrBind);

                        return empty($resultSet);
                    };
                    // 송장번호별로 묶어서 발송
                    foreach ($groupByInvoiceNo($orderData, $orderInfo) as $index => $replaceArguments) {
                        if (empty($replaceArguments)) {
                            $logger->info(sprintf('Skip sms. this order[%s] do not have invoiceNo or deliveryName', $orderData['orderNo']));
                            continue;
                        }
                        $logger->info('invoice send orderInfo(replaceCode)', $replaceArguments);
                        if ($possibleSendSms(['invoiceNo' => $replaceArguments['invoiceNo'], 'orderNo' => $replaceArguments['orderNo']])) {
                            $sendSms($orderData['mallSno'], $sendType, $receiverInfo, $replaceArguments, $orderData);
                        } else {
                            $logger->info(sprintf('Skip sms. this orderGoods is sended. %s', $replaceArguments['goodsNo']));
                        }
                        foreach ($replaceArguments['groupInGoodsNo'] as $goodsIndex => $goodsNo) {
                            $this->saveSendResultByOrderGoods([
                                'sendSmsFl' => [Code::INVOICE_CODE => ['sendFl' => 'y',]],
                                'goodsNo' => $goodsNo,
                                'orderNo' => $orderData['orderNo'],
                            ]);
                        }
                    }
                } // 전송 타입별 - 입금요청
                elseif ($sendType == Code::ACCOUNT) {
                    if ($orderData['settleKind'] == 'gb') {
                        $accountData = gd_isset($orderData['bankAccount'][0]) . '/' . gd_isset($orderData['bankAccount'][1]) . '/' . gd_isset($orderData['bankAccount'][2]);
                    } else {
                        if ($orderData['settleMethod'] == 'v') {
                            $accountData = gd_isset($orderData['pgSettleNm'][0]) . '/' . gd_isset($orderData['pgSettleNm'][1]) . '/' . gd_isset($orderData['pgSettleNm'][2]);
                        } else {
                            return;
                        }
                    }
                    $orderInfo['account'] = $accountData;
                    $orderInfo['bankAccount'] = $accountData;
                    $sendSms($orderData['mallSno'], $sendType, $receiverInfo, $orderInfo, $orderData);
                } else if ($sendType == 'DELIVERY' || $sendType == 'DELIVERY_COMPLETED') {
                    foreach ($orderData['goods'] as $goodsNo) {
                        $this->saveSendResultByOrderGoods([
                            'sendSmsFl' => [$sendType => ['sendFl' => 'y',]],
                            'goodsNo' => $goodsNo['goodsNo'],
                            'orderNo' => $orderData['orderNo'],
                        ]);
                    }
                    if ($sendType == 'DELIVERY_COMPLETED') {
                        $delivery = \App::load('Component\\Delivery\\Delivery');
                        $aDelivery = $delivery->getDeliveryCompany($orderData['goods']['invoiceCompanySno'][0]);
                        $orderInfo['invoiceNo'] = $orderData['goods']['invoiceNo'][0];
                        $orderInfo['deliveryName'] = $aDelivery[0]['companyName'];
                    }
                    $sendSms($orderData['mallSno'], $sendType, $receiverInfo, $orderInfo, $orderData);
                } else {
                    $accountData = '';
                    if ($orderData['settleKind'] == 'gb') {
                        $accountData = gd_isset($orderData['bankAccount'][0]) . '/' . gd_isset($orderData['bankAccount'][1]) . '/' . gd_isset($orderData['bankAccount'][2]);
                    } else {
                        if ($orderData['settleMethod'] == 'v') {
                            $accountData = gd_isset($orderData['pgSettleNm'][0]) . '/' . gd_isset($orderData['pgSettleNm'][1]) . '/' . gd_isset($orderData['pgSettleNm'][2]);
                        }
                    }
                    $orderInfo['bankAccount'] = $accountData;

                    $sendSms($orderData['mallSno'], $sendType, $receiverInfo, $orderInfo, $orderData);
                }
            } catch (\Throwable $e) {
                // 예외 발생시 주문로직이 정지해버리기때문에 로그 기록만 하도록 처리
                $logger->error($e->getTraceAsString());
            }
        }
        unset($receiverInfo, $orderInfo);
        // 전송 여부 저장
        $this->saveOrderSendInfoResult($orderNo, $sendFl);
    }

    protected function saveSendResultByOrderGoods($params)  {
        $logger = \App::getInstance('logger');
        $logger->info(__METHOD__, $params);
        $db = \App::getInstance('DB');
        $arrBind = [];
        $strSQL = 'SELECT sendSmsFl FROM '.DB_ORDER_GOODS .' WHERE sno = ? AND orderNo = ?';
        $db->bind_param_push($arrBind, 's', $params['goodsNo']);
        $db->bind_param_push($arrBind, 's', $params['orderNo']);
        $_sendSmsFl = $db->query_fetch($strSQL, $arrBind, false);
        if($_sendSmsFl['sendSmsFl']) {
            $sendSmsFl = gd_isset(json_decode($_sendSmsFl['sendSmsFl'], true), []);
            $params['sendSmsFl'] = array_merge($params['sendSmsFl'], $sendSmsFl);
        }
        unset($arrBind);

        $arrBind = $db->get_binding(DBTableField::tableOrderGoods(), $params, 'update', ['sendSmsFl']);
        $db->bind_param_push($arrBind['bind'], 's', $params['goodsNo']);
        $db->bind_param_push($arrBind['bind'], 's', $params['orderNo']);
        $db->set_update_db(DB_ORDER_GOODS, $arrBind['param'], 'sno = ? AND orderNo = ?', $arrBind['bind']);
    }

    protected function sendOrderInfoByMail($sendType, $orderNo)
    {
        $logger = \App::getInstance('logger');
        // 주문 정보
        $orderData = $this->getOrderDataSend($orderNo);
        if ($orderData['multiShippingFl'] == 'y') {
            $tmpOrderInfoData = $this->getMultiOrderInfo($orderNo);
        }

        // 주문 데이터가 없는 경우
        if (empty($orderData) === true) {
            $logger->info(sprintf('Return empty orderData by order number %s', $orderNo));
            return;
        }
        $sendFl = $this->getSendMailSmsFl($orderData['sendMailSmsFl']);

        // 전송 여부
        $sendMailFl = true;

        // 이미 보내진 상태라면
        if (gd_isset($sendFl['mail_' . $sendType], 'y') == 'y') {
            $logger->warning(sprintf('This order mail was sent. sendFl[%s]', $sendFl['mail_' . $sendType]));
            $sendMailFl = false;
        } else {
            $sendFl['mail_' . $sendType] = 'y';
        }

        // 메일 전송 설정
        $receiverInfo = [
            'email' => gd_isset($orderData['orderEmail']),
            'name'  => gd_isset($orderData['orderName']),
        ];

        // 메일 전송 데이타 설정
        $isSendTypeDelivery = $sendType == 'DELIVERY';
        $isSendTypeInCash = $sendType == 'INCASH';
        $isSendTypeOrder = $sendType == 'ORDER';
        if ($isSendTypeOrder || $isSendTypeInCash || $isSendTypeDelivery) {
            // 주문 상품 정보
            $orderGoods = $this->getOrderGoodsData($orderNo, null, null, null, 'user', true, false, null, null, true, \Component\Order\OrderMultiShipping::isUseMultiShipping());
            if($isSendTypeDelivery || $isSendTypeOrder){
                $logger->info('Set goods delivery or order mail');
                $orderData['goods'] = [];
                foreach ($orderGoods as $provider) {
                    foreach ($provider as $index => $item) {
                        $orderData['goods'][] = $item;
                    }
                }
            } else {
                $logger->info('Set goods other type');
                $orderGoodsKey = key($orderGoods);
                $orderData['goods'] = $orderGoods;
                if (isset($orderData['goods'][$orderGoodsKey])) {
                    $orderData['goods'] = $orderGoods[$orderGoodsKey];
                }
            }

            // 배송회사 정보 설정
            if ($isSendTypeDelivery) {
                $logger->info('Set delivery company info.');
                /** @var \Bundle\Component\Delivery\Delivery $delivery */
                $delivery = \App::load('\\Component\\Delivery\\Delivery');
                $deliveryCompanyList = $delivery->getDeliveryCompany();
                $arrSno = ArrayUtils::getSubArrayByKey($deliveryCompanyList, 'sno');
                $arrCompanyName = ArrayUtils::getSubArrayByKey($deliveryCompanyList, 'companyName');
                $arrTraceUrl = ArrayUtils::getSubArrayByKey($deliveryCompanyList, 'traceUrl');
                $arrDelivery = array_combine($arrSno, $arrCompanyName);
                $arrDeliveryTrace = array_combine($arrSno, $arrTraceUrl);

                $mailPolicy = ComponentUtils::getPolicy('mail.configAuto', DEFAULT_MALL_NUMBER);
                $isSendTypeN = gd_isset($mailPolicy['order']['delivery']['sendType'], 'y') == 'n'; // 주문번호 기준 1회만 발송 설정 시

                foreach ($orderData['goods'] as $index => &$good) {
                    if ($isSendTypeN && $good['orderStatus'] == 'd1' && $sendFl['mail_' . $sendType . '_' . $good['sno']] == 'y') {
                        $sendMailFl = false; // 주문번호 기준 1회만 발송 설정 시 상품 하나라도 메일발송 이력이 있으면 메일발송 안함.
                        break;
                    }
                    if ($good['deliveryMethodFl'] == 'visit') {
                        unset($orderData['goods'][$index], $good);
                        continue;
                    }
                    $invoiceCompanySno = $good['invoiceCompanySno'];
                    if (empty($invoiceCompanySno) || $invoiceCompanySno < 1) {
                        continue;
                    }
                    $good['invoiceCompanyName'] = $arrDelivery[$invoiceCompanySno];

                    if ($invoiceCompanySno === '7') { // 대신택배
                        $good['invoiceNo'] = str_replace('-', '', $good['invoiceNo']);
                        $dsInvoiceNo[0] = substr($good['invoiceNo'], 0, 4);
                        $dsInvoiceNo[1] = substr($good['invoiceNo'], 4, 3);
                        $dsInvoiceNo[2] = substr($good['invoiceNo'], 7, 6);
                        $good['invoiceLink'] = $arrDeliveryTrace[$invoiceCompanySno];
                        foreach ($dsInvoiceNo as $key => $value) {
                            $good['invoiceLink'] = str_replace('__INVOICENO' . $key . '__', $value, $good['invoiceLink']);
                        }
                    } else {
                        $good['invoiceLink'] = str_replace('__INVOICENO__', $good['invoiceNo'], $arrDeliveryTrace[$invoiceCompanySno]);
                    }
                }
            }

            // 마일리지 정보
            // $mileage = gd_mileage_give_info();

            // 주문 사은품 정보
            $orderData['gift'] = $this->getOrderGift($orderNo, null, 40);

            $orderInfo = [
                'orderData' => $orderData,
                // 'mileage'   => $mileage['info'],
            ];
        } else {
            $orderInfo = ['orderData' => $orderData];
        }

        // 메일 전송
        if ($sendMailFl === true) {
            $this->mailMimeAuto = App::load('\\Component\\Mail\\MailMimeAuto');
            $logger->info('Send order mail.');
            try {
                if ($isSendTypeOrder) {
                    $session = \App::getInstance('session');
                    if ($orderData['mallSno'] > DEFAULT_MALL_NUMBER && !$session->has(SESSION_GLOBAL_MALL)) {
                        $mallHelper = MallHelper::getInstance();
                        $currencyConfig = $mallHelper->getConnectionGlobalCurrencyConfig($mallHelper->getMall($orderData['mallSno']));
                        $session->set(SESSION_GLOBAL_MALL, ['currencyConfig' => $currencyConfig]);
                    }
                    $policy = new Policy();
                    $settleKind = $policy->getDefaultSettleKind();
                    $mailData = [
                        'email'                 => $orderData['orderEmail'],
                        'orderDt'               => $orderData['regDt'],
                        'orderNm'               => $orderData['orderName'],
                        'orderNo'               => $orderData['orderNo'],
                        'settlePrice'           => $orderData['settlePrice'],
                        'settleKind'            => $settleKind[$orderData['settleKind']]['name'],
                        'goods'                 => $orderData['goods'],
                        'gift'                  => $orderData['gift'],
                        'totalGoodsPrice'       => $orderData['totalGoodsPrice'],
                        'totalDeliveryCharge'   => $orderData['totalDeliveryCharge'],
                        'totalSumMemberDcPrice' => ($orderData['totalGoodsDcPrice'] + $orderData['totalMemberDcPrice'] + $orderData['totalMemberOverlapDcPrice'] + $orderData['totalCouponGoodsDcPrice'] + $orderData['totalCouponOrderDcPrice'] + $orderData['totalCouponDeliveryDcPrice'] + $orderData['totalMemberDeliveryDcPrice']),
                        'useMileage'            => $orderData['useMileage'],
                        'useDeposit'            => $orderData['useDeposit'],
                        'receiverNm'            => $orderData['receiverName'],
                        'receiverZipcode'       => $orderData['receiverZipcode'],
                        'receiverZonecode'      => $orderData['receiverZonecode'],
                        'receiverAddress'       => $orderData['receiverAddress'],
                        'receiverAddressSub'    => $orderData['receiverAddressSub'],
                        'receiverPhone'         => $orderData['receiverPhone'],
                        'receiverCellPhone'     => $orderData['receiverCellPhone'],
                        'receiverMemo'          => $orderData['orderMemo'],
                        'receiverPhonePrefixCode'       => $orderData['receiverPhonePrefixCode'],
                        'receiverCellPhonePrefixCode'   => $orderData['receiverCellPhonePrefixCode'],
                        'receiverPhonePrefix'           => $orderData['receiverPhonePrefix'],
                        'receiverCellPhonePrefix'       => $orderData['receiverCellPhonePrefix'],
                        'receiverCountryCode'           => $orderData['receiverCountryCode'],
                        'receiverCountry'               => $orderData['receiverCountry'],
                        'receiverState'                 => $orderData['receiverState'],
                        'receiverCity'                  => $orderData['receiverCity'],
                        'totalDeliveryInsuranceFee'     => $orderData['totalDeliveryInsuranceFee'],
                    ];

                    // 마이앱 사용에 따른 분기 처리
                    if ($this->useMyapp) {
                        $mailData['totalSumMemberDcPrice'] += $orderData['totalMyappDcPrice'];
                    }

                    if ($orderData['multiShippingFl'] == 'y' && empty($tmpOrderInfoData) === false) {
                        $mailData['receiverNmAdd'] = $tmpOrderInfoData['receiverNm'];
                        $mailData['receiverZonecodeAdd'] = $tmpOrderInfoData['receiverZonecode'];
                        $mailData['receiverAddressAdd'] = $tmpOrderInfoData['receiverAddress'];
                        $mailData['receiverAddressSubAdd'] = $tmpOrderInfoData['receiverAddressSub'];
                        $mailData['receiverPhoneAdd'] = $tmpOrderInfoData['receiverPhone'];
                        $mailData['receiverCellPhoneAdd'] = $tmpOrderInfoData['receiverCellPhone'];
                        $mailData['orderMemoAdd'] = $tmpOrderInfoData['orderMemo'];
                    }
                    $logger->debug('detail mail data', $mailData);
                    if ($this->mailMimeAuto->isUseObserver()) {
                        $observer = new MailAutoObserver();
                        $observer->setType(MailMimeAuto::ORDER_DETAIL);
                        $observer->setReplaceInfo($mailData);
                        $observer->setMallSno($orderData['mallSno']);
                        $observer->setValidateFunction(function() use($orderData) {
                            return $this->existOrder($orderData['orderNo']);
                        });
                        $this->mailMimeAuto->attach($observer);
                    } else {
                        $this->mailMimeAuto->init(MailMimeAuto::ORDER_DETAIL, $mailData, $orderData['mallSno'])->autoSend();
                    }
                } else if ($isSendTypeInCash) {
                    // settlePrice 변경이 필요할지도... 상품 1개에 대한 입금확인 이면 해당 상품 가격*수량의 금액이 들어가야함....
                    $mailData = [
                        'email'          => $orderData['orderEmail'],
                        'orderNm'        => $orderData['orderName'],
                        'orderNo'        => $orderData['orderNo'],
                        'orderDt'        => $orderData['regDt'],
                        'settlePrice'    => $orderData['settlePrice'],
                        'paymentCheckDt' => $orderData['paymentDt'],
                        'bankSender'     => $orderData['bankSender'],
                        'accountHolder'  => $orderData['bankAccount'][2],
                        'accountNumber'  => $orderData['bankAccount'][1],
                        'bank'           => $orderData['bankAccount'][0],
                    ];
                    $logger->debug('incash mail data', $mailData);
                    if ($this->mailMimeAuto->isUseObserver()) {
                        $observer = new MailAutoObserver();
                        $observer->setType(MailMimeAuto::ORDER_INCASH);
                        $observer->setReplaceInfo($mailData);
                        $observer->setMallSno($orderData['mallSno']);
                        $observer->setValidateFunction(function() use($orderData) {
                            return $this->existOrder($orderData['orderNo']);
                        });
                        $this->mailMimeAuto->attach($observer);
                    } else {
                        $this->mailMimeAuto->init(MailMimeAuto::ORDER_INCASH, $mailData, $orderData['mallSno'])->autoSend();
                    }
                } else if ($isSendTypeDelivery) {
                    // 2016-10-06 yjwee 상품별 배송상태에 따라 전송되도록 변경함.
                    $deliveryGoods = $orderData['goods'];
                    $deliveryGoodsNo = $tmpOrderInfoData = [];
                    $mainDeliveryInfoUse = false;
                    foreach ($deliveryGoods as $orderGoodKey => $orderGood) {
                        $deliveryGoodsNo[] = $orderGood['sno'];
                        if ($orderGood['orderStatus'] == 'd1' && $sendFl['mail_' . $sendType . '_' . $orderGood['sno']] != 'y') {
                            $sendFl['mail_' . $sendType . '_' . $orderGood['sno']] = 'y';    // 상품별 배송메일 발송 여부
                            if ($orderGood['orderInfoCd'] > 1) {
                                $tmpOrderInfoData['receiverNm'][$orderGood['orderInfoCd'] - 1] = $orderGood['receiverName'];
                                $tmpOrderInfoData['receiverZonecode'][$orderGood['orderInfoCd'] - 1] = $orderGood['receiverZipcode'];
                                $tmpOrderInfoData['receiverAddress'][$orderGood['orderInfoCd'] - 1] = $orderGood['receiverAddress'];
                                $tmpOrderInfoData['receiverAddressSub'][$orderGood['orderInfoCd'] - 1] = $orderGood['receiverAddressSub'];
                                $tmpOrderInfoData['receiverPhone'][$orderGood['orderInfoCd'] - 1] = $orderGood['receiverPhone'];
                                $tmpOrderInfoData['receiverCellPhone'][$orderGood['orderInfoCd'] - 1] = $orderGood['receiverCellPhone'];
                                $tmpOrderInfoData['orderMemo'][$orderGood['orderInfoCd'] - 1] = $orderGood['orderMemo'];
                            } else {
                                $mainDeliveryInfoUse = true;
                            }
                        } else {
                            $sendFl['mail_' . $sendType] = 'n';
                            unset($deliveryGoods[$orderGoodKey]);
                        }
                    }
                    $isSendFl = true;
                    foreach ($deliveryGoodsNo as $item) {
                        if ($sendFl['mail_' . $sendType . '_' . $item] != 'y') {
                            $isSendFl = false;
                        }
                    }
                    if ($isSendFl) {
                        $sendFl['mail_' . $sendType] = 'y';  // 해당 주문의 전체 상품이 배송메일 발송 완료 여부
                    }
                    $mailData = [
                        'email'              => $orderData['orderEmail'],
                        'orderNm'            => $orderData['orderName'],
                        'orderNo'            => $orderData['orderNo'],
                        'goods'              => $deliveryGoods,
                        'orderDt'            => $orderData['regDt'],
                        'receiverPhonePrefixCode'       => $orderData['receiverPhonePrefixCode'],
                        'receiverCellPhonePrefixCode'   => $orderData['receiverCellPhonePrefixCode'],
                        'receiverPhonePrefix'           => $orderData['receiverPhonePrefix'],
                        'receiverCellPhonePrefix'       => $orderData['receiverCellPhonePrefix'],
                        'receiverCountryCode'           => $orderData['receiverCountryCode'],
                        'receiverCountry'               => $orderData['receiverCountry'],
                        'receiverState'                 => $orderData['receiverState'],
                        'receiverCity'                  => $orderData['receiverCity'],
                    ];
                    if ($orderData['multiShippingFl'] == 'y') {
                        if ($mainDeliveryInfoUse === true) {
                            $mailData['receiverNm'] = $orderData['receiverName'];
                            $mailData['receiverZipcode'] = $orderData['receiverZipcode'];
                            $mailData['receiverZonecode'] = $orderData['receiverZonecode'];
                            $mailData['receiverAddress'] = $orderData['receiverAddress'];
                            $mailData['receiverAddressSub'] = $orderData['receiverAddressSub'];
                            $mailData['receiverPhone'] = $orderData['receiverPhone'];
                            $mailData['receiverCellPhone'] = $orderData['receiverCellPhone'];
                            $mailData['receiverMemo'] = $orderData['orderMemo'];
                        }
                        if (empty($tmpOrderInfoData) === false) {
                            $mailData['receiverNmAdd'] = $tmpOrderInfoData['receiverNm'];
                            $mailData['receiverZonecodeAdd'] = $tmpOrderInfoData['receiverZonecode'];
                            $mailData['receiverAddressAdd'] = $tmpOrderInfoData['receiverAddress'];
                            $mailData['receiverAddressSubAdd'] = $tmpOrderInfoData['receiverAddressSub'];
                            $mailData['receiverPhoneAdd'] = $tmpOrderInfoData['receiverPhone'];
                            $mailData['receiverCellPhoneAdd'] = $tmpOrderInfoData['receiverCellPhone'];
                            $mailData['orderMemoAdd'] = $tmpOrderInfoData['orderMemo'];
                        }
                    } else {
                        $mailData['receiverNm'] = $orderData['receiverName'];
                        $mailData['receiverZipcode'] = $orderData['receiverZipcode'];
                        $mailData['receiverZonecode'] = $orderData['receiverZonecode'];
                        $mailData['receiverAddress'] = $orderData['receiverAddress'];
                        $mailData['receiverAddressSub'] = $orderData['receiverAddressSub'];
                        $mailData['receiverPhone'] = $orderData['receiverPhone'];
                        $mailData['receiverCellPhone'] = $orderData['receiverCellPhone'];
                        $mailData['receiverMemo'] = $orderData['orderMemo'];
                    }
                    $logger->debug('delivery mail data', $mailData);
                    if ($this->mailMimeAuto->isUseObserver()) {
                        $observer = new MailAutoObserver();
                        $observer->setType(MailMimeAuto::GOODS_DELIVERY);
                        $observer->setReplaceInfo($mailData);
                        $observer->setMallSno($orderData['mallSno']);
                        $observer->setValidateFunction(function() use($orderData) {
                            return $this->existOrder($orderData['orderNo']);
                        });
                        $this->mailMimeAuto->attach($observer);
                    } else {
                        $this->mailMimeAuto->init(MailMimeAuto::GOODS_DELIVERY, $mailData, $orderData['mallSno'])->autoSend();
                    }
                }
            } catch (\Throwable $e) {
                // 예외 발생시 주문로직이 정지해버리기때문에 로그 기록만 하도록 처리
                $logger->error($e->getTraceAsString());
            }
        }
        unset($receiverInfo, $orderInfo, $mailData);
        // 전송 여부 저장
        $this->saveOrderSendInfoResult($orderNo, $sendFl);
    }

    /**
     * 주문이 존재하는지 확인
     *
     * @param $orderNo
     *
     * @return array ['result' => true|false, 'message' => 'result message']
     */
    protected function existOrder($orderNo)
    {
        $db = \App::getInstance('DB');
        $db->strField = 'COUNT(*) AS cnt';
        $db->strWhere = 'orderNo=?';
        $db->bind_param_push($bind, 's', $orderNo);
        $query = $this->db->query_complete();
        $strSQL = 'SELECT ' . array_shift($query) . ' FROM ' . DB_ORDER  . implode(' ', $query);
        $resultSet = $db->query_fetch($strSQL, $bind, false);
        StringUtils::strIsSet($resultSet['cnt'], 0);
        $existOrder = $resultSet['cnt'] > 0;

        return ['result' => $existOrder, 'message' => $existOrder ? sprintf('exist order[%d]', $orderNo) : sprintf('not exist order[%d]', $orderNo)];
    }

    /**
     * 지정된 주문상태가 로그에서 한번이라도 기록되어 있는지 체크하는 메소드
     * 본 함수는 반드시 setStatusChange 이전에 실행되어져야 합니다.
     *
     * @param integer $orderNo       주문번호
     * @param integer $orderGoodsSno 주문상품번호
     * @param string  $orderStatus   주문상태
     *
     * @return mixed
     * @author Jong-tae Ahn <qnibus@godo.co.kr>
     */
    public function isSetOrderStatus($orderNo, $orderGoodsSno, $orderStatus = 's')
    {
        // 반환데이터 초기화
        $settleCount = 0;

        // 패턴 정의 및 로그 데이터 가져오기
        $pattern = '/([opgdsebrfcz]{1}[0-9]{1})/i';
        $getData = $this->getOrderGoodsLog($orderNo, $orderGoodsSno);

        // 지정된 상태가 적용됬는지 여부 체크
        $orderStatusLength = strlen($orderStatus);
        if ($orderStatusLength <= 2) {
            foreach ($getData as $key => $val) {
                if (preg_match_all($pattern, $val['logCode02'], $matches) !== false) {
                    if (substr($matches[0][0], 0, $orderStatusLength) === $orderStatus) {
                        $settleCount++;
                    }
                }
            }
        }

        return ($settleCount > 0 ? $settleCount : false);
    }

    /*
     * 주문상품상태 변경 로그중 확정상태가 있는지 체크
     * 확정상태 [s, z5]
     *
     * @param string $orderNo       주문번호
     * @param integer $orderGoodsSno 주문상품번호
     *
     * @return boolean $returnBool
     *
     * @author bumyul2000@godo.co.kr
     */
    public function isSetConfirmOrderGoodsStatus($orderNo, $orderGoodsSno)
    {
        // 반환데이터 초기화
        $returnBool = false;

        // 확정상태 검사 [s-구매확정, z5-교환추가완료]
        $confirmPattern = '/([s]{1}[0-9]{1}|[z5]{2})/i';
        $getData = $this->getOrderGoodsLog($orderNo, $orderGoodsSno);

        if(count($getData) > 0){
            foreach ($getData as $key => $val) {
                if (preg_match($confirmPattern, $val['logCode02'])) {
                    $returnBool = true;
                    break;
                }
            }
        }

        return $returnBool;
    }

    /**
     * 회원 구매금액 갱신
     * 한번이라도 구매확정이 된 이후에는 처리되지 않아야 한다.
     *
     * @param string $orderNo       주문번호
     * @param array  $orderGoodsSno 주문상품번호 배열
     */
    public function setOrderPriceMember($orderNo, $orderGoodsSno)
    {
        // 주문상품번호 배열 처리
        if (!is_array($orderGoodsSno)) {
            $orderGoodsSno = [$orderGoodsSno];
        }

        // 배송비 포함여부를 지정
        // true - 포함(첫번째카운팅에 배송비포함), false - 미포함(포함하면 안됨)
        $orderDeliveryFlList = [];

        // 주문데이터 로드
        $strSQL = 'SELECT memNo FROM ' . DB_ORDER . ' WHERE orderNo = ?';
        $arrBind = [
            's',
            $orderNo,
        ];
        $orderData = $this->db->query_fetch($strSQL, $arrBind, false);
        unset($strSQL, $arrBind);

        // 주문상품데이터 로드
        $arrBind = $orderGoodsData = [];
        $this->db->bind_param_push($arrBind, 's', $orderNo);
        $this->db->strField = "sno, orderDeliverySno";
        $this->db->strWhere = 'orderNo = ?';

        $query = $this->db->query_complete();
        $strSQL = 'SELECT ' . array_shift($query) . ' FROM ' . DB_ORDER_GOODS . ' ' . implode(' ', $query);
        $tmpOrderGoodsData = $this->db->query_fetch($strSQL, $arrBind, true);
        unset($arrBind, $query, $strSQL);

        // 배송비 포함여부를 미리 처리
        if(count($tmpOrderGoodsData) > 0){
            foreach($tmpOrderGoodsData as $key => $value){
                $orderGoodsData[$value['sno']] = $value;

                if($orderDeliveryFlList[$value['orderDeliverySno']] !== false){
                    $orderDeliveryFlList[$value['orderDeliverySno']] = true;
                    if ($this->isSetConfirmOrderGoodsStatus($orderNo, $value['sno']) === true) {
                        // 확정단계의 상태가 있다면 배송비를 포함하지 않는다.(이미 배송비가 포함되어 회원구매금액에 적용되어 있는 상태)
                        $orderDeliveryFlList[$value['orderDeliverySno']] = false;
                    }
                }
            }
            unset($tmpOrderGoodsData);
        }

        // 회원 번호가 존재하면 업데이트 진행
        if (!empty($orderData) && $orderData['memNo'] > 0) {
            $deliveryCountArr = [];

            // 주문상품번호별로 회원구매금액 및 수량 설정
            foreach ($orderGoodsSno as $sno) {
                // 해당 주문상품의 상태변경이력중 확정단계의 상태가 존재하지 않느다면 구매금액, 구매건수를 업데이트한다.
                if ($this->isSetConfirmOrderGoodsStatus($orderNo, $sno) !== true) {
                    $realSettlePrice = [];
                    $realSaleAmt = $realSaleCnt = 0;

                    $orderDeliverySno = $orderGoodsData[$sno]['orderDeliverySno'];

                    // 환불을 제외한 실 복합과세 금액 (배송비가 제외된 실제 결제금액 - 에서 배송비를 포함하는것으로 변경됨.)
                    $realSettlePrice = $this->getOrderRealComplexTax($orderNo, $sno);
                    $realSaleAmt = $realSettlePrice['taxSupply'] + $realSettlePrice['taxVat'] + $realSettlePrice['taxFree'];
                    $realSaleCnt = $realSettlePrice['orderGoodsCnt'];

                    if($orderDeliveryFlList[$orderDeliverySno] === true){
                        // 주문상품별 배송비포함 (첫번째 카운팅에 배송비 포함)
                        if((int)$deliveryCountArr[$orderDeliverySno] < 1){
                            $realSaleAmt += $realSettlePrice['deliveryCharge'];
                        }

                        (int)$deliveryCountArr[$orderDeliverySno]++;
                    }

                    // 회원 테이블 갱신
                    $this->db->set_update_db_query(DB_MEMBER, 'saleAmt = saleAmt + ' . $realSaleAmt . ', saleCnt = saleCnt + ' . $realSaleCnt, 'memNo = \'' . $this->db->escape($orderData['memNo']) . '\'');
                }
            }

        }
    }

    /**
     * 결제 방법 출력
     *
     * @author artherot
     *
     * @param string $settleKind 결제방법 코드
     *
     * @return array  결제방법
     */
    public function getSettleKind($settleKind = null)
    {
        // 정책에서 결제수단 가져오기
        $getData = gd_policy('order.settleKind', 1);

        // 전액할인 추가
        $getData[self::SETTLE_KIND_ZERO] = [
            'name'  => __('전액할인'),
            'mode'  => 'general',
            'useFl' => 'y',
        ];

        // 예치금 추가 (리스트 검색테이블)
        $getDeposit = gd_policy('member.depositConfig');
        $getData[self::SETTLE_KIND_DEPOSIT] = [
            'name'  => $getDeposit['name'],
            'mode'  => 'general',
            'useFl' => $getDeposit['payUsableFl'],
        ];

        // 마일리지 추가 (리스트 검색테이블)
        $getMileage = Globals::get('gSite.member.mileageBasic');
        $getData[self::SETTLE_KIND_MILEAGE] = [
            'name'  => $getMileage['name'],
            'mode'  => 'general',
            'useFl' => 'y',
        ];

        // 기타 추가
        $getData[self::SETTLE_KIND_REST] = [
            'name'  => __('기타'),
            'mode'  => 'general',
            'useFl' => 'y',
        ];

        // 결제수단에 추가된 모바일PG 설정 화면에 출력되지 않도록 unset 처리
        unset($getData['mobilePgConfFl']);

        // 순서에 따른 정렬
        $this->sortSettleKind($getData);

        if (is_null($settleKind)) {
            return $getData;
        } else {
            foreach ($getData as $key => $val) {
                if ($key == $settleKind) {
                    return $getData[$key]['name'];
                }
            }

            return false;
        }
    }

    /**
     * 결제 방법 정렬
     *
     * @author artherot
     * @param array $getData 결제정보
     */
    protected function sortSettleKind(&$getData)
    {
        $sort['gb'] = 1; // 무통장입금
        $sort['gz'] = 2; // 전액결제
        $sort['gm'] = 3; // 마일리지
        $sort['gd'] = 4; // 예치금
        $sort['pc'] = 5; // 카드결제
        $sort['pb'] = 6; // 계좌이체
        $sort['pv'] = 7; // 가상계좌
        $sort['ph'] = 8; // 핸드폰결제
        $sort['ec'] = 9; // 계좌이체
        $sort['eb'] = 10; // 계좌이체
        $sort['ev'] = 11; // 가상계좌
        $sort['fa'] = 12; // 무통장입금
        $sort['fc'] = 13; // 계좌이체
        $sort['fb'] = 14; // 계좌이체
        $sort['fv'] = 15; // 가상계좌
        $sort['fh'] = 16; // 핸드폰결제
        $sort['pk'] = 17; // 카카오페이
        $sort['fp'] = 18; // 포인트결제
        $sort['op'] = 19; // PAYPAL
        $sort['ov'] = 20; // VISA / MASTER
        $sort['oj'] = 21; // JCB / AMEX
        $sort['oa'] = 22; // ALIPAY
        $sort['ot'] = 23; // TENPAY
        $sort['ou'] = 24; // UNIONPAY
        $sort['gr'] = 25; // 기타

        // 정렬 번호 추가
        foreach ($getData as $key => $val) {
            $getData[$key]['sort'] = $sort[$key];
        }

        // sort 에 의한 정렬
        ArrayUtils::subKeySort($getData, 'sort', true);

        // sort 삭제
        foreach ($getData as $key => $val) {
            if (isset($getData[$key]['sort'])) {
                unset($getData[$key]['sort']);
            }
        }
    }

    /**
     * PG별 디바이스별 사용 가능한 결제 방법 출력
     *
     * @author artherot
     *
     * @param array $pgConf PG 정보
     *
     * @return array 결제방법
     */
    protected function getDeviceSettleKind($pgConf)
    {
        // 결제 방법
        $getData = $this->getSettleKind();

        // PG를 사용하는 경우
        if (empty($pgConf['pgName']) === false) {
            // 디바이스 체크
            if (Request::isMobileDevice() === true) {
                $checkDevice = 'mobile';
            } else {
                $checkDevice = 'front';
            }

            // 각 PG사별 디바이스별 설정값
            $pgCode = App::getConfig('payment.pg')->getPgDeviceSettleFl()[$pgConf['pgName']][$checkDevice];

            // 체크
            foreach ($pgCode as $key => $val) {
                if ($val === 'n') {
                    $getData[$key]['useFl'] = 'n';
                }
            }
        }

        return $getData;
    }

    /**
     * 사용하는 결제 방법 출력
     * !주의! 전액할인|마일리지|예치금이 제외된 수단만 표기하니 모든 결제수단 표기를 원할 경우 self::getSettleKind() 사용 할 것.
     *
     * @author artherot
     *
     * @param string $settleGb 이용결제 수단 - 회원 등급에서 사용
     * @param array  $pgConf   PG 정보
     * @param string $limitGb  결제 제한 수단 결제 수단
     *
     * @return array 결제방법
     */
    public function useSettleKind($settleGb = null, $pgConf = null, $limitGb = null)
    {
        if (empty($settleGb)) {
            $settleGb = 'all';
        }

        // PG사에 따른 설정
        if (is_null($pgConf) === true) {
            $pgConf = gd_pgs();
        }

        // 결제 방법 출력
        $getData = $this->getDeviceSettleKind($pgConf);

        // 전액할인, 마일리지결제, 예치금결제, 기타는 화면에 표시하지 않는다.
        unset(
            $getData[self::SETTLE_KIND_ZERO],
            $getData[self::SETTLE_KIND_MILEAGE],
            $getData[self::SETTLE_KIND_DEPOSIT],
            $getData[self::SETTLE_KIND_REST]
        );

        // 해외PG의 decimal과 format 설정을 위해 사용
        $exchangeRate = new ExchangeRate();

        // 휴대폰 결제 사용 여부
        $mobilePgConfFl = false;
        foreach ($getData as $key => $val) {
            if (gd_isset($limitGb) && is_array($limitGb)) {
                if (($val['mode'] == 'general' && substr($key, 0, 1) == 'p') || $val['mode'] == 'escrow' || $val['mode'] == 'overseas') {
                    $payCheck = "pg";
                } else {
                    $payCheck = $key;
                }
                if (in_array($payCheck, $limitGb) == false) {
                    continue;
                }
            }

            // 휴대폰 결제 체크
            if ($key == 'ph') {
                // 휴대폰 결제 설정
                $mobilePgConf = gd_mpgs();
                if ($mobilePgConf && $mobilePgConf['pgId']) {
                    if ($mobilePgConf['useFl'] == 't') {
                        $val['useFl'] = 'n';
                        if (gd_is_admin()) {
                            $val['useFl'] = 'y';
                            $mobilePgConfFl = true;
                        }
                    } else if ($mobilePgConf['useFl'] == 'n') {
                        if ($val['useFl'] == 'n') {
                            continue;
                        }
                    } else {
                        $val['useFl'] = $mobilePgConf['useFl'];
                        $mobilePgConfFl = true;
                    }
                } else {
                    if ($val['useFl'] == 'n') {
                        continue;
                    }
                }
            } else {
                // 사용여부에 따라
                if ($val['useFl'] == 'n') {
                    continue;
                }
            }

            // 에스크로 사용여부에 따라
            if ($val['mode'] == 'escrow' && ($pgConf['escrowFl'] == 'n' || empty($pgConf['escrowId']) === true)) {
                continue;
            }

            // 일반 결제 사용여부에 따라
            if ($val['mode'] == 'general' && substr($key, 0, 1) == 'p' && (empty($pgConf['pgId']) || empty($pgConf['pgName']))) {
                if ($key == 'ph' && $mobilePgConfFl === true) {
                    // 휴대폰 결제 이고, 휴대폰 결제설정이 되어 있는 경우 패스
                } elseif ($key == 'pk' && $val['useFl'] != 'n') {
                    // 카카오페이이고 사용설정이면 패스
                } else {
                    continue;
                }
            }

            // 회원 등급별 이용 결제 수단에 따라
            if ($settleGb != 'all') {
                if ($settleGb == 'nobank' && substr($key, 0, 1) == 'g') {
                    continue;
                }
                if ($settleGb == 'bank' && substr($key, 0, 1) != 'g') {
                    continue;
                }
            }

            // 해외PG의 경우 승인금액 계산을 위한 기준 통화단위를 설정해 내보낸다.
            if ($val['mode'] === 'overseas') {
                // 해외PG 설정 가져오기
                $overseasPg = gd_opgs();
                $overseasPgComponent = App::load('\\Component\\Payment\\PG');
                $overseasPgList = $overseasPgComponent->setDefaultPgDataOverseasList();

                // 해외PG 리스트가 2가지로 분리되어 있어 하나로 만들기 위한 기존 키 리스트 설정
                $allOverseasPgList = $overseasPgComponent->setPgNameOverseas();

                // 해당 결제수단으로 설정된 해외PG 리스트의 값(value)과 매칭시켜 값을 찾고 통화코드 추가
                foreach ($allOverseasPgList as $pgVal) {
                    if (($selectedPg = array_search($val['name'], $overseasPgList[$pgVal])) !== false) {
                        // mallSno
                        $mallSno = gd_isset(Mall::getSession('sno'), 1);
                        $val['mallFl'] = $overseasPg[$pgVal][$selectedPg]['mallFl'][$mallSno];
                        $val['pgCurrency'] = $overseasPg[$pgVal][$selectedPg]['pgCurrency'];
                        $val['pgSymbol'] = $overseasPg[$pgVal][$selectedPg]['pgSymbol'];

                        $currencyData = reset($exchangeRate->getGlobalCurrency($val['pgCurrency'], 'isoCode'));
                        if (!empty($currencyData)) {
                            $val['pgDecimal'] = $currencyData['globalCurrencyDecimal'];
                            $val['pgDecimalFormat'] = $currencyData['globalCurrencyDecimalFormat'];
                        }
                        break;
                    }
                }

                // 사용상점에 따라
                if ($val['mallFl'] !== 'y') {
                    continue;
                }
            }

            $tmp[$val['mode']][$key] = $val;
        }

        // 카카오페이 설정에 따라 노출여부 처리
        foreach ($tmp['general'] as $k => $v) {
            if ($k == 'pk') {
                // 해외면 미노출
                if (Globals::get('gGlobal.isFront')) {
                    unset($tmp['general'][$k]);
                    unset($tmp['general']);
                    break;
                }
                $pgKakao = gd_policy('pg.kakaopay');
                // es_config의 pg.kakaopay값이 없으면..
                if (empty($pgKakao)) {
                    unset($tmp['general'][$k]);
                    break;
                }

                $saveManagerId = gd_is_admin();
                if ($pgKakao['testYn'] == 'Y' && empty($saveManagerId)) {
                    unset($tmp['general'][$k]);
                    break;
                }
                if ($pgKakao['useYn'] == 'mobile' && empty(Request::isMobile())) {
                    unset($tmp['general'][$k]);
                    break;
                }
                if ($pgKakao['useYn'] == 'pc' && Request::isMobile()) {
                    unset($tmp['general'][$k]);
                    break;
                }
            }
        }

        // PAYCO
        $payco = \App::load('\\Component\\Payment\\Payco\\Payco');
        $paycoTmp =  $payco->paycoSettleKind();
        if (empty($paycoTmp) === false) {
            $tmp = array_merge(gd_isset($tmp), $paycoTmp);
        }

        // 에스크로 서비스가 있는 구매안전 표시
        if (empty($tmp['escrow']) === false && $pgConf['eggDisplayFl'] != 'n') {
            $tmp['egg']['pgName'] = Globals::get('gPg.' . $pgConf['pgName']);
        }

        return $tmp;
    }

    /**
     * 결제 방법 출력
     *
     * @param string $settleKind 결제방법 코드
     *
     * @return array 결제방법
     */
    public function printSettleKind($settleKind)
    {
        if ($this->isSettleKind === false) {
            $this->settleKind = $this->getSettleKind();
            $this->isSettleKind = true;
        }

        foreach ($this->settleKind as $key => $val) {
            if ($key == $settleKind) {
                return $this->settleKind[$key]['name'];
            }
        }
    }

    /**
     * 기간별 주문상태별 이름 및 주문수량이 0개 이상인 경우에 대한 요약정보를 산출한다.
     * 마이페이지 주문상태별 요약정보 위젯에서 사용하며 주문상품 리스트 중 제일 첫번째 상품의 주문상태를 기준으로 체크한다.
     *
     * @author   Jong-tae Ahn <qnibus@godo.co.kr>
     *
     * @param null $memNo
     * @param int  $day
     * @param int  $scmNo
     * @param bool $isStatusMode 주문상태 코드를 한글자로 체크해서 묶는 경우
     *
     * @return Array 주문상태별 데이터
     * @internal param 개월 $month
     * @internal param int $month 개월 (30일)
     *
     */
    public function getEachOrderStatus($memNo = null, $scmNo = null, $day = 30)
    {
        // 배열 선언
        $arrBind = $arrWhere = [];

        // 접속 사이트
        $rootDirectory = App::getInstance('ControllerNameResolver')->getControllerRootDirectory();

        // 주문상태 정보
        $status = $this->_getOrderStatus(null, $rootDirectory == 'admin' ? 'admin' : 'user');
        foreach ($status as $code => $str) {
            // 데이터 초기화
            $getData[$code]['name'] = $str;
            $getData[$code]['count'] = 0;
            $getData[$code]['active'] = '';

            // 상태에 따른 필드 구문 작성
            $arrField[] = 'SUM(IF(og.orderStatus="' . $code . '", 1, 0)) AS ' . $code;
        }

        // 회원상태
        if ($memNo !== null) {
            $arrWhere[] = 'o.memNo=?';
            $this->db->bind_param_push($arrBind, 'i', $memNo);
        }

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

        if ($day > 0) {
            // 기간산출 (한달 이내로 쿼리 조작)
            $arrWhere[] = 'og.regDt BETWEEN ? AND ?';
            $this->db->bind_param_push($arrBind, 's', date('Y-m-d', strtotime('-' . $day . ' days')) . ' 00:00:00');
            $this->db->bind_param_push($arrBind, 's', date('Y-m-d') . ' 23:59:59');
        } else {
            $arrWhere[] = 'og.regDt BETWEEN ? AND ?';
            $this->db->bind_param_push($arrBind, 's', date('Y-m-d') . ' 00:00:00');
            $this->db->bind_param_push($arrBind, 's', date('Y-m-d') . ' 23:59:59');
        }

        // 상태별 합계 산출
        $this->db->strJoin = ' LEFT JOIN ' . DB_ORDER . ' o ON o.orderNo = og.orderNo ';
        $this->db->strField = ' og.regDt, ' . implode(', ', $arrField);
        $this->db->strWhere = implode(' AND ', $arrWhere);
        $this->db->strGroup = ' og.orderNo ';
        $query = $this->db->query_complete();
        $strSQL = 'SELECT ' . array_shift($query) . ' FROM ' . DB_ORDER_GOODS . ' og ' . implode(' ', $query);
        $result = $this->db->query_fetch($strSQL, $arrBind, true);

        // 산출된 주문리스트로 상태별 합계 산출
        foreach ($result as $key => $val) {
            foreach ($status as $code => $str) {
                $getData[$code]['count'] += $result[$key][$code];
                if ($getData[$code]['count'] > 0) {
                    $getData[$code]['active'] = 'active';
                }

                // 취소/교환/반품 관련 건수를 위해 단계별 상태 세팅
                $prefix = substr($code, 0, 1);
                $getData[$prefix]['count'] += $result[$key][$code];
                if ($getData[$prefix]['count'] > 0) {
                    $getData[$prefix]['active'] = 'active';
                }
            }
        }

        return gd_htmlspecialchars_stripslashes($getData);
    }

    /**
     * 최초 주문여부 확인
     * 관리자에서 사용하면 안되며, 비회원은 최초 주문조건에서 제외된다.
     *
     * @author Jong-tae Ahn <qnibus@godo.co.kr>
     *
     * @param integer $memNo 회원번호
     *
     * @return bool 최초주문 여부
     */
    public function isFirstSale($memNo = 0)
    {
        if (empty($memNo) === false && $memNo > 0) {
            $arrField[] = ' o.firstSaleFl ';
            $this->db->strJoin = 'LEFT JOIN ' . DB_ORDER . ' o ON og.orderNo = o.orderNo';
            $this->db->strField = implode(', ', $arrField);
            $this->db->strWhere = ' o.memNo="' . $memNo . '" AND LEFT(og.orderStatus, 1) != \'f\'';
            $this->db->strOrder = ' o.firstSaleFl ASC ';
            $this->db->strLimit = ' 0, 1 ';
            $query = $this->db->query_complete();
            $strSQL = 'SELECT ' . array_shift($query) . ' FROM ' . DB_ORDER_GOODS . ' og ' . implode(' ', $query);
            $getData = $this->db->query_fetch($strSQL, null, true);
            $result = (in_array('y', array_column($getData, 'firstSaleFl')) === false);
        } else {
            $result = false;
        }

        return $result;
    }

    /**
     * 해당 주문로직이 다 돌아가고 난 뒤 DB에 저장되면
     * 첫 주문의 여부를 확인하고 업데이트 처리
     *
     * @param integer $orderNo 주문번호
     * @param integer $memNo   회원번호
     *
     * @author Jong-tae Ahn <qnibus@godo.co.kr>
     */
    public function updateFirstSale($orderNo)
    {
        if (empty($orderNo) === false) {
            $orderData = $this->getOrderData($orderNo, null, ['memNo']);
            if ($this->isFirstSale($orderData['memNo'])) {
                $order['firstSaleFl'] = 'y';
                $compareField = array_keys($order);
                $arrBind = $this->db->get_binding(DBTableField::tableOrder(), $order, 'update', $compareField);
                $this->db->bind_param_push($arrBind['bind'], 's', $orderNo);
                $this->db->set_update_db(DB_ORDER, $arrBind['param'], 'orderNo = ? AND firstSaleFl = \'n\'', $arrBind['bind']);
                unset($arrBind);
            }
        }
    }

    /**
     * 최초 주문시 쿠폰 발급 여부 확인
     * 관리자에서 사용하면 안되며, 비회원은 최초 주문조건에서 제외된다.
     *
     * @author Jong-tae Ahn <qnibus@godo.co.kr>
     *
     * @param integer $orderNo 주문번호
     *
     * @return bool 최초주문쿠폰 발급 여부
     */
    public function isFirstCoupon($orderNo)
    {
        $result = false;
        $arrField = [
            'o.firstSaleFl',
            'o.firstCouponFl',
        ];
        $this->db->strField = implode(', ', $arrField);
        $this->db->strWhere = ' o.orderNo="' . $orderNo . '"';
        $query = $this->db->query_complete();
        $strSQL = 'SELECT ' . array_shift($query) . ' FROM ' . DB_ORDER . ' o ' . implode(' ', $query);
        $getData = $this->db->query_fetch($strSQL, null, false);
        if ($getData['firstSaleFl'] == 'y' && $getData['firstCouponFl'] != 'y') {
            $result = true;
        }

        return $result;
    }

    /**
     * 요청사항/상담메모건 정보 출력
     *
     * @param integer $orderNo 주문번호
     *
     * @return array 해당 주문의 주문자/수취인 정보
     */
    public function getPayHistory($orderNo)
    {
        $arrField = DBTableField::setTableField('tableOrderPayHistory');
        $strSQL = 'SELECT regDt, ' . implode(', ', $arrField) . ' FROM ' . DB_ORDER_PAY_HISTORY . ' WHERE orderNo = ? ORDER BY sno ASC';
        $arrBind = [
            's',
            $orderNo,
        ];
        $getData = $this->db->query_fetch($strSQL, $arrBind);
        if (count($getData) > 0) {
            return gd_htmlspecialchars_stripslashes($getData);
        } else {
            return false;
        }
    }

    /**
     * setPayHistory
     *
     * @param $data DB에 담을 데이터 (orderNo|type|goodsPrice|deliveryCharge|dcPrice|addPrice|settlePrice)
     *
     * @author Jong-tae Ahn <qnibus@godo.co.kr>
     * @return boolean
     */
    public function setPayHistory($data)
    {
        if (!in_array(
            $data['type'], [
                'fs',
                'pc',
                'ac',
                'pr',
                'ar',
            ]
        )
        ) {
            return false;
        }

        $compareField = array_keys($data);
        $arrBind = $this->db->get_binding(DBTableField::tableOrderPayHistory(), $data, 'insert', $compareField);
        $this->db->set_insert_db(DB_ORDER_PAY_HISTORY, $arrBind['param'], $arrBind['bind'], 'y');
        unset($arrBind);
    }

    /**
     * 주문 log 처리
     *
     * @author artherot
     *
     * @param string  $orderNo   주문 번호
     * @param integer $goodsSno  주문 상품 번호
     * @param string  $logCode01 로그 코드 1
     * @param string  $logCode02 로그 코드 2
     * @param string  $logDesc   로그 내용
     * @param boolean $userOrder 사용자 모드 저장여
     */
    public function orderLog($orderNo, $goodsSno, $logCode01 = null, $logCode02 = null, $logDesc = null, $userOrder = false, $autoProcess = false)
    {
        $tableField = DBTableField::tableLogOrder();
        foreach ($tableField as $key => $val) {
            $arrData[$val['val']] = gd_isset(${$val['val']});
        }

        // IP 추가
        $arrData['managerIp'] = Request::getRemoteAddress();

        if($autoProcess == true){
            if(strpos($logCode02, 'r3)') > 0){
                $arrData['managerNo'] = 0;
                $arrData['managerId'] = 'System';
                $arrData['managerIp'] = '';
            }else if(strpos($logCode02, 'r1)') > 0){
                $arrData['managerNo'] = 0;
                $arrData['managerId'] = gd_isset(Session::get('member.memId'), '');
            }else{
                $arrData['managerNo'] = 0;
                $arrData['managerId'] = '';
            }
        }else if ($userOrder === false) {
            if (Session::has('manager.managerId')) {
                $arrData['managerNo'] = Session::get('manager.managerNo');
                $arrData['managerId'] = Session::get('manager.managerId');
            } else {
                $arrData['managerNo'] = '';
                $arrData['managerId'] = '';
            }

            if($this->logManagerNo){
                $manager = new Manager();
                $memberData = $manager->getManagerInfo($this->logManagerNo);
                $arrData['managerNo'] = $memberData['sno'];
                $arrData['managerId'] = $memberData['managerId'];
            }

        } else {
            $arrData['managerId'] = '';
        }

        if($this->channel == 'naverpay') {
            if(!$this->logManagerNo){   //네이버페이 센터 혹은 네이버페이 구매자에서 처리된거면
                $data = $this->getOrderGoodsData($orderNo,$goodsSno,null,null,null,false);
                if($data['naverpayStatus']['requestChannel'] &&  ($data['orderStatus'] == 'r1' || $data['orderStatus'] == 'r3' || $data['orderStatus'] == 'e1' || $data['orderStatus'] == 'b1')){ //클레임 접수일때만 처리자 기록

                    $arrData['managerId']  = mb_substr($data['naverpayStatus']['requestChannel'],0,3)  == '판매자' ? '네이버페이센터' : '구매자' ;
                }
                else if(!$arrData['managerNo']){
                    $arrData['managerId'] = '네이버페이센터';
                }
            }
        }

        $arrBind = $this->db->get_binding(DBTableField::tableLogOrder(), $arrData, 'insert');
        $this->db->set_insert_db(DB_LOG_ORDER, $arrBind['param'], $arrBind['bind'], 'y');
        unset($arrBind);
    }

    /**
     * 재고 log 처리
     *
     * @author artherot
     *
     * @param integer $goodsNo        상품 번호
     * @param integer $orderNo        주문 번호
     * @param string  $optionValue    옵션값
     * @param integer $beforeStock    전재고
     * @param integer $afterStock     후재고
     * @param integer $variationStock 변화량
     * @param string  $logDesc        로그 내용
     */
    public function stockLog($goodsNo, $orderNo = null, $optionValue = null, $beforeStock, $afterStock, $variationStock, $logDesc = null)
    {
        $tableField = DBTableField::tableLogStock();
        foreach ($tableField as $key => $val) {
            $arrData[$val['val']] = gd_isset(${$val['val']});
        }

        if (Session::has('manager.managerId')) {
            $arrData['managerId'] = Session::get('manager.managerId');
            $arrData['managerNo'] = Session::get('manager.sno');
        } else {
            $arrData['managerId'] = '';
        }

        // IP 추가
        $arrData['managerIp'] = Request::getRemoteAddress();

        $arrBind = $this->db->get_binding(DBTableField::tableLogStock(), $arrData, 'insert');
        $this->db->set_insert_db(DB_LOG_STOCK, $arrBind['param'], $arrBind['bind'], 'y');
        unset($arrBind);
    }

    /**
     * 주문번호 기준의 상품들을 토대로 지역별 배송비 산출하기
     *
     * @param $orderNo
     */
    public function getDeliverAreaData($orderNo)
    {
        // 주문 정보
        $getData = $this->getOrderView($orderNo);


        // 상품별 배송정책 종류에 따른 배송정책 기본 정보 (배송비 부과방법 y - 배송비조건별 , n - 상품별)
        $delivery = \App::load('\\Component\\Delivery\\DeliveryCart');

        foreach ($getData['goods'] as $val) {
            $getDeliveryInfo = $delivery->getDataDeliveryWithGoodsNo($val['orderDeliverySno']);
        }
    }

    /**
     * 배송지 관리 리스트 및 페이지 가져오기
     *
     * @param integer $page 페이지번호
     *
     * @return mixed
     */
    public function getShippingAddressList($page = 1, $pageNum = 5, $memNo='')
    {
        // --- 페이지 기본설정
        $page = \App::load('\\Component\\Page\\Page', $page, 0, 0, 5, 5);
        $page->page['list'] = $pageNum; // 페이지당 리스트 수
        $page->setPage();

        if(trim($memNo) === ''){
            $memNo = Session::get('member.memNo');
        }

        $arrWhere[] = 'memNo="' . $memNo . '"';

        if (Globals::get('gGlobal.isFront')) {
            $arrWhere[] = 'mallSno=' . \Component\Mall\Mall::getSession('sno');
        }

        $tmpField = DBTableField::setTableField('tableOrderShippingAddress');
        $strSQL = 'SELECT sno, ' . implode(',', $tmpField) . ' FROM ' . DB_ORDER_SHIPPING_ADDRESS . ' WHERE ' . implode(' AND ', $arrWhere) . ' ORDER BY defaultFl ASC, shippingTitle ASC, shippingAddress ASC  LIMIT ' . $page->recode['start'] . ', ' . $pageNum;
        $getData = $this->db->query_fetch($strSQL);

        // 검색 레코드 수
        list($page->recode['total']) = $this->db->fetch('SELECT count(*) FROM ' . DB_ORDER_SHIPPING_ADDRESS . ' WHERE memNo="' . $memNo . '"', 'row');
        $page->setPage();

        return gd_htmlspecialchars_stripslashes($getData);
    }

    /**
     * 배송지 관리 데이터 가져오기
     *
     * @param integer $sno 배송지관리번호
     *
     * @return mixed
     * @throws Exception
     */
    public function getShippingAddressData($sno)
    {
        if (!MemberUtil::isLogin()) {
            throw new Exception(__('로그인을 하셔야 사용가능합니다.'));
        }

        $tmpField = DBTableField::setTableField('tableOrderShippingAddress');

        $arrBind = [];
        $strSQL = 'SELECT sno, ' . implode(',', $tmpField) . ' FROM ' . DB_ORDER_SHIPPING_ADDRESS . ' WHERE memNo = ? AND sno = ? ';
        $this->db->bind_param_push($arrBind, 'i', Session::get('member.memNo'));
        $this->db->bind_param_push($arrBind, 'i', $sno);
        $getData = $this->db->query_fetch($strSQL, $arrBind, false);
        unset($arrBind);

        if (empty($this->getShippingDefaultFlYn()) === true) {
            $getData['defaultFl'] = 'y';
            $getData['defaultFlDisabled'] = true;
        }

        return gd_htmlspecialchars_stripslashes($getData);
    }

    /**
     * 기본 배송지 데이터 반환
     *
     * @return mixed
     */
    public function getDefaultShippingAddress()
    {
        $tmpField = DBTableField::setTableField('tableOrderShippingAddress');
        $strSQL = 'SELECT sno, ' . implode(',', $tmpField) . ' FROM ' . DB_ORDER_SHIPPING_ADDRESS . ' WHERE defaultFl="y" AND memNo=? ORDER BY sno DESC LIMIT 0, 1';
        $getData = $this->db->query_fetch(
            $strSQL, [
            'i',
            Session::get('member.memNo'),
        ], false
        );

        return gd_htmlspecialchars_stripslashes($getData);
    }

    /**
     * 최근 배송지 데이터 반환
     * 마지막 주문건의 배송정보를 DB_ORDER_SHIPPING_ADDRESS 테이블의 형식에 맞게 반환
     *
     * @return mixed
     */
    public function getRecentShippingAddress()
    {
        $tmpField = [
            'oi.receiverName AS shippingName',
            'oi.receiverPhone AS shippingPhone',
            'oi.receiverCellPhone AS shippingCellPhone',
            'oi.receiverZipcode AS shippingZipcode',
            'oi.receiverZonecode AS shippingZonecode',
            'oi.receiverAddress AS shippingAddress',
            'oi.receiverAddressSub AS shippingAddressSub',
        ];
        // 해외용 필드 추가
        $tmpField = array_merge($tmpField, [
            'oi.receiverPhonePrefixCode AS shippingPhonePrefixCode',
            'oi.receiverPhonePrefix AS shippingPhonePrefix',
            'oi.receiverCellPhonePrefixCode AS shippingCellPhonePrefixCode',
            'oi.receiverCellPhonePrefix AS shippingCellPhonePrefix',
            'oi.receiverCountryCode AS shippingCountryCode',
            'oi.receiverCountry AS shippingCountry',
            'oi.receiverCity AS shippingCity',
            'oi.receiverState AS shippingState',
        ]);

        $strSQL = 'SELECT ' . implode(',', $tmpField) . ' FROM ' . DB_ORDER_INFO . ' AS oi LEFT JOIN ' . DB_ORDER . ' AS o ON o.orderNo = oi.orderNo AND oi.orderInfoCd = 1 WHERE o.memNo=? ORDER BY o.regDt DESC LIMIT 0, 1';
        $getData = $this->db->query_fetch(
            $strSQL, [
            'i',
            Session::get('member.memNo'),
        ], false
        );

        return gd_htmlspecialchars_stripslashes($getData);
    }

    /**
     * 주문서작성에서 배송지관리 등록 및 수정을 실행한다.
     *
     * @param array $request 리퀘스트 데이터
     *
     * @return bool
     * @throws Exception
     */
    public function registShippingAddress($request)
    {
        if (!MemberUtil::isLogin()) {
            throw new Exception(__('로그인을 하셔야 사용가능합니다.'));
        }

        // 데이터 이스케이프 처리
        $request = gd_htmlspecialchars_stripslashes($request);

        // diabled 된 경우 값이 넘어오지 않아서 별도 처리
        if ($request['tmpDefaultFl'] === 'y') {
            $request['defaultFl'] = 'y';
        }

        $data = [];
        $tableField = DBTableField::tableOrderShippingAddress();
        foreach ($tableField as $key => $val) {
            $data[$val['val']] = gd_isset($request[$val['val']]);
        }

        $data['mallSno'] = gd_isset(Mall::getSession('sno'), 1);
        $data['defaultFl'] = gd_isset($data['defaultFl'], 'n');
        $data['memNo'] = Session::get('member.memNo');
        $data['shippingCountry'] = $this->getCountryName($data['shippingCountryCode']);
        $data['shippingPhonePrefix'] = $this->getCountryCallPrefix($data['shippingPhonePrefixCode']);
        $data['shippingPhone'] = StringUtils::numberToPhone(str_replace('-', '', $data['shippingPhone']), true);
        $data['shippingCellPhonePrefix'] = $this->getCountryCallPrefix($data['shippingCellPhonePrefixCode']);
        $data['shippingCellPhone'] = StringUtils::numberToPhone(str_replace('-', '', $data['shippingCellPhone']), true);
        $data['shippingAddress'] = StringUtils::xssClean($data['shippingAddress']);
        $data['shippingAddressSub'] = StringUtils::xssClean($data['shippingAddressSub']);

        // 기본설정 강제 해지
        if ($data['defaultFl'] == 'y') {
            $updateField = ['defaultFl' => 'n'];
            $arrBind = $this->db->get_binding($tableField, $updateField, 'update', array_keys($updateField));
            $this->db->bind_param_push($arrBind['bind'], 's', 'y');
            $this->db->bind_param_push($arrBind['bind'], 'i', Session::get('member.memNo'));
            $this->db->set_update_db(DB_ORDER_SHIPPING_ADDRESS, $arrBind['param'], 'defaultFl=? AND memNo=?', $arrBind['bind']);
            unset($arrBind);
        }

        if (gd_isset($request['sno'])) {
            // 배송지 관리 등록
            $arrBind = $this->db->get_binding(
                $tableField, $data, 'update', array_keys($data), [
                    'sno',
                    'memNo',
                ]
            );
            $this->db->bind_param_push($arrBind['bind'], 'i', $request['sno']);
            $this->db->bind_param_push($arrBind['bind'], 'i', Session::get('member.memNo'));
            $this->db->set_update_db(DB_ORDER_SHIPPING_ADDRESS, $arrBind['param'], 'sno=? AND memNo=?', $arrBind['bind']);
            unset($arrBind);

            return true;
        } else {
            // 중복 등록되어 있는지 확인
            $arrField = DBTableField::setTableField(
                'tableOrderShippingAddress', null, [
                    'sno',
                    'defaultFl',
                    'shippingName',
                ]
            );
            foreach ($arrField as $val) {
                if (gd_isset($request[$val])) {
                    $arrWhere[] = $val . ' = ?';
                    $this->db->bind_param_push($arrBind, 's', $val);
                }
            }

            $strSQL = 'SELECT sno FROM ' . DB_ORDER_SHIPPING_ADDRESS . ' WHERE memNo = ' . Session::get('member.memNo') . ' AND ' . implode(' AND ', $arrWhere) . ' ORDER BY sno DESC';
            $getData = $this->db->query_fetch($strSQL, $arrBind, false);
            $getData = gd_htmlspecialchars_stripslashes($getData);
            if (count($getData) > 0) {
                return false;
            }

            // 배송지 관리 수정
            $arrBind = $this->db->get_binding($tableField, $data, 'insert');
            $this->db->set_insert_db(DB_ORDER_SHIPPING_ADDRESS, $arrBind['param'], $arrBind['bind'], 'y');
            unset($arrBind);

            return true;
        }

        return false;
    }

    /**
     * 배송주소록에서 특정 SNO를 기준으로 삭제처리 한다.
     *
     * @param integer $sno
     *
     * @throws Exception
     */
    public function deleteShippingAddress($sno)
    {
        if (!MemberUtil::isLogin()) {
            throw new Exception(__('로그인을 하셔야 사용가능합니다.'));
        }

        $this->db->bind_param_push($arrBind, 'i', $sno);
        $this->db->bind_param_push($arrBind, 'i', Session::get('member.memNo'));
        $this->db->set_delete_db(DB_ORDER_SHIPPING_ADDRESS, 'sno = ? AND memNo = ?', $arrBind);
        unset($arrBind);
    }

    // 기본배송지 저장
    public function defaultShippingAddress($sno)
    {
        if (!MemberUtil::isLogin()) {
            throw new Exception(__('로그인을 하셔야 사용가능합니다.'));
        }

        $tableField = DBTableField::tableOrderShippingAddress();
        $arrBind = $this->db->get_binding($tableField, ['defaultFl' => 'n'], 'update', ['defaultFl']);
        $this->db->bind_param_push($arrBind['bind'], 's', 'y');
        $this->db->bind_param_push($arrBind['bind'], 'i', Session::get('member.memNo'));
        $this->db->set_update_db(DB_ORDER_SHIPPING_ADDRESS, $arrBind['param'], 'defaultFl=? AND memNo=?', $arrBind['bind']);
        unset($arrBind);

        $arrBind = $this->db->get_binding($tableField, ['defaultFl' => 'y'], 'update', ['defaultFl']);
        $this->db->bind_param_push($arrBind['bind'], 'i', $sno);
        $this->db->bind_param_push($arrBind['bind'], 'i', Session::get('member.memNo'));
        $this->db->set_update_db(DB_ORDER_SHIPPING_ADDRESS, $arrBind['param'], 'sno=? AND memNo=?', $arrBind['bind']);
        unset($arrBind);
    }

    // 기본배송지 유무
    public function getShippingDefaultFlYn()
    {
        list($cnt) = $this->db->fetch('SELECT count(*) FROM ' . DB_ORDER_SHIPPING_ADDRESS . ' WHERE memNo="' . Session::get('member.memNo') . '" AND defaultFl="y"', 'row');

        return $cnt;
    }

    /**
     * 특정주문의 과세/비과세/부가세 금액계산
     *
     * @deprecated
     *
     * @param $orderNo
     *
     * @return array
     */
    public function getOrderTaxPrice($orderNo)
    {
        $arrayOrderGoodsData = $this->getOrderGoods($orderNo);

        $taxableGoods = 0;
        $taxfreeGoods = 0;

        foreach ($arrayOrderGoodsData as $orderGoodsData) {
            //상품 과세/비과세 구분
            $goodsTaxInfo = explode(STR_DIVISION, $orderGoodsData['goodsTaxInfo']);

            //기본상품금액 = (판매가 + 옵션가 + 텍스트옵션가) * 수량
            $goodsPrice = ($orderGoodsData['goodsPrice'] + $orderGoodsData['optionPrice'] + $orderGoodsData['optionTextPrice']) * $orderGoodsData['goodsCnt'];

            //총 상품금액 = 기본상품금액 + 총 추가상품금액
            $totalGoodsPrice = $goodsPrice + $orderGoodsData['addGoodsPrice'];

            //상품할인금액
            $totalGoodsDiscount = $orderGoodsData['goodsDcPrice'] + $orderGoodsData['memberDcPrice'] + $orderGoodsData['memberOverlapDcPrice'] + $orderGoodsData['couponGoodsDcPrice'];

            if ($this->useMyapp) { // 마이앱 사용에 따른 분기 처리
                $totalGoodsDiscount += $orderGoodsData['myappDcPrice'];
            }

            //상품할인금액에 대한 기본상품의 비율 계산
            $goodsDiscountRate = 0;
            if ($totalGoodsDiscount > 0) {
                $goodsDiscountRate = $this->getPriceRate($totalGoodsPrice, $goodsPrice, $totalGoodsDiscount);
            }

            if ($goodsTaxInfo[0] == 't') {
                $taxableGoods += $goodsPrice - $goodsDiscountRate;
            } else {
                $taxfreeGoods += $goodsPrice - $goodsDiscountRate;
            }

            //추가상품 과세/비과세 계산
            $arrayAddGoodsData = $this->getOrderAddGoods($orderNo, $orderGoodsData['orderCd']);

            //상품할인금액에 대한 추가상품의 비율 계산
            $addGoodsDiscountRate = 0;
            if ($totalGoodsDiscount > 0) {
                $addGoodsDiscountRate = $totalGoodsDiscount - $goodsDiscountRate;
            }

            $checkAddGoodsDiscount = 0;
            for ($i = 0; $i < count($arrayAddGoodsData); $i++) {
                $addGoodsData = $arrayAddGoodsData[$i];
                if ((int) $addGoodsData['goodsPrice'] == 0) {
                    continue;
                }
                if ($i == (count($arrayAddGoodsData) - 1)) {
                    $addGoodsDiscount = $addGoodsDiscountRate - $checkAddGoodsDiscount;
                } else {
                    $addGoodsDiscount = $this->getPriceRate($orderGoodsData['addGoodsPrice'], $addGoodsData['goodsPrice'], $addGoodsDiscountRate);
                }
                $checkAddGoodsDiscount += $addGoodsDiscount;

                $addGoodsTaxInfo = explode(STR_DIVISION, $addGoodsData['goodsTaxInfo']);
                $addGoodsPrice = $addGoodsData['goodsPrice'] * $addGoodsData['goodsCnt'];
                if ($addGoodsTaxInfo[0] == 't') {
                    $taxableGoods += $addGoodsPrice - $addGoodsDiscount;
                } else {
                    $taxfreeGoods += $addGoodsPrice - $addGoodsDiscount;
                }
            }
        }

        //배송비 제외 상품금액
        $goodsPrice = $taxableGoods + $taxfreeGoods;
        $orderData = $this->getOrderData($orderNo);

        //주문할인금액 = 쿠폰할인 + 사용예치금 + 사용마일리지
        $totalOrderDiscountPrice = $orderData['totalCouponOrderDcPrice'] + $orderData['useDeposit'] + $orderData['useMileage'];
        $orderTaxfree = 0;
        $orderTaxable = 0;
        if ($totalOrderDiscountPrice > 0) {
            //과세상품에 대한 주문할인 비율계산
            $orderTaxable = $this->getPriceRate($goodsPrice, $taxableGoods, $totalOrderDiscountPrice);

            //면세상품에 대한 주문할인 비율계산
            $orderTaxfree = $goodsPrice - $orderTaxable;//$this->getPriceRate($goodsPrice, $taxfreeGoods, $totalOrderDiscountPrice);
        }

        //주문할인을 적용한 과세/면세금액 계산
        $returnData['orderTaxable'] = round(($taxableGoods - $orderTaxable) / (1 + (10 / 100)));
        $returnData['orderTaxfree'] = $taxfreeGoods - $orderTaxfree;
        $returnData['orderVat'] = $taxableGoods - $orderTaxable - $returnData['orderTaxable'];

        // 결제배송비 = 총배송비 - 배송비쿠폰
        $settleDelivery = $orderData['totalDeliveryCharge'] - $orderData['totalCouponDeliveryDcPrice'];//배송쿠폰을 제외한 배송비(지역별배송비 포함)

        $returnData['deliveryTaxable'] = 0;
        $returnData['deliveryVat'] = 0;

        if ($settleDelivery > 0) {
            $tmpTax = round($settleDelivery / (1 + (10 / 100)));
            $returnData['deliveryTaxable'] = $tmpTax;
            $returnData['deliveryVat'] = $settleDelivery - $tmpTax;
        }

        return $returnData;
    }

    /**
     * 기준금액에 대한 비율금액 반환
     *
     * @param integer $standardTotal  전체금액
     * @param integer $strandardPrice 비율계산을 위한 금액
     * @param integer $targetPrice    비율을 구하고자 하는 금액
     *
     * @return float
     *
     */
    public function getPriceRate($standardTotal, $strandardPrice, $targetPrice)
    {
        $goodsDiscountRate = $strandardPrice / $standardTotal;

        return round($goodsDiscountRate * $targetPrice);
    }

    /**
     * 상품금액 + 배송비 복합과세 여부 체크
     * 상품금액과 배송비의 과세율이 10%가 아닌 부분을 검색해서 복합과세여부 결정
     *
     * @param $orderNo
     *
     * @return bool 복합과세 여부 (true 복합과세|false 일반과세)
     */
    public function isComplexTax($orderNo)
    {
        $isComplexTax = true;
        $goodsTax = $this->isComplexTaxForGoods($orderNo);
        $deliveryTax = $this->isComplexTaxForDelivery($orderNo);

        // 상품과 배송의 복합과세 여부 비교 후 처리
        if (!$goodsTax['isComplexTax'] && !$deliveryTax['isComplexTax']) {
            if ($goodsTax['taxFreeFl'] != '' && $deliveryTax['taxFreeFl'] != '' && $goodsTax['taxFlag'] == $deliveryTax['taxFlag']) {
                $isComplexTax = false;
            }
        }

        return $isComplexTax;
    }

    /**
     * 상품금액 복합과세 여부 체크
     * 상품금액 과세율이 10%가 아닌 부분을 검색해서 복합과세여부 결정
     *
     * @param $orderNo
     *
     * @return bool
     */
    public function isComplexTaxForGoods($orderNo)
    {
        $isComplexTax = false;
        $goods = $this->getOrderGoods(
            $orderNo, null, null, [
                'orderCd',
                'goodsTaxInfo',
            ]
        );
        foreach ($goods as $key => $val) {
            // 전부 과세 혹은 전부 면세가 아닌 경우 복합과세
            if (isset($taxInvoice) && $taxInvoice != substr($val['goodsTaxInfo'], 0, 1)) {
                $isComplexTax = true;
                break;
            }

            // 과세 10.0%가 아닌 경우가 포함되면 복합과세
            if (substr($val['goodsTaxInfo'], 0, 1) == 't' && substr($val['goodsTaxInfo'], 4) !== '10.0') {
                $isComplexTax = true;
                break;
            }

            // 추가상품 체크
            $addGoods = $this->getOrderAddGoods(
                $orderNo, $val['orderCd'], [
                    'addGoodsNo',
                    'goodsTaxInfo',
                ]
            );
            if (empty($addGoods) === false) {
                foreach ($addGoods as $aKey => $aVal) {
                    // 전부 과세 혹은 전부 면세가 아닌 경우 복합과세
                    if (isset($taxInvoice) && $taxInvoice != substr($aVal['goodsTaxInfo'], 0, 1)) {
                        $isComplexTax = true;
                        break;
                    }

                    // 과세 10.0%가 아닌 경우가 포함되면 복합과세
                    if (substr($aVal['goodsTaxInfo'], 0, 1) == 't' && substr($aVal['goodsTaxInfo'], 4) !== '10.0') {
                        $isComplexTax = true;
                        break;
                    }

                    $taxInvoice = substr($aVal['goodsTaxInfo'], 0, 1);
                }
            }

            $taxInvoice = substr($val['goodsTaxInfo'], 0, 1);
        }

        return [
            'taxFreeFl'    => (!$isComplexTax ? $taxInvoice : ''),
            'isComplexTax' => $isComplexTax,
        ];
    }

    /**
     * 배송비 복합과세 여부 체크
     * 배송비의 과세율이 10%가 아닌 부분을 검색해서 복합과세여부 결정
     *
     * @param $orderNo
     *
     * @return bool
     */
    public function isComplexTaxForDelivery($orderNo)
    {
        $isComplexTax = false;
        $delivery = $this->getOrderDelivery($orderNo, null, false, ['deliveryTaxInfo']);
        foreach ($delivery as $dKey => $dVal) {
            // 전부 과세 혹은 전부 면세가 아닌 경우 복합과세
            if (isset($taxInvoice) && $taxInvoice != substr($dVal['deliveryTaxInfo'], 0, 1)) {
                $isComplexTax = true;
                break;
            }

            // 과세 10.0%가 아닌 경우가 포함되면 복합과세
            if (substr($dVal['deliveryTaxInfo'], 0, 1) == 't' && substr($dVal['deliveryTaxInfo'], 4) !== '10.0') {
                $isComplexTax = true;
                break;
            }

            $taxInvoice = substr($dVal['deliveryTaxInfo'], 0, 1);
        }

        return [
            'taxFreeFl'    => (!$isComplexTax ? $taxInvoice : ''),
            'isComplexTax' => $isComplexTax,
        ];
    }

    /**
     * 주문상태 코드를 사용하는 주문이 있는지 확인
     *
     * @param string $orderStatus 주문상태
     *
     * @return bool
     * @author Jong-tae Ahn <qnibus@godo.co.kr>
     */
    public function checkUsableOrderStatus($orderStatus)
    {
        $strSQL = 'SELECT COUNT(orderNo) AS count FROM ' . DB_ORDER_GOODS . ' WHERE orderStatus = ?';
        $arrBind = [
            's',
            $orderStatus,
        ];
        $getData = $this->db->query_fetch($strSQL, $arrBind, false);

        return ($getData['count'] > 0);
    }

    /**
     * getOrderAddFieldCheckField
     *
     * @return mixed
     * @author su
     */
    public function getOrderAddFieldCheckField()
    {
        $this->arrWhere[] = "oaf.orderAddFieldDisplay = 'y'";

        $this->db->strField = "oaf.orderAddFieldNo, oaf.orderAddFieldRequired, oaf.orderAddFieldType, oaf.orderAddFieldOption";
        $this->db->strWhere = implode(' AND ', gd_isset($this->arrWhere));
        $this->db->strOrder = "oaf.orderAddFieldSort asc";

        $query = $this->db->query_complete();
        $strSQL = 'SELECT ' . array_shift($query) . ' FROM ' . DB_ORDER_ADD_FIELD . ' as oaf ' . implode(' ', $query);
        $data = $this->db->query_fetch($strSQL, $this->arrBind);
        unset($this->arrBind);

        $getData = gd_htmlspecialchars_stripslashes(gd_isset($data));

        return $getData;
    }

    /**
     * getOrderAddFieldUseList
     *
     * @param $cartInfo
     *
     * @return mixed
     * @author su
     */
    public function getOrderAddFieldUseList($cartInfo)
    {
        // 테이블명 반환
        $mall = new Mall();
        $tableName = $mall->getTableName(DB_ORDER_ADD_FIELD, \Component\Mall\Mall::getSession('sno'));

        $getData['addFieldConf'] = 'n';
        $addFieldConf = gd_policy('order.addField');
        if ($addFieldConf['orderAddFieldUseFl'] == 'y') {
            $this->arrWhere[] = "oaf.orderAddFieldDisplay = 'y'";
            if (\Component\Mall\Mall::getSession('sno') > DEFAULT_MALL_NUMBER) {
                $this->arrWhere[] = "oaf.mallSno = '" . \Component\Mall\Mall::getSession('sno') . "'";
            }

            $this->db->strField = "oaf.*";
            $this->db->strWhere = implode(' AND ', gd_isset($this->arrWhere));
            $this->db->strOrder = "oaf.orderAddFieldSort asc";

            $query = $this->db->query_complete();
            $strSQL = 'SELECT ' . array_shift($query) . ' FROM ' . $tableName . ' as oaf ' . implode(' ', $query);
            $data = $this->db->query_fetch($strSQL, $this->arrBind);
            unset($this->arrBind);

            $getData['data'] = gd_htmlspecialchars_stripslashes(gd_isset($data));
            foreach ($getData['data'] as $key => $val) {
                // 노출유형 별 설정
                $orderAddFieldOption = json_decode($val['orderAddFieldOption'], true);
                unset($getData['data'][$key]['orderAddFieldOption']);
                $getData['data'][$key]['orderAddFieldOption'] = $orderAddFieldOption;

                // 상품조건 설정
                $orderAddFieldApply = json_decode($val['orderAddFieldApply'], true);
                unset($getData['data'][$key]['orderAddFieldApply']);
                $getData['data'][$key]['orderAddFieldApply'] = $orderAddFieldApply;

                // 예외조건 설정
                $orderAddFieldExcept = json_decode($val['orderAddFieldExcept'], true);
                unset($getData['data'][$key]['orderAddFieldExcept']);
                $getData['data'][$key]['orderAddFieldExcept'] = $orderAddFieldExcept;
            }

            $getData['data'] = $this->getOrderAddFieldUseConvert($getData['data'], $cartInfo);

            if (count($getData['data']) > 0) {
                $getData['addFieldConf'] = $addFieldConf['orderAddFieldUseFl'];
            }
        }

        return $getData;
    }

    /**
     * 주문 상품 로그
     *
     * @param array $orderNo 주문 번호
     * @param array $goodsSno 주문 상품 sno
     *
     * @return array 해당 주문 상품 로그 정보
     */
    public function getOrderGoodsLog($orderNo, $goodsSno = null)
    {
        $this->db->bind_param_push($arrBind, 's', $orderNo);
        $arrWhere[] = 'lo.orderNo = ?';
        if ($goodsSno !== null) {
            $this->db->bind_param_push($arrBind, 'i', $goodsSno);
            $arrWhere[] = 'lo.goodsSno = ?';
        }

        if (Manager::isProvider()) {
            $this->db->bind_param_push($arrBind, 'i', Session::get('manager.scmNo'));
            $arrWhere[] = 'og.scmNo = ?';
        }

        // join 문
        $join[] = ' LEFT JOIN ' . DB_ORDER_GOODS . ' og ON lo.goodsSno = og.sno ';
        $join[] = ' LEFT JOIN ' . DB_MANAGER . ' m ON m.sno = lo.managerNo ';

        $arrField = DBTableField::setTableField('tableLogOrder', null, null, 'lo');
        //  $arrField[] = 'IF(lo.managerId<>\'\', (SELECT m.managerNm FROM ' . DB_MANAGER . ' m WHERE m.sno = lo.managerNo), NULL) AS managerNm';

        $this->db->strField = implode(', ', $arrField) . ', lo.regDt, og.orderCd, og.goodsNm,m.managerNm,m.isDelete';
        $this->db->strWhere = implode(' AND ', $arrWhere);
        $this->db->strJoin = implode('', $join);
        $this->db->strOrder = 'lo.sno DESC';

        $query = $this->db->query_complete();
        $strSQL = 'SELECT ' . array_shift($query) . ' FROM ' . DB_LOG_ORDER . ' lo ' . implode(' ', $query);
        $getData = $this->db->query_fetch($strSQL, $arrBind);
        Manager::displayListData($getData);
        // 데이타 출력
        if (count($getData) > 0) {
            return gd_htmlspecialchars_stripslashes($getData);
        } else {
            return false;
        }
    }

    /**
     * getCartAddFieldGoodsData
     *
     * @param $cartInfo
     *
     * @return array
     * @author su
     */
    public function getCartAddFieldGoodsData($cartInfo)
    {
        foreach ($cartInfo as $scmKey => $scmVal) {
            foreach ($scmVal as $deliveryKey => $deliveryVal) {
                foreach ($deliveryVal as $goodsKey => $goodsVal) {
                    $goodsData[] = $goodsVal;
                }
            }
        }

        return $goodsData;
    }

    /**
     * getOrderAddFieldUseConvert
     *
     * @param $orderAddFieldUseData
     * @param $cartInfo
     *
     * @return mixed
     * @author su
     */
    public function getOrderAddFieldUseConvert($orderAddFieldUseData, $cartInfo)
    {
        $cartGoods = $this->getCartAddFieldGoodsData($cartInfo);
        $i = 0;
        foreach ($orderAddFieldUseData as $useKey => $useVal) {
            if ($useVal['orderAddFieldRequired'] == 'y') {
                $requiredName = '<span style="color:#fa2828">('. __('필수') .')</span>';
            } else {
                $requiredName = '<span style="color:#117EF9">('. __('선택') .')</span>';
            }
            if ($useVal['orderAddFieldProcess'] == 'goods') {
                foreach ($cartGoods as $goodsKey => $goodsVal) {
                    $orderAddFieldUsable = $this->getOrderAddFieldUseApplyExcept($useVal, $goodsVal);
                    if ($orderAddFieldUsable) {
                        unset($optionName);
                        unset($optionTextName);
                        if (count($goodsVal['option']) > 0) {
                            foreach ($goodsVal['option'] as $optionKey => $optionVal) {
                                $optionName[] = $optionVal['optionName'] . ':' . $optionVal['optionValue'];
                            }
                            $goodsOptionName = ' - ' . implode(" / ", $optionName);
                        } else {
                            $goodsOptionName = '';
                        }
                        if (count($goodsVal['optionText']) > 0) {
                            foreach ($goodsVal['optionText'] as $optionTextKey => $optionTextVal) {
                                $optionTextName[] = $optionTextVal['optionName'] . ':' . $optionTextVal['optionValue'];
                            }
                            $goodsOptionTextName = ' / ' . implode(" / ", $optionTextName);
                        } else {
                            $goodsOptionTextName = '';
                        }
                        $goodsName = StringUtils::removeTag($goodsVal['goodsNm']) . StringUtils::removeTag($goodsOptionName) . StringUtils::removeTag($goodsOptionTextName);
                        $goods['cartNo'] = $goodsVal['sno'];
                        $goods['goodsNm'] = $goodsName;
                        $returnData[$i]['orderAddFieldName'] = $requiredName . ' ' . $useVal['orderAddFieldName'] . ' : ' . $goodsName;
                        $returnData[$i]['orderAddFieldHtml'] = $this->getOrderAddFieldUseHtml($useVal, $goods, ($i + 1));
                        $returnData[$i]['orderAddFieldRequired'] = $useVal['orderAddFieldRequired'];
                        $returnData[$i]['orderAddFieldType'] = $useVal['orderAddFieldType'];
                        $i++;
                    }
                }
            } else if ($useVal['orderAddFieldProcess'] == 'order') {
                foreach ($cartGoods as $goodsKey => $goodsVal) {
                    $orderAddFieldUsable = $this->getOrderAddFieldUseApplyExcept($useVal, $goodsVal);
                    if ($orderAddFieldUsable) {
                        break 1;
                    }
                }
                if ($orderAddFieldUsable) {
                    $returnData[$i]['orderAddFieldName'] = $requiredName . ' ' . $useVal['orderAddFieldName'];
                    $returnData[$i]['orderAddFieldHtml'] = $this->getOrderAddFieldUseHtml($useVal, null, ($i + 1));
                    $returnData[$i]['orderAddFieldRequired'] = $useVal['orderAddFieldRequired'];
                    $returnData[$i]['orderAddFieldType'] = $useVal['orderAddFieldType'];
                    $i++;
                }
            }
        }

        return $returnData;
    }

    /**
     * getOrderAddFieldUseApplyExcept
     *
     * @param $orderAddFieldUseData
     * @param $goodsVal
     *
     * @return bool
     * @author su
     */
    public function getOrderAddFieldUseApplyExcept($orderAddFieldUseData, $goodsVal)
    {
        $orderAddFieldUsable = false;
        if ($orderAddFieldUseData['orderAddFieldApply']['type'] == 'all') {
            $orderAddFieldUsable = true;
        } else {
            if ($orderAddFieldUseData['orderAddFieldApply']['type'] == 'category') {
                $checkVal = 'cateCd';
            } else if ($orderAddFieldUseData['orderAddFieldApply']['type'] == 'brand') {
                $checkVal = 'brandCd';
            } else if ($orderAddFieldUseData['orderAddFieldApply']['type'] == 'goods') {
                $checkVal = 'goodsNo';
            }

            foreach ($orderAddFieldUseData['orderAddFieldApply']['data'] as $applyKey => $applyVal) {
                if ($applyVal == $goodsVal[$checkVal]) {
                    $orderAddFieldUsable = true;
                    break 1;
                }
            }
        }

        if (in_array('category', $orderAddFieldUseData['orderAddFieldExcept']['type'])) {
            $checkType = 'category';
            $checkVal = 'cateCd';
            if (in_array($goodsVal[$checkVal], $orderAddFieldUseData['orderAddFieldExcept']['data'][$checkType])) {
                $orderAddFieldUsable = false;
            }
        }
        if (in_array('brand', $orderAddFieldUseData['orderAddFieldExcept']['type'])) {
            $checkType = 'brand';
            $checkVal = 'brandCd';
            if (in_array($goodsVal[$checkVal], $orderAddFieldUseData['orderAddFieldExcept']['data'][$checkType])) {
                $orderAddFieldUsable = false;
            }
        }
        if (in_array('goods', $orderAddFieldUseData['orderAddFieldExcept']['type'])) {
            $checkType = 'goods';
            $checkVal = 'goodsNo';
            if (in_array($goodsVal[$checkVal], $orderAddFieldUseData['orderAddFieldExcept']['data'][$checkType])) {
                $orderAddFieldUsable = false;
            }
        }

        return $orderAddFieldUsable;
    }

    /**
     * getOrderAddFieldUseHtml
     *
     * @param      $orderAddFieldUseData
     * @param null $goods
     *
     * @return string
     * @author su
     */
    public function getOrderAddFieldUseHtml($orderAddFieldUseData, $goods = null, $key)
    {
        if(gd_is_skin_division()) {
            $formClass = "form_element";
            $choiceClass = "choice_s";
        } else {
            $formClass = "form-element";
            $choiceClass = "choice-s";
        }

        $fieldKey = empty($key) === false ? $key : $orderAddFieldUseData['orderAddFieldSort'];
        if ($goods['cartNo'] > 0) {
            $attrName = 'addField[' . $fieldKey . '][data][' . $goods['cartNo'] . ']';
            $attrId = 'addField_' . $orderAddFieldUseData['orderAddFieldNo'] . '_goods_' . $goods['cartNo'];
            $process = 'goods';
            $returnGoodsName = '<input type="hidden" name="addField[' . $fieldKey . '][goodsNm][' . $goods['cartNo'] . ']" value="' . $goods['goodsNm'] . '">';
        } else {
            $attrName = 'addField[' . $fieldKey . '][data]';
            $attrId = 'addField_' . $fieldKey . '_order';
            $process = 'order';
            $returnGoodsName = '';
        }
        if ($orderAddFieldUseData['orderAddFieldRequired'] == 'y') {
            $required = ' required="required"';
            $requiredClass = ' required';
            $title = sprintf(' title="' . $orderAddFieldUseData['orderAddFieldName'] . '%s"', __('은 필수 항목입니다.'));
        } else {
            $required = '';
            $requiredClass = '';
            $title = '';
        }
        $returnNo = '<input type="hidden" name="addField[' . $fieldKey . '][no]" value="' . $orderAddFieldUseData['orderAddFieldNo'] . '">';
        $returnSort = '<input type="hidden" name="addField[' . $fieldKey . '][sort]" value="' . $fieldKey . '">';
        $returnType = '<input type="hidden" name="addField[' . $fieldKey . '][type]" value="' . $orderAddFieldUseData['orderAddFieldType'] . '">';
        $returnProcess = '<input type="hidden" name="addField[' . $fieldKey . '][process]" value="' . $process . '">';
        $returnName = '<input type="hidden" name="addField[' . $fieldKey . '][name]" value="' . $orderAddFieldUseData['orderAddFieldName'] . '">';
        if (Request::isMobile()) {
            if ($orderAddFieldUseData['orderAddFieldType'] == 'text') {
                if ($orderAddFieldUseData['orderAddFieldOption']['password'] == 'y') {
                    $type = 'password';
                } else {
                    $type = 'text';
                }
                if ($orderAddFieldUseData['orderAddFieldOption']['maxlength'] > 0) {
                    $maxlength = ' maxlength="' . $orderAddFieldUseData['orderAddFieldOption']['maxlength'] . '"';
                } else {
                    $maxlength = ' maxlength="250"';
                }
                $returnData = '<div class="inp_tx"><input type="' . $type . '" name="' . $attrName . '"' . $maxlength . $required . $title . ' class="' . $requiredClass . '" /></div>';
            } else if ($orderAddFieldUseData['orderAddFieldType'] == 'textarea') {
                if ($orderAddFieldUseData['orderAddFieldOption']['height'] > 0) {
                    $height = ' height:' . $orderAddFieldUseData['orderAddFieldOption']['height'] . 'px;';
                } else {
                    $height = ' height:100px;';
                }
                $maxlength = ' maxlength="1000"';
                $returnData = '<textarea name="' . $attrName . '" style="width:98%;' . $height . '"' . $maxlength . $required . $title . ' class="input_textarea' . $requiredClass . '"></textarea>';
            } else if ($orderAddFieldUseData['orderAddFieldType'] == 'file') {
                $returnData = '<div class="inp_tx"><input type="file" name="' . $attrName . '"' . $required . $title . ' class="sp' . $requiredClass . '" /></div>';
            } else if ($orderAddFieldUseData['orderAddFieldType'] == 'radio') {
                if (is_array($orderAddFieldUseData['orderAddFieldOption']['field'])) {
                    foreach ($orderAddFieldUseData['orderAddFieldOption']['field'] as $radioKey => $radioVal) {
                        $radioData[] = '<span class="inp_rdo" style="position:static;"><input type="radio" name="' . $attrName . '" id="' . $attrId . '_' . $radioKey . '"' . $required . $title . ' class="sp' . $requiredClass . '" value="' . $radioVal . '"  /><label for="' . $attrId . '_' . $radioKey . '">' . $radioVal . '</label></span>';
                    }
                    $returnData = implode(' ', $radioData);
                    unset($radioData);
                }
            } else if ($orderAddFieldUseData['orderAddFieldType'] == 'checkbox') {
                if (is_array($orderAddFieldUseData['orderAddFieldOption']['field'])) {
                    foreach ($orderAddFieldUseData['orderAddFieldOption']['field'] as $checkboxKey => $checkboxVal) {
                        $checkboxData[] = '<span class="inp_chk" style="position:static;"><input type="checkbox" name="' . $attrName . '[' . $checkboxKey . ']" id="' . $attrId . '_' . $checkboxKey . '"' . $title . ' class="sp" value="' . $checkboxVal . '" /><label for="' . $attrId . '_' . $checkboxKey . '">' . $checkboxVal . '</label></span>';
                    }
                    $returnData = implode(' ', $checkboxData);
                    unset($checkboxData);
                }
            } else if ($orderAddFieldUseData['orderAddFieldType'] == 'select') {
                if (is_array($orderAddFieldUseData['orderAddFieldOption']['field'])) {
                    $selectData = '<div class="inp_sel" style="position:static;"><select name="' . $attrName . '" id="' . $attrId . '"' . $required . $title . ' />';
                    $selectData .= '<option value="" />= 선택 =</option>';
                    foreach ($orderAddFieldUseData['orderAddFieldOption']['field'] as $selectKey => $selectVal) {
                        $selectData .= '<option value="' . $selectVal . '" />' . $selectVal . '</option>';
                    }
                    $selectData .= '</select></div>';
                    $returnData = $selectData;
                    unset($selectData);
                }
            }
        } else {
            if ($orderAddFieldUseData['orderAddFieldType'] == 'text') {
                if ($orderAddFieldUseData['orderAddFieldOption']['password'] == 'y') {
                    $type = 'password';
                } else {
                    $type = 'text';
                }
                if ($orderAddFieldUseData['orderAddFieldOption']['width'] > 0) {
                    $width = ' width:' . $orderAddFieldUseData['orderAddFieldOption']['width'] . 'px;';
                } else {
                    $width = '';
                }
                if ($orderAddFieldUseData['orderAddFieldOption']['maxlength'] > 0) {
                    $maxlength = ' maxlength="' . $orderAddFieldUseData['orderAddFieldOption']['maxlength'] . '"';
                } else {
                    $maxlength = ' maxlength="250"';
                }
                $returnData = '<div class="txt-field hs" style="' . $width . '"><input type="' . $type . '" name="' . $attrName . '"' . $maxlength . $required . $title . ' class="text' . $requiredClass . '" /></div>';
            } else if ($orderAddFieldUseData['orderAddFieldType'] == 'textarea') {
                if ($orderAddFieldUseData['orderAddFieldOption']['height'] > 0) {
                    $height = ' height:' . $orderAddFieldUseData['orderAddFieldOption']['height'] . 'px;';
                } else {
                    $height = ' height:100px;';
                }
                $maxlength = ' maxlength="1000"';
                $returnData = '<textarea name="' . $attrName . '" style="width:98%;' . $height . '"' . $maxlength . $required . $title . ' class="text' . $requiredClass . '"></textarea>';
            } else if ($orderAddFieldUseData['orderAddFieldType'] == 'file') {
                $returnData = '<input type="file" name="' . $attrName . '"' . $required . $title . ' class="text' . $requiredClass . '" />';
            } else if ($orderAddFieldUseData['orderAddFieldType'] == 'radio') {
                if (is_array($orderAddFieldUseData['orderAddFieldOption']['field'])) {
                    foreach ($orderAddFieldUseData['orderAddFieldOption']['field'] as $radioKey => $radioVal) {
                        $radioData[] = '<span class="'.$formClass.'"><input type="radio" name="' . $attrName . '" id="' . $attrId . '_' . $radioKey . '"' . $required . $title . ' class="radio' . $requiredClass . '" value="' . $radioVal . '" /><label class="'.$choiceClass.'" for="' . $attrId . '_' . $radioKey . '">' . $radioVal . '</label></span>';
                    }
                    $returnData = implode(' ', $radioData);
                    unset($radioData);
                }
            } else if ($orderAddFieldUseData['orderAddFieldType'] == 'checkbox') {
                if (is_array($orderAddFieldUseData['orderAddFieldOption']['field'])) {
                    foreach ($orderAddFieldUseData['orderAddFieldOption']['field'] as $checkboxKey => $checkboxVal) {
                        $checkboxData[] = '<span class="'.$formClass.'"><input type="checkbox" name="' . $attrName . '[' . $checkboxKey . ']" id="' . $attrId . '_' . $checkboxKey . '"' . $title . ' class="checkbox" value="' . $checkboxVal . '" /><label class="check-s" for="' . $attrId . '_' . $checkboxKey . '">' . $checkboxVal . '</label></span>';
                    }
                    $returnData = implode(' ', $checkboxData);
                    unset($checkboxData);
                }
            } else if ($orderAddFieldUseData['orderAddFieldType'] == 'select') {
                if (is_array($orderAddFieldUseData['orderAddFieldOption']['field'])) {
                    $selectData = '<span class="'.$formClass.'"><select name="' . $attrName . '" id="' . $attrId . '"' . $required . $title . ' class="select">';
                    $selectData .= '<option value="">= 선택 =</option>';
                    foreach ($orderAddFieldUseData['orderAddFieldOption']['field'] as $selectKey => $selectVal) {
                        $selectData .= '<option value="' . $selectVal . '">' . $selectVal . '</option>';
                    }
                    $selectData .= '</select></span>';
                    $returnData = $selectData;
                    unset($selectData);
                }
            }
        }

        $return = $returnNo . $returnSort . $returnType . $returnProcess . $returnName . $returnGoodsName . $returnData;

        return $return;
    }

    /**
     * getOrderAddFieldSaveData
     *
     * @param $postAddField
     *
     * @return mixed
     * @throws Exception
     * @author su
     */
    public function getOrderAddFieldSaveData($postAddField)
    {
        // 설정된 주문추가정보의 고유번호, 필수여부, 노출유형, 노출유형설정
        $getOrderAddFieldCheckField = $this->getOrderAddFieldCheckField();
        // 설정된 주문추가정보의 고유번호를 key 로 array 생성
        $checkArr = [];
        foreach ($getOrderAddFieldCheckField as $checkKey => $checkVal) {
            $checkArr[$checkVal['orderAddFieldNo']] = $getOrderAddFieldCheckField[$checkKey];
        }
        // 주문하기에서 입력한 추가정보 데이터 가공
        foreach ($postAddField as $addFieldKey => $addFieldVal) {
            // 설정된 주문추가정보의 필수여부로 주문하기에서 넘어온 추가정보의 필수 체크
            if ($checkArr[$addFieldVal['no']]['orderAddFieldRequired'] == 'y') {
                if ($addFieldVal['type'] == 'checkbox') {
                    if ($addFieldVal['process'] == 'goods') {
                        $checkGoodsNumber = count($addFieldVal['goodsNm']);
                    } else {
                        $checkGoodsNumber = 1;
                    }
                    if (count($addFieldVal['data']) < $checkGoodsNumber) { // 공통한번, 상품별에 상관 없이 checked 를 하지 않으면 data가 안넘어옴
                        throw new Exception(sprintf($addFieldVal['name'] . '%s', __('은 필수 항목입니다.')));
                    }
                } else {
                    if (!$addFieldVal['data']) {
                        throw new Exception(sprintf($addFieldVal['name'] . '%s', __('은 필수 항목입니다.')));
                    }
                }
            }
            // 설정된 주문추가정보의 노출유형이 text 이고 암호화, 마스킹 처리에 따른 값 저장
            if ($checkArr[$addFieldVal['no']]['orderAddFieldType'] == 'text') {
                $options = json_decode($checkArr[$addFieldVal['no']]['orderAddFieldOption'], true);
                if ($options['encryptor'] == 'y') {
                    $postAddField[$addFieldKey]['encryptor'] = 'y';
                }
                if ($options['password'] == 'y') {
                    $postAddField[$addFieldKey]['password'] = 'y';
                }
            }
            // 입력 방법이 공통한번, 상품별 입력에 따른 데이터 가공
            if ($addFieldVal['process'] == 'goods') {
                // 상품별은 data 안에 상품명을 key로 한 데이터
                foreach ($addFieldVal['data'] as $addDataKey => $addDataVal) {
                    if ($addFieldVal['type'] == 'checkbox') { // 체크박스는 , 구분하여 값 저장
                        $addVal = implode(', ', $addDataVal);
                    } else {
                        $addVal = $addDataVal;
                    }
                    $addVal = htmlentities($addVal, ENT_QUOTES);
                    $addVal = stripslashes($addVal); // 역슬래시 제거
                    $addVal = trim(preg_replace('/(\r\n)|\n|\r/', '<br/>', $addVal));
                    if ($addFieldVal['type'] == 'text' && $postAddField[$addFieldKey]['encryptor'] == 'y') { // 암호화 저장
                        if (($postAddField[$addFieldKey]['password'] == 'y') && !preg_match("/^[A-Za-z0-9]*$/", $addVal)) { // 마스킹은 영문, 숫자만 가능
                            throw new Exception(sprintf($addFieldVal['name'] . '%s', __('은 영문, 숫자만 가능합니다.')));
                        }
                        if ($addVal) {
                            $addVal = Encryptor::encrypt($addVal);
                        }
                    }
                    $postAddField[$addFieldKey]['data'][$addDataKey] = $addVal;
                    if (!$postAddField[$addFieldKey]['data'][$addDataKey]) {
                        unset($postAddField[$addFieldKey]['data'][$addDataKey]);
                    }
                }
            } else {
                if ($addFieldVal['type'] == 'checkbox') {
                    $addVal = implode(', ', $addFieldVal['data']); // 체크박스는 , 구분하여 값 저장
                } else {
                    $addVal = $addFieldVal['data'];
                }
                $addVal = htmlentities($addVal, ENT_QUOTES);
                $addVal = stripslashes($addVal); // 역슬래시 제거
                $addVal = trim(preg_replace('/(\r\n)|\n|\r/', '<br/>', $addVal));
                if ($addFieldVal['type'] == 'text' && $postAddField[$addFieldKey]['encryptor'] == 'y') { // 암호화 저장
                    if (($postAddField[$addFieldKey]['password'] == 'y') && !preg_match("/^[A-Za-z0-9]*$/", $addVal)) { // 마스킹은 영문, 숫자만 가능
                        throw new Exception(sprintf($addFieldVal['name'] . '%s', __('은 영문, 숫자만 가능합니다.')));
                    }
                    if ($addVal) {
                        $addVal = Encryptor::encrypt($addVal);
                    }
                }
                $postAddField[$addFieldKey]['data'] = $addVal;
            }
            if (!$postAddField[$addFieldKey]['data']) {
                unset($postAddField[$addFieldKey]);
            }
        }

        return $postAddField;
    }

    /**
     * getOrderAddFieldView
     *
     * @param $getAddField
     *
     * @return mixed
     * @author su
     */
    public function getOrderAddFieldView($getAddField)
    {
        if (empty($getAddField) === false) {
            $getAddField = json_decode($getAddField, true);
            foreach ($getAddField as $addFieldKey => $addFieldVal) {
                if ($addFieldVal['type'] == 'text') {
                    if ($addFieldVal['process'] == 'goods') {
                        foreach ($addFieldVal['data'] as $goodsKey => $goodsVal) {
                            if ($addFieldVal['encryptor'] == 'y' && $goodsVal) {
                                $getAddField[$addFieldKey]['data'][$goodsKey] = __('해당 항목은 암호화 처리되었습니다.');
                            }
                        }
                    } else {
                        if ($addFieldVal['encryptor'] == 'y' && $addFieldVal['data']) {
                            $getAddField[$addFieldKey]['data'] = __('해당 항목은 암호화 처리되었습니다.');
                        }
                    }
                }
            }
        }

        return $getAddField;
    }


    /**
     * 자식 상품주문번호 정보 가져오는 기능
     *
     * @param      $orderNo
     * @param      $orderGoodsNo
     * @param null $search
     *
     * @return array|null
     */
    public function getChildAddGoods($orderNo, $orderGoodsNo, $search = null)
    {
        $orderGoodsList = $this->getOrderGoods($orderNo);
        $result = null;
        $addOrderGoodsData = null;
        $beforeOrderGoodsNo = null;
        foreach ($orderGoodsList as $orderGoodsData) {
            if ($orderGoodsData['goodsType'] == 'addGoods' && $beforeOrderGoodsNo) {
                $addOrderGoodsData[$beforeOrderGoodsNo][$orderGoodsData['sno']] = $orderGoodsData;
            } else {
                $beforeOrderGoodsNo = $orderGoodsData['sno'];
            }
        }
        foreach ($addOrderGoodsData as $parentOrderGoodsNo => $addGoods) {
            if ($parentOrderGoodsNo == $orderGoodsNo) {
                foreach ($addGoods as $key => $val) {
                    if ($search['orderStatus']) {
                        if (in_array($val['orderStatus'], $search['orderStatus']) === true) {
                            $result[] = $val;
                        }
                        continue;
                    }
                    $result[] = $val;
                }
            }
        }

        return $result;
    }

    /**
     * Sms/Mail 발송 후 결과를 저장하는 함수
     *
     * @param $orderNo
     * @param $flags
     */
    public function saveOrderSendInfoResult($orderNo, $flags)
    {
        $resultXml = '<root>' . ArrayUtils::arrayToXml($flags) . '</root>';
        $arrBind = [];
        $arrBind['param'][] = 'sendMailSmsFl = ?';
        $this->db->bind_param_push($arrBind['bind'], 's', $resultXml);
        $this->db->bind_param_push($arrBind['bind'], 's', $orderNo);
        $this->db->set_update_db(DB_ORDER, $arrBind['param'], 'orderNo = ?', $arrBind['bind']);
    }

    /**
     * 사용자 클레임 신청(환불/교환/반품)이 있을경우 주문리스트를 가공하여 리턴
     *
     * @param array   $arrData   주문리스트
     * @param string  $mode      모드
     *
     * @return mixed
     */
    public function getOrderClaimList($arrData, $mode = null)
    {
        if (empty($arrData) === true || is_array($arrData) === false) {
            return $arrData;
        }

        // 배열이 아닌 경우 배열로 만듦
        if (!is_array($arrData[0])) {
            $arrData = [$arrData];
        }

        // 사용자가 클레임을 신청하였다면 row를 추가
        $userHandleCnt = 0;
        foreach ($arrData as $oKey => $oVal) {
            $claimList = $this->getOrderListForClaim($oVal['orderNo']);
            foreach ($oVal['goods'] as $gKey => $gVal) {
                if ($gVal['userHandleSno'] > 0 && $gVal['handleSno'] == 0) {
                    // 배열이 아닌 경우 배열로 만듦
                    if (!is_array($claimList[0])) {
                        $claimList = [$claimList];
                    }

                    foreach ($claimList as $list) {
                        // 클레임sno와 같거나(전체) 주문상품번호가 같을 경우(수량별)
                        if (($list['userHandleNo'] === $gVal['userHandleSno'] && $list['goodsNo'] === $gVal['goodsNo']) || ($list['userHandleNo'] !== $gVal['userHandleSno'] && $list['userHandleGoodsNo'] === $gVal['sno'])) {
                            // 수량별로 클레임을 신청하였을 경우
                            if (empty($list['userHandleGoodsCnt']) === false && $list['userHandleGoodsCnt'] != $gVal['goodsCnt']) {
                                $arr_front = array_slice($arrData[$oKey]['goods'], 0, $gKey + $userHandleCnt);

                                // 한 주문상품, 여러개의 수량별 클레임의 경우
                                if ($list['userHandleGoodsNo'] === $gVal['sno'] && $list['userHandleNo'] !== $gVal['userHandleSno']) {
                                    $gVal['userHandleSno'] = $list['userHandleNo'];
                                }

                                // 클레임 상품정보row 생성하여 주문데이터에 추가
                                $arr_front[] = $gVal;
                                $arr_front[$gKey + $userHandleCnt]['goodsCnt'] = $list['userHandleGoodsCnt'];
                                $arr_front[$gKey + $userHandleCnt]['userHandleMode'] = $list['userHandleMode'];
                                $arr_front[$gKey + $userHandleCnt]['userHandleFl'] = $list['userHandleFl'];
                                $arr_front[$gKey + $userHandleCnt]['orderInfoRow'] = 1;

                                if ($list['userHandleMode'] === 'r') {
                                    $arr_front[$gKey + $userHandleCnt]['canRefund'] = true;
                                } else if ($list['userHandleMode'] === 'b') {
                                    $arr_front[$gKey + $userHandleCnt]['canBack'] = true;
                                } else if ($list['userHandleMode'] === 'e') {
                                    $arr_front[$gKey + $userHandleCnt]['canExchange'] = true;
                                }

                                // 수량별 클레임 신청에 따른 기존 주문데이터 값 변경
                                if ($arrData[$oKey]['goods'][$gKey + $userHandleCnt]['goodsCnt'] - $list['userHandleGoodsCnt'] !== 0) {
                                    $arrData[$oKey]['goods'][$gKey + $userHandleCnt]['goodsCnt'] = $arrData[$oKey]['goods'][$gKey + $userHandleCnt]['goodsCnt'] - $list['userHandleGoodsCnt'];
                                    $arrData[$oKey]['goods'][$gKey + $userHandleCnt]['userHandleSno'] = 0;
                                    $arrData[$oKey]['goods'][$gKey + $userHandleCnt]['userHandleMode'] = '';

                                    $arr_end = array_slice($arrData[$oKey]['goods'], $gKey + $userHandleCnt);
                                    $arrData[$oKey]['goods'] = array_merge($arr_front, $arr_end);

                                    if ($arrData[$oKey]['goods'][$gKey + $userHandleCnt]['goodsCnt'] !== 0) {
                                        if ($gVal['goodsType'] === 'goods') {
                                            $arrData[$oKey]['orderGoodsCnt']++;
                                        } else {
                                            $arrData[$oKey]['orderAddGoodsCnt']++;
                                        }
                                    }
                                    $userHandleCnt++;
                                } else {
                                    $arrData[$oKey]['goods'][$gKey + $userHandleCnt]['userHandleSno'] = $list['userHandleNo'];
                                    $arrData[$oKey]['goods'][$gKey + $userHandleCnt]['userHandleMode'] = $list['userHandleMode'];
                                }
                            }
                        }
                    }
                }
            }

            $userHandleCnt = 0;
        }

        // 리스트에서 각 모드별로 불필요한 row를 제거
        if (empty($mode) === false) {
            // 모드에 따라 클레임 코드 구분
            if ($mode == 'cancelRequest') {
                $statusUserCancelRequestCode = [];
                foreach ($this->statusUserClaimRequestCode as $key => $val) {
                    if ($val != 'r') {
                        $statusUserCancelRequestCode[$key] = $val;
                    }
                }
            } else if ($mode == 'refundRequest') {
                $statusUserRefundRequestCode = [];
                foreach ($this->statusUserClaimRequestCode as $key => $val) {
                    if ($val != 'b' && $val != 'e') {
                        $statusUserRefundRequestCode[$key] = $val;
                    }
                }
            }

            foreach ($arrData as $oKey => $oVal) {
                foreach ($oVal['goods'] as $gKey => $gVal) {
                    $isExceptGoods = false;
                    if ($mode == 'cancelRequest' || $mode == 'refundRequest') {
                        if ($gVal['userHandleSno'] == 0) {
                            $isExceptGoods = true;
                        } else if ($mode == 'cancelRequest' && in_array(substr($gVal['userHandleMode'], 0, 1), $statusUserCancelRequestCode) == false) {
                            $isExceptGoods = true;
                        } else if ($mode == 'refundRequest' && in_array(substr($gVal['userHandleMode'], 0, 1), $statusUserRefundRequestCode) == false) {
                            $isExceptGoods = true;
                        }
                    } else if ($mode == 'backRegist' || $mode == 'exchangeRegist' || $mode == 'refundRegist') {
                        if ($gVal['handleSno'] > 0 || $gVal['userHandleSno'] > 0) {
                            $isExceptGoods = true;
                        } else if ($mode =='backRegist' && substr($gVal['orderStatus'], 0, 1) != 'd') {
                            $isExceptGoods = true;
                        } else if ($mode =='exchangeRegist' && in_array(substr($gVal['orderStatus'], 0, 1), ['o', 'p', 'g', 'd']) == false) {
                            $isExceptGoods = true;
                        } else if ($mode =='refundRegist' && in_array(substr($gVal['orderStatus'], 0, 1), $this->statusClaimCode['r']) == false) {
                            $isExceptGoods = true;
                        }
                    }

                    if ($isExceptGoods == true) {
                        if ($gVal['goodsType'] === 'goods') {
                            $arrData[$oKey]['orderGoodsCnt']--;
                        } else {
                            $arrData[$oKey]['orderAddGoodsCnt']--;
                        }
                        unset($arrData[$oKey]['goods'][$gKey]);
                    }

                    if (empty($arrData[$oKey]['goods']) === true) {
                        unset($arrData[$oKey]);
                    }
                }
            }
        }

        // 주문리스트에도 클레임신청 가능여부값 저장
        foreach ($arrData as $oKey => $oVal) {
            foreach ($oVal['goods'] as $gKey => $gVal) {
                if ($gVal['handleSno'] <= 0 && $gVal['userHandleSno'] == 0) {
                    if ($gVal['canRefund'] == true) {
                        $arrData[$oKey]['canRefund'] = true;
                    }
                    if ($gVal['canBack'] == true && substr($gVal['orderStatus'], 0, 1) != 's') {
                        $arrData[$oKey]['canBack'] = true;
                    }
                    if ($gVal['canExchange'] == true && substr($gVal['orderStatus'], 0, 1) != 's') {
                        $arrData[$oKey]['canExchange'] = true;
                    }
                }
            }
        }

        return $arrData;
    }

    /**
     * 해당 상점일련번호가 기준몰인 경우 true 반환
     *
     * @param integer $mallSno 상점일련번호
     * @return bool
     * @author Jong-tae Ahn <qnibus@godo.co.kr>
     */
    public function isDefaultMall($mallSno) {
        return ($mallSno == DEFAULT_MALL_NUMBER);
    }

    /**
     * 멀티상점 텍스트 만들기
     *
     * @param $sno
     * @return string
     * @author Jong-tae Ahn <qnibus@godo.co.kr>
     */
    public function getMultiShopName($sno)
    {
        return MallDAO::getInstance()->selectMall($sno);
    }

    /**
     * 전체 국가 리스트 가져오기
     *
     * @return mixed
     * @author Jong-tae Ahn <qnibus@godo.co.kr>
     */
    public function getCountriesList()
    {
        return MallDAO::getInstance()->selectCountries();
    }

    /**
     * getCountryName
     *
     * @param $code
     *
     * @return mixed
     * @author Jong-tae Ahn <qnibus@godo.co.kr>
     */
    public function getCountryName($code)
    {
        $country = MallDAO::getInstance()->selectCountries($code);

        return $country['countryName'];
    }

    /**
     * getCountryCallPrefix
     *
     * @param $code
     *
     * @return mixed
     * @author Jong-tae Ahn <qnibus@godo.co.kr>
     */
    public function getCountryCallPrefix($code)
    {
        $country = MallDAO::getInstance()->selectCountries($code);

        return $country['callPrefix'];
    }

    /*
     * 배송정책에 정의된
     * 사용가능한 국가 리스트 가져오기
     *
     * @param integer $mallSno
     *
     * @author Jong-tae Ahn <qnibus@godo.co.kr>
     */
    public function getUsableCountriesList($mallSno=null)
    {
        $countries = [];

        if($mallSno === null){
            $mallSno = \Component\Mall\Mall::getSession('sno');
        }

        $overseasDelivery = new OverseasDelivery();
        $policy = $overseasDelivery->getBasicData($mallSno, 'mallSno');

        if ($policy['data']['standardFl'] == 'ems') {
            $countries = $policy['emsCountries'];
        } else {
            foreach ($policy['group'] as $group) {
                if (empty($group['countries']) === false) {
                    $countries = array_merge($group['countries'], $countries);
                }
            }
        }

        // 국가데이터 이름순 정렬 (영문기준)
        ArrayUtils::subKeySort($countries, 'countryName', $ascending = true);

        return $countries;
    }

    /**
     * getCountriesCode
     *
     * @return array|object
     * @author Jong-tae Ahn <qnibus@godo.co.kr>
     */
    public function getCountriesCode()
    {
        return MallDAO::getInstance()->selectCountries(null, false);
    }

    /**
     * 최근 주문 내역
     *
     */
    public function getLastOrderInfo($memNo)
    {
        if(empty($memNo) === true) {
            return;
        }

        $addField = 'o.orderNo, og.orderStatus, FLOOR(o.settlePrice) AS settlePrice, o.orderGoodsNm, o.orderGoodsNmStandard, og.goodsNo, g.imagePath, g.imageStorage, gi.imageName';
        $this->db->strField = $addField;
        $arrJoin[]  = ' LEFT JOIN ' . DB_ORDER_GOODS . ' og ON og.orderNo = o.orderNo';
        $arrJoin[]  = ' LEFT JOIN ' . DB_GOODS . ' g ON g.goodsNo = og.goodsNo';
        $arrJoin[]  = ' LEFT JOIN ' . DB_GOODS_IMAGE . ' gi ON g.goodsNo = gi.goodsNo AND imageKind = \'list\' ';
        $this->db->strJoin = implode('', $arrJoin);

        $arrWhere[] = 'o.memNo = ? ';
        $this->db->bind_param_push($this->arrBind, 'i', $memNo);
        $this->db->strWhere = implode(' AND ', gd_isset($arrWhere));

        $this->db->strOrder = 'o.regDt DESC';
        $this->db->strLimit = 1;

        $query = $this->db->query_complete();
        $strSQL = 'SELECT ' . array_shift($query) . ' FROM ' . DB_ORDER . ' AS o ' . implode(' ', $query);
        $data = $this->db->query_fetch($strSQL, $this->arrBind);
        return $data[0];
    }

    /*
     * 입금계좌 변경
     *
     */
    public function updateBankInfo($postData)
    {
        $updateData = [
            'bankAccount' => $postData['bankAccount'],
            'bankSender' => $postData['bankSender'],
        ];

        $arrBind = $this->db->get_binding(DBTableField::tableOrder(), $updateData, 'update', array_keys($updateData), ['orderNo']);
        $this->db->bind_param_push($arrBind['bind'], 's', $postData['orderNo']);
        $this->db->set_update_db(DB_ORDER, $arrBind['param'], 'orderNo = ?', $arrBind['bind'], false);
    }

    public function getMultiOrderInfo($orderNo, $useDefaultOrderInfo = false)
    {
        $arrField = DBTableField::setTableField('tableOrderInfo');
        $arrField[] = 'sno';
        $arrBind = $arrWhere = [];

        $arrWhere[] = '`orderNo` = ?';
        $this->db->bind_param_push($arrBind, 's', $orderNo);
        if ($useDefaultOrderInfo === false) {
            $arrWhere[] = '`orderInfoCd` > ?';
            $this->db->bind_param_push($arrBind, 'i', 1);
        }

        $this->db->strField = implode(', ', $arrField);
        $this->db->strWhere = implode(' AND ', gd_isset($arrWhere));
        $this->db->strOrder = 'orderInfoCd asc';

        $query = $this->db->query_complete();
        $strSQL = 'SELECT ' . array_shift($query) . ' FROM ' . DB_ORDER_INFO . implode(' ', $query);
        $data = $this->db->query_fetch($strSQL, $arrBind, true);

        $setData = [];
        $orderBasic = gd_policy('order.basic');
        foreach ($data as $key => $val) {
            $setData['InfoSno'][] = $val['sno'];
            $setData['receiverNm'][] = $val['receiverName'];
            $setData['receiverZipcode'][] = $val['receiverZipcode'];
            $setData['receiverZonecode'][] = $val['receiverZonecode'];
            $setData['receiverAddress'][] = $val['receiverAddress'];
            $setData['receiverAddressSub'][] = $val['receiverAddressSub'];
            $setData['receiverPhone'][] = $val['receiverPhone'];
            $setData['receiverCellPhone'][] = $val['receiverCellPhone'];
            $setData['orderMemo'][] = $val['orderMemo'];
            $setData['receiverPhonePrefixCode'][] = $val['receiverPhonePrefixCode'];
            $setData['receiverCellPhonePrefixCode'][] = $val['receiverCellPhonePrefixCode'];
            $setData['receiverPhonePrefix'][] = $val['receiverPhonePrefix'];
            $setData['receiverCellPhonePrefix'][] = $val['receiverCellPhonePrefix'];
            $setData['receiverCountryCode'][] = $val['receiverCountryCode'];
            $setData['receiverCountry'][] = $val['receiverCountry'];
            $setData['receiverState'][] = $val['receiverState'];
            $setData['receiverCity'][] = $val['receiverCity'];
            $setData['totalDeliveryInsuranceFee'][] = $val['totalDeliveryInsuranceFee'];
            $setData['deliveryVisit'][] = $val['deliveryVisit'];
            $setData['visitAddress'][] = $val['visitAddress'];
            $setData['visitName'][] = $val['visitName'];
            $setData['visitPhone'][] = $val['visitPhone'];
            $setData['visitMemo'][] = $val['visitMemo'];
            if ($orderBasic['useSafeNumberFl'] == 'y') {
                $setData['receiverUseSafeNumberFl'][] = $val['receiverUseSafeNumberFl'];
                $setData['receiverSafeNumber'][] = $val['receiverSafeNumber'];
            }
        }

        return $setData;
    }

    public function getOrderDeliveryInfo($orderNo)
    {
        $setData = $arrBind = $arrWhere = [];

        $arrWhere[] = '`orderNo` = ?';
        $this->db->bind_param_push($arrBind, 's', $orderNo);

        $this->db->strField = 'SUM(deliveryPolicyCharge) AS deliveryPolicyCharge, SUM(deliveryAreaCharge) AS deliveryAreaCharge';
        $this->db->strWhere = implode(' AND ', gd_isset($arrWhere));
        $this->db->strGroup = 'orderInfoSno';
        $this->db->strOrder = 'orderInfoSno ASC';

        $query = $this->db->query_complete();
        $strSQL = 'SELECT ' . array_shift($query) . ' FROM ' . DB_ORDER_DELIVERY . implode(' ', $query);
        $data = $this->db->query_fetch($strSQL, $arrBind, true);

        foreach ($data as $key => $value) {
            $setData['deliveryPolicyCharge'] += $value['deliveryPolicyCharge'];
            $setData['deliveryAreaCharge'] += $value['deliveryAreaCharge'];
            $setData['orderInfoCharge'][$key] = $value['deliveryPolicyCharge'];
        }

        return $setData;
    }

    public function getOrderRefundCount($orderNo)
    {
        $setData = $arrBind = $arrWhere = [];

        $arrWhere[] = 'orderNo = ?';
        $arrWhere[] = "orderStatus = 'r3'";
        $this->db->bind_param_push($arrBind, 's', $orderNo);

        $this->db->strField = 'COUNT(sno) AS cnt';
        $this->db->strWhere = implode(' AND ', gd_isset($arrWhere));

        $query = $this->db->query_complete();
        $strSQL = 'SELECT ' . array_shift($query) . ' FROM ' . DB_ORDER_GOODS . implode(' ', $query);
        $data = $this->db->query_fetch($strSQL, $arrBind, true);

        return $data[0]['cnt'];
    }

    public function getOrderHandleInfo($sno)
    {
        $setData = $arrBind = $arrWhere = [];

        $arrWhere[] = 'sno = ?';
        $this->db->bind_param_push($arrBind, 'i', $sno);

        $this->db->strField = '*';
        $this->db->strWhere = implode(' AND ', gd_isset($arrWhere));

        $query = $this->db->query_complete();
        $strSQL = 'SELECT ' . array_shift($query) . ' FROM ' . DB_ORDER_HANDLE . implode(' ', $query);
        $data = $this->db->query_fetch($strSQL, $arrBind, true);

        return $data[0];
    }

    public function getOrderDeliveryOriginal($sno)
    {
        $setData = $arrBind = $arrWhere = [];

        $arrWhere[] = 'sno = ?';
        $this->db->bind_param_push($arrBind, 'i', $sno);

        $this->db->strField = '*';
        $this->db->strWhere = implode(' AND ', gd_isset($arrWhere));

        $query = $this->db->query_complete();
        $strSQL = 'SELECT ' . array_shift($query) . ' FROM ' . DB_ORDER_DELIVERY_ORIGINAL . implode(' ', $query);
        $data = $this->db->query_fetch($strSQL, $arrBind, true);

        return $data[0];
    }

    /**
     * 배송 중, 배송 완료 상태인 상품를 카운트해서 2개 이상이면 true 아니면 false 을 넣어서 반환, 교환,환불,반품 신청 할 경우 제외
     * @param array $orderData 주문 정보
     * @return array $orderData
     */
    public function getOrderSettleButton($orderData)
    {
        $count = 0;
        if (gd_isset($orderData)) {
            foreach ($orderData as &$val) {
                foreach ($val['goods'] as &$orderGoods) {
                    //교환, 환불, 반품 신청 확인
                    $isHandle = gd_isset($orderGoods['userHandleFl']);
                    if (substr($orderGoods['orderStatus'], 0, 1) == 'd'  && $isHandle === null) {
                        $count++;
                    }
                }
                if ($count >= 2) {
                    $val['orderSettleButton'] = true;
                } else {
                    $val['orderSettleButton'] = false;
                }
                $count = 0;
            }
        }
        return $orderData;
    }

    /**
     * 클레임 발생 상태 체크
     *
     * @param string $orderNo 주문번호
     *
     * @return array $getData
     */
    public function getOrderGoodsClaimData($orderNo)
    {
        $arrBind = [];
        $strSQL = "
        SELECT og.sno, og.orderStatus, og.goodsCnt,
        (SELECT SUM(g.goodsCnt) - SUM(IF(og.orderStatus in ('c%', 'r%'), g.goodsCnt, 0)) FROM " . DB_ORDER_GOODS . " g WHERE g.orderNo = og.orderNo) AS allCnt 
        FROM " . DB_ORDER_GOODS . " og 
        WHERE og.orderNo = ?";

        $this->db->bind_param_push($arrBind, 's', $orderNo);
        $getData = $this->db->query_fetch($strSQL, $arrBind);
        unset($arrBind);

        $arrSubOrdStatus = [];
        $arrOriOrdStatus = [];
        foreach($getData as $key => $val){
            $ordStatus = substr($val['orderStatus'], 0, 1);
            $getData[$key]['changeOrderStatus'] = $ordStatus;
            $getData[$key]['originalOrderStatus'] = $val['orderStatus'];
            $arrSubOrdStatus[] = $getData[$key]['changeOrderStatus'];
            $arrOriOrdStatus[] = $getData[$key]['originalOrderStatus'];
        }

        if(in_array('o', $arrSubOrdStatus)){    // 부분 취소
            $setData['mode'] = 'cancel';
        } else if(in_array('c', $arrSubOrdStatus)) {
            if ($getData[0]['orderStatus'] == 'c1') { // 자동 취소
                $setData['mode'] = 'allAutoCancel';
            } else {  // 전체 취소
                $setData['mode'] = 'allCancel';
            }
        } else if(in_array('r1', $arrOriOrdStatus) || in_array('p', $arrSubOrdStatus)){    // 부분 환불
            $setData['mode'] = 'repay';
        } else if(!in_array('r1', $arrOriOrdStatus)){   // 전체 환불
            $setData['mode'] = 'allRepay';
        }

        return $setData;
    }

    /*
     * 주문의 상태값을 반환
     *
     * @param string $orderNo 주문번호
     *
     * @return string $orderStatus 주문상태
     *
     */
    public function getOnlyOrderStatus($orderNo)
    {
        $strSQL = 'SELECT orderStatus FROM ' . DB_ORDER . ' WHERE orderNo = ?';
        $arrBind = [
            's',
            $orderNo,
        ];
        $orderData = $this->db->query_fetch($strSQL, $arrBind)[0];
        $orderStatus = gd_isset($orderData['orderStatus'], '');

        return $orderStatus;
    }

    /*
     * 주문의 결제방식 값을 반환
     *
     * @param string $orderNo 주문번호
     *
     * @return string $settleKind 결제방식
     *
     */
    public function getOnlyOrderSettleKind($orderNo)
    {
        $strSQL = 'SELECT orderChannelFl, settleKind FROM ' . DB_ORDER . ' WHERE orderNo = ?';
        $arrBind = [
            's',
            $orderNo,
        ];
        $orderData = $this->db->query_fetch($strSQL, $arrBind)[0];
        $settleKind['kind'] = gd_isset($orderData['settleKind'], '');
        $settleKind['channel'] = gd_isset($orderData['orderChannelFl'], '');

        return $settleKind;
    }

    /*
     * handleMode 반환
     *
     * @param integer $orderGoodsSno 주문상품번호
     *
     * @return string handleMode 클레임종류
     *
     */
    public function getOnlyOrderHandleMode($orderGoodsSno)
    {
        $arrbind = [];

        $where[] = 'og.sno = ?';
        $this->db->bind_param_push($arrbind, 'i', $orderGoodsSno);

        // 쿼리문 생성 및 데이타 호출
        $this->db->strField = 'oh.handleMode';
        $this->db->strJoin = 'LEFT JOIN ' . DB_ORDER_HANDLE . ' AS oh ON og.handleSno = oh.sno';
        $this->db->strWhere = implode(' AND ', $where);
        $this->db->strLimit = 1;
        $query = $this->db->query_complete();
        $strSQL = 'SELECT ' . array_shift($query) . ' FROM ' . DB_ORDER_GOODS . ' AS og ' . implode(' ', $query);
        $getData = $this->db->query_fetch($strSQL, $arrbind, true)[0];

        return gd_isset($getData['handleMode'], '');
    }

    /*
    * 결제로그에 특정 주문상태값에 대한 변경처리가 있었는지 확인
    *
    * @param string $orderNo 주문번호
    * @param string $orderStatus 주문상태
    *
    * @return boolean
    *
    */
    public function getOrderLogChangeFl($orderNo, $orderStatus = 'p')
    {
        $strSQL = 'SELECT settleKind FROM ' . DB_ORDER . ' WHERE orderNo = ? LIMIT 1';
        $arrBind = [
            's',
            $orderNo,
        ];
        $orderSettleKindData = $this->db->query_fetch($strSQL, $arrBind, false);

        if($orderSettleKindData['settleKind'] === self::SETTLE_KIND_ZERO){
            //전액할인 결제수단 (초기주문이 p이므로 logCode01가 있는경우 이미 첫 결제확인 처리가 되었다는 의미)
            $strSQL = 'SELECT COUNT(1) AS cnt FROM ' . DB_LOG_ORDER . ' WHERE orderNo = ? AND logCode01 IS NOT NULL AND logCode01 != \'\'';
            $arrBind = [
                's',
                $orderNo,
            ];
        }
        else {
            //그외 결제수단 (logCode02 에 p 가 있다면 첫 결제확인 처리가 되었다는 의미)
            $strSQL = 'SELECT COUNT(1) AS cnt FROM ' . DB_LOG_ORDER . ' WHERE orderNo = ? AND INSTR(logCode02, ?) > 0';
            $arrBind = [
                'ss',
                $orderNo,
                $orderStatus,
            ];
        }

        $orderData = $this->db->query_fetch($strSQL, $arrBind, false);
        if((int)$orderData['cnt'] > 0){
            return true;
        }
        else {
            return false;
        }
    }

    /*
     * ID와 품목별로 카운트 처리
     *
     * @param string $orderNo 주문번호
     * @param array $goodsSno 품목번호
     * @param array $orderKind 결제 방식
     *
     * @return boolean
     *
     */
	public function setCountOrderGoods($orderNo, $aGoodsSno, $orderKind, $isClaim = false, $claimInfo)
	{
		$aOrderData = $this->getOnlyOrderData($orderNo);

		if ($aOrderData['memNo'] == 0) {
			return false;
		}

		// 주문 실패로그가있으면 무조건 패스
		$strSQL = 'SELECT COUNT(1) AS cnt FROM ' . DB_LOG_ORDER . ' WHERE orderNo = ? AND (INSTR(logCode02, ?) > 0 OR INSTR(logCode02, ?) > 0 OR INSTR(logCode02, ?) > 0)';
		$arrBind = [
			'ssss',
			$orderNo,
			'f2',
			'f3',
			'f4',
		];
		$orderDataFailed = $this->db->query_fetch($strSQL, $arrBind, false);

		if ((int)$orderDataFailed['cnt'] == 0) {
			$strSQL = 'SELECT COUNT(1) AS cnt FROM ' . DB_MEMBER_ORDER_GOODS_COUNT_LOG . ' WHERE orderNo = ?';
			$arrBind = [
				's',
				$orderNo,
			];
			$orderLogData = $this->db->query_fetch($strSQL, $arrBind, false);

			if ((int)$orderLogData['cnt'] == 0 || $isClaim === true) {
				foreach ($aGoodsSno['sno'] as $v) {
					$aValue = array();

					$aOrderGoodsData = $this->getOnlyOrderGoodsData($orderNo, $v);

					// 인서트 or 업데이트
					$aMemberOrderGoodsCountData = $this->getMemberOrderGoodsCountData($aOrderData['memNo'], $aOrderGoodsData['goodsNo']);
					if ($aOrderGoodsData['goodsType'] == 'goods') {
						if ($aMemberOrderGoodsCountData) {
							$aValue['memNo'] = $aOrderData['memNo'];
							$aValue['goodsNo'] = $aOrderGoodsData['goodsNo'];
							$aValue['orderCount'] = $aOrderGoodsData['goodsCnt'];
							$aValue['orderCountBefore'] = $aMemberOrderGoodsCountData['orderCount'];
							$aValue['orderNo'] = $orderNo;

							$this->updateMemberOrderGoodsCountData($aValue, $isClaim, $claimInfo, $claimInfo);
						} else {
							$aValue['memNo'] = $aOrderData['memNo'];
							$aValue['goodsNo'] = $aOrderGoodsData['goodsNo'];
							$aValue['orderCount'] = $aOrderGoodsData['goodsCnt'];
							$aValue['orderNo'] = $orderNo;

							$this->setMemberOrderGoodsCountData($aValue);
						}
					}
				}
			}
		}

		//중간체크

		return true;
	}

    /*
     * es_order의 정보만 가져온다
     *
     * @param string $orderNo 주문번호
     *
     * @return array
     *
     */
    public function getOnlyOrderData($orderNo = null)
    {
        if ($orderNo == null) return false;

        //그외 결제수단 (logCode02 에 p 가 있다면 첫 결제확인 처리가 되었다는 의미)
        $strSQL = 'SELECT * FROM ' . DB_ORDER . ' WHERE orderNo = ?';
        $arrBind = [
            's',
            $orderNo,
        ];

        $orderData = $this->db->query_fetch($strSQL, $arrBind, false);

        return $orderData;
    }

    /*
     * es_orderGoods의 정보만 가져온다
     *
     * @param string $orderNo 주문번호
     * @param int $goodsSno
     *
     * @return array
     *
     */
    public function getOnlyOrderGoodsData($orderNo = null, $goodsSno = 0)
    {
        if ($orderNo == null) return false;

        $strSQL = 'SELECT * FROM ' . DB_ORDER_GOODS . ' WHERE orderNo = ? and sno = ?';
        $arrBind = [
            'si',
            $orderNo,
            $goodsSno,
        ];

        $orderGoodsData = $this->db->query_fetch($strSQL, $arrBind);

        return $orderGoodsData[0];
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

        //그외 결제수단 (logCode02 에 p 가 있다면 첫 결제확인 처리가 되었다는 의미)
        $strSQL = 'SELECT * FROM ' . DB_MEMBER_ORDER_GOODS_COUNT . ' WHERE memNo = ? and goodsNo = ?';
        $arrBind = [
            'ii',
            $memNo,
            $goodsNo,
        ];

        $orderGoodsData = $this->db->query_fetch($strSQL, $arrBind);

        return $orderGoodsData[0];
    }

    /*
     * es_memberOrderGoodsCount 에 카운트 정보 입력한다
     *
     * @param int $memNo 회원No
     * @param int $goodsNo 상품번호
     * @param int $orderCount 주문카운트
     * @param int $orderNo 주문번호
     *
     * @return array
     *
     */
    public function setMemberOrderGoodsCountData($aValue = null)
    {
        if ($aValue == null || empty($aValue['memNo'])) return false;

        $aData['memNo'] = $aValue['memNo']; // 무조건 n 스킨에서 값이 잘못 던져짐
        $aData['goodsNo'] = $aValue['goodsNo']; // 무조건 n 스킨에서 값이 잘못 던져짐
        $aData['orderCount'] = $aValue['orderCount'];

        // 주문 사은품 정보 저장
        $arrBind = $this->db->get_binding(DBTableField::tableMemberOrderGoodsCount(), $aData, 'insert');
        $this->db->set_insert_db(DB_MEMBER_ORDER_GOODS_COUNT, $arrBind['param'], $arrBind['bind'], 'y', false);
        unset($aData, $arrBind);

        $aData['memNo'] = $aValue['memNo']; // 무조건 n 스킨에서 값이 잘못 던져짐
        $aData['goodsNo'] = $aValue['goodsNo']; // 무조건 n 스킨에서 값이 잘못 던져짐
        $aData['orderCountBefore'] = 0;
        $aData['orderCountAfter'] = $aValue['orderCount'];
        $aData['orderNo'] = $aValue['orderNo'];
        $aData['acceptIp'] = Request::getRemoteAddress();

        $arrBind = $this->db->get_binding(DBTableField::tableMemberOrderGoodsCountLog(), $aData, 'insert');
        $this->db->set_insert_db(DB_MEMBER_ORDER_GOODS_COUNT_LOG, $arrBind['param'], $arrBind['bind'], 'y', false);
    }

	/*
	 * es_memberOrderGoodsCount 에 카운트 정보 업데이트
	 *
	 * @param array $aValue 처리할 값의 배열
	 *
	 */
	public function updateMemberOrderGoodsCountData($aValue = null, $isClaim = false, $claimInfo)
	{
		if ($aValue == null || empty($aValue['memNo']) || empty($aValue['goodsNo'])) return false;

		if ($isClaim === false) {
			$aData['orderCount'] = $aValue['orderCountBefore'] + $aValue['orderCount'];
		} else {
			$aData['orderCount'] = $aValue['orderCountBefore'] - $claimInfo['orderCount'];
		}


		$compareField = array_keys($aData);
		$arrBind = $this->db->get_binding(DBTableField::tableMemberOrderGoodsCount(), $aData, 'update', $compareField);
		$this->db->bind_param_push($arrBind['bind'], 'i',  $aValue['memNo']);
		$this->db->bind_param_push($arrBind['bind'], 'i',  $aValue['goodsNo']);
		$this->db->set_update_db(DB_MEMBER_ORDER_GOODS_COUNT, $arrBind['param'], 'memNo = ? AND goodsNo = ?', $arrBind['bind']);
		unset($arrBind, $aData);

		$aData['memNo'] = $aValue['memNo']; // 무조건 n 스킨에서 값이 잘못 던져짐
		$aData['goodsNo'] = $aValue['goodsNo']; // 무조건 n 스킨에서 값이 잘못 던져짐
		$aData['orderCountBefore'] = $aValue['orderCountBefore'];
		if ($isClaim === false) {
			$aData['orderCountAfter'] = $aValue['orderCountBefore'] + $aValue['orderCount'];
		} else {
			$aData['orderCountAfter'] = $aValue['orderCountBefore'] - $claimInfo['orderCount'];
		}
		$aData['orderNo'] = $aValue['orderNo'];
		$aData['acceptIp'] = Request::getRemoteAddress();

		$arrBind = $this->db->get_binding(DBTableField::tableMemberOrderGoodsCountLog(), $aData, 'insert');
		$this->db->set_insert_db(DB_MEMBER_ORDER_GOODS_COUNT_LOG, $arrBind['param'], $arrBind['bind'], 'y', false);
	}

    /*
     * 취소/교환/반품/환불 처리 중 환불일 경우 PG환불 하는 프로세스
     *
     * @param array $bundleData 신청 정보
     *
     * @return string 결과정보
     */
    public function processAutoPgCancel($bundleData, $userHandleSno){
        if(Manager::isProvider()){
            //공급사일 경우, 처리 하지 않음
            return 'provider';
        }
        $logger = \App::getInstance('logger');
        $logger->info('processAutoPgCancel Start');
        //설정 불러오기
        $config = gd_policy('order.basic');
        $paycoPolicy = gd_policy('pg.payco');
        $kakaoPolicy = gd_policy('pg.kakaopay');

        //주문 정보 가져오기
        $order = \App::load('\\Component\\Order\\OrderAdmin');
        $logger->info('processAutoPgCancel call getOrderGoods'.$bundleData['orderNo']);
        $orderGoodsInfo = $order->getOrderGoods($bundleData['orderNo'], null, null, null, null, ['memNo']);

        foreach ($orderGoodsInfo as $key => $value) {
            $orderGoods[$value['sno']] = $value;

            // 이마트 보안취약점 요청사항 (사용자 환불신청시 회원 유효성 검증)
            if ($value['memNo'] != Session::get('member.memNo')) {
                $logger->info('return invalid_order');
                return 'invalid_order';
            }
        }

        //자동 환불 사용 중인지 확인
        $logger->info('processAutoPgCancel userHandleAutoFl value is '.$config['userHandleAutoFl']);
        if($config['userHandleAutoFl'] != 'y'){
            $logger->info('return not_use');
            return 'not_use';
        }
        //주문 전체건 환불인지 확인
        $oriGoodsCnt = 0;
        $userHandleGoodsCnt = 0;
        foreach($orderGoods as $key => $value){
            $oriGoodsCnt += $value['goodsCnt'];
            if(in_array($key, $bundleData['orderGoodsNo'])){
                $userHandleGoodsCnt += $bundleData['claimGoodsCnt'][$key];
            }
        }
        //전체 상품인지 확인
        if($oriGoodsCnt != $userHandleGoodsCnt){
            $logger->info('return not_whole_goods');
            return 'not_whole_goods';
        }

        //PG환불인지 확인(PG결제 중 신용카드, 간편 결제 중 페이코와 카카오페이)
        $tmpOrderInfo = $order->getOrderData($bundleData['orderNo']);
        if(!in_array('p', $config['userHandleAutoSettle']) && $tmpOrderInfo['pgName'] == 'payco'){
            $logger->info('return payco_payment');
            return 'payco_payment';
        }else if(!in_array('k', $config['userHandleAutoSettle']) && $tmpOrderInfo['pgName'] == 'kakaopay'){
            $logger->info('return kakao_payment');
            return 'kakao_payment';
        }
        // 전액할인, pg(신용카드), 페이코, 카카오페이 외 모든 결제 수단은 자동 결제 환불 불가
        if($tmpOrderInfo['settleKind'] != 'gz' && $tmpOrderInfo['settleKind'] != 'pc' && $tmpOrderInfo['pgName'] != 'payco' && $tmpOrderInfo['pgName'] != 'kakaopay'){
            $logger->info('return not_accepted_payment');
            return 'not_accepted_payment';
        }

        //주문상태 확인
        foreach($orderGoods as $key => $value){
            if(substr($value['orderStatus'], 0, 1) != 'p'){
                $logger->info('return not_accepted_status P');
                return 'not_accepted_status';
            }

            //공급사 포함 되어 있는지 확인
            if($config['userHandleAutoScmFl'] == 'n' && $value['scmNo'] > 1){
                $logger->info('return provider');
                return 'provider';
            }
        }

        //자동으로 일단 승인 하기
        foreach ($bundleData['orderGoodsNo'] as $key => $value) {
            $statusCheck[] = $bundleData['orderNo'];
            $statusCheck[] = $value;
            $statusCheck[] = $userHandleSno[$value];
            $statusCheck[] = $bundleData['claimGoodsCnt'][$value];
            $statusCheck[] = $bundleData['claimGoodsCnt'][$value];
            $userHandle2Process['statusCheck'][] = implode(INT_DIVISION, $statusCheck);
            unset($statusCheck);
        }

        $userHandle2Process['mode'] = 'user_claim_handle_accept';
        $userHandle2Process['statusMode'] = 'r';
        $userHandle2Process['adminHandleReason'] = '자동 환불';
        $orderAdmin = \App::load('\\Component\\Order\\OrderAdmin');
        $logger->info('bundleData value: '.print_r($bundleData, true));
        $logger->info('userHandle2Process value: '.print_r($userHandle2Process, true));
        $orderAdmin->approveUserHandle($userHandle2Process, 'y', true);

        //환불 처리용 데이터 생성
        $orderGoods = $order->getOrderGoods($bundleData['orderNo']);
        $orderView = $order->getOrderView($bundleData['orderNo']);

        $getData = $order->getRefundOrderView($bundleData['orderNo'], null, null, 'r', ['r3', 'e1', 'e2', 'e3', 'e4', 'e5', 'c1', 'c2', 'c3', 'c4'], null, 1);
        $orderReOrderCalculation = App::load(\Component\Order\ReOrderCalculation::class);
        $etcData = $order->getOrderView($bundleData['orderNo'], null, null, null, ['r3', 'c1', 'c2', 'c3', 'c4', 'e1', 'e2', 'e3', 'e4', 'e5']); // 나머지 정상적인 주문건
        $refundData = $orderReOrderCalculation->getSelectOrderGoodsRefundData($bundleData['orderNo'], $orderView['goods'], $etcData['goods']);
        $totalDeliveryPrice = gd_isset(array_sum($refundData['realDeliveryCharge']), 0);
        $tmpData = $getData['refundData'];
        $member = App::load(\Component\Member\Member::class);
        $memInfo = $member->getMemberId($getData['memNo']);
        if(!empty($memInfo['memId'])) {
            $memData = $member->getMember($memInfo['memId'], 'memId');
        }
        foreach ($orderGoods as $key => $val) {
            if ($userHandleSno == $val['handleSno']) {
                $handleData = $val;
            }
            // 결제금액
            $settlePrice = (($val['goodsPrice'] + $val['optionPrice'] + $val['optionTextPrice']) * $val['goodsCnt']) + $val['addGoodsPrice'] - $val['goodsDcPrice'] - $val['totalMemberDcPrice'] - $val['totalMemberOverlapDcPrice'] - $val['totalCouponGoodsDcPrice'] - $val['enuri'] - $val['totalDivisionCouponOrderDcPrice'];

            // 주문상태 모드 (한자리)
            $statusMode = substr($val['orderStatus'], 0, 1);

            // 합계금액 계산
            $totalGoodsPrice += ($val['goodsCnt'] * ($val['goodsPrice'] + $val['optionPrice'] + $val['optionTextPrice'])) + $val['addGoodsPrice'];
            $totalCostPrice += ($val['goodsCnt'] * ($val['costPrice'] + $val['optionCostPrice']));
            $totalDcPrice += $val['goodsDcPrice'] + $val['totalMemberDcPrice'] + $val['totalMemberOverlapDcPrice'] + $val['totalCouponGoodsDcPrice'] + $val['enuri'] + $val['totalDivisionCouponOrderDcPrice'];
            $totalSettlePrice += $settlePrice;
            $totalGoodsUseDeposit += $val['totalGoodsDivisionUseDeposit'];
            $totalGoodsUseMileage += $val['totalGoodsDivisionUseMileage'];
            $totalDeliveryUseDeposit += $val['divisionGoodsDeliveryUseDeposit'];
            $totalDeliveryUseMileage += $val['divisionGoodsDeliveryUseMileage'];
            $totalGiveMileage += $val['totalRealGoodsMileage'] + $val['totalRealMemberMileage'] + $val['totalRealCouponGoodsMileage'] + $val['totalRealDivisionCouponOrderMileage'];
            $totalRefundDeliveryCharge += ($val['refundDeliveryCharge'] + $val['refundDeliveryUseDeposit'] + $val['refundDeliveryUseMileage']);
            $totalRefundUseDeposit += $val['refundUseDeposit'];
            $totalRefundUseMileage += $val['refundUseMileage'];
            $totalRefundDeliveryDeposit += $val['refundDeliveryUseDeposit'];
            $totalRefundDeliveryMileage += $val['refundDeliveryUseMileage'];
            $totalRefundGiveMileage += $val['refundGiveMileage'];
            $totalRefundCharge += $val['refundCharge'];
            $totalRefundCompletePrice += $val['refundPrice'];
            $totalRefundDeliveryInsuranceFee += $val['refundDeliveryInsuranceFee'];
            $totalRefundUseDepositCommission += $val['refundUseDepositCommission']; // 예치금 부가결제 수수료
            $totalRefundUseMileageCommission += $val['refundUseMileageCommission']; // 마일리지 부가결제 수수료

            // 환불 금액 설정 합계
            $totalCompletePriceData['completeCashPrice'][$val['refundGroupCd']] += $val['completeCashPrice'];
            $totalCompletePriceData['completePgPrice'][$val['refundGroupCd']] += $val['completePgPrice'];
            $totalCompletePriceData['completeDepositPrice'][$val['refundGroupCd']] += $val['completeDepositPrice'];
            $totalCompletePriceData['completeMileagePrice'][$val['refundGroupCd']] += $val['completeMileagePrice'];

            $goodsTotalMileage = $val['totalRealGoodsMileage'] + $val['totalRealMemberMileage'] + $val['totalRealCouponGoodsMileage'] + $val['totalRealDivisionCouponOrderMileage'];

            $goodsPrice = ($val['goodsPrice'] + $val['optionPrice'] + $val['optionTextPrice']) * $val['goodsCnt'];
            $goodsDcPrice = $val['goodsDcPrice'] + $val['memberDcPrice'] + $val['memberOverlapDcPrice'] + $val['couponGoodsDcPrice'] + $val['divisionCouponOrderDcPrice'] + $val['enuri'];
            $refundPrice = $goodsPrice - $goodsDcPrice;

            // 최대 환불 수수료 계산
            $thisGoodsPrice = ($val['goodsPrice'] + $val['optionPrice'] + $val['optionTextPrice']) * $val['goodsCnt'];
            if ($thisGoodsPrice > $getData['totalRealPayedPrice']) {
                $thisMaxRefundCharge = $getData['totalRealPayedPrice'];
            } else {
                $thisMaxRefundCharge = $thisGoodsPrice;
            }

            // 기본 배송업체 설정
            if (empty($val['deliverySno']) === true) {
                $val['orderDeliverySno'] = $deliverySno;
            }

            $logger->info('foreach Step Start with '.$key);
            $refund[$val['handleSno']]['sno'] = $val['sno'];
            $refund[$val['handleSno']]['returnStock'] = 'n';
            $refund[$val['handleSno']]['originGiveMileage'] = $goodsTotalMileage;
            $refund[$val['handleSno']]['refundGiveMileage'] = $goodsTotalMileage;
            $refund[$val['handleSno']]['refundGoodsPrice'] = $goodsPrice;
            $tmpData['refundCharge'.$val['handleSno']] = 0;
            $tmpData['refundCharge'.$val['handleSno'].'Max'] = $thisMaxRefundCharge;
            $tmpData['refundCharge'.$val['handleSno'].'MaxOrg'] = $thisGoodsPrice;

            // 배송비 합계 (주문상품 테이블 기준)
            $totalDeliveryCharge += $val['deliveryCharge'] - $val['divisionDeliveryUseDeposit'] - $val['divisionDeliveryUseMileage'];
            $totalRealDeliveryCharge += $val['realDeliveryCharge'];
            $totalDeliveryInsuranceFee += $val['deliveryInsuranceFee'];

            $totalDeliveryCharge -= $val['refundDeliveryCharge'];

            // 배송비 합계 (배송비 테이블 기준)
            $totalDeliveryDcPrice += $val['divisionDeliveryCharge'];
            $totalDeliveryDcPrice += $val['divisionMemberDeliveryDcPrice'];
            $totalRealDelivery = $totalDeliveryCharge - $totalDeliveryDcPrice;
            if ($totalRealDelivery < 0 ) {
                $totalRealDelivery = 0;
            }
        }
        $totalCompleteCashPrice = array_sum($totalCompletePriceData['completeCashPrice']);
        $totalCompletePgPrice = array_sum($totalCompletePriceData['completePgPrice']);
        $totalCompleteDepositPrice = array_sum($totalCompletePriceData['completeDepositPrice']);
        $totalCompleteMileagePrice = array_sum($totalCompletePriceData['completeMileagePrice']);
        unset($totalCompletePriceData);

        foreach ($tmpData['aAliveDeliverySno'] as $k => $v) {
            $tmpData['aAliveDeliverySnoTmp'][] = $v;
        }
        unset($tmpData['aAliveDeliverySno']);
        $tmpData['aAliveDeliverySno'] = $tmpData['aAliveDeliverySnoTmp'];
        unset($tmpData['aAliveDeliverySnoTmp']);

        $iRefundDeliveryCouponDcPriceMin = 0;
        foreach ($tmpData['aDeliveryAmount'] as $orderDeliverySno => $aVal) {
            if (!in_array($orderDeliverySno, $tmpData['aAliveDeliverySno'])) {
                $iRefundDeliveryCouponDcPriceMin += $aVal['iCoupon'];
            }
            $tmpData['refundDeliveryCharge_'.$orderDeliverySno] = $aVal['iAmount'];
            $tmpData['refundDeliveryCharge_'.$orderDeliverySno.'Max'] = $aVal['iAmount'];
            $tmpData['refundDeliveryCharge_'.$orderDeliverySno.'Coupon'] = $aVal['iCoupon'];
        }
        $tmpData['refundDeliveryCouponDcPriceMin'] = $iRefundDeliveryCouponDcPriceMin;

        $logger->info('make [info]');
        $info['refundUseDepositCommission'] = 0;
        $info['refundUseMileageCommission'] = 0;
        $info['refundMethod'] = $tmpOrderInfo['settleKind'] == 'gz' ? '복합환불' : 'PG환불';
        $info['completeCashPrice'] = 0;
        $logger->info('$orderView value: '.print_r($orderView, true));
        $info['completePgPrice'] = $orderView['settlePrice']; //가격 확인 필요
        //$info['completeDepositPrice'] = $orderView['useDeposit'];
        $info['completeDepositPrice'] = 0;
        //$info['completeMileagePrice'] = $orderView['useMileage'];
        $info['completeMileagePrice'] = 0;
        $info['handleReason'] = $bundleData['userHandleReason'];
        $info['handleDetailReason'] = $bundleData['userHandleDetailReason'];
        $info['refundBankName'] = $bundleData['userRefundBankName'];
        $info['refundAccountNumber'] = $bundleData['userRefundAccountNumber'];
        $info['refundDepositor'] = $bundleData['userRefundDepositor'];

        $logger->info('make [check]');

        $logger->info('make [tmpData]');
        $tmpData['mode'] = 'refund_complete';
        $tmpData['orderNo'] = $bundleData['orderNo'];
        $tmpData['handleSno'] = max($userHandleSno);
        $tmpData['isAll'] = true;

        $tmpData['refund'] = $refund;
        $tmpData['refundDeliveryCharge'] = $totalDeliveryPrice;
        $tmpData['refundAliveDeliveryPriceSum'] = $totalDeliveryPrice;
        $tmpData['info'] = $info;

        $tmpData['lessRefundPrice'] = 0;
        $tmpData['refundPriceSum'] = $orderView['settlePrice'];
        $tmpData['refundGoodsPrice'] = $tmpData['refundGoodsPriceSum'];
        $tmpData['refundDeliveryPriceSum'] = $totalDeliveryPrice;
        $tmpData['returnGiftFl'] = 'n';
        $tmpData['returnStockFl'] = $config['userHandleAutoStockFl']; //재고 수량 복원 할지 여부
        $tmpData['returnCouponFl'] = $config['userHandleAutoCouponFl']; //쿠폰 복원 할지 여부

        $tmpData['totalRefundDeliveryPriceSum'] = $totalDeliveryPrice;
        $tmpData['refundDepositPrice'] = $tmpData['refundDepositPriceOrg'] = $tmpData['refundDepositPriceMax'] = $tmpData['refundDepositPriceMaxOrg'] = $tmpData['refundDepositPriceTotal'];
        $tmpData['refundMileagePrice'] = $tmpData['refundMileagePriceOrg'] = $tmpData['refundMileagePriceMax'] = $tmpData['refundMileagePriceMaxOrg'] = $tmpData['refundMileagePriceTotal'];
        $tmpData['refundGoodsCouponMileageOrg'] = $tmpData['refundGoodsCouponMileageMax'] = $tmpData['refundGoodsCouponMileage'];
        $tmpData['refundOrderCouponMileageOrg'] = $tmpData['refundOrderCouponMileageMax'] = $tmpData['refundOrderCouponMileage'];
        $tmpData['refundGroupMileageOrg'] = $tmpData['refundGroupMileageMax'] = $tmpData['refundGroupMileage'];
        $tmpData['refundGoodsDcPriceOrg'] = $tmpData['refundGoodsDcPrice'];
        $tmpData['refundGoodsDcPriceMaxOrg'] = $tmpData['refundGoodsDcPriceMax'];
        $tmpData['refundMemberAddDcPriceOrg'] = $tmpData['refundMemberAddDcPrice'];
        $tmpData['refundMemberAddDcPriceMaxOrg'] = $tmpData['refundMemberAddDcPriceMax'];
        $tmpData['refundMemberOverlapDcPriceOrg'] = $tmpData['refundMemberOverlapDcPrice'];
        $tmpData['refundMemberOverlapDcPriceMaxOrg'] = $tmpData['refundMemberOverlapDcPriceMax'];
        $tmpData['refundEnuriDcPriceOrg'] = $tmpData['refundEnuriDcPrice'];
        $tmpData['refundEnuriDcPriceMaxOrg'] = $tmpData['refundEnuriDcPriceMax'];
        $tmpData['refundMyappDcPriceOrg'] = $tmpData['refundMyappDcPrice'];
        $tmpData['refundMyappDcPriceMaxOrg'] = $tmpData['refundMyappDcPriceMax'];
        $tmpData['refundGoodsCouponDcPriceOrg'] = $tmpData['refundGoodsCouponDcPrice'];
        $tmpData['refundGoodsCouponDcPriceMaxOrg'] = $tmpData['refundGoodsCouponDcPriceMax'];
        $tmpData['refundOrderCouponDcPriceOrg'] = $tmpData['refundGoodsCouponDcPriceMax'];
        $tmpData['refundGoodsCouponDcPriceMaxOrg'] = $tmpData['refundGoodsCouponDcPriceMax'];
        $tmpData['refundOrderCouponDcPriceOrg'] = $tmpData['refundOrderCouponDcPrice'];
        $tmpData['refundOrderCouponDcPriceMaxOrg'] = $tmpData['refundOrderCouponDcPriceMax'];
        $tmpData['refundDeliveryCouponDcPriceOrg'] = $tmpData['refundDeliveryCouponDcPriceMax'] = $tmpData['refundDeliveryCouponDcPriceMaxOrg'] = $tmpData['refundDeliveryCouponDcPrice'];
        $tmpData['refundDepositPriceOrg'] = $tmpData['refundDepositPrice'];
        $tmpData['refundDepositPriceMaxOrg'] = $tmpData['refundDepositPriceMax'];
        $tmpData['refundMileagePriceOrg'] = $tmpData['refundMileagePrice'];
        $tmpData['refundMileagePriceMaxOrg'] = $tmpData['refundMileagePriceMax'];
        $tmpData['refundDepositPrice'] = $tmpData['refundDepositPriceNow'];
        $tmpData['refundUseDepositCommissionMax'] = $tmpData['refundDepositPriceNow'];
        $tmpData['refundUseMileageCommissionMax'] = $tmpData['refundMileagePriceNow'];
        $tmpData['refundGoodsDcSum'] = $tmpData['refundGoodsDcPriceSum'];

        $realRefundPrice = $totalSettlePrice + $totalDeliveryInsuranceFee + $totalDeliveryCharge - ($totalDeliveryCharge - $totalRealDeliveryCharge);
        $realRefundPrice -= ($totalRefundUseDeposit + $totalRefundUseMileage);

        $check['totalSettlePrice'] = $realRefundPrice;
        $check['totalRefundCharge'] = 0;
        $check['totalDeliveryCharge'] = $tmpData['totalRefundDeliveryPrice'];;
        $check['totalRefundPrice'] = $tmpData['totalRefundPrice'];
        $check['totalDeliveryInsuranceFee'] = $totalDeliveryInsuranceFee;
        $check['totalGiveMileage'] = $totalGiveMileage;
        $check['totalDeliveryCharge'] = $totalRealDeliveryCharge;

        $tmpData['check'] = $check;

        $tmpData['tmp']['refundMinusMileage'] = 'y';
        $tmpData['tmp']['memberMileage'] = $memData['mileage'];
        $tmpData['lessRefundPrice'] = $tmpData['totalRefundPrice'];
        $tmpData['etcRefundAddPaymentPrice'] = $tmpData['etcRefundGoodsAddPaymentPrice'] = $tmpData['etcRefundDeliveryAddPaymentPrice'] = 0;
        $tmpData['etcGoodsSettlePrice'] = gd_isset($tmpData['etcGoodsSettlePrice'], 0);
        $tmpData['etcDeliverySettlePrice'] = gd_isset($tmpData['etcDeliverySettlePrice'], 0);
        $tmpData['userRealRefundPrice'] = $tmpData['totalRefundPrice'];

        $tmpData['refundGoodsDcPriceFlag'] = ($tmpData['refundGoodsDcPriceNow'] > 0) ? 'T' : 'F';
        $tmpData['refundMemberAddDcPriceFlag'] = ($tmpData['refundMemberAddDcPriceNow'] > 0) ? 'T' : 'F';
        $tmpData['refundMemberOverlapDcPriceFlag'] = ($tmpData['refundMemberOverlapDcPriceNow'] > 0) ? 'T' : 'F';
        $tmpData['refundEnuriDcPriceFlag'] = ($tmpData['refundEnuriDcPriceNow'] > 0) ? 'T' : 'F';
        $tmpData['refundMyappDcPriceFlag'] = ($tmpData['refundMyappDcPriceNow'] > 0) ? 'T' : 'F';
        $tmpData['refundGoodsCouponDcPriceFlag'] = ($tmpData['refundGoodsCouponDcPriceNow'] > 0) ? 'T' : 'F';
        $tmpData['refundOrderCouponDcPriceFlag'] = ($tmpData['refundOrderCouponDcPriceNow'] > 0) ? 'T' : 'F';

        if($config['userHandleAutoCouponFl'] == 'y'){
            // 환불쿠폰 정보
            $orderCd = null;
            foreach ($orderView['goods'] as $sVal) {
                foreach ($sVal as $dVal) {
                    foreach ($dVal as $gVal) {
                        $orderCd[] = $gVal['orderCd'];
                    }
                }
            }
            $orderCoupon = $order->getOrderCoupon($bundleData['orderNo'], $orderCd, false);
        }
        foreach($orderCoupon as $key => $value){
            $tmpData['returnCoupon'][$value['memberCouponNo']] = 'y';
        }

        $tmpData['systemOrderType'] = 'refund';
        $tmpData['systemOrder']['use'] = true;

        $orderReorderCalculation = App::load(\Component\Order\ReOrderCalculation::class);
        $orderReorderCalculation->setRefundCompleteOrderGoodsNew($tmpData, true);

        return 'ok';


    }

	/**
	 * 아이디별 주문 개수 추가 되어 있는지 확인
	 *
	 *
	 * @param string  $orderNo 주문 번호
	 * @param string  $goodsNo 상품 번호
	 * @param string  $memNo   회원 번호
	 */
	public function isLoggedMemberOrderGoodsCountData($orderNo, $orderGoodsSno) {
		$strSQL = 'SELECT COUNT(1) as cnt FROM ' . DB_MEMBER_ORDER_GOODS_COUNT_LOG . ' WHERE orderNo = ? AND goodsNo = (SELECT goodsNo FROM '.DB_ORDER_GOODS.' WHERE sno = ?)';

		$arrBind = [
			'si',
			$orderNo,
			$orderGoodsSno
		];
		$getData = $this->db->query_fetch($strSQL, $arrBind, false);
		if ($getData['cnt'] > 0) {
			return true;
		} else {
			return false;
		}
	}
}
