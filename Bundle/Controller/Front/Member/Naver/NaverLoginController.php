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

namespace Bundle\Controller\Front\Member\Naver;

use Component\Attendance\AttendanceCheckLogin;
use Component\Godo\GodoNaverServerApi;
use Component\Member\Member;
use Component\Member\MemberSnsService;
use Component\Member\MyPage;
use Component\Member\Util\MemberUtil;
use Exception;
use Framework\Debug\Exception\AlertCloseException;
use Framework\Debug\Exception\AlertRedirectException;
use Framework\Debug\Exception\AlertRedirectCloseException;
use Framework\Utility\StringUtils;
use Message;
use Request;
use Session;

/**
 * 네이버 아이디 로그인 컨트롤러
 * @package Bundle\Controller\Front\Member\Naver
 */
class NaverLoginController extends \Controller\Front\Controller
{
    public function index()
    {
        $naverApi = new GodoNaverServerApi();

        $request = \App::getInstance('request');
        $session = \App::getInstance('session');
        $logger = \App::getInstance('logger');

        $logger->info(sprintf('start controller: %s', __METHOD__));
        try {
            $functionName = 'popup';
            if(gd_is_skin_division()) {
                $functionName = 'gd_popup';
            }

            if ($request->isMyapp() && $request->get('saveAutoLogin')) {
                $tempArray = explode('?', $request->get()->get('saveAutoLogin'));
                $request->get()->set('saveAutoLogin', $tempArray[0]);
                $tempArray = explode('=', $tempArray[1]);
                $request->get()->set($tempArray[0], $tempArray[1]);
            }

            switch ($request->get()->get('mode', 'login')) {
                case 'login':
                    $referer = $request->get()->get('referer', '');
                    if (empty($referer) === true) {
                        $returlUrl = $request->get()->get('returnUrl', '');
                        $param = explode('?', $returlUrl);
                        parse_str($param[1], $output);
                        $referer = $output['referer'];
                    }

                    if($naverApi->hasError()) {
                        throw new Exception($request->get()->get('error_description'));
                    }

                    if($naverApi->isAuthorizationResponse()) {
                        $naverToken = $naverApi->getToken($request->get()->get('code'));
                        $session->set(GodoNaverServerApi::SESSION_ACCESS_TOKEN, $naverToken);

                        if ($naverApi->isSuccess($naverToken)) {
                            $logger->info('naver api success');
                            $memberSnsService = new MemberSnsService();
                            $memberSns = $memberSnsService->getMemberSnsByUUID($naverToken['response']['id']);

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
                                            if (window.opener === null || Object.keys(window.opener).indexOf('" . $functionName . "') < 0) {
                                                location.href='../../main/index.php';
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
                                            if (window.opener === null || Object.keys(window.opener).indexOf('" . $functionName . "') < 0) {
                                                location.href='../../mypage/my_page_password.php';
                                            } else {
                                                opener.location.href='../../mypage/my_page_password.php';
                                                self.close();
                                            }
                                        ";
                                        $this->js($js);
                                    }
                                    if (StringUtils::contains($referer, 'my_page_password') || in_array($request->get()->get('naverType', ''), ['my_page_password', 'hack_out']) === true) {
                                        if ($request->get()->get('naverType', '') == 'my_page_password') {
                                            $logger->info('move my page');
                                            $session->set(MyPage::SESSION_MY_PAGE_PASSWORD, true);
                                            $js = "
                                                if (window.opener === null || Object.keys(window.opener).indexOf('" . $functionName . "') < 0) {
                                                    location.href='../../mypage/my_page.php';
                                                } else {
                                                    opener.location.href='../../mypage/my_page.php';
                                                    self.close();
                                                }
                                            ";
                                        } elseif ($request->get()->get('naverType', '') == 'hack_out') {
                                            $logger->info('move hack out');
                                            $session->set(GodoNaverServerApi::SESSION_NAVER_HACK, true);
                                            $js = "
                                                if (window.opener === null || Object.keys(window.opener).indexOf('" . $functionName . "') < 0) {
                                                    location.href='../../mypage/hack_out.php';
                                                } else {
                                                    opener.location.href='../../mypage/hack_out.php';
                                                    self.close();
                                                }
                                            ";
                                        }
                                        $this->js($js);
                                    }
                                    $logger->info('move main or reload');
                                    $js = "
                                        alert('" . __('회원으로 로그인 된 상태입니다.') . "');
                                        if (window.opener === null || Object.keys(window.opener).indexOf('" . $functionName . "') < 0) {
                                            location.href='../../main/index.php';
                                        } else {
                                            opener.location.href='../../main/index.php';
                                            self.close();
                                        }
                                    ";
                                    $this->js($js);
                                }
                                $memberSnsService->saveToken($naverToken['response']['id'], $naverToken['access_token'], $naverToken['refresh_token']);
                                $logger->info('success save Token');
                                $memberSnsService->loginBySns($naverToken['response']['id']);
                                $logger->info('success login by sns');
                                $naverApi->logByLogin();
                                $logger->info('success send naver api loin log');

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
                                            if (window.opener === null || Object.keys(window.opener).indexOf('" . $functionName . "') < 0) {
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

                                if (StringUtils::contains($referer, 'join_method') || $request->get()->get('naverType', '') == 'join_method') {
                                    $logger->info('join member');
                                    $js = "
                                        alert('" . __('이미 가입한 회원입니다.') . "');
                                        if (window.opener === null || Object.keys(window.opener).indexOf('" . $functionName . "') < 0) {
                                            location.href='../../member/login.php';
                                        } else {
                                            opener.location.href='../../member/login.php';
                                            self.close();
                                        }
                                    ";
                                    $this->js($js);
                                }
                                $logger->info('move return url');

                                // $returlUrl 이 있을경우 $returlUrl을 사용하도록 수정 MemberUtil::getLoginReturnURL() 의 경우 메인으로 리턴함
                                if ($returlUrl) {
                                    $loginReturnURL = $returlUrl;
                                } else {
                                    $loginReturnURL = MemberUtil::getLoginReturnURL();
                                }

                                $js = "
                                    if (typeof(window.top.layerSearchArea) == 'object') {
                                        parent.location.href='" . $loginReturnURL . "';
                                    } else if (window.opener === null) {
                                        location.href='" . $loginReturnURL . "';
                                    } else {
                                        opener.location.href='" . $loginReturnURL . "';
                                        self.close();
                                    }
                                ";
                                $this->js($js);
                            }
                            // 성인인증 로그인 시도 회원정보 없음
                            if (StringUtils::contains($referer, 'intro/adult')) {
                                $js = "
                                    if (window.opener === null || Object.keys(window.opener).indexOf('" . $functionName . "') < 0) {
                                        if (confirm('" . __('가입되지 않은 회원정보입니다. 회원가입을 진행하시겠습니까?') . "')) {
                                            location.href = '../join_agreement.php';
                                        } else {
                                            location.href='/intro/adult.php';
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
                            // 회원가입에서의 로그인 약관 동의 화면 이동
                            if (StringUtils::contains($referer, 'member/join_method') || $request->get()->get('naverType', '') == 'join_method') {
                                $js = "
                                    if (window.opener === null || Object.keys(window.opener).indexOf('" . $functionName . "') < 0) {
                                        location.href='../join_agreement.php';
                                    } else {
                                        opener.location.href='../join_agreement.php';
                                        self.close();
                                    }
                                ";
                                $this->js($js);
                            }
                            // 내정보수정에서의 로그인 sns 인증 계정 다를 경우
                            if (StringUtils::contains($referer, 'mypage/my_page_password')) {
                                $js = "
                                    alert('" . __('가입된 계정이 아닙니다. 가입하신 계정으로 재인증 진행해주세요.') . "');
                                    if (window.opener === null || Object.keys(window.opener).indexOf('" . $functionName . "') < 0) {
                                        location.href='" . $request->getReferer() . "';
                                    } else {
                                        self.close();
                                    }
                                ";
                                $this->js($js);
                            }
                            // 회원가입을 하지 않은 경우
                            $js = "
                                if (window.opener === null || Object.keys(window.opener).indexOf('" . $functionName . "') < 0) {
                                    if (confirm('" . __('가입되지 않은 회원정보입니다. 회원가입을 진행하시겠습니까?') . "')) {
                                        location.href = '../join_agreement.php';
                                    } else {
                                        location.href = '../login.php';
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
                        $js = "
                            if (window.opener === null || Object.keys(window.opener).indexOf('" . $functionName . "') < 0) {
                                location.href='../join_method.php';
                            } else {
                                opener.location.href='../join_method.php';
                                self.close();
                            }
                        ";
                        $this->js($js);
                    }

                    $parseUrl = parse_url($request->getRequestUri(),PHP_URL_QUERY);
                    parse_str($parseUrl,$parseReturlUrlQuery);
                    if(strpos($parseReturlUrlQuery['returnUrl'], '?')!==false){
                        $returnURL = $request->getDomainUrl() . $request->getRequestUri() . '&referer=' . $request->getReferer();
                    }
                    else {
                        $returnURL = $request->getDomainUrl() . $request->getRequestUri() . '?referer=' . $request->getReferer();
                    }

                    if (strpos($returnURL, 'returnUrl') === false) {
                        $returnURL .= '&returnUrl=' . MemberUtil::getLoginReturnURL();
                    }
                    $logger->info(sprintf('NaverLogin Return Login. url is %s', $returnURL));
                    $loginURL = $naverApi->getLoginURL($returnURL);

                    $logger->info(sprintf('Redirect naver login. url is %s', $loginURL));
                    $this->redirect($loginURL);
                    break;
                case 'connect':
                    if($naverApi->hasError()) {
                        throw new Exception($request->get()->get('error_description'));
                    }
                    if (Request::get()->has('code') && Request::get()->has('state')) {
                        $NaverToken = $naverApi->getToken(Request::get()->get('code'));
                        Session::set(GodoNaverServerApi::SESSION_ACCESS_TOKEN, $NaverToken);
                        if ($naverApi->isSuccess($NaverToken)) {
                            $memberSnsService = new MemberSnsService();
                            if ($memberSnsService->hasSnsMember($NaverToken['response']['id'])) {
                                $js = "
                                    alert('" . __('이미 다른 회원정보와 연결된 계정입니다. 다른 계정을 이용해주세요.') . "');
                                    if (window.opener === null || Object.keys(window.opener).indexOf('" . $functionName . "') < 0) {
                                        location.href='../../main/index.php';
                                    } else {
                                        self.close();
                                    }
                                ";
                                $this->js($js);
                            }
                            $userProfile = $naverApi->getUserProfile($NaverToken);
                            $memberSnsService->connectSns(Session::get(Member::SESSION_MEMBER_LOGIN . '.memNo'), $userProfile['id'], $NaverToken['access_token'], 'naver');
                            $naverApi->logByLink();
                            $js = "
                                alert('" . __('계정 연결이 완료되었습니다. 로그인 시 연결된 계정으로 로그인 하실 수 있습니다.') . "');
                                if (window.opener === null || Object.keys(window.opener).indexOf('" . $functionName . "') < 0) {
                                    location.href='../../mypage/my_page.php';
                                } else {
                                    opener.location.href='../../mypage/my_page.php';
                                    self.close();
                                }
                            ";
                            $this->js($js);
                        }
                    }
                    $returnURL = Request::getDomainUrl() . Request::getRequestUri() . '&referer=' . Request::getReferer();
                    $loginURL = $naverApi->getLoginURL($returnURL);
                    $this->redirect($loginURL);
                    break;
                case 'disconnect':
                    if($naverApi->hasError()) {
                        throw new Exception($request->get()->get('error_description'));
                    }
                    $member = $session->get(Member::SESSION_MEMBER_LOGIN);
                    if ($member['snsJoinFl'] == 'y') {
                        $logger->info('Impossible disconnect member joined by naver');
                        $this->json(
                            [
                                'error'   => 'naver',
                                'message' => __('네이버로 가입한 회원님은 연결을 해제 할 수 없습니다.'),
                            ]
                        );
                    }

                    if ($session->has(GodoNaverServerApi::SESSION_ACCESS_TOKEN)) {
                        $logger->info('Has naver access token');
                        $naverToken = $session->get(GodoNaverServerApi::SESSION_ACCESS_TOKEN, []);
                        $logger->debug('session access token', $naverToken);
                        $session->del(GodoNaverServerApi::SESSION_ACCESS_TOKEN);
                        $memberSnsService = new MemberSnsService();
                        $memberSnsService->disconnectSns($member['memNo']);
                        $naverApi->logByDrop();
                        $logger->info('Disconnect naver');
                        $this->json(
                            [
                                'message' => __('네이버 연결이 해제되었습니다.'),
                                'url'     => '../mypage/my_page.php',
                            ]
                        );
                    } else {
                        $logger->info('Disconnect naver fail. not found disconnect information');
                        $this->json(
                            [
                                'error'   => 'naver',
                                'message' => __('네이버 연결해제에 필요한 정보를 찾을 수 없습니다.'),
                                'url'     => '../mypage/my_page_password.php',
                            ]
                        );
                    }
                    break;
            }
        } catch (AlertRedirectCloseException $e) {
            throw $e;
        } catch(\Exception $e) {
            switch ($request->get()->get('mode', 'login')) {
                case 'login':
                    if (Request::isMobile()) {
                        throw new AlertRedirectException($e->getMessage(), null, null, '../login.php');
                    } else {
                        throw new AlertCloseException($e->getMessage(), $e->getCode(), $e);
                            /*
                            if ($e->getTarget() == 'opener') {
                                throw $e;
                            } else {
                                throw new AlertCloseException($e->getMessage(), $e->getCode(), $e);
                            }
                            */
                    }
                    break;
                case 'connect':
                    MemberUtil::logoutNaver();
                    throw new AlertCloseException($e->getMessage(), $e->getCode(), $e);
                    break;
                case 'disconnect':
                    break;
            }
        }
    }
}
