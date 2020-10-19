<?php
/**
 * OutSideScriptAdmin Class
 *
 * @author    su
 * @version   1.0
 * @since     1.0
 * @copyright ⓒ 2016, NHN godo: Corp.
 */

namespace Bundle\Component\OutSideScript;

use Component\Mall\Mall;
use Component\Database\DBTableField;
use Component\Validator\Validator;

/**
 * Class OutSideScriptAdmin
 * @package Bundle\Component\OutSideScript
 * @author  Seung-gak Kim <surlira@godo.co.kr>
 */
class OutSideScriptAdmin
{
    // 디비 접속
    /** @var \Framework\Database\DBTool $db */
    protected $db;

    /**
     * @var array arrBind
     */
    protected $arrBind = [];
    protected $arrWhere = [];
    protected $checked = [];
    protected $selected = [];
    protected $search = [];
    protected $fieldTypes;


    /**
     * 생성자
     *
     * @author su
     */
    public function __construct()
    {
        if (!is_object($this->db)) {
            $this->db = \App::load('DB');
        }
        $this->fieldTypes['outSideScript'] = DBTableField::getFieldTypes('tableOutSideScript');
    }

    /**
     * getOutSideScript
     * 관리자에서 등록한 외부 스크립트 불러오기
     * outSideScriptNo = 고유번호로 불러오기
     * outSideScriptUse = 사용여부로 불러오기
     * outSideScriptUseHeader = header 스크립트 사용여부로 불러오기
     * outSideScriptUseFooter = footer 스크립트 사용여부로 불러오기
     * outSideScriptUsePage = page 스크립트 사용여부로 불러오기
     *
     * @param $outSideScriptArrData
     *
     * @return mixed
     */
    public function getOutSideScript($outSideScriptArrData, $mode = 'list', $mallSno = DEFAULT_MALL_NUMBER)
    {
        $mall = \App::load('Component\\Mall\\Mall');
        $arrBind = [];
        if ($outSideScriptArrData['outSideScriptNo'] > 0) {
            $arrWhere[] = "outSideScriptNo = ?";
            $this->db->bind_param_push($arrBind, $this->fieldTypes['outSideScript']['outSideScriptNo'], $outSideScriptArrData['outSideScriptNo']);
        }
        if (empty($outSideScriptArrData['outSideScriptUse']) === false) {
            $arrWhere[] = "outSideScriptUse = ?";
            $this->db->bind_param_push($arrBind, $this->fieldTypes['outSideScript']['outSideScriptUse'], $outSideScriptArrData['outSideScriptUse']);
        }
        if (empty($outSideScriptArrData['outSideScriptUseHeader']) === false) {
            $arrWhere[] = "outSideScriptUseHeader = ?";
            $this->db->bind_param_push($arrBind, $this->fieldTypes['outSideScript']['outSideScriptUseHeader'], $outSideScriptArrData['outSideScriptUseHeader']);
        }
        if (empty($outSideScriptArrData['outSideScriptUseFooter']) === false) {
            $arrWhere[] = "outSideScriptUseFooter = ?";
            $this->db->bind_param_push($arrBind, $this->fieldTypes['outSideScript']['outSideScriptUseFooter'], $outSideScriptArrData['outSideScriptUseFooter']);
        }
        if (empty($outSideScriptArrData['outSideScriptUsePage']) === false) {
            $arrWhere[] = "outSideScriptUsePage = ?";
            $this->db->bind_param_push($arrBind, $this->fieldTypes['outSideScript']['outSideScriptUsePage'], $outSideScriptArrData['outSideScriptUsePage']);
        }

        // 테이블명 반환
        $tableName = DB_OUT_SIDE_SCRIPT;
        if ($mode != 'list' && $mallSno > DEFAULT_MALL_NUMBER) {
            $tableName = $mall->getTableName(DB_OUT_SIDE_SCRIPT, $mallSno);
            $arrWhere[] = "mallSno = ?";
            $this->db->bind_param_push($arrBind, 'i', $mallSno);
        }

        $this->db->strWhere = implode(' AND ', $arrWhere);

        $query = $this->db->query_complete();
        $strSQL = 'SELECT ' . array_shift($query) . ' FROM ' . $tableName . ' ' . implode(' ', $query);
        $getData = $this->db->query_fetch($strSQL, $arrBind);

        $globals = \App::getInstance('globals');
        $mallList = $globals->get('gGlobal.useMallList');
        if (count($mallList) < 1) {
            $mallList = $mall->getListByUseMall();
        }
        if ($mode == 'list' && $mall->isUsableMall() === true) {
            $useMallKey = array_keys($mallList);

            $arrWhere[] = "mallSno IN (" . @implode(',', array_fill(0, count($useMallKey), '?')) . ")";
            foreach ($useMallKey as $mallKey) {
                $this->db->bind_param_push($arrBind, 'i', $mallKey);
            }

            // 테이블명 반환
            $listTableName = $mall->getTableName(DB_OUT_SIDE_SCRIPT, '', true);
            $this->db->strWhere = implode(' AND ', $arrWhere);

            $query = $this->db->query_complete();
            $strSQL = 'SELECT ' . array_shift($query) . ' FROM ' . $listTableName . ' ' . implode(' ', $query);
            $getData = array_merge($getData, $this->db->query_fetch($strSQL, $arrBind));
        }

        if (count($getData) > 0) {
            foreach ($getData as $key => $val) {
                $pageScriptJsonData = $val['outSideScriptPage'];
                $returnData[$key] = gd_htmlspecialchars_stripslashes($getData[$key]);
                $returnData[$key]['outSideScriptPage'] = $pageScriptJsonData;
            }
        } else {
            $returnData = [];
        }
        return $returnData;
    }

    /**
     * getUserOutSideScript
     * Front / Mobile 의 외부 스크립트 적용
     * interceptor의 OutSideScript.php 에서 사용
     *
     * @param null|integer $managerNo    관리자 로그인 세션의 관리자 고유번호
     * @param null|string  $thisPage     controller->getPageName()
     * @param bool         $mobileDevice request::isMobile()
     *
     * @return array
     */
    public function getUserOutSideScript($managerNo = null, $thisPage = null, $mobileDevice = false)
    {
        $session = \App::getInstance('session');
        if ($thisPage !== null) {
            $thisPage = str_replace('.php', '', $thisPage);
        }
        $addHeader = '';
        $addFooter = '';

        $mallCfg = $session->get(SESSION_GLOBAL_MALL);
        $mallSno = gd_isset($mallCfg['sno'], 1);

        $paramData['outSideScriptUse'] = 'y';
        $getUseArrData = $this->getOutSideScript($paramData, 'use', $mallSno);

        // User 외부 스크립트 (테스트모드 - 관리자 로그인 시만 작동)
        $getTestArrData = [];
        if ($managerNo > 0) {
            $paramData['outSideScriptUse'] = 't';
            $getTestArrData = $this->getOutSideScript($paramData);
        }
        if (count($getTestArrData) > 0) {
            foreach ($getTestArrData as $testKey => $testVal) {
                array_push($getUseArrData, $getTestArrData[$testKey]);
            }
        }

        if (count($getUseArrData) > 0) {
            foreach ($getUseArrData as $getKey => $getVal) {
                if ($getVal['outSideScriptUseHeader'] == 'y') {
                    if ($mobileDevice) {
                        if ($getVal['outSideScriptHeaderMobile']) {
                            $addHeader .= $getVal['outSideScriptHeaderMobile'];
                        }
                    } else {
                        if ($getVal['outSideScriptHeaderPC']) {
                            $addHeader .= $getVal['outSideScriptHeaderPC'];
                        }
                    }
                }
                if ($getVal['outSideScriptUsePage'] == 'y') {
                    $outSideScriptPage = json_decode($getVal['outSideScriptPage'], true);
                    foreach ($outSideScriptPage as $pageKey => $pageVal) {
                        $replacePageValUrl = str_replace('.php', '', $pageVal['Url']);
                        if ($replacePageValUrl == $thisPage) {
                            if ($mobileDevice) {
                                if ($pageVal['Mobile']) {
                                    $addFooter .= $pageVal['Mobile'];
                                }
                            } else {
                                if ($pageVal['PC']) {
                                    $addFooter .= $pageVal['PC'];
                                }
                            }
                        }
                    }
                }
                if ($getVal['outSideScriptUseFooter'] == 'y') {
                    if ($mobileDevice) {
                        if ($getVal['outSideScriptFooterMobile']) {
                            $addFooter .= $getVal['outSideScriptFooterMobile'];
                        }
                    } else {
                        if ($getVal['outSideScriptFooterPC']) {
                            $addFooter .= $getVal['outSideScriptFooterPC'];
                        }
                    }
                }
            }
        }
        $addScript['addHeader'] = $addHeader;
        $addScript['addFooter'] = $addFooter;

        return $addScript;
    }

    /**
     * setOutSideScript
     * 관리자 외부 스크립트 저장/수정
     *
     * @param $arrData
     *
     * @return int|string
     * @throws \Exception
     */
    public function setOutSideScript($arrData)
    {
        if ($arrData['outSideScriptUseHeader'] == 'y') {
            if (empty(trim($arrData['outSideScriptHeaderPC'])) === true && empty(trim($arrData['outSideScriptHeaderMobile'])) === true) {
                throw new \Exception(__('상단 공통영역 스크립트를 입력하셔야 합니다.'));
            }
        }
        if ($arrData['outSideScriptUseFooter'] == 'y') {
            if (empty(trim($arrData['outSideScriptFooterPC'])) === true && empty(trim($arrData['outSideScriptFooterMobile'])) === true) {
                throw new \Exception(__('하단 공통영역 스크립트를 입력하셔야 합니다.'));
            }
        }
        if ($arrData['outSideScriptUsePage'] == 'y') {
            if (empty(trim($arrData['outSideScriptPage'][1]['PC'])) === true && empty(trim($arrData['outSideScriptPage'][1]['Mobile'])) === true) {
                throw new \Exception(__('선택 페이지 내 스크립트를 입력하셔야 합니다.'));
            }
        }
        if ($arrData['outSideScriptUseHeader'] != 'y') {
            $arrData['outSideScriptUseHeader'] = 'n';
        }
        if ($arrData['outSideScriptUseFooter'] != 'y') {
            $arrData['outSideScriptUseFooter'] = 'n';
        }
        if ($arrData['outSideScriptUsePage'] != 'y') {
            $arrData['outSideScriptUsePage'] = 'n';
        }

        $arrData['outSideScriptPage'] = json_encode($arrData['outSideScriptPage'], JSON_UNESCAPED_UNICODE);
        $validator = new Validator();
        $validator->add('mode', 'alpha', true); // 모드
        $validator->add('outSideScriptServiceName', '', true); // 서비스명
        $validator->add('outSideScriptUse', '', true); // 사용여부
        $validator->add('outSideScriptUseHeader', 'yn', true); // header 사용여부
        $validator->add('outSideScriptUseFooter', 'yn', true); // footer 사용여부
        $validator->add('outSideScriptUsePage', 'yn', true); // page 사용여부
        $validator->add('outSideScriptHeaderPC', '', ''); // header PC 스크립트
        $validator->add('outSideScriptHeaderMobile', '', ''); // header mobile 스크립트
        $validator->add('outSideScriptFooterPC', '', ''); // footer PC 스크립트
        $validator->add('outSideScriptFooterMobile', '', ''); // footer mobile 스크립트
        $validator->add('outSideScriptPage', '', ''); // page 스크립트 정보 json
        $validator->add('mallSno', '', true); // 상점번호
        $validator->add('managerNo', '', true); // 등록/수정 관리자고유번호
        $validator->add('managerNm', '', true); // 등록/수정 관리자 이름
        $validator->add('managerId', '', true); // 등록/수정 관리자 아이디
        if ($arrData['mode'] == 'modify') {
            $validator->add('outSideScriptNo', '', true); // 고유번호
        }

        if ($validator->act($arrData, true) === false) {
            throw new \Exception(implode("<br/>", $validator->errors));
        }
        //        $arrData = ArrayUtils::removeEmpty($arrData);

        try {
            $mall = new Mall();

            // 테이블명 반환
            $tableName = $mall->getTableName(DB_OUT_SIDE_SCRIPT, $arrData['mallSno']);

            if ($arrData['mode'] == 'insert') {
                // 저장
                $arrBind = $this->db->get_binding(DBTableField::tableOutSideScript(), $arrData, 'insert', array_keys($arrData), ['outSideScriptNo']);
                if ($arrData['mallSno'] > DEFAULT_MALL_NUMBER) {
                    $arrBind['param'][] = 'mallSno';
                    $arrBind['bind'][0] .= 'i';
                    $arrBind['bind'][] = $arrData['mallSno'];
                }
                $this->db->set_insert_db($tableName, $arrBind['param'], $arrBind['bind'], 'y');

                // 등록된 고유번호
                $outSideScriptNo = $this->db->insert_id();
            } else if ($arrData['mode'] == 'modify') {
                // 수정
                $arrBind = $this->db->get_binding(DBTableField::tableOutSideScript(), $arrData, 'update', array_keys($arrData), ['outSideScriptNo']);
                if ($arrData['mallSno'] > DEFAULT_MALL_NUMBER) {
                    $arrBind['param'][] = 'mallSno=?';
                    $arrBind['bind'][0] .= 'i';
                    $arrBind['bind'][] = $arrData['mallSno'];
                }
                $this->db->bind_param_push($arrBind['bind'], 'i', $arrData['outSideScriptNo']);
                $this->db->set_update_db($tableName, $arrBind['param'], 'outSideScriptNo = ?', $arrBind['bind'], false);

                // 수정된 고유번호
                $outSideScriptNo = $arrData['outSideScriptNo'];
            }

            return $outSideScriptNo;
        } catch (\Exception $e) {
            throw new \Exception(__('정산 요청 중 오류가 발생하였습니다. 다시 시도해 주세요.'));
        }
    }

    /**
     * setOutSideScriptDelete
     *
     * @param $outSideScriptNoArr
     *
     * @throws \Exception
     */
    public function setOutSideScriptDelete($outSideScriptNoArr, $mallSnoArr)
    {
        $mall = new Mall();

        if (count($outSideScriptNoArr) < 1) {
            throw new \Exception(__('삭제할 외부스크립트를 선택해주세요.'));
        }
        foreach ($outSideScriptNoArr as $key => $val) {
            $outSideScriptNo = (int) $val;
            $mallSno = $mallSnoArr[$key];
            if (Validator::number($outSideScriptNo, null, null, true) === false) {
                throw new \Exception(__('삭제할 외부스크립트를 선택해주세요.'));
            }

            // 테이블명 반환
            $tableName = $mall->getTableName(DB_OUT_SIDE_SCRIPT, $mallSno);

            $arrBind = [
                'i',
                $outSideScriptNo,
            ];
            // --- 삭제
            $this->db->set_delete_db($tableName, 'outSideScriptNo = ?', $arrBind);
        }
    }
}
