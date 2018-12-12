<?php

class list_short extends rex_console_command {
    protected function execute() {
        $projects = LicenseCheck::getReposFromPath(rex_path::base());
        if (trim(rex_addon::get('ylicense')->getConfig('path')) != '') {
            $additional_paths = explode("\n", rex_addon::get('ylicense')->getConfig('path'));
            foreach ($additional_paths AS $path) {
                $projects = LicenseCheck::getReposFromPath($path);
            }
        }
        $projects = LicenseCheck::sortRepos($projects);
        echo LicenseCheck::displayProjectsAsMarkDown($projects,false);
    }
}

class list_full extends rex_console_command {
    protected function execute() {
        $projects = LicenseCheck::getReposFromPath(rex_path::base());
        if (trim(rex_addon::get('ylicense')->getConfig('path')) != '') {
            $additional_paths = explode("\n", rex_addon::get('ylicense')->getConfig('path'));
            foreach ($additional_paths AS $path) {
                $projects = LicenseCheck::getReposFromPath($path);
            }
        }
        $projects = LicenseCheck::sortRepos($projects);
        echo LicenseCheck::displayProjectsAsMarkDown($projects,true);
    }
}