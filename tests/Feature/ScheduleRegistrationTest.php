<?php

namespace Tests\Feature;

use Tests\TestCase;
use Illuminate\Console\Scheduling\Schedule;
use App\Console\Kernel as ConsoleKernel;

class ScheduleRegistrationTest extends TestCase
{
    /** @test */
    public function test_the_update_client_status_command_is_scheduled()
    {
        $schedule = $this->app->make(Schedule::class);
        $kernel = $this->app->make(ConsoleKernel::class);

        // call protected schedule() by reflection
        $m = new \ReflectionMethod($kernel, 'schedule');
        $m->setAccessible(true);
        $m->invoke($kernel, $schedule);

        $events = collect($schedule->events());

        $found = $events->contains(function ($event) {
            $summary = method_exists($event, 'getSummaryForDisplay')
                ? $event->getSummaryForDisplay()
                : (string) $event;
            return str_contains($summary, 'crm:update-client-statuses');
        });

        $this->assertTrue($found, 'crm:update-client-statuses should be scheduled');
    }
}
