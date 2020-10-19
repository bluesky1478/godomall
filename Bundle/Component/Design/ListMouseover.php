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
namespace Bundle\Component\Design;

use App;
use Component\AbstractComponent;
use Framework\Database\DBTool;
use Framework\Debug\Exception\AlertBackException;
use Framework\Utility\SkinUtils;

/**
 * Class ListMouseover
 * @package Bundle\Component\Design
 * @author  Bagyj
 */
class ListMouseover extends \Component\AbstractComponent
{
    protected $db;

    // __('슬라이드')
    // __('페이드')
    private $effectFl = [
        'slide' => '슬라이드',
        'fade' => '페이드',
        'zoom' => 'zoomInout',
    ];
    private $speedFl = [
        '100' => '0.1',
        '200' => '0.2',
        '500' => '0.5',
        '700' => '0.7',
        '1000' => '1.0',
        '1200' => '1.2',
        '1500' => '1.5',
        '1700' => '1.7',
        '2000' => '2.0',
    ];
    private $borderFl = [
        '0', '1', '2', '3', '4', '5', '6', '7', '8', '9', '10',
    ];

    /**
     * 생성자
     */
    public function __construct()
    {
        if (gd_is_plus_shop(PLUSSHOP_CODE_LISTMOUSEOVER) === false) {
            throw new AlertBackException(__('[플러스샵] 미설치 또는 미사용 상태입니다. 설치 완료 및 사용 설정 후 플러스샵 앱을 사용할 수 있습니다.'));
        }
        if ($db === null) {
            $db = App::load('DB');
        }
        $this->db = $db;
    }

    public function getObject($obj)
    {
        return $this->$obj;
    }

    public function save($param)
    {
        $data = gd_policy('design.listMouseover');
        $inputData = [
            $param['mode'] => [
                'useFl' => $param['useFl'],
                'effectFl' => $param['effectFl'],
                'image' => $param['image'],
                'speedFl' => $param['speedFl'],
                'borderFl' => $param['borderFl'],
                'color' => $param['color'],
            ]
        ];

        $data[$param['mode']] = $inputData[$param['mode']];
        gd_set_policy('design.listMouseover', $data);
    }

    public function getImageData($goodsNo)
    {
        if (empty($goodsNo) === true) return '';
        $arrWhere = $arrBind = $retData = [];

        $arrWhere[] = 'g.goodsNo = ?';
        $this->db->bind_param_push($arrBind, 'i', $goodsNo);

        $this->db->strField = 'g.goodsNo, g.imageStorage, g.imagePath, gi.imageKind, gi.imageName, gi.imageSize ';
        $this->db->strJoin = DB_GOODS . ' AS g LEFT JOIN ' . DB_GOODS_IMAGE . ' AS gi ON g.goodsNo = gi.goodsNo AND gi.imageNo = 0';
        $this->db->strWhere = implode(' AND ', $arrWhere);
        $query = $this->db->query_complete();
        $strSQL = 'SELECT ' . array_shift($query) . ' FROM ' . implode(' ', $query);
        $data = $this->db->query_fetch($strSQL, $arrBind);

        foreach ($data as $k => &$v) {
            if (empty($v['imageKind']) === true) continue;
            $retData[] = 'data-image-' . $v['imageKind'] . ' = "' . SkinUtils::imageViewStorageConfig($v['imageName'], $v['imagePath'], $v['imageStorage'], $v['imageSize'], 'goods')[0] . '"';
        }
        unset($data);

        return @implode($retData, ' ');
    }
}
