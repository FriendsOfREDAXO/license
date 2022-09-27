<?php

class list_short extends rex_console_command
{
    protected function execute(Symfony\Component\Console\Input\InputInterface $input, Symfony\Component\Console\Output\OutputInterface $output)
    {
        $projects = LicenseCheck::getReposFromPath(rex_path::base());
        if ('' != trim(rex_addon::get('license')->getConfig('path'))) {
            $additional_paths = explode("\n", rex_addon::get('license')->getConfig('path'));
            foreach ($additional_paths as $path) {
                $projects = LicenseCheck::getReposFromPath($path);
            }
        }
        $projects = LicenseCheck::sortRepos($projects);
        echo LicenseCheck::displayProjectsAsMarkDown($projects, false);
        return 1;
    }
}

class list_full extends rex_console_command
{
    protected function execute(Symfony\Component\Console\Input\InputInterface $input, Symfony\Component\Console\Output\OutputInterface $output)
    {
        $projects = LicenseCheck::getReposFromPath(rex_path::base());
        if ('' != trim(rex_addon::get('license')->getConfig('path'))) {
            $additional_paths = explode("\n", rex_addon::get('license')->getConfig('path'));
            foreach ($additional_paths as $path) {
                $projects = LicenseCheck::getReposFromPath($path);
            }
        }
        $projects = LicenseCheck::sortRepos($projects);
        echo LicenseCheck::displayProjectsAsMarkDown($projects, true);
        return 1;
    }
}
