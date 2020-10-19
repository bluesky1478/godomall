<script type="text/javascript">
    <!--
    function goArticlePage(mode, sno) {
        if (mode == 'reply') {
            window.open("../board/article_write.php?bdId=<?=$bdList['cfg']['bdId']?>&popupMode=yes&mode=reply&sno=" + sno, "<?=$bdList['cfg']['bdNm']?> 게시판", 'width=1600,height=800,scrollbars=yes,resizable=yes');
        }
        else {
            window.open("../board/article_view.php?bdId=<?=$bdList['cfg']['bdId']?>&popupMode=yes&mode=modify&sno=" + sno, "<?=$bdList['cfg']['bdNm']?> 게시판", 'width=1600,height=800,scrollbars=yes,resizable=yes');
        }
    }
    $(document).ready(function () {
        var colspanCnt = $('.qa-contents > thead > tr > th').length;

        $('.no-data').attr('colspan', colspanCnt);

    });
    //-->
</script>
<form name="frmSearch" method="get" id="frmSearch" class="frmSearch">
    <input type="hidden" name="bdId" value="<?= $req['bdId'] ?>"/>
    <input type="hidden" name="memNo" value="<?= $req['memNo'] ?>"/>

    <div class="search-detail-box">
        <table class="table table-cols">
            <colgroup>
                <col class="width10p">
                <col class="width40p">
                <col class="width10p">
                <col class="width40p">
            </colgroup>
            <tr>
                <th>검색어
                </th>
                <td class="form-inline">
                    <select class="form-control" name="searchField">
                        <option
                            value="subject_contents" <?php if ($req['searchField'] == 'subject_contents') echo 'selected' ?>>
                            =통합검색=
                        </option>
                        <option value="subject" <?php if ($req['searchField'] == 'subject') echo 'selected' ?>>
                            제목
                        </option>
                        <option
                            value="contents" <?php if ($req['searchField'] == 'contents') echo 'selected' ?>>내용
                        </option>
                    </select>

                    <input name="searchWord" value="<?= gd_isset($req['searchWord']) ?>"
                           class="form-control form-control">
                </td>
                <th>말머리
                </th>
                <td>
                    <div class="form-inline">
                        <?= gd_isset($bdList['categoryBox'], '-'); ?>
                    </div>
                </td>
            </tr>
            <tr>
                <th>기간검색
                </th>
                <td>
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
                                   value="<?= $searchDate[0]; ?>">
                                    <span class="input-group-addon">
                                        <span class="btn-icon-calendar">
                                        </span>
                                    </span>
                        </div>

                        ~
                        <div class="input-group js-datepicker">
                            <input type="text" class="form-control width-xs" name="rangDate[]"
                                   value="<?= $searchDate[1]; ?>">
                                    <span class="input-group-addon">
                                        <span class="btn-icon-calendar">
                                        </span>
                                    </span>
                        </div>
                        <?= gd_search_date($searchPeriod, 'rangDate', false) ?>
                    </div>
                </td>
                <th>답변상태
                </th>
                <td>
                    <select name="replyStatus" class="form-control">
                        <option value="">=전체=</option>
                        <?php foreach ($board::REPLY_STATUS_LIST as $key => $val) { ?>
                            <option value="<?= $key ?>" <?php if ($req['replyStatus'] == $key) echo 'selected' ?>><?= $val ?></option>
                        <?php } ?>
                    </select>
                </td>
            </tr>
        </table>
        <div class="table-btn">
            <input type="submit" value="검색" class="btn btn-lg btn-black">
        </div>
    </div>
</form>

<table class="table table-rows table-fixed qa-contents">
    <thead>
    <tr>
        <th class="width-2xs">번호</th>
        <th>제목</th>
        <th class="width-sm">작성일</th>
        <th class="width-2xs">조회</th>
        <?php if ($bdList['cfg']['bdReplyStatusFl'] == 'y') { ?>
            <th class="width-sm">답변상태</th>
        <?php } ?>
        <?php if ($bdList['cfg']['bdRecommendFl'] == 'y') { ?>
            <th class="width-2xs"> 추천</th>
        <?php } ?>
        <?php if ($bdList['cfg']['bdGoodsPtFl'] == 'y') { ?>
            <th class="width-2xs">평점</th>
        <?php } ?>
        <th class="width-sm">답변</th>
    </tr>
    </thead>
    <?php
    if (gd_array_is_empty($bdList['list']) === false) {
        foreach ($bdList['list'] as $val) {
            ?>
            <tr class="center">
                <td class="font-num">
                    <?php if ($val['isNotice'] == 'y') {
                        echo gd_isset($bdList['cfg']['bdIconNotice']);
                    } else {
                        echo number_format($val['listNo'] + $bdList['cfg']['bdStartNum'] - 1);
                    } ?>
                </td>
                <td align="left">
                    <?php
                    if ($val['category']) {
                        echo '[' . $val['category'] . ']';
                    } ?>
                    <?= $val['gapReply'] ?><?php if ($val['groupThread'] != '')
                        echo gd_isset($bdList['cfg']['bdIconReply']); ?>
                    <a class="btn <?php if ($val['isNotice'] == 'y') {
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
                <td><?= $val['regDt'] ?></td>
                <td><?= number_format($val['hit']) ?></td>
                <?php if ($bdList['cfg']['bdReplyStatusFl'] == 'y') { ?>
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
                <td>
                    <a onclick="goArticlePage('reply',<?= $val['sno'] ?>);"
                       class="btn btn-white btn-sm">답변</a>
                </td>
            </tr>
            <?php
        }
    } else {
        ?>
        <tr>
            <td colspan="6" height="50" class="no-data">게시물이 없습니다.</td>
        </tr>
    <?php } ?>
</table>


<div class="center"><?= $bdList['pagination']; ?></div>
