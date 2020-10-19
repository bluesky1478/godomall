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
namespace Bundle\Controller\Admin\Scm;

use Component\Member\Manager;
use Component\Naver\NaverPay;
use Component\Policy\Policy;
use Exception;
use Framework\Debug\Exception\LayerNotReloadException;
use Message;
use Request;

class ScmPsController extends \Controller\Admin\Controller
{
    /**
     * 공급사 처리
     * [관리자 모드] 공급사 처리
     *
     * @author    su
     * @version   1.0
     * @since     1.0
     * @copyright ⓒ 2016, NHN godo: Corp.
     *
     * @param array $get
     * @param array $post
     * @param array $files
     *
     * @throws Except
     * @throws LayerNotReloadException
     */
    public function index()
    {
        try {
            // 모드 별 처리
            switch (Request::post()->get('mode')) {
                case 'insertScmRegist':
                case 'modifyScmModify':
                    // 모듈 호출
                    $scmAdmin = \App::load(\Component\Scm\ScmAdmin::class);
                    $postValue = Request::post()->toArray();
                    $filesValue = Request::files()->toArray();
                    $isProvider = Manager::isProvider();

                    $naverPay = new NaverPay();
                    if($naverPay->checkUse() && $isProvider) {
                        $deliveryKey = [
                            'returnPrice',
                            'areaDelivery',
                            'area22Price',
                            'area32Price',
                            'area33Price'
                        ];

                        $policy = new Policy();
                        $naverPayConfig = $policy->getNaverPaySetting();
                        foreach ($deliveryKey as $val) {
                            $naverPayConfig[$val] = $postValue[$val];
                        }
                        $policy->saveNaverPaySetting($naverPayConfig);
                    }

                    // 운영자 권한 설정 아닌 경우 주요현황-매출, 주요현황-주문, 상단 고정진열 적용 설정 유지되도록 처리
                    if (Request::post()->get('mode') == 'modifyScmModify' && Request::post()->get('permissionSetupMode') != 'managePermission' && $postValue['scmNo'] != DEFAULT_CODE_SCMNO) {
                        $tmp = $scmAdmin->getScmFunctionAuth($postValue['scmNo']);
                        if ($tmp['functionAuth']['goodsSortTop'] == 'y' && $postValue['functionAuth']['goodsSortTop'] != 'y') {
                            $postValue['functionAuth']['goodsSortTop'] = 'y';
                        }
                        if ($tmp['functionAuth']['mainStatisticsOrder'] == 'y' && $postValue['functionAuth']['mainStatisticsOrder'] != 'y') {
                            $postValue['functionAuth']['mainStatisticsOrder'] = 'y';
                        }
                        if ($tmp['functionAuth']['mainStatisticsSales'] == 'y' && $postValue['functionAuth']['mainStatisticsSales'] != 'y') {
                            $postValue['functionAuth']['mainStatisticsSales'] = 'y';
                        }
                    }

                    $scmNo = $scmAdmin->saveScm($postValue, $filesValue);

                    // 공급사 권한 저장
                    if (Request::post()->get('permissionSetupMode') == 'managePermission' && $scmNo != '' && method_exists($scmAdmin, 'savePermissionScm') && $this->getData('managerPermissionMethodExist') === true && $this->getData('adminMenuPermissionMethodExist') === true) {
                        $scmAdmin->savePermissionScm(Request::post()->toArray(), $scmNo);
                    }

                    $this->layer(__('저장이 완료되었습니다.'), null, null, null, 'top.location.href="scm_list.php";');
                    break;

                case 'deleteScmList':
                    $scmAdmin = \App::load(\Component\Scm\ScmAdmin::class);
                    $postValue = Request::post()->toArray();
                    $scmReturnCount = $scmAdmin->deleteScm($postValue['chkScm']);
                    if ($scmReturnCount > 0) {
                        $this->layer(sprintf(__('%1$s  개의 공급사가 주문 및 상품이 존재하여 삭제 되지 않았습니다.'),$scmReturnCount), null, null, null, 'top.location.href="scm_list.php";');
                    } else {
                        $this->layer(__('공급사가 삭제 되었습니다.'), null, null, null, 'top.location.href="scm_list.php";');
                    }
                    break;

                case 'insertScmCommissionSchedule':
                    $scmCommission = \App::load(\Component\Scm\ScmCommission::class);
                    $postValue = Request::post()->toArray();
                    $scmApplyFl = $scmCommission->saveScmScheduleCommission($postValue);
                    if(empty($scmApplyFl) === false){
                        $this->json($scmApplyFl);
                    }

                    break;

                case 'deleteScmCommissionSchedule':
                    $scmCommission = \App::load(\Component\Scm\ScmCommission::class);
                    $postValue = Request::post()->toArray();
                    $scmApplyFl = $scmCommission->deleteScmScheduleCommission($postValue);
                    if(empty($scmApplyFl) === false){
                        $this->json($scmApplyFl);
                    }

                    break;
                case 'stopScmCommissionSchedule':
                    $scmCommission = \App::load(\Component\Scm\ScmCommission::class);
                    $postValue = Request::post()->toArray();
                    $scmApplyFl = $scmCommission->stopScmScheduleCommission($postValue);
                    if(empty($scmApplyFl) === false){
                        $this->json($scmApplyFl);
                    }

                    break;
                case 'getScmCommissionSelectBox' : // 공급사 수수료 가져오기
                    $scmCommission = \App::load(\Component\Scm\ScmCommission::class);
                    $postValue = Request::post()->toArray();
                    $getScmCommission = $scmCommission->getScmCommissionDataConvert($postValue['scmNo']);
                    if($getScmCommission) {
                        $this->json($getScmCommission);
                    }
                    exit;
                    break;

                case 'checkScmModify':
                    $scmAdmin = \App::load(\Component\Scm\ScmAdmin::class);
                    $postValue = Request::post()->toArray();
                    if ($postValue['scmNo'] > 0) {
                        $scmMsg = '';
                        $scmOrderAdjustData = $scmAdmin->getScmOrderAdjust($postValue['scmNo']);
                        if ($scmOrderAdjustData['order']) {
                            $scmMsg .= __('주문 처리 건').'<br/>----------------------------------<br/>';
                            foreach ($scmOrderAdjustData['order'] as $key => $val) {
                                $scmMsg .= $val['name'] . ' : ' . $val['count'] . '건 <br/>';
                            }
                            $scmMsg .= '<br/>';
                        }
                        if ($scmOrderAdjustData['adjust']) {
                            $scmMsg .= __('정산 처리 건').'<br/>----------------------------------<br/>';
                            foreach ($scmOrderAdjustData['adjust'] as $key => $val) {
                                $scmMsg .= $val['name'] . ' : ' . $val['count'] . '건 <br/>';
                            }
                            $scmMsg .= '<br/>';
                        }
                    } else {
                        $scmMsg = __('공급사 정보가 없습니다.');
                    }
                    echo $scmMsg;
                    exit;
                    break;
                default:
                    exit();
                    break;
            }
        } catch (Exception $e) {
            //$this->layer($e->getMessage());
            throw new LayerNotReloadException($e->getMessage()); //새로고침안됨
        }
    }
}
