var convertLinkAjax = function (){
    return {
        init: function (links){
            $(links).each(function (i, item){
                var zone = $(item).data('ajax-zone-update');
                var indicator = $(item).data('ajax-indicator');
                if (!zone) {
                    return;
                }
                var url = $(item).attr('href');
                $(item).attr('href', '#');
                $(item).click(function (e){
                    e.preventDefault();
                    jQuery.ajax({
                        type:'GET',
                        dataType:'html',
                        url: url,
                        success: function(data, textStatus){
                            jQuery(zone).html(data);
                        },
                        beforeSend: function(XMLHttpRequest){
                            jQuery(indicator).show();
                        },
                        complete: function(XMLHttpRequest, textStatus){
                            jQuery(indicator).hide();
                        }
                    });
                });
            });
        }
    };
}();

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

var confirmModal = function (){
    'use strict';
    return {
        init: function (){
            var url_confirms   = '[data-toggle=url-confirm]';
            $(url_confirms).each(function (i, item){
                var title = $(item).data('header');
                if (!title) {
                    title = $(item).attr('title');
                }
                var body = $(item).data('body');
                var url = $(item).attr('href');
                $(item).attr('url', '#');
                $(item).click(function (e){
                    e.preventDefault();
                    var html = '';
                    html += '<div id="md-default" class="modal fade" role="dialog" tabindex="-1" style="display: none;" aria-hidden="true">';
                    html += '   <div class="modal-dialog">';
                    html += '       <div class="modal-content">';
                    html += '           <div class="modal-header">';
                    html += '               <button class="close" aria-hidden="true" data-dismiss="modal" type="button ">×</button>';
                    html += '           </div>';
                    html += '           <div class="modal-body">';
                    html += '               <div class="text-center">';
                    html += '                   <h4>'+title+'</h4>';
                    html += '                   <p>'+body+'</p>';
                    html += '               </div>';
                    html += '           </div>';
                    html += '           <div class="modal-footer">';
                    html += '               <button class="btn btn-default btn-flat" data-dismiss="modal" type="button">Cancel</button>';
                    html += '               <a class="btn btn-primary btn-flat" href="'+url+'">Proceed</a>';
                    html += '           </div>';
                    html += '       </div>';
                    html += '   </div>';
                    html += '</div>';
                    $(html).modal();
                });
            });
        }
    };
}();