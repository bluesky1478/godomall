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
namespace Bundle\Controller\Mobile\Event;

use Framework\Debug\Exception\AlertBackException;
use Framework\Debug\Exception\AlertOnlyException;
use Framework\Debug\Exception\Framework\Debug\Exception;
use Framework\Utility\DateTimeUtils;
use Message;
use Globals;
use Request;
use Session;

class TimeSaleController extends \Controller\Mobile\Controller
{

    /**
     * 타임세일 리스트 페이지
     *
     * @author artherot, sunny
     * @version 1.0
     * @since 1.0
     * @copyright Copyright (c), Godosoft
     * @throws Except
     */
    public function index()
    {
        $getValue = Request::get()->toArray();

        // 모듈 설정
        $displayConfig = \App::load('\\Component\\Display\\DisplayConfig');
        $timeSale = \App::load('\\Component\\Promotion\\TimeSale');

        try {

            $getData = $timeSale->getInfoTimeSale($getValue['sno']);

            if (!Session::has('manager.managerId')) {
                if ($getData['endDt'] < date('Y-m-d H:i:s')) {
                    throw new \Exception(__('타임세일이 종료되었습니다.'));
                } else if ($getData['startDt'] > date('Y-m-d H:i:s')) {
                    throw new \Exception(__('타임세일이 존재하지 않습니다.'));
                }
            }

            $timeSaleDuration = strtotime($getData['endDt'])- time();

            $getData['startDt'] = gd_date_format("Y.m.d H:i", $getData['startDt']);
            $getData['endDt'] = gd_date_format("Y.m.d H:i" ,$getData['endDt']);

            $themeInfo = $displayConfig->getInfoThemeConfig($getData['mobileThemeCd']);
            $themeInfo['displayField'] = explode(",", $themeInfo['displayField']);

            // 장바구니 설정
            if ($themeInfo['displayType'] == '11') {
                $cartInfo = gd_policy('order.cart');
                $this->setData('cartInfo', gd_isset($cartInfo));
            }

            $this->setData('timeSaleList', gd_isset($timeSale->getListTimeSale()));
            $this->setData('timeSaleSno',$getValue['sno']);
            $this->setData('sort', gd_isset($getValue['sort']));
            $this->setData('timeSaleInfo', gd_isset($getData));
            $this->setData('timeSaleDuration', gd_isset($timeSaleDuration));
            $this->setData('themeInfo', gd_isset($themeInfo));
            $this->setData('currency', Globals::get('gCurrency'));


        } catch (\Exception $e) {
            throw new AlertBackException($e->getMessage());
        }


    }
}
