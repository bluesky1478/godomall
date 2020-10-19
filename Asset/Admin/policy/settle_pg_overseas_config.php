<script type="text/javascript">
    <!--
    $(document).ready(function () {
        $('#frmPG').validate({
            submitHandler: function (form) {
                <?php if ($pgAutoSetting === true) { ?>
                form.target = 'ifrmProcess';
                form.submit();
                <?php } else {?>
                alert('\'고도 > 쇼핑몰 > 부가서비스 > 전자결제(PG)서비스\'에서 신규 신청을 하셔야 합니다.<br/>해외전자결제 서비스 설정에 실패 하였습니다.');
                return false;
                <?php }?>
            },
            rules: {
            },
            messages: {
            }
        });
    });

    /**
     * 출력 여부1
     *
     * @param string arrayID 해당 ID
     * @param string modeStr 출력 여부 (show or hide)
     */
    function display_toggle(thisID, modeStr) {
        if (modeStr == 'show') {
            $('#' + thisID).show();
        } else if (modeStr == 'hide') {
            $('#' + thisID).hide();
        }
    }
    //-->
</script>

<form id="frmPG" name="frmPG" action="settle_ps.php" method="post" enctype="multipart/form-data">
    <input type="hidden" name="mode" value="pg_overseas_config"/>

    <div class="page-header js-affix">
        <h3><?php echo end($naviMenu->location); ?>
            <small>계약된 해외 결제(PG)의 설정을 하실 수 있습니다.</small>
        </h3>
        <div class="btn-group">
            <input type="submit" value="PG 정보 저장" class="btn btn-red"/>
        </div>
    </div>

    <div class="table-title gd-help-manual mgt30">
        해외 결제 설정
    </div>
    <ul class="nav nav-tabs nav-justified mgb28" role="tablist">
        <?php
        foreach ($pgList as $pgName => $pgCode) {
            foreach ($pgCode as $key => $val) {
                if ($key === 'alipay') {
                    $classNm = 'active';
                } else {
                    $classNm = '';
                }
                ?>
                <li role="presentation" class="<?php echo $classNm;?>">
                    <a href="#pg_<?php echo $key;?>" role="tab" data-toggle="tab"><?php echo $val;?></a>
                </li>
                <?php
            }
        } ?>
    </ul>

    <div class="tab-content">
        <?php
        foreach ($pgList as $pgName => $pgCode) {
            foreach ($pgCode as $key => $val) {
                if ($key === 'alipay') {
                    $classNm = 'active';
                } else {
                    $classNm = '';
                }
            ?>
        <div role="pg_<?php echo $key;?>" class="tab-pane <?php echo $classNm;?>" id="pg_<?php echo $key;?>">
            <table class="table table-cols" style="border-top-color: #fff; margin-top: -20px;">
                <colgroup>
                    <col class="width-lg"/>
                    <col/>
                </colgroup>
                <tr>
                    <th>사용여부</th>
                    <td class="form-inline">
                        <?php if ($data[$pgName][$key]['pgAutoSetting'] === 'y') { ?>
                            <label class="radio-inline">
                                <input type="radio" name="<?php echo $pgName;?>[<?php echo $key;?>][useFl]" value="y" <?php echo gd_isset($checked[$settleKindConf[$key]]['y']);?> /> 사용함
                            </label>
                            <label class="radio-inline">
                                <input type="radio" name="<?php echo $pgName;?>[<?php echo $key;?>][useFl]" value="n" <?php echo gd_isset($checked[$settleKindConf[$key]]['n']);?> /> 사용안함
                            </label>
                        <?php } else { ?>
                            <input type="hidden" name="<?php echo $pgName;?>[<?php echo $key;?>][useFl]" value="n"/>
                            <div class="notice-info notice-danger">해외 결제(PG) 신청 전 또는 승인대기상태입니다.</div>
                            <div class="notice-info">
                                신청 전인 경우 먼저 서비스를 신청하세요.
                                <a href="https://www.godo.co.kr/echost/global/overseas-pg-intro.gd" target="_blank" class="btn btn-gray btn-sm">해외 결제(PG) 신청</a>
                            </div>
                        <?php }?>
                        <input type="hidden" name="<?php echo $pgName;?>[<?php echo $key;?>][testFl]" value="n" />
                        <?php if ($data[$pgName][$key]['testFl'] === 'y') { ?>
                            <div class="notice-info notice-danger">테스트용 설정입니다. 실결제 처리가 되지 않습니다.</div>
                        <?php }?>
                    </td>
                </tr>
                <?php if ($data[$pgName][$key]['pgAutoSetting'] === 'y') { ?>
                <tr>
                    <th>사용상점</th>
                    <td class="form-inline">
                        <?php
                        foreach ($gGlobal['useMallList'] as $gVal) {
                            if ($useMallConf[$key][$gVal['sno']] !== 'y') {
                                continue;
                            }
                            ?>
                            <label class="radio-inline">
                                <input type="checkbox" name="<?php echo $pgName;?>[<?php echo $key;?>][mallFl][<?= $gVal['sno'] ?>]" value="y" <?= gd_isset($checked[$settleKindConf[$key]]['mallFl'][$gVal['sno']]['y']); ?>/><span class="flag flag-16 flag-<?= $gVal['domainFl'] ?>"></span> <?= $gVal['mallName'] ?>
                            </label>
                            <?php
                        }
                        ?>
                    </td>
                </tr>
                <?php }?>
                <tr>
                    <th>승인 가능 화폐</th>
                    <td class="form-inline">
                        <?php
                        echo "<div class='bold' style='font-size: 20px; margin-bottom: -15px;'>" . implode(' / ', $settleCurrencyConf[$pgName]) . "</div><hr/>";
                        if (count($settleCurrencyConf[$pgName]) > 1) {
                            if ($data[$pgName][$key]['pgAutoSetting'] === 'y') {
                                echo "<div class='mgb10 bold' style='font-size: 12px;'>■ 승인 미지원 화폐에 대한 화폐설정</div>";
                                foreach ($settleCurrencyConf[$pgName] as $cKey => $cVal) {
                                    if ($data[$pgName][$key]['pgCurrency'] === $cVal) {
                                        $checkedStr = 'checked="checked"';
                                    } else {
                                        $checkedStr = '';
                                    }
                                    echo "<label class=\"radio-inline\"><input type=\"radio\" name=\"" . $pgName . "[" . $key . "][pgCurrency]\" value=\"" . $cVal . "\" " . $checkedStr . "/> " . $cVal . "</label>";
                                }
                            } else {
                                echo "<input type=\"hidden\" name=\"" . $pgName . "[" . $key . "][pgCurrency]\" value=\"" . $settleCurrencyConf[$pgName][0] . "\"/>";
                            }
                            echo "
                            <div class=\"mgt10 notice-info\">
                                사용화폐가 USD 혹은 CNY일 경우에는 해당 화폐단위로 승인이 이루어집니다. (ex: 영문몰 – USD , 중문몰 – CNY )<br/>
                                다만, 일문몰처럼 사용화폐와 승인화폐가 다를 경우, 승인되는 화폐단위를 선택합니다.<br/>
                                 (ex: 일문몰 사용화폐인 JPY에 대한 승인화폐 설정)
                            </div>";
                        } else {
                            echo "<input type=\"hidden\" name=\"" . $pgName . "[" . $key . "][pgCurrency]\" value=\"" . $settleCurrencyConf[$pgName][0] . "\"/>";
                            echo "
                            <div class=\"notice-info\">
                                해당 PG는 사용화폐단위와 상관없이 USD로만 결제가 가능합니다. <br/>
                                따라서, 상품에 입력된 기준가격 대비 미국달러(USD)로 환율설정된 금액으로 결제가 이루어집니다.
                            </div>";
                        }
                        ?>
                    </td>
                </tr>
                <tr>
                    <th><?php echo $val;?> 상점 ID</th>
                    <td class="form-inline">
                        <?php if ($data[$pgName][$key]['pgAutoSetting'] === 'y') { ?>
                            <span class="text-blue bold"><?php echo $data[$pgName][$key]['pgId'];?></span> <span class="text-blue">(자동 설정 완료)</span>
                        <?php } else { ?>
                            <div class="notice-info notice-danger">해외 결제(PG) 신청 전 또는 승인대기상태입니다.</div>
                        <?php }?>
                    </td>
                </tr>
                <tr>
                    <th><?php echo $val;?> 상점 Key</th>
                    <td>
                        <?php if ($data[$pgName][$key]['pgAutoSetting'] === 'y') { ?>
                            <?php
                            foreach ($pgKeyConf[$pgName] as $kKey => $kVal) {
                                if (empty($data[$pgName][$key][$kKey]) === false) {
                                    echo "<div>" . $kVal . " : <span class=\"text-blue bold\">" . $data[$pgName][$key][$kKey] . "</span></div>";
                                }
                            }
                            ?>
                        <?php } else { ?>
                            <div class="notice-info notice-danger">해외 결제(PG) 신청 전 또는 승인대기상태입니다.</div>
                        <?php }?>
                    </td>
                </tr>
                <tr>
                    <th><?php echo ucwords ($pgName);?> 사이트</th>
                    <td>
                        <a href="<?php echo $pgSite[$pgName]['admin'];?>" target="_blank" class="btn btn-gray btn-sm"> 상점 관리자모드 바로가기</a>
                        <a href="<?php echo $pgSite[$pgName]['site'];?>" target="_blank" class="btn btn-gray btn-sm"> 사이트 바로가기</a>
                    </td>
                </tr>
            </table>
        </div>
            <?php
            }
        } ?>
    </div>
</form>
