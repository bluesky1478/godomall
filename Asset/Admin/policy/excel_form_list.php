<script type="text/javascript">
    <!--
    $(document).ready(function () {


        // 삭제
        $('button.checkDelete').click(function () {
            var chkCnt = $('input[name*="sno["]:checkbox:checked').length;
            if (chkCnt == 0) {
                alert('선택된 다운로드 양식이 없습니다.');
                return;
            }

            dialog_confirm('선택한 ' + chkCnt + '개 다운로드 양식을  정말로 삭제하시겠습니까?\n삭제시 정보는 복구 되지 않습니다.', function (result) {
                if (result) {
                    $('#frmList input[name=\'mode\']').val('delete');
                    $('#frmList').attr('method', 'post');
                    $('#frmList').attr('action', './excel_form_ps.php');
                    $('#frmList').submit();
                }
            });
        });

        // 등록
        $('#checkRegister').click(function () {
            location.href = './excel_form_register.php';
        });

        $('select[name=\'pageNum\']').change(function () {
            $('#frmSearchBase').submit();
        });

        $('select[name=\'sort\']').change(function () {
            $('#frmSearchBase').submit();
        });

    });


    /**
     * 카테고리 연결하기 Ajax layer
     */
    function layer_register(typeStr, mode, isDisabled) {

        var addParam = {
            "mode": mode,
        };

        if (typeStr == 'scm') {
            $('input:radio[name=scmFl]:input[value=y]').prop("checked", true);
        }

        if (!_.isUndefined(isDisabled) && isDisabled == true) {
            addParam.disabled = 'disabled';
        }

        layer_add_info(typeStr,addParam);
    }

    function select_location(val) {

        $.post('excel_form_ps.php', {'mode': 'select_location', 'menu': val,'displayFl' : 'y' }, function (data) {
console.log(data);
            var locationList = $.parseJSON(data);

            var addHtml = "<option value=''>선택</option>";
            if(locationList.info) {
                $.each(locationList.info, function (key, val) {
                    addHtml += "<option value='" + key + "'>" + val+ "</option>";
                });
            }
            $('select[name="location"]').html(addHtml);

            <?php if($data['menu'] && $data['location']) { ?>
            $('select[name="location"]').val('<?=$data['location']?>');
            <?php } ?>

        });

    }

    //-->
</script>

<div class="page-header js-affix">
    <h3><?php echo end($naviMenu->location); ?> </h3>
    <div class="btn-group">
        <input type="button" id="checkRegister" value="양식 등록" class="btn btn-red-line" />
    </div>
</div>

<form id="frmSearchBase" name="frmSearchBase" method="get" class="js-form-enter-submit">
    <div class="table-title gd-help-manual">
       다운로드 양식 검색
    </div>
    <input type="hidden" name="detailSearch" value="<?php echo $search['detailSearch']; ?>"/>

    <div class="search-detail-box">
        <table class="table table-cols">
            <colgroup>
                <col class="width-sm"/>
                <col/>
                <col class="width-sm"/>
                <col/>
            </colgroup>
            <tbody>
            <tr>
                <th>검색어</th>
                <td colspan="3"><div class="form-inline">
                        <?php echo gd_select_box('key', 'key', $search['combineSearch'], null, $search['key'], null); ?>
                        <input type="text" name="keyword" value="<?php echo $search['keyword']; ?>" class="form-control"/>
                    </div>
                </td>
            </tr>
            <tr>
                <th >메뉴 분류</th>
                <td>
                    <select name="menu" class="form-control width-xl" onchange="select_location(this.value)">
                        <option value="">선택</option>
                        <?php foreach($menuList as $k => $v) { ?>
                            <option value="<?=$k?>" <?php if($data['menu'] == $k) { echo "selected='selected'"; }  ?>><?=$v?></option>
                        <?php } ?>
                    </select>
                </td>
                <th>상세 항목</th>
                <td>  <select name="location" class="form-control width-xl">
                        <option value="">선택</option>
                    </select></td>
            </tr>
            </tbody>
        </table>
    </div>


    <div class="table-btn">
        <input type="submit" value="검색" class="btn btn-lg btn-black">
    </div>

    <div class="table-header">
        <div class="pull-left">
            검색 <strong><?php echo number_format($page->recode['total']);?></strong>개 /
            전체 <strong><?php echo number_format($page->recode['amount']);?></strong>개
        </div>
        <div class="pull-right form-inline">
            <?php echo gd_select_box('sort', 'sort', $search['sortList'], null, $search['sort']); ?>
            <?php echo gd_select_box('pageNum', 'pageNum', gd_array_change_key_value([10, 20, 30, 40, 50, 60, 70, 80, 90, 100, 200, 300, 500]), '개 보기', Request::get()->get('pageNum'), null); ?>
        </div>
    </div>

</form>

<form id="frmList" action="" method="get" target="ifrmProcess">
    <input type="hidden" name="mode" value="">
    <table class="table table-rows">
        <thead>
        <tr>
            <th class="width5p"><input type="checkbox" id="allCheck" value="y"
                                       onclick="check_toggle(this.id,'giftNo');"/></th>
            <th class="width5p">번호</th>
            <th>다운로드 양식명</th>
            <th class="width10p">메뉴 분류</th>
            <th class="width10p">상세 항목</th>
            <th class="width10p">등록자</th>
            <th class="width10p">등록일</th>
            <th class="width5p">수정</th>
        </tr>
        </thead>
        <tbody>
        <?php
        if (gd_isset($data)) {
            foreach ($data as $key => $val) {
                if ($val['menu'] == 'plusreview') {
                    $val['menu'] = 'board';
                }
                ?>
                <tr>
                    <td class="center"><input type="checkbox" name="sno[<?php echo $val['sno']; ?>]" value="<?php echo $val['sno']; ?>" <?php if($val['defaultFl'] =='y') { echo "disabled = 'true'"; }  ?>/></td>
                    <td class="center number"><?php echo number_format($page->idx--);?></td>
                    <td> <?php echo $val['title'];?></td>
                    <td class="center"><?php echo $menuList[$val['menu']];?></td>
                    <td class="center"><?php echo $locationList[$val['menu']][$val['location']];?></td>
                    <td class="center"><?php echo $val['managerNm'];?><br/>(<?php echo $val['managerId'];?>)
                        <?=$val['deleteText']?></td>
                    <td class="center date"><?php echo gd_date_format('Y-m-d', $val['regDt']);?></td>
                    <td class="center">
                        <a href="./excel_form_register.php?sno=<?php echo $val['sno'];?>" class="btn btn-white btn-xs">수정</a>
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
            <button type="button" class="btn btn-white  checkDelete">선택 삭제</button>
        </div>
    </div>


</form>

<div class="center"><?php echo $page->getPage();?></div>
