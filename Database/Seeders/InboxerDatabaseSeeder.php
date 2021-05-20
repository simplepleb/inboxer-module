<?php

namespace Modules\Inboxer\Database\Seeders;

use App\Models\Permission;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Database\Eloquent\Model;

class InboxerDatabaseSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        Model::unguard();

        // $this->call("OthersTableSeeder");

        \Artisan::call('auth:permission', [
            'name' => 'lists',
        ]);
        echo "\n _Lists_ Permissions Created.";

        echo "\n\n";

        \Artisan::call('auth:permission', [
            'name' => 'subscribers',
        ]);
        echo "\n _Subscribers_ Permissions Created.";

        echo "\n\n";

        \Artisan::call('auth:permission', [
            'name' => 'templates',
        ]);
        echo "\n _Templates_ Permissions Created.";

        echo "\n\n";

        \Artisan::call('auth:permission', [
            'name' => 'campaigns',
        ]);
        echo "\n _Campaigns_ Permissions Created.";

        echo "\n\n";


    }
}
