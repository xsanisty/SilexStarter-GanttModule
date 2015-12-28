<?php

namespace Xsanisty\Gantt\Contract;

use Cartalyst\Sentry\Users\UserInterface;
use Xsanisty\Gantt\Model\Chart;

interface ChartRepositoryInterface
{
    /**
     * Generate query for datatable builder
     */
    public function generateDatatableQueryForUser(UserInterface $user);

    /**
     * Find chart by id;
     *
     * @param  int $id  The chart id
     *
     * @return Chart
     */
    public function findById($id);

    /**
     * Find chart by author.
     *
     * @param  UserInterface $user The user who become the author
     *
     * @return Chart[]              Collection of chart object
     */
    public function findByAuthor(UserInterface $user);

    /**
     * Find chart where the user become the member.
     *
     * @param  UserInterface $user  The user who become the member
     *
     * @return Chart[]              Collection of chart object
     */
    public function findByMember(UserInterface $user);

    /**
     * Find chart where the user participated to, either become member or the author.
     *
     * @param  UserInterface $user  The user who participated to the chart
     *
     * @return Chart[]              Collection of chart object
     */
    public function findByParticipant(UserInterface $user);

    /**
     * Find chart by id and check if the given user has access to the chart.
     *
     * @param  int              $id     The chart id
     * @param  UserInterface    $user   The user who try to access the chart
     *
     * @return Chart                    The chart object
     */
    public function findByIdIfAllowed($id, UserInterface $user);

    /**
     * Find chart already bookmarked by user.
     *
     * @param  UserInterface $user  The user who bookmarked chart
     *
     * @return Chart[]              Collection of chart object
     */
    public function findBookmarkedByUser(UserInterface $user);

    /**
     * Create new bookmark entry.
     *
     * @param  int $ganttId
     * @param  int $userId
     *
     * @return void
     */
    public function bookmark($ganttId, $userId);

    /**
     * Remove existing bookmark entry.
     *
     * @param  int $ganttId
     * @param  int $userId
     *
     * @return void
     */
    public function removeBookmark($ganttId, $userId);

    /**
     * Create new chart.
     *
     * @param  array    $data   The chart data
     *
     * @return Chart            The chart object
     */
    public function create(array $data);

    /**
     * Update the chart.
     *
     * @param array     $id     The chart id
     * @param Chart     $data   The chart data need to be updated
     *
     * @return Chart
     */
    public function update($id, array $data);

    /**
     * Delete chart with specific id
     *
     * @param  int $id The chart id
     *
     * @return boolean
     */
    public function delete($id);
}
