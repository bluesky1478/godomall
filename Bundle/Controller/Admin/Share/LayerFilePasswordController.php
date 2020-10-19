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

/**
 * 파일 패스워드 설정 레이어
 *
 * @author  haky <haky2@godo.co.kr>
 */
class LayerFilePasswordController extends \Controller\Admin\Controller
{
    public function index()
    {
        $request = \App::getInstance('request');
        $getValue = $request->get()->all();

        $this->setData('data', $getValue);
        $this->getView()->setDefine('layout', 'layout_layer.php');
        $this->getView()->setPageName('share/layer_file_password.php');
    }
}
