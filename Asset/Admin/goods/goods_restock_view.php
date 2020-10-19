<div class="page-header js-affix">
    <h3><?=end($naviMenu->location); ?></h3>
</div>

<!-- 상품정보 -->
<div class="table-title">
    상품 정보
</div>

<table class="table table-rows">
    <thead>
    <tr>
        <th class="width20p" rowspan="2">상품명</th>
        <th class="width20p" rowspan="2">옵션</th>
        <th class="width10p" rowspan="2">재고</th>
        <th class="width10p" rowspan="2">품절상태</th>
        <th class="width20p" colspan="2">노출상태</th>
        <th class="width20p" colspan="2">판매상태</th>
    </tr>
    <tr>
        <th class="width10p center">PC쇼핑몰</th>
        <th class="width10p center">모바일쇼핑몰</th>
        <th class="width10p center">PC쇼핑몰</th>
        <th class="width10p center">모바일쇼핑몰</th>
    </tr>
    </thead>
    <tbody>
    <?php
    if($getDataView['status'] === "deleteComplete"){
    ?>
        <tr <?=$getDataView['trBackground']?>>
            <td colspan="8" class="center">완전 삭제된 상품입니다.</td>
        </tr>
    <?php
    }
    else {
    ?>
        <tr <?= $getDataView['trBackground'] ?>>
            <td class="center"><?= $getDataView['goodsNm'] ?></td>
            <td class="center"><?= $getDataView['option'] ?></td>
            <td class="center"><?= number_format($getDataView['totalStock']) ?></td>
            <td class="center"><?= $getDataView['soldOutResult'] ?></td>
            <td class="center"><?= $getDataView['goodsDisplayFl'] ?></td>
            <td class="center"><?= $getDataView['goodsDisplayMobileFl'] ?></td>
            <td class="center"><?= $getDataView['goodsSellFl'] ?></td>
            <td class="center"><?= $getDataView['goodsSellMobileFl'] ?></td>
        </tr>
    <?php
    }
    ?>
    </tbody>
</table>
<div>
    <p class="notice-danger">
        노란색 리스트는 상품정보(상품명/옵션명/옵션값)가 변경된 리스트이며 재고량이 다를 수 있습니다.<br />
        빨간색 리스트는 상품이 삭제된 리스트이며, 완전 삭제된 상품의 경우 “완전삭제＂로 표기됩니다.<br />
        해당 상품의 상품정보를 확인하신 후 재입고 알림 메세지를 전송해 주세요.
    </p>
</div>
<!-- 상품정보 -->

<!-- 검색 -->
<div class="table-title mgt55">
    신청 내역
</div>

<form id="frmSearchGoods" name="frmSearchGoods" method="get" class="js-form-enter-submit">
<input type="hidden" name="diffKey" value="<?=$getDataView['diffKey']?>">
<table class="table table-cols">
    <colgroup>
        <col class="width-md"/>
        <col>
        <col class="width-md"/>
        <col/>
    </colgroup>
    <tbody>
    <tr>
        <th>발송여부</th>
        <td>
            <label class="radio-inline"><input type="radio" name="smsSendFl" value="" <?=gd_isset($checked['smsSendFl']['']); ?> />전체</label>
            <label class="radio-inline"><input type="radio" name="smsSendFl" value="y" <?=gd_isset($checked['smsSendFl']['y']); ?> />발송</label>
            <label class="radio-inline"><input type="radio" name="smsSendFl" value="n" <?=gd_isset($checked['smsSendFl']['n']); ?> />미발송</label>
        </td>
        <th>회원구분</th>
        <td>
            <label class="radio-inline"><input type="radio" name="memberFl" value="" <?=gd_isset($checked['memberFl']['']); ?> />전체</label>
            <label class="radio-inline"><input type="radio" name="memberFl" value="y" <?=gd_isset($checked['memberFl']['y']); ?> />회원</label>
            <label class="radio-inline"><input type="radio" name="memberFl" value="n" <?=gd_isset($checked['memberFl']['n']); ?> />비회원</label>
        </td>
    </tr>
    </tbody>
</table>


<p class="notice-info">
    SMS 잔여 포인트가 부족한 경우 부족한 포인트만큼 문자발송이 되지 않습니다.<br />
    <a href="/member/sms_log.php" target="_blank" class="btn-link">[회원>SMS 관리>SMS 발송 내역 보기]</a>에서 발송결과를 꼭 확인하시기 바랍니다.
</p>

<div class="table-btn">
    <input type="submit" value="검색" class="btn btn-lg btn-black">
</div>

<div class="table-header">
    <div class="pull-left">
        검색결과 <strong><?=number_format($page->recode['total']);?></strong>개 /
        전체 <strong><?=number_format($page->recode['amount']);?></strong>개
    </div>
    <div class="pull-right form-inline">
        <?=gd_select_box('sort', 'sort', $search['sortList'], null, $search['sort'], null); ?>
        <?=gd_select_box('pageNum', 'pageNum', gd_array_change_key_value([10, 20, 30, 40, 50, 60, 70, 80, 90, 100, 200, 300, 500]), '개 보기', Request::get()->get('pageNum'), null); ?>
    </div>
</div>
</form>
<!-- 검색 -->

<form id="frmList" action="" method="get" target="ifrmProcess">
    <input type="hidden" name="mode" value="">
    <table class="table table-rows">
        <thead>
        <tr>
            <th class="width10p center"><input type="checkbox" class="js-checkall" data-target-name="sno"></th>
            <th class="width10p">번호</th>
            <th class="width10p">신청일</th>
            <th class="width40p">신청자</th>
            <th class="width15p">휴대폰번호</th>
            <th class="width15p">발송여부</th>
        </tr>
        </thead>
        <tbody>
        <?php
        if (gd_isset($data)) {
            foreach ($data as $key => $val) {
                if($val['smsSendFl'] === 'y'){
                    $smsSend = "발송";
                }
                else {
                    $smsSend = "미발송";
                }

                $memberData = array();
                $memberInfo = '';
                if((int)$val['memNo'] > 0){
                    $memberData = $memberService->getMemberId($val['memNo']);
                    $memberInfo = '&nbsp;(<span class="js-layer-crm hand font-eng">' . $memberData['memId'] . '</span> / ' . $memberData['groupNm'] . ')';
                }
        ?>
                <tr data-member-no="<?= $val['memNo']; ?>">
                    <td class="center"><input type="checkbox" name="sno[<?=$val['sno']; ?>]" value="<?=$val['sno']; ?>" /></td>
                    <td class="center number"><?=number_format($page->idx--); ?></td>
                    <td class="center number"><?=$val['regdt']?></td>
                    <td><?=$val['name']?><?=$memberInfo?></td>
                    <td class="center"><?=$val['cellPhone']?></td>
                    <td class="center"><?=$smsSend?></td>
                </tr>
                <?php
            }
        }
        else {
        ?>
            <tr>
                <td class="center" colspan="6">검색된 정보가 없습니다.</td>
            </tr>
        <?php
        }
        ?>
        </tbody>
    </table>

    <div class="table-action">
        <div class="pull-left">
            해당 상품 재입고 알림 신청한
            <div style="display: inline-block; vertical-align: bottom;"><?=gd_select_box('smsTarget', 'smsTarget', $smsTarget, null, '', null); ?></div>
            에게
            <button type="button" id="restock_sms_send" class="btn btn-info">SMS 발송</button>
            <div class="js-sms-send" style="display: none;" data-type="" data-opener="goods" data-target-selector=""></div>
        </div>
        <div class="pull-right"></div>
    </div>
</form>

<div class="text-center"><?=$page->getPage(); ?></div>


<script type="text/javascript">
    <!--
    $(document).ready(function () {
        $("#restock_sms_send").click(function(){
            var targetValue = $("#smsTarget").val();
            if($.trim(targetValue) === ''){
                alert("발송범위를 선택하세요.");
                return;
            }

            $(".js-sms-send").data("type", targetValue);
            if(targetValue === 'search' || targetValue === 'all'){
                $(".js-sms-send").data("target-selector", "#frmSearchGoods");
            }
            else {
                $(".js-sms-send").data("target-selector", "input[name*=sno]:checked");
            }

            $(".js-sms-send").trigger('click');
        });

        $("select[name='pageNum'], select[name='sort']").change(function () {
            $('#frmSearchGoods').submit();
        });
    });
    //-->
</script>
