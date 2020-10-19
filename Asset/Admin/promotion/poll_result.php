<div class="page-header js-affix">
    <h3><?= end($naviMenu->location); ?></h3>
</div>

<h5 class="table-title">설문조사 요약</h5>
<table class="table table-cols">
    <colgroup>
        <col class="width-md"/>
        <col/>
    </colgroup>
    <tbody>
    <tr>
        <th>
            설문 제목
        </th>
        <td>
            <div class="form-inline">
                <?php echo $title; ?>
            </div>
        </td>
    </tr>
    <tr>
        <th>
            설문 기간
        </th>
        <td>
            <div class="form-inline">
                <?php echo $date; ?>
            </div>
        </td>
    </tr>
    <tr>
        <th>
            진행범위
        </th>
        <td>
            <div class="form-inline">
                <?php echo $deviceFl[$data['pollDeviceFl']]; ?>
            </div>
        </td>
    </tr>
    <tr>
        <th>
            참여대상
        </th>
        <td>
            <div class="form-inline">
                <?php echo $groupFl[$data['pollGroupFl']]; ?>
            </div>
        </td>
    </tr>
    <tr>
        <th>
            참여자 현황
        </th>
        <td>
            <div class="form-inline">
                총 응답자 <?php echo number_format($total['all']); ?>명
                <?php
                    if ($data['pollGroupFl'] == 'all' || $data['pollMileage'] > 0) {
                        echo '(';
                        if ($data['pollGroupFl'] == 'all') {
                            echo '회원 ' . number_format($total['member']) . '명 / 비회원 ' . number_format($total['nonMember']) . '명 ';
                        }
                        if ($data['pollMileage'] > 0) {
                            echo ', 총 지급 마일리지 ' . number_format($total['mileage']) . '원';
                        }
                        echo ')';
                    }
                ?>
            </div>
        </td>
    </tr>
    <?php if ($data['pollGroupFl'] == 'select' && is_array($data['pollGroupNm']) === true) {?>
    <tr>
        <th>
            회원등급별<br />참여자 현황
        </th>
        <td>
            <?php foreach ($data['pollGroupNm'] as $k => $v) {?>
            <div class="form-inline">
                - <?php echo $v; ?> : <?php echo number_format($data['pollGroupCnt'][$k]); ?>명 (<?php echo $total['all'] > 0 ? floor(($data['pollGroupCnt'][$k] * 100) / $total['all']) : 0; ?>%)
            </div>
            <?php } ?>
        </td>
    </tr>
    <?php } ?>
</table>

<h5 class="table-title">설문 상세결과</h5>
<table class="table table-cols">
    <colgroup>
        <col class="width-md"/>
        <col/>
    </colgroup>
    <tbody>
    <?php
    if (is_array($item['itemTitle']) === true) {
        foreach ($item['itemTitle'] as $k => $v) {
            ?>
            <tr>
                <th colspan="2"><?php echo $v . ($item['itemRequired'][$k] == 'Y' ? ' <span class="text-red">(필수응답)</span>' : ''); ?></th>
            </tr>
            <tr>
                <th>
                    총 응답자
                </th>
                <td>
                    <div class="form-inline">
                        <?php echo number_format($sortData['total'][$k]); ?>명 (응답률 <?php echo $total['all'] > 0 ? floor(($sortData['total'][$k] * 100) / $total['all']) : 0; ?>%)
                        <?php if ($item['itemAnswerType'][$k] == 'sub') {?>
                            <button type="button" title="" class="btn btn-gray btn-sm btn-result-detail" data-code="<?php echo $data['pollCode']; ?>" data-detail="<?php echo $k; ?>" data-type="pollResult">상세내용 보기</button>
                        <?php } ?>
                    </div>
                </td>
            </tr>
            <?php if ($item['itemAnswerType'][$k] == 'obj' && is_array($item['itemAnswer'][$k]) === true) { ?>
                <tr>
                    <th>
                        응답결과
                    </th>
                    <td>
                        <?php foreach ($item['itemAnswer'][$k] as $key => $val) {?>
                            <div class="poll-graph">
                                <span class="graph-name"><?php echo $val; ?></span>
                                <span class="graph-gauge" style="width:<?php echo floor(($sortData[$k][$key] * 100) / $graphData[$k]) * 3; ?>px;"></span>
                                <span class="graph-text"><?php echo $sortData[$k][$key] * 1;?>명 (<?php echo $graphData[$k] > 0 ? floor(($sortData[$k][$key] * 100) / $graphData[$k]) : 0; ?>%)</span>
                                <?php if ($item['itemLastAnswer'][$k] && max(array_keys($item['itemAnswer'][$k])) == $key && $sortData[$k][$key] > 0) {?>
                                    <button type="button" title="" class="btn btn-gray btn-sm btn-result-detail" data-code="<?php echo $data['pollCode']; ?>" data-detail="<?php echo $k; ?>" data-type="pollResultEtc">상세내용 보기</button>
                                <?php } ?>
                            </div>
                        <?php } ?>
                    </td>
                </tr>
            <?php
            }
        }
    }
    ?>
    </tbody>
</table>
<div class="pull-right">
    <button type="button" class="btn btn-white btn-icon-excel js-poll-excel-download" data-code="<?php echo $data['pollCode']?>">엑셀다운로드</button>
</div>

<style>
    .poll-graph{
        padding:3px 0;
    }
    .poll-graph .graph-name{
        display: inline-block;
        width: 100px;
    }
    .poll-graph .graph-gauge{
        display: inline-block;
        background: #ff4c2e;
        height: 15px;
    }
</style>
<script typ="text/javascript">
    $(document).ready(function () {
        $('.btn-result-detail').click(function () {
            var addParam = {
                "layerFormID": 'viewInfoForm',
                "code": $(this).data('code'),
                "detail": $(this).data('detail'),
                "type": $(this).data('type'),
            };
            if (addParam['layerFormID'] == undefined) addParam['layerFormID'] = 'addSearchForm';
            $.ajax({
                url: '../promotion/poll_detail_view.php',
                type: 'get',
                data: addParam,
                async: false,
                success: function (data) {
                    data = '<div id="' + addParam['layerFormID'] + '">' + data + '</div>';
                    var layerForm = data;
                    BootstrapDialog.show({
                        title: "상세내용 보기",
                        size: get_layer_size('wide'),
                        message: $(layerForm),
                        closable: true,
                    });
                }
            });

            /*$.get('../promotion/poll_detail_view.php',{ code : code, detail : detail, type : type }, function(data){

                data = '<div id="viewInfoForm">'+data+'</div>';

                var layerForm = data;

                BootstrapDialog.show({
                    title:title,
                    size: get_layer_size('wide'),
                    message: $(layerForm),
                    closable: true
                });
            });*/
        });

        $('.js-poll-excel-download').click(function () {
            var code = $(this).data('code');

            ifrmProcess.location.href = '../promotion/poll_make_excel.php?code=' + code;
        });
    });
</script>