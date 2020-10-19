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

use App;
use Component\Member\Member;
use Component\Member\MemberSleep;
use Exception;
use Framework\Debug\Exception\AlertRedirectException;
use Framework\Utility\ArrayUtils;
use Framework\Utility\ComponentUtils;
use Framework\Utility\DateTimeUtils;
use Framework\Utility\SkinUtils;
use Framework\Utility\StringUtils;
use Globals;
use Request;
use Session;

/**
 * Class WakeController
 * @package Bundle\Controller\Front\Member
 * @author  yjwee
 */
class WakeController extends \Controller\Front\Controller
{
    public function index()
    {
        try {
            if (!Session::has(MemberSleep::SESSION_WAKE_INFO)) {
                throw new AlertRedirectException(__('휴면회원 해제에 필요한 정보를 찾을 수 없습니다.'), 401, null, '../member/login.php');
            }
            $sleepPolicy = ComponentUtils::getPolicy('member.sleep');
            $wakeInfo = Session::get(MemberSleep::SESSION_WAKE_INFO);

            /** @var \Bundle\Component\Member\MemberSleep $memberSleep */
            $memberSleep = App::load('\\Component\\Member\\MemberSleep');

            $sleepData = $memberSleep->getSleepInfoByMemberId($wakeInfo['memId']);
            $memberDecrypt = $memberSleep->getSleepInfoByMemberIdWithDecrypt($sleepData);
            $result = '';

            if (Session::has(Member::SESSION_DREAM_SECURITY)) {
                $result = 'ERROR';
                $dream = Session::get(Member::SESSION_DREAM_SECURITY);
                if ($memberDecrypt['rncheck'] == 'authCellphone' || $memberDecrypt['dupeinfo'] == $dream['DI']) {
                    Session::del(Member::SESSION_DREAM_SECURITY);
                    $sleepPolicy['wakeType'] = 'normal';
                    $result = 'AUTH';
                }
            } else if (Session::has(Member::SESSION_IPIN)) {
                $result = 'ERROR';
                $ipin = Session::get(Member::SESSION_IPIN);
                if ($memberDecrypt['rncheck'] == 'ipin' || $memberDecrypt['dupeinfo'] == $ipin['dupInfo']) {
                    Session::del(Member::SESSION_IPIN);
                    $sleepPolicy['wakeType'] = 'normal';
                    $result = 'AUTH';
                }
            }

            $memberDecrypt['sleepDt'] = DateTimeUtils::dateFormat(__('Y년 m월 d일'), $memberDecrypt['sleepDt']);
            $memberDecrypt['entryDt'] = DateTimeUtils::dateFormat(__('Y년 m월 d일'), $memberDecrypt['entryDt']);
            $memberDecrypt['lastLoginDt'] = DateTimeUtils::dateFormat(__('Y년 m월 d일'), $memberDecrypt['lastLoginDt']);

            Session::set(
                MemberSleep::SESSION_WAKE_INFO, [
                    'sleepNo'     => $memberDecrypt['sleepNo'],
                    'memNo'       => $memberDecrypt['memNo'],
                    'email'       => gd_isset($memberDecrypt['email'], $sleepData['email']),
                    'cellPhone'   => gd_isset($memberDecrypt['cellPhone'], $sleepData['cellPhone']),
                    'sleepDt'     => $memberDecrypt['sleepDt'],
                    'entryDt'     => $memberDecrypt['entryDt'],
                    'lastLoginDt' => $memberDecrypt['lastLoginDt'],
                    'memId'       => $wakeInfo['memId'],
                    'memPw'       => $wakeInfo['memPw'],
                ]
            );

            $maskEmail = __('등록된 이메일이 없습니다.');
            $maskCellphone = __('등록된 휴대폰번호가 없습니다.');

            if ($sleepPolicy['wakeType'] == 'info' || $sleepPolicy['wakeType'] == 'auth') {
                if (StringUtils::strIsSet($memberDecrypt['email'], '') !== '') $maskEmail = StringUtils::mask($memberDecrypt['email'], 3, 6);
                if (StringUtils::strIsSet($memberDecrypt['cellPhone'], '') !== '') $maskCellphone = StringUtils::mask($memberDecrypt['cellPhone'], 2, 5);
            }

            $mailDomain = SkinUtils::getMailDomain(true);

            ArrayUtils::unsetDiff(
                $memberDecrypt, [
                    'sleepDt',
                    'entryDt',
                    'lastLoginDt',
                ]
            );

            $this->setData('result', $result);
            $this->setData('maskCellphone', $maskCellphone);
            $this->setData('maskEmail', $maskEmail);
            $this->setData('memberInfo', $memberDecrypt);
            $this->setData('sleepPolicy', $sleepPolicy);
            $this->setData('mailDomain', $mailDomain);
            $this->setData('phoneArea', SkinUtils::getPhoneArea());
            $this->setData('domainUrl', Request::getDomainUrl());
            $this->setData('authDataCpCode', ComponentUtils::getAuthCellphone());
        } catch (Exception $e) {
            throw new AlertRedirectException($e->getMessage(), $e->getCode(), $e, '/member/login.php', 'top');
        }
    }
}
