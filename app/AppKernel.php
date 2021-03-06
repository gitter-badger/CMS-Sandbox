<?php

require_once __DIR__.'/../src/SmartCore/Bundle/SimpleProfilerBundle/Profiler.php';

use Symfony\Component\HttpKernel\Kernel;
use Symfony\Component\Config\Loader\LoaderInterface;

class AppKernel extends Kernel
{
    /**
     * Подключенные модули.
     *
     * @var array
     */
    protected $modules = [];

    /**
     * @return \Symfony\Component\HttpKernel\Bundle\BundleInterface[]
     */
    public function registerBundles()
    {
        $bundles = array(
            new Doctrine\Bundle\DoctrineBundle\DoctrineBundle(),
            new Symfony\Bundle\FrameworkBundle\FrameworkBundle(),
            new Symfony\Bundle\SecurityBundle\SecurityBundle(),
            new Symfony\Bundle\TwigBundle\TwigBundle(),
            new Symfony\Bundle\MonologBundle\MonologBundle(),
            new Symfony\Bundle\SwiftmailerBundle\SwiftmailerBundle(),
            new Symfony\Bundle\AsseticBundle\AsseticBundle(),

            //new Sensio\Bundle\FrameworkExtraBundle\SensioFrameworkExtraBundle(), // "sensio/framework-extra-bundle": "2.2.*",
            //new JMS\JMSAopBundle\JMSAopBundle(),
            //new JMS\DiExtraBundle\JMSDiExtraBundle($this), // "jms/di-extra-bundle": "1.3.*",
            //new JMS\SecurityExtraBundle\JMSSecurityExtraBundle(), // "jms/security-extra-bundle": "1.4.*",

            new Knp\Bundle\GaufretteBundle\KnpGaufretteBundle(),
            new Dizda\CloudBackupBundle\DizdaCloudBackupBundle(),

            //new Abmundi\DatabaseCommandsBundle\AbmundiDatabaseCommandsBundle(), // "abmundi/database-commands-bundle": "dev-master",
            new Avalanche\Bundle\ImagineBundle\AvalancheImagineBundle(),
            new FM\ElfinderBundle\FMElfinderBundle(),
            //new FOS\FacebookBundle\FOSFacebookBundle(),
            //new FOS\RestBundle\FOSRestBundle(), // "friendsofsymfony/rest-bundle": "*",
            new FOS\UserBundle\FOSUserBundle(),
            new Genemu\Bundle\FormBundle\GenemuFormBundle(),
            //new JMS\SerializerBundle\JMSSerializerBundle(), // "jms/serializer-bundle": "*",
            //new HWI\Bundle\OAuthBundle\HWIOAuthBundle(), // "hwi/oauth-bundle": "0.3.*@dev",
            new Knp\Bundle\DisqusBundle\KnpDisqusBundle(),
            new Knp\Bundle\MenuBundle\KnpMenuBundle(),
            new Knp\RadBundle\KnpRadBundle(),
            new Liip\DoctrineCacheBundle\LiipDoctrineCacheBundle(),
            new Liip\ThemeBundle\LiipThemeBundle(),
            new Mopa\Bundle\BootstrapBundle\MopaBootstrapBundle(),
            new RaulFraile\Bundle\LadybugBundle\RaulFraileLadybugBundle(),
            new RickySu\TagcacheBundle\TagcacheBundle(),
            new SmartCore\Bundle\ChatBundle\SmartChatBundle(),
            new SmartCore\Bundle\CMSBundle\CMSBundle(),
            new SmartCore\Bundle\FelibBundle\FelibBundle(),
            new SmartCore\Bundle\FOSUserBundle\SmartCoreFOSUserBundle(),
            new SmartCore\Bundle\HtmlBundle\HtmlBundle(),
            new SmartCore\Bundle\MediaBundle\SmartMediaBundle(),
            new SmartCore\Bundle\SimpleProfilerBundle\SmartSimpleProfilerBundle(),
            new SmartCore\Bundle\SitemapBundle\SmartSitemapBundle(),
            new SmartCore\Bundle\SeoBundle\SmartSeoBundle(),
            new SmartCore\Bundle\SessionBundle\SmartCoreSessionBundle(),
            new SmartCore\Bundle\UnicatBundle\UnicatBundle(),
            new SmartCore\Bundle\Unicat2Bundle\Unicat2Bundle(),
            new Sonata\IntlBundle\SonataIntlBundle(),
            new Stfalcon\Bundle\TinymceBundle\StfalconTinymceBundle(), // "stfalcon/tinymce-bundle": "v0.2.1",
            new WhiteOctober\BreadcrumbsBundle\WhiteOctoberBreadcrumbsBundle(),
            new WhiteOctober\PagerfantaBundle\WhiteOctoberPagerfantaBundle(),
        );

        if (in_array($this->getEnvironment(), array('dev', 'test'))) {
            $bundles[] = new Symfony\Bundle\WebProfilerBundle\WebProfilerBundle();
            $bundles[] = new Sensio\Bundle\DistributionBundle\SensioDistributionBundle();
            $bundles[] = new Sensio\Bundle\GeneratorBundle\SensioGeneratorBundle();
            $bundles[] = new JMS\DebuggingBundle\JMSDebuggingBundle($this); // "jms/debugging-bundle": "dev-master",
            $bundles[] = new Elao\WebProfilerExtraBundle\WebProfilerExtraBundle();
            // $bundles[] = new Leek\GitDebugBundle\LeekGitDebugBundle();
            $bundles[] = new Doctrine\Bundle\FixturesBundle\DoctrineFixturesBundle();
            //$bundles[] = new Doctrine\Bundle\MigrationsBundle\DoctrineMigrationsBundle();
            $bundles[] = new Egulias\ListenersDebugCommandBundle\EguliasListenersDebugCommandBundle();
            $bundles[] = new Mremi\TemplatingExtraBundle\MremiTemplatingExtraBundle();
            $bundles[] = new SensioLabs\DoctrineQueryStatisticsBundle\SensioLabsDoctrineQueryStatisticsBundle();
            $bundles[] = new SmartCore\Bundle\CMSGeneratorBundle\CMSGeneratorBundle();
            // $bundles[] = new Alb\TwigReflectionBundle\AlbTwigReflectionBundle(); // "alb/twig-reflection-bundle": "*",
        }

        if (file_exists($this->rootDir . '/usr/site.ini')) {
            $site = parse_ini_file($this->rootDir . '/usr/site.ini');

            if (isset($site['name'])) {
                $className = '\\' . $site['name'] . '\SiteBundle\\SiteBundle';
                if (class_exists($className)) {
                    $bundles[] = new $className();
                }
            }
        }

        // Чтение списка модулей. Т.е. модули подключаются почти динамически.
        if (file_exists($this->rootDir . '/usr/modules.ini')) {
            foreach (parse_ini_file($this->rootDir . '/usr/modules.ini') as $module_name => $module_class) {
                if (class_exists($module_class)) {
                    $bundles[] = new $module_class;
                    $this->modules[$module_name] = $module_class;
                } else {
                    // @todo сообщение об отсутсвии класса.
                }
            }
        }

        return $bundles;
    }

    public function boot()
    {
        parent::boot();
        \Profiler::setKernel($this);
    }

    /**
     * @return array
     */
    public function getModules()
    {
        return $this->modules;
    }

    /**
     * @param LoaderInterface $loader
     * @return void
     */
    public function registerContainerConfiguration(LoaderInterface $loader)
    {
        $loader->load(__DIR__.'/config/config_'.$this->getEnvironment().'.yml');
    }

    protected function getContainerBaseClass()
    {
        if (in_array($this->getEnvironment(), array('dev', 'test'))) {
            return '\JMS\DebuggingBundle\DependencyInjection\TraceableContainer';
        }

        return parent::getContainerBaseClass();
    }

    /**
     * @return string
     */
    public function getCacheDir()
    {
        return $this->rootDir.'/../var/cache/'.$this->environment;
    }

    /**
     * @return string
     */
    public function getLogDir()
    {
        return $this->rootDir.'/../var/logs';
    }
}
