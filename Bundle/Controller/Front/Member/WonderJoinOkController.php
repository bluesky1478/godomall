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
 * 위메프 아이디 로그인 완료 이동 페이지
 * @package Bundle\Controller\Front\Member
 * @author  yoonar
 */
class WonderJoinOkController extends \Controller\Front\Controller
{
    public function index()
    {
        $this->js('top.opener.location.href=\'../member/join_ok.php\'; top.self.close();');
        exit;
    }
}
