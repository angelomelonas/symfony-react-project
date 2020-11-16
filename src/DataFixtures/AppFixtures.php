<?php

namespace App\DataFixtures;

use App\Entity\BlogPost;
use DateTime;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;

class AppFixtures extends Fixture
{
    public function load(ObjectManager $manager)
    {
        $blogPost = new BlogPost();
        $blogPost->setTitle('Title');
        $blogPost->setAuthor('Author');
        $blogPost->setPublished(new DateTime('2020-11-15 12:00:00'));
        $blogPost->setContent('Some content');
        $blogPost->setSlug('first-post');

        $manager->persist($blogPost);

        $manager->flush();
    }
}
