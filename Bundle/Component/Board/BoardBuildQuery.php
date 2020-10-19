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
 * @link http://www.godo.co.kr
 */

namespace Bundle\Component\Board;

use Component\Order\Order;
use Component\Database\DBTableField;
use Framework\StaticProxy\Proxy\App;

class BoardBuildQuery
{
    protected static $_instance = null;
    protected static $_bdId;
    protected static $_db;
    protected static $_fieldTypes;
    protected static $_cfg;

    /**
     * init
     *
     * @static
     * @param $bdId
     * @param null $boardConfig
     * @return BoardBuildQuery|null
     */
    public static function init($bdId ,$boardConfig = null)
    {
        self::$_db = App::load('DB');
        self::$_bdId = $bdId;
        if(empty($boardConfig)){
            $boardConfig = new BoardConfig($bdId);
        }
        self::$_cfg = $boardConfig->cfg;

        self::$_fieldTypes['board'] = DBTableField::getFieldTypes('tableBd');
        self::$_fieldTypes['memo'] = DBTableField::getFieldTypes('tableBdMemo');

        if (self::$_instance === null) {
            self::$_instance = new \Component\Board\BoardBuildQuery;
        }

        return self::$_instance;
    }

    public function getQueryWhere($search = null)
    {
        $arrBind = [];
        $strWhere = "";
        $arrWhere = [];
        $arrJoin = [];
        //회원검색
        if ($search['mypageFl'] == 'y') {
            $arrWhere[] = " (b.memNo  = ?) ";
            self::$_db->bind_param_push($arrBind, 'i', \Session::get('member.memNo'));
        }
        else if (gd_isset($search['memNo'])) {
            if(self::$_cfg['bdKind'] == Board::KIND_QA){
                $arrWhere[] = " (b.memNo  = ?) ";
                self::$_db->bind_param_push($arrBind, 'i', $search['memNo']);
            }
            else {
                $arrWhere[] = " (b.memNo  = ?) ";
                self::$_db->bind_param_push($arrBind, 'i', $search['memNo']);
            }
        }

        if ($search['recentlyDate']) {
            $arrWhere[] = "TO_DAYS(now()) - TO_DAYS(b.regDt) <= " . $search['recentlyDate'];
        }

        if (gd_isset($search['scmNo'])) {
            $arrWhere[] = "g.scmNo = ?";
            self::$_db->bind_param_push($arrBind, 'i', $search['scmNo']);
            $arrJoin[] = DB_GOODS;
        }

        if (gd_isset($search['searchWord'])) {
            switch ($search['searchField']) {
                case 'subject' :
                    self::$_db->bind_param_push($arrBind, 's', $search['searchWord']);
                    $arrWhere[] = "subject LIKE concat('%',?,'%')";
                    break;
                    break;
                case 'contents' :
                    self::$_db->bind_param_push($arrBind, 's', $search['searchWord']);
                    $arrWhere[] = "contents LIKE concat('%',?,'%')";
                    break;
                case 'writerNm' :
                    if($search['searchWord'] == '네이버페이구매자' || $search['searchWord'] == '네이버페이 구매자') {
                        self::$_db->bind_param_push($arrBind, 's', 'naverpay');
                        $arrWhere[] = "channel  = ? ";
                    } else {
                        self::$_db->bind_param_push($arrBind, 's', $search['searchWord']);
                        $arrWhere[] = "writerNm  LIKE concat('%',?,'%')";
                    }
                    break;
                case 'writerNick' :
                    self::$_db->bind_param_push($arrBind, 's', $search['searchWord']);
                    $arrWhere[] = "writerNick  LIKE concat('%',?,'%')";
                    break;
                case 'writerId' :
                    self::$_db->bind_param_push($arrBind, 's', $search['searchWord']);
                    $arrWhere[] = "writerId  LIKE concat('%',?,'%')";
                    break;
                case 'subject_contents' :
                    $arrWhere[] = "(subject LIKE concat('%',?,'%') or contents LIKE concat('%',?,'%') )";
                    self::$_db->bind_param_push($arrBind, 's', $search['searchWord']);
                    self::$_db->bind_param_push($arrBind, 's', $search['searchWord']);
                    break;
                case 'goodsNm' :
                    if (self::$_cfg['bdGoodsFl'] == 'y') {
                        $arrJoin[] = DB_GOODS;
                        self::$_db->bind_param_push($arrBind, 's', $search['searchWord']);
                        $arrWhere[] = "g.goodsNm  LIKE concat('%',?,'%')";
                    }
                    break;
                case 'goodsNo' :
                    if (self::$_cfg['bdGoodsFl'] == 'y') {
                        $arrJoin[] = DB_GOODS;
                        self::$_db->bind_param_push($arrBind, 's', $search['searchWord']);
                        $arrWhere[] = "g.goodsNo  LIKE concat('%',?,'%')";
                    }
                    break;
                case 'goodsCd' :
                    if (self::$_cfg['bdGoodsFl'] == 'y') {
                        $arrJoin[] = DB_GOODS;
                        self::$_db->bind_param_push($arrBind, 's', $search['searchWord']);
                        $arrWhere[] = "g.goodsCd  LIKE concat('%',?,'%')";
                        break;
                    }
            }
        }

        if (gd_isset($search['goodsPt'])) {
            $arrWhere[] = "goodsPt = ?";
            self::$_db->bind_param_push($arrBind, 'i', $search['goodsPt']);
        }


        if (gd_isset($search['replyStatus'])) {
            $arrWhere[] = " replyStatus = ?";
            self::$_db->bind_param_push($arrBind, 's', $search['replyStatus']);
        }

        switch ($search['period']) {
            case 'current' :
                $arrWhere[] = " now() between eventStart and eventEnd ";
                break;
            case 'end' :
                $arrWhere[] = " now() > eventEnd ";
                break;
        }

        //일자 검색
        if (gd_isset($search['rangDate'][0]) && gd_isset($search['rangDate'][1])) {
            if ($search['searchDateFl'] == 'modDt') {
                $dateField = 'b.modDt';
            } else {
                $dateField = 'b.regDt';
            }

            $arrWhere[] = $dateField . " between ? and ?";
            self::$_db->bind_param_push($arrBind, 's', $search['rangDate'][0]);
            self::$_db->bind_param_push($arrBind, 's', $search['rangDate'][1] . ' 23:59');
        }

        //이벤트 기간검색
        if (gd_isset($search['rangEventDate'][0]) && gd_isset($search['rangEventDate'][1])) {
            $arrWhere[] = " eventStart < ? AND eventEnd > ? ";
            self::$_db->bind_param_push($arrBind, 's', $search['rangEventDate'][0]);
            self::$_db->bind_param_push($arrBind, 's', $search['rangEventDate'][1] . ' 23:59');
        }

        if (self::$_cfg['bdCategoryFl'] == 'y') {
            if (gd_isset($search['category'])) {
                $arrWhere[] = "category = ?";
                self::$_db->bind_param_push($arrBind, self::$_fieldTypes['board']['category'], $search['category']);
            }
        }

        if (self::$_cfg['bdGoodsFl'] == 'y') {
            if (gd_isset($search['goodsNo'])) {
                $arrWhere[] = " b.goodsNo  = ?";
                self::$_db->bind_param_push($arrBind, 'i', $search['goodsNo']);
            }
        }

        if (gd_isset($search['isNotice'])) {
            $arrWhere[] = "isNotice = ?";
            self::$_db->bind_param_push($arrBind, self::$_fieldTypes['board']['isNotice'], $search['isNotice']);
        }

        $strWhere .= implode(" AND ", $arrWhere);

        return [$strWhere, $arrBind, $arrJoin];
    }

    public function selectOne($sno)
    {
        $query = " SELECT b.*   FROM " . DB_BD_ . self::$_bdId . " as b  ";
        $query .= " WHERE b.sno = ?";
        $arrBind = [];
        self::$_db->bind_param_push($arrBind, 'i', $sno);
        $result = self::$_db->query_fetch($query, $arrBind)[0];

        return $result;
    }

    public function selectOneWithGoodsAndMember($sno)
    {
        $query = " SELECT b.*, g.scmNo, m.email, m.cellPhone, m.smsFl, m.maillingFl   FROM " . DB_BD_ . self::$_bdId . " as b LEFT JOIN " . DB_GOODS . " AS g ON b.goodsNo=g.goodsNo";
        $query .= " LEFT JOIN " . DB_MEMBER . " AS m ON b.memNo=m.memNo";
        $query .= " WHERE b.sno = ?";
        $arrBind = [];
        self::$_db->bind_param_push($arrBind, 'i', $sno);
        $result = self::$_db->query_fetch($query, $arrBind)[0];

        return $result;
    }

    public function selectOneWithMember($sno)
    {
        $query = " SELECT b.*, m.email, m.cellPhone, m.smsFl, m.maillingFl   FROM " . DB_BD_ . self::$_bdId . " as b LEFT JOIN " . DB_MEMBER . " AS m ON b.memNo=m.memNo";
        $query .= " WHERE b.sno = ?";
        $arrBind = [];
        self::$_db->bind_param_push($arrBind, 'i', $sno);
        $result = self::$_db->query_fetch($query, $arrBind)[0];

        return $result;
    }

    /**
     * selectList
     *
     * @param null $search
     * @param array|null $addWhereQuery
     * @param int $offset
     * @param int $limit
     * @param null $arrInclude 포함시킬 필드. 게시글필드는 prefix b. / 상품필드는 prefix g. / 회원필드는 prefix m.
     * @param null $orderByField 정렬시킬 필드
     * @return mixed
     */
    public function selectList($search = null, array $addWhereQuery = null, $offset = 0, $limit = 10, $arrInclude = null,$orderByField = null)
    {
        if ($search) {
            list($strWhere, $arrBind) = self::getQueryWhere($search);
            $strWhere = (!$strWhere) ? "" : " AND " . $strWhere;
        }
        if(!$orderByField) {
            if($search['sort']) {
                $orderByField = $search['sort'];
            } else {
                $orderByField = 'b.groupNo asc';
            }
        }

        $joinGoods = false;
        $joinGoodsImage = false;
        if ($arrInclude) {
            foreach ($arrInclude as $_field) {
                switch (substr($_field, 0, 2)) {
                    case 'gi.' :
                        if (self::$_cfg['bdGoodsFl'] == 'y') {
                            $joinGoods = true;
                            $joinGoodsImage = true;
                            $gField[] = $_field;
                        }
                        break;
                    case 'g.':
                        if (self::$_cfg['bdGoodsFl'] == 'y') {
                            $joinGoods = true;
                            $gField[] = $_field;
                        }
                        break;
                    case 'm.' :
                        $mField[] = $_field;
                    case 'b.' :
                        $bField[] = $_field;
                        break;
                    default :
                        $bField[] = 'b.' . $_field;
                }
            }
            $boardFields = implode(',', $bField);
            if($gField) {
                $goodsFields = ',' . implode(',', $gField);
            }

        } else {
            $boardFields = implode(',', DBTableField::setTableField('tableBd', null, ['apiExtraData','contents'], 'b'));
            //$boardFields.=',SUBSTRING(b.contents,1,1000) as contents';
            $boardFields .= ', b.contents as contents';
            if (self::$_cfg['bdGoodsFl'] == 'y') {
                $joinGoods = true;
                $joinGoodsImage = true;
                $arrGoodsField = ['scmNo','goodsNm','goodsPrice','brandCd','makerNm','originNm','imagePath','imageStorage','onlyAdultFl','onlyAdultImageFl'];
                $goodsFields = ','.implode(',', DBTableField::setTableField('tableGoods', $arrGoodsField, null, 'g'));
            }

            if (self::$_cfg['goodsType'] == 'order') {
                $joinExtra = true;
                $arrExtraField = ',goodsNoText,orderGoodsNoText';
            }
        }
        $boardField = 'b.sno,b.regDt,b.modDt,' . $boardFields . $goodsFields . $arrExtraField;

        $strSQL = " SELECT " . $boardField . " FROM " . DB_BD_ . self::$_bdId . " as b  ";
        if ($joinGoods) {
            $strSQL .= " LEFT OUTER JOIN " . DB_GOODS . " as g ON b.goodsNo = g.goodsNo ";

            if (\Request::getSubdomainDirectory() !== 'admin') {
                //접근권한 체크
                if (gd_check_login()) {
                    $strGoodsWhere = ' (g.goodsAccess !=\'group\'  OR (g.goodsAccess=\'group\' AND FIND_IN_SET(\''.\Session::get('member.groupSno').'\', REPLACE(g.goodsAccessGroup,"'.INT_DIVISION.'",","))) OR (g.goodsAccess=\'group\' AND !FIND_IN_SET(\''.\Session::get('member.groupSno').'\', REPLACE(g.goodsAccessGroup,"'.INT_DIVISION.'",",")) AND g.goodsAccessDisplayFl =\'y\'))';
                } else {
                    $strGoodsWhere = ' (g.goodsAccess=\'all\' OR (g.goodsAccess !=\'all\' AND g.goodsAccessDisplayFl =\'y\'))';
                }

                //성인인증안된경우 노출체크 상품은 노출함
                if (gd_check_adult() === false) {
                    $strGoodsWhere .= ' AND (onlyAdultFl = \'n\' OR (onlyAdultFl = \'y\' AND onlyAdultDisplayFl = \'y\'))';
                }

                $strWhere .=" AND (b.goodsNo = '0' OR (b.goodsNo > 0 AND " .$strGoodsWhere."))";
            }
        }
        if ($joinExtra) {
            $strSQL .= " LEFT OUTER JOIN " . DB_BOARD_EXTRA_DATA . " as bet ON bet.bdId = '".self::$_bdId."' AND  b.sno = bet.bdSno ";
        }
        $strSQL .= " WHERE 1 " . $strWhere;

        if ($addWhereQuery) {
            foreach($addWhereQuery as $key=>$val) {
                if(!$val){
                    unset($addWhereQuery[$key]);
                }
            }
            $strSQL .= ' AND ' . implode(' AND ', $addWhereQuery);
        }
        $limit = $limit ?? 10;
        $strSQL .= " ORDER BY  ".$orderByField."  ,  groupThread  "."LIMIT {$offset},{$limit}";

        $result = self::$_db->slave()->query_fetch($strSQL, $arrBind);


        if ($joinGoodsImage) {
            foreach ($result as $row) {
                if ($row['goodsNo']) {
                    $goodsNos[] = $row['goodsNo'];
                }
            }

            if ($goodsNos) {
                $sql = "SELECT gi.goodsNo,gi.imageSize,gi.imageNo,gi.imageName FROM " . DB_GOODS_IMAGE . " as gi WHERE gi.goodsNo in (" . implode(",", $goodsNos) . ") AND gi.imageKind='main' ";   //리스트이미지로
                $goodsImageData = self::$_db->query_fetch($sql);
                foreach ($goodsImageData as $_goodsData) {
                    $arrGoodsJoinData[$_goodsData['goodsNo']] = $_goodsData;
                }
                foreach ($result as &$row) {
                    foreach ($gField as $_key=>$val) {
                        $row[$val] = $arrGoodsJoinData[$row['goodsNo']][$val];
                    }
                    $row['imageSize'] = $arrGoodsJoinData[$row['goodsNo']]['imageSize'];
                    $row['imageNo'] = $arrGoodsJoinData[$row['goodsNo']]['imageNo'];
                    $row['imageName'] = $arrGoodsJoinData[$row['goodsNo']]['imageName'];
                    $row['cateCd'] = $arrGoodsJoinData[$row['goodsNo']]['cateCd'];
                }
            }
        }
        return $result;
    }


    /**
     * 게시글갯수 노출
     *
     * @param null $search
     * @param array|null $addWhereQuery
     * @return mixed
     */
    public function selectCount($search = null, array $addWhereQuery = null)
    {
        if ($search) {
            list($strWhere, $arrBind, $arrJoin) = self::getQueryWhere($search);
            $strWhere = (!$strWhere) ? "" : " AND " . $strWhere;
        }

        //검색결과로 조인여부 결정
        foreach($addWhereQuery as $_val) {
            if(strpos($_val,'g.')!==false){ //상품필드가 조건절에 포함되어있으면 조인
                $goodsJoin = true;
            }
        }

        if(self::$_cfg['bdGoodsFl'] == 'y') {
            $goodsJoin = true;
        }

        if (in_array(DB_GOODS, $arrJoin) || $goodsJoin) {
            $leftJoinGoods = " LEFT OUTER  JOIN " . DB_GOODS . " AS g
                    ON g.goodsNo = b.goodsNo ";

            if (\Request::getSubdomainDirectory() !== 'admin') {
                //접근권한 체크
                if (gd_check_login()) {
                    $strGoodsWhere = ' (g.goodsAccess !=\'group\'  OR (g.goodsAccess=\'group\' AND FIND_IN_SET(\''.\Session::get('member.groupSno').'\', REPLACE(g.goodsAccessGroup,"'.INT_DIVISION.'",","))) OR (g.goodsAccess=\'group\' AND !FIND_IN_SET(\''.\Session::get('member.groupSno').'\', REPLACE(g.goodsAccessGroup,"'.INT_DIVISION.'",",")) AND g.goodsAccessDisplayFl =\'y\'))';
                } else {
                    $strGoodsWhere = '  (g.goodsAccess=\'all\' OR (g.goodsAccess !=\'all\' AND g.goodsAccessDisplayFl =\'y\'))';
                }

                //성인인증안된경우 노출체크 상품은 노출함
                if (gd_check_adult() === false) {
                    $strGoodsWhere .= ' AND (onlyAdultFl = \'n\' OR (onlyAdultFl = \'y\' AND onlyAdultDisplayFl = \'y\'))';
                }

                $strWhere .=" AND (b.goodsNo = '0' OR (b.goodsNo > 0 AND " .$strGoodsWhere."))";
            }
        }

        if (in_array(DB_MEMBER, $arrJoin)) {
            $leftJoinMember = " LEFT OUTER  JOIN " . DB_MEMBER . " AS m
                    ON m.memNo = b.memNo ";
        }
        $strSQL = " SELECT count(*) AS cnt FROM " . DB_BD_ . self::$_bdId . " as b  ";


        $strSQL .= $leftJoinGoods . $leftJoinMember;
        $strSQL .= " WHERE 1 " . $strWhere;
        if ($addWhereQuery) {
            $strSQL .= ' AND ' . implode(' AND ', $addWhereQuery);
        }

        $result = self::$_db->slave()->query_fetch($strSQL, $arrBind, false);

        return $result['cnt'];
    }

    public function selectMemoList($bdSno)
    {
        $strSQL = "SELECT  *  FROM " . DB_BOARD_MEMO . "  WHERE bdId=? AND bdSno=? ORDER BY  groupNo desc, groupThread";
        $arrBind = [];
        self::$_db->bind_param_push($arrBind, self::$_fieldTypes['memo']['bdId'], self::$_bdId);
        self::$_db->bind_param_push($arrBind, self::$_fieldTypes['memo']['bdSno'], $bdSno);
        $result = self::$_db->query_fetch($strSQL, $arrBind);
        return $result;
    }

    public function selectMemoOne($sno)
    {
        $strSQL = "SELECT *  FROM " . DB_BOARD_MEMO . "   WHERE bdId=?  AND sno = ?";
        $arrBind = [];
        $filedTypes = DBTableField::getFieldTypes('tableBdMemo');
        self::$_db->bind_param_push($arrBind, $filedTypes['bdId'], self::$_bdId);
        self::$_db->bind_param_push($arrBind, 'i', $sno);
        $result = self::$_db->query_fetch($strSQL, $arrBind,false);
        return $result;
    }

    public function deleteMemo($sno)
    {
        $arrBind = [];
        self::$_db->bind_param_push($arrBind, self::$_fieldTypes['memo']['bdId'], self::$_bdId);
        self::$_db->bind_param_push($arrBind, 'i', $sno);
        return self::$_db->set_delete_db(DB_BOARD_MEMO, 'bdId=? AND sno=?', $arrBind);
    }

    public function deleteMemoByBdSno($bdSno)
    {
        $arrBind = [];
        self::$_db->bind_param_push($arrBind, self::$_fieldTypes['memo']['bdId'], self::$_bdId);
        self::$_db->bind_param_push($arrBind, 'i', $bdSno);
        return self::$_db->set_delete_db(DB_BOARD_MEMO, 'bdId=? AND bdSno=?', $arrBind);
    }

    public function insert($arrData)
    {
        $arrBind = self::$_db->get_binding(DBTableField::tableBd(), $arrData, 'insert', array_keys($arrData));
        self::$_db->set_insert_db(DB_BD_ . self::$_bdId, $arrBind['param'], $arrBind['bind'], 'y');
        return self::$_db->insert_id();
    }

    public function update($arrData, $sno)
    {
        $arrBind = self::$_db->get_binding(DBTableField::tableBd(), $arrData, 'update', array_keys($arrData));
        self::$_db->bind_param_push($arrBind['bind'], 'i', $sno);
        $affectedRows = self::$_db->set_update_db(DB_BD_ . self::$_bdId, $arrBind['param'], 'sno = ?', $arrBind['bind']);
        return $affectedRows;
    }

    public function updateDelete($sno)
    {
        $arrBind = [];
        self::$_db->bind_param_push($arrBind, 'i', $sno);
        return self::$_db->set_update_db(DB_BD_ . self::$_bdId, " isDelete='y' ", 'sno=?', $arrBind);
    }

    public function delete($sno)
    {
        $arrBind = [];
        self::$_db->bind_param_push($arrBind, 'i', $sno);
        return self::$_db->set_delete_db(DB_BD_ . self::$_bdId, 'sno = ?', $arrBind);
    }

    public function getMemoCount($bdSno) {
        $strSQL = "SELECT  count(*) as cnt  FROM " . DB_BOARD_MEMO . "  WHERE bdId=? AND bdSno=? ";
        $arrBind = [];
        self::$_db->bind_param_push($arrBind, self::$_fieldTypes['memo']['bdId'], self::$_bdId);
        self::$_db->bind_param_push($arrBind, self::$_fieldTypes['memo']['bdSno'], $bdSno);
        $result = self::$_db->query_fetch($strSQL, $arrBind, false);

        return $result['cnt'];
    }

    public function insertOrUpdateExtraData($arrData) {
        $arrBind = [];
        $query = "SELECT count(*) as cnt FROM ".DB_BOARD_EXTRA_DATA." WHERE bdId= ? AND bdSno = ?";
        self::$_db->bind_param_push($arrBind, 's', self::$_bdId);
        self::$_db->bind_param_push($arrBind, 'i', $arrData['bdSno']);
        $result = self::$_db->query_fetch($query,$arrBind,false);
        $updateData =['goodsNoText'=>$arrData['goodsNoText'],'orderGoodsNoText'=>$arrData['orderGoodsNoText']];
        if($result['cnt']>0) {
            $arrBind = self::$_db->get_binding(DBTableField::tableBoardExtraData(), $updateData, 'update', array_keys($updateData));
            self::$_db->bind_param_push($arrBind['bind'], 's', self::$_bdId);
            self::$_db->bind_param_push($arrBind['bind'], 'i', $arrData['bdSno']);
            self::$_db->set_update_db(DB_BOARD_EXTRA_DATA, $arrBind['param'], 'bdId = ? AND bdSno = ?', $arrBind['bind']);
        }
        else {
            $arrData['bdId'] = self::$_bdId;
            $arrBind = self::$_db->get_binding(DBTableField::tableBoardExtraData(), $arrData, 'insert', array_keys($arrData));
            self::$_db->set_insert_db(DB_BOARD_EXTRA_DATA, $arrBind['param'], $arrBind['bind'], 'y');
        }

        return 1;
    }

    public function selectExtraData($bdSno) {
        $query = "SELECT * FROM ".DB_BOARD_EXTRA_DATA." WHERE bdId = ? AND bdSno = ?";
        $arrBind = [];
        self::$_db->bind_param_push($arrBind, 's', self::$_bdId);
        self::$_db->bind_param_push($arrBind, 'i', $bdSno);
        return  self::$_db->query_fetch($query, $arrBind)[0];
    }

    public function selectCountByOrderGoodsNo($orderGoodsNo , $bdSno  = null,$isAll = false) {

        $order = new Order();
        $orderGoodsData = $order->getOrderGoods(null,$orderGoodsNo)[0];
        if($orderGoodsData['goodsType'] == 'addGoods') {    //추가상품인경우
            $orderGoodsNo = 'A'.$orderGoodsNo;
        }
        $arrBind = [];
        $query = "SELECT count(*) as cnt FROM ".DB_BOARD_EXTRA_DATA." as be INNER JOIN ".DB_BD_.self::$_bdId ." as b ON be.bdSno = b.sno  WHERE be.bdId = ? AND be.orderGoodsNoText = ? ";
        if($isAll === false){
            $query.= " AND b.isDelete = 'n'   ";
        }
        self::$_db->bind_param_push($arrBind, 's', self::$_bdId);
        self::$_db->bind_param_push($arrBind, 'i', $orderGoodsNo);
        if(self::$_cfg['bdId'] == Board::BASIC_GOODS_REIVEW_ID) {
            if($guestOrderNo = \Session::get('guest.orderNo')){
                $query.=" AND b.orderNo = ? AND b.memNo = 0 ";
                self::$_db->bind_param_push($arrBind, 's',  $guestOrderNo);
            }
            else {
                $query.=" AND b.memNo = ?";
                self::$_db->bind_param_push($arrBind, 'i',  \Session::get('member.memNo'));
            }
        }
        else {
            $query.=" AND b.memNo = ?"; //@TODO:주문번호 2개이상 받게 수정될경우 LIKE 문 추가(구분자)
            self::$_db->bind_param_push($arrBind, 'i',  \Session::get('member.memNo'));
        }

        if($bdSno) {
            $query.= ' AND  bdSno != ?';
            self::$_db->bind_param_push($arrBind, 'i', $bdSno);
        }
        $result = self::$_db->query_fetch($query, $arrBind, false);
        return $result['cnt'];
    }

    public function selectListByGroupNo($groupNo, $groupThread = null){
        $arrBind = [];
        $query = "SELECT * FROM " . DB_BD_ . self::$_bdId . " WHERE groupNo = ? AND parentSno > 0";
        self::$_db->bind_param_push($arrBind, 'i', $groupNo);

        // 답변글이 있는 게시글 삭제시, 답변글도 함께 삭제
        if($groupThread) {
            $query .= " AND (INSTR(groupThread, ?) AND groupThread != ?) ";
            self::$_db->bind_param_push($arrBind, 's', $groupThread);
            self::$_db->bind_param_push($arrBind, 's', $groupThread);
        }

        $query .= " ORDER BY groupThread ";
        $result = self::$_db->query_fetch($query, $arrBind);

        return $result;
    }

}
