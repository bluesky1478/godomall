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

/**
 * Class 회원가입 방법 선택
 * @package Bundle\Controller\Mobile\Member
 * @author  yjwee
 */
class JoinMethodController extends \Controller\Mobile\Controller
{
    public function index()
    {
        /** @var \Bundle\Controller\Front\Member\JoinMethodController $front */
        $front = \App::load('\\Controller\\Front\\Member\\JoinMethodController');
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
    }
}
