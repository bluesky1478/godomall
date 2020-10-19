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

namespace Bundle\Component\Mall;

use Component\GoodsStatistics\GoodsStatistics;
use Component\Order\OrderSalesStatistics;
use Component\Sms\Sms;
use Component\Validator\Validator;
use Component\VisitStatistics\VisitStatistics;
use Exception;
use Framework\Object\PropertiesAccessorTrait;
use Framework\Utility\ComponentUtils;
use Framework\Utility\DateTimeUtils;
use Framework\Utility\GodoUtils;
use Framework\Utility\StringUtils;
use Globals;
use Session;

/**
 * 멀티 상점 클래스 입니다. 해당 클래스는 튜닝을 하지 마시기 바랍니다.
 * @package Bundle\Component\Mall
 * @author  yjwee <yeongjong.wee@godo.co.kr>
 * @method MallDAO getMallDao()
 */
class Mall
{
    use PropertiesAccessorTrait;

    /** 글로벌 상점 공통 사용 기본 정보 **/
    const GLOBAL_MALL_BASE_INFO = [
        'mallNmEng',
        'mallFavicon',
        'mallDomain',
        'mallCategory',
        'businessNo',
        'service',
        'item',
        'email',
        'stampImage',
    ];
    /** 설정 공통 사용 **/
    const GLOBAL_MALL_SHARE = [
        'basic.info',
        'order.status',
        'member.paycoLogin',
        'member.snsLogin',
    ];
    /** 상품상세 이용안내 해외몰 기본 groupCd **/
    const GLOBAL_MALL_DETAIL_INFO = [
        'detailInfoDelivery' => '002001',
        'detailInfoAS'       => '003001',
        'detailInfoRefund'   => '004001',
        'detailInfoExchange' => '005001',
    ];
    /** @var  \Component\Mall\MallDAO $mallDao 상점 DAO 클래스 */
    protected $mallDao;
    /** @var  \Component\Design\SkinSave $skinSave 스킨저장 클래스 */
    protected $skinSave;
    /** @var  \Component\ExchangeRate\ExchangeRate $exchangeRate 환율 및 통화 클래스 */
    protected $exchangeRate;
    /** @var  array $mallInfo 쇼핑몰 정보 */
    protected $mallInfo;
    /** @var array $useMallList 사용중인 상점 */
    protected $useMallList;

    /**
     * Mall 생성자.
     *
     * @param array $config
     */
    public function __construct(array $config = [])
    {
        if (empty($config) === true) {
            $app = \App::getInstance('application');
            $config = [
                'mallDao'      => $app->load('Component\\Mall\\MallDAO'),
                'skinSave'     => $app->load('Component\\Design\\SkinSave'),
                'exchangeRate' => $app->load('Component\\ExchangeRate\\ExchangeRate'),
            ];
        }
        $this->mallDao = $config['mallDao'];
        $this->skinSave = $config['skinSave'];
        $this->exchangeRate = $config['exchangeRate'];
    }

    /**
     * 세션에 있는 멀티상점 정보를 2차 키값으로 가져오기
     *
     * @static
     *
     * @param string $key
     * @param null   $default
     *
     * @return mixed
     * @author Jong-tae Ahn <qnibus@godo.co.kr>
     */
    public static function getSession($key, $default = null)
    {
        return \App::getInstance('session')->get(SESSION_GLOBAL_MALL . '.' . $key, $default);
    }

    /**
     * 사용중인 상점 정보 반환하며 배열의 키값은 상점번호로 설정
     *
     * @return array
     */
    public function getListByUseMall()
    {
        $result = [];
        if (!$this->hasUseMallList()) {
            $this->useMallList = $this->mallDao->selectUsableMallList();
        }
        foreach ($this->useMallList as $index => $item) {
            if ($item['sno'] == DEFAULT_MALL_NUMBER) $item['standardFl'] = "y"; //기준몰여부 추가
            else $item['standardFl'] = "n";

            $skinData = gd_policy('design.skin', $item['sno']);
            $item['skin'] = $skinData;
            $result[$item['sno']] = $item;
        }

        return $result;
    }

    /**
     * 사용중인 해외몰 상점 정보 반환 키 값은 상점번호로 설정
     *
     * @return array
     */
    public function getUseGlobalMall()
    {
        $result = [];
        if (!$this->hasUseMallList()) {
            $this->useMallList = $this->mallDao->selectUsableMallList();
        }
        foreach ($this->useMallList as $index => $item) {
            if ($item['domainFl'] !== 'kr') {
                $result[$item['sno']] = $item;
            }
        }

        return $result;
    }

    /**
     * 상점 정보를 반환하는 함수
     *
     * @param array $params 조회 조건
     *
     * @return array|object
     */
    public function getList(array $params = [])
    {
        $result = [];
        $mallList = $this->mallDao->selectMallList($params);
        foreach ($mallList as $index => $item) {
            if ($item['sno'] == DEFAULT_MALL_NUMBER) $item['standardFl'] = "y"; //기준몰여부 추가
            else $item['standardFl'] = "n";
            $result[$item['sno']] = $item;
        }

        return $result;
    }

    /**
     * 상점을 사용하는지 여부 반환
     *
     * @return bool
     * @author Jong-tae Ahn <qnibus@godo.co.kr>
     */
    public function isUsableMall()
    {
        if (!$this->hasUseMallList()) {
            $this->useMallList = $this->mallDao->selectUsableMallList();
        }

        return (count($this->useMallList) > 1);
    }

    /**
     * 멀티 상점 수정
     *
     * @param array $mallInfo
     *
     * @throws Exception
     */
    public function modifyMall(array $mallInfo)
    {
        \App::getInstance('logger')->info(__METHOD__, $mallInfo);
        if (StringUtils::strIsSet($mallInfo['domainFl'], '') === '') {
            throw new Exception(__('멀티 상점 정보 수정에 필요한 정보가 없습니다.'));
        }

        // 자동환율데이터가 없는 경우를 대비해 자동으로 insert 처리
        $this->exchangeRate->manualSetExchangeRateAuto();

        // 환율데이터가 없는 경우 저장 불가
        $globalCurrency = reset($this->exchangeRate->getGlobalCurrency($mallInfo['sno'] - 1));
        $recentExchangeRate = $this->exchangeRate->getExchangeRate();
        $exchangeRate = $recentExchangeRate['exchangeRate' . $globalCurrency['isoCode']];
        if (empty($exchangeRate) === true || $exchangeRate == 0) {
            throw new Exception('해외상점을 사용하시기 위해서는 반드시 환율설정을 먼저 입력 바랍니다.');
        }

        // 우선국가 설정
        StringUtils::strIsSet($mallInfo['recommendCountryCode'], 'kr');

        // 언어별 기본 스킨 세팅 (설정 및 자동 다운)
        $mallInfo['skinPc'] = $this->setDefaultSkin($mallInfo, 'skinPc');
        $mallInfo['skinMobile'] = $this->setDefaultSkin($mallInfo, 'skinMobile');

        // 국제 통화 테이블 설정 (자리수)
        $globalCurrencyInfo['globalCurrencyDecimal'] = gd_isset($mallInfo['globalCurrencyDecimal'], 2);
        $globalCurrencyInfo['globalCurrencyDecimalFormat'] = gd_isset($mallInfo['globalCurrencyDecimalFormat'], '0.00');

        // 정보 저장
        $this->mallInfo = $mallInfo;
        $this->validateMall();
        $this->setMallInfo();
        $this->exchangeRate->setGlobalCurrencyDecimal($mallInfo['sno'] - 1, $globalCurrencyInfo);
        $this->mallDao->updateMall($this->mallInfo);

        // 사용 스킨 변경
        $this->changeLiveSkin($mallInfo, $mallInfo['sno']);
    }

    /**
     * 멀티상점 정보 검증 함수
     *
     * @throws Exception
     */
    public function validateMall()
    {
        $v = new Validator();
        $v->add('useFl', 'yn', true, '{' . __('사용설정') . '}');
        $v->add('currencyDisplayFl', 'pattern', true, '{' . __('사용화폐') . '}', '/^(symbol|string|name|name2)$/');
        if ($v->act($this->mallInfo) === false) {
            throw new \Exception(implode("\n", $v->errors));
        };
        if (is_array($this->mallInfo['connectDomain']) && count($this->mallInfo['connectDomain']) > 3) {
            throw new Exception(__('연결도메인은 최대 3개까지만 추가 가능합니다.'));
        }
        if (count($this->mallInfo['connectDomain']) != count(array_unique($this->mallInfo['connectDomain']))) {
            throw new Exception(__('중복된 연결도메인이 존재 합니다.'));
        }
    }

    /**
     * 멀티 상점 정보 조회, 기본 값 지정
     *
     * @param string|integer $value
     * @param string         $key
     *
     * @return array|object
     */
    public function getMall($value, $key = 'sno')
    {
        $mall = $this->mallDao->selectMall($value, $key);
        $mall['connectDomain'] = json_decode($mall['connectDomain'], true);
        if (empty($mall['connectDomain'])) {
            $mall['connectDomain'] = [];
        }
        StringUtils::strIsSet($mall['useFl'], 'n');
        StringUtils::strIsSet($mall['addCurrencyFl'], 0);

        return $mall;
    }

    /**
     * 멀티 상점 전용 테이블명 반환
     *
     * @param string  $tableName
     * @param integer $mallSno
     * @param boolean $useFl
     *
     * @return string
     */
    public function getTableName($tableName, $mallSno = DEFAULT_MALL_NUMBER, $useFl = false)
    {
        return ($mallSno > DEFAULT_MALL_NUMBER || $useFl === true) ? $tableName . 'Global' : $tableName;
    }

    /**
     * 임시 도메인을 반환하는 함수
     *
     * @param $mall
     *
     * @return string
     */
    public function getTempDomain($mall)
    {
        return $this->getRequestDomain() . '/' . $mall . '/';;
    }

    /**
     * 임시 도메인(호스트와 스키마를 포함한 도메인 주소)을 반환하는 함수
     *
     * @param $mall
     *
     * @return string
     */
    public function getTempDomainUrl($mall)
    {
        if (\App::getInstance('request')->getSubdomainDirectory() == 'admin') {
            return $this->getTempDomain($mall);
        } else {
            return \App::getInstance('request')->getDomainUrl() . '/' . $mall . '/';
        }
    }

    /**
     * 연결하려는 도메인이 연결되었는지 여부를 체크하는 함수
     *
     * @param $domain
     *
     * @throws Exception
     */
    public function validateConnectDomain($domain)
    {
        if (StringUtils::strIsSet($domain, '') === '') {
            throw new Exception(__('잘못된 도메인입니다.'));
        }
        if ($this->isDefaultHostDomain($domain)) {
            throw new Exception(__('잘못된 도메인입니다.'));
        }
        if ($this->isBasicPolicyDomain($domain)) {
            throw new Exception(__('잘못된 도메인입니다.'));
        }
        if (strpos($domain, 'http://') !== false) {
            throw new Exception(__('잘못된 도메인입니다.'));
        }
        if (strpos($domain, 'https://') !== false) {
            throw new Exception(__('잘못된 도메인입니다.'));
        }
        if ($this->isConnectDomain($domain)) {
            throw new Exception(__('이미 등록된 도메인으로 등록되지 않습니다.'));
        }
        if ($this->isShopApiDomain($domain)) {
            throw new Exception(__('\'마이고도 > 도메인 연결\'에서 해당 솔루션에 연결한 도메인만 연결신청이 가능합니다.'));
        }

    }

    /**
     * @param $domain
     *
     * @return bool
     */
    public function isShopApiDomain($domain)
    {
        $returnCode = 0;
        $godoSsl = \App::load('Component\\Godo\\GodoSslServerApi');
        $domainApiList = $godoSsl->getShopDomainList();
        $domainApiList = json_decode($domainApiList, true);
        if ($domainApiList['resultCode'] == 'OK') {
            if ($domain == $domainApiList['data']['shopDomain']) {
                $returnCode++;
            }
            foreach ($domainApiList['data']['forwardDomain'] as $key => $val) {
                if ($domain == $val) {
                    $returnCode++;
                }
            }

            if ($returnCode === 0) {
                return true;
            } else {
                return false;
            }
        } else {
            \App::getInstance('logger')->info(__METHOD__, $domainApiList);

            return true;
        }
    }

    /**
     * 연결된 도메인 여부를 반환
     *
     * @param $domain
     *
     * @return bool
     */
    public function isConnectDomain($domain)
    {
        $mallData = $this->mallDao->selectConnectDomain($domain);
        $connectDomain = json_decode($mallData['connectDomain'], true);

        return in_array($domain, $connectDomain['connect']);
    }

    /**
     * 쇼핑몰 서비스 정보
     *
     * @param bool $isDisk     용량 정보 필요여부
     * @param bool $isGoodsCnt 상품 수량 정보 필요여부
     *
     * @return array
     */
    public function getServiceInfo($isDisk = false, $isGoodsCnt = false)
    {
        $data = [];

        // 서비스군
        $data['kind'] = Globals::get('gLicense.ecKind');

        // 버전
        $data['version'] = Globals::get('gLicense.version');

        // 설치일
        $data['setDate'] = DateTimeUtils::dateFormat('Y.m.d', Globals::get('gLicense.sdate'));

        // 기간
        $data['sDate'] = DateTimeUtils::dateFormat('Y.m.d', Globals::get('gLicense.sdate'));
        $data['eDate'] = DateTimeUtils::dateFormat('Y.m.d', Globals::get('gLicense.edate'));

        // 잔여일
        $data['restDays'] = Globals::get('gLicense.restDay');
        $data['disRestDays'] = sprintf('%03d', $data['restDays']);

        // 쇼핑몰이름
        $mData = gd_policy('basic.info');
        $data['mallNm'] = gd_isset($mData['mallNm']);

        // 쇼핑몰도메인
        if (empty($mData['mallDomain']) === false) {
            $data['mallDomain'] = $mData['mallDomain'];
        } else {
            //$data['mallDomain'] = Request::getHost();
            $data['mallDomain'] = '';
        }

        // 트래픽
        $data['traffic'] = __('무제한');

        // 상품수
        if ($data['kind'] == 'standard') {
            $maxGoods = Globals::get('gLicense.maxGoods');
            if ($maxGoods == 'unlimited') {
                $data['maxGoodsTxt'] = '(' . __('무제한') . ')';
            } else {
                $data['maxGoodsTxt'] = '' . strval($maxGoods) . __('개');
            }
        }

        // 전자결제
        $pgConf = gd_pgs();
        if (empty($pgConf['pgName']) === true || empty($pgConf['pgId']) === true) {
            $data['pgTxt'] = __('미사용중');
        } else {
            $data['pgTxt'] = Globals::get('gPg.' . $pgConf['pgName']);
        }

        return $data;
    }

    /**
     * 운영서비스 사용현황
     * @author sunny
     * @return array
     */
    public function getServiceState()
    {
        $data = [];

        // 고도 이차 도메인 사용여부
        $mData = gd_policy('basic.info');
        if (empty($mData['mallDomain']) === false) {
            $mallDomain = $mData['mallDomain'];
        } else {
            $mallDomain = null;
        }
        $data['domain'] = (GodoUtils::isGodomallDomain($mallDomain) === false ? true : false);
        unset($mData);

        // 아이핀 본인인증
        $data['ipin'] = gd_use_ipin();

        // 휴대폰 본인인증
        $data['auth_cellphone'] = gd_use_auth_cellphone();

        // 보안서버
        // 관리자, PC, 모바일에서 한 군데라도 유료 보안서버를 사용하면 메인 > 운영필수서비스현황 > 보안서버(SSL) 이 ON 처리됨
        // 보안서버 사용여부
        $ssl = \App::load('\\Component\\SiteLink\\SecureSocketLayer');
        $searchArr = [
            'sslConfigUse'  => 'y',
            'sslConfigType' => 'godo',
        ];
        $sslCfg = $ssl->getSsl($searchArr);

        $data['ssl'] = false;
        if (empty($sslCfg[0]['sslConfigDomain']) === false) {
            $data['ssl'] = true;
        }

        // 에이스카운터
        $data['acecounter'] = false;

        // SMS
        $smsPoint = Sms::getPoint();
        $data['sms'] = (empty($smsPoint) === false ? true : false);

        // 우체국 택배
        $data['epost'] = false;
        $epostConfig = gd_policy('order.godopost');
        if (empty($epostConfig['compdivcd']) === false) {
            $data['epost'] = true;
        }
        unset($epostConfig);

        // 파워메일
        $pmailConfig = gd_policy('mail.configPmail');
        if (isset($pmailConfig['userId']) === false || isset($pmailConfig['userNm']) === false || isset($pmailConfig['email']) === false || isset($pmailConfig['tel']) === false || isset($pmailConfig['mobile']) === false) {
            $pmailConfig['result'] = false;
        } else {
            if (empty($pmailConfig['userId']) === true) {
                $pmailConfig['result'] = false;
            } else {
                $pmailConfig['result'] = true;
            }
        }
        $data['pmail'] = $pmailConfig['result'];
        unset($pmailConfig);

        // 전자결제
        $pgConf = gd_pgs();
        $data['pg'] = (empty($pgConf['pgName']) === false && empty($pgConf['pgId']) === false ? true : false);

        // 구매안전(에스크로)
        $data['escrow'] = (gd_isset($pgConf['escrowFl']) === 'y' && empty($pgConf['escrowId']) === false ? true : false);
        unset($pgConf);

        // 휴대폰 결제 서비스 설정
        $pgConf = gd_mpgs();
        $data['mpg'] = (empty($pgConf['pgName']) === false && empty($pgConf['pgId']) === false ? true : false);
        unset($pgConf);

        // Payco
        $payco = gd_policy('pg.payco');
        $data['payco'] = (empty($payco['paycoCpId']) === false && empty($payco['paycoSellerKey']) === false ? true : false);
        unset($payco);

        // 네이버 페이
        $naverPay = gd_policy('naverPay.config');
        $data['naverPay'] = (empty($naverPay['naverId']) === false && empty($naverPay['cryptkey']) === false && $naverPay['useYn'] === 'y' ? true : false);
        unset($naverPay);

        // 무통장 자동입금
        $data['bankda'] = false;
        $bankdaConfig = gd_policy('order.bankda');
        if (empty($bankdaConfig['useFl']) === false) {
            if ($bankdaConfig['useFl'] === 'y' && $bankdaConfig['endDate'] > date('Ymd')) {
                $data['bankda'] = true;
            }
        }
        unset($bankdaConfig);

        // 고도빌
        $godobill = gd_policy('order.taxInvoice');
        $data['godobill'] = (gd_isset($godobill['eTaxInvoiceFl']) === 'y' ? true : false);
        unset($godobill);

        // 네이버 쇼핑
        $dbUrl = \App::load(\Component\Marketing\DBUrl::class);
        $naver = $dbUrl->getConfig('naver', 'config');
        $data['naver'] = (gd_isset($naver['naverFl'], 'n') === 'y' ? true : false);
        unset($naver);

        // 다음 쇼핑하우
        $daum = $dbUrl->getConfig('daumcpc', 'config');
        $data['daum'] = (gd_isset($daum['useFl'], 'n') === 'y' ? true : false);
        unset($daum);

        return $data;
    }

    /**
     * 상점 설정의 우선지역 리스트 반환
     *
     * @return array
     */
    public function getRecommendCountries()
    {
        $result = [];
        $countries = $this->mallDao->selectCountries();
        foreach ($countries as $index => $country) {
            $result[$country['code']] = $country['countryNameKor'];
        }

        return $result;
    }

    /**
     * getStatisticsMallList
     * 통계 에서만 사용되는 멀티 몰 리스트
     * 멀티 몰 사용을 안해도 멀티 상점 통계 데이터가 있으면 검색 노출 - 통계 종류중 하나의 통계에서라도 있으면 전체 통계에서 검색 노출
     *
     * @return array
     */
    public function getStatisticsMallList()
    {
        $mallList = $this->getList();
        $returnStatisticsMall = [];
        foreach ($mallList as $mallKey => $mallVal) {
            if ($mallVal['useFl'] == 'y') {
                $returnStatisticsMall[$mallKey] = $mallVal;
                continue;
            }
            $mallUsedCheck = ComponentUtils::getPolicy('mall.used', $mallVal['sno']);
            if ($mallUsedCheck['visit'] === 'y') {
                $returnStatisticsMall[$mallKey] = $mallVal;
                continue;
            }
            if ($mallUsedCheck['order'] === 'y') {
                $returnStatisticsMall[$mallKey] = $mallVal;
                continue;
            }
            if ($mallUsedCheck['goods'] === 'y') {
                $returnStatisticsMall[$mallKey] = $mallVal;
                continue;
            }
        }

        return $returnStatisticsMall;
    }

    /**
     * hasUseMallList
     *
     * @return bool
     */
    public function hasUseMallList()
    {
        return count($this->useMallList) > 0;
    }

    /**
     * @param array $useMallList
     */
    public function setUseMallList(array $useMallList)
    {
        $this->useMallList = $useMallList;
    }

    /**
     * 멀티상점 등록/수정 전 데이터 가공 함수
     */
    protected function setMallInfo()
    {
        if (is_array($this->mallInfo['connectDomain'])) {
            $this->mallInfo['connectDomain'] = json_encode(['connect' => $this->mallInfo['connectDomain']]);
        }
    }

    /**
     * 각 해외몰별 기본 스킨 다운 및 설정하기
     */
    protected function setDefaultSkin($mallInfo, $mallKey)
    {
        // 각 국가별 default 스킨 요청 체크
        if ($mallInfo[$mallKey] != STR_DIVISION . 'default' . STR_DIVISION) {
            return $mallInfo[$mallKey];
        }

        // 사용여부 체크 (사용 안함일 경우 스킨을 초기화함)
        if ($mallInfo['useFl'] !== 'y') {
            return '';
        }

        // SkinType 설정
        $arrSkinType = [
            'skinPc'     => 'front',
            'skinMobile' => 'mobile',
        ];

        // 고도 공지서버 모듈 호출
        $godoApi = new \Component\Godo\GodoGongjiServerApi();
        $getResult = $godoApi->getGodoGlobalSkinServerData($arrSkinType[$mallKey], $mallInfo['domainFl']);
        $setData = json_decode($getResult, true);

        // 결과에 의한 스킨 코드값 처리
        if ($setData['result'] === 'success') {
            $skinCode = trim($setData['msg']);
        } else {
            $skinCode = '';
        }

        return $skinCode;
    }

    /**
     * changeLiveSkin
     *
     * @param array $mallInfo
     * @param       $sno
     */
    protected function changeLiveSkin(array $mallInfo, $sno)
    {
        $designSkin = ComponentUtils::getPolicy('design.skin', $sno);
        if (empty($mallInfo['skinPc']) === true) {
            $frontWork = '';
        } else {
            $frontWork = is_dir(\UserFilePath::data('skin', 'front', $designSkin['frontWork'])) && empty($designSkin['frontWork']) === false ? $designSkin['frontWork'] : $mallInfo['skinPc'];
        }
        if (empty($mallInfo['skinMobile']) === true) {
            $mobileWork = '';
        } else {
            $mobileWork = is_dir(\UserFilePath::data('skin', 'mobile', $designSkin['mobileWork'])) && empty($designSkin['mobileWork']) === false ? $designSkin['mobileWork'] : $mallInfo['skinMobile'];
        }
        $skinInfo = [
            'sno'        => $sno,
            'frontLive'  => $mallInfo['skinPc'],
            'frontWork'  => $frontWork,
            'mobileLive' => $mallInfo['skinMobile'],
            'mobileWork' => $mobileWork,
            'domainFl'   => $mallInfo['domainFl'],
        ];
        $this->skinSave->saveSkinConfig($skinInfo, $sno);
    }

    protected function isDefaultHostDomain($domain)
    {
        return $domain == \App::getInstance('request')->getDefaultHost();
    }

    protected function isBasicPolicyDomain($domain)
    {
        $basicInfoPolicy = ComponentUtils::getPolicy('basic.info');

        return $domain == $basicInfoPolicy['mallDomain'];
    }

    protected function getRequestDomain()
    {
        return \App::getInstance('request')->getScheme() . '://' . \App::getInstance('request')->getDefaultHost();
    }

    /**
     * globalShopDomainSetting
     * 위젯 해외몰 대표 도메인 셋팅
     *
     * @param array $mallList
     * @return array
     */
    public function globalShopDomainSetting(array $mallList)
    {
        foreach ($mallList as $key => $val) {
            if (count(json_decode($val['connectDomain'], true)) > 0 && empty($val['connectDomain'] === false)) {
                $useShopDomain = gd_isset($val['useShopDomain'], 0);
                $connectDomainList = json_decode($val['connectDomain'], true);
                $mallList[$key]['domain'] = $shopDomain[$val['domainFl']] = 'http://' . $connectDomainList['connect'][$useShopDomain];
            } else {
                $tempDomains = $this->getTempDomainUrl($val['domainFl']);
                $exp = explode('/', $tempDomains);
                if ($exp[3] == 'kr') {
                    $tempDomains = str_replace('/kr', '', $this->getTempDomainUrl($val['domainFl']));
                }
                $mallList[$key]['domain'] = $shopDomain[$val['domainFl']] = $tempDomains;
            }
        }

        return $mallList;
    }

}
