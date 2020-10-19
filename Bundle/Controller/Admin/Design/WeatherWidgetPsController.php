<?php

namespace Bundle\Controller\Admin\Design;


use Bundle\Component\PlusShop\WeatherWidget\WeatherWidgetDao;
use Bundle\Controller\Admin\Controller;
use Request;

class WeatherWidgetPsController extends Controller
{
    public function index()
    {
        $dao = new WeatherWidgetDao();

        $data = $dao->getData();
        $data['active'] = Request::post()->get('active') == 1;
        $data['base_location'] = Request::post()->get('base_location');
        $data['widget_link_usable_setting'] = intval(Request::post()->get('widget_link_usable_setting'));
        $data['widget_background_color_usable_setting'] = intval(Request::post()->get('widget_background_color_usable_setting'));
        $data['widget_border_usable_setting'] = intval(Request::post()->get('widget_border_usable_setting'));
        $data['font_color'] = Request::post()->get('font_color');
        $data['background_color'] = Request::post()->get('background_color');
        $data['border_color'] = Request::post()->get('border_color');
        $data['widget_type'] = intval(Request::post()->get('widget_type'));

        $dao->update($data);
        $this->layer(__('저장되었습니다.'), 'top.location.reload()');
    }
}
