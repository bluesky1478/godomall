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

namespace Bundle\Component\Goods;

use Component\Sms\Sms;
use Component\Sms\SmsSender;
use Component\Sms\SmsMessage;
use Component\Database\DBTableField;

/**
 * Class 주문/배송 SMS 자동 발송
 * 환불, 반품, 교환, 고객 교환/반품/환불 승인/거절 문자 발송
 *
 * @package Bundle\Component\Sms
 * @author  seong ho yun
 */
class KakaoAlimStock
{
    protected $db;

    protected $kakaoSendDuration = '1';      //카카오 발송 시간(시간단위, 0이면 실시간)
    protected $kakaoNight = 'n';          //카카오 야간 발송 여부
    protected $kakaoNumbers;              //카카오 발송 번호(배열)
    protected $kakaoStopSend = false;             //카카오 판매중지 발송 여부
    protected $kakaoRequestSend = false;          //카카오 확인요청 발송 여부
    protected $kakaoSendRange = 'all';            //카카오 발송 범위(본사, 공급사 등)

    protected $goodsInfo;               //발송 상품 정보

    /**
     * KakaoAlimStock constructor.
     * @param array $config 설정내용
     */
    public function __construct(array $config = [])
    {
        if (!is_object($this->db)) {
            $this->db = \App::load('DB');
        }

        //카카오발송 설정 확인(발송 시간)
        $this->kakaoSendDuration = $config['kakaoSendDuration'];
        $this->kakaoNight = $config['kakaoNight'][0];

        //카카오발송 설정 확인(카카오 번호)
        $this->kakaoNumbers = $config['kakaoNumber'];

        //카카오발송 설정 확인(판매중지 발송, 확인요청 발송)
        if(in_array('stop', $config['kakaoRange'])) $this->kakaoStopSend = true;
        if(in_array('request', $config['kakaoRange'])) $this->kakaoRequestSend = true;

        //본사상품, 공급사 상품에만 알림 보낼지 설정
        $this->kakaoSendRange = $config['sendRange'][0];
    }

    /**
     * 상품 정보 세팅
     * @param $goodsNo
     * @param $goodsNm
     * @param $optNo
     * @param $goodsCd
     * @param $optNm
     * @param $type
     * @param $stockCnt
     */
    public  function setGoods($goodsNo, $goodsNm, $optNo, $goodsCd, $optNm, $type, $stockCnt){
        $this->goodsInfo['goodsNo'] = $goodsNo;
        $this->goodsInfo['goodsNm'] = $goodsNm;
        $this->goodsInfo['goodsCd'] = $goodsCd;
        $this->goodsInfo['optNo'] = $optNo;
        $this->goodsInfo['optNm'] = $optNm;
        $this->goodsInfo['type'] = $type;
        $this->goodsInfo['cnt'] = $stockCnt;
    }

    /**
     * 판매중지 카카오발송 메시지 생성
     * @param $direct
     * @return string
     */
    private function getStopSellMsg($direct){
        //데이터 초기화
        $goodsNm = $this->goodsInfo['goodsNm'];
        if(!empty($this->goodsInfo['goodsCd'])){
            $goodsCd = sprintf('(%s)', $this->goodsInfo['goodsCd']);
        }else{
            $goodsCd = '';
        }
        $optNm = $this->goodsInfo['optNm'];
        $cnt = $this->goodsInfo['cnt'];

        if($direct === true){
            $content = sprintf('판매중지 옵션
%s%s
%s
재고:%s', $goodsNm, $goodsCd, $optNm, $cnt);
        }
        return $content;
    }

    /**
     * 재고확인 카카오발송 메시지 생성
     * @param $direct
     * @return string
     */
    private function getRequestMsg($direct){
        //데이터 초기화
        $goodsNm = $this->goodsInfo['goodsNm'];
        if(!empty($this->goodsInfo['goodsCd'])){
            $goodsCd = sprintf('(%s)', $this->goodsInfo['goodsCd']);
        }else{
            $goodsCd = '';
        }
        $optNm = $this->goodsInfo['optNm'];
        $cnt = $this->goodsInfo['cnt'];

        if($direct === true){
            $content = sprintf('확인요청 옵션
%s%s
%s
재고:%s', $goodsNm, $goodsCd, $optNm, $cnt);
        }
        return $content;
    }

    /**
     * 카카오 발송 시간 구하기
     * @return false|string
     */
    private function getNextSendTime(){
        //설정별 발송 시간
        if($this->kakaoNight == true){
            $timeTable['1'] = [0, 1, 2, 3, 4, 5, 6, 7, 8, 9, 10, 11, 12, 13, 14, 15, 16, 17, 18, 19, 20, 21, 22, 23];
            $timeTable['3'] = [0, 3, 6, 9, 12, 15, 18, 21];
            $timeTable['6'] = [0, 6, 12, 18];
            $timeTable['12'] = [0, 12];
            $timeTable['24'] = [9];
        }else{
            $timeTable['1'] = [8, 9, 10, 11, 12, 13, 14, 15, 16, 17, 18, 19, 20, 21];
            $timeTable['3'] = [9, 12, 15, 18, 21];
            $timeTable['6'] = [12, 18];
            $timeTable['12'] = [12];
            $timeTable['24'] = [9];
        }

        //현재 시 구하기
        $hour = date('H');
        $nextHourIdx = 0;
        $nextDay = true;

        //다음 시간 구하기
        foreach($timeTable[$this->kakaoSendDuration] as $k => $v){
            if($v > $hour){
                $nextHourIdx = $k;
                $nextDay = false;
                break;
            }
        }

        $nextHour = $timeTable[$this->kakaoSendDuration][$nextHourIdx];
        $day = date('d');
        if($nextDay == true){
            $day += 1;
        }
        $returnDate = date('Y-m-d H:00:00', mktime($nextHour, 0, 0, date('m'), $day, date('Y')));
        return $returnDate;

    }

    /**
     * 카카오 발송 대상인지 확인
     * @return bool
     */
    public function checkSendTarget(){
        //설정에서 발송 대상인지 확인
        if($this->goodsInfo['type'] == 'stop' && $this->kakaoStopSend == false){
            return false;
        }
        if($this->goodsInfo['type'] == 'request' && $this->kakaoRequestSend == false){
           return false;
        }

        //알림 상품 범위
        if($this->kakaoSendRange == 'scm' || $this->kakaoSendRange == 'headquater'){
            //상품의 공급사 정보 가져오기
            $strWhere = 'goodsNo = ?';
            $this->db->bind_param_push($arrBind['bind'], 's', $this->goodsInfo['goodsNo']);
            $strSQL = "SELECT scmNo FROM " . DB_GOODS . " WHERE " . $strWhere;
            $getData = $this->db->query_fetch($strSQL, $arrBind['bind'], false);

            if($this->kakaoSendRange == 'scm' && $getData['scmNo'] == '1') return false;
            if($this->kakaoSendRange == 'headquater' && $getData['scmNo'] != '1') return false;
        }
        return true;
    }

    /**
     * 카카오 알림톡 발송 함수
     * @return mixed
     */
    public function sendKakao(){
        if(!$this->checkSendTarget()){
            $kakaoAuth['message'] = 'Not a target! Check the setting.';
            return $kakaoAuth;
        }

        //해당 옵션이 이미 알림톡이 발송 되었는지 체크
        $strWhere = 'goodsNo = ? AND optionNo = ? AND deliverySmsSent = "y"';
        $this->db->bind_param_push($arrBind['bind'], 's', $this->goodsInfo['goodsNo']);
        $this->db->bind_param_push($arrBind['bind'], 's', $this->goodsInfo['optNo']);

        $strSQL = "SELECT count(*) cnt FROM " . DB_GOODS_OPTION . " WHERE " . $strWhere;
        $getData = $this->db->query_fetch($strSQL, $arrBind['bind'], false);
        unset($arrBind);
        if($getData['cnt'] > 0){
            //이미 발송된 적이 있음
            $smsAuth['message'] = 'Already Sent Kakao Arlim!';
            return $smsAuth;
        }

        //옵션 확인 요청수량이라면 상품의 옵션 테이블에도 마킹(중복 발송 방지)
        if($this->goodsInfo['type'] == 'request'){
            $arrBind['param'][] = 'deliverySmsSent = ?';
            $this->db->bind_param_push($arrBind['bind'], 's', 'y');
            $this->db->set_update_db(DB_GOODS_OPTION, $arrBind['param'], 'goodsNo = "' . $this->goodsInfo['goodsNo'] . '" AND optionNo = "' . $this->goodsInfo['optNo']  . '"', $arrBind['bind']);
            unset($arrBind);

            $this->goodsInfo['goodsNo'];
        }
        
        //예약 발송 이라면, DB에만 저장
        if($this->kakaoSendDuration > 0){
            return $this->storeStockKakaoAlim();
        }

        //설정에 따라서 메시지 변경
        if($this->goodsInfo['type'] == 'stop'){
            $contents = $this->getStopSellMsg(true);
        }else{
            $contents = $this->getRequestMsg(true);
        }

        foreach($this->kakaoNumbers as $v){
            if(empty($v)) continue;
            $receiver[]['cellPhone'] = $v;
        }

        $oKakao = new \Component\Member\KakaoAlrim;

        $smsUtil = \App::load('Component\\Sms\\SmsUtil');
        $aSender = $smsUtil->getSender();
        $aSmsLog = [
            'sendFl'            => 'kakao',
            'smsType'           => '',
            'smsDetailType'     => '',
            'sendType'          => 'send',
            'subject'           => '카카오 알림톡 도착',
            'contents'          => $contents,
            'receiverCnt'       => count($receiver),
            'replaceCodeType'   => '',
            'sendDt'            => date('Y-m-d H:i:s'),
            'smsSendKey'        => '',
            'smsAutoSendOverFl' => 'none',
            'code'              => $item['code'],
        ];
        $aLogData = $logData;
        // 카카오 알림톡의 경우 알림톡 발송 실패시 sms로 재발송처리 여부를 확인하기 위해 smsFl값을 기본n으로 하여 sms재발송을 처리할경우 y로 변경해 중복발송을 방지한다
        foreach ($receiver as $k => $v) {
            $receiver[$k]['smsFl'] = 'n';
        }
        $receiverForSaveSmsSendList = $receiverData;

        $basicInfo = gd_policy('basic.info');
        $replaceArguments['shopUrl'] = $basicInfo['mallDomain'];

        //$oKakao->sendKakaoAlrim($aSmsLog, $aSender, $aLogData, $receiverForSaveSmsSendList, $this->replaceArguments, $contents);
    }

    /**
     * 예약 카카오 알림톡 발송을 위해 DB에 저장하는 함수
     * @return mixed
     */
    private function storeStockKakaoAlim(){
        $goodsData['alarmType'] = $this->goodsInfo['type'];
        $goodsData['goodsNm'] = $this->goodsInfo['goodsNm'];
        $goodsData['goodsCd'] = $this->goodsInfo['goodsCd'];
        $goodsData['optionNm'] = $this->goodsInfo['optNm'];
        $goodsData['stock'] = $this->goodsInfo['cnt'];
        $goodsData['platform'] = 'kakao';
        $goodsData['sendTime'] = $this->getNextSendTime();
        $goodsData['regDt'] = date('Y-m-d H:i:s');

        $arrBind = $this->db->get_binding(DBTableField::tableGoodsOptionStockAlarm(), $goodsData, 'insert');
        $this->db->set_insert_db(DB_GOODS_OPTION_STOCK_ALARM, $arrBind['param'], $arrBind['bind'], 'y');
        unset($goodsData);
        unset($arrBind);

        $smsAuth['message'] = 'OK';
        return $smsAuth;
    }
}