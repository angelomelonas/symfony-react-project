<?php

namespace App\DataFixtures;

use App\Entity\BlogPost;
use App\Entity\Comment;
use App\Entity\User;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use Faker\Factory;
use Faker\Generator;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;

class AppFixtures extends Fixture
{
    private UserPasswordEncoderInterface $userPasswordEncoder;
    private Generator $faker;


    private const USERS = [
        [
            'username' => 'admin',
            'email' => 'admin@blog.com',
            'name' => 'Piotr Jura',
            'password' => 'secret123#',
            'roles' => [User::ROLE_SUPER_ADMIN],
            'enabled' => true
        ],
        [
            'username' => 'john_doe',
            'email' => 'john@blog.com',
            'name' => 'John Doe',
            'password' => 'secret123#',
            'roles' => [User::ROLE_ADMIN],
            'enabled' => true
        ],
        [
            'username' => 'rob_smith',
            'email' => 'rob@blog.com',
            'name' => 'Rob Smith',
            'password' => 'secret123#',
            'roles' => [User::ROLE_WRITER],
            'enabled' => true
        ],
        [
            'username' => 'jenny_rowling',
            'email' => 'jenny@blog.com',
            'name' => 'Jenny Rowling',
            'password' => 'secret123#',
            'roles' => [User::ROLE_WRITER],
            'enabled' => true
        ],
        [
            'username' => 'han_solo',
            'email' => 'han@blog.com',
            'name' => 'Han Solo',
            'password' => 'secret123#',
            'roles' => [User::ROLE_EDITOR],
            'enabled' => false
        ],
        [
            'username' => 'jedi_knight',
            'email' => 'jedi@blog.com',
            'name' => 'Jedi Knight',
            'password' => 'secret123#',
            'roles' => [User::ROLE_COMMENTATOR],
            'enabled' => true
        ],
    ];

    public function __construct(UserPasswordEncoderInterface $userPasswordEncoder)
    {
        $this->userPasswordEncoder = $userPasswordEncoder;
        $this->faker = Factory::create();
    }

    public function load(ObjectManager $manager)
    {
        $this->loadUsers($manager);
        $this->loadBlogPost($manager);
        $this->loadComments($manager);
    }

    public function loadBlogPost(ObjectManager $manager)
    {
        for ($i = 0; $i < 100; $i++) {
            $blogPost = new BlogPost();
            $blogPost->setTitle($this->faker->realText(30));
            $blogPost->setAuthor($this->getRandomUserReference($blogPost));
            $blogPost->setPublished($this->faker->dateTimeThisYear);
            $blogPost->setContent($this->faker->realText());
            $blogPost->setSlug($this->faker->slug);
            $this->setReference("blog_post_" . $i, $blogPost);

            $manager->persist($blogPost);
        }

        $manager->flush();
    }

    public function loadComments(ObjectManager $manager)
    {
        for ($i = 0; $i < 100; $i++) {
            $blogPost = $this->getBlogPostReference($i);

            for ($j = 0; $j < rand(1, 10); $j++) {
                $comment = new Comment();
                $comment->setContent($this->faker->realText());
                $comment->setPublished($this->faker->dateTimeThisYear);
                $comment->setAuthor($this->getRandomUserReference($comment));
                $comment->setBlogPost($blogPost);

                $manager->persist($comment);
            }
        }

        $manager->flush();
    }

    public function loadUsers(ObjectManager $manager)
    {
        foreach (self::USERS as $userFixture) {
            $user = new User();
            $user->setUsername($userFixture['username']);
            $user->setEmail($userFixture['email']);
            $user->setName($userFixture['name']);
            $user->setPassword(
                $this->userPasswordEncoder->encodePassword(
                    $user,
                    $userFixture['password'],
                ));
            $user->setRoles($userFixture['roles']);
            $this->addReference('user_' . $userFixture['username'], $user);

            $manager->persist($user);
        }
        $manager->flush();
    }

    private function getRandomUserReference($entity): User
    {
        $randomUser = self::USERS[rand(0, 5)];
        $canWriteBlogPost = !count($this->canUserWriteBlogPost($randomUser['roles']));
        $canWriteComment = !count($this->canUserWriteComment($randomUser['roles']));

        if ($entity instanceof BlogPost && $canWriteBlogPost) {
            return $this->getRandomUserReference($entity);
        }

        if ($entity instanceof Comment && $canWriteComment) {
            return $this->getRandomUserReference($entity);
        }

        return $this->getReference('user_' . $randomUser['username']);
    }

    public function getBlogPostReference(int $i): BlogPost
    {
        return $this->getReference('blog_post_' . $i);
    }

    /**
     * @param $roles
     * @return array
     */
    public function canUserWriteBlogPost($roles): array
    {
        return array_intersect(
            $roles,
            [
                User::ROLE_SUPER_ADMIN,
                User::ROLE_ADMIN,
                User::ROLE_WRITER,
            ]);
    }

    /**
     * @param $roles
     * @return array
     */
    public function canUserWriteComment($roles): array
    {
        return array_intersect(
            $roles,
            [
                User::ROLE_SUPER_ADMIN,
                User::ROLE_ADMIN,
                User::ROLE_WRITER,
                User::ROLE_COMMENTATOR,
            ]);
    }
}
