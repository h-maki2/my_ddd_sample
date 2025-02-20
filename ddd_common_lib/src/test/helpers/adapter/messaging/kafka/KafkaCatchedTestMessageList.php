<?php

namespace dddCommonLib\test\helpers\adapter\messaging\kafka;

class KafkaCatchedTestMessageList
{
    private array $testMessageList = [];

    private static ?self $instance = null;

    private function __construct(){}

    public static function instance(): self
    {
        if (self::$instance === null) {
            self::$instance = new self();
        }

        return self::$instance;
    }

    public function addCatchedMessage(string $message): void
    {
        $this->testMessageList[] = $message;
    }

    public function isContainsMessage(string $message): bool
    {
        print_r($this->testMessageList);
        foreach ($this->testMessageList as $testMessage) {
            if ($testMessage === $message) {
                return true;
            }
        }

        return false;
    }

    public function resetMessage(): void
    {
        $this->testMessageList = [];
    }
}