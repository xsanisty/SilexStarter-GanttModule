<?php

namespace Xsanisty\Gantt\Controller;

use Exception;
use Xsanisty\Gantt\Contract\LinkRepositoryInterface;

class LinkController
{
    protected $repo;

    public function __construct(LinkRepositoryInterface $repo)
    {
        $this->repo = $repo;
    }

    public function store($chart_id)
    {
        $data = Request::only(['source', 'target', 'type']);
        $data['chart_id'] = $chart_id;

        try {
            $link = $this->repo->create($data);

            return Response::json(
                [
                    'action'=> 'inserted',
                    'tid'   => $link->id,
                    'data'  => $link
                ]
            );
        } catch (Exception $e) {
            return Response::json(
                [
                    'action'=> 'error',
                    'data'  => $e->getMessage()
                ]
            );
        }
    }

    public function delete($chart_id, $id)
    {
        try {
            $this->repo->delete($id);
            return Response::json(
                [
                    'action'=> 'deleted',
                    'tid'   => $id
                ]
            );
        } catch (Exception $e) {
            return Response::json(
                [
                    'action'=> 'error',
                    'data'  => $e->getMessage()
                ]
            );
        }
    }
}
