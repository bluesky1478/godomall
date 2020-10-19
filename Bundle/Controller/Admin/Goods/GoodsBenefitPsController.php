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
use Component\File\StorageHandler;
use Request;
use Framework\Debug\Exception\LayerNotReloadException;
use Exception;

class GoodsBenefitPsController extends \Controller\Admin\Controller
{

    /**
     * 상품 혜택 관리 저장 처리
     *
     * @author  cjb3333
     */
    public function index()
    {
        // --- POST 값 처리
        $postValue = Request::post()->all();
        $goodsBenefit = \App::load('\\Component\\Goods\\GoodsBenefit');
        try {
            switch ($postValue['mode']) {

                case 'register':
                    $goodsBenefit->setGoodsBenefit($postValue);
                    if($postValue['popupMode'] == 'yes'){
                        $this->layer(__('상품 혜택을 등록 하였습니다.'), 'parent.close();');
                    }else{
                        $this->layer(__("상품 혜택을 등록 하였습니다."));
                    }

                    break;

                case 'modify':
                    $goodsBenefit->setGoodsBenefit($postValue);
                    if($postValue['popupMode'] == 'yes'){
                        $this->layer(__('상품 혜택을 수정 하였습니다.'), 'parent.close();');

                    }else {
                        $this->layer(__("상품 혜택을 수정 하였습니다."));
                    }

                    break;

                case 'delete':

                    if (empty($postValue['sno']) === false) {
                        foreach ($postValue['sno'] as $sno) {
                            $goodsBenefit->setDeleteGoodsBenefit($sno);
                        }
                    }
                    $this->layer(__('상품 혜택을 삭제 하였습니다.'));

                case 'config':
                    $goodsBenefit->setConfig($postValue);
                    $this->layer(__('저장이 완료되었습니다.'));

                default:
                    break;
            }
        } catch (Exception $e) {
            throw new LayerNotReloadException($e->getMessage(), 0, null, null, 4000, false);
        }

        exit;
    }
}
