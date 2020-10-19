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

namespace Bundle\Controller\Front\Member;

/**
 * Class 프론트-회원승인대기 컨트롤러
 * @package Bundle\Controller\Front\Member
 * @author  yjwee
 */
class JoinWaitController extends \Controller\Front\Controller
{
    public function index()
    {
        if (gd_is_login() === true) {
            $this->redirect('/');
        }
        $infoPolicy = gd_policy('basic.info');
        $joinPolicy = gd_policy('member.join');

        $this->setData('join', $joinPolicy);
        $this->setData('mallNm', $infoPolicy['mallNm']);
    }
}
