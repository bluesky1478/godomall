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

use Component\Goods\Populate;
use Exception;
use Globals;
use App;
use Request;
/**
 * 인기상품
 * @author <kookoo135@godo.co.kr>
 */
class PopulateConfigController extends \Controller\Admin\Controller
{
    /**
     * index
     *
     * @throws Exception
     */
    public function index()
    {
        if (Request::get()->has('sno')) { // 수정인 경우
            $this->callMenu('goods', 'displayConfig', 'modify');
        } else { // 등록인 경우
            $this->callMenu('goods', 'displayConfig', 'register');
        }

        // --- 모듈 호출
        $populate = new Populate();
        $display = \App::load('\\Component\\Display\\DisplayConfigAdmin');
        $goods = \App::load('\\Component\\Goods\Goods');

        $image = gd_policy('goods.image');
        $data = $populate->cfg;

        $imageArr = [];
        foreach ($image as $key => $value) {
            $imageArr[$key] = $value['text'] . ' - ' . $value['size1'] . 'px';
        }
        $frontSkinLive = \UserFilePath::frontSkin(Globals::get('gSkin.frontSkinLive'), 'proc', '_populate.html');
        $frontSkinWork = \UserFilePath::frontSkin(Globals::get('gSkin.frontSkinWork'), 'proc', '_populate.html');
        $mobileSkinLive = \UserFilePath::frontSkin(Globals::get('gSkin.mobileSkinLive'), 'proc', '_populate.html');
        $mobileSkinWork = \UserFilePath::frontSkin(Globals::get('gSkin.mobileSkinWork'), 'proc', '_populate.html');

        $checkPathFront = $checkPathMobile = false;
        if (file_exists($frontSkinLive) === true && file_exists($frontSkinWork)) {
            $checkPathFront = true;
        }
        if (file_exists($mobileSkinLive) === true && file_exists($mobileSkinWork)) {
            $checkPathMobile = true;
        }

        $themeDisplayType =  $display->themeDisplayType;
        unset($themeDisplayType['03']);
        unset($themeDisplayType['04']);
        unset($themeDisplayType['05']);
        unset($themeDisplayType['06']);
        unset($themeDisplayType['07']);
        unset($themeDisplayType['08']);
        unset($themeDisplayType['09']);
        unset($themeDisplayType['10']);
        unset($themeDisplayType['11']);
        unset($themeDisplayType['12']);

        $data['displayFl'] = gd_isset($data['displayFl'], 'y');
        $data['type'] = gd_isset($data['type'], 'sell');
        $data['rank'] = gd_isset($data['rank'], '20');
        $data['renewal'] = gd_isset($data['renewal'], '1');
        $data['collect'] = gd_isset($data['collect'], '7 DAY');
        $data['range'] = gd_isset($data['range'], 'all');
        $data['template'] = gd_isset($data['template'], '01');
        $data['same'] = gd_isset($data['same'], 'y');
        $data['useFl'] = gd_isset($data['useFl'], 'y');
        $data['soldOutFl'] = gd_isset($data['soldOutFl'], 'y');
        $data['soldOutDisplayFl'] = gd_isset($data['soldOutDisplayFl'], 'y');
        $data['soldOutIconFl'] = gd_isset($data['soldOutIconFl'], 'y');
        $data['iconFl'] = gd_isset($data['iconFl'], 'y');
        if(empty(\Request::get()->get('sno'))) {
            $data['displayField'][] = $data['mobileDisplayField'][] = 'img';
            $data['displayField'][] = $data['mobileDisplayField'][] = 'brandCd';
            $data['displayField'][] = $data['mobileDisplayField'][] = 'makerNm';
            $data['displayField'][] = $data['mobileDisplayField'][] = 'goodsNm';
            $data['displayField'][] = $data['mobileDisplayField'][] = 'shortDescription';
            $data['displayField'][] = $data['mobileDisplayField'][] = 'fixedPrice';
            $data['displayField'][] = $data['mobileDisplayField'][] = 'goodsPrice';
            $data['displayField'][] = $data['mobileDisplayField'][] = 'coupon';
            $data['displayField'][] = $data['mobileDisplayField'][] = 'goodsDcPrice';
            $data['displayField'][] = $data['mobileDisplayField'][] = 'mileage';
            $data['displayField'][] = $data['mobileDisplayField'][] = 'goodsModelNo';
            $data['displayField'][] = $data['mobileDisplayField'][] = 'goodsNo';
            $data['goodsDiscount'] =
            $data['mobileGoodsDiscount'] = ['goods'];
            $data['priceStrike'] =
            $data['mobilePriceStrike'] = ['fixedPrice'];
        }
        $data['displayType'] = gd_isset($data['displayType'], '01');

        $data['mobileUseFl'] = gd_isset($data['mobileUseFl'], 'y');
        $data['mobileSoldOutFl'] = gd_isset($data['mobileSoldOutFl'], 'y');
        $data['mobileSoldOutDisplayFl'] = gd_isset($data['mobileSoldOutDisplayFl'], 'y');
        $data['mobileSoldOutIconFl'] = gd_isset($data['mobileSoldOutIconFl'], 'y');
        $data['mobileIconFl'] = gd_isset($data['mobileIconFl'], 'y');
        $data['mobileDisplayType'] = gd_isset($data['mobileDisplayType'], '01');

        $checked['displayFl'][$data['displayFl']] =
        $checked['type'][$data['type']] =
        $checked['range'][$data['range']] =
        $checked['except_goods'][$data['except_goods']] =
        $checked['except_category'][$data['except_category']] =
        $checked['except_brand'][$data['except_brand']] =
        $checked['template'][$data['template']] =
        $checked['same'][$data['same']] =
        $checked['useFl'][$data['useFl']] =
        $checked['soldOutFl'][$data['soldOutFl']] =
        $checked['soldOutDisplayFl'][$data['soldOutDisplayFl']] =
        $checked['soldOutIconFl'][$data['soldOutIconFl']] =
        $checked['iconFl'][$data['iconFl']] =
        $checked['displayType'][$data['displayType']] =
        $checked['mobileUseFl'][$data['mobileUseFl']] =
        $checked['mobileSoldOutFl'][$data['mobileSoldOutFl']] =
        $checked['mobileSoldOutDisplayFl'][$data['mobileSoldOutDisplayFl']] =
        $checked['mobileSoldOutIconFl'][$data['mobileSoldOutIconFl']] =
        $checked['mobileIconFl'][$data['mobileIconFl']] =
        $checked['mobileDisplayType'][$data['mobileDisplayType']] = ' checked="checked"';

        $data['collectGoodsNo'] = $goods->getGoodsDataDisplay($data['goodsNo']);
        if($data['categoryCd']) {
            $cate = \App::load('\\Component\\Category\\CategoryAdmin');
            $tmp['code'] = explode(INT_DIVISION, $data['categoryCd']);
            foreach ($tmp['code'] as $val) {
                $tmp['name'][] = gd_htmlspecialchars_decode($cate->getCategoryPosition($val));
            }

            $data['collectCateCd'] = $tmp;
            unset($tmp);
        }

        if($data['brandCd']) {
            $brand = \App::load('\\Component\\Category\\BrandAdmin');
            $tmp['code'] = explode(INT_DIVISION, $data['brandCd']);
            foreach ($tmp['code'] as $val) {
                $tmp['name'][] = gd_htmlspecialchars_decode($brand->getCategoryPosition($val));
            }

            $data['collectBrandCd'] = $tmp;
            unset($tmp);
        }

        $data['exceptGoodsNo'] = $goods->getGoodsDataDisplay($data['except_goodsNo']);
        if($data['except_categoryCd']) {
            $cate = \App::load('\\Component\\Category\\CategoryAdmin');
            $tmp['code'] = explode(INT_DIVISION, $data['except_categoryCd']);
            foreach ($tmp['code'] as $val) {
                $tmp['name'][] = gd_htmlspecialchars_decode($cate->getCategoryPosition($val));
            }

            $data['exceptCateCd'] = $tmp;
            unset($tmp);
        }

        if($data['except_brandCd']) {
            $brand = \App::load('\\Component\\Category\\BrandAdmin');
            $tmp['code'] = explode(INT_DIVISION, $data['except_brandCd']);
            foreach ($tmp['code'] as $val) {
                $tmp['name'][] = gd_htmlspecialchars_decode($brand->getCategoryPosition($val));
            }

            $data['exceptBrandCd'] = $tmp;
            unset($tmp);
        }

        $this->setData('renewal', $populate::RENEWAL_SELECT);
        $this->setData('collect', $populate::COLLECT_SELECT);
        $this->setData('image', $imageArr);
        $this->setData('themeDisplayField', $display->themeDisplayField);
        $this->setData('themeDisplayType', $themeDisplayType);
        $this->setData('data', $data);
        $this->setData('checked', $checked);
        $this->setData('checkPathFront', $checkPathFront);
        $this->setData('checkPathMobile', $checkPathMobile);
        $this->setData('totalCnt', $populate->getTotalPopulateThemeCnt());
        $this->setData('themeGoodsDiscount', $display->themeGoodsDiscount);
        $this->setData('themePriceStrike', $display->themePriceStrike);
        $this->setData('themeDisplayAddField', $display->themeDisplayAddField);
    }
}
