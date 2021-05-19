<?php
namespace PrintableService;
/**
 * PrintableService
 *
 * Base class for Printable Services
 *
 * @package PrintableService
 * @author akirk
 * @copyright Copyright (c) 2021 NPEU
 * @version 0.1

 **/
 
#require_once __DIR__ . '/vendor/autoload.php';

class PrintableService
{

    protected $valid_params = [];
    protected $param_defs = [];

	public function __construct()
	{
        if (!empty($this->param_defs)) {
            foreach ($this->param_defs as $name => $pattern) {
                if (array_key_exists($name, $_GET)) {
                    if (preg_match($pattern, $_GET[$name])) {
                        $this->valid_params[$name] = $_GET[$name];
                    }
                }
            }
        }
	}

	/*public function init()
	{
		return true;
	}*/
    
    public function run()
	{

	}
}