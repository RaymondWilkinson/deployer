<?php

namespace REBELinBLUE\Deployer\Repositories;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Foundation\Bus\DispatchesJobs;
use REBELinBLUE\Deployer\Jobs\TestServerConnection;
use REBELinBLUE\Deployer\ProjectServer;
use REBELinBLUE\Deployer\Repositories\Contracts\ServerRepositoryInterface;
use REBELinBLUE\Deployer\Server;

/**
 * The project server repository.
 */
class EloquentProjectServerRepository extends EloquentRepository implements ProjectServerRepositoryInterface
{

}
