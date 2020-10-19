<?php

namespace Bundle\Controller\Front\Member\Kakao;

use Component\Member\MemberSnsService;
use Component\Member\MyPage;
use Component\Godo\GodoKakaoServerApi;
use Component\Attendance\AttendanceCheckLogin;
use Component\Member\Util\MemberUtil;
use Component\Member\Member;
use Exception;
use Framework\Debug\Exception\AlertRedirectException;
use Framework\Debug\Exception\AlertCloseException;
use Component\Member\MemberSnsDAO;

/**
 * 카카오 로그인 및 회원가입
 * @package Bundle\Controller\Front\Member\Kakao
 * @author  sojoeng
 */

class KakaoLoginController extends \Controller\Front\Controller
{
    public function index()
    {
        $request = \App::getInstance('request');
        $session = \App::getInstance('session');
        $logger = \App::getInstance('logger');
        $logger->info(sprintf('start controller: %s', __METHOD__));

        $kakaoType=null;

        try {
            $functionName = 'popup';
            if(gd_is_skin_division()) {
                $functionName = 'gd_popup';
            }

            $kakaoApi = new GodoKakaoServerApi();
            $memberSnsService = new MemberSnsService();

            //state 값 decode
            $state = json_decode($request->get()->get('state'), true);
            $logger->info(sprintf('state : %s', $request->get()->get('state')));
            $logger->info(sprintf('state code : %s', $request->get()->get('code')));

            //state 값을 이용해 분기처리
            $kakaoType= $state['kakaoType'];
            //returnUrl 추출
            $returnURLFromAuth = rawurldecode($state['returnUrl']);
            // saveAutologin
            $saveAutoLogin = $state['saveAutoLogin'];

            //카카오계정 로그인 팝업창에서 동의안함 클릭시 팝업창 닫힘 처리
            if($request->get()->get('error') == 'access_denied'){
                $logger->channel('kakaoLogin')->info($request->get()->get('error_description'));
               $js=" 
               if (window.opener === null || Object.keys(window.opener).indexOf('" . $functionName . "') < 0) {
                    if('".$kakaoType."' == 'join_method'){
                        location.href='../../member/join_method.php';
                    }else{     
                        location.href='../../mypage/my_page.php';
                    }
                } else {
                    opener.location.reload();
                    self.close();
                }";
                $this->js($js);
            }

            if($code = $request->get()->get('code')){
                if($endlen = (strpos($request->getRequestUri(), '?'))){
                    $returnURL = $request->getDomainUrl() . substr($request->getRequestUri(), 0, $endlen);
                }
                // 토큰 정보
                $properties = $kakaoApi->getToken($code, $returnURL);

                //사용자 정보
                $userInfo = $kakaoApi->getUserInfo($properties['access_token']);

                //세션에 사용자 정보 저장
                $session->set(GodoKakaoServerApi::SESSION_ACCESS_TOKEN, $properties);
                //$session->set(GodoKakaoServerApi::SESSION_USER_PROFILE, $userInfo);

                $memberSns = $memberSnsService->getMemberSnsByUUID($userInfo['id']);

                // kakao 아이디로 회원가입한 회원인지 검증
                if($memberSnsService->validateMemberSns($memberSns)) {
                    $logger->channel('kakaoLogin')->info('pass validationMemberSns');

                    if($session->has(Member::SESSION_MEMBER_LOGIN)){
                        //일반회원 카카오 아이디 연동 거부 처리
                        if($kakaoType == 'connect'){
                            $logger->channel('kakaoLogin')->info('Deny app link');
                            $js = "
                               alert('" . __('이미 다른 회원정보와 연결된 계정입니다. 다른 계정을 이용해주세요.') . "');
                               if (window.opener === null || Object.keys(window.opener).indexOf('" . $functionName . "') < 0) {
                                  location.href='../../mypage/my_page.php';
                               } else {
                                  self.close();
                               }
                             ";
                            $this->js($js);
                        }
                        //마이페이지 회원정보 수정시 인증 정보 다를때 처리
                        if ($memberSns['memNo'] != $session->get(Member::SESSION_MEMBER_LOGIN . '.memNo', 0)) {
                            $logger->info($session->get(GodoKakaoServerApi::SESSION_ACCESS_TOKEN));
                            $logger->channel('kakaoLogin')->info('not eq memNo');
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

                        //마이페이지 회원정보 수정시 인증
                        if($kakaoType == 'my_page_password'){
                            $memberSnsService->saveToken($userInfo['id'], $properties['access_token'], $properties['refresh_token']);
                            $logger->channel('kakaoLogin')->info('move my page');
                            $session->set(MyPage::SESSION_MY_PAGE_PASSWORD, true);
                            $js="
                                 if (typeof(window.top.layerSearchArea) == \"object\") {
                                        parent.location.href='../../mypage/my_page.php';
                                    } else if (window.opener === null) {
                                        location.href='" . gd_isset($returnURLFromAuth, '../../mypage/my_page.php') . "';
                                    } else {
                                        opener.location.href='../../mypage/my_page.php';
                                        self.close();
                                    }
                            ";
                            $this->js($js);
                        }

                        //회원탈퇴
                        if($kakaoType == 'hack_out') {
                            $logger->channel('kakaoLogin')->info('hack out kakao id');
                            $session->set(GodoKakaoServerApi::SESSION_KAKAO_HACK, true);
                            $js = "
                                   if (window.opener === null || Object.keys(window.opener).indexOf('" . $functionName . "') < 0) {
                                       location.href='../../mypage/hack_out.php';
                                   } else {
                                       opener.location.href='../../mypage/hack_out.php';
                                       self.close();
                                   }
                                   ";
                            $this->js($js);
                        }

                        //일반회원 마이페이지 카카오 아이디 연동 해제
                        if($kakaoType == 'disconnect') {
                            if($memberSns['snsJoinFl'] == 'y'){
                                $logger->channel('kakaoLogin')->info('Impossible disconnect member joined by kakao');
                                $js=" alert('" . __('카카오로 가입한 회원님은 연결을 해제 할 수 없습니다.') . "');";
                                $this->js($js);
                            }
                            if ($session->has(GodoKakaoServerApi::SESSION_ACCESS_TOKEN)) {
                                $kakaoToken = $session->get(GodoKakaoServerApi::SESSION_ACCESS_TOKEN);
                                $kakaoApi->unlink($kakaoToken['access_token']);
                                $session->del(GodoKakaoServerApi::SESSION_ACCESS_TOKEN);
                                $memberSnsService = new MemberSnsService();
                                $memberSnsService->disconnectSns($memberSns['memNo']);
                                $session->set(Member::SESSION_MEMBER_LOGIN . '.snsTypeFl', '');
                                $session->set(Member::SESSION_MEMBER_LOGIN . '.accessToken', '');
                                $session->set(Member::SESSION_MEMBER_LOGIN . '.snsJoinFl', '');
                                $session->set(Member::SESSION_MEMBER_LOGIN . '.connectFl', '');
                                $js = "
                                alert('" . __('카카오 연결이 해제되었습니다.') . "');
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
                    }
                    if (isset($memberSns['accessToken'])) {
                        $logger->info('isset accessToken');
                        $kakaoApi->logout($memberSns['accessToken']);
                        $logger->info('success logout');
                    }
                    // 카카오 아이디 로그인
                    $memberSnsService->saveToken($userInfo['id'], $properties['access_token'], $properties['refresh_token']);
                    $memberSnsService->loginBySns($userInfo['id']);
                    if ($saveAutoLogin == 'y') $session->set(Member::SESSION_MYAPP_SNS_AUTO_LOGIN, 'y');
                    $logger->channel('kakaoLogin')->info('success login by kakao');

                    $db = \App::getInstance('DB');
                    try {
                        $db->begin_tran();
                        $check = new AttendanceCheckLogin();
                        $message = $check->attendanceLogin();
                        $db->commit();

                        // 에이스 카운터 로그인 스크립트
                        $acecounterScript = \App::load('\\Component\\Nhn\\AcecounterCommonScript');
                        $acecounterUse = $acecounterScript->getAcecounterUseCheck();
                        if ($acecounterUse) {
                            echo $acecounterScript->getLoginScript();
                        }

                        $logger->info('commit attendance login');
                        if ($message) {
                            $logger->info(sprintf('has attendance message: %s', $message));
                            $js = "
                                    alert('" . $message . "');
                                    if (typeof(window.top.layerSearchArea) == 'object') {
                                        parent.location.href='" . $returnURLFromAuth . "';
                                    } else if (window.opener === null) {
                                        location.href='" . $returnURLFromAuth . "';
                                    } else {
                                        opener.location.href='" . $returnURLFromAuth . "';
                                        self.close();
                                    }
                                ";
                            $this->js($js);
                        }
                    } catch (Exception $e) {
                        $db->rollback();
                        $logger->error(__METHOD__ . ', ' . $e->getFile() . '[' . $e->getLine() . '], ' . $e->getMessage());
                    }

                    if ($kakaoType == 'join_method') {
                        $logger->channel('kakaoLogin')->info('already join member');
                        $js = "
                                alert('" . __('이미 가입한 회원입니다.') . "');
                                if (typeof(window.top.layerSearchArea) == 'object') {
                                    parent.location.href='" . $returnURLFromAuth . "';
                                } else if (window.opener === null) {
                                    location.href='" . $returnURLFromAuth . "';
                                } else {
                                    opener.location.href='" . $returnURLFromAuth . "';
                                    self.close();
                                }
                            ";
                        $this->js($js);
                    }
                    $logger->channel('kakaoLogin')->info('move return url');
                    $loginReturnUrl = $returnURLFromAuth;
                    if ($request->isMyapp() && $request->get()->get('saveAutoLogin') == 'y') {
                        $loginReturnUrl .= '?saveAutoLogin=' . $request->get()->get('saveAutoLogin');
                    }
                    $js = "
                            if (typeof(window.top.layerSearchArea) == 'object') {
                                parent.location.href='" . $loginReturnUrl . "';
                            } else if (window.opener === null) {
                                location.href='" . $loginReturnUrl . "';
                            } else {
                                opener.location.href='" . $loginReturnUrl . "';
                                self.close();
                            }
                        ";
                    $this->js($js);
                }

                // 일반회원 카카오 아이디 연동 처리
                if($kakaoType == 'connect') {
                    $kakaoToken = $session->get(GodoKakaoServerApi::SESSION_ACCESS_TOKEN);
                    $kakaoApi->appLink($kakaoToken['access_token']);
                    $memberSnsService->connectSns($session->get(Member::SESSION_MEMBER_LOGIN . '.memNo'), $userInfo['id'], $properties['access_token'], 'kakao');
                    $memberSnsService->saveToken($userInfo['id'], $properties['access_token'], $properties['refresh_token']);
                    $session->set(Member::SESSION_MEMBER_LOGIN . '.snsTypeFl', 'kakao');
                    $session->set(Member::SESSION_MEMBER_LOGIN . '.accessToken', $properties['access_token']);
                    $session->set(Member::SESSION_MEMBER_LOGIN . '.snsJoinFl', 'n');
                    $session->set(Member::SESSION_MEMBER_LOGIN . '.connectFl', 'y');
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

                if ($kakaoType == 'join_method') {
                    $logger->channel('kakaoLogin')->info('kakao id applink success');
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
                //마이페이지 회원 인증 다를경우
                if($kakaoType == 'my_page_password'){
                    //현재 받은 세션값으로 로그아웃 시키기
                    \Logger::channel('kakaoLogin')->info('different inform', $session->get(GodoKakaoServerApi::SESSION_USER_PROFILE));
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
                                location.href='" . gd_isset($returnURLFromAuth, '../../main/index.php') . "';
                            }
                        } else {
                           if (confirm('" . __('가입되지 않은 회원정보입니다. 회원가입을 진행하시겠습니까?') . "')) {
                                opener.location.href = '../join_agreement.php';
                                self.close();
                           }else {
                                opener.location.href = '" . gd_isset($returnURLFromAuth, '../../main/index.php') . "';
                                self.close();
                           }
                        }
                        ;";
                $this->js($js);

            }
            // 카카오 로그인 팝업을 띄우는 케이스
            $callbackUri = $request->getRequestUri();
            \Logger::channel('kakaoLogin')->info('callbackUri 350 %s', $callbackUri);

            $state = array();
            if ($startLen = strpos($request->getRequestUri(), "?")) {
                $requestUriArray = explode('&', substr($request->getRequestUri(), ($startLen + 1)));
                \Logger::channel('kakaoLogin')->info('requestUriArray 354 %s', json_encode($requestUriArray));

                $kakaoTypeInRequestUri = $requestUriArray[0];
                $kakaoTypeToState= explode('=', $kakaoTypeInRequestUri);
                $state['kakaoType'] = $kakaoTypeToState[1];
                //returnUrl이 여러 개 있을 경우
                foreach ($requestUriArray as $key => $val) {
                    $isReturnUrl = strstr($val, 'returnUrl');
                    if ($isReturnUrl) {
                        // 기존에 explode 를 = 을 기준으로 작업되어 returnUrl에 있는 파라미터 들의 = 까지 구분되어 주소가 수정되는 이슈로 str_replace 로 변경
                        $returnUrlToState = str_replace('returnUrl=','',$val);
                        $state['returnUrl'] = $returnUrlToState;
                        // 정상적인 returnUrl 인지 확인용 로그
                        \Logger::channel('kakaoLogin')->info('returnUrlToState 359 %s', json_encode($returnUrlToState));
                    }
                }
                $state['referer'] = $request->getReferer();
                if ($request->get()->get('saveAutoLogin') == 'y') $state['saveAutoLogin'] = 'y';
                $callbackUri = substr($request->getRequestUri(), 0, $startLen);
            }
            $redirectUri = $request->getDomainUrl() . $callbackUri;
            \Logger::channel('kakaoLogin')->info('Redirect URI is %s', $redirectUri);

            $getCodeURL = $kakaoApi->getCodeURL($redirectUri, $state);
            \Logger::channel('kakaoLogin')->info('Code URI is %s', $getCodeURL);
            $this->redirect($getCodeURL);
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
            }
        }
    }
}