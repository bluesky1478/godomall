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

use Component\Member\MemberSleep;
use Framework\Debug\Exception\AlertRedirectException;
use Framework\Utility\ArrayUtils;
use Framework\Utility\StringUtils;
use Session;

/**
 * Class WakeController
 * @package Bundle\Controller\Front\Member
 * @author  yjwee
 */
class WakeCertificationController extends \Controller\Front\Controller
{
    /**
     * @inheritdoc
     */
    public function index()
    {
        if (!Session::has(MemberSleep::SESSION_WAKE_INFO)) {
            throw new AlertRedirectException(__('휴면회원 해제에 필요한 정보를 찾을 수 없습니다.'), 401, null, '../member/login.php');
        }
        $wakeInfo = Session::get(MemberSleep::SESSION_WAKE_INFO);
        ArrayUtils::unsetDiff(
            $wakeInfo, [
                'sleepDt',
                'entryDt',
                'lastLoginDt',
                'authType',
                'cellPhone',
                'email',
            ]
        );
        if (StringUtils::strIsSet($wakeInfo['email'], '') !== '') $maskEmail = StringUtils::mask($wakeInfo['email'], 3, 6);
        if (StringUtils::strIsSet($wakeInfo['cellPhone'], '') !== '') $maskCellphone = StringUtils::mask($wakeInfo['cellPhone'], 2, 5);

        $this->setData('authType', \Request::get()->get('authType', ''));
        $this->setData('memberInfo', $wakeInfo);
        $this->setData('maskEmail', $maskEmail);
        $this->setData('maskCellphone', $maskCellphone);
    }
}
