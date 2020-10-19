<form id="frmSearchScm" method="get" class="js-form-enter-submit">
    <table class="table table-cols">
        <tr>
            <th>공급사명</th>
            <td class="width300">
                <input type="hidden" name="popupMode" value="<?=$popupMode;?>">
                <input type="hidden" name="key" value="companyNm">
                <input type="text" name="keyword" value="<?php echo $search['keyword']; ?>" class="form-control"/>
            </td>
            <td>
                <input type="submit" value="검색" class="btn btn-lg btn-black">
            </td>
        </tr>
    </table>
</form>
<table class="table table-rows">
    <thead>
    <tr>
        <th>번호</th>
        <th>공급사명</th>
        <th>상품수수료</th>
        <th>배송수수료</th>
        <th>상태</th>
        <th>등록일</th>
        <th>수정</th>
    </tr>
    </thead>
    <tbody>
    <?php
    if (empty($data) === false && is_array($data)) {
        foreach ($data as $row) {
            //본사는 노출하지 않음
            if ($row['scmKind'] == 'c') {
                continue;
            }
            if ($row['scmType'] == 'y') {
                $row['scmType'] = '운영';
                $disabled = 'disabled="disabled"';
            } else if ($row['scmType'] == 'n') {
                $row['scmType'] = '일시정지';
                $disabled = '';
            } else if ($row['scmType'] == 'x') {
                $row['scmType'] = '탈퇴';
                $disabled = '';
            }
            $addScmCommission = '';
            $addScmCommissionDelivery = '';
            $sellCnt = 1;
            $deliveryCnt = 1;
            $sellDivision = '';
            $deliveryDivision = '';
            //추가 수수료
            foreach ($row['addCommissionData']  as $key => $val) {
                if ($sellCnt != 1) { $sellDivision = ' / '; }
                if ($val['commissionType'] == 'sell' && $val['commissionValue'] != 0.00) {
                    $addScmCommission .= $sellDivision.$val['commissionValue'].'%';
                    $sellCnt++;
                }
                if ($deliveryCnt != 1) { $deliveryDivision = ' / '; }
                if ($val['commissionType'] == 'delivery' && $val['commissionValue'] != 0.00) {
                    $addScmCommissionDelivery .= $deliveryDivision.$val['commissionValue'].'%';
                    $deliveryCnt++;
                }
            }
            //판매수수료 동일 적용
            if ($row['scmSameCommission']) {
                $scmCommissionDeliveryTd = $row['scmSameCommission'];
            } else {
                $scmCommissionDeliveryTd = $row['scmCommissionDelivery'].'%(기본)<br/>'.$addScmCommissionDelivery;
            }
            ?>
            <tr class="text-center">
                <td><?= number_format($page->idx--); ?></td>
                <td><?= $row['companyNm']; ?></td>
                <td><strong><?= $row['scmCommission']; ?>%(기본)</strong><br/><?= $addScmCommission; ?></td>
                <td><?= $scmCommissionDeliveryTd; ?></td>
                <td><?= $row['scmType']; ?></td>
                <td><?= gd_date_format('Y-m-d', $row['regDt']); ?></td>
                <td><a href="#" data-scm-no="<?= $row['scmNo']; ?>" class="btn btn-dark-gray btn-sm js-scmCommission-regist">수정</a></td>
            </tr>
            <?php
        }
    } else {
        ?>
        <tr>
            <td colspan="13" class="no-data">
                검색된 공급사가 없습니다.
            </td>
        </tr>
        <?php
    }
    ?>
    </tbody>
</table>
<div class="center"><?= $page->getPage(); ?></div>
<div class="table-btn">
    <input type="button" value="확인" class="btn btn-lg btn-black js-close">
</div>
<script type="text/javascript">
    <!--
    $(document).ready(function () {
        $('.js-close').click(function () {
            close();
        });
        //공급사 수수료 설정 팝업
        $('.js-scmCommission-regist').click(function() {
            var scmNo =  $(this).attr('data-scm-no');
            var url = "/scm/scm_regist.php?popupMode=yes&scmno=" + scmNo;
            var win = popup({
                url: url,
                width: 1000,
                height: 700,
                resizable: 'yes'
            });
        });
    });
    //-->
</script>