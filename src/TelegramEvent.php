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
    
    protected function correctFormattingForMarkdown($string) {
        //https://core.telegram.org/bots/api#markdownv2-style
        //https://stackoverflow.com/questions/61224362/telegram-bot-cant-find-end-of-the-entity-starting-at-truncated
        //https://stackoverflow.com/questions/40626896/telegram-does-not-escape-some-markdown-characters
        //https://stackoverflow.com/questions/18134971/escape-a-group-of-characters-with-another-character
        //https://ideone.com/MxHEmf

        $inputCharsToEscape = " '_', '*', '[', ']', '(', ')', '~', '`', '>', '#', '+', '-', '=', '|', '{', '}', '.', '!' ";
        $inputEscapeSeq = "\\";

        $charsToEscape = preg_quote($inputCharsToEscape, '/');
        $regexSafeEscapeSeq = preg_quote($inputEscapeSeq, '/');
        $escapeSeq = preg_replace('/([$\\\\])/', '\\\$1', $inputEscapeSeq);

        $finale = preg_replace('/(?<!'.$regexSafeEscapeSeq.')(['.$charsToEscape.'])/', $escapeSeq.'$1', $string);
        return $finale;       

    }

    public function telegramOutputTo($chatId) {

        $this->ensureOutputIsBeingCaptured();

        $text = is_file($this->output) ? file_get_contents($this->output) : '';

        if (empty($text)) {
            return;
        }

        return $this->then(function () use($chatId, $text) {

            $contents = "*" . "Scheduled Job Output For [{$this->command}]" . "*" . PHP_EOL;
            $contents .= "*" . $this->description . "*" . PHP_EOL;
            $contents .= $text;

            Telegram::sendMessage([
                'chat_id' => $chatId,
                'text' => $this->correctFormattingForMarkdown($contents),
                'parse_mode' => 'MarkdownV2'
            ]);

        });

    }

}
