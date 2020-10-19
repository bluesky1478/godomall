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

use Exception;
use Framework\Debug\Exception\LayerException;
use Message;
use Request;
use Component\Validator\Validator;

class GoodsMustInfoPsController extends \Controller\Admin\Controller
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
        $mustInfo = \App::load('\\Component\\Goods\\GoodsMustInfo');
        try {

            switch ($postValue['mode']) {
                // 상품 등록 / 수정
                case 'register':
                case 'modify':

                    $mustInfo->saveInfoMustInfo($postValue);

                    $this->layer(__('저장이 완료되었습니다.'));

                // 상품 삭제
                case 'delete':
                    // 삭제

                    if (empty(Request::post()->get('sno')) === false) {

                        $mustInfo->deleteMustInfo(Request::post()->get('sno'));

                    }

                    unset($postArray);

                    $this->layer(__('삭제 되었습니다.'));

                    break;
                // 상품 복사
                case 'copy':


                    if (empty(Request::post()->get('sno')) === false) {
                        foreach (Request::post()->get('sno') as $sno) {
                            $mustInfo->setCopyMustInfo($sno);
                        }
                    }

                    unset($postArray);

                    $this->layer(__('복사가 완료 되었습니다.'));

                    break;
                // json 데이터
                case 'search_scm':

                    gd_isset($postValue['scmNo'], DEFAULT_CODE_SCMNO);

                    $data = $mustInfo->getJsonListMustInfo($postValue['scmNo']);

                    echo $data;
                    exit;

                    break;
                // json 데이터
                case 'select_json':

                    if (empty($postValue['sno']) === false) {

                        $data = $mustInfo->getDataMustInfo($postValue['sno']);
                        $data = $data['data'];

                        foreach($data['addMustInfo']['infoTitle'] as $key => $val) {
                            $info[$key]['count'] = (count($val)*2);
                            foreach($val as $k => $v) {
                                $info[$key]['info'][$v] = $data['addMustInfo']['infoValue'][$key][$k];
                            }
                        }


                        echo json_encode(gd_htmlspecialchars_stripslashes($info));
                        exit;
                    }

                    break;
            }

        } catch (Exception $e) {
            throw new LayerException($e->getMessage());
        }

        exit;

    }
}
