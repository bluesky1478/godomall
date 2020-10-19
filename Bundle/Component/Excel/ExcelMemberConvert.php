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

use Component\Member\Member;
use Component\Member\MemberAdmin;
use Component\Member\MemberValidation;
use Component\Member\MemberVO;
use Exception;

/**
 * 회원 엑셀 업로드
 * @package Bundle\Component\Excel
 * @author  yjwee
 */
class ExcelMemberConvert extends \Component\Excel\ExcelDataConvert
{
    /** @var \Bundle\Component\Member\MemberAdmin */
    private $memberAdminService;
    private $hasMemberNoField = false;
    private $isTransaction = true;
    private $memberHandleMode = 'insert';
    private $memberHandleResult = '실패';

    /**
     * @inheritDoc
     */
    public function __construct(MemberAdmin $memberAdminService = null)
    {
        parent::__construct();
        $this->memberAdminService = $memberAdminService;
        if ($memberAdminService === null) {
            $this->memberAdminService = new MemberAdmin();
        }
        $this->memberHandleResult = __('실패');
    }

    /**
     * 회원 업로드 함수
     *
     * @return bool
     */
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
        $this->createTableHeader();
        $excelMember = new ExcelMember();
        $fields = $excelMember->formatMember();
        $this->resetExcelCode($fields);
        $this->setTableKey();
        $this->processExcel();
        $this->printExcel();

        return true;
    }

    /**
     * 업로드 결과 헤더 설정 함수
     */
    public function createTableHeader()
    {
        $this->excelBody[] = '<table border="1">' . chr(10);
        $this->excelBody[] = '<tr>' . chr(10);
        $this->excelBody[] = '<td>' . __('번호') . '</td><td>' . __('회원 번호') . '</td><td>' . __('아이디') . '</td><td>' . __('등록/수정') . '</td>' . chr(10);
        $this->excelBody[] = '</tr>' . chr(10);
    }

    /**
     * 엑셀의 항목과 회원 테이블의 컬럼 매칭 및 회원번호 필드가 존재하는지 체크하는 함수
     */
    public function setTableKey()
    {
        $sheet = $this->excelReader->sheets[0];
        $numCols = $sheet['numCols'];
        for ($i = 1; $i <= $numCols; $i++) {
            $fieldName = $sheet['cells'][2][$i];
            $this->tableKeys[$i] = $this->fields[$fieldName];
            if ($fieldName == 'mem_no') {
                $this->hasMemberNoField = true;
            }
        }
    }

    /**
     * 엑셀의 데이터를 DB에 저장하는 함수
     */
    public function processExcel()
    {
        $sheet = $this->excelReader->sheets[0];
        $numRows = $sheet['numRows'];
        for ($i = 4; $i <= $numRows; $i++) {
            \Session::del(Member::SESSION_MODIFY_MEMBER_INFO);
            $isMemberNoEach = true;
            $memberData = $linkData = $infoData = $optionData = $iconData = $addNameData = $addValueData = $textData = $imageData = [];
            $numCols = $sheet['numCols'];

            for ($j = 1; $j <= $numCols; $j++) {
                $tableKey = $this->tableKeys[$j];
                if ($this->dbNames[$tableKey] == 'member') {
                    $memberData[$tableKey] = iconv('EUC-KR', 'UTF-8', gd_isset($sheet['cells'][$i][$j]));

                    if ($tableKey == 'memPwEnc' && $memberData['memPw'] == '' && $memberData[$tableKey] != '') {
                        $this->memberAdminService->setIsExcelUpload(true);
                        $memberData['memPw'] = $memberData['memPwEnc'];
                    }
                    if ($memberData[$tableKey] == '') {
                        unset($memberData[$tableKey]);
                    }
                }
            }

            $beforeMember = [];
            $existMember = false;
            if (!empty($memberData['memNo'])) {
                $beforeMember = $this->db->getData(DB_MEMBER, $memberData['memNo'], 'memNo');
                if (gd_isset($memberData['memPw'], '') == '') {
                    unset($beforeMember['memPw']);
                }
                $existMember = $beforeMember['memNo'] == $memberData['memNo'];
            }
            if ((empty($memberData['memNo']) === true || $existMember === false)) {
                $isMemberNoEach = false;
            } else if ($existMember) {
                $this->memberHandleMode = 'update';
                if (!empty($memberData['memId']) && $beforeMember['memId'] != $memberData['memId']) {
                    $this->addBodyByErrors($i, $memberData, [__('기존 회원의 아이디는 수정할 수 없습니다')]);
                    continue;
                }
                \Session::set(Member::SESSION_MODIFY_MEMBER_INFO, $beforeMember);
                $memberData = array_merge($beforeMember, $memberData);
            }

            \DB::begin_tran();
            if (!$isMemberNoEach) {
                $requireErrors = $this->getRequireErrors($memberData);
                if (count($requireErrors) > 0) {
                    \DB::rollback();
                    $this->addBodyByErrors($i, $memberData, $requireErrors);
                    continue;
                }
            }

            $overlapErrors = $this->getOverlapErrors($memberData);
            if (count($overlapErrors) > 0) {
                \DB::rollback();
                $this->addBodyByErrors($i, $memberData, $overlapErrors);
                continue;
            }

            $memberValidation = new MemberValidation();
            if ($memberValidation->isUnableId($memberData['memId'])) {
                \DB::rollback();
                $this->addBodyByErrors($i, $memberData, [__('가입불가 회원 아이디')]);
                continue;
            }

            try {
                if ($isMemberNoEach === false || $this->hasMemberNoField === false) {
                    $memberVO = new MemberVO($memberData);
                    $memberNo = $this->memberAdminService->register($memberVO);
                    $memberData['memNo'] = $memberNo;
                    $this->memberAdminService->applyExcelCoupon('excel', 0, $memberData['groupSno'], $memberData);
                    $this->memberHandleResult = __('등록');
                    $this->addBodyByRegister($i, $memberData);
                } else {
                    $this->memberAdminService->modifyMemberData($memberData, 'excel');
                    $this->memberHandleMode = 'update';
                    $this->memberHandleResult = __('수정');
                    $this->addBodyByModify($i, $memberData);
                }
                \DB::commit();
            } catch (Exception $e) {
                \DB::rollback();
                $this->addBodyByErrors($i, $memberData, [$e->getMessage()]);
                continue;
            }
        }
        $this->excelBody[] = '</table>' . chr(10);
    }

    /**
     * 회원 정보 저장 시 중복데이터 체크 후 오류 메시지 추가 함수
     *
     * @param array $member
     *
     * @return array
     */
    public function getOverlapErrors(array $member)
    {
        $overlapMembers = $this->getMemberByOverlap($member);
        $errors = [];
        if (count($overlapMembers) > 0) {
            foreach ($overlapMembers as $overlapMember) {
                foreach ($overlapMember as $index => $item) {
                    if ($item != '' && $item == $member[$index]) {
                        $errors[] = $this->fieldTexts[$index] . __(' 중복');
                    }
                }
            }
        }

        return $errors;
    }

    /**
     * 업로드 결과에 오류 내용을 추가하는 함수
     *
     * @param       $i
     * @param array $memberData
     * @param array $errors
     */
    public function addBodyByErrors($i, array $memberData, array $errors)
    {
        $this->excelBody[] = '<tr>' . chr(10);
        $this->excelBody[] = '<td>' . ($i - 3) . '</td>' . chr(10);
        $this->excelBody[] = '<td>' . $memberData['memNo'] . '</td>' . chr(10);
        $this->excelBody[] = '<td>' . $memberData['memId'] . '</td>' . chr(10);
        $this->excelBody[] = '<td>' . $this->memberHandleMode . ' (' . $this->memberHandleResult . ') ' . implode(chr(10), $errors) . '</td>' . chr(10);
        $this->excelBody[] = '</tr>' . chr(10);
    }

    /**
     * 업로드 시 필수 데이터 체크 후 오류 메시지 반환 함수
     *
     * @param array $member
     *
     * @return array
     */
    public function getRequireErrors(array $member)
    {
        $errors = [];
        if (gd_isset($member['memId'], '') == '') {
            $errors[] = $this->fieldTexts['memId'] . __(' 값은 필수입니다.');
        }
        if (gd_isset($member['memPw'], '') == '' && gd_isset($member['memPwEnc'], '') == '') {
            $errors[] = $this->fieldTexts['memPw'] . __(' 값은 필수입니다.');
        }
        if (gd_isset($member['memNm'], '') == '') {
            $errors[] = $this->fieldTexts['memNm'] . __(' 값은 필수입니다.');
        }

        return $errors;
    }

    /**
     * 업로드 결과에 회원등록 메시지 추가 함수
     *
     * @param       $i
     * @param array $memberData
     */
    public function addBodyByRegister($i, array $memberData)
    {
        $this->excelBody[] = '<tr>' . chr(10);
        $this->excelBody[] = '<td>' . ($i - 3) . '</td>' . chr(10);
        $this->excelBody[] = '<td>' . $memberData['memNo'] . '</td>' . chr(10);
        $this->excelBody[] = '<td>' . $memberData['memId'] . '</td>' . chr(10);
        $this->excelBody[] = '<td>' . $this->memberHandleMode . ' (' . $this->memberHandleResult . ')</td>' . chr(10);
        $this->excelBody[] = '</tr>' . chr(10);
    }

    /**
     * 업로드 결과에 회원수정 메시지 추가 함수
     *
     * @param       $i
     * @param array $memberData
     */
    public function addBodyByModify($i, array $memberData)
    {
        $this->excelBody[] = '<tr>' . chr(10);
        $this->excelBody[] = '<td>' . ($i - 3) . '</td>' . chr(10);
        $this->excelBody[] = '<td>' . $memberData['memNo'] . '</td>' . chr(10);
        $this->excelBody[] = '<td>' . $memberData['memId'] . '</td>' . chr(10);
        $this->excelBody[] = '<td>' . $this->memberHandleMode . ' (' . $this->memberHandleResult . ')</td>' . chr(10);
        $this->excelBody[] = '</tr>' . chr(10);
    }

    /**
     * @param boolean $isTransaction
     */
    public function setIsTransaction($isTransaction)
    {
        $this->isTransaction = $isTransaction;
    }

    /**
     * 업로드 정보 중복 데이터를 테이블을 통해 체크하는 함수
     *
     * @param array $member
     *
     * @return array|object
     */
    public function getMemberByOverlap(array $member)
    {
        $binders = $wheres = [];
        $this->db->query_reset();
        $this->db->strField = 'memId, nickNm, email';
        $wheres[] = 'memId=?';
        $this->db->bind_param_push($binders, 's', $member['memId']);
        if (gd_isset($member['nickNm'], '') != '') {
            $wheres[] = 'nickNm=?';
            $this->db->bind_param_push($binders, 's', $member['nickNm']);
        }
        if (gd_isset($member['email'], '') != '') {
            $wheres[] = 'email=?';
            $this->db->bind_param_push($binders, 's', $member['email']);
        }
        $this->db->strWhere = implode(' OR ', $wheres);
        if ($member['memNo'] > 0) {
            $this->db->strWhere = '(' . $this->db->strWhere . ') AND memNo!=?';
            $this->db->bind_param_push($binders, 's', $member['memNo']);
        }

        $query = $this->db->query_complete();
        $strSQL = 'SELECT ' . array_shift($query) . ' FROM ' . DB_MEMBER . implode(' ', $query);

        $data = $this->db->query_fetch($strSQL, $binders);

        unset($arrBind, $where, $strSQL);

        return $data;
    }
}
