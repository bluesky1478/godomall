<form id="frmFrequencyAddress" name="frmFrequencyAddress" method="get">
	<input type="hidden" name="sort[name]" value="<?=$sort['fieldName']?>">
	<input type="hidden" name="sort[mode]" value="<?=$sort['sortMode']?>" />
    <table class="table table-cols no-title-line">
        <colgroup>
            <col class="width-md"/>
            <col/>
        </colgroup>
        <tbody>
        <tr>
            <th>그룹</th>
            <td>
                <label class="mgb0">
                    <?= gd_select_box('group', 'group', $search['combineGroup'], null, $search['group'], null);?>
                </label>
            </td>
        </tr>
        <tr>
            <th>검색어</th>
            <td>
                <div class="form-inline">
                    <?= gd_select_box('key', 'key', $search['combineSearch'], null, $search['key'], null);?>
                    <input type="text" name="keyword" value="<?= $search['keyword']; ?>" class="form-control width-xl" placeholder="키워드를 입력해 주세요." />
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
            검색 <strong><?= number_format($page->recode['total']); ?></strong>개 /
            전체 <strong><?= number_format($page->recode['amount']); ?></strong>개
		</div>
	</div>
</form>

<form id="frmList" name="frmList" method="post">
	<input type="hidden" name="mode" value="delete_frequency" />
    <div class="table-responsive">
        <table class="table table-rows">
            <thead>
            <tr>
                <th class="width3p">선택</th>
                <th class="width3p">번호</th>
                <th class="width7p">그룹</th>
                <th class="width5p">이름</th>
                <th class="">주소</th>
                <th class="width10p">이메일</th>
                <th class="width10p">전화번호/휴대폰번호</th>
                <th class="width10p">사업자번호</th>
                <th class="width10p">회사명/(대표자명)</th>
                <th class="width10p">메모</th>
            </tr>
            </thead>
            <tbody>
    <?php
        if (empty($data) === false) {
            // 자주쓰는 주소 리스트
            foreach ($data as $key => $val) {
    ?>
                <tr class="text-center nowrap" data-name="<?=$val['name'];?>" data-zonecode="<?=$val['zonecode']?>" data-zipcode="<?=$val['zipcode']?>" data-address="<?=$val['address']?>" data-address-sub="<?=$val['addressSub']?>" data-email="<?=$val['email']?>" data-phone="<?=$val['phone']?>" data-cell-phone="<?=$val['cellPhone']?>">
                    <td><input name="sno[]" type="radio" value="<?= $val['sno']; ?>" /></td>
                    <td class="font-num"><?=number_format($page->idx--); ?></td>
                    <td class="font-kor"><?=$val['groupNm'];?></td>
                    <td class="font-kor bold"><?=$val['name'];?></td>
                    <td class="notice-ref" style="text-align: left;">
                        <!-- 기본 주소 -->
                        <?php echo $val['zonecode'];?> <?php if (strlen($val['zipcode']) === 7) { echo '(' . $val['zipcode'] . ')'; } ?> <?php echo $val['address'] . ' ' . $val['addressSub'];?><br />
                        <!-- 사업장 주소 -->
                        <?php echo $val['bZonecode'];?> <?php if (strlen($val['bZipcode']) === 7) { echo '(' . $val['bZipcode'] . ')'; } ?> <?php echo $val['bAddress'] . ' ' . $val['bAddressSub'];?>
                    </td>

                    <td class="font-kor"><?=$val['email'];?></td>
                    <td class="font-num"><?=$val['phone'];?><br /><?php echo $val['cellPhone'];?></td>
                    <td class="font-num"><?=$val['businessNo'];?></td>
                    <td class="font-num"><?=$val['bCompanyNm'];?><?php if($val['bCeoNm']){?><br /><?php echo '(' . $val['bCeoNm'] .')'; }?></td>
                    <td class="font-kor"><?=$val['memo'];?></td>
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
    </div>

    <div class="center"><?= $page->getPage('layer_list_search(\'PAGELINK\')');?></div>

    <div class="table-btn">
        <button type="button" class="btn btn-lg btn-white js-layer-close">취소</button>
        <button type="submit" class="btn btn-lg btn-black">확인</button>
    </div>
</form>

<script type="text/javascript">
    <!--
    $(function() {
        // 자주쓰는 주소 등록
        $('.js-btn-register').click(function(e){
            $.get('<?= URI_ADMIN;?>order/layer_frequency_address_register.php', function(data){
                BootstrapDialog.show({
                    title: '자주쓰는 주소 등록',
                    message: $(data)
                });
            });
        });

        $('.js-btn-modify').click(function(e){
            $.get('<?= URI_ADMIN;?>order/layer_frequency_address_register.php?sno=' + $(this).data('sno'), function(data){
                BootstrapDialog.show({
                    title: '자주쓰는 주소 수정',
                    message: $(data)
                });
            });
        });

        // 검색 체크
        $('#frmFrequencyAddress').validate({
            dialog: false,
            submitHandler: function(form) {
                layer_list_search();
            },
            rules: {
                'sno[]': 'required'
            },
            messages: {
                'sno[]': '선택된 주소가 없습니다.'
            }
        });

        // 리스트 선택 체크
        $('#frmList').validate({
            dialog: false,
            submitHandler: function (form) {
                var $self = $('input[name="sno[]"]:checked').closest('tr');
                var data = {
                    memNo: 0,
                    memNm: $self.data('name'),
                    email: $self.data('email'),
                    phone: $self.data('phone'),
                    cellPhone: $self.data('cell-phone'),
                    zonecode: $self.data('zonecode'),
                    zipcode: $self.data('zipcode'),
                    address: $self.data('address'),
                    addressSub: $self.data('address-sub')
                }
                insert_address_info(data);
            },
            rules: {
                'sno[]': 'required'
            },
            messages: {
                'sno[]': '선택된 주소가 없습니다.'
            }
        });
    });

    /**
     * 페이지 출력 이벤트 처리
     *
     * @param pagelink
     */
    function layer_list_search(pagelink)
    {
        var params= {
            group: $('select[name=group]').val(),
            key: $('select[name=key]').val(),
            keyword: $('input[name=keyword]').val()
        };
        pagelink = (_.isUndefined(pagelink) ? '' : '&' + pagelink);

        $.get('<?= URI_ADMIN;?>order/layer_frequency_address.php?' + $.param(params) + pagelink, function(data){
            $('.bootstrap-dialog-message').html(data);
        });
    }
    //-->
</script>
