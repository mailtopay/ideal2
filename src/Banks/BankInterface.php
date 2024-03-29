<?php declare(strict_types=1);

namespace POM\iDEAL\Banks;

interface BankInterface
{
    public function getClient(): string;
    public function getBaseUrl(): string;
    public function getApp(): string;
}