<?php
/**
 * This is commercial software, only users who have purchased a valid license
 * and accept to the terms of the License Agreement can install and use this
 * program.
 *
 * Do not edit or add to this file if you wish to upgrade Enamoo S5 to newer
 * versions in the future.
 *
 * @copyright Copyright (c) 2015 GodoSoft.
 * @link      http://www.godo.co.kr
 */
namespace Bundle\Controller\Api\Statistics;

use Exception;

/**
 * 방문자통계API 갱신 5분
 *
 * @author tomi <tomi@godo.co.kr>
 */
class G5VisitBatchController extends \Controller\Api\Controller
{
    protected $db;
    protected $logger;

    public function index()
    {
        $this->logger = \App::getInstance('logger')->channel('statistics');
        $visitConfig = \App::getConfig('app.statistics-visit-batch')->toArray()['statistics-visit'];
        if ($visitConfig === true) {
            $postValue = \Request::post()->toArray();
            switch($postValue['sendMode']) {
                case 'batch' :
                    try {
                        $resultArr['result'] = true;
                        echo json_encode($resultArr);
                        $this->db = \App::load('DB');
                        $result = $this->saveVisitStatisticsBatchSummary($postValue['visitData'], true);
                        exit;
                    } catch (Exception $e) {
                        $this->json(['result' => false, 'msg' => $e->getMessage()]);
                    }
                    break;
                default :
                    $this->json(['result' => false, 'msg' => 'not process']);
                    break;
            }
        } else {
            $this->logger->INFO("[statistics-visit-batch]", ["batch not use"]);
            $this->json(['result' => false, 'msg' => 'batch not use']);
        }
    }

    /**
     * saveVisitStatisticsBatchSummary
     * today visitStatisticsDay, visitStatisticsHour summary
     * @param $summaryData
     * @return array
     */
    protected function saveVisitStatisticsBatchSummary($summaryData)
    {
        $this->logger->INFO("[statistics-visit-batch]",["START"]);
        $this->setBatchSummaryDataCommon($summaryData);
        $this->logger->INFO("[statistics-visit-batch]",["END"]);
        return ['result'=>true];
    }

    /**
     *
     * common Json foreach
     * saveVisitStatisticsBatchSummary, saveVisitStatisticsRangeSummary
     * @param $summaryData
     */
    protected function setBatchSummaryDataCommon($summaryData)
    {
        $summaryDataArr = json_decode($summaryData, true);
        $return = false;
        try {
            if (empty($summaryDataArr) === false) {
                // 쿼리 구성
                $this->db->strField = '*';
                if (empty($summaryDataArr['hour']) === false) {
                    foreach($summaryDataArr['hour'] as $dbKey => $dbVal) {
                        $arrBind = [];
                        if ($this->db->strWhere) {
                            $this->db->strWhere = $this->db->strWhere . ' AND vh.visitYMD = ? ';
                            $this->db->bind_param_push($arrBind, 'i', $dbVal['visitYMD']);
                        } else {
                            $this->db->strWhere = ' vh.visitYMD = ? ';
                            $this->db->bind_param_push($arrBind, 'i', $dbVal['visitYMD']);
                        }
                        if ($dbVal['mallSno']) {
                            if ($this->db->strWhere) {
                                $this->db->strWhere = $this->db->strWhere . ' AND vh.mallSno = ? ';
                                $this->db->bind_param_push($arrBind, 'i', $dbVal['mallSno']);
                            } else {
                                $this->db->strWhere = ' vh.mallSno = ? ';
                                $this->db->bind_param_push($arrBind, 'i', $dbVal['mallSno']);
                            }
                        }
                        $query = $this->db->query_complete();
                        $strCountSQL = 'SELECT count(visitYMD) as cnt FROM ' . DB_VISIT_HOUR . ' as vh ' . $query['where'];
                        $totalNum = $this->db->slave()->query_fetch($strCountSQL, $arrBind, false)['cnt'];

//                        if ($dbVal[$nowHourData]) {
                        $visitHourJson = [];
                        $updateColumn = [];
                        for ($i = 0; $i < 24; $i++) {
                            if ($dbVal['h'.$i]) {
                                $updateColumn[$i] = '`' . $i . '`=?';
                                $visitHourJson[$i] = $dbVal['h'.$i];
                            }
                        }
                        if ($totalNum > 0 ) {
                            $arrBind = [];
                            $strSQL = "UPDATE " . DB_VISIT_HOUR . " SET " . implode(',', $updateColumn) . ", modDt=now() WHERE `visitYMD`=? AND `mallSno`=?";
                            foreach ($visitHourJson as $val) {
                                $this->db->bind_param_push($arrBind, 's', $val);
                            }
                            $this->db->bind_param_push($arrBind, 'i', $dbVal['visitYMD']);
                            $this->db->bind_param_push($arrBind, 'i', $dbVal['mallSno']);
                            $this->db->bind_query($strSQL, $arrBind);
                            unset($arrBind);
                        } else {
                            $arrBind = [];
                            $strSQL = "INSERT INTO " . DB_VISIT_HOUR . " SET `visitYMD`=?, `mallSno`=?, " . implode(',', $updateColumn) . ", `regDt`=now()";
                            $this->db->bind_param_push($arrBind, 'i', $dbVal['visitYMD']);
                            $this->db->bind_param_push($arrBind, 'i', $dbVal['mallSno']);
                            foreach ($visitHourJson as $val) {
                                $this->db->bind_param_push($arrBind, 's', $val);
                            }
                            $this->db->bind_query($strSQL, $arrBind);
                            unset($arrBind);
                        }
//                        }
                    }
                    $return = true;
                }
                if (empty($summaryDataArr['day']) === false) {
                    foreach($summaryDataArr['day'] as $dbKey => $dbVal) {
                        $arrBind = [];
                        if ($this->db->strWhere) {
                            $this->db->strWhere = $this->db->strWhere . ' AND vd.visitYM = ? ';
                            $this->db->bind_param_push($arrBind, 'i', $dbVal['visitYM']);
                        } else {
                            $this->db->strWhere = ' vd.visitYM = ? ';
                            $this->db->bind_param_push($arrBind, 'i', $dbVal['visitYM']);
                        }
                        if ($dbVal['mallSno']) {
                            if ($this->db->strWhere) {
                                $this->db->strWhere = $this->db->strWhere . ' AND vd.mallSno = ? ';
                                $this->db->bind_param_push($arrBind, 'i', $dbVal['mallSno']);
                            } else {
                                $this->db->strWhere = ' vd.mallSno = ? ';
                                $this->db->bind_param_push($arrBind, 'i', $dbVal['mallSno']);
                            }
                        }
                        $query = $this->db->query_complete();
                        $strCountSQL = 'SELECT count(visitYM) as cnt FROM ' . DB_VISIT_DAY . ' as vd ' . $query['where'];
                        $totalNum = $this->db->slave()->query_fetch($strCountSQL, $arrBind, false)['cnt'];
//                        if ($dbVal[$nowDayData]) {
                        $visitDayJson = [];
                        $updateColumn = [];
                        for ($i = 1; $i < 32; $i++) {
                            if ($dbVal['d'.$i]) {
                                $updateColumn[$i] = '`' . $i . '`=?';
                                $visitDayJson[$i] = $dbVal['d'.$i];
                            }
                        }
                        if ($totalNum > 0) {
                            $arrBind = [];
                            $strSQL = "UPDATE " . DB_VISIT_DAY . " SET " . implode(', ', $updateColumn) .  ", modDt=now() WHERE `visitYM`=? AND `mallSno`=?";
                            foreach ($visitDayJson as $val) {
                                $this->db->bind_param_push($arrBind, 's', $val);
                            }
                            $this->db->bind_param_push($arrBind, 'i', $dbVal['visitYM']);
                            $this->db->bind_param_push($arrBind, 'i', $dbVal['mallSno']);
                            $this->db->bind_query($strSQL, $arrBind);
                            unset($arrBind);
                        } else {
                            $arrBind = [];
                            $strSQL = "INSERT INTO " . DB_VISIT_DAY . " SET `visitYM`=?, `mallSno`=?, " . implode(', ', $updateColumn) . ", `regDt`=now()";
                            $this->db->bind_param_push($arrBind, 'i', $dbVal['visitYM']);
                            $this->db->bind_param_push($arrBind, 'i', $dbVal['mallSno']);
                            foreach ($visitDayJson as $val) {
                                $this->db->bind_param_push($arrBind, 's', $val);
                            }
                            $this->db->bind_query($strSQL, $arrBind);
                            unset($arrBind);
                        }
//                        }
                    }
                    $return = true;
                }
            } else {
                $this->logger->INFO("[statistics-visit db fail]",["summary data null"]);
                throw new \Exception('summary data null');
            }
        } catch (Exception $e) {
            $this->logger->INFO("[statistics-visit db fail]",[$e->getMessage(), $e->getTrace()]);
        }
    }
}


