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
namespace Bundle\Controller\Admin\Promotion;

use Framework\Debug\Exception\LayerException;
use Message;
use Request;

/**
 * 타임세일
 * @author Young Eun Jung <atomyang@godo.co.kr>
 */
class TimeSalePsController extends \Controller\Admin\Controller
{

    /**
     * index
     *
     */
    public function index()
    {
        $postValue = Request::post()->toArray();

        // --- 사은품 class
        $timeSale = \App::load('\\Component\\Promotion\\TimeSaleAdmin');

        try {
            switch ($postValue['mode']) {
                // 타임세일 등록 / 수정
                case 'register':
                case 'modify':
                    $timeSale->saveInfoTimeSale($postValue);
                    $this->layer(__('저장이 완료되었습니다.'));

                    break;

                // 타임세일 삭제
                case 'delete':
                    if (empty($postValue['sno']) === false) {
                        $timeSale->setDeleteTimeSale($postValue);
                    }

                    $this->layer(__('삭제 되었습니다.'));

                    break;

                // 타임세일 강제종료
                case 'close':
                    if (empty($postValue['closeSno']) === false) {
                        $timeSale->setCloseTimeSale([$postValue['closeSno']]);
                    }

                    $this->layer(__("선택한 타임세일이 정상적으로 종료되었습니다."));

                    break;
            }
        } catch (Exception $e) {
            throw new LayerException($e->getMessage());
        }

        exit();
    }
}
