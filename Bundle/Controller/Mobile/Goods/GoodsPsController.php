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
namespace Bundle\Controller\Mobile\Goods;

use Component\Board\BoardList;
use Framework\Utility\GodoUtils;
use Message;
use Request;
use Framework\Debug\Exception\AlertCloseException;
use Framework\Debug\Exception\AlertBackException;
use League\Flysystem\Exception;

/**
 * 상품 상세 페이지 처리
 *
 * @author artherot
 * @version 1.0
 * @since 1.0
 * @copyright Copyright (c), Godosoft
 */
class GoodsPsController extends \Controller\Mobile\Controller
{
    /**
     * {@inheritdoc}
     *
     * @author Jong-tae Ahn <qnibus@godo.co.kr>
     */
    public function index()
    {
        // --- 각 배열을 trim 처리
        $postValue = Request::request()->toArray();
        //$postValue = Request::get()->toArray();

        // --- 각 모드별 처리
        switch ($postValue['mode']) {
            // 옵션 선택
            case 'option_select':
                try {
                    ob_start();

                    // --- 상품 class
                    $goods = \App::load('\\Component\\Goods\\Goods');

                    $result = $goods->getGoodsOptionSelect($postValue['goodsNo'], $postValue['optionVal'], $postValue['optionKey'], $postValue['mileageFl']);

                    if ($out = ob_get_clean()) {
                        throw new Except('ECT_SAVE_FAIL', $out);
                    }
                    $result['log'] = 'ok';
                    echo json_encode($result);
                    exit;
                } catch (Exception $e) {
                    if ($e->ectName == 'ERROR_VIEW') {
                        $setData['log'] = __('안내') . chr(10) . gd_isset($__text[$e->ectMessage], $e->ectMessage);
                    } else {
                        $e->actLog();
                        $setData['log'] = __('오류') . chr(10) . $__text['_FAIL_'];
                    }
                    echo json_encode($setData);
                }

                break;
            //오늘 본 상품 삭제
            case 'delete_today_goods':
                try {
                    // --- 상품 class
                    $goods = \App::load('\\Component\\Goods\\Goods');
                    if (is_array($postValue['goodsNo'])) {
                        foreach ($postValue['goodsNo'] as $k => $v) {
                            $goods->removeTodayViewedGoods($v);
                        }
                    } else {
                        $goods->removeTodayViewedGoods($postValue['goodsNo']);
                    }
                    exit;

                } catch (Exception $e) {
                    $this->json([
                        'code' => '200',
                        'message' => $e->getMessage()
                    ]);
                }

                break;
            //최근검색어 삭제
            case 'delete_recent_keyword':
                try {
                    // --- 상품 class
                    $goods = \App::load('\\Component\\Goods\\Goods');
                    $goods->removeRecentKeyword($postValue['keyword']);
                    exit;

                } catch (Exception $e) {
                    $this->json([
                        'code' => '200',
                        'message' => $e->getMessage()
                    ]);
                }

                break;
            // 최근검색어 전체 삭제
            case 'delete_recent_all_keyword':
                try {
                    // --- 상품 class
                    $goods = \App::load('\\Component\\Goods\\Goods');
                    $goods->removeRecentAllKeyword();
                    exit;

                } catch (Exception $e) {
                    $this->json([
                        'code' => '200',
                        'message' => $e->getMessage()
                    ]);
                }

                break;
            //상품 상세 할인 / 혜택 계산
            case 'get_benefit':
                try {
                    // --- 상품 class
                    $cart = \App::load('\\Component\\Cart\\Cart');
                    $setData = $cart->goodsViewBenefit($postValue);
                    echo json_encode($setData);
                    exit;
                } catch (Exception $e) {
                    echo json_encode(array('message' => $e->getMessage()));
                }

                break;
            // 브랜드 가져오기
            case 'get_brand':
                try {
                    // --- 상품 class
                    $brand = \App::load('\\Component\\Category\\Brand');
                    $cateNm = $postValue['brand'];
                    $getData = $brand->getBrandCodeInfo(null, 4, $cateNm, false, null, true);

                    echo json_encode($getData);
                    exit;
                } catch (Exception $e) {
                    echo json_encode(array('message' => $e->getMessage()));
                }

                break;
            //상품 목록
            case 'get_list':
                try {
                    $goods = \App::load('\\Component\\Goods\\Goods');

                    if ($postValue['cateType'] =='brand') {
                        $cate = \App::load('\\Component\\Category\\Brand');
                        $cateMode = "brand";
                    } else {
                        $cateMode = "category";
                        $cate = \App::load('\\Component\\Category\\Category');
                    }

                    Request::get()->set('page', $postValue['page']);
                    Request::get()->set('sort', $postValue['sort']);

                    $cateCd = $postValue['cateCd'];
                    $themeCd = $postValue['themeCd'];

                    if (empty($themeCd) === true) {
                        $displayConfig = \App::load('\\Component\\Display\\DisplayConfig');
                        $cateInfo = $displayConfig->getInfoThemeConfigCate('C', 'y')[0];
                        $cateInfo['displayField'] = explode(",", $cateInfo['displayField']);
                    } else {
                        $cateInfo = $cate->getCategoryGoodsList($cateCd, "y");
                    }

                    if($cateInfo['soldOutDisplayFl'] =='n')  $displayOrder[] = "soldOut asc";

                    if($cateInfo['sortAutoFl'] =='y' ) $displayOrder[] = "gl.fixSort desc,".gd_isset($cateInfo['sortType'],'g.regDt desc').",g.goodsNo desc";
                    else $displayOrder[] = "gl.fixSort desc, gl.goodsSort desc";

                    // 상품 정보
                    $displayCnt = gd_isset($cateInfo['lineCnt']) * gd_isset($cateInfo['rowCnt']);
                    $pageNum = gd_isset($postValue['pageNum'], $displayCnt);
                    $optionFl = in_array('option', array_values($cateInfo['displayField'])) ? true : false;
                    $soldOutFl = (gd_isset($cateInfo['soldOutFl']) == 'y' ? true : false); // 품절상품 출력 여부
                    $brandFl = in_array('brandCd', array_values($cateInfo['displayField'])) ? true : false;
                    $couponPriceFl = in_array('coupon', array_values($cateInfo['displayField'])) ? true : false;     // 쿠폰가 출력 여부
                    $goodsData = $goods->getGoodsList($cateCd, $cateMode, $pageNum, $displayOrder, gd_isset($cateInfo['imageCd']), $optionFl, $soldOutFl, $brandFl, $couponPriceFl, null, $displayCnt);

                    if ($goodsData['listData']) {
                        $goodsList = array_chunk($goodsData['listData'], $cateInfo['lineCnt']);
                    }
                    unset($goodsData['listData']);
                    //품절상품 설정
                    $soldoutDisplay = gd_policy('soldout.mobile');

                    $setData = array('cateInfo' => $cateInfo,'soldoutDisplay' => $soldoutDisplay,'goodsList' => $goodsList);
                    echo json_encode($setData);
                    exit;

                } catch (Exception $e) {
                    echo json_encode(array('message' => $e->getMessage()));
                }
                break;
            //상품 검색
            case 'get_search_list':
                try {
                    // 모듈 설정
                    $goods = \App::load('\\Component\\Goods\\Goods');

                    Request::get()->set('page', $postValue['page']);
                    Request::get()->set('sort', $postValue['sort']);
                    Request::get()->set('keyword', $postValue['keyword']);
                    Request::get()->set('key', 'goodsNm'); //상품명검색


                    //설정
                    $goodsConfig = gd_policy('search.goods');


                    //테마정보
                    $displayConfig = \App::load('\\Component\\Display\\DisplayConfig');
                    $themeInfo = $displayConfig->getInfoThemeConfig($goodsConfig['mobileThemeCd']);
                    $themeInfo['displayField'] = explode(",", $themeInfo['displayField']);

                    $displayCnt = gd_isset($themeInfo['lineCnt']) * gd_isset($themeInfo['rowCnt']);
                    $pageNum = gd_isset($postValue['pageNum'], $displayCnt);
                    $optionFl = in_array('option', array_values($themeInfo['displayField'])) ? true : false;
                    $soldOutFl = (gd_isset($themeInfo['soldOutFl']) == 'y' ? true : false); // 품절상품 출력 여부
                    $brandFl = in_array('brandCd', array_values($themeInfo['displayField'])) ? true : false;
                    $couponPriceFl = in_array('coupon', array_values($themeInfo['displayField'])) ? true : false;     // 쿠폰가 출력 여부
                    $brandDisplayFl = in_array('brand', array_values($goodsConfig['searchType'])) ? true : false;     // 브랜드 출력여부


                    if ($themeInfo['soldOutDisplayFl'] == 'n') {
                        $goodsConfig['sort'] = "g.soldOutFl desc," . $goodsConfig['sort'];
                    }


                    // 최근 본 상품 진열
                    $goodsData = $goods->getGoodsSearchList($pageNum, gd_isset($goodsConfig['sort']), gd_isset($themeInfo['imageCd']), $optionFl, $soldOutFl, $brandFl, $couponPriceFl, $displayCnt, $brandDisplayFl);
                    if ($goodsData['listData']) {
                        $goodsList = array_chunk($goodsData['listData'], $themeInfo['lineCnt']);
                    }

                    //품절상품 설정
                    $soldoutDisplay = gd_policy('soldout.mobile');

                    $pager = \App::load('\\Component\\Page\\Page'); // 페이지 재설정


                    $setData = array('soldoutDisplay' => $soldoutDisplay,'goodsList' => $goodsList,'total' => $pager->recode['total']);
                    echo json_encode($setData);
                    exit;

                } catch (Exception $e) {
                    echo json_encode(array('message' => $e->getMessage()));
                }

                break;
            //게시글 가져오기
            case 'get_board':
                try {
                    gd_isset($postValue['page'], 1);
                    $boardList = new BoardList($postValue);
                    $getData = $boardList->getList(false, 3);
                    echo json_encode($getData['data']);
                    exit;
                } catch (Exception $e) {
                    $this->json([
                        'code' => '200',
                        'message' => $e->getMessage()
                    ]);
                }

                break;

            // 단축주소 가져오기
            case 'get_short_url':
                try {
                    $url1 = explode('?', $postValue['url']);
                    $url2 = explode('&', $url1[1]);
                    $url = $url1[0].'?';
                    foreach($url2 as $val) {
                        if(strpos($val, 'goodsNo') !== false) {
                            $url .= $val;
                            break;
                        }
                    }

                    $shortUrl = GodoUtils::shortUrl($url);
                    echo json_encode(['url' => urldecode($shortUrl)]);
                    exit;
                } catch (Exception $e) {
                    echo json_encode(array('message' => $e->getMessage()));
                }

                break;

            // 메인페이지 더보기
            case 'get_main':
                try {

                    Request::get()->set('isMain',true);
                    Request::get()->set('sort', $postValue['sort']);

                    $this->setData('soldoutDisplay', gd_policy('soldout.mobile'));
                    gd_isset($postValue['page'],1);

                    $goods = \App::load('\\Component\\Goods\\Goods');
                    $getData = $goods->getDisplayThemeInfo($postValue['mainSno']);
                    $mainLinkData = [
                        'mainThemeSno' => $getData['sno'],
                        'mainThemeNm' => $getData['themeNm'],
                        'mainThemeDevice' => $getData['mobileFl'],
                    ];
                    Request::get()->set('mainLinkData',$mainLinkData);
                    //기획전 그룹형 그룹정보 로드
                    if((int)$postValue['groupSno'] > 0) {
                        $eventGroup = \App::load('\\Component\\Promotion\\EventGroupTheme');
                        $getData = $eventGroup->replaceEventData($postValue['groupSno'], $getData);
                    }

                    if ($getData['kind'] !='main') {
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
                    if (empty($postValue['displayType']) === false) {
                        $themeInfo['displayType'] = $postValue['displayType'];
                    }

                    if($getData && $getData['displayFl'] =='y') {

                        if ($themeInfo['detailSet']) $themeInfo['detailSet'] = unserialize($themeInfo['detailSet']);
                        $themeInfo['displayField'] = explode(",", $themeInfo['displayField']);
                        $themeInfo['goodsDiscount'] = explode(",", $themeInfo['goodsDiscount']);
                        $themeInfo['priceStrike'] = explode(",", $themeInfo['priceStrike']);
                        $themeInfo['displayAddField'] = explode(",", $themeInfo['displayAddField']);
                        if($postValue['displayType']) $displayCnt = 10*$postValue['more'];
                        else $displayCnt = (gd_isset($themeInfo['lineCnt']) * gd_isset($themeInfo['rowCnt']))*$postValue['more'];

                        $imageType = gd_isset($themeInfo['imageCd'], 'main');                        // 이미지 타입 - 기본 'main'
                        $soldOutFl = $themeInfo['soldOutFl'] == 'y' ? true : false;            // 품절상품 출력 여부 - true or false (기본 true)
                        $brandFl = in_array('brandCd', array_values($themeInfo['displayField'])) ? true : false;    // 브랜드 출력 여부 - true or false (기본 false)
                        $couponPriceFl = in_array('coupon', array_values($themeInfo['displayField'])) ? true : false;        // 쿠폰가격 출력 여부 - true or false (기본 false)
                        $optionFl = in_array('option', array_values($themeInfo['displayField'])) ? true : false;

                        $goodsNoData = implode(INT_DIVISION,array_filter(explode(STR_DIVISION,  $getData['goodsNo'])));
                        if($getData['sortAutoFl'] =='n') {
                            if($themeInfo['displayType'] =='07') {

                                $goodsNoData =explode(STR_DIVISION,  $getData['goodsNo']);
                                foreach($goodsNoData as $key => $value) {
                                    if($value) {
                                        Request::get()->set('goodsNo',explode(INT_DIVISION,$value));
                                        $mainOrder = "FIELD(g.goodsNo," . str_replace(INT_DIVISION, ",", $value) . ")";
                                        if ($themeInfo['soldOutDisplayFl'] == 'n') $mainOrder = "soldOut asc," . $mainOrder;
                                        $goods->setThemeConfig($themeInfo);
                                        $tmpGoodsList = $goods->getGoodsSearchList($displayCnt , $mainOrder, $imageType, $optionFl, $soldOutFl, $brandFl, $couponPriceFl  ,$displayCnt);
                                        $goodsData[] =  array_chunk($tmpGoodsList['listData'],$themeInfo['lineCnt']);
                                        Request::get()->del('goodsNo');
                                    }
                                }

                            } else {
                                if ($getData['goodsNo'] && $goodsNoData) {
                                    Request::get()->set('goodsNo', explode(INT_DIVISION, $goodsNoData));
                                    // MOBILE 기획전 일반형/그룹형 정렬
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
                            $goodsData = $goods->getGoodsSearchList($displayCnt, $mainOrder, $imageType, $optionFl, $soldOutFl, $brandFl, $couponPriceFl ,$displayCnt);
                            Request::get()->del('exceptGoodsNo');
                            Request::get()->del('exceptCateCd');
                            Request::get()->del('exceptBrandCd');
                            Request::get()->del('exceptScmNo');
                        }

                        if($themeInfo['displayType'] =='07') {
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

                            $this->setData('goodsList', $goodsList);
                        }
                        $page = \App::load('\\Component\\Page\\Page'); // 페이지 재설정
                        $this->setData('goodsCnt', $page->recode['total']);

                        // 카테고리 노출항목 중 상품할인가
                        if (in_array('goodsDcPrice', $themeInfo['displayField'])) {
                            foreach ($goodsList as $key => $val) {
                                foreach ($val as $key2 => $val2) {
                                    $goodsList[$key][$key2]['goodsDcPrice'] = $goods->getGoodsDcPrice($val2);
                                }
                            }
                        }
                        $this->setData('goodsList', gd_isset($goodsList));

                        $mileage = gd_mileage_give_info();
                        $this->setData('mileageData', gd_isset($mileage['info']));

                        // 장바구니 설정
                        if ($themeInfo['displayType'] == '11') {
                            $cartInfo = gd_policy('order.cart');
                            $this->setData('cartInfo', gd_isset($cartInfo));
                        }

                        $this->setData('mainData', $getData);
                        $this->setData('themeInfo', $themeInfo);
                        echo "<input type='hidden' name='totalPage' value='". gd_isset($page->page['total'],1)."'>";
                        gd_isset($postValue['displayType'],$themeInfo['displayType']);
                        $this->getView()->setPageName( 'goods/list/list_' . $postValue['displayType']);

                        unset($getData);
                        unset($goodsList);
                        unset($themeInfo);
                    } else {
                        $this->setData('mainData', $getData);
                        $this->getView()->setPageName( 'goods/list/list_01');
                    }

                } catch (Exception $e) {
                    echo json_encode(array('message' => $e->getMessage()));
                }

                break;

            // 메인페이지 더보기
            case 'get_goods_recom':
                try {

                    $this->setData('soldoutDisplay', gd_policy('soldout.mobile'));

                    $goods = \App::load('\\Component\\Goods\\Goods');
                    if($postValue['cateType'] =='brand') $cate = \App::load('\\Component\\Category\\Brand');
                    else $cate = \App::load('\\Component\\Category\\Category');

                    Request::get()->set('sort', $postValue['sort']);

                    // 카테고리 정보
                    $cateInfo = $cate->getCategoryGoodsList($postValue['cateCd'],'y');

                    if (empty($cateInfo['recomMobileThemeCd']) === true) {
                        $displayConfig = \App::load('\\Component\\Display\\DisplayConfig');
                        $cateInfo['recomTheme'] = $displayConfig->getInfoThemeConfigCate('D','y')[0];
                    }

                    $recomTheme = $cateInfo['recomTheme'];
                    if ($recomTheme['detailSet']) {
                        $recomTheme['detailSet'] = unserialize($recomTheme['detailSet']);
                    }

                    gd_isset($recomTheme['lineCnt'],4);
                    gd_isset($postValue['displayType'],$recomTheme['displayType']);
                    $recomTheme['goodsDiscount'] = explode(",", $recomTheme['goodsDiscount']);
                    $recomTheme['priceStrike'] = explode(",", $recomTheme['priceStrike']);
                    $recomTheme['displayAddField'] = explode(",", $recomTheme['displayAddField']);
                    $displayCnt = 10*$postValue['more'];

                    $imageType		= gd_isset($recomTheme['imageCd'],'list');						// 이미지 타입 - 기본 'main'
                    $soldOutFl		= $recomTheme['soldOutFl'] == 'y' ? true : false;			// 품절상품 출력 여부 - true or false (기본 true)
                    $brandFl		= in_array('brandCd',array_values($recomTheme['displayField']))  ? true : false;	// 브랜드 출력 여부 - true or false (기본 false)
                    $couponPriceFl	= in_array('coupon',array_values($recomTheme['displayField']))  ? true : false;		// 쿠폰가격 출력 여부 - true or false (기본 false)
                    $optionFl = in_array('option',array_values($recomTheme['displayField']))  ? true : false;

                    if($cateInfo['recomSortAutoFl'] =='y') $recomOrder = $cateInfo['recomSortType'].",g.goodsNo desc";
                    else $recomOrder = "FIELD(g.goodsNo," . str_replace(INT_DIVISION, ",", $cateInfo['recomGoodsNo']) . ")";
                    if ($recomTheme['soldOutDisplayFl'] == 'n') $recomOrder = "soldOut asc," . $recomOrder;

                    Request::get()->set('goodsNo',explode(INT_DIVISION,$cateInfo['recomGoodsNo']));
                    $goods->setThemeConfig($recomTheme);
                    $goodsData = $goods->getGoodsSearchList($displayCnt , $recomOrder, $imageType, $optionFl, $soldOutFl, $brandFl, $couponPriceFl  ,$displayCnt);
                    Request::get()->del('goodsNo');

                    if($goodsData['listData']) $goodsList = array_chunk($goodsData['listData'],$recomTheme['lineCnt']);
                    $page = \App::load('\\Component\\Page\\Page'); // 페이지 재설정
                    unset($goodsData['listData']);

                    $mileage = gd_mileage_give_info();
                    $this->setData('mileageData', gd_isset($mileage['info']));

                    $this->setData('goodsList', $goodsList);
                    $this->setData('goodsCnt', $page->recode['total']);


                    $this->setData('themeInfo', $recomTheme);
                    echo "<input type='hidden' name='totalPage' value='". gd_isset($page->page['total'],1)."'>";
                    $this->getView()->setPageName( 'goods/list/list_' . $postValue['displayType']);

                    unset($getData);
                    unset($goodsList);
                    unset($themeInfo);


                } catch (Exception $e) {
                    echo json_encode(array('message' => $e->getMessage()));
                }
                break;

            //재입고 알림 신청
            case 'save_restock' :
                try {
                    if (gd_is_plus_shop(PLUSSHOP_CODE_RESTOCK) !== true) {
                        throw new Exception("[플러스샵] 미설치 또는 미사용 상태입니다. 설치 완료 및 사용 설정 후 플러스샵 앱을 사용할 수 있습니다.");
                    }

                    $duplicationFl = array();
                    $goods = \App::load('\\Component\\Goods\\Goods');

                    $goodsData = $goods->getGoodsView($postValue['goodsNo']);
                    $useAble = $goods->setRestockUsableFl($goodsData);
                    if($useAble !== 'y'){
                        throw new Exception("재입고 신청을 할 수 없는 상태의 상품입니다.", 2);
                    }

                    //옵션이 있을시
                    if(count($postValue['restock_option']) > 0){
                        //옵션정보가 변경되지 않았는지 체크
                        foreach($postValue['restock_option'] as $key => $value){
                            list($checkOptionSno) = explode("@|@", $value);

                            $checkOptionData = $goods->getGoodsOptionInfo($checkOptionSno);
                            if(count($checkOptionData) < 1 || (int)$checkOptionData['sno'] < 1){
                                throw new Exception("옵션 정보가 변경되었습니다.\n다시 시도해 주세요.", 1);
                            }
                        }

                        foreach($postValue['restock_option'] as $key => $value){
                            $postValue['optionSno'] = $postValue['optionValue'] = '';
                            list($postValue['optionSno'], $postValue['optionValue']) = explode("@|@", $value);

                            //diffKey 생성
                            $postValue['diffKey'] = $goods->setGoodsRestockDiffKey($postValue);

                            //중복건 체크
                            $duplicationRestock = $goods->checkDuplicationRestock($postValue);
                            if($duplicationRestock === true){
                                $duplicationFl[] = 'y';
                                continue;
                            }

                            //저장
                            $insertId = $goods->saveGoodsRestock($postValue);
                            if(!$insertId){
                                throw new Exception("신청을 실패했습니다.\n고객센터에 문의해 주세요.", 2);
                                break;
                            }
                        }

                        if(count($duplicationFl) > 0){
                            if(count($duplicationFl) === count($postValue['restock_option'])){
                                throw new Exception("이미 재입고 신청이 된 상품입니다.", 1);
                            }
                            else {
                                throw new AlertCloseException(__("중복 신청건을 제외한 재입고 알림이 신청되었습니다."));
                            }
                        }
                    }
                    else {
                        //diffKey 생성
                        $postValue['diffKey'] = $goods->setGoodsRestockDiffKey($postValue);

                        //중복건 체크
                        $duplicationRestock = $goods->checkDuplicationRestock($postValue);
                        if($duplicationRestock === true){
                            throw new Exception("이미 재입고 신청이 된 상품입니다.", 1);
                        }
                        //옵션이 없을시
                        $insertId = $goods->saveGoodsRestock($postValue);
                        if(!$insertId){
                            throw new Exception("신청을 실패했습니다.\n고객센터에 문의해 주세요.", 2);
                        }
                    }

                    throw new AlertCloseException(__("재입고 알림이 신청되었습니다."));
                } catch (\Exception $e) {
                    if($e->getCode() === 1){
                        throw new AlertBackException(__($e->getMessage()));
                    }
                    else {
                        throw new AlertCloseException(__($e->getMessage()));
                    }
                }
                break;

            // 장바구니 사용 상태의 쿠폰 적용 취소처리
            case 'UserCartCouponDel':
                // --- 상품 class
                $cart = \App::load('\\Component\\Cart\\Cart');
                $cartInfo = $cart->getCartGoodsData();

                // 장바구니에 사용된 쿠폰의 유효성 체크
                if($cartInfo > 0) {
                    $reSetMemberCouponApply = false;
                    foreach ($cartInfo as $key => $value) {
                        foreach ($value as $key1 => $value1) {
                            foreach ($value1 as $key2 => $value2) {
                                if ($value2['memberCouponNo']) {
                                    $memberCouponNoArr = explode(INT_DIVISION, $value2['memberCouponNo']);
                                    foreach ($memberCouponNoArr as $memberCouponNo) {
                                        if ($memberCouponNo == $postValue['memberCouponNo']) {
                                            $cart->setMemberCouponDelete($value2['sno']); // 상품적용 쿠폰 제거
                                        } else {
                                            $reSetMemberCouponApply = true;
                                            $couponApply[$value2['sno']]['couponApplyNo'][] = $memberCouponNo;
                                        }
                                    }
                                }
                            }
                        }
                    }

                    // 사용가능한 쿠폰만 다시 적용
                    if($reSetMemberCouponApply) {
                        foreach ($couponApply as $cartKey => $couponApplyInfo) {
                            // 상품적용 쿠폰 적용 / 변경
                            $couponApplyNo = implode(INT_DIVISION, $couponApplyInfo['couponApplyNo']);
                            $cart->setMemberCouponApply($cartKey, $couponApplyNo);
                        }
                    }
                }
                break;

            // 쿠폰상태 확인 및 상태값 변경
            case 'checkCouponType':
                $getValue = Request::post()->toArray();
                if($getValue['mileageGiveFl']) {
                    $mileageGive = gd_policy('member.mileageGive');
                    if($mileageGive['giveFl'] == 'n') {
                        $this->json(array('isSuccess'=>false));
                        break;
                    }
                }
                // 모듈 호출
                $couponAdmin = \App::load('\\Component\\Coupon\\CouponAdmin');
                $return = $couponAdmin->checkCouponTypeArr($getValue['couponNo']);
                $this->json(array('isSuccess'=>$return));
                break;


            // 쿠폰상태 확인 및 상태값 변경
            case 'goodsCheckCouponTypeArr':
                $coupon = \App::load('\\Component\\Coupon\\Coupon');
                $return = $coupon->goodsCheckCouponTypeArr($postValue['couponNo']);
                $this->json(array('isSuccess'=>$return['result'], 'setCouponApplyNo'=>$return['setCouponApplyNo']));
                break;
        }

        if($postValue['mode'] == 'openerReload'){
            $this->js('opener.updateBoard("'.$postValue['bdId'].'");self.close();');
        }
    }
}
