<?php

namespace Xsanisty\Gantt\Controller;

use Exception;
use Carbon\Carbon;
use Cartalyst\Sentry\Users\UserInterface;
use Xsanisty\Gantt\Model\Chart;
use Xsanisty\Gantt\Contract\TaskRepositoryInterface;
use Xsanisty\Gantt\Contract\LinkRepositoryInterface;
use Xsanisty\Gantt\Contract\ChartRepositoryInterface;

class TaskController
{
    protected $user;
    protected $task;
    protected $link;
    protected $chart;

    public function __construct(
        ChartRepositoryInterface $chart,
        TaskRepositoryInterface $task,
        LinkRepositoryInterface $link,
        UserInterface $user = null
    ) {
        $this->user = $user;
        $this->task = $task;
        $this->link = $link;
        $this->chart= $chart;
    }

    public function index($chart_id)
    {
        $chart = $this->chart->findByIdIfAllowed($chart_id, $this->user);

        if (Request::ajax()) {
            if (!$chart) {
                return Response::ajax('Chart or Tasks not found', 404);
            }

            return Response::json(
                [
                    'data'  => $this->task->findByChart($chart),
                    'links' => $this->link->findByChart($chart)
                ]
            );
        }
    }

    public function show($id)
    {
        return Response::json($this->task->reCalculateProgress($id));
    }

    public function publicTask($chart_id)
    {
        $chart = $this->chart->findById($chart_id['id']);

        if (Request::ajax()) {
            if (!$chart) {
                return Response::ajax('Chart or Tasks not found', 404);
            }

            return Response::json(
                [
                    'data'  => $this->task->findByChart($chart),
                    'links' => $this->link->findByChart($chart)
                ]
            );
        }
    }

    public function store($chart_id)
    {
        $chart = $this->chart->findByIdIfAllowed($chart_id, $this->user);

        if (Request::ajax()) {
            if (!$chart) {
                return Response::ajax('Chart or Tasks not found', 404);
            }

            $task  = Request::get();
            $task['chart_id']   = $chart->id;
            $task['parent']     = $task['parent'] == 0 ? null : $task['parent'];

            $task = $this->task->create($task);

            return Response::json(
                [
                    'tid'   => $task->id,
                    'status'=> 'inserted',
                    'data'  => $task
                ]
            );
        }
    }

    public function update($chart_id, $id)
    {
        $chart = $this->chart->findByIdIfAllowed($chart_id, $this->user);

        if (Request::ajax()) {
            if (!$chart) {
                return Response::ajax('Chart or Tasks not found', 404);
            }

            $permissions = count($chart->members)
                        ? (array) json_decode($chart->members[0]->pivot->permissions)
                        : ['gantt.task.delete' => 0];

            /* if is author, or user has permission to edit task */
            if ($this->user->hasAccess('admin') || $chart->author_id == $this->user->getId() || $permissions['gantt.task.edit'] == 1) {
                $task = Request::except('id');

                if (isset($task['parent'])) {
                    $task['parent'] = $task['parent'] == 0 ? null : $task['parent'];
                }

                if (count($task) > 1 && isset($task['open'])) {
                    unset($task['open']);
                }

                try {
                    $task = $this->task->update($id, $task);

                    return Response::json(
                        [
                            'tid'   => $id,
                            'sid'   => $id,
                            'action'=> 'updated'
                        ]
                    );
                } catch (Exception $e) {
                    return Response::json(
                        [
                            'tid'   => $id,
                            'sid'   => $id,
                            'action'=> 'error',
                            'data'  => $e->getMessage()
                        ]
                    );
                }
            }

            return Response::json(
                [
                    'tid'   => $id,
                    'action'=> 'invalid',
                    'data'  => 'Insufficient permissions to delete the task'
                ]
            );
        }
    }

    public function delete($chart_id, $id)
    {
        $chart = $this->chart->findByIdIfAllowed($chart_id, $this->user);

        if (Request::ajax()) {
            if (!$chart) {
                return Response::ajax('Chart or Tasks not found', 404);
            }

            $permissions = count($chart->members)
                        ? (array) json_decode($chart->members[0]->pivot->permissions)
                        : ['gantt.task.delete' => 0];

            /* if is author, or user has permission to delete task */
            if ($chart->author_id == $this->user->getId() || $permissions['gantt.task.delete'] == 1) {
                $deleted = $this->task->delete($id);

                return Response::json(
                    [
                        'tid'   => $id,
                        'sid'   => $id,
                        'action'=> $deleted ? 'deleted' : 'error',
                        'status'=> $deleted ? 'deleted' : 'error'
                    ]
                );
            }

            return Response::json(
                [
                    'tid'   => $id,
                    'action'=> 'invalid',
                    'data'  => 'Insufficient permissions to delete the task'
                ]
            );
        }
    }

    protected function getChartPermissions(Chart $chart)
    {

    }
}
