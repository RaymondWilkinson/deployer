<?php

use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;
use REBELinBLUE\Deployer\Project;
use REBELinBLUE\Deployer\Server;
use REBELinBLUE\Deployer\ServerTemplate;

class CreateProjectServersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('project_server', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('project_id');
            $table->unsignedInteger('server_id');
            $table->string('user')->nullable();
            $table->string('path')->nullable();
            $table->text('connect_log')->nullable();
            $table->unsignedInteger('order')->default(0);
            $table->unsignedInteger('status')->default(Server::UNTESTED);
            $table->boolean('deploy_code')->default(true);
            $table->timestamps();
            $table->softDeletes();
            $table->foreign('project_id')->references('id')->on('projects');
            $table->foreign('server_id')->references('id')->on('servers');
        });

        Schema::table('servers', function (Blueprint $table) {
            $table->string('user')->nullable()->change();
            $table->string('path')->nullable()->change();
        });

        Server::withTrashed()->chunk(100, function (Collection $servers) {
            /** @var Server $server */
            foreach ($servers as $server) {
                $project = Project::withTrashed()->where('id', $server->project_id)->firstOrFail();
                $project->servers()->attach($server->id, [
                    'deploy_code' => $server->deploy_code,
                    'connect_log' => $server->connect_log,
                    'status'      => $server->status,
                    'order'       => $server->order
                ]);
                $project->save();
            }
        });

        Schema::table('servers', function (Blueprint $table) {
            $table->dropForeign('servers_project_id_foreign');
            $table->string('type')->default(Server::TYPE_UNIQUE);
            $table->dropColumn(['project_id', 'deploy_code', 'connect_log', 'status', 'order']);
        });

        Schema::dropIfExists('server_templates');
    }

    /**
     * Reverse the migrations.
     */
    public function down()
    {
        Schema::drop('project_server');
    }
}
