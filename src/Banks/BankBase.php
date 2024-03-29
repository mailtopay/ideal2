<?php

namespace POM\iDEAL\Banks;

class BankBase
{
    /**
     * @var string
     */
    private string $client;

    /**
     * @var string
     */
    private string $app;

    /**
     * @var string
     */
    private string $baseUrl;

    /**
     * @return string
     */
    public function getClient(): string
    {
        return $this->client;
    }

    /**
     * @return string
     */
    public function getBaseUrl(): string
    {
        return $this->baseUrl;
    }

    /**
     * @return string
     */
    public function getApp(): string
    {
        return $this->app;
    }

    /**
     * @param string $client
     */
    public function setClient(string $client): void
    {
        $this->client = $client;
    }

    /**
     * @param string $baseUrl
     */
    public function setBaseUrl(string $baseUrl): void
    {
        $this->baseUrl = $baseUrl;
    }

    /**
     * @param string $app
     */
    public function setApp(string $app): void
    {
        $this->app = $app;
    }
}