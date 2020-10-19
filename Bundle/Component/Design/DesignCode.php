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

use Framework\Utility\ComponentUtils;
use Request;

/*
 * 디자인 치환코드
 * 디자인 치환코드 관련 Class
 *
 * @author    kookoo135
 * @version   1.0
 * @since     1.0
 * @copyright ⓒ 2016, NHN godo: Corp.
 */

class DesignCode
{
    private $db;
    private $getData;

    /**
     * 생성자
     */
    public function __construct()
    {
        $this->db = \App::load('DB');
        $this->search = Request::post()->toArray();
    }

    /**
     * 디자인 치환코드 load
     *
     * @author kookoo135
     */
    public function getDesignCode($fileName)
    {
        $data = ComponentUtils::getDesignCode($fileName, '', $this->search['key'], $this->search['keyword']);

        $getData = [];
        if (!$data) {
            $getData[] = '
                <tr>
                    <td align="center"> ' . __('검색결과가 없습니다.') . '</td>
                </tr>
            ';
        } else {
            foreach ($data as $val) {
                $getDataExample = '';
                if ($val->designCodeExample) {
                    $getDataExample = '<button type="button" title="' . __('치환코드 예제보기') . '" onclick="code_view(' . $val->sno . ')" class="btn btn-gray">' . __('예제보기') . '</button>';
                }
                $getData[] = '
                <tr>
                    <td>
                        <div class="design-code-info">' . nl2br(htmlspecialchars($val->designCode)) . '</div>
                        <div class="design-code-data">' . htmlspecialchars($val->designCodeInfo) . '</div>
                        <div class="design-code-copy">
                            ' . $getDataExample . '
                            <button type="button" title="' . __('치환코드 복사') . '" class="btn btn-gray js-clipboard" data-clipboard-text="' . htmlspecialchars($val->designCode) . '">' . __('코드복사') . '</button>
                        </div>
                    </td>
                </tr>
                ';
            }
            unset($getDataExample);
        }
        return $getData;
    }
}
