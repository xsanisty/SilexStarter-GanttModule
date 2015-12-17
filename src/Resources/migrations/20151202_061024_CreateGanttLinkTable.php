<?php

namespace Xsanisty\Gantt\Migration;

use SilexStarter\Migration\Migration;

class CreateGanttLinkTable extends Migration
{
    /**
     * Run upgrade migration.
     */
    public function up()
    {
        $this->schema->create(
            'gantt_links',
            function ($table) {
                $table->increments('id');
                $table->unsignedInteger('chart_id');
                $table->unsignedInteger('source');
                $table->unsignedInteger('target');
                $table->string('type');

                $table->foreign('chart_id')
                      ->references('id')
                      ->on('gantt_charts')
                      ->onDelete('cascade');

                $table->foreign('source')
                      ->references('id')
                      ->on('gantt_tasks')
                      ->onDelete('cascade');

                $table->foreign('target')
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
        try {
            $this->schema->table(
                'gantt_links',
                function ($table) {
                    $table->dropForeign('gantt_links_target_foreign');
                    $table->dropForeign('gantt_links_source_foreign');
                    $table->dropForeign('gantt_links_chart_id_foreign');
                }
            );
        } catch (\Exception $e) {
            //do nothing
        }

        $this->schema->drop('gantt_links');
    }
}
