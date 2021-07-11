<?php

namespace ANOITCOM\EAVReasonerBundle\Tests\TestCases;

use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

class BundleTestCase extends KernelTestCase
{

    protected function setUp(): void
    {
        self::bootKernel();
    }

}