<?php
/**
 * CouponAdmin Class
 *
 * @author    sj, artherot
 * @version   1.0
 * @since     1.0
 * @copyright ⓒ 2016, NHN godo: Corp.
 */

namespace Bundle\Component\Coupon;

use Component\Database\DBTableField;
use Component\Member\Manager;
use Component\Sms\Code;
use Component\Sms\Sms;
use Component\Sms\SmsAdmin;
use Component\Sms\SmsAutoCode;
use Component\Validator\Validator;
use Framework\Utility\ArrayUtils;
use Globals;
use Request;
use Session;
use UserFilePath;

class CouponAdmin extends \Component\Coupon\Coupon
{
    const ECT_INVALID_ARG = 'CouponAdmin.ECT_INVALID_ARG';
    const ECT_NOTSELECTED_MEMBER = 'CouponAdmin.ECT_NOTSELECTED_MEMBER';
    const ECT_EXCEED_COUNT = 'CouponAdmin.ECT_EXCEED_COUNT';
    const ECT_ALREADY_GIVE = 'CouponAdmin.ECT_ALREADY_GIVE';
    const ECT_UPLOAD_FILEERROR = 'CouponAdmin.ECT_UPLOAD_FILEERROR';
    const ECT_REMOVE_FAILURE = 'CouponAdmin.ECT_REMOVE_FAILURE';
    const ECT_CANCEL_FAILURE = 'CouponAdmin.ECT_CANCEL_FAILURE';
    const ECT_UNAMENDABLE_COUPON = 'CouponAdmin.ECT_UNAMENDABLE_COUPON';

    const TEXT_INVALID_ARG = '%s인자가 잘못되었습니다.';
    const TEXT_NOTSELECTED_MEMBER = '쿠폰을 발급할 회원을 선택하세요.';
    const TEXT_EXCEED_COUNT = '발급할 수 있는 수량을 초과하였습니다.';
    const TEXT_ALREADY_GIVE = '쿠폰이 이미 발급되었습니다.';
    const TEXT_REMOVE_FAILURE = '쿠폰 삭제가 실패하였습니다.';
    const TEXT_CANCEL_FAILURE = '해당 발급된 쿠폰은 이미 사용된 쿠폰입니다.';
    const TEXT_UNAMENDABLE_COUPON = '수정할 수 없는 쿠폰입니다.\r\n(페이퍼 쿠폰과 회원에게 발급된 쿠폰은 수정할 수 없습니다.)';

    const BIRTHDAY_COUPON_RESERVE_DAYS_LIST = [
        0 => '당일',
        1 => '1일 전',
        2 => '2일 전',
        3 => '3일 전',
        4 => '4일 전',
        5 => '5일 전',
        6 => '6일 전',
        7 => '7일 전'
    ];
    const BIRTHDAY_COUPON_RESERVE_MONTH_LIST = [
        'current' => '당월',
        'last'    => '전월',
    ];
    const COUPONZONE_ORDER_BY_LIST = [
        'custom' => '운영자 진열순',
        'regDt DESC' => '최근 등록 쿠폰 위로',
        'regDt ASC'    => '최근 등록 쿠폰 아래로',
        'couponBenefitType ASC, couponBenefit DESC'    => '혜택금액(%,액) 높은 쿠폰 위로',
        'couponBenefitType DESC, couponBenefit ASC'    => '혜택금액(%,액) 높은 쿠폰 아래로',
        'couponNm ASC'    => '쿠폰명 가나다순',
        'couponNm DESC'    => '쿠폰명 가나다역순',
    ];

    /**
     * 쿠폰 리스트 가져오기
     *
     * @author su
     */
    public function getCouponAdminList($mode = null, $addSaveTypeWhere = '')
    {
        $getValue = Request::get()->toArray();
        $this->setCouponAdminSearch($getValue);

        $sort['fieldName'] = gd_isset($getValue['sort']['name']);
        $sort['sortMode'] = gd_isset($getValue['sort']['mode']);
        if (empty($sort['fieldName']) || empty($sort['sortMode'])) {
            $sort['fieldName'] = 'c.regDt';
            $sort['sortMode'] = 'desc';
        } else {
            $sort['fieldName'] = 'c' . $sort['fieldName'];
        }

        // 레이어에서 자바스크립트 페이징 처리시 사용되는 구문
        if (gd_isset($getValue['pagelink'])) {
            $getValue['page'] = (int) str_replace('page=', '', preg_replace('/^{page=[0-9]+}/', '', gd_isset($getValue['pagelink'])));
        }
        gd_isset($getValue['page'], 1);
        gd_isset($getValue['pageNum'], 10);

        $page = \App::load('\\Component\\Page\\Page', $getValue['page']);
        $page->page['list'] = $getValue['pageNum'];

        if (!$mode) {
            $mode = 'online';
        }

        if ($addSaveTypeWhere != '') {
            $this->arrWhere[] = 'c.couponSaveType = ?';
            $this->db->bind_param_push($this->arrBind, $this->fieldTypes['coupon']['couponSaveType'], $addSaveTypeWhere);
            $addSaveTypeWhere = " AND couponSaveType = '".$addSaveTypeWhere."'";
        }

        if ($mode == 'all') {
            $this->arrWhere[] = '1';
            $amountSql = 'SELECT count(couponNo) FROM ' . DB_COUPON . ' WHERE 1' . $addSaveTypeWhere;
        } else {
            $this->arrWhere[] = 'c.couponKind = ?';
            $this->db->bind_param_push($this->arrBind, $this->fieldTypes['coupon']['couponKind'], $mode);
            $amountSql = 'SELECT count(couponNo) FROM ' . DB_COUPON . ' WHERE couponKind = "' . $mode . '"' . $addSaveTypeWhere;
        }
        list($page->recode['amount']) = $this->db->fetch($amountSql, 'row');
        $page->setPage();
        $page->setUrl(\Request::getQueryString());

        $this->db->strField = "c.*";
        $this->db->strWhere = implode(' AND ', gd_isset($this->arrWhere));
        $this->db->strOrder = $sort['fieldName'] . ' ' . $sort['sortMode'];
        if($getValue['couponUseLayer'] != 'attendance_detail') { // 출석체크 쿠폰 혜택지급 레이어에서는 전체 쿠폰리스트 필요하여 limit 삭제
            $this->db->strLimit = $page->recode['start'] . ',' . $getValue['pageNum'];
        }
        // 검색 카운트
        $strSQL = ' SELECT COUNT(c.couponNo) AS cnt FROM ' . DB_COUPON .' as c ' . ' WHERE ' . $this->db->strWhere;
        $res = $this->db->query_fetch($strSQL, $this->arrBind, false);
        $page->recode['total'] = $res['cnt']; // 검색 레코드 수
        $page->setPage();
        $page->setUrl(\Request::getQueryString());

        $query = $this->db->query_complete();
        $strSQL = 'SELECT ' . array_shift($query) . ',m.isDelete FROM ' . DB_COUPON . ' as c LEFT OUTER JOIN ' . DB_MANAGER . " as m ON c.managerNo = m.sno" . implode(' ', $query);
//        \Logger::debug($strSQL, $this->arrBind);
        $data = $this->db->query_fetch($strSQL, $this->arrBind);
        Manager::displayListData($data);

        $getData['data'] = gd_htmlspecialchars_stripslashes(gd_isset($data));


        $getData['sort'] = $sort;
        $getData['search'] = gd_htmlspecialchars($this->search);
        $getData['checked'] = $this->checked;

        return $getData;
    }

    /**
     * 장바구니 알림용 쿠폰 리스트 가져오기
     *
     * @author su
     */
    public function getCouponCartRemind()
    {
        $this->arrWhere[] = 'couponEventType = ?';
        $this->db->bind_param_push($this->arrBind, $this->fieldTypes['coupon']['couponEventType'], 'cartRemind');
        $this->db->strField = "*";
        $this->db->strWhere = implode(' AND ', gd_isset($this->arrWhere));

        $query = $this->db->query_complete();
        $strSQL = 'SELECT ' . array_shift($query) . ' FROM ' . DB_COUPON . ' as c ' . implode(' ', $query);
        $data = $this->db->query_fetch($strSQL, $this->arrBind);
        $getData = gd_htmlspecialchars_stripslashes(gd_isset($data));

        return $getData;
    }

    /**
     * 쿠폰의 회원 리스트 가져오기
     *
     * @author su
     */
    public function getMemberCouponAdminList()
    {
        $getValue = Request::get()->toArray();
        $this->setMemberCouponAdminSearch($getValue);

        $sort['fieldName'] = gd_isset($getValue['sort']['name']);
        $sort['sortMode'] = gd_isset($getValue['sort']['mode']);
        if (empty($sort['fieldName']) || empty($sort['sortMode'])) {
            $sort['fieldName'] = 'mc.regDt';
            $sort['sortMode'] = 'desc';
        } else {
            $sort['fieldName'] = 'mc.' . $sort['fieldName'];
        }

        gd_isset($getValue['page'], 1);
        gd_isset($getValue['pageNum'], 10);

        $page = \App::load('\\Component\\Page\\Page', $getValue['page']);
        $page->page['list'] = $getValue['pageNum'];
        $total = $this->db->fetch('SELECT count(mc.memNo) as total FROM ' . DB_MEMBER_COUPON . ' as mc WHERE mc.couponNo = ' . $getValue['couponNo']);
        $page->recode['amount'] = $total['total'];
        $page->setPage();
        $page->setUrl(\Request::getQueryString());

        $this->db->strField = "mc.*, c.couponNm, c.couponDescribed, c.couponSaveType, ";
        $this->db->strField .= " m.memId, m.memNm, m.groupSno, IF(ms.connectFl='y', ms.snsTypeFl, '') AS snsTypeFl";
        $this->db->strWhere = implode(' AND ', gd_isset($this->arrWhere));
        $this->db->strJoin = 'LEFT JOIN ' . DB_MEMBER . ' as m ON m.memNo = mc.memNo ';
        $this->db->strJoin .= 'LEFT JOIN ' . DB_MEMBER_SNS . ' as ms ON mc.memNo = ms.memNo ';
        $this->db->strJoin .= ' LEFT JOIN ' . DB_COUPON . ' as c ON c.couponNo = mc.couponNo';
        $this->db->strOrder = $sort['fieldName'] . ' ' . $sort['sortMode'];
        $this->db->strLimit = $page->recode['start'] . ',' . $getValue['pageNum'];

        // 검색 카운트
        $strSQL = ' SELECT COUNT(mc.memNo) AS cnt FROM ' . DB_MEMBER_COUPON .' as mc ' . $this->db->strJoin . ' WHERE ' . $this->db->strWhere;
        $res = $this->db->query_fetch($strSQL, $this->arrBind, false);
        $page->recode['total'] = $res['cnt']; // 검색 레코드 수
        $page->setPage();
        $page->setUrl(\Request::getQueryString());

        $query = $this->db->query_complete();
        $strSQL = 'SELECT ' . array_shift($query) . ' FROM ' . DB_MEMBER_COUPON . ' as mc ' . implode(' ', $query);
        $data = $this->db->query_fetch($strSQL, $this->arrBind);

        $getData['data'] = gd_htmlspecialchars_stripslashes(gd_isset($data));
        $getData['sort'] = $sort;
        $getData['search'] = gd_htmlspecialchars($this->search);
        $getData['checked'] = $this->checked;
        return $getData;
    }

    /**
     * 오프라인 쿠폰의 회원 리스트 가져오기
     *
     * @author su
     */
    public function getMemberCouponOfflineAdminList()
    {
        $getValue = Request::get()->toArray();
        $this->setMemberCouponAdminSearch($getValue);

        $sort['fieldName'] = gd_isset($getValue['sort']['name']);
        $sort['sortMode'] = gd_isset($getValue['sort']['mode']);
        if (empty($sort['fieldName']) || empty($sort['sortMode'])) {
            $sort['fieldName'] = 'mc.regDt';
            $sort['sortMode'] = 'desc';
        } else {
            $sort['fieldName'] = 'mc.' . $sort['fieldName'];
        }

        gd_isset($getValue['page'], 1);
        gd_isset($getValue['pageNum'], 10);

        $page = \App::load('\\Component\\Page\\Page', $getValue['page']);
        $page->page['list'] = $getValue['pageNum'];
        $total = $this->db->fetch('SELECT count(mc.memNo) as total FROM ' . DB_MEMBER_COUPON . ' as mc WHERE mc.couponNo = ' . $getValue['couponNo']);
        $page->recode['amount'] = $total['total'];
        $page->setPage();
        $page->setUrl(\Request::getQueryString());

        $this->db->strField = "coc.*, mc.*, c.couponAuthType, ";
        $this->db->strField .= " m.memId, m.memNm, m.groupSno";
        $this->db->strWhere = implode(' AND ', gd_isset($this->arrWhere));
        $this->db->strJoin .= ' LEFT JOIN ' . DB_COUPON . ' as c ON c.couponNo = mc.couponNo ';
        $this->db->strJoin .= ' LEFT JOIN ' . DB_COUPON_OFFLINE_CODE . ' as coc ON mc.couponNo = coc.couponNo AND mc.memNo = coc.memNo AND mc.memberCouponNo = coc.memberCouponNo ';
        $this->db->strJoin .= ' LEFT JOIN ' . DB_MEMBER . ' as m ON m.memNo = mc.memNo ';
        $this->db->strOrder = $sort['fieldName'] . ' ' . $sort['sortMode'];
        $this->db->strLimit = $page->recode['start'] . ',' . $getValue['pageNum'];

        // 검색 카운트
        $strSQL = ' SELECT COUNT(mc.memNo) AS cnt FROM ' . DB_MEMBER_COUPON .' as mc ' . $this->db->strJoin . ' WHERE ' . $this->db->strWhere;
        $res = $this->db->query_fetch($strSQL, $this->arrBind, false);
        $page->recode['total'] = $res['cnt']; // 검색 레코드 수
        $page->setPage();
        $page->setUrl(\Request::getQueryString());

        $query = $this->db->query_complete();
        $strSQL = 'SELECT ' . array_shift($query) . ' FROM ' . DB_MEMBER_COUPON . ' as mc ' . implode(' ', $query);
        $data = $this->db->query_fetch($strSQL, $this->arrBind);

        $getData['data'] = gd_htmlspecialchars_stripslashes(gd_isset($data));
        $getData['sort'] = $sort;
        $getData['search'] = gd_htmlspecialchars($this->search);
        $getData['checked'] = $this->checked;

        return $getData;
    }

    /**
     * 오프라인 쿠폰의 인증번호 리스트 가져오기
     *
     * @author su
     */
    public function getCouponOfflineAuthCodeAdminList()
    {
        $getValue = Request::get()->toArray();
        $couponNo = $getValue['couponNo'];
        $this->arrWhere[] = 'coc.couponNo =?';
        $this->db->bind_param_push($this->arrBind, $this->fieldTypes['couponOffline']['couponNo'], $couponNo);

        $sort['fieldName'] = 'coc.couponOfflineCode, coc.regDt';
        $sort['sortMode'] = 'desc';

        gd_isset($getValue['page'], 1);
        gd_isset($getValue['pageNum'], 10);

        $page = \App::load('\\Component\\Page\\Page', $getValue['page']);
        $page->page['list'] = $getValue['pageNum'];
        $page->recode['amount'] = $this->db->table_status(DB_MEMBER_COUPON, 'Rows');
        $page->setPage();
        $page->setUrl(\Request::getQueryString());

        $this->db->strField = "coc.* ";
        $this->db->strWhere = implode(' AND ', gd_isset($this->arrWhere));
        $this->db->strOrder = $sort['fieldName'] . ' ' . $sort['sortMode'];
        $this->db->strLimit = $page->recode['start'] . ',' . $getValue['pageNum'];

        // 검색 카운트
        $strSQL = ' SELECT COUNT(*) AS cnt FROM ' . DB_COUPON_OFFLINE_CODE .' as coc ' . ' WHERE ' . $this->db->strWhere;
        $res = $this->db->query_fetch($strSQL, $this->arrBind, false);
        $page->recode['total'] = $res['cnt']; // 검색 레코드 수
        $page->setPage();
        $page->setUrl(\Request::getQueryString());

        $query = $this->db->query_complete();
        $strSQL = 'SELECT ' . array_shift($query) . ' FROM ' . DB_COUPON_OFFLINE_CODE . ' as coc ' . implode(' ', $query);
        $data = $this->db->query_fetch($strSQL, $this->arrBind);

        $getData['data'] = gd_htmlspecialchars_stripslashes(gd_isset($data));

        return $getData;
    }


    /**
     * 오프라인 쿠폰의 인증번호 리스트 가져오기 엑셀 데이터 생성
     *
     * @author su
     */
    public function getCouponOfflineAuthCodeAdminListExcel($getValue)
    {
        if ($getValue['layer_auth_code'] && is_array($getValue['layer_auth_code'])) {
            $this->arrWhere[] = "couponOfflineCode IN ('" . implode("','", $getValue['layer_auth_code']) . "')";
        }

        $couponNo = $getValue['couponNo'];
        $this->arrWhere[] = 'coc.couponNo =?';
        $this->db->bind_param_push($this->arrBind, $this->fieldTypes['couponOffline']['couponNo'], $couponNo);

        $sort['fieldName'] = 'coc.regDt';
        $sort['sortMode'] = 'desc';

        $this->db->strField = "coc.* ";
        $this->db->strWhere = implode(' AND ', gd_isset($this->arrWhere));
        $this->db->strOrder = $sort['fieldName'] . ' ' . $sort['sortMode'];

        $query = $this->db->query_complete();
        $strSQL = 'SELECT ' . array_shift($query) . ' FROM ' . DB_COUPON_OFFLINE_CODE . ' as coc ' . implode(' ', $query);

        $data = $this->db->query_fetch($strSQL, $this->arrBind);

        return gd_htmlspecialchars_stripslashes(gd_isset($data));

    }

    /**
     * 여러(Array) 쿠폰데이터를 한글로 변경한 별도의 Array 생성
     *
     * @param array $couponArrData 쿠폰의 리스트
     *
     * @return array $convertCouponArrData 쿠폰의 리스트의 표시용 한글 변경
     *
     * @author su
     */
    public function convertCouponAdminArrData($couponArrData)
    {
        $mileage = gd_mileage_give_info();
        $mileageName = $mileage['basic']['name'];
        $deposit = gd_policy('member.depositConfig');
        $depositName = $deposit['name'];
        foreach ($couponArrData as $key => $val) {
            if ($val['couponKind'] == 'online') {
                $convertCouponArrData[$key]['couponKind'] = '일반쿠폰';
            } else if ($val['couponKind'] == 'offline') {
                $convertCouponArrData[$key]['couponKind'] = '페이퍼쿠폰';
            }
            if ($val['couponKind'] == 'offline' && $val['couponAuthType'] == 'n') {
                $couponAuthNumber = $this->getCouponOfflineInfo($val['couponNo'], 'couponOfflineCodeUser');
                $convertCouponArrData[$key]['couponAuthNumber'] = $couponAuthNumber['couponOfflineCodeUser'];
            }

            if ($val['couponType'] == 'y') {
                $convertCouponArrData[$key]['couponType'] = '발급중';
            } else if ($val['couponType'] == 'n') {
                $convertCouponArrData[$key]['couponType'] = '일시중지';
            } else if($val['couponType'] == 'f') {
                $convertCouponArrData[$key]['couponType'] = '발급종료';
            }
            if ($val['couponSaveType'] == 'down') {
                $convertCouponArrData[$key]['couponSaveType'] = '회원다운로드';
            } else if ($val['couponSaveType'] == 'auto') {
                $convertCouponArrData[$key]['couponSaveType'] = '자동발급';
            } else if ($val['couponSaveType'] == 'manual') {
                $convertCouponArrData[$key]['couponSaveType'] = '수동발급';
            }
            if ($val['couponUseType'] == 'product') {
                $convertCouponArrData[$key]['couponUseType'] = '상품적용쿠폰';
            } else if ($val['couponUseType'] == 'order') {
                $convertCouponArrData[$key]['couponUseType'] = '주문적용쿠폰';
            } else if ($val['couponUseType'] == 'delivery') {
                $convertCouponArrData[$key]['couponUseType'] = '배송비적용쿠폰';
            } else if ($val['couponUseType'] == 'gift') {
                $convertCouponArrData[$key]['couponUseType'] = '기프트쿠폰';
            }
            if ($val['couponUsePeriodType'] == 'period') {
                $convertCouponArrData[$key]['couponUsePeriodType'] = '쿠폰사용가능기간-날짜';
                $convertCouponArrData[$key]['useEndDate'] = date('Y-m-d H:i', strtotime($val['couponUsePeriodStartDate'])) . '~' . date('Y-m-d H:i', strtotime($val['couponUsePeriodEndDate']));
            } else if ($val['couponUsePeriodType'] == 'day') {
                $couponEndDate = '';
                $convertCouponArrData[$key]['couponUsePeriodType'] = '쿠폰사용가능기간-기간';
                if (strtotime($val['couponUseDateLimit']) > 0) {
                    $couponEndDate = '<br/>사용가능일 : ' . $val['couponUseDateLimit'];
                }
                $convertCouponArrData[$key]['useEndDate'] = '발급일로부터 ' . $val['couponUsePeriodDay'] . '일까지' . $couponEndDate;
            }
            if ($val['couponKindType'] == 'sale') {
                $convertCouponArrData[$key]['couponKindType'] = '상품할인';
            } else if ($val['couponKindType'] == 'add') {
                $convertCouponArrData[$key]['couponKindType'] = $mileageName . '적립';
            } else if ($val['couponKindType'] == 'delivery') {
                $convertCouponArrData[$key]['couponKindType'] = '배송비할인';
            } else if ($val['couponKindType'] == 'deposit') {
                $convertCouponArrData[$key]['couponKindType'] = $depositName . '지급';
            }
            if ($val['couponBenefitFixApply'] == 'all') {
                $convertCouponArrData[$key]['couponKindType'] = '수량별 ' . $convertCouponArrData[$key]['couponKindType'];
            }
            if ($val['couponDeviceType'] == 'all') {
                $convertCouponArrData[$key]['couponDeviceType'] = 'PC+모바일';
            } else if ($val['couponDeviceType'] == 'pc') {
                $convertCouponArrData[$key]['couponDeviceType'] = 'PC';
            } else if ($val['couponDeviceType'] == 'mobile') {
                $convertCouponArrData[$key]['couponDeviceType'] = '모바일';
            }
            if ($val['couponBenefitType'] == 'percent') {
                $convertCouponArrData[$key]['couponBenefit'] = number_format($val['couponBenefit']) . ' %';
                if ($val['couponMaxBenefitType'] == 'y') {
                    $convertCouponArrData[$key]['couponMaxBenefit'] = '최대 할인액 : ' . gd_currency_symbol() . ' ' . gd_money_format($val['couponMaxBenefit']) . ' ' . gd_currency_string();
                } else {
                    $convertCouponArrData[$key]['couponMaxBenefit'] = '';
                }
            } else if ($val['couponBenefitType'] == 'fix') {
                $convertCouponArrData[$key]['couponBenefit'] = gd_currency_symbol() . ' ' . gd_money_format($val['couponBenefit']) . ' ';
                if ($val['couponKindType'] == 'add') {
                    $convertCouponArrData[$key]['couponBenefit'] .= Globals::get('gSite.member.mileageBasic.unit');
                } elseif ($val['couponKindType'] == 'deposit') {
                    $convertCouponArrData[$key]['couponBenefit'] .= Globals::get('gSite.member.depositConfig.unit');
                } else {
                    $convertCouponArrData[$key]['couponBenefit'] .= gd_currency_string();
                }
            }
            if ($val['couponMinOrderPrice'] > 0) {
                $convertCouponArrData[$key]['couponMinOrderPrice'] = '구매금액이 ' . gd_currency_symbol() . ' ' . gd_money_format($val['couponMinOrderPrice']) . ' ' . gd_currency_string() . ' 이상 결제시 사용가능';
            } else {
                $convertCouponArrData[$key]['couponMinOrderPrice'] = '';
            }
            if ($val['couponApplyDuplicateType'] == 'y') {
                $convertCouponArrData[$key]['couponApplyDuplicateType'] = '중복 사용가능';
            } else {
                $convertCouponArrData[$key]['couponApplyDuplicateType'] = '중복 사용불가';
            }
            if ($val['couponImageType'] == 'self') {
                $convertCouponArrData[$key]['couponImage'] = $this->getCouponImageData($val['couponImage']);
            } else {
                $convertCouponArrData[$key]['couponImage'] = PATH_ADMIN_GD_SHARE . 'img/coupon.png';
            }

            //노출 미노출
            $now = date('Y-m-d H:i:s');
            if($val['couponType'] == 'n' || ($val['couponUsePeriodType'] == 'day' && $val['couponUseDateLimit'] > '0000-00-00 00:00:00' && $val['couponUseDateLimit'] < $now) || ($val['couponUsePeriodType'] == 'period' && $val['couponUsePeriodEndDate'] < $now) || ($val['couponDisplayType'] == 'y' && $val['couponDisplayStartDate'] > $now) || ($val['couponDisplayType'] == 'y' && $val['couponDisplayEndDate'] < $now)) {
                $convertCouponArrData[$key]['displayFl'] = '미노출';
            } else {
                $convertCouponArrData[$key]['displayFl'] = '노출';
            }

            // 레이어에서 데이터 선택시 사용하기 위해 추가
            $convertCouponArrData[$key]['couponNo'] = $val['couponNo'];
            $convertCouponArrData[$key]['couponNm'] = $val['couponNm'];
            $convertCouponArrData[$key]['regDt'] = $val['regDt'];
        }

        return $convertCouponArrData;
    }

    /**
     * 오프라인쿠폰코드 자동생성
     *
     * @author su
     */
    public function setCouponOfflineAutoCode($couponNo, $couponAmount)
    {
        if ($couponAmount > LIMIT_OFFLINE_COUPON_AMOUNT) {
            $couponAmount = LIMIT_OFFLINE_COUPON_AMOUNT;
        }
        $string = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ';
        for ($i = 1; $i <= $couponAmount; $i++) {
            $rand = rand(0, strlen($string) - 1);
            $couponOfflineCode = strtoupper(md5(uniqid($couponNo . $rand . $i)));
            $couponOfflineCodeUser = substr($couponOfflineCode, 0, 12);
            $couponOfflineCodeKey = substr($couponOfflineCode, 12);

            $arrBind['param'] = "couponOfflineCode, couponOfflineCodeUser, couponOfflineCodeKey, couponNo, couponOfflineCodeSaveType, couponOfflineInsertAdminId,managerNo";
            $this->db->bind_param_push($arrBind['bind'], 's', $couponOfflineCode);
            $this->db->bind_param_push($arrBind['bind'], 's', $couponOfflineCodeUser);
            $this->db->bind_param_push($arrBind['bind'], 's', $couponOfflineCodeKey);
            $this->db->bind_param_push($arrBind['bind'], 'i', $couponNo);
            $this->db->bind_param_push($arrBind['bind'], 's', 'n');
            $this->db->bind_param_push($arrBind['bind'], 's', Session::get('manager.managerId'));
            $this->db->bind_param_push($arrBind['bind'], 'i', Session::get('manager.sno'));
            $this->db->set_insert_db(DB_COUPON_OFFLINE_CODE, $arrBind['param'], $arrBind['bind'], 'y');
            unset($arrBind);
        }
    }

    /**
     * 오프라인쿠폰코드 엑셀파일 등록
     *
     * @author su
     */
    public function setCouponOfflineExcelCode($couponNo)
    {
        $arrData = [];
        $arrData['couponNo'] = $couponNo;
        $arrData['couponOfflineCodeSaveType'] = 'n';
        $arrData['couponOfflineInsertAdminId'] = Session::get('manager.managerId');
        $arrData['managerNo'] = Session::get('manager.sno');

        // --- 엑셀 class
        $excel = \App::load('\\Component\\Excel\\ExcelDataConvert');
        $result = $excel->setCouponOfflineExcelCodeUp($arrData);

        return $result;
    }

    /**
     * 오프라인쿠폰코드 1개 인증번호 사용 등록
     *
     * @author su
     */
    public function setCouponOfflineDirectCode($couponNo, $authNumber)
    {
        // 인증번호 중복체크
        $duplicateOfflineCode = $this->checkOfflineCode($authNumber);
        if ($duplicateOfflineCode) {
            throw new \Exception(__('쿠폰번호가 이미 존재합니다.<br/>생성된 쿠폰에 가셔서 쿠폰번호를 재생성하셔야 합니다.'));
        }
        // 인증번호 저장
        $arrBind['param'] = "couponOfflineCode, couponOfflineCodeUser, couponOfflineCodeKey, couponNo, couponOfflineCodeSaveType, couponOfflineInsertAdminId,managerNo";
        $this->db->bind_param_push($arrBind['bind'], 's', $authNumber);
        $this->db->bind_param_push($arrBind['bind'], 's', $authNumber);
        $this->db->bind_param_push($arrBind['bind'], 's', '');
        $this->db->bind_param_push($arrBind['bind'], 'i', $couponNo);
        $this->db->bind_param_push($arrBind['bind'], 's', 'n');
        $this->db->bind_param_push($arrBind['bind'], 's', Session::get('manager.managerId'));
        $this->db->bind_param_push($arrBind['bind'], 'i', Session::get('manager.sno'));
        $this->db->set_insert_db(DB_COUPON_OFFLINE_CODE, $arrBind['param'], $arrBind['bind'], 'y');
        unset($arrBind);
    }

    /**
     * 쿠폰 상태 변경하기
     *
     * @author su
     */
    public function changeCouponType()
    {
        $getValue = Request::post()->toArray();
        $arrData['couponType'] = $getValue['couponType'];
        $arrData['couponNo'] = $getValue['couponNo'];
        $arrBind = $this->db->get_binding(DBTableField::tableCoupon(), $arrData, 'update', array_keys($arrData), ['couponNo']);
        $this->db->bind_param_push($arrBind['bind'], 'i', $arrData['couponNo']);
        $result = $this->db->set_update_db(DB_COUPON, $arrBind['param'], 'couponNo = ?', $arrBind['bind'], false);
        if ($result > 0) {
            echo json_encode(['result' => 'ok', 'msg' => __('쿠폰 발급상태가 변경되었습니다.')]);
        } else {
            echo json_encode(['result' => 'fail', 'msg' => __('다시 시도해 주세요.')]);
        }
    }

    /**
     * 쿠폰 삭제하기
     *
     * @author su
     */
    public function deleteCoupon()
    {
        $getValue = Request::post()->toArray();
        foreach ($getValue['chkCoupon'] as $key => $val) {
            $couponNo = (int)$val;
            if (Validator::number($couponNo, null, null, true) === false) {
                throw new \Exception(__('쿠폰번호 인자가 잘못되었습니다.'), 500);
            }
            $countMemberCouponData = $this->getMemberCouponTotalCount($couponNo);
            if ($countMemberCouponData > 0) {
                throw new \Exception(__('발급된 쿠폰이 존재하여 쿠폰을 삭제할 수 없습니다.'));
            }

            $countEventCouponData = $this->getEventCouponTotalCount($couponNo);
            if ($countEventCouponData > 0) {
                throw new \Exception(__('이벤트에 등록된 쿠폰(장바구니 알림, 출석체크, 회원정보 수정 이벤트 등)이 존재하여 쿠폰을 삭제할 수 없습니다.'));
            }

            $arrBind = [
                'i',
                $couponNo,
            ];
            // --- 삭제
            $this->db->set_delete_db(DB_COUPON, 'couponNo = ?', $arrBind);

            // 페이퍼쿠폰 삭제시 등록된 쿠폰코드 전체 삭제
            if ($getValue['mode'] == 'deleteCouponOfflineList') {
                $this->deleteAllCouponOfflineCode($couponNo);
            }
        }
    }

    /**
     * 회원쿠폰 삭제하기
     *
     * @author su
     */
    public function deleteMemberCoupon()
    {
        $getValue = Request::post()->toArray();
        foreach ($getValue['chkMemberCoupon'] as $key => $val) {
            if ($val) {
                $memberCouponNo = (int)$val;
                $memberCoupon = $this->getMemberCouponInfo($memberCouponNo, 'c.couponNo, mc.memberCouponState, mc.memberCouponUseDate');
                if ($memberCoupon['memberCouponState'] == 'order' || $memberCoupon['memberCouponState'] == 'coupon') {
                    throw new \Exception(__('사용된 회원쿠폰은 삭제할 수 없습니다.'));
                }
                if (strtotime($memberCoupon['memberCouponUseDate']) > 0) {
                    throw new \Exception(__('주문에 사용된 회원쿠폰은 삭제할 수 없습니다.'));
                }
                if (Validator::number($memberCouponNo, null, null, true) === false) {
                    throw new \Exception(__('회원쿠폰번호 인자가 잘못되었습니다.'), 500);
                }
                $arrBind = [
                    'i',
                    $memberCouponNo,
                ];
                // --- 회원 정보 삭제
                $this->db->set_delete_db(DB_MEMBER_COUPON, 'memberCouponNo = ?', $arrBind);
                // 발급된 쿠폰 수 감소
                $this->setCouponMemberDeleteCount($memberCoupon['couponNo']);
                $coupon = $this->getCouponInfo($memberCoupon['couponNo'], 'couponType');
                if($coupon['couponType'] == 'f') $this->checkCouponType($memberCoupon['couponNo'], 'f');
            }
        }
    }

    /**
     * 오프라인 쿠폰 코드 삭제하기
     *
     * @author su
     */
    public function deleteCouponOfflineCode($authCodeArr)
    {
        foreach ($authCodeArr as $key => $val) {
            $arrBind = [
                's',
                $val,
            ];
            // --- 회원 정보 삭제
            $this->db->set_delete_db(DB_COUPON_OFFLINE_CODE, 'couponOfflineCode = ?', $arrBind);
        }

        return true;
    }

    /**
     * 오프라인 쿠폰 코드 전체 삭제하기
     *
     * @param integer $couponNo  쿠폰 번호
     *
     * @return bool
     *
     * @author haky2@godo.co.kr
     */
    public function deleteAllCouponOfflineCode($couponNo)
    {
        $arrBind = [
            's',
            $couponNo,
        ];
        // --- 쿠폰 코드 정보 삭제
        $this->db->set_delete_db(DB_COUPON_OFFLINE_CODE, 'couponNo = ?', $arrBind);

        return true;
    }

    /**
     * 오프라인 쿠폰 코드 번호 확인
     *
     * @author su
     */
    public function checkOfflineCode($offlineCode)
    {
        $strSQL = 'SELECT couponOfflineCodeUser FROM ' . DB_COUPON_OFFLINE_CODE . ' WHERE couponOfflineCodeUser = ? ';
        $this->db->bind_param_push($arrBind, $this->fieldTypes['couponOffline']['couponOfflineCodeUser'], $offlineCode);
        $data = $this->db->query_fetch($strSQL, $arrBind);
        unset($arrBind);
        if ($this->db->num_rows() > 0) {
            return true;
        }
    }


    /**
     * 쿠폰 저장
     *
     * @author su
     */
    public function saveCoupon(&$arrData, &$files)
    {
        if (substr($arrData['mode'], 0, 6) == 'modify') {
            $couponNo = (int)$arrData['couponNo'];
            $countMemberCouponData = $this->getMemberCouponTotalCount($couponNo);
            if ($countMemberCouponData > 0) {
                $this->checkCouponType($couponNo);
                throw new \Exception(__('발급된 쿠폰이 존재하여 쿠폰을 수정할 수 없습니다.'));
            }

        }
        // 쿠폰 허용 회원등급
        if ($arrData['couponApplyMemberGroup']) {
            $arrData['couponApplyMemberGroup'] = implode(INT_DIVISION, $arrData['couponApplyMemberGroup']);
        } else {
            $arrData['couponApplyMemberGroup'] = null;
        }
        // 쿠폰 사용 회원등급
        if (!$arrData['couponUseMemberGroup']) {
            $arrData['couponUseMemberGroup'] = null;
        }
        // 쿠폰 허용 공급사
        if ($arrData['couponApplyProvider']) {
            $arrData['couponApplyProvider'] = implode(INT_DIVISION, $arrData['couponApplyProvider']);
        }
        // 쿠폰 허용 카테고리
        if ($arrData['couponApplyCategory']) {
            $arrData['couponApplyCategory'] = implode(INT_DIVISION, $arrData['couponApplyCategory']);
        }
        // 쿠폰 허용 브랜드
        if ($arrData['couponApplyBrand']) {
            $arrData['couponApplyBrand'] = implode(INT_DIVISION, $arrData['couponApplyBrand']);
        }
        // 쿠폰 허용 상품
        if ($arrData['couponApplyGoods']) {
            $arrData['couponApplyGoods'] = implode(INT_DIVISION, $arrData['couponApplyGoods']);
        }
        // 쿠폰 제외 공급사
        if ($arrData['couponExceptProvider']) {
            $arrData['couponExceptProvider'] = implode(INT_DIVISION, $arrData['couponExceptProvider']);
        }
        // 쿠폰 제외 카테고리
        if ($arrData['couponExceptCategory']) {
            $arrData['couponExceptCategory'] = implode(INT_DIVISION, $arrData['couponExceptCategory']);
        }
        // 쿠폰 제외 브랜드
        if ($arrData['couponExceptBrand']) {
            $arrData['couponExceptBrand'] = implode(INT_DIVISION, $arrData['couponExceptBrand']);
        }
        // 쿠폰 제외 상품
        if ($arrData['couponExceptGoods']) {
            $arrData['couponExceptGoods'] = implode(INT_DIVISION, $arrData['couponExceptGoods']);
        }
        // 쿠폰 수량 적용 옵션
        if (!$arrData['couponBenefitFixApply']) {
            $arrData['couponBenefitFixApply'] = 'one';
        }

        // 쿠폰 이미지
        if (ArrayUtils::isEmpty($files['couponImage']) === false) {
            $file = $files['couponImage'];
            if ($file['error'] == 0 && $file['size']) {
                $saveFileName = substr(md5(microtime()), 0, 8) . rand(100, 999);
                $this->storage()->upload($file['tmp_name'], $saveFileName);
                $arrData['couponImage'] = $saveFileName;
            }
        }
        // Validation
        $validator = new Validator();
        if (substr($arrData['mode'], 0, 6) == 'modify') {
            $validator->add('couponNo', 'number', true); // 쿠폰 고유번호
        } else {
            $arrData['couponInsertAdminId'] = Session::get('manager.managerId');
            $arrData['managerNo'] = Session::get('manager.sno');
            $validator->add('couponInsertAdminId', 'userid', true); // 쿠폰등록자-아이디
            $validator->add('managerNo', 'number', true);
        }
        $validator->add('mode', 'alpha', true); // 모드
        $validator->add('couponKind', 'alpha', true); // 종류–온라인쿠폰(‘online’),페이퍼쿠폰(‘offline’)
        $validator->add('couponType', 'alpha', true); // 사용여부–사용(‘y’),정지(‘n’),종료(‘f’)

        $validator->add('couponUseType', 'alpha', true); // 쿠폰유형–상품쿠폰(‘product’),주문쿠폰(‘order’),배송비쿠폰('delivery')
        // 사용구분에 따른 체크
        // 상품 적용 쿠폰
        if ($arrData['couponUseType'] == 'product') {
            if ($arrData['couponKindType'] == 'delivery') {
                throw new \Exception(__('상품적용 쿠폰은 배송비할인 조건을 설정할 수 없습니다.'));
            }
            $validator->add('couponApplyProductType', 'alpha', true); // 쿠폰적용상품–전체(‘all’),공급사(‘provider’),카테고리(‘category’),브랜드(‘brand’),상품(‘goods’)
            if ($arrData['couponApplyProductType'] == 'provider') { // 공급사 쿠폰 적용
                $validator->add('couponApplyProvider', null, true); // 쿠폰적용공급사
                $arrData['couponApplyCategory'] = null;
                $arrData['couponApplyBrand'] = null;
                $arrData['couponApplyGoods'] = null;
                $validator->add('couponApplyCategory', null, null); // 쿠폰적용카테고리
                $validator->add('couponApplyBrand', null, null); // 쿠폰적용브랜드
                $validator->add('couponApplyGoods', null, null); // 쿠폰적용상품
            } else if ($arrData['couponApplyProductType'] == 'category') { // 카테고리 쿠폰 적용
                $validator->add('couponApplyCategory', null, true); // 쿠폰적용카테고리
                $arrData['couponApplyProvider'] = null;
                $arrData['couponApplyBrand'] = null;
                $arrData['couponApplyGoods'] = null;
                $validator->add('couponApplyProvider', null, null); // 쿠폰적용공급사
                $validator->add('couponApplyBrand', null, null); // 쿠폰적용브랜드
                $validator->add('couponApplyGoods', null, null); // 쿠폰적용상품
            } else if ($arrData['couponApplyProductType'] == 'brand') { // 브랜드 쿠폰 적용
                $validator->add('couponApplyBrand', null, true); // 쿠폰적용브랜드
                $arrData['couponApplyProvider'] = null;
                $arrData['couponApplyCategory'] = null;
                $arrData['couponApplyGoods'] = null;
                $validator->add('couponApplyProvider', null, null); // 쿠폰적용공급사
                $validator->add('couponApplyCategory', null, null); // 쿠폰적용카테고리
                $validator->add('couponApplyGoods', null, null); // 쿠폰적용상품s
            } else if ($arrData['couponApplyProductType'] == 'goods') { // 상품 쿠폰 적용
                $validator->add('couponApplyGoods', null, true); // 쿠폰적용상품
                $arrData['couponApplyProvider'] = null;
                $arrData['couponApplyCategory'] = null;
                $arrData['couponApplyBrand'] = null;
                $validator->add('couponApplyProvider', null, null); // 쿠폰적용공급사
                $validator->add('couponApplyCategory', null, null); // 쿠폰적용카테고리
                $validator->add('couponApplyBrand', null, null); // 쿠폰적용브랜드
            } else { // 전체 적용
                $arrData['couponApplyProvider'] = null;
                $arrData['couponApplyCategory'] = null;
                $arrData['couponApplyBrand'] = null;
                $arrData['couponApplyGoods'] = null;
                $validator->add('couponApplyProvider', null, null); // 쿠폰적용공급사
                $validator->add('couponApplyCategory', null, null); // 쿠폰적용카테고리
                $validator->add('couponApplyBrand', null, null); // 쿠폰적용브랜드
                $validator->add('couponApplyGoods', null, null); // 쿠폰적용상품
            }
            // 체크박스에 대한 초기화
            gd_isset($arrData['couponExceptProviderType'], 'n');
            gd_isset($arrData['couponExceptCategoryType'], 'n');
            gd_isset($arrData['couponExceptBrandType'], 'n');
            gd_isset($arrData['couponExceptGoodsType'], 'n');
            $validator->add('couponExceptProviderType', 'yn', null); // 쿠폰제외공급사여부-사용(‘y’)
            if ($arrData['couponExceptProviderType'] == 'y') {
                $validator->add('couponExceptProvider', null, true); // 쿠폰제외공급사
            } else {
                $arrData['couponExceptProvider'] = null;
                $validator->add('couponExceptProvider', null, true); // 쿠폰제외공급사
            }
            $validator->add('couponExceptCategoryType', 'yn', null); // 쿠폰제외카테고리여부-사용(‘y’)
            if ($arrData['couponExceptCategoryType'] == 'y') {
                $validator->add('couponExceptCategory', null, true); // 쿠폰제외카테고리
            } else {
                $arrData['couponExceptCategory'] = null;
                $validator->add('couponExceptCategory', null, true); // 쿠폰제외카테고리
            }
            $validator->add('couponExceptBrandType', 'yn', null); // 쿠폰제외브랜드여부-사용(‘y’)
            if ($arrData['couponExceptBrandType'] == 'y') {
                $validator->add('couponExceptBrand', null, true); // 쿠폰제외브랜드
            } else {
                $arrData['couponExceptBrand'] = null;
                $validator->add('couponExceptBrand', null, true); // 쿠폰제외브랜드
            }
            $validator->add('couponExceptGoodsType', 'yn', null); // 쿠폰제외상품여부-사용(‘y’)
            if ($arrData['couponExceptGoodsType'] == 'y') {
                $validator->add('couponExceptGoods', null, true); // 쿠폰제외상품
            } else {
                $arrData['couponExceptGoods'] = null;
                $validator->add('couponExceptGoods', null, true); // 쿠폰제외상품
            }

            // 주문 적용 쿠폰
        } else if ($arrData['couponUseType'] == 'order') {
            if ($arrData['couponKindType'] == 'delivery') {
                throw new \Exception(__('주문적용 쿠폰은 배송비할인 조건을 설정할 수 없습니다.'));
            }
            if ($arrData['couponApplyProvider'] || $arrData['couponApplyCategory'] || $arrData['couponApplyBrand'] || $arrData['couponApplyGoods']) {
                throw new \Exception(__('주문적용 쿠폰은 발급/사용 사용 조건을 설정할 수 없습니다.'));
            }
            if ($arrData['couponExceptProviderType'] || $arrData['couponExceptProvider'] || $arrData['couponExceptCategoryType'] || $arrData['couponExceptCategory'] || $arrData['couponExceptBrandType'] || $arrData['couponExceptBrand'] || $arrData['couponExceptGoodsType'] || $arrData['couponExceptGoods']) {
                throw new \Exception(__('주문적용 쿠폰은 발급/사용 사용 조건을 설정할 수 없습니다.'));
            }
            if ($arrData['couponBenefitType'] == 'fix') {
                throw new \Exception(__('주문적용 쿠폰은 정율(%) 할인만 가능합니다.'));
            }
            // 배송비 쿠폰
        } else if ($arrData['couponUseType'] == 'delivery') {
            if ($arrData['couponKindType'] != 'delivery') {
                throw new \Exception(__('배송비적용 쿠폰은 배송비할인 조건만 설정할 수 있습니다.'));
            }
            if ($arrData['couponApplyProvider'] || $arrData['couponApplyCategory'] || $arrData['couponApplyBrand'] || $arrData['couponApplyGoods']) {
                throw new \Exception(__('배송비할인 쿠폰은 발급/사용 조건을 설정할 수 없습니다.'));
            }
            if ($arrData['couponExceptProviderType'] || $arrData['couponExceptProvider'] || $arrData['couponExceptCategoryType'] || $arrData['couponExceptCategory'] || $arrData['couponExceptBrandType'] || $arrData['couponExceptBrand'] || $arrData['couponExceptGoodsType'] || $arrData['couponExceptGoods']) {
                throw new \Exception(__('배송비할인 쿠폰은 발급/사용 조건을 설정할 수 없습니다.'));
            }
            if ($arrData['couponBenefitType'] == 'percent') {
                throw new \Exception(__('배송비할인 쿠폰은 정액(원) 할인만 가능합니다.'));
            }
        }

        $validator->add('couponSaveType', 'alpha', true); // 발급방식–회원다운로드(‘down’),자동발급(‘auto’),수동발급(‘manual’)
        // 발급방식에 따른 체크
        // 회원다운로드 일 경우
        if ($arrData['couponSaveType'] == 'down') {
            if ($arrData['couponUseType'] == 'order' || $arrData['couponUseType'] == 'delivery') { // 쿠폰유형–상품쿠폰(‘product’),주문쿠폰(‘order’),배송비쿠폰('delivery')
                throw new \Exception(__('회원다운로드 발급은 상품 쿠폰만 생성할 수 있습니다.'));
            }
            $validator->add('couponDisplayType', 'yn', true); // 상세노출기간종류–즉시(‘n’),예약(‘y’)
            if ($arrData['couponDisplayType'] == 'y') {
                $validator->add('couponDisplayStartDate', 'datetime', true); // 노출기간-시작
                $validator->add('couponDisplayEndDate', 'datetime', true); // 노출기간-끝
            }
            $validator->add('couponAmountType', 'yn', true); // 발급수량종류–무제한(‘n’), 제한(‘y’)
            if ($arrData['couponAmountType'] == 'y') {
                $validator->add('couponAmount', 'number', true); // 발급수량
            }
            $validator->add('couponApplyMemberGroup', null, null); // 발급가능회원등급
            gd_isset($arrData['couponApplyMemberGroupDisplayType'], 'n');
            $validator->add('couponApplyMemberGroupDisplayType', 'yn', null); // 발급가능회원등급만노출–사용(‘y’)

            $validator->add('couponUseMemberGroup', null, null); // 사용가능회원등급
            // 자동발급 일 경우
        } else if ($arrData['couponSaveType'] == 'auto') {
            // 체크박스에 대한 초기화
            gd_isset($arrData['couponEventOrderFirstType'], 'n');
            gd_isset($arrData['couponEventOrderSmsType'], 'n');
            gd_isset($arrData['couponEventFirstSmsType'], 'n');
            gd_isset($arrData['couponEventBirthSmsType'], 'n');
            gd_isset($arrData['couponEventMemberSmsType'], 'n');
            gd_isset($arrData['couponEventAttendanceSmsType'], 'n');
            gd_isset($arrData['couponEventMemberModifySmsType'], 'n');
            gd_isset($arrData['couponEventWakeSmsType'], 'n');
            $validator->add('couponEventType', 'alpha', true); // 자동발급쿠폰 종류-첫구매(‘first’),구매감사(‘order’),생일축하(‘birth’),회원가입(‘join’),출석체크(‘attend’),장바구니알림(‘cartRemind’),회원정보수정(‘memberModifyEvent’),휴면회원해제(‘wakeEvent’)
            if ($arrData['couponEventType'] == 'first') {
                $validator->add('couponEventFirstSmsType', 'yn', null); // 첫구매쿠폰SMS발송여부-함(‘y’)
            } else if ($arrData['couponEventType'] == 'order') {
                $validator->add('couponEventOrderFirstType', 'yn', null); // 구매감사쿠폰과 첫구매쿠폰의 중복안함-(‘y’)
                $validator->add('couponEventOrderSmsType', 'yn', null); // 구매감사쿠폰SMS발송여부-함(‘y’)
            } else if ($arrData['couponEventType'] == 'birth') {
                $validator->add('couponEventBirthSmsType', 'yn', null); // 생일쿠폰SMS발송여부-함(‘y’)
            } else if ($arrData['couponEventType'] == 'join') {
                $validator->add('couponEventMemberSmsType', 'yn', null); // 회원가입쿠폰SMS발송여부-함(‘y’)
            } else if ($arrData['couponEventType'] == 'attend') {
                $validator->add('couponEventAttendanceSmsType', 'yn', null); // 출석체크쿠폰SMS발송여부-함(‘y’)
            } else if ($arrData['couponEventType'] == 'cartRemind') {
                // 장바구니 알림은 SMS 발송 여부 체크 없음
            } else if ($arrData['couponEventType'] == 'memberModifyEvent') {
                $validator->add('couponEventMemberModifySmsType', 'yn', null); // 회원정보수정쿠폰SMS발송여부-함(‘y’)
            } else if ($arrData['couponEventType'] == 'wake') {
                $validator->add('couponEventWakeSmsType', 'yn', null); // 휴먼회원해제쿠폰SMS발송여부-함(‘y’)
            }
            if ($arrData['couponUsePeriodType'] != 'day') {
                throw new \Exception(__('자동 발급은 사용기간이 발급일으로만 가능합니다.'));
            }
            if ($arrData['couponDisplayType']) {
                throw new \Exception(__('상품리스트 / 상품상세 쿠폰발급설정은 회원다운로드 발급만 설정할 수 있습니다.'));
            }
            $validator->add('couponAmountType', 'yn', true); // 발급수량종류–무제한(‘n’), 제한(‘y’)
            if ($arrData['couponAmountType'] == 'y') {
                $validator->add('couponAmount', 'number', true); // 발급수량
            }
            $validator->add('couponApplyMemberGroup', null, null); // 발급가능회원등급
            gd_isset($arrData['couponApplyMemberGroupDisplayType'], 'n');
            $validator->add('couponApplyMemberGroupDisplayType', 'yn', null); // 발급가능회원등급만노출–사용(‘y’)

            $validator->add('couponUseMemberGroup', null, null); // 사용가능회원등급
            // 수동발급 일 경우
        } else if ($arrData['couponSaveType'] == 'manual') {
            if ($arrData['couponDisplayType']) {
                throw new \Exception(__('상품리스트 / 상품상세 쿠폰발급설정은 회원다운로드 발급만 설정할 수 있습니다.'));
            }
            if ($arrData['couponAmountType'] || $arrData['couponAmount']) {
                throw new \Exception(__('수동 발급은 발급수량 제한이 없습니다.'));
            }
            if ($arrData['couponSaveDuplicateType'] || $arrData['couponSaveDuplicateLimitType'] || $arrData['couponSaveDuplicateLimit']) {
                throw new \Exception(__('수동발급은 재발급 설정을 할 수 없습니다.'));
            }
        }

        $validator->add('couponNm', null, true); // 쿠폰명
        //        $validator->add('couponNm', 'maxlen', true, null, 30); // 쿠폰명 최대 30자
        $validator->add('couponDescribed', '', null); // 쿠폰설명
        //        $validator->add('couponDescribed', 'maxlen', null, null, 50); // 쿠폰설명 최대 50자
        $validator->add('couponUsePeriodType', null, true); // 사용기간–기간(‘period’),일(‘day’)
        if ($arrData['couponUsePeriodType'] == 'period') { // 사용기간 설정이 기간 설정일 경우
            $validator->add('couponUsePeriodStartDate', 'datetime', true); // 사용기간-시작
            $validator->add('couponUsePeriodEndDate', 'datetime', true); // 사용기간-끝
            if ($arrData['couponUsePeriodStartDate'] >= $arrData['couponUsePeriodEndDate']) { // 사용기간-시작이 사용기간-끝보다 클 수 없습니다.
                throw new \Exception(__('사용기간 시작일은 사용기간 종료일보다 클 수 없습니다.'));
            }
        } else { // 사용기간 설정이 일자 설정일 경우
            $validator->add('couponUsePeriodDay', 'number', true); // 사용가능일
            $validator->add('couponUseDateLimit', 'datetime', null); // 사용제한 일자
        }
        $validator->add('couponKindType', 'alpha', true); // 해당구분–상품할인(‘sale’),마일리지적립(‘add’),배송비할인('delivery')
        $validator->add('couponDeviceType', 'alpha', true); // 사용범위–PC+모바일(‘all’),PC(‘pc’),모바일(‘mobile’)
        $validator->add('couponBenefit', null, true); // 혜택금액(할인,적립)액–소수점 2자리 가능
        $validator->add('couponBenefitType', 'alpha', true); // 혜택금액종류-정율%(‘percent’),정액-원(‘fix’)–금액은 $등 가능
        $validator->add('couponBenefitFixApply', 'alpha', true); // 정액쿠폰수량별적용-수량별로적용(‘all’),기존대로 상품에적용(‘one’)
        if ($arrData['couponBenefitType'] == 'percent') {
            if ($arrData['couponBenefit'] < 0 || $arrData['couponBenefit'] > 100) {
                throw new \Exception(__('정률(%s)의 경우 숫자 %d까지 입력하실 수 있습니다.','%',100));
            }
        }
        gd_isset($arrData['couponMaxBenefitType'], 'n');
        $validator->add('couponMaxBenefitType', 'yn', null); // 최대혜택금액여부–사용(‘y’)
        if ($arrData['couponMaxBenefitType'] == 'y') {
            $validator->add('couponMaxBenefit', null, true); // 최대혜택금액–소수점2자리가능
        }

        // 사용기간이 기간제(일자제 아님)이고 노출기간 종료일보다 사용기간 종료일이 클 경우
        if (($arrData['couponUsePeriodType'] == 'period') && $arrData['couponDisplayEndDate'] && $arrData['couponUsePeriodEndDate'] && ($arrData['couponDisplayEndDate'] > $arrData['couponUsePeriodEndDate'])) {
            throw new \Exception(__('쿠폰 발급 종료일자가 쿠폰 사용 만료일보다 길 수 없습니다.'));
        }

        $validator->add('couponImageType', 'alpha', true); // 이미지종류–기본(‘basic’),직접(‘self’)
        if ($arrData['couponImageType'] == 'self') {
            $validator->add('couponImage', null, true); // 이미지
        }

        //사용기간만료시 SMS발송
        gd_isset($arrData['couponLimitSmsFl'], 'n');
        $validator->add('couponLimitSmsFl', 'yn', null); // SMS발송–사용(‘y’)

        //결제수단 사용제한
        gd_isset($arrData['couponUseAblePaymentType'], 'all');
        $validator->add('couponUseAblePaymentType', 'alpha', true); // 제한없음(‘all’), 무통장만(‘bank’)

        if ($arrData['couponSaveType'] != 'manual') { // 수동 발급이 아니면
            gd_isset($arrData['couponSaveDuplicateLimitType'], 'n');
            $validator->add('couponSaveDuplicateType', 'yn', true); // 중복발급제한여부–안됨(‘n’),중복가능(‘y’)
            $validator->add('couponSaveDuplicateLimitType', 'yn', true); // 중복발급최대제한여부–사용(‘y’)
            if ($arrData['couponSaveDuplicateLimitType'] == 'y') {
                $validator->add('couponSaveDuplicateLimit', 'number', true); // 중복발급최대개수
            }
        }
        $validator->add('couponProductMinOrderType', 'alpha', true); // 쿠폰유형–상품쿠폰(‘product’),주문쿠폰(‘order’),배송비쿠폰('delivery')
        $validator->add('couponMinOrderPrice', null, null); // 쿠폰적용의 최소상품구매금액제한–소수점2자리가능
        $validator->add('couponApplyDuplicateType', 'yn', true); // 쿠폰적용 여부-중복가능(‘y’),안됨(‘n’)

        // 쿠폰유형 (주문적용 쿠폰/배송비할인 쿠폰/기프트쿠폰) 따른 설정 초기값 정의
        switch ($arrData['couponUseType']) {
            case 'gift': // 기프트쿠폰
                // 결제수단 사용제한 (validator->add()는 기본 정의됨)
                $arrData['couponUseAblePaymentType'] = 'all';

                // 최소 상품구매금액 제한 (validator->add()는 기본 정의됨)
                $arrData['couponMinOrderPrice'] = '0'; // 구매금액 최소한도
                $arrData['couponProductMinOrderType'] = 'product'; // 구매금액 기준 옵션

                // 같은 유형의 쿠폰과 중복사용 여부 (validator->add()는 기본 정의됨)
                $arrData['couponApplyDuplicateType'] = 'y';
            case 'order': // 주문적용 쿠폰
            case 'delivery': // 배송비할인 쿠폰
            case 'gift': // 기프트쿠폰
                // 전체 발급수량
                if ($arrData['couponSaveType'] == 'manual') { // 발급방식이 '수동발급' 경우
                    $arrData['couponAmountType'] = 'n';
                    $arrData['couponAmount'] = '';
                    $validator->add('couponAmountType', null, null); // 발급수량종류
                    $validator->add('couponAmount', null, null); // 발급수량
                }

                // 쿠폰 재발급 제한
                if ($arrData['couponSaveType'] == 'manual') { // 발급방식이 '수동발급' 경우
                    $arrData['couponSaveDuplicateType'] = $arrData['couponSaveDuplicateLimitType'] = 'n';
                    $arrData['couponSaveDuplicateLimit'] = '';
                    $validator->add('couponSaveDuplicateType', null, null); // 쿠폰 재발급 제한여부
                    $validator->add('couponSaveDuplicateLimitType', null, null); // 최대 재발급 여부
                    $validator->add('couponSaveDuplicateLimit', null, null); // 최대 재발급 수량
                }

                // 발급 가능 회원등급 선택
                $arrData['couponApplyMemberGroupDisplayType'] = 'n';
                $validator->add('couponApplyMemberGroupDisplayType', null, null); // 발급/사용 가능한 회원등급에게만 쿠폰노출
                if ($arrData['couponSaveType'] == 'manual') { // 발급방식이 '수동발급' 경우
                    $arrData['couponApplyMemberGroup'] = '';
                    $validator->add('couponApplyMemberGroup', null, null); // 선택된 회원등급
                }

                // 사용 가능 회원등급 선택
                if ($arrData['couponSaveType'] == 'manual') { // 발급방식이 '수동발급' 경우
                    $arrData['couponUseMemberGroup'] = '';
                    $validator->add('couponUseMemberGroup', null, null); // 선택된 회원등급
                }

                // 쿠폰 발급/사용 가능 범위 설정
                $arrData['couponApplyProductType'] = 'all'; // 쿠폰적용상품
                $arrData['couponApplyProvider'] = null; // 쿠폰적용공급사
                $arrData['couponApplyCategory'] = null; // 쿠폰적용카테고리
                $arrData['couponApplyBrand'] = null; // 쿠폰적용브랜드
                $arrData['couponApplyGoods'] = null; // 쿠폰적용상품
                $validator->add('couponApplyProductType', 'alpha', true); // 쿠폰적용상품
                $validator->add('couponApplyProvider', null, null); // 쿠폰적용공급사
                $validator->add('couponApplyCategory', null, null); // 쿠폰적용카테고리
                $validator->add('couponApplyBrand', null, null); // 쿠폰적용브랜드
                $validator->add('couponApplyGoods', null, null); // 쿠폰적용상품

                // 쿠폰 발급/사용 제외 설정
                $arrData['couponExceptProviderType'] = 'n'; // 쿠폰제외공급사여부
                $arrData['couponExceptProvider'] = null; // 쿠폰제외공급사
                $arrData['couponExceptCategoryType'] = 'n'; // 쿠폰제외카테고리여부
                $arrData['couponExceptCategory'] = null; // 쿠폰제외카테고리
                $arrData['couponExceptBrandType'] = 'n'; // 쿠폰제외브랜드여부
                $arrData['couponExceptBrand'] = null; // 쿠폰제외브랜드
                $arrData['couponExceptGoodsType'] = 'n'; // 쿠폰제외상품여부
                $arrData['couponExceptGoods'] = null; // 쿠폰제외상품
                $validator->add('couponExceptProviderType', 'yn', null); // 쿠폰제외공급사여부
                $validator->add('couponExceptProvider', null, true); // 쿠폰제외공급사
                $validator->add('couponExceptCategoryType', 'yn', null); // 쿠폰제외카테고리여부
                $validator->add('couponExceptCategory', null, true); // 쿠폰제외카테고리
                $validator->add('couponExceptBrandType', 'yn', null); // 쿠폰제외브랜드여부
                $validator->add('couponExceptBrand', null, true); // 쿠폰제외브랜드
                $validator->add('couponExceptGoodsType', 'yn', null); // 쿠폰제외상품여부
                $validator->add('couponExceptGoods', null, true); // 쿠폰제외상품

                // 최소 상품구매금액 제한
                $arrData['couponProductMinOrderType'] = 'product'; // 구매금액 기준 옵션
                break;
        }

        if ($validator->act($arrData, true) === false) {
            throw new \Exception(implode("<br/>", $validator->errors));
        }

        //        선택에 따른 값의 초기화를 위한 빈값 제거 - 주석처리 함 - 상태에 따른 빈값 저장(초기화)이 필요한 경우가 존재함.
        //        $arrData = ArrayUtils::removeEmpty($arrData);

        $couponNo = $arrData['couponNo'];
        switch (substr($arrData['mode'], 0, 6)) {
            case 'insert' : {
                // 저장
                $arrBind = $this->db->get_binding(DBTableField::tableCoupon(), $arrData, 'insert', array_keys($arrData), ['couponNo']);
                $this->db->set_insert_db(DB_COUPON, $arrBind['param'], $arrBind['bind'], 'y');
                // 등록된 쿠폰고유번호
                $couponNo = $this->db->insert_id();

                foreach ($arrData['couponUseMemberGroup'] as $v) {
                    $arrDataUseMemberGroup = array('couponNo' => $couponNo, 'couponUseMemberGroup' => $v);
                    $arrBind = $this->db->get_binding(DBTableField::tableCouponUseMemberGroup(), $arrDataUseMemberGroup, 'insert', array_keys($arrDataUseMemberGroup));
                    $this->db->set_insert_db(DB_COUPON_USE_MEMBER_GROUP, $arrBind['param'], $arrBind['bind'], 'y');
                }
                break;
            }
            case 'modify' : {
                // 수정
                $arrBind = $this->db->get_binding(DBTableField::tableCoupon(), $arrData, 'update', array_keys($arrData), ['couponNo']);
                $this->db->bind_param_push($arrBind['bind'], 'i', $arrData['couponNo']);
                $this->db->set_update_db(DB_COUPON, $arrBind['param'], 'couponNo = ?', $arrBind['bind'], false);

                // couponMemberUseGroup 테이블을 지우고 다시 인서트
                $arrBind = ['i', $arrData['couponNo']];
                // --- 삭제
                $this->db->set_delete_db(DB_COUPON_USE_MEMBER_GROUP, 'couponNo = ?', $arrBind);
                foreach ($arrData['couponUseMemberGroup'] as $v) {
                    $arrDataUseMemberGroup = array('couponNo' => $arrData['couponNo'], 'couponUseMemberGroup' => $v);
                    $arrBind = $this->db->get_binding(DBTableField::tableCouponUseMemberGroup(), $arrDataUseMemberGroup, 'insert', array_keys($arrDataUseMemberGroup));
                    $this->db->set_insert_db(DB_COUPON_USE_MEMBER_GROUP, $arrBind['param'], $arrBind['bind'], 'y');
                }
                break;
            }
        }
        $this->checkCouponType($couponNo, $arrData['couponType']);
    }

    /**
     * 오프라인 쿠폰 저장
     *
     * @author su
     */
    public function saveOfflineCoupon(&$arrData, &$files)
    {
        if (substr($arrData['mode'], 0, 6) == 'modify') {
            $couponNo = (int)$arrData['couponNo'];
            $countMemberCouponData = $this->getMemberCouponTotalCount($couponNo);
            if ($countMemberCouponData > 0) {
                $this->checkCouponType($couponNo);
                throw new \Exception(__('발급된 쿠폰이 존재하여 쿠폰을 수정할 수 없습니다.'));
            }

        }
        // 쿠폰 허용 회원등급
        if ($arrData['couponApplyMemberGroup']) {
            $arrData['couponApplyMemberGroup'] = implode(INT_DIVISION, $arrData['couponApplyMemberGroup']);
        } else {
            $arrData['couponApplyMemberGroup'] = null;
        }
        // 쿠폰 허용 공급사
        if ($arrData['couponApplyProvider']) {
            $arrData['couponApplyProvider'] = implode(INT_DIVISION, $arrData['couponApplyProvider']);
        }
        // 쿠폰 허용 카테고리
        if ($arrData['couponApplyCategory']) {
            $arrData['couponApplyCategory'] = implode(INT_DIVISION, $arrData['couponApplyCategory']);
        }
        // 쿠폰 허용 브랜드
        if ($arrData['couponApplyBrand']) {
            $arrData['couponApplyBrand'] = implode(INT_DIVISION, $arrData['couponApplyBrand']);
        }
        // 쿠폰 허용 상품
        if ($arrData['couponApplyGoods']) {
            $arrData['couponApplyGoods'] = implode(INT_DIVISION, $arrData['couponApplyGoods']);
        }
        // 쿠폰 제외 공급사
        if ($arrData['couponExceptProvider']) {
            $arrData['couponExceptProvider'] = implode(INT_DIVISION, $arrData['couponExceptProvider']);
        }
        // 쿠폰 제외 카테고리
        if ($arrData['couponExceptCategory']) {
            $arrData['couponExceptCategory'] = implode(INT_DIVISION, $arrData['couponExceptCategory']);
        }
        // 쿠폰 제외 브랜드
        if ($arrData['couponExceptBrand']) {
            $arrData['couponExceptBrand'] = implode(INT_DIVISION, $arrData['couponExceptBrand']);
        }
        // 쿠폰 제외 상품
        if ($arrData['couponExceptGoods']) {
            $arrData['couponExceptGoods'] = implode(INT_DIVISION, $arrData['couponExceptGoods']);
        }

        // 쿠폰 이미지
        if (ArrayUtils::isEmpty($files['couponImage']) === false) {
            $file = $files['couponImage'];
            if ($file['error'] == 0 && $file['size']) {
                $saveFileName = substr(md5(microtime()), 0, 8) . rand(100, 999);
                $this->storage()->upload($file['tmp_name'], $saveFileName);
                $arrData['couponImage'] = $saveFileName;
            }
        }
        // Validation
        $validator = new Validator();
        if (substr($arrData['mode'], 0, 6) == 'modify') {
            $validator->add('couponNo', 'number', true); // 쿠폰 고유번호
        } else {
            $arrData['couponInsertAdminId'] = Session::get('manager.managerId');
            $arrData['managerNo'] = Session::get('manager.sno');
            $validator->add('couponInsertAdminId', 'userid', true); // 쿠폰등록자-아이디
            $validator->add('managerNo', 'number', true); // 쿠폰등록자-키
        }
        $validator->add('mode', 'alpha', true); // 모드
        $validator->add('couponKind', 'alpha', true); // 종류–온라인쿠폰(‘online’),페이퍼쿠폰(‘offline’)
        $validator->add('couponType', 'alpha', true); // 사용여부–사용(‘y’),정지(‘n’),종료(‘f’)

        $validator->add('couponUseType', 'alpha', true); // 쿠폰유형–상품쿠폰(‘product’),주문쿠폰(‘order’),배송비쿠폰('delivery')
        // 사용구분에 따른 체크
        // 상품 적용 쿠폰
        if ($arrData['couponUseType'] == 'product') {
            if ($arrData['couponKindType'] == 'delivery') {
                throw new \Exception(__('상품적용 쿠폰은 배송비할인 조건을 설정할 수 없습니다.'));
            }
            $validator->add('couponApplyProductType', 'alpha', true); // 쿠폰적용상품–전체(‘all’),공급사(‘provider’),카테고리(‘category’),브랜드(‘brand’),상품(‘goods’)
            if ($arrData['couponApplyProductType'] == 'provider') { // 공급사 쿠폰 적용
                $validator->add('couponApplyProvider', null, true); // 쿠폰적용공급사
                $arrData['couponApplyCategory'] = null;
                $arrData['couponApplyBrand'] = null;
                $arrData['couponApplyGoods'] = null;
                $validator->add('couponApplyCategory', null, null); // 쿠폰적용카테고리
                $validator->add('couponApplyBrand', null, null); // 쿠폰적용브랜드
                $validator->add('couponApplyGoods', null, null); // 쿠폰적용상품
            } else if ($arrData['couponApplyProductType'] == 'category') { // 카테고리 쿠폰 적용
                $validator->add('couponApplyCategory', null, true); // 쿠폰적용카테고리
                $arrData['couponApplyProvider'] = null;
                $arrData['couponApplyBrand'] = null;
                $arrData['couponApplyGoods'] = null;
                $validator->add('couponApplyProvider', null, null); // 쿠폰적용공급사
                $validator->add('couponApplyBrand', null, null); // 쿠폰적용브랜드
                $validator->add('couponApplyGoods', null, null); // 쿠폰적용상품
            } else if ($arrData['couponApplyProductType'] == 'brand') { // 브랜드 쿠폰 적용
                $validator->add('couponApplyBrand', null, true); // 쿠폰적용브랜드
                $arrData['couponApplyProvider'] = null;
                $arrData['couponApplyCategory'] = null;
                $arrData['couponApplyGoods'] = null;
                $validator->add('couponApplyProvider', null, null); // 쿠폰적용공급사
                $validator->add('couponApplyCategory', null, null); // 쿠폰적용카테고리
                $validator->add('couponApplyGoods', null, null); // 쿠폰적용상품s
            } else if ($arrData['couponApplyProductType'] == 'goods') { // 상품 쿠폰 적용
                $validator->add('couponApplyGoods', null, true); // 쿠폰적용상품
                $arrData['couponApplyProvider'] = null;
                $arrData['couponApplyCategory'] = null;
                $arrData['couponApplyBrand'] = null;
                $validator->add('couponApplyProvider', null, null); // 쿠폰적용공급사
                $validator->add('couponApplyCategory', null, null); // 쿠폰적용카테고리
                $validator->add('couponApplyBrand', null, null); // 쿠폰적용브랜드
            } else { // 전체 적용
                $arrData['couponApplyProvider'] = null;
                $arrData['couponApplyCategory'] = null;
                $arrData['couponApplyBrand'] = null;
                $arrData['couponApplyGoods'] = null;
                $validator->add('couponApplyProvider', null, null); // 쿠폰적용공급사
                $validator->add('couponApplyCategory', null, null); // 쿠폰적용카테고리
                $validator->add('couponApplyBrand', null, null); // 쿠폰적용브랜드
                $validator->add('couponApplyGoods', null, null); // 쿠폰적용상품
            }
            // 체크박스에 대한 초기화
            gd_isset($arrData['couponExceptProviderType'], 'n');
            gd_isset($arrData['couponExceptCategoryType'], 'n');
            gd_isset($arrData['couponExceptBrandType'], 'n');
            gd_isset($arrData['couponExceptGoodsType'], 'n');
            $validator->add('couponExceptProviderType', 'yn', null); // 쿠폰제외공급사여부-사용(‘y’)
            if ($arrData['couponExceptProviderType'] == 'y') {
                $validator->add('couponExceptProvider', null, true); // 쿠폰제외공급사
            } else {
                $arrData['couponExceptProvider'] = null;
                $validator->add('couponExceptProvider', null, true); // 쿠폰제외공급사
            }
            $validator->add('couponExceptCategoryType', 'yn', null); // 쿠폰제외카테고리여부-사용(‘y’)
            if ($arrData['couponExceptCategoryType'] == 'y') {
                $validator->add('couponExceptCategory', null, true); // 쿠폰제외카테고리
            } else {
                $arrData['couponExceptCategory'] = null;
                $validator->add('couponExceptCategory', null, true); // 쿠폰제외카테고리
            }
            $validator->add('couponExceptBrandType', 'yn', null); // 쿠폰제외브랜드여부-사용(‘y’)
            if ($arrData['couponExceptBrandType'] == 'y') {
                $validator->add('couponExceptBrand', null, true); // 쿠폰제외브랜드
            } else {
                $arrData['couponExceptBrand'] = null;
                $validator->add('couponExceptBrand', null, true); // 쿠폰제외브랜드
            }
            $validator->add('couponExceptGoodsType', 'yn', null); // 쿠폰제외상품여부-사용(‘y’)
            if ($arrData['couponExceptGoodsType'] == 'y') {
                $validator->add('couponExceptGoods', null, true); // 쿠폰제외상품
            } else {
                $arrData['couponExceptGoods'] = null;
                $validator->add('couponExceptGoods', null, true); // 쿠폰제외상품
            }
            // 주문 적용 쿠폰
        } else if ($arrData['couponUseType'] == 'order') {
            if ($arrData['couponKindType'] == 'delivery') {
                throw new \Exception(__('주문적용 쿠폰은 배송비할인 조건을 설정할 수 없습니다.'));
            }
            if ($arrData['couponApplyProvider'] || $arrData['couponApplyCategory'] || $arrData['couponApplyBrand'] || $arrData['couponApplyGoods']) {
                throw new \Exception(__('주문적용 쿠폰은 발급/사용 사용 조건을 설정할 수 없습니다.'));
            }
            if ($arrData['couponExceptProviderType'] || $arrData['couponExceptProvider'] || $arrData['couponExceptCategoryType'] || $arrData['couponExceptCategory'] || $arrData['couponExceptBrandType'] || $arrData['couponExceptBrand'] || $arrData['couponExceptGoodsType'] || $arrData['couponExceptGoods']) {
                throw new \Exception(__('주문적용 쿠폰은 발급/사용 사용 조건을 설정할 수 없습니다.'));
            }
            if ($arrData['couponBenefitType'] == 'fix') {
                throw new \Exception(__('주문적용 쿠폰은 정율(%) 할인만 가능합니다.'));
            }
            // 배송비 쿠폰
        } else if ($arrData['couponUseType'] == 'delivery') {
            if ($arrData['couponKindType'] != 'delivery') {
                throw new \Exception(__('배송비적용 쿠폰은 배송비할인 조건만 설정할 수 있습니다.'));
            }
            if ($arrData['couponApplyProvider'] || $arrData['couponApplyCategory'] || $arrData['couponApplyBrand'] || $arrData['couponApplyGoods']) {
                throw new \Exception(__('배송비할인 쿠폰은 발급/사용 조건을 설정할 수 없습니다.'));
            }
            if ($arrData['couponExceptProviderType'] || $arrData['couponExceptProvider'] || $arrData['couponExceptCategoryType'] || $arrData['couponExceptCategory'] || $arrData['couponExceptBrandType'] || $arrData['couponExceptBrand'] || $arrData['couponExceptGoodsType'] || $arrData['couponExceptGoods']) {
                throw new \Exception(__('배송비할인 쿠폰은 발급/사용 조건을 설정할 수 없습니다.'));
            }
            if ($arrData['couponBenefitType'] == 'percent') {
                throw new \Exception(__('배송비할인 쿠폰은 정액(원) 할인만 가능합니다.'));
            }
        }

        $validator->add('couponNm', null, true); // 쿠폰명
        //        $validator->add('couponNm', 'maxlen', true, null, 30); // 쿠폰명 최대 30자
        $validator->add('couponDescribed', '', null); // 쿠폰설명
        //        $validator->add('couponDescribed', 'maxlen', null, null, 50); // 쿠폰설명 최대 50자
        $validator->add('couponUsePeriodType', null, true); // 사용기간–기간(‘period’),일(‘day’)
        if ($arrData['couponUsePeriodType'] == 'period') { // 사용기간 설정이 기간 설정일 경우
            $validator->add('couponUsePeriodStartDate', 'datetime', true); // 사용기간-시작
            $validator->add('couponUsePeriodEndDate', 'datetime', true); // 사용기간-끝
            if ($arrData['couponUsePeriodStartDate'] >= $arrData['couponUsePeriodEndDate']) { // 사용기간-시작이 사용기간-끝보다 클 수 없습니다.
                throw new \Exception(__('사용기간 시작일은 사용기간 종료일보다 클 수 없습니다.'));
            }
        } else { // 사용기간 설정이 일자 설정일 경우
            $validator->add('couponUsePeriodDay', 'number', true); // 사용가능일
            $validator->add('couponUseDateLimit', 'datetime', null); // 사용제한 일자
        }
        $validator->add('couponKindType', 'alpha', true); // 해당구분–상품할인(‘sale’),마일리지적립(‘add’),배송비할인('delivery')
        $validator->add('couponDeviceType', 'alpha', true); // 사용범위–PC+모바일(‘all’),PC(‘pc’),모바일(‘mobile’)
        $validator->add('couponBenefit', null, true); // 혜택금액(할인,적립)액–소수점 2자리 가능
        $validator->add('couponBenefitType', 'alpha', true); // 혜택금액종류-정율%(‘percent’),정액-원(‘fix’)–금액은 $등 가능
        if ($arrData['couponBenefitType'] == 'percent') {
            if ($arrData['couponBenefit'] < 0 || $arrData['couponBenefit'] > 100) {
                throw new \Exception(__('정률(%s)의 경우 숫자 %d까지 입력하실 수 있습니다.','%',100));
            }
        }
        gd_isset($arrData['couponMaxBenefitType'], 'n');
        $validator->add('couponMaxBenefitType', 'yn', null); // 최대혜택금액여부–사용(‘y’)
        if ($arrData['couponMaxBenefitType'] == 'y') {
            $validator->add('couponMaxBenefit', null, true); // 최대혜택금액–소수점2자리가능
        }
        $validator->add('couponDisplayStartDate', '', null); // 등록기간–시작
        $validator->add('couponDisplayEndDate', '', null); // 등록기간–끝

        // 사용기간이 기간제(일자제 아님)이고 노출기간 종료일보다 사용기간 종료일이 클 경우
        if (($arrData['couponUsePeriodType'] == 'period') && $arrData['couponDisplayEndDate'] && $arrData['couponUsePeriodEndDate'] && ($arrData['couponDisplayEndDate'] > $arrData['couponUsePeriodEndDate'])) {
            throw new \Exception(__('쿠폰 발급 종료일자가 쿠폰 사용 만료일보다 길 수 없습니다.'));
        }

        $validator->add('couponImageType', 'alpha', true); // 이미지종류–기본(‘basic’),직접(‘self’)
        if ($arrData['couponImageType'] == 'self') {
            $validator->add('couponImage', null, true); // 이미지
        }

        //사용기간만료시 SMS발송
        gd_isset($arrData['couponLimitSmsFl'], 'n');
        $validator->add('couponLimitSmsFl', 'yn', null); // SMS발송–사용(‘y’)

        //결제수단 사용제한
        gd_isset($arrData['couponUseAblePaymentType'], 'all');
        $validator->add('couponUseAblePaymentType', 'alpha', true); // 제한없음(‘all’), 무통장만(‘bank’)

        // 쿠폰등록 및 인증번호 타입 수정인 경우
        if (substr($arrData['mode'], 0, 6) == 'insert' || $arrData['registedCouponAuthType'] != $arrData['couponAuthType']) {
            $validator->add('couponAuthType', 'yn', true); // 쿠폰인증번호 생성방식
            if ($arrData['couponAuthType'] == 'n') {
                if ($arrData['duplicateCouponAuthNumber'] != $arrData['couponAuthNumber']) {
                    throw new \Exception(__('중복확인을 하셔야 합니다.'));
                } else {
                    $couponAuthNumber = $arrData['couponAuthNumber'];
                }
            }
        }
        gd_isset($arrData['couponSaveDuplicateLimitType'], 'n');
        $validator->add('couponSaveDuplicateType', 'yn', true); // 중복발급제한여부–안됨(‘n’),중복가능(‘y’)
        $validator->add('couponSaveDuplicateLimitType', 'yn', true); // 중복발급최대제한여부–사용(‘y’)
        if ($arrData['couponSaveDuplicateLimitType'] == 'y') {
            $validator->add('couponSaveDuplicateLimit', 'number', true); // 중복발급최대개수
        }
        $validator->add('couponApplyMemberGroup', null, null); // 발급가능회원등급
        $validator->add('couponProductMinOrderType', 'alpha', true); // 쿠폰유형–상품쿠폰(‘product’),주문쿠폰(‘order’),배송비쿠폰('delivery')
        $validator->add('couponMinOrderPrice', null, null); // 쿠폰적용의 최소상품구매금액제한–소수점2자리가능
        $validator->add('couponApplyDuplicateType', 'yn', true); // 쿠폰적용 여부-중복가능(‘y’),안됨(‘n’)

        // 쿠폰유형 (주문적용 쿠폰/배송비할인 쿠폰/기프트쿠폰) 따른 설정 초기값 정의
        switch ($arrData['couponUseType']) {
            case 'gift': // 기프트쿠폰
                // 결제수단 사용제한 (validator->add()는 기본 정의됨)
                $arrData['couponUseAblePaymentType'] = 'all';

                // 최소 상품구매금액 제한 (validator->add()는 기본 정의됨)
                $arrData['couponMinOrderPrice'] = '0'; // 구매금액 최소한도
                $arrData['couponProductMinOrderType'] = 'product'; // 구매금액 기준 옵션

                // 같은 유형의 쿠폰과 중복사용 여부 (validator->add()는 기본 정의됨)
                $arrData['couponApplyDuplicateType'] = 'y';
            case 'order': // 주문적용 쿠폰
            case 'delivery': // 배송비할인 쿠폰
            case 'gift': // 기프트쿠폰
                // 쿠폰 발급/사용 가능 범위 설정
                $arrData['couponApplyProductType'] = 'all'; // 쿠폰적용상품
                $arrData['couponApplyProvider'] = null; // 쿠폰적용공급사
                $arrData['couponApplyCategory'] = null; // 쿠폰적용카테고리
                $arrData['couponApplyBrand'] = null; // 쿠폰적용브랜드
                $arrData['couponApplyGoods'] = null; // 쿠폰적용상품
                $validator->add('couponApplyProductType', 'alpha', true); // 쿠폰적용상품
                $validator->add('couponApplyProvider', null, null); // 쿠폰적용공급사
                $validator->add('couponApplyCategory', null, null); // 쿠폰적용카테고리
                $validator->add('couponApplyBrand', null, null); // 쿠폰적용브랜드
                $validator->add('couponApplyGoods', null, null); // 쿠폰적용상품

                // 쿠폰 발급/사용 제외 설정
                $arrData['couponExceptProviderType'] = 'n'; // 쿠폰제외공급사여부
                $arrData['couponExceptProvider'] = null; // 쿠폰제외공급사
                $arrData['couponExceptCategoryType'] = 'n'; // 쿠폰제외카테고리여부
                $arrData['couponExceptCategory'] = null; // 쿠폰제외카테고리
                $arrData['couponExceptBrandType'] = 'n'; // 쿠폰제외브랜드여부
                $arrData['couponExceptBrand'] = null; // 쿠폰제외브랜드
                $arrData['couponExceptGoodsType'] = 'n'; // 쿠폰제외상품여부
                $arrData['couponExceptGoods'] = null; // 쿠폰제외상품
                $validator->add('couponExceptProviderType', 'yn', null); // 쿠폰제외공급사여부
                $validator->add('couponExceptProvider', null, true); // 쿠폰제외공급사
                $validator->add('couponExceptCategoryType', 'yn', null); // 쿠폰제외카테고리여부
                $validator->add('couponExceptCategory', null, true); // 쿠폰제외카테고리
                $validator->add('couponExceptBrandType', 'yn', null); // 쿠폰제외브랜드여부
                $validator->add('couponExceptBrand', null, true); // 쿠폰제외브랜드
                $validator->add('couponExceptGoodsType', 'yn', null); // 쿠폰제외상품여부
                $validator->add('couponExceptGoods', null, true); // 쿠폰제외상품

                // 최소 상품구매금액 제한
                $arrData['couponProductMinOrderType'] = 'product'; // 구매금액 기준 옵션
                break;
        }

        if ($validator->act($arrData, true) === false) {
            throw new \Exception(implode("<br/>", $validator->errors));
        }

        //        선택에 따른 값의 초기화를 위한 빈값 제거 - 주석처리 함 - 상태에 따른 빈값 저장(초기화)이 필요한 경우가 존재함.
        //        $arrData = ArrayUtils::removeEmpty($arrData);

        $couponNo = $arrData['couponNo'];
        switch (substr($arrData['mode'], 0, 6)) {
            case 'insert' : {
                // 저장
                $arrBind = $this->db->get_binding(DBTableField::tableCoupon(), $arrData, 'insert', array_keys($arrData), ['couponNo']);
                $this->db->set_insert_db(DB_COUPON, $arrBind['param'], $arrBind['bind'], 'y');
                // 등록된 쿠폰고유번호
                $couponNo = $this->db->insert_id();
                if ($arrData['couponAuthType'] == 'n') {
                    $this->setCouponOfflineDirectCode($couponNo, $couponAuthNumber);
                }
                break;
            }
            case 'modify' : {
                // 수정
                $arrBind = $this->db->get_binding(DBTableField::tableCoupon(), $arrData, 'update', array_keys($arrData), ['couponNo']);
                $this->db->bind_param_push($arrBind['bind'], 'i', $arrData['couponNo']);
                $this->db->set_update_db(DB_COUPON, $arrBind['param'], 'couponNo = ?', $arrBind['bind'], false);
                // 인증번호 타입 수정할 경우
                if ($arrData['registedCouponAuthType'] != $arrData['couponAuthType']) {
                    $this->deleteAllCouponOfflineCode($arrData['couponNo']);
                    if ($arrData['couponAuthType'] == 'n') {
                        $this->setCouponOfflineDirectCode($arrData['couponNo'], $couponAuthNumber);
                    }
                }
                break;
            }
        }
        $this->checkCouponType($couponNo, $arrData['couponType']);
        return $couponNo;
    }

    /**
     * 수동 발급의 회원쿠폰 저장
     *
     * @author su
     */
    public function saveMemberCoupon($memNoArr, $searchQuery = null)
    {
        $getValue = Request::post()->toArray();
        if(!$this->checkCouponType($getValue['couponNo'])) {
            return false;
        }
        $arrData = [];
        $arrData['couponNo'] = $getValue['couponNo'];
        $arrData['couponSaveAdminId'] = Session::get('manager.managerId');
        $arrData['managerNo'] = Session::get('manager.sno');
        $arrData['memberCouponStartDate'] = $this->getMemberCouponStartDate($getValue['couponNo']);
        $arrData['memberCouponEndDate'] = $this->getMemberCouponEndDate($getValue['couponNo']);
        $arrData['memberCouponState'] = 'y';

        if ($memNoArr) {
            foreach ($memNoArr as $val) {
                unset($arrData['memNo']);
                $arrData['memNo'] = $val;
                // 저장
                $arrBind = $this->db->get_binding(DBTableField::tableMemberCoupon(), $arrData, 'insert', array_keys($arrData), ['memberCouponNo']);
                $this->db->set_insert_db(DB_MEMBER_COUPON, $arrBind['param'], $arrBind['bind'], 'y');
                $this->setCouponMemberSaveCount($getValue['couponNo']);
            }
            return 'T';
            exit;
        }
        if ($searchQuery) {
            $memberAdmin = \App::load(\Component\Member\MemberAdmin::class);
            $searchQuery = json_decode($searchQuery);
            $searchQuery = ArrayUtils::objectToArray($searchQuery);
            $tmp = $memberAdmin->searchMemberWhere($searchQuery);
            $getMemberData = $memberAdmin->getMemberList($tmp['arrWhere'], null, $tmp['arrBind']);
            if (count($getMemberData) > 30000) { //발급건수가 30000건이상일경우 CLI로 처리
                if (file_exists(\App::getUserBasePath() . '/config/CliSaveCoupon')) {
                    $sFiledata = \FileHandler::read(\App::getUserBasePath() . '/config/CliSaveCouponCount');
                    $getCliData = json_decode($sFiledata, true);
                    return $getCliData['couponNo'];
                    exit;
                } else {
                    $nowCouponCount = $this->getMemberCouponTotalCount($arrData['couponNo']);
                    $nowCouponInfo = $this->getCouponInfo($arrData['couponNo'], 'couponNm');
                    $aCount = array('couponNo' => $arrData['couponNo'], 'totalCount' => count($getMemberData), 'orgCount' => $nowCouponCount, 'couponName' => $nowCouponInfo['couponNm']);
                    \FileHandler::write(\App::getUserBasePath() . '/config/CliSaveCouponCount', json_encode($aCount));

                    $tempMemberData = array();
                    foreach ($getMemberData as $val) {
                        $tempMemberData[] = $val['memNo'];
                    }
                    \FileHandler::write(\App::getUserBasePath() . '/config/CliSaveCoupon', json_encode($tempMemberData));
                    $aTempData = array('arrData' => $arrData, 'searchQuery' => 'T');
                    $sData = json_encode($aTempData);
                    exec("/usr/local/php/bin/php " . \App::getUserBasePath() . "/route.php job --name='CliSaveCoupon' --extravalue='" . $sData . "'" . " > /dev/null 2>/dev/null &");
                    return 'C';
                    exit;
                }
            } else {
                foreach ($getMemberData as $val) {
                    unset($arrData['memNo']);
                    $arrData['memNo'] = $val['memNo'];
                    // 저장
                    $arrBind = $this->db->get_binding(DBTableField::tableMemberCoupon(), $arrData, 'insert', array_keys($arrData), ['memberCouponNo']);
                    $this->db->set_insert_db(DB_MEMBER_COUPON, $arrBind['param'], $arrBind['bind'], 'y');
                }
                $this->setCouponMemberSaveCountAll($getValue['couponNo'], count($getMemberData));
                return 'T';
                exit;
            }
        }
    }

    /**
     * 수동 발급의 회원쿠폰 저장
     *
     * @author su
     */
    public function saveMemberCouponCli($arrData, $searchQuery = null)
    {
        if ($searchQuery) {
            $sFiledata = \FileHandler::read(\App::getUserBasePath() . '/config/CliSaveCoupon');
            $getMemberData = json_decode($sFiledata, true);
            foreach ($getMemberData as $val) {
                unset($arrData['memNo']);
                $arrData['memNo'] = $val;
                // 저장
                $arrBind = $this->db->get_binding(DBTableField::tableMemberCoupon(), $arrData, 'insert', array_keys($arrData), ['memberCouponNo']);
                $this->db->set_insert_db(DB_MEMBER_COUPON, $arrBind['param'], $arrBind['bind'], 'y');
            }
            $this->setCouponMemberSaveCountAll($arrData['couponNo'], count($getMemberData));
        }
        \FileHandler::delete(\App::getUserBasePath() . '/config/CliSaveCoupon');
    }

    /**
     * 수동 발급의 회원쿠폰 저장 & SMS발송
     *
     * @author su
     */
    public function saveMemberCouponSms($memNoArr, $searchQuery = null, $passwordCheckFl = true)
    {
        $getValue = Request::post()->toArray();

        $logger = \App::getInstance('logger');
        $smsAuto = \App::load(\Component\Sms\SmsAuto::class);
        if(!$this->checkCouponType($getValue['couponNo'])) {
            return false;
        }
        $couponInfo = $this->getCouponInfo($getValue['couponNo'], '*');

        $arrData = [];
        $arrData['couponNo'] = $getValue['couponNo'];
        $arrData['couponSaveAdminId'] = gd_isset(Request::post()->get('couponSaveAdminId'), Session::get('manager.managerId'));
        $arrData['managerNo'] = Session::get('manager.sno');
        $arrData['memberCouponStartDate'] = $this->getMemberCouponStartDate($getValue['couponNo']);
        $arrData['memberCouponEndDate'] = $this->getMemberCouponEndDate($getValue['couponNo']);
        $arrData['memberCouponState'] = 'y';

        if ($memNoArr) {
            foreach ($memNoArr as $val) {
                unset($arrData['memNo']);
                $arrData['memNo'] = $val;
                // 저장
                $arrBind = $this->db->get_binding(DBTableField::tableMemberCoupon(), $arrData, 'insert', array_keys($arrData), ['memberCouponNo']);
                $this->db->set_insert_db(DB_MEMBER_COUPON, $arrBind['param'], $arrBind['bind'], 'y');

                // sms 발송처리
                $member = ['smsFl' => 'n'];
                if ($val >= 1) {
                    $member = \Component\Member\MemberDAO::getInstance()->selectMemberByOne($val);
                } else {
                    $logger->info('Send coupon auto sms. not found member number.');
                }
                if ($couponInfo) {
                    if ($member['smsFl'] == 'y') {
                        $smsAuto->setPasswordCheckFl($passwordCheckFl);
                        $smsAuto->setSmsAutoCodeType(Code::COUPON_MANUAL);
                        $smsAuto->setSmsType(SmsAutoCode::PROMOTION);
                        $smsAuto->setReceiver($member);
                        $smsAuto->setReplaceArguments(['name' => $member['memNm'], 'CouponName' => $couponInfo['couponNm'], 'rc_memid' => $member['memId']]);
                        $smsAuto->autoSend();
                    } else {
                        $logger->info(sprintf('Disallow sms receiving. memNo[%s], smsFl [%s]', $member['memNo'], $member['smsFl']));
                    }
                }

                $this->setCouponMemberSaveCount($getValue['couponNo']);
            }
        }
        if ($searchQuery) {
            $memberAdmin = \App::load(\Component\Member\MemberAdmin::class);
            $searchQuery = json_decode($searchQuery);
            $searchQuery = ArrayUtils::objectToArray($searchQuery);
            $tmp = $memberAdmin->searchMemberWhere($searchQuery);
            $getMemberData = $memberAdmin->getMemberList($tmp['arrWhere'], null, $tmp['arrBind']);
            foreach ($getMemberData as $val) {
                unset($arrData['memNo']);
                $arrData['memNo'] = $val['memNo'];
                // 저장
                $arrBind = $this->db->get_binding(DBTableField::tableMemberCoupon(), $arrData, 'insert', array_keys($arrData), ['memberCouponNo']);
                $this->db->set_insert_db(DB_MEMBER_COUPON, $arrBind['param'], $arrBind['bind'], 'y');

                // sms 발송처리
                if ($couponInfo) {
                    if ($val['smsFl'] == 'y') {
                        $smsAuto->setSmsAutoCodeType(Code::COUPON_MANUAL);
                        $smsAuto->setSmsType(SmsAutoCode::PROMOTION);
                        $smsAuto->setReceiver($val);
                        $smsAuto->setReplaceArguments(['name' => $val['memNm'], 'CouponName' => $couponInfo['couponNm'], 'rc_memid' => $val['memId']]);
                        $smsAuto->autoSend();
                    } else {
                        $logger->info(sprintf('Disallow sms receiving. memNo[%s], smsFl [%s]', $val['memNo'], $val['smsFl']));
                    }
                }

                $this->setCouponMemberSaveCount($getValue['couponNo']);
            }
        }

        return 'T';
        exit;
    }

    /**
     * 수동 발급의 엑셀파일 회원쿠폰 저장
     *
     * @author su
     */
    public function saveExcelMemberCoupon($sSmsFlag = 'n')
    {
        $getValue = Request::post()->toArray();
        $arrData = [];
        $arrData['couponNo'] = $getValue['couponNo'];
        $arrData['couponSaveAdminId'] = Session::get('manager.managerId');
        $arrData['memberCouponStartDate'] = $this->getMemberCouponStartDate($getValue['couponNo']);
        $arrData['memberCouponEndDate'] = $this->getMemberCouponEndDate($getValue['couponNo']);
        $arrData['memberCouponState'] = 'y';


        // --- 엑셀 class
        $excel = \App::load('\\Component\\Excel\\ExcelDataConvert');
        $excel->setExcelMemberCouponUp($arrData, $sSmsFlag);

        return 'T';
        exit;
    }

    public function setCouponAdminSearch($getValue = null)
    {
        if (is_null($getValue)) $getValue = Request::get()->toArray();

        // 통합 검색
        $this->search['combineSearch'] = [
            'all' => '=' . __('통합검색') . '=',
            'couponNm' => __('쿠폰명'),
            'couponDescribed' => __('쿠폰설명'),
            'couponInsertAdminId' => __('등록자(아이디)'),
        ];
        if($getValue['couponTypeY']) $getValue['couponType'] = 'y'; // 발급중 쿠폰 리스트만

        $this->search['key'] = gd_isset($getValue['key']);
        $this->search['keyword'] = gd_isset($getValue['keyword']);
        $this->search['searchDate'] = gd_isset($getValue['searchDate']);
        $this->search['couponType'] = gd_isset($getValue['couponType']);
        $this->search['couponSaveType'] = gd_isset($getValue['couponSaveType']);
        $this->search['couponUseType'] = gd_isset($getValue['couponUseType']);
        $this->search['couponDeviceType'] = gd_isset($getValue['couponDeviceType']);
        $this->search['couponKindType'] = gd_isset($getValue['couponKindType']);
        $this->search['couponEventType'] = gd_isset($getValue['couponEventType']);

        // 쿠폰 리스트 페이지 7일 검색 추가
        if (gd_php_self() == '/promotion/coupon_list.php' || gd_php_self() == '/promotion/coupon_offline_list.php') {
            $this->search['searchPeriod'] = gd_isset($getValue['searchPeriod'],'6');
            $this->search['searchDate'][0] = gd_isset($getValue['searchDate'][0], date('Y-m-d', strtotime('-6 day')));
            $this->search['searchDate'][1] = gd_isset($getValue['searchDate'][1], date('Y-m-d'));

            $this->checked['searchPeriod'][$this->search['searchPeriod']] ="active";
        }

        // 키워드 검색
        if ($this->search['key'] && $this->search['keyword']) {
            if(empty($getValue['couponUseType']) === true) { // 출석체크 상세보기 레이어에서는 쿠폰검색이 아닌 회원검색이므로 해당 where절 포함안함
                if ($this->search['key'] == 'all') {
                    $tmpWhere = ['couponNm', 'couponDescribed', 'couponInsertAdminId'];
                    $arrWhereAll = [];
                    foreach ($tmpWhere as $keyNm) {
                        $arrWhereAll[] = '(c.' . $keyNm . ' LIKE concat(\'%\',?,\'%\'))';
                        $this->db->bind_param_push($this->arrBind, $this->fieldTypes['coupon'][$keyNm], $this->search['keyword']);
                    }
                    $this->arrWhere[] = '(' . implode(' OR ', $arrWhereAll) . ')';
                } else {
                    $this->arrWhere[] = 'c.' . $this->search['key'] . ' LIKE concat(\'%\',?,\'%\')';
                    $this->db->bind_param_push($this->arrBind, $this->fieldTypes['coupon'][$this->search['key']], $this->search['keyword']);
                }
            }
        }
        // 기간 검색
        if ($this->search['searchDate']) {
            if ($this->search['searchDate'][0] && $this->search['searchDate'][1]) {
                $this->arrWhere[] = '(c.regDt BETWEEN DATE_FORMAT(?,\'%Y-%m-%d 00:00:00\') AND DATE_FORMAT(?,\'%Y-%m-%d 23:59:59\'))';
                $this->db->bind_param_push($this->arrBind, 's', $this->search['searchDate'][0]);
                $this->db->bind_param_push($this->arrBind, 's', $this->search['searchDate'][1]);
            } else if ($this->search['searchDate'][0] && !$this->search['searchDate'][1]) {
                $this->arrWhere[] = '(c.regDt >= DATE_FORMAT(?,\'%Y-%m-%d 00:00:00\'))';
                $this->db->bind_param_push($this->arrBind, 's', $this->search['searchDate'][0]);
            } else if (!$this->search['searchDate'][0] && $this->search['searchDate'][1]) {
                $this->arrWhere[] = '(c.regDt <= DATE_FORMAT(?,\'%Y-%m-%d 23:59:59\'))';
                $this->db->bind_param_push($this->arrBind, 's', $this->search['searchDate'][1]);
            }
        }

        if ($this->search['couponType']) {
            $this->arrWhere[] = 'c.couponType =?';
            $this->db->bind_param_push($this->arrBind, $this->fieldTypes['coupon']['couponType'], $this->search['couponType']);
        }
        if ($this->search['couponSaveType']) {
            $this->arrWhere[] = 'c.couponSaveType =?';
            $this->db->bind_param_push($this->arrBind, $this->fieldTypes['coupon']['couponSaveType'], $this->search['couponSaveType']);
        }
        if ($this->search['couponUseType']) {
            $this->arrWhere[] = 'c.couponUseType=?';
            $this->db->bind_param_push($this->arrBind, $this->fieldTypes['coupon']['couponUseType'], $this->search['couponUseType']);
        }
        if ($this->search['couponDeviceType']) {
            $this->arrWhere[] = 'c.couponDeviceType=?';
            $this->db->bind_param_push($this->arrBind, $this->fieldTypes['coupon']['couponDeviceType'], $this->search['couponDeviceType']);
        }
        if ($this->search['couponKindType']) {
            $this->arrWhere[] = 'c.couponKindType=?';
            $this->db->bind_param_push($this->arrBind, $this->fieldTypes['coupon']['couponKindType'], $this->search['couponKindType']);
        }
        if ($this->search['couponEventType']) {
            $this->arrWhere[] = 'c.couponEventType=?';
            $this->db->bind_param_push($this->arrBind, $this->fieldTypes['coupon']['couponEventType'], $this->search['couponEventType']);
        }
        $this->checked['couponType'][$this->search['couponType']] =
        $this->checked['couponSaveType'][$this->search['couponSaveType']] =
        $this->checked['couponUseType'][$this->search['couponUseType']] =
        $this->checked['couponDeviceType'][$this->search['couponDeviceType']] =
        $this->checked['couponKindType'][$this->search['couponKindType']] = "checked='checked'";

        $this->selected['sort'][$this->search['sort']] = "selected='selected'";

        if (empty($this->arrBind)) {
            $this->arrBind = null;
        }
    }

    public function setMemberCouponAdminSearch($getValue = null)
    {
        if (is_null($getValue)) $getValue = Request::get()->toArray();

        // 통합 검색
        $this->search['combineSearch'] = [
            'all' => '=' . __('통합검색') . '=',
            'memId' => __('아이디'),
            'memNm' => __('이름'),
            'couponSaveAdminId' => __('발급자(아이디)'),
        ];
        // 기간 검색
        $this->search['combineSearchDate'] = [
            'regDt' => __('발급일'),
            'memberCouponEndDate' => __('만료일'),
            'memberCouponUseDate' => __('사용일'),
        ];

        $this->search['couponNo'] = gd_isset($getValue['couponNo']);
        $this->search['key'] = gd_isset($getValue['key']);
        $this->search['keyword'] = gd_isset($getValue['keyword']);
        $this->search['keyDate'] = gd_isset($getValue['keyDate'], 'regDt');
        $this->search['keywordDate'][0] = gd_isset($getValue['keywordDate'][0], date('Y-m-d', strtotime('-6 day')));
        $this->search['keywordDate'][1] = gd_isset($getValue['keywordDate'][1], date('Y-m-d'));
        $this->search['searchPeriod'] = gd_isset($getValue['searchPeriod'],'6');
        $this->checked['searchPeriod'][$this->search['searchPeriod']] ="active";

        $fieldType['member'] = DBTableField::getFieldTypes('tableMember');

        if ($this->search['couponNo']) {
            $this->arrWhere[] = 'mc.couponNo =?';
            $this->db->bind_param_push($this->arrBind, $this->fieldTypes['memberCoupon']['couponNo'], $this->search['couponNo']);
        } else {
            throw new \Exception(__('쿠폰 정보가 없습니다.'));
        }
        // 키워드 검색
        if ($this->search['key'] && $this->search['keyword']) {
            if ($this->search['key'] == 'all') {
                $tmpWhere = ['memId', 'memNm'];
                $arrWhereAll = [];
                foreach ($tmpWhere as $keyNm) {
                    $arrWhereAll[] = '(m.' . $keyNm . ' LIKE concat(\'%\',?,\'%\'))';
                    $this->db->bind_param_push($this->arrBind, $fieldType['member'][$keyNm], $this->search['keyword']);
                }
                $tmpWhere = ['couponSaveAdminId'];
                foreach ($tmpWhere as $keyNm) {
                    $arrWhereAll[] = '(mc.' . $keyNm . ' LIKE concat(\'%\',?,\'%\'))';
                    $this->db->bind_param_push($this->arrBind, $this->fieldTypes['memberCoupon'][$keyNm], $this->search['keyword']);
                }
                $this->arrWhere[] = '(' . implode(' OR ', $arrWhereAll) . ')';
            } else if ($this->search['key'] == 'memId' || $this->search['key'] == 'memNm') {
                $this->arrWhere[] = 'm.' . $this->search['key'] . ' LIKE concat(\'%\',?,\'%\')';
                $this->db->bind_param_push($this->arrBind, $fieldType['member'][$this->search['key']], $this->search['keyword']);
            } else {
                $this->arrWhere[] = 'mc.' . $this->search['key'] . ' LIKE concat(\'%\',?,\'%\')';
                $this->db->bind_param_push($this->arrBind, $fieldType['member'][$this->search['key']], $this->search['keyword']);
            }
        }
        if ($this->search['keyDate'] && $this->search['keywordDate']) {
            if ($this->search['keywordDate'][0] && $this->search['keywordDate'][1]) {
                $this->arrWhere[] = '(mc.' . $this->search['keyDate'] . ' BETWEEN DATE_FORMAT(?,\'%Y-%m-%d 00:00:00\') AND DATE_FORMAT(?,\'%Y-%m-%d 23:59:59\'))';
                $this->db->bind_param_push($this->arrBind, $this->fieldTypes['memberCoupon'][$this->search['keyDate']], $this->search['keywordDate'][0]);
                $this->db->bind_param_push($this->arrBind, $this->fieldTypes['memberCoupon'][$this->search['keyDate']], $this->search['keywordDate'][1]);
            } else if ($this->search['keywordDate'][0] && !$this->search['keywordDate'][1]) {
                $this->arrWhere[] = '(mc.' . $this->search['keyDate'] . ' >= DATE_FORMAT(?,\'%Y-%m-%d 00:00:00\')';
                $this->db->bind_param_push($this->arrBind, $this->fieldTypes['memberCoupon'][$this->search['keyDate']], $this->search['keywordDate'][0]);
            } else if (!$this->search['keywordDate'][0] && $this->search['keywordDate'][1]) {
                $this->arrWhere[] = '(mc.' . $this->search['keyDate'] . ' <= DATE_FORMAT(?,\'%Y-%m-%d 00:00:00\')';
                $this->db->bind_param_push($this->arrBind, $this->fieldTypes['memberCoupon'][$this->search['keyDate']], $this->search['keywordDate'][0]);
            }
        }

        $this->selected['sort'][$this->search['sort']] = "selected='selected'";

        if (empty($this->arrBind)) {
            $this->arrBind = null;
        }
    }

    /**
     * 컴백 쿠폰 리스트 가져오기
     *
     * @author su
     */
    public function getComebackCouponList()
    {
        $getValue = Request::get()->toArray();
        $this->setCouponAdminSearch($getValue);

        $sort['fieldName'] = gd_isset($getValue['sort']['name']);
        $sort['sortMode'] = gd_isset($getValue['sort']['mode']);
        if (empty($sort['fieldName']) || empty($sort['sortMode'])) {
            $sort['fieldName'] = 'cc.regDt';
            $sort['sortMode'] = 'desc';
        } else {
            $sort['fieldName'] = 'c' . $sort['fieldName'];
        }

        // 레이어에서 자바스크립트 페이징 처리시 사용되는 구문
        if (gd_isset($getValue['pagelink'])) {
            $getValue['page'] = (int) str_replace('page=', '', preg_replace('/^{page=[0-9]+}/', '', gd_isset($getValue['pagelink'])));
        }
        gd_isset($getValue['page'], 1);
        gd_isset($getValue['pageNum'], 10);

        $page = \App::load('\\Component\\Page\\Page', $getValue['page']);
        $page->page['list'] = $getValue['pageNum'];

        list($page->recode['amount']) = $this->db->fetch('SELECT count(sno) FROM ' . DB_COMEBACK_COUPON. ' WHERE deleteFl = "n"', 'row');
        $page->setPage();
        $page->setUrl(\Request::getQueryString());

        $this->arrWhere[] = 'cc.deleteFl = ?';
        $this->db->bind_param_push($this->arrBind, 's', 'n');
        $this->db->strField = "cc.*";
        $this->db->strWhere = implode(' AND ', gd_isset($this->arrWhere));
        $this->db->strOrder = $sort['fieldName'] . ' ' . $sort['sortMode'];
        $this->db->strLimit = $page->recode['start'] . ',' . $getValue['pageNum'];

        // 검색 카운트
        $strSQL = ' SELECT COUNT(cc.sno) AS cnt FROM ' . DB_COMEBACK_COUPON .' as cc ' . ' WHERE ' . $this->db->strWhere;
        $res = $this->db->query_fetch($strSQL, $this->arrBind, false);
        $page->recode['total'] = $res['cnt']; // 검색 레코드 수
        $page->setPage();
        $page->setUrl(\Request::getQueryString());

        $query = $this->db->query_complete();
        $strSQL = 'SELECT ' . array_shift($query) . ', m.managerId FROM ' . DB_COMEBACK_COUPON . ' as cc LEFT OUTER JOIN ' . DB_MANAGER . " as m ON cc.managerNo = m.sno" . implode(' ', $query);
//        \Logger::debug($strSQL, $this->arrBind);
        $data = $this->db->query_fetch($strSQL, $this->arrBind);
        Manager::displayListData($data);

        $getData['data'] = gd_htmlspecialchars_stripslashes(gd_isset($data));
        $getData['sort'] = $sort;

        return $getData;
    }

    /**
     * 컴백 쿠폰 삭제하기
     *
     * @author su
     */
    public function deleteComebackCoupon()
    {
        $getValue = Request::post()->toArray();
        foreach ($getValue['chkCoupon'] as $key => $val) {
            $sno = (int)$val;
            if (Validator::number($sno, null, null, true) === false) {
                throw new \Exception(__('컴백쿠폰번호 인자가 잘못되었습니다.'), 500);
            }

            // 삭제플래그로업데이트
            $arrData['deleteFl'] = 'y';
            $arrBind = $this->db->get_binding(DBTableField::tableComebackCoupon(), $arrData, 'update', array_keys($arrData));
            $this->db->bind_param_push($arrBind['bind'], 'i', $sno);
            $this->db->set_update_db(DB_COMEBACK_COUPON, $arrBind['param'], 'sno = ?', $arrBind['bind'], false);
        }
    }

    /**
     * 컴백 쿠폰 복사하기
     *
     * @author su
     */
    public function copyComebackCoupon()
    {
        $getValue = Request::post()->toArray();
        foreach ($getValue['chkCoupon'] as $key => $val) {
            $sno = (int)$val;
            if (Validator::number($sno, null, null, true) === false) {
                throw new \Exception(__('컴백쿠폰번호 인자가 잘못되었습니다.'), 500);
            }
            $arrData = $this->getComebackCouponInfo($sno);

            $arrBind = $this->db->get_binding(DBTableField::tableComebackCoupon(), $arrData, 'insert', array_keys($arrData), ['sno', 'sendDt']);
            $this->db->set_insert_db(DB_COMEBACK_COUPON, $arrBind['param'], $arrBind['bind'], 'y');
        }
    }

    /**
     * 컴백 쿠폰 저장
     *
     * @author su
     */
    public function saveComebackCoupon(&$arrData)
    {
        if (substr($arrData['mode'], 0, 6) == 'modify') {
            $sno = (int)$arrData['sno'];
            $comebackCouponInfo = $this->getComebackCouponInfo($sno, 'couponNo, sendDt');
            if ($comebackCouponInfo['sendDt'] != null && $comebackCouponInfo['sendDt'] != '0000-00-00 00:00:00') {
                $this->checkCouponType($comebackCouponInfo['couponNo']);
                throw new \Exception(__('컴백 쿠폰이 발송되어 내용을 수정할 수 없습니다.'));
            }
        }

        // Validation
        $validator = new Validator();
        if (substr($arrData['mode'], 0, 6) == 'modify') {
            $validator->add('sno', 'number', true); // 컴백쿠폰 고유번호
        } else {
            $arrData['managerNo'] = Session::get('manager.sno');
            $validator->add('managerNo', 'number', true);
        }

        $validator->add('mode', 'alpha', true); // 모드
        $validator->add('title', 'required', true); // 제목
        $validator->add('targetFl', 'alpha', true); // 대상 선택유형 o:주문관련 / g:상품관련
        if ($arrData['targetFl'] == 'o') {
            $validator->add('targetOrderFl', 'alpha', true); // 대상 주문 선택유형 p:결제완료 / s:배송완료 / c:구매확정
            $validator->add('targetOrderDay', 'number', true); // 대상/주문관련-선택유형으로부터 몇일이 지난값
            if ($arrData['targetOrderPriceMax']) {
                gd_isset($arrData['targetOrderPriceMax'], 0);
                $validator->add('targetOrderPriceMin', 'number', true); // 대상/주문관련-선택유형의 결제금액 최소값
                $validator->add('targetOrderPriceMax', 'number', true); // 대상/주문관련-선택유형의 결제금액 최대값
                if ($arrData['targetOrderPriceMin'] >= $arrData['targetOrderPriceMax']) { // 결제대상 금액의 최소값이 최대값보다 같거나 클 수 없습니다.
                    throw new \Exception(__('결제대상 금액의 최소값이 최대값보다 같거나 클 수 없습니다.'));
                }
            } else {
                $arrData['targetOrderPriceMin'] = null;
                $arrData['targetOrderPriceMax'] = null;
                $validator->add('targetOrderPriceMin', null, null); // 대상/주문관련-선택유형의 결제금액 최소값
                $validator->add('targetOrderPriceMax', null, null); // 대상/주문관련-선택유형의 결제금액 최대값
            }
            $arrData['targetGoodFl'] = 'p';
            $arrData['targetGoodDay'] = null;
            $arrData['targetGoodGoods'] = null;
            $validator->add('targetGoodFl', 'alpha', true); // 대상 상품 선택유형 p:결제완료 / s:배송완료 / c:구매확정
            $validator->add('targetGoodDay', null, null); // 대상/상품관련-선택유형으로부터 몇일이 지난값
            $validator->add('targetGoodGoods', null, null); // 대상 상품 선택된 상품배열
        } else {
            $arrData['targetOrderFl'] = 'p';
            $arrData['targetOrderDay'] = null;
            $arrData['targetOrderPriceMin'] = null;
            $arrData['targetOrderFl'] = null;
            $validator->add('targetOrderPriceMin', 'alpha', true); // 대상 주문 선택유형 p:결제완료 / s:배송완료 / c:구매확정
            $validator->add('targetOrderDay', null, null); // 대상/주문관련-선택유형으로부터 몇일이 지난값
            $validator->add('targetOrderPriceMin', null, null); // 대상/주문관련-선택유형의 결제금액 최소값
            $validator->add('targetOrderPriceMax', null, null); // 대상/주문관련-선택유형의 결제금액 최대값
            if (!$arrData['targetGoodGoods']) {
                $arrData['targetGoodGoods'] = null;
                $validator->add('targetGoodGoods', null, null); // 대상 상품 선택된 상품배열
            } else {
                $arrData['targetGoodGoods'] = implode(INT_DIVISION, $arrData['targetGoodGoods']);
                $validator->add('targetGoodGoods', 'required', true); // 대상 상품 선택된 상품배열
            }
            $validator->add('targetGoodFl', 'alpha', true); // 대상 상품 선택유형 p:결제완료 / s:배송완료 / c:구매확정
            $validator->add('targetGoodDay', 'number', true); // 대상/상품관련-선택유형으로부터 몇일이 지난값
        }

        // 쿠폰 여부
        if (!$arrData['couponNo']) {
            $arrData['couponNo'] = null;
            $validator->add('couponNo', null, null); // 발행 쿠폰 고유번호
        } else {
            $validator->add('couponNo', 'number', true); // 발행 쿠폰 고유번호
        }

        // sms발송 여부
        if (!$arrData['smsFl']) {
            $arrData['smsFl'] = null;
            $validator->add('smsFl', null, null); // sms 동시 발송여부
        } else {
            $validator->add('smsFl', 'alpha', true); // sms 동시 발송여부
        }

        // sms 스팸 여부
        if (!$arrData['smsSpamFl']) {
            $arrData['smsSpamFl'] = null;
            $validator->add('smsSpamFl', null, null); // 광고성 문구 추가
        } else {
            $validator->add('smsSpamFl', 'alpha', true); // 광고성 문구 추가
        }

        // sms 내용
        if (!$arrData['smsContents'] || trim($arrData['smsContents']) == '') {
            $arrData['smsContents'] = "(광고)
[{rc_mallNm}]
구매하신 상품은 마음에 드셨나요? 특별한 고객님만을 위한 컴백 할인 쿠폰 발급!
지금 바로 확인하세요!";
        }
        $validator->add('smsContents', 'required', true); // SMS 발송 내용

        // 전송일 null처리
        $arrData['sendDt'] = null;
        $validator->add('sendDt', null, null); // SMS 발송 내용

        if ($validator->act($arrData, true) === false) {
            throw new \Exception(implode("<br/>", $validator->errors));
        }

        $couponNo = $arrData['couponNo'];
        switch (substr($arrData['mode'], 0, 6)) {
            case 'regist' : {
                // 저장
                $arrBind = $this->db->get_binding(DBTableField::tableComebackCoupon(), $arrData, 'insert', array_keys($arrData));
                $this->db->set_insert_db(DB_COMEBACK_COUPON, $arrBind['param'], $arrBind['bind'], 'y');
                // 등록된 쿠폰고유번호
                $couponNo = $this->db->insert_id();
                break;
            }
            case 'modify' : {
                // 수정
                $arrBind = $this->db->get_binding(DBTableField::tableComebackCoupon(), $arrData, 'update', array_keys($arrData));
                $this->db->bind_param_push($arrBind['bind'], 'i', $arrData['sno']);
                $this->db->set_update_db(DB_COMEBACK_COUPON, $arrBind['param'], 'sno = ?', $arrBind['bind'], false);
                break;
            }
        }
        $this->checkCouponType($couponNo, $arrData['couponType']);
    }

    /**
     * 컴백 쿠폰 대상자 리스트 가져오기
     *
     * @author su
     */
    public function getComebackCouponMemberList(&$arrData, $countFl = 'n', $allFl = 'n')
    {
        $getValue = Request::get()->toArray();

        $sort['fieldName'] = 'm.memId';
        $sort['sortMode'] = 'asc';

        // 레이어에서 자바스크립트 페이징 처리시 사용되는 구문
        if (gd_isset($getValue['pagelink'])) {
            $arrData['page'] = (int) str_replace('page=', '', preg_replace('/^{page=[0-9]+}/', '', gd_isset($getValue['pagelink'])));
        }
        gd_isset($arrData['page'], 1);
        gd_isset($arrData['pageNum'], 10);

        $page = \App::load('\\Component\\Page\\Page', $arrData['page']);
        $page->page['list'] = $arrData['pageNum'];

        // 컴백쿠폰 설정값으로 대상 쿼리 필요값들 생성
        // 쿠폰 설정 - 회원등급제한
        $getCouponData = $this->getCouponInfo($arrData['couponNo'], '*');
        if ($getCouponData['couponApplyMemberGroup']) {
            $applyMemberGroupArr = explode(INT_DIVISION, $getCouponData['couponApplyMemberGroup']);
            $applyMemberGroup = implode(',', $applyMemberGroupArr);
        } else {
            $applyMemberGroup = '';
        }

        // 검색조건값들 처리
        if ($arrData['targetFl'] == 'o') {
            $checkDate = date("Y-m-d", strtotime(date("Y-m-d")." -" . $arrData['targetOrderDay'] . " days")) . "23:59:59.999999";
            if ($arrData['targetOrderFl'] == 'p') {
                $this->arrWhere[] = 'tt.paymentDt < ? ';
                $sSelect1 = 'paymentDt';
            } elseif ($arrData['targetOrderFl'] == 's') {
                $this->arrWhere[] = 'tt.deliveryCompleteDt < ? ';
                $sSelect1 = 'deliveryCompleteDt';
            } elseif ($arrData['targetOrderFl'] == 'c') {
                $this->arrWhere[] = 'tt.finishDt < ? ';
                $sSelect1 = 'finishDt';
            }
            $this->db->bind_param_push($this->arrBind, 's', $checkDate);
            if ($arrData['targetOrderPriceMax'] > 0) {
                $this->arrWhere[] = 'o.settlePrice BETWEEN ? AND ? ';
                $this->db->bind_param_push($this->arrBind, 'i', gd_isset($arrData['targetOrderPriceMin'], 0));
                $this->db->bind_param_push($this->arrBind, 'i', $arrData['targetOrderPriceMax']);
            }
            $sTargetTable = '(SELECT o.memNo, MAX(o.orderNo) as orderNo, MAX(og.' . $sSelect1 . ') as ' . $sSelect1 . ' FROM ' . DB_ORDER_GOODS . ' as og JOIN ' . DB_ORDER . ' as o ON o.orderNo = og.orderNo WHERE o.memNo > 0 AND (og.' . $sSelect1 . ' IS NOT NULL AND og.' . $sSelect1 . ' != \'0000-00-00 00:00:00\') GROUP BY o.memNo) as tt';
        } else {
            $checkDate = date("Y-m-d", strtotime(date("Y-m-d")." -" . $arrData['targetGoodDay'] . " days")) . "23:59:59.999999";
            if ($arrData['targetGoodFl'] == 'p') {
                $this->arrWhere[] = 'tt.paymentDt < ? ';
                $sSelect1 = 'paymentDt';
            } elseif ($arrData['targetGoodFl'] == 's') {
                $this->arrWhere[] = 'tt.deliveryCompleteDt < ? ';
                $sSelect1 = 'deliveryCompleteDt';
            } elseif ($arrData['targetGoodFl'] == 'c') {
                $this->arrWhere[] = 'tt.finishDt < ? ';
                $sSelect1 = 'finishDt';
            }
            $this->db->bind_param_push($this->arrBind, 's', $checkDate);

            if ($arrData['targetGoodGoods'] != '') {
                $arrData['targetGoodGoods'] =  str_replace(INT_DIVISION, ',', $arrData['targetGoodGoods']);
            }
            //$this->arrWhere[] = 'og.goodsNo IN (?) ';
            //$this->db->bind_param_push($this->arrBind, 'i', $arrData['targetGoodGoods']);

            $sTargetTable = '(SELECT o.memNo, MAX(o.orderNo) as orderNo, MAX(og.' . $sSelect1 . ') as ' . $sSelect1 . ' FROM ' . DB_ORDER_GOODS . ' as og JOIN ' . DB_ORDER . ' as o ON o.orderNo = og.orderNo WHERE o.memNo > 0 AND og.goodsNo IN (' . $arrData['targetGoodGoods'] . ') AND (og.' . $sSelect1 . ' IS NOT NULL AND og.' . $sSelect1 . ' != \'0000-00-00 00:00:00\') GROUP BY o.memNo) as tt';
        }
        if ($applyMemberGroup != '') {
            $this->arrWhere[] = 'm.groupSno IN (?) ';
            $this->db->bind_param_push($this->arrBind, 's', $applyMemberGroup);
        }

        // sms만 지급시
        if ($arrData['smsFl'] == 'y' && ($arrData['couponNo'] == 0 || $arrData['couponNo'] == '')) {
            $whereSmsFlag = 'AND m.smsFl = \'y\'';
        } else {
            $whereSmsFlag = '';
        }

        $this->db->strField = "m.memId, m.memNm, mg.groupNm, mc.couponNo";
        $this->db->strWhere = implode(' AND ', gd_isset($this->arrWhere));
        $this->db->strOrder = $sort['fieldName'] . ' ' . $sort['sortMode'];
        $this->db->strGroup = 'm.memId';

        $query = $this->db->query_complete();
        $strSQL = 'SELECT ' . array_shift($query) . ' FROM ' . $sTargetTable . ' JOIN ' . DB_ORDER . ' as o ON o.orderNo = tt.orderNo JOIN '
                . DB_MEMBER . ' as m ON tt.memNo = m.memNo AND m.memNo > 0 AND m.sleepFl = \'n\' AND m.mallSno = 1 ' . $whereSmsFlag . ' JOIN ' . DB_MEMBER_GROUP . ' as mg ON m.groupSno = mg.sno LEFT JOIN '
                . DB_MEMBER_COUPON . ' as mc ON mc.couponNo = ' . $arrData['couponNo'] . ' AND m.memNo = mc.memNo ' . implode(' ', $query);
//        \Logger::debug($strSQL, $this->arrBind);

        $data = $this->db->query_fetch($strSQL, $this->arrBind);
        Manager::displayListData($data);

        $page->recode['amount'] = count($data);
        $page->setPage();
        $page->setUrl(\Request::getQueryString());

        if ($countFl == 'y') {
            return $page->recode['amount'];
        } else {
            $this->db->strField = "m.memId, m.memNo, m.groupSno, m.memNm, m.cellPhone, mg.groupNm, mc.couponNo, m.smsFl";
            $this->db->strWhere = implode(' AND ', gd_isset($this->arrWhere));
            $this->db->strOrder = $sort['fieldName'] . ' ' . $sort['sortMode'];
            $this->db->strGroup = 'm.memId';
            if ($allFl == 'n') {
                $this->db->strLimit = $page->recode['start'] . ',' . $arrData['pageNum'];
            }

            // 검색 카운트
            $strSQL = ' SELECT COUNT(*) AS cnt FROM ' . $sTargetTable . ' JOIN ' . DB_ORDER . ' as o ON o.orderNo = tt.orderNo JOIN '
                . DB_MEMBER . ' as m ON tt.memNo = m.memNo AND m.memNo > 0 AND m.sleepFl = \'n\' AND m.mallSno = 1 ' . $whereSmsFlag . ' JOIN ' . DB_MEMBER_GROUP . ' as mg ON m.groupSno = mg.sno LEFT JOIN '
                . DB_MEMBER_COUPON . ' as mc ON mc.couponNo = ' . $arrData['couponNo'] . ' AND m.memNo = mc.memNo ' . ' WHERE ' . $this->db->strWhere;
            $res = $this->db->query_fetch($strSQL, $this->arrBind, false);
            $page->recode['total'] = $res['cnt']; // 검색 레코드 수
            $page->setPage();
            $page->setUrl(\Request::getQueryString());

            $query = $this->db->query_complete();
            $strSQL = 'SELECT ' . array_shift($query) . ' FROM ' . $sTargetTable . ' JOIN ' . DB_ORDER . ' as o ON o.orderNo = tt.orderNo JOIN '
                . DB_MEMBER . ' as m ON tt.memNo = m.memNo AND m.memNo > 0 AND m.sleepFl = \'n\' AND m.mallSno = 1 ' . $whereSmsFlag . ' JOIN ' . DB_MEMBER_GROUP . ' as mg ON m.groupSno = mg.sno LEFT JOIN '
                . DB_MEMBER_COUPON . ' as mc ON mc.couponNo = ' . $arrData['couponNo'] . ' AND m.memNo = mc.memNo ' . implode(' ', $query);
            $data = $this->db->query_fetch($strSQL, $this->arrBind);
            Manager::displayListData($data);

            $getData['data'] = gd_htmlspecialchars_stripslashes(gd_isset($data));
            $getData['sort'] = $sort;

            return $getData;
        }
    }

    /**
     * 컴백 쿠폰 대상자 리스트 가져오기
     *
     * @author su
     */
    public function getComebackCouponMemberResultList(&$arrData)
    {
        $getValue = Request::get()->toArray();

        $sort['fieldName'] = 'm.memId';
        $sort['sortMode'] = 'asc';

        // 레이어에서 자바스크립트 페이징 처리시 사용되는 구문
        if (gd_isset($getValue['pagelink'])) {
            $arrData['page'] = (int) str_replace('page=', '', preg_replace('/^{page=[0-9]+}/', '', gd_isset($getValue['pagelink'])));
        }
        gd_isset($arrData['page'], 1);
        gd_isset($arrData['pageNum'], 10);

        $page = \App::load('\\Component\\Page\\Page', $arrData['page']);
        $page->page['list'] = $arrData['pageNum'];

        $this->arrWhere[] = 'ccm.ccSno = ? ';
        $this->db->bind_param_push($this->arrBind, 'i', $arrData['dataSno']);

        // 검색조건값들 처리
        if ($arrData['searchValue'] != '') {
            if ($arrData['searchMode'] == 'all') {
                $this->arrWhere[] = '(m.memId LIKE ? OR m.memNm LIKE ? OR m.nickNm LIKE ? OR m.phone LIKE ? OR m.cellPhone LIKE ?)';
                $this->db->bind_param_push($this->arrBind, 's', '%' . $arrData['searchValue'] . '%');
                $this->db->bind_param_push($this->arrBind, 's', '%' . $arrData['searchValue'] . '%');
                $this->db->bind_param_push($this->arrBind, 's', '%' . $arrData['searchValue'] . '%');
                $this->db->bind_param_push($this->arrBind, 's', '%' . $arrData['searchValue'] . '%');
                $this->db->bind_param_push($this->arrBind, 's', '%' . $arrData['searchValue'] . '%');
            } else {
                if ($arrData['searchMode'] == 'memId') {
                    $this->arrWhere[] = 'm.memId LIKE ?';
                    $this->db->bind_param_push($this->arrBind, 's', '%' . $arrData['searchValue'] . '%');
                } elseif ($arrData['searchMode'] == 'memNm') {
                    $this->arrWhere[] = 'm.memNm LIKE ?';
                    $this->db->bind_param_push($this->arrBind, 's', '%' . $arrData['searchValue'] . '%');
                } elseif ($arrData['searchMode'] == 'nickNm') {
                    $this->arrWhere[] = 'm.nickNm LIKE ?';
                    $this->db->bind_param_push($this->arrBind, 's', '%' . $arrData['searchValue'] . '%');
                } elseif ($arrData['searchMode'] == 'email') {
                    $this->arrWhere[] = 'm.email LIKE ?';
                    $this->db->bind_param_push($this->arrBind, 's', '%' . $arrData['searchValue'] . '%');
                } elseif ($arrData['searchMode'] == 'phone') {
                    $this->arrWhere[] = 'm.phone LIKE ?';
                    $this->db->bind_param_push($this->arrBind, 's', '%' . $arrData['searchValue'] . '%');
                } elseif ($arrData['searchMode'] == 'cellPhone') {
                    $this->arrWhere[] = 'm.cellPhone LIKE ?';
                    $this->db->bind_param_push($this->arrBind, 's', '%' . $arrData['searchValue'] . '%');
                }
            }
        }

        // 쿠폰발급여부
        if ($arrData['issueCouponFl'] != 'all') {
            if ($arrData['issueCouponFl'] == 'n') {
                $this->arrWhere[] = '(ccm.memberCouponNo IS NULL OR ccm.memberCouponNo = 0)';
            } else {
                $this->arrWhere[] = 'ccm.memberCouponNo > 0';
            }
        }

        // SMS발송여부
        if ($arrData['sendSmsFl'] != 'all') {
            $this->arrWhere[] = 'ccm.smsFl = ?';
            $this->db->bind_param_push($this->arrBind, 's', $arrData['sendSmsFl']);
        }

        if ($arrData['useType'] != 'all') {
            if ($arrData['useType'] == 'n') {
                $this->arrWhere[] = '(mc.memberCouponUseDate IS NULL OR mc.memberCouponUseDate = "0000-00-00 00:00:00")';
            } else {
                $this->arrWhere[] = '(mc.memberCouponUseDate IS NOT NULL AND mc.memberCouponUseDate != "0000-00-00 00:00:00")';
            }
        }

        $this->db->strField = "ccm.sno, ccm.memberCouponNo, ccm.couponNo, ccm.smsFl, m.memId, m.memNo, m.memNm, mg.groupNm, mc.memberCouponUseDate";
        $this->db->strWhere = implode(' AND ', gd_isset($this->arrWhere));
        $this->db->strOrder = $sort['fieldName'] . ' ' . $sort['sortMode'];
        //$this->db->strGroup = 'm.memId';
        $this->db->strLimit = $page->recode['start'] . ',' . $arrData['pageNum'];

        // 검색 카운트
        $strSQL = ' SELECT COUNT(*) AS cnt FROM ' . DB_COMEBACK_COUPON_MEMBER . ' as ccm '
            . 'JOIN ' . DB_MEMBER . ' as m ON m.memNo = ccm.memNo JOIN ' . DB_MEMBER_GROUP . ' as mg ON mg.sno = ccm.groupSno '
            . ' LEFT JOIN ' . DB_MEMBER_COUPON . ' as mc ON mc.couponNo = ccm.couponNo AND mc.memNo = ccm.memNo AND mc.memberCouponNo = ccm.memberCouponNo ' . ' WHERE ' . $this->db->strWhere;
        $res = $this->db->query_fetch($strSQL, $this->arrBind, false);
        $page->recode['total'] = $res['cnt']; // 검색 레코드 수
        $page->setPage();
        $page->setUrl(\Request::getQueryString());

        $query = $this->db->query_complete();
        $strSQL = 'SELECT ' . array_shift($query) . ' FROM ' . DB_COMEBACK_COUPON_MEMBER . ' as ccm '
            . 'JOIN ' . DB_MEMBER . ' as m ON m.memNo = ccm.memNo JOIN ' . DB_MEMBER_GROUP . ' as mg ON mg.sno = ccm.groupSno '
            . ' LEFT JOIN ' . DB_MEMBER_COUPON . ' as mc ON mc.couponNo = ccm.couponNo AND mc.memNo = ccm.memNo AND mc.memberCouponNo = ccm.memberCouponNo ' . implode(' ', $query);
        $data = $this->db->query_fetch($strSQL, $this->arrBind);
        Manager::displayListData($strSQL);

        $getData['data'] = gd_htmlspecialchars_stripslashes(gd_isset($data));
        $getData['sort'] = $sort;

        return $getData;
    }

    /**
     * 컴백 쿠폰 대상쿠폰 기간확인
     *
     * @author su
     */
    public function checkCouponLimit($couponNo)
    {
        $getData = $this->getCouponInfo($couponNo, '*');

        if ($getData) {
            if ($getData['couponUsePeriodType'] == 'period') {
                if (date('Y-m-d H:i:s') < $getData['couponUsePeriodEndDate']) {
                    return true;
                } else {
                    return false;
                }
            } else {
                if ($getData['couponUseDateLimit'] == null || $getData['couponUseDateLimit'] == '0000-00-00 00:00:00' || date('Y-m-d H:i:s') < $getData['couponUseDateLimit']) {
                    return true;
                } else {
                    return false;
                }
            }
        } else {
            return false;
        }
    }

    /**
     * 컴백 쿠폰 발송
     *
     * @author su
     */
    public function sendComebackCoupon($sno)
    {
        $request = \App::getInstance('request');
        $logger = \App::getInstance('logger');

        // 컴백 쿠폰 정보
        $getData = $this->getComebackCouponInfo($sno, '*');

        if(!$this->checkCouponType($getData['couponNo'])) {
            throw new \Exception(__('발급하고자 하는 쿠폰이 발급종료 상태입니다.<br/>선택된 쿠폰을 수정한 후 다시 시도해주세요.'));
        }
        // 쿠폰 발급대상
        $aMemberList = $aComebackCouponMemberList = $this->getComebackCouponMemberList($getData, 'n', 'y');

        if ($aMemberList['data']) {
            // 설정쿠폰이있으면 쿠폰 발행처리
            if ($getData['couponNo'] > 0) {
                $arrData = [];
                $arrData['couponNo'] = $getData['couponNo'];
                $arrData['couponSaveAdminId'] = '<b>컴백쿠폰</b>' . INT_DIVISION . $sno;
                $arrData['managerNo'] = Session::get('manager.sno');
                $arrData['memberCouponStartDate'] = $this->getMemberCouponStartDate($getData['couponNo']);
                $arrData['memberCouponEndDate'] = $this->getMemberCouponEndDate($getData['couponNo']);
                $arrData['memberCouponState'] = 'y';
            }

            // 설정SMS있으면 SMS발송처리
            if ($getData['smsFl'] == 'y') {
                $smsAdmin = new SmsAdmin();

                // 발신 번호
                $smsSendData['smsCallNum'] = $smsAdmin->getSmsCallNum();
                // 수신거부회원 포함 발송 여부 (y - 수신거부 포함, n - 수신회원만)
                $smsSendData['rejectSend'] = 'n';
                // 발송 설정 (now - 즉시 발송 , reserve - 예약 발송)
                $smsSendData['smsSendType'] = 'now';
                // 예약발송 일자 시간 (yyyy-mm-dd hh:ii:ss)
                //$smsSendData['smsSendReserveDate'] = date('Y-m-d H:i:s', mktime($cartRemindSendData['autoSendTime'],0,0,date('m'),date('d'),date('Y')));
                // 수신 카운팅
                $smsSendData['agreeCnt'] = 0;
                // 수신 거부 카운팅
                $smsSendData['rejectCnt'] = 0;
                // 수신자 데이터
                $smsSendData['receivers'] = [];
                // sms 잔여 포인트
                $smsSendData['smsPoint'] = Sms::getPoint();
                // 전송 내용
                $smsSendData['smsContents'] = str_replace('rc_memNm', 'memNm', $getData['smsContents']);
                // 치환 코드 그룹
                $smsSendData['replaceCodeGroup'] = 'promotion';
                // 전송요청 수
                $result['request'] = count($aMemberList);

                // sms, lms
                if (gd_str_length($getData['smsContents']) > Sms::SMS_STRING_LIMIT) {
                    $smsSendData['sendFl'] = 'lms';
                } else {
                    $smsSendData['sendFl'] = 'sms';
                }
            }

            foreach ($aMemberList['data'] as $val) {
                $tempMemberCouponNo = NULL;
                $saveData = [];
                if ($getData['couponNo'] > 0) {
                    unset($arrData['memNo']);
                    $arrData['memNo'] = $val['memNo'];
                    // 저장
                    if (!$val['couponNo']) { // 이미 발행된 동일쿠폰이 없어야 발행
                        $arrBind = $this->db->get_binding(DBTableField::tableMemberCoupon(), $arrData, 'insert', array_keys($arrData), ['memberCouponNo']);
                        $this->db->set_insert_db(DB_MEMBER_COUPON, $arrBind['param'], $arrBind['bind'], 'y');
                        $this->setCouponMemberSaveCount($getData['couponNo']);

                        $data = $this->db->query_fetch("SELECT memberCouponNo FROM " . DB_MEMBER_COUPON . " WHERE memNo = " . $val['memNo'] . " AND couponNo = " . $getData['couponNo'] . " ORDER BY regDt DESC LIMIT 1");

                        $tempMemberCouponNo = $data[0]['memberCouponNo'];
                    }
                }

                if ($getData['smsFl'] == 'y' && $val['smsFl'] == 'y') {
                    $smsSendData['receivers'][] = [
                        'cellPhone'   => $val['cellPhone'],
                        'replaceCode' => [
                            'memNm'   => $val['memNm'],
                            'rc_mallNm'  => Globals::get('gMall.mallNm'),
                        ],
                    ];
                    $saveData['smsFl'] = 'y';
                    $smsSendData['agreeCnt']++;
                } else {
                    $saveData['smsFl'] = 'n';
                    $smsSendData['rejectCnt']++;
                }

                $saveData['ccSno'] = $sno;
                $saveData['memberCouponNo'] = $tempMemberCouponNo;
                $saveData['memNo'] = $val['memNo'];
                $saveData['groupSno'] = $val['groupSno'];
                $saveData['couponNo'] = $getData['couponNo'];
                if ($getData['couponNo'] > 0 && $getData['smsFl'] == 'n' && $val['couponNo']) {  // 쿠폰만 발송시 기존 쿠폰을 가지고있으면 저장안함
                    //$this->saveComebackCouponMember($saveData);
                } else {  // SMS만발송시는 무조건 저장 / 쿠폰만 발송시는 쿠폰을 발행하는경우만 저장 / 둘다 발송시는 무조건 저장
                    $this->saveComebackCouponMember($saveData);
                }
                unset($saveData);
            }

            if ($getData['smsFl'] == 'y') {
                $smsSendData['password'] = $request->post()->get('password');
                $smsSendData['passwordCheckFl'] = $request->post()->get('passwordCheckFl');
                $cnt = $smsAdmin->sendSms($smsSendData);
                $logger->info(sprintf(__('SMS 발송이 완료되었습니다. (%1$s건 성공 / %2$s건 실패)'), $cnt['success'], $cnt['fail']));
            }

            //combackCoupon테이블 업데이트
            $aUpdateData = [];
            $aUpdateData['sno'] = $sno;
            $aUpdateData['sendDt'] = date('Y-m-d H:i:s');
            $aUpdateBind = $this->db->get_binding(DBTableField::tableComebackCoupon(), $aUpdateData, 'update', array_keys($aUpdateData));
            $this->db->bind_param_push($aUpdateBind['bind'], 'i', $aUpdateData['sno']);
            $this->db->set_update_db(DB_COMEBACK_COUPON, $aUpdateBind['param'], 'sno = ?', $aUpdateBind['bind'], false);
        }

        return true;
    }

    /**
     * 컴백 쿠폰 저장
     *
     * @author su
     */
    public function saveComebackCouponMember(&$arrData)
    {
        $arrBind = $this->db->get_binding(DBTableField::tableComebackCouponMember(), $arrData, 'insert', array_keys($arrData));
        $this->db->set_insert_db(DB_COMEBACK_COUPON_MEMBER, $arrBind['param'], $arrBind['bind'], 'y');
    }

    /**
     * 프로모션 > 쿠폰 관리 > 쿠폰 발급 내역 엑셀 세팅 데이터
     *
     * @param $arrData
     * @return array|string
     */
    public function getCouponAdminListExcel($arrData)
    {
        $arrBind = [];

        if($arrData['whereFl'] == 'search') {
            if($arrData['whereDetail']['keyword']){
                if($arrData['whereDetail']['key'] == 'couponSaveAdminId'){
                    $keyword = 'mc.';
                    $this->arrWhere[] = $keyword . $arrData['whereDetail']['key'] . '= ?';
                    $this->db->bind_param_push($arrBind, 's', $arrData['whereDetail']['keyword']);
                }else if($arrData['whereDetail']['key'] == 'memId' || $arrData['whereDetail']['key'] == 'memNm'){
                    $keyword = 'm.';
                    $this->arrWhere[] = $keyword . $arrData['whereDetail']['key'] . '= ?';
                    $this->db->bind_param_push($arrBind, 's', $arrData['whereDetail']['keyword']);
                }else{
                    if(empty($arrData['whereDetail']['keyword']) === false){
                        $tmpWhere = ['memId', 'memNm'];
                        $arrWhereAll = [];
                        foreach ($tmpWhere as $keyNm) {
                            $arrWhereAll[] = '(m.' . $keyNm . ' LIKE concat(\'%\',?,\'%\'))';
                            $this->db->bind_param_push($arrBind, 's', $arrData['whereDetail']['keyword']);
                        }
                        $tmpWhere = ['couponSaveAdminId'];
                        foreach ($tmpWhere as $keyNm) {
                            $arrWhereAll[] = '(mc.' . $keyNm . ' LIKE concat(\'%\',?,\'%\'))';
                            $this->db->bind_param_push($arrBind, 's', $arrData['whereDetail']['keyword']);
                        }
                        $this->arrWhere[] = '(' . implode(' OR ', $arrWhereAll) . ')';
                    }
                }
            }
            $this->arrWhere[] = '(mc.' . $arrData['whereDetail']['keyDate'] . ' BETWEEN DATE_FORMAT(?,\'%Y-%m-%d 00:00:00\') AND DATE_FORMAT(?,\'%Y-%m-%d 23:59:59\'))';
            $this->db->bind_param_push($arrBind, 's', $arrData['whereDetail']['keywordDate'][0]);
            $this->db->bind_param_push($arrBind, 's', $arrData['whereDetail']['keywordDate'][1]);
            $couponNo = $arrData['whereDetail']['couponNo'];
            $this->arrWhere[] = 'mc.couponNo =?';
            $this->db->bind_param_push($arrBind, $this->fieldTypes['couponOffline']['couponNo'], $couponNo);
        }else{
            $couponNo = $arrData['couponNo'];
            $this->arrWhere[] = 'mc.couponNo =?';
            $this->db->bind_param_push($arrBind, $this->fieldTypes['couponOffline']['couponNo'], $couponNo);
        }

        $sort['fieldName'] = 'mc.regDt';
        $sort['sortMode'] = 'desc';

        $this->db->strField = "mc.memberCouponNo, mc.couponNo, mc.couponSaveAdminId, mc.regDt, mc.memberCouponEndDate, mc.memberCouponUseDate, mc.memberCouponState, m.memNm, m.memId, m.groupSno";
        $this->db->strJoin = 'LEFT JOIN es_member AS m ON mc.memNo = m.memNo LEFT JOIN es_memberSns AS ms ON m.memNo = ms.memNo';
        $this->db->strWhere = implode(' AND ', gd_isset($this->arrWhere));
        $this->db->strOrder = $sort['fieldName'] . ' ' . $sort['sortMode'];

        $query = $this->db->query_complete();

        $strSQL = 'SELECT ' . array_shift($query) . ' FROM ' . DB_MEMBER_COUPON . ' as mc ' . implode(' ', $query);
        $data = $this->db->query_fetch($strSQL, $arrBind);

        return gd_htmlspecialchars_stripslashes(gd_isset($data));

    }

    public function getCouponOfflineAdminListExcel($arrData)
    {
        $arrBind = [];
        // 쿠폰일련번호로 couponAuthType값 체크
        $strSQL = 'SELECT couponAuthType FROM ' . DB_COUPON . ' WHERE couponNo = ?';
        $this->db->bind_param_push($arrBind, 's', $arrData['whereDetail']['couponNo']);
        $data = $this->db->query_fetch($strSQL, $arrBind, false);
        unset($arrBind);

        if($arrData['whereFl'] == 'search') {
            if ($data['couponAuthType'] == 'n') { // 페이퍼 쿠폰 인증번호 타입 : 1개의 인증번호
                $this->db->strField = "coc.couponOfflineCodeUser, mc.regDt, mc.memberCouponEndDate, mc.memberCouponUseDate, mc.memberCouponState, m.memId, m.memNm, m.groupSno ";
                $this->db->strJoin = " LEFT JOIN " . DB_COUPON_OFFLINE_CODE . " AS coc ON coc.couponNo = mc.couponNo LEFT JOIN " . DB_MEMBER . " AS m ON m.memNo = mc.memNo LEFT JOIN " . DB_MEMBER_SNS . " AS ms ON m.memNo = ms.memNo";
                if($arrData['whereDetail']['keyword']){
                    if($arrData['whereDetail']['key'] == 'couponSaveAdminId'){
                        $keyword = 'mc.';
                        $this->arrWhere[] = $keyword . $arrData['whereDetail']['key'] . '= ?';
                        $this->db->bind_param_push($arrBind, 's', $arrData['whereDetail']['keyword']);
                    }else if($arrData['whereDetail']['key'] == 'memId' || $arrData['whereDetail']['key'] == 'memNm'){
                        $keyword = 'm.';
                        $this->arrWhere[] = $keyword . $arrData['whereDetail']['key'] . '= ?';
                        $this->db->bind_param_push($arrBind, 's', $arrData['whereDetail']['keyword']);
                    }else{
                        if(empty($arrData['whereDetail']['keyword']) === false){
                            $tmpWhere = ['memId', 'memNm'];
                            $arrWhereAll = [];
                            foreach ($tmpWhere as $keyNm) {
                                $arrWhereAll[] = '(m.' . $keyNm . ' LIKE concat(\'%\',?,\'%\'))';
                                $this->db->bind_param_push($arrBind, 's', $arrData['whereDetail']['keyword']);
                            }
                            $tmpWhere = ['couponSaveAdminId'];
                            foreach ($tmpWhere as $keyNm) {
                                $arrWhereAll[] = '(mc.' . $keyNm . ' LIKE concat(\'%\',?,\'%\'))';
                                $this->db->bind_param_push($arrBind, 's', $arrData['whereDetail']['keyword']);
                            }
                            $this->arrWhere[] = '(' . implode(' OR ', $arrWhereAll) . ')';
                        }
                    }
                }
                $this->arrWhere[] = '(mc.' . $arrData['whereDetail']['keyDate'] . ' BETWEEN DATE_FORMAT(?,\'%Y-%m-%d 00:00:00\') AND DATE_FORMAT(?,\'%Y-%m-%d 23:59:59\'))';
                $this->db->bind_param_push($arrBind, 's', $arrData['whereDetail']['keywordDate'][0]);
                $this->db->bind_param_push($arrBind, 's', $arrData['whereDetail']['keywordDate'][1]);
                $couponNo = $arrData['whereDetail']['couponNo'];
                $this->arrWhere[] = 'mc.couponNo =?';
                $this->db->bind_param_push($arrBind, $this->fieldTypes['couponOffline']['couponNo'], $couponNo);
                $this->db->strWhere = implode(' AND ', gd_isset($this->arrWhere));
                $this->db->strOrder = ' mc.regDt DESC';
                $query = $this->db->query_complete();

                $strSQL = 'SELECT ' . array_shift($query) . ' FROM ' . DB_MEMBER_COUPON . ' as mc ' . implode(' ', $query);
                $result = $this->db->query_fetch($strSQL, $arrBind);
                unset($arrBind);

            } else {  // 페이퍼 쿠폰 인증번호 타입 : 회원별 인증번호
                $this->db->strField = "coc.couponOfflineCodeUser, mc.regDt, mc.memberCouponEndDate, mc.memberCouponUseDate, mc.memberCouponState, m.memId, m.memNm, m.groupSno ";
                $this->db->strJoin = " LEFT JOIN " . DB_COUPON_OFFLINE_CODE . " AS coc ON coc.memberCouponNo = mc.memberCouponNo LEFT JOIN " . DB_MEMBER . " AS m ON m.memNo = mc.memNo LEFT JOIN " . DB_MEMBER_SNS . " AS ms ON m.memNo = ms.memNo";
                if($arrData['whereDetail']['keyword']){
                    if($arrData['whereDetail']['key'] == 'couponSaveAdminId'){
                        $keyword = 'mc.';
                        $this->arrWhere[] = $keyword . $arrData['whereDetail']['key'] . '= ?';
                        $this->db->bind_param_push($arrBind, 's', $arrData['whereDetail']['keyword']);
                    }else if($arrData['whereDetail']['key'] == 'memId' || $arrData['whereDetail']['key'] == 'memNm'){
                        $keyword = 'm.';
                        $this->arrWhere[] = $keyword . $arrData['whereDetail']['key'] . '= ?';
                        $this->db->bind_param_push($arrBind, 's', $arrData['whereDetail']['keyword']);
                    }else{
                        if(empty($arrData['whereDetail']['keyword']) === false){
                            $tmpWhere = ['memId', 'memNm'];
                            $arrWhereAll = [];
                            foreach ($tmpWhere as $keyNm) {
                                $arrWhereAll[] = '(m.' . $keyNm . ' LIKE concat(\'%\',?,\'%\'))';
                                $this->db->bind_param_push($arrBind, 's', $arrData['whereDetail']['keyword']);
                            }
                            $tmpWhere = ['couponSaveAdminId'];
                            foreach ($tmpWhere as $keyNm) {
                                $arrWhereAll[] = '(mc.' . $keyNm . ' LIKE concat(\'%\',?,\'%\'))';
                                $this->db->bind_param_push($arrBind, 's', $arrData['whereDetail']['keyword']);
                            }
                            $this->arrWhere[] = '(' . implode(' OR ', $arrWhereAll) . ')';
                        }
                    }
                }
                $this->arrWhere[] = '(mc.' . $arrData['whereDetail']['keyDate'] . ' BETWEEN DATE_FORMAT(?,\'%Y-%m-%d 00:00:00\') AND DATE_FORMAT(?,\'%Y-%m-%d 23:59:59\'))';
                $this->db->bind_param_push($arrBind, 's', $arrData['whereDetail']['keywordDate'][0]);
                $this->db->bind_param_push($arrBind, 's', $arrData['whereDetail']['keywordDate'][1]);
                $couponNo = $arrData['whereDetail']['couponNo'];
                $this->arrWhere[] = 'mc.couponNo =?';
                $this->db->bind_param_push($arrBind, $this->fieldTypes['couponOffline']['couponNo'], $couponNo);
                $this->db->strWhere = implode(' AND ', gd_isset($this->arrWhere));
                $this->db->strOrder = ' mc.regDt DESC';
                $query = $this->db->query_complete();

                $strSQL = 'SELECT ' . array_shift($query) . ' FROM ' . DB_MEMBER_COUPON . ' as mc ' . implode(' ', $query);
                $result = $this->db->query_fetch($strSQL, $arrBind);
                unset($arrBind);
            }
        }else{
            $this->db->strField = "coc.couponOfflineCodeUser, mc.regDt, mc.memberCouponEndDate, mc.memberCouponUseDate, mc.memberCouponState, m.memId, m.memNm, m.groupSno ";
            if($data['couponAuthType'] == 'n'){
                $this->db->strJoin = " LEFT JOIN " . DB_COUPON_OFFLINE_CODE . " AS coc ON coc.couponNo = mc.couponNo LEFT JOIN " . DB_MEMBER . " AS m ON m.memNo = mc.memNo LEFT JOIN " . DB_MEMBER_SNS . " AS ms ON m.memNo = ms.memNo";
            }else{
                $this->db->strJoin = " LEFT JOIN " . DB_COUPON_OFFLINE_CODE . " AS coc ON coc.memberCouponNo = mc.memberCouponNo LEFT JOIN " . DB_MEMBER . " AS m ON m.memNo = mc.memNo LEFT JOIN " . DB_MEMBER_SNS . " AS ms ON m.memNo = ms.memNo";
            }
            $couponNo = $arrData['couponNo'];
            $this->arrWhere[] = 'mc.couponNo =?';
            $this->db->bind_param_push($arrBind, $this->fieldTypes['couponOffline']['couponNo'], $couponNo);
            $this->db->strWhere = implode(' AND ', gd_isset($this->arrWhere));
            $this->db->strOrder = ' mc.regDt DESC';
            $query = $this->db->query_complete();

            $strSQL = 'SELECT ' . array_shift($query) . ' FROM ' . DB_MEMBER_COUPON . ' as mc ' . implode(' ', $query);
            $result = $this->db->query_fetch($strSQL, $arrBind);
            unset($arrBind);
        }

        return gd_htmlspecialchars_stripslashes(gd_isset($result));

    }


    /**
     * 쿠폰존 설정 저장
     *
     * @param $arrData
     * @param $files
     */
    public function saveCouponzone($arrData, $files)
    {
        $couponConfigArrData = [
            'useFl'                     => $arrData['useFl'],
            'autoDisplayFl'             => $arrData['autoDisplayFl'],
            'couponzoneSort'            => $arrData['couponzoneSort'],
            'groupNm'                   => $arrData['groupNm'],
            'groupCoupon'               => $arrData['groupCoupon'],
            'unexposedCoupon'           => $arrData['unexposedCoupon'],
            'couponImageType'           => $arrData['couponImageType'],
            'descriptionSameFl'         => gd_isset($arrData['descriptionSameFl'], 'n'),
            'pcContents'                => $arrData['pcContents'],
            'mobileContents'            => $arrData['mobileContents'],
            'pcCouponImage'             => $arrData['pcCouponImage'],
            'mobileCouponImage'         => $arrData['mobileCouponImage'],
        ];

        // 쿠폰 이미지
        if (ArrayUtils::isEmpty($files['pcCouponImageFile']) === false) {
            $file = $files['pcCouponImageFile'];
            if ($file['error'] == 0 && $file['size']) {
                if (gd_file_uploadable($file, 'image') == true) {
                    $saveFileName = substr(md5(microtime()), 0, 8) . rand(100, 999);
                    $this->storage()->upload($file['tmp_name'], $saveFileName);
                    $couponConfigArrData['pcCouponImage'] = $saveFileName;
                } else {
                    throw new \Exception(__('이미지파일만 가능합니다.'));
                }
            }
        }
        if (ArrayUtils::isEmpty($files['mobileCouponImageFile']) === false) {
            $file = $files['mobileCouponImageFile'];
            if ($file['error'] == 0 && $file['size']) {
                if (gd_file_uploadable($file, 'image') == true) {
                    $saveFileName = substr(md5(microtime()), 0, 8) . rand(100, 999);
                    $this->storage()->upload($file['tmp_name'], $saveFileName);
                    $couponConfigArrData['mobileCouponImage'] = $saveFileName;
                } else {
                    throw new \Exception(__('이미지파일만 가능합니다.'));
                }
            }
        }
        gd_set_policy('coupon.couponzone', $couponConfigArrData);
    }

    public function getCouponZoneGroupCoupon($arrData)
    {
        if(empty($arrData)) return false;
        $groupCoupon = [];
        foreach($arrData as $sort => $v) {
            $data = [];
            foreach($v as $key => $val) {
                $data[$key] = $this->getCouponInfo($val);
            }
            $groupCoupon[$sort] = $this->convertCouponAdminArrData($data);
        }
        return $groupCoupon;
    }
}
