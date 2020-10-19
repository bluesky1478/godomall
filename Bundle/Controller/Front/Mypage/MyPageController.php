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
namespace Bundle\Controller\Front\Mypage;

use Bundle\Component\Godo\GodoKakaoServerApi;
use Bundle\Component\Policy\KakaoLoginPolicy;
use Bundle\Component\Godo\GodoWonderServerApi;
use Component\Agreement\BuyerInform;
use Component\Agreement\BuyerInformCode;
use Component\Facebook\Facebook;
use Component\Member\Member;
use Component\Member\MyPage;
use Component\Member\Util\MemberUtil;
use Component\Policy\PaycoLoginPolicy;
use Component\Policy\NaverLoginPolicy;
use Component\Policy\WonderLoginPolicy;
use Component\Policy\SnsLoginPolicy;
use Component\SiteLink\SiteLink;
use Exception;
use Framework\Debug\Exception\AlertBackException;
use Framework\Debug\Exception\AlertRedirectException;
use Framework\Utility\ArrayUtils;
use Request;
use Session;


/**
 * Class MyPageController
 * @package Bundle\Controller\Front\Mypage
 * @author  yjwee
 */
class MyPageController extends \Controller\Front\Controller
{
    public function index()
    {
        $request = \App::getInstance('request');
        $session = \App::getInstance('session');
        $locale = \Globals::get('gGlobal.locale');
        $scripts = [
            'moment/moment.js',
            'moment/locale/' . $locale . '.js',
            'jquery/datetimepicker/bootstrap-datetimepicker.js',
            'gd_member2.js',
            'gd_payco.js',
            'gd_naver.js',
            'gd_kakao.js',
            'gd_wonder.js',
        ];

        $styles = [
            'plugins/bootstrap-datetimepicker.min.css',
            'plugins/bootstrap-datetimepicker-standalone.css',
        ];

        try {
            $referer = $request->getReferer();
            $uri = $request->getRequestUri();

            // 비밀번호 검증 후 reload 시에는 검증하지 않기 위한 조건
            if ($session->has(MyPage::SESSION_MY_PAGE_PASSWORD) === false || $session->get(MyPage::SESSION_MY_PAGE_PASSWORD, false) === false) {
                if (strpos($referer, $uri) === false) {
                    $session->del(MyPage::SESSION_MY_PAGE_PASSWORD);
                    $session->del(MyPage::SESSION_MY_PAGE_MEMBER_NO);
                    throw new AlertRedirectException(__('비밀번호 검증이 필요합니다.'), 401, null, '../mypage/my_page_password.php', 'top');
                }
            }

            $snsLoginPolicy = new SnsLoginPolicy();
            $paycoPolicy = new PaycoLoginPolicy();
            $naverPolicy = new NaverLoginPolicy();
            $kakaoPolicy = new KakaoLoginPolicy();
            $wonderPolicy = new WonderLoginPolicy();
            $myPage = new MyPage();
            $inform = new BuyerInform();
            $siteLink = new SiteLink();

            $mallSno = \SESSION::get(SESSION_GLOBAL_MALL)['sno'] ? \SESSION::get(SESSION_GLOBAL_MALL)['sno'] : DEFAULT_MALL_NUMBER;
            $joinField = MemberUtil::getJoinField($mallSno, 'mypage');
            $joinField['memPw']['require'] = 'n';
            $memberData = $myPage->myInformation();
            //회원 번호를 세션으로 인증 하도록 처리
            Session::set(MyPage::SESSION_MY_PAGE_MEMBER_NO, $memberData['memNo']);

            if ($memberData['memberFl'] != 'business') {
                $joinField['businessinfo']['use'] = 'n';
            }

            $snsConnection = $memberData;
            //@formatter:off
            ArrayUtils::unsetDiff($snsConnection, ['snsJoinFl', 'snsTypeFl']);
            //@formatter:on

            $useFacebook = $snsLoginPolicy->useFacebook();
            $isConnectFacebook = $memberData['snsTypeFl'] == SnsLoginPolicy::FACEBOOK;
            if ($useFacebook) {
                $scripts[] = 'gd_sns.js';
                $facebook = new Facebook();
                if ($isConnectFacebook) {
                    $this->setData('facebookUrl', '../member/facebook/dis_connect.php');
                } else {
                    if ($snsLoginPolicy->useGodoAppId()) {
                        $this->setData('facebookUrl', $facebook->getGodoConnectUrl());
                    } else {
                        $this->setData('facebookUrl', $facebook->getConnectUrl());
                    }
                }
            }

            $useKakao = $kakaoPolicy->useKakaoLogin();
            $isConnectKakao = $memberData['snsTypeFl'] == KakaoLoginPolicy::KAKAO;
            if($useKakao){
                if($isConnectKakao){
                    $this->setData('kakaoUrl', '../member/kakao/kakao_login.php?kakaoType=disconnect');
                }
            }

            $memberSession = $session->get(Member::SESSION_MEMBER_LOGIN);
            $isEmptyPassword = isset($memberSession['memPw']) == false && $memberSession['snsJoinFl'] == 'y';

            $countries = \Component\Mall\MallDAO::getInstance()->selectCountries();
            $countryPhone = [];
            foreach ($countries as $key => $val) {
                if ($val['callPrefix'] > 0) {
                    if ($session->has(SESSION_GLOBAL_MALL)) {
                        $countryPhone[$val['code']] = __($val['countryName']) . '(+' . $val['callPrefix'] . ')';
                    } else {
                        $countryPhone[$val['code']] = __($val['countryNameKor']) . '(+' . $val['callPrefix'] . ')';
                    }
                }
            }
            $DateYear = [];
            $DateMonth = [];
            $DateDay = [];
            $startYear = (int)date("Y");
            $endYear = 1900;
            $fixFront = '';
            for ($i=$startYear; $i>=$endYear; $i--) {
                $DateYear[$i] = $i;
            }
            for ($j=1; $j<=12; $j++) {
                if ($j < 10) {
                    $fixFront = 0;
                }
                $DateMonth[$fixFront.$j] = $fixFront.$j;
                $fixFront = '';
            }
            for ($k=1; $k<=31; $k++) {
                if ($k < 10) {
                    $fixFront = 0;
                }
                $DateDay[$fixFront.$k] = $fixFront.$k;
                $fixFront = '';
            }

            //생일, 결혼기념이 년,월,일 나누기
            if (isset($memberData['birthDt']) === true) {
                $memberData['birthYear'] = substr($memberData['birthDt'], 0, 4);
                $memberData['birthMonth'] = substr($memberData['birthDt'], 4, 2);
                $memberData['birthDay'] = substr($memberData['birthDt'], 6, 2);
            }
            if (isset($memberData['marriDate']) === true) {
                $memberData['marriYear'] = substr($memberData['marriDate'], 0, 4);
                $memberData['marriMonth'] = substr($memberData['marriDate'], 4, 2);
                $memberData['marriDay'] = substr($memberData['marriDate'], 6, 2);
            }

            // 인증설정체크해서 데이터 추가 useDataJoinFl
            $authCellPhoneConfig = gd_get_auth_cellphone_info();
            if ($authCellPhoneConfig['useFl'] == 'y' && ($authCellPhoneConfig['useDataModifyFl'] == 'y' || $authCellPhoneConfig['useDataModifyFlKcp'] == 'y')) {
                $memberData['authReadOnly'] = ' readonly';    //readonly처리
                $memberData['authDisabled'] = ' disabled';    //readonly처리
                $memberData['authRequired'] = ' required';    //필수클래스값처리
                $memberData['authClassRequired'] = ' class="important"';    //필수클래스값처리
            }

            if ($wonderPolicy->useWonderLogin()) {
                $wonder = new GodoWonderServerApi();
                $this->setData('wonderReturnUrl', $wonder->getAuthUrl('connect'));
            }

            // 평생회원 이벤트
            $modifyEvent = \App::load('\\Component\\Member\\MemberModifyEvent');
            $activeEvent = $modifyEvent->getActiveMemberModifyEvent($memberData['mallSno'], 'life');
            $memberLifeEventCnt = $modifyEvent->checkDuplicationModifyEvent($activeEvent['sno'], $memberData['memNo'], 'life');
            $getMemberLifeEventCount = $modifyEvent->getMemberLifeEventCount($memberData['memNo']);
            $getMemberLifeEventAdminCount = $modifyEvent->getMemberLifeEventAdminCount($memberData['memNo']);

            if ($getMemberLifeEventCount > 0) {
                $this->setData('memberLifeEventView', 'hidden');
            }

            // 관리자가 평생회원 > 1,3,5년 수정시에는 노출
            if ($getMemberLifeEventAdminCount > 0 && $memberData['expirationFl'] != '999') {
                unset($getMemberLifeEventCount);
            }

            $couponConfig = gd_policy('coupon.config'); // 쿠폰 설정값 정보
            $mileageBasic = gd_policy('member.mileageBasic'); // 마일리지 사용 여부
            $this->setData('activeEvent', $activeEvent);
            if (count($activeEvent) > 0) {
                if ($activeEvent['benefitType'] == 'coupon' && $couponConfig['couponUseType'] === 'y') {
                    // --- 모듈 호출
                    $coupon = \App::load('\\Component\\Coupon\\Coupon');
                    if ($coupon->checkCouponType($activeEvent['benefitCouponSno'])) {
                        $couponData = $modifyEvent->getDataByTable(DB_COUPON, $activeEvent['benefitCouponSno'], 'couponNo', 'couponNm');
                        $this->setData('benefitInfo', gd_htmlspecialchars_stripslashes($couponData['couponNm']).'쿠폰');
                    } else {
                        $this->setData('memberLifeEventView', 'hidden');
                    }
                } else if ($activeEvent['benefitType'] == 'mileage' && $mileageBasic['payUsableFl'] === 'y') {
                    $this->setData('benefitInfo', (int)$activeEvent['benefitMileage'].'원 ' . gd_display_mileage_name());
                }
                $this->setData('benefitType', $activeEvent['benefitType']);
                $this->setData('memberLifeEventCnt', $memberLifeEventCnt); // 회원 평생회원 참여횟수
                $this->setData('getMemberLifeEventCount', $getMemberLifeEventCount); // 회원 평생회원 참여횟수 (로그 히스토리 내역조회)
            }

            $this->setData('countryPhone', $countryPhone);
            $this->setData('DateYear', $DateYear);
            $this->setData('DateMonth', $DateMonth);
            $this->setData('DateDay', $DateDay);

            $this->setData('mypageActionUrl', $siteLink->link('../mypage/my_page_ps.php', 'ssl'));
            $this->setData('isMyPage', true);
            $this->setData('data', $memberData);
            $this->setData('joinField', $joinField);
            $this->setData('privateApprovalOption', $inform->getInformDataArray(BuyerInformCode::PRIVATE_APPROVAL_OPTION));
            $this->setData('privateOffer', $inform->getInformDataArray(BuyerInformCode::PRIVATE_OFFER));
            $this->setData('privateConsign', $inform->getInformDataArray(BuyerInformCode::PRIVATE_CONSIGN));
            $this->setData('snsConnection', json_encode($snsConnection));
            $this->setData('usePaycoLogin', $paycoPolicy->usePaycoLogin());
            $this->setData('useNaverLogin', $naverPolicy->useNaverLogin());
            $this->setData('useWonderLogin', $wonderPolicy->useWonderLogin());
            $this->setData('useFacebookLogin', $useFacebook);
            $this->setData('useKakaoLogin', $useKakao);
            $this->setData('useSnsLogin', gd_isset($memberData['snsTypeFl'], 'n'));
            $this->setData('connectPayco', $memberData['snsTypeFl'] == PaycoLoginPolicy::PAYCO);
            $this->setData('connectNaver', $memberData['snsTypeFl'] == NaverLoginPolicy::NAVER);
            $this->setData('connectWonder', $memberData['snsTypeFl'] == WonderLoginPolicy::WONDER);
            $this->setData('connectFacebook', $isConnectFacebook);
            $this->setData('connectKakao', $isConnectKakao);
            $this->setData('isEmptyPassword', $isEmptyPassword);
            $this->setData('authDataCpCode', $authCellPhoneConfig['cpCode']);
            $this->setData('domainUrl', Request::getDomainUrl());
            $this->setData('authCellPhoneConfig', $authCellPhoneConfig);
            $this->addScript($scripts);
            $this->addCss($styles);
            $emailDomain = gd_array_change_key_value(gd_code('01004'));
            $emailDomain = array_merge(['self' => __('직접입력')], $emailDomain);
            $this->setData('emailDomain', $emailDomain); // 메일주소 리스팅
        } catch (Exception $e) {
            throw new AlertBackException($e->getMessage(), $e->getCode(), $e);
        }
    }
}
