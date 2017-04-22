/**
 * Defines global variables.
 *
 * Assigned while jQuery is ready:
 *
 * ```
 * jQuery(document).ready(function () {
 *     ns = $('ol.sortable').nestedSortable({"isTree":true, ... ,"update":update}); ///update is the allback function of nestedSortable
 *     treeView = $('#tree-view');
 *     loading = $('#loading');
 * });
 * ```
 */
var treeView;
var loading;
var ns;

/**
 * Callback functions of nestedSortable.
 */
function update(e, obj) {
    var url = obj.item.data('action-url');
    var parent = obj.item.parents('li');
    var prev = obj.item.prev();
    var next = obj.item.next();
    var data = {
        parent_id: parent.length ? parent.data('node-id') : 0,
        prev_id: prev.length ? prev.data('node-id') : 0,
        next_id: next.length ? next.data('node-id') : 0
    };

    ///Deprecation Notice: The jqXHR.success(), jqXHR.error(), 
    ///and jqXHR.complete() callbacks are removed as of jQuery 3.0. 
    ///You can use jqXHR.done(), jqXHR.fail(), and jqXHR.always() instead.
    ///@see http://api.jquery.com/jQuery.ajax
    jQuery.ajax({
        url: url,
        method: 'post',
        data: data,
        beforeSend: function(jqXHR, settings) {
            treeView.css({opacity: .4});
            loading.fadeIn();
            ns.nestedSortable('disable');
        },
        success: function(data, textStatus, jqXHR) {
            if (data.status == 'error') {
                ns.nestedSortable('cancel');    ///restore orignal sort node list if error
                alert(data.error);
            }
        },
        error: function(jqXHR, textStatus, errorThrown) {
            ns.nestedSortable('cancel');    ///restore orignal sort node list if error

            ///@see http://stackoverflow.com/questions/6792878/jquery-ajax-error-function
            var msg = '';
            if (jqXHR.status === 0) {
                msg = 'Not connect.\n Verify Network.';
            } else if (jqXHR.status == 404) {
                msg = 'Requested page not found. [404]';
            } else if (jqXHR.status == 500) {
                msg = 'Internal Server Error [500].';
            } else if (exception === 'parsererror') {
                msg = 'Requested JSON parse failed.';
            } else if (exception === 'timeout') {
                msg = 'Time out error.';
            } else if (exception === 'abort') {
                msg = 'Ajax request aborted.';
            } else {
                msg = 'Uncaught Error.\n' + jqXHR.responseText;
            }
            alert(msg);
        },
        complete: function(jqXHR, textStatus) {
            ns.nestedSortable('enable');
            loading.fadeOut();
            treeView.css({opacity: 1});
        },
    });
};

/**
 * Event handers.
 */
jQuery(function () {
    ///Click event hander of `disclose` that show/hide children
    $('.disclose').attr('title', 'Click to show/hide children');
    $('.disclose').on('click', function() {
        $(this).closest('li').toggleClass('mjs-nestedSortable-collapsed').toggleClass('mjs-nestedSortable-expanded');
    });

    ///Click event handers of `collapse-all`, `expand-all`
    $('#collapse-all').attr('title', 'Click to collapse all nodes');
    $('#collapse-all').on('click', function() {
        $('.mjs-nestedSortable-branch').addClass('mjs-nestedSortable-collapsed').removeClass('mjs-nestedSortable-expanded');
        $(this).hide();
        $("#expand-all").show();
    });
    $('#expand-all').attr('title', 'Click to expand all nodes');
    $('#expand-all').on('click', function() {
        $('.mjs-nestedSortable-branch').addClass('mjs-nestedSortable-expanded').removeClass('mjs-nestedSortable-collapsed');
        $(this).hide();
        $("#collapse-all").show();
    });
});
