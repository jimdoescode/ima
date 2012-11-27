<?php

return [

    'base_url' => 'http://localhost/photohost',

    'cache_directory' => 'cache',

    'dimension_aliases' => [
        'default' => '640x',
        'thumbnail' => '80x',
        'original' => null,
    ],

    'filters' => [
        'blur' => IMG_FILTER_SELECTIVE_BLUR,
        'emboss' => IMG_FILTER_EMBOSS,
        'negative' => IMG_FILTER_NEGATE,
        'grayscale' => IMG_FILTER_GRAYSCALE,
        'edge' => IMG_FILTER_EDGEDETECT,
        'sketch' => IMG_FILTER_MEAN_REMOVAL
    ],

];