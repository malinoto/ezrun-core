<?php
namespace Core;

class Render extends BaseCore {
    
    protected $router;
    protected $twig;
    protected $template;
    protected $template_parameters = array();
    
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
            
            if(rtrim($route['path'], '/') == rtrim($_SERVER['REQUEST_URI'], '/')) {
                
                $data = explode(':', $route['defaults']['_controller']);
                
                $controller = new Controller($this, $data[0], $data[1]);
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
    
}
