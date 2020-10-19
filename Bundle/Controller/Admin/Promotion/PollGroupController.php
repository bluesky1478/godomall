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

namespace Bundle\Controller\Admin\Promotion;

use Component\Promotion\Poll;
use Component\Member\Group\Util as GroupUtil;
use Exception;
use Request;

/**
 * Class ShortUrlListController
 *
 * @package Bundle\Controller\Admin\Promotion
 * @author  Young-jin Bag <kookoo135@godo.co.kr>
 */
class PollGroupController extends \Controller\Admin\Controller
{
    public function index()
    {
        $getValue = Request::get()->all();
        $poll = new Poll();

        $groupSno = str_replace(INT_DIVISION, ',', $getValue['group']);
        $data = GroupUtil::getGroupName("sno IN (" . $groupSno . ")");

        $this->getView()->setDefine('layout', 'layout_layer.php');
        $this->setData('data', $data);
    }
}
