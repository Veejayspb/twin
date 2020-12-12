<?php

\twin\Twin::setAlias('@root', dirname(__DIR__, 2));
\twin\Twin::setAlias('@twin', dirname(__DIR__));
\twin\Twin::setAlias('@app', '@root/app');
\twin\Twin::setAlias('@runtime', '@app/runtime');
\twin\Twin::setAlias('@web', '@root/web');

return [
    'name' => 'Twin application',
    'language' => 'en',
    'params' => [],
];
