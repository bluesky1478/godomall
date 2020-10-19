<div>
    <div class="mgt10"></div>
    <div>
        <table class="table table-cols no-title-line">
            <colgroup>
                <col class="width-md"/>
            </colgroup>
            <tr>
                <th>
                    ■ <?= $title; ?>
                    <?php if ($couponName != '') { ?>
                    <br/><p class="mgl15">- <?= $couponName; ?></p>
                    <?php } ?>
                </th>
            </tr>
        </table>
    </div>
</div>

<form id="frmCouponSearch">
<div>
    <div class="mgt10"></div>
    <div>
        <table class="table table-cols no-title-line">
            <colgroup>
                <col class="width-md"/>
                <col/>
                <col class="width-md"/>
                <col/>
                <col class="width-xs"/>
            </colgroup>
            <tr>
                <th>검색어</th>
                <td colspan="3">
                    <div class="form-inline">
                        <?= gd_select_box('searchMode', 'searchMode', $searchMode, null, $getValue['searchMode']); ?>
                        <input type="text" value="<?=$getValue['searchValue']?>" name="searchValue"  />
                    </div>
                </td>
            </tr>
            <tr>
                <th>쿠폰발급여부</th>
                <td>
                    <?= gd_radio_box('issueCouponFl', $issueCouponFl, $getValue['issueCouponFl']); ?>
                </td>
                <th>SMS발송여부</th>
                <td>
                    <?= gd_radio_box('sendSmsFl', $sendSmsFl, $getValue['sendSmsFl']); ?>
                </td>
            </tr>
            <tr>
                <th>사용여부</th>
                <td colspan="3">
                    <?= gd_radio_box('useType', $useType, $getValue['useType']); ?>
                </td>
            </tr>
        </table>
    </div>
</div>

<div>
    <div class="mgt10"></div>
    <div class="text-center">
        <input type="submit" value="검색" class="btn btn-black btn-hf" onclick="layer_list_search();" />
    </div>
    <div class="mgt10"></div>
</div>
</form>


<div class="table-header">
    <div class="pull-left">
        총 <strong class="text-danger"><?= number_format(gd_isset($page->recode['total'], 0)); ?></strong>명
    </div>
</div>

<table class="table table-rows">
    <thead>
    <tr>
        <th>번호</th>
        <th>아이디</th>
        <th>이름</th>
        <th>등급</th>
        <th>쿠폰발급여부</th>
        <th>SMS발송여부</th>
        <th>사용일</th>
    </tr>
    </thead>
    <tbody>
    <?php
    if (empty($data) === false) {
        foreach ($data as $key => $val) {
            ?>
            <tr class="text-center">
                <td><?=number_format($page->idx--)?></td>
                <td><button type="button" class="btn btn-link font-eng js-layer-crm" data-member-no="<?= $val['memNo'] ?>"><?= $val['memId'] ?></button></td>
                <td><?= $val['memNm'] ?></td>
                <td><?= $val['groupNm'] ?></td>
                <td><?php if (empty($val['memberCouponNo']) == false && $val['memberCouponNo'] > 0) echo 'O'; else echo 'X'; ?></td>
                <td><?php if ($val['smsFl'] == 'y') echo 'O'; else echo 'X'; ?></td>
                <?php if ($val['memberCouponUseDate'] == '0000-00-00 00:00:00' || $val['memberCouponUseDate'] == NULL) { ?>
                <td>-</td>
                <?php } else { ?>
                <td><?= gd_date_format('Y-m-d', $val['memberCouponUseDate']) ?></td>
                <?php } ?>
            </tr>
            <?php
        }
    } else {
        ?>
        <tr>
            <td colspan="7" class="no-data">검색 할 쿠폰이 없습니다.</td>
        </tr>
    <?php } ?>
    </tbody>
</table>

<div class="text-center"><?php echo $page->getPage('layer_list_search(\'PAGELINK\')');?></div>

<script type="text/javascript">
    $(function(){
        // 검색어 입력후 엔터시
        $('input[name="searchValue"]').keyup(function(e) {
            if (e.keyCode == 13) layer_list_search();
        });

        // 검색 submit시 처리
        $('#frmCouponSearch').submit(function(e){
            layer_list_search();
            e.preventDefault();
            return false;
        });

        // 회원ID 클릭시 처리
        $('.js-layer-crm').click(function(e){
            var memNo = $(this).data('member-no');
            window.open('/share/member_crm.php?popupMode=yes&memNo=' + memNo, 'member_crm', 'width=1200,height=850,scrollbars=yes');
            return false;
        });
    });

    // 페이지 출력
    function layer_list_search(pagelink) {
        if (typeof pagelink == 'undefined') {
            pagelink = '';
        }
        var parameters = {
            layerFormID: '<?php echo $layerFormID?>',
            parentFormID: '<?php echo $parentFormID?>',
            dataFormID: '<?php echo $dataFormID?>',
            dataInputNm: '<?php echo $dataInputNm?>',
            callFunc: '<?php echo $callFunc?>',
            dataSno: '<?php echo $getValue['dataSno']?>',
            searchMode: $('select[name=searchMode] option:selected').val(),
            searchValue: $('input[name=searchValue]').val(),
            issueCouponFl: $('input[name=issueCouponFl]:checked').val(),
            sendSmsFl: $('input[name=sendSmsFl]:checked').val(),
            useType: $('input[name=useType]:checked').val(),
            mode: '<?php echo $mode?>',
            couponNo: '<?php echo $couponNo?>',
            pagelink: pagelink
        };

        $.get('<?php echo URI_ADMIN;?>share/layer_comeback_coupon_result.php', parameters, function (data) {
            $('#<?php echo $layerFormID?>').html(data);
        });
    }
</script>

