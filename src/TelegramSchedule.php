<?php

namespace michelmelo\LaravelTelegramEventOutput;

use Illuminate\Console\Scheduling\Schedule;
use michelmelo\LaravelTelegramEventOutput\TelegramEvent;

class TelegramSchedule extends Schedule
{

	public function exec($command, array $parameters = [])
    {
        if (count($parameters)) {
            $command .= ' '.$this->compileParameters($parameters);
        }

        $this->events[] = $event = new TelegramEvent($command);

        return $event;
    }

}
