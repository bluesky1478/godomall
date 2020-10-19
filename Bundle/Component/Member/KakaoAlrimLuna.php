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

namespace Bundle\Component\Member;

use App;
use Component\Database\DBTableField;
use Component\Godo\GodoSmsServerApi;
use Component\Member\Group\Util;
use Component\Page\Page;
use Component\Validator\Validator;
use Component\Sms\Sms;
use Component\Sms\SmsAutoCode;
use Component\Sms\Code;
use Component\Storage\Storage;
use Exception;
use Framework\Database\DBTool;
use Framework\Utility\ArrayUtils;
use Framework\Utility\DateTimeUtils;
use Framework\Utility\StringUtils;
use Framework\Utility\ComponentUtils;
use UserFilePath;
use Logger;
use Request;
use Framework\Debug\Exception\AlertBackException;
/**
 * Class KakaoAlrimLuna
 * @package Bundle\Component\Member
 * @author  cjb333
 */
class KakaoAlrimLuna
{
    /** @var \Framework\Database\DBTool $db */
    private $db;
    private $globals;
    private $kakaoSetting;
    private $smsAutoConfig;
    private $fields;
    protected $smsAutoCode;
    protected $recall;
    protected $fieldTypes;


    /**
     * @var string 카카오알림톡 중앙서버용 암호화 키
     */
    const ENCRYPT_KEY = "fnskthvmxmzkzkdhdkfflaxhrqkfthdr";

    function __construct(DBTool $db = null)
    {
        if (gd_is_plus_shop(PLUSSHOP_CODE_KAKAOALRIMLUNA) === false) {
            throw new AlertBackException(__('[플러스샵] 미설치 또는 미사용 상태입니다. 설치 완료 및 사용 설정 후 플러스샵 앱을 사용할 수 있습니다.'));
        }

        if ($db === null) {
            $db = App::load('DB');
        }
        $this->db = $db;

        $this->smsAutoCode = new SmsAutoCode();
        $this->globals = \App::getInstance('globals');
        $this->kakaoSetting = gd_policy('kakaoAlrimLuna.config');
        $this->smsAutoConfig = gd_policy('sms.smsAuto');
        //$this->fields = DBTableField::getFieldTypes('tableKakaoMessageTemplate');

        $smsConf = gd_policy('sms.config');
        $this->recall = $smsConf['smsCallNum'];
        //$this->fieldTypes = array_merge(DBTableField::getFieldTypes('tableSmsLog'), DBTableField::getFieldTypes('tableSmsSendList'));
    }


/*
    public function saveTemplte($request)
    {

        $values[smsType] = $request[smsType];
        $values[templateCode] = $request[templateCode];
        $values[templateName] = $request[templateName];
        $values[templateContent] = $request[templateContent];
        $bind = $this->db->get_binding(DBTableField::tableKakaoLunaMessageTemplate(), $values, 'insert');
        $this->db->set_insert_db(DB_KAKAO_LUNA_MESSAGE_TEMPLATE, $bind['param'], $bind['bind'], 'y');
    }
*/
    /**
     * 카카오 알림톡 중앙서버로 카카오 알림메세지 전송
     *
     * @return string
     */
    public function sendKakaoAlrimLuna($aSmsLog, $aSender, $aLogData, $receiverForSaveSmsSendList, $aReplaceArguments, $contents)
    {
        $shopSno = $this->globals->get('gLicense.godosno');
        foreach ($receiverForSaveSmsSendList as $val) {
            $randNum = rand(0, 9) . rand(0, 9) . rand(0, 9) . rand(0, 9);
            $kakaoSendKey = time() . $randNum;
            $aSmsLog['smsSendKey'] = $kakaoSendKey;
            $aLogData['kakaoSendKey'] = $kakaoSendKey;
            $tempDomain = str_replace('http://', '', $aReplaceArguments['shopUrl']);
            $tempDomain = str_replace('https://', '', $tempDomain);
            $aTempDomain = explode(':', $tempDomain);
            $tempDomain = $aTempDomain[0];
            $tempDomain = StringUtils::getKoreanToPuny($tempDomain);

            $val['cellPhone'] = str_replace('-', '', $val['cellPhone']);

            if ($aSmsLog['sendType'] == 'res_send') {
                $tranDTime = $aSmsLog['reserveDt'];
            } else {
                $tranDTime = 'now';
            }

            $useParam = explode("||",$aSmsLog['useParam']);
            $tempParam = array();
            foreach ($useParam as $value){
                if ($aSmsLog['smsType'] == 'board') { // 게시판은 다른곳이랑 다르게 상점명코드로 작성자명을 대처해서...
                    if ($value == 'rc_mallNm') {
                        $tempParam['rc_mallNm'] = $aReplaceArguments['wriNm'];
                    } elseif ($value == 'wriNm') {
                        $tempParam['wriNm'] = $aReplaceArguments['rc_mallNm'];
                    } else {
                        $tempParam[$value] = $aReplaceArguments[$value];
                    }
                } else {
                    $tempParam[$value] = $aReplaceArguments[$value];
                }
            }
            //공통파라미터
            $tempParam['api_key'] = $this->kakaoSetting['lunaClientKey']; //루나키
            $tempParam['tel_no'] = $val['cellPhone'];
            $tempParam['message_code'] = $aSmsLog['code'];
            $replaceData = json_encode($tempParam, JSON_UNESCAPED_UNICODE);

            $data = array(
                'serviceMode' => 'luna',
                'svcKind' => 'echost',
                'svcKey' => $shopSno,
                'fromPhoneNumber' => $this->recall,
                'toPhoneNumber' => $val['cellPhone'],
                'tranId' => $shopSno,
                'tranDTime' => $tranDTime,
                'shopDomain' => $tempDomain,
                'shopKind' => 'godo5',
                'msg' => $contents,
                'pass' => Sms::getSmsPassword(),
                'templateCode' => $aSmsLog['code'],
                'kakaoSendKey' => $kakaoSendKey,
                'clientId' => $this->kakaoSetting['lunaCliendId'], //루나아이디
                'replaceData' => $replaceData, //루나전송 데이터(json)
            );

            Logger::channel('kakao')->info('KAKAO_ALRIM_SEND_LUNA', $aReplaceArguments);
            Logger::channel('kakao')->info('KAKAO_ALRIM_SEND_LUNA', $data);

            $ch = curl_init();
            curl_setopt($ch, CURLOPT_URL, 'https://kakaoapi.godo.co.kr/kakaoSend.php');
            curl_setopt($ch, CURLOPT_POST, 1);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
            curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 10);
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
            curl_setopt($ch, CURLOPT_POSTFIELDS, $data);

            $result = curl_exec($ch);
            curl_close($ch);
            $curlResult = json_decode($result);

            Logger::channel('kakao')->info('KAKAO_ALRIM_SEND_LUNA_RESULT', json_decode($result, true));

        }

        return $curlResult;
    }

    /**
     * SMS 자동 발송 관련 기본 설정 값 으로 카카오 알림톡에 맞게 조금 변경해서 데이터를 리턴
     *
     * @return array 설정값
     */
    public function getSmsAutoData()
    {

        // 기본 SMS 자동발송 코드
        $smsAutoType = $this->smsAutoCode->getCodes();

        // 기본 SMS 자동발송코드에서 카카오알림톡에 필요없는 값들 제거
        unset($smsAutoType['promotion']);
        unset($smsAutoType['admin']);

        $lunaOrderUseKey = array(
            'ORDER',
            'INCASH',
            'ACCOUNT',
            'DELIVERY',
            'INVOICE_CODE',
            'DELIVERY_COMPLETED',
            'CANCEL',
            'REPAY',
            //'REPAYPART',
            'SOLD_OUT'
        );
        $lunaMemberUseKey = array(
            'JOIN',
            'APPROVAL',
            'PASS_AUTH',
            'SLEEP_INFO',
            'SLEEP_INFO_TODAY',
            'SLEEP_AUTH',
            'AGREEMENT2YPERIOD',
            'GROUP_CHANGE',
            //'MILEAGE_PLUS',
            'MILEAGE_MINUS',
            //'MILEAGE_EXPIRE',
            //'DEPOSIT_PLUS',
            'DEPOSIT_MINUS'
        );
        $lunaBoardUseKey = array(
            //'goodsreview',
            //'goodsqa',
            'qa',
            //'notice',
            //'event',
            //'cooperation'
        );
        $lunaUseKey = array_merge($lunaOrderUseKey,$lunaMemberUseKey,$lunaBoardUseKey);
        
        //루나소프트에서 사용하는 키값만 재정의
        $tmpSmsAutoType = array();
        foreach ($smsAutoType as $key => $smsAutoVal ){
            foreach ($smsAutoVal as $smsAutoData){
                if (in_array($smsAutoData['code'], $lunaUseKey)) {
                    if($smsAutoData['code'] == 'qa'){
                        $smsAutoData['sendType'] = 'member_admin'; //qa게시판은 공급사 사용안함
                    }
                    $tmpSmsAutoType[$key][] = $smsAutoData;
                }
            }
        }
        $smsAutoType = $tmpSmsAutoType;

        // SMS 자동발송 설정값 불러오기
        $smsAuto = gd_policy('kakaoAlrimLuna.kakaoAuto');

        if (empty($smsAuto['smsAutoSendOver']) === true) {
            $smsAuto['smsAutoSendOver'] = 'limit';
        }
        $smsAutoType['checked']['smsAutoSendOver'][$smsAuto['smsAutoSendOver']] = 'checked=\'checked\'';

        foreach ($smsAutoType as $keyType => $valDate) {
            if ($keyType === 'checked') {
                continue;
            }
            foreach ($valDate as $key => $val) {
                // 발송항목 및 발송종류 관련 설정 (SMS 자동발송 설정값 불러 왔을때 해당 값이 있다면)
                if (isset($smsAuto[$keyType][$val['code']]) === true && empty($smsAuto[$keyType][$val['code']]) === false) {
                    foreach ($smsAuto[$keyType][$val['code']] as $aKey => $aVal) {
                        $smsAutoType[$keyType][$key][$aKey] = $aVal;
                    }
                }
                // 쿠폰만료안내 저장값이 없을때 디폴트값 셋팅
                if ($val['code'] == 'COUPON_WARNING' && !$smsAutoType[$keyType][$key]['smsCouponLimitDate']) {
                    $smsAutoType[$keyType][$key]['smsCouponLimitDate'] = '7';
                }
            }
        }

        $smsAutoType['orderUseFlag'] = gd_isset($smsAuto['orderUseFlag'], 'n');
        $smsAutoType['memberUseFlag'] = gd_isset($smsAuto['memberUseFlag'], 'n');
        $smsAutoType['boardUseFlag'] = gd_isset($smsAuto['boardUseFlag'], 'n');

        // SMS 기본 설정값 불러오기
        $smsAutoType['smsCallNum'] = $this->recall;

        $smsAutoType['orderUseFlag'] = gd_isset($smsAutoType['orderUseFlag'], 'n');
        $smsAutoType['memberUseFlag'] = gd_isset($smsAutoType['memberUseFlag'], 'n');
        $smsAutoType['boardUseFlag'] = gd_isset($smsAutoType['boardUseFlag'], 'n');

        return $smsAutoType;
    }

    public function getKakaoTemplateList()
    {
        $strSQL = 'SELECT templateCode,templateContent,useParam FROM ' . DB_KAKAO_LUNA_MESSAGE_TEMPLATE  ;
        $data = $this->db->query_fetch($strSQL, null, false);
        return $data;
    }

    public function sendLunaId($request)
    {

        $shopSno = $this->globals->get('gLicense.godosno');
        $clientId = $request['lunaCliendId'];
        $tempDomain = str_replace('http://', '', URI_HOME);
        $tempDomain = str_replace('https://', '', $tempDomain);
        $aTempDomain = explode(':', $tempDomain);
        $tempDomain = $aTempDomain[0];
        $shopDomain = StringUtils::getKoreanToPuny($tempDomain);
        $returnUrl = URI_ADMIN.'member/set_kakao_luna.php';

        $p = array(
            'godosno' => $shopSno,
            'clientId' => $clientId,
            'shopDomain' => rtrim($shopDomain, DS),
            'returnUrl' => urlencode($returnUrl)
        );

        $encodedContent = $this->encrypt($p);

        return $encodedContent;
    }


    /**
     * 카카오 알림 자동 발송 관련 설정 저장
     *
     * @param array $request 설정값
     */
    public function saveKakaoAuto($request)
    {

        unset($request['mode']);
        unset($request['return_mode']);
        $smsAutoReceiveKind = Sms::SMS_AUTO_RECEIVE_LIST;
        unset($smsAutoReceiveKind['promotion']);
        $smsAutoType = [
            'member',
            'admin',
            'provider',
            'board',
        ];

        foreach ($smsAutoReceiveKind as $smsType => $val) {
            foreach ($request[$smsType] as $autoCode => $autoData) {
                foreach ($smsAutoType as $autoType) {
                    unset($request[$smsType][$autoCode][$autoType . 'Contents']);
                }
            }
        }

        // SMS 자동발송 관련 내용 저장
        gd_set_policy('kakaoAlrimLuna.kakaoAuto', $request);
    }

    // 알림톡 결과 수신 상태
    public function getStringResultCode($sCode)
    {
        $aCode = array(
            'K000' => '성공',
            'K101' => '메시지를 전송할 수 없음',
            'K102' => '전화번호 오류',
            'K103' => '메시지 길이제한 오류',
            'K999' => '시스템오류',
        );
        if ($aCode[$sCode] != '') {
            return $aCode[$sCode];
        } else {
            return '기타사유';
        }
    }

    /**
     * 카카오 알림톡 중앙서버로 카카오인증키 삭제요청
     *
     * @return string
     */
    public function deleteLunaKey()
    {
        $shopSno = $this->globals->get('gLicense.godosno');
        //'godosno' => 333410,
        $p = array(
            'godosno' => $shopSno,
            'clientId' => $this->kakaoSetting['lunaCliendId']
        );
        $encodedContent = $this->encrypt($p);
        $data = array(
            'p' => $encodedContent
        );
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, 'https://kakaoapi.godo.co.kr/lunaKakaoRemove.php');
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 10);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
        $result = curl_exec($ch);
        curl_close($ch);
        $curlResult = json_decode($result, true);
        return $curlResult;
    }

    public function getLunaKeyDec($decContent){

        $lunaKey = $this->decrypt($decContent);
        return $lunaKey;
    }

    //256bit aes 암호화
    private function encrypt($data)
    {
        $data = json_encode($data, JSON_UNESCAPED_UNICODE);
        $iv = substr(self::ENCRYPT_KEY,0,16);
        $enc = openssl_encrypt($data,'AES-256-CBC',self::ENCRYPT_KEY,1,$iv);
        $data = base64_encode($enc);

        return $data;
    }

    //256bit aes 복호화
    private function decrypt($data)
    {
        $data = base64_decode($data);
        $iv = substr(self::ENCRYPT_KEY,0,16);
        $dec = openssl_decrypt($data,'AES-256-CBC',self::ENCRYPT_KEY,1,$iv);
        $data = json_decode($dec, true);

        return $data;
    }

    /**
     * 자동 발송 알림톡 예약시간 리스트
     *
     * @return array
     */
    public function getKakaoAlrimReservationTime()
    {
        $defaultReservationTime = Sms::KAKAO_AUTO_RESERVATION_DEFAULT_TIME;
        $reservationTime = Sms::SMS_AUTO_RESERVATION_TIME_LIST;
        $result = [];

        foreach ($defaultReservationTime as $dKey => $dVal) {
            if ($dVal == ArrayUtils::first($reservationTime)) {
                $result[$dKey] = $reservationTime;
            } else {
                $tmpReservationTime = $reservationTime;
                foreach ($reservationTime as $rVal) {
                    if ($dVal == $rVal) {
                        $result[$dKey] = $tmpReservationTime;
                        break;
                    } else {
                        array_shift($tmpReservationTime);
                    }
                }
            }
        }

        return $result;
    }




}
