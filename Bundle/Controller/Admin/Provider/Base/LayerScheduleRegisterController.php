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
namespace Bundle\Controller\Admin\Provider\Base;

use Framework\Debug\Exception\LayerException;
use Request;
use Message;

/**
 * 스케줄(일정관리) 작성
 *
 * @author Shin Donggyu <artherot@godo.co.kr>
 */
class LayerScheduleRegisterController extends  \Controller\Admin\Base\LayerScheduleRegisterController
{
    /**
     * index
     *
     * @throws LayerException
     * @throws \Exception
     */
    public function index()
    {
        parent::index();
        $this->getView()->setPagename('base/layer_schedule_register.php');
    }
}
