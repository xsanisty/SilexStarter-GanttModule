<?php

namespace Xsanisty\Gantt\Contract;

use Xsanisty\Gantt\Model\Link;
use Xsanisty\Gantt\Model\Task;
use Xsanisty\Gantt\Model\Chart;

interface LinkRepositoryInterface
{
    public function findByChart(Chart $chart);

    public function create(array $data);

    public function delete($id);
}
