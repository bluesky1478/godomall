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

namespace Bundle\Component\Goods;

use Component\Storage\Storage;
use Framework\Utility\ArrayUtils;

/**
 * 상품관련 게시판내 상품검색 Class
 *
 * @package Bundle\Component\Goods
 * @author Jong-tae Ahn <qnibus@godo.co.kr>
 */
class GoodsSearch
{
    protected $db = null;
    protected $storage;

    /**
     * GoodsSearch 생성자.
     */
    public function __construct()
    {
        if (!is_object($this->db)) {
            $this->db = \App::load('DB');
        }

    }

    /**
     * getSearchedGoodsList
     *
     * @param $req
     * @return array
     * @author Jong-tae Ahn <qnibus@godo.co.kr>
     */
    public function getSearchedGoodsList(&$req)
    {
        $page = preg_replace("/[^0-9]*/", "", gd_isset($_GET['page'], 1));
        $reqGoodsCateCd = gd_isset($req['goodsCateCd']);
        $reqGoodsNm = gd_isset($req['goodsNm']);

        $getData = array();
        $limit = 10;
        if ($reqGoodsCateCd || $reqGoodsNm) {
            $start = ($page - 1) * $limit;

            $strWhere = array();
            if ($reqGoodsCateCd) {
                $strWhere[] = " cateCd LIKE ?";
                $this->db->bind_param_push($arrBind, 's', $reqGoodsCateCd . '%');
            }
            if ($reqGoodsNm) {
                $strWhere[] = " goodsNm LIKE ?";
                $this->db->bind_param_push($arrBind, 's', '%' . $reqGoodsNm . '%');
            }

            //--- 목록
            $this->db->strField = " g.goodsNo, g.goodsNm, g.imagePath, gi.imageName, g.imageStorage ";
            $this->db->strJoin = DB_GOODS . " AS g
						LEFT JOIN (SELECT goodsNo, imageName, MIN(imageNo) FROM " . DB_GOODS_IMAGE . " WHERE imageKind='detail' GROUP BY goodsNo ) AS gi ON g.goodsNo = gi.goodsNo ";
            $this->db->strWhere = implode(" AND ", $strWhere);
            $this->db->strOrder = " regDt DESC";
            $this->db->strLimit = $start . ", " . $limit;

            // 검색 카운트
            $strSQL = ' SELECT COUNT(*) AS cnt FROM ' . $this->db->strJoin . ' WHERE ' . $this->db->strWhere;
            $res = $this->db->query_fetch($strSQL, $arrBind, false);

            $qryComplete = $this->db->query_complete();
            $strSQL = "SELECT " . array_shift($qryComplete) . " FROM " . array_shift($qryComplete) . " " . implode(" ", $qryComplete);
            $getData = gd_htmlspecialchars_stripslashes($this->db->query_fetch($strSQL, $arrBind));

            // 전체 카운트
            $this->db->strField = " COUNT(*) ";
            $this->db->strWhere = implode(" AND ", $strWhere);
            $qryComplete = $this->db->query_complete();
            $strSQL = "SELECT (SELECT " . array_shift($qryComplete) . " FROM " . DB_GOODS . " " . implode(" ", $qryComplete) . ") AS total";
            $cnt = $this->db->query_fetch($strSQL, $arrBind, false);
            $cnt['search'] = $res['cnt']; // 검색 레코드 수

            if (ArrayUtils::isEmpty($getData) === false) {
                //$dataFile = \App::load('\\Component\\File\\DataFile');
                foreach ($getData as &$val) {
                    if ($val['imageStorage'] != 'url') {
                        //if ($dataFile->getImageStorage($val['imageStorage']) == null) {
                        //    $dataFile->setImageStorage($val['imageStorage'], $val['imageStorage'], 'goods');
                        //}

                        //$tmpPath = $dataFile->getUrl($val['imageStorage'], $val['imagePath'] . $val['imageName']);
                        //$tmpPath = $this->storageHandler->get($val['imageStorage'],$val['imagePath'] . $val['imageName'])->getHttpHostPath();
                        $tmpPath = Storage::disk(Storage::PATH_CODE_GOODS, $val['imageStorage'])->getHttpPath($val['imagePath'] . $val['imageName']);
                        $goodsData[] = array('goodsNo' => $val['goodsNo'], 'goodsNm' => $val['goodsNm'], 'imageUrl' => $tmpPath);
                    } else {
                        $goodsData[] = array('goodsNo' => $val['goodsNo'], 'goodsNm' => $val['goodsNm'], 'imageUrl' => $val['imageName']);
                    }
                }
            }

            return array('goodsData' => $goodsData, 'cnt' => $cnt);
        }
    }
}
