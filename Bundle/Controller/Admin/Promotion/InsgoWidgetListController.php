<?php

namespace Bundle\Controller\Admin\Promotion;

use App;

class InsgoWidgetListController extends \Controller\Admin\Controller
{
    public function Index()
    {
        // --- 메뉴 설정
        $this->callMenu('promotion', 'sns', 'insgoWidget');
        $widget = App::load('\\Component\\Promotion\\InsgoWidget');
        $widgetData = $widget->getData();
        $this->setData('data', $widgetData);

        $widgetType = [
            'grid' => __('그리드'),
            'scroll' => __('스크롤'),
            'slide' => __('슬라이드'),
        ];
        $this->setData('widgetType', $widgetType);
    }
}
