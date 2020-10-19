<form id="frmSettlekind" name="frmSettlekind" action="settle_ps.php" method="post" target="ifrmProcess">
    <input type="hidden" name="mode" value="settle_settlekind"/>
    <input type="hidden" name="mobilePgConfFl" value="<?php echo $mobilePgConfFl?>"/>

    <div class="page-header js-affix">
        <h3><?php echo end($naviMenu->location); ?>
            <small>각 결제 수단의 명칭을 변경하실 수 있습니다.</small>
        </h3>
        <input type="submit" value="저장" class="btn btn-red">
    </div>

    <div class="table-title gd-help-manual">
        결제 수단 설정
    </div>
    <table class="table table-cols">
        <thead>
        <tr>
            <th class="width-md">결제 기준</th>
            <th class="width-md">결제 수단 명칭</th>
            <th class="width-md">노출 이름</th>
            <th class="text-left" style="padding-left:75px">사용 설정</th>
        </tr>
        </thead>
        <tbody>
        <tr>
            <th rowspan="5">일반 결제</th>
            <td class="left bold">
                <img src="<?php echo UserFilePath::adminSkin('gd_share', 'img', 'settlekind_icon')->www(); ?>/icon_settlekind_gb.gif" alt="무통장 입금"/> 무통장 입금
            </td>
            <td>
                <input type="text" name="gb[name]" value="<?php echo $data['gb']['name']; ?>" class="form-control width90p"/>
            </td>
            <td>
                <input type="hidden" name="gb[mode]" value="general"/>
                <label class="radio-inline">
                    <input type="radio" name="gb[useFl]" value="y" <?php echo gd_isset($checked['gb']['y']); ?> /> 사용함
                </label>
                <label class="radio-inline">
                    <input type="radio" name="gb[useFl]" value="n" <?php echo gd_isset($checked['gb']['n']); ?> /> 사용안함
                </label>
            </td>
        </tr>
        <tr>
            <td class="left bold">
                <img src="<?php echo UserFilePath::adminSkin('gd_share', 'img', 'settlekind_icon')->www(); ?>/icon_settlekind_pc.gif" alt="신용카드"/> 신용카드
            </td>
            <td>
                <input type="text" name="pc[name]" value="<?php echo $data['pc']['name']; ?>" class="form-control width90p"/>
            </td>
            <td>
                <input type="hidden" name="pc[mode]" value="general"/>
                <label class="radio-inline">
                    <input type="radio" name="pc[useFl]" value="y" <?php echo gd_isset($checked['pc']['y']); ?> <?php echo gd_isset($disabled['pc']['y']);?> /> 사용함
                </label>
                <label class="radio-inline">
                    <input type="radio" name="pc[useFl]" value="n" <?php echo gd_isset($checked['pc']['n']); ?> <?php echo gd_isset($disabled['pc']['n']);?> /> 사용안함
                </label>
            </td>
        </tr>
        <tr>
            <td class="left bold">
                <img src="<?php echo UserFilePath::adminSkin('gd_share', 'img', 'settlekind_icon')->www(); ?>/icon_settlekind_pb.gif" alt="계좌이체"/> 계좌이체
            </td>
            <td>
                <input type="text" name="pb[name]" value="<?php echo $data['pb']['name']; ?>" class="form-control width90p"/>
            </td>
            <td>
                <input type="hidden" name="pb[mode]" value="general"/>
                <label class="radio-inline">
                    <input type="radio" name="pb[useFl]" value="y" <?php echo gd_isset($checked['pb']['y']); ?> <?php echo gd_isset($disabled['pb']['y']);?> /> 사용함
                </label>
                <label class="radio-inline">
                    <input type="radio" name="pb[useFl]" value="n" <?php echo gd_isset($checked['pb']['n']); ?> <?php echo gd_isset($disabled['pb']['n']);?> /> 사용안함
                </label>
            </td>
        </tr>
        <tr>
            <td class="left bold">
                <img src="<?php echo UserFilePath::adminSkin('gd_share', 'img', 'settlekind_icon')->www(); ?>/icon_settlekind_pv.gif" alt="가상계좌"/> 가상계좌
            </td>
            <td>
                <input type="text" name="pv[name]" value="<?php echo $data['pv']['name']; ?>" class="form-control width90p"/>
            </td>
            <td>
                <input type="hidden" name="pv[mode]" value="general"/>
                <label class="radio-inline">
                    <input type="radio" name="pv[useFl]" value="y" <?php echo gd_isset($checked['pv']['y']); ?> <?php echo gd_isset($disabled['pv']['y']);?> /> 사용함
                </label>
                <label class="radio-inline">
                    <input type="radio" name="pv[useFl]" value="n" <?php echo gd_isset($checked['pv']['n']); ?> <?php echo gd_isset($disabled['pv']['n']);?> /> 사용안함
                </label>
            </td>
        </tr>
        <tr>
            <td class="left bold">
                <img src="<?php echo UserFilePath::adminSkin('gd_share', 'img', 'settlekind_icon')->www(); ?>/icon_settlekind_ph.gif" alt="휴대폰"/> 휴대폰
            </td>
            <td>
                <input type="text" name="ph[name]" value="<?php echo $data['ph']['name']; ?>" class="form-control width90p"/>
            </td>
            <td>
                <input type="hidden" name="ph[mode]" value="general"/>
                <label class="radio-inline">
                    <input type="radio" name="ph[useFl]" value="y" <?php echo gd_isset($checked['ph']['y']); ?> <?php echo gd_isset($disabled['ph']['y']);?> /> 사용함
                </label>
                <label class="radio-inline">
                    <input type="radio" name="ph[useFl]" value="n" <?php echo gd_isset($checked['ph']['n']); ?> <?php echo gd_isset($disabled['ph']['n']);?> /> 사용안함
                </label>
                <?php
                if ($mobilePgConfFl === true) {
                    echo '<div class="notice-info">
                    휴대폰 결제 사용 설정은 <a href="../policy/settle_pg_mobile_config.php" target="_blank" class="snote bold">[기본설정 > 결제정책 > 휴대폰 결제 서비스 설정]</a> 에서만 가능합니다.
                    </div>';
                }
                ?>
            </td>
        </tr>
        <tr>
            <th rowspan="3">에스크로 결제</th>
            <td class="left bold">
                <img src="<?php echo UserFilePath::adminSkin('gd_share', 'img', 'settlekind_icon')->www(); ?>/icon_settlekind_ec.gif" alt="신용카드"/> 신용카드
            </td>
            <td>
                <input type="text" name="ec[name]" value="<?php echo $data['ec']['name']; ?>" class="form-control width90p"/>
            </td>
            <td>
                <input type="hidden" name="ec[mode]" value="escrow"/>
                <label class="radio-inline">
                    <input type="radio" name="ec[useFl]" value="y" <?php echo gd_isset($checked['ec']['y']); ?> <?php echo gd_isset($disabled['ec']['y']);?> /> 사용함
                </label>
                <label class="radio-inline">
                    <input type="radio" name="ec[useFl]" value="n" <?php echo gd_isset($checked['ec']['n']); ?> <?php echo gd_isset($disabled['ec']['n']);?> /> 사용안함
                </label>
            </td>
        </tr>
        <tr>
            <td class="left bold">
                <img src="<?php echo UserFilePath::adminSkin('gd_share', 'img', 'settlekind_icon')->www(); ?>/icon_settlekind_eb.gif" alt="계좌이체"/> 계좌이체
            </td>
            <td>
                <input type="text" name="eb[name]" value="<?php echo $data['eb']['name']; ?>" class="form-control width90p"/>
            </td>
            <td>
                <input type="hidden" name="eb[mode]" value="escrow"/>
                <label class="radio-inline">
                    <input type="radio" name="eb[useFl]" value="y" <?php echo gd_isset($checked['eb']['y']); ?> <?php echo gd_isset($disabled['eb']['y']);?> /> 사용함
                </label>
                <label class="radio-inline">
                    <input type="radio" name="eb[useFl]" value="n" <?php echo gd_isset($checked['eb']['n']); ?> <?php echo gd_isset($disabled['eb']['n']);?> /> 사용안함
                </label>
            </td>
        </tr>
        <tr>
            <td class="left bold">
                <img src="<?php echo UserFilePath::adminSkin('gd_share', 'img', 'settlekind_icon')->www(); ?>/icon_settlekind_ev.gif" alt="가상계좌"/> 가상계좌
            </td>
            <td>
                <input type="text" name="ev[name]" value="<?php echo $data['ev']['name']; ?>" class="form-control width90p"/>
            </td>
            <td>
                <input type="hidden" name="ev[mode]" value="escrow"/>
                <label class="radio-inline">
                    <input type="radio" name="ev[useFl]" value="y" <?php echo gd_isset($checked['ev']['y']); ?> <?php echo gd_isset($disabled['ev']['y']);?> /> 사용함
                </label>
                <label class="radio-inline">
                    <input type="radio" name="ev[useFl]" value="n" <?php echo gd_isset($checked['ev']['n']); ?> <?php echo gd_isset($disabled['ev']['n']);?> /> 사용안함
                </label>
            </td>
        </tr>
        </tbody>
    </table>

    <?php
    $checkPgKey = ['f', 'o'];
    foreach ($data as $pKey => $pVal) {
        if (in_array(substr($pKey, 0, 1), $checkPgKey) === true) {
            echo '<input type="hidden" name="' . $pKey . '[name]" value="' . $data[$pKey]['name'] . '" />' . PHP_EOL;
            echo '<input type="hidden" name="' . $pKey . '[mode]" value="' . $data[$pKey]['mode'] . '" />' . PHP_EOL;
            echo '<input type="hidden" name="' . $pKey . '[useFl]" value="' . $data[$pKey]['useFl'] . '" />' . PHP_EOL;
        }
    }
    ?>

    <?php if ($pgConfFl === false) {?>
        <div class="notice-info mgl15 mgb15">
            전자결제(PG) 신청 전인 경우 먼저 서비스를 신청하세요. <a href="https://www.godo.co.kr/echost/power/add/payment/pg-intro.gd" target="_blank" class="btn btn-gray btn-sm">전자결제(PG) 신청</a>
        </div>
        <div class="linepd30"></div>
    <?php }?>
</form>
