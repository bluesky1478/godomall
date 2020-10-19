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

namespace Bundle\Widget\Front\Board;

use Component\Board\BoardList;
use Framework\Cache\CacheableProxyFactory;

/**
 * Class BoardArticleWidget
 * @package Bundle\Widget\Front\Board
 * @author Lee Namju <lnjts@godo.co.kr>
 */
class BoardArticleScrollingWidget extends \Widget\Front\Widget
{
    public function index()
    {
        $bdId = $this->getData('bdId');
        $listCount = $this->getData('listCount') ?? 5;
        $strCut = $this->getData('strCut') ?? 30;
        $boardList = new BoardList(['bdId' => $bdId]);
        $noticeFl = $this->getData('noticeFl') === true ? true : false;
        $listCountWithNotice = $this->getData('listCountWithNotice') === true ? true : false;

        if ($boardList->canUsePc()) {
            $canList = $boardList->canList();
            $this->setData('canList', $canList);
            $this->setData('bdName', $boardList->getConfig('bdNm'));
            $this->setData('bdId', $bdId);
            $this->setData('cfg', $boardList->getConfig());
            if ($canList == 'y') {
                $list = $boardList->getList(false, $listCount, $strCut, null, ['subject', 'category', 'goodsPt', 'writerNm', 'writerId', 'writerNick'], $noticeFl);
                if ($list['noticeData']) {
                    // 리스트에 공지사항 포함
                    if ($noticeFl === true && $listCountWithNotice === true) {
                        $listCount = $listCount - $boardList->cfg['bdNoticeCount'] + 1;
                        $list['data'] = array_slice($list['data'], 0, $listCount);
                    }
                    $data = array_merge($list['noticeData'], $list['data']);
                } else {
                    $data = $list['data'];
                }
                $this->setData('list', $data);
            }
        }
    }
}
