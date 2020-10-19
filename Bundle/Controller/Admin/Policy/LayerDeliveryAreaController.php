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

use Framework\Debug\Exception\Except;
use Globals;
use Request;
use Exception;

/**
 * 지역별 추가 배송비 레이어 팝업
 *
 */
class LayerDeliveryAreaController extends \Controller\Admin\Controller
{
    /**
     * {@inheritdoc}
     *
     * @throws Exception
     */
    public function index()
    {
        try {
            // --- 모듈 호출
            $delivery = \App::load('\\Component\\Delivery\\Delivery');

            $getValue = Request::get()->toArray();
            // 리퀘스트 기본값 재셋팅
            if ($getValue['page']) {
                parse_str($getValue['page'], $aPage);
                $getValue['page'] = $aPage['page'];
                Request::get()->set('page', $aPage['page']);
            } else {
                $getValue['page'] = 1;
            }
            if (isset($getValue['layer_scmFl'])) {
                Request::get()->set('scmFl', $getValue['layer_scmFl']);
            } else {
                Request::get()->set('scmFl', 'all');
            }
            if (isset($getValue['layer_scmNo'])) {
                Request::get()->set('scmNo', $getValue['layer_scmNo']);
            }
            if (isset($getValue['layer_scmNoNm'])) {
                Request::get()->set('scmNoNm', $getValue['layer_scmNoNm']);
            }

            $checked['scmFl'][Request::get()->get('scmFl')] = 'checked="checked"';
            if ($getValue['layer_scmNo'] == 0) {
                $checked['scmUseFl'][0] = 'checked="checked"';
            } else {
                $checked['scmUseFl'][1] = 'checked="checked"';
            }

            $getData = $delivery->getAreaGroupDeliveryList();
            $page = \App::load('Component\\Page\\Page');

            $this->setData('page', gd_isset($page));
            $this->setData('pageNum', gd_isset($page->page['list']));
            $this->setData('search', $getData['search']);
            $this->setData('checked', $checked);
            $this->setData('delivery', $delivery);
            $this->setData('data', gd_isset($getData['data']));
            $this->setData('total', count($getData['data']));

            $this->setData('layerFormID', $getValue['layerFormID']);
            $this->setData('parentFormID', $getValue['parentFormID']);
            $this->setData('dataFormID', $getValue['dataFormID']);
            $this->setData('dataInputNm', $getValue['dataInputNm']);
            $this->setData('mode', gd_isset($getValue['mode'], 'search'));

            $this->getView()->setDefine('layout', 'layout_layer.php');
        } catch (Exception $e) {
            throw $e;
        }
    }
}

