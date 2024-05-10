<?php

namespace PranandaYoga\LoginManagement\App {
    function header(string $value)
    {
        echo $value;
    }
}

namespace PranandaYoga\LoginManagement\Service {
    function setcookie(string $name, string $value)
    {
        echo "$name: $value";
    }
}
