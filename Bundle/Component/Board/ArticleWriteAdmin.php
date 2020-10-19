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

namespace Bundle\Component\Board;

use Framework\Utility\ArrayUtils;

class ArticleWriteAdmin Extends \Component\Board\ArticleAdmin
{
    /**
     * 생성자
     */
    public function __construct($req)
    {
        parent::__construct($req);
        gd_isset($this->req['mode'], 'write');
        if (in_array($this->req['mode'], array('modify', 'reply')) && (!is_numeric($this->req['sno']))) {
            throw new \Exception(sprintf(parent::TEXT_INVALID_ARG, __('게시물 번호')));
        }
    }

    /**
     * 글수정
     * @return array data
     */
    private function getModify()
    {
        $data = $this->buildQuery->selectOne($this->req['sno']);
        $data['canWriteGoodsSelect'] = ($this->canWriteGoodsSelect($data)) ? 'y' : 'n';
        $data['canWriteOrderSelect'] = ($this->canWriteOrderSelect($data)) ? 'y' : 'n';
        $data['extraData'] = $this->getExtraData($this->req['sno']);
        if (isset($data['saveFileNm']) && strlen(trim($data['saveFileNm'])) > 0) {
            $tmp = explode(STR_DIVISION, $data['saveFileNm']);
            unset($data['saveFileNm']);
            if (ArrayUtils::isEmpty($tmp) === false) {
                $data['saveFileNm'] = $tmp;
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

        if (isset($data['goodsNo'])) {
            $data['goodsData'] = $this->setGoodsDataPool($data['goodsNo']);
        }
        $data['memo'] = $this->getMemo($this->req['sno'], true);
        $data['writer'] = $this->getWriterInfo($data);
        return gd_isset(gd_htmlspecialchars_stripslashes($data));
    }

    /**
     * 쓰기
     */
    public function getWrite($data = null)
    {
        $data['memNo'] = (isset($this->member['memNo'])) ? $this->member['memNo'] : '';
        $data['writerId'] = (isset($this->member['memId'])) ? $this->member['memId'] : '';
        $data['writerNm'] = (isset($this->member['memNm'])) ? $this->member['memNm'] : '';
        $data['writer'] = $data['writerId'] . '(' . $data['writerNm'] . ')';
        $data['answerManagerWriter'] = $this->getWriterInfo($data, 'answerManagerNo');
        $data['category'] = gd_isset($this->req['category']);
        $data['canWriteGoodsSelect'] = ($this->canWriteGoodsSelect()) ? 'y' : 'n';
        $data['canWriteOrderSelect'] = ($this->canWriteOrderSelect()) ? 'y' : 'n';
        return $data;
    }

    public function getData()
    {
        $data = null;
        $pData = null;

        switch ($this->req['mode']) {
            case 'modify': {
                $data = $this->getModify();
                if (gd_isset($data['pSno'])) {
                    $pData = $this->getModify($data['pSno']);
                }
                $data['auth'] = $this->canModify($data);
                break;
            }
            case 'reply': {
                $pData = $this->getModify();
                $data = $this->getWrite();
                $data['auth'] = $this->canWrite('r', $pData);
                $data['subject'] = 'RE : ' . $pData['subject'];
                $data['canWriteGoodsSelect'] = ($this->canWriteGoodsSelect($data)) ? 'y' : 'n';
                $data['canWriteOrderSelect'] = ($this->canWriteOrderSelect($data)) ? 'y' : 'n';
                $data['isSecret'] = $pData['isSecret']; // 부모글이 비밀글이면 답글도 비밀글
                if ($this->cfg['bdGoodsFl'] == 'y' && empty($pData['goodsNo']) === false) {
                    if ($pData['goodsNo']) {
                        $data['goodsData'] = $this->setGoodsDataPool($pData['goodsNo']);
                    }
                }
                break;
            }
            case 'write': {
                $data = $this->getWrite();
                $data['auth'] = $this->canWrite();
                break;
            }
        }
        $data['checked'] = $this->getChecked($this->req['mode'], $data);
        if($this->req['mode'] == 'write'){
            $this->getinitSecretData($this->req['mode'],$data);
        }

        if ($data['auth'] == 'n') {
            throw new \Exception(__('권한이 없습니다.'));
        }

        if ($this->cfg['bdUploadMaxSize'] == '') {
            $this->cfg['bdUploadMaxSize'] = str_replace('M', '', parent::UPLOAD_DEFAULT_MAX_SIZE);
        }

        $this->cfg['bdStrMaxSize'] = $this->cfg['bdUploadMaxSize'] . 'M';

        if (gd_isset($data)) $getData['data'] = $data;
        if (gd_isset($pData)) $getData['pData'] = $pData;

        return $getData;
    }

    public function getReplyTemplate()
    {
        $arrBind = [];
        $this->db->strField = " * ";
        $this->db->strWhere = " bdId in ('', ?)";
        $this->db->bind_param_push($arrBind, 's', $this->cfg['bdId']);

        $query = $this->db->query_complete();
        $strSQL = "SELECT " . array_shift($query) . " FROM " . DB_BOARD_TEMPLATE . " " . implode(" ", $query);
        $data = $this->db->query_fetch($strSQL, $arrBind);
        unset($arrBind);

        return gd_htmlspecialchars_stripslashes($data);
    }

    public function getChecked($mode, $data)
    {
        $checked = array();
        switch($mode){
            case 'modify':
            case 'reply':
                if($data['isSecret'] === 'y'){
                    $checked['isSecret'] = "checked='checked'";
                }
                break;
            case 'write': default :
                break;
        }

        return $checked;
    }

    /*
     *  게시판 비밀글 설정에 따른 게시글 등록 시 초기 비밀글 설정
     */
    public function getinitSecretData($mode,&$data){
        if($mode == 'write'){
            if($this->cfg['bdSecretFl'] == 3 || $this->cfg['bdSecretFl'] == 1)  {
                $data['isSecret'] = 'y';
                $data['checked'] = [
                    'isSecret' => "checked='checked'",
                ];
            } else {
                $data['isSecret'] = 'n';
                $data['checked'] = [];
            }
        }
    }
}
