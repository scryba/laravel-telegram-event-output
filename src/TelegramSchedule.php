<?php

namespace Laweitech\LaravelTelegramEventOutput;

use Illuminate\Console\Scheduling\Schedule;
use Laweitech\LaravelTelegramEventOutput\TelegramEvent;

class TelegramSchedule extends Schedule
{

    //inspiration :- https://github.com/illuminate/console/blob/master/Scheduling/Schedule.php#L229
    /**
     * Add a new command event to the schedule.
     *
     * @param  string  $command
     * @param  array  $parameters
     * @return \Illuminate\Console\Scheduling\Event
     */
    public function exec($command, array $parameters = [])
    {
        if (count($parameters)) {
            $command .= ' '.$this->compileParameters($parameters);
        }

        $this->events[] = $event = new TelegramEvent($this->eventMutex, $command, $this->timezone);

        return $event;
    }

}
