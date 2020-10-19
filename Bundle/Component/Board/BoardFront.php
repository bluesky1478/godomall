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

use Component\Storage\Storage;
use Component\Member\Manager;
use Component\Member\Member;
use Component\Member\Util\MemberUtil;
use Component\Order\Order;
use Framework\Utility\DateTimeUtils;
use Framework\Utility\FileUtils;
use Framework\Utility\SkinUtils;
use Request;
use Session;

class BoardFront extends \Component\Board\Board
{
    public function __construct($req)
    {
        if (Session::has('member.memNo')) {
            $this->member = ['memNo' => Session::get('member.memNo'), 'memNm' => Session::get('member.memNm'), 'memId' => Session::get('member.memId'), 'groupSort' => Session::get('member.groupSort'), 'memNick' => Session::get('member.nickNm'), 'groupSno' => Session::get('member.groupSno')];
        }
        parent::__construct($req);
    }

    protected function isAdmin()
    {
        return false;
    }

    /**
     * getWriterInfo
     *
     * @param $data
     * @param string $refManagerField
     * @return string
     * @throws \Exception
     * @internal param string $refManagerSno
     */
    public function getWriterInfo($data, $refManagerField = 'memNo')
    {
        $rtnData = null;
        $isAdmin = false;
        if($data['channel'] == 'naverpay') {
            return '네이버페이 구매자';
        }
        if ($data[$refManagerField] < 0 || $data['memNo'] < 0) {   //관리자
            $query = "SELECT scmNo,managerNickNm,dispImage  FROM " . DB_MANAGER . " WHERE sno = " . abs($data[$refManagerField]);
            $managerData = $this->db->query_fetch($query, null, false);
            if ($managerData['scmNo'] == DEFAULT_CODE_SCMNO) {
                if ($this->cfg['bdAdminDsp'] == 'nick') {
                    $result = $managerData['managerNickNm'];
                } else if ($this->cfg['bdAdminDsp'] == 'image') {
                    $result = "<img src='" . $managerData['dispImage'] . "' />";
                } else {
                    $result = $managerData['managerNickNm'];
                }
            } else {
                if ($this->cfg['bdSupplyDsp'] == 'nick') {
                    $result = $managerData['managerNickNm'];
                } else if ($this->cfg['bdSupplyDsp'] == 'image') {
                    $result = "<img src='" . $managerData['dispImage'] . "' />";
                } else {
                    $result = $managerData['managerNickNm'];
                }
            }

            if (!$result) {
                $result = __('관리자');
            }

            return $this->xssClean($result);

        } else if ($data['memNo'] > 0) {    //정회원
            if ($this->cfg['bdUserDsp'] == 'nick') {
                if ($data['writerNick']) {
                    $result = $data['writerNick'];
                } else {  //닉네임이 없으면? TODO:
                    $result = $data['writerNm'];
                }
            } else if ($this->cfg['bdUserDsp'] == 'id') {
                $result = $data['writerId'] ?? $data['writerNm'];
            } else {
                $result = $data['writerNm'];
            }
        } else {    //비회원
            $result = $data['writerNm'];
        }

        if ($isAdmin === false) {
            $star = '';
            if ($this->cfg['bdUserLimitDsp']) {
                if (iconv_strlen($result, SET_CHARSET) > $this->cfg['bdUserLimitDsp']) {
                    $starCnt = iconv_strlen($result, SET_CHARSET) - $this->cfg['bdUserLimitDsp'];
                } else {
                    $starCnt = 0;
                }

                for ($i = 0; $i < $starCnt; $i++) {
                    $star .= '*';
                }

                $restWriterNm = iconv_substr($result, 0, $this->cfg['bdUserLimitDsp'], SET_CHARSET);
                $result = $restWriterNm . $star;
            }
        }

        return $this->xssClean(strip_tags($result));
    }

    public function canList()
    {
        return $this->canConfigCheck($this->cfg['bdAuthList'], $this->cfg['bdAuthListGroup']) ? 'y' : 'n';
    }

    public function canRead($data)
    {
        if ($data['isDelete'] == 'y') {
            return 'n';
        }

        $canAccess = $this->canConfigCheck($this->cfg['bdAuthRead'], $this->cfg['bdAuthReadGroup']);
        if ($canAccess === false) {
            return 'n';
        }

        if (MemberUtil::isLogin()) {
            if ($data['memNo'] == $this->member['memNo']) {
                return 'y';
            }
        }

        if ($data['isSecret'] == 'y') {  //비밀글이면
            $memNo = $data['memNo'];
            if (empty($data['groupThread']) === false) {
                $memNo = $this->buildQuery->selectOne($data['parentSno'])['memNo'];
            }
            if ($memNo == 0) {  //대상글이 비회원글이면
                if (MemberUtil::isLogin()) {
                    return 'n';
                }
                return 'c';
            }

            if ($data['memNo'] > 0) {    //로긴글이면
                if ($memNo == $this->member['memNo'] && MemberUtil::isLogin()) {   //부모글의 작성자의sno 와 로긴회원의 sno를 비교
                    return 'y';
                }
                return 'n';
            } else {  //비회원 또는 관리자 비밀글
                if ($memNo == $this->member['memNo'] && MemberUtil::isLogin()) {   //부모글의 작성자의sno 와 로긴회원의 sno를 비교
                    return 'y';
                }
            }
            return 'n';
        }

        return 'y';
    }

    /**
     * 비밀 댓글 읽을 수 있는 권한 체크
     * @return array
     */
    public function canReadSecretReply(&$data)
    {
        if (gd_isset($data)) {
            $data['allReplyShow'] = 'n';
            $isLogin = false;
            if(MemberUtil::isLogin()) {
                $isLogin = true;
            }
            //비회원 비밀글일때 이미 게시글 진입할 시 비밀번호 인증했으므로 모든 댓글 보이게함
            if (($data['memNo'] == $this->member['memNo'] && $isLogin) || ($data['isSecret'] == 'y' && $data['memNo'] < 1)) {
                 $data['allReplyShow'] = 'y';
            } else {
                $memoList = &$data['memoList'];
                foreach ($memoList as $key => &$val) {
                    $result = '';
                    if ($val['isSecretReply'] == 'y') {  //비밀 댓글이면
                        if ($isLogin) {
                            if ($val['memNo'] == $this->member['memNo']) {
                                $result = 'y';
                            } else {
                                $result = 'n';
                            }
                        }
                        $val['isShowSecretReply'] = $result;
                    }
                }
            }
        }

        return $data;
    }
    /**
     * 비밀 댓글 체크박스 컨트롤
     * @return array
     */
    public function setSecretReplyView($boardCfg)
    {
        $result = array();
        $secretReplyType = $boardCfg['bdSecretReplyFl'];

        //비밀댓글 체크박스
        switch ($secretReplyType) {
            case '1' :
                $result['replyWrite'] = '<input type="checkbox" id="secretReplyWrite" name="isSecretReply" class="checkbox sp" checked="checked"><label for="secretReplyWrite" class="on">'.__('비밀댓글').'</label>';
                $result['replyModify'] = '<input type="checkbox" id="secretReplyModify" name="isSecretReply" class="checkbox sp" checked="checked"><label for="secretReplyModify" class="on">'.__('비밀댓글').'</label>';
                break;
            case '2' :
                $result = '';
                break;
            case '3' :
                $result['replyWrite'] = '<input type="hidden" name="isSecretReply" value="y">'.__('해당글은 비밀댓글로만 작성이 됩니다.');
                $result['replyModify'] = '<input type="hidden" name="isSecretReply" value="y">'.__('해당글은 비밀댓글로만 작성이 됩니다.');
                break;
            default:
                $result['replyWrite'] = '<input type="checkbox" id="secretReplyWrite" name="isSecretReply" class="checkbox sp"><label for="secretReplyWrite">'.__('비밀댓글').'</label>';
                $result['replyModify'] = '<input type="checkbox" id="secretReplyModify" name="isSecretReply" class="checkbox sp"><label for="secretReplyModify">'.__('비밀댓글').'</label>';
                break;
        }

        //비밀댓글 영역
        if (is_null($boardCfg['bdSecretReplyTitle']) === true || empty($boardCfg['bdSecretReplyTitle']) === true) { $secretReplyTitle = '비밀 댓글입니다.'; }
        else { $secretReplyTitle = $boardCfg['bdSecretReplyTitle']; }
        $result['secretReplyTitle'] = $secretReplyTitle;

        return $result;
    }

    protected function canConfigCheck($auth, $groupAuth)
    {
        switch ($auth) {
            case 'all' :
                return true;
            case 'buyer' :
                if (MemberUtil::isLogin() === false) {
                    return false;
                }
                $goodsNo = is_array($this->req['goodsNo']) ? $this->req['goodsNo'][0] : $this->req['goodsNo'];
                if (!$goodsNo) {
                    return true;
                }
                $order = new Order();
                $orderGoodsData = $order->getOrderGoodsByGoodsNo(null, $goodsNo, null, null, null, $this->member['memNo'], 's1'); //구매확정
                if ($orderGoodsData === false) {
                    return ['result' => false, 'msg' => __('해당 상품의 주문내역이 없습니다.')];
                }
                return true;

                break;
            case 'admin' :
                return false;
            case 'member' :
                return MemberUtil::isLogin();
            case 'group' :
                if (MemberUtil::isLogin() === false) {
                    return false;
                }
                $arrBdAuthWriteGroup = explode(INT_DIVISION, $groupAuth);
                $memberData = MemberUtil::getMemberBySession();
                if (!$memberData) {
                    return false;
                }
                if (in_array($memberData['groupSno'], $arrBdAuthWriteGroup)) {
                    return true;
                }
        }

        return false;
    }

    /**
     * canWrite
     *
     * @param string $mode w : 일반글 , r : 답글 , m : 댓글
     * @param null $parentData 답글일때 부모글
     * @return string
     * @throws \Exception
     */
    public function canWrite($mode = 'w', $parentData = null)
    {
        if ($mode == 'r') {
            if ($this->cfg['bdReplyFl'] == 'n') {
                return 'n';
            }

            if ($this->cfg['bdKind'] == Board::KIND_QA) {
                return 'n';
            }

            if ($parentData['isNotice'] == 'y') {
                return 'n';
            }

            $auth = $this->cfg['bdAuthReply'];
            $groupAuth = $this->cfg['bdAuthReplyGroup'];
        } else if ($mode == 'm') {
            if ($this->cfg['bdMemoFl'] == 'n') {
                return 'n';
            }
            $auth = $this->cfg['bdAuthMemo'];
            $groupAuth = $this->cfg['bdAuthMemoGroup'];
        } else if ($mode == 'w') {
            $auth = $this->cfg['bdAuthWrite'];
            $groupAuth = $this->cfg['bdAuthWriteGroup'];
        } else {
            throw new \Exception(__('잘못된 인자값 입니다.'));
        }

        $canConfigCheck = $this->canConfigCheck($auth, $groupAuth);
        if (is_array($canConfigCheck)) {
            return $canConfigCheck;
        }

        return $canConfigCheck == true ? 'y' : 'n';
    }

    public function canModify($data)
    {
        if ($data['channel']) {
            return 'n';
        }

        if (MemberUtil::isLogin() == false) {    //비로그인상태
            if ($data['memNo']) { //회원글이면
                return 'n';
            }

            if(\App::getController()->getPageName() == 'board/write' && $data['isSecret'] == 'y') {
                  $checkPassword = $this->checkPassword($data, \Request::post()->get('oldPassword'));
                  if(!$checkPassword) {
                      return 'n';
                  }
              }

            return 'c'; //비회원글
        } else {
            if (in_array($data['replyStatus'], [2, 3])) { //답변상태가 대기 OR 완료일 경우
                return 'n';
            }
            if ($data['memNo'] == $this->member['memNo']) {
                return 'y';
            }
        }
        //로긴상태
        if (!$data['memNo']) {    // 비회원글 접근하면
            return 'n';
        }

        return 'n';
    }

    /**
     * 웹서비스 형태로 데이터 가공
     *
     * @param array &$arrData
     */
    public function applyConfigList(&$arrData)
    {
        if (!$arrData) {
            return;
        }
        foreach ($arrData as &$data) {
            // 프론트 게시글의 답변작성자가 관리자면서 답변상태가 답변완료가 아닐때 체크
            if($this->cfg['bdAnswerStatusFl'] == 'y'){
                if($data['groupThread'] != ''){
                    $data['frontAdminReplyFl'] = $this->chkAdminData($data['writerId']);
                    if($data['frontAdminReplyFl'] == 1 && $data['replyStatus'] != 3){
                        $data['adminReplyFl'] = 'y';
                    }
                }
            }

            $data['bdId'] = $this->cfg['bdId'];
            $data['regDate'] = date_format(date_create($data['regDt']), "Y.m.d");
            if (date('Y.m.d') == $data['regDate']) {
                $data['regDate'] = date_format(date_create($data['regDt']), "H:i");
            }

            if ($this->cfg['bdEventFl'] == 'y' && (empty($data['eventStart']) === false) && (empty($data['eventEnd']) === false)) {
                $data['eventStart'] = date_format(date_create($data['eventStart']), "Y.m.d H:i");
                $data['eventEnd'] = date_format(date_create($data['eventEnd']), "Y.m.d H:i");
            }
            $data['replyStatusText'] = '-';
            $data['replyComplete'] = false;
            if ($this->cfg['bdReplyStatusFl'] == 'y' && $data['replyStatus'] > 0) {
                $array = Board::REPLY_STATUS_LIST;
                $data['replyStatusText'] = $array[$data['replyStatus']];
                $data['replyComplete'] = ($data['replyStatus'] == Board::REPLY_STATUS_COMPLETE);
            }

            if (!$data['recommend']) {
                $data['recommend'] = 0;
            }

            $data['gapReply'] = '';
            $data['isNew'] = 'n';
            $data['isHot'] = 'n';
            $data['isAdmin'] = 'n';
            $data['isFile'] = 'n';

            // 이미지 설정
            $data['imgSizeW'] = $this->cfg['bdListImgWidth'];
            $data['imgSizeH'] = $this->cfg['bdListImgHeight'];

            if ($data['groupThread'] != '') {
                $data['gapReply'] = '<span style="margin-left:' . (((strlen($data['groupThread']) / 2) - 1) * 15) . 'px"></span>';
            }

            if ($data['isDelete'] == 'y') {
                $data['auth']['view'] = $data['auth']['modify'] = $data['auth']['delete'] = 'n';
            }

            $data['isFile'] = 'n';
            if (gd_isset($data['saveFileNm'])) {
                $data['isFile'] = 'y';
            }

            $data['isImage'] = 'n';
            preg_match("/<img[^>]*src=[\"']?([^>\"']+)[\"']?[^>]*>/i", $data['contents'], $match);
            $imgSrc = $match[1];
            if ($imgSrc) {
                $data['isImage'] = 'y';
                $data['editorImageSrc'] = $imgSrc;
            }

            if ($data['isSecret'] == 'y') {
                if ($this->cfg['bdSecretTitleFl'] == 1) {
                    if (gd_is_login() && $this->member['memNo'] == $data['memNo']) {
                    } else {
                        $data['subject'] = $this->cfg['bdSecretTitleTxt'];
                    }
                }
            }

            if ($this->cfg['bdGoodsPtFl'] == 'y') {
                $data['goodsPtPer'] = $data['goodsPt'] * 20;
            }

            if ($this->cfg['bdSubjectLength']) {
                $data['subject'] = gd_html_cut($data['subject'], $this->cfg['bdSubjectLength']);
            }

            $data['subject'] = $this->xssClean($data['subject']);
            if ($this->canList() == 'n') {
                $data['subject'] = '볼 수 있는 권한이 없습니다.';
            }

            $data['auth']['view'] = $this->canRead($data);
            $data['auth']['modify'] = $this->canModify($data);
            $data['auth']['delete'] = $this->canRemove($data);

            // 아이콘 설정
            if ($this->cfg['bdNewFl'] && (time() - strtotime($data['regDt'])) / 60 / 60 < $this->cfg['bdNewFl']) $data['isNew'] = 'y';
            if ($this->cfg['bdHotFl'] && $data['hit'] >= $this->cfg['bdHotFl']) $data['isHot'] = 'y';

            $data['writer'] = $this->getWriterInfo($data);
            //리스트 노출이미지
            $imgStr = '<img src="%s" width="%d" height="%d" />';
            $data['viewListImage'] = '';

            if ($data['uploadFileNm']) {
                $uploadFileNms = explode(STR_DIVISION, $data['uploadFileNm']);
                $imageFileNum = -1;
                for ($i = 0; $i < count($uploadFileNms); $i++) {
                    if (FileUtils::isImageExtension($uploadFileNms[$i])) {
                        $imageFileNum = $i;
                        break;
                    }
                }
                if ($imageFileNum > -1) {
                    $saveFileNames = explode(STR_DIVISION, $data['saveFileNm']);
                    if (gd_isset($data['bdUploadStorage'])) {
                        $storage = Storage::disk(Storage::PATH_CODE_BOARD, $data['bdUploadStorage']);
                        if (Request::isMobile()) {
                            $data['attachImageSrc'] = $storage->getHttpPath($data['bdUploadPath'] . $saveFileNames[$imageFileNum]);
                        } else {
                            try {
                                $existsThumPath = null;
                                if($data['bdUploadStorage'] == 'local') {
                                    $existsThumPath = $storage->isFileExists($data['bdUploadThumbPath'] . $saveFileNames[$imageFileNum]);
                                }
                                if ($existsThumPath) {
                                    if ($this->cfg['bdKind'] == Board::KIND_EVENT) {
                                        $data['attachImageSrc'] = $storage->getHttpPath($data['bdUploadPath'] . $saveFileNames[$imageFileNum]);
                                    } else {
                                        $data['attachImageSrc'] = $storage->getHttpPath($data['bdUploadThumbPath'] . $saveFileNames[$imageFileNum]);
                                    }
                                } else if ($data['bdUploadPath']) {
                                    $data['attachImageSrc'] = $storage->getHttpPath($data['bdUploadPath'] . $saveFileNames[$imageFileNum]);
                                }
                            } catch(\Throwable $e){

                            }
                        }
                    }
                }
            }

            if ($this->cfg['goodsType'] == 'order' && substr($data['orderGoodsNoText'], 0, 1) == 'A') {
                $order = new Order();
                $orderGoodsNo = substr($data['orderGoodsNoText'], 1);
                $_addOrderGoodsData = $order->getOrderGoodsData($data['orderNo'], $orderGoodsNo);
                $scmNo = key($_addOrderGoodsData);
                $addOrderGoodsData = $_addOrderGoodsData[$scmNo][0];
                $data['goodsImageSrc'] = SkinUtils::imageViewStorageConfig($addOrderGoodsData['addImageName'], $addOrderGoodsData['addImagePath'], $addOrderGoodsData['addImageStorage'], 100, 'add_goods')[0];
            } else {
                if ($data['onlyAdultFl'] == 'y' && gd_check_adult() === false && $data['onlyAdultImageFl'] =='n') {
                    if (\Request::isMobile()) {
                        $data['goodsImageSrc'] = "/data/icon/goods_icon/only_adult_mobile.png";
                    } else {
                        $data['goodsImageSrc'] = "/data/icon/goods_icon/only_adult_pc.png";
                    }
                } else {
                    $data['goodsImageSrc'] = SkinUtils::imageViewStorageConfig($data['imageName'], $data['imagePath'], $data['imageStorage'], 100, 'goods')[0];
                }
            }

            switch ($this->cfg['bdListImageTarget']) {
                case 'upload' :
                    $data['viewListImage'] = $data['attachImageSrc'];
                    break;
                case'editor' :
                    preg_match("/<img[^>]*src=[\"']?([^>\"']+)[\"']?[^>]*>/i", $data['contents'], $match);
                    $imgSrc = $match[1];
                    if ($imgSrc) {
                        $data['isImage'] = 'y';
                        $data['editorImageSrc'] = $imgSrc;
                    }
                    $data['viewListImage'] = $data['editorImageSrc'];
                    break;
                case 'goods' :
                    $data['viewListImage'] = $data['goodsImageSrc'];
                    break;
            }
        }

    }

    /**
     * applyConfigView
     *
     * @param $data
     * @return bool
     * @throws \Exception
     */
    public function applyConfigView(&$data)
    {
        if ($this->cfg == null || empty($data) === true) {
            return false;
        }
        $data['regDate'] = date_format(date_create($data['regDt']), "Y.m.d H:i:s");
        if ($this->cfg['bdEventFl'] == 'y') {
            $data['eventStart'] = DateTimeUtils::dateFormat('Y.m.d H:i:s', $data['eventStart']);
            $data['eventEnd'] = DateTimeUtils::dateFormat('Y.m.d H:i:s', $data['eventEnd']);
            if (strlen($this->cfg['bdEndEventMsg']) > 0) {
                if ($data['eventStart'] > date('Y.m.d H:i:s') || $data['eventEnd'] < date('Y.m.d H:i:s')) {
                    throw new \Exception($this->cfg['bdEndEventMsg']);
                }
            }
        }
        $data['subject'] = $this->xssClean($data['subject']);
        $data['replyStatusText'] = '-';
        $data['replyComplete'] = false;
        $isMobile = $data['isMobile'] == 'y' ? true : false;
        if ($this->cfg['bdReplyStatusFl'] == 'y') {
            $replyStatusList = Board::REPLY_STATUS_LIST;
            if ($data['replyStatus']) {
                $data['replyStatusText'] = $replyStatusList[$data['replyStatus']];
            }
            $data['workedAnswerContents'] = $this->setContents($data['answerContents'], $data, false, false, true);
            if ($data['answerManagerNo']) {
                $data['answerManagerNm'] = $this->getWriterInfo($data, 'answerManagerNo');
            }

            $data['replyComplete'] = ($data['replyStatus'] == Board::REPLY_STATUS_COMPLETE);
        }

        gd_isset($data['recommend'], 0);
        $data['gapReply'] = '';
        $data['isNew'] = 'n';
        $data['isHot'] = 'n';
        $data['isAdmin'] = 'n';
        $data['isFile'] = 'n';
        if ($this->cfg['bdGoodsFl'] == 'y') {
            $data['goodsData'] = $this->setGoodsDataPool($data['goodsNo']);
        }
        // 이미지 설정
        $data['imgSizeW'] = $this->cfg['bdListImgWidth'];
        $data['imgSizeH'] = $this->cfg['bdListImgHeight'];

        if ($data['groupThread'] != '') {
            $data['gapReply'] = '<span style="margin-left:' . (((strlen($data['groupThread']) / 2) - 1) * 15) . 'px"></span>';
        }

        $data['isFile'] = 'n';
        if (gd_isset($data['saveFileNm'])) {
            $data['isFile'] = 'y';
        }

        //권한설정
        $data['auth']['view'] = $this->canRead($data);
        $data['auth']['modify'] = $this->canModify($data);
        $data['auth']['delete'] = $this->canRemove($data);
        $data['auth']['reply'] = $this->canWrite('r', $data);

        $data['isViewGoodsInfo'] = 'n';
        if ($this->cfg['goodsType'] == 'goods' && $data['goodsNo'] && !$data['groupThread']) {
            $data['isViewGoodsInfo'] = 'y';
        }
        $data['isViewOrderInfo'] = 'n';
        if ($this->cfg['goodsType'] == 'order' && $data['extraData']['arrOrderGoodsData'] && !$data['groupThread']) {
            $data['isViewOrderInfo'] = 'y';
        }

        // 아이콘 설정
        if ($this->cfg['bdNewFl'] && (time() - strtotime($data['regDt'])) / 60 / 60 < $this->cfg['bdNewFl']) $data['isNew'] = 'y';
        if ($this->cfg['bdHotFl'] && $data['hit'] >= $this->cfg['bdHotFl']) $data['isHot'] = 'y';

        $data['writer'] = $this->getWriterInfo($data);
        $data['hit'] = number_format(($data['hit']));
        $data['workedContents'] = $this->setContents($data['contents'], $data, false, $isMobile);
        $data['memoList'] = $this->getMemo($data['sno']);

        if ($data['saveFileNm']) {
            $data['uploadedFile'] = $this->getFilelist(array('uploadFileNm' => $data['uploadFileNm'], 'saveFileNm' => $data['saveFileNm']), $data['bdUploadStorage'], $data['bdUploadPath']);
        }

        if ($this->cfg['bdIpFilterFl'] == 'y' && $this->isAdmin() === false && gd_isset($data['writerIp'])) {
            preg_match($pattern = '/\.[0-9]*$/', $data['writerIp'], $matches);
            if (strlen($matches[0]) > 1) $data['writerIp'] = preg_replace($pattern, '.' . str_repeat("*", strlen($matches[0]) - 1), $data['writerIp']);
        }

        if ($data['goodsData']['goodsImageSrc']) {
            $data['viewListImage'] = $data['goodsData']['goodsImageSrc'];
        }

        if ($data['isDelete'] == 'y') {
            throw new \Exception(__('삭제된 글입니다.'));
        }
    }

    public function canRemove($data)
    {
        return $this->canModify($data);
    }

    public function getCount($arrWhere = [])
    {
        $arrWhere[] = "isDelete = 'n'";
        $total = parent::getCount($arrWhere);
        return $total;
    }

    public function isExceptReviewGoods($goodsNo)
    {
        if (in_array($goodsNo, explode(INT_DIVISION, $this->cfg['bdReviewExceptGoodsNo']))) {
            return true;
        }

        return false;
    }

    /**
     * 상품후기 상품/주문연동  가능여부 체크
     *
     * @return array ( possible : 가능여부(TF)  / errorMsg : 애러메시지 )
     */
    public function checkReviewPossible()
    {
        $goodsNo = is_array($this->req['goodsNo']) ? $this->req['goodsNo'][0] : $this->req['goodsNo'];
        $orderGoodsNo = is_array($this->req['orderGoodsNo']) ? $this->req['orderGoodsNo'][0] : $this->req['orderGoodsNo'];
        $errorMsg = false;
        if ($this->cfg['bdId'] != Board::BASIC_GOODS_REIVEW_ID) {
            return ['possible' => true];
        }
        if ($this->cfg['bdAuthWrite'] == 'all' && $this->cfg['bdGoodsType'] == 'order' && MemberUtil::checkLogin() === false) {
            $errorMsg = "로그인이 필요한 서비스입니다.(비회원의 경우, 비회원 주문조회를 통해 이용하시기 바랍니다)";
            return ['possible' => false, 'errorMsg' => $errorMsg, 'requireLogin' => true];
        }

        if (empty($orderGoodsNo) && empty($goodsNo)) {  //상품후기 게시판리스트에서 글작성 버튼 누를때
            return ['possible' => true];
        }

        $isGuest = MemberUtil::checkLogin() == 'guest' ? true : false;

        if ($this->cfg['bdReviewAuthWrite'] == 'all') {   //구매여부와 상관없이 후기작성
            if(empty($goodsNo) && $orderGoodsNo) {
                $order = new Order();
                $orderGoodsData = $order->getOrderGoods(null, $orderGoodsNo)[0];
                $goodsNo = $orderGoodsData['goodsNo'];
            }

            if ($this->isExceptReviewGoods($goodsNo) === true) {
                return ['possible' => false, errorMsg => '선택하신 상품은 후기를 작성하실 수 없습니다.'];
            }
            if (MemberUtil::isLogin() === false) {  //비회원은 중복제한 체크 안함.
                return ['possible' => true];
            }

            if ($this->cfg['bdReviewDuplicateLimit'] == 'one') {
                $writeCnt = $this->buildQuery->selectCount(['goodsNo' => $goodsNo, 'memNo' => $this->member['memNo']]);
                if ($writeCnt > 0) {
                    return ['possible' => false, errorMsg => __('선택하신 상품은 후기를 작성하실 수 없습니다.')];
                } else {
                    return ['possible' => true];
                }
            } else {
                return ['possible' => true];
            }
        }

        if ($orderGoodsNo) {
            $order = new Order();
            $orderGoodsData = $order->getOrderGoods(null, $orderGoodsNo)[0];
            if (empty($orderGoodsData)) {
                return ['possible' => false, errorMsg => __('해당 상품의 주문내역이 없습니다.')];
            }
            if ($this->isExceptReviewGoods($orderGoodsData['goodsNo']) === true || $orderGoodsData['goodsType'] == 'addGoods') {
                return ['possible' => false, errorMsg => '선택하신 상품은 후기를 작성하실 수 없습니다.'];
            }

            if ($errorMsg = $this->getErrorMsgGoodsReviewOrderStatus($orderGoodsData)) {
                return ['possible' => false, 'errorMsg' => $errorMsg];
            }

            if ($this->cfg['bdReviewAuthWrite'] == 'all') {   //구매여부와 상관없이 후기작성
                return ['possible' => true];
            } else if ($this->cfg['bdReviewAuthWrite'] == 'buyer') {  //구매자만
                if ($this->cfg['bdReviewDuplicateLimit'] == 'one') {
                    $cnt = BoardBuildQuery::init($this->req['bdId'])->selectCountByOrderGoodsNo($orderGoodsNo, $this->req['bdSno'],true);
                    if ($cnt > 0) {
                        return ['possible' => false, 'errorMsg' => __('선택하신 상품은 후기를 작성하실 수 없습니다.')];
                    }
                }
            }

        } else {
            if ($this->isExceptReviewGoods($goodsNo) === true) {
                return ['possible' => false, errorMsg => '선택하신 상품은 후기를 작성하실 수 없습니다.'];
            }

            $arrBind = [];
            //구매여부 체크
            $query = "SELECT count(*)  as cnt FROM " . DB_ORDER_GOODS . " as og INNER JOIN " . DB_ORDER . " as o ON og.orderNo = o.orderNo  WHERE  og.goodsNo  = ? AND ";
            $this->db->bind_param_push($arrBind, 'i', $goodsNo);
            if ($isGuest) {
                $query .= "og.orderNo = ?  AND o.memNo = 0 ";
                $this->db->bind_param_push($arrBind, 's', \Session::get('guest.orderNo'));
            } else {
                $query .= "o.memNo = ?   ";
                $this->db->bind_param_push($arrBind, 'i', $this->member['memNo']);
            }
            if ($orderGoodsNo) {
                $query .= " AND og.sno = ?   ";
                $this->db->bind_param_push($arrBind, 'i', $orderGoodsNo);
            }

            $result = $this->db->query_fetch($query, $arrBind, false);
            if ($result['cnt'] == 0) {
                $errorMsg = __('해당 상품의 주문내역이 없습니다.');
                return ['possible' => false, errorMsg => $errorMsg];
            }

            //주문상태 , 기한 체크
            $arrBind = [];
            $query = "SELECT og.*   FROM " . DB_ORDER_GOODS . " as og INNER JOIN " . DB_ORDER . " as o ON og.orderNo = o.orderNo  WHERE  og.goodsNo  = ? AND  ";
            $this->db->bind_param_push($arrBind, 'i', $goodsNo);
            if ($isGuest) {
                $query .= "og.orderNo = ?  AND o.memNo = 0 ";
                $this->db->bind_param_push($arrBind, 's', \Session::get('guest.orderNo'));
            } else {
                $query .= "o.memNo = ?   ";
                $this->db->bind_param_push($arrBind, 'i', $this->member['memNo']);
            }
            if ($orderGoodsNo) {
                $query .= " AND og.sno = ?   ";
                $this->db->bind_param_push($arrBind, 'i', $orderGoodsNo);
            }
            $query .= " AND  SUBSTRING(og.orderStatus,1,1) in ('p','d','g','s')";
            $query .= " AND og.regDt>=(CURDATE()-INTERVAL 1 YEAR) ";
            $result = $this->db->query_fetch($query, $arrBind);
            foreach ($result as $val) {
                //중복체크
                if ($this->cfg['bdReviewDuplicateLimit'] == 'one') {
                    $arrBind = null;
                    $query = "SELECT count(*) as cnt FROM " . DB_BOARD_EXTRA_DATA . " as bed INNER JOIN " . DB_BD_ . Board::BASIC_GOODS_REIVEW_ID . " as bd ON bed.bdId= ? AND   bed.bdSno = bd.sno  where  orderGoodsNoText = ? AND  "; // AND bd.isDelete ='n'
                    $this->db->bind_param_push($arrBind, 's', Board::BASIC_GOODS_REIVEW_ID);
                    $this->db->bind_param_push($arrBind, 'i', $val['sno']);
                    if ($isGuest) {
                        $query .= ' bd.orderNo = ? ';
                        $this->db->bind_param_push($arrBind, 's', \Session::get('guest.orderNo'));
                    } else {
                        $query .= ' bd.memNo = ? ';
                        $this->db->bind_param_push($arrBind, 'i', $this->member['memNo']);
                    }

                    $result = $this->db->query_fetch($query, $arrBind, false);
                    if ($result['cnt'] > 0) {
                        continue;
                    }
                }

                if ($errorMsg = $this->getErrorMsgGoodsReviewOrderStatus($val)) {
                    break;
                }
                $orderGoodsNo = $val['sno'];//성공
                break;
            }

            if ($errorMsg) {
                return ['possible' => false, 'errorMsg' => $errorMsg];
            }

            if (!$orderGoodsNo) {
                $errorMsg = $errorMsg ? $errorMsg : __('선택하신 상품은 후기를 작성하실 수 없습니다.');
                return ['possible' => false, 'errorMsg' => $errorMsg];
            }
        }

        return ['possible' => true, 'orderGoodsNo' => $orderGoodsNo];
    }

    /**
     * 마이페이지에서 후기작성 버튼 노출여부 체크
     *
     * @param $orderGoodsData
     * @return bool
     */
    public function viewWriteGoodsReview($orderGoodsData)
    {
        if (\Request::isMobile()) {
            if ($this->cfg['bdUseMobileFl'] != 'y') {
                return false;
            }
        } else {
            if ($this->cfg['bdUsePcFl'] != 'y') {
                return false;
            }
        }

        if($this->cfg['bdReviewAuthWrite'] === 'all'){
            return true;
        }
        else {
            return $this->getErrorMsgGoodsReviewOrderStatus($orderGoodsData) === false;
        }
    }

    /**
     * 상품후기 작성 시 주문조건관련 애러메시지 가져오는 함수. false 이면 애러없음.
     *
     * @param $orderGoodsData
     * @return bool|string
     */
    protected function getErrorMsgGoodsReviewOrderStatus($orderGoodsData)
    {
        if ($orderGoodsData['goodsType'] == 'addGoods') {
            return __('선택하신 상품은 후기를 작성하실 수 없습니다.');
        }

        if ($this->cfg['bdGoodsType'] == 'goods') {
            if (substr($orderGoodsData['orderStatus'], 0, 1) !== 's') {
                return __('선택하신 상품은 후기를 작성하실 수 없습니다.');
            }
            return false;
        }

        if (empty($orderGoodsData)) {
            return __('선택하신 상품은 후기를 작성하실 수 없습니다.');
        }

        $getSort = function ($status) {
            $statusSort = [
                'o' => 100,
                'p' => 200,
                'g' => 300,
                'd' => 400,
                's' => 500,
            ];

            //교환추가완료의 상태값은 구매확정의 값과 동일.
            if($status === 'z5'){
                return $statusSort['s'] + substr($status, 1, 1);
            }
            return $statusSort[substr($status, 0, 1)] + substr($status, 1, 1);
        };

        if ($getSort($orderGoodsData['orderStatus']) < $getSort($this->cfg['bdReviewOrderStatus'])) {
            $errorMsg = __('선택하신 상품은 후기를 작성하실 수 없습니다.');
        } else if ($this->cfg['bdReviewPeriod'] > 0) {
            if ($this->cfg['bdReviewOrderStatus'] == 'p1') {
                $checkDate = $orderGoodsData['paymentDt'];
            } else if ($this->cfg['bdReviewOrderStatus'] == 'd1') {
                $checkDate = $orderGoodsData['deliveryDt'];
            } else if ($this->cfg['bdReviewOrderStatus'] == 'd2') {
                $checkDate = $orderGoodsData['deliveryCompleteDt'];
            } else if ($this->cfg['bdReviewOrderStatus'] == 's1') {
                $checkDate = $orderGoodsData['finishDt'];
            }
            if($this->cfg['bdReviewPeriod']>1){
                $betweenDt = date("Y-m-d", strtotime($checkDate . " +" . ($this->cfg['bdReviewPeriod']-1) . ' day'));
            }
            else {
                $betweenDt = $checkDate;
            }
            if (date("Y-m-d") > $betweenDt) {
                $errorMsg = __('선택하신 상품은 후기를 작성하실 수 없습니다.');
            }
        }

        if ($errorMsg) {
            return $errorMsg;
        }
        return false;
    }

    /**
     * 관리자 체크
     * @return $cnt
     */
    public function chkAdminData($id)
    {
        $db = \App::load('DB');
        $arrBind = [];
        $query = "SELECT COUNT(*)as cnt FROM " . DB_MANAGER . " WHERE managerId = ?";
        $db->bind_param_push($arrBind,'s',$id);
        $result = $db->query_fetch($query, $arrBind, false);
        unset($arrBind);

        return $result['cnt'];
    }

}
