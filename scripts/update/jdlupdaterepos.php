#!/usr/bin/env php
<?php
/**
 * @package    JdlUpdateRepos
 * @subpackage Base
 * @author     Nikolai Plath {@link https://github.com/elkuku}
 * @author     Created on 16-Jun-2012
 * @license    GNU/GPL
 */

// We are a valid Joomla!entry point.
define('_JEXEC', 1);

require dirname(__DIR__).'/bootstrap.php';

const ERR_TEST = 66;

const ERR_REQ = 2;

const ERR_DOMAIN = 10;

/**
 * JDL Update repositories class.
 *
 * @package  JdlUpdateRepos
 */
class JdlUpdateRepos extends JdlApplicationCli
{
    private $repoBase = '';

    private $gitPath = '';

    /**
     * Execute the application.
     *
     * @throws Exception
     * @throws UnexpectedValueException
     * @throws DomainException
     * @return void
     */
    public function doExecute()
    {
        $this->setup();

        $this->outputTitle(jgettext('Update Repositories'));

        $this->output(sprintf(jgettext('Repository Path: %s'), $this->repoBase));

        $folders = JFolder::folders($this->repoBase);

        foreach($folders as $folder)
        {
            //-- Check if it is a git repo
            if(false == JFolder::exists($this->repoBase.'/'.$folder.'/.git'))
                continue;

            $this->output()
                ->output(sprintf(jgettext('Updating: %s...'), $folder), false, '', '', 'bold');

            $cmd = 'cd "'.$this->repoBase.'/'.$folder.'" && git pull 2>&1';

            passthru($cmd, $ret);

            if(0 !== $ret)
                $this->output(jgettext('ERROR'), true, 'red', '', 'bold');
            //throw new DomainException('Something went wrong pulling the repo', ERR_DOMAIN);
        }

        $this->output()
            ->output(str_repeat('=', 30))
            ->output(sprintf(jgettext('Execution time: %s secs.')
            , time() - $this->get('execution.timestamp')))
            ->output(str_repeat('=', 30))
            ->output()
            ->outputTitle(jgettext('Finished =;)'), 'green');

        if(1)
        {
            $this->output()
                ->output(jgettext('You may close this window now.'), true, 'red', '', 'bold');
        }
    }

    private function setup()
    {
        $this->repoBase = $this->configXml->global->repoDir;

        if(! $this->repoBase || ! JFolder::exists($this->repoBase))
            throw new DomainException(jgettext('Invalid repository base'), ERR_DOMAIN);

        if('' == $this->gitPath)
        {
            exec('which git 2>/dev/null', $output, $ret);

            if(0 !== $ret)
                throw new UnexpectedValueException(jgettext('Git must be installed to run this script'), ERR_REQ);

            $this->gitPath = 'git';
        }

        return $this;
    }
}

//-- Main routine

try
{
    $application = JApplicationCli::getInstance('JdlUpdateRepos');

    JFactory::$application = $application;

    $application->execute();
}
catch(Exception $e)
{
    if(COLORS)
    {
        $color = new Console_Color2;

        $msg = $color->color('red', null, 'grey')
            .' Error: '.$e->getMessage().' '
            .$color->color('reset')
            .NL;
    }
    else
    {
        $msg = 'ERROR: '.$e->getMessage().NL;
    }

    echo $msg;

    echo NL.$e->getTraceAsString().NL;

    exit($e->getCode() ? : 1);
}
