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
 * @link      http://www.godo.co.kr
 */

namespace Bundle\Controller\Admin\Share;

use Component\Order\OrderAdmin;
use Component\Member\Manager;

/**
 * @author  <kookoo135@godo.co.kr>
 */
class LayerHandleReasonController extends \Controller\Admin\Controller
{
    public function index()
    {
        $orderAdmin = new OrderAdmin();
        $getValue = \Request::get()->all();
        $data = $orderAdmin->getOrderUserHandle($getValue['orderNo'], null, [], null, $getValue['handleNo'])[0];
        $data['userHandleDetailReason'] = str_replace("\\r\\n", '<br />', $data['userHandleDetailReason']);
        $data['adminHandleReason'] = str_replace("\\r\\n", '<br />', $data['adminHandleReason']);
        if (empty($data['userRefundAccountNumber']) === false && gd_str_length($data['userRefundAccountNumber']) > 50) {
            $data['userRefundAccountNumber'] = \Encryptor::decrypt($data['userRefundAccountNumber']);
        }
        if ($getValue['type'] == 'admin' && $data['managerNo'] > 0) {
            $manager = new Manager();
            $managerInfo = $manager->getManagerInfo($data['managerNo']);
            $this->setData('managerInfo', $managerInfo);
        } else if ($getValue['type'] == 'admin' && $data['managerNo'] == -1){
            $managerInfo['managerNm'] = 'System';
            $managerInfo['managerId'] = '';
            $this->setData('managerInfo', $managerInfo);
        }

        $this->setData('type', $getValue['type']);
        $this->setData('handleMode', $getValue['handleMode']);
        $this->setData('goodsNm', $getValue['goodsNm']);
        $this->setData('data', $data);
        $this->getView()->setDefine('layout', 'layout_layer.php');
    }
}
