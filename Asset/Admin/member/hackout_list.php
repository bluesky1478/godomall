<div class="page-header js-affix">
    <h3><?= end($naviMenu->location) ?></h3>
</div>
<div class="table-title gd-help-manual">탈퇴내역 검색</div>
<form id="frmSearchBase" method="get" class="js-form-enter-submit">
    <input type="hidden" name="sort" value="<?= gd_isset($search['sort']) ?>"/>
    <input type="hidden" name="pageNum" value="<?= Request::get()->get('pageNum', 10); ?>"/>

    <div class="form-inline">
        <table class="table table-cols">
            <colgroup>
                <col class="width-sm"/>
                <col class="width-3xl"/>
                <col class="width-sm"/>
                <col/>
            </colgroup>
            <?php if ($gGlobal['isUse']) { ?>
                <tr>
                    <th>상점</th>
                    <td colspan="3">
                        <label class="radio-inline">
                            <input type="radio" name="mallSno"
                                   value="" <?= gd_isset($checked['mallSno']['']); ?>/>
                            전체
                        </label>
                        <?php foreach ($gGlobal['useMallList'] as $item) { ?>
                            <label class="radio-inline">
                                <input type="radio" name="mallSno"
                                       value="<?= $item['sno']; ?>" <?= gd_isset($checked['mallSno'][$item['sno']]); ?>/>
                                <span class="flag flag-16 flag-<?= $item['domainFl']; ?>"></span><?= $item['mallName']; ?>
                            </label>
                        <?php } ?>
                    </td>
                </tr>
            <?php } ?>
            <tr>
                <th>아이디</th>
                <td colspan="3">
                    <input type="text" id="memId" name="memId"
                           value="<?= gd_isset($search['memId']) ?>"/>
                </td>
            </tr>
            <tr>
                <th>탈퇴일</th>
                <td colspan="3">
                    <div class="input-group js-datepicker">
                        <input type="text" class="form-control" placeholder="" name="hackDt[]"
                               value="<?= gd_isset($search['hackDt'][0]) ?>"/>
                        <span class="input-group-addon">
                            <span class="btn-icon-calendar">
                            </span>
                        </span>
                    </div>
                    ~
                    <div class="input-group js-datepicker">
                        <input type="text" class="form-control" placeholder="" name="hackDt[]"
                               value="<?= gd_isset($search['hackDt'][1]) ?>"/>
                        <span class="input-group-addon">
                            <span class="btn-icon-calendar">
                            </span>
                        </span>
                    </div>
                </td>
            </tr>
            <tr>
                <th>탈퇴유형</th>
                <td>
                    <?= gd_radio_box('hackType', $_hackType, gd_isset($search['hackType'], 'done')) ?>
                </td>
                <th>재가입여부</th>
                <td>
                    <?= gd_radio_box(
                        'rejoinFl', [
                        'done' => '전체',
                        'y'    => '가능',
                        'n'    => '불가능',
                    ], gd_isset($search['rejoinFl'], 'done')
                    ) ?>
                </td>
            </tr>
        </table>
    </div>
    <div class="table-btn">
        <input type="submit" value="검색" class="btn btn-lg btn-black" id="btnSearch"/>
    </div>
</form>

<form id="frmList" action="" method="get" target="ifrmProcess">
    <input type="hidden" name="mode" value="">
    <div class="table-header form-inline">
        <div class="pull-left"><?= gd_display_search_result($page->recode['total'], $page->recode['amount'], '명'); ?></div>
        <div class="pull-right">
            <select name="sort" class="form-control">회원가입일
                <option value="hackDt desc" <?= gd_isset($selected['sort']['hackDt desc']) ?>>탈퇴일&darr;</option>
                <option value="hackDt asc" <?= gd_isset($selected['sort']['hackDt asc']) ?>>탈퇴일&uarr;</option>
                <option value="memId desc" <?= gd_isset($selected['sort']['memId desc']) ?>>아이디&darr;</option>
                <option value="memId asc" <?= gd_isset($selected['sort']['memId asc']) ?>>아이디&uarr;</option>
            </select>&nbsp;
            <?= gd_select_box_by_page_view_count(Request::get()->get('pageNum', 10)); ?>
        </div>
    </div>

    <table class="table table-rows">
        <thead>
        <tr>
            <th class="width-xs">
                <input type="checkbox" id="chk_all" class="js-checkall" data-target-name="chk"/>
            </th>
            <th class="width-xs">번호</th>
            <?php if ($gGlobal['isUse']) { ?>
                <th>상점 구분</th>
            <?php } ?>
            <th>아이디</th>
            <th>탈퇴유형</th>
            <th>탈퇴일</th>
            <th>재가입여부</th>
            <th>탈퇴처리 IP</th>
            <th>처리자</th>
            <th>상세정보</th>
        </tr>
        </thead>
        <tbody>
        <?php
        if (gd_isset($data)) {
            foreach ($data as $key => $val) {
                $hackDt = (substr($val['hackDt'], 2, 8) != date('y-m-d')) ? substr($val['hackDt'], 2, 8) : '<span class="">' . substr($val['hackDt'], 11) . '</span>';
                $regDt = (substr($val['regDt'], 2, 8) != date('y-m-d')) ? substr($val['regDt'], 2, 8) : '<span class="">' . substr($val['regDt'], 11) . '</span>';
                $rejoinFl = ($val['rejoinFl'] === 'y') ? '가능' : '불가능';
                ?>
                <tr class="center">
                    <td>
                        <input type="checkbox" name="chk[]" value="<?= $val['sno'] ?>"/>
                    </td>
                    <td class="font-num"><?= $page->idx-- ?></td>
                    <?php if ($gGlobal['isUse']) { ?>
                        <td class="">
                            <span class="flag flag-16 flag-<?= gd_isset($gGlobal['mallList'][$val['mallSno']]['domainFl'], 'kr'); ?>"></span><?= gd_isset($gGlobal['mallList'][$val['mallSno']]['mallName'], '기준몰'); ?>
                        </td>
                    <?php } ?>
                    <td class="font-eng"><?= $val['memId'] ?></td>
                    <td class="font-kor"><?= $_hackType[$val['hackType']] ?></td>
                    <td class="font-date"><?= $hackDt ?></td>
                    <td class="font-kor"><?= $rejoinFl ?></td>
                    <td class="font-num"><?= $val['hackType'] == 'directManager' ? $val['managerIp'] : $val['regIp'] ?></td>
                    <td class="font-eng"><?= $val['hackType'] == 'directManager' ? $val['managerNm'] . '<br/>(' . $val['managerId'] . ')' : '-' ?><?= $val['deleteText'] ?></td>
                    <td>
                        <a class="btn btn-sm btn-white" href="./hackout_register.php?sno=<?= $val['sno'] ?>">보기</a>
                    </td>
                </tr>
                <?php
            }
        } else {
            ?>
            <tr>
                <td class="center" colspan="9">검색된 정보가 없습니다.</td>
            </tr>
            <?php
        }
        ?>
        </tbody>
    </table>
    <div class="table-action">
        <div class="pull-left">
            <button type="button" class="btn btn-white" id="btnDelete">선택 삭제</button>
        </div>
    </div>
</form>
<div class="center"><?= $page->getPage() ?></div>

<script type="text/javascript">
    var hackoutList = {
        frmSearchBase: $('#frmSearchBase')
        , frmList: $('#frmList')
    };

    $(document).ready(function () {

        $('#btnSearch').on('click', function (e) {
            e.preventDefault();
            hackoutList.frmSearchBase.find('input[name=sort]').val($('select[name=\'sort\']').val());
            hackoutList.frmSearchBase.find('input[name=pageNum]').val($('select[name=\'pageNum\']').val());
            hackoutList.frmSearchBase.submit();
        });

        hackoutList.frmList.on('click', '#btnDelete', function (e) {
            e.preventDefault();
            if (member.alert_check(hackoutList.frmList, '선택된 탈퇴 내역이 없습니다.')) {
                if (confirm('선택한 탈퇴 내역을 삭제하시겠습니까?\n삭제된 탈퇴 내역은 복구하실 수 없습니다.')) {
                    hackoutList.frmList.find('input[name=mode]').val('delete');
                    hackoutList.frmList.attr('method', 'post');
                    hackoutList.frmList.attr('action', '../member/hackout_ps.php');
                    hackoutList.frmList.submit();
                }
            }
        });

        $('select[name=\'sort\']').change({targetForm: '#frmSearchBase'}, member.page_sort);
        $('select[name=\'pageNum\']').change({targetForm: '#frmSearchBase'}, member.page_number);
    });
</script>
