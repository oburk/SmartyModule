<?php
/**
 * @link        https://github.com/MurgaNikolay/SmartyModule for the canonical source repository
 * @license     http://framework.zend.com/license/new-bsd New BSD License
 * @author      Murga Nikolay <work@murga.kiev.ua>
 * @package     SmartyModule
 */
namespace SmartyModule\View\Strategy;

use Laminas\EventManager\ListenerAggregateTrait;
use SmartyModule\View\Renderer\SmartyRenderer;
use Laminas\EventManager\ListenerAggregateInterface;
use Laminas\EventManager\EventManagerInterface;
use Laminas\View\ViewEvent;
use Laminas\View\Model;

class SmartyStrategy implements ListenerAggregateInterface
{
    use ListenerAggregateTrait;

    protected $view;

    public function __construct(SmartyRenderer $renderer)
    {
        $this->renderer = $renderer;
    }

    /**
     * Retrieve the composed renderer
     *
     * @return SmartyRenderer
     */
    public function getRenderer()
    {
        return $this->renderer;
    }

    /**
     * Retrieve the composed renderer
     *
     * @param \Laminas\View\ViewEvent $e
     * @return SmartyRenderer
     */
    public function selectRenderer(ViewEvent $e)
    {
        $model = $e->getModel();

        // this case needs special checking, as JsonModel is a subclass of
        // ViewModel
        if ($model instanceof Model\JsonModel) {
            // JsonModel; do nothing
            return;
        }

        if (!$model instanceof Model\ViewModel) {
            // no ViewModel; do nothing
            return;
        }

        return $this->renderer;
    }

    /**
     * Populate the response object from the View
     *
     * Populates the content of the response object from the view rendering
     * results.
     *
     * @param \Laminas\View\ViewEvent $e
     * @return void
     */
    public function injectResponse(ViewEvent $e)
    {
        $renderer = $e->getRenderer();
        if ($renderer !== $this->renderer) {
            return;
        }
        $result   = $e->getResult();
        $response = $e->getResponse();
        $response->setContent($result);
    }

    /**
     * Attach one or more listeners
     *
     * Implementors may add an optional $priority argument; the EventManager
     * implementation will pass this to the aggregate.
     *
     * @param EventManagerInterface $events
     * @param int $priority
     * @return void
     */
    public function attach(EventManagerInterface $events, $priority = 1)
    {
        $this->listeners[] = $events->attach(ViewEvent::EVENT_RENDERER, array($this, 'selectRenderer'), $priority);
        $this->listeners[] = $events->attach(ViewEvent::EVENT_RESPONSE, array($this, 'injectResponse'), $priority);
    }
}
