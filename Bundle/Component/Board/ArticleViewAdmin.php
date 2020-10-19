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

/**
 * 게시판 글보기 Class
 */
namespace Bundle\Component\Board;

class ArticleViewAdmin extends \Component\Board\ArticleAdmin
{

    public function __construct($req)
    {
        parent::__construct($req);
    }

    /**
     * 관련글 가져오기
     *
     * @param array $data 글정보
     * @return array $relation
     * @throws \Exception
     */
    public function getRelation($data)
    {
        if ($data['isNotice'] == 'y') {
            return null;
        }

        if (empty($data['groupNo']) === true) {
            throw new \Exception(__('등록된 게시글이 없습니다.'));
        }

        $relationData = $this->buildQuery->selectList(null,[" groupNo = ".$data['groupNo']]);
        $relation['reply'] = null;
        if (count($relationData) > 1) {
            $boardList = new BoardList($this->req);
            $boardList->applyConfigList($relationData);
            $relation['reply'] = $relationData;
        }
        return $relation;
    }

    /**
     * increaseBoardHit
     *
     * @param $sno
     * @return bool
     * @internal param int $hit
     */
    protected function increaseBoardHit($sno)
    {
      return true;
    }
}
