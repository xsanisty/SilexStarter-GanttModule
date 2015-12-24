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
        $task           = $this->task->newQuery()->find($id);
        $lastProgress   = $task->progress;

        $task->update($data);

        if ($lastProgress != $data['progress']) {
            echo 'calculating progress';
            $this->reCalculateProgress($id);
        }
    }

    /**
     * {@inheritdoc}
     */
    public function delete($id)
    {
        return $this->task->newQuery()->where('id', '=', $id)->delete();
    }

    /**
     * {@inheritdoc}
     */
    public function reCalculateProgress($id)
    {
        $task       = $this->task->newQuery()->with('parentTask')->find($id);
        $parent     = $task->parentTask;

        if ($parent) {
            $siblings   = $parent->subTasks()->select(['progress', 'duration'])->get();
            $duration   = 0;
            $progress   = 0;

            foreach ($siblings as $sibling) {
                $duration  += $sibling->duration;
                $progress  += $sibling->progress * $sibling->duration;
            }

            $parent->update(['progress' => $progress / $duration]);
            $this->reCalculateProgress($parent->id);
        }
    }
}
