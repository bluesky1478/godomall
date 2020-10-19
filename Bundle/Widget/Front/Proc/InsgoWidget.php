<?php

namespace Bundle\Widget\Front\Proc;

use Request;
use Exception;
use App;
use Framework\Debug\Exception\AlertOnlyException;

class InsgoWidget extends \Widget\Front\Widget
{
    public function index()
    {
        try {
            $sno = $this->getData('sno');
            $widget = App::load('\\Component\\Promotion\\InsgoWidget');
            $widgetData = $widget->getData($sno, null, false);
            $insgoWidgetData = $widget->getInsgoWidgetData($widgetData['insgoData'],$widgetData['sno']);
            $insgoWidgetData['data']['total'] = count($insgoWidgetData['thumbnails']);
            $this->setData('insgoData', json_encode($insgoWidgetData));
            $this->setData('widgetSideButtonColor', $insgoWidgetData['data']['widgetSideButtonColor'] == '' ? '#ffffff' : $insgoWidgetData['data']['widgetSideButtonColor']);
        } catch (Exception $e) {

        }
    }
}
