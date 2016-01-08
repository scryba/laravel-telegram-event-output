<?php

namespace AlbertoBottarini\LaravelTelegramEventOutput;

use Illuminate\Console\Scheduling\Event;
use Telegram;
use Config;

class TelegramEvent extends Event
{

    public function telegramOutputTo() {

        if (is_null($this->output) || $this->output == $this->getDefaultOutput()) {
            throw new LogicException('Must direct output to a file in order to telegram results.');
        }

        return $this->then(function () {
            $contents = "*" . $this->description . "*" . PHP_EOL;
            $contents .= file_get_contents($this->output);
            Telegram::sendMessage([
                'chat_id' => Config::get('telegram-output.chatId');,
                'text' => $contents,
                'parse_mode' => 'Markdown'
            ]);
        });

    }

}