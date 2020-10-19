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

class TimeSaleRegisterController extends \Controller\Admin\Controller
{
    public function index()
    {
        $timeSaleAdmin = \App::load('\\Component\\Promotion\\TimeSaleAdmin');

        try {

            $data = $timeSaleAdmin->getDataTimeSale(Request::get()->get('sno'));

            $displayConfig = \App::load('\\Component\\Display\\DisplayConfigAdmin');
            $data['data']['pcThemeList'] = $displayConfig->getInfoThemeConfigCate('F', 'n');
            $data['data']['mobileThemeList'] = $displayConfig->getInfoThemeConfigCate('F','y');
            $data['data']['sortList'] = array_merge(array('' => __('운영자 진열 순서')) + $displayConfig->goodsSortList);

        } catch (\Exception $e) {
            throw $e;
        }
        $this->callMenu('promotion', 'timeSale', $data['data']['mode']);

        // --- 관리자 디자인 템플릿
        if (Request::get()->get('popupMode')) {
            $this->getView()->setDefine('layout', 'layout_blank.php');
        }

        $this->setData('data',$data['data']);
        $this->setData('checked', $data['checked']);
        $this->setData('selected', $data['selected']);
    }
}
