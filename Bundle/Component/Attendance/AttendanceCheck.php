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

use Component\Attendance\Attendance;
use Component\Attendance\AttendanceBenefit;
use Component\Attendance\AttendanceReply;
use Component\Database\DBTableField;
use Component\Member\Member;
use Component\Validator\Validator;
use Controller\Front\Event\AttendancePsController;
use Framework\Database\DBTool;
use Framework\Debug\Exception\AlertBackException;
use Framework\Object\SimpleStorage;
use Framework\Utility\DateTimeUtils;
use Component\Coupon\Coupon;
/**
 * Class AttendanceCheck 출석체크
 * @package Bundle\Component\Attendance
 * @author  yjwee
 */
class AttendanceCheck extends \Component\AbstractComponent
{
    // '=' . __('통합검색') . '='
    // __('이름')
    // __('아이디')
    const COMBINE_SEARCH = [
        'all'   => '=통합검색=',
        'memNm' => '이름',
        'memId' => '아이디',
    ];

    // __('최종참여일')
    // __('혜택지급일')
    // __('아이디')
    const RANGE_SEARCH = [
        'modDt'       => '최종참여일',
        'conditionDt' => '조건달성일',
        'benefitDt'   => '혜택지급일',
    ];

    /** @var  SimpleStorage */
    protected $requestStorage;
    /** @var  SimpleStorage */
    protected $checkStorage;
    /** @var  SimpleStorage */
    protected $attendanceStorage;
    /** @var  SimpleStorage */
    protected $replyStorage;

    // __('출석이 완료되었습니다. 내일도 참여해주세요.')
    protected $attendanceMessage = '출석이 완료되었습니다. 내일도 참여해주세요.';

    /** @var \Bundle\Component\Attendance\AttendanceBenefit $attendanceBenefit */
    protected $attendanceBenefit;

    private $iSearchCount = 0;

    /**
     * @inheritDoc
     */
    public function __construct(AttendanceBenefit $benefit = null, DBTool $db = null)
    {
        parent::__construct($db);
        $this->tableFunctionName = 'tableAttendanceCheck';
        $this->tableName = DB_ATTENDANCE_CHECK;
        if ($benefit === null) {
            $benefit = \App::load(AttendanceBenefit::class);
        }
        $this->attendanceBenefit = $benefit;
    }

    /**
     * attendance
     *
     * @param      $methodFl
     * @param null $arrData
     *
     * @return string
     * @throws \Exception
     */
    public function attendance($methodFl, $arrData = null)
    {
        if (!\Component\Member\Util\MemberUtil::getInstance()->isDefaultMallMemberSession()) {
            throw new \RuntimeException(__('기준몰 회원이 아닙니다.'));
        }
        if ($arrData === null) {
            $request = \App::getInstance('request');
            $arrData = $request->post()->all();
        }
        $dataStorage = new SimpleStorage($arrData);
        $this->setRequestStorage($dataStorage);
        $this->setReplyStorage($dataStorage);
        try {
            switch ($methodFl) {
                case AttendancePsController::INSERT_REPLY:
                    $methodFl = 'reply';
                    break;
                case AttendancePsController::INSERT_STAMP:
                case AttendancePsController::UPDATE_STAMP:
                    $methodFl = 'stamp';
                    break;
                default:
                    throw new \RuntimeException(__('존재하지 않는 출석체크 방법입니다.'));
                    break;
            }

            /** @var \Bundle\Component\Attendance\Attendance $attendance */
            $attendance = \App::load(Attendance::class);
            $this->attendanceStorage = $attendance->getDataByActive($methodFl);
            if ($this->requestStorage->get('attendanceSno', '') === '') {
                $this->requestStorage->set('attendanceSno', $this->attendanceStorage->get('sno', ''));
            }
            if ($this->attendanceStorage->get('sno', '') === '') {
                throw new \RuntimeException(__('진행중인 출석체크가 없습니다.'), 200);
            }
            $attendance->checkDevice($this->attendanceStorage->get('deviceFl'));
            $groupFl = $this->attendanceStorage->get('groupFl');
            $attendance->checkGroup($groupFl, $this->attendanceStorage->get('groupSno'));
            $this->attendanceMessage = $this->attendanceStorage->get('completeComment', __('출석이 완료되었습니다. 내일도 참여해주세요.'));

            $session = \App::getInstance('session');
            $this->getAttendanceCheck($this->requestStorage->get('attendanceSno'), $session->get('member.memNo'));
            if ($this->hasBenefit()) {
                throw new \RuntimeException(__('이미 출석체크 이벤트 혜택을 지급 받으셨습니다.'));
            }
            $this->checkAttendance();

            if (\count($this->checkStorage->all()) > 0) {
                // 조건 달성 한 상태이지만 혜택을 받지 못한 경우
                if ($this->isComplete() && $this->isBenefitCondition()
                    && $this->isBenefitGiveFlAuto()) {
                    $logger = \App::getInstance('logger');
                    $logger->info(__METHOD__ . ' isComplete && isBenefitCondition && benefitGive auto');
                    $this->giveBenefitAutoByComplete();

                    return $this->attendanceMessage;
                }
                $this->updateAttendance();
            } else {
                $this->insertAttendance();
            }

            if ($methodFl === 'reply') {
                /** @var \Bundle\Component\Attendance\AttendanceReply $reply */
                $reply = \App::load(AttendanceReply::class);
                $arrReplyData = $this->replyStorage->all();
                $arrReplyData['checkSno'] = $this->checkStorage->get('sno');
                $reply->reply($arrReplyData);
            }
        } catch (\Exception $e) {
            $logger = \App::getInstance('logger');
            $exceptionMessage = __METHOD__ . ', ' . $e->getFile() . '[' . $e->getLine() . '], ' . $e->getMessage();
            $logger->error($exceptionMessage, $e->getTrace());

            return $e->getMessage();
        }

        return $this->attendanceMessage;
    }

    /**
     * @param SimpleStorage $data
     */
    public function setRequestStorage($data)
    {
        $session = \App::getInstance('session');
        $data->set('attendanceSno', $data->get('sno', ''));
        $data->del('sno');
        $data->set('sno', $data->get('checkSno', ''));
        $data->set('memNo', $session->get('member.memNo', ''));

        $this->requestStorage = $data;
    }

    /**
     * @param SimpleStorage $data
     */
    public function setReplyStorage($data)
    {
        $session = \App::getInstance('session');
        $memberNo = $session->get(Member::SESSION_MEMBER_LOGIN . '.memNo');
        $this->replyStorage = new SimpleStorage();
        $this->replyStorage->set('attendanceSno', $data->get('sno'));
        $this->replyStorage->set('sno', $data->get('checkSno', ''));
        $this->replyStorage->set('memNo', $memberNo);
        $this->replyStorage->set('reply', $data->get('reply'));
    }

    /**
     * getData
     *
     * @param $attendanceSno
     * @param $memNo
     *
     * @return SimpleStorage
     */
    public function getAttendanceCheck($attendanceSno, $memNo)
    {
        $arrData = [
            $attendanceSno,
            $memNo,
        ];
        $arrWhere = [
            'attendanceSno',
            'memNo',
        ];
        $this->checkStorage = new SimpleStorage($this->db->getData($this->tableName, $arrData, $arrWhere));

        return $this->checkStorage;
    }

    /**
     * 혜택을 지급 받았는지 판단하는 함수
     *
     * @return bool
     */
    public function hasBenefit()
    {
        $startDt = $this->attendanceStorage->get('startDt');
        $benefitDt = $this->checkStorage->get('benefitDt');

        // 매번 지급 이벤트가 지급안되는 현상으로 인해 매번 지급형은 false 반환하게끔 수정
        return ($startDt < $benefitDt) && ($this->isEachConditionFl() === false);
    }

    /**
     *  출석체크 이벤트조건이 출석할 때마다 지급인지 여부
     *
     * @return bool 출석할 때마다 혜택지급 true
     */
    protected function isEachConditionFl()
    {
        return $this->attendanceStorage->get('conditionFl') === 'each';
    }

    /**
     * 오늘 출석체크 참여 여부 확인 함수
     *
     * @throws AlertBackException
     */
    public function checkAttendance()
    {
        $today = gd_date_format('Y-m-d', 'now');
        $modDt = gd_date_format('Y-m-d', $this->checkStorage->get('modDt'));
        $regDt = gd_date_format('Y-m-d', $this->checkStorage->get('regDt'));

        if ($today === $modDt || $today === $regDt) {
            throw new AlertBackException(__('이미 출석체크에 참여하셨습니다. 내일 다시 출석해주세요.'));
        }
    }

    /**
     * 출석체크 달성 여부 반환 함수
     *
     * @return bool
     */
    public function isComplete()
    {
        $startDt = $this->attendanceStorage->get('startDt');
        $conditionDt = $this->checkStorage->get('conditionDt');

        // 매번 지급 이벤트가 지급안되는 현상으로 인해 매번 지급형은 false 반환하게끔 수정
        return $startDt < $conditionDt && ($this->isEachConditionFl() === false);
    }

    /**
     * 현재 조회된 출석체크 이벤트와 출석정보를 가지고 혜택 지급 여부를 판단하는 함수
     *
     * @return bool 매번 지급 또는 출석으로 인한 누적/연속 출석 횟수가 달성한 경우 true 아니면 false
     */
    public function isBenefitCondition()
    {
        if ($this->isEachConditionFl()) {
            return true;
        }
        $completeCount = $this->attendanceStorage->get('conditionCount');
        $attendanceCount = $this->checkStorage->get('attendanceCount', 0);

        return $completeCount === ($attendanceCount + 1);
    }

    /**
     * isBenefitGiveFlAuto
     *
     * @return bool
     */
    protected function isBenefitGiveFlAuto(): bool
    {
        return $this->attendanceStorage->get('benefitGiveFl') === 'auto';
    }

    /**
     * giveBenefitAutoByComplete
     */
    public function giveBenefitAutoByComplete()
    {
        $this->_initAttendanceBenefit($this->attendanceStorage, $this->checkStorage->get('sno'));
        $this->attendanceBenefit->benefit();

        $this->validateUpdate();
        $arrData = $this->requestStorage->all();
        $arrData['sno'] = $this->checkStorage->get('sno');
        $arrData['benefitDt'] = gd_date_format('Y-m-d G:i:s', 'now');
        $this->update($arrData);
        $this->attendanceMessage = $this->attendanceStorage->get('conditionComment', __('축하드립니다! 출석목표가 달성되었습니다.'));
    }

    private function _initAttendanceBenefit(SimpleStorage $storage, $attendanceCheckSno)
    {
        $this->attendanceBenefit->setBenefitFl($storage->get('benefitFl'));
        $this->attendanceBenefit->setTitle($storage->get('title'));
        $this->attendanceBenefit->setAttendanceSno($storage->get('sno'));
        $this->attendanceBenefit->setCheckSno($attendanceCheckSno);
        $this->attendanceBenefit->setBenefitMileage($storage->get('benefitMileage'));
        $this->attendanceBenefit->setBenefitCouponSno($storage->get('benefitCouponSno'));
    }

    /**
     * validateUpdate
     *
     * @throws \Exception
     */
    public function validateUpdate()
    {
        if ($this->requestStorage === null) {
            throw new \RuntimeException(__('검증에 필요한 정보가 없습니다.'));
        }

        $data = $this->requestStorage;

        if (!Validator::required($data->get('attendanceSno'))) {
            throw new \RuntimeException(__('이벤트 번호가 없습니다.'));
        }
        if (!Validator::required($data->get('memNo'))) {
            throw new \RuntimeException(__('회원 번호가 없습니다.'));
        }
        if (!Validator::required($data->get('sno'))) {
            throw new \RuntimeException(__('출석체크 이벤트 번호가 없습니다.'));
        }

        $v = $this->v;
        $v->init();
        // __('출석체크 이벤트 번호가 없습니다.')
        // __('이벤트 번호가 없습니다.')
        // __('회원 번호가 없습니다.')
        $v->add('sno', 'number', true, '{출석체크 이벤트 번호가 없습니다.}');
        $v->add('attendanceSno', 'number', true, '{이벤트 번호가 없습니다.}');
        $v->add('memNo', 'number', true, '{회원 번호가 없습니다.}');

        $validateData = $data->all();
        if ($v->act($validateData, true) === false) {
            throw new \RuntimeException(implode("\n", $v->errors));
        }
        $data->setData($validateData);
    }

    /**
     * update
     *
     * @param null $arrData
     *
     * @return bool
     */
    public function update($arrData = null)
    {
        if ($arrData === null) {
            $arrData = $this->requestStorage->all();
        }
        $bindField = DBTableField::getBindField($this->tableFunctionName);
        $arrBind = $this->db->updateBinding($bindField, $arrData, array_keys($arrData));
        $this->db->bind_param_push($arrBind['bind'], 'i', $arrData['sno']);

        $result = $this->db->set_update_db(DB_ATTENDANCE_CHECK, $arrBind['param'], 'sno=?', $arrBind['bind']);

        return $result;
    }

    /**
     * updateAttendance
     *
     * @throws \Exception
     */
    public function updateAttendance()
    {
        $this->requestStorage->set('sno', $this->checkStorage->get('sno'));
        $this->validateUpdate();
        $arrData = $this->requestStorage->all();
        $arrData['attendanceCount'] = $this->checkStorage->get('attendanceCount', 0) + 1;
        if ($this->attendanceStorage->get('conditionFl') === 'continue') {
            $arrData['attendanceCount'] = $this->getContinueCount() + 1;
        }
        $history = json_decode($this->checkStorage->get('attendanceHistory'))->history;
        $history[] = gd_date_format('Y-m-d', 'now');
        $arrData['attendanceHistory'] = json_encode(['history' => $history]);

        // 조건 달성으로 인한 혜택 지급 처리
        if ($this->isBenefitGiveFlAuto()) {
            $this->benefitAutoByAttendance($arrData);
        } else {
            $this->benefitManualByAttendance($arrData);
        }
        $isComplete = $this->attendanceStorage->get('conditionCount') < $arrData['attendanceCount'];
        if ($isComplete && ($this->isEachConditionFl() === false)) {
            throw new \RuntimeException(__('이미 출석체크 이벤트를 달성하였습니다.'));
        }

        $this->update($arrData);
    }

    /**
     * 연속된 출석 일수를 구하는 함수
     *
     * @return int
     */
    public function getContinueCount()
    {
        // 연속된 출석인지에 따라 t/f 리턴, 뒤에서 부터 연속된 출석횟수 체크 해야함
        $count = 0;
        $history = $this->getAttendanceHistoryByCheckStorage();
        if ($history === null) {
            return $count;
        }
        $history = array_reverse($history);
        $length = \count($history);
        for ($i = 0; $i < $length; $i++) {
            $nextHistory = $history[$i + 1];
            if ($nextHistory === null) {
                $count++;

                return $count;
            }
            $currentHistory = $history[$i];
            $intervalDay = (int) DateTimeUtils::intervalDay($nextHistory, $currentHistory);
            if ($intervalDay !== 1) {
                $count++;
                break;
            }
            $count++;
        }

        return $count;
    }

    /**
     * 출석체크 참석 년월일 배열 반환함수
     * es_attendanceCheck > attendanceHistory.history
     *
     * @return array 출석체크 참여한 년월일
     */
    protected function getAttendanceHistoryByCheckStorage()
    {
        return json_decode($this->checkStorage->get('attendanceHistory', ''))->history;
    }

    /**
     * benefitAutoByAttendance
     *
     * @param $arrData
     *
     * @throws \Exception
     */
    public function benefitAutoByAttendance(&$arrData)
    {
        if ($this->isBenefitCondition() && $this->isBenefitGiveFlAuto()) {
            $this->_initAttendanceBenefit($this->attendanceStorage, $this->checkStorage->get('sno'));
            $this->attendanceBenefit->benefit();

            $arrData['benefitDt'] = $arrData['conditionDt'] = gd_date_format('Y-m-d G:i:s', 'now');
            $this->attendanceMessage = $this->attendanceStorage->get('conditionComment', __('축하드립니다! 출석목표가 달성되었습니다.'));
        }
    }

    public function benefitManualByAttendance(&$arrData)
    {
        if ($this->isBenefitCondition()) {
            $this->_initAttendanceBenefit($this->attendanceStorage, $this->checkStorage->get('sno'));
            $arrData['conditionDt'] = gd_date_format('Y-m-d G:i:s', 'now');
            $this->attendanceMessage = $this->attendanceStorage->get('conditionComment', __('축하드립니다! 출석목표가 달성되었습니다.'));
        }
    }

    /**
     * insertAttendance
     *
     * @throws \Exception
     */
    public function insertAttendance()
    {
        $this->validateInsert();
        $arrData = $this->requestStorage->all();
        $arrData['attendanceCount'] = 1;
        $history[] = gd_date_format('Y-m-d', 'now');
        $arrData['attendanceHistory'] = json_encode(['history' => $history]);
        if ($this->isBenefitGiveFlAuto()) {
            $this->benefitAutoByAttendance($arrData);
        } else {
            $this->benefitManualByAttendance($arrData);
        }
        $checkSno = $this->insert($arrData);
        $this->checkStorage->set('sno', $checkSno);
    }

    /**
     * validateInsert
     *
     * @throws \Exception
     */
    public function validateInsert()
    {
        if ($this->requestStorage === null) {
            throw new \RuntimeException(__('검증에 필요한 정보가 없습니다.'));
        }

        $data = $this->requestStorage;

        if (!Validator::required($data->get('attendanceSno'))) {
            throw new \RuntimeException(__('이벤트 번호가 없습니다.'));
        }
        if (!Validator::required($data->get('memNo'))) {
            throw new \RuntimeException(__('회원 번호가 없습니다.'));
        }

        $v = $this->v;
        $v->init();
        // __('이벤트 번호가 없습니다.')
        // __('회원 번호가 없습니다.')
        $v->add('attendanceSno', 'number', true, '{이벤트 번호가 없습니다.}');
        $v->add('memNo', 'number', true, '{회원 번호가 없습니다.}');
        $validateData = $data->all();
        if ($v->act($validateData, true) === false) {
            throw new \RuntimeException(implode("\n", $v->errors));
        }
        $data->setData($validateData);
    }

    /**
     * insert
     *
     * @param null $arrData
     *
     * @return int|string
     */
    public function insert($arrData = null)
    {
        if ($arrData === null) {
            $arrData = $this->requestStorage->all();
        }
        $tableAttendanceCheck = DBTableField::tableAttendanceCheck();
        $arrBind = $this->db->get_binding($tableAttendanceCheck, $arrData, 'insert', array_keys($arrData));
        $this->db->set_insert_db(DB_ATTENDANCE_CHECK, $arrBind['param'], $arrBind['bind'], 'y');
        unset($arrBind);

        return $this->db->insert_id();
    }

    /**
     * 출석체크 상세보기 리스트 조회
     *
     * @param array $arrData
     * @param int   $offset
     * @param int   $limit
     *
     * @return array
     */
    public function lists(array $arrData, $offset = 1, $limit = 5)
    {
        $arrBind = $arrWhere = [];

        $this->db->bindParameter('sno', $arrData, $arrBind, $arrWhere, $this->tableFunctionName, 'ach', 'attendanceSno');
        $this->db->bindParameterByKeyword(self::COMBINE_SEARCH, $arrData, $arrBind, $arrWhere, 'tableMember', 'm');
        $arrData[$arrData['rangeKey']] = $arrData['rangeDt'];
        $this->db->bindParameterByDateTimeRange($arrData['rangeKey'], $arrData, $arrBind, $arrWhere, $this->tableFunctionName, 'ach');
        if (!empty($arrWhere)) {
            $grep = preg_grep('/ach\.modDt/', $arrWhere);
            if (!empty($grep)) {
                $grepKey = key($grep);
                $arrWhere[$grepKey] = preg_replace('/(.*Dt)(,*)/', 'IF($1 IS NULL, ach.regDt, $1)$2', $grep[$grepKey]);
            }
        }
        if ($arrData['conditionDtFl'] === 'y') {
            $arrWhere[] = 'ach.conditionDt IS NOT NULL';
        } elseif ($arrData['conditionDtFl'] === 'n') {
            $arrWhere[] = 'ach.conditionDt IS NULL';
        }

        if ($arrData['benefitDtFl'] === 'y') {
            $arrWhere[] = 'ach.benefitDt IS NOT NULL';
        } elseif ($arrData['benefitDtFl'] === 'n') {
            $arrWhere[] = 'ach.benefitDt IS NULL';
        }

        $this->db->strField = 'ach.*, m.memNm, m.memId, m.groupSno, m.mallSno';
        $this->db->strWhere = implode(' AND ', $arrWhere);
        $this->db->strOrder = ' ach.regDt DESC';
        $this->db->strJoin = ' JOIN ' . DB_MEMBER . ' AS m ON ach.memNo=m.memNo';
        if ($offset !== null && $limit !== null) {
            $this->db->strLimit = ($offset - 1) * $limit . ', ' . $limit;
        }

        $arrQuery = $this->db->query_complete();
        $query = 'SELECT /* 출석체크 리스트 조회 */ ' . array_shift($arrQuery) . ' FROM ' . $this->tableName;
        $query .= ' AS ach ' . implode(' ', $arrQuery);
        $resultSet = $this->db->query_fetch($query, $arrBind);
        $this->iSearchCount = $this->db->query_count($arrQuery, $this->tableName . ' AS ach ', $arrBind);
        unset($arrBind, $arrWhere, $arrQuery);

        return $resultSet;
    }

    public function getSmsReceiversByCouponBenefit()
    {
        return $this->attendanceBenefit->getSmsReceivers();
    }

    /**
     * @return SimpleStorage
     */
    public function getCheckStorage()
    {
        return $this->checkStorage;
    }

    /**
     * @return SimpleStorage
     */
    public function getAttendanceStorage()
    {
        return $this->attendanceStorage;
    }

    public function getSearchCount()
    {
        return gd_isset($this->iSearchCount, 0);
    }

    /**
     * 출석체크페이지에서 쿠폰 수동지급시
     * 회원별 지급가능한 쿠폰 식별
     * @author sojeong
     */
    public function getAttendanceMemberCoupon($memberList)
    {
        $arrBind = [];
        $memberInfo = [];
        $tmp = [];
        $couponClass = new Coupon();

        // 출석체크 쿠폰 정보 호출
        $this->db->strField = '*';
        $this->db->strWhere = 'couponEventType = \'attend\' AND couponType=\'y\' ';
        $this->db->strOrder = 'regDt DESC';
        $query = $this->db->query_complete();
        $strSQL = 'SELECT ' . array_shift($query) . ' FROM ' . DB_COUPON . implode(' ', $query);
        $coupons = $this->db->query_fetch($strSQL, $arrBind, false);

        // 쿠폰배열 재정렬
        $couponInfo = [];
        foreach ($coupons as $coupon) {
            $couponInfo[$coupon['couponNo']] = $coupon;
            if($coupon['couponAmountType'] == 'y'){ // 전체쿠폰수량제한 사용시
                //발급가능한 개수 저장
                $memberInfo['couponAmountInfo'][$coupon['couponNo']] = $coupon['couponAmount'] - $coupon['couponSaveCount'];
            }
            $memberInfo['couponInfo'][$coupon['couponNo']] = $coupon['couponNm'];
        }
        foreach($memberList as $member) {
            foreach($coupons as $couponData){
                $couponUseData = $couponClass->getAutoCouponUsable('attend', $member['memNo'], $member['groupSno'], $couponData['couponNo']);
                if(empty($couponUseData)===false){
                    foreach($couponUseData as $key){
                        $tmp[] = $key['couponNo'];
                    }
                }
            }
            $memberInfo[$member['memNo']] = implode("||", $tmp);
            unset($tmp);
        }
        return $memberInfo;
    }
}
