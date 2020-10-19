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
use Framework\Debug\Exception\LayerException;
use Message;
use Request;
use Framework\Utility\ImageUtils;

class DisplayConfigPsController extends \Controller\Admin\Controller
{

    /**
     * 상품 노출형태  처리 페이지
     * [관리자 모드] 상품 노출형태  관련 처리 페이지
     *
     * @author artherot
     * @version 1.0
     * @since 1.0
     * @throws Except
     * @throws LayerException
     * @copyright ⓒ 2016, NHN godo: Corp.
     */
    public function index()
    {
        $postValue = Request::post()->toArray();

        // --- 상품노출 class
        $display = \App::load('\\Component\\Display\\DisplayConfigAdmin');
        try {

            switch ($postValue['mode']) {
                // 테마 등록 / 수정
                case 'theme_register':
                case 'theme_modify':

                    $display->saveInfoThemeConfig($postValue);


                    $this->layer(__('저장이 완료되었습니다.'));

                    break;
                case 'theme_register_ajax':
                case 'theme_modify_ajax':
                    try {

                        $themeCd = $display->saveInfoThemeConfig($postValue);

                        echo json_encode(array("state"=>true,"themeCd"=>$themeCd,'themeNm'=>$postValue['themeNm'],"msg"=>__('저장이 완료되었습니다.')));

                    } catch (Except $e) {

                        echo json_encode(array("state"=>false,'msg'=>__('처리중에 오류가 발생하여 실패되었습니다.')));
                    }
                    break;
                // 테마 삭제
                case 'theme_delete':
                    // 삭제

                    if (empty(Request::post()->get('themeCd')) === false) {

                            $display->deleteThemeConfig(Request::post()->get('themeCd'));

                    }

                    unset($postArray);

                    $this->layer(__('삭제 되었습니다.'));

                    break;
                // 테마 json목록
                case 'theme_ajax':

                        $data = $display->getInfoThemeConfig($postValue['themeCd']);

                        $confImage = gd_policy('goods.image');
                        ImageUtils::sortImageConf($confImage, array('detail', 'magnify'));

                        $result['themeNm']= $data['themeNm'];
                        $result['themeCd']= $data['themeCd'];
                        $result['imageCdNm'] = $confImage[$data['imageCd']]['text'].' '.$confImage[$data['imageCd']]['size1'].'pixel';
                        $result['cntNm'] = __('가로') . ' : '.$data['lineCnt'].' X  ' . __('세로') . ' : '.$data['rowCnt'];

                        $result['soldOutFlNm']  = $data['soldOutFl'] =='n' ? __('아니요') : __('예');
                        $result['soldOutIconFlNm']  = $data['soldOutIconFl'] =='n' ? __('아니요') : __('예');
                        $result['iconFlNm']  = $data['iconFl'] =='n' ? __('아니요') : __('예');
                        $result['displayTypeNm']= $display->themeDisplayType[$data['displayType']]['name'];
                        $result['soldOutDisplayFlNm']  = $data['soldOutDisplayFl'] =='n' ? __('리스트 끝으로 보내기') : __('정렬 순서대로 보여주기');

                        //탭형인 경우 세팅필요
                        if($data['displayType'] =='07') {
                            $detailSet = unserialize($data['detailSet']);
                            $result['tabConfig'] = unserialize($data['detailSet']);
                        }

                        $displayFieldArr = explode(",",$data['displayField']);
                        foreach($displayFieldArr as $k => $v) {
                            $displayField[] = $display->themeDisplayField[$v];
                        }

                        $result['displayFieldNm'] = implode(',',$displayField);

                        echo json_encode($result);
                        exit;

                    break;
                //  메뉴 레이어 등록
                case 'memuLayer_register':


                    $display->saveInfoDisplayMenuLayer($postValue);

                    unset($postArray);

                    $this->layer(__('저장이 완료되었습니다.'));
                    break;
                //  네비 등록
                case 'navi_register':

                    $display->saveInfoDisplayNavi($postValue);

                    unset($postArray);

                    $this->layer(__('저장이 완료되었습니다.'));

                    break;
                //  관련상품 노출설정 등록
                case 'relation_register':

                        $display->saveInfoDisplayRelation($postValue);

                        unset($postArray);

                        $this->layer(__('저장이 완료되었습니다.'));

                    break;
                //  상품상세 노출항목 설정
                case 'goods_register':

                    $display->saveInfoDisplayGoods($postValue);

                    unset($postArray);

                    $this->layer(__('저장이 완료되었습니다.'));

                    break;

                //  상품상세 노출항목 설정
                case 'select_goods_field':

                    $field = $display->goodsDisplayField;

                    if($postValue['sort'] =='desc')  array_multisort($field,SORT_ASC);
                    if($postValue['sort'] =='asc') array_multisort($field,SORT_DESC);

                    echo json_encode($field);
                    exit;

                    break;
                case 'recom_goods':
                    $recom = \App::load('\\Component\\Goods\\RecommendGoods');

                    $goodsNoData = $postValue['recomGoods'];
                    unset($postValue['recomGoods']);
                    unset($postValue['mode']);

                    if (count($goodsNoData) > RECOMMENDGOODS::DEFAULT_RECOMMEND_GOODS_CNT) {
                        $this->layer('추천 상품은 최대 ' . RECOMMENDGOODS::DEFAULT_RECOMMEND_GOODS_CNT . '개 까지만 선택 가능합니다.');
                    }

                    foreach (['goodsDiscount', 'priceStrike', 'displayAddField'] as $val) {
                        if (empty($postValue[$val]) === true) {
                            $postValue[$val] = '';
                        }
                    }

                    gd_set_policy('goods.recom', $postValue);

                    $result = $recom->save($goodsNoData);
                    if ($result === true) {
                        $this->layer('저장되었습니다.');
                    } else {
                        $this->layer('저장에 실패했습니다. 재시도 해주세요.');
                    }
                    break;
                case 'recom_del':
                    $recom = \App::load('\\Component\\Goods\\RecommendGoods');
                    $result = $recom->del($postValue['del']);

                    echo json_encode($result);
                    break;
            }

        } catch (Exception $e) {
            throw new LayerException($e->getMessage());
        }

    }
}
