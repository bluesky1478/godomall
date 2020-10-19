<?php

namespace Bundle\Controller\Admin\Promotion;

use Framework\Debug\Exception\AlertRedirectException;
use Request;
use App;

class Insgov2WidgetConfigController extends \Controller\Admin\Controller
{
    public function Index()
    {
        try {
            $globals = \App::getInstance('globals');
            $shopSno = $globals->get('gLicense.godosno');
            $this->setData('shopSno', $shopSno);

            $widget = App::load('\\Component\\Promotion\\Insgov2Widget');
            $insgoConfig = gd_policy('promotion.insgo');

            $disabled = '';
            $widgetData = $widget->getData($insgoConfig['insgoManagerNo'], true);
            if (empty($insgoConfig) || $insgoConfig['accessToken'] == '') {
                $disabled = 'disabled';
                $this->setData('mode', 'regist');
            }else{
                if($widgetData['displayType'] == 'grid'){
                    $displayType = 'grid';
                }else if($widgetData['displayType'] == 'scroll'){
                    $displayType = 'scroll';
                }else if($widgetData['displayType'] == 'slide'){
                    $displayType = 'slide';
                }

                $this->setData('displayType', $displayType);
                $this->setData('widthCount', $widgetData['widgetWidthCount']);
                $this->setData('heightCount', $widgetData['widgetHeightCount']);
                $this->setData('backgroundColor', $widgetData['widgetBackgroundColor']);
                $this->setData('ImageMargin', $widgetData['widgetImageMargin']);
                $this->setData('thumbnailSizePx', $widgetData['widgetThumbnailSizePx']);
                $this->setData('thumbnailSize', $widgetData['thumbnailSize']);
                $this->setData('thumbnailBorder', $widgetData['thumbnailBorder']);
                $this->setData('overEffect', $widgetData['overEffect']);
                $this->setData('sideButtonColor', $widgetData['widgetSideButtonColor']);
                $this->setData('width', $widgetData['widgetWidth']);
                $this->setData('autoScroll', $widgetData['autoScroll']);
                $this->setData('scrollSpeed', $widgetData['scrollSpeed']);
                $this->setData('scrollTime', $widgetData['scrollTime']);
                $this->setData('effect', $widgetData['effect']);
                $this->setData('mode', 'modify');
            }
            $this->setData('disabled', $disabled);
            $this->setData('data', $widgetData);
            $this->setData('config', $insgoConfig);

            if($widgetData['displayType'] == 'grid'){
                $displayType = 'grid';
            }else if($widgetData['displayType'] == 'scroll'){
                $displayType = 'scroll';
            }else if($widgetData['displayType'] == 'slide'){
                $displayType = 'slide';
            }

            // --- 메뉴 설정
            $this->callMenu('promotion', 'sns', 'insgov2');
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
            throw new AlertRedirectException(__('인스고위젯관리 화면 에러'));
        }
    }
}
