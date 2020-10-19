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

namespace Bundle\Component\Myapp;

use Bundle\Component\Database\DBTableField;
use Bundle\Component\Policy\SnsLoginPolicy;
use Bundle\Component\Validator\Validator;
use Bundle\Component\Godo\GodoWonderServerApi;
use Exception;
use Framework\Http\Response;
use Framework\Utility\ComponentUtils;
use Framework\Utility\DateTimeUtils;
use Globals;
use Session;
use Framework\Utility\StringUtils;

/**
 * 마이앱 API
 *
 * @package Bundle\Component\Myapp
 * @author Hakyoung Lee <haky2@godo.co.kr>
 */
class MyappApi
{
    const APP_CONFIG_ALTERNATIVE_PUSH = 'alternative_push';
    const APP_CONFIG_APP_DEVICE = 'app_device';
    const APP_CONFIG_APP_STORE = 'app_store';
    const APP_CONFIG_APP_PUSH = 'app_push';
    const APP_CONFIG_BENEFIT = 'benefit';
    const APP_CONFIG_BUILDER_AUTH = 'builder_auth';
    const APP_CONFIG_BUILDER_AUTH_DEL = 'builder_auth_del';
    const APP_CONFIG_PROMOTE_POPUP = 'promote_popup';
    const APP_CONFIG_QUICK_LOGIN = 'quick_login';
    const APP_BUILDER_INSTALL_BENEFIT = 'install_benefit';
    const APP_BUILDER_ORDER = 'order';
    const APP_BUILDER_PUSH_ORDER = 'push_order';
    const APP_BUILDER_SPECIFIC_MEMBER = 'specific_member';
    // 데이터 조회 API 최대 개수
    const GET_DATA_SIZE_LIMIT = 500;
    // 앱 설치 권장 팝업 사용자 등록 이미지
    const APP_PROMOTE_POPUP_CUSTOM_IMAGE = 'img_install_custom';
    // 비회원 주문조회 체크
    const APP_IS_GUEST_ORDER = 'is_guest_order';
    // 솔루션 마이앱 소스 사용여부 설정
    const APP_CONFIG_MYAPP_LOGIC_USE = 'useMyapp';


    private $logger;

    private $db;

    private $request;

    private $appConfig;

    private $builderAuthDel;

    /**
     * @var string 푸시발송 API 주소
     */
    protected $appBuilderIssuedApiUrl = 'https://myappapi.godo.co.kr/myapp/authorize/builder';

    public function __construct()
    {
        $this->logger = \App::getInstance('logger')->channel('myapp');
        $this->db = \App::getInstance('DB');
        $this->request = \App::getInstance('request');
        $this->appConfig = gd_policy('myapp.config');
        $this->builderAuthDel = 'n';
    }

    /**
     * 앱 서버 설정 저장
     *
     * @param $mode
     * @throws Exception
     */
    public function setAppConfig($mode)
    {
        $getParams = $this->request->post()->toArray();
        $validationResult = $this->appConfigValidate($mode, $getParams);
        if ($validationResult['result'] === false) {
            throw new Exception(json_encode($validationResult['msg'], JSON_UNESCAPED_UNICODE), $validationResult['code']);
        }

        // 검증 후 true / false 로 변경
        switch ($mode) {
            case 'APP_CONFIG_BENEFIT':
                $getParams['installBenefit']['isUsing'] = ($getParams['installBenefit']['isUsing'] === 'y') ? true : false;
                $getParams['orderAdditionalBenefit']['isUsing'] = ($getParams['orderAdditionalBenefit']['isUsing'] === 'y') ? true : false;
                $getParams['orderAdditionalBenefit']['benefit']['useOptionalPrice'] = ($getParams['orderAdditionalBenefit']['benefit']['useOptionalPrice'] === 'y') ? true : false;
                $getParams['orderAdditionalBenefit']['benefit']['useTextOptionalPrice'] = ($getParams['orderAdditionalBenefit']['benefit']['useTextOptionalPrice'] === 'y') ? true : false;
                break;
            case 'APP_CONFIG_QUICK_LOGIN':
                $getParams['useQuickLogin'] = ($getParams['useQuickLogin'] === 'y') ? true : false;
                break;
            case 'APP_CONFIG_PROMOTE_POPUP':
                $getParams['useYn'] = ($getParams['useYn'] === 'y') ? true : false;
                break;
            case 'APP_CONFIG_MYAPP_LOGIC_USE':
                $getParams['myappLogicUse'] = ($getParams['myappLogicUse'] === 'y') ? 1 : 0;
                $getParams['useMyapp'] = $getParams['myappLogicUse'];
                unset($getParams['myappLogicUse']);
                break;
        }

        // 관리자 등록 팝업 이미지 있는 경우 다운로드
        if ($mode === self::APP_CONFIG_PROMOTE_POPUP && empty($getParams['popupImage']) === false) {
            $this->setRemoteImage($getParams['popupImage']);
        }

        // 빌더 인증정보 삭제
        if ($mode === 'builder_auth_del') {
            $this->builderAuthDel = 'y';
            $mode = 'builder_auth';
        }

        $policy[$mode] = gd_policy('myapp.config')[$mode];

        if (is_array($policy[$mode])) {
            $policy[$mode] = array_merge($policy[$mode], $getParams);

            // 빌더 인증정보 삭제
            if ($this->builderAuthDel === 'y') {
                $policy['builder_auth']['clientId'] = '';
                $policy['builder_auth']['secretKey'] = '';
            }

            ComponentUtils::setPolicy('myapp.config', $policy);
        } else {
            ComponentUtils::setPolicy('myapp.config', $getParams);
        }
    }

    /**
     * 앱 푸시 설정 저장
     *
     * @param $mode
     * @throws Exception
     */
    public function setAppPushConfig($mode)
    {
        $getParams = $this->request->post()->toArray();
        $validationResult = $this->appConfigValidate($mode, $getParams);
        if ($validationResult['result'] === false) {
            throw new Exception(json_encode($validationResult['msg'], JSON_UNESCAPED_UNICODE), $validationResult['code']);
        }
        $myapp = \App::load('Bundle\\Component\\Myapp\\Myapp');
        $deviceParams['uuid'] = $getParams['deviceUuid'];
        $deviceInfo = $myapp->getAppDeviceInfo($deviceParams);
        if (empty($deviceInfo)) {
            throw new Exception(json_encode('no regist device', JSON_UNESCAPED_UNICODE), Response::HTTP_BAD_REQUEST);
        } else {
            $getParams['pushEnabled'] = ($getParams['pushEnabled'] === 'y') ? true : false;
            $update['pushEnabled'] = $getParams['pushEnabled'];
            $arrBind = $this->db->get_binding(DBTableField::tableAppDeviceInfo(), $update, 'update', array_keys($update));
            $where = 'uuid = ?';
            $this->db->bind_param_push($arrBind['bind'], 's', $deviceParams['uuid']);
            $this->db->set_update_db(DB_APP_DEVICE_INFO, $arrBind['param'], $where, $arrBind['bind']);
        }
    }

    /**
     * 앱 디바이스 등록/수정
     *
     * @param $mode
     * @throws Exception
     */
    public function setAppDeviceInfo($mode)
    {
        $getParams = $this->request->post()->toArray();
        $validationResult = $this->appConfigValidate($mode, $getParams);
        if ($validationResult['result'] === false) {
            throw new Exception(json_encode($validationResult['msg'], JSON_UNESCAPED_UNICODE), $validationResult['code']);
        }

        $myapp = \App::load('Component\\Myapp\\Myapp');
        $setData['uuid'] = $getParams['deviceUuid'];
        $device = $myapp->getAppDeviceInfo($setData);

        $setData['memNo'] = $getParams['memberNo'];
        $setData['loggedIn'] = ($getParams['memberNo']) ? "y" : "n";

        if($getParams['platform']) $setData['platform'] = $getParams['platform'];
        if($getParams['model']) $setData['model'] = $getParams['model'];
        if($getParams['osVersion']) $setData['osVersion'] = $getParams['osVersion'];
        if($getParams['appVersion']) $setData['appVersion'] = $getParams['appVersion'];
        if($getParams['pushEnabled']) $setData['pushEnabled'] = $getParams['pushEnabled'];
        if($getParams['pushToken']) $setData['pushToken'] = ($getParams['pushEnabled'] === 'y') ? true : false;

        if (empty($device)) {
            $arrBind = $this->db->get_binding(DBTableField::tableAppDeviceInfo(), $setData, 'insert');
            $this->db->set_insert_db(DB_APP_DEVICE_INFO, $arrBind['param'], $arrBind['bind'], 'y');
        } else {
            unset($setData['uuid']);
            $arrBind = $this->db->get_binding(DBTableField::tableAppDeviceInfo(), $setData, 'update', array_keys($setData));
            $where = 'uuid = ?';
            $this->db->bind_param_push($arrBind['bind'], 's', $getParams['deviceUuid']);
            $this->db->set_update_db(DB_APP_DEVICE_INFO, $arrBind['param'], $where, $arrBind['bind']);
        }
    }


    /**
     * 쇼핑몰 정보 조회
     *
     * @return mixed
     */
    public function getMallInfo()
    {
        $mobileConfig = gd_policy('mobile.config');
        // 쇼핑몰명
        $result['shopName'] = Globals::get('gMall.mallNm');
        // 모바일샵 사용여부
        $result['useMobileShop'] = $mobileConfig['mobileShopFl'];
        // 모바일 쇼핑몰 주소
        $godoSsl = \App::load('Component\\Godo\\GodoSslServerApi');
        $domainApiList = $godoSsl->getShopDomainList();
        $domainApiList = json_decode($domainApiList, true);
        $result['mobileShopUrl'] = ($domainApiList['data']['shopDomain']) ? $this->request->getScheme() . '://m.' . $domainApiList['data']['shopDomain'] : $this->request->getScheme() . '://m.' . $domainApiList['data']['basicDomain'];
        $result['pcShopUrl'] = ($domainApiList['data']['shopDomain']) ? $this->request->getScheme() . '://' . $domainApiList['data']['shopDomain'] : $this->request->getScheme() . '://' . $domainApiList['data']['basicDomain'];
        $result['apiShopUrl'] = ($domainApiList['data']['shopDomain']) ? $this->request->getScheme() . '://api.' . $domainApiList['data']['shopDomain'] : $this->request->getScheme() . '://api.' . $domainApiList['data']['basicDomain'];
        $result['adminShopUrl'] = ($domainApiList['data']['shopDomain']) ? $this->request->getScheme() . '://gdadmin.' . $domainApiList['data']['shopDomain'] : $this->request->getScheme() . '://gdadmin.' . $domainApiList['data']['basicDomain'];

        /* 2019.03.08 API 사용안함으로 인한 주석처리 (추후 사용할지 몰라 놔둠)
         * $ssl = \App::load('Bundle\\Component\\SiteLink\\SecureSocketLayer');
        $usableDomain = DOMAIN_USEABLE_LIST;
        $defaultDomain = $this->request->getDefaultHost();
        $apiDomain = $usableDomain['api'] . '.' . $defaultDomain;
        $sslUseFl = false;
        $searchArr = [
            'sslConfigPosition' => 'api',
            'sslConfigDomain' => $apiDomain,
        ];
        $sslCfg = $ssl->getSsl($searchArr);
        if ($sslCfg['sslConfigUse'] == 'y' && strtotime($sslCfg['sslConfigStartDate']) > time() && strtotime($sslCfg['sslConfigEndDate']) < time()) {
            $sslUseFl = true;
        }
        // api 보안서버 사용 유무
        $result['useApiSSL'] = $sslUseFl;*/

        return $result;
    }

    /**
     * 쿠폰 조회
     *
     * @return mixed
     * @throws \Framework\Debug\Exception\DatabaseException
     */
    public function getCoupons()
    {
        $param = $this->request->get()->toArray();
        gd_isset($param['page'], 1);
        if ($param['size'] > self::GET_DATA_SIZE_LIMIT) {
            $param['size'] = self::GET_DATA_SIZE_LIMIT;
        }
        gd_isset($param['size'], 10);
        $strField = [
            'couponNo',
            'couponNm',
            'couponUsePeriodType',
            'couponUsePeriodStartDate',
            'couponUsePeriodEndDate',
            'couponUseDateLimit',
            'couponUsePeriodDay',
            'couponUseType',
            'couponDeviceType',
            'couponType as couponStatus',
            'regDt',
        ];
        // 쿠폰명
        if (empty($param['couponNm']) === false) {
            $arrWhere[] = "couponNm LIKE concat('%', ?, '%')";
            $this->db->bind_param_push($arrBind, 's', $param['couponNm']);
        }
        // 쿠폰유형
        if (empty($param['couponUseType']) === false) {
            $arrWhere[] = 'couponUseType = ?';
            $this->db->bind_param_push($arrBind, 's', $param['couponUseType']);
        }
        // 사용범위
        if (empty($param['couponDeviceType']) === false) {
            $arrWhere[] = 'couponDeviceType = ?';
            $this->db->bind_param_push($arrBind, 's', $param['couponDeviceType']);
        }
        // 발급상태
        if (empty($param['couponStatus']) === false) {
            $arrWhere[] = 'couponType = ?';
            $this->db->bind_param_push($arrBind, 's', $param['couponStatus']);
        }
        // 온라인 쿠폰
        $arrWhere[] = 'couponKind = ?';
        $this->db->bind_param_push($arrBind, 's', 'online');
        // 수동발급 쿠폰
        $arrWhere[] = 'couponSaveType = ?';
        $this->db->bind_param_push($arrBind, 's', 'manual');

        $this->db->strField = implode(',', $strField);
        $this->db->strWhere = implode(' AND ', $arrWhere);
        $this->db->strLimit = ($param['page'] - 1) * $param['size'] . ', ' . $param['size'];
        $query = $this->db->query_complete();
        $strSQL = 'SELECT ' . array_shift($query) . ' FROM ' . DB_COUPON . ' as c ' . implode(' ', $query);
        $couponList = $this->db->query_fetch($strSQL, $arrBind);

        // 결과 값 세팅
        if (is_array($couponList) && count($couponList) > 0) {
            // 전체 쿠폰 개수
            $result['totalCouponCount'] = $this->getTotalDataCount(DB_COUPON, '', '');
            // 검색 쿠폰 개수
            $result['searchedCouponCount'] = $this->getTotalDataCount(DB_COUPON, $query['where'], $arrBind);
            // 쿠폰 데이터 변환
            $result['couponData'] = $this->toCouponDescription($couponList);
        } else {
            $result = array('totalCouponCount' => 0, 'searchedCouponCount' => 0, 'couponData' => []);
        }
        return $result;
    }

    /**
     * 회원 조회
     *
     * @return mixed
     * @throws \Framework\Debug\Exception\DatabaseException
     */
    public function getMemberInfo()
    {
        $param = $this->request->get()->toArray();
        gd_isset($param['page'], 1);
        gd_isset($param['size'], 10);
        if ($param['size'] > self::GET_DATA_SIZE_LIMIT) {
            $param['size'] = self::GET_DATA_SIZE_LIMIT;
        }
        $strField = [
            'memNo',
            'memId',
        ];
        // 회원 아이디
        if (empty($param['memId']) === false) {
            $arrWhere[] = "memId LIKE concat('%', ?, '%')";
            $this->db->bind_param_push($arrBind, 's', $param['memId']);
        }
        // 그룹번호
        if (empty($param['groupSno']) === false) {
            $groupSnoArr = json_decode($param['groupSno'], true);
            $arrWhere[] = "(groupSno IN ('" . implode("',' ", $groupSnoArr) . "'))";
            unset($groupSnoArr);
        }
        // 승인여부
        $arrWhere[] = 'appFl = ?';
        $this->db->bind_param_push($arrBind, 's', 'y');

        $this->db->strField = implode(',', $strField);
        $this->db->strWhere = implode(' AND ', $arrWhere);
        $this->db->strLimit = ($param['page'] - 1) * $param['size'] . ', ' . $param['size'];
        $query = $this->db->query_complete();
        $strSQL = 'SELECT ' . array_shift($query) . ' FROM ' . DB_MEMBER . ' as m ' . implode(' ', $query);
        $memberList = $this->db->query_fetch($strSQL, $arrBind);

        // 결과 값 세팅
        if (is_array($memberList) && count($memberList) > 0) {
            $totalCount = $this->getTotalDataCount(DB_MEMBER, $query['where'], $arrBind);
            $result['totalMember'] = $totalCount;
            $result['totalPage'] = ceil($totalCount / $param['size']);
            $myapp = \App::load('Component\\Myapp\\Myapp');
            foreach ($memberList as $mKey => $mVal) {
                $deviceParam['memNo'] = $mVal['memNo'];
                $memberList[$mKey]['totalMemberDevice'] = count($myapp->getAppDeviceInfo($deviceParam));
                unset($deviceParam);
            }
            $result['memberData'] = $memberList;
        } else {
            $result = null;
        }

        return $result;
    }

    /**
     * 회원등급 조회
     *
     * @return mixed
     * @throws \Framework\Debug\Exception\DatabaseException
     */
    public function getMemberGradeInfo()
    {
        $strField = [
            'sno as groupSno',
            'groupNm as groupName',
        ];

        $this->db->strField = implode(',', $strField);
        $query = $this->db->query_complete();
        $strSQL = 'SELECT ' . array_shift($query) . ' FROM ' . DB_MEMBER_GROUP . ' as mg ' . implode(' ', $query);
        $gradeList = $this->db->query_fetch($strSQL, $arrBind);

        return $gradeList;
    }

    /**
     * 앱 설정 파라미터 검증
     *
     * @param $mode
     * @param array $arrData
     * @return mixed
     */
    private function appConfigValidate($mode, array &$arrData)
    {
        $validator = new Validator();
        // validator act 여부
        $actFl = false;
        $result['result'] = true;
        $validator->init();
        switch ($mode) {
            case self::APP_CONFIG_ALTERNATIVE_PUSH :
                // 푸시 대체 발송 타입 (사용안함 / 대체발송 / 함께발송)
                $validator->add('alternativeType', 'alpha', true);
                break;
            case self::APP_CONFIG_APP_STORE :
                // 애플 앱스토어 url
                $validator->add('iosAppUrl', 'url', true);
                // 구글 플레이스토어 url
                $validator->add('androidAppUrl', 'url', true);
                break;
            case self::APP_CONFIG_BENEFIT :
                $actFl = true;
                // 앱 설치 혜택 정보
                $arrData['installBenefit'] = json_decode($arrData['installBenefit'], true);
                // 앱 주문 추가 혜택
                $arrData['orderAdditionalBenefit'] = json_decode($arrData['orderAdditionalBenefit'], true);
                $this->toYn('isUsing', $arrData['installBenefit']['isUsing'], $arrData['installBenefit']);
                $this->toYn('isUsing', $arrData['orderAdditionalBenefit']['isUsing'], $arrData['orderAdditionalBenefit']);
                $this->toYn('useOptionalPrice', $arrData['orderAdditionalBenefit']['benefit']['useOptionalPrice'], $arrData['orderAdditionalBenefit']['benefit']);
                $this->toYn('useTextOptionalPrice', $arrData['orderAdditionalBenefit']['benefit']['useTextOptionalPrice'], $arrData['orderAdditionalBenefit']['benefit']);
                // 앱 설치 혜택 및 앱 주문 추가 혜택 사용여부
                $validator->add('isUsing', 'yn', true, $validator::TEXT_BOOLEAN_INVALID);
                $validIsUsingFl['installBenefit'] = $validator->actAfterIsset($arrData['installBenefit']);
                $validIsUsingFl['orderAdditionalBenefit'] = $validator->actAfterIsset($arrData['orderAdditionalBenefit']);
                if ($validIsUsingFl['installBenefit'] === false || $validIsUsingFl['orderAdditionalBenefit'] === false) {
                    $result['result'] = false;
                    break;
                }
                // 앱 설치 혜택 검증
                $validator->init();
                // 설치 혜택 타입 (Mileage / Coupon)
                $arrData['installBenefit']['benefit']['type'] = strtolower($arrData['installBenefit']['benefit']['type']);
                $validator->add('type', 'alpha', true);
                // 마일리지
                $validator->add('price', 'number');
                // 쿠폰 고유번호
                $validator->add('couponNo', 'number');
                $validBenefitFl = $validator->actAfterIsset($arrData['installBenefit']['benefit'], true);
                if ($validBenefitFl === false) {
                    $result['result'] = $validBenefitFl;
                    break;
                }
                // 앱 주문 추가 혜택 검증
                $validator->init();
                // 할인금액 기준 옵션가 사용여부
                $validator->add('useOptionalPrice', 'yn', true, $validator::TEXT_BOOLEAN_INVALID);
                // 할인금액 기준 텍스트 옵션가 사용여부
                $validator->add('useTextOptionalPrice', 'yn', true, $validator::TEXT_BOOLEAN_INVALID);
                // 할인 금액 설정
                $validator->add('discountValue', 'number');
                // 할인 기준 (원 / %)
                $arrData['orderAdditionalBenefit']['benefit']['discountType'] = strtolower($arrData['orderAdditionalBenefit']['benefit']['discountType']);
                $validator->add('discountType', 'alpha');
                $validBenefitFl = $validator->actAfterIsset($arrData['orderAdditionalBenefit']['benefit'], true);
                if ($validBenefitFl === false) {
                    $result['result'] = $validBenefitFl;
                    break;
                }
                break;
            case self::APP_CONFIG_BUILDER_AUTH :
            case self::APP_CONFIG_BUILDER_AUTH_DEL :
                // 클라이언트 아이디
                $validator->add('clientId', 'alphaNum', true);
                // 비밀키
                $validator->add('secretKey', 'alphaNum', true);
                break;
            case self::APP_CONFIG_PROMOTE_POPUP :
                // 노출여부
                $this->toYn('useYn', $arrData['useYn'], $arrData);
                $validator->add('useYn', 'yn', true, $validator::TEXT_BOOLEAN_INVALID);
                // 노출 기준 설정 (1 ~ 10)
                $validator->add('viewCount', 'number');
                // 오늘하루 보이지 않음 사용여부
                $validator->add('checkDayUse', 'yn', false, $validator::TEXT_BOOLEAN_INVALID);
                // 팝업유형
                $arrData['popupType'] = strtolower($arrData['popupType']);
                $validator->add('popupType', 'alpha');
                // 선택 팝업 정보
                $validator->add('popupInfo', '');
                // 이미지 url
                $validator->add('popupImage', '');
                break;
            case self::APP_CONFIG_QUICK_LOGIN :
                // 사용여부
                $this->toYn('useQuickLogin', $arrData['useQuickLogin'], $arrData);
                $validator->add('useQuickLogin', 'yn', true, $validator::TEXT_BOOLEAN_INVALID);
                break;
            case self::APP_CONFIG_APP_PUSH :
                // 사용여부
                $this->toYn('pushEnabled', $arrData['pushEnabled'], $arrData);
                $validator->add('deviceUuid', '', true);
                $validator->add('pushEnabled', 'yn', true, $validator::TEXT_BOOLEAN_INVALID);
                break;
            case self::APP_CONFIG_APP_DEVICE :
                $validator->add('deviceUuid', '', true);
                if(empty($arrData['memberNo']) === false) {
                    $validator->add('memberNo', 'number');
                } else {
                    $validator->add('memberNo', '');
                }

                $this->toYn('pushEnabled', $arrData['pushEnabled'], $arrData);
                if(empty($arrData['pushEnabled']) === false) {
                    $validator->add('pushEnabled', 'yn', true);
                } else {
                    $validator->add('pushEnabled', '');
                }
                $arrData['platform'] = strtolower($arrData['platform']);
                $validator->add('pushToken', '');
                $validator->add('platform', '');
                $validator->add('model', '');
                $validator->add('deviceModel', '');
                $validator->add('osVersion', '');
                $validator->add('appVersion', '');
                break;
            case self::APP_CONFIG_MYAPP_LOGIC_USE :
                $this->toYn('myappLogicUse', $arrData['myappLogicUse'], $arrData);
                $validator->add('myappLogicUse', 'yn', true);
                break;
        }

        if ($actFl === false) {
            $result['result'] = $validator->actAfterIsset($arrData, true);
        }

        if ($result['result'] === false) {
            $result['code'] = Response::HTTP_BAD_REQUEST;
            $result['msg'] = sprintf(__('%s'), implode(',', $validator->errors));
        }

        return $result;
    }

    /**
     * 앱 설치 혜택 통계 조회
     *
     * @return array|object
     * @throws \Framework\Debug\Exception\DatabaseException
     */
    public function getAppInstallBenefitStatistics()
    {
        $param = $this->request->get()->toArray();
        $validationResult = $this->appBuilderValidate(self::APP_BUILDER_INSTALL_BENEFIT, $param);
        if ($validationResult['result'] === false) {
            throw new Exception(json_encode($validationResult['msg'], JSON_UNESCAPED_UNICODE), $validationResult['code']);
        }

        // 각 앱 혜택별 합계
        $strField = [
            'FLOOR(IFNULL(SUM(ast.mileageAmountAndroid + ast.mileageAmountIos), 0)) as mileage',
            'IFNULL(SUM(ast.couponCntAndroid + ast.couponCntIos), 0) as couponCnt',
            'DATE_FORMAT(date,\'%Y%m%d\') as date',
        ];

        // 기간 검색
        $arrWhere[] = 'date >= DATE_FORMAT(?, "%Y-%m-%d")';
        $this->db->bind_param_push($arrBind, 's', $param['treatStartDate']);
        $arrWhere[] = 'date <= DATE_FORMAT(?, "%Y-%m-%d")';
        $this->db->bind_param_push($arrBind, 's', $param['treatEndDate']);

        $this->db->strField = implode(',', $strField);
        $this->db->strWhere = implode(' AND ', $arrWhere);
        $query = $this->db->query_complete();
        $strSQL = 'SELECT ' . array_shift($query) . ' FROM ' . DB_APP_STATISTICS . ' as ast ' . implode(' ', $query);
        $installBenefit = $this->db->query_fetch($strSQL, $arrBind, false);

        $result[$installBenefit['date']] = $installBenefit;

        return $result;
    }

    /**
     * 앱 빌더 파라미터 검증
     *
     * @param $mode
     * @param array $arrData
     * @return mixed
     */
    private function appBuilderValidate($mode, array &$arrData)
    {
        $validator = new Validator();
        $validator->init();
        $garbageDeleteFl = true;
        switch ($mode) {
            case self::APP_BUILDER_INSTALL_BENEFIT :
                // 기간검색 (최대 30일)
                if (empty($arrData['treatStartDate']) === false && empty($arrData['treatEndDate']) === false) {
                    $limitDate = date('Y-m-d', strtotime($arrData['treatStartDate'] . ' + 30 days'));
                    if (DateTimeUtils::intervalDay($limitDate, $arrData['treatEndDate']) > 0) {
                        $arrData['treatEndDate'] = $limitDate;
                    }
                }
                $validator->add('treatStartDate', 'date', true);
                $validator->add('treatEndDate', 'date', true);
                break;
            case self::APP_BUILDER_ORDER :
                // 기간검색 (최대 30일)
                if (empty($arrData['treatStartDate']) === false && empty($arrData['treatEndDate']) === false) {
                    $limitDate = date('Y-m-d', strtotime($arrData['treatStartDate'] . ' + 30 days'));
                    if (DateTimeUtils::intervalDay($limitDate, $arrData['treatEndDate']) > 0) {
                        $arrData['treatEndDate'] = $limitDate;
                    }
                }
                $validator->add('treatStartDate', 'date', true);
                $validator->add('treatEndDate', 'date', true);
                break;
            case self::APP_BUILDER_PUSH_ORDER :
                $validator->add('pushCode', '', true);
                break;
            case self::APP_BUILDER_SPECIFIC_MEMBER :
                // 회원검색 타입
                $validator->add('memberType', '', true);
                if ($arrData['memberType'] == 'cart_in') {
                    if (empty($arrData['cartDate']) === false || empty($arrData['cartStock']) === false) {
                        // 기간 직접입력
                        if ($arrData['cartDate'] == 'input') {
                            $validator->add('cartStartDate', 'date', true);
                            $validator->add('cartEndDate', 'date', true);
                        }
                        // 수량
                        if ($arrData['cartStock'] > 0) {
                            $validator->add('cartUnit', 'alpha', true);
                        }
                    } else {
                        $validator->add(null, '', true, 'empty parameters');
                    }
                } elseif ($arrData['memberType'] == 'longterm_login') {
                    // 장기 미로그인 기준
                    $validator->add('sleepDate', '', true);
                } elseif ($arrData['memberType'] == 'order_history') {
                    if (empty($arrData['buyerMemberDateType']) === false || empty($arrData['buyMinPrice']) === false || empty($arrData['buyMaxPrice']) === false) {
                        // 구매내역 기준일
                        if (empty($arrData['buyerMemberDateType']) === false) {
                            $validator->add('buyDay', '', true);
                        }
                    } else {
                        $validator->add(null, '', true, 'empty parameters');
                    }
                }
                $garbageDeleteFl = false;
                break;
            case self::APP_IS_GUEST_ORDER :
                $validator->add('orderNo', '', true);
                $validator->add('orderNm', '', true);
                break;
        }

        $result['result'] = $validator->actAfterisset($arrData, $garbageDeleteFl);

        if ($result['result'] === false) {
            $result['code'] = Response::HTTP_BAD_REQUEST;
            $result['msg'] = sprintf(__('%s'), implode(',', $validator->errors));
        }

        return $result;
    }

    /**
     * true/false 를 y/n으로 변환
     *
     * @param $key
     * @param $value
     * @param $arrData
     */
    private function toYn($key, $value, &$arrData)
    {
        $result = null;
        if ($value === true || $value == 'true') {
            $result = 'y';
        } elseif ($value === false || $value == 'false') {
            $result = 'n';
        } elseif (strtolower($value) == 'y' || strtolower($value) == 'n') {
            $result = 'fail';
        }
        if (empty($result) === false) {
            $arrData[$key] = $result;
        }
    }

    /**
     * 쿠폰값을 문자열(값 설명)로 변환
     *
     * @param array $getValue
     * @return mixed
     */
    private function toCouponDescription(array $getValue)
    {
        $couponList = null;
        foreach ($getValue as $key => $val) {
            $couponList[$key]['couponNo'] = $val['couponNo'];
            $couponList[$key]['couponNm'] = $val['couponNm'];
            $couponList[$key]['regDt'] = $val['regDt'];
            if ($val['couponStatus'] == 'y') {
                $couponList[$key]['couponStatus'] = '발급중';
            } else if ($val['couponStatus'] == 'n') {
                $couponList[$key]['couponStatus'] = '일시중지';
            }
            if ($val['couponUseType'] == 'product') {
                $couponList[$key]['couponUseType'] = '상품적용쿠폰';
            } else if ($val['couponUseType'] == 'order') {
                $couponList[$key]['couponUseType'] = '주문적용쿠폰';
            } else if ($val['couponUseType'] == 'delivery') {
                $couponList[$key]['couponUseType'] = '배송비적용쿠폰';
            }
            if ($val['couponUsePeriodType'] == 'period') {
                $couponList[$key]['couponDate'] = date('Y-m-d H:i', strtotime($val['couponUsePeriodStartDate'])) . '~' . date('Y-m-d H:i', strtotime($val['couponUsePeriodEndDate']));
            } else if ($val['couponUsePeriodType'] == 'day') {
                $couponEndDate = '';
                if (strtotime($val['couponUseDateLimit']) > 0) {
                    $couponEndDate = '. 사용가능일 : ' . $val['couponUseDateLimit'];
                }
                $couponList[$key]['couponDate'] = '발급일로부터 ' . $val['couponUsePeriodDay'] . '일까지' . $couponEndDate;
            }
            if ($val['couponDeviceType'] == 'all') {
                $couponList[$key]['couponDeviceType'] = 'PC+모바일';
            } else if ($val['couponDeviceType'] == 'pc') {
                $couponList[$key]['couponDeviceType'] = 'PC';
            } else if ($val['couponDeviceType'] == 'mobile') {
                $couponList[$key]['couponDeviceType'] = '모바일';
            }
        }

        return $couponList;
    }

    /**
     * 해당 쿼리조건에 해당하는 총 갯수 조회
     *
     * @param $table
     * @param $where
     * @param $bind
     * @return mixed
     * @throws \Framework\Debug\Exception\DatabaseException
     */
    private function getTotalDataCount($table, $where, $bind)
    {
        $countSQL = 'SELECT count(1) as cnt FROM ' . $table . $where;
        $totalCount = $this->db->query_fetch($countSQL, $bind, false);
        return $totalCount['cnt'];
    }

    /**
     * 원격 이미지 저장
     *
     * @param $imageUrl
     * @param null $imageName
     */
    private function setRemoteImage($imageUrl, $imageName = null)
    {
        $downloadPath = '.' . \UserFilePath::data('commonimg', 'myapp')->www() . DS;
        if (empty($imageName)) {
            $imageName = self::APP_PROMOTE_POPUP_CUSTOM_IMAGE;
        }
        $context = stream_context_create(array('http'=> array(
            'timeout' => 5
        )));
        $result = file_put_contents($downloadPath . $imageName, file_get_contents($imageUrl, false, $context));
        $this->logger->info(sprintf('set remote image result : %s', $result));
    }

    /**
     * 회원 로그인 관련 정보 조회
     *
     * @return array
     */
    public function getMemberLoginInfo()
    {
        $param = $this->request->get()->toArray();
        $result = [];
        // 인트로 설정
        $memberAccess = gd_policy('member.access');

        // 로그인 폼 타입 (adult=성인상품) > 성인전용 인트로 설정과 동일한 결과 리턴
        if (empty($param['formType']) === false && $param['formType'] == 'adult') {
            $memberAccess['introMobileUseFl'] = 'y';
            $memberAccess['introMobileAccess'] = 'adult';
        }

        if ($memberAccess['introMobileUseFl'] == 'n' || ($memberAccess['introMobileUseFl'] == 'y' && $memberAccess['introMobileAccess'] == 'free')) {
            $result['intro']['introType'] = 'none';
        } elseif ($memberAccess['introMobileUseFl'] == 'y' && $memberAccess['introMobileAccess'] == 'adult') {
            $result['intro']['introType'] = 'adult';
        } elseif ($memberAccess['introMobileUseFl'] == 'y' && $memberAccess['introMobileAccess'] == 'member') {
            $result['intro']['introType'] = 'member';
        } elseif ($memberAccess['introMobileUseFl'] == 'y' && $memberAccess['introMobileAccess'] == 'walkout') {
            $result['intro']['introType'] = '';
        }

        // 인트로 이미지 URL
        if ($result['intro']['introType'] == 'adult') {
            $result['intro']['introImage'] = substr(URI_MOBILE, 0, -1) . PATH_MOBILE_SKIN . 'img/intro/adult02.png';
        } else if ($result['intro']['introType'] == 'member') {
            $result['intro']['introImage'] = substr(URI_MOBILE, 0, -1) . PATH_MOBILE_SKIN . 'img/intro/member_only02.png';
        } else {
            $result['intro']['introImage'] = '';
        }

        // 로그인 url
        $result['login']['url'] = URI_MOBILE . 'member/login_ps.php';
        // 로그인 아이디 파라미터 (moment 기준)
        $result['login']['loginID'] = 'loginId';
        // 로그인 비밀번호 파라미터 (moment 기준)
        $result['login']['loginPW'] = 'loginPwd';
        // 로그인 상태유지 파라미터 (moment 기준)
        $result['login']['autoLogin']['autoLogin'] = 'saveAutoLogin';
        // 로그인 상태유지 사용시 전송될 데이터
        $result['login']['autoLogin']['use'] = 'y';
        // 로그인 상태유지 미사용시 전송될 데이터
        $result['login']['autoLogin']['unused'] = 'n';

        // 회원가입 url
        $result['member']['join'] = URI_MOBILE . 'member/join_agreement.php';
        // 아이디 찾기 url
        $result['member']['find_id'] = URI_MOBILE . 'member/find_id.php';
        // 비밀번호 찾기 url
        $result['member']['find_password'] = URI_MOBILE . 'member/find_password.php';

        // 로그인 영역
        if ($this->request->request()->has('returnUrl')) {
            $returnUrl = $this->request->getReturnUrl();
        } else {
            $returnUrl = '{referer}';
        }

        // 비회원 주문하기 url
        $result['nonmember']['nonmemberOrder'] = URI_MOBILE . 'order/order.php';
        // 비회원 주문조회 url
        $result['nonmember']['nonmemberSearchOrder'] = URI_MOBILE . 'member/member_ps.php?mode=guestOrder';
        // 프론트 주문상태 페이지 URL
        $result['nonmember']['nonmemberSearchOrderRedirect'] = URI_HOME . 'mypage/order_view.php';
        // 비회원 주문조회 주문자명 파라미터명 (moment 기준)
        $result['nonmember']['orderName'] = 'orderNm';
        // 비회원 주문조회 주문번호 파라미터명 (moment 기준)
        $result['nonmember']['orderNo'] = 'orderNo';

        // 페이코 로그인 url
        $paycoLoginPolicy = gd_policy(\Component\Policy\PaycoLoginPolicy::KEY);
        if ($paycoLoginPolicy['useFl'] == 'y') {
            $paycoType = '';
            $paycoLogin['key'] = 'payco';
            $paycoLogin['url'] = URI_MOBILE . 'member/payco/payco_login.php?paycoType=' . $paycoType . 'returnUrl=' . $returnUrl;
            $result['simpleLogin'][] = $paycoLogin;
        }
        // 페이스북 로그인 url
        $facebookLoginPolicy = \Component\Policy\SnsLoginPolicy::getInstance();
        $facebookLoginUseFl = $facebookLoginPolicy->getSnsLoginUse()['facebook'];
        if ($facebookLoginUseFl == 'y') {
            $snsLoginPolicy = new SnsLoginPolicy();
            $facebook = \App::load('Bundle\\Component\\Facebook\\Facebook');
            $useFacebook = $snsLoginPolicy->useFacebook();
            if ($useFacebook) {
                $facebookLogin['key'] = 'facebook';
                if ($snsLoginPolicy->useGodoAppId()) {
                    $facebookLogin['url'] = $facebook->getGodoLoginUrl($returnUrl);
                } else {
                    $facebookLogin['url'] = $facebook->getLoginUrl($returnUrl);
                }
                $result['simpleLogin'][] = $facebookLogin;
            }
        }
        // 네이버 로그인 url
        $naverLoginPolicy = gd_policy(\Component\Policy\NaverLoginPolicy::KEY);
        if ($naverLoginPolicy['useFl'] == 'y') {
            $naverType = '';
            $naverLogin['key'] = 'naver';
            $naverLogin['url'] = URI_MOBILE . 'member/naver/naver_login.php?naverType=' . $naverType . 'returnUrl=' . $returnUrl;
            $result['simpleLogin'][] = $naverLogin;
        }
        // 원더 로그인 url
        $wonderPolicy = gd_policy(\Component\Policy\WonderLoginPolicy::KEY);
        if ($wonderPolicy['useFl'] === 'y') {
            $wonder = new GodoWonderServerApi();
            $wonderLogin['key'] = 'wonder';
            $wonderLogin['url'] = $wonder->getAuthUrl('login');
            $result['simpleLogin'][] = $wonderLogin;
        }


        // 아이핀 인증 url
        $ipinConfig = gd_policy('member.ipin');
        if ($ipinConfig['useFl'] == 'y') {
            $ipinAuth['key'] = 'ipinCertify';
            $ipinAuth['url'] = URI_MOBILE . 'member/ipin/ipin_main.php?callType=certAdult&returnUrl=' . $returnUrl;
            $result['certify'][] = $ipinAuth;
        }
        // 휴대폰 인증 url
        $authCellPhoneConfig = gd_get_auth_cellphone_info();
        if ($authCellPhoneConfig['useFl'] == 'y') {
            $cellPhoneAuth['key'] = 'phoneCertify';
            $callbackUrl = URI_MOBILE . 'member/authcellphone/dreamsecurity_result.php';
            $cellPhoneAuth['url'] = $this->request->getScheme() . '://hpauthdream.godo.co.kr/module/NEW_hpauthDream_Main.php?callType=certAdult&shopUrl=' . $callbackUrl . '&cpid=' . $authCellPhoneConfig['cpCode'];
            $result['certify'][] = $cellPhoneAuth;
        }

        return $result;
    }

    /**
     * 앱 주문 통계 조회
     *
     * @return array|object
     * @throws \Framework\Debug\Exception\DatabaseException
     */
    public function getAppOrderStatistics()
    {
        $param = $this->request->get()->toArray();
        $validationResult = $this->appBuilderValidate(self::APP_BUILDER_ORDER, $param);
        if ($validationResult['result'] === false) {
            throw new Exception(json_encode($validationResult['msg'], JSON_UNESCAPED_UNICODE), $validationResult['code']);
        }

        $strField = [
            'IFNULL(SUM(ast.orderCntAndroid), 0) as orderCntAndroid',
            'IFNULL(SUM(ast.orderCntIos), 0) as orderCntIos',
            'IFNULL(SUM(ast.orderAmountAndroid), 0) as orderAmountAndroid',
            'IFNULL(SUM(ast.orderAmountIos), 0) as orderAmountIos',
            'IFNULL(SUM(ast.settlePriceAndroid), 0) as settlePriceAndroid',
            'IFNULL(SUM(ast.settlePriceIos), 0) as settlePriceIos',
            'DATE_FORMAT(date,\'%Y%m%d\') as date',
        ];

        // 기간 검색
        $arrWhere[] = 'date >= DATE_FORMAT(?, "%Y-%m-%d")';
        $this->db->bind_param_push($arrBind, 's', $param['treatStartDate']);
        $arrWhere[] = 'date <= DATE_FORMAT(?, "%Y-%m-%d")';
        $this->db->bind_param_push($arrBind, 's', $param['treatEndDate']);
        $table = DB_APP_STATISTICS;

        $this->db->strField = implode(',', $strField);
        $this->db->strWhere = implode(' AND ', $arrWhere);
        $query = $this->db->query_complete();
        $strSQL = 'SELECT ' . array_shift($query) . ' FROM ' . $table . ' as ast ' . implode(' ', $query);
        $orderStatistics = $this->db->query_fetch($strSQL, $arrBind, false);

        $result[$orderStatistics['date']] = $orderStatistics;

        return $result;
    }

    /**
     * 앱 푸시 주문 통계 조회
     *
     * @return array|object
     * @throws \Framework\Debug\Exception\DatabaseException
     */
    public function getAppPushOrderStatistics()
    {
        $param = $this->request->get()->toArray();
        $validationResult = $this->appBuilderValidate(self::APP_BUILDER_PUSH_ORDER, $param);
        if ($validationResult['result'] === false) {
            throw new Exception(json_encode($validationResult['msg'], JSON_UNESCAPED_UNICODE), $validationResult['code']);
        }

        $strField = [
            'ast.pushCode',
            'IFNULL(SUM(ast.orderCntAndroid), 0) as orderCntAndroid',
            'IFNULL(SUM(ast.orderCntIos), 0) as orderCntIos',
            'IFNULL(SUM(ast.orderAmountAndroid), 0) as orderAmountAndroid',
            'IFNULL(SUM(ast.orderAmountIos), 0) as orderAmountIos',
            'IFNULL(SUM(ast.settlePriceAndroid), 0) as settlePriceAndroid',
            'IFNULL(SUM(ast.settlePriceIos), 0) as settlePriceIos',
        ];

        // 푸시 토큰
        $pushCodeArr = json_decode($param['pushCode']);
        foreach ($pushCodeArr as $pushCode) {
            $arrWhere[] = 'pushCode = ?';
            $this->db->bind_param_push($arrBind, 's', $pushCode);
        }
        $table = DB_APP_PUSH_STATISTICS;

        $this->db->strField = implode(',', $strField);
        $this->db->strWhere = implode(' AND ', $arrWhere);
        $query = $this->db->query_complete();
        $strSQL = 'SELECT ' . array_shift($query) . ' FROM ' . $table . ' as ast ' . implode(' ', $query);
        $orderStatistics = $this->db->query_fetch($strSQL, $arrBind, false);

        return $orderStatistics;
    }

    /**
     * 조건별 회원 조회
     *
     * @return bool|null
     * @throws \Framework\Debug\Exception\DatabaseException
     */
    public function getSpecificMemberInfo()
    {
        $param = $this->request->get()->toArray();
        $validationResult = $this->appBuilderValidate(self::APP_BUILDER_SPECIFIC_MEMBER, $param);
        if ($validationResult['result'] === false) {
            throw new Exception(json_encode($validationResult['msg'], JSON_UNESCAPED_UNICODE), $validationResult['code']);
        }

        $table = null;
        $arrWhere = $arrJoin = [];

        switch ($param['memberType']) {
            // 장바구니에 상품을 담은 회원
            case 'cart_in':
                $strField = [
                    'c.memNo'
                ];
                $table = DB_CART . ' as c';
                $arrJoin[] = ' INNER JOIN ' . DB_APP_DEVICE_INFO . ' as adi ON adi.memNo = c.memNo';
                $arrJoin[] = ' LEFT JOIN ' . DB_GOODS . ' as g ON c.goodsNo = g.goodsNo';
                // 상품 담은 기간
                if (empty($param['cartDate']) === false) {
                    if ($param['cartDate'] == 'input') {
                        $cartStartDate = $param['cartStartDate'] . ' 00:00:00';
                        $cartEndDate = $param['cartEndDate'] . ' 23:59:59';
                    } else {
                        $cartDate = explode('to', $param['cartDate']);
                        $cartStartDate = date('Y-m-d 00:00:00', strtotime('-' . $cartDate[1] . ' day', strtotime(date('Y-m-d'))));
                        $cartEndDate = date('Y-m-d 23:59:59', strtotime('-' . $cartDate[0] . ' day', strtotime(date('Y-m-d'))));
                    }
                    $arrWhere[] = 'c.regDt >= ?';
                    $this->db->bind_param_push($arrBind, 's', $cartStartDate);
                    $arrWhere[] = 'c.regDt <= ?';
                    $this->db->bind_param_push($arrBind, 's', $cartEndDate);
                }
                // 상품 재고량 (무한정 판매 포함)
                if (empty($param['cartStock']) === false && empty($param['cartUnit']) === false) {
                    if ($param['cartUnit'] == 'more') {
                        $operator = '>=';
                    } else {
                        $operator = '<=';
                    }
                    $arrWhere[] = "g.stockFl = 'n' OR (g.soldOutFl = 'n' AND g.totalStock " . $operator . " ?)";
                    $this->db->bind_param_push($arrBind, 'i', $param['cartStock']);
                }
                if (count($arrWhere) > 0) {
                    $arrWhere[] = 'c.memNo > 0';
                    $this->db->strGroup = 'c.memNo';
                }
                break;
            // 장기 미로그인한 회원
            case 'longterm_login':
                $strField = [
                    'm.memNo',
                    'm.memId',
                ];
                $table = DB_MEMBER . ' as m';
                // 기준일
                if (empty($param['sleepDate']) === false) {
                    $arrWhere[] = 'lastLoginDt <= ?';
                    $this->db->bind_param_push($arrBind, 's', date('Y-m-d 00:00:00', strtotime('-' . $param['sleepDate'] . ' day', strtotime(date('Y-m-d')))));
                }
                break;
            // 구매내역이 있는 회원
            case 'order_history':
                $strField = [
                    'm.memNo',
                    'm.memId',
                ];
                $table = DB_MEMBER . ' as m';
                $arrJoin[] = ' INNER JOIN ' . DB_APP_DEVICE_INFO . ' as adi ON adi.memNo = m.memNo';
                $arrJoin[] = ' LEFT JOIN ' . DB_ORDER . ' as o ON o.memNo = m.memNo';
                $arrJoin[] = ' LEFT JOIN ' . DB_ORDER_GOODS . ' as og ON og.orderNo = o.orderNo';
                // 기준일
                if (empty($param['buyerMemberDateType']) === false && empty($param['buyDay']) === false) {
                    if ($param['buyerMemberDateType'] == 'paymentDt') {
                        $arrWhere[] = 'o.' . $param['buyerMemberDateType'] . ' >= ?';
                    } else {
                        $arrWhere[] = 'og.' . $param['buyerMemberDateType'] . ' >= ?';
                    }
                    $this->db->bind_param_push($arrBind, 's', date('Y-m-d 00:00:00', strtotime('-' . $param['buyDay'] . ' day', strtotime(date('Y-m-d')))));
                }
                // 결제 최소 금액
                if (empty($param['buyMinPrice']) === false) {
                    $arrWhere[] = 'o.settlePrice >= ?';
                    $this->db->bind_param_push($arrBind, 'd', $param['buyMinPrice']);
                }
                // 결제 최대 금액
                if (empty($param['buyMaxPrice']) === false) {
                    $arrWhere[] = 'o.settlePrice <= ?';
                    $this->db->bind_param_push($arrBind, 'd', $param['buyMaxPrice']);
                }
                if (count($arrWhere) > 0) {
                    $orderAdmin = \App::load('Bundle\\Component\\Order\\OrderAdmin');
                    foreach ($orderAdmin->statusReceiptApprovalPossible as $item) {
                        $tmpWhere[] = 'SUBSTR(o.orderStatus, 1, 1) = ?';
                        $this->db->bind_param_push($arrBind, 's', $item);
                    }
                    $arrWhere[] = '(' . implode(' OR ', $tmpWhere) . ')';
                    $arrWhere[] = 'o.memNo > 0';
                    $this->db->strGroup = 'm.memNo';
                }
                break;
        }

        if (empty($table) || count($arrWhere) < 1) {
            return false;
        }

        $this->db->strField = implode(',', $strField);
        $this->db->strJoin = implode('', $arrJoin);
        $this->db->strWhere = implode(' AND ', $arrWhere);
        $query = $this->db->query_complete();
        $strSQL = 'SELECT ' . array_shift($query) . ' FROM ' . $table . implode(' ', $query);
        $memberList = $this->db->query_fetch($strSQL, $arrBind);

        // 결과 값 세팅
        if (is_array($memberList) && count($memberList) > 0) {
            $result['totalMember'] = count($memberList);
            $myapp = \App::load('Component\\Myapp\\Myapp');
            $member = \App::load('Component\\Member\\Member');
            foreach ($memberList as $mKey => $mVal) {
                $deviceParam['memNo'] = $mVal['memNo'];
                $memInfo = $member->getMemberId($mVal['memNo']);
                $memberList[$mKey]['totalMemberDevice'] = count($myapp->getAppDeviceInfo($deviceParam));
                $memberList[$mKey]['memId'] = ($mVal['memId']) ? $mVal['memId'] : $memInfo['memId'];
                unset($deviceParam);
                unset($memInfo);
            }
            $result['memberData'] = $memberList;
        } else {
            $result = null;
        }

        return $result;
    }

    /**
     * 빌더 페이지 인증코드 발급
     *
     * @param string $myappId
     *
     * @return array
     */
    public function appBuilderIssued($myappId = null)
    {
        $managerId = Session::get('manager.managerId');
        $managerId = StringUtils::mask($managerId, 3, strlen($managerId));
        $clientId = gd_policy('myapp.config')['builder_auth']['clientId'];

        $request = 'myappId='.$clientId.'&managerId='.$managerId;

        $ch = curl_init($this->appBuilderIssuedApiUrl);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HEADER, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-type: application/x-www-form-urlencoded'));
        curl_setopt($ch, CURLOPT_POSTFIELDS, $request);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, false);
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 15);
        $res = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $header = substr($res,0,curl_getinfo($ch,CURLINFO_HEADER_SIZE));
        $result['httpCode'] = $httpCode;
        curl_close($ch);

        foreach (explode("\r\n", $header) as $i => $line) {
            if ($i === 0)
                $headers['http_code'] = $line;
            else
            {
                list ($key, $value) = explode(': ', $line);

                $headers[$key] = $value;
            }
        }

        $this->logger->info('빌더 페이지 인증코드 발급 통신 결과', [$headers]);

        return $headers['X-GDMYAPP-BUILDERCODE'];
    }

    /**
     * 회원 로그인 유효성 체크
     *
     * @throws Exception
     */
    public function MemberLoginAvailable() {
        $member = \App::load('\\Component\\Member\\Member');
        $param = $this->request->post()->toArray();
        $memId = $param['loginId'];
        $memPw = $param['loginPwd'];
        $member->login($memId, $memPw);
    }

    /**
     * 비회원 주문조회 유효성 체크
     *
     * @throws Exception
     */
    public function GuestOrderAvailable() {
        $param = $this->request->post()->toArray();
        $validationResult = $this->appBuilderValidate(self::APP_IS_GUEST_ORDER, $param);
        if ($validationResult['result'] === false) {
            throw new Exception(json_encode($validationResult['msg'], JSON_UNESCAPED_UNICODE), $validationResult['code']);
        }

        $order = \App::load('\\Component\\Order\\Order');
        $orderNo = $param['orderNo'];
        $orderNm = $param['orderNm'];
        $aResult = $order->isGuestOrder($orderNo, $orderNm);

        if ($aResult['result']) {
            return true;
        } else {
            return false;
        }
    }
}