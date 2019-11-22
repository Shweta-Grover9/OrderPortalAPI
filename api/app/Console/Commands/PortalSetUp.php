<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

class PortalSetUp extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'portal:setup';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command used to run portal set up commands';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {   
        $this->callSilent('view:clear');      
        
        $this->callSilent('route:cache');
        
        $this->callSilent('route:clear');

       echo "#### Executing Unit Test Cases ####";
       echo "".shell_exec('php ./vendor/phpunit/phpunit/phpunit ./tests/Unit 2>&1');
       
       echo "#### Executing Integration Test Cases ####";
       echo "".shell_exec('php ./vendor/phpunit/phpunit/phpunit ./tests/Feature 2>&1');
       
       $this->callSilent('l5-swagger:generate');
    }
}
