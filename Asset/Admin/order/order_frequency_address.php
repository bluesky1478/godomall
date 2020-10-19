<div class="page-header js-affix">
	<h3><?php echo end($naviMenu->location); ?></h3>
	<div class="btn-group">
		<button type="button" class="btn btn-red-line js-btn-register" role="button">자주쓰는 주소 등록</button>
	</div>
</div>

<form id="frmFrequencyAddress" name="frmFrequencyAddress" method="get" class="js-form-enter-submit">
	<input type="hidden" name="sort[name]" value="<?=$sort['fieldName']?>">
	<input type="hidden" name="sort[mode]" value="<?=$sort['sortMode']?>" />

    <div class="table-title gd-help-manual">주소 검색</div>

    <table class="table table-cols">
        <colgroup>
            <col class="width-md"/>
            <col class="width-2xl"/>
            <col class="width-md"/>
            <col/>
        </colgroup>
        <tbody>
        <tr>
            <th>검색어</th>
            <td>
                <div class="form-inline">
                    <?php echo gd_select_box('key', 'key', $search['combineSearch'], null, $search['key'], null);?>
                    <input type="text" name="keyword" value="<?php echo $search['keyword']; ?>" class="form-control width-md" placeholder="키워드를 입력해 주세요." />
                </div>
            </td>
            <th>그룹</th>
            <td>
                <label class="mgb0">
                <?php echo gd_select_box('group', 'group', $search['combineGroup'], null, $search['group'], null);?>
                </label>
            </td>
        </tr>
        <tr>
            <th>기간검색</th>
            <td colspan="3">
                <div class="form-inline">
                    <?php echo gd_select_box('treatDateFl', 'treatDateFl', $search['combineTreatDate'], '', $search['treatDateFl']); ?>
                    <div class="input-group js-datepicker">
                        <input type="text" name="treatDate[start]" value="<?php echo $search['treatDate'][0];?>" class="form-control width-xs" placeholder="수기입력 가능" />
                        <span class="input-group-addon"><span class="btn-icon-calendar"></span></span>
                    </div>
                    ~
                    <div class="input-group js-datepicker">
                        <input type="text" name="treatDate[end]" value="<?php echo $search['treatDate'][1];?>" class="form-control width-xs" placeholder="수기입력 가능" />
                        <span class="input-group-addon"><span class="btn-icon-calendar"></span></span>
                    </div>
                    <?= gd_search_date(gd_isset($search['searchPeriod'], 6), 'treatDate', false) ?>
                </div>
            </td>
        </tr>
        </tbody>
    </table>

	<div class="table-btn">
		<input type="submit" value="검색" class="btn btn-lg btn-black"/>
	</div>

	<div class="table-header">
		<div class="pull-left">
            검색 <strong><?php echo number_format($page->recode['total']); ?></strong>개 /
            전체 <strong><?php echo number_format($page->recode['amount']); ?></strong>개
		</div>
        <div class="pull-right">
            <div class="form-inline">
                <?= gd_select_box('sort', 'sort', $search['sortList'], null, $search['sort']); ?>
                <?= gd_select_box('pageNum', 'pageNum', gd_array_change_key_value([10,20,30,40,50,60,70,80,90,100,200,300,500,]), '개 보기', $page->page['list']); ?>
            </div>
        </div>
	</div>
</form>

<form id="frmList" name="frmList" action="./order_ps.php" method="post">
	<input type="hidden" name="mode" value="deleteFrequency" />
	<table class="table table-rows table-fixed">
		<thead>
		<tr>
			<th class="width3p"><input class="js-checkall" type="checkbox" data-target-name="sno[]"></th>
            <th class="width3p">번호</th>
            <th class="width7p">그룹</th>
            <th class="width5p">이름</th>
			<th class="">주소</th>
			<th class="width10p">이메일</th>
            <th class="width10p">전화번호/휴대폰번호</th>
            <th class="width10p">사업자번호</th>
            <th class="width10p">회사명/(대표자명)</th>
            <th class="width10p">메모</th>
			<th class="width7p">등록일</th>
			<th class="width5p">수정</th>
		</tr>
		</thead>
		<tbody>
<?php
    if (empty($data) === false) {
        // 자주쓰는 주소 리스트
        foreach ($data as $key => $val) {
?>
            <tr class="text-center">
                <td><input name="sno[]" type="checkbox" value="<?php echo $val['sno']; ?>" <?php if ($selectedApprovalChk === false) { echo 'disabled="disabled"';  }?>/></td>
                <td class="font-num"><?php echo number_format($page->idx--); ?></td>
                <td class="font-kor"><?php echo $val['groupNm'];?></td>
                <td class="font-kor bold"><?php echo $val['name'];?></td>
                <td class="notice-ref" style="text-align: left;">
                    <!-- 기본 주소 -->
                    <?php echo $val['zonecode'];?> <?php if (strlen($val['zipcode']) === 7) { echo '(' . $val['zipcode'] . ')'; } ?> <?php echo $val['address'] . ' ' . $val['addressSub'];?><br />
                    <!-- 사업장 주소 -->
                    <?php echo $val['bZonecode'];?> <?php if (strlen($val['bZipcode']) === 7) { echo '(' . $val['bZipcode'] . ')'; } ?> <?php echo $val['bAddress'] . ' ' . $val['bAddressSub'];?>
                </td>
                <td class="font-kor"><?php echo $val['email'];?></td>
                <td class="font-num"><?php echo $val['phone'];?><br /><?php echo $val['cellPhone'];?></td>
                <td class="font-num"><?php echo $val['businessNo'];?></td>
                <td class="font-num"><?php echo $val['bCompanyNm'];?><?php if($val['bCeoNm']){?><br /><?php echo '('.$val['bCeoNm'].')'; }?></td>
                <td class="font-kor"><?php echo $val['memo'];?></td>
                <td class="font-date"><?php echo gd_date_format('Y-m-d', $val['regDt']);?></td>
                <td class=""><button type="button" data-sno="<?php echo $val['sno'];?>" class="btn btn-gray btn-sm js-btn-modify">수정</button></td>
            </tr>
<?php
        }
    } else {
?>
        <tr>
            <td class="no-data" colspan="10">검색된 정보가 없습니다.</td>
        </tr>
<?php
    }
?>
        </tbody>
    </table>
    <div class="table-action">
        <div class="pull-left">
            <input type="button" value="선택 삭제" class="btn btn-white js-btn-delete" />
        </div>
        <div class="pull-right">
        </div>
    </div>
</form>

<div class="center"><?php echo $page->getPage(); ?></div>

<script type="text/javascript">
    <!--
    $(function() {
        // 자주쓰는 주소 등록
        $('.js-btn-register').click(function(e){
            $.get('./layer_frequency_address_register.php', function(data){
                BootstrapDialog.show({
                    title: '자주쓰는 주소 등록',
                    message: $(data)
                });
            });
        });

        // 자주쓰는 주소 수정
        $('.js-btn-modify').click(function(e){
            $.get('./layer_frequency_address_register.php?sno=' + $(this).data('sno'), function(data){
                BootstrapDialog.show({
                    title: '자주쓰는 주소 수정',
                    message: $(data)
                });
            });
        });

        // 폼 체크
        $('#frmList').validate({
            submitHandler: function (form) {
                form.target = 'ifrmProcess';
                form.submit();
            },
            rules: {
                'sno[]': 'required'
            },
            messages: {
                'sno[]': '선택된 주소가 없습니다.'
            }
        });

        // 자주쓰는 주소 삭제
        $('.js-btn-delete').click(function (e) {
            $.validator.setDefaults({dialog: false});
            if ($('input[name="sno[]"]:checked').length > 0) {
                BootstrapDialog.confirm({
                    type: BootstrapDialog.TYPE_DANGER,
                    title: '주문삭제',
                    message: '선택된 ' + $('input[name="sno[]"]:checked').length + '개의 자주쓰는 주소를 정말로 삭제 하시겠습니까?<br />삭제 시 정보는 복구 되지 않습니다.',
                    closable: false,
                    callback: function(result) {
                        if (result) {
                            $('#frmList').submit();
                        }
                    }
                });
            } else {
                $('#frmList').submit();
            }
        });
    });
    //-->
</script>
