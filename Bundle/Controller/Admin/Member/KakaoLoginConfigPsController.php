<?php
/**
 * Created by PhpStorm.
 * User: godo
 * Date: 2018-08-13
 * Time: 오전 10:56
 */
namespace Bundle\Controller\Admin\Member;

use Bundle\Component\Policy\JoinItemPolicy;
use Bundle\Component\Policy\KakaoLoginPolicy;
use Framework\Debug\Exception\AlertOnlyException;
use Framework\Debug\Exception\AlertRedirectException;
use Request;
use Exception;

class KakaoLoginConfigPsController extends \Controller\Admin\Controller
{
    public function index()
    {
        try{
            $policy = new KakaoLoginPolicy();
            $getPost = Request::post()->all();

            if($policy->save($getPost)){
                $redirectURL = '../member/kakao_login_config.php';
                $redirectTarget = 'parent';
                throw new AlertRedirectException(__('저장이 완료되었습니다.'), 200, null, $redirectURL, $redirectTarget);

            } else {
                throw new AlertOnlyException(__('처리중에 오류가 발생하여 실패되었습니다.'));
            }
        }catch (Exception $e) {
            throw $e;
        }
    }
}