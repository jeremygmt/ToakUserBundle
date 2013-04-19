<?php

namespace Toak\UserBundle\Command;

use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Toak\UserBundle\Entity\User;

class UserCommand extends ContainerAwareCommand
{
    protected function configure()
    {
        $this
            ->setName('toak:user:add')
            ->setDescription('Ajoute un nouvel utilisateur')
            ->addArgument('email', InputArgument::REQUIRED, 'L\'adresse email')
            ->addArgument('password', InputArgument::REQUIRED, 'Le mot de passe')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {

        //var_dump($this); die();

        $email = $input->getArgument('email');
        $password = $input->getArgument('password');

        $user = new User();
        $user->setEmail($email);
        $user->setRawPassword($password);

        $factory = $this->getContainer()->get('security.encoder_factory');
        $encoder = $factory->getEncoder($user);
        $user->encodePassword($encoder);
            
        $em = $this->getContainer()->get('doctrine')->getEntityManager();
        $em->persist($user);
        $em->flush();
        
        $output->writeln('<comment>Utilisateur ajouté !</comment>');
        
    }
}
