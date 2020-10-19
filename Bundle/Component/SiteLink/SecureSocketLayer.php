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

use Component\Database\DBTableField;
use Component\Policy\Policy;
use Component\Mall\MallDAO;
use Framework\Utility\ArrayUtils;
use Logger;
use Exception;
use Framework\Utility\HttpUtils;
use Framework\Utility\DateTimeUtils;
use Framework\Debug\Exception\LayerNotReloadException;

/**
 * SSL 관리 사이트 경로
 * @package Bundle\Component\SecureSocketLayer
 * @author  su
 */
class SecureSocketLayer
{
    // 유료보안서버 상태
    public $arrSslStatus;

    // 디비 접속
    /** @var \Framework\Database\DBTool $db */
    protected $db;

    public function __construct()
    {
        if (!is_object($this->db)) {
            $this->db = \App::load('DB');
        }
        $this->arrSslStatus = [
            'used'         => __('설치완료'),
            'use'          => __('설치완료'),
            'request'      => __('신청대기'),
            'renewrequest' => __('연장신청대기'),
            'wait'         => __('입금대기중'),
            'process'      => __('처리중'),
            'usePay'       => __('유료보안서버 사용중'),
        ];
    }

    /**
     * 무료 보안서버 설치 확인
     * @return bool
     */
    public function getFreeSslUse()
    {
        $globals = \App::getInstance('globals');
        $godoSno = $globals->get('gLicense.godosno');
        $godoSsl = \App::load('Component\\Godo\\GodoSslServerApi');
        $godoDomain = $godoSsl->getGodoDomain();
        $out = HttpUtils::remoteGet('https://' . $godoDomain . '/check_ssl_free.php');
        if ($out !== false && $godoSno == $out) {
            return true;
        }

        return false;
    }

    /**
     * Mall 도메인 리스트
     * @return array
     */
    public function globalMallDefaultDomainList()
    {
        $returnData = [];

        $siteLink = \App::load('Component\\SiteLink\\SiteLink');
        $mallService = \App::load('Component\\Mall\\Mall');
        $globalsInfo = $mallService->getList();
        foreach ($globalsInfo as $mallSno => $mallVal) {
            $domainList = json_decode($mallVal['connectDomain'], true);
            foreach ($domainList['connect'] as $domainVal) {
                $defaultDomain = $siteLink->getDefaultSslDomain($domainVal);
                $returnData[$defaultDomain]['mallSno'] = $mallSno;
                $returnData[$defaultDomain]['domainFl'] = $mallVal['domainFl'];
                $returnData[$defaultDomain]['mallName'] = $mallVal['mallName'];
                unset($defaultDomain);
            }
        }
        $godoSsl = \App::load('Component\\Godo\\GodoSslServerApi');
        $godoDomain = $godoSsl->getGodoDomain();
        $defaultDomain = $siteLink->getDefaultSslDomain($godoDomain);
        $returnData[$defaultDomain]['mallSno'] = 1;
        $returnData[$defaultDomain]['domainFl'] = 'kr';
        $returnData[$defaultDomain]['mallName'] = '기준몰';

        $policy = \App::load('Component\\Policy\\Policy');
        $data = $policy->getValue('basic.info', 1);
        $defaultDomain = $siteLink->getDefaultSslDomain($data['mallDomain']);
        $returnData[$defaultDomain]['mallSno'] = 1;
        $returnData[$defaultDomain]['domainFl'] = 'kr';
        $returnData[$defaultDomain]['mallName'] = '기준몰';
        unset($defaultDomain);

        return $returnData;
    }

    /**
     * default 도메인으로 Mall 정보
     * @param $domain
     *
     * @return array|mixed
     */
    public function getMallDomainSearch($domain)
    {
        $returnData = ['mallSno' => '1', 'domainFl' => 'kr', 'mallName' => '기준몰'];
        $mallDomainList = $this->globalMallDefaultDomainList();

        if (is_array($mallDomainList[$domain])) {
            $returnData = $mallDomainList[$domain];
        }
        return $returnData;
    }

    /**
     * @param null $sslData
     *
     * @return array
     */
    public function getSslDomainUse($sslData = null)
    {
        $returnData = ['use' => 'n', 'useString' => '사용안함'];
        if (is_array($sslData)) {
            $returnData['use'] = $sslData['sslConfigUse'];
            if ($sslData['sslConfigUse'] == 'y') {
                $returnData['useString'] = '사용함';
            }
        }

        return $returnData;
    }

    /**
     * @param null $sslData
     *
     * @return array
     * @throws \Framework\Debug\Exception\DatabaseException
     */
    public function getSslDomainStatus($sslData = null)
    {
        $returnData = ['status' => 'request', 'statusString' => '신청대기'];
        if (is_array($sslData)) {
            if (empty($sslData['sslConfigDomain']) === false) {
                if ($sslData['sslConfigType'] == 'free') {
                    $searchArr = [
                        'sslConfigType' => 'godo',
                        'sslConfigMallFl' => 'kr',
                        'sslConfigUse' => 'y',
                        'sslConfigPosition' => 'pc',
                    ];
                    $checkData = $this->getSsl($searchArr);
                    if (count($checkData) > 0) {
                        $returnData['status'] = 'usePay';
                    } else if ($this->getFreeSslUse()) {
                        $returnData['status'] = 'used';
                    } else {
                        $returnData['status'] = 'process';
                    }
                } else {
                    $nDate = date('Y-m-d');
                    $sDateCheck = DateTimeUtils::intervalDay($sslData['sslConfigStartDate'], $nDate);
                    $eDateCheck = DateTimeUtils::intervalDay($nDate, $sslData['sslConfigEndDate']);
                    if ($sDateCheck >= 0 && $eDateCheck >= 0) {
                        $returnData['status'] = 'used';
                    } else {
                        if (in_array($sslData['sslConfigStatus'], ['wait', 'process']) === true) {
                            $returnData['status'] = $sslData['sslConfigStatus'];
                        } else {
                            $returnData['status'] = 'renewrequest';
                        }
                    }
                }
            } else {
                if (in_array($sslData['sslConfigStatus'], ['wait', 'process']) === true) {
                    $returnData['status'] = $sslData['sslConfigStatus'];
                } else {
                    $returnData['status'] = 'request';
                }
            }
            $returnData['statusString'] = $this->arrSslStatus[$returnData['status']];
        }

        return $returnData;
    }


    /**
     * 관리자 보안서버 설정 정보
     * @param string $position
     *
     * @return mixed
     * @throws Exception
     */
    public function getSslSetting($position = 'pc')
    {
        $preFix = $this->getPrefixDomain($position);
        $godoSsl = \App::load('Component\\Godo\\GodoSslServerApi');
        $domainApiList = $godoSsl->getShopDomainList();
        $domainApiList = json_decode($domainApiList, true);
        // 임시도메인 임시 처리
        if ($domainApiList['resultCode'] == 'OK') {
            if ($preFix == 'www.') {
                $returnData['free']['domain'] = $domainApiList['data']['basicDomain'];
            } else {
                $returnData['free']['domain'] = $preFix . $domainApiList['data']['basicDomain'];
            }
            $returnData['free']['mall'] = $this->getMallDomainSearch($domainApiList['data']['basicDomain']);
            $returnData['free']['use'] = $this->getSslDomainUse();
            $returnData['free']['status'] = $this->getSslDomainStatus();
            if ($domainApiList['data']['shopDomain']) {
                $returnData['godo']['shop']['domain'] = $preFix . $domainApiList['data']['shopDomain'];
                $returnData['godo']['shop']['mall'] = $this->getMallDomainSearch($domainApiList['data']['shopDomain']);
                $returnData['godo']['shop']['use'] = $this->getSslDomainUse();
                $returnData['godo']['shop']['status'] = $this->getSslDomainStatus();
            }
            $i = 0;
            foreach ($domainApiList['data']['forwardDomain'] as $key => $val) {
                $returnData['godo']['forward'][$i]['domain'] = $preFix . $val;
                $returnData['godo']['forward'][$i]['mall'] = $this->getMallDomainSearch($val);
                $returnData['godo']['forward'][$i]['use'] = $this->getSslDomainUse();
                $returnData['godo']['forward'][$i]['status'] = $this->getSslDomainStatus();
                $i++;
            }
        } else {
            throw new Exception('도메인 리스트를 불러오지 못했습니다.');
        }

        $searchArr = [
            'sslConfigPosition' => $position,
        ];
        $sslData = $this->getSsl($searchArr);

        if (count($sslData) > 0) {
            foreach ($sslData as $key => $val) {
                //                $siteLink = \App::load('Component\\SiteLink\\SiteLink');
                //                $defaultDomain = $siteLink->getDefaultSslDomain($val['sslConfigDomain']);
                if ($val['sslConfigDomain'] == $returnData['free']['domain']) {
                    $returnData['free']['ssl'] = $val;
                    $returnData['free']['use'] = $this->getSslDomainUse($val);
                    $returnData['free']['status'] = $this->getSslDomainStatus($val);
                } else {
                    $shopSubDomain = '';
                    if ($position == 'pc') {
                        $shopDomainArr = explode('www.', $returnData['godo']['shop']['domain']);
                        $shopSubDomain = $shopDomainArr[1];
                    }
                    if ($val['sslConfigDomain'] == $returnData['godo']['shop']['domain']) {
                        $returnData['godo']['shop']['ssl'] = $val;
                        $returnData['godo']['shop']['use'] = $this->getSslDomainUse($val);
                        $returnData['godo']['shop']['status'] = $this->getSslDomainStatus($val);
                    } else if (empty($shopSubDomain) === false && $val['sslConfigDomain'] == $shopSubDomain) {
                        $returnData['godo']['shop']['domain'] = $shopSubDomain;
                        $returnData['godo']['shop']['ssl'] = $val;
                        $returnData['godo']['shop']['use'] = $this->getSslDomainUse($val);
                        $returnData['godo']['shop']['status'] = $this->getSslDomainStatus($val);
                    } else {
                        foreach ($returnData['godo']['forward'] as $forwardKey => $forwardVal) {
                            $forwardSubDomain = '';
                            if ($position == 'pc') {
                                $forwardDomainArr = explode('www.', $forwardVal['domain']);
                                $forwardSubDomain = $forwardDomainArr[1];
                            }
                            if ($val['sslConfigDomain'] == $forwardVal['domain']) {
                                $returnData['godo']['forward'][$forwardKey]['ssl'] = $val;
                                $returnData['godo']['forward'][$forwardKey]['use'] = $this->getSslDomainUse($val);
                                $returnData['godo']['forward'][$forwardKey]['status'] = $this->getSslDomainStatus($val);
                            } else if (empty($forwardSubDomain) === false && $val['sslConfigDomain'] == $forwardSubDomain) {
                                $returnData['godo']['forward'][$forwardKey]['domain'] = $forwardSubDomain;
                                $returnData['godo']['forward'][$forwardKey]['ssl'] = $val;
                                $returnData['godo']['forward'][$forwardKey]['use'] = $this->getSslDomainUse($val);
                                $returnData['godo']['forward'][$forwardKey]['status'] = $this->getSslDomainStatus($val);
                            }
                        }
                    }
                }
            }
        }

        // 2018/08/29 버젼에서는 관리자 멀티 도메인 접속 제한
        // 추후 오픈
        if ($position == 'admin') {
            unset($returnData['free']);
            unset($returnData['godo']['forward']);
        }

        return $returnData;
    }

    /**
     * @param $getValue
     *
     * @return mixed
     * @throws Exception
     */
    public function getSslView($getValue)
    {
        if ($getValue['domain']) {
            if ($getValue['configNo']) {
                $searchArr = [
                    'sslConfigNo' => $getValue['configNo'],
                ];
                $getData = $this->getSsl($searchArr);
                if ($getData[0]['sslConfigType'] == 'free') {
                    if ($this->getFreeSslUse()) {
                        $getData[0]['sslConfigStatus'] = 'used';
                    }
                    $searchArr = [
                        'sslConfigType' => 'godo',
                        'sslConfigMallFl' => 'kr',
                        'sslConfigUse' => 'y',
                    ];
                    $checkData = $this->getSsl($searchArr);
                } else {
                    $searchArr = [
                        'sslConfigType' => 'free',
                        'sslConfigUse' => 'y',
                    ];
                    $checkData = $this->getSsl($searchArr);
                }
                $getData[0]['sslConfigUserRule'] = explode(',', $getData[0]['sslConfigUserRule']);
                $getData[0]['checkAlert'] = count($checkData);
                $getData[0]['sslConfigStatusString'] = $this->arrSslStatus[$getData[0]['sslConfigStatus']];
            } else {
                $getData[0]['sslConfigDomain'] = $getValue['domain'];
                $getData[0]['sslConfigPosition'] = $getValue['position'];
                $getData[0]['sslConfigType'] = $getValue['type'];
                $getData[0]['sslConfigStatus'] = 'request';
                $getData[0]['sslConfigStatusString'] = $this->arrSslStatus[$getData[0]['sslConfigStatus']];
                DBTableField::setDefaultData('tableSslConfig', $getData[0]);
            }
        } else {
            throw new Exception('설치 신청을 하셔야 합니다.');
        }

        return $getData[0];
    }

    /**
     * @param array $arrData
     *
     * @return array|object
     * @throws \Framework\Debug\Exception\DatabaseException
     */
    public function getSsl($arrData = null)
    {
        // 쿼리 구성
        $this->db->strField = '*';
        //        $this->db->strJoin = implode(' ', $join);
        if ($arrData['sslConfigNo']) {
            $arrWhere[] = 'sc.sslConfigNo = ?';
            $this->db->bind_param_push($arrBind, 'i', $arrData['sslConfigNo']);
        }
        if ($arrData['sslConfigDomain']) {
            $arrWhere[] = 'sc.sslConfigDomain = ?';
            $this->db->bind_param_push($arrBind, 's', $arrData['sslConfigDomain']);
        }
        if ($arrData['sslConfigPosition']) {
            $arrWhere[] = 'sc.sslConfigPosition IN ("all", ?)';
            $this->db->bind_param_push($arrBind, 's', $arrData['sslConfigPosition']);
        }
        if ($arrData['sslConfigMallFl']) {
            $arrWhere[] = 'sc.sslConfigMallFl = ?';
            $this->db->bind_param_push($arrBind, 's', $arrData['sslConfigMallFl']);
        }
        if ($arrData['sslConfigUse']) {
            $arrWhere[] = 'sc.sslConfigUse = ?';
            $this->db->bind_param_push($arrBind, 's', $arrData['sslConfigUse']);
        }
        if ($arrData['sslConfigType']) {
            $arrWhere[] = 'sc.sslConfigType = ?';
            $this->db->bind_param_push($arrBind, 's', $arrData['sslConfigType']);
        }
        if ($arrData['sslConfigMainDomain']) {
            $arrWhere[] = 'sc.sslConfigMainDomain = ?';
            $this->db->bind_param_push($arrBind, 's', $arrData['sslConfigMainDomain']);
        }
        if ($arrData['sslConfigSearchType'] == 'redirect') {
            $this->db->strLimit = '0, 1';
        }
        $this->db->strWhere = implode(' AND ', $arrWhere);
        $this->db->strOrder = 'sc.sslConfigNo asc';
        $query = $this->db->query_complete();
        $strSQL = 'SELECT ' . array_shift($query) . ' FROM ' . DB_SSL_CONFIG . ' as sc ' . implode(' ', $query);
        $getData = $this->db->query_fetch($strSQL, $arrBind, true);

        return $getData;
    }

    /**
     * @param $position
     *
     * @return mixed
     */
    public function getPrefixDomain($position)
    {
        $subDomainArr = [
            'pc' => 'www.',
            'mobile' => 'm.',
            'api' => 'api.',
            'admin' => 'gdadmin.',
        ];

        return $subDomainArr[$position];
    }

    /**
     * @param $hostDomain
     * @param $position
     *
     * @return string
     */
    public function getSearchSslDomain($hostDomain, $position)
    {
        if (preg_match('/\.godomall\.com/i', $hostDomain)) {
            $perFix = '';
        } else {
            $perFix = $this->getPrefixDomain($position);
        }
        $siteLink = \App::load('Component\\SiteLink\\SiteLink');
        $defaultDomain = $siteLink->getDefaultSslDomain($hostDomain);

        return $perFix . $defaultDomain;
    }

    /**
     * @param $hostDomain
     *
     * @return string
     * @throws \Framework\Debug\Exception\DatabaseException
     */
    public function getDomainSslCheck($hostDomain)
    {
        $searchArr = [
            'sslConfigDomain' => $hostDomain,
        ];
        $sslDomainData = $this->getSsl($searchArr);

        if ($sslDomainData[0]['sslConfigDomain'] && $sslDomainData[0]['sslConfigUse'] == 'y' && $sslDomainData[0]['sslConfigType'] == 'free') {
            return 'free';
        } else if ($sslDomainData[0]['sslConfigDomain'] && $sslDomainData[0]['sslConfigUse'] == 'y' && $sslDomainData[0]['sslConfigType'] == 'godo'){
            return 'godo';
        } else {
            return 'F';
        }
    }

    /**
     * @param array $searchSslArrData
     *
     * @return array|mixed
     * @throws \Framework\Debug\Exception\DatabaseException
     */
    public function getRedirectSsl($searchSslArrData)
    {
        $sslData = [];
        $searchSslDomain = $this->getSearchSslDomain($searchSslArrData['sslConfigDomain'], $searchSslArrData['sslConfigPosition']);
        // 접속 도메인 안에 position 에 맞는 보안서버 있는지 체크
        $searchArr = [
            'sslConfigDomain' => $searchSslDomain,
            'sslConfigPosition' => $searchSslArrData['sslConfigPosition'],
        ];
        $sslDomainData = $this->getSsl($searchArr);
        if ($sslDomainData[0]['sslConfigDomain'] && $sslDomainData[0]['sslConfigUse'] == 'y') {
            $sslData = $sslDomainData[0];
        } else {
            // 접속 해외상점 안에 position 에 맞는 보안서버 있는지 체크
            $searchArr = [
                'sslConfigMallFl' => $searchSslArrData['sslConfigMallFl'],
                'sslConfigPosition' => $searchSslArrData['sslConfigPosition'],
                'sslConfigUse' => 'y',
                'sslConfigSearchType' => 'redirect',
            ];
            $sslMallFlData = $this->getSsl($searchArr);
            if ($sslMallFlData[0]['sslConfigDomain'] && $sslMallFlData[0]['sslConfigUse'] == 'y') {
                $sslData = $sslMallFlData[0];
            }
        }
        
        if ($sslData['sslConfigType'] === 'free') {
            $searchArr = [
                'sslConfigMallFl' => 'kr',
                'sslConfigPosition' => 'pc',
                'sslConfigMainDomain' => 'y',
            ];
            $sslSuperDomain = $this->getSsl($searchArr);
            if ($sslSuperDomain[0]['sslConfigDomain']) {
                $sslData['sslConfigSuperDomain'] = $sslSuperDomain[0]['sslConfigDomain'];
            }
        }

        return $sslData;
    }

    /**
     * @throws \Framework\Debug\Exception\DatabaseException
     */
    public function setSslFree()
    {
        $searchArr = [
            'sslConfigType' => 'free',
        ];
        $searchData = $this->getSsl($searchArr);
        if (count($searchData) === 0) {
            $godoSsl = \App::load('Component\\Godo\\GodoSslServerApi');
            $sslData['sslConfigDomain'] = $godoSsl->getGodoDomain();;
            if ($this->getFreeSslUse()) {
                $sslData['sslConfigStatus'] = 'used';
            } else {
                $sslData['sslConfigStatus'] = 'process';
            }
            $sslData['sslConfigType'] = 'free';
            $sslData['sslConfigPosition'] = 'pc';
            if ($sslData['sslConfigDomain']) {
                $this->saveSsl($sslData);
            }
        }
    }

    /**
     * @param $mallFl
     *
     * @throws \Framework\Debug\Exception\DatabaseException
     */
    public function setSslMallFlReset($mallFl)
    {
        $strSql = "UPDATE " . DB_SSL_CONFIG . " SET sslConfigMallFl = ? WHERE sslConfigMallFl = ?";
        $this->db->bind_query(
            $strSql,
            [
                'ss',
                'kr',
                $mallFl,
            ]
        );
        Logger::channel('service')->info('SSL_MallFl_RESET', [$mallFl]);
    }

    /**
     * @param $domainArr
     * @param $mallFl
     *
     * @throws \Framework\Debug\Exception\DatabaseException
     */
    public function setSslMallFlDiff($domainArr, $mallFl)
    {
        $searchArr = [
            'sslConfigMallFl' => $mallFl,
        ];
        $mallFlList = $this->getSsl($searchArr);
        $mallDomainList = array_column($mallFlList, 'sslConfigDomain', 'sslConfigNo');
        $defaultDomain = [];
        $siteLink = \App::load('Component\\SiteLink\\SiteLink');
        foreach ($mallDomainList as $key => $val) {
            $defaultDomain[$key] = $siteLink->getDefaultSslDomain($val);
        }
        $mallDefaultDomain = array_unique($defaultDomain);
        $resetMallDomain = array_diff($mallDefaultDomain, $domainArr);
        $this->setSslMallFlUpdate($resetMallDomain, 'kr');
    }

    /**
     * @param $domainArr
     * @param $mallFl
     *
     * @throws \Framework\Debug\Exception\DatabaseException
     */
    public function setSslMallFlUpdate($domainArr, $mallFl)
    {
        foreach ($domainArr as $domain) {
            $strSql = "UPDATE " . DB_SSL_CONFIG . " SET sslConfigMallFl = ? WHERE sslConfigType = ? AND sslConfigDomain IN (?, ?, ?, ?, ?, ?)";
            $this->db->bind_query(
                $strSql,
                [
                    'ssssssss',
                    $mallFl,
                    'godo',
                    $domain,
                    'www.'.$domain,
                    'm.'.$domain,
                    'gdadmin.'.$domain,
                    'api.'.$domain,
                    '*.'.$domain,
                ]
            );
            Logger::channel('service')->info('SSL_MallFl', [$domain, $mallFl]);
        }
    }

    /**
     * @param $domainArr
     * @param $mallFl
     *
     * @throws LayerNotReloadException
     */
    public function setSslMallFl($domainArr, $mallFl)
    {
        try {
            if (is_array($domainArr)) {
                $this->setSslMallFlUpdate($domainArr, $mallFl);
                $this->setSslMallFlDiff($domainArr, $mallFl);
            } else {
                $this->setSslMallFlReset($mallFl);
            }
        } catch (Exception $e) {
            throw new LayerNotReloadException($e->getMessage());
        }
    }

    /**
     * @param $sendData
     *
     * @throws Exception
     */
    public function saveSsl($sendData)
    {
        if (!$sendData['sslConfigDomain']) {
            Logger::channel('service')->info('SSL_SETTING', ['domain is null']);
            throw new Exception('domain is null', 001);
        }
        if ($sendData['sslConfigType'] == 'free') { // 보안서버 유료/무료
            $godoSsl = \App::load('Component\\Godo\\GodoSslServerApi');
            $sendData['sslConfigDomain'] = $godoSsl->getGodoDomain();
            $sendData['sslConfigImageUse'] = $sendData['sslFreeImageUse'];
        } else {
            $sendData['sslConfigImageUse'] = $sendData['sslGodoImageUse'];
        }
        unset($sendData['sslFreeImageUse']);
        unset($sendData['sslGodoImageUse']);
        unset($sendData['mode']);
        unset($sendData['checkAlert']);

        gd_isset($sendData['sslConfigPort'], '443'); // 보안서버 port 없으면 443
        gd_isset($sendData['sslConfigStatus'], 'used'); // 보안서버 상태
        gd_isset($sendData['sslConfigApplyLimit'], 'n'); // 보안서버 적용페이지 범위

        $domainArr = explode('.', $sendData['sslConfigDomain']);
        if ($domainArr[0] == 'm') {
            $sendData['sslConfigPosition'] = 'mobile';
        } else if ($domainArr[0] == 'gdadmin') {
            $sendData['sslConfigPosition'] = 'admin';
            $sendData['sslConfigApplyLimit'] = 'y';
        } else if ($domainArr[0] == 'api') {
            $sendData['sslConfigPosition'] = 'api';
        } else if ($domainArr[0] == '*') {
            $sendData['sslConfigPosition'] = 'all';
        } else {
            $sendData['sslConfigPosition'] = 'pc';
        }
        $sendData['sslConfigUserRule'] = implode(',', ArrayUtils::removeEmpty($sendData['sslConfigUserRule']));

        $siteLink = \App::load('Component\\SiteLink\\SiteLink');
        $defaultDomain = $siteLink->getDefaultSslDomain($sendData['sslConfigDomain']);
        $domainMall = $this->getMallDomainSearch($defaultDomain);
        $sendData['sslConfigMallFl'] = $domainMall['domainFl'];

        $searchArr = [
            'sslConfigDomain' => $sendData['sslConfigDomain'],
        ];
        $getSsl = $this->getSsl($searchArr);

        if (count($getSsl) > 0) {
            // 수정
            $arrBind = $this->db->get_binding(DBTableField::getBindField('tableSslConfig'), $sendData, 'update', array_keys($sendData), ['sslConfigNo']);
            $this->db->bind_param_push($arrBind['bind'], 'i', $getSsl[0]['sslConfigNo']);
            $rows = $this->db->set_update_db(DB_SSL_CONFIG, $arrBind['param'], 'sslConfigNo = ?', $arrBind['bind'], false);
            if ($rows > 0) {
                if ($sendData['sslConfigType'] == 'free' && $sendData['sslConfigUse'] == 'y') {
                    // 유료보안서버는 사용안함 처리
                    $strSql = "UPDATE " . DB_SSL_CONFIG . " SET sslConfigUse = ? WHERE sslConfigType = ? AND sslConfigMallFl = ? AND sslConfigPosition = ?";
                    $this->db->bind_query(
                        $strSql,
                        [
                            'ssss',
                            'n',
                            'godo',
                            'kr',
                            'pc',
                        ]
                    );
                }
                if ($sendData['sslConfigType'] == 'godo' && $sendData['sslConfigUse'] == 'y' && $getSsl[0]['sslConfigMallFl'] == 'kr') {
                    // 무료보안서버는 사용안함 처리
                    $strSql = "UPDATE " . DB_SSL_CONFIG . " SET sslConfigUse = ? WHERE sslConfigType = ? AND sslConfigMallFl = ? AND sslConfigPosition = ?";
                    $this->db->bind_query(
                        $strSql,
                        [
                            'ssss',
                            'n',
                            'free',
                            'kr',
                            'pc',
                        ]
                    );
                }
                Logger::channel('service')->info('SSL_UPDATE', ['OK', $arrBind]);
            } else {
                Logger::channel('service')->info('SSL_UPDATE', ['FAIL', $arrBind]);
            }
        } else {
            // 저장
            $arrBind = $this->db->get_binding(DBTableField::getBindField('tableSslConfig'), $sendData, 'insert', null, ['sslConfigNo']);
            $this->db->set_insert_db(DB_SSL_CONFIG, $arrBind['param'], $arrBind['bind'], 'y');
            if ($this->db->insert_id() > 0) {
                Logger::channel('service')->info('SSL_INSERT', ['OK', $arrBind]);
                if ($sendData['sslConfigType'] == 'godo' && $sendData['sslConfigUse'] == 'y' && $sendData['sslConfigMallFl'] == 'kr') {
                    // 무료보안서버는 사용안함 처리
                    $strSql = "UPDATE " . DB_SSL_CONFIG . " SET sslConfigUse = ? WHERE sslConfigType = ? AND sslConfigMallFl = ? AND sslConfigPosition = ?";
                    $this->db->bind_query(
                        $strSql,
                        [
                            'ssss',
                            'n',
                            'free',
                            'kr',
                            'pc',
                        ]
                    );
                }
            } else {
                Logger::channel('service')->info('SSL_INSERT', ['FAIL', $arrBind]);
            }
        }
        unset($arrBind);
        unset($sslData);
    }

    /**
     * @param $shopDomain
     *
     * @throws \Framework\Debug\Exception\DatabaseException
     */
    public function clearSsl($shopDomain)
    {
        if ($shopDomain) {
            $arrBind = [];
            $arrField = [
                'sslConfigType = ?',
                'sslConfigDomain IN (?, ?, ?, ?, ?, ?)',
            ];
            $this->db->bind_param_push($arrBind, 's', 'godo');
            $this->db->bind_param_push($arrBind, 's', $shopDomain);
            $this->db->bind_param_push($arrBind, 's', 'www.'.$shopDomain);
            $this->db->bind_param_push($arrBind, 's', 'm.'.$shopDomain);
            $this->db->bind_param_push($arrBind, 's', 'gdadmin.'.$shopDomain);
            $this->db->bind_param_push($arrBind, 's', 'api.'.$shopDomain);
            $this->db->bind_param_push($arrBind, 's', '*.'.$shopDomain);
            $rows = $this->db->set_delete_db(DB_SSL_CONFIG, implode(' AND ', $arrField), $arrBind);
            Logger::channel('service')->info('SSL_DELETE', [$rows, 'OK']);
        }
    }
}
