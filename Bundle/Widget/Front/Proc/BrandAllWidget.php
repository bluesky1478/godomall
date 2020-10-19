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

namespace Bundle\Widget\Front\Proc;

/**
 * Class BrandAllWidget
 *
 * @package Bundle\Widget\Front\Proc
 * @author  Hakyoung Lee <haky2@godo.co.kr>
 */
class BrandAllWidget extends \Widget\Front\Widget
{
    public function index()
    {
        if($this->getData('type') =='banner') {
            $this->getView()->setPageName('proc/_brand_all_banner');
        }
    }
}