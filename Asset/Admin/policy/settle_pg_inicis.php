<script type="text/javascript">
    <!--
    $(document).ready(function(){
        <?php
        if ($data['noInterestFl'] != 'y') {
            echo 'display_toggle(\'noInterestToggle\',\'hide\');'.chr(10);
        }
        if ($data['installmentFl'] != 'y') {
            echo 'display_toggle(\'installmentToggle\',\'hide\');'.chr(10);
        }
        if ($data['eggDisplayFl'] == 'n') {
            echo 'display_toggle(\'eggDisplayBannerConf\',\'hide\');'.chr(10);
        }
        ?>
    });
    //-->
</script>

<div class="table-title gd-help-manual">
    <?php echo $pgNm;?> 일반결제 설정
</div>
<table class="table table-cols">
    <colgroup>
        <col class="width-lg"/>
        <col/>
    </colgroup>
    <tr>
        <th>PG사 모듈 버전</th>
        <td>INIpay Standard 웹표준 결제 (V 1.2.2 - 20160421) / INIpay Mobile WEB (V 4.08 - 20160322)</td>
    </tr>
    <tr>
        <th>결제수단 설정</th>
        <td>
            <label class="checkbox-inline"><input type="checkbox" name="settleKind[pc][useFl]" value="y" <?php echo gd_isset($checked['pc']['y']);?> <?php echo gd_isset($disabled['pc']['y']);?> /> 신용카드</label>
            <label class="checkbox-inline"><input type="checkbox" name="settleKind[pb][useFl]" value="y" <?php echo gd_isset($checked['pb']['y']);?> <?php echo gd_isset($disabled['pb']['y']);?> /> 계좌이체</label>
            <label class="checkbox-inline"><input type="checkbox" name="settleKind[pv][useFl]" value="y" <?php echo gd_isset($checked['pv']['y']);?> <?php echo gd_isset($disabled['pv']['y']);?> /> 가상계좌</label>
            <label class="checkbox-inline"><input type="checkbox" name="settleKind[ph][useFl]" value="y" <?php echo gd_isset($checked['ph']['y']);?> <?php echo gd_isset($disabled['ph']['y']);?> /> 휴대폰</label>
        </td>
    </tr>
    <tr>
        <th><?php echo $pgNm;?> 상점 ID</th>
        <td class="form-inline">
            <?php if ($data['pgAutoSetting'] === 'y') { ?>
                <span class="text-blue bold"><?php echo $data['pgId'];?></span> <span class="text-blue">(자동 설정 완료)</span>
                <div class="display-inline-block width-sm"><input type="button" onclick="settleKindUpdate();" value="<?php echo $pgNm;?> 정보 갱신" class="btn btn-gray btn-sm" /></div>
            <?php } else { ?>
                <?php if ($data['pgApprovalSetting'] === 'y') {?>
                    <span class="text-blue bold"><?php echo $data['pgId'];?></span> <span class="text-blue">(개별 승인 완료)</span>
                    <?php if (empty($pgApprovalId) === false) {?>
                        <input type="hidden" name="pgId" value="<?php echo $data['pgId'];?>" />
                        <div class="notice-info">개별 승인 요청이 승인 되었습니다. 반드시 정보를 확인 후 "PG 정보 저장"을 눌러 PG 설정을 완료 하셔야 합니다.</div>
                    <?php }?>
                <?php } else { ?>
                    <div class="notice-info notice-danger">전자결제(PG) 신청 전 또는 승인대기상태입니다.</div>
                    <div class="notice-info">
                        신청 전인 경우 먼저 서비스를 신청하세요.
                        <a href="https://www.godo.co.kr/echost/power/add/payment/pg-intro.gd" target="_blank" class="btn btn-gray btn-sm">전자결제(PG) 신청</a>
                    </div>
                    <div class="notice-info">
                        전자결제(PG)ID와 에스크로ID가 동일한 경우 PG 설정이 되지 않으면 "개별 승인 신청" 바랍니다.
                        <input type="button" onclick="pg_prefix();" value="개별 승인 신청" class="btn btn-gray btn-sm" />
                    </div>
                <?php }?>
            <?php }?>
        </td>
    </tr>
    <tr>
        <th><?php echo $pgNm;?> signKey</th>
        <td>
            <?php if ($data['pgAutoSetting'] === 'y') { ?>
                <span class="text-blue bold"><?php echo $data['pgKey'];?></span> <span class="text-blue">(자동 설정 완료)</span>
            <?php } else { ?>
                <?php if ($data['pgApprovalSetting'] === 'y') {?>
                    <input type="text" name="pgKey" value="<?php echo $data['pgKey'];?>" class="form-control width-2xl" />
                    <?php if (empty($pgApprovalId) === false) {?><div class="notice-info">개별 승인 요청이 승인 되었습니다. 반드시 정보를 확인 후 "PG 정보 저장"을 눌러 PG 설정을 완료 하셔야 합니다.</div><?php }?>
                <?php } else { ?>
                    <div class="notice-info notice-danger">전자결제(PG) 신청 전 또는 승인대기상태입니다.</div>
                <?php }?>
            <?php }?>
        </td>
    </tr>
    <tr>
        <th><?php echo $pgNm;?> Key File</th>
        <td class="form-inline">
            <?php
            $arrKeyFile = ['keypass.enc', 'mcert.pem', 'mpriv.pem',];
            if ($data['pgAutoSetting'] === 'y') {
                foreach ($arrKeyFile as $key => $val) {
                    if (is_file(UserFilePath::data('pg_key', $data['pgCode'], $data['pgId'], $val)) && empty($data['pgId']) === false) {
                        echo '<span class="text-blue">Key File #' . ($key + 1) . ' : ' . $val . ' - OK</span><br />';
                    } else {
                        echo '<span class="text-red">Key File #' . ($key + 1) . ' : ' . $val . ' - not exist</span>';
                    }
                }
            } else {
                if ($data['pgApprovalSetting'] === 'y') {
                    echo '<ul class="list-unstyled">';
                    foreach ($arrKeyFile as $key => $val) {
                        echo '<li>';
                        echo 'Key File #' . ($key + 1) . ' : <input type="file" name="pgIdKey[]" value="" class="form-control" />';
                        if (is_file(UserFilePath::data('pg_key', $data['pgCode'], $data['pgId'], $val)) && empty($data['pgId']) === false) {
                            echo ' <span class="text-blue">' . $val . ' - OK</span><br />';
                        } else {
                            echo ' <span class="text-red">' . $val . ' - not exist</span>';
                        }
                        echo '</li>';
                    }
                    echo '</ul>';
                    if (empty($pgApprovalId) === false) {
                        echo '<div class="notice-info">개별 승인 요청이 승인 되었습니다. 반드시 정보를 확인 후 "PG 정보 저장"을 눌러 PG 설정을 완료 하셔야 합니다.</div>';
                    }
                } else {
                    echo '<div class="notice-info notice-danger">전자결제(PG) 신청 전 또는 승인대기상태입니다.</div>';
                }
            }
            ?>
        </td>
    </tr>
    <tr>
        <th>일반 할부 사용 설정</th>
        <td>
            <label class="radio-inline"><input type="radio" name="installmentFl" value="n" <?php echo gd_isset($checked['installmentFl']['n']);?> onclick="display_toggle('installmentToggle','hide');" /> 일시불 결제</label>
            <label class="radio-inline"><input type="radio" name="installmentFl" value="y" <?php echo gd_isset($checked['installmentFl']['y']);?> onclick="display_toggle('installmentToggle','show');" /> 일반 할부 결제</label>
        </td>
    </tr>
    <tr id="installmentToggle">
        <th>일반 할부 기간 설정</th>
        <td class="space-checkbox">
<?php
    for ($i = 2; $i <= $pgPeriod['general']; $i++) {
        $strCheck        = gd_isset($checked['installmentPeroid'][$i]);
        if (empty($strCheck)) {
            $strClass    = 'nobold';
        } else {
            $strClass    = 'bold';
        }
        echo '<label class="display-inline-block width-2xs hand"><input type="checkbox" name="installmentPeroid[]" value="'.$i.'" '.$strCheck.' onclick="checked_bold(this)" /><span class="'.$strClass.'">'.$i.'개월</span></label>';
        if (($i % 12) == 0) {
            echo '<br/>';
        }
    }
?>
        </td>
    </tr>
    <tr>
        <th>무이자 할부 사용 설정</th>
        <td>
            <label class="radio-inline"><input type="radio" name="noInterestFl" value="y" <?php echo gd_isset($checked['noInterestFl']['y']);?> onclick="display_toggle('noInterestToggle','show');" /> 사용함</label>
            <label class="radio-inline"><input type="radio" name="noInterestFl" value="n" <?php echo gd_isset($checked['noInterestFl']['n']);?> onclick="display_toggle('noInterestToggle','hide');" /> 사용안함</label>
        </td>
    </tr>
    <tr id="noInterestToggle">
        <th>무이자 할부 기간 설정</th>
        <td>
            <input type="button" onclick="noInterest_peroid_conf('<?php echo $data['pgCode'];?>');" value="기간 설정" class="btn btn-gray btn-sm" />
            <div id="noInterestPeroid">
<?php
    $arrPeroid    = explode(',', $data['noInterestPeroid']);
    foreach ($arrPeroid as $key => $val) {
        if (empty($val)) {
            continue;
        }
        $arrCard    = explode('-',$val);
        if ($arrCard[0] == 'ALL') {
            $divId        = 'all';
            $strCard    = '전체카드';
        } else {
            $divId        = $key;
            $strCard    = $pgNointerest[$arrCard[0]];
        }
        echo '<div id="noInterest_Peroid_'.$divId.'" class="mgt3">'.chr(10);
        echo '<input type="button" onclick="field_remove(\'noInterest_Peroid_'.$divId.'\');" value="삭제" class="btn btn-red-box btn-xs" />'.chr(10);
        echo '<input type="hidden" name="noInterestPeroid[]" value="'.$val.'" />'.chr(10);
        echo ' <span>'.$strCard.' - '.str_replace(':','개월, ',$arrCard[1]).'개월</span>'.chr(10);
        echo '</div>'.chr(10);
    }
?>
            </div>
        </td>
    </tr>
    <tr>
        <th>옵션 설정</th>
        <td class="form-inline">
            <?php echo gd_check_box('pgOption[]', ['CARDPOINT' => '신용카드 포인트 사용', 'OCB' => 'OK Cashbag 포인트 적립 사용'], $data['pgOption']);?>
        </td>
    </tr>
    <tr>
        <th>가상계좌 입금기한</th>
        <td class="form-inline">
            <input type="text" name="vBankDay" value="<?php echo $data['vBankDay'];?>" maxlength="2" class="form-control js-number width-3xs" data-number="2, 30, 3" /> 일
        </td>
    </tr>
    <tr>
        <th>가상계좌 입금내역<br />실시간 통보 URL</th>
        <td class="input_area snote">
            <?php
            if ($isGodomallDomain === true) {
                echo '<div class="notice-info notice-danger">' . $pgNm . ' 가상계좌 입금내역 실시간 통보를 위해서는 쇼핑몰 도메인이 설정되어야 합니다. 쇼핑몰 도메인을 설정하세요. <a href="../policy/base_info.php" class="text-red">설정하기 ></a></div>';
            } else {
                echo '<div class="font-eng bold">' . $vBankReturnUrl . '</div>';
                echo '<div class="notice-info">' . $pgNm . ' 상점 관리자모드 [ 상점정보 > 계약정보 > 결제수단 정보 ]의 "가상계좌 항목"에 "입금통보URL(IP)"에 넣으세요.</div>';
                echo '<div class="notice-info">설정된 쇼핑몰 도메인이 변경이 되었다면 정확한 실시간 통보를 위해 쇼핑몰 도메인을 변경하세요. <a href="../policy/base_info.php" class="btn-link">변경하기 ></a></div>';
            }
            ?>
        </td>
    </tr>
    <input type="hidden" name="testFl" value="n" />
    <tr>
        <th>앱 스키마 설정<br />app scheme</th>
        <td>
            <input type="text" name="appScheme" value="<?php echo $data['appScheme'];?>" class="form-control width-2xl" />
        </td>
    </tr>
    <!--<tr>
        <th>테스트 여부</th>
        <td>
            <label><input type="radio" name="testFl" value="n" <?php echo gd_isset($checked['testFl']['n']);?> /> 실결제</label>
            <label><input type="radio" name="testFl" value="y" <?php echo gd_isset($checked['testFl']['y']);?> /> 테스트 결제</label>
        </td>
    </tr>-->
    <tr>
        <th>복합과세안내</th>
        <td>
            <div class="notice-info">
                <div>필독 : PG사와의 과세 & 복합과세 계약 유의사항</div>
                <div class="text-danger">- 복합과세(면/과세 상품 동시 취급)로 쇼핑몰을 운영하신다면, 반드시 먼저 PG사와 복합과세 계약을 신청하시기 바랍니다.</div>
                <div>- 복합과세로 PG사와 계약이 되어 있지 않은 상태에서 복합과세로 설정 시 일부 면세 상품의 부분취소가 어려울 수 있습니다.</div>
            </div>
        </td>
    </tr>
    <tr>
        <th><?php echo $pgNm;?> 사이트</th>
        <td>
            <a href="https://iniweb.inicis.com/index.html" target="_blank" class="btn btn-gray btn-sm"><?php echo $pgNm;?> 상점 관리자모드 바로가기</a>
            <a href="http://www.inicis.com/" target="_blank" class="btn btn-gray btn-sm"><?php echo $pgNm;?> 사이트 바로가기</a>
        </td>
    </tr>
</table>

<div class="table-title gd-help-manual">
    <?php echo $pgNm;?> 에스크로 설정
</div>
<table class="table table-cols">
    <colgroup>
        <col class="width-lg"/>
        <col/>
    </colgroup>
    <tr>
        <th>PG사 모듈 버전</th>
        <td>INIpay V5.0 INIescrow 신에스크로 (V 0.0.7 - 20160502)</td>
    </tr>
    <tr>
        <th>사용 설정</th>
        <td>
            <label class="radio-inline"><input type="radio" name="escrowFl" value="y" <?php echo gd_isset($checked['escrowFl']['y']);?> /> 사용함</label>
            <label class="radio-inline"><input type="radio" name="escrowFl" value="n" <?php echo gd_isset($checked['escrowFl']['n']);?> /> 사용안함</label>
        </td>
    </tr>
    <tr>
        <th>결제수단 설정</th>
        <td>
            <div class="checkbox">
                <label class="checkbox-inline"><input type="checkbox" name="settleKind[ec][useFl]" value="y" <?php echo gd_isset($checked['ec']['y']);?> <?php echo gd_isset($disabled['ec']['y']);?> /> 신용카드</label>
                <label class="checkbox-inline"><input type="checkbox" name="settleKind[eb][useFl]" value="y" <?php echo gd_isset($checked['eb']['y']);?> <?php echo gd_isset($disabled['eb']['y']);?> /> 계좌이체</label>
                <label class="checkbox-inline"><input type="checkbox" name="settleKind[ev][useFl]" value="y" <?php echo gd_isset($checked['ev']['y']);?> <?php echo gd_isset($disabled['ev']['y']);?> /> 가상계좌</label>
            </div>
            <div class="notice-info">
                반드시 &quot;<?php echo $pgNm;?>&quot;에 에스크로 계약이 되어 있는 결제수단만 체크를 하십시요.
            </div>
        </td>
    </tr>
    <tr>
        <th>구매 안전 표시</th>
        <td>
            <label class="radio-inline">
                <input type="radio" name="eggDisplayFl" value="a" <?php echo gd_isset($checked['eggDisplayFl']['a']);?> onclick="display_toggle('eggDisplayBannerConf','show');" /> 표시함
            </label>
            <label class="radio-inline">
                <input type="radio" name="eggDisplayFl" value="n" <?php echo gd_isset($checked['eggDisplayFl']['n']);?> onclick="display_toggle('eggDisplayBannerConf','hide');" /> 표시안함
            </label>
        </td>
    </tr>
    <tr id="eggDisplayBannerConf">
        <th>구매 안전 표시 로고</th>
        <td class="form-inline">
            <input type="hidden" name="eggDisplayBannerFl" value="self" />
            <div>
                <textarea name="eggDisplayBannerTmp" rows="5" class="form-control width90p"><?php echo gd_htmlspecialchars_decode($data['eggDisplayBanner']);?></textarea>
            </div>
            <div class="notice-info">
                [<?php echo $pgNm;?> 인증마크]</a>에서 제공 받은 내용을 넣으세요
                <a href="https://www.inicis.com/blog/archives/824" class="btn btn-gray btn-sm" target="_blank">[<?php echo $pgNm;?> 인증마크 바로가기]</a>
            </div>
        </td>
    </tr>
</table>

<div class="table-title gd-help-manual">
    <?php echo $pgNm;?> 현금영수증 설정
</div>
<table class="table table-cols">
    <colgroup>
        <col class="width-lg"/>
        <col/>
    </colgroup>
    <tr>
        <th>PG사 모듈 버전</th>
        <td>INIpay V5.0 (V 0.2.1 - 20151224)</td>
    </tr>
    <tr>
        <th>사용 설정</th>
        <td>
            <label class="radio-inline"><input type="radio" name="cashReceiptFl" value="y" <?php echo gd_isset($checked['cashReceiptFl']['y']);?> /> 사용함</label>
            <label class="radio-inline"><input type="radio" name="cashReceiptFl" value="n" <?php echo gd_isset($checked['cashReceiptFl']['n']);?> /> 사용안함</label>
        </td>
    </tr>
    <tr>
        <th>필수 신청</th>
        <td>
            <label class="radio-inline"><input type="radio" name="cashReceiptAboveFl" value="y" <?php echo gd_isset($checked['cashReceiptAboveFl']['y']);?> /> 사용함</label>
            <label class="radio-inline"><input type="radio" name="cashReceiptAboveFl" value="n" <?php echo gd_isset($checked['cashReceiptAboveFl']['n']);?> /> 사용안함</label>
            <div class="notice-info">현금성 거래 시, 고객이 주문서 작성 시점에 현금영수증을 필수로 신청하도록 설정하는 기능입니다.</div>
        </td>
    </tr>
    <tr>
        <th>신청 기간 제한</th>
        <td>
            <div class="form-inline">
                결제완료일로 부터
                <?php echo gd_select_box('cashReceiptPeriod','cashReceiptPeriod', gd_array_change_key_value($cashReceiptPeriod),'일',$data['cashReceiptPeriod']);?>
                이내 신청 가능
            </div>
        </td>
    </tr>
    <tr>
        <th>현금영수증 발급방법</th>
        <td>
            <label class="radio-inline"><input type="radio" name="cashReceiptAutoFl" value="y" <?php echo gd_isset($checked['cashReceiptAutoFl']['y']);?> /> 자동 발급</label>
            <label class="radio-inline"><input type="radio" name="cashReceiptAutoFl" value="n" <?php echo gd_isset($checked['cashReceiptAutoFl']['n']);?> /> 관리자 직접 발급</label>
        </td>
    </tr>
</table>
