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
function confirmarUrl(text, url)
{
    var band = confirm(text);
    if (band) {
        window.location = url;
    }
}
