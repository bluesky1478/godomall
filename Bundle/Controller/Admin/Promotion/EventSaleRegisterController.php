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

namespace Bundle\Controller\Admin\Promotion;

use Exception;
use Request;
use Session;

class EventSaleRegisterController extends \Controller\Admin\Controller
{
    public function index()
    {

        $goods = \App::load('\\Component\\Goods\\GoodsAdmin');

        // --- 메인 증정 데이터
        try {
            $data = $goods->getDataDisplayTheme(Request::get()->get('sno'),'event');
            $displayConfig = \App::load('\\Component\\Display\\DisplayConfigAdmin');
            $data['data']['sortList'] = array_merge(array('' => __('운영자 진열 순서')) + $displayConfig->goodsSortList);
        } catch (Exception $e) {
            throw $e;
        }

        if ($data['data']['mode'] == 'register') {
            $this->callMenu('promotion', 'eventSale', 'register');
            $data['data']['themeCd'] = $data['data']['themeCd'] ?? 'F0000001';
            $data['data']['mobileThemeCd'] = $data['data']['mobileThemeCd'] ?? 'F0000002';
        } else {
            $this->callMenu('promotion', 'eventSale', 'modify');
        }

        $toggle = gd_policy('display.toggle');
        $SessScmNo = Session::get('manager.scmNo');

        $this->setData('data', $data['data']);
        $this->setData('checked', $data['checked']);
        $this->setData('selected', $data['selected']);
        $this->setData('toggle', $toggle);
        $this->setData('SessScmNo', $SessScmNo);

        //seo태그 개별설정
        $this->getView()->setDefine('seoTagFrm',  'share/seo_tag_each.php');
    }
}
