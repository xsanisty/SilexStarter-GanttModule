<?php

namespace Xsanisty\Gantt\Repository;

use Cartalyst\Sentry\Users\UserInterface;
use Xsanisty\Gantt\Contract\ChartRepositoryInterface;
use Xsanisty\Gantt\Model\Chart;

class ChartRepository implements ChartRepositoryInterface
{
    protected $chart;

    public function __construct(Chart $chart)
    {
        $this->chart = $chart;
    }

    /**
     * {@inheritdoc}
     */
    public function generateDatatableQueryForUser(UserInterface $user)
    {
        return $this->chart
        ->with(
            [
                'author'  => function ($query) {
                    $query->select(['id', 'profile_pic', 'first_name', 'last_name', 'email']);
                },
                'members' => function ($query) {
                    $query->select(['id', 'profile_pic', 'first_name', 'last_name', 'email']);
                }
            ]
        )
        ->where('author_id', '=', $user->getId())
        ->orWhereRaw(
            'EXISTS(
                SELECT 1
                FROM `gantt_members`
                WHERE `chart_id` = `gantt_charts`.`id`
                AND `user_id` = '. $user->getId() .'
            )'
        );
    }

    /**
     * {@inheritdoc}
     */
    public function findById($id)
    {
        return $this->chart->find($id);
    }

    /**
     * {@inheritdoc}
     */
    public function findByAuthor(UserInterface $user)
    {
        return $this->chart
        ->newQuery()
        ->where('author_id', '=', $user->getId())
        ->get();
    }

    /**
     * {@inheritdoc}
     */
    public function findByMember(UserInterface $user)
    {
        return $this->chart
        ->newQuery()
        ->where(
            'EXISTS(
                SELECT 1
                FROM `gantt_charts` `g`
                LEFT JOIN `gantt_members` `m` ON (`g`.`id` = `m`.`chart_id`)
                WHERE `m`.`user_id` = '. $user->getId() .'
            )'
        )
        ->get();
    }

    /**
     * {@inheritdoc}
     */
    public function findByParticipant(UserInterface $user)
    {
        return $this->chart
        ->newQuery()
        ->where('author_id', '=', $user->getId())
        ->orWhereRaw(
            'EXISTS(
                SELECT 1
                FROM `gantt_charts` `g`
                LEFT JOIN `gantt_members` `m` ON (`g`.`id` = `m`.`chart_id`)
                WHERE `m`.`user_id` = '. $user->getId() .'
            )'
        )
        ->get();
    }

    /**
     * {@inheritdoc}
     */
    public function findByIdIfAllowed($id, UserInterface $user)
    {
        $chart = $this->chart
        ->newQuery()
        ->with(
            [
                'members' => function ($query) use ($user) {
                    $query->where('user_id', '=', $user->getId());
                }
            ]
        )
        ->find($id);

        if ($user->hasAccess('admin') || $chart->author_id == $user->getId() || count($chart->members)) {
            return $chart;
        }
    }

    /**
     * {@inheritdoc}
     */
    public function findBookmarkedByUser(UserInterface $user)
    {
        $chart = $this->chart
        ->newQuery()
        ->join('gantt_bookmarks', 'gantt_charts.id', '=', 'gantt_bookmarks.chart_id')
        ->where('gantt_bookmarks.user_id', '=', $user->getId())
        ->get();
    }

    /**
     * {@inheritdoc}
     */
    public function bookmark($ganttId, $userId)
    {

    }

    /**
     * {@inheritdoc}
     */
    public function create(array $data)
    {
        return $this->chart->create($data);
    }

    /**
     * {@inheritdoc}
     */
    public function update($id, array $data)
    {
        return $this->chart->where('id', '=', $id)->update($data);
    }

    /**
     * {@inheritdoc}
     */
    public function delete($id)
    {
        return $this->chart
        ->newQuery()
        ->where('id', '=', $id)
        ->delete();
    }
}
