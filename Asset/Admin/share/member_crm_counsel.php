<form id="frmSearch" method="get" class="content-form js-search-form">
    <input type="hidden" name="indicate" value="search"/>
    <input type="hidden" name="mode" value=""/>
    <input type="hidden" name="memNo" id="memNo" value="<?= $requestGetParams['memNo']; ?>"/>
    <input type="hidden" name="navTabs" id="navTabs" value="<?= $requestGetParams['navTabs']; ?>"/>
    <input type="hidden" name="detailSearch" value="<?= $requestGetParams['detailSearch']; ?>"/>
    <input type="hidden" name="sort" value="<?= $requestGetParams['sort']; ?>"/>
    <input type="hidden" name="pageNum" value="<?= $requestGetParams['pageNum']; ?>"/>

    <div class="form-inline search-detail-box">
        <table class="table table-cols">
            <colgroup>
                <col class="width-sm"/>
                <col/>
                <col class="width-sm"/>
                <col/>
            </colgroup>
            <tbody>
            <tr>
                <th>등록자</th>
                <td>
                    <input type="hidden" name="key" value="all"/>
                    <input type="text" name="keyword" value="<?= gd_isset($requestGetParams['keyword']); ?>"
                           class="form-control"/>
                </td>
                <th>기간검색</th>
                <td>
                    <div class="input-group js-datepicker">
                        <input type="text" class="form-control" placeholder="" name="regDt[]"
                               value="<?= $requestGetParams['regDt'][0]; ?>"/>
                        <span class="input-group-addon">
                        <span class="btn-icon-calendar">
                        </span>
                        </span>
                    </div>
                    ~
                    <div class="input-group js-datepicker">
                        <input type="text" class="form-control" placeholder="" name="regDt[]"
                               value="<?= $requestGetParams['regDt'][1]; ?>"/>
                        <span class="input-group-addon">
                        <span class="btn-icon-calendar">
                        </span>
                        </span>
                    </div>
                </td>
            </tr>
            <tr>
                <th>상담수단</th>
                <td>
                    <label>
                        <input type="radio" name="method"
                               value="" <?= $checked['method']['']; ?>/>
                        전체
                    </label>
                    <label>
                        <input type="radio" name="method"
                               value="p" <?= $checked['method']['p']; ?>/>
                        전화
                    </label>
                    <label>
                        <input type="radio" name="method"
                               value="m" <?= $checked['method']['m']; ?>/>
                        메일
                    </label>
                </td>
                <th>상담구분</th>
                <td><?= gd_select_box('kind', 'kind', $kinds, null, gd_isset($requestGetParams['kind']), '전체'); ?></td>
            </tr>
            </tbody>
        </table>
    </div>
    <div class="table-btn">
        <input type="submit" value="검색" class="btn btn-lg btn-black" id="btn_search">
    </div>
    <div class="table-header form-inline">
        <div class="pull-left">
            상담 리스트 (검색결과<strong><?= $page->recode['total']; ?></strong>건/ 전체<strong><?= $page->recode['amount']; ?></strong>건)
        </div>
        <div class="pull-right">
            <?= gd_select_box('sort', 'sort', $sorts, '',$requestGetParams['sort']); ?>
            &nbsp;
            <?= gd_select_box_by_page_view_count($requestGetParams['pageNum']); ?>
        </div>
    </div>
</form>
<form id="frmList" action="" method="get" target="ifrmProcess">
    <table class="table table-rows">
        <colgroup>
            <col>
            <col>
            <col>
            <col class="width-xs"/>
            <col class="width-xs"/>
            <col>
            <col>
        </colgroup>
        <thead>
        <tr>
            <th><input type="checkbox" class="js-checkall" data-target-name="sno"></th>
            <th>번호</th>
            <th>등록일</th>
            <th>등록자</th>
            <th>상담수단</th>
            <th>상담구분</th>
            <th>상담내용</th>
            <th>수정</th>
        </tr>
        </thead>
        <tbody>
        <?php
        if (count($list) > 0) {
            $listHtml = [];
            foreach ($list as $val) {
                $listHtml[] = '<tr class="center" data-sno="' . $val['sno'] . '">';
                $listHtml[] = '<td><input name="sno[]" type="checkbox" value="' .  $val['sno'] . '"></td>';
                $listHtml[] = '<td class="font-num">' . $page->idx-- . '</td>';
                $listHtml[] = '<td class="font-date text-nowrap">' . $val['regDt'] . '</td>';
                $listHtml[] = '<td class="text-nowrap">' . $val['managerId'] . '<br/> (' . $val['managerNm'] . ')  </td>';
                $listHtml[] = '<td>' . $val['method'] . '</td>';
                $listHtml[] = '<td>' . $val['kind'] . '</td>';
                $listHtml[] = '<td class="left" style="width:100% !important;word-break:break-all;" wrap="hard">' . str_replace(array('\r\n', '\r', '\n'), '<br />', $val['contents']) . '</td>';
                $listHtml[] = '<td><input type="button" value="수정" data-sno="' . $val['sno'] . '" class="btn btn-white btn-sm btn-modify"></td>';
                $listHtml[] = '</tr>';
            }
            echo implode('', $listHtml);
        } else {
            echo '<tr> <td colspan="8" class="no-data">상담내역이 없습니다.</td> </tr>';
        }
        ?>
        </tbody>
    </table>
    <div class="pull-left form-inline">
        <button type="submit" class="btn btn-white js-btn-delete"/>
        선택 삭제</button>
    </div>
    <div class="center"><?= $page->getPage(); ?></div>
</form>
<script type="text/javascript">
    $('.btn-register').click(function () {
        member_counsel($('#memNo').val());
    });
    // 수정 시 팝업 오픈
    $('.btn-modify').click(function () {
        var counselSno = $(this).attr('data-sno');
        member_counsel($('#memNo').val(), counselSno);
    });
    $(document).ready(function () {
        $('select[name=\'pageNum\']').change(function () {
            $('#frmSearch').submit();
        });

        $('select[name=\'sort\']').change(function () {
            $('#frmSearch').submit();
        });

        $('.js-btn-delete').click(function () {
            $('#frmList').validate({
                ignore: ':hidden',
                dialog: false,
                submitHandler: function (form) {
                    form.target = 'ifrmProcess';
                    dialog_confirm('선택한 글을 삭제하시겠습니까?\n\r영구 삭제되어 복원 불가능합니다.', function (result) {
                        if (result) {
                            var params = $(form).serializeArray();
                            params.push({name: "mode", value: "delete"});
                            $.post('../share/member_crm_counsel_ps.php', params, function (data) {
                                dialog_alert(data, '알림', {isReload: true});
                                setTimeout(function(){
                                    location.reload(true);
                                }, 2000)
                            });
                        }
                    });
                },
                rules: {
                    'sno[]': {
                        required: true
                    }
                },
                messages: {
                    'sno[]': {
                        required: '삭제할 상담 내역을 선택해주세요.'
                    },

                },
            });
        });
    });
</script>
