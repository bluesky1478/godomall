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
 * @link http://www.godo.co.kr
 */

namespace Bundle\Controller\Front\Member;


use Component\Member\Member;
use Framework\Security\Token;
use Framework\Utility\ComponentUtils;
use Framework\Utility\StringUtils;
use Globals;
use Request;
use Session;

/**
 * Class UserCertificationController
 * @package Bundle\Controller\Front\Member
 * @author  yjwee
 */
class UserCertificationController extends \Controller\Front\Controller
{
    public function index()
    {
        $userCertificationSession = Session::get(Member::SESSION_USER_CERTIFICATION);

        $smsFl = ComponentUtils::isPasswordAuthSms();
        $emailFl = ComponentUtils::isPasswordAuthEmail();

        $this->setData('emailFl', $emailFl);
        if ($emailFl) {
            $maskEmail = StringUtils::mask($userCertificationSession['email'], 3, 6);
            $this->setData('email', empty($maskEmail) ? __('등록된 이메일이 없습니다.') : $maskEmail);
        }

        $this->setData('smsFl', $smsFl);
        if ($smsFl) {
            $maskCellphone = StringUtils::mask($userCertificationSession['cellPhone'], 2, 5);
            $this->setData('cellphone', empty($maskCellphone) ? __('등록된 휴대폰번호가 없습니다.') : $maskCellphone);
        }

        $this->setData('boxHeader', __('본인인증'));
        $this->setData('token', Token::generate('token'));
        $this->setData('domaiUrl', Request::getDomainUrl());
        $this->setData('ipinFl', ComponentUtils::useIpin());
        $this->setData('authCellphoneFl', ComponentUtils::useAuthCellphone());
        $this->setData('authShopUrl', URI_AUTH_PHONE_MODULE);
        $this->setData('authDataCpCode', ComponentUtils::getAuthCellphone());

        if (strpos(Request::getReferer(), 'find_password') > -1) {
            $userCertificationSession['certificationType'] = 'find_password';
            $this->setData('boxHeader', __('비밀번호 찾기'));
        }
        Session::set(Member::SESSION_USER_CERTIFICATION, $userCertificationSession);
    }
}
