<?php

namespace Laweitech\LaravelTelegramEventOutput;

trait TelegramConsoleKernel
{

	protected function defineConsoleSchedule()
    {
        $this->app->instance(
            'Illuminate\Console\Scheduling\Schedule', $schedule = new TelegramSchedule
        );

        $this->schedule($schedule);
    }

}