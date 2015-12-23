<?php

namespace Xsanisty\Gantt\Repository;

use Xsanisty\Gantt\Model\Task;
use Xsanisty\Gantt\Model\Chart;
use Xsanisty\Gantt\Contract\TaskRepositoryInterface;

class TaskRepository implements TaskRepositoryInterface
{
    protected $task;

    public function __construct(Task $task)
    {
        $this->task = $task;
    }

    /**
     * {@inheritdoc}
     */
    public function findById($id)
    {
        return $this->task->find($id);
    }

    /**
     * {@inheritdoc}
     */
    public function findByChart(Chart $chart)
    {
        return $chart->tasks;
    }

    /**
     * {@inheritdoc}
     */
    public function create(array $task)
    {
        return $this->task->create($task);
    }

    /**
     * {@inheritdoc}
     */
    public function update($id, array $data)
    {
        return $this->task->newQuery()->where('id', '=', $id)->update($data);
    }

    /**
     * {@inheritdoc}
     */
    public function delete($id)
    {
        return $this->task->newQuery()->where('id', '=', $id)->delete();
    }
}
