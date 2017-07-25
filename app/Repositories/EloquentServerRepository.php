<?php

namespace REBELinBLUE\Deployer\Repositories;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Foundation\Bus\DispatchesJobs;
use REBELinBLUE\Deployer\Jobs\TestServerConnection;
use REBELinBLUE\Deployer\ProjectServer;
use REBELinBLUE\Deployer\Repositories\Contracts\ServerRepositoryInterface;
use REBELinBLUE\Deployer\Server;

/**
 * The server repository.
 */
class EloquentServerRepository extends EloquentRepository implements ServerRepositoryInterface
{
    use DispatchesJobs;

    /**
     * EloquentServerRepository constructor.
     *
     * @param Server $model
     */
    public function __construct(Server $model)
    {
        $this->model = $model;
    }

    /**
     * {@inheritdoc}
     */
    public function getAll()
    {
        return $this->model
                    ->orderBy('name')
                    ->get();
    }

    /**
     * Creates a new instance of the model.
     *
     * @param array $fields
     *
     * @return \Illuminate\Database\Eloquent\Model
     */
    public function create(array $fields)
    {
        // Get the current highest server order
        $max = $this->model->where('project_id', $fields['project_id'])
                           ->orderBy('order', 'DESC')
                           ->first();

        $order = 0;
        if (isset($max)) {
            $order = $max->order + 1;
        }

        $fields['type']  = Server::TYPE_UNIQUE;
        $fields['order'] = $order;

        $add_commands = false;
        if (isset($fields['add_commands'])) {
            $add_commands = $fields['add_commands'];
            unset($fields['add_commands']);
        }

        $server = $this->model->create($fields);

        // Add the server to the existing commands
        if ($add_commands) {
            foreach ($server->project->commands as $command) {
                $command->servers()->attach($server->id);
            }
        }

        return $server;
    }

    /**
     * @param int $project_server_id
     */
    public function queueForTesting($project_server_id)
    {
        /** @var Server $server */
        $server = $this->model->whereHas('projects', function (Builder $query) use ($project_server_id) {
            $query->where('project_server.id', $project_server_id);
        })->firstOrFail()[0];

        if (!$server->isTesting()) {
            $server->projects()->updateExistingPivot($project_server_id, ['status' => Server::TESTING]);

            $this->dispatch(new TestServerConnection($server));
        }
    }
}
