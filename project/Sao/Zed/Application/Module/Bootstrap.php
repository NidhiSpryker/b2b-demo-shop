<?php

use ProjectA\Shared\Library\Error\ErrorLogger;
use ProjectA\Zed\Library\Controller\Action\Helper\ViewRenderer;
use ProjectA\Shared\Library\Application\TestEnvironment;

class Bootstrap extends Zend_Application_Bootstrap_Bootstrap
{

    public function run()
    {
        $front   = $this->getResource('FrontController');
        $front->setParam('bootstrap', $this);
        $response = $front->dispatch();
        if ($front->returnResponse()) {
            return $response;
        }
    }

    protected function _initSession()
    {
        if (PHP_SAPI === 'cli' && ProjectA_Shared_Library_Environment::isNotTesting()) {
            return;
        }
        $config = ProjectA_Shared_Library_Config::get('zed');
        $saveHandler = $config->session->save_handler;
        $savePath = $config->session->save_path;
        if (isset($saveHandler) && !empty($saveHandler)) {
            ini_set('session.save_handler', $saveHandler);
        }
        if (isset($savePath) && !empty($savePath)) {
            session_save_path($savePath);
        }
        ini_set('session.auto_start', false);
    }

    protected function _initCache()
    {
        $dataDir = ProjectA_Shared_Library_Data::getLocalStoreSpecificPath('cache');
        $cache = Zend_Cache::factory('Core', 'File',
            array('lifetime' => 200),
            array('cache_dir' => $dataDir)
        );
        Zend_Locale::setCache($cache);
        return $cache;
    }

    protected function _initDbTestModePlugin()
    {
        $front = Zend_Controller_Front::getInstance();
        $front->registerPlugin(new ProjectA_Zed_Library_Controller_Plugin_DbTestMode());
    }

    protected function _initLocale()
    {
        $locale = new Zend_Locale(ProjectA_Shared_Library_Store::getInstance()->getCurrentLocale());
        Zend_Registry::set('Zend_Locale', $locale);
    }

    protected function _initMultibyteEncoding()
    {
        mb_internal_encoding('UTF-8');
        mb_regex_encoding('UTF-8');
    }

    protected function _initNavigation()
    {
        if (PHP_SAPI == 'cli') {
            return;
        }

        libxml_disable_entity_loader(false);
        $config = new Zend_Config_Xml(APPLICATION_ROOT . '/config/Zed/navigation.xml');
        $navigation = new Zend_Navigation($config);
        require_once(APPLICATION_ROOT . '/config/Zed/navigation.php');
        if (isset($navigations)) {
            $moduleNavigationFiles = $navigations;
            $this->addNavigationPages($moduleNavigationFiles, $navigation);
        }
        Zend_Registry::set('Zend_Navigation', $navigation);
    }

    private function addNavigationPages(array $navigationFilesToRead, Zend_Navigation $navigation)
    {
        foreach ($navigationFilesToRead as $moduleNavigationFile) {
            if (file_exists($moduleNavigationFile)) {
                $config = new Zend_Config_Xml($moduleNavigationFile);
                $newPages = new Zend_Navigation($config);
                foreach ($newPages->getPages() as $newPage) {
                    $parentId = $newPage->get('parent_id');
                    if ($parentId) {
                        $page = $navigation->findOneBy('id', $parentId);
                    } else {
                        $page = $navigation;
                    }
                    if (!is_null($page) && $this->checkForExistingPage($page->getPages(), $newPage->get('label'))) {
                        $page->addPage($newPage);
                    }
                }
            } else {
                $e = new Exception('Unknown file in navigation: ' . $moduleNavigationFile);
                ErrorLogger::log($e);
            }
        }
    }

    private function checkForExistingPage(array $pages, $labelOfPageToLookFor)
    {
        foreach ($pages as $page) {
            if ($page->get('label') == $labelOfPageToLookFor) {
                return false;
            }
        }
        return true;
    }

    protected function _initViewEscaping()
    {
        $this->bootstrap('view');
        $view = $this->getResource('view');
        $view->setEscape(array('ProjectA_Zed_Library_Security_Html', 'escape'));
    }

    /**
     * @return bool
     */
    protected function _initTranslate()
    {
        $pathToLanguageFile = APPLICATION_ROOT.'/config/Zed/language/';
        $pathToLanguageFile .= ProjectA_Shared_Library_Store::getInstance()->getCurrentLanguage() . '/lang.csv';

        Zend_Registry::set('Zend_Translate', $this->getTranslate('csv', $pathToLanguageFile));
    }

    /**
     * @param $adapter
     * @param $content
     *
     * @return ProjectA_Zed_Library_Translate
     */
    protected function getTranslate($adapter, $content)
    {
        return new ProjectA_Zed_Library_Translate(array(
            'adapter' => $adapter,
            'content' => $content,
            'locale' => ProjectA_Shared_Library_Store::getInstance()->getCurrentLocale()
        ));
    }

    /**
     * Initialize Propel ORM
     */
    protected function _initPropel()
    {
        $dbConfig = ProjectA_Shared_Library_Config::get('db');
        $logConfig = ProjectA_Shared_Library_Config::get('log');
        ProjectA_Zed_Library_Propel_Config::setConfig($dbConfig, $logConfig, $this->getOption('propelconfig'));
        if (TestEnvironment::isSystemUnderTest()) {
            $connection = \Propel::getConnection();
            $connection->beginTransaction();
        }
    }

    protected function _initDependencyInjector()
    {
        ProjectA_Zed_Library_Dependency_Injector::useInjector();
    }

    protected function _initActionHelper()
    {
        Zend_Controller_Action_HelperBroker::addHelper(
            new ViewRenderer()
        );

        Zend_Controller_Action_HelperBroker::addHelper(
            new ProjectA_Zed_Library_Controller_PageAction()
        );
    }

    protected function _initNewRelic()
    {
        $front = Zend_Controller_Front::getInstance();
        $front->registerPlugin(new ProjectA_Zed_Library_Controller_NewRelic());
    }

    protected function _initSsl()
    {
        Zend_Controller_Front::getInstance()->registerPlugin(new ProjectA_Zed_Library_Controller_Ssl());
    }

    public function _initDispatcher()
    {
        $dispatcher = new ProjectA_Zed_Library_Controller_Dispatcher_Project();
        $front = Zend_Controller_Front::getInstance();
        $front->setDispatcher($dispatcher);
        return $dispatcher;
    }

}
