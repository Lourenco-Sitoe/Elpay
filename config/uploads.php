<?php

return [

    /*
    | Limites de upload (KB). No gratuito (InfinityFree: 10 MB/ficheiro),
    | definir UPLOAD_MAX_FILE_KB=9216, UPLOAD_MAX_IMAGE_KB=4096 e
    | UPLOAD_MAX_DOC_KB=4096 no .env. VPS mantém os valores altos.
    */
    'max_file_kb' => (int) env('UPLOAD_MAX_FILE_KB', 512000),
    'max_image_kb' => (int) env('UPLOAD_MAX_IMAGE_KB', 4096),
    'max_doc_kb' => (int) env('UPLOAD_MAX_DOC_KB', 5120),

];
