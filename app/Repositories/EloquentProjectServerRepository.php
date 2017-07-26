<?php

namespace REBELinBLUE\Deployer\Repositories;

use REBELinBLUE\Deployer\ProjectServer;
use REBELinBLUE\Deployer\Repositories\Contracts\ProjectServerRepositoryInterface;

/**
 * The project server repository.
 */
class EloquentProjectServerRepository extends EloquentRepository implements ProjectServerRepositoryInterface
{
    /**
     * EloquentProjectServerRepository constructor.
     *
     * @param ProjectServer $model
     */
    public function __construct(ProjectServer $model)
    {
        $this->model = $model;
    }
}
