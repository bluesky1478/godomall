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

/**
 * Class LayerSelectSalesProcessController
 * @package Bundle\Controller\Admin\Share
 * @author  kyeonk
 */

namespace Bundle\Controller\Admin\Share;

use Exception;

class LayerSelectSalesProcessController extends \Controller\Admin\Controller
{
    public function index()
    {
        try {
            // --- 메뉴 설정
            $this->callMenu('statistics', 'sales', 'day');

            // 튜닝 파일 여부
            $tuningFileExistFl = false;
            $tuningFilePath = \UserFilePath::module("Component/Order/OrderSalesStatistics.php")->getPathName();
            if(\FileHandler::isExists($tuningFilePath)) {
                $tuningFileExistFl = true;
            }

            // config 확인
            $config = gd_policy('statistics.order');
            $checked = [];
            $checked['periodic'] = 'checked="checked"';
            $realTimeDisabled = [];
            $tuningInfo = '';

            if ($tuningFileExistFl) {
                $tuningConfig = gd_policy('development.tuning');
                $tuningInfo = '튜닝 파일 적용';
                if (gd_isset($tuningConfig)) {
                    $tuningConfigDate = ' : '.$tuningConfig['date'];
                    $tuningInfo .= $tuningConfigDate;
                }
                $realTimeDisabled['radioBox'] = 'disabled';
                $realTimeDisabled['textColor'] = 'text-gray';
            } else {
                if (gd_isset($config)) {
                    if ($config['processSystem'] == 'realTime') {
                        $checked['realTime'] = 'checked="checked"';
                    }
                }
            }

            $this->setData('checked', $checked);
            $this->setData('realTimeDisabled', $realTimeDisabled);
            $this->setData('tuningInfo', $tuningInfo);

            $this->getView()->setDefine('layout', 'layout_layer.php');
        } catch (Exception $e) {
            throw $e;
        }
    }
}