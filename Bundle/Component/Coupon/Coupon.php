<?php
/**
 *
 * This is commercial software, only users who have purchased a valid license
 * and accept to the terms of the License Agreement can install and use this
 * program.
 *
 * Do not edit or add to this file if you wish to upgrade Godomall5 to newer
 * versions in the future.
 *
 * @copyright ⓒ 2016, NHN godo: Corp.
 * @link      http://www.godo.co.kr
 *
 */

namespace Bundle\Component\Coupon;

use Component\Database\DBTableField;
use Component\Member\Group\Util as GroupUtil;
use Component\Member\Util\MemberUtil;
use Component\Deposit\Deposit;
use Component\Mileage\Mileage;
use Component\Sms\Code;
use Component\Sms\SmsAutoCode;
use Component\Storage\Storage;
use Component\Cart\CartAdmin;
use Framework\Object\SimpleStorage;
use Framework\Utility\ArrayUtils;
use Globals;
use Request;
use Session;

/**
 * Coupon Class
 *
 * @author    sj, artherot
 * @version   1.0
 * @since     1.0
 * @copyright ⓒ 2016, NHN godo: Corp.
 */
class Coupon
{
    const ERROR_VIEW = 'ERROR_VIEW';

    const ECT_ALREADY_GIVE = 'Coupon.ECT_ALREADY_GIVE';
    const ECT_NOTMATCH_SERIAL = 'Coupon.ECT_NOTMATCH_SERIAL';
    const ECT_WRONG_EXPIREDATE = 'Coupon.ECT_WRONG_EXPIREDATE';
    const ECT_EXCEED_USECOUNT = 'Coupon.ECT_EXCEED_USECOUNT';
    const TEXT_ALREADY_GIVE = '이미 사용된 인증번호입니다.';
    const TEXT_NOTMATCH_SERIAL = '일치하는 인증번호가 없습니다.';
    const TEXT_WRONG_EXPIREDATE = '현재 쿠폰의 유효기간이 아닙니다.';
    const TEXT_EXCEED_USECOUNT = '%s개 까지만 인증이 가능합니다.';

    const TEXT_NOT_EXIST_COUPON = 'NOT_EXIST_COUPON';
    const TEXT_LOGIN_CHECK = 'LOGIN_CHECK';
    const TEXT_OVER_COUPON_CNT = 'OVER_COUPON_CNT';
    const TEXT_DUPLICATION_COUPON = 'DUPLICATION_COUPON';
    const TEXT_EXIST_COUPON_GIVE = 'EXIST_COUPON_GIVE';
    const TEXT_NOT_EXIST_GOODSNO = 'NOT_EXIST_GOODSNO';
    const TEXT_NOT_EXIST_CARTSNO = 'NOT_EXIST_CARTSNO';

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

    protected $storage;
    protected $fieldTypes;

    /** @var  SimpleStorage */
    protected $resultStorage;

    protected $couponTrunc;
    protected $couponConfig;

    public $productCouponChangeLimitVersionFl; // 상품쿠폰 주문서페이지 사용여부 패치 SRC 버전체크

    private $birthDayCouponYear;

    /**
     * 생성자
     */
    public function __construct()
    {
        if (!is_object($this->db)) {
            $this->db = \App::load('DB');
        }
        // 쿠폰에서 사용되는 데이터베이스 테이블 필드 정의
        $this->fieldTypes['coupon'] = DBTableField::getFieldTypes('tableCoupon');
        $this->fieldTypes['couponOffline'] = DBTableField::getFieldTypes('tableCouponOfflineCode');
        $this->fieldTypes['memberCoupon'] = DBTableField::getFieldTypes('tableMemberCoupon');

        // 파일 핸들링을 위한 클래스 정의(쿠폰이미지)
        $this->storage = null;

        if (Request::isCli()) {
            $this->couponTrunc = gd_policy('basic.trunc')['coupon'];
        } else {
            $this->couponTrunc = Globals::get('gTrunc.coupon');
        }

        $this->couponConfig = gd_policy('coupon.config');

        $globals = \App::getInstance('globals');
        $gLicense = $globals->get('gLicense');
        if ($gLicense['sdate'] < 20180417) {
            $noticeFl = true;
        } else {
            $noticeFl = false;
        }
        $this->productCouponChangeLimitVersionFl = $noticeFl; // 상품쿠폰 주문서페이지 사용여부 패치 SRC 버전체크
    }

    /**
     * 쿠폰 정보 출력
     *
     * 완성된 쿼리문은 $db->strField , $db->strJoin , $db->strWhere , $db->strGroup , $db->strOrder , $db->strLimit 멤버 변수를
     * 이용할수 있습니다.
     *
     * @param string      $couponNo    쿠폰 고유 번호 (기본 null)
     * @param string      $couponField 출력할 필드명 (기본 null)
     * @param array       $arrBind     bind 처리 배열 (기본 null)
     * @param bool|string $dataArray   return 값을 배열처리 (기본값 false)
     *
     * @return array 쿠폰 정보
     *
     * @author su
     */
    public function getCouponInfo($couponNo = null, $couponField = null, $arrBind = null, $dataArray = false)
    {
        if (is_null($arrBind)) {
            // $arrBind = array();
        }
        if ($couponNo) {
            if ($this->db->strWhere) {
                $this->db->strWhere = " c.couponNo = ? AND " . $this->db->strWhere;
            } else {
                $this->db->strWhere = " c.couponNo = ?";
            }
            $this->db->bind_param_push($arrBind, 'i', $couponNo);
        }
        if ($couponField) {
            if ($this->db->strField) {
                $this->db->strField = $couponField . ', ' . $this->db->strField;
            } else {
                $this->db->strField = $couponField;
            }
        }
        $query = $this->db->query_complete();
        $strSQL = 'SELECT ' . array_shift($query) . ' FROM ' . DB_COUPON . ' as c ' . implode(' ', $query);
        $getData = $this->db->query_fetch($strSQL, $arrBind);

        if (count($getData) == 1 && $dataArray === false) {
            return gd_htmlspecialchars_stripslashes($getData[0]);
        }

        return gd_htmlspecialchars_stripslashes($getData);
    }

    /**
     * 오프라인 쿠폰인증번호 정보 출력
     *
     * 완성된 쿼리문은 $db->strField , $db->strJoin , $db->strWhere , $db->strGroup , $db->strOrder , $db->strLimit 멤버 변수를
     * 이용할수 있습니다.
     *
     * @param string      $couponNo    쿠폰 고유 번호 (기본 null)
     * @param string      $couponField 출력할 필드명 (기본 null)
     * @param array       $arrBind     bind 처리 배열 (기본 null)
     * @param bool|string $dataArray   return 값을 배열처리 (기본값 false)
     *
     * @return array 쿠폰 정보
     *
     * @author su
     */
    public function getCouponOfflineInfo($couponNo = null, $couponField = null, $arrBind = null, $dataArray = false)
    {
        if (is_null($arrBind)) {
            // $arrBind = array();
        }
        if ($couponNo) {
            if ($this->db->strWhere) {
                $this->db->strWhere = " coc.couponNo = ? AND " . $this->db->strWhere;
            } else {
                $this->db->strWhere = " coc.couponNo = ?";
            }
            $this->db->bind_param_push($arrBind, 'i', $couponNo);
        }
        if ($couponField) {
            if ($this->db->strField) {
                $this->db->strField = $couponField . ', ' . $this->db->strField;
            } else {
                $this->db->strField = $couponField;
            }
        }
        $query = $this->db->query_complete();
        $strSQL = 'SELECT ' . array_shift($query) . ' FROM ' . DB_COUPON_OFFLINE_CODE . ' as coc ' . implode(' ', $query);
        $getData = $this->db->query_fetch($strSQL, $arrBind);

        if (count($getData) == 1 && $dataArray === false) {
            return gd_htmlspecialchars_stripslashes($getData[0]);
        }

        return gd_htmlspecialchars_stripslashes($getData);
    }

    /**
     * 회원쿠폰 발급시 사용시작일 계산
     *
     * @param integer $couponNo 쿠폰 고유 번호
     *
     * @return datetime $couponStartDate 사용시작일
     *
     * @author su
     */
    public function getMemberCouponStartDate($couponNo)
    {
        $couponData = $this->getCouponInfo($couponNo, 'couponUsePeriodType, couponUsePeriodStartDate , couponUsePeriodEndDate, couponUsePeriodDay');
        // 기간쿠폰
        if ($couponData['couponUsePeriodType'] == 'period') {
            $couponStartDate = $couponData['couponUsePeriodStartDate'];
            // 발급 후 몇일
        } else if ($couponData['couponUsePeriodType'] == 'day') {
            $couponStartDate = date('Y-m-d H:i:s');
        }

        return $couponStartDate;
    }

    /**
     * 회원쿠폰 발급시 사용만료일 계산
     *
     * @param integer $couponNo 쿠폰 고유 번호
     *
     * @return datetime $couponEndDate 사용만료일
     *
     * @author su
     */
    public function getMemberCouponEndDate($couponNo)
    {
        $couponData = $this->getCouponInfo($couponNo, 'couponUsePeriodType, couponUsePeriodEndDate, couponUsePeriodDay, couponUseDateLimit');
        // 기간쿠폰
        if ($couponData['couponUsePeriodType'] == 'period') {
            $couponEndDate = $couponData['couponUsePeriodEndDate'];
            // 발급 후 몇일
        } else if ($couponData['couponUsePeriodType'] == 'day') {
            $couponEndDate = strtotime('+' . $couponData['couponUsePeriodDay'] . ' day', time());
            $couponEndDateLimit = strtotime($couponData['couponUseDateLimit']);
            if ($couponEndDateLimit > 0) {
                if ($couponEndDate > $couponEndDateLimit) {
                    $couponEndDate = $couponEndDateLimit;
                }
            }
            $couponEndDate = date('Y-m-d H:i:s', $couponEndDate);
        }

        return $couponEndDate;
    }

    /**
     * 쿠폰의 발급된 총 쿠폰 수 가져오기
     *
     * 1. 쿠폰고유번호로 발급된 총 쿠폰수
     * 2. 회원고유번호로 회원에 발급된 총 쿠폰수
     * 3. 쿠폰고유번호 + 회원고유번호로 해당쿠폰의 해당회원에 발급된 쿠폰 수
     *
     * @param integer $couponNo 쿠폰 고유 번호
     * @param integer $memNo    회원 고유 번호
     *
     * @return integer $countMemberCouponData 쿠폰수
     *
     * @author su
     */
    public function getMemberCouponTotalCount($couponNo = null, $memNo = null)
    {
        $arrBind = [];
        $arrWhere = [];
        $this->db->strField = 'count(memNo) as countMemberCoupon';
        if ($couponNo > 0) {// 쿠폰 고유번호 기준
            $arrWhere[] = 'mc.couponNo=?';
            $this->db->bind_param_push($arrBind, 'i', $couponNo);
        }
        if ($memNo > 0) {// 회원 고유번호 기준
            $arrWhere[] = 'mc.memNo=?';
            $this->db->bind_param_push($arrBind, 'i', $memNo);
        }
        $this->db->strWhere = implode(' AND ', gd_isset($arrWhere));
        $query = $this->db->query_complete();
        $strSQL = 'SELECT ' . array_shift($query) . ' FROM ' . DB_MEMBER_COUPON . ' as mc ' . implode(' ', $query);
        $getData = $this->db->query_fetch($strSQL, $arrBind);
        $countMemberCouponData = $getData[0]['countMemberCoupon'];
        unset($arrBind);
        unset($arrWhere);
        unset($getData);

        return $countMemberCouponData;
    }

    /**
     * 쿠폰이 등록된 이벤트 수 가져오기
     *
     * @param integer $couponNo 쿠폰 고유 번호
     *
     * @return integer $getEventCouponTotalCount 이벤트 수
     *
     * @author su
     */
    public function getEventCouponTotalCount($couponNo)
    {
        $arrBind = [];
        $arrWhere = [];
        $this->db->strField = 'count(cr.cartRemindCoupon) as countCartRemind';
        $arrWhere[] = 'cr.cartRemindCoupon=?';
        $this->db->bind_param_push($arrBind, 'i', $couponNo);
        $this->db->strWhere = implode(' AND ', gd_isset($arrWhere));
        $query = $this->db->query_complete();
        $strSQL = 'SELECT ' . array_shift($query) . ' FROM ' . DB_CART_REMIND . ' as cr ' . implode(' ', $query);
        $getData = $this->db->query_fetch($strSQL, $arrBind);
        $countCartRemind = $getData[0]['countCartRemind'];
        unset($arrBind);
        unset($arrWhere);
        unset($getData);

        $arrBind = [];
        $arrWhere = [];
        $this->db->strField = 'count(a.benefitCouponSno) as countAttendance';
        $arrWhere[] = 'a.benefitCouponSno=?';
        $this->db->bind_param_push($arrBind, 'i', $couponNo);
        $this->db->strWhere = implode(' AND ', gd_isset($arrWhere));
        $query = $this->db->query_complete();
        $strSQL = 'SELECT ' . array_shift($query) . ' FROM ' . DB_ATTENDANCE . ' as a ' . implode(' ', $query);
        $getData = $this->db->query_fetch($strSQL, $arrBind);
        $countAttendance = $getData[0]['countAttendance'];
        unset($arrBind);
        unset($arrWhere);
        unset($getData);

        $arrBind = [];
        $arrWhere = [];
        $this->db->strField = 'count(mme.benefitCouponSno) as countMemberModifyEvent';
        $arrWhere[] = 'mme.benefitCouponSno=?';
        $this->db->bind_param_push($arrBind, 'i', $couponNo);
        $this->db->strWhere = implode(' AND ', gd_isset($arrWhere));
        $query = $this->db->query_complete();
        $strSQL = 'SELECT ' . array_shift($query) . ' FROM ' . DB_MEMBER_MODIFY_EVENT . ' as mme ' . implode(' ', $query);
        $getData = $this->db->query_fetch($strSQL, $arrBind);
        $countMemberModifyEvent = $getData[0]['countMemberModifyEvent'];
        unset($arrBind);
        unset($arrWhere);
        unset($getData);

        $getEventCouponTotalCount = $countCartRemind + $countAttendance + $countMemberModifyEvent;

        return $getEventCouponTotalCount;
    }

    /**
     * 발급된 해당 쿠폰의 전체 상태
     *
     * 중복쿠폰에 대한 발급 기준을 잡기위한 상태여부
     * 쿠폰고유번호 + 회원고유번호로 해당쿠폰(중복발급된쿠폰까지의)을 해당회원에 발급된 쿠폰들의 상태 처리
     *
     * @param integer $couponNo 쿠폰 고유 번호
     * @param integer $memNo    회원 고유 번호
     *
     * @return boolean $stateMemberCoupon 쿠폰사용여부 상태 (true / false)
     *
     * @author su
     */
    public function getMemberCouponState($couponNo, $memNo)
    {
        $arrBind = [];
        $arrWhere = [];
        $this->db->strField = 'mc.memberCouponState as memberCouponState';
        if ($couponNo > 0) {// 쿠폰 고유번호 기준
            $arrWhere[] = 'mc.couponNo=?';
            $this->db->bind_param_push($arrBind, 'i', $couponNo);
        }
        if ($memNo > 0) {// 회원 고유번호 기준
            $arrWhere[] = 'mc.memNo=?';
            $this->db->bind_param_push($arrBind, 'i', $memNo);
        }
        $this->db->strWhere = implode(' AND ', gd_isset($arrWhere));
        $query = $this->db->query_complete();
        $strSQL = 'SELECT ' . array_shift($query) . ' FROM ' . DB_MEMBER_COUPON . ' as mc ' . implode(' ', $query);
        $getData = $this->db->query_fetch($strSQL, $arrBind);
        unset($arrBind);
        unset($arrWhere);

        $yesMemberCoupon = 0;
        foreach ($getData as $couponVal) {
            if (($couponVal['memberCouponState'] == 'y') || ($couponVal['memberCouponState'] == 'cart')) {
                $yesMemberCoupon++;
            }
        }

        if ($yesMemberCoupon > 0) {
            $stateMemberCoupon = false;
        } else {
            $stateMemberCoupon = true;
        }

        return gd_htmlspecialchars_stripslashes($stateMemberCoupon);
    }

    /**
     * 회원쿠폰의 사용 가능한 쿠폰 수 가져오기
     *
     * 마이페이지 보유 쿠폰 표시
     * 쿠폰고유번호 + 회원고유번호로 사용가능한 쿠폰수
     *
     * @param integer $couponNo 쿠폰 고유 번호
     * @param integer $memNo    회원 고유 번호
     *
     * @return integer $countMemberCouponData 사용가능 회원쿠폰 수
     *
     * @author su
     */
    public function getMemberCouponUsableCount($couponNo, $memNo = 0)
    {
        $arrBind = [];
        $arrWhere = [];
        $this->db->strField = 'count(memNo) as countMemberCoupon';
        $arrWhere[] = 'mc.memberCouponState="y" AND mc.memberCouponStartDate <= NOW() AND mc.memberCouponEndDate >= NOW()';
        if ($couponNo > 0) {// 쿠폰 고유번호 기준
            $arrWhere[] = 'mc.couponNo=?';
            $this->db->bind_param_push($arrBind, 'i', $couponNo);
        }
        if ($memNo > 0) {// 회원 고유번호 기준
            $arrWhere[] = 'mc.memNo=?';
            $this->db->bind_param_push($arrBind, 'i', $memNo);
        } else {
            return 0;
        }
        $this->db->strWhere = implode(' AND ', gd_isset($arrWhere));
        $query = $this->db->query_complete();
        $strSQL = 'SELECT ' . array_shift($query) . ' FROM ' . DB_MEMBER_COUPON . ' as mc ' . implode(' ', $query);
        $getData = $this->db->query_fetch($strSQL, $arrBind);
        $countMemberCouponData = $getData[0]['countMemberCoupon'];
        unset($arrBind);
        unset($arrWhere);
        unset($getData);

        return gd_htmlspecialchars_stripslashes($countMemberCouponData);
    }

    /**
     * 회원쿠폰의 사용 가능 여부
     * 회원쿠폰고유번호 사용가능한 쿠폰체크
     *
     * @param string $memberCouponNo 회원쿠폰 고유번호 (INT_DIVISION 으로 구분된 회원쿠폰 고유번호)
     *
     * @return boolean $memberCouponUsable 회원쿠폰 사용가능 여부
     *
     * @author   su
     */
    public function getMemberCouponUsableCheck($memberCouponNo)
    {
        $memberCouponArrNo = explode(INT_DIVISION, $memberCouponNo);
        $memberCouponUsable = true;
        foreach ($memberCouponArrNo as $val) {
            $memberCouponState = $this->getMemberCouponInfo($val, 'mc.memberCouponState,mc.memberCouponStartDate,mc.memberCouponEndDate');
            if ($memberCouponState['memberCouponState'] == 'y') {
                // 쿠폰 사용 시작일
                if (strtotime($memberCouponState['memberCouponStartDate']) > 0) {
                    if (strtotime($memberCouponState['memberCouponStartDate']) > time()) { // 쿠폰 사용 시작일이 지금 시간 보다 크다면 제한
                        $usable = 'EXPIRATION_START_PERIOD';
                    } else {
                        $usable = 'YES';
                    }
                    // 쿠폰 사용 만료일
                } else if (strtotime($memberCouponState['memberCouponEndDate']) > 0) {
                    if (strtotime($memberCouponState['memberCouponEndDate']) < time()) { // 쿠폰 사용 만료일이 지금 시간 보다 작다면 제한
                        $usable = 'EXPIRATION_END_PERIOD';
                    } else {
                        $usable = 'YES';
                    }
                } else {
                    $usable = 'YES';
                }
            } else if ($memberCouponState['memberCouponState'] == 'cart') {
                // 상품쿠폰 주문에서 적용 시
                $usable = 'USE_CART';
            } else if ($memberCouponState['memberCouponState'] == 'order') {
                $usable = 'USE_ORDER';
            } else if ($memberCouponState['memberCouponState'] == 'coupon') {
                $usable = 'USE_COUPON';
            }
            if (($usable == 'USE_CART' || $usable == 'YES') && $memberCouponUsable) {
                $memberCouponUsable = true;
            } else {
                $memberCouponUsable = false;
            }
        }

        return $memberCouponUsable;
    }

    public function getMemberCouponUsableCheckOrderWrite($memberCouponNo, $useablePassd=false)
    {
        //수기주문에서 회원 장바구니 추가 페이지에서의 접근은 이미 쿠폰이 사용되어 있는 경우이므로 체크를 패스한다.
        if($useablePassd === true){
            return true;
        }

        $memberCouponArrNo = explode(INT_DIVISION, $memberCouponNo);
        $memberCouponUsable = true;
        foreach ($memberCouponArrNo as $val) {
            $memberCouponState = $this->getMemberCouponInfo($val, 'mc.memberCouponState,mc.orderWriteCouponState,mc.memberCouponStartDate,mc.memberCouponEndDate,mc.couponNo');
            if($memberCouponState['memberCouponState'] == 'y'){
                if ($memberCouponState['orderWriteCouponState'] == 'y') {
                    // 쿠폰 사용 시작일
                    if (strtotime($memberCouponState['memberCouponStartDate']) > 0) {
                        if (strtotime($memberCouponState['memberCouponStartDate']) > time()) { // 쿠폰 사용 시작일이 지금 시간 보다 크다면 제한
                            $usable = 'EXPIRATION_START_PERIOD';
                        } else {
                            $usable = 'YES';
                        }
                    }

                    // 쿠폰 사용 만료일
                    if (strtotime($memberCouponState['memberCouponEndDate']) > 0) {
                        if (strtotime($memberCouponState['memberCouponEndDate']) < time()) { // 쿠폰 사용 만료일이 지금 시간 보다 작다면 제한
                            $usable = 'EXPIRATION_END_PERIOD';
                        } else {
                            $usable = 'YES';
                        }
                    }
                } else if ($memberCouponState['orderWriteCouponState'] == 'cart') {
                    $usable = 'USE_CART';
                } else if ($memberCouponState['orderWriteCouponState'] == 'coupon') {
                    $usable = 'USE_COUPON';
                }
            }
            else if ($memberCouponState['memberCouponState'] == 'cart') {
                $usable = 'USE_CART';
            }
            else if ($memberCouponState['memberCouponState'] == 'order') {
                $usable = 'USE_ORDER';
            }
            else if ($memberCouponState['memberCouponState'] == 'coupon') {
                $usable = 'USE_COUPON';
            }

            if (($usable == 'USE_CART' || $usable == 'YES') && $memberCouponUsable) {
                $memberCouponUsable = true;
            } else {
                // 쿠폰 유효기간 지난 쿠폰은 발급종료 상태 변경
                if ($usable === 'EXPIRATION_END_PERIOD') {
                    $this->checkCouponType($memberCouponState['couponNo']);
                }
                $memberCouponUsable = false;
            }
        }

        return $memberCouponUsable;
    }

    /**
     * 회원쿠폰의 상태 표시
     * 회원쿠폰고유번호의 회원쿠폰 상태 표시
     *
     * @param array $memberCouponData 회원쿠폰 데이터
     *
     * @return string $memberCouponState 회원쿠폰 상태
     *
     * @author su
     */
    public function getMemberCouponUsableDisplay($memberCouponData)
    {
        $memberCouponUsable = '';
        foreach ($memberCouponData as $memberCouponKey => $memberCouponVal) {
            if ($memberCouponVal['memberCouponState'] == 'y') {
                // 쿠폰 사용 시작일 - 쿠폰 사용 시작일이 지금 시간 보다 크다면 제한
                if ((strtotime($memberCouponVal['memberCouponStartDate']) > 0) && (strtotime($memberCouponVal['memberCouponStartDate']) > time())) {
                    $memberCouponUsable = 'EXPIRATION_START_PERIOD';
                    // 쿠폰 사용 만료일 - 쿠폰 사용 만료일이 지금 시간 보다 작다면 제한
                } else if ((strtotime($memberCouponVal['memberCouponEndDate']) > 0) && (strtotime($memberCouponVal['memberCouponEndDate']) < time())) {
                    $memberCouponUsable = 'EXPIRATION_END_PERIOD';
                } else {
                    $memberCouponUsable = 'YES';
                }
            } else if ($memberCouponVal['memberCouponState'] == 'cart') {
                $memberCouponUsable = 'USE_CART';
            } else if ($memberCouponVal['memberCouponState'] == 'order') {
                $memberCouponUsable = 'USE_ORDER';
            } else if ($memberCouponVal['memberCouponState'] == 'coupon') {
                $memberCouponUsable = 'USE_COUPON';
            }
            $memberCouponData[$memberCouponKey]['memberCouponUsable'] = $memberCouponUsable;
        }

        return $memberCouponData;
    }

    /**
     * 쿠폰 Arr 의 발급된 총 쿠폰 수 Arr 가져오기
     *
     * 마이페이지 보유 쿠폰 표시
     * 쿠폰고유번호 + 회원고유번호로 사용가능한 쿠폰수
     *
     * @param array $couponArrData 쿠폰정보의 데이터
     *
     * @return array $countMemberCouponArrData 쿠폰의 해당 쿠폰의 발급수
     *
     * @author su
     */
    public function getMemberCouponArrTotalCount($couponArrData)
    {
        foreach ($couponArrData as $key => $val) {
            $arrBind = [];
            $couponNo = (int) $val['couponNo'];
            $this->db->strField = 'count(memNo) as countMemberCoupon';
            $this->db->strWhere = "mc.couponNo = ?";
            $this->db->bind_param_push($arrBind, 'i', $couponNo);

            $query = $this->db->query_complete();
            $strSQL = 'SELECT ' . array_shift($query) . ' FROM ' . DB_MEMBER_COUPON . ' as mc ' . implode(' ', $query);
            $getData = $this->db->query_fetch($strSQL, $arrBind);
            $countMemberCouponArrData[$couponNo] = $getData[0]['countMemberCoupon'];
            unset($arrBind);
            unset($getData);
        }

        return gd_htmlspecialchars_stripslashes($countMemberCouponArrData);
    }

    /**
     * 오프라인 쿠폰 리스트에서 입력한 코드에 해당하는 오프라인 쿠폰 리스트
     *
     * @param string  $couponOfflineNumber 상품고유번호
     * @param integer $memNo               회원고유번호
     * @param integer $memGroupNo          회원등급고유번호
     *
     * @return array $offlineCouponListData 사용가능한 쿠폰의 리스트
     *
     * @author su
     */
    public function getOfflineCouponDownList($couponOfflineNumber, $memNo, $memGroupNo)
    {
        $arrBind = [];
        $arrWhere = [];

        // 입력한 유저코드의 오프라인 쿠폰
        $arrWhere[] = 'co.couponOfflineCodeUser=?';
        $this->db->bind_param_push($arrBind, $this->fieldTypes['couponOffline']['couponOfflineCodeUser'], $couponOfflineNumber);
        // 발급되지 않은 오프라인 쿠폰
        $arrWhere[] = 'co.couponOfflineCodeSaveType=?';
        $this->db->bind_param_push($arrBind, $this->fieldTypes['couponOffline']['couponOfflineCodeSaveType'], 'n');
        // 발급종류 (온라인,오프라인)
        $arrWhere[] = 'c.couponKind=?';
        $this->db->bind_param_push($arrBind, $this->fieldTypes['coupon']['couponKind'], 'offline');
        // 발급여부 쿠폰(발급 종료 제외)
        $arrWhere[] = 'c.couponType!=?';
        $this->db->bind_param_push($arrBind, $this->fieldTypes['coupon']['couponType'], 'f');

        // 기본 where 로 쿠폰 정보 가져오기
        $this->db->strField = "*";
        $this->db->strJoin = "LEFT JOIN " . DB_COUPON . " as c ON co.couponNo = c.couponNo";
        $this->db->strWhere = implode(' AND ', gd_isset($arrWhere));
        $this->db->strOrder = 'co.regDt desc';

        $query = $this->db->query_complete();
        $strSQL = 'SELECT ' . array_shift($query) . ' FROM ' . DB_COUPON_OFFLINE_CODE . ' as co ' . implode(' ', $query);
        $offlineCouponListData = $this->db->query_fetch($strSQL, $arrBind);
        unset($arrBind);
        unset($arrWhere);

        // 제외 $key
        $removeKey = [];
        foreach ($offlineCouponListData as $key => $val) {
            if($val['couponType'] == 'n') {
                $offlineCouponListData[$key]['chkMemberCoupon'] = 'COUPON_PAUSE';
                continue;
            }
            // 등록 가능 기간
            if (strtotime($val['couponDisplayStartDate']) > 0) {// 등록 가능 시작일
                if (strtotime($val['couponDisplayStartDate']) > time()) {
                    $offlineCouponListData[$key]['chkMemberCoupon'] = 'NOT_START_DISPLAY_DATE';
                    continue;
                } else {// 설정 기간 외로 제외처리
                    $offlineCouponListData[$key]['chkMemberCoupon'] = 'YES';
                }
            }
            if (strtotime($val['couponDisplayEndDate']) > 0) {// 등록 가능 종료일
                if (strtotime($val['couponDisplayEndDate']) < time()) {
                    $offlineCouponListData[$key]['chkMemberCoupon'] = 'NOT_END_DISPLAY_DATE';
                    continue;
                } else {// 설정 기간 외로 제외처리
                    $offlineCouponListData[$key]['chkMemberCoupon'] = 'YES';
                }
            }
            // 발급 회원등급 체크
            if ($val['couponApplyMemberGroup'] && $memGroupNo) {//발급회원등급
                $applyMemberGroupArr = explode(INT_DIVISION, $val['couponApplyMemberGroup']);
                if (array_search($memGroupNo, $applyMemberGroupArr) !== false) {//로그인한 회원등급 존재

                } else {//로그인한 회원등급 존재안함
                    $offlineCouponListData[$key]['chkMemberCoupon'] = 'NOT_MEMBER_GROUP';
                    continue;
                }
            }
            $saveMemberCouponCount = $this->getMemberCouponTotalCount($val['couponNo'], $memNo); // 회원의 해당 쿠폰 발급 총 갯수
            $stateMemberCoupon = $this->getMemberCouponState($val['couponNo'], $memNo);
            // 재발급 제한
            if ($val['couponSaveDuplicateType'] == 'n') {// 재발급 안됨
                if ($saveMemberCouponCount > 0) { // 발급된 내역이 있다면
                    $offlineCouponListData[$key]['chkMemberCoupon'] = 'NO_DUPLICATE_COUPON';
                    continue;
                } else {
                    $offlineCouponListData[$key]['chkMemberCoupon'] = 'YES';
                }
            } else if ($val['couponSaveDuplicateType'] == 'y') {// 재발급 가능
                if ($val['couponSaveDuplicateLimitType'] == 'y') {// 재발급 최대 수 여부
                    if ($saveMemberCouponCount > 0) { // 발급된 내역이 있다면
                        if ($val['couponSaveDuplicateLimit'] <= $saveMemberCouponCount) {
                            $offlineCouponListData[$key]['chkMemberCoupon'] = 'MAX_DUPLICATE_COUPON';
                            continue;
                        } else {// 재발급 최대 수에 모자람
                            if ($stateMemberCoupon) {// 해당 쿠폰이 모두 주문사용 사용됨
                                $offlineCouponListData[$key]['chkMemberCoupon'] = 'YES';
                            } else {// 해당 쿠폰이 장바구니 사용 또는 사용전
                                if ($val['couponUseType'] == 'gift') {
                                    $offlineCouponListData[$key]['chkMemberCoupon'] = 'YES';
                                } else {
                                    $offlineCouponListData[$key]['chkMemberCoupon'] = 'SOME_DUPLICATE_COUPON';
                                    continue;
                                }
                            }
                        }
                    } else {
                        $offlineCouponListData[$key]['chkMemberCoupon'] = 'YES';
                    }
                } else {// 무조건 재발급
                    if ($stateMemberCoupon) {// 해당 쿠폰이 모두 주문사용 사용됨
                        $offlineCouponListData[$key]['chkMemberCoupon'] = 'YES';
                    } else {// 해당 쿠폰이 장바구니 사용 또는 사용전
                        if ($val['couponUseType'] == 'gift') {
                            $offlineCouponListData[$key]['chkMemberCoupon'] = 'YES';
                        } else {
                            $offlineCouponListData[$key]['chkMemberCoupon'] = 'SOME_DUPLICATE_COUPON';
                            continue;
                        }
                    }
                }
            }
        }

        return $offlineCouponListData;
    }

    /**
     * 상품의 해당 쿠폰 리스트
     *
     * @param integer $goodsNo          상품고유번호
     * @param integer $memNo            회원고유번호 (존재하면 로그인한 회원의 재발급제한 및 회원등급 처리까지 필터링함) - 부하를 고려해야함
     * @param integer $memGroupNo       회원등급고유번호 (존재하면 로그인한 회원의 재발급제한 및 회원등급 처리까지 필터링함) - 부하를 고려해야함
     * @param null    $couponDeviceType ( pc , mobile , null)
     * @param null    $appendWhere      (추가조건식 ex: 다음EP에서 사용 partner/daumSomeController )
     *
     * @return array $couponListData 다운가능한 쿠폰의 리스트
     *
     * @author su
     */
    public function getGoodsCouponDownList($goodsNo, $memNo = null, $memGroupNo = null, $couponDeviceType = null, $appendWhere = null)
    {
        if (empty($goodsNo) === true) {
            throw new \Exception('상품 정보가 없습니다.');
        }
        $goods = \App::load('\\Component\\Goods\\Goods');
        $goodsData = $goods->getGoodsInfo($goodsNo, 'scmNo, brandCd');
        $cateArr = $goods->getGoodsLinkCategory($goodsNo);
        $cateCdArr = [];
        if (is_array($cateArr)) {
            $cateCdArr = array_column($cateArr, 'cateCd');
        }

        $arrBind = [];
        $arrWhere = [];
        // 온라인 쿠폰
        $arrWhere[] = 'c.couponKind=?';
        $this->db->bind_param_push($arrBind, $this->fieldTypes['coupon']['couponKind'], 'online');
        // 발급여부 쿠폰(발급중)
        $arrWhere[] = 'c.couponType=?';
        $this->db->bind_param_push($arrBind, $this->fieldTypes['coupon']['couponType'], 'y');
        // 상품 쿠폰
        $arrWhere[] = 'c.couponUseType=?';
        $this->db->bind_param_push($arrBind, $this->fieldTypes['coupon']['couponUseType'], 'product');
        // 회원다운로드 쿠폰
        $arrWhere[] = 'c.couponSaveType=?';
        $this->db->bind_param_push($arrBind, $this->fieldTypes['coupon']['couponSaveType'], 'down');
        // 할인 쿠폰, 적립 쿠폰
        $arrWhere[] = '(c.couponKindType in (?,?))';
        $this->db->bind_param_push($arrBind, $this->fieldTypes['coupon']['couponKindType'], 'sale');
        $this->db->bind_param_push($arrBind, $this->fieldTypes['coupon']['couponKindType'], 'add');
        // 사용범위?PC+모바일(‘a’),PC(‘p’),모바일(‘m’)
        $arrWhere[] = '(c.couponDeviceType in (?,?))';
        if ($appendWhere !== null) {
            foreach ($appendWhere as $condition) {
                $arrWhere[] = $condition;
            }
        }
        if ($couponDeviceType) {
            if ($couponDeviceType == 'pc') {
                $this->db->bind_param_push($arrBind, $this->fieldTypes['coupon']['couponDeviceType'], 'all');
                $this->db->bind_param_push($arrBind, $this->fieldTypes['coupon']['couponDeviceType'], 'pc');
            } else if ($couponDeviceType == 'mobile') {
                $this->db->bind_param_push($arrBind, $this->fieldTypes['coupon']['couponDeviceType'], 'all');
                $this->db->bind_param_push($arrBind, $this->fieldTypes['coupon']['couponDeviceType'], 'mobile');
            }
        } else {
            if (Request::isMobile()) { // 모바일 접속 여부
                $this->db->bind_param_push($arrBind, $this->fieldTypes['coupon']['couponDeviceType'], 'all');
                $this->db->bind_param_push($arrBind, $this->fieldTypes['coupon']['couponDeviceType'], 'mobile');
            } else {
                $this->db->bind_param_push($arrBind, $this->fieldTypes['coupon']['couponDeviceType'], 'all');
                $this->db->bind_param_push($arrBind, $this->fieldTypes['coupon']['couponDeviceType'], 'pc');
            }
        }

        // 기본 where 로 쿠폰 정보 가져오기
        $this->db->strField = "c.*";
        $this->db->strWhere = implode(' AND ', gd_isset($arrWhere));
        $this->db->strOrder = 'c.regDt asc';

        $query = $this->db->query_complete();
        $strSQL = 'SELECT ' . array_shift($query) . ' FROM ' . DB_COUPON . ' as c ' . implode(' ', $query);
        $couponListData = $this->db->query_fetch($strSQL, $arrBind);
        unset($arrBind);
        unset($arrWhere);

        // 제외 $key
        $removeKey = [];
        foreach ($couponListData as $key => $val) {
            if(!$this->checkCouponType($val['couponNo'])) {
                $removeKey[] = $key;
                continue;
            }
            // 노출 기간
            if ($val['couponDisplayType'] == 'n') {// 즉시 노출
                if ($val['couponUsePeriodType'] == 'period') {
                    if ($val['couponUsePeriodStartDate'] <= date('Y-m-d H:i:s', time()) && $val['couponUsePeriodEndDate'] >= date('Y-m-d H:i:s', time())) {
                        $couponListData[$key]['chkMemberCoupon'] = 'YES';
                    } else {
                        $removeKey[] = $key;
                        continue;
                    }
                }
                if ($val['couponUsePeriodType'] == 'day') {
                    if (empty($val['couponUseDateLimit']) || $val['couponUseDateLimit'] == '0000-00-00 00:00:00' || $val['couponUseDateLimit'] >= date('Y-m-d H:i:s', time())) {
                        $couponListData[$key]['chkMemberCoupon'] = 'YES';
                    } else {
                        $removeKey[] = $key;
                        continue;
                    }
                }
            } else if ($val['couponDisplayType'] == 'y') {
                if ($val['couponDisplayStartDate'] <= date('Y-m-d H:i:s', time()) && $val['couponDisplayEndDate'] >= date('Y-m-d H:i:s', time())) {// 설정 기간내 노출
                    $couponListData[$key]['chkMemberCoupon'] = 'YES';
                } else {// 설정 기간 외로 제외처리
                    $removeKey[] = $key;
                    continue;
                }
            }
            // 발급 수량
            if ($val['couponAmountType'] == 'n') {// 무제한
                $couponListData[$key]['chkMemberCoupon'] = 'YES';
            } else if ($val['couponAmountType'] == 'y') {// 제한
                // 발급 총 수량 제한 체크
                if ($val['couponAmount'] <= $val['couponSaveCount']) {// 발급 수량 외로 제외처리
                    $removeKey[] = $key;
                    continue;
                } else {
                    $couponListData[$key]['chkMemberCoupon'] = 'YES';
                }
            }
            // 발급 제외 공급사
            if ($val['couponExceptProviderType'] == 'y') {
                $exceptProviderGroupArr = explode(INT_DIVISION, $val['couponExceptProvider']);
                if (array_search($goodsData['scmNo'], $exceptProviderGroupArr) !== false) {//공급사 존재
                    $removeKey[] = $key;
                    continue;
                } else {//공급사 존재안함
                    $couponListData[$key]['chkMemberCoupon'] = 'YES';
                }
            }
            // 발급 제외 카테고리
            if ($val['couponExceptCategoryType'] == 'y') {
                $exceptCategoryGroupArr = explode(INT_DIVISION, $val['couponExceptCategory']);
                $matchCateData = 0;
                foreach ($cateCdArr as $cateKey => $cateVal) {
                    if (array_search($cateVal, $exceptCategoryGroupArr) !== false) {//카테고리 존재
                        $matchCateData++;
                    } else {//카테고리 존재안함
                        $couponListData[$key]['chkMemberCoupon'] = 'YES';
                    }
                }
                if ($matchCateData > 0) {// 상품의 적용된 카테고리와 쿠폰 허용 카테고리 존재
                    $removeKey[] = $key;
                    continue;
                } else {// 상품의 적용된 카테고리와 쿠폰 허용 카테고리 존재안함
                    $couponListData[$key]['chkMemberCoupon'] = 'YES';
                }
            }
            // 발급 제외 브랜드
            if ($val['couponExceptBrandType'] == 'y') {
                $exceptBrandGroupArr = explode(INT_DIVISION, $val['couponExceptBrand']);
                if (array_search($goodsData['brandCd'], $exceptBrandGroupArr) !== false) {//브랜드 존재
                    $removeKey[] = $key;
                    continue;
                } else {//브랜드 존재안함
                    $couponListData[$key]['chkMemberCoupon'] = 'YES';
                }
            }
            // 발급 제외 상품
            if ($val['couponExceptGoodsType'] == 'y') {
                $exceptGoodsGroupArr = explode(INT_DIVISION, $val['couponExceptGoods']);
                if (array_search($goodsNo, $exceptGoodsGroupArr) !== false) {//상품 존재
                    $removeKey[] = $key;
                    continue;
                } else {//상품 존재안함
                    $couponListData[$key]['chkMemberCoupon'] = 'YES';
                }
            }
            // 발급 허용 체크
            if ($val['couponApplyProductType'] == 'all') {//전체 발급

            } else if ($val['couponApplyProductType'] == 'provider') {//공급사 발급
                $applyProviderGroupArr = explode(INT_DIVISION, $val['couponApplyProvider']);
                if (array_search($goodsData['scmNo'], $applyProviderGroupArr) !== false) {//공급사 존재
                    $couponListData[$key]['chkMemberCoupon'] = 'YES';
                } else {//공급사 존재안함
                    $removeKey[] = $key;
                    continue;
                }
            } else if ($val['couponApplyProductType'] == 'category') {//카테고리 발급
                $applyCategoryGroupArr = explode(INT_DIVISION, $val['couponApplyCategory']);
                $matchCateData = 0;
                foreach ($cateCdArr as $cateKey => $cateVal) {
                    if (array_search($cateVal, $applyCategoryGroupArr) !== false) {//카테고리 존재
                        $matchCateData++;
                    } else {//카테고리 존재안함
                        $couponListData[$key]['chkMemberCoupon'] = 'YES';
                    }
                }
                if ($matchCateData > 0) {// 상품의 적용된 카테고리와 쿠폰 허용 카테고리 존재
                    $couponListData[$key]['chkMemberCoupon'] = 'YES';
                } else {// 상품의 적용된 카테고리와 쿠폰 허용 카테고리 존재안함
                    $removeKey[] = $key;
                    continue;
                }
            } else if ($val['couponApplyProductType'] == 'brand') {//브랜드 발급
                $applyBrandGroupArr = explode(INT_DIVISION, $val['couponApplyBrand']);
                if (array_search($goodsData['brandCd'], $applyBrandGroupArr) !== false) {//브랜드 존재
                    $couponListData[$key]['chkMemberCoupon'] = 'YES';
                } else {//브랜드 존재안함
                    $removeKey[] = $key;
                    continue;
                }
            } else if ($val['couponApplyProductType'] == 'goods') {//상품 발급
                $applyGoodsGroupArr = explode(INT_DIVISION, $val['couponApplyGoods']);
                if (array_search($goodsNo, $applyGoodsGroupArr) !== false) {//상품 존재
                    $couponListData[$key]['chkMemberCoupon'] = 'YES';
                } else {//상품 존재안함
                    $removeKey[] = $key;
                    continue;
                }
            }
            if ($memNo) {
                // 발급 회원등급 체크
                if ($val['couponApplyMemberGroup'] && $memGroupNo) {//발급회원등급이 있다면
                    $applyMemberGroupArr = explode(INT_DIVISION, $val['couponApplyMemberGroup']);
                    $chkMemberCoupon = array_search($memGroupNo, $applyMemberGroupArr);
                    if ($val['couponApplyMemberGroupDisplayType'] == 'y') {//해당 등급만 노출
                        if ($chkMemberCoupon !== false) {//로그인한 회원등급 존재
                            $couponListData[$key]['chkMemberCoupon'] = 'YES';
                        } else {//로그인한 회원등급 존재안함
                            $removeKey[] = $key;
                            continue;
                        }
                    } else {//전체 노출
                        if ($chkMemberCoupon !== false) {//로그인한 회원등급 존재
                            $couponListData[$key]['chkMemberCoupon'] = 'YES';
                        } else {//로그인한 회원등급 존재안함
                            $couponListData[$key]['chkMemberCoupon'] = 'NO_MEMBER_GROUP';
                            continue;
                        }
                    }
                }
                $saveMemberCouponCount = $this->getMemberCouponTotalCount($val['couponNo'], $memNo); // 회원의 해당 쿠폰 발급 총 갯수
                //$stateMemberCoupon = $this->getMemberCouponState($val['couponNo'], $memNo); // 사용상태에 상관없이 재발급이 가능한 쿠폰은 재발급 가능하도록 바뀌어서 안씀
                // 재발급 제한
                if ($val['couponSaveDuplicateType'] == 'n') {// 재발급 안됨
                    if ($saveMemberCouponCount > 0) { // 발급된 내역이 있다면
                        $couponListData[$key]['chkMemberCoupon'] = 'DUPLICATE_COUPON';
                        continue;
                    } else {
                        $couponListData[$key]['chkMemberCoupon'] = 'YES';
                    }
                } else if ($val['couponSaveDuplicateType'] == 'y') {// 재발급 가능
                    if ($val['couponSaveDuplicateLimitType'] == 'y') {// 재발급 최대 수 여부
                        if ($saveMemberCouponCount > 0) { // 발급된 내역이 있다면
                            if ($val['couponSaveDuplicateLimit'] <= $saveMemberCouponCount) {
                                $couponListData[$key]['chkMemberCoupon'] = 'DUPLICATE_COUPON';
                                continue;
                            } else {// 재발급 최대 수에 모자람
                                $couponListData[$key]['chkMemberCoupon'] = 'YES';
                            }
                        } else {
                            $couponListData[$key]['chkMemberCoupon'] = 'YES';
                        }
                    } else {// 무조건 재발급
                        $couponListData[$key]['chkMemberCoupon'] = 'YES';
                    }
                }
            }
        }
        // 조건 필터링
        foreach ($removeKey as $val) {
            unset($couponListData[$val]);
        }
        unset($removeKey);

        return $couponListData;
    }

    /**
     * 상품의 다운받을 수 있는 쿠폰의 개수
     *
     * @param array   $couponData       쿠폰 데이터
     * @param mixed   $goodsNo, $memNo, $memGroupNo, $couponDeviceType, $appendWhere        getGoodsCouponDownList()에서 이용하는 파라미터
     *
     * @return array $goodsCouponCnt 상품의 다운받을 수 있는 쿠폰의 개수
     *
     * @author haky
     */
    public function getGoodsCouponDownListCount($couponData = null, $goodsNo = null, $memNo = null, $memGroupNo = null, $couponDeviceType = null, $appendWhere = null)
    {
        if ($couponData != null && is_array($couponData)) {
            $couponListData = $couponData;
        } else {
            $couponListData = $this->getGoodsCouponDownList($goodsNo, $memNo, $memGroupNo, $couponDeviceType, $appendWhere);
        }

        // 다운받을 수 있는 쿠폰의 개수
        $goodsCouponCnt = 0;
        foreach ($couponListData as $data) {
            if ($data['chkMemberCoupon'] == 'YES') $goodsCouponCnt++;
        }

        return $goodsCouponCnt;
    }

    /**
     * 회원 쿠폰 리스트에서 해당 상품에 적용할 수 있는 쿠폰 리스트
     *
     * @param integer $goodsNo              상품고유번호
     * @param integer $memNo                회원고유번호
     * @param integer $memGroupNo           회원등급고유번호
     * @param array   $exceptMemberCouponNo 제외될 회원쿠폰고유번호
     * @param array   $nowMemberCouponNo    선택된 회원쿠폰고유번호
     * @param array   $pageType             요청온 페이지
     * @param boolean $isOrderWrite 수기주문 페이지 여부
     * @param string  $isWriteCouponModeData 수기주문에서 회원장바구니추가를 통해 주문상품을 등록 후 쿠폰정보 접근시 필요데이터
     * @param array $couponKindTypes        쿠폰 할인 종류 -> 할인/적립
     * @param boolean $exceptCart           장바구니에 담겨있는 쿠폰 제외 여부
     *
     * @return array $memberCouponListData 사용가능한 쿠폰의 리스트
     *
     * @author su
     */
    public function getGoodsMemberCouponList($goodsNo, $memNo, $memGroupNo, $exceptMemberCouponNo = null, $nowMemberCouponNo = null, $pageType = 'goods', $isOrderWrite=false, $isWriteCouponModeData=[], $couponKindTypes = ['sale','add'], $exceptCart = false)
    {
        $goods = \App::load('\\Component\\Goods\\Goods');
        // 상품의 공급사, 브랜드코드, 상품결제방식제한설정값
        $goodsData = $goods->getGoodsInfo($goodsNo, 'goodsNo, scmNo, brandCd, payLimitFl, payLimit');

        // 상품의 연결된 카테고리 모두
        $cateArr = $goods->getGoodsLinkCategory($goodsNo);
        $cateCdArr = [];
        if (is_array($cateArr)) {
            $cateCdArr = array_column($cateArr, 'cateCd');
        }
        $arrBind = [];
        $arrWhere = [];

        if ($exceptMemberCouponNo) {
            foreach ($exceptMemberCouponNo as $val) {
                if ($val) {
                    // 제외할 회원쿠폰
                    $arrWhere[] = 'mc.memberCouponNo!=?';
                    $this->db->bind_param_push($arrBind, $this->fieldTypes['memberCoupon']['memberCouponNo'], $val);
                }
            }
        }
        // 로그인한 회원의 쿠폰
        $arrWhere[] = 'mc.memNo=?';
        $this->db->bind_param_push($arrBind, $this->fieldTypes['memberCoupon']['memNo'], $memNo);
        // 상품 쿠폰
        $arrWhere[] = 'c.couponUseType=?';
        $this->db->bind_param_push($arrBind, $this->fieldTypes['coupon']['couponUseType'], 'product');

        // 할인 쿠폰, 적립 쿠폰
        $tmpWhereBind = [];
        if (empty($couponKindTypes) === true || is_array($couponKindTypes) === false) { $couponKindTypes = ['sale', 'add']; }
        foreach ($couponKindTypes as $couponKindTypeVal) {
            $tmpWhereBind[] = '?';
            $this->db->bind_param_push($arrBind, $this->fieldTypes['coupon']['couponKindType'], $couponKindTypeVal);
        }
        $arrWhere[] = '(c.couponKindType in (' . implode(',', $tmpWhereBind) . '))';

        //장바구니 담겨져있는 쿠폰 제외
        if ($exceptCart === true) {
            $tmpExceptCartSQL   = 'SELECT GROUP_CONCAT(memberCouponNo SEPARATOR \',\') AS exceptMemCouponNo FROM ' . DB_CART . ' WHERE memNo = ? AND memberCouponNo > 0;';
            $tmpExceptCart      = $this->db->query_fetch($tmpExceptCartSQL, ['i', $memNo]);
            if (empty($tmpExceptCart[0]['exceptMemCouponNo']) === false) {
                $arrWhere[] = 'mc.memberCouponNo NOT IN (' . $tmpExceptCart[0]['exceptMemCouponNo'] . ')';
            }
        }

        if($isOrderWrite !== true){
            // 사용범위?PC+모바일(‘a’),PC(‘p’),모바일(‘m’)
            $arrWhere[] = '(c.couponDeviceType in (?,?))';
            if (Request::isMobile()) { // 모바일 접속 여부
                $this->db->bind_param_push($arrBind, $this->fieldTypes['coupon']['couponDeviceType'], 'all');
                $this->db->bind_param_push($arrBind, $this->fieldTypes['coupon']['couponDeviceType'], 'mobile');
            } else {
                $this->db->bind_param_push($arrBind, $this->fieldTypes['coupon']['couponDeviceType'], 'all');
                $this->db->bind_param_push($arrBind, $this->fieldTypes['coupon']['couponDeviceType'], 'pc');
            }
        }

        // 회원쿠폰 사용 여부
        if($isOrderWrite === true){
            //수기주문 페이지에서는 가사용여부를 체크
            if(trim($isWriteCouponModeData['mode']) !== ''){
                if($isWriteCouponModeData['mode'] === 'memberCartModify'){
                    //수기주문에서 회원장바구니추가를 통해 주문상품을 등록 후 쿠폰정보 접근시
                    // 장바구니 사용 상태의 쿠폰도 노출 되도록 수정
                    $arrWhere[] = '(mc.memberCouponState=? OR mc.memberCouponState=?)';
                    $this->db->bind_param_push($arrBind, $this->fieldTypes['memberCoupon']['memberCouponState'], 'y');
                    $this->db->bind_param_push($arrBind, $this->fieldTypes['memberCoupon']['memberCouponState'], 'cart');
                }
                else if($isWriteCouponModeData['mode'] === 'memberCartNew'){
                    $arrWhere[] = '(mc.memberCouponState = ? or (mc.memberCouponState = ? AND mc.memberCouponNo = ?))';
                    $this->db->bind_param_push($arrBind, $this->fieldTypes['memberCoupon']['memberCouponState'], 'y');
                    $this->db->bind_param_push($arrBind, $this->fieldTypes['memberCoupon']['memberCouponState'], 'cart');
                    $this->db->bind_param_push($arrBind, $this->fieldTypes['memberCoupon']['memberCouponNo'], $isWriteCouponModeData['memberCouponNo']);
                }
                else {}
            }
            else {
                // 장바구니 사용 상태의 쿠폰도 노출 되도록 수정
                $arrWhere[] = '(mc.memberCouponState=? OR mc.memberCouponState=?)';
                $this->db->bind_param_push($arrBind, $this->fieldTypes['memberCoupon']['memberCouponState'], 'y');
                $this->db->bind_param_push($arrBind, $this->fieldTypes['memberCoupon']['memberCouponState'], 'cart');
            }
            // 장바구니 사용 상태의 쿠폰도 노출 되도록 수정
            $arrWhere[] = '(mc.orderWriteCouponState=? OR mc.orderWriteCouponState=?)';
            $this->db->bind_param_push($arrBind, $this->fieldTypes['memberCoupon']['orderWriteCouponState'], 'y');
            $this->db->bind_param_push($arrBind, $this->fieldTypes['memberCoupon']['orderWriteCouponState'], 'cart');
        }
        else {
            $couponConfig = gd_policy('coupon.config');
            if($couponConfig['productCouponChangeLimitType'] == 'n' && $pageType == 'order') { // 상품쿠폰 주문에서 사용할 경우 cart 허용
                $arrWhere[] = '(mc.memberCouponState=? OR mc.memberCouponState=?)';
                $this->db->bind_param_push($arrBind, $this->fieldTypes['memberCoupon']['memberCouponState'], 'y');
                $this->db->bind_param_push($arrBind, $this->fieldTypes['memberCoupon']['memberCouponState'], 'cart');
            } else {
                if ($pageType == 'goods' || $pageType == 'cart') {
                    $arrWhere[] = '(mc.memberCouponState=? OR mc.memberCouponState=?)';
                    $this->db->bind_param_push($arrBind, $this->fieldTypes['memberCoupon']['memberCouponState'], 'y');
                    $this->db->bind_param_push($arrBind, $this->fieldTypes['memberCoupon']['memberCouponState'], 'cart');
                } else {
                    $arrWhere[] = 'mc.memberCouponState=?';
                    $this->db->bind_param_push($arrBind, $this->fieldTypes['memberCoupon']['memberCouponState'], 'y');
                }
            }
        }

        // 회원쿠폰 만료기간
        $arrWhere[] = 'mc.memberCouponStartDate<=? AND mc.memberCouponEndDate>?';
        $this->db->bind_param_push($arrBind, $this->fieldTypes['memberCoupon']['memberCouponStartDate'], date('Y-m-d H:i:s'));
        $this->db->bind_param_push($arrBind, $this->fieldTypes['memberCoupon']['memberCouponEndDate'], date('Y-m-d H:i:s'));
        // 쿠폰 결제방식설정에따른 제외조건 추가
        // 회원 정보
        $member = \App::load('\\Component\\Member\\Member');
        $memInfo = $member->getMemberInfo();
        $memInfo['settleGb'] = GroupUtil::matchSettleGbDataToString($memInfo['settleGb']);
        if (($goodsData['payLimitFl'] == 'y' && !preg_match('/gb/', $goodsData['payLimit'])) || !in_array('gb', $memInfo['settleGb'])) {
            $arrWhere[] = 'c.couponUseAblePaymentType=?';
            $this->db->bind_param_push($arrBind, $this->fieldTypes['coupon']['couponUseAblePaymentType'], 'all');
        }

        // 기본 where 로 쿠폰 정보 가져오기
        $this->db->strField = "*";
        $this->db->strJoin = "LEFT JOIN " . DB_COUPON . " as c ON mc.couponNo = c.couponNo";
        $this->db->strWhere = implode(' AND ', gd_isset($arrWhere));
        $this->db->strOrder = 'mc.couponNo, mc.regDt';
        //$this->db->strGroup = 'mc.couponNo';

        $query = $this->db->query_complete();
        $strSQL = 'SELECT ' . array_shift($query) . ' FROM ' . DB_MEMBER_COUPON . ' as mc ' . implode(' ', $query);
        $memberCouponListData = $this->db->query_fetch($strSQL, $arrBind);
        unset($arrBind);
        unset($arrWhere);

        // 제외 $key
        $removeKey = [];
        $checkKey = '';
        foreach ($memberCouponListData as $key => $val) {
            if ($pageType == 'goods' || ($pageType != 'goods' && !is_array($nowMemberCouponNo))) { // 상품상세에서넘어오거나 장바구니에서 선택된쿠폰이없이 넘어올때는 해당분기처리
                if ($checkKey == '') { // 첫배열일때 처리
                    if (is_array($nowMemberCouponNo)) {
                        if (in_array($val['memberCouponNo'], $nowMemberCouponNo)) { // 첫배열인데 선택된쿠폰이있으면 checkKey값만 설정하고 continue
                            $checkKey = $val['couponNo'];
                            continue;
                        } else {
                            $checkKey = $val['couponNo'];
                        }
                    } else {
                        $checkKey = $val['couponNo'];
                    }
                } else {
                    if ($checkKey == $val['couponNo']) { // 중복쿠폰이면
                        if($couponConfig['productCouponChangeLimitType'] != 'n' && $pageType != 'order') { // 상품쿠폰 주문에서 사용할 경우 cart 허용
                            if (is_array($nowMemberCouponNo)) {
                                if (in_array($val['memberCouponNo'], $nowMemberCouponNo)) {
                                    $removeKey[] = $key - 1;
                                    continue;
                                } else {
                                    $removeKey[] = $key;
                                    continue;
                                }
                            } else {
                                $removeKey[] = $key;
                                continue;
                            }
                        }
                    } else {
                        $checkKey = $val['couponNo'];
                    }
                }
            } else { // 장바구니에서 선택된쿠폰이있을때 넘어오면 $nowMemberCouponNo 에있는 쿠폰과 couponNO값이 동일한 쿠폰은 모두 예외처리
                if ($checkKey == '') { // 첫배열일때 처리
                    $checkKey = $val['couponNo'];
                } else {
                    if ($checkKey == $val['couponNo']) { // 중복쿠폰이면
                        $removeKey[] = $key;
                        continue;
                    } else {
                        $checkKey = $val['couponNo'];
                    }
                }
                if (in_array($val['couponNo'], $nowMemberCouponNo)) {
                    $removeKey[] = $key;
                    continue;
                }
            }

            // 발급 제외 공급사
            if ($val['couponExceptProviderType'] == 'y') {
                $exceptProviderGroupArr = explode(INT_DIVISION, $val['couponExceptProvider']);
                if (array_search($goodsData['scmNo'], $exceptProviderGroupArr) !== false) {//공급사 존재
                    $removeKey[] = $key;
                    continue;
                } else {//공급사 존재안함

                }
            }
            // 발급 제외 카테고리
            if ($val['couponExceptCategoryType'] == 'y') {
                $exceptCategoryGroupArr = explode(INT_DIVISION, $val['couponExceptCategory']);
                $matchCateData = 0;
                foreach ($cateCdArr as $cateKey => $cateVal) {
                    if (array_search($cateVal, $exceptCategoryGroupArr) !== false) {//카테고리 존재
                        $matchCateData++;
                    } else {//카테고리 존재안함

                    }
                }
                if ($matchCateData > 0) {// 상품의 적용된 카테고리와 쿠폰 허용 카테고리 존재
                    $removeKey[] = $key;
                    continue;
                } else {// 상품의 적용된 카테고리와 쿠폰 허용 카테고리 존재안함
                }
            }
            // 발급 제외 브랜드
            if ($val['couponExceptBrandType'] == 'y') {
                $exceptBrandGroupArr = explode(INT_DIVISION, $val['couponExceptBrand']);
                if (array_search($goodsData['brandCd'], $exceptBrandGroupArr) !== false) {//브랜드 존재
                    $removeKey[] = $key;
                    continue;
                } else {//브랜드 존재안함

                }
            }
            // 발급 제외 상품
            if ($val['couponExceptGoodsType'] == 'y') {
                $exceptGoodsGroupArr = explode(INT_DIVISION, $val['couponExceptGoods']);
                if (array_search($goodsData['goodsNo'], $exceptGoodsGroupArr) !== false) {//상품 존재
                    $removeKey[] = $key;
                    continue;
                } else {//상품 존재안함

                }
            }
            // 발급 허용 체크
            if ($val['couponApplyProductType'] == 'all') {//전체 발급

            } else if ($val['couponApplyProductType'] == 'provider') {//공급사 발급
                $applyProviderGroupArr = explode(INT_DIVISION, $val['couponApplyProvider']);
                if (array_search($goodsData['scmNo'], $applyProviderGroupArr) !== false) {//공급사 존재

                } else {//공급사 존재안함
                    $removeKey[] = $key;
                    continue;
                }
            } else if ($val['couponApplyProductType'] == 'category') {//카테고리 발급
                $applyCategoryGroupArr = explode(INT_DIVISION, $val['couponApplyCategory']);
                $matchCateData = 0;
                foreach ($cateCdArr as $cateKey => $cateVal) {
                    if (array_search($cateVal, $applyCategoryGroupArr) !== false) {//카테고리 존재
                        $matchCateData++;
                    } else {//카테고리 존재안함

                    }
                }
                if ($matchCateData > 0) {// 상품의 적용된 카테고리와 쿠폰 허용 카테고리 존재

                } else {// 상품의 적용된 카테고리와 쿠폰 허용 카테고리 존재안함
                    $removeKey[] = $key;
                    continue;
                }
            } else if ($val['couponApplyProductType'] == 'brand') {//브랜드 발급
                $applyBrandGroupArr = explode(INT_DIVISION, $val['couponApplyBrand']);
                if (array_search($goodsData['brandCd'], $applyBrandGroupArr) !== false) {//브랜드 존재

                } else {//브랜드 존재안함
                    $removeKey[] = $key;
                    continue;
                }
            } else if ($val['couponApplyProductType'] == 'goods') {//상품 발급
                $applyGoodsGroupArr = explode(INT_DIVISION, $val['couponApplyGoods']);
                if (array_search($goodsData['goodsNo'], $applyGoodsGroupArr) !== false) {//상품 존재

                } else {//상품 존재안함
                    $removeKey[] = $key;
                    continue;
                }
            }
            // 발급 회원등급 체크 & 쿠폰 타입도 체크해야함
            if ($val['couponSaveType'] == 'down') {
                if ($val['couponApplyMemberGroup'] && $memGroupNo) {//발급회원등급
                    $applyMemberGroupArr = explode(INT_DIVISION, $val['couponApplyMemberGroup']);
                    if (array_search($memGroupNo, $applyMemberGroupArr) !== false) {//로그인한 회원등급 존재

                    } else {//로그인한 회원등급 존재안함
                        $removeKey[] = $key;
                        continue;
                    }
                }
            } else {
                $val['couponUseMemberGroup'] = $this->getCouponUseMemberGroup($val['couponNo']);
                if ($val['couponUseMemberGroup'] && $memGroupNo) {//발급회원등급
                    if (array_search($memGroupNo, $val['couponUseMemberGroup']) !== false) {//로그인한 회원등급 존재

                    } else {//로그인한 회원등급 존재안함
                        $removeKey[] = $key;
                        continue;
                    }
                }
            }

            // 마일리지 지급설정 - 사용안함 일 경우 마일리지 적립 쿠폰 제외
            $mileageGive = gd_policy('member.mileageGive');
            if($mileageGive['giveFl'] == 'n') {
                if($val['couponKindType'] == 'add') {
                    $removeKey[] = $key;
                    continue;
                }
            }

            // 쿠폰 리스트에서 현재 사용 중인 쿠폰 제외
            if($couponConfig['productCouponChangeLimitType'] == 'n' && $pageType == 'order') { // 상품쿠폰 주문에서 사용할 경우 cart 허용
                // where 문
                $arrBind = $arrWhere = [];
                $arrWhere[] = 'memberCouponNo = ?';
                if($val['couponKindType'] == 'sale') {
                    $arrWhere[] = 'minusCouponFl = \'n\'';
                    $arrWhere[] = 'minusRestoreCouponFl = \'n\'' ;
                } else if($val['couponKindType'] == 'add') {
                    $arrWhere[] = 'plusCouponFl = \'n\'';
                    $arrWhere[] = 'plusRestoreCouponFl = \'n\'';
                }

                $this->db->bind_param_push($arrBind, 's', $val['memberCouponNo']);
                $this->db->strField = '*';
                $this->db->strWhere = implode(' AND ', $arrWhere);

                $query = $this->db->query_complete();
                $strSQL = 'SELECT ' . array_shift($query) . ' FROM ' . DB_ORDER_COUPON . ' ' . implode(' ', $query);
                $getData = $this->db->query_fetch($strSQL, $arrBind);
                unset($arrBind);
                if($getData) {
                    foreach($getData as $orderCouponKey => $orderCouponVal) {
                        if($orderCouponVal['orderNo']) {
                            $order = \App::load('\\Component\\Order\\Order');
                            $orderGoodsData = $order->getOrderGoods($orderCouponVal['orderNo']);
                            $removePass = false;
                            $orderRemoveStatus = $order->statusDeleteCd;
                            foreach($orderGoodsData as $orderGoodsKey => $orderGoodsData) {
                                if($orderGoodsData['orderCd'] == $orderCouponVal['orderCd']) {
                                    $orderGoodsStatus = substr($orderGoodsData['orderStatus'], 0, 1); // 주문상품 상태
                                    if(in_array($orderGoodsStatus, $orderRemoveStatus) == false) {
                                        $removePass = true;
                                    }
                                } else {
                                    continue;
                                }
                            }
                            if($removePass == true) {
                                $removeKey[] = $key;
                                continue;
                            }
                        }
                    }
                }
            }
        }
        // 조건 필터링
        foreach ($removeKey as $val) {
            unset($memberCouponListData[$val]);
        }
        unset($removeKey);

        return $memberCouponListData;
    }

    /**
     * 회원 쿠폰 리스트에서 해당 주문에 적용할 수 있는 쿠폰 리스트
     *
     * @param integer $memNo 회원고유번호
     *
     * @return array $memberCouponListData 사용가능한 쿠폰의 리스트
     * @return array $aPayLimit 사용가능한 결제수단 (디폴트값 기본all로해서 all이아닌경우에만 체크)
     * @param boolean $isOrderWrite 수기주문 페이지 여부
     *
     * @author su
     */
    public function getOrderMemberCouponList($memNo, $aPayLimit = array('all'), $isOrderWrite=false)
    {
        if (is_string($aPayLimit) === true) {
            $aPayLimit = GroupUtil::matchSettleGbDataToString($aPayLimit);
        }
        // 주문할인,적립 || 배송비할인
        $orderCouponType = [
            'order',
            'delivery',
        ];

        // 쿠폰 결제방식설정에따른 제외조건 추가 기본은 플래그값을n으로 하고 $aPayLimit값이 all이 아니면 y로변경하고 루프돌면서 결제수단에 gb가있을때만 n처리
        if (is_array($aPayLimit) && empty($aPayLimit)) {
            $aPayLimit = array('all');
        }
        $payLimitFl = 'n';
        if ($aPayLimit[0] != 'all') {
            $payLimitFl = 'y';
            foreach ($aPayLimit as $val) {
                if ($val == 'gb') {
                    $payLimitFl = 'n';
                }
            }
        }

        foreach ($orderCouponType as $val) {
            $arrBind = [];
            $arrWhere = [];
            // 로그인한 회원의 쿠폰
            $arrWhere[] = 'mc.memNo=?';
            $this->db->bind_param_push($arrBind, $this->fieldTypes['memberCoupon']['memNo'], $memNo);
            // 주문할인,적립(order) || 배송비할인(delivery)
            $arrWhere[] = 'c.couponUseType=?';
            $this->db->bind_param_push($arrBind, $this->fieldTypes['coupon']['couponUseType'], $val);

            if($isOrderWrite !== true){
                // 사용범위?PC+모바일(‘a’),PC(‘p’),모바일(‘m’)
                $arrWhere[] = '(c.couponDeviceType in (?,?))';
                if (Request::isMobile()) { // 모바일 접속 여부
                    $this->db->bind_param_push($arrBind, $this->fieldTypes['coupon']['couponDeviceType'], 'all');
                    $this->db->bind_param_push($arrBind, $this->fieldTypes['coupon']['couponDeviceType'], 'mobile');
                } else {
                    $this->db->bind_param_push($arrBind, $this->fieldTypes['coupon']['couponDeviceType'], 'all');
                    $this->db->bind_param_push($arrBind, $this->fieldTypes['coupon']['couponDeviceType'], 'pc');
                }
            }
            // 회원쿠폰 사용 여부
            $arrWhere[] = 'mc.memberCouponState=?';
            $this->db->bind_param_push($arrBind, $this->fieldTypes['memberCoupon']['memberCouponState'], 'y');
            // 회원쿠폰 만료기간
            $arrWhere[] = 'mc.memberCouponStartDate<=? AND mc.memberCouponEndDate>?';
            $this->db->bind_param_push($arrBind, $this->fieldTypes['memberCoupon']['memberCouponStartDate'], date('Y-m-d H:i:s'));
            $this->db->bind_param_push($arrBind, $this->fieldTypes['memberCoupon']['memberCouponEndDate'], date('Y-m-d H:i:s'));
            // 주문서에서 정해진 결제방식에 무통장이없으면 무통장쿠폰 노출안되도록 처리
            if ($payLimitFl == 'y') {
                $arrWhere[] = 'c.couponUseAblePaymentType=?';
                $this->db->bind_param_push($arrBind, $this->fieldTypes['coupon']['couponUseAblePaymentType'], 'all');
            }

            // 기본 where 로 쿠폰 정보 가져오기
            $this->db->strField = "*";
            $this->db->strJoin = "LEFT JOIN " . DB_COUPON . " as c ON mc.couponNo = c.couponNo";
            $this->db->strWhere = implode(' AND ', gd_isset($arrWhere));
            $this->db->strOrder = 'mc.regDt desc';

            $query = $this->db->query_complete();
            $strSQL = 'SELECT ' . array_shift($query) . ' FROM ' . DB_MEMBER_COUPON . ' as mc ' . implode(' ', $query);
            $memberCouponOrderListData = $this->db->query_fetch($strSQL, $arrBind);

            // 회원 등급 체크를 위한정보
            $memberGroupSno = Session::get('member.groupSno');
            foreach ($memberCouponOrderListData as $key2 => $val2) {
                if ($val2['couponSaveType'] == 'auto') {
                    $couponUseMemberGroup = $this->getCouponUseMemberGroup($val2['couponNo']);
                    if ($couponUseMemberGroup && $memberGroupSno) {//발급회원등급
                        if (array_search($memberGroupSno, $couponUseMemberGroup) !== false) {//로그인한 회원등급 존재
                        } else {//로그인한 회원등급 존재안함
                            unset($memberCouponOrderListData[$key2]);
                        }
                    }
                }
            }

            $memberCouponListData[$val] = $memberCouponOrderListData;
            unset($arrBind);
            unset($arrWhere);
            unset($memberCouponOrderListData);
        }

        return $memberCouponListData;
    }

    /**
     * 상품리스트 전시용 쿠폰 할인 금액 (할인쿠폰만 금액계산 - 마일리지적립쿠폰 제외 함)
     * - 상품의 적용된 최대 할인 금액으로 쿠폰가 표시
     * - 상품의 판매가에서 계산됨 ( 옵션가 , 텍스트옵션가 , 추가상품 금액 제외 )
     *
     * @param array   $couponListData 쿠폰리스트
     * @param integer $goodsPrice     상품의 판매가
     *
     * @return integer $couponMaxSalePrice 최대할인적용된 정액쿠폰가
     *
     * @author su
     */
    public function getGoodsCouponDisplaySalePrice($couponListData, $goodsPrice)
    {
        if (!$couponListData) {
            return null;
        }

        if ($this->couponConfig['couponDisplayType'] == 'all') {// 회원+비회원 모두 표시

        } else {// 회원만 표시
            if (!MemberUtil::isLogin()) {// 로그인이 안되어 있을 때
                return null;
            } else {// 로그인이 되어 있을 때

            }
        }

        $couponSalePrice = [];
        foreach ($couponListData as $key => $val) {
            if ($val['couponKindType'] == 'sale') {//할인 쿠폰에서만
                if ($val['couponBenefitType'] == 'percent') {//정율 쿠폰
                    $couponSalePrice[$key] = $goodsPrice * $val['couponBenefit'] * 1 / 100;
                    if ($val['couponMaxBenefitType'] == 'y') {
                        if ($val['couponMaxBenefit'] < $couponSalePrice[$key]) {
                            $couponSalePrice[$key] = $val['couponMaxBenefit'];
                        }
                    }
                } else if ($val['couponBenefitType'] == 'fix') {//정액 쿠폰
                    $couponSalePrice[$key] = $val['couponBenefit'];
                }
            }
        }

        $benefitType = 'fix';
        $couponMaxSalePrice = 0;
        if (count($couponSalePrice) > 0) {
            // 쿠폰 금액 중 가장 큰 것
            $couponMaxSalePrice = max($couponSalePrice);
            $tmpSalePriceKey    = array_keys($couponSalePrice, $couponMaxSalePrice); //쿠폰 할인 타입을 가져오기 위해 key값 구함
            $couponMaxSalePriceKey  = gd_isset($tmpSalePriceKey[0], 0);
            $benefitType            = gd_isset($couponListData[$couponMaxSalePriceKey]['couponBenefitType'], 'fix');
        }

        if ($benefitType === 'percent') {
            //정률 쿠폰인 경우, 쿠폰 절사 기준으로 계산하여 할인가 노출
            return gd_number_figure($couponMaxSalePrice, $this->couponTrunc['unitPrecision'], $this->couponTrunc['unitRound']);
        } else {
            return gd_number_figure($couponMaxSalePrice); //정액 쿠폰인 경우, 할인가 그대로 노출
        }
    }

    /**
     * 회원쿠폰에 적용된 쿠폰 할인/적립 쿠폰액
     * - 상품에 적용된 쿠폰 금액을 정액으로 계산
     *
     * @param array  $goodsPrice     상품금액(판매가,옵션가,텍스트옵션가,추가상품)
     * @param string $memberCouponNo 회원쿠폰고유번호 (INT_DIVISION로 구분된 memberCouponNo)
     * @param array $aTotalPrice 주문서 상품쿠폰 변경가능설정일때 총금액
     * @param string $isAllFl 장바구니데이터 읽을때 대상이 1개인지 2개이상인지 구분값(T면 다수로봄 - 1개일수도있지만 체크안하는 곳에선 상관없음)
     *
     * @return array $memberCouponPrice 회원쿠폰에 해당하는 정액 할인/적립 쿠폰액 (할인/적립을 배열로 분리 or 하나로 리턴)
     *
     * @author su
     */
    public function getMemberCouponPrice($goodsPrice, $memberCouponNo, $aTotalPrice = array(), $isAllFl = 'T')
    {
        //Referer체크로 cartps에서만 체크할때는 상품쿠폰이고 최고금액조건이 주문금액일때는 기존대로 상품금액기준으로 체크하도록 isAllFl값을 강제로 수정
        $request = \App::getInstance('request');
        $referer = $request->getParserReferer();
        if ($referer->path == '/order/cart.php') $isAllFl = 'T';

        // 할인/적립 기준금액
        $totalGoodsPrice = $goodsPrice['goodsPriceSum'];
        if ($this->couponConfig['couponOptPriceType'] == 'y') {
            $totalGoodsPrice += $goodsPrice['optionPriceSum'];
        }
        if ($this->couponConfig['couponTextPriceType'] == 'y') {
            $totalGoodsPrice += $goodsPrice['optionTextPriceSum'];
        }
        if ($this->couponConfig['couponAddPriceType'] == 'y') {
            $totalGoodsPrice += $goodsPrice['addGoodsPriceSum'];
        }
        $totalGoodsPriceAll = 0;
        if (!empty($aTotalPrice)) {
            $totalGoodsPriceAll = $aTotalPrice['goodsPriceSum'];
            $totalGoodsPriceAll += $aTotalPrice['optionPriceSum'];
            $totalGoodsPriceAll += $aTotalPrice['optionTextPriceSum'];
            $totalGoodsPriceAll += $aTotalPrice['addGoodsPriceSum'];
        }
        if ($totalGoodsPriceAll == 0) $totalGoodsPriceAll = $totalGoodsPrice;

        $memberCouponArrNo = explode(INT_DIVISION, $memberCouponNo);

        foreach ($memberCouponArrNo as $memberCouponKey => $memberCouponVal) {
            if ($memberCouponVal != null) {
                $memberCouponData = $this->getMemberCouponInfo($memberCouponVal, 'c.couponUseType, c.couponKindType, c.couponBenefit, c.couponBenefitType, c.couponBenefitFixApply, c.couponMaxBenefitType, c.couponMaxBenefit, c.couponMinOrderPrice, c.couponProductMinOrderType');

                if ($memberCouponData['couponProductMinOrderType'] == 'order') {
                    if ($isAllFl == 'T') {
                        if ($memberCouponData['couponMinOrderPrice'] > $totalGoodsPriceAll) {
                            $memberCouponPrice['memberCouponAlertMsg'][$memberCouponVal] = 'LIMIT_MIN_PRICE';
                        } else {
                            $memberCouponPrice['memberCouponAlertMsg'][$memberCouponVal] = '';
                        }
                    } else {
                        // 장바구니가 단건이면 최소금액 체크안하게
                        $memberCouponPrice['memberCouponAlertMsg'][$memberCouponVal] = '';
                    }
                } else {
                    if ($memberCouponData['couponMinOrderPrice'] > $totalGoodsPrice) {
                        $memberCouponPrice['memberCouponAlertMsg'][$memberCouponVal] = 'LIMIT_MIN_PRICE';
                    } else {
                        $memberCouponPrice['memberCouponAlertMsg'][$memberCouponVal] = '';
                    }
                }

                // 정액 조건에 따른 쿠폰 실 할인 금액
                if ($memberCouponData['couponKindType'] == 'sale' && $memberCouponData['couponBenefitType'] == 'fix' && $memberCouponData['couponKindType'] != 'delivery') {
                    if ($totalGoodsPrice < $memberCouponData['couponBenefit']) {
                        $memberCouponData['couponBenefit'] = $totalGoodsPrice;
                    }
                }

                if ($memberCouponData['couponKindType'] == 'sale') {// 할인 쿠폰
                    if ($memberCouponData['couponBenefitType'] == 'percent') {//정율 쿠폰
                        $memberCouponPrice['memberCouponSalePrice'][$memberCouponVal] = $totalGoodsPrice * $memberCouponData['couponBenefit'] * 1 / 100;
                        $memberCouponPrice['memberCouponSalePrice'][$memberCouponVal] = gd_number_figure($memberCouponPrice['memberCouponSalePrice'][$memberCouponVal], $this->couponTrunc['unitPrecision'], $this->couponTrunc['unitRound']);
                        if ($memberCouponData['couponMaxBenefitType'] == 'y') {
                            if ($memberCouponData['couponMaxBenefit'] < $memberCouponPrice['memberCouponSalePrice'][$memberCouponVal]) {
                                $memberCouponPrice['memberCouponSalePrice'][$memberCouponVal] = $memberCouponData['couponMaxBenefit'];
                            }
                        }
                    } else if ($memberCouponData['couponBenefitType'] == 'fix') {//정액 쿠폰
                        if ($memberCouponData['couponBenefitFixApply'] == 'one') {
                            $memberCouponPrice['memberCouponSalePrice'][$memberCouponVal] = $memberCouponData['couponBenefit'];
                        } else {
                            $memberCouponPrice['memberCouponSalePrice'][$memberCouponVal] = $memberCouponData['couponBenefit'] * $goodsPrice['goodsCnt'];
                        }
                    }
                } else if ($memberCouponData['couponKindType'] == 'add') {// 마일리지 적립 쿠폰
                    if ($memberCouponData['couponBenefitType'] == 'percent') {//정율 쿠폰
                        $memberCouponPrice['memberCouponAddMileage'][$memberCouponVal] = $totalGoodsPrice * $memberCouponData['couponBenefit'] * 1 / 100;
                        $memberCouponPrice['memberCouponAddMileage'][$memberCouponVal] = gd_number_figure($memberCouponPrice['memberCouponAddMileage'][$memberCouponVal], $this->couponTrunc['unitPrecision'], $this->couponTrunc['unitRound']);
                        if ($memberCouponData['couponMaxBenefitType'] == 'y') {
                            if ($memberCouponData['couponMaxBenefit'] < $memberCouponPrice['memberCouponAddMileage'][$memberCouponVal]) {
                                $memberCouponPrice['memberCouponAddMileage'][$memberCouponVal] = $memberCouponData['couponMaxBenefit'];
                            }
                        }
                    } else if ($memberCouponData['couponBenefitType'] == 'fix') {//정액 쿠폰
                        if ($memberCouponData['couponBenefitFixApply'] == 'one') {
                            $memberCouponPrice['memberCouponAddMileage'][$memberCouponVal] = $memberCouponData['couponBenefit'];
                        } else {
                            $memberCouponPrice['memberCouponAddMileage'][$memberCouponVal] = $memberCouponData['couponBenefit'] * $goodsPrice['goodsCnt'];
                        }
                    }
                } else if ($memberCouponData['couponKindType'] == 'delivery') {// 배송비 할인 쿠폰
                    if ($memberCouponData['couponBenefitType'] == 'percent') {//정율 쿠폰
                        $memberCouponPrice['memberCouponDeliveryPrice'][$memberCouponVal] = $totalGoodsPrice * $memberCouponData['couponBenefit'] * 1 / 100;
                        $memberCouponPrice['memberCouponDeliveryPrice'][$memberCouponVal] = gd_number_figure($memberCouponPrice['memberCouponDeliveryPrice'][$memberCouponVal], $this->couponTrunc['unitPrecision'], $this->couponTrunc['unitRound']);
                        if ($memberCouponData['couponMaxBenefitType'] == 'y') {
                            if ($memberCouponData['couponMaxBenefit'] < $memberCouponPrice['memberCouponDeliveryPrice'][$memberCouponVal]) {
                                $memberCouponPrice['memberCouponDeliveryPrice'][$memberCouponVal] = $memberCouponData['couponMaxBenefit'];
                            }
                        }
                    } else if ($memberCouponData['couponBenefitType'] == 'fix') {//정액 쿠폰
                        $memberCouponPrice['memberCouponDeliveryPrice'][$memberCouponVal] = $memberCouponData['couponBenefit'];
                    }
                }
                unset($memberCouponData);
            }
        }

        return $memberCouponPrice;
    }

    /**
     * 해당(하나) 쿠폰의 허용/제외 조건에 해당하는 실제 이름 적용
     * - 허용/제외 조건에 고유번호로 저장된 내용을 고유번호와 실제 이름을 2차 배열로 반환
     *
     * @param array $couponData 쿠폰데이터
     *
     * @return array $couponData 쿠폰데이터의 허용/제외 조건에 해당하는 실제 이름을 추가배열로 반환
     *
     * @author su
     */
    public function getCouponApplyExceptData($couponData)
    {
        // 쿠폰 허용 회원등급
        if ($couponData['couponApplyMemberGroup']) {
            $couponApplyMemberGroup = explode(INT_DIVISION, $couponData['couponApplyMemberGroup']);
            unset($couponData['couponApplyMemberGroup']);
            foreach ($couponApplyMemberGroup as $key => $val) {
                $groupNm = GroupUtil::getGroupName('sno=' . $val);
                $couponData['couponApplyMemberGroup'][$key]['no'] = $val;
                $couponData['couponApplyMemberGroup'][$key]['name'] = $groupNm[$val];
            }
        }
        // 쿠폰 사용 회원등급
        $data = $this->db->query_fetch("SELECT couponUseMemberGroup FROM " . DB_COUPON_USE_MEMBER_GROUP . " WHERE couponNo = " . $couponData['couponNo'] . " ORDER BY couponUseMemberGroup");
        if ($data) {
            foreach ($data as $key => $val) {
                $groupNm = GroupUtil::getGroupName('sno=' . $val['couponUseMemberGroup']);
                $couponData['couponUseMemberGroup'][$key]['no'] = $val['couponUseMemberGroup'];
                $couponData['couponUseMemberGroup'][$key]['name'] = $groupNm[$val['couponUseMemberGroup']];
            }
        }
        if ($couponData['couponApplyProductType'] == 'provider') {
            // 쿠폰 허용 공급사
            if ($couponData['couponApplyProvider']) {
                $scm = \App::load('\\Component\\Scm\\Scm');
                $couponApplyProvider = explode(INT_DIVISION, $couponData['couponApplyProvider']);
                unset($couponData['couponApplyProvider']);
                foreach ($couponApplyProvider as $key => $val) {
                    $scmNm = $scm->getScmInfo($val, 'companyNm');
                    $couponData['couponApplyProvider'][$key]['no'] = $val;
                    $couponData['couponApplyProvider'][$key]['name'] = $scmNm['companyNm'];
                }
            }
        } else if ($couponData['couponApplyProductType'] == 'category') {
            // 쿠폰 허용 카테고리
            if ($couponData['couponApplyCategory']) {
                $category = \App::load('\\Component\\Category\\Category');
                $couponApplyCategory = explode(INT_DIVISION, $couponData['couponApplyCategory']);
                unset($couponData['couponApplyCategory']);
                foreach ($couponApplyCategory as $key => $val) {
                    $categoryNm = $category->getCategoryPosition($val);
                    $couponData['couponApplyCategory'][$key]['no'] = $val;
                    $couponData['couponApplyCategory'][$key]['name'] = $categoryNm;
                }
            }
        } else if ($couponData['couponApplyProductType'] == 'brand') {
            // 쿠폰 허용 브랜드
            if ($couponData['couponApplyBrand']) {
                $brand = \App::load('\\Component\\Category\\Brand');
                $couponApplyBrand = explode(INT_DIVISION, $couponData['couponApplyBrand']);
                unset($couponData['couponApplyBrand']);
                foreach ($couponApplyBrand as $key => $val) {
                    $brandNm = $brand->getCategoryPosition($val);
                    $couponData['couponApplyBrand'][$key]['no'] = $val;
                    $couponData['couponApplyBrand'][$key]['name'] = $brandNm;
                }
            }
        } else if ($couponData['couponApplyProductType'] == 'goods') {
            // 쿠폰 허용 상품
            if ($couponData['couponApplyGoods']) {
                $goods = \App::load('\\Component\\Goods\\Goods');
                $goodsData = $goods->getGoodsDataDisplay($couponData['couponApplyGoods']);
                unset($couponData['couponApplyGoods']);
                $couponData['couponApplyGoods'] = $goodsData;
            }
        }
        if ($couponData['couponExceptProviderType'] == 'y') {
            // 쿠폰 제외 공급사
            if ($couponData['couponExceptProvider']) {
                $scm = \App::load('\\Component\\Scm\\Scm');
                $couponExceptProvider = explode(INT_DIVISION, $couponData['couponExceptProvider']);
                unset($couponData['couponExceptProvider']);
                foreach ($couponExceptProvider as $key => $val) {
                    $scmNm = $scm->getScmInfo($val, 'companyNm');
                    $couponData['couponExceptProvider'][$key]['no'] = $val;
                    $couponData['couponExceptProvider'][$key]['name'] = $scmNm['companyNm'];
                }
            }
        }
        if ($couponData['couponExceptCategoryType'] == 'y') {
            // 쿠폰 제외 카테고리
            if ($couponData['couponExceptCategory']) {
                $category = \App::load('\\Component\\Category\\Category');
                $couponExceptCategory = explode(INT_DIVISION, $couponData['couponExceptCategory']);
                unset($couponData['couponExceptCategory']);
                foreach ($couponExceptCategory as $key => $val) {
                    $categoryNm = $category->getCategoryPosition($val);
                    $couponData['couponExceptCategory'][$key]['no'] = $val;
                    $couponData['couponExceptCategory'][$key]['name'] = $categoryNm;
                }
            }
        }
        if ($couponData['couponExceptBrandType'] == 'y') {
            // 쿠폰 제외 브랜드
            if ($couponData['couponExceptBrand']) {
                $brand = \App::load('\\Component\\Category\\Brand');
                $couponExceptBrand = explode(INT_DIVISION, $couponData['couponExceptBrand']);
                unset($couponData['couponExceptBrand']);
                foreach ($couponExceptBrand as $key => $val) {
                    $brandNm = $brand->getCategoryPosition($val);
                    $couponData['couponExceptBrand'][$key]['no'] = $val;
                    $couponData['couponExceptBrand'][$key]['name'] = $brandNm;
                }
            }
        }
        if ($couponData['couponExceptGoodsType'] == 'y') {
            // 쿠폰 제외 상품
            if ($couponData['couponExceptGoods']) {
                $goods = \App::load('\\Component\\Goods\\Goods');
                $goodsData = $goods->getGoodsDataDisplay($couponData['couponExceptGoods']);
                unset($couponData['couponExceptGoods']);
                $couponData['couponExceptGoods'] = $goodsData;
            }
        }

        return $couponData;
    }

    /**
     * 여러(Array) 쿠폰의 허용/제외 조건에 해당하는 실제 이름 적용
     * - 허용/제외 조건에 고유번호로 저장된 내용을 고유번호와 실제 이름을 2차 배열로 반환
     *
     * @param array $couponArrData 쿠폰데이터의 Arr
     *
     * @return array $couponArrData 쿠폰데이터의 Arr를 개별 허용/제외 조건에 해당하는 실제 이름을 추가배열로 반환
     *
     * @author su
     */
    public function getCouponApplyExceptArrData($couponArrData)
    {
        foreach ($couponArrData as $couponKey => $couponVal) {
            // 쿠폰 허용 회원등급
            if ($couponVal['couponSaveType'] == 'down') {
                if ($couponVal['couponApplyMemberGroup']) {
                    $couponApplyMemberGroup = explode(INT_DIVISION, $couponVal['couponApplyMemberGroup']);
                    unset($couponArrData[$couponKey]['couponApplyMemberGroup']);
                    foreach ($couponApplyMemberGroup as $key => $val) {
                        $groupNm = GroupUtil::getGroupName('sno=' . $val);
                        $couponArrData[$couponKey]['couponApplyMemberGroup'][$key]['no'] = $val;
                        $couponArrData[$couponKey]['couponApplyMemberGroup'][$key]['name'] = $groupNm[$val];
                    }
                }
            } else {
                // 쿠폰 사용 회원등급
                $data = $this->db->query_fetch("SELECT couponUseMemberGroup FROM " . DB_COUPON_USE_MEMBER_GROUP . " WHERE couponNo = " . $couponVal['couponNo'] . " ORDER BY couponUseMemberGroup");
                if ($data) {
                    unset($couponArrData[$couponKey]['couponApplyMemberGroup']);
                    foreach ($data as $key => $val) {
                        $groupNm = GroupUtil::getGroupName('sno=' . $val['couponUseMemberGroup']);
                        $couponArrData[$couponKey]['couponApplyMemberGroup'][$key]['no'] = $val['couponUseMemberGroup'];
                        $couponArrData[$couponKey]['couponApplyMemberGroup'][$key]['name'] = $groupNm[$val['couponUseMemberGroup']];
                    }
                } else {
                    $couponArrData[$couponKey]['couponApplyMemberGroup'] = NULL;
                }
            }

            if ($couponVal['couponApplyProductType'] == 'provider') {
                // 쿠폰 허용 공급사
                if ($couponVal['couponApplyProvider']) {
                    $scm = \App::load('\\Component\\Scm\\Scm');
                    $couponApplyProvider = explode(INT_DIVISION, $couponVal['couponApplyProvider']);
                    unset($couponArrData[$couponKey]['couponApplyProvider']);
                    foreach ($couponApplyProvider as $key => $val) {
                        $scmNm = $scm->getScmInfo($val, 'companyNm');
                        $couponArrData[$couponKey]['couponApplyProvider'][$key]['no'] = $val;
                        $couponArrData[$couponKey]['couponApplyProvider'][$key]['name'] = $scmNm['companyNm'];
                    }
                }
            } else if ($couponVal['couponApplyProductType'] == 'category') {
                // 쿠폰 허용 카테고리
                if ($couponVal['couponApplyCategory']) {
                    $category = \App::load('\\Component\\Category\\Category');
                    $couponApplyCategory = explode(INT_DIVISION, $couponVal['couponApplyCategory']);
                    unset($couponArrData[$couponKey]['couponApplyCategory']);
                    foreach ($couponApplyCategory as $key => $val) {
                        $categoryNm = $category->getCategoryPosition($val);
                        $couponArrData[$couponKey]['couponApplyCategory'][$key]['no'] = $val;
                        $couponArrData[$couponKey]['couponApplyCategory'][$key]['name'] = $categoryNm;
                    }
                }
            } else if ($couponVal['couponApplyProductType'] == 'brand') {
                // 쿠폰 허용 브랜드
                if ($couponVal['couponApplyBrand']) {
                    $brand = \App::load('\\Component\\Category\\Brand');
                    $couponApplyBrand = explode(INT_DIVISION, $couponVal['couponApplyBrand']);
                    unset($couponArrData[$couponKey]['couponApplyBrand']);
                    foreach ($couponApplyBrand as $key => $val) {
                        $brandNm = $brand->getCategoryPosition($val);
                        $couponArrData[$couponKey]['couponApplyBrand'][$key]['no'] = $val;
                        $couponArrData[$couponKey]['couponApplyBrand'][$key]['name'] = $brandNm;
                    }
                }
            } else if ($couponVal['couponApplyProductType'] == 'goods') {
                // 쿠폰 허용 상품
                if ($couponVal['couponApplyGoods']) {
                    $goods = \App::load('\\Component\\Goods\\Goods');
                    $goodsData = $goods->getGoodsDataDisplay($couponVal['couponApplyGoods']);
                    unset($couponArrData[$couponKey]['couponApplyGoods']);
                    $couponArrData[$couponKey]['couponApplyGoods'] = $goodsData;
                }
            }
            if ($couponVal['couponExceptProviderType'] == 'y') {
                // 쿠폰 제외 공급사
                if ($couponVal['couponExceptProvider']) {
                    $scm = \App::load('\\Component\\Scm\\Scm');
                    $couponExceptProvider = explode(INT_DIVISION, $couponVal['couponExceptProvider']);
                    unset($couponArrData[$couponKey]['couponExceptProvider']);
                    foreach ($couponExceptProvider as $key => $val) {
                        $scmNm = $scm->getScmInfo($val, 'companyNm');
                        $couponArrData[$couponKey]['couponExceptProvider'][$key]['no'] = $val;
                        $couponArrData[$couponKey]['couponExceptProvider'][$key]['name'] = $scmNm['companyNm'];
                    }
                }
            }
            if ($couponVal['couponExceptCategoryType'] == 'y') {
                // 쿠폰 제외 카테고리
                if ($couponVal['couponExceptCategory']) {
                    $category = \App::load('\\Component\\Category\\Category');
                    $couponExceptCategory = explode(INT_DIVISION, $couponVal['couponExceptCategory']);
                    unset($couponArrData[$couponKey]['couponExceptCategory']);
                    foreach ($couponExceptCategory as $key => $val) {
                        $categoryNm = $category->getCategoryPosition($val);
                        $couponArrData[$couponKey]['couponExceptCategory'][$key]['no'] = $val;
                        $couponArrData[$couponKey]['couponExceptCategory'][$key]['name'] = $categoryNm;
                    }
                }
            }
            if ($couponVal['couponExceptBrandType'] == 'y') {
                // 쿠폰 제외 브랜드
                if ($couponVal['couponExceptBrand']) {
                    $brand = \App::load('\\Component\\Category\\Brand');
                    $couponExceptBrand = explode(INT_DIVISION, $couponVal['couponExceptBrand']);
                    unset($couponArrData[$couponKey]['couponExceptBrand']);
                    foreach ($couponExceptBrand as $key => $val) {
                        $brandNm = $brand->getCategoryPosition($val);
                        $couponArrData[$couponKey]['couponExceptBrand'][$key]['no'] = $val;
                        $couponArrData[$couponKey]['couponExceptBrand'][$key]['name'] = $brandNm;
                    }
                }
            }
            if ($couponVal['couponExceptGoodsType'] == 'y') {
                // 쿠폰 제외 상품
                if ($couponVal['couponExceptGoods']) {
                    $goods = \App::load('\\Component\\Goods\\Goods');
                    $goodsData = $goods->getGoodsDataDisplay($couponVal['couponExceptGoods']);
                    unset($couponArrData[$couponKey]['couponExceptGoods']);
                    $couponArrData[$couponKey]['couponExceptGoods'] = $goodsData;
                }
            }
        }

        return $couponArrData;
    }

    /**
     * 발급 가능한 자동 발급 쿠폰 정보
     *
     * @param string  $couponEventType 자동발급 이벤트 코드
     *                                 [첫구매(‘first’),구매감사(‘order’),생일축하(‘birth’),회원가입(‘join’),출석체크(‘attend’),회원정보수정이벤트(‘memberModifyEvent’)]
     * @param integer $memNo           회원고유번호
     * @param integer $memGroupNo      회원그룹고유번호
     * @param integer $couponNo        쿠폰 고유번호
     * @param string  $firstOrder      첫 구매 여부
     *
     * @return array 쿠폰 정보
     *
     * @author su
     */
    public function getAutoCouponUsable($couponEventType, $memNo, $memGroupNo, $couponNo = null, $firstOrder = null)
    {
        $this->db->strField = "c.*";
        $addQuery[] = "c.couponType = 'y' AND couponSaveType = 'auto' AND c.couponEventType = ?";
        $this->db->bind_param_push($arrBind, 's', $couponEventType);
        if ($couponNo > 0) {
            $addQuery[] = "c.couponNo = ?";
            $this->db->bind_param_push($arrBind, 'i', $couponNo);
        }
        if ($couponEventType == 'order' && $firstOrder == 'y') {
            $addQuery[] = 'c.couponEventOrderFirstType = ?';
            $this->db->bind_param_push($arrBind, 's', 'y');
        }
        $this->db->strWhere = implode(' AND ', $addQuery);
        $this->db->strOrder = 'c.regDt asc';
        $query = $this->db->query_complete();
        $strSQL = 'SELECT ' . array_shift($query) . ' FROM ' . DB_COUPON . ' as c ' . implode(' ', $query);
        $autoCouponUsableData = $this->db->query_fetch($strSQL, $arrBind);

        // 제외 $key
        $removeKey = [];
        if (is_array($autoCouponUsableData)) {
            foreach ($autoCouponUsableData as $key => $val) {
                if(!$this->checkCouponType($val['couponNo'])) {
                    $removeKey[] = $key;
                    continue;
                }
                // 발급 수량
                if ($val['couponAmountType'] == 'n') {// 무제한
                    $couponListData[$key]['chkMemberCoupon'] = 'YES';
                } else if ($val['couponAmountType'] == 'y') {// 제한
                    // 발급 총 수량 제한 체크
                    if ($val['couponAmount'] <= $val['couponSaveCount']) {// 발급 수량 외로 제외처리
                        $removeKey[] = $key;
                        continue;
                    }
                }
                if ($memNo) {
                    // 발급 회원등급 체크
                    if ($val['couponApplyMemberGroup'] && $memGroupNo) {//발급회원등급이 있다면
                        $applyMemberGroupArr = explode(INT_DIVISION, $val['couponApplyMemberGroup']);
                        $chkMemberCoupon = array_search($memGroupNo, $applyMemberGroupArr);
                        if ($chkMemberCoupon !== false) {//로그인한 회원등급 존재
                            $couponListData[$key]['chkMemberCoupon'] = 'YES';
                        } else {//로그인한 회원등급 존재안함
                            $removeKey[] = $key;
                            continue;
                        }
                    }
                    $saveMemberCouponCount = $this->getMemberCouponTotalCount($val['couponNo'], $memNo); // 회원의 해당 쿠폰 발급 총 갯수
                    $stateMemberCoupon = $this->getMemberCouponState($val['couponNo'], $memNo);

                    // 기존 발급된 생일쿠폰 년도 확인 (년도가 없는 경우 등록일로 사용)
                    if ($couponEventType == 'birth' && empty($this->birthDayCouponYear) == false) {
                        $memberBirthDayCouponYearArr = $this->getMemberBirthDayCouponYear($val['couponNo'], $memNo);
                        if (count($memberBirthDayCouponYearArr) > 0) {
                            $memberBirthDayCouponYear = gd_isset($memberBirthDayCouponYearArr['birthDayCouponYear'], date('Y', strtotime($memberBirthDayCouponYearArr['regDt'])));
                        }
                        unset($memberBirthDayCouponYearArr);
                    }

                    // 재발급 제한
                    if ($val['couponSaveDuplicateType'] == 'n') {// 재발급 안됨
                        if ($saveMemberCouponCount > 0) { // 발급된 내역이 있다면
                            $removeKey[] = $key;
                            continue;
                        } else {
                            $couponListData[$key]['chkMemberCoupon'] = 'YES';
                        }
                    } else if ($val['couponSaveDuplicateType'] == 'y') {// 재발급 가능
                        if ($val['couponSaveDuplicateLimitType'] == 'y') {// 재발급 최대 수 여부
                            // 동일 생일쿠폰은 1년에 1개만 지급
                            if ($couponEventType == 'birth' && empty($memberBirthDayCouponYear) == false && $memberBirthDayCouponYear >= $this->birthDayCouponYear) {
                                $removeKey[] = $key;
                                continue;
                            }
                            if ($saveMemberCouponCount > 0) { // 발급된 내역이 있다면
                                if ($val['couponSaveDuplicateLimit'] <= $saveMemberCouponCount) {
                                    $removeKey[] = $key;
                                    continue;
                                } else {// 재발급 최대 수에 모자람
                                    if ($stateMemberCoupon) {// 해당 쿠폰이 모두 주문사용 사용됨
                                        $couponListData[$key]['chkMemberCoupon'] = 'YES';
                                    } else {// 해당 쿠폰이 장바구니 사용 또는 사용전
                                        $removeKey[] = $key;
                                        continue;
                                    }
                                }
                            } else {
                                $couponListData[$key]['chkMemberCoupon'] = 'YES';
                            }
                        } else {// 무조건 재발급
                            if ($stateMemberCoupon) {// 해당 쿠폰이 모두 주문사용 사용됨
                                // 동일 생일쿠폰은 1년에 1개만 지급
                                if ($couponEventType == 'birth' && $saveMemberCouponCount > 0 && empty($memberBirthDayCouponYear) == false && $memberBirthDayCouponYear >= $this->birthDayCouponYear) {
                                    $removeKey[] = $key;
                                    continue;
                                } else {
                                    $couponListData[$key]['chkMemberCoupon'] = 'YES';
                                }
                            } else {// 해당 쿠폰이 장바구니 사용 또는 사용전
                                $removeKey[] = $key;
                                continue;
                            }
                        }
                    }
                }
            }
        }
        // 조건 필터링
        foreach ($removeKey as $val) {
            unset($autoCouponUsableData[$val]);
        }
        unset($removeKey);

        return gd_htmlspecialchars_stripslashes($autoCouponUsableData);
    }

    /**
     * 회원 쿠폰 정보 출력
     * 완성된 쿼리문은 $db->strField , $db->strJoin , $db->strWhere , $db->strGroup , $db->strOrder , $db->strLimit 멤버 변수를
     * 이용할수 있습니다.
     *
     * @param integer     $memberCouponNo    회원쿠폰 고유 번호
     * @param string      $memberCouponField 출력할 필드명
     * @param array       $arrBind           bind 처리 배열 (기본 null)
     * @param bool|string $dataArray         return 값을 배열처리 (기본값 false)
     *
     * @return array 회원 쿠폰 정보
     */
    public function getMemberCouponInfo($memberCouponNo = null, $memberCouponField = null, $arrBind = null, $dataArray = false)
    {
        if (is_null($arrBind)) {
            // $arrBind = array();
        }
        if (!empty($memberCouponNo)) {
            $this->db->strWhere = " mc.memberCouponNo = ?";
            $this->db->bind_param_push($arrBind, 'i', $memberCouponNo);
        } else {
            return false;
        }
        if ($memberCouponField) {
            if ($this->db->strField) {
                $this->db->strField = $memberCouponField . ', ' . $this->db->strField;
            } else {
                $this->db->strField = $memberCouponField;
            }
        }
        $this->db->strJoin = "LEFT JOIN " . DB_COUPON . " as c ON mc.couponNo = c.couponNo";
        $query = $this->db->query_complete();
        $strSQL = 'SELECT ' . array_shift($query) . ' FROM ' . DB_MEMBER_COUPON . ' as mc ' . implode(' ', $query);
        $getData = $this->db->query_fetch($strSQL, $arrBind);
        unset($arrBind);

        if (count($getData) == 1 && $dataArray === false) {
            return gd_htmlspecialchars_stripslashes($getData[0]);
        }

        return gd_htmlspecialchars_stripslashes($getData);
    }

    /**
     * 회원의 쿠폰 리스트 가져오기
     * - 마이페이지 나의쿠폰함에서 발급받은 모든 쿠폰을 리스트
     * - 회원쿠폰정보와 해당 쿠폰의 정보를 포함
     *
     * @param integer $memNo 회원고유번호
     * @param string  $pageType 프론트/모바일
     *
     * @return array $memberCouponData 회원쿠폰의 리스트
     *
     * @author su
     */
    public function getMemberCouponList($memNo, $pageType = 'pc')
    {
        $getValue = Request::get()->toArray();
        Request::get()->set('pageType', $pageType);
        Request::get()->set('memberCouponState', gd_isset(Request::request()->get('memberCouponState'), 'y'));
        $getValue['pageType'] = ($pageType == 'mobile') ? 'mobile' : 'pc';
        $this->setMemberCouponSearch($getValue);

        if ($getValue['pageType'] == 'pc') {
            $sort['fieldName'] = gd_isset($getValue['sort']['name']);
            $sort['sortMode'] = gd_isset($getValue['sort']['mode']);
            if (empty($sort['fieldName']) || empty($sort['sortMode'])) {
                $sort['fieldName'] = 'mc.regDt';
                $sort['sortMode'] = 'desc';
            } else {
                $sort['fieldName'] = 'mc' . $sort['fieldName'];
            }

            gd_isset($getValue['page'], 1);
            gd_isset($getValue['pageNum'], 10);

            $page = \App::load('\\Component\\Page\\Page', $getValue['page']);
            $page->page['list'] = $getValue['pageNum'];
            list($page->recode['amount']) = $this->db->fetch('SELECT count(memberCouponNo) FROM ' . DB_MEMBER_COUPON . ' WHERE memNo = ' . $memNo, 'row');
            $page->setPage();
            $page->setUrl(\Request::getQueryString());
        }


        // 검색 조건
        $this->arrWhere[] = 'mc.memNo = ?';
        $this->db->bind_param_push($this->arrBind, 'i', $memNo);

        if ($getValue['pageType'] == 'mobile') {
            $sort['fieldName'] = 'mc.regDt';
            $sort['sortMode'] = 'desc';
        }

        $this->db->strField = "mc.*, c.*, mc.regDt, mc.modDt";
        $this->db->strWhere = implode(' AND ', gd_isset($this->arrWhere));
        $this->db->strJoin = "LEFT JOIN " . DB_COUPON . " as c on mc.couponNo = c.couponNo";
        $this->db->strOrder = $sort['fieldName'] . ' ' . $sort['sortMode'];

        if ($getValue['pageType'] == 'pc') {
            $this->db->strLimit = $page->recode['start'] . ',' . $getValue['pageNum'];
        }

        // 검색 카운트
        $strSQL = ' SELECT COUNT(mc.memberCouponNo) AS cnt FROM ' . DB_MEMBER_COUPON .' as mc ' . $this->db->strJoin . ' WHERE ' . $this->db->strWhere;
        $res = $this->db->query_fetch($strSQL, $this->arrBind, false);

        if ($getValue['pageType'] == 'pc') {
            $page->recode['total'] = $res['cnt']; // 검색 레코드 수
            $page->setPage();
            $page->setUrl(\Request::getQueryString());
        }

        $query = $this->db->query_complete();
        $strSQL = 'SELECT ' . array_shift($query) . ' FROM ' . DB_MEMBER_COUPON . ' as mc ' . implode(' ', $query);
        $data = $this->db->query_fetch($strSQL, $this->arrBind);
        unset($this->arrBind);
        unset($this->arrWhere);

        $memberCouponData['data'] = gd_htmlspecialchars_stripslashes(gd_isset($data));
        $memberCouponData['sort'] = $sort;
        $memberCouponData['search'] = gd_htmlspecialchars($this->search);
        $memberCouponData['selected'] = $this->selected;

        return $memberCouponData;
    }

    /**
     * 회원의 쿠폰 리스트 가져오기(모바일용)
     * 모바일은 사용가능 쿠폰만 노출
     * 모바일은 페이지 없고
     * 모바일은 제한조건/사용조건 노출 없음
     * 모바일은 모바일에서 사용할 수 있는 쿠폰만 노출
     *
     * @param integer $memNo 회원고유번호
     *
     * @return array $memberCouponData 회원쿠폰의 리스트
     *
     * @author su
     */
    public function getMobileMemberCouponList($memNo)
    {
        // 검색 조건
        $this->arrWhere[] = 'mc.memNo = ?';
        $this->arrWhere[] = 'c.couponDeviceType IN (?,?)';
        $this->arrWhere[] = 'mc.memberCouponState = ?';
        $this->arrWhere[] = 'mc.memberCouponStartDate <= NOW() AND mc.memberCouponEndDate >= NOW()';
        $this->db->bind_param_push($this->arrBind, 'i', $memNo);
        $this->db->bind_param_push($this->arrBind, 's', 'all');
        $this->db->bind_param_push($this->arrBind, 's', 'mobile');
        $this->db->bind_param_push($this->arrBind, 's', 'y');

        $this->db->strField = "c.*, mc.memberCouponNo, mc.memberCouponStartDate, mc.memberCouponEndDate, mc.memberCouponState";
        $this->db->strWhere = implode(' AND ', gd_isset($this->arrWhere));
        $this->db->strJoin = "LEFT JOIN " . DB_COUPON . " as c on mc.couponNo = c.couponNo";
        $this->db->strOrder = 'regDt desc';

        $query = $this->db->query_complete();
        $strSQL = 'SELECT ' . array_shift($query) . ' FROM ' . DB_MEMBER_COUPON . ' as mc ' . implode(' ', $query);
        $data = $this->db->query_fetch($strSQL, $this->arrBind);
        unset($this->arrBind);
        unset($this->arrWhere);

        $memberCouponData['data'] = gd_htmlspecialchars_stripslashes(gd_isset($data));

        return $memberCouponData;
    }

    /**
     * 쿠폰 이미지
     * - 쿠폰이미지 경로 포함 가져오기
     *
     * @param string $couponImageNm 쿠폰이미지의 이름
     *
     * @return string $couponImagePath 쿠폰이미지의 경로포함
     *
     * @author su
     */
    public function getCouponImageData($couponImageNm)
    {
        $couponImagePath = $this->storage()->getHttpPath($couponImageNm);

        return $couponImagePath;
    }

    /**
     * setAutoCouponMemberSave 함수 실행 결과를 반환하는 함수
     *
     * @return SimpleStorage
     */
    public function getResultStorage()
    {
        return $this->resultStorage;
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
    public function convertCouponArrData($couponArrData)
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
                unset($couponEndDate);
                $convertCouponArrData[$key]['couponUsePeriodType'] = '쿠폰사용가능기간-기간';
                if (strtotime($val['couponUseDateLimit']) > 0) {
                    $endDate = $this->getMemberCouponEndDate($val['couponNo']);
                    if ($endDate == $val['couponUseDateLimit']) {
                        $couponEndDate = '사용가능일 : ' . $val['couponUseDateLimit'];
                    } else {
                        $couponEndDate = '발급일로부터 ' . $val['couponUsePeriodDay'] . '일까지';
                    }
                } else {
                    $couponEndDate = '발급일로부터 ' . $val['couponUsePeriodDay'] . '일까지';
                }
                $convertCouponArrData[$key]['useEndDate'] = $couponEndDate;
            }
            if ($val['couponKindType'] == 'sale') {
                $convertCouponArrData[$key]['couponKindType'] = '상품할인';
                $convertCouponArrData[$key]['couponKindTypeShort'] = '할인';
            } else if ($val['couponKindType'] == 'add') {
                $convertCouponArrData[$key]['couponKindType'] = $mileageName . '적립';
                $convertCouponArrData[$key]['couponKindTypeShort'] = '적립';
            } else if ($val['couponKindType'] == 'delivery') {
                $convertCouponArrData[$key]['couponKindType'] = '배송비할인';
                $convertCouponArrData[$key]['couponKindTypeShort'] = '할인';
            } else if ($val['couponKindType'] == 'deposit') {
                $convertCouponArrData[$key]['couponKindType'] = $depositName . '지급';
                $convertCouponArrData[$key]['couponKindTypeShort'] = '지급';
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

            if ($val['couponProductMinOrderType'] == 'product') {
                $convertCouponArrData[$key]['couponProductMinOrderType'] = '상품';
            } else {
                $convertCouponArrData[$key]['couponProductMinOrderType'] = '주문';
            }

            // 레이어에서 데이터 선택시 사용하기 위해 추가
            $convertCouponArrData[$key]['couponNo'] = $val['couponNo'];
            $convertCouponArrData[$key]['couponNm'] = $val['couponNm'];
            $convertCouponArrData[$key]['regDt'] = $val['regDt'];
        }

        return $convertCouponArrData;
    }

    /**
     * 해당(한개) 쿠폰데이터를 한글로 변경한 별도의 Array 생성
     *
     * @param array $couponData 쿠폰의데이터
     *
     * @return array $convertCouponData 쿠폰의데이터의 표시용 한글 변경
     *
     * @author su
     */
    public function convertCouponData($couponData)
    {
        $mileage = gd_mileage_give_info();
        $mileageName = $mileage['basic']['name'];
        $deposit = gd_policy('member.depositConfig');
        $depositName = $deposit['name'];
        if ($couponData['couponKind'] == 'online') {
            $convertCouponData['couponKind'] = '일반쿠폰';
        } else if ($couponData['couponKind'] == 'offline') {
            $convertCouponData['couponKind'] = '페이퍼쿠폰';
        }
        if ($couponData['couponType'] == 'y') {
            $convertCouponData['couponType'] = '발급중';
        } else if ($couponData['couponType'] == 'n') {
            $convertCouponData['couponType'] = '일시중지';
        } else if($couponData['couponType'] == 'f') {
            $convertCouponData['couponType'] = '발급종료';
        }
        if ($couponData['couponSaveType'] == 'down') {
            $convertCouponData['couponSaveType'] = '회원다운로드';
        } else if ($couponData['couponSaveType'] == 'auto') {
            $convertCouponData['couponSaveType'] = '자동발급';
        } else if ($couponData['couponSaveType'] == 'manual') {
            $convertCouponData['couponSaveType'] = '수동발급';
        }
        if ($couponData['couponUseType'] == 'product') {
            $convertCouponData['couponUseType'] = '상품적용쿠폰';
        } else if ($couponData['couponUseType'] == 'order') {
            $convertCouponData['couponUseType'] = '주문적용쿠폰';
        } else if ($couponData['couponUseType'] == 'delivery') {
            $convertCouponData['couponUseType'] = '배송비적용쿠폰';
        }
        if ($couponData['couponUsePeriodType'] == 'period') {
            $convertCouponData['couponUsePeriodType'] = '쿠폰사용가능기간-날짜';
            $convertCouponData['useEndDate'] = date('Y-m-d H:i', strtotime($couponData['couponUsePeriodStartDate'])) . '~' . date('Y-m-d H:i', strtotime($couponData['couponUsePeriodEndDate']));
        } else if ($couponData['couponUsePeriodType'] == 'day') {
            $convertCouponData['couponUsePeriodType'] = '쿠폰사용가능기간-기간';
            if (strtotime($couponData['couponUseDateLimit']) > 0) {
                $couponEndDate = '<br/>사용가능일 : ' . $couponData['couponUseDateLimit'];
            }
            $convertCouponData['useEndDate'] = '발급일로부터 ' . $couponData['couponUsePeriodDay'] . '일까지' . $couponEndDate;
        }
        if ($couponData['couponKindType'] == 'sale') {
            $convertCouponData['couponKindType'] = '상품할인';
            $convertCouponData['couponKindTypeShort'] = '할인';
        } else if ($couponData['couponKindType'] == 'add') {
            $convertCouponData['couponKindType'] = $mileageName . '적립';
            $convertCouponData['couponKindTypeShort'] = '적립';
        } else if ($couponData['couponKindType'] == 'delivery') {
            $convertCouponData['couponKindType'] = '배송비할인';
            $convertCouponData['couponKindTypeShort'] = '할인';
        } else if ($couponData['couponKindType'] == 'deposit') {
            $convertCouponData['couponKindType'] = $depositName . '지급';
            $convertCouponData['couponKindTypeShort'] = '지급';
        }
        if ($couponData['couponDeviceType'] == 'all') {
            $convertCouponData['couponDeviceType'] = 'PC+모바일';
        } else if ($couponData['couponDeviceType'] == 'pc') {
            $convertCouponData['couponDeviceType'] = 'PC';
        } else if ($couponData['couponDeviceType'] == 'mobile') {
            $convertCouponData['couponDeviceType'] = '모바일';
        }
        if ($couponData['couponBenefitType'] == 'percent') {
            $convertCouponData['couponBenefit'] = number_format($couponData['couponBenefit']) . ' %';
            if ($couponData['couponMaxBenefitType'] == 'y') {
                $convertCouponData['couponMaxBenefit'] = '최대 할인액 : ' . gd_currency_symbol() . ' ' . gd_money_format($couponData['couponMaxBenefit']) . ' ' . gd_currency_string();
            } else {
                $convertCouponData['couponMaxBenefit'] = '';
            }
        } else if ($couponData['couponBenefitType'] == 'fix') {
            $convertCouponData['couponBenefit'] = gd_currency_symbol() . ' ' . gd_money_format($couponData['couponBenefit']) . ' ';
            if ($couponData['couponKindType'] == 'add') {
                $convertCouponData['couponBenefit'] .= Globals::get('gSite.member.mileageBasic.unit');
            } elseif ($couponData['couponKindType'] == 'deposit') {
                $convertCouponData['couponBenefit'] .= Globals::get('gSite.member.depositConfig.unit');
            } else {
                $convertCouponData['couponBenefit'] .= gd_currency_string();
            }
        }
        if ($couponData['couponSaveDuplicateType'] == 'y') {
            $convertCouponData['couponSaveDuplicateType'] = '중복 발급가능';
            if ($couponData['couponSaveDuplicateLimitType'] == 'y') {
                $convertCouponData['couponSaveDuplicateType'] .= ' (최대 ' . $couponData['couponSaveDuplicateLimit'] . '장 중복가능)';
            }
        } else {
            $convertCouponData['couponSaveDuplicateType'] = '중복 발급불가';
        }
        if ($couponData['couponMinOrderPrice'] > 0) {
            $convertCouponData['couponMinOrderPrice'] = '구매금액이 ' . $couponData['couponMinOrderPrice'] . ' 이상 결제시 사용가능';
        } else {
            $convertCouponData['couponMinOrderPrice'] = '';
        }
        if ($couponData['couponApplyDuplicateType'] == 'y') {
            $convertCouponData['couponApplyDuplicateType'] = '중복 사용가능';
        } else {
            $convertCouponData['couponApplyDuplicateType'] = '중복 사용불가';
        }
        if ($couponData['couponAuthType'] == 'y') {
            $convertCouponData['couponAuthType'] = '회원별로 다른 인증번호';
        } else {
            $convertCouponData['couponAuthType'] = '1개의 인증번호 사용';
        }

        return $convertCouponData;
    }

    /**
     * 해당 회원쿠폰의 상태 변경
     *
     * @param string $memberCouponNo    회원쿠폰고유번호 (INT_DIVISION로 구분된 memberCouponNo)
     * @param string $memberCouponState 변경할 회원쿠폰 상태
     * @param string $orderWriteFieldChange 수기주문에서 사용하는 쿠폰사용 여부 체크값 변동 여부
     * @param integer $cartSno 장바구니 sno
     *
     * @author su
     */
    public function setMemberCouponState($memberCouponNo, $memberCouponState, $orderWriteFieldChange = false, $cartSno = null)
    {
        $memberCouponArrNo = explode(INT_DIVISION, $memberCouponNo);
        foreach ($memberCouponArrNo as $val) {
            $arrBind = [];
            $arrData['memberCouponState'] = $memberCouponState;
            if($orderWriteFieldChange === true){
                $arrData['orderWriteCouponState'] = $memberCouponState;
            }
            if ($memberCouponState == 'y') {
                $arrData['memberCouponCartDate'] = '';
                $arrData['memberCouponUseDate'] = '';
            } else if ($memberCouponState == 'cart') {
                $arrData['memberCouponCartDate'] = date('Y-m-d H:i:s');
            } else if ($memberCouponState == 'order' || $memberCouponState == 'coupon') {
                $arrData['memberCouponUseDate'] = date('Y-m-d H:i:s');
            }
            $arrBind = $this->db->get_binding(DBTableField::tableMemberCoupon(), $arrData, 'update', array_keys($arrData), ['memberCouponNo']);
            $this->db->bind_param_push($arrBind['bind'], 'i', $val);
            $this->db->set_update_db(DB_MEMBER_COUPON, $arrBind['param'], 'memberCouponNo = ?', $arrBind['bind'], false);
            unset($arrBind);
            unset($arrData);
        }
    }

    /**
     * 수기 주문시 orderWriteCouponState 필드상태값 수정
     *
     * @param string $memberCouponNo    회원쿠폰고유번호 (INT_DIVISION로 구분된 memberCouponNo)
     * @param string $memberCouponState 변경할 수기주문 회원쿠폰 상태
     *
     * @author by
     */
    public function setMemberCouponStateOrderWrite($memberCouponNo, $memberCouponState)
    {
        $memberCouponArrNo = explode(INT_DIVISION, $memberCouponNo);
        foreach ($memberCouponArrNo as $val) {
            $arrBind = [];
            $arrData['orderWriteCouponState'] = $memberCouponState;
            $arrBind = $this->db->get_binding(DBTableField::tableMemberCoupon(), $arrData, 'update', array_keys($arrData), ['memberCouponNo']);
            $this->db->bind_param_push($arrBind['bind'], 'i', $val);
            $this->db->set_update_db(DB_MEMBER_COUPON, $arrBind['param'], 'memberCouponNo = ?', $arrBind['bind'], false);
            unset($arrBind);
            unset($arrData);
        }
    }

    /*
     * 수기주문에서 orderWriteCouponState 값 초기화
     */
    public function resetMemberCouponStateOrderWrite($memNo)
    {
        $arrBind = [];
        $this->db->bind_param_push($arrBind, 's', 'y');
        $this->db->bind_param_push($arrBind, 'i', $memNo);
        $this->db->bind_param_push($arrBind, 's', 'order');
        $this->db->bind_param_push($arrBind, 's', 'order');
        $this->db->set_update_db_query(DB_MEMBER_COUPON, 'orderWriteCouponState = ?', 'memNo = ? AND orderWriteCouponState != ? AND memberCouponState != ?', $arrBind);
        unset($arrBind);
    }

    /**
     * 상품에 적용된 회원다운로드 쿠폰을 발급 받기
     *
     * @param mixed   $couponNo 쿠폰고유번호 (쿠폰여러개Arr면 배열로 처리, 단일쿠폰고유번호면 해당쿠폰만 발급됨)
     * @param integer $goodsNo  상품고유번호
     * @param integer $memNo    회원고유번호
     *
     * @throws \Exception 발급조건이 안맞을 때
     *
     * @author su
     */
    public function setGoodsCouponMemberSave($couponNo, $goodsNo, $memNo, $memGroupNo)
    {
        $checkCoupon = false;
        $goodsMemberCouponArrData = $this->getGoodsCouponDownList($goodsNo, $memNo, $memGroupNo);
        foreach ($goodsMemberCouponArrData as $val) {
            if ($val['chkMemberCoupon'] == 'YES') {
                $couponNoData[] = $val['couponNo'];
            }
        }

        if (is_array($couponNo)) {
            foreach ($couponNo as $val) {
                $checkCoupon = in_array($val, $couponNoData);

                if ($checkCoupon == true) {
                    $arrData = [];
                    $arrData['couponNo'] = $val;
                    $arrData['couponSaveAdminId'] = $goodsNo . '의 상품쿠폰';
                    $arrData['memberCouponStartDate'] = $this->getMemberCouponStartDate($val);
                    $arrData['memberCouponEndDate'] = $this->getMemberCouponEndDate($val);
                    $arrData['memberCouponState'] = 'y';
                    $arrData['memNo'] = $memNo;
                    $arrBind = $this->db->get_binding(DBTableField::tableMemberCoupon(), $arrData, 'insert', array_keys($arrData), ['memberCouponNo']);
                    $this->db->set_insert_db(DB_MEMBER_COUPON, $arrBind['param'], $arrBind['bind'], 'y');
                    $this->setCouponMemberSaveCount($val);
                } else {
                    throw new \Exception('발급 조건이 맞지 않는 쿠폰이있습니다. 쿠폰다운창을 다시 열고 받아주세요.');
                }
            }
        } else {
            $checkCoupon = in_array($couponNo, $couponNoData);

            if ($checkCoupon == true) {
                $arrData = [];
                $arrData['couponNo'] = $couponNo;
                $arrData['couponSaveAdminId'] = $goodsNo . '의 상품쿠폰';
                $arrData['memberCouponStartDate'] = $this->getMemberCouponStartDate($couponNo);
                $arrData['memberCouponEndDate'] = $this->getMemberCouponEndDate($couponNo);
                $arrData['memberCouponState'] = 'y';
                $arrData['memNo'] = Session::get('member.memNo');
                $arrBind = $this->db->get_binding(DBTableField::tableMemberCoupon(), $arrData, 'insert', array_keys($arrData), ['memberCouponNo']);
                $this->db->set_insert_db(DB_MEMBER_COUPON, $arrBind['param'], $arrBind['bind'], 'y');
                $this->setCouponMemberSaveCount($couponNo);
            } else {
                throw new \Exception('발급 조건이 맞지 않습니다.');
            }
        }
    }

    /**
     * 오프라인 쿠폰을 발급 받기
     *
     * @param string  $couponOfflineNumber 오프라인쿠폰유저코드
     * @param integer $memNo               회원고유번호
     * @param integer $memGroupNo          회원등급고유번호
     *
     * @return string
     * @throws \Exception 발급조건이 안맞을 때
     *
     * @author su
     */
    public function setOfflineCouponMemberSave($couponOfflineNumber, $memNo, $memGroupNo)
    {
        $offlineCouponArrData = $this->getOfflineCouponDownList($couponOfflineNumber, $memNo, $memGroupNo);
        if ($offlineCouponArrData) {
            foreach ($offlineCouponArrData as $offlineCouponKey => $offlineCouponVal) {
                if (!$this->checkCouponType($offlineCouponVal['couponNo'])) {
                    throw new \Exception('발급조건이 맞지 않습니다.');
                }
                if ($offlineCouponVal['chkMemberCoupon'] == 'YES') {
                    // 회원쿠폰 발급
                    $arrData = [];
                    $arrData['couponNo'] = $offlineCouponVal['couponNo'];
                    $arrData['couponSaveAdminId'] = $offlineCouponVal['couponNm'] . '의 페이퍼 쿠폰';
                    $arrData['memberCouponStartDate'] = $this->getMemberCouponStartDate($offlineCouponVal['couponNo']);
                    $arrData['memberCouponEndDate'] = $this->getMemberCouponEndDate($offlineCouponVal['couponNo']);
                    $arrData['memberCouponState'] = 'y';
                    $arrData['memNo'] = $memNo;
                    $arrBind = $this->db->get_binding(DBTableField::tableMemberCoupon(), $arrData, 'insert', array_keys($arrData), ['memberCouponNo']);
                    // 쿠폰 insert 전 한번 더 중복 체크
                    if ($offlineCouponVal['couponSaveDuplicateType']) {
                        $strBind = [];
                        foreach ($arrBind['param'] as $_bind) {
                            $strBind[] = '?';
                        }
                        $strSQL = 'INSERT INTO '. DB_MEMBER_COUPON . '(' . implode(',', $arrBind['param']) . ', regDt)';
                        $strSQL .= ' SELECT ' . implode(',', $strBind) . ', NOW() FROM DUAL ' ;
                        $strSQL .= ' WHERE (SELECT count(memNo) as countMemberCoupon FROM ' . DB_MEMBER_COUPON . ' WHERE couponNo = ? AND memNo = ? AND regDt >= (now()-INTERVAL 30 SECOND)) = 0';
                        $this->db->bind_param_push($arrBind['bind'], 'i', $arrData['couponNo']);
                        $this->db->bind_param_push($arrBind['bind'], 'i', $arrData['memNo']);
                        $this->db->bind_query($strSQL, $arrBind['bind']);
                        $memberCouponNo = $this->db->insert_id();
                        if ($memberCouponNo < 1) { //중복 쿠폰
                            throw new \Exception(__("이미 발급된 쿠폰입니다."));
                        }
                    } else {
                        $this->db->set_insert_db(DB_MEMBER_COUPON, $arrBind['param'], $arrBind['bind'], 'y');
                        $memberCouponNo = $this->db->insert_id();
                    }
                    $this->setCouponMemberSaveCount($offlineCouponVal['couponNo']);
                    unset($arrData);
                    unset($arrBind);

                    if ($offlineCouponVal['couponAuthType'] == 'n') {
                        // 1개의 인증번호 사용
                    } else {
                        // 회원별로 다른 인증번호 사용
                        // 오프라인 쿠폰 코드 사용됨으로 변경
                        $arrData = [];
                        $arrData['memNo'] = $memNo;
                        $arrData['memberCouponNo'] = $memberCouponNo;
                        $arrData['couponOfflineCodeSaveType'] = 'y';
                        $arrBind = $this->db->get_binding(DBTableField::tableCouponOfflineCode(), $arrData, 'update', array_keys($arrData), ['couponOfflineCode']);
                        $this->db->bind_param_push($arrBind['bind'], $this->fieldTypes['couponOffline']['couponOfflineCode'], $offlineCouponVal['couponOfflineCode']);
                        $this->db->set_update_db(DB_COUPON_OFFLINE_CODE, $arrBind['param'], 'couponOfflineCode = ?', $arrBind['bind'], false);
                        unset($arrData);
                        unset($arrBind);
                    }
                    break; // 중복된 유저코드가 존재 하더라도 1번만 발급되게 break
                } else if ($offlineCouponVal['chkMemberCoupon'] == 'NOT_MEMBER_GROUP') {
                    throw new \Exception('발급 회원등급이 아닙니다.');
                } else if ($offlineCouponVal['chkMemberCoupon'] == 'NO_DUPLICATE_COUPON') {
                    throw new \Exception('이미 발급된 쿠폰입니다.');
                } else if ($offlineCouponVal['chkMemberCoupon'] == 'MAX_DUPLICATE_COUPON') {
                    throw new \Exception('발급 가능 수량을 모두 발급 받았습니다.');
                } else if ($offlineCouponVal['chkMemberCoupon'] == 'SOME_DUPLICATE_COUPON') {
                    throw new \Exception('이미 발급 받으신 해당 쿠폰을 사용 후에 발급이 가능합니다.');
                } else if ($offlineCouponVal['chkMemberCoupon'] == 'NOT_START_DISPLAY_DATE') {
                    throw new \Exception('등록 기간 전입니다. 등록 기간에 등록됩니다.');
                } else if ($offlineCouponVal['chkMemberCoupon'] == 'NOT_END_DISPLAY_DATE') {
                    throw new \Exception('등록 기간이 종료되었습니다.');
                } else if ($offlineCouponVal['chkMemberCoupon'] == 'COUPON_PAUSE') {
                    throw new \Exception('쿠폰이 존재하지 않습니다.');
                }
            }
        } else {
            throw new \Exception('쿠폰이 존재하지 않습니다.');
        }
    }

    /**
     * 회원다운로드 쿠폰의 링크클릭으로 쿠폰 발급 가능 체크
     *
     * @param integer $couponNo   쿠폰고유번호
     * @param integer $memNo      회원고유번호
     * @param integer $memGroupNo 회원등급고유번호
     * @param boolean $return true-return false-exception 반환
     *
     * @return boolean
     * @throws \Exception 발급조건이 안맞을 때
     *
     * @author su
     */
    public function getCouponLinkDownUsable($couponNo, $memNo, $memGroupNo, $return = false)
    {
        if ($memNo > 0) {
            if($this->checkCouponType($couponNo)) {
                $couponData = $this->getCouponInfo($couponNo, '*');
                // 쿠폰 사용중 이고 온라인 쿠폰 이고 다운로드 쿠폰인 것만 가능
                if ($couponData['couponType'] == 'y' && $couponData['couponKind'] == 'online' && $couponData['couponSaveType'] == 'down') {
                    // 회원다운로드 노출기간 체크
                    if ($couponData['couponDisplayType'] == 'y') {
                        if (strtotime($couponData['couponDisplayStartDate']) <= time() && strtotime($couponData['couponDisplayEndDate']) >= time()) {
                            // 정상
                        } else {
                            if($return) return false;
                            throw new \Exception('발급 기간이 지난 쿠폰입니다.');
                        }
                    } else {
                        // 정상
                    }
                    // 발급 회원등급 체크
                    if ($couponData['couponApplyMemberGroup'] && $memGroupNo) {//발급회원등급
                        $applyMemberGroupArr = explode(INT_DIVISION, $couponData['couponApplyMemberGroup']);
                        if (array_search($memGroupNo, $applyMemberGroupArr) !== false) {//로그인한 회원등급 존재
                            // 정상
                        } else {//로그인한 회원등급 존재안함
                            if($return) return false;
                            throw new \Exception('발급 가능 회원등급이 아닙니다.');
                        }
                    }
                    // 발급 수량
                    if ($couponData['couponAmountType'] == 'n') {// 무제한
                        // 정상
                    } else if ($couponData['couponAmountType'] == 'y') {// 제한
                        // 발급 총 수량 제한 체크
                        if ($couponData['couponAmount'] <= $couponData['couponSaveCount']) {// 발급 수량 외로 제외처리
                            if($return) return false;
                            throw new \Exception('쿠폰 발급 개수가 초과되어 발급할 수 없습니다.');
                        } else {
                            // 정상
                        }
                    }
                    $saveMemberCouponCount = $this->getMemberCouponTotalCount($couponData['couponNo'], $memNo); // 회원의 해당 쿠폰 발급 총 갯수
                    $stateMemberCoupon = $this->getMemberCouponState($couponData['couponNo'], $memNo);
                    // 재발급 제한
                    if ($couponData['couponSaveDuplicateType'] == 'n') {// 재발급 안됨
                        if ($saveMemberCouponCount > 0) { // 발급된 내역이 있다면
                            if($return) return false;
                            throw new \Exception('이미 발급 받은 쿠폰 입니다.');
                        } else {
                            // 정상
                        }
                    } else if ($couponData['couponSaveDuplicateType'] == 'y') {// 재발급 가능
                        if ($couponData['couponSaveDuplicateLimitType'] == 'y') {// 재발급 최대 수 여부
                            if ($saveMemberCouponCount > 0) { // 발급된 내역이 있다면
                                if ($couponData['couponSaveDuplicateLimit'] <= $saveMemberCouponCount) {
                                    if($return) return false;
                                    throw new \Exception('쿠폰 발급 횟수가 초과되어 발급할 수 없습니다.');
                                } else {// 재발급 최대 수에 모자람
                                    if ($stateMemberCoupon) {// 해당 쿠폰이 모두 주문사용 사용됨
                                        // 정상
                                    } else {// 해당 쿠폰이 장바구니 사용 또는 사용전
                                        if($return) return false;
                                        throw new \Exception('이미 발급받은 쿠폰입니다. 사용 후 다시 발급할 수 있습니다.');
                                    }
                                }
                            } else {
                                // 정상
                            }
                        } else {// 무조건 재발급
                            if ($stateMemberCoupon) {// 해당 쿠폰이 모두 주문사용 사용됨
                                // 정상
                            } else {// 해당 쿠폰이 장바구니 사용 또는 사용전
                                if($return) return false;
                                throw new \Exception('이미 발급받은 쿠폰입니다. 사용 후 다시 발급할 수 있습니다.');
                            }
                        }
                    }
                } else {
                    if($return) return false;
                    throw new \Exception('삭제된 쿠폰입니다.');
                }
            } else {
                if($return) return false;
                throw new \Exception('발급조건이 맞지 않습니다.');
            }

            return true;
        } else {
            if($return) return false;
            throw new \Exception('로그인 하셔야 합니다.');
        }
    }

    /**
     * 회원다운로드 쿠폰의 링크클릭으로 쿠폰 발급
     *
     * @param integer $couponNo   쿠폰고유번호
     * @param integer $memNo      회원고유번호
     * @param integer $memGroupNo 회원등급고유번호
     *
     * @return boolean
     * @throws \Exception 발급조건이 안맞을 때
     *
     * @author su
     */
    public function setCouponLinkDown($couponNo, $memNo, $memGroupNo)
    {
        $couponLinkDownData = $this->getCouponLinkDownUsable($couponNo, $memNo, $memGroupNo);
        if ($couponLinkDownData) {
            $arrData = [];
            $arrData['couponNo'] = $couponNo;
            $arrData['couponSaveAdminId'] = '회원다운로드쿠폰 링크 다운';
            $arrData['memberCouponStartDate'] = $this->getMemberCouponStartDate($couponNo);
            $arrData['memberCouponEndDate'] = $this->getMemberCouponEndDate($couponNo);
            $arrData['memberCouponState'] = 'y';
            $arrData['memNo'] = $memNo;
            $arrBind = $this->db->get_binding(DBTableField::tableMemberCoupon(), $arrData, 'insert', array_keys($arrData), ['memberCouponNo']);
            $this->db->set_insert_db(DB_MEMBER_COUPON, $arrBind['param'], $arrBind['bind'], 'y');

            unset($arrData);
            unset($arrBind);
            // 쿠폰 발급 카운트 증가
            $this->setCouponMemberSaveCount($couponNo);
        } else {
            throw new \Exception($couponLinkDownData);
        }
    }

    /**
     * 자동 쿠폰 발급
     *
     * @param string  $couponEventType 자동발급 이벤트 코드
     *                                 [첫구매(‘first’),구매감사(‘order’),생일축하(‘birth’),회원가입(‘join’),출석체크(‘attend’)]
     * @param integer $memNo           회원고유번호
     * @param integer $memGroupNo      회원그룹고유번호
     * @param integer $couponNo        쿠폰 고유번호
     * @param string  $eventType       회원정보 이벤트시 사용 (modify : 회원정보 수정 / life : 평생회원)
     * @param string  $firstOrder      첫 주문인지 여부 (y : 첫 주문)
     *
     * @author su
     */
    public function setAutoCouponMemberSave($couponEventType, $memNo, $memGroupNo, $couponNo = null, $eventType = null, $firstOrder = null)
    {
        $logger = \App::getInstance('logger');

        if (empty($memNo)) {
            $logger->info('cannot offer ' . $couponEventType . ' auto coupon because of empty memNo.' . __METHOD__);
            return;
        }

        if ($couponEventType == 'first') {
            $couponSaveAdminId = "첫구매 축하 쿠폰";
        } else if ($couponEventType == 'order') {
            $couponSaveAdminId = "구매 감사 쿠폰";
        } else if ($couponEventType == 'birth') {
            $couponSaveAdminId = gd_isset($this->birthDayCouponYear, date('Y')) . "년 생일 축하 쿠폰";
        } else if ($couponEventType == 'join') {
            $couponSaveAdminId = "회원가입 축하 쿠폰";
        } else if ($couponEventType == 'attend') {
            $couponSaveAdminId = "출석체크 감사 쿠폰";
        } else if ($couponEventType == 'cartRemind') {
            $couponSaveAdminId = "장바구니 알림 쿠폰";
        } else if ($couponEventType == 'plusReview') {
            $couponSaveAdminId = "플러스리뷰 전용 쿠폰";
        } else if ($couponEventType == 'memberModifyEvent') {
            $couponSaveAdminId = "회원정보수정 이벤트 쿠폰";
        } else if ($couponEventType == 'wake') {
            $couponSaveAdminId = "휴면회원 해제 감사 쿠폰";
        } else if ($couponEventType == 'joinEvent') {
            $couponSaveAdminId = "주문 간단 가입 쿠폰";
        }

        if ($couponEventType == 'attend' || $couponEventType == 'cartRemind' || $couponEventType == 'memberModifyEvent' || $couponEventType == 'joinEvent') { // 출석체크, 장바구니 알림, 회원정보수정 이벤트는 해당 설정에서 하나의 쿠폰을 설정하여 지급됨
            $autoCouponArrData = $this->getAutoCouponUsable($couponEventType, $memNo, $memGroupNo, $couponNo);
        } else { // 첫구매, 구매감사, 생일추가, 회원가입, 휴먼회원 해제는 등록되어 사용가능한 해당 이벤트 쿠폰이 모두 지급됨
            $autoCouponArrData = $this->getAutoCouponUsable($couponEventType, $memNo, $memGroupNo, null, $firstOrder);
        }
        $logger->info(sprintf('Auto coupon data is not array. coupon event type[%s], memNo[%s], groupNo[%s]', $couponEventType, $memNo, $memGroupNo));
        if (is_array($autoCouponArrData)) {
            $logger->debug(__METHOD__, $autoCouponArrData);
            if (is_null($this->resultStorage)) {
                $this->resultStorage = new SimpleStorage();
                $this->resultStorage->set('total', count($autoCouponArrData));
                $this->resultStorage->set('success', 0);
            }

            $smsReceivers = [];
            foreach ($autoCouponArrData as $autoCouponKey => $autoCouponVal) {
                $arrData = [];
                $arrData['couponNo'] = $autoCouponVal['couponNo'];
                $arrData['couponSaveAdminId'] = $couponSaveAdminId;
                $arrData['memberCouponStartDate'] = $this->getMemberCouponStartDate($autoCouponVal['couponNo']);
                $arrData['memberCouponEndDate'] = $this->getMemberCouponEndDate($autoCouponVal['couponNo']);
                $arrData['memberCouponState'] = 'y';
                $arrData['memNo'] = $memNo;
                // 생일쿠폰 발급년도
                if ($couponEventType == 'birth' && empty($this->birthDayCouponYear) == false) {
                    $arrData['birthDayCouponYear'] = $this->birthDayCouponYear;
                }
                // 회원정보수정 이벤트 유형 (modify : 회원정보 수정 / life : 평생회원)
                if ($autoCouponVal['couponEventMemberModifySmsType'] === 'y') {
                    $autoCouponVal['couponEventMemberModifyEventType'] = ($eventType === 'modify') ? '회원정보 수정' : '평생회원';
                }
                $arrBind = $this->db->get_binding(DBTableField::tableMemberCoupon(), $arrData, 'insert', array_keys($arrData), ['memberCouponNo']);
                $this->db->set_insert_db(DB_MEMBER_COUPON, $arrBind['param'], $arrBind['bind'], 'y');
                $memCouponNo = $this->db->insert_id();
                if ($memCouponNo > 0) {
                    $this->resultStorage->increase('success');
                    $this->resultStorage->add('benefitSuccessMemNo', $memNo);
                    $smsReceivers[$autoCouponVal['couponNo'] . '_' . $memNo] = [
                        'memNo'                        => $memNo,
                        'couponNo'                     => $autoCouponVal['couponNo'],
                        'couponEventOrderSmsType'      => $autoCouponVal['couponEventOrderSmsType'],
                        'couponEventFirstSmsType'      => $autoCouponVal['couponEventFirstSmsType'],
                        'couponEventBirthSmsType'      => $autoCouponVal['couponEventBirthSmsType'],
                        'couponEventMemberSmsType'     => $autoCouponVal['couponEventMemberSmsType'],
                        'couponEventAttendanceSmsType' => $autoCouponVal['couponEventAttendanceSmsType'],
                        'couponEventMemberModifySmsType' => $autoCouponVal['couponEventMemberModifySmsType'],
                        'couponEventWakeSmsType'       => $autoCouponVal['couponEventWakeSmsType'],
                    ];
                    $this->sendSms($memNo, $autoCouponVal);
                };

                unset($arrData);
                unset($arrBind);
                // 쿠폰 발급 카운트 증가
                $this->setCouponMemberSaveCount($autoCouponVal['couponNo']);
            }
            $this->resultStorage->set('smsReceivers', $smsReceivers);
        }
    }

    /**
     * 쿠폰 지급 시 발송 될 SMS 내역 추가
     *
     * @param       $memNo
     * @param array $autoCoupon
     */
    protected function sendSms($memNo, array $autoCoupon)
    {
        $logger = \App::getInstance('logger');
        $member = ['smsFl' => 'n'];
        if ($memNo > 1) {
            $member = \Component\Member\MemberDAO::getInstance()->selectMemberByOne($memNo);
        } else {
            $logger->info('Send coupon auto sms. not found member number.');
        }
        $smsAuto = \App::load(\Component\Sms\SmsAuto::class);
        ArrayUtils::unsetDiff($autoCoupon, explode(',', 'couponEventOrderSmsType,couponEventFirstSmsType,couponEventBirthSmsType,couponEventMemberSmsType,couponEventAttendanceSmsType,couponEventMemberModifySmsType,couponEventWakeSmsType,couponEventMemberModifyEventType'));
        foreach ($autoCoupon as $index => $item) {
            if ($item == 'y') {
                if ($member['smsFl'] == 'y' && $index != 'couponEventBirthSmsType') {   //생일축하쿠폰Sms 는 스케줄러에서 발송함
                    switch ($index) {
                        case 'couponEventOrderSmsType':
                            $smsAuto->setSmsAutoCodeType(Code::COUPON_ORDER);
                            break;
                        case 'couponEventFirstSmsType':
                            $smsAuto->setSmsAutoCodeType(Code::COUPON_ORDER_FIRST);
                            break;
                        case 'couponEventMemberSmsType':
                            $smsAuto->setSmsAutoCodeType(Code::COUPON_JOIN);
                            break;
                        case 'couponEventAttendanceSmsType':
                            $smsAuto->setSmsAutoCodeType(Code::COUPON_LOGIN);
                            break;
                        case 'couponEventMemberModifySmsType':
                            $smsAuto->setSmsAutoCodeType(Code::COUPON_MEMBER_MODIFY);
                            break;
                        case 'couponEventWakeSmsType':
                            $smsAuto->setSmsAutoCodeType(Code::COUPON_WAKE);
                            break;
                        default:
                            $logger->info(sprintf('Not found coupon sms smsAutoCodeType. couponEventSmsType[%s]', $index));
                            break;
                    }
                    $smsAuto->setSmsType(SmsAutoCode::PROMOTION);
                    $smsAuto->setReceiver($member);

                    // 회원정보수정 이벤트 추가
                    if ($autoCoupon['couponEventMemberModifySmsType'] === 'y') {
                        $smsAuto->setReplaceArguments(['eventType' => $autoCoupon['couponEventMemberModifyEventType']]);
                    }

                    $smsAuto->autoSend();
                } else {
                    $logger->info(sprintf('Disallow sms receiving. memNo[%s], smsFl [%s]', $member['memNo'], $member['smsFl']));
                }
            }
        }
    }

    /**
     * 해당 쿠폰의 발급 카운트 증가 - 쿠폰 발급 될때 넣어줘야 함
     *
     * @param integer $couponNo 쿠폰고유번호
     *
     * @author su
     */
    public function setCouponMemberSaveCount($couponNo)
    {
        $arrBind = [];
        $this->db->bind_param_push($arrBind, $this->fieldTypes['coupon']['couponNo'], $couponNo);
        $this->db->set_update_db_query(DB_COUPON, 'couponSaveCount = couponSaveCount+1', 'couponNo = ?', $arrBind);
        unset($arrBind);
        $this->checkCouponType($couponNo); // 발급 수 증가로 발급종료 상태로 변경 될 수 있어 실행
    }

    /**
     * 해당 쿠폰의 발급 카운트 일괄 증가 - 쿠폰 발급 될때 넣어줘야 함
     *
     * @param integer $couponNo 쿠폰고유번호
     * @param integer $iCount 발급 카운트
     *
     * @author su
     */
    public function setCouponMemberSaveCountAll($couponNo, $iCount)
    {
        $arrBind = [];
        $this->db->bind_param_push($arrBind, 'i', $iCount);
        $this->db->bind_param_push($arrBind, $this->fieldTypes['coupon']['couponNo'], $couponNo);
        $this->db->set_update_db_query(DB_COUPON, 'couponSaveCount = couponSaveCount+?', 'couponNo = ?', $arrBind);
        unset($arrBind);
        $this->checkCouponType($couponNo); // 발급 수 증가로 발급종료 상태로 변경 될 수 있어 실행
    }

    /**
     * 해당 쿠폰의 발급 카운트 감소 - 발급된 쿠폰 삭제 될때 넣어줘야 함
     *
     * @param integer $couponNo 쿠폰고유번호
     *
     * @author su
     */
    public function setCouponMemberDeleteCount($couponNo)
    {
        $arrBind = [];
        $this->db->bind_param_push($arrBind, $this->fieldTypes['coupon']['couponNo'], $couponNo);
        $this->db->set_update_db_query(DB_COUPON, 'couponSaveCount = couponSaveCount-1', 'couponNo = ?', $arrBind);
        unset($arrBind);
    }


    /**
     * 회원 쿠폰 검색 - 마이페이지
     *
     * @author su
     */
    public function setMemberCouponSearch()
    {
        $getValue = Request::get()->toArray();

        $startDate = ($getValue['pageType'] == 'pc') ? date('Y-m-d', strtotime('-6 days')) : date('Y-m-d', strtotime('-90 days'));
        $endDate = date('Y-m-d');
        $this->search['wDate'] = gd_isset(
            $getValue['wDate'], [
                $startDate,
                $endDate,
            ]
        );
        $this->search['memberCouponState'] = gd_isset($getValue['memberCouponState'], 'y');
        $this->search['couponEventType'] = gd_isset($getValue['couponEventType']);
        // 발급일 기준에 따른 검색
        if ($this->search['wDate'][0] && $this->search['wDate'][1]) {
            $this->arrWhere[] = 'mc.regDt >= ? AND mc.regDt <= ?';
            $this->db->bind_param_push($this->arrBind, 's', $this->search['wDate'][0] . ' 00:00:00');
            $this->db->bind_param_push($this->arrBind, 's', $this->search['wDate'][1] . ' 23:59:59');
        } else if ($this->search['wDate'][0] && !$this->search['wDate'][1]) {
            $this->arrWhere[] = 'mc.regDt >= ?';
            $this->db->bind_param_push($this->arrBind, 's', $this->search['wDate'][0] . ' 00:00:00');
        } else if (!$this->search['wDate'][0] && $this->search['wDate'][1]) {
            $this->arrWhere[] = 'mc.regDt <= ?';
            $this->db->bind_param_push($this->arrBind, 's', $this->search['wDate'][1] . ' 23:59:59');
        }
        // 회원쿠폰 상태에 따른 검색
        if ($this->search['memberCouponState'] == 'y') {
            if ($getValue['pageType'] == 'mobile') {
                $this->arrWhere[] = "c.couponDeviceType IN (?,?)";
                $this->db->bind_param_push($this->arrBind, 's', 'all');
                $this->db->bind_param_push($this->arrBind, 's', 'mobile');
            }
            $this->arrWhere[] = 'mc.memberCouponStartDate <= NOW() AND mc.memberCouponEndDate >= NOW()';

            $this->arrWhere[] = 'mc.memberCouponState IN (?, \'cart\')';
            $this->db->bind_param_push($this->arrBind, $this->fieldTypes['memberCoupon']['memberCouponState'], 'y');
        } else if ($this->search['memberCouponState'] == 'n') {
            // 사용한 쿠폰이거나 사용기간이 맞지 않는 쿠폰
            if ($getValue['pageType'] == 'mobile') {
                $this->arrWhere[] = "c.couponDeviceType IN (?,?)";
                $this->db->bind_param_push($this->arrBind, 's', 'all');
                $this->db->bind_param_push($this->arrBind, 's', 'mobile');
            }
            $this->arrWhere[] = '((mc.memberCouponState != ? AND mc.memberCouponState != ?) OR mc.memberCouponStartDate > NOW() OR mc.memberCouponEndDate < NOW())';

            $this->db->bind_param_push($this->arrBind, $this->fieldTypes['memberCoupon']['memberCouponState'], 'y');
            $this->db->bind_param_push($this->arrBind, 's', 'cart');
        }
        if($this->search['couponEventType']) {
            $this->arrWhere[] = 'c.couponEventType = ?';
            $this->db->bind_param_push($this->arrBind, $this->fieldTypes['coupon']['couponEventType'], $this->search['couponEventType']);
        }
        $this->selected['memberCouponState'][$this->search['memberCouponState']] = "selected='selected'";

        if (empty($this->arrBind)) {
            $this->arrBind = null;
        }
    }

    /**
     * 회원쿠폰 테이블에서 특정일 남은 쿠폰정보
     *
     * @param int $iLimitDay    쿠폰 만료 몇일전 설정 (기본 7)
     *
     * @return array 쿠폰 정보
     *
     */
    public function getWarningPeriodLImitCouponInfo($iLimitDay = 7)
    {
        $checkDay = date('Y-m-d', strtotime('+ ' . $iLimitDay . ' day'));

        $arrWhere[] = 'mc.memberCouponState = "y"';
        $arrWhere[] = "cast(mc.memberCouponEndDate as date) = ?";
        $this->db->bind_param_push($arrBind, 's', $checkDay);
        $arrWhere[] = "mc.memberCouponUseDate = ?";
        $this->db->bind_param_push($arrBind, 's', '0000-00-00 00:00:00');
        $this->db->strWhere = implode(' AND ', gd_isset($arrWhere));
        $this->db->strField = 'c.couponNm, mc.*';

        $query = $this->db->query_complete();
        $strSQL = 'SELECT ' . array_shift($query) . ' FROM ' . DB_MEMBER_COUPON . ' as mc JOIN ' . DB_COUPON . ' as c ON (mc.couponNo = c.couponNo AND c.couponLimitSmsFl = \'y\')' . implode(' ', $query);
        $getData = $this->db->query_fetch($strSQL, $arrBind);

        return gd_htmlspecialchars_stripslashes($getData);
    }

    /**
     * 컴백쿠폰 정보 출력
     *
     * 완성된 쿼리문은 $db->strField , $db->strJoin , $db->strWhere , $db->strGroup , $db->strOrder , $db->strLimit 멤버 변수를
     * 이용할수 있습니다.
     *
     * @param string      $sno    쿠폰 고유 번호 (기본 null)
     * @param string      $couponField 출력할 필드명 (기본 null)
     * @param array       $arrBind     bind 처리 배열 (기본 null)
     * @param bool|string $dataArray   return 값을 배열처리 (기본값 false)
     *
     * @return array 쿠폰 정보
     *
     * @author su
     */
    public function getComebackCouponInfo($sno = null, $couponField = null, $arrBind = null, $dataArray = false)
    {
        if (is_null($arrBind)) {
            // $arrBind = array();
        }
        if ($sno) {
            if ($this->db->strWhere) {
                $this->db->strWhere = " c.sno = ? AND " . $this->db->strWhere;
            } else {
                $this->db->strWhere = " c.sno = ?";
            }
            $this->db->bind_param_push($arrBind, 'i', $sno);
        }
        if ($couponField) {
            if ($this->db->strField) {
                $this->db->strField = $couponField . ', ' . $this->db->strField;
            } else {
                $this->db->strField = $couponField;
            }
        }
        $query = $this->db->query_complete();
        $strSQL = 'SELECT ' . array_shift($query) . ' FROM ' . DB_COMEBACK_COUPON . ' as c ' . implode(' ', $query);
        $getData = $this->db->query_fetch($strSQL, $arrBind);

        if (count($getData) == 1 && $dataArray === false) {
            return gd_htmlspecialchars_stripslashes($getData[0]);
        }

        return gd_htmlspecialchars_stripslashes($getData);
    }

    /**
     * 상품의 해당 쿠폰 리스트용 전체 쿠폰 리스트 추출
     *
     * @param null    $couponDeviceType ( pc , mobile , null)
     *
     * @return array $couponListData 다운가능한 쿠폰의 리스트
     *
     * @author su
     */
    public function getGoodsCouponDownListAll($appendWhere = null,$couponDeviceType = null)
    {
        $arrBind = [];
        $arrWhere = [];

        // 온라인 쿠폰
        $arrWhere[] = 'c.couponKind=?';
        $this->db->bind_param_push($arrBind, $this->fieldTypes['coupon']['couponKind'], 'online');
        // 발급여부 쿠폰(발급중)
        $arrWhere[] = 'c.couponType=?';
        $this->db->bind_param_push($arrBind, $this->fieldTypes['coupon']['couponType'], 'y');
        // 상품 쿠폰
        $arrWhere[] = 'c.couponUseType=?';
        $this->db->bind_param_push($arrBind, $this->fieldTypes['coupon']['couponUseType'], 'product');
        // 회원다운로드 쿠폰
        $arrWhere[] = 'c.couponSaveType=?';
        $this->db->bind_param_push($arrBind, $this->fieldTypes['coupon']['couponSaveType'], 'down');
        // 할인 쿠폰, 적립 쿠폰
        $arrWhere[] = '(c.couponKindType in (?,?))';
        $this->db->bind_param_push($arrBind, $this->fieldTypes['coupon']['couponKindType'], 'sale');
        $this->db->bind_param_push($arrBind, $this->fieldTypes['coupon']['couponKindType'], 'add');
        // 사용범위?PC+모바일(‘a’),PC(‘p’),모바일(‘m’)
        $arrWhere[] = '(c.couponDeviceType in (?,?))';

        if ($couponDeviceType) {
            if ($couponDeviceType == 'pc') {
                $this->db->bind_param_push($arrBind, $this->fieldTypes['coupon']['couponDeviceType'], 'all');
                $this->db->bind_param_push($arrBind, $this->fieldTypes['coupon']['couponDeviceType'], 'pc');
            } else if ($couponDeviceType == 'mobile') {
                $this->db->bind_param_push($arrBind, $this->fieldTypes['coupon']['couponDeviceType'], 'all');
                $this->db->bind_param_push($arrBind, $this->fieldTypes['coupon']['couponDeviceType'], 'mobile');
            }
        } else {
            if (Request::isMobile()) { // 모바일 접속 여부
                $this->db->bind_param_push($arrBind, $this->fieldTypes['coupon']['couponDeviceType'], 'all');
                $this->db->bind_param_push($arrBind, $this->fieldTypes['coupon']['couponDeviceType'], 'mobile');
            } else {
                $this->db->bind_param_push($arrBind, $this->fieldTypes['coupon']['couponDeviceType'], 'all');
                $this->db->bind_param_push($arrBind, $this->fieldTypes['coupon']['couponDeviceType'], 'pc');
            }
        }

        if ($appendWhere !== null)  $arrWhere = array_merge($arrWhere,$appendWhere);

        // 기본 where 로 쿠폰 정보 가져오기
        $this->db->strField = "c.*";
        $this->db->strWhere = implode(' AND ', gd_isset($arrWhere));
        $this->db->strOrder = 'c.regDt asc';

        $query = $this->db->query_complete();
        $strSQL = 'SELECT ' . array_shift($query) . ' FROM ' . DB_COUPON . ' as c ' . implode(' ', $query);
        $couponListData = $this->db->query_fetch($strSQL, $arrBind);

        return $couponListData;
    }

    /**
     * 상품의 해당 쿠폰 각 상품별 가격 추출
     *
     * @param null    $goodsData ( pc , mobile , null)
     * @param null    $goodsData ( pc ,$couponListData)
     * @return array $couponListData 다운가능한 쿠폰의 리스트
     *
     * @author su
     */
    public function getGoodsCouponDownListPrice($goodsData,$couponListData,$memNo = null, $memGroupNo = null) {
        // 제외 $key
        $removeKey = [];
        foreach ($couponListData as $key => $val) {
            // 노출 기간
            if ($val['couponDisplayType'] == 'n') {// 즉시 노출
                if ($val['couponUsePeriodType'] == 'period') {
                    if ($val['couponUsePeriodStartDate'] <= date('Y-m-d H:i:s', time()) && $val['couponUsePeriodEndDate'] >= date('Y-m-d H:i:s', time())) {
                        $couponListData[$key]['chkMemberCoupon'] = 'YES';
                    } else {
                        $removeKey[] = $key;
                        continue;
                    }
                }
                if ($val['couponUsePeriodType'] == 'day') {
                    if (empty($val['couponUseDateLimit']) || $val['couponUseDateLimit'] == '0000-00-00 00:00:00' || $val['couponUseDateLimit'] >= date('Y-m-d H:i:s', time())) {
                        $couponListData[$key]['chkMemberCoupon'] = 'YES';
                    } else {
                        $removeKey[] = $key;
                        continue;
                    }
                }
            } else if ($val['couponDisplayType'] == 'y') {
                if ($val['couponDisplayStartDate'] <= date('Y-m-d H:i:s', time()) && $val['couponDisplayEndDate'] >= date('Y-m-d H:i:s', time())) {// 설정 기간내 노출
                    $couponListData[$key]['chkMemberCoupon'] = 'YES';
                } else {// 설정 기간 외로 제외처리
                    $removeKey[] = $key;
                    continue;
                }
            }
            // 발급 수량
            if ($val['couponAmountType'] == 'n') {// 무제한
                $couponListData[$key]['chkMemberCoupon'] = 'YES';
            } else if ($val['couponAmountType'] == 'y') {// 제한
                // 발급 총 수량 제한 체크
                if ($val['couponAmount'] <= $val['couponSaveCount']) {// 발급 수량 외로 제외처리
                    $removeKey[] = $key;
                    continue;
                } else {
                    $couponListData[$key]['chkMemberCoupon'] = 'YES';
                }
            }
            // 발급 제외 공급사
            if ($val['couponExceptProviderType'] == 'y') {
                $exceptProviderGroupArr = explode(INT_DIVISION, $val['couponExceptProvider']);
                if (array_search($goodsData['scmNo'], $exceptProviderGroupArr) !== false) {//공급사 존재
                    $removeKey[] = $key;
                    continue;
                } else {//공급사 존재안함
                    $couponListData[$key]['chkMemberCoupon'] = 'YES';
                }
            }
            // 발급 제외 카테고리
            if ($val['couponExceptCategoryType'] == 'y') {
                $exceptCategoryGroupArr = explode(INT_DIVISION, $val['couponExceptCategory']);
                $matchCateData = 0;
                foreach ($goodsData['cateCdArr'] as $cateKey => $cateVal) {
                    if (array_search($cateVal, $exceptCategoryGroupArr) !== false) {//카테고리 존재
                        $matchCateData++;
                    } else {//카테고리 존재안함
                        $couponListData[$key]['chkMemberCoupon'] = 'YES';
                    }
                }
                if ($matchCateData > 0) {// 상품의 적용된 카테고리와 쿠폰 허용 카테고리 존재
                    $removeKey[] = $key;
                    continue;
                } else {// 상품의 적용된 카테고리와 쿠폰 허용 카테고리 존재안함
                    $couponListData[$key]['chkMemberCoupon'] = 'YES';
                }
            }
            // 발급 제외 브랜드
            if ($val['couponExceptBrandType'] == 'y') {
                $exceptBrandGroupArr = explode(INT_DIVISION, $val['couponExceptBrand']);
                if (array_search($goodsData['brandCd'], $exceptBrandGroupArr) !== false) {//브랜드 존재
                    $removeKey[] = $key;
                    continue;
                } else {//브랜드 존재안함
                    $couponListData[$key]['chkMemberCoupon'] = 'YES';
                }
            }
            // 발급 제외 상품
            if ($val['couponExceptGoodsType'] == 'y') {
                $exceptGoodsGroupArr = explode(INT_DIVISION, $val['couponExceptGoods']);
                if (array_search($goodsData['goodsNo'], $exceptGoodsGroupArr) !== false) {//상품 존재
                    $removeKey[] = $key;
                    continue;
                } else {//상품 존재안함
                    $couponListData[$key]['chkMemberCoupon'] = 'YES';
                }
            }
            // 발급 허용 체크
            if ($val['couponApplyProductType'] == 'all') {//전체 발급

            } else if ($val['couponApplyProductType'] == 'provider') {//공급사 발급
                $applyProviderGroupArr = explode(INT_DIVISION, $val['couponApplyProvider']);
                if (array_search($goodsData['scmNo'], $applyProviderGroupArr) !== false) {//공급사 존재
                    $couponListData[$key]['chkMemberCoupon'] = 'YES';
                } else {//공급사 존재안함
                    $removeKey[] = $key;
                    continue;
                }
            } else if ($val['couponApplyProductType'] == 'category') {//카테고리 발급
                $applyCategoryGroupArr = explode(INT_DIVISION, $val['couponApplyCategory']);
                $matchCateData = 0;
                foreach ($goodsData['cateCdArr'] as $cateKey => $cateVal) {
                    if (array_search($cateVal, $applyCategoryGroupArr) !== false) {//카테고리 존재
                        $matchCateData++;
                    } else {//카테고리 존재안함
                        $couponListData[$key]['chkMemberCoupon'] = 'YES';
                    }
                }
                if ($matchCateData > 0) {// 상품의 적용된 카테고리와 쿠폰 허용 카테고리 존재
                    $couponListData[$key]['chkMemberCoupon'] = 'YES';
                } else {// 상품의 적용된 카테고리와 쿠폰 허용 카테고리 존재안함
                    $removeKey[] = $key;
                    continue;
                }
            } else if ($val['couponApplyProductType'] == 'brand') {//브랜드 발급
                $applyBrandGroupArr = explode(INT_DIVISION, $val['couponApplyBrand']);
                if (array_search($goodsData['brandCd'], $applyBrandGroupArr) !== false) {//브랜드 존재
                    $couponListData[$key]['chkMemberCoupon'] = 'YES';
                } else {//브랜드 존재안함
                    $removeKey[] = $key;
                    continue;
                }
            } else if ($val['couponApplyProductType'] == 'goods') {//상품 발급
                $applyGoodsGroupArr = explode(INT_DIVISION, $val['couponApplyGoods']);
                if (array_search($goodsData['goodsNo'], $applyGoodsGroupArr) !== false) {//상품 존재
                    $couponListData[$key]['chkMemberCoupon'] = 'YES';
                } else {//상품 존재안함
                    $removeKey[] = $key;
                    continue;
                }
            }
            if ($memNo) {
                // 발급 회원등급 체크
                if ($val['couponApplyMemberGroup'] && $memGroupNo) {//발급회원등급이 있다면
                    $applyMemberGroupArr = explode(INT_DIVISION, $val['couponApplyMemberGroup']);
                    $chkMemberCoupon = array_search($memGroupNo, $applyMemberGroupArr);
                    if ($val['couponApplyMemberGroupDisplayType'] == 'y') {//해당 등급만 노출
                        if ($chkMemberCoupon !== false) {//로그인한 회원등급 존재
                            $couponListData[$key]['chkMemberCoupon'] = 'YES';
                        } else {//로그인한 회원등급 존재안함
                            $removeKey[] = $key;
                            continue;
                        }
                    } else {//전체 노출
                        if ($chkMemberCoupon !== false) {//로그인한 회원등급 존재
                            $couponListData[$key]['chkMemberCoupon'] = 'YES';
                        } else {//로그인한 회원등급 존재안함
                            $couponListData[$key]['chkMemberCoupon'] = 'NO_MEMBER_GROUP';
                            continue;
                        }
                    }
                }
                $saveMemberCouponCount = $this->getMemberCouponTotalCount($val['couponNo'], $memNo); // 회원의 해당 쿠폰 발급 총 갯수
                $stateMemberCoupon = $this->getMemberCouponState($val['couponNo'], $memNo);
                // 재발급 제한
                if ($val['couponSaveDuplicateType'] == 'n') {// 재발급 안됨
                    if ($saveMemberCouponCount > 0) { // 발급된 내역이 있다면
                        $couponListData[$key]['chkMemberCoupon'] = 'DUPLICATE_COUPON';
                        continue;
                    } else {
                        $couponListData[$key]['chkMemberCoupon'] = 'YES';
                    }
                } else if ($val['couponSaveDuplicateType'] == 'y') {// 재발급 가능
                    if ($val['couponSaveDuplicateLimitType'] == 'y') {// 재발급 최대 수 여부
                        if ($saveMemberCouponCount > 0) { // 발급된 내역이 있다면
                            if ($val['couponSaveDuplicateLimit'] <= $saveMemberCouponCount) {
                                $couponListData[$key]['chkMemberCoupon'] = 'DUPLICATE_COUPON';
                                continue;
                            } else {// 재발급 최대 수에 모자람
                                if ($stateMemberCoupon) {// 해당 쿠폰이 모두 주문사용 사용됨
                                    $couponListData[$key]['chkMemberCoupon'] = 'YES';
                                } else {// 해당 쿠폰이 장바구니 사용 또는 사용전
                                    $couponListData[$key]['chkMemberCoupon'] = 'DUPLICATE_COUPON';
                                    continue;
                                }
                            }
                        } else {
                            $couponListData[$key]['chkMemberCoupon'] = 'YES';
                        }
                    } else {// 무조건 재발급
                        if ($stateMemberCoupon) {// 해당 쿠폰이 모두 주문사용 사용됨
                            $couponListData[$key]['chkMemberCoupon'] = 'YES';
                        } else {// 해당 쿠폰이 장바구니 사용 또는 사용전
                            $couponListData[$key]['chkMemberCoupon'] = 'DUPLICATE_COUPON';
                            continue;
                        }
                    }
                }
            }
        }
        // 조건 필터링
        foreach ($removeKey as $val) {
            unset($couponListData[$val]);
        }
        unset($removeKey);

        return $this->getGoodsCouponDisplaySalePrice($couponListData,$goodsData['goodsPrice']);
    }

    //나 자신을 제외한 cart 인 상품을 제외상품으로 지정
    public function getOrderWriteExcludeMemberCouponNo($memberCouponNo, $memNo)
    {
        $arrWhere[] = 'memberCouponState = ?';
        $this->db->bind_param_push($arrBind, $this->fieldTypes['memberCoupon']['memberCouponState'], 'cart');

        $arrWhere[] = 'memberCouponNo != ?';
        $this->db->bind_param_push($arrBind, $this->fieldTypes['memberCoupon']['memberCouponNo'], $memberCouponNo);

        $arrWhere[] = 'memNo = ?';
        $this->db->bind_param_push($arrBind, $this->fieldTypes['memberCoupon']['memNo'], $memNo);

        // 기본 where 로 쿠폰 정보 가져오기
        $this->db->strField = "memberCouponNo";
        $this->db->strWhere = implode(' AND ', gd_isset($arrWhere));

        $query = $this->db->query_complete();
        $strSQL = 'SELECT ' . array_shift($query) . ' FROM ' . DB_MEMBER_COUPON . implode(' ', $query);
        $excludeCouponDataArray = $this->db->query_fetch($strSQL, $arrBind);
        $excludeCouponData = array_column($excludeCouponDataArray, 'memberCouponNo');

        return $excludeCouponData;
    }

    /**
     * storage
     *
     * @return \Bundle\Component\Storage\FtpStorage|\Bundle\Component\Storage\LocalStorage
     */
    protected function storage()
    {
        if ($this->storage == null) {
            $this->storage = Storage::disk(Storage::PATH_CODE_COUPON_IMAGE);
        }

        return $this->storage;
    }

    /**
     * 상품쿠폰이 주문서 수정이 허용일 때 제한 사항 검증 - product 주문허용
     *
     * @param array    $memberCouponNo 쿠폰번호
     * @return int      $goodsPrice 가격
     *
     * @author tomi
     */
    public function getProductCouponUsableCheck($memberCouponNo, $dataCartSno, $cartInfo, $cartGoodsPrice)
    {
        if($dataCartSno) {
            $memberCouponArrNo = explode(INT_DIVISION, $memberCouponNo);
            $memberCouponUsable = true;
            // 카트 데이터 호출 후 상품정보 가져오기(Sno에 해당되는 상품 것만)
            $cart = \App::load('\\Component\\Cart\\Cart');
            $cartData = $cart->getCartGoodsData($dataCartSno);
            $scmCartInfo = array_shift($cartData);
            $goodsCartInfo = array_shift($scmCartInfo)[0];

            foreach ($memberCouponArrNo as $val) {
                $memberCouponState = $this->getMemberCouponInfo($val, 'mc.memberCouponState,mc.memberCouponStartDate,mc.memberCouponEndDate,c.couponMinOrderPrice,c.couponProductMinOrderType,c.couponKindType');

                if ($memberCouponState['memberCouponState'] == 'y' || $memberCouponState['memberCouponState'] == 'cart') {
                    // 쿠폰 사용 시작일
                    if (strtotime($memberCouponState['memberCouponStartDate']) > 0) {
                        if (strtotime($memberCouponState['memberCouponStartDate']) > time()) { // 쿠폰 사용 시작일이 지금 시간 보다 크다면 제한
                            $usable = 'EXPIRATION_START_PERIOD';
                        } else {
                            $usable = 'YES';
                        }
                        // 쿠폰 사용 만료일
                    } else if (strtotime($memberCouponState['memberCouponEndDate']) > 0) {
                        if (strtotime($memberCouponState['memberCouponEndDate']) < time()) { // 쿠폰 사용 만료일이 지금 시간 보다 작다면 제한
                            $usable = 'EXPIRATION_END_PERIOD';
                        } else {
                            $usable = 'YES';
                        }
                    } else {
                        $usable = 'YES';
                    }
                    // 상품쿠폰 주문에서 적용 시
                    $thisCallController = \App::getInstance('ControllerNameResolver')->getControllerName();
                    if (($this->couponConfig['productCouponChangeLimitType'] == 'n') && ($thisCallController == 'Controller\Front\Order\CartPsController' || $thisCallController == 'Controller\Mobile\Order\CartPsController')) {
                        // 최소 상품구매금액 제한
                        if($memberCouponState['couponProductMinOrderType'] == 'order') {
                            // Sno상관없이 주문 시 생성된 cart 상품가격 정보 가져오기
                            // 카트 데이터 호출 후 상품정보 가져오기(Sno에 해당되는 상품 것만)
                            $goodsCouponForTotalPrice = $cart->getProductCouponGoodsAllPrice($cartInfo, $memberCouponNo, 'front', '2');
                            $goodsCartInfo['price'] = $goodsCouponForTotalPrice;
                        }
                        // 할인/적립 기준금액
                        $totalGoodsPrice = $goodsCartInfo['price']['goodsPriceSum'];
                        if ($this->couponConfig['couponOptPriceType'] == 'y') {
                            $totalGoodsPrice += $goodsCartInfo['price']['optionPriceSum'];
                        }
                        if ($this->couponConfig['couponTextPriceType'] == 'y') {
                            $totalGoodsPrice += $goodsCartInfo['price']['optionTextPriceSum'];
                        }
                        if ($this->couponConfig['couponAddPriceType'] == 'y') {
                            $totalGoodsPrice += $goodsCartInfo['price']['addGoodsPriceSum'];
                        }
                        // 금액 체크
                        if ($memberCouponState['couponMinOrderPrice'] > $totalGoodsPrice) {
                            $usable = 'NO';
                        } else {
                            $usable = 'YES';
                        }
                        /* 타임 세일 관련 */
                        if (gd_is_plus_shop(PLUSSHOP_CODE_TIMESALE) === true) {
                            if($goodsCartInfo[0]['timeSaleFl'] == true) {
                                $strScmSQL = 'SELECT ts.couponFl as timeSaleCouponFl,ts.sno as timeSaleSno,ts.goodsNo as goodsNo FROM ' . DB_TIME_SALE . ' as ts WHERE FIND_IN_SET(' . $goodsCartInfo[0]['goodsNo'] . ', REPLACE(ts.goodsNo,"' . INT_DIVISION . '",",")) AND UNIX_TIMESTAMP(ts.startDt) < UNIX_TIMESTAMP() AND  UNIX_TIMESTAMP(ts.endDt) > UNIX_TIMESTAMP() AND ts.pcDisplayFl="y"';
                                $tmpScmData = $this->db->query_fetch($strScmSQL, null, false);
                                if ($tmpScmData) {
                                    if ($tmpScmData['timeSaleCouponFl'] == 'n') {
                                        $usable = 'NO';
                                    }
                                }
                                unset($tmpScmData);
                                unset($strScmSQL);
                            }
                        }
                    }
                    if($memberCouponState['couponKindType'] == 'add') {
                        $mileageGive = gd_policy('member.mileageGive');
                        if($mileageGive['giveFl'] == 'n') {
                            $usable = 'NO';
                        }
                    }
                } else {
                    $usable = 'NO';
                }
                if ($usable == 'YES' && $memberCouponUsable) {
                    $memberCouponUsable = true;
                } else {
                    $memberCouponUsable = false;
                }
            }
            return $memberCouponUsable;
        }
    }

    /**
     * 상품쿠폰이 주문서 수정이 제한안함일 때 데이터 불러오기
     *
     * @param array    $cartData
     * @return array $goodsCouponData 다운가능한 쿠폰의 리스트
     *
     * @author tomi
     */
    public function getProductCouponChangeData($mode ='layer', $cartData, $cartPrice)
    {
        $goodsCouponData = $goodsCartInfo = $cartCouponNoArr = $goodsCouponSnoArr = $goodsCouponArrData = $convertGoodsCouponArrData = $convertGoodsCouponPriceArrData = $goodsMemberCouponNoArr = []; // 초기화
        if($mode != 'layer') {
            $goodsCartInfo = $cartData;
        }

        $member = \App::Load(\Component\Member\Member::class);
        $memInfo = $member->getMemberInfo();
        if (empty($memInfo) === false && $memInfo['settleGb'] != 'all') {
            $memInfo['settleGb'] = GroupUtil::matchSettleGbDataToString($memInfo['settleGb']);
        }


        if($mode == 'layer') { // Front 주문 쿠폰 레이어
            foreach($cartData as $scmCartInfoKey => $scmCartInfo ) {
                foreach ($scmCartInfo as $scmKey => $goodsCartInfo) { // scm
                    foreach ($goodsCartInfo as $goodsKey => $goodsVal) { // goods
                        // 혜택제외 체크 (쿠폰)
                        $exceptBenefit = explode(STR_DIVISION, $goodsVal['exceptBenefit']);
                        $exceptBenefitGroupInfo = explode(INT_DIVISION, $goodsVal['exceptBenefitGroupInfo']);
                        if($goodsVal['exceptBenefitGroup'] == 'all' && in_array('coupon', $exceptBenefit) == true) continue;
                        if(($goodsVal['exceptBenefitGroup'] == 'group' && in_array('coupon', $exceptBenefit) == true && in_array($memInfo['groupSno'], $exceptBenefitGroupInfo) === true)) continue;

                        /* 타임 세일 관련 */
                        if (gd_is_plus_shop(PLUSSHOP_CODE_TIMESALE) === true) {
                            if ($goodsVal['timeSaleFl'] == 'y') {
                                $strScmSQL = 'SELECT ts.couponFl as timeSaleCouponFl,ts.sno as timeSaleSno,ts.goodsNo as goodsNo FROM ' . DB_TIME_SALE . ' as ts WHERE FIND_IN_SET(' . $goodsVal['goodsNo'] . ', REPLACE(ts.goodsNo,"' . INT_DIVISION . '",",")) AND UNIX_TIMESTAMP(ts.startDt) < UNIX_TIMESTAMP() AND  UNIX_TIMESTAMP(ts.endDt) > UNIX_TIMESTAMP() AND ts.pcDisplayFl="y"';
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


                        $cartCouponNoArr[$goodsVal['goodsNo']][$goodsVal['optionSno']] = explode(INT_DIVISION, $goodsVal['memberCouponNo']);
                        $cartCouponNoArrNull = null;
                        // 카트 일련번호
                        $goodsCouponSnoArr[$goodsVal['goodsNo']][$goodsVal['optionSno']] = $goodsVal['sno']; // sno 삽입 - goodsCouponSnoArr
                        $goodsCouponArrData[$goodsVal['goodsNo']] = $this->getGoodsMemberCouponList($goodsVal['goodsNo'], Session::get('member.memNo'), Session::get('member.groupSno'), null, $cartCouponNoArrNull, 'order');
                        if ($goodsCouponArrData[$goodsVal['goodsNo']]) {
                            $memberCouponNoArr = array_column($goodsCouponArrData[$goodsVal['goodsNo']], 'memberCouponNo');
                            if ($memberCouponNoArr) {
                                $memberCouponNoString = implode(INT_DIVISION, $memberCouponNoArr);
                                // 해당 상품의 사용가능한 회원쿠폰 리스트를 보기용으로 변환
                                $convertGoodsCouponArrData[$goodsVal['goodsNo']] = $this->convertCouponArrData($goodsCouponArrData[$goodsVal['goodsNo']]);
                                // 해당 상품의 사용가능한 회원쿠폰의 정율도 정액으로 계산된 금액
                                $getGoodsCouponPrice = $this->getMemberCouponPrice($goodsVal['price'], $memberCouponNoString, $cartPrice, 'orderGoodsCoupon');
                                $getSettleGoodsCouponPrice = $this->getSettleProductCouponPrice($getGoodsCouponPrice, $goodsVal);
                                $convertGoodsCouponPriceArrData[$goodsVal['goodsNo']][$goodsVal['optionSno']] = $getSettleGoodsCouponPrice;
                                unset($getGoodsCouponPrice);
                                $goodsMemberCouponNoArr[$goodsVal['goodsNo']] = $memberCouponNoArr;
                            }
                        }
                    }
                }
            }
            $goodsCouponData['goodsCouponSnoArr'] = $goodsCouponSnoArr; // 카트 일련번호
            $goodsCouponData['cartCouponNoArr'] = $cartCouponNoArr; // 카트 DB 데이터 쿠폰번호
            $goodsCouponData['goodsCouponArrData'] = $goodsCouponArrData; // 쿠폰 DB 데이터
            $goodsCouponData['convertGoodsCouponArrData'] = $convertGoodsCouponArrData; // 변환 쿠폰 데이터
            $goodsCouponData['convertGoodsCouponPriceArrData'] = $convertGoodsCouponPriceArrData; // 쿠폰 가격 데이터
            $goodsCouponData['goodsMemberCouponNoArr'] = $goodsMemberCouponNoArr;
            unset($cartData, $goodsCouponSnoArr, $cartCouponNoArr, $goodsCouponArrData, $convertGoodsCouponArrData, $convertGoodsCouponPriceArrData);
            return $goodsCouponData;
        }
    }


    /**
     * 상품쿠폰이 주문사용일 때 상품쿠폰 적용 레이어에서 쿠폰 할인 가격 노출(상품DC 고려계산)
     *
     * @param int    $couponPrice 가격
     * @param array  $cartData cart데이터
     * @return int   $couponPrice 가격
     *
     * @author tomi
     */
    public function getSettleProductCouponPrice($couponPrice, $cartData)
    {

        $goods = \App::load('\\Component\\Goods\\Goods');
        $goodsData = $goods->getGoodsInfo($cartData['goodsNo']);
        $cart = \App::load('\\Component\\Cart\\Cart');
        // 상품별 상품 할인 설정
        $cartData['price']['goodsDcPrice'] =  $cart->setProductCouponGoodsDcData($goodsData, $cartData['goodsCnt'], $cartData['price'], $cartData['fixedGoodsDiscount'], $cartData['goodsDiscountGroup'], $cartData['goodsDiscountGroupMemberInfo']);
        unset($goodsData['goodsDiscountFl'], $goodsData['goodsDiscount'], $goodsData['goodsDiscountUnit']);

        // 쿠폰할인금액이 상품결제금액 보다 큰 경우 쿠폰가격 재조정 (상품결제금액이 마이너스로 나오는 오류 수정)
        $exceptCouponPrice = $cartData['price']['goodsPriceSum'] - $cartData['price']['goodsDcPrice'];

        if ($this->couponConfig['couponOptPriceType'] == 'y') $exceptCouponPrice += $cartData['price']['optionPriceSum'];
        if ($this->couponConfig['couponAddPriceType'] == 'y') $exceptCouponPrice += $cartData['price']['addGoodsPriceSum'];
        if ($this->couponConfig['couponTextPriceType'] == 'y') $exceptCouponPrice += $cartData['price']['optionTextPriceSum'];
        if (empty($this->couponConfig['chooseCouponMemberUseType']) === true || $this->couponConfig['chooseCouponMemberUseType'] == 'all') {
            //$exceptCouponPrice -= $cartData['price']['memberDcPrice'] + $cartData['price']['memberOverlapDcPrice'];
        }

        foreach($couponPrice['memberCouponSalePrice'] as $key => $val) {
            $couponPrice['exceptCouponPrice'][$key] = $exceptCouponPrice;
            if ($exceptCouponPrice < $val) {
                $couponPrice['memberCouponSalePrice'][$key] = $exceptCouponPrice;
            }
        }

        unset($goodsData);
        return $couponPrice;
    }

    /**
     * 발급된 생일 쿠폰의 년도 가져오기
     *
     * 생일쿠폰 지급 설정으로 인하여 같은해에 동일 생일 쿠폰이 중복 발급되는 경우 방지를 위한 값
     *
     * @param integer $couponNo 쿠폰 번호
     * @param integer $memNo    회원 번호
     *
     * @return mixed
     */
    public function getMemberBirthDayCouponYear($couponNo, $memNo)
    {
        $arrBind = [];
        $arrWhere = [];
        $this->db->strField = 'mc.couponNo, mc.birthDayCouponYear, mc.regDt';
        if ($couponNo > 0) {// 쿠폰 고유번호 기준
            $arrWhere[] = 'mc.couponNo=?';
            $this->db->bind_param_push($arrBind, 'i', $couponNo);
        }
        if ($memNo > 0) {// 회원 고유번호 기준
            $arrWhere[] = 'mc.memNo=?';
            $this->db->bind_param_push($arrBind, 'i', $memNo);
        }
        $this->db->strWhere = implode(' AND ', gd_isset($arrWhere));
        $this->db->strOrder = 'mc.memberCouponNo desc';
        $this->db->strLimit = '1';
        $query = $this->db->query_complete();
        $strSQL = 'SELECT ' . array_shift($query) . ' FROM ' . DB_MEMBER_COUPON . ' as mc ' . implode(' ', $query);
        $getData = $this->db->query_fetch($strSQL, $arrBind, false);
        unset($arrBind, $arrWhere);

        return gd_htmlspecialchars_stripslashes($getData);
    }

    /**
     * 생일쿠폰 지급년도 세팅
     *
     * @param integer $year 생일쿠폰 지급년도
     *
     */
    public function setBirthDayCouponYear($year)
    {
        $this->birthDayCouponYear = $year;
    }


    /**
     * 2019.10.15
     *  - 바코드 기능 제거 작업으로 기존 레거시 보장을 위해 함수는 유지하되 로직 제거
     *  - 무조건 false로 던져서 메뉴를 사용하지 않도록 처리
     */
    public function getBarcodeMenuDisplay() {
        return 'n';
    }

    /**
     * getCouponMemberSaveFl
     * 결제 전 쿠폰 사용 가능 여부 체크
     * @param integer|string $memberCouponNo 쿠폰 번호
     * @return boolean
     */
    public function getCouponMemberSaveFl($memberCouponNo) {
        $memNo = Session::get('member.memNo');
        $memberCouponArrNo = explode(INT_DIVISION, $memberCouponNo);
        $couponCount = 0;
        $arrBind = $arrBindParam = $arrWhere = [];
        if ($memberCouponArrNo !== null) {
            if (is_array($memberCouponArrNo)) {
                foreach ($memberCouponArrNo as $sno) {
                    if (!empty($sno)) {
                        $this->db->bind_param_push($arrBind, 'i', $sno);
                        $arrBindParam[] = '?';
                        $couponCount++;
                    }
                }
                $arrWhere[] = 'memberCouponNo IN (' . implode(',', $arrBindParam) . ')';
            } else {
                $arrWhere[] = 'memberCouponNo = ? ';
                $this->db->bind_param_push($arrBind, 'i', $memberCouponArrNo);
                $couponCount++;
            }

            if ($couponCount > 0) {
                $arrWhere[] = 'memNo = ?';
                $this->db->bind_param_push($arrBind, $this->fieldTypes['memberCoupon']['memNo'], $memNo);

                $arrWhere[] = 'memberCouponUseDate = ?';
                $this->db->bind_param_push($arrBind, $this->fieldTypes['memberCoupon']['memberCouponUseDate'], '0000-00-00 00:00:00');

                $this->db->strField = 'COUNT(memberCouponNo) AS cnt';
                $this->db->strWhere = implode(' AND ', gd_isset($arrWhere));

                $query = $this->db->query_complete();
                $strSQL = 'SELECT ' . array_shift($query) . ' FROM ' . DB_MEMBER_COUPON . implode(' ', $query);
                $total = $this->db->query_fetch($strSQL, $arrBind, false)['cnt'];

                if ($total == $couponCount) {
                    return true;
                } else {
                    return false;
                }
            } else {
                return true;
            }
        } else {
            return true;
        }
    }

    /**
     * 기프트 쿠폰 사용 처리
     *
     * 기프트쿠폰 사용처리
     *
     * @param integer $couponNo 쿠폰 번호
     * @param integer $memNo    회원 번호
     *
     * @return mixed
     */
    public function useGiftCoupon($couponNo, $memNo)
    {
        $aInfo = $this->getMemberCouponInfo($couponNo);

        if (empty($aInfo)) { // 쿠폰정보가없으면
            return array('result' => 'F', 'msg' => '잘못된 쿠폰번호입니다. 다시 시도해주세요.');
            exit;
        }
        if ($aInfo['memNo'] != $memNo) { // 회원정보가 다르면
            return array('result' => 'F', 'msg' => '잘못된 쿠폰번호입니다. 다시 시도해주세요.');
            exit;
        }
        if ($aInfo['couponUseType'] != 'gift') { // 기프트쿠폰이 아니면
            return array('result' => 'F', 'msg' => '잘못된 쿠폰번호입니다. 다시 시도해주세요.');
            exit;
        }
        if ($aInfo['memberCouponEndDate'] < date('Y-m-d H:i:s')) { // 쿠폰사용만료일 지났으면
            return array('result' => 'F', 'msg' => '쿠폰사용기간이 지났습니다. 다시 확인해주세요.');
            exit;
        }
        if ($aInfo['memberCouponState'] != 'y') { // 쿠폰사용가능상태가아니면
            return array('result' => 'F', 'msg' => '잘못된 쿠폰번호입니다. 다시 시도해주세요.');
            exit;
        }

        // 회원쿠폰 테이블 사용 표기 업데이트
        $this->setMemberCouponState($couponNo, 'coupon', true);

        if ($aInfo['couponKindType'] == 'add') {
            // 처리 내용
            $contents = __('기프트쿠폰 마일리지 적립(' . $aInfo['couponNm'] . ')');

            // 마일리지 처리
            /** @var \Bundle\Component\Mileage\Mileage $mileage */
            $mileage = \App::load('\\Component\\Mileage\\Mileage');
            $mileage->setIsTran(false);
            if ($this->changeStatusAuto) {
                $mileage->setSmsReserveTime(date('Y-m-d 08:00:00', strtotime('now')));
            }
            $mileage->setMemberMileage($memNo, $aInfo['couponBenefit'], Mileage::REASON_CODE_GROUP . Mileage::REASON_CODE_GIFT_COUPON, 'c', null, null, $contents);

            $sMsg = '마일리지가 적립되었습니다. 적립금액 : ' . number_format(intval($aInfo['couponBenefit'])) . Globals::get('gSite.member.mileageBasic.unit');
        } else {
            // 처리 내용
            $contents = __('기프트쿠폰 예치금 지급(' . $aInfo['couponNm'] . ')');

            // 예치금 처리
            /** @var \Bundle\Component\Deposit\Deposit $deposit */
            $deposit = \App::load('\\Component\\Deposit\\Deposit');
            $deposit->setMemberDeposit($memNo, $aInfo['couponBenefit'], Deposit::REASON_CODE_GROUP . Deposit::REASON_CODE_GIFT_COUPON, 'c', null, null, $contents);

            $sMsg = '예치금이 지급되었습니다. 지급금액 : ' . number_format(intval($aInfo['couponBenefit'])) . Globals::get('gSite.member.depositConfig.unit');
        }

        return array('result' => 'T', 'msg' => $sMsg);
        exit;
    }

    /**
     * checkCouponType
     * 쿠폰 상태 확인
     * @param integer $couponNo 쿠폰번호
     * @param string $couponType y 발행 불가 체크 / f 발행 가능 체크
     * @param string $memberCouponNo 발급 후 몇일 쿠폰 사용기간 체크시 사용
     * @return boolean true 발행 가능 / false 발행 불가
     */
    public function checkCouponType($couponNo = 0, $couponType = 'y', $memberCouponNo = null)
    {
        $return = true;
        $msg = null;
        $now = date('Y-m-d H:i:s');
        $coupon = $this->getCouponInfo($couponNo, 'couponType, couponUsePeriodType, couponUsePeriodEndDate, couponUseDateLimit, couponDisplayEndDate, couponAmountType, couponAmount, couponSaveCount');

        if($coupon['couponUsePeriodType'] == 'period' && $coupon['couponUsePeriodEndDate'] != '0000-00-00 00:00:00' && $coupon['couponUsePeriodEndDate'] < $now) {
            $return = false;
            $msg = "쿠폰 사용기간 종료";
        } else if($coupon['couponUsePeriodType'] == 'day' && $coupon['couponUseDateLimit'] != null && $coupon['couponUseDateLimit'] != '0000-00-00 00:00:00' && $coupon['couponUseDateLimit'] < $now) {
            $return = false;
            $msg = "쿠폰 사용기간 종료";
        } else if($coupon['couponDisplayEndDate'] != '0000-00-00 00:00:00' && $coupon['couponDisplayEndDate'] < $now) {
            $return = false;
            $msg = "쿠폰 발급기간 종료";
        } else if($coupon['couponAmountType'] == 'y' && $coupon['couponAmount'] <= $coupon['couponSaveCount']) {
            $return = false;
            $msg = "쿠폰 발급수량 초과";
        }

        // 발급 후 몇일 쿠폰 사용기간 체크
        /* 쿠폰상태체크후 발급쿠폰의 상태값을 쿠폰의 사용마지막일로 체크하는부분이 잘못개발된듯하고 문제가 생기는 경우가 확인되어 우선 주석처리 -  기획적 의도는없는걸로 파악됩니다.
        if ($memberCouponNo) {
            $memberCoupon = $this->getMemberCouponInfo($memberCouponNo, 'memberCouponEndDate');
            if($coupon['couponUsePeriodType'] == 'day' && $memberCoupon['memberCouponEndDate'] < $now) {
                $return = false;
            }
        }
        */

        $arrData['couponNo'] = $couponNo;
        $logger = \App::getInstance('logger');
        if($return && $couponType == 'f' && $coupon['couponType'] != 'n') {
            $arrData['couponType'] = 'y';
            $arrBind = $this->db->get_binding(DBTableField::tableCoupon(), $arrData, 'update', array_keys($arrData), ['couponNo']);
            $this->db->bind_param_push($arrBind['bind'], 'i', $arrData['couponNo']);
            $this->db->set_update_db(DB_COUPON, $arrBind['param'], 'couponNo = ?', $arrBind['bind'], false);
            $logger->info(sprintf(__('쿠폰 사용가능 상태 변경 couponNo [%1$s] date [%2$s] '.Request::getReferer()), $arrData['couponNo'], $now));
        }
        if(!$return && $couponType != 'f') {
            $arrData['couponType'] = 'f';
            $arrBind = $this->db->get_binding(DBTableField::tableCoupon(), $arrData, 'update', array_keys($arrData), ['couponNo']);
            $this->db->bind_param_push($arrBind['bind'], 'i', $arrData['couponNo']);
            $this->db->set_update_db(DB_COUPON, $arrBind['param'], 'couponNo = ?', $arrBind['bind'], false);
            $logger->info(sprintf(__('쿠폰 발급종료 상태 변경 [%1$s] couponNo [%2$s] date [%3$s] '.Request::getReferer()), $msg, $arrData['couponNo'], $now));
        }
        $return = ($couponType == 'n') ? false : $return;
        return $return;
    }

    public function checkCouponTypeArr($couponNoArr)
    {
        if(!is_array($couponNoArr)) $couponNoArr = explode(INT_DIVISION, $couponNoArr);
        $return = true;
        foreach($couponNoArr as $val) {
            if(!$this->checkCouponType($val)) $return = false;
        }
        return $return;
    }

    public function getCouponzoneList() {
        $couponConfig = gd_policy('coupon.couponzone');
        $arrWhere = $arrBind = $list = [];
        $memNo = gd_isset(Session::get('member.memNo'), 0);
        $groupSno = gd_isset(Session::get('member.groupSno'), 0);

        if($couponConfig['autoDisplayFl'] == 'y') { // 자동진열

            $this->db->strField = "*";
            // 검색 조건
            // 온라인 쿠폰
            $arrWhere[] = 'couponKind = ?';
            $this->db->bind_param_push($arrBind, $this->fieldTypes['coupon']['couponKind'], 'online');
            // 발급여부 쿠폰(일시정지 제외)
            $arrWhere[] = 'couponType <> ?';
            $this->db->bind_param_push($arrBind, $this->fieldTypes['coupon']['couponType'], 'n');
            // 회원다운로드 쿠폰
            $arrWhere[] = 'couponSaveType = ?';
            $this->db->bind_param_push($arrBind, $this->fieldTypes['coupon']['couponSaveType'], 'down');
            // 할인 쿠폰, 적립 쿠폰
            $arrWhere[] = 'couponKindType in (?,?)';
            $this->db->bind_param_push($arrBind, $this->fieldTypes['coupon']['couponKindType'], 'sale');
            $this->db->bind_param_push($arrBind, $this->fieldTypes['coupon']['couponKindType'], 'add');
            // 쿠폰 노출 기간
            $arrWhere[] = ' ( couponDisplayType = \'n\' OR ( couponDisplayType = \'y\' AND couponDisplayStartDate < now() AND couponDisplayEndDate > now()) )';
            // 쿠폰 사용 기간 만료 체크
            $arrWhere[] = ' ((couponUsePeriodType = \'period\' and couponUsePeriodEndDate <> \'0000-00-00 00:00:00\' and couponUsePeriodEndDate > now()) OR (couponUsePeriodType = \'day\' and (couponUseDateLimit is not null or couponUseDateLimit = \'0000-00-00 00:00:00\' or couponUseDateLimit > now())))';

            $this->db->strLimit = 20;
            if($couponConfig['couponzoneSort'] != 'custom') $this->db->strOrder = $couponConfig['couponzoneSort'];
            if(!empty($couponConfig['unexposedCoupon'])) {
                if (is_array($couponConfig['unexposedCoupon'])) {
                    foreach($couponConfig['unexposedCoupon'] as $val) {
                        $bindQuery[] = '?';
                        $this->db->bind_param_push($arrBind,'s',$val);
                    }
                    $arrWhere[] = ' couponNo NOT IN (' . implode(',', $bindQuery) . ') ';
                } else {
                    $arrWhere[] = ' couponNo = ? ';
                    $this->db->bind_param_push($arrBind,'s',$couponConfig['unexposedCoupon']);
                }
            }

            $this->db->strWhere = implode(' AND ', gd_isset($arrWhere));
            $query = $this->db->query_complete();
            $strSQL = 'SELECT ' . array_shift($query) . ' FROM ' . DB_COUPON . implode(' ', $query);
            $data = $this->db->query_fetch($strSQL, $arrBind);

            $i = 1;
            foreach ($data as $key => $val) {
                if ($i > 9) {
                    unset($data[$key]);
                    continue;
                }
                if ($val['couponApplyMemberGroupDisplayType'] == 'y') { // 해당 등급만 노출
                    $couponApplyMemberGroup = explode(INT_DIVISION, $val['couponApplyMemberGroup']);
                    if (!in_array($groupSno, $couponApplyMemberGroup)) {
                        unset($data[$key]);
                        continue;
                    }
                }
                $i++;
            }
            $list[0]['list'] = $this->convertCouponArrData($data);
        } else { // 수동진열

            $now = date('Y-m-d h:i:s');
            foreach($couponConfig['groupNm'] as $key => $val) {
                $groupCoupon = [];
                if($couponConfig['couponzoneSort'] != 'custom') {
                    $strSQL = 'SELECT couponNo FROM '. DB_COUPON .' WHERE couponNo IN (\'' . implode('\',\'', $couponConfig['groupCoupon'][$key]) .'\') ORDER BY ' . $couponConfig['couponzoneSort'];
                    $couponNoSort = $this->db->query_fetch($strSQL);
                    foreach($couponNoSort as $sVal) {
                        $groupCoupon[] = $sVal['couponNo'];
                    }
                } else {
                    $groupCoupon = $couponConfig['groupCoupon'][$key];
                }

                $data = [];
                foreach($groupCoupon as $cKey => $cVal) {
                    $tmp = $this->getCouponInfo($cVal);
                    if($tmp['couponKind'] != 'online') continue;
                    if($tmp['couponType'] == 'n') continue;
                    if($tmp['couponSaveType'] != 'down') continue;
                    if($tmp['couponKindType'] != 'sale' && $tmp['couponKindType'] != 'add') continue;
                    if($tmp['couponDisplayType'] == 'y' && ($tmp['couponDisplayStartDate'] > $now || $tmp['couponDisplayEndDate'] < $now)) continue;
                    if($tmp['couponUsePeriodType'] == 'period' && $tmp['couponUsePeriodEndDate'] != '0000-00-00 00:00:00' && $tmp['couponUsePeriodEndDate'] < $now) continue;
                    if($tmp['couponUsePeriodType'] == 'day' && $tmp['couponUseDateLimit'] && $tmp['couponUseDateLimit'] != '0000-00-00 00:00:00' && $tmp['couponUseDateLimit'] < $now) continue;
                    if($tmp['couponApplyMemberGroupDisplayType'] == 'y') { // 해당 등급만 노출
                        $couponApplyMemberGroup = explode(INT_DIVISION, $tmp['couponApplyMemberGroup']);
                        if (!in_array($groupSno, $couponApplyMemberGroup)) {
                            continue;
                        }
                    }

                    $data[] = $tmp;
                }
                $list[$key]['title'] = $val;
                $list[$key]['list'] = $this->convertCouponArrData($data);
            }

        }

        if($memNo > 0) {
            foreach($list as $key => $val) {
                foreach ($val['list'] as $cKey => $cVal) {
                    $tmp = $this->getCouponInfo($cVal['couponNo']);
                    $saveMemberCouponCount = $this->getMemberCouponTotalCount($cVal['couponNo'], $memNo); // 회원의 해당 쿠폰 발급 총 갯수
                    // 재발급 제한
                    if ($tmp['couponSaveDuplicateType'] == 'n') {// 재발급 안됨
                        if ($saveMemberCouponCount > 0) { // 발급된 내역이 있다면
                            $list[$key]['list'][$cKey]['chkMemberCoupon'] = 'DUPLICATE_COUPON';
                            continue;
                        }
                    } else if ($tmp['couponSaveDuplicateType'] == 'y') {// 재발급 가능
                        if ($tmp['couponSaveDuplicateLimitType'] == 'y') {// 재발급 최대 수 여부
                            if ($saveMemberCouponCount > 0) { // 발급된 내역이 있다면
                                if ($tmp['couponSaveDuplicateLimit'] <= $saveMemberCouponCount) {
                                    $list[$key]['list'][$cKey]['chkMemberCoupon'] = 'DUPLICATE_COUPON';
                                    continue;
                                }
                            }
                        }
                    }
                    if($tmp['couponAmountType'] == 'y') {// 발급수량 제한
                        if($tmp['couponAmount'] <= $tmp['couponSaveCount']) {
                            $list[$key]['list'][$cKey]['chkMemberCoupon'] = 'MAX_COUPON';
                            continue;
                        }
                    }
                }
            }
        }

        return $list;
    }
    /**
     * 쿠폰존 쿠폰 발급
     *
     * @param integer $couponNo   쿠폰고유번호
     * @param integer $memNo      회원고유번호
     * @param integer $memGroupNo 회원등급고유번호
     *
     * @return boolean
     * @throws \Exception 발급조건이 안맞을 때
     *
     * @author su
     */
    public function setCouponzoneDown($couponNo, $memNo, $memGroupNo)
    {
        $barcodeDisplayFl = gd_isset($this->getBarcodeMenuDisplay(), 'n');
        if ($barcodeDisplayFl === 'y') {
            $barcodeCoupon = \App::load('\\Component\\Promotion\\BarcodeCoupon');
        }

        if (is_array($couponNo)) {
            $success = 0;
            foreach ($couponNo as $val) {
                $couponLinkDownData = $this->getCouponLinkDownUsable($val, $memNo, $memGroupNo, true);
                if ($couponLinkDownData) {
                    $arrData = [];
                    $arrData['couponNo'] = $val;
                    $arrData['couponSaveAdminId'] = '쿠폰존';
                    $arrData['memberCouponStartDate'] = $this->getMemberCouponStartDate($val);
                    $arrData['memberCouponEndDate'] = $this->getMemberCouponEndDate($val);
                    $arrData['memberCouponState'] = 'y';
                    $arrData['memNo'] = $memNo;
                    $arrBind = $this->db->get_binding(DBTableField::tableMemberCoupon(), $arrData, 'insert', array_keys($arrData), ['memberCouponNo']);
                    $this->db->set_insert_db(DB_MEMBER_COUPON, $arrBind['param'], $arrBind['bind'], 'y');

                    if ($barcodeDisplayFl === 'y') {
                        //바코드 발급. - 2018.11.07 parkjs
                        $memCouponNo = $this->db->insert_id();
                        $barcodeResult = $barcodeCoupon->setCouponNo([$arrData['couponNo']])->setMemberCouponNo($memCouponNo)->couponBarcodeGenarator('give');
                    }

                    unset($arrData);
                    unset($arrBind);
                    // 쿠폰 발급 카운트 증가
                    $this->setCouponMemberSaveCount($val);
                    $success++;
                }
            }
            $return['return'] = true;
            $return['success'] = $success;
            return $return;
        } else {
            $couponLinkDownData = $this->getCouponLinkDownUsable($couponNo, $memNo, $memGroupNo);
            if ($couponLinkDownData) {
                $arrData = [];
                $arrData['couponNo'] = $couponNo;
                $arrData['couponSaveAdminId'] = '쿠폰존';
                $arrData['memberCouponStartDate'] = $this->getMemberCouponStartDate($couponNo);
                $arrData['memberCouponEndDate'] = $this->getMemberCouponEndDate($couponNo);
                $arrData['memberCouponState'] = 'y';
                $arrData['memNo'] = $memNo;
                $arrBind = $this->db->get_binding(DBTableField::tableMemberCoupon(), $arrData, 'insert', array_keys($arrData), ['memberCouponNo']);
                $this->db->set_insert_db(DB_MEMBER_COUPON, $arrBind['param'], $arrBind['bind'], 'y');

                if ($barcodeDisplayFl === 'y') {
                    //바코드 발급. - 2018.11.07 parkjs
                    $memCouponNo = $this->db->insert_id();
                    $barcodeResult = $barcodeCoupon->setCouponNo([$arrData['couponNo']])->setMemberCouponNo($memCouponNo)->couponBarcodeGenarator('give');
                }

                unset($arrData);
                unset($arrBind);
                // 쿠폰 발급 카운트 증가
                $this->setCouponMemberSaveCount($couponNo);
                $return['return'] = true;
                $return['success'] = 1;
                return $return;
            } else {
                throw new \Exception($couponLinkDownData);
            }
        }
    }

    /**
     * 수기주문 장바구니에 사용된 쿠폰의 유효성 체크 및 재적용
     *
     * @param array    $cartInfo 쿠폰번호
     * @param array    $realCartSno 장바구니 번호
     * @param array    $memNo 회원번호
     * @return array   $result 성공여부 / 사용 불가능한 쿠폰의 할인금액 합계
     *
     * @author agni
     */
    public function setRealMemberCouponApplyOrderWrite($cartInfo, $realCartSno, $memNo) {
        $reSetMemberCouponApply = false;
        $resetMemberCouponSalePrice = 0;
        $resetCouponApplyNo = [];
        $memberCouponStateCartChk = false;
        if ($cartInfo > 0) {
            foreach ($cartInfo as $key => $value) {
                foreach ($value as $key1 => $value1) {
                    foreach ($value1 as $key2 => $value2) {
                        if ($value2['memberCouponNo']) {
                            // 장바구니에 사용된 회원쿠폰의 정율도 정액으로 계산된 금액
                            $convertCartCouponPriceArrData = $this->getMemberCouponPrice($value2['price'], $value2['memberCouponNo']);
                            $memberCouponNoArr = explode(INT_DIVISION, $value2['memberCouponNo']);
                            foreach ($memberCouponNoArr as $memberCouponNo) {
                                $memberCouponData = $this->getMemberCouponInfo($memberCouponNo, 'c.couponNo, mc.memberCouponNo, mc.memberCouponState');
                                // 장바구니 담긴 쿠폰 유효성 체크
                                if($this->checkCouponType($memberCouponData['couponNo'])) {
                                    $couponApply[$value2['sno']]['couponApplyNo'][] = $memberCouponData['memberCouponNo'];
                                    $couponApply[$value2['sno']]['memberCouponSalePrice'][] =  $convertCartCouponPriceArrData['memberCouponSalePrice'][$memberCouponData['memberCouponNo']];
                                    $couponApply[$value2['sno']]['memberCouponState'][] = $memberCouponData['memberCouponState'];
                                } else {
                                    $reSetMemberCouponApply = true;
                                    $resetCouponApply[$value2['sno']]['couponApplyNo'][] = $memberCouponData['memberCouponNo'];
                                    $resetCouponApply[$value2['sno']]['memberCouponSalePrice'][] =  $convertCartCouponPriceArrData['memberCouponSalePrice'][$memberCouponData['memberCouponNo']];
                                }
                            }
                        }
                    }
                }
            }

            // 사용가능한 쿠폰만 다시 적용
            $cart = new CartAdmin($memNo, true);
            foreach ($couponApply as $cartKey => $couponApplyInfo) {
                // 원래 적용되어 있던 실제 장바구니의 상품적용 쿠폰 적용 / 변경
                $couponApplyNo = implode(INT_DIVISION, $couponApplyInfo['couponApplyNo']);
                $cart->setMemberCouponApplyOrderWrite($realCartSno[$cartKey], $couponApplyNo, $couponApplyNo);

                // 회원 쿠폰사용 여부가 CART 상태가 존재하는지 여부 추가
                if (in_array('cart', $couponApplyInfo['memberCouponState'])) {
                    $memberCouponStateCartChk = true;
                }
            }

            foreach ($resetCouponApply as $cartKey => $resetCouponApplyInfo) {
                // 수기주문 장바구니의 memberCouponNo를 update 처리
                $cart = null;
                unset($cart);
                $cart = new CartAdmin($memNo);
                $resetCouponApplyNo = implode(INT_DIVISION, $resetCouponApply[$cartKey]['couponApplyNo']);
                $cart->updateOrderWriteRealCouponData($cartKey, $resetCouponApplyNo);

                // 사용 불가능한 쿠폰의 할인금액 합계
                $resetMemberCouponSalePrice += array_sum($resetCouponApply[$cartKey]['memberCouponSalePrice']);
            }
        } else {
            return array('result' => $reSetMemberCouponApply);
        }
        return array('result' => $reSetMemberCouponApply, 'resetMemberCouponSalePrice' => $resetMemberCouponSalePrice, 'resetCouponApplyNo' => $resetCouponApplyNo, 'memberCouponStateCartChk' => $memberCouponStateCartChk);
    }

    public function goodsCheckCouponTypeArr($couponNoArr)
    {
        if(!is_array($couponNoArr)) $couponNoArr = explode(INT_DIVISION, $couponNoArr);
        $return = true;
        $setCouponApplyNo = [];
        foreach($couponNoArr as $val) {
            $memberCouponData = $this->getMemberCouponInfo($val, 'c.couponNo, mc.memberCouponNo');
            // 장바구니 담긴 쿠폰 유효성 체크
            if(!$this->checkCouponType($memberCouponData['couponNo'], 'y', $memberCouponData['memberCouponNo'])) {
                $return = false;
            } else {
                $setCouponApplyNo[] = $val;
            }
        }
        return array('result' => $return, 'setCouponApplyNo' => $setCouponApplyNo);
    }

    public function setRealMemberCouponApplyOrder($cartInfo) {
        $reSetMemberCouponApply = false;
        $resetMemberCouponSalePrice = 0;
        $resetMemberCouponAddMileage = 0;

        if($cartInfo > 0) {
            foreach ($cartInfo as $key => $value) {
                foreach ($value as $key1 => $value1) {
                    foreach ($value1 as $key2 => $value2) {
                        if ($value2['memberCouponNo']) {
                            // 장바구니에 사용된 회원쿠폰의 정율도 정액으로 계산된 금액
                            $convertCartCouponPriceArrData = $this->getMemberCouponPrice($value2['price'], $value2['memberCouponNo']);
                            $memberCouponNoArr = explode(INT_DIVISION, $value2['memberCouponNo']);
                            foreach ($memberCouponNoArr as $memberCouponNo) {
                                $memberCouponData = $this->getMemberCouponInfo($memberCouponNo, 'c.couponNo, mc.memberCouponNo');
                                if($this->checkCouponType($memberCouponData['couponNo'])) {
                                    $couponApply[$value2['sno']]['couponApplyNo'][] = $memberCouponData['memberCouponNo'];
                                } else {
                                    $reSetMemberCouponApply = true;
                                    $resetCouponApply[$value2['sno']]['memberCouponSalePrice'][] =  $convertCartCouponPriceArrData['memberCouponSalePrice'][$memberCouponData['memberCouponNo']];
                                    $resetCouponApply[$value2['sno']]['memberCouponAddMileage'][] =  $convertCartCouponPriceArrData['memberCouponAddMileage'][$memberCouponData['memberCouponNo']];
                                }
                            }
                        }
                    }
                }
            }
        }

        // 사용가능한 쿠폰만 다시 적용
        if($reSetMemberCouponApply) {
            $cart = \App::load('\\Component\\Cart\\Cart');
            foreach ($resetCouponApply as $cartKey => $resetCouponApplyInfo) {
                $cart->setMemberCouponDelete($cartKey); // 상품적용 쿠폰 제거
                // 사용 불가능한 쿠폰의 할인금액 합계
                $resetMemberCouponSalePrice += array_sum($resetCouponApply[$cartKey]['memberCouponSalePrice']);
                $resetMemberCouponAddMileage += array_sum($resetCouponApply[$cartKey]['memberCouponAddMileage']);
            }

            foreach ($couponApply as $cartKey => $couponApplyInfo) {
                // 상품적용 쿠폰 적용 / 변경
                $couponApplyNo = implode(INT_DIVISION, $couponApplyInfo['couponApplyNo']);
                $cart->setMemberCouponApply($cartKey, $couponApplyNo);
            }
            return array('result' => $reSetMemberCouponApply, 'resetMemberCouponSalePrice' => $resetMemberCouponSalePrice, 'resetMemberCouponAddMileage' => $resetMemberCouponAddMileage);
        }
    }

    /**
     * 해당 회원쿠폰의 중복체크 (장바구니 사용여부)
     *
     * @param string $memberCouponNo    회원쿠폰고유번호 (INT_DIVISION로 구분된 memberCouponNo)
     * @param integer $cartSno 장바구니 sno
     *
     * @author su
     */
    public function couponOverlapCheck($memberCouponNo, $cartSno) {
        $memberCouponArrNo = explode(INT_DIVISION, $memberCouponNo);
        if ($cartSno) {
            $cart = new CartAdmin(Session::get('member.memNo'), true);
            $cartInfo = $cart->getCartGoodsData();
            foreach ($cartInfo as $key => $value) {
                foreach ($value as $key1 => $value1) {
                    foreach ($value1 as $key2 => $value2) {
                        if (($cartSno != $value2['sno']) && empty($value2['memberCouponNo']) === false) {
                            $memberCouponApplyNo = explode(INT_DIVISION, $value2['memberCouponNo']);
                            foreach ($memberCouponArrNo as $applyMemberCouponNo) {
                                if (in_array($applyMemberCouponNo, $memberCouponApplyNo)) {
                                    throw new \Exception('이미 사용중인 쿠폰이 적용되어 있습니다.');
                                }
                            }
                        }
                    }
                }
            }
        }
    }

    /**
     *
     * 회원가입시 발급 될 쿠폰 리스트 (혜택 높은순, 등록일 최근)
     * @param $simpleJoinFl (주문간단가입 true / 푸쉬가입 false)
     */
    public function getJoinEventCouponList($simpleJoinFl = true)
    {
        $this->db->strField = "c.*";
        $addQuery[] = "c.couponSaveType = 'auto'";
        $addQuery[] = 'c.couponType = ?';
        $this->db->bind_param_push($arrBind, 's', 'y');

        $joinEventOrder = gd_policy('member.joinEventOrder');
        if($joinEventOrder['couponNo'] && $simpleJoinFl) {
            $addQuery[] = "(c.couponEventType = 'join' OR couponNo = ? )";
            $this->db->bind_param_push($arrBind, 's', $joinEventOrder['couponNo']);
        } else {
            $addQuery[] = "c.couponEventType = 'join'";
        }

        $this->db->strOrder = 'c.couponBenefitType ASC, c.couponBenefit DESC, regDt DESC';
        $this->db->strWhere = implode(' AND ', $addQuery);

        $query = $this->db->query_complete();
        $strSQL = 'SELECT ' . array_shift($query) . ' FROM ' . DB_COUPON . ' as c ' . implode(' ', $query);
        $couponData = $this->db->query_fetch($strSQL, $arrBind);
        return $couponData;
        //SELECT  c.*  FROM es_coupon as c   WHERE c.couponSaveType = `auto` AND c.couponType = `y` AND (c.couponEventType = `join` OR couponNo = `188` )    ORDER BY c.couponBenefitType ASC, c.couponBenefit DESC
    }

    /**
     * 회원 간단가입시 발급된 쿠폰 리스트
     * order by 혜택 높은 순, 등록일 최근
     *
     * @param $memNo
     * @param $memberCouponNo
     * @param $orderBy string
     * @param pageFl boolean
     * @param $req array
     */
    public function getMemberSimpleJoinCouponList($memNo, $memberCouponNo = null, $orderBy = null, $pageFl = false, $req = null){
        if(empty($memNo) && $memberCouponNo == null) {
            return false;
        }
        $this->db->strField = "mc.*, c.*";
        if($memNo) {
            $arrWhere[] = 'mc.memNo = ?';
            $this->db->bind_param_push($arrBind, $this->fieldTypes['memberCoupon']['memNo'], $memNo);
        } else {
            $arrWhere[] = 'mc.memberCouponNo in (' . str_replace(INT_DIVISION, ',', $memberCouponNo) . ')';
        }
        $arrWhere[] = "c.couponSaveType = 'auto'";
        $arrWhere[] = "(c.couponEventType = 'join' OR c.couponEventType = 'joinEvent')";
        $this->db->strWhere = implode(' AND ', $arrWhere);
        $this->db->strJoin = ' LEFT JOIN es_coupon AS c ON c.couponNo = mc.couponNo';

        if($orderBy) $this->db->strOrder = $orderBy;

        // --- 페이지 기본설정
        if($pageFl) {
            if (gd_isset($req['pagelink'])) {
                $req['page'] = (int)str_replace('page=', '', preg_replace('/^{page=[0-9]+}/', '', gd_isset($req['pagelink'])));
            } else {
                $req['page'] = 1;
            }
            $page = \App::load('\\Component\\Page\\Page', $req['page']);
            $page->page['list'] = $req['pageNum']; // 페이지당 리스트 수
            $page->recode['amount'] = $req['amount'];
            $page->setPage();
            $page->setUrl(\Request::getQueryString());
            $strSQL = ' SELECT COUNT(*) AS cnt FROM ' . DB_MEMBER_COUPON . ' as mc LEFT JOIN ' . DB_COUPON . ' AS c ON c.couponNo = mc.couponNo WHERE ' . $this->db->strWhere;
            $res = $this->db->query_fetch($strSQL, $arrBind, false);
            $page->recode['total'] = $res['cnt']; // 검색 레코드 수
            // 검색 레코드 수
            $page->setPage();
            $this->db->strLimit = $page->recode['start'] . ', ' . $req['pageNum'];
        }
        $query = $this->db->query_complete();
        $strSQL = 'SELECT ' . array_shift($query) . ' FROM ' . DB_MEMBER_COUPON . ' as mc ' . implode(' ', $query);
        $couponData = $this->db->query_fetch($strSQL, $arrBind);
        return $couponData;
        //SELECT  mc.*, c.*  FROM es_memberCoupon as mc   LEFT JOIN es_coupon AS c ON c.couponNo = mc.couponNo   WHERE mc.memNo = 14815 AND c.couponSaveType = `auto` AND (c.couponEventType = `join` OR c.couponEventType = `joinEvent`)    ORDER BY c.couponBenefitType ASC, c.couponBenefit DESC
    }

    /**
     * getGroupCouponForCouponTypeY
     * 그룹쿠폰중 발급가능한 쿠폰리스트
     *
     * @param bool | integer $sno 그룹번호
     * @return bool | array
     */
    public function getGroupCouponForCouponTypeY($sno = false)
    {
        $strSQL = "SELECT replace(group_concat(groupCoupon), '||', ',') as groupCoupon FROM ". DB_MEMBER_GROUP." WHERE groupCoupon <> ''";
        if($sno) $strSQL .= ' AND sno = '.$sno;
        $groupCoupon = $this->db->query_fetch($strSQL, null, false)['groupCoupon'];
        if($groupCoupon) {
            $strSQL = "SELECT * FROM " . DB_COUPON . " WHERE couponNo IN ( " . $groupCoupon . " ) AND couponType = 'y'";
            return $this->db->query_fetch($strSQL, null, false);
        }
        return false;
    }

    /**
     * getGroupCouponForCouponTypeY
     * 그룹쿠폰중 발급가능한 쿠폰리스트
     *
     * @param bool | integer $sno 그룹번호
     * @return bool | array
     */
    public function getCouponUseMemberGroup($couponNo)
    {
        if ($couponNo) {
            $memberGroup = $this->db->query_fetch("SELECT couponUseMemberGroup FROM " . DB_COUPON_USE_MEMBER_GROUP . " WHERE couponNo = " . $couponNo);
            if ($memberGroup) {
                $resultMemberGroup = array();
                foreach ($memberGroup as $val) {
                    $resultMemberGroup[] = $val['couponUseMemberGroup'];
                }
                return $resultMemberGroup;
            } else {
                return false;
            }
        } else {
            return false;
        }
    }

}



