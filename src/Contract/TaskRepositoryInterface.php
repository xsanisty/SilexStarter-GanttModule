<?php

namespace Xsanisty\Gantt\Contract;

use Xsanisty\Gantt\Model\Chart;

interface TaskRepositoryInterface
{
    public function findById($id);

    public function findByChart(Chart $cart);

    public function reCalculateProgress($id);

    public function create(array $task);

    public function update($id, array $data);

    public function delete($id);
}
