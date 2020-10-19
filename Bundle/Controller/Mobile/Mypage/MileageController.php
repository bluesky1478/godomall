<?php

/**
 *
 *  This is commercial software, only users who have purchased a valid license
 *  and accept to the terms of the License Agreement can install and use this
 *  program.
 *
 *  Do not edit or add to this file if you wish to upgrade Godomall5 to newer
 *  versions in the future.
 *
 * @copyright ⓒ 2016, NHN godo: Corp.
 * @link http://www.godo.co.kr
 *
 */
namespace Bundle\Controller\Mobile\Mypage;

use App;

/**
 * 마이페이지-혜택 마일리지
 * @package Bundle\Controller\Mobile\Mypage
 * @author  yjwee
 */
class MileageController extends \Controller\Mobile\Controller
{
    /**
     * @inheritdoc
     */
    public function index()
    {
        /** @var \Bundle\Controller\Front\Mypage\MileageController $front */
        $front = App::load('\\Controller\\Front\\Mypage\\MileageController');
        $front->index();
        $this->setData($front->getData());

        $this->addCss(
            [
                'plugins/bootstrap-datetimepicker.min.css',
                'plugins/bootstrap-datetimepicker-standalone.css',
            ]
        );

        $locale = \Globals::get('gGlobal.locale');
        $this->addScript(
            [
                'gd_board_list.js',
                'moment/moment.js',
                'moment/locale/' . $locale . '.js',
                'jquery/datetimepicker/bootstrap-datetimepicker.min.js',
            ]
        );
        //        debug($regTerm);
        //        debug($regDt);
    }
}
