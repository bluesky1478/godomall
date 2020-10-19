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

namespace Bundle\Controller\Admin\Member;

use Component\Member\HackOut\HackOutService;
use Component\Member\Manager;
use Component\Member\MemberVO;
use Component\Member\Util\MemberUtil;
use Component\Policy\JoinItemPolicy;
use Component\Policy\MileagePolicy;
use Component\Storage\Storage;
use Exception;
use Framework\Debug\Exception\AlertOnlyException;
use Framework\Debug\Exception\LayerException;

/**
 * Class 회원 처리
 * @package Bundle\Controller\Admin\Member
 * @author  yjwee
 */
class MemberPsController extends \Controller\Admin\Controller
{
    /**
     * @inheritdoc
     */

    const ERR_CODE_MEMBER_AUTH = 901,
        ERR_CODE_REQUIRE_BIRTHDAY = 902,
        ERR_CODE_JOIN_EVENT = 903,
        ERR_CODE_MEMBER_AUTH_BIRTHDAY = 904;


    public function index()
    {
        $request = \App::getInstance('request');

        /**
         * @var \Bundle\Component\Member\Member      $memberService
         * @var \Bundle\Component\Member\MemberAdmin $memberAdminService
         * @var \Bundle\Component\Policy\Policy      $policy
         * @var \Bundle\Component\Admin\AdminLogDAO  $logAction
         */
        $memberService = \App::load('\\Component\\Member\\Member');
        $memberAdminService = \App::load('\\Component\\Member\\MemberAdmin');
        $policy = \App::load('\\Component\\Policy\\Policy');
        $logAction = \App::load('Component\\Admin\\AdminLogDAO');

        $requestPostParams = $request->post()->xss()->all();
        $requestAllParams = array_merge($request->get()->toArray(), $request->post()->toArray());

        try {
            if(($request->getReferer() == $request->getDomainUrl()) || empty($request->getReferer()) === true){
                \Logger::error(__METHOD__ .' Access without referer');
                throw new Exception(__("요청을 찾을 수 없습니다."));
            }

            switch ($requestAllParams['mode']) {
                case 'register':
                    $memberService->register(new MemberVO($requestPostParams));
                    $logAction->setAdminLog();
                    $this->json(__("회원등록을 성공하였습니다."));
                    break;
                case 'modify':
                    $memberAdminService->modifyMemberData($requestAllParams);
                    $logAction->setAdminLog();
                    $this->json(__('저장이 완료되었습니다.'));
                    break;
                case 'delete':
                    $session = \App::getInstance('session');
                    try {
                        $hackOutService = new HackOutService();
                        $managerSession = $session->get(Manager::SESSION_MANAGER_LOGIN);
                        \DB::begin_tran();
                        $hackOutService->setManagerNo($managerSession['sno']);
                        $hackOutService->setManagerId($managerSession['managerId']);
                        $hackOutService->setManagerIp($request->getRemoteAddress());
                        $hackOutService->hackOutByMemberList($request->post()->get('chk'));
                        \DB::commit();
                    } catch (Exception $e) {
                        \DB::rollback();
                        throw $e;
                    }
                    $logAction->setAdminLog();
                    $this->json(__('선택한 회원의 탈퇴처리가 완료되었습니다.'));
                    break;
                case 'overlapMemId':
                    if (MemberUtil::overlapMemId($request->post()->get('memId')) === false) {
                        $this->json(__("사용가능한 아이디입니다."));
                    } else {
                        throw new Exception(__("이미 등록된 아이디입니다.") . " " . __("다른 아이디를 입력해 주세요."));
                    }
                    break;
                case 'overlapNickNm':
                    if (MemberUtil::overlapNickNm($request->post()->get('memId'), $request->post()->get('nickNm')) === false) {
                        $this->json(__('사용가능한 닉네임입니다.'));
                    } else {
                        throw new Exception(__("이미 등록된 닉네임입니다."));
                    }
                    break;
                case 'overlapEmail':
                    if (\App::load('Component\\Member\\Util\\MemberUtil')->simpleOverlapEmail($request->post()->get('emailAddress') . '@' . $request->post()->get('emailDomain'), $requestAllParams['memId']) === false) {
                        $this->json(__('사용가능한 이메일입니다.'));
                    } else {
                        throw new Exception(__("이미 등록된 이메일입니다."));
                    }
                    break;
                case 'overlapBusiNo':
                    $memId = $request->post()->get('memId');
                    $busiNo = $request->post()->get('busiNo');
                    $busiLength = $request->post()->get('charlen');

                    if (strlen($busiNo) != $busiLength) {
                        throw new Exception(sprintf(__("사업자번호는 %s자로 입력해야 합니다."), $busiLength));
                    }

                    if (\App::load('Component\\Member\\Util\\MemberUtil')->simpleOverlapBusiNo($busiNo, $requestAllParams['memId']) === false) {
                        $this->json(__('사용가능한 사업자번호입니다.'));
                    } else {
                        throw new Exception(__("이미 등록된 사업자번호입니다. 중복되는 회원이 있는지 확인해주세요."));
                    }
                    break;
                case 'checkRecommendId':
                    // 추천인 아이디 체크
                    if (MemberUtil::checkRecommendId($request->post()->get('recommId'), $request->post()->get('memId'))) {
                        $this->json(__('아이디가 정상적으로 확인되었습니다.'));
                    } else {
                        throw new Exception(__('등록된 회원 아이디가 아닙니다. 추천하실 아이디를 다시 확인해주세요.'));
                    }

                    break;
                case 'history':
                    // 개인정보 변경 이력 불러오기
                    /** @var \Bundle\Component\Member\History $history */
                    $history = \App::load('\\Component\\Member\\History');
                    $memberHistory = $history->getMemberHistory($request->post()->get('memNo', null), $request->post()->get('page', 0), $request->post()->get('pageNum', 10));
                    $this->json($memberHistory);
                    break;
                case 'mailingAgreeCount':
                    $groupSno = $request->post()->get('groupSno', '');
                    $result = $memberAdminService->mailingAgreeCount($groupSno);
                    $this->json($result);
                    break;

                // --- 회원 가입 항목 설정
                case 'member_joinitem':
                    try {
                        $joinItemPolicy = new JoinItemPolicy();
                        $joinItemPolicy->saveMemberJoinItem($requestPostParams);
                        $this->layer();
                    } catch (Exception $e) {
                        if ($request->isAjax()) {
                            throw $e;
                        } else {
                            throw new LayerException($e->getMessage(), $e->getCode(), $e);
                        }
                    }
                    break;

                // --- 회원가입 정책관리
                case 'member_join':
                    try {
                        if ($policy->saveMemberJoin($request->post()->all())) {
                            $this->json(__('저장이 완료되었습니다.'));
                        } else {
                            throw new Exception(__('처리중에 오류가 발생하여 실패되었습니다.'));
                        }
                    } catch (Exception $e) {
                        if ($request->isAjax()) {
                            throw $e;
                        } else {
                            throw new LayerException($e->getMessage(), $e->getCode(), $e);
                        }
                    }
                    break;

                // --- 휴면 회원 설정
                case 'member_sleep':
                    try {
                        $policy->saveMemberSleep($requestPostParams);
                        $this->layer(__('저장이 완료되었습니다.'));
                    } catch (Exception $e) {
                        if ($request->isAjax()) {
                            throw $e;
                        } else {
                            throw new LayerException($e->getMessage(), $e->getCode(), $e);
                        }
                    }
                    break;

                // --- 회원의 마일리지 기본 설정
                case 'mileage_basic':
                    try {
                        $mileagePolicy = new MileagePolicy();
                        $mileagePolicy->saveMileageBasic($requestPostParams);
                        $this->layer(__('저장이 완료되었습니다.'));
                    } catch (Exception $e) {
                        throw new AlertOnlyException($e->getMessage(), $e->getCode(), $e);
                    }
                    break;

                // --- 회원의 마일리지 지급 설정
                case 'mileage_give':
                    try {
                        $mileagePolicy = new MileagePolicy();
                        $mileagePolicy->saveMileageGive($requestPostParams);
                        $this->layer(__('저장이 완료되었습니다.'));
                    } catch (Exception $e) {
                        $item = ($e->getMessage() ? ' - ' . str_replace("\n", ' - ', $e->getMessage()) : '');
                        throw new LayerException(__('처리중에 오류가 발생하여 실패되었습니다.') . $item);
                    }
                    break;

                // --- 회원의 마일리지 사용 설정
                case 'mileage_use':
                    try {
                        /** @var \Bundle\Component\Policy\MileagePolicy $policy */
                        $mileagePolicy = new MileagePolicy();
                        $mileagePolicy->saveMileageUse($requestPostParams);
                        $this->layer(__('저장이 완료되었습니다.'));
                    } catch (Exception $e) {
                        $item = ($e->getMessage() ? ' - ' . str_replace("\n", ' - ', $e->getMessage()) : '');
                        throw new LayerException(__('처리중에 오류가 발생하여 실패되었습니다.') . $item);
                    }
                    break;

                // --- 회원의 예치금 사용 설정
                case 'deposit_config':
                    try {
                        $policy->saveDepositConfig($requestPostParams);

                        $this->layer(__('저장이 완료되었습니다.'));
                    } catch (Exception $e) {
                        $item = ($e->getMessage() ? ' - ' . str_replace("\n", ' - ', $e->getMessage()) : '');
                        throw new LayerException(__('처리중에 오류가 발생하여 실패되었습니다.') . $item);
                    }
                    break;
                case 'getJoinPolicy':
                    $this->json(gd_policy('member.joinitem'));
                    break;
                // 14세이하 회원가입 동의서 다운로드
                case 'under14Download':
                    $downloadPath = Storage::disk(Storage::PATH_CODE_COMMON)->getDownLoadPath('under14sample.docx');
                    $this->download($downloadPath, __('만14세미만회원가입동의서(샘플).docx'));
                    break;
                case 'setJoinEventConfig':
                    if ($policy->saveMemberJoinEvent($request->post()->all(),$request->files()->all())) {
                        $this->layer(__('저장이 완료되었습니다.'));
                    } else {
                        throw new Exception(__('처리중에 오류가 발생하여 실패되었습니다.'), 903);
                    }
                    break;
                case 'checkSendSms':
                    $checkType = $requestAllParams['checkType'];
                    $smsPolicy = gd_policy('sms.smsAuto');
                    $depositPolicy = gd_policy('member.depositConfig');
                    $mileagePolicy = gd_policy('member.mileageGive');
                    $kakaoPolicy = gd_policy('kakaoAlrim.config');
                    $kakaoAutoPolicy = gd_policy('kakaoAlrim.kakaoAuto');
                    if($checkType == 'join') {
                        if($smsPolicy['member']['JOIN']['memberSend'] == 'y') {
                            $this->json(true);
                        }
                        if($smsPolicy['member']['MILEAGE_PLUS']['memberSend'] == 'y' && $depositPolicy['payUsableFl'] == 'y' && $mileagePolicy['giveFl'] == 'y' && $mileagePolicy['joinAmount'] > 0) {
                            $this->json(true);
                        }
                        $couponAdmin = \App::load('\\Component\\Coupon\\CouponAdmin');
                        $couponList = $couponAdmin->getJoinEventCouponList(false);
                        if($smsPolicy['promotion']['COUPON_JOIN']['memberSend'] == 'y' && count($couponList) > 0){
                            $this->json(true);
                        }
                        if($kakaoPolicy['useFlag'] == 'y' && $kakaoAutoPolicy['member']['JOIN']['memberSend']) {
                            $this->json(true);
                        }
                    } else if($checkType == 'group') {
                        if($smsPolicy['member']['GROUP_CHANGE']['memberSend'] == 'y') {
                            $this->json(true);
                        }
                        $couponAdmin = \App::load('\\Component\\Coupon\\CouponAdmin');
                        $couponList = $couponAdmin->getGroupCouponForCouponTypeY($requestAllParams['groupSno']);
                        if($smsPolicy['promotion']['COUPON_MANUAL']['memberSend'] == 'y' && $couponList) {
                            $this->json(true);
                        }
                        if($kakaoPolicy['useFlag'] == 'y' && $kakaoAutoPolicy['member']['GROUP_CHANGE']['memberSend']) {
                            $this->json(true);
                        }
                    }
                    break;
                default:
                    throw new Exception(__('요청을 처리할 페이지를 찾을 수 없습니다.'), 404);
                    break;
            }
        } catch (Exception $e) {
            if ($request->isAjax() === true) {
                switch ($e->getCode()) {
                    case self::ERR_CODE_REQUIRE_BIRTHDAY :
                        $arrayErr = array_merge($this->exceptionToArray($e), ['isReload' => true, 'isClose' => false, 'title' => __('경고')]);
                        $this->json($arrayErr);
                        break;
                    default :
                        $this->json($this->exceptionToArray($e));
                        break;
                }
            } else {
                switch ($e->getCode()) {
                    case self::ERR_CODE_MEMBER_AUTH :
                        $this->js('parent.dialog_alert("' . addslashes(__($e->getMessage())) . '","' . __('경고') . '" ,{isReload:true});');
                        break;
                    case self::ERR_CODE_JOIN_EVENT :
                        throw new AlertOnlyException($e->getMessage());
                        break;
                    case self::ERR_CODE_MEMBER_AUTH_BIRTHDAY:
                        $this->js('parent.dialog_alert("' . addslashes(__($e->getMessage())) . '","' . __('경고') . '" ,{isReload:false});');
                        break;
                    default :
                        throw new LayerException($e->getMessage(), $e->getCode(), $e);
                        break;
                }
            }
        }
    }
}
