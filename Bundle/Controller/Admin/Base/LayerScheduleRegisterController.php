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
namespace Bundle\Controller\Admin\Base;

use Framework\Debug\Exception\LayerException;
use Request;
use Message;

/**
 * 스케줄(일정관리) 작성
 *
 * @author Shin Donggyu <artherot@godo.co.kr>
 */
class LayerScheduleRegisterController extends \Controller\Admin\Controller
{
    /**
     * index
     *
     * @throws LayerException
     * @throws \Exception
     */
    public function index()
    {
        // schedule 정의
        $schedule = \App::load('\\Component\\Admin\\Schedule');

        try {
            $data = $schedule->getScheduleListByDate(Request::get()->get('scdDt'));
        }
        catch (\Exception $e) {
            throw new LayerException($e->getMessage());
        }

        // --- 관리자 디자인 템플릿
        $this->getView()->setDefine('layout', 'layout_layer.php');
        $this->setData('data', $data);
        $this->setData('requestDate', Request::get()->get('scdDt'));
    }
}
