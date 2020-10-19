<?php
/**
 * This is commercial software, only users who have purchased a valid license
 * and accept to the terms of the License Agreement can install and use this
 * program.
 *
 * Do not edit or add to this file if you wish to upgrade Godomall5 to newer
 * versions in the future.
 *
 * @copyright Copyright (c) 2018 NHN godo: Corp.
 * @link http://www.godo.co.kr
 */

namespace Bundle\Controller\Admin\Share;

use Framework\Debug\Exception\LayerException;

class LayerUnstoringRegisterController extends \Controller\Admin\Controller
{
    public function index()
    {

        $request = \App::getInstance('request');

        $unstoring = \App::load('\\Component\\Delivery\\Unstoring');

        $getValue = $request->get()->toArray();

        $type = ($getValue['subTitle'] == '출고지') ? 'unstoring' : 'return';

        try {

            if (isset($getValue['unstoringNo'])) {
                $data = $unstoring->getUnstoringInfoOne((int)$getValue['unstoringNo']);
                $data['mode'] = 'modify';
            } else {
                $data['mode'] = 'register';
            }

        } catch (Exception $e) {

        }

        $this->setData('title', gd_isset($getValue['subTitle']));
        $this->setData('mallName', gd_isset($getValue['mallName']));
        $this->setData('mallFl', gd_isset($getValue['mallFl']));
        $this->setData('checkedNo', gd_isset($getValue['checkedNo']));
        $this->setData('page', gd_isset($getValue['page']));
        $this->setData('type', gd_isset($type));
        $this->setData('data', gd_isset($data));
//        $this->setData('checkedList', gd_isset(json_encode($getValue['checkedList'])));

        // --- 관리자 디자인 템플릿
        $this->getView()->setDefine('layout', 'layout_layer.php');
    }
}
