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

namespace Bundle\Controller\Admin\Goods;

use Component\Member\Group\Util;
use Exception;
use Request;
use Globals;

/**

 *
 * @package Bundle\Controller\Admin\Goods
 * @author  <cjb3333@godo.co.kr>
 */
class LayerBenefitDetailController extends \Controller\Admin\Controller
{
    public function index()
    {
        $sno = Request::get()->get('sno');

        // --- 모듈 호출
        $goodsBenefit = \App::load('\\Component\\Goods\\GoodsBenefit');
        $memberGroup = \App::load('\\Component\\Member\\MemberGroup');

        $groupList = $memberGroup->getGroupListSelectBox(['key'=>'sno', 'value'=>'groupNm']);
        $data = $goodsBenefit->getGoodsBenefit($sno);

        $this->getView()->setDefine('layout', 'layout_layer.php');
        $this->setData('data', $data['data']);
        $this->setData('groupList', $groupList['data']);

    }
}
