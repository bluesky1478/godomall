<?php
/**
 * This is commercial software, only users who have purchased a valid license
 * and accept to the terms of the License Agreement can install and use this
 * program.
 *
 * Do not edit or add to this file if you wish to upgrade Enamoo S5 to newer
 * versions in the future.
 *
 * @copyright Copyright (c) 2015 GodoSoft.
 * @link http://www.godo.co.kr
 */

namespace Bundle\Controller\Admin\Board;


use Component\Board\PlusMemoManager;

class PlusMemoRegisterController extends \Controller\Admin\Controller
{
    public function index()
    {
        $this->callMenu('board', 'board', 'plusMemoRegister');

        $this->addCss([
            'design.css',
            '../script/jquery/colorpicker/colorpicker.css',
        ]);
        $this->addScript([
            'jquery/colorpicker/colorpicker.js',
            'jquery/jquery.colorChart.js',
        ]);

        $plusMemo = new PlusMemoManager();
        $get = \Request::get()->all();
        $mode = gd_isset($get['mode'],'add');
        if($mode == 'modify') {
            $getData= $plusMemo->get($get['sno']);
            $data= $plusMemo->getFormValue($getData);
        }
        else {
            $data= $plusMemo->getFormValue();
        }

        $this->setData('mode',$mode);
        $this->setData('checked',$data['checked']);
        $this->setData('selected',$data['selected']);
        $this->setData('data',$data['data']);

    }
}
