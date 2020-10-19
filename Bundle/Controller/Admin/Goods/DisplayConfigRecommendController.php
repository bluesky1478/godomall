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

use Component\Goods\RecommendGoods;
use Framework\Utility\ImageUtils;
use Request;

/**
 * 검색창 추천상품 노출 설정
 */
class DisplayConfigRecommendController extends \Controller\Admin\Controller
{

    /**
     * index
     *
     * @throws \Exception
     */
    public function index()
    {
        // --- 메뉴 설정
        $this->callMenu('goods', 'displayConfig', 'goodsRecommend');

        try {
            // --- 설정 호출
            $recom = new RecommendGoods();
            $config = gd_policy('goods.recom');
            $display = \App::load('\\Component\\Display\\DisplayConfigAdmin');

            $data = $recom->getGoodsData();

            // --- 이미지 설정 및 필요한 이미지만 추출
            $confImage = gd_policy('goods.image');

            $config['pcDisplayFl'] = gd_isset($config['pcDisplayFl'], 'y');
            $config['mobileDisplayFl'] = gd_isset($config['mobileDisplayFl'], 'y');
            $config['imageCd'] = gd_isset($config['imageCd'], 'main');
            $config['displayType'] = gd_isset($config['displayType'], 'random');
            $config['soldOutFl'] = gd_isset($config['soldOutFl'], 'y');
            $config['displayField'] = gd_isset($config['displayField'], ['img', 'goodsNm']);
            if (in_array('goodsDiscount', array_keys($config)) === false) gd_isset($config['goodsDiscount'], ['goods']);

            $checked['pcDisplayFl'][$config['pcDisplayFl']] =
            $checked['mobileDisplayFl'][$config['mobileDisplayFl']] =
            $checked['displayType'][$config['displayType']] =
            $checked['soldOutFl'][$config['soldOutFl']] = 'checked = "checked"';
        } catch (\Exception $e) {
            throw $e;
        }

        // --- 관리자 디자인 템플릿
        if (isset($getValue['popupMode']) === true) {
            $this->getView()->setDefine('layout', 'layout_blank.php');
        }

        $this->addScript([
            'jquery/jquery.multi_select_box.js',
        ]);

        $this->setData('data', $data);
        $this->setData('config', $config);
        $this->setData('defaultRecommendGoodsCnt', RecommendGoods::DEFAULT_RECOMMEND_GOODS_CNT);
        $this->setData('checked', $checked);
        $this->setData('confImage', $confImage);
        $this->setData('themeDisplayField', $display->themeDisplayField);
        $this->setData('themeGoodsDiscount', $display->themeGoodsDiscount);
        $this->setData('themePriceStrike', $display->themePriceStrike);
        $this->setData('themeDisplayAddField', $display->themeDisplayAddField);
    }
}
