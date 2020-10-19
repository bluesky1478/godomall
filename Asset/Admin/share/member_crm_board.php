<script type="text/javascript">
    <!--
    function goArticlePage(mode, sno, bdKind) {
        if(bdKind == 'plus_review') {
            if(mode == 'view') {
                window.open("../board/plus_review_view.php?popupMode=yes&sno=" + sno, "플러스리뷰 게시판", 'width=1600,height=800,scrollbars=yes,resizable=yes');
            }
        } else {
            if (mode == 'reply') {
                window.open("../board/article_write.php?bdId=<?=$bdList['cfg']['bdId']?>&popupMode=yes&mode=reply&sno=" + sno, "<?=$bdList['cfg']['bdNm']?> 게시판", 'width=1600,height=800,scrollbars=yes,resizable=yes');
            }
            else {
                window.open("../board/article_view.php?bdId=<?=$bdList['cfg']['bdId']?>&popupMode=yes&mode=modify&sno=" + sno, "<?=$bdList['cfg']['bdNm']?> 게시판", 'width=1600,height=800,scrollbars=yes,resizable=yes');
            }
        }
    }
    $(document).ready(function () {
        var colspanCnt = $('.board-contents > thead > tr > th').length;
        var boardSearchColspanCnt = $('.board-search-tr > td').length;

        $('.no-data').attr('colspan', colspanCnt);

        if(boardSearchColspanCnt == 1) {
            $('.board-search-td').attr('colspan', boardSearchColspanCnt+2);
        }

        $('select[name=\'pageNum\']').change(function () {
            $('#frmSearch').submit();
        });

        $('select[name=\'sort\']').change(function () {
            $('#frmSearch').submit();
        });

        $('select[name=bdId]').bind('change', function () {
            var memNo = $('input[name=memNo]').val();
            location.href = 'member_crm_board.php?memNo=' + memNo + '&navTabs=board&bdId=' + $(this).val();
        });
    });
    //-->
</script>
<form name="frmSearch" method="get" id="frmSearch" class="frmSearch">
    <input type="hidden" name="bdId" value="<?= $req['bdId'] ?>"/>
    <input type="hidden" name="memNo" value="<?= $req['memNo'] ?>"/>
    <input type="hidden" name="navTabs" id="navTabs" value="<?= $req['navTabs']; ?>"/>
    <div class="search-detail-box">
        <table class="table table-cols">
            <colgroup>
                <col class="width10p">
                <col class="width40p">
                <col class="width10p">
                <col class="width40p">
            </colgroup>
            <?php if($req['navTabs'] != 'plus_review') {?>
                <tr>
                    <th>게시판</th>
                    <td>
                        <?php if (!gd_is_provider()) { ?>
                            <select name="bdId" id="bdId" class="form-control width-lg">
                                <?php
                                if (isset($boards) && is_array($boards)) {
                                    foreach ($boards as $val) {
                                        if($val['bdKind'] == 'event') continue; // 이벤트형 게시판 제외
                                        foreach($bdCnt as $keyBdId => $bdCntVal){
                                            if($keyBdId == $val['bdId'])
                                                $countBd = $bdCntVal;
                                        }
                                        ?>
                                        <option
                                                value="<?= $val['bdId'] ?>" <?php if ($val['bdId'] == $bdList['cfg']['bdId'])
                                            echo "selected='selected'" ?> data-bdReplyStatusFl="<?=$val['bdReplyStatusFl']?>" data-bdEventFl="<?=$val['bdEventFl']?>" data-bdGoodsPtFl="<?=$val['bdGoodsPtFl']?>" data-bdGoodsFl="<?=$val['bdGoodsFl']?>"><?= $val['bdNm'] . '(' . $val['bdId'] . ') : ' . $countBd ?>개</option>
                                        <?php
                                    }
                                }
                                ?>
                            </select>
                        <?php } else { ?>
                            <?= $bdList['cfg']['bdNm'] ?> (<?= $bdList['cfg']['bdId'] ?>)
                            <input type="hidden" name="bdId" value="<?= $bdList['cfg']['bdId'] ?>"/>
                        <?php } ?>
                    </td>
                    <th>말머리</th>
                    <td>
                        <div class="form-inline">
                            <?php if ($bdList['cfg']['bdReplyStatusFl'] == 'y') { ?>
                                <?= gd_isset($bdList['categoryBox'], '-'); ?>
                            <?php } else {?>
                                -
                            <?php }?>
                        </div>
                    </td>
                </tr>
            <?php } ?>
            <tr>
                <th>기간검색</th>
                <td colspan="3">
                    <div class="form-inline">
                        <select name="searchDateFl" class="form-control">
                            <option value="regDt" <?php if ($req['searchDateFl'] == 'regDt') echo 'selected' ?>>
                                등록일
                            </option>
                            <option value="modDt" <?php if ($req['searchDateFl'] == 'modDt') echo 'selected' ?>>
                                수정일
                            </option>
                        </select>

                        <div class="input-group js-datepicker">
                            <input type="text" class="form-control width-xs" name="rangDate[]"
                                   value="<?= $req['rangDate'][0]; ?>">
                            <span class="input-group-addon">
                                        <span class="btn-icon-calendar">
                                        </span>
                                    </span>
                        </div>

                        ~
                        <div class="input-group js-datepicker">
                            <input type="text" class="form-control width-xs" name="rangDate[]"
                                   value="<?= $req['rangDate'][1]; ?>">
                            <span class="input-group-addon">
                                        <span class="btn-icon-calendar">
                                        </span>
                                    </span>
                        </div>
                        <?= gd_search_date(gd_isset($req['searchPeriod'], -1), 'rangDate', false) ?>
                    </div>
                </td>
            </tr>
            <tr class="board-search-tr">
                <?php if ($bdList['cfg']['bdReplyStatusFl'] == 'y' || $bdList['cfg']['bdAnswerStatusFl'] == 'y') { ?>
                    <th>답변상태</th>
                    <td>
                        <select name="replyStatus" class="form-control">
                            <option value="">=전체=</option>
                            <?php foreach ($board::REPLY_STATUS_LIST as $key => $val) { ?>
                                <option value="<?= $key ?>" <?php if ($req['replyStatus'] == $key) echo 'selected' ?>><?= $val ?></option>
                            <?php } ?>
                        </select>
                    </td>
                <?php }
                if($bdList['cfg']['bdGoodsPtFl'] == 'y') {
                    ?>
                    <th>평점</th>
                    <td>
                        <select name="goodsPt" class="form-control">
                            <option value="">=전체=</option>
                            <?php
                            for ($i = 1; $i < 6; $i++) { ?>
                                <option
                                        value="<?= $i ?>" <?php if ((string)$i == (string)$req['goodsPt']) echo 'selected' ?>><?= $i ?></option>
                            <?php } ?>
                        </select>
                    </td>
                <?php }?>
            </tr>
            <tr>
                <th>검색어</th>
                <td colspan="3">
                    <div class="form-inline">
                        <select class="form-control" name="searchField">
                            <?php foreach($boardSearchField as $searchSelectKey => $searchSelectVal) {?>
                                <option value="<?=$searchSelectKey;?>" <?php if ($req['searchField'] == $searchSelectKey) echo 'selected' ?>>
                                    <?=$searchSelectVal;?>
                                </option>
                            <?php } ?>
                        </select>

                        <input name="searchWord" value="<?= gd_isset($req['searchWord']) ?>"
                               class="form-control form-control">
                    </div>
                </td>
            </tr>
            <?php
            if($req['navTabs'] == 'plus_review') {?>
                <tr>
                    <th class="width-md">속성</th>
                    <td class="form-inline">
                        <label class="radio-inline"><input type="radio" name="reviewType" value="" <?php if ($req['reviewType'] == '') echo 'checked' ?>>전체</label>
                        <label class="radio-inline"><input type="radio" name="reviewType" value="photo" <?php if ($req['reviewType'] == 'photo') echo 'checked' ?>>포토리뷰</label>
                        <label class="radio-inline"><input type="radio" name="reviewType" value="text" <?php if ($req['reviewType'] == 'text') echo 'checked' ?>>일반리뷰</label>
                    </td>
                    <th class="width-md">댓글여부</th>
                    <td class="form-inline">
                        <label class="radio-inline"><input type="radio" name="isMemo" value="" <?php if ($req['isMemo'] == '') echo 'checked' ?>>전체</label>
                        <label class="radio-inline"><input type="radio" name="isMemo" value="y" <?php if ($req['isMemo'] == 'y') echo 'checked' ?>>댓글있음</label>
                        <label class="radio-inline"><input type="radio" name="isMemo" value="n" <?php if ($req['isMemo'] == 'n') echo 'checked' ?>>댓글없음</label>
                    </td>
                </tr>
            <?php }?>
        </table>
        <div class="table-btn">
            <input type="submit" value="검색" class="btn btn-lg btn-black">
        </div>
    </div>
    <div class="table-header">
        <div class="pull-left">
            검색&nbsp;<strong><?=number_format($bdList['cnt']['search']) ?></strong>개/
            전체&nbsp;<strong><?= number_format($bdList['cnt']['total']) ?></strong>개
        </div>
        <div class="pull-right">
            <div class="form-inline">
                <?= gd_select_box('sort', 'sort', $bdList['sort'], null, $req['sort']); ?>
                <?= gd_select_box_by_page_view_count(Request::get()->get('pageNum',10)); ?>
            </div>
        </div>
    </div>
</form>

<table class="table table-rows board-contents">
    <thead>
    <tr>
        <th class="width-2xs">번호</th>
        <?php if ($bdList['cfg']['bdGoodsFl'] === 'y' && $bdList['cfg']['bdGoodsType'] === 'goods') { ?>
            <th class="width-sm">상품이미지</th>
        <?php } ?>
        <?php if($req['navTabs'] != 'plus_review') {?>
        <th>제목</th>
        <?php } else { ?>
        <th>내용</th>
        <th class="width-2xs">속성</th>
        <th class="width-2xs">댓글</th>
        <th class="width-2xs">평가</th>
        <th class="width-2xs">추천</th>
        <?php } ?>
        <th class="width-sm">작성일</th>
        <?php if($req['navTabs'] != 'plus_review') {?>
        <th class="width-2xs">조회</th>
        <?php } ?>
        <?php if ($bdList['cfg']['bdReplyStatusFl'] == 'y' || $bdList['cfg']['bdAnswerStatusFl'] == 'y') { ?>
            <th class="width-sm">답변상태</th>
        <?php } ?>
        <?php if ($bdList['cfg']['bdRecommendFl'] == 'y') { ?>
            <th class="width-2xs"> 추천</th>
        <?php } ?>
        <?php if ($bdList['cfg']['bdGoodsPtFl'] == 'y') { ?>
            <th class="width-2xs">평점</th>
        <?php } ?>
        <?php if ($bdList['cfg']['bdReplyFl'] == 'y') { ?>
        <th class="width-sm">답변</th>
        <?php } ?>
    </tr>
    </thead>
    <?php
    if (gd_array_is_empty($bdList['list']) === false) {
        foreach ($bdList['list'] as $val) {
            if ($bdList['cfg']['bdGoodsFl'] === 'y' && $bdList['cfg']['bdGoodsType'] === 'goods') {
                //게시글 관리에서 노출되는 상품이미지 항목의 노이미지 노출을 위해 imageStorage가 없는 경우 local 셋팅
                if(!gd_isset($val['imageStorage'])){
                    $val['imageStorage'] = 'local';
                }
            }
            ?>
            <tr class="center">
                <td class="font-num">
                <?php if($req['navTabs'] != 'plus_review') {
                    if ($val['isNotice'] == 'y') {
                        echo gd_isset($bdList['cfg']['bdIconNotice']);
                    }
                    else {
                        echo $val['articleListNo'] ;
                    }
                } else {
                    echo $val['no'];
                }
                ?>
                </td>
                <?php if ($bdList['cfg']['bdGoodsFl'] === 'y' && $bdList['cfg']['bdGoodsType'] === 'goods') { ?>
                    <td><?=gd_html_goods_image($val['goodsNo'], $val['imageName'], $val['imagePath'], $val['imageStorage'], 40, $val['goodsNm'], '_blank'); ?></td>
                <?php } ?>
                <?php if($req['navTabs'] != 'plus_review') {?>
                <td align="left">
                    <?php
                    if ($val['category']) {
                        echo '[' . $val['category'] . ']';
                    } ?>
                    <?= $val['gapReply'] ?><?php if ($val['groupThread'] != '')
                        echo gd_isset($bdList['cfg']['bdIconReply']); ?>
                    <a style="white-space: normal !important;display: inline !important;" class="btn <?php if ($val['isNotice'] == 'y') {
                        echo 'notice';
                    } ?>"
                       onclick="javascript:goArticlePage('view',<?= $val['sno'] ?>);">
                        <?= $val['subject']; ?>
                    </a>
                    <?php if ($bdList['cfg']['bdMemoFl'] == 'y' && $val['memoCnt']) {
                        echo '&nbsp;<span class="memoCnt">[' . gd_isset($val['memoCnt']) . ']</span>';
                    } ?>
                    <?php if ($val['isSecret'] == 'y') {
                        echo gd_isset($bdList['cfg']['bdIconSecret']);
                    } ?>
                    <?php if ($val['isNew'] == 'y')
                        echo gd_isset($bdList['cfg']['bdIconNew']); ?>
                    <?php if ($val['isHot'] == 'y')
                        echo gd_isset($bdList['cfg']['bdIconHot']); ?>
                    <?php if ($val['isFile'] == 'y')
                        echo gd_isset($bdList['cfg']['bdIconFile']); ?>
                </td>
                <?php } else { ?>
                <td align="left">
                    <?php if($val['isFile'] == 'y') {?>
                        <img src="<?=PATH_ADMIN_GD_SHARE?>img/ico_bd_file.gif" />
                    <?php }?>
                    <?php if($val['isNew'] == 'y') {?>
                        <img src="<?=PATH_ADMIN_GD_SHARE?>img/ico_bd_new.gif" />
                    <?php }?>
                    <a style="white-space: normal !important;" class="btn <?php if ($val['isNotice'] == 'y') {
                        echo 'notice';
                    } ?>"
                       onclick="javascript:goArticlePage('view',<?= $val['sno'] ?>, 'plus_review');"><?=$val['listContents'];?></a>
                </td>
                <td><?=$val['reviewTypeText'];?></td>
                <td><?=number_format($val['memoCnt']);?></td>
                <td><?=$val['goodsPt'];?></td>
                <td><?=number_format($val['recommend']);?></td>
                <?php } ?>
                <td><?= $val['regDt'] ?></td>
                <?php if($req['navTabs'] != 'plus_review') {?>
                <td><?= number_format($val['hit']) ?></td>
                <?php } ?>
                <?php if ($bdList['cfg']['bdReplyStatusFl'] == 'y' || $bdList['cfg']['bdAnswerStatusFl'] == 'y') { ?>
                <td>
                    <?= $val['replyStatusText'] ?>
                </td>
                <?php } ?>
                <?php if ($bdList['cfg']['bdRecommendFl'] == 'y') { ?>
                <td class="width-2xs">  <?= gd_isset($val['recommend'], 0) ?></td>
                <?php } ?>
                <?php if ($bdList['cfg']['bdGoodsPtFl'] == 'y') { ?>
                <td class="width-2xs"><?= gd_isset($val['goodsPt'], 0) ?></td>
                <?php } ?>
                <?php if ($bdList['cfg']['bdReplyFl'] == 'y') { ?>
                <td>
                    <a onclick="goArticlePage('reply',<?= $val['sno'] ?>);" class="btn btn-white btn-sm">답변</a>
                </td>
                <?php } ?>
            </tr>
            <?php
        }
    } else {
        ?>
        <tr>
            <td colspan="8" height="50" class="no-data">게시물이 없습니다.</td>
        </tr>
    <?php } ?>
</table>


<div class="center"><?= $bdList['pagination']; ?></div>
