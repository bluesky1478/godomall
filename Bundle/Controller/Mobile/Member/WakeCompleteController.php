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

namespace Bundle\Controller\Mobile\Member;

use App;
use Component\Member\Member;
use Component\Member\MemberSleep;
use Exception;
use Framework\Debug\Exception\AlertRedirectException;
use Request;
use Session;

/**
 * Class WakeController
 * @package Bundle\Controller\Mobile\Member
 * @author  mjlee
 */
class WakeCompleteController extends \Controller\Mobile\Controller
{
    /**
     * @inheritdoc
     */
    public function index()
    {
        \Logger::info(__METHOD__);
        try {
            $front = \App::load('\\Controller\\Front\\Member\\WakeCompleteController');
            $front->index();
            $this->setData($front->getData());
            $wakeInfo = Session::get(MemberSleep::SESSION_WAKE_INFO);
            $skinData = gd_policy('design.skin');
            $realPath = implode(DIRECTORY_SEPARATOR, [USERPATH, 'data', 'skin', 'mobile', $skinData['mobileLive'], 'member', 'wake_complete.html',]);
            if (!is_file($realPath)) {  // 스킨패치를 하지 않은 경우
                if ($wakeInfo['memPw']) {
                    $this->js('location.href="../main/index.php"');
                } else {
                    $this->js('location.href="../member/login.php"');
                }
            }
        } catch (Exception $e) {
            throw new AlertRedirectException($e->getMessage(), 0, null, '../member/login.php');
        } finally {
            Session::del(MemberSleep::SESSION_WAKE_INFO);
            Session::del(Member::SESSION_DREAM_SECURITY);
            Session::del(Member::SESSION_IPIN);
        }
    }
}
