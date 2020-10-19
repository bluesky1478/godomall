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

namespace Bundle\Controller\Admin\Base;

use App;
use Component\Godo\MyGodoSmsServerApi;
use Component\Mail\MailMimeAuto;
use Component\Member\Exception\LoginException;
use Component\Member\Manager;
use Component\Member\ManagerCs;
use Component\Policy\ManageSecurityPolicy;
use Component\Sms\Exception\PasswordException;
use Component\Sms\SmsSender;
use Component\Validator\Validator;
use Exception;
use Framework\Debug\Exception\AlertRedirectException;
use Framework\Debug\Exception\LayerException;
use Framework\Debug\Exception\LayerNotReloadException;
use Framework\Security\Otp;
use Framework\Utility\ComponentUtils;
use Framework\Utility\DateTimeUtils;
use Framework\Utility\StringUtils;
use RuntimeException;
use Vendor\Captcha\Captcha;

class LoginPsController extends \Controller\Admin\Controller
{
    /**
     * {@inheritdoc}
     *
     * @throws AlertRedirectException
     * @throws LayerException
     * @author Jong-tae Ahn <qnibus@godo.co.kr>
     */
    public function index()
    {
        if (!\is_object($this->db)) {
            $this->db = \App::load('DB');
        }

        $logger = \App::getInstance('logger');
        $request = \App::getInstance('request');
        $session = \App::getInstance('session');
        $cookie = \App::getInstance('cookie');
        $adminLog = \App::load('Component\\Admin\\AdminLogDAO');
        $setLogFl = false;
        try {
            $funcLoginPostHandler = function () use ($request, $cookie, $session) {
                // 아이디 저장 처리 (암호화)
                if ($request->post()->has('saveId')) {
                    if ($request->post()->get('saveId') === 'y' && $request->post()->get('managerId')) {
                        $encryptor = \App::getInstance('encryptor');
                        // 암호화 쿠키생성 (10일)
                        $encryptId = $encryptor->encrypt($request->post()->get('managerId'));
                        $cookie->set('SAVE_MANAGER_ID', $encryptId, 3600 * 24 * 10);
                    }
                } elseif ($cookie->has('SAVE_MANAGER_ID')) {
                    $cookie->del('SAVE_MANAGER_ID');
                }

                if ($session->has(\Component\Member\Manager::SESSION_MANAGER_LOGIN)) {
                    $logger = \App::getInstance('logger');
                    // 관리자 로그인 시 메일 발송 무료 포인트 충전 여부 체크 및 충전
                    $isFirstFreePointCharge = false;
                    $config = ComponentUtils::getPolicy('mail.config');
                    StringUtils::strIsSet($config['freePointChargeDate'], '');
                    $currentDt = new \DateTime('now');
                    $lastChargeDt = new \DateTime($config['freePointChargeDate']);
                    if ($config['freePointChargeDate'] === ''
                        || ($currentDt->format('m') !== $lastChargeDt->format('m'))) {
                        $isFirstFreePointCharge = true;
                    }

                    if ($isFirstFreePointCharge) {
                        $globals = \App::getInstance('globals');
                        $config = ComponentUtils::getPolicy('mail.config');
                        $config['freePointChargeCount']++;
                        $config['freePoint'] = $globals->get('gLicense.mailPoint');
                        $config['freePointChargeDate'] = DateTimeUtils::dateFormat('Y-m-d', 'now');
                        ComponentUtils::setPolicy('mail.config', $config, true);
                        $logger->notice(__METHOD__ . ', 메일발송 무료 포인트가 충전되었습니다.');
                    }
                }
            };

            // back history시 아이프레임이 아닌 경우가 발생한다.
            // 이에 request 값이 없는 경우 관리자 메인으로 이동 처리
            if (!$request->request()->has('mode')) {
                throw new AlertRedirectException(null, null, null, '/base/login.php');
            }

            // 모드값에 따른 처리
            switch ($request->request()->get('mode')) {
                // 관리자 로그인 여부 확인
                case 'isLogin':
                    $this->json(['isLogin' => $session->has('manager.managerId'),]);
                    break;
                // --- 관리자 로그인
                case 'login':
                    if ($request->post() === null) {
                        throw new AlertRedirectException(__('접근 권한이 없습니다.'), null, null, URI_ADMIN . 'base/login.php');
                    }
                    $managerId = $request->post()->get('managerId');

                    $arrBind = [];
                    $query = "SELECT isSuper FROM es_manager WHERE managerId = ?";
                    $this->db->bind_param_push($arrBind,'s',$managerId);
                    $csManualAccount = $this->db->query_fetch($query, $arrBind, false);
                    unset($arrBind);

                    if (strpos($managerId, ManagerCs::PREFIX_CS_ID) === 0 || $csManualAccount['isSuper'] == 'cs' ) {
                        $manager = \App::load(ManagerCs::class);
                    } else {
                        $manager = \App::load(Manager::class);
                    }

                    $rentalDisk = \App::load('\\Component\\Mall\\RentalDisk');
                    $rentalDisk->diskUsage('react');
                    $rentalDisk->setDu('all');

                    // 관리자 용량초과여부 체크
                    $diskData = $manager->adminDiskCheck();

                    // 로그인 체크 후 매니저 정보 반환
                    $managerInfo = $manager->validateManagerLogin(
                        [
                            'managerId' => $managerId,
                            'managerPw' => $request->post()->get('managerPw'),
                        ],
                        $diskData['adminAccess']
                    );

                    $securityPolicy = \App::load(ManageSecurityPolicy::class);
                    if ($securityPolicy->useSecurityLogin() &&
                        (($managerInfo['isSmsAuth'] === 'y'
                                && Validator::required($managerInfo['cellPhone']))
                            || ($managerInfo['isEmailAuth'] === 'y'
                                && Validator::email($managerInfo['email'], true)))) {
                        $isValidAuth = function () use ($managerInfo, $securityPolicy) {
                            $period = $securityPolicy->getValue()['authLoginPeriod'];
                            StringUtils::strIsSet($period, 0);
                            if ($period < 1 || $managerInfo['lastLoginAuthDt'] === '0000-00-00 00:00:00') {
                                return false;
                            }
                            $timestamp = strtotime($managerInfo['lastLoginAuthDt']);
                            $addPeriodDate = strtotime('+' . $period . ' days', $timestamp);
                            $lastLoginAuthDate = date('Y-m-d', $addPeriodDate);
                            $nowDate = date('Y-m-d');

                            return $lastLoginAuthDate > $nowDate;
                        };

                        if ($isValidAuth()) {
                            $manager->afterManagerLogin($managerInfo);
                            $funcLoginPostHandler();
                            // 공급사/본사 여부에 따른 페이지 이동 처리
                            if ($request->post()->has('returnUrl') && $request->post()->get('returnUrl')) {
                                $returnUrl = $request->post()->get('returnUrl');
                            } elseif (Manager::isProvider()) {
                                $returnUrl = URI_ADMIN . 'provider/index.php';
                            } else {
                                $returnUrl = URI_ADMIN . 'base/index.php';
                            }
                            $addScript = 'top.location.href="' . $returnUrl . '"';
                            $message = '관리자 접속이 승인되었습니다.';
                            $setLogFl = true;
                            throw new LayerException(__($message), null, null, $addScript, 1000, true);
                        }
                    }

                    if ($diskData['adminAccess'] === false) {
                        if ($managerInfo['scmKind'] === 'c') {
                            $message = '<p><div>쇼핑몰 제공용량이 초과되어 전체 관리자 로그인이 차단되었습니다.<br />';
                            $message .= '<br />- 초과시점 : <span class="c-gdred">' . $diskData['fullDate'] . '</span>';
                            $message .= '<br />- 잔여용량 확보 방법 안내<br />1. 불필요한 용량 정리<br />';
                            $message .= '2. 디스크 용량 추가 </div><div style="text-align:center;margin-top:30px;">';
                            $message .= '<button class="btn btn-white btn-lg" onclick="gotoGodomall(\'disk\')"';
                            $message .= ' style="margin-right:10px;">마이고도로 이동</button><button class="btn btn-black';
                            $message .= ' btn-lg" onclick="window.location.href=\'./\'">확인</button></div></p>';
                        } else {
                            $message = '<p><div>쇼핑몰 제공용량이 초과되어 전체 관리자 로그인이 차단되었습니다.<br />';
                            $message .= '본사 담당자에게 문의해주시기 바랍니다.</div><div style="text-align:center;';
                            $message .= 'margin-top:30px;"><button class="btn btn-black btn-lg"';
                            $message .= ' onclick="window.location.href=\'./\'">확인</button></div></p>';
                        }
                        $setLogFl = true;
                        throw new LayerException($message, null, null, null, 60000);
                    }

                    if (array_key_exists('csSno', $managerInfo) || $securityPolicy->useSecurityLogin()) {
                        // 임시 데이터 저장
                        $session->set(Manager::SESSION_TEMP_MANAGER, $managerInfo);
                        $script = 'if (typeof parent.godo.layer.auth_login === "undefined")';
                        $script .= ' {alert("인증에 필요한 요소가 없습니다.");}';
                        $script .= ' else {parent.godo.layer.auth_login();}';
                        $this->js($script);
                    } else {
                        $manager->afterManagerLogin($managerInfo);
                        $funcLoginPostHandler();
                        // 공급사/본사 여부에 따른 페이지 이동 처리
                        if ($request->post()->has('returnUrl') && $request->post()->get('returnUrl')) {
                            $returnUrl = $request->post()->get('returnUrl');
                        } elseif (Manager::isProvider()) {
                            $returnUrl = URI_ADMIN . 'provider/index.php';
                        } else {
                            $returnUrl = URI_ADMIN . 'base/index.php';
                        }

                        $addScript = 'top.location.href="' . $returnUrl . '"';
                        $message = '관리자 접속이 승인되었습니다.';
                        $setLogFl = true;
                        throw new LayerException(__($message), null, null, $addScript, 1000, true);
                    }
                    break;
                // --- 관리자 로그아웃
                case 'logout':
                    $message = __('안전하게 로그아웃 되었습니다.');

                    if (!$session->has('manager')) {
                        $message = __('로그인 정보가 없거나 잘못된 접속입니다.');
                    }
                    $manager = \App::load(Manager::class);
                    $manager->managerLogout();

                    // 샵링커 설정 config 불러오기
                    $shoplinkerInfo = gd_policy('shoplinker.config');

                    if (empty($shoplinkerInfo) === true) {
                        throw new AlertRedirectException($message, null, null, URI_ADMIN . 'base/login.php', 'top');
                    }

                    header('access-control-allow-origin: *');
                    $echos = [];
                    $echos[] = "<script type='text/javascript'";
                    $echos[] = " src='/admin/gd_share/script/jquery/jquery.min.js?ts=1482718074'></script>";
                    $echos[] = "<script>
                    $(document).ready(function () {
                        alert('" . $message . "');
                        top.location.replace('/base/login.php');
                    });
                    </script>";
                    $echos[] = "<iframe src='https://mgr.shoplinker-s.com/eshop/signin/logout'";
                    $echos[] = " width='0' height='0' frameborder='0'>";

                    echo implode('', $echos);
                    exit;

                    break;

                // 인증번호 체크
                case 'checkSmsNumber':
                    // 임시 세션 정보
                    $tmpManager = $session->get(Manager::SESSION_TEMP_MANAGER);

                    if ($cookie->get('CAPTCHA_RETRY_' . strtoupper($tmpManager['managerId'])) > 4) {
                        $captchaNumber = strtoupper($request->post()->get('capchaNumber'));
                        $captcha = new Captcha();   //자동입력 방지문구 체크
                        $rst = $captcha->verify($captchaNumber, 1);

                        if ($rst['code'] !== '0000') {
                            $this->json(
                                [
                                    'error'   => 100,
                                    'message' => '자동등록방지문자가 틀렸습니다.',
                                ]
                            );
                        }
                    }

                    if (trim($request->post()->get('smsAuthNumber')) === $tmpManager['smsAuthNumber']) {
                        $cookie->del('CAPTCHA_RETRY_' . strtoupper($tmpManager['managerId']));
                        $tmpManager['lastLoginAuthDt'] = DateTimeUtils::dateFormat('Y-m-d H:i:s', 'now');

                        if (array_key_exists('csSno', $tmpManager)) {
                            $manager = \App::load(ManagerCs::class);
                        } else {
                            $manager = \App::load(Manager::class);
                        }
                        $manager->afterManagerLogin($tmpManager);
                        $funcLoginPostHandler();

                        // 공급사/본사 여부에 따른 페이지 이동 처리
                        if ($request->post()->has('returnUrl') && $request->post()->get('returnUrl')) {
                            $returnUrl = $request->post()->get('returnUrl');
                        } elseif (Manager::isProvider()) {
                            $returnUrl = URI_ADMIN . 'provider/index.php';
                        } else {
                            $returnUrl = URI_ADMIN . 'base/index.php';
                        }

                        $this->json(
                            [
                                'error'   => 0,
                                'message' => 'SUCCESS',
                                'link'    => $returnUrl,
                            ]
                        );
                    } else {
                        $retry = $cookie->get('CAPTCHA_RETRY_' . strtoupper($tmpManager['managerId'])) + 1;
                        $cookie->set('CAPTCHA_RETRY_' . strtoupper($tmpManager['managerId']), $retry, 0);
                        $this->json(
                            [
                                'error'   => 101,
                                'message' => '관리자 인증번호가 맞지 않습니다.',
                                'retry'   => $retry,
                            ]
                        );
                    }
                    break;

                // 인증번호 재전송
                case 'smsReSend':
                    // 임시 세션 정보
                    $tmpManager = $session->get(Manager::SESSION_TEMP_MANAGER);
                    $manager = \App::load(Manager::class);
                    $smsSender = \App::load(SmsSender::class);
                    $smsSender->setIsThrowPasswordException(true);
                    $smsAuth = $manager->sendSmsAuthNumber($tmpManager);
                    $tmpManager['smsAuthNumber'] = $smsAuth['smsAuthNumber'];
                    $tmpManager['isSmsLogin'] = $smsAuth['message'];

                    // 일반 or SMS 보안로그인 처리
                    if ($tmpManager['isSmsLogin'] === 'OK') {
                        $session->set(Manager::SESSION_TEMP_MANAGER, $tmpManager);
                        // 인증번호 발송시 로그 작성
                        $request->post()->set('authTarget', $tmpManager['cellPhone']);
                        $request->post()->set('managerId', $tmpManager['managerId']);
                        $adminLog->setAdminLog();
                        // 임시 데이터 저장
                        $this->json(
                            [
                                'error'   => 0,
                                'message' => __('인증번호전송성공'),
                            ]
                        );
                    } else {
                        $this->json(
                            [
                                'error'   => 1,
                                'message' => $tmpManager['isSmsLogin'],
                            ]
                        );
                    }
                    break;

                //이메일 인증번호 발송
                case 'emailSend':
                    $basicInfo = gd_policy('basic.info');
                    $mallEmail = $basicInfo['email'];
                    StringUtils::strIsSet($mallEmail, '');
                    if ($mallEmail === '') {
                        $this->json(
                            [
                                'error'   => 4,
                                'message' => __('쇼핑몰 도메인/대표 이메일 정보 등록이 필요합니다.'),
                            ]
                        );
                    }

                    $managerInfo = $session->get(Manager::SESSION_TEMP_MANAGER);
                    unset($managerInfo['hasCellPhoneChangeAuthorize']);
                    $mailMimeAuto = App::load(MailMimeAuto::class);
                    $securityInfo['certificationCode'] = Otp::getOtp(8);
                    $securityInfo['authType'] = 'authEmail';
                    $request = \App::getInstance('request');
                    if (array_key_exists('csSno', $managerInfo) && $managerInfo['csSno'] > 0) {
                        $managerInfo['newEmail'] = $securityInfo['email'] = $request->post()->get('newEmail');
                    } else {
                        $securityInfo['email'] = $managerInfo['email'];
                    }

                    if (array_key_exists('csSno', $managerInfo)) {
                        $api = \App::load(MyGodoSmsServerApi::class);
                        $securityInfo['email'] = $request->post()->get('newEmail');
                        $apiResult = $api->checkGodoCsInfo($securityInfo['email']);
                        if ($apiResult['code'] !== 'OK') {
                            $this->json(
                                [
                                    'error'   => 3,
                                    'message' => '관리자 어드민 접근권한이 없거나 잘못된 인증정보입니다. 다시 확인 바랍니다.',
                                ]
                            );
                        }
                    }

                    $result = $mailMimeAuto
                        ->init(MailMimeAuto::ADMIN_SECURITY, $securityInfo, DEFAULT_MALL_NUMBER)
                        ->autoSend();

                    $managerInfo['smsAuthNumber'] = $securityInfo['certificationCode'];
                    $session->set(Manager::SESSION_TEMP_MANAGER, $managerInfo);

                    if ($result === true) {
                        // 인증번호 발송시 로그 작성
                        $request->post()->set('authTarget', $securityInfo['email']);
                        $request->post()->set('managerId', $managerInfo['managerId']);
                        $adminLog->setAdminLog();
                        $this->json(
                            [
                                'error'   => 0,
                                'message' => __('인증번호전송성공'),
                            ]
                        );
                    } else {
                        $this->json(
                            [
                                'error'   => 2,
                                'message' => __('메일 발송 중 오류가 발생하였습니다.'),
                            ]
                        );
                    }
                    break;
                case 'initLoginLimit':
                    $initLimitLoginLog = ['affectedRows' => 0];

                    if (MyGodoSmsServerApi::getAuth()) {
                        $manager = \App::load(Manager::class);
                        $logParams = ['managerId' => $session->get($manager::SESSION_LIMIT_FLAG_ON_MANAGER_ID)];
                        $initLimitLoginLog = $manager->initLimitLoginLog($logParams);
                    } else {
                        $logger->info('fail godo sms auth');
                    }

                    MyGodoSmsServerApi::deleteAuth();

                    if ($initLimitLoginLog['affectedRows'] > 0) {
                        throw new LayerNotReloadException('로그인 제한이 해제되었습니다.');
                    }

                    $logger->info('fail init login limit id[' . $request->post()->get('managerId') . ']');
                    throw new LayerNotReloadException('로그인 제한이 해제에 실패하였습니다.');
                    break;
                case 'findAdminId':
                    if (MyGodoSmsServerApi::getAuth()) {
                        $manager = \App::load(Manager::class);
                        $params['sno'] = 1;
                        $adminId = $manager->getSpecificManagerInfo($params, 'managerId');
                    } else {
                        throw new LayerNotReloadException('인증을 다시 시도해 주시기 바랍니다.');
                    }

                    MyGodoSmsServerApi::deleteAuthKey();
                    MyGodoSmsServerApi::deleteAuth();

                    $this->json(['adminId' => $adminId['managerId'],]);
                    break;
                case 'deleteAdminAuth':
                    MyGodoSmsServerApi::deleteAuthKey();
                    break;
                case 'checkAdminId':
                    $manager = \App::load(Manager::class);
                    $result = $manager->compareManagerId($request->request()->get('managerId'));
                    $this->json(['result' => $result,]);
                    break;
                default:
                    throw new AlertRedirectException('요청을 찾을 수 없습니다.', 404, null, '/base/login.php', 'top');
                    break;
            }
        } catch (LoginException $e) {
            if ($e->getCode() === LoginException::CODE_SUPER_MANAGER_LOGIN_FAIL_LIMIT_FLAG_ON) {
                throw new LayerNotReloadException($e->getMessage(), $e->getCode(), $e, 'parent.open_sms_auth();');
            }
            $addScript = 'parent.document.getElementById("frmLogin").reset();';
            $addScript .= 'parent.document.getElementById("login").focus();';
            throw new LayerNotReloadException($e->getMessage(), $e->getCode(), $e, $addScript);
        } catch (AlertRedirectException $e) {
            throw $e;
        } catch (LayerException $e) {
            if ($setLogFl === true) {
                $adminLog->setAdminLog();
            }
            throw $e;
        } catch (LayerNotReloadException $e) {
            throw $e;
        } catch (PasswordException $e) {
            $this->json(
                [
                    'error'   => 5,
                    'message' => $e->getMessage(),
                ]
            );
        } catch (Exception $e) {
            $adminLog->setAdminLog();
            if ($e instanceof RuntimeException) {
                $adminLog->setAdminLog();
            }
            $logger->warning($e->getMessage() . ', ' . $e->getFile() . ', ' . $e->getLine(), $e->getTrace());
            throw new LayerException(__('로그인 정보 오류로 접속 실패하였습니다.') . '<br>' . $e->getMessage(), null, null, null, 3000);
        }
    }
}
