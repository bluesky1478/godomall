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

use Component\Design\DesignPopup;
use Message;
use Globals;
use Request;

/**
 * 팝업 정보 처리
 * @author Shin Donggyu <artherot@godo.co.kr>
 */
class PopupPsController extends \Controller\Admin\Controller
{
    /**
     * index
     *
     */
    public function index()
    {
        // designPopup 정의
        $designPopup = new DesignPopup();
        $designPopup->setSkin(Globals::get('gSkin.frontSkinWork'));

        switch (Request::post()->get('mode')) {
            // 등록 / 수정
            case 'register':
            case 'modify':
                try {
                    // _POST 데이터
                    $postValue = Request::post()->toArray();

                    // 팝업 정보 저장
                    $sno = $designPopup->savePopupData($postValue);
                    if ($postValue['mode'] === 'register') {
                        $this->layer(__('저장이 완료되었습니다.'), 'parent.location.replace("./popup_register.php?sno=' . $sno . '");');
                    } else {
                        $this->layer(__('저장이 완료되었습니다.'));
                    }
                } catch (\Exception $e) {
                    $item = ($e->getMessage() ? ' - ' . str_replace("\n", ' - ', $e->getMessage()) : '');
                    $this->layer(__('처리중에 오류가 발생하여 실패되었습니다.') . $item);
                }
                break;

            // 삭제
            case 'delete':
                try {
                    // _POST 데이터
                    $postValue = Request::post()->toArray();

                    // 팝업 정보 삭제
                    $designPopup->deletePopupData($postValue['sno']);
                } catch (\Exception $e) {
                    echo $e->getMessage();
                }
                break;

            // 선택 삭제
            case 'delete_selected':
                try {
                    // _POST 데이터
                    $postValue = Request::post()->toArray();

                    // 팝업 정보 삭제
                    foreach ($postValue['sno'] as $val){
                        $designPopup->deletePopupData($val);
                    }
                    $this->layer(__('삭제 되었습니다.'));
                } catch (\Exception $e) {
                    $item = ($e->getMessage() ? ' - ' . str_replace("\n", ' - ', $e->getMessage()) : '');
                    $this->layer(__('처리중에 오류가 발생하여 실패되었습니다.') . $item);
                }
                break;
            // 팝업창 노출 위치 저장
            case 'popupPageRegist':
                try {
                    $postValue = Request::post()->toArray();
                    $returnData = $designPopup->pagePopupRegist($postValue);

                    echo json_encode($returnData);
                } catch (\Exception $e) {
                    $this->layer($e->getMessage());
                }
                break;
            // 팝업창 노출 위치 삭제
            case 'popupPageDelete':
                try {
                    $postValue = Request::post()->toArray();
                    $returnData = $designPopup->pagePopupDelete($postValue);

                    echo json_encode($returnData);
                } catch (\Exception $e) {
                    $this->layer($e->getMessage());
                }
                break;
        }
        exit();
    }
}
