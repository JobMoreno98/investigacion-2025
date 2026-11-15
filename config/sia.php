<?php

return [

    /*
    | Sistema con el que trabaja ESTA aplicación (slug de la tabla `sistemas`).
    | Cada aplicación que comparte la base de datos declara el suyo en su .env:
    |   SIA_SISTEMA=sia            (aplicación SIA)
    |   SIA_SISTEMA=investigacion  (aplicación de Investigación)
    */
    'sistema' => env('SIA_SISTEMA', 'sia'),

];