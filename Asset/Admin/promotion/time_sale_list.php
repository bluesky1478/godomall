<div class="page-header js-affix">
    <h3><?=end($naviMenu->location); ?> </h3>
    <div class="btn-group">
        <input type="button" id="checkRegister" value="타임세일 등록" class="btn btn-red-line" />
    </div>
</div>

<form id="frmSearchGift" name="frmSearchGift" method="get" class="js-form-enter-submit">
    <div class="table-title">
        타임세일 검색
    </div>
    <input type="hidden" name="detailSearch" value="<?=$search['detailSearch'];?>" />
    <div class="search-detail-box">
        <table class="table table-cols">
            <colgroup><col class="width-sm" /><col /><col class="width-sm" /><col /></colgroup>
            <tbody>
            <tr>
                <th>검색어</th>
                <td colspan="3"><div class="form-inline">
                        <?=gd_select_box('key','key',array('all'=>'=통합검색=','timeSaleTitle'=>'타임세일 상품명','managerNm'=>'등록자'),null,$search['key'],null);?>
                        <input type="text" name="keyword" value="<?=$search['keyword'];?>" class="form-control" />
                    </div>
                </td>
            </tr>
            <tr>
                <th>기간검색</th>
                <td colspan="3"><div class="form-inline">
                        <select name="searchDateFl" class="form-control">
                            <option value="regDt" <?=gd_isset($selected['searchDateFl']['regDt']); ?>>등록일</option>
                            <option value="startDt" <?=gd_isset($selected['searchDateFl']['startDt']); ?>>시작일</option>
                            <option value="endDt" <?=gd_isset($selected['searchDateFl']['endDt']); ?>>종료일</option>
                        </select>

                        <div class="input-group js-datepicker">
                            <input type="text" class="form-control width-xs" name="searchDate[]" value="<?=$search['searchDate'][0]; ?>" >
                    <span class="input-group-addon">
                        <span class="btn-icon-calendar">
                        </span>
                    </span>
                        </div>

                        ~  <div class="input-group js-datepicker">
                            <input type="text" class="form-control width-xs" name="searchDate[]" value="<?=$search['searchDate'][1]; ?>" >
                    <span class="input-group-addon">
                        <span class="btn-icon-calendar">
                        </span>
                    </span>
                        </div>
                        <?=gd_search_date($search['searchPeriod'], 'searchDate', false)?>
                    </div>
                </td>
            </tr>
            <tr>
                <th>노출범위</th>
                <td class="contents">
                    <label  class="radio-inline" ><input type="radio" name="displayFl" value="" <?=gd_isset($checked['displayFl']['']);?> />전체</label>
                    <label  class="radio-inline" ><input type="radio" name="displayFl" value="all" <?=gd_isset($checked['displayFl']['all']);?> />PC+모바일</label>
                    <label  class="radio-inline" ><input type="radio" name="displayFl" value="p" <?=gd_isset($checked['displayFl']['p']);?> />PC쇼핑몰</label>
                    <label  class="radio-inline" ><input type="radio" name="displayFl" value="m" <?=gd_isset($checked['displayFl']['m']);?> />모바일쇼핑몰</label>
                </td>
                <th>진행상태</th>
                <td class="contents">
                    <label  class="radio-inline" ><input type="radio" name="stateFl" value="all" <?=gd_isset($checked['stateFl']['all']);?> />전체</label>
                    <label  class="radio-inline" ><input type="radio" name="stateFl" value="n" <?=gd_isset($checked['stateFl']['n']);?> />진행중</label>
                    <label  class="radio-inline" ><input type="radio" name="stateFl" value="e" <?=gd_isset($checked['stateFl']['e']);?> />종료</label>
                    <label  class="radio-inline" ><input type="radio" name="stateFl" value="d" <?=gd_isset($checked['stateFl']['d']);?> />대기</label>
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
            <?=gd_select_box('sort', 'sort', $search['sortList'], null, $search['sort'], null); ?>
            <?=gd_select_box('pageNum', 'pageNum', gd_array_change_key_value([10, 20, 30, 40, 50, 60, 70, 80, 90, 100, 200, 300, 500]), '개 보기', Request::get()->get('pageNum'), null); ?>
        </div>
    </div>


</form>


<form id="frmList" action="" method="get" target="ifrmProcess">
    <input type="hidden" name="mode" value="">
    <input type="hidden" name="closeSno" value="">
    <table class="table table-rows">
        <thead>
        <tr>
            <th class="width5p"><input type="checkbox" class="js-checkall" data-target-name="sno"></th>
            <th class="width5p">번호</th>
            <th>타임세일명</th>
            <th class="width10p">시작일</th>
            <th class="width10p">종료일</th>
            <th class="width10p">노출범위</th>
            <th class="width5p">상태</th>
            <th class="width5p center" >링크복사</th>
            <th class="width5p center" >미리보기</th>
            <th class="width10p">등록일</th>
            <th class="width10p">등록자</th>
            <th class="width5p">수정</th>
        </tr>
        </thead>
        <tbody>
        <?php
        if (gd_isset($data)) {
            foreach ($data as $key => $val) {

                if($val['pcDisplayFl'] =='y') $displayFl [] ="PC";
                if($val['mobileDisplayFl'] =='y') $displayFl [] ="모바일";

                $updateFl = "n";
                $closeFl = "n";
                if($val['startDt'] < date('Y-m-d H:i:s') && $val['endDt'] > date('Y-m-d H:i:s')) {
                    $closeFl = "y";
                    $stateText = "진행중";
                    $stateFl = "n";
                } else if($val['endDt'] < date('Y-m-d H:i:s')) {
                    $stateText = "종료";
                    $updateFl = "y";
                    $stateFl = "e";
                } else if($val['startDt'] > date('Y-m-d H:i:s')) {
                    $stateText = "대기중";
                    $updateFl = "y";
                    $stateFl = "d";
                }

                ?>
                <tr>
                    <td class="center"><input type="checkbox" name="sno[<?=$val['sno']; ?>]" value="<?=$val['sno']; ?>" <?php if($updateFl == "n") { echo "disabled='disabled'"; } ?> /><input type="hidden" name="stateFl[<?=$val['sno']; ?>]" value="<?=$stateFl?>" /></td>
                    <td class="center number"><?=number_format($page->idx--);?></td>
                    <td class="hand">
                        <?=$val['timeSaleTitle'];?>
                    </td>
                    <td class="center date"><?= gd_date_format('Y-m-d H:i:s', $val['startDt']);?></td>
                    <td class="center date"><?= gd_date_format('Y-m-d H:i:s', $val['endDt']);?></td>
                    <td class="center"><?=implode("+",$displayFl);?></td>
                    <td class="center"><?=$stateText;?>
                   <?php if($closeFl =='y') { ?> <br/><input type="button" value="강제 종료" class="btn btn-sm btn-gray js-time-sale-close mgt5" data-sno="<?=$val['sno']?>" /><?php } ?>
                    </td>
                    <td class="center">
                        <div class="form-inline">
                        <?php if($val['pcDisplayFl'] =='y') { ?><a class="btn btn-sm btn-white js-clipboard" title="임의정보" data-clipboard-text="<?=URI_HOME;?>event/time_sale.php?sno=<?=$val['sno'];?>">&nbsp;&nbsp;P C&nbsp;&nbsp;</a><?php } ?>
                        <?php if($val['mobileDisplayFl'] =='y') { ?><a class="btn btn-sm btn-white js-clipboard mgt5" title="임의정보" data-clipboard-text="<?=URI_MOBILE;?>event/time_sale.php?sno=<?=$val['sno'];?>">모바일</a><?php } ?></div></td>
                    <td class="center"> <div class="form-inline">
                <?php if($val['pcDisplayFl'] =='y') { ?> <a href="<?=URI_HOME;?>event/time_sale.php?sno=<?=$val['sno'];?>" class="btn btn-white btn-xs" target="_blank">&nbsp;&nbsp;P C&nbsp;&nbsp;</a><?php } ?>
                        <?php if($val['mobileDisplayFl'] =='y') { ?><a href="<?=URI_MOBILE;?>event/time_sale.php?sno=<?=$val['sno'];?>" class="btn btn-white btn-xs mgt5" target="_blank">모바일</a><?php } ?></div>
                    </td>
                    <td class="center date"><?= gd_date_format('Y-m-d H:i:s', $val['regDt']);?></td>
                    <td class="center"><?=$val['managerNm'];?><br/>(<?=$val['managerId'];?>)<?=$val['deleteText']?></td>
                    <td  class="center">  <a href="./time_sale_register.php?sno=<?=$val['sno']; ?>"  class="btn btn-white btn-sm">수정</a></td>
                </tr>
                <?php

                unset($displayFl);
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
            <button type="button" class="btn btn-white checkDelete">선택 삭제</button>
        </div>
    </div>
</form>

<div class="center"><?=$page->getPage();?></div>

<script type="text/javascript">
    <!--
    $(document).ready(function(){

        //강제종료
        $('.js-time-sale-close').click(function () {
            $('#frmList input[name=\'closeSno\']').val($(this).data('sno'));
            dialog_confirm('클릭 시 선택한 타임세일이 즉시 종료되고, 종료된 이벤트는 수정 및 삭제할 수 없습니다.\n 이벤트를 종료하시겠습니까?', function (result) {
                if (result) {
                    $('#frmList input[name=\'mode\']').val('close');
                    $('#frmList').attr('method', 'post');
                    $('#frmList').attr('action', './time_sale_ps.php');
                    $('#frmList').submit();
                }
            });

        });

        // 삭제
        $('button.checkDelete').click(function () {
            var chkCnt = $(':checkbox:checked').not('.js-checkall').length;
            if (chkCnt == 0) {
                alert('선택된 타임세일이 없습니다.');
                return;
            }
            BootstrapDialog.confirm({
                title: "타임세일 삭제",
                message: '선택한 ' + chkCnt + '개 타임세일을  정말로 삭제하시겠습니까?\n삭제시 정보는 복구 되지 않습니다.',
                btnOKLabel: "확인",
                btnCancelLabel: "취소",
                callback: function (result) {
                    if (result) {
                        $('#frmList input[name=\'mode\']').val('delete');
                        $('#frmList').attr('method', 'post');
                        $('#frmList').attr('action', './time_sale_ps.php');
                        $('#frmList').submit();
                    }
                }
            });
        });

        // 등록
        $('#checkRegister').click(function () {
            location.href = './time_sale_register.php';
        });

        $('select[name=\'pageNum\']').change(function () {
            $('#frmSearchGift').submit();
        });

        $('select[name=\'sort\']').change(function () {
            $('#frmSearchGift').submit();
        });
    });

    //-->
</script>



