<?php

namespace REBELinBLUE\Deployer;

use Illuminate\Database\Eloquent\Relations\Pivot;

class ProjectServer extends Pivot
{
    /**
     * The attributes that should be cast to native types.
     *
     * @var array
     */
    protected $casts = [
        'project_id'  => 'integer',
        'server_id'   => 'integer',
        'status'      => 'integer',
        'deploy_code' => 'boolean',
    ];

    /**
     * Belongs to many relationship.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany
     */
    public function projects()
    {
        return $this->belongsToMany(Project::class);
    }
}
