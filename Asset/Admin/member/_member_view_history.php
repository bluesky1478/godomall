<form id="frmHistory" method="get">
    <input type="hidden" name="pageNum"/>

    <div class="table-title">
        회원정보 수정이력
    </div>
    <div class="table-header">
        <div class="pull-right">
            <div class="form-inline">
                <?= gd_select_box_by_page_view_count(Request::get()->get('pageNum', 10), null, null, ''); ?>
            </div>
        </div>
    </div>
    <table class="table table-rows">
        <colgroup>
            <col class="width-xs"/>
            <col class="width-md"/>
            <col class="width-sm"/>
            <col class="width-sm"/>
            <col class="width-md"/>
            <col/>
        </colgroup>
        <thead>
        <tr>
            <th>번호</th>
            <th>변경일자</th>
            <th>처리자</th>
            <th>IP주소</th>
            <th>변경항목</th>
            <th>변경내용</th>
        </tr>
        </thead>
        <tbody>
        </tbody>
    </table>
    <div class="center" id="div-pagination"></div>
</form>
<script type="text/template" id="history-row-template2">
    <% _.each(data, function(item){ %>
    <% if(item.updateColumn !== '최종구매일') { %>
    <tr class="center">
        <td class="font-num"><%- item.idx %></td>
        <td class="font-date"><%- item.regDt %></td>
        <td><%- item.processor %><%- item.deleteText %></td>
        <td><%- item.processorIp %></td>
        <td><%- item.updateColumn %></td>
        <% if(item.updateColumn === '비밀번호') { %>
        <td><%- item.afterValue %></td>
        <% } else { %>
        <td><%- item.beforeValue %>&nbsp;&gt;&nbsp;<%- item.afterValue %><% if (typeof(item.displayOtherValue) === 'string') { %><%- item.displayOtherValue %><% } %></br>
            <% if(item.otherValue != 'null' && typeof(item.otherValue) === 'string' && item.processor == 'member') { %><%- item.otherValue %><% } %>
            <% if(item.memberLifeEventValue != 'null' && typeof(item.memberLifeEventValue) === 'string' && item.updateColumn == '개인정보유효기간' && item.beforeValue !== '999' && item.afterValue === '999') { %><%- item.memberLifeEventValue %><% } %>
        </td>
        <% } %>
    </tr>
    <% } %>
    <%});%>
</script>
<script id="history-pagination-template2" type="text/template">
    <nav>
        <ul class="pagination pagination-sm">
            <% for(var i = page.start; i < page.end; i++) { %> <% if(i === (page.now *1)){ %>
            <li class="active">
                <span><%- i %></span>
            </li>
            <% } else { %>
            <li><a href="#" class="page_navi_number"><%- i %></a></li>
            <% }} %>
        </ul>
    </nav>
</script>
