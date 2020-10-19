<div class="page-header js-affix">
    <h3><?= end($naviMenu->location); ?>
    </h3>
    <div class="btn-group">
        <input type="button"  value="기획전 등록" class="btn btn-red-line js-register"/>

    </div>
</div>

<form id="frmSearchBase" name="frmSearchBase" method="get" class="js-form-enter-submit">
    <input type="hidden" name="detailSearch" value="<?= $search['detailSearch']; ?>"/>

    <div class="table-title gd-help-manual">
        기획전 검색
    </div>
    <div class="search-detail-box">
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
                        <?= gd_select_box('key', 'key', $search['eventSaleListSelect'], null, $search['key'], null, ''); ?>
                        <input type="text" name="keyword" value="<?= $search['keyword']; ?>" class="form-control"/>
                    </div>
                </td>
            </tr>
            <tr>
                <th>기간검색</th>
                <td colspan="3">
                    <div class="form-inline">
                        <select name="searchDateFl" class="form-control">
                            <option value="regDt" <?= gd_isset($selected['searchDateFl']['regDt']); ?>>등록일</option>
                            <option value="displayStartDate" <?= gd_isset($selected['searchDateFl']['displayStartDate']); ?>>시작일</option>
                            <option value="displayEndDate" <?= gd_isset($selected['searchDateFl']['displayEndDate']); ?>>종료일</option>
                        </select>

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
            <tr>
                <th>진열유형</th>
                <td colspan="3">
                    <label class="radio-inline">
                        <input type="radio" name="displayCategory" value="" <?= gd_isset($checked['displayCategory']['']); ?>/>전체
                    </label>
                    <label class="radio-inline">
                        <input type="radio" name="displayCategory" value="n" <?= gd_isset($checked['displayCategory']['n']); ?>/>일반형
                    </label>
                    <label class="radio-inline">
                        <input type="radio" name="displayCategory" value="g" <?= gd_isset($checked['displayCategory']['g']); ?>/>그룹형
                    </label>
                </td>
            </tr>
            </tbody>
            <tbody class="js-search-detail">
            <tr>
                <th>노출범위</th>
                <td colspan="3">
                    <label class="radio-inline"><input type="radio" name="device"
                                  value="" <?= gd_isset($checked['device']['']); ?>/>전체</label>
                    <label class="radio-inline"><input type="radio" name="device"
                                  value="yy" <?= gd_isset($checked['device']['yy']); ?>/>PC+모바일</label>
                    <label class="radio-inline"><input type="radio" name="device"
                                  value="yn" <?= gd_isset($checked['device']['yn']); ?>/>PC쇼핑몰</label>
                    <label class="radio-inline"><input type="radio" name="device"
                                  value="ny" <?= gd_isset($checked['device']['ny']); ?>/>모바일쇼핑몰</label>
                </td>
            </tr>
            </tbody>
        </table>
    </div>
    <div class="table-btn">
        <input type="submit" value="검색" class="btn btn-lg btn-black">
    </div>

    <div class="table-header">
        <div class="pull-left">
            <?= gd_display_search_result($page->recode['total'], $page->recode['amount'], '개'); ?>
        </div>
        <div class="pull-right form-inline">
            <?= gd_select_box('sort', 'sort', $search['eventSaleSortList'], null, $search['sort'], null); ?>
            <?php echo gd_select_box('pageNum', 'pageNum', gd_array_change_key_value([10, 20, 30, 40, 50, 60, 70, 80, 90, 100, 200, 300, 500]), '개 보기', Request::get()->get('pageNum'), null); ?>
        </div>
    </div>
</form>

<form id="frmList" action="" method="get" target="ifrmProcess">
    <input type="hidden" name="mode" value="">
    <table class="table table-rows">
        <thead>
        <tr>
            <th class="width5p">
                <input type="checkbox" class="js-checkall" data-target-name="sno">
            </th>
            <th class="width5p">번호</th>
            <th>기획전명</th>
            <th class="width10p">등록일</th>
            <th class="width7p">등록자</th>
            <th class="width10p">시작일</th>
            <th class="width10p">종료일</th>
            <th class="width10p">노출범위</th>
            <th class="width7p">상태</th>
            <th class="width7p">링크복사</th>
            <th class="width7p">미리보기</th>
            <th class="width7p">수정</th>
        </tr>
        </thead>
        <tbody>
        <?php
        if (gd_isset($data)) {
            foreach ($data as $key => $val) {
                ?>
                <tr>
                    <td class="center">
                        <input name="sno[]" type="checkbox" value="<?= $val['sno']; ?>">
                    </td>
                    <td class="center number"><?= number_format($page->idx--); ?></td>
                    <td><a href="./event_sale_register.php?sno=<?= $val['sno'] ?>&mode=modify"><?= $val['themeNm']; ?></a></td>
                    <td class="center font-date"><?= gd_date_format('Y-m-d H:i:s', $val['regDt']); ?></td>
                    <td class="center">
                        <?= $val['writer'].$val['deleteText'] ?>
                    </td>
                    <td class="center font-date">
                        <?= $val['displayStartDate']; ?>
                    </td>
                    <td class="center font-date">
                        <?= $val['displayEndDate']; ?>
                    </td>
                    <td class="center"><?= $val['displayDeviceText'] ?></td>
                    <td class="center"><?= $val['statusText'] ?></td>
                    <td class="center" style="vertical-align: top">
                        <?php if ($val['pcFl'] == 'y') { ?>
                            <button type="button" data-clipboard-text="<?= $val['eventSaleUrl'] ?>" class="js-clipboard btn btn-white btn-xs width-3xs"
                                    title="<?= $val['themeNm']; ?>">
                                PC
                            </button>
                        <?php } ?>
                        <?php if ($val['mobileFl'] == 'y') { ?>
                            <div style="padding:3px"></div>
                            <button type="button" data-clipboard-text="<?= $val['MobileEventSaleUrl'] ?>" class="js-clipboard btn btn-white btn-xs width-3xs"
                                    title="<?= $val['themeNm']; ?>">
                                모바일
                            </button>
                        <?php } ?>
                    </td>
                    <td class="center" style="vertical-align: top">
                        <?php if ($val['pcFl'] == 'y') { ?>
                            <a href="<?= $val['eventSaleUrl'] ?>" target="_blank" class="btn btn-white btn-xs width-3xs">
                                PC
                            </a>
                        <?php } ?>
                        <?php if ($val['mobileFl'] == 'y') { ?>
                            <div style="padding:3px"></div>
                            <a href="<?= $val['MobileEventSaleUrl'] ?>" target="_blank" class="btn btn-white btn-xs width-3xs">
                                모바일
                            </a>
                        <?php } ?>
                    </td>
                    <td class="center">
                        <a href="./event_sale_register.php?sno=<?= $val['sno'] ?>&mode=modify" class="btn btn-white btn-sm">수정</a>
                    </td>
                </tr>
                <?php
            }
        } else {
            ?>
            <tr>
                <td class="no-data" colspan="12">검색된 정보가 없습니다.</td>
            </tr>
            <?php
        }
        ?>
        </tbody>
    </table>

    <div class="table-action">
        <div class="pull-left">
            <button type="submit" class="btn btn-white">선택 삭제</button>
        </div>
        <div class="pull-right">
            <button type="button" class="btn btn-white js-sms-send" data-type="select" data-opener="promotion" data-target-selector=":checkbox[name='sno[]']:checked">기획전 홍보 SMS발송</button>
        </div>
    </div>
</form>
<div class="center"><?= $page->getPage(); ?></div>


<script type="text/javascript">
    <!--
    $(document).ready(function () {

        $("#frmList").validate({
            dialog : false,
            submitHandler: function (form) {
                dialog_confirm('선택한 항목을 삭제하시겠습니까?\n삭제된 항목은 복구하실 수 없습니다.', function (result) {
                    if (result) {
                        $('#frmList input[name=\'mode\']').val('main_delete');
                        $('#frmList').attr('method', 'post');
                        var data = $('#frmList').serializeArray();
                        post_with_reload('../goods/display_ps.php', data);
                    }
                });
            },
            rules: {
                "sno[]": 'required'
            },
            messages: {
                "sno[]": '선택된 항목이 없습니다.'
            }
        });

        // 등록
        $('.js-register').click(function () {
            location.href = './event_sale_register.php';
        });

        $('select[name=\'pageNum\']').change(function () {
            $('#frmSearchBase').submit();
        });

        $('select[name=\'sort\']').change(function () {
            $('#frmSearchBase').submit();
        });

    });

    //-->
</script>
