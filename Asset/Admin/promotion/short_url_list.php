<form id="frmSearch" action="" method="get" class="js-form-enter-submit">
    <input type="hidden" name="mode" value="">
    <div class="page-header js-affix">
        <h3><?php echo end($naviMenu->location); ?></h3>
        <div class="btn-group">
            <button type="button" class="btn btn-red-line js-shorturl-regist">단축URL 등록</button>
        </div>
    </div>
    <div class="table-title">
        단축URL 검색
    </div>
    <table class="table table-cols">
        <colgroup>
            <col class="width-md"/>
            <col/>
            <col class="width-md"/>
            <col/>
        </colgroup>
        <tbody>
        <tr>
            <th>검색어</th>
            <td colspan="3">
                <div class="form-inline">
                    <?= gd_select_box('key', 'key', $search['combineSearch'], null, $search['key'], null, ''); ?>
                    <input type="text" name="keyword" value="<?= $search['keyword']; ?>" class="form-control"/>
                </div>
            </td>
        </tr>
        <tr>
            <th>등록일</th>
            <td colspan="3">
                <div class="form-inline">
                    <div class="input-group js-datepicker">
                        <input type="text" class="form-control width-xs" name="searchDate[]" value="<?= $search['searchDate'][0]; ?>">
                    <span class="input-group-addon">
                        <span class="btn-icon-calendar">
                        </span>
                    </span>
                    </div>

                    ~
                    <div class="input-group js-datepicker">
                        <input type="text" class="form-control width-xs" name="searchDate[]" value="<?= $search['searchDate'][1]; ?>">
                    <span class="input-group-addon">
                        <span class="btn-icon-calendar">
                        </span>
                    </span>
                    </div>

                    <?= gd_search_date($search['searchPeriod'], 'searchDate', false) ?>
                </div>
            </td>
        </tr>
        </tbody>
    </table>
    <div class="table-btn">
        <input type="submit" value="검색" class="btn btn-lg btn-black">
    </div>

    <div class="table-header">
        <div class="pull-left">
            <?= gd_display_search_result($page->recode['total'], $page->recode['amount'], '개'); ?>
        </div>
        <div class="pull-right form-inline">
            <?= gd_select_box('sort', 'sort', $search['sortList'], null, $search['sort'], null); ?>
            <?php echo gd_select_box('pageNum', 'pageNum', gd_array_change_key_value([10, 20, 30, 40, 50, 60, 70, 80, 90, 100, 200, 300, 500]), '개 보기', Request::get()->get('pageNum'), null); ?>
        </div>
    </div>
</form>

<form id="frmList" action="" method="get" target="ifrmProcess">
    <input type="hidden" name="mode" value="deleteShortUrl">
    <table class="table table-rows">
        <thead>
        <tr>
            <th class="width-2xs">
                <input type="checkbox" id="chk_all" class="js-checkall" data-target-name="chk"/>
            </th>
            <th class="width-2xs">번호</th>
            <th>단축URL</th>
            <th>URL설명</th>
            <th>원본URL</th>
            <th>전체조회수</th>
            <th>등록자</th>
            <th>등록일</th>
            <th>복사</th>
            <th>상세보기</th>
        </tr>
        </thead>
        <tbody>
        <?php
        if (empty($data) === false) {
            foreach ($data as $val) {
                ?>
                <tr class="center">
                    <td>
                        <input type="checkbox" name="chk[]" value="<?php echo $val['sno']; ?>"/>
                    </td>
                    <td class="font-num"><?= $page->idx--; ?></td>
                    <td><?= $val['shortUrl']; ?></td>
                    <td><?= $val['description']; ?></td>
                    <td><?= $val['longUrl']; ?></td>
                    <td><?= number_format($val['count']); ?></td>
                    <td>
                        <?php if (empty($val['managerNm']) === false) { ?>
                        <?= $val['managerNm']; ?><br>
                        <span class="text-muted">(<?= $val['managerId']; ?>)</span>
                        <?php } ?>
                    </td>
                    <td class="font-date"><?php echo gd_date_format('Y-m-d h:i:s', $val['regDt']); ?></td>
                    <td><button type="button" class="btn btn-sm btn-white js-clipboard" title="단축주소" data-clipboard-text="<?=$val['shortUrl']?>">복사</button></td>
                    <td><a href="./short_url_view.php?sno=<?=$val['sno']?>&searchPeriod=<?=$search['searchPeriod']?>&searchDate[0]=<?=$search['searchDate'][0]?>&searchDate[1]=<?=$search['searchDate'][1]?>" class="btn btn-sm btn-white rowButton" name="rowEdit">상세보기</a></td>
                </tr>
                <?php
            }
        } else {
        ?>
            <tr>
                <td colspan="10" class="no-data">단축 URL이 없습니다.</td>
            </tr>
        <?php
        }
        ?>
        </tbody>
    </table>

    <div class="table-action">
        <div class="pull-left">
            <button class="btn btn-white js-selected-delete" type="button">선택 삭제</button>
        </div>
    </div>
    <div class="center"><?php echo $page->getPage(); ?></div>
</form>

<script type="text/javascript">
    <!--
    $(document).ready(function () {
        // 단축 URL 등록하기
        $('.js-shorturl-regist').click(function (e) {
            $.post('layer_short_url_regist.php', '', function (data) {
                layer_popup(data, '단축 URL 등록');
            });
        });

        // 단축 URL 삭제하기
        $('button.js-selected-delete').on('click', function (e) {
            if ($(':checkbox:checked').length == 0) {
                alert('선택된 단축URL이 없습니다.');
                return;
            }
            dialog_confirm('선택한 ' + $(':checkbox:checked').length + '개 단축URL을 삭제하시겠습니까? 삭제 시 정보는 복구되지 않습니다.', function (result) {
                if (result) {
                    var params = $('#frmList').serializeArray();
                    post_with_reload('short_url_ps.php', params);
                }
            }, '단축URL 삭제', {
                confirmLabel: '삭제',
                cancelLabel: '취소'
            });
        });

        // 리스트 정렬
        $('#sort, #pageNum').change(function (e) {
            $('#frmSearch').submit();
        });
    });
    //-->
</script>
