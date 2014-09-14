<?php

define('CONSOLE_SEPARATOR', '============================');

// Define path to application directory
defined('APPLICATION_PATH')
    || define('APPLICATION_PATH', realpath(dirname(__FILE__) . '/../application'));

// Ensure library/ is on include_path
set_include_path(implode(PATH_SEPARATOR, array(
    realpath(APPLICATION_PATH . '/../library'),
    get_include_path(),
)));

// Lets allow the horrible ZF1 autoloading
require_once 'Zend/Loader/Autoloader.php';
$autoloader = Zend_Loader_Autoloader::getInstance();
$autoloader->setFallbackAutoloader(true);


// Options available to us
$options = null;
try {
    $options = new Zend_Console_Getopt(
        array(
            'branch|b-w' => '(Optional) The branch in git you would like to release. Defaults to master.',
            'repo|r-s' => '(Optional) The location of your repository branch. Defaults to one folder up from here.',
            'dest|d=s' => '(Required) The destination directory you would like to release your site to.',
            'tmp|t-s' => '(Optional) The temp directory you would like to build your site in. Auto generated if not set.',
            'quiet|q' => '           Minimal output.',
            'help|h' => '           Displays the help message.'
        )
    );
} catch (Zend_Console_Getopt_Exception $e) {
    print $e->getUsageMessage();
    die($e->getMessage());
}


// Set some default script variables
$tmpPathAutoGenerated = false;
$tmpWorkDone = false;


// Set some default variables up, based on the options passed to us
$branch = $options->getOption('b');
$repoPath = $options->getOption('r');
$destination = $options->getOption('d');
$tmpPath = $options->getOption('t');
$quiet = $options->getOption('q');
$help = $options->getOption('h');


// Show help if it was asked for
if ($help) {
    output(CONSOLE_SEPARATOR, $quiet);
    print $options->getUsageMessage();
    output(CONSOLE_SEPARATOR, $quiet);
    exit;
}


// Set some defaults up if they haven't been passed to us
if (empty($branch)) {
    $branch = 'master';
}
if (empty($repoPath)) {
    $repoPath =  realpath(__DIR__ . '/../');
}
if (empty($tmpPath)) {
    $tmpPath = '/tmp/site_' . time();
    if (!mkdir($tmpPath, 0777)) {
        error('Could not create temporary directory');
    }
    $tmpPathAutoGenerated = true;
}


// Check necessary variables are set and are valid
if (empty($destination)) {
    error('You must set a destination to release to');
}
if (!is_dir($destination)) {
    error("$destination is not a valid directory, or does not exist");
}
if (!is_dir($tmpPath)) {
    error("$tmpPath is not a valid directory, or does not exist");
}

output(CONSOLE_SEPARATOR);


// Initiate git
output('Initializing git ...', $quiet);
$git = new Git_Git($repoPath);
if ($git === false) {
    error('Git is not installed');
}
output($git->getVersion(), $quiet);

output('Checking repository path ...', $quiet);
if (!$git->isGitDirectory()) {
    error("Repo Path: $repoPath is not under source control");
}
output('Repository path OK!', $quiet);
output(CONSOLE_SEPARATOR, $quiet);


output("Deploying branch : $branch");
output("To               : $destination");
output("Via              : $tmpPath");
output(CONSOLE_SEPARATOR);


// Let's get the new contents
// If tmp folder already existed, check if it is under git control
if (!$tmpPathAutoGenerated) {
    $git->setRepositoryPath($tmpPath);
    if ($git->isGitDirectory()) {
        // If it is, lets update and reset it
        output('Updating from existing tmp directory ...');
        $git->fetch($branch);
        output('Resetting contents ...');
        $git->reset();
        $tmpWorkDone = true;
    }
}

// Otherwise let's clone the code into our new tmp folder
if (!$tmpWorkDone) {
    $git->cloneBranch($branch, $tmpPath);
    $git->setRepositoryPath($tmpPath);
    $tmpWorkDone = true;
}

// Update all submodules
output('Initializing submodules ... ');
$git->submoduleUpdate();
output(CONSOLE_SEPARATOR);

// BACKUP TARGET DIRECTORY

// COPY CONFIG FILES?

// CLEAN TARGET DIRECTORY

// SYNC TMP DIRECTORY TO TARGET DIRECTORY

// CLEAN TMP DIRECTORY



// Helper functions
function output($message, $quiet = false) {
    if ($quiet) {
        return;
    }
    print $message . "\r\n";
}

function error($error) {
    print CONSOLE_SEPARATOR . "\r\n";
    print "Error! \r\n";
    print "Error! $error \r\n";
    print "Error! \r\n";
    print CONSOLE_SEPARATOR ."\r\n";
    exit;
}