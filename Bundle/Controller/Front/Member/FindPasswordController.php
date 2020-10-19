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

use Component\Member\Util\MemberUtil;

/**
 * Class 비밀번호 찾기
 * @package Bundle\Controller\Front\Member
 * @author  yjwee
 */
class FindPasswordController extends \Controller\Front\Controller
{
    /**
     * @inheritdoc
     */
    public function index()
    {
        MemberUtil::logout();
    }
}
