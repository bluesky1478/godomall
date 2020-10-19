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

use Framework\Debug\Exception\LayerException;
use Message;
use Request;

class AddGoodsPsController extends \Controller\Admin\Controller
{

    /**
     * 추가상품  처리 페이지
     * [관리자 모드] 추가상품  관련 처리 페이지
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
        $addGoods = \App::load('\\Component\\Goods\\AddGoodsAdmin');

        try {

            switch ($postValue['mode']) {
                // 상품 등록 / 수정
                case 'register':
                case 'register_ajax':
                case 'modify':

                        $fileValue = Request::files()->toArray();
                        $saveCnt = 0;
                        if (gd_isset($postValue['optionNm'])) {

                            if($postValue['scmNoNm'] =='') {
                                $scmAdmin = \App::load('\\Component\\Scm\\ScmAdmin');
                                $tmpData = $scmAdmin->getScmInfo($postValue['scmNo'], 'companyNm');
                                $postValue['scmNoNm'] = $tmpData['companyNm'];
                            }

                            foreach ($postValue['goodsPrice'] as $k => $v) {
                                if (trim($v) != '') {
                                    $imgData = array();

                                    if (empty($fileValue['imageNm']['name'][$k]) === false) {
                                        $imgData = array(
                                            'name' => $fileValue['imageNm']['name'][$k],
                                            'type' => $fileValue['imageNm']['type'][$k],
                                            'tmp_name' => $fileValue['imageNm']['tmp_name'][$k],
                                            'error' => $fileValue['imageNm']['error'][$k],
                                            'size' => $fileValue['imageNm']['size'][$k]
                                        );
                                    }

                                    $arrData = array(
                                        'mode' => $postValue['mode'],
                                        'applyFl' => $postValue['applyFl'],
                                        'addGoodsNo' => gd_isset($postValue['addGoodsNo']),
                                        'goodsNm' => $postValue['goodsNm'],
                                        'goodsDescription' => gd_isset($postValue['goodsDescription']),
                                        'scmNo' => gd_isset($postValue['scmNo']),
                                        'scmNoNm' => gd_isset($postValue['scmNoNm']),
                                        'purchaseNo' => gd_isset($postValue['purchaseNo']),
                                        'purchaseNoNm' => gd_isset($postValue['purchaseNoNm']),
                                        'goodsModelNo' => gd_isset($postValue['goodsModelNo']),
                                        'brandCd' => gd_isset($postValue['brandCd']),
                                        'brandCdNm' => gd_isset($postValue['brandCdNm']),
                                        'commission' => gd_isset($postValue['commission']),
                                        'taxFreeFl' => gd_isset($postValue['taxFreeFl']),
                                        'taxPercent' => gd_isset($postValue['taxPercent']),
                                        'makerNm' => gd_isset($postValue['makerNm']),
                                        'imageStorage' => $postValue['imageStorage'],
                                        'imagePath' => $postValue['imagePath'],
                                        'globalData' => $postValue['globalData'],
                                        'goodsNmGlobalFl' => $postValue['goodsNmGlobalFl'],
                                        'goodsPrice' => $postValue['goodsPrice'][$k],
                                        'costPrice' => $postValue['costPrice'][$k],
                                        'stockUseFl' => $postValue['stockUseFl'][$k],
                                        'stockCnt' => gd_isset($postValue['stockCnt'][$k], 0),
                                        'goodsCd' => gd_isset($postValue['goodsCd'][$k]),
                                        'optionNm' =>gd_isset($postValue['optionNm'][$k]),
                                        'imageNm' => gd_isset($postValue['imageNm'][$k]),
                                        'viewFl' => gd_isset($postValue['viewFl'][$k]),
                                        'soldOutFl' => gd_isset($postValue['soldOutFl'][$k]),
                                        'imgData' => $imgData,
                                        'purchaseNoDel' => gd_isset($postValue['purchaseNoDel'], 'n'),
                                        'brandCdDel' => gd_isset($postValue['brandCdDel'], 'n')
                                    );

                                    $arrData  = $addGoods->saveInfoAddGoods($arrData);

                                    if ($postValue['mode'] =='register_ajax') {
                                        $arrData['goodsNo'] = $arrData['addGoodsNo'];
                                        $arrData['totalStock'] =  gd_isset($postValue['stockCnt'][$k], 0);
                                        $arrData['scmNm'] = gd_isset($postValue['scmNoNm'],' ');
                                        $arrData['stockFl'] = gd_isset($arrData['stockUseFl']);
                                        $arrData['soldOutFl'] = gd_isset($arrData['soldOutFl']);
                                        $arrData['image'] = rawurlencode(gd_html_add_goods_image($arrData['addGoodsNo'], $arrData['imageNm'], $arrData['imagePath'], $arrData['imageStorage'], 30, $arrData['goodsNm'], '_blank'));

                                        $ajax_data[] = $arrData;
                                    }

                                    $saveCnt++;
                                }

                            }

                            $addGoods->saveInfoAddGoodsGlobal($arrData);

                            if($saveCnt) {
                                if ($postValue['mode'] =='register_ajax') {
                                    echo json_encode(array("state"=>true,"info"=>$ajax_data,"mode"=>'register_ajax',"msg"=>__('저장이 완료되었습니다.')));
                                    exit;
                                }

                                if($arrData['applyFl'] =='a') {
                                    $this->layer(__("승인을 요청하였습니다."));
                                } else {
                                    $this->layer(__('저장이 완료되었습니다.'));
                                }

                            } else {
                                $this->layer(__('처리중에 오류가 발생하여 실패되었습니다.'));
                            }

                        }


                    break;
                // 상품 삭제
                case 'delete':
                    // 삭제

                        if (empty(Request::post()->get('addGoodsNo')) === false) {
                            foreach (Request::post()->get('addGoodsNo') as $addGoodsNo) {
                                $applyFl = $addGoods->deleteAddGoods($addGoodsNo);
                            }
                        }

                        unset($postArray);

                        if($applyFl =='a') {
                            $this->layer(__("승인을 요청하였습니다."));
                        } else {
                            $this->layer(__('삭제 되었습니다.'));
                        }

                    break;
                // 상품 복사
                case 'copy':

                        if (empty(Request::post()->get('addGoodsNo')) === false) {
                            foreach (Request::post()->get('addGoodsNo') as $addGoodsNo) {
                                $applyFl =  $addGoods->setCopyGoods($addGoodsNo);
                            }
                        }

                        unset($postArray);

                        if($applyFl =='a') {
                            $this->layer(__("승인을 요청하였습니다."));
                        } else {
                            $this->layer(__('복사가 완료 되었습니다.'));
                        }



                    break;
                // 상품승인
                case 'apply':

                    if (empty($postValue['addGoodsNo']) === false) {

                        foreach ($postValue['addGoodsNo']as $addGoodsNo) {
                            $addGoods->setApplyAddGoods($addGoodsNo,$postValue['applyType'][$addGoodsNo]);
                        }

                    }

                    unset($postArray);
                    $this->layer(__('승인처리 되었습니다.'));

                    break;

                // 상품반려
                case 'applyReject':

                    if (empty($postValue['addGoodsNo']) === false) {

                        $addGoods->setApplyRejectAddGoods($postValue['addGoodsNo'],$postValue['applyMsg']);

                    }

                    unset($postArray);
                    $this->layer(__('반려처리 되었습니다.'));

                    break;
                case 'group_register':
                case 'group_modify':

                        $addGoods->saveInfoAddGoodsGroup($postValue);

                        $this->layer(__('저장이 완료되었습니다.'));

                    break;
                // 상품 삭제
                case 'group_delete':
                    // 삭제

                        if (empty(Request::post()->get('sno')) === false) {
                            foreach ($postValue['sno'] as $sno) {
                                $addGoods->deleteAddGoodsGroup($sno, $postValue['groupCd'][$sno]);
                            }
                        }

                        unset($postArray);

                    $this->layer(__('삭제 되었습니다.'));
                    break;
                // 상품 복사
                case 'group_copy':

                        if (empty(Request::post()->get('sno')) === false) {
                            foreach ($postValue['sno'] as $sno) {
                                $addGoods->setCopyGoodsGroup($sno, $postValue['groupCd'][$sno]);
                            }
                        }

                        unset($postArray);

                        $this->layer(__('복사가 완료 되었습니다.'));

                    break;
                // json 데이터
                case 'search_scm':

                        gd_isset($postValue['scmNo'], DEFAULT_CODE_SCMNO);

                        $data = $addGoods->getJsonListAddGoodsGroup($postValue['scmNo']);

                        echo $data;
                        exit;

                    break;
                // json 데이터
                case 'select_json':

                        if (empty($postValue['sno']) === false) {

                            $data = $addGoods->getDataAddGoodsGroup($postValue['sno']);


                            foreach ($data['addGoodsList'] as $key => $val) {
                                $data['addGoodsList'][$key]['totalStock'] =  gd_isset($val['stockCnt'], 0);
                                if($val['stockUseFl'] =='0')  $data['addGoodsList'][$key]['totalStock']  = "∞";
                                $data['addGoodsList'][$key]['goodsNo'] = $val['addGoodsNo'];
                                $data['addGoodsList'][$key]['image'] = rawurlencode(gd_html_add_goods_image($val['addGoodsNo'], $val['imageNm'], $val['imagePath'], $val['imageStorage'], 30, $val['goodsNm'], '_blank'));


                            }

                            echo json_encode(gd_htmlspecialchars_stripslashes(array('info'=>$data['addGoodsList'])),JSON_FORCE_OBJECT);
                            exit;
                        }

                    break;
            }

        } catch (Exception $e) {
            throw new LayerException($e->getMessage());
        }

    }
}
