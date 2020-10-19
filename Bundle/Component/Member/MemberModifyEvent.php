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

namespace Bundle\Component\Member;

use App;
use Bundle\Component\Database\DBTableField;
use Bundle\Component\Member\Group\Util;
use Exception;
use Framework\Debug\Exception\AlertBackException;
use Framework\Utility\DateTimeUtils;
use Framework\Utility\SkinUtils;
use Framework\Utility\StringUtils;
use Globals;
use Request;
use Session;

/**
 * 회원정보 수정 이벤트
 *
 * @author haky <haky2@godo.co.kr>
 */
class MemberModifyEvent extends \Component\AbstractComponent
{
    // 통합 검색 항목
    protected $searchKeywordList = [
        'all'              => '=통합검색=',
        'eventNm'          => '이벤트명',
        'eventDescription' => '이벤트설명',
        'managerId'        => '등록자(아이디)',
    ];

    // 참여 내역 검색 항목
    protected $searchKeywordResult = [
        'all'   => '=통합검색=',
        'memId' => '아이디',
        'memNm' => '이름',
    ];

    // 진행상태 항목
    protected $searchStatus = [
        'all' => '전체',
        'y'   => '진행중',
        'n'   => '종료',
        'w'   => '대기',
    ];

    // 이벤트 유형
    protected $searchEventType = [
        'all'       => '전체',
        'modify'    => '회원정보 수정',
        'life'      => '평생회원',
    ];

    // 기간 검색 항목
    protected $searchDateStatusList = [
        'regDt'        => '등록일',
        'eventStartDt' => '시작일',
        'eventEndDt'   => '종료일',
    ];

    // 이벤트 정렬 리스트
    protected $sortList = [
        'mme.regDt desc'        => '등록일 ↑',
        'mme.regDt asc'         => '등록일 ↓',
        'mme.eventStartDt desc' => '시작일 ↑',
        'mme.eventStartDt asc'  => '시작일 ↓',
        'mme.eventEndDt desc'   => '종료일 ↑',
        'mme.eventEndDt asc'    => '종료일 ↓',
        'mme.eventNm desc'      => '이벤트명 ↑',
        'mme.eventNm asc'       => '이벤트명 ↓',
    ];

    // 이벤트 대상 제외 가입 기간 리스트
    protected $exceptJoinDayList = [];

    // 이벤트 진행 상태
    private $eventStatusFl = [
        'y',
        'n',
    ];

    // 이벤트 제외 가입기간 상태
    private $exceptJoinType = [
        'unlimit',
        'date',
        'day',
    ];

    // 혜택 지급 조건 상태
    private $benefitCondition = [
        'some',
        'all',
    ];

    // 혜택 지급 방법 상태
    private $benefitProvideType = [
        'auto',
        'manual',
    ];

    // 지급 혜택 상태
    private $benefitType = [
        'mileage',
        'coupon',
        'manual',
    ];

    // 팝업 유형
    private $popupContentType = [
        'default',
        'direct',
    ];

    // 이벤트 유형
    private $eventType = [
        'modify',
        'life',
    ];

    /**
     * @var array arrBind
     */
    private $arrBind = [];

    /**
     * @var array 조건
     */
    private $arrWhere = [];

    /**
     * @var array 검색
     */
    private $search = [];


    /**
     * MemberModifyEvent constructor.
     */
    public function __construct()
    {
        parent::__construct();
        $this->exceptJoinDayList = gd_array_change_key_value(
            [
                1,
                2,
                3,
                4,
                5,
                6,
                7,
                8,
                9,
                10,
                11,
                12,
                13,
                14,
                15,
                16,
                17,
                18,
                19,
                20,
                21,
                22,
                23,
                24,
                25,
                26,
                27,
                28,
                29,
                30,
                60,
                90,
            ]
        );
    }


    /**
     * 회원정보수정 이벤트 종료
     *
     * @param  array $eventSno 이벤트번호
     *
     * @throws Exception
     *
     */
    public function closeMemberModifyEvent($eventSno = null)
    {
        if (empty($eventSno)) {
            throw new AlertBackException(__('종료할 이벤트가 없습니다.'));
        }

        $targetCnt = $this->getCountEventStatus('n', $eventSno);

        if ($targetCnt > 0) {
            throw new Exception(__('종료된 이벤트입니다.'));
        }

        if (is_array($eventSno) && count($eventSno) > 1) {
            $where = 'sno IN (' . implode(', ', $eventSno) . ')';
        } else {
            $where = 'sno = ' . $eventSno[0];
        }

        $arrUpdate[] = "eventStatusFl = 'n'";
        $arrUpdate[] = 'eventEndDt = now()';
        $this->db->set_update_db(DB_MEMBER_MODIFY_EVENT, $arrUpdate, $where);
    }

    /**
     * 회원정보수정 이벤트 삭제
     *
     * @param  array $eventSno 이벤트번호
     *
     * @throws Exception
     *
     */
    public function deleteMemberModifyEvent($eventSno = null)
    {
        if (empty($eventSno)) {
            throw new AlertBackException(__('삭제할 이벤트가 없습니다.'));
        }

        $targetCnt = $this->getCountEventStatus('y', $eventSno);

        if ($targetCnt > 0) {
            throw new Exception(__('진행 중인 이벤트는 삭제하실 수 없습니다. 이벤트 종료 후 삭제하여 주시기 바랍니다.'));
        }

        if (is_array($eventSno) && count($eventSno) > 1) {
            $where = 'sno IN (' . implode(', ', $eventSno) . ')';
        } else {
            $where = 'sno = ' . $eventSno[0];
        }

        $this->db->set_delete_db(DB_MEMBER_MODIFY_EVENT, $where);
    }

    /**
     * 회원정보수정 이벤트 참여내역 삭제
     *
     * @param  array $resultSno 참여내역번호
     *
     * @throws Exception
     *
     */
    public function deleteMemberModifyEventResult($resultSno)
    {
        if (empty($resultSno)) {
            throw new AlertBackException(__('삭제할 참여내역이 없습니다.'));
        }

        if (is_array($resultSno) && count($resultSno) > 1) {
            $where = 'sno IN (' . implode(', ', $resultSno) . ')';
        } else {
            $where = 'sno = ' . $resultSno[0];
        }

        $this->db->set_delete_db(DB_MEMBER_MODIFY_EVENT_RESULT, $where);
    }

    /**
     * 회원정보수수정 이벤트 상태별 카운팅
     *
     * @param  string $status   이벤트 상태
     * @param  array  $eventSno 이벤트 번호
     *
     * @return integer
     *
     * @throws Exception
     *
     */
    public function getCountEventStatus($status, $eventSno)
    {
        if (gd_isset($status) == null || gd_isset($eventSno) == null) {
            throw new Exception(__('오류가 발생하였습니다.'));
        }

        if (is_array($eventSno) && count($eventSno) > 1) {
            $where = 'WHERE sno IN (' . implode(', ', $eventSno) . ')';
        } else {
            $eventSno = $eventSno[0];
            $where = 'WHERE sno = ' . $eventSno;
        }

        $where .= " AND eventStatusFl = '" . $status . "'";

        // 진행상태가 대기인 경우에 대한 체크 추가
        if ($status == 'y') {
            $where .= " AND eventStartDt <= now() ";
        }

        return $this->getCount(DB_MEMBER_MODIFY_EVENT, '1', $where);
    }

    /**
     * 참여내역관리 총 참여회원수 카운팅
     *
     * @param  integer  $eventSno 이벤트 번호
     *
     * @return integer
     *
     * @throws Exception
     *
     */
    public function getCountEventResultCount($eventSno)
    {
        if (gd_isset($eventSno) == null) {
            throw new Exception(__('오류가 발생하였습니다.'));
        }

        return $this->getCount(DB_MEMBER_MODIFY_EVENT_RESULT, '1', 'WHERE eventNo = ' . $eventSno);
    }

    /**
     * 평생회원 이벤트 참여 카운팅 (로그 히스토리 내역조회)
     *
     * @param  integer  $memNo 회원 번호
     *
     * @return integer
     *
     * @throws Exception
     *
     */
    public function getMemberLifeEventCount($memNo)
    {
        if (gd_isset($memNo) == null) {
            throw new Exception(__('오류가 발생하였습니다.'));
        }

        return $this->getCount(DB_MEMBER_HISTORY, '1', 'WHERE memNo = \'' . $memNo . '\' AND processor IN (\'member\') AND updateColumn = \'개인정보유효기간\' AND afterValue = \'999\'');
    }

    /**
     * 평생회원 이벤트 참여 카운팅 (로그 히스토리 내역조회 - 관리자)
     *
     * @param  integer  $memNo 회원 번호
     *
     * @return integer
     *
     * @throws Exception
     *
     */
    public function getMemberLifeEventAdminCount($memNo)
    {
        if (gd_isset($memNo) == null) {
            throw new Exception(__('오류가 발생하였습니다.'));
        }

        return $this->getCount(DB_MEMBER_HISTORY, '1', 'WHERE memNo = \'' . $memNo . '\' AND processor IN (\'admin\') AND updateColumn = \'개인정보유효기간\' AND beforeValue = \'999\' AND afterValue IN (\'1\',\'3\',\'5\')');
    }

    /**
     * 회원정보수정 이벤트 적용
     *
     * @param array $updatedData      회원정보수정 데이터
     * @param array $beforeMemberInfo 수정되기전 회원 데이터
     *
     * @return mixed
     */
    public function applyMemberModifyEvent($updatedData, $beforeMemberInfo)
    {
        // 수정전 데이터가 없을 경우 종료
        if (empty($beforeMemberInfo)) {
            return false;
        }

        // 진행중인 이벤트
        $activeEvent = $this->getActiveMemberModifyEvent($beforeMemberInfo['mallSno'], 'modify');
        if (empty($activeEvent)) {
            return false;
        }

        // 현재 진행중인 이벤트에 참여한 기록이 있으면 종료
        if ($this->checkDuplicationModifyEvent($activeEvent['sno'], $beforeMemberInfo['memNo'])) {
            return false;
        }

        // 제외 가입기간에 포함 될 경우 종료
        if ($this->checkMemberExceptJoinPeriod($activeEvent, $beforeMemberInfo['regDt'])) {
            return false;
        }

        // 혜택 지급
        $result = $this->provideEventBenefit($activeEvent, $beforeMemberInfo, $updatedData);

        return $result;
    }

    /**
     * 평생회원 이벤트 적용
     *
     * @param array   $memberData  회원 데이터
     * @param string  $eventType 이벤트 유형 (modify : 회원정보 수정 / life : 평생회원)
     *
     * @return mixed
     */
    public function applyMemberLifeEvent($memberData, $eventType)
    {
        // 진행중인 이벤트
        $activeEvent = $this->getActiveMemberModifyEvent($memberData['mallSno'], $eventType);
        if (empty($activeEvent)) {
            return false;
        }

        // 현재 진행중인 이벤트에 참여한 기록이 있으면 종료
        if ($this->checkDuplicationModifyEvent($activeEvent['sno'], $memberData['memNo'], $eventType)) {
            return false;
        }

        // 혜택 지급
        $result = $this->provideLifeEventBenefit($activeEvent, $memberData);

        return $result;
    }

    /**
     * 이벤트 혜택 지급
     *
     * @param array $eventData   이벤트 데이터
     * @param array $memberData  회원 데이터
     * @param array $updatedData 수정된 데이터
     *
     * @return mixed 혜택 지급 결과
     *
     */
    public function provideEventBenefit($eventData, $memberData, $updatedData)
    {
        // 수정된 회원 정보 필드
        $modifyData = $this->checkModifyMemberInfo($eventData, $memberData, $updatedData);
        if (empty($modifyData)) {
            return false;
        }

        // 혜택 지급
        if ($eventData['benefitProvideType'] == 'auto') {
            if ($eventData['benefitType'] == 'mileage') {
                $mileagePolicy = gd_mileage_give_info();
                if ($mileagePolicy['give']['giveFl'] == 'y') {
                    $mileageTrunc = Globals::get('gTrunc.mileage');
                    $fixMileage = gd_number_figure($eventData['benefitMileage'], $mileageTrunc['unitPrecision'], $mileageTrunc['unitRound']);
                    $eventData['benefitMileage'] = $fixMileage;
                    if ($this->provideMileage($eventData, $memberData['memNo'])) {
                        $result['mileage'] = $fixMileage;
                    }
                }
            } else if ($eventData['benefitType'] == 'coupon') {
                if (gd_use_coupon()) {
                    $arrWhere = [
                        'memNo',
                        'couponNo',
                    ];
                    $arrData = [
                        $memberData['memNo'],
                        $eventData['benefitCouponSno'],
                    ];
                    // 발급된 쿠폰이 없는 경우만 쿠폰 발행
                    $tmpMemberCouponData = $this->getDataByTable(DB_MEMBER_COUPON, $arrData, $arrWhere, 'memberCouponNo');
                    if (empty($tmpMemberCouponData)) {
                        if ($this->provideCoupon($eventData, $memberData)) {
                            $couponData = gd_htmlspecialchars_stripslashes($this->getDataByTable(DB_COUPON, $eventData['benefitCouponSno'], 'couponNo', 'couponNm'));
                            $memberCouponData = $this->getDataByTable(DB_MEMBER_COUPON, $arrData, $arrWhere, 'memberCouponNo');
                            if($memberCouponData['memberCouponNo']) { //쿠폰 발급 제한 조건으로 인해 쿠폰 미발급 시 alert 내 발급안내문구 제거
                                $result['couponNm'] = $couponData['couponNm'];
                            }
                            $memberData['memberCouponSno'] = $memberCouponData['memberCouponNo'];
                        }
                    }
                }
            }
        }

        // 참여내역 저장
        $resultCnt = $this->setMemberModifyEventResult($eventData, $memberData, $modifyData);

        if ($resultCnt > 0) {
            $result['msg'] = __("회원정보 수정 이벤트에 참여하여 주셔서 감사합니다.");
            if (empty($result['mileage']) == false && $result['mileage'] > 0) {
                $result['msg'] .= '\n' . gd_display_mileage_name() . ' ' . $result['mileage'] . ' ' . gd_display_mileage_unit() . ' ' . __('지급되었습니다.');
            } else if (empty($result['couponNm']) == false) {
                $result['msg'] .= '\n' . '[' . $result['couponNm'] . '] ' . __('쿠폰이 발급되었습니다.');
            }

            return $result;
        } else {
            return false;
        }
    }

    /**
     * 이벤트 혜택 지급
     *
     * @param array $eventData   이벤트 데이터
     * @param array $memberData  회원 데이터
     *
     * @return mixed 혜택 지급 결과
     *
     */
    public function provideLifeEventBenefit($eventData, $memberData)
    {
        // 수정된 회원 정보 필드
        $modifyData['expirationFl'] = 'expirationFl';
        if (empty($modifyData)) {
            return false;
        }

        // 혜택 지급
        if ($eventData['benefitProvideType'] == 'auto') {
            if ($eventData['benefitType'] == 'mileage') {
                $mileagePolicy = gd_mileage_give_info();
                if ($mileagePolicy['give']['giveFl'] == 'y') {
                    $mileageTrunc = Globals::get('gTrunc.mileage');
                    $fixMileage = gd_number_figure($eventData['benefitMileage'], $mileageTrunc['unitPrecision'], $mileageTrunc['unitRound']);
                    $eventData['benefitMileage'] = $fixMileage;
                    if ($this->provideMileage($eventData, $memberData['memNo'])) {
                        $result['mileage'] = $fixMileage;
                    }
                }
            } else if ($eventData['benefitType'] == 'coupon') {
                if (gd_use_coupon()) {
                    $arrWhere = [
                        'memNo',
                        'couponNo',
                    ];
                    $arrData = [
                        $memberData['memNo'],
                        $eventData['benefitCouponSno'],
                    ];
                    // 발급된 쿠폰이 없는 경우만 쿠폰 발행
                    $tmpMemberCouponData = $this->getDataByTable(DB_MEMBER_COUPON, $arrData, $arrWhere, 'memberCouponNo');
                    if (empty($tmpMemberCouponData)) {
                        if ($this->provideCoupon($eventData, $memberData)) {
                            $couponData = gd_htmlspecialchars_stripslashes($this->getDataByTable(DB_COUPON, $eventData['benefitCouponSno'], 'couponNo', 'couponNm'));
                            $memberCouponData = $this->getDataByTable(DB_MEMBER_COUPON, $arrData, $arrWhere, 'memberCouponNo');
                            if($memberCouponData['memberCouponNo']) { //쿠폰 발급 제한 조건으로 인해 쿠폰 미발급 시 alert 내 발급안내문구 제거
                                $result['couponNm'] = $couponData['couponNm'];
                            }
                            $memberData['memberCouponSno'] = $memberCouponData['memberCouponNo'];
                        }
                    }
                }
            }
        }

        // 참여내역 저장
        $resultCnt = $this->setMemberModifyEventResult($eventData, $memberData, $modifyData);

        if ($resultCnt > 0) {
            $result['msg'] = __("평생회원 이벤트에 참여하여 주셔서 감사합니다.");
            if (empty($result['mileage']) == false && $result['mileage'] > 0) {
                $result['msg'] .= '\n' . gd_display_mileage_name() . ' ' . $result['mileage'] . ' ' . gd_display_mileage_unit() . ' ' . __('지급되었습니다.');
            } else if (empty($result['couponNm']) == false) {
                $result['msg'] .= '\n' . '[' . $result['couponNm'] . '] ' . __('쿠폰이 발급되었습니다.');
            }

            return $result;
        } else {
            return false;
        }
    }

    /**
     * 이벤트 혜택(마일리지) 지급
     *
     * @param array   $eventData 이벤트 데이터
     * @param integer $memNo     회원 번호
     *
     * @return bool
     *
     */
    public function provideMileage($eventData, $memNo)
    {
        if (empty($eventData['sno']) || empty($eventData['benefitMileage']) || $eventData['benefitMileage'] < 1) {
            return false;
        }

        $mileage = \App::load('\\Component\\Mileage\\Mileage');
        $this->setIsTran(false);
        $code = $mileage::REASON_CODE_GROUP . $mileage::REASON_CODE_MEMBER_MODIFY_EVENT;
        $result = $mileage->setMemberMileage($memNo, $eventData['benefitMileage'], $code, 'm', $eventData['sno'], null, '회원정보 수정 이벤트 참여');
        unset($mileage);

        return $result;
    }

    /**
     * 이벤트 혜택(쿠폰) 지급
     *
     * @param array $eventData  이벤트 데이터
     * @param array $memberData 회원 데이터
     *
     * @return bool
     *
     */
    public function provideCoupon($eventData, $memberData)
    {
        if (empty($eventData['sno']) || empty($eventData['benefitCouponSno']) || $eventData['benefitCouponSno'] < 1) {
            return false;
        }

        $coupon = \App::load('\\Component\\Coupon\\Coupon');
        $coupon->setAutoCouponMemberSave('memberModifyEvent', $memberData['memNo'], $memberData['groupSno'], $eventData['benefitCouponSno'], $eventData['eventType']);
        unset($coupon);

        return true;
    }

    /**
     * 회원정보 수정 이벤트 참여 내역 저장
     *
     * @param array $eventData  이벤트 데이터
     * @param array $memberData 회원 데이터
     * @param array $modifyData 수정된 필드
     *
     * @return mixed
     *
     */
    public function setMemberModifyEventResult($eventData, $memberData, $modifyData)
    {
        if (empty($modifyData) || count($modifyData) == 0) {
            return false;
        }

        $setData['eventNo'] = $eventData['sno'];
        $setData['memNo'] = $memberData['memNo'];
        $setData['memNm'] = $memberData['memNm'];
        $setData['eventType'] = $eventData['eventType'];
        $groupInfo = Util::getGroupName('sno = ' . $memberData['groupSno']);
        $setData['groupNm'] = $groupInfo[$memberData['groupSno']];
        $setData['modifiedField'] = implode(STR_DIVISION, $modifyData);
        $setData['provideBenefitType'] = $eventData['benefitType'];
        if ($eventData['benefitProvideType'] == 'auto') {
            if ($setData['provideBenefitType'] == 'mileage') {
                $setData['provideBenefitMileage'] = $eventData['benefitMileage'];
            } else if ($setData['provideBenefitType'] == 'coupon') {
                $setData['provideBenefitCouponSno'] = $eventData['benefitCouponSno'];
                $setData['provideMemberCouponSno'] = $memberData['memberCouponSno'];
            }
        }

        $arrBind = $this->db->get_binding(DBTableField::tableMemberModifyEventResult(), $setData, 'insert');
        $this->db->set_insert_db(DB_MEMBER_MODIFY_EVENT_RESULT, $arrBind['param'], $arrBind['bind'], 'y');

        return $this->insertId();
    }

    /**
     * 수정된 회원정보의 필드 반환
     *
     * @param array $activeEvent 이벤트 데이터
     * @param array $memberData  회원 데이터
     * @param array $updatedData 수정 데이터
     *
     * @return mixed  수정된 필드
     *
     */
    public function checkModifyMemberInfo($activeEvent, $memberData, $updatedData)
    {
        $eventApplyField = explode(STR_DIVISION, $activeEvent['eventApplyField']);
        $joinItemPolicy = gd_policy('member.joinitem', $activeEvent['mallSno']);
        $benefitCondition = $activeEvent['benefitCondition'];
        $modifyData = [];

        foreach ($eventApplyField as $aKey => $aVal) {
            // 혜택 지급 조건이 모두 수정일때 수정 항목을 사용하지 않는 경우 종료
            if ($benefitCondition == 'all') {
                if (!($aVal == 'addressSub' || $aVal == 'cellPhoneCountryCode') && array_key_exists($aVal, $joinItemPolicy) == false || (array_key_exists($aVal, $joinItemPolicy) && $joinItemPolicy[$aVal]['use'] == 'n')) {
                    return false;
                }
            }
            // 수정된 회원정보 필드 저장
            switch ($aVal) {
                case 'maillingFl':
                case 'smsFl':
                    if ($updatedData[$aVal] == 'y' && $memberData[$aVal] == 'n') {
                        $modifyData[] = $aVal;
                    }
                    break;
                case 'memPw':
                    if ($updatedData['changePasswordFl'] == 'y') {
                        $modifyData[] = $aVal;
                    }
                    break;
                default:
                    // 휴대폰번호 형식으로 변경
                    if ($aVal == 'cellPhone') {
                        $updatedData[$aVal] = StringUtils::numberToCellPhone($updatedData[$aVal]);
                    }
                    if (array_key_exists($aVal, $updatedData) && trim($updatedData[$aVal]) != trim($memberData[$aVal])) {
                        $modifyData[] = $aVal;
                    }
                    break;
            }
        }

        if (count($modifyData) == 0) {
            return false;
        }

        // 모두 수정일 경우에 주소와 휴대폰(해외몰)은 하나만 수정해도 적용
        if ($benefitCondition == 'all') {
            $modifyAllDataFl = false;
            $exceptData = [
                'address',
                'addressSub',
                'cellPhone',
                'cellPhoneCountryCode',
            ];
            $unModifyData = array_diff($eventApplyField, $modifyData);
            if (count($unModifyData) > 1) {
                return false;
            } else if (count($unModifyData) == 1) {
                foreach ($exceptData as $key => $val) {
                    if (in_array($val, $unModifyData) == true) {
                        $modifyAllDataFl = true;
                    }
                }
                if ($modifyAllDataFl != true) {
                    return false;
                }
            }
        }

        return $modifyData;
    }

    /**
     * 회원정보 수정 이벤트 참여 내역 중복 체크
     *
     * @param integer $eventNo 이벤트 정보
     * @param integer $memNo   회원가입일
     * @param string  $eventType 이벤트 유형 (modify : 회원정보 수정 / life : 평생회원)
     *
     * @return bool
     *
     */
    public function checkDuplicationModifyEvent($eventNo, $memNo, $eventType = 'modify')
    {
        if (gd_isset($eventNo) == null || gd_isset($memNo) == null) {
            return false;
        }
        $arrBind = [];
        $strSQL = 'SELECT COUNT(1) as cnt FROM ' . DB_MEMBER_MODIFY_EVENT_RESULT . ' WHERE eventType = ? AND eventNo = ? AND memNo = ? ';
        $this->db->bind_param_push($arrBind, 's', $eventType);
        $this->db->bind_param_push($arrBind, 'i', $eventNo);
        $this->db->bind_param_push($arrBind, 'i', $memNo);
        $data = $this->db->query_fetch($strSQL, $arrBind, false);
        unset($arrBind);

        return $data['cnt'] > 0;
    }

    /**
     * 회원의 이벤트 대상 제외 가입기간 포함 여부
     *
     * @param array  $exceptData  이벤트 정보
     * @param string $memberRegDt 회원가입일
     *
     * @return bool
     *
     */
    public function checkMemberExceptJoinPeriod($exceptData, $memberRegDt)
    {
        if ($exceptData['exceptJoinType'] == 'unlimit') {
            return false;
        } else {
            $regDt = strtotime($memberRegDt);
            if ($exceptData['exceptJoinType'] == 'date') {
                $exceptStartDt = strtotime($exceptData['exceptJoinStartDt']);
                $exceptEndDt = strtotime($exceptData['exceptJoinEndDt']);

                if ($regDt >= $exceptStartDt && $regDt <= $exceptEndDt) {
                    return true;
                } else {
                    return false;
                }
            } else if ($exceptData['exceptJoinType'] == 'day') {
                $now = strtotime('now');
                $exceptDate = strtotime('+' . $exceptData['exceptJoinDay'] . ' day', $regDt);

                if ($now > $exceptDate) {
                    return false;
                } else {
                    return true;
                }
            }
        }
    }

    /**
     * 회원정보 수정 이벤트 관리자 수정 반영 여부 확인
     *
     * @param   integer $mallSno 상점번호
     * @param   string  $eventType 이벤트 유형 (modify : 회원정보 수정 / life : 평생회원)
     *
     * @return bool
     *
     */
    public function checkAdminModifyFl($mallSno = DEFAULT_MALL_NUMBER, $eventType = null)
    {
        $activeEvent = $this->getActiveMemberModifyEvent($mallSno, $eventType);

        if ($activeEvent['adminModifyFl'] == 'y') {
            return true;
        } else {
            return false;
        }
    }

    /**
     * 조회일 기준으로 진행 중인 이벤트를 반환
     *
     * @param   integer $mallSno 상점번호
     * @param   string  $eventType 이벤트 유형 (modify : 회원정보 수정 / life : 평생회원)
     *
     * @return array|null 진행중인 이벤트 정보
     *
     */
    public function getActiveMemberModifyEvent($mallSno = DEFAULT_MALL_NUMBER, $eventType = null)
    {
        $this->arrWhere[] = "mallSno = ?";
        $this->db->bind_param_push($this->arrBind, 'i', $mallSno);

        if (gd_isset($eventType) && $eventType != 'all') {
            $this->arrWhere[] = "eventType = ?";
            $this->db->bind_param_push($this->arrBind, 's', $eventType);
        }

        $this->arrWhere[] = "eventStatusFl = 'y'";
        $this->arrWhere[] = "eventStartDt <= now()";
        $this->arrWhere[] = "eventEndDt >= now()";

        $this->db->strField = "*";
        $this->db->strWhere = implode(' AND ', gd_isset($this->arrWhere));

        $query = $this->db->query_complete();
        $strSQL = 'SELECT ' . array_shift($query) . ' FROM ' . DB_MEMBER_MODIFY_EVENT . implode(' ', $query);
        $data = $this->db->query_fetch($strSQL, $this->arrBind);
        unset($this->arrBind);
        unset($this->arrWhere);

        if (gd_isset($eventType) && $eventType != 'all') {
            if (count($data) > 1) {
                return $data;
            } else {
                return $data[0];
            }
        } else {
            return gd_htmlspecialchars_stripslashes(gd_isset($data));
        }
    }

    /**
     * 회원정보수정 이벤트 상세정보
     *
     * @param   integer $sno 이벤트번호
     *
     * @return  array|null 이벤트 정보
     *
     */
    public function getMemberModifyEventInfo($sno = null)
    {
        $eventData = null;

        if ($sno) {
            $data = $this->getDataByTable(DB_MEMBER_MODIFY_EVENT, $sno, 'sno');
            $eventData['data'] = gd_htmlspecialchars_stripslashes(gd_isset($data));
        }

        return $eventData;
    }

    /**
     * 회원정보수정 이벤트 참여 내역 검색 설정
     *
     * @param   array $searchData 검색데이터
     *
     * @return  mixed
     *
     */
    public function setSearchEventResult($searchData)
    {
        // 검색 기본값 설정
        $this->search['searchKeywordResult'] = $this->getSearchKeywordResult();
        $this->search['eventNo'] = gd_isset($searchData['eventNo'], '');
        $this->search['searchKeyword'] = gd_isset($searchData['searchKeyword'], 'all');
        $this->search['keyword'] = gd_isset(trim($searchData['keyword']), '');
        $this->search['searchPeriod'] = gd_isset($searchData['searchPeriod'], '-1');
        $this->search['page'] = gd_isset($searchData['page'], 1);
        $this->search['pageNum'] = gd_isset($searchData['pageNum'], 10);

        // 이벤트 검색
        if ($this->search['eventNo']) {
            $this->arrWhere[] = 'mmer.eventNo = ?';
            $this->db->bind_param_push($this->arrBind, 'i', $this->search['eventNo']);
        }

        // 키워드 검색
        if ($this->search['keyword']) {
            if ($this->search['searchKeyword'] == 'all') {
                $this->arrWhere[] = '(m.memId LIKE CONCAT(\'%\', ?, \'%\') OR mmer.memNm LIKE CONCAT(\'%\', ?, \'%\'))';
                $this->db->bind_param_push($this->arrBind, 's', $this->search['keyword']);
                $this->db->bind_param_push($this->arrBind, 's', $this->search['keyword']);
            } else {
                if ($this->search['searchKeyword'] == 'memId') {
                    $this->arrWhere[] = 'm.memId LIKE CONCAT(\'%\', ?, \'%\')';
                    $this->db->bind_param_push($this->arrBind, 's', $this->search['keyword']);
                } else if ($this->search['searchKeyword'] == 'memNm') {
                    $this->arrWhere[] = 'mmer.memNm LIKE CONCAT(\'%\', ?, \'%\')';
                    $this->db->bind_param_push($this->arrBind, 's', $this->search['keyword']);
                }
            }
        }

        // 기간 검색 날짜 설정
        if ($this->search['searchPeriod'] < 0) {
            $this->search['searchDate'][] = gd_isset($searchData['searchDate'][0], '');
            $this->search['searchDate'][] = gd_isset($searchData['searchDate'][1], '');
        } else {
            $this->search['searchDate'][] = gd_isset($searchData['searchDate'][0], date('Y-m-d', strtotime('-' . $this->search['searchPeriod'] . ' day')));
            $this->search['searchDate'][] = gd_isset($searchData['searchDate'][1], date('Y-m-d'));
        }

        // 날짜 검색
        if ($this->search['searchDate'][0]) {
            $this->arrWhere[] = 'mmer.regDt >= ?';
            $this->db->bind_param_push($this->arrBind, 's', $this->search['searchDate'][0] . ' 00:00:00');
        }

        if ($this->search['searchDate'][1]) {
            $this->arrWhere[] = 'mmer.regDt <= ?';
            $this->db->bind_param_push($this->arrBind, 's', $this->search['searchDate'][1] . ' 23:59:59');
        }
    }

    /**
     * 회원정보수정 이벤트 리스트 검색 설정
     *
     * @param   array $searchData 검색데이터
     *
     * @return  mixed
     *
     */
    public function setSearchEventList($searchData)
    {
        // 검색 기본값 설정
        $this->search['mallStatus'] = gd_isset($searchData['mallStatus'], 'all');
        $this->search['searchKeyword'] = gd_isset($searchData['searchKeyword'], 'all');
        $this->search['keyword'] = gd_isset(trim($searchData['keyword']), '');
        $this->search['eventStatusFl'] = gd_isset($searchData['eventStatusFl'], 'all');
        $this->search['searchDateStatus'] = gd_isset($searchData['searchDateStatus'], 'regDt');
        $this->search['searchPeriod'] = gd_isset($searchData['searchPeriod'], '-1');
        $this->search['sort'] = gd_isset($searchData['sort'], 'mme.regDt desc');
        $this->search['page'] = gd_isset($searchData['page'], 1);
        $this->search['pageNum'] = gd_isset($searchData['pageNum'], 10);
        $this->search['eventType'] = gd_isset($searchData['eventType'], 'all');

        // 상점 검색
        if ($this->search['mallStatus'] != 'all') {
            $this->arrWhere[] = 'mme.mallSno = ? ';
            $this->db->bind_param_push($this->arrBind, 'i', $this->search['mallStatus']);
        }

        // 키워드 검색
        if ($this->search['keyword']) {
            if ($this->search['searchKeyword'] == 'all') {
                $this->arrWhere[] = '(mme.eventNm LIKE CONCAT(\'%\', ?, \'%\') OR mme.eventDescription LIKE CONCAT(\'%\', ?, \'%\') OR m.managerId LIKE CONCAT(\'%\', ?, \'%\'))';
                $this->db->bind_param_push($this->arrBind, 's', $this->search['keyword']);
                $this->db->bind_param_push($this->arrBind, 's', $this->search['keyword']);
                $this->db->bind_param_push($this->arrBind, 's', $this->search['keyword']);
            } else {
                if ($this->search['searchKeyword'] == 'eventNm') {
                    $this->arrWhere[] = 'mme.eventNm LIKE CONCAT(\'%\', ?, \'%\')';
                    $this->db->bind_param_push($this->arrBind, 's', $this->search['keyword']);
                } else if ($this->search['searchKeyword'] == 'eventDescription') {
                    $this->arrWhere[] = 'mme.eventDescription LIKE CONCAT(\'%\', ?, \'%\')';
                    $this->db->bind_param_push($this->arrBind, 's', $this->search['keyword']);
                } else if ($this->search['searchKeyword'] == 'managerId') {
                    $this->arrWhere[] = 'm.managerId LIKE CONCAT(\'%\', ?, \'%\')';
                    $this->db->bind_param_push($this->arrBind, 's', $this->search['keyword']);
                }
            }
        }

        // 진행 상태 검색
        if ($this->search['eventStatusFl'] != 'all') {
            if ($this->search['eventStatusFl'] == 'w') {
                $this->arrWhere[] = 'mme.eventStartDt > now()';
                $this->arrWhere[] = 'mme.eventStatusFl != ?';
                $this->db->bind_param_push($this->arrBind, 's', 'n');
            } else if ($this->search['eventStatusFl'] == 'y') {
                $this->arrWhere[] = 'mme.eventStartDt <= now() AND mme.eventEndDt > now()';
                $this->arrWhere[] = 'mme.eventStatusFl != ?';
                $this->db->bind_param_push($this->arrBind, 's', 'n');
            } else if ($this->search['eventStatusFl'] == 'n') {
                $this->arrWhere[] = 'mme.eventStatusFl = ?';
                $this->db->bind_param_push($this->arrBind, 's', $this->search['eventStatusFl']);
            }
        }

        // 이벤트 유형 검색
        if ($this->search['eventType'] != 'all') {
            $this->arrWhere[] = 'mme.eventType = ?';
            $this->db->bind_param_push($this->arrBind, 's', $this->search['eventType']);
        }

        // 기간 검색 날짜 설정
        if ($this->search['searchPeriod'] < 0) {
            $this->search['searchDate'][] = gd_isset($searchData['searchDate'][0], '');
            $this->search['searchDate'][] = gd_isset($searchData['searchDate'][1], '');
        } else {
            $this->search['searchDate'][] = gd_isset($searchData['searchDate'][0], date('Y-m-d', strtotime('-' . $this->search['searchPeriod'] . ' day')));
            $this->search['searchDate'][] = gd_isset($searchData['searchDate'][1], date('Y-m-d'));
        }

        // 날짜 검색
        if ($this->search['searchDate'][0]) {
            if ($this->search['searchDateStatus'] == 'regDt') {
                $this->arrWhere[] = 'mme.regDt >= ?';
                $this->db->bind_param_push($this->arrBind, 's', $this->search['searchDate'][0] . ' 00:00:00');
            } else if ($this->search['searchDateStatus'] == 'eventStartDt') {
                $this->arrWhere[] = 'mme.eventStartDt >= ?';
                $this->db->bind_param_push($this->arrBind, 's', $this->search['searchDate'][0] . ' 00:00:00');
            } else if ($this->search['searchDateStatus'] == 'eventEndDt') {
                $this->arrWhere[] = 'mme.eventEndDt >= ?';
                $this->db->bind_param_push($this->arrBind, 's', $this->search['searchDate'][0] . ' 00:00:00');
            }
        }

        if ($this->search['searchDate'][1]) {
            if ($this->search['searchDateStatus'] == 'regDt') {
                $this->arrWhere[] = 'mme.regDt <= ?';
                $this->db->bind_param_push($this->arrBind, 's', $this->search['searchDate'][1] . ' 23:59:59');
            } else if ($this->search['searchDateStatus'] == 'eventStartDt') {
                $this->arrWhere[] = 'mme.eventStartDt <= ?';
                $this->db->bind_param_push($this->arrBind, 's', $this->search['searchDate'][1] . ' 23:59:59');
            } else if ($this->search['searchDateStatus'] == 'eventEndDt') {
                $this->arrWhere[] = 'mme.eventEndDt <= ?';
                $this->db->bind_param_push($this->arrBind, 's', $this->search['searchDate'][1] . ' 23:59:59');
            }
        }
    }

    /**
     * 회원정보수정 이벤트 참여내역
     *
     * @param   array $params 검색데이터
     *
     * @return  mixed
     *
     */
    public function getMemberModifyEventResult($params = null)
    {
        // 검색 설정
        $this->setSearchEventResult($params);

        // 페이지 설정
        $page = \App::load('\\Component\\Page\\Page', $this->search['page'], 0, $this->getCount(DB_MEMBER_MODIFY_EVENT_RESULT, '1', 'WHERE eventNo = ' . $params['eventNo']), $this->search['pageNum']);

        // 데이터 처리
        $this->db->strField = 'mmer.sno, mmer.memNo, mmer.memNm, mmer.groupNm, mmer.regDt, IF(m.memId != \'\', m.memId, mh.memId) AS memId';
        $this->db->strWhere = implode(' AND ', gd_isset($this->arrWhere));
        $this->db->strJoin = ' LEFT JOIN ' . DB_MEMBER . ' AS m ON m.memNo = mmer.memNo';
        $this->db->strJoin .= ' LEFT JOIN ' . DB_MEMBER_HACKOUT . ' AS mh ON mh.memNo = mmer.memNo';
        $this->db->strOrder = 'mmer.regDt desc';
        if ($params['mode'] != 'excel_modify_event_result_down') {
            $this->db->strLimit = $page->recode['start'] . ',' . $this->search['pageNum'];
        }
        $query = $this->db->query_complete(true, true);
        $strSQL = 'SELECT ' . array_shift($query) . ' FROM ' . DB_MEMBER_MODIFY_EVENT_RESULT . ' AS mmer' . implode(' ', $query);
        $data = $this->db->query_fetch($strSQL, $this->arrBind);

        $funcFoundRows = function () {
            $query = $this->db->getQueryCompleteBackup(
                [
                    'field' => 'COUNT(*) AS cnt',
                    'limit' => null,
                    'order' => null,
                ]
            );
            $strSQL = 'SELECT ' . array_shift($query) . ' FROM ' . DB_MEMBER_MODIFY_EVENT_RESULT . ' AS mmer' . implode(' ', $query);
            $cnt = $this->db->query_fetch($strSQL, $this->arrBind, false)['cnt'];
            StringUtils::strIsSet($cnt, 0);

            return $cnt;
        };

        // 검색 레코드 수 설정
        $page->recode['total'] = $funcFoundRows();
        $page->setPage();
        $page->setUrl(Request::getQueryString());

        // 각 데이터 배열화
        $getData['data'] = gd_htmlspecialchars_stripslashes(gd_isset($data));
        $getData['search'] = gd_htmlspecialchars($this->search);
        unset($this->arrWhere, $this->arrBind);

        return $getData;
    }

    /**
     * 회원정보수정 이벤트 리스트
     *
     * @param   array $params 검색데이터
     *
     * @return  mixed
     *
     */
    public function getMemberModifyEventList($params = null)
    {
        // 검색 설정
        $this->setSearchEventList($params);

        // 페이지 설정
        $page = \App::load('\\Component\\Page\\Page', $this->search['page'], 0, $this->getCount(DB_MEMBER_MODIFY_EVENT), $this->search['pageNum']);

        // 데이터 처리
        $this->db->strField = 'mme.sno, mme.mallSno, mme.eventNm, mme.eventStatusFl, mme.eventStartDt, mme.eventEndDt, mme.managerNo, mme.regDt, m.managerId, m.managerNm, mme.eventType';
        $this->db->strWhere = implode(' AND ', gd_isset($this->arrWhere));
        $this->db->strOrder = $this->search['sort'];
        $this->db->strJoin = ' LEFT JOIN ' . DB_MANAGER . ' AS m ON m.sno = mme.managerNo';
        $this->db->strLimit = $page->recode['start'] . ',' . $this->search['pageNum'];
        $query = $this->db->query_complete(true, true);
        $strSQL = 'SELECT ' . array_shift($query) . ' FROM ' . DB_MEMBER_MODIFY_EVENT . ' AS mme' . implode(' ', $query);
        $data = $this->db->query_fetch($strSQL, $this->arrBind);

        $funcFoundRows = function () {
            $query = $this->db->getQueryCompleteBackup(
                [
                    'field' => 'COUNT(*) AS cnt',
                    'order' => null,
                    'limit' => null,
                ]
            );
            $strSQL = 'SELECT ' . array_shift($query) . ' FROM ' . DB_MEMBER_MODIFY_EVENT . ' AS mme' . implode(' ', $query);
            $cnt = $this->db->query_fetch($strSQL, $this->arrBind, false)['cnt'];
            StringUtils::strIsSet($cnt, 0);

            return $cnt;
        };
        // 검색 레코드 수 설정
        $page->recode['total'] = $funcFoundRows();
        $page->setPage();
        $page->setUrl(Request::getQueryString());

        // 각 데이터 배열화
        $getData['data'] = gd_htmlspecialchars_stripslashes(gd_isset($data));
        $getData['search'] = gd_htmlspecialchars($this->search);
        unset($this->arrWhere, $this->arrBind);

        return $getData;
    }

    /**
     * 회원정보수정 이벤트 참여 내역 화면노출 정보 세팅
     *
     * @param   array $arrData 이벤트 정보
     *
     * @return  array
     *
     */
    public function setDisplayMemberModifyEventResult($arrData = null)
    {
        if ($arrData['data']['exceptJoinType'] == 'date') {
            $arrData['data']['display']['exceptJoin'] = $arrData['data']['exceptJoinStartDt'] . ' ~ ' . $arrData['data']['exceptJoinEndDt'];
        } else if ($arrData['data']['exceptJoinType'] == 'day') {
            $arrData['data']['display']['exceptJoin'] = '가입 후 ' . $arrData['data']['exceptJoinDay'] . '일 동안 이벤트 대상 제외';
        }

        if ($arrData['data']['benefitCondition'] == 'all') {
            $arrData['data']['display']['benefitCondition'] = '선택한 항목을 모두 수정 시 혜택 지급';
        } else if ($arrData['data']['benefitCondition'] == 'some') {
            $arrData['data']['display']['benefitCondition'] = '선택한 항목 중 1개 이상 수정 시 혜택 지급';
        }

        if ($arrData['data']['benefitType'] == 'mileage') {
            $mileageTrunc = Globals::get('gTrunc.mileage');
            $benefitMileage = gd_number_figure($arrData['data']['benefitMileage'], $mileageTrunc['unitPrecision'], $mileageTrunc['unitRound']);
            $arrData['data']['display']['benefit'] = gd_display_mileage_name() . ' ' . $benefitMileage . gd_display_mileage_unit() . ' 지급';
        } else if ($arrData['data']['benefitType'] == 'coupon') {
            $couponData = $this->getDataByTable(DB_COUPON, $arrData['data']['benefitCouponSno'], 'couponNo', 'couponNm');
            $couponNm = gd_htmlspecialchars_stripslashes($couponData['couponNm']);
            $arrData['data']['display']['benefit'] = '쿠폰 지급 (' . $couponNm . ')';
        } else if ($arrData['data']['benefitType'] == 'manual') {
            $arrData['data']['display']['benefit'] = '수동지급';
        }

        $arrData['data']['eventTypeName'] = ($arrData['data']['eventType'] === 'modify') ? '회원정보 수정' : '평생회원';

        return $arrData;
    }

    /**
     * 회원정보수정 이벤트 등록 및 수정 화면노출 정보 세팅
     *
     * @param   array $arrData 이벤트 정보
     *
     * @return  array
     *
     */
    public function setDisplayMemberModifyEventRegister($arrData = null)
    {
        $mileagePolicy = gd_mileage_give_info();
        $mileageGiveFl = $mileagePolicy['give']['giveFl'];
        $mallSno = Request::get()->get('mallSno', gd_isset($arrData['data']['mallSno'], DEFAULT_MALL_NUMBER));

        // 디폴트 셀렉트 박스 옵션
        $arrData['search']['exceptJoinDayList'] = $this->getExceptJoinDayList();
        $arrData['search']['couponData'] = $this->getMemberModifyEventCouponList();
        $arrData['search']['fieldList'] = $this->getApplyFieldList($mallSno);

        // 이벤트 팝업 설정 > 창위치
        $arrData['data']['popupPositionT'] = ($arrData['data']['popupPositionT']) ? $arrData['data']['popupPositionT'] : 0;
        $arrData['data']['popupPositionL'] = ($arrData['data']['popupPositionL']) ? $arrData['data']['popupPositionL'] : 0;

        if (\Globals::get('gGlobal.isUse')) {
            foreach (\Globals::get('gGlobal.useMallList') as $val) {
                if ($val['sno'] == $mallSno) {
                    $countryCode = $val['domainFl'];
                }
            }
        }

        // 팝업내용
        if ($arrData['data']['eventType']) {
            if ($arrData['data']['eventType'] === 'modify') {
                $arrData['data']['popupContent'] = ($arrData['data']['popupContent']) ? $arrData['data']['popupContent'] : "<div style='text-align: center;'></div><img src='" . PATH_ADMIN_GD_SHARE . "img/member/member_modify_event_" . $countryCode . "_02.png' /><br><br><p><a href='../mypage/my_page_password.php' class='btn_event'><img src='" . PATH_ADMIN_GD_SHARE . "img/member/member_modify_event_" . $countryCode . "_03.png' /></a></p><div>";
            } else {
                $arrData['data']['popupContent'] = ($arrData['data']['popupContent']) ? $arrData['data']['popupContent'] : "<div style='text-align: center;'><img src='" . PATH_ADMIN_GD_SHARE . "img/member/member_modify_life_" . $countryCode . "_02.png' /><br><br><p><a href='../mypage/my_page_password.php' class='btn_event'><img src='" . PATH_ADMIN_GD_SHARE . "img/member/member_life_event_" . $countryCode . "_03.png' /></a></p></div>";
            }
        } else {
            $arrData['data']['popupContent'] = ($arrData['data']['popupContent']) ? $arrData['data']['popupContent'] : "<div style='text-align: center;'><img src='" . PATH_ADMIN_GD_SHARE . "img/member/member_modify_event_" . $countryCode . "_02.png' /><br><br><p><a href='../mypage/my_page_password.php' class='btn_event'><img src='" . PATH_ADMIN_GD_SHARE . "img/member/member_modify_event_" . $countryCode . "_03.png' /></a></p></div>";
        }

        $arrData['data']['display']['directHtmlEditorView'] = ($arrData['data']['popupContentType'] === 'direct') ? "display:block;" : "display:none;";

        // 등록 체크 항목
        if (empty($arrData['data']['sno'])) {
            $arrData['checked']['exceptJoinType']['unlimit'] =
            $arrData['checked']['benefitCondition']['some'] =
            $arrData['checked']['loginDisplayFl']['y'] =
            $arrData['checked']['mainDisplayFl']['n'] =
            $arrData['checked']['mypageDisplayFl']['n'] =
            $arrData['checked']['todayUnSeeFl']['n'] =
            $arrData['checked']['popupContentType']['default'] =
            $arrData['checked']['eventType']['modify'] = 'checked="checked"';
            // 상점별 노출 항목 처리
            if ($mallSno > DEFAULT_MALL_NUMBER) {
                $arrData['checked']['benefitProvideType']['manual'] = 'checked="checked"';
                $arrData['disabled']['benefitType']['mileage'] =
                $arrData['disabled']['benefitType']['coupon'] =
                $arrData['disabled']['benefitProvideType']['auto'] = 'disabled="disabled"';
                $arrData['disabled']['benefit']['auto'] = 'display-none';
            } else {
                $arrData['checked']['benefitProvideType']['auto'] =
                $arrData['checked']['benefitType']['mileage'] = 'checked="checked"';
                $arrData['disabled']['benefit']['manual'] = 'display-none';

                // 마일리지 및 쿠폰 사용여부에 따른 항목 비활성화
                if ($mileageGiveFl != 'y' || gd_use_coupon() == false) {
                    if ($mileagePolicy['give']['giveFl'] != 'y') {
                        $arrData['checked']['benefitType']['coupon'] = 'checked="checked"';
                        unset($arrData['checked']['benefitType']['mileage']);
                        $arrData['disabled']['benefitType']['mileage'] =
                        $arrData['disabled']['benefitMileage'] = 'disabled="disabled"';
                    }
                    if (gd_use_coupon() == false) {
                        $arrData['disabled']['benefitType']['coupon'] =
                        $arrData['disabled']['benefitCouponSno'] =
                        $arrData['disabled']['benefitCouponRegister'] = 'disabled="disabled"';
                    }
                    if ($mileageGiveFl != 'y' && gd_use_coupon() == false) {
                        $arrData['checked']['benefitProvideType']['manual'] = 'checked="checked"';
                        $arrData['disabled']['benefitProvideType']['auto'] = 'disabled="disabled"';
                        $arrData['disabled']['benefit']['auto'] = 'display-none';
                        unset($arrData['checked']['benefitProvideType']['auto'], $arrData['disabled']['benefit']['manual']);
                    }
                }
            }

            return $arrData;
        }

        // 삭제된 관리자 확인
        $manager = \App::load('\\Component\\Member\\Manager');
        $manager->displayListData($arrData['data']);

        // 마일리지 소수점 처리
        $mileageTrunc = Globals::get('gTrunc.mileage');
        $arrData['data']['display']['benefitMileage'] = gd_number_figure($arrData['data']['benefitMileage'], $mileageTrunc['unitPrecision'], $mileageTrunc['unitRound']);

        // 이벤트 항목
        $arrData['data']['display']['eventApplyField'] = explode(STR_DIVISION, $arrData['data']['eventApplyField']);

        // 이벤트 시작일
        if ($arrData['data']['eventStartDt']) {
            $arrData['data']['display']['eventStartDate'] = date('Y-m-d', strtotime($arrData['data']['eventStartDt']));
            $arrData['data']['display']['eventStartTime'] = date('H:i:s', strtotime($arrData['data']['eventStartDt']));
        }

        // 이벤트 종료일
        if ($arrData['data']['eventEndDt']) {
            $arrData['data']['display']['eventEndDate'] = date('Y-m-d', strtotime($arrData['data']['eventEndDt']));
            $arrData['data']['display']['eventEndTime'] = date('H:i:s', strtotime($arrData['data']['eventEndDt']));
        }

        // 이벤트를 등록한 상점 정보
        $mallList = Globals::get('gGlobal.mallList');
        foreach ($mallList as $mVal) {
            if ($arrData['data']['mallSno'] == $mVal['sno']) {
                $arrData['data']['domainFl'] = $mVal['domainFl'];
                $arrData['data']['mallName'] = $mVal['mallName'];
            }
        }

        // 수정 체크 항목
        $arrData['disabled']['benefitProvideType']['auto'] =
        $arrData['disabled']['benefitProvideType']['manual'] = 'disabled="disabled"';
        $arrData['checked'] = SkinUtils::setChecked(
            [
                'exceptJoinType',
                'benefitCondition',
                'adminModifyFl',
                'benefitProvideType',
                'benefitType',
                'eventType',
                'loginDisplayFl',
                'mainDisplayFl',
                'mypageDisplayFl',
                'todayUnSeeFl',
                'popupContentType',
            ], $arrData['data']
        );

        // 혜택지급 상태에 따른 비활성화 처리
        if ($arrData['data']['benefitType'] == 'manual') {
            $arrData['disabled']['benefitType']['mileage'] =
            $arrData['disabled']['benefitType']['coupon'] =
            $arrData['disabled']['benefitMileage'] =
            $arrData['disabled']['benefitCouponSno'] =
            $arrData['disabled']['benefitCouponRegister'] = 'disabled="disabled"';
            $arrData['disabled']['benefit']['auto'] = 'display-none';
        } else {
            $arrData['disabled']['benefit']['manual'] = 'display-none';
        }

        // 마일리지 및 쿠폰 사용여부에 따른 항목 비활성화
        if ($mileageGiveFl != 'y' || gd_use_coupon() == false) {
            if ($mileageGiveFl != 'y') {
                $arrData['disabled']['benefitType']['mileage'] =
                $arrData['disabled']['benefitMileage'] = 'disabled="disabled"';
            }
            if (gd_use_coupon() == false) {
                $arrData['disabled']['benefitType']['coupon'] =
                $arrData['disabled']['benefitCouponSno'] =
                $arrData['disabled']['benefitCouponRegister'] = 'disabled="disabled"';
            }
        }

        return $arrData;
    }

    /**
     * 회원정보수정 이벤트 리스트 화면노출 정보 세팅
     *
     * @param   array $arrData 이벤트 리스트 정보
     *
     * @return  array
     *
     */
    public function setDisplayMemberModifyEventList($arrData = null)
    {
        // 디폴트 셀렉트 박스 옵션
        $arrData['search']['searchKeywordList'] = $this->getSearchKeywordList();
        $arrData['search']['searchStatus'] = $this->getSearchStatus();
        $arrData['search']['searchDateStatusList'] = $this->getSearchDateStatusList();
        $arrData['search']['sortList'] = $this->getSortList();
        $arrData['search']['searchEventType'] = $this->getSearchEventType();
        $arrData['checked']['mallFl'][$arrData['search']['mallStatus']] = 'checked="checked"';

        // 검색된 데이터가 없을 경우
        if (empty($arrData['data'])) {
            unset($arrData['data']);

            return $arrData;
        }

        // 삭제된 관리자 확인
        $manager = \App::load('\\Component\\Member\\Manager');
        $manager->displayListData($arrData['data']);

        // 상점 리스트
        $mallList = Globals::get('gGlobal.mallList');

        // 기간지난 이벤트 번호
        $endMemberModifyEventSno = [];

        foreach ($arrData['data'] as $key => $val) {
            // 이벤트 진행상태
            if ($val['eventStatusFl'] == 'y') {
                $now = strtotime('now');
                if ($now < strtotime($val['eventStartDt'])) {
                    $arrData['data'][$key]['display']['eventStatusFl'] = '대기';
                } else if ($now >= strtotime($val['eventStartDt']) && $now < strtotime($val['eventEndDt'])) {
                    $arrData['data'][$key]['display']['eventStatusFl'] = '진행중';
                } else {
                    $endMemberModifyEventSno[] = $val['sno'];
                    $arrData['data'][$key]['display']['eventStatusFl'] = '종료';
                }
            } else if ($val['eventStatusFl'] == 'n') {
                $arrData['data'][$key]['display']['eventStatusFl'] = '종료';
            }

            // 이벤트 등록일
            if ($val['regDt']) {
                $arrData['data'][$key]['display']['regDate'] = date('Y-m-d', strtotime($val['regDt']));
                $arrData['data'][$key]['display']['regTime'] = date('H:i:s', strtotime($val['regDt']));
            }

            // 이벤트 시작일
            if ($val['eventStartDt']) {
                $arrData['data'][$key]['display']['eventStartDate'] = date('Y-m-d', strtotime($val['eventStartDt']));
                $arrData['data'][$key]['display']['eventStartTime'] = date('H:i', strtotime($val['eventStartDt']));
            }

            // 이벤트 종료일
            if ($val['eventEndDt']) {
                $arrData['data'][$key]['display']['eventEndDate'] = date('Y-m-d', strtotime($val['eventEndDt']));
                $arrData['data'][$key]['display']['eventEndTime'] = date('H:i', strtotime($val['eventEndDt']));
            }

            // 이벤트를 등록한 상점 정보
            foreach ($mallList as $mVal) {
                if ($val['mallSno'] == $mVal['sno']) {
                    $arrData['data'][$key]['domainFl'] = $mVal['domainFl'];
                    $arrData['data'][$key]['mallName'] = $mVal['mallName'];
                }
            }

            // 이벤트 유형
            $arrData['data'][$key]['eventTypeName'] = ($val['eventType'] == 'modify') ? '회원정보 수정' : '평생회원';

            // 참여내역관리 (총 회원참여 수)
            $arrData['data'][$key]['memberShipTotal'] = $this->getCountEventResultCount($val['sno']);
        }

        // 기간지난 이벤트는 종료처리
        if (count($endMemberModifyEventSno) > 0) {
            $this->closeMemberModifyEvent($endMemberModifyEventSno);
            unset($endMemberModifyEventSno);
        }

        return $arrData;
    }

    /**
     * 회원정보수수정 이벤트 기간 체크
     *
     * @param  string  $startDt 시작기간
     * @param  string  $endDt   종료기간
     * @param  integer $mallSno 상점번호
     * @param  integer $eventNo 이벤트번호
     * @param  string  $eventType 이벤트 유형
     *
     * @return bool
     *
     */
    public function checkActiveEventPeriod($startDt, $endDt, $mallSno = DEFAULT_MALL_NUMBER, $eventNo = null, $eventType)
    {
        if (gd_isset($startDt) == null || gd_isset($endDt) == null) {
            return false;
        }

        $where = "WHERE ('" . $endDt . "' >= eventStartDt AND eventEndDt >= '" . $startDt . "')";
        $where .= " AND eventStatusFl = 'y'";
        $where .= " AND mallSno = " . $mallSno;
        $where .= " AND eventType = '" .$eventType ."'";
        if (gd_isset($eventNo, '') != '') {
            $where .= ' AND sno != ' . $eventNo;
        }

        return $this->getCount(DB_MEMBER_MODIFY_EVENT, '1', $where) > 0;
    }

    /**
     * 회원정보수정 이벤트 등록
     *
     * @param   array $getData 입력 데이터
     *
     * @throws  Exception
     *
     */
    public function registerMemberModifyEvent($getData = null)
    {
        if (empty($getData)) {
            throw new Exception(__('등록할 데이터가 없습니다.'));
        } else {
            $eventData = StringUtils::trimValue($getData);
        }

        // 입력 데이터 세팅
        $eventData['managerNo'] = Session::get('manager.sno');
        $eventData['eventStartDt'] = $getData['eventStartDt']['date'] . ' ' . $getData['eventStartDt']['time'] . ':00';
        $eventData['eventEndDt'] = $getData['eventEndDt']['date'] . ' ' . $getData['eventEndDt']['time'] . ':59';

        // 기간 검증
        $now = strtotime('now');
        if ($now > strtotime($eventData['eventStartDt']) && $now > strtotime($eventData['eventEndDt'])) {
            $eventData['eventStatusFl'] = 'n';
        } else {
            $eventData['eventStatusFl'] = 'y';
        }

        // 이벤트 항목 string 처리
        if (empty($getData['eventApplyField']) == false && is_array($getData['eventApplyField'])) {
            $eventData['eventApplyField'] = implode(STR_DIVISION, $getData['eventApplyField']);
        }

        // 불필요 데이터 삭제
        if ($getData['exceptJoinType'] == 'unlimit') {
            unset($eventData['exceptJoinStartDt'], $eventData['exceptJoinEndDt'], $eventData['exceptJoinDay']);
        } else if ($getData['exceptJoinType'] == 'date') {
            unset($eventData['exceptJoinDay']);
        } else if ($getData['exceptJoinType'] == 'day') {
            unset($eventData['exceptJoinStartDt'], $eventData['exceptJoinEndDt']);
        }

        // 수동지급시 지급혜택도 수동으로 처리
        if ($getData['benefitProvideType'] == 'manual') {
            $eventData['benefitType'] = 'manual';
            unset($eventData['benefitMileage'], $eventData['benefitCouponSno']);
        }

        // 입력값 검증
        $this->validateEvent($eventData);
        $eventTypeTitle = ($eventData['eventType'] === 'modify') ? '회원정보 수정' : '평생회원';

        // 이벤트기간 중복 검증
        if ($this->checkActiveEventPeriod($eventData['eventStartDt'], $eventData['eventEndDt'], $eventData['mallSno'], null, $eventData['eventType'])) {
            throw new Exception(__('다른 '.$eventTypeTitle.' 이벤트와 진행기간은 중복될 수 없습니다.'));
        }

        $arrBind = $this->db->get_binding(DBTableField::tableMemberModifyEvent(), $eventData, 'insert');
        $this->db->set_insert_db(DB_MEMBER_MODIFY_EVENT, $arrBind['param'], $arrBind['bind'], 'y');

        if ($this->insertId() === 0) {
            throw new Exception(__($eventTypeTitle. ' 이벤트 등록 중 오류가 발생하였습니다.'));
        }
    }

    /**
     * 회원정보수정 이벤트 수정
     *
     * @param   array $getData 입력 데이터
     *
     * @throws  Exception
     *
     */
    public function modifyMemberModifyEvent($getData = null)
    {
        if (empty($getData)) {
            throw new Exception(__('등록할 데이터가 없습니다.'));
        } else {
            $eventData = StringUtils::trimValue($getData);
        }

        // 입력 데이터 세팅
        $eventData['eventStartDt'] = $getData['eventStartDt']['date'] . ' ' . $getData['eventStartDt']['time'] . ':00';
        $eventData['eventEndDt'] = $getData['eventEndDt']['date'] . ' ' . $getData['eventEndDt']['time'] . ':00';

        // 기간 검증
        if ($eventData['eventStatusFl'] == 'y') {
            $now = strtotime('now');
            if ($now > strtotime($eventData['eventStartDt']) && $now > strtotime($eventData['eventEndDt'])) {
                $eventData['eventStatusFl'] = 'n';
            }
        }

        // 종료된 이벤트 수정 불가
        if ($eventData['eventStatusFl'] == 'n') {
            throw new Exception(__('종료된 이벤트는 수정할 수 없습니다.'));
        }

        // 이벤트 항목 string 처리
        if (empty($getData['eventApplyField']) == false && is_array($getData['eventApplyField'])) {
            $eventData['eventApplyField'] = implode(STR_DIVISION, $getData['eventApplyField']);
        }

        // 지급혜택 미선택값 초기화
        if ($getData['benefitType'] == 'mileage') {
            $eventData['benefitCouponSno'] = 0;
        } else if ($getData['benefitType'] == 'coupon') {
            $eventData['benefitMileage'] = 0;
        }

        // 제외 가입기간 미선택값 초기화
        if ($getData['exceptJoinType'] == 'unlimit') {
            $eventData['exceptJoinStartDt'] = $eventData['exceptJoinEndDt'] = null;
            $eventData['exceptJoinDay'] = 0;
        } else if ($getData['exceptJoinType'] == 'date') {
            $eventData['exceptJoinDay'] = 0;
        } else if ($getData['exceptJoinType'] == 'day') {
            $eventData['exceptJoinStartDt'] = $eventData['exceptJoinEndDt'] = null;
        }

        // 관리자 수정 초기화
        gd_isset($eventData['loginDisplayFl'], 'n');
        gd_isset($eventData['mainDisplayFl'], 'n');
        gd_isset($eventData['mypageDisplayFl'], 'n');
        gd_isset($eventData['todayUnSeeFl'], 'n');
        gd_isset($eventData['popupContentType'], 'default');
        gd_isset($eventData['popupContent'], '');

        // 입력값 검증
        $this->validateEvent($eventData);

        // 이벤트기간 중복 검증
        if ($this->checkActiveEventPeriod($eventData['eventStartDt'], $eventData['eventEndDt'], $eventData['mallSno'], $eventData['sno'], $eventData['eventType'])) {
            throw new Exception(__('다른 회원정보 수정 이벤트와 진행기간은 중복될 수 없습니다.'));
        }

        // 수정 기본 항목 업데이트 제외
        $arrExclude[] = 'mallSno';
        $arrExclude[] = 'managerNo';
        $arrExclude[] = 'benefitProvideType';

        // 수동지급일 경우 지급혜택 제외
        if ($getData['benefitProvideType'] == 'manual') {
            $arrExclude[] = 'benefitType';
            $arrExclude[] = 'benefitMileage';
            $arrExclude[] = 'benefitCouponSno';
        }

        $arrBind = $this->db->updateBinding(DBTableField::getBindField('tableMemberModifyEvent'), $eventData, array_keys($eventData), $arrExclude);
        $this->db->bind_param_push($arrBind['bind'], 'i', $getData['sno']);
        $this->db->set_update_db(DB_MEMBER_MODIFY_EVENT, $arrBind['param'], 'sno = ?', $arrBind['bind']);
    }

    /**
     * 회원정보수정 이벤트 입력값 검증
     *
     * @param   array $eventData 입력 데이터
     *
     * @throws  Exception
     *
     */
    public function validateEvent($eventData)
    {
        if (empty($eventData['managerNo']) || $eventData['managerNo'] < 1) {
            throw new Exception(__('관리자 번호가 없습니다.'));
        }

        // 종료일보다 늦은 시작일 검증
        DateTimeUtils::intervalDateTime($eventData['eventStartDt'], $eventData['eventEndDt']);

        $this->v->init();
        $this->addRules($eventData);
        if ($this->v->act($eventData, true) === false) {
            $errMsg = implode("<br/>", $this->v->errors);
            throw new Exception($errMsg);
        }
    }

    /**
     * 회원정보수정 이벤트 입력값 검증 규칙 추가
     *
     * @param   array $eventData 이벤트 입력 데이터
     *
     */
    public function addRules($eventData)
    {
        $this->v->add('mallSno', 'number', true, '{상점번호}');
        $this->v->add('eventNm', '', true, '{이벤트명}');
        $this->v->add('eventStatusFl', 'pattern', true, '{이벤트 진행상태}', '/^(' . implode('|', array_values($this->eventStatusFl)) . ')$/');
        $this->v->add('eventStartDt', 'datetime', true, '{이벤트 시작일}');
        $this->v->add('eventEndDt', 'datetime', true, '{이벤트 종료일}');
        if ($eventData['modify']) {
            $this->v->add('eventApplyField', '', true, '{이벤트 항목}');
        }
        $this->v->add('exceptJoinType', 'pattern', true, '{이벤트 제외 가입기간 설정}', '/^(' . implode('|', array_values($this->exceptJoinType)) . ')$/');
        if ($eventData['eventJoinType'] == 'date') {
            $this->v->add('exceptJoinStartDt', 'datetime', true, '{이벤트 제외 가입기간 시작일}');
            $this->v->add('exceptJoinEndDt', 'datetime', true, '{이벤트 제외 가입기간 종료일}');
        } else if ($eventData['eventJoinType'] == 'day') {
            $this->v->add('exceptJoinDay', 'number', true, '{이벤트 제외 가입기간 지정일}');
        }
        $this->v->add('benefitCondition', 'pattern', true, '{혜택 지급 조건}', '/^(' . implode('|', array_values($this->benefitCondition)) . ')$/');
        $this->v->add('adminModifyFl', 'yn', false, '{관리자 수정 적용여부}');
        $this->v->add('benefitProvideType', 'pattern', true, '{혜택 지급 방법}', '/^(' . implode('|', array_values($this->benefitProvideType)) . ')$/');
        $this->v->add('benefitType', 'pattern', true, '{지급혜택}', '/^(' . implode('|', array_values($this->benefitType)) . ')$/');
        if ($eventData['benefitProvideType'] == 'auto') {
            if ($eventData['benefitType'] == 'mileage') {
                $this->v->add('benefitMileage', 'number', true, '{지급혜택 마일리지}');
            } else if ($eventData['benefitType'] == 'coupon') {
                $this->v->add('benefitCouponSno', 'number', true, '{지급혜택 쿠폰}');
            }
        }
        $this->v->add('managerNo', 'number', true, '{관리자번호}');
        $this->v->add('eventType', 'pattern', true, '{이벤트 유형}', '/^(' . implode('|', array_values($this->eventType)) . ')$/');
        $this->v->add('loginDisplayFl', 'yn', false, '{노출페이지 로그인 완료 즉시}');
        $this->v->add('mainDisplayFl', 'yn', false, '{노출페이지 메인}');
        $this->v->add('mypageDisplayFl', 'yn', false, '{노출페이지 마이페이지}');
        $this->v->add('popupPositionT', 'number', true, '{팝업 상단 위치}');
        $this->v->add('popupPositionL', 'number', true, '{팝업 왼쪽 위치}');
        $this->v->add('todayUnSeeFl', 'yn', false, '{오늘 하루 보이지 않음 여부}');
        $this->v->add('popupContentType', 'pattern', true, '{팝업 유형}', '/^(' . implode('|', array_values($this->popupContentType)) . ')$/');
        $this->v->add('popupContent', '', true, '{팝업 내용}');
    }

    /**
     * 회원정보수정 이벤트 쿠폰 리스트
     *
     * @return mixed 쿠폰 리스트
     *
     */
    public function getMemberModifyEventCouponList()
    {
        Request::get()->set('couponSaveType', 'auto');
        Request::get()->set('couponEventType', 'memberModifyEvent');

        $couponAdmin = App::load('\\Component\\Coupon\\CouponAdmin');
        $couponAdminList = $couponAdmin->getCouponAdminList();
        $couponData = [];
        foreach ($couponAdminList['data'] as $index => $item) {
            $couponData[$item['couponNo']] = $item;
        }

        Request::get()->del('couponSaveType');
        Request::get()->del('couponEventType');
        unset($couponAdminList);

        return $couponData;
    }

    /**
     * 이벤트 적용 항목 리스트
     *
     * @param  integer $mallSno 상점번호
     *
     * @return array
     *
     */
    public function getApplyFieldList($mallSno = DEFAULT_MALL_NUMBER)
    {
        $fieldData = [
            'memPw'      => '비밀번호 수정',
            'maillingFl' => '이메일 수신동의',
            'smsFl'      => 'SMS 수신동의',
            'cellPhone'  => '휴대폰번호 수정',
            'address'    => '주소 수정',
        ];

        // 해외몰 주소 및 sms 수신동의 항목 제거
        if ($mallSno > DEFAULT_MALL_NUMBER) {
            unset($fieldData['address']);
            unset($fieldData['smsFl']);
        }

        // 회원가입 추가정보 사용할 경우
        $joinItem = gd_policy('member.joinitem', $mallSno);
        foreach ($joinItem as $key => $val) {
            switch ($key) {
                case 'ex1':
                case 'ex2':
                case 'ex3':
                case 'ex4':
                case 'ex5':
                case 'ex6':
                    if ($val['use'] == 'y') {
                        $fieldData[$key] = $val['name'] . ' 수정';
                    }
                    break;
                default:
                    break;
            }
        }

        return $fieldData;
    }

    /**
     * getExceptJoinDayList
     *
     * @return array
     *
     */
    public function getExceptJoinDayList()
    {
        return $this->exceptJoinDayList;
    }

    /**
     * getSearchDateStatusList
     *
     * @return array
     *
     */
    public function getSearchDateStatusList()
    {
        return $this->searchDateStatusList;
    }

    /**
     * getSearchKeywordList
     *
     * @return array
     *
     */
    public function getSearchKeywordList()
    {
        return $this->searchKeywordList;
    }

    /**
     * getSearchKeywordResult
     *
     * @return array
     *
     */
    public function getSearchKeywordResult()
    {
        return $this->searchKeywordResult;
    }

    /**
     * getSearchStatus
     *
     * @return array
     *
     */
    public function getSearchStatus()
    {
        return $this->searchStatus;
    }

    /**
     * getSortList
     *
     * @return array
     *
     */
    public function getSortList()
    {
        return $this->sortList;
    }

    /**
     * getSortList
     *
     * @return array
     *
     */
    public function getSearchEventType()
    {
        return $this->searchEventType;
    }

    /**
     * 팽셩회원 이벤트참여 > 개인정보 유효기간 업데이트
     *
     * @param  array $expirationFl 회원정보 (개인정보유효기간)
     *
     * @throws Exception
     */
    public function updateExpirationFl($expirationFl)
    {
        if (gd_isset($expirationFl['memNo']) == null) {
            throw new Exception(__('오류가 발생하였습니다.'));
        }
        /** @var \Bundle\Component\Member\Member $member */
        $member = App::load('\\Component\\Member\\Member');
        $member->updateMemberByMemberNo($expirationFl['memNo'], ['expirationFl', 'lifeMemberConversionDt'], ['999', date('Y-m-d H:i:s')]);
        $historyService = \App::load('Component\\Member\\History');
        $historyService->setAfter(['memNo' => $expirationFl['memNo']]);
        $historyService->setProcessor('member');
        $historyService->setManagerNo($expirationFl['memNo']);
        $historyService->setProcessorIp(Request::getRemoteAddress());
        $historyService->insertHistory(
            'expirationFl', [
                $expirationFl['expirationFl'],
                '999',
            ]
        );
    }
}
