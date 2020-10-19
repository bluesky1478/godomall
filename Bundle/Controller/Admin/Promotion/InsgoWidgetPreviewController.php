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

use Framework\Debug\Exception\AlertBackException;
use Component\Promotion\InsgoWidget;
use Request;
use App;

/**
 * 인스고위젯 미리보기
 * @author  <@godo.co.kr>
 */
class InsgoWidgetPreviewController extends \Controller\Admin\Controller
{
    /**
     * index
     *
     * @throws AlertBackException
     */
    public function index()
    {
        $widget = App::load('\\Component\\Promotion\\InsgoWidget');
        $getValue =Request::get()->all();
        switch($getValue['mode']) {
            case 'list':
                $sno = $getValue['sno'];
                $widgetData = $widget->getData($sno, null, false);
                $insgoWidgetData = $widget->getInsgoWidgetData($widgetData['insgoData']);

                break;
            case 'regist':
            case 'modify':
                $insgoWidgetData = $widget->getInsgoWidgetData($getValue);
                break;

        }
        $insgoWidgetData['data']['total'] = count($insgoWidgetData['thumbnails']);

        $this->setData('insgoData', json_encode($insgoWidgetData));
        $this->setData('widgetSideButtonColor', $insgoWidgetData['data']['widgetSideButtonColor'] == '' ? '#ffffff' : $insgoWidgetData['data']['widgetSideButtonColor']);
        $this->getView()->setDefine('layout', 'layout_layer.php');
    }
}
