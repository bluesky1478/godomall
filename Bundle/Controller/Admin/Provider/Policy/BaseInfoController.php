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

use Component\Naver\NaverPay;
use Component\Database\DBTableField;
use Exception;
use Framework\Debug\Exception\LayerException;
use Framework\StaticProxy\Proxy\Session;
use Framework\Utility\ArrayUtils;
use Globals;

/**
 * Class BaseInfoController
 * @package Bundle\Controller\Admin\Provider\Policy
 * @author  Seung-gak Kim <surlira@godo.co.kr>
 */
class BaseInfoController extends \Controller\Admin\Controller
{
    /**
     * index
     *
     * @throws Except
     */
    public function index()
    {
        // --- 공급사 사용 설정 정보
        try {
            $getData = [];

            // --- 모듈 호출
            $scmAdmin = \App::load(\Component\Scm\ScmAdmin::class);
            // 공급사 고유 번호
            $scmNo = Session::get('manager.scmNo');
            // $scmNo 가 없으면 디비 디폴트 값 설정
            if ($scmNo > 0) {
                $getData = $scmAdmin->getScm($scmNo);
                if ($getData['scmPermissionInsert'] == 'a') {
                    $getData['scmPermissionInsert'] = __('자동승인');
                } else if ($getData['scmPermissionInsert'] == 'c') {
                    $getData['scmPermissionInsert'] = __('관리자승인');
                }
                if ($getData['scmPermissionModify'] == 'a') {
                    $getData['scmPermissionModify'] = __('자동승인');
                } else if ($getData['scmPermissionModify'] == 'c') {
                    $getData['scmPermissionModify'] = __('관리자승인');
                }
                if ($getData['scmPermissionDelete'] == 'a') {
                    $getData['scmPermissionDelete'] = __('자동승인');
                } else if ($getData['scmPermissionDelete'] == 'c') {
                    $getData['scmPermissionDelete'] = __('관리자승인');
                }
                if ($getData['zonecode'] == $getData['unstoringZonecode'] && $getData['addressSub'] == $getData['unstoringAddressSub']) {
                    $getData['chkSameUnstoringAddr'] = 'y';
                } else {
                    $getData['chkSameUnstoringAddr'] = 'n';
                }
                if ($getData['zonecode'] == $getData['returnZonecode'] && $getData['addressSub'] == $getData['returnAddressSub']) {
                    $getData['chkSameReturnAddr'] = 'y';
                } else if ($getData['unstoringZonecode'] == $getData['returnZonecode'] && $getData['unstoringAddressSub'] == $getData['returnAddressSub']) {
                    $getData['chkSameReturnAddr'] = 'x';
                } else {
                    $getData['chkSameReturnAddr'] = 'n';
                }
                if ($getData['staff']) {
                    $staff = gd_htmlspecialchars_stripslashes($getData['staff']);
                    $staff = json_decode($staff);
                    $getData['staff'] = $staff;
                }
                if ($getData['account']) {
                    //$account = gd_htmlspecialchars_stripslashes($getData['account']);
                    $account = json_decode($getData['account']);
                    $getData['account'] = $account;
                }
                //추가 수수료
                $getData['scmCommission'] .= '%(기본) ';
                $getData['scmCommissionDelivery'] .= '%(기본) ';

                if (gd_isset($getData['addCommissionData'])) {
                    foreach ($getData['addCommissionData'] as $key => $val) {
                        if ($val['commissionType'] == 'sell' && $val['commissionValue'] != 0.00) {
                            $getData['scmCommission'] .= '/ '.$val['commissionValue'].'% ';
                        }
                        if ($val['commissionType'] == 'delivery' && $val['commissionValue'] != 0.00) {
                            $getData['scmCommissionDelivery'] .= '/ '.$val['commissionValue'].'% ';
                        }
                    }
                }
                $getData['mode'] = 'modifyScmModify';
                $this->callMenu('policy', 'basic', 'info');
            } else {
                DBTableField::setDefaultData('tableScmManage', $getData);
                $getData['mode'] = 'insertScmRegist';
                $getData['chkSameUnstoringAddr'] = 'y';
                $getData['chkSameReturnAddr'] = 'y';

                $this->callMenu('policy', 'basic', 'info');
            }
            $department = gd_code('02001'); // 부서
            $account = gd_code('04002'); // --- 기본 은행 정보

            $naverPay = new NaverPay();
            $naverPayConfig = $naverPay->getConfig();
            $checked['chkSameUnstoringAddr'][$getData['chkSameUnstoringAddr']] =
            $checked['chkSameReturnAddr'][$getData['chkSameReturnAddr']] = 'checked="checked"';
            $checked['areaDelivery'][$naverPayConfig['deliveryData'][$scmNo]['areaDelivery']] = 'checked';
            $selected['couponBenefitType'][$getData['couponBenefitType']] =
            $selected['couponBenefitLimit'][$getData['couponBenefitLimit']] =
            $selected['couponBenefitLimitType'][$getData['couponBenefitLimitType']] = 'selected="selected"';
        } catch (Exception $e) {
            throw new LayerException($e->getMessage());
        }
        $this->addScript(
            [
                'jquery/jquery.multi_select_box.js',
                'jquery/validation/additional/businessnoKR.js'
            ]
        );
        $this->setData('account', $account);
        $this->setData('naverPay', gd_isset($naverPayConfig));
        $this->setData('scmNo', gd_isset($scmNo));
        $this->setData('getData', gd_isset($getData));
        $this->setData('department', $department);
        $this->setData('checked', gd_isset($checked));
        $this->setData('selected', gd_isset($selected));
    }
}
