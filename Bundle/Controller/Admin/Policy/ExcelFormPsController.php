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
 * @link      http://www.godo.co.kr
 */
namespace Bundle\Controller\Admin\Policy;

use Framework\Debug\Exception\LayerException;
use Framework\Utility\GodoUtils;
use Message;
use Request;
use Component\Member\Manager;

/**
 * 엑셈폼양식관리 관련
 * @author Young Eun Jung <atomyang@godo.co.kr>
 */
class ExcelFormPsController extends \Controller\Admin\Controller
{
    /**
     * {@inheritdoc}
     */
    public function index()
    {
        $postValue = Request::post()->toArray();

        // --- 사은품 class
         $excelForm = \App::load('Component\\Excel\\ExcelForm');

        try {
            switch ($postValue['mode']) {
                // 엑셀폼 등록 /수정
                case 'register':
                case 'modify':
                    //추가 항목이 있을 경우.
                    if (empty($postValue['addFields']['name']) === false && is_array($postValue['addFields']['name']) === true) {
                        $postValue['useFields'] = $addFields = $tmpCheckDupl = [];
                        foreach ($postValue['addFields']['name'] as $k => $fieldNm) {
                            $tmpFieldNm = trim($fieldNm);
                            if (empty($tmpCheckDupl[$tmpFieldNm]) === false) { continue; } //중복 제거
                            if (strlen($tmpFieldNm) > 0) {
                                $addFields[] = '{addFieldNm}_' . $tmpFieldNm;
                                if ($postValue['addFields']['use'][$k] === 'y') {
                                    $postValue['useFields'][] = '{addFieldNm}_' . $tmpFieldNm;
                                }
                                $tmpCheckDupl[$tmpFieldNm] = $tmpFieldNm;
                            }
                        }
                        //공급사 별 추가항목 가져오기
                        if(Manager::isProvider()) {
                            $scmNo = \Session::get('manager.scmNo');
                        } else {
                            $scmNo = 1;
                        }
                        $excelForm->saveExcelAddFieldsByScm($scmNo, $addFields);
                    }
                    if (strpos($postValue['location'], 'plusreview') !== false && $postValue['menu'] == 'board') {
                        $postValue['menu'] = 'plusreview';
                    }
                    $excelForm->saveInfoExcelForm($postValue);
                    $this->layer(__('저장이 완료되었습니다.'));
                    break;
                // 상세 카테고리 선택
                case 'select_location':
                    $data = $excelForm->locationList[$postValue['menu']];
                    if ($postValue['menu'] === 'board' && GodoUtils::isPlusShop(PLUSSHOP_CODE_REVIEW) && Manager::isProvider() === false) {
                        $tmpData = $excelForm->locationList['plusreview'];
                        $data = array_merge($data, $tmpData);
                    }

                    if ($postValue['displayFl'] == 'y') {
                        unset($data['goods_must_info_list']);
                        unset($data['coupon_offline_list']);
                    }

                    echo json_encode(
                        [
                            "state" => true,
                            "info"  => $data,
                        ]
                    );
                    exit;

                    break;

                // 상세 필드 선택
                case 'select_field':
                    if (strpos($postValue['location'], 'plusreview') !== false && $postValue['menu'] != 'plusreview') {
                        $postValue['menu'] = 'plusreview';
                    }
                    $data = $excelForm->setExcelForm($postValue['menu'], $postValue['location'], $postValue['selected']);
                    $selectItem = json_decode($postValue['select-item']); // 선택한 항목

                    if (!$postValue['sort'] && !$postValue['item']) {
                        foreach ($data as $k => $v) {
                            if (in_array($k, $selectItem)) {
                                $data[$k]['selected'] = 'y';
                            }
                        }
                    }

                    if ($postValue['sort'] != '') {
                        $tmpOrder = $tmpGoods = $tmpSelect = $tmpNonSelect = [];
                        foreach ($data as $k => $v) {
                            $tmp[$k] = $v['name'];
                            if ($v['orderFl'] === 'y') {
                                $tmpOrder[$k] = $v['name'];
                            } else {
                                $tmpGoods[$k] = $v['name'];
                            }
                            if (count($selectItem) > 0) {
                                if (in_array($k, $selectItem)) {
                                    $tmpSelect[$k] = $v['name'];
                                } else {
                                    $tmpNonSelect[$k] = $v['name'];
                                }
                            }
                        }

                        switch ($postValue['sort']) {
                            case 'asc':
                                arsort($tmp);
                                break;
                            case 'desc':
                                asort($tmp);
                                break;
                            case 'select-asc': // 선택항목 위로
                                if (count($selectItem) > 0) {
                                    $tmp = array_merge($tmpSelect, $tmpNonSelect);
                                }
                                break;
                            case 'select-desc': // 선택항목 아래로
                                if (count($selectItem) > 0) {
                                    $tmp = array_merge($tmpNonSelect, $tmpSelect);
                                }
                                break;
                            case 'order': // 주문서 항목
                                $tmp = array_merge($tmpOrder, $tmpGoods);
                                break;
                            case 'goods': // 상품 항목
                                $tmp = array_merge($tmpGoods, $tmpOrder);
                                break;
                        }

                        foreach ($tmp as $k => $v) {
                            $tmp[$k] = $data[$k];
                            if (in_array($k, $selectItem)) {
                                $tmp[$k]['selected'] = 'y';
                            }
                        }
                        $data = $tmp;
                    }

                    if ($postValue['item'] != '') {
                        unset($tmp);
                        foreach ($data as $k => $v) {
                            switch ($postValue['item']) {
                                case 'order': // 주문서 항목
                                    if ($v['orderFl'] === 'y') {
                                        $tmp[$k] = $v['name'];
                                    }
                                    break;
                                case 'goods': // 상품 항목
                                    if (!$v['orderFl']) {
                                        $tmp[$k] = $v['name'];
                                    }
                                    break;
                                default:
                                    $tmp[$k] = $v['name'];
                                    break;

                            }
                        }
                        foreach ($tmp as $k => $v) {
                            $tmp[$k] = $data[$k];
                            if (in_array($k, $selectItem)) {
                                $tmp[$k]['selected'] = 'y';
                            }
                        }
                        $data = $tmp;
                    }

                    echo json_encode(
                        [
                            "state" => true,
                            "info"  => $data,
                        ]
                    );
                    exit;
                    break;

                // 엑셀폼 삭제
                case 'delete':
                    if (empty($postValue['sno']) === false) {
                        $excelForm->setDeleteExcelForm($postValue['sno']);
                    }
                    unset($postArray);
                    if ($postValue['layerFl'] !== 'n') {
                        $this->layer(__('삭제 되었습니다.'));
                    }
                    break;
            }
        } catch (Exception $e) {
            throw new LayerException($e->getMessage());
        }

        exit();
    }
}
