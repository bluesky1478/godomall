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


use Component\Member\Manager;
use Framework\Utility\DateTimeUtils;
use Framework\Utility\StringUtils;

/**
 * Class Sms080
 * @package Bundle\Component\Sms
 * @author  yjwee <yeongjong.wee@godo.co.kr>
 */
class Sms080
{
    /**
     * 수신거부 리스트 자동 동기화
     *
     * @return int 수신거부 처리된 번호 갯수
     */
    public function syncListByAuto()
    {
        return $this->syncList($this->_getParamsForSync('A'));
    }

    /**
     * 수신거부 리스트 수동 동기화
     *
     * @return int 수신거부 처리된 번호 갯수
     */
    public function syncListByManual()
    {
        return $this->syncList($this->_getParamsForSync('H'));
    }

    /**
     * 수신거부 리스트 동기화
     *
     * @param array $arrParam
     *
     * @return int 수신거부 처리된 번호 갯수
     */
    protected function syncList(array $arrParam)
    {
        $insertCount = 0;
        $logger = \App::getInstance('logger');
        $api = \App::load('Component\\Godo\\GodoCenterServerApi');
        $apiResult = $api->getSms080List($arrParam);
        $apiResult = json_decode($apiResult, true);
        $rejectCount = $apiResult['count'];

        $rejectList = $apiResult['data'];
        $logger->debug(__METHOD__, $rejectList);
        $rejectListData = json_decode($rejectList, true);
        foreach ($rejectListData as $index => $item) {
            if ($this->addSms080($item)) {
                $this->setRejectSmsFl(
                    [
                        'cellPhone' => StringUtils::numberToCellPhone($item['reject_ani']),
                        'regDt'     => $item['reg_time'],
                    ]
                );
                $insertCount++;
            }
        }
        $logger->info(sprintf('Complete insert reject cellphone. insert[%d]/reject[%d]', $insertCount, $rejectCount));

        return $insertCount;
    }

    /**
     * 수신거부 처리
     *
     * @param array $params
     */
    protected function setRejectSmsFl(array $params)
    {
        $cellPhone = $params['cellPhone'];
        $db = \App::getInstance('DB');
        $selectMember = function () use ($db, $cellPhone) {
            $db->strField = 'memNo, email, memNm, maillingFl';
            $db->strWhere = 'cellPhone = ? AND smsFl = \'y\'';
            $db->bind_param_push($arrBind, 's', $cellPhone);
            $query = $db->query_complete(true);
            $strSQL = 'SELECT ' . array_shift($query) . ' FROM ' . DB_MEMBER . implode(' ', $query);
            $resultSet = $db->query_fetch($strSQL, $arrBind);

            return $resultSet;
        };
        $history = \App::load('Component\\Member\\History');
        $dao = \App::load('Component\\Member\\MemberDAO');
        $mailAuto = \App::load('Component\\Mail\\MailMimeAuto');
        $session = \App::getInstance('session');
        $request = \App::getInstance('request');
        $processorIp = $request->getRemoteAddress();
        $manager = $session->get(Manager::SESSION_MANAGER_LOGIN);
        $managerId = $manager['managerId'];
        $managerNo = $manager['sno'];
        StringUtils::strIsSet($managerId, 'admin');
        StringUtils::strIsSet($managerNo, 0);
        $arrMember = $selectMember();
        foreach ($arrMember as $index => $member) {
            $dao->updateMember(
                [
                    'memNo' => $member['memNo'],
                    'smsFl' => 'n',
                ], ['smsFl'], []
            );  // 수신거부로 변경
            $history->setAfter(['memNo' => $member['memNo']]);
            $history->setProcessor($managerId);
            $history->setProcessorIp($processorIp);
            $history->insertHistory(
                'smsFl', [
                    'y',
                    'n',
                    'otherValue' => ['smsFl' => ['sms080' => ['regDt' => $params['regDt']]]],
                ]
            );  // 회원정보 변경 내역 저장
            $mailAuto->init(
                $mailAuto::AGREEMENT, [
                    'email'      => $member['email'],
                    'memNm'      => $member['memNm'],
                    'smsFl'      => 'n',
                    'maillingFl' => $member['maillingFl'],
                    'modDt'      => DateTimeUtils::dateFormat('Y-m-d', 'now'),
                ]
            )->autoSend();  // 수신동의 상태 변경 메일 발송
        }
    }

    /**
     * 수신거부 리스트 추가
     *
     * @param array $item
     *
     * @return bool
     */
    protected function addSms080(array $item)
    {
        $logger = \App::getInstance('logger');
        $dao = \App::load('Component\\Sms\\Sms080DAO');
        $cellPhone = StringUtils::numberToCellPhone($item['reject_ani']);
        if ($cellPhone === false) {
            $logger->info(sprintf('Wrong cellphone [%s]', $cellPhone));
        } elseif ($dao->countList(['keyword' => $cellPhone]) < 1) {
            $insertId = $dao->insert(
                [
                    'rejectCellPhone' => str_replace('-', '', $cellPhone),
                    'rejectDt'        => $item['reg_time'],
                ]
            );
            if ($insertId > 0) {
                return true;
            } else {
                $logger->info(sprintf('Insert fail reject cellphone[%s], regDt[%s]', $cellPhone, $item['reg_time']));
            }
        } else {
            $logger->info(sprintf('Exists cellphone [%s]', $cellPhone));
        }

        return false;
    }

    /**
     * 수신거부 리스트 동기화 요청 파라미터 생성함수
     *
     * @param string $processType
     *
     * @return array
     */
    private function _getParamsForSync($processType)
    {
        $globals = \App::getInstance('globals');
        $arrParam = [
            'mall_id'     => 'GODO' . str_pad($globals->get('gLicense.godosno'), 7, '0', STR_PAD_LEFT),
            'processType' => $processType,
            'domain'      => $globals->get('gMall.mallDomain', ''),
            'start_date'  => $this->_getStartDate(),
            'end_date'    => DateTimeUtils::dateFormat('Y-m-d H:i:s', 'now'),
        ];

        return $arrParam;
    }

    /**
     * 동기화 시작일자
     *
     * @return string
     */
    private function _getStartDate()
    {
        $dao = \App::load('Component\\Sms\\Sms080DAO');
        $list = $dao->selectList(
            [
                'sort'   => 'sno DESC',
                'offset' => '1',
                'limit'  => '1',
            ]
        );
        $startDate = $list[0]['regDt'];
        StringUtils::strIsSet($startDate, '');

        return $startDate;
    }
}
