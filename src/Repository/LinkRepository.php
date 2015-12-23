<?php

namespace Xsanisty\Gantt\Repository;

use Xsanisty\Gantt\Model\Link;
use Xsanisty\Gantt\Model\Task;
use Xsanisty\Gantt\Model\Chart;
use Xsanisty\Gantt\Contract\LinkRepositoryInterface;

class LinkRepository implements LinkRepositoryInterface
{
    protected $link;

    public function __construct(Link $link)
    {
        $this->link = $link;
    }

    public function findByChart(Chart $chart)
    {
        return $chart->links;
    }

    public function create(array $data)
    {
        return $this->link->create($data);
    }

    public function delete($id)
    {
        return $this->link->newQuery()->where('id', '=', $id)->delete();
    }
}
