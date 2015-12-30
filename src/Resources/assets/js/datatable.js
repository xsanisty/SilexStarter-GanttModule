$(document).ready(function(){
    var domSetting  = global.admin_template == 'RDash'
                    ? "<'row'<'col-md-12'<'widget'<'widget-title'<'row'<'col-md-5'<'#icon-wrapper'>><'col-md-3 hidden-xs'l><'col-md-4 hidden-xs'f>><'clearfix'>><'widget-body flow no-padding'tr><'widget-title'<'col-sm-5'i><'col-sm-7'p><'clearfix'>>>>"
                    : "<'row'<'col-md-12'<'box box-primary'<'box-header'<'row'<'col-md-6'<'#icon-wrapper'>><'col-md-3 hidden-xs'l><'col-md-3 hidden-xs'f>><> <'box-body no-padding'tr><'box-footer clearfix'<'col-sm-5'i><'col-sm-7'p>>>>>";
    var $datatable = $('#gantt-table').DataTable({
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
            "url": global.ganttDatatableUrl,
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

        if(confirm('Are you sure want to delete this chart?')){
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
                        alert('Error occured when deleting data');
                        btn.prop('disabled', false).text('delete');
                    }
                }
            });
        }
    });

    var $settingCache = $('#columns_setting').html();
    $('#columns_setting').sortable();

    $('#btn-chart-create')
    .appendTo('#icon-wrapper')
    .click(function(e){
        e.preventDefault();

        $('#chart-form')[0].reset();
        $('#columns_setting').html($settingCache);
        $('input[type=checkbox]').iCheck({
            checkboxClass: 'icheckbox_square-blue',
            radioClass: 'iradio_square-blue',
            increaseArea: '20%' // optional
        });
        $('#chart-modal').modal('show');
    });

    $('#btn-chart-save').click(function(e){
        e.preventDefault();

        var formId = '#chart-form';
        var btn = $(this);

        btn.prop('disabled', true).text('Saving...');

        $.ajax({
            'method' : 'POST',
            'data' : $(formId).serialize(),
            'url' : $('#_method').val() == 'PUT' ? $(formId).attr('action') + $('#id').val() : $(formId).attr('action'),
            'success' : function(resp){
                alert(resp.content);

                btn.prop('disabled', false).text('Save');

                $('#chart-modal').modal('hide');
                $datatable.ajax.reload(null, false);
            },
            'error': function(resp){
                btn.prop('disabled', false).text('Save');

                if(resp.status == 401){
                    alert('Your session is expired!\n\nYou will be redirected to the login page shortly.');
                    window.location.reload();
                } else {
                    alert(resp.responseJSON.content + '\n\nDetailed error:\n' + resp.responseJSON.errors[0].message);
                }
            }
        });
    });

    $('input[type=checkbox]').iCheck({
        checkboxClass: 'icheckbox_square-blue',
        radioClass: 'iradio_square-blue',
        increaseArea: '20%' // optional
    });
});