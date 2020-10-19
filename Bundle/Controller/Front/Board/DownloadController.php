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

namespace Bundle\Controller\Front\Board;

use Component\Storage\Storage;
use Component\Board\BoardConfig;
use Component\Board\BoardFront;
use Framework\Debug\Exception\AlertBackException;
use Framework\Debug\Exception\AlertOnlyException;
use Request;
use Session;

class DownloadController extends \Controller\Front\Controller
{
    protected $db;

    public function index()
    {
        try {
            if (!is_object($this->db)) {
                $this->db = \App::load('DB');
            }

            $req = Request::get()->toArray();

            if (!isset($req['bdId']) || !isset($req['fid']) || !isset($req['sno'])) {
                exit();
            }

            $bdId = $req['bdId'];
            $fid = $req['fid'] + 0;

            $this->db->strField = "uploadFileNm, saveFileNm , bdUploadStorage, bdUploadPath, isDelete, isSecret, memNo, isSecret, groupThread, parentSno";
            $this->db->strWhere = "sno=?";
            $this->db->bind_param_push($arrBind, 'i', $req['sno']);

            $query = $this->db->query_complete();
            $strSQL = 'SELECT ' . array_shift($query) . ' FROM ' . DB_BD_ . $bdId . ' ' . implode(' ', $query);
            $data = $this->db->query_fetch($strSQL, $arrBind, false);

            // 게시판 읽기 권한에 따른 첨부파일 다운로드 (XSS 취약점 개선)
            $boardFront = new BoardFront($req);
            $auth = $boardFront->canRead($data);

            if ($auth === 'n') {
                throw new AlertBackException(__('다운로드 불가한 회원입니다.'));
            } else if ($auth === 'c') {
                if ($data['isSecret'] === 'y') {
                    // 비회원 비밀글 첨부파일 다운로드시 비밀번호 검증여부 체크
                    if (!Session::has('writerPwOk_' . $req['bdId'] . '_' . $req['sno'])) {
                        throw new AlertBackException(__('다운로드 불가한 회원입니다.'));
                    }
                }
            }

            $uploadFileNm = explode(STR_DIVISION, $data['uploadFileNm']);
            $saveFileNm = explode(STR_DIVISION, $data['saveFileNm']);

            $uploadFileNm = $uploadFileNm[$fid];
            $saveFileNm = $saveFileNm[$fid];

            Storage::disk(Storage::PATH_CODE_BOARD, $data['bdUploadStorage'])->download($data['bdUploadPath'] . $saveFileNm, $uploadFileNm);
        } catch (\Exception $e) {
            if ($e->getMessage()) {
                throw new AlertBackException(__($e->getMessage()));
            } else {
                throw new AlertBackException(__('다운로드 받을 파일이 존재하지 않습니다.'));
            }
        }
    }


}
