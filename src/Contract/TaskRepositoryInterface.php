<?php

namespace Xsanisty\Gantt\Contract;

use Xsanisty\Gantt\Model\Chart;

interface TaskRepositoryInterface
{
    /**
     * Find specific task by given id.
     *
     * @param  int      $id     The task id
     *
     * @return Xsanisty\Gantt\Model\Task
     */
    public function findById($id);

    /**
     * Find task belong to specific chart.
     *
     * @param  Chart  $cart     The chart model
     *
     * @return Xsanisty\Gantt\Model\Task[]
     */
    public function findByChart(Chart $cart);

    /**
     * Recalculate parent task progress if child task is updated.
     *
     * @param  int  $id     The child task id
     *
     * @return void
     */
    public function reCalculateProgress($id);

    /**
     * Create new task object in database.
     *
     * @param  array  $task     The task data
     *
     * @return Xsanisty\Gantt\Model\Task
     */
    public function create(array $task);

    /**
     * Update existing task data in database.
     *
     * @param  int      $id     The task id
     * @param  array    $data   The task data
     *
     * @return Xsanisty\Gantt\Model\Task
     */
    public function update($id, array $data);

    /**
     * Delete existing task data in database.
     *
     * @param  int  $id     The task id
     *
     * @return void
     */
    public function delete($id);
}
