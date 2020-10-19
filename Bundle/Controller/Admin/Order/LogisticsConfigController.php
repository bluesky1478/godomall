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
 * @link      http://www.godo.co.kr
 */

namespace Bundle\Controller\Admin\Order;

/**
 * Class CJ대한통운 설정
 * @package Bundle\Controller\Admin\Order
 * @author  Lee Namju <lnjts@godo.co.kr>
 */
class LogisticsConfigController extends \Controller\Admin\Controller
{
    public function index()
    {
        $this->callMenu('order', 'epostParcel', 'logisticsConfig');

        $logisticsConfig = gd_policy('logistics.config');
        $default = ['FRT_DV_CD' => '03', 'BOX_TYPE_CD' => '03'];

        $config = array_merge($default, (array) $logisticsConfig);
        $checked['FRT_DV_CD'][$config['FRT_DV_CD']] = $checked['BOX_TYPE_CD'][$config['BOX_TYPE_CD']] = 'checked';

        $this->setData('checked', $checked);
        $this->setData('data', $config);

    }
}
