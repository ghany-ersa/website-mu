<?php

namespace App\Logging;

use GuzzleHttp\Client;
use Monolog\Handler\AbstractProcessingHandler;
use Monolog\Level;
use Monolog\LogRecord;
use Throwable;

/**
 * Pushes log records to a Telegram chat via the Bot API. Fire-and-forget: a Telegram
 * API failure (bad token, network blip, rate limit) must never break the request that
 * triggered the log, so every send is wrapped and swallowed rather than re-thrown.
 */
class TelegramHandler extends AbstractProcessingHandler
{
    public function __construct(
        private readonly ?string $botToken,
        private readonly ?string $chatId,
        int|string|Level $level = Level::Error,
        bool $bubble = true,
    ) {
        parent::__construct($level, $bubble);
    }

    protected function write(LogRecord $record): void
    {
        if (! $this->botToken || ! $this->chatId) {
            return;
        }

        $text = sprintf(
            "🚨 *%s* [%s]\n\n%s",
            config('app.name', 'Laravel'),
            $record->level->getName(),
            $this->truncate($this->summarize($record)),
        );

        try {
            (new Client(['timeout' => 5]))->post(
                "https://api.telegram.org/bot{$this->botToken}/sendMessage",
                [
                    'form_params' => [
                        'chat_id' => $this->chatId,
                        'text' => $text,
                        'parse_mode' => 'Markdown',
                    ],
                ]
            );
        } catch (Throwable) {
            // Swallow: logging must never throw and take down the original request.
        }
    }

    /**
     * Keeps the Telegram message short and skimmable: the log message plus, if an
     * exception triggered it, its class and the single "thrown in file:line" location -
     * never the full stack trace, which is what made early messages unreadably long.
     */
    private function summarize(LogRecord $record): string
    {
        $lines = [];

        if (app()->bound('request') && ($url = request()?->fullUrl())) {
            $lines[] = 'URL: '.$url;
        }

        $lines[] = $record->message;

        $exception = $record->context['exception'] ?? null;

        if ($exception instanceof Throwable) {
            $lines[] = sprintf(
                '%s at %s:%d',
                $exception::class,
                $exception->getFile(),
                $exception->getLine(),
            );
        }

        return implode("\n", $lines);
    }

    private function truncate(string $text): string
    {
        // Telegram's hard cap is 4096 chars; leave headroom for the prefix above.
        return mb_strlen($text) > 3500 ? mb_substr($text, 0, 3500)."\n…(truncated)" : $text;
    }
}
