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

namespace Bundle\Controller\Admin\Promotion;

use Component\Attendance\Mode;
use Component\Page\Page;

/**
 * Class AttendanceListController
 *
 * @package Bundle\Controller\Admin\Promotion
 * @author  yjwee
 *
 */
class AttendanceListController extends \Controller\Admin\Controller
{
    /**
     * @inheritdoc
     *
     */
    public function index()
    {
        /** @var \Bundle\Controller\Admin\Controller $this */
        /** page navigation */
        $this->callMenu('promotion', 'attendance', 'list');

        $currentPage = \Request::get()->get('page', 1);
        $pageNum = \Request::get()->get('pageNum', 10);

        /** @var \Bundle\Component\Member\Manager $manager */
        $manager = \App::load('\\Component\\Member\\Manager');
        $arrManager = $manager->getManagerName();

        /** @var \Bundle\Component\Attendance\Attendance $attendance */
        $attendance = \App::load('\\Component\\Attendance\\Attendance');
        $data = $attendance->lists(\Request::get()->all(), $currentPage, $pageNum);

        /**
         * set page number
         * @var \Bundle\Component\Page\Page $pageObject
         */
        $page = new Page($currentPage, $attendance->getSearchCount(), $attendance->getCount(DB_ATTENDANCE), $pageNum);
        $page->setPage();
        $page->setUrl(\Request::getQueryString());

        /** set checkbox, select property */
        $checked['deviceFl'][\Request::get()->get('deviceFl', '')] =
        $checked['activeFl'][\Request::get()->get('activeFl', '')] =
        $checked['conditionFl'][\Request::get()->get('conditionFl', '')] =
        $checked['methodFl'][\Request::get()->get('methodFl', '')] = 'checked="checked"';

        $this->setData('page', $page);
        $this->setData('data', $data);
        $this->setData('checked', $checked);
        $this->setData('arrManager', $arrManager);
        $this->setData('combineSearch', $attendance::COMBINE_SEARCH);
        $this->setData('deleteMode', AttendancePsController::MODE_DELETE);
        $this->setData('activeFl', $attendance->getActiveFl());
        $this->setData('deviceFl', $attendance->getDeviceFl());
        $this->setData('methodFl', $attendance->getMethodFl());
        $this->setData('conditionFl', $attendance->getConditionFl());
    }
}
