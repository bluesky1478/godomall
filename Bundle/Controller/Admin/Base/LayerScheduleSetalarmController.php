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

use Globals;

/**
 * 스케줄(일정관리) 알람설정
 *
 * @author Shin Donggyu <artherot@godo.co.kr>
 */
class LayerScheduleSetalarmController extends \Controller\Admin\Controller
{
    /**
     * index
     *
     */
    public function index()
    {
        // --- 데이터
        $data = gd_policy('basic.schedule');
        $data['phone'] = explode('-', gd_isset($data['phone']));

        $checked['alarmUseFl'][gd_isset($data['alarmUseFl'], 'n')] = 'checked="checked"';
        $selected['dDayPopup'][gd_isset($data['dDayPopup'])] = 'selected="selected"';

        // --- 관리자 디자인 템플릿
        $this->getView()->setDefine('layout', 'layout_blank.php');

        $this->setData('data', $data);
        $this->setData('checked', $checked);
        $this->setData('selected', $selected);
    }
}
