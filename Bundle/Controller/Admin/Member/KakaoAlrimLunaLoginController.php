<?php
namespace Bundle\Controller\Admin\Member;

use Exception;
use Framework\Debug\Exception\AlertBackException;
use Component\Policy\Policy;
use Component\Member\KakaoAlrimLuna;
/**
 * 카카오 아이디 로그인 설정(루나)
 * Class KakaoAlrimLunaLoginController
 * @package Bundle\Controller\Admin\Member
 */
class KakaoAlrimLunaLoginController extends \Controller\Admin\Controller
{
    public function index()
    {
        $request = \App::getInstance('request');
        $requestGetParams = $request->post()->all();
        $oKakao = new KakaoAlrimLuna;
        $encodedContent = $oKakao->sendLunaId($requestGetParams);
        echo $encodedContent;
        exit;
    }
}
?>