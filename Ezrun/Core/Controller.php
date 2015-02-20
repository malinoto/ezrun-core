<?php
namespace Ezrun\Core;

class Controller extends BaseCore {
    
    protected $render;
    protected $controller;
    protected $action;
    protected $request_parameters;
    protected $class;
    protected $respone;
    
    public function __construct(Render $render, $controller, $action, $request_parameters) {
        
        $this->setController($controller);
        $this->setAction($action);
        $this->setRequestParameters($request_parameters);
        $this->setRender($render);
    }
    
    public function prepare() {
        
        $this->setClass();
        
        if(method_exists($this->getClass(), $this->getAction())) {
            
            $fixed_parameters_order = $this->fixParametersOrder();
            
            call_user_func_array(array($this->getClass(), $this->getAction()),
                    $fixed_parameters_order);
        }
    }
    
    public function render($template, $parameters = array()) {
        
        $template = preg_replace('/\:/iu', '/', $template);
        
        $this->getRender()->setTemplate($template);
        $this->getRender()->setTemplateParameters($parameters);
    }
    
    public function setClass() {
        
        $controller_file = $this->getController() . 'Controller.php';
        
        require_once(controllers_path . $controller_file);
        
        $classname  = $this->getReflectionClassname();
        $reflection = new \ReflectionClass($classname);
        $class      = $reflection->newInstanceArgs(
                        array(
                            $this->getRender(),
                            $this->getController(),
                            $this->getAction(),
                            $this->getRequestParameters()
                        )
                    );
        
        $this->class = $class;
        
        return $this;
    }
    
    public function getClass() {
        
        return $this->class;
    }
    
    private function getReflectionClassname() {
        
        $classname = '\\Controllers\\' . $this->getController() . 'Controller';
        
        return $classname;
    }
    
    private function getReflectionParameterType(\ReflectionParameter $param) {
        
        preg_match('/\[\s([^\$]*)/iu', $param->__toString(), $matches);
        
        $result = isset($matches[1]) ? $matches[1] : null;
        
        return $result;
    }
    
    private function fixParametersOrder() {
        
        $classname  = $this->getReflectionClassname();
        $reflection = new \ReflectionClass($classname);
        $parameters = $reflection->getMethod($this->getAction())->getParameters();

        $request_parameters = $this->getRequestParameters();
        $fixed_order        = array();

        for($i = 0; $i < count($parameters); $i++) {

            if(isset($request_parameters[$parameters[$i]->getName()])) {

                array_push($fixed_order, $request_parameters[$parameters[$i]->getName()]);
            }
            else {

                $parameter_instance = $parameters[$i]->getClass()->getName();
                
                switch($parameter_instance) {

                    case __NAMESPACE__ . '\Request':
                        array_push($fixed_order, $this->getRender()->getRequest());
                        break;

                    default:
                        throw new \Exception('Unknown parameter instance ' . $parameter_instance, 403);
                        break;
                }

            }
        }
        
        return $fixed_order;
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
    
    public function setRequestParameters($request_parameters) {
        
        $this->request_parameters = $request_parameters;
        
        return $this;
    }
    
    public function getRequestParameters() {
        
        return $this->request_parameters;
    }
    
    public function setResponse($response) {
        
        $this->response = $response;
        
        return $this;
    }
    
    public function getResponse() {
        
        return $this->response;
    }
    
}
