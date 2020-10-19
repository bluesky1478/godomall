<div class="page-header js-affix">
    <h3><?php echo end($naviMenu->location); ?>
    </h3>
    <input type="button" value="저장" class="btn btn-red js-save"/>
</div>
<form id="frmSearchBase" method="get">
    <input type="hidden" name="mallSno" value="<?= $mallSno ?>">
    <?php if ($gGlobal['isUse'] == 'y') { ?>
        <ul class="multi-skin-nav nav nav-tabs" style="margin-bottom:20px;">
            <?php foreach ($gGlobal['useMallList'] as $key => $mall) { ?>
                <li role="presentation" class="js-popover <?= $mallSno == $mall['sno'] ? 'active' : 'passive'; ?>" data-html="true" data-content="<?= $mall['mallName']; ?>" data-placement="top">
                    <a href="<?= $mall['tabUrl']; ?>">
                        <span class="flag flag-16 flag-<?= $mall['domainFl'] ?>"></span>
                        <span class="mall-name"><?= $mall['mallName']; ?></span>
                    </a>
                </li>
            <?php } ?>
        </ul>
    <?php } ?>


    <div class="table-title gd-help-manual">
        <?php echo end($naviMenu->location); ?>
    </div>
    <div class="search-detail-box">
        <table class="table table-cols">
            <tr>
                <th class="width-md">구분</th>
                <td class="width-2xl">
                    <?php echo gd_select_box('categoryGroupCd', 'categoryGroupCd', $categoryGroupCd, null, $search['categoryGroupCd']); ?>
                </td>
                <th class="width-md">코드 그룹 선택</th>
                <td>
                    <?php
                    echo gd_select_box('groupCd', 'groupCd', $gcode, null, $search['groupCd']); ?>
                </td>
            </tr>
        </table>
    </div>
</form>
<form id="frmListBase" method="post">
    <input type="hidden" name="mallSno" value="<?= $mallSno ?>">
    <input type="hidden" name="mode" value="code_save"/>
    <input type="hidden" name="groupCd" value=""/>

    <div class="table-action mgb0 mgt0">
        <div class="pull-left">
            <div class="btn-group">
                <button type="button" class="btn btn-white btn-icon-bottom js-moverow" data-direction="bottom">
                    맨아래
                </button>
                <button type="button" class="btn btn-white btn-icon-down js-moverow" data-direction="down">
                    아래
                </button>
                <button type="button" class="btn btn-white btn-icon-up js-moverow" data-direction="up">
                    위
                </button>

                <button type="button" class="btn btn-white btn-icon-top js-moverow" data-direction="top">
                    맨위
                </button>
            </div>
        </div>
    </div>

    <table class="table table-rows <?= ($search['groupCd'] == '01004') ? 'mgb10' : '' ?>" data-toggle="" data-use-row-attr-func="false" data-reorderable-rows="true"
           id="codeListTbl">
        <thead>
        <tr>
            <th class="width-2xs">번호<!--<input type="checkbox" id="selectedAll">--></th>
            <th class="width-sm">코드번호</th>
            <th colspan="2">항목명</th>
            <th class="width-sm">사용설정</th>
            <th class="width-sm">추가/삭제</th>
        </tr>
        </thead>
        <tbody>
        <?php
        $num = 1;
        if ($data) foreach ($data as $val) {
            $arrUseFl = [
                'y' => '사용',
                'n' => '미사용',
            ];
            $arrBgColor = [
                'y' => '',
                'n' => 'background:#f0f0f0',
            ];
            $checkUseFl = 'y';

            $disabledText = $disabledUse = '';
            if($val['isUpdatableText'] == 'n') $disabledText = 'readonly';
            if($val['isUpdatableUse'] == 'n') $disabledUse = 'disabled';

            ?>
            <tr class="text-center move-row<?php if ($val['useFl'] == 'n') { ?> active<?php } ?>">
                <td class="center"><?php echo $num ?>
                </td>
                <td class="center">
                    <input type="hidden" name="itemCd[]" value="<?php echo gd_isset($val['itemCd']); ?>"/>
                    <?php echo gd_isset($val['itemCd']); ?>
                </td>
                <?php if ($search['categoryGroupCd'] == '05' && $search['groupCd'] == '05001') {
                    $tmpValue = explode(STR_DIVISION, $val['itemNm']);
                    $val['itemNm'] = $tmpValue[0];
                    $val['itemNmAdd'] = $tmpValue[1];
                    ?>
                    <td class="center" style="border-right:none;">
                        <input type="text" name="itemNm[]" value="<?php echo gd_isset($val['itemNm']); ?>"
                               class="form-control"<?=$disabledText?> />
                    </td>
                    <td class="center" style="width:205px;border-left:none;">
                        <div class="form-inline">
                            <input type="text" class="form-control" readonly name="itemNmAdd[]" value="#<?php echo gd_isset($val['itemNmAdd']); ?>" maxlength="7" class="form-control width-xs center"/>
                            <span class="color-selector-disabled color-selector-sm" <?php if ($val['itemNmAdd']) { ?>style="background:#<?= $val['itemNmAdd'] ?>"<?php } ?>></span>
                        </div>
                    </td>
                <?php } else { ?>
                    <td class="center" colspan="2">
                        <input type="text" name="itemNm[]" value="<?php echo gd_isset($val['itemNm']); ?>"
                               class="form-control"<?=$disabledText?>/>
                    </td>
                <?php } ?>
                <td>
                    <div class="form-inline">
                        <?php
                        if ($disabledUse == 'disabled'){
                            ?><input type="hidden" name="useFl[]" value="y" />
                            <?= gd_select_box('useFlx[]', 'useFlx[]', $arrUseFl, null, $val['useFl'], null, $disabledUse) ?><?php
                        }else{
                        ?>
                        <?= gd_select_box('useFl[]', 'useFl[]', $arrUseFl, null, $val['useFl'], null, $disabledUse) ?><?php
                        }
                         ?></div>
                </td>
                <td>
                    <?php if ($num == 1) {
                        echo '<button type="button" class="btn btn-sm btn-white btn-icon-plus js-code-add" />추가</button>';
                    } else {
                        $isDefaultMileageCode = $val['groupCd'] == '01005' && ($val['itemCd'] <= '01005011' || ($val['itemCd'] >= '01005501' && $val['itemCd'] <= '01005505') || $val['itemCd'] == '010059999');
                        $isDefaultDepositCode = $val['groupCd'] == '01006' && ($val['itemCd'] <= '01006006' || ($val['itemCd'] >= '01006501' && $val['itemCd'] <= '01006505'));
                        $isDefaultSmsCode = $val['groupCd'] == '01007' && $val['isBasic'] == 'y';
                        if ($disabledUse == 'disabled'){
                            echo '-';
                        } else if ($isDefaultMileageCode || $isDefaultDepositCode || $isDefaultSmsCode) {
                            echo '<button type="button" data-sno="' . $val['sno'] . '" class="btn btn-sm btn-white btn-icon-minus" disabled="disabled"/>삭제</button>';
                        } else {
                            echo '<button type="button" data-sno="' . $val['sno'] . '" class="btn btn-sm btn-white btn-icon-minus js-code-delete" />삭제</button>';
                        }
                    }
                    ?>
                </td>
            </tr>
            <?php
            $num++;
        } ?>
        </tbody>
    </table>
    <?php if ($search['groupCd'] == '01004') { ?>
        <div class="notice-danger mgb5">유효하지 메일도메인의 경우 회원가입, 주문 작성 등 쇼핑몰 기능이 정상 작동하지 않을 수 있으므로, 반드시 유효한 도메인을 입력 및 사용하시기 바랍니다.</div>
    <?php } ?>
    <div class="table-action mgb0 mgt0">
        <div class="pull-left">
            <div class="btn-group">
                <button type="button" class="btn btn-white btn-icon-bottom js-moverow" data-direction="bottom">
                    맨아래
                </button>
                <button type="button" class="btn btn-white btn-icon-down js-moverow" data-direction="down">
                    아래
                </button>
                <button type="button" class="btn btn-white btn-icon-up js-moverow" data-direction="up">
                    위
                </button>

                <button type="button" class="btn btn-white btn-icon-top js-moverow" data-direction="top">
                    맨위
                </button>
            </div>
        </div>
    </div>
</form>

<div>&nbsp;</div>

<div id="codeDetail" class="display-none"></div>
<script type="text/javascript">
    <!--
    $(document).ready(function () {
        var num = <?=$num?>;
        var delCount = 0;

        // 회원가입항목(직업, 관심분야)에서 이용시 셀렉트박스(구분, 코드 그룹 선택) 비활성화
        if (location.href.indexOf('popupMode=y') != -1) {
            $('#categoryGroupCd').attr('disabled', true);
            $('#groupCd').attr('disabled', true);
        }

        <?php if($search['categoryGroupCd'] == '05' && $search['groupCd'] == '05001') { ?>
        $(document).on('focus', 'input[name="itemNmAdd[]"]', function () {

            var index = $('input[name="itemNmAdd[]"]').index($(this));
            var color = $(this).val().toUpperCase();
            var chk = 0;

            $('input[name="itemNmAdd[]"]').each(function (key) {
                var sel_color = $(this).val().toUpperCase();
                if (key != index && sel_color != '' && sel_color === color) {
                    chk++;
                }

            });

            if (chk > 0) {
                alert('이미 등록된 색상입니다.');
                $(this).val('');
                $(this).next().css("background-color", "");
                return false;
            } else {
                return true;
            }

        });
        <?php } ?>

        // 폼체크
        $('#frmListBase').validate({
            submitHandler: function (form) {
                var groupCd = $("select[name=groupCd]").val();
                form.groupCd.value = groupCd;
                form.action = './base_ps.php';
                form.target = "ifrmProcess";
                form.submit();
            },
            <?php if($search['categoryGroupCd'] == '05' && $search['groupCd'] == '05001') { ?>
            rules: {
                "itemNm[]": "required",
                "itemNmAdd[]": "required"
            },
            messages: {
                "itemNm[]": "항목명을 입력하세요.",
                "itemNmAdd[]": "항목명(색상)을 입력하세요."
            }
            <?php } else { ?>
            rules: {
                "itemNm[]": "required"
            },
            messages: {
                "itemNm[]": "항목명을 입력하세요.",
            }
            <?php } ?>
        });

        $('.js-save').on('click', function () {
            var check = [];
            var $totalCount = <?=count($data)?>;
            var $count = $('input[name="itemNm[]"]').length;
            var nullCount = 0;
            $('input[name="itemNm[]"]').each(function () {
                if ($(this).val() == '') {
                    nullCount++;
                }
                if ($.inArray($(this).val(), check) == -1) {  //result 에서 값을 찾는다.  //값이 없을경우(-1)
                    check.push($(this).val());
                }
            });

            if (nullCount > 0) {
                alert("항목명을 입력하세요.");
                return false;
            }

            if ($count != check.length) {
                alert('동일한 항목명은 사용할 수 없습니다. 다른 항목명을 사용하세요.');
                return false;
            }


            <?php if($search['categoryGroupCd'] == '05' && $search['groupCd'] == '05001') { ?>
            var check = [];
            var $addCount = $('input[name="itemNmAdd[]"]').length;
            var nullCount = 0;
            $('input[name="itemNmAdd[]"]').each(function () {
                if ($(this).val() == '') {
                    nullCount++;
                }
                if ($.inArray($(this).val().toUpperCase(), check) == -1) {  //result 에서 값을 찾는다.  //값이 없을경우(-1)
                    check.push($(this).val().toUpperCase());
                }
            });

            if (nullCount > 0) {
                alert("항목명(색상)을 입력하세요.");
                return false;
            }

            if ($addCount != check.length) {
                alert('동일한 항목명(색상)은 사용할 수 없습니다. 다른 항목명(색상)을 사용하세요.');
                return false;
            }
            <?php } ?>

            if (delCount > 0) {
                dialog_confirm('코드 삭제 시 해당 코드를 선택한 데이터가 정상적으로 보이지 않거나 사라지므로 코드를 삭제하지 않는 것을 권장합니다. \n 코드를 정말로 삭제하시겠습니까?', function (result) {
                    if (result) {
                        $('#frmListBase').submit();
                    }
                });
            }
            else {
                $('#frmListBase').submit();
            }
        });


        $('#frmSearchBase select[name=categoryGroupCd]').change(function () {
            $('select[name=groupCd]').val(null);
            $('#frmSearchBase').submit();
        });


        $('#frmSearchBase select[name=groupCd]').change(function () {
            $('#frmSearchBase').submit();
        });

        // 리스트 클릭 활성/비활성화
        var iciRow = '';
        var preRow = '';
        $(document).on('click', '#codeListTbl tbody tr', function (e) {
            // 인풋박스 선택시 입력만 활성화
            if ($(e.target).is('span[class=color-selector-btn]') || $(e.target).is('input[type=text]') || $(e.target).is('button') || ($(e.target).is('td') && $(e.target).find('input[type=text]').length)) {
                return true;
            }

            // 이동할 셀 선택처리
            if (preRow && iciRow) {
                $(this).siblings().each(function () {
                    $(this).removeClass('warning');
                });
                preRow = iciRow = '';
            } else {
                if (iciRow) preRow = iciRow;
                iciRow = $(this);
                iciRow.addClass('warning');
                if (preRow && iciRow) {
                    var preRowIndex = (preRow.index('.move-row') + 1);
                    var iciRowIndex = (iciRow.index('.move-row') + 1);

                    if (preRowIndex == iciRowIndex) {
                        $(this).removeClass('warning');
                        preRow = iciRow = '';
                    } else {
                        if (preRowIndex > iciRowIndex) {
                            var startNo = iciRowIndex;
                            var endNo = preRowIndex;
                        } else {
                            var startNo = preRowIndex;
                            var endNo = iciRowIndex;
                        }
                        for (var j = startNo; j <= endNo; j++) {
                            $(this).siblings().each(function () {
                                var thisFormRow = $(this).index('.move-row') + 1;
                                if (thisFormRow == j) {
                                    $(this).addClass('warning');
                                }
                            });
                        }
                    }
                }
            }
        });

        // 리스트 키보드 이동
        $(document).keydown(function (event) {
            if (iciRow) {
                var idx = (iciRow.index('.move-row') + 1);
                switch (event.keyCode) {
                    case 38:
                        iciRow.moveRow(-1);
                        break;
                    case 40:
                        iciRow.moveRow(1);
                        break;
                }
            }
        });

        // 위/아래이동 버튼 이벤트
        $('.js-moverow').click(function (e) {
            if (iciRow) {
                var idx = (iciRow.index('tr') + 1);
                switch ($(this).data('direction')) {
                    case 'up':
                        iciRow.moveRow(-1);
                        break;
                    case 'down':
                        iciRow.moveRow(1);
                        break;
                    case 'top':
                        iciRow.moveRow(-100);
                        break;
                    case 'bottom':
                        iciRow.moveRow(100);
                        break;
                }
            } else {
                alert('순서 변경을 원하시는 코드를 클릭해주세요.');
            }
        });

        // 테이블 ROW 삭제 처리
        $(document).on('click', '.js-code-delete', function () {
            <?php if($search['categoryGroupCd'] == '05' && $search['groupCd'] == '05001') { ?>
            var target = $(this).closest('tr');
            var color = target.find('input[name="itemNmAdd[]"]').val();
            if (color) {
                $.post('./base_ps.php', {'mode': 'search_color', 'color': color.replace("#", "")}, function (data) {
                    if (data > 0) {
                        alert("상품에 등록된 대표색상 코드는 삭제할 수 없습니다.");
                        return false;
                    } else {
                        target.remove();
                        delCount++;
                    }
                });
            } else {
                var target = $(this).closest('tr');
                target.remove();
            }
            <?php } else { ?>
            var target = $(this).closest('tr');
            target.remove();
            delCount++;
            <?php } ?>
        });


        // 테이블 ROW 추가 처리
        $('.js-code-add').click(function (e) {
            var lastItemCd = $('#codeListTbl tbody tr:last').find('[name="itemCd[]"]').val();
            var itemCdCode = lastItemCd.substring(0, 5);
            var itemCdNumber = Number(lastItemCd.substring(5)) + 1;
            var itemCd = itemCdCode + pad(String(itemCdNumber));
            var compiled = _.template($('#moveRowTemplate').html());
            $('#codeListTbl tbody').append(compiled({num: num, itemCd: itemCd, itemCdNumber: itemCdNumber}));

            function pad(s) {
                while (s.length < 3)
                    s = '0' + s;
                return s;
            };

            init_color_picker();
            num++;
        });
    });
    //-->
</script>
<script id="moveRowTemplate" type="text/html">
    <tr class="text-center move-row">
        <td>
            <%=num%>
        </td>
        <td>
            <%=itemCd%>
            <input type="hidden" name="itemCd[]" value="<%=itemCd%>">
        </td>
        <?php if ($search['categoryGroupCd'] == '05' && $search['groupCd'] == '05001') { ?>
            <td class="center" style="border-right:none;">
                <input type="text" name="itemNm[]" class="form-control"/>
            </td>
            <td class="center" style="width:205px;border-left:none;">
                <div class="form-inline">
                    <label for="colorSelector<%=itemCdNumber%>">
                        <input type="text" class="form-control color-selector" name="itemNmAdd[]" id="colorSelector<%=itemCdNumber%>" readonly maxlength="7"/>
                    </label>
                </div>
            </td>
        <?php } else { ?>
            <td class="center" colspan="2">
                <input type="text" name="itemNm[]" class="form-control"/>
            </td>
        <?php } ?>
        <td>
            <div class="form-inline">
                <select class="form-control" name="useFl[]">
                    <option value="y" selected="selected">사용</option>
                    <option value="n">미사용</option>
                </select></div>
        </td>
        <td>
            <button type="button" class="btn btn-sm btn-white btn-icon-minus js-code-delete">삭제</button>
        </td>
    </tr>
</script>
