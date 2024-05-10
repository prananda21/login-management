<?php

namespace PranandaYoga\LoginManagement\App;

use PHPUnit\Framework\TestCase;

class ViewTest extends TestCase
{
    public function testRender()
    {
        View::render('Home/index', [
            "PHP Login Management"
        ]);

        $this->expectOutputRegex('[PHP Login Management]');
        $this->expectOutputRegex('[html]');
        $this->expectOutputRegex('[body]');
        $this->expectOutputRegex('[Login Management]');
        $this->expectOutputRegex('[Register]');
        $this->expectOutputRegex('[Login]');
        $this->expectOutputRegex('[Prananda Yoga]');
    }
}

// use PHPUnit\Framework\TestCase;

// class ViewTest extends TestCase
// {
//     public function testRender()
//     {
//         View::render('Home/index', [
//             "PHP Login Management"
//         ]);

//         $this->expectOutputRegex('[PHP Login Management]');
//         $this->expectOutputRegex('[html]');
//         $this->expectOutputRegex('[body]');
//         $this->expectOutputRegex('[Login Management]');
//         $this->expectOutputRegex('[Login]');
//         $this->expectOutputRegex('[Register]');
//     }
// }
