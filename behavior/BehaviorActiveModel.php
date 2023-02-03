<?php

namespace twin\behavior;

use twin\model\active\ActiveModel;

abstract class BehaviorActiveModel extends BehaviorModel
{
    /**
     * @var ActiveModel
     */
    protected $owner;

    /**
     * {@inheritdoc}
     * @param ActiveModel $owner
     */
    public function __construct(ActiveModel $owner, array $properties = [])
    {
        parent::__construct($owner, $properties);
    }
}
