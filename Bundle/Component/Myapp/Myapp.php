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

use Bundle\Component\Code\Code;
use Bundle\Component\Database\DBTableField;
use Bundle\Component\Member\Member;
use Bundle\Component\Member\Util\MemberUtil;
use Bundle\Component\Mileage\Mileage;
use Framework\Utility\ArrayUtils;
use Globals;

/**
 * 마이앱
 *
 * @package Bundle\Component\Myapp
 * @author Hakyoung Lee <haky2@godo.co.kr>
 */
class Myapp
{
    private $logger;

    private $db;

    private $request;

    private $appConfig;

    /**
     * @var string 푸시발송 API 주소
     */
    protected $appApiUrl = 'http://push.devops.godo.co.kr/vendor.app/message';

    public function __construct()
    {
        $this->logger = \App::getInstance('logger')->channel('myapp');
        $this->db = \App::getInstance('DB');
        $this->request = \App::getInstance('request');
        $this->appConfig = gd_policy('myapp.config');
        $this->session = \App::getInstance('session');
    }

    /**
     * 앱 기기 정보 조회
     *
     * @param $params
     * @return array|bool|object
     * @throws \Framework\Debug\Exception\DatabaseException
     */
    public function getAppDeviceInfo($params)
    {
        // 회원번호
        if (empty($params['memNo']) === false) {
            $arrWhere[] = 'memNo = ?';
            $this->db->bind_param_push($arrBind, 'i', $params['memNo']);
        }

        if (empty($params['uuid']) === false) {
            $arrWhere[] = 'uuid = ?';
            $this->db->bind_param_push($arrBind, 's', $params['uuid']);
        }

        if (empty($arrWhere)) {
            return false;
        }

        $this->db->strField = '*';
        $this->db->strWhere = implode(' AND ', $arrWhere);
        $this->db->strOrder = 'sno DESC';
        $query = $this->db->query_complete();
        $strSQL = 'SELECT ' . array_shift($query) . ' FROM ' . DB_APP_DEVICE_INFO . ' as adi ' . implode(' ', $query);
        $deviceList = $this->db->query_fetch($strSQL, $arrBind);

        return $deviceList;
    }

    /**
     * 앱 설치 혜택 지급
     *
     * @param array $member
     * @return bool
     * @throws \Framework\Debug\Exception\DatabaseException
     */
    public function setAppInstallBenefit(array $member)
    {
        $result = false;

        // 지급 여부 확인
        if (empty($this->getAppInstallBenefitInfo($member['memNo'])) === false) {
            return $result;
        }

        $deviceInfo = $this->getAppDeviceInfo($member)[0];

        // 등록 기기 확인
        if (empty($deviceInfo)) {
            return $result;
        }

        // 앱 설치 혜택 지급
        if ($this->appConfig['benefit']['installBenefit']['isUsing'] == true) {
            $benefitData['memNo'] = $member['memNo'];
            $benefitData['uuid'] = $deviceInfo['uuid'];
            // 마일리지 지급
            if ($this->appConfig['benefit']['installBenefit']['benefit']['type'] == 'mileage') {
                $mileagePolicy = gd_mileage_give_info();
                if ($mileagePolicy['give']['giveFl'] == 'y') {
                    $benefitAmount = $this->appConfig['benefit']['installBenefit']['benefit']['price'];
                    $benefitMileage = gd_number_figure($benefitAmount, $mileagePolicy['trunc']['unitPrecision'], $mileagePolicy['trunc']['unitRound']);
                    $benefitData['amount'] = $benefitMileage;
                    // 발급 마일리지 액수 통계 데이터
                    if ($this->setAppMileage($benefitData)) {
                        $result = true;
                        $field = $this->getAppStatisticsField('mileage', $deviceInfo['platform']);
                        $setData[$field] = $benefitMileage;
                    }
                }
            } else {
                // 쿠폰 지급
                if (gd_use_coupon()) {
                    $benefitCouponNo = $this->appConfig['benefit']['installBenefit']['benefit']['couponNo'];
                    // 발급된 쿠폰이 없는 경우만 쿠폰 발행
                    $coupon = \App::load('Bundle\\Component\\Coupon\\Coupon');
                    if ($coupon->getMemberCouponTotalCount($benefitCouponNo, $member['memNo']) > 0) {
                        return $result;
                    }
                    unset($coupon);
                    $benefitData['couponNo'] = $benefitCouponNo;
                    $benefitData['couponNo'] = $this->setAppCoupon($benefitData);
                    // 발급 쿠폰 갯수 통계 데이터
                    if ($benefitData['couponNo'] !== false) {
                        $result = true;
                        $field = $this->getAppStatisticsField('coupon', $deviceInfo['platform']);
                        $setData[$field] = 1;
                    }
                }
            }
        }

        // 앱 설치 혜택 지급 내역 및 앱 통계 저장
        if ($result) {
            $this->logger->info(sprintf('success to give benefit(%s)', $this->appConfig['benefit']['installBenefit']['benefit']['type']), $benefitData);
            $this->setAppInstallBenefitInfo($benefitData);
            $this->setAppStatistics($setData);
        }

        return $result;
    }

    /**
     * 마일리지 지급
     *
     * @param array $mileageInfo
     * @return bool
     */
    private function setAppMileage(array $mileageInfo)
    {
        $result = false;
        if (empty($mileageInfo['memNo']) || empty($mileageInfo['amount'])) {
            return $result;
        }

        $mileage = \App::load('Bundle\\Component\\Mileage\\Mileage');
        $mileage->setIsTran(false);
        $code = $mileage::REASON_CODE_GROUP . $mileage::REASON_CODE_MILEAGE_MOBILE_APP;
        $handleCd = null;
        // uuid를 처리코드로 이용
        if (empty($mileageInfo['uuid']) === false) {
            $handleCd = substr($mileageInfo['uuid'], -20);
        }
        $result = $mileage->setMemberMileage($mileageInfo['memNo'], $mileageInfo['amount'], $code, 'm', $handleCd);
        unset($mileage);

        return $result;
    }

    /**
     * 쿠폰 지급 (수동쿠폰 SMS 미발송)
     *
     * @param array $couponInfo
     * @return mixed
     * @throws \Framework\Debug\Exception\DatabaseException
     */
    private function setAppCoupon(array $couponInfo)
    {
        $result = false;
        if (empty($couponInfo['memNo']) || empty($couponInfo['couponNo'])) {
            return $result;
        }

        $couponAdmin = \App::load('Bundle\\Component\\Coupon\\CouponAdmin');
        // 마일리지 코드와 동일 사유
        $mileageReasons = Code::getGroupItems(Mileage::REASON_CODE_GROUP);
        $mileageContents = $mileageReasons[Mileage::REASON_CODE_GROUP . Mileage::REASON_CODE_MILEAGE_MOBILE_APP];

        $arrData = [];
        $arrData['memNo'] = $couponInfo['memNo'];
        $arrData['couponNo'] = $couponInfo['couponNo'];
        $arrData['couponSaveAdminId'] = $mileageContents;
        $arrData['memberCouponStartDate'] = $couponAdmin->getMemberCouponStartDate($couponInfo['couponNo']);
        $arrData['memberCouponEndDate'] = $couponAdmin->getMemberCouponEndDate($couponInfo['couponNo']);
        $arrData['memberCouponState'] = 'y';
        $arrBind = $this->db->get_binding(DBTableField::tableMemberCoupon(), $arrData, 'insert', array_keys($arrData), ['memberCouponNo']);
        $this->db->set_insert_db(DB_MEMBER_COUPON, $arrBind['param'], $arrBind['bind'], 'y');
        $result = $this->db->insert_id();
        $couponAdmin->setCouponMemberSaveCount($couponInfo['couponNo']);

        return $result;
    }

    /**
     * 앱 설치 혜택 지급 내역 저장
     *
     * @param array $installInfo
     * @return bool
     * @throws \Framework\Debug\Exception\DatabaseException
     */
    private function setAppInstallBenefitInfo(array $installInfo)
    {
        if (empty($installInfo['memNo'])) {
            return false;
        }

        if (empty($installInfo['memId'])) {
            $member = \App::load('Bundle\\Component\\Member\\MemberDAO');
            $memberInfo = $member->selectMemberByOne($installInfo['memNo']);
            $installInfo['memId'] = $memberInfo['memId'];
        }

        $setData['amount'] = gd_isset($installInfo['amount'], 0);
        $setData['couponNo'] = gd_isset($installInfo['couponNo'], 0);
        $setData['uuid'] = $installInfo['uuid'];
        $setData['memNo'] = $installInfo['memNo'];
        $setData['memId'] = $installInfo['memId'];

        $arrBind = $this->db->get_binding(DBTableField::tableAppInstallBenefit(), $setData, 'insert');
        $this->db->set_insert_db(DB_APP_INSTALL_BENEFIT, $arrBind['param'], $arrBind['bind'], 'y');
    }

    /**
     * 앱 설치 혜택 내역 조회
     *
     * @param $memNo
     * @return array|object
     * @throws \Framework\Debug\Exception\DatabaseException
     */
    public function getAppInstallBenefitInfo($memNo)
    {
        // 회원번호
        $arrWhere[] = 'memNo = ?';
        $this->db->bind_param_push($arrBind, 'i', $memNo);
        $this->db->strField = '*';
        $this->db->strWhere = implode(' AND ', $arrWhere);
        $query = $this->db->query_complete();
        $strSQL = 'SELECT ' . array_shift($query) . ' FROM ' . DB_APP_INSTALL_BENEFIT . ' as aib ' . implode(' ', $query);
        $benefitInfo = $this->db->query_fetch($strSQL, $arrBind, false);

        return $benefitInfo;
    }

    /**
     * 앱 통계 조회
     *
     * @param null $date
     * @return array|object
     * @throws \Framework\Debug\Exception\DatabaseException
     */
    public function getAppStatistics($date = null)
    {
        if (is_null($date)) {
            $date = date('Y-m-d');
        }

        // 통계날짜
        $arrWhere[] = 'date = ?';
        $this->db->bind_param_push($arrBind, 's', $date);
        $this->db->strField = '*';
        $this->db->strWhere = implode(' AND ', $arrWhere);
        $query = $this->db->query_complete();
        $strSQL = 'SELECT ' . array_shift($query) . ' FROM ' . DB_APP_STATISTICS . ' as ast ' . implode(' ', $query);
        $statistics = $this->db->query_fetch($strSQL, $arrBind, false);

        return $statistics;
    }

    /**
     * 앱 푸시 통계 조회
     *
     * @param $pushCode
     * @return array|bool|object
     * @throws \Framework\Debug\Exception\DatabaseException
     */
    public function getAppPushStatistics($pushCode)
    {
        if (empty($pushCode)) {
            return false;
        }

        // 통계날짜
        $arrWhere[] = 'pushCode = ?';
        $this->db->bind_param_push($arrBind, 's', $pushCode);
        $this->db->strField = '*';
        $this->db->strWhere = implode(' AND ', $arrWhere);
        $query = $this->db->query_complete();
        $strSQL = 'SELECT ' . array_shift($query) . ' FROM ' . DB_APP_PUSH_STATISTICS . ' as aps ' . implode(' ', $query);
        $statistics = $this->db->query_fetch($strSQL, $arrBind, false);

        return $statistics;
    }

    /**
     * 앱 통계 필드 조회
     *
     * @param $mode
     * @param $appOs
     * @return bool|mixed
     */
    private function getAppStatisticsField($mode, $appOs = null)
    {
        $androidField = [
            'mileage' => 'mileageAmountAndroid',
            'coupon' => 'couponCntAndroid',
            'order' => [
                'orderCntAndroid',
                'orderAmountAndroid',
                'settlePriceAndroid',
            ],
        ];
        $iosField = [
            'mileage' => 'mileageAmountIos',
            'coupon' => 'couponCntIos',
            'order' => [
                'orderCntIos',
                'orderAmountIos',
                'settlePriceIos',
            ],
        ];

        $result = false;

        if ($mode == 'order') {
            $result = array_merge($androidField[$mode], $iosField[$mode]);
            $result = ArrayUtils::setDefaultValue(ArrayUtils::reverseKeyValue($result), 0, false);
        } else {
            if (strtolower($appOs) === 'android') {
                $result = $androidField[$mode];
            } elseif (strtolower($appOs) === 'ios') {
                $result = $iosField[$mode];
            }
        }

        return $result;
    }

    /**
     * 마이앱 브릿지 스크립트 반환
     *
     * @param $mode
     * @param $goodsNo
     * @return mixed
     */
    public function getAppBridgeScript($mode, $goodsNo = null)
    {
        $myappScript = \App::getConfig('outsidescript.myapp')->toArray();
        $memberSession = $this->session->get(Member::SESSION_MEMBER_LOGIN);
        $bridgetScript = $myappScript['bridge'];

        switch ($mode) {
            case 'loginView':
                $referer = Request::getReferer();
                $loginViewMsg = $myappScript['loginViewMsg'];
                $loginViewMsg = str_replace('[MYAPP_REFERER]', $referer, $loginViewMsg);
                $bridgetScript = str_replace('[MYAPP_MESSAGE]', $loginViewMsg, $bridgetScript);
                break;
            case 'adultLoginView':
                // 비로그인 상태에서 성인상품 접속시
                $referer = URI_MOBILE . 'intro/adult.php?returnUrl=' . urlencode("/goods/goods_view.php?goodsNo=" . $goodsNo);
                $adultLoginViewMessage = $myappScript['adultLoginView'];
                $adultLoginViewMessage = str_replace('[MYAPP_TYPE]', 'adult', $adultLoginViewMessage);
                $adultLoginViewMessage = str_replace('[MYAPP_REFERER]', $referer, $adultLoginViewMessage);
                $bridgetScript = str_replace('[MYAPP_MESSAGE]', $adultLoginViewMessage, $bridgetScript);
                break;
            case 'login':
                // 자동 로그인
                $autoLoginData = MemberUtil::getCookieByLogin();
                $autoLoginFl = 'n';
                if ($autoLoginData[MemberUtil::COOKIE_LOGIN_FLAG] == MemberUtil::KEY_AUTO_LOGIN) {
                    $autoLoginFl = 'y';
                }
                // sns 로그인 여부
                $loginMessage = $myappScript['loginMsg'];
                $snsLoginMessage = '';
                if (empty($memberSession['snsTypeFl']) === false) {
                    $snsLoginMessage = ', snsLogin: "' . $memberSession['snsTypeFl'] . '"';
                    if ($this->session->get(Member::SESSION_MYAPP_SNS_AUTO_LOGIN) == 'y') {
                        $autoLoginFl = 'y';
                    }
                }

                $loginMessage = str_replace('[MYAPP_SNS_LOGIN]', $snsLoginMessage, $loginMessage);
                $loginMessage = str_replace('[MYAPP_MEMNO]', $memberSession['memNo'], $loginMessage);
                $loginMessage = str_replace('[MYAPP_MEMID]', $memberSession['memId'], $loginMessage);
                $loginMessage = str_replace('[MYAPP_MEMNM]', $memberSession['memNm'], $loginMessage);
                $loginMessage = str_replace('[MYAPP_NICKNM]', $memberSession['nickNm'], $loginMessage);
                $loginMessage = str_replace('[MYAPP_AUTOLOGIN]', $autoLoginFl, $loginMessage);
                $bridgetScript = str_replace('[MYAPP_MESSAGE]', $loginMessage, $bridgetScript);
                break;
            case 'logout':
                $bridgetScript = str_replace('[MYAPP_MESSAGE]', $myappScript['logoutMsg'], $bridgetScript);
                break;
            case 'autoLogout':
            case 'autoSnsLogout':
                $autoLogoutMsg = $myappScript['autoLogoutMsg'];
                $autoLogoutMsg = str_replace('[MYAPP_AUTOLOGOUT_MSG]', '오랜 시간동안 응답이 없어서 자동로그아웃 됩니다.', $autoLogoutMsg);
                $bridgetScript = str_replace('[MYAPP_MESSAGE]', $autoLogoutMsg, $bridgetScript);
                break;
            case 'snsLogout':
                $bridgetScript = str_replace('[MYAPP_MESSAGE]', $myappScript['logoutMsg'], $bridgetScript);
                $bridgetScript = $myappScript['logoutFrm'] . $bridgetScript;
                break;
            default:
                $bridgetScript = '';
                break;
        }

        return $bridgetScript;
    }

    /**
     * 앱 통계 저장
     *
     * @param $fields
     * @param $date
     * @return bool
     * @throws \Framework\Debug\Exception\DatabaseException
     */
    private function setAppStatistics($fields, $date)
    {
        if (empty($fields) || is_array($fields) === false || empty($date)) {
            return false;
        }
        // 통계 조회
        $targetStatistics = $this->getAppStatistics($date);
        // 통계 유무에 따른 분기
        if (empty($targetStatistics)) {
            $insert['date'] = $date;
            foreach ($fields as $key => $val) {
                $insert[$key] = $val;
            }
            $arrBind = $this->db->get_binding(DBTableField::tableAppStatistics(), $insert, 'insert');
            $this->db->set_insert_db(DB_APP_STATISTICS, $arrBind['param'], $arrBind['bind'], 'y');
        } else {
            $update = [];
            foreach ($fields as $key => $val) {
                $update[$key] = ($key == 'orderCntAndroid' || $key == 'orderCntIos') ? $targetStatistics[$key] + $val : $val;
            }
            $where = 'date = ?';
            $arrBind = $this->db->get_binding(DBTableField::tableAppStatistics(), $update, 'update', array_keys($update));
            $this->db->bind_param_push($arrBind['bind'], 's', $date);
            $this->db->set_update_db(DB_APP_STATISTICS, $arrBind['param'], $where, $arrBind['bind']);
        }
    }

    /**
     * 앱 추가 혜택 조회
     *
     * @param $goodsPriceInfo
     * @return null
     */
    public function getOrderAdditionalBenefit($goodsPriceInfo)
    {
        $result = null;

        if ($this->appConfig['benefit']['orderAdditionalBenefit']['isUsing'] == true) {
            $goodsPriceInfo['goodsPrice'] = $goodsPriceInfo['goodsPrice'] ?? 0;
            $goodsPriceInfo['optionPrice'] = $goodsPriceInfo['optionPrice'] ?? 0;
            $goodsPriceInfo['optionTextPrice'] = $goodsPriceInfo['optionTextPrice'] ?? 0;
            $goodsPriceInfo['goodsCnt'] = $goodsPriceInfo['goodsCnt'] ?? 1;
            // 상품가
            $goodsPrice = $goodsPriceInfo['goodsPrice'];
            // 모바일앱 할인혜택
            $discountValue = $this->appConfig['benefit']['orderAdditionalBenefit']['benefit']['discountValue'];
            if ($this->appConfig['benefit']['orderAdditionalBenefit']['benefit']['discountType'] == 'won') {
                if ($goodsPrice < $discountValue) {
                    $discountValue = $goodsPrice;
                }
                $result['goodsDcPrice'] = $discountValue;
                $discountType = gd_global_currency_string();
            } else {
                // 상품옵션가
                if ($this->appConfig['benefit']['orderAdditionalBenefit']['benefit']['useOptionalPrice'] == true) {
                    $goodsPrice += $goodsPriceInfo['optionPrice'];
                }
                // 상품텍스트옵션가
                if ($this->appConfig['benefit']['orderAdditionalBenefit']['benefit']['useTextOptionalPrice'] == true) {
                    $goodsPrice += $goodsPriceInfo['optionTextPrice'];
                }
                // 총 상품가격
                $goodsTotalPrice = $goodsPrice * $goodsPriceInfo['goodsCnt'];
                // 마이앱 할인가
                $result['discount']['goods'] = $goodsTotalPrice / 100 * $discountValue;
                $discountType = '%';
            }
            // 이미지 경로
            if (\Request::isMobile()) {
                $imgPath = PATH_MOBILE_SKIN . 'img/etc/icon_device_m.png';
            } else {
                $imgPath = PATH_SKIN . 'img/etc/icon_device_pc.png';
            }

            $goodsViewReplaceCode = '<img src="' . $imgPath . '" alt="' . __('모바일앱 이미지') . '" />';
            $goodsViewReplaceCode .= __('모바일앱') . ' ' . $discountValue . $discountType . ' ' . __('즉시할인가') . ' ';
            $goodsViewReplaceCode .= '<strong id="myapp_additional_benefit">' . gd_global_currency_symbol() . gd_global_money_format($goodsPriceInfo['goodsPrice'] - $result['discount']['goods']) . gd_global_currency_string() . '</strong>';
            $goodsViewReplaceCode = '<span class="app_notice">' . $goodsViewReplaceCode . '</span>';
            $result['replaceCode']['goodsView'] = $goodsViewReplaceCode;
        }

        return $result;
    }

    /**
     * 주문 정보 조회
     *
     * @param $params
     * @return array|object
     * @throws \Framework\Debug\Exception\DatabaseException
     */
    public function getOrderData($params)
    {
        $strField = [
            'o.orderNo',
            'o.orderStatus',
            'o.appOs',
            'o.pushCode',
            'o.statisticsAppOrderCntFl',
            'o.settlePrice',
            'o.realTaxSupplyPrice',
            'o.realTaxVatPrice',
            'o.realTaxFreePrice',
            'o.useMileage',
            'o.useDeposit',
            'o.settleKind',
            'DATE_FORMAT(o.paymentDt, "%Y-%m-%d") as paymentDt',
            'DATE_FORMAT(o.regDt, "%Y-%m-%d") as regDt',
            'DATE_FORMAT(o.modDt, "%Y-%m-%d") as modDt',
        ];

        // 주문 모드
        if (empty($params['mode']) === false) {
            if ($params['mode'] == 'order') {
                if (empty($params['date']) === false) {
                    $arrWhere[] = '((regDt >= ? AND regDt <= ?) OR (modDt >= ? AND modDt <= ?))';
                    $this->db->bind_param_push($arrBind, 's', $params['date'] . ' 00:00:00');
                    $this->db->bind_param_push($arrBind, 's', $params['date'] . ' 23:59:59');
                    $this->db->bind_param_push($arrBind, 's', $params['date'] . ' 00:00:00');
                    $this->db->bind_param_push($arrBind, 's', $params['date'] . ' 23:59:59');
                }
            } elseif ($params['mode'] == 'claim') {
                if (empty($params['date']) === false) {
                    $arrWhere[] = 'modDt >= ? AND modDt <= ? AND regDt < ?';
                    $this->db->bind_param_push($arrBind, 's', $params['date'] . ' 00:00:00');
                    $this->db->bind_param_push($arrBind, 's', $params['date'] . ' 23:59:59');
                    $this->db->bind_param_push($arrBind, 's', $params['date'] . ' 00:00:00');
                }
                $arrWhere[] = "statisticsAppOrderCntFl = 'y'";
            }
        }

        // 주문 상태
        if (empty($params['orderStatus']) === false) {
            $tmpWhere = [];
            if (is_array($params['orderStatus'])) {
                foreach ($params['orderStatus'] as $item) {
                    $tmpWhere[] = 'SUBSTR(orderStatus, 1, 1) = ?';
                    $this->db->bind_param_push($arrBind, 's', $item);
                }
                $arrWhere[] = '(' . implode(' OR ', $tmpWhere) . ')';
            } else {
                $arrWhere[] = 'SUBSTR(orderStatus, 1, 1) = ?';
                $this->db->bind_param_push($arrBind, 's', $params['orderStatus']);
            }
        }
        // appOs
        $arrWhere[] = 'appOs IS NOT NULL';

        $this->db->strField = implode(',', $strField);
        $this->db->strWhere = implode(' AND ', $arrWhere);
        $query = $this->db->query_complete();
        $strSQL = 'SELECT ' . array_shift($query) . ' FROM ' . DB_ORDER . ' as o ' . implode(' ', $query);
        $orderData = $this->db->query_fetch($strSQL, $arrBind);

        return $orderData;
    }

    /**
     * 앱 주문 통계 처리
     *
     * @param $date
     * @throws \Framework\Debug\Exception\DatabaseException
     */
    public function setAppOrderStatistics($date)
    {
        // 증감시킬 주문 통계 (주문/결제 상태)
        $this->setAppOrderIncreaseStatistics($date);
        // 차감시킬 주문 통계 (클레임 상태)
//        $this->setAppOrderDecreaseStatistics($date);
    }

    /**
     * 앱 주문 통계 업데이트
     *
     * @param $date
     * @return bool
     * @throws \Framework\Debug\Exception\DatabaseException
     */
    public function setAppOrderIncreaseStatistics($date)
    {
        // 통계 처리 날짜
        if (empty($date)) {
            $date = date('Y-m-d');
        }
//        $date = date('Y-m-d', strtotime($date . ' -1 days'));
        $orderAdmin = \App::load('Bundle\\Component\\Order\\OrderAdmin');
        // 주문 데이터 조건
        $params = [
            'mode' => 'order',
            'date' => $date,
            'orderStatus' => $orderAdmin->statusReceiptPossible,
        ];
        // 주문 데이터
        $orderData = $this->getOrderData($params);
        if (count($orderData) < 1) {
            return false;
        }
        // 업데이트 통계필드 초기화
        $initStatisticsField = $this->getAppStatisticsField('order');
        $appStatisticsIncreaseList = $appPushStatisticsIncreaseList = [];
        foreach ($orderData as $key => $val) {
            if (empty($val['appOs'])) {
                continue;
            }
            $update = [];
            // 앱 주문 통계 금액 = 실 결제 금액 + 총 부가결제금액(사용 마일리지 + 사용 예치금)
            $tmpOrderPrice = $val['realTaxFreePrice'] + $val['realTaxSupplyPrice'] + $val['realTaxVatPrice'] + $val['useDeposit'] + $val['useMileage'];
            $tmpOrderSettlePrice = $val['settlePrice'];

            // 주문 건수
            if ($val['statisticsAppOrderCntFl'] != 'y') {
                if (array_key_exists($val['regDt'], $appStatisticsIncreaseList) === false) {
                    $appStatisticsIncreaseList[$val['regDt']] = $initStatisticsField;
                }
                // 앱 주문 통계 건수 반영 여부
                $update['statisticsAppOrderCntFl'] = 'y';
                // os별 주문 건수 카운팅
                $val['appOs'] == 'android' ? $appStatisticsIncreaseList[$val['regDt']]['orderCntAndroid']++ : $appStatisticsIncreaseList[$val['regDt']]['orderCntIos']++;
                if (empty($val['pushCode']) === false) {
                    if (array_key_exists($val['pushCode'], $appPushStatisticsIncreaseList) === false) {
                        $appPushStatisticsIncreaseList[$val['pushCode']] = $initStatisticsField;
                    }
                    $val['appOs'] == 'android' ? $appPushStatisticsIncreaseList[$val['pushCode']]['orderCntAndroid']++ : $appPushStatisticsIncreaseList[$val['pushCode']]['orderCntIos']++;
                }
            }

            // os별 주문 및 결제 금액 계산
            if (substr($val['orderStatus'], 0, 1) != 'o' && $val['paymentDt'] != '0000-00-00') {
                if (array_key_exists($val['regDt'], $appStatisticsIncreaseList) === false) {
                    $appStatisticsIncreaseList[$val['regDt']] = $initStatisticsField;
                }

                if ($val['appOs'] == 'android') {
                    $appStatisticsIncreaseList[$val['regDt']]['orderAmountAndroid'] += $tmpOrderPrice;
                    $appStatisticsIncreaseList[$val['regDt']]['settlePriceAndroid'] += $tmpOrderSettlePrice;
                } else {
                    $appStatisticsIncreaseList[$val['regDt']]['orderAmountIos'] += $tmpOrderPrice;
                    $appStatisticsIncreaseList[$val['regDt']]['settlePriceIos'] += $tmpOrderSettlePrice;
                }

                if (empty($val['pushCode']) === false) {
                    if (array_key_exists($val['pushCode'], $appPushStatisticsIncreaseList) === false) {
                        $appPushStatisticsIncreaseList[$val['pushCode']] = $initStatisticsField;
                    }

                    if ($val['appOs'] == 'android') {
                        $appPushStatisticsIncreaseList[$val['pushCode']]['orderAmountAndroid'] += $tmpOrderPrice;
                        $appPushStatisticsIncreaseList[$val['pushCode']]['settlePriceAndroid'] += $tmpOrderSettlePrice;
                    } else {
                        $appPushStatisticsIncreaseList[$val['pushCode']]['orderAmountIos'] += $tmpOrderPrice;
                        $appPushStatisticsIncreaseList[$val['pushCode']]['settlePriceIos'] += $tmpOrderSettlePrice;
                    }
                }
            } else {
                if (array_key_exists($val['regDt'], $appStatisticsIncreaseList) === false) {
                    $appStatisticsIncreaseList[$val['regDt']] = $initStatisticsField;
                }

                // os별 주문 금액 계산
                if ($val['appOs'] == 'android') {
                    $appStatisticsIncreaseList[$val['regDt']]['settlePriceAndroid'] += $tmpOrderSettlePrice;
                } else {
                    $appStatisticsIncreaseList[$val['regDt']]['settlePriceIos'] += $tmpOrderSettlePrice;
                }

                if (empty($val['pushCode']) === false) {
                    if (array_key_exists($val['pushCode'], $appPushStatisticsIncreaseList) === false) {
                        $appPushStatisticsIncreaseList[$val['pushCode']] = $initStatisticsField;
                    }

                    if ($val['appOs'] == 'android') {
                        $appPushStatisticsIncreaseList[$val['pushCode']]['settlePriceAndroid'] += $tmpOrderSettlePrice;
                    } else {
                        $appPushStatisticsIncreaseList[$val['pushCode']]['settlePriceIos'] += $tmpOrderSettlePrice;
                    }
                }
            }

            // 주문 필드 업데이트
            if (count($update) > 0) {
                $where = 'orderNo = ?';
                $arrBind = $this->db->get_binding(DBTableField::tableOrder(), $update, 'update', array_keys($update));
                $this->db->bind_param_push($arrBind['bind'], 's', $val['orderNo']);
                $this->db->set_update_db(DB_ORDER, $arrBind['param'], $where, $arrBind['bind']);
                unset($update, $where, $arrBind);
            }
        }
        // 통계 데이터 저장
        $this->setAppStatisticsDataUpdate($appStatisticsIncreaseList, $appPushStatisticsIncreaseList);
    }

    /**
     * 앱 주문 통계 업데이트 (클레임 처리)
     * 사용 안함 (혹시 몰라 주석처리)
     *
     * @param $date
     * @return bool
     * @throws \Framework\Debug\Exception\DatabaseException
     */
    /*public function setAppOrderDecreaseStatistics($date)
    {
        // 통계 처리 날짜
        if (empty($date)) {
            $date = date('Y-m-d');
        }
        $date = date('Y-m-d', strtotime($date . ' -1 days'));
        // 주문 데이터 조건
        $params = [
            'mode' => 'claim',
            'date' => $date,
        ];
        // 주문 데이터
        $orderData = $this->getOrderData($params);
        if (count($orderData) < 1) {
            return false;
        }
        // 업데이트 통계필드 초기화
        $initStatisticsField = $this->getAppStatisticsField('order');
        $appStatisticsDecreaseList = $appPushStatisticsDecreaseList = [];
        $decreaseOrderCntStatus = ['c'];
        $decreaseOrderCntFullStatus = ['r3'];
        foreach ($orderData as $key => $val) {
            if (empty($val['appOs'])) {
                continue;
            }
            $update = [];
            // 앱 주문 통계 금액 = 실 결제 금액 + 총 부가결제금액(사용 마일리지 + 사용 예치금)
            $tmpOrderPrice = $val['realTaxFreePrice'] + $val['realTaxSupplyPrice'] + $val['realTaxVatPrice'] + $val['useDeposit'] + $val['useMileage'];
            // 주문 건수 (전체 주문이 취소, 환불 된 경우)
            if (in_array($val['orderStatus'], $decreaseOrderCntFullStatus) || ($val['settleKind'] == 'gb' && $tmpOrderPrice == 0) ||in_array(substr($val['orderStatus'], 0, 1), $decreaseOrderCntStatus)) {
                if (array_key_exists($val['regDt'], $appStatisticsDecreaseList) === false) {
                    $appStatisticsDecreaseList[$val['regDt']] = $initStatisticsField;
                }
                // 앱 주문 통계 건수 반영 여부
                $update['statisticsAppOrderCntFl'] = 'n';
                // os별 주문 건수 카운팅
                $val['appOs'] == 'android' ? $appStatisticsDecreaseList[$val['regDt']]['orderCntAndroid']-- : $appStatisticsDecreaseList[$val['regDt']]['orderCntIos']--;
                if (empty($val['pushCode']) === false) {
                    if (array_key_exists($val['pushCode'], $appPushStatisticsDecreaseList) === false) {
                        $appPushStatisticsDecreaseList[$val['pushCode']] = $initStatisticsField;
                    }
                    $val['appOs'] == 'android' ? $appPushStatisticsDecreaseList[$val['pushCode']]['orderCntAndroid']-- : $appPushStatisticsDecreaseList[$val['pushCode']]['orderCntIos']--;
                }
            }
            // 주문 금액 (주문 금액이 변경되거나, 다시 입금대기로 변경된 경우)
            if ($val['statisticsAppOrderAmount'] > 0 && ($tmpOrderPrice != $val['statisticsAppOrderAmount'] || $val['paymentDt'] == '0000-00-00')) {
                if (array_key_exists($val['regDt'], $appStatisticsDecreaseList) === false) {
                    $appStatisticsDecreaseList[$val['regDt']] = $initStatisticsField;
                }
                // 앱 주문 통계 반영 금액
                if ($tmpOrderPrice - $val['statisticsAppOrderAmount'] == 0) {
                    $update['statisticsAppOrderAmount'] = 0;
                    $tmpAppOrderAmount = -$tmpOrderPrice;
                } else {
                    $update['statisticsAppOrderAmount'] = $tmpOrderPrice;
                    $tmpAppOrderAmount = $tmpOrderPrice - $val['statisticsAppOrderAmount'];
                }
                // os별 주문 금액 계산
                $val['appOs'] == 'android' ? $appStatisticsDecreaseList[$val['regDt']]['orderAmountAndroid'] += $tmpAppOrderAmount : $appStatisticsDecreaseList[$val['regDt']]['orderAmountIos'] += $tmpAppOrderAmount;
                if (empty($val['pushCode']) === false) {
                    if (array_key_exists($val['pushCode'], $appPushStatisticsDecreaseList) === false) {
                        $appPushStatisticsDecreaseList[$val['pushCode']] = $initStatisticsField;
                    }
                    $val['appOs'] == 'android' ? $appPushStatisticsDecreaseList[$val['pushCode']]['orderAmountAndroid'] += $tmpAppOrderAmount : $appPushStatisticsDecreaseList[$val['pushCode']]['orderAmountIos'] += $tmpAppOrderAmount;
                }
            }
            // 주문 필드 업데이트
            if (count($update) > 0) {
                $where = 'orderNo = ?';
                $arrBind = $this->db->get_binding(DBTableField::tableOrder(), $update, 'update', array_keys($update));
                $this->db->bind_param_push($arrBind['bind'], 's', $val['orderNo']);
                $this->db->set_update_db(DB_ORDER, $arrBind['param'], $where, $arrBind['bind']);
                unset($update, $where, $arrBind);
            }
        }
        // 통계 데이터 저장
        $this->setAppStatisticsDataUpdate($appStatisticsDecreaseList, $appPushStatisticsDecreaseList);
    }*/

    /**
     * 통계 데이터 저장
     *
     * @param array $appData
     * @param array $appPushData
     * @throws \Framework\Debug\Exception\DatabaseException
     */
    private function setAppStatisticsDataUpdate($appData = [], $appPushData = [])
    {
        // 앱 통계 업데이트 (주문 등록일 기준)
        if (count($appData) > 0) {
            foreach ($appData as $regDt => $data) {
                $appUpdate['orderCntAndroid'] = $data['orderCntAndroid'];
                $appUpdate['orderCntIos'] = $data['orderCntIos'];
                $appUpdate['orderAmountAndroid'] = $data['orderAmountAndroid'];
                $appUpdate['orderAmountIos'] = $data['orderAmountIos'];
                $appUpdate['settlePriceAndroid'] = $data['settlePriceAndroid'];
                $appUpdate['settlePriceIos'] = $data['settlePriceIos'];
                $this->setAppStatistics($appUpdate, $regDt);
                unset($appUpdate);
            }
        }

        // 앱 푸시 통계 업데이트 (푸시 코드 기준)
        if (count($appPushData) > 0) {
            foreach ($appPushData as $pushCode => $pushData) {
                $appPushUpdate['orderCntAndroid'] = $pushData['orderCntAndroid'];
                $appPushUpdate['orderCntIos'] = $pushData['orderCntIos'];
                $appPushUpdate['orderAmountAndroid'] = $pushData['orderAmountAndroid'];
                $appPushUpdate['orderAmountIos'] = $pushData['orderAmountIos'];
                $appPushUpdate['settlePriceAndroid'] = $pushData['settlePriceAndroid'];
                $appPushUpdate['settlePriceIos'] = $pushData['settlePriceIos'];
                $this->setAppPushStatistics($appPushUpdate, $pushCode);
                unset($appPushUpdate);
            }
        }
    }

    /**
     * 앱 푸시 통계 데이터 저장
     *
     * @param $fields
     * @param $pushCode
     * @return bool
     * @throws \Framework\Debug\Exception\DatabaseException
     */
    private function setAppPushStatistics($fields, $pushCode)
    {
        if (empty($fields) || is_array($fields) === false || empty($pushCode)) {
            return false;
        }
        // 푸시 주문 통계 내역
        $pushStatistics = $this->getAppPushStatistics($pushCode);
        // 푸시 주문 내역에 따른 분기
        if (empty($pushStatistics)) {
            $insert['pushCode'] = $pushCode;
            foreach ($fields as $key => $val) {
                $insert[$key] = $val;
            }
            $arrBind = $this->db->get_binding(DBTableField::tableAppPushStatistics(), $insert, 'insert');
            $this->db->set_insert_db(DB_APP_PUSH_STATISTICS, $arrBind['param'], $arrBind['bind'], 'y');
        } else {
            $update = [];
            foreach ($fields as $key => $val) {
                $update[$key] = ($key == 'orderCntAndroid' || $key == 'orderCntIos') ? $pushStatistics[$key] + $val : $val;
            }
            $where = 'pushCode = ?';
            $arrBind = $this->db->get_binding(DBTableField::tableAppPushStatistics(), $update, 'update', array_keys($update));
            $this->db->bind_param_push($arrBind['bind'], 's', $pushCode);
            $this->db->set_update_db(DB_APP_PUSH_STATISTICS, $arrBind['param'], $where, $arrBind['bind']);
        }
    }

    /**
     * 푸시 대체 발송
     *
     * @param string $pushToken
     * @param string $pushTitle
     * @param string $pushContent
     * @param array $pushUser
     *
     * @return array
     */
    public function sendAppPush($pushToken, $pushTitle, $pushContent, $pushUser)
    {
        $request = [
            'users'     => $pushUser,
            'title'     => $pushTitle,
            'content'   => $pushContent,
            'category'  => 'notice',
        ];

        $ch = curl_init($this->appApiUrl);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HEADER, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type: application/json', 'NGPG-VENDOR-APP-TOKEN: ' .$pushToken, 'NGPG-VENDOR-API-TOKEN: c6d2086fc5e52d19ef85c6abcc644ad1e806e879432416969c38188f2b65157f'));
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($request));
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, false);
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 15);
        $res = curl_exec($ch);
        $body = substr($res,0,curl_getinfo($ch,CURLINFO_HEADER_SIZE));
        curl_close($ch);

        $this->logger->info('마이앱 푸시 대체 발송 전송 데이터', $request);
        $this->logger->info('마이앱 푸시 대체 발송 통신 결과', [$body]);

        return $res;
    }


    /**
     * 접속 OS 체크
     *
     * @return string
     */
    function getMyappOsAgent() {
        $user_agent = \Request::getUserAgent();
        $os_platform  = "Unknown OS Platform";
        $os_array     = array(
            '/iphone/i'             =>  'ios',
            '/ipod/i'               =>  'ios',
            '/ipad/i'               =>  'ios',
            '/android/i'            =>  'android'
//            '/windows nt 10/i'      =>  'Windows 10',
//            '/windows nt 6.3/i'     =>  'Windows 8.1',
//            '/windows nt 6.2/i'     =>  'Windows 8',
//            '/windows nt 6.1/i'     =>  'Windows 7',
//            '/windows nt 6.0/i'     =>  'Windows Vista',
//            '/windows nt 5.2/i'     =>  'Windows Server 2003/XP x64',
//            '/windows nt 5.1/i'     =>  'Windows XP',
//            '/windows xp/i'         =>  'Windows XP',
//            '/windows nt 5.0/i'     =>  'Windows 2000',
//            '/windows me/i'         =>  'Windows ME',
//            '/win98/i'              =>  'Windows 98',
//            '/win95/i'              =>  'Windows 95',
//            '/win16/i'              =>  'Windows 3.11',
//            '/macintosh|mac os x/i' =>  'Mac OS X',
//            '/mac_powerpc/i'        =>  'Mac OS 9',
//            '/linux/i'              =>  'Linux',
//            '/ubuntu/i'             =>  'Ubuntu',
//            '/blackberry/i'         =>  'BlackBerry',
//            '/webos/i'              =>  'Mobile'
        );

        foreach ($os_array as $regex => $value)
            if (preg_match($regex, $user_agent))
                $os_platform = $value;

        return $os_platform;
    }

    /**
     * 접속 브라우저 체크
     *
     * @return string
     */
    function getMyappBrowserAgent() {
        $user_agent = \Request::getUserAgent();
        $browser        = "Unknown Browser";
        $browser_array = array(
            '/msie/i'      => 'Internet Explorer',
            '/firefox/i'   => 'Firefox',
            '/safari/i'    => 'Safari',
            '/chrome/i'    => 'Chrome',
            '/edge/i'      => 'Edge',
            '/opera/i'     => 'Opera',
            '/netscape/i'  => 'Netscape',
            '/maxthon/i'   => 'Maxthon',
            '/konqueror/i' => 'Konqueror',
            '/mobile/i'    => 'Handheld Browser'
        );

        foreach ($browser_array as $regex => $value)
            if (preg_match($regex, $user_agent))
                $browser = $value;

        return $browser;
    }
}