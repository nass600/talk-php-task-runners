<?php

use Robo\Tasks;

/**
 * This is project's console commands configuration for Robo task runner.
 *
 * @see http://robo.li/
 */
class RoboFile extends Tasks
{
    /**
     * @var string Production environment
     */
    const ENV_PROD = 'prod';

    /**
     * @var string Development environment
     */
    const ENV_DEV = 'dev';

    /**
     * @var string Node modules binary directory
     */
    protected $npmBin = 'node_modules/.bin';

    /**
     * @var string Composer Packages binary directory
     */
    protected $composerBin = 'vendor/bin';

    /**
     * @var string Symfony binary file
     */
    protected $symfonyBin = 'bin/console';

    /**
     * @var string Source code directory
     */
    protected $srcDir = 'src';

    /**
     * @var string Tests directory
     */
    protected $testsDir = 'tests';

    /**
     * @var string Build directory for QA reports
     */
    protected $buildDir = 'build';

    /**
     * First install command
     *
     * @param string $env
     * @return bool
     */
    public function setupInstall($env = self::ENV_DEV)
    {
        $task = $this->taskComposerInstall();

        if (self::ENV_PROD === $env) {
            $task->noDev()->optimizeAutoloader();
            putenv('SYMFONY_ENV=prod');
        }

        $result = $task->run();

        if (!$result->wasSuccessful()) {
            $this->say('Aborting installation due to some errors');

            return false;
        }

        $this->taskNpmInstall()->run();
        $this->databaseInstall();
        $this->cacheClear($env);
    }

    /**
     * Run server with hot reload
     *
     * @param string $env
     * @return bool
     */
    public function setupRun($env = self::ENV_DEV)
    {
        if ($env !== self::ENV_DEV) {
            return;
        }

        $this->taskExec("$this->symfonyBin server:run 0.0.0.0:8100")->background()->run();
        $this->taskExec("npm run serve")->run();
    }

    /**
     * Clears Symfony cache
     *
     * @param string $env
     */
    public function cacheClear($env = self::ENV_DEV)
    {
        $task = $this->taskExec("$this->symfonyBin cache:clear")->options(['ansi' => null, 'no-warmup' => null]);

        if (self::ENV_PROD === $env) {
            $task->option('no-debug')->option('env', 'prod');
        }

        $task->run();
    }

    /**
     * Reloads the database
     */
    public function databaseInstall($env = self::ENV_DEV)
    {
        if ($env !== self::ENV_DEV) {
            return;
        }

        $this->taskExec("$this->symfonyBin doctrine:database:drop")->option('quiet')->option('force')->run();
        $this->taskExec("$this->symfonyBin doctrine:database:create")->option('quiet')->run();
        $this->taskExec("$this->symfonyBin doctrine:schema:update")->option('force')->run();
        $this->taskExec("$this->symfonyBin hautelook:fixtures:load")->option('quiet')->run();
    }

    /**
     * Runs QA tools
     *
     * @param bool $parallel
     */
    public function qaBuild($parallel = false)
    {
        $this->qaClean();
        $this->qaPrepare();

        $tasks = [
            $this->qaPdepend(false),
            $this->qaPhpmd(false),
            $this->qaPhpcpd(false),
            $this->qaPhploc(false),
            $this->qaPhpcs(false)
        ];

        $tasks[] = $this
            ->taskExec("$this->npmBin/eslint")
            ->arg($this->srcDir)
            ->option('--color');

        if (false === $parallel) {
            foreach ($tasks as $task) {
                $task->run();
            }

            return;
        }

        $stack = $this->taskParallelExec();

        foreach ($tasks as $task) {
            $stack->process($task);
        }

        $stack->run();
    }

    /**
     * Cleans QA reports directory
     */
    public function qaClean()
    {
        $this
            ->taskExec("rm -Rf $this->buildDir")
            ->run();
    }

    /**
     * Creates QA reports directory scaffolding
     */
    public function qaPrepare()
    {
        $this
            ->taskExec("mkdir -p $this->buildDir/logs")
            ->taskExec("mkdir -p $this->buildDir/pdepend")
            ->taskExec("mkdir -p $this->buildDir/phploc")
            ->run();
    }

    /**
     * Runs pdepend
     *
     * @param bool $run
     *
     * @return $this|\Robo\Result
     */
    public function qaPdepend($run = true)
    {
        $task = $this
            ->taskExec("$this->composerBin/pdepend")
            ->option("--jdepend-xml=$this->buildDir/logs/jdepend.xml")
            ->option("--jdepend-chart=$this->buildDir/pdepend/dependencies.svg")
            ->option("--overview-pyramid=$this->buildDir/pdepend/overview-pyramid.svg")
            ->arg($this->srcDir);

        if ($run) {
            return $task->run();
        }

        return $task;
    }

    /**
     * Runs phpmd
     *
     * @param bool $run
     * @return $this|\Robo\Result
     */
    public function qaPhpmd($run = true)
    {
        $task = $this
            ->taskExec("$this->composerBin/phpmd")
            ->arg($this->srcDir)
            ->arg('text')
            ->arg("phpmd.xml");

        if ($run) {
            return $task->run();
        }

        return $task;
    }

    /**
     * Runs phpcpd
     *
     * @param bool $run
     * @return $this|\Robo\Result
     */
    public function qaPhpcpd($run = true)
    {
        $task = $this
            ->taskExec("$this->composerBin/phpcpd")
            ->arg($this->srcDir);

        if ($run) {
            return $task->run();
        }

        return $task;
    }

    /**
     * Runs phploc
     *
     * @param bool $run
     * @return $this|\Robo\Result
     */
    public function qaPhploc($run = true)
    {
        $task = $this
            ->taskExec("$this->composerBin/phploc")
            ->option("--count-tests")
            ->arg($this->srcDir)
            ->arg($this->testsDir);

        if ($run) {
            return $task->run();
        }

        return $task;
    }

    /**
     * Runs phpcs
     *
     * @param bool $run
     * @return $this|\Robo\Result
     */
    public function qaPhpcs($run = true)
    {
        $task = $this
            ->taskExec("$this->composerBin/phpcs")
            ->arg($this->srcDir)
            ->arg($this->testsDir)
            ->option('--standard=PSR2')
            ->option('--extensions=php')
            ->option('--ignore=autoload.php');

        if ($run) {
            return $task->run();
        }

        return $task;
    }
}
