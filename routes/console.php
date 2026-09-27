<?php
use Illuminate\Support\Facades\Schedule;
use App\Jobs\ExpireReservations;
Schedule::job(new ExpireReservations)->everyMinute()->withoutOverlapping();
Schedule::job(new \App\Jobs\SyncRefunds)->everyFiveMinutes()->withoutOverlapping();
