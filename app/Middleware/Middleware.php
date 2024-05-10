<?php

namespace PranandaYoga\LoginManagement\Middleware;

interface Middleware // Middleware contract
{
    function before(): void;
}
