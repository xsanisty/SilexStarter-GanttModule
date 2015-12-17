<?php

namespace Xsanisty\Gantt\Migration;

use SilexStarter\Migration\Migration;

class CreateGanttTaskMemberTable extends Migration
{
    /**
     * Run upgrade migration.
     */
    public function up()
    {
        $this->schema->create(
            'gantt_task_members',
            function ($table) {
                $table->unsignedInteger('user_id');
                $table->unsignedInteger('task_id');

                $table->foreign('user_id')
                      ->references('id')
                      ->on('users')
                      ->onDelete('cascade');

                $table->foreign('task_id')
                      ->references('id')
                      ->on('gantt_tasks')
                      ->onDelete('cascade');

                $table->engine = 'InnoDB';
                $table->primary(['user_id', 'task_id']);
            }
        );
    }

    /**
     * Run downgrade migration.
     */
    public function down()
    {
        try {
            $this->schema->table(
                'gantt_task_members',
                function ($table) {
                    $table->dropForeign('gantt_task_members_user_id_foreign');
                    $table->dropForeign('gantt_task_members_task_id_foreign');
                }
            );
        } catch (\Exception $e) {
            //do nothing
        }

        $this->schema->drop('gantt_task_members');
    }
}
