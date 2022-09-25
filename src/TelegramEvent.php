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
    
    protected function removeSpacesAll($string)
    {
		//replace whitespaces with nothing
        $cleaned_string = preg_replace('/\s+/', '', $string);

        //Replace Multiple New Lines with nothing
        $cleaned_string = preg_replace("/[\r\n]+/", "", $cleaned_string);

        //Replace Multiple New Lines with nothing & Remove blank / empty lines
        $cleaned_string = preg_replace("/(^[\r\n]*|[\r\n]+)[\s\t]*[\r\n]+/", "", $cleaned_string);

        //Replace any horizontal whitespace character (since PHP 5.2.4) with nothing
        $cleaned_string = preg_replace('/\h+/', '', $cleaned_string);

        //don't know but also Replaces any horizontal whitespace character
        $cleaned_string = preg_replace('/[ \t]+/', '', $cleaned_string);

        return $cleaned_string;
	}

    protected function correctFormattingForMarkdown($string) {
        //https://core.telegram.org/bots/api#markdownv2-style
        //https://stackoverflow.com/questions/61224362/telegram-bot-cant-find-end-of-the-entity-starting-at-truncated
        //https://stackoverflow.com/questions/40626896/telegram-does-not-escape-some-markdown-characters
        //https://stackoverflow.com/questions/18134971/escape-a-group-of-characters-with-another-character
        //https://ideone.com/MxHEmf

        $inputCharsToEscape = " '_', '*', '[', ']', '(', ')', '~', '`', '>', '#', '+', '-', '=', '|', '{', '}', '.', '!' ";
        $inputEscapeSeq = "\\";

        //remove spaces in $inputCharsToEscape or else space will be escaped
        $inputCharsToEscape = $this->removeSpacesAll($inputCharsToEscape);

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

            //telegram's max lent is 4096
            //https://dev-qa.com/320717/sending-large-messages-telegram-bot
            // so we have to make sure the contents is less than that

            $contents = "*" . "Scheduled Job Output For [{$this->command}]" . "*" . PHP_EOL;
            $contents .= "*" . $this->description . "*" . PHP_EOL;
            $contents .= $text;

            //split to meet max requirements
            $contentsArray = str_split($contents, 4000);

            Telegram::sendMessage([
                'chat_id' => $chatId,
                'text' => $this->correctFormattingForMarkdown($contentsArray[0]),
                'parse_mode' => 'MarkdownV2'
            ]);

        });

    }

}
