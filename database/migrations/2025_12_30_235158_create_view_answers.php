<?php

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Migrations\Migration;

return new class extends Migration
{
    public function up()
    {

        DB::statement("
            CREATE OR REPLACE VIEW  answers_view AS
SELECT
    `a`.`id` AS `answer_id`,
    `a`.`entry_id` AS `entry_id`,
    `e`.`is_editable` AS `is_editable`,
    JSON_UNQUOTE(`a`.`value`) AS `respuesta`,
    `q`.`id` AS `question_id`,
    `q`.`label` AS `pregunta`,
    `s`.`id` AS `section_id`,
    `s`.`title` AS `section_title`,
    `u`.`id` AS `user_id`,
    `u`.`name` AS `user_name`,
    `u`.`email` AS `user_email`,
    `e`.`created_at` AS `fecha_creado`
FROM
    (
        (
            (
                (
                    `sia`.`answers` `a`
                JOIN `sia`.`questions` `q`
                ON
                    ((`a`.`question_id` = `q`.`id`))
                )
            JOIN `sia`.`sections` `s`
            ON
                ((`q`.`section_id` = `s`.`id`))
            )
        JOIN `sia`.`entries` `e`
        ON
            (
                (
                    (`a`.`entry_id` = `e`.`id`) AND(`e`.`deleted_at` IS NULL)
                )
            )
        )
    JOIN `sia`.`users` `u`
    ON
        ((`e`.`user_id` = `u`.`id`))
    )");
    }

    public function down()
    {
        DB::statement('DROP VIEW IF EXISTS answers_view');
    }
};
