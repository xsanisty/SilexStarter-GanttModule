<?php

namespace Xsanisty\Gantt\Contract;

use Xsanisty\Gantt\Model\Link;
use Xsanisty\Gantt\Model\Task;
use Xsanisty\Gantt\Model\Chart;

interface LinkRepositoryInterface
{
    /**
     * Find link belong to specific chart.
     *
     * @param  Chart  $cart     The chart model
     *
     * @return Xsanisty\Gantt\Model\Link[]
     */
    public function findByChart(Chart $chart);

    /**
     * Create new link object in database.
     *
     * @param  array  $data     The link data
     *
     * @return Xsanisty\Gantt\Model\Link
     */
    public function create(array $data);

    /**
     * Delete existing task data in database.
     *
     * @param  int  $id     The task id
     *
     * @return void
     */
    public function delete($id);
}
