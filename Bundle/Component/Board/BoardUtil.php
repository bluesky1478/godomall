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

use DB;
use Framework\Utility\ArrayUtils;
use Framework\Utility\StringUtils;
use Vendor\Captcha\Captcha;
use Request;
use App;

class BoardUtil
{
    /**
     * 댓글 답글상태 문자열
     *
     * @param $bdId
     * @param $bdSno
     * @param $groupNo
     * @param string $parentGroupThread
     * @return string
     * @throws \Exception
     */
    static public function createMemoGroupThread($bdId, $bdSno, $groupNo, $parentGroupThread = '')
    {
        $db = \App::load('DB');
        $beginReplyChar = 'AA';
        $endReplyChar = 'ZZ';
        $replyNumber = 1;
        $replyLen = strlen($parentGroupThread) + 1;
        $arrBind = [];
        $sql = " select MIN(SUBSTRING(groupThread, {$replyLen}, 2)) as reply from " . DB_BOARD_MEMO . " where bdId = ?  AND bdSno = ? AND  groupNo = ? AND SUBSTRING(groupThread, ?, 2) <> '' ";
        $db->bind_param_push($arrBind, 's', $bdId);
        $db->bind_param_push($arrBind, 'i', $bdSno);
        $db->bind_param_push($arrBind, 'i', $groupNo);
        $db->bind_param_push($arrBind, 's', $replyLen);
        if ($parentGroupThread) {
            $sql .= " and groupThread like '?%' ";
            $db->bind_param_push($arrBind, 's', $parentGroupThread);
        }

        $result = $db->query_fetch($sql,$arrBind,false);
        $row = $result['reply'];

        if (!$row) {
            $replyChar = $beginReplyChar;
        } else if ($row == $endReplyChar) { // AA~ZZ은 26 입니다.
            throw new \Exception(sprintf(__('더 이상 답변하실 수 없습니다.\\n\\n답변은 %s 개 까지만 가능합니다.'),676));
        } else {
            $replyChar = self::c26dec(self::decc26($row) + $replyNumber);
        }

        $reply = $parentGroupThread . $replyChar;
        return $reply;
    }

    static public function createMemoGroupNo($bdId, $bdSno)
    {
        $db = \App::load('DB');
        $arrBind = [];
        $query = "SELECT MIN(groupNo) as groupNo FROM " . DB_BOARD_MEMO . " WHERE bdId = ?  AND bdSno = ? ";
        $db->bind_param_push($arrBind, 's', $bdId);
        $db->bind_param_push($arrBind, 'i', $bdSno);
        $result = $db->query_fetch($query,$arrBind,false);
        $groupNo  = $result['groupNo'];
        if ($groupNo == null) {
            return -1;
        }
        return $groupNo - 1;
    }

    static public function createGroupNo($bdId)
    {
        $query = "SELECT MIN(groupNo) FROM " . DB_BD_ . $bdId;
        list($groupNo) = DB::fetch($query, 'row');
        if ($groupNo == null) {
            return -1;
        }

        return $groupNo - 1;
    }

    /**
     * 답글상태 문자열
     *
     * @param $bdId
     * @param $groupNo
     * @param string $parentGroupThread
     * @return string
     * @throws \Exception
     */
    static public function createGroupThread($bdId, $groupNo, $parentGroupThread = '')
    {
        $db = \App::load('DB');
        $beginReplyChar = 'AA';
        $endReplyChar = 'ZZ';
        $replyNumber = 1;
        $arrBind = [];
        $replyLen = (int)(strlen($parentGroupThread) + 1);
        $sql = " select MAX(SUBSTRING(groupThread, {$replyLen}, 2)) as reply from " . DB_BD_ . $bdId . " where groupNo = ?   and SUBSTRING(groupThread, {$replyLen} , 2) <> '' ";
        $db->bind_param_push($arrBind,'i',$groupNo);
        if ($parentGroupThread) {
            $sql .= " and groupThread like '{$parentGroupThread}%' ";
        }

        $result = $db->query_fetch($sql,$arrBind,false);
        $row = $result['reply'];
        if (!$row) {
            $replyChar = $beginReplyChar;
        } else if ($row == $endReplyChar) { // AA~ZZ은 26 입니다.
            throw new \Exception(sprintf(__('더 이상 답변하실 수 없습니다.\\n\\n답변은 %s 개 까지만 가능합니다.'),676));
        } else {
            $replyChar = self::c26dec(self::decc26($row) + $replyNumber);
        }

        $reply = $parentGroupThread . $replyChar;
        return $reply;
    }

    /**
     * antiSpam
     *
     * @param $captchaKey
     * @param $switch
     * @param string $method
     *
     * @return array
     */
    static public function antiSpam($captchaKey, $switch, $method = '')
    {
        // 한글 도메인(퓨니코드 -> 한글) 번역 (퓨니코드아니면 그대로 리턴)
        $hostName = (preg_match('/MSIE\s(?P<v>\d+)/i', Request::getUserAgent(), $ieCheck) && $ieCheck['v'] <= 9) ? StringUtils::getPunyToKorean(Request::getHost()) : Request::getHost();

        if (preg_match('/' . str_replace('www.','', $hostName) . '/i', Request::server()->get('HTTP_REFERER')) != 1) {
            return ['code' => '1001', 'msg' => 'Fail to verify HTTP_HOST'];
        }
        if (strlen($switch) > 1 && $switch[1] == '2' && $method != '' && strcasecmp(strtoupper($method), Request::getMethod())) {
            return ['code' => '2001', 'msg' => 'Fail to verify Method'];
        }

        if (strlen($switch) > 2 && $switch[2] == '3') {
            $rst = self::checkCaptcha($captchaKey);
            if ($rst['code'] != '0000') {
                return $rst;
            }
        }

        return ['code' => '0000', 'msg' => 'Succeed in verifing'];
    }

    static public function checkCaptcha($captchaKey) {
        $captcha = new Captcha();
        $rst = $captcha->verify($captchaKey, 1);
        return $rst;
    }

    /**
     * 파일이름을 형식에 맞게 정리하기
     *
     * @param array &$uploadFileNm 업로드된 파일이름
     * @param $saveFileNm
     * @internal param array $saveFileName 실제 저장된 파일이름
     */
    static public function setFilename(&$uploadFileNm, &$saveFileNm)
    {
        $maxStr = @floor((700 - count(Request::files()->get('upfiles.tmp_name'))) / count(Request::files()->get('upfiles.tmp_name')));
        if (empty(Request::files()->get('upfiles.tmp_name')) === false) {
            foreach ($uploadFileNm as $key=>$val) {
                if (($val)) {
                    if (strlen($val) > $maxStr) {
                        $t = explode(".", $val);
                    }
                    $tmp_old[] = (strlen($val) > $maxStr) ? substr($val, 0, $maxStr - 6) . sprintf("%02d", $key + 1) . "." . substr($ext[count($ext) - 1], 0, 3) : $val;
                    $tmp_new[] = $saveFileNm[$key];
                }
            }

            if (ArrayUtils::isEmpty($tmp_new) === false) {
                $saveFileNm = @implode(STR_DIVISION, $tmp_new);
            } else {
                $saveFileNm = '';
            }
            if (ArrayUtils::isEmpty($tmp_old) === false) {
                $uploadFileNm = @implode(STR_DIVISION, $tmp_old);
            } else {
                $uploadFileNm = '';
            }
        } else {
            if (ArrayUtils::isEmpty($saveFileNm) === false) {
                $saveFileNm = @implode(STR_DIVISION, $saveFileNm);
            } else {
                $saveFileNm = '';
            }
            if (ArrayUtils::isEmpty($uploadFileNm) === false) {
                $uploadFileNm = @implode(STR_DIVISION, $uploadFileNm);
            } else {
                $uploadFileNm = '';
            }
        }
        $pattern = ["/\|[\|]+/", "/(^[\|]*|[\|]*$)/"];
        $replace = ["|", ""];
        $saveFileNm = preg_replace($pattern, $replace, $saveFileNm);
        $uploadFileNm = preg_replace($pattern, $replace, $uploadFileNm);

    }

    /**
     * sendSms
     *
     * @param $bdId
     * @param array $data (memNo , wirterNm , cellPhone)
     * @param array $convertWord
     * @param array $sendTarget (member, admin, provider)
     *
     * @param null $smsFl
     * @return array
     * @throws \Exception
     */
    static public function sendSms($bdId, array $data, array $convertWord = [], $sendTarget = null, $smsFl = null)
    {
        /** @var \Bundle\Component\Sms\Sms $sms */
        $sms = App::load('\\Component\\Sms\\Sms');
        $scmNo = $data['scmNo'];
        $memNo = $data['memNo'];
        $memNm = $data['writerNm'];
        $cellPhone = $data['cellPhone'];

        // 2017-03-08 yjwee 게시판은 수신여부와 상관없이 발송이기때문에 smsFl 값을 y로 고정한다.
        $arr = ['scmNo' => $scmNo, 'memNo' => $memNo, 'memNm' => $memNm, 'smsFl' => 'y', 'cellPhone' => $cellPhone];

        $result = $sms->smsAutoSend('board', $bdId, $arr, $convertWord, $sendTarget);
        return $result;
    }

    /**
     * 10진수를 26진수로
     *
     * @param $dec
     * @return string
     */
    static public function c26dec($dec)
    {
        //$key = '0123456789ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz';
        $key = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ';
        $len = strlen($key);
        $tmp = floor($dec / $len);
        $c62 = $key[$dec - ($tmp * $len)];
        if ($tmp)
            $c62 = self::c26dec($tmp) . $c62;

        if (strlen($c62) == 1) {
            $c62 = 'A' . $c62;
        }
        return $c62;
    }

    /**
     * 26진수를 10진수로
     *
     * @param $c62
     * @return bool|int
     */
    static public function decc26($c62)
    {
        $key = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ';
        $len = strlen($key);
        $c62 = strrev($c62);
        $dec = 0;
        for ($i = 0; $i <= strlen($c62) - 1; $i++) {
            $dec += strpos($key, $c62[$i]) * pow($len, $i);
        }

        return $dec;
    }

    static public function getCount($bdId, $search, $isAdmin)
    {
        if (!$isAdmin) {
            $addWhere[] =" isDelete = 'n'";
        }
        return BoardBuildQuery::init($bdId)->selectCount($search, $addWhere);
    }

    /**
     * checkForbiddenWord
     *
     * @param $str
     *
     * @return bool
     * @throws \Exception
     */
    static public function checkForbiddenWord(&$str)
    {
        $forbidden = gd_policy('board.forbidden');
        if (!isset($forbidden['word']) || !$forbidden['word']) {
            return true;
        }

        $forbiddenWord = explode(STR_DIVISION, $forbidden['word']);
        $findForbiddenWord = null;
        if (ArrayUtils::isEmpty($forbiddenWord) === false) {
            foreach($forbiddenWord as $val) {
                if(strpos($str,$val)!==false) { //문자열 찾았으면.
                    $findForbiddenWord = $val;
                    break;
                }
            }

            if ($findForbiddenWord) {
                throw new \Exception(sprintf(__('%s 는 사용 하실 수 없는 단어입니다.'),$findForbiddenWord));
            }
        }
        return true;
    }

    static public function getData($bdId) {
        $db = \App::load('DB');
        $arrBind = [];
        $query = "SELECT * FROM " . DB_BOARD . " WHERE bdId = ?";
        $db->bind_param_push($arrBind,'s',$bdId);
        $result = $db->query_fetch($query, $arrBind,false);
        return $result;
    }
}
