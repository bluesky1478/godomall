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

namespace Bundle\Controller\Admin\Member;

use Framework\Utility\StringUtils;

/**
 * Class SmsSendPsController
 * @package Bundle\Controller\Admin\Member
 * @author  yjwee <yeongjong.wee@godo.co.kr>
 */
class SmsSendPsController extends \Controller\Admin\Controller
{
    public function index()
    {
        $request = \App::getInstance('request');
        $logger = \App::getInstance('logger');
        $logger->info(__METHOD__, $request->post()->all());
        switch ($request->post()->get('mode')) {
            case 'excelResult': // 엑셀 업로드 결과 조회
                try {
                    $service = \App::load('Component\\Sms\\SmsExcelLog');
                    $uploadKey = $request->post()->get('uploadKey');
                    $count = $service->countValidationLogByUploadKey($uploadKey);
                    //@formatter:off
                    $this->json(['result' => 'SUCCESS', 'count' => $count, 'message' => '업로드 결과 조회 완료']);
                    //@formatter:on
                } catch (\Exception $e) {
                    //@formatter:off
                    $this->json(['result' => 'FAIL', 'message' => $e->getMessage()]);
                    //@formatter:on
                }
                break;
            case 'excelUpload': // 엑셀 업로드
                $logger->info(__METHOD__, $request->files()->all());
                set_time_limit(RUN_TIME_LIMIT);
                $excel = \App::load('Component\\Excel\\ExcelSmsConvert');
                try {
                    $excel->upload();
                    $this->json(
                        [
                            'success'   => true,
                            'uploadKey' => $excel->getUploadKey(),
                            'resultUrl' => \App::getInstance('user.path')->data('etc', 'excel_upload_result.xls')->wholeUri(),
                        ]
                    );
                } catch (\Throwable $e) {
                    $logger->error(__METHOD__ . ', ' . $e->getFile() . '[' . $e->getLine() . '], ' . $e->getMessage(), $e->getTrace());
                    $this->json(['success' => false,]);
                }
                break;
            case 'excelSampleDown':     // 엑셀 샘플파일 다운로드
                $this->streamedDownload('SMS샘플파일.xls');
                $excel = \App::load('Component\\Excel\\ExcelSmsConvert');
                $excel->downloadSample();
                exit();
                break;
            case 'countTargetAll': // 전체회원 수 조회
                $db = \App::getInstance('DB');
                $responseParams = $db->query_fetch('SELECT COUNT(*) AS total, COUNT(IF(`smsFl`=\'n\', `smsFl`, NULL)) AS reject FROM ' . DB_MEMBER . ' WHERE mallSno=' . DEFAULT_MALL_NUMBER . ' AND (cellPhone != \'\' AND cellPhone IS NOT NULL)', null, false);
                $this->json($responseParams);
                break;
            case 'countTargetGroup': // 선택된 그룹 회원 수 조회
                $db = \App::getInstance('DB');
                $responseParams = [
                    'total'  => 0,
                    'reject' => 0,
                ];
                $memberGroupNo = $request->post()->get('memberGroupNo');
                if ($memberGroupNo) {
                    $binds = [];
                    $db->strWhere = 'groupSno IN (';
                    foreach ($memberGroupNo as $groupNo) {
                        $db->bind_param_push($binds, 'i', $groupNo);
                        $db->strWhere .= '?,';
                    }
                    $db->strWhere = substr($db->strWhere, 0, strlen($db->strWhere) - 1) . ')';
                    $db->strWhere .= ' AND (cellPhone != \'\' AND cellPhone IS NOT NULL) AND mallSno=' . DEFAULT_MALL_NUMBER;
                    $db->strField = 'COUNT(*) AS total, COUNT(IF(`smsFl`=\'n\', `smsFl`, NULL)) AS reject';
                    $query = $db->query_complete();
                    $strSQL = 'SELECT ' . array_shift($query) . ' FROM ' . DB_MEMBER . ' ' . implode(' ', $query);
                    $responseParams = $db->query_fetch($strSQL, $binds, false);
                }
                $this->json($responseParams);
                break;
            case 'countTargetPopup':
                $responseParams = ['total' => 0,];
                $popupReceiver = $request->post()->get('receiver', []);
                $opener = $popupReceiver['opener'];
                unset($popupReceiver['opener']);
                $countOrder = function ($search) use ($logger) {
                    $logger->info(__METHOD__, $search);
                    $count = 0;
                    $reject = 0;
                    StringUtils::strIsSet($search['mallFl'], DEFAULT_MALL_NUMBER);
                    if ($search['mallFl'] == 'all') {
                        $search['mallFl'] = DEFAULT_MALL_NUMBER;
                    }
                    if ($search['mallFl'] == DEFAULT_MALL_NUMBER) {
                        $service = \App::load('Component\\Order\\OrderAdmin');
                        $member = \App::load('\\Component\\Member\\Member');
                        $search['useStrLimit'] = false;
                        gd_isset($search['view'], 'order');
                        gd_isset($search['searchPeriod'], 7);
                        $isUserHandle = false;
                        if(in_array($search['view'], ['exchange', 'back', 'refund'])) $isUserHandle = true;
                        $orders = $service->getOrderListForAdmin($search, $search['searchPeriod'], $isUserHandle);
                        $logger->info(__METHOD__ . ' search orders count[' . count($orders) . ']');
                        foreach ($orders as $order) {
                            if (is_array($order) === false) {
                                continue;
                            }
                            StringUtils::strIsSet($order['orderCellPhone'], '');
                            if ($order['orderCellPhone'] === '') {
                                continue;
                            }
                            if ($order['memNo'] == 0) {
                                if($order['smsFl'] == 'n') $reject++;
                            } else {
                                $memberSmsFl = $member->getMember($order['memNo'], 'memNo', 'smsFl');
                                if($memberSmsFl['smsFl'] == 'n') $reject++;
                            }
                            $count++;
                        }
                    } else {
                        $logger->info('Sms sending is possible only default mall. search mall flag is ' . $search['mallFl']);
                    }

                    return ['count'=> $count, 'reject'=> $reject];
                };
                //재입고 알림 신청자 수 반환
                $countGoods = function ($dataArray) {
                    $goods = \App::load('\\Component\\Goods\\GoodsAdmin');
                    $restockData = $goods->getGoodsRestockViewList($dataArray);
                    $count = count($restockData['data']);

                    return $count;
                };
                switch ($opener) {
                    case 'order':
                        $tmpCnt = $countOrder($popupReceiver['receiverSearch']);
                        $responseParams['total'] = $tmpCnt['count'];
                        $responseParams['reject'] = $tmpCnt['reject'];
                        break;
                    case 'goods':
                        $responseParams['total'] = $countGoods($popupReceiver['receiverSearch']);
                        break;
                    default:
                        $logger->warning('not found opener.');
                        break;
                }
                $this->json($responseParams);
                break;
            case 'getSmsContentsBox': // sms 발송내역 리스트 조회
                /**
                 * 리스트 조회
                 *
                 * @param integer $current 현재 페이지
                 * @param integer $row     페이지당 조회 건수
                 *
                 * @return array
                 */
                $lists = function ($current, $row) {
                    $db = \App::getInstance('DB');
                    $request = \App::getInstance('request');
                    $binds = [];
                    $fieldTypes = \Component\Database\DBTableField::getFieldTypes(\Component\Database\DBTableField::getFuncName(DB_SMS_CONTENTS));
                    $db->strField = '*';
                    if ($request->post()->get('smsAutoCode', '') == '') {
                        $db->strWhere = 'smsType = \'user\' AND smsAutoCode LIKE CONCAT(\'01007\', \'%\')';
                    } else {
                        $db->strWhere = 'smsType = \'user\' AND smsAutoCode = ?';
                        $db->bind_param_push($binds, $fieldTypes['smsAutoCode'], $request->post()->get('smsAutoCode'));
                    }
                    if ($request->post()->get('searchWord', '') != '') {
                        $db->strWhere .= ' AND (subject LIKE CONCAT(\'%\', ?, \'%\') OR contents LIKE CONCAT(\'%\', ?, \'%\'))';
                        $db->bind_param_push($binds, $fieldTypes['subject'], $request->post()->get('searchWord'));
                        $db->bind_param_push($binds, $fieldTypes['contents'], $request->post()->get('searchWord'));
                    }
                    $db->strLimit = '?, ?';
                    $db->strOrder = 'regDt DESC';
                    $db->bind_param_push($binds, 'i', ($current - 1) * $row);
                    $db->bind_param_push($binds, 'i', $row);
                    $query = $db->query_complete();
                    $strSQL = 'SELECT ' . array_shift($query) . ' FROM ' . DB_SMS_CONTENTS . ' ' . implode(' ', $query);
                    $resultSet = $db->query_fetch($strSQL, $binds);
                    $result = [];
                    $codeService = \App::load('Component\\Code\\Code');
                    $codeGroup = $codeService->getGroupItems('01007');
                    foreach ($resultSet as $index => $item) {
                        StringUtils::strIsSet($item['subject'], $codeGroup[$item['smsAutoCode']] . ' / -');
                        $result[$index] = StringUtils::htmlSpecialCharsStripSlashes($item);
                    }

                    return $result;
                };
                /**
                 * 페이지 처리를 위한 전체 건수 카운트
                 *
                 * @return mixed
                 */
                $amount = function () {
                    $db = \App::getInstance('DB');
                    $request = \App::getInstance('request');
                    $fieldTypes = \Component\Database\DBTableField::getFieldTypes(\Component\Database\DBTableField::getFuncName(DB_SMS_CONTENTS));
                    $binds = [];
                    $db->strField = 'COUNT(*) AS cnt';
                    if ($request->post()->get('smsAutoCode', '') == '') {
                        $db->strWhere = 'smsType = \'user\' AND smsAutoCode LIKE CONCAT(\'01007\', \'%\')';
                    } else {
                        $db->strWhere = 'smsType = \'user\' AND smsAutoCode = ?';
                        $db->bind_param_push($binds, $fieldTypes['smsAutoCode'], $request->post()->get('smsAutoCode'));
                    }
                    $query = $db->query_complete();
                    $strSQL = 'SELECT ' . array_shift($query) . ' FROM ' . DB_SMS_CONTENTS . ' ' . implode(' ', $query);
                    $data = $db->query_fetch($strSQL, $binds, false);

                    return $data['cnt'];
                };
                /**
                 * 페이지 처리를 위한 조회 건수 카운트
                 *
                 * @return mixed
                 */
                $total = function () {
                    $db = \App::getInstance('DB');
                    $request = \App::getInstance('request');
                    $fieldTypes = \Component\Database\DBTableField::getFieldTypes(\Component\Database\DBTableField::getFuncName(DB_SMS_CONTENTS));
                    $binds = [];
                    $db->strField = 'COUNT(*) AS cnt';
                    if ($request->post()->get('smsAutoCode', '') == '') {
                        $db->strWhere = 'smsType = \'user\' AND smsAutoCode LIKE CONCAT(\'01007\', \'%\')';
                    } else {
                        $db->strWhere = 'smsType = \'user\' AND smsAutoCode = ?';
                        $db->bind_param_push($binds, $fieldTypes['smsAutoCode'], $request->post()->get('smsAutoCode'));
                    }
                    if ($request->post()->get('searchWord', '') != '') {
                        $db->strWhere .= ' AND (subject LIKE CONCAT(\'%\', ?, \'%\') OR contents LIKE CONCAT(\'%\', ? , \'%\'))';
                        $db->bind_param_push($binds, $fieldTypes['subject'], $request->post()->get('searchWord'));
                        $db->bind_param_push($binds, $fieldTypes['contents'], $request->post()->get('searchWord'));
                    }
                    $query = $db->query_complete();
                    $strSQL = 'SELECT ' . array_shift($query) . ' FROM ' . DB_SMS_CONTENTS . ' ' . implode(' ', $query);
                    $data = $db->query_fetch($strSQL, $binds, false);

                    return $data['cnt'];
                };
                $page = $request->post()->get('page', 1);
                $pagination = new \Component\Page\Page($page, 0, 0, 3);
                $pagination->setTotal($total());
                $pagination->setAmount($amount());
                $pagination->setPage();
                $pagination->setUrl($request->getQueryString());
                $responseParams = [
                    'lists' => $lists($page, 3),
                    'page'  => $pagination,
                ];
                $this->json($responseParams);
                break;
            case 'saveSmsContents': // 현재 발송내역 저장
                $request = \App::getInstance('request');
                $smsAdmin = \App::load('Component\\Sms\\SmsAdmin');
                try {
                    $isUpdate = $request->post()->has('sno');
                    $smsAdmin->validateSmsContents($request->post(), $isUpdate);
                    if ($smsAdmin->saveSmsContentsData($request->post()->all())) {
                        //@formatter:off
                        $this->json(['result' => 'OK', 'message' => $isUpdate ? '수정되었습니다.' : '저장되었습니다.',]);
                        //@formatter:on
                    } else {
                        //@formatter:off
                        $this->json(['result'=>'FAIL', 'message' => '오류가 발생하였습니다.',]);
                        //@formatter:on
                    };
                } catch (\Exception $e) {
                    $logger->info($e->getMessage(), $request->post()->all());
                    //@formatter:off
                    $this->json(['result'=>'FAIL', 'message' => StringUtils::nl2br($e->getMessage()),]);
                    //@formatter:on
                }
                break;
            case 'deleteSmsContents': // 선택 발송내역 삭제
                $request = \App::getInstance('request');
                $smsAdmin = \App::load('Component\\Sms\\SmsAdmin');
                if ($smsAdmin->deleteSmsContentsData($request->post()->get('sno', []))) {
                    $this->json(
                        [
                            'result'  => 'OK',
                            'message' => '삭제되었습니다.',
                        ]
                    );
                } else {
                    $this->json(
                        [
                            'result'  => 'FAIL',
                            'message' => '오류가 발생하였습니다.',
                        ]
                    );
                };

                break;
            default:
                throw new \Exception(__("요청을 찾을 수 없습니다."));
                break;
        }
    }
}
