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
namespace Bundle\Widget\Front\Goods;

/**
 * Class GoodsDisplayGroupMainWidget
 *
 * @package Bundle\Controller\Front\Outline
 * @author  <bumyul2000@godo.co.kr>
 */

class GoodsDisplayGroupMainWidget extends \Widget\Front\Widget
{

    public function index()
    {
        $eventGroup = \App::load('\\Component\\Promotion\\EventGroupTheme');
        $groupData = $eventGroup->getSimpleData($this->getData('sno'));

        $this->setData('groupData', $groupData);
    }
}
