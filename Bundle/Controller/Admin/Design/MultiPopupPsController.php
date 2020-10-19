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

use Message;
use Globals;
use Request;

/**
 * 팝업 정보 처리
 * @author Shin Donggyu <artherot@godo.co.kr>
 */
class MultiPopupPsController extends \Controller\Admin\Controller
{
    /**
     * index
     *
     */
    public function index()
    {
        $designMultiPopup = \App::load('\\Component\\Design\\DesignMultiPopup');

        switch (Request::post()->get('mode')) {
            // 등록 / 수정
            case 'register':
            case 'modify':
                try {
                    // _POST 데이터
                    $postValue = Request::post()->toArray();

                    // 팝업 정보 저장
                    $sno = $designMultiPopup->saveMultiPopupData($postValue);
                    if ($postValue['mode'] === 'register') {
                        $this->layer(__('저장이 완료되었습니다.'), 'parent.location.replace("./multi_popup_register.php?sno=' . $sno . '");');
                    } else {
                        $this->layer(__('저장이 완료되었습니다.'));
                    }
                } catch (\Exception $e) {
                    $item = ($e->getMessage() ? ' - ' . str_replace("\n", ' - ', $e->getMessage()) : '');
                    $this->layer(__('처리중에 오류가 발생하여 실패되었습니다.') . $item);
                }
                break;

            // 선택 삭제
            case 'delete_selected':
                try {
                    // _POST 데이터
                    $postValue = Request::post()->toArray();

                    // 팝업 정보 삭제
                    foreach ($postValue['sno'] as $val){
                        $designMultiPopup->deleteMultiPopupData($val);
                    }
                    $this->layer(__('삭제 되었습니다.'));
                } catch (\Exception $e) {
                    $item = ($e->getMessage() ? ' - ' . str_replace("\n", ' - ', $e->getMessage()) : '');
                    $this->layer(__('처리중에 오류가 발생하여 실패되었습니다.') . $item);
                }
                break;

            // 이미지 업로드
            case 'image':

                $setData = $designMultiPopup->saveMultiPopupImage();
                echo json_encode($setData);
                exit;

                break;
        }
        exit();
    }
}
