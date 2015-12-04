<?php

namespace Xsanisty\Gantt\Migration;

use SilexStarter\Migration\Migration;

class CreateGanttMemberTable extends Migration
{
    /**
     * Run upgrade migration.
     */
    public function up()
    {
        $this->schema->create(
            'gantt_members',
            function ($table) {
                $table->unsignedInteger('gantt_id');
                $table->unsignedInteger('user_id');

                $table->foreign('gantt_id')
                      ->references('id')
                      ->on('gantts')
                      ->onDelete('cascade');

                $table->foreign('user_id')
                      ->references('id')
                      ->on('users')
                      ->onDelete('cascade');

                $table->engine = 'InnoDB';
                $table->primary(['gantt_id', 'user_id']);
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
                'gantt_members',
                function ($table) {
                    $table->dropForeign('gantt_members_gantt_id_foreign');
                }
            );
        } catch (\Exception $e) {
            //do nothing
        }

        try {
            $this->schema->table(
                'gantt_members',
                function ($table) {
                    $table->dropForeign('gantt_members_user_id_foreign');
                }
            );
        } catch (\Exception $e) {
            //do nothing
        }

        $this->schema->drop('gantt_members');
    }
}
