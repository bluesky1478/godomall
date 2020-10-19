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
namespace Bundle\Widget\Mobile\Goods;

/**
 * Class MypageQnaController
 *
 * @package Bundle\Controller\Front\Outline
 * @author  Young Eun Jung <atomyang@godo.co.kr>
 */
use Request;
use Framework\Utility\ArrayUtils;
use UserFilePath;

class GoodsDisplayMainWidget extends \Widget\Mobile\Widget
{

    public function index()
    {
        if (is_null($this->getData('soldoutDisplay'))) {
            $this->setData('soldoutDisplay', gd_policy('soldout.mobile'));
        }

        Request::get()->set('isMain',true);

        $goods = \App::load('\\Component\\Goods\\Goods');
        $getData = $goods->getDisplayThemeInfo($this->getData('sno'));
        $mainLinkData = [
            'mainThemeSno' => $getData['sno'],
            'mainThemeNm' => $getData['themeNm'],
            'mainThemeDevice' => $getData['mobileFl'],
        ];
        Request::get()->set('mainLinkData',$mainLinkData);
        //기획전 그룹형 그룹정보 로드
        if((int)$this->getData('groupSno') > 0) {
            $eventGroup = \App::load('\\Component\\Promotion\\EventGroupTheme');
            $getData = $eventGroup->replaceEventData($this->getData('groupSno'), $getData, 'mobile');
        }

        //다른기획전 보기
        $getData['otherEventData'] = $goods->getDisplayOtherEventList();

        if($getData['kind'] === 'event'){
            //모바일 기획전에서 'pc/모바일 상세설명 동일 사용'일시 내용 체인지
            if($getData['descriptionSameFl'] === 'y'){
                $getData['mobileContents'] = $getData['pcContents'];
                $getData['eventThemeMobileContents'] = $getData['eventThemePcContents'];
            }
            //하단 더보기노출 미사용일시 전체노출
            if($getData['moreBottomFl'] === 'y'){
                $this->setData('viewType', '');
            }
        }

        if($getData && $getData['displayFl'] =='y') {


            if ($this->getData('isMobile') == 'y') {
                $themeCd = $getData['mobileThemeCd'];
            } else {
                $themeCd = $getData['themeCd'];
            }
            $displayConfig = \App::load('\\Component\\Display\\DisplayConfig');
            if (empty($themeCd) === true) {
                $themeInfo = $displayConfig->getInfoThemeConfigCate('B','y')[0];
            } else {
                $themeInfo = $displayConfig->getInfoThemeConfig($themeCd);
            }


            if ($themeInfo['detailSet']) $themeInfo['detailSet'] = unserialize($themeInfo['detailSet']);
            $themeInfo['displayField'] = explode(",", $themeInfo['displayField']);
            $themeInfo['goodsDiscount'] = explode(",", $themeInfo['goodsDiscount']);
            $themeInfo['priceStrike'] = explode(",", $themeInfo['priceStrike']);
            $themeInfo['displayAddField'] = explode(",", $themeInfo['displayAddField']);
            if($this->getData('viewType') == 'all') {
                $displayCnt = count(explode(INT_DIVISION,$getData['goodsNo']));
            }
            else {
                $displayCnt = gd_isset($themeInfo['lineCnt']) * gd_isset($themeInfo['rowCnt']);
            }

            $imageType = gd_isset($themeInfo['imageCd'], 'main');                        // 이미지 타입 - 기본 'main'
            $soldOutFl = $themeInfo['soldOutFl'] == 'y' ? true : false;            // 품절상품 출력 여부 - true or false (기본 true)
            $brandFl = in_array('brandCd', array_values($themeInfo['displayField'])) ? true : false;    // 브랜드 출력 여부 - true or false (기본 false)
            $couponPriceFl = in_array('coupon', array_values($themeInfo['displayField'])) ? true : false;        // 쿠폰가격 출력 여부 - true or false (기본 false)
            $optionFl = in_array('option', array_values($themeInfo['displayField'])) ? true : false;

            if($getData['sortAutoFl'] =='n') {
                if($themeInfo['displayType'] =='07') {
                    //debug($themeInfo);
                    $goodsNoData =explode(STR_DIVISION,  $getData['goodsNo']);
                    foreach($goodsNoData as $key => $value) {
                        if($value) {
                            Request::get()->set('goodsNo',explode(INT_DIVISION,$value));
                            $mainOrder = "FIELD(g.goodsNo," . str_replace(INT_DIVISION, ",", $value) . ")";
                            if ($themeInfo['soldOutDisplayFl'] == 'n') $mainOrder = "soldOut asc," . $mainOrder;
                            $goods->setThemeConfig($themeInfo);
                            $tmpGoodsList = $goods->getGoodsSearchList($displayCnt , $mainOrder, $imageType, $optionFl, $soldOutFl, $brandFl, $couponPriceFl  ,$displayCnt);
                            $goodsCnt[] = $tmpGoodsList['total'];
                            $goodsData[] =  array_chunk($tmpGoodsList['listData'],$themeInfo['lineCnt']);
                            Request::get()->del('goodsNo');
                        } else {
                            $goodsCnt[] = 0;
                            $goodsData[] =  '';
                        }
                    }

                } else {
                    $goodsNoData = implode(INT_DIVISION,array_filter(explode(STR_DIVISION,  $getData['goodsNo'])));

                    if ($getData['goodsNo'] && $goodsNoData) {
                        Request::get()->set('goodsNo', explode(INT_DIVISION, $goodsNoData));
                        if($getData['kind'] === 'event' && trim($getData['sort']) !== ''){
                            $mainOrder = $getData['sort'];
                            if(preg_match('/goodsPrice|orderCnt|hitCnt/', $getData['sort'])){
                                $sortType = explode(" ", $getData['sort']);
                                $mainOrder .= ', g.goodsNo '.$sortType[1];
                            }
                        }
                        else {
                            $mainOrder = "FIELD(g.goodsNo," . str_replace(INT_DIVISION, ",", $goodsNoData) . ")";
                        }
                        if ($themeInfo['soldOutDisplayFl'] == 'n') $mainOrder = "soldOut asc," . $mainOrder;
                        $goods->setThemeConfig($themeInfo);
                        $goodsData = $goods->getGoodsSearchList($displayCnt, $mainOrder, $imageType, $optionFl, $soldOutFl, $brandFl, $couponPriceFl, $displayCnt);
                        Request::get()->del('goodsNo');

                    }
                }

                if(!$goodsData) {
                    $goodsData['multiple'] = ['10'];
                }

                Request::get()->del('goodsNo');

            } else {
                $mainOrder = $getData['sort'];
                if ($themeInfo['soldOutDisplayFl'] == 'n') $mainOrder = "soldOut asc,".$mainOrder ;
                Request::get()->set('offsetGoodsNum','500');
                if($getData['exceptGoodsNo']) Request::get()->set('exceptGoodsNo',explode(INT_DIVISION, $getData['exceptGoodsNo']));
                if($getData['exceptCateCd']) Request::get()->set('exceptCateCd',explode(INT_DIVISION, $getData['exceptCateCd']));
                if($getData['exceptBrandCd']) Request::get()->set('exceptBrandCd',explode(INT_DIVISION, $getData['exceptBrandCd']));
                if($getData['exceptScmNo']) Request::get()->set('exceptScmNo',explode(INT_DIVISION, $getData['exceptScmNo']));

                $goods->setThemeConfig($themeInfo);
                $goodsData = $goods->getGoodsSearchList($displayCnt, $mainOrder, $imageType, $optionFl, $soldOutFl, $brandFl, $couponPriceFl ,$displayCnt );

                Request::get()->del('exceptGoodsNo');
                Request::get()->del('exceptCateCd');
                Request::get()->del('exceptBrandCd');
                Request::get()->del('exceptScmNo');
            }

            if($themeInfo['displayType'] =='07') {
                //상품할인가 설정
                if (in_array('goodsDcPrice', $themeInfo['displayField'])) {
                    foreach ($goodsData as $k => $v) {
                        foreach ($v as $k2 => $v2) {
                            foreach ($v2 as $k3 => $v3) {
                                $goodsData[$k][$k2][$k3]['goodsDcPrice'] = $goods->getGoodsDcPrice($v3);
                            }
                        }
                    }
                }

                $this->setData('goodsList',$goodsData);
                $this->setData('goodsCnt', "");

                //탭세팅정보
                $tabConfig['count'] =  $themeInfo['detailSet'][0];
                $tabConfig['direction'] =  $themeInfo['detailSet'][1];
                unset($themeInfo['detailSet'][0]);
                unset($themeInfo['detailSet'][1]);
                $tabConfig['title'] = $themeInfo['detailSet'];

                $this->setData('tabConfig', $tabConfig);
            } else {
                if($goodsData['listData']) $goodsList = array_chunk($goodsData['listData'],$themeInfo['lineCnt']);
                unset($goodsData['listData']);

                // 카테고리 노출항목 중 상품할인가
                if (in_array('goodsDcPrice', $themeInfo['displayField'])) {
                    foreach ($goodsList as $key => $val) {
                        foreach ($val as $key2 => $val2) {
                            $goodsList[$key][$key2]['goodsDcPrice'] = $goods->getGoodsDcPrice($val2);
                        }
                    }
                }
                $this->setData('goodsList', $goodsList);
            }

            $page = \App::load('\\Component\\Page\\Page'); // 페이지 재설정
            $this->setData('goodsCnt', $page->recode['total']);
            $this->setData('totalPage', gd_isset($page->page['total'],1));

            if ($themeInfo['displayType'] =='04' || $themeInfo['displayType'] =='06') {
                $realGoodsCnt = $page->recode['total'] > $themeInfo['lineCnt'] * $themeInfo['rowCnt'] ? $themeInfo['lineCnt'] * $themeInfo['rowCnt'] : $page->recode['total'];
                $this->setData('realGoodsCnt', $realGoodsCnt);
                $this->setData('goodsCnt', $realGoodsCnt);
            }

            if($getData['imageNm']) {
                $getData['themeNmText'] = $getData['themeNm'];
                $getData['themeNm'] = "<img src='".UserFilePath::data('display')->www().DS.$getData['imageNm']."'>";
            }

            $mileage = gd_mileage_give_info();
            $this->setData('mileageData', gd_isset($mileage['info']));

            // 장바구니 설정
            if ($themeInfo['displayType'] == '11') {
                $cartInfo = gd_policy('order.cart');
                $this->setData('cartInfo', gd_isset($cartInfo));
            }

            $this->setData('mainData', $getData);
            $this->setData('themeInfo', $themeInfo);
            if (gd_is_login()) {
                $loginFl = 't';
            } else {
                $loginFl = 'f';
            }
            $this->setData('loginFl', $loginFl);
            $this->getView()->setDefine('goodsTemplate', 'goods/list/list_' . $themeInfo['displayType'] . '.html');

            unset($getData);
            unset($goodsList);
            unset($themeInfo);
        } else {
            $this->setData('mainData', $getData);
            $this->getView()->setDefine('goodsTemplate', 'goods/list/list_01.html');
        }
    }
}
