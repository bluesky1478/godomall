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

namespace Bundle\Controller\Front\Member\Payco;

use Component\Attendance\AttendanceCheckLogin;
use Component\Godo\GodoPaycoServerApi;
use Component\Member\Member;
use Component\Member\MemberSnsService;
use Component\Member\MyPage;
use Component\Member\Util\MemberUtil;
use Exception;
use Framework\Debug\Exception\AlertRedirectCloseException;
use Framework\Debug\Exception\AlertRedirectException;
use Framework\Debug\Exception\AlertCloseException;
use Framework\Utility\StringUtils;
use Message;
use Request;
use Session;

/**
 * 페이코 로그인 및 회원가입
 * @package Bundle\Controller\Front\Member\Payco
 * @author  yjwee
 */
class PaycoLoginController extends \Controller\Front\Controller
{
    /**
     * @inheritdoc
     *
     * @throws AlertCloseException
     */
    public function index()
    {
        $request = \App::getInstance('request');
        $session = \App::getInstance('session');
        $logger = \App::getInstance('logger');
        $logger->info(sprintf('start controller: %s', __METHOD__));

        try {
            $referer = $request->get()->get('referer', '');

            $paycoApi = new GodoPaycoServerApi();

            if ($paycoApi->hasError()) {
                throw new Exception($request->get()->get('error_description'), $request->get()->get('error'));
            }

            // 페이코 로그인, 회원가입, 연결/연결해제 응답 부 처리
            if ($paycoApi->isAuthorizationResponse()) {
                $paycoToken = $paycoApi->getToken($request->get()->get('code'));
                $session->set(GodoPaycoServerApi::SESSION_ACCESS_TOKEN, $paycoToken);

                // 응답이 정상일 경우의 처리
                if ($paycoApi->isSuccess($paycoToken['header'])) {
                    $logger->info('payco api success');
                    $memberSnsService = new MemberSnsService();
                    $memberSns = $memberSnsService->getMemberSnsByUUID($paycoToken['idNo']);

                    // SNS 회원 검증
                    if ($memberSnsService->validateMemberSns($memberSns)) {
                        $logger->info('validateMemberSns pass');
                        if ($session->has(SESSION_GLOBAL_MALL)) {
                            $mallBySession = $session->get(SESSION_GLOBAL_MALL);
                            $logger->info(sprintf('has session %s', \Component\Member\Member::SESSION_MEMBER_LOGIN));
                            if ($memberSns['mallSno'] != $mallBySession['sno']) {
                                $logger->info(sprintf('member join mall number[%s], mall session sno[%d]', $memberSns['mallSno'], $mallBySession['sno']));
                                $js = "
                                    alert('" . __('회원을 찾을 수 없습니다.') . "');
                                    if (typeof(window.top.layerSearchArea) == 'object') {
                                        parent.location.href='../../main/index.php';
                                    } else if (window.opener === null) {
                                        location.href='" . gd_isset($request->get()->get('returnUrl'), '../../main/index.php') . "';
                                    } else {
                                        opener.location.href='../../main/index.php';
                                        self.close();
                                    }
                                ";
                                $this->js($js);
                            }
                        }
                        if ($session->has(Member::SESSION_MEMBER_LOGIN)) {
                            if ($memberSns['memNo'] != $session->get(Member::SESSION_MEMBER_LOGIN . '.memNo', 0)) {
                                $logger->info('not eq memNo');
                                $js = "
                                    alert('" . __('로그인 시 인증한 정보와 다릅니다 .') . "');
                                    if (typeof(window.top.layerSearchArea) == 'object') {
                                        parent.location.href='../../mypage/my_page_password.php';
                                    } else if (window.opener === null) {
                                        location.href='" . gd_isset($request->get()->get('returnUrl'), '../../mypage/my_page_password.php') . "';
                                    } else {
                                        opener.location.href='../../mypage/my_page_password.php';
                                        self.close();
                                    }
                                ";
                                $this->js($js);
                            }
                            if (StringUtils::contains($referer, 'my_page_password') || $request->get()->get('paycoType', '') == 'my_page_password') {
                                $logger->info('move my page');
                                $session->set(MyPage::SESSION_MY_PAGE_PASSWORD, true);
                                $js = "
                                    if (typeof(window.top.layerSearchArea) == \"object\") {
                                        parent.location.href='../../mypage/my_page.php';
                                    } else if (window.opener === null) {
                                        location.href='" . gd_isset($request->get()->get('returnUrl'), '../../mypage/my_page.php') . "';
                                    } else {
                                        opener.location.href='../../mypage/my_page.php';
                                        self.close();
                                    }
                                ";
                                $this->js($js);
                            }
                            $logger->info('move main or reload');
                            $js = '';
                            $js .= "
                                if (typeof(window.top.layerSearchArea) == 'object') {
                                    parent.location.reload();
                                } else if (window.opener === null) {
                                    location.href='" . gd_isset($request->get()->get('returnUrl'), '../../main/index.php') . "';
                                } else {
                                    opener.location.href='../../main/index.php';self.close();
                                }
                            ";
                            $this->js($js);
                        }
                        if (isset($memberSns['accessToken'])) {
                            $logger->info('isset accessToken');
                            $paycoApi->serviceOff($memberSns['accessToken']);
                            $logger->info('success serviceOff');
                        }
                        $memberSnsService->saveToken($paycoToken['idNo'], $paycoToken['access_token'], $paycoToken['refresh_token']);
                        $logger->info('success save Token');
                        $memberSnsService->loginBySns($paycoToken['idNo']);
                        $logger->info('success login by sns');
                        $paycoApi->logByLogin();
                        $logger->info('success send payco api login log');

                        $db = \App::getInstance('DB');
                        try {
                            $db->begin_tran();
                            $check = new AttendanceCheckLogin();
                            $message = $check->attendanceLogin();
                            $db->commit();

                            // 에이스 카운터 로그인 스크립트
                            $acecounterScript = \App::load('\\Component\\Nhn\\AcecounterCommonScript');
                            $acecounterUse = $acecounterScript->getAcecounterUseCheck();
                            if($acecounterUse) {
                                echo $acecounterScript->getLoginScript();
                            }

                            $logger->info('commit attendance login');
                            if ($message) {
                                $logger->info(sprintf('has attendance message: %s', $message));
                                $js = "
                                    alert('" . $message . "');
                                    if (typeof(window.top.layerSearchArea) == 'object') {
                                        parent.location.href='" . MemberUtil::getLoginReturnURL() . "';
                                    } else if (window.opener === null) {
                                        location.href='" . MemberUtil::getLoginReturnURL() . "';
                                    } else {
                                        opener.location.href='" . MemberUtil::getLoginReturnURL() . "';
                                        self.close();
                                    }
                                ";
                                $this->js($js);
                            }
                        } catch (Exception $e) {
                            $db->rollback();
                            $logger->error(__METHOD__ . ', ' . $e->getFile() . '[' . $e->getLine() . '], ' . $e->getMessage());
                        }

                        if (StringUtils::contains($referer, 'join_method') || $request->get()->get('paycoType', '') == 'join_method') {
                            $logger->info('join member');
                            $js = "
                                alert('" . __('이미 가입한 회원입니다.') . "');
                                if (typeof(window.top.layerSearchArea) == 'object') {
                                    parent.location.href='" . MemberUtil::getLoginReturnURL() . "';
                                } else if (window.opener === null) {
                                    location.href='" . MemberUtil::getLoginReturnURL() . "';
                                } else {
                                    opener.location.href='" . MemberUtil::getLoginReturnURL() . "';
                                    self.close();
                                }
                            ";
                            $this->js($js);
                        }
                        $logger->info('move return url');
                        $js = "
                            if (typeof(window.top.layerSearchArea) == 'object') {
                                parent.location.href='" . MemberUtil::getLoginReturnURL() . "';
                            } else if (window.opener === null) {
                                location.href='" . MemberUtil::getLoginReturnURL() . "';
                            } else {
                                opener.location.href='" . MemberUtil::getLoginReturnURL() . "';
                                self.close();
                            }
                        ";
                        $this->js($js);
                    }
                    // 성인인증 로그인 시도 회원정보 없음
                    if (StringUtils::contains($referer, 'intro/adult')) {
                        $paycoApi->removeServiceOff($paycoToken['access_token']);
                        $js = "
                            if (typeof(window.top.layerSearchArea) == 'object') {
                                if (confirm('" . __('가입되지 않은 회원정보입니다. 회원가입을 진행하시겠습니까?') . "')) {
                                    parent.location.href = '../../member/join_agreement.php';
                                } else {
                                    parent.location.reload();
                                }
                            } else {
                                if(confirm('" . __('가입되지 않은 회원정보입니다. 회원가입을 진행하시겠습니까?') . "')) {
                                    window.opener.location.href='../../member/join_agreement.php';
                                }
                                self.close();
                            }
                        ";
                        $this->js($js);
                    }
                    // 회원가입에서의 로그인 약관 동의 화면 이동
                    if (StringUtils::contains($referer, 'member/join_method')) {
                        $js = "
                            if (typeof(window.top.layerSearchArea) == 'object') {
                                parent.location.href='../join_agreement.php';
                            } else if (window.opener === null) {
                                location.href='../join_agreement.php';
                            } else {
                                opener.location.href='../join_agreement.php';self.close();
                            }
                        ";
                        $this->js($js);
                    }
                    // 내정보수정에서의 로그인 sns 인증 계정 다를 경우
                    if (StringUtils::contains($referer, 'mypage/my_page_password')) {
                        $js = "
                            alert('" . __('가입된 계정이 아닙니다. 가입하신 계정으로 재인증 진행해주세요.') . "');
                            if (typeof(window.top.layerSearchArea) == 'object') {
                                parent.location.reload();
                            } else {
                                self.close();
                            }
                        ";
                        $this->js($js);
                    }
                    // 회원가입을 하지 않은 경우
                    $js = "
                        if (typeof(window.top.layerSearchArea) == 'object') {
                            if (confirm('" . __('가입되지 않은 회원정보입니다. 회원가입을 진행하시겠습니까?') . "')) {
                                parent.location.href = '../join_agreement.php';
                            } else {
                                parent.location.reload();
                            }
                        } else if (window.opener === null) {
                            if (confirm('" . __('가입되지 않은 회원정보입니다. 회원가입을 진행하시겠습니까?') . "')) {
                                location.href = '../join_agreement.php';
                            } else {
                                location.href='" . gd_isset($request->get()->get('returnUrl'), '../../main/index.php') . "';
                            }
                        } else {
                            if(confirm('" . __('가입되지 않은 회원정보입니다. 회원가입을 진행하시겠습니까?') . "')) {
                                window.opener.location.href='../join_agreement.php';
                            }
                            self.close();
                        }
                    ";
                    $this->js($js);
                }
                // 응답 실패인 경우
                $paycoApi->removeServiceOff($paycoToken['access_token']);
                $js = "
                    if (typeof(window.top.layerSearchArea) == 'object') {
                        parent.location.href='../join_method.php';
                    } else if (window.opener === null) {
                        location.href='../join_method.php';
                    } else {
                        opener.location.href='../join_method.php';self.close();
                    }
                ";
                $this->js($js);
            }

            // 페이코 로그인 팝업을 띄우는 케이스
            $returnURL = $request->getDomainUrl() . $request->getRequestUri() . (strpos($request->getRequestUri(), '?') === false ? '?' : '&') . 'referer=' . $request->getReferer();
            if (strpos($returnURL, 'returnUrl') === false) {
                $returnURL .= '&returnUrl=' . MemberUtil::getLoginReturnURL();
            }
            $logger->info(sprintf('PaycoLogin Return Login. url is %s', $returnURL));
            if ($request->get()->get('paycoType', '') != '') {
                $returnURL .= '&paycoType=' . $request->get()->get('paycoType');
            }
            $loginURL = $paycoApi->getLoginURL($returnURL);
            $logger->info(sprintf('Redirect payco login. url is %s', $loginURL));
            $this->redirect($loginURL);
        } catch (AlertRedirectException $e) {
            $logger->error($e->getTraceAsString());
            MemberUtil::logout();
            throw $e;
        } catch (AlertRedirectCloseException $e) {
            $logger->error($e->getTraceAsString());
            throw $e;
        } catch (Exception $e) {
            $logger->error($e->getTraceAsString());
            if ($request->isMobile()) {
                MemberUtil::logout();
                throw new AlertRedirectException($e->getMessage(), $e->getCode(), $e, '../../member/login.php', 'parent');
            } else {
                MemberUtil::logout();
                throw new AlertCloseException($e->getMessage(), $e->getCode(), $e);
                /*
                    if ($e->getTarget() == 'opener') {
                        throw $e;
                    } else {
                        MemberUtil::logout();
                        throw new AlertCloseException($e->getMessage(), $e->getCode(), $e);
                    }
                    */
            }
        }
        $logger->info(sprintf('end controller: %s', __METHOD__));
    }
}
