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

use Bundle\Component\Member\Member;
use Component\Member\Util\MemberUtil;
use Framework\Debug\Exception\AlertRedirectException;
use function GuzzleHttp\Psr7\str;
use Validation;

/**
 * 로그아웃 요청 처리
 * @package Bundle\Controller\Front\Member
 * @author  yjwee
 */
class LogoutController extends \Controller\Front\Controller
{

    /**
     * @inheritdoc
     */
    public function index()
    {
        $request = \App::getInstance('request');
        $logger = \App::getInstance('logger');

        $returnUrl = $request->get()->get('returnUrl', $request->post()->get('returnUrl'));

        // returnUrl 데이타 타입 체크
        try {
            Validation::setExitType('throw');
            Validation::defaultCheck(gd_isset($returnUrl), 'url');
        } catch (\Exception $e) {
            $returnUrl = '/';
        }
        $logger->info(sprintf('logout!!! request params return url=>%s]', $returnUrl));
        if (empty($request->getReferer()) === false) {
            $returnUrl = $request->getReferer();
            $logger->info(sprintf('request referer not empty!! change return url=>%s', $returnUrl));
        }

        if (strpos($returnUrl, 'main/index') > -1 || empty($returnUrl) === true || strpos($returnUrl, 'member/') !== false || strpos($returnUrl, 'mypage/') !== false || strpos($returnUrl, 'order/') !== false) {
            $logger->info(sprintf('except return url!! now return url [%s]. change return url [/]', $returnUrl));
            $returnUrl = '/';
        }

        MemberUtil::logoutWithCookie();

        // 라이브피드 로그아웃 스크립트 호출
        $livefeed = \App::load('\\Component\\Service\\LivefeedScript');
        $livefeedConfig = $livefeed->config;
        if ($livefeedConfig['livefeedUseType'] == 'y') {
            echo $livefeed->getLogoutScript();
        }

        // 해피톡 카카오 상담톡 로그아웃
        $happytalk = \App::load('\\Component\\Service\\Happytalk');
        $happytalk->logoutHappytalk();

        // 마이앱 로그아웃 스크립트
        $myappBuilderInfo = gd_policy('myapp.config')['builder_auth'];
        if (\Request::isMyapp() && empty($myappBuilderInfo['clientId']) === false && empty($myappBuilderInfo['secretKey']) === false) {
            $myapp = \App::load('Component\\Myapp\\Myapp');
            // 로그인 타입
            if (\Session::get(Member::SESSION_MYAPP_SNS_LOGIN) == 'y') {
                $myappScript = $myapp->getAppBridgeScript('snsLogout');
                \Session::del(Member::SESSION_MYAPP_SNS_LOGIN);
            } else {
                $myappScript = $myapp->getAppBridgeScript('logout');
            }

            if (!$request->get()->has('noMessage') && $request->get()->get('noMessage') != 'y') {
                echo $myappScript;
            }
            \Cookie::del(MemberUtil::COOKIE_LOGIN);
        }

        $message = __('로그아웃 되었습니다.');
        if ($request->get()->has('noMessage') && $request->get()->get('noMessage') == 'y') {
            $logger->info('logout message clear');
            $message = null;
        }

        // 로그아웃 후 로그인 페이지 이동할 경우
        if(strpos($request->get()->get('returnUrl'), 'member/login.php') !== false){
            $returnUrl = '../member/login.php?returnUrl='.$request->getReferer();
        }

        throw new AlertRedirectException($message, 0, null, $returnUrl);
    }
}
