<?php
/**
 * Coupon Class
 *
 * @author    sj, artherot
 * @version   1.0
 * @since     1.0
 * @copyright ⓒ 2016, NHN godo: Corp.
 */

namespace Bundle\Component\CartRemind;

use Component\Coupon\Coupon;
use Component\Coupon\CouponAdmin;
use Component\Database\DBTableField;
use Component\Design\ReplaceCode;
use Component\Member\Group\Util as GroupUtil;
use Component\Member\Manager;
use Component\Sms\Sms;
use Component\Sms\SmsAdmin;
use Component\Sms\SmsUtil;
use Component\Validator\Validator;
use Framework\Debug\Exception;
use Framework\Utility\ArrayUtils;
use Framework\Utility\DateTimeUtils;
use Framework\Utility\GodoUtils;
use Session;

class CartRemind
{
    // 디비 접속
    /** @var \Framework\Database\DBTool $db */
    protected $db;

    /**
     * @var array arrBind
     */
    protected $arrBind = [];
    protected $arrWhere = [];
    protected $checked = [];
    protected $selected = [];
    protected $search = [];

    public $period = null;

    /**
     * 생성자
     */
    public function __construct()
    {
        if (!is_object($this->db)) {
            $this->db = \App::load('DB');
        }

        $this->period = [
            "manual" => [
            1 => "최근 1~3일 전",
            2 => "최근 3~5일 전",
            3 => "최근 5~7일 전",
            4 => "최근 3~7일 전",
            5 => "직접선택",
        ],
            "auto"   => [
                1 => "최근 1일 전",
                2 => "최근 2일 전",
                3 => "최근 3일 전",
                4 => "최근 4일 전",
                5 => "최근 5일 전",
                6 => "최근 6일 전",
                7 => "최근 7일 전",
            ]
        ];
    }

    /**
     * 장바구니 알림 정보 출력
     *
     * 완성된 쿼리문은 $db->strField , $db->strJoin , $db->strWhere , $db->strGroup , $db->strOrder , $db->strLimit 멤버 변수를
     * 이용할수 있습니다.
     *
     * @param string      $cartRemindNo    장바구니 알림 고유 번호 (기본 null)
     * @param string      $cartRemindField 출력할 필드명 (기본 null)
     * @param array       $arrBind         bind 처리 배열 (기본 null)
     * @param bool|string $dataArray       return 값을 배열처리 (기본값 false)
     *
     * @return array 쿠폰 정보
     *
     * @author su
     */
    public function getCartRemindInfo($cartRemindNo = null, $cartRemindField = null, $arrBind = null, $dataArray = false)
    {
        if (is_null($arrBind)) {
            // $arrBind = array();
        }
        if ($cartRemindNo) {
            if ($this->db->strWhere) {
                $this->db->strWhere = " cr.cartRemindNo = ? AND " . $this->db->strWhere;
            } else {
                $this->db->strWhere = " cr.cartRemindNo = ?";
            }
            $this->db->bind_param_push($arrBind, 'i', $cartRemindNo);
        }
        if ($cartRemindField) {
            if ($this->db->strField) {
                $this->db->strField = $cartRemindField . ', ' . $this->db->strField;
            } else {
                $this->db->strField = $cartRemindField;
            }
        }
        $query = $this->db->query_complete();
        $strSQL = 'SELECT ' . array_shift($query) . ' FROM ' . DB_CART_REMIND . ' as cr ' . implode(' ', $query);
        $getData = $this->db->query_fetch($strSQL, $arrBind);

        if (count($getData) == 1 && $dataArray === false) {
            return gd_htmlspecialchars_stripslashes($getData[0]);
        }

        return gd_htmlspecialchars_stripslashes($getData);
    }

    /**
     * 장바구니 알림 리스트
     *
     * @param array $searchData 검색조건
     *
     * @return array $cartRemindListData 장바구니 알림 데이터
     *
     * @author su
     */
    public function getCartRemindList($searchData)
    {
        $this->setCartRemindSearch($searchData);

        $sort['fieldName'] = gd_isset($searchData['sort']['name']);
        $sort['sortMode'] = gd_isset($searchData['sort']['mode']);
        if (empty($sort['fieldName']) || empty($sort['sortMode'])) {
            $sort['fieldName'] = 'cr.regDt';
            $sort['sortMode'] = 'desc';
        } else {
            $sort['fieldName'] = 'cr.' . $sort['fieldName'];
        }

        gd_isset($searchData['page'], 1);
        gd_isset($searchData['pageNum'], 10);

        $page = \App::load('\\Component\\Page\\Page', $searchData['page']);
        $page->page['list'] = $searchData['pageNum'];
        $page->recode['amount'] = $this->db->getCount(DB_CART_REMIND, 'cartRemindNo', ' WHERE cartRemindNo > 0', 'row'); // 전체 레코드 수
        $page->setPage();
        $page->setUrl(\Request::getQueryString());

        // 검색 조건
        $this->db->strField = "cr.*, m.isDelete";
        $this->db->strWhere = implode(' AND ', gd_isset($this->arrWhere));
        $this->db->strOrder = $sort['fieldName'] . ' ' . $sort['sortMode'];
        $this->db->strLimit = $page->recode['start'] . ',' . $searchData['pageNum'];

        $query = $this->db->query_complete();
        $strSQL = 'SELECT ' . array_shift($query) . ' FROM ' . DB_CART_REMIND . ' as cr LEFT OUTER JOIN '.DB_MANAGER.' as m ON cr.managerNo = m.sno'. implode(' ', $query);
        $data = $this->db->query_fetch($strSQL, $this->arrBind);
        Manager::displayListData($data);

        unset($query['left'], $query['order'], $query['limit']);
        $page->recode['total'] = $this->db->query_count($query, DB_CART_REMIND . ' as cr', $this->arrBind);
        $page->setPage();
        unset($this->arrBind);
        unset($this->arrWhere);

        $cartRemindData['data'] = gd_htmlspecialchars_stripslashes(gd_isset($data));
        $cartRemindData['sort'] = $sort;
        $cartRemindData['search'] = gd_htmlspecialchars($this->search);
        $cartRemindData['selected'] = $this->selected;

        return $cartRemindData;
    }

    /**
     * 장바구니 알림 발송 대상 및 차감 포인트
     *
     * @param int $cartRemindNo 장바구니 알림 고유번호
     *
     * @return array $cartRemindSendMembedr 장바구니 알림 발송 대상 및 차감 포인트
     *
     * @author su
     */
    public function getCartRemindSend($cartRemindNo)
    {
        $cartRemindData = $this->getCartRemindInfo($cartRemindNo, '*');

        // 발송 대상 수동이고 직접일자 지정
        if ($cartRemindData['cartRemindType'] == 'manual' && $cartRemindData['cartRemindPeriod'] == '5') {
            $cartRemindPeriod['start'] = $cartRemindData['cartRemindPeriodStart'] . ' 00:00:00';
            $cartRemindPeriod['end'] = $cartRemindData['cartRemindPeriodEnd'] . ' 23:59:59';
        } else {
            $cartRemindPeriod = $this->getCartRemindPeriodDate($cartRemindData['cartRemindType'], $cartRemindData['cartRemindPeriod']);
        }

        $goodsWhere[] = "c.goodsNo = g.goodsNo AND g.applyFl = 'y'";
        if ($cartRemindData['cartRemindGoodsSellFl'] == 'y') {
            $goodsWhere[] = "g.goodsSellFl ='y' AND g.goodsSellMobileFl ='y'";
        }
        if ($cartRemindData['cartRemindGoodsDisplayFl'] == 'y') {
            $goodsWhere[] = "g.goodsDisplayFl ='y' AND g.goodsDisplayMobileFl ='y'";
        }
        $cartRemindGoodsSoldOutFl = $cartRemindData['cartRemindGoodsSoldOutFl'] == 'y'; //품절제외(무제한판매상품 포함)
        $cartRemindGoodsStock = $cartRemindData['cartRemindGoodsStock'] > 0; //재고량에따른 발송제한
        if ($cartRemindGoodsSoldOutFl || $cartRemindGoodsStock) {
            if ($cartRemindGoodsSoldOutFl && $cartRemindGoodsStock) {
                if ($cartRemindData['cartRemindGoodsStockSel'] == 'up') { //이상(무제한판매상품 포함)
                    $goodsWhere[] = "g.soldOutFl = 'n' AND (g.stockFl = 'n' OR (g.stockFl = 'y' AND g.totalStock >= ?))";
                } else { //이하(무제한판매상품 미포함)
                    $goodsWhere[] = "g.soldOutFl = 'n' AND g.stockFl = 'y' AND g.totalStock > 0 AND g.totalStock <= ?";
                }
                $this->db->bind_param_push($this->arrBind, 'i', $cartRemindData['cartRemindGoodsStock']);
            } else if ($cartRemindGoodsSoldOutFl) {
                $goodsWhere[] = "g.soldOutFl = 'n' AND (g.stockFl = 'n' OR (g.stockFl = 'y' AND g.totalStock > 0))";
            } else if ($cartRemindGoodsStock) {
                if ($cartRemindData['cartRemindGoodsStockSel'] == 'up') { //이상(무제한판매상품 포함)
                    $goodsWhere[] = "(g.stockFl = 'n' OR (g.stockFl = 'y' AND g.totalStock >= ?))";
                } else { //이하(무제한판매상품 미포함)
                    $goodsWhere[] = "g.stockFl = 'y' AND g.totalStock <= ?";
                }
                $this->db->bind_param_push($this->arrBind, 'i', $cartRemindData['cartRemindGoodsStock']);
            }
        }

        $memberWhere[] = "c.memNo = m.memNo AND m.smsFl = 'y' AND m.cellPhone != ''";
        $cartRemindApplyMemberGroup = ArrayUtils::objectToArray(json_decode($cartRemindData['cartRemindApplyMemberGroup']));
        if ($cartRemindApplyMemberGroup) {
            $memberWhere[] = "m.groupSno IN (" . implode(', ', $cartRemindApplyMemberGroup) . ")";
        }

        $this->arrWhere[] = "(c.regDt BETWEEN ? AND ?)";
        $this->db->bind_param_push($this->arrBind, 's', $cartRemindPeriod['start']);
        $this->db->bind_param_push($this->arrBind, 's', $cartRemindPeriod['end']);
        $this->arrWhere[] = "c.memNo > 0 AND c.directCart = 'n'";

        // 검색 조건
        $this->db->strField = "c.sno, m.memNo, m.memNm, m.groupSno, m.cellPhone";
        $this->db->strJoin = "INNER JOIN " . DB_MEMBER . " as m ON " . implode(' AND ', gd_isset($memberWhere))
            . " INNER JOIN " . DB_GOODS . " as g ON " . implode(' AND ', gd_isset($goodsWhere));
        $this->db->strWhere = implode(' AND ', gd_isset($this->arrWhere));

        $query = $this->db->query_complete();
        $strSQL = 'SELECT ' . array_shift($query) . ' FROM ' . DB_CART . ' as c ' . implode(' ', $query);
        $data = $this->db->query_fetch($strSQL, $this->arrBind);
        $data = gd_htmlspecialchars_stripslashes(gd_isset($data));
        unset($this->arrBind);
        unset($this->arrWhere);

        // 중복 제거
        $memNoData = array_unique(array_column($data, 'memNo'));
        $memNmData = array_column($data, 'memNm'); // 회원 이름은 unique 아님 동명이인
        $groupSnoData = array_column($data, 'groupSno'); // 회원그룹 번호는 unique 하면 안됨
        $cellPhoneData = array_column($data, 'cellPhone'); // 핸드폰 번호는 unique 아니므로 회원고유번호가 다르지만 같은 핸드폰 번호 가능

        $memberData = [];
        foreach ($memNoData as $memNoKey => $memNoVal) {
            $memberData[$memNoKey]['memNo'] = $memNoVal;
            $memberData[$memNoKey]['memNm'] = $memNmData[$memNoKey];
            $memberData[$memNoKey]['groupSno'] = $groupSnoData[$memNoKey];
            $memberData[$memNoKey]['cellPhoneData'] = $cellPhoneData[$memNoKey];
        }

        $getCartRemindSend['member'] = $memberData;

        if ($cartRemindData['cartRemindSendType'] == 'sms') {
            $getCartRemindSend['useSmsPoint'] = count($getCartRemindSend['member']);
        } else if ($cartRemindData['cartRemindSendType'] == 'lms') {
            $getCartRemindSend['useSmsPoint'] = count($getCartRemindSend['member']) * Sms::LMS_POINT;
        }
        $getCartRemindSend['type'] = $cartRemindData['cartRemindType'];
        $getCartRemindSend['autoUse'] = $cartRemindData['cartRemindAutoUse'];
        $getCartRemindSend['autoSendTime'] = $cartRemindData['cartRemindAutoSendTime'];
        $getCartRemindSend['smsType'] = $cartRemindData['cartRemindSendType'];
        $getCartRemindSend['message'] = $cartRemindData['cartRemindSendMessage'];
        $getCartRemindSend['cartUrl'] = $cartRemindData['cartRemindSendUrl'];
        if ($cartRemindData['cartRemindCoupon'] > 0) {
            $getCartRemindSend['coupon'] = $cartRemindData['cartRemindCoupon'];
        }

        return $getCartRemindSend;
    }

    /**
     * 장바구니 알림 발송대상 한글 표시
     *
     * @param string $cartRemindType   장바구니 알림 발송유형 (manual, auto)
     * @param int    $cartRemindPeriod 장바구니 알림 발송대상 (1~7)
     *
     * @return string $convertCartRemindPeriod 장바구니 알림 리스트의 표시용 한글 변경
     *
     * @author su
     */
    public function convertCartRemindPeriod($cartRemindType, $cartRemindPeriod)
    {
        $convertCartRemindPeriod = $this->period[$cartRemindType][$cartRemindPeriod];

        return $convertCartRemindPeriod;
    }

    /**
     * 장바구니 알림 현재 발송대상 기간
     *
     * @param string $cartRemindType   장바구니 알림 발송유형 (manual, auto)
     * @param int    $cartRemindPeriod 장바구니 알림 발송대상 (1~7)
     *
     * @return array $getCartRemindPeriodDate 장바구니 알림 발송대상 기간
     *
     * @author su
     */
    public function getCartRemindPeriodDate($cartRemindType, $cartRemindPeriod)
    {
        $period = [];
        if ($cartRemindType == 'manual') {
            switch ($cartRemindPeriod) {
                case '1':
                    $period['start'] = -3;
                    $period['end'] = -1;
                    break;
                case '2':
                    $period['start'] = -5;
                    $period['end'] = -3;
                    break;
                case '3':
                    $period['start'] = -7;
                    $period['end'] = -5;
                    break;
                case '4':
                    $period['start'] = -7;
                    $period['end'] = -3;
                    break;
            }
            $getCartRemindPeriodDate['start'] = DateTimeUtils::getDateTimeByPeriod($period['start'] . 'days');
            $getCartRemindPeriodDate['start'] = date_format($getCartRemindPeriodDate['start'], 'Y-m-d 00:00:00');
            $getCartRemindPeriodDate['end'] = DateTimeUtils::getDateTimeByPeriod($period['end'] . 'days');
            $getCartRemindPeriodDate['end'] = date_format($getCartRemindPeriodDate['end'], 'Y-m-d 23:59:59');
        } else {
            $cartRemindPeriodDate = DateTimeUtils::getDateTimeByPeriod('-' . $cartRemindPeriod . 'days');
            $getCartRemindPeriodDate['start'] = date_format($cartRemindPeriodDate, 'Y-m-d 00:00:00');
            $getCartRemindPeriodDate['end'] = date_format($cartRemindPeriodDate, 'Y-m-d 23:59:59');
        }

        return $getCartRemindPeriodDate;
    }

    /**
     * 여러(Array) 장바구니 알림 리스트용 데이터를 한글로 변경한 별도의 Array 생성
     *
     * @param array $cartRemindArrData 장바구니 알림의 리스트
     *
     * @return array $convertCartRemindArrData 장바구니 알림 리스트의 표시용 한글 변경
     *
     * @author su
     */
    public function convertCartRemindArrData($cartRemindArrData)
    {
        foreach ($cartRemindArrData as $key => $val) {
            // 지금 발송 시 발송대상 회원 과 발송 차감 포인트
            $cartRemindSendMember = $this->getCartRemindSend($val['cartRemindNo']);

            if ($val['cartRemindType'] == 'manual') {
                $convertCartRemindArrData[$key]['cartRemindType'] = __('수동발송');
                $convertCartRemindArrData[$key]['sendMemberCount'] = sprintf(__('발송대상 %s 명'),count($cartRemindSendMember['member']));
                $convertCartRemindArrData[$key]['sendPoint'] = sprintf(__('%s 포인트 차감 예정'),$cartRemindSendMember['useSmsPoint']);
                $convertCartRemindArrData[$key]['sendAction'] = "<button type=\"button\" class=\"btn btn-sm btn-white js-manual-send\" data-no=\"" . $val['cartRemindNo'] . "\" data-cnt=\"". count($cartRemindSendMember['member']) ."\">".__('발송하기')."</button>";
            } else if ($val['cartRemindType'] == 'auto') {
                $convertCartRemindArrData[$key]['cartRemindType'] = __('자동발송');
                if ($val['cartRemindAutoUse'] == 'y') {
                    $convertCartRemindArrData[$key]['sendMemberCount'] = sprintf(__('발송대상 %s 명'),count($cartRemindSendMember['member']));
                    $convertCartRemindArrData[$key]['sendPoint'] =  sprintf(__('%s 포인트 차감 예정'),$cartRemindSendMember['useSmsPoint']);
                    $convertCartRemindArrData[$key]['sendAction'] = "<button type=\"button\" class=\"btn btn-sm btn-white js-auto-state\" data-type=\"stop\" data-no=\"" . $val['cartRemindNo'] . "\">".__("발송중지")."</button>";
                } else {
                    $convertCartRemindArrData[$key]['sendPoint'] = '<span class="text-red">'.__('발송중지').'</span>';
                    $convertCartRemindArrData[$key]['sendAction'] = "<button type=\"button\" class=\"btn btn-sm btn-white js-auto-state\" data-type=\"start\" data-no=\"" . $val['cartRemindNo'] . "\">".__("발송시작")."</button>";
                }
            }
            $cartRemindPeriod = $this->convertCartRemindPeriod($val['cartRemindType'], $val['cartRemindPeriod']);
            if ($cartRemindPeriod == '직접선택') {
                $cartRemindPeriod = $val['cartRemindPeriodStart'] . ' ~ ' . $val['cartRemindPeriodEnd'];
            }
            $convertCartRemindArrData[$key]['cartRemindPeriod'] = $cartRemindPeriod;
            $cartRemindApplyMemberGroup = json_decode($val['cartRemindApplyMemberGroup']);
            if ($cartRemindApplyMemberGroup) {
                foreach ($cartRemindApplyMemberGroup as $memkey => $memval) {
                    $groupNm = GroupUtil::getGroupName('sno=' . $memval);
                    $convertCartRemindArrData[$key]['cartRemindApplyMemberGroup'][$memkey]['no'] = $memval;
                    $convertCartRemindArrData[$key]['cartRemindApplyMemberGroup'][$memkey]['name'] = $groupNm[$memval];
                }
            }
            if ($val['cartRemindGoodsStock'] > 0) {
                if ($val['cartRemindGoodsStockSel'] == 'down') {
                    $convertCartRemindArrData[$key]['cartRemindGoodsStock'] =  sprintf(__('%s 이하'),$val['cartRemindGoodsStock']);
                } else if ($val['cartRemindGoodsStockSel'] == 'up') {
                    $convertCartRemindArrData[$key]['cartRemindGoodsStock'] = sprintf(__('%s 이상'),$val['cartRemindGoodsStock']);
                }
            } else {
                $convertCartRemindArrData[$key]['cartRemindGoodsStock'] = __('없음');
            }
            if ($val['cartRemindCoupon'] > 0) {
                $convertCartRemindArrData[$key]['cartRemindCoupon'] = __('있음');
                $convertCartRemindArrData[$key]['cartRemindCoupon'] .= "<button type=\"button\" class=\"btn btn-sm btn-white js-coupon-detail\" data-couponno=\"" . $val['cartRemindCoupon'] . "\">".__('상세보기')."</button>";
            } else {
                $convertCartRemindArrData[$key]['cartRemindCoupon'] = __('없음');
            }
        }

        return $convertCartRemindArrData;
    }

    /**
     * 장바구니 알림 검색
     *
     * @author su
     */
    public function setCartRemindSearch($searchData)
    {
        gd_isset($searchData['cartRemindType'], '');
        gd_isset($searchData['cartRemindPeriod'], '');
        gd_isset($searchData['cartRemindGoodsSellFl'], '');
        gd_isset($searchData['cartRemindGoodsDisplayFl'], '');
        $this->search['cartRemindType'] = $searchData['cartRemindType'];


        // 발급유형에 따른 검색
        if ($this->search['cartRemindType']) {
            $this->arrWhere[] = 'cr.cartRemindType = ?';
            $this->db->bind_param_push($this->arrBind, 's', $this->search['cartRemindType']);
        }

        $this->selected['cartRemindType'][$this->search['cartRemindType']] = "selected='selected'";

        if (empty($this->arrBind)) {
            $this->arrBind = null;
        }
    }

    /**
     * 장바구니 알림 저장
     *
     * @author su
     */
    public function setCartRemind(&$arrData)
    {
        $cartRemindMemberGroupCheck = true;
        if ($arrData['cartRemindApplyMemberGroup'] && $arrData['cartRemindCoupon']) {
            $couponAdmin = new CouponAdmin();
            $couponMemberGroup = $couponAdmin->getCouponInfo($arrData['cartRemindCoupon'],'couponApplyMemberGroup');
            if ($couponMemberGroup['couponApplyMemberGroup']) {
                $couponMemberGroupArr = explode(INT_DIVISION,$couponMemberGroup['couponApplyMemberGroup']);
                foreach ($arrData['cartRemindApplyMemberGroup'] as $memGroupKey => $memGroupVal) {
                    if (array_search($memGroupVal,$couponMemberGroupArr) === false) {
                        $cartRemindMemberGroupCheck = false;
                    }
                }
            }
        } else if (!$arrData['cartRemindApplyMemberGroup'] && $arrData['cartRemindCoupon']) {
            $couponAdmin = new CouponAdmin();
            $couponMemberGroup = $couponAdmin->getCouponInfo($arrData['cartRemindCoupon'],'couponApplyMemberGroup');
            if ($couponMemberGroup['couponApplyMemberGroup']) {
                $cartRemindMemberGroupCheck = false;
            }
        }
        if (!$cartRemindMemberGroupCheck) {
            throw new \Exception(__('장바구니 알림 발송대상 회원등급과 선택한 쿠폰의 발급가능 회원등급이 다릅니다. 확인 후 다시 선택해주세요.'));
        }
        // 장바구니 알림 회원등급
        $arrData['cartRemindApplyMemberGroup'] = json_encode($arrData['cartRemindApplyMemberGroup'], JSON_FORCE_OBJECT);

        // Validation
        $validator = new Validator();
        if (substr($arrData['mode'], 0, 6) == 'modify') {
            $validator->add('cartRemindNo', 'number', true); // 장바구니 알림 고유번호
        } else {
            $totalCartRemind = $this->getCartRemindCount();
            if ($totalCartRemind >= LIMIT_CART_REMIND_AMOUNT) {
                throw new \Exception(sprintf(__('장바구니 알림은 최대 %s개까지만 등록할 수 있습니다. 기존 알림을 수정하거나 삭제 후 등록해주세요.'),LIMIT_CART_REMIND_AMOUNT));
            }
            $arrData['cartRemindInsertAdminId'] = Session::get('manager.managerId');
            $arrData['managerNo'] = Session::get('manager.sno');
            $validator->add('cartRemindInsertAdminId', 'userid', true); // 장바구니 알림 등록자-아이디
            $validator->add('managerNo', 'number', true); // 장바구니 알림 등록자-키
        }
        $validator->add('mode', null, true); // 모드
        $validator->add('cartRemindNm', null, true); // 장바구니 알림명
        $validator->add('cartRemindType', 'alpha', true); // 수동발송(‘manual’), 자동발송(‘auto’)
        if ($arrData['cartRemindType'] == 'manual') {
            $arrData['cartRemindPeriod'] = $arrData['cartRemindPeriodManual'];
        } else if ($arrData['cartRemindType'] == 'auto') {
            $arrData['cartRemindPeriod'] = $arrData['cartRemindPeriodAuto'];
            $validator->add('cartRemindAutoSendTime', 'number', true); // 자동발송 시간
        }
        $validator->add('cartRemindPeriod', 'number', true); // 발송대상기간

        if ($arrData['cartRemindType'] == 'manual' && $arrData['cartRemindPeriod'] == 5) {
            $diffDay = DateTimeUtils::intervalDateTime($arrData['cartRemindPeriodStart'], $arrData['cartRemindPeriodEnd']);
            if ($diffDay->days > 7) {
                throw new \Exception(sprintf(__('최대 발송대상은 %s일 입니다.'),7));
            }
            $validator->add('cartRemindPeriodStart', null, true); // 장바구니 알림 발송대상기간 직접 입력
            $validator->add('cartRemindPeriodEnd', null, true); // 장바구니 알림 발송대상기간 직접 입력
        }
        gd_isset($arrData['cartRemindGoodsSellFl'], 'n');
        gd_isset($arrData['cartRemindGoodsDisplayFl'], 'n');
        gd_isset($arrData['cartRemindGoodsSoldOutFl'], 'n');
        $validator->add('cartRemindGoodsSellFl', 'yn', null); // 판매안함은 pc 든 모바일이든 어느 하나 판매안함이면 제외
        $validator->add('cartRemindGoodsDisplayFl', 'yn', null); // 노출안함은 pc 든 모바일이든 어느 하나 노출안함이면 제외
        $validator->add('cartRemindGoodsSoldOutFl', 'yn', null); // 품절이면 제외

        $validator->add('cartRemindGoodsStock', 'number', null); // 상품재고 0일때는 작동 안됨 , 빈값일 때 작동 안됨
        if ($arrData['cartRemindGoodsStock'] > 0) {
            $validator->add('cartRemindGoodsStockSel', 'alpha', true); // 재고량에 이상(up),이하(down)
        }
        $validator->add('cartRemindApplyMemberGroup', null, null); // 발송회원등급
        $validator->add('cartRemindCoupon', null, null); // 발송쿠폰
        $validator->add('cartRemindSendType', null, true); // 발송타입 (sms,lms)
        $validator->add('cartRemindSendMessage', null, true); // 발송메세지

        if ($validator->act($arrData, true) === false) {
            throw new \Exception(implode("<br/>", $validator->errors));
        }

        // 선택에 따른 값의 초기화를 위한 빈값 제거 - 주석처리 함 - 상태에 따른 빈값 저장(초기화)이 필요한 경우가 존재함.
        // $arrData = ArrayUtils::removeEmpty($arrData);

        switch (substr($arrData['mode'], 0, 6)) {
            case 'insert' : {
                // 저장
                $arrBind = $this->db->get_binding(DBTableField::tableCartRemind(), $arrData, 'insert', array_keys($arrData), ['cartRemindNo']);
                $this->db->set_insert_db(DB_CART_REMIND, $arrBind['param'], $arrBind['bind'], 'y');
                // 등록된 쿠폰고유번호
                $cartRemindNo = $this->db->insert_id();
                unset($arrData);
                unset($arrBind);

                // 장바구니 알림 링크 - short url
                $longUrl = 'http://' . \Request::getDefaultHost() . DS . 'order' . DS . 'cart.php?cr=' . $cartRemindNo;
                $arrData['cartRemindSendUrl'] = GodoUtils::shortUrl($longUrl);

                $arrBind = $this->db->get_binding(DBTableField::tableCartRemind(), $arrData, 'update', array_keys($arrData), ['cartRemindNo']);
                $this->db->bind_param_push($arrBind['bind'], 'i', $cartRemindNo);
                $this->db->set_update_db(DB_CART_REMIND, $arrBind['param'], 'cartRemindNo = ?', $arrBind['bind'], false);
                unset($arrData);
                unset($arrBind);

                break;
            }
            case 'modify' : {
                // 수정
                $arrBind = $this->db->get_binding(DBTableField::tableCartRemind(), $arrData, 'update', array_keys($arrData), ['cartRemindNo']);
                $this->db->bind_param_push($arrBind['bind'], 'i', $arrData['cartRemindNo']);
                $this->db->set_update_db(DB_CART_REMIND, $arrBind['param'], 'cartRemindNo = ?', $arrBind['bind'], false);
                unset($arrData);
                unset($arrBind);
                break;
            }
        }
    }

    /**
     * 장바구니 알림 메세지 수정
     *
     * @author su
     */
    public function setCartRemindSendMessage(&$arrData)
    {
        // Validation
        $validator = new Validator();
        $validator->add('mode', null, true); // 모드
        $validator->add('cartRemindNo', 'number', true); // 장바구니 알림 고유번호
        $validator->add('cartRemindSendType', null, true); // 발송타입 (sms,lms)
        $validator->add('cartRemindSendMessage', null, true); // 발송메세지

        if ($validator->act($arrData, true) === false) {
            throw new \Exception(implode("<br/>", $validator->errors));
        }

        // 선택에 따른 값의 초기화를 위한 빈값 제거 - 주석처리 함 - 상태에 따른 빈값 저장(초기화)이 필요한 경우가 존재함.
        // $arrData = ArrayUtils::removeEmpty($arrData);

        // 수정
        $arrBind = $this->db->get_binding(DBTableField::tableCartRemind(), $arrData, 'update', array_keys($arrData), ['cartRemindNo']);
        $this->db->bind_param_push($arrBind['bind'], 'i', $arrData['cartRemindNo']);
        $this->db->set_update_db(DB_CART_REMIND, $arrBind['param'], 'cartRemindNo = ?', $arrBind['bind'], false);
    }

    /**
     * 장바구니 알림으로 접속시 세션 생성
     *
     * @author su
     */
    public function setCartRemindSession($cartRemindNo)
    {
        Session::set('cartRemind', $cartRemindNo);
    }

    /**
     * 장바구니 알림으로 접속 시 카운트 증가
     *
     * @author su
     */
    public function setCartRemindConnectCount($cartRemindNo)
    {
        if (Session::get('cartRemind') > 0) {
            $arrBind = [];
            $this->db->bind_param_push($arrBind, 'i', $cartRemindNo);
            $this->db->set_update_db_query(DB_CART_REMIND, 'cartRemindConnectCount = cartRemindConnectCount + 1', 'cartRemindNo = ?', $arrBind);
            unset($arrBind);
        }
    }

    /**
     * 장바구니 알림으로 주문 시 카운트 증가
     *
     * @author su
     */
    public function setCartRemindOrderCount($cartRemindNo)
    {
        if (Session::get('cartRemind') > 0) {
            $arrBind = [];
            $this->db->bind_param_push($arrBind, 'i', $cartRemindNo);
            $this->db->set_update_db_query(DB_CART_REMIND, 'cartRemindOrderCount = cartRemindOrderCount + 1', 'cartRemindNo = ?', $arrBind);
            unset($arrBind);
        }
    }

    /**
     * 장바구니 알림으로 자동발송 상태 변경
     *
     * @author su
     */
    public function setCartRemindAutoState(&$postValue)
    {
        $arrBind = [];
        if ($postValue['type'] == 'stop') {
            $this->db->bind_param_push($arrBind, 's', 'n');
        } else if ($postValue['type'] == 'start') {
            $this->db->bind_param_push($arrBind, 's', 'y');
        }
        $this->db->bind_param_push($arrBind, 'i', $postValue['cartRemindNo']);
        $this->db->set_update_db_query(DB_CART_REMIND, 'cartRemindAutoUse = ?', 'cartRemindNo = ?', $arrBind);
        unset($arrBind);
    }

    /**
     * 장바구니 알림 전송
     *
     * @author su
     *
     * @param $postValue
     *
     * @return mixed
     * @throws \Component\Sms\Exception\PasswordException
     */
    public function sendCartRemind(&$postValue)
    {
        $cartRemindNo = $postValue['cartRemindNo'];
        $cartRemindSendData = $this->getCartRemindSend($cartRemindNo);

        $smsAdmin = new SmsAdmin();
        // 발신 번호
        $smsSendData['smsCallNum'] = $smsAdmin->getSmsCallNum();

        if ($smsSendData['smsCallNum'] === '') {
            $result['msg'] = __('등록된 SMS 발신번호가 없습니다. SMS 발신번호를 등록해주세요.<br>발신번호 사전등록제에 따라 거짓으로 표시된 전화번호로 인한 이용자 피해 예방을 위해 사전 등록한 발신번호로만 SMS를 발송하실 수 있습니다.');
            return $result;
        }

        // 수신거부회원 포함 발송 여부 (y - 수신거부 포함, n - 수신회원만)
        $smsSendData['rejectSend'] = 'n';

        if (\count($cartRemindSendData['member']) < 1) {
            $result['msg'] = __('장바구니 알림 조건에 해당하는 발송대상이 없습니다.');
            return $result;
        }
        if ($cartRemindSendData['useSmsPoint'] > Sms::getPoint()) {
            $result['msg'] = __('SMS 잔여포인트가 부족하여 장바구니 알림을 발송할 수 없습니다. SMS포인트를 충전해주세요.');
            return $result;
        }

        if ($cartRemindSendData['type'] === 'manual') {
            $nowTime = date('G');
            if ($nowTime > 8 && $nowTime < 21) {
                $smsSendPossibleTimeChk = true;
            } else {
                $smsSendPossibleTimeChk = false;
            }

            if (!$smsSendPossibleTimeChk) {
                $result['msg'] = __('장바구니 알림은 영리목적의 광고성 정보에 해당하므로 별도의 동의없이<br/>오후 09 ~ 다음날 오전 08시사이에는 발송할 수 없습니다.');
                return $result;
            }

            // 발송 설정 (now - 즉시 발송 , reserve - 예약 발송)
            $smsSendData['smsSendType'] = 'now';
            // 예약발송 일자 시간 (yyyy-mm-dd hh:ii:ss)
            $smsSendData['smsSendReserveDate'] = '';
        } elseif ($cartRemindSendData['type'] === 'auto') {
            // 발송 설정 (now - 즉시 발송 , reserve - 예약 발송)
            $smsSendData['smsSendType'] = 'reserve';
            // 예약발송 일자 시간 (yyyy-mm-dd hh:ii:ss)
            $smsSendData['smsSendReserveDate'] = date('Y-m-d H:i:s', mktime($cartRemindSendData['autoSendTime'],0,0,date('m'),date('d'),date('Y')));
        }
        // sms, lms
        $smsSendData['sendFl'] = $cartRemindSendData['smsType'];

        // ??
        $smsSendData['agreeCnt'] = '';
        $smsSendData['rejectCnt'] = '';

        // 개별발송
        $smsSendData['receiverType'] = 'each';

        // 전송요청 수
        $result['request'] = \count($cartRemindSendData['member']);
        // 전송성공 수
        $result['success'] = 0;
        // 전송실패 수
        $result['fail'] = 0;

        foreach ($cartRemindSendData['member'] as $memKey => $memVal) {
            // 장바구니 알림에 쿠폰 지급이 있다면 지급
            if ($cartRemindSendData['coupon'] > 0) {
                $coupon = new Coupon();
                $coupon->setAutoCouponMemberSave('cartRemind', $memVal['memNo'], $memVal['groupSno'], $cartRemindSendData['coupon']);
            }

            $replaceCode = new ReplaceCode();
            $replaceData['memNm'] = $memVal['memNm'];
            $replaceData['cartRemindLink'] = $cartRemindSendData['cartUrl'];
            $replaceCode->setReplaceCodeByCartRemind($replaceData);
            $smsSendData['smsContents'] = $replaceCode->replace(trim($cartRemindSendData['message']));
            $smsSendData['receiverList'] = $memVal['memNo'];
            $smsSendData['password'] = \App::load(SmsUtil::class)->getPassword();
            $cnt = $smsAdmin->sendSms($smsSendData);
            $result['success'] += $cnt['success'];
            $result['fail'] += $cnt['fail'];
        }
        $result['msg'] = sprintf(__('SMS 발송이 완료되었습니다. (%1$s건 성공 / %2$s건 실패)'),$result['success'],$result['fail']);

        return $result;
    }

    /**
     * 장바구니 알림 삭제 (이미 발송 등록된 SMS 는 삭제 되지 않음)
     *
     * @author su
     */
    public function deleteCartRemind(&$postValue)
    {
        $cartRemindNo = $postValue['chkCartRemind'];

        foreach ($cartRemindNo as $key => $val) {

            $arrBind = [
                'i',
                $val,
            ];
            // --- 삭제
            $this->db->set_delete_db(DB_CART_REMIND, 'cartRemindNo = ?', $arrBind);
        }
    }

    /**
     * 장바구니 알림 자동 발송
     *
     * @author su
     */
    public function sendCartRemindAuto()
    {
        $this->db->strWhere = " cr.cartRemindType = ? AND cr.cartRemindAutoUse = ? ";
        $this->db->bind_param_push($arrBind, 's', 'auto');
        $this->db->bind_param_push($arrBind, 's', 'y');
        $this->db->strField = "*";
        $query = $this->db->query_complete();
        $strSQL = 'SELECT ' . array_shift($query) . ' FROM ' . DB_CART_REMIND . ' as cr ' . implode(' ', $query);
        $getData = $this->db->query_fetch($strSQL, $arrBind);

        foreach ($getData as $key => $val) {
            $postValue = [
                'cartRemindNo' => $val['cartRemindNo'],
            ];
            $this->sendCartRemind($postValue);
        }
        unset($arrBind);
    }

    /**
     * 장바구니 알림 등록 수
     *
     * @author su
     */
    public function getCartRemindCount()
    {
        $cartRemindCount = $this->db->fetch('SELECT count(cartRemindNo) FROM ' . DB_CART_REMIND . ' WHERE cartRemindNo > 0', 'row');
        return $cartRemindCount[0];
    }
}
