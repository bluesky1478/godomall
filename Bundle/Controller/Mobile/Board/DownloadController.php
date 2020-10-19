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

namespace Bundle\Controller\Mobile\Board;

use Component\Storage\Storage;
use Component\Board\BoardConfig;
use Framework\Debug\Exception\AlertBackException;
use Request;

class DownloadController extends \Controller\Mobile\Controller
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
                throw new \Exception(__('잘못된 경로로 접근하셨습니다.'));
            }

            $bdId = $req['bdId'];
            $fid = $req['fid'] + 0;

            $this->db->strField = "uploadFileNm, saveFileNm , bdUploadStorage, bdUploadPath";
            $this->db->strWhere = "sno=?";
            $this->db->bind_param_push($arrBind, 'i', $req['sno']);

            $query = $this->db->query_complete();
            $strSQL = 'SELECT ' . array_shift($query) . ' FROM ' . DB_BD_ . $bdId . ' ' . implode(' ', $query);
            $data = $this->db->query_fetch($strSQL, $arrBind, false);

            $uploadFileNm = explode(STR_DIVISION, $data['uploadFileNm']);
            $saveFileNm = explode(STR_DIVISION, $data['saveFileNm']);

            $uploadFileNm = $uploadFileNm[$fid];
            $saveFileNm = $saveFileNm[$fid];

            Storage::disk(Storage::PATH_CODE_BOARD, $data['bdUploadStorage'])->download($data['bdUploadPath'] . $saveFileNm, $uploadFileNm);
        } catch (\Exception $e) {
            throw new AlertBackException(__('다운로드 받을 파일이 존재하지 않습니다.'));
        }
    }


}
