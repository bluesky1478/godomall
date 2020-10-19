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
use Component\Member\Manager;
use Component\Validator\Validator;
use Framework\Debug\Exception\AlertBackException;
use Framework\Utility\ArrayUtils;
use Framework\Utility\GodoUtils;
use Session;

/**
 * Class ExcelForm
 * @package Bundle\Component\Excel
 * @author  yjwee <yeongjong.wee@godo.co.kr>
 *          atomyang
 */
class ExcelForm
{
    const SESSION_SECURITY_AUTH = 'SESSION_SECURITY_AUTH';
    const EXCEL_DOWNLOAD_REASON_CODE_ORDER = '06001';
    const EXCEL_DOWNLOAD_REASON_CODE_MEMBER = '06002';
    const EXCEL_DOWNLOAD_REASON_CODE_BOARD = '06003';

    protected $db;
    protected $gGlobal;

    /**
     * @var array arrBind
     */
    private $arrBind = [];

    /**
     * @var array 조건
     */
    private $arrWhere = [];

    /**
     * @var array 체크
     */
    private $checked = [];

    /**
     * @var array 검색
     */
    private $search = [];

    public $menuList = [];
    public $locationList = [];


    private $scheme = "";

    public function __construct()
    {
        if (!is_object($this->db)) {
            $this->db = \App::load('DB');
        }

        $databaseConfig = \App::getConfig('database');
        $this->scheme = $databaseConfig->getDatabase();

        //메뉴분류
        $this->menuList = [
            'goods'  => __('상품'),
            'order'  => __('주문'),
            'member' => __('회원'),
            /* 'promotion'=>'프로모션', */
            'board'  => __('게시판'),
            'scm'    => __('공급사'),
        ];

        //상품 상세 항목
        $this->locationList['goods'] = [
            'gift_list'            => __('사은품관리'),
            'gift_present_list'    => __('사은품 지급조건 관리'),
            'goods_list_delete'    => __('삭제상품 관리'),
            'goods_must_info_list' => __('상품 필수정보 관리'),
            'goods_list'           => __('상품 리스트'),
            'add_goods_list'       => __('추가상품 관리'),
            'common_content_list'  => __('상품상세 공통정보 관리'),
        ];

        //회원관리
        $this->locationList['member'] = [
            'member_list' => __('회원관리'),
        ];

        //프로모션
        $this->locationList['promotion'] = [
            'coupon_offline_list' => __('페이퍼쿠폰인증번호관리'),
            'coupon_offline_manage' => __('페이퍼쿠폰발급내역관리'),
            'coupon_manage' => __('쿠폰발급내역관리'),
        ];

        //게시판
        $this->locationList['board'] = [
            'board' => __('게시글 관리_게시글'),
            'memo'  => __('게시글 관리_댓글'),
        ];

        // 플러스리뷰
        if (GodoUtils::isPlusShop(PLUSSHOP_CODE_REVIEW) && Manager::isProvider() === false) {
            $this->menuList['plusreview'] = __('게시판');
            $this->locationList['plusreview']['plusreview_board'] = __('플러스리뷰 게시글 관리_게시글');
            $this->locationList['plusreview']['plusreview_memo'] = __('플러스리뷰 게시글 관리_댓글');
        }

        // 크리마 csv
        $this->locationList['crema'] = [
            'csv' => __('크리마리뷰 CSV'),
        ];

        //공급사 상세 항목
        if (gd_is_provider() === true) {

            //주문상세항목
            $this->locationList['order'] = [
                'order_list_pay'         => __('결제완료리스트'),
                'order_list_exchange'    => __('교환관리'),
                'order_list_settle'      => __('구매확정리스트'),
                'order_list_back'        => __('반품관리'),
                'order_list_delivery_ok' => __('배송완료리스트'),
                'order_list_delivery'    => __('배송중리스트'),
                'order_list_goods'       => __('상품준비중 리스트'),
                'order_list_all'         => __('주문통합리스트'),
                'order_list_refund'      => __('환불관리')
                //'tax_invoice_request'=>__('발행 요청 리스트'),
                //'tax_invoice_list'=>__('발행 내역 리스트')
            ];

            $this->menuList['scm'] = __('정산');
            $this->locationList['scm'] = [
                'scm_adjust_list'           => __('정산 관리'),
                'scm_adjust_order'          => __('주문상품 정산 요청'),
                'scm_adjust_delivery'       => __('배송비 정산 요청'),
                'scm_adjust_after_order'    => __('정산 후 주문 상품 환불 정산'),
                'scm_adjust_after_delivery' => __('정산 후 배송비 환불 정산'),
            ];

            $this->locationList['orderDraft'] = [
                'order_list_pay'         => __('결제완료리스트'),
            ];

            unset($this->menuList['promotion']);
            unset($this->menuList['member']);

        } else {

            //주문상세항목
            $this->locationList['order'] = [
                'order_list_fail'          => '결제실패/시도리스트',
                'order_list_pay'           => '결제완료리스트',
                'order_list_user_exchange' => '고객 교환신청 관리',
                'order_list_user_return'   => '고객 반품신청 관리',
                'order_list_user_refund'   => '고객 환불신청 관리',
                'order_list_exchange'      => '교환관리',
                'order_list_settle'        => '구매확정리스트',
                'order_list_back'          => '반품관리',
                'order_list_delivery_ok'   => '배송완료리스트',
                'order_list_delivery'      => '배송중리스트',
                'order_list_goods'         => '상품준비중 리스트',
                'order_list_order'         => '입금대기 리스트',
                'order_list_all'           => '주문통합리스트',
                'order_list_cancel'        => '취소관리',
                'order_list_refund'        => '환불관리',
                'tax_invoice_request'      => '발행 요청 리스트',
                'tax_invoice_list'         => '발행 내역 리스트',
            ];

            $this->locationList['scm'] = [
                'scm_list'                  => '공급사 관리',
                'scm_adjust_total'          => '통합 정산 관리',
                'scm_adjust_list'           => '정산 관리',
                'scm_adjust_order'          => '주문상품 정산 요청',
                'scm_adjust_delivery'       => '배송비 정산 요청',
                'scm_adjust_after_order'    => '정산 후 주문 상품 환불 정산',
                'scm_adjust_after_delivery' => '정산 후 배송비 환불 정산',
            ];

            $this->locationList['orderDraft'] = [
                'order_list_pay'         => __('결제완료리스트'),
            ];
        }

        $globals = \App::getInstance('globals');
        $this->gGlobal = $globals->get('gGlobal');
    }

    /**
     * 공급사 등록시 기본 양식 폼 등록
     *
     * @param $arrData
     */
    public function saveExcelFormDefaultData($arrData)
    {

        unset($this->menuList['member']);

        $orderAdd = [
            'order_list_pay',
            'order_list_settle',
            'order_list_delivery_ok',
            'order_list_delivery',
            'order_list_goods',
            'order_list_all',
        ];

        $scmExcept = [
            'order_list_cancel',
            'scm_list',
            'scm_adjust_total',
            'order_list_fail',
            'order_list_user_exchange',
            'order_list_user_return',
            'order_list_user_refund',
            'order_list_order',
        ];

        $this->menuList['orderDraft'] = _('발주서');
        $session = \App::getInstance('session');
        foreach ($this->menuList as $k => $v) {
            foreach ($this->locationList[$k] as $k1 => $v1) {

                if (in_array($k1, $scmExcept)) continue;

                $excelField = $this->setExcelForm($k, $k1);
                foreach ($excelField as $k2 => $v2) {
                    if ($v2['scmDisplayFl'] == 'n') unset($excelField[$k2]);
                }
                $setData['scmNo'] = $arrData['scmNo'];
                $setData['title'] = $v1;
                $setData['menu'] = $k;
                $setData['location'] = $k1;
                $setData['managerNo'] = $session->get('manager.sno');
                $setData['excelField'] = implode(STR_DIVISION, array_keys($excelField));
                $setData['defaultFl'] = "y";

                if ($k1 == 'goods_must_info_list' || $k1 == 'coupon_offline_list') {
                    $setData['displayFl'] = "n";
                } else {
                    $setData['displayFl'] = "y";
                }

                $arrBind = $this->db->get_binding(DBTableField::tableExcelForm(), $setData, 'insert');
                $this->db->set_insert_db(DB_EXCEL_FORM, $arrBind['param'], $arrBind['bind'], 'y');
                unset($arrBind);

                if ($k == 'order' && in_array($k1, $orderAdd)) {
                    $setData['title'] = $v1 . "_주문별";
                    $excelField = $this->setExcelForm($k, $k1);
                    foreach ($excelField as $k2 => $v2) {
                        if ($v2['orderFl'] != 'y') unset($excelField[$k2]);
                        if ($v2['scmDisplayFl'] == 'n') unset($excelField[$k2]);
                    }
                    $setData['excelField'] = implode(STR_DIVISION, array_keys($excelField));

                    $arrBind = $this->db->get_binding(DBTableField::tableExcelForm(), $setData, 'insert');
                    $this->db->set_insert_db(DB_EXCEL_FORM, $arrBind['param'], $arrBind['bind'], 'y');
                    unset($arrBind);
                }

                unset($setData);
            }
        }
    }


    /**
     * 개인정보를 포함한 필드를 카운트
     *
     * @param array|string $fields
     *
     * @return int
     */
    public function countPersonalField($fields)
    {
        if (is_array($fields) === false) {
            $fields = explode(STR_DIVISION, $fields);
            $fields = ArrayUtils::changeKeyValue($fields);
        }
        //@formatter:off
        $filters = [
            'memNm', 'cellPhone', 'phone', 'email', 'address', 'addressSub', 'orderName', 'orderPhone', 'orderCellPhone', 'orderEmail',
            'receiverName', 'receiverPhone', 'receiverCellPhone', 'taxCeoNm', 'taxAddress', 'taxEmail',
            'receiverAddress', 'receiverAddressSub', 'receiverAddressTotal', 'writerNm', 'writerHp', 'writerEmail', 'writerMobile',
            'ceo', 'comAddress', 'ceoNm', 'unstoringAddress', 'unstoringAddressSub', 'returnAddress', 'returnAddressSub', 'staff', 'applicantNm'
        ];
        //@formatter:on
        $fieldsKey = array_keys($fields);
        $count = 0;
        $filteredKeys = [];
        foreach ($filters as $filter) {
            if (in_array($filter, $fieldsKey)) {
                $filteredKeys[] = $filter;
                $count++;
                if($filter == 'staff') { // 담당자 정보는 count 4개
                    $count += 3;
                }
            }
        }

        return $count;
    }

    public function setExcelForm($menu, $location, $selected = null)
    {

        switch ($menu) {
            case 'goods':
                $setData = $this->setExcelFormGoods($location);
                break;
            case 'order':
                $setData = $this->setExcelFormOrder($location);
                break;
            case 'member':
                $setData = $this->setExcelFormMember($location);
                break;
            case 'promotion':
                $setData = $this->setExcelFormPromotion($location);
                break;
            case 'board':
                $setData = $this->setExcelFormBoard($location);
                break;
            case 'scm':
                $setData = $this->setExcelFormScm($location);
                break;
            case 'orderDraft':
                $setData = $this->setExcelFormOrderDraft($location);
                break;
            case 'plusreview':
                $setData = $this->setExcelFormPlusreview($location);
                break;
        }


        $selected = explode(STR_DIVISION, $selected);
        foreach ($setData as $k => $v) {
            if ($selected && in_array($k, $selected)) $setData[$k]['checked'] = "checked";
            if (gd_is_provider() === true && $v['scmDisplayFl'] == 'n') unset($setData[$k]);
        }


        return $setData;

    }

    public function setExcelFormMember($location)
    {
        $setData = [];
        //@formatter:off
        switch ($location) {
            case 'member_list':
                $setData = [
                    'memNo' => ['name'=>__('회원번호')],
                    'regDt' =>['name'=>__('등록일')],
                    'entryDt' =>['name'=>__('가입승인일')],
                    'appFl' =>['name'=>__('가입승인여부')],
                    'entryPath' =>['name'=>__('가입경로')],
                    'lastLoginDt' =>['name'=>__('최종로그인일')],
                    'sleepWakeDt' =>['name'=>__('휴면해제일')],
                    'memberFl' =>['name'=>__('회원구분')],
                    'groupSno' =>['name'=>__('등급')],
                    'memId' =>['name'=>__('아이디')],
                    'nickNm' =>['name'=>__('닉네임')],
                    'memPw' =>['name'=> sprintf('%s(%s)', __('비밀번호'), __('암호화문자'))],
                    'memNm' =>['name'=>__('이름')],
                    'email' =>['name'=>__('이메일')],
                    'cellPhone' =>['name'=>__('휴대폰번호')],
                    'phone' =>['name'=>__('전화번호')],
                    'address' =>['name'=>__('주소')],
                    'maillingFl' =>['name'=>__('메일수신여부')],
                    'smsFl' =>['name'=>__('SMS수신여부')],
                    'saleCnt' =>['name'=>__('상품주문건수')],
                    'saleAmt' =>['name'=>__('주문금액'),'type'=>'price'],
                    'mileage' =>['name'=>__('마일리지'),'type'=>'mileage'],
                    'deposit' =>['name'=>__('예치금'),'type'=>'deposit'],
                    'loginCnt' =>['name'=>__('방문횟수')],
                    'company' =>['name'=>__('상호')],
                    'busiNo' =>['name'=>__('사업자번호')],
                    'ceo' =>['name'=>__('대표자명')],
                    'service' =>['name'=>__('업태')],
                    'item' =>['name'=>__('종목')],
                    'comAddress' =>['name'=>__('사업자 주소')],
                    'fax' =>['name'=>__('팩스 번호')],
                    'recommId' =>['name'=>__('추천인아이디')],
                    'sexFl' =>['name'=>__('성별')],
                    'birthDt' =>['name'=>__('생일')],
                    'marriFl' =>['name'=>__('결혼여부')],
                    'marriDate' =>['name'=>__('결혼기념일')],
                    'job' =>['name'=>__('직업')],
                    'interest' =>['name'=>__('관심분야')],
                    'expirationFl' =>['name'=>__('개인정보유효기간')],
                    'memo' =>['name'=>__('남기는말씀')],
                    'ex1' =>['name'=>sprintf(__('추가정보%d'), 1)],
                    'ex2' =>['name'=>sprintf(__('추가정보%d'), 2)],
                    'ex3' =>['name'=>sprintf(__('추가정보%d'), 3)],
                    'ex4' =>['name'=>sprintf(__('추가정보%d'), 4)],
                    'ex5' =>['name'=>sprintf(__('추가정보%d'), 5)],
                    'ex6' =>['name'=>sprintf(__('추가정보%d'), 6)],
                    'mallSno' => ['name'=>__('상점구분')],
                ];

                break;
        }
        //@formatter:on
        return $setData;

    }


    public function setExcelFormPromotion($location)
    {
        $setData = [];
        //@formatter:off
        switch ($location) {
            case 'coupon_offline_list':
                $setData = [
                    'couponOfflineCodeUser' =>['name'=>__('인증번호')],
                    'regDt' =>['name'=>__('등록일')],
                    'couponOfflineInsertAdminId' =>['name'=>__('등록자')],
                    'couponOfflineCodeSaveType' =>['name'=>__('발급상태')]
                ];
                break;
            case 'coupon_offline_manage':
                $setData = [
                    'couponOfflineCodeUser' =>['name'=>__('인증번호')],
                    'memId' =>['name'=>__('회원 아이디')],
                    'memNm' =>['name'=>__('회원 이름')],
                    'groupNm' =>['name'=>__('회원 등급')],
                    'regDt' =>['name'=>__('발급일')],
                    'memberCouponEndDate' =>['name'=>__('만료일')],
                    'memberCouponUseDate' =>['name'=>__('사용일')],
                    'memberCouponState' =>['name'=>__('쿠폰상태')]
                ];
                break;
            case 'coupon_manage':
                $setData = [
                    'memId' =>['name'=>__('회원 아이디')],
                    'memNm' =>['name'=>__('회원 이름')],
                    'groupNm' =>['name'=>__('회원 등급')],
                    'regDt' =>['name'=>__('발급일')],
                    'memberCouponEndDate' =>['name'=>__('만료일')],
                    'memberCouponUseDate' =>['name'=>__('사용일')],
                    'couponSaveAdminId' =>['name'=>__('처리자')],
                    'memberCouponState' =>['name'=>__('쿠폰상태')]
                ];
                break;
        }
        //@formatter:on
        return $setData;

    }

    public function setExcelFormOrder($location)
    {
        //@formatter:off
        $setBaseData = [
            'orderNo' =>['name'=>__('주문 번호'),'orderFl'=>'y'],
            'orderTypeFl' =>['name'=>__('주문 유형'),'orderFl'=>'n'],
            'apiOrderNo' =>['name'=>__('외부채널주문번호'),'orderFl'=>'y'],
            'memNm' =>['name'=>__('회원명'),'orderFl'=>'y','scmDisplayFl'=>'n'],
            'memNo' =>['name'=>__('회원 아이디'),'orderFl'=>'y','scmDisplayFl'=>'n'],
            'groupNm' =>['name'=>__('회원등급명'),'orderFl'=>'y','scmDisplayFl'=>'n'],
            'orderGoodsNm' =>['name'=>__('주문 상품명'),'orderFl'=>'y'],
            'orderGoodsCnt' =>['name'=>__('주문 품목 개수'),'orderFl'=>'y'],
            'orderChannelFl' =>['name'=>__('주문 채널'),'orderFl'=>'y'],
            'totalSettlePrice'=>['name'=>__('총 결제 금액'),'type'=>'price','orderFl'=>'y','scmDisplayFl'=>'y'],
            'totalGoodsPrice'=>['name'=>__('총 품목 금액'),'type'=>'price','orderFl'=>'y'],
            'totalGoodsPriceByGoods'=>['name'=>__('상품별 품목금액'),'type'=>'price'],
            'totalDeliveryCharge'=>['name'=>__('총 배송 금액'),'type'=>'price','orderFl'=>'y'],
            'totalGift'=>['name'=>__('총 사은품 정보'),'orderFl'=>'y'],
            'totalUseDeposit'=>['name'=>__('사용된 총 예치금'),'type'=>'deposit','orderFl'=>'y','scmDisplayFl'=>'n'],
            'totalUseMileage'=>['name'=>__('사용된 총 마일리지'),'type'=>'mileage','orderFl'=>'y','scmDisplayFl'=>'n'],
            'totalMemberDcPrice'=>['name'=>__('총 회원 할인 금액') ,'type'=>'price','orderFl'=>'y','scmDisplayFl'=>'n'],
            'totalGoodsDcPrice'=>['name'=>__('총 상품 할인 금액') ,'type'=>'price','orderFl'=>'y','scmDisplayFl'=>'n'],
            'totalCouponDcPrice'=>['name'=>__('총 쿠폰 할인 금액') ,'type'=>'price','orderFl'=>'y','scmDisplayFl'=>'n'],
            'totalMileage'=>['name'=>__('적립될 총 마일리지') ,'type'=>'mileage','orderFl'=>'y','scmDisplayFl'=>'n'],
            'totalGoodsMileage'=>['name'=>__('적립될 총 상품 마일리지'),'type'=>'mileage','orderFl'=>'y','scmDisplayFl'=>'n'],
            'totalMemberMileage'=>['name'=>__('적립될 총 회원 마일리지'),'type'=>'mileage','orderFl'=>'y','scmDisplayFl'=>'n'],
            'totalCouponMileage'=>['name'=>__('적립될 총 쿠폰 마일리지'),'type'=>'mileage','orderFl'=>'y','scmDisplayFl'=>'n'],
            'settleKind'=>['name'=>__('결제방법'),'orderFl'=>'y','scmDisplayFl'=>'n'],
            'bankAccount'=>['name'=>__('입금계좌'),'orderFl'=>'y','scmDisplayFl'=>'n'],
            'bankSender' =>['name'=>__('입금자'),'orderFl'=>'y','scmDisplayFl'=>'n'],
            'receiptFl' =>['name'=>__('영수증 신청 여부'),'orderFl'=>'y','scmDisplayFl'=>'n'],
            'pgResultCode' =>['name'=>__('PG 결과 코드'),'orderFl'=>'y','scmDisplayFl'=>'n'],
            'pgTid' =>['name'=>__('PG 거래 번호'),'orderFl'=>'y','scmDisplayFl'=>'n'],
            'pgAppNo' =>['name'=>__('PG 승인 번호'),'orderFl'=>'y','scmDisplayFl'=>'n'],
            'paymentDt' =>['name'=>__('입금일자'),'scmDisplayFl'=>'n'],
            'orderDt' =>['name'=>__('주문일자'),'orderFl'=>'y'],
            'orderName' =>['name'=>__('주문자 이름'),'orderFl'=>'y'],
            'orderEmail' =>['name'=>__('주문자 e-mail'),'orderFl'=>'y'],
            'orderPhone' =>['name'=>__('주문자 전화번호'),'orderFl'=>'y'],
            'orderCellPhone' =>['name'=>__('주문자 핸드폰 번호'),'orderFl'=>'y'],
            'receiverName' =>['name'=>__('수취인 이름'),'orderFl'=>'y'],
            'receiverPhone' =>['name'=>__('수취인 전화번호'),'orderFl'=>'y'],
            'receiverCellPhone' =>['name'=>__('수취인 핸드폰 번호'),'orderFl'=>'y'],
            'receiverZipcode' =>['name'=>__('수취인 구 우편번호 (6자리)'),'orderFl'=>'y'],
            'receiverZonecode' =>['name'=>__('수취인 우편번호'),'orderFl'=>'y'],
            'receiverAddress' =>['name'=>__('수취인 주소'),'orderFl'=>'y'],
            'receiverAddressSub' =>['name'=>__('수취인 나머지 주소'),'orderFl'=>'y'],
            'receiverAddressTotal' =>['name'=>__('수취인 전체주소'),'orderFl'=>'y'],
            'orderMemo' =>['name'=>__('주문시 남기는 글'),'orderFl'=>'y'],
            'orderDeliverySno' =>['name'=>__('배송번호'),'scmDisplayFl'=>'n'],
            'goodsDeliveryCollectFl' =>['name'=>__('배송비 구분'),'scmDisplayFl'=>'n'],
            'scmNo' =>['name'=>__('공급사코드'),'scmDisplayFl'=>'n'],
            'scmNm' =>['name'=>__('공급사명'),'scmDisplayFl'=>'n'],
            'orderGoodsSno' =>['name'=>__('상품주문번호')],
            'apiOrderGoodsNo' =>['name'=>__('외부채널품목번호')],
            'orderCd' =>['name'=>__('주문코드(순서)')],
            'orderStatus' =>['name'=>__('주문상태')],
            'goodsNo' =>['name'=>__('상품코드')],
            'goodsCd' =>['name'=>__('자체상품코드')],
            'goodsModelNo' =>['name'=>__('모델명')],
            'goodsType' =>['name'=>__('상품종류')],
            'goodsNm' =>['name'=>__('상품명')],
            'optionInfo' =>['name'=>__('옵션정보')],
            'optionCode' =>['name'=>__('자체옵션코드')],
            'goodsCnt' =>['name'=>__('상품수량')],
            'goodsWeightVolume' =>['name'=>__('상품 무게/용량')],
            'goodsTotalWeightVolume' =>['name'=>__('상품 배송 총 무게/용량')],
            'cateCd' =>['name'=>__('카테고리명')],
            'brandCd' =>['name'=>__('브랜드명')],
            'makerNm' =>['name'=>__('제조사')],
            'originNm' =>['name'=>__('원산지')],
            'optionTextInfo' =>['name'=>__('텍스트옵션정보')],
            'divisionUseDeposit' =>['name'=>__('사용된 예치금'),'type'=>'deposit','scmDisplayFl'=>'n'],
            'divisionUseMileage' =>['name'=>__('사용된 마일리지'),'type'=>'mileage','scmDisplayFl'=>'n'],
            'memberDcPrice' =>['name'=>__('회원 할인 금액'),'type'=>'price','scmDisplayFl'=>'n'],
            'memberPolicy' =>['name'=>__('회원 할인 정보'),'scmDisplayFl'=>'n'],
            'goodsDcPrice' =>['name'=>__('상품 할인 금액'),'type'=>'price','scmDisplayFl'=>'n'],
            'goodsDiscountInfo' =>['name'=>__('상품 할인 정보'),'scmDisplayFl'=>'n'],
            'couponGoodsDcPrice' =>['name'=>__('쿠폰 할인 금액'),'type'=>'price','scmDisplayFl'=>'n'],
            'useCouponNm' =>['name'=>__('사용된 쿠폰명'),'scmDisplayFl'=>'n'],
            'timeSalePrice' =>['name'=>__('타임세일 할인금액'),'scmDisplayFl'=>'n'],
            'memberMileage' =>['name'=>__('적립 회원 마일리지'),'type'=>'mileage','scmDisplayFl'=>'n'],
            'goodsMileage' =>['name'=>__('적립 상품 마일리지'),'type'=>'mileage','scmDisplayFl'=>'n'],
            'couponGoodsMileage' =>['name'=>__('적립 쿠폰 마일리지'),'type'=>'mileage','scmDisplayFl'=>'n'],
            'goodsTaxInfo' =>['name'=>__('상품부가세정보')],
            'goodsPrice' =>['name'=>__('판매가'),'type'=>'price'],
            'fixedPrice' =>['name'=>__('정가'),'type'=>'price'],
            'costPrice' =>['name'=>__('매입가'),'type'=>'price'],
            'commission' =>['name'=>__('수수료율')],
            'optionPrice' =>['name'=>__('옵션 금액'),'type'=>'price'],
            'optionCostPrice' =>['name'=>__('옵션 매입가'),'type'=>'price'],
            'optionTextPrice' =>['name'=>__('텍스트옵션 금액'),'type'=>'price'],
            'goodsPriceWithOption' =>['name'=>__('판매가 (옵션가포함)'),'type'=>'price'],
            'ogi.presentSno' =>['name'=>__('사은품 지급 제목')],
            'ogi.giftNo' =>['name'=>__('사은품 정보')],
            'invoiceCompanySno' =>['name'=>__('배송 업체 번호')],
            'invoiceNo' =>['name'=>__('송장 번호')],
            'addField' =>['name'=>__('추가 정보')],
            'hscode' => ['name'=>__('HS코드')],
            'multiShippingOrder' => ['name'=>__('배송지')],
            'multiShippingPrice' => ['name'=>__('배송지별 배송비'),'type'=>'price'],
            'totalEnuriDcPrice' => ['name'=>__('총 운영자추가할인 금액'),'type'=>'price','orderFl'=>'y'],
            'enuri' => ['name'=>__('운영자추가할인 금액'),'type'=>'price'],
        ];
        $setTaxData = [
            'sno' => ['name'=>__('번호')],
            'regDt' => ['name'=>__('발행요청일')],
            'orderNo' => ['name'=>__('주문번호')],
            'applicantNm' => ['name'=>__('주문자')],
            'orderStatusStr' => ['name'=>__('주문상태')],
            'requestNm' => ['name'=>__('요청인')],
            'taxBusiNo' => ['name'=>__('사업자번호')],
            'taxCompany' => ['name'=>__('회사명')],
            'taxCeoNm' => ['name'=>__('대표자명')],
            'taxService' => ['name'=>__('업태')],
            'taxItem' => ['name'=>__('종목')],
            'taxAddress' => ['name'=>__('사업장 주소')],
            'taxEmail' => ['name'=>__('발행 이메일')],
            'totalPrice' => ['name'=>__('결제금액')],
            'tax' => ['name'=>__('세금등급')],
            'reTotalPrice' => ['name'=>__('발행액')],
            'price' => ['name'=>__('공급가액')],
            'vat' => ['name'=>__('세액')],
            'issueDt' => ['name'=>__('발행일')],
        ];
        if ($location =='order_list_refund' || $location =='order_list_all') {
            $setGlobalData = [
                'mallSno' => ['name'=>__('상점구분'),'orderFl'=>'y' ],
                'orderGoodsNmStandard' => ['name'=>__('주문 상품명(해외상점)'),'orderFl'=>'y'],
                'global_totalSettlePrice' => ['name'=>__('총 결제금액(해외상점)'),'orderFl'=>'y','type'=>'price',],
                'global_totalGoodsPrice' => ['name'=>__('총 품목금액(해외상점)'),'orderFl'=>'y','type'=>'price',],
                'global_totalDeliveryCharge' => ['name'=>__('총 배송금액(해외상점)'),'orderFl'=>'y','type'=>'price',],
                'overseasSettlePrice' => ['name'=>__('PG승인 금액'),'orderFl'=>'y','type'=>'price',],
                'global_totalGoodsDcPrice' => ['name'=>__('총 상품할인 금액(해외상점)'),'type'=>'price',],
                'global_refundPrice' => ['name'=>__('총 환불금액(해외상점)'),'type'=>'price',],
                'global_completePgPrice' => ['name'=>__('카드환불 금액(해외상점)'),'type'=>'price',],
                'global_refundDeliveryCharge' => ['name'=>__('총 환불 배송비(해외상점)'),'type'=>'price',],
                'goodsNmStandard' => ['name'=>__('상품명(해외상점)')],
            ];
        } else {
            $setGlobalData = [
                'mallSno' => ['name'=>__('상점구분'),'orderFl'=>'y' ],
                'orderGoodsNmStandard' => ['name'=>__('주문 상품명(해외상점)'),'orderFl'=>'y'],
                'global_totalSettlePrice' => ['name'=>__('총 결제금액(해외상점)'),'orderFl'=>'y','type'=>'price',],
                'global_totalGoodsPrice' => ['name'=>__('총 품목금액(해외상점)'),'orderFl'=>'y','type'=>'price',],
                'global_totalDeliveryCharge' => ['name'=>__('총 배송금액(해외상점)'),'orderFl'=>'y','type'=>'price',],
                'overseasSettlePrice' => ['name'=>__('PG승인 금액'),'orderFl'=>'y','type'=>'price',],
                'global_totalGoodsDcPrice' => ['name'=>__('총 상품할인 금액(해외상점)'),'type'=>'price',],
                'goodsNmStandard' => ['name'=>__('상품명(해외상점)')],
            ];
        }

        $setAppData = [
            'totalMyappDcPrice' =>['name'=>__('총 모바일앱 할인금액'), 'scmDisplayFl'=>'n', 'orderFl'=>'y'],
            'myappDcPrice' =>['name'=>__('모바일앱 할인금액'), 'scmDisplayFl'=>'n'],
        ];

        if (gd_is_plus_shop(PLUSSHOP_CODE_USEREXCHANGE)) {
            $orderBasic = gd_policy('order.basic');
            if (($orderBasic['userHandleAdmFl'] == 'y' && $orderBasic['userHandleScmFl'] == 'y') === false) {
                unset($orderBasic['userHandleScmFl']);
            }

            if (((!Manager::isProvider() && $orderBasic['userHandleAdmFl'] == 'y') || (Manager::isProvider() && $orderBasic['userHandleScmFl'] == 'y')) && in_array($location, ['order_list_all', 'order_list_order', 'order_list_goods', 'order_list_delivery', 'order_list_delivery_ok'])) {
                $setBaseData['userHandleInfo'] = [
                    'name' => __('고객 클레임 신청정보'),
                ];
            }
        }
        //@formatter:on

        $setData = [];
        //@formatter:off
        switch ($location) {
            case 'order_list_fail':
                $setData = $setBaseData;
                break;
            case 'order_list_delivery_ok':
            case 'order_list_delivery':
            case 'order_list_goods':
            case 'order_list_settle':
                $addSetData =  [
                    'deliveryDt' =>['name'=>__('배송 일자')],
                    'deliveryCompleteDt' =>['name'=>__('배송 완료 일자')],
                    'finishDt' =>['name'=>__('구매확정 일자')],
                    'packetCodeFl' =>['name'=>__('묶음배송 여부'),'orderFl'=>'y'],
                    'packetCode' =>['name'=>__('묶음배송 그룹번호'),'orderFl'=>'y']
                ];

                $setData = array_merge($setBaseData, $addSetData);
                break;
            case 'order_list_order':
            case 'order_list_pay':

                $addSetData =  [
                    'deliveryDt' =>['name'=>__('배송 일자')],
                    'deliveryCompleteDt' =>['name'=>__('배송 완료 일자')],
                    'finishDt' =>['name'=>__('구매확정 일자')]
                ];

                $setData = array_merge($setBaseData, $addSetData);
                break;

            case 'order_list_user_exchange':
            case 'order_list_user_return':
            case 'order_list_user_refund':

                $addSetData =  ['totalClaimCnt' =>['name'=>__('주문 클래임 품목 개수'),'scmDisplayFl'=>'n'],
                    'userHandleReason' =>['name'=>__('사유')],
                    'userHandleDetailReason' =>['name'=>__('고객클레임 신청 메모')],
                    'userRefundAccountNumber' =>['name'=>__('환불계좌')],
                    'userHandleRegDt' =>['name'=>__('클레임 접수일자')],
                    'handleDt' =>['name'=>__('클레임 완료일자')],
                    'adminHandleReason' =>['name'=>__('클레임 운영자 클레임 관리 메모')],
                    'deliveryDt' =>['name'=>__('배송 일자')],
                    'deliveryCompleteDt' =>['name'=>__('배송 완료 일자')],
                    'finishDt' =>['name'=>__('구매확정 일자')]];

                $setData = array_merge($setBaseData, $addSetData);

                break;
            case 'order_list_exchange' :
            case 'order_list_exchange_cancel' :
            case 'order_list_exchange_add' :

                $addSetData =  ['handleReason' =>['name'=>__('사유')],
                    'handleDetailReason' =>['name'=>__('상세사유')],
                    'handleRegDt' =>['name'=>__('교환 접수일자')],
                    'handleDt' =>['name'=>__('교환 완료일자')],
                    'deliveryDt' =>['name'=>__('배송 일자')],
                    'deliveryCompleteDt' =>['name'=>__('배송 완료 일자')],
                    'finishDt' =>['name'=>__('구매확정 일자')]];

                $setData = array_merge($setBaseData, $addSetData);

                break;
            case 'order_list_back':

                $addSetData =  ['handleReason' =>['name'=>__('사유')],
                    'handleDetailReason' =>['name'=>__('상세사유')],
                    'refundMethod' =>['name'=>__('환불수단')],
                    'refundAccountNumber' =>['name'=>__('환불계좌')],
                    'handleRegDt' =>['name'=>__('클레임 접수일자')],
                    'handleDt' =>['name'=>__('클레임 완료일자')],
                    'deliveryDt' =>['name'=>__('배송 일자')],
                    'deliveryCompleteDt' =>['name'=>__('배송 완료 일자')],
                    'finishDt' =>['name'=>__('구매확정 일자')]];

                $setData = array_merge($setBaseData, $addSetData);
                break;
            case 'order_list_all':

                $addSetData =  ['totalClaimCnt' =>['name'=>__('주문 클래임 품목 개수'),'scmDisplayFl'=>'n'],
                    'refundPrice'=>['name'=>__('총 환불금액'),'type'=>'price'],
                    'completeCashPrice'=>['name'=>__('현금 환불금액'),'type'=>'price' ,'scmDisplayFl'=>'n'],
                    'completePgPrice'=>['name'=>__('카드 환불금액'),'type'=>'price' ,'scmDisplayFl'=>'n'],
                    'completeDepositPrice'=>['name'=>__('예치금 환불금액'),'type'=>'deposit' ,'scmDisplayFl'=>'n'],
                    'completeMileagePrice'=>['name'=>__('마일리지 환불금액'),'type'=>'mileage' ,'scmDisplayFl'=>'n'],
                    'refundCharge'=>['name'=>__('환불수수료'),'type'=>'price' ,'scmDisplayFl'=>'n'],
                    'refundDeliveryCharge'=>['name'=>__('총 환불 배송비'),'type'=>'price'],
                    'refundUseDeposit'=>['name'=>__('총 환원 예치금'),'type'=>'deposit' ,'scmDisplayFl'=>'n'],
                    'refundUseMileage'=>['name'=>__('총 환원 마일리지'),'type'=>'mileage','scmDisplayFl'=>'n'],
                    'refundUseDepositCommission'=>['name'=>__('총 환원 예치금 수수료'),'type'=>'deposit' ,'scmDisplayFl'=>'n'],
                    'refundUseMileageCommission'=>['name'=>__('총 환원 마일리지 수수료'),'type'=>'mileage','scmDisplayFl'=>'n'],
                    'handleReason' =>['name'=>__('사유')],
                    'handleDetailReason' =>['name'=>__('상세사유')],
                    'refundAccountNumber' =>['name'=>__('환불계좌')],
                    'regDt' =>['name'=>__('클레임 접수일자')],
                    'handleDt' =>['name'=>__('클레임 완료일자')],
                    'cancelGoodsCnt' =>['name'=>__('취소 상품 수량'),'scmDisplayFl'=>'n'],
                    'exchangeGoodsCnt' =>['name'=>__('교환 상품 수량')],
                    'backGoodsCnt' =>['name'=>__('반품 상품 수량')],
                    'refundGoodsCnt' =>['name'=>__('환불 상품 수량')],
                    'deliveryDt' =>['name'=>__('배송 일자')],
                    'deliveryCompleteDt' =>['name'=>__('배송 완료 일자')],
                    'finishDt' =>['name'=>__('구매확정 일자')],
                    'packetCodeFl' =>['name'=>__('묶음배송 여부'),'orderFl'=>'y'],
                    'packetCode' =>['name'=>__('묶음배송 그룹번호'),'orderFl'=>'y']];

                $setData = array_merge($setBaseData, $addSetData);

                break;
            case 'order_list_cancel':

                $addSetData =  ['handleReason' =>['name'=>__('사유')],
                    'deliveryDt' =>['name'=>__('배송 일자'), 'scmDisplayFl'=>'n'],
                    'deliveryCompleteDt' =>['name'=>__('배송 완료 일자'), 'scmDisplayFl'=>'n'],
                    'finishDt' =>['name'=>__('구매확정 일자'), 'scmDisplayFl'=>'n']];

                $setData = array_merge($setBaseData, $addSetData);

                break;
            case 'order_list_refund':

                $addSetData =  ['totalClaimCnt' =>['name'=>__('주문 클래임 품목 개수') ,'scmDisplayFl'=>'n'],
                    'refundPrice'=>['name'=>__('총 환불금액'),'type'=>'price'],
                    'completeCashPrice'=>['name'=>__('현금 환불금액'),'type'=>'price','scmDisplayFl'=>'n'],
                    'completePgPrice'=>['name'=>__('카드 환불금액'),'type'=>'price','scmDisplayFl'=>'n'],
                    'completeDepositPrice'=>['name'=>__('예치금 환불금액'),'type'=>'deposit','scmDisplayFl'=>'n'],
                    'completeMileagePrice'=>['name'=>__('마일리지 환불금액'),'type'=>'mileage','scmDisplayFl'=>'n'],
                    'refundCharge'=>['name'=>__('환불수수료'),'type'=>'price','scmDisplayFl'=>'n'],
                    'refundDeliveryCharge'=>['name'=>__('총 환불 배송비'),'type'=>'price'],
                    'refundUseDeposit'=>['name'=>__('총 환원 예치금'),'type'=>'deposit','scmDisplayFl'=>'n'],
                    'refundUseMileage'=>['name'=>__('총 환원 마일리지'),'type'=>'mileage','scmDisplayFl'=>'n'],
                    'refundUseDepositCommission'=>['name'=>__('총 환원 예치금 수수료'),'type'=>'deposit','scmDisplayFl'=>'n'],
                    'refundUseMileageCommission'=>['name'=>__('총 환원 마일리지 수수료'),'type'=>'mileage','scmDisplayFl'=>'n'],
                    'handleReason' =>['name'=>__('사유')],
                    'handleDetailReason' =>['name'=>__('상세사유')],
                    'refundMethod' =>['name'=>__('환불수단')],
                    'refundAccountNumber' =>['name'=>__('환불계좌')],
                    'regDt' =>['name'=>__('클레임 접수일자')],
                    'handleDt' =>['name'=>__('클레임 완료일자')],
                    'deliveryDt' =>['name'=>__('배송 일자')],
                    'deliveryCompleteDt' =>['name'=>__('배송 완료 일자')],
                    'finishDt' =>['name'=>__('구매확정 일자')]];

                $setData = array_merge($setBaseData, $addSetData);

                break;
            case 'tax_invoice_request':
                $addSetData = [
                    'adminMemo' => ['name'=>__('메모')],
                ];
                $setData = array_merge($setTaxData, $addSetData);
                break;
            case 'tax_invoice_list':
                $addSetData = [
                    'issueFl' => ['name'=>__('종류')],
                    'printFl' => ['name'=>__('발행상태')],
                    'processDt' => ['name'=>__('처리일')],
                    'adminMemo' => ['name'=>__('메모')],
                ];
                $setData = array_merge($setTaxData, $addSetData);
                break;
        }
        //@formatter:on
        if (gd_is_plus_shop(PLUSSHOP_CODE_PURCHASE) === true && gd_is_provider() === false) {
            $setData = array_merge($setData, ['purchaseNm' => ['name' => __('매입처')]]);
        }

        $orderBasic = gd_policy('order.basic');
        if (gd_isset($orderBasic['safeNumberFl'])) {
            $setData = array_merge($setData, ['receiverSafeNumber' =>['name'=>__('수취인 안심번호'), 'orderFl'=>'y']]);
        }

        $setData = array_merge($setData, $setGlobalData);
        $setData = array_merge($setData, $setAppData);

        return $setData;

    }


    public function setExcelFormGoods($location)
    {
        $setData = [];
        //@formatter:off
        switch ($location) {
            case 'gift_list':
                $setData = [
                    'regDt' =>['name'=>__('등록일')],
                    'modDt' =>['name'=>__('수정일')],
                    'scmNm' =>['name'=>__('공급사'),'scmDisplayFl'=>'n'],
                    'giftNo' =>['name'=>__('사은품코드')],
                    'giftCd' =>['name'=>__('자체사은품코드')],
                    'giftNm' =>['name'=>__('사은품명')],
                    'brandNm' =>['name'=>__('브랜드')],
                    'makerNm' =>['name'=>__('제조사명')],
                    'stock' =>['name'=>__('재고')],
                    'imageStorage' =>['name'=>__('이미지 저장소')],
                    'imagePath' =>['name'=>__('이미지경로')],
                    'imageNm' =>['name'=>__('이미지명')],
                    'giftDescription' =>['name'=>__('사은품 설명')]
                ];
                break;
            case 'gift_present_list':
                $setData = [
                    'regDt' =>['name'=>__('등록일')],
                    'modDt' =>['name'=>__('수정일')],
                    'scmNo' =>['name'=>__('공급사'),'scmDisplayFl'=>'n'],
                    'presentTitle' =>['name'=>__('사은품 지급 제목')],
                    'presentState' =>['name'=>__('진행상태')],
                    'periodYmd' =>['name'=>__('지급기간')],
                    'presentFl' =>['name'=>__('상품조건')],
                    'presentKindCd' =>['name'=>__('상품조건상세')],
                    'conditionFl' =>['name'=>__('지급조건')],
                    'conditionInfo' =>['name'=>__('지급조건 상세')],
                    'exceptFl' =>['name'=>__('예외조건')],
                    'exceptInfo' =>['name'=>__('예외조건 상세')]
                ];
                break;
            case 'goods_must_info_list' :
                $setData = [
                    'scmNm' =>['name'=>__('공급사'),'scmDisplayFl'=>'n'],
                    'mustInfoNm' =>['name'=>__('필수정보명')],
                    'regDt' =>['name'=>__('등록일')],
                    'modDt' =>['name'=>__('수정일')],
                    'mustInfo' =>['name'=>__('상세정보')]
                ];
                break;
            case 'add_goods_list' :
                $setData = [
                    'regDt' =>['name'=>__('등록일')],
                    'modDt' =>['name'=>__('수정일')],
                    'scmNm' =>['name'=>__('공급사'),'scmDisplayFl'=>'n'],
                    'addGoodsNo' =>['name'=>__('상품코드')],
                    'goodsCd' =>['name'=>__('자체 상품코드')],
                    'goodsModelNo' =>['name'=>__('모델번호')],
                    'viewFl' =>['name'=>__('노출여부')],
                    'soldOutFl' =>['name'=>__('품절여부')],
                    'goodsNm' =>['name'=>__('상품명')],
                    'brandNm' =>['name'=>__('브랜드')],
                    'makerNm' =>['name'=>__('제조사')],
                    'stockUseFl' =>['name'=>__('재고')],
                    'imageStorage' =>['name'=>__('이미지 저장소')],
                    'imagePath' =>['name'=>__('이미지 경로')],
                    'imageNm' =>['name'=>__('이미지')],
                    'goodsDescription' =>['name'=>__('상품 설명')],
                    'goodsPrice' =>['name'=>__('판매가'),'type'=>'price'],
                    'costPrice' =>['name'=>__('매입가'),'type'=>'price'],
                    'commission' =>['name'=>__('수수료율')],
                    'taxFreeFl' =>['name'=>__('과세/면세')],
                    'optionNm' =>['name'=>__('옵션값')]
                ];

                if (gd_is_plus_shop(PLUSSHOP_CODE_PURCHASE) === true && gd_is_provider() === false) {
                    $setData['purchaseNm'] = ['name'=>__('매입처')];
                }

                $globalField = DBTableField::getFieldNames('tableAddGoodsGlobal');
                unset($globalField['mallSno']);
                unset($globalField['addGoodsNo']);

                foreach($globalField as $k1 => $v1) {
                    foreach($this->gGlobal['mallList'] as $k => $v) {
                        if ($v['standardFl'] == 'n') {
                            $setData['global_' . $k . '_'.$k1] = ['name' => $v1.'('. $v['mallName'].')'];
                        }
                    }
                }


                break;
            case 'goods_list_delete':
            case 'goods_list':
                $setData = [
                    'regDt' =>['name'=>__('등록일')],
                    'modDt' =>['name'=>__('수정일')],
                    'scmNm' =>['name'=>__('공급사'),'scmDisplayFl'=>'n'],
                    'goodsNo' =>['name'=>__('상품코드')],
                    'goodsCd' =>['name'=>__('자체상품코드')],
                    'goodsModelNo' =>['name'=>__('모델번호')],
                    'goodsDisplayFl' =>['name'=>__(' PC쇼핑몰 노출상태')],
                    'goodsDisplayMobileFl' =>['name'=>__('모바일쇼핑몰 노출상태')],
                    'goodsSellFl' =>['name'=>__('PC쇼핑몰 판매상태')],
                    'goodsSellMobileFl' =>['name'=>__('모바일쇼핑몰 판매상태')],
                    'soldOutFl' =>['name'=>'품절여부'],
                    'category' =>['name'=>__('카테고리(카테고리 코드:카테고리명)')],
                    'goodsNm' =>['name'=>__('상품명(기본)')],
                    'goodsNmMain' =>['name'=>__('상품명(메인)')],
                    'goodsNmList' =>['name'=>__('상품명(리스트)')],
                    'goodsNmDetail' =>['name'=>__('상품명(상세)')],
                    'goodsNmPartner' =>['name'=>__('상품명(제휴)')],
                    'goodsSearchWord' =>['name'=>__('검색키워드')],
                    'brandNm' =>['name'=>__('브랜드')],
                    'makerNm' =>['name'=>__('제조사')],
                    'originNm' =>['name'=>__('원산지')],
                    'makeYmd' =>['name'=>__('제조일')],
                    'launchYmd' =>['name'=>__('출시일')],
                    'effectiveYmd' =>['name'=>__('유효일자')],
                    'goodsOpenDt' =>['name'=>__('상품노출시간')],
                    'goodsState' =>['name'=>__('상품상태')],
                    'goodsColor' =>['name'=>__('상품대표색상')],
                    'goodsWeight' =>['name'=>__('상품무게')],
                    'goodsVolume' =>['name'=>__('상품용량')],
                    'stockFl' =>['name'=>__('판매재고')],
                    'totalStock' =>['name'=>__('상품재고')],
                    'minOrderCnt' =>['name'=>__('최소구매수량')],
                    'maxOrderCnt' =>['name'=>__('최대구매수량')],
                    'salesYmd' =>['name'=>__('판매기간')],
                    'goodsPermission' =>['name'=>__('구매가능 회원등급')],
                    'goodsAccess' =>['name'=>__('접근권한')],
                    'mileageFl' =>['name'=>__('마일리지 지급')],
                    'goodsDiscountFl' =>['name'=>__('상품 할인') ,'scmDisplayFl'=>'n'],
                    'deliverySno' =>['name'=>__('배송비')],
                    'imageStorage' =>['name'=>__('이미지저장소')],
                    'imagePath' =>['name'=>__('이미지경로')],
                    'imageList' =>['name'=>__('이미지')],
                    'shortDescription' =>['name'=>__('짧은설명')],
                    'goodsDescription' =>['name'=>__('PC쇼핑몰상세설명')],
                    'goodsDescriptionMobile' =>['name'=>__('모바일쇼핑몰상세설명')],
                    'goodsDescriptionSameFl' =>['name'=>__('pc/모바일설명공통사용')],
                    'goodsPriceString' =>['name'=>__('판매가 대체문구')],
                    'fixedPrice' =>['name'=>__('정가'),'type'=>'price'],
                    'costPrice' =>['name'=>__('매입가'),'type'=>'price'],
                    'goodsPrice' =>['name'=>__('판매가'),'type'=>'price'],
                    'commission' =>['name'=>__('수수료율')],
                    'taxFreeFl' =>['name'=>__('과세/면세')],
                    'optionFl' =>['name'=>__('옵션사용여부')],
                    'optionDisplayFl' =>['name'=>__('옵션노출방식')],
                    'optionName' =>['name'=>__('옵션명')],
                    'option' =>['name'=>__('옵션')],
                    'addGoodsFl' =>['name'=>__('추가상품 사용여부')],
                    'addGoods' =>['name'=>__('추가상품(상품명,필수여부,판매가)')],
                    'optionTextFl' =>['name'=>__('텍스트옵션 사용여부')],
                    'optionText' =>['name'=>__('텍스트옵션(옵션명,필수여부,옵션가,필수여부)')],
                    'relationFl' =>['name'=>__('관련상품 사용여부')],
                    'relationCnt' =>['name'=>__('관련상품 수량')],
                    'relationGoodsNo' =>['name'=>__('관련상품 코드')],
                    'goodsIconStartYmd' =>['name'=>__('아이콘기간(시작)')],
                    'goodsIconEndYmd' =>['name'=>__('아이콘기간(끝)')],
                    'goodsIconCdPeriod' =>['name'=>__('아이콘(기간제한용)')],
                    'goodsIconCd' =>['name'=>__('아이콘(무제한용)')],
                    'goodsAddInfo' =>['name'=>__('추가항목(항목:내용)')],
                    'goodsMustInfo' =>['name'=>__('필수정보(항목:내용)')],
                    'detailInfoDelivery' =>['name'=>__('배송안내')],
                    'detailInfoAS' =>['name'=>__('AS안내')],
                    'detailInfoRefund' =>['name'=>__('환불안내')],
                    'detailInfoExchange' =>['name'=>__('교환안내')],
                    'naverFl' =>['name'=>__('네이버쇼핑 노출여부')],
                    'naverImportFlag' =>['name'=>__('수입 및 제작 여부')],
                    'naverProductFlag' =>['name'=>__('판매방식 구분')],
                    'naverAgeGroup' =>['name'=>__('주요 사용 연령대')],
                    'naverGender' =>['name'=>__('주요 사용 성별')],
                    'naverTag' =>['name'=>__('검색 태그')],
                    'naverAttribute' =>['name'=>__('속성 정보')],
                    'naverCategory' =>['name'=>__('네이버 카테고리 ID')],
                    'naverProductId' =>['name'=>__('가격비교 페이지 ID')],
                    'restockFl' =>['name'=>__('재입고알림')],
                    'qrCodeFl' =>['name'=>__('QR CODE 노출')],
                    'onlyAdultFl' =>['name'=>__('성인인증')],
                    'imgDetailViewFl' =>['name'=>__('상품 이미지 돋보기 사용여부')],
                    'externalVideoFl' =>['name'=>__('외부 동영상 사용 여부')],
                    'eventDescription' =>['name'=>__('이벤트문구')],
                    'memo' =>['name'=>__('관리메모')],
                    'hscode' =>['name'=>__('HS코드')],
                    'payLimitFl' =>['name'=>__('결제 수단 설정')],
                    'payLimit' =>['name'=>__('사용가능 결제수단')],
                    'seoTagFl' =>['name'=>__('상품 개별 SEO 설정 사용여부')],
                    'seoTagTitle' =>['name'=>__('타이틀')],
                    'seoTagAuthor' =>['name'=>__('메타태그 작성자')],
                    'seoTagDescription' =>['name'=>__('메타태그 설명')],
                    'seoTagKeyword' =>['name'=>__('메타태그 키워드')],
                    'paycoFl' =>['name'=>__('페이코쇼핑 노출여부')],
                ];


                if (gd_is_plus_shop(PLUSSHOP_CODE_PURCHASE) === true && gd_is_provider() === false) {
                    $setData['purchaseNm'] = ['name'=>__('매입처')];
                }

                $globalField = DBTableField::getFieldNames('tableGoodsGlobal');
                unset($globalField['mallSno']);
                unset($globalField['goodsNo']);


                foreach($globalField as $k1 => $v1) {
                    foreach($this->gGlobal['mallList'] as $k => $v) {
                        if ($v['standardFl'] == 'n') {
                            $setData['global_' . $k . '_'.$k1] = ['name' => $v1.'('. $v['mallName'].')'];
                        }
                    }
                }

                $setData['fixedSales'] = ['name'=>__('묶음주문기준')];
                $setData['salesUnit'] = ['name'=>__('묶음주문단위')];
                $setData['fixedOrderCnt'] = ['name'=>__('구매수량기준')];
                if(gd_is_provider() === false) {
                    $setData['orderGoodsCnt'] = ['name' => __('결제')];
                    $setData['hitCnt'] = ['name' => __('조회')];
                    $setData['orderRate'] = ['name' => __('구매율(%)')];
                    $setData['cartCnt'] = ['name' => __('담기')];
                    $setData['wishCnt'] = ['name' => __('관심')];
                    $setData['reviewCnt'] = ['name' => __('후기')];
                }
                $setData['purchaseGoodsNm'] = ['name'=>__('매입처 상품명')];
                //$setData['sellStopStock'] = ['name'=>__('판매중지수량')];
                //$setData['confirmRequestStock'] = ['name'=>__('확인요청수량')];
                $setData['optionDelivery'] = ['name'=>__('옵션배송상태')];
                $setData['optionSellFl'] = ['name'=>__('옵션품절상태')];
                $setData['cultureBenefitFl'] = ['name'=>__('도서공연비 소득공제 여부')];
                $setData['daumFl'] = ['name'=>__('쇼핑하우 노출여부')];
                $setData['kcmarkFl'] = ['name'=>__('KC인증 표시 여부')];
                $setData['kcmarkDivFl'] = ['name'=>__('KC인증 구분')];
                $setData['kcmarkNo'] = ['name'=>__('KC인증 번호')];
                $setData['kcmarkDt'] = ['name'=>__('KC인증 일자')];
                break;
            case 'common_content_list':
                $setData = [
                    'sno' =>['name'=>__('번호')],
                    'commonTitle' =>['name'=>__('공통정보 제목')],
                    'commonDt' =>['name'=>__('노출기간')],
                    'commonStatusFl' =>['name'=>__('진행상태')],
                    'commonUseFl' =>['name'=>__('노출상태')],
                    'commonTargetFl' =>['name'=>__('상품조건')],
                    'commonCd' =>['name'=>__('상품조건상세')],
                    'commonEx' =>['name'=>__('예외조건')],
                    'commonExCd' =>['name'=>__('예외조건 상세')],
                    'regDt' =>['name'=>__('등록일')],
                    'modDt' =>['name'=>__('수정일')]
                ];
                break;
        }
        //@formatter:on

        return $setData;
    }


    public function setExcelFormScm($location)
    {
        $setData = [];
        //@formatter:off
        switch ($location) {
            case 'scm_list':
                $setData = [
                    'scmType' =>['name'=>__('상태')],
                    'managerId' =>['name'=>__('공급사 아이디')],
                    'companyNm' =>['name'=>__('공급사명')],
                    'managerNickNm' =>['name'=>__('닉네임')],
                    'scmKind' =>['name'=>__('공급사타입')],
                    'scmCode' =>['name'=>__('공급사코드')],
                    'ceoNm' =>['name'=>__('대표자')],
                    'businessNo' =>['name'=>__('사업자번호')],
                    'service' =>['name'=>__('업태')],
                    'item' =>['name'=>__('종목')],
                    'address' =>['name'=>__('사업장주소')],
                    'addressSub' =>['name'=>__('상세주소')],
                    'unstoringAddress' =>['name'=>__('출고지주소')],
                    'unstoringAddressSub' =>['name'=>__('상세주소')],
                    'returnAddress' =>['name'=>__('반품/교환주소')],
                    'returnAddressSub' =>['name'=>__('상세주소')],
                    'scmCommission' =>['name'=>__('수수료')],
                    'scmCommissionDelivery' =>['name'=>__('배송비 수수료')],
                    'phone' =>['name'=>__('대표번호')],
                    'scmPermissionInsert' =>['name'=>__('상품등록권한')],
                    'scmPermissionModify' =>['name'=>__('상품수정권한')],
                    'scmPermissionDelete' =>['name'=>__('상품삭제권한')],
                    'scmInsertAdminId' =>['name'=>__('등록자')],
                    'regDt' =>['name'=>__('등록일')],
                    'staff' =>['name'=>__('담당자 정보')],
                    'account' =>['name'=>__('계좌 정보')]
                ];
                break;
            case 'scm_adjust_total':
                $setData = [
                    'sno' =>['name'=>__('번호')],
                    'searchDate' =>['name'=>__('조회기간')],
                    'companyNm' =>['name'=>__('공급사')],
                    'scmCode' =>['name'=>__('공급사코드')],
                    'priceTotal' =>['name'=>__('매출') ,'type'=>'price'],
                    'adjustTotal' =>['name'=>sprintf('%s(%s)', __('정산금액'), __('합계')) ,'type'=>'price'],
                    'adjustGoods' =>['name'=>sprintf('%s(%s)', __('정산금액'), __('상품')) ,'type'=>'price'],
                    'adjustDelivery' =>['name'=>sprintf('%s(%s)', __('정산금액'), __('배송비')),'type'=>'price'],
                    'commissionTotal' =>['name'=>sprintf('%s(%s)', __('수수료매출'), __('합계')),'type'=>'price'],
                    'commissionGoods' =>['name'=>sprintf('%s(%s)', __('수수료매출'), __('상품')),'type'=>'price'],
                    'commissionDelivery' =>['name'=>sprintf('%s(%s)', __('수수료매출'), __('배송비')),'type'=>'price'],
                    'refundTotal' =>['name'=>sprintf('%s(%s)', __('정산후환불'), __('합계')),'type'=>'price'],
                    'refundGoods' =>['name'=>sprintf('%s(%s)', __('정산후환불'), __('상품')),'type'=>'price'],
                    'refundDelivery' =>['name'=>sprintf('%s(%s)', __('정산후환불'), __('배송비')),'type'=>'price'],
                    'step1' =>['name'=>sprintf('%s(%s)', __('정산요청건'), __('합계'))],
                    'step10' =>['name'=>__('정산확정건')],
                    'step30' =>['name'=>__('지급완료건')],

                ];
                break;
            case 'scm_adjust_list':
                $setData = [
                    'sno' =>['name'=>__('번호')],
                    'regDt' =>['name'=>__('처리일자')],
                    'scmAdjustDt' =>['name'=>__('요청일자')],
                    'companyNm' =>['name'=>__('공급사명')],
                    'scmCode' =>['name'=>__('공급사코드')],
                    'scmAdjustType' =>['name'=>__('정산타입')],
                    'orderName' =>['name'=>__('주문자명')],
                    'memId' =>['name'=>__('주문자아이디')],
                    'scmAdjustKind' =>['name'=>__('요청타입')],
                    'goodsNm' =>['name'=>__('주문상품')],
                    'goodsCnt' =>['name'=>__('수량')],
                    'orderStatusStr' =>['name'=>__('주문상태')],
                    'price' =>['name'=>__('판매(배송)금액'),'type'=>'price'],
                    'commission' =>['name'=>__('수수료')],
                    'adjustCommission' =>['name'=>__('수수료액(VAT포함)')],
                    'adjustPrice' =>['name'=>__('정산금액'),'type'=>'price'],
                    'managerNm' =>['name'=>__('정산요청(이름)')],
                    'managerId' =>['name'=>__('정산요청(아이디)')],
                    'scmAdjustState' =>['name'=>__('정산상태')],
                    'scmAdjustCode' =>['name'=>__('정산요청번호')],
                    'orderNo' =>['name'=>__('주문번호')],
                    'orderGoodsSno' =>['name'=>__('상품주문번호')],
                    'invoiceCompanySno' =>['name'=>__('배송 업체 번호')],
                    'invoiceNo' =>['name'=>__('송장 번호')],
                    'receiverName' =>['name'=>__('수령자명')],
                    'goodsPrice' =>['name'=>__('상품 금액'),'type'=>'price'],
                    'optionInfo' =>['name'=>__('옵션정보')],
                    'optionTextInfo' =>['name'=>__('텍스트옵션정보')],
                    'optionPrice' =>['name'=>__('옵션 금액'),'type'=>'price'],
                    'optionTextPrice' =>['name'=>__('텍스트옵션 금액'),'type'=>'price'],
                ];
                break;
            case 'scm_adjust_after_order':
            case 'scm_adjust_order':
                $setData = [
                    'sno' =>['name'=>__('번호')],
                    'regDt' =>['name'=>__('주문일')],
                    'companyNm' =>['name'=>__('공급사명')],
                    'managerId' =>['name'=>__('공급사아이디')],
                    'orderNo' =>['name'=>__('주문번호')],
                    'orderName' =>['name'=>__('주문자명')],
                    'memId' =>['name'=>__('주문자아이디')],
                    'goodsNm' =>['name'=>__('주문상품')],
                    'goodsPrice' =>['name'=>__('상품금액'),'type'=>'price'],
                    'goodsCnt' =>['name'=>__('수량')],
                    'orderStatusStr' =>['name'=>__('주문상태')],
                    'totalPrice' =>['name'=>__('판매(배송)금액') ,'type'=>'price'],
                    'commission' =>['name'=>__('수수료')],
                    'goodsAdjustCommission' =>['name'=>__('수수료액(VAT포함)'),'type'=>'price'],
                    'totalAdjustPrice' =>['name'=>__('정산금액'),'type'=>'price']
                ];
                break;
            case 'scm_adjust_after_delivery':
            case 'scm_adjust_delivery':
                $setData = [
                    'sno' =>['name'=>__('번호')],
                    'regDt' =>['name'=>__('주문일')],
                    'companyNm' =>['name'=>__('공급사명')],
                    'managerId' =>['name'=>__('공급사아이디')],
                    'orderNo' =>['name'=>__('주문번호')],
                    'orderName' =>['name'=>__('주문자명')],
                    'memId' =>['name'=>__('주문자아이디')],
                    'deliveryCharge' =>['name'=>__('배송비'),'type'=>'price'],
                    'orderStatusStr' =>['name'=>__('주문상태')],
                    'commission' =>['name'=>__('수수료')],
                    'deliveryAdjustCommission' =>['name'=>__('수수료액(VAT포함)'),'type'=>'price'],
                    'deliveryAdjustPrice' =>['name'=>__('정산금액'),'type'=>'price']
                ];
                break;
        }
        //@formatter:on

        return $setData;
    }

    public function setExcelFormBoard($location)
    {
        $setData = [];
        //@formatter:off
        switch ($location) {
            case 'board':
                $setData = [
                    'sno' =>['name'=>__('번호')],
                    'groupNo' =>['name'=>__('게시글그룹 번호'),'scmDisplayFl'=>'n'],
                    'groupThread' =>['name'=>__('답변코드'),'scmDisplayFl'=>'n'],
                    'groupNm' =>['name'=>__('회원등급'),'scmDisplayFl'=>'n'],
                    'memNo' =>['name'=>__('회원번호'),'scmDisplayFl'=>'n'],
                    'writerId' =>['name'=>__('작성자아이디')],
                    'writerNm' =>['name'=>__('작성자명')],
                    'writerEmail' =>['name'=>__('작성자이메일')],
                    'writerHp' =>['name'=>__('작성자홈페이지')],
                    'writerPw' =>['name'=>__('작성자비밀번호(암호화)'),'scmDisplayFl'=>'n'],
                    'writerIp' =>['name'=>__('작성자IP'),'scmDisplayFl'=>'n'],
                    'subject' =>['name'=>__('제목')],
                    'subSubject' =>['name'=>__('부가설명'),'scmDisplayFl'=>'n'],
                    'contents' =>['name'=>__('내용')],
                    'urlLink' =>['name'=>'urlLink'],
                    'uploadFileNm' =>['name'=>__('업로드파일명')],
                    'saveFileNm' =>['name'=>__('저장파일명')],
                    'parentPw' =>['name'=>__('원글비밀번호(암호화)'),'scmDisplayFl'=>'n'],
                    'parentSno' =>['name'=>__('부모글번호')],
                    'isNotice' =>['name'=>__('공지글여부')],
                    'isSecret' =>['name'=>__('비밀글여부')],
                    'hit' =>['name'=>__('조회수')],
                    'memoCnt' =>['name'=>__('댓글수')],
                    'category' =>['name'=>__('말머리이름')],
                    'writerMobile' =>['name'=>__('작성자모바일번호'),'scmDisplayFl'=>'n'],
                    'goodsNo' =>['name'=>__('상품번호')],
                    'goodsPt' =>['name'=>__('상품평점')],
                    'orderNo' =>['name'=>__('주문번호')],
                    'mileage' =>['name'=>__('발급마일리지'),'scmDisplayFl'=>'n'],
                    'mileageReason' =>['name'=>__('발급사유'),'scmDisplayFl'=>'n'],
                    'replyStatus' =>['name'=>__('답변상태')],
                    'recommend' =>['name'=>__('답변글수')],
                    'isDelete' =>['name'=>__('삭제여부')],
                    'eventStart' =>['name'=>__('이벤트시작일'),'scmDisplayFl'=>'n'],
                    'eventEnd' =>['name'=>__('이벤트종료일'),'scmDisplayFl'=>'n'],
                    'answerSubject' =>['name'=>__('답변제목')],
                    'answerContents' =>['name'=>__('답변내용')],
                    'answerManagerNo' =>['name'=>__('답변관리자번호'),'scmDisplayFl'=>'n'],
                    'answerModDt' =>['name'=>__('답변수정일')],
                    'bdUploadStorage' =>['name'=>__('이미지업로드저장위치'),'scmDisplayFl'=>'n'],
                    'bdUploadPath' =>['name'=>__('이미지업로드저장경로'),'scmDisplayFl'=>'n'],
                    'bdUploadThumbPath' =>['name'=>__('섬네일이미지업로드저장경로'),'scmDisplayFl'=>'n'],
                    'isMobile' =>['name'=>__('모바일작성여부')],
                    'regDt' =>['name'=>__('등록일')],
                    'modDt' =>['name'=>__('수정일')]
                ];
                break;
            case 'memo':
                $setData = [
                    'sno' =>['name'=>__('번호')],
                    'bdId' =>['name'=>__('게시판아이디')],
                    'bdSno' =>['name'=>__('게시글번호')],
                    'groupNm' =>['name'=>__('회원등급'),'scmDisplayFl'=>'n'],
                    'writerId' =>['name'=>__('작성자아이디')],
                    'writerNm' =>['name'=>__('작성자명')],
                    'writerNick' =>['name'=>__('작성자닉네임')],
                    'memo' =>['name'=>__('댓글내용')],
                    'writerPw' =>['name'=>__('댓글비밀번호'),'scmDisplayFl'=>'n'],
                    'memNo' =>['name'=>__('회원번호'),'scmDisplayFl'=>'n'],
                    'mileage' =>['name'=>__('마일리지'),'scmDisplayFl'=>'n'],
                    'mileageReason' =>['name'=>__('마일리지발급사유'),'scmDisplayFl'=>'n'],
                    'regDt' =>['name'=>__('등록일')],
                    'modDt' =>['name'=>__('수정일')],
                    'groupNo' =>['name'=>__('정렬번호'),'scmDisplayFl'=>'n'],
                    'groupThread' =>['name'=>__('답변코드')],
                    'isSecretReply' =>['name'=>__('비밀댓글여부')]
                ];
                break;
        }
        //@formatter:on

        return $setData;
    }

    public function setExcelFormStatistics($location)
    {
        switch($location) {
            case 'visit_ip_list':
                $setData = [
                    'visitIP' => ['name' => __('IP')],
                    'visitOS' => ['name' => __('운영체제')],
                    'visitBrowser' => ['name' => __('브라우저')],
                    'visitPageView' => ['name' => __('전체 페이지뷰')],
                ];
                break;
            case 'visit_ip_detail':
                $setData = [
                    'visitOS' => ['name' => __('운영체제')],
                    'visitBrowser' => ['name' => __('브라우저')],
                    'regDt' => ['name' => __('접속시간')],
                    'visitPageView' => ['name' => __('페이지뷰')],
                    'visitReferer' => ['name' => __('방문경로')],
                ];
                break;
            default:
                break;
        }
        return $setData;
    }

    function setExcelFormOrderDraft()
    {
        //주문서
        $setBaseData = [
            'orderNo' =>['name'=>__('주문 번호'),'orderFl'=>'y'],
            'apiOrderNo' =>['name'=>__('외부채널주문번호'),'orderFl'=>'y'],
            'memNm' =>['name'=>__('회원명'),'orderFl'=>'y','scmDisplayFl'=>'n'],
            'memNo' =>['name'=>__('회원 아이디'),'orderFl'=>'y','scmDisplayFl'=>'n'],
            'groupNm' =>['name'=>__('회원등급명'),'orderFl'=>'y','scmDisplayFl'=>'n'],
            'orderGoodsNm' =>['name'=>__('주문 상품명'),'orderFl'=>'y'],
            'orderGoodsCnt' =>['name'=>__('주문 품목 개수'),'orderFl'=>'y'],
            'orderChannelFl' =>['name'=>__('주문 채널'),'orderFl'=>'y'],
            'totalSettlePrice'=>['name'=>__('총 결제 금액'),'type'=>'price','orderFl'=>'y','scmDisplayFl'=>'y'],
            'totalGoodsPrice'=>['name'=>__('총 품목 금액'),'type'=>'price','orderFl'=>'y'],
            'totalDeliveryCharge'=>['name'=>__('총 배송 금액'),'type'=>'price','orderFl'=>'y'],
            'totalGift'=>['name'=>__('총 사은품 정보'),'orderFl'=>'y'],
            'paymentDt' =>['name'=>__('입금일자'),'scmDisplayFl'=>'n'],
            'orderDt' =>['name'=>__('주문일자'),'orderFl'=>'y'],
            'orderName' =>['name'=>__('주문자 이름'),'orderFl'=>'y'],
            'orderEmail' =>['name'=>__('주문자 e-mail'),'orderFl'=>'y'],
            'orderPhone' =>['name'=>__('주문자 전화번호'),'orderFl'=>'y'],
            'orderCellPhone' =>['name'=>__('주문자 핸드폰 번호'),'orderFl'=>'y'],
            'receiverName' =>['name'=>__('수취인 이름'),'orderFl'=>'y'],
            'receiverPhone' =>['name'=>__('수취인 전화번호'),'orderFl'=>'y'],
            'receiverCellPhone' =>['name'=>__('수취인 핸드폰 번호'),'orderFl'=>'y'],
            'receiverZipcode' =>['name'=>__('수취인 구 우편번호 (6자리)'),'orderFl'=>'y'],
            'receiverZonecode' =>['name'=>__('수취인 우편번호'),'orderFl'=>'y'],
            'receiverAddress' =>['name'=>__('수취인 주소'),'orderFl'=>'y'],
            'receiverAddressSub' =>['name'=>__('수취인 나머지 주소'),'orderFl'=>'y'],
            'receiverAddressTotal' =>['name'=>__('수취인 전체주소'),'orderFl'=>'y'],
            'orderMemo' =>['name'=>__('주문시 남기는 글'),'orderFl'=>'y'],

            'orderDeliverySno' =>['name'=>__('배송번호'),'scmDisplayFl'=>'n'],
            'goodsDeliveryCollectFl' =>['name'=>__('배송비 구분'),'scmDisplayFl'=>'n'],
            'scmNo' =>['name'=>__('공급사코드'),'scmDisplayFl'=>'n'],
            'scmNm' =>['name'=>__('공급사명'),'scmDisplayFl'=>'n'],
            'orderGoodsSno' =>['name'=>__('상품주문번호')],
            'apiOrderGoodsNo' =>['name'=>__('외부채널품목번호')],
            'orderCd' =>['name'=>__('주문코드(순서)')],
            'orderStatus' =>['name'=>__('주문상태')],
            'goodsNo' =>['name'=>__('상품코드')],
            'goodsCd' =>['name'=>__('자체상품코드')],
            'goodsModelNo' =>['name'=>__('모델명')],
            'goodsType' =>['name'=>__('상품종류')],
            'goodsNm' =>['name'=>__('상품명')],
            'optionInfo' =>['name'=>__('옵션정보')],
            'optionCode' =>['name'=>__('자체옵션코드')],
            'goodsCnt' =>['name'=>__('상품수량')],
            'goodsWeight' =>['name'=>__('상품무게')],
            'cateCd' =>['name'=>__('카테고리명')],
            'brandCd' =>['name'=>__('브랜드명')],
            'makerNm' =>['name'=>__('제조사')],
            'originNm' =>['name'=>__('원산지')],
            'optionTextInfo' =>['name'=>__('텍스트옵션정보')],

            'goodsPrice' =>['name'=>__('판매가'),'type'=>'price'],
            'fixedPrice' =>['name'=>__('정가'),'type'=>'price'],
            'costPrice' =>['name'=>__('매입가'),'type'=>'price'],
            'commission' =>['name'=>__('수수료율')],
            'optionPrice' =>['name'=>__('옵션 금액'),'type'=>'price'],
            'optionCostPrice' =>['name'=>__('옵션 매입가'),'type'=>'price'],
            'optionTextPrice' =>['name'=>__('텍스트옵션 금액'),'type'=>'price'],
            'ogi.presentSno' =>['name'=>__('사은품 지급 제목')],
            'ogi.giftNo' =>['name'=>__('사은품 정보')],
            'invoiceCompanySno' =>['name'=>__('배송 업체 번호')],
            'invoiceNo' =>['name'=>__('송장 번호')],
            'addField' =>['name'=>__('추가 정보')],
            'hscode' => ['name'=>__('HS코드')],
            'multiShippingOrder' => ['name'=>__('배송지')],
            'multiShippingPrice' => ['name'=>__('배송지별 배송비'),'type'=>'price'],
        ];

        //@formatter:on
        if (gd_is_plus_shop(PLUSSHOP_CODE_PURCHASE) === true && gd_is_provider() === false) {
            $setBaseData = array_merge($setBaseData, ['purchaseNm' => ['name' => __('매입처')]]);
        }

        $setGlobalData = [
            'mallSno' => ['name'=>__('상점구분'),'orderFl'=>'y' ],
            'orderGoodsNmStandard' => ['name'=>__('주문 상품명(해외상점)'),'orderFl'=>'y'],
            'global_totalSettlePrice' => ['name'=>__('총 결제금액(해외상점)'),'orderFl'=>'y','type'=>'price',],
            'global_totalGoodsPrice' => ['name'=>__('총 품목금액(해외상점)'),'orderFl'=>'y','type'=>'price',],
            'global_totalDeliveryCharge' => ['name'=>__('총 배송금액(해외상점)'),'orderFl'=>'y','type'=>'price',],
//            'overseasSettlePrice' => ['name'=>__('PG승인 금액'),'orderFl'=>'y','type'=>'price',],
//            'global_totalGoodsDcPrice' => ['name'=>__('총 상품할인 금액(해외상점)'),'type'=>'price',],
            'goodsNmStandard' => ['name'=>__('상품명(해외상점)')],
        ];

        $setData = array_merge($setBaseData, $setGlobalData);
        return $setData;
    }

    public function setExcelFormPlusreview($location)
    {
        $setData = [];
        //@formatter:off
        switch ($location) {
            case 'plusreview_board':
                if (GodoUtils::isPlusShop(PLUSSHOP_CODE_REVIEW) && Manager::isProvider() === false) {
                    $setData = [
                        'sno' => ['name' => __('번호')],
                        'writerId' => ['name' => __('작성자아이디')],
                        'writerNm' => ['name' => __('작성자명')],
                        'writerNick' => ['name' => __('작성자닉네임')],
                        'cellPhone' => ['name' => __('작성자휴대폰번호')],
                        'writerPw' => ['name' => __('작성자비밀번호(암호화)'), 'scmDisplayFl' => 'n'],
                        'writerIp' => ['name' => __('작성자IP'), 'scmDisplayFl' => 'n'],
                        'contents' => ['name' => __('내용')],
                        'reviewTypeText' => ['name' => __('속성')],
                        'uploadFileNm' => ['name' => __('업로드파일명')],
                        'saveFileNm' => ['name' => __('저장파일명')],
                        'hit' => ['name' => __('조회수')],
                        'memoCnt' => ['name' => __('댓글수')],
                        'recommend' => ['name' => __('추천수')],
                        'goodsNo' => ['name' => __('상품번호')],
                        'goodsPt' => ['name' => __('상품평점')],
                        'applyFl' => ['name' => __('승인여부')],
                        'addFormData' => ['name' => __('추가정보 (1~10)')],
                        'orderNo' => ['name' => __('주문번호')],
                        'orderPrice' => ['name' => __('주문 실 결제금액')],
                        'buyGoodsRegDt' => ['name' => __('주문일')],
                        'orderStatus' => ['name' => __('처리상태')],
                        'mileageGiveFl' => ['name' => __('마일리지 지급 여부')],
                        'mileage' => ['name' => __('발급 마일리지')],
                        'mileageDt' => ['name' => __('마일리지 지급일')],
                        'deleteScheduleDt' => ['name' => __('마일리지 소멸예정일')],
                        'mileageReason' => ['name' => __('발급사유')],
                        'uploadStorage' => ['name' => __('이미지업로드저장위치'), 'scmDisplayFl' => 'n'],
                        'uploadPath' => ['name' => __('이미지업로드저장경로'), 'scmDisplayFl' => 'n'],
                        'uploadThumbPath' => ['name' => __('썸네일이미지업로드저장경로'), 'scmDisplayFl' => 'n'],
                        'isMobile' => ['name' => __('모바일 작성여부')],
                        'regDt' => ['name' => __('등록일')],
                        'modDt' => ['name' => __('수정일')]
                    ];
                }
                break;
            case 'plusreview_memo':
                if (GodoUtils::isPlusShop(PLUSSHOP_CODE_REVIEW) && Manager::isProvider() === false) {
                    $setData = [
                        'sno' => ['name' => __('번호')],
                        'articleSno' => ['name' => __('부모글 번호')],
                        'writerId' => ['name' => __('작성자아이디')],
                        'writerNm' => ['name' => __('작성자명')],
                        'writerNick' => ['name' => __('작성자닉네임')],
                        'cellPhone' => ['name' => __('작성자휴대폰번호')],
                        'writerPw' => ['name' => __('작성자비밀번호(암호화)'), 'scmDisplayFl' => 'n'],
                        'writerIp' => ['name' => __('작성자IP'), 'scmDisplayFl' => 'n'],
                        'memo' => ['name' => __('내용')],
                        'regDt' => ['name' => __('등록일')],
                        'modDt' => ['name' => __('수정일')]
                    ];
                }
                break;
        }
        //@formatter:on

        return $setData;
    }

    /**
     * 엑셀
     *
     */
    public function getInfoExcelForm($sno = null, $goodsField = null, $arrBind = null, $dataArray = false)
    {
        if (empty($arrBind) === true) {
            $arrBind = [];
        }

        // 상품 코드 정보가 있는경우
        if ($sno) {
            $arrWhere = [];
            if ($this->db->strWhere) $arrWhere[] = $this->db->strWhere;

            // 상품 코드가 배열인 경우
            if (is_array($sno) === true) {
                $arrWhere[] = "sno IN ('" . implode("','", $sno) . "')";
                // 상품 코드가 하나인경우
            } else {
                $arrWhere[] = 'sno = ?';
                $this->db->bind_param_push($arrBind, 'i', $sno);
            }

            $this->db->strWhere = implode(' AND ', $arrWhere);
        }

        // 사용할 필드가 있는 경우
        if ($goodsField) {
            if ($this->db->strField) {
                $this->db->strField = $goodsField . ', ' . $this->db->strField;
            } else {
                $this->db->strField = $goodsField;
            }
        }

        // 쿼리문 생성
        $query = $this->db->query_complete();
        $strSQL = 'SELECT ' . array_shift($query) . ' FROM ' . DB_EXCEL_FORM . implode(' ', $query);
        $getData = $this->db->query_fetch($strSQL, $arrBind);

        if (count($getData) == 1 && $dataArray === false) {
            return gd_htmlspecialchars_stripslashes($getData[0]);
        }

        return gd_htmlspecialchars_stripslashes($getData);
    }


    /**
     * getDataExcelForm
     *
     * @param null $sno
     *
     * @return mixed
     * @throws AlertBackException
     */
    public function getDataExcelForm($sno = null)
    {
        $request = \App::getInstance('request');
        $session = \App::getInstance('session');
        $getValue = $request->get()->toArray();

        //등록인 경우
        if (empty($sno) === true) {
            $data['mode'] = 'register';

            DBTableField::setDefaultData('tableExcelForm', $data);

        } else {
            // 추가상품 정보
            $data = $this->getInfoExcelForm($sno);
            $data['mode'] = 'modify';
            $data['excelField'] = explode(STR_DIVISION, $data['excelField']);

            if ($session->get('manager.isProvider')) {
                if ($data['scmNo'] != $session->get('manager.scmNo')) {
                    throw new AlertBackException(__("타 공급사의 자료는 열람하실 수 없습니다."));
                }
            }

            // 기본값 설정
            DBTableField::setDefaultData('tableExcelForm', $data);
        }

        $getData['data'] = $data;

        return $getData;
    }

    /**
     * saveInfoExcelForm
     *
     * @param $arrData
     *
     * @throws \Exception
     */
    public function saveInfoExcelForm($arrData)
    {
        // 다운로드 양식명
        if (Validator::required(gd_isset($arrData['title'])) === false) {
            throw new \Exception(sprintf(__('%s은(는) 필수 항목 입니다.'), __('다운르도 양식명')), 500);
        }
        $session = \App::getInstance('session');
        $arrData['excelField'] = implode(STR_DIVISION, $arrData['excelField']);
        $arrData['managerNo'] = $session->get('manager.sno');
        $arrData['scmNo'] = $session->get('manager.scmNo');

        if (empty($arrData['useFields']) === false && is_array($arrData['useFields']) === true) {
            $arrData['excelField'] .= STR_DIVISION . implode(STR_DIVISION, $arrData['useFields']);
        }

        if ($arrData['mode'] == 'modify') {
            $arrBind = $this->db->get_binding(DBTableField::tableExcelForm(), $arrData, 'update');
            $this->db->bind_param_push($arrBind['bind'], 's', $arrData['sno']);
            $this->db->set_update_db(DB_EXCEL_FORM, $arrBind['param'], 'sno = ?', $arrBind['bind']);
        } else {
            $arrBind = $this->db->get_binding(DBTableField::tableExcelForm(), $arrData, 'insert');
            $this->db->set_insert_db(DB_EXCEL_FORM, $arrBind['param'], $arrBind['bind'], 'y');
        }

        unset($arrBind);
    }

    /**
     * getExcelListForAdmin
     *
     * @return mixed
     */
    public function getExcelFormListForAdmin($orderDraftFl = 'n')
    {
        $request = \App::getInstance('request');
        $session = \App::getInstance('session');
        $getValue = $request->get()->toArray();

        // --- 검색 설정
        $this->_setSearch($getValue);

        // --- 정렬 설정
        $sort = gd_isset($getValue['sort']);
        if (empty($sort)) {
            $sort = 'regDt desc';
        }

        // --- 페이지 기본설정
        gd_isset($getValue['page'], 1);
        gd_isset($getValue['pageNum'], 10);

        $page = \App::load('\\Component\\Page\\Page', $getValue['page']);
        $page->page['list'] = $getValue['pageNum']; // 페이지당 리스트 수
        $page->recode['amount'] = $this->db->table_status(DB_EXCEL_FORM, 'Rows'); // 전체 레코드 수
        $page->setPage();
        $page->setUrl($request->getQueryString());

        $strSQL = ' SELECT COUNT(sno) AS cnt FROM ' . DB_EXCEL_FORM . ' WHERE scmNo = \'' . $session->get('manager.scmNo') . '\'';
        if ($getValue['displayFl']) $strSQL .= " AND displayFl = '" . $getValue['displayFl'] . "'";
        if ($orderDraftFl === 'y') {
            $strSQL .= " AND menu = 'orderDraft'";
        } else {
            $strSQL .= " AND menu != 'orderDraft'";
            $this->arrWhere[] = 'ef.menu != \'orderDraft\'';
        }
        $res = $this->db->query_fetch($strSQL, null, false);
        $page->recode['amount'] = $res['cnt']; // 전체 레코드 수
        $page->setPage();
        $page->setUrl($request->getQueryString());

        $join[] = ' LEFT JOIN ' . DB_MANAGER . ' m ON ef.managerNo = m.sno ';

        // 현 페이지 결과
        $this->db->strField = "ef.*,m.managerNm,m.managerId,m.isDelete";
        $this->db->strJoin = implode('', $join);
        $this->db->strWhere = implode(' AND ', gd_isset($this->arrWhere));
        $this->db->strOrder = $sort;
        $this->db->strLimit = $page->recode['start'] . ',' . $getValue['pageNum'];

        $query = $this->db->query_complete();
        $strSQL = 'SELECT ' . array_shift($query) . ' FROM ' . DB_EXCEL_FORM . ' as ef' . implode(' ', $query);
        $data = $this->db->query_fetch($strSQL, $this->arrBind);
        Manager::displayListData($data);

        $this->db->strField = "count(*) as cnt";
        $this->db->strJoin = implode('', $join);
        $this->db->strWhere = implode(' AND ', gd_isset($this->arrWhere));

        $query = $this->db->query_complete();
        $strSQL = 'SELECT ' . array_shift($query) . ' FROM ' . DB_EXCEL_FORM . ' as ef' . implode(' ', $query);
        $total = $this->db->query_fetch($strSQL, $this->arrBind, false)['cnt'];

        // 검색 레코드 수
        $page->recode['total'] = $total;
        $page->setPage();

        // 각 데이터 배열화
        $getData['data'] = gd_htmlspecialchars_stripslashes(gd_isset($data));
        $getData['sort'] = $sort;
        $getData['search'] = gd_htmlspecialchars($this->search);
        $getData['checked'] = $this->checked;
        $getData['selected'] = $this->selected;

        return $getData;
    }

    /**
     * getExcelListForAdmin
     *
     * @return mixed
     */
    public function getExcelFormList()
    {
        $request = \App::getInstance('request');
        $getValue = $request->get()->toArray();

        // --- 검색 설정
        $this->_setSearch($getValue);

        // --- 정렬 설정
        $sort = gd_isset($getValue['sort']);
        if (empty($sort)) {
            $sort = 'regDt desc';
        }

        // 현 페이지 결과
        $this->db->strField = " ef.*";
        $this->db->strWhere = implode(' AND ', gd_isset($this->arrWhere));
        $this->db->strOrder = $sort;

        $query = $this->db->query_complete();
        $strSQL = 'SELECT ' . array_shift($query) . ' FROM ' . DB_EXCEL_FORM . ' as ef' . implode(' ', $query);
        $data = $this->db->query_fetch($strSQL, $this->arrBind);

        // 각 데이터 배열화
        $getData = gd_htmlspecialchars_stripslashes(gd_isset($data));
        foreach ($getData as $index => $data) {
            $getData[$index]['countPersonalField'] = $this->countPersonalField($data['excelField']);
        }

        return $getData;
    }


    /**
     * _setSearch
     *
     * @param     $searchData
     * @param int $searchPeriod
     */
    public function _setSearch($searchData)
    {
        // 검색을 위한 bind 정보
        $fieldType = DBTableField::getFieldTypes('tableExcelForm');


        $this->search['combineSearch'] = [
            'all'       => __('=통합검색='),
            'title'     => __('다운로드 양식명'),
            'managerNm' => __('등록자'),
        ];

        //검색설정
        $this->search['sortList'] = [
            'regDt desc' => sprintf('%s ↑', __('등록일')),
            'regDt asc'  => sprintf('%s ↓', __('등록일')),
            'title desc' => sprintf('%s ↑', __('다운로드양식명')),
            'title asc'  => sprintf('%s ↓', __('다운로드양식명')),
        ];

        // --- 검색 설정
        $this->search['sort'] = gd_isset($searchData['sort'], 'regDt desc');
        $this->search['key'] = gd_isset($searchData['key'], 'all');
        $this->search['keyword'] = gd_isset($searchData['keyword']);
        $this->search['menu'] = gd_isset($searchData['menu']);
        $this->search['location'] = gd_isset($searchData['location']);
        $this->search['displayFl'] = gd_isset($searchData['displayFl']);

        $this->arrWhere[] = "ef.scmNo = ?";
        $this->db->bind_param_push($this->arrBind, 'i', Session::get('manager.scmNo'));


        if ($this->search['key'] && $this->search['keyword']) {
            if ($this->search['key'] == 'all') {
                $tmpWhere = [
                    'title',
                    'managerNm',
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

        // 메누
        if ($this->search['displayFl']) {
            $this->arrWhere[] = 'displayFl = ?';
            $this->db->bind_param_push($this->arrBind, $fieldType['displayFl'], $this->search['displayFl']);
        }

        // 메누
        if ($this->search['menu']) {
            if ($this->search['menu'] == 'board') {
                $this->arrWhere[] = 'menu in (?, ?)';
                $this->db->bind_param_push($this->arrBind, $fieldType['menu'], $this->search['menu']);
                $this->db->bind_param_push($this->arrBind, $fieldType['menu'], 'plusreview');
            } else {
                $this->arrWhere[] = 'menu = ?';
                $this->db->bind_param_push($this->arrBind, $fieldType['menu'], $this->search['menu']);
            }
        }

        // 카테고리
        if ($this->search['location']) {
            $this->arrWhere[] = 'location = ?';
            $this->db->bind_param_push($this->arrBind, $fieldType['location'], $this->search['location']);
        }

        if (empty($this->arrBind)) {
            $this->arrBind = null;
        }
    }

    /**
     * setDeleteExcelForm
     *
     * @param $sno
     */
    public function setDeleteExcelForm($sno)
    {
        $strWhere = "sno IN ('" . implode("','", $sno) . "')";
        $this->db->set_delete_db(DB_EXCEL_FORM, $strWhere);
    }

    /**
     * saveExcelAddFieldsByScm
     * 공급사(본사 포함) 별 추가항목 저장하기
     */
    public function saveExcelAddFieldsByScm($scmNo, $addFields) {
        $strSQL = "SELECT addInfo FROM " . DB_SCM_MANAGE . " WHERE scmNo = ?;" ;
        $addInfo = $this->db->query_fetch($strSQL, ['i', $scmNo]);
        $saveAddInfo = [];
        if (empty($addInfo[0]['addInfo']) === false) {
            $saveAddInfo = json_decode($addInfo[0]['addInfo'], true);
        }
        $saveAddInfo['orderDraft']['addFields'] = $addFields;
        $updateParam   = "addInfo = ?";
        $arrBind    = [];
        $this->db->bind_param_push($arrBind, 's', json_encode($saveAddInfo));
        $this->db->bind_param_push($arrBind, 'i', $scmNo);
        $this->db->set_update_db(DB_SCM_MANAGE, $updateParam, 'scmNo = ?', $arrBind);
    }

    /**
     * getScmAddFields
     * 공급사(본사 포함) 별 추가항목 가져오기.
     */
    public function getExcelAddFieldsByScm($scmNo) {
        $strSQL = "SELECT addInfo FROM " . DB_SCM_MANAGE . " WHERE scmNo = ?;" ;
        $getData = $this->db->query_fetch($strSQL, ['i', $scmNo]);
        if (empty($getData[0]['addInfo']) === false) {
            $addInfo = json_decode($getData[0]['addInfo'], true);
            return $addInfo['orderDraft']['addFields'];
        }
        return [];
    }

}
