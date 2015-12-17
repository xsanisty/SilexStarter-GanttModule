<?php

namespace Xsanisty\Gantt\Migration;

use SilexStarter\Migration\Migration;

class CreateGanttBookmarkTable extends Migration
{
    /**
     * Run upgrade migration.
     */
    public function up()
    {
        $this->schema->create(
            'gantt_bookmarks',
            function ($table) {
                $table->unsignedInteger('user_id');
                $table->unsignedInteger('chart_id');

                $table->foreign('user_id')
                      ->references('id')
                      ->on('users')
                      ->onDelete('cascade');

                $table->foreign('chart_id')
                      ->references('id')
                      ->on('gantt_charts')
                      ->onDelete('cascade');

                $table->engine = 'InnoDB';
                $table->primary(['user_id', 'chart_id']);
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
                'gantt_bookmarks',
                function ($table) {
                    $table->dropForeign('gantt_bookmarks_user_id_foreign');
                    $table->dropForeign('gantt_bookmarks_chart_id_foreign');
                }
            );
        } catch (\Exception $e) {
            //do nothing
        }

        $this->schema->drop('gantt_bookmarks');
    }
}
