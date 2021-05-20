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
    
    protected function getStaffData() {
        $db = \JFactory::getDbo();

        #echo '<pre>'; var_dump($db); echo '</pre>'; exit;

        // Create the select statement.
        $q = $this->getStaffQuery();

        $db->setQuery($q);
        if (!$db->execute($q)) {
            JError::raiseError( 500, $db->stderr() );
            return false;
        }
        
        return $db->loadAssocList();
    }
    
    protected function getStaffQuery() {
        
        $q  = 'SELECT u.id, u.name, u.email, up1.profile_value AS first_name, up2.profile_value AS last_name, up3.profile_value AS tel, up4.profile_value AS room, up5.profile_value AS alias, up6.profile_value AS avatar , up7.profile_value AS role ';
        $q .= 'FROM `#__users` u ';
        $q .= 'JOIN `#__user_usergroup_map` ugm ON u.id = ugm.user_id ';
        $q .= 'JOIN `#__usergroups` ug ON ugm.group_id = ug.id ';
        $q .= 'JOIN `#__user_profiles` up1 ON u.id = up1.user_id AND up1.profile_key = "firstlastnames.firstname" ';
        $q .= 'JOIN `#__user_profiles` up2 ON u.id = up2.user_id AND up2.profile_key = "firstlastnames.lastname" ';
        $q .= 'JOIN `#__user_profiles` up3 ON u.id = up3.user_id AND up3.profile_key = "staffprofile.tel" ';
        $q .= 'JOIN `#__user_profiles` up4 ON u.id = up4.user_id AND up4.profile_key = "staffprofile.room" ';
        $q .= 'JOIN `#__user_profiles` up5 ON u.id = up5.user_id AND up5.profile_key = "staffprofile.alias" ';
        $q .= 'JOIN `#__user_profiles` up6 ON u.id = up6.user_id AND up6.profile_key = "staffprofile.avatar_img" ';
        $q .= 'JOIN `#__user_profiles` up7 ON u.id = up7.user_id AND up7.profile_key = "staffprofile.role" ';
        $q .= 'WHERE ug.title = "Staff" ';
        $q .= 'AND u.block = 0 ';
        $q .= 'ORDER BY last_name, first_name;';
        
        return $q;
    }
}