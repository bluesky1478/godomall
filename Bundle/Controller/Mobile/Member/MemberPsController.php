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

namespace Bundle\Controller\Mobile\Member;

use App;
use Component\Facebook\Facebook;
use Component\Godo\GodoPaycoServerApi;
use Component\Godo\GodoNaverServerApi;
use Component\Godo\GodoKakaoServerApi;
use Component\Godo\GodoWonderServerApi;
use Component\Member\MemberSnsService;
use Component\Member\MemberValidation;
use Component\Member\Util\MemberUtil;
use Component\Coupon\Coupon;
use Component\Policy\SnsLoginPolicy;
use Component\Storage\Storage;
use Framework\Debug\Exception\DatabaseException;
use Framework\Object\SimpleStorage;
use Exception;
use Framework\Debug\Exception\AlertBackException;
use Framework\Utility\ComponentUtils;
use Framework\Utility\StringUtils;
use Request;
use Session;

/**
 * Class MemberPsController
 * @package Bundle\Controller\Mobile\Member
 * @author  yjwee
 */
class MemberPsController extends \Controller\Mobile\Controller
{
    /**
     * @inheritdoc
     */
    public function index()
    {
        try {
            // myapp checker
            if (Request::isMyapp() === true) {
                $useMyapp = empty(gd_policy('myapp.config')['builder_auth']['clientId']) === false
                    && empty(gd_policy('myapp.config')['builder_auth']['secretKey']) === false;
                $useMyappQuickLogin = gd_policy('myapp.config')['useQuickLogin'] == 'true' ? true : false;
            }

            if (Request::isMyapp() === true && Request::post()->get('hash')) {
                $myApp = App::load('\\Component\\Myapp\\Myapp');
                $code = Request::post()->get('code');
                if ($code) {
                    $guestOrderInfo = $myApp->getGuestOrderInfo($code);
                    Request::post()->set('orderNm', $guestOrderInfo['orderNm']);
                    Request::post()->set('orderNo', $guestOrderInfo['orderNo']);
                } else {
                    $postData = Request::post()->all();
                    if ($myApp->hmacValidate($postData) !== true) {
                        \Logger::channel('myapp')->error('Wrong Hash : ' . json_encode(Request::post()->all()));
                        throw new Exception(__("요청을 찾을 수 없습니다."));
                    }
                }
            } else {
                if((Request::getReferer() == Request::getDomainUrl()) || empty(Request::getReferer()) === true){
                    \Logger::error(__METHOD__ .' Access without referer');
                    throw new Exception(__("요청을 찾을 수 없습니다."));
                }
            }
            /** @var  \Bundle\Component\Member\Member $member */
            $member = App::load('\\Component\\Member\\Member');

            // --- 수신 정보
            $returnUrl = urldecode(Request::post()->get('returnUrl'));
            if (empty($returnUrl) || strpos($returnUrl, "member_ps") !== false) {
                $returnUrl = Request::getReferer();
            }

            $mode = Request::post()->get('mode', Request::get()->get('mode'));
            switch ($mode) {
                // 비회원 로그인 및 비회원 주문하기
                case 'guest':
                    MemberUtil::guest();
                    $this->redirect($returnUrl, null, 'top');
                    break;

                // 비회원 주문조회 하기 (최종 주문상세보기 페이지 이동은 AJAX에서 처리)
                case 'guestOrder':
                    $order = App::load('\\Component\\Order\\Order');

                    $orderNm = Request::post()->get('orderNm');
                    $orderNo = Request::post()->get('orderNo');
                    $aResult = $order->isGuestOrder($orderNo, $orderNm);
                    if ($aResult['result']) {
                        $orderNo = $aResult['orderNo'];
                        MemberUtil::guestOrder($orderNo, $orderNm);

                        // 마이앱 로그인뷰 스크립트
                        if (\Request::isMyapp() && $useMyapp && $useMyappQuickLogin) {
                            $this->redirect($returnUrl . '?orderNo='.$orderNo, null, 'top');
                            break;
                        }

                        $this->json(
                            [
                                'result'  => '0',
                                'message' => __('주문조회에 성공했습니다.'),
                                'orderNo' => $orderNo,
                            ]
                        );
                    } else {
                        $this->json(
                            [
                                'result'  => '-1',
                                'message' => __('주문자명과 주문번호가 일치하는 주문이 존재하지 않습니다. 다시 입력해 주세요. 주문번호와 비밀번호를 잊으신 경우, 고객센터로 문의하여 주시기 바랍니다.'),
                            ]
                        );
                    }
                    break;

                // 성인 비회원 로그인
                case 'adultGuest':
                    if (empty($returnUrl)) {
                        $returnUrl = Request::getReferer();
                    }
                    MemberUtil::adultGuest(Request::post()->toArray());
                    $this->redirect($returnUrl, null, 'top');
                    break;

                // 회원가입
                case 'join':
                    $memberVO = null;
                    try {
                        if (Session::has(GodoWonderServerApi::SESSION_USER_PROFILE)) {
                            \Component\Member\Util\MemberUtil::saveJoinInfoBySession(Request::post()->all());
                        }
                        \DB::begin_tran();
                        Session::set('isFront', 'y');
                        if (Session::has('pushJoin')) {
                            Request::post()->set('simpleJoinFl','push');
                        }
                        $memberVO = $member->join(Request::post()->xss()->all());
                        Session::del('isFront');
                        if (Session::has(GodoPaycoServerApi::SESSION_USER_PROFILE)) {
                            $paycoToken = Session::get(GodoPaycoServerApi::SESSION_ACCESS_TOKEN);
                            $userProfile = Session::get(GodoPaycoServerApi::SESSION_USER_PROFILE);
                            Session::del(GodoPaycoServerApi::SESSION_ACCESS_TOKEN);
                            Session::del(GodoPaycoServerApi::SESSION_USER_PROFILE);
                            $memberSnsService = new MemberSnsService();
                            $memberSnsService->joinBySns($memberVO->getMemNo(), $paycoToken['idNo'], $paycoToken['access_token'], 'payco');
                            $paycoApi = new GodoPaycoServerApi();
                            $paycoApi->logByJoin();
                        } elseif (Session::has(Facebook::SESSION_USER_PROFILE)) {
                            $userProfile = Session::get(Facebook::SESSION_USER_PROFILE);
                            $accessToken = Session::get(Facebook::SESSION_ACCESS_TOKEN);
                            $memberSnsService = new MemberSnsService();
                            $memberSnsService->joinBySns($memberVO->getMemNo(), $userProfile['id'], $accessToken, SnsLoginPolicy::FACEBOOK);
                        } elseif (Session::has(GodoNaverServerApi::SESSION_USER_PROFILE)) {
                            $naverToken = Session::get(GodoNaverServerApi::SESSION_ACCESS_TOKEN);
                            $userProfile = Session::get(GodoNaverServerApi::SESSION_USER_PROFILE);
                            Session::del(GodoNaverServerApi::SESSION_ACCESS_TOKEN);
                            Session::del(GodoNaverServerApi::SESSION_USER_PROFILE);
                            $memberSnsService = new MemberSnsService();
                            $memberSnsService->joinBySns($memberVO->getMemNo(), $userProfile['id'], $naverToken['access_token'], 'naver');
                            $naverApi = new GodoNaverServerApi();
                            $naverApi->logByJoin();
                        } elseif(Session::has(GodoKakaoServerApi::SESSION_USER_PROFILE)) {
                            $kakaoToken = Session::get(GodoKakaoServerApi::SESSION_ACCESS_TOKEN);
                            $kakaoProfile = Session::get(GodoKakaoServerApi::SESSION_USER_PROFILE);
                            Session::del(GodoKakaoServerApi::SESSION_ACCESS_TOKEN);
                            Session::del(GodoKakaoServerApi::SESSION_USER_PROFILE);
                            $memberSnsService = new MemberSnsService();
                            $memberSnsService->joinBySns($memberVO->getMemNo(), $kakaoProfile['id'], $kakaoToken['access_token'], 'kakao');
                        } elseif (Session::has(GodoWonderServerApi::SESSION_USER_PROFILE)) {
                            $wonderToken = Session::get(GodoWonderServerApi::SESSION_ACCESS_TOKEN);
                            $userProfile = Session::get(GodoWonderServerApi::SESSION_USER_PROFILE);
                            Session::del(GodoWonderServerApi::SESSION_ACCESS_TOKEN);
                            Session::del(GodoWonderServerApi::SESSION_USER_PROFILE);
                            $memberSnsService = new MemberSnsService();
                            $memberSnsService->joinBySns($memberVO->getMemNo(), $userProfile['mid'], $wonderToken['access_token'], 'wonder');
                            $wonderApi = new GodoWonderServerApi();
                            $wonderApi->logByJoin();
                        }
                        \DB::commit();
                    } catch (Exception $e) {
                        \DB::rollback();
                        if (get_class($e) == Exception::class) {
                            if ($e->getMessage()) {
                                $this->js("alert('".$e->getMessage()."');window.parent.callback_not_disabled();");
                            }
                        } else {
                            throw $e;
                        }
                    }
                    if ($memberVO != null) {
                        $smsAutoConfig = ComponentUtils::getPolicy('sms.smsAuto');
                        $kakaoAutoConfig = ComponentUtils::getPolicy('kakaoAlrim.kakaoAuto');
                        $kakaoLunaAutoConfig = ComponentUtils::getPolicy('kakaoAlrimLuna.kakaoAuto');
                        if (gd_is_plus_shop(PLUSSHOP_CODE_KAKAOALRIMLUNA) === true && $kakaoLunaAutoConfig['useFlag'] == 'y' && $kakaoLunaAutoConfig['memberUseFlag'] == 'y') {
                            $smsDisapproval = $kakaoLunaAutoConfig['member']['JOIN']['smsDisapproval'];
                        }else if ($kakaoAutoConfig['useFlag'] == 'y' && $kakaoAutoConfig['memberUseFlag'] == 'y') {
                            $smsDisapproval = $kakaoAutoConfig['member']['JOIN']['smsDisapproval'];
                        } else {
                            $smsDisapproval = $smsAutoConfig['member']['JOIN']['smsDisapproval'];
                        }
                        StringUtils::strIsSet($smsDisapproval, 'n');
                        $sendSmsJoin = ($memberVO->getAppFl() == 'n' && $smsDisapproval == 'y') || $memberVO->getAppFl() == 'y';
                        $mailAutoConfig = ComponentUtils::getPolicy('mail.configAuto');
                        $mailDisapproval = $mailAutoConfig['join']['join']['mailDisapproval'];
                        StringUtils::strIsSet($smsDisapproval, 'n');
                        $sendMailJoin = ($memberVO->getAppFl() == 'n' && $mailDisapproval == 'y') || $memberVO->getAppFl() == 'y';
                        if ($sendSmsJoin) {
                            /** @var \Bundle\Component\Sms\SmsAuto $smsAuto */
                            $smsAuto = \App::load('\\Component\\Sms\\SmsAuto');
                            $smsAuto->notify();
                        }
                        if ($sendMailJoin) {
                            $member->sendEmailByJoin($memberVO);
                        }
                        if (Session::has('pushJoin')) {
                            $memNo = $memberVO->getMemNo();
                            $memberData = $member->getMember($memNo, 'memNo', 'memNo, memId, appFl, groupSno, mileage');
                            $coupon = new Coupon();
                            $getData = $coupon->getMemberSimpleJoinCouponList($memNo);
                            $member->setSimpleJoinLog($memNo, $memberData, $getData, 'push');
                            Session::del('pushJoin');
                        }
                    }

                    // 에이스카운터 회원가입 스크립트
                    $acecounterScript = \App::load('\\Component\\Nhn\\AcecounterCommonScript');
                    $acecounterUse = $acecounterScript->getAcecounterUseCheck();
                    if ($acecounterUse) {
                        echo $acecounterScript->getJoinScript($memberVO->getMemNo());
                    }


                    // 평생회원 이벤트
                    if (Request::post()->get('expirationFl') === '999') {
                        $modifyEvent = \App::load('\\Component\\Member\\MemberModifyEvent');
                        $memberData = $member->getMember($memberVO->getMemNo(), 'memNo', 'memNo, memNm, mallSno, groupSno'); // 회원정보
                        $resultLifeEvent = $modifyEvent->applyMemberLifeEvent($memberData, 'life');
                        if (empty($resultLifeEvent['msg']) == false) {
                            $msg = 'alert("' . $resultLifeEvent['msg'] . '");';
                        }
                    }

                    $this->js($msg. 'parent.location.href=\'../member/join_ok.php\'');
                    break;
                case 'simpleJoin':
                    $memberVO = null;
                    try {
                        \DB::begin_tran();
                        Session::set('isFront', 'y');
                        Session::set('simpleJoin', 'y');
                        $data = Request::post()->toArray();
                        Request::post()->set('simpleJoinFl','order');
                        Request::post()->set('appFl','y');
                        $memberVO = $member->join(Request::post()->xss()->all());
                        Session::del('isFront');
                        \DB::commit();
                    } catch (\Throwable $e) {
                        \DB::rollback();
                        if (get_class($e) == Exception::class) {
                            if ($e->getMessage()) {
                                $this->json(['result' => 'false', 'message' => $e->getMessage()]);
                            }
                        } else {
                            throw $e;
                        }
                    }
                    if ($memberVO != null) {
                        $mailAutoConfig = ComponentUtils::getPolicy('mail.configAuto');
                        $mailDisapproval = $mailAutoConfig['join']['join']['mailDisapproval'];
                        StringUtils::strIsSet($smsDisapproval, 'n');
                        $sendMailJoin = ($memberVO->getAppFl() == 'n' && $mailDisapproval == 'y') || $memberVO->getAppFl() == 'y';
                        if ($sendMailJoin) {
                            $member->sendEmailByJoin($memberVO);
                        }

                        Request::post()->set('loginId',$data['email']);
                        Request::post()->set('loginPwd',$data['memPw']);
                        $member->login($data['email'], $data['memPw']);
                        $storage = new SimpleStorage(Request::post()->all());
                        MemberUtil::saveCookieByLogin($storage);

                        $memNo = $memberVO->getMemNo();
                        $memberData = $member->getMember($memNo, 'memNo', 'memNo, memId, appFl, groupSno, mileage');
                        $coupon = new Coupon();
                        $getData = $coupon->getMemberSimpleJoinCouponList($memNo, null, 'c.couponBenefitType ASC, c.couponBenefit DESC, c.regDt DESC');
                        $member->setSimpleJoinLog($memNo, $memberData, $getData, 'order');
                        $couponCnt = count($getData);
                        if($couponCnt == 1) {
                            $c = '쿠폰: ['.$getData[0]['couponNm'].']';
                        } else if($couponCnt > 1) {
                            $c = '쿠폰: ['.$getData[0]['couponNm'].'] 외 '.($couponCnt - 1).'장';
                        } else {
                            $c = 'false';
                        }
                        $this->json(['result' => 'true', 'mileage' => $memberData['mileage'], 'coupon' => $c]);
                    } else {
                        $this->json(['result' => 'false', 'message' => '요청을 찾을 수 없습니다.']);
                    }
                    exit;
                    break;
                // 이메일중복확인
                case 'overlapEmail':
                    $memId = Request::post()->get('memId');
                    if (\App::load('Component\\Member\\Util\\MemberUtil')->simpleOverlapEmail(Request::post()->get('email'), $memId) === false) {
                        $this->json(__('사용가능한 이메일입니다.'));
                    } else {
                        throw new Exception(__("이미 등록된 이메일 주소입니다.") . " " . __("다른 이메일 주소를 입력해 주세요."));
                    }
                    break;
                // 아이디중복확인
                case 'overlapMemId':
                    $memId = Request::post()->get('memId');
                    if (MemberUtil::overlapMemId($memId) === false) {
                        $this->json(__("사용가능한 아이디입니다."));
                    } else {
                        throw new Exception(__("이미 등록된 아이디입니다.") . " " . __("다른 아이디를 입력해 주세요."));
                    }
                    break;

                // 닉네임중복확인
                case 'overlapNickNm':
                    $memId = Request::post()->get('memId');
                    $nickNm = Request::post()->get('nickNm');

                    if (MemberUtil::overlapNickNm($memId, $nickNm) === false) {
                        $this->json(__('사용가능한 닉네임입니다.'));
                    } else {
                        throw new Exception(__("이미 등록된 닉네임입니다. 다른 닉네임을 입력해 주세요."));
                    }
                    break;
                case 'overlapBusiNo':
                    $memId = Request::post()->get('memId');
                    $busiNo = Request::post()->get('busiNo');
                    $busiLength = Request::post()->get('charlen');

                    if (strlen($busiNo) != $busiLength) {
                        throw new Exception(sprintf(__("사업자번호는 %s자로 입력해야 합니다."), $busiLength));
                    }
                    if (MemberUtil::overlapBusiNo($memId, $busiNo) === false) {
                        $this->json(__("사용가능한 사업자번호입니다."));
                    } else {
                        throw new Exception(__("이미 등록된 사업자번호입니다."));
                    }
                    break;
                case 'checkRecommendId':
                    // 추천인 아이디 체크
                    if (MemberUtil::checkRecommendId(Request::post()->get('recommId'), Request::post()->get('memId'))) {
                        $this->json(__('아이디가 정상적으로 확인되었습니다.'));
                    } else {
                        throw new Exception(__('추천인 아이디를 다시 확인해 주세요.'));
                    }
                    break;
                case 'validateMemberPassword':
                    // 비밀번호 검증
                    $memberPassword = Request::post()->get('memPw');
                    $result = MemberValidation::validateMemberPassword($memberPassword);
                    $this->json($result);
                    break;
                case 'under14Download':
                    // 14세 미만 가입동의서 다운로드
                    $downloadPath = Storage::disk(Storage::PATH_CODE_COMMON)->getDownLoadPath('under14sample.docx');
                    $this->download($downloadPath, __('만14세미만회원가입동의서(샘플).docx'));
                    break;
                case 'setSimpleJoinPushClosed':
                    Session::set('joinEventPush.joinEventPushClose', 'y');
                    break;
                case 'setSimpleJoinPushLog':
                    Session::set('pushJoin', 'y');
                    $eventType = Request::post()->get('eventType');
                    $member->setSimpleJoinPushLog($eventType);
                    break;
                default:
                    /** @var \Bundle\Controller\Front\Member\MemberPsController $front */
                    $front = \App::load('\\Controller\\Front\\Member\\MemberPsController');
                    $front->index();
                    break;
            }
        } catch (DatabaseException $e) {
            if ($e->getCode() == '1062') {
                throw new AlertBackException('이미 등록된 회원입니다.', $e->getCode(), $e);
            } else {
                throw new AlertBackException($e->getCode(), $e->getCode(), $e);
            }
        } catch (Exception $e) {
            \Logger::error(__METHOD__ . ', ' . $e->getFile() . '[' . $e->getLine() . '], ' . $e->getMessage(), $e->getTrace());
            if (Request::isAjax() === true) {
                $this->json($this->exceptionToArray($e));
            } else {
                throw new AlertBackException($e->getMessage());
            }
        }
    }
}
