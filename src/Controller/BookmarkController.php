<?php

namespace Xsanisty\Gantt\Controller;

use Exception;
use Xsanisty\Admin\DashboardModule;
use Cartalyst\Sentry\Users\UserInterface;
use Xsanisty\Gantt\Contract\ChartRepositoryInterface;

class BookmarkController
{
    protected $user;
    protected $chartRepository;

    public function __construct(UserInterface $user, ChartRepositoryInterface $chartRepository)
    {
        $this->user = $user;
        $this->chartRepository = $chartRepository;
    }

    public function index()
    {
        $this->initDashboard();
        Menu::get('admin_sidebar')->setActive('xsanisty-gantt.my-gantt-bookmark');
        Asset::exportVariable('datatableUrl', Url::to('gantt.bookmark.datatable'));

        return View::make(
            '@xsanisty-gantt/bookmark/index',
            [
                'title'         => 'My Gantt Bookmark',
                'page_title'    => 'My Gantt Bookmark',
            ]
        );
    }

    public function store()
    {
        try {
            $this->chartRepository->bookmark(Request::get('chart_id'), $this->user->getId());

            return Response::ajax('Chart bookmarked!');
        } catch (Exception $e) {
            return Response::ajax(
                'Error occured while bookmarking chart!',
                500,
                [[
                    'message'   => 'Error occured while bookmarking chart!',
                    'code'      => 500
                ]]
            );
        }
    }

    public function delete($id)
    {
        try {
            $this->chartRepository->removeBookmark($id, $this->user->getId());

            return Response::ajax('Bookmark removed!');
        } catch (Exception $e) {
            return Response::ajax(
                'Error occured while removing bookmark!',
                500,
                [[
                    'message'   => 'Error occured while removing bookmark!',
                    'code'      => 500
                ]]
            );
        }
    }

    public function datatable()
    {
        $query      = $this->chartRepository->generateBookmarkQueryForUser($this->user);
        $formatter  = function ($row) {
            $deleteTpl  = '<a href="%s" class="btn btn-xs btn-danger btn-delete">delete</a>';
            $deleteBtn  = sprintf($deleteTpl, Url::to('gantt.bookmark.delete', ['id' => $row->id]));

            return [$row->name, $deleteBtn];
        };

        $data   = Datatable::of($query)
                ->setColumn(['name', 'id'])
                ->setFormatter($formatter)
                ->make();

        return Response::json($data);
    }

    protected function initDashboard()
    {
        Event::fire(DashboardModule::INIT);

        Menu::get('admin_breadcrumb')->createItem(
            'gantt-home',
            [
                'label' => 'Manage Gantt Bookmark',
                'icon'  => 'bookmark',
                'url'   => Url::to('gantt.bookmark.index')
            ]
        );
    }
}
