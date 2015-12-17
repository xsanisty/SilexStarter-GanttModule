<?php

namespace Xsanisty\Gantt\Migration;

use SilexStarter\Migration\Migration;

class CreateGanttTable extends Migration
{
    /**
     * Run upgrade migration.
     */
    public function up()
    {
        $this->schema->create(
            'gantt_charts',
            function ($table) {
                $table->increments('id');
                $table->unsignedInteger('author_id');
                $table->string('name');
                $table->timestamps();

                $table->foreign('author_id')
                      ->references('id')
                      ->on('users')
                      ->ondDelete('cascade');

                $table->engine = 'InnoDB';
            }
        );
    }

    /**
     * Run downgrade migration.
     */
    public function down()
    {
        if ($this->schema->hasTable('gantt_members')) {
            try {
                $this->schema->table(
                    'gantt_members',
                    function ($table) {
                        $table->dropForeign('gantt_members_gantt_id_foreign');
                    }
                );
            } catch (\Exception $e) {
                //do nothing
            }
        }

        if ($this->schema->hasTable('gantt_tasks')) {
            try {
                $this->schema->table(
                    'gantt_tasks',
                    function ($table) {
                        $table->dropForeign('gantt_tasks_chart_id_foreign');
                    }
                );
            } catch (\Exception $e) {
                //do nothing
            }
        }

        if ($this->schema->hasTable('gantt_links')) {
            try {
                $this->schema->table(
                    'gantt_tasks',
                    function ($table) {
                        $table->dropForeign('gantt_links_chart_id_foreign');
                    }
                );
            } catch (\Exception $e) {
                //do nothing
            }
        }

        if ($this->schema->hasTable('gantt_bookmarks')) {
            try {
                $this->schema->table(
                    'gantt_bookmarks',
                    function ($table) {
                        $table->dropForeign('gantt_bookmarks_chart_id_foreign');
                    }
                );
            } catch (\Exception $e) {
                //do nothing
            }
        }

        $this->schema->drop('gantt_charts');
    }
}
