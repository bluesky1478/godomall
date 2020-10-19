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

namespace Bundle\Component\Member\Group;

use App;
use Component\Database\DBTableField;
use Component\Member\Member;
use Component\Sms\Code;
use Component\Sms\SmsAutoCode;
use Component\Sms\SmsAutoObserver;
use Exception;
use Framework\Object\SimpleStorage;
use Framework\Utility\ComponentUtils;
use Framework\Utility\DateTimeUtils;
use Session;

/**
 * Class GroupAppraisal
 * @package Bundle\Component\Member\Group\Util
 * @author  yjwee
 */
class Appraisal extends \Component\AbstractComponent
{
    /** @var array 등급명 */
    protected $groupNames = [];
    /** @var  array 등급정보 */
    protected $groupConfig;
    /** @var  array 기본등급정보 */
    protected $defaultGroupConfig;
    /** @var  integer 전체회원수 */
    protected $countByTotalMember;
    /** @var SimpleStorage $group 등급정책 */
    protected $group;
    /** @var  SimpleStorage */
    protected $appraisalGroupConfig;
    /** @var  AppraisalSearch */
    protected $search;
    /** @var AppraisalMailSender 등급평가 메일발송 */
    protected $mailSender;
    /** @var array 등급평가 완료된 회원번호 */
    protected $completeMemNo = [];
    /** @var string 등급평가일시 */
    protected $appraisalDateTime;
    /** @var bool 현재 평가 등급 기준치 만족 여부 */
    protected $isCurrentGroupMember = true;
    /** @var bool 기본등급 기준치 만족 여부 */
    protected $isDefaultGroupMember = false;
    /** @var array 현재 평가대상 회원 */
    protected $currentMembers;
    /** @var array 등급평가 제외 설정된 등급 sno 배열 */
    protected  $apprExclusionOfRating;
    /** @var array 그룹 sno별 등급 */
    protected  $groupSort;
    /** @var array 그룹 관리 설정 */
    public $policy;
    /** @var array 기본 설정 */
    public $basicInfo;
    /**
     * Appraisal constructor.
     *
     * @param AppraisalSearch|null $search 등급평가대상조회
     */
    public function __construct(AppraisalSearch $search = null)
    {
        parent::__construct();
        $this->setSearch($search);
        $this->groupConfig();
        $this->validateAppraisalGroupConfig();

        $this->group = new SimpleStorage();
        $this->mailSender = new AppraisalMailSender();

        $this->policy = ComponentUtils::getPolicy('member.group');
        $this->basicInfo = gd_policy('basic.info');
        ini_set('memory_limit', '-1');
    }

    /**
     * setSearch
     *
     * @param AppraisalSearch|null $search               등급평가대상조회
     * @param SimpleStorage        $appraisalGroupConfig 등급평가 설정
     */
    public function setSearch(AppraisalSearch $search = null, SimpleStorage $appraisalGroupConfig = null)
    {
        $this->search = $search;
        if ($this->search === null) {
            $this->search = new AppraisalSearch();
        }
        $this->appraisalGroupConfig($appraisalGroupConfig);
        $this->search->setAppraisalGroupConfig($this->appraisalGroupConfig);
    }

    /**
     * appraisalGroupConfig
     *
     * @param SimpleStorage $appraisalGroupConfig
     *
     * @throws Exception
     */
    public function appraisalGroupConfig(SimpleStorage $appraisalGroupConfig = null)
    {
        if ($appraisalGroupConfig === null) {
            $data = ComponentUtils::getPolicy('member.group');
        } else {
            $data = $appraisalGroupConfig->all();
        }

        if ($data['apprSystem'] == 'point' && $data['appraisalPointOrderPriceFl'] != 'y' && $data['appraisalPointOrderRepeatFl'] != 'y' && $data['appraisalPointReviewRepeatFl'] != 'y' && $data['appraisalPointLoginRepeatFl'] != 'y') {
            $logger = \App::getInstance('logger');
            $logger->warning('Not set appraisal performance point');
            throw new Exception(__('실적 점수제 평가 설정 값이 없습니다.'));
        }
        $this->appraisalGroupConfig = new SimpleStorage($data);
    }

    /**
     * groupConfig
     * @return void
     * @throws Exception
     */
    public function groupConfig()
    {
        $dao = \App::load('Component\\Member\\Group\\GroupDAO');
        $params = [];
        $isAppraisalFigure = $this->appraisalGroupConfig->get('apprSystem') == 'figure';
        if ($isAppraisalFigure) {
            $params['apprSystem'] = $this->appraisalGroupConfig->get('apprSystem');
        }
        $groups = $dao->selectAppraisalGroups($params);
        $validation = \App::load('Component\\Member\\Group\\GroupValidation');
        $validation->setTargetGroups($groups);
        foreach ($groups as $group) {
            $validation->setDomain(new \Component\Member\Group\GroupDomain($group));
            if ($isAppraisalFigure) {
                $validation->validateFigure();
            } else {
                $validation->validatePoint();
            }
            $validation->validateOverlapStandard();
            $this->groupSort[$group['sno']] = $group['groupSort']; // 그룹 key: sno, value: sort 배열 생성
        }
        // 회원등급 평가시 제외인 등급 그룹 배열 생성
        $exclusionGroups = $dao->selectGroupExclusion();
        foreach ($exclusionGroups as $key){
            if($key['apprExclusionOfRatingFl'] == 'y')
                $this->apprExclusionOfRating[$key['sno']] = $key['sno'];
        }

        $this->setGroupNames();
        $lastIndex = count($groups) - 1;
        $defaultGroup = $groups[$lastIndex];
        if (!$this->isDefaultGroup($defaultGroup)) {
            throw new \Exception('기준(가입) 등급 설정 오류 입니다.');
        }
        $this->defaultGroupConfig = $defaultGroup;
        unset($groups[$lastIndex]);
        $this->groupConfig = $groups;
    }

    /**
     * setGroupNames
     * @return void
     */
    public function setGroupNames()
    {
        foreach ($this->groupConfig as $index => $item) {
            $this->groupNames[$item['sno']] = $item['groupNm'];
        }
    }

    /**
     * validateAppraisalGroupConfig
     *
     * @throws Exception 등급 검증 오류
     * @return void
     */
    public function validateAppraisalGroupConfig()
    {
        if ($this->appraisalGroupConfig->get('apprSystem', '') == '') {
            throw new Exception(__('회원등급 평가방법 정보가 없습니다.'));
        }

        if ($this->appraisalGroupConfig->get('calcPeriodFl', '') == 'y') {
            if ($this->appraisalGroupConfig->get('calcPeriodBegin', '') == '') {
                throw new Exception(__('등급 평가에 필요한 실적 계산기간 기준 정보가 없습니다.'));
            }
            if ($this->appraisalGroupConfig->get('calcPeriodMonth', '') == '') {
                throw new Exception(__('등급 평가에 필요한 실적 계산기간 기간 정보가 없습니다.'));
            }
        }
    }

    /**
     * getDefaultGroupConfig
     *
     * @return array
     */
    public function getDefaultGroupConfig()
    {
        return $this->defaultGroupConfig;
    }

    /**
     * getGroupConfig
     *
     * @return array
     */
    public function getGroupConfig()
    {
        return $this->groupConfig;
    }

    /**
     * appraisalGroupBySearch
     *
     * @return void
     */
    public function appraisalGroupBySearch()
    {
        $logger = \App::getInstance('logger');
        $logger->notice('Start group appraisal.');
        $total = $this->countTotalMember();
        $limit = $this->search->getAppraisalLimit();
        $this->countByTotalMember = $this->appraisalCountByTotalMember($total, $limit);
        $logger->notice('총 회원[' . $total . '], 1회 평가당 제한 인원수[' . $limit . '], 평가 실행 횟수[' . $this->countByTotalMember . ']');
        $this->completeMemNo = [];

        $this->appraisalDateTime = DateTimeUtils::dateFormat('Y-m-d H:i:s', 'now');

        for ($offset = 0; $offset < $this->countByTotalMember; $offset++) {
            $this->appraisalGroupByOffset($offset);
            // 평가 대상으로 검색된 회원 초기화
            $this->currentMembers = null;
        }
        $logger->notice('End group appraisal.');
    }

    /**
     * appraisalGroupByOffset
     *
     * @param integer $offset 조회시작 번호
     *
     * @return void
     */
    public function appraisalGroupByOffset($offset)
    {
        $logger = \App::getInstance('logger');
        $db = \App::getInstance('DB');
        $this->search->setAppraisalOffset($offset);
        $logger->info(sprintf('Start appraisal group set offset %s', $offset));

        foreach ($this->groupConfig as $index => $item) {
            $db->ping();
            $logger->info(sprintf('Start group name is %s, group sno is %s, group sort is %s', $item['groupNm'], $item['sno'], $item['groupSort']));
            $this->group = new SimpleStorage($item);
            if ($this->currentMembers === null) {
                $this->currentMembers = $this->search->search($this->group);
            }
            if ($this->search->hasAppraisalMember($this->currentMembers)) {
                $logger->info(sprintf('has appraisal member count is %d', count($this->currentMembers)));
                $this->updateGroupWithAddMailReceiver($this->currentMembers);
            }
            $logger->info(sprintf('End group name is %s, group sno is %s, group sort is %s', $item['groupNm'], $item['sno'], $item['groupSort']));
        }

        if ($this->policy['downwardAdjustment'] == 'y') {
            $this->isDefaultGroupMember = true;
            $logger->info(sprintf('Start group name is %s, group sno is %s, group sort is %s', $this->defaultGroupConfig['groupNm'], $this->defaultGroupConfig['sno'], $this->defaultGroupConfig['groupSort']));
            $this->group = new SimpleStorage($this->defaultGroupConfig);
            $this->search->setAppraisalDateTime($this->appraisalDateTime);
            $this->currentMembers = $this->search->searchDefaultGroup($this->appraisalDateTime);
            if ($this->search->hasAppraisalMember($this->currentMembers)) {
                $db->ping();
                $logger->info(sprintf('has appraisal member count is %d', count($this->currentMembers)));
                $this->updateGroupWithAddMailReceiver($this->currentMembers);
            } else {
                $logger->info('Not found appraisal members by default group', $this->defaultGroupConfig);
            }
        }
        $logger->info(sprintf('End group name is %s, group sno is %s, group sort is %s', $this->defaultGroupConfig['groupNm'], $this->defaultGroupConfig['sno'], $this->defaultGroupConfig['groupSort']));
        $this->isDefaultGroupMember = false;
    }

    /**
     * appraisalMailSend
     * @return void
     */
    public function appraisalMailSend()
    {
        $this->mailSender->send();
    }

    /**
     * appraisalCountByTotalMember
     *
     * @param integer $total 전체회원수
     * @param integer $limit 조회할 회원 수
     *
     * @return float 조회할 횟수
     */
    public function appraisalCountByTotalMember($total, $limit)
    {
        return $total <= $limit ? 1 : (ceil($total / $limit) + 1);
    }

    /**
     * updateGroupWithAddMailReceiver
     *
     * @param array $members 등급변경할 회원
     *                       AppraisalSearch::search 조회
     *
     * @return void
     */
    public function updateGroupWithAddMailReceiver(array $members)
    {
        $logger = \App::getInstance('logger');
        $session = \App::getInstance('session');
        $db = \App::getInstance('DB');
        try {
            $groupSno = $this->group->get('sno');
            $settleGb = $this->group->get('settleGb');

            $session->del(Member::SESSION_MODIFY_MEMBER_INFO);

            $db->begin_tran();
            $passwordCheckFl = gd_isset(\Request::post()->get('passwordCheckFl'), 'y');
            if($passwordCheckFl != 'n') {
                $smsSender = \App::load(\Component\Sms\SmsSender::class);
                $smsSender->validPassword(\Request::post()->get('password'));
            }
            $count = 500;
            foreach ($members as $index => $item) {
                $count--;
                if ($count == 0) {
                    $db->ping();
                    $count = 500;
                }
                $session->set(Member::SESSION_MODIFY_MEMBER_INFO, $item);

                $storage = new SimpleStorage($item);
                $storage->set('beforeGroupSno', $item['groupSno']);
                $storage->set('groupSno', $groupSno);
                $storage->set('settleGb', $settleGb);

                $memNo = $storage->get('memNo');
                array_push($this->completeMemNo, $memNo);
                // 현재 등급이 '등급평가시 제외' 설정이 아닐 때
                if(array_key_exists($item['groupSno'], $this->apprExclusionOfRating) == false) {
                    $this->checkGroupByAppraisalSystem($item);
                    $beforeGroupSort = $this->groupSort[$item['groupSno']];
                    $afterGroupSort = $this->groupSort[$groupSno];
                    //하향펑가를 설정하지 않았거나, 하향평가를 사용하거나, 하향평가를 사용하지 않고 변경 등급이 기존 등급보다 높거나 같을 때 만 업데이트
                    if (empty($this->policy['downwardAdjustment']) || $this->policy['downwardAdjustment'] == 'y' || ($this->policy['downwardAdjustment'] == 'n' && $beforeGroupSort < $afterGroupSort)) {
                        if ($this->isCurrentGroupMember || $this->isDefaultGroupMember) {
                            $logger->info('Group change target. sno[' . $this->group->get('sno', 0) . '], name[' . $this->group->get('', '-') . ']', $item);
                            $this->updateGroup($memNo);
                            $this->writeHistory($memNo);
                            $this->addMailReceiver($storage, $this->group);
                            $this->addSmsReceiver($storage, $this->group);
                            unset($members[$index]);
                        }
                    }
                }
            }
            if ($this->isTran) {
                $db->commit();
                $this->currentMembers = $members;
            }
        } catch (Exception $e) {
            $logger->error(__METHOD__ . ', ' . $e->getFile() . '[' . $e->getLine() . '], ' . $e->getMessage(), $e->getTrace());
            $db->rollback();
        }
    }

    /**
     * checkGroupByAppraisalSystem
     *
     * @param array $item 등급평가 대상의 기준치 정보
     *
     * @return void
     */
    public function checkGroupByAppraisalSystem(array $item)
    {
        if ($this->_isAppraisalSystemPoint()) {
            $this->checkGroupByPoint($item);
        } else {
            $this->checkGroupByFigure($item);
        }
    }

    /**
     * 실적점수제 평가
     *
     * @param array $item 등급평가 대상의 기준치 정보
     *
     * @return void
     */
    public function checkGroupByPoint($item)
    {
        $this->isCurrentGroupMember = true;

        $apprPointMore = $this->group->get('apprPointMore', 0);
        $apprPointBelow = $this->group->get('apprPointBelow', 0);
        $apprPointMoreMobile = $this->group->get('apprPointMoreMobile', 0);
        $apprPointBelowMobile = $this->group->get('apprPointBelowMobile', 0);

        $orderPriceUnit = $this->appraisalGroupConfig->get('apprPointOrderPriceUnit', 0);
        $orderPricePoint = $this->appraisalGroupConfig->get('apprPointOrderPricePoint', 0);
        $orderRepeatPoint = $this->appraisalGroupConfig->get('apprPointOrderRepeatPoint', 0);
        $reviewRepeatPoint = $this->appraisalGroupConfig->get('apprPointReviewRepeatPoint', 0);
        $loginRepeatPoint = $this->appraisalGroupConfig->get('apprPointLoginRepeatPoint', 0);

        $orderPriceUnitMobile = $this->appraisalGroupConfig->get('apprPointOrderPriceUnitMobile', 0);
        $orderPricePointMobile = $this->appraisalGroupConfig->get('apprPointOrderPricePointMobile', 0);
        $orderRepeatPointMobile = $this->appraisalGroupConfig->get('apprPointOrderRepeatPointMobile', 0);
        $reviewRepeatPointMobile = $this->appraisalGroupConfig->get('apprPointReviewRepeatPointMobile', 0);
        $loginRepeatPointMobile = $this->appraisalGroupConfig->get('apprPointLoginRepeatPointMobile', 0);

        $orderPrice = $item['orderPrice'];
        $orderCount = $item['orderCount'];
        $reviewCount = $item['reviewCount'];
        $loginCount = $item['loginCount'];

        $orderPriceMobile = $item['orderPriceMobile'];
        $orderCountMobile = $item['orderCountMobile'];
        $reviewCountMobile = $item['reviewCountMobile'];
        $loginCountMobile = $item['loginCountMobile'];

        $point = 0;
        $pointMobile = 0;

        if ($this->appraisalGroupConfig->get('appraisalPointOrderPriceFl', 'n') == 'y') {
            if ($orderPriceUnit > 0 && $orderPricePoint > 0) {
                $point += round(($orderPrice / $orderPriceUnit)) * $orderPricePoint;
            }
            if ($orderPriceUnitMobile > 0 && $orderPricePointMobile > 0) {
                $pointMobile += round(($orderPriceMobile / $orderPriceUnitMobile)) * $orderPricePointMobile;
            }
        }
        if ($this->appraisalGroupConfig->get('appraisalPointOrderRepeatFl', 'n') == 'y') {
            $point += ($orderCount * $orderRepeatPoint);
            if ($orderRepeatPointMobile > 0) {
                $pointMobile += ($orderCountMobile * $orderRepeatPointMobile);
            }
        }
        if ($this->appraisalGroupConfig->get('appraisalPointReviewRepeatFl', 'n') == 'y') {
            $point += ($reviewCount * $reviewRepeatPoint);
            if ($reviewRepeatPointMobile > 0) {
                $pointMobile += ($reviewCountMobile * $reviewRepeatPointMobile);
            }
        }
        if ($this->appraisalGroupConfig->get('appraisalPointLoginRepeatFl', 'n') == 'y') {
            $point += ($loginCount * $loginRepeatPoint);
            if ($loginRepeatPointMobile > 0) {
                $pointMobile += ($loginCountMobile * $loginRepeatPointMobile);
            }
        }
        if (!($apprPointMore <= $point && $point < $apprPointBelow)) {
            $this->isCurrentGroupMember = false;
        }
        if ($apprPointMoreMobile > 0 && $apprPointBelowMobile > 0) {
            if (!($apprPointMoreMobile <= $pointMobile && $pointMobile < $apprPointBelowMobile)) {
                $this->isCurrentGroupMember = false;
            }
        }
        if ($point > 0) {
            $logger = \App::getInstance('logger');
            $logger->info(
                __METHOD__, [
                    $point,
                    $pointMobile,
                ]
            );
        }
    }

    /**
     * 실적수치제 평가
     *
     * @param array $item 등급평가 대상의 기준치 정보
     *
     * @return void
     */
    public function checkGroupByFigure($item)
    {
        $logger = \App::getInstance('logger');
        $this->isCurrentGroupMember = true;
        $priceUnit = 10000;

        $orderPrice = $item['orderPrice'];
        $orderCount = $item['orderCount'];
        $reviewCount = $item['reviewCount'];

        $orderPriceMobile = $item['orderPriceMobile'];
        $orderCountMobile = $item['orderCountMobile'];
        $reviewCountMobile = $item['reviewCountMobile'];

        $apprFigureOrderPriceMore = $this->group->get('apprFigureOrderPriceMore', 0) * $priceUnit;
        $apprFigureOrderPriceBelow = $this->group->get('apprFigureOrderPriceBelow', 0) * $priceUnit;
        $apprFigureOrderRepeat = $this->group->get('apprFigureOrderRepeat', 0);
        $apprFigureReviewRepeat = $this->group->get('apprFigureReviewRepeat', 0);

        $apprFigureOrderPriceMoreMobile = $this->group->get('apprFigureOrderPriceMoreMobile', 0) * $priceUnit;
        $apprFigureOrderPriceBelowMobile = $this->group->get('apprFigureOrderPriceBelowMobile', 0) * $priceUnit;
        $apprFigureOrderRepeatMobile = $this->group->get('apprFigureOrderRepeatMobile', 0);
        $apprFigureReviewRepeatMobile = $this->group->get('apprFigureReviewRepeatMobile', 0);

        if ($this->group->get('apprFigureOrderPriceFl', 'n') == 'y') {
            if (!($apprFigureOrderPriceMore <= $orderPrice && $orderPrice < $apprFigureOrderPriceBelow)) {
                $this->isCurrentGroupMember = false;
            }
            if ($apprFigureOrderPriceMoreMobile > 0 && $apprFigureOrderPriceBelowMobile > 0) {
                if (!($apprFigureOrderPriceMoreMobile <= $orderPriceMobile && $orderPriceMobile < $apprFigureOrderPriceBelowMobile)) {
                    $this->isCurrentGroupMember = false;
                }
            }
        }

        if ($this->group->get('apprFigureOrderRepeatFl', 'n') == 'y') {
            if (!($apprFigureOrderRepeat <= $orderCount)) {
                $this->isCurrentGroupMember = false;
            }
            if (!($apprFigureOrderRepeatMobile <= $orderCountMobile)) {
                $this->isCurrentGroupMember = false;
            }
        }

        if ($this->group->get('apprFigureReviewRepeatFl', 'n') == 'y') {
            if (!($apprFigureReviewRepeat <= $reviewCount)) {
                $this->isCurrentGroupMember = false;
            }
            if (!($apprFigureReviewRepeatMobile <= $reviewCountMobile)) {
                $this->isCurrentGroupMember = false;
            }
        }
    }

    /**
     * 등급변경된 회원의 수정이력 추가
     *
     * @param SimpleStorage $storage 회원정보
     *
     * @return void
     */
    public function updateGroupWithInsertHistory(SimpleStorage $storage)
    {
        $memNo = $storage->get('memNo');
        $this->updateGroup($memNo);
        $this->writeHistory($memNo);
    }

    /**
     * 등급수정
     *
     * @param integer $memNo 회원번호
     *
     * @return void
     */
    public function updateGroup($memNo)
    {
        $beforeGroup = $this->currentMembers[$memNo]['groupSno'];
        $afterGroup = $this->group->get('sno');

        $applyCoupon = false;

        //등급 변경시에만 발급이라면 별도 루틴을 따를 것
        if($this->policy['couponConditionCompleteChange'] == 'y') {
            //수동이나 자동 발급일 경우
            if (!empty($beforeGroup)) {
                if ($this->policy['couponConditionComplete'] == 'y') {
                    //발급 설정이 되어 있는가?(Y)
                    if ($this->policy['couponConditionCompleteChange'] == 'y') {
                        //등급 변경 시에만 발급인가?(Y)
                        if ($beforeGroup != $afterGroup) {
                            //회원 등급이 변경 되었는가?
                            if (!empty($this->group->get('groupCoupon'))) {
                                //업데이트된 회원 등급에 쿠폰 혜택이 있는가?(Y)
                                $applyCoupon = true;
                            } else {
                                $applyCoupon = false;
                            }
                        } else {
                            $applyCoupon = false;
                        }
                    } else {
                        //등급 변경 시에만 발급인가?(N)
                        if (!empty($this->group->get('groupCoupon'))) {
                            //속해있는 회원등급에 쿠폰 혜택이 있는가?(Y)
                            $applyCoupon = true;
                        } else {
                            //속해있는 회원등급에 쿠폰 혜택이 있는가?(N)
                            $applyCoupon = false;
                        }
                    }
                } else {
                    //발급 설정이 되어 있는가?(N)
                    $applyCoupon = false;
                }
            }
        }

        $passwordCheckFl = gd_isset(\Request::post()->get('passwordCheckFl'), 'y');
        if($passwordCheckFl != 'n') {
            $smsSender = \App::load(\Component\Sms\SmsSender::class);
            $smsSender->validPassword(\Request::post()->get('password'));
        }
        $passwordCheckFl = $passwordCheckFl == 'n' ? false : true;
        //쿠폰 지급 하는 코드
        if($applyCoupon === true){
            $applyCouponList = explode(INT_DIVISION, $this->group->get('groupCoupon'));
            foreach($applyCouponList as $value){
                $coupon = new \Component\Coupon\CouponAdmin;
                \Request::post()->set('couponNo', $value);
                \Request::post()->set('couponSaveAdminId', '회원등급 쿠폰 혜택');
                \Request::post()->set('managerNo', Session::get('manager.sno'));
                \Request::post()->set('memberCouponStartDate', $coupon->getMemberCouponStartDate($value));
                \Request::post()->set('memberCouponEndDate', $coupon->getMemberCouponEndDate($value));
                \Request::post()->set('memberCouponState', 'y');

                $memberArr[] = $memNo;

                $coupon->saveMemberCouponSms($memberArr, null, $passwordCheckFl);
                unset($memberArr);
            }
        }

        $db = \App::getInstance('DB');
        $fields = DBTableField::getFieldTypes('tableMember');

        $arrUpdate[] = 'groupSno=?';
        $db->bind_param_push($arrBind, $fields['groupSno'], $this->group->get('sno'));
        $arrUpdate[] = 'groupModDt=?';
        $db->bind_param_push($arrBind, $fields['groupModDt'], $this->appraisalDateTime);

        $db->bind_param_push($arrBind, $fields['memNo'], $memNo);
        $db->bind_param_push($arrBind, $fields['appFl'], 'y');
        $db->bind_param_push($arrBind, $fields['sleepFl'], 'n');
        $db->set_update_db(DB_MEMBER, $arrUpdate, 'memNo = ? AND appFl = ? AND sleepFl = ?', $arrBind);
    }

    function applyCouponAllMember(){
        //전체 회원 정보 가져오기
        $member = \App::load('Component\Member\MemberAdmin');

        $arrWhere[] = 'appFl = \'y\'';
        $arrWhere[] = 'sleepFl != \'y\'';

        $field[] = 'memNo';
        $field[] = 'groupSno';

        $memberPageList = $member->getMemberList($arrWhere, null, null, null, null, $field);

        //그룹 설정 가져오기
        $group = \App::load('Component\Member\Group\GroupDAO');

        $passwordCheckFl = gd_isset(\Request::post()->get('passwordCheckFl'), 'y');
        if($passwordCheckFl != 'n') {
            $smsSender = \App::load(\Component\Sms\SmsSender::class);
            $smsSender->validPassword(\Request::post()->get('password'));
        }
        $passwordCheckFl = $passwordCheckFl == 'n' ? false : true;
        if (empty($memberPageList) === false && count($memberPageList) > 0) {
            $coupon = new \Component\Coupon\CouponAdmin;
            foreach ($memberPageList as $key => $value) {
                //그룹 정보 가져오기
                if (empty($groupList[$value['groupSno']])) {
                    $groupList[$value['groupSno']] = $group->selectGroup($value['groupSno'])['groupCoupon'];
                }

                //하나씩 쿠폰 발급 하기
                if (empty($groupList[$value['groupSno']]) === false && count($groupList[$value['groupSno']]) > 0) {
                    $applyCouponList = explode(INT_DIVISION, $groupList[$value['groupSno']]);
                }

                if (empty($applyCouponList) === false && count($applyCouponList) > 0) {
                    \Request::post()->set('couponSaveAdminId', '회원등급 쿠폰 혜택');
                    \Request::post()->set('memberCouponState', 'y');
                    \Request::post()->set('managerNo', Session::get('manager.sno'));
                    $memberArr[] = $value['memNo'];
                    foreach ($applyCouponList as $valueCoupon) {
                        if (is_numeric($valueCoupon) && $valueCoupon > 0) {
                            \Request::post()->set('couponNo', $valueCoupon);
                            \Request::post()->set('memberCouponStartDate', $coupon->getMemberCouponStartDate($valueCoupon));
                            \Request::post()->set('memberCouponEndDate', $coupon->getMemberCouponEndDate($valueCoupon));
                            $coupon->saveMemberCouponSms($memberArr, null, $passwordCheckFl);
                        }
                    }
                    unset($memberArr, $applyCouponList);
                }
            }
        }
    }

    /**
     * 수정이력 추가
     *
     * @param integer $memNo 회원번호
     *
     * @return void
     */
    public function writeHistory($memNo)
    {
        /** @var \Bundle\Component\Member\History $history */
        $history = App::load('\\Component\\Member\\History');
        $history->setMemNo($memNo);
        $history->setProcessor('admin');
        $history->setProcessorIp($this->remoteAddress);
        $history->initBeforeAndAfter();
        // @formatter:off
        $history->addFilter(['groupSno','groupModDt']);
        $history->addExclude(['smsFl', 'maillingFl']);
        // @formatter:on
        $history->writeHistory();
    }

    /**
     * 메일 발송 대상 배열에 등급변경된 회원 정보 추가
     *
     * @param SimpleStorage $storage 회원정보
     * @param SimpleStorage $group   등급정보
     *
     * @return void
     */
    public function addMailReceiver(SimpleStorage $storage, SimpleStorage $group)
    {
        if (($storage->get('beforeGroupSno') != $group->get('sno')) && $this->mailSender !== null) {
            $this->mailSender->addReceiver($storage->all(), $group->all());
        }
    }

    /**
     * SMS 발송 대상 배열에 등급변경된 회원 정보 추가
     *
     * @param SimpleStorage $storage 회원정보
     * @param SimpleStorage $group   등급정보
     *
     * @return void
     */
    public function addSmsReceiver(SimpleStorage $storage, SimpleStorage $group)
    {
        $logger = \App::getInstance('logger');
        if ($storage->get('cellPhone') != null && $storage->get('memNm') != null) {
            if ($storage->get('beforeGroupSno') != $group->get('sno')) {
                $smsAuto = \App::load(\Component\Sms\SmsAuto::class);
                $observer = new SmsAutoObserver();
                $observer->setSmsType(SmsAutoCode::MEMBER);
                $observer->setSmsAutoCodeType(Code::GROUP_CHANGE);
                $observer->setReceiver($storage->get('cellPhone'));
                $observer->setReplaceArguments(
                    [
                        'rc_groupNm' => $group->get('groupNm'),
                        'name'       => $storage->get('memNm'),
                        'memNm'      => $storage->get('memNm'),
                        'groupNm'    => $group->get('groupNm'),
                        'rc_mallNm'  => \Globals::get('gMall.mallNm'),
                        'shopUrl'    => $this->basicInfo['mallDomain'],
                    ]
                );
                $smsAuto->attach($observer);
            } else {
                $logger->info(sprintf('The membership level is the same. before[%d], now[%d]', $storage->get('beforeGroupSno'), $group->get('sno')));
            }
        } else {
            $logger->info(sprintf('Not have a cell phone number or member name. memNo[%d]', $storage->get('memNo', 0)));
        }
    }

    /**
     * isCurrentGroupMember
     *
     * @return bool 현재 평가중인 등급 기준치 만족 여부
     */
    public function isCurrentGroupMember()
    {
        return $this->isCurrentGroupMember;
    }

    /**
     * setGroup
     *
     * @param SimpleStorage $group 등급정보
     *
     * @return void
     */
    public function setGroup(SimpleStorage $group)
    {
        $this->group = $group;
    }

    /**
     * @param mixed $appraisalDateTime 등급평가일시
     *
     * @return void
     */
    public function setAppraisalDateTime($appraisalDateTime)
    {
        $this->appraisalDateTime = $appraisalDateTime;
    }

    /**
     * @param mixed $countByTotalMember 전체회원수
     *
     * @return void
     */
    public function setCountByTotalMember($countByTotalMember)
    {
        $this->countByTotalMember = $countByTotalMember;
    }

    /**
     * 기준(가입) 등급인지 확인하는 함수
     *
     * @param array $group
     *
     * @return bool
     */
    protected function isDefaultGroup(array $group)
    {
        $isDefaultSort = $group['groupSort'] == 1;
        $isDefaultFigureFl = ($group['apprFigureOrderPriceFl'] == 'n') && ($group['apprFigureOrderRepeatFl'] == 'n') && ($group['apprFigureReviewRepeatFl'] == 'n');
        $isDefaultFigureOrderPrice = ($group['apprFigureOrderPriceMore'] == 0) && ($group['apprFigureOrderPriceBelow'] == 0) && ($group['apprFigureOrderPriceMoreMobile'] == 0) && ($group['apprFigureOrderPriceBelowMobile'] == 0);
        $isDefaultFigureOrderRepeat = ($group['apprFigureOrderRepeat'] == 0) && ($group['apprFigureOrderRepeatMobile'] == 0);
        $isDefaultFigureReviewRepeat = ($group['apprFigureReviewRepeat'] == 0) && ($group['apprFigureReviewRepeatMobile'] == 0);
        $isDefaultPoint = ($group['apprPointMore'] == 0) && ($group['apprPointBelow'] == 0) && ($group['apprPointMoreMobile'] == 0) && ($group['apprPointBelowMobile'] == 0);

        return $isDefaultSort && $isDefaultFigureFl && $isDefaultFigureOrderPrice && $isDefaultFigureOrderRepeat && $isDefaultFigureReviewRepeat && $isDefaultPoint;
    }

    /**
     * countTotalMember
     *
     * @return mixed
     */
    protected function countTotalMember()
    {
        return $this->_countTotalMember();
    }

    /**
     * _countTotalMember
     *
     * @return mixed
     */
    private function _countTotalMember()
    {
        return $this->getCount(DB_MEMBER, '1', 'WHERE sleepFl=\'n\' AND appFl=\'y\'');
    }

    private function _isAppraisalSystemPoint()
    {
        return $this->appraisalGroupConfig->get('apprSystem') == 'point';
    }
}
