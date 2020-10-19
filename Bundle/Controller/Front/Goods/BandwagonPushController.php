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
namespace Bundle\Controller\Front\Goods;

use Component\Goods\BandwagonPush;
use Framework\StaticProxy\Proxy\UserFilePath;
use Request;

class BandwagonPushController extends \Controller\Front\Controller
{
    public function index()
    {
        try {
            $bandwagon = new BandwagonPush();
            $param = Request::post()->all();
            switch ($param['mode']) {
                case 'getData':
                    $data = $bandwagon->getData($param['page'], $param['goodsNo']);

                    $this->setData('soldoutDisplay', gd_policy('soldout.pc'));
                    $this->setData('imagePath', UserFilePath::data('common')->www());
                    $this->setData('cfg', $bandwagon->cfg);
                    $this->setData('data', $data);
                    break;
                default:
                    break;
            }
        } catch (\Exception $e) {}
    }
}
