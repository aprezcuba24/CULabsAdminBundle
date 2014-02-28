function admin_list_checkall(checkall, list)
{
    if (checkall.checked) {
        $(list + '[type=checkbox]').each(function (i, obj) {

            if (!$(obj).is(":checked")) {
                $(obj).click();
            }
        });
    } else {

        $(list + '[type=checkbox]').each(function (i, obj) {

            if ($(obj).is(":checked")) {
                $(obj).click();
            }
        });
    }
}
function redirect(url)
{
    window.location = url;
}
function confirmarUrl(text, url, header)
{
    var html = '';
    
    html += '<div class="modal hide" >';
    html += '  <div class="modal-header">';
    html += '    <button type="button" class="close" data-dismiss="modal" aria-hidden="true">×</button>';
    html += '    <h3 id="myModalLabel">'+header+'</h3>';
    html += '  </div>';
    html += '  <div class="modal-body">';
    html += '    <p>'+text+'</p>';
    html += '  </div>';
    html += '  <div class="modal-footer">';
    html += '    <a href="'+url+'" class="btn btn-primary">Si</a>';
    html += '    <a href="#" data-dismiss="modal" aria-hidden="true" class="btn">No</a>';
    html += '  </div>';
    html += '</div>';
    $(html).modal();
}
