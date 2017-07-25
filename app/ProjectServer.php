<?php

namespace REBELinBLUE\Deployer;

use Illuminate\Database\Eloquent\Model;

class ProjectServer extends Model
{
    const SUCCESSFUL = 0;
    const UNTESTED   = 1;
    const FAILED     = 2;
    const TESTING    = 3;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = ['user', 'path', 'project_id', 'server_id', 'deploy_code'];

    /**
     * The attributes that should be cast to native types.
     *
     * @var array
     */
    protected $casts = [
        'id'          => 'integer',
        'project_id'  => 'integer',
        'server_id'   => 'integer',
        'status'      => 'integer',
        'deploy_code' => 'boolean',
    ];

    /**
     * Belongs to relationship.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function project()
    {
        return $this->belongsTo(Project::class);
    }

    /**
     * Belongs to relationship.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function server()
    {
        return $this->belongsTo(Server::class);
    }

    /**
     * Determines whether the server is currently being testing.
     *
     * @return bool
     */
    public function isTesting()
    {
        return ($this->status === self::TESTING);
        //return ($this->status === self::TESTING);
    }
}
