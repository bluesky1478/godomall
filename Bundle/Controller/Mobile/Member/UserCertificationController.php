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
 * Class UserCertificationController
 * @package Bundle\Controller\Mobile\Member
 * @author  yjwee
 */
class UserCertificationController extends \Controller\Mobile\Controller
{
    public function index()
    {
        /** @var \Bundle\Controller\Front\Member\UserCertificationController $front */
        $front = App::load('\\Controller\\Front\\Member\\UserCertificationController');
        $front->index();

        $this->setData($front->getData());
    }
}
