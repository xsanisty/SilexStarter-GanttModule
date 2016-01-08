function setScale(scale) {
    switch(scale) {
        case "hour":
            gantt.config.scale_unit = "day";
            gantt.config.step = 1;
            gantt.config.date_scale = "%d %M";
            gantt.config.subscales = [
                {unit:"hour", step:1, date:"%G:00" }
            ];
            gantt.config.min_column_width = 40;
            gantt.config.scale_height = 50;
            gantt.templates.date_scale = null;
            break;
        case "day":
            gantt.config.scale_unit = "day";
            gantt.config.step = 1;
            gantt.config.date_scale = "%d %M";
            gantt.config.subscales = [];
            gantt.config.scale_height = 27;
            gantt.templates.date_scale = null;
            break;
        case "week":
            var weekScaleTemplate = function(date){
                var dateToStr = gantt.date.date_to_str("%d %M");
                var endDate = gantt.date.add(gantt.date.add(date, 1, "week"), -1, "day");
                return dateToStr(date) + " - " + dateToStr(endDate);
            };

            gantt.config.scale_unit = "week";
            gantt.config.min_column_width = 40;
            gantt.config.step = 1;
            gantt.templates.date_scale = weekScaleTemplate;
            gantt.config.subscales = [
                {unit:"day", step:1, date:"%D" }
            ];
            gantt.config.scale_height = 50;
            break;
        case "month":
            gantt.config.scale_unit = "month";
            gantt.config.date_scale = "%F, %Y";
            gantt.config.scale_height = 50;
            gantt.config.min_column_width = 20;

            gantt.config.subscales = [
                {unit:"day", step:1, date:"%j" }
            ];
            break;
        case "quarter":
            gantt.config.scale_unit = "year";
            gantt.config.step = 1;
            gantt.config.date_scale = "%Y";
            gantt.config.min_column_width = 50;

            gantt.config.scale_height = 90;

            function quarterLabel(date){
                var month = date.getMonth();
                var q_num;

                if(month >= 9){
                    q_num = 4;
                }else if(month >= 6){
                    q_num = 3;
                }else if(month >= 3){
                    q_num = 2;
                }else{
                    q_num = 1;
                }

                return "Q" + q_num;
            }

            gantt.config.subscales = [
                {unit:"quarter", step:1, template:quarterLabel},
                {unit:"month", step:1, date:"%M" }
            ];
            break;
        case "year":
            gantt.config.scale_unit = "year";
            gantt.config.step = 1;
            gantt.config.date_scale = "%Y";
            gantt.config.min_column_width = 50;

            gantt.config.scale_height = 70;
            gantt.templates.date_scale = null;


            gantt.config.subscales = [
                {unit:"month", step:1, date:"%M" }
            ];
            break;
        default:
            break;
    }
}

$('.sidebar-toggle').on('click', function()
{
    setTimeout(function(){
        gantt.render();
    }, 500);
});

$('#columns_setting').sortable();
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
    var $list  = $('#columns_setting');
    $list.find('input[type=checkbox]').iCheck('destroy');

    for(var a in chartInfo){
        if(a != 'settings') {
            console.log(a);
            $('#'+a).val(chartInfo[a]);
        } else {
            var settings = chartInfo[a];

            for(var s in settings.columns){
                $('#settings_columns_'+settings.columns[s].name+'_enabled').parents('.form-group').attr('data-order', s);
                $('#settings_columns_'+settings.columns[s].name+'_enabled').prop('checked', settings.columns[s].enabled == 1);
                $('#settings_columns_'+settings.columns[s].name+'_label').val(settings.columns[s].label);
                $('#settings_columns_'+settings.columns[s].name+'_align').val(settings.columns[s].align);
                $('#settings_columns_'+settings.columns[s].name+'_width').val(settings.columns[s].width);
            }

            $('#settings_scale').val(settings.scale);
        }
    }
    var $items = $list.find('.form-group').sort(function (a, b) {
        return $(a).attr('data-order') - $(b).attr('data-order');
    });

    $list.html('');
    $list.append($items);

    $list.find('input[type=checkbox]').iCheck({
        checkboxClass: 'icheckbox_square-blue',
        radioClass: 'iradio_square-blue',
        increaseArea: '10%'
    });

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
            setScale(global.chartInfo.settings.scale);


            gantt.render();
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
    ajax: {
        url: global.searchUserUrl,
        dataType: 'json',
        delay: 250,
        data: function (params) {
            return {
                keyword : params.term,
                page : params.page
            };
        },
        processResults: function (data, params) {
            params.page = params.page || 1;

            return {
                results: data.items,
                pagination: {
                    more: (params.page * 30) < data.total_count
                }
            };
        },
        cache: true
    },
});

$('#btn-chart-invite').click(function(e){
    e.preventDefault();
});

$('input[type=checkbox]').iCheck({
    checkboxClass: 'icheckbox_square-blue',
    radioClass: 'iradio_square-blue',
    increaseArea: '10%'
});

/** gantt configuration */
global.chartInfo.settings.columns.push({name:"add", label:"", width:44 });
gantt.config.grid_width = 0;
gantt.config.order_branch = true;
gantt.config.order_branch_free = true;

for( var conf in global.chartInfo.settings.columns) {
    gantt.config.grid_width += parseFloat(global.chartInfo.settings.columns[conf].width);

    if (global.chartInfo.settings.columns[conf].name == 'progress') {
        global.chartInfo.settings.columns[conf].template = function(task) {
            var progress = parseFloat(task.progress)*100;
            return progress.toFixed(0) + '%';
        }
    }
}

setScale(global.chartInfo.settings.scale);

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

$(window).resize(function () {
    var wrapperHeight = $(window).height()
                        - parseFloat($('.content-header').height())
                        - parseFloat($('.main-header').height());
    $('#gantt-container').css('height', wrapperHeight - 120);

    gantt.render();
}).resize();
