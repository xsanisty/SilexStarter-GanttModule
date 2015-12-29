<?php

namespace Xsanisty\Gantt\Controller;

use Exception;
use Hashids\Hashids;
use Xsanisty\Admin\DashboardModule;
use Xsanisty\Gantt\Contract\ChartRepositoryInterface;
use Cartalyst\Sentry\Users\UserInterface;

class ChartController
{
    protected $user;
    protected $chartRepo;
    protected $hasher;

    public function __construct(
        ChartRepositoryInterface $chartRepo,
        Hashids $hasher,
        UserInterface $user = null
    ) {
        $this->user      = $user;
        $this->chartRepo = $chartRepo;
        $this->hasher    = $hasher;
    }

    /**
     * Show all available gantt chart
     */
    public function index()
    {
        $this->initDashboard();
        Menu::get('admin_sidebar')->setActive('xsanisty-gantt.my-gantt-chart');
        Asset::exportVariable('ganttDatatableUrl', Url::to('gantt.chart.datatable'));

        return View::make(
            '@xsanisty-gantt/chart/index',
            [
                'title'         => 'My Gantt Chart',
                'page_title'    => 'My Gantt Chart',
            ]
        );
    }

    /**
     * Show specific gantt chart based on id, or display 404 page if not found
     */
    public function edit($id)
    {
        $this->initDashboard();
        $chart = $this->chartRepo->findByIdIfAllowed($id, $this->user);

        if (!$chart) {
            Menu::get('admin_breadcrumb')->createItem(
                'gantt-not-found',
                [
                    'icon'  => 'warning',
                    'label' => 'Error Page',
                    'url'   => '#'
                ]
            );
            return Response::view('@silexstarter-dashboard/'.Config::get('@silexstarter-dashboard.config.template').'/404', [], 404);
        }

        try {
            Menu::get('admin_sidebar')->setActive('xsanisty-gantt.my-gantt-' . $id);
        } catch (Exception $e) {
            //do nothing
        }

        $chartUrl   = Url::to('gantt.chart.show', ['id' => $id]);
        $publicUrl  = $chart->visibility == 'public'
                    ? Url::to(
                        'gantt.chart.public',
                        [
                            'chart_id' => $this->hasher->encode($chart->id, $chart->id+5, $chart->id+8)
                        ]
                    ) : '';

        Menu::get('admin_breadcrumb')->createItem(
            'gantt-chart-' . $id,
            [
                'icon'  => 'tasks',
                'label' => $chart->name,
                'url'   => $chartUrl
            ]
        );

        Asset::exportVariable(
            [
                'ganttApi'      => $chartUrl,
                'bookmarkUrl'   => Url::to('gantt.bookmark.create'),
                'chartInfo'     => $chart
            ]
        );

        return View::make(
            '@xsanisty-gantt/chart/edit',
            [
                'title'         => $chart->name,
                'page_title'    => $chart->name,
                'chart'         => $chart,
                'public_url'    => $publicUrl,
            ]
        );
    }

    /**
     * Save gantt chart data
     */
    public function store()
    {
        try {
            $chart      = Request::only(['name', 'visibility', 'settings'], ['', 'private', []]);
            $colSettings= [];

            if (trim($chart['name']) == '') {
                throw new Exception("Chart name can not be empty!", 500);
            }

            $chart['author_id'] = $this->user->getId();

            foreach ($chart['settings']['columns'] as $column => $config) {
                if (!empty($config['enabled'])) {
                    $colSettings[] = $config;
                }
            }

            $chart['settings']['columns'] = $colSettings;

            $this->chartRepo->create($chart);

            return Response::ajax('New chart created!', 201);
        } catch (Exception $e) {
            return Response::ajax(
                'Error occured while creating chart',
                500,
                [[
                    'message'   => $e->getMessage(),
                    'code'      => $e->getCode(),
                    'trace'     => $e->getTrace()
                ]]
            );
        }
    }

    /**
     * Update chart data
     */
    public function update($id)
    {
        try {
            $chart      = Request::except(['id', '_method']);
            $colSettings= [];

            if (trim($chart['name']) == '') {
                throw new Exception("Chart name can not be empty!", 500);
            }

            $chart['author_id'] = $this->user->getId();

            foreach ($chart['settings']['columns'] as $column => $config) {
                if (!empty($config['enabled'])) {
                    $colSettings[] = $config;
                }
            }

            $chart['settings']['columns'] = $colSettings;

            $chart = $this->chartRepo->update($id, $chart);

            return Response::ajax(
                [
                    'message'   => 'Chart info updated!',
                    'chart'     => $chart
                ],
                200
            );
        } catch (Exception $e) {
            return Response::ajax(
                'Error occured while updating chart',
                500,
                [[
                    'message'   => $e->getMessage(),
                    'code'      => $e->getCode(),
                    'trace'     => $e->getTrace()
                ]]
            );
        }
    }

    /**
     * Display publicly available chart.
     *
     * @param  int  $chart_id   The chart id
     */
    public function publicChart($chart_id)
    {
        $chart = $this->chartRepo->findById($chart_id['id']);

        if (!$chart || ($chart&& $chart->visibility == 'private')) {
            return Response::make('chart not found!', 404);
        }

        Asset::exportVariable('ganttApi', Url::to('gantt.chart.public', ['chart_id' => $chart_id['hash']]));
        Asset::exportVariable('ganttSettings', $chart->settings);

        return View::make(
            '@xsanisty-gantt/chart/public_chart',
            [
                'title'         => $chart->name,
                'page_title'    => $chart->name,
                'chart'         => $chart
            ]
        );
    }

    /**
     * Delete existing chart.
     */
    public function delete($id)
    {
        try {
            $this->chartRepo->delete($id);

            return Response::ajax('Chart has been deleted');
        } catch (Exception $e) {
            return Response::ajax(
                'Error occured while deleting chart',
                500,
                [[
                    'message'   => $e->getMessage(),
                    'code'      => $e->getCode()
                ]]
            );
        }
    }

    /**
     * Initialize integration with the dashboard module
     */
    protected function initDashboard()
    {
        Event::fire(DashboardModule::INIT);

        Menu::get('admin_breadcrumb')->createItem(
            'gantt-home',
            [
                'label' => 'My Gantt Chart',
                'icon'  => 'tasks',
                'url'   => Url::to('gantt.chart.index')
            ]
        );
    }

    /**
     * Build datatable response for gantt list
     */
    public function datatable()
    {
        $user       = $this->user;
        $hasher     = $this->hasher;
        $formatter  = function ($row) use ($user, $hasher) {
            $profileTpl = '<a href="%s"><img data-toggle="tooltip" title="%s" src="%s" class="img-circle img-sm" /></a>';
            $styleTpl   = 'style="margin-right: 5px; margin-top:5px"';
            $editTpl    = '<a href="%s" class="btn btn-xs btn-primary btn-edit" %s >edit</a>';
            $deleteTpl  = '<a href="%s" class="btn btn-xs btn-danger btn-delete" %s >delete</a>';
            $publicTpl  = '<a href="%s" class="btn btn-xs btn-info btn-public" %s target="_blank">public view</a>';

            $membersLink= '';
            $isAuthor   = $user->getId() == $row->author->id;
            $permissions= [
                'gantt.chart.edit'  => false,
                'gantt.chart.delete'=> false,
                'gantt.chart.edit'  => false,
            ];

            $author = sprintf(
                $profileTpl,
                Url::to('usermanager.user.show', ['id' => $row->author->id]),
                $row->author->first_name . ' ' . $row->author->last_name,
                $row->author->profile_pic
                ? Asset::resolvePath('img/profile/' . $row->author->profile_pic)
                : Asset::resolvePath('@silexstarter-dashboard/img/avatar.jpg')
            );

            $row->members->add($row->author);

            foreach ($row->members as $member) {
                $profileLink = sprintf(
                    $profileTpl,
                    Url::to('usermanager.user.show', ['id' => $member->id]),
                    $member->first_name . ' ' . $member->last_name,
                    $member->profile_pic
                    ? Asset::resolvePath('img/profile/' . $member->profile_pic)
                    : Asset::resolvePath('@silexstarter-dashboard/img/avatar.jpg')
                );

                $membersLink .= $profileLink;

                if (!$isAuthor && $member->id == $user->getId()) {
                    $permissions = array_merge(
                        $permissions,
                        (array) json_decode($member->pivot->permissions, true)
                    );
                }
            }

            $editBtn    = $isAuthor || $permissions['gantt.chart.edit']
                        ? sprintf($editTpl, Url::to('gantt.chart.edit', ['id' => $row->id]), $styleTpl)
                        : '';
            $deleteBtn  = $isAuthor || $permissions['gantt.chart.delete']
                        ? sprintf($deleteTpl, Url::to('gantt.chart.delete', ['id' => $row->id]), $styleTpl)
                        : '';
            $publicBtn  = $row->visibility == 'public'
                        ? sprintf(
                            $publicTpl,
                            Url::to(
                                'gantt.chart.public',
                                [
                                    'chart_id' => $hasher->encode($row->id, $row->id+5, $row->id+8)
                                ]
                            ),
                            $styleTpl
                        )
                        : '';

            return [
                $row->name,
                $author . ' ' . $row->author->first_name . ' ' . $row->author->last_name,
                $membersLink,
                $row->created_at->format('Y-m-d H:i'),
                $publicBtn . $editBtn . $deleteBtn
            ];
        };

        $query  = $this->chartRepo->generateDatatableQueryForUser($user);
        $data   = Datatable::of($query)
                ->setColumn(['name', 'created_at', 'id', 'author_id', 'visibility'])
                ->setFormatter($formatter)
                ->make();

        return Response::json($data);
    }
}
