$('.sidebar-toggle').on('click', function()
{
    setTimeout(function(){
        gantt.render();
    }, 500);
});

$('#btn-refresh').click(function(){ gantt.load(global.ganttApi+'/task/'); });
$('#btn-export-png').click(function(){ gantt.exportToPNG(); });
$('#btn-export-pdf').click(function(){ gantt.exportToPDF(); });
$('#btn-invite').click(function(e){
    e.preventDefault();

    $('#invite-modal').modal('show');
});
$('#btn-bookmark').click(function(e){
    e.preventDefault();

    var chartId = $(this).attr('data-id');
    $.ajax({
        url : global.bookmarkUrl,
        method : 'POST',
        dataType : 'json',
        data : {chart_id : chartId},
        success : function(resp){
            if (resp.success) {
                alert('Chart bookmarked!');
            } else {
                alert('Error occured while bookmarking chart!');
            }
        },
        error : function(resp){
            alert('Error occured while bookmarking chart!');
        }
    });
});
$('#btn-edit').click(function(e){
    e.preventDefault();
    var chartInfo = global.chartInfo;

    $('#chart-form')[0].reset();

    for(var a in chartInfo){
        if(a != 'settings') {
            $('#'+a).val(chartInfo[a]);
        } else {
            var settings = chartInfo[a];

            for(var s in settings.columns){
                $('#settings_columns_'+settings.columns[s].name+'_enabled').iCheck(settings.columns[s].enabled == 1 ? 'check' : 'uncheck');
                $('#settings_columns_'+settings.columns[s].name+'_label').val(settings.columns[s].label);
                $('#settings_columns_'+settings.columns[s].name+'_align').val(settings.columns[s].align);
                $('#settings_columns_'+settings.columns[s].name+'_width').val(settings.columns[s].width);
            }
        }
    }

    $('#chart-modal').modal('show');
});

$('#btn-chart-save').click(function(e){
    e.preventDefault();

    var formId = '#chart-form';
    var btn = $(this);

    btn.prop('disabled', true).text('Saving...');

    $.ajax({
        'method' : 'PUT',
        'data' : $(formId).serialize(),
        'url' : $(formId).attr('action') + $('#id').val(),
        'success' : function(resp){
            alert(resp.content.message);
            global.chartInfo = resp.content.chart;
            gantt.config.columns = resp.content.chart.settings.columns;
            gantt.config.columns.push({name:"add", label:"", width:44 });
            gantt.init("gantt-container");
            gantt.load(global.ganttApi+'/task/');
            btn.prop('disabled', false).text('Save');

            if(resp.content.chart.visibility == 'private'){
                $('#btn-public').hide();
            } else {
                $('#btn-public').show();
            }

            $('#chart-modal').modal('hide');
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

$('#invite-user-email').select2({
    tags: true,
    tokenSeparators: [',']
});

$('#btn-chart-invite').click(function(e){
    e.preventDefault();
});

$('input[type=checkbox]').iCheck({
    checkboxClass: 'icheckbox_square-blue',
    radioClass: 'iradio_square-blue',
    increaseArea: '10%' // optional
});

/** gantt configuration */
global.chartInfo.settings.columns.push({name:"add", label:"", width:44 });
gantt.config.grid_width = 0;

for( var conf in global.chartInfo.settings.columns) {
    gantt.config.grid_width += parseFloat(global.chartInfo.settings.columns[conf].width);
}

gantt.config.columns  = global.chartInfo.settings.columns;
gantt.config.xml_date = "%Y-%m-%d %H:%i:%s";

/** gantt hook template */
gantt.templates.task_text = function(start, end, task){
    if (!task.progress) {
        task.progress = 0;
    }
    return task.text+ ' [' +Math.round(task.progress*100)+ '%]';
};
gantt.attachEvent("onTaskClosed", function(id){
    $.ajax({
        'method' : 'PUT',
        'url'    : global.ganttApi+'/task/'+id,
        'data'   : {'open' : 0}
    });
    return true;
});
gantt.attachEvent("onTaskOpened", function(id){
    $.ajax({
        'method' : 'PUT',
        'url'    : global.ganttApi+'/task/'+id,
        'data'   : {'open' : 1}
    });
    return true;
});

gantt.init("gantt-container");
gantt.load(global.ganttApi+'/task/');

var dp = new gantt.dataProcessor(global.ganttApi);
dp.init(gantt);
dp.setTransactionMode("REST");