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
use Framework\Debug\Exception\AlertRedirectException;
use Request;
use Session;
use Framework\Security\Token;

/**
 * Class 본인인증 인증번호 확인
 * @package Bundle\Controller\Front\Member
 * @author  yjwee
 */
class UserCertificationConfirmController extends \Controller\Front\Controller
{
    /**
     * @inheritdoc
     */
    public function index()
    {
        $userCertificationSession = Session::get(Member::SESSION_USER_CERTIFICATION);

        if (empty($userCertificationSession['authType']) === true) {
            throw new AlertRedirectException(__('인증에 필요한 정보가 없습니다.'), null, null, '../member/login.php');
        }

        // 인증 검증 시간 추가
        if ($userCertificationSession['limitTime'] < time()) {
            $requestAuth = 'false';
        }

        $this->addScript([
            'jquery/jquery.countdownTimer.js',
        ]);

        $this->addCss([
            'plugins/jquery.countdownTimer.css'
        ]);

        /**
        *   set view data
        */
        $this->setData('token', Token::generate('token'));
        $this->setData('authType', $userCertificationSession['authType']);
        $this->setData('certificationType', $userCertificationSession['certificationType']);
        $this->setData('boxHeader', __('본인인증'));
        $this->setData('requestAuth', gd_isset($requestAuth, 'true'));
        if ($userCertificationSession['certificationType'] === 'find_password') {
            $this->setData('boxHeader', __('비밀번호 찾기'));
        }
    }
}
