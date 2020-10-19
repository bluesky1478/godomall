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

/**
 * 스케줄(일정관리) 출력
 *
 * @author haky
 */
class LayerScheduleListController extends \Controller\Admin\Controller
{
    /**
     * index
     *
     * @throws LayerException
     */
    public function index()
    {
        // schedule 정의
        $sch = \App::load('\\Component\\Admin\\Schedule');

        try {
            $schedule['requestDateList'] = $sch->getScheduleListByDate(Request::get()->get('scdDt'));
            $schedule['requestDate'] = Request::get()->get('scdDt');
        }
        catch (\Exception $e) {
            throw new LayerException($e->getMessage());
        }

        $this->getView()->setDefine('layout', 'base/layer_schedule_list.php');
        $this->setData('schedule', $schedule);
    }
}
