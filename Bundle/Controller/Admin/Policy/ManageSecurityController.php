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

use Component\Sms\Sms;
use Exception;
use Framework\Debug\Exception\LayerException;
use Framework\Utility\StringUtils;
use Component\Member\Manager;
use Component\Mall\MallDAO;

/**
 * 운영 보안 설정
 * [관리자 모드] 운영 보안 설정
 *
 * Class ManageSecurityController
 *
 * @package Bundle\Controller\Admin\Policy
 * @author  Seung-gak Kim <surlira@godo.co.kr>
 */
class ManageSecurityController extends \Controller\Admin\Controller
{
    /**
     * index
     *
     * @throws Exception
     */
    public function index()
    {
        $request = \App::getInstance('request');
        try {
            // --- 메뉴 설정
            $this->callMenu('policy', 'management', 'security');

            // --- 각 설정값 정보
            $dataSecurity = gd_policy('manage.security');
            $dataPageProtect = gd_policy('design.page_protect');

            if ($dataSecurity && $dataPageProtect) {
                $mode = 'modifyManageSecurity';
            } else {
                $mode = 'insertManageSecurity';
            }

            // --- 기본값 설정
            StringUtils::strIsSet($dataSecurity['smsSecurityFl'], 'n');//인증수단 > SMS인증
            StringUtils::strIsSet($dataSecurity['emailSecurityFl'], 'n');//인증수단 > 이메일인증
            StringUtils::strIsSet($dataSecurity['smsSecurity'], 'n');//보안로그인
            StringUtils::strIsSet($dataSecurity['screenSecurity'], 'n');//화면보안접속
            StringUtils::strIsSet($dataSecurity['sessionLimitUseFl'], 'n'); // 자동로그아웃
            StringUtils::strIsSet($dataSecurity['sessionLimitTime'], '7200'); // 자동로그아웃 시간 - 초
            StringUtils::strIsSet($dataSecurity['ipAdminSecurity'], 'n');
            StringUtils::strIsSet($dataSecurity['ipAdmin'], '');
            StringUtils::strIsSet($dataSecurity['ipFrontSecurity'], 'n');
            StringUtils::strIsSet($dataSecurity['ipFront'], '');
            StringUtils::strIsSet($dataSecurity['excel']['use'], 'y');
            StringUtils::strIsSet($dataSecurity['authLoginPeriod'], 0);
            StringUtils::strIsSet($dataPageProtect['unDragFl'], 'n');
            StringUtils::strIsSet($dataPageProtect['unContextmenuFl'], 'n');
            StringUtils::strIsSet($dataPageProtect['managerUnblockFl'], 'n');
            StringUtils::strIsSet($dataSecurity['ipLoginTry'], '');


            $manager = \App::load(Manager::class);
            $smsAuthCount = $manager->getManagerSmsAuthCheck();
            $superMangerInfo = $manager->getManagerData(1);

            // 국가별 접속 허용 및 차단 설정
            $countryAccessAllowed = [];
            $countryAccessBlocking = [];
            foreach (MallDAO::getInstance()->selectCountries() as $key => $val) {
                $countryAccessAllowed[$val['code']] = $val['countryNameKor'] . '(' . $val['countryName'] . ')';
            }
            gd_isset($dataSecurity['countryAccessAllowed'], $countryAccessAllowed);

            if ($dataSecurity['countryAccessBlocking']) {
                foreach ($dataSecurity['countryAccessBlocking'] as $code) {
                    $countryName = MallDAO::getInstance()->selectCountries($code);
                    $countryAccessBlocking[$code] = $countryName['countryNameKor'] . '(' . $countryName['countryName'] . ')';
                }
                unset($dataSecurity['countryAccessBlocking']);
                $dataSecurity['countryAccessBlocking'] = $countryAccessBlocking;
            }
            gd_isset(asort($dataSecurity['countryAccessBlocking']), '');

            if ($superMangerInfo['data']['isSmsAuth'] == 'y' && Sms::getPoint() > 0) {
                $dataSecurity['superCellPhoneFl'] = true;
            } else {
                $dataSecurity['superCellPhoneFl'] = false;
                $dataSecurity['superSecurityText'][] = 'SMS';
                $dataSecurity['smsSecurityFl'] = 'n';
            }

            if ($superMangerInfo['data']['isEmailAuth'] == 'y') {
                $dataSecurity['superEmailFl'] = true;
            } else {
                $dataSecurity['superEmailFl'] = false;
                $checked['emailSecurityFl'][$dataSecurity['emailSecurityFl']] = '';
                $dataSecurity['emailSecurityFl'] = 'n';

                $dataSecurity['superSecurityText'][] = '이메일';
            }

            $dataSecurity['superManagerModifyUrl'] = './manage_register.php?sno=' . $superMangerInfo['data']['sno'];

            if(empty($dataSecurity['excel']['scope']['company'])) $dataSecurity['excel']['scope']['company'][] = 'member';
            if(empty($dataSecurity['excel']['auth'])) $dataSecurity['excel']['auth'][] = 'sms';

            $checked = [];
            $checked['excel']['smsAuth'][$dataSecurity['excel']['smsAuth']] =
            $checked['excel']['use'][$dataSecurity['excel']['use']] =
            $checked['smsSecurityFl'][$dataSecurity['smsSecurityFl']] =
            $checked['emailSecurityFl'][$dataSecurity['emailSecurityFl']] =
            $checked['smsSecurity'][$dataSecurity['smsSecurity']] =
            $checked['screenSecurity'][$dataSecurity['screenSecurity']] =
            $checked['sessionLimitUseFl'][$dataSecurity['sessionLimitUseFl']] =
            $checked['ipAdminSecurity'][$dataSecurity['ipAdminSecurity']] =
            $checked['ipFrontSecurity'][$dataSecurity['ipFrontSecurity']] =
            $checked['unDragFl'][$dataPageProtect['unDragFl']] =
            $checked['unContextmenuFl'][$dataPageProtect['unContextmenuFl']] =
            $checked['managerUnblockFl'][$dataPageProtect['managerUnblockFl']] = 'checked="checked"';
            if (count($dataSecurity['excel']['scope']['company']) > 0) {
                foreach ($dataSecurity['excel']['scope']['company'] as $index => $item) {
                    $checked['excel']['scope']['company'][$item] = 'checked="checked"';
                }
            }
            if (count($dataSecurity['excel']['scope']['provider']) > 0) {
                foreach ($dataSecurity['excel']['scope']['provider'] as $index => $item) {
                    $checked['excel']['scope']['provider'][$item] = 'checked="checked"';
                }
            }
            if (count($dataSecurity['excel']['auth']) > 0) {
                foreach ($dataSecurity['excel']['auth'] as $index => $item) {
                    $checked['excel']['auth'][$item] = 'checked="checked"';
                }
            }
            if (count($dataSecurity['ipAdminBandWidth']) > 0) {
                foreach ($dataSecurity['ipAdminBandWidth'] as $key => $value) {
                    $checked['ipAdminBandWidthFl'][$key] = '';
                    if (trim($value) !== '') {
                        $checked['ipAdminBandWidthFl'][$key] = 'checked="checked"';
                    }
                }
            }
            if (count($dataSecurity['ipFrontBandWidth']) > 0) {
                foreach ($dataSecurity['ipFrontBandWidth'] as $key => $value) {
                    $checked['ipFrontBandWidthFl'][$key] = '';
                    if (trim($value) !== '') {
                        $checked['ipFrontBandWidthFl'][$key] = 'checked="checked"';
                    }
                }
            }
            if (count($dataSecurity['ipExcelBandWidth']) > 0) {
                foreach ($dataSecurity['ipExcelBandWidth'] as $key => $value) {
                    $checked['ipExcelBandWidthFl'][$key] = '';
                    if (trim($value) !== '') {
                        $checked['ipExcelBandWidthFl'][$key] = 'checked="checked"';
                    }
                }
            }
            if (count($dataSecurity['ipLoginTryAdminBandWidth']) > 0) {
                foreach ($dataSecurity['ipLoginTryAdminBandWidth'] as $key => $value) {
                    $checked['ipLoginTryAdminBandWidthFl'][$key] = '';
                    if (trim($value) !== '') {
                        $checked['ipLoginTryAdminBandWidthFl'][$key] = 'checked="checked"';
                    }
                }
            }

            $selected['sessionLimitTime'][$dataSecurity['sessionLimitTime']] = 'selected="selected"';

            // --- 관리자 디자인 템플릿
            $this->setData('mode', $mode);
            $this->setData('smsAuthCount', $smsAuthCount);
            $this->setData('remoteAddr', $request->getRemoteAddress());
            $this->setData('dataSecurity', $dataSecurity);
            $this->setData('dataPageProtect', $dataPageProtect);
            $this->setData('checked', $checked);
            $this->setData('selected', $selected);
        } catch (Exception $e) {
            throw new LayerException($e->getMessage());
        }
    }
}
