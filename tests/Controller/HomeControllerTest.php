<?php

namespace App\Tests\Controller;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

final class HomeControllerTest extends WebTestCase
{
    public function testIndex(): void
    {
        $client = static::createClient();
        $client->request('GET', '/');

        self::assertResponseIsSuccessful();

        $siteName = self::getContainer()->getParameter('app.site_name');

        self::assertPageTitleSame('Accueil - '.$siteName);
        self::assertSelectorTextSame('nav .navbar-brand', $siteName);
        self::assertSelectorExists('nav .navbar-brand[href="/"]');
        self::assertSelectorTextSame('nav a[aria-current="page"][href="/"]', 'Accueil');
    }
}
