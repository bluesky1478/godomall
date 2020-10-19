<?php
/**
 * BoardConfig Class
 *
 * @author sj
 * @version 1.0
 * @since 1.0
 * @copyright ⓒ 2016, NHN godo: Corp.
 */

namespace Bundle\Component\Board;

use Component\Database\DBTableField;
use League\Flysystem\Exception;

class BoardConfig
{
    const ECT_ERROR = '%s.ECT_ERROR';
    protected $db;
    public $cfg = [];

    /**
     * 생성자
     * @param $bdId
     * @throws Exception
     * @throws \Exception
     */
    public function __construct($bdId)
    {
        if (!is_object($this->db)) {
            $this->db = \App::load('DB');
        }
        $this->cfg = $this->getConfig($bdId);
    }

    /**
     * getConfig
     *
     * @param $bdId
     * @return string
     * @throws Except
     * @throws \Exception
     */
    public function getConfig($bdId)
    {
        $config = [];
        $arrBind = [];
        $this->db->strField = "*";
        $this->db->strWhere = "bdId=?";
        $this->db->bind_param_push($arrBind, 's', $bdId);

        $query = $this->db->query_complete();
        $strSQL = "SELECT " . array_shift($query) . " FROM " . DB_BOARD . " " . implode(" ", $query);
        $data = $this->db->query_fetch($strSQL, $arrBind, false);

        if (!$data) {
            throw new \Exception(__('존재하지 않는 게시판입니다.'));
        }

        $data['bdListImgWidth'] = 100;
        $data['bdListImgHeight'] = 100;
        if ($data['bdListImageSize']) {
            list($bdListImgWidth, $bdListImgHeight) = explode(INT_DIVISION, $data['bdListImageSize']);
            $data['bdListImgWidth'] = $bdListImgWidth;
            $data['bdListImgHeight'] = $bdListImgHeight;
        }

        $category = explode(STR_DIVISION, $data['bdCategory']);

        // 게시판 말머리 번역
        foreach ($category as $k => $v) {
            $category[$k] = __($v);
        }

        $config['arrCategory'] = array_combine($category, $category);
        unset($category);

        if(\Globals::get('gGlobal.isUse')){
            $mallInfo = \Session::get(SESSION_GLOBAL_MALL);
            $domainPostfix = '';
            if($mallInfo){
                $domainPostfix  = $mallInfo['domainFl'] == 'kr' ? '' : ucfirst($mallInfo['domainFl']);
            }
            if (\Request::isMobile()) {
                $themeSno = $data['mobileTheme'.$domainPostfix.'Sno'];
            } else {
                $themeSno = $data['theme'.$domainPostfix.'Sno'];
            }

        }
        else {
            if (\Request::isMobile()) {
                $themeSno = $data['mobileThemeSno'];
            } else {
                $themeSno = $data['themeSno'];
            }
        }

        $tData = $this->getBoardTheme($themeSno);
        $boardTheme = new BoardTheme();
        $tData['iconImage'] = $tData['iconImageMobile'] = $boardTheme->getIconImageInfo($tData);
        $tData['skinBdMobileFl'] = $tData['bdMobileFl'];
        unset($tData['bdMobileFl']);    //게시판테이블과 중복필드
        $data = array_merge($data, (array)$tData);

        if ($data['bdKind'] == Board::KIND_GALLERY) {
            $data['bdListCnt'] = $data['bdListColsCount'] * $data['bdListRowsCount'];
        } else {
            $data['bdListCnt'] = $data['bdListCount'];
        }

        //상품연동타입
        if ($data['bdGoodsType'] == 'order' || $data['bdGoodsType'] == 'orderDuplication') {
            $data['goodsType'] = 'order';
        } else if ($data['bdGoodsType'] == 'goods') {
            $data['goodsType'] = 'goods';
        } else {
            $data['goodsType'] = 'n';
        }
        $data['orderDuplication'] = $data['bdGoodsType'] == 'orderDuplication' ? 'y' : 'n'; //주문중복허용

        if ($data['bdKind'] == Board::KIND_EVENT) {
            $data['bdAuthWrite'] = 'admin';     //이벤트 게시판 글작성 권한은 무조건 어드민
        }
        $data['bdWidthStyle'] = 'style="width:' . $data['bdWidth'] . $data['bdWidthUnit'] . '""';
        $data['bdHeightStyle'] = 'style="height:' . $data['bdListLineSpacing'] . 'px"';

        if ($data['bdNoticeDisplay'] && strpos($data['bdNoticeDisplay'],STR_DIVISION)!==false) {
            list($data['bdNoticeCount'], $data['bdListInNotice'], $data['bdOnlyMainNotice']) = explode(STR_DIVISION, $data['bdNoticeDisplay']);
        }

        $bdIncludeReplayInSearchType = $data['bdIncludeReplayInSearchType'];
        $data['bdIncludeReplayInSearchType'] = null;
        $data['bdIncludeReplayInSearchType']['front'] = $data['bdIncludeReplayInSearchType']['admin'] = 'n';
        if($data['bdIncludeReplayInSearchFl'] == 'y'){
            $data['bdIncludeReplayInSearchType']['front'] = $bdIncludeReplayInSearchType & 1 ? 'y' : 'n';
            $data['bdIncludeReplayInSearchType']['admin'] = $bdIncludeReplayInSearchType & 2 ? 'y' : 'n';
        }

        $data['bdNoticeCount'] = is_numeric($data['bdNoticeCount']) === false ? 0 : $data['bdNoticeCount'];
        $data['bdListInNotice'] = $data['bdListInNotice'] ? $data['bdListInNotice'] : 'y';
        $data['bdOnlyMainNotice'] = $data['bdOnlyMainNotice'] ? $data['bdOnlyMainNotice'] :'y';

        $config = array_merge($data, $config);
        return gd_htmlspecialchars_stripslashes($config);
    }

    /**
     * 게시판 테마 정보
     * @author sunny
     * @param $sno
     * @return array 해당 게시판 테마 정보
     * @internal param  스킨 고유번호 $themeId
     */
    public function getBoardTheme($sno)
    {
        $arrBind = [];

        $arrField = DBTableField::setTableField('tableBoardTheme');
        $strSQL = 'SELECT ' . implode(', ', $arrField) . ' FROM ' . DB_BOARD_THEME . ' WHERE sno = ? ';
        $this->db->bind_param_push($arrBind, 's', $sno);
        $getData = $this->db->slave()->query_fetch($strSQL, $arrBind, false);
        if (count($getData) > 0) {
            $getData = gd_htmlspecialchars_stripslashes($getData);
        }
        return $getData;
    }
}
