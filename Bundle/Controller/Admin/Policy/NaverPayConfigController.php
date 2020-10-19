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

namespace Bundle\Controller\Admin\Policy;


use Component\PlusShop\PlusReview\PlusReviewConfig;
use Component\Policy\Policy;
use Framework\Utility\GodoUtils;

class NaverPayConfigController extends \Controller\Admin\Controller
{
    public function index()
    {
        $this->callMenu('policy', 'settle', 'naverPayConfig');

        $this->addScript([
            'jquery/jquery.multi_select_box.js',
        ]);
        $policy = new Policy();
        $data = $policy->getNaverPaySetting();
        $delivery = \App::load(\Component\Delivery\Delivery::class);
        $tmpDelivery = $delivery->getDeliveryCompany(null, true);
        $deliveryCom[0] = '= ' . __('배송 업체') . ' =';
        $deliverySno = 0;
        if (empty($tmpDelivery) === false) {
            foreach ($tmpDelivery as $key => $val) {
// 기본 배송업체 sno
                if ($key == 0) {
                    $deliverySno = $val['sno'];
                }
                $deliveryCom[$val['sno']] = $val['companyName'];
            }
            unset($tmpDelivery);
        }
        $plusReview = new PlusReviewConfig();
        $checked['areaDelivery'][$data['deliveryData'][\Session::get('manager.scmNo')]['areaDelivery']] = 'checked';
        $this->setData('checked', gd_isset($checked));
        $this->setData('data', gd_isset($data));
        $this->setData('scmNo', gd_isset(\Session::get('manager.scmNo')));
        $this->setData('deliveryCom', gd_isset($deliveryCom));
        $this->setData('deliverySno', gd_isset($deliverySno));
        $this->setData('isPlusReview', GodoUtils::isPlusShop(PLUSSHOP_CODE_REVIEW));
        $this->setData('disablePlusReview', $plusReview->getConfig('useFl') != 'y' ? 'disabled' : '');
    }
}
