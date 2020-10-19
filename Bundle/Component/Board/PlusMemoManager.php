<?php
/**
 * This is commercial software, only users who have purchased a valid license
 * and accept to the terms of the License Agreement can install and use this
 * program.
 *
 * Do not edit or add to this file if you wish to upgrade Enamoo S5 to newer
 * versions in the future.
 *
 * @copyright Copyright (c) 2015 GodoSoft.
 * @link http://www.godo.co.kr
 */

namespace Bundle\Component\Board;

use \Component\Member\Group\Util;
use \Component\Page\Page;
use Respect\Validation\Validator as v;

class PlusMemoManager
{
    protected $dao;


    public function __construct()
    {
        $this->dao = new PlusMemoManagerDao();
    }

    public function getDefaultValue($key = null)
    {
        $field = [
            'maxMemoLength'=>300,
            'writerDisplay' => 'id',
            'writerDisplayLimit' => '0',
            'dateShowFl' => 'y',
            'managerDisplay' => 'nick',
            'listCount' => 10,
            'secretFl' => 'n',
            'secretCommentFl' => 'n',
            'authAccess' => 'all',
            'authWrite' => 'all',
            'authComment' => 'all',
            'commentFl' => 'n',
            'contentsStyle' => [
                'fontSize' => '12',
                'fontColor' => '#444',
                'background' => '#ffffff',
            ],
            'commentStyle' => [  //@todo:memo
                'fontSize' => '12',
                'fontColor' => '#444',
                'background' => '#f8f8f8',
            ],
            'mobileContentsStyle' => [
                'fontSize' => '15',
                'fontColor' => '#222',
                'background' => '#ffffff',
            ],
            'mobileCommentStyle' => [
                'fontSize' => '15',
                'fontColor' => '#222',
                'background' => '#ffffff',
            ],
        ];

        if ($key) {
            if (array_key_exists($key, $field)) {
                return $field[$key];
            }
            return false;
        }

        return $field;
    }

    public function getFormValue($data = null)
    {
        $strChecked = 'checked';
        $checked['writerDisplay'][$data['writerDisplay'] ?? $this->getDefaultValue('writerDisplay')] = $strChecked;
        $checked['dateShowFl'][$data['dateShowFl'] ?? $this->getDefaultValue('dateShowFl')] = $strChecked;
        $checked['managerDisplay'][$data['managerDisplay'] ?? $this->getDefaultValue('managerDisplay')] = $strChecked;
        $checked['secretFl'][$data['secretFl'] ?? $this->getDefaultValue('secretFl')] = $strChecked;
        $checked['secretCommentFl'][$data['secretCommentFl'] ?? $this->getDefaultValue('secretCommentFl')] = $strChecked;
        $checked['authAccess'][$data['authAccess'] ?? $this->getDefaultValue('authAccess')] = $strChecked;
        $checked['authWrite'][$data['authWrite'] ?? $this->getDefaultValue('authWrite')] = $strChecked;
        $checked['commentFl'][$data['commentFl'] ?? $this->getDefaultValue('commentFl')] = $strChecked;
        $checked['authComment'][$data['authComment'] ?? $this->getDefaultValue('authComment')] = $strChecked;
        $selected['writerDisplayLimit'][$data['writerDisplayLimit'] ?? $this->getDefaultValue('writerDisplayLimit')] = 'selected';
        $data = $data ? $data : $this->getDefaultValue();
        return [
            'checked' => $checked,
            'selected' => $selected,
            'data' => $data,
        ];
    }

    public function getList($req, $isPaging = true,$hasStatics = false)
    {
        if($isPaging){
            $page = gd_isset($req['page'], 1);
            $listCount = gd_isset($req['pageNum'], 10);
            $offset = ($page - 1) * $listCount;
        }

        $orderByField = gd_isset($req['sort'], 'sno desc');
        if($hasStatics){
            $list = $this->dao->selectJoinGroupByCount($req, $offset, $listCount, $orderByField);
        }
        else {
            $list = $this->dao->select($req, $offset, $listCount, $orderByField);
        }

        if ($isPaging) {
            $searchCnt = $this->dao->count($req);  //fornt
            $listNo = $searchCnt - $offset;
            if ($list) {
                foreach ($list as &$row) {
                    $row['no'] = $listNo;
                    $row['replaceCode'] = $this->getReplaceCode($row['sno']);
                    $row['regDate'] = gd_date_format('Y-m-d', $row['regDt']);
                    $row['modDate'] = gd_date_format('Y-m-d', $row['modDt']);
                    $listNo--;
                }
            }
            $totalCnt = $this->dao->count();
            $pagination = new Page($page, $searchCnt, $totalCnt, $listCount, 10);
            $pagination->setUrl(http_build_query($req));

            $data['pagination'] = $pagination->getPage();
            $data['cnt']['search'] = $searchCnt;
            $data['cnt']['total'] = $totalCnt;
            $data['cnt']['totalPage'] = $pagination->page['total'];
            $data['sort'] = [
                'sno desc' => __('등록일↓'),
                'sno asc' => __('등록일↑'),
                'modDt desc' => __('수정일↓'),
                'modDt asc' => __('수정일↑'),
                'boardName asc' => __('메모 게시판명↓'),
                'boardName desc' => __('메모 게시판명↑'),
            ];

            if($hasStatics){
              $data['sort']['totalCount asc'] =  __('게시글 수↓');
              $data['sort']['totalCount desc'] =  __('게시글 수↑');
            }
            $data['list'] = $list;
            return $data;
        }

        return $list;
    }

    public function getReplaceCode($sno)
    {
        return sprintf('{=includeWidget(\'board/plusmemo/plus_memo\', \'sno\',%s)}', $sno);
    }

    public function get($sno)
    {
        $data = $this->dao->selectBySno($sno);
        if (v::json()->validate($data['contentsStyle'])) {
            $data['contentsStyle'] = json_decode($data['contentsStyle'], true);
        } else {
            $data['contentsStyle'] = $this->getDefaultValue('contentsStyle');
        }

        if (v::json()->validate($data['mobileContentsStyle'])) {
            $data['mobileContentsStyle'] = json_decode($data['mobileContentsStyle'], true);
        } else {
            $data['mobileContentsStyle'] = $this->getDefaultValue('mobileContentsStyle');
        }

        if (v::json()->validate($data['commentStyle'])) {
            $data['commentStyle'] = json_decode($data['commentStyle'], true);
        } else {
            $data['commentStyle'] = $this->getDefaultValue('commentStyle');
        }

        if (v::json()->validate($data['mobileCommentStyle'])) {
            $data['mobileCommentStyle'] = json_decode($data['mobileCommentStyle'], true);
        } else {
            $data['mobileCommentStyle'] = $this->getDefaultValue('mobileCommentStyle');
        }

        if ($data['authAccessGroup']) {
            $_arrAuthGroup = explode(INT_DIVISION, $data['authAccessGroup']);
            $data['authAccessGroup'] = Util::getGroupName("sno IN ('" . implode("','", $_arrAuthGroup) . "')");
        }

        if ($data['authWriteGroup']) {
            $_arrAuthGroup = explode(INT_DIVISION, $data['authWriteGroup']);
            $data['authWriteGroup'] = Util::getGroupName("sno IN ('" . implode("','", $_arrAuthGroup) . "')");
        }

        if ($data['authCommentGroup']) {
            $_arrAuthGroup = explode(INT_DIVISION, $data['authCommentGroup']);
            $data['authCommentGroup'] = Util::getGroupName("sno IN ('" . implode("','", $_arrAuthGroup) . "')");
        }

        $data['replaceCode'] = $this->getReplaceCode($sno);
        $data['style'] = $this->getStyle($data);

        $data = array_merge((array)$this->getDefaultValue(),(array)$data);
        return $data;
    }

    public function getStyle($data)
    {
        if(\Request::isMobile()){
            return $this->getMobileStyle($data);
        }

        $html[] = '<style>';
        if ($data['contentsStyle']['fontSize'] || $data['contentsStyle']['fontColor']) {
            $html[] = ".memo-reply .memo-view.contents a{
                         font-size:" . $data['contentsStyle']['fontSize'] . "px !important;
                         color : " . $data['contentsStyle']['fontColor'] . " !important;
                         }";
        }
        if ($data['contentsStyle']['background']) {
            $html[] = ".memo-reply{
    background-color : " . $data['contentsStyle']['background'] . " !important; }";
        }

        if ($data['commentStyle']['fontSize'] || $data['commentStyle']['fontColor']) {
            $html[] = ".js-plus-ajax-comment .memo-view a{
                         font-size:" . $data['commentStyle']['fontSize'] . "px !important;
                         color : " . $data['commentStyle']['fontColor'] . " !important;
                         }";
        }

        if ($data['commentStyle']['background']) {
            $html[] = ".js-plus-ajax-comment .memo-reply,.js-plus-ajax-comment{
    background-color : " . $data['commentStyle']['background'] . " !important; }";
        }

        $html[] = '</style>';


        return implode(chr(10),$html);
    }

    protected function getMobileStyle($data){
        $html[] = '<style>';
        if ($data['mobileContentsStyle']['fontSize'] || $data['mobileContentsStyle']['fontColor']) {
            $html[] = " .memo-reply .memo-view a{
                         font-size:" . $data['mobileContentsStyle']['fontSize'] . "px !important;
                         color : " . $data['mobileContentsStyle']['fontColor'] . " !important;
                         }";
        }
        if ($data['mobileContentsStyle']['background']) {
            $html[] = "#plusAjaxMemoList .memo-reply {
    background-color : " . $data['mobileContentsStyle']['background'] . " !important; }";
        }

        if ($data['mobileCommentStyle']['fontSize'] || $data['mobileCommentStyle']['fontColor']) {
            $html[] = ".js-plus-ajax-comment .memo-view a{
                         font-size:" . $data['mobileCommentStyle']['fontSize'] . "px !important;
                         color : " . $data['mobileCommentStyle']['fontColor'] . " !important;
                         }";
        }

        if ($data['mobileCommentStyle']['background']) {
            $html[] = ".js-plus-ajax-comment {
    background-color : " . $data['mobileCommentStyle']['background'] . " !important; }";
        }

        $html[] = '</style>';


        return implode(chr(10),$html);
    }



    public function add($req)
    {
        if (empty($req['boardName'])) {
            throw new \Exception(__('메모게시판명을 입력해주세요.'));
        }
        $this->convertSaveData($req);

        return $this->dao->insert($req);
    }

    public function modify($req)
    {
        if (v::intVal()->validate($req['sno']) === false || !$req['boardName']) {
            throw new \Exception(__('잘못된 접근입니다.'));
        }
        $this->convertSaveData($req);

        return $this->dao->update($req);
    }

    public function remove($sno)
    {
        return $this->dao->delete($sno);
    }

    protected function convertSaveData(&$req)
    {
        $req['authAccessGroup'] = $req['authAccessGroup'] ? implode(INT_DIVISION, $req['authAccessGroup']) : '';
        $req['authWriteGroup'] = $req['authWriteGroup'] ? implode(INT_DIVISION, $req['authWriteGroup']) : '';
        $req['authCommentGroup'] = $req['authCommentGroup'] ? implode(INT_DIVISION, $req['authCommentGroup']) : '';
        $req['listCount'] = $req['listCount'] ? $req['listCount'] : $this->getDefaultValue('listCount');
        $req['contentsStyle'] = json_encode($req['contentsStyle']);
        $req['commentStyle'] = json_encode($req['commentStyle']);
        $req['mobileContentsStyle'] = json_encode($req['mobileContentsStyle']);
        $req['mobileCommentStyle'] = json_encode($req['mobileCommentStyle']);
    }

}
