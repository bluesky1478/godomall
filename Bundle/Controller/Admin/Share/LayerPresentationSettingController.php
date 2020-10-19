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

namespace Bundle\Controller\Admin\Share;


use Component\Member\Manager;
use Component\Policy\MainSettingPolicy;

/**
 * Class LayerPresentationSettingController
 * @package Bundle\Controller\Admin\Share
 * @author  yjwee
 */
class LayerPresentationSettingController extends \Controller\Admin\Controller
{
    public function index()
    {
        $mainSettingPolicy = new MainSettingPolicy();
        $presentation = $mainSettingPolicy->getPresentation(\Session::get(Manager::SESSION_MANAGER_LOGIN . '.sno'));
        $checked['period'][$presentation] = 'checked="checked"';
        $this->setData('checked', $checked);
        // 템플릿 정의
        $this->getView()->setDefine('layout', 'layout_layer.php');
        // 공급사와 동일한 페이지 사용
        $this->getView()->setPageName('share/layer_presentation_setting.php');
    }
}
