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

namespace Bundle\Controller\Admin\Design;

use Component\Design\ListMouseover;

/**
 * 리스트 마우스오버 효과
 * @author <kookoo135@godo.co.kr>
 */
class ListMouseoverController extends \Controller\Admin\Controller
{
    /**
     * index
     *
     */
    public function index()
    {
        //--- 메뉴 설정
        $this->callMenu('design', 'designConf', 'listMouseover');
        $listMouseover = new ListMouseover();

        $getValue = \Request::get()->all();
        $mode = $getValue['mode'] ?? 'main';

        $data = gd_policy('design.listMouseover');
        $image = gd_policy('goods.image');

        $imageArr = [];
        foreach ($image as $key => $value) {
            if ($key == 'imageType') {
                continue;
            }
            $imageArr[$key] = $value['text'];
        }

        if (empty($data[$mode]) === true) {
            $data[$mode]['useFl'] = 'n';
        }

        $checked['useFl'][$data[$mode]['useFl']] = 'checked="checked"';
        $selected['effectFl'][$data[$mode]['effectFl']] =
        $selected['speedFl'][$data[$mode]['speedFl']] =
        $selected['borderFl'][$data[$mode]['borderFl']] =
        $selected['image'][$data[$mode]['image']] = 'selected="selected"';

        $this->addCss(
            [
                'design.css',
                '../script/jquery/colorpicker/colorpicker.css',
            ]
        );
        $this->addScript(
            [
                'jquery/colorpicker/colorpicker.js',
                'jquery/jquery.colorChart.js',
                'jquery/jstree/jquery.tree.js',
                'jquery/jstree/plugins/jquery.tree.contextmenu.js',
                'design/designTree.js',
                'design/design.js',
            ]
        );

        //--- 관리자 디자인 템플릿
        $this->getView()->setDefine('layoutMenu', 'menu_design.php');

        $this->setData('mode', $mode);
        $this->setData('data', $data[$mode]);
        $this->setData('checked', $checked);
        $this->setData('selected', $selected);
        $this->setData('effectFl', $listMouseover->getObject('effectFl'));
        $this->setData('image', $imageArr);
        $this->setData('speedFl', $listMouseover->getObject('speedFl'));
        $this->setData('borderFl', $listMouseover->getObject('borderFl'));
    }
}
