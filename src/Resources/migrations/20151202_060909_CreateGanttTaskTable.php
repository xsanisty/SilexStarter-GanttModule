<?php

namespace Xsanisty\Gantt\Migration;

use SilexStarter\Migration\Migration;

class CreateGanttTaskTable extends Migration
{
    /**
     * Run upgrade migration.
     */
    public function up()
    {
        $this->schema->create(
            'gantt_tasks',
            function ($table) {
                $table->increments('id');
                $table->unsignedInteger('chart_id');
                $table->dateTime('start_date');
                $table->dateTime('end_date');
                $table->string('text');
                $table->enum('type', ['task', 'project', 'milestone']);
                $table->float('progress');
                $table->float('priority');
                $table->boolean('open')->default(true);
                $table->unsignedInteger('duration');
                $table->unsignedInteger('sortorder');
                $table->unsignedInteger('parent')->nullable();
                $table->timestamps();

                $table->foreign('chart_id')
                      ->references('id')
                      ->on('gantt_charts')
                      ->onDelete('cascade');

                $table->foreign('parent')
                      ->references('id')
                      ->on('gantt_tasks')
                      ->onDelete('cascade');

                $table->engine = 'InnoDB';
            }
        );
    }

    /**
     * Run downgrade migration.
     */
    public function down()
    {
        if ($this->schema->hasTable('gantt_links')) {
            try {
                $this->schema->table(
                    'gantt_links',
                    function ($table) {
                        $table->dropForeign('gantt_links_target_foreign');
                        $table->dropForeign('gantt_links_source_foreign');
                    }
                );
            } catch (\Exception $e) {
                //do nothing
            }
        }

        if ($this->schema->hasTable('gantt_task_members')) {
            try {
                $this->schema->table(
                    'gantt_task_members',
                    function ($table) {
                        $table->dropForeign('gantt_task_members_task_id_foreign');
                    }
                );
            } catch (\Exception $e) {
                //do nothing
            }
        }

        try {
            $this->schema->table(
                'gantt_tasks',
                function ($table) {
                    $table->dropForeign('gantt_tasks_parent_foreign');
                    $table->dropForeign('gantt_tasks_chart_id_foreign');
                }
            );
        } catch (\Exception $e) {
            //do nothing
        }

        $this->schema->drop('gantt_tasks');
    }
}
