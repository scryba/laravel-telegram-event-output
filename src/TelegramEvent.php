<?php

namespace Laweitech\LaravelTelegramEventOutput;

use Illuminate\Console\Scheduling\Event;
use Telegram;
use Config;
use LogicException;

class TelegramEvent extends Event
{
    //inspiration:
    //Illuminate\Console\Scheduling\Event
    //https://github.com/illuminate/console/blob/025d017ac98976aa4c6346540c249c060b7b2883/Scheduling/Event.php
    

    /**
     * Ensure that the command output is being captured.
     *
     * @return void
     */
    protected function ensureOutputIsBeingCaptured()
    {
        if (is_null($this->output) || $this->output == $this->getDefaultOutput()) {
            $this->sendOutputTo(storage_path('logs/schedule-'.sha1($this->mutexName()).'.log'));
        }
    }
    
    public function telegramOutputTo($chatId) {

        $this->ensureOutputIsBeingCaptured();

        $text = is_file($this->output) ? file_get_contents($this->output) : '';

        if (empty($text)) {
            return;
        }

        return $this->then(function () use($chatId) {

            $contents = "*" . "Scheduled Job Output For [{$this->command}]" . "*" . PHP_EOL;
            $contents .= "*" . $this->description . "*" . PHP_EOL;
            $contents .= $text;

            Telegram::sendMessage([
                'chat_id' => $chatId,
                'text' => $contents,
                'parse_mode' => 'Markdown'
            ]);
            
        });

    }

}
