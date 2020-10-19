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

namespace Bundle\Component\Member\Group;

use Component\Database\DBTableField;

/**
 * Class GroupDAO
 * @package Bundle\Component\Member\Group
 * @author  yjwee
 */
class GroupDAO extends \Component\AbstractComponent
{
    /**
     * 등급 등록
     *
     * @param array $group  등급정보
     * @param array $fields 등급항목
     *
     * @return integer|string
     */
    public function insertGroup(array $group, array $fields)
    {
        $arrBind = $this->db->get_binding(DBTableField::tableMemberGroup(), $group, 'insert', $fields);
        $this->db->set_insert_db(DB_MEMBER_GROUP, $arrBind['param'], $arrBind['bind'], 'y');
        $id = $this->db->insert_id();

        return $id;
    }

    /**
     * 등급 수정
     *
     * @param array $group  등급정보
     * @param array $fields 등급항목
     *
     * @return void
     */
    public function updateGroup(array $group, array $fields)
    {
        $excludes = ['groupSort'];
        $arrBind = $this->db->get_binding(DBTableField::tableMemberGroup(), $group, 'update', $fields, $excludes);
        $this->db->bind_param_push($arrBind['bind'], 'i', $group['sno']);
        $this->db->set_update_db(DB_MEMBER_GROUP, $arrBind['param'], 'sno = ? ', $arrBind['bind']);
    }

    /**
     * 회원 등급 평가 시 사용될 등급 조회
     *
     * @param array $params ['apprSystem' => 'figure|point'   // 실적수치제|실적점수제]
     *
     * @return array
     */
    public function selectAppraisalGroups(array $params = [])
    {
        $db = \App::getInstance('DB');
        $db->strField = '*';
        if (key_exists('apprSystem', $params) && $params['apprSystem'] == 'figure') {
            $db->strWhere = 'apprFigureOrderPriceFl=\'y\' OR apprFigureOrderRepeatFl=\'y\' OR apprFigureReviewRepeatFl=\'y\' OR sno=1';
        }
        $db->strOrder = 'groupSort DESC';
        $query = $db->query_complete();
        $strSQL = 'SELECT ' . array_shift($query) . ' FROM ' . DB_MEMBER_GROUP . ' ' . implode(' ', $query);
        $resultSet = $db->query_fetch($strSQL);

        return $resultSet;
    }

    /**
     * 등급 조회
     *
     * @param integer $sno 등급번호
     *
     * @return array|object
     */
    public function selectGroup($sno)
    {
        $this->db->strField = '*';
        $this->db->strWhere = 'sno=?';
        $this->db->bind_param_push($arrBind, 'i', $sno);
        $query = $this->db->query_complete();
        $strSQL = 'SELECT ' . array_shift($query) . ' FROM ' . DB_MEMBER_GROUP . ' ' . implode(' ', $query);
        $group = $this->db->query_fetch($strSQL, $arrBind, false);

        return $group;
    }

    /**
     * 등급별 평가기준 점수 조회
     *
     * @return array|object
     */
    public function selectGroupStandards()
    {
        $this->db->strField = 'sno, groupNm, apprFigureOrderPriceFl, apprFigureOrderRepeatFl, apprFigureReviewRepeatFl';
        $this->db->strField .= ', apprFigureOrderPriceMore, apprFigureOrderPriceBelow';
        $this->db->strField .= ', apprFigureOrderRepeat, apprFigureReviewRepeat, apprPointMore, apprPointBelow';
        $this->db->strField .= ', apprFigureOrderPriceMoreMobile, apprFigureOrderPriceBelowMobile';
        $this->db->strField .= ', apprFigureOrderRepeatMobile, apprFigureReviewRepeatMobile, apprPointMoreMobile, apprPointBelowMobile';
        $this->db->strWhere = 'sno!=1';
        $this->db->strOrder = 'apprFigureOrderPriceMore DESC, apprFigureOrderRepeat DESC';
        $query = $this->db->query_complete();
        $strSQL = 'SELECT ' . array_shift($query) . ' FROM ' . DB_MEMBER_GROUP . ' ' . implode(' ', $query);
        $group = $this->db->query_fetch($strSQL, null, true);

        return $group;
    }

    /**
     * 새로 생성되는 그룹의 순번 생성
     *
     * @return array|object
     */
    public function selectNewGroupSort()
    {
        $strSQL = 'SELECT if(max(groupSort) > 0, (max(groupSort) + 1), 1) as newGroupSort FROM ' . DB_MEMBER_GROUP;
        $data = $this->db->query_fetch($strSQL, null, false);

        return $data['newGroupSort'];
    }

    /**
     * 그룹명을 이용한 그룹테이블 카운트
     *
     * @param string       $name 등급명
     * @param null|integer $sno  등급번호
     *
     * @return mixed
     */
    public function countGroupName($name, $sno)
    {
        $where = 'WHERE groupNm=\'' . $name . '\'';
        if ($sno > 0) {
            $where .= ' AND sno!=' . $sno;
        }

        return $this->db->getCount(DB_MEMBER_GROUP, '1', $where);
    }

    /**
     * selectGroupName
     *
     * @return array
     */
    public function selectGroupName()
    {
        $db = \App::getInstance('DB');
        $strSQL = 'SELECT sno, groupNm FROM ' . DB_MEMBER_GROUP;
        $resultSet = $db->query_fetch($strSQL);

        return $resultSet;
    }

    /**
     * 회원등급 평가 시 제외인 등급 select
     *
     * @return array
     */
    public function selectGroupExclusion()
    {
        $db = \App::getInstance('DB');
        $strSQL = 'SELECT sno, apprExclusionOfRatingFl FROM ' . DB_MEMBER_GROUP;
        $resultSet = $db->query_fetch($strSQL);

        return $resultSet;
    }
}
