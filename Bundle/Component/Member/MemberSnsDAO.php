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

namespace Bundle\Component\Member;


use App;
use Component\Database\DBTableField;
use Framework\Database\DB;

/**
 * Class MemberSnsDAO
 * @package Bundle\Component\Member
 * @author  yjwee
 */
class MemberSnsDAO
{
    /** @var  \Framework\Database\DBTool $db */
    private $db;
    private $fields;

    public function __construct(DB $db = null)
    {
        if ($db === null) {
            $db = App::load('DB');
        }
        $this->db = $db;
        $this->fields = DBTableField::getFieldTypes('tableMemberSns');
    }

    /**
     * select table (es_memberSns)
     *
     * @param integer $memberNo member sno
     * @param string  $appId    third party app id
     *
     * @return array es_memberSns(*)
     */
    public function selectMemberSns($memberNo, $appId)
    {
        $this->db->strField = '*';
        $this->db->strWhere = 'memNo = ? AND appId = ?';
        $this->db->bind_param_push($bind, $this->fields['memNo'], $memberNo);
        $this->db->bind_param_push($bind, $this->fields['appId'], $appId);
        $session = \App::getInstance('session');
        if ($session->has(SESSION_GLOBAL_MALL)) {
            $this->db->strWhere .= ' AND mallSno = ?';
            $mallSession = $session->get(SESSION_GLOBAL_MALL);
            $this->db->bind_param_push($bind, $this->fields['mallSno'], $mallSession['sno']);
        } else {
            $this->db->strWhere .= ' AND mallSno = ?';
            $this->db->bind_param_push($bind, $this->fields['mallSno'], DEFAULT_MALL_NUMBER);
        }
        $query = $this->db->query_complete();
        $strSQL = 'SELECT ' . array_shift($query) . ' FROM ' . DB_MEMBER_SNS . implode(' ', $query);

        return $this->db->query_fetch($strSQL, $bind, false);
    }

    /**
     * select table (es_memberSns, es_member)
     *
     * @param string $uuid  third party user id
     * @param string $appId third party app id
     *
     * @return array es_memberSns(*), es_member(mallSno)
     */
    public function selectMemberByUUID($uuid, $appId)
    {
        $this->db->strField = 'ms.*, m.mallSno';
        $this->db->strJoin = ' LEFT JOIN ' . DB_MEMBER . ' AS m ON ms.memNo = m.memNo';
        $this->db->strWhere = 'ms.uuid = ? AND ms.appId = ?';
        $this->db->strOrder = 'ms.sno DESC';
        $this->db->strLimit = '1';
        $this->db->bind_param_push($bind, $this->fields['uuid'], $uuid);
        $this->db->bind_param_push($bind, $this->fields['appId'], $appId);
        $session = \App::getInstance('session');
        if ($session->has(SESSION_GLOBAL_MALL)) {
            $this->db->strWhere .= ' AND ms.mallSno = ?';
            $mallSession = $session->get(SESSION_GLOBAL_MALL);
            $this->db->bind_param_push($bind, $this->fields['mallSno'], $mallSession['sno']);
        } else {
            $this->db->strWhere .= ' AND ms.mallSno = ?';
            $this->db->bind_param_push($bind, $this->fields['mallSno'], DEFAULT_MALL_NUMBER);
        }
        $query = $this->db->query_complete();
        $strSQL = 'SELECT ' . array_shift($query) . ' FROM ' . DB_MEMBER_SNS . ' AS ms ';
        $strSQL .= implode(' ', $query);

        return $this->db->query_fetch($strSQL, $bind, false);
    }

    /**
     * select login info by table (es_memberSns, es_member, es_memberGroup, es_memberHackout)
     *
     * @param string $uuid  third party user id
     * @param string $appId third party app id
     *
     * @return array es_memberSns(snsJoinFl, snsTypeFl, connectFl, accessToken), es_member(...), es_memberGroup(groupNm, groupSort), es_memberHackout(sno)
     */
    public function selectLoginInfoByUUID($uuid, $appId)
    {
        $this->db->strField = 'm.memNo, m.memId, m.memPw, m.groupSno, m.memNm, m.appFl, m.sleepFl, m.maillingFl, m.smsFl, m.loginLimit';
        $this->db->strField .= ', m.email, m.adultConfirmDt, m.adultFl, m.loginCnt, m.changePasswordDt, m.guidePasswordDt';
        $this->db->strField .= ', m.modDt AS mModDt, m.regDt AS mRegDt, ms.snsJoinFl, ms.snsTypeFl, ms.connectFl, ms.accessToken';
        $this->db->strField .= ', mg.groupNm, mg.groupSort, mh.sno AS hackOutSno';
        $this->db->strJoin = ' LEFT JOIN ' . DB_MEMBER . ' AS m ON ms.memNo = m.memNo';
        $this->db->strJoin .= ' LEFT JOIN ' . DB_MEMBER_GROUP . ' AS mg ON mg.sno = m.groupSno';
        $this->db->strJoin .= ' LEFT JOIN ' . DB_MEMBER_HACKOUT . ' AS mh ON mh.memNo = m.memNo';
        $this->db->strWhere = 'ms.uuid=? AND mh.sno IS NULL AND ms.appId=?';
        $this->db->strLimit = '1';

        $this->db->bind_param_push($bind, $this->fields['uuid'], $uuid);
        $this->db->bind_param_push($bind, $this->fields['appId'], $appId);
        $session = \App::getInstance('session');
        if ($session->has(SESSION_GLOBAL_MALL)) {
            $this->db->strWhere .= ' AND ms.mallSno = ?';
            $mallSession = $session->get(SESSION_GLOBAL_MALL);
            $this->db->bind_param_push($bind, $this->fields['mallSno'], $mallSession['sno']);
        } else {
            $this->db->strWhere .= ' AND ms.mallSno = ?';
            $this->db->bind_param_push($bind, $this->fields['mallSno'], DEFAULT_MALL_NUMBER);
        }
        $query = $this->db->query_complete();

        $strSQL = 'SELECT  ' . array_shift($query) . ' FROM ' . DB_MEMBER_SNS . ' AS ms ';
        $strSQL .= implode(' ', $query);

        return $this->db->query_fetch($strSQL, $bind, false);
    }

    /**
     * insert table (es_memberSns)
     *
     * @param integer $memberNo    member sno
     * @param string  $uuid        third party user id
     * @param string  $snsJoinFl   sns join(y) or sns connect(n)
     * @param string  $accessToken sns accessToken
     * @param string  $snsTypeFl   third part type
     * @param string  $appId       third party app id
     *
     * @return int insert id
     */
    public function insertMemberSns($memberNo, $uuid, $snsJoinFl, $accessToken, $snsTypeFl = '', $appId = 'godo')
    {
        $values = [
            'memNo'       => $memberNo,
            'uuid'        => $uuid,
            'snsJoinFl'   => $snsJoinFl,
            'accessToken' => $accessToken,
            'snsTypeFl'   => $snsTypeFl,
            'connectFl'   => 'y',
            'appId'       => $appId,
        ];
        $session = \App::getInstance('session');
        if ($session->has(SESSION_GLOBAL_MALL)) {
            $mallSession = $session->get(SESSION_GLOBAL_MALL);
            $values['mallSno'] = $mallSession['sno'];
        }
        $bind = $this->db->get_binding(DBTableField::tableMemberSns(), $values, 'insert');
        $this->db->set_insert_db(DB_MEMBER_SNS, $bind['param'], $bind['bind'], 'y');

        return $this->db->insert_id();
    }

    /**
     * updateToken
     *
     * @param array $params
     */
    public function updateToken(array $params)
    {
        $include = [
            'accessToken',
            'refreshToken',
        ];
        $bind = $this->db->get_binding(DBTableField::tableMemberSns(), $params, 'update', $include);
        $this->db->bind_param_push($bind['bind'], $this->fields['uuid'], $params['uuid']);
        $this->db->bind_param_push($bind['bind'], $this->fields['appId'], $params['appId']);
        $this->db->set_update_db(DB_MEMBER_SNS, $bind['param'], 'uuid = ? AND appId = ?', $bind['bind']);
    }

    /**
     * deleteMemberSns
     *
     * @param        $memberNo
     */
    public function deleteMemberSns($memberNo)
    {
        $this->db->bind_param_push($bind, $this->fields['memNo'], $memberNo);
        $this->db->set_delete_db(DB_MEMBER_SNS, 'memNo=?', $bind);
    }

    /**
     * updateUUIDByAppId
     *
     * @param array $params
     *
     * @return int
     */
    public function updateUUIDBySnsTypeFl(array $params)
    {
        $arrBind = $this->db->get_binding(DBTableField::tableMemberSns(), ['uuid' => $params['uuid']], 'update', ['uuid']);
        $this->db->bind_param_push($arrBind['bind'], $this->fields['snsTypeFl'], $params['snsTypeFl']);
        $this->db->set_update_db(DB_MEMBER_SNS, $arrBind['param'], 'snsTypeFl = ?', $arrBind['bind']);

        return $this->db->affected_rows();
    }

    /**
     * deleteMemberUserId
     *
     * @param        $uuid
     */
    public function deleteSnsMember($uuid)
    {
        $this->db->bind_param_push($bind, $this->fields['uuid'], $uuid);
        $this->db->set_delete_db(DB_MEMBER_SNS, 'uuid=?', $bind);
    }
}
