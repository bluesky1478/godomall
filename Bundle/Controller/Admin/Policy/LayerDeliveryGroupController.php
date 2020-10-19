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
use Component\Mall\MallDAO;
use Request;

/**
 * Class LayerDeliveryCountryGroupController
 *
 * @package Bundle\Controller\Admin\Policy
 * @author Jong-tae Ahn <qnibus@godo.co.kr>
 */
class LayerDeliveryGroupController extends \Controller\Admin\Controller
{
    /**
     * {@inheritdoc}
     *
     */
    public function index()
    {
        // 해외배송 콤포넌트 호출
        $overseasDelivery = new OverseasDelivery();

        // 국가데이터 가져오기
        $deliveryGroup = $overseasDelivery->getGroupView(Request::get()->get('sno'));
        $this->setData('deliveryGroup', $deliveryGroup);

        // 레이어 레이아웃 설정
        $this->getView()->setDefine('layout', 'layout_layer.php');
    }
}
