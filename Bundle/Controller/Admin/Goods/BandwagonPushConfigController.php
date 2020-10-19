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
namespace Bundle\Controller\Admin\Goods;

use Component\Goods\BandwagonPush;
use Framework\StaticProxy\Proxy\UserFilePath;
use Request;

/**
 * 밴드웨건 푸시 설정
 * @author <kookoo135@godo.co.kr>
 */
class BandwagonPushConfigController extends \Controller\Admin\Controller
{

    /**
     * index
     *
     * @throws \Exception
     */
    public function index()
    {
        // --- 메뉴 설정
        $this->callMenu('goods', 'displayConfig', 'bandwagonPushConfig');

        $bandwagon = new BandwagonPush();

        $data = $bandwagon->cfg;

        $checked['soldOutFl'][$data['soldOutFl']] =
        $checked['position'][$data['position']] =
        $checked['stockFl'][$data['stockFl']] =
        $checked['iconFl'][$data['iconFl']] =
        $checked['mobileFl'][$data['mobileFl']] = ' checked="checked"';

        $this->addCss([
            '../script/jquery/colorpicker-master/jquery.colorpicker.css',
        ]);
        $this->addScript([
            'jquery/colorpicker-master/jquery.colorpicker.js',
        ]);

        $this->setData('data', $data);
        $this->setData('imagePath', UserFilePath::data('common')->www());
        $this->setData('range', $bandwagon::BW_RANGE);
        $this->setData('field', $bandwagon::BW_FIELD);
        $this->setData('term', $bandwagon::BW_TERM);
        $this->setData('page', $bandwagon::BW_PAGE);
        $this->setData('checked', $checked);
    }
}
