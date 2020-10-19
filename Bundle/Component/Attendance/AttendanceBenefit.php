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

namespace Bundle\Component\Attendance;


use Component\Coupon\Coupon;
use Component\Database\DBTableField;
use Component\Mileage\Mileage;
use Exception;
use Framework\Object\SimpleStorage;
use Framework\Utility\ArrayUtils;
use Framework\Utility\DateTimeUtils;

/**
 * Class AttendanceBenefit
 * @package Bundle\Component\Attendance
 * @author  yjwee
 */
class AttendanceBenefit extends \Component\AbstractComponent
{
    /** @var  SimpleStorage */
    private $benefitStorage;
    /** @var  SimpleStorage */
    private $resultStorage;
    private $smsReceivers = [];

    /**
     * @inheritDoc
     */
    public function __construct()
    {
        parent::__construct();
        $session = \App::getInstance('session')->get(\Component\Member\Member::SESSION_MEMBER_LOGIN);
        $this->resultStorage = new SimpleStorage();
        $this->benefitStorage = new SimpleStorage();
        $this->benefitStorage->set('memNo', $session['memNo']);
        $this->benefitStorage->set('cellPhone', $session['cellPhone']);
        $this->benefitStorage->set('smsFl', $session['smsFl']);
        $this->benefitStorage->set('groupSno', $session['groupSno']);
        $this->benefitStorage->set('mallSno', $session['mallSno']);

        $this->resultStorage->set('total', 0);
        $this->resultStorage->set('success', 0);
    }

    /**
     * 혜택 지급 실행
     *
     * @throws Exception
     */
    public function benefit()
    {
        if (\Component\Member\Util\MemberUtil::getInstance()->isDefaultMallMemberSession()) {
            if ($this->benefitStorage->get('benefitFl', '') === 'mileage') {
                $this->benefitByMileage();
            } elseif ($this->benefitStorage->get('benefitFl', '') === 'coupon') {
                $this->benefitByCoupon();
            } else {
                throw new \RuntimeException(__('지급 가능한 혜택을 선택해주시기 바랍니다.'));
            }
        } else {
            $format = 'only the default mall is eligible for the attendance check. your mall number is %d';
            \App::getInstance('logger')->info(sprintf($format, $this->benefitStorage->get('mallSno', 0)));
        }
    }

    /**
     * 마일리지 혜택 지급
     *
     * @throws Exception
     */
    public function benefitByMileage()
    {
        if ($this->benefitStorage->get('attendanceSno', '') === '') {
            throw new \RuntimeException(__('이벤트 번호가 없습니다.'));
        }
        if ($this->benefitStorage->get('benefitMileage', -1) < 1) {
            throw new \RuntimeException(__('지급할 마일리지가 없습니다.'));
        }

        $mileage = \App::load(Mileage::class);
        $mileage->setIsTran(false);
        $code = $mileage::REASON_CODE_GROUP . $mileage::REASON_CODE_ETC;
        $memNo = $this->benefitStorage->get('memNo');
        $benefitMileage = $this->benefitStorage->get('benefitMileage');
        $attendanceSno = $this->benefitStorage->get('attendanceSno');
        $checkSno = $this->benefitStorage->get('checkSno');
        $title = $this->benefitStorage->get('title', __('출석체크 마일리지 지급'));
        $result = $mileage->setMemberMileage($memNo, $benefitMileage, $code, 'm', $attendanceSno, $checkSno, '출석체크 참여 (' . $title . ')');
        if ($result) {
            $this->resultStorage->increase('success');
            $this->resultStorage->add('benefitSuccessMemNo', $memNo);
        }
        unset($mileage);
    }

    /**
     * 쿠폰 혜택 지급
     *
     */
    public function benefitByCoupon()
    {
        /** @var \Bundle\Component\Coupon\Coupon $coupon */
        $coupon = \App::load(Coupon::class);
        $couponNo = $this->benefitStorage->get('benefitCouponSno');
        $memGroupNo = $this->benefitStorage->get('groupSno');
        $memNo = $this->benefitStorage->get('memNo');
        $coupon->setAutoCouponMemberSave('attend', $memNo, $memGroupNo, $couponNo);
        $this->_addSmsReceiverByAutoCoupon($coupon->getResultStorage());
    }

    /**
     * 출석체크 자동 발급 쿠폰 혜택 대상자 sms 발송 리스트에 추가
     *
     * @param SimpleStorage $storage
     */
    private function _addSmsReceiverByAutoCoupon(SimpleStorage $storage)
    {
        if ($storage->get('success', 0) > 0) {
            $this->resultStorage->increase('success');
            $this->resultStorage->add('benefitSuccessMemNo', $this->benefitStorage->get('memNo'));
            $smsReceiversByCoupon = $storage->get('smsReceivers', []);
            $receiverKey = $this->benefitStorage->get('benefitCouponSno') . '_' . $this->benefitStorage->get('memNo');
            $smsReceiver = $smsReceiversByCoupon[$receiverKey];
            if ($smsReceiver['couponEventAttendanceSmsType'] === 'y') {
                $smsReceiver['memNo'] = $this->benefitStorage->get('memNo');
                $smsReceiver['cellPhone'] = $this->benefitStorage->get('cellPhone');
                $smsReceiver['smsFl'] = $this->benefitStorage->get('smsFl');
                $smsReceiver['smsCode'] = 'COUPON_LOGIN';
                $this->smsReceivers[$this->benefitStorage->get('memNo')] = $smsReceiver;
            }
        }
    }

    /**
     * 수동 혜택 지급 검증
     *
     * @throws Exception
     */
    public function validateBenefitByManual()
    {
        if ($this->benefitStorage->get('attendanceSno', '') === '') {
            throw new \RuntimeException(__('이벤트 번호가 없습니다.'));
        }
        if ($this->benefitStorage->get('targetFl', '') === '') {
            throw new \RuntimeException(__('대상회원 선택을 해주세요.'));
        }
        $isTargetFlSelect = $this->benefitStorage->get('targetFl', '') === 'select';
        if ($isTargetFlSelect && \count($this->benefitStorage->get('layerChk', [])) > 0) {
            $bindParam = [];
            foreach ($this->benefitStorage->get('layerChk', []) as $item) {
                $this->db->bind_param_push($bindParam, 'i', $item);
            }
            $strSQL = 'SELECT /* 출석체크 혜택 수동 지급 기준몰 회원 검증 */ m.memNo, m.groupSno FROM ' . DB_MEMBER;
            $implodeMemberNo = implode(',', array_fill(0, \count($bindParam) - 1, '?'));
            $strSQL .= ' AS m WHERE m.mallSno=' . DEFAULT_MALL_NUMBER . ' AND m.memNo IN(' . $implodeMemberNo . ')';
            $resultSet = $this->db->query_fetch($strSQL, $bindParam);
            $this->benefitStorage->set('layerChk', ArrayUtils::getSubArrayByKey($resultSet, 'memNo'));
            $this->benefitStorage->set('groupSno', ArrayUtils::getSubArrayByKey($resultSet, 'groupSno'));
        }
        if ($isTargetFlSelect && \count($this->benefitStorage->get('layerChk', [])) < 1) {
            throw new \RuntimeException(__('선택된 회원이 없습니다.'));
        }

        $benefitFl = $this->benefitStorage->get('benefitFl', '');
        if ($benefitFl === '') {
            throw new \RuntimeException(__('지급할 혜택을 선택해 주세요.'));
        }

        if ($benefitFl === 'mileage' && $this->benefitStorage->get('benefitMileage', -1) < 1) {
            throw new \RuntimeException(__('지급할 마일리지를 입력해 주세요.'));
        }

        if ($benefitFl === 'coupon' && $this->benefitStorage->get('benefitCouponSno', -1) < 1) {
            throw new \RuntimeException(__('지급할 쿠폰을 선택해 주세요.'));
        }
        if ($this->getCount(DB_ATTENDANCE, '1', ' WHERE sno=' . $this->benefitStorage->get('attendanceSno')) < 1) {
            throw new \RuntimeException(__('이벤트를 찾을 수 없습니다.'));
        }
    }

    /**
     * 수동 혜택 지급
     *
     * @throws Exception
     */
    public function benefitByManual()
    {
        $this->validateBenefitByManual();

        if ($this->benefitStorage->get('targetFl', '') === 'search') {
            $check = new AttendanceCheck();
            $this->benefitStorage->set('conditionDtFl', 'y');
            $this->benefitStorage->set('benefitDtFl', 'n');
            $this->benefitStorage->set('sno', $this->benefitStorage->get('attendanceSno', ''));
            $lists = $check->lists($this->benefitStorage->all(), null, null);
            $arrMemNo = $arrGroupSno = [];
            foreach ($lists as $item) {
                if ($item['mallSno'] === DEFAULT_MALL_NUMBER) {
                    $arrMemNo[] = $item['memNo'];
                    $arrGroupSno[] = $item['groupSno'];
                }
            }
            $this->benefitStorage->set('layerChk', $arrMemNo);
            $this->benefitStorage->set('groupSno', $arrGroupSno);
            unset($lists, $arrMemNo, $arrGroupSno, $check);
        }

        $arrMemNo = $this->benefitStorage->get('layerChk', []);
        $arrGroupSno = $this->benefitStorage->get('groupSno', []);
        $this->resultStorage->set('total', \count($arrMemNo));

        $coupon = \App::load(Coupon::class);
        $couponNo = $this->benefitStorage->get('benefitCouponSno');
        foreach($arrMemNo as $k => $v){ //검색한 전체회원 쿠폰전송할 경우 선택한 쿠폰이 발급불가한 회원이 한명이라도 있을경우 무조건 불가 처리
            $useCouponData = $coupon->getAutoCouponUsable('attend', $v, $arrGroupSno[$k], $couponNo);
            if(empty($useCouponData) === true){
                throw new \RuntimeException(__('검색된 회원 중 쿠폰 발급이 불가한 회원이 포함되어 있습니다.'));
            } else {
                if($useCouponData[0]['couponAmountType'] == 'y'){ // 쿠폰발급개수 제한일경우
                    $couponCnt = $useCouponData[0]['couponAmount'] - $useCouponData[0]['couponSaveCount'];
                    if($couponCnt < count($arrMemNo)){
                        throw new \RuntimeException(__('검색된 회원 중 쿠폰 발급이 불가한 회원이 포함되어 있습니다.'));
                    }
                }
            }
        }
        foreach ($arrMemNo as $index => $memNo) {
            $this->benefitStorage->set('memNo', $memNo);
            $this->benefitStorage->set('groupSno', $arrGroupSno[$index]);
            if ($this->benefitStorage->get('benefitFl', '') === 'mileage') {
                $this->benefitByMileage();
            } elseif ($this->benefitStorage->get('benefitFl', '') === 'coupon') {
                $this->benefitByCoupon();
            } else {
                throw new \RuntimeException(__('지급 가능한 혜택을 선택해주시기 바랍니다.'));
            }
        }

        if ($this->resultStorage->get('success', 0) > 0) {
            $whereBindParams = [];
            $this->db->bind_param_push($whereBindParams, 'i', $this->benefitStorage->get('attendanceSno'));
            $whereMemberBindParams = [];
            foreach ($this->resultStorage->get('benefitSuccessMemNo', []) as $index => $successMemNo) {
                $this->db->bind_param_push($whereMemberBindParams, 'i', $successMemNo);
            }
            $this->db->query_reset();
            $strSQL = 'SELECT /* 출석체크 수동지급 대상 조회 */ ac.sno FROM ' . DB_ATTENDANCE_CHECK;
            $implodeMemberNo = implode(',', array_fill(0, \count($whereMemberBindParams) - 1, '?'));
            $strSQL .= ' AS ac WHERE ac.attendanceSno=? AND ac.memNo IN (' . $implodeMemberNo . ')';
            $arrBind = array_merge([array_shift($whereBindParams) . array_shift($whereMemberBindParams)], $whereBindParams, $whereMemberBindParams);
            $resultSet = $this->db->query_fetch($strSQL, $arrBind);
            $tableAttendanceCheckFields = DBTableField::getBindField('tableAttendanceCheck');
            $updateArrData = ['benefitDt' => DateTimeUtils::dateFormat('Y-m-d H:i:s', 'now')];
            $updateBindParams = $this->db->updateBinding($tableAttendanceCheckFields, $updateArrData, ['benefitDt']);
            $whereBindParams = [];
            foreach ($resultSet as $index => $resultRow) {
                $this->db->bind_param_push($whereBindParams, 'i', $resultRow['sno']);
            }
            $strWhere = 'sno IN (' . implode(',', array_fill(0, \count($whereBindParams) - 1, '?')) . ')';
            $updateArrBindParam = array_merge([array_shift($updateBindParams['bind']) . array_shift($whereBindParams),], $updateBindParams['bind'], $whereBindParams);
            $this->db->set_update_db(DB_ATTENDANCE_CHECK, $updateBindParams['param'], $strWhere, $updateArrBindParam);
        }
    }

    /**
     * setRequest
     *
     */
    public function setRequest()
    {
        $request = \App::getInstance('request');
        $this->benefitStorage = new SimpleStorage($request->post()->all());
        $this->benefitStorage->del('sno');
        $this->setAttendanceSno($request->post()->get('sno'));
    }

    public function setCheckSno($sno)
    {
        $this->benefitStorage->set('checkSno', $sno);
    }

    public function setAttendanceSno($sno)
    {
        $this->benefitStorage->set('attendanceSno', $sno);
    }

    public function setBenefitMileage($mileage)
    {
        $this->benefitStorage->set('benefitMileage', $mileage);
    }

    public function setTitle($title)
    {
        $this->benefitStorage->set('title', $title);
    }

    public function setBenefitFl($benefitFl)
    {
        $this->benefitStorage->set('benefitFl', $benefitFl);
    }

    public function setTargetFl($targetFl)
    {
        $this->benefitStorage->set('targetFl', $targetFl);
    }

    public function setBenefitCouponSno($couponSno)
    {
        $this->benefitStorage->set('benefitCouponSno', $couponSno);
    }

    /**
     * @return SimpleStorage
     */
    public function getResultStorage()
    {
        return $this->resultStorage;
    }

    /**
     * @return array
     */
    public function getSmsReceivers()
    {
        return $this->smsReceivers;
    }
}

