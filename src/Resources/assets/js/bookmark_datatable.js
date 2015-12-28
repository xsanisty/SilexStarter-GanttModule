$(document).ready(function(){
    var domSetting  = global.admin_template == 'RDash'
                    ? "<'row'<'col-md-12'<'widget'<'widget-title'<'row'<'col-md-5'<'#icon-wrapper'>><'col-md-3 hidden-xs'l><'col-md-4 hidden-xs'f>><'clearfix'>><'widget-body flow no-padding'tr><'widget-title'<'col-sm-5'i><'col-sm-7'p><'clearfix'>>>>"
                    : "<'row'<'col-md-12'<'box box-primary'<'box-header'<'row'<'col-md-6'<'#icon-wrapper'>><'col-md-3 hidden-xs'l><'col-md-3 hidden-xs'f>><> <'box-body no-padding'tr><'box-footer clearfix'<'col-sm-5'i><'col-sm-7'p>>>>>";
    var $datatable = $('#bookmark-table').DataTable({
        "processing": true,
        "serverSide": true,
        "responsive": true,
        "dom" : domSetting,
        "columnDefs": [
            {
                "targets": -1,
                "className": 'text-center',
                "orderable": false,
                "searchable": false
            }
        ],
        "language": {
            "paginate": {
                "previous": "&laquo;",
                "next": "&raquo;"
            }
        },
        "ajax": {
            "url": global.datatableUrl,
            "type": "POST",
            "error": function(resp){
                if(resp.status == 401){
                    alert('Your session is expired!\n\nYou will be redirected to the login page shortly.');
                    window.location.reload();
                }
            }
        }
    }).on('click', '.btn-delete', function(e){
        e.preventDefault();

        if(confirm('Are you sure want to delete this bookmark?')){
            var btn = $(this);
            var url = btn.attr('href');

            btn.prop('disabled', true).text('deleting...');

            $.ajax({
                'method' : 'DELETE',
                'url' : url,
                'success' : function(){
                    $datatable.ajax.reload(null, false);
                },
                'error' : function(resp){

                    if(resp.status == 401){
                        alert('Your session is expired!\n\nYou will be redirected to the login page shortly.');
                        window.location.reload();
                    } else {
                        alert('Error occured when deleting bookmark');
                        btn.prop('disabled', false).text('delete');
                    }
                }
            });
        }
    });
});