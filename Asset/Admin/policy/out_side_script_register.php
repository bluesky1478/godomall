<form id="frmOutSideScriptRegister" name="frmOutSideScriptRegister" action="out_side_script_ps.php" method="post">
    <input type="hidden" name="mode" value="<?= $data['mode']; ?>"/>
    <input type="hidden" name="outSideScriptNo" value="<?= $data['outSideScriptNo']; ?>"/>
    <input type="hidden" name="mallSno" value="<?php echo $mallSno; ?>"/>
    <div class="page-header js-affix">
        <h3><?php echo end($naviMenu->location); ?></h3>
        <div class="btn-group">
            <input type="button" value="목록" class="btn btn-white btn-icon-list" onclick="goList('./out_side_script_list.php');" />
            <button type="submit" class="btn btn-red">저장</button>
        </div>
    </div>

    <?php if ($mallCnt > 1) { ?>
        <ul class="multi-skin-nav nav nav-tabs" style="margin-bottom:20px;">
            <?php
            foreach ($mallList as $key => $mall) {
                if (empty($data['outSideScriptNo']) === false && $mallSno != $mall['sno']) continue;
                ?>
                <li role="presentation" class="js-popover <?php echo $mallSno == $mall['sno'] ? 'active' : 'passive'; ?>" data-html="true" data-content="<?php echo $mall['mallName']; ?>" data-placement="top">
                    <a href="./out_side_script_register.php?mallSno=<?php echo $mall['sno'] . (empty($data['outSideScriptNo']) === false ? '&outSideScriptNo=' . $data['outSideScriptNo'] : ''); ?>">
                        <span class="flag flag-16 flag-<?php echo $mall['domainFl']?>"></span>
                        <span class="mall-name"><?php echo $mall['mallName']; ?></span>
                    </a>
                </li>
            <?php } ?>
        </ul>
    <?php } ?>

    <div class="table-title gd-help-manual">
        <?php echo end($naviMenu->location); ?>
    </div>

    <table class="table table-cols">
        <colgroup>
            <col class="width-md"/>
            <col/>
        </colgroup>
        <tbody>
        <tr>
            <th class="require">서비스명</th>
            <td>
                <input type="text" name="outSideScriptServiceName" value="<?= $data['outSideScriptServiceName']; ?>" class="form-control width-xl js-maxlength" maxlength="30" />
            </td>
        </tr>
        <tr>
            <th class="require">사용설정</th>
            <td>
                <label class="radio-inline">
                    <input type="radio" name="outSideScriptUse" value="y" <?= gd_isset($checked['outSideScriptUse']['y']); ?> />
                    사용함
                </label>
                <label class="radio-inline">
                    <input type="radio" name="outSideScriptUse" value="n" <?= gd_isset($checked['outSideScriptUse']['n']); ?> />
                    사용안함
                </label>
                <label class="radio-inline">
                    <input type="radio" name="outSideScriptUse" value="t" <?= gd_isset($checked['outSideScriptUse']['t']); ?> />
                    테스트모드
                </label>
                <div class="notice-info">테스트모드 설정 시 관리자 접속 상태에서만 쇼핑몰 화면에 외부 스크립트 관련 내용이 노출됩니다.(쇼핑몰 화면에서 노출되는 서비스만 해당됩니다)</div>
            </td>
        </tr>
        <tr>
            <th>스크립트 삽입위치<br/>선택</th>
            <td>
                <label class="checkbox-inline">
                    <input type="checkbox" name="outSideScriptUseHeader" value="y" <?= gd_isset($checked['outSideScriptUseHeader']['y']); ?> />
                    상단 공통영역
                </label>
                <label class="checkbox-inline">
                    <input type="checkbox" name="outSideScriptUseFooter" value="y" <?= gd_isset($checked['outSideScriptUseFooter']['y']); ?> />
                    하단 공통영역
                </label>
                <label class="checkbox-inline">
                    <input type="checkbox" name="outSideScriptUsePage" value="y" <?= gd_isset($checked['outSideScriptUsePage']['y']); ?> />
                    삽입페이지 선택
                </label>
            </td>
        </tr>
        <tr class="js-tr-header">
            <th>상단 공통영역<br/>스크립트 입력</th>
            <td>
                <ul class="nav nav-tabs mgb0" role="tablist">
                    <li role="presentation" class="active"><a href="#header_PC" aria-controls="header_PC" role="tab" data-toggle="tab" class="js-pc">PC 쇼핑몰</a></li>
                    <li role="presentation" class=""><a href="#header_mobile" aria-controls="header_mobile" role="tab" data-toggle="tab" class="js-mobile">모바일 쇼핑몰</a></li>
                </ul>
                <div class="tab-content">
                    <div role="header_PC" class="tab-pane fade in active" id="header_PC">
                        <textarea name="outSideScriptHeaderPC" rows="5" cols="100" class="form-control"><?= gd_isset($data['outSideScriptHeaderPC']); ?></textarea>
                    </div>
                    <div role="header_mobile" class="tab-pane fade" id="header_mobile">
                        <textarea name="outSideScriptHeaderMobile" rows="5" cols="100" class="form-control"><?= gd_isset($data['outSideScriptHeaderMobile']); ?></textarea>
                    </div>
                </div>
            </td>
        </tr>
        <tr class="js-tr-footer">
            <th>하단 공통영역<br/>스크립트 입력</th>
            <td>
                <ul class="nav nav-tabs mgb0" role="tablist">
                    <li role="presentation" class="active"><a href="#footer_PC" aria-controls="footer_PC" role="tab" data-toggle="tab">PC 쇼핑몰</a></li>
                    <li role="presentation" class=""><a href="#footer_mobile" aria-controls="footer_mobile" role="tab" data-toggle="tab">모바일 쇼핑몰</a></li>
                </ul>
                <div class="tab-content">
                    <div role="footer_PC" class="tab-pane fade in active" id="footer_PC">
                        <textarea name="outSideScriptFooterPC" rows="5" cols="100" class="form-control"><?= gd_isset($data['outSideScriptFooterPC']); ?></textarea>
                    </div>
                    <div role="footer_mobile" class="tab-pane fade" id="footer_mobile">
                        <textarea name="outSideScriptFooterMobile" rows="5" cols="100" class="form-control"><?= gd_isset($data['outSideScriptFooterMobile']); ?></textarea>
                    </div>
                </div>
            </td>
        </tr>
        <tr class="js-tr-page">
            <th style="padding-top:25px; vertical-align:top;">선택 페이지 내<br/>스크립트 입력</th>
            <td>
                <button type="button" class="btn btn-sm btn-white btn-icon-plus js-page-script-add">추가</button>
                <div class="notice-info">스크립트를 추가할 페이지를 선택 후 스크립트를 입력하세요. (최대 12개 페이지 추가 가능)</div>
                <div class="notice-info">외부 스크립트 내에서는 치환코드 사용이 불가합니다.</div>
                <div class="notice-info">
                    스크립트 내에 치환코드 사용이 필요한 경우, '<a href="../design/design_skin_list.php" target="_blank">디자인>디자인설정>디자인페이지수정</a>'에 스크립트를 직접 입력하여 사용이 가능합니다.<br />
                    <div>
                        ㆍ상단 공통영역 : 전체 레이아웃 > 상단 레이아웃 (outline/_header.html)<br />
                        ㆍ하단 공통영역 : 전체 레이아웃 > 하단 레이아웃 (outline/_footer.html)<br />
                        ㆍ특정 페이지 : 삽입페이지 선택에서 제공하는 페이지
                    </div>
                </div>
                <div class="js-page-script">
                    <?php
                    if ($data['mode'] == 'modify') {
                        if (count($data['outSideScriptPage']) > 0) {
                            foreach ($data['outSideScriptPage'] as $key => $val) {
                                $selected[$key][$data['outSideScriptPage'][$key]['Url']] = 'selected="selected"';
                            ?>
                    <div class="js-page-each">
                        <div class="form-inline">
                            <select name="outSideScriptPage[<?=$key;?>][Url]" class="form-control eng mgt10 mgb10 js-page-url">
                                <?php
                                foreach ($getOutSideScriptPage as $pKey => $pVal) {
                                    echo '<option value="' . $pVal . '"' . gd_isset($selected[$key][$pVal]) . '>' . $pKey . ' : ' . $pVal . '</option>';
                                }
                                ?>
                            </select>
                            <!--                            <button type="button" class="btn btn-sm btn-white btn-icon-minus js-page-script-del">삭제</button>-->
                        </div>
                        <ul class="nav nav-tabs mgb0" role="tablist">
                            <li role="presentation" class="active"><a href="#page_PC_<?=$key;?>" aria-controls="page_PC_<?=$key;?>" role="tab" data-toggle="tab">PC 쇼핑몰</a></li>
                            <li role="presentation" class=""><a href="#page_mobile_<?=$key;?>" aria-controls="page_mobile_<?=$key;?>" role="tab" data-toggle="tab">모바일 쇼핑몰</a></li>
                        </ul>
                        <div class="tab-content">
                            <div role="page_PC_<?=$key;?>" class="tab-pane fade in active" id="page_PC_<?=$key;?>">
                                <textarea name="outSideScriptPage[<?=$key;?>][PC]" rows="5" cols="100" class="form-control js-page-pc"><?=$val['PC'];?></textarea>
                            </div>
                            <div role="page_mobile_<?=$key;?>" class="tab-pane fade" id="page_mobile_<?=$key;?>">
                                <textarea name="outSideScriptPage[<?=$key;?>][Mobile]" rows="5" cols="100" class="form-control js-page-mobile"><?=$val['Mobile'];?></textarea>
                            </div>
                        </div>
                    </div>
                            <?php
                            }
                        }
                    } else if ($data['mode'] == 'insert') {
                    ?>
                    <div class="js-page-each">
                        <div class="form-inline">
                            <select name="outSideScriptPage[1][Url]" class="form-control eng mgt10 mgb10 js-page-url">
                                <?php
                                foreach ($getOutSideScriptPage as $pKey => $pVal) {
                                    echo '<option value="' . $pVal . '">' . $pKey . ' : ' . $pVal . '</option>';
                                }
                                ?>
                            </select>
<!--                            <button type="button" class="btn btn-sm btn-white btn-icon-minus js-page-script-del">삭제</button>-->
                        </div>
                        <ul class="nav nav-tabs mgb0" role="tablist">
                            <li role="presentation" class="active"><a href="#page_PC_1" aria-controls="page_PC_1" role="tab" data-toggle="tab">PC 쇼핑몰</a></li>
                            <li role="presentation" class=""><a href="#page_mobile_1" aria-controls="page_mobile_1" role="tab" data-toggle="tab">모바일 쇼핑몰</a></li>
                        </ul>
                        <div class="tab-content">
                            <div role="page_PC_1" class="tab-pane fade in active" id="page_PC_1">
                                <textarea name="outSideScriptPage[1][PC]" rows="5" cols="100" class="form-control js-page-pc"></textarea>
                            </div>
                            <div role="page_mobile_1" class="tab-pane fade" id="page_mobile_1">
                                <textarea name="outSideScriptPage[1][Mobile]" rows="5" cols="100" class="form-control js-page-mobile"></textarea>
                            </div>
                        </div>
                    </div>
                    <?php } ?>
                </div>
            </td>
        </tr>
        </tbody>
    </table>
</form>

<script type="text/javascript">
    <!--
    $(document).ready(function () {
        var frmObj = $('#frmOutSideScriptRegister');
        frmObj.validate({
            debug: false,
            onclick: false,
            onfocusout: false,
            onkeyup: false,
            ignore: ':hidden',
            submitHandler: function (form) {
                form.target = 'ifrmProcess';
                form.submit();
            },
            rules: {
                outSideScriptServiceName: {
                    required: true
                },
                outSideScriptUse: {
                    required: true,
                },
                outSideScriptUseHeader: {
                    required: function (input) {
                        var required = false;
                        if (($('input:checkbox[name="outSideScriptUseFooter"]').prop("checked") === false) && ($('input:checkbox[name="outSideScriptUsePage"]').prop("checked") === false)) {
                            required = true;
                        }
                        return required;
                    }
                },
                outSideScriptHeaderPC: {
                    required: function (input) {
                        var required = false;
                        if (($('input:checkbox[name="outSideScriptUseHeader"]').prop("checked") === true) && ($.trim($('[name="outSideScriptHeaderMobile"]').val()) == '')) {
                            required = true;
                        }
                        return required;
                    }
                },
                outSideScriptHeaderMobile: {
                    required: function (input) {
                        var required = false;
                        if (($('input:checkbox[name="outSideScriptUseHeader"]').prop("checked") === true) && ($.trim($('[name="outSideScriptHeaderPC"]').val()) == '')) {
                            required = true;
                        }
                        return required;
                    }
                },
                outSideScriptFooterPC: {
                    required: function (input) {
                        var required = false;
                        if (($('input:checkbox[name="outSideScriptUseFooter"]').prop("checked") === true) && ($.trim($('[name="outSideScriptFooterMobile"]').val()) == '')) {
                            required = true;
                        }
                        return required;
                    }
                },
                outSideScriptFooterMobile: {
                    required: function (input) {
                        var required = false;
                        if (($('input:checkbox[name="outSideScriptUseFooter"]').prop("checked") === true) && ($.trim($('[name="outSideScriptFooterPC"]').val()) == '')) {
                            required = true;
                        }
                        return required;
                    }
                },
            },
            messages: {
                outSideScriptServiceName: {
                    required: '서비스명을 입력해 주세요.'
                },
                outSideScriptUse: {
                    required: '사용설정을 선택해 주세요.',
                },
                outSideScriptUseHeader: {
                    required: '스크립트 삽입위치를 선택해 주세요.',
                },
                outSideScriptHeaderPC: {
                    required: '상단 공통영역 스크립트를 입력해 주세요.',
                },
                outSideScriptHeaderMobile: {
                    required: '상단 공통영역 스크립트를 입력해 주세요.',
                },
                outSideScriptFooterPC: {
                    required: '하단 공통영역 스크립트를 입력해 주세요.',
                },
                outSideScriptFooterMobile: {
                    required: '하단 공통영역 스크립트를 입력해 주세요.',
                },
            },
        });

        var countPageScript = $(".js-page-script > .js-page-each").length;
        $('.js-page-script-add').click(function () {
            if ($(".js-page-script > .js-page-each").length > 11) {
                alert('페이지 스크립트는 최대 12개입니다.');
                return false;
            }
            countPageScript = countPageScript + 1;
            var addHtml = "";
            var complied = _.template($('#pageScriptTemplate').html());
            addHtml += complied({
                key: countPageScript,
            });
            $(".js-page-script").append(addHtml);
            addValidateRule();
        });

        $('.js-page-script').on('click', '.js-page-script-del', function (e) {
            $(this).closest('.js-page-each').remove();
        });

        $('input:checkbox[name="outSideScriptUseHeader"], input:checkbox[name="outSideScriptUseFooter"], input:checkbox[name="outSideScriptUsePage"]').click(function () {
            changeScriptLocationDisplay();
        });

        changeScriptLocationDisplay();
    });

    function addValidateRule() {
        $('.js-page-pc').each(function (index) {
            if ($.trim($('.js-page-mobile').eq(index).val()) == '') {
                $(this).rules('add', {
                    required: true,
                    messages: {
                        required: '선택 페이지 내 스크립트를 입력해 주세요.',
                    }
                });
            }
        });
        $('.js-page-mobile').each(function (index) {
            if ($.trim($('.js-page-pc').eq(index).val()) == '') {
                $(this).rules('add', {
                    required: true,
                    messages: {
                        required: '선택 페이지 내 스크립트를 입력해 주세요.',
                    }
                });
            }
        });
    }

    function changeScriptLocationDisplay() {
        if ($('input:checkbox[name="outSideScriptUseHeader"]').prop("checked") == true) {
            $('.js-tr-header').show();
        } else {
            $('.js-tr-header').hide();
        }
        if ($('input:checkbox[name="outSideScriptUseFooter"]').prop("checked") == true) {
            $('.js-tr-footer').show();
        } else {
            $('.js-tr-footer').hide();
        }
        if ($('input:checkbox[name="outSideScriptUsePage"]').prop("checked") == true) {
            $('.js-tr-page').show();
            addValidateRule();
        } else {
            $('.js-tr-page').hide();
        }
    }
    //-->
</script>
<script type="text/html" id="pageScriptTemplate">
    <div class="js-page-each">
        <div class="form-inline">
            <select name="outSideScriptPage[<%=key%>][Url]" class="form-control eng mgt10 mgb10 js-page-url">
                <?php
                foreach ($getOutSideScriptPage as $pKey => $pVal) {
                    echo '<option value="' . $pVal . '">' . $pKey . ' : ' . $pVal . '</option>';
                }
                ?>
            </select>
            <button type="button" class="btn btn-sm btn-white btn-icon-minus js-page-script-del">삭제</button>
        </div>
        <ul class="nav nav-tabs mgb0" role="tablist">
            <li role="presentation" class="active"><a href="#page_PC_<%=key%>" aria-controls="page_PC_<%=key%>" role="tab" data-toggle="tab">PC 쇼핑몰</a></li>
            <li role="presentation" class=""><a href="#page_mobile_<%=key%>" aria-controls="page_mobile_<%=key%>" role="tab" data-toggle="tab">모바일 쇼핑몰</a></li>
        </ul>
        <div class="tab-content">
            <div role="page_PC_<%=key%>" class="tab-pane fade in active" id="page_PC_<%=key%>">
                <textarea name="outSideScriptPage[<%=key%>][PC]" rows="5" cols="100" class="form-control js-page-pc"></textarea>
            </div>
            <div role="page_mobile_<%=key%>" class="tab-pane fade" id="page_mobile_<%=key%>">
                <textarea name="outSideScriptPage[<%=key%>][Mobile]" rows="5" cols="100" class="form-control js-page-mobile"></textarea>
            </div>
        </div>
    </div>
</script>
