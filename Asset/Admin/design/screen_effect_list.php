<style>
    div .count {
        margin-bottom: 1%;
    }

    div .count span {
        color: red;
    }

    #none_display {
        display: none;
    }

    #none_display td {
        text-align: center;
    }

    #btnStop {
        margin-right: 10px;
    }

    #bottom {
        background-color: #EAEAEA;
    }
</style>

<div class="page-header">
    <h3><?=end($naviMenu->location);?></h3>
    <div class="btn-group">
        <a href="./screen_effect_regist.php" onclick="return AdminIndex.checkTotalCount()" class="btn btn-red-line" role="button">효과 등록</a>
    </div>
</div>

<form id="frmSearchBase" name="frmSearchBase" method="get" class="js-form-enter-submit"
      onsubmit="return AdminIndex.checkSearchValidation()">
    <div class="table-title">효과 검색</div>
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
                <th>기간검색</th>
                <td colspan="3">
                    <div class="form-inline">
                        <select name="searchDateFl" class="form-control">
                            <option value="reg_dt"<?=$searchDateFl == 'reg_dt' ? ' selected="selected"':''?>>등록일</option>
                            <option value="effect_start_date"
                                <?=$searchDateFl == 'effect_start_date' ? ' selected="selected"':''?>>시작일</option>
                            <option value="effect_end_date"
                                <?=$searchDateFl == 'effect_end_date' ? ' selected="selected"':''?>>종료일</option>
                            <option value="mod_dt"
                                <?=$searchDateFl == 'mod_dt' ? ' selected="selected"':''?>>수정일</option>
                        </select>

                        <div class="input-group js-datepicker">
                            <input type="text" class="form-control width-xs" name="searchDate[]"
                                   value="<?=$searchDate[0]?>">
                            <span class="input-group-addon">
                            <span class="btn-icon-calendar">
                            </span>
                        </span>
                        </div>

                        ~
                        <div class="input-group js-datepicker">
                            <input type="text" class="form-control width-xs" name="searchDate[]"
                                   value="<?=$searchDate[1]?>">
                            <span class="input-group-addon">
                            <span class="btn-icon-calendar">
                            </span>
                        </span>
                        </div>

                        <?= gd_search_date(null, 'searchDate', false) ?>
                    </div>
                </td>
            </tr>
            <tr>
                <th>효과명</th>
                <td colspan="3">
                    <div class="form-inline">
                        <input type="text" name="effectName" value="<?=$effectName?>" style="width: 250px" class="form-control"/>
                    </div>
                </td>
            </tr>
            </tbody>
        </table>
    </div>
    <div class="table-btn">
        <input type="submit" value="검색" class="btn btn-lg btn-black">
    </div>
</form>

<ul class="nav nav-tabs mgb30" style="margin-top: 50px">
    <li class="choice_list active" onclick="AdminIndex.effectTab(0)">
        <a href="#all">전체 (<?=$listCount?>)</a>
    </li>
    <li class="choice_list" onclick="AdminIndex.effectTab(1)">
        <a href="#applying">적용 진행중 효과</a>
    </li>
    <li class="choice_list" onclick="AdminIndex.effectTab(2)">
        <a href="#reserved">적용 예정 효과</a>
    </li>
    <li class="choice_list" onclick="AdminIndex.effectTab(3)">
        <a href="#expired">적용 종료 효과</a>
    </li>
</ul>
<div class="count">
    <b> 검색 <span><?=$listCount?></span>개 </b> / <b> 전체 <span><?=$totalCount?></span>개</b>
</div>

<form id="frmStop" method="POST" action="screen_effect_ps.php" target="ifrmProcess">
    <input type="hidden" name="mode" value="stop">
</form>
<form id="frmDelete" method="POST" action="screen_effect_ps.php" target="ifrmProcess">
    <input type="hidden" name="mode" value="delete">
</form>
<form id="effect_index_form" method="POST" action="screen_effect_register.php">
    <table class="table table-rows">
        <colgroup>
            <col width="30px" />
            <col width="50px" />
            <col width="*" />
            <col width="200px" />
            <col width="160px" />
            <col width="110px" />
            <col width="100px" />
            <col width="70px" />
        </colgroup>
        <thead>
        <tr>
            <th><input id="check_all" onclick="AdminIndex.checkAll()" type="checkbox" /></th>
            <th>번호</th>
            <th>효과명</th>
            <th>적용기간</th>
            <th>치환코드</th>
            <th>등록일/수정일</th>
            <th>등록자</th>
            <th>수정</th>
        </tr>
        </thead>
        <tbody>
        <?php foreach ($totalList as $index => $item) { ?>
        <tr class="effect_list">
            <td align="center">
                <input onclick="AdminIndex.check()" name="checkbox" type="checkbox" value="<?=$item['sno']?>" />
            </td>
            <td align="center" class="numbering"><?=$page->idx--?></td>
            <td align="center"><a href="screen_effect_regist.php?sno=<?=$item['sno']?>"><?=$item['effect_name']?></a></td>
            <td align="center" class="date">
                <?php if ($item['effect_limited'] == 0) {?>제한 없음<?php } else { ?>
                <?=$item['effect_start_date']?> <?=$item['effect_start_time']?><br>
                ~ <?=$item['effect_end_date']?> <?=$item['effect_end_time']?>
                <?php } ?>
            </td>
            <td align="center" style="text-align: center">
                <input type="button" data-original-title="치환코드" data-content="<?=$item['effect_code']?>"
                       data-placement="bottom" class="btn btn-gray js-popover" value="코드보기">
                <input type="button" onclick="AdminIndex.clipboard('<?=$item['effect_code']?>')"
                       class="copy btn btn-white" value="복사">
            </td>
            <td align="center">
                <div class="reg_date_log"><?=$getDate($item['reg_dt'])?></div>
                <div class="mod_date_log"><?=$getDate($item['mod_dt'])?></div>
            </td>
            <td align="center"><?=$admin_id ? $admin_id : $managerId?></td>
            <td align="center">
                <a href="screen_effect_regist.php?sno=<?=$item['sno']?>" class="btn btn-white">수정</a>
            </td>
        </tr>
        <?php } ?>

        <tr id="none_display">
            <td align="center" colspan="100%">등록된 화면효과가 없습니다.</td>
        </tr>
        <?php if ($listCount == 0) {?>
        <tr id="none_list">
            <td align="center" colspan="100%" style="text-align: center">등록된 화면효과가 없습니다.</td>
        </tr>
        <?php }?>
        </tbody>
        <tfoot>
        <tr id="bottom">
            <td align="center" colspan="100%" style="text-align: left; padding-left: 10px;">
                <input id="btnStop" onclick="AdminIndex.stop()" class="btn btn-white" type="button" value="선택 종료"
                    style="margin-right: 10px">
                <input id="delete" onclick="AdminIndex.delete()" class="btn btn-white" type="button" value="선택 삭제">
            </td>
        </tr>
        </tfoot>
    </table>
</form>

<div class="center"><?=$page->getPage()?></div>

<div class="information">
    <h4>안내</h4>
    <div class="content">
        <p>
            <b>
                <span style="font-size: 10pt; font-family: 나눔고딕, NanumGothic;">화면 효과는 어떻게 적용하나요?</span>
            </b>
        </p>
        <p>
            <span style="font-size: 8pt; font-family: 나눔고딕, NanumGothic;">ㆍ&nbsp; 생성된 치환코드를 복사하여 '디자인 > 디자인 설정'에서 효과를 적용하고자 하는 페이지에 삽입합니다. &nbsp;</span>
        </p>
        <p>
            <span style="font-size: 8pt; font-family: 나눔고딕, NanumGothic;">ㆍ&nbsp; 치환코드는 &#123;# header&#125; 아래에 삽입해야지만 정상적으로 적용됩니다. &nbsp;</span>
        </p>
        <p>
            <span style="font-size: 8pt; font-family: 나눔고딕, NanumGothic;">ㆍ&nbsp; 화면 효과를 삭제할 경우 삽입한 치환코드는 자동으로 삭제되지 않으며, 추가했던 페이지에서 직접 삭제하셔야 합니다. &nbsp;</span>
        </p>
        <p>
            <b>
                <span style="font-size: 10pt; font-family: 나눔고딕, NanumGothic;">선택 종료는 무엇인가요?</span>
            </b>
        </p>
        <p>
            <span style="font-size: 8pt; font-family: 나눔고딕, NanumGothic;">ㆍ&nbsp; 종료할 효과를 선택하고 [선택 종료] 클릭 시 진행중인 효과의 종료일이 현재 시점으로 변경되어 쇼핑몰에 효과가 노출되지 않습니다. &nbsp;</span>
        </p>
        <p>
            <span style="font-size: 8pt; font-family: 나눔고딕, NanumGothic;">ㆍ&nbsp; 전체 / 진행중 효과 페이지에서 진행중인 효과만 종료 처리됩니다. &nbsp;</span>
        </p>
        <p>
            <span style="font-size: 8pt; font-family: 나눔고딕, NanumGothic;">ㆍ&nbsp; 종료된 효과는 '적용기간'을 수정하여 진행중 또는 진행 예정 상태로 변경할 수 있습니다. &nbsp;</span>
        </p>
        <p>
            <b>
                <span style="font-size: 10pt; font-family: 나눔고딕, NanumGothic;">선택 삭제는 무엇인가요?</span>
            </b>
        </p>
        <p>
            <span style="font-size: 8pt; font-family: 나눔고딕, NanumGothic;">ㆍ&nbsp; 삭제할 효과를 선택하고 [선택 삭제] 클릭 시 효과를 삭제할 수 있습니다. &nbsp;</span>
        </p>
        <p>
            <span style="font-size: 8pt; font-family: 나눔고딕, NanumGothic;">ㆍ&nbsp; 삭제된 효과 정보는 복구가 불가능하므로 삭제 시 유의하시기 바랍니다. &nbsp;</span>
        </p>
        <p>
            <b>
                <span style="font-size: 10pt; font-family: 나눔고딕, NanumGothic;">화면 효과 적용 시 주의사항</span>
            </b>
        </p>
        <p>
            <span style="font-size: 8pt; font-family: 나눔고딕, NanumGothic;">ㆍ&nbsp; 화면 효과는 최대 10개까지만 등록 가능합니다. &nbsp;</span>
        </p>
        <p>
            <span style="font-size: 8pt; font-family: 나눔고딕, NanumGothic;">ㆍ&nbsp; 페이지 당 1개의 화면 효과 적용을 권장합니다. &nbsp;</span>
        </p>
        <p>
            <span style="font-size: 8pt; font-family: 나눔고딕, NanumGothic;">ㆍ&nbsp; 2개 이상의 화면 효과 적용 시 기능이 정상적으로 작동하지 않거나 쇼핑몰 속도가 느려질 수 있습니다. &nbsp;</span>
        </p>
        <p>
            <span style="font-size: 8pt; font-family: 나눔고딕, NanumGothic;">ㆍ&nbsp; 쇼핑몰의 스킨 및 스크립트 등을 튜닝한 경우, 효과가 적용되지 않을 수 있습니다. &nbsp;</span>
        </p>
    </div>
</div>

<script type="text/javascript">
    var AdminIndex = {
        effectPeriod: {
            total: 0,
            applying: [],
            reserved: [],
            expired: []
        },
        init: function () {
            this.effectPeriod = this.getEffectPeriod();
            var applying = $('.choice_list a').eq(1).text();
            var reserved = $('.choice_list a').eq(2).text();
            var expired = $('.choice_list a').eq(3).text();
            $('.choice_list a').eq(1).text(applying + ' (' + this.effectPeriod.applying.length + ')');
            $('.choice_list a').eq(2).text(reserved + ' (' + this.effectPeriod.reserved.length + ')');
            $('.choice_list a').eq(3).text(expired + ' (' + this.effectPeriod.expired.length + ')');

            this.setViewCode();

            var hash = window.location.hash.substr(1);
            switch (hash) {
                case 'all':
                    this.effectTab(0);
                    break;
                case 'applying':
                    this.effectTab(1);
                    break;
                case 'reserved':
                    this.effectTab(2);
                    break;
                case 'expired':
                    this.effectTab(3);
                    break;
            }
        },
        getViewCode: function (code) {
            return "<!--{" + "# nhngodo/screen_effect_free effect_code=" + code + " #}-->";
        },
        setViewCode: function () {
            var self = this;
            $('.js-popover').each(function(index, e) {
                var code = $(e).attr('data-content');
                $(e).attr('data-content', self.getViewCode(code));
            });
        },
        checkSearchValidation: function() {
            var inputDate = $('input[name*=searchDate]');
            var startDate = new Date(inputDate.eq(0).val());
            var endDate = new Date(inputDate.eq(1).val());
            var days = (endDate - startDate) / (1000 * 60 * 60 * 24);
            if (days >= 365) {
                alert('1년 이상 기간으로 검색하실 수 없습니다.');
                return false;
            }

            return true;
        },
        effectTab: function (index) {
            $('input[type=checkbox]').prop('checked', false);
            $('.choice_list').removeClass('active');
            $('.choice_list').eq(index).addClass('active');

            this.effectList(index);
            if (index === 0 || index === 1) {
                $('#btnStop').show();
            } else if (index === 2 || index === 3) {
                $('#btnStop').hide();
            }
        },
        getEffectPeriod: function() {
            var date = $('.date');
            var date_list = [];
            var tmp_date = [];
            var now = new Date();
            var result = {
                total: date.length,
                applying: [],
                reserved: [],
                expired: []
            };
            var sno;

            for (var i = 0; i < date.length; i++) {
                date_list[i] = $.trim(date.eq(i).text());
                if (date_list[i].indexOf('~') != -1) {
                    tmp_date[i] = date_list[i].split(' ~ ');
                    var start_date = tmp_date[i][0].split(' ')[0];
                    var start_time = tmp_date[i][0].split(' ')[1];
                    var end_date = tmp_date[i][1].split(' ')[0];
                    var end_time = tmp_date[i][1].split(' ')[1];
                    start_date = new Date(start_date.split('-')[0], start_date.split('-')[1]-1, start_date.split('-')[2], start_time.split(':')[0], start_time.split(':')[1]);
                    end_date = new Date(end_date.split('-')[0], end_date.split('-')[1]-1, end_date.split('-')[2], end_time.split(':')[0], end_time.split(':')[1]);
                }

                sno = date.eq(i).parent().find('input[type=checkbox]').val();
                if (date_list[i] == '제한 없음' || (start_date <= now && now <= end_date)) {
                    result.applying.push(sno);
                } else if (date_list[i] != '제한 없음' && start_date > now) {
                    result.reserved.push(sno);
                } else if (date_list[i] != '제한 없음' && end_date < now) {
                    result.expired.push(sno);
                }
            }

            return result;
        },
        effectList: function (effect) {
            var effect_list = $('.effect_list');
            var date = $('.date');
            var display = $('#none_display');
            var count_text = $('.count b span');
            var none_list = $('#none_list');
            var sno;

            display.css('display', 'none');
            for (var i = 0; i < date.length; i++) {
                sno = date.eq(i).parent().find('input[type=checkbox]').val();

                switch (effect) {
                    case 0 :
                        effect_list.eq(i).show();
                        break;
                    case 1 :
                        if (this.isApplying(sno)) {
                            effect_list.eq(i).show();
                        } else {
                            effect_list.eq(i).hide();
                        }
                        break;
                    case 2 :
                        if (this.isReserved(sno)) {
                            effect_list.eq(i).show();
                        } else {
                            effect_list.eq(i).hide();
                        }
                        break;
                    case 3 :
                        if (this.isExpired(sno)) {
                            effect_list.eq(i).show();
                        } else {
                            effect_list.eq(i).hide();
                        }
                        break;
                }
            }
            if (this.effectPeriod.total === 0) {
                display.css('display', 'table-row');
                display.attr('colspan', '100%');
            }
            if (none_list.css('display')) {
                display.hide();
            }
            count_text.eq(0).text(this.effectPeriod.total);
        },
        isApplying: function (sno) {
            return $.inArray(sno, this.effectPeriod.applying) !== -1;
        },
        isReserved: function (sno) {
            return $.inArray(sno, this.effectPeriod.reserved) !== -1;
        },
        isExpired: function (sno) {
            return $.inArray(sno, this.effectPeriod.expired) !== -1;
        },
        checkAll: function () {
            var self = this;

            $('.choice_list').each(function(index) {
                if ($(this).attr('class').match(/active/)) {
                    $('input[name=checkbox]').each(function() {
                        var checkbox = $(this);
                        var sno = checkbox.val();

                        if (index === 0) {
                            checkbox.prop('checked', $('#check_all').prop('checked'));
                        } else if (index === 1 && self.isApplying(sno)) {
                            checkbox.prop('checked', $('#check_all').prop('checked'));
                        } else if (index === 2 && self.isReserved(sno)) {
                            checkbox.prop('checked', $('#check_all').prop('checked'));
                        } else if (index === 3 && self.isExpired(sno)) {
                            checkbox.prop('checked', $('#check_all').prop('checked'));
                        }
                    });
                }
            });
        },
        check: function () {
            $('#check_all').prop('checked', true);

            $('input[name=checkbox]').each(function () {
                if ($(this).prop('checked') === false) {
                    $('#check_all').prop('checked', false);
                }
            });
        },
        stop: function() {
            var formName = 'frmStop';
            var checkbox = $('input[name=checkbox]:checked');
            var count = 0;
            var effectPeriod = this.effectPeriod;

            if (checkbox.length === 0) {
                alert('종료할 효과를 선택해주세요.');
                return false;
            }

            $('#' + formName).find("input[name='sno[]']").remove();

            checkbox.each(function (idx, e) {
                if ($.inArray(e.value, effectPeriod.applying) !== -1) {
                    $('<input>').attr({
                        type: 'hidden',
                        name: 'sno[]',
                        value: e.value
                    }).appendTo('#' + formName);
                    count++;
                }
            });

            if (count === 0) {
                alert('진행 중인 효과가 없습니다.');
                return false;
            }

            this.confirm('진행중인 ' + count + '개의 효과를 종료하시겠습니까?', formName);
        },
        delete: function () {
            var formName = 'frmDelete';
            var checkbox = $('input[name=checkbox]:checked');
            var count = checkbox.length;

            if (count === 0) {
                alert('삭제할 효과를 선택해주세요.');
                return false;
            }

            $('#' + formName).find("input[name='sno[]']").remove();

            checkbox.each(function (idx, e) {
                $('<input>').attr({
                    type: 'hidden',
                    name: 'sno[]',
                    value: e.value
                }).appendTo('#' + formName);
            });

            this.confirm('선택된 ' + count + '개의 효과를 삭제 하시겠습니까?', formName);
        },
        confirm: function (message, formName) {
            BootstrapDialog.confirm({
                type: BootstrapDialog.TYPE_DANGER,
                title: '경고',
                message: message,
                closable: false,
                callback: function (result) {
                    if (result) {
                        $('#' + formName).submit();
                    }
                }
            });
        },
        clipboard: function (code) {
            var $temp = $("<input>");
            $("body").append($temp);
            $temp.val(this.getViewCode(code)).select();
            document.execCommand("copy");
            $temp.remove();
            alert('[치환코드 복사] 정보를 클립보드에 복사했습니다.<br>Ctrl+v를 이용해서 사용하세요.');
        },
        checkTotalCount: function () {
            if (this.effectPeriod.total >= 10) {
                alert('화면 효과는 최대 10개까지만 등록이 가능합니다.');
                return false;
            }

            return true;
        }
    };

    $(document).ready(function () {
        AdminIndex.init();
    });
</script>
