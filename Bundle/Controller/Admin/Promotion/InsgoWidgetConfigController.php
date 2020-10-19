<?php

namespace Bundle\Controller\Admin\Promotion;

use Framework\Debug\Exception\AlertBackException;
use Request;
use App;

class InsgoWidgetConfigController extends \Controller\Admin\Controller
{
    public function Index()
    {
        try {
            $getValue = Request::get()->toArray();
            $widget = App::load('\\Component\\Promotion\\InsgoWidget');
            if($getValue['sno']) {
                $menuName = 'insgoWidgetModify';
                $widgetData = $widget->getData($getValue['sno'], null, false, true);
                $this->setData('data', $widgetData);
                $this->setData('mode', 'modify');
            } else {
                $widgetCount = $widget->getCount();
                if($widgetCount >= 5) {
                    throw new AlertBackException(__('인스고위젯은 최대 5개까지만 등록할 수 있습니다. 기존 위젯을 수정하거나 삭제 후 등록해주세요.'));
                }
                $menuName = 'insgoWidgetRegist';
                $this->setData('mode', 'regist');
            }
            // --- 메뉴 설정
            $this->callMenu('promotion', 'sns', $menuName);
            $this->addCss(
                [
                    'design.css',
                    '../script/jquery/colorpicker-master/jquery.colorpicker.css',
                ]
            );
            $this->addScript(
                [
                    'jquery/colorpicker-master/jquery.colorpicker.js',
                ]
            );
        } catch (Exception $e) {
            throw new AlertBackException(__('인스고 위젯 등록 화면 에러'));
        }
    }
}
