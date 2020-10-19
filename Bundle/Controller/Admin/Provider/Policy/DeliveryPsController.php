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
namespace Bundle\Controller\Admin\Provider\Policy;

use Framework\Debug\Exception\LayerException;
use Exception;
use Message;
use Request;

/**
 * 배송 정책 관련 처리 페이지
 * [관리자 모드] 배송 정책 관련 처리 페이지
 *
 * @package Bundle\Controller\Admin\Provider\Policy
 * @author  Jong-tae Ahn <qnibus@godo.co.kr>
 */
class DeliveryPsController extends \Controller\Admin\Controller
{
    /**
     * @inheritdoc
     *
     * @throws LayerException
     */
    public function index()
    {
        // --- 각 배열을 trim 처리
        $postValue = Request::post()->toArray();

        // --- 모듈 호출
        $delivery = \App::load('\\Component\\Delivery\\Delivery');

        // 각 모드에 따른 처리
        switch (Request::request()->get('mode')) {
            // --- 배송 정책 설정
            case 'regist':
                try {
                    if ($postValue['basic']['deliveryConfigType'] != 'etc') {
                        $delivery->saveInfoDelivery($postValue);
                    } else {
                        $delivery->saveInfoDeliveryEtc($postValue);
                    }
                    $this->layer(__('저장이 완료되었습니다.'), 'top.location.replace("../policy/delivery_config.php");');
                } catch (Exception $e) {
                    throw new LayerException(__('처리중에 오류가 발생하여 실패되었습니다.') . $e->getMessage(), null, null, null, 0);
                }
                break;

            // --- 배송 정책 삭제
            case 'delete':
                try {
                    $message = $delivery->deleteInfoDelivery($postValue);
                    $this->layer($message, 'top.location.reload(true);');
                } catch (Exception $e) {
                    throw new LayerException(__('처리중에 오류가 발생하여 실패되었습니다.') . $e->getMessage(), null, null, null, 0);
                }
                break;

            // --- 배송 정책 설정
            case 'area_regist':
                try {
                    $delivery->saveInfoAreaGroup($postValue);

                    // 팝업으로 전달된 경우 해당 창 닫고 부모창 새로고침 처리
                    if ($postValue['popupMode'] === 'true') {
                        $this->layer(__('저장이 완료되었습니다.'), 'parent.opener.location.reload(true);parent.close();');
                    } else {
                        $this->layer(__('저장이 완료되었습니다.'), 'top.location.replace("../policy/delivery_area.php");');
                    }
                } catch (Exception $e) {
                    throw new LayerException(__('처리중에 오류가 발생하여 실패되었습니다.') . $e->getMessage(), null, null, null, 0);
                }
                break;

            // --- 지역별배송 정책 삭제
            case 'area_delete':
                try {
                    $totalCount = count($postValue['deliverChk']);
                    $deleteCount = $delivery->deleteInfoAreaGroup($postValue);
                    $this->layer(__('총 ' . $totalCount . '개의 추가배송비 중 ' . $deleteCount . '건을 삭제했습니다.\n해당 지역별 추가배송비가 적용된 배송비조건이 존재하는 경우 삭제가 불가합니다.'), 'top.location.replace("../policy/delivery_area.php");');
                } catch (Exception $e) {
                    throw new LayerException(__('처리중에 오류가 발생하여 실패되었습니다.') . $e->getMessage(), null, null, null, 0);
                }
                break;

            // --- 배송 업체 순서 변경
            case 'company_register':
                try {
                    $delivery->saveDeliveryCompany($postValue);
                    $this->layer(__('저장이 완료되었습니다.'));
                } catch (Exception $e) {
                    throw new LayerException(__('처리중에 오류가 발생하여 실패되었습니다.'), null, null, null, 0);
                }
                break;

            // 공급사별 기본 배송지
            case 'search_scm':
                try {

                    $scmNo = Request::post()->get('scmNo');
                    $sno = Request::post()->get('deliverySno');
                    gd_isset($scmNo, DEFAULT_CODE_SCMNO);
                    gd_isset($sno);

                    $arr = $delivery->getScmDeliveryBasic($scmNo,$sno);

                    echo  json_encode(gd_htmlspecialchars_stripslashes($arr));
                    exit;

                } catch (Exception $e) {
                    throw $e;
                }
                break;

            // --- 지역별 추가배송비내 지역검색 (중앙서버)
            case 'area_search':
                if (Request::isAjax()) {
                    if (Request::post()->has('newAreaSidoCode')) {
                        $godo = \App::load('\\Component\\Godo\\GodoCenterServerApi');
                        echo $godo->getCurlDataAddDelivery('newAreaGugun', Request::post()->get('newAreaSidoCode'));
                        exit();
                    }
                }
                break;

            // --- 지역별 추가배송비 등록
            case 'add_area_delivery':
                try {
                    $delivery->addAreaDelivery($postValue);
                    throw new LayerException(__('저장이 완료되었습니다.'));
                } catch (Exception $e) {
                    //                    throw $e;
                    throw new LayerException(__('처리중에 오류가 발생하여 실패되었습니다.') . $e->getMessage(), null, null, null, 0);
                }
                break;

            // --- 기본지역리스트 적용
            case 'basic_area_delivery':
                try {
                    $tmp = [];
                    $handle = fopen(\App::getBasePath() . '/data/excel/policy_newAreaDelivery.csv', "r");
                    while (($data = fgetcsv($handle, 1000, ",")) !== false) {
                        $num = count($data);
                        if ($row == 1) {
                            $dynamicField[0] = $data[0];
                            $dynamicField[1] = $data[1];
                            $dynamicField[2] = $data[2];
                        }

                        if ($row > 1) {
                            for ($c=0; $c < $num; $c++) {
                                $tmp[$row-2][$dynamicField[$c]] = iconv('euc-kr', 'utf-8', $data[$c]);
                            }
                        }
                        $row++;
                    }
                    fclose($handle);
                    $this->setData($tmp);
                    $this->json();
                } catch (Exception $e) {

                }
                break;

            // 엑셀파일 샘플 다운로드
            case 'downloadDeliveryArea':
                try {
                    $downloadPath = \App::getBasePath() . '/data/excel/policy_sampleAreaDelivery.xls';
                    $this->download($downloadPath, 'area_delivery_excel_sample.xls');
                } catch (Exception $e) {
                    if (Request::isAjax()) {
                        $this->json([
                            'code' => 0,
                            'message' => $e->getMessage(),
                        ]);
                    } else {
                        throw $e;
                    }
                }
                break;

            // 추가지역 리스트 가져오기 ajax
            case 'getDeliveryAreaList':
                try {
                    $sno = Request::get()->get('sno');
                    $areaData = $delivery->getSnoDeliveryArea($sno);

                    echo json_encode($areaData);
                } catch (Exception $e) {
                    echo '';
                }
                break;

            // 공급사중 기본값등록된 추가배송비가있는지 확인 ajax
            case 'checkDefaultDeliveryArea':
                try {
                    $areaData = $delivery->getCountDefaultDeliveryArea();

                    echo json_encode(array('cnt' => $areaData));
                } catch (Exception $e) {
                    echo '';
                }
                break;
        }
    }
}
