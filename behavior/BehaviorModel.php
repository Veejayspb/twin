<?php

namespace twin\behavior;

use twin\model\Model;

abstract class BehaviorModel extends Behavior
{
    /**
     * @var Model
     */
    protected $owner;

    /**
     * {@inheritdoc}
     * @param Model $owner
     */
    public function __construct(Model $owner, array $properties = [])
    {
        parent::__construct($owner, $properties);
    }
}
