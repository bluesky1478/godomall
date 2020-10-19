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

/**
 * Class 프론트-회원승인대기 컨트롤러
 * @package Bundle\Controller\Mobile\Member
 * @author  yjwee
 */
class JoinWaitController extends \Controller\Mobile\Controller
{
    public function index()
    {
        /** @var \Bundle\Controller\Front\Member\JoinWaitController $front */
        $front = \App::load('\\Controller\\Front\\Member\\JoinWaitController');
        $front->index();

        $this->setData($front->getData());
        $this->setData('gPageName', __('회원가입 승인대기'));
    }
}
