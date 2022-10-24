<?php

namespace App\Test\Controller;

use App\Entity\User;
use App\Repository\UserRepository;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\DomCrawler\Crawler;
use App\DataFixtures\UserFixture;

class UserControllerTest extends WebTestCase
{
    private KernelBrowser $client;
    private UserRepository $repository;
    private string $path = '/user/';

    protected function setUp(): void
    {
       $this->client = static::createClient();
        $this->repository = static::getContainer()->get('doctrine')->getRepository(User::class);

        foreach ($this->repository->findAll() as $object) {
          $this->repository->remove($object, true);
        }
    }

    public function newUser()
    {
        $fixture = new User();
        $fixture->setName('user1');
        $fixture->setLastName('user1_lastname');
        $fixture->setAge(100);
        $fixture->setEmail('user1_email@gmail.com');
        $fixture->setPhone(123456789);
        $this->repository->save($fixture);
        
        return $fixture;
    }

    public function displayPropertiesUser($entity, $typeSelector = '#'){

        $this->assertSelectorTextContains('html tr > td'.$typeSelector.'id', $entity->getId());
        $this->assertSelectorTextContains('html tr > td'.$typeSelector.'name', $entity->getName());
        $this->assertSelectorTextContains('html tr > td'.$typeSelector.'lastname', $entity->getLastName());
        $this->assertSelectorTextContains('html tr > td'.$typeSelector.'age', $entity->getAge());
        $this->assertSelectorTextContains('html tr > td'.$typeSelector.'email', $entity->getEmail());
        $this->assertSelectorTextContains('html tr > td'.$typeSelector.'phone', $entity->getPhone());
    }


    public function testIndex(): void
    {
    
        $fixture = $this->newUser();
    
        $crawler = $this->client->request('GET', $this->path.'page/1');
        
        self::assertResponseStatusCodeSame(200);
        self::assertPageTitleContains('User index');

        $this->displayPropertiesUser($fixture, '.');

        $createUser = $crawler->filter('a:contains("Crear usuario")')->first()->link();
        $this->client->click($createUser);

        $showUser = $crawler->filter('a:contains("Mostrar")')->first()->link();
        $this->client->click($showUser);

        $editUser = $crawler->filter('a:contains("Editar")')->first()->link();
        $this->client->click($editUser);

    }

     public function testNew(): void
    {
       $this->client->request('GET', $this->path.'new?currentPage=1');

        self::assertResponseStatusCodeSame(200);
        self::assertPageTitleContains('Nuevo usuario');

        $this->client->submitForm('Guardar', [
            'user[name]' => 'Testing_name',
            'user[last_name]' => 'Testing_last_name',
            'user[age]' => 13,
            'user[email]' => 'Testing@gmail.com',
            'user[phone]' => 625033344,
        ]);

        $fixture = $this->repository->findAll();
        self::assertSame('Testing_name', $fixture[0]->getName());
        self::assertSame('Testing_last_name', $fixture[0]->getLastName());
        self::assertSame(13, $fixture[0]->getAge());
        self::assertSame('Testing@gmail.com', $fixture[0]->getEmail());
        self::assertSame(625033344, $fixture[0]->getPhone());
        self::assertResponseRedirects('/user/page/1');
      
      
    }
  
    public function testShow(): void
    {
    
        $fixture = $this->newUser();
        $crawler = $this->client->request('GET', $this->path . $fixture->getId() .'?currentPage=1');

        self::assertResponseStatusCodeSame(200);
        self::assertPageTitleContains('Usuario');
        
        $this->assertSelectorTextContains('html h1#title', 'Usuario');

        $this->displayPropertiesUser($fixture, '#');
        
        $link = $crawler->filter('a:contains("Volver al listado")')->first()->link();
        $this->client->click($link); 
        
        
    }

    public function testEdit(): void
    {
        $fixture = $this->newUser();
        
        $this->client->request('GET', $this->path . $fixture->getId() .'/edit?currentPage=1');
        self::assertResponseStatusCodeSame(200);

        $this->client->submitForm('Actualizar', [
            'user[name]' => 'user',
            'user[last_name]' => 'userLastName',
            'user[age]' => 13,
            'user[email]' => 'userlasname@gmail.com',
            'user[phone]' => 12313113,
        ]);

        self::assertResponseRedirects('/user/page/1');

        //Test if data changed

        $fixture_update = $this->repository->findAll();

        self::assertSame('user', $fixture_update[0]->getName());
        self::assertSame('userLastName', $fixture_update[0]->getLastName());
        self::assertSame(13, $fixture_update[0]->getAge());
        self::assertSame('userlasname@gmail.com', $fixture_update[0]->getEmail());
        self::assertSame(12313113, $fixture_update[0]->getPhone());
    }
  
    public function testRemove(): void
    {
        $originalNumObjectsInRepository = count($this->repository->findAll());

        $fixture = $this->newUser();

        self::assertSame($originalNumObjectsInRepository + 1, count($this->repository->findAll()));
        $this->client->request('GET', $this->path . $fixture->getId() .'?currentPage=1');
        self::assertResponseStatusCodeSame(200);

        $this->client->submitForm('Eliminar usuario');

        self::assertSame($originalNumObjectsInRepository, count($this->repository->findAll()));
        
        self::assertResponseRedirects('/user/page/1');
    }
 
}