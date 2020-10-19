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

namespace Bundle\Component\Excel;

use Component\Deposit\Deposit;
use Component\Member\History;
use Component\Member\Manager;
use Component\Member\MemberDAO;
use Component\Validator\Validator;
use Framework\Utility\UrlUtils;
use Globals;
use Request;
use Session;

/**
 * Class ExcelDepositConvert
 * @package Bundle\Component\Excel
 * @author  yjwee <yeongjong.wee@godo.co.kr>
 */
class ExcelDepositConvert extends \Component\Excel\ExcelDataConvert
{
    /** @var  \Bundle\Component\Member\History $historyService */
    private $historyService;
    /** @var  \Bundle\Component\Member\MemberDAO $memberDAO */
    private $memberDAO;
    /** @var  \Bundle\Component\Deposit\Deposit $depositService */
    private $depositService;
    private $excelDeposit;
    private $sheet;
    private $totalRow;
    private $totalColumn;
    private $row = 4;
    private $column = 1;
    private $deposits = [];
    private $mailReceivers = [];
    private $smsReceivers = [];
    private $depositCode;
    private $isSendGuideSms = false;
    private $isSendGuideMail = false;

    public function __construct(MemberDAO $memberDAO = null, History $history = null)
    {
        parent::__construct();
        $this->excelDeposit = new ExcelDeposit();
        $this->depositService = new Deposit();

        if ($memberDAO == null) {
            $memberDAO = new MemberDAO();
        }
        $this->memberDAO = $memberDAO;
        if ($history == null) {
            $history = new History();
        }
        $this->historyService = $history;
        $this->depositCode = gd_code(Deposit::REASON_CODE_GROUP);
    }

    public function downloadSample()
    {
        $excelField = $this->excelDeposit->formatDeposit();
        $arrField = [
            'text',
            'excelKey',
            'comment',
        ];

        $setData = [];
        foreach ($excelField as $key => $val) {
            $setData['fieldCheck'][$key] = $val['dbKey'];
        }

        echo $this->excelHeader;
        echo '<table border="1">' . chr(10);
        for ($i = 0; $i < count($arrField); $i++) {
            echo '<tr>' . chr(10);
            foreach ($excelField as $key => $val) {
                if (in_array($val['dbKey'], $setData['fieldCheck'])) {
                    echo '<td class="title">' . $val[$arrField[$i]] . '</td>' . chr(10);
                }
            }
            echo '</tr>' . chr(10);
        }

        $getData = [];
        foreach ($excelField as $key => $val) {
            $getData[0][$val['dbKey']] = $val['sample'];
        }
        unset($excelField, $arrField);

        echo '<tr>' . chr(10);
        foreach ($getData as $sampleData) {
            foreach ($setData['fieldCheck'] as $fVal) {
                // 회원 번호
                if ($fVal == 'memNo') {
                    $className = 'xl31';
                } else {
                    $className = 'xl24';
                }
                echo '<td class="' . $className . '">' . $sampleData[$fVal] . '</td>' . chr(10);
            }
        }
        echo '</tr>' . chr(10);
        echo '</table>' . chr(10);

        echo $this->excelFooter;
    }


    public function upload()
    {
        if ($this->hasError()) {
            $this->createBodyByError();
            $this->printExcel();

            return false;
        };

        if (!$this->read()) {
            $this->createBodyByReadError();
            $this->printExcel();

            return false;
        }

        if (!$this->hasData()) {
            $this->createDataError();
            $this->printExcel();

            return false;
        }

        $this->excelBody = [];
        $this->_createTableHeader();
        $excelMember = new ExcelDeposit();
        $fields = $excelMember->formatDeposit();
        $this->resetExcelCode($fields);
        $this->_setTableKey();
        $this->_processExcel();
        $this->_processDeposit();
        $this->printExcel();

        return true;
    }

    private function _createTableHeader()
    {
        $this->excelBody[] = '<table border="1">' . chr(10);
        $this->excelBody[] = '<tr>' . chr(10);
        $this->excelBody[] = '<td>' . __('번호') . '</td><td>' . __('아이디') . '</td><td>' . __('지급/차감 예치금') . '</td>' . chr(10);
        $this->excelBody[] = '</tr>' . chr(10);
    }

    private function _setTableKey()
    {
        $sheet = $this->excelReader->sheets[0];
        $numCols = $sheet['numCols'];
        for ($i = 1; $i <= $numCols; $i++) {
            $fieldName = $sheet['cells'][2][$i];
            $this->tableKeys[$i] = $this->fields[$fieldName];
        }
    }

    private function _processExcel()
    {
        $this->sheet = $this->excelReader->sheets[0];
        $this->totalRow = $this->sheet['numRows'];
        $this->totalColumn = $this->sheet['numCols'];

        while ($this->row <= $this->totalRow) {
            $this->column = 1;
            $this->deposits[] = $this->_rowToDeposit();
            $this->row++;
        }
    }

    private function _processDeposit()
    {
        foreach ($this->deposits as $index => $deposit) {
            $member = $this->memberDAO->selectDepositByMemberId($deposit['memId']);
            if (gd_isset($member['memNo'], '') == '') {
                $this->_addBodyError($index, $deposit, __('ID를 찾을 수 없습니다. 휴면회원은 지급/차감 대상이 아닙니다.'));
                continue;
            }
            if (Validator::signDouble($deposit['deposit'], null, null, true) == false) {
                $this->_addBodyError($index, $deposit, __('예치금은 필수 입니다.'));
                continue;
            }
            $maxlength = $deposit['deposit'] < 0 ? 9 : 8;
            if (Validator::maxlen($maxlength, $deposit['deposit'], true) == false) {
                $this->_addBodyError($index, $deposit, __('지급/차감 금액이 최대 입력 자리수(8자리)를 초과하여 처리되지 않았습니다.'));
                continue;
            }

            $insertDeposit = $this->_saveDeposit($member, $deposit);
            $this->_updateMemberDepositWithHistory($insertDeposit);
            $this->_addGuideMailReceiver($member, $insertDeposit);
            $this->_addGuideSmsReceiver($member, $insertDeposit);
            $this->_addBody($index, $deposit);
        }
    }

    private function _saveDeposit(array $member, array $deposit)
    {
        gd_isset($deposit['handleMode'], 'm');
        gd_isset($deposit['reasonCd'], Deposit::REASON_CODE_GROUP . Deposit::REASON_CODE_ETC);
        if (empty($deposit['handleCd'])) {
            $deposit['handleCd'] = null;
        }

        $deposit['memNo'] = $member['memNo'];
        $deposit['beforeDeposit'] = $member['deposit'];
        $deposit['afterDeposit'] = $member['deposit'] + $deposit['deposit'];
        if ($deposit['contents'] == '') {
            $deposit['contents'] = $this->depositCode[$deposit['reasonCd']];
        }
        $deposit['managerId'] = Session::get('manager.managerId');
        $deposit['managerNo'] = Session::get('manager.sno');

        return $this->depositService->saveDeposit($deposit);
    }

    private function _updateMemberDepositWithHistory(array $deposit)
    {
        $updateDeposit = [
            'deposit' => $deposit['afterDeposit'],
            'memNo'   => $deposit['memNo'],
        ];
        $this->memberDAO->updateMember($updateDeposit, ['deposit'], []);

        $manager = Session::get(Manager::SESSION_MANAGER_LOGIN);
        $this->historyService->setAfter(['memNo' => $deposit['memNo']]);
        $this->historyService->setProcessor($manager['managerId']);
        $this->historyService->setManagerNo(Session::get('manager.sno'));
        $this->historyService->setProcessorIp(Request::getRemoteAddress());
        $this->historyService->insertHistory(
            'deposit', [
                $deposit['beforeDeposit'],
                $deposit['afterDeposit'],
            ]
        );
    }

    /**
     * SMS 수신자 추가
     *
     * @param array $member
     * @param array $domain
     */
    private function _addGuideSmsReceiver(array $member, array $domain)
    {
        // 2017-02-13 yjwee 예치금 지급/차감의 경우 회원의 광고정보수신동의 여부와 무관하다.
        $groupInfo = \Component\Member\Group\Util::getGroupName('sno=' . $member['groupSno']);
        $isGuideSms = ($member['cellPhone'] != '') && $this->isSendGuideSms;
        $aBasicInfo = gd_policy('basic.info');
        if ($isGuideSms) {
            $this->smsReceivers[] = [
                'memNo'      => $member['memNo'],
                'cellPhone'  => $member['cellPhone'],
                'name'       => $member['memNm'],
                'rc_deposit' => $domain['deposit'],
                'depositSno' => $domain['sno'],
                'memNm'       => $member['memNm'],
                'memId'       => $member['memId'],
                'mileage'     => $member['mileage'],
                'deposit'     => $member['deposit'],
                'groupNm'     => $groupInfo[$member['groupSno']],
                'rc_mallNm' => Globals::get('gMall.mallNm'),
                'shopUrl' => $aBasicInfo['mallDomain']
            ];
        }
    }

    private function _addGuideMailReceiver(array $member, array $domain)
    {
        $isGuideMail = ($member['email'] != '') && ($member['maillingFl'] == 'y') && $this->isSendGuideMail;
        if ($isGuideMail) {
            $this->mailReceivers[] = [
                'memNo'        => $member['memNo'],
                'email'        => $member['email'],
                'memId'        => $member['memId'],
                'deposit'      => $domain['deposit'],
                'totalDeposit' => $domain['afterDeposit'],
                'depositSno'   => $domain['sno'],
            ];
        }
    }

    private function _addBody($i, array $deposit)
    {
        $this->excelBody[] = '<tr>' . chr(10);
        $this->excelBody[] = '<td>' . ($i + 1) . '</td>' . chr(10);
        $this->excelBody[] = '<td>' . $deposit['memId'] . '</td>' . chr(10);
        $this->excelBody[] = '<td>' . $deposit['deposit'] . '</td>' . chr(10);
        $this->excelBody[] = '</tr>' . chr(10);
    }

    private function _addBodyError($i, array $deposit, $message)
    {
        $this->excelBody[] = '<tr>' . chr(10);
        $this->excelBody[] = '<td>' . ($i + 1) . '</td>' . chr(10);
        $this->excelBody[] = '<td>' . $deposit['memId'] . '</td>' . chr(10);
        $this->excelBody[] = '<td>' . $message . '</td>' . chr(10);
        $this->excelBody[] = '</tr>' . chr(10);
    }

    private function _rowToDeposit()
    {
        $deposit = [];
        while ($this->column <= $this->totalColumn) {
            $depositKey = $this->tableKeys[$this->column];
            $cell = $this->sheet['cells'][$this->row][$this->column];
            $cell = iconv('EUC-KR', 'UTF-8', $cell);
            $deposit[$depositKey] = $cell;
            $this->column++;
        }

        return $deposit;
    }

    public function getSmsReceivers()
    {
        return $this->smsReceivers;
    }

    public function getMailReceivers()
    {
        return $this->mailReceivers;
    }

    /**
     * 엑셀 업로드 시 회원안내 항목 SMS 발송 체크 여부
     *
     * @param $isSendGuideSms
     */
    public function setSendGuideSms($isSendGuideSms)
    {
        $this->isSendGuideSms = $isSendGuideSms;
    }

    /**
     * 엑셀 업로드 시 회원안내 항목 Mail 발송 체크 여부
     *
     * @param $isSendGuideMail
     */
    public function setSendGuideMail($isSendGuideMail)
    {
        $this->isSendGuideMail = $isSendGuideMail;
    }
}
