<?php
/**
 * MemberSearchList
 * 파워메일/메일 보내기 회원 검색 class
 *
 * @author    sj
 * @version   1.0
 * @since     1.0
 * @copyright ⓒ 2016, NHN godo: Corp.
 */

namespace Bundle\Component\Member;

use Framework\Utility\StringUtils;
use Logger;

class MemberSearchList
{
    /** @var \Framework\Database\DBTool $db */
    protected $db = null;

    /**
     * 생성자
     */
    public function __construct()
    {
        if (!is_object($this->db)) {
            $this->db = \App::load('DB');
        }
    }

    /**
     * 검색 쿼리 및 셀렉트 박스, 체크 박스 상태값 가져오기
     *
     * @static
     *
     * @param array  $req REQUEST 값.
     * @param object $db  db class.
     *
     * @return array
     */
    public static function getQueryString(&$req, &$db)
    {
        if (gd_isset($req['skey']) && gd_isset($req['sword'])) {
            $selected['skey'][$req['skey']] = "selected";

            if ($req['skey'] == 'all') {
                $arrWhere[] = "( CONCAT(memId,memNm) LIKE CONCAT('%',?,'%') OR nickNm LIKE CONCAT('%',?,'%') )";
                $db->bind_param_push($arrBind, 's', $req['sword']);
                $db->bind_param_push($arrBind, 's', $req['sword']);
            } else {
                $arrWhere[] = $req['skey'] . " like CONCAT('%',?,'%')";
                $db->bind_param_push($arrBind, 's', $req['sword']);
            }
        }

        if (gd_isset($req['appFl']) != '') {
            $selected['appFl'][$req['appFl']] = "selected";
            $arrWhere[] = "appFl=?";
            $db->bind_param_push($arrBind, 's', $req['appFl']);
        }
        if (gd_isset($req['groupSno']) != '') {
            $selected['groupSno'][$req['groupSno']] = "selected";
            $arrWhere[] = "groupSno=?";
            $db->bind_param_push($arrBind, 's', $req['groupSno']);
        }

        if (gd_isset($req['saleAmt'][0]) != '' && gd_isset($req['saleAmt'][1]) != '') {
            $arrWhere[] = "saleAmt BETWEEN ? AND ?";
            $db->bind_param_push($arrBind, 's', $req['saleAmt'][0]);
            $db->bind_param_push($arrBind, 's', $req['saleAmt'][1]);
        } elseif (gd_isset($req['saleAmt'][0]) != '' && gd_isset($req['saleAmt'][1]) == '') {
            $arrWhere[] = "saleAmt >= ?";
            $db->bind_param_push($arrBind, 's', $req['saleAmt'][0]);
        } elseif (gd_isset($req['saleAmt'][0]) == '' && gd_isset($req['saleAmt'][1]) != '') {
            $arrWhere[] = "saleAmt <= ?";
            $db->bind_param_push($arrBind, 's', $req['saleAmt'][1]);
        }

        if (gd_isset($req['mileage'][0]) != '' && gd_isset($req['mileage'][1]) != '') {
            $arrWhere[] = "mileage BETWEEN ? AND ?";
            $db->bind_param_push($arrBind, 's', $req['mileage'][0]);
            $db->bind_param_push($arrBind, 's', $req['mileage'][1]);
        } elseif (gd_isset($req['mileage'][0]) != '' && gd_isset($req['mileage'][1]) == '') {
            $arrWhere[] = "mileage >= ?";
            $db->bind_param_push($arrBind, 's', $req['mileage'][0]);
        } elseif (gd_isset($req['mileage'][0]) == '' && gd_isset($req['mileage'][1]) != '') {
            $arrWhere[] = "mileage <= ?";
            $db->bind_param_push($arrBind, 's', $req['mileage'][1]);
        }

        if (gd_isset($req['entryDt'][0]) && gd_isset($req['entryDt'][1])) {
            $arrWhere[] = "entryDt BETWEEN DATE_FORMAT(?,'%Y-%m-%d 00:00:00') AND DATE_FORMAT(?,'%Y-%m-%d 23:59:59')";
            $db->bind_param_push($arrBind, 's', $req['entryDt'][0]);
            $db->bind_param_push($arrBind, 's', $req['entryDt'][1]);
        }
        if (gd_isset($req['lastLoginDt'][0]) && gd_isset($req['lastLoginDt'][1])) {
            $arrWhere[] = "IFNULL(lastLoginDt, entryDt) BETWEEN DATE_FORMAT(?,'%Y-%m-%d 00:00:00') AND DATE_FORMAT(?,'%Y-%m-%d 23:59:59')";
            $db->bind_param_push($arrBind, 's', $req['lastLoginDt'][0]);
            $db->bind_param_push($arrBind, 's', $req['lastLoginDt'][1]);
        }

        if (gd_isset($req['sexFl'])) {
            $checked['sexFl'][$req['sexFl']] = ' checked="checked" ';
            $arrWhere[] = "sexFl = ?";
            $db->bind_param_push($arrBind, 's', $req['sexFl']);
        } else {
            $checked['sexFl'][''] = ' checked="checked" ';
        }

        if (gd_isset($req['ageGroup']) != '') {
            $selected['ageGroup'][$req['ageGroup']] = "selected";
            $age[] = date('Y') + 1 - $req['ageGroup'];
            $age[] = $age[0] - 9;
            if (empty($age) === false) {
                foreach ($age as $k => $v) {
                    $age[$k] = substr($v, 2, 2);
                }
            }
            if ($req['ageGroup'] == '60') {
                $arrWhere[] = "RIGHT(birthDt,2) <= ?";
                $db->bind_param_push($arrBind, 's', $age[1]);
            } else {
                $arrWhere[] = "RIGHT(birthDt,2) BETWEEN ? AND ?";
                $db->bind_param_push($arrBind, 's', $age[1]);
                $db->bind_param_push($arrBind, 's', $age[0]);
            }
        }

        if (gd_isset($req['loginCnt'][0]) != '' && gd_isset($req['loginCnt'][1]) != '') {
            $arrWhere[] = "loginCnt BETWEEN ? AND ?";
            $db->bind_param_push($arrBind, 's', $req['loginCnt'][0]);
            $db->bind_param_push($arrBind, 's', $req['loginCnt'][1]);
        } elseif (gd_isset($req['loginCnt'][0]) != '' && gd_isset($req['loginCnt'][1]) == '') {
            $arrWhere[] = "loginCnt >= ?";
            $db->bind_param_push($arrBind, 's', $req['loginCnt'][0]);
        } elseif (gd_isset($req['loginCnt'][0]) == '' && gd_isset($req['loginCnt'][1]) != '') {
            $arrWhere[] = "loginCnt <= ?";
            $db->bind_param_push($arrBind, 's', $req['loginCnt'][1]);
        }

        if (gd_isset($req['dormancy'])) {
            $dormancyDate = date("Ymd", strtotime("-{$req['dormancy']} day"));
            $arrWhere[] = " DATE_FORMAT(IFNULL(lastLoginDt, entryDt),'%Y%m%d') <= ?";
            $db->bind_param_push($arrBind, 's', $dormancyDate);
        }

        if (gd_isset($req['maillingFl'])) {
            $arrWhere[] = "maillingFl = ?";
            $db->bind_param_push($arrBind, 's', $req['maillingFl']);
            $checked['maillingFl'][$req['maillingFl']] = ' checked="checked" ';
        } else {
            $checked['maillingFl'][''] = ' checked="checked" ';
        }

        if (gd_isset($req['smsFl'])) {
            $arrWhere[] = "smsFl = ?";
            $db->bind_param_push($arrBind, 's', $req['smsFl']);
            $checked['smsFl'][$req['smsFl']] = ' checked="checked" ';
        } else {
            $checked['smsFl'][''] = ' checked="checked" ';
        }

        if (gd_isset($req['birthDate'][0])) {
            if (gd_isset($req['calendarFl'])) {
                $checked['calendarFl'][$req['calendarFl']] = ' checked="checked" ';
                $arrWhere[] = "calendarFl = ?";
                $db->bind_param_push($arrBind, 's', $req['calendarFl']);
            } else {
                $checked['calendarFl'][''] = ' checked="checked" ';
            }
            if ($req['birthDate'][1]) {
                if (strlen($req['birthDate'][0]) > 4 && strlen($req['birthDate'][1]) > 4) {
                    $arrWhere[] = "CONCAT(birthDt, birth) BETWEEN ? AND ?";
                    $db->bind_param_push($arrBind, 's', $req['birthDate'][0]);
                    $db->bind_param_push($arrBind, 's', $req['birthDate'][1]);
                } else {
                    $arrWhere[] = "birth BETWEEN ? AND ?";
                    $db->bind_param_push($arrBind, 's', $req['birthDate'][0]);
                    $db->bind_param_push($arrBind, 's', $req['birthDate'][1]);
                }
            } else {
                $arrWhere[] = "birth = ?";
                $db->bind_param_push($arrBind, 's', $req['birthDate'][0]);
            }
        }

        if (gd_isset($req['marriFl'])) {
            $checked['marriFl'][$req['marriFl']] = ' checked="checked" ';
            $arrWhere[] = "marriFl = ?";
            $db->bind_param_push($arrBind, 's', $req['marriFl']);
        } else {
            $checked['marriFl'][''] = ' checked="checked" ';
        }
        if (gd_isset($req['marriDate'][0])) {
            if (gd_isset($req['marriDate'][1])) {
                if (strlen($req['marriDate'][0]) > 4 && strlen($req['marriDate'][1]) > 4) {
                    $arrWhere[] = "marriDate BETWEEN ? AND ?";
                    $db->bind_param_push($arrBind, 's', $req['marriDate'][0]);
                    $db->bind_param_push($arrBind, 's', $req['marriDate'][1]);
                } else {
                    $arrWhere[] = "SUBSTRING(marriDate,5,4) BETWEEN ? AND ?";
                    $db->bind_param_push($arrBind, 's', $req['marriDate'][0]);
                    $db->bind_param_push($arrBind, 's', $req['marriDate'][1]);
                }
            } else {
                $arrWhere[] = "SUBSTRING(marriDate,5,4) = ?";
                $db->bind_param_push($arrBind, 's', $req['marriDate'][0]);
            }
        }

        return [
            gd_isset($arrWhere),
            gd_isset($arrBind),
            gd_isset($selected),
            gd_isset($checked),
        ];
    }

    /**
     * 회원 리스트 가져오기
     *
     * @param array      $req       REQUEST 값.
     * @param bool|false $allListFl 전체 리스트 출력 여부(true일 경우 페이징X)
     *
     * @return mixed
     */
    public function getList(&$req, $allListFl = false)
    {
        // 총 레코드수
        $res = $this->db->fetch("SELECT count(*) AS cnt FROM " . DB_MEMBER);
        $totalMemCnt = $res['cnt'];

        // 목록
        if (gd_isset($req['indicate']) == 'search') {
            if ($allListFl === false) {
                gd_isset($req['page'], 1);
                gd_isset($req['perPage'], 10);
            }

            $orderby = gd_isset($req['sort'], 'entryDt desc'); # 정렬 쿼리

            // 변수할당
            list($arrWhere, $arrBind, $selected, $checked) = $this->getQueryString($req, $this->db);

            $selected['perPage'][$req['perPage']] = "selected";
            $selected['sort'][$orderby] = "selected";

            $this->db->strField = "*";
            if ($arrWhere) {
                $this->db->strWhere = implode(' AND ', $arrWhere);
            }
            $this->db->strOrder = $orderby;
            if ($allListFl === false) {
                $this->db->strLimit = ($req['page'] - 1) * $req['perPage'] . ', ' . $req['perPage'];
            }
            $query = $this->db->query_complete(true, true);
            $strSQL = 'SELECT ' . array_shift($query) . ' FROM ' . DB_MEMBER . ' ' . implode(' ', $query);
            $data = gd_htmlspecialchars_stripslashes($this->db->query_fetch($strSQL, $arrBind));

            $funcFoundRows = function () use ($arrBind) {
                $query = $this->db->getQueryCompleteBackup(
                    [
                        'field' => 'COUNT(*) AS cnt',
                        'order' => null,
                        'limit' => null,
                    ]
                );
                $strSQL = 'SELECT ' . array_shift($query) . ' FROM ' . DB_MEMBER . ' ' . implode(' ', $query);
                $cnt = $this->db->query_fetch($strSQL, $arrBind, false)['cnt'];
                StringUtils::strIsSet($cnt, 0);

                return $cnt;
            };
            $getData['srchCnt'] = $funcFoundRows();
            $getData['data'] = &$data;
            $getData['selected'] = gd_isset($selected);
            $getData['checked'] = gd_isset($checked);

            unset($arrWhere);
            unset($selected);
            unset($checked);
        } else {
            $getData['checked']['sexFl'][''] = 'checked="checked"';
            $getData['checked']['smsFl'][''] = 'checked="checked"';
            $getData['checked']['maillingFl'][''] = 'checked="checked"';
            $getData['checked']['calendarFl'][''] = 'checked="checked"';
            $getData['checked']['marriFl'][''] = 'checked="checked"';
        }

        $getData['totalCnt'] = $totalMemCnt;

        return $getData;
    }

    /**
     * 회원 전체 리스트 가져오기
     *
     * @param array $req REQUEST 값
     *
     * @return array
     */
    public function getAllList(&$req)
    {
        Logger::debug(__METHOD__, $req);
        $orderby = gd_isset($req['sort'], 'entryDt desc'); # 정렬 쿼리

        // 변수할당
        list($arrWhere, $arrBind, $selected, $checked) = $this->getQueryString($req, $this->db);

        $this->db->strField = 'memNo, maillingFl';
        if ($arrWhere) {
            $this->db->strWhere = implode(' AND ', $arrWhere);
        }
        $this->db->strOrder = $orderby;
        $query = $this->db->query_complete();

        $strSQL = 'SELECT ' . array_shift($query) . ' FROM ' . DB_MEMBER . ' ' . implode(' ', $query);
        $data = gd_htmlspecialchars_stripslashes($this->db->query_fetch($strSQL, $arrBind));

        unset($arrWhere);
        unset($selected);
        unset($checked);

        return $data;
    }
}
