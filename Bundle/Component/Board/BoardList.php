<?php

/**
 * This is commercial software, only users who have purchased a valid license
 * and accept to the terms of the License Agreement can install and use this
 * program.
 *
 * Do not edit or add to this file if you wish to upgrade Smart to newer
 * versions in the future.
 *
 * @copyright ⓒ 2016, NHN godo: Corp.
 * @link http://www.godo.co.kr
 */

/**
 * 게시판 리스트 Class
 */
namespace Bundle\Component\Board;

use Component\Database\DBTableField;
use Component\Goods\Goods;
use Component\Member\Util\MemberUtil;
use Component\Order\Order;
use Component\Page\Page;
use Component\Storage\Storage;
use Framework\Debug\Exception\RequiredLoginException;
use Framework\StaticProxy\Proxy\Request;
use Framework\Utility\ArrayUtils;
use Framework\Utility\FileUtils;
use Framework\Utility\ImageUtils;
use Framework\Utility\SkinUtils;
use Framework\Utility\StringUtils;

class BoardList extends \Component\Board\BoardFront
{
    public function __construct($req)
    {
        parent::__construct($req);

        gd_isset($this->req['page'], 1);

        // 값이 없는 경우 기본설정
        gd_isset($this->cfg['bdListCols'], 5);
        gd_isset($this->cfg['bdListRows'], 4);
        if ($this->cfg['bdKind'] == Board::KIND_GALLERY) {
            gd_isset($this->cfg['bdListImgWidth'], 100);
            gd_isset($this->cfg['bdListImgHeight'], '');
        } else {
            gd_isset($this->cfg['bdListImgWidth'], 45);
            gd_isset($this->cfg['bdListImgHeight'], 45);
        }
    }

    /**
     * 검색폼의 체크상태 가져오기
     *
     * @return array
     */
    public function getChecked()
    {
        $checked['key'] = array('writerNm' => '', 'subject' => '', 'contents' => '');

        if (empty($this->req['key']) === false) {
            foreach ($this->req['key'] as $key => $val) {
                $checked['key'][$val] = ' checked="checked" ';
            }
        } else {
            $this->req['key'][0] = 'subject';
            $checked['key']['subject'] = ' checked="checked" ';
        }

        return $checked;
    }

    /**
     * getList
     *
     * @param bool $isPaging 페이징여부
     * @param int $listCount 출력 게시글 수
     * @param int $subjectCut 제목길이( 0 이면 모두노출)
     * @param array $arrWhere
     * @param null $arrInclude (출력필드 . 값이 없으면 모든정보 노출)
     * @param bool $displayNotice (공지사항 출력여부)
     *
     * @return mixed
     * @throws RequiredLoginException
     * @throws \Exception
     */
    public function getList($isPaging = true, $listCount = 0, $subjectCut = 0, $arrWhere = [], $arrInclude = null, $displayNotice = true)
    {
        if($this->req['self'] == 'y') {
            $this->req['memNo'] = $this->member['memNo'];
        }

        if (empty($this->req['memNo']) === false) { //마이페이지 접근 시 회원고유값 체크
            if ($this->req['memNo'] != $this->member['memNo']) {
                throw new \Exception(__('권한이 없습니다.'));
            }
        }

        if ($listCount == null) {
            if ($this->cfg['bdKind'] == Board::KIND_GALLERY) {  //유형이 겔러리일때 노출타입 가로*세로 계산
                $listCount = $this->cfg['bdListColsCount'] * $this->cfg['bdListRowsCount'];
                $this->cfg['bdListCount'] = $listCount;
            } else {
                $listCount = $this->cfg['bdListCount'];
            }
        }

        if ($this->cfg['bdKind'] == Board::KIND_GALLERY && $this->req['gboard'] != 'y' && !$this->req['goodsNo']) {
            $arrWhere[] = "groupThread = '' ";
        }

        if ($this->cfg['bdListInNotice'] == 'n') {
            $this->req['isNotice'] = 'n';
        }

        $data = parent::getList($isPaging, $listCount, $subjectCut, $arrWhere, $arrInclude, $displayNotice);
        //이벤트일경우 종료일 카운트
        if ($this->cfg['bdEventFl'] == 'y') {
            $totalEventCount = $this->buildQuery->selectCount([" isDelete = 'n' "]);
            $currentEventCount = $this->buildQuery->selectCount([" isDelete = 'n' "], ["  now() BETWEEN eventStart AND eventEnd "]);
            $endEventCount = $this->buildQuery->selectCount([" isDelete = 'n' "], ["  now() >  eventEnd "]);

            $data['cnt']['totalEventCount'] = $totalEventCount;
            $data['cnt']['currentEventCount'] = $currentEventCount;
            $data['cnt']['endEventCount'] = $endEventCount;
        }

        return $data;
    }

    /**
     * getGoodsPageListNum
     * 상품상세페이지에서의 상품후기, 상품문의 페이지 개수 반환
     * @param string $mode
     * @param array $dataArray
     *
     * @return string
     */
    public function getGoodsPageListNum($mode, $dataArray)
    {
        gd_isset($mode, 'pc');
        $returnData = array('pc'=>10, 'mobile'=>5);
        $cfgArray = array('pc'=>'bdGoodsPageCountPc', 'mobile'=>'bdGoodsPageCountMobile');

        if($dataArray['gboard'] === 'y' && (int)$dataArray['goodsNo'] > 0){
            if($dataArray['bdId'] === Board::BASIC_GOODS_REIVEW_ID || $dataArray['bdId'] === Board::BASIC_GOODS_QA_ID){
                $returnData[$mode] = $this->cfg[$cfgArray[$mode]];
            }
        }

        return $returnData[$mode];
    }
}
