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

namespace Bundle\Controller\Front\Mypage;

use Component\Board\BoardList;
use Component\Page\Page;
use Request;
use View\Template;
use Component\Database\DBTableField;
use Component\Except\Except;
use Cookie;
use Session;

class MypageTodayController  extends \Controller\Front\Controller
{
     private $db;

     private $arrGoodsFields;

    public function index()
    {
        //@formatter:off
        $this->arrGoodsFields = [
            ['titleNm' => __('품절 상품'),		'CodeNm' => 'soldOutFl',		'mustFl' => 'y'],
            ['titleNm' => __('품절 아이콘'),	'CodeNm' => 'soldOutIconFl',	'mustFl' => 'y'],
            ['titleNm' => __('상품 이미지'),	'CodeNm' => 'imageFl',			'mustFl' => 'y'],
            ['titleNm' => __('아이콘'),		'CodeNm' => 'iconFl',			'mustFl' => 'y'],
            ['titleNm' => __('상품명'),		'CodeNm' => 'goodsNmFl',		'mustFl' => 'y'],
            ['titleNm' => __('쿠폰가격'),		'CodeNm' => 'couponPriceFl',	'mustFl' => 'n'],
            ['titleNm' => __('가격'),			'CodeNm' => 'goodsPriceFl',		'mustFl' => 'y'],
            ['titleNm' => __('정가'),			'CodeNm' => 'fixedPriceFl',		'mustFl' => 'n'],
            ['titleNm' => __('마일리지'),		'CodeNm' => 'mileageFl',		'mustFl' => 'y'],
            ['titleNm' => __('옵션'),			'CodeNm' => 'optionFl',			'mustFl' => 'n'],
            ['titleNm' => __('브랜드'),		'CodeNm' => 'brandFl',			'mustFl' => 'n'],
            ['titleNm' => __('제조사'),		'CodeNm' => 'makerFl',			'mustFl' => 'n'],
            ['titleNm' => __('짧은설명'),		'CodeNm' => 'shortDescFl',		'mustFl' => 'n'],
        ];
        //@formatter:on

        if (!is_object($this->db)) {
            $this->db = \App::load('DB');
        }

        $mallBySession = SESSION::get(SESSION_GLOBAL_MALL);
        if($mallBySession) {
            $todayCookieName = 'todayGoodsNo'.$mallBySession['sno'];
        } else {
            $todayCookieName = 'todayGoodsNo';
        }

        // 쿠키 정보 설정 및 상품 정보
        if (Cookie::has($todayCookieName) === true) {

            // 쿠키의 상품 정보
            $todayGoodsNo	= json_decode(Cookie::get($todayCookieName));
            $todayGoodsNo	= implode(INT_DIVISION, $todayGoodsNo);

            // 상품 설정에 따른 출력옵션
            $prnOpt = array();
            foreach ($this->arrGoodsFields as $key => $val) {
                $prnOpt[$val['CodeNm']]		= gd_isset($wdata[$val['CodeNm']], $val['mustFl']);
            }

            // 기능 설정 정보
            $imageType		= gd_isset($wdata['imageCd'],'main');						// 이미지 타입 - 기본 'main'
            $optionFl		= $prnOpt['optionFl'] == 'y' ? true : false;			// 옵션 출력 여부 - true or false (기본 false)
            $soldOutFl		= $prnOpt['soldOutFl'] == 'y' ? true : false;			// 품절상품 출력 여부 - true or false (기본 true)
            $brandFl		= $prnOpt['brandFl'] == 'y' ? true : false;				// 브랜드 출력 여부 - true or false (기본 false)
            $couponPriceFl	= $prnOpt['couponPriceFl'] == 'y' ? true : false;		// 쿠폰가격 출력 여부 - true or false (기본 false)

            // 최근 본 상품 설정 config 불러오기
            $todayConf		= gd_policy('goods.today');
            if(empty($todayConf['todayHour']) === false && empty($todayConf['todayCnt']) === false) {

                // 최근 본 상품 진열
                $goods		= \App::load('Goods','\Component\Goods\Goods');
                $goodsList	= $goods->goodsDataDisplay('goods', gd_isset($todayGoodsNo), $todayConf['todayCnt'], gd_isset($_GET['sort'],'sort asc'), $imageType, $optionFl, $soldOutFl, $brandFl, $couponPriceFl);
                $goodsCnt	= count($goodsList);
            }
        }

        // Print Template
        $this->setData('location',gd_isset($location));
        $this->setData('goodsList',gd_isset($goodsList));
        $this->setData('goodsCnt',gd_isset($goodsCnt));
        $this->setData('prnOpt',gd_isset($prnOpt));
        $this->setData('sort',Request::get()->get('sort'));
    }

}
