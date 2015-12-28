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

    $('#chart-modal').modal('show');
});

$('#invite-user-email').select2({
    tags: true,
    tokenSeparators: [',']
});

$('input[type=checkbox]').iCheck({
    checkboxClass: 'icheckbox_square-blue',
    radioClass: 'iradio_square-blue',
    increaseArea: '10%' // optional
});

/** gantt configuration */
global.ganttSettings.columns.push({name:"add", label:"", width:44 });
gantt.config.grid_width = 0;

for( var conf in global.ganttSettings.columns) {
    gantt.config.grid_width += parseFloat(global.ganttSettings.columns[conf].width);
}

gantt.config.columns  = global.ganttSettings.columns;
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