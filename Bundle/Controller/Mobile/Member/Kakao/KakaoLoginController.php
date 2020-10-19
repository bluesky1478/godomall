<?php
namespace Bundle\Controller\Mobile\Member\Kakao;

/**
 * Class KakaoLoginController
 * @package Bundle\Controller\Mobile\Member\Kakao
 */
class KakaoLoginController extends \Controller\Mobile\Controller
{
    public function index()
    {
        /** @var \Bundle\Controller\Front\Member\Kakao\KakaoLoginController $front */
        $front = \App::load('\\Controller\\Front\\Member\\Kakao\\KakaoLoginController');
        $front->index();
    }
}