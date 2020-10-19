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

namespace Bundle\Controller\Front\Member\Payco;

use Component\Godo\GodoPaycoServerApi;
use Component\Member\Member;
use Component\Member\MemberSnsService;
use Component\Member\Util\MemberUtil;
use Exception;
use Framework\Debug\Exception\AlertCloseException;
use Request;
use Session;

/**
 * Class PaycoConnectController
 * @package Bundle\Controller\Front\Member\Payco
 * @author  yjwee
 */
class PaycoConnectController extends \Controller\Front\Controller
{
    public function index()
    {
        $paycoApi = new GodoPaycoServerApi();

        try {
            if (Request::get()->has('code') && Request::get()->has('state')) {
                $paycoToken = $paycoApi->getToken(Request::get()->get('code'));
                Session::set(GodoPaycoServerApi::SESSION_ACCESS_TOKEN, $paycoToken);
                if ($paycoApi->isSuccess($paycoToken['header'])) {
                    $memberSnsService = new MemberSnsService();
                    if ($memberSnsService->hasSnsMember($paycoToken['idNo'])) {
                        $js = 'alert(\'' . __('이미 다른 회원정보와 연결된 계정입니다. 다른 계정을 이용해주세요.') . '\');' . PHP_EOL;
                        $js .= 'if (typeof(window.top.layerSearchArea) == "object") {' . PHP_EOL;
                        $js .= 'parent.location.href=\'../../main/index.php\';' . PHP_EOL;
                        $js .= '} else {' . PHP_EOL;
                        $js .= 'self.close();' . PHP_EOL;
                        $js .= '}' . PHP_EOL;
                        $this->js($js);
                    }
                    $userProfile = $paycoApi->getUserProfile($paycoToken['access_token']);
                    $memberSnsService->connectSns(Session::get(Member::SESSION_MEMBER_LOGIN . '.memNo'), $userProfile['idNo'], $paycoToken['access_token'], 'payco');
                    $paycoApi->logByLink();
                    $js = 'alert(\'' . __('계정 연결이 완료되었습니다. 로그인 시 연결된 계정으로 로그인 하실 수 있습니다.') . '\');' . PHP_EOL;
                    $js .= 'if (typeof(window.top.layerSearchArea) == "object") {' . PHP_EOL;
                    $js .= 'parent.location.href=\'../../mypage/my_page.php\';' . PHP_EOL;
                    $js .= '} else {' . PHP_EOL;
                    $js .= 'opener.location.href=\'../../mypage/my_page.php\';self.close();' . PHP_EOL;
                    $js .= '}' . PHP_EOL;
                    $this->js($js);
                }
            }
            $returnURL = Request::getDomainUrl() . Request::getRequestUri() . '?referer=' . Request::getReferer();
            $loginURL = $paycoApi->getLoginURL($returnURL);
            $this->redirect($loginURL);
        } catch (Exception $e) {
            MemberUtil::logoutPayco();
            throw new AlertCloseException($e->getMessage(), $e->getCode(), $e);
        }
    }
}
