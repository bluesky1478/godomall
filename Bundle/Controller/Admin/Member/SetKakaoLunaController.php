<?php
/**
 * Created by PhpStorm.
 * User: godo
 * Date: 2018-08-10
 * Time: 오후 5:21
 */

namespace Bundle\Controller\Admin\Member;

use Exception;
use Framework\Debug\Exception\AlertBackException;
use Component\Policy\Policy;
use Component\Member\KakaoAlrimLuna;
use Request;
use Logger;
/**
 * 카카오 아이디 로그인 설정(루나)
 * Class KakaoAlrimLunaLoginController
 * @package Bundle\Controller\Admin\Member
 */
class SetKakaoLunaController extends \Controller\Admin\Controller
{
    public function index()
    {

        $aRequest = Request::request()->toArray();
        $policy = \App::load('\\Component\\Policy\\Policy');
        // 요청 정보 저장
        Logger::channel('kakao')->info('SERVICE_INFO', Request::request()->toArray());
        Logger::channel('kakao')->info('REMOTE_ADDR', [Request::getRemoteAddress()]);
        Logger::channel('kakao')->info('HTTP_REFERER', [Request::getReferer()]);

        $decContent = $aRequest['p'];
        $oKakao = new KakaoAlrimLuna;
        $lunaKey = $oKakao->getLunaKeyDec($decContent);

        $policy->saveKakaoAlrimLunaConfig($lunaKey);

        $this->js("parent.location.reload();");
        exit;
    }

}
?>