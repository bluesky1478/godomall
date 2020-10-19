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

namespace Bundle\Controller\Admin\Board;

use Component\Board\BoardAdmin;
use Framework\Debug\Exception\Except;
use Globals;
use Request;

class PopupCaptchaController extends \Controller\Admin\Controller
{
    /**
     * Description
     */
    public function index()
    {
        //--- 페이지 데이터
        try {
            $boardAdmin = new BoardAdmin();

            $getData = [];
            if (Request::get()->get('sno')) {
                $getData = $boardAdmin->getCaptchaColor(Request::get()->get('sno'));
                $getData['sno'] = Request::get()->get('sno');
            }
        } catch (\Exception $e) {
            echo($e->ectMessage);
        }

        //--- 관리자 디자인 템플릿
        $this->getView()->setDefine('layout', 'layout_blank.php');
        $this->getView()->setDefine('layoutContent', Request::getDirectoryUri() . '/' . Request::getFileUri());

        $this->setData(
            'headerStyle', [
            PATH_ADMIN_GD_SHARE . 'script/jquery/colorpicker/colorpicker.css',
        ]
        );
        $this->addScript(['jquery/colorpicker/colorpicker.js', 'jquery/jquery.colorChart.js']);
        $this->setData('data', $getData);
    }


}
