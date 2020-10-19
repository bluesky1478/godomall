<?php
/**
 *
 * This is commercial software, only users who have purchased a valid license
 * and accept to the terms of the License Agreement can install and use this
 * program.
 *
 * Do not edit or add to this file if you wish to upgrade Godomall5 to newer
 * versions in the future.
 *
 * @copyright ⓒ 2016, NHN godo: Corp.
 * @link      http://www.godo.co.kr
 *
 */
namespace Bundle\Component\SiteLink;

use Exception;
use Framework\Utility\HttpUtils;
use Globals;
use Logger;

/**
 * SSL 관리 사이트 경로
 * @package Bundle\Component\SiteLinkAdmin
 * @author  su
 */
class SiteLinkAdmin
{
    const ECT_REQUEST_FREESSL_DUPLICATION = 'SitelinkAdmin.REQUEST_FREESSL_DUPLICATION';
    const ECT_REQUEST_FREESSL_FAILURE = 'SitelinkAdmin.REQUEST_FREESSL_FAILURE';
    const TEXT_REQUEST_FREESSL_DUPLICATION = '이미 신청되었습니다. 설치시간은 요청 후 최대 하루 입니다.';
    const TEXT_REQUEST_FREESSL_FAILURE = '서비스에 장애가 발생하였습니다. 잠시 후 이용해주시기 바랍니다.';

    // SSL 환경정보
    public $sslAdminCfg = [
        'sslType'     => '', // godo-유료,direct-직접설정
        'sslDomain'   => '', // 유료/직접설정 SSL 도메인
        'sslPort'     => '', // 유료/직접설정 SSL 포트
        'sslSdate'    => '', // 유료/직접설정 사용기간
        'sslEdate'    => '', // 유료/직접설정 사용기간
        'sslStep'     => '', // 유료 단계(wait,process)
        'userSslRule' => '', // 유료 추가 보안 페이지
        'sslImageUse' => '' // 유료 보안 이미지 노출
    ];
    public $sslFrontCfg = [
        'sslType'       => '', // free-무료,godo-유료,direct-직접설정
        'sslDomain'     => '', // 유료/직접설정 SSL 도메인
        'sslPort'       => '', // 유료/직접설정 SSL 포트
        'sslSdate'      => '', // 유료/직접설정 사용기간
        'sslEdate'      => '', // 유료/직접설정 사용기간
        'sslStep'       => '', // 유료 단계(wait,process)
        'userSslRule'   => '', // 유료 추가 보안 페이지
        'sslFreeDomain' => '', // 무료 SSL 도메인
        'sslImageUse'   => '' // 유료 보안 이미지 노출
    ];
    public $sslMobileCfg = [
        'sslType'     => '', // godo-유료,direct-직접설정
        'sslDomain'   => '', // 유료/직접설정 SSL 도메인
        'sslPort'     => '', // 유료/직접설정 SSL 포트
        'sslSdate'    => '', // 유료/직접설정 사용기간
        'sslEdate'    => '', // 유료/직접설정 사용기간
        'sslStep'     => '', // 유료 단계(wait,process)
        'userSslRule' => '', // 유료 추가 보안 페이지
        'sslImageUse' => '' // 유료 보안 이미지 노출
    ];
    // 유료보안서버 단계
    public $arSslStep;

    public function __construct()
    {
        $this->arSslStep = [
            'used'         => __('사용중'),
            'use'          => __('사용가능'),
            'request'      => __('신청대기'),
            'renewrequest' => __('연장신청대기'),
            'wait'         => __('입금대기중'),
            'process'      => __('처리중'),
        ];
        $this->sslAdminCfg = array_merge($this->sslAdminCfg, gd_policy('ssl.admin'));
        $this->sslFrontCfg = array_merge($this->sslFrontCfg, gd_policy('ssl.front'));
        $this->sslMobileCfg = array_merge($this->sslMobileCfg, gd_policy('ssl.mobile'));
        $this->gLicense = Globals::get('gLicense');
    }

    /**
     * SSL PC 쇼핑몰 설정정보
     * @author su
     * @return array
     * @deprecated 20180829 종료
     */
    public function getSslView()
    {
        $getData = $checked = $selected = [];
        gd_isset($this->sslFrontCfg['sslApplyLimit'], 'n');
        $checked['sslType'][$this->sslFrontCfg['sslType']] = 'checked="checked"';
        $checked['sslFreeImageUse'][$this->sslFrontCfg['sslFreeImageUse']] = 'checked="checked"';
        $checked['sslGodoImageUse'][$this->sslFrontCfg['sslGodoImageUse']] = 'checked="checked"';
        $checked['sslGodoImageType'][$this->sslFrontCfg['sslGodoImageType']] = 'checked="checked"';
        $checked['sslApplyLimit'][$this->sslFrontCfg['sslApplyLimit']] = 'checked="checked"';
        $data = $this->sslFrontCfg;

        // 무료보안서버정보(사용가능여부, SSL 도메인)
        $data['freeSsl'] = ['isUse' => false, 'freeDomain' => ''];
        if (in_array($this->gLicense['webCode'], ['webhost_outside', 'webhost_server']) === false) {
            $freedomain_result = HttpUtils::remoteGet('http://gongji.godo.co.kr/userinterface/get.basicdomain.php?sno=' . $this->gLicense['godosno']);
            if (empty($freedomain_result) === false) {
                try {
                    $out = HttpUtils::remoteGet('https://' . $freedomain_result . '/check_ssl_free.php');
                    if ($out !== false && $this->gLicense['godosno'] == $out) {
                        $data['freeSsl']['isUse'] = true;
                        $data['freeSsl']['freeDomain'] = $freedomain_result;
                    }
                } catch (\Exception $e) {
                    // 접속이 안되거나 정보가 없다면 무료보안서버 정보 없이 통과
                }
            }
        }

        // SSL 타입 리셋
        if ($this->sslFrontCfg['sslType'] == 'free' && $data['freeSsl']['isUse'] === false) {
            $this->sslFrontCfg['sslType'] = '';
        } else if ($this->sslFrontCfg['sslType'] == 'free' && in_array($this->gLicense['webCode'], ['webhost_outside','webhost_server'])) {
            $this->sslFrontCfg['sslType'] = '';
        } elseif ($this->sslFrontCfg['sslType'] == 'godo' && $this->gLicense['webCode'] == 'webhost_outside') {
            $this->sslFrontCfg['sslType'] = '';
        } elseif ($this->sslFrontCfg['sslType'] == 'direct' && $this->gLicense['webCode'] != 'webhost_outside') {
            $this->sslFrontCfg['sslType'] = '';
        }

        // 유료보안서버 단계
        $data['godoSsl'] = ['sslStep' => '', 'strSslStep' => ''];
        if (empty($this->sslFrontCfg['sslDomain']) === false) {
            $today = (int) date('Ymd');
            $ssl_sdate = (int) $this->sslFrontCfg['sslSdate'];
            $ssl_edate = (int) $this->sslFrontCfg['sslEdate'];
            if ($ssl_sdate <= $today && $ssl_edate >= $today) {
                if ($this->sslFrontCfg['sslType'] == 'godo') {
                    $data['godoSsl']['sslStep'] = 'used';
                } else {
                    $data['godoSsl']['sslStep'] = 'use';
                }
            } else {
                if (in_array($this->sslFrontCfg['sslStep'], ['wait', 'process']) === true) {
                    $data['godoSsl']['sslStep'] = $this->sslFrontCfg['sslStep'];
                } else {
                    $data['godoSsl']['sslStep'] = 'renewrequest';
                }
            }
        } else {
            if (in_array($this->sslFrontCfg['sslStep'], ['wait', 'process']) === true) {
                $data['godoSsl']['sslStep'] = $this->sslFrontCfg['sslStep'];
            } else {
                $data['godoSsl']['sslStep'] = 'request';
            }
        }
        $data['godoSsl']['strSslStep'] = $this->arSslStep[$data['godoSsl']['sslStep']];

        // 셋팅지원모드
        $data['setMode'] = '';
        if ($this->gLicense['webCode'] == 'webhost_server') {
            $data['setMode'] = 'serverhost';
        } else if (in_array($this->gLicense['webCode'], ['webhost_outside', 'webhost_server']) === false) {
            $data['setMode'] = 'webhost';
        } else if ($this->gLicense['webCode'] == 'webhost_outside') {
            $data['setMode'] = 'outhost';
        }

        // 유료보안서버 적용범위
        $data['userSslRule'] = explode(',', $data['userSslRule']);
        if (is_array(gd_isset($data['userSslRule'])) === false) {
            $data['userSslRule'][0] = '';
        }

        $getData['data'] = gd_htmlspecialchars_stripslashes($data);
        $getData['checked'] = $checked;
        $getData['selected'] = $selected;

        return $getData;
    }

    /**
     * SSL ADMIN 쇼핑몰 설정정보
     * @author su
     * @return array
     */
    public function getAdminSslView()
    {
        $getData = $checked = $selected = [];
        $checked['sslType'][$this->sslAdminCfg['sslType']] = 'checked="checked"';
        $data = $this->sslAdminCfg;

        // SSL 타입 리셋
        if ($this->sslAdminCfg['sslType'] == 'godo' && $this->gLicense['webCode'] == 'webhost_outside') {
            $this->sslAdminCfg['sslType'] = '';
        } elseif ($this->sslAdminCfg['sslType'] == 'direct' && $this->gLicense['webCode'] != 'webhost_outside') {
            $this->sslAdminCfg['sslType'] = '';
        }

        // 유료보안서버 단계
        $data['godoSsl'] = ['sslStep' => '', 'strSslStep' => ''];
        if (empty($this->sslAdminCfg['sslDomain']) === false) {
            $today = (int) date('Ymd');
            $ssl_sdate = (int) $this->sslAdminCfg['sslSdate'];
            $ssl_edate = (int) $this->sslAdminCfg['sslEdate'];
            if ($ssl_sdate <= $today && $ssl_edate >= $today) {
                if ($this->sslAdminCfg['sslType'] == 'godo') {
                    $data['godoSsl']['sslStep'] = 'used';
                } else {
                    $data['godoSsl']['sslStep'] = 'use';
                }
            } else {
                if (in_array($this->sslAdminCfg['sslStep'], ['wait', 'process']) === true) {
                    $data['godoSsl']['sslStep'] = $this->sslAdminCfg['sslStep'];
                } else {
                    $data['godoSsl']['sslStep'] = 'renewrequest';
                }
            }
        } else {
            if (in_array($this->sslAdminCfg['sslStep'], ['wait', 'process']) === true) {
                $data['godoSsl']['sslStep'] = $this->sslAdminCfg['sslStep'];
            } else {
                $data['godoSsl']['sslStep'] = 'request';
            }
        }
        $data['godoSsl']['strSslStep'] = $this->arSslStep[$data['godoSsl']['sslStep']];

        // 셋팅지원모드
        $data['setMode'] = '';
        if ($this->gLicense['webCode'] == 'webhost_server') {
            $data['setMode'] = 'serverhost';
        } else if (in_array($this->gLicense['webCode'], ['webhost_outside', 'webhost_server']) === false) {
            $data['setMode'] = 'webhost';
        } else if ($this->gLicense['webCode'] == 'webhost_outside') {
            $data['setMode'] = 'outhost';
        }

        // 유료보안서버 적용범위
        $data['userSslRule'] = explode(',', $data['userSslRule']);
        if (is_array(gd_isset($data['userSslRule'])) === false) {
            $data['userSslRule'][0] = '';
        }

        $getData['data'] = gd_htmlspecialchars_stripslashes($data);
        $getData['checked'] = $checked;
        $getData['selected'] = $selected;

        return $getData;
    }

    /**
     * SSL MOBILE 쇼핑몰 설정정보
     * @author su
     * @return array
     */
    public function getMobileSslView()
    {
        $getData = $checked = $selected = [];
        gd_isset($this->sslMobileCfg['sslApplyLimit'], 'n');
        $checked['sslType'][$this->sslMobileCfg['sslType']] = 'checked="checked"';
        $checked['sslApplyLimit'][$this->sslMobileCfg['sslApplyLimit']] = 'checked="checked"';
        $data = $this->sslMobileCfg;

        // SSL 타입 리셋
        if ($this->sslMobileCfg['sslType'] == 'godo' && $this->gLicense['webCode'] == 'webhost_outside') {
            $this->sslMobileCfg['sslType'] = '';
        } elseif ($this->sslMobileCfg['sslType'] == 'direct' && $this->gLicense['webCode'] != 'webhost_outside') {
            $this->sslMobileCfg['sslType'] = '';
        }

        // 유료보안서버 단계
        $data['godoSsl'] = ['sslStep' => '', 'strSslStep' => ''];
        if (empty($this->sslMobileCfg['sslDomain']) === false) {
            $today = (int) date('Ymd');
            $ssl_sdate = (int) $this->sslMobileCfg['sslSdate'];
            $ssl_edate = (int) $this->sslMobileCfg['sslEdate'];
            if ($ssl_sdate <= $today && $ssl_edate >= $today) {
                if ($this->sslMobileCfg['sslType'] == 'godo') {
                    $data['godoSsl']['sslStep'] = 'used';
                } else {
                    $data['godoSsl']['sslStep'] = 'use';
                }
            } else {
                if (in_array($this->sslMobileCfg['sslStep'], ['wait', 'process']) === true) {
                    $data['godoSsl']['sslStep'] = $this->sslMobileCfg['sslStep'];
                } else {
                    $data['godoSsl']['sslStep'] = 'renewrequest';
                }
            }
        } else {
            if (in_array($this->sslMobileCfg['sslStep'], ['wait', 'process']) === true) {
                $data['godoSsl']['sslStep'] = $this->sslMobileCfg['sslStep'];
            } else {
                $data['godoSsl']['sslStep'] = 'request';
            }
        }
        $data['godoSsl']['strSslStep'] = $this->arSslStep[$data['godoSsl']['sslStep']];

        // 셋팅지원모드
        $data['setMode'] = '';
        if ($this->gLicense['webCode'] == 'webhost_server') {
            $data['setMode'] = 'serverhost';
        } else if (in_array($this->gLicense['webCode'], ['webhost_outside', 'webhost_server']) === false) {
            $data['setMode'] = 'webhost';
        } else if ($this->gLicense['webCode'] == 'webhost_outside') {
            $data['setMode'] = 'outhost';
        }

        // 유료보안서버 적용범위
        $data['userSslRule'] = explode(',', $data['userSslRule']);
        if (is_array(gd_isset($data['userSslRule'])) === false) {
            $data['userSslRule'][0] = '';
        }

        $getData['data'] = gd_htmlspecialchars_stripslashes($data);
        $getData['checked'] = $checked;
        $getData['selected'] = $selected;

        return $getData;
    }

    /**
     * 무료SSL설치요청
     */
    public function requestFreessl()
    {
        $ssl = \App::load('\\Component\\SiteLink\\SecureSocketLayer');
//        $godoSsl = \App::load('Component\\Godo\\GodoSslServerApi');
//        $out = $godoSsl->setFreeSsl();
        $out = 'request';
        Logger::channel('service')->info('SSL_SET_FREE', [$out]);
        if ($out == 'request') {
            $ssl->setSslFree();
            return true;
        } else if ($out == 'duplication') {
            $ssl->setSslFree();
            throw new Exception(__('이미 신청되었습니다. 설치시간은 요청 후 최대 하루 입니다.'));
        } else {
            throw new Exception(__('서비스에 장애가 발생하였습니다. 잠시 후 이용해주시기 바랍니다.'));
        }
    }
}
