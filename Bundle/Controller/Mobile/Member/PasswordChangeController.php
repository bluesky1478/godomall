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

/**
 * Class PasswordChangeController
 * @package Bundle\Controller\Front\Member
 * @author  yjwee
 */
class PasswordChangeController extends \Controller\Mobile\Controller
{
    public function index()
    {
        /** @var \Bundle\Controller\Front\Member\PasswordChangeController $front */
        $front = App::load('\\Controller\\Front\\Member\\PasswordChangeController');
        $front->index();
        $this->setData('gPageName', __('비밀번호 변경 안내'));
        $this->setData($front->getData());
    }
}
