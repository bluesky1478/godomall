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

namespace Bundle\Controller\Admin\Share;

use Framework\Debug\Exception\LayerException;
use Component\Policy\ManageSecurityPolicy;

/**
 * Class LayerExcelPsController
 * @package Bundle\Controller\Admin\Share
 * @author  yjwee <yeongjong.wee@godo.co.kr>
 */
class LayerExcelPsController extends \Controller\Admin\Controller
{
    public function index()
    {
        ini_set('memory_limit', '-1');
        set_time_limit(RUN_TIME_LIMIT);
        $request = \App::getInstance('request');
        $logger = \App::getInstance('logger');
        $session = \App::getInstance('session');
        $postValue = $request->post()->toArray();
        $excelRequest = \App::load('Component\\Excel\\ExcelRequest');
        $logger->info(__METHOD__, $postValue);
        try {
            switch ($postValue['mode']) {
                case 'excel':
                    $data = $excelRequest->saveInfoExcelRequest($postValue);
                    if ($data === true) {
                        echo "<script> parent.set_excel_list('" . __('다운로드할 엑셀파일 생성이 완료되었습니다. 파일을 선택하여 다운로드하시기 바랍니다.') . "'); </script>";
                    } else {
                        echo "<script> parent.hide_process(); </script>";
                        $this->layerNotReload(__('엑셀 파일 내용이 없습니다. 데이터를 확인해주세요.'));
                    }
                    break;
                case 'download':
                    $pathResolver = \App::getInstance('user.path');
                    $fileInfo = $excelRequest->getInfoExcelRequest($postValue['sno'], 'menu, filePath,fileName,downloadFileName,regDt');

                    if ($fileInfo['menu'] == 'goods') {
                        if ($fileInfo['location'] == 'gift_list' || $fileInfo['location'] == 'gift_present_list' || $fileInfo['location'] == 'goods_list_delete' || $fileInfo['location'] == 'goods_must_info_list' || $fileInfo['location'] == 'add_goods_list') {
                            // 사은품관리/사은품 지급조건 관리/삭제상품 관리/상품 필수정보 관리/추가상품 관리 내역은 상품정보 엑셀다운로드와 상관없이 다운되게 처리
                        } else {
                            if ($session->get('manager.functionAuthState') == 'check' && $session->get('manager.functionAuth.goodsExcelDown') != 'y') {
                                $this->js('top.dialog_alert(\'권한이 없습니다. 권한은 대표운영자에게 문의하시기 바랍니다.\')');
                            }
                        }
                    }
                    if ($fileInfo['menu'] == 'order') {
                        if ($fileInfo['location'] == 'order_list_user_exchange' || $fileInfo['location'] == 'order_list_user_return' || $fileInfo['location'] == 'order_list_user_refund') {
                            // 고객 교환/반품/환불 신청 내역은 주문정보 엑셀다운로드와 상관없이 다운되게 처리
                        } else {
                            if ($session->get('manager.functionAuthState') == 'check' && $session->get('manager.functionAuth.orderExcelDown') != 'y') {
                                $this->js('top.dialog_alert(\'권한이 없습니다. 권한은 대표운영자에게 문의하시기 바랍니다.\')');
                            }
                        }
                    }

                    if ($fileInfo['menu'] === 'orderDraft') {
                        if ($session->get('manager.functionAuthState') == 'check' && $session->get('manager.functionAuth.orderExcelDown') != 'y') {
                            $this->js('top.dialog_alert(\'권한이 없습니다. 권한은 대표운영자에게 문의하시기 바랍니다.\')');
                        }
                    }

                    $policy = \App::load(ManageSecurityPolicy::class);
                    if ($policy->useExcelSecurityScope($fileInfo['menu'], $fileInfo['location'])) {
                        if ($policy->useSecurityIp() && $policy->notAuthenticationIp()) {
                            $logger->warning(sprintf('use excel download security. you are ip address [%s] not registered', $request->getRemoteAddress()));
                            $this->js('top.dialog_alert(\'엑셀 다운로드 보안 설정을 사용 중입니다.<br/>등록된 IP가 아니므로 엑셀 다운로드가 불가합니다.\')');
                        }
                        if ($policy->requireAuthInfo()) {
                            $this->js('top.dialog_alert(\'엑셀 다운로드 보안 인증을 사용 중입니다.<br/>로그인하신 운영자 계정은 인증정보가 없어 다운로드가 불가하오니,<br/>인증정보를 등록 후, 다시 시도해주세요.\')');
                        }
                        if (!$policy->hasAuthorize()) {
                            if ($policy->requireAuthorizeExcelDownload()) {
                                $this->js('top.open_excel_auth();');
                            }
                        }
                    }
                    \Component\Godo\MyGodoSmsServerApi::deleteAuth();   // 고도회원 인증 결과 제거
                    $fileName = explode(STR_DIVISION, $fileInfo['fileName']);
                    $downloadPath = $pathResolver->data($fileInfo['filePath'], $fileName[$postValue['excelKey']])->getRealPath();
                    $ext = pathinfo($downloadPath)['extension'];
                    $menuList = ['member', 'order', 'board', 'orderDraft', 'plusreview'];
                    if (in_array($fileInfo['menu'], $menuList)) {
                        $request->request()->set($fileInfo['menu'] . 'Log', true);
                        $request->post()->set('fileName', $fileName[$postValue['excelKey']]);
                        $request->post()->set('downloadFileName', $fileInfo['downloadFileName']);
                        $logAction = \App::load('Component\\Admin\\AdminLogDAO');
                        $logAction->setAdminLog();
                    }
                    $this->download($downloadPath, str_replace("/", "", urldecode($fileInfo['downloadFileName'])) . '_' . ($fileInfo['regDt']) . "." . $ext);
                    break;
                case 'searchList':
                    $request->get()->set('menu', $postValue['menu']);
                    $request->get()->set('location', $postValue['location']);
                    $getData = $excelRequest->getExcelRequestListForAdmin();
                    echo json_encode($getData['data']);
                    exit;
                    break;
                case 'search_form':
                    $request->get()->set('menu', $postValue['menu']);
                    $request->get()->set('location', $postValue['location']);
                    $excelForm = \App::load('\\Component\\Excel\\ExcelForm');
                    $getData = $excelForm->getExcelFormList();
                    echo json_encode($getData);
                    exit;
                    break;
                case 'countPersonalField':
                    $excelForm = \App::load('\\Component\\Excel\\ExcelForm');
                    $data = $excelForm->getDataExcelForm($postValue['formSno']);
                    echo $excelForm->countPersonalField(implode(STR_DIVISION,$data['data']['excelField']));
                    break;
                case 'checkAuthUseFl':
                    $checkAuthUseFl = false;
                    $fileInfo = $excelRequest->getInfoExcelRequest($postValue['sno'], 'menu, filePath,fileName,downloadFileName,regDt');
                    if ($fileInfo['menu'] == 'order') {
                        if ($fileInfo['location'] == 'order_list_user_exchange' || $fileInfo['location'] == 'order_list_user_return' || $fileInfo['location'] == 'order_list_user_refund') {
                            // 고객 교환/반품/환불 신청 내역은 주문정보 엑셀다운로드와 상관없이 다운되게 처리
                        } else {
                            if ($session->get('manager.functionAuthState') == 'check' && $session->get('manager.functionAuth.orderExcelDown') != 'y') {
                                $checkAuthUseFl = true;
                            }
                        }
                    }

                    if ($fileInfo['menu'] === 'orderDraft') {
                        if ($session->get('manager.functionAuthState') == 'check' && $session->get('manager.functionAuth.orderExcelDown') != 'y') {
                            $checkAuthUseFl = true;
                        }
                    }

                    $policy = \App::load(ManageSecurityPolicy::class);
                    if ($policy->useExcelSecurityScope($fileInfo['menu'], $fileInfo['location'])) {
                        if ($policy->useSecurityIp() && $policy->notAuthenticationIp()) {
                            $checkAuthUseFl = true;
                        }
                        if ($policy->requireAuthInfo()) {
                            $checkAuthUseFl = true;
                        }
                        if (!$policy->hasAuthorize()) {
                            if ($policy->requireAuthorizeExcelDownload()) {
                                $checkAuthUseFl = true;
                            }
                        }
                    }

                    echo json_encode($checkAuthUseFl);
                    exit;
                    break;
            }
        } catch (\Exception $e) {
            $logger->info($e->getMessage(), $e->getTrace());
            throw new LayerException($e->getMessage());
        }
        exit();
    }
}
