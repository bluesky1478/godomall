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

namespace Bundle\Controller\Admin\Policy;

use Component\Delivery\OverseasDelivery;
use Component\Mall\Mall;
use Component\Policy\DesignSkinPolicy;
use Request;

/**
 * Class MallDeliveryListController
 *
 * @package Bundle\Controller\Admin\Policy
 * @author Jong-tae Ahn <qnibus@godo.co.kr>
 */
class MallDeliveryGroupListController extends \Controller\Admin\Controller
{
    /**
     * {@inheritdoc}
     *
     * @author Jong-tae Ahn <qnibus@godo.co.kr>
     */
    public function index()
    {
        // 관리자 메뉴 설정
        $this->callMenu('policy', 'multiMall', 'mallDeliveryGroup');

        // 해외배송 콤포넌트 호출
        $overseasDelivery = new OverseasDelivery();

        // 해외배송 기본리스트
        $delivery = $overseasDelivery->getCountryGroupList(\Request::get()->get('page'));
        $this->setData('page', $overseasDelivery->getPage());
        $this->setData('delivery', $delivery);
        $this->setData('weightUnit', $overseasDelivery->getWeightUnit());
    }
}
