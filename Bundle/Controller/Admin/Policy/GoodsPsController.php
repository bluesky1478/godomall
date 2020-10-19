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
namespace Bundle\Controller\Admin\Policy;

use Framework\Debug\Exception\LayerException;
use Framework\Debug\Exception\LayerNotReloadException;
use Message;
use Request;
use Exception;

/**
 * 상품 정책 저장 처리
 * @author Shin Donggyu <artherot@godo.co.kr>
 */
class GoodsPsController extends \Controller\Admin\Controller
{

    /**
     * index
     *
     * @throws LayerException
     */
    public function index()
    {
        switch (Request::post()->get('mode')) {
            // --- 상품 이미지 사이즈 설정
            case 'goods_image':
                try {
                    $policy = \App::load('\\Component\\Policy\\Policy');
                    $policy->saveGoodsImages(Request::post()->toArray());
                    throw new LayerException();
                } catch (Exception $e) {
                    throw $e;
                }
                break;

            // --- 상품 이미지 사이즈 설정
            case 'goods_tax':
                try {
                    $policy = \App::load('\\Component\\Policy\\Policy');
                    $policy->saveGoodsTax(Request::post()->toArray());
                    throw new LayerException();
                } catch (Exception $e) {
                    throw $e;
                }
                break;

            // --- 상품 상세 이용안내 공급사 기본 관계
            case 'goods_info_scm_relation':
                try {
                    $inform = \App::load('\\Component\\Agreement\\BuyerInform');
                    $scmRelationCount = $inform->getGoodsInfoScmRelation(Request::post()->toArray());
                    echo $scmRelationCount;
                } catch (Exception $e) {
                    echo $e->getMessage();
                }
                exit;
                break;

            // --- 상품 상세 이용안내
            case 'goods_info':
                try {
                    $inform = \App::load('\\Component\\Agreement\\BuyerInform');
                    $inform->saveGoodsInfo(Request::post()->toArray());
                    $this->layer();
                } catch (Exception $e) {
                    throw new LayerNotReloadException($e->getMessage());
                }
                break;

            // 상품 상세 이용안내 복사
            case 'goods_info_copy':
                $inform = \App::load('\\Component\\Agreement\\BuyerInform');
                foreach(Request::post()->get('informCd') as $k => $v) {
                    $inform->setGoodsInfoCopy($v);
                }
                throw new LayerException(__('복사가 완료 되었습니다.'));
                break;

            // 상품 상세 이용안내 삭제
            case 'goods_info_delete':
                $inform = \App::load('\\Component\\Agreement\\BuyerInform');
                foreach(Request::post()->get('informCd') as $k => $v) {
                    $inform->setGoodsInfoDelete($v);
                }
                throw new LayerException(__('삭제 되었습니다.'));
                break;
            // --- 상품 상세 공급사별 검색
            case 'search_scm_goods_info':
                // 모듈 호출

                try {

                    if(!Request::post()->get('scmNo')) Request::post()->set('scmNo',DEFAULT_CODE_SCMNO);

                    $inform = \App::load('\\Component\\Agreement\\BuyerInform');
                    $data = $inform->getGoodsInfoCode(Request::post()->get('goods_mode'),Request::post()->get('scmNo'));

                    if(Request::post()->get('goods_mode') =='modify' && Request::post()->get('defaultScmNo') == Request::post()->get('scmNo')) {
                       $defaultData = Request::post()->toArray();
                       $data['default'] = $defaultData;
                    }

                    if($data['default']) {
                        foreach($data['default'] as $k => $v) {
                            if($k == 'mode' || $k == 'goods_mode' || $k == 'scmNo' || $k == 'defaultScmNo') continue;
                            $getBuyerInformData = gd_buyer_inform($v);
                            $default[$k]['informCd'] = $getBuyerInformData['informCd'];
                            $default[$k]['content'] = $getBuyerInformData['content'];
                            $default[$k]['informNm'] = $getBuyerInformData['informNm'];
                        }
                    }

                    $groupCd = array('detailInfoAS','detailInfoExchange','detailInfoDelivery','detailInfoRefund');
                    foreach($groupCd as $val){
                        if(!$default[$val]['informCd']) $default[$val]['informCd'] = 0;
                        if(!$default[$val]['content']) $default[$val]['content'] = '';
                        if(!$default[$val]['informNm']) $default[$val]['informNm'] = '';
                    }

                    $result['default'] = $default;
                    unset($data['default']);

                    echo json_encode($result,JSON_UNESCAPED_UNICODE);

                    exit;

                } catch (Exception $e) {
                    $e->actLog();


                }


                break;
            // --- 상품 상세 이용안내 검색
            case 'search_detail_info':
                // 모듈 호출

                try {

                    $data = gd_buyer_inform(Request::post()->get('informCd'));
                    echo json_encode($data,JSON_UNESCAPED_UNICODE);

                    exit;

                } catch (Exception $e) {
                    $e->actLog();

                }


                break;

            // --- 최근 본 상품 설정
            case 'goods_today':
                try {
                    $policy = \App::load('\\Component\\Policy\\Policy');
                    $policy->saveGoodsToday(Request::post()->toArray());
                    throw new LayerException();
                } catch (Exception $e) {
                    throw $e;
                }
                break;
            // --- 상품정보 노출설정
            case 'goods_display':
                try {
                    $policy = \App::load('\\Component\\Policy\\Policy');
                    $policy->saveGoodsDisplay(Request::post()->toArray());
                    throw new LayerException();
                } catch (Exception $e) {
                    throw $e;
                }
                break;
            case 'get_goods_info':
                $inform = \App::load('\\Component\\Agreement\\BuyerInform');
                $mallSno = gd_isset(Request::post()->get('mallSno'), 1);
                $data = $inform->getGoodsInfo(Request::post()->get('informCd'), $mallSno);

                echo json_encode($data);
                break;
            // --- 상품속도개선
            case 'goods_division':
                try {
                    $policy = \App::load('\\Component\\Policy\\Policy');
                    $policy->saveGoodsDivision(Request::post()->toArray());
                    throw new LayerException();
                } catch (Exception $e) {
                    throw $e;
                }
                break;
            // --- 옵션 기능 개선
            case 'goods_option_1903_change':
                try {
                    $policy = \App::load('\\Component\\Policy\\Policy');
                    $useData['use'] = 'y';
                    $policy->setValue('goods.option_1903', $useData);
                    throw new LayerException('새로운 기능으로 적용 완료되었습니다.');
                } catch (Exception $e) {
                    throw $e;
                }
                break;
        }
        exit();
    }
}
