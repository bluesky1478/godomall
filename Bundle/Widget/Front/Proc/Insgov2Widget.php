<?php

namespace Bundle\Widget\Front\Proc;

use Request;
use Exception;
use App;

class Insgov2Widget extends \Widget\Front\Widget
{
    public function index()
    {
        try {
            $widget = App::load('\\Component\\Promotion\\Insgov2Widget');
            $widgetData = $widget->getData(null, false);
            $insgoWidgetData = $widget->getInsgoWidgetData($widgetData,$widgetData['widgetSno']);

            if($widgetData['widgetDisplayType'] == 'grid'){
                $layoutCnt = $widgetData['widgetWidthCount'] * $widgetData['widgetHeightCount'];
                $insgoWidgetData['thumbnails'] = array_slice($insgoWidgetData['thumbnails'], 0, $layoutCnt);
            }
            $insgoWidgetData['data']['total'] = count($insgoWidgetData['thumbnails']);
            $this->setData('insgoData', json_encode($insgoWidgetData));
            $this->setData('widgetSideButtonColor', $insgoWidgetData['data']['widgetSideButtonColor'] == '' ? '#ffffff' : $insgoWidgetData['data']['widgetSideButtonColor']);
        } catch (Exception $e) {

        }
    }
}
