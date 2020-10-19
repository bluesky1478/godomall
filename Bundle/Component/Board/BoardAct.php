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

use Component\Member\MemberAdmin;
use Component\Mileage\Mileage;
use Component\Member\Util\MemberUtil;
use Component\Storage\Storage;
use Component\Database\DBTableField;
use Component\Validator\Validator;
use Core\Base\View\Alert;
use Framework\Database\DB;
use Framework\Debug\Exception\Except;
use Framework\Debug\Exception\RequiredLoginException;
use Framework\Debug\Handler\AlertHandler;
use Framework\Utility\ArrayUtils;
use Framework\Utility\ImageUtils;
use Framework\Utility\UrlUtils;
use League\Flysystem\Exception;
use Request;
use Message;
use Globals;
use Symfony\Component\Console\Exception\InvalidArgumentException;

/**
 * 게시판 처리 Class
 * 글쓰기, 수정, 답변, 삭제
 */
class BoardAct extends \Component\Board\BoardFront
{

    public function __construct($req)
    {
        parent::__construct($req);

        if ($this->req['sno']) {
            if (Validator::number($this->req['sno'], null, null) === false) {
                throw new  \Exception(__('잘못된 형식의 게시글번호 입니다.'));
            }
        }
    }

    public function checkModifyPassword($password)
    {
        $modify = $this->buildQuery->selectOne($this->req['sno']);
        if ($this->checkPassword($modify, $password) === false) {
            return false;
        }

        return true;
    }

    /**
     * setMileage
     *
     * @param $sno
     * @param $reply
     * @return bool
     * @throws \Exception
     */
    protected function setMileage($sno, $reply = null)
    {
        if (\Globals::get('gGlobal.isUse')) { //해외몰 마일리지 사용안함
            $mallBySession = \SESSION::get(SESSION_GLOBAL_MALL);
            if ($mallBySession && $mallBySession['sno'] != DEFAULT_MALL_NUMBER) {
                return false;
            }
        }

        $memNo = $this->member['memNo'];

        // 답변글 작성 시에도 마일리지 지급
        if ($this->cfg['bdReplyMileageFl'] == 'y' && $reply == 'y') {
            $mileageReason = "답변글 작성";
        } else {
            $mileageReason = "게시글 작성";
        }

        if (Globals::get('gSite.member.mileageBasic')['payUsableFl'] == 'y' && $this->cfg['bdMileageFl'] == 'y') {
            if ($this->cfg['bdMileageAmount'] > 0) {
                $mileage = new Mileage();
                $result = $mileage->setMemberMileage($memNo, $this->cfg['bdMileageAmount'], Mileage::REASON_CODE_GROUP . Mileage::REASON_CODE_WRITE_BOARD, 'b', $this->cfg['bdId'], $sno);
                if ($result) {
                    $arrBind = [];
                    $this->db->bind_param_push($arrBind, 'i', $this->cfg['bdMileageAmount']);
                    $this->db->bind_param_push($arrBind, 's', __($mileageReason));
                    $this->db->bind_param_push($arrBind, 'i', $sno);
                    $this->db->set_update_db(DB_BD_ . $this->cfg['bdId'], 'mileage = ? , mileageReason = ? ', 'sno = ?', $arrBind);
                    return $this->cfg['bdMileageAmount'];
                }
            }
        }

        return false;
    }

    /**
     * 게시글 삭제
     *
     * @param $sno
     * @return string
     * @throws Exception
     * @throws \Exception
     */
    public function deleteData($sno)
    {
        $this->checkAntiSpam();

        $data = $this->buildQuery->selectOne($sno);
        if ($data == null) {
            throw new \Exception(__('글이 존재하지 않습니다.'));
        }

        switch ($this->canRemove($data)) {
            case 'y':
                break;
            case 'c':
                $writerPw = md5(gd_isset($this->req['writerPw']));
                if ($data['writerPw'] != $writerPw) {
                    throw new \Exception(__('비밀번호가 일치하지 않습니다.'));
                }
                break;
            default:
                throw new \Exception(__('권한이 없습니다.'));
                break;
        }

        if (Globals::get('gSite.member.mileageBasic')['payUsableFl'] == 'y' && $this->cfg['bdMileageFl'] == 'y') {
            if ($this->cfg['bdMileageDeleteFl'] == 'y') {
                $mileageInfo = $this->db->getData(DB_MEMBER, $data['memNo'], 'memNo', 'mileage, memNm');
                $mileageInfo = gd_htmlspecialchars_stripslashes($mileageInfo);
                $ownMileage = $mileageInfo['mileage'];   //현재 마일리지

                $minusMileage = $data['mileage'] * -1; //차감할 마일리지
                if ($ownMileage < $data['mileage']) {   //차감할 마일리지가 보유마일리지보다 크면
                    if ($this->cfg['bdMileageLackAction'] == 'nodelete') {
                        throw new Exception(__('지급된 마일리지를 차감할 수 없어 해당 게시글 삭제가 불가능 합니다'));
                    } else if ($this->cfg['bdMileageLackAction'] == 'delete') {  //마이너스 차감 후 게시글 삭제

                    }
                }
                $mileage = new Mileage();
                $mileage->setMemberMileage($data['memNo'], $minusMileage, Mileage::REASON_CODE_GROUP . Mileage::REASON_CODE_REMOVE_BOARD, 'b', $this->cfg['bdId'], $sno);
            }
        }

        if (!$data['parentSno']) {   //부모글이면 자식글 삭제
            if ($this->cfg['bdReplyDelFl'] == 'reply') {
                $childData = $this->getChildList($data['groupNo']);
                if ($childData) {
                    if (count($childData) > 26) {
                        throw new \Exception(sprintf(__('자식글 갯수(%1$s) 초과오류'), count($childData)));
                    }
                    foreach ($childData as $row) {
                        $this->buildQuery->updateDelete($row['sno']);
                    }
                }
            }
        } else {
            // 답변글이 있는 게시글 삭제시, 답변글도 함께 삭제
            if ($this->cfg['bdReplyDelFl'] == 'reply') {
                $childData = $this->getChildList($data['groupNo'], $data['groupThread']);
                if ($childData) {
                    if (count($childData) > 26) {
                        throw new \Exception(sprintf(__('자식글 갯수(%1$s) 초과오류'), count($childData)));
                    }
                    foreach ($childData as $row) {
                        $this->buildQuery->updateDelete($row['sno']);
                    }
                }
            }
        }

        if ($this->buildQuery->updateDelete($sno)) {

            //상품테이블 상품평 카운팅 리셋
            if ($this->cfg['bdId'] == Board::BASIC_GOODS_REIVEW_ID) {
                $goods = \App::load('\\Component\\Goods\\Goods');
                $goods->setRevicwCount($data['goodsNo'], true);
            }

            return 'ok';
        }
        return 'fail';
    }

    /**
     * 수정 전 처리해야할 액션
     *
     * @param $data
     * @throws \Exception
     */
    protected function handleBeforeModify($data)
    {
        if ($this->req['oldPassword']) {
            if ($this->checkPassword($data, $this->req['oldPassword'], false) === false) {
                throw new \Exception(parent::TEXT_NOTMATCH_PASSWORD);
            }
        } else {
            if ($this->checkPassword($data, $this->req['writerPw']) === false) {
                throw new \Exception(parent::TEXT_NOTMATCH_PASSWORD);
            }
        }
    }

    /**
     * 글작성 후 처리해야하는 액션
     *
     * @param $data
     * @param null $msgs
     */
    protected function handleAfterWrite($data, &$msgs = null)
    {
        if ($mileage = $this->setMileage($data['sno'])) {
            $msgs = sprintf(__('%1$s 마일리지가 적립되었습니다.'), $mileage);
        }

        //상품테이블 상품평 카운팅
        if ($this->cfg['bdId'] == Board::BASIC_GOODS_REIVEW_ID) {
            $goods = \App::load('\\Component\\Goods\\Goods');
            $goods->setRevicwCount($data['goodsNo']);
        }

        //본사,공급사로 전송
        $scmNo = $data['scmNo'] ?? DEFAULT_CODE_SCMNO;
        $receiveData = ['scmNo' => $scmNo, 'memNo' => $data['memNo'], 'writerNm' => $data['writerNm'], 'cellPhone' => null];
        $aBasicInfo = gd_policy('basic.info');
        BoardUtil::sendSms($this->cfg['bdId'], $receiveData, ['rc_mallNm' => $data['writerNm'], 'shopName' => Globals::get('gMall.mallNm'), 'wriNm' => Globals::get('gMall.mallNm'), 'shopUrl' => $aBasicInfo['mallDomain']], 'admin');
        BoardUtil::sendSms($this->cfg['bdId'], $receiveData, ['rc_mallNm' => $data['writerNm'], 'shopName' => Globals::get('gMall.mallNm'), 'wriNm' => Globals::get('gMall.mallNm'), 'shopUrl' => $aBasicInfo['mallDomain']], 'provider');
    }

    /**
     * 답글 후 처리해야할 액션
     *
     * @param $data
     * @param $msgs
     * @return bool
     */
    protected function handleAfterReply($data, &$msgs)
    {
        if ($mileage = $this->setMileage($data['sno'], 'y')) {
            $msgs = sprintf(__('%1$s 마일리지가 적립되었습니다.'), $mileage);
        }
        return $mileage;
    }

    /**
     * 추천
     *
     * @param $sno
     * @throws \Exception
     */
    public function recommend($sno)
    {
        if (!MemberUtil::isLogin()) {
            throw new RequiredLoginException();
        }
        $arrBind = [];
        $query = "SELECT COUNT(*) AS cnt FROM  es_boardRecommend WHERE bdId=? and bdSno=? and memNo=? ";
        $this->db->bind_param_push($arrBind, 's', $this->cfg['bdId']);
        $this->db->bind_param_push($arrBind, 'i', $sno);
        $this->db->bind_param_push($arrBind, 'i', $this->member['memNo']);
        $data = $this->db->query_fetch($query, $arrBind, false);

        if ($data['cnt'] > 0) {
            throw new \Exception(__('이미 추천하셨습니다.'));
        }
        $data = null;
        unset($arrBind);
        $validator = new Validator();
        $this->setSaveData('bdId', $this->cfg['bdId'], $arrData, $validator);
        $this->setSaveData('bdSno', $sno, $arrData, $validator);
        $this->setSaveData('memNo', $this->member['memNo'], $arrData, $validator);
        $this->setSaveData('writerIp', Request::getRemoteAddress(), $arrData, $validator);
        if ($validator->act($arrData, true) === false) {
            throw new \Exception($validator->errors);
        }

        $arrBind = $this->db->get_binding(DBTableField::tableBoardRecommend(), $arrData, 'insert', array_keys($arrData));
        $this->db->set_insert_db('es_boardRecommend', $arrBind['param'], $arrBind['bind'], 'y');
        unset($arrBind);

        $data = $this->buildQuery->selectOne($sno, false);
        $this->db->bind_param_push($arrBind['sno'], 'i', $sno);
        if (!$data['recommend']) {
            $this->db->set_update_db(DB_BD_ . $this->cfg['bdId'], 'recommend = 1', 'sno = ?', $arrBind['sno']);
        } else {
            $this->db->set_update_db(DB_BD_ . $this->cfg['bdId'], 'recommend = recommend+1', 'sno = ?', $arrBind['sno']);
        }

        return $data['recommend'] + 1;

    }

    /**
     * 말머리 양식 글
     *
     * @param $data
     * @return string
     */
    public function getBdCategoryTemplate($data)
    {
        //return $this->cfg;
        $templateSno = 0;
        $bdCategory = explode(STR_DIVISION, gd_isset($this->cfg['bdCategory']));
        $bdCategoryTemplateSno = explode(STR_DIVISION, gd_isset($this->cfg['bdCategoryTemplateSno']));
        foreach($bdCategory as $key => $val){
            if($data['category'] == $val) $templateSno = $bdCategoryTemplateSno[$key]; // 말머리 양식 Sno
        }

        if($templateSno == 0) { // 말머리 양식이 없을 때
            $templateSno = $this->cfg['bdTemplateSno']; // 게시판 게시글 양식 Sno
        }

        $returnTemplateContents = '';
        if($templateSno > 0) {
            $boardTemplate = new BoardTemplate();
            $templateContents = $boardTemplate->getData($templateSno, 'front')['contents'];
            $returnTemplateContents = $templateContents;
            if ($this->cfg['bdEditorFl'] == 'n' || \Request::isMobile()) {
                $returnTemplateContents = str_replace(["</p>", "<br>", "</br>"], "\n", $templateContents);
                $returnTemplateContents = str_replace(["&nbsp;"], " ", $returnTemplateContents);
                $returnTemplateContents = strip_tags($returnTemplateContents);
            }
        }
        return $returnTemplateContents;
    }
}
