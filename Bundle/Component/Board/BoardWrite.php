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
 * 게시판 글쓰기/수정/답변 Class
 */
namespace Bundle\Component\Board;

use Component\Member\Util\MemberUtil;
use Framework\Debug\Exception\RequiredLoginException;
use Framework\Utility\ArrayUtils;

class BoardWrite extends \Component\Board\BoardFront
{

    /**
     * 생성자
     * @param $req
     * @throws Except
     * @throws \Exception
     */
    public function __construct($req)
    {
        parent::__construct($req);
        gd_isset($this->req['mode'], 'write');

        if (in_array($this->req['mode'], array('modify', 'reply')) && (!is_numeric($this->req['sno']))) {
            throw new \Exception(__('잘못된 접근입니다.'));
        }
    }

    /**
     * 글수정시 필요한 글정보가져오기
     * @return array data
     * @throws \Exception
     */
    private function getModify()
    {
        $data = $this->buildQuery->selectOne($this->req['sno']);
        $data['canWriteGoodsSelect'] = ($this->canWriteGoodsSelect($data)) ? 'y' : 'n';
        $data['canWriteOrderSelect'] = ($this->canWriteOrderSelect($data)) ? 'y' : 'n';
        $data['extraData'] = $this->getExtraData($this->req['sno']);
        if ($data == null) {
            throw new \Exception(__('존재하지 않는 게시글입니다.'));
        }

        if (isset($data['saveFileNm']) && strlen(trim($data['saveFileNm'])) > 0) {
            $tmp = explode(STR_DIVISION, $data['saveFileNm']);
            unset($data['saveFileNm']);
            if (ArrayUtils::isEmpty($tmp) === false) {
                $data['saveFileNm'] = $tmp;
                foreach ($data['saveFileNm'] as $val) {
                    $saveHttpUrl[] = $this->storage->getHttpPath($data['bdUploadPath'] . $val);
                }
                $data['saveHttpUrl'] = $saveHttpUrl;
            }
            unset($tmp);
        }

        $data['upfilesCnt'] = 0;
        if (isset($data['uploadFileNm']) && strlen(trim($data['uploadFileNm'])) > 0) {
            $tmp = explode(STR_DIVISION, $data['uploadFileNm']);
            unset($data['uploadFileNm']);
            if (ArrayUtils::isEmpty($tmp) === false) {
                $data['upfilesCnt'] = count($tmp);
                $data['uploadFileNm'] = $tmp;
            }
            unset($tmp);
        }

        if ($data['goodsNo']) {
            $data['goodsData'] = $this->setGoodsDataPool($data['goodsNo']);
        }

        return $data;
    }

    /**
     * 답글쓰기시 필요한 정보 가져오기
     *
     * @return array data
     */
    private function getReply()
    {
        $data = $this->getWrite();
        $parentData = $this->buildQuery->selectOne($this->req['sno']);;
        $data['isSecret'] = $parentData['isSecret'];
        $data['canWriteGoodsSelect'] = ($this->canWriteGoodsSelect($data)) ? 'y' : 'n';
        $data['canWriteOrderSelect'] = ($this->canWriteOrderSelect($data)) ? 'y' : 'n';

        return $data;
    }

    /**
     * 글쓰기시 필요한 정보 가져오기
     *
     * @return array data
     */
    private function getWrite()
    {
        $data = null;
        $arrBind = [];
        if (isset($this->member)) {
            $data['memNo'] = (isset($this->member['memNo'])) ? $this->member['memNo'] : '';
            $data['writerId'] = (isset($this->member['memId'])) ? $this->member['memId'] : '';
            $data['writerNm'] = (isset($this->member['memNm'])) ? $this->member['memNm'] : '';
            if (($this->cfg['bdEmailFl'] == 'y' || $this->cfg['bdMobileFl'] == 'y')) {
                $this->db->bind_param_push($arrBind, 'i', $this->member['memNo']);
                $memInfo = gd_htmlspecialchars_stripslashes($this->db->query_fetch("SELECT email, cellPhone FROM " . DB_MEMBER . " WHERE memNo = ?", $arrBind, false));
                $data['writerEmail'] = gd_isset($memInfo['email']);
                $data['writerMobile'] = gd_isset($memInfo['cellPhone']);
            }
        } else {
            $this->cfg['bdPrivateYN'] = 'y';
        }
        $data['category'] = gd_isset($this->req['category']);
        $data['canWriteGoodsSelect'] = ($this->canWriteGoodsSelect()) ? 'y' : 'n';
        $data['canWriteOrderSelect'] = ($this->canWriteOrderSelect()) ? 'y' : 'n';
        $data['contents'] = $this->cfg['templateContents'];
        return $data;
    }

    /**
     * 글작성시 필요한 정보가져오기(글쓰기, 답글쓰기, 글수정)
     * @return array data
     * @throws Except
     * @throws \Exception
     */
    public function getData()
    {
        $this->cfg['bdPrivateYN'] = 'n';

        $data = null;
        switch ($this->req['mode']) {
            case 'modify': {
                $data = $this->getModify();
                $data['auth'] = $this->canModify($data);
                break;
            }
            case 'reply': {
                $data = $this->getReply();
                $data['auth'] = $this->canWrite('r', $data);
                break;
            }
            case 'write': {
                $data = $this->getWrite();
                $data['auth'] = $this->canWrite();
                break;
            }
        }

        if ($data['auth'] == 'n') {
            if (MemberUtil::isLogin() === false) {
                throw new RequiredLoginException();
            }

            if ($this->cfg['bdAuthWrite'] == 'buyer') {
                throw new \Exception(__('구매자만 작성 가능합니다.'));
            }
            throw new \Exception(__('접근 권한이 없습니다.'));
        }

        $check = $this->checkReviewPossible();
        if($check['requireLogin'] == true){
            throw new RequiredLoginException($check['errorMsg']);
        }
        else if($check['errorMsg']) {
            throw new \Exception($check['errorMsg']);
        }

        if ($this->cfg['bdUploadMaxSize'] == '') {
            $this->cfg['bdUploadMaxSize'] = str_replace('M', '', ini_get('upload_max_filesize'));
        }
        $this->cfg['bdStrMaxSize'] = $this->cfg['bdUploadMaxSize'] . 'M';
        return gd_htmlspecialchars_stripslashes($data);
    }
}
