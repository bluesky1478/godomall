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

namespace Bundle\Component\Sms;

use Component\Database\DBTableField;
use Component\Member\Sleep\SleepService;
use Component\Page\Page;
use Component\Sms\SmsSender;
use Framework\Object\StorageInterface;
use Framework\Utility\ArrayUtils;
use Framework\Utility\ComponentUtils;
use Framework\Utility\DateTimeUtils;
use Framework\Utility\GodoUtils;
use Framework\Utility\NumberUtils;
use Framework\Utility\StringUtils;
use Respect\Validation\Validator;

/**
 * SMS 관리자 관련 처리
 *
 * @author Shin Donggyu <artherot@godo.co.kr>
 */
class SmsAdmin
{
    /** @var \Framework\Database\DBTool $db */
    private $db;
    /** @var  SmsAutoCode $smsAutoCode */
    protected $smsAutoCode;
    protected $recall;
    protected $fieldTypes;

    public function __construct($config = [])
    {
        // 기존 DBTool 파라미터를 보장하기 위한 로직
        if (\get_class($config) === 'DBTool') {
            $this->db = $config;
        } else {
            $this->db = \is_object($config['db']) ? $config['db'] : \App::load('DB');
            $this->smsAutoCode = \is_object($config['smsAutoCode']) ? $config['smsAutoCode'] : new SmsAutoCode();
        }
        // 발신번호
        $this->recall = $this->getConfig('smsCallNum');
        $this->fieldTypes = array_merge(DBTableField::getFieldTypes('tableSmsLog'), DBTableField::getFieldTypes('tableSmsSendList'));

    }

    /**
     * SMS 자동 발송 관련 설정 저장
     *
     * @param array $request 설정값
     */
    public function saveSmsAuto($request)
    {
        // 디비에 저장될 항목을 정리 (insert , update)
        $smsAutoReceiveKind = Sms::SMS_AUTO_RECEIVE_LIST;
        $smsAutoType = [
            'member',
            'admin',
            'provider',
            'board',
        ];
        foreach ($smsAutoReceiveKind as $smsType => $val) {
            foreach ($request[$smsType] as $autoCode => $autoData) {
                foreach ($smsAutoType as $autoType) {
                    if (isset($autoData[$autoType . 'Mode'])) {
                        $dbData[$autoData[$autoType . 'Mode']][$smsType][$autoCode][$autoType]['sno'] = $autoData[$autoType . 'Sno'];
                        $dbData[$autoData[$autoType . 'Mode']][$smsType][$autoCode][$autoType]['subject'] = $autoData['subject'];
                        $dbData[$autoData[$autoType . 'Mode']][$smsType][$autoCode][$autoType]['contents'] = $autoData[$autoType . 'Contents'];
                        //    $dbData[$autoData[$autoType . 'Mode']][$smsType][$autoCode][$autoType]['smsType'] = $smsType;
                        unset($request[$smsType][$autoCode][$autoType . 'Mode']);
                        unset($request[$smsType][$autoCode][$autoType . 'Sno']);
                        unset($request[$smsType][$autoCode]['subject']);
                        unset($request[$smsType][$autoCode][$autoType . 'Contents']);
                    }
                }
            }
        }
        // 디비 정보 저장
        foreach ($dbData as $saveMode => $tmpData1) {
            foreach ($tmpData1 as $smsType => $tmpData2) {
                foreach ($tmpData2 as $smsAutoCode => $tmpData3) {
                    foreach ($tmpData3 as $smsAutoType => $saveData) {
                        $arrBind = [];
                        // 저장 방법
                        if ($saveMode === 'insert') {
                            $saveData['groupNm'] = __('자동발송');
                            $saveData['smsAutoCode'] = $smsAutoCode;
                            $saveData['smsAutoType'] = $smsAutoType;
                            $saveData['smsType'] = $smsType;
                            $arrBind = $this->db->get_binding(DBTableField::tableSmsContents(), $saveData, 'insert');
                            $this->db->set_insert_db(DB_SMS_CONTENTS, $arrBind['param'], $arrBind['bind'], 'y');
                        } elseif ($saveMode === 'update') {
                            $arrBind = $this->db->get_binding(DBTableField::tableSmsContents(), $saveData, 'update', array_keys($saveData));
                            $this->db->bind_param_push($arrBind['bind'], 'i', $saveData['sno']);
                            $this->db->set_update_db(DB_SMS_CONTENTS, $arrBind['param'], 'sno=?', $arrBind['bind']);
                        }
                        unset($arrBind);
                    }
                }
            }
        }

        // SMS 기본 정보 저장
        $config['smsCallNum'] = $request['smsCallNum'];
        $this->setConfig($config);

        // 기 저장된 항목 삭제
        unset($request['mode'], $request['smsCallNum']);

        // SMS 자동발송 관련 내용 저장
        gd_set_policy('sms.smsAuto', $request);
    }

    /**
     * SMS 자동 발송 관련 설정 저장
     *
     * @param string $smsCallNum SMS 자동 발송 번호
     *
     * @return bool
     */
    public function saveCallNum($smsCallNum)
    {
        if (empty($smsCallNum) === true) {
            return false;
        }

        // SMS 기본 정보 저장
        $config['smsCallNum'] = $smsCallNum;
        $this->setConfig($config);

        return true;
    }

    /**
     * SMS 기본 설정 저장
     *
     * @param array $config 설정 배열
     */
    public function setConfig($config)
    {
        gd_set_policy('sms.config', $config);
    }

    /**
     * SMS 기본 설정 중 원하는 값 가지고 오기
     *
     * @param string $key code
     *
     * @return string 원하는 값
     */
    public function getConfig($key)
    {
        $smsConf = gd_policy('sms.config');

        return gd_isset($smsConf[$key]);
    }

    /**
     * SMS 발신번호
     *
     * @return string 발신번호
     */
    public function getSmsCallNum()
    {
        return $this->recall;
    }

    /**
     * SMS 자동 발송 관련 기본 설정 값
     *
     * @return array 설정값
     */
    public function getSmsAutoData()
    {
        $orderBasic = ComponentUtils::getPolicy('order.basic');
        // 기본 SMS 자동발송 코드
        $smsAutoType = $this->smsAutoCode->getCodes();

        // SMS 자동 발송 내용
        $this->db->strField = implode(', ', DBTableField::setTableField('tableSmsContents'));
        //        $this->db->strWhere = 'smsType IN (\'order\', \'member\', \'promotion\', \'board\', \'admin\') AND smsAutoCode IS NOT NULL AND smsAutoType IS NOT NULL';// 2016-08-02 무통장입금 SMS 들어가면서 admin 메뉴 임시로 제거함 다른 부분이 들어갈꺼면 처리하면 됨
        $this->db->strWhere = 'smsType IN (\'order\', \'member\', \'promotion\', \'board\') AND smsAutoCode IS NOT NULL AND smsAutoType IS NOT NULL';
        $this->db->strOrder = 'smsType ASC';

        $query = $this->db->query_complete();

        $strSQL = 'SELECT ' . array_shift($query) . ' FROM ' . DB_SMS_CONTENTS . array_shift($query) . ' ' . implode(' ', $query);
        $getData = gd_htmlspecialchars_stripslashes($this->db->query_fetch($strSQL, null, false));
        foreach ($getData as $data) {
            $setData[$data['smsType']][$data['smsAutoCode']][$data['smsAutoType']]['contents'] = $data['contents'];
            $setData[$data['smsType']][$data['smsAutoCode']][$data['smsAutoType']]['sno'] = $data['sno'];
        }
        unset($getData);
        // SMS 자동발송 설정값 불러오기
        $smsAuto = gd_policy('sms.smsAuto');
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
                // 발송대상 및 SMS 문구설정 데이타
                $arrType = explode('_', $val['sendType']);
                foreach ($arrType as $sendType) {
                    if (isset($setData[$keyType][$val['code']][$sendType]['contents']) === true) {
                        $smsAutoType[$keyType][$key][$sendType . 'Contents'] = $setData[$keyType][$val['code']][$sendType]['contents'];
                        $smsAutoType[$keyType][$key][$sendType . 'Mode'] = 'update';
                        $smsAutoType[$keyType][$key][$sendType . 'Sno'] = $setData[$keyType][$val['code']][$sendType]['sno'];
                    } else {
                        $smsAutoType[$keyType][$key][$sendType . 'Contents'] = $val[$sendType . 'Contents'];
                        $smsAutoType[$keyType][$key][$sendType . 'Mode'] = 'insert';
                        $smsAutoType[$keyType][$key][$sendType . 'Sno'] = '';
                    }
                }

                // 쿠폰만료안내 저장값이 없을때 디폴트값 셋팅
                if ($val['code'] == 'COUPON_WARNING' && !$smsAutoType[$keyType][$key]['smsCouponLimitDate']) {
                    $smsAutoType[$keyType][$key]['smsCouponLimitDate'] = '7';
                }
            }
        }
        if (StringUtils::strIsSet($orderBasic['userHandleFl'], 'n') === 'n') {
            $smsAutoTypeByOrder = [];
            //@formatter:off
            $smsAutoCodeByExchange = [Code::REFUND, Code::BACK, Code::EXCHANGE, Code::ADMIN_APPROVAL, Code::ADMIN_REJECT];
            //@formatter:on
            foreach ($smsAutoType['order'] as $index => $item) {
                if (in_array($item['code'], $smsAutoCodeByExchange)) {
                    continue;
                }
                $smsAutoTypeByOrder[] = $item;
            }
            $smsAutoType['order'] = $smsAutoTypeByOrder;
        }

        // SMS 기본 설정값 불러오기
        $smsAutoType['smsCallNum'] = $this->getSmsCallNum();

        return $smsAutoType;
    }

    /**
     * getSmsContentsList
     *
     * @param string $smsType SMS 전송타입
     *
     * @return mixed
     */
    public function getSmsContentsList($smsType)
    {
        $request = \App::getInstance('request');
        $getValue = $request->get()->toArray();

        // 검색 설정
        $setGetKey = [
            'key',
            'keyword',
            'printDt',
            'smsAutoCode',
            'sort',
            'page',
            'pageNum',
        ];
        $setKeyword = ['contents'];
        $search = [];
        $checked = [];
        $selected = [];

        // 페이지 설정
        if (gd_isset($getValue['pagelink'])) {
            $getValue['page'] = (int) str_replace('page=', '', preg_replace('/^{page=[0-9]+}/', '', gd_isset($getValue['pagelink'])));
        } else {
            $getValue['page'] = 1;
        }

        //검색설정
        $search['sortList'] = [
            'regDt desc' => __('등록일') . ' ↑',
            'regDt asc'  => __('등록일') . ' ↓',
            'modDt desc' => __('수정일') . ' ↑',
            'modDt asc'  => __('수정일') . ' ↓',
        ];

        foreach ($setGetKey as $gVal) {
            if (isset($getValue[$gVal]) === true) {
                $search[$gVal] = $getValue[$gVal];
            } else {
                $search[$gVal] = '';
            }
        }

        $selected['smsAutoCode'][$search['smsAutoCode']] = 'selected="selected"';

        // 스킨 검색
        $arrWhere = [];
        $arrBind = [];

        // 기본 검색
        $arrWhere[] = 'smsType = ?';
        $this->db->bind_param_push($arrBind, 's', $smsType);

        // 키워드 검색
        if ($search['key'] && $search['keyword']) {
            if ($search['key'] === 'all') {
                $tmpWhere = [];
                foreach ($setKeyword as $keyNm) {
                    $tmpWhere[] = '(' . $keyNm . ' LIKE concat(\'%\', ?, \'%\'))';
                    $this->db->bind_param_push($arrBind, 's', $search['keyword']);
                }
                $arrWhere[] = '(' . implode(' OR ', $tmpWhere) . ')';
            } else {
                $arrWhere[] = $search['key'] . " LIKE concat('%',?,'%')";
                $this->db->bind_param_push($arrBind, 's', $search['keyword']);
            }
        }

        // SMS 그룹 검색
        if ($search['smsAutoCode']) {
            $arrWhere[] = 'smsAutoCode = ?';
            $this->db->bind_param_push($arrBind, 's', $search['smsAutoCode']);
        }

        // --- 정렬 설정
        $sort = gd_isset($search['sort'], 'regDt desc');

        // --- 페이지 기본설정
        if (empty($search['page']) === true) {
            $search['page'] = 1;
        }
        if (empty($search['pageNum']) === true) {
            $search['pageNum'] = 10;
        }

        $page = new Page($search['page']);
        $page->page['list'] = $search['pageNum']; // 페이지당 리스트 수

        $this->db->strField = implode(', ', DBTableField::setTableField('tableSmsContents'));
        $this->db->strWhere = implode(' AND ', gd_isset($arrWhere));
        $this->db->strOrder = $sort;
        $this->db->strLimit = $page->recode['start'] . ',' . $search['pageNum'];

        $query = $this->db->query_complete();
        $strSQL = 'SELECT ' . array_shift($query) . ' FROM ' . DB_SMS_CONTENTS . implode(' ', $query);
        $data = $this->db->query_fetch($strSQL, $arrBind);

        // 검색 레코드 수
        unset($query['left'], $query['order'], $query['limit']);
        $page->recode['total'] = $this->db->query_count($query, DB_SMS_CONTENTS, $arrBind);
        $page->setPage();
        unset($arrBind);

        // 각 데이터 배열화
        $getData['data'] = gd_htmlspecialchars_stripslashes($data);
        $getData['page'] = $page;
        $getData['sort'] = $sort;
        $getData['search'] = gd_htmlspecialchars($search);
        $getData['checked'] = $checked;
        $getData['selected'] = $selected;

        return $getData;
    }

    /**
     * getContentsData
     *
     * @param null|string $groupNm
     *
     * @return mixed
     */
    public function getContentsData($groupNm = null)
    {
        $data = $selected = $checked = null;
        if (gd_isset($groupNm)) {
            $this->db->strField = "*";
            $this->db->strJoin = DB_SMS_CONTENTS;
            $this->db->strWhere = "sno=?";

            $query = $this->db->query_complete();

            $strSQL = 'SELECT ' . array_shift($query) . ' FROM ' . array_shift($query) . ' ' . implode(' ', $query);
            $this->db->bind_param_push($arrBind, 's', $groupNm);
            $data = gd_htmlspecialchars_stripslashes($this->db->query_fetch($strSQL, $arrBind, false));

            $selected['group'][gd_isset($data['groupNm'])] = ' selected="selected" ';
            $checked['receiverType'][gd_isset($data['receiverType'])] = ' checked="checked" ';
        } else {
            $checked['receiverType']['a'] = ' checked="checked" ';
        }

        $getData['data'] = gd_isset($data);
        $getData['selected'] = gd_isset($selected);
        $getData['checked'] = $checked;

        unset($data, $selected, $checked);

        return $getData;
    }

    /**
     * SMS 문구 저장
     *
     * @param array $postValue SMS 문구
     *
     * @return bool
     */
    public function saveSmsContentsData(array $postValue)
    {
        if (empty($postValue['smsContentsGroup']) === false) {
            $postValue['smsAutoCode'] = $postValue['smsContentsGroup'];
            unset($postValue['smsContentsGroup']);
        }
        if (empty($postValue['smsContentsText']) === false) {
            $postValue['contents'] = $postValue['smsContentsText'];
            unset($postValue['smsContentsText']);
        }
        if (empty($postValue['smsContentsSubject']) === false) {
            $postValue['subject'] = $postValue['smsContentsSubject'];
            unset($postValue['smsContentsSubject']);
        }
        if (empty($postValue['smsAutoCode'])) {
            return false;
        }
        if (empty($postValue['contents'])) {
            return false;
        }
        $postValue['smsType'] = 'user';
        StringUtils::strIsSet($postValue['subject'], '');
        if (key_exists('sno', $postValue)) {
            $arrBind = $this->db->get_binding(DBTableField::tableSmsContents(), $postValue, 'update');
            $this->db->bind_param_push($arrBind['bind'], 'i', $postValue['sno']);
            $this->db->set_update_db(DB_SMS_CONTENTS, $arrBind['param'], 'sno = ?', $arrBind['bind']);
        } else {
            $arrBind = $this->db->get_binding(DBTableField::tableSmsContents(), $postValue, 'insert');
            $this->db->set_insert_db(DB_SMS_CONTENTS, $arrBind['param'], $arrBind['bind'], 'y');
        }

        return true;
    }

    /**
     * SMS 문구 삭제
     *
     * @param array $snoValue
     *
     * @return bool
     */
    public function deleteSmsContentsData($snoValue)
    {
        if (empty($snoValue) === true || is_array($snoValue) === false) {
            return false;
        }

        foreach ($snoValue as $sno) {
            $arrBind = [];
            $this->db->bind_param_push($arrBind['bind'], 'i', $sno);
            $this->db->set_delete_db(DB_SMS_CONTENTS, 'sno = ? AND isUserBasic=\'n\' AND smsType=\'user\'', $arrBind['bind'], false);
            unset($arrBind);
        }

        return true;
    }

    /**
     * getMemberCountByGroup
     *
     * @return mixed
     * @deprecated 2018-11-20 yjwee 사용이 금지된 함수입니다.
     */
    public function getMemberCountByGroup()
    {
        // 수신 회원
        $strWhere = '(cellPhone != \'\' AND cellPhone IS NOT NULL)';
        $this->db->strField = 'groupSno, COUNT(memNo) AS cnt';
        $this->db->strWhere = $strWhere . ' AND smsFl = \'y\' AND mallSno=' . DEFAULT_MALL_NUMBER;
        $this->db->strGroup = 'groupSno';
        $this->db->strOrder = 'groupSno ASC';

        $query = $this->db->query_complete();
        $strSQL = 'SELECT ' . array_shift($query) . ' FROM ' . DB_MEMBER . ' ' . implode(' ', $query);
        $data = gd_htmlspecialchars_stripslashes($this->db->query_fetch($strSQL, null));
        foreach ($data as $tmpData) {
            foreach ($tmpData as $aKey => $aVal) {
                if ($aKey === 'cnt') {
                    $setData[$tmpData['groupSno']]['agree_cnt'] = $aVal;
                } else {
                    $setData[$tmpData['groupSno']][$aKey] = $aVal;
                }
            }
        }

        // 수신 거부 회원
        $this->db->strField = 'groupSno, COUNT(memNo) AS cnt';
        $this->db->strWhere = $strWhere . ' AND smsFl = \'n\' AND mallSno=' . DEFAULT_MALL_NUMBER;
        $this->db->strGroup = 'groupSno';
        $this->db->strOrder = 'groupSno ASC';

        $query = $this->db->query_complete();
        $strSQL = 'SELECT ' . array_shift($query) . ' FROM ' . DB_MEMBER . ' ' . implode(' ', $query);
        $data = gd_htmlspecialchars_stripslashes($this->db->query_fetch($strSQL, null));
        foreach ($data as $tmpData) {
            foreach ($tmpData as $aKey => $aVal) {
                if ($aKey === 'cnt') {
                    $setData[$tmpData['groupSno']]['reject_cnt'] = $aVal;
                } else {
                    $setData[$tmpData['groupSno']][$aKey] = $aVal;
                }
            }
        }

        return $setData;
    }

    /**
     * 관리자 SMS 개별/전체 발송
     *
     * @param array $receiverData 전송할 데이터
     *
     * @return array
     * @throws \Component\Sms\Exception\PasswordException
     */
    public function sendSms($receiverData)
    {
        $logger = \App::getInstance('logger');
        $transport = \App::load(SmsSender::class);
        if($receiverData['passwordCheckFl'] == 'n') {
            $receiverData['password'] = \App::load(\Component\Sms\SmsUtil::class)->getPassword();
        }
        $transport->validPassword($receiverData['password']);
        $transport->setIsThrowPasswordException(true);

        // 인증번호 오입력 횟수가 존재하는 상태에서 인증번호는 정상 입력하였으면 오입력 횟수 초기화
        $config = ComponentUtils::getPolicy('sms.config');
        $authentication = $config['authentication'];
        if ($authentication['failCnt'] > 0) {
            $config['authentication']['failLog'] = [];
            $config['authentication']['failCnt'] = 0;
            ComponentUtils::setPolicy('sms.config', $config);
        }

        // SMS 발신번호 확인
        $senderPhoneNumber = $this->getConfig('smsCallNum');
        if (empty($senderPhoneNumber) === true) {
            throw new \RuntimeException('SMS ' . __('발신번호를 확인해 주세요.'));
        }

        $session = \App::getInstance('session');
        // 발송자 정보
        $senderInfo = [
            'managerId' => $session->get('manager.managerId'),
            'managerNm' => $session->get('manager.managerNm'),
            'recall'    => $senderPhoneNumber,
        ];

        // 수신거부회원 포함 발송 여부 (y - 수신거부 포함, n - 수신회원만)
        if (empty($receiverData['rejectSend']) === true) {
            $receiverData['rejectSend'] = 'n';
        }

        // 발송 설정 (now - 즉시 발송 , reserve - 예약 발송)
        if (empty($receiverData['smsSendType']) === true) {
            $receiverData['smsSendType'] = 'now';
        }

        // 보내는 내용 체크
        if (empty($receiverData['smsContents']) === true) {
            throw new \RuntimeException('SMS ' . __('발송 내용을 입력해 주세요.'));
        }

        // 보내는 내용 처리 (trim, addslashes 처리는 하지 않음)
        $receiverData['smsContents'] = trim($receiverData['smsContents']);

        // 전송 타입이 sms 인경우 제한 글자수를 넘었는지 체크함
        if ($receiverData['sendFl'] === 'sms') {
            $strLength = gd_str_length($receiverData['smsContents']);
            if ($strLength > Sms::SMS_STRING_LIMIT) {
                throw new \Exception('SMS ' . __('발송 내용이') . ' ' . Sms::SMS_STRING_LIMIT . ' Byte' . __('가 넘었습니다.') . ' (' . $strLength . ') ' . __('개행처리, 공백, 특수문자에 따라서 체크되는 글자수가 다를 수 있으므로 내용을 다시 확인해 주시기 바랍니다.'));
            }
        } elseif ($receiverData['sendFl'] === 'lms') {
            $strLength = gd_str_length($receiverData['smsContents']);
            if ($strLength > Sms::LMS_STRING_LIMIT) {
                throw new \Exception('LMS ' . __('전송은 최대') . ' ' . number_format(Sms::LMS_STRING_LIMIT) . ' Byte ' . __('까지 가능합니다.'));
            }
        }

        // 예약 발송인경우 발송 일자 설정
        if ($receiverData['smsSendType'] === 'reserve') {
            if (empty($receiverData['smsSendReserveDate']) === true) {
                throw new \Exception(__('예약발송 일자를 선택해 주세요.'));
            }

            // 예약 발송 시간을 timestamp 로 변경
            $smsSendReserveDate = strtotime($receiverData['smsSendReserveDate']);

            // 지금 시간과 비교
            if ($smsSendReserveDate <= time()) {
                throw new \Exception(__('예약발송할 시간이 이미 지났습니다. 예약발송 시간을 다시 설정 해 주세요.'));
            }

            // 예약 발송 10분후 체크
            if (strtotime('+10 minute', time()) > $smsSendReserveDate) {
                throw new \Exception(__('예약발송 시간은 현재시간 기준 10분 후부터 가능합니다.'));
            }

            // 예약 일자 (발송시간)
            $smsSendDate = date('Y-m-d H:i:s', $smsSendReserveDate);

            // 발송 타입
            $tranType = 'res_send';
        } else {
            // 발송시간
            $smsSendDate = date('Y-m-d H:i:s', time());

            // 발송 타입
            $tranType = 'send';
        }

        // 로그 데이터
        $logData = [];
        $logData['receiver']['type'] = $receiverData['receiverType'];
        $logData['receiver']['rejectSend'] = $receiverData['rejectSend'];
        $logData['receiver']['smsPoint'] = $receiverData['smsPoint'];
        $logData['receiver']['agreeCnt'] = $receiverData['agreeCnt'];
        if ($receiverData['rejectSend'] === 'n') {
            $logData['receiver']['rejectCnt'] = 0;
        } else {
            $logData['receiver']['rejectCnt'] = $receiverData['rejectCnt'];
        }
        $logData['reserve']['mode'] = $receiverData['smsSendType'];
        $logData['reserve']['date'] = $smsSendDate;

        // 대상 회원 타입에 따른 데이타 세팅
        $receiverMode = 'guest';
        $receivers = [];
        $logger->info(__METHOD__ . ', sendSms receiverType[' . $receiverData['receiverType'] . ']');
        switch ($receiverData['receiverType']) {
            // 개별 발송인 경우
            case 'each':
                $receiverMode = 'member';
                // where 정보
                $arrWhere[] = 'm.memNo IN (' . $receiverData['receiverList'] . ')';
                break;

            // 회원 그룹별 발송인 경우
            case 'group':
                $receiverMode = 'member';
                $groupNumbers = $receiverData['memberGroupNo'];
                $groupNames = $receiverData['memberGroupNoNm'];
                foreach ($groupNumbers as $index => $number) {
                    if (!Validator::intVal()->validate($number)) {
                        throw new \RuntimeException(__('회원등급 번호(%s, %s)가 올바르지 않습니다.', $number, $groupNames[$index]));
                    }
                }
                // where 정보
                $arrWhere[] = 'groupSno IN (\'' . implode('\', \'', $groupNumbers) . '\')';
                // receiver 정보
                $logData['receiver']['group'] = [
                    'groupSno'  => $groupNumbers,
                    'groupName' => $groupNames,
                ];
                break;

            // 전체 회원에게 발송인 경우
            case 'all':
                $receiverMode = 'member';
                // 아래 조건이 기본적으로 전체 회원
                break;

            // 팝업창 모드에서의 발송인 경우
            case 'popup':
                // 정보 추출
                if (\is_array($receiverData['popupData'])) {
                    foreach ($receiverData['popupData'] as $key => $popupData) {
                        $tmp = explode(STR_DIVISION, $popupData);
                        if (empty($tmp[0]) === true) {
                            $receiverMode = 'guest';
                            $receivers[$key]['memNo'] = '0';
                            $receivers[$key]['memNm'] = $tmp[1];
                            $receivers[$key]['smsFl'] = $tmp[2];
                            $receivers[$key]['cellPhone'] = $tmp[3];
                        } else {
                            $receiverMode = 'member';
                            // where 정보
                            $arrWhere[] = 'm.memNo IN (' . $tmp[0] . ')';
                        }
                    }
                } elseif (\is_array($receiverData['receiverSearch']) || \is_array($receiverData['receiverKeys'])) {
                    $searchOrderReceivers = function ($search) use ($logger, $receiverData) {
                        $receivers = [];
                        $service = \App::load('Component\\Order\\OrderAdmin');
                        $search['useStrLimit'] = false;
                        gd_isset($search['view'], 'order');
                        gd_isset($search['searchPeriod'], 7);
                        $isUserHandle = false;
                        if(in_array($search['view'], ['exchange', 'back', 'refund'])) $isUserHandle = true;
                        StringUtils::strIsSet($search['mallFl'], DEFAULT_MALL_NUMBER);
                        if ($search['mallFl'] == 'all') {
                            $search['mallFl'] = DEFAULT_MALL_NUMBER;
                        }
                        if ($search['mallFl'] == DEFAULT_MALL_NUMBER) {
                            $search['mallFl'] = DEFAULT_MALL_NUMBER;
                            $orders = $service->getOrderListForAdmin($search, $search['searchPeriod'], $isUserHandle);
                            unset($orders['data'], $orders['isUserHandle'], $orders['search'], $orders['checked'], $orders['amount']);
                            $memberAdmin = \App::load('\\Component\\Member\\MemberAdmin');
                            foreach ($orders as $order) {
                                if($receiverData['rejectSend'] == 'n') {
                                    if($order['memNo'] > 0) {
                                        $memberSmsFl = $memberAdmin->getMember($order['memNo'], 'memNo', 'smsFl');
                                        if($memberSmsFl['smsFl'] == 'n') continue;
                                    } else {
                                        if($order['smsFl'] == 'n') continue;
                                    }
                                }
                                // sms 개선(부분취소 or 부분환불시 금액 재설정)
                                if(empty($order['orderNo']) === false) {
                                    $ordStatus = substr($order['orderStatus'], 0, 1);
                                    $ordClaimData = $service->getOrderView($order['orderNo'], null, null, null, $ordStatus, null);
                                    $claimCancelPrice = $ordClaimData['dashBoardPrice']['cancelPrice'];
                                    $claimRefundPrice = $ordClaimData['dashBoardPrice']['refundPrice'];
                                }

                                StringUtils::strIsSet($order['orderCellPhone'], '');
                                if ($order['orderCellPhone'] !== '') {
                                    if (in_array($order['settleKind'], ['pv', 'ev', 'fv']) === true && empty($order['pgSettleNm']) === false) {
                                        $order['bankAccount'] = $order['pgSettleNm'];
                                    }
                                    $receivers[] = [
                                        'memNo'       => gd_isset($order['memNo'], 0),
                                        'cellPhone'   => $order['orderCellPhone'],
                                        'replaceCode' => [
                                            'settlePrice'   => NumberUtils::moneyFormat($order['settlePrice']) . NumberUtils::currencyString(),
                                            'bankAccount'   => explode(STR_DIVISION, $order['bankAccount'])[1],
                                            'orderNo'       => $order['orderNo'],
                                            'orderName'     => $order['orderName'],
                                            'bankInfo'      => str_replace(STR_DIVISION, '/', gd_isset($order['bankAccount'])),
                                            'orderDate'     => DateTimeUtils::dateFormat('Y-m-d', $order['regDt']),
                                            'cancelPrice'   => NumberUtils::moneyFormat($claimCancelPrice) . NumberUtils::currencyString(),
                                            'gbRefundPrice' => NumberUtils::moneyFormat($claimRefundPrice) . NumberUtils::currencyString(),
                                            'depositNm'     => $order['bankSender'],
                                        ],
                                    ];
                                }
                            }
                        } else {
                            $logger->info('Sms sending is possible only default mall. search mall flag is ' . $search['mallFl']);
                        }

                        return $receivers;
                    };

                    $getOrderReceivers = function (array $receiverKeys) use ($receiverData) {
                        $service = \App::load('Component\\Order\\Order');
                        $memberAdmin = \App::load('\\Component\\Member\\MemberAdmin');
                        $receivers = [];
                        foreach ($receiverKeys as $key) {
                            $order = $service->getOrderData($key);
                            if($receiverData['rejectSend'] == 'n') {
                                if($order['memNo'] > 0) {
                                    $memberSmsFl = $memberAdmin->getMember($order['memNo'], 'memNo', 'smsFl');
                                    if($memberSmsFl['smsFl'] == 'n') continue;
                                } else {
                                    if($order['smsFl'] == 'n') continue;
                                }
                            }
                            // sms 개선(부분취소 or 부분환불시 금액 재설정)
                            $ordStatus = substr($order['orderStatus'], 0, 1);
                            $orderAdmin = \App::load('Component\\Order\\OrderAdmin');
                            $ordClaimData = $orderAdmin->getOrderView($key, null, null, null, $ordStatus, null);
                            $claimCancelPrice = $ordClaimData['dashBoardPrice']['cancelPrice'];
                            $claimRefundPrice = $ordClaimData['dashBoardPrice']['refundPrice'];

                            if (\is_array($order)) {
                                StringUtils::strIsSet($order['orderCellPhone'], '');
                                if ($order['orderCellPhone'] !== '') {
                                    if (in_array($order['settleKind'], ['pv', 'ev', 'fv']) === true && empty($order['pgSettleNm']) === false) {
                                        $order['bankAccount'] = $order['pgSettleNm'];
                                    }
                                    $receivers[] = [
                                        'memNo'       => gd_isset($order['memNo'], 0),
                                        'cellPhone'   => $order['orderCellPhone'],
                                        'replaceCode' => [
                                            'settlePrice'   => NumberUtils::moneyFormat($order['settlePrice']) . NumberUtils::currencyString(),
                                            'bankAccount'   => explode(STR_DIVISION, $order['bankAccount'])[1],
                                            'orderNo'       => $order['orderNo'],
                                            'orderName'     => $order['orderName'],
                                            'bankInfo'      => str_replace(STR_DIVISION, '/', gd_isset($order['bankAccount'])),
                                            'orderDate'     => DateTimeUtils::dateFormat('Y-m-d', $order['regDt']),
                                            'cancelPrice'   => NumberUtils::moneyFormat($claimCancelPrice) . NumberUtils::currencyString(),
                                            'gbRefundPrice' => NumberUtils::moneyFormat($claimRefundPrice) . NumberUtils::currencyString(),
                                            'depositNm'     => $order['bankSender'],
                                        ],
                                    ];
                                }
                            }
                        }

                        return $receivers;
                    };

                    //상품 재입고 알림 수신자 정보 설정
                    $getReceiversByRestockGoods = function ($receiverData, &$restockUpdateData) {
                        $goods = \App::load('\\Component\\Goods\\GoodsAdmin');

                        $receivers = [];
                        $mobileConfig = gd_policy('mobile.config');
                        $restockGoodsUrl = ($mobileConfig['mobileShopFl'] === 'y') ? URI_MOBILE : URI_HOME;
                        $restockGoodsUrl = $restockGoodsUrl . 'goods/goods_view.php?goodsNo=';

                        if ($receiverData['searchType'] === 'search') {
                            //검색된회원, 전체회원일 경우
                            $restockData = $goods->getGoodsRestockViewList($receiverData['receiverSearch']);
                            foreach ($restockData['data'] as $key => $value) {
                                $restockUpdateData[] = $value;
                                $optionName = $goods->getGoodsRestockOptionDisplay($value);
                                $optionName = str_replace("<br />", ",", $optionName);
                                $restockGoodsShortUrl = GodoUtils::shortUrl($restockGoodsUrl . $value['goodsNo']);

                                if (trim($value['cellPhone']) !== '') {
                                    $receivers[] = [
                                        'cellPhone'   => $value['cellPhone'],
                                        'replaceCode' => [
                                            'restockName'       => $value['name'],
                                            'restockGoodsNm'    => $value['goodsNm'],
                                            'restockOptionName' => $optionName,
                                            'restockGoodsUrl'   => $restockGoodsShortUrl,
                                        ],
                                    ];
                                }
                            }
                        } else {
                            //선택회원 일 경우
                            foreach ($receiverData['receiverKeys'] as $index => $key) {
                                $getData = $restockData = [];
                                $getData = $goods->getGoodsRestockViewList(['sno' => $key]);
                                $restockData = $getData['data'][0];
                                $restockUpdateData[] = $getData['data'][0];
                                if (count($restockData) > 0) {
                                    $optionName = $goods->getGoodsRestockOptionDisplay($restockData);
                                    $optionName = str_replace("<br />", ",", $optionName);
                                    $restockGoodsShortUrl = GodoUtils::shortUrl($restockGoodsUrl . $restockData['goodsNo']);

                                    if (trim($restockData['cellPhone']) !== '') {
                                        $receivers[] = [
                                            'cellPhone'   => $restockData['cellPhone'],
                                            'replaceCode' => [
                                                'restockName'       => $restockData['name'],
                                                'restockGoodsNm'    => $restockData['goodsNm'],
                                                'restockOptionName' => $optionName,
                                                'restockGoodsUrl'   => $restockGoodsShortUrl,
                                            ],
                                        ];
                                    }
                                }
                            }
                        }

                        return $receivers;
                    };
                    switch ($receiverData['opener']) {
                        case 'order':
                            if ($receiverData['searchType'] === 'search') {
                                $receivers = $searchOrderReceivers($receiverData['receiverSearch']);
                            } else {
                                $receivers = $getOrderReceivers($receiverData['receiverKeys']);
                            }
                            break;
                        case 'goods':
                            $restockUpdateData = [];
                            $receivers = $getReceiversByRestockGoods($receiverData, $restockUpdateData);
                            break;
                        case'member':
                            $receiverMode = 'member';
                            $arrWhere[] = 'm.memNo IN (' . implode(',', $receiverData['receiverKeys']) . ')';
                            break;
                        default:
                            $logger->info('sendSms not found popupReceiver opener[' . $receiverData['opener'] . ']');
                            $receivers = [];
                            break;
                    }
                } else {
                    $logger->info('not found popup mode case', $receiverData);
                    $receivers = [];
                }
                break;
            case 'direct':
                $directReceiverNumbers = $receiverData['directReceiverNumbers'];
                if (\is_array($directReceiverNumbers)) {
                    foreach ($directReceiverNumbers as $index => $number) {
                        $formattedNumber = StringUtils::numberToCellPhone($number);
                        if ($formattedNumber === false) {
                            throw new \RuntimeException(__('유효하지 않은 번호입니다. %s', $number));
                        }
                        $receivers[$index]['memNo'] = '0';
                        $receivers[$index]['cellPhone'] = $formattedNumber;
                    }
                }
                break;
            case 'excel':
                $uploadKey = $receiverData['uploadKey'];
                $smsExcelLogService = \App::load('Component\\Sms\\SmsExcelLog');
                $excelReceivers = $smsExcelLogService->getValidationLogByUploadKey($uploadKey);
                foreach ($excelReceivers as $index => $excelReceiver) {
                    $receivers[$index]['memNo'] = '0';
                    $receivers[$index]['cellPhone'] = $excelReceiver['cellPhone'];
                    $receivers[$index]['replaceCode'] = ['name' => $excelReceiver['name']];
                }
                break;
            case 'reSend':
                StringUtils::strIsSet($receiverData['arrSmsSendListSno'], []);
                $logger->info($receiverData['smsLogSno'], $receiverData['arrSmsSendListSno']);
                $componentSmsLog = \App::load('Component\\Sms\\SmsLog');
                $smsLog = $componentSmsLog->getSmsLog('*', $receiverData['smsLogSno']);
                $smsSendList = $componentSmsLog->getSmsSendListBySmsLogSno($receiverData['smsLogSno']);
                $receiverData['replaceCodeGroup'] = $smsLog['replaceCodeType'];
                if (empty($receiverData['arrSmsSendListSno'])) {
                    foreach ($smsSendList as $index => $item) {
                        $receivers[] = [
                            'memNo'       => $item['memNo'],
                            'cellPhone'   => $item['receiverCellPhone'],
                            'replaceCode' => json_decode($item['receiverReplaceCode'], true),
                        ];
                    }
                } else {
                    foreach ($smsSendList as $index => $item) {
                        if (in_array($item['sno'], $receiverData['arrSmsSendListSno'])) {
                            $receivers[] = [
                                'memNo'       => $item['memNo'],
                                'cellPhone'   => $item['receiverCellPhone'],
                                'replaceCode' => json_decode($item['receiverReplaceCode'], true),
                            ];
                        }
                    }
                }
                $logger->info('Re send sms receivers', $receivers);
                break;
        }

        // 대상 회원 타입이 없고, 수신자 데이터가 있는 경우
        if (empty($receivers) === true && gd_isset($receiverData['receivers']) !== null) {
            $receivers = $receiverData['receivers'];
        }

        // 회원인 경우 회원 정보 추출
        if ($receiverMode === 'member') {
            // 회원 모듈
            $memberAdmin = \App::load('\\Component\\Member\\MemberAdmin');

            // where 정보 공통 정보
            $arrWhere[] = '(cellPhone != \'\' AND cellPhone IS NOT NULL)';
            $arrWhere[] = 'm.mallSno = ' . DEFAULT_MALL_NUMBER; // SMS 발송은 기준몰만 가능함.
            if ($receiverData['rejectSend'] === 'n') {
                $arrWhere[] = 'smsFl = \'y\''; // 수신회원만 보내는 경우
            }

            // sort 정보
            $sort = 'm.memNo ASC';

            $receivers = $memberAdmin->getMemberList($arrWhere, $sort);
            foreach ($receivers as $index => $member) {
                $period = '+' . (SleepService::SLEEP_PERIOD * $member['expirationFl']) . ' day';
                $baseDate = StringUtils::strIsSet($member['lastLoginDt'], '0000-00-00 00:00:00') == '0000-00-00 00:00:00' ? $member['entryDt'] : $member['lastLoginDt'];
                $receivers[$index]['sleepScheduleDt'] = DateTimeUtils::dateFormatByParameter('Y-m-d', $baseDate, $period);
                $receivers[$index]['smsAgreementDt'] = DateTimeUtils::dateFormat('Y-m-d', $member['smsAgreementDt']);
                $receivers[$index]['mailAgreementDt'] = DateTimeUtils::dateFormat('Y-m-d', $member['mailAgreementDt']);
                $receivers[$index]['mileage'] = NumberUtils::moneyFormat($member['mileage']);
                $receivers[$index]['deposit'] = NumberUtils::moneyFormat($member['deposit']);
            }
        }
        $countReceivers = \count($receivers);
        $logger->info(__METHOD__ . ', count=' . $countReceivers);
        $logData['reserve']['dbTable'] = $receiverMode;

        // 팝업 모드 발송 시 치환코드 타입이 달라진다.
        StringUtils::strIsSet($receiverData['replaceCodeGroup'], 'member');
        if ($receiverData['receiverType'] === 'excel') {
            $receiverData['replaceCodeGroup'] = $receiverData['receiverType'];
        } elseif ($receiverData['receiverType'] !== 'popup' && $receiverData['receiverType'] !== 'reSend') {
            $receiverData['replaceCodeGroup'] = 'member';
        }
        if ($receiverData['sendFl'] === 'lms') {
            $message = new \Component\Sms\LmsMessage($receiverData['smsContents'], $receiverData['replaceCodeGroup']);
        } else {
            $message = new \Component\Sms\SmsMessage($receiverData['smsContents'], $receiverData['replaceCodeGroup']);
        }

        $transport->setSmsPoint($this->getPoint());
        $transport->setMessage($message);
        $transport->setSmsType('user');
        $transport->setReceiver($receivers);
        $transport->setSender($senderInfo);
        $transport->setSendDate($smsSendDate);
        $transport->setTranType($tranType);
        $transport->setLogData($logData);
        if ($countReceivers > 100) {
            set_time_limit(RUN_TIME_LIMIT);
        }

        //상품 재입고 알림 SMS 발송 후 발송여부 업데이트
        if ($receiverData['receiverType'] === 'popup' &&
            (\is_array($receiverData['receiverSearch']) || \is_array($receiverData['receiverKeys'])) &&
            $receiverData['opener'] === 'goods'
        ) {
            $goods = \App::load('\\Component\\Goods\\GoodsAdmin');
            $goods->updateGoodsRestockSmsSend($restockUpdateData);
        }

        // SMS 발송
        $cnt = $transport->send();

        // excel전송일경우 DB_SMS_EXCEL_LOG 테이블정보 삭제
        if ((int) $cnt['success'] > 0 && $receiverData['receiverType'] === 'excel') {
            $smsExcelLogService->deleteLogByUploadKey($uploadKey);
        }

        return $cnt;
    }

    /**
     * getPoint
     *
     * @return int
     */
    protected function getPoint()
    {
        $sms = \App::load('Component\\Sms\\Sms');

        return $sms->getPoint();
    }

    /**
     * SMS 포인트 충전 금액 가지고 오기
     *
     * @return mixed
     * @throws \Exception
     */
    public function getSmsPriceList()
    {
        // SMS 포인트 충전 금액 데이타 가져오기
        $minUnit = 0;
        $smsPrice = [];
        $out = '';
        try {
            $url = 'https://www.godo.co.kr/userinterface/_godoConn/conf/sms.cfg';
            $uri = parse_url($url);
            $fp = fopen($url, 'r');
            if ($fp) {
                $res = '';
                fwrite($fp, 'GET ' . $uri['path'] . ' HTTP/1.0\r\nHost: ' . $uri['host'] . '\r\n\r\n');
                while (!feof($fp)) {
                    $res .= fread($fp, 1024);
                }
                fclose($fp);

                $arrRes = explode("\r\n\r\n", $res);
                // array_shift($arrRes);
                if (ArrayUtils::isEmpty($arrRes) === false) {
                    $out = implode("", $arrRes);
                }
            }
        } catch (\Exception $e) {
            throw new \Exception('SMS ' . __('포인트 충전 금액 데이터 가져오기가 실패하였습니다.'));
        }

        if (gd_isset($out)) {
            $div = explode(chr(10), $out);
            if (empty($div) === false) {
                foreach ($div as $v) {
                    $div2 = explode('|', $v);

                    $useFee = $div2[1] * 10 / 11;
                    $unit = round($useFee / $div2[0], 1);
                    $bonus = null;
                    if (count($div2) > 2) {
                        $bonus = $div2[2];
                    }

                    $key = $div2[0];

                    if ($bonus) {
                        $key -= $bonus;
                    }

                    $smsPrice[$key] = [
                        'useFee' => $useFee,
                        'unit'   => $unit,
                        'bonus'  => $bonus,
                    ];

                    if ($minUnit == 0 || $minUnit > $unit) {
                        $minUnit = $unit;
                    }
                }
            }
        }

        return gd_isset($smsPrice);
    }

    /**
     * es_smsContents 저장 검증
     *
     * @param StorageInterface $storage  검증 데이터
     *
     * @param bool             $isUpdate 수정 데이터 검증 시 true 로 전달
     *
     * @return bool
     * @throws \Exception
     */
    public function validateSmsContents(StorageInterface $storage, $isUpdate = false)
    {
        $code = \App::load('Component\\Code\\Code');
        $errors = [];
        if ($isUpdate) {
            if (!Validator::intVal()->min(1)->validate($storage->get('sno'))) {
                $errors[] = '일련번호는 필수 값입니다.';
            }
        }
        if (!Validator::intVal()->length(8)->validate($storage->get('smsAutoCode'))) {
            $errors[] = '그룹을 선택하여 주세요.';
        } elseif (!$code->existsCode($storage->get('smsAutoCode'))) {
            $errors[] = '존재하지 않는 그룹입니다.';
        }
        if (!Validator::stringType()->length(1, 10)->validate($storage->get('subject'))) {
            $errors[] = '제목을 입력하여 주세요.';
        }
        if (!Validator::stringType()->length(1)->validate($storage->get('contents'))) {
            $errors[] = '내용을 입력하여 주세요.';
        }
        if (StringUtils::strLength($storage->get('contents')) > 2000) {
            $errors[] = '내용은 최대 2000 bytes 까지 입력 가능합니다.';
        }
        if (count($errors) > 0) {
            throw new \Exception(implode('\r\n', $errors));
        }

        return true;
    }

    /**
     * 자동 발송 SMS 예약시간 리스트
     *
     * @return array
     */
    public function getSmsReservationTime()
    {
        $defaultReservationTime = Sms::SMS_AUTO_RESERVATION_DEFAULT_TIME;
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
