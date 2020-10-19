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

use Framework\Debug\Exception\LayerNotReloadException;
use Framework\Debug\Exception\LayerException;
use Message;
use Request;

/**
 * 사은품 관련 처리 페이지
 * @author Young Eun Jung <atomyang@godo.co.kr>
 */
class GiftPsController extends \Controller\Admin\Controller
{

    /**
     * index
     *
     */
    public function index()
    {
        $postValue = Request::post()->toArray();

        // --- 사은품 class
        $gift = \App::load('\\Component\\Gift\\GiftAdmin');

        try {
            switch ($postValue['mode']) {
                // 사은품 증정 정책 설정
                case 'gift_config':
                    try {
                        $policy = \App::load('\\Component\\Policy\\Policy');
                        $policy->saveGoodsGift(Request::post()->toArray());
                        throw new LayerException();
                    } catch (Exception $e) {
                        throw $e;
                    }

                    break;

                // 사은품 등록 / 수정
                case 'register':
                case 'modify':
                    $gift->saveInfoGift($postValue);
                    $this->layer(__('저장이 완료되었습니다.'));

                    break;

                // 사은품 복사
                case 'copy':
                    if (empty($postValue['giftNo']) === false) {
                        foreach ($postValue['giftNo'] as $giftNo) {
                            $gift->setCopyGift($giftNo);
                        }
                    }
                    unset($postArray);
                    $this->layer(__('복사가 완료 되었습니다.'));

                    break;

                // 사은품 삭제
                case 'delete':

                    if (empty($postValue['giftNo']) === false) {
                        foreach ($postValue['giftNo'] as $giftNo) {
                                $gift->setDeleteGift($giftNo);
                            }
                        }
                        unset($postArray);

                        $this->layer(__('삭제 되었습니다.'));


                    break;

                // 사은품 증정 등록 / 수정
                case 'present_register':
                case 'present_modify':
                        $gift->saveInfoGiftPresent($postValue);
                        $this->layer(__('저장이 완료되었습니다.'));

                    break;

                // 사은품 증정 복사
                case 'present_copy':
                        if (empty($postValue['sno'] ) === false) {
                            foreach ($postValue['sno']  as $sno) {
                                $gift->setCopyGiftPresent($sno);
                            }
                        }
                        unset($postArray);
                        $this->layer(__('복사가 완료 되었습니다.'));

                    break;

                // 사은품 증정 삭제
                case 'present_delete':
                        if (empty($postValue['sno']) === false) {
                            foreach ($postValue['sno'] as $sno) {
                                $gift->setDeleteGiftPresent($sno);
                            }
                        }
                        unset($postArray);
                        $this->layer(__('삭제 되었습니다.'));

                    break;
            }
        } catch (\Exception $e) {
            throw new LayerNotReloadException($e->getMessage());
        }

        exit();
    }
}
