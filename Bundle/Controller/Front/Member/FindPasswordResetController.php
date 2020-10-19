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

use Component\Member\Member;
use Component\Member\Util\MemberUtil;
use Framework\Application\Bootstrap\Log;
use Framework\Debug\Exception\AlertRedirectException;
use Framework\Security\Token;
use Framework\Utility\ComponentUtils;
use Request;
use Session;

/**
 * Class FindPasswordResetController
 * @package Bundle\Controller\Front\Member
 * @author  yjwee
 */
class FindPasswordResetController extends \Controller\Front\Controller
{
    public function index()
    {
        $session = \App::getInstance('session');
        $logger = \App::getInstance('logger');
        $globals = \App::getInstance('globals');

        /** @var \Bundle\Controller\Front\Controller $this */
        $userCertification = $session->get(Member::SESSION_USER_CERTIFICATION);

        if ($session->has(Member::SESSION_DREAM_SECURITY)) {
            $dream = $session->get(Member::SESSION_DREAM_SECURITY);
            $isAuthCellPhone = $userCertification['rncheck'] == 'authCellphone';
            $isDupeInfo = $userCertification['dupeinfo'] == $dream['DI'];

            if (!($isAuthCellPhone && $isDupeInfo)) {
                $session->del(Member::SESSION_USER_CERTIFICATION);
                $session->del(Member::SESSION_DREAM_SECURITY);

                $logger->channel(Log::CHANNEL_DREAMSECURITY)->error(
                    'isAuthCellPhone[' . $isAuthCellPhone . '], isDupeInfo[' . $isDupeInfo . ']', [
                        $userCertification['rncheck'],
                        $userCertification['dupeinfo'],
                        $dream['DI'],
                    ]
                );

                throw new AlertRedirectException(__('휴대폰인증 정보가 다릅니다.'), 200, null, '../member/login.php');
            }
        } else if ($session->has(Member::SESSION_IPIN)) {
            $ipin = $session->get(Member::SESSION_IPIN);
            $isIpin = $userCertification['rncheck'] == 'ipin';
            $isDupeInfo = $userCertification['dupeinfo'] == $ipin['dupInfo'];

            if (!($isIpin && $isDupeInfo)) {
                $session->del(Member::SESSION_USER_CERTIFICATION);
                $session->del(Member::SESSION_IPIN);

                $logger->channel(Log::CHANNEL_IPIN)->error(
                    'isAuthCellPhone[' . $isIpin . '], isDupeInfo[' . $isDupeInfo . ']', [
                        $userCertification['rncheck'],
                        $userCertification['dupeinfo'],
                        $ipin['dupInfo'],
                    ]
                );

                throw new AlertRedirectException(__('아이핀 인증 정보가 다릅니다.'), 200, null, '../member/login.php');
            }
        } else {
            // 보안 취약점 개선요청 사항
            if ($session->get('certificationFindPassword') != 'y') {
                throw new AlertRedirectException(__('잘못된 접근입니다.'), 200, null, '../member/login.php');
            }
        }

        $joinField = MemberUtil::getJoinField();

        $this->setData('joinField', gd_isset($joinField));
        $this->setData('useField', gd_isset($useField));
        $this->setData('ipinFl', ComponentUtils::useIpin());
        $this->setData('authCellphoneFl', ComponentUtils::useAuthCellphone());
        $this->setData('authShopUrl', URI_AUTH_PHONE_MODULE);
        $this->setData('authDataCpCode', ComponentUtils::getAuthCellphone());
        $this->addScript(['gd_member2.js']);
    }
}
