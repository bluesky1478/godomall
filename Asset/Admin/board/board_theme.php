<script>
    $(document).ready(function () {
        $('input[name=deviceType]').bind('click',function(event,isLoad){
            /*$('select[name=liveSkin] option[data-role="pc"]').hide();
            $('select[name=liveSkin] option[data-role="mobile"]').hide();
            if($(this).val() == 'pc'){
                $('select[name=liveSkin] option[data-role="pc"]').show();
            }
            else if($(this).val() == 'mobile'){
                $('select[name=liveSkin] option[data-role="mobile"]').show();
            }
            else {
                $('select[name=liveSkin] option[data-role="pc"]').show();
                $('select[name=liveSkin] option[data-role="mobile"]').show();
            }

            if(isLoad !== true) {
                $('select[name=liveSkin]').find("option:eq(0)").prop("selected", true);
            }*/
            if(isLoad !== true) {
                changeApplySkinList();
                $('select[name=liveSkin]').find("option:eq(0)").prop("selected", true);
            }
        })

        $('input[name=domainFl]').bind('click',function(event,isLoad){
            if(isLoad !== true) {
                changeApplySkinList();
            }
        });

        var changeApplySkinList = function(){
            var domainFl = $('input[name=domainFl]:checked').val();
            var deviceType = $('input[name=deviceType]:checked').val();
            if (domainFl != '') {
                $.ajax({
                    method: 'post',
                    url: 'board_theme_ps.php',
                    data: {'mode': 'getApplySkinList', 'domainFl' : domainFl , 'deviceType' : deviceType },
                    dataType: 'json'
                }).success(function (data) {
                    console.log(data);
                    $('select[name=liveSkin').empty().append($('<option>', {value: '', text: '=디자인 스킨 검색='}));
                    for (var i = 0; i < data.list.length; i++) {
                        $('select[name=liveSkin').append($('<option>', {value: data.list[i].skinValue, text: data.list[i].skinTitle}));
                    }
                }).error(function (e) {
                    console.log(e);
                    alert(e);
                });
            }
        }

        $('input[name=deviceType]:checked').trigger('click',[true]);

        $('#frmList').validate({
            dialog: false,
            submitHandler: function (form) {
                form.target = 'ifrmProcess';
                dialog_confirm('선택한 스킨을 삭제하시겠습니까?\n\r영구 삭제되어 복원 불가능합니다.', function (result) {
                    if (result) {
                        form.submit();
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
                    required: '선택한 스킨이 없습니다.'
                },

            },
        });

        $('.js-row-delete').bind('click', function () {
            $(this).closest('tr').find('input[name="sno[]"]').prop('checked', true);
            $('#frmList').submit();
        })

    })
</script>
<div class="page-header js-affix">
    <h3><?= end($naviMenu->location); ?>
        <small></small>
    </h3>
    <input type="button" onclick="location.href='./board_theme_register.php'" class="btn btn-red" value="등록"/>
</div>
<div class="table-title gd-help-manual">게시판 스킨 검색</div>
<form id="frmSearch" method="get" class="js-form-enter-submit">
    <div class="search-detail-box">
        <table class="table table-cols">
            <colgroup>
                <col class="width-sm"/>
                <col/>
            </colgroup>
            <?php if($gGlobal['isUse']){?>
            <tr>
                <th>상점</th>
                <td>
                    <?php foreach($gGlobal['useMallList'] as $val) {
                        ?>
                        <label class="radio-inline"><input type="radio" name="domainFl" value="<?=$val['domainFl']?>" <?=$checked['domainFl'][$val['domainFl']]?>>
                            <span class="flag flag-16 flag-<?= $val['domainFl'] ?>"></span>
                            <?=$val['mallName']?></label>
                    <?php }?>
                </td>
            </tr>
            <?php }?>
            <tr>
                <th>검색어</th>
                <td>
                    <div class="form-inline">
                        <?=gd_select_box('searchField', 'searchField', array( 'themeId' => '스킨코드', 'themeNm' => '이름'), '', gd_isset($req['searchField'])); ?>
                        <input type="text" name="keyword" value="<?=gd_isset($req['keyword']); ?>" class="form-control"/>
                    </div>
                </td>
            </tr>
            <tr>
                <th>구분</th>
                <td>
                    <label class="radio-inline"><input type="radio" name="deviceType" value="all" <?=$checked['deviceType']['all']?>>전체</label>
                    <label class="radio-inline"><input type="radio" name="deviceType" value="pc" <?=$checked['deviceType']['pc']?>>PC쇼핑몰</label>
                    <label class="radio-inline"><input type="radio" name="deviceType" value="mobile" <?=$checked['deviceType']['mobile']?>>모바일쇼핑몰</label>
                </td>
            </tr>
            <?php if($gGlobal['isUse']){?>
            <tr>
                <th>적용 디자인 스킨</th>
                <td>
                    <select name="liveSkin">
                        <option value="">=디자인 스킨 검색=</option>
                        <?php foreach($applySkinList as $key=>$val) {?>
                            <option data-role="<?=$val['device']?>"  value="<?=$val['device'].STR_DIVISION.$val['skinCode']?>" <?=$selected['liveSkin'][$val['skinValue']]?>><?=$val['skinTitle']?></option>
                        <?php }?>
                    </select>
                </td>
            </tr>
            <?php }?>
            <tr>
                <th>유형</th>
                <td>
                    <label class="checkbox-inline"><input type="checkbox" name="boardKind[all]" value="y" <?=$checked['boardKind']['all']?>>전체</label>
                    <label class="checkbox-inline"><input type="checkbox" name="boardKind[default]" value="y" <?=$checked['boardKind']['default']?>>일반형</label>
                    <label class="checkbox-inline"><input type="checkbox" name="boardKind[gallery]" value="y" <?=$checked['boardKind']['gallery']?>>갤러리형</label>
                    <label class="checkbox-inline"><input type="checkbox" name="boardKind[event]" value="y" <?=$checked['boardKind']['event']?>>이벤트형</label>
                    <label class="checkbox-inline"><input type="checkbox" name="boardKind[qa]" value="y" <?=$checked['boardKind']['qa']?>>1:1문의형</label>
                </td>
            </tr>
        </table>
        <div class="table-btn">
            <input type="submit" value="검색" class="btn btn-lg btn-black">
        </div>
    </div>
</form>
<form action="board_theme_ps.php" id="frmList" name="frmList" method="post" action="board_theme_ps.php">
    <input type="hidden" name="mode" value="theme_delete"/>
    <div class="table-header">
        <div class="pull-left">
             검색
            <strong><?= number_format($page->recode['total']); ?></strong>개 /
            전체 <strong><?= number_format($page->recode['amount']); ?></strong>건
        </div>
    </div>

    <table class="table table-rows">
        <thead>
        <tr>
            <th class="width5p"><input type="checkbox" class="js-checkall" data-target-name="sno"></th>
            <th class="width5p">번호</th>
            <th class="width10p">구분</th>
            <th class="width10p">적용 디자인스킨</th>
            <th>스킨코드</th>
            <th>스킨명</th>
            <th>적용개수</th>
            <th>정렬</th>
            <th>넓이</th>
            <th>등록일</th>
            <th class="width5p">수정</th>
            <th class="width5p">삭제</th>
        </tr>
        </thead>
        <tbody>
        <?php
        if (is_array(gd_isset($data))) {
            foreach ($data as $key => $val) {
                ?>
                <tr align="center">
                    <td>
                        <input name="sno[]" type="checkbox" value="<?= $val['sno'] ?>" <?php if ($val['bdBasicFl'] == 'y') echo 'disabled' ?>>
                    </td>
                    <td><?= number_format($page->idx--); ?></td>
                    <td>
                        <?=$val['deviceTypeText']?>
                    </td>
                    <td>
                        <?=$val['liveSkin']?>
                    </td>
                    <td>
                        <a href="./board_theme_register.php?sno=<?= $val['sno']; ?>"><?= $val['themeId']; ?></a>
                    </td>
                    <td>
                        <a href="./board_theme_register.php?sno=<?= $val['sno']; ?>"><?= $val['themeNm']; ?></a>
                    </td>
                    <td><span class="font-num"><?= number_format($val['applyThemeCount']); ?></span>
                        개
                    </td>
                    <td><?= $val['bdAlignText']; ?></td>
                    <td><?= $val['bdWidthText']?></td>
                    <td><?= gd_date_format('Y-m-d', $val['regDt']); ?></td>
                    <td><a class="btn btn-white btn-sm"
                           href="./board_theme_register.php?sno=<?= $val['sno']; ?>">수정</a></td>
                    <td><?php if ($val['bdBasicFl'] == 'n') { ?>
                            <input type="button" value="삭제" class="btn btn-white btn-sm js-row-delete"/><?php } ?>
                    </td>
                </tr>
                <?php
            }
        } else {
            ?>
            <tr>
                <td class="no-data" colspan="8">검색된 정보가 없습니다.</td>
            </tr>
            <?php
        }
        ?>
        </tbody>
    </table>
    <div class="table-action">
        <div class="pull-left form-inline">
            <span class="action-title">선택한 스킨</span>
            <button type="submit" class="btn btn-white"/>
            삭제</button>
        </div>
    </div>
    <div class="center"><?= $page->getPage(); ?></div>
</form>
