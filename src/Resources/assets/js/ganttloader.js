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
$('#btn-bookmark').click(function(){});
$('#btn-edit').click(function(e){
    e.preventDefault();

    $('#chart-modal').modal('show');
});

$('#invite-user-email').select2({
    tags: true,
    tokenSeparators: [',']
})

gantt.config.columns = [
    {name:"text", label:"Task name", width:200, tree:true },
    {name:"add", label:"", width:44 }
];
gantt.config.xml_date = "%Y-%m-%d %H:%i:%s";

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