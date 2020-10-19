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

use Component\Design\SkinDesign;
use Component\Page\Page;
use Globals;
use Request;
use UserFilePath;

/**
 * 멀티팝업 미리보기
 * @author jung young eun<atomyang@godo.co.kr>
 */
class MultiPopupPreviewController extends \Controller\Admin\Controller
{
    /**
     * index
     *
     */
    public function index()
    {
        // --- 메뉴 설정
        $this->callMenu('design', 'designConf', 'multiPopupList');


        //--- 상품 데이터
        try {

            $postValue = Request::post()->toArray();

            if($postValue['page'] =='list') {
                $designMultiPopup = \App::load('\\Component\\Design\\DesignMultiPopup');
                $postValue = $designMultiPopup->getPopupDetailData($postValue['sno']);
                $postValue['image'] =json_decode($postValue['popupImageInfo'], true);
            }

            $postValue['imagePath'] = UserFilePath::data('multi_popup')->www().DS;
            $postValue['widthCount'] = substr($postValue['popupSlideCount'],0,1);
            $postValue['heightCount'] = substr($postValue['popupSlideCount'],1,1);
            $postValue['popupSlideThumbW'] =  $postValue['popupSlideViewW'] / $postValue['widthCount'];

            $this->setData('getData', $postValue);

            //--- 관리자 디자인 템플릿
            $this->getView()->setDefine('layout', 'layout_layer.php');


        } catch (Exception $e) {
            throw $e;
        }

    }
}
