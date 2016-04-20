<?php
namespace SmartGrabber\Component;

use DOMNavigator\Finder\FinderInterface;
use DOMNavigator\Finder\XPathFinder;
use DOMNavigator\Loader\LoaderInterface;
use DOMNavigator\Loader\URLLoader;
use DOMNavigator\Navigator;
use SmartGrabber\Criteria\CriteriaInterface;
use SmartGrabber\Factory\BaseElementFactory;
use SmartGrabber\Finder\NavigatorFinder;
use SmartGrabber\Grabber;
use SmartGrabber\GrabberInterface;
use SmartGrabber\Visitor\BaseElementVisitor;
use SmartGrabber\Visitor\ElementVisitorInterface;
use Yii;
use yii\base\Component;

class SmartGrabberComponent extends Component implements GrabberInterface
{
    /**
     * Grabber finder, use class implements the interface "SmartGrabber\Finder\FinderInterface"
     *
     * @var string
     */
    public $grabberFinderClass = NavigatorFinder::class;
    /**
     * DOM finder algorithm for DOMNavigator, if use it.
     * Expected class implements the interface "DOMNavigator\Finder\FinderInterface"
     *
     * @var string
     */
    public $navigatorFinderClass = XPathFinder::class;
    /**
     * Algorithm for loading document, only if you use DOMNavigator for finding nodes.
     * Expected class implements the interface "DOMNavigator\Loader\LoaderInterface"
     *
     * @var string
     */
    public $navigatorLoaderClass = URLLoader::class;
    /**
     * Class for create element from nodes.
     * Expected class implements the interface "SmartGrabber\Factory\ElementFactoryInterface"
     *
     * @var string
     */
    public $elementFactoryClass = BaseElementFactory::class;
    /**
     * Visitor for preparing elements content.
     * Expected class implements the interface "SmartGrabber\Visitor\ElementVisitorInterface".
     *
     * @var string
     */
    public $elementVisitorClass = BaseElementVisitor::class;

    /**
     * Instance of grabber
     *
     * @var GrabberInterface
     */
    protected $grabber;

    /**
     * Initialize base classes
     */
    public function init()
    {
        Yii::$container->set(NavigatorFinder::class, function(){
            /**
             * @var FinderInterface $finder
             * @var LoaderInterface $loader
             */
            $finder = Yii::$container->get($this->navigatorFinderClass);
            $loader = Yii::$container->get($this->navigatorLoaderClass);

            $navigator = new Navigator($loader, $finder);

            return new NavigatorFinder($navigator);
        });
    }

    /**
     * Set visitor for found elements
     *
     * @param ElementVisitorInterface $visitor
     */
    public function setVisitor(ElementVisitorInterface $visitor)
    {
        $this->getGrabber()->setVisitor($visitor);
    }

    /**
     * Grab by criteria. Find all elements by given criteria.
     * If visitor is accepted, visit in each of element.
     *
     * @param CriteriaInterface $criteria
     *
     * @return array
     */
    public function grab(CriteriaInterface $criteria)
    {
        return $this->getGrabber()->grab($criteria);
    }

    /**
     * Get instance of grabber
     *
     * @return Grabber|GrabberInterface
     *
     * @throws \yii\base\InvalidConfigException
     */
    public function getGrabber()
    {
        if (!$this->grabber) {
            $finder = Yii::$container->get($this->grabberFinderClass);
            $factory = Yii::$container->get($this->elementFactoryClass);

            $this->grabber = new Grabber($finder, $factory);

            if ($this->elementVisitorClass) {
                $visitor = Yii::$container->get($this->elementVisitorClass);
                $this->grabber->setVisitor($visitor);
            }
        }

        return $this->grabber;
    }
}