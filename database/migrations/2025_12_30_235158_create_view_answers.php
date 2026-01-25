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
select `a`.`id` AS `answer_id`,`a`.`entry_id` AS `entry_id`,
`e`.`is_editable` AS `is_editable`,`a`.`value` AS `respuesta`,
`q`.`id` AS `question_id`,`q`.`label` AS `pregunta`,`s`.`id` AS `section_id`,
`s`.`title` AS `section_title`,`u`.`id` AS `user_id`,`u`.`name` AS `user_name`,
`u`.`email` AS `user_email` , e.created_at as fecha_creado
from ((((`sia`.`answers` `a` join `sia`.`questions` `q` on((`a`.`question_id` = `q`.`id`))) 
join `sia`.`sections` `s` on((`q`.`section_id` = `s`.`id`))) join `sia`.`entries` `e` on((`a`.`entry_id` = `e`.`id`) AND(`e`.`deleted_at` IS NULL)
 )) join `sia`.`users` `u` on((`e`.`user_id` = `u`.`id`)))
        ");
    }

    public function down()
    {
        DB::statement('DROP VIEW IF EXISTS answers_view');
    }
};
