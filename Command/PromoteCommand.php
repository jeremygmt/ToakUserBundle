<?php

namespace Toak\UserBundle\Command;

use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Toak\UserBundle\Entity\User;

class PromoteCommand extends ContainerAwareCommand
{
    protected function configure()
    {
        $this
            ->setName('toak:user:promote')
            ->setDescription('Promote un utilisateur admin')
            ->addArgument('email', InputArgument::REQUIRED, 'L\'adresse email')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {

        $email = $input->getArgument('email');
        
        $em = $this->getContainer()->get('doctrine')->getEntityManager();

        $user = $em->getRepository('ToakUserBundle:User')->findOneBy(array('email' => $email));

        if ($user) {
            $user->setIsAdmin(true);
            $em->persist($user);
            $em->flush();
        }
        
        $output->writeln('<comment>Utilisateur promote !</comment>');
        
    }
}
