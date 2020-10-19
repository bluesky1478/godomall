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

use Component\Coupon\CouponAdmin;
use Component\Database\DBTableField;
use Component\Member\Group\Util as GroupUtil;
use Component\Member\Manager;
use Component\Storage\Storage;
use Component\Validator\Validator;
use Exception;
use Framework\Database\DBTool;
use Framework\Object\SimpleStorage;
use Framework\Utility\DateTimeUtils;
use Framework\Utility\StringUtils;

/**
 * Class Attendance
 * @package Bundle\Component\Attendance
 * @author  yjwee
 */
class Attendance extends \Component\AbstractComponent
{
    /** 통합검색 항목 */
    // '=' . __('통합검색') . '='
    // __('출석체크 이벤트명')
    // __('등록자')
    const COMBINE_SEARCH = [
        'all'       => '=통합검색=',
        'title'     => '출석체크 이벤트명',
        'managerNm' => '등록자',
    ];

    const NOT_FOUND_ACTIVE_ATTENDANCE = 999404;

    /** @var  SimpleStorage */
    private $requestStorage;

    /** @var  SimpleStorage */
    private $attendStorage;

    // __('진행중')
    // __('종료')
    // __('대기')
    private $activeFl = [
        'y' => '진행중',
        'n' => '종료',
        'w' => '대기',
    ];

    // __('PC쇼핑몰')
    // __('모바일쇼핑몰')
    // __('PC+모바일')
    private $deviceFl = [
        'pc'     => 'PC쇼핑몰',
        'mobile' => '모바일쇼핑몰',
        'all'    => 'PC+모바일',
    ];

    private $groupFl = [
        'all',
        'select',
    ];

    // __('스탬프형')
    // __('로그인형')
    // __('댓글형')
    private $methodFl = [
        'stamp' => '스탬프형',
        'login' => '로그인형',
        'reply' => '댓글형',
    ];

    // __('누적')
    // __('연속')
    // __('출석 시')
    private $conditionFl = [
        'sum'      => '누적',
        'continue' => '연속',
        'each'     => '출석 시',
    ];

    // __('자동지급')
    // __('수동지급')
    private $benefitGiveFl = [
        'auto'   => '자동지급',
        'manual' => '수동지급',
    ];

    private $benefitFl = [
        'mileage',
        'coupon',
    ];

    private $designHeadFl = [
        'default',
        'html',
    ];

    private $designBodyFl = [
        'stamp1',
        'stamp2',
        'reply1',
        'reply2',
    ];

    private $stampFl = [
        'default',
        'upload',
    ];

    private $iSearchCount = 0;

    /**
     * @inheritDoc
     */
    public function __construct(DBTool $db = null)
    {
        parent::__construct($db);
        $this->tableFunctionName = 'tableAttendance';
        $this->tableName = DB_ATTENDANCE;
    }

    public function delete($sno)
    {

        $where = 'sno = ?';
        $type = 'i';
        $arrBind = [
            $type,
            $sno,
        ];
        if (is_array($sno)) {
            $type = '';
            $where = 'sno IN (';
            foreach ($sno as $item) {
                if (!$this->v->required($item)) {
                    throw new Exception(__('출석체크 번호가 없습니다.'));
                }
                $type .= 'i';
                $where .= '?,';
            }
            $where = substr($where, 0, strlen($where) - 1);
            $where .= ')';
            $arrBind = $sno;
            array_unshift($arrBind, $type);
        } else {
            if (!$this->v->required($sno)) {
                throw new Exception(__('출석체크 번호가 없습니다.'));
            }
        }

        if ($this->countActiveByDelete($sno) > 0) {
            throw new Exception(__('진행중인 이벤트는 삭제할 수 없습니다.'));
        }

        if ($this->db->set_delete_db(DB_ATTENDANCE, $where, $arrBind) == 0) {
            throw new Exception(__('삭제 중 오류가 발생하였습니다.'));
        }
    }

    public function countActiveByDelete($sno)
    {
        $where = 'WHERE sno=' . $sno;

        if (is_array($sno)) {
            $where = 'WHERE sno IN (' . implode(', ', $sno) . ')';
        }

        $today = gd_date_format('Y-m-d H:i:s', 'today');
        $where .= ' AND \'' . $today . '\' BETWEEN startDt AND endDt';

        return $this->getCount(DB_ATTENDANCE, '1', $where);
    }

    public function register(array $arrData)
    {
        $this->setRequestStorage($arrData);
        $this->validateInsert();
        $this->validatePost();
        $this->uploadStamp();

        if ($this->insert() === 0) {
            throw new Exception(__('출석체크 이벤트 등록 중 오류가 발생하였습니다.'));
        };
    }

    public function setRequestStorage(array $data)
    {
        $this->requestStorage = new SimpleStorage($data);
        // 종료기간 제한없는 경우 종료일을 9999-12-31 로 설정한다.
        if ($this->requestStorage->get('eventEndDtFl', 'n') == 'y' && $this->requestStorage->get('endDt', '') == '') {
            $this->requestStorage->set('endDt', '9999-12-31');
        }

        // 전체회원 등급 참여 이벤트인 경우 선택된 회원등급을 삭제함
        if ($this->requestStorage->get('groupFl', '') == 'all') {
            $this->requestStorage->del('groupSno');
        } else {
            if ($this->requestStorage->has('groupSno')) {
                $groupSno = $this->requestStorage->get('groupSno');
                if (is_array($groupSno) && count($groupSno) > 0) {
                    $this->requestStorage->set('groupSno', json_encode($groupSno, JSON_UNESCAPED_SLASHES));
                } else {
                    $this->requestStorage->del('groupSno');
                }
            }
        }

        if ($this->requestStorage->get('conditionFl', '') == 'sum') {
            $this->requestStorage->set('conditionCount', $this->requestStorage->get('conditionCountBySum'));
        } elseif ($this->requestStorage->get('conditionFl', '') == 'continue') {
            $this->requestStorage->set('conditionCount', $this->requestStorage->get('conditionCountByContinue'));
        }

        // 상단 영역 html 직접 입력이면서 상단 영역 html 이 공백일 경우 기본 상단 영역으로 설정
        if ($this->requestStorage->get('designHeadFl', 'default') == 'html' && $this->requestStorage->get('designHead', '') == '') {
            $this->requestStorage->set('designHeadFl', 'default');
        }
        if ($this->requestStorage->has('stampPath') && $this->requestStorage->get('stampPath', '')['name'] == '') {
            $this->requestStorage->set('stampFl', 'default');
            $this->requestStorage->del('stampPath');
        }

        if ($this->requestStorage->get('completeComment', '') == '') {
            $this->requestStorage->set('completeComment', __('출석이 완료되었습니다. 내일도 참여해주세요.'));
        }

        if ($this->requestStorage->get('conditionComment', '') == '') {
            $this->requestStorage->set('conditionComment', __('축하드립니다! 출석목표가 달성되었습니다.'));
        }

        if ($this->requestStorage->get('methodFl', '') == 'login') {
            $this->requestStorage->del('designHeadFl');
            $this->requestStorage->del('designBodyFl');
            $this->requestStorage->del('designFooter');
            $this->requestStorage->del('stampFl');
        }
        $this->requestStorage->set('managerNo', \Session::get('manager.sno'));
    }

    /**
     * validateInsert
     *
     * @throws Exception
     */
    public function validateInsert()
    {
        if (!isset($this->requestStorage)) {
            throw new Exception(__('검증에 필요한 정보가 없습니다.'));
        }
        if ($this->requestStorage->get('managerNo', 0) < 1) {
            throw new Exception(__('관리자 번호가 없습니다.'));
        }
        $this->v->init();
        $this->addRules();
        $data = $this->requestStorage->all();
        if ($this->v->act($data, true) === false) {
            throw new Exception(implode("\n", $this->v->errors));
        }
        $this->requestStorage = new SimpleStorage($data);
    }

    public function addRules()
    {
        $data = $this->requestStorage;

        $this->v->add('title', '', true);
        $this->v->add('startDt', '', true);
        $this->v->add('endDt', '', true);
        // __('이벤트 활성여부')
        $this->v->add('activeFl', 'pattern', true, '{이벤트 활성여부}', '/^(' . implode('|', array_keys($this->activeFl)) . ')$/');
        // __('진행범위')
        $this->v->add('deviceFl', 'pattern', true, '{진행범위}', '/^(' . implode('|', array_keys($this->deviceFl)) . ')$/');
        // __('참여가능 회원등급')
        $this->v->add('groupFl', 'pattern', true, '{참여가능 회원등급}', '/^(' . implode('|', $this->groupFl) . ')$/');
        $this->v->add('groupSno', '');
        // __('출석방법')
        $this->v->add('methodFl', 'pattern', true, '{출석방법}', '/^(' . implode('|', array_keys($this->methodFl)) . ')$/');
        // __('이벤트 조건')
        $this->v->add('conditionFl', 'pattern', true, '{이벤트 조건}', '/^(' . implode('|', array_keys($this->conditionFl)) . ')$/');
        // 선택된 이벤트 조건에 따라 검증조건 추가
        if ($data->get('conditionFl', '') == 'sum') {
            $this->v->add('conditionCountBySum', 'number', true);
            $this->v->add('conditionCount', 'number', true);
        } elseif ($data->get('conditionFl', '') == 'continue') {
            $this->v->add('conditionCountByContinue', 'number', true);
            $this->v->add('conditionCount', 'number', true);
        }

        // __('혜택지급 방법')
        $this->v->add('benefitGiveFl', 'pattern', true, '{혜택지급 방법}', '/^(' . implode('|', array_keys($this->benefitGiveFl)) . ')$/');
        if ($data->get('benefitGiveFl', '') == 'auto') {
            // __('이벤트 조건달성 시 지급혜택')
            $this->v->add('benefitFl', 'pattern', true, '{이벤트 조건달성 시 지급혜택}', '/^(' . implode('|', $this->benefitFl) . ')$/');
            // 선택된 이벤트 조건달성 시 지급혜택에 따라 검증조건 추가
            if ($data->get('benefitFl', '') == 'mileage') {
                $this->v->add('benefitMileage', 'double', true);
            } else if ($data->get('benefitFl', '') == 'coupon') {
                $this->v->add('benefitCouponSno', 'number', true);
            }
        }
        if ($data->get('methodFl', '') != 'login') {
            // __('상단 영역')
            $this->v->add('designHeadFl', 'pattern', true, '{상단 영역}', '/^(' . implode('|', $this->designHeadFl) . ')$/');
            // __('본문 스킨')
            $this->v->add('designBodyFl', 'pattern', true, '{본문 스킨}', '/^(' . implode('|', $this->designBodyFl) . ')$/');
            if ($data->get('designHeadFl', '') == 'html') {
                $this->v->add('designHead', '', false);
            }
            $this->v->add('designFooter', '', false);
            // __('스탬프 이미지')
            $this->v->add('stampFl', 'pattern', true, '{스탬프 이미지}', '/^(' . implode('|', $this->stampFl) . ')$/');
        }
        $this->v->add('completeComment', '', false);
        $this->v->add('conditionComment', '', false);
        $this->v->add('managerNo', 'number', true);
    }

    public function validatePost()
    {
        $this->validateGroupFlagBySelect();
        $this->validateEventEndDateFlagByEndDate();

        $attendanceMemberGroupCheck = true;
        if ($this->isBenefitFlagCouponByGroupFlag($this->groupFl[1])) {
            $couponAdmin = new CouponAdmin();
            $couponMemberGroup = $couponAdmin->getCouponInfo($this->requestStorage->get('benefitCouponSno'), 'couponApplyMemberGroup');
            if ($couponMemberGroup['couponApplyMemberGroup']) {
                $couponMemberGroupArr = explode(INT_DIVISION, $couponMemberGroup['couponApplyMemberGroup']);
                foreach (json_decode($this->requestStorage->get('groupSno', '')) as $memGroupKey => $memGroupVal) {
                    if (array_search($memGroupVal, $couponMemberGroupArr) === false) {
                        $attendanceMemberGroupCheck = false;
                    }
                }
            }
        } else if ($this->isBenefitFlagCouponByGroupFlag($this->groupFl[0])) {
            $couponAdmin = new CouponAdmin();
            $couponMemberGroup = $couponAdmin->getCouponInfo($this->requestStorage->get('benefitCouponSno'), 'couponApplyMemberGroup');
            if ($couponMemberGroup['couponApplyMemberGroup']) {
                $attendanceMemberGroupCheck = false;
            }
        }
        if (!$attendanceMemberGroupCheck) {
            throw new Exception(__('출석체크 참여가능 회원등급과 선택한 쿠폰의 발급가능 회원등급이 다릅니다. 확인 후 다시 선택해주세요.'));
        }

        // 시작일이 종료일 보다 늦은 날짜인지 여부 체크
        $this->validateEventDate();

        if ($this->hasActiveEvent()) {
            throw new Exception(__('출석체크 진행 기간 중복입니다.'));
        }
    }

    protected function validateGroupFlagBySelect()
    {
        if ($this->requestStorage->get('groupFl', '') == 'select' && $this->requestStorage->get('groupSno', '') == '') {
            throw new Exception(__('선택된 등급만 참여가능한 경우 등급을 선택해주셔야합니다.'), 500);
        }
    }

    protected function validateEventEndDateFlagByEndDate()
    {
        if ($this->requestStorage->get('eventEndDtFl', 'n') === 'y'
            && $this->requestStorage->get('endDt', '') !== '9999-12-31') {
            throw new Exception(__('종료기간에 제한이 없는 경우 종료일은 설정할 수 없습니다.'), 500);
        }
    }

    /**
     * isBenefitFlagCouponByGroupFlag
     *
     * @param $flag
     *
     * @return bool
     */
    protected function isBenefitFlagCouponByGroupFlag($flag)
    {
        return $this->requestStorage->get('groupFl', 'select') === $flag
            && $this->requestStorage->get('benefitFl', 'coupon') === 'coupon';
    }

    protected function validateEventDate()
    {
        DateTimeUtils::intervalDateTime($this->requestStorage->get('startDt'), $this->requestStorage->get('endDt'));
    }

    public function hasActiveEvent($startDt = null, $endDt = null)
    {
        if ($startDt === null) {
            $startDt = $this->requestStorage->get('startDt');
        }
        if ($endDt === null) {
            $endDt = $this->requestStorage->get('endDt');
        }
        $arrBind = [];
        $fields = DBTableField::getFieldTypes(DBTableField::getFuncName($this->tableName));
        $this->db->query_reset();
        $this->db->strField = 'COUNT(*) AS cnt';

        $deviceFl = $this->requestStorage->get('deviceFl');
        if($deviceFl != 'all') {
            $this->db->strWhere = ' deviceFl=?';
            $this->db->bind_param_push($arrBind, 's', $deviceFl);
            $this->db->strWhere .= ' AND ((? <= startDt AND startDt <= ?) OR (? <= endDt AND endDt <= ?) OR (startDt <= ? AND ? <=  endDt))';
        }else {
            $this->db->strWhere = ' ((? <= startDt AND startDt <= ?) OR (? <= endDt AND endDt <= ?) OR (startDt <= ? AND ? <=  endDt))';
        }
        $this->db->bind_param_push($arrBind, $fields['startDt'], $startDt);
        $this->db->bind_param_push($arrBind, $fields['endDt'], $endDt);
        $this->db->bind_param_push($arrBind, $fields['startDt'], $startDt);
        $this->db->bind_param_push($arrBind, $fields['endDt'], $endDt);
        $this->db->bind_param_push($arrBind, $fields['startDt'], $startDt);
        $this->db->bind_param_push($arrBind, $fields['endDt'], $endDt);

        if ($this->requestStorage->get('sno', 0) > 0) {
            $this->db->strWhere .= ' AND sno!=?';
            $this->db->bind_param_push($arrBind, 'i', $this->requestStorage->get('sno'));
        }
        $query = $this->db->query_complete();
        $strSQL = 'SELECT ' . array_shift($query) . ' FROM ' . $this->tableName . implode(' ', $query);
        $cnt = $this->db->query_fetch($strSQL, $arrBind, false)['cnt'];
        StringUtils::strIsSet($cnt, 0);

        return $cnt > 0;
    }

    /**
     * uploadStamp
     *
     * @throws Exception
     */
    public function uploadStamp()
    {
        if ($this->requestStorage === null) {
            throw new Exception(__('검증에 필요한 정보가 없습니다.'));
        }
        $uploadFile = \Request::files()->get('stampPath');
        if (gd_file_uploadable($uploadFile, 'image') === true) {
            Storage::disk(Storage::PATH_CODE_ATTENDANCE_ICON_USER)->upload($uploadFile['tmp_name'], $uploadFile['name']);
            $this->requestStorage->set('stampPath', $uploadFile['name']);
            $this->requestStorage->set('stampFl', 'upload');
        } elseif ($this->requestStorage->has('stampPathTemp')
            && $this->requestStorage->get('stampPathTemp') !== '') {
            $this->requestStorage->set('stampPath', $this->requestStorage->get('stampPath'));
            $this->requestStorage->set('stampFl', 'upload');
        }
    }

    /**
     * insert
     *
     * @return int|string
     */
    public function insert()
    {
        $data = $this->requestStorage;
        $arrBind = $this->db->get_binding(DBTableField::tableAttendance(), $data->all(), 'insert', array_keys($data->all()));
        $this->db->set_insert_db(DB_ATTENDANCE, $arrBind['param'], $arrBind['bind'], 'y');
        unset($arrBind);

        return $this->db->insert_id();
    }

    /** @deprecated */
    public function validateRequestStorage(array &$data)
    {
        $this->v->init();
        foreach ($data as $index => $item) {
            $this->v->add($index, '');
        }
        $this->v->act($data, true);

        return $data;
    }

    public function hasLimitlessEvent($startDt = null, $endDt = null)
    {
        if ($startDt === null) {
            $startDt = $this->requestStorage->get('startDt');
        }
        if ($endDt === null) {
            $endDt = $this->requestStorage->get('endDt');
        }
        $fields = DBTableField::getFieldTypes(DBTableField::getFuncName($this->tableName));
        $this->db->query_reset();
        $this->db->strField = 'COUNT(*) AS cnt';
        $this->db->strWhere = '((? BETWEEN startDt AND endDt)';
        $this->db->strWhere .= ' OR (? BETWEEN startDt AND endDt))';
        $this->db->bind_param_push($arrBind, $fields['startDt'], $startDt);
        $this->db->bind_param_push($arrBind, $fields['endDt'], $endDt);
        if ($this->requestStorage->get('sno', 0) > 0) {
            $this->db->strWhere .= ' AND sno!=?';
            $this->db->bind_param_push($arrBind, 'i', $this->requestStorage->get('sno'));
        }
        $query = $this->db->query_complete();
        $strSQL = 'SELECT ' . array_shift($query) . ' FROM ' . $this->tableName . implode(' ', $query);
        $cnt = $this->db->query_fetch($strSQL, $arrBind, false)['cnt'];
        StringUtils::strIsSet($cnt, 0);

        return $cnt > 0;
    }

    public function hasWaitEvent()
    {
        return $this->db->getCount($this->tableName, '1', 'WHERE startDt > CURDATE()') > 0;
    }

    public function modify(array $arrData)
    {
        if (gd_isset($arrData['startDt'], '') == '') {
            $attendance = $this->getDataByTable($this->tableName, $arrData['sno'], 'sno');
            $arrData['startDt'] = $attendance['startDt'];
            unset($attendance);
        }
        $this->setRequestStorage($arrData);
        $this->validateUpdate();

        $this->v->init();
        $this->v->add('sno', '');
        $this->addRules();

        $data = $this->requestStorage->all();
        if ($this->v->act($data, true) === false) {
            throw new Exception(implode("\n", $this->v->errors));
        }
        $this->requestStorage = new SimpleStorage($data);

        $this->validatePost();

        $this->uploadStamp();
        if ($this->update() != 1) {
            throw new Exception(__('출석체크 이벤트 수정 중 오류가 발생하였습니다.'));
        }
    }

    public function validateUpdate()
    {
        if (!isset($this->requestStorage)) {
            throw new Exception(__('검증에 필요한 정보가 없습니다.'));
        }
        if ($this->requestStorage->get('sno', 0) < 1) {
            throw new Exception(__('출석체크 이벤트 번호가 없습니다.'));
        }
        if ($this->requestStorage->get('managerNo', 0) < 1) {
            throw new Exception(__('관리자 번호가 없습니다.'));
        }
        if ($this->requestStorage->has('deviceFl')) {
            if ($this->requestStorage->get('deviceFl', '') != 'pc' && $this->requestStorage->get('methodFl', '') == 'reply') {
                throw new Exception(__('모바일에서 참여가능한 출석체크 이벤트는 스탬프형과 로그인형만 가능합니다.'));
            }
        }
        if ($this->requestStorage->get('conditionFl', '') == 'each') {
            if ($this->requestStorage->get('benefitGiveFl', '') != 'auto') {
                throw new Exception(__('출석할 때마다 혜택지급 시에는 자동지급만 선택 가능합니다.'));
            }
            if ($this->requestStorage->get('benefitFl', '') != 'mileage') {
                throw new Exception(__('출석할 때마다 혜택지급 시에는 마일리지만 지급 가능합니다.'));
            }
        }
    }

    /**
     * update
     *
     * @return bool
     */
    public function update()
    {
        $data = $this->requestStorage;
        $arrBind = $this->db->updateBinding(DBTableField::getBindField($this->tableFunctionName), $data->all(), array_keys($data->all()));
        $this->db->bind_param_push($arrBind['bind'], 'i', $data->get('sno'));

        return $this->db->set_update_db(DB_ATTENDANCE, $arrBind['param'], 'sno=?', $arrBind['bind']);
    }

    public function hasAttendanceCheckMember()
    {
        return $this->getCount(DB_ATTENDANCE_CHECK, '1', ' WHERE attendanceSno=' . $this->requestStorage->get('sno')) > 0;
    }

    /**
     * lists
     *
     * @param array $arrData
     * @param int   $offset
     * @param int   $limit
     *
     * @return array|object
     */
    public function lists(array $arrData, $offset = 0, $limit = 20)
    {
        $arrBind = $arrWhere = [];

        // 초기 검색일 설정
        gd_isset($arrData['regDt'][0], date('Y-m-d', strtotime('-6 day')));
        gd_isset($arrData['regDt'][1], date('Y-m-d'));

        $today = DateTimeUtils::dateFormat('Y-m-d H:i:s', 'today');
        if ($arrData['activeFl'] == 'y') {
            // 오늘 날짜 기준 이벤트 날짜 사이로 설정
            $arrData['startDt'] = [
                '',
                $today,
            ];
            $arrData['endDt'] = [
                $today,
                '',
            ];
            $this->db->bindParameterByDateRange('startDt', $arrData, $arrBind, $arrWhere, $this->tableFunctionName);
            $this->db->bindParameterByDateRange('endDt', $arrData, $arrBind, $arrWhere, $this->tableFunctionName);
        } elseif ($arrData['activeFl'] == 'n') {
            // 오늘 날짜 기준 이벤트 종료일 지남
            $arrData['endDt'] = [
                '',
                $today,
            ];
            $this->db->bindParameterByDateRange('endDt', $arrData, $arrBind, $arrWhere, $this->tableFunctionName, '', false);
        } elseif ($arrData['activeFl'] == 'w') {
            // 오늘 날짜 기준 이벤트 종료일 이전
            $arrData['startDt'] = [
                $today,
                '',
            ];
            $this->db->bindParameterByDateRange('startDt', $arrData, $arrBind, $arrWhere, $this->tableFunctionName, '', false);
        }

        $this->bindParameterByKeyword($arrData, $arrBind, $arrWhere);
        $this->db->bindParameterByDateTimeRange('regDt', $arrData, $arrBind, $arrWhere, $this->tableFunctionName, 'a');
        $this->db->bindParameter('deviceFl', $arrData, $arrBind, $arrWhere, $this->tableFunctionName, 'a');
        $this->db->bindParameter('conditionFl', $arrData, $arrBind, $arrWhere, $this->tableFunctionName, 'a');
        $this->db->bindParameter('methodFl', $arrData, $arrBind, $arrWhere, $this->tableFunctionName, 'a');

        $this->db->strField = 'm.managerNm, m.isDelete , a.*, COUNT(ach.sno) AS totalAttendanceCount, COUNT(ach.conditionDt) AS completeAttendanceCount';
        $this->db->strWhere = implode(' AND ', $arrWhere);
        $this->db->strOrder = 'a.regDt DESC';
        $this->db->strJoin = ' LEFT JOIN ' . DB_ATTENDANCE_CHECK . ' AS ach ON a.sno=ach.attendanceSno';
        $this->db->strJoin .= ' LEFT JOIN ' . DB_MANAGER . ' AS m ON m.sno=a.managerNo';
        $this->db->strGroup = 'a.sno';
        if (is_null($offset) === false && is_null($limit) === false) {
            $this->db->strLimit = ($offset - 1) * $limit . ', ' . $limit;
        }
        $arrQuery = $this->db->query_complete();
        $query = 'SELECT ' . array_shift($arrQuery) . ' FROM ' . DB_ATTENDANCE . ' AS a ' . implode(' ', $arrQuery);
        $resultSet = $this->db->query_fetch($query, $arrBind);
        Manager::displayListData($resultSet);
        $this->iSearchCount = $this->db->query_count($arrQuery, DB_ATTENDANCE . ' AS a ', $arrBind);
        unset($arrBind, $arrWhere, $arrQuery);

        return $resultSet;
    }

    public function bindParameterByKeyword($requestParams, &$bindParam, &$whereParams)
    {
        if (empty($requestParams['keyword'])) {
            return false;
        }

        $fieldAttendance = DBTableField::getFieldTypes('tableAttendance');
        $fieldManager = DBTableField::getFieldTypes('tableManager');

        if ($requestParams['key'] == 'title') {
            $whereParams[] = 'a.' . $requestParams['key'] . ' LIKE concat(\'%\',?,\'%\')';
            $this->db->bind_param_push($bindParam, $fieldAttendance[$requestParams['key']], $requestParams['keyword']);
        } else if ($requestParams['key'] == 'managerNm') {
            $whereParams[] = 'm.' . $requestParams['key'] . ' LIKE concat(\'%\',?,\'%\')';
            $this->db->bind_param_push($bindParam, $fieldManager[$requestParams['key']], $requestParams['keyword']);
        } else {
            $tmpWhere = [];
            $tmpWhere[] = '(a.title  LIKE concat(\'%\',?,\'%\'))';
            $this->db->bind_param_push($bindParam, $fieldAttendance['title'], $requestParams['keyword']);
            $tmpWhere[] = '(m.managerNm LIKE concat(\'%\',?,\'%\'))';
            $this->db->bind_param_push($bindParam, $fieldManager['managerNm'], $requestParams['keyword']);
            $whereParams[] = '(' . implode(' OR ', $tmpWhere) . ')';
        }
    }

    /**
     * getData
     *
     * @param $sno
     *
     * @return SimpleStorage
     * @throws Exception
     */
    public function getAttendance($sno)
    {
        if (!Validator::required($sno)) {
            throw new Exception(__('출석체크 이벤트 번호가 없습니다.'));
        }
        $storage = new SimpleStorage($this->db->getData(DB_ATTENDANCE, $sno, 'sno'));

        if ($storage->get('conditionFl') == 'sum') {
            $storage->set('conditionCountBySum', $storage->get('conditionCount'));
        } elseif ($storage->get('conditionFl') == 'continue') {
            $storage->set('conditionCountByContinue', $storage->get('conditionCount'));
        }
        if ($storage->get('groupFl') == 'select') {
            $groupSno = json_decode(stripslashes($storage->get('groupSno')), true);
            if (!is_array($groupSno)) {
                throw new Exception(__('출석체크 이벤트 중 오류가 발생하였습니다. 등급 정보가 없습니다.'));
            }
            $groupData = GroupUtil::getGroupName('sno IN (\'' . implode('\',\'', $groupSno) . '\')');
            $storage->set('groupSno', $groupData);
        }
        if ($storage->get('benefitFl') == 'coupon') {
            $storage->set('benefitMileage', '');
        }

        $this->attendStorage = $storage;

        return $this->attendStorage;
    }

    /**
     * getDataByActive  조회일 기준으로 진행 중인 이벤트를 반환하는 함수
     *
     * @param null $methodFl 출석체크 진행 방법 null 인 경우 전체 출석체크를 대상으로 조회한다.
     *
     * @return SimpleStorage
     * @throws Exception
     */
    public function getDataByActive($methodFl = null)
    {
        $arrBind = $where = [];

        $today = gd_date_format('Y-m-d H:i:s', 'now');

        $where[] = 'startDt <= ?';
        $where[] = 'endDt >= ?';
        $this->db->bind_param_push($arrBind, 's', $today);
        $this->db->bind_param_push($arrBind, 's', $today);

        /*if(\Request::isMobile()) {
            $where[] = 'deviceFl = \'mobile\'';
        }*/
        if (!is_null($methodFl)) {
            $where[] = 'methodFl = ?';
            $this->db->bind_param_push($arrBind, 's', $methodFl);
        }

        $this->db->strField = '*';
        $this->db->strWhere = implode(' AND ', $where);
        $this->db->strLimit = '1';
        $query = $this->db->query_complete();

        $strSQL = 'SELECT ' . array_shift($query) . ' FROM ' . DB_ATTENDANCE . implode(' ', $query);
        $data = $this->db->query_fetch($strSQL, $arrBind)[0];
        unset($arrBind);

        $storage = new SimpleStorage($data);

        if ($storage->get('conditionFl') == 'sum') {
            $storage->set('conditionCountBySum', $storage->get('conditionCount'));
        } elseif ($storage->get('conditionFl') == 'continue') {
            $storage->set('conditionCountByContinue', $storage->get('conditionCount'));
        }
        if ($storage->get('groupFl') == 'select') {
            $groupSno = json_decode(stripslashes($storage->get('groupSno')), true);
            if (!is_array($groupSno)) {
                throw new Exception(__('출석체크 이벤트 중 오류가 발생하였습니다. 등급 정보가 없습니다.'));
            }
            $groupData = GroupUtil::getGroupName('sno IN (\'' . implode('\',\'', $groupSno) . '\')');
            $storage->set('groupSno', $groupData);
        }
        if ($storage->get('benefitFl') == 'coupon') {
            $storage->set('benefitMileage', '');
        }

        $this->attendStorage = $storage;

        return $this->attendStorage;
    }

    /**
     * @return array
     */
    public function getDeviceFl()
    {
        return $this->deviceFl;
    }

    /**
     * @return array
     */
    public function getMethodFl()
    {
        return $this->methodFl;
    }

    /**
     * @return array
     */
    public function getActiveFl()
    {
        return $this->activeFl;
    }

    /**
     * @return array
     */
    public function getConditionFl()
    {
        return $this->conditionFl;
    }

    /**
     * isActiveEvent
     *
     * @param null|string $startDt
     * @param null|string $endDt
     * @param null|string $currentDt
     * @param string      $format
     *
     * @return bool
     */
    public function isActiveEvent($startDt = null, $endDt = null, $currentDt = null, $format = 'Y-m-d H:i:s')
    {
        $startDt = $startDt ?? $this->attendStorage->get('startDt');
        $endDt = $endDt ?? $this->attendStorage->get('endDt');
        $currentDt = $currentDt ?? gd_date_format($format, 'today');

        if ($format != 'Y-m-d H:i:s') {
            $startDt = gd_date_format($format, $startDt);
            $endDt = gd_date_format($format, $endDt);
            $currentDt = gd_date_format($format, $currentDt);
        }

        if ($currentDt >= $startDt && $currentDt <= $endDt) {
            return true;
        } else {
            return false;
        }
    }

    /**
     * checkDevice
     *
     * @param string $deviceFl
     *
     * @throws Exception
     */
    public function checkDevice($deviceFl = 'pc')
    {
        if ($deviceFl == 'pc' && \Request::isMobile()) {
            throw new \Exception(__('해당 이벤트는 PC에서 참여 가능합니다.'));
        } else if ($deviceFl == 'mobile' && !\Request::isMobile()) {
            throw new \Exception(__('해당 이벤트는 모바일에서 참여 가능합니다.'));
        }
    }

    /**
     * checkGroup
     *
     * @param string            $groupFl
     * @param null|array|string $groupSno
     *
     * @throws Exception
     */
    public function checkGroup($groupFl = 'all', $groupSno = null)
    {
        if (is_string($groupSno)) {
            $groupSno = json_decode($groupSno, true);
        }
        if ($groupFl == 'select' && !array_key_exists(\Session::get('member.groupSno'), $groupSno)) {
            throw new \Exception(__('%s 등급만 참여하실 수 있습니다.', implode(',', array_values($groupSno))));
        }
    }

    /**
     * @return array
     */
    public function getBenefitGiveFl()
    {
        return $this->benefitGiveFl;
    }

    public function getSearchCount()
    {
        return gd_isset($this->iSearchCount, 0);
    }
}
