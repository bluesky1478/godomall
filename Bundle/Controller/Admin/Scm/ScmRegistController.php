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

use Component\Database\DBTableField;
use Exception;
use Framework\Debug\Exception\LayerException;
use Request;

class ScmRegistController extends \Controller\Admin\Controller
{

    /**
     * 공급사 등록
     * [관리자 모드] 공급사 등록
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
     */

    public function index()
    {

        // --- 공급사 사용 설정 정보
        try {
            $getData = [];
            // --- 모듈 호출
            $scmAdmin = \App::load(\Component\Scm\ScmAdmin::class);
            $scmCommission = \App::load(\Component\Scm\ScmCommission::class);
            // 공급사 고유 번호
            $scmNo = Request::get()->get('scmno');
            // $scmNo 가 없으면 디비 디폴트 값 설정
            if ($scmNo > 0) {
                $getData = $scmAdmin->getScm($scmNo);

                //판매 수수료, 배송비 수수료 동일적용 (배송비 수수료가 일정에 있을 경우 동일하더라도 체크하지 않음)
                $commissionSameFl = $scmCommission->compareWithScmCommission($getData['addCommissionData']);
                if ($commissionSameFl && $getData['scmCommission'] == $getData['scmCommissionDelivery'] && empty($getData['scmCommissionSameCheckBox'])) {
                    $checked['scmCommissionSame'] = 'checked="checked"';
                }

                if ($getData['scmKind'] == 'c') {
                    $getData['scmTypeReadOnly'] = 'disabled="disabled"';
                    $getData['scmKindName'] = '본사';
                } else {
                    $getData['scmTypeReadOnly'] = '';
                    $getData['scmKindName'] = '공급사';
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
                // 공급사 기능 권한
                $functionAuth = json_decode($getData['functionAuth'], true);
                if (is_array($functionAuth)) {
                    foreach ($functionAuth['functionAuth'] as $functionKey => $functionVal) {
                        $checked['functionAuth'][$functionKey][$functionVal] = 'checked="checked"';
                    }
                }

                if ($getData['staff']) {
                    $staff = gd_htmlspecialchars_stripslashes($getData['staff']);
                    $staff = json_decode($staff);
                    $getData['staff'] = $staff;
                }

                if ($getData['account']) {
                    $account = json_decode($getData['account']);
                    $getData['account'] = $account;
                }

                $getData['managerId'] = $scmAdmin->getScmSuperManagerId($scmNo);

                $getData['mode'] = 'modifyScmModify';
                $this->callMenu('scm', 'scm', 'scmModify');

                if ($getData['scmNo'] != DEFAULT_CODE_SCMNO) {
                    $this->getView()->setDefine('layoutFunctionAuth', 'policy/_manage_function_auth_scm.php');// 리스트폼
                }
            } else {
                DBTableField::setDefaultData('tableScmManage', $getData);
                $getData['mode'] = 'insertScmRegist';
                $getData['chkSameUnstoringAddr'] = 'y';
                $getData['chkSameReturnAddr'] = 'y';

                $this->callMenu('scm', 'scm', 'scmRegist');

                $this->getView()->setDefine('layoutFunctionAuth', 'policy/_manage_function_auth_scm.php');// 리스트폼
            }
            $department = gd_code('02001'); // 부서

            $account = gd_code('04002'); // --- 기본 은행 정보

            $popupMode = Request::get()->get('popupMode');
            if (isset($popupMode) === true) {
                $this->getView()->setDefine('layout', 'layout_blank.php');
            }
            // 저장소 설정
            $tmp = gd_policy('basic.storage');
            $defaultImageStorage = '';
            foreach ($tmp['storageDefault'] as $index => $item) {
                if (in_array('goods', $item)) {
                    if (is_null($scmNo)) {
                        $defaultImageStorage = $tmp['httpUrl'][$index];
                    }
                }
            }
            foreach ($tmp['httpUrl'] as $key => $val) {
                $conf['storage'][$val] = $tmp['storageName'][$key];
            }
            $conf['storage']['url'] = __("URL 직접입력");
            if (empty($defaultImageStorage) === false) {
                $getData['imageStorage'] = $defaultImageStorage;
            }
            unset($tmp);

            // 운영자 권한 설정
            if ($getData['mode'] == 'modifyScmModify' && $getData['scmNo'] == DEFAULT_CODE_SCMNO) {
                unset($getData['permissionSetupMode']);
            } else if (method_exists($scmAdmin, 'savePermissionScm') && method_exists($scmAdmin, 'getRepackManagerRegisterPermission') && $this->getData('managerPermissionMethodExist') === true && $this->getData('adminMenuPermissionMethodExist') === true) {
                if ($getData['mode'] == 'modifyScmModify') {
                    $repack = $scmAdmin->getRepackManagerRegisterPermission(['scmNo' => $getData['scmNo'], 'functionAuth' => $getData['functionAuth']]);
                } else {
                    $repack = $scmAdmin->getRepackManagerRegisterPermission();
                }
                if ($repack !== null) {
                    $getData['permissionSetupMode'] = 'managePermission';
                    $this->setData('data', $repack);
                }
            }

            $checked['scmType'][$getData['scmType']] =
            $checked['scmPermissionInsert'][$getData['scmPermissionInsert']] =
            $checked['scmPermissionModify'][$getData['scmPermissionModify']] =
            $checked['scmPermissionDelete'][$getData['scmPermissionDelete']] =
            $checked['chkSameUnstoringAddr'][$getData['chkSameUnstoringAddr']] =
            $checked['chkSameReturnAddr'][$getData['chkSameReturnAddr']] = 'checked="checked"';

            $selected['couponBenefitType'][$getData['couponBenefitType']] =
            $selected['couponBenefitLimit'][$getData['couponBenefitLimit']] =
            $selected['couponBenefitLimitType'][$getData['couponBenefitLimitType']] = 'selected="selected"';
        } catch (Exception $e) {
            throw new LayerException($e->getMessage());
        }

        if ($getData['permissionSetupMode'] == 'managePermission') {
            $this->addCss([
                'managePermissionStyle.css?' . time(), // 운영자 권한 설정 CSS
            ]);
            $this->addScript([
                'managePermission.js?' . time(), // 운영자 권한 설정 JS
            ]);
        }

        $this->addScript(
            [
                'jquery/jquery.multi_select_box.js',
                'jquery/validation/additional/businessnoKR.js'
            ]
        );
        $this->setData('account', $account);
        $this->setData('getData', gd_isset($getData));
        $this->setData('department', $department);
        $this->setData('conf', $conf);
        $this->setData('checked', gd_isset($checked));
        $this->setData('selected', gd_isset($selected));
        $this->setData('popupMode', gd_isset($popupMode));
        $this->setData('policy', \App::load('Component\\Policy\\JoinItemPolicy')->getStandardValidation());
    }
}
