<?php

namespace App\DataFixtures;

use App\Entity\Cursus;
use App\Entity\EnrollmentCursus;
use App\Entity\EnrollmentLesson;
use App\Entity\Lesson;
use App\Entity\Theme;
use App\Entity\User;
use App\Enum\UserRole;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use Symfony\Component\HttpFoundation\File\File;

class AppFixtures extends Fixture
{
    public function load(ObjectManager $manager): void
    {
        $users = $this->createUsers($manager);
        $content = $this->createThemesAndContent($manager);
        $this->createEnrollments($manager, $users, $content);
        $manager->flush();
    }

    private function createUsers(ObjectManager $manager): array
    {
        $user = new User();
        $user->setName('Johndoe');
        $user->setEmail('johndoe@gmail.com');
        $user->setPassword(password_hash('johndoe', PASSWORD_BCRYPT));
        $user->setRole(UserRole::USER);
        $user->setIsVerified(true);
        $manager->persist($user);

        $admin = new User();
        $admin->setName('admin');
        $admin->setEmail('admin@gmail.com');
        $admin->setPassword(password_hash('adminpassword', PASSWORD_BCRYPT));
        $admin->setRole(UserRole::ADMIN);
        $admin->setIsVerified(true);
        $manager->persist($admin);

        return ['user' => $user, 'admin' => $admin];
    }

    private function createThemesAndContent(ObjectManager $manager): array
    {

        /** @var Theme $themeMusique */
        $themeMusique = new Theme();
        $themeMusique->setName('Musique');
        $themeMusique->setImageFile(new File('public/uploads/img/musique.jpg'));
        $themeMusique->setImageName('musique.jpg');
        $manager->persist($themeMusique);

        /** @var Cursus $cursusGuitare */
        $cursusGuitare = new Cursus();
        $cursusGuitare->setName('Initiation à la guitare');
        $cursusGuitare->setPrice(50.0);
        $cursusGuitare->setTheme($themeMusique);
        $manager->persist($cursusGuitare);

        /** @var Lesson $lessonGuitare1 */
        $lessonGuitare1 = new Lesson();
        $lessonGuitare1->setName('Découverte de l\'instrument');
        $lessonGuitare1->setPrice(26.0);
        $lessonGuitare1->setDescription('Lorem ipsum dolor sit amet, consectetur adipiscing elit. Sed do eiusmod tempor incididunt ut labore et dolore magna aliqua. Ut enim ad minim veniam, quis nostrud exercitation ullamco laboris.');
        $lessonGuitare1->setVideoName('test-video.mp4');
        $lessonGuitare1->setCursus($cursusGuitare);
        $cursusGuitare->addLesson($lessonGuitare1);
        $manager->persist($lessonGuitare1);

        /** @var Lesson $lessonGuitare2 */
        $lessonGuitare2 = new Lesson();
        $lessonGuitare2->setName('Les accords et les gammes');
        $lessonGuitare2->setPrice(26.0);
        $lessonGuitare2->setDescription('Lorem ipsum dolor sit amet, consectetur adipiscing elit. Sed do eiusmod tempor incididunt ut labore et dolore magna aliqua. Ut enim ad minim veniam, quis nostrud exercitation ullamco laboris.');
        $lessonGuitare2->setVideoName('test-video.mp4');
        $lessonGuitare2->setCursus($cursusGuitare);
        $cursusGuitare->addLesson($lessonGuitare2);
        $manager->persist($lessonGuitare2);

        /** @var Cursus $cursusPiano */
        $cursusPiano = new Cursus();
        $cursusPiano->setName('Initiation au piano');
        $cursusPiano->setPrice(50.0);
        $cursusPiano->setTheme($themeMusique);
        $manager->persist($cursusPiano);

        /** @var Lesson $lessonPiano1 */
        $lessonPiano1 = new Lesson();
        $lessonPiano1->setName('Découverte de l\'instrument');
        $lessonPiano1->setPrice(26.0);
        $lessonPiano1->setDescription('Lorem ipsum dolor sit amet, consectetur adipiscing elit. Sed do eiusmod tempor incididunt ut labore et dolore magna aliqua. Ut enim ad minim veniam, quis nostrud exercitation ullamco laboris.');
        $lessonPiano1->setVideoName('test-video.mp4');
        $lessonPiano1->setCursus($cursusPiano);
        $cursusPiano->addLesson($lessonPiano1);
        $manager->persist($lessonPiano1);

        /** @var Lesson $lessonPiano2 */
        $lessonPiano2 = new Lesson();
        $lessonPiano2->setName('Les accords et les gammes');
        $lessonPiano2->setPrice(26.0);
        $lessonPiano2->setDescription('Lorem ipsum dolor sit amet, consectetur adipiscing elit. Sed do eiusmod tempor incididunt ut labore et dolore magna aliqua. Ut enim ad minim veniam, quis nostrud exercitation ullamco laboris.');
        $lessonPiano2->setVideoName('test-video.mp4');
        $lessonPiano2->setCursus($cursusPiano);
        $cursusPiano->addLesson($lessonPiano2);
        $manager->persist($lessonPiano2);

        /** @var Theme $themeInformatique */
        $themeInformatique = new Theme();
        $themeInformatique->setName('Informatique');
        $themeInformatique->setImageFile(new File('public/uploads/img/informatique.jpg'));
        $themeInformatique->setImageName('informatique.jpg');
        $manager->persist($themeInformatique);

        /** @var Cursus $cursusWeb */
        $cursusWeb = new Cursus();
        $cursusWeb->setName('Initiation au développement web');
        $cursusWeb->setPrice(60.0);
        $cursusWeb->setTheme($themeInformatique);
        $manager->persist($cursusWeb);

        /** @var Lesson $lessonWeb1 */
        $lessonWeb1 = new Lesson();
        $lessonWeb1->setName('Les langages HTML et CSS');
        $lessonWeb1->setPrice(32.0);
        $lessonWeb1->setDescription('Lorem ipsum dolor sit amet, consectetur adipiscing elit. Sed do eiusmod tempor incididunt ut labore et dolore magna aliqua. Ut enim ad minim veniam, quis nostrud exercitation ullamco laboris.');
        $lessonWeb1->setVideoName('test-video.mp4');
        $lessonWeb1->setCursus($cursusWeb);
        $cursusWeb->addLesson($lessonWeb1);
        $manager->persist($lessonWeb1);

        /** @var Lesson $lessonWeb2 */
        $lessonWeb2 = new Lesson();
        $lessonWeb2->setName('Dynamiser votre site avec JavaScript');
        $lessonWeb2->setPrice(32.0);
        $lessonWeb2->setDescription('Lorem ipsum dolor sit amet, consectetur adipiscing elit. Sed do eiusmod tempor incididunt ut labore et dolore magna aliqua. Ut enim ad minim veniam, quis nostrud exercitation ullamco laboris.');
        $lessonWeb2->setVideoName('test-video.mp4');
        $lessonWeb2->setCursus($cursusWeb);
        $cursusWeb->addLesson($lessonWeb2);
        $manager->persist($lessonWeb2);

        /** @var Theme $themeJardinage */
        $themeJardinage = new Theme();
        $themeJardinage->setName('Jardinage');
        $themeJardinage->setImageFile(new File('public/uploads/img/jardinage.jpeg'));
        $themeJardinage->setImageName('jardinage.jpeg');
        $manager->persist($themeJardinage);

        /** @var Cursus $cursusJardinage */
        $cursusJardinage = new Cursus();
        $cursusJardinage->setName('Initiation au jardinage');
        $cursusJardinage->setPrice(30.0);
        $cursusJardinage->setTheme($themeJardinage);
        $manager->persist($cursusJardinage);

        /** @var Lesson $lessonJardinage1 */
        $lessonJardinage1 = new Lesson();
        $lessonJardinage1->setName('Les outils du jardinier');
        $lessonJardinage1->setPrice(16.0);
        $lessonJardinage1->setDescription('Lorem ipsum dolor sit amet, consectetur adipiscing elit. Sed do eiusmod tempor incididunt ut labore et dolore magna aliqua. Ut enim ad minim veniam, quis nostrud exercitation ullamco laboris.');
        $lessonJardinage1->setVideoName('test-video.mp4');
        $lessonJardinage1->setCursus($cursusJardinage);
        $cursusJardinage->addLesson($lessonJardinage1);
        $manager->persist($lessonJardinage1);

        /** @var Lesson $lessonJardinage2 */
        $lessonJardinage2 = new Lesson();
        $lessonJardinage2->setName('Jardiner avec la lune');
        $lessonJardinage2->setPrice(16.0);
        $lessonJardinage2->setDescription('Lorem ipsum dolor sit amet, consectetur adipiscing elit. Sed do eiusmod tempor incididunt ut labore et dolore magna aliqua. Ut enim ad minim veniam, quis nostrud exercitation ullamco laboris.');
        $lessonJardinage2->setVideoName('test-video.mp4');
        $lessonJardinage2->setCursus($cursusJardinage);
        $cursusJardinage->addLesson($lessonJardinage2);
        $manager->persist($lessonJardinage2);

        /** @var Theme $themeCuisine */
        $themeCuisine = new Theme();
        $themeCuisine->setName('Cuisine');
        $themeCuisine->setImageFile(new File('public/uploads/img/cuisine.jpeg'));
        $themeCuisine->setImageName('cuisine.jpeg');
        $manager->persist($themeCuisine);

        /** @var Cursus $cursusCuisine1 */
        $cursusCuisine1 = new Cursus();
        $cursusCuisine1->setName('Initiation à la cuisine');
        $cursusCuisine1->setPrice(44.0);
        $cursusCuisine1->setTheme($themeCuisine);
        $manager->persist($cursusCuisine1);

        /** @var Lesson $lessonCuisine1 */
        $lessonCuisine1 = new Lesson();
        $lessonCuisine1->setName('Les modes de cuisson');
        $lessonCuisine1->setPrice(23.0);
        $lessonCuisine1->setDescription('Lorem ipsum dolor sit amet, consectetur adipiscing elit. Sed do eiusmod tempor incididunt ut labore et dolore magna aliqua. Ut enim ad minim veniam, quis nostrud exercitation ullamco laboris.');
        $lessonCuisine1->setVideoName('test-video.mp4');
        $lessonCuisine1->setCursus($cursusCuisine1);
        $cursusCuisine1->addLesson($lessonCuisine1);
        $manager->persist($lessonCuisine1);

        /** @var Lesson $lessonCuisine2 */
        $lessonCuisine2 = new Lesson();
        $lessonCuisine2->setName('Les saveurs');
        $lessonCuisine2->setPrice(23.0);
        $lessonCuisine2->setDescription('Lorem ipsum dolor sit amet, consectetur adipiscing elit. Sed do eiusmod tempor incididunt ut labore et dolore magna aliqua. Ut enim ad minim veniam, quis nostrud exercitation ullamco laboris.');
        $lessonCuisine2->setVideoName('test-video.mp4');
        $lessonCuisine2->setCursus($cursusCuisine1);
        $cursusCuisine1->addLesson($lessonCuisine2);
        $manager->persist($lessonCuisine2);

        /** @var Cursus $cursusCuisine2 */
        $cursusCuisine2 = new Cursus();
        $cursusCuisine2->setName('L\'art du dressage culinaire');
        $cursusCuisine2->setPrice(48.0);
        $cursusCuisine2->setTheme($themeCuisine);
        $manager->persist($cursusCuisine2);

        /** @var Lesson $lessonCuisine3 */
        $lessonCuisine3 = new Lesson();
        $lessonCuisine3->setName('Mettre en œuvre le style dans l\'assiette');
        $lessonCuisine3->setPrice(26.0);
        $lessonCuisine3->setDescription('Lorem ipsum dolor sit amet, consectetur adipiscing elit. Sed do eiusmod tempor incididunt ut labore et dolore magna aliqua. Ut enim ad minim veniam, quis nostrud exercitation ullamco laboris.');
        $lessonCuisine3->setVideoName('test-video.mp4');
        $lessonCuisine3->setCursus($cursusCuisine2);
        $cursusCuisine2->addLesson($lessonCuisine3);
        $manager->persist($lessonCuisine3);

        /** @var Lesson $lessonCuisine4 */
        $lessonCuisine4 = new Lesson();
        $lessonCuisine4->setName('Harmoniser un repas à quatre plats');
        $lessonCuisine4->setPrice(26.0);
        $lessonCuisine4->setDescription('Lorem ipsum dolor sit amet, consectetur adipiscing elit. Sed do eiusmod tempor incididunt ut labore et dolore magna aliqua. Ut enim ad minim veniam, quis nostrud exercitation ullamco laboris.');
        $lessonCuisine4->setVideoName('test-video.mp4');
        $lessonCuisine4->setCursus($cursusCuisine2);
        $cursusCuisine2->addLesson($lessonCuisine4);
        $manager->persist($lessonCuisine4);

        return [
            'lessons' => [
                $lessonGuitare1, $lessonGuitare2, $lessonPiano1, $lessonPiano2,
                $lessonWeb1, $lessonWeb2, $lessonJardinage1, $lessonJardinage2,
                $lessonCuisine1, $lessonCuisine2, $lessonCuisine3, $lessonCuisine4
            ],
            'cursus' => [
                $cursusGuitare, $cursusPiano, $cursusWeb, $cursusJardinage,
                $cursusCuisine1, $cursusCuisine2
            ]
        ];
    }

    private function createEnrollments(ObjectManager $manager, array $users, array $content): void
    {
        $user = $users['user'];
        
        $enrollmentLesson = new EnrollmentLesson();
        $enrollmentLesson->setUser($user);
        $enrollmentLesson->setLesson($content['lessons'][0]);
        $enrollmentLesson->setInscription(new \DateTime());
        $enrollmentLesson->setIsValidated(false);
        $manager->persist($enrollmentLesson);

        $enrollmentCursus = new EnrollmentCursus();
        $enrollmentCursus->setUser($user);
        $enrollmentCursus->setCursus($content['cursus'][2]);
        $enrollmentCursus->setInscription(new \DateTime());
        $enrollmentCursus->setIsValidated(false);
        $manager->persist($enrollmentCursus);
    }
}
