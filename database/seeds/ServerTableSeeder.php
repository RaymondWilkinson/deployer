<?php

use Illuminate\Database\Seeder;
use REBELinBLUE\Deployer\Project;
use REBELinBLUE\Deployer\ProjectServer;
use REBELinBLUE\Deployer\Server;

class ServerTableSeeder extends Seeder
{
    public function run()
    {
        DB::table('servers')->delete();

        Server::create([
            'name'        => 'Web VM',
            'ip_address'  => '192.168.33.50',
            'type'        => Server::TYPE_UNIQUE,
            'user'        => 'deploy',
            'path'        => '/var/www',
        ]);

        Server::create([
            'name'        => 'Cron VM',
            'ip_address'  => '192.168.33.60',
            'type'        => Server::TYPE_UNIQUE,
            'user'        => 'deploy',
            'path'        => '/var/www',
        ]);

        $project = Project::where('name', 'Deployer')->firstOrFail();
        foreach (Server::where('type', Server::TYPE_UNIQUE)->get() as $server) {
            $project->servers()->attach($server->id, array(
                'deploy_code' => true,
                'connect_log' => null,
                'status'      => ProjectServer::UNTESTED,
                'order'       => 0,
            ));
        }

        $shared = Server::create([
            'name'        => 'Database VM',
            'ip_address'  => '192.168.33.70',
            'user'        => 'deploy',
            'path'        => '/home/deploy',
            'type'        => Server::TYPE_SHARED,
        ]);

        $project->servers()->attach($shared->id, [
            'deploy_code' => false,
            'connect_log' => null,
            'status'      => ProjectServer::UNTESTED,
            'order'       => 0,
        ]);
    }
}
