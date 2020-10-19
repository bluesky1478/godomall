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

use Component\Design\DesignBanner;
use Component\Design\DesignPopup;
use Message;
use Request;

/**
 * 배너 정보 처리
 * @author Shin Donggyu <artherot@godo.co.kr>
 */
class BannerPsController extends \Controller\Admin\Controller
{
    /**
     * index
     *
     */
    public function index()
    {
        // deㅣsignBanner 정의
        $designBanner = new DesignBanner();

        switch (Request::post()->get('mode')) {
            // 등록 / 수정
            case 'register':
            case 'modify':
                try {
                    // _POST 데이터
                    $postValue = Request::post()->toArray();

                    // 배너 정보 저장
                    $sno = $designBanner->saveBannerData($postValue);
                    if ($postValue['mode'] === 'register') {
                        $this->layer(__('저장이 완료되었습니다.'), 'parent.location.replace("./banner_register.php?sno=' . $sno . '");');
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

                    // 배너 정보 삭제
                    $designBanner->deleteBannerData($postValue['sno']);
                } catch (\Exception $e) {
                    echo $e->getMessage();
                }
                break;

            // 선택 삭제
            case 'delete_selected':
                try {
                    // _POST 데이터
                    $postValue = Request::post()->toArray();

                    // 배너 정보 삭제
                    foreach ($postValue['sno'] as $val){
                        $designBanner->deleteBannerData($val);
                    }
                    $this->layer(sprintf(__('선택한 %s이(가) 삭제 되었습니다.'), __('배너')));
                } catch (\Exception $e) {
                    $item = ($e->getMessage() ? ' - ' . str_replace("\n", ' - ', $e->getMessage()) : '');
                    $this->layer(sprintf(__('선택한 %s의 삭제시 오류가 발생하여 실패되었습니다.'), __('배너')) . $item);
                }
                break;

            // 배너 그룹 등록 / 수정
            case 'banner_group_register':
            case 'banner_group_modify':
                try {
                    // _POST 데이터
                    $postValue = Request::post()->toArray();

                    // 배너 정보 저장
                    $designBanner->saveBannerGroupData($postValue);
                    if ($postValue['mode'] === 'banner_group_register') {
                        $this->layer(__('저장이 완료되었습니다.'));
                    } else {
                        $this->layer(__('저장이 완료되었습니다.'));
                    }
                } catch (\Exception $e) {
                    $item = ($e->getMessage() ? ' - ' . str_replace("\n", ' - ', $e->getMessage()) : '');
                    $this->layer(__('처리중에 오류가 발생하여 실패되었습니다.') . $item);
                }
                break;

            // 배너 그룹 삭제
            case 'banner_group_delete':
                try {
                    // _POST 데이터
                    $postValue = Request::post()->toArray();

                    // 배너 정보 삭제
                    $designBanner->deleteBannerGroupData($postValue['sno']);
                } catch (\Exception $e) {
                    echo $e->getMessage();
                }
                break;

            // 배너 그룹 선택 삭제
            case 'banner_group_delete_selected':
                try {
                    // _POST 데이터
                    $postValue = Request::post()->toArray();

                    // 배너 정보 삭제
                    foreach ($postValue['sno'] as $val){
                        $designBanner->deleteBannerGroupData($val);
                    }
                    $this->layer(sprintf(__('선택한 %s이(가) 삭제 되었습니다.'), '배너 그룹'));
                } catch (\Exception $e) {
                    $item = ($e->getMessage() ? ' - ' . str_replace("\n", ' - ', $e->getMessage()) : '');
                    $this->layer(sprintf(__('선택한 %s의 삭제시 오류가 발생하여 실패되었습니다.'), '배너 그룹') . $item);
                }
                break;
        }
        exit();
    }
}
