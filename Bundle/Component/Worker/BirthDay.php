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

namespace Bundle\Component\Worker;

use App;
use Component\Database\DBTableField;
use Component\Mileage\Mileage;
use Component\Sms\Code;
use Component\Sms\SmsAutoCode;
use Exception;
use Framework\Utility\ComponentUtils;

/**
 * BirthDay
 *
 * @author    su
 * @version   1.0
 * @since     1.0
 * @copyright ⓒ 2016, NHN godo: Corp.
 */
class BirthDay
{
    /** @var  \Bundle\Component\Mileage\Mileage $mileageService */
    protected $mileageService;
    protected $db;
    public $couponConfig;
    public $birthDate;

    public function __construct(Mileage $mileageService = null, $date = null)
    {
        if ($mileageService === null) {
            $this->mileageService = new Mileage();
        }
        if (!is_object($this->db)) {
            $this->db = App::load('DB');
        }

        $this->couponConfig = gd_policy('coupon.config');
        // 생일 쿠폰 발급 기본 값 설정 (당일 발급)
        $this->couponConfig['birthdayCouponReserveType'] = gd_isset($this->couponConfig['birthdayCouponReserveType'], 'days');
        $this->couponConfig['birthdayCouponReserveDays'] = gd_isset($this->couponConfig['birthdayCouponReserveDays'], 0);

        if ($date) {
            $dateArr = explode('-', $date);
            // 발급 연도
            $this->birthDate['y'] = $dateArr[0];
            // 양력 생일
            $this->birthDate['s'] = $dateArr[1] . '-' . $dateArr[2];
            // 음력 생일
            $lunarDate = ComponentUtils::getLunarDate($date);
            if ($lunarDate) {
                $this->birthDate['l'] = date('m-d', strtotime($lunarDate));
            } else {
                $this->birthDate['l'] = '';
            }
        } else {
            // 발급 연도
            $this->birthDate['y'] = date('Y');
            // 양력 생일
            $this->birthDate['s'] = date('m-d');
            // 음력 생일
            $lunarDate = ComponentUtils::getLunarDate();
            if ($lunarDate) {
                $this->birthDate['l'] = date('m-d', strtotime($lunarDate));
            } else {
                $this->birthDate['l'] = '';
            }
        }
    }

    /**
     * 스케줄러에서 호출
     *
     * @return bool
     * @deprecated 2017-02-27 yjwee 미사용 함수여서 제거 될 수도 있습니다. 사용하지 마시기 바랍니다.
     */
    public function run()
    {
        $todayBirthDayMemberArr = $this->getTodayBirthDayMember();
        $this->sendSmsBirthDay($todayBirthDayMemberArr);
        if ($this->couponConfig['couponUseType'] == 'y') {
            $this->sendCouponBirthDay($todayBirthDayMemberArr);
        }

        return true;
    }

    /**
     * 생일축하 sms 발송
     *
     * @return bool
     */
    public function runSms()
    {
        $todayBirthDayMemberArr = $this->getTodayBirthDayMember(false);
        $this->sendSmsBirthDay($todayBirthDayMemberArr);

        return true;
    }

    /**
     * 생일축하 쿠폰 발급 및 sms 발송
     *
     * @param array $todayBirthDayMemberArr
     *
     * @return bool
     */
    public function runCoupon(array $todayBirthDayMemberArr)
    {
        try {
			$this->sendCouponBirthDay($todayBirthDayMemberArr);
        } catch (Exception $e) {
            return false;
        }

        return true;
    }

    /**
     * 생일축하 마일리지 지급 및 sms 발송
     *
     * @param array $todayBirthDayMemberArr
     *
     * @return bool
     */
    public function runMileage(array $todayBirthDayMemberArr)
    {
        try {
            $policy = gd_policy('member.mileageGive');
            $birthAmount = $policy['birthAmount'];
            if ($birthAmount > 0) {
                $this->sendMileageBirthDay($todayBirthDayMemberArr, $birthAmount);
            }
        } catch (Exception $e) {
            return false;
        }

        return true;
    }

    /**
     * 오늘 생인인 회원 고유번호
     *
     * @param bool $birthEventFl 생일 이벤트여부
     *
     * @return array|object
     */
    public function getTodayBirthDayMember($birthEventFl = true)
    {
        // 2월29일이 생일인 사람은 4년마다 만! 생일자 검색이 된다(실 주민번호상 0229 존재). 4년마다 제공
        $arrBind = [];

        // 생일 이벤트 제공 여부
        if ($birthEventFl) {
            // 양력/음력 생일 회원 - 음력 정상 체크
            if ($this->birthDate['l']) {
                $this->db->strWhere = "((DATE_FORMAT(birthDt, '%m-%d') = ? AND calendarFl = 's') or (DATE_FORMAT(birthDt, '%m-%d') = ? AND calendarFl = 'l')) AND appFl = 'y' AND DATE_FORMAT(birthEventFl, '%Y') < ?";
                $this->db->bind_param_push($arrBind, 's', $this->birthDate['s']);
                $this->db->bind_param_push($arrBind, 's', $this->birthDate['l']);
                $this->db->bind_param_push($arrBind, 'i', $this->birthDate['y']);
            } else {
                $this->db->strWhere = "(DATE_FORMAT(birthDt, '%m-%d') = ? AND calendarFl = 's') AND appFl = 'y' AND DATE_FORMAT(birthEventFl, '%Y') < ?";
                $this->db->bind_param_push($arrBind, 's', $this->birthDate['s']);
                $this->db->bind_param_push($arrBind, 'i', $this->birthDate['y']);
            }
        } else {
            // 양력/음력 생일 회원 - 음력 정상 체크
            if ($this->birthDate['l']) {
                $this->db->strWhere = "((DATE_FORMAT(birthDt, '%m-%d') = ? AND calendarFl = 's') or (DATE_FORMAT(birthDt, '%m-%d') = ? AND calendarFl = 'l')) AND appFl = 'y'";
                $this->db->bind_param_push($arrBind, 's', $this->birthDate['s']);
                $this->db->bind_param_push($arrBind, 's', $this->birthDate['l']);
            } else {
                $this->db->strWhere = "(DATE_FORMAT(birthDt, '%m-%d') = ? AND calendarFl = 's') AND appFl = 'y'";
                $this->db->bind_param_push($arrBind, 's', $this->birthDate['s']);
            }
        }

        $this->db->strWhere .= ' AND sleepFl=\'n\'';
        $this->db->strField = "memNo, groupSno, birthDt, calendarFl, cellPhone, memNm, memId, smsFl";
        $query = $this->db->query_complete();
        $strSQL = 'SELECT ' . array_shift($query) . ' FROM ' . DB_MEMBER . implode(' ', $query);
        $todayBirthDayMemberArr = $this->db->query_fetch($strSQL, $arrBind);
        unset($arrBind);

        return $todayBirthDayMemberArr;
    }

    /**
     * 생일축하 쿠폰 자동발급 및 쿠폰 발급 안내 SMS 발송
     *
     * @param array $todayBirthDayMemberArr 발급 대상 회원 정보
     *                                      (memNo, groupSno, birthDt, calendarFl, cellPhone, memNm, memId, smsFl)
     */
    public function sendCouponBirthDay($todayBirthDayMemberArr)
    {
        $smsAuto = \App::load('\\Component\\Sms\\SmsAuto');
        $smsAuto->setSmsType(SmsAutoCode::PROMOTION);
        $smsAuto->setSmsAutoCodeType(Code::COUPON_BIRTH);
        /** @var \Bundle\Component\Coupon\Coupon $coupon */
        $coupon = App::load('\\Component\\Coupon\\Coupon');
        $couponYear = $this->getBirthDayCouponYear();
        $coupon->setBirthDayCouponYear($couponYear);
        foreach ($todayBirthDayMemberArr as $val) {
            // 생일 쿠폰 발급
            $coupon->setAutoCouponMemberSave('birth', $val['memNo'], $val['groupSno']);
            $couponSaveResult = $coupon->getResultStorage();
            $hasBirthDayCoupon = ($couponSaveResult->get('success', 0) > 0);

            // 생일 쿠폰 발급 SMS 전송
            if ($hasBirthDayCoupon && $val['smsFl'] == 'y' && in_array($val['memNo'], $couponSaveResult->get('benefitSuccessMemNo'))) {
                $smsAuto->setSmsAutoSendDate($smsAuto->getSmsAutoReserveTime(Code::COUPON_BIRTH));
                $smsAuto->setReceiver($val['cellPhone']);
                $smsAuto->setReplaceArguments(['name' => $val['memNm']]);
                $smsAuto->autoSend();
            }
        }
    }

    /**
     * 생일축하 마일리지 지급
     *
     * @param array $todayBirthDayMemberArr
     * @param       $birthAmount
     */
    public function sendMileageBirthDay(array $todayBirthDayMemberArr, $birthAmount)
    {
        $this->mileageService->setSmsReserveTime($this->getSmsReserveTime());
        foreach ($todayBirthDayMemberArr as $val) {
            $reasonCd = Mileage::REASON_CODE_GROUP . Mileage::REASON_CODE_ETC;
            $result = $this->mileageService->setMemberMileage($val['memNo'], $birthAmount, $reasonCd, 'm', $val['memId'], null, '생일 마일리지 지급');
            /* 회원 birthEventFl 에 지급년월일 넣기
             * 2018-06-07 haky 생일 쿠폰 지급일 설정 추가로 생일 당일에 1번 지급되는 마일리지쪽으로 이동 (기존 sendCouponBirthDay)
             */
            if ($result) {
                $this->setMemberBirthDayEventDate($val['memNo']);
            }
        }
    }

    protected function getSmsReserveTime()
    {
        return date('Y-m-d 08:00:00', strtotime('now'));
    }

    /**
     * 생일축하 SMS 발송
     *
     * @param array $todayBirthDayMemberArr 발급 대상 회원 정보
     *                                      (memNo, groupSno, birthDt, calendarFl, cellPhone, memNm, memId, smsFl)
     */
    public function sendSmsBirthDay($todayBirthDayMemberArr)
    {
        $smsAuto = \App::load('\\Component\\Sms\\SmsAuto');
        $smsAuto->setSmsType(SmsAutoCode::PROMOTION);
        $smsAuto->setSmsAutoCodeType(Code::BIRTH);
        $smsAuto->setSmsAutoSendDate($smsAuto->getSmsAutoReserveTime(Code::BIRTH));
        foreach ($todayBirthDayMemberArr as $val) {
            // 생일 축하 SMS 전송
            if ($val['smsFl'] == 'y') {
                $smsAuto->setReceiver($val['cellPhone']);
                $smsAuto->setReplaceArguments(['name' => $val['memNm']]);
                $smsAuto->autoSend();
            }
        }
    }

    /*
     * 생일 회원 birthEventFl 에 지급년월일 넣기
     */
    public function setMemberBirthDayEventDate($memNo)
    {
        $arrData['birthEventFl'] = date('Y-m-d');
        $arrBind = $this->db->get_binding(DBTableField::tableMember(), $arrData, 'update', array_keys($arrData), ['memNo']);
        $this->db->bind_param_push($arrBind['bind'], 'i', $memNo);
        $this->db->set_update_db(DB_MEMBER, $arrBind['param'], 'memNo = ?', $arrBind['bind'], false);
        unset($arrBind);
        unset($arrData);
    }

    /**
     * 생일 쿠폰 발급을 위한 회원 리스트
     *
     * @return array
     */
    public function getMemberListForBirthDayCoupon()
    {
        $arrBind = [];
        // 생일 쿠폰 발급 기본 값 설정 (당일 발급)
        $today = $this->birthDate['y'] . '-' . $this->birthDate['s'];

        // 생일 쿠폰 월별/일별 발급
        if ($this->couponConfig['birthdayCouponReserveType'] == 'month') {
            list($targetMonth, $todayDate) = explode('-', $this->birthDate['s']);
            $birthdayCouponReserveDate = $this->couponConfig['birthdayCouponReserveDate'];
            $targetMonthDays = gd_specific_month_days('d', '0', $today);

            // 지급일이 없는 달에는 달의 말일로 지급일 설정
            if ($birthdayCouponReserveDate > $targetMonthDays['last']) {
                $birthdayCouponReserveDate = $targetMonthDays['last'];
            }

            // 전월 발급
            if ($this->couponConfig['birthdayCouponReserveMonth'] == 'last') {
                $targetMonth = explode('-', gd_next_month_date(1, $today))[1];
                $targetMonthDays = gd_specific_month_days('d', '0', $this->birthDate['y'] . '-' . $targetMonth . '-' . $birthdayCouponReserveDate);
            }

            // 설정에 따른 음력날짜 계산 (양력상 한달 안에 음력으로는 두달이 걸쳐 있기때문에 음력 첫날과 마지막날로 계산)
            $targetLunarDate['first'] = ComponentUtils::getLunarDate($this->birthDate['y'] . '-' . $targetMonth . '-' . $targetMonthDays['first']);
            $targetLunarDate['last'] = ComponentUtils::getLunarDate($this->birthDate['y'] . '-' . $targetMonth . '-' . $targetMonthDays['last']);

            if ($birthdayCouponReserveDate != $todayDate) {
                // 오늘이 월별 지급일이 아닌 경우
                return [];
            } else {
                if ($targetLunarDate['first'] && $targetLunarDate['last']) {
                    $this->db->strWhere = "((DATE_FORMAT(birthDt, '%m') = ? AND calendarFl = 's') or ((DATE_FORMAT(birthDt, '%m-%d') BETWEEN ? AND ?) AND calendarFl = 'l')) AND appFl = 'y'";
                    $this->db->bind_param_push($arrBind, 's', $targetMonth);
                    $this->db->bind_param_push($arrBind, 's', date('m-d', strtotime($targetLunarDate['first'])));
                    $this->db->bind_param_push($arrBind, 's', date('m-d', strtotime($targetLunarDate['last'])));
                } else {
                    $this->db->strWhere = "(DATE_FORMAT(birthDt, '%m') = ? AND calendarFl = 's') AND appFl = 'y'";
                    $this->db->bind_param_push($arrBind, 's', $targetMonth);
                }
            }
        } else {
            // 양력 계산
            $solarDate = date('m-d', strtotime('+' . $this->couponConfig['birthdayCouponReserveDays'] . ' days', strtotime($today)));
            if ($this->birthDate['l']) {
                // 음력 계산
                $lunarDate = date('m-d', strtotime('+' . $this->couponConfig['birthdayCouponReserveDays'] . ' days', strtotime($this->birthDate['y'] . '-' . $this->birthDate['l'])));
                $this->db->strWhere = "((DATE_FORMAT(birthDt, '%m-%d') = ? AND calendarFl = 's') or (DATE_FORMAT(birthDt, '%m-%d') = ? AND calendarFl = 'l')) AND appFl = 'y'";
                $this->db->bind_param_push($arrBind, 's', $solarDate);
                $this->db->bind_param_push($arrBind, 's', $lunarDate);
            } else {
                $this->db->strWhere = "(DATE_FORMAT(birthDt, '%m-%d') = ? AND calendarFl = 's') AND appFl = 'y'";
                $this->db->bind_param_push($arrBind, 's', $solarDate);
            }
        }

        $this->db->strWhere .= ' AND sleepFl=\'n\'';
        $this->db->strField = "memNo, groupSno, birthDt, calendarFl, cellPhone, memNm, memId, smsFl";
        $query = $this->db->query_complete();
        $strSQL = 'SELECT ' . array_shift($query) . ' FROM ' . DB_MEMBER . implode(' ', $query);
        $memberList = $this->db->query_fetch($strSQL, $arrBind);
        unset($arrBind);

        return $memberList;
    }

    /**
     * 몇년도 생일축하 쿠폰인지 년도 계산
     *
     * @return integer
     */
    public function getBirthDayCouponYear()
    {
        $today = $this->birthDate['y'] . '-' . $this->birthDate['s'];
        $issueYear = $this->birthDate['y'];
        $thisMonth = explode('-', $this->birthDate['s'])[0];

        // 생일 쿠폰 월별/일별 발급
        if ($this->couponConfig['birthdayCouponReserveType'] == 'month') {
            if ($this->couponConfig['birthdayCouponReserveMonth'] == 'last' && $thisMonth == 12) {
                $issueYear += 1;
            }
        } else {
            $solarDate = date('m-d', strtotime('+' . $this->couponConfig['birthdayCouponReserveDays'] . ' days', strtotime($today)));
            $issueMonth = explode('-', $solarDate)[0];
            if ($thisMonth == 12 && $issueMonth == 1) {
                $issueYear += 1;
            }
        }

        return $issueYear;
    }
}
