<?php
namespace Ezrun\Core;

class Render extends BaseCore {
    
    protected $router;
    protected $twig;
    protected $template;
    protected $template_parameters  = array();
    protected $request_parameters   = array();
    protected $parameters_keys      = array();
    protected $request;
    
    public function __construct(Router $router, \Twig_Environment $twig) {
        
        $this->setRouter($router);
        $this->setTwig($twig);
        
        $this->prepare();
    }
    
    private function prepare() {
        
        foreach($this->getRouter()->getRouting() as $route) {
            
            $this->find($route);
        }
    }
    
    private function find($route) {
        
        try {
            
            $fixed_path = $this->pregFixPath($route['path']);
            
            if(preg_match($fixed_path, rtrim($_SERVER['REQUEST_URI'], '/'))) {
                
                $this->extractValuesFromPath($fixed_path);
                
                if(isset($route['requirements']['_method'])) {
                    
                    if(strtolower($route['requirements']['_method'])
                            != strtolower($_SERVER['REQUEST_METHOD'])) {
                        
                        $message  = 'Forbidden request method. Expecting ' . $route['requirements']['_method'] . '.';
                        $message .= $_SERVER['REQUEST_METHOD'] . ' given.';
                        
                        throw new \Exception($message, 403);
                    }
                }
                
                //add request
                $method = isset($route['requirements']['_method'])
                        ? $route['requirements']['_method']
                        : $_SERVER['REQUEST_METHOD'];
                
                $this->setRequest($method);
                
                $data = explode(':', $route['defaults']['_controller']);
                
                $controller = new Controller($this, $data[0], $data[1], $this->getRequestParameters());
                $controller->prepare();
            }
        }
        catch(Exeption $e) {
            
            print($e);
        }
    }
    
    public function show() {
        
        echo $this->getTwig()->render($this->getTemplate(), $this->getTemplateParameters());
    }
    
    public function setRouter($router) {
        
        $this->router = $router;
        
        return $this;
    }
    
    public function getRouter() {
        
        return $this->router;
    }
    
    public function setTwig($twig) {
        
        $this->twig = $twig;
        
        return $this;
    }
    
    public function getTwig() {
        
        return $this->twig;
    }
    
    public function setTemplate($template) {
        
        $this->template = $template;
        
        return $this;
    }
    
    public function getTemplate() {
        
        return $this->template;
    }
    
    public function setTemplateParameters($template_parameters) {
        
        $this->template_parameters = $template_parameters;
        
        return $this;
    }
    
    public function getTemplateParameters() {
        
        return $this->template_parameters;
    }
    
    public function addRequestParameter($key, $value) {
        
        $this->request_parameters[$key] = $value;
        
        return $this;
    }
    
    public function getRequestParameters() {
        
        return $this->request_parameters;
    }
    
    public function setRequest($method) {
        
        $this->request = new Request($method, $this->getRequestParameters());
        
        return $this;
    }
    
    public function getRequest() {
        
        return $this->request;
    }
    
    private function pregFixPath($path) {
        
        $path = preg_replace('/\//iu', '\/', $path);
        $path = preg_replace_callback('/\{([^\}]*)\}/iu', array(&$this, 'pregMatchPath'), $path);
        
        $path = rtrim($path, '\/');
        
        $path = '/^' . $path . '$/iu';
        
        return $path;
    }
    
    private function pregMatchPath(&$matches) {
        
        if(isset($matches[1]) && !empty($matches[1])) {
            
            array_push($this->parameters_keys, $matches[1]);
            
            return '([^\/\-\.]*)';
        }
        
        return false;
    }
    
    private function extractValuesFromPath($fixed_path) {
        
        preg_replace_callback($fixed_path, array(&$this, 'pregMatchValues'),
                rtrim($_SERVER['REQUEST_URI'], '/'));
    }
    
    private function pregMatchValues(&$matches) {
        
        for($i = 1; $i <= count($this->parameters_keys); $i++) {
            
            if(isset($matches[$i])) {
                
                $key    = $this->parameters_keys[($i - 1)];
                $value  = $matches[$i];
                
                $this->addRequestParameter($key, $value);
            }
        }
    }
}
