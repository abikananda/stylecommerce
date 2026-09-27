<?php
use Illuminate\Support\Facades\Schedule;
use App\Jobs\ExpireReservations;
Schedule::job(new ExpireReservations)->everyMinute()->withoutOverlapping();
