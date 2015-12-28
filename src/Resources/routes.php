<?php

Route::group(
    Config::get('@silexstarter-dashboard.config.admin_prefix') . '/gantt',
    function () {
        /** route to chart */
        Route::resource(
            '/chart',
            'ChartController',
            [
                'as'        => 'gantt.chart',
                'permission'=> 'gantt.chart',
                'except'    => ['page', 'create']
            ]
        );
        Route::post(
            '/chart/datatable',
            'ChartController:datatable',
            [
                'as' => 'gantt.chart.datatable',
                'permission' => 'gantt.chart.read'
            ]
        );
        /** route to task */
        Route::resource(
            '/chart/{chart_id}/task',
            'TaskController',
            [
                'as'        => 'gantt.task',
                'permission'=> 'gantt.task',
                'assert'    => ['chart_id' => '\d+'],
                'only'      => ['index', 'show', 'update', 'delete']
            ]
        );
        Route::post(
            '/chart/{chart_id}/task',
            'TaskController:store',
            [
                'as'        => 'gantt.task.store',
                'permission'=> 'gantt.task.create',
                'assert'    => ['chart_id' => '\d+']
            ]
        );
        /** route to link */
        Route::delete(
            '/chart/{chart_id}/link/{id}',
            'LinkController:delete',
            [
                'as'        => 'gantt.link.delete',
                'permission'=> 'gantt.link.delete',
                'assert'    => ['chart_id' => '\d+', 'id' => '\d+']
            ]
        );
        Route::post(
            '/chart/{chart_id}/link',
            'LinkController:store',
            [
                'as'        => 'gantt.link.store',
                'permission'=> 'gantt.link.create',
                'assert'    => ['chart_id' => '\d+']
            ]
        );

        /** route to bookmark */
        Route::post(
            '/bookmark',
            'BookmarkController:store',
            [
                'as' => 'gantt.bookmark.create'
            ]
        );

        Route::post(
            '/bookmark/datatable',
            'BookmarkController:datatable',
            [
                'as' => 'gantt.bookmark.datatable'
            ]
        );

        Route::get(
            '/bookmark',
            'BookmarkController:index',
            [
                'as' => 'gantt.bookmark.index'
            ]
        );

        Route::delete(
            '/bookmark/{id}',
            'BookmarkController:delete',
            [
                'as'    => 'gantt.bookmark.delete',
                'assert'=> ['id' => '\d+']
            ]
        );
    },
    [
        'namespace' => 'Xsanisty\Gantt\Controller',
        'before'    => 'admin.auth',
    ]
);

/** for publicly accessible chart */
Route::get(
    'gantt/{chart_id}',
    'Xsanisty\Gantt\Controller\ChartController:publicChart',
    [
        'as'        => 'gantt.chart.public',
        'convert'   => [
            'chart_id' => function ($chart_id) use ($app) {
                $ids    = $app['hashids']->decode($chart_id);
                $id     = isset($ids[0]) ? $ids[0] : 0;

                return ['id' => $id, 'hash' => $chart_id];
            }
        ]
    ]
);

Route::get(
    'gantt/{chart_id}/task/',
    'Xsanisty\Gantt\Controller\TaskController:publicTask',
    [
        'as'        => 'gantt.task.public',
        'convert'   => [
            'chart_id' => function ($chart_id) use ($app) {
                $ids    = $app['hashids']->decode($chart_id);
                $id     = isset($ids[0]) ? $ids[0] : 0;

                return ['id' => $id, 'hash' => $chart_id];
            }
        ]
    ]
);
