function admin_list_checkall(checkall, list)
{
    if (checkall.checked)
        $(list + ":checkbox:not(:checked)").attr("checked", "checked");
    else
        $(list + ":checkbox:checked").removeAttr("checked");
}
function admin_list_checkrow(check)
{
    if ($(check).is(":checked"))
        $(check).removeAttr("checked");
    else
        $(check).attr("checked","checked");
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
