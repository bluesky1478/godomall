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
namespace Bundle\Controller\Front\Member;

use Bundle\Component\Godo\GodoKakaoServerApi;
use Bundle\Component\Policy\KakaoLoginPolicy;
use Component\Agreement\BuyerInform;
use Component\Agreement\BuyerInformCode;
use Component\Godo\GodoPaycoServerApi;
use Component\Godo\GodoNaverServerApi;
use Component\Godo\GodoWonderServerApi;
use Component\Mall\Mall;
use Component\Policy\PaycoLoginPolicy;
use Component\Policy\NaverLoginPolicy;
use Component\Policy\WonderLoginPolicy;
use Component\Policy\SnsLoginPolicy;
use Framework\Security\Token;
use Request;
use Session;
use Framework\Utility\StringUtils;

/**
 * Class JoinAgreementController
 * @package Bundle\Controller\Front\Member
 * @author  yjwee
 */
class JoinAgreementController extends \Controller\Front\Controller
{
    public function index()
    {
        $getValue = \Request::get()->all();
        Session::del(GodoPaycoServerApi::SESSION_USER_PROFILE);
        Session::del(GodoNaverServerApi::SESSION_USER_PROFILE);
        Session::del(GodoKakaoServerApi::SESSION_USER_PROFILE);
        Session::del(GodoWonderServerApi::SESSION_USER_PROFILE);

        //SNS 회원 가입을 진행중인 경우
        //본인 인증 노출 여부를 위한 체크값 (아이핀/휴대폰 본인인증)
        $snsMemberAuthFl    = 'y';
        $chkSNSMemberAuthFl = \Component\Member\MemberValidation::checkSNSMemberAuth();

        if (Session::has(GodoPaycoServerApi::SESSION_ACCESS_TOKEN)) {
            $paycoToken = Session::get(GodoPaycoServerApi::SESSION_ACCESS_TOKEN);
            $paycoApi = new GodoPaycoServerApi();
            $userProfile = $paycoApi->getUserProfile($paycoToken['access_token']);
            Session::set(GodoPaycoServerApi::SESSION_USER_PROFILE, $userProfile);
            $snsMemberAuthFl = $chkSNSMemberAuthFl;
        }

        $naverLoginPolicy = gd_policy('member.naverLogin');
        if (Session::has(GodoNaverServerApi::SESSION_ACCESS_TOKEN) && $naverLoginPolicy['useFl'] === 'y') {
            $naverToken = Session::get(GodoNaverServerApi::SESSION_ACCESS_TOKEN);
            $naverApi = new GodoNaverServerApi();
            $userProfile = $naverApi->getUserProfile($naverToken);
            Session::set(GodoNaverServerApi::SESSION_USER_PROFILE, $userProfile);
            $snsMemberAuthFl = $chkSNSMemberAuthFl;
        }

        $kakaoLoginPolicy = gd_policy('member.kakaoLogin');
        if (empty(Session::has(GodoKakaoServerApi::SESSION_ACCESS_TOKEN)) === false && $kakaoLoginPolicy['useFl'] === 'y') {
            $kakaoToken = Session::get(GodoKakaoServerApi::SESSION_ACCESS_TOKEN);
            $kakaoApi = new GodoKakaoServerApi();
            $kakaoApi->appLink($kakaoToken['access_token']);
            $userInfo = $kakaoApi->getUserInfo($kakaoToken['access_token']);
            Session::set(GodoKakaoServerApi::SESSION_USER_PROFILE, $userInfo);
            $snsMemberAuthFl = $chkSNSMemberAuthFl;
        }

        $wonderLoginPolicy = gd_policy('member.wonderLogin');
        if (empty(Session::has(GodoWonderServerApi::SESSION_ACCESS_TOKEN)) === false && $wonderLoginPolicy['useFl'] === 'y' && Request::get()->get('joinType') === 'wonder') {
            $snsMemberAuthFl = $chkSNSMemberAuthFl;
            $siteLink = new \Component\SiteLink\SiteLink();
            $this->setData('joinActionUrl', $siteLink->link('../member/member_ps.php', 'ssl'));
            $wonderToken = Session::get(GodoWonderServerApi::SESSION_ACCESS_TOKEN);
            $wonderApi = new GodoWonderServerApi();
            $userProfile = $wonderApi->getUserProfile($wonderToken);
            $userProfile['name'] = str_replace(' ', '', $userProfile['name']);
            $userProfile['sexFl'] = $userProfile['gender'] == '2' ? 'w' : 'm';
            Session::set(GodoWonderServerApi::SESSION_USER_PROFILE, $userProfile);
            $this->setData('terms', $wonderApi->getTerms());
            $this->setData('memberInfo', $userProfile);
            $this->setData('emailAsterisk', StringUtils::mask($userProfile['email'], 3, strpos($userProfile['email'], '@') -3));
            $this->getView()->setPageName('member/join_agreement_sns');
        }

        $inform = new BuyerInform();
        $mall = new Mall();
        $serviceInfo = $mall->getServiceInfo();
        $agreementInfo = $inform->getAgreementWithReplaceCode(BuyerInformCode::AGREEMENT);
        $privateApproval = $inform->getInformData(BuyerInformCode::PRIVATE_APPROVAL);
        $privateApprovalOption = $inform->getInformDataArray(BuyerInformCode::PRIVATE_APPROVAL_OPTION);
        $privateConsign = $inform->getInformDataArray(BuyerInformCode::PRIVATE_CONSIGN);
        $privateOffer = $inform->getInformDataArray(BuyerInformCode::PRIVATE_OFFER);
        $ipinConfig = gd_policy('member.ipin');
        $authCellPhoneConfig = gd_get_auth_cellphone_info();
        $memberJoinConfig = gd_policy('member.join');

        $snsLoginPolicy = new SnsLoginPolicy();
        $paycoPolicy = new PaycoLoginPolicy();
        $naverPolicy = new NaverLoginPolicy();
        $kakaoPolicy = new KakaoLoginPolicy();

        //facebook login check
        if ($snsLoginPolicy->useGodoAppId() && Session::has(\Component\Facebook\Facebook::SESSION_METADATA)) {
            $snsMemberAuthFl = $chkSNSMemberAuthFl;
        } elseif (Session::has(\Component\Facebook\Facebook::SESSION_METADATA) && Session::has(\Component\Facebook\Facebook::SESSION_ACCESS_TOKEN)) {
            $snsMemberAuthFl = $chkSNSMemberAuthFl;
        }

        //SNS 회원 가입을 진행중이고
        //본인 인증을 노출하지 않을 경우 아이핀/휴대폰 본인인증의 상태값을 미사용(n)으로 변경함.
        if ($snsMemberAuthFl === 'n') {
            $ipinConfig['useFl']          = 'n';
            $authCellPhoneConfig['useFl'] = 'n';
        }

        // 만 14세 이상 동의 항목
        $joinPolicy = gd_policy('member.join');
        $this->setData('under14ConsentFl', $joinPolicy['under14ConsentFl']);

        $this->setData('useThirdParty', $snsLoginPolicy->useFacebook() || $paycoPolicy->usePaycoLogin() || $naverPolicy->useNaverLogin() || $kakaoPolicy->useKakaoLogin());
        $this->setData('token', Token::generate('token'));
        $this->setData('authDataCpCode', $authCellPhoneConfig['cpCode']);
        $this->setData('domainUrl', Request::getDomainUrl());
        $this->setData('authCellPhoneConfig', $authCellPhoneConfig);
        $this->setData('memberJoinConfig', $memberJoinConfig);
        $this->setData('ipinConfig', $ipinConfig);
        $this->setData('serviceInfo', $serviceInfo);
        $this->setData('agreementInfo', $agreementInfo);
        $this->setData('privateApproval', $privateApproval);
        $this->setData('privateApprovalOption', $privateApprovalOption);
        $this->setData('privateConsign', $privateConsign);
        $this->setData('privateOffer', $privateOffer);


        //        debug($memberJoinConfig);
        //        debug($agreementInfo);
        //        debug($privateConsign);
    }
}
