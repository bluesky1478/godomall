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

use Request;
use Cookie;
use Session;

/**
 * 최근 본 상품 리스트
 *
 * @author Shin Donggyu <artherot@godo.co.kr>
 */
class GoodsTodayController  extends \Controller\Front\Controller
{

    /**
     * index
     *
     * @throws \Exception
     */
    public function index()
    {
        try {
            // 모듈 설정
            $goods = \App::load('\\Component\\Goods\\Goods');
            $getValue = Request::get()->toArray();

            // 마일리지 정보
            $mileage = gd_mileage_give_info();

            $mallBySession = SESSION::get(SESSION_GLOBAL_MALL);
            if($mallBySession) {
                $todayCookieName = 'todayGoodsNo'.$mallBySession['sno'];
            } else {
                $todayCookieName = 'todayGoodsNo';
            }

            // 최근 본 상품 추출
            if (Cookie::has($todayCookieName)) {
                // 최근 본 상품 정보
                $todayGoodsNo = json_decode(Cookie::get($todayCookieName));
                $todayGoodsNo = implode(INT_DIVISION, $todayGoodsNo);

                // 최근 본 상품 진열
                $goodsData = $goods->goodsDataDisplay('goods', $todayGoodsNo, null, gd_isset($getValue['sort'], 'sort asc'));
            }
        }
        catch (\Exception $e) {
            //echo $e->getMessage();
        }

        $this->setData('mileage', $mileage['info']);
        $this->setData('goodsList', gd_isset($goodsData));
        $this->setData('goodsCnt', count($goodsData));
    }
}
