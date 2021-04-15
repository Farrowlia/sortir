<?php
//
//namespace App\DataFixtures;
//
//use App\Entity\Campus;
//use Doctrine\Bundle\FixturesBundle\Fixture;
//use Doctrine\Persistence\ObjectManager;
//
//class CampusFixtures extends Fixture
//{
//    public function load(ObjectManager $manager)
//    {
//        $tableauCampus = [
//            1 => [
//                'nom' => 'NIORT',
//            ],
//            2 => [
//                'nom' => 'RENNES',
//            ],
//            3 => [
//                'nom' => 'LAVAL',
//            ],
//            4 => [
//                'nom' => 'QUIMPER',
//            ],
//            5 => [
//                'nom' => 'LE MANS',
//            ],
//            6 => [
//                'nom' => 'ANGERS',
//            ],
//            7 => [
//                'nom' => 'NANTES',
//            ],
//            8 => [
//                'nom' => 'LA ROCHE SUR YON',
//            ],
//        ];
//
//        foreach($tableauCampus as $key => $value){
//            $campus = new Campus();
//            $campus->setNom($value['nom']);
//            $manager->persist($campus);
//        }
//
//        $manager->flush();
//    }
//}
