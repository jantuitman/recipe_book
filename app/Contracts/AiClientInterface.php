<?php

namespace App\Contracts;

interface AiClientInterface
{
    /**
     * Create a chat completion
     *
     * @param array $parameters
     * @return mixed
     */
    public function chatCreate(array $parameters);
}
