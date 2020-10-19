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
namespace Bundle\Controller\Admin\Share;

use Request;
use Exception;
use App;
use Framework\Debug\Exception\AlertCloseException;

/**
 * Class LayerShippingAddress
 *
 * @package Bundle\Controller\Admin\Share
 * @author  <bumyul2000@godo.co.kr>
 */
class LayerShippingAddressController extends \Controller\Admin\Controller
{
    /**
     * @inheritdoc
     * @throws Exception
     */
    public function index()
    {
        try {
            $getValue = Request::get()->toArray();

            if((int)$getValue['memNo'] < 1){
                $this->layerNotReload('회원을 선택해 주세요.');
                exit;
            }

            if (gd_isset($getValue['page'])) {
                $getValue['page'] = (int)str_replace('page=', '', preg_replace('/^{page=[0-9]+}/', '', gd_isset($getValue['page'])));
            } else {
                $getValue['page'] = 1;
            }
            gd_isset($getValue['pageNum'], 5);

            $order = App::load('\\Component\\Order\\Order');
            $deliveryAddress = $order->getShippingAddressList($getValue['page'], $getValue['pageNum'], $getValue['memNo']);
            $this->setData('deliveryAddress', $deliveryAddress);

            // 페이지
            $pageObj = \App::load('\\Component\\Page\\Page');
            $this->setData('page', $pageObj);

            // page Url
            $pageUrl = '../' . Request::getDirectoryUri() . '/' . Request::getFileUri();
            $this->setData('pageUrl', $pageUrl);

            $this->setData('memNo', $getValue['memNo']);
            $this->setData('layerFormID', $getValue['layerFormID']);

            $this->getView()->setDefine('layout', 'layout_layer.php');
        } catch (Exception $e) {
            throw $e;
        }
    }
}
