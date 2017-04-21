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
    treeView.css({opacity: .4});
    loading.fadeIn();
    ns.nestedSortable('disable');

    var url = obj.item.data('action-url');
    var parent = obj.item.parents('li');
    var before = obj.item.prev();
    var data = {
        parent_id: parent.length ? parent.data('node-id') : 0,
        before_id: before.length ? before.data('node-id') : 0
    };

    jQuery.ajax({
        url: url,
        method: 'post',
        data: data,
        success: function () {
            ns.nestedSortable('enable');
            loading.fadeOut();
            treeView.css({opacity: 1});
        },
        error: function () {
            ns.nestedSortable('cancel');    ///restore orignal sort node list if error
            ns.nestedSortable('enable');
            loading.fadeOut();
            treeView.css({opacity: 1});
        }
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
