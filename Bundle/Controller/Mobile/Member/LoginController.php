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

use Component\Attendance\AttendanceCheckLogin;
use Component\Member\Util\MemberUtil;
use Component\SiteLink\SiteLink;
use Exception;

/**
 * Class LoginController
 * @package Bundle\Controller\Mobile\Member
 * @author  yjwee
 */
class LoginController extends \Controller\Mobile\Controller
{
    private $message = '';

    public function index()
    {
        // 마이앱 로그인뷰 스크립트
        $myappBuilderInfo = gd_policy('myapp.config')['builder_auth'];
        $myappUseQuickLogin = gd_policy('myapp.config')['useQuickLogin'];
        if (\Request::isMyapp() && empty($myappBuilderInfo['clientId']) === false && empty($myappBuilderInfo['secretKey']) === false && !MemberUtil::isLogin() && $myappUseQuickLogin === 'true') {
            $myapp = \App::load('Component\\Myapp\\Myapp');
            echo $myapp->getAppBridgeScript('loginView', \Request::get()->get('goodsNo'), \Request::get()->get('guestOrder'));

            $this->js('parent.history.back()');
            exit;
        }

        /** @var \Bundle\Controller\Front\Member\LoginController $front */
        $front = \App::load('\\Controller\\Front\\Member\\LoginController');
        $front->index();

        $this->setData($front->getData());
        $scripts = ['gd_payco.js'];
        if ($front->getData('useFacebookLogin') === true) {
            $scripts[] = 'gd_sns.js';
        }
        if ($front->getData('useNaverLogin') === true) {
            $scripts[] = 'gd_naver.js';
        }
        if ($front->getData('useKakaoLogin') === true) {
            $scripts[] = 'gd_kakao.js';
        }
        if ($front->getData('useWonderLogin') === true) {
            $scripts[] = 'gd_wonder.js';
        }

        $this->addScript($scripts);

        if (MemberUtil::isLogin()) {
            try {
                \DB::begin_tran();
                $check = new AttendanceCheckLogin();
                $message = $check->attendanceLogin();
                \DB::commit();
                if ($message) {
                    $this->js('alert(\'' . $message . '\');parent.location.href=\'' . URI_MOBILE . '\';');
                }
            } catch (Exception $e) {
                \DB::rollback();
                \Logger::info(__METHOD__ . ', ' . $e->getFile() . '[' . $e->getLine() . '], ' . $e->getMessage());
            }

            \Logger::info(__METHOD__ . ', isLogin attendance message=[' . $this->message . ']');
            if ($this->message != '') {
                $this->js('alert(\'' . $this->message . '\');location.href=\'' . URI_MOBILE . '\'');
            } else {
                $this->redirect(URI_MOBILE);
            }
        }
    }
}
