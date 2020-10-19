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

namespace Bundle\Controller\Admin\Design;

use Framework\Debug\Exception\LayerException;
use Component\Mall\Mall;
use Component\Design\SkinDesign;
use Message;
use Globals;
use Request;

/**
 * 스킨 디자인 페이지 정보 저장
 * @author Shin Donggyu <artherot@godo.co.kr>
 */
class DesignPageEditPsController extends \Controller\Admin\Controller
{
    /**
     * index
     *
     * @throws LayerException
     */
    public function index()
    {
        // skinType 설정
        if (Request::post()->has('skinType') === false) {
            $skinType = 'front';
        } else {
            $skinType = Request::post()->get('skinType');
        }

        // SkinDesign 정의
        $skinDesign = new SkinDesign($skinType);
        $mall = new Mall();
        $skinWork = Globals::get('gSkin.' . $skinDesign->skinType . 'SkinWork');
        if (\Session::has('mallSno') === true && $mall->isUsableMall() === true) {
            $mallSkin = gd_policy('design.skin', \Session::get('mallSno'));
            $skinWork = $mallSkin[$skinDesign->skinType . 'Work'];
        }
        $skinDesign->setSkin($skinWork);

        switch (Request::post()->get('mode')) {
            case 'save':
            case 'saveas':
                try {
                    $skinDesign->saveDesignPageInfo(Request::post()->get('mode'));
                    $this->layer(__('저장이 완료되었습니다.'));
                } catch (\Exception $e) {
                    $item = ($e->getMessage() ? ' - ' . str_replace("\n", ' - ', $e->getMessage()) : '');
                    throw new LayerException(__('처리중에 오류가 발생하여 실패되었습니다.') . $item);
                }
                break;

            case 'batch_apply':
                try {
                    $skinDesign->saveDesignPageBatch();
                    $this->layer(__('저장이 완료되었습니다.'));
                } catch (\Exception $e) {
                    $item = ($e->getMessage() ? ' - ' . str_replace("\n", ' - ', $e->getMessage()) : '');
                    throw new LayerException(__('처리중에 오류가 발생하여 실패되었습니다.') . $item);
                }
                break;

            case 'designPageCreate': // 새로운 페이지 추가
                try {
                    // POST 파라메터
                    $postValue = Request::post()->toArray();

                    $setData['designPage'] = $postValue['dirPath'] . '/' . $postValue['fileName'] . $postValue['fileExt'];
                    $setData['linkurl'] = '';
                    $setData['content'] = $postValue['fileContent'];
                    $setData['text'] = $postValue['fileText'];

                    $skinDesign->saveDesignPage($postValue['saveMode'], $setData);
                    $this->layer(__('저장이 완료되었습니다.'));
                } catch (\Exception $e) {
                    $item = ($e->getMessage() ? ' - ' . str_replace("\n", ' - ', $e->getMessage()) : '');
                    throw new LayerException(__('처리중에 오류가 발생하여 실패되었습니다.') . $item);
                }
                break;

            // 디자인 페이지 삭제
            case 'deleteDesignPage':
                try {
                    // _POST 데이터
                    $postValue = Request::post()->toArray();

                    // 배너 정보 삭제
                    $skinDesign->deleteDesignPage($postValue['designPage']);
                } catch (\Exception $e) {
                    echo $e->getMessage();
                }
                break;
        }
        exit();
    }
}
