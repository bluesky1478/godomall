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

namespace Bundle\Controller\Front\Member;

use Component\Member\Util\MemberUtil;

/**
 * Class FindPasswordCompleteController
 * @package Bundle\Controller\Front\Member
 * @author  yjwee
 */
class FindPasswordCompleteController extends \Controller\Front\Controller
{
    /**
     * @inheritdoc
     */
    public function index()
    {
        if (MemberUtil::checkLogin()) {
            $this->redirect('../main/index.php');
        }
    }
}


