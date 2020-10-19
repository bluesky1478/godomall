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

use Component\Database\DBTableField;
use Component\Mail\MailUtil;

/**
 * Class 주문/배송 SMS 자동 발송
 * 환불, 반품, 교환, 고객 교환/반품/환불 승인/거절 문자 발송
 *
 * @package Bundle\Component\Sms
 * @author  seong ho yun
 */
class MailStock
{
    protected $db;

    protected $mailSendDuration = '1';      //메일 발송 시간(시간단위, 0이면 실시간)
    protected $mailNight = 'n';          //메일 야간 발송 여부
    protected $mailAddresses;              //메일 발송 번호(배열)
    protected $mailStopSend = false;             //메일 판매중지 발송 여부
    protected $mailRequestSend = false;          //메일 확인요청 발송 여부
    protected $mailSendRange = 'all';            //메일 발송 범위(본사, 공급사 등)

    protected $goodsInfo;               //발송 상품 정보

    /**
     * SmsStock constructor.
     * @param array $config 설정내용
     */
    public function __construct(array $config = [])
    {
        if (!is_object($this->db)) {
            $this->db = \App::load('DB');
        }

        //메일발송 설정 확인(발송 시간)
        $this->mailSendDuration = $config['mailSendDuration'];
        $this->mailNight = $config['mailNight'][0];

        //메일발송 설정 확인(메일 번호)
        for($i=0;$i<3;$i++){
            if(!empty($config['emailLocalPart'][$i]) && !empty($config['emailDomain'][$i])){
                $this->mailAddresses[] = $config['emailLocalPart'][$i].'@'.$config['emailDomain'][$i];
            }
        }

        //메일발송 설정 확인(판매중지 발송, 확인요청 발송)
        if(in_array('stop', $config['mailRange'])) $this->mailStopSend = true;
        if(in_array('request', $config['mailRange'])) $this->mailRequestSend = true;

        //본사상품, 공급사 상품에만 알림 보낼지 설정
        $this->mailSendRange = $config['sendRange'][0];
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
     * 판매중지 메일발송 메시지 생성
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
     * 재고확인 메일발송 메시지 생성
     * @param $direct
     * @return string
     */
    private function getRequestMsg(){

        //판매 중지 알림 설정 모든 항목 가져오기
        $arWhere[] = 'g.stockFl = \'y\'';
        $arWhere[] = 'go.sellStopFl = \'y\'';
        $arWhere[] = 'go.sellStopStock >= go.stockCnt';
        $strWhere = implode(' AND ', $arWhere);
        $field = 'g.goodsNo, g.goodsNm, go.optionValue1, go.optionValue2, go.optionValue3, go.optionValue4, go.optionValue5, go.stockCnt, go.sellStopStock';
        $strSQL = 'SELECT ' . $field . ' FROM ' . DB_GOODS_OPTION . ' as go LEFT JOIN ' . DB_GOODS . ' as g ON g.goodsNo = go.goodsNo WHERE ' . $strWhere;
        $getData = $this->db->query_fetch($strSQL, null, false);
        unset($arWhere);

        $k = 0;
        foreach($getData as $key => $value){
            for($i=1;$i<5;$i++){
                if(empty($value['optionValue'.$i])) break;
                $tmpOptionNm[] = $value['optionValue'.$i];
            }

            $sellStop[$k]['goodsNo'] = $value['goodsNo'];
            $sellStop[$k]['goodsNm'] = $value['goodsNm'];
            $sellStop[$k]['optionNm'] = implode('/', $tmpOptionNm);
            $sellStop[$k]['stockCnt'] = $value['stockCnt'];
            $sellStop[$k]['bifurcation'] = $value['sellStopStock'];
            unset($tmpOptionNm);
            $k++;
        }

        //재고 알림 설정 모든 항목 가져오기
        $arWhere[] = 'g.stockFl = \'y\'';
        $arWhere[] = 'go.confirmRequestFl = \'y\'';
        $arWhere[] = 'go.confirmRequestStock >= go.stockCnt';
        $strWhere = implode(' AND ', $arWhere);
        $field = 'g.goodsNo, g.goodsNm, go.optionValue1, go.optionValue2, go.optionValue3, go.optionValue4, go.optionValue5, go.stockCnt, go.confirmRequestStock';
        $strSQL = 'SELECT ' . $field . ' FROM ' . DB_GOODS_OPTION . ' as go LEFT JOIN ' . DB_GOODS . ' as g ON g.goodsNo = go.goodsNo WHERE ' . $strWhere;
        $getData = $this->db->query_fetch($strSQL, null, false);
        unset($arWhere);

        $k = 0;
        foreach($getData as $key => $value){
            for($i=1;$i<5;$i++){
                if(empty($value['optionValue'.$i])) break;
                $tmpOptionNm[] = $value['optionValue'.$i];
            }

            $sellRequest[$k]['goodsNo'] = $value['goodsNo'];
            $sellRequest[$k]['goodsNm'] = $value['goodsNm'];
            $sellRequest[$k]['optionNm'] = implode('/', $tmpOptionNm);
            $sellRequest[$k]['stockCnt'] = $value['stockCnt'];
            $sellRequest[$k]['bifurcation'] = $value['confirmRequestStock'];
            unset($tmpOptionNm);
            $k++;
        }
        //쇼핑몰 정보 가져오기
        $shopInfo = gd_policy('basic.info');

        $content[] = '<html>';
        $content[] = '	<meta charset="utf-8">';
        $content[] = '	<body>';
        $content[] = '	<div style="width:750px;">';
        $content[] = '		<div style="font-size:20px;font-weight: bold;">상품재고 알림 메일</div>';
        $content[] = '		<div style="font-size:15px; font-weight: bold; color:gray;">메일작성 시점의 현황이므로, 현재 상품 정보와 다를 수 있습니다.</div>';
        $content[] = '		<div>&nbsp;</div>';
        $content[] = '		<div style="font-size:13px; color:gray; margin-bottom:8px;"><img src="http://hoyun-ui.com/admin/gd_share/img/icon_notice_gray.png"> 판매중지 옵션과 확인요청 옵션은 관리자 화면 상품등록(수정)의 옵션/재고 설정에서 확인할 수 있습니다.</div>';
        $content[] = '		<div style="background-color: lightgray; display: inline-block; width:730px; padding:10px;">';
        $content[] = '			<div style="font-size:13px; font-weight: bold; float:left; width:100px;">쇼핑몰 정보</div>';
        $content[] = '			<div style="float:left;">';
        $content[] = '				<div style="font-size:13px; font-weight: bold;"> ·'.$shopInfo['mallNm'].'</div>';
        $content[] = '				<div style="font-size:13px; font-weight: bold;"> ·'.$shopInfo['mallDomain'].'</div>';
        $content[] = '			</div>';
        $content[] = '		</div>';
        $content[] = '		<div>&nbsp;</div>';
        $content[] = '		<div style="font-size:13px; font-weight: bold; clear:both; margin-bottom:5px;">판매중지 옵션 정보 - 총 <span style="color:red">'.count($sellStop).'</span>개</div>';
        $content[] = '		<table width="100%" cellspacing="0" cellpadding="5">';
        $content[] = '			<tr>';
        $content[] = '				<td width="15%;" align="center" style="font-size:13px; font-weight: bold; color:white; background-color:gray; border-top:solid 2px black; border-left: solid lightgrey 1px;">상품코드</td>';
        $content[] = '				<td width="35%;" align="center" style="font-size:13px; font-weight: bold; color:white; background-color:gray; border-top:solid 2px black; border-left: solid lightgrey 1px;">상품명</td>';
        $content[] = '				<td width="40%;" align="center" style="font-size:13px; font-weight: bold; color:white; background-color:gray; border-top:solid 2px black; border-left: solid lightgrey 1px;">옵션</td>';
        $content[] = '				<td width="5%;" align="center" style="font-size:13px; font-weight: bold; color:white; background-color:gray; border-top:solid 2px black; border-left: solid lightgrey 1px;">판매<br />중지<br />수량</td>';
        $content[] = '				<td width="5%;" align="center" style="font-size:13px; font-weight: bold; color:white; background-color:gray; border-top:solid 2px black; border-left: solid lightgrey 1px; border-right: solid lightgrey 1px;">재고</td>';
        $content[] = '			</tr>';
        foreach($sellStop as $key => $value) {
            $content[] = '			<tr>';
            $content[] = '				<td height="25" align="center" style="font-size:13px; border-left: solid lightgrey 1px; border-bottom: solid lightgrey 1px;">'.$value['goodsNo'].'</td>';
            $content[] = '				<td align="left" style="font-size:13px; border-left: solid lightgrey 1px; border-bottom: solid lightgrey 1px;">'.$value['goodsNm'].'</td>';
            $content[] = '				<td align="left" style="font-size:13px; border-left: solid lightgrey 1px; border-bottom: solid lightgrey 1px;">'.$value['optionNm'].'</td>';
            $content[] = '				<td align="center" style="font-size:13px; border-left: solid lightgrey 1px; border-bottom: solid lightgrey 1px;">'.number_format($value['bifurcation']).'</td>';
            $content[] = '				<td align="center" style="font-size:13px; border-left: solid lightgrey 1px; border-bottom: solid lightgrey 1px; border-right: solid lightgrey 1px;">'.number_format($value['stockCnt']).'</td>';
            $content[] = '			</tr>';
        }
        $content[] = '		</table>';
        $content[] = '		<div>&nbsp;</div>';
        $content[] = '		<div style="font-size:13px; font-weight: bold; clear:both; margin-bottom:5px;">확인요청 옵션 정보 - 총 <span style="color:red">'.count($sellRequest).'</span>개</div>';
        $content[] = '		<table width="100%" cellspacing="0" cellpadding="5">';
        $content[] = '			<tr>';
        $content[] = '				<td width="15%;" align="center" style="font-size:13px; font-weight: bold; color:white; background-color:gray; border-top:solid 2px black; border-left: solid lightgrey 1px;">상품코드</td>';
        $content[] = '				<td width="35%;" align="center" style="font-size:13px; font-weight: bold; color:white; background-color:gray; border-top:solid 2px black; border-left: solid lightgrey 1px;">상품명</td>';
        $content[] = '				<td width="40%;" align="center" style="font-size:13px; font-weight: bold; color:white; background-color:gray; border-top:solid 2px black; border-left: solid lightgrey 1px;">옵션</td>';
        $content[] = '				<td width="5%;" align="center" style="font-size:13px; font-weight: bold; color:white; background-color:gray; border-top:solid 2px black; border-left: solid lightgrey 1px;">확인<br />요청<br />수량</td>';
        $content[] = '				<td width="5%;" align="center" style="font-size:13px; font-weight: bold; color:white; background-color:gray; border-top:solid 2px black; border-left: solid lightgrey 1px; border-right: solid lightgrey 1px;">재고</td>';
        $content[] = '			</tr>';
        foreach($sellRequest as $key => $value) {
            $content[] = '			<tr>';
            $content[] = '				<td height="25" align="center" style="font-size:13px; border-left: solid lightgrey 1px; border-bottom: solid lightgrey 1px;">'.$value['goodsNo'].'</td>';
            $content[] = '				<td align="left" style="font-size:13px; border-left: solid lightgrey 1px; border-bottom: solid lightgrey 1px;">'.$value['goodsNm'].'</td>';
            $content[] = '				<td align="left" style="font-size:13px; border-left: solid lightgrey 1px; border-bottom: solid lightgrey 1px;">'.$value['optionNm'].'</td>';
            $content[] = '				<td align="center" style="font-size:13px; border-left: solid lightgrey 1px; border-bottom: solid lightgrey 1px;">'.number_format($value['bifurcation']).'</td>';
            $content[] = '				<td align="center" style="font-size:13px; border-left: solid lightgrey 1px; border-bottom: solid lightgrey 1px; border-right: solid lightgrey 1px;">'.number_format($value['stockCnt']).'</td>';
            $content[] = '			</tr>';
        }
        $content[] = '		</table>';
        $content[] = '		<div>&nbsp;</div>';
        $content[] = '		<div>';
        $content[] = '			<div style="float: left; width:127px; height:48px; "><img src="http://img.godo.co.kr/email/common/logo/godo-logo-x2.png" width="116"></div>';
        $content[] = '			<div style="font-size: 12px; float: left; color:gray;">본 메일은 발신전용이므로 문의사항은 <a href="http://www.godo.co.kr/mygodo/helper_write.html" style="color:#555; text-decoration:underline;" target="_blank">1:1 문의하기</a> 또는 <span style="color:#555;">고객센터 1688-7662</span>를 이용해 주시기 바랍니다.<br />';
        $content[] = '				<span style="color:#000;">엔에이치엔고도(주)</span> 서울특별시 구로구 디지털로32길 30, 13층 (코오롱디지털타워빌란트 1차)<br />';
        $content[] = '				대표 : 최인호 ㅣ 전화 : 1688-7662 ㅣ 사업자등록번호 : 120-86-46911';
        $content[] = '			</div>';
        $content[] = '		</div>';
        $content[] = '	</div>';
        $content[] = '	</body>';
        $content[] = '</html>';

        $content = implode('', $content);
        return $content;
    }

    /**
     * 메일 발송 시간 구하기
     * @return false|string
     */
    private function getNextSendTime(){
        //설정별 발송 시간
        if($this->mailNight == true){
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
        foreach($timeTable[$this->mailSendDuration] as $k => $v){
            if($v > $hour){
                $nextHourIdx = $k;
                $nextDay = false;
                break;
            }
        }

        $nextHour = $timeTable[$this->mailSendDuration][$nextHourIdx];
        $day = date('d');
        if($nextDay == true){
            $day += 1;
        }
        $returnDate = date('Y-m-d H:00:00', mktime($nextHour, 0, 0, date('m'), $day, date('Y')));
        return $returnDate;

    }

    /**
     * 메일 발송 대상인지 확인
     * @return bool
     */
    public function checkSendTarget(){
        //설정에서 발송 대상인지 확인
        if($this->goodsInfo['type'] == 'stop' && $this->mailStopSend == false){
            return false;
        }
        if($this->goodsInfo['type'] == 'request' && $this->mailRequestSend == false){
           return false;
        }

        //알림 상품 범위
        if($this->mailSendRange == 'scm' || $this->mailSendRange == 'headquater'){
            //상품의 공급사 정보 가져오기
            $strWhere = 'goodsNo = ?';
            $this->db->bind_param_push($arrBind['bind'], 's', $this->goodsInfo['goodsNo']);
            $strSQL = "SELECT scmNo FROM " . DB_GOODS . " WHERE " . $strWhere;
            $getData = $this->db->query_fetch($strSQL, $arrBind['bind'], false);

            if($this->mailSendRange == 'scm' && $getData['scmNo'] == '1') return false;
            if($this->mailSendRange == 'headquater' && $getData['scmNo'] != '1') return false;
        }
        return true;
    }

    /**
     * 메일발송 함수
     * @return mixed
     */
    public function sendMail(){
        if(!$this->checkSendTarget()){
            $mailAuth['message'] = 'Not a target! Check the setting.';
            return $mailAuth;
        }

        //해당 옵션이 이미 메일이 발송 되었는지 체크
        $strWhere = 'goodsNo = ? AND optionNo = ? AND deliverySmsSent = "y"';
        $this->db->bind_param_push($arrBind['bind'], 's', $this->goodsInfo['goodsNo']);
        $this->db->bind_param_push($arrBind['bind'], 's', $this->goodsInfo['optNo']);

        $strSQL = "SELECT count(*) cnt FROM " . DB_GOODS_OPTION . " WHERE " . $strWhere;
        $getData = $this->db->query_fetch($strSQL, $arrBind['bind'], false);
        unset($arrBind);
        if($getData['cnt'] > 0){
            //이미 발송된 적이 있음
            $mailAuth['message'] = 'Already Sent Mail!';
            return $mailAuth;
        }

        //옵션 확인 요청수량이라면 상품의 옵션 테이블에도 마킹(중복 발송 방지)
        if($this->goodsInfo['type'] == 'request'){
            $arrBind['param'][] = 'deliverySmsSent = ?';
            $this->db->bind_param_push($arrBind['bind'], 's', 'y');
            $this->db->set_update_db(DB_GOODS_OPTION, $arrBind['param'], 'goodsNo = "' . $this->goodsInfo['goodsNo'] . '" AND optionNo = "' . $this->goodsInfo['optNo']  . '"', $arrBind['bind']);
            unset($arrBind);

            $this->goodsInfo['goodsNo'];
        }

        //예약 발송 DB에 우선 저장
        $this->storeStockMail();

        $contents = $this->getRequestMsg();
        foreach($this->mailAddresses as $v){
            if(empty($v)) continue;
            $receiver[] = $v;
        }

        $mime = new \Mail_mime(['eol' => '']);
        $contents = stripslashes($contents);
        $util = new MailUtil();
        $mime->setHTMLBody($contents, false);

        $body = $mime->get();

        $mime->setContentType('text/html', ['charset' => SET_CHARSET]);
        $mail = &\Mail::factory('mail');
        if ($mail instanceof \PEAR_Error) {
            return false;
        }

        $shopInfo = gd_policy('basic.info');
        foreach($receiver as $key => $value){
            $this->to = $value;
            debug($value);
            $this->header['From'] = $shopInfo['mallNm'].'<'.$shopInfo['email'].'>';
            $this->header['Subject'] = '=?' . SET_CHARSET . '?B?' . base64_encode('상품재고 알림 메일') . '?=';
            $headers = $mime->headers($this->header);
            $result = $mail->send($this->to, $headers, $body);
        }

        return $smsAuth;
    }

    /**
     * 예약 SMS 발송을 위해 DB에 저장하는 함수
     * @return mixed
     */
    private function storeStockMail(){
        $goodsData['alarmType'] = $this->goodsInfo['type'];
        $goodsData['goodsNm'] = $this->goodsInfo['goodsNm'];
        $goodsData['goodsCd'] = $this->goodsInfo['goodsCd'];
        $goodsData['optionNm'] = $this->goodsInfo['optNm'];
        $goodsData['stock'] = $this->goodsInfo['cnt'];
        $goodsData['platform'] = 'mail';
        $goodsData['sendTime'] = $this->getNextSendTime();
        $goodsData['regDt'] = date('Y-m-d H:i:s');

        $arrBind = $this->db->get_binding(DBTableField::tableGoodsOptionStockAlarm(), $goodsData, 'insert');
        $this->db->set_insert_db(DB_GOODS_OPTION_STOCK_ALARM, $arrBind['param'], $arrBind['bind'], 'y');
        unset($goodsData);
        unset($arrBind);

        $mailAuth['message'] = 'OK';
        return $smsAuth;
    }
}