<?php
namespace Ezrun\Core;

class Controller extends BaseCore {
    
    protected $render;
    protected $controller;
    protected $action;
    protected $class;
    protected $respone;
    
    public function __construct(Render $render, $controller, $action) {
        
        $this->setController($controller);
        $this->setAction($action);
        $this->setRender($render);
    }
    
    public function prepare() {
        
        $this->setClass();
        
        if(method_exists($this->getClass(), $this->getAction()))
            call_user_func(array($this->getClass(), $this->getAction()));
    }
    
    public function render($template, $parameters = array()) {
        
        $template = preg_replace('/\:/iu', '/', $template);
        
        $this->getRender()->setTemplate($template);
        $this->getRender()->setTemplateParameters($parameters);
    }
    
    public function setClass() {
        
        $controller_file = $this->getController() . 'Controller.php';
        
        require_once(controllers_path . $controller_file);
        
        $classname = $this->class = '\\Controllers\\' . $this->getController() . 'Controller';
        
        $reflection = new \ReflectionClass($classname);
        $class      = $reflection->newInstanceArgs(
                        array(
                            $this->getRender(),
                            $this->getController(),
                            $this->getAction()
                        )
                    );
        
        $this->class = $class;
        
        return $this;
    }
    
    public function getClass() {
        
        return $this->class;
    }
    
    public function setRender($render) {
        
        $this->render = $render;
        
        return $this;
    }
    
    public function getRender() {
        
        return $this->render;
    }
    
    public function setController($controller) {
        
        $this->controller = $controller;
        
        return $this;
    }
    
    public function getController() {
        
        return $this->controller;
    }
    
    public function setAction($action) {
        
        $this->action = $action . 'Action';
        
        return $this;
    }
    
    public function getAction() {
        
        return $this->action;
    }
    
    public function setResponse($response) {
        
        $this->response = $response;
        
        return $this;
    }
    
    public function getResponse() {
        
        return $this->response;
    }
    
}
