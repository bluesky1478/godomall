<?php

namespace Bundle\Controller\Admin\Design;


/**
 * 환율계산 위젯
 */
class ExchangeRateWidgetController extends \Controller\Admin\Controller
{
    public function index()
    {
        $this->callMenu('design', 'widget', 'exchangeRateWidget');

        $exchangeRateDao = \App::load('\\Component\\PlusShop\\ExchangeRateWidget\\ExchangeRateDao');
        $exchangeRateConfig = \App::load('\\Component\\PlusShop\\ExchangeRateWidget\\ExchangeRateConfig');
        $data = $exchangeRateDao->getAppData();

        $this->setData('widget_display', $exchangeRateDao->isDisplay());
        $this->setData('base_cur_type', $data['base_cur_type']);
        $this->setData('exchange_cur_type', $data['exchange_cur_type']);
        $this->setData('widget_type', $data['widget_type']);
        $this->setData('widget_icon_type', $data['widget_icon_type']);
        $this->setData('widget_icon_use_both', $data['widget_icon_use_both']);
        $this->setData('installed', is_dir($exchangeRateConfig->getSkinPath()));

        $pc = $exchangeRateConfig->getIconPc();
        $mb = $exchangeRateConfig->getIconMb();
        if ($pc) {
            $path = $exchangeRateConfig->getImageWebPath() . DS . basename($pc) . '?rand=' . rand(1, 9999);
            $this->setData('pc_icon', $path);
        }
        if ($mb) {
            $path = $exchangeRateConfig->getImageWebPath() . DS . basename($mb) . '?rand=' . rand(1, 9999);
            $this->setData('mb_icon', $path);
        }
    }
}
