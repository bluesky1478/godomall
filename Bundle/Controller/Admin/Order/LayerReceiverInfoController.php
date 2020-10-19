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
namespace Bundle\Controller\Admin\Order;

use Exception;
use Request;
use App;

/**
 * Class LayerReceiverInfoController
 * 수령자 정보 변경
 *
 * @package Bundle\Controller\Admin\Order
 * @author  by
 */
class LayerReceiverInfoController extends \Controller\Admin\Controller
{
    /**
     * @inheritdoc
     */
    public function index()
    {
        $request = App::getInstance('request');
        try {
            // POST 리퀘스트
            $postValue = Request::post()->toArray();
            $this->setData('orderNo', $postValue['orderNo']);

            // 모듈 설정
            $order = App::load(\Component\Order\OrderAdmin::class);
            $getData = $order->getOrderView($postValue['orderNo']);
            $this->setData('data', $getData);
            
            // 국가데이터 가져오기
            $countriesCode = $order->getUsableCountriesList($getData['mallSno']);

            // 주소용 국가코드 셀렉트 박스 데이터
            $countryAddress = [];
            foreach ($countriesCode as $key => $val) {
                $countryAddress[$val['code']] = __($val['countryNameKor']) . '(' . $val['countryName'] . ')';
            }
            $this->setData('countryAddress', $countryAddress);

            // 전화용 국가코드 셀렉트 박스 데이터
            foreach ($countriesCode as $key => $val) {
                if ($val['callPrefix'] > 0) {
                    $countryPhone[$val['code']] = __($val['countryNameKor']) . '(+' . $val['callPrefix'] . ')';
                }
            }
            $this->setData('countryPhone', $countryPhone);

            $this->getView()->setDefine('layout', 'layout_layer.php');
            $this->getView()->setDefine('layoutOrderViewReceiverInfoModify', $request->getDirectoryUri() . '/layout_order_view_receiver_info_modify.php');

            // 공급사와 동일한 페이지 사용
            $this->getView()->setPageName('order/layer_receiver_info.php');

        } catch (Exception $e) {
            throw $e;
        }
    }
}
