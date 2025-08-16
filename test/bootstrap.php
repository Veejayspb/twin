<?php

require_once dirname(__DIR__) . DIRECTORY_SEPARATOR . 'src' . DIRECTORY_SEPARATOR . 'Twin.php';

\twin\Twin::app();
\twin\helper\Alias::set('@test', __DIR__);
