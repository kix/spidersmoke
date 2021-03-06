<?php

namespace SpiderSmoke\Command;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Formatter\OutputFormatterStyle;

class CollectUrlsCommand extends Command
{
    
    protected function configure()
    {   
        $start = 0;
        $stop = 100;

        $this->setName("urls:collect")
             ->setDescription("Collect URLs from a given domain")
             ->setDefinition(array(
                  new InputOption('domain', 'd', InputOption::VALUE_REQUIRED, 'Domain to parse URLs from'),
            ))
             ->setHelp(<<<EOT
Crawls all accessible URLs on the given domain, and checks their HTTP return codes
EOT
);
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $domain = $input->getOption('domain');

        $crawler = new \Arachnid\Crawler(
            $domain, 
            3
        );

        $output->writeln(sprintf('<info>Crawling %s...</info>', $domain));
        $crawler->traverse();
        $links = $crawler->getLinks();

        $output->writeln(sprintf('Collected <comment>%s</comment> URLs', count($links)));

        $table = $this->getHelper('table');
        $table->setHeaders(array('Status', 'No. of URLs'));
        $counts = array();
        $results = array();

        foreach ($links as $url => $info) {
            if (!array_key_exists('status_code', $info)) {
                continue;
            }

            if (!array_key_exists($info['status_code'], $counts)) {
                $counts[$info['status_code']] = 1;
            } else {
                $counts[$info['status_code']]++;
            }

            if ($info['status_code'] !== 200) {
                if (!array_key_exists($info['status_code'], $results)) {
                    $results[$info['status_code']] = array();
                }

                $results[$info['status_code']] []= $info;
            }
            
        }

        $rows = array();

        foreach ($counts as $key => $value) {
            $rows[]= array($key, $value);
        }

        $table->setRows($rows);

        $table->render($output);
    }

}