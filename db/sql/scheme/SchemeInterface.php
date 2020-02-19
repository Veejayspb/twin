<?php

namespace twin\db\sql\scheme;

interface SchemeInterface
{
    public function getTables();

    public function addTable();
}
