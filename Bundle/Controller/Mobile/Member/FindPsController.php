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

use Framework\Http\Response;

/**
 * Class FindPsController
 * @package Bundle\Controller\Mobile\Member
 * @author  yjwee
 */
class FindPsController extends \Controller\Mobile\Controller
{
    public function index()
    {
        /** @var \Bundle\Controller\Front\Member\FindPsController $front */
        $front = \App::load('\\Controller\\Front\\Member\\FindPsController');
        $front->index();
    }
}
