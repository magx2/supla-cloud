<?php
namespace SuplaBundle\Command;

use Assert\Assertion;
use Doctrine\ORM\EntityManagerInterface;
use SuplaBundle\Entity\EntityUtils;
use SuplaBundle\Repository\UserRepository;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Helper\QuestionHelper;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Question\Question;

class ChangeUserLimitsCommand extends ContainerAwareCommand {
    /** @var UserRepository */
    private $userRepository;
    /** @var EntityManagerInterface */
    private $entityManager;

    public function __construct(UserRepository $userRepository, EntityManagerInterface $entityManager) {
        parent::__construct();
        $this->userRepository = $userRepository;
        $this->entityManager = $entityManager;
    }

    protected function configure() {
        $this
            ->setName('supla:change-user-limits')
            ->setDescription('Allows to change user limits.');
    }

    protected function execute(InputInterface $input, OutputInterface $output) {
        /** @var QuestionHelper $helper */
        $helper = $this->getHelper('question');
        $email = $helper->ask($input, $output, new Question('Whose limits do you want to change? (email address): '));
        $user = $this->userRepository->findOneByEmail($email);
        Assertion::notNull($user, 'Such user does not exist.');
        foreach ([
                     'limitAid' => 'Access Identifiers',
                     'limitChannelGroup' => 'Channel Groups',
                     'limitChannelPerGroup' => 'Channels per Channel Group',
                     'limitDirectLink' => 'Direct Links',
                     'limitLoc' => 'Locations',
                     'limitOAuthClient' => 'OAuth Clients',
                     'limitScenes' => 'Scenes',
                     'limitSchedule' => 'Schedules',
                 ] as $field => $label) {
            $currentLimit = EntityUtils::getField($user, $field);
            $newLimit = $helper->ask($input, $output, new Question("Limit of $label [$currentLimit]: ", $currentLimit));
            EntityUtils::setField($user, $field, $newLimit);
        }
        $this->entityManager->persist($user);
        $this->entityManager->flush();
        $output->writeln('<info>User limits have been updated.</info>');
    }
}
